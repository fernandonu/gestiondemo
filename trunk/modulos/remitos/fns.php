<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.7 $
$Date: 2006/01/09 20:08:19 $
*/

//NOTA SE REQUIERE QUE ANTES SE INCLUYA LA LIBRERIA CON FUNCIONES ADODB

//elimina los campos demas en el arreglo filas 
//pone comillas necesarias y agrega el id_factura
function prepare_remito($id_remito,&$filas)
{
 while ($filas['cantidad']--)
 {
	$filas[$filas['cantidad']]['id_remito']=$id_remito;
 	$filas[$filas['cantidad']]['descripcion']="'".$filas[$filas['cantidad']]['descripcion']."'";
 	unset($filas[$filas['cantidad']]['subtotal']);
 }
 unset($filas['cantidad']);
}

//recupera los items de la pagina anterior
//o de la base de datos
function get_items($id_remito=false)
{
 global $db;
 $i=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if(!($id_remito==false))
 {
	 $query="SELECT * FROM remitos as r JOIN items_remito as i on ".
		   	 "i.id_remito=r.id_remito WHERE r.id_remito=$id_remito";
	 $datos=$db->Execute($query) or die($db->ErrorMsg(). "<br>$query");
	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	   	$items[$i]['id_item']=$datos->fields['id_item'];
	   	if ($datos->fields['id_renglones_oc']) $items[$i]['id_renglones_oc']=$datos->fields['id_renglones_oc'];
	   	$items[$i]['cant_prod']=$datos->fields['cant_prod'];
	   	$items[$i]['precio']=$datos->fields['precio'];   	
	   	$items[$i]['descripcion']=$datos->fields['descripcion'];
	   	//$items[$i]['total_iva']=$datos->fields['total_iva'];
	   	$i++;
	   	$datos->MoveNext();
	 }
  	 $items['cantidad']=$i;
 }
 else 
 {	
	 $i=0;
	 while ($clave_valor=each($_POST))
	 {
		   if (is_int(strpos($clave_valor[0],"cant_")))
		   {  
				 $posfijo=substr($clave_valor[0],5);
				 $items[$i]['id_item']=$_POST['idi_'.$posfijo];
				 //if (is_int(strpos($clave_valor[0],"idr_"))) 
				 $items[$i]['id_renglones_oc']=$_POST['idr_'.$posfijo] or $items[$i]['id_renglones_oc']='null';
				 $items[$i]['cant_prod']=$_POST['cant_'.$posfijo];
				 $items[$i]['precio']=$_POST['precio_'.$posfijo];
				 $items[$i]['descripcion']=$_POST['desc_'.$posfijo];
				 $items[$i]['subtotal']=$_POST['subtotal_'.$posfijo];
		       $i++;
		   }
	 }
	 $items['cantidad']=$i;
 }
 return $items;
}
//elimina las filas de la orden 
//@items contiene todos los items que van a quedar
//@id_factura es el numero de factura por la cual buscar
function del_items($items,$id_remito)
{
	global $db;
	$items2=get_items($id_remito);
	$borrar="";
	for($i=0;$i < $items2['cantidad'];$i++)
	{
		for($j=0;$j < $items['cantidad'];$j++)
		{
			if ($items[$j]['id_item']==$items2[$i]['id_item'])
			 break;
		}
		if ($j==$items['cantidad'])
		 $borrar.=$items2[$i]['id_item'].",";
	}
	$borrar=substr($borrar,0,strlen($borrar)-1);
	$q= "delete from items_remito where id_remito=$id_remito AND id_item in ($borrar)";
	if ($borrar!="")
		$db->Execute($q) or die($q);
}


?>