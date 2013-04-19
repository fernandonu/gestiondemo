<?php
/*
$Author: cestila $
$Revision: 1.3 $
$Date: 2004/02/05 21:46:40 $
*/
include "head.php";
$id=$parametros["id"] or $id=$_POST["id"];
//Valores del formulario de busqueda
$up=$parametros["up"] or $up=$_POST["up"];
$sort=$parametros["sort"] or $sort=$_POST["sort"];
$page=$parametros["page"] or $page=$_POST["page"];
$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
$filter=$parametros["filter"] or $filter=$_POST["filter"];
//Datos por post
$proveedor=$_POST["proveedor"];
$descripcion=$_POST["descripcion"];
$enviado=$_POST["enviado"];
if ($_POST["nuevo"]=="Modificar") {
    $error="";
    if (!$proveedor)
        $error.="No se puede cargar el repuesto sin un proveedor.<br>";
    if (!$descripcion)
         $error.="Debe cargarse la descripción del repuesto.<br>";
    if (!$enviado)
        $enviado=0;
    $sql="UPDATE repuestos set proveedor='$proveedor',descripcion='$descripcion',enviado=$enviado WHERE idrepuesto=$idest";
    if (!$error){
         $db->execute($sql) or die($db->errormsg());
         $descripcion="";
         $proveedor="";
         $enviado=0;
    }
    else
        error($error);
}
if ($_POST["nuevo"]=="Nuevo") {
    $error="";
    if (!$proveedor)
        $error.="No se puede cargar el repuesto sin un proveedor.<br>";
    if (!$descripcion)
         $error.="Debe cargarse la descripción del repuesto.<br>";
    if (!$enviado)
        $enviado=0;
    $sql="INSERT INTO repuestos (idcaso,descripcion,proveedor,enviado) VALUES ($id,'$descripcion','$proveedor',$enviado)";
    if (!$error){
         $db->execute($sql) or die($db->errormsg());
         $descripcion="";
         $proveedor="";
         $enviado=0;
    }
    else
        error($error);
}
echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
?>
<script>
function radioclick(id,proveedor,descripcion,enviado) {
         descrip = descripcion.replace("<n>","\n");
         document.all.descripcion.value=descrip;
         document.all.proveedor.value=proveedor;
         if (enviado==1)
             document.all.enviado.checked = "True";
         else
             document.all.enviado.checked = "";
         document.all.nuevo.value='Modificar';
}
function radionuevo() {
         document.all.nuevo.value = "Nuevo";
         document.all.descripcion.value = "";
         document.all.proveedor.value = "";
         document.all.enviado.checked = "";
}
</script>
<?
$sql="select nrocaso from casos_cdr where idcaso=$id";
$rs=$db->execute($sql) or die($db->errormsg());
echo "<center><h3>Repuestos del caso Nro: ".$rs->fields["nrocaso"]."</h3></center>\n";
// Barra de consulta para enviarle al formulario
echo "<form action='caso_repuesto.php' method='post'>";
echo "<input type=hidden name=short value='$sort'>\n";
echo "<input type=hidden name='id' value='$id'>";
echo "<table width=99% border=0 cellspacing=0 cellpadding=0>\n";
echo "<tr><td align=center>\n";

if (!$sort) $sort=1;

$orden = array(
"1" => "descripcion",
"2" => "proveedor",
"3" => "enviado"
);

$filtro = array(
"descripcion"      => "Descripción",
"proveedor"       => "Proveedor"
);

if ($up == "0") {
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
    $where = " AND ";
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
$sql = "SELECT idrepuesto,descripcion,proveedor,enviado FROM repuestos WHERE idcaso=$id";
$sql .= " $where";
$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);
$sqlcount="SELECT COUNT(*) AS total FROM repuestos WHERE idcaso=$id $where";
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
"keyword" => $keyword,
"id" => $id
);

