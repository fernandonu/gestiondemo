<?
require_once("../../config.php");

$onclickcargar=$parametros['onclickcargar'];
$onclicksalir=$parametros['onclicksalir'];

$sql="select nombre,activo,id_firmante_lic from firmantes_lic where firmantes_lic.activo=1";
$resultado_firmantes=sql($sql) or fin_pagina();
echo $html_header;
?>
<form name="form_ventana" action="ventana_firmante.php" method="POST">
<table width=60% align=center class=bordes>
  <tr id=ma>
    <td>Elija Nuevo Firmante</td>
  <tr>
  </tr>
    <td align=center width=100%>
       <select name="select_firmante" size=5 style='width:60%'>
         <?
         while(!$resultado_firmantes->EOF)
         {
         ?>
         <option value="<?=$resultado_firmantes->fields['id_firmante_lic']; ?>"><?=$resultado_firmantes->fields['nombre'];?></option>
         <?
         $resultado_firmantes->MoveNext();
         }
         ?>
       </select>
    </td>
  </tr>
  <tr>
     <td align=center>
        <input type="button" name="boton" value="Guardar" onclick="<?=$onclickcargar;?>;window.close();"; style="cursor:hand;width:100">&nbsp;&nbsp;&nbsp;
        <input type="button" name="boton" value="Salir" onclick="<?=$onclicksalir; ?>" style="cursor:hand;width:100">
     </td>
  </tr>
</table>
</form>