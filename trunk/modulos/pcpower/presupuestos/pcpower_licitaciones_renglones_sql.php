<?php
/*AUTOR: Fernando - MAC
  Fecha: 01/10/04

$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2004/10/22 14:04:41 $
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
$usuario_crea = $_ses_user_name;
$usuario_time = date("Y-m-d H:i:s");
$total=0;

if ($_POST['select_etap']==0)
                            $id_etap="NULL";
                            else
                            $id_etap=$_POST['select_etap'];


$sql="select * from pcpower_licitacion
       left join pcpower_entidad using (id_entidad)
       left join pcpower_distrito using (id_distrito)
       where id_licitacion=$id_licitacion";
$res=sql($sql) or fin_pagina();

if($res->fields['nombre']=="Buenos Aires - GCBA")
                   $id_dis=$res->fields['id_distrito'];
                   else
                   $id_dis="";
/*if ($_POST['producto']=="Computadora Enterprise" && $_SERVER["HTTP_HOST"]!="localhost"){
    if ($id_dis==2)
        subir_folletos($id_licitacion,1);
        else
        subir_folletos($id_licitacion,2);
    }
*/
$sql="select * from proveedor where razon_social='licitaciones'";
$resultado=sql($sql) or fin_pagina();
$id_proveedor=$resultado->fields['id_proveedor'];

if ($id_renglon==""){
                     //inserto el renglon
                     $query="select nextval('pcpower_renglon_id_renglon_seq')as id_renglon";
                     $resultado=sql($query) or fin_pagina();
                     $id_renglon = $resultado->fields['id_renglon'];
                     $sql="insert into pcpower_renglon (id_renglon,id_licitacion,titulo,codigo_renglon,ganancia,cantidad,usuario,usuario_time,tipo,sin_descripcion,id_etap,resumen)
                            values ($id_renglon,$id_licitacion,'$titulo','$renglon',$ganancia,$cantidad,'$usuario_crea','$usuario_time','$tipo',$sin_descripcion,$id_etap,'$resumen')";

                     sql($sql) or fin_pagina();
                     //para obtener el id del renglon que inserte
                     //para sabe el id de licitaciones
                     $sql="select id_renglon from pcpower_renglon where id_licitacion=$id_licitacion and titulo = '$titulo' and codigo_renglon = '$renglon'";
                     
                     
                     switch($tipo){
                           case 'Impresora':
                                            insertar_renglon($impresora,$id_renglon);
                                            break;//de Impresora
                           case 'Software':
                                            insertar_renglon($software,$id_renglon);
                                            break;//Software
                           case 'Otro':
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
                $sql="update pcpower_renglon set tipo='$tipo',titulo = '$titulo',codigo_renglon = '$renglon',
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
                           case 'Otro':
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
$link=encode_link("pcpower_licitaciones_renglones.php",array("id_licitacion"=>$id_licitacion,"ID"=>$id_licitacion,"ganancia_oculta"=>$_POST["ganancia_oculta"]));
header("location:$link");
?>