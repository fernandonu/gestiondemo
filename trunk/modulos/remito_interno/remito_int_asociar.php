<?
/*
AUTOR: Cesar

MODIFICADO POR:
$Author: cesar $
$Revision: 1.1 $
$Date: 2004/08/18 15:40:21 $

*/

require_once("../../config.php");

//$nro_orden=$_POST['nro_orden'];

//traemos los depositos de la base de datos para generar los check para asociar
//  $query="select * from depositos";
//  $stocks=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los depositos");

if($_POST['boton_asociar']=="Asociar")
{
 switch	($_POST['radio_asociar'])
 {case "lic": $link=encode_link("../licitaciones/licitaciones_view.php",array("backto"=>"../remito_interno/remito_int_nuevo.php","id_licitacion"=>$licitacion,"pag"=>"asociar","_ses_global_extra"=>array()));
              header("Location:$link");
              break;

  case "otro":$link=encode_link("remito_int_nuevo.php",array("nro_orden"=>$nro_orden,"pagina"=>"asociar","licitacion"=>""));
              header("Location:$link");
              break;

 /* case "ordprod":$link=encode_link("../ordprod/ordenes_ver.php",array("nro_orden"=>$nro_orden,"pag"=>"asociar",'back'=>"../remito_interno/remito_int_nuevo.php"));
              header("Location:$link");
              break;*/
 }
}
?>
<html>
<head>
<title>
 Asociación Remito Interno
</title>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
</head>
<?=$html_header?>
<br><br><br><br>
 <form name="form1" method="POST">
 <input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
 <table width="60%" align="center" class="bordes">
  <tr id=mo>
   <td>
    <font size=3>Asociar Remito Interno a:</font>
   </td>
  </tr>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[0].checked=true">
   <td>
    <input type="radio" name="radio_asociar" value="lic"> Licitación
   </td>
  </tr>
  <!--<tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[1].checked=true">
   <td>
    <input type="radio" name="radio_asociar" value="ordprod"> Orden de Producción
   </td>
  </tr>-->
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[1].checked=true">
    <td>
    <input type="radio" name="radio_asociar" value="otro"> Otro
   </td>
  </tr>

  <tr bgcolor=<?=$bgcolor3?>>
   <td align="center">
   <br>
    <input type="submit" name="boton_asociar" value="Asociar">
   </td>
  </tr>
 </table>
 </form>
</body>
</html>