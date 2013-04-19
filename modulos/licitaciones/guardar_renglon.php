<?php
/*
$Author: diegoinga $
$Revision: 1.58 $
$Date: 2004/09/15 19:20:56 $
*/
require_once("../../config.php");
//agregar renglon
//placa madre
//agrego el renglon
global $nro_licitacion;
global $tipo;
$db->StartTrans();
$titulo = $_POST['titulo'];
$renglon = $_POST['renglon'];
$ganancia= $_POST['ganancia_renglon'];
$cantidad = $_POST['cantidad_renglon'];
$tipo = $_POST['producto'];
if ($_POST['sin_descripcion']) $sin_descripcion=1;
                               else $sin_descripcion=0;
$usuario_crea = $_ses_user_name;
$usuario_time = date("Y-m-d H:i:s");
$total=0;
$sql="select * from renglon where id_licitacion = $nro_licitacion and codigo_renglon = '$renglon'";
$resultado=$db->execute($sql) or die ($sql);
if ($resultado->RecordCount()==0) {
if ($_POST['select_etap']==0)
	$id_etap="NULL";
else
	$id_etap=$_POST['select_etap'];
$resumen=$_POST['resumen'];

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

$sql="insert into renglon (id_licitacion,titulo,codigo_renglon,ganancia,cantidad,usuario,usuario_time,tipo,sin_descripcion,id_etap,resumen)";
$sql.=" values ($nro_licitacion,'$titulo','$renglon',$ganancia,$cantidad,'$usuario_crea','$usuario_time','$tipo',$sin_descripcion,$id_etap,'$resumen')";
//$sql="insert into renglon (id_licitacion,titulo,codigo_renglon) values ($nro_licitacion,'$titulo','$renglon')";
$db->execute($sql) or die($db->ErrorMSG()."<br>".$sql);
//para obtener el id del renglon que inserte
$sql="select * from proveedor where razon_social='licitaciones'";
$resultado=$db->execute($sql) or die($sql);
$id_proveedor=$resultado->fields['id_proveedor'];
//
$sql="select id_renglon from renglon where id_licitacion=$nro_licitacion and titulo = '$titulo' and codigo_renglon = '$renglon'";
$resultado=$db->execute($sql) or die($sql);
$id_renglon = $resultado->fields['id_renglon'];

if ((strcmp($_POST['producto'],"Computadora Enterprise")==0) || (strcmp($_POST['producto'],"Computadora Matrix")==0) || (strcmp($_POST['producto'],"Computadora Porteña")==0)|| (strcmp($_POST['producto'],"Computadora Argentina")==0)) {
if ($_POST['select_kit']!=0) {
                                $id_producto=$_POST['select_kit'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $cantidad=$_POST['cantidad_kit'];
                                $precio=$_POST['precio_kit'];
                                $desc_precio=$_POST['desc_precio_kit'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                                                
                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                	                                    
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//aca insertar en tabla archivos
								  while (!$resultado_fo->EOF) {
                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                   $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                   $db->execute($sql) or die($sql);
                                  }
								  $resultado_fo->MoveNext();
								  }
                                 }

                               //actualizo los precios de productos
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

                               }


if ($_POST['select_madre']!=0) {
                                $id_producto=$_POST['select_madre'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_madre'];
                                $desc_precio=$_POST['desc_precio_madre'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);

                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								  while (!$resultado_fo->EOF) {	
                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }
if ($_POST['select_micro']!=0) {
                                $id_producto=$_POST['select_micro'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_micro'];
                                $desc_precio=$_POST['desc_precio_micro'];
                                $cantidad=$_POST['cantidad_micro'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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




                                 }
if ($_POST['select_memoria']!=0) {
                                $id_producto=$_POST['select_memoria'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_memoria'];
                                $desc_precio=$_POST['desc_precio_memoria'];
                                $cantidad=$_POST['cantidad_memoria'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                    
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }

if ($_POST['select_disco']!=0) {
                                $id_producto=$_POST['select_disco'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_disco'];
                                $desc_precio=$_POST['desc_precio_disco'];
                                $cantidad=$_POST['cantidad_disco'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                    
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }
if ($_POST['select_cd']!=0) {
                                $id_producto=$_POST['select_cd'];

                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_cd'];
                                $desc_precio=$_POST['desc_precio_cd'];
                                $cantidad=$_POST['cantidad_cd'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }
if ($_POST['select_monitor']!=0) {
                                $id_producto=$_POST['select_monitor'];

                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_monitor'];
                                $desc_precio=$_POST['desc_precio_monitor'];
                                $cantidad=$_POST['cantidad_monitor'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								  while (!$resultado_fo->EOF) {
                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."' and id_licitacion=$nro_licitacion";
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->MoveNext();
								  }

                                 } // del resultado record count de folleto
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

                                }
if ($_POST['select_sistemaoperativo']!=0) {
                                $id_producto=$_POST['select_sistemaoperativo'];

                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_sistemaoperativo'];
                                $desc_precio=$_POST['desc_precio_sistemaoperativo'];
                                $cantidad=$_POST['cantidad_sistemaoperativo'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->MoveNext();
								  }

                                 } // del resultado record count de folleto
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

                               }
if ($_POST['select_conexo']!=0) {
                                $id_producto=$_POST['select_conexo'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_conexo'];
                                $desc_precio=$_POST['desc_precio_conexo'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());

                                
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                 //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }

if ($_POST['select_garantia']!=0) {
                                $id_producto=$_POST['select_garantia'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['desc_gral'];
                                $tipo=$resultado->fields['tipo'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto) values ($id_renglon,'$marca','$modelo','$tipo',$id_producto)" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                          
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto

                                 }

if ($_POST['select_video']!=0) {
                                $id_producto=$_POST['select_video'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_video'];
                                $desc_precio=$_POST['desc_precio_video'];
                                $cantidad=$_POST['cantidad_video'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                   
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto+

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


                                 }

///aca empiezan los adicionales
if ($_POST['select_grabadora']!=0) {
                                $id_producto=$_POST['select_grabadora'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_grabadora'];
                                $desc_precio=$_POST['desc_precio_grabadora'];
                                $cantidad = $_POST['cantidad_grabadora'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                 
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }


if ($_POST['select_dvd']!=0) {
                                $id_producto=$_POST['select_dvd'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_dvd'];
                                $desc_precio=$_POST['desc_precio_dvd'];
                                $cantidad = $_POST['cantidad_dvd'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                    }


if ($_POST['select_red']!=0) {
                                $id_producto=$_POST['select_red'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_red'];
                                $desc_precio=$_POST['desc_precio_red'];
                                $cantidad = $_POST['cantidad_red'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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

                                                              }


                                 }
if ($_POST['select_modem']!=0) {
                                $id_producto=$_POST['select_modem'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_modem'];
                                $desc_precio=$_POST['desc_precio_modem'];
                                $cantidad = $_POST['cantidad_modem'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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


                                 }
if ($_POST['select_zip']!=0) {
                                $id_producto=$_POST['select_zip'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_zip'];
                                $desc_precio=$_POST['desc_precio_zip'];
                                $cantidad = $_POST['cantidad_zip'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }
                                 
                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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

                                 }

	// Si el producto es computadora, subimos el certificado iso9001
	subir_folletos($nro_licitacion,3);
 }
//el if de matriz o enterprise
//si es impresora

if (strcmp($_POST['producto'],"Impresora")==0)  {
if ($_POST['select_impresora']!=0) {

                                $id_producto=$_POST['select_impresora'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_impresora'];
                                $desc_precio=$_POST['desc_precio_impresora'];
                                $cantidad = $_POST['cantidad_impresora'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }

                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto

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



                                 }
if ($_POST['select_conexo']!=0) {
                                $id_producto=$_POST['select_conexo'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST['precio_conexo'];
                                $desc_precio=$_POST['desc_precio_conexo'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg());
                                
                                                                    
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }

                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto
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




                                 }

if ($_POST['select_garantia']!=0) {
                                $id_producto=$_POST['select_garantia'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['desc_gral'];
                                $tipo=$resultado->fields['tipo'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto) values ($id_renglon,'$marca','$modelo','$tipo',$id_producto)" ;
                                $db->execute($sql) or die($db->errorMsg());

                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto


                                 }



    }//del if de impresora (primero)

if (strcmp($_POST['producto'],"Software")==0||strcmp($_POST['producto'],"Otro")==0)  {
	if ($_POST['select_garantia']!=0) {
                                $id_producto=$_POST['select_garantia'];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);

                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['desc_gral'];
                                $tipo=$resultado->fields['tipo'];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,id_producto) values ($id_renglon,'$marca','$modelo','$tipo',$id_producto)" ;
                                $db->execute($sql) or die($db->errorMsg());

                                //busco el folleto del producto seleccionado
                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);
                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $sql="select id_producto from archivos where nombre='".$resultado_fo->fields['nombre_ar']."' and id_licitacion=$nro_licitacion";
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }

                                 } // del resultado record count de folleto


                                 }


}

//productos adicionales//

for($i=1;$i<=15;$i++){
if (strcmp($_POST["tipo$i"],"")!=0)  {
                                $id_producto=$_POST["tipo$i"];
                                $sql="select * from productos where id_producto = $id_producto";
                                $resultado=$db->execute($sql) or die($sql);
                                $marca=$resultado->fields['marca'];
                                $modelo=$resultado->fields['modelo'];
                                $tipo=$resultado->fields['tipo'];
                                $precio=$_POST["precio$i"];
                                $desc_precio=$_POST["desc_precio_$i"];
                                $cantidad = $_POST["cantidad$i"];
                                $sql="insert into producto (id_renglon,marca,modelo,tipo,precio_licitacion,cantidad,id_producto,comentarios,desc_gral,id_proveedor,desc_precio_licitacion) values ('$id_renglon','$marca','$modelo','$tipo',$precio,$cantidad,$id_producto,'adicionales','".$resultado->fields['desc_gral']."',$id_proveedor,'$desc_precio')" ;
                                $db->execute($sql) or die($db->errorMsg()."<br>".$sql);
                                
                                                                     
                                if($desc_precio!="")
                                 {$sql="update historial_comentario_producto set actual=0 where id_producto=$id_producto";
                                  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
                                  $sql="insert into historial_comentario_producto(id_producto,fecha_comentario,comentario,id_usuario,actual) values($id_producto,'".date("Y-m-d H:i:s")."','".$desc_precio."',".$_ses_user['id'].",1)";
                                  $db->execute($sql) or die($sql."<br>".$db->errormsg()."1");
                                 }

                                $sql="select * from folletos where id_producto=$id_producto";
                                $resultado_fo=$db->execute($sql) or die($sql);

                                if ($resultado_fo->RecordCount()>0) //significa que existe un archivo asociado
                                 {//selecciono el producto que inserte en el renglon
								 while (!$resultado_fo->EOF) {

                                  $sql="select * from producto where id_producto=$id_producto  and id_renglon = $id_renglon order by id";
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
                                  $resultado_archivo=$db->execute($sql) or die($sql);
                                  if ($resultado_archivo->RecordCount()<=0)
                                  {//inserto el id del producto pero el id del renglon
                                  $sql="insert into archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario,id_producto)  values ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario',$id)";
                                  $db->execute($sql) or die($sql);
								  }
								  $resultado_fo->movenext();
								  }
                                 }
                                } //del primer if



} //del for


}//del primer if


$db->CompleteTrans();


$link=encode_link('realizar_oferta.php',array('licitacion'=>$nro_licitacion));
header("Location:$link");
?>