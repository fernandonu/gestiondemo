<?
/*
$Author: fernando $
$Revision: 1.3 $
$Date: 2006/06/03 16:37:49 $
*/

/*************************************************************
@param $id_muestra
@param $id_licitacion
@param $cantidad
@desc  funcion que me inserto o modifica la tabla productos_descontados
       cuando una muestra pasa a en curso, esta descuenta esos producots de la 
       orden de producion y se insertan en esta tabla para poder usarse despues
**************************************************************/
function actualiza_producto_descontado($id_muestra,$id_prod_esp,$cantidad){
global $_ses_user;
    
$fecha = date("Y-m-d");
$usuario = $_ses_user["name"];
    
$sql=" select id_productos_descontado from productos_descontado
       where id_muestra=$id_muestra and id_prod_esp=$id_prod_esp"; 
$res=sql($sql) or fin_pagina();       
   
if ($id_productos_descontado=$res->fields["id_productos_descontado"]){
    $campos=" cantidad=cantidad + $cantidad, usuario='$usuario', fecha='$fecha'";
    $sql=" update productos_descontado set $campos where id_productos_descontado=$id_productos_descontado";
    sql($sql) or fin_pagina();
       
   }
   else{
          $campos="id_muestra,id_prod_esp,cantidad,fecha,usuario";
          $values="$id_muestra,$id_prod_esp,$cantidad,'$fecha','$usuario'";
          $sql=" insert into productos_descontado ($campos) values ($values) ";
          sql($sql) or fin_pagina();
   }
    
  
} //de la funcion



//recupera los items ya sea de la BD si se pasa el id, o del post, si no se pasa
function get_items_ordprod($id_muestra=false)
{
 global $db;
 $i=0;
 $total_muestra=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if(!($id_muestra ==false))
 {
     
 	 $query="select ordprod_muestra.*,orden_de_produccion.id_licitacion as lic,orden_de_produccion.cantidad as cant_ord,desc_prod as producto
             from muestras.ordprod_muestra 
             left join ordenes.orden_de_produccion using (nro_orden)
             where id_muestra=$id_muestra";
     $datos=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer ordenes de la muestra");

	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	 	$items[$i]['id_producto']=$datos->fields['nro_orden'];
	    $items[$i]['nroorden']=$datos->fields['nro_orden'];
	    $items[$i]['cantidad']=$datos->fields['cant_ord'];
 	  	//$items[$i]['monto']=$datos->fields['monto'];
 	  	$items[$i]['lic']=$datos->fields['lic']; //licitacion asociada a la orden de produccion
        $items[$i]['producto']=$datos->fields['producto']; 
      //  $total_muestra+=$datos->fields['monto']*$datos->fields['cantidad'];
 	  	$i++;
	   	$datos->MoveNext();
	 }
  	 $items['cantidad_ord']=$i;
  	
 }
 else 
 {	 
	 $i=0;
	 while ($clave_valor=each($_POST))
	 {
		   if (is_int(strpos($clave_valor[0],"idp_")))
		   {  
				 $posfijo=substr($clave_valor[0],4);
				 $items[$i]['id_producto']=$_POST['idp_'.$posfijo];
				 $items[$i]['nroorden']=$_POST['nroorden_'.$posfijo];
				 $items[$i]['cantidad']=$_POST['cantmaq_'.$posfijo];
				// $items[$i]['monto']=$_POST['monto_'.$posfijo];
				 $items[$i]['lic']=$_POST['lic_'.$posfijo];
				 $items[$i]['producto']=$_POST['desc_'.$posfijo];
 	  	         //$total_muestra+=$_POST['monto_'.$posfijo]*$_POST['cantmaq_'.$posfijo];
 	  	         
		       $i++;
		   }
	 }
	 $items['cantidad_ord']=$i;
}
$items['total_muestra']=$total_muestra;
return $items;
}




/***************************************************************
FUNCION que inserta las ordenes de produccion de la muestra seleccionada.

Para eso, borra las actuales e inserta las que estan en la tabla.
Asi evita controles de insersion o actualizacion.
****************************************************************/
function insertar_ordprod($id) {
global $db;

$db->StartTrans();
 
 //primero borramos todas las partes de ese id
 $query="delete from ordprod_muestra where id_muestra=$id";
 $db->Execute($query) or die($db->ErrorMsg()."<br>Error al borrar las ordenes de la muestra");
 
 //luego insertamos los items que estan en la tabla
$items=get_items_ordprod();
//print_r($items);
for($i=0;$i<$items['cantidad_ord'];$i++)
 {
   if ($items[$i]['id_producto']!="") {
   $query="insert into ordprod_muestra(id_muestra,nro_orden,cantidad)
            values($id,".$items[$i]['nroorden'].",'".$items[$i]['cantidad']."') ";
   $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar el producto $i de la muestra <br>$query");
   }
 }	
 $db->CompleteTrans(); 	
}//de function insertar_ordprod($id)

?>