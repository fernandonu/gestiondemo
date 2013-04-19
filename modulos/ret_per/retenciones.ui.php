<?
/*
Autor: GACZ
Creado: jueves 05/05/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.4 $
$Date: 2005/06/21 20:11:29 $
*/
require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

//en este archivo se hacen las actualizacions y consultas para recuperar los datos
require("retenciones.db.php");
$r=sql($q) or fin_pagina();
?>
<script> 
//funcion que retorna un objeto de tipo retencion
//@i es la i-esima posicion en la lista (i=0...n)
function Retencion(i)
{
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
		o.distrito.id=eval("parseInt(document.forms[0].hid_distrito_"+i+".value)");
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
			eval("document.forms[0].hid_factura_"+i+".value=this.factura.id");
			eval("document.all.span_tipofactura_"+i+".innerText=this.factura.tipo+' '");
			eval("document.all.span_nrofactura_"+i+".innerText=this.factura.numero");
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
			eval("document.forms[0].hid_distrito_"+i+".value="+this.distrito.id);
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
				eval("document.all.td_cambiarfact_"+i+".style.display='inline'");
				eval("document.all.td_cambiarfecha_"+i+".style.display='inline'");
				eval("document.forms[0].fecha_"+i+".style.border='thin outset'");
				eval("document.forms[0].fecha_"+i+".style.backgroundColor='white'");
				eval("document.forms[0].fecha_"+i+".readOnly=false");
				eval("document.forms[0].certificado_"+i+".readOnly=false");
				eval("document.forms[0].certificado_"+i+".style.border='thin outset'");
				eval("document.forms[0].certificado_"+i+".style.backgroundColor='white'");
				eval("document.all.td_cambiarentidad_"+i+".style.display='inline'");
				eval("document.all.span_nbredistrito_"+i+".style.display='none'");
				eval("document.all.select_distrito_"+i+".style.display='inline'");
				if (this.isNew())
				{
					eval("document.forms[0].iva_"+i+".style.backgroundColor='white';"
					+"document.forms[0].iva_"+i+".style.border='thin outset';"
//					+"document.forms[0].iva_"+i+".value=document.forms[0].iva_"+i+".value.replace(',','.');"
					+"document.forms[0].iva_"+i+".readOnly=false;"
					+"document.forms[0].ib_"+i+".style.backgroundColor='white';"
					+"document.forms[0].ib_"+i+".style.border='thin outset';"
//					+"document.forms[0].ib_"+i+".value=document.forms[0].ib_"+i+".value.replace(',','.');"
					+"document.forms[0].ib_"+i+".readOnly=false;"
					+"document.forms[0].ganancia_"+i+".style.backgroundColor='white';"
					+"document.forms[0].ganancia_"+i+".style.border='thin outset';"
//					+"document.forms[0].ganancia_"+i+".value=document.forms[0].ganancia_"+i+".value.replace(',','.');"
					+"document.forms[0].ganancia_"+i+".readOnly=false");
				}
			}
			else
			{
				eval("document.all.td_cambiarfact_"+i+".style.display='none'");
				eval("document.all.td_cambiarfecha_"+i+".style.display='none'");
				eval("document.forms[0].fecha_"+i+".style.border='none'");
				eval("document.forms[0].fecha_"+i+".style.backgroundColor='transparent'");
				eval("document.forms[0].fecha_"+i+".readOnly=true");
				eval("document.forms[0].certificado_"+i+".readOnly=true");
				eval("document.forms[0].certificado_"+i+".style.border='none'");
				eval("document.forms[0].certificado_"+i+".style.backgroundColor='transparent'");
				eval("document.all.td_cambiarentidad_"+i+".style.display='none'");
				eval("document.all.span_nbredistrito_"+i+".style.display='inline'");
				eval("document.all.select_distrito_"+i+".style.display='none'");
				if (this.isNew())
				{
					eval("document.forms[0].iva_"+i+".style.backgroundColor='transparent'");
					eval("document.forms[0].iva_"+i+".style.border='none'");
					eval("document.forms[0].iva_"+i+".readOnly=true");
					eval("document.forms[0].ib_"+i+".style.backgroundColor='transparent'");
					eval("document.forms[0].ib_"+i+".style.border='none'");
					eval("document.forms[0].ib_"+i+".readOnly=true");
					eval("document.forms[0].ganancia_"+i+".style.backgroundColor='transparent'");
					eval("document.forms[0].ganancia_"+i+".style.border='none'");
					eval("document.forms[0].ganancia_"+i+".readOnly=true");
				}
			}
		}
		//me dice si es una nueva retencion o una que ya esta guardada
		o.isNew=function() {return (typeof eval("document.forms[0].hnuevo_"+i)!='undefined')}
		//me dice si se cambio algun valor del objeto original
		o.isModified=function ()
		{ 
			return (this.factura.id!=eval("parseInt(document.forms[0].hid_factura_"+i+".defaultValue)") ||
			this.entidad.id!=eval("parseInt(document.forms[0].hid_entidad_"+i+".defaultValue)") ||
			this.distrito.id!=eval("parseInt(document.forms[0].hid_distrito_"+i+".defaultValue)") ||
			this.fecha!=eval("document.forms[0].fecha_"+i+".defaultValue") ||
			this.certificado!=eval("document.forms[0].certificado_"+i+".defaultValue") ||
			this.iva!=eval("parseFloat(document.forms[0].iva_"+i+".defaultValue)") ||
			this.ib!=eval("parseFloat(document.forms[0].ib_"+i+".defaultValue)") ||
			this.ganancia!=eval("parseFloat(document.forms[0].ganancia_"+i+".defaultValue)"));
		}
		return o;
	}
	else
		return null;//no se encontro el objeto con el indice @i
}
//funcion que sirve para cambiar a modo edicion en el chick del checkbox
function fnEdit(i)
{
	var oretencion=new Retencion(i);
	oretencion.editMode(window.event.srcElement.checked);
}
function fnEditMode(boolvalue)
{
	var totalret=parseInt(document.forms[0].htotal_retenciones.value);
	for (var i=0; i < totalret; i++)
	{
		var chk;
		if (typeof eval("chk=document.forms[0].chk_"+i)!='undefined' && chk.checked)
		{
			var oretencion=new Retencion(i);
			oretencion.editMode(true);
		}
	}
}
//variable global que indica si se esta editando
var EDITING=false;

