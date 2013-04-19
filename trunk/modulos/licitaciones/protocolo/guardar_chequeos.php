<?php
require_once("../../../config.php");
$fecha=date("d/m/Y H:i:s");
$check=array("xt"=>"t","x"=>"f");
$sql="select * from protocolo where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and ((check_gati is not NULL) or (check_imred is not NULL));";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
if ($_POST['protocolo']==1)
{if ($filas_encontradas>0)
  {$sql="update protocolo set check_iso='".$check["x".$_POST['pc']['iso']]."', check_gati='".$check["x".$_POST['pc']['gabinete_tipo']]."', check_mimo='".$check["x".$_POST['pc']['micro_modelo']]."', check_mive='".$check["x".$_POST['pc']['micro_velocidad']]."', ";
   $sql.="check_meti='".$check["x".$_POST['pc']['mem_tipo']]."', check_meta='".$check["x".$_POST['pc']['mem_tamaño']]."' ,check_diti='".$check["x".$_POST['pc']['disco_tipo']]."', ";
   $sql.="check_dita='".$check["x".$_POST['pc']['disco_tamaño']]."', check_dibu='".$check["x".$_POST['pc']['disco_bus']]."', check_dirpm='".$check["x".$_POST['pc']['disco_rpm']]."' ,";
   $sql.="check_viti='".$check["x".$_POST['pc']['video_tipo']]."', check_temo='".$check["x".$_POST['pc']['teclado']]."', check_vita='".$check["x".$_POST['pc']['video_memoria']]."', ";
   $sql.="check_mota='".$check["x".$_POST['pc']['monitor_tamaño']]."', ";
   $sql.="check_muso='".$check["x".$_POST['pc']['multimedia_sonido']]."', check_mucd='".$check["x".$_POST['pc']['multimedia_cd']]."', check_muwr='".$check["x".$_POST['pc']['multimedia_cdwr']]."', check_mudvd='".$check["x".$_POST['pc']['multimedia_dvd']]."', check_mupa='".$check["x".$_POST['pc']['multimedia_parlantes']]."', check_mumic='".$check["x".$_POST['pc']['multimedia_mic']]."', ";
   $sql.="check_lati='".$check["x".$_POST['pc']['lan_tipo']]."', ";
   $sql.="check_soso='".$check["x".$_POST['pc']['software_so']]."', check_soof='".$check["x".$_POST['pc']['software_oficina']]."', ";
   $sql.="tipo='".$_POST['tipo_protocolo']."',datamation='".$_POST['data']."', iso='".$_POST['iso']."', iso_tipo='".$_POST['iso_tipo']."', micro_modelo='".$_POST['pc_micro_modelo']."', micro_velocidad='".$_POST['pc_micro_velocidad']."', ";
   $sql.="memoria_tipo='".$_POST['pc_memoria_tipo']."', memoria_tam='".$_POST['pc_memoria_tam']."', disco_tipo='".$_POST['pc_disco_tipo']."' ,disco_tamaño='".$_POST['pc_disco_tamaño']."', disco_bus='".$_POST['pc_disco_bus']."', disco_rpm='".$_POST['pc_disco_rpm']."', ";
   $sql.="video_tipo='".$_POST['pc_video_tipo']."', video_tamaño='".$_POST['pc_video_tamaño']."', monitor_tamaño='".$_POST['pc_monitor_tamaño']."', multimedia_sonido='".$_POST['pc_multimedia_sonido']."', multimedia_cd='".$_POST['pc_multimedia_cd']."', ";
   $sql.="multimedia_cdwr='".$_POST['pc_multimedia_cdwr']."', multimedia_dvd='".$_POST['pc_multimedia_dvd']."', multimedia_parlantes='".$_POST['pc_multimedia_parlantes']."', multimedia_mic='".$_POST['pc_multimedia_mic']."', ";
   $sql.="teclado_mouse='".$_POST['pc_teclado_mouse']."', lan_tipo='".$_POST['pc_lan_tipo']."', software_so='".$_POST['pc_software_so']."', software_oficina='".$_POST['pc_software_oficina']."', gabinete_tipo='".$_POST['pc_gabinete_tipo']."', garantia='".$_POST['pc_garantia']."', comentarios='".$_POST['comentario']."', ";
   $sql.="check_gara='".$check["x".$_POST['pc']['garantia']]."' where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and check_gati is not NULL;";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }
  else //primer protocolo que lleno
  {$sql="insert into protocolo(check_temo,nro_version,tipo,datamation,iso,iso_tipo,micro_modelo,micro_velocidad,";
   $sql.="memoria_tipo,memoria_tam,disco_tipo,disco_tamaño,disco_bus,disco_rpm,";
   $sql.="video_tipo,video_tamaño,monitor_tamaño,multimedia_sonido,multimedia_cd,";
   $sql.="multimedia_cdwr,multimedia_dvd,multimedia_parlantes,multimedia_mic,";
   $sql.="teclado_mouse,lan_tipo,software_so,software_oficina,gabinete_tipo,";
   $sql.="garantia,comentarios,check_iso,check_gati,check_mimo,check_mive,check_meti,check_meta,check_diti,check_dita,check_dibu,";
   $sql.="check_dirpm,check_viti,check_vita,check_mota,check_muso,check_mucd,check_muwr,check_mudvd,check_mupa,check_mumic,";
   $sql.="check_lati,check_soso,check_soof,check_gara,usuario,nro_licitacion,id_renglon) values('".$check["x".$_POST['pc']['teclado']]."', ";
   $sql.=$_POST['version'].",'".$_POST['tipo_protocolo']."','".$_POST['data']."','".$_POST['iso']."','".$_POST['iso_tipo']."','";
   $sql.=$_POST['pc_micro_modelo']."','".$_POST['pc_micro_velocidad']."','";
   $sql.=$_POST['pc_memoria_tipo']."','".$_POST['pc_memoria_tam']."','".$_POST['pc_disco_tipo']."','".$_POST['pc_disco_tamaño']."','";
   $sql.=$_POST['pc_disco_bus']."','".$_POST['pc_disco_rpm']."','";
   $sql.=$_POST['pc_video_tipo']."','".$_POST['pc_video_tamaño']."','".$_POST['pc_monitor_tamaño']."','".$_POST['pc_multimedia_sonido']."','".$_POST['pc_multimedia_cd']."','";
   $sql.=$_POST['pc_multimedia_cdwr']."','".$_POST['pc_multimedia_dvd']."','".$_POST['pc_multimedia_parlantes']."','".$_POST['pc_multimedia_mic']."','";
   $sql.=$_POST['pc_teclado_mouse']."','".$_POST['pc_lan_tipo']."','".$_POST['pc_software_so']."','".$_POST['pc_software_oficina']."','".$_POST['pc_gabinete_tipo']."','";
   $sql.=$_POST['pc_garantia']."','".$_POST['comentario']."','";
   $sql.=$check["x".$_POST['pc']['iso']]."','".$check["x".$_POST['pc']['gabinete_tipo']]."','".$check["x".$_POST['pc']['micro_modelo']]."','".$check["x".$_POST['pc']['micro_velocidad']]."','";
   $sql.=$check["x".$_POST['pc']['mem_tipo']]."','".$check["x".$_POST['pc']['mem_tamaño']]."','".$check["x".$_POST['pc']['disco_tipo']]."','";
   $sql.=$check["x".$_POST['pc']['disco_tamaño']]."','".$check["x".$_POST['pc']['disco_bus']]."','".$check["x".$_POST['pc']['disco_rpm']]."','";
   $sql.=$check["x".$_POST['pc']['video_tipo']]."','".$check["x".$_POST['pc']['video_memoria']]."','".$check["x".$_POST['pc']['monitor_tamaño']]."','";
   $sql.=$check["x".$_POST['pc']['multimedia_sonido']]."','".$check["x".$_POST['pc']['multimedia_cd']]."','".$check["x".$_POST['pc']['multimedia_cdwr']]."','".$check["x".$_POST['pc']['multimedia_dvd']]."','".$check["x".$_POST['pc']['multimedia_parlantes']]."','".$check["x".$_POST['pc']['multimedia_mic']]."','";
   $sql.=$check["x".$_POST['pc']['lan_tipo']]."','".$check["x".$_POST['pc']['software_so']]."','".$check["x".$_POST['pc']['software_oficina']]."','".$check["x".$_POST['pc']['garantia']]."','".$_ses_user_login."',".$_POST['licitacion'].",".$_POST['renglon'].");";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }//fin else
}//fin if

