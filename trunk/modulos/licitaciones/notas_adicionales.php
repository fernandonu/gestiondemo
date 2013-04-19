<?
/*
$Author: mari $
$Revision: 1.1 $
$Date: 2006/06/09 19:37:13 $

*/

require_once("../../config.php");
echo $html_header;
$id_licitacion=$parametros["id_licitacion"] or $_POST['id_licitacion'];

if ($_POST['aceptar'])  {
 $info=$_POST['contenido'];
 $id_licitacion=$_POST['id_licitacion'];
 
 $sql="update licitacion set notas_adicionales_lic='$info' where id_licitacion='$id_licitacion'";
 sql($sql,"update $sql") or fin_pagina(); 
 
?> 
<script> 
window.close();
</script>
<?
}


?>

<form name=form1 method=post action="notas_adicionales.php">
<input type=hidden name=id_licitacion value="<?=$id_licitacion?>">
<?
    $sql="select entidad.nombre as entidad,distrito.nombre as distrito,notas_adicionales_lic
          from licitacion 
          join entidad using(id_entidad) 
          join distrito using (id_distrito)
          where id_licitacion=$id_licitacion";
    $res_lic=sql($sql,"datos lciitacion") or fin_pagina();

  
      $contenido=$res_lic->fields['notas_adicionales_lic'];
  
   ?>
   <table width=80% align=center >
   <tr>
        <td align='center' colspan=2><font size=2> <b>ID Licitación:</b> </font> <font color="Blue" size=2><?=$id_licitacion?></font></td>
   </tr> 
   <tr>     
        <td><br> <b>Entidad: </b><?=$res_lic->fields['entidad']?></td>
        <td><br> <b>Distrito: </b><?=$res_lic->fields['distrito']?></td>
   </tr>
   </table>
   <br>
   <table width=80% align=center class=bordes>
   <tr>
   </tr>
     <tr id=mo>
       <td width=100%>Ingrese Notas Adicionales </td>
     </tr>
     <tr>
      <td align=center>
       <textarea name="contenido" rows=20 cols='120'><?=$contenido?></textarea>
      </td>
     </tr>
     <tr>
       <td align=center>
          <input type=submit name=aceptar value=Aceptar>
          &nbsp;
          <input type=button name=cancelar value=Cerrar onclick="window.close()">
       </td>
     </tr>
   </table>
</form>