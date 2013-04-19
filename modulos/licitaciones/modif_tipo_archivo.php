<?php
require_once ("../../config.php");
echo $html_header;

$id_archivo=$parametros["id_archivo"] or $id_archivo=$_POST["id_archivo"];
$id_tipo_post=$_POST["tipo"];
if ($_POST["aceptar"]){
                      if ($id_tipo_post && $id_archivo){
                         $sql=" update archivos set id_tipo_archivo=$id_tipo_post where idarchivo=$id_archivo";
                         sql($sql) or fin_pagina();
                         Aviso("Se realizo la modificación con éxito");
                      }
                      else
                        {
                        Aviso("No se puede modificar el tipo de archivo");
                        }
                      //echo $sql;
                      }


$sql="select * from tipo_archivo_licitacion where activo=1";
$result=sql($sql) or fin_pagina();
?>
<form name='form1' method='post' action='modif_tipo_archivo.php'>
<input type=hidden name=id_archivo value="<?=$id_archivo?>">
  <table width=50% class=bordes align=center>
     <tr id=ma><td>Modificación del tipo de Archivo</td></tr>
     <tr id=mo> <td align=center>Elija el tipo de archivo </td></tr>
     <tr>
         <td align=center>
            <select name=tipo size=6>
              <?
              for($i=0;$i<$result->recordcount();$i++){
               $id_tipo=$result->fields["id_tipo_archivo"];
               $tipo=$result->fields["tipo"];
              ?>
              <option value='<?=$id_tipo?>'><?=$tipo?></option>
              <?
              $result->movenext();
              }
              ?>
            </select>
         </td>
     </tr>
    <tr>
       <td align=center>
       <input type=submit name="aceptar"  value="Aceptar">

       &nbsp;
       <input type=button name="cancelar" value="Cancelar" onclick='window.close()'>
       </td>
    </tr>
  </table>
</form>