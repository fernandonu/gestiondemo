<?php
/*
$Author: cestila $
$Revision: 1.1 $
$Date: 2004/02/05 21:47:23 $
*/
require_once("../../config.php");
echo $html_header;

//Valores del formulario de busqueda
$up=$parametros["up"] or $up=$_POST["up"];
$sort=$parametros["sort"] or $sort=$_POST["sort"];
$page=$parametros["page"] or $page=$_POST["page"];
$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
$filter=$parametros["filter"] or $filter=$_POST["filter"];

//print_r ($_POST);
// Barra de consulta para enviarle al formulario
echo "<form action='drivers_admin.php' method='post'>";
echo "<input type=hidden name=sort value='$sort'>\n";
echo "<table width=99% border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";

if (!$sort) $sort=1;

$orden = array(
"1" => "archivo",
//"2" => "tipo",
"3" => "modelo",
"4" => "descripcion",
"5" => "size"
);

$filtro = array(
"archivo"         => "Drivers",
"modelo"       => "Modelos de Motheboard",
"descripcion"      => "Descripción"
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
    $where = " WHERE ";
    if ($filter == "all" or !$filter) {
        $where_arr = array();
        $where .= "(";
        reset($filtro);
        while (list($key, $val) = each($filtro)) {
               $where_arr[] = "$key like '%$keyword%'";
        }
        $where .= implode(" or ", $where_arr);
        $where .= ")";
    }
    else {
          $where .= "$filter like '%$keyword%'";
    }
}
$sql = "SELECT * FROM drivers";
$sql .= " $where";
$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);
$sqlcount="SELECT COUNT(*) AS total FROM drivers $where";
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
	$link_pagina_p = "<a style='color: white;' href='".encode_link("caso_admin.php",$link_form)."'><<</a>";
}
$sum=0;
if (($total % $itemspp)>0) $sum=1;
$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)."&nbsp;de&nbsp;". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
if ($total > $page_n*$itemspp) {
    $link_form["page"]=$page_n;
	$link_pagina_n = "<a style='color: white;' href='".encode_link("caso_admin.php",$link_form)."'>>></a>";
}
if ($total > 0 and $total > $itemspp) {
    $link_pagina = $link_pagina_p.$link_pagina_num.$link_pagina_n;
}
else {
    $link_pagina = "&nbsp;";
}
    //echo "sql: $sql total: $totalProv link: $link_pagina<Br>"; exit;
echo "<input type=submit name=envia value='Buscar'>&nbsp;&nbsp;";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='index.php?modulo=caso';\">\n";
echo "</td></tr></table><br>\n";
echo "</form>\n";
$rs = $db->Execute($sql) or error($db->ErrorMsg());

echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='white' align=left>";
echo "<tr><td colspan=2 style='border-right: 0;' align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Drivers.</td>\n";
echo "<td colspan=2 style='border-left: 0;' align=right id=ma>$link_pagina</td></tr>\n";
$link_form["page"]=$page;
$link_form["up"]=$up2;
$link_form["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_form)."'>Archivo</a></td>\n";
//echo "<td align=right id=mo><a id=mo href='index.php?modo=$modo&modulo=drivers&sort=2&up=$up2&page=$page&keyword=$keyword&filter=$filter'>Tipo de Driver</a></td>\n";
$link_form["sort"]=3;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_form)."'>Modelo</a></td>\n";
$link_form["sort"]=4;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_form)."'>Descripción</a></td>\n";
$link_form["sort"]=5;
echo "<td align=right id=mo><a id=mo href='".encode_link("caso_admin.php",$link_form)."'>Tamaño</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
    //$ref = "index.php?modo=admin&modulo=caso&cmd=modificar&id=".$rs->fields["id_caso"];
    //tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'");
    echo "<tr style='font-size: 9pt' bgcolor='#f0f0f0'><td align=center>".$rs->fields["archivo"]."</td>\n";
//    echo "<td align=center>&nbsp;".$tipo_drivers[$rs->fields["tipo"]]."</td>\n";
    echo "<td align=center>&nbsp;".$rs->fields["modelo"]."</td>\n";
    echo "<td align=center>&nbsp;".$rs->fields["descripcion"]."</td>\n";
    $size=number_format(($rs->fields["size"] / 1024));
    echo "<td align=center>&nbsp;$size Kb</td>\n";
    echo "</tr>\n";
    $rs->MoveNext();
}
echo "</td></tr>\n";
echo "</table>\n";
//echo "<input type=submit name=cmd value='Nuevo Caso'>&nbsp;&nbsp;";
$ocurrioerror=0;


?>