if ($_POST['protocolo']==2)
{if ($filas_encontradas>0)
  {$sql="update protocolo set check_temo='".$check["x".$_POST['pc']['teclado']]."', garantia='".$_POST['servidor_garantia']."', comentarios='".$_POST['comentario']."', tipo='".$_POST['tipo_protocolo']."', micro_modelo='".$_POST['servidor_micro_modelo']."', micro_cache='".$_POST['servidor_micro_cache']."',micro_cantidad='".$_POST['servidor_micro_cantidad']."', check_gati='".$check["x".$_POST['servidor']['gabinete_tipo']]."', check_mimo='".$check["x".$_POST['servidor']['micro_tipo']]."', check_mica='".$check["x".$_POST['servidor']['micro_cantidad']]."', check_mich='".$check["x".$_POST['servidor']['micro_cache']]."', ";
   $sql.="memoria_tipo='".$_POST['servidor_memoria_tipo']."', memoria_tam='".$_POST['servidor_memoria_tam']."', memoria_expansion='".$_POST['servidor_memoria_expansion']."', ";
   $sql.="storage_tipo='".$_POST['servidor_storage_tipo']."', storage_interface='".$_POST['servidor_storage_interface']."', storage_rpm='".$_POST['servidor_storage_rpm']."', storage_tamaño='".$_POST['servidor_storage_tamaño']."', storage_cantidad='".$_POST['servidor_storage_cantidad']."', storage_raid='".$_POST['servidor_storage_raid']."', ";
   $sql.="backup_tipo='".$_POST['servidor_backup_tipo']."', backup_modelo='".$_POST['servidor_backup_extras']."',backup_extras='".$_POST['servidor_backup_extras']."', ";
   $sql.="video_tamaño='".$_POST['servidor_video_tamaño']."', expansion_pci='".$_POST['servidor_expansion_pci']."', monitor_tamaño='".$_POST['servidor_monitor_tamaño']."', monitor_rack='".$_POST['servidor_monitor_rack']."', ";
   $sql.="teclado_mouse='".$_POST['servidor_teclado_mouse']."', switch_ports='".$_POST['servidor_switch_ports']."', software_so='".$_POST['servidor_software_so']."', gabinete_tipo='".$_POST['servidor_gabinete_tipo']."', ";
   $sql.="check_meti='".$check["x".$_POST['servidor']['mem_tipo']]."', check_meta='".$check["x".$_POST['servidor']['mem_tamaño']]."' ,check_meex='".$check["x".$_POST['servidor']['mem_expansion']]."', ";
   $sql.="check_vita='".$check["x".$_POST['servidor']['video_tamaño']]."', check_expc='".$check["x".$_POST['servidor']['expansion_pci']]."', check_stti='".$check["x".$_POST['servidor']['storage_tipo']]."' ,";
   $sql.="check_stin='".$check["x".$_POST['servidor']['storage_interface']]."', check_rpm='".$check["x".$_POST['servidor']['storage_rpm']]."', ";
   $sql.="check_stta='".$check["x".$_POST['servidor']['storage_tamaño']]."', ";
   $sql.="check_stca='".$check["x".$_POST['servidor']['storage_cantidad']]."', check_straid='".$check["x".$_POST['servidor']['storage_raid']]."', check_bati='".$check["x".$_POST['servidor']['backup_tipo']]."', check_bamo='".$check["x".$_POST['servidor']['backup_modelo']]."', check_baex='".$check["x".$_POST['servidor']['backup_extras']]."', check_mota='".$check["x".$_POST['servidor']['monitor_tamaño']]."', ";
   $sql.="check_mora='".$check["x".$_POST['servidor']['monitor_rack']]."', ";
   $sql.="check_switch='".$check["x".$_POST['servidor']['swich_ports']]."', check_soso='".$check["x".$_POST['servidor']['sistema_oper']]."', ";
   $sql.="check_gara='".$check["x".$_POST['servidor']['garantia']]."' where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and check_gati is not NULL;";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }
  else //primer protocolo que lleno
  {$sql="insert into protocolo(check_temo,nro_version,garantia,comentarios,tipo,micro_modelo,micro_cache,micro_cantidad,";
   $sql.="memoria_tipo,memoria_tam,memoria_expansion,";
   $sql.="storage_tipo,storage_interface,storage_rpm,storage_tamaño,storage_cantidad,storage_raid,";
   $sql.="backup_tipo,backup_modelo,backup_extras,";
   $sql.="video_tamaño,expansion_pci,monitor_tamaño,monitor_rack,";
   $sql.="teclado_mouse,switch_ports,software_so,gabinete_tipo,check_gati,check_mimo,check_mica,check_mich,check_meti,check_meta,check_meex,check_vita,";
   $sql.="check_expc,check_stti,check_stin,check_rpm,check_stta,check_stca,check_straid,check_bati,check_bamo,check_baex,";
   $sql.="check_mota,check_mora,check_switch,check_soso,check_gara,usuario,nro_licitacion,id_renglon) values('".$check["x".$_POST['pc']['teclado']]."', ";
   $sql.=$_POST['version'].",'".$_POST['servidor_garantia']."','".$_POST['comentario']."','".$_POST['tipo_protocolo']."','".$_POST['servidor_micro_modelo']."','".$_POST['servidor_micro_cache']."','".$_POST['servidor_micro_cantidad']."','";
   $sql.=$_POST['servidor_memoria_tipo']."','".$_POST['servidor_memoria_tam']."','".$_POST['servidor_memoria_expansion']."','";
   $sql.=$_POST['servidor_storage_tipo']."','".$_POST['servidor_storage_interface']."','".$_POST['servidor_storage_rpm']."','".$_POST['servidor_storage_tamaño']."','".$_POST['servidor_storage_cantidad']."','".$_POST['servidor_storage_raid']."','";
   $sql.=$_POST['servidor_backup_tipo']."','".$_POST['servidor_backup_modelo']."','".$_POST['servidor_backup_extras']."','";
   $sql.=$_POST['servidor_video_tamaño']."','".$_POST['servidor_expansion_pci']."','".$_POST['servidor_monitor_tamaño']."','".$_POST['servidor_monitor_rack']."','";
   $sql.=$_POST['servidor_teclado_mouse']."','".$_POST['servidor_switch_ports']."','".$_POST['servidor_software_so']."','".$_POST['servidor_gabinete_tipo']."','";
   $sql.=$check["x".$_POST['servidor']['gabinete_tipo']]."','".$check["x".$_POST['servidor']['micro_tipo']]."','".$check["x".$_POST['servidor']['micro_cantidad']]."','";
   $sql.=$check["x".$_POST['servidor']['micro_cache']]."','".$check["x".$_POST['servidor']['mem_tipo']]."','".$check["x".$_POST['servidor']['mem_tamaño']]."','";
   $sql.=$check["x".$_POST['servidor']['mem_expansion']]."','".$check["x".$_POST['servidor']['video_tamaño']]."','".$check["x".$_POST['servidor']['expansion_pci']]."','";
   $sql.=$check["x".$_POST['servidor']['storage_tipo']]."','".$check["x".$_POST['servidor']['storage_interface']]."','".$check["x".$_POST['servidor']['storage_rpm']]."','";
   $sql.=$check["x".$_POST['servidor']['storage_tamaño']]."','".$check["x".$_POST['servidor']['storage_cantidad']]."','".$check["x".$_POST['servidor']['storage_raid']]."','".$check["x".$_POST['servidor']['backup_tipo']]."','".$check["x".$_POST['servidor']['backup_modelo']]."','".$check["x".$_POST['servidor']['backup_extras']]."','";
   $sql.=$check["x".$_POST['servidor']['monitor_tamaño']]."','".$check["x".$_POST['servidor']['monitor_rack']]."','".$check["x".$_POST['servidor']['swich_ports']]."','".$check["x".$_POST['servidor']['sistema_oper']]."','".$check["x".$_POST['servidor']['garantia']]."','".$_ses_user_login."',".$_POST['licitacion'].",".$_POST['renglon'].");";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }//fin else
}//fin if

