<?
/*
$Author: mari $
$Revision: 1.3 $
$Date: 2006/06/09 18:50:58 $

*/

require_once("../../config.php");
echo $html_header;
$id_licitacion=$parametros["id_licitacion"] or $_POST['id_licitacion'];

if ($_POST['aceptar'])  {
 $info=$_POST['contenido'];
 $id_licitacion=$_POST['id_licitacion'];
 $sql="select informacion from ver_apertura where id_licitacion=$id_licitacion";
 $res=sql($sql,"select ver_apertura") or fin_pagina();
 if ($res->RecordCount() > 0) {
   $sql="update ver_apertura set informacion='$info' where id_licitacion='$id_licitacion'";
   sql($sql,"update en ver_apertura ") or fin_pagina(); 
 } else {
   $sql="insert into ver_apertura (id_licitacion,informacion) values ($id_licitacion,'$info')";
   sql($sql,"insert en ver_apertura ") or fin_pagina();
 }
 
?> 
<script> 
window.close();
</script>
<?
}


?>

<form name=form1 method=post action="datos_apertura.php">
<input type=hidden name=id_licitacion value="<?=$id_licitacion?>">
<?
    $sql="select fecha_apertura,entidad.nombre as entidad,distrito.nombre as distrito
          from licitacion 
          join entidad using(id_entidad) 
          join distrito using (id_distrito)
          where id_licitacion=$id_licitacion";
   $res_lic=sql($sql,"datos lciitacion") or fin_pagina();

   $sql="select informacion from ver_apertura where id_licitacion=$id_licitacion";
   $res=sql($sql,"buscar en ver_apertura") or fin_pagina();
   if ($res->RecordCount() >0 )  
      $contenido=$res->fields['informacion'];
   else $contenido="";
   ?>
   <table width=80% align=center >
   <tr>
        <td align='center' colspan=2><font size=2> <b>ID Licitación:</b> </font> <font color="Blue" size=2><?=$id_licitacion?></font></td>
   </tr> 
   <tr>     
        <td><br> <b>Entidad: </b><?=$res_lic->fields['entidad']?></td>
        <td><br> <b>Distrito: </b><?=$res_lic->fields['distrito']?></td>
   </tr>
   <tr>
      <td colspan=2> <b>Fecha Apertura: </b><?=fecha($res_lic->fields['fecha_apertura']) ?>
           &nbsp;&nbsp;&nbsp;<b> Hora Apertura:</b> <?=Hora($res_lic->fields['fecha_apertura'])?>
      </td>
   </tr>
   </table>
   <br>
   <table width=80% align=center class=bordes>
   <tr>
   </tr>
     <tr id=mo>
       <td width=100%>Ingrese información sobre la Apertura </td>
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