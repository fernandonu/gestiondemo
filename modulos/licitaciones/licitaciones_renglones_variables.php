<?php

//Pagina que contiene las variables para realizar la oferta de las
//licitaciones
$cantidad_adicionales=20;


$kit=array("descripcion"=>"Kit",
           "cantidad"=>"cantidad_kit",
           "select"=>"select_kit",
           "descripcion_precio"=>"desc_precio_kit",
           "descripcion_precio_viejo"=>"desc_precio_v_kit",
           "precio"=>"precio_kit",
           "nuevo_precio"=>"nuevo_p_kit",
           "tipo"=>"kit",
           "flag"=>"flag_kit",
           "idproducto"=>"idproducto_kit"
           );

$floppy=array("descripcion"=>"Floppy",
           "cantidad"=>"cantidad_floppy",
           "select"=>"select_floppy",
           "descripcion_precio"=>"desc_precio_floppy",
           "descripcion_precio_viejo"=>"desc_precio_v_floppy",
           "precio"=>"precio_floppy",
           "nuevo_precio"=>"nuevo_p_floppy",
           "tipo"=>"floppy",
           "flag"=>"flag_floppy",
           "idproducto"=>"idproducto_floppy"
           );

$procesador=array("descripcion"=>"Microprocesador",
                  "cantidad"=>"cantidad_micro",
                  "select"=>"select_micro",
                  "descripcion_precio"=>"desc_precio_micro",
                  "descripcion_precio_viejo"=>"desc_precio_v_micro",
                  "precio"=>"precio_micro",
                  "nuevo_precio"=>"nuevo_p_micro",
                  "tipo"=>"micro",
                  "flag"=>"flag_micro",
                  "idproducto"=>"idproducto_micro"
                  );
//placa madre
$placa_madre=array("descripcion"=>"Placa Madre",
                   "cantidad"=>"cantidad_madre",
                   "select"=>"select_madre",
                   "descripcion_precio"=>"desc_precio_madre",
                   "descripcion_precio_viejo"=>"desc_precio_v_madre",
                   "precio"=>"precio_madre",
                   "nuevo_precio"=>"nuevo_p_madre",
                   "tipo"=>"placa madre",
                   "flag"=>"flag_madre",
                   "idproducto"=>"idproducto_madre"
                  );

//memoria
$memoria=array("descripcion"=>"Memoria",
               "cantidad"=>"cantidad_memoria",
               "select"=>"select_memoria",
               "descripcion_precio"=>"desc_precio_memoria",
               "descripcion_precio_viejo"=>"desc_precio_v_memoria",
               "precio"=>"precio_memoria",
               "nuevo_precio"=>"nuevo_p_memoria",
               "tipo"=>"memoria",
               "flag"=>"flag_memoria",
               "idproducto"=>"idproducto_memoria"
               );

//disco rigido
$disco=array("descripcion"=>"Disco Rigido",
             "cantidad"=>"cantidad_disco",
             "select"=>"select_disco",
             "descripcion_precio"=>"desc_precio_disco",
             "descripcion_precio_viejo"=>"desc_precio_v_disco",
             "precio"=>"precio_disco",
             "nuevo_precio"=>"nuevo_p_disco",
             "tipo"=>"disco rigido",
             "flag"=>"flag_disco",
             "idproducto"=>"idproducto_disco"

             );
//cd romm
$cdrom=array("descripcion"=>"CD ROM",
             "cantidad"=>"cantidad_cd",
             "select"=>"select_cd",
             "descripcion_precio"=>"desc_precio_cd",
             "descripcion_precio_viejo"=>"desc_precio_v_cd",
             "precio"=>"precio_cd",
             "nuevo_precio"=>"nuevo_p_cd",
             "tipo"=>"cdrom",
             "flag"=>"flag_cd",
             "idproducto"=>"idproducto_cd"

             );


//monitor
$monitor=array("descripcion"=>"Monitor",
               "cantidad"=>"cantidad_monitor",
               "select"=>"select_monitor",
               "descripcion_precio"=>"desc_precio_monitor",
               "descripcion_precio_viejo"=>"desc_precio_v_monitor",
               "precio"=>"precio_monitor",
               "nuevo_precio"=>"nuevo_p_monitor",
               "tipo"=>"monitor",
               "flag"=>"flag_monitor",
               "idproducto"=>"idproducto_monitor"

               );
