<?
/*
AUTOR: MAC

MODIFICADO POR:
$Author: mari $
$Revision: 1.16 $
$Date: 2007/01/05 18:51:38 $

*/

require_once("../../config.php");

$modo=$_GET['modo'] or $modo=$parametros['modo'] or $modo=$_POST['modo']  ;

if ($modo=="oc_compras")
	$modo="Orden de Compra";
else 
	$modo="Orden de Pago";

$nro_orden=$_POST['nro_orden'];

//traemos los depositos de la base de datos para generar los check para asociar
  $query="select * from depositos";
  $stocks=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los depositos");

if($_POST['boton_asociar']=="Asociar")
{
 switch	($_POST['radio_asociar'])
 {case "lic": $link=encode_link("../licitaciones/licitaciones_view.php",array("backto"=>"../ord_compra/ord_compra.php","nro_orden"=>$nro_orden,"pag"=>"asociar","_ses_global_extra"=>array()));
              header("Location:$link");
              break;
  case "pres": $link=encode_link("../presupuestos/presupuestos_view.php",array("backto"=>"../ord_compra/ord_compra.php","nro_orden"=>$nro_orden,"pag"=>"asociar","_ses_global_extra"=>array()));
              header("Location:$link");
              break;
  case "stec":$link=encode_link("../casos/caso_admin.php",array("backto"=>"../ord_compra/ord_compra.php","nro_orden"=>$nro_orden,"pag"=>"asociar","coradir_bs_as"=>"no"));
              header("Location:$link");
              break;
  case "gastos_servicio_tecnico":
              $link=encode_link("../casos/caso_ate.php",array("backto"=>"../ord_pago/ord_pago.php","nro_orden"=>$nro_orden,"pag"=>"asociar","_ses_global_extra"=>array(),"gastos_servicio_tecnico"=>1));
              header("Location:$link");
              break;
  case "otro":if ($modo=='Orden de Compra')
                 $link=encode_link("ord_compra.php",array("nro_orden"=>$nro_orden,"pagina"=>"asociar","licitacion"=>""));
              else  
                 $link=encode_link("../ord_pago/ord_pago.php",array("nro_orden"=>$nro_orden,"pagina"=>"asociar","licitacion"=>"")); 
              header("Location:$link");
              break;
  case "stk": $link=encode_link("ord_compra.php",array("nro_orden"=>$nro_orden,"pagina"=>"asociar","licitacion"=>"","flag_stock"=>"1"));
              header("Location:$link");
              break;
  /*case "prod":$link=encode_link("../ordprod/ordenes_ver.php",array("nro_orden"=>$nro_orden,"pag"=>"asociar",'back'=>"../ord_compra/ord_compra.php"));
              header("Location:$link");
              break;*/
  case "oc_internacional":
  			  $link=encode_link("ord_compra.php",array("nro_orden"=>$nro_orden,"pagina"=>"asociar","licitacion"=>"","internacional"=>1));
              header("Location:$link");
              break;
 }
}
?>
<html>
<head>
<title>
 Asociación Orden de Compra
</title>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
</head>
<?=$html_header?>
<br><br><br><br>
 <form name="form1" method="POST">
 <input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
 <input type="hidden" name="modo" value="<?=$modo?>">
 
 <table width="60%" align="center" class="bordes">
  <tr id=mo>
   <td>
    <font size=3>Asociar <?=$modo?> a:</font>
   </td>
  </tr>
  <?if ($modo=='Orden de Compra') {?>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[0].checked=true">
   <td>
    <input type="radio" name="radio_asociar" value="lic"> Licitación
   </td>
  </tr>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[1].checked=true">
   <td>
    <input type="radio" name="radio_asociar" value="pres"> Presupuesto
   </td>
  </tr>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[2].checked=true">
   <td>
    <input type="radio" name="radio_asociar" value="oc_internacional"> <font color='#00C021' ><b>Orden de Compra Internacional</b></font>
   </td>
  </tr>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[3].checked=true">
   <td>
    <input type="radio" name="radio_asociar" value="stec"> Servicio Técnico
   </td>
  </tr>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[4].checked=true">

    <td>
     <input type="radio" name="radio_asociar" value="stk"> Stock Coradir
    </td>

   </tr>
  <?
  $j=5;  //cantidad de radio
  }
  else {
  	?>
     <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[0].checked=true">
    <td>
    <input type="radio" name="radio_asociar" value="gastos_servicio_tecnico"> Honorarios  Servicios Técnicos
   </td>
   </tr>
  <? $j=1;
  }?>
  
   
<?/*
   <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[6].checked=true">
    <td>
    <input type="radio" name="radio_asociar" value="prod"> RMA de Producción
   </td>
  </tr>
  */?>
  <tr align="left" bgcolor=<?=$bgcolor_out?> onclick="document.all.radio_asociar[<?=$j?>].checked=true">
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