if ($_POST['protocolo']==3)
{if ($filas_encontradas>0)
  {$sql="update protocolo set check_temo='".$check["x".$_POST['pc']['teclado']]."', check_imti='".$check["x".$_POST['impresora']['tipo']]."', check_imco='".$check["x".$_POST['impresora']['conexion']]."', check_imppm='".$check["x".$_POST['impresora']['ppm']]."', check_imram='".$check["x".$_POST['impresora']['ram']]."', ";
   $sql.="tipo='".$_POST['protocolo_tipo']."',impresora_tipo='".$_POST['impresora_tipo']."', impresora_conexion='".$_POST['impresora_conexion']."', impresora_ppm='".$_POST['impresora_ppm']."', impresora_ram='".$_POST['impresora_ram']."', impresora_be='".$_POST['impresora_be']."',impresora_duplex='".$_POST['impresora_duplex']."',";
   $sql.="impresora_red='".$_POST['impresora_red']."', impresora_extras='".$_POST['impresora_extras']."', impresora_garantia='".$_POST['impresora_garantia']."',comentarios='".$_POST['comentario']."', ";
   $sql.="check_imbe='".$check["x".$_POST['impresora']['hojas']]."', check_imdu='".$check["x".$_POST['impresora']['duplex']]."' ,check_imred='".$check["x".$_POST['impresora']['interface']]."', ";
   $sql.="check_imex='".$check["x".$_POST['impresora']['extras']]."', check_imga='".$check["x".$_POST['impresora']['garantia']]."' where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and check_imred is not NULL;";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }
  else //primer protocolo que lleno
  {$sql="insert into protocolo(check_temo,nro_version,check_imti,check_imco,check_imppm,check_imram,check_imbe,check_imdu,check_imred,check_imex,";
   $sql.="check_imga,usuario,nro_licitacion,id_renglon,tipo,";
   $sql.="impresora_tipo,impresora_conexion,impresora_ppm,impresora_ram,impresora_be,impresora_duplex,";
   $sql.="impresora_red,impresora_extras,impresora_garantia,";
   $sql.="comentarios) values('".$check["x".$_POST['pc']['teclado']]."', ";
   $sql.=$_POST['version'].",'".$check["x".$_POST['impresora']['tipo']]."','".$check["x".$_POST['impresora']['conexion']]."','".$check["x".$_POST['impresora']['ppm']]."','";
   $sql.=$check["x".$_POST['impresora']['ram']]."','".$check["x".$_POST['impresora']['hojas']]."','".$check["x".$_POST['impresora']['duplex']]."','";
   $sql.=$check["x".$_POST['impresora']['interface']]."','".$check["x".$_POST['impresora']['extras']]."','".$check["x".$_POST['impresora']['garantia']]."','".$_ses_user_login."',".$_POST['licitacion'].",".$_POST['renglon'].",'".$_POST['tipo_protocolo']."','";
   $sql.=$_POST['impresora_tipo']."','".$_POST['impresora_conexion']."','".$_POST['impresora_ppm']."','".$_POST['impresora_ram']."','".$_POST['impresora_be']."','".$_POST['impresora_duplex']."','";
   $sql.=$_POST['impresora_red']."','".$_POST['impresora_extras']."','".$_POST['impresora_garantia']."','".$_POST['comentario']."');";
   $db->Execute($sql) or die($db->ErrorMsg());   
   }//fin else
}//fin if

