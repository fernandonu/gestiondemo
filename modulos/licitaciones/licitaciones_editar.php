<?
/*
Author: MAC
Fecha: 2/07/2004
MODIFICADA POR
$Author: fernando $
$Revision: 1.33 $
$Date: 2006/11/27 21:00:01 $
*/

require_once("../../config.php");
require_once("../stock/funciones.php");
require_once("../bancos/balances/funciones_balance.php");
echo $html_header;

cargar_calendario();
extract($_POST);
echo "<br>";

//las diferentes formas de pago
if ($guardar=="Guardar")
{/////////////////////////////////////////////////////////////////////////////////////////////
 
	
 $observaciones=str_replace("\""," ",$observaciones);
 $observaciones=str_replace("\'"," ",$observaciones);
 $direccion=str_replace("\""," ",$direccion);
 $direccion=str_replace("\'"," ",$direccion);
 $proceso_numero=str_replace("\""," ",$proceso_numero);
 $proceso_numero=str_replace("\'"," ",$proceso_numero);
 $expediente=str_replace("\""," ",$expediente);
 $expediente=str_replace("\'"," ",$expediente);
 $valor_pliego=str_replace("\""," ",$valor_pliego);
 $valor_pliego=str_replace("\'"," ",$valor_pliego);
 $otros_garantia_oferta=str_replace("\""," ",$otros_garantia_oferta);
 $otros_garantia_oferta=str_replace("\'"," ",$otros_garantia_oferta);
 $otros_garantia_contrato=str_replace("\""," ",$otros_garantia_contrato);
 $otros_garantia_contrato=str_replace("\'"," ",$otros_garantia_contrato);
 $plazo_impugnacion=str_replace("\""," ",$plazo_impugnacion);
 $plazo_impugnacion=str_replace("\'"," ",$plazo_impugnacion);
 $garantia_impugnacion=str_replace("\""," ",$garantia_impugnacion);
 $garantia_impugnacion=str_replace("\'"," ",$garantia_impugnacion);
 $garantia_bienes=str_replace("\""," ",$garantia_bienes);
 $garantia_bienes=str_replace("\'"," ",$garantia_bienes);
 $vencimiento_presentacion_comentario=str_replace("\""," ",$vencimiento_presentacion_comentario);
 $vencimiento_presentacion_comentario=str_replace("\'"," ",$vencimiento_presentacion_comentario);

 if ($responsables != -1)
     $id_responsable_apertura=$responsables;
     else 
     $id_responsable_apertura="NULL";

 /////////////////////////////////////////////////////////////////////////////////////////////

 $puede_guardar=1;
 $db->StartTrans();
  //traemos el id del estado "Perdida"
  $query="select id_estado from estado where nombre='Perdida'";
  $estado_p=sql($query) or fin_pagina();
  //Si el estado es igual a perdida, controlamos que todos los renglones
  //tengan como ganador a algun comeptidor que no sea CORADIR
  if($_POST['validar_control']=="")
       $validar_control=0;
       else 
       $validar_control=1;

  if($mod_estado==$estado_p->fields['id_estado'])
  {
   $reng_ctrl=control_resultados_renglon($ID, $validar_control);
   if($reng_ctrl!=1)
   {Error("No se puede cambiar el estado de la Licitación - $reng_ctrl");
    $puede_guardar=0;
   }
  }
 if($puede_guardar)
 {
   //garantia de oferta
   $insertar=0;
   if ($monto_numero_oferta!="" || $radio_garantia_oferta!="" || $otros_garantia_oferta!="" || $monto_porcentaje_oferta!="")
      {
       if($id_garantia_de_oferta==""||$id_garantia_de_oferta=="null")//si es vacio, insertamos, sino actualizamos
       {$sql = "select nextval('garantia_de_oferta_id_garantia_de_oferta_seq') as id_garantia_de_oferta";
        $id_rec = sql($sql) or fin_pagina();
        $id_garantia_de_oferta=$id_rec->fields['id_garantia_de_oferta'];
        $insertar=1;
       }
       //si agrego un nuevo tipo de garantia de oferta, la insertamos
       if ($otros_garantia_oferta!="")
          {$sql = "select nextval('tipo_garantia_id_tipo_garantia_seq') as id_tipo_garantia";
           $id_rec = sql($sql) or fin_pagina();
           $id_tipo_garantia=$id_rec->fields['id_tipo_garantia'];
           $sql = "insert into licitaciones_datos_adicionales.tipo_garantia
                   (id_tipo_garantia,nombre,defecto) values ($id_tipo_garantia,'$otros_garantia_oferta',0)";
           sql($sql) or fin_pagina();
          }
       ///////////////////////////////////////////////////
       if ($radio_garantia_oferta) $id_tipo_garantia=$radio_garantia_oferta; else $id_tipo_garantia="NULL";
       if ($radio_garantia_oferta!=1 || $otros_garantia_oferta!="") $exige_reaseguro_oferta=-1;
       if ($exige_reaseguro_oferta=="si") $exige_reaseguro_oferta1=1;
       elseif ($exige_reaseguro_oferta=="no") $exige_reaseguro_oferta1=0; else $exige_reaseguro_oferta1="NULL";
       if ($monto_porcentaje_oferta=="") $monto_porcentaje_oferta="NULL";
       if ($monto_numero_oferta=="") $monto_numero_oferta="NULL";
       ///////////////////////////////////////////////////
       if($insertar)//si es vacio, insertamos, sino actualizamos
       {
        $sql = "insert into licitaciones_datos_adicionales.garantia_de_oferta
                (id_garantia_de_oferta,id_tipo_garantia_ga_oferta,id_moneda_ga_oferta,monto_ga_oferta,porcentaje_ga_oferta,reaseguro_ga_oferta)
                values ($id_garantia_de_oferta,$id_tipo_garantia,$monto_moneda_oferta,$monto_numero_oferta,$monto_porcentaje_oferta,$exige_reaseguro_oferta1)";
       }
       else
       {$sql="update garantia_de_oferta set
              id_tipo_garantia_ga_oferta=$id_tipo_garantia,id_moneda_ga_oferta=$monto_moneda_oferta,
              monto_ga_oferta=$monto_numero_oferta,porcentaje_ga_oferta=$monto_porcentaje_oferta,reaseguro_ga_oferta=$exige_reaseguro_oferta1
              where id_garantia_de_oferta=$id_garantia_de_oferta";
       }
       sql($sql) or fin_pagina();
      }
      elseif($id_garantia_de_oferta)
      {$query="update licitacion set id_garantia_de_oferta=null where id_licitacion=$ID";
       sql($query) or fin_pagina();
       $query="delete from garantia_de_oferta where id_garantia_de_oferta=$id_garantia_de_oferta";
       sql($query) or fin_pagina();
       $id_garantia_de_oferta="";
      }
    //garantia de contrato
    if ($monto_numero_contrato!="" || $radio_garantia_contrato!="" || $otros_garantia_contrato!="" || $monto_porcentaje_contrato!="")
       {$insertar1=0;
       	if($id_garantia_contrato==""||$id_garantia_contrato=="null")//si es vacio, insertamos, sino actualizamos
       	{$sql = "select nextval('garantia_contrato_id_garantia_contrato_seq') as id_garantia_contrato";
         $id_rec = sql($sql) or fin_pagina();
         $id_garantia_contrato=$id_rec->fields['id_garantia_contrato'];
         $insertar1=1;
       	}
        if ($otros_garantia_contrato!="")
           {$sql = "select nextval('tipo_garantia_id_tipo_garantia_seq') as id_tipo_garantia";
            $id_rec = sql($sql) or fin_pagina();
            $id_tipo_garantia=$id_rec->fields['id_tipo_garantia'];
            $sql = "insert into licitaciones_datos_adicionales.tipo_garantia
                    (id_tipo_garantia,nombre,defecto) values ($id_tipo_garantia,'$otros_garantia_contrato',0)";
            sql($sql) or fin_pagina();
           }
        /////////////////////////////////////////////////////////
        if ($radio_garantia_contrato) $id_tipo_garantia=$radio_garantia_contrato; else $id_tipo_garantia="NULL";
        if ($radio_garantia_oferta!=1 || $otros_garantia_oferta!="") $exige_reaseguro_contrato=-1;
        if ($exige_reaseguro_contrato=="si") $exige_reaseguro_contrato1=1;
        elseif ($exige_reaseguro_contrato=="no") $exige_reaseguro_contrato1=0; else $exige_reaseguro_contrato1="NULL";
        if ($monto_porcentaje_contrato=="") $monto_porcentaje_contrato="NULL";
        if ($monto_numero_contrato=="") $monto_numero_contrato="NULL";
        if ($dias_vigencia=="") $dias_vigencia="NULL";
        /////////////////////////////////////////////////////////
        /*if ($radio_garantia_contrato) $id_tipo_garantia=$radio_garantia_contrato;
        if ($exige_reaseguro_contrato=="Si") $exige_reaseguro_contrato1=1; else $exige_reaseguro_contrato1=0;
        if($monto_porcentaje_contrato=="")
         $monto_porcentaje_contrato="null";*/
        if($dias_vigencia=="")
         $dias_vigencia="null";
        if($insertar1)//si es vacio, insertamos, sino actualizamos
        {$sql = "insert into licitaciones_datos_adicionales.garantia_contrato
                (id_garantia_contrato,id_tipo_garantia_ga_contrato,id_moneda_ga_contrato,monto_ga_contrato,porcentaje_ga_contrato,reaseguro_ga_contrato,vigencia,dias_tipo_ga_contrato)
                values ($id_garantia_contrato,$id_tipo_garantia,$monto_moneda_contrato,$monto_numero_contrato,$monto_porcentaje_contrato,$exige_reaseguro_contrato1,$dias_vigencia,'$dias_vigencia_como')";
        }
        else
        {$sql="update garantia_contrato set
               id_tipo_garantia_ga_contrato=$id_tipo_garantia,id_moneda_ga_contrato=$monto_moneda_contrato,
               monto_ga_contrato=$monto_numero_contrato,porcentaje_ga_contrato=$monto_porcentaje_contrato,reaseguro_ga_contrato=$exige_reaseguro_contrato1,
               vigencia=$dias_vigencia,dias_tipo_ga_contrato='$dias_vigencia_como'
               where id_garantia_contrato=$id_garantia_contrato";
        }
        sql($sql) or fin_pagina();
      }
      elseif($id_garantia_contrato)
      {$query="update licitacion set id_garantia_contrato=null where id_licitacion=$ID";
       sql($query) or fin_pagina();
       $query="delete from garantia_contrato where id_garantia_contrato=$id_garantia_contrato";
       sql($query) or fin_pagina();
       $id_garantia_contrato="";
      }

    //impugnacion
    if ($dias_impugnacion!="" || $plazo_impugnacion!="" || $porcentaje_impugnacion!="" || $garantia_impugnacion!="" || $monto_presupuesto_oficial!="")
       {
       	if($dias_impugnacion=="")
       	 $dias_impugnacion="null";
       	if($porcentaje_impugnacion=="")
       	 $porcentaje_impugnacion="null";
       	if($monto_presupuesto_oficial=="")
       	 $monto_presupuesto_oficial="null";
       	if($id_impugnacion==""||$id_impugnacion=="null")
       	{$sql = "select nextval('impugnacion_id_impugnacion_seq') as id_impugnacion";
         $id_rec = sql($sql) or fin_pagina();
         $id_impugnacion = $id_rec->fields['id_impugnacion'];
         $sql = "insert into impugnacion (id_impugnacion,cant_dias,dias_tipo_imp,plazo,porcentaje_imp,monto_imp,id_moneda_imp,porcentaje_texto)
                 values ($id_impugnacion,$dias_impugnacion,'$dias_impugnacion_como','$plazo_impugnacion',$porcentaje_impugnacion,$monto_presupuesto_oficial,$moneda_presupuesto_oficial,'$garantia_impugnacion')";
       	}
       	else
       	{$sql="update impugnacion set
       	       cant_dias=$dias_impugnacion,dias_tipo_imp='$dias_impugnacion_como',
       	       plazo='$plazo_impugnacion',porcentaje_imp=$porcentaje_impugnacion,
       	       monto_imp=$monto_presupuesto_oficial,id_moneda_imp=$moneda_presupuesto_oficial,porcentaje_texto='$garantia_impugnacion'
       	       where id_impugnacion=$id_impugnacion";
       	}
         $id_rec = sql($sql) or fin_pagina();
       }
       elseif($id_impugnacion)
       {$query="update licitacion set id_impugnacion=null where id_licitacion=$ID";
        sql($query) or fin_pagina();

       	$query="delete from impugnacion where id_impugnacion=$id_impugnacion";
        sql($query) or fin_pagina();
        $id_impugnacion="";
       }

   $fecha_modif = date("Y-m-d H:i:s",mktime());
   if ($avisar_antes=="on") $avisar_antes=1; else $avisar_antes=0;
   $fecha_apertura1=fecha_db($fecha_apertura);
   $fecha_apertura1.=" ".$hora.":".$minutos.":00";


   if ($guardar_por_defecto)
   {
    $query_ent="update entidad set direccion='$direccion' where id_entidad=$entidad";
    sql($query_ent) or fin_pagina();
   }


    if($id_garantia_de_oferta=="")
     $id_garantia_de_oferta="null";

     if($id_garantia_contrato=="")
     $id_garantia_contrato="null";

    if($id_impugnacion=="")
     $id_impugnacion="null";

    if($renovacion_automatica!=1)
     $renovacion_automatica=0;

    if($fecha_entrega=="")
     $fecha_entrega1="null";
    else
     $fecha_entrega1="'".fecha_db($fecha_entrega)."'";

    if($vencimiento_presentacion=="")
     $vencimiento_presentacion1="null";
    else
     $vencimiento_presentacion1="'".fecha_db($vencimiento_presentacion)."'";

    if($dias_mantenimiento=="")
     $dias_mantenimiento1="null";
    else
     $dias_mantenimiento1=$dias_mantenimiento;

    if($alternativas_oferta=="")
     $alternativas_oferta="null";

    if($componentes_tipo=="")
     $componentes_tipo="null";

/////////////////////////////////////////////////////////////////////

    if($monto_ofertado=="")
     $monto_ofertado="null";

    if($monto_estimado=="")
     $monto_estimado="null";

    if($monto_ganado=="")
     $monto_ganado="null";

///////////////////////////////////////////////////////////////////



   $sql = "update licitacion set id_entidad=$entidad,dir_entidad='$direccion',id_responsable_apertura=$id_responsable_apertura,";
   //si el valor de $_POST['radio_estado'] es 1, entonces se estan usando los estados normales (si es 2 son estados especiales)
   if ($_POST['radio_estado']==1)
   {
   	  $mod_estado=$_POST['mod_estado'];
      $sql.=" id_estado=$mod_estado,";
   }//de if ($_POST['radio_estado']==1)

   $sql.=" mantenimiento_oferta=$dias_mantenimiento1,mant_oferta_especial='$dias_mantenimiento_como',
           monto_ofertado=$monto_ofertado,monto_estimado=$monto_estimado,monto_ganado=$monto_ganado,
           prorroga_automatica=$renovacion_automatica,forma_de_pago='$forma_pago',plazo_entrega='$plazo_entrega',
           nro_lic_codificado='$proceso_numero',exp_lic_codificado='$expediente',valor_pliego=$valor_pliego,
           id_moneda=$moneda,fecha_apertura='$fecha_apertura1 ',fecha_entrega=$fecha_entrega1,cotizar_alternativas=$cotizar_alternativas,
           id_normas=$alternativas_oferta,id_componentes_nueva_lic=$componentes_tipo,comentario_garantia_bienes='$garantia_bienes',
           exige_muestras=$exige_muestras,vencimiento_muestras=$vencimiento_presentacion1,
           comentarios_muestras='$vencimiento_presentacion_comentario',registro_proveedores='$hay_inscripcion',
           avisar_antes=$avisar_antes,observaciones='$observaciones',
           id_garantia_de_oferta=$id_garantia_de_oferta,id_garantia_contrato=$id_garantia_contrato,id_impugnacion=$id_impugnacion
           where id_licitacion=$ID";
   $id_rec = sql($sql) or fin_pagina();



   //si el estado de la licitacion es Entregada, y antes era otro, se sacan del stock en produccion, los productos
   //que se fueron agrenado al mismo a medida que se entregaban los productos desde el modulo Orden de Compras
   if ($_POST['radio_estado']==1 && $_POST["estado_anterior"]!=$_POST['mod_estado'])
   {
   	 //revisamos cual es el id del estado "Entregada"
   	 $query="select id_estado from estado where nombre='Entregada'";
   	 $est_ent=sql($query,"<br>Error al traer el id del estado Entregada<br>") or fin_pagina();

   	 if($_POST['mod_estado']==$est_ent->fields["id_estado"])
   	 {
   	  //obtengo el neto antes de entregar	
   	  $balance_antes = obtener_neto();	
   	 	
   	  $sql="update  cobranzas set licitacion_entregada=1
             where cobranzas.id_licitacion=$ID and cobranzas.estado='PENDIENTE'";

      sql($sql) or fin_pagina();
   	  desc_en_produccion($ID);
   	  //obtengo el neto despues de entregar
   	  $balance_despues = obtener_neto();
   	  
   	  //guardo en costo real;
      $sql = " select id_costo_real 
               from licitaciones.entrega_estimada
               join licitaciones.costo_real using (id_entrega_estimada)
               where id_licitacion=$ID  and cerrar_1 = 1 and cerrar_2 = 0 ";
      $res = sql($sql) or fin_pagina();
       
      if ($res->recordcount()>0){
      	  $id_costo_real = $res->fields["id_costo_real"];
      	  $sql = "update costo_real set balance_antes = $balance_antes,balance_despues = $balance_despues
      	          where id_costo_real = $id_costo_real  ";
          sql($sql) or fin_pagina();      	  
      }
   	  
   	  
   	 }
   }//de if ($_POST['radio_estado']==1 && $_POST["estado_anterior"]!=$_POST['mod_estado'])

   if (is_array($file_id) and count($file_id) > 0) {
				while (list($mod_file_id, $mod_file_imp) = each($file_id)) {
				//actualizamos la tabla de entregar_lic para indicar si el archivo
				//esta listo para imprimir o no
				if($mod_file_imp=='t')
					 $of_imp=1;
					else
					 $of_imp=0;
				$query="select nombre from archivos where idarchivo=$mod_file_id";
				$nombre_arch=sql($query) or fin_pagina();
 			    $query="update entregar_lic set oferta_lista_imprimir=$of_imp where id_licitacion=$ID and archivo_oferta='".$nombre_arch->fields['nombre']."'";
  			    sql($query) or fin_pagina();
 			    $query="update entregar_lic set oferta_lista_imprimir=$of_imp where id_licitacion=$ID and archivo_oferta='".$nombre_arch->fields['nombre']."'";
				sql($query) or fin_pagina();


				$sql = "update archivos set ";
				$sql .= "imprimir='$mod_file_imp' ";
				$sql .= "where idarchivo=$mod_file_id";
				sql($sql) or fin_pagina();
				}
		}//de if (is_array($file_id) and count($file_id) > 0)

   echo "<table align=center><tr><td><font size='4'><b>Se actualizó correctamente la licitación número: $ID</b></font></td></tr></table>";
   $db->CompleteTrans();

   //traemos los id de estado para: Perdida, Fracasada, Robada
   $query="select nombre,id_estado from licitaciones.estado where nombre='Robada' or nombre='Perdida' or nombre='Fracasada'";
   $estados_mail=sql($query,"<br>Error sl traer los id de los estados que generan mails<br>") or fin_pagina();
   $arr_est=$estados_mail->GetAssoc();

  //traemos el nombre del lider de la licitacion, y el monto ofertado
  $query="select licitacion.monto_ofertado,usuarios.nombre,usuarios.apellido,moneda.simbolo
  		  from licitaciones.licitacion join sistema.usuarios on licitacion.lider=usuarios.id_usuario
  		  join licitaciones.moneda using(id_moneda)
  		  where id_licitacion=$ID";
  $datos_lic_mail=sql($query,"<br>Error al traer los datos de la lic para enviar el mail<br>") or fin_pagina();

  $lider_lic=$datos_lic_mail->fields["nombre"]." ".$datos_lic_mail->fields["apellido"];
  $monto_ofertado_sup=$datos_lic_mail->fields["monto_ofertado"];
  $simbolo_moneda=$datos_lic_mail->fields["simbolo"];

  //enviamos un mail avisando que se cambio el estado de la licitacion, si el estado nuevo es: Perdida, Fracasada, Robada
  if((($simbolo_moneda=='$' && $monto_ofertado_sup>=60000)||($simbolo_moneda=='U$S' && $monto_ofertado_sup>=20000)) && in_array($_POST["mod_estado"],$arr_est))
  {
  	  //traemos el nombre del nuevo estado
	  $query="select nombre from licitaciones.estado where id_estado=".$_POST["mod_estado"];
	  $nbre_estado=sql($query,"<br>Error al traer el nombre del estado nuevo<br>") or fin_pagina();

	  $estado_nuevo=$nbre_estado->fields["nombre"];

   	  $para="licitaciones@coradir.com.ar";
   	  $asunto="La Licitación Nº $ID se pasó a estado $estado_nuevo";
  	  $texto="La Licitación Nº $ID se pasó a estado $estado_nuevo. Esta licitación tiene un monto ofertado superior a $simbolo_moneda $monto_ofertado_sup.\n\n";
  	  $texto.="Atención Lider $lider_lic:\n";
  	  $texto.="Debido a la importancia de esta Licitación, por favor extreme las medidas para ver si podemos impugnar y recuperar este proceso licitatorio.\n\n";
  	  $texto.="Usuario que cambió el estado: ".$_ses_user["name"]." - Fecha: ".date("d/m/Y H:i:s")."\n\n\n";

  	  enviar_mail($para,$asunto,$texto,"","","","");

  }//de if((($simbolo=='$' && $monto_ofertado>60000)||($simbolo=='U\$S' && $monto_ofertado>20000)) && in_array($_POST["mod_estado"],$arr_est))

  detalle($ID);

 }//de 	if($puede_guardar)

}//de if ($guardar=="Guardar")

