<?
$fecha=date("d/m/Y H:i:s");
//@usuario debe venir por post
$usuario=$_ses_login;


if (($_POST['protocolo']==1))
{if ($_POST['check']==1)
 {$datamation='t';
  $iso='f';
  $iso_tipo=$_POST['pc']['iso_tipo'];
 }
 else
 {$datamation='f';
  $iso='t';
  $iso_tipo=$_POST['pc']['iso_tipo'];
 }
 $sql="insert into protocolo(nro_version,fecha_modif,id_renglon,nro_licitacion,tipo,datamation,iso,iso_tipo,micro_modelo,micro_velocidad,";
 $sql.="memoria_tipo,memoria_tam,disco_tipo,disco_tamaño,disco_bus,disco_rpm,";
 $sql.="video_tipo,video_tamaño,monitor_tamaño,multimedia_sonido,multimedia_cd,";
 $sql.="multimedia_cdwr,multimedia_dvd,multimedia_parlantes,multimedia_mic,";
 $sql.="teclado_mouse,lan_tipo,software_so,software_oficina,gabinete_tipo,";
 $sql.="garantia,comentarios,usuario) values(".$_POST['version'].",'".$fecha."',".$parametros['renglon'].",".$parametros['licitacion'].",'pc','$datamation','$iso','$iso_tipo','".$_POST['pc']['micro_modelo']."','".$_POST['pc']['micro_velocidad']."',";
 $sql.="'".$_POST['pc']['memoria_tipo']."','".$_POST['pc']['memoria_tamaño']."','".$_POST['pc']['disco_tipo']."','".$_POST['pc']['disco_tamaño']."','".$_POST['pc']['disco_bus']."','".$_POST['pc']['disco_rpm']."',";
 $sql.="'".$_POST['pc']['video_tipo']."','".$_POST['pc']['video_tamaño']."','".$_POST['pc']['monitor_tamaño']."','".$_POST['pc']['multimedia_sonido']."','".$_POST['pc']['multimedia_cd']."',";
 $sql.="'".$_POST['pc']['multimedia_cdwr']."','".$_POST['pc']['multimedia_dvd']."','".$_POST['pc']['multimedia_parlantes']."','".$_POST['pc']['multimedia_mic']."',";
 $sql.="'".$_POST['pc']['teclado']."','".$_POST['pc']['lan_tipo']."','".$_POST['pc']['software_so']."','".$_POST['pc']['software_oficina']."','".$_POST['pc']['gabinete_tipo']."',";
 $sql.="'".$_POST['pc']['garantia']."','".$_POST['comentario']."','".$_ses_user_login."');";
 $db->Execute($sql) or die($sql);
}
if (($_POST['protocolo']==2))
{$sql="insert into protocolo(nro_version,fecha_modif,id_renglon,nro_licitacion,tipo,micro_modelo,micro_cache,micro_cantidad,";
 $sql.="memoria_tipo,memoria_tam,memoria_expansion,";
 $sql.="storage_tipo,storage_interface,storage_rpm,storage_tamaño,storage_cantidad,storage_raid,";
 $sql.="backup_tipo,backup_modelo,backup_extras,";
 $sql.="video_tamaño,expansion_pci,monitor_tamaño,monitor_rack,";
 $sql.="teclado_mouse,switch_ports,software_so,gabinete_tipo,";
 $sql.="garantia,comentarios,usuario) values(".$_POST['version'].",'".$fecha."',".$parametros['renglon'].",".$parametros['licitacion'].",'servidor','".$_POST['servidor']['micro_modelo']."','".$_POST['servidor']['micro_cache']."','".$_POST['servidor']['micro_cantidad']."',";
 $sql.="'".$_POST['servidor']['memoria_tipo']."','".$_POST['servidor']['memoria_tamaño']."','".$_POST['servidor']['memoria_expansion']."',";
 $sql.="'".$_POST['servidor']['storage_tipo']."','".$_POST['servidor']['storage_interface']."','".$_POST['servidor']['storage_rpm']."','".$_POST['servidor']['storage_tamaño']."','".$_POST['servidor']['storage_cantidad']."','".$_POST['servidor']['storage_raid']."',";
 $sql.="'".$_POST['servidor']['storage_backup_tipo']."','".$_POST['servidor']['storage_backup_modelo']."','".$_POST['servidor']['storage_backup_extras']."',";
 $sql.="'".$_POST['servidor']['video_tamaño']."','".$_POST['servidor']['expansion_pci']."','".$_POST['servidor']['monitor_tamaño']."','".$_POST['servidor']['monitor_rackeable']."',";
 $sql.="'".$_POST['servidor']['teclado']."','".$_POST['servidor']['switch']."','".$_POST['servidor']['software_so']."','".$_POST['servidor']['gabinete_tipo']."',";
 $sql.="'".$_POST['servidor']['garantia']."','".$_POST['comentario']."','".$_ses_user_login."');";
 $db->Execute($sql) or die($sql);
}
if (($_POST['protocolo']==3))
{$sql="insert into protocolo(nro_version,fecha_modif,id_renglon,nro_licitacion,tipo,";
 $sql.="impresora_tipo,impresora_conexion,impresora_ppm,impresora_ram,impresora_be,impresora_duplex,";
 $sql.="impresora_red,impresora_extras,impresora_garantia,";
 $sql.="comentarios,usuario) values(".$_POST['version'].",'".$fecha."',".$parametros['renglon'].",".$parametros['licitacion'].",'impresora',";
 $sql.="'".$_POST['impresora']['tipo']."','".$_POST['impresora']['conexion']."','".$_POST['impresora']['ppm']."','".$_POST['impresora']['ram']."','".$_POST['impresora']['be']."','".$_POST['impresora']['duplex']."',";
 $sql.="'".$_POST['impresora']['interface']."','".$_POST['impresora']['extras']."','".$_POST['impresora']['garantia']."',";
 $sql.="'".$_POST['comentario']."','".$_ses_user_login."');";
 $db->Execute($sql) or die($sql);
}
if (($_POST['protocolo']==4))
{
 if ($chek==1)
 {$datamation="true";
  $iso="false";
  $iso_tipo="";
 }
 else
 {$datamation="false";
  $iso="true";
  $iso_tipo=$pc['iso_tipo'];
 }
 
 $sql="insert into protocolo(nro_version,fecha_modif,id_renglon,nro_licitacion,tipo,datamation,iso,iso_tipo,micro_modelo,micro_velocidad,";
 $sql.="memoria_tipo,memoria_tam,disco_tipo,disco_tamaño,disco_bus,disco_rpm,";
 $sql.="video_tipo,video_tamaño,monitor_tamaño,multimedia_sonido,multimedia_cd,";
 $sql.="multimedia_cdwr,multimedia_dvd,multimedia_parlantes,multimedia_mic,";
 $sql.="teclado_mouse,lan_tipo,software_so,software_oficina,gabinete_tipo,";
 $sql.="impresora_tipo,impresora_conexion,impresora_ppm,impresora_ram,impresora_be,impresora_duplex,";
 $sql.="impresora_red,impresora_extras,impresora_garantia,"; 
 $sql.="garantia,comentarios,usuario) values(".$_POST['version'].",'".$fecha."',".$parametros['renglon'].",".$parametros['licitacion'].",'pc+impresora','$datamation','$iso','$iso_tipo','".$_POST['pc']['micro_modelo']."','".$_POST['pc']['micro_velocidad']."',";
 $sql.="'".$_POST['pc']['memoria_tipo']."','".$_POST['pc']['memoria_tamaño']."','".$_POST['pc']['disco_tipo']."','".$_POST['pc']['disco_tamaño']."','".$_POST['pc']['disco_bus']."','".$_POST['pc']['disco_rpm']."',";
 $sql.="'".$_POST['pc']['video_tipo']."','".$_POST['pc']['video_tamaño']."','".$_POST['pc']['monitor_tamaño']."','".$_POST['pc']['multimedia_sonido']."','".$_POST['pc']['multimedia_cd']."',";
 $sql.="'".$_POST['pc']['multimedia_cdwr']."','".$_POST['pc']['multimedia_dvd']."','".$_POST['pc']['multimedia_parlantes']."','".$_POST['pc']['multimedia_mic']."',";
 $sql.="'".$_POST['pc']['teclado']."','".$_POST['pc']['lan_tipo']."','".$_POST['pc']['software_so']."','".$_POST['pc']['software_oficina']."','".$_POST['pc']['gabinete_tipo']."',";
 $sql.="'".$_POST['impresora']['tipo']."','".$_POST['impresora']['conexion']."','".$_POST['impresora']['ppm']."','".$_POST['impresora']['ram']."','".$_POST['impresora']['be']."','".$_POST['impresora']['duplex']."',";
 $sql.="'".$_POST['impresora']['interface']."','".$_POST['impresora']['extras']."','".$_POST['impresora']['garantia']."',";
 $sql.="'".$_POST['pc']['garantia']."','".$_POST['comentario']."','".$_ses_user_login."');";
 $db->Execute($sql) or die($sql);
}
if (($_POST['protocolo']==5))
{$sql="insert into protocolo(nro_version,fecha_modif,id_renglon,nro_licitacion,tipo,micro_modelo,micro_cache,micro_cantidad,";
 $sql.="memoria_tipo,memoria_tam,memoria_expansion,";
 $sql.="storage_tipo,storage_interface,storage_rpm,storage_tamaño,storage_cantidad,storage_raid,";
 $sql.="backup_tipo,backup_modelo,backup_extras,";
 $sql.="video_tamaño,expansion_pci,monitor_tamaño,monitor_rack,";
 $sql.="teclado_mouse,switch_ports,software_so,gabinete_tipo,";
 $sql.="impresora_tipo,impresora_conexion,impresora_ppm,impresora_ram,impresora_be,impresora_duplex,";
 $sql.="impresora_red,impresora_extras,impresora_garantia,";
 $sql.="garantia,comentarios,usuario) values(".$_POST['version'].",'".$fecha."',".$parametros['renglon'].",".$parametros['licitacion'].",'servidor+impresora','".$_POST['servidor']['micro_modelo']."','".$_POST['servidor']['micro_cache']."','".$_POST['servidor']['micro_cantidad']."',";
 $sql.="'".$_POST['servidor']['memoria_tipo']."','".$_POST['servidor']['memoria_tamaño']."','".$_POST['servidor']['memoria_expansion']."',";
 $sql.="'".$_POST['servidor']['storage_tipo']."','".$_POST['servidor']['storage_interface']."','".$_POST['servidor']['storage_rpm']."','".$_POST['servidor']['storage_tamaño']."','".$_POST['servidor']['storage_cantidad']."','".$_POST['servidor']['storage_raid']."',";
 $sql.="'".$_POST['servidor']['storage_backup_tipo']."','".$_POST['servidor']['storage_backup_modelo']."','".$_POST['servidor']['storage_backup_extras']."',";
 $sql.="'".$_POST['servidor']['video_tamaño']."','".$_POST['servidor']['expansion_pci']."','".$_POST['servidor']['monitor_tamaño']."','".$_POST['servidor']['monitor_rackeable']."',";
 $sql.="'".$_POST['servidor']['teclado']."','".$_POST['servidor']['switch']."','".$_POST['servidor']['software_so']."','".$_POST['servidor']['gabinete_tipo']."',";
 $sql.="'".$_POST['impresora']['tipo']."','".$_POST['impresora']['conexion']."','".$_POST['impresora']['ppm']."','".$_POST['impresora']['ram']."','".$_POST['impresora']['be']."','".$_POST['impresora']['duplex']."',";
 $sql.="'".$_POST['impresora']['interface']."','".$_POST['impresora']['extras']."','".$_POST['impresora']['garantia']."',";
 $sql.="'".$_POST['servidor']['garantia']."','".$_POST['comentario']."','".$_ses_user_login."');";
 
 
 /************************
 hay que sacar a programador despues y poner la variable
 ************************/
 
 $db->Execute($sql) or die($sql);
}
//esto lo puse yo(fernando) link
//antes de volver a la página realizar_oferta.php seteo el flag de participamos ( el campo
// no participamos de la base de datos).

  $query="UPDATE renglon SET no_participamos=FALSE WHERE id_renglon = '".$parametros["renglon"]."'";
  $db ->Execute("$query")or die($db->ErrorMsg());


$link = encode_link("../realizar_oferta.php",array("licitacion" => $parametros['licitacion'],
                                "renglon" => $parametros['renglon']));
header("location:$link");
//aca termina lo que puse yo                                
//header("location: ../realizar_oferta.php");
?>