function fnAddProd()
{
	var totalret=parseInt(document.forms[0].htotal_retenciones.value)+1;
	document.forms[0].htotal_retenciones.value=totalret--;
	var otabla=document.all.tabla_retenciones;
	var fila=otabla.insertRow(otabla.rows.length);
	fila.style.backgroundColor="<?=$bgcolor_out ?>";
	//fila.className=((totalret--)%2)?'par':'impar';
	var cell=fila.insertCell(0);
  cell.align='center';
//  cell.colSpan=9;
 	cell.innerHTML=
 	'<input type="checkbox" name="chk_'+totalret+'" value="1" checked onclick="fnEdit('+totalret+')">'
 	+'<input type="hidden" name="hid_retencion_'+totalret+'">'
 	+'<input type="hidden" name="hid_distrito_'+totalret+'">'
  +'<input type="hidden" name="hid_factura_'+totalret+'">'
  +'<input type="hidden" name="hnuevo_'+totalret+'" value=1>' //sirve para saber si se pueden editar los campos de montos
  +'<input type="hidden" name="hid_entidad_'+totalret+'">';
  cell=fila.insertCell(1);
  var url="<?=$winfactura->url=encode_link('facturas.lista.ui.php',array())?>"; 
  //contiene el window.open para llamar a la ventana de facturas.lista
  var openwin="<?=$winfactura->open();?>";
  openwin=openwin.replace(url,url+"&onrowclick=window.opener.fnCambiarFactura("+totalret+")");
  cell.innerHTML=
	'<table width="100%" border="0" cellspacing="2" cellpadding="0">'
  +'<tr>'
  +'<td id="td_cambiarfact_'+totalret+'" width="5%"><input name="bcambiarfact_'+totalret+'" type="button" value="C" title="Cambiar Factura" onclick="'+openwin+'" ></td>'
  +'<td nowrap align="center" width="95%"><font color="#FF0000" title="No se olvide de Guardar"><span id=span_tipofactura_'+totalret+'></span> <span id="span_nrofactura_'+totalret+'">NUEVO</span></font></td>'
  +'</tr>'
  +'</table>';
  cell=fila.insertCell(2);
	cell.noWrap=true;
	cell.innerHTML='<span id="span_cuit_'+totalret+'"></span>';
  cell=fila.insertCell(3);
	cell.innerHTML=
	'<table width="100%" border="0" cellpadding="1" cellspacing="0">'
  +'<tr>'
  +'<td width="5%" align="left"><input name="fecha_'+totalret+'" type="text" style="width:100%;text-align:center" value="<?=$fecha_hoy ?>" size="10" maxlength="10"></td>'
	+'<td width="95%" id="td_cambiarfecha_'+totalret+'" align="right">'//<img src="../../imagenes/cal.gif" border=0 style="cursor:hand;" alt="Haga click aqui para seleccionar la fecha"  onClick="popUpCalendar(this.parentNode.parentNode, fecha_'+totalret+', \'dd/mm/yyyy\');">
	+'</td>'
	+'</tr>'
  +'</table>';
  cell=fila.insertCell(4);
	cell.innerHTML='<input name="certificado_'+totalret+'" type="text" class="write" size="14">';
  cell=fila.insertCell(5);
	url="<?=$winentidad->url=encode_link('../modulo_clientes/nuevo_cliente.php',array())?>"; 
  //contiene el window.open para llamar a la ventana de entidades
	openwin="<?=$winentidad->open();?>";
	openwin=openwin.replace(url,url+"&onclickelegir=window.opener.fnCambiarEntidad("+totalret+")");
	cell.innerHTML=
	'<table width="100%"  border="0" cellspacing="2" cellpadding="0">'
	+'<tr>'
  	+'<td id="td_cambiarentidad_'+totalret+'" width="5%"><input name="bcambiar_entidad_'+totalret+'" type="button" value="C" title="Cambiar" onclick="'+openwin+'" ></td>'
	+'<td width="95%"><span id="span_nbreentidad_'+totalret+'"></span></td>'
	+'</tr>'
	+'</table>';
	cell=fila.insertCell(6);
 	cell.innerHTML=
 	'<table width="100%" border="0" cellpadding="1" cellspacing="0">'
	+'<tr>'
  	+'<td width="12%" align="left">$</td>'
	+'<td width="88%" align="right"><input name="iva_'+totalret+'" type="text" class="write" style="text-align:right;width:auto" size="6" value=0></td>'
	+'</tr>'
	+'</table>';
	cell=fila.insertCell(7);
 	cell.innerHTML=
	'<table width="100%" border="0" cellpadding="1" cellspacing="0">'
  	+'<tr>'
  	+'<td width="16%" align="left">$</td>'
	+'<td width="84%" align="right"><input name="ib_'+totalret+'" type="text" class="write" style="text-align:right;width:auto" size="6" value=0></td>'
	+'</tr>'
  	+'</table>';
	cell=fila.insertCell(8);
 	cell.innerHTML=
	'<table width="100%" border="0" cellpadding="1" cellspacing="0">'
	+'<tr>'
	+'<td width="16%" align="left">$</td>'
	+'<td width="84%" align="right"><input name="ganancia_'+totalret+'" type="text" class="write" style="text-align:right;width:auto" size="10" value=0></td>'
	+'</tr>'
	+'</table>';
	cell=fila.insertCell(9);
 	cell.innerHTML= 
 	'<select name="select_distrito_'+totalret+'" onchange="fnCambiarDistrito('+totalret+')">'
 	+document.forms[0].select_distrito_0.innerHTML
 	+'</select>'
 	+'<span id="span_nbredistrito_'+totalret+'" style="display:none"></span>';
 	
 	eval("document.forms[0].select_distrito_"+totalret+".selectedIndex=0");
}

