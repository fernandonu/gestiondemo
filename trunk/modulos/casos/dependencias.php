<?
/*
$Author: mari $
$Revision: 1.25 $
$Date: 2006/06/29 18:23:33 $
*/
include "head.php";
//Valores generales de los datos

if ($_POST["nuevo"]=="Nuevo") {
	// Valores del formulario
	//print_r($_POST);
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
		} //del while
	$error="";
	if (!$cliente)
		$error.="Debe seleccionar un cliente<br>";
	if (!$contacto)
		$error.="El Organismo no tiene un contacto.<br>";
	if (!$dependencia)
		$error.="Falta dependencia.<br>";
	if (!$direccion)
		$error.="Falta el domicilio del cliente.<br>";
	if (!$telefono)
		$error.="Olvido colocar el Teléfono del contacto.<br>";
	if (!es_numero($provincia)) $provincia="NULL";
	$sql="INSERT INTO dependencias ";
	$sql.="(id_entidad,dependencia,contacto,direccion,lugar,cp,id_distrito,telefono,mail,comentario) VALUES ";
	$sql.="($cliente,'$dependencia','$contacto','$direccion','$lugar','$cp',$provincia,'$telefono','$mail','$comentario')";
	if (!$error) {
		$db->execute($sql) or die($db->errormsg()." - ".$sql);
		aviso("Los datos se modificaron satisfactoriamente");
	}
	else
		error($error);
}
if ($_POST["nuevo"]=="Modificar") {
	while (list($key,$cont)=each($_POST)){
		$$key=$cont;
	}
	if (!$contacto)
		$error.="El Organismo no tiene un contacto.<br>";
	if (!$dependencia)
		$error.="Falta dependencia.<br>";
	if (!$direccion)
		$error.="Falta el domicilio del cliente.<br>";
	if (!$telefono)
		$error.="Olvido colocar el Teléfono del contacto.<br>";
	if (!es_numero($provincia)) $provincia="NULL";
	$sql="UPDATE dependencias SET ";
	$sql.="id_entidad=$cliente,
		dependencia='$dependencia',
		contacto='$contacto',
		direccion='$direccion',
		lugar='$lugar',
		cp='$cp',
		id_distrito=$provincia,
		telefono='$telefono',
		comentario='$comentario',
		mail='$mail' WHERE id_dependencia=$radiodep";
	if (!$error) {
		$db->execute($sql) or die($db->errormsg()." - ".$sql);
		aviso("Los cambios se realizaron satisfactoriamente");
	}
	else
		error($error);
}
?>
<script>
var ventana_cliente=0;
function radioclick(nombre,dependencia,contacto,direccion,lugar,cp,comentario,telefono,provincia,cliente,mail) {
		 var i,encontro;
		 document.all.nombre_cliente.value=nombre;
		 document.all.dependencia.value=dependencia;
		 document.all.contacto.value=contacto;
		 document.all.direccion.value=direccion;
		 document.all.telefono.value=telefono;
		 document.all.mail.value=mail;
		 document.all.lugar.value=lugar;
		 document.all.cliente.value=cliente;
		 document.all.cp.value=cp;
		 document.all.comentario.value=comentario;
		 i=0;
		 encontro=0;
		 while((i<document.all.provincia.options.length) && (!encontro))
		 {
			 if (document.all.provincia.options[i].value==provincia)
			 {
				 encontro=1;
				 document.all.provincia.selectedIndex=i;
			 }
			 i++;
		 }
		 document.all.nuevo.value='Modificar';
		 document.all.eliminar.style.visibility = "visible";

}
function radionuevo() {
		 document.all.dependencia.value="";
		 document.all.contacto.value="";
		 document.all.direccion.value="";
		 document.all.telefono.value="";
		 document.all.mail.value="";
		 document.all.lugar.value="";
		 document.all.cp.value="";
		 document.all.comentario.value="";
		 document.all.cliente.value="";
		 document.all.provincia.selectedIndex=0;
		 document.all.nuevo.value = "Nuevo";
		 document.all.eliminar.style.visibility = "hidden";
}
function cargar() {
	document.all.cliente.value=ventana_cliente.document.all.select_cliente.options[ventana_cliente.document.all.select_cliente.selectedIndex].value;
	document.all.nombre_cliente.value=ventana_cliente.document.all.nombre.value;
}//fin cargar

</script>
<?
variables_form_busqueda("dependencias");
// Barra de consulta para enviarle al formulario
echo "<form action='dependencias.php' method='post'>";
echo "<input type=hidden name=sort value='$sort'>\n";
echo "<table width='99%' border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";


$orden = array(
"default" => "1",
"1" => "nombre",
"2" => "dependencia",
"3" => "dependencias.contacto",
"4" => "dependencias.direccion",
"5" => "dependencias.lugar",
"6" => "dependencias.mail",
"7" => "dependencias.telefono"
);

