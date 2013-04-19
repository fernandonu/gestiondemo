<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2004/06/17 21:55:28 $
*/
require_once("../../config.php");
$id_producto=$parametros["id_producto"];

$sql="select * from productos where id_producto=$id_producto";
$resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$desc_gral=$resultado->fields["desc_gral"];


$sql="select razon_social,h.fecha,h.usuario,h.precio from historial_precio h ";
$sql.=" join proveedor using(id_proveedor)";
$sql.=" where id_producto=$id_producto ";
$sql.=" order by fecha";
$resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());

echo $html_header;
?>
<table align="center" width="80%" border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0">
 <tr>
   <td id="ma">Historial de  Precios</td>
 </tr>
 <tr>
  <td>
     <table width=100%>
      <tr>
        <td id="ma_sf">Producto:</td>
        <td align=center><b><?=$desc_gral?></b></td>
      </tr>
     </table>
  </td>
 </tr>
 <tr>
   <td>
    <table width=100% align=center>
     <tr id=mo>
        <td>Fecha</td>
        <td>Proveedor</td>
        <td>Precio</td>
        <td>Usuario</td>
     </tr>
    <?
    $cantidad=$resultado->recordcount();
    for ($i=0;$i<$cantidad;$i++){

     $hora=substr($resultado->fields["fecha"],11,19);
    ?>
    <tr>
        <td><b><?=fecha($resultado->fields["fecha"])."      ".$hora;?></td>
        <td><b><?=$resultado->fields["razon_social"];?></td>
        <td><b><?=formato_money($resultado->fields["precio"]);?></td>
        <td><b><?=$resultado->fields["usuario"]?></td>
    </tr>
    <?
    $resultado->movenext();
    }//del for
    ?>
    </table>
   </td>
 </tr>
 <tr>
    <td align=center><input type=button name=cerrar value=Cerrar onclick="window.close()"></td>
 </tr>
</table>