if ($_POST['protocolo']==4)
{if ($filas_encontradas>0)
  {$sql="update protocolo set check_temo='".$check["x".$_POST['pc']['teclado']]."', check_iso='".$check["x".$_POST['pc']['iso']]."', check_imti='".$check["x".$_POST['impresora']['tipo']]."', check_imco='".$check["x".$_POST['impresora']['conexion']]."', check_imppm='".$check["x".$_POST['impresora']['ppm']]."', check_imram='".$check["x".$_POST['impresora']['ram']]."', ";
   $sql.="check_imbe='".$check["x".$_POST['impresora']['hojas']]."', check_imdu='".$check["x".$_POST['impresora']['duplex']]."' ,check_imred='".$check["x".$_POST['impresora']['interface']]."', ";
   $sql.="check_imex='".$check["x".$_POST['impresora']['extras']]."', check_imga='".$check["x".$_POST['impresora']['garantia']]."', check_gati='".$check["x".$_POST['pc']['gabinete_tipo']]."', check_mimo='".$check["x".$_POST['pc']['micro_modelo']]."', check_mive='".$check["x".$_POST['pc']['micro_velocidad']]."', ";
   $sql.="check_meti='".$check["x".$_POST['pc']['mem_tipo']]."', check_meta='".$check["x".$_POST['pc']['mem_tamaño']]."' ,check_diti='".$check["x".$_POST['pc']['disco_tipo']]."', ";
   $sql.="check_dita='".$check["x".$_POST['pc']['disco_tamaño']]."', check_dibu='".$check["x".$_POST['pc']['disco_bus']]."', check_dirpm='".$check["x".$_POST['pc']['disco_rpm']]."' ,";
   $sql.="check_viti='".$check["x".$_POST['pc']['video_tipo']]."', check_vita='".$check["x".$_POST['pc']['video_memoria']]."', ";
   $sql.="check_mota='".$check["x".$_POST['pc']['monitor_tamaño']]."', ";
   $sql.="check_muso='".$check["x".$_POST['pc']['multimedia_sonido']]."', check_mucd='".$check["x".$_POST['pc']['multimedia_cd']]."', check_muwr='".$check["x".$_POST['pc']['multimedia_cdwr']]."', check_mudvd='".$check["x".$_POST['pc']['multimedia_dvd']]."', check_mupa='".$check["x".$_POST['pc']['multimedia_parlantes']]."', check_mumic='".$check["x".$_POST['pc']['multimedia_mic']]."', ";
   $sql.="check_lati='".$check["x".$_POST['pc']['lan_tipo']]."', ";
   $sql.="tipo='".$_POST['tipo_protocolo']."',datamation='".$_POST['data']."', iso='".$_POST['iso']."', iso_tipo='".$_POST['iso_tipo']."', micro_modelo='".$_POST['pc_micro_modelo']."', micro_velocidad='".$_POST['pc_micro_velocidad']."', ";
   $sql.="memoria_tipo='".$_POST['pc_memoria_tipo']."', memoria_tam='".$_POST['pc_memoria_tam']."', disco_tipo='".$_POST['pc_disco_tipo']."' ,disco_tamaño='".$_POST['pc_disco_tamaño']."', disco_bus='".$_POST['pc_disco_bus']."', disco_rpm='".$_POST['pc_disco_rpm']."', ";
   $sql.="video_tipo='".$_POST['pc_video_tipo']."', video_tamaño='".$_POST['pc_video_tamaño']."', monitor_tamaño='".$_POST['pc_monitor_tamaño']."', multimedia_sonido='".$_POST['pc_multimedia_sonido']."', multimedia_cd='".$_POST['pc_multimedia_cd']."', ";
   $sql.="multimedia_cdwr='".$_POST['pc_multimedia_cdwr']."', multimedia_dvd='".$_POST['pc_multimedia_dvd']."', multimedia_parlantes='".$_POST['pc_multimedia_parlantes']."', multimedia_mic='".$_POST['pc_multimedia_mic']."', ";
   $sql.="teclado_mouse='".$_POST['pc_teclado_mouse']."', lan_tipo='".$_POST['pc_lan_tipo']."', software_so='".$_POST['pc_software_so']."', software_oficina='".$_POST['pc_software_oficina']."', gabinete_tipo='".$_POST['pc_gabinete_tipo']."', garantia='".$_POST['pc_garantia']."', comentarios='".$_POST['comentario']."', ";
   $sql.="impresora_tipo='".$_POST['impresora_tipo']."', impresora_conexion='".$_POST['impresora_conexion']."', impresora_ppm='".$_POST['impresora_ppm']."', impresora_ram='".$_POST['impresora_ram']."', impresora_be='".$_POST['impresora_be']."',impresora_duplex='".$_POST['impresora_duplex']."',";
   $sql.="impresora_red='".$_POST['impresora_red']."', impresora_extras='".$_POST['impresora_extras']."', impresora_garantia='".$_POST['impresora_garantia']."', ";
   $sql.="check_soso='".$check["x".$_POST['pc']['software_so']]."', check_soof='".$check["x".$_POST['pc']['software_oficina']]."', ";
   $sql.="check_gara='".$check["x".$_POST['pc']['garantia']]."' where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and check_gati is not NULL;";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }
  else //primer protocolo que lleno
  {$sql="insert into protocolo(check_temo,nro_version,tipo,datamation,iso,iso_tipo,micro_modelo,micro_velocidad,";
   $sql.="memoria_tipo,memoria_tam,disco_tipo,disco_tamaño,disco_bus,disco_rpm,";
   $sql.="video_tipo,video_tamaño,monitor_tamaño,multimedia_sonido,multimedia_cd,";
   $sql.="multimedia_cdwr,multimedia_dvd,multimedia_parlantes,multimedia_mic,";
   $sql.="teclado_mouse,lan_tipo,software_so,software_oficina,gabinete_tipo,";
   $sql.="garantia,comentarios,check_iso,check_imti,check_imco,check_imppm,check_imram,check_imbe,check_imdu,check_imred,check_imex,";
   $sql.="check_imga,check_gati,check_mimo,check_mive,check_meti,check_meta,check_diti,check_dita,check_dibu,";
   $sql.="check_dirpm,check_viti,check_vita,check_mota,check_muso,check_mucd,check_muwr,check_mudvd,check_mupa,check_mumic,";
   $sql.="check_lati,check_soso,check_soof,check_gara,";
   $sql.="impresora_tipo,impresora_conexion,impresora_ppm,impresora_ram,impresora_be,impresora_duplex,";
   $sql.="impresora_red,impresora_extras,impresora_garantia,";
   $sql.="usuario,nro_licitacion,id_renglon) values('".$check["x".$_POST['pc']['teclado']]."', ";
   $sql.=$_POST['version'].",'".$_POST['tipo_protocolo']."','".$_POST['data']."','".$_POST['iso']."','".$_POST['iso_tipo']."','";
   $sql.=$_POST['pc_micro_modelo']."','".$_POST['pc_micro_velocidad']."','";
   $sql.=$_POST['pc_memoria_tipo']."','".$_POST['pc_memoria_tam']."','".$_POST['pc_disco_tipo']."','".$_POST['pc_disco_tamaño']."','";
   $sql.=$_POST['pc_disco_bus']."','".$_POST['pc_disco_rpm']."','";
   $sql.=$_POST['pc_video_tipo']."','".$_POST['pc_video_tamaño']."','".$_POST['pc_monitor_tamaño']."','".$_POST['pc_multimedia_sonido']."','".$_POST['pc_multimedia_cd']."','";
   $sql.=$_POST['pc_multimedia_cdwr']."','".$_POST['pc_multimedia_dvd']."','".$_POST['pc_multimedia_parlantes']."','".$_POST['pc_multimedia_mic']."','";
   $sql.=$_POST['pc_teclado_mouse']."','".$_POST['pc_lan_tipo']."','".$_POST['pc_software_so']."','".$_POST['pc_software_oficina']."','".$_POST['pc_gabinete_tipo']."','";
   $sql.=$_POST['pc_garantia']."','".$_POST['comentario']."','";
   $sql.=$check["x".$_POST['pc']['iso']]."','".$check["x".$_POST['impresora']['tipo']]."','".$check["x".$_POST['impresora']['conexion']]."','".$check["x".$_POST['impresora']['ppm']]."','";
   $sql.=$check["x".$_POST['impresora']['ram']]."','".$check["x".$_POST['impresora']['hojas']]."','".$check["x".$_POST['impresora']['duplex']]."','";
   $sql.=$check["x".$_POST['impresora']['interface']]."','".$check["x".$_POST['impresora']['extras']]."','".$check["x".$_POST['impresora']['garantia']]."','";
   $sql.=$check["x".$_POST['pc']['gabinete_tipo']]."','".$check["x".$_POST['pc']['micro_modelo']]."','".$check["x".$_POST['pc']['micro_velocidad']]."','";
   $sql.=$check["x".$_POST['pc']['mem_tipo']]."','".$check["x".$_POST['pc']['mem_tamaño']]."','".$check["x".$_POST['pc']['disco_tipo']]."','";
   $sql.=$check["x".$_POST['pc']['disco_tamaño']]."','".$check["x".$_POST['pc']['disco_bus']]."','".$check["x".$_POST['pc']['disco_rpm']]."','";
   $sql.=$check["x".$_POST['pc']['video_tipo']]."','".$check["x".$_POST['pc']['video_memoria']]."','".$check["x".$_POST['pc']['monitor_tamaño']]."','";
   $sql.=$check["x".$_POST['pc']['multimedia_sonido']]."','".$check["x".$_POST['pc']['multimedia_cd']]."','".$check["x".$_POST['pc']['multimedia_cdwr']]."','".$check["x".$_POST['pc']['multimedia_dvd']]."','".$check["x".$_POST['pc']['multimedia_parlantes']]."','".$check["x".$_POST['pc']['multimedia_mic']]."','";
   $sql.=$check["x".$_POST['pc']['lan_tipo']]."','".$check["x".$_POST['pc']['software_so']]."','".$check["x".$_POST['pc']['software_oficina']]."','".$check["x".$_POST['pc']['garantia']]."','";
   $sql.=$_POST['impresora_tipo']."','".$_POST['impresora_conexion']."','".$_POST['impresora_ppm']."','".$_POST['impresora_ram']."','".$_POST['impresora_be']."','".$_POST['impresora_duplex']."','";
   $sql.=$_POST['impresora_red']."','".$_POST['impresora_extras']."','".$_POST['impresora_garantia']."','";
   $sql.=$_ses_user_login."',".$_POST['licitacion'].",".$_POST['renglon'].");";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }//fin else
}//fin if

