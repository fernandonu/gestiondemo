<?
/*
Autor: MAC
Fecha: 21/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.14 $
$Date: 2005/07/29 15:16:09 $

*/

require_once("../../../config.php");

if($_POST["guardar"]=="Guardar")
{$db->StartTrans();
 extract($_POST,EXTR_SKIP);
 //insertamos el asiento de remuneracion
 $fecha_hoy=date("Y-m-d H:i:s",mktime());	
 
 if($actualizar=="")//si es un asiento nuevo, lo insertamos
 {
 	$query="select nextval('asiento_ventas_id_asiento_ventas_seq') as id_log_asiento";
    $idlog=sql($query,"<br><br>Error al traer secuencia de asiento de remuneracion<br><br>") or fin_pagina();
 	$id_asiento_venta=$idlog->fields['id_log_asiento'];
 	
 	$query="insert into asiento_ventas(id_asiento_ventas,resp_inscripto_21,resp_inscripto_105,
 	        cons_final_21,cons_final_105,neto_a,neto_b,mes_periodo,anio_periodo)
 	        values($id_asiento_venta,$resp_ins_21,$resp_ins_105,$cons_final_21,$cons_final_105,
 	        $neto_a,$neto_b,$mes,$año)";
 	sql($query,"<br>Error al guardar el asiento de ventas") or fin_pagina();
 	
 	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_ventas (id_asiento_ventas,tipo,fecha,usuario)
    	    values($id_asiento_venta,'Creación','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de creación<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se insertó con éxito el asiento de ventas del período $mes/$año</center></b>";
 }
 else//sino actualizamos el ya existente
 {
 	 	$query="update asiento_ventas set 
 	 	    resp_inscripto_21=$resp_ins_21,
 	 	    resp_inscripto_105=$resp_ins_105,cons_final_21=$cons_final_21,
 	 	    cons_final_105=$cons_final_105,neto_a=$neto_a,neto_b=$neto_b
 	 	    where id_asiento_ventas=$id_asiento_venta";
 	        
 	sql($query,"<br>Error al actualizar el asiento de ventas") or fin_pagina();
 	
 	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_ventas (id_asiento_ventas,tipo,fecha,usuario)
    	    values($id_asiento_venta,'Actualización','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de actualización<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se actualizó con éxito el asiento de ventas del período $mes/$año</center></b>";
 }	
 $db->CompleteTrans();
 
}


if($_POST["Borrar"]=="Borrar")
{
 $id_asiento_venta=$_POST["id_asiento_venta"];	
 $db->StartTrans();
 /*$query="delete from cuentas_compras where id_asiento_ventas=$id_asiento_venta";
 sql($query,"<br>Error <br>") or fin_pagina();*/
 $query="delete from log_asiento_ventas where id_asiento_ventas=$id_asiento_venta";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from asiento_ventas where id_asiento_ventas=$id_asiento_venta";
 sql($query,"<br>Error <br>") or fin_pagina();
 $id_asiento_venta="";
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
 //controlamos si el asiento para el periodo elegido, ya esta cargado
 $query="select * from asiento_ventas where mes_periodo=$mes and anio_periodo=$año";
 $asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 
 $id_asiento_venta=$asiento_info->fields["id_asiento_ventas"];
  
 //traemos los datos para llenar todos los campos correspondientes
 if($id_asiento_venta=="")
 { 
  $query="select facturas.*,case when estado='a' then 0 else total end as total 
      from facturas left join 
	  (select id_factura,sum(precio*cant_prod) as total 
       from items_factura group by id_factura) as totales using (id_factura)  
       where  fecha_factura ilike '$año-$mes-%' 
       order by fecha_factura";
  $datos=sql($query) or fin_pagina();


  //hacemos las sumas respectivas para poder llenar los campos necesarios
  $resp_ins_21=0;$resp_ins_105=0;$cons_final_21=0;$cons_final_105=0;$neto_a=0;$neto_b=0;
  while(!$datos->EOF)
  {if ($datos->fields['cotizacion_dolar']!=0)
	 $ratio_dolar=$datos->fields['cotizacion_dolar'];
   else
	 $ratio_dolar=1;
   if ($datos->fields['tipo_factura']=='a' && $datos->fields['iva_tasa']==21)
      $resp_ins_21+=($ratio_dolar*$datos->fields["total"])*(($datos->fields["iva_tasa"]/100)/(1+($datos->fields["iva_tasa"]/100)));

   if ($datos->fields['tipo_factura']=='a' && $datos->fields['iva_tasa']==10.5)
      $resp_ins_105+=($ratio_dolar*$datos->fields["total"])*(($datos->fields["iva_tasa"]/100)/(1+($datos->fields["iva_tasa"]/100)));

   if ($datos->fields['tipo_factura']=='b' && $datos->fields['iva_tasa']==21)
       $cons_final_21+=($ratio_dolar*$datos->fields["total"])*(($datos->fields["iva_tasa"]/100)/(1+($datos->fields["iva_tasa"]/100)));
   if ($datos->fields['tipo_factura']=='b' && $datos->fields['iva_tasa']==10.5)
       $cons_final_105+=($ratio_dolar*$datos->fields["total"])*(($datos->fields["iva_tasa"]/100)/(1+($datos->fields["iva_tasa"]/100)));
   if ($datos->fields['tipo_factura']=='a')
      $neto_a+=($ratio_dolar*$datos->fields['total'])/(1+($datos->fields['iva_tasa']/100));
    if ($datos->fields['tipo_factura']=='b')
      $neto_b+=($ratio_dolar*$datos->fields['total'])/(1+($datos->fields['iva_tasa']/100));

   $datos->MoveNext();
  }//de while(!$datos_sueldos->EOF)
  
  //le damos formato a los numeros
  $resp_ins_21=number_format($resp_ins_21,2,'.','');
  $resp_ins_105=number_format($resp_ins_105,2,'.','');
  $cons_final_21=number_format($cons_final_21,2,'.','');
  $cons_final_105=number_format($cons_final_105,2,'.','');
  $neto_a=number_format($neto_a,2,'.','');
  $neto_b=number_format($neto_b,2,'.','');
 }//de if($id_asiento_venta=="")
 else 
 {
  $cartel="<br><B><center><font color='red'>ATENCION: El asiento para este período ya fue realizado</font></center></b>";	
  //si existe el nro_asiento, entonces llenamos las variables con valores
  //traidos desde la BD
  $resp_ins_21=number_format($asiento_info->fields["resp_inscripto_21"],2,'.','');
  $resp_ins_105=number_format($asiento_info->fields["resp_inscripto_105"],2,'.','');
  $cons_final_21=number_format($asiento_info->fields["cons_final_21"],2,'.','');
  $cons_final_105=number_format($asiento_info->fields["cons_final_105"],2,'.','');
  $neto_a=number_format($asiento_info->fields["neto_a"],2,'.','');
  $neto_b=number_format($asiento_info->fields["neto_b"],2,'.','');
  
  $actualizar=1;
 }//del else de if($nro_asiento=="")
 
}//de if($_POST["traer_datos"]=="Traer Datos")

echo $html_header;
?>
<script language="JavaScript" src="../../../lib/NumberFormat150.js"></script>
<script>

//funcion que calcula el iva debito fiscal
function calcular_deb_fiscal()
{
 var resp_ins_21;
 var resp_ins_105;
 var cons_final_21;
 var cons_final_105;
 
 resp_ins_21=parseFloat(document.all.resp_ins_21.value);
 resp_ins_105=parseFloat(document.all.resp_ins_105.value);
 cons_final_21=parseFloat(document.all.cons_final_21.value);
 cons_final_105=parseFloat(document.all.cons_final_105.value);
 
 document.all.suma_haber.value=formato_BD(resp_ins_21+ resp_ins_105+cons_final_21+cons_final_105);
}	

//funcion que calcula el total de ventas
function calcular_ventas()
{
 var neto_a;	
 var neto_b;

 neto_a=parseFloat(document.all.neto_a.value);
 neto_b=parseFloat(document.all.neto_b.value);
 
 document.all.ventas.value=formato_BD(neto_a+neto_b);
}	


function calcular_caja()
{
 var suma_haber;
 var ventas;
 
 suma_haber=parseFloat(document.all.suma_haber.value);
 ventas=parseFloat(document.all.ventas.value);
	
 document.all.caja.value=formato_BD(suma_haber+ventas);
}	


//funcion que controla que los campos obligatorios sean llenados
function control_campos()
{var msg;
 var faltan;
 faltan=0;
 msg="Faltan llenar los siguientes campos\n";
 msg+="-------------------------------------------\n";
 
 if(document.all.resp_ins_21.value=="")
 {faltan=1;
  msg+="Responsable Inscripto - 21%\n";
 }
 if(document.all.resp_ins_105.value=="")
 {faltan=1;
  msg+="Responsable Inscripto - 10.5%\n";
 }		
 if(document.all.cons_final_21.value=="")
 {faltan=1;
  msg+="Consumidor Final - 21%\n";
 }		
 if(document.all.cons_final_105.value=="")
 {faltan=1;
  msg+="Consumidor Final - 10.5%\n";
 }		
 if(document.all.neto_a.value=="")
 {faltan=1;
  msg+="Neto A\n";
 }
 if(document.all.neto_b.value=="")
 {faltan=1;
  msg+="Neto B\n";
 }
 
 if(faltan)
 {msg+="-------------------------------------------\n";
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
{document.all.resp_ins_21.readOnly=0;
 document.all.resp_ins_105.readOnly=0;
 document.all.cons_final_21.readOnly=0;
 document.all.cons_final_105.readOnly=0;
 document.all.neto_a.readOnly=0;
 document.all.neto_b.readOnly=0;
 
}

function deshabilitar_edicion()
{document.all.resp_ins_21.readOnly=1;
 document.all.resp_ins_105.readOnly=1;
 document.all.cons_final_21.readOnly=1;
 document.all.cons_final_105.readOnly=1;
 document.all.neto_a.readOnly=1;
 document.all.neto_b.readOnly=1;
}	

</script>
<?
if($actualizar)
{$query="select id_asiento_ventas,tipo,fecha,usuario 
 from log_asiento_ventas where id_asiento_ventas=$id_asiento_venta order by fecha DESC";
 $log_info=sql($query,"<br>Error al traer el log del asiento<br>") or fin_pagina();
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

if($msg=="")
 echo $cartel;
else 
 echo $msg; 
?>
<br>
<table align="center" width="95%" border="1">
 <tr>
  <td id="mo">
   <font size="3">Asiento de Ventas</font>
  </td>
 </tr>
</table>
<form name="form1" action="asiento_ventas.php" method="POST" <?=$disabled_form?>>
<input type="hidden" name="actualizar" value="<?=$actualizar?>">
<input type="hidden" name="id_asiento_venta" value="<?=$id_asiento_venta?>">
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
      <input type="submit" name="traer_datos" value="Traer Datos" 
          onclick="if(document.all.cambios.value==1)
                   {if(confirm('Ha realizado cambios en este Asiento de Ventas. Si continúa se perderán los cambios.\n¿Está Seguro que desea continuar?'))
                    {
                     document.all.actualizar.value=0; 
                     return true;
                    }
                    else
                     return false; 
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
    <font size='3' color='red'><b>Seleccione el período del asiento de ventas que desea completar y presione el botón traer datos</b></font>
   </td>
  </tr> 
  <?
 }
?> 
 <tr id="ma">
  <td width="10%">
   Cuenta
  </td>
  <td width="30%">
   Denominación
  </td>
  <td width="30%">
   Tasa
  </td>
  <td width="10%">
   Parcial
  </td>
  <td width="15%">
   DEBE
  </td>
  <td width="15%">
   HABER
  </td>
 </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      225
     </td>
     <td width="35%">
      <b>I.V.A. Débito Fiscal</b>
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="10%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="suma_haber" value="" readonly size="10">
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Responsable Inscripto
     </td>
     <td width="30%">
      21%
     </td>
     <td width="15%">
      <input type="text" name="resp_ins_21" readonly value="<?=$resp_ins_21?>"  size="10" onchange="calcular_deb_fiscal();calcular_caja();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>   
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Responsable Inscripto
     </td>
     <td width="30%">
      10.5%
     </td>
     <td width="15%">
      <input type="text" name="resp_ins_105" readonly value="<?=$resp_ins_105?>"  size="10" onchange="calcular_deb_fiscal();calcular_caja();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Consumidor Final
     </td>
     <td width="30%">
      21%
     </td>
     <td width="15%">
      <input type="text" name="cons_final_21"  readonly value="<?=$cons_final_21?>"  size="10" onchange="calcular_deb_fiscal();calcular_caja();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Consumidor Final
     </td>
     <td width="30%">
      10.5%
     </td>
     <td width="15%"> 
      <input type="text" name="cons_final_105" readonly value="<?=$cons_final_105?>"  size="10" onchange="calcular_deb_fiscal();calcular_caja();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      <b>VENTAS</b>
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="ventas" value="<?=$ventas?>"  size="10" onchange="hay_cambios();" readonly>
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      NETO "A"
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="neto_a" value="<?=$neto_a?>"  readonly size="10" onchange="calcular_ventas();calcular_caja();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      NETO "B"
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="neto_b" value="<?=$neto_b?>" readonly size="10" onchange="calcular_ventas();calcular_caja();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      <b>CAJA</b>
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="caja" value="<?=$caja?>"  size="10" readonly>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
</table>
<table align="center" border="1" width="95%">
 <tr>
   <?
  if($id_asiento_venta && permisos_check("inicio","permiso_boton_borrar_asientos"))
  {
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
   if(!permisos_check("inicio","permiso_boton_guardar_asiento_ventas"))
    $disabled_permiso="disabled";
   ?>
   <input type="submit" name="guardar" <?=$disabled_permiso?> <?=$disabled_form?> value="Guardar" onclick="return control_campos();">
  </td>
  <?
  if($actualizar)
  {
   $link_imprimir=encode_link("imprimir_ventas.php",array("id_asiento_venta"=>$id_asiento_venta));
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
//calculamos el total de debito fiscal
calcular_deb_fiscal();
//calculamos el total de venta
calcular_ventas();
//calculamos el total de caja
calcular_caja();
</script>

<br>
<?fin_pagina();?>