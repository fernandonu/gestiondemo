<?/*

----------------------------------------
 Autor: MAC
 Fecha: 22/09/2005
----------------------------------------

MODIFICADA POR
$Author: ferni $
$Revision: 1.2 $
$Date: 2005/11/16 14:39:21 $
*/

include_once("../../config.php");
echo $html_header;
$id_muleto=$parametros["id_muleto"] or $id_muleto=$_POST["id_muleto"];
if($_POST["Borrar"]=="Borrar")
{
	$s=1;
	$cant_guardar=$_POST["contador"];
	while($s<=$cant_guardar)
	{  
	if ($_POST['chequeado_'.$s])
		{
		$codigo=$_POST["id_muleto"];		
		$nombre=$_POST['chequeado_'.$s];
		//if (unlink(UPLOADS_DIR."/ord_compra/archivos_subidos/$archivo_comp"))
		if (unlink("./Fotos/$codigo/$nombre"))
		{
		$del_foto="delete  from casos.foto_muleto  where id_muleto=$codigo and nombre_archivo='$nombre'";
		sql($del_foto,"No se pudo dar de baja la foto")or fin_pagina();
		}
		}
		$s++;
	}
}


//traemos los datos de las fotos subidas para el producto pasado como parametro
$query="select * from foto_muleto where id_muleto=$id_muleto";
$fotos=sql($query,"<br>Error al traer los datos de las fotos del producto<br>") or fin_pagina();
?>
<style type="text/css">
.separador{border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:black}
</style>
<form name='form_archivos' action='ver_fotos_muleto.php' method=POST>
<table align="center" width="75%" class="bordes">
 <tr  id=mo>
  <td colspan="4"><b>
   <?echo "Fotos del Muleto";?></b>
    
 
  </td>
 </tr>
<?
$cont=1;
while (!$fotos->EOF)
{?>
 <tr>
  <td title="<?=$fotos->fields["nombre_archivo"]?>" align="center" class="separador" >
   <?$link_foto=encode_link("foto_ampliada_muleto.php",array("id_muleto"=>$id_muleto,"archivo"=>$fotos->fields['nombre_archivo'],"coment"=>$fotos->fields["comentario_foto"]))?>
   <img src="./Fotos/<?=$id_muleto?>/<?=$fotos->fields['nombre_archivo']?>" width="150" height="150" style='cursor: hand;' onclick="window.open('<?=$link_foto?>')">
   <br>
   <b><?=$fotos->fields["comentario_foto"]?></b> 
   </td>
  <td width="1%" class="separador">
  <input type="checkbox" name="chequeado_<? echo $cont; ?>" value="<?=$fotos->fields["nombre_archivo"]?>">
  </td> 
 <?
 $fotos->MoveNext();
 $cont++;
 if(!$fotos->EOF)
 {
 ?>
 <td title="<?=$fotos->fields["nombre_archivo"]?>" align="center" class="separador" >
   <?$link_foto=encode_link("foto_ampliada_muleto.php",array("id_muleto"=>$id_muleto,"archivo"=>$fotos->fields['nombre_archivo'],"coment"=>$fotos->fields["comentario_foto"]))?>
   <img src="./Fotos/<?=$id_muleto?>/<?=$fotos->fields['nombre_archivo']?>" width="150" height="150" style='cursor: hand;' onclick="window.open('<?=$link_foto?>')">
   <br>
   <b><?=$fotos->fields["comentario_foto"]?></b> 
   </td>
  <td width="1%" class="separador">
  <input type="checkbox" name="chequeado_<? echo $cont; ?>" value="<?=$fotos->fields["nombre_archivo"]?>">
  </td> 
 
 <?
 }
 ?>
 </tr>
 <?
$fotos->MoveNext();
 $cont++; 
}//de while(!$fotos->EOF)
?>

<tr>
<input type="hidden" name="id_muleto" value="<?=$id_muleto?>">
<input type="hidden" name="contador" value="<?=$cont--?>">
<td align="center" colspan="2">
<input type="button" name="Cerrar" value="Cerrar" onclick="window.close();">
</td>
<td align="center" colspan="2">
<input type="submit" name="Borrar" value="Borrar"> 
</td>
</tr>
</table> 