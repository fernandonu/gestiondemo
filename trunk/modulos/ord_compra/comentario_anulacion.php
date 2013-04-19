<?
/*
Autor: diegoinga

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2005/09/10 17:30:40 $
*/
require_once("../../config.php");

echo $html_header;
$nro_orden=$parametros['nro_orden'];
$tipo=$parametros["tipo"];
$titulo_pagina=$parametros["titulo_pagina"];
?>
<script>
function control_comentario()
{
if(document.all.coment.value.indexOf('"')==-1)
{<?
 if($tipo=="anular")
 {?>
 opener.document.all.comentario_anular.value=document.all.coment.value;
 opener.document.all.h_anular.value="Anular";
 opener.document.all.form1.submit();
 //alert(opener.document.all.comentario_anular.value);
 window.close();
 <?
 }
 else//$tipo=="rechazar"
 {?>
 opener.document.all.comentario_rechazar.value=document.all.coment.value;
 opener.document.all.h_rechazar.value="Rechazar";
 opener.document.all.form1.submit();
 //alert(opener.document.all.comentario_anular.value);
 window.close();
 <?
 }
 ?>
}
else
{
alert("No ingrese comillas dobles en los comentarios");
}
}//fin funcion
</script>
<center>
<?
if($tipo=="anular")
 $justif="la anulacion";
else
 $justif="el rechazo";
?>
    <h4> Justifique <?=$justif?> de la <?=$titulo_pagina?> Nº <?=$nro_orden?></h4><br>
	<p> 
      <textarea name="coment" cols="60" rows="7" id="coment"></textarea>
	  <script>
		  //document.all.coment.value=opener.document.all.<?=$variable?>.value;
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
