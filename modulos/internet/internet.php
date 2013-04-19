<?php
/*
$Author: nazabal $
$Revision: 1.3 $
$Date: 2003/07/08 19:43:06 $
*/

include "../../config.php";

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

function gen_tr_tag ($link) {
  global $cnr, $bgcolor1, $bgcolor2, $color_fila;
  if ($color_fila) { $color = $color_fila; }
  else { $color = $bgcolor1; }
  $tr_hover_on = "onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\" onClick=\"location.href = '$link'\"";
  echo "<tr bgcolor=$color $tr_hover_on>\n";
}
/*
function ConvFecha($fecha) {
	list($dia,$mes,$anio)=split("-", $fecha);
	return "$anio-$mes-$dia";
}

function check_fecha($fecha) {
    $fecha2=strtotime($fecha);
    $num1=($fecha2-intval(time()))/60/60/24;
//    $res=0;
    if ($num1 > 7) {
       $res=0;
    } elseif ($num1>=0 and $num1<=7) {
       $res=1;
    } else {
       $res=2;
    }
    return($res);
}
*/
function ActualizarEstado($id,$estado) {
	global $db;
	$sql = "update internet.internet set estado='$estado' where idcliente=$id;";
	$result=$db->Execute($sql);
	if (!$result) {
		Error("Error al actualizar el estado del cliente $id");
		return 0;
	}
	return 1;
}

if (!($EstActivo || $EstBorrado || $EstVencido || $EstCerrado || $EstGratis)) {
	$EstActivo=1;
	$EstVencido=1;
}

echo "<html><head><link rel=stylesheet type='text/css' href='$css_style'>$lang_cfg</head><body bgcolor='$bgcolor2'>\n";

if (!$mode) { $mode = "view"; }

// always include the options overview
include_once("./internet_$mode.php");
?>

</body>
</html>