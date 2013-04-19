<?
require_once("../../config.php");

echo $html_header;
$nro=$parametros['nro'];
$tipo=$parametros["tipo"];
?>
<script>
function control_comentario(){
if(document.all.coment.value.indexOf('"')==-1){
	opener.document.all.comentario_anular.value=document.all.coment.value;
	opener.document.all.anular_aux.value="true";
 	opener.document.all.remito.submit();
 	window.close();
}
else{
	alert("No ingrese comillas dobles en los comentarios");
}
}//fin funcion
</script>
<center>
    <?if ($tipo=='factura'){?>
	<h4> Justifique la Anulacion de la Factura Nº <?=$nro?></h4><br>
	<?}?>
	<?if ($tipo=='remito'){?>
	<h4> Justifique la Anulacion del Remito Nº <?=$nro?></h4><br>
	<?}?>
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