//esto intercambia las posiciones de los objetos en juego
//document.forms[0].select_distrito_0.swapNode(document.forms[0].chk_0);

//Esta funcion clona un objeto, NO SE ESTA USANDO
function clone(myObj)
{
	if(typeof(myObj) != 'object') return myObj;
	if(myObj == null) return myObj;

	var myNewObj = new Object();

	for(var i in myObj)
		myNewObj[i] = clone(myObj[i]);

	return myNewObj;
}


var winentidad;
//esta funcion se llama desde la ventana hijo de entidades
function fnCambiarEntidad(index)
{
	var o_retencion=Retencion(index);
	if (o_retencion!=null)
		o_retencion.setEntidad(winentidad.document.all.select_entidad.value,winentidad.document.all.nombre.value,winentidad.document.all.cuit.value);
	else
		alert('Error de indice de retencion ('+index+')');
	winentidad.close();
}

var winfactura;
function fnCambiarFactura(index)
{
	var o_retencion=Retencion(index);
	if (o_retencion!=null)
	{
//		alert(winfactura.document.all.id_factura.value+" "+winfactura.document.all.tipo_factura.value+" "+winfactura.document.all.nro_factura.value);
		o_retencion.setFactura(winfactura.document.all.id_factura.value,winfactura.document.all.tipo_factura.value.toUpperCase(),winfactura.document.all.nro_factura.value);
	}
	else
		alert('Error de indice de retencion ('+index+')');
	winfactura.close();
}

