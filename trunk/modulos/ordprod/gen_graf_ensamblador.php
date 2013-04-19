<? 
/*
$Author: mari $
$Revision: 1.3 $
$Date: 2004/09/09 14:55:20 $
*/
require_once("../../config.php");
echo $html_header;

$id_ensamblador=$_POST['ensamblador'];
echo "<input type='button' name='cerrar' value='Cerrar' onclick='window.close()'>";
echo "<div align='center'><b>SEGUIMIENTO DE ARMADO DE COMPUTADORAS CDR</b></div>";
 echo "<br>";
    $link=encode_link("grafica_ensamblador.php",array("id_ensamblador"=>$id_ensamblador));
    echo "<div align='center'><img src='$link' border=0 align=top></div>\n";


?>