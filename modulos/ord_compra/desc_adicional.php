<?
/*
Autor: elizabeth

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.4 $
$Date: 2004/11/10 16:50:16 $
*/
require_once("../../config.php");
echo $html_header;
$producto=$_GET['posicion'];
//@nombres contiene los nombres de las variables en la ventana opener
$nombres=$_GET['nombres'];// es de la forma $nombres="nbre_descorig,nbre_descadic,nbre_concat";
$title=$_GET['title'] or $title=0; //si se pone o no la concatenacion en el title del obj donde queda la concatenacion
if ($nombres)
$nombres=split(",",$nombres);

$onclick['guardar']=$_GET['onclickguardar'] or $onclick['guardar']="control()";
$onclick['cerrar']=$_GET['onclickcerrar'] or $onclick['cerrar']="window.close()";
?>
<script>
function concatenar_desc(){
<?
	if (!is_array($nombres)) 
	{
?>	
		window.opener.document.all.h_desc_<?=$producto?>.value=document.all.coment.value;	
		window.opener.document.all.desc_<?=$producto?>.value = document.all.producto.value+" "+document.all.coment.value;
<?
	} 
	else
	{
?>		
		window.opener.document.all.<?=$nombres[1]?>.value=document.all.coment.value;	
		window.opener.document.all.<?=$nombres[2]?>.value=document.all.producto.value+' '+document.all.coment.value;
		<? if ($title) {?>
		window.opener.document.all.<?=$nombres[2]?>.title=document.all.producto.value+' '+document.all.coment.value;
		<?} ?>
<?		
	}
?>
	window.close()
}

function control(){
	    if (document.all.coment.value.indexOf("'")!=-1){
             alert('No se puede ingresar comentarios con comillas simples (\') ');
             return 0;
              }
        else if (document.all.coment.value.indexOf("\"")!=-1) {
             alert('No se puede ingresar comentarios con comillas dobles (") ');
             return 0;
               }
        else {
             concatenar_desc();
             return 1;   
            }
	}

</SCRIPT>
<table align="center">
<tr id=mo>
<td align="center"><h4> Descripción del Producto </h4></td>
</tr>
<tr id=ma>
<td align="center" ><h5><textarea name="producto" cols="60" rows="3" readonly></textarea></h5><br></td>
</tr>
</table>
<center>
    <h5> <font color="Blue"> Este agregado se insertará a continuación de la descripción original del producto </font> </h5>
    <p> 
      <textarea name="coment" cols="60" rows="7" wrap="PHYSICAL" id="coment"></textarea>
	</p>
    <p> 																																												
      <input name="boton" type="button" value="Guardar" onclick="<?=$onclick['guardar']?>">
      &nbsp;
      <input name="boton" type="button" value="Cerrar" onclick="<?=$onclick['cerrar']?>">
    </p>
    
</center>
</body>
    <script>
    <? if (!is_array($nombres))
    	{
    ?>
       document.all.producto.value=window.opener.document.all.desc_orig_<?=$producto?>.value;
       document.all.coment.value=window.opener.document.all.h_desc_<?=$producto?>.value;
    <? }
    	 else
    	 {
    ?>
       eval("document.all.producto.value=window.opener.document.all."+"<?=$nombres[0] ?>"+".value");
       eval("document.all.coment.value=window.opener.document.all."+"<?=$nombres[1]?>"+".value");
		<?    	 	
    	 }
    ?>
    </script>
</html>
