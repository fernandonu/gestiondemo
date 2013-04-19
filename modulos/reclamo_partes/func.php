<?

/*AUTOR: MAC

$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2004/05/29 16:48:46 $
*/

//recupera los items ya sea de la BD si se pasa el id, o del post, si no se pasa
//es similar a la de Ordenes de Compra (fns.php) pero para reclamo de partes
function get_items_rp($id_reclamo_partes=false)
{
 global $db;
 $i=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if(!($id_reclamo_partes ==false))
 {
	 $query="select * from partes where id_reclamo_partes=$id_reclamo_partes";
        $datos=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer productos del reclamo de partes");
	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	   	$items[$i]['id_partes']=$datos->fields['id_partes'];
	   	$items[$i]['id_producto']=$datos->fields['id_producto'];
	   	$items[$i]['cantidad']=$datos->fields['cantidad'];
 	  	$items[$i]['descripcion']=$datos->fields['descripcion'];
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
		   if (is_int(strpos($clave_valor[0],"idp_")))
		   {  
				 $posfijo=substr($clave_valor[0],4);
				 $items[$i]['id_partes']=$_POST['idf_'.$posfijo];
				 $items[$i]['id_producto']=$_POST['idp_'.$posfijo];
				 $items[$i]['cantidad']=$_POST['cant_'.$posfijo];
				 $items[$i]['descripcion']=$_POST['desc_'.$posfijo];
		       $i++;
		   }
	 }
	 $items['cantidad']=$i;
 }
 return $items;
}

/***************************************************************
FUNCION que inserta las partes del reclamo seleccionado.

Para eso, borra las actuales e inserta las que estan en la tabla.
Asi evita controles de insersion o actualizacion.
****************************************************************/
function insertar_partes($id)
{global $db;
 $db->StartTrans();
 
 //primero borramos todas las partes de ese id
 $query="delete from partes where id_reclamo_partes=$id";
 $db->Execute($query) or die($db->ErrorMsg()."<br>Error al borrar partes del relcamo de partes");
 
 //luego insertamos los items que estran en la tabla
 $items=get_items_rp();	
 for($i=0;$i<$items['cantidad'];$i++)
 {if($items[$i]['id_producto'])
  {$query="insert into partes(id_reclamo_partes,id_producto,descripcion,cantidad)
          values($id,".$items[$i]['id_producto'].",'".$items[$i]['descripcion']."',".$items[$i]['cantidad'].") 
          ";
   $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar la parte $i del reclamo de partes <br>$query");
  }//de  if($items[$i]['id_producto'])
 }	
 $db->CompleteTrans(); 	
}//de function insertar_partes($id)

?>