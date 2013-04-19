<?php
//phpinfo();

session_start();

$path_pre="../../";
$include_path = $path_pre."config.php";
include_once $include_path;

$abonos=array(
        "A"     => "Servicio Gold",
        "B"     => "Servicio Gold",
        "C"     => "Servicio Gold",
        "D"     => "Servicio Gold",
        "E"     => "Servicio Gold",
        "F"     => "Cuenta Gratis",
        "H10"   => "Servicio Basic",
        "H50"   => "Servicio Medium",
        "X50"   => "Servicio Medium (Cuenta Extra)",
        "XF"    => "Servicio Gold (Cuenta Extra)"
);


$colorOk="#7CB656"; //"#CCFFFF"; //"#66CC66";
$color7dias="#FFCC00";
$colorVencido="#FF0000";
$colorBorrado="#FFFFFF";
$colorCerrado="#CCFFFF";

/**
 * @return 1.234,56
 * @param num int
 * @desc Convierte un numero de la forma 1234.56 al formato de
 *       dinero 1.234,56
 */
 function formato_money($num) {
 	return number_format($num, 2, ',', '.');
 }
 /**
  * @return string
  * @param link url
  * @desc Retorna el codigo para un tag TR que cambia de color
  *       cuando pasa el mouse y genera un link con la URL dada
  *       como parametro.
  */
 function gen_tr_tag ($link) {
 	global $cnr, $bgcolor1, $bgcolor2, $color_fila;
 	if ($color_fila) { $color = $color_fila; }
 	else { $color = $bgcolor1; }
 	$tr_hover_on = "onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\" onClick=\"location.href = '$link'\"";
 	echo "<tr bgcolor=$color $tr_hover_on>\n";
 }

if (!($EstActivo || $EstBorrado || $EstVencido || $EstCerrado || $EstGratis)) {
	$EstActivo=1;
	$EstVencido=1;
}
// Nuevas Fuinciones
function form_busqueda($sql,$orden,$filtro,$link_pagina,$where_extra="") {
	global $bgcolor2,$page,$filter,$keyword;
	$itemspp = 20;			// Items por pagina
	$sort = $_GET["sort"] or $sort = "default";
	if ($_GET["up"] == "0") {
		$up = $_GET["up"];
		$direction="DESC";
		$up2 = "1";
	}
	else {
		$up = "1";
		$direction = "ASC";
		$up2 = "0";
	}

	if ($sort == "default") { $sort = $orden[$sort]; }
	
	echo "<table border=0 bgcolor=$bgcolor2 width=80% align=center>\n";
	echo "<tr><td align=center>\n";
	echo "Buscar: <input type='text' name='keyword' value='$keyword' size=20 maxlength=20>\n";
	echo "en: <select name='filter'>\n";
	echo "<option value='all'";
	if (!$filter) echo " selected";
	echo ">Todos los campos\n";
	while (list($key, $val) = each($filtro)) {
		echo "<option value='$key'";
		if ($filter == "$key") echo " selected";
		echo ">$val\n";
	}
	echo "</select>\n";
	echo "<input type=submit name=buscar value='   Buscar   '>\n";
	echo "</td></tr></table>\n";

	if ($keyword) {
		$where = " WHERE ";
		if ($filter == "all" or !$filter) {
			$where_arr = array();
			$where .= "(";
			reset($filtro);
			while (list($key, $val) = each($filtro)) {
				$where_arr[] = "$filtro[$key] like '%$keyword%'";
			}
			$where .= implode(" or ", $where_arr);
			$where .= ")";
		}
		else {
			$where .= "$filtro[$filter] like '%$keyword%'";
		}
	}
	
	$sql .= " $where";
	if ($where_extra != "") {
		$sql .= " AND $where_extra";
	}

	$sql_cont = eregi_replace("^SELECT (.+) FROM", "SELECT COUNT(*) AS total FROM", $sql);
	$result = db_query($sql_cont) or db_die($sql_cont);
	$row = db_fetch_row($result);
	$total = $row["total"];

	$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);


	$page_n = $page + 1;
	$page_p = $page - 1;
	$link_pagina_p = "";
	$link_pagina_n = "";
	if ($page > 0) {
		$link_pagina_p .= $link_pagina."&sort=$sort&up=$up&page=$page_p&keyword=$keyword&filter=$filter'><<</a>";
	}
	$sum=0;
	if (($total % $itemspp)>0) $sum=1;
	$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)." de ". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
	if ($total > $page_n*$itemspp) {
		$link_pagina_n = $link_pagina."&sort=$sort&up=$up&page=$page_n&keyword=$keyword&filter=$filter'>>></a>";
	}
	
	$link_pagina = $link_pagina_p.$link_pagina_num.$link_pagina_n;

	return array($sql,$total,$link_pagina,$up2);
}
function NuevProv ($id=0) {
	global $bgcolor2,$PHPSESSID;
	echo "<br><table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	// Titulo del Formulario
	if ($id!=0) echo "<tr bordercolor='#000000'><td id=mo align=center>Datos del Proveedor</td></tr>";
	else echo "<tr bordercolor='#000000'><td id=mo align=center>Nuevo Proveedor</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	if ($id!=0) {
		$sql="SELECT * FROM bancos.proveedores WHERE IdProv=$id";
		$result = db_query($sql) or db_die($sql);
		$fila=db_fetch_row($result);
		echo "<tr><td align=right><b>Id Proveedor</b></td>";
		echo "<td align=left><input type=hidden name=Nuevo_Proveedor_Id value='$fila[idprov]'>$fila[idprov]</td</tr>";
	}
	echo "<tr><td align=right><b>Nombre</b></td>";
	echo "<td align=left>";
	echo "<input type=text name=Nuevo_Proveedor_Nombre value='$fila[proveedor]' size=30 maxlength=100>\n";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Número C.U.I.T.</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_CUIT value='$fila[cuit]' size=30 maxlength=20>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Domicilio</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Domicilio value='$fila[domicilio]' size=30 maxlength=100>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Código Postal</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_CP value='$fila[cp]' size=30 maxlength=6>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Localidad</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Localidad value='$fila[localidad]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Provincia</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Provincia value='$fila[provincia]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Contacto</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Contacto value='$fila[contacto]' size=30 maxlength=100>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>E-Mail</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Mail value='$fila[mail]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Teléfono</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Telefono value='$fila[teléfono]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Fax</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Fax value='$fila[fax]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
	echo "</td><td>";
	echo "<textarea name=Nuevo_Proveedor_Comentarios cols=21 rows=5>$fila[comentarios]</textarea>";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Nuevo_Proveedor_Aceptar value='Aceptar'>&nbsp;&nbsp;&nbsp;\n";
	if ($_POST[Ingreso_Cheque_Nuevo_Proveedor]=="Nuevo Proveedor"){
		echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=forms&cmd=Ing_Cheques';\">\n";
	}
	else {
		echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=forms&cmd=Mant_Proveedores';\">\n";
	}
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table></form><br>\n";	
}
// fin nuevas funciones
echo "<html><head><link rel=stylesheet type='text/css' href='../../lib/style.css'></head><body bgcolor='$bgcolor2'>\n";
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function JS(jsStr) { //v2.0
  return eval(jsStr)
}
//-->
</script>
<?
if (!$mode) { $mode = "view"; }
include ("./bancos_$mode.php");
?>
</body>
</html>
