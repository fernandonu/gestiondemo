<?
/*
$Author: mari $
$Revision: 1.2 $
$Date: 2004/09/09 19:01:51 $
*/
require_once("../../config.php");
echo $html_header;



?>
<form name='form' action="gen_graf_ensamblador.php" target="_blank" method="POST"> 
<!--<form name='form' action="grafica_ensamblador.php" target="_blank" method="POST"> -->

<?
$sql="select distinct id_ensamblador,ensamblador.nombre
      from  licitaciones.entrega_estimada 
      join ordenes.ensamblador using (id_ensamblador)
      join licitaciones.licitacion using (id_licitacion)
      where borrada='f' and finalizada=0 ";
$resultado=sql($sql) or fin_pagina();
?>
<div align="center">
<table align="center" cellpadding="2" class="bordes" width="75%">
<tr> <td id="mo" colspan="2" bgcolor="<?=$bgcolor3?>" align="center">ENSAMBLADORES </td></tr>

<?  while (!$resultado->EOF)
 {
 ?>
 <tr bgcolor=<?=$bgcolor_out?>>
 <td width="10%"><input type="radio" name='ensamblador' value="<?=$resultado->fields['id_ensamblador'];?>" onclick="document.all.gantt.disabled=false"></td>
 <td > <?=$resultado->fields['nombre']; ?></td>
 </tr>
 <?
 $resultado->MoveNext();
 }
 ?>
 
 </table>

</div>

<br>
<div align="center"> 
     <input type='submit' name='gantt' value='Diagrama de Gantt' disabled >
     <input type='button' name='cerrar' value='Cerrar' onclick='window.close()'></div>

</form>