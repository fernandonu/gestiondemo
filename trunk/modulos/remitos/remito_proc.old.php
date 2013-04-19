<?PHP
/*
Author: GACZ
$Revision: 1.1 $
$Date: 2003/11/10 14:00:19 $
*/

require_once("../../config.php");

/********************
BUSCAR VARIABLES QUE CONTENGAN LOS ID_ITEM

*****************/
/*
echo "Valores enviados con el método POST:<br>";
reset ($HTTP_POST_VARS);
while (list ($clave, $val) = each ($HTTP_POST_VARS)) {
    echo "$clave => $val<br>";
}

//die();

echo "<BR>VALORES de LOS ITEMS <BR>";
$items=get_items();
while (($items['cantidad']))
{
echo "<br>pos =".--$items['cantidad'];
echo "<br>get_index=". get_index($items[$items['cantidad']]['id_item']);
echo "<br>id_item=".$items[$items['cantidad']]['id_item'];	
echo "<br>id_producto=".$items[$items['cantidad']]['id_producto'];	
echo "<br>cantidad=".$items[$items['cantidad']]['cant_prod'];	
echo "<br>descripcion=".$items[$items['cantidad']]['descripcion'];	
echo "<br>deposito=".$items[$items['cantidad']]['id_deposito']."<BR>";	

}
die();

/******************************************
ACTUALIZAR: actualiza todos los items
@items es un arreglo que contiene los items y TODOS los campos
@return retorna true si tuvo exito false si no
******************************************/
function actualizar($items)
{
global $db;
	for ($i=0; $i < $items['cantidad'];$i++)
   {
	 $query_update="UPDATE items_remito SET ".
	 "cant_prod=".$items[$i]['cant_prod'].", ".
	 "descripcion='".$items[$i]['descripcion']."', ".
	 "id_deposito=".$items[$i]['id_deposito'].", ".
	 "precio=".$items[$i]['precio'].
	 " WHERE id_item=".$items[$i]['id_item'];
    $db->Execute($query_update) or die($db->ErrorMsg()." en query_update de actualizar <br> $query_update");
   }
   return true;
}
/************************************************
GET_INDEX: retorna el indice dentro de la variable
           global @items que corresponde con el 
           @id del item
************************************************/
//POR AHORA no se usa en ningun lado
function get_index($id)
{
 global $items;
 $i=0;
 while ($i < $items[$i]['id_item'])
 {
 	if ($items[$i]['id_item']==$id)
 	 break;
 	$i++;
 }
 return $i;
} 

