<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2004/08/09 23:54:18 $
*/
require_once("../../config.php");


if ($_POST["aceptar"]=="Aceptar"){

         $valor=$_POST["valor_dolar"];
         if (!$valor)
                   error("Debe ingresar un valor dolar");
         if (!is_numeric($valor))
                   error("Debe ingresar un número");

         $comentarios=$_POST["comentarios"];
         $sql="update dolar_general set valor=$valor , comentario='$comentarios' ";
         if (!$error) {
         if ($db->execute($sql)) // or die($db->errormsg()."<br>".$sql);
                             $msg="Se actualizó el valor dolar con éxito";
                             else
                             $msg="No se Pudo actualizar el valor dolar";
         }
}


$sql="select * from dolar_general";
$resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
$valor_dolar_actual=$resultado->fields["valor"];
$comentario=$resultado->fields["comentario"];




echo $html_header;

if ($msg) {
?>
<table width=70% align=Center>
  <tr>
    <td><?=aviso($msg)?></font></td>
  </tr>
</table>
<?
}
?>
<form name=form1 method=post>
<table width=50% align=center cellspacing=1 cellpading=1 border=1 bgcolor=<?=$bgcolor2?>>
<tr>
   <td colspan=2 id=mo>Valor Dolar General</td>
</tr>
<tr>
   <td><b>Valor Dolar Actual:</b></td>
   <td align=center><b><?=formato_money($valor_dolar_actual)?></b></td>
</tr>
<tr>
    <td><b>Nuevo Valor Dolar:</b></td>
    <td align=center>
       <input type=text name=valor_dolar value="" size=4>
    </td>
</tr>
<tr id=ma_sf>
   <td colspan=2>Comentarios</td>
</tr>
<tr>
   <td colspan=2>
   <textarea name=comentarios rows=5 style="width:100%"><?=$comentario?></textarea>
   </td>
</tr>
<tr>
   <td align=center colspan=2>
   <input type=submit name=aceptar value=Aceptar>
   <input type=reset name=cancelar value=Cancelar>
   </td>
</tr>

</table>

</form>