$filtro = array(
"nombre"      => "Organismo",
"dependencia" => "Dependencia",
"dependencias.contacto"    => "Contacto",
"dependencias.direccion"   => "Dirección",
"dependencias.telefono"    => "Teléfono",
"dependencias.mail"        => "Mail",
"dependencias.lugar"       => "Localidad",
"dependencias.comentario"       => "Observaciones",
"casos_cdr.nserie" => "Nro Serie"
);
// link de pagina
$link_temp = Array(
"sort" => $sort,
"up" => $up,
"filter" => $filter,
"keyword" => $keyword
);
$sql_temp="Select dependencias.*,entidad.nombre,casos_cdr.nserie ";
$sql_temp.=" from dependencias ";
$sql_temp.=" inner join casos_cdr using(id_dependencia) ";
$sql_temp.=" inner join entidad USING(id_entidad)";
if ($parametros["id_dependencia"]) {
	$w_temp="id_dependencia=".$parametros["id_dependencia"]." and idcaso=".$parametros["id_caso"];
}
else $w_temp=NULL;
list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_temp,$orden,$filtro,$link_temp,$w_temp,"buscar");
$rs = $db->Execute($sql) or die($db->ErrorMsg());
$rs->MoveFirst();
?>
<input type='submit' name=enviar value=Buscar>
<table class="bordes" width="99%" cellspacing=2 align=center>
<tr>
<td style='border-right: 0;' colspan=2 align=left id=ma>
<b>Total:</b> <?=$total?> Casos.</td>
<td style='border-left: 0;' colspan=7 align=right id=ma><?=$link_pagina?></td></tr>
<?
$link_temp["page"]=$page;
$link_temp["up"]=$up2;
$link_temp["sort"]="1";
$link_temp["cmd"]=$cmd;
echo "<tr>";
echo "<td align=right id=mo width='10%'><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Organismo</a></td>\n";
$link_temp["sort"]=2;
echo "<td align=right id=mo width='10%'><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Dependecia</td>\n";
$link_temp["sort"]=3;
echo "<td align=right width='10%' id=mo><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Contacto</td>\n";
$link_temp["sort"]=4;
echo "<td align=right width='10%' id=mo><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Dirección</td>\n";
$link_temp["sort"]=5;
echo "<td align=right width='10%' id=mo><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Localidad</td>\n";
$link_temp["sort"]=6;
echo "<td align=right width='10%' id=mo><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Teléfono</td>\n";
$link_temp["sort"]=7;
echo "<td align=right width='10%' id=mo><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Mail</td>\n";
$link_temp["sort"]=8;
echo "<td align=right width='10%' id=mo><a id=mo href='".encode_link("dependencias.php",$link_temp)."'>Nro Serie</td>\n";
echo "<td id=mo>&nbsp;</td>";
echo "</tr>\n";
while (!$rs->EOF) {
    echo "<tr bgcolor=$bgcolor_out>\n";
    echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["nombre"]."</td>\n";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["dependencia"]."</td>\n";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["contacto"]."</td>\n";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["direccion"]."</td>\n";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["lugar"]."</td>\n";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["telefono"]."</td>";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["mail"]."</td>";
	echo "<td align=center style='font-size: 9pt;'>&nbsp;".$rs->fields["nserie"]."</td>";
	echo "<td width=10 align=center style='font-size: 9pt;'>";
	$direccion = str_replace('"','\"',$rs->fields["direccion"]);
	$contacto = str_replace('"','\"',$rs->fields["contacto"]);
	$dependencia = str_replace('"','\"',$rs->fields["dependencia"]);
	$lugar = str_replace('"','\"',$rs->fields["lugar"]);
	$nombre = str_replace('"','\"',$rs->fields["nombre"]);
	$cp = str_replace('"','\"',$rs->fields["cp"]);
	$comentario = str_replace('"','\"',$rs->fields["comentario"]);
	$direccion = str_replace("'","\'",$rs->fields["direccion"]);
	$contacto = str_replace("'","\'",$rs->fields["contacto"]);
	$dependencia = str_replace("'","\'",$rs->fields["dependencia"]);
	$lugar = str_replace("'","\'",$rs->fields["lugar"]);
	$nombre = str_replace("'","\'",$rs->fields["nombre"]);
	$cp = str_replace("'","\'",$rs->fields["cp"]);
	echo  "<input type=radio name='radiodep' value='".$rs->fields["id_dependencia"]."' onClick='radioclick(\"$nombre\",
	\"$dependencia\",
	\"$contacto\",
	\"$direccion\",
	\"$lugar\",
	\"$cp\",
	\"$comentario\",
	\"".$rs->fields["telefono"]."\",
	\"".$rs->fields["id_distrito"]."\",
	\"".$rs->fields["id_entidad"]."\",
	\"".$rs->fields["mail"]."\");'>";
	echo  "</td>\n";
	echo "</tr>\n";
	$rs->MoveNext();
}
echo "<tr><td colspan=9 align=right>\n";
echo "<input type=radio name=radiodep onClick='radionuevo();' checked> Nuevo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>\n";
echo "</table><br>\n";
?>
<br><table width="100%" style='border: 1px solid black;border-collapse:collapse' cellpadding=0 cellspacing=6 bgcolor=#EFEFEF bordercolor="#111111">
<tr>
<td>
<b>NOTA</b>:</font> Los campos marcados con<b><font color="#FF0000"> * </font>
</b>(asterisco) son indispensables para abrir el caso.</font></p>
<center>
<div align="center">
  <center>
