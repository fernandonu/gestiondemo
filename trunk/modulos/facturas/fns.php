<?
/*
Autor: GACZ

MODIFICADA POR
$Author: cestila $
$Revision: 1.4 $
$Date: 2004/08/27 22:01:04 $
*/

//NOTA SE REQUIERE QUE ANTES SE INCLUYA LA LIBRERIA CON FUNCIONES ADODB

//elimina los campos demas en el arreglo filas 
//pone comillas necesarias y agrega el id_factura
function prepare_factura($id_factura,&$filas)
{
 while ($filas['cantidad']--)
 {
	$filas[$filas['cantidad']]['id_factura']=$id_factura;
 	$filas[$filas['cantidad']]['descripcion']="'".$filas[$filas['cantidad']]['descripcion']."'";
 	unset($filas[$filas['cantidad']]['subtotal']);
 }
 unset($filas['cantidad']);
}

//recupera los items de la pagina anterior
//o de la base de datos
function get_items($id_factura=false)
{
 global $db;
 $i=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if(!($id_factura==false))
 {
	 $query="SELECT * FROM facturas as f JOIN items_factura as i on ".
		   	 "i.id_factura=f.id_factura WHERE f.id_factura=$id_factura";
	 $datos=$db->Execute($query) or die($db->ErrorMsg(). "<br>$query");
	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	   	$items[$i]['id_item']=$datos->fields['id_item'];
	   	$items[$i]['id_renglones_oc']=$datos->fields['id_renglones_oc'];
	   	$items[$i]['id_producto']=$datos->fields['id_producto'];
	   	$items[$i]['cant_prod']=$datos->fields['cantidad'];
	   	$items[$i]['precio']=$datos->fields['precio_unitario'];   	
	   	$items[$i]['descripcion']=$datos->fields['descripcion_prod'];
	   	//$items[$i]['total_iva']=$datos->fields['total_iva'];
	   	$i++;
	   	$datos->MoveNext();
	 }
  	 $items['cantidad']=$i;
 }
 else 
 {	
	 $i=0;
	 //print_r($_POST);
	 while ($clave_valor=each($_POST))
	 {
		   if (is_int(strpos($clave_valor[0],"idp_")) || is_int(strpos($clave_valor[0],"idr_")))
		   {  
				 $posfijo=substr($clave_valor[0],4);
				 $items[$i]['id_item']=$_POST['idi_'.$posfijo];
				 $items[$i]['id_renglones_oc']=$_POST['idr_'.$posfijo] or $items[$i]['id_renglones_oc']='null';
				 $items[$i]['id_producto']=$_POST['idp_'.$posfijo] or $items[$i]['id_producto']='null';
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
function del_items($items,$id_factura)
{
	global $db;
	$items2=get_items($id_factura);
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
	$q= "delete from items_factura where id_factura=$id_factura AND id_item in ($borrar)";
	if ($borrar!="")
		$db->Execute($q) or die($q);
}


?>