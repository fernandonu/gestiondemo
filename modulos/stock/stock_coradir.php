<?php
/*
AUTOR: MAC
FECHA: 29/05/06

MODIFICADO POR:
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2006/07/03 21:13:52 $
*/

require_once("../../config.php");

echo $html_header;
?>

<link rel="STYLESHEET" type="text/css" href="<?=$html_root?>/lib/dhtmlXTree.css">
<script  src="<?=$html_root?>/lib/dhtmlXCommon.js"></script>
<script  src="<?=$html_root?>/lib/dhtmlXTree.js"></script>

<script>
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla,nro)
{
 oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 if (obj_tabla.style.display=='none'){
 		obj_tabla.style.display='inline';
    oimg.show=0;
    oimg.src=img_ext;
    tree.findItem('Todos',0,1);
 }
 else{
 	obj_tabla.style.display='none';
    oimg.show=1;
		oimg.src=img_cont;
		document.frames['frame_archivos'].frame_stock_completo.id_tipo_prod.value='todos';
	 	document.frames['frame_archivos'].frame_stock_completo.submit();
 }
}//de function muestra_tabla(obj_tabla,nro)
</script>

<form name='arbol' action='stock_coradir.php' method='POST'>
<table class='bordes' width=99% cellspacing=2 height="522px">
<tr>
<td valign="top" class='bordes'>
	<table height="100%">
		<tr>
			<td id="mo" valign="top">
	 			<img id="imagen_1" src="<?=$img_ext?>" border=0 title="Ocultar Tipos de Productos" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.directorios,1);">
	 		</td>
		</tr>
	</table>
</td>

<td valign="top" width="25%" id="directorios">
	<table class='bordes' width=100% cellspacing=2 >
	 <tr>
	 	<td id="mo">
	 		Tipos de Productos
	 	</td>
	 </tr>
	 <tr>
	 		<td colspan="2">
	 			<hr>
	 			<div id="treeboxbox_tree" style="width:240;height:450"></div>
	 			<script>
	 				function tonclick(id)
	 				{
	 					if(id=="raiz")
	 					{
	 						document.frames['frame_stock_completo'].document.location.href="stock_completo.php?id_tipo_prod=0&modo=por_tipo";
	 					}
	 					else
	 					{
	 					    document.frames['frame_stock_completo'].document.location.href="stock_completo.php?id_tipo_prod="+id+"&modo=por_producto";
	 					}
	 				}
					function tondblclick(id){
						//alert("Item "+tree.getItemText(id)+" was doubleclicked");
					}
					function tondrag(id,id2){
						return true;
					}
					function tonopen(id,mode){
						return true;
					}
					function toncheck(id,state){
						//alert("Item "+tree.getItemText(id)+" was " +((state)?"checked":"unchecked"));
					}

					tree=new dhtmlXTreeObject("treeboxbox_tree","100%","100%",0);
					tree.setImagePath("../../imagenes/tree/");
					tree.enableCheckBoxes(0);
					tree.enableDragAndDrop(0);
					tree.setOnOpenHandler(tonopen);
					tree.setOnClickHandler(tonclick);
					tree.setOnCheckHandler(toncheck);
					tree.setOnDblClickHandler(tondblclick);
					tree.setDragHandler(tondrag);
					tree.enableSmartXMLParsing(1);

					tree.loadXML("tipo_prod.xml");

				</script>
	 		</td>
	 </tr>
	</table>
</td>
<td valign="top" width="100%" class="bordes">
		<iframe name="frame_stock_completo" width="100%" height="100%" allowTransparency=true marginwidth=0 marginheight=0 frameborder=0
		id="frame_stock_completo" align='center' src='stock_completo.php'></iframe>
</td>
</tr>
</table>
</form>
</body>
</html>