if ($page > 0) {
    $link_form["page"]=$page_p;
	$link_pagina_p = "<a style='color: white;' href='".encode_link("caso_repuesto.php",$link_form)."'><<</a>";
}
$sum=0;
if (($total % $itemspp)>0) $sum=1;
$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)."&nbsp;de&nbsp;". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
if ($total > $page_n*$itemspp) {
    $link_form["page"]=$page_p;
	$link_pagina_n = "<a style='color: white;' href='".encode_link("caso_repuesto.php",$link_form)."'>>></a>";
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
echo "</td></tr><br>\n";
$rs = $db->Execute($sql) or die($db->ErrorMsg());
?>
 <tr>
  <td>
   Por Hacer :
   <input type=button name=cmd value="Datos del Caso" onclick="window.location='<? echo encode_link("caso_modi.php",Array("id"=>$id)); ?>';">
   <input type=button name=cmd value="Estados del Caso" onclick="window.location='<? echo encode_link("caso_estados.php",Array("id"=>$id)); ?>';">
   <input type=button name=cmd value="Lista de Casos" onclick="window.location='<? echo encode_link("caso_admin.php",Array("id"=>$id)); ?>';">
   <input type=button name=cmd value="Informe" onclick="window.location='<? echo encode_link("caso_inf.php",Array("id"=>$id)); ?>';">
  </td>
 </tr>
</table></form>
<form action='caso_repuesto.php' method='POST' name=frm id=frm>
<input type=hidden name='id' value='<? echo $id; ?>'>
<?
echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#ffffff' align=left>";
echo "<tr><td style='border-right: 0;' align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Repuestos.</td>\n";
echo "<td style='border-left: 0;' colspan=3 align=right id=ma>$link_pagina</td></tr>\n";
$link_form["page"]=$page;
$link_form["up"]=$up2;
$link_form["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("caso_repuesto.php",$link_form)."'>Descripción</a></td>\n";
$link_form["sort"]=2;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_repuesto.php",$link_form)."'>Proveedor</td>\n";
$link_form["sort"]=3;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_repuesto.php",$link_form)."'>Enviado</td>\n";
echo "<td align=right id=mo>Función</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
    //$ref = "index.php?modo=admin&modulo=caso&cmd=modi&id=".$rs->fields["idest"];
    //tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'",$rs->fields['fechacierre']);
    echo "<tr bgcolor='$bgcolor2'>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["descripcion"]."</td>\n";
    echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs->fields["proveedor"]."</td>\n";
    $descrip = str_replace(chr(13).chr(10),"<n>",$rs->fields["descripcion"]);
    if ($rs->fields["enviado"]) $envio="Si";
    else $envio="No";
    echo "<td align=left style='font-size: 9pt;'>&nbsp;".$envio."</td>\n";
    echo "<td align=left style='font-size: 9pt;'><input type=radio name=idest value='".$rs->fields["idrepuesto"]."' onClick='radioclick(".$rs->fields["idrepuesto"].",\"".$rs->fields["proveedor"]."\",\"".$descrip."\",".$rs->fields["enviado"].");'</td>\n";
    echo "</tr>\n";
    $rs->MoveNext();
}
echo "<tr><td colspan=4 align=right>\n";
echo "<input type=radio name=idest onClick='radionuevo();' checked> Nuevo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>\n";
echo "<tr><td colspan=4>\n";
?>
<br><table width=100% style='border: 1px solid black;border-collapse:collapse' cellpadding=0 cellspacing=6 bgcolor=#EFEFEF bordercolor="#111111">
<tr>
<td>
<p align="left"><font face="Trebuchet MS" size="2">Modifique los datos de este
CAS, tenga en cuenta la importancia requerida en el repuesto del CAS.</font></p>
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
      <p align="right"><font face="Trebuchet MS" size="2">Descripción<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
      <textarea name=descripcion cols=20><?echo $descripcion;?></textarea>
     </td>
    </tr>
    <tr>
     <td width=40% valign=top>
      <p align="right"><font face="Trebuchet MS" size="2">Proveedor<font color="#FF0000"><b> *
</b> </font>
      : </font>
     </td>
     <td>
      <input type=text name=proveedor value='<?echo $proveedor;?>' size=26>
     </td>
    </tr>
    <tr>
     <td width=40% valign=top>
      <p align="right"><font face="Trebuchet MS" size="2">Enviado: </font>
     </td>
     <td>
<?
if ($enviado==1)
    echo "<input type=checkbox name=enviado value='1' checked>\n";
else
    echo "<input type=checkbox name=enviado value='1'>\n";
?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</tr></td>
<tr>
 <td colspan=3 align=right >
  <input type=submit name=nuevo value='Nuevo'>
</td></tr></table>
 </td>
</tr>
</table>