<?php
/*
$Author: cesar $
$Revision: 1.4 $
$Date: 2004/08/06 23:20:03 $
*/
include "head.php";
//Valores generales de los datos
$idest=$parametros["idest"] or $idest=$_POST["idest"];
$descripcion=$parametros["descripcion"] or $descripcion=$_POST["descripcion"];
//Valores del formulario de busqueda
$up=$parametros["up"] or $up=$_POST["up"];
$sort=$parametros["sort"] or $sort=$_POST["sort"];
$page=$parametros["page"] or $page=$_POST["page"];
$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
$filter=$parametros["filter"] or $filter=$_POST["filter"];

if ($_POST["eliminar"]=="Eliminar") {
    if (!$idest)
         $error="No a seleccionado ningun estado.<br>";
    $sql="DELETE FROM estadousuarios where idestuser=$idest";
    if (!$error){
         $db->execute($sql) or die($db->errormsg() . " - " . $sql);
         $descripcion="";
    }
    else
        error($error);
}
if ($_POST["nuevo"]=="Modificar") {
    $error="";
    if (!$descripcion)
         $error.="Debe cargarse la descripción del repuesto.<br>";
    $sql="UPDATE estadousuarios set descripcion='$descripcion' WHERE idestuser=$idest";
    if (!$error){
         $db->execute($sql) or die($db->errormsg() . " - " . $sql);
         $descripcion="";
    }
    else
        error($error);
}
if ($_POST["nuevo"]=="Nuevo") {
	$error="";
    if (!$descripcion)
         $error.="Debe cargarse la descripción del repuesto.<br>";
    $sql="INSERT INTO estadousuarios (descripcion) VALUES ('$descripcion')";
    if (!$error){
         $db->execute($sql) or die($db->errormsg());
         $descripcion="";
    }
    else
        error($error);
}
echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
?>
<script>
function radioclick(id,descripcion) {
         descrip = descripcion.replace("<n>","\n");
         document.all.descripcion.value=descrip;
         document.all.nuevo.value='Modificar';
		 document.all.eliminar.style.visibility = "visible";
}
function radionuevo() {
         document.all.nuevo.value = "Nuevo";
         document.all.descripcion.value = "";
		 document.all.eliminar.style.visibility = "hidden";
}
</script>
<?
// Barra de consulta para enviarle al formulario
echo "<form action='caso_est.php' method='post'>";
//echo "<input type=hidden name=modo value='admin'>\n";
//echo "<input type=hidden name=modulo value='caso'>\n";
//echo "<input type=hidden name=cmd value='est'>\n";
echo "<input type=hidden name=short value='$sort'>\n";
echo "<table width=99% border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";

if (!$sort) $sort=1;

$orden = array(
"1" => "idestuser",
"2" => "descripcion"
);

$filtro = array(
"idestuser"       => "Id",
"descripcion"      => "Descripción"
);

if ($up == "0") {
    $up = $_GET["up"];
    $direction="DESC";
    $up2 = "1";
}
else {
     $up = "1";
     $direction = "ASC";
     $up2 = "0";
}

$tmp=es_numero($keyword);
echo "<b>Buscar:&nbsp;</b><input type='text' name='keyword' value='$keyword' size=20 maxlength=20>\n";
echo "<b>&nbsp;en:&nbsp;<b><select name='filter'>&nbsp;\n";
echo "<option value='all'";
if (!$filter) echo " selected";
echo ">Todos los campos\n";
while (list($key, $val) = each($filtro)) {
       echo "<option value='$key'";
       if ($filter == "$key") echo " selected";
       echo ">$val\n";
}
echo "</select>\n";

if ($keyword) {
    $where = " WHERE ";
    if ($filter == "all" or !$filter) {
        $where_arr = array();
        $where .= "(";
        reset($filtro);
        while (list($key, $val) = each($filtro)) {
               $where_arr[] = "$key ilike '%$keyword%'";
        }
        $where .= implode(" or ", $where_arr);
        $where .= ")";
    }
    else {
          $where .= "$filter ilike '%$keyword%'";
    }
}
$sql = "SELECT idestuser,descripcion FROM estadousuarios";
$sql .= " $where";
$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);
$sqlcount="SELECT COUNT(*) AS total FROM estadousuarios $where";
$rs=$db->Execute($sqlcount);
$total=$rs->fields["total"];
$page_n = $page + 1;
$page_p = $page - 1;
$link_pagina_p = "";
$link_pagina_n = "";

