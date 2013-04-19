<?php
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2003/07/28 19:22:19 $
*/

$path_pre="../";
$include_path = $path_pre."lib/lib.inc.php";
include_once $include_path;

$estados = array(
	"0" => array(
		"texto"  => "En&nbsp;curso",
		"imagen" => "e-blanco.gif",
		"color"  => "#FFFFFF"
		),
	"1" => array(
		"texto"  => "Entregada",
		"imagen" => "e-celeste.gif",
		"color"  => "#1AD7FF"
		),
	"2" => array(
		"texto"  => "Presuntamente&nbsp;ganada",
		"imagen" => "e-amarillo.gif",
		"color"  => "#FFFF00"
		),
	"3" => array(
		"texto"  => "Preadjudicada",
		"imagen" => "e-verdec.gif",
		"color"  => "#00FF00"
		),
	"4" => array(
		"texto"  => "Impugnada&nbsp;por&nbsp;nosotros",
		"imagen" => "e-rojo.gif",
		"color"  => "#FF7878"
		),
	"5" => array(
		"texto"  => "Perdida",
		"imagen" => "e-azul.gif",
		"color"  => "#0000FF"
		),
	"6" => array(
		"texto"  => "Robada",
		"imagen" => "e-negro.gif",
		"color"  => "#000000"
		),
	"7" => array(
		"texto"  => "Orden&nbsp;de&nbsp;compra",
		"imagen" => "e-verde.gif",
		"color"  => "#00AA00"
		)
);

echo "<html><head><link rel=stylesheet type='text/css' href='$css_style'>$lang_cfg\n";
echo "</head><body bgcolor=\"$bgcolor3\">\n";

//$barra = "<table cellspacing=5 cellpadding=5 align=center><tr>\n";
$barra = "";
if (!$cmd) {
	$cmd="proximas";
	$_GET["cmd"]="proximas";
}
if ($cmd == "proximas") {
	$lic_menuid="ma";
	$op = ">";
	if ($up == "") {
		$up = 1;
	}
}
else { $lic_menuid="mo"; }
$barra .= "<td width=16.5% id=$lic_menuid><a id=$lic_menuid href='licitaciones.php?mode=view&cmd=proximas&sort=2'>$lic_8</a></td>\n";
if ($cmd == "presentadas") {
	$lic_menuid="ma";
	$op = "<=";
	if ($up == "") {
		$up = 0;
	}
}
else { $lic_menuid="mo"; }
$barra .= "<td width=16.5% id=$lic_menuid><a id=$lic_menuid href='licitaciones.php?mode=view&cmd=presentadas&sort=2'>$lic_7</a></td>\n";
if ($cmd == "historial") {
	$lic_menuid="ma";
	$op = "<=";
	if ($up == "") {
		$up = 0;
	}
}
else { $lic_menuid="mo"; }
$barra .= "<td width=16.5% id=$lic_menuid><a id=$lic_menuid href='licitaciones.php?mode=view&cmd=historial&sort=2'>$lic_36</a></td>\n";
if ($cmd == "newlic") { $lic_menuid="ma"; }
else { $lic_menuid="mo"; }
$barra .= "<td width=16.5% id=$lic_menuid><a id=$lic_menuid href='licitaciones.php?mode=forms&cmd=newlic&cmd1=newlic'>$lic_1</a></td>\n";
if ($cmd == "stats") { $lic_menuid="ma"; }
else { $lic_menuid="mo"; }
$barra .= "<td width=16.5% id=$lic_menuid><a id=$lic_menuid href='licitaciones.php?mode=stats&cmd=stats'>Estadísticas</a></td>\n";
if ($cmd == "configuracion") { $lic_menuid="ma"; }
else { $lic_menuid="mo"; }
$barra .= "<td width=16.5% id=$lic_menuid><a id=$lic_menuid href='licitaciones.php?mode=forms&cmd=configuracion&cmd1=configuracion'>$lic_30</a></td>\n";
$barra .= "</tr>";//</table>\n";

if (!$mode) { $mode = "view"; }
// if the call is coming from the index, change it to the nromal mode variable
if ($mode_index) $mode = $mode_index;

/*
echo "<form action='licitaciones.php' method='post'>";
echo "<input type=hidden name=cmd value='$cmd'>\n";
//echo "<input type=hidden name=cmd1 value='$cmd1'>\n";
echo "<input type=hidden name=mode value='$mode'>\n";
echo "<table width=100% border=0 cellspacing=5 cellpadding=5 bgcolor=$bgcolor3 align=center>\n";
echo "<tr><td colspan=6 align=center>\n";
if ($cmd == "proximas" or $cmd == "presentadas" or $cmd == "historial") {
	echo "<a href='$doc/licitaciones.html' target='_blank'><b>Licitaciones</b></a>&nbsp;&nbsp;\n";
	echo "Buscar: <input type='text' name='keyword' value='$keyword' size=20 maxlength=20>\n";
	echo "en: <select name='filter'>\n";
	echo "<option value='all'";
	if (!$filter) echo " selected";
	echo ">Todos los campos\n";
	echo "<option value='id'";
	if ($filter == "id") echo " selected";
	echo ">ID de Licitación\n";
	echo "<option value='numero'";
	if ($filter == "numero") echo " selected";
	echo ">Número\n";
	echo "<option value='distrito'";
	if ($filter == "distrito") echo " selected";
	echo ">Distrito\n";
	echo "<option value='entidad'";
	if ($filter == "entidad") echo " selected";
	echo ">Entidad\n";
	echo "<option value='comentarios'";
	if ($filter == "comentarios") echo " selected";
	echo ">Comentarios\n";
	echo "<option value='mantoferta'";
	if ($filter == "mantoferta") echo " selected";
	echo ">Mantenimiento de oferta\n";
	echo "<option value='formapago'";
	if ($filter == "formapago") echo " selected";
	echo ">Forma de pago\n";
	echo "<option value='moneda'";
	if ($filter == "moneda") echo " selected";
	echo ">Moneda\n";
	echo "</select>&nbsp;\n";

	echo "Estado: <select name='status'>\n";
	echo "<option value='all' selected>Todos\n";
	foreach ($estados as $est => $arr) {
		echo "<option value=$est";
		if ("$est" == "$status") { echo " selected"; }
		echo ">{$estados[$est][texto]}";
	}
	echo "</select>";

	echo "&nbsp;&nbsp;Mostrar:\n";
	// items per page
	if (!$perpage) { $perpage = 20; } // set default per page
	echo "<select name='perpage'>\n";
	for ($i = 1; $i <= 5; $i++) {
		$j = $i * 10;
		echo "<option value='$j'";
		if ($j == $perpage) { echo " selected"; }
		echo ">$j\n";
	}
	echo "</select> $items \n";
	echo "&nbsp;<input type=image src='$img_path/los.gif' border=0 id=tr>&nbsp;</form>\n";
}
echo "</td></tr>$barra</table><br>\n";
*/
//echo $barra;
echo "<table width=100% border=0 cellspacing=5 cellpadding=5 bgcolor=$bgcolor3 align=center>\n";
echo "$barra</table>\n";
include_once("./licitaciones_$mode.php");

?>
</body>
</html>