<table width="325" border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse; " bordercolor="#9A9A9A">
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Modificar/Agregar datos de la Dependencia</font></b></p>
  </td>
 </tr>
 <tr>
	 <td>
		 <font color=blue face="Trebuchet MS" size="2" onclick="ventana_cliente=window.open('<?=encode_link('caso_elegir_cliente.php',array('onclickcargar'=>'window.opener.cargar();','onclicksalir'=>'window.close()'))?>','','left=40,top=80,width=700,height=400');" style="cursor:hand;">
		 <u>Cliente:</u>
		 </font><font color="#FF0000"> * </font>
		 <input type="text" name="nombre_cliente" value="" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" size="100" readonly>
	 </td>
 </tr>
 <tr>
  <td>
	  <table width="100%">
	  <tr>
	 <td>
	  <font face="Trebuchet MS" size="2">Dependencia<b><font color="#FF0000"> * </font>
</b>:
	  </font>
	 </td>
	 <td>
	  <input type=text name=dependencia value='' size=25>
	 </td>
	 <td>
	  <font face="Trebuchet MS" size="2">Contacto<b><font color="#FF0000"> * </font>
</b>:
	  </font>
	 </td>
	 <td>
	  <input type=text name=contacto value='' size=25>
	 </td>
	</tr>
	<tr>
	 <td>
	  <font face="Trebuchet MS" size="2">Dirección<b><font color="#FF0000"> * </font>
</b>: </font>
	 </td>
	 <td>
	  <input type=text name=direccion value='' size=25>
	 </td>
	 <td>
	  <font face="Trebuchet MS" size="2">Lugar/Ubicación: </font>
	 </td>
	 <td>
	  <input type=text name=lugar value='' size=25>
	 </td>
	</tr>
	<tr>
	 <td>
	  <font face="Trebuchet MS" size="2">Codigo Postal: </font>
	 </td>
	 <td>
	  <input type=text name=cp value='' size=25>
	 </td>
	 <td>
	  <font face="Trebuchet MS" size="2">Provincia</font>
	 </td>
	 <td>
	  <select name=provincia>
	  <option>&nbsp;</option>
	  <?
	  $sql1="select id_distrito,nombre from distrito order by nombre";
	  $rs1=$db->execute($sql1) or die($db->errormsg());
	   while ($fila=$rs1->fetchrow()) {
		 echo "<option value='".$fila['id_distrito']."'>".$fila['nombre']."</option>\n";
		 }
	   ?>
	 </select>
	 </td>
	</tr>
	<tr>
	 <td>
	  <font face="Trebuchet MS" size="2">Teléfono<b><font color="#FF0000"> * </font>
</b>: </font>
	 </td>
	 <td>
	  <input type=text name=telefono value='' size=25>
	 </td>
	 <td>
	  <font face="Trebuchet MS" size="2">E-M@il: </font>
	 </td>
	 <td>
	  <input type=text name=mail value='' size=25>
	 </td>
	</tr>
	<tr>
		<td valign=top>
			Observaciones:
		</td>
		<td colspan=3>
			<textarea name="comentario" rows=5 cols=60></textarea>
		</td>
	</tr>
   </table>
  </td>
 </tr>
<tr>
 <td colspan=2 align=right>
  <input type=hidden name=cliente value="<? echo $rs->fields["id_cliente"];?>">
  <input type=submit style="visibility: hidden;" name=eliminar value='Eliminar'>
  <input type=submit name=nuevo value='Nuevo'>
  <input type=button name=cerrar value="Cerrar" onClick="window.close();" 
  <? if (!$parametros["id_dependencia"]) echo "style='visibility: hidden';"?>> 
</td></tr></table>
 </td>
</tr>
</table>
<?if ($parametros["id_dependencia"]) echo "<script>
document.all.radiodep[0].click();
</script>";
	   ?>