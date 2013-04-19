<?php
/*
$Author: diegoinga $
$Revision: 1.58 $
$Date: 2004/09/15 19:21:07 $
*/

require_once("../../config.php");
require_once("funciones.php");
$nro_licitacion=$parametros["licitacion"];
$renglon = $parametros['renglon'];
$titulo = $_POST['titulo'];
$codigo_renglon = $_POST['codigo_renglon'];
$ganancia= $_POST['ganancia'];
$cantidad = $_POST['cantidad'];
$usuario_crea = $_ses_user_name;
$usuario_time = date("Y-m-d H:i:s");

$etap= ($_POST['select_etap']==0 || !isset($_POST['select_etap']) )?"NULL":$_POST['select_etap'];

if ($_POST['sin_descripcion']) $sin_descripcion=1;
                               else $sin_descripcion=0;

//////////////////////////////////
$cons="select * from licitaciones.licitacion ";
$cons.="left join licitaciones.entidad using (id_entidad) ";
$cons.="left join licitaciones.distrito using (id_distrito) where id_licitacion=$nro_licitacion";
$res=$db->execute($cons) or die ($cons);
//cambiar esto por una consulta por si llegan a cambiar de lugar (fila) el nombre
//del distrito
if($res->fields['nombre']=="Buenos Aires - GCBA")
  $id_dis=$res->fields['id_distrito'];
else
  $id_dis="";
//
if ($_POST['producto']=="Computadora Enterprise"){
   if ($id_dis==2)
      subir_folletos($nro_licitacion,1);
   else
      subir_folletos($nro_licitacion,2);
}
/////////////////////////////////



//modifico renglon
$db->StartTrans();
$sql="update renglon set titulo = '$titulo',codigo_renglon = '$codigo_renglon',cantidad = $cantidad,ganancia = $ganancia,usuario = '$usuario_crea',usuario_time = '$usuario_time',sin_descripcion=$sin_descripcion,id_etap=$etap where id_renglon = $renglon ";
$db->execute($sql) or die($db->errorMsg()."<br>". $sql);
$sql="delete from log_renglon where id_renglon = $renglon";
$db->execute($sql) or die("Error en delete de  ok: ".$db->errorMsg()."<br>". $sql);
//para obtener el id del renglon que inserte

$sql="select * from proveedor where razon_social='licitaciones'";
$resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
$id_proveedor=$resultado->fields['id_proveedor'];

$sql="select tipo from renglon  where id_renglon=$renglon";
$resultados=$db->execute($sql) or die($db->errorMsg()."<br>". $sql);


