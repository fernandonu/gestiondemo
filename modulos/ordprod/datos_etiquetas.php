<?
/*
Autor: diegoinga
Modificado por
$Author: gabriel $
$Revision: 1.11 $
$Date: 2005/11/28 15:22:19 $
*/
require_once("../../config.php");
echo $html_header;
$producto=$_GET['posicion'];
?>
<script>
function control_datos(){
	var error=0;

	if (document.all.texto_titulo.value.length>21){
		document.all.texto_titulo.value=document.all.texto_titulo.value.substring(0,21);
	}
	if (document.all.texto_titulo.value.indexOf("\n")!=-1){
  	alert('No se puede ingresar un título con mas de una linea');
  	error=1;
  }
  if (document.all.coment.value.indexOf("\n")!=-1){
  	alert('No se puede ingresar una descripción con mas de una linea');
  	error=1;
  }
 	if(error==0){
 		window.opener.document.all.texto_titulo.value=document.all.texto_titulo.value;
 		window.opener.document.all.texto_descripcion.value=document.all.texto_descripcion.value;
  	window.close();
 	}else return 0;
}

function make_sugestion(txt){
	if (((pos=txt.toLowerCase().indexOf('enterprise'))!=-1)||((pos=txt.toLowerCase().indexOf('matrix'))!=-1))
		texto=txt.substring(pos, pos+21);
  else texto=txt.substring(0, 21);
  
  document.all.t_sugerencia.value=texto;
  return true;
}
</SCRIPT>
<form id="form1" method="POST" action="datos_etiquetas.php">
<table align="center" bgcolor="<?=$bgcolor_out?>">
<tr id=mo>
	<td align="center"><h4> Datos de La etiqueta </h4></td>
</tr>
<tr bgcolor="<?=$bgcolor_out?>">
	<td align=center>
		<h5><font color="Blue"> Este agregado se insertar&aacute; como dato para generar la etiqueta.</font></h5>
	</td>
</tr>
<tr>
	<td>
		<b>T&iacute;tulo del rengl&oacute;n:</b><br>
		<textarea name="coment" cols="75" rows="2" wrap="PHYSICAL" id="coment" readonly><?=(($parametros["titulo_renglon"])?$parametros["titulo_renglon"]:$_POST["coment"])?></textarea>
	</td>
</tr>
<tr><td><hr></td></tr>
<tr>
	<td>
		<b>T&iacute;tulo de la etiqueta:</b> 
		<input type="text" name="texto_titulo" value="" size="21" maxlength="21"><br>
		Sugerencia "<input type="text" name="t_sugerencia" value="<?=$_POST["t_sugerencia"]?>" readonly size="21" maxlength="21" style="border-style:none;text-align:center;background-color:'transparent';color:#2E5576;font-weight: bold;">"
		<img src="../../imagenes/up.gif" onclick="document.all.texto_titulo.value=document.all.t_sugerencia.value;" title="Usar sugerencia"><br>
		Tiene un m&aacute;ximo de 21 caracteres.
	</td>
</tr>
<tr>
	<td>
		<b>Descripci&oacute;n de la etiqueta:</b>
		<input type="text" name="texto_descripcion" value="" size="42" maxlength="42"><br>
		Tiene un m&aacute;ximo de 42 caracteres.
	</td>
</tr>
<tr><td><hr></td></tr>
<tr bgcolor="<?=$bgcolor_out?>">
	<td align=center>
		<input name="boton" type="button" value="Guardar" onclick="return control_datos();">
		&nbsp;
		<input name="boton" type="button" value="Cerrar" onclick="window.close();">
  </td>
</tr>
</table>
</form>
    <script>
    	document.all.texto_titulo.value=window.opener.document.all.texto_titulo.value;
    	make_sugestion(document.all.coment.value);
    	document.all.texto_descripcion.value=window.opener.document.all.texto_descripcion.value;
    </script>
<?
fin_pagina();
?>