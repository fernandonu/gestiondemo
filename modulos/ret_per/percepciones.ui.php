<?
/*
Autor: GACZ
Creado: miercoles 18/05/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.4 $
$Date: 2005/07/08 21:47:05 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

//en este archivo se hacen las actualizacions y consultas para recuperar los datos
require("percepciones.db.php");
$r=sql($q) or fin_pagina();
?>
<script> 
//funcion que retorna un objeto de tipo retencion
//@i es la i-esima posicion en la lista (i=0...n)
function Percepcion(i)
{
	//NOTA: las entidades son proveedores
	
	var o=new Object();
	if (typeof eval("document.forms[0].hid_factura_"+i)!='undefined')
	{
		o.factura= new Object();
		o.factura.id=eval("parseInt(document.forms[0].hid_factura_"+i+".value)");
		o.factura.tipo=eval("document.all.span_tipofactura_"+i+".innerText");
		o.factura.numero=eval("document.all.span_nrofactura_"+i+".innerText");
		o.entidad=new Object();
		o.entidad.id=eval("parseInt(document.forms[0].hid_entidad_"+i+".value)");
		o.entidad.nombre=eval("document.all.span_nbreentidad_"+i+".innerText");
		o.entidad.cuit=eval("document.all.span_cuit_"+i+".value");
		o.distrito=new Object();
		o.distrito.id=eval("parseInt(document.forms[0].select_distrito_"+i+".value)");
		o.distrito.nombre=eval("document.all.span_nbredistrito_"+i+".innerText");
		o.iva=eval("parseFloat(document.forms[0].iva_"+i+".value)");
		o.ib=eval("parseFloat(document.forms[0].ib_"+i+".value)");
		o.ganancia=eval("parseFloat(document.forms[0].ganancia_"+i+".value)");
		o.fecha=eval("document.forms[0].fecha_"+i+".value");
		o.certificado=eval("document.forms[0].certificado_"+i+".value");
		o.selected=eval("document.forms[0].chk_"+i+".checked");
		
		//cambia los datos de la factura
		o.setFactura=function(id,tipo,nro)
		{
			this.factura.id=parseInt(id);
			this.factura.tipo=tipo;
			this.factura.numero=nro;
			eval("document.forms[0].hid_factura_"+i+".value="+this.factura.id);
			eval("document.all.span_tipofactura_"+i+".innerText='"+this.factura.tipo+"'");
			eval("document.all.span_nrofactura_"+i+".innerText='"+this.factura.numero+"'");
		};
		//cambia los datos de la entidad
		o.setEntidad=function(id,nbre,cuit)
		{
			this.entidad.id=parseInt(id);
			this.entidad.nombre=nbre;
			this.entidad.cuit=cuit;
			eval("document.forms[0].hid_entidad_"+i+".value=this.entidad.id");
			eval("document.all.span_nbreentidad_"+i+".innerText=this.entidad.nombre");
			eval("document.all.span_cuit_"+i+".innerText=this.entidad.cuit");			
		}
		//cambia los datos del distrito
		o.setDistrito=function(id,nbre)
		{
			this.distrito.id=parseInt(id);
			this.distrito.nombre=nbre;
			eval("document.forms[0].select_distrito_"+i+".value="+this.distrito.id);
			eval("document.all.span_nbredistrito_"+i+".innerText='"+this.distrito.nombre+"'");
		}
		o.setIva=function(iva_monto)
		{
			this.iva=parseFloat(iva_monto);
			eval("document.forms[0].iva_"+i+".value="+this.iva);
		}
		o.setIb=function(ib_monto)
		{
			this.ib=parseFloat(ib_monto);
			eval("document.forms[0].ib_"+i+".value="+this.ib);
		}
		o.setGanancia=function(ganancia_monto)
		{
			this.ganancia=parseFloat(ganancia_monto);
			eval("document.forms[0].ganancia_"+i+".value="+this.ganancia);
		}
		o.setFecha=function(fecha)
		{
			o.fecha=fecha;
			eval("document.forms[0].fecha_"+i+".value="+this.fecha);
		}
		o.setCertificado=function(certificado_nro)
		{
			o.certificado=certificado_nro;
			eval("document.forms[0].certificado_"+i+".value="+this.certificado);
		}
		//cambia la visibilidad y permisos de los controles para que el usuario pueda editar 
		o.editMode=function(boolvalue)
		{
			if (boolvalue)
			{
//				eval("document.all.td_cambiarfact_"+i+".style.display='inline'");
//				eval("document.all.td_cambiarfecha_"+i+".style.display='inline'");
//				eval("document.forms[0].fecha_"+i+".style.border='thin outset'");
//				eval("document.forms[0].fecha_"+i+".style.backgroundColor='white'");
//				eval("document.forms[0].fecha_"+i+".readOnly=false");
//				eval("document.forms[0].certificado_"+i+".readOnly=false");
//				eval("document.forms[0].certificado_"+i+".style.border='thin outset'");
//				eval("document.forms[0].certificado_"+i+".style.backgroundColor='white'");
//				eval("document.all.td_cambiarentidad_"+i+".style.display='inline'");
				eval("document.all.span_nbredistrito_"+i+".style.display='none'");
				eval("document.all.select_distrito_"+i+".style.display='inline'");
//				if (this.isNew())
//				{
//					eval("document.forms[0].iva_"+i+".style.backgroundColor='white'");
//					eval("document.forms[0].iva_"+i+".style.border='thin outset'");
//					eval("document.forms[0].iva_"+i+".readOnly=false");
//					eval("document.forms[0].ib_"+i+".style.backgroundColor='white'");
//					eval("document.forms[0].ib_"+i+".style.border='thin outset'");
//					eval("document.forms[0].ib_"+i+".readOnly=false");
//					eval("document.forms[0].ganancia_"+i+".style.backgroundColor='white'");
//					eval("document.forms[0].ganancia_"+i+".style.border='thin outset'");
//					eval("document.forms[0].ganancia_"+i+".readOnly=false");
//				}
			}
			else
			{
//				eval("document.all.td_cambiarfact_"+i+".style.display='none'");
//				eval("document.all.td_cambiarfecha_"+i+".style.display='none'");
//				eval("document.forms[0].fecha_"+i+".style.border='none'");
//				eval("document.forms[0].fecha_"+i+".style.backgroundColor='transparent'");
//				eval("document.forms[0].fecha_"+i+".readOnly=true");
//				eval("document.forms[0].certificado_"+i+".readOnly=true");
//				eval("document.forms[0].certificado_"+i+".style.border='none'");
//				eval("document.forms[0].certificado_"+i+".style.backgroundColor='transparent'");
//				eval("document.all.td_cambiarentidad_"+i+".style.display='none'");
				eval("document.all.span_nbredistrito_"+i+".style.display='inline'");
				eval("document.all.select_distrito_"+i+".style.display='none'");
//				if (this.isNew())
//				{
//					eval("document.forms[0].iva_"+i+".style.backgroundColor='transparent'");
//					eval("document.forms[0].iva_"+i+".style.border='none'");
//					eval("document.forms[0].iva_"+i+".readOnly=true");
//					eval("document.forms[0].ib_"+i+".style.backgroundColor='transparent'");
//					eval("document.forms[0].ib_"+i+".style.border='none'");
//					eval("document.forms[0].ib_"+i+".readOnly=true");
//					eval("document.forms[0].ganancia_"+i+".style.backgroundColor='transparent'");
//					eval("document.forms[0].ganancia_"+i+".style.border='none'");
//					eval("document.forms[0].ganancia_"+i+".readOnly=true");
//				}
			}
		}
		//me dice si es una nueva retencion o una que ya esta guardada
		//o.isNew=function() {return (typeof eval("document.forms[0].hnuevo_"+i)!='undefined')}
		//me dice si se cambio algun valor del objeto original
//		o.isModified=function ()
//		{ 
//			return (this.factura.id!=eval("parseInt(document.forms[0].hid_factura_"+i+".defaultValue)") ||
//			this.entidad.id!=eval("parseInt(document.forms[0].hid_entidad_"+i+".defaultValue)") ||
//			this.distrito.id!=eval("parseInt(document.forms[0].select_distrito_"+i+".defaultValue)") ||
//			this.fecha!=eval("document.forms[0].fecha_"+i+".defaultValue") ||
//			this.certificado!=eval("document.forms[0].certificado_"+i+".defaultValue") ||
//			this.iva!=eval("parseFloat(document.forms[0].iva_"+i+".defaultValue)") ||
//			this.ib!=eval("parseFloat(document.forms[0].ib_"+i+".defaultValue)") ||
//			this.ganancia!=eval("parseFloat(document.forms[0].ganancia_"+i+".defaultValue)"));
//		}
		return o;
	}
	else
		return null;//no se encontro el objeto con el indice @i
}
//funcion que sirve para cambiar a modo edicion en el chick del checkbox
function fnEdit(i)
{
	var opercepcion=new Percepcion(i);
	opercepcion.editMode(window.event.srcElement.checked);
}
function fnCambiarDistrito(index)
{
	var opercepcion=new Percepcion(index);
	var oselect=eval("document.forms[0].select_distrito_"+index);
	opercepcion.setDistrito(oselect.options[oselect.selectedIndex].value,oselect.options[oselect.selectedIndex].text);
}

</script>
<form name="form1" action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST" >
<br>
<?
echo "<table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"1\">
    <tr> 
      <td width=\"100%\" align=\"center\" valign=\"middle\" > ";
if (!$cerrada[$cmd] )	echo "<input name=\"bcerrar\" type=\"submit\" id=\"bcerrar\" style=\"width:125px\" value=\"Cerrar $cmd\" title=\"Cierra las $cmd ({$meses[intval($mes)]} {$anio})\" onclick=\"return (confirm('Seguro que desea cerrar las $cmd de {$meses[intval($mes)]} {$anio}'))\">&nbsp;";

echo  "<font size=\"2\"><strong>".strtoupper($cmd).": ver datos correspondientes a</strong></font>&nbsp;&nbsp;&nbsp;Mes &nbsp; ";
$select_mes->toBrowser();
echo "&nbsp; A&ntilde;o &nbsp;";
$select_anio->toBrowser();
echo "&nbsp;&nbsp; <input type=\"submit\" name=\"Submit\" value=\"Actualizar\"> &nbsp;&nbsp;";
    if ($cmd=="todas")
    	echo " <a title=\"Bajar datos {$meses[intval($mes)]} de $anio en un excel\" href=\"".encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,'mes'=>$mes,'anio'=>$anio))."\" ><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
echo " </td> </tr> </table>";
echo ($msg?"<br><center><b>$msg</b></center>":""); //si hay mensaje, lo muestra  
?>
<br>
<table id="tabla_retenciones" width="100%"  border="0" cellspacing="2" cellpadding="1">
  <tr align="center" id=mo>
    <td colspan="11">PERCEPCIONES (<?=$r->recordcount() ?>)(MES <?=strtoupper($meses[intval($mes)])?>, A&Ntilde;O <?=$anio ?>) </td>
    </tr>
  <tr id=mo>
    <td width="1%" nowrap>&nbsp;</td>  
   <td width="7%" nowrap>Factura<br>Tipo y N&ordm; </td>
    <td width="10%" nowrap>CUIT</td>
    <td width="1%" nowrap>Fecha</td>
    <td width="13%" nowrap>N&ordm; Cert / Fra</td>
    <td width="25%" nowrap>Nombre</td>
    <td width="5%" nowrap>IVA</td>
    <td width="7%" nowrap>Ing. Brutos </td>
    <td width="7%" nowrap>Ganancia</td>
    <td width="10%" nowrap>Provincia</td>
  </tr>
<?
 $select_distrito->style="display:none"; 
 $id_factura=0;//para imprimir los datos que se repiten (como iva,ganancia) una sola vez

 for ($i=0; !$r->EOF; $i++)
{
	$winentidad->url=encode_link('../modulo_clientes/nuevo_cliente.php',array('onclickelegir'=>"window.opener.fnCambiarEntidad($i)"));
?>
<!--  <tr class="<?=($i%2)?"impar":"par"?>">-->
  <tr bgcolor="<?=$bgcolor_out ?>">
    <input type="hidden" name="hid_percepcion_<?=$i?>" value="<?=$r->fields['id_percepcion']?>" >
    <input type="hidden" name="hid_factura_<?=$i?>" value="<?=$r->fields['id_factura']?>" >
    <input type="hidden" name="hid_entidad_<?=$i?>" value="<?=$r->fields['id_proveedor']?>" >
  <td <? if ($r->fields['mes_percepcion']=="") echo "style='background-color:#ffaaaa' title='No pertenece a ningun mes de percepciones (requiere guardar)'"; ?>>
    <input type="checkbox" name="chk_<?=$i?>" value="1" onclick="//fnEdit(<?=$i?>)" <? if ($cerrada[$cmd]) echo "disabled" ?>>
  </td>
 <td>
		<table width="100%" border="0" cellspacing="2" cellpadding="0">
      <tr>
        <td id="td_cambiarfact_<?=$i?>" style="display:none" width="5%"><input name="bcambiarfact" type="button" value="C" title="Cambiar Factura"></td>
        <td nowrap width="95%"><span id="span_tipofactura_<?=$i?>"><?=$r->fields['tipo_fact']?></span> <span id="span_nrofactura_<?=$i?>"><?=$r->fields['nro_factura']?></span></td>
      </tr>
    </table>    
 </td> 
    <td nowrap><span id="span_cuit_<?=$i?>"><?=$r->fields['cuit']?></span></td>
    <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="95%"><input name="fecha_<?=$i?>" type="text" class="read" style="width:100%;text-align:center" value="<?= date2(false,$r->fields['fecha']) ?>" size="10" maxlength="10" readonly></td>
        <td width="5%" id="td_cambiarfecha_<?=$i?>" style="display:none">
        <!--<img src=../../imagenes/cal.gif border=0 style='cursor:hand;' alt='Haga click aqui para seleccionar la fecha'  onClick="popUpCalendar(this.parentNode.parentNode, document.forms[0].fecha_<?=$i?>, 'dd/mm/yyyy');">-->
        </td>
      </tr>
    </table></td>
    <td><input name="certificado_<?=$i?>" type="text" class="read" size="14" readonly  value="<?=$r->fields['nro_certificado']?>" ></td>
    <td width="20%"><table width="100%"  border="0" cellspacing="2" cellpadding="0">
      <tr>
        <td id="td_cambiarentidad_<?=$i?>" style="display:none" width="5%"><input name="bcambiar_entidad" type="button" value="C" title="Cambiar"  onclick="<?=$winentidad->open() ?>"></td>
        <td width="95%"><span id="span_nbreentidad_<?=$i?>"><?=$r->fields['razon_social']?></span></td>
      </tr>
    </table></td>
    <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="12%" align="left">$</td>
        <td width="88%" align="right"><input name="iva_<?=$i?>" type="text" class="read" readonly style="text-align:right;width:auto" size="8" value="<?= formato_money($id_factura==$r->fields['id_factura']?0:$r->fields['iva_monto'])?>" ></td>
      </tr>
    </table></td>
    <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="16%" align="left">$</td>
        <td width="84%" align="right"><input name="ib_<?=$i?>" type="text" class="read" readonly style="text-align:right;width:auto" size="8" value="<?=formato_money($r->fields['ib_monto'])?>"></td>
      </tr>
    </table></td>
    <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="12%" align="left">$</td>
        <td width="88%" align="right"><input name="ganancia_<?=$i?>" type="text" class="read" readonly style="text-align:right;width:auto" size="10" value="<?=formato_money($id_factura==$r->fields['id_factura']?0:$r->fields['ganancia_monto'])?>"></td>
      </tr>
    </table></td>
    <td>
    <? 
    	$select_distrito->name="select_distrito_$i";
    	$select_distrito->selectedIndex=-1; 
    	$select_distrito->setSelected($r->fields['id_distrito']) ;
    	$select_distrito->set_attribute("onchange","fnCambiarDistrito($i)");
    	echo $select_distrito->toBrowser(); ?>
    <span id="span_nbredistrito_<?=$i?>" ><?=$r->fields['distrito']?></span>
		</td>
  </tr>
<?
		//para identificar las facturas ya repetidas
		$id_factura=$r->fields['id_factura'];
		$r->movenext();
 	} 
?>
</table>
<br />
<table width="100%"  border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="152" align="center">&nbsp;</td>
    <td width="182" align="center"><b>Cambiar seleccion al mes</b>
      <table width="42%"  border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td width="22%" align="right">Mes</td>
    <td width="78%">
<? 
$select_mes->name='select_mes_cambiar';
$select_mes->disabled=$cerrada[$cmd];
$select_mes->toBrowser();
?>    
	</td>
  </tr>
  <tr>
    <td align="right">A&ntilde;o</td>
    <td>
<?    
$select_anio->name='select_anio_cambiar';
$select_anio->disabled=$cerrada[$cmd];
$select_anio->toBrowser();
?>
	</td>
  </tr>
</table>         </td>

    <td width="630" valign="bottom">
		<input name="bcambiar_mes" type="submit" style="width:110px " value="Cambiar mes" onClick="return confirm('Confirme que desea cambiar los elementos seleccionados a: \n\n\tMes: '+ forms[0].select_mes_cambiar.options[forms[0].select_mes_cambiar.options.selectedIndex].text+'\n\tAño: '+forms[0].select_anio_cambiar.options[forms[0].select_anio_cambiar.options.selectedIndex].text)" <? if ($cerrada[$cmd]) echo "disabled" ?>>
	&nbsp;&nbsp;
    <input name="bguardar" type="submit" style="width:120px " value="Guardar" title="Guarda TODAS las Percepciones mostradas en el mes actual" <? if ($cerrada[$cmd]) echo "disabled" ?>>
		</td>
  </tr>
</table>
<input type="hidden" name="percepciones" value="1">
<input type="hidden" name="htotal_percepciones" value="<?=$i?>">
</form>
<br>