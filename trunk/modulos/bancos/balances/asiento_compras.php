<?
/*
Autor: MAC
Fecha: 03/01/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.16 $
$Date: 2006/07/11 14:45:11 $

*/

require_once("../../../config.php");

if($_POST["guardar"]=="Guardar")
{require_once("funciones.php");
 $db->StartTrans();
 extract($_POST,EXTR_SKIP);
 //insertamos el asiento de compras
 $fecha_hoy=date("Y-m-d H:i:s",mktime());

 if($actualizar=="")//si es un asiento nuevo, lo insertamos
 {
 	$query="select nextval('asiento_compras_id_asiento_compras_seq') as id_asiento_compras";
    $idlog=sql($query,"<br><br>Error al traer secuencia de asiento de compras<br><br>") or fin_pagina();
 	$id_asiento_compras=$idlog->fields['id_asiento_compras'];

 	$query="insert into asiento_compras(id_asiento_compras,mes_periodo,anio_periodo,
 	                                    iva_cf_21,iva_cf_27,iva_cf_105,iva_cf_19,iva_cf_95,impuesto_interno)
 	        values($id_asiento_compras,$mes,$año,$iva_cf_21,$iva_cf_27,$iva_cf_105,
 	               $iva_cf_19,$iva_cf_95,$impuesto_interno)";
 	sql($query,"<br>Error al guardar el asiento de compras<br>") or fin_pagina();

 	insertar_cuentas_compras($id_asiento_compras);

 	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_compras (id_asiento_compras,tipo,fecha,usuario)
    	    values($id_asiento_compras,'Creación','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de creación<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se insertó con éxito el Asiento de Compras del período $mes/$año</center></b>";
 }
 else//sino actualizamos el ya existente
 {
 	 	$query="update asiento_compras set
 	 	        iva_cf_21=$iva_cf_21,iva_cf_27=$iva_cf_27,iva_cf_105=$iva_cf_105,
 	 	        iva_cf_19=$iva_cf_19,iva_cf_95=$iva_cf_95,impuesto_interno=$impuesto_interno
 	 	        where id_asiento_compras=$id_asiento_compras";

 	sql($query,"<br>Error al actualizar el asiento de compras<br>") or fin_pagina();

 	//borramos todas las entradasde cuentas_compras para este asiento de compras,
 	//e insertamos todo nuevamente para facilitar el código
 	$query="delete from cuentas_compras where id_asiento_compras=$id_asiento_compras";
 	sql($query) or fin_pagina();

 	insertar_cuentas_compras($id_asiento_compras);

 	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_compras (id_asiento_compras,tipo,fecha,usuario)
    	    values($id_asiento_compras,'Actualización','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de actualización<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se actualizó con éxito el Asiento de Compras del período $mes/$año</center></b>";
 }

 $db->CompleteTrans();

}



if($_POST["Borrar"]=="Borrar")
{
 $id_asiento_compras=$_POST["id_asiento_compras"];
 $db->StartTrans();
 $query="delete from cuentas_compras where id_asiento_compras=$id_asiento_compras";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from log_asiento_compras where id_asiento_compras=$id_asiento_compras";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from asiento_compras where id_asiento_compras=$id_asiento_compras";
 sql($query,"<br>Error <br>") or fin_pagina();
 $id_asiento_compras="";
 unset($_POST);
?>
<table align="center">
<tr>
<td>
<b>El proceso de borrado se ha realizado Correctamente</b>
</td>
</tr>
</table>
<?
 $db->CompleteTrans();

}





if($_POST["traer_datos"]=="Traer Datos" ||$_POST["guardar"]=="Guardar")
{

 $mes=$_POST["mes"];
 $año=$_POST["año"];
 $valor_dolar=$_POST["valor_dolar"];
 //controlamos si el asiento para el periodo elegido, ya esta cargado
 $query="select * from asiento_compras where mes_periodo=$mes and anio_periodo=$año";
 $asiento_info=sql($query,"<br><br>Error al traer datos de asiento de compras<br><br>") or fin_pagina();
 $id_asiento_compras=$asiento_info->fields["id_asiento_compras"];

 //traemos los datos para llenar todos los campos correspondientes
 if($id_asiento_compras=="")
 {
  //buscamos los pagos hechos por caja, cheques o debitos, para todas las cuentas,
  //para el periodo que se eligio
  $union="
  select sum(monto) as monto,numero_cuenta, concepto,plan from
  (
   --caja pesos
   select sum(monto_caja) as monto,numero_cuenta, concepto,plan,'caja' as tipo
   from general.tipo_cuenta join caja.ingreso_egreso using(numero_cuenta) join caja.caja using(id_caja)
	join (select sum(detalle_imputacion.monto) as monto_caja,id_ingreso_egreso
			from contabilidad.detalle_imputacion join contabilidad.imputacion using(id_imputacion)
			join contabilidad.tipo_imputacion using(id_tipo_imputacion)
			where tipo_imputacion.nombre='monto_neto'
			group by id_ingreso_egreso
		)as imput using(id_ingreso_egreso)
   join licitaciones.moneda using(id_moneda)
   where caja.fecha ilike '$año-$mes-%' and simbolo='$' and not plan ilike 'RIB%' and not plan='Retenciones I.V.A.' and not plan='Retenciones Ganancia'
   and concepto<>'Bancos' and plan<>'SUSS' and plan<>'Faecys' and plan<>'Remuneraciones' and plan<>'Cargas Sociales'
   and plan<>'Sindicato empleados de Comercio'
   group by numero_cuenta, concepto,plan
   union all
   --caja dolar
   --(fue modificado para que tome el monto neto de la imputacion. Como siemrpe esta en pesos, no es necesario el $valor_dolar
   select sum(monto_caja) as monto,numero_cuenta, concepto,plan,'caja' as tipo
   from general.tipo_cuenta join caja.ingreso_egreso using(numero_cuenta) join caja.caja using(id_caja)
	join (select sum(detalle_imputacion.monto) as monto_caja,id_ingreso_egreso
			from contabilidad.detalle_imputacion join contabilidad.imputacion using(id_imputacion)
			join contabilidad.tipo_imputacion using(id_tipo_imputacion)
			where tipo_imputacion.nombre='monto_neto'
			group by id_ingreso_egreso
		)as imput using(id_ingreso_egreso)
   join licitaciones.moneda using(id_moneda)
   where caja.fecha ilike '$año-$mes-%' and simbolo='U$\S' and not plan ilike 'RIB%' and not plan='Retenciones I.V.A.' and not plan='Retenciones Ganancia'
   and concepto<>'Bancos' and plan<>'SUSS' and plan<>'Faecys' and plan<>'Remuneraciones' and plan<>'Cargas Sociales'
   and plan<>'Sindicato empleados de Comercio'
   group by numero_cuenta, concepto,plan
   union all
   --cheques
   select sum(monto_cheques) as monto, numero_cuenta, concepto,plan,'cheque' as tipo
   from general.tipo_cuenta join bancos.cheques using (numero_cuenta) join bancos.tipo_banco using (idbanco)
	  join (select sum(detalle_imputacion.monto) as monto_cheques,idbanco,númeroch
			from contabilidad.detalle_imputacion join contabilidad.imputacion using(id_imputacion)
			join contabilidad.tipo_imputacion using(id_tipo_imputacion)
			where tipo_imputacion.nombre='monto_neto'
			group by idbanco,númeroch
		)as imput using(idbanco,númeroch)
   where fechaemich ilike '$año-$mes-%' and not plan ilike 'RIB%' and not plan='Retenciones I.V.A.' and not plan='Retenciones Ganancia'
   and concepto<>'Bancos' and plan<>'SUSS' and plan<>'Faecys' and plan<>'Remuneraciones' and plan<>'Cargas Sociales'
   and plan<>'Sindicato empleados de Comercio'
   and  not nombrebanco ilike '%Corapi%'
   group by numero_cuenta, concepto,plan
   union all
   --debitos
   select sum(monto_debito) as monto,numero_cuenta, concepto,plan,'débito' as tipo
   from general.tipo_cuenta join bancos.débitos using (numero_cuenta) join bancos.tipo_banco using (idbanco)
	  join (select sum(detalle_imputacion.monto) as monto_debito,iddébito
			from contabilidad.detalle_imputacion join contabilidad.imputacion using(id_imputacion)
			join contabilidad.tipo_imputacion using(id_tipo_imputacion)
			where tipo_imputacion.nombre='monto_neto'
			group by iddébito
		)as imput using(iddébito)
   where fechadébito ilike '$año-$mes-%' and not plan ilike 'RIB%' and not plan='Retenciones I.V.A.' and not plan='Retenciones Ganancia'
   and concepto<>'Bancos' and plan<>'SUSS' and plan<>'Faecys' and plan<>'Remuneraciones' and plan<>'Cargas Sociales'
   and plan<>'Sindicato empleados de Comercio'
   and  not nombrebanco ilike '%Corapi%'
   group by numero_cuenta, concepto,plan
  )as uniones
  group by numero_cuenta, concepto,plan
  ";
  $datos_cuentas_compras=sql($union) or fin_pagina();

  //traemos los datos del libro de IVA, compras
  $query="select fact_prov.*, proveedor.razon_social from fact_prov join proveedor using (id_proveedor) ";
  $where="anio_libro_iva=$año AND mes_libro_iva=$mes order by fecha_emision asc";
  $query.="where $where";

  $datos_iva_compras=sql($query,"<br>Error al traer iva compras<br>") or fin_pagina();

  while(!$datos_iva_compras->EOF)
  {
   $iva_cf_21+=$datos_iva_compras->fields["iva21"];
   $iva_cf_27+=$datos_iva_compras->fields["iva27"];
   $iva_cf_105+=$datos_iva_compras->fields["iva10"];
   $iva_cf_19+=$datos_iva_compras->fields["iva19"];
   $iva_cf_95+=$datos_iva_compras->fields["iva95"];
   $impuesto_interno+=$datos_iva_compras->fields["imp_internos"];

   $datos_iva_compras->MoveNext();
  }

  $iva_cf_21=number_format($iva_cf_21,2,'.','');
  $iva_cf_27=number_format($iva_cf_27,2,'.','');
  $iva_cf_105=number_format($iva_cf_105,2,'.','');
  $iva_cf_19=number_format($iva_cf_19,2,'.','');
  $iva_cf_95=number_format($iva_cf_95,2,'.','');
  $impuesto_interno=number_format($impuesto_interno,2,'.','');

 }//de if($id_asiento_compras=="")
 else
 {
  $cartel="<br><B><center><font color='red'>ATENCION: El asiento para este período ya fue realizado</font></center></b>";
  //si existe el nro_asiento, entonces llenamos las variables con valores
  //traidos desde la BD
  $iva_cf_21=number_format($asiento_info->fields["iva_cf_21"],2,'.','');
  $iva_cf_27=number_format($asiento_info->fields["iva_cf_27"],2,'.','');
  $iva_cf_105=number_format($asiento_info->fields["iva_cf_105"],2,'.','');
  $iva_cf_19=number_format($asiento_info->fields["iva_cf_19"],2,'.','');
  $iva_cf_95=number_format($asiento_info->fields["iva_cf_95"],2,'.','');
  $impuesto_interno=number_format($asiento_info->fields["impuesto_interno"],2,'.','');

  //traemos todos los datos de cuentas_compras
  $query="select sum(monto) as monto,numero_cuenta,concepto,plan
   from cuentas_compras join tipo_cuenta using (numero_cuenta)
   where id_asiento_compras=$id_asiento_compras
   group by numero_cuenta,concepto,plan";
  $datos_cuentas_compras=sql($query,"<br>Error al traer cuentas_compras, del asiento") or fin_pagina();

  $actualizar=1;
 }//del else de if($nro_asiento=="")

}//de if($_POST["traer_datos"]=="Traer Datos")

echo $html_header;
?>
<script language="JavaScript" src="../../../lib/NumberFormat150.js"></script>
<script>

//calcula el sub-total de las cuentas
function calcula_sub_total()
{var cant_cuentas;
 var acum=0;
 cant_cuentas=document.all.cant_cuentas.value;
 for(i=0;i<cant_cuentas;i++)
 {aux=eval("document.all.cuenta_"+i);
  if(aux.value=="")
   acum+=0;
  else
   acum+=parseFloat(aux.value);
 }
 document.all.sub_total.value=formato_BD(acum);
 return acum;
}

//funcion que calcula la suma del debe y setea los campos caja y suma_haber
function calcular_suma_debe()
{
 var iva_cf_21;
 var iva_cf_27;
 var iva_cf_105;
 var iva_cf_19;
 var iva_cf_95;
 var impuesto_interno;
 var total;

 if(document.all.iva_cf_21.value=="")
  iva_cf_21=0;
 else
  iva_cf_21=parseFloat(document.all.iva_cf_21.value);
 if(document.all.iva_cf_27.value=="")
  iva_cf_27=0;
 else
  iva_cf_27=parseFloat(document.all.iva_cf_27.value);
 if(document.all.iva_cf_105.value=="")
  iva_cf_105=0;
 else
  iva_cf_105=parseFloat(document.all.iva_cf_105.value);
 if(document.all.iva_cf_19.value=="")
  iva_cf_19=0;
 else
  iva_cf_19=parseFloat(document.all.iva_cf_19.value);
 if(document.all.iva_cf_95.value=="")
  iva_cf_95=0;
 else
  iva_cf_95=parseFloat(document.all.iva_cf_95.value);
 if(document.all.impuesto_interno.value=="")
  impuesto_interno=0;
 else
  impuesto_interno=parseFloat(document.all.impuesto_interno.value);

 total=iva_cf_21+iva_cf_27+iva_cf_105+iva_cf_19+iva_cf_95+impuesto_interno+calcula_sub_total();

 document.all.suma_debe.value=formato_money(total);
 document.all.caja.value=formato_BD(total);
 document.all.suma_haber.value=formato_money(total);

}

//funcion que controla que los campos obligatorios sean llenados
function control_campos()
{var msg;
 var faltan;
 var i,aux,aux1;
 faltan=0;
 msg="Faltan llenar los siguientes campos\n";
 msg+="-----------------------------------------------------------------\n";

 //control de retencion ingresos brutos
 var cant_cuentas;
 cant_cuentas=document.all.cant_cuentas.value;
 for(i=0;i<cant_cuentas;i++)
 {aux=eval("document.all.cuenta_"+i);
  aux1=eval("document.all.desc_cuenta_"+i);
  if(aux.value=="")
  {faltan=1;
   msg+=aux1.value+"\n";
  }
 }//del for

 if(document.all.iva_cf_21.value=="")
 {faltan=1;
  msg+="I.V.A. Crédito Fiscal 21%\n";
 }
 if(document.all.iva_cf_27.value=="")
 {faltan=1;
  msg+="I.V.A. Crédito Fiscal 27%\n";
 }
 if(document.all.iva_cf_105.value=="")
 {faltan=1;
  msg+="I.V.A. Crédito Fiscal 10.5%\n";
 }
 if(document.all.iva_cf_19.value=="")
 {faltan=1;
  msg+="I.V.A. Crédito Fiscal 19%\n";
 }
 if(document.all.iva_cf_95.value=="")
 {faltan=1;
  msg+="I.V.A. Crédito Fiscal 9.5%\n";
 }
 if(document.all.impuesto_interno.value=="")
 {faltan=1;
  msg+="Impuesto Interno\n";
 }


 if(faltan)
 {msg+="-----------------------------------------------------------------\n";
  alert(msg);
  return false;
 }
 else
  return true;

}//de function control_campos()

//funcion que deshabilita el botón de imprimir y avisa que hubo cambios
function hay_cambios()
{
 document.all.cambios.value=1;
 if(typeof(document.all.imprimir)!='undefined')
 {document.all.imprimir.disabled=1;
  document.all.imprimir.title="Debe guardar para poder imprimir";
 }
}


//habilita los campos para editarlos
function habilitar_edicion()
{var i,aux;
 var acum=0;
 var cant_cuentas;

 document.all.iva_cf_21.readOnly=0;
 document.all.iva_cf_27.readOnly=0;
 document.all.iva_cf_105.readOnly=0;
 document.all.iva_cf_19.readOnly=0;
 document.all.iva_cf_95.readOnly=0;
 document.all.impuesto_interno.readOnly=0;

 cant_cuentas=document.all.cant_cuentas.value;
 for(i=0;i<cant_cuentas;i++)
 {aux=eval("document.all.cuenta_"+i);
  aux.readOnly=0;
 }

}

function deshabilitar_edicion()
{var i,aux;
 var acum=0;
 var cant_cuentas;

 document.all.iva_cf_21.readOnly=1;
 document.all.iva_cf_27.readOnly=1;
 document.all.iva_cf_105.readOnly=1;
 document.all.iva_cf_19.readOnly=1;
 document.all.iva_cf_95.readOnly=1;
 document.all.impuesto_interno.readOnly=1;

 cant_cuentas=document.all.cant_cuentas.value;
 for(i=0;i<cant_cuentas;i++)
 {aux=eval("document.all.cuenta_"+i);
  aux.readOnly=1;
 }

}

</script>
<?
if($actualizar)
{$query="select id_asiento_compras,tipo,fecha,usuario
 from log_asiento_compras where id_asiento_compras=$id_asiento_compras order by fecha DESC";
 $log_info=sql($query,"<br>Error al traer el log de asiento de compras<br>") or fin_pagina();
 ?>
<center>
<div align="right" style='position:relative; width:95%; height:10%; overflow:auto;'>
 <table  width="100%">
  <?
   while(!$log_info->EOF)
   {?>
    <tr id="ma">
     <td align="left">
      Fecha de <?=$log_info->fields["tipo"]?>: <?=fecha($log_info->fields["fecha"])?> <?=Hora($log_info->fields["fecha"])?>
     </td>
     <td align="right">
      Usuario: <?=$log_info->fields["usuario"]?>
     </td>
    </tr>
   	<?
   	$log_info->MoveNext();
   }//de while(!$log_info->EOF)
  ?>
 </table>
</div>
</center>
 <?
}//de if($actualizar)
//echo "<BR><CENTER><h4>ATENCION: ESTE MODULO ESTA EN VERSION BETA, LOS DATOS QUE TRAE PODRIAN NO SER CORRECTOS PORQUE NO SE TOMA EN CUENTA LA MONEDA DE LOS EGRESOS DE CAJA</h4></CENTER>";
if($msg=="")
 echo $cartel;
else
 echo $msg;
?>
<br>
<table align="center" width="95%" border="1">
 <tr>
  <td id="mo">
   <font size="3">Asiento de Compras</font>
  </td>
 </tr>
</table>
<form name="form1" action="asiento_compras.php" method="POST" <?=$disabled_form?>>
<input type="hidden" name="actualizar" value="<?=$actualizar?>">
<input type="hidden" name="id_asiento_compras" value="<?=$id_asiento_compras?>">
<input type="hidden" name="cambios" value="0">

<table align="center" width="95%" class="bordes">
 <tr>
  <td colspan="6">
   <table width="100%" bgcolor="White" cellpadding="3">
    <tr>
     <td>
      <?if($_POST["mes"]!="")
      {?>
       <input type="checkbox" name="editar" onchange="if(this.checked==1)habilitar_edicion();else deshabilitar_edicion();"> Editar
      <?
      }
	  else
	  {?>
	   &nbsp;
	  <?
	  }?>
     </td>
     <td align="right" colspan="2">
      <table border="1" width="60%">
       <tr>
        <td>
         <font color="Blue"><b>Período</b></font>
        </td>
        <td>
         <b>Mes</b>&nbsp;
         <select name=mes onchange="document.all.guardar.disabled=1">
          <option value='01' <?if ($mes==1) echo "selected"?>>Enero</option>
          <option value='02' <?if ($mes==2) echo "selected"?>>Febrero</option>
          <option value='03' <?if ($mes==3) echo "selected"?>>Marzo</option>
          <option value='04' <?if ($mes==4) echo "selected"?>>Abril</option>
          <option value='05' <?if ($mes==5) echo "selected"?>>Mayo</option>
          <option value='06' <?if ($mes==6) echo "selected"?>>Junio</option>
          <option value='07' <?if ($mes==7) echo "selected"?>>Julio</option>
          <option value='08' <?if ($mes==8) echo "selected"?>>Agosto</option>
          <option value='09' <?if ($mes==9) echo "selected"?>>Septiembre</option>
          <option value='10' <?if ($mes==10) echo "selected"?>>Octubre</option>
          <option value='11' <?if ($mes==11) echo "selected"?>>Noviembre</option>
          <option value='12' <?if ($mes==12) echo "selected"?>>Diciembre</option>
         </select>
        </td>
        <td colspan="2">
         <b>Año</b>&nbsp;
         <select name=año onchange="document.all.guardar.disabled=1">
          <option value='2003' <?if ($año==2003) echo "selected"?>>2003</option>
          <option value='2004' <?if ($año==2004) echo "selected"?>>2004</option>
          <option value='2005' <?if ($año==2005) echo "selected"?>>2005</option>
          <option value='2006' <?if ($año==2006) echo "selected"?>>2006</option>
          <option value='2007' <?if ($año==2007) echo "selected"?>>2007</option>
          <option value='2008' <?if ($año==2008) echo "selected"?>>2008</option>
          <option value='2009' <?if ($año==2009) echo "selected"?>>2009</option>
          <option value='2010' <?if ($año==2010) echo "selected"?>>2010</option>
          <option value='2011' <?if ($año==2011) echo "selected"?>>2011</option>
          <option value='2012' <?if ($año==2012) echo "selected"?>>2012</option>
         </select>
        </td>
       </tr>
      </table>
     </td>
     <td>
       <font color="Blue"><b>Valor Dolar</b></font> <input type="text" name="valor_dolar" value="<?=$valor_dolar?>" size="6" onkeypress="return filtrar_teclas(event,'0123456789.');"  onchange="document.all.guardar.disabled=1">
     </td>
     <td>
      <input type="submit" name="traer_datos" value="Traer Datos"
          onclick="if(document.all.valor_dolar.value=='')
                     {alert('Debe ingresar un Valor Dolar para traer los datos');
                      return false;
                     }
                     else
                     {
                	  if(document.all.cambios.value==1)
                      {if(confirm('Ha realizado cambios en este Asiento de Compras. Si continúa se perderán los cambios.\n¿Está seguro que desea continuar?'))
                       {
                        document.all.actualizar.value=0;
                        return true;
                       }
                       else
                        return false;
                      }
                     }
                  "
      >
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <?
 if($_POST["mes"]=="")
 {$disabled_form="disabled";
  ?>
  <tr>
   <td colspan="6" align="center">
    <font size='3' color='red'><b>Seleccione el período del asiento de compras que desea completar y presione el botón traer datos</b></font>
   </td>
  </tr>
  <?
 }
?>
 <tr id="ma">
  <td width="10%">
   Cuenta
  </td>
  <td width="20%">
   Concepto
  </td>
  <td width="40%">
   Plan
  </td>
  <td width="15%">
   DEBE
  </td>
  <td width="15%">
   HABER
  </td>
 </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
 <?
 $i=0;
 if($mes)
 {
  while(!$datos_cuentas_compras->EOF)
  {
 ?>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      <?=$datos_cuentas_compras->fields["numero_cuenta"]?>
     </td>
     <td width="20%">
      <?=$datos_cuentas_compras->fields["concepto"]?>
     </td>
     <td width="40%">
      <?=$datos_cuentas_compras->fields["plan"]?>
     </td>
     <td width="15%">
      <input type="hidden" name="desc_cuenta_<?=$i?>" value="<?=$datos_cuentas_compras->fields["concepto"]." [".$datos_cuentas_compras->fields["plan"]."]"?>">
      <input type="hidden" name="nro_cuenta_<?=$i?>" value="<?=$datos_cuentas_compras->fields["numero_cuenta"]?>">
      <input type="text" name="cuenta_<?=$i?>" value="<?=number_format($datos_cuentas_compras->fields["monto"],2,'.','')?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
   <?
   $i++;
   $datos_cuentas_compras->MoveNext();
  }//de while(!$datos_cuentas_compras->EOF)
 }//de if($mes)
  ?>
  <input type="hidden" name="cant_cuentas" value="<?=$i?>">
  <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
  </tr>
  <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      &nbsp;
     </td>
     <td width="20%">
      <b>SUB-TOTAL</b>
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="sub_total" value="" readonly size="13">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
  <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
  </tr>
  <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      40
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 21%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="iva_cf_21" value="<?=$iva_cf_21?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      41
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 27%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="iva_cf_27" value="<?=$iva_cf_27?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      42
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 10.5%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="iva_cf_105" value="<?=$iva_cf_105?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      43
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 19%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="iva_cf_19" value="<?=$iva_cf_19?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      44
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 9.5%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="iva_cf_95" value="<?=$iva_cf_95?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      536
     </td>
     <td width="20%">
      Impuesto Interno
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="impuesto_interno" value="<?=$impuesto_interno?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td width="10%">
      1
     </td>
     <td width="20%">
      <b>CAJA</b>
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="caja" value=""  size="13" readonly>
     </td>
    </tr>
    <tr  bgcolor="White">
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <font color="Blue"><b>Totales</b></font>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="suma_debe"  readonly value="<?=$suma_debe?>" class="text_8"  size="13">
     </td>
     <td width="15%">
      <input type="text" name="suma_haber"  readonly value="<?=$suma_haber?>" class="text_8" size="13">
     </td>
    </tr>
</table>
<table align="center" border="1" width="95%">
 <tr>
  <?
  if($id_asiento_compras && permisos_check("inicio","permiso_boton_borrar_asientos"))
  {//onclick=" control_campos();
   ?>
   <td width="1%">
     <input type="submit" name="Borrar"   value="Borrar" onclick="if(confirm ('Se borraran los datos de este asiento.\n¿Está seguro que desea continuar?'))
																  {document.all.actualizar.value=0;
																   return true;
																  }
																  else
																   return false;
																 "
     >
   </td>
  <?
  }
 ?>
  <td align="<?if($actualizar) echo "right";else echo "center"?>">
   <?
   if(!permisos_check("inicio","permiso_boton_guarda_asiento_compras"))
    $disabled_permiso="disabled";
   ?>
   <input type="submit" name="guardar" <?=$disabled_permiso?> <?=$disabled_form?> value="Guardar" onclick="return control_campos();">
  </td>

  <?

  if($actualizar)
  {
   $link_imprimir=encode_link("imprimir_compras.php",array("id_asiento_compras"=>$id_asiento_compras));
   ?>

   <td align="left">
    <input type="button" name="imprimir" value="Imprimir" onclick="window.open('<?=$link_imprimir?>')">
   </td>
  <?
  }
  ?>


 </tr>
</table>
</form>
<script>
//calculamos la suma del debe y el haber, y seteamos caja y sub-total
calcular_suma_debe();
</script>

<br>
<?fin_pagina();?>