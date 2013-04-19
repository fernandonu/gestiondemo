<?
/*
Autor: Lizi

MODIFICADA POR
$Author: elizabeth $
$Revision: 1.1 $
$Date: 2004/07/28 21:44:46 $
*/
require_once("../../config.php");
echo $html_header;
$variable=$parametros["var"];
$id=$parametros["id"];
$producto=$parametros["producto"];
?>
<center>
    <h4> Comentario del Renglon codigo: <?=$id?></h4><br>
	<h4> Producto: <?=$producto?>
    <p> 
      <textarea name="coment" cols="60" rows="7" wrap="PHYSICAL" id="coment" readonly><?=$variable?></textarea>
	</p>
    <p> 																																												
      <input name="boton" type="button" value="Cerrar" onclick="window.close();">
    </p>
</center>
</body>
</html>