//fin_pagina();die;



//modifico los eventos de la licitacion
if ($_POST["guardar_evento"]=="Guardar")
        {
         $db->StartTrans();
         //si el evento tiene algo lo inserto
         //si no no hago nada
         $usuario=$_ses_user['name'];
         $evento=$_POST["evento_nuevo"];
         if ($evento){
             $fecha=$_POST["fecha_evento"];
             $hora=$_POST["hora_evento"];
             if (!FechaOk($fecha)) {
                   Error("El formato de la fecha del evento no es válido");
                  }
                  else $fecha=Fecha_db($fecha);
             if (!hora_ok($hora)){
                  Error("El formato de la hora del evento no es válido");
             }
             $fecha="$fecha $hora";
             $sql="insert into eventos_lic ";
             $sql.=" (id_licitacion,evento,fecha,usuario)";
             $sql.=" values";
             $sql.=" ($ID,'$evento','$fecha','$usuario')";
             $sql_array[0]=$sql;
           }

           $cantidad=$_POST["cant_eventos"];
           for($y=0;$y<$cantidad;$y++){
                  $id_eventos=$_POST["id_evento_$y"];
                  $eventos=$_POST["evento_$y"];
                  $ch_eventos=$_POST["ch_evento_$y"];
                   if (!$eventos){
                                 Error("Debe ingresar un evento");
                                 }
                   if (!$ch_eventos) $ch_eventos=0;

                   $sql=" update eventos_lic set ";
                   $sql.=" evento='$eventos',activo=$ch_eventos,usuario='$usuario' ";
                   $sql.=" where id_eventos=$id_eventos";
                   //echo $sql;
                   $sql_array[$y+1]=$sql;
                  } //del for

            if ((!$error) && (sizeof($sql_array)>0)){
                        sql($sql_array) or fin_pagina();
                        echo "<table align=center><tr><td><font size='4'><b>El Evento se agregó con éxito</b></font></td></tr></table>";
                        }//del if de  !$error
$db->CompleteTrans();
detalle($ID);
}//de if ($_POST["guardar_evento"]=="Guardar")


