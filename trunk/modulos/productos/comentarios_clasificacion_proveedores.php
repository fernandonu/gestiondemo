<?php
  /*
$Author: fernando $
$Revision: 1.1 $
$Date: 2005/03/08 18:54:19 $
*/
include("../../config.php");
$id_historial=$parametros["id_historial"];
$sql="select id_historial,fecha,nombre,apellido,clasificado,comentario
             from historial_proveedor
             left join usuarios on login=usuario
             where id_historial=$id_historial";
$resultado=sql($sql) or fin_pagina();
echo $html_header;
?>
<form name=form1>
<table width=80% align=center class=bordes>
 <tr>
   <tr><td colspan=2 id=mo>Descripcion del Comentario </td></tr>
   <td width='20%' align=left id=ma_sf><b> Usuario </b></td>
   <td> <?=$resultado->fields["apellido"].", ".$resultado->fields["nombre"]?></td>
 </tr>
 <tr>
  <td align=left id=ma_sf><b>Fecha</b></td>
  <td><?=fecha($resultado->fields["fecha"])?></td>
 </tr>
 <tr><td align=left id=ma_sf><b>Paso a </b></td><td><?=$resultado->fields["clasificado"]?></td></tr>
 <tr>
   <td align=left id=ma_sf valign=top><b>Comentario</b></td>
   <td><textarea rows='5' style='width:100%'><?=$resultado->fields["comentario"]?></textarea></td>
 </tr>
<tr><td colspan=2 align=center><input type=button name=cerrar value=Cerrar onclick="window.close()"></td></tr>
</table>

</form>