$link_form = Array(
"sort" => $sort,
"up" => $up,
"filter" => $filter,
"keyword" => $keyword
);

if ($page > 0) {
	$link_form["page"]=$page_p;
    $link_pagina_p = "<a style='color: white;' href='".encode_link("caso_est.php",$link_form)."'><<</a>";
}
$sum=0;
if (($total % $itemspp)>0) $sum=1;
$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)."&nbsp;de&nbsp;". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
if ($total > $page_n*$itemspp) {
    $link_form["page"]=$page_n;
	$link_pagina_n = "<a style='color: white;' href='".encode_link("caso_est.php",$link_form)."'>>></a>";
}
if ($total > 0 and $total > $itemspp) {
    $link_pagina = $link_pagina_p.$link_pagina_num.$link_pagina_n;
}
else {
    $link_pagina = "&nbsp;";
}
//    echo "sql: $sql total: $totalProv link: $link_pagina<Br>"; exit;
echo "<input type=submit name=envia value='Buscar'>&nbsp;&nbsp;";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='index.php?modulo=caso';\">\n";
echo "</td></tr></table></form><br>\n";
$rs = $db->Execute($sql) or die($db->ErrorMsg());
?>
<form action='caso_est.php' method='POST' name=frm id=frm>
<?
echo "<table class='bordes' width=99% cellspacing=2 align=center>";
echo "<tr><td style='border-right: 0;' align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Estados.</td>\n";
echo "<td style='border-left: 0;' colspan=2 align=right id=ma>$link_pagina</td></tr>\n";
$link_form["page"]=$page;
$link_form["up"]=$up2;
$link_form["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("caso_est.php",$link_form)."'>Id</a></td>\n";
$link_form["sort"]=2;
echo "<td align=right id=mo width=60%><a id=mo href='".encode_link("caso_est.php",$link_form)."'>Descripción</td>\n";
echo "<td align=right id=mo>Función</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
    //$ref = "index.php?modo=admin&modulo=caso&cmd=modi&id=".$rs->fields["idest"];
    //tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'",$rs->fields['fechacierre']);
    echo "<tr bgcolor='$bgcolor_out'>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["idestuser"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["descripcion"]."</td>\n";
    $descrip = str_replace(chr(13).chr(10),"<n>",$rs->fields["descripcion"]);
    if ($rs->fields["enviado"]) $envio="Si";
    else $envio="No";
    if ($rs->fields["idestuser"]!=1 and $rs->fields["idestuser"]!=2)
        echo "<td align=left style='font-size: 9pt;'><input type=radio name=idest value='".$rs->fields["idestuser"]."' onClick='radioclick(".$rs->fields["idestuser"].",\"".$descrip."\");'</td>\n";
    else
        echo "<td align=left style='font-size: 9pt;'>&nbsp;</td>";
    echo "</tr>\n";
    $rs->MoveNext();
}
echo "<tr><td colspan=3 align=right>\n";
echo "<input type=radio name=idest onClick='radionuevo();' checked> Nuevo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>\n";
echo "<tr><td colspan=4>\n";
?>
<br><table width=100% style='border: 1px solid black;border-collapse:collapse' cellpadding=0 cellspacing=6 bgcolor=#EFEFEF bordercolor="#111111">
<tr>
<td>
<p align="left"><font face="Trebuchet MS" size="2">Manejo de los estados del
CAS, Estos son los estados que van a ver los usuarios.</font></p>
<p align="left"><font face="Trebuchet MS" size="2">
<font color="#993300">
<b>NOTA</b>:</font> Los campos marcados con<b><font color="#FF0000"> * </font>
</b>(asterisco) son indispensables para abrir el caso.</font></p>
<center>
<div align="center">
  <center>
<table width=325 border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse; " bordercolor="#9A9A9A">
 <tr>
  <td align=center>
   <p class=menutitulo style='margin-bottom: 0;'><b>
   <font face="Trebuchet MS" color="#009900">Modificar datos del&nbsp; CAS</font></b></p>
  </td>
 </tr>
 <tr>
  <td>
   <table width=100%>
    <tr>
     <td width=40% valign=top>
      <p align="right"><font face="Trebuchet MS" size="2">Estado<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
      <input name=descripcion cols=40 value='<?echo $descripcion;?>'>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</td>
</tr>
<tr>
 <td colspan=3 align=right >
  <input type=submit style="visibility: hidden;" name=eliminar value='Eliminar'>
  <input type=submit name=nuevo value='Nuevo'>
 </td>
</tr>
</table>
</td>
</tr>
</table>