///////////////////////////////////////////////////////////////////////////
$array_forma = array("Seleccione forma Pago",
                     "30 dias a partir de la recepcion definitiva",
                     "Contado contra entrega",
					 "10 días de la fecha de entrega",
					 "10 días de la recepción de los bienes",
					 "15 días de la fecha de entrega",
					 "15 días de la recepción de los bienes",
					 "30 días de la fecha de entrega",
					 "30 días de la recepción de los bienes",
					 "60 días de la fecha de entrega",
					 "60 días de la recepción de los bienes"
                    );
if ($forma_pago)
   {foreach ($array_forma as $val)
            {if ($forma_pago!=$eval) $control=1;
            }
    if ($control==1)
       {$array_forma[sizeof($array_forma)]=$forma_pago;
        $control=0;
       }
   }
////////////////////////////////////////////////////////////////

//para plazo de entrega
$array_plazo = array("Seleccione plazo Entrega",
                     "Inmediato",
					 "5 días corridos",
					 "5 días hábiles",
					 "10 días corridos",
					 "10 días hábiles",
					 "15 días corridos",
					 "15 días hábiles",
					 "30 días corridos",
					 "30 días hábiles",
					 "45 días corridos",
					 "45 días hábiles"
					);
if ($plazo_entrega)
   {foreach ($array_plazo as $val)
            {if ($plazo_entrega!=$eval) $control=1;
            }
    if ($control==1)
       {$array_plazo[sizeof($array_plazo)]=$plazo_entrega;
        $control=0;
       }
   }
////////////////////////////////////////////////////////////////
$sql = "select * from licitaciones_datos_adicionales.normas order by nombre";
$consulta_normas = sql($sql) or fin_pagina();

$sql = "select * from  licitaciones_datos_adicionales.componentes_nueva_lic order by nombre";
$consulta_componentes = sql($sql) or fin_pagina();
$sql = "select * from  licitaciones_datos_adicionales.tipo_garantia where defecto=1 order by id_tipo_garantia";
$consulta_tipo_garantia = sql($sql) or fin_pagina();