//cd de recuperacion
$cd_de_recuperacion=array("descripcion"=>"CD de Recuperación",
                           "cantidad"=>"cantidad_cd_de_recuperacion",
                           "select"=>"select_cd_de_recuperacion",
                           "descripcion_precio"=>"desc_precio_cd_de_recuperacion",
                           "descripcion_precio_viejo"=>"desc_precio_cd_de_recuperacion",
                           "precio"=>"precio_cd_de_recuperacion",
                           "nuevo_precio"=>"nuevo_cd_de_recuperacion",
                           "tipo"=>"cd y manuales",
                           "flag"=>"flag_cd_de_recuperacion",
                           "idproducto"=>"idproducto_cd_de_recuperacion"
               );               
//sistema operativo
$sistema_operativo=array("descripcion"=>"Sistema Operativo",
                 "cantidad"=>"cantidad_sistemaoperativo",
                 "select"=>"select_sistemaoperativo",
                 "descripcion_precio"=>"desc_precio_sistemaoperativo",
                 "descripcion_precio_viejo"=>"desc_precio_v_sistemaoperativo",
                 "precio"=>"precio_sistemaoperativo",
                 "nuevo_precio"=>"nuevo_p_sistemaoperativo",
                 "tipo"=>"sistema operativo",
                 "flag"=>"flag_sistemaoperativo",
                 "idproducto"=>"idproducto_sistemaoperativo"
                 );
//Etiquetas                 
$etiquetas=array("descripcion"=>"Etiquetas SO",
                         "cantidad"=>"cantidad_etiquetas_so",
                         "select"=>"select_etiquetas_so",
                         "descripcion_precio"=>"desc_precio_etiquetas_so",
                         "descripcion_precio_viejo"=>"desc_precio_v_etiquetas_so",
                         "precio"=>"precio_etiquetas_so",
                         "nuevo_precio"=>"nuevo_p_etiquetas_so",
                         "tipo"=>"Etiquetas Sistema Operativo",
                         "flag"=>"flag_etiquetas_so",
                         "idproducto"=>"idproducto_etiquetas_so"
                         );
                         
//Conexo
$conexo=array("descripcion"=>"Conexo",
              //"cantidad"=>"cantidad_conexo",
              "cantidad"=>"",
              "select"=>"select_conexo",
              "descripcion_precio"=>"desc_precio_conexo",
              "descripcion_precio_viejo"=>"desc_precio_v_conexo",
              "precio"=>"precio_conexo",
              "nuevo_precio"=>"nuevo_p_conexo",
              "tipo"=>"conexos",
              "flag"=>"flag_conexo",
              "idproducto"=>"idproducto_conexo"

             );
//Garantia
$garantia=array("descripcion"=>"Garantía",
                //"cantidad"=>"cantidad_garantia",
                "cantidad"=>"",
                "select"=>"select_garantia",
                "descripcion_precio"=>"",
                "descripcion_precio_viejo"=>"",
                "precio"=>"",
                "nuevo_precio"=>"",
                "tipo"=>"garantia",
                "flag"=>"flag_garantia",
                "idproducto"=>"idproducto_garantia"
               );

$video=array("descripcion"=>"Placa de Video",
             "cantidad"=>"cantidad_video",
             "select"=>"select_video",
             "descripcion_precio"=>"desc_precio_video",
             "descripcion_precio_viejo"=>"desc_precio_v_video",
             "precio"=>"precio_video",
             "nuevo_precio"=>"nuevo_p_video",
             "tipo"=>"video",
             "flag"=>"flag_video",
             "idproducto"=>"idproducto_video"

             );
$grabadora=array("descripcion"=>"Grabadora",
                 "cantidad"=>"cantidad_grabadora",
                 "select"=>"select_grabadora",
                 "descripcion_precio"=>"desc_precio_grabadora",
                 "descripcion_precio_viejo"=>"desc_precio_v_grabadora",
                 "precio"=>"precio_grabadora",
                 "nuevo_precio"=>"nuevo_p_grabadora",
                 "tipo"=>"grabadora",
                 "flag"=>"flag_grabadora",
                 "idproducto"=>"idproducto_grabadora"

                 );