if (($resultados->fields['tipo']=="Computadora Enterprise") || ($resultados->fields['tipo']=="Computadora Matrix")  || ($resultados->fields['tipo']=="Computadora Porteña") || ($resultados->fields['tipo']=="Computadora Argentina"))
{
$flag=$_POST['flag_kit'];
$flag=trim($flag);
if ($_POST['select_kit']!=0) {
                             $id_producto=$_POST['select_kit'];
                             $sql="select * from productos where id_producto = $id_producto";
                             $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                             $marca=$resultado->fields['marca'];
                             $modelo=$resultado->fields['modelo'];
                             $tipo=$resultado->fields['tipo'];
                             $precio=$_POST['precio_kit'];
                             $desc_precio=$_POST['desc_precio_kit'];
                             $cantidad=$_POST['cantidad_kit'];
                             if ($flag!="0") {
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_kit'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                  $sql="delete from descripciones_renglon where id = $id";
                                  $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql = "delete from archivos where id_producto = $flag ";
                                  $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                  
                                  //traigo descripcion de precio de la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                                                    
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  {
                                   $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
                                  }
                                  
                                                                                                                           
                                                                                                     
                                  $sql="delete from producto where id_renglon = $renglon and id= $id";
                                  $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) {//significa que existe un archivo asociado
                                        //aca insertar en tabla archivos
                                        while (!$resultado_fo->EOF) {
										$name=$resultado_fo->fields['nombre_ar'];
                                        $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                        $tamaño=$resultado_fo->fields['tamaño'];
                                        $tipo=$resultado_fo->fields['tipo'];
                                        $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                        $subidofecha=date("Y-m-d H:i:s", mktime());
                                        $subidousuario=$_ses_user_name;
                                        //me fijo si se inserto anteriormente un producto
										$sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
										$resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
										if ($resultado_archivo->RecordCount()<=0)
										{//inserto el id del producto pero el id del renglon
											$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
											$db->execute($sql) or die($sql."<br>".$db->errorMsg());
										}
										$resultado_fo->MoveNext();
										}
									} //del if de resultado_fo
									} //de la comparacion de los id
                                    else 
                                     {//traigo descripcion de preciod e la bd y comparo con el que viene por post
                                      $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                       {
                                   		//traigo descripcion de preciod e la bd y comparo con el que viene por post
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		$sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		$db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
                                  	   }
                                     }	
                                 }//del then de flag_kit
                                else {
									  $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
									  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
									  
                                      
                                      if($desc_precio!="")
                                      {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   	   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                       $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                      }
									  
                                     //busco el folleto del producto seleccionado
                                     $sql="select * from folletos where id_producto=$id_producto";
                                     $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                     if ($resultado_fo->RecordCount()>0){
                                      //significa que existe un archivo asociado
                                    //selecciono el producto que inserte en el renglon
                                      while (!$resultado_fo->EOF) {
									  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                      $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                      $resultado_pro->Movelast();
                                      $id=$resultado_pro->fields['id'];
                                      //datos del folleto
                                      $name=$resultado_fo->fields['nombre_ar'];
                                      $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                      $tamaño=$resultado_fo->fields['tamaño'];
                                      $tipo=$resultado_fo->fields['tipo'];
                                     $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                     $subidofecha=date("Y-m-d H:i:s", mktime());
                                     $subidousuario=$_ses_user_name;
                                     //me fijo si se inserto anteriormente un producto
									 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
									 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
									 if ($resultado_archivo->RecordCount()<=0)
									 {//inserto el id del producto pero el id del renglon
										$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
										$db->execute($sql) or die($sql."<br>".$db->errorMsg());
									 }
									 $resultado_fo->MoveNext();
									}
									} // del resultado record count de folleto

								   } //del else de flag kit
                             //relacionado con el precio
                               if ($_POST['nuevo_p_kit']==1) {
                                                              $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                              $resultado_precio=$db->execute($sql) or die ($sql);

                                                              if ($resultado_precio->RecordCount()>=1) {
                                                                                      $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }

                             } //del if select kit
else {
	 if ($flag!="0") {
	 			$id=$_POST['flag_kit'];
	 			$sql="delete from descripciones_renglon where id = $id";
		    	$resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	$sql="delete from producto where id_renglon = $renglon and id= $id";
		    	$resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	$sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
	          }
       }
$flag=$_POST['flag_madre'];
$flag=trim($flag);
if ($_POST['select_madre']!=0) {
                                $id_producto=$_POST['select_madre'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_madre'];
                                $desc_precio=$_POST['desc_precio_madre'];
                                $cantidad=$_POST['cantidad_madre'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_madre'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                       $sql="delete from descripciones_renglon where id = $id";
                                       $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       $sql = "delete from archivos where id_producto = $flag ";
                                       $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                       
                                       //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  		$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  		$result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                                
                                  		if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                         $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                        $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                        $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  		 
                                       
                                  
                                       $sql="delete from producto where id_renglon = $renglon and id= $id";
                                       $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                       $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       
                                       //inserto el folleto del nuevo producto
                                       $sql = "select * from folletos where id_producto = $id_producto";
                                       $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                       if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                            {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                         }//del if que compara los id
                                         else 
                                     {
                                      $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                                                           	
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) &&($desc_precio!=""))
                                  		{$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                         $db->execute($sql) or die($sql."<br>".$db->errormsg()."2");
                                   		}
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                if($desc_precio!="")
                                      {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                       $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                       $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                      }
                                      
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_madre']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }

                          } //del then  select madre

else {
	 if ($flag!="0") {
	 			$id=$flag;
	 			$sql="delete from descripciones_renglon where id = $id";
		    	$resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	$sql="delete from producto where id_renglon = $renglon and id= $id";
		    	$resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);


	          }
    }//del else

