<?
/*
MODIFICADA POR
$Author: cestila $
$Revision: 1.12 $
$Date: 2006/05/03 20:58:46 $
*/

require_once("../../config.php");
$maquina=$_GET['serie'];

$q="select ensamblador.nombre as ensamblador,entidad.id_entidad,entidad.telefono,fecha_inicio,distrito.nombre as provincia,entidad.direccion,entidad.nombre,nro_serie,nro_orden,id_licitacion,desc_prod as modelo from maquina join orden_de_produccion using(nro_orden) join ensamblador using(id_ensamblador) left join entidad using(id_entidad) left join distrito using(id_distrito) where maquina.nro_serie ilike '%$maquina%' order by maquina.nro_serie";
$resultado_maquina=sql($q) or fin_pagina();

?>
<head>
<title>Clientes</title>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<?=$html_header?>
</head>
<body topmargin="0">
<script>
var dependencias=new Array();
<?
while (!$resultado_maquina->EOF)
{
$q2="select id_fila,filas_ord_prod.descripcion,productos.id_producto from filas_ord_prod join productos USING (id_producto) join general.tipos_prod using(id_tipo_prod) where codigo='garantia' and nro_orden=".$resultado_maquina->fields["nro_orden"];
if ($resultado_maquina->fields["nro_orden"]) $garantia=sql($q2) or fin_pagina();
$dias=diferencia_dias(Fecha($resultado_maquina->fields['fecha_inicio']),date ("d-m-Y"));
$d=0;
if ($garantia->fields["id_producto"]==76) $d=730;
if ($garantia->fields["id_producto"]==78 || $garantia->fields["id_producto"]==513) $d=548;
if ($garantia->fields["id_producto"]==77) $d=1095;
if ($garantia->fields["id_producto"]==75) $d=365;
if ($garantia->fields["id_producto"]==369) $d=183;
//echo $garantia->fields["id_fila"];
$texto="&nbsp;";
$color_garantia="Blue";
if ($d and ($d<$dias)) {
	$texto="Maquina fuera de garantia";
	$color_garantia="#CC0000";
}
else {
	if ($d) {
		$texto="Maquina en garantia";
		$color_garantia="#00CC00";
	}
}
?>
var dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["dependencia"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_dependencia"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["cp"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["mail"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["telefono"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["direccion"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["lugar"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_distrito"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["contacto"]=new Array();
dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["comentario"]=new Array();
var maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>=new Array();
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["color_garantia"]="<?php echo $color_garantia;?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["texto_garantia"]="<?php echo $texto;?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nro_serie"]="<?php if($resultado_maquina->fields["nro_serie"]){echo $resultado_maquina->fields["nro_serie"];} ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nro_orden"]="<?php echo $resultado_maquina->fields["nro_orden"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["ensamblador"]="<?php echo $resultado_maquina->fields["ensamblador"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_licitacion"]="<?php if($resultado_maquina->fields["id_licitacion"]){echo $resultado_maquina->fields["id_licitacion"];}else echo "No se encuentra asociada a ninguna licitacion";?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_licitacion_ref"]="<?php if($resultado_maquina->fields["id_licitacion"]){echo encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$resultado_maquina->fields["id_licitacion"]));}else echo "No se encuentra asociada a ninguna licitacion";?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["modelo"]="<?php echo $resultado_maquina->fields["modelo"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["cliente"]="<?php echo $resultado_maquina->fields["nombre"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["garantia"]="<?php echo $garantia->fields["descripcion"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["provincia"]="<?php echo $resultado_maquina->fields["provincia"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["direccion"]="<?php echo $resultado_maquina->fields["direccion"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["telefono"]="<?php echo $resultado_maquina->fields["telefono"]; ?>";
maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_entidad"]="<?php echo $resultado_maquina->fields["id_entidad"]; ?>";
<?
if ($resultado_maquina->fields["id_entidad"]!="")
{$sql1="select * from dependencias where id_entidad=".$resultado_maquina->fields["id_entidad"];
 $sql1.=" order by dependencia";
 $dependencias=sql($sql1) or fin_pagina();
$i=0;
while(!$dependencias->EOF)
  {
?>
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["dependencia"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["dependencia"]); ?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_dependencia"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["id_dependencia"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["telefono"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["telefono"])?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["cp"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["cp"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["direccion"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["direccion"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["mail"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["mail"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["lugar"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["lugar"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_distrito"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["id_distrito"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["contacto"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["contacto"]);?>";
    dependencias_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["comentario"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["comentario"]);?>";
<?
  $i++;
  $dependencias->MoveNext();
  }
}
$resultado_maquina->MoveNext();
}
?>
function cargar_ventana_produccion()
{
	if (document.all.nro_orden.value<1141){
		window.open('../ordprod/ordenes_nueva_gestion_2.php?nro_orden='+document.all.nro_orden.value+'&modo=modificar','','menubar=1,toolbar=1,resizable=1,location=1,scrollbars=1');
	}
	else{
		window.open('../ordprod/ordenes_nueva.php?nro_orden='+document.all.nro_orden.value+'&modo=modificar','','menubar=1,toolbar=1,resizable=1,location=1,scrollbars=1');		
	}
}

function cargar_ventana_licitacion()
{window.open(document.all.ref_licitacion.value,'','menubar=1,toolbar=1,resizable=1,location=1,scrollbars=1');
}

var info=new Array();
function set_datos()
{var i;
	switch(document.all.select_maquina.options[document.all.select_maquina.selectedIndex].value)
	{<?PHP
	 $resultado_maquina->Move(0);
	 while(!$resultado_maquina->EOF)
	 {?>
	  case '<? echo $resultado_maquina->fields["nro_serie"]?>':info=maquina_<? echo $resultado_maquina->fields["nro_serie"];?>;
	                                                           dependencias['dependencia']=new Array();
	                                                           dependencias['id_dependencia']=new Array();
	                                                           dependencias['telefono']=new Array();
	                                                    	   dependencias['cp']=new Array();
	                                                    	   dependencias['mail']=new Array();
	                                                    	   dependencias['direccion']=new Array();
	                                                    	   dependencias['id_distrito']=new Array();
	                                                    	   dependencias['lugar']=new Array();
	                                                           dependencias['contacto']=new Array();
	                                                           dependencias['comentario']=new Array();
	                                                           i=0;
	                                                           while (i<dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['dependencia'].length)
	                                                     		{dependencias['dependencia'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['dependencia'][i];
	                                                      		dependencias['id_dependencia'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['id_dependencia'][i];
	                                                      		dependencias['telefono'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['telefono'][i];
	                                                      		dependencias['cp'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['cp'][i];
	                                                      		dependencias['mail'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['mail'][i];
	                                                      		dependencias['id_distrito'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['id_distrito'][i];
	                                                      		dependencias['direccion'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['direccion'][i];
	                                                      		dependencias['lugar'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['lugar'][i];
	                                                      		dependencias['contacto'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['contacto'][i];
	                                                      		dependencias['comentario'][i]=dependencias_<? echo $resultado_maquina->fields["nro_serie"]; ?>['comentario'][i];
	                                                      		i++;
	                                                     		}
	                                                          break;
	 <?
	  $resultado_maquina->MoveNext();
	 }
	 ?>
	}
//	alert (texto_garantia.innerHTML);
	texto_garantia.innerHTML="<font size=4 color='"+info["color_garantia"]+"'><b>"+info["texto_garantia"]+"</b></font>";
	document.all.nro_orden.value=info["nro_orden"];
	document.all.cargar1.disabled=false;
	document.all.cargar1.onclick=cargar_ventana_produccion;
	document.all.licitacion.value=info["id_licitacion"];
	if(info['id_licitacion_ref']!="No se encuentra asociada a ninguna licitacion")
	 {document.all.ref_licitacion.value=info['id_licitacion_ref'];
	  document.all.cargar2.disabled=false;
	  document.all.cargar2.onclick=cargar_ventana_licitacion;
	 }
	else
	{document.all.cargar2.disabled=true;
	 document.all.cargar2.onclick="";
	}
	document.all.modelo.value=info["modelo"];
	document.all.nro_serie.value=info["nro_serie"];
	document.all.ensamblador.value=info["ensamblador"];
	document.all.entidad.value=info["cliente"];
	document.all.garantia.value=info["garantia"];
	document.all.provincia.value=info["provincia"];
	document.all.direccion.value=info["direccion"];
	document.all.telefono.value=info["telefono"];
	document.all.id_entidad.value=info["id_entidad"];
	//alert(document.all.nro_orden.onclick);
} //fin de la funcion set_datos()


function borrar_buffer(){
   //inicializa la cadena buscada
	cadena="";
	puntero=0;
}
</script>
<?
$link=encode_link("caso_elegir_maquina.php",array('onclickcargar'=>$parametros['onclickcargar'],'onclicksalir'=>$parametros['onclicksalir']));
?>
<form name="form1" method="post" action="<? echo $link; ?>" target="_parent">
<input type="hidden" name="telefono">
<input type="hidden" name="id_entidad">
<input type="hidden" name="ref_licitacion">
<table width=100% align=center border=0>
<tr>
  <td id=mo align="center" width="30%">Selección de Maquinas</td>
 <td id=ma align=center width="70%"><b>Datos de la maquina</td>
</tr>
<tr>
<td align=center>
 <select name="select_maquina" size="15" style="width:100%" onchange="set_datos();">
	  <?
	  $resultado_maquina->Move(0);
	  while (!$resultado_maquina->EOF)
	  {
	  ?>
			<option value="<?=$resultado_maquina->fields['nro_serie']; ?>"><?=$resultado_maquina->fields['nro_serie']; ?></option>
			<?
	 $resultado_maquina->MoveNext();
	  }
	 ?>
  </select>
</td>
<td width=25% valign="top">
<table>
<tr>
<td align=center>
<div class='texto_garantia' id='texto_garantia' height=20 width=100 style="border-style:none;background-color:'transparent';"><font size=4><b>&nbsp;</b></font></div>
<!--<input type="text" name="texto_garantia" style="border-style:none;background-color:'transparent';color:'Blue'; font-weight: bold;cursor:hand;" size="50">-->
</td>
</tr>
<tr>
<td>
<b>Orden de Producción:</b><input type="text" name="nro_orden" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;cursor:hand;" size="4"><input type="button" name="cargar1" value="Ir" disabled>
</td>
</tr>
<tr>
<td><b>Nro Serie:<input type="text" name="nro_serie" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
<tr>
<td><b>Ensamblador:<input type="text" name="ensamblador" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
<tr>
<td><b>Modelo:<input type="text" name="modelo" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
<tr>
<td>
<b>Garantia:<input type="text" name="garantia" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
<tr>
<td>
<b>Licitación:<input type="text" name="licitacion" value="" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;cursor:hand" size="4"><input type="button" name="cargar2" value="Ir" disabled>
</td>
</tr>
<tr>
<td><b>Cliente:<input type="text" name="entidad" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
<tr>
<td><b>Direccion:<input type="text" name="direccion" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
<tr>
<td><b>Distrito:<input type="text" name="provincia" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
</td>
</tr>
</table>
</td>
<tr>
  <td align="center" colspan=2>
  <input name="aceptar" type="button" value="Cargar" onclick="<?=$parametros['onclickcargar']; ?>window.close();" style="width:'10%'">
  <input name="cancelar" type="button" value="Salir" onclick="<?=$parametros['onclicksalir']; ?>" style="width:'10%'">
  </td>
</tr>
</table>
</form>
</body>
<?
fin_pagina();