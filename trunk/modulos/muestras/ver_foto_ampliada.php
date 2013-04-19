<?php
/* 

----------------------------------------
 Autor: quique
 Fecha: 22/09/2005
----------------------------------------

MODIFICADA POR
$Author: fernando $
$Revision: 1.1 $
$Date: 2006/06/03 16:38:11 $
*/

include_once("../../config.php");

$id_foto_muestra=$parametros["id_foto_muestra"] or $id_foto_muestra=$_POST["id_foto_muestra"];
$id_muestra=$parametros["id_muestra"] or $id_muestra=$_POST["id_muestra"];

$sql=" select nombre_archivo from foto_muestra where id_foto_muestra=$id_foto_muestra";
$res=sql($sql) or fin_pagina();
echo $html_header;
?>
<form name=form1 method=post action="ver_foto_ampliada.php">
  <table align=center width=95% class=bordes>
     <tr id=mo><td>Foto Ampliada</td></tr>
     <tr>
        <td align=center>
          <img width=800 height=600 src="<?="./fotos/$id_muestra/".$res->fields["nombre_archivo"]?>">
        </td>
     </tr>
     <tr> 
       <td align=Center>
         <input type=button name=cerrar value=Cerrar onclick="window.close()">
       </td>
     </tr>
  </table>
</form>