if ($_POST['protocolo']==5)
{if ($filas_encontradas>0)
  {$sql="update protocolo set check_temo='".$check["x".$_POST['pc']['teclado']]."', garantia='".$_POST['servidor_garantia']."', comentarios='".$_POST['comentario']."', check_imti='".$check["x".$_POST['impresora']['tipo']]."', check_imco='".$check["x".$_POST['impresora']['conexion']]."', check_imppm='".$check["x".$_POST['impresora']['ppm']]."', check_imram='".$check["x".$_POST['impresora']['ram']]."', ";
   $sql.="impresora_tipo='".$_POST['impresora_tipo']."', impresora_conexion='".$_POST['impresora_conexion']."', impresora_ppm='".$_POST['impresora_ppm']."', impresora_ram='".$_POST['impresora_ram']."', impresora_be='".$_POST['impresora_be']."',impresora_duplex='".$_POST['impresora_duplex']."', ";
   $sql.="impresora_red='".$_POST['impresora_red']."', impresora_extras='".$_POST['impresora_extras']."', impresora_garantia='".$_POST['impresora_garantia']."', ";
   $sql.="tipo='".$_POST['tipo_protocolo']."', micro_modelo='".$_POST['servidor_micro_modelo']."', micro_cache='".$_POST['servidor_micro_cache']."',micro_cantidad='".$_POST['servidor_micro_cantidad']."', ";
   $sql.="memoria_tipo='".$_POST['servidor_memoria_tipo']."', memoria_tam='".$_POST['servidor_memoria_tam']."', memoria_expansion='".$_POST['servidor_memoria_expansion']."', ";
   $sql.="storage_tipo='".$_POST['servidor_storage_tipo']."', storage_interface='".$_POST['servidor_storage_interface']."', storage_rpm='".$_POST['servidor_storage_rpm']."', storage_tamaño='".$_POST['servidor_storage_tamaño']."', storage_cantidad='".$_POST['servidor_storage_cantidad']."', storage_raid='".$_POST['servidor_storage_raid']."', ";
   $sql.="backup_tipo='".$_POST['servidor_backup_tipo']."', backup_modelo='".$_POST['servidor_backup_extras']."',backup_extras='".$_POST['servidor_backup_extras']."', ";
   $sql.="video_tamaño='".$_POST['servidor_video_tamaño']."', expansion_pci='".$_POST['servidor_expansion_pci']."', monitor_tamaño='".$_POST['servidor_monitor_tamaño']."', monitor_rack='".$_POST['servidor_monitor_rack']."', ";
   $sql.="teclado_mouse='".$_POST['servidor_teclado_mouse']."', switch_ports='".$_POST['servidor_switch_ports']."', software_so='".$_POST['servidor_software_so']."', gabinete_tipo='".$_POST['servidor_gabinete_tipo']."', ";
   $sql.="check_imbe='".$check["x".$_POST['impresora']['hojas']]."', check_imdu='".$check["x".$_POST['impresora']['duplex']]."' ,check_imred='".$check["x".$_POST['impresora']['interface']]."', ";
   $sql.="check_imex='".$check["x".$_POST['impresora']['extras']]."', check_imga='".$check["x".$_POST['impresora']['garantia']]."',";
   $sql.="check_gati='".$check["x".$_POST['servidor']['gabinete_tipo']]."', check_mimo='".$check["x".$_POST['servidor']['micro_tipo']]."', check_mica='".$check["x".$_POST['servidor']['micro_cantidad']]."', check_mich='".$check["x".$_POST['servidor']['micro_cache']]."', ";
   $sql.="check_meti='".$check["x".$_POST['servidor']['mem_tipo']]."', check_meta='".$check["x".$_POST['servidor']['mem_tamaño']]."' ,check_meex='".$check["x".$_POST['servidor']['mem_expansion']]."', ";
   $sql.="check_vita='".$check["x".$_POST['servidor']['video_tamaño']]."', check_expc='".$check["x".$_POST['servidor']['expansion_pci']]."', check_stti='".$check["x".$_POST['servidor']['storage_tipo']]."',";
   $sql.="check_stin='".$check["x".$_POST['servidor']['storage_interface']]."', check_rpm='".$check["x".$_POST['servidor']['storage_rpm']]."', ";
   $sql.="check_stta='".$check["x".$_POST['servidor']['storage_tamaño']]."', ";
   $sql.="check_stca='".$check["x".$_POST['servidor']['storage_cantidad']]."', check_straid='".$check["x".$_POST['servidor']['storage_raid']]."', check_bati='".$check["x".$_POST['servidor']['backup_tipo']]."', check_bamo='".$check["x".$_POST['servidor']['backup_modelo']]."', check_baex='".$check["x".$_POST['servidor']['backup_extras']]."', check_mota='".$check["x".$_POST['servidor']['monitor_tamaño']]."', ";
   $sql.="check_mora='".$check["x".$_POST['servidor']['monitor_rack']]."', ";
   $sql.="check_switch='".$check["x".$_POST['servidor']['swich_ports']]."', check_soso='".$check["x".$_POST['servidor']['sistema_oper']]."', ";
   $sql.="check_gara='".$check["x".$_POST['servidor']['garantia']]."' where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and check_gati is not NULL";
   $db->Execute($sql) or die($db->ErrorMsg());   
 }
  else //primer protocolo que lleno
  {$sql="insert into protocolo(check_temo,nro_version,garantia,comentarios,tipo,micro_modelo,micro_cache,micro_cantidad,";
   $sql.="memoria_tipo,memoria_tam,memoria_expansion,";
   $sql.="storage_tipo,storage_interface,storage_rpm,storage_tamaño,storage_cantidad,storage_raid,";
   $sql.="backup_tipo,backup_modelo,backup_extras,";
   $sql.="video_tamaño,expansion_pci,monitor_tamaño,monitor_rack,";
   $sql.="teclado_mouse,switch_ports,software_so,gabinete_tipo,check_imti,check_imco,check_imppm,check_imram,check_imbe,check_imdu,check_imred,check_imex,";
   $sql.="check_imga,usuario,nro_licitacion,id_renglon,check_gati,check_mimo,check_mica,check_mich,check_meti,check_meta,check_meex,check_vita,";
   $sql.="check_expc,check_stti,check_stin,check_rpm,check_stta,check_stca,check_straid,check_bati,check_bamo,check_baex,";
   $sql.="check_mota,check_mora,check_switch,check_soso,check_gara,impresora_tipo,impresora_conexion,impresora_ppm,impresora_ram,impresora_be,impresora_duplex,";
   $sql.="impresora_red,impresora_extras,impresora_garantia) values('".$check["x".$_POST['pc']['teclado']]."', ";
   $sql.=$_POST['version'].",'".$_POST['servidor_garantia']."','".$_POST['comentario']."','".$_POST['tipo_protocolo']."','".$_POST['servidor_micro_modelo']."','".$_POST['servidor_micro_cache']."','".$_POST['servidor_micro_cantidad']."','";
   $sql.=$_POST['servidor_memoria_tipo']."','".$_POST['servidor_memoria_tam']."','".$_POST['servidor_memoria_expansion']."','";
   $sql.=$_POST['servidor_storage_tipo']."','".$_POST['servidor_storage_interface']."','".$_POST['servidor_storage_rpm']."','".$_POST['servidor_storage_tamaño']."','".$_POST['servidor_storage_cantidad']."','".$_POST['servidor_storage_raid']."','";
   $sql.=$_POST['servidor_backup_tipo']."','".$_POST['servidor_backup_modelo']."','".$_POST['servidor_backup_extras']."','";
   $sql.=$_POST['servidor_video_tamaño']."','".$_POST['servidor_expansion_pci']."','".$_POST['servidor_monitor_tamaño']."','".$_POST['servidor_monitor_rack']."','";
   $sql.=$_POST['servidor_teclado_mouse']."','".$_POST['servidor_switch_ports']."','".$_POST['servidor_software_so']."','".$_POST['servidor_gabinete_tipo']."','";
   $sql.=$check["x".$_POST['impresora']['tipo']]."','".$check["x".$_POST['impresora']['conexion']]."','".$check["x".$_POST['impresora']['ppm']]."','";
   $sql.=$check["x".$_POST['impresora']['ram']]."','".$check["x".$_POST['impresora']['hojas']]."','".$check["x".$_POST['impresora']['duplex']]."','";
   $sql.=$check["x".$_POST['impresora']['interface']]."','".$check["x".$_POST['impresora']['extras']]."','".$check["x".$_POST['impresora']['garantia']]."','".$_ses_user_login."',".$_POST['licitacion'].",".$_POST['renglon'].",'".$check["x".$_POST['servidor']['gabinete_tipo']]."','".$check["x".$_POST['servidor']['micro_tipo']]."','".$check["x".$_POST['servidor']['micro_cantidad']]."','";
   $sql.=$check["x".$_POST['servidor']['micro_cache']]."','".$check["x".$_POST['servidor']['mem_tipo']]."','".$check["x".$_POST['servidor']['mem_tamaño']]."','";
   $sql.=$check["x".$_POST['servidor']['mem_expansion']]."','".$check["x".$_POST['servidor']['video_tamaño']]."','".$check["x".$_POST['servidor']['expansion_pci']]."','";
   $sql.=$check["x".$_POST['servidor']['storage_tipo']]."','".$check["x".$_POST['servidor']['storage_interface']]."','".$check["x".$_POST['servidor']['storage_rpm']]."','";
   $sql.=$check["x".$_POST['servidor']['storage_tamaño']]."','".$check["x".$_POST['servidor']['storage_cantidad']]."','".$check["x".$_POST['servidor']['storage_raid']]."','".$check["x".$_POST['servidor']['backup_tipo']]."','".$check["x".$_POST['servidor']['backup_modelo']]."','".$check["x".$_POST['servidor']['backup_extras']]."','";
   $sql.=$check["x".$_POST['servidor']['monitor_tamaño']]."','".$check["x".$_POST['servidor']['monitor_rack']]."','".$check["x".$_POST['servidor']['swich_ports']]."','".$check["x".$_POST['servidor']['sistema_oper']]."','".$check["x".$_POST['servidor']['garantia']]."','".$_POST['impresora_tipo']."','".$_POST['impresora_conexion']."','".$_POST['impresora_ppm']."','".$_POST['impresora_ram']."','".$_POST['impresora_be']."','".$_POST['impresora_duplex']."','";
   $sql.=$_POST['impresora_red']."','".$_POST['impresora_extras']."','".$_POST['impresora_garantia']."');";
   $db->Execute($sql) or die($db->ErrorMsg());   
  }//fin else
}//fin if

/*if ($_POST['Terminar']=="terminado")
{$sql="update protocolo set terminado='t',fecha_terminado='".$fecha."' where nro_licitacion=".$_POST['licitacion']." and id_renglon=".$_POST['renglon']." and usuario='".$_ses_user_login."' and nro_version=".$_POST['version']." and ((check_gati is not NULL) or (check_imred is not NULL));"; 
 $db->Execute($sql) or die($db->ErrorMsg());   
}*/
?>