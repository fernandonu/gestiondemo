<?/*

----------------------------------------
 Autor: quique
 Fecha: 22/09/2005
----------------------------------------

MODIFICADA POR
$Author: enrique $
$Revision: 1.1 $
$Date: 2005/10/11 18:59:23 $
*/

include_once("../../config.php");
echo $html_header;
$id_prod_esp=$parametros["id_prod_esp"] or $id_prod_esp=$_POST["id_prod_esp"];
$nombre_producto=$parametros["nombre_producto"] or $nombre_producto=$_POST["nombre_producto"];
$archivo=$parametros["archivo"] or $nombre_producto=$_POST["archivo"];
$coment=$parametros["coment"] or $nombre_producto=$_POST["coment"];

?>
<style type="text/css">
.separador{border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:black}
</style>
<form name='form_archivos' action='ver_fotos_productos.php' method=POST>
<?
$query="select * from foto_producto where id_prod_esp=$id_prod_esp";
$fotos=sql($query,"<br>Error al traer los datos de las fotos del producto<br>") or fin_pagina();
?>
<table align="center" width="75%" class="bordes">
 <tr  id=mo>
  <td colspan="4"><b>
   <?=$nombre_producto?></b>
  </td>
 </tr>
 <tr>
  <td title="<?=$fotos->fields["nombre_archivo"]?>" align="center" class="separador" >
   
  <img src="./Fotos/<?=$id_prod_esp?>/<?=$archivo?>" height="500" width="750">

   <br>
    <b><?=$coment?></b>
   </td>

 </tr>
<tr>
<input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
<input type="hidden" name="nombre_producto" value="<?=$nombre_producto?>">
<input type="hidden" name="contador" value="<?=$cont--?>">
<td align="center">
<input type="button" name="Cerrar" value="Cerrar" onclick="window.close();">
</td>

</tr>
</table> 