/***********************************************
GET_ITEMS: recupera todos los items del remito
           y toda la informacion sobre ellos
           los que vienen de la pagina anterior 
           si se da el @id_remito los recupera 
           de la BD
           
@return un arreglo multidimension con todos los items
***********************************************/
function get_items($id_remito=0)
{
 global $db;
 $i=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if($id_remito==0)
 {
 	 while ($clave_valor=each($_POST))
	 {
	   if (is_int(strpos($clave_valor[0],"id_i")))
	   {  
	   	$items[$i]['id_item']=$clave_valor[1];
	   	$items[$i]['id_producto']=$_POST['id_p'.$items[$i]['id_item']];
	   	$items[$i]['id_deposito']=$_POST['lista_'.$items[$i]['id_item']];   	
	   	$items[$i]['id_proveedor']=$_POST['id_prov'.$items[$i]['id_item']];   	
	   	$items[$i]['cant_prod']=$_POST['cant_'.$items[$i]['id_item']];
	   	$items[$i]['descripcion']=$_POST['desc_'.$items[$i]['id_item']];   	
	   	$items[$i]['precio']=$_POST['precio_'.$items[$i]['id_item']];   	
	   	$items[$i]['subtotal']=$_POST['subtotal_'.$items[$i]['id_item']];   	
	   	$items[$i]['chk']=$_POST['chk_'.$items[$i]['id_item']];   	
	   	$i++;
	   }
	 }
 }
 else 
 {
	 $query="SELECT * FROM facturacion.remitos as r JOIN items_remito as i on ".
		   	 "r.id_remito=i.id_remito WHERE r.id_remito=$id_remito";
	 $datos=$db->Execute($query) or die($db->ErrorMsg(). "<br>$query");
	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	   	$items[$i]['id_item']=$datos->fields['id_item'];
	   	$items[$i]['id_producto']=$datos->fields['id_producto'];
	   	$items[$i]['id_deposito']=$datos->fields['id_deposito'];   	
	   	$items[$i]['id_proveedor']=$datos->fields['id_proveedor'];
	   	$items[$i]['cant_prod']=$datos->fields['cant_prod'];
	   	$items[$i]['descripcion']=$datos->fields['descripcion'];
	   	$items[$i]['precio']=$datos->fields['precio'];
	   	$i++;
	   	$datos->MoveNext();
	 }
 }
 
 $items['nuevo']['id_producto']=$_POST['id_p'];
 $items['nuevo']['cant_prod']=$_POST['cant_'];
 $items['nuevo']['descripcion']=$_POST['desc_'];
 $items['nuevo']['precio']=$_POST['precio_'];
 $items['nuevo']['subtotal']=$_POST['subtotal_'];
 $items['nuevo']['chk']=$_POST['chk_'];
 $items['nuevo']['id_proveedor']=$_POST['proveedor'];

 $items['cantidad']=$i; //no se cuenta el nuevo item
 return $items;
}
/*********************************************************
nuevo_item: añade un item nuevo al remito
@return 1 si el item se añadio
@return 0 si el (producto en el mismo deposito ya se habia añadido), no se añade 
***********************************************************/
function nuevo_item()
{
 global $db;
 global $id_deposito,$id_producto,$id_remito,$cantidad,$precio,$descripcion,$id_proveedor;
	
 if ($id_deposito!="" && $id_remito!="" && $id_producto!="" && $cantidad!="" && $id_proveedor!="")
 {
     $tabla="items_remito";
      //$clave_tabla="(id_deposito,id_remito,id_producto)";
	  $campos="(id_deposito,id_remito,id_producto,cant_prod,descripcion,precio,id_proveedor)";
	  $valores="($id_deposito,$id_remito,$id_producto,$cantidad,'$descripcion',$precio,$id_proveedor)";
	  $query_insert="INSERT INTO $tabla $campos VALUES $valores";
	  $db->Execute($query_insert) or die($db->ErrorMsg()."<br>$query_insert");
 }
}


/***************************************
update_ni()
*********************************************************/


/*********************************************************
CONTROLAR LAS VARIABLES QUE VIENEN POR POST, 
SE DEBEN INICIALIZAR COMO ABAJO
*********************************************************/
//DATOS DEL REMITO se extraen del arreglo POST
extract($_POST,EXTR_SKIP);
//traemos el nombre del cliente seleccionado
$c_nombre=$_POST['c_nbre'];
/*$query="select nombre from clientes where id_cliente=$c_nombre";
$res=$db->Execute($query) or die($db->ErrorMsg());
$c_nombre=$res->fields['nombre'];
*/
$usuario=$_ses_user_login;

//datos del nuevo item
$id_deposito=$_POST['lista_'];
$cantidad=$_POST['cant_'];
$descripcion=$_POST['desc_'];
$id_remito=$_POST['id_remito'];
$id_producto=$_POST['id_p'];
$precio=$_POST['precio_'];
$id_proveedor=$_POST['proveedor'];


/************************************************************
$do; //variable que me dice que joraca debe hacer la pagina
$do=1; //añadir item Y reenviar a buscar items
$do=2; //guardar remito NO TERMINADO
$do=3; //terminar remito
$do=4; //anular remito SOLO CUANDO YA SE HABIA CREADO
$do=5; //eliminar items
$do=6; //default reenviar a la pagina de llenar_remito
************************************************************/

if ($boton=="Terminar")
 $do=3;
elseif ($boton=="Anular")
 $do=4;
elseif ($boton=="Eliminar")
 $do=5;
elseif ($boton=="Añadir Producto")
 $do=1;
elseif ($boton=="Guardar")
 $do=2;
elseif ($boton=="Cerrar")
 $do=6;

