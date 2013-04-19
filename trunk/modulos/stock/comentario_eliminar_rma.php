<?
/*
Autor: diegoinga

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2006/03/31 20:54:56 $
*/
require_once("../../config.php");

echo $html_header;
$id_info_rma=$parametros['id_info_rma'];

?>
<script>
function control_comentario()
{
 if(document.all.coment.value=="")
 {
 	alert("Debe Ingresar una justificación para Eliminar este RMA del Sistema");
 }
 else if(document.all.coment.value.indexOf('"')==-1)
 {
   opener.document.all.comentario_eliminar.value=document.all.coment.value;
   opener.document.all.h_eliminar.value="EliminarRMA";
   opener.document.all.form1.submit();
   //alert(opener.document.all.comentario_anular.value);
   window.close();
 }
 else
 {
   alert("No ingrese comillas dobles en los comentarios");
 }
}//de function control_comentario()
</script>
<center>

    <h4> Justifique la eliminación del RMA Nº <?=$id_info_rma?></h4><br>
	<p>
      <textarea name="coment" cols="60" rows="7" id="coment"></textarea>
    </p>
    <p>
      <input name="boton" type="button" value="Guardar" onclick="control_comentario()">
      &nbsp;
      <input name="boton" type="button" value="Cerrar" onclick="window.close();">
    </p>
</center>
</body>
</html>
