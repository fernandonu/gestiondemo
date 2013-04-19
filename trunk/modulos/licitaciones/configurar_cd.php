<?
include("../../config.php");

function   show_combo() {

echo "<select name='select_tipo'>";
echo "<option> </option>";
echo "<option value='oferta'> Oferta </option>";
echo "<option value='descripcion'> Descripcion </option>";
echo "<option value='folleto'> Folleto </option>";
echo "<option value='no exportar'> No Exportar </option>";

}

$id_licitacion=$parametros["ID"];

$campos="licitacion.id_licitacion,licitacion.id_entidad,licitacion.nro_lic_codificado,licitacion.ultimo_usuario_fecha,entidad.nombre,archivos.nombre as nombre_archivo, archivos.tipo,archivos.nombrecomp";
$query="SELECT $campos from licitacion join archivos using(id_licitacion)
        join entidad using(id_entidad) where id_licitacion=$id_licitacion";
//echo $query;
$resultados_licitacion=$db->Execute($query) or die($db->ErrorMsg().$query);
$entidad=$resultados_licitacion->fields['nombre'];
$nro_lic_cod=$resultados_licitacion->fields['nro_lic_codificado'];
$fecha=$resultados_licitacion->fields['ultimo_usuario_fecha'];

?>

<html>
  <head>
     <?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
    <style type="text/css">
    </style>
 </head>
 </head>
  <body bgcolor="#E0E0E0">
  <hr>
  <center>
<form name="form1" method="post" action="<?echo $link1 ?>">
  <table id=mo width='100%'>
   <tr>
     <td> <td><b> Licitacion Numero: </b><?=$id_licitacion?> </td>
     </tr>
  </table>
<br>

  <table id=ma>
       <td align='left'> <b> Entidad: </b></td><td><input type='text' name='text_entidad' value='<?=$entidad?>' size='35'> </td>
   </tr>
   <tr>
    <td align='left'> <b> Nro de Licitacion codificado:</b></td><td> <input type='text' name='text_numero_lic' value='<?=$nro_lic_cod?>' size='35'> </td>
   </tr>
   <tr>
    <td align='left'> <b> Fecha y Hora:</b></td><td><input type='text' name='text_fecha' value='<?=Fecha($fecha)?>' size='35'> </td>
   </tr>
   </table>
   </center>

<br>
<hr>
<div style="position:relative; width:100%; height:80%; overflow:auto;">   
<table width='100%'>
<tr id=mo>
 <center>
     <td width='75%'> <b>Listado de Archivos</b> </td>
     <td width='25%'> <b>Tipo</b></td>
</tr>
<?

   while (!$resultados_licitacion->EOF) {
     echo "<tr id=ma>";
     echo "<td align='left'>";
     echo $resultados_licitacion->fields['nombre_archivo'];
     echo "</td>";
     echo "<td>";
     show_combo();
     echo "</td>";
     echo "</tr>";
    $resultados_licitacion->MoveNext();
   }
?>


 </center>
</table>
</div>
<center>
<HR>

<input type='submit' name='Guardar' value='Guardar Configuracion'>
<input type='submit' name='CD' value='CD >>'>
</center>
</form>
</body>
</html>