switch ($do)
{
  case 5: //BORRA LOS ITEMS SELECCIONADOS
		  $query_del="DELETE FROM items_remito where id_item=";
		  $items=get_items();
		  $borrar_count=0;
		  for ($i=0; $i < $items['cantidad']; $i++)
		   if ($items[$i]['chk']==1)
		     $borrar_count++;
		 
		  $ejecutar=0;
		  while ((--$items['cantidad']) >= 0)
		  {
             if ($items[$items['cantidad']]['chk']==1)
             {  
		  	    if (--$borrar_count == 0)
			  	  $query_del.=$items[$items['cantidad']]['id_item'];
			    else 
			      $query_del.=$items[$items['cantidad']]['id_item']." OR id_item=";
			      
			    $ejecutar=1;
             }
		  }
		  if ($ejecutar)
		    $db->Execute($query_del) or die($db->ErrorMsg(). " en query_delete<br>$query_del");
          $parametros['remito']=$id_remito;
		  if (!($items['nuevo']['chk']==1))
		  {
		  	  $parametros['producto']=$items['nuevo']['id_producto'];
		  	  $parametros['proveedor']=$items['nuevo']['id_proveedor'];
		  }
  		  $pagina="remito_nuevo.php";
		  $link=encode_link($pagina,$parametros);
		  header("location: $link"); 
		  break;
  case 1: //nuevo item Y REENVIA A LA PAGINA DE DIEGO
  		  if ($nuevo)
 		  {//si es el primer item entonces insertar nuevo remito
	 		  $tabla="facturacion.remitos";	 
	 		  $campos="(nro_remito,nro_pedido,tipo_venta,cliente1,direccion,cuit_cliente1,iva_tasa,iva_cond,
	 		  nro_iib,otros,cliente2,tipo_doc_c2,nro_doc_c2,usuario,fecha_llenado,estado)";
	 		  $valores="('$nro_remito','$pedido','$venta','$c_nombre','$c_dir','$c_cuit',
	 		  $c_iva,'$c_cond','$c_iib','$otros','$recib_ap,$recib_nbre','$recib_tipodoc',
	 		  '$recib_nrodoc','$usuario','".date("Y/m/j H:i:s")."','N')";
	 		  $query_insert="INSERT INTO $tabla $campos VALUES $valores"; 
	 		  $db->Execute($query_insert) or die($db->ErrorMsg()."en query_insert". $query_insert);
	 		  /*$campos="(nro_remito,nro_pedido,tipo_venta,cliente2,tipo_doc_c2,nro_doc_c2,usuario,fecha_llenado,estado,id_cliente)";
	 		  $valores="('$nro_remito','$pedido','$venta','$recib_ap,$recib_nbre','$recib_tipodoc','$recib_nrodoc','$usuario','".date("Y/m/j H:i:s")."','N',$c_nombre)";
	 		  $query_insert="INSERT INTO $tabla $campos VALUES $valores";
	 		  $db->Execute($query_insert) or die($db->ErrorMsg()."en query_insert". $query_insert);*/
	 		  $query_remito="SELECT max(id_remito) from facturacion.remitos";
	 		  $remito=$db->Execute($query_remito) or die($db->ErrorMsg()."en query_remito<br>".$query_remito);
	 		  $id_remito=$remito->fields['max'];
 		  }
          nuevo_item();
 		  if ($items > 0)
		  {//actualizar los demas en caso de que se modifique el comentario
		  	actualizar(get_items());
		  }
		  $pagina="../general/productos1.php";
		  $pagina_volver="../remitos/remito_nuevo.php";
		  $parametros=array("remito"=>$id_remito,"modulo"=>"remito","nombre_pagina"=>$pagina_volver);
		  $link=encode_link($pagina,$parametros);
		  header("location: $link"); 
		  break;
	
  case 3: //TERMINAR
		  //PRIMERO GUARDA EN CASO DE QUE EL USUARIO NO LO HAYA HECHO	
		  $items=get_items();
		  //añade el ultimo item añadido
        nuevo_item();
        if ($items > 0)
		  {//actualizar los demas en caso de que se modifique el comentario
		  	actualizar($items);
		  }

      	$db->BeginTrans();
           $tabla="facturacion.remitos";	 
	 	     /*$campos="nro_pedido='$pedido',tipo_venta='$venta',".
	 		  "cliente2='$recib_ap,$recib_nbre',tipo_doc_c2='$recib_tipodoc',".
	 		  "nro_doc_c2='$recib_nrodoc',estado='P',id_cliente=$c_nombre";*/
	 		  
	 		  $campos="nro_remito='$nro_remito',nro_pedido='$pedido',tipo_venta='$venta',
	 		  cliente1='$c_nombre',direccion='$c_dir',cuit_cliente1='$c_cuit',iva_tasa=$c_iva,iva_cond='$c_cond',
	 		  nro_iib='$c_iib',otros='$otros',".
	 		  "cliente2='$recib_ap,$recib_nbre',tipo_doc_c2='$recib_tipodoc',".
	 		  "nro_doc_c2='$recib_nrodoc',estado='P'";
	 		 $query_update="UPDATE $tabla SET $campos where id_remito=$id_remito"; 
	 		  $db->Execute($query_update) or die($db->ErrorMsg()."en query_update <br>". $query_update);

      	//descuenta de stock SOLO CUANDO LO TERMINA
      	 /*$query_stock="SELECT * FROM stock";
	      $datos_stock=$db->Execute($query_stock) or die($db->ErrorMsg() ." en query_stock");
	      $items=get_items();
          
	      for ($i=0; $i < $items['cantidad']; $i++)
	      { 
	      	while (!$datos_stock->EOF) 
	      	{
	      		$id_producto=$items[$i]['id_producto'];
	      		$id_deposito=$items[$i]['id_deposito'];
	      		$id_proveedor=$items[$i]['id_proveedor'];
	      		$cant=$items[$i]['cant_prod'];
 	      	   if ($datos_stock->fields['id_producto']==$id_producto &&
		      	    $datos_stock->fields['id_deposito']==$id_deposito &&
		      	    $datos_stock->fields['id_proveedor']==$id_proveedor)
		      	{
		      		$nueva_cantidad=$datos_stock->fields['cant_disp']-$cant;
		      		if ($nueva_cantidad>=0)
		      		{
					    $query_update="UPDATE stock SET cant_disp=$nueva_cantidad".
				       " WHERE id_producto=$id_producto AND ".
				       " id_deposito=$id_deposito AND ".
				       " id_proveedor=$id_proveedor";
				       $db->Execute($query_update) or die($db->ErrorMsg() ." en query_update<br>$query_update");
		      		}
		      		else
		      		{ 
		      			$db->CommitTrans(false);
		      			break 2;
		      		}
		      	}
		      	$datos_stock->MoveNext();
	      	}
	      	$datos_stock->MoveFirst();
	      }*/

	      $query_update="UPDATE facturacion.remitos SET fecha_entrega='".date("Y/m/j H:i:s").
	      "' WHERE id_remito=$id_remito";
	      $db->Execute($query_update) or die($db->ErrorMsg() ." en query_update<br>$query_update");
  	      if ($db->transCnt > 0)
  	      {
  	      	$db->CommitTrans(true);
  		      require("pdf.php");
  	      }
  			else 
  			{
  				$msg="<script>alert('No se pudo terminar el remito pruebe otra vez') </script>";	
  			}
  		  $pagina="remito_nuevo.php";
  		  $pagina_volver="remito_nuevo.php";
		  $parametros=array("remito"=>$id_remito,"nombre_pagina"=>$pagina_volver,"msg"=>$msg);
		  $link=encode_link($pagina,$parametros);
		  //decode_link($link);
		  header("location: $link"); 

	      break;
  case 2: //guardar
          
		  if (!$nuevo)
		  {
		  	  $tabla="facturacion.remitos";	 
	 		  /*$campos="nro_pedido='$pedido',tipo_venta='$venta',id_cliente='$c_nombre',".
	 		  "cliente2='$recib_ap,$recib_nbre',tipo_doc_c2='$recib_tipodoc',".
	 		  "nro_doc_c2='$recib_nrodoc'";*/
	 		  
	 		  $campos="nro_remito='$nro_remito',nro_pedido='$pedido',tipo_venta='$venta',
	 		  cliente1='$c_nombre',direccion='$c_dir',cuit_cliente1='$c_cuit',iva_tasa=$c_iva,iva_cond='$c_cond',
	 		  nro_iib='$c_iib',otros='$otros',".
	 		  "cliente2='$recib_ap,$recib_nbre',tipo_doc_c2='$recib_tipodoc',".
	 		  "nro_doc_c2='$recib_nrodoc',estado='N'";
	 		  $query_update="UPDATE $tabla SET $campos where id_remito=$id_remito"; 
	 		  $db->Execute($query_update) or die($db->ErrorMsg()."en query_update <br>". $query_update);
		  }
		  else 
		  {
	 		  $tabla="facturacion.remitos";	 
	 		  /*$campos="(nro_remito,nro_pedido,tipo_venta,cliente2,tipo_doc_c2,nro_doc_c2,usuario,fecha_llenado,estado,id_cliente)";
	 		  $valores="('$nro_remito','$pedido','$venta','$recib_ap,$recib_nbre','$recib_tipodoc',
	 		  '$recib_nrodoc','$usuario','".date("Y/m/j H:i:s")."','N',$c_nombre)";*/
	 		  
	 		  $campos="(nro_remito,nro_pedido,tipo_venta,cliente1,direccion,cuit_cliente1,iva_tasa,iva_cond,
	 		  nro_iib,otros,cliente2,tipo_doc_c2,nro_doc_c2,usuario,fecha_llenado,estado)";
	 		  $valores="('$nro_remito','$pedido','$venta','$c_nombre','$c_dir','$c_cuit',
	 		  $c_iva,'$c_cond','$c_iib','$otros','$recib_ap,$recib_nbre','$recib_tipodoc',
	 		  '$recib_nrodoc','$usuario','".date("Y/m/j H:i:s")."','N')";
	 		  $query_insert="INSERT INTO $tabla $campos VALUES $valores"; 
	 		  $db->Execute($query_insert) or die($db->ErrorMsg()."<br>$query_insert");
	 		  $query_remito="SELECT max(id_remito) from facturacion.remitos";
	 		  $remito=$db->Execute($query_remito) or die($db->ErrorMsg(). "<br>$query_remito");
	 		  $id_remito=$remito->fields['max'];
		  }
          //añade el ultimo item añadido
          nuevo_item(); 
          if ($items > 0)
		  {//actualizar los demas en caso de que se modifique el comentario
		  	 actualizar(get_items());
		     //print_r(get_items());
		  }
		  $pagina="remito_nuevo.php";
  		  $pagina_volver="remito_nuevo.php";
		  $parametros=array("remito"=>$id_remito,"nombre_pagina"=>$pagina_volver);
		  $link=encode_link($pagina,$parametros);
		  //decode_link($link);
		  header("location: $link"); 
		  break;
	case 4://Anular solo cuando ya se termino el remito
	       
	       $items=get_items();           
			//consulta y actualiza
		   for ($i=0; $i < $items['cantidad']; $i++)
	       {
			$query_stock="SELECT * FROM stock WHERE ";
	       	$query_stock.="(id_producto=".$items[$i]['id_producto'].
         	 " AND id_deposito=".$items[$i]['id_deposito'].
         	 " AND id_proveedor=".$items[$i]['id_proveedor'].")"; 
            $stock=$db->Execute($query_stock) or die($db->ErrorMsg(). "<br>$query_stock");
            $query_update="UPDATE stock SET cant_disp=cant_disp+".$items[$i]['cant_prod'].
	       	" WHERE id_producto=".$items[$i]['id_producto'].
	       	" AND id_deposito=".$items[$i]['id_deposito'].
	       	" AND id_proveedor=".$items[$i]['id_proveedor'];
			$db->Execute($query_update) or die($db->ErrorMsg(). "<br>$query_update");             
	       }
	       $query_update="UPDATE facturacion.remitos SET estado='A' WHERE id_remito=$id_remito";
	       $remito=$db->Execute($query_update) or die($db->ErrorMsg(). "<br>$query_update");
           $pagina="remito_nuevo.php";
  		   $pagina_volver="remito_nuevo.php";
		   $parametros=array("remito"=>$id_remito,"nombre_pagina"=>$pagina_volver);
		   $link=encode_link($pagina,$parametros);
		   //decode_link($link);
		   header("location: $link"); 
           break;	
   case 6: //cerrar remito
		  	  $tabla="facturacion.remitos";	 
	 		  $campos="cliente2='$recib_ap,$recib_nbre',tipo_doc_c2='$recib_tipodoc',".
	 		  "nro_doc_c2='$recib_nrodoc',estado='C'";
	 		  $query_update="UPDATE $tabla SET $campos where id_remito=$id_remito"; 
	 		  $db->Execute($query_update) or die($db->ErrorMsg()."en query_update <br>". $query_update);
  		   $pagina="remito_nuevo.php";
	 		$pagina_volver="remito_nuevo.php";
		   $parametros=array("remito"=>$id_remito,"nombre_pagina"=>$pagina_volver);
		   $link=encode_link($pagina,$parametros);
		   //decode_link($link);
		   header("location: $link"); 
         break;	
   		
}
?>