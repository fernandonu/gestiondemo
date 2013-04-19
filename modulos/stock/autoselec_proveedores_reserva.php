<?
/*
AUTOR: MAC
FECHA: 01/09/04

$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2004/09/10 15:04:53 $
*/

require_once("../../config.php");

if($_POST['recargado']=="")
{$onclickcargar= $parametros['onclickcargar'];
 $link_reservar=encode_link("autoselec_proveedores_reserva.php",array("id_producto"=>$parametros['id_producto'],"id_deposito"=>$parametros['id_deposito'],"onclickcargar"=>$onclickcargar));		
 ?>
 <html>
  <body>
   <form name='form1'action="<?=$link_reservar?>" method="POST">
    <input type="hidden" name="cant_reserva">
    <input type="hidden" name="recargado" value="1">
   </form>
   <script>
    document.all.cant_reserva.value=window.opener.document.all.cant_reserv.value;
    document.form1.submit();
   </script>
  </body>
 </html>
 <?
}
else 
{
 echo $html_header;
 $cantidad=$_POST['cant_reserva'];
 $onclickcargar= $parametros['onclickcargar'];
 ?>
 <table width="100%">
  <tr>
   <td colspan="2">
    <input type="text" name="mensaje" class="text_6" size=45 value="Reservando Productos...">
    <input type="text" name="mensaje2" class="text_6" size=45 value="">
   </td>
  </tr>
  <tr>
   <td align="right">
    <br><input type="button" name="Ok" value="OK" style="width='80'" onclick="window.opener.<?=$onclickcargar?>;window.close();">
   </td>
   <td> 
    <br><input type="button" name="Cerrar" value="Cerrar" style="width='80'" onclick="window.close();">
   </td> 
  </tr>
 </table> 
 <?
 $proveedores_cantidad=stock_seleccionar_reserva($parametros['id_producto'],$parametros['id_deposito'],$cantidad);
 //si devolvio cero, no hay cantidad suficiente para reservar
 if($proveedores_cantidad===0)
 {?>
  <script>
   document.all.Ok.disabled=1;
   document.all.mensaje.value="La cantidad que intenta reservar es";
   document.all.mensaje2.value="mayor a la actualmente disponible en Stock";
  </script>
 <?
 }
 else 
 {?>
  <script>
   window.opener.document.all.proveedores_cantidad.value="<?=$proveedores_cantidad?>";
   document.all.mensaje.value="La cantidad a reservar es correcta.";
   document.all.mensaje2.value="Presione 'OK' para cargar el producto";
  </script>
 <?
 }
}//del else	
?>
</body>
</html>