$sql = "SELECT licitacion.id_moneda,licitacion.nro_lic_codificado,licitacion.fecha_apertura,licitacion.ultimo_usuario_fecha,
        licitacion.valor_pliego,licitacion.mantenimiento_oferta,licitacion.prorroga_automatica,id_responsable_apertura,
        licitacion.forma_de_pago,licitacion.plazo_entrega,licitacion.mant_oferta_especial,licitacion.id_estado,
        licitacion.observaciones,licitacion.dir_entidad,licitacion.exp_lic_codificado,licitacion.fecha_entrega,
        licitacion.cotizar_alternativas,licitacion.comentario_garantia_bienes,licitacion.exige_muestras,licitacion.comentarios_muestras,
        licitacion.vencimiento_muestras,licitacion.comentarios_muestras,licitacion.registro_proveedores,licitacion.avisar_antes,
        licitacion.monto_ofertado,licitacion.monto_estimado,licitacion.monto_ganado,
        case when licitacion.viene_de_presupuesto is null then 0 else 1 end as viene_de_presupuesto,
        normas.id_normas,componentes_nueva_lic.id_componentes_nueva_lic,
        impugnacion.*,garantia_contrato.*,garantia_de_oferta.*,entidad.id_distrito,entidad.id_entidad,
        tipo_garantia.nombre as nombre_tipo_ga_oferta,tipo_garantia.defecto as defecto_ga_oferta,
        tipo_ga_contrato.nombre as nombre_tipo_ga_contrato,tipo_ga_contrato.defecto as defecto_ga_contrato
        FROM licitacion left join normas using(id_normas)
        left join componentes_nueva_lic using(id_componentes_nueva_lic)
        left join impugnacion using(id_impugnacion)
        left join garantia_contrato using(id_garantia_contrato)
        left join garantia_de_oferta using(id_garantia_de_oferta)
        left join entidad using(id_entidad)
        left join tipo_garantia on id_tipo_garantia_ga_oferta=tipo_garantia.id_tipo_garantia
        left join tipo_garantia as tipo_ga_contrato on id_tipo_garantia_ga_contrato=tipo_ga_contrato.id_tipo_garantia
        WHERE id_licitacion=$ID";
	$result = sql($sql) or die;
	//print_r($result->fields);
     $viene_de_presupuesto=$result->fields["viene_de_presupuesto"];

	/****************************************************
	cargamos los datos en las variables correspondientes
	*****************************************************/
	if(!$distrito)
	 $distrito=$result->fields["id_distrito"];
	if(!$entidad)
	 $entidad=$result->fields["id_entidad"];
	if(!$direccion)
	 $direccion=$result->fields["dir_entidad"];
	if(!$mod_estado)
	 $mod_estado=$result->fields["id_estado"];
	if(!$dias_mantenimiento)
	 $dias_mantenimiento=$result->fields["mantenimiento_oferta"];
	if(!$dias_mantenimiento_como)
	 $dias_mantenimiento_como=$result->fields["mant_oferta_especial"];
    if(!$renovacion_automatica)
     $renovacion_automatica=$result->fields["prorroga_automatica"];
    if(!$forma_pago)
     $forma_pago=$result->fields["forma_de_pago"];
    if(!$plazo_entrega)
     $plazo_entrega=$result->fields["plazo_entrega"];
    if(!$proceso_numero)
     $proceso_numero=$result->fields["nro_lic_codificado"];
    if(!$expediente)
     $expediente=$result->fields["exp_lic_codificado"];
    if(!$valor_pliego)
     $valor_pliego=$result->fields["valor_pliego"];
    if(!$moneda)
     $moneda=$result->fields["id_moneda"];
    if(!$fecha_apertura)
     $fecha_apertura=fecha($result->fields["fecha_apertura"]);
    $hs=hora($result->fields["fecha_apertura"]);
    $hs=split(":",$hs);
    if(!$hora)
     $hora=$hs[0];
    if(!$minutos)
     $minutos=$hs[1];
    if(!$fecha_entrega)
     $fecha_entrega=fecha($result->fields["fecha_entrega"]);

    if(!$monto_moneda_oferta)
     $monto_moneda_oferta=$result->fields["id_moneda_ga_oferta"];
    if(!$monto_numero_oferta)
     $monto_numero_oferta=$result->fields["monto_ga_oferta"];
    if(!$monto_porcentaje_oferta)
     $monto_porcentaje_oferta=$result->fields["porcentaje_ga_oferta"];
    if(!$radio_garantia_oferta)
     $radio_garantia_oferta=$result->fields["id_tipo_garantia_ga_oferta"];
    if(!$otros_garantia_oferta && $result->fields["defecto_ga_oferta"]!=1)
     $otros_garantia_oferta=$result->fields["nombre_tipo_ga_oferta"];
    if(!$monto_moneda_contrato)
     $monto_moneda_contrato=$result->fields["id_moneda_ga_contrato"];
    if(!$monto_numero_contrato)
     $monto_numero_contrato=$result->fields["monto_ga_contrato"];
    if(!$monto_porcentaje_contrato)
     $monto_porcentaje_contrato=$result->fields["porcentaje_ga_contrato"];
    if(!$radio_garantia_contrato)
     $radio_garantia_contrato=$result->fields["id_tipo_garantia_ga_contrato"];
    if(!$otros_garantia_contrato && $result->fields["defecto_ga_contrato"]!=1)
     $otros_garantia_contrato=$result->fields["nombre_tipo_ga_contrato"];
    if(!$dias_vigencia)
     $dias_vigencia=$result->fields["vigencia"];
    if(!$dias_vigencia_como)
     $dias_vigencia_como=$result->fields["dias_tipo_ga_contrato"];
    if(!$dias_impugnacion)
     $dias_impugnacion=$result->fields["cant_dias"];
    if(!$dias_impugnacion_como)
     $dias_impugnacion_como=$result->fields["dias_tipo_imp"];
    if(!$plazo_impugnacion)
     $plazo_impugnacion=$result->fields["plazo"];
    if(!$porcentaje_impugnacion)
     $porcentaje_impugnacion=$result->fields["porcentaje_imp"];
    if(!$garantia_impugnacion)
     $garantia_impugnacion=$result->fields["porcentaje_texto"];
    if(!$moneda_presupuesto_oficial)
     $moneda_presupuesto_oficial=$result->fields["id_moneda_imp"];
    if(!$monto_presupuesto_oficial)
     $monto_presupuesto_oficial=$result->fields["monto_imp"];
    if(!$cotizar_alternativas)
     $cotizar_alternativas=$result->fields["cotizar_alternativas"];
    if(!$alternativas_oferta)
     $alternativas_oferta=$result->fields["id_normas"];
    if(!$componentes_tipo)
     $componentes_tipo=$result->fields["id_componentes_nueva_lic"];
    if(!$garantia_bienes)
     $garantia_bienes=$result->fields["comentario_garantia_bienes"];
    if(!$exige_muestras)
     $exige_muestras=$result->fields["exige_muestras"];
    if(!$vencimiento_presentacion)
     $vencimiento_presentacion=fecha($result->fields["vencimiento_muestras"]);
    if(!$vencimiento_presentacion_comentario)
     $vencimiento_presentacion_comentario=$result->fields["comentarios_muestras"];
    if(!$hay_inscripcion)
     $hay_inscripcion=$result->fields["registro_proveedores"];
    if(!$avisar_antes)
     $avisar_antes=$result->fields["avisar_antes"];
    if(!$observaciones)
     $observaciones=$result->fields["observaciones"];
    if(!$id_garantia_contrato)
      $id_garantia_contrato=$result->fields["id_garantia_contrato"];
    if(!$id_garantia_de_oferta)
      $id_garantia_de_oferta=$result->fields["id_garantia_de_oferta"];
    if(!$id_impugnacion)
      $id_impugnacion=$result->fields["id_impugnacion"];
    if(!$ultimo_usuario_fecha)
     $ultimo_usuario_fecha=$result->fields["ultimo_usuario_fecha"];
    if(!$monto_ofertado)
     $monto_ofertado=$result->fields["monto_ofertado"];
    if(!$monto_estimado)
     $monto_estimado=$result->fields["monto_estimado"];
    if(!$monto_ganado)
     $monto_ganado=$result->fields["monto_ganado"];
    if(!$exige_reaseguro_oferta)
      {if ($result->fields["reaseguro_ga_oferta"]!="") $exige_reaseguro_oferta=$result->fields["reaseguro_ga_oferta"];
       else $exige_reaseguro_oferta="-1";
      }
    if(!$exige_reaseguro_contrato)
      {if ($result->fields["reaseguro_ga_contrato"]!="") $exige_reaseguro_contrato=$result->fields["reaseguro_ga_contrato"];
       else $exige_reaseguro_contrato="-1";
      }

    $id_responsable_apertura=$result->fields['id_responsable_apertura'];
    //echo "diego".$result->fields["reaseguro_ga_contrato"];die();
   /******************************************
   fin de carga de datos
   *******************************************/
  ?>
<SCRIPT language='JavaScript' src='funcion.js'></SCRIPT>
<script>
function habilitar_garantia_oferta()
 {if (document.all.otros_garantia_oferta.value!="")
     {var control=0;
      while (control<=4)
            {chek=eval("document.all.radio_garantia_oferta["+control+"]");
             chek.checked=0;
             control++;
            }
     }
 }

 function deshabilitar_otros_oferta()
 {document.all.otros_garantia_oferta.value="";
 }



 function habilitar_garantia_contrato()
 {if (document.all.otros_garantia_contrato.value!="")
     {var control=0;
      while (control<=4)
            {chek=eval("document.all.radio_garantia_contrato["+control+"]");
             chek.checked=0;
             control++;
            }
     }
 }
 function deshabilitar_otros_contrato()
 {document.all.otros_garantia_contrato.value="";
 }


 function controles()
 {
 var texto, retorno=true, control=0, chequeado=0;
 control=0;


 if (document.all.viene_de_presupuesto.value!=1) {
  texto="Los siguientes campos son Obligatorios:\n"
  if (document.all.distrito.value=="")
     {texto=texto+"Distrito\n";
      retorno=false;
     }
  if (document.all.entidad.value=="-1")
     {texto=texto+"Entidad\n";
      retorno=false;
     }
  if (document.all.direccion.value=="")
     {texto=texto+"Dirección\n";
      retorno=false;
     }

  if (document.all.dias_mantenimiento.value=="" && document.all.no_controlar_mant_of.value==0)
     {texto=texto+"Mantenimiento de Oferta\n";
      retorno=false;
     }

     else if(document.all.no_controlar_mant_of.value==0 && (document.all.dias_mantenimiento_como.value!="Hábiles" && document.all.dias_mantenimiento_como.value!="Corridos"))
     {alert('En el Mantenimiento de Oferta debe especificar solo días "Hábiles" o días "Corridos"');
      return false;
     }



  if (document.all.forma_pago.value=="Seleccione forma Pago")
     {texto=texto+"Forma de Pago\n";
      retorno=false;
     }

  if (document.all.plazo_entrega.value=="Seleccione plazo Entrega")
     {texto=texto+"Plazo de Entrega\n";
      retorno=false;
     }

  if (document.all.proceso_numero.value=="")

     {texto=texto+"Proceso Número\n";
      retorno=false;
     }

  if (document.all.valor_pliego.value=="")
     {texto=texto+"Valor del Pliego\n";
      retorno=false;
     }

  if (document.all.fecha_apertura.value=="")
     {texto=texto+"Apertura\n";
      retorno=false;
     }

  if (document.all.hora.value=="-1" || document.all.hora.value=="-1")
     {texto=texto+"Hora\n";
      retorno=false;
     }

  while (control<=4)
        {chek=eval("document.all.alternativas_oferta["+control+"]");
         if (chek.checked) {chequeado=1; control=5;}
         control++;
        }

  if (!chequeado && document.all.no_controlar_viejos.value==0)
     {texto=texto+"Normas de la Oferta\n";
      retorno=false;
     }

  chequeado=0;
  control=0;

  while (control<=2)
        {chek=eval("document.all.componentes_tipo["+control+"]");
         if (chek.checked) {chequeado=1; control=2;}
         control++;
        }

  if (!chequeado && document.all.no_controlar_viejos.value==0)
     {texto=texto+"Componentes de la Oferta\n";
      retorno=false;
     }

  if (document.all.garantia_bienes.value=="" && document.all.no_controlar_viejos.value==0)
     {texto=texto+"Comentarios en Garantía de los Bienes\n";
      retorno=false;
     }

}

  if (!retorno)
     {
      alert (texto);
      return retorno;
     }
 }
