<?

//retorna un arreglo con los precios asignados para un producto 
//segun los disitinto proveedores
function recuperar_precios($id_prod) {
$sql="select id_proveedor,precio,observaciones,usuario,fecha
       from precios where id_producto=$id_prod order by id_producto, id_proveedor";
$res_precios=sql($sql,"precios ") or fin_pagina();
$precios="";

 while (!$res_precios->EOF) {
 	$id_prov=$res_precios->fields['id_proveedor'];
    $precios[$id_prov]['id_proveedor']=$id_prov;
    $precios[$id_prov]['precio']=$res_precios->fields['precio'];
    $precios[$id_prov]['observaciones']=$res_precios->fields['observaciones'];
    $precios[$id_prov]['usuario']=$res_precios->fields['usuario'];
    $precios[$id_prov]['fecha']=$res_precios->fields['fecha'];
 	
 $res_precios->MoveNext();	
 }
 
  return $precios;
}

//retorna un arreglo datos del stock asignados para un producto 
//segun los distintos proveedores y depositos
function recuperar_stock($id_prod) {
//datos de stock del producto
 $sql="select id_deposito,id_producto,id_proveedor,cant_disp,comentario,last_user,
       last_modif,comentario_inventario
       from stock where id_producto=$id_prod order by id_producto,id_proveedor,id_deposito";
 $res_stock=sql($sql,"stock") or fin_pagina();
 $stock="";
 while (!$res_stock->EOF) {
 	$id_proveedor=$res_stock->fields["id_proveedor"];
 	$id_deposito=$res_stock->fields["id_deposito"];
 	
    $stock[$id_proveedor][$id_deposito]["id_proveedor"]=$id_proveedor;
    $stock[$id_proveedor][$id_deposito]["id_deposito"]=$id_deposito;
    $stock[$id_proveedor][$id_deposito]["cant_disp"]=$res_stock->fields["cant_disp"];
    $stock[$id_proveedor][$id_deposito]["comentario"]=$res_stock->fields["comentario"];
    $stock[$id_proveedor][$id_deposito]["last_user"]=$res_stock->fields["last_user"];
    $stock[$id_proveedor][$id_deposito]["last_modif"]=$res_stock->fields["last_modif"];
    $stock[$id_proveedor][$id_deposito]["comentario_inventario"]=$res_stock->fields["comentario_inventario"];
 
 $res_stock->MoveNext();
 }
return $stock;
}


//verifica que exista el precio para el nuevo producto
//verifica si existe la nupla en stock para el nuevo producto
//si no existe se crea la entrada

//por ejemplo el producto a borrar es el 44 con provedor 618 y deposito 10
// verifica si existe la nupla (77,618,10) donde el 77 es el producto que queda (id_nuevo)

// por ejemplo el producto a borrar es el 44 con provedor 618 y deposito 10
// verifica si existe la nupla (77,618,10) donde el 77 es el producto que queda (id_nuevo)
function verificar_prod($id_nuevo,$id_viejo,$id_proveedor,$id_deposito) {
	global $precios_nuevo,$precios_viejo,$stock_nuevo,$stock_viejo;

//$precios_nuevo -> tiene los precios del producto que queda
//$precios_viejo -> tiene los precios del producto que se borra

if ($precios_nuevo[$id_proveedor]["id_proveedor"] == ""  ) { 

// si no existe el precio para el proveedor y el nuevo producto, donde 
// el proveedor corresponde a la nupla del producto que se va a borrar
// creo una entrada e la tabla precio
   
  $precio=$precios_viejo[$id_proveedor]['precio'];
  $observaciones=$precios_viejo[$id_proveedor]['observaciones']; 
  $usuario=$precios_viejo[$id_proveedor]['usuario'];
  $fecha=$precios_viejo[$id_proveedor]['fecha'];
  
  $sql2="insert into precios (id_proveedor,id_producto,precio,observaciones,usuario,fecha) 
  values ($id_proveedor,$id_nuevo,$precio,";
  
  if ($observaciones=="") $sql2.="null,";
     else  $sql2.="'$observaciones',";
  if ($usuario=="") $sql2.="null,";
     else  $sql2.="'$usuario',";
  if ($fecha=="") $sql2.="null";
     else  $sql2.="'$fecha'";
  
  $sql2.=")";
 
  sql($sql2,"insert precios") or fin_pagina();
  
  //actualizar $precios_nuevo
    $precios_nuevo[$id_proveedor]['id_proveedor']=$id_proveedor;
    $precios_nuevo[$id_proveedor]['precio']=$precio;
    $precios_nuevo[$id_proveedor]['observaciones']=$observaciones;
    $precios_nuevo[$id_proveedor]['usuario']=$usuario;
    $precios_nuevo[$id_proveedor]['fecha']=$fecha; 
   
}


if ( $stock_nuevo =="" || $stock_nuevo[$id_proveedor][$id_deposito]["id_proveedor"] == "") {

  $cant_disp=0;
  //1$cant_disp=$stock_viejo[$id_proveedor][$id_deposito]['cant_disp'];
  $comentario=$stock_viejo[$id_proveedor][$id_deposito]['comentario'];
  $last_user=$stock_viejo[$id_proveedor][$id_deposito]['last_user'];
  $last_modif=$stock_viejo[$id_proveedor][$id_deposito]['last_modif'];
  $comentario_inventario=$stock_viejo[$id_proveedor][$id_deposito]['comentario_inventario'];
  
  $sql4="insert into stock (id_deposito,id_producto,id_proveedor,cant_disp,comentario,
         last_user,last_modif,comentario_inventario)
         values($id_deposito,$id_nuevo,$id_proveedor,$cant_disp,";
  if ($comentario == "") $sql4.="null,";
      else $sql4.="'$comentario',";
  if ($last_user == "") $sql4.="null,";
        else $sql4.="'$last_user',";
  if ($last_modif == "") $sql4.="null,";
        else $sql4.="'$last_modif',";  
  if ($comentario_inventario == "") $sql4.="null";
        else $sql4.="'$comentario_inventario'";        
          
  $sql4.=")";
 
  sql($sql4,"inserta en stock ") or fin_pagina();

  //actualizo el arreglo de stock del producto que queda
  $stock_nuevo[$id_proveedor][$id_deposito]["id_proveedor"]=$id_proveedor;
  $stock_nuevo[$id_proveedor][$id_deposito]["id_deposito"]=$id_deposito;
  $stock_nuevo[$id_proveedor][$id_deposito]['cant_disp']=$cant_disp;
  $stock_nuevo[$id_proveedor][$id_deposito]['comentario']=$comentario;
  $stock_nuevo[$id_proveedor][$id_deposito]['last_user']=$last_user;
  $stock_nuevo[$id_proveedor][$id_deposito]['last_modif']=$last_modif;
  $stock_nuevo[$id_proveedor][$id_deposito]['comentario_inventario']=$comentario_inventario;
  
} 

 
}  //fin de verificar_prod

?>