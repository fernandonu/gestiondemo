<?php
/*
$Author: mari $
$Revision: 1.9 $
$Date: 2007/01/05 19:58:52 $
*/
require_once("../../config.php");


$db->StartTrans();

$titulo   =  $_POST['titulo'];
$renglon  =  $_POST['renglon'];
$ganancia =  $_POST['ganancia'];
$cantidad =  $_POST['cantidad_renglon'];
$tipo     =  $_POST['producto'];
$resumen  =  $_POST['resumen'];

if ($_POST['sin_descripcion']) $sin_descripcion=1;
                          else $sin_descripcion=0;
$usuario_crea = $_ses_user['name'];
$usuario_time = date("Y-m-d H:i:s");
$total=0;

if ($_POST['select_etap']==0)
                            $id_etap="NULL";
                            else
                            $id_etap=$_POST['select_etap'];


$sql="select distrito.* from licitaciones.licitacion
       left join licitaciones.entidad using (id_entidad)
       left join licitaciones.distrito using (id_distrito)
       where id_licitacion=$id_licitacion";
$res=sql($sql) or fin_pagina();

if($res->fields['nombre']=="Buenos Aires - GCBA")
                   $id_dis=$res->fields['id_distrito'];
                   else
                   $id_dis="";
if ($_POST['producto']=="Computadora Enterprise" && $_SERVER["HTTP_HOST"]!="localhost"){

    if ($id_dis==2)
                subir_folletos($id_licitacion,1);
                else
                subir_folletos($id_licitacion,2);
}

$sql="select id_proveedor from proveedor where razon_social='licitaciones'";
$resultado=sql($sql) or fin_pagina();
$id_proveedor=$resultado->fields['id_proveedor'];

if ($id_renglon==""){
                     //inserto el renglon
                     $sql="insert into renglon (id_licitacion,titulo,codigo_renglon,ganancia,cantidad,usuario,usuario_time,tipo,sin_descripcion,id_etap,resumen)
                            values ($id_licitacion,'$titulo','$renglon',$ganancia,$cantidad,'$usuario_crea','$usuario_time','$tipo',$sin_descripcion,$id_etap,'$resumen')";

                     sql($sql) or fin_pagina();
                     //para obtener el id del renglon que inserte
                     //para sabe el id de licitaciones
                     $sql="select id_renglon from renglon where id_licitacion=$id_licitacion and titulo = '$titulo' and codigo_renglon = '$renglon'";
                     $resultado=sql($sql) or fin_pagina();
                     $id_renglon = $resultado->fields['id_renglon'];
                     switch($tipo){
                           case 'Impresora':
                                            insertar_renglon($impresora,$id_renglon);
                                            break;//de Impresora
                           case 'Software':
                                            insertar_renglon($software,$id_renglon);
                                            break;//Software
                           case 'Servidor':
                           case 'Otro'    :
                                           insertar_renglon($otro,$id_renglon);
                                           breaK;//Otro
                          default:
                                  insertar_renglon($maquina_basica,$id_renglon);
                                  insertar_renglon($maquina_adicional,$id_renglon);
                                  break;
                      }
                      //los adicionales estan en todos los tipos de renglon
                      insertar_adicionales ();
              }
              else{
                //modifico el renglon por que me paso un id de renglon y ya existe
                $sql="update renglon set tipo='$tipo',titulo = '$titulo',codigo_renglon = '$renglon',
                                     cantidad = $cantidad,ganancia = $ganancia,usuario = '$usuario_crea',
                                     usuario_time = '$usuario_time',sin_descripcion=$sin_descripcion,
                                     id_etap=$id_etap
                                     where id_renglon = $id_renglon ";
                sql($sql) or fin_pagina();

                 switch($tipo){
                           case 'Impresora':
                                            modificar_renglon($impresora,$id_renglon);
                                            break;//de Impresora
                           case 'Software':
                                            modificar_renglon($software,$id_renglon);
                                            break;//Software
                           case 'Servidor':
                           case 'Otro'    :
                                           modificar_renglon($otro,$id_renglon);
                                           breaK;//Otro
                          default:
                                           modificar_renglon($maquina_basica,$id_renglon);
                                           modificar_renglon($maquina_adicional,$id_renglon);
                                           break;
                      }

               modificar_adicionales();
              }
$db->CompleteTrans();
$link=encode_link("licitaciones_renglones.php",array("id_licitacion"=>$id_licitacion,"ID"=>$id_licitacion));
header("location:$link") or die();
?>