$flag=$_POST['flag_micro'];
$flag=trim($flag);
if ($_POST['select_micro']!=0) {
                                $id_producto=$_POST['select_micro'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_micro'];
                                $desc_precio=$_POST['desc_precio_micro'];
                                $cantidad=$_POST['cantidad_micro'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_micro'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                   
                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  	$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  	$result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  	if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                         $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                         $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                         $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  	 
                                                                    
                                  	                                 
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  
                                       //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                         }//del if que compara las cosas
                                         else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                                                
                                if($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                               if ($_POST['nuevo_p_micro']==1) {
                                                              $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                              $resultado_precio=$db->execute($sql) or die ($sql);

                                                              if ($resultado_precio->RecordCount()>=1) {
                                                                                      $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }






                          } //del if select micro

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_memoria'];
$flag=trim($flag);
if ($_POST['select_memoria']!=0)                                 {
                                $id_producto=$_POST['select_memoria'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_memoria'];
                                $desc_precio=$_POST['desc_precio_memoria'];
                                $cantidad=$_POST['cantidad_memoria'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_memoria'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){

                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                   
                                  //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }

                                   
                                                                    
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                       //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                     }//del if que compara los id
                                     else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                                                
                                                                
                                if($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                        }
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                               if ($_POST['nuevo_p_memoria']==1) {
                                                              $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                              $resultado_precio=$db->execute($sql) or die ($sql);

                                                              if ($resultado_precio->RecordCount()>=1) {
                                                                                      $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }
                          } //del if select micro

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_disco'];
$flag=trim($flag);
if ($_POST['select_disco']!=0)                                 {
                                $id_producto=$_POST['select_disco'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_disco'];
                                $desc_precio=$_POST['desc_precio_disco'];
                                $cantidad=$_POST['cantidad_disco'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_disco'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                  
                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                   
                                                                    
                                                                   
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                     }//del if que compara id
                                     else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                                      
                                if($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_disco']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }


                          } //del if select micro

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_cd'];
$flag=trim($flag);
if ($_POST['select_cd']!=0) {
                                $id_producto=$_POST['select_cd'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_cd'];
                                $desc_precio=$_POST['desc_precio_cd'];
                                $cantidad=$_POST['cantidad_cd'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_cd'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                   

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                   if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                                                   
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                     }//del if que compara los precios
                                     else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                                               
                                 if($desc_precio!="")
                                  		{
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_cd']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }


                          } //del if select cd


else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_monitor'];
$flag=trim($flag);
if ($_POST['select_monitor']!=0) {
                                $id_producto=$_POST['select_monitor'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_monitor'];
                                $desc_precio=$_POST['desc_precio_monitor'];
                                $cantidad=$_POST['cantidad_monitor'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_monitor'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                   if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                                                    
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                                                   
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."' and id_licitacion=$nro_licitacion";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                      }//del if que compara los id
                                      else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                       if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                
                                                               
                                 if($desc_precio!="")
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_monitor']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select monitor

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
//sistema operativo
$flag=$_POST['flag_sistemaoperativo'];
$flag=trim($flag);
if ($_POST['select_sistemaoperativo']!=0) {
                                $id_producto=$_POST['select_sistemaoperativo'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_sistemaoperativo'];
                                $desc_precio=$_POST['desc_precio_sistemaoperativo'];
                                $cantidad=$_POST['cantidad_sistemaoperativo'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_sistemaoperativo'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                                                   
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                                                   
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                     }
                                     else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                                                
                                if($desc_precio!="")
                                  		{
                                  		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
   	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_sistemaoperativo']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select sistema operativo

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else

$flag=$_POST['flag_conexo'];
$flag=trim($flag);
if ($_POST['select_conexo']!=0)  {
                                $id_producto=$_POST['select_conexo'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_conexo'];
                                $desc_precio=$_POST['desc_precio_conexo'];
                                //$cantidad=$_POST['cantidad_conexo'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_conexo'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                                                   
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                       }
                                       else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                               
                                if($desc_precio!="")
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_conexo']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select conexo

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_garantia'];
$flag=trim($flag);
if ($_POST['select_garantia']!=0){
                                $id_producto=$_POST['select_garantia'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                 if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $sql="update producto set marca='$marca', modelo='$modelo', tipo='$tipo', id_producto=$id_producto where id_renglon=$renglon and id=$flag";
                                   $db->execute($sql) or die($db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo

                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto) values ($renglon,'$marca','$modelo','$tipo',$id_producto)" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else

                          } //del if select micro

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else

///aca empiezan los adicionales
$flag=$_POST['flag_video'];
$flag=trim($flag);
if ($_POST['select_video']!=0) {
                                $id_producto=$_POST['select_video'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_video'];
                                $desc_precio=$_POST['desc_precio_video'];
                                $cantidad=$_POST['cantidad_video'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_video'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                       }
                                       else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                                               
                                if ($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_video']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select video

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_grabadora'];
$flag=trim($flag);
if ($_POST['select_grabadora']!=0)                                   {
                                $id_producto=$_POST['select_grabadora'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_grabadora'];
                                $desc_precio=$_POST['desc_precio_grabadora'];
                                $cantidad=$_POST['cantidad_grabadora'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_grabadora'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
	                              $sql="delete from producto where id_renglon = $renglon and id= $id";
                                  $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                        }
                                        else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                 $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                                               
                                if($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_grabadora']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select grabadora

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else

$flag=$_POST['flag_dvd'];
$flag=trim($flag);
if ($_POST['select_dvd']!=0){
                                $id_producto=$_POST['select_dvd'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_dvd'];
                                $desc_precio=$_POST['desc_precio_dvd'];
                                $cantidad=$_POST['cantidad_dvd'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_dvd'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                 
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                        }
                                        else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                               
                                
                                if($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   		 
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_dvd']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }




                          } //del if select dvd

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_red'];
$flag=trim($flag);
if ($_POST['select_red']!=0) {
                                $id_producto=$_POST['select_red'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_red'];
                                $desc_precio=$_POST['desc_precio_red'];
                                $cantidad=$_POST['cantidad_red'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_red'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                 
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                         }
                                         else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                
                                if($desc_precio!="")
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_red']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                    $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if
                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                        } // del if del nuevo precio



                          } //del if select red

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_modem'];
$flag=trim($flag);
if ($_POST['select_modem']!=0) {
                                $id_producto=$_POST['select_modem'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_modem'];
                                $desc_precio=$_POST['desc_precio_modem'];
                                $cantidad=$_POST['cantidad_modem'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_modem'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                                    // recupero todos los comentarios del producto 
                                  $sql="select * from historial_comentario_producto where id=$id";
                                  $resultado_comentario_historial = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  $sql="delete from historial_comentario_producto where id=$id";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                         }
                                         else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                if ($desc_precio!="")
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_modem']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select modem

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_zip'];
$flag=trim($flag);
if ($_POST['select_zip']!=0)  {
                                $id_producto=$_POST['select_zip'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_zip'];
                                $desc_precio=$_POST['desc_precio_zip'];
                                $cantidad=$_POST['cantidad_zip'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_zip'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                  //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                   
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                       }
                                       else 
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                     if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                 $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                
                                if ($desc_precio!="")
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                        if ($_POST['nuevo_p_zip']==1) {
                                                       $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                       $resultado_precio=$db->execute($sql) or die ($sql);
                                                       if ($resultado_precio->RecordCount()>=1) {
                                                                                    $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select  zip
else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else


}
//el if de matriz o enterprise
//si es impresora

if ($resultados->fields['tipo']=="Impresora") {
$flag=$_POST['flag_impresora'];
$flag=trim($flag);
if ($_POST['select_impresora']!=0)  {
                                $id_producto=$_POST['select_impresora'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_impresora'];
                                $desc_precio=$_POST['desc_precio_impresora'];
                                $cantidad=$_POST['cantidad_impresora'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_impresora'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                  
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                        }
                                        else
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                
                                if($desc_precio!="")
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else

                               if ($_POST['nuevo_p_impresora']==1) {
                                                              $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                              $resultado_precio=$db->execute($sql) or die ($sql);

                                                              if ($resultado_precio->RecordCount()>=1) {
                                                                                      $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }




                          } //del if select
else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else


$flag=$_POST['flag_conexo'];
$flag=trim($flag);
if ($_POST['select_conexo']!=0)  {
                                $id_producto=$_POST['select_conexo'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_conexo'];
                                $desc_precio=$_POST['desc_precio_conexo'];
                                //$cantidad=$_POST['cantidad_conexo'];
                                if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_conexo'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                 
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                       }
                                       else
                                     {$sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                      $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      $sql="update producto set precio_licitacion=$precio,cantidad=$cantidad,desc_precio_licitacion='$desc_precio' where id=$id" ;
                                      $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                      if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                     }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                if ($desc_precio!="")
                                  		{
                                   		 
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else
                               if ($_POST['nuevo_p_conexo']==1) {
                                                              $sql="select * from precios where id_producto=$id_producto and id_proveedor = $id_proveedor";
                                                              $resultado_precio=$db->execute($sql) or die ($sql);

                                                              if ($resultado_precio->RecordCount()>=1) {
                                                                                      $sql="update precios SET  precio=$precio where id_producto = $id_producto and id_proveedor = $id_proveedor";
                                                                                      $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                       } //del if

                                                                                                     else {
                                                                                                     $sql="insert into precios (id_producto,id_proveedor,precio) values($id_producto,$id_proveedor,$precio)";
                                                                                                     $db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                                                                                            }

                                                              }



                          } //del if select micro

else {
     if ($flag!="0") {
                $id=$flag;
                $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
$flag=$_POST['flag_garantia'];
$flag=trim($flag);
if ($_POST['select_garantia']!=0){
                                $id_producto=$_POST['select_garantia'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                 if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_garantia'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                   //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                  if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
                                  
                                 
                                  
                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,cantidad,id_producto,id_proveedor) values ($renglon,'$marca','$modelo','$tipo',$cantidad,$id_producto,$id_proveedor)" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
											 while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                       }

                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto) values ($renglon,'$marca','$modelo','$tipo',$id_producto)" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                
                                if ($desc_precio!="")
                                  		{
                                   		$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else

                          } //del if select micro

else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else


 }//del if de select impresora


if (strcmp($_POST['producto'],"Software")==0||strcmp($_POST['producto'],"Otro")==0)  {
    $flag=$_POST['flag_garantia'];
    $flag=trim($flag);
    if ($_POST['select_garantia']!=0){
                                $id_producto=$_POST['select_garantia'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                 if ($flag!="0"){   //es que habia algo y hay que actualizar el producto y borro el folleto
                                   $id=$flag;
                                   $id_producto_viejo=$_POST['idproducto_garantia'];
                                   $id_producto_viejo=trim($id_producto_viejo);
                                   if ($id_producto_viejo!=$id_producto){
                                   $sql="delete from descripciones_renglon where id = $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql = "delete from archivos where id_producto = $flag ";
                                   $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);

                                  //traigo descripcion de preciod e la bd y comparo con el que viene por post
                                  $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
                                  $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                   if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
                                  		{
                                   		 
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
	                                    }
                                   
                                 

                                   $sql="delete from producto where id_renglon = $renglon and id= $id";
                                   $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   $sql="insert into producto (id_renglon,marca,modelo,tipo,cantidad,id_producto,id_proveedor) values ($renglon,'$marca','$modelo','$tipo',$cantidad,$id_producto,$id_proveedor)" ;
                                   $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  
                                   //inserto el folleto del nuevo producto
                                   $sql = "select * from folletos where id_producto = $id_producto";
                                   $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
                                   if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                             {
                                             //aca insertar en tabla archivos
                                             while (!$resultado_fo->EOF) {
											 $name=$resultado_fo->fields['nombre_ar'];
                                             $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                             $tamaño=$resultado_fo->fields['tamaño'];
                                             $tipo=$resultado_fo->fields['tipo'];
                                             $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                             $subidofecha=date("Y-m-d H:i:s", mktime());
                                             $subidousuario=$_ses_user_name;
                                             //me fijo si se inserto anteriormente un producto
											 $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."' and id_licitacion=$nro_licitacion";
											 $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 if ($resultado_archivo->RecordCount()<=0)
											 {//inserto el id del producto pero el id del renglon
												$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
												$db->execute($sql) or die($sql."<br>".$db->errorMsg());
											 }
											 $resultado_fo->MoveNext();
											 }
											} //del if de resultado_fo
                                       }
                                   }//del flag
                                 else {
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto) values ($renglon,'$marca','$modelo','$tipo',$id_producto)" ;
                                $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                
                                 if($desc_precio!="")
                                  		{
                                   		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                   		 $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                   		 $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                   
	                                    }
	                                    
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
                                  while (!$resultado_fo->EOF) {
								  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
                                  $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
                                  $resultado_pro->Movelast();
                                  $id=$resultado_pro->fields['id'];
                                  //datos del folleto
                                  $name=$resultado_fo->fields['nombre_ar'];
                                  $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
                                  $tamaño=$resultado_fo->fields['tamaño'];
                                  $tipo=$resultado_fo->fields['tipo'];
                                  $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
                                  $subidofecha=date("Y-m-d H:i:s", mktime());
                                  $subidousuario=$_ses_user_name;
                                  //me fijo si se inserto anteriormente un producto
								  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
								  $resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  if ($resultado_archivo->RecordCount()<=0)
								  {//inserto el id del producto pero el id del renglon
									$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
									$db->execute($sql) or die($sql."<br>".$db->errorMsg());
								  }
								  $resultado_fo->MoveNext();
								  }
								} // del resultado record count de folleto
                        }//del else

                          } //del if select micro

    else {
     if ($flag!="0") {
                 $id=$flag;
                 $sql="delete from descripciones_renglon where id = $id";
		    	 $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
		    	 $sql="delete from producto where id_renglon = $renglon and id= $id";
                $resultado = $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                $sql = "delete from archivos where id_producto = $id ";
                $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
                     }
    }//del else
}
$i=1;
while ($i<=15)
{
switch($_POST["estado$i"])
{
 //case 0 modifica
 case 0:{$id_producto=$_POST["tipo$i"];
         $id=$_POST["id$i"];
         $sql="select * from productos where id_producto = $id_producto";
         $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
         $marca=$resultado->fields['marca'];
         $modelo=$resultado->fields['modelo'];
         $tipo=$resultado->fields['tipo'];
         $descripcion=$_POST["descripcion$i"];
         $precio=$_POST["precio$i"];
         $desc_precio=$_POST["desc_precio_$i"];
         $cantidad = $_POST["cantidad$i"];
 	     
         //traigo descripcion de preciod e la bd y comparo con el que viene por post
         $sql="select desc_precio_licitacion from producto where id_renglon = $renglon and id= $id";
         $result_desc_precio=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
         
         $sql="update producto set desc_gral='$descripcion', marca='$marca', modelo='$modelo', tipo='$tipo', precio_licitacion=$precio,cantidad=$cantidad, id_producto=$id_producto,desc_precio_licitacion='$desc_precio' where id=$id";
         $db->execute($sql) or die($sql."<br>".$db->errorMsg());
         
         if((strcmp($result_desc_precio->fields['desc_precio_licitacion'],$desc_precio)!=0) && ($desc_precio!=""))
          {
           $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
           $db->execute($sql) or die($sql."<br>".$db->errorMsg());
           $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
           $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
          }
          
         $sql="select * from producto where id_renglon=$renglon and id_producto=$id_producto";
         $resultado_id=$db->execute($sql) or die($db->errorMsg()."<br>".$sql);
         $sql = "delete from archivos where id_producto =".$resultado_id->fields['id'];
         $db->execute($sql) or  die($db->ErrorMsg()."Diego".$sql);
         //inserto el folleto del nuevo producto
         $sql = "select * from folletos where id_producto = $id_producto";
         $resultado_fo= $db->execute($sql) or die($db->errorMsg." ".$sql);
         if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
         {
            //aca insertar en tabla archivos
            while (!$resultado_fo->EOF) {
			$name=$resultado_fo->fields['nombre_ar'];
            $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
            $tamaño=$resultado_fo->fields['tamaño'];
            $tipo=$resultado_fo->fields['tipo'];
            $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
            $subidofecha=date("Y-m-d H:i:s", mktime());
            $subidousuario=$_ses_user_name;
            //me fijo si se inserto anteriormente un producto
			$sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
			$resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
			if ($resultado_archivo->RecordCount()<=0)
			{//inserto el id del producto pero el id del renglon
				$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
				$db->execute($sql) or die($sql."<br>".$db->errorMsg());
			}
			$resultado_fo->MoveNext();
			}
		 } //del if de resultado_fo

         break;
 	    }
 //case 1 eliminar
 case 1:{$id_producto=$_POST["producto$i"];
         $sql="select * from producto where id_renglon=$renglon and id_producto=$id_producto";
         $resultado=$db->execute($sql) or die($db->errorMsg()."<br>".$sql);
         $sql="delete from descripciones_renglon where id =".$resultado->fields['id'];
         $db->execute($sql) or die($sql."<br>".$db->errorMsg());
         $sql="delete from producto where id_renglon = $renglon and id=".$resultado->fields['id'];
         $db->execute($sql) or die($db->errorMsg()."<br>".$sql);
         $sql = "delete from archivos where id_producto =".$resultado->fields['id'];
         $db->execute($sql) or  die($db->ErrorMsg()." ".$sql);
         break;
        }
 //case2  insertar
 case 2:{$id_producto=$_POST["tipo$i"];
         $sql="select * from productos where id_producto = $id_producto";
         $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
         $marca=$resultado->fields['marca'];
         $modelo=$resultado->fields['modelo'];
         $tipo=$resultado->fields['tipo'];
         $precio=$_POST["precio$i"];
         $desc_precio=$_POST["desc_precio_$i"];
         $cantidad = $_POST["cantidad$i"];
         $descripcion=$_POST["descripcion$i"];
         $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto,comentarios,precio_licitacion,cantidad,desc_gral,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$id_producto,'adicionales',$precio,$cantidad,'$descripcion',$id_proveedor,'$desc_precio')" ;
         $db->execute($sql) or die($sql."<br>".$db->errorMsg());

         
         if($desc_precio!="")
     		{
       		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
             $db->execute($sql) or die($sql."<br>".$db->errorMsg());
             $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
             $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
            }
         
         $sql="select * from folletos where id_producto=$id_producto";
         $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
         if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
          {//selecciono el producto que inserte en el renglon
           while (!$resultado_fo->EOF) {
		   $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
           $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
           $resultado_pro->Movelast();
           $id=$resultado_pro->fields['id'];
           //datos del folleto
           $name=$resultado_fo->fields['nombre_ar'];
           $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
           $tamaño=$resultado_fo->fields['tamaño'];
           $tipo=$resultado_fo->fields['tipo'];
           $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
           $subidofecha=date("Y-m-d H:i:s", mktime());
           $subidousuario=$_ses_user_name;
           //me fijo si se inserto anteriormente un producto
			$sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
			$resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
			if ($resultado_archivo->RecordCount()<=0)
			{//inserto el id del producto pero el id del renglon
				$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
				$db->execute($sql) or die($sql."<br>".$db->errorMsg());
			}
			$resultado_fo->MoveNext();
			}
		 } // del resultado record count de folleto
         break;
       }
 //case 3 eliminar e insertar
 case 3:{$id_producto=$_POST["producto$i"];
         $id=$_POST["id$i"];
         $sql="delete from producto where id_renglon = $renglon and id= $id";
         $db->execute($sql) or die($db->errorMsg()."<br>".$sql);
         $sql="delete from descripciones_renglon where id =$id";
         $db->execute($sql) or die($sql."<br>".$db->errorMsg());
         $id_producto=$_POST["tipo$i"];
         $sql="select * from productos where id_producto = $id_producto";
         $resultado=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
         $marca=$resultado->fields['marca'];
         $modelo=$resultado->fields['modelo'];
         $tipo=$resultado->fields['tipo'];
         $precio=$_POST["precio$i"];
         $desc_precio=$_POST["desc_precio_$i"];
         $cantidad = $_POST["cantidad$i"];
         $descripcion=$_POST["descripcion$i"];
         $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto,comentarios,precio_licitacion,cantidad,desc_gral,id_proveedor,desc_precio_licitacion) values ($renglon,'$marca','$modelo','$tipo',$id_producto,'adicionales',$precio,$cantidad,'$descripcion',$id_proveedor,'$desc_precio')" ;
         $db->execute($sql) or die($sql."<br>".$db->errorMsg());
         
         
         if($desc_precio!="")
     		{
       		 $sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
           $db->execute($sql) or die($sql."<br>".$db->errorMsg());
           $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
           $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
            }
         $sql="select * from folletos where id_producto=$id_producto";
         $resultado_fo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
         if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
          {//selecciono el producto que inserte en el renglon
           while (!$resultado_fo->EOF) {
		   $sql="select * from producto where id_producto=$id_producto  and id_renglon = $renglon order by id";
           $resultado_pro=$db->execute($sql) or die($sql."<br>".$db->ErrorMsg());
           $resultado_pro->Movelast();
           $id=$resultado_pro->fields['id'];
           //datos del folleto
           $name=$resultado_fo->fields['nombre_ar'];
           $nombrecomp= substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
           $tamaño=$resultado_fo->fields['tamaño'];
           $tipo=$resultado_fo->fields['tipo'];
           $tamaño_comprimido=$resultado_fo->fields['tamaño_comp'];
           $subidofecha=date("Y-m-d H:i:s", mktime());
           $subidousuario=$_ses_user_name;
           //me fijo si se inserto anteriormente un producto
			$sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."'";
			$resultado_archivo=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
			if ($resultado_archivo->RecordCount()<=0)
			{//inserto el id del producto pero el id del renglon
				$sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
				$db->execute($sql) or die($sql."<br>".$db->errorMsg());
			}
			$resultado_fo->MoveNext();
			}
		 } // del resultado record count de folleto
         break;
        }
}
$i++;
}
//consulta para saber los datos de los renglones
$query = "SELECT renglon.* FROM renglon WHERE id_licitacion = $nro_licitacion ORDER BY codigo_renglon";
$resultados=$db->execute($query) or die($query);
$cantidad_filas = $resultados->RecordCount();
$cnr = 1;
$i=0;
//se actualizan los totales por renglon
while ( $i< $cantidad_filas )  {
     $id_renglon = $resultados->fields['id_renglon'];
     $titulo_renglon = $resultados->fields['titulo'];
     $nro_renglon = $resultados->fields['codigo_renglon'];
     $ganancia = $resultados->fields['ganancia'];
     $cantidad = $resultados->fields['cantidad'];
     $sin_descripcion=$resultados->fields['sin_descripcion'];
     $lista_descripcion=$resultados->fields['lista_descripcion'];

     $sql="select * from producto where id_renglon = $id_renglon";
     $resultados_suma=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
     $filas_encontradas = $resultados_suma->RecordCount();
     $j=0;
     $total_renglon=0;
     while ($j<$filas_encontradas){
      $total_producto = $resultados_suma->fields['cantidad'] * $resultados_suma->fields['precio_licitacion'];
      $total_renglon+=$total_producto;
      $j++;
      $resultados_suma->MoveNext();
      }
     //total renglon tiene la suma de los productos de los renglones
     $total_renglon=number_format($total_renglon,'2','.','');

     if ($resultado_licitacion->fields['id_moneda']==1) {

                  $subtotal_renglon=($total_renglon * $resultado_licitacion->fields['valor_dolar_lic'])/$ganancia;
                  $subtotal_renglon=ceil($subtotal_renglon);
                  $total_cantidad_renglon=$resultados->fields['cantidad']*$subtotal_renglon;
           } //del if
                            else {
                                 $subtotal_renglon=$total_renglon /$ganancia;
                                 $subtotal_renglon=ceil($subtotal_renglon);
                                 $total_cantidad_renglon=$resultados->fields['cantidad']*$subtotal_renglon;
                                       } //del else
  //en el renglon coloco unicamente el subototal osea el precio unitario falta multiplicarlo por la cantidad
  $sql="update renglon set total = $subtotal_renglon where id_renglon = $id_renglon";
  $db->execute($sql) or die ("Fallo en la actualizacin de totales: ".$sql);
  $resultados->MoveNext();
  $i++;
  }


$db->CompleteTrans();
$link=encode_link('realizar_oferta.php',array('licitacion'=>$nro_licitacion));
header("Location:$link");
?>