function fnCambiarDistrito(index)
{
	var oretencion=new Retencion(index);
	var oselect=eval("document.forms[0].select_distrito_"+index);
	oretencion.setDistrito(oselect.options[oselect.selectedIndex].value,oselect.options[oselect.selectedIndex].text);
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
    <td colspan="11">RETENCIONES (<?=$r->recordcount() ?>)(MES <?=strtoupper($meses[intval($mes)])?>, A&Ntilde;O <?=$anio ?>) </td>
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
for ($i=0; !$r->EOF; $i++)
{
	$winentidad->url=encode_link('../modulo_clientes/nuevo_cliente.php',array('onclickelegir'=>"window.opener.fnCambiarEntidad($i)"));
	$winfactura->url=encode_link('facturas.lista.ui.php',array('onrowclick'=>"window.opener.fnCambiarFactura($i)"));
?>
<!--  <tr class="<?=($i%2)?"impar":"par"?>">-->
  <tr bgcolor="<?=$bgcolor_out ?>" >
    <td align="center">
    <input type="checkbox" name="chk_<?=$i?>" value="1"  onclick="fnEdit(<?=$i?>)" <? if ($cerrada[$cmd]) echo "disabled" ?> >
    <input type="hidden" name="hid_retencion_<?=$i?>" value="<?=$r->fields['id_retencion']?>" >
    <input type="hidden" name="hid_distrito_<?=$i?>" value="<?=$r->fields['id_distrito']?>" >
    <input type="hidden" name="hid_ingreso_egreso_<?=$i?>" value="<?=$r->fields['id_ingreso_egreso']?>" >
    <input type="hidden" name="hid_factura_<?=$i?>" value="<?=$r->fields['id_factura']?>" >
    <input type="hidden" name="hid_entidad_<?=$i?>" value="<?=$r->fields['id_entidad']?>" >
<? if ($r->fields['fecha_nuevo']) echo "<input type='hidden' name='hnuevo_$i' value=1 >\n" ?>
    </td>
 <td>
		<table width="100%" border="0" cellspacing="2" cellpadding="0">
      <tr>
        <td id="td_cambiarfact_<?=$i?>" style="display:none" width="5%"><input name="bcambiarfact" type="button" value="C" title="Cambiar Factura" onclick="<?= $winfactura->open() ?>"></td>
        <td nowrap width="95%"><span id="span_tipofactura_<?=$i?>"><?=$r->fields['tipo_factura']?></span> <span id="span_nrofactura_<?=$i?>"><?=$r->fields['nro_factura']?></span></td>
      </tr>
    </table>    
 </td> 
    <td nowrap title="CUIT"><span id="span_cuit_<?=$i?>"><?=$r->fields['cuit']?></span></td>
    <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="95%"><input name="fecha_<?=$i?>" type="text" class="read" onfocus="if (!this.readOnly) this.select()" style="width:100%;text-align:center" value="<?= date2(false,$r->fields['fecha']) ?>" size="10" maxlength="10" readonly></td>
        <td width="5%" id="td_cambiarfecha_<?=$i?>" style="display:none">
        <!--<img src=../../imagenes/cal.gif border=0 style='cursor:hand;' alt='Haga click aqui para seleccionar la fecha'  onClick="popUpCalendar(this.parentNode.parentNode, document.forms[0].fecha_<?=$i?>, 'dd/mm/yyyy');">-->
        </td>
      </tr>
    </table></td>
    <td><input name="certificado_<?=$i?>" type="text" class="read" size="14" readonly value="<?=$r->fields['nro_certificado']?>"></td>
    <td width="20%"><table width="100%"  border="0" cellspacing="2" cellpadding="0">
      <tr title="Entidad">
        <td id="td_cambiarentidad_<?=$i?>" style="display:none" width="5%"><input name="bcambiar_entidad" type="button" value="C" title="Cambiar"  onclick="<?=$winentidad->open() ?>"></td>
        <td width="95%"><span id="span_nbreentidad_<?=$i?>"><?=$r->fields['entidad']?></span></td>
      </tr>
    </table></td>
    <td title="IVA" ><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="12%" align="left">$</td>
        <td width="88%" align="right"><input name="iva_<?=$i?>" type="text" class="read" readonly style="text-align:right;width:auto" size="8" value="<?=number_format($r->fields['iva_monto'],2,".","")?>" ></td>
      </tr>
    </table></td>
    <td title="Ingresos Brutos"><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="16%" align="left">$</td>
        <td width="84%" align="right"><input name="ib_<?=$i?>" type="text" class="read" readonly style="text-align:right;width:auto" size="8" value="<?=number_format($r->fields['ib_monto'],2,".","")?>"></td>
      </tr>
    </table></td>
    <td title="Ganancia"><table width="100%" border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td width="12%" align="left">$</td>
        <td width="88%" align="right"><input name="ganancia_<?=$i?>" type="text" class="read" readonly style="text-align:right;width:auto" size="10" value="<?=number_format($r->fields['ganancia_monto'],2,".","")?>"></td>
      </tr>
    </table></td>
    <td title="Provincia">
    <? $select_distrito->name="select_distrito_$i";$select_distrito->selectedIndex=-1; $select_distrito->style="display:none";  $select_distrito->setSelected($r->fields['id_distrito']); $select_distrito->add_event('onchange',"fnCambiarDistrito($i)"); echo $select_distrito->toBrowser(); ?>
    <span id="span_nbredistrito_<?=$i?>" ><?=$r->fields['distrito']?></span>
		</td>
  </tr>
<?
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
	<input name="bnuevo" type="button" style="width:110px " value="Nuevo" onClick="fnAddProd()" <? if ($cerrada[$cmd]) echo "disabled" ?>>
	&nbsp;&nbsp;
    <input name="bguardar" type="submit" style="width:120px " value="Guardar Cambios" <? if ($cerrada[$cmd]) echo "disabled" ?>></td>
  </tr>
</table>
<input type="hidden" name="retenciones" value="1">
<input type="hidden" name="htotal_retenciones" value="<?=$i?>">
<script>
document.onkeypress=function(){if (event.keyCode==13) return false};
</script>
</form>
<br>