</script>





<form action='licitaciones_view.php' method=post>
<input type="hidden" name="det_edit" value="">
<input type="hidden" name="ID" value="<?=$ID?>">
<input type="hidden" name="viene_de_presupuesto"  value="<?=$viene_de_presupuesto?>">
<?
//si la fecha de apertura es menor a 2004-11-01 ,
//entonces los controles nuevos
//no se deben hacer para la licitacion
$f_ap=fecha_db($fecha_apertura);
if($f_ap<"2004-11-01")
 $no_controle_viejos=1;
else
 $no_controle_viejos=0;
?>

<input type="hidden" name="no_controlar_viejos" value="<?=$no_controle_viejos?>">
<input type="hidden" name="no_controlar_mant_of" value="<?=$no_controle_viejos?>">
<input type="hidden" name="id_garantia_contrato" value="<?=$id_garantia_contrato?>">
<input type="hidden" name="id_garantia_de_oferta" value="<?=$id_garantia_de_oferta?>">
<input type="hidden" name="id_impugnacion" value="<?=$id_impugnacion?>">
<br>
<br>
 <input name="dir_cambio" value="0" type="hidden">
 <table width=95% class="bordes" border="1" cellspacing="1" cellpadding="2" bgcolor="<?=$bgcolor_out?>" align="center" >
  <tr id=mo>
   <td align="center" colspan="2"><b><font size="3">Modificación de datos de la Licitación Nº <?=$ID?></font></b></td>
  </tr>
  <tr>
   <td align="left"><b><font color="Red">No ingresar datos con comillas dobles ("")</font></b></td>
   <td align="right">
    <input name="cancelar" type="button" value="Volver" onClick="document.location='<?=encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$ID))?>'; return false;">
   </td>
  </tr>
  <tr>
   <td align="left" valign="bottom" colspan="2">
    <b>Los campos marcados con <font size="4" color="red">*</font> son <font zise="3" color="red">OBLIGATORIOS</font></b>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table  width="100%">
     <tr>
      <td ><b><font size="3" color="red">*</font>&nbsp;Distrito:</b></td>
      <td colspan="2">
       <select name="distrito" onchange='document.all.dir_cambio.value=1;document.all.det_edit.value=1;document.forms[0].submit()'>
        <?if (!$distrito) {?><option value=""></option><?}
          $result = "select id_distrito,nombre from distrito order by nombre";
          $resultado_consulta=sql($result) or fin_pagina();
	     while (!$resultado_consulta->EOF)
	           {if ($resultado_consulta->fields['id_distrito']==$distrito) $selected="selected";
		        ?>
		         <option <?=$selected?> value="<?=$resultado_consulta->fields['id_distrito']?>"><?=$resultado_consulta->fields['nombre']?></option>
		        <?
		        $resultado_consulta->MoveNext();
		        $selected="";
	           }
        ?>
       </select>
      </td>
     </tr>
     <tr>
      <td ><b><font size="3" color="red">*</font>&nbsp;Entidad:</b></td>
      <td colspan="2">
       <select name="entidad" onchange='document.all.dir_cambio.value=1;document.all.det_edit.value=1;document.forms[0].submit()'>
        <?if (!$distrito) {?><option selected value="-1">Seleccione Distrito</option>
        <?} else
             {$ent_datos = "select id_entidad, nombre, direccion from entidad where id_distrito=$distrito order by nombre";
              $resultado_consulta=sql($ent_datos) or fin_pagina();
	          while (!$resultado_consulta->EOF)
	           {if ($resultado_consulta->fields['id_entidad']==$entidad) {$selected="selected";/*$direccion=$resultado_consulta->fields['direccion'];*/}
		        ?>
		         <option <?=$selected?> value="<?=$resultado_consulta->fields['id_entidad']?>"><?=$resultado_consulta->fields['nombre']?></option>
		        <?
		        $resultado_consulta->MoveNext();
		        $selected="";
	           }
             }
        ?>
       </select>
      </td>
     </tr>
     <tr>
      <td ><b><font size="3" color="red">*</font>&nbsp;Dirección:</b></td>
      <td><input name="direccion" type="text" size="70" value="<?=$direccion?>" huighiugh></td>
      <td align="right"><input name="guardar_por_defecto" type="checkbox" <?if ($guardar_por_defecto) echo "checked"?> value=1>&nbsp;&nbsp;<b>Guardar por Defecto</b> </td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%" >
     <tr>
      <td width="30%"><b><font size="3" color="red">*</font>&nbsp;Mantenimiento de Oferta:</b></td>
      <td width="30%">
       <input name="dias_mantenimiento" type="text" size="10" value="<?=$dias_mantenimiento?>" onkeypress="return filtrar_teclas(event,'1234567890');">&nbsp;&nbsp;&nbsp;
       <select name="dias_mantenimiento_como" onchange="if(this.value=='Hábiles' || this.value=='Corridos')document.all.no_controlar_mant_of.value=0; else document.all.no_controlar_mant_of.value=1;">
        <option <?if ($dias_mantenimiento_como=="Hábiles") echo "selected"?> value="Hábiles">Hábiles</option>
        <option <?if ($dias_mantenimiento_como=="Corridos") echo "selected"?> value="Corridos">Corridos</option>
        <?
        if($dias_mantenimiento_como!="Corridos" && $dias_mantenimiento_como!="Hábiles")
        {?> <option selected value="<?=$dias_mantenimiento_como?>"><?=$dias_mantenimiento_como?></option>
        <?
        }
        ?>
       </select>
      </td>
      <td align="left">
       &nbsp;&nbsp;<input name="renovacion_automatica" <?if ($renovacion_automatica) echo "checked"?> type="checkbox" value="1">&nbsp;<b>Renovación Automática</b>
      </td>
     </tr>
     <tr>
      <td><b><font size="3" color="red">*</font>&nbsp;Forma de Pago:</b></td>
      <td align="left" colspan="2">
       <select name='forma_pago' OnChange='beginEditing(this);'>
	   <?$fp_sel=0;
	     foreach ($array_forma as $val)
		         {
				  ?>
				  <option <?if ($val==$forma_pago) {echo "selected";$fp_sel=1;}?> value="<?=$val?>"><?=$val?></option>
				  <?
				 }
		 if($fp_sel==0)
		 {?>
		  <option selected value="<?=$forma_pago?>"><?=$forma_pago?></option>
         <?
         }
	   ?>
		<option id=editable>Edite aquí</option>
	   </select>
      </td>
     </tr>
     <tr>
      <td><b><font size="3" color="red">*</font>&nbsp;Plazo de Entrega:</b></td>
      <td colspan="2">
       <select name="plazo_entrega" OnChange='beginEditing(this);'>
        <?
          $pe_sel=0;
          foreach ($array_plazo as $val)
		         {
				  ?>
				  <option <?if ($val==$plazo_entrega) {echo "selected";$pe_sel=0;}?> value="<?=$val?>"><?=$val?></option>
				  <?
				 }
		 if($pe_sel==0)
		 {?>
		  <option selected value="<?=$plazo_entrega?>"><?=$plazo_entrega?></option>
		 <?
		 }
	    ?>
        <option id=editable>Edite aquí</option>
       </select>
      </td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%" >
     <tr>
      <td ><b><font size="3" color="red">*</font>&nbsp;Proceso Número:</b></td>
      <td><input name="proceso_numero" type="text" size="15" value="<?=$proceso_numero?>"></td>
      <td ><b>Expediente:</b></td>
      <td><input name="expediente" type="text" size="15" value="<?=$expediente?>"></td>
      <td ><font size="3" color="red">*</font>&nbsp;<b>Valor del Pliego:</b></td>
      <td><input name="valor_pliego" type="text" size="15" value="<?=$valor_pliego?>"></td>
     </tr>
     <tr>
      <td><b><font size="3" color="red">*</font>&nbsp;Apertura:</b></td>
      <td ><input name="fecha_apertura" type="text" id="fecha_apertura" value="<?=$fecha_apertura?>"size="10" readonly>&nbsp;<?=link_calendario("fecha_apertura");?></td>
      <td><b><font size="3" color="red">*</font>&nbsp;Hora:</b></td>
      <td colspan="2">
       <select name="hora">
        <option value="-1"></option>
        <option <?if ($hora=="07") echo "selected"?> value="07">07</option>
        <option <?if ($hora=="08") echo "selected"?> value="08">08</option>
        <option <?if ($hora=="09") echo "selected"?> value="09">09</option>
        <option <?if ($hora=="10") echo "selected"?> value="10">10</option>
        <option <?if ($hora=="11") echo "selected"?> value="11">11</option>
        <option <?if ($hora=="12") echo "selected"?> value="12">12</option>
        <option <?if ($hora=="13") echo "selected"?> value="13">13</option>
        <option <?if ($hora=="14") echo "selected"?> value="14">14</option>
        <option <?if ($hora=="15") echo "selected"?> value="15">15</option>
        <option <?if ($hora=="16") echo "selected"?> value="16">16</option>
        <option <?if ($hora=="17") echo "selected"?> value="17">17</option>
        <option <?if ($hora=="18") echo "selected"?> value="18">18</option>
        <option <?if ($hora=="19") echo "selected"?> value="19">19</option>
        <option <?if ($hora=="20") echo "selected"?> value="20">20</option>
       </select>
       <b>:</b>
       <select name="minutos">
        <option <?if ($minutos=="00") echo "selected"?> value="00">00</option>
        <option <?if ($minutos=="05") echo "selected"?> value="05">05</option>
        <option <?if ($minutos=="10") echo "selected"?> value="10">10</option>
        <option <?if ($minutos=="15") echo "selected"?> value="15">15</option>
        <option <?if ($minutos=="20") echo "selected"?> value="20">20</option>
        <option <?if ($minutos=="25") echo "selected"?> value="25">25</option>
        <option <?if ($minutos=="30") echo "selected"?> value="30">30</option>
        <option <?if ($minutos=="35") echo "selected"?> value="35">35</option>
        <option <?if ($minutos=="40") echo "selected"?> value="40">40</option>
        <option <?if ($minutos=="45") echo "selected"?> value="45">45</option>
        <option <?if ($minutos=="50") echo "selected"?> value="50">50</option>
        <option <?if ($minutos=="55") echo "selected"?> value="55">55</option>
       </select>
      </td>
      <?
      $sql_responsable="select id_responsable_apertura,id_usuario,nombre,
                        apellido,interno,mail,movil
					    from responsables_apertura
					    join usuarios using(id_usuario)";
	  $res_responsable=sql($sql_responsable) or fin_pagina();?>
	  <td>
	  <b> Responsable Apertura:</b>
	    <select  name='responsables'  onKeypress='buscar_op(this);' onblur='borrar_buffer();' onclick='borrar_buffer();'>";
		  <option value=-1>Seleccionar ... </option>
      <?
      while (!$res_responsable->EOF) { ?>
         <option value='<?=$res_responsable->fields['id_responsable_apertura'];?>'
	      <? if ($res_responsable->fields['id_responsable_apertura']==$id_responsable_apertura) echo "selected";?>>
          <?=$res_responsable->fields['apellido'].' '.$res_responsable->fields['nombre'];?>
         </option>
            <?
        $res_responsable->MoveNext();
      } ?>
     </select>
     </tr>
     <tr>
      <td><b><font size="3" color="red">&nbsp;</font>&nbsp;Fecha de Entrega:</b></td>
      <td ><input name="fecha_entrega" type="text" id="fecha_entrega" value="<?=$fecha_entrega?>"size="10" readonly>&nbsp;<?=link_calendario("fecha_entrega");?></td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width=100% align=center>
      <tr>
      <td colspan="6"><b>Moneda:</b>
      <?$result1 = sql("select id_moneda,nombre from moneda") or fin_pagina();?>
      &nbsp;&nbsp;
       <?echo "<select name=moneda>\n";
      $result1->Move(0);
      while (!$result1->EOF)
           {echo "<option value='".$result1->fields["id_moneda"]."'";
            if ($result1->fields["id_moneda"] == $moneda) echo " selected";
		        echo ">".$result1->fields["nombre"]."\n";
		        $result1->MoveNext();
	           }
	     echo "</select>\n";
       ?>
      </td>
     </tr>
     <tr>
       <td>
       <b>Ofertado:&nbsp; </b><input name="monto_ofertado" type="text" value="<?=number_format($monto_ofertado,'2','.','')?>" onkeypress="return filtrar_teclas(event,'0123456789.');">
       </td>
       <td >
         <b>Estado:</b>
       </td>
      </tr>
      <tr>
       <td>
       <b>Estimado: </b><input name="monto_estimado" type="text" value="<?=number_format($monto_estimado,'2','.','')?>" onkeypress="return filtrar_teclas(event,'0123456789.');">
       </td>
       <td >
        <input type="hidden" name="estado_anterior" value="<?=$result3->fields["id_estado"]?>">
        <input type=radio name=radio_estado value=1 checked onclick="habilitar_estado();">
        &nbsp;&nbsp;<select name=mod_estado onchange="if (this.value!=5) validar.style.visibility='hidden'; else validar.style.visibility='visible'">
        <?
        $result3 = sql("select id_estado,nombre,color from estado order by id_estado") or die;
        while (!$result3->EOF) {
              $nombre_estado=$result3->fields["nombre"];
              if (($mod_estado==$result3->fields["id_estado"])||($nombre_estado!="Presuntamente ganada" && $nombre_estado!="Orden de compra" && $nombre_estado!="Preadjudicada"))
                 {
                 echo "<option value='".$result3->fields["id_estado"]."' ";
		         echo "style='background-color: ".$result3->fields["color"]."; ";
		         echo "color:".contraste($result3->fields["color"],"#000000","#ffffff").";'";
		         if ($mod_estado == $result3->fields["id_estado"]) echo " selected";
                 echo ">".$nombre_estado."\n";
                 }
            $result3->MoveNext();
	      }//del while
	    ?>
        </select>
      </td>
      </tr>
      <tr>
      <td>
       <b>Ganado:&nbsp;&nbsp;&nbsp; </b><input name="monto_ganado" type="text" value="<?=number_format($monto_ganado,'2','.','')?>" onkeypress="return filtrar_teclas(event,'0123456789.');">
      </td>
      <td>
       <input type=radio name=radio_estado value=2 onclick="habilitar_estado();">
         &nbsp;&nbsp;
         <?
         $link=encode_link("lic_est_esp.php",array("id"=>$ID));
         ?>
         <input type=button name=estados_especiales disabled value='Estados Especiales' onclick="window.open('<?=$link?>');">
        </td>
       </tr>
       <?if ($mod_estado!=5) $validar="style='visibility:hidden'"; ?>
       <tr <?=$validar?>>
         <td colspan=2 id=validar>
           <input type=checkbox name=validar_control value="1" <?if($validar_control)echo "checked"?>>
           <b> Ignorar control de resultados cargados</b>
         </td>
       </tr>
     </table>
   </td>
  </tr>
   <?
  //agrego los eventos de la licitacion
  $sql="select * from eventos_lic where id_licitacion=$ID order by fecha";
  $resultado=sql($sql);
  $cant_eventos=$resultado->recordcount();
  if($cant_eventos>0)
    {?>
     <tr>
      <td colspan="2">
       <table width="100%" border="1">
     <?
    }

    if (($cmd=="proximas")||($cant_eventos))
    {
    ?>
    <input type=hidden name=cant_eventos value=<?=$cant_eventos?>>
    <tr id='ma'><td colspan=2>Eventos de la Licitación </td> </tr>
    <?
    if ($cmd=="proximas")
    {?>
    <tr><td colspan=2>
    <!--tabla con las referencias de los eventos-->
    <table width=100% align=right  border=0>
      <tr>
       <td width=33% >
         <table  cellspacing=0 cellpadding=0 wdith=100%>
          <tr>
           <td width=15 bgcolor='<?=$cuatro_dias?>' bordercolor='#000000' height=15>&nbsp;</td>
           <td ><b>Cuatro Días</td>
          </tr>
         </table>
        </td>
        <td width=33% >
         <table cellspacing=0 cellpadding=0 wdith=100%>
          <tr>
           <td width=15 bgcolor='<?=$dos_dias?>' bordercolor='#000000' height=15>&nbsp;</td>
           <td ><b>Dos Días</td>
          </tr>
         </table>
        </td>
        <td width=33% >
         <table  cellspacing=0 cellpadding=0 wdith=100%>
          <tr>
           <td width=15 bgcolor=<?=$vencido?> bordercolor='#000000' height=15>&nbsp;</td>
           <td ><b>Vencido</td>
          </tr>
         </table>
        </td>
      </tr>
      </table>
     </td>
    </tr>
    <?
}//de if ($cmd=="proximas")
    ?>
    <tr>
     <td colspan=2 valign=top>
      <table width='100%'  border='1' cellpadding='2' cellspacing='1'  bordercolor='#ffffff'>
       <tr>
        <td  align=center width=20%> <b> Fecha       </td>
        <td  align=center width=75%> <b>Descripción  </td>
        <td  align=center width=5%>  <b>Hecho       </td>
       </tr>
       <?
       for($i=0;$i<$cant_eventos;$i++)
       {
       $id_evento     =$resultado->fields["id_eventos"];
       $fecha=$resultado->fields["fecha"];
       $fecha_evento  =substr($fecha,0,10);
       $hora_evento   =substr($fecha,11,8);
       $usuario_evento=$resultado->fields["usuario"];
       $evento        = str_replace(chr(13).chr(10),"<n>",$resultado->fields["evento"]);
       $activo_evento = $resultado->fields["activo"];

       if ($activo_evento) $ch_evento="checked";
                      else $ch_evento="";

       $fecha_hoy=date("d/m/y");
       $dias=diferencia_dias_habiles($fecha_hoy,fecha($fecha));
       $bgcolor="$bgcolor3";
       if(($dias>2)&&($dias<=4)) $bgcolor="$cuatro_dias";
       if(($dias>0)&&($dias<=2)) $bgcolor="$dos_dias";
       if(($dias==0)&&($activo_evento==0)) $bgcolor="$vencido";
       if (($activo_evento)||($cmd!="proximas")) $bgcolor="$bgcolor3";
       ?>
       <input type=hidden name=id_evento_<?=$i?> value=<?=$id_evento?>>
       <tr bgcolor='<?=$bgcolor?>'>
        <td align=center valign=top>
         <b><?=Fecha($fecha_evento)." ".$hora_evento?><br>
         <br><?=$usuario_evento?></b>
        </td>
        <td>
         <textarea  name=evento_<?=$i?> style='width:100%;' rows=4><?=$evento?></textarea>
        </td>
        <td align=center>
         <input type=checkbox  name=ch_evento_<?=$i?> value=1 <?=$ch_evento?>>
        </td>
       </tr>
    <?
    $resultado->MoveNext();
   }//de for($i=0;$i<$cant_eventos;$i++)
   ?>
    </table>
   </td>
  </tr>
  <tr id='ma'><td colspan='2'>&nbsp;</td></tr>
  <?
  } //de if (($cmd=="proximas")||($cant_eventos))
  if ($cmd=="proximas")
  {
   $fecha=date("H:i:s d/m/Y");
   $hora_evento=substr($fecha,0,8);
   $fecha=substr($fecha,9,10);
   ?>
   <tr id='ma'>
    <td align=center valign=top colspan=2>
     <table width=100% align=Center border=0>
      <tr>
       <td width=10%>
        <table width=100% align=Center border=0>
         <tr>
          <td><b>Fecha</td>
          <td>
           <input type=text name=fecha_evento value='<?=$fecha?>' size=12>
            &nbsp;
            <?=link_calendario("fecha_evento")?>
          </td>
         </tr>
         <tr>
          <td><b>Hora</td>
          <td><input type=text name=hora_evento value='<?=$hora_evento?>' size=12></td>
         </tr>
        </table>
       </td>
       <td  align=left>
        <textarea name=evento_nuevo style='width:100%;' rows=4></textarea>
       </td>
      </tr>
     </table>
    </td>
   </tr>
   <tr id='ma'>
    <script>
    function limpiar()
    {
    document.all.evento_nuevo.value='';
    }
    </script>
    <td colspan=2 align=Center border=1>
     <table width=100% align=center border=0>
      <tr>
        <td colspan=2 align=center>
         <b>Guardar eventos</b>
        </td>
       </tr>
       <tr>
        <td width=50% align=right>
         <input type=submit name=guardar_evento value=Guardar onclick="document.all.det_edit.value=1;">
        </td>
        <td width=50% align=left>
         <input type=button name=deshacer_evento value=Deshacer onclick="limpiar()">
        </td>
       </tr>
     </table>
    </td>
   </tr>
<?
  } //del if que muestra los eventos de cmd == proximas
  if($cant_eventos>0)
  {?>
   </table>
   </td>
   </tr>
  <?
  }
  ?>
  <tr>
   <td width="50%">
    <table width="100%" >
     <tr>
      <td align="center" colspan="3"><u><b>Garantía de Oferta</b></u></td>
     </tr>
     <tr>
      <td><b>Monto:</b></td>
      <td>
       <?echo "<select name='monto_moneda_oferta'>\n";
	     $result1->Move(0);
	     while (!$result1->EOF)
	           {echo "<option value='".$result1->fields["id_moneda"]."'";
	            if ($result1->fields["id_moneda"] == $monto_moneda_oferta) echo " selected";
                echo ">".$result1->fields["nombre"]."\n";
                $result1->MoveNext();
	           }
	     echo "</select>\n";
       ?>
       <input name="monto_numero_oferta" value="<?=$monto_numero_oferta?>" type="text" size="10" onkeypress="return filtrar_teclas(event,'0123456789.');">
      </td>
      <td><input name="monto_porcentaje_oferta" value="<?=$monto_porcentaje_oferta?>" type="text" size="5" onkeypress="return filtrar_teclas(event,'0123456789.');">&nbsp;<b>%</b> </td>
     </tr>
     <?
      $primero=1;
      while (!$consulta_tipo_garantia->EOF)
            {
            ?>
             <tr><td><b><?=$consulta_tipo_garantia->fields['nombre']?></b></td><td colspan="2"><input name="radio_garantia_oferta" type="radio" value="<?=$consulta_tipo_garantia->fields['id_tipo_garantia']?>" onclick="deshabilitar_otros_oferta()" <?if ($radio_garantia_oferta==$consulta_tipo_garantia->fields['id_tipo_garantia']) echo "checked"?>>
                 <? if ($primero==1) {?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Exige Reaseguro?</b><select name="exige_reaseguro_oferta"><option value="-1" <?if ($exige_reaseguro_oferta=="-1") echo "selected"?>></option><option <?if ($exige_reaseguro_oferta=="si" || $exige_reaseguro_oferta==1) echo "selected"?> value="si">Si</option><option <?if ($exige_reaseguro_oferta=="no" || $exige_reaseguro_oferta==0) echo "selected"?> value="no">No</option></select></td></tr><?}
             $primero=2;
             $consulta_tipo_garantia->MoveNext();
            }
     ?>
    <tr><td><b>Otros</b></td><td colspan="2"><input name="otros_garantia_oferta" value="<?=$otros_garantia_oferta?>" type="text" size="40" onchange="habilitar_garantia_oferta()"></td></tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    </table>
   </td>
   <td width="50%">
    <table width="100%">
     <tr>
      <td align="center" colspan="3"><u><b>Garantía de Contrato y/o Adjudicación</b></u></td>
     </tr>
     <tr>
      <td><b>Monto:</b></td>
      <td>
       <?echo "<select name='monto_moneda_contrato'>\n";
	     $result1->Move(0);
	     while (!$result1->EOF)
	           {echo "<option value='".$result1->fields["id_moneda"]."'";
	            if ($result1->fields["id_moneda"] == $monto_moneda_contrato) echo " selected";
		        echo ">".$result1->fields["nombre"]."\n";
		        $result1->MoveNext();
	           }
	     echo "</select>\n";
        ?>
       <input name="monto_numero_contrato" value="<?=$monto_numero_contrato?>" type="text" size="10" onkeypress="return filtrar_teclas(event,'0123456789.');">
      </td>
      <td><input name="monto_porcentaje_contrato" value="<?=$monto_porcentaje_contrato?>" type="text" size="5" onkeypress="return filtrar_teclas(event,'0123456789.');">&nbsp;<b>%</b> </td>
     </tr>
     <?
      $consulta_tipo_garantia->MoveFirst();
      $primero=1;
      while (!$consulta_tipo_garantia->EOF)
            {
            ?>
             <tr><td><b><?=$consulta_tipo_garantia->fields['nombre']?></b></td><td colspan="2"><input name="radio_garantia_contrato" type="radio" value="<?=$consulta_tipo_garantia->fields['id_tipo_garantia']?>" onclick="deshabilitar_otros_contrato()" <?if ($radio_garantia_contrato==$consulta_tipo_garantia->fields['id_tipo_garantia']) echo "checked"?>>
             <? if ($primero==1) {?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Exige Reaseguro?</b><select name="exige_reaseguro_contrato"><option value="-1" <?if ($exige_reaseguro_contrato=="-1") echo "selected"?>></option><option <?if ($exige_reaseguro_contrato=="si" || $exige_reaseguro_contrato==1) echo "selected"?> value="si">Si</option><option <?if ($exige_reaseguro_contrato=="no" || $exige_reaseguro_contrato==0) echo "selected"?> value="no">No</option></select></td></tr><?}
             $primero=2;
             $consulta_tipo_garantia->MoveNext();
            }
     ?>
     <tr><td><b>Otros</b></td><td colspan="2"><input name="otros_garantia_contrato" value="<?=$otros_garantia_contrato?>" type="text" size="40" onchange="habilitar_garantia_contrato()"></td></tr>
     <tr>
      <td><b>Vigencia</b></td>
      <td colspan="2">
       <input name="dias_vigencia" value="<?=$dias_vigencia?>" type="text" size="5" onkeypress="return filtrar_teclas(event,'123456789');">
       <select name="dias_vigencia_como">
        <option <?if ($dias_vigencia_como=="habiles") echo "selected"?> value="habiles">Habiles</option>
        <option <?if ($dias_vigencia_como=="corridos") echo "selected"?> value="corridos">Corridos</option>
       </select>
      </td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%">
     <tr>
      <td colspan="3"><font size="2"><b><u>Impugnación:</u></b></font></td>
     </tr>
     <tr>
      <td><b>Plazo de Impugnación</b></td>
      <td>
       <input name="dias_impugnacion" value="<?=$dias_impugnacion?>" type="text" onkeypress="return filtrar_teclas(event,'0123456789');" size="5">
       <select name="dias_impugnacion_como">
        <option <?if ($dias_impugnacion_como=="habiles") echo "selected"?> value="habiles">Habiles</option>
        <option <?if ($dias_impugnacion_como=="corridos") echo "selected"?> value="corridos">Corridos</option>
       </select>
      </td>
      <td><input name="plazo_impugnacion" value="<?=$plazo_impugnacion?>" type="text" size="40"></td>
     </tr>
     <tr>
      <td><b>Garantía de Impugnación</b></td>
      <td>&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="porcentaje_impugnacion" value="<?=$porcentaje_impugnacion?>" type="text" onkeypress="return filtrar_teclas(event,'0123456789.');" size="5"><b>%</b></td>
      <td><input name="garantia_impugnacion" value="<?=$garantia_impugnacion?>" type="text" size="40">
     </tr>
     <tr>
      <td><b>Presupuesto Oficial</b></td>
      <td colspan="2">
       <?
        echo "<select name='moneda_presupuesto_oficial'>\n";
	    $result1->Move(0);
	    while (!$result1->EOF)
	          {echo "<option value='".$result1->fields["id_moneda"]."'";
	           if ($result1->fields["id_moneda"] == $moneda_presupuesto_oficial) echo " selected";
               echo ">".$result1->fields["nombre"]."\n";
               $result1->MoveNext();
	          }
	    echo "</select>\n";
       ?>
       <input name="monto_presupuesto_oficial" value="<?=$monto_presupuesto_oficial?>" type="text" onkeypress="return filtrar_teclas(event,'0123456789.');" size="20">
      </td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%" >
     <tr>
      <td colspan="2"><b><font size="3" color="red">*</font>&nbsp;<u>De la Oferta:</b></u></td>
     </tr>
     <tr>
      <td width="35%"><b>Se pueden Cotizar Alternativas</b></td>
      <td>
       <?
        switch ($cotizar_alternativas)
        {case -1:$sel_="selected";break;
         case 1:$sel_si="selected";break;
         case 0:$sel_no="selected";break;
        }
       ?>
       <select name="cotizar_alternativas">
        <option value=-1 <?=$sel_?>></option>
        <option value=1 <?=$sel_si?>>Si</option>
        <option value=0 <?=$sel_no?>>No</option>
       </select>
      </td>
     </tr>
     <?
      while (!$consulta_normas->EOF)
            {
            ?>
             <tr>
              <td width="35%"> <b><?=$consulta_normas->fields['nombre']?></b></td>
              <td><input name="alternativas_oferta" type="radio" value="<?=$consulta_normas->fields['id_normas']?>" <?if ($alternativas_oferta==$consulta_normas->fields['id_normas']) echo "checked"?>></td>
             </tr>
            <?
             $consulta_normas->MoveNext();
            }
     ?>
     <tr>
      <td colspan="2">&nbsp;</td>
     </tr>
     <?
      while (!$consulta_componentes->EOF)
            {
            ?>
             <tr>
              <td width="35%"><b><font size="3" color="red">*</font>&nbsp;<?=$consulta_componentes->fields['nombre']?></b></td>
              <td><input name="componentes_tipo" type="radio" value="<?=$consulta_componentes->fields['id_componentes_nueva_lic']?>" <?if ($componentes_tipo==$consulta_componentes->fields['id_componentes_nueva_lic']) echo "checked"?>></td>
             </tr >
            <?
             $consulta_componentes->MoveNext();
            }
     ?>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%">
     <tr>
      <td><b><font size="3" color="red">*</font>&nbsp;<u>Garantía de los Bienes:</b></u></td>
     </tr>
     <tr>
      <td><textarea name="garantia_bienes"  cols="110" rows="3"><?=$garantia_bienes?></textarea></td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%" >
     <tr>
      <td width="50%" colspan="2"><b>Exige Muestras</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     <?
      switch ($exige_muestras)
        {case -1:$esel_="selected";break;
         case 1:$esel_si="selected";break;
         case 0:$esel_no="selected";break;
        }
       ?>
       <select name="exige_muestras">
        <option value=-1 <?=$esel_?>></option>
        <option value=1 <?=$esel_si?>>Si</option>
        <option value=0 <?=$esel_no?>>No</option>
       </select>
     </tr>
     <tr>
      <td width="50%"><b>Vencimiento de la Presentacion de las muestras</b></td>
      <td><input name="vencimiento_presentacion" type="text" id="vencimiento_presentacion" value="<?=$vencimiento_presentacion?>"size="10" readonly>&nbsp;<?=link_calendario("vencimiento_presentacion");?></td>
     </tr>
     <tr>
      <td colspan="2"><textarea name="vencimiento_presentacion_comentario" cols="110" rows="3"><?=$vencimiento_presentacion_comentario?></textarea></td>
    </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%">
     <tr>
      <td colspan="2"><u><b>Para los Distritos</b></u></td>
     </tr>
     <tr>
      <td width="68%"><b>Tenemos la Inscripción en el Registro de Proveedores ?</b></td>
      <td>
       <select name="hay_inscripcion">
        <option value="-1"></option>
        <option <?if ($hay_inscripcion=="si") echo "selected"?> value="si">Si</option>
        <option <?if ($hay_inscripcion=="ge") echo "selected"?> value="ge">Hay que Gestionar</option>
       </select>
      </td>
     </tr>
     <tr>
      <td width="68%"><b>Avisar 3 dias habiles anteriores a la apertura porque debe salir antes</b></td>
      <td><input name="avisar_antes" type="checkbox" <?if ($avisar_antes) echo "checked"?>></td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td colspan="2">
    <table width="100%">
     <tr>
      <td><u><b>Observaciones:</b></u></td>
     </tr>
     <tr>
      <td><textarea name="observaciones" cols="110" rows="7"><?=$observaciones?></textarea></td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td align=left colspan=2>
    <b>Archivos:</b><br>
	<table cellpadding=3 cellspacing=3 width=100%>
	 <tr>
	  <td colspan=5 align=left></td>
	 </tr>
	<?
    $result5 = sql("select * from archivos where id_licitacion=$ID order by subidofecha asc") or die;
	if ($result5->RecordCount() > 0 )
    {?>
	 <tr bgcolor=<?=$bgcolor3?>>
	  <td width=10% align=center><b>Imprimir</b></td>
	  <td width=45% align=left><b>Nombre</b></td>
	  <td width=20% align=center><b>Fecha de cargado</b></td>
	  <td width=25% align=left><b>Cargado por</b></td>
     </tr>
     <?
	while (!$result5->EOF) {
			$mc = substr($result5->fields["subidofecha"],5,2);
			$dc = substr($result5->fields["subidofecha"],8,2);
			$yc = substr($result5->fields["subidofecha"],0,4);
			$hc = substr($result5->fields["subidofecha"],11,5);
			$imprimir = $result5->fields["imprimir"];
			if ($imprimir == "t") $color_imprimir = "#00cc00";
			else $color_imprimir = "#cc2222";
			?>
		  <tr bgcolor=<?=$bgcolor3?>>
		   <td align=center bgcolor='<?=$color_imprimir?>'>
			<select name="file_id[<?=$result5->fields["idarchivo"]?>]">
			 <option value='t'
			  <?
			  if ($imprimir == "t") echo " selected";
			  ?>
			 >Sí
			 <option value='f'
			  <?
			  if ($imprimir == "f") echo " selected";
			  ?>
			 >No
		    </select>
		   </td>
		   <td align=left>
		    <a title='Archivo: <?=$result5->fields["nombrecomp"]?>Tamaño: <?=number_format($result5->fields["tamañocomp"]/1024)?> Kb' href='<?=encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result5->fields["idarchivo"],"cmd1"=>"download","Comp"=>1))?>'>
			 <img align=middle src=<?="$html_root/imagenes/zip.gif"?> border=0>
			</a>&nbsp;&nbsp;
			<a title='Archivo: <?=$result5->fields["nombre"]?>Tamaño: <?=number_format($result5->fields["tamaño"]/1024)?> Kb' href='<?=encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result5->fields["idarchivo"],"cmd1"=>"download"))?>'><?=$result5->fields["nombre"]?></a>
		   </td>
		   <td align=center><?="$dc/$mc/$yc $hc"?> hs.</td>
		   <td align=left><?=$result5->fields["subidousuario"]?></td>
		  </tr>
		<?
		  $result5->MoveNext();
		}
	}
	else {
		echo "<tr><td colspan=5 align=center><b>No hay archivos disponibles para esta licitación</b></td></tr>\n";
	}
  ?>
   </table>
   </td>
  </tr>
<!--  <tr>

   <td align="center"><input name="guardar" type="submit" value="Guardar" onclick="document.all.det_edit.value=1;return controles()"> </td>

   <td align="center"><input name="cancelar" type="button" value="Volver" onClick="document.location='<?=encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$ID))?>'; return false;"> </td>

  </tr> -->
 </table>
 <SCRIPT language='JavaScript' src='../../lib/genMove.js'></SCRIPT>
<?
$dentro="<table width=100% align=center border=0>
<tr>
   <td align='center'><input name='guardar' type='submit' value='Guardar' onclick='document.all.det_edit.value=1;return controles()'> </td>
</tr><tr>
	<td align='center'><input name='cancelar' type='button' value='Volver' onClick='document.location=\"".encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$ID))."\"; return false;'> </td>
</tr>
</table>";
inicio_barra("botonera","&nbsp;&nbsp;&nbsp;Guardar",$dentro,80,100,1,1);
?>
</form>
</body>
</html><!--onclick="return controles()"-->
<?echo fin_pagina();?>