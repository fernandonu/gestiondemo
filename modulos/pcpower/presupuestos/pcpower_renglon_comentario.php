<?
/*AUTOR: MAC
  Fecha: 14/10/04

$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2004/10/20 14:35:33 $
*/

require_once("../../../config.php");
echo $html_header;
$variable=$parametros["var"];
$id=$parametros["id"];
$producto=$parametros["producto"];
?>
<script>
function control_comentario()
{
if(document.all.coment.value.indexOf('"')==-1)
{opener.document.all.<?=$variable?>.value=document.all.coment.value;
 window.close();
}
else
{
alert("No ingrese comillas dobles en los comentarios");
}
}//fin funcion
</script>
<center>
    <h4> Comentario del Renglon codigo: <?=$id?></h4><br>
	<h4> Producto: <?=$producto?>
    <p> 
      <textarea name="coment" cols="60" rows="7" wrap="PHYSICAL" id="coment">
	  </textarea>
	  <script>
		  document.all.coment.value=opener.document.all.<?=$variable?>.value;
	  </script>
    </p>
    <p> 																																												
      <input name="boton" type="button" value="Guardar" onclick="control_comentario()">
      &nbsp;
      <input name="boton" type="button" value="Cerrar" onclick="window.close();">
    </p>
</center>
</body>
</html>
