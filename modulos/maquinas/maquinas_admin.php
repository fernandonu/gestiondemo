<?php
/*
$Author: cestila $
$Revision: 1.1 $
$Date: 2004/02/05 21:47:23 $
*/
if ($_POST["cmd"]=="Modificar") {
	include("maquinas_modificar.php");
	exit();
}
require_once("../../config.php");
echo $html_header;

//Valores del formulario de busqueda
$up=$parametros["up"] or $up=$_POST["up"];
$sort=$parametros["sort"] or $sort=$_POST["sort"];
$page=$parametros["page"] or $page=$_POST["page"];
$keyword=$parametros["keyword"] or $keyword=$_POST["keyword"];
$filter=$parametros["filter"] or $filter=$_POST["filter"];

// Barra de consulta para enviarle al formulario
echo "<form action='maquinas_admin.php' method='post'>";
echo "<input type=hidden name=sort value='$sort'>\n";
echo "<table width=99% border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";

if (!$sort) $sort=1;

$orden = array(
"1" => "clientes_drivers.nserie",
"2" => "clientes_drivers.garantia",
"3" => "clientes_drivers.fecha",
"4" => "clientes.nombre",
"5" => "dependencia.dependencia",
"6" => "clientes.telefono",
);

$filtro = array(
"clientes_drivers.nserie"         => "Número de serie",
"clientes_drivers.fecha"         => "Fecha de compra",
"clientes.nombre"       => "Organismo",
"dependencia.dependencia"       => "Dependencia",
"clientes.telefono"      => "Teléfono",
);
if (!$up) $up="0";
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
               $where_arr[] = "$key ilike '%$keyword%'";
        }
        $where .= implode(" or ", $where_arr);
        $where .= ")";
    }
    else {
          $where .= "$filter ilike '%$keyword%'";
    }
}
$sql = "SELECT clientes_drivers.id,clientes_drivers.nserie,clientes_drivers.garantia,
clientes_drivers.fecha,clientes.nombre as organismo,
clientes.telefono,dependencia.dependencia 
FROM clientes_drivers left join clientes on clientes.id_cliente=clientes_drivers.idcliente 
left join dependencia on dependencia.id_cliente=idcliente";
$sql .= " $where ";
$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);
$sqlcount="SELECT COUNT(*) AS total FROM clientes_drivers left join clientes on clientes.id_cliente=clientes_drivers.idcliente 
left join dependencia on dependencia.id_cliente=idcliente $where";
$rs=$db->Execute($sqlcount) or die($db->errormsg());
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
	$link_pagina_p = "<a style='color: white;' href='".encode_link("maquinas_admin.php",$link_form)."'><<</a>";
}
$sum=0;
if (($total % $itemspp)>0) $sum=1;
$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)."&nbsp;de&nbsp;". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
if ($total > $page_n*$itemspp) {
    $link_form["page"]=$page_n;
	$link_pagina_n = "<a style='color: white;' href='".encode_link("maquinas_admin.php",$link_form)."'>>></a>";
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
echo "</td></tr>\n";
echo "</table><br>\n";
$rs = $db->Execute($sql) or error($db->ErrorMsg());
echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#ffffff' align=left>";
echo "<tr><td colspan=6>\n";
echo " Modificar por rango: <input type=text name=desde onChange='document.all.desde.value = document.all.desde.value.toUpperCase();'> a <input type=text name=hasta onChange='document.all.hasta.value = document.all.hasta.value.toUpperCase();'>&nbsp;<input type=submit name=cmd value=Modificar>\n";
echo "</td></tr>\n";
echo "<tr><td style='border-right: 0;' colspan=2 align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Máquinas.</td>\n";
echo "<td style='border-left: 0;' colspan=4 align=right id=ma>$link_pagina</td></tr>\n";
$link_form["page"]=$page;
$link_form["up"]=$up2;
$link_form["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("maquinas_admin.php",$link_form)."'>N° de Serie</a></td>\n";
$link_form["sort"]=2;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_admin.php",$link_form)."'>Garantia</td>\n";
$link_form["sort"]=3;
echo "<td align=right width=60 id=mo><a id=mo href='".encode_link("maquinas_admin.php",$link_form)."'>fecha</td>\n";
$link_form["sort"]=4;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_admin.php",$link_form)."'>Organismo</td>\n";
$link_form["sort"]=5;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_admin.php",$link_form)."'>Dependencia</td>\n";
$link_form["sort"]=6;
echo "<td align=right id=mo><a id=mo href='".encode_link("maquinas_admin.php",$link_form)."'>Teléfono</td>\n";
//echo "<td align=right id=mo>X</td>\n";
echo "</tr>\n";
while (!$rs->EOF) {
    $ref = encode_link("maquinas_modificar.php",Array("id"=>$rs->fields["id"]));
    tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'");
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["nserie"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["garantia"]."</td>\n";
    echo "<td width=60 align=center style='font-size: 9pt;'>".ConvFecha($rs->fields["fecha"])."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["organismo"]."</td>\n";
    echo "<td align=center style='font-size: 9pt;'>".$rs->fields["dependencia"]."</td>\n";
    echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs->fields["telefono"]."</td>\n";
//    echo "<td align=left style='font-size: 9pt;'>&nbsp;</td>\n";
    echo "</tr>\n";
    $rs->MoveNext();
}
//echo "<tr><td colspan=6 align=right>\n";
//echo "</td></tr>\n";
echo "</table>\n";
echo "</form>\n";
$ocurrioerror=0;

?>