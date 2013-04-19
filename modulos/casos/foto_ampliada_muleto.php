<?/*

----------------------------------------
 Autor: quique
 Fecha: 22/09/2005
----------------------------------------

MODIFICADA POR
$Author: ferni $
$Revision: 1.3 $
$Date: 2005/11/16 18:09:34 $
*/

include_once("../../config.php");
echo $html_header;
$id_muleto=$parametros["id_muleto"] or $id_muleto=$_POST["id_muleto"];
$archivo=$parametros["archivo"] or $nombre_producto=$_POST["archivo"];
$coment=$parametros["coment"] or $nombre_producto=$_POST["coment"];

?>
<style type="text/css">
.separador{border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:black}
</style>
<form name='form_archivos' action='ver_fotos_muletos.php' method=POST>
<?
if ($id_muleto){
$query="select * from casos.foto_muleto where id_muleto=$id_muleto";
$fotos=sql($query,"<br>Error al traer los datos de las fotos del producto<br>") or fin_pagina();
?>
<table align="center" width="75%" class="bordes">
 <tr  id=mo>
  <td colspan="4"><b>
   Foto del Muleto</b>
  </td>
 </tr>
 <tr>
  <td title="<?=$fotos->fields["nombre_archivo"]?>" align="center" class="separador" >
   
  <img src="./Fotos/<?=$id_muleto?>/<?=$archivo?>" height="500" width="750">

   <br>
    <b><?=$coment?></b>
   </td>

 </tr>
<tr>
<input type="hidden" name="id_muleto" value="<?=$id_prod_esp?>">
<input type="hidden" name="contador" value="<?=$cont--?>">
<td align="center">
<input type="button" name="Cerrar" value="Cerrar" onclick="window.close();">
</td>

</tr>
<?}
else {?>
<tr  id=mo>
  <td colspan="4">
  <b>Debe Subir una Foto</b>
  </td>
 </tr>
<?}?>

</table> 