$dvd=array("descripcion"=>"DVD",
           "cantidad"=>"cantidad_dvd",
           "select"=>"select_dvd",
           "descripcion_precio"=>"desc_precio_dvd",
           "descripcion_precio_viejo"=>"desc_precio_v_dvd",
           "precio"=>"precio_dvd",
           "nuevo_precio"=>"nuevo_p_dvd",
           "tipo"=>"dvd",
           "flag"=>"flag_dvd",
           "idproducto"=>"idproducto_dvd"
           );

$red=array("descripcion"=>"Red",
           "cantidad"=>"cantidad_red",
           "select"=>"select_red",
           "descripcion_precio"=>"desc_precio_red",
           "descripcion_precio_viejo"=>"desc_precio_v_red",
           "precio"=>"precio_red",
           "nuevo_precio"=>"nuevo_p_red",
           "tipo"=>"lan",
           "flag"=>"flag_red",
           "idproducto"=>"idproducto_red"
           );
$modem=array("descripcion"=>"Modem",
           "cantidad"=>"cantidad_modem",
           "select"=>"select_modem",
           "descripcion_precio"=>"desc_precio_modem",
           "descripcion_precio_viejo"=>"desc_precio_v_modem",
           "precio"=>"precio_modem",
           "nuevo_precio"=>"nuevo_p_modem",
           "tipo"=>"modem",
           "flag"=>"flag_modem",
           "idproducto"=>"idproducto_modem"
           );
$zip=array("descripcion"=>"Zip",
           "cantidad"=>"cantidad_zip",
           "select"=>"select_zip",
           "descripcion_precio"=>"desc_precio_zip",
           "descripcion_precio_viejo"=>"desc_precio_v_zip",
           "precio"=>"precio_zip",
           "nuevo_precio"=>"nuevo_p_zip",
           "tipo"=>"zip",
           "flag"=>"flag_zip",
           "idproducto"=>"idproducto_zip"
           );

$impre=array("descripcion"=>"Impresora",
           "cantidad"=>"cantidad_impresora",
           "select"=>"select_impresora",
           "descripcion_precio"=>"desc_precio_impresora",
           "descripcion_precio_viejo"=>"desc_precio_v_impresora",
           "precio"=>"precio_impresora",
           "nuevo_precio"=>"nuevo_p_impresora",
           "tipo"=>"impresora",
           "flag"=>"flag_impresora",
           "idproducto"=>"idproducto_impresora"
           );
 $cable=array("descripcion"=>"Cables",
              "cantidad"=>"cantidad_cables",
           "select"=>"select_cables",
           "descripcion_precio"=>"desc_precio_cables",
           "descripcion_precio_viejo"=>"desc_precio_v_cables",
           "precio"=>"precio_cables",
           "nuevo_precio"=>"nuevo_p_cables",
           "tipo"=>"cables",
           "flag"=>"flag_cables",
           "idproducto"=>"idproducto_cables"
           );

$usb=array("descripcion"=>"USB",
            "cantidad"=>"cantidad_usb",
           "select"=>"select_usb",
           "descripcion_precio"=>"desc_precio_usb",
           "descripcion_precio_viejo"=>"desc_precio_v_usb",
           "precio"=>"precio_usb",
           "nuevo_precio"=>"nuevo_p_usb",
           "tipo"=>"USB",
           "flag"=>"flag_usb",
           "idproducto"=>"idproducto_usb"
           );
//Configuracion de la maquina con los productos basicos
$maquina_basica=array(0=>$kit,
                      1=>$floppy,
                      2=>$procesador,
                      3=>$placa_madre,
                      4=>$memoria,
                      5=>$disco,
                      6=>$cdrom,
                      7=>$monitor,
                      8=>$sistema_operativo,
                      9=>$etiquetas,
                      10=>$cd_de_recuperacion,
                      11=>$usb,
                      12=>$conexo,
                      13=>$garantia
                      );
//Configuracion de la maquina con los productos adicionales
$maquina_adicional=array(
                     0=>$video,
                     1=>$grabadora,
                     2=>$dvd,
                     3=>$red,
                     4=>$modem,
                     5=>$zip
                    );


$impresora=array(0=>$impre,
                 1=>$cable,
                 2=>$conexo,
                 3=>$garantia);

$software=array(0=>$garantia);

$otro=array(0=>$garantia);
?>