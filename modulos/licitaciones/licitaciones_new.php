<?
/*
Author: Broggi
Fecha: 28/07/2004

MODIFICADA POR
$Author: mari $
$Revision: 1.39 $
$Date: 2007/01/05 19:57:05 $
*/
require_once("../../config.php");

echo $html_header;
//print_r($_POST);


cargar_calendario();
extract($_POST);
echo "<br>";


///////////////GUARDO EN LA BASE///////////////////////////////////////////
if ($guardar=="Guardar")
{$observaciones=str_replace("\""," ",$observaciones);
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
 else  $id_responsable_apertura="NULL";
 //echo $observaciones;
 
 $db->StartTrans();
   $sql = "select nextval('licitacion_id_licitacion_seq') as id_licitacion";
   $id_rec = sql($sql) or fin_pagina();
   $id_licitacion=$id_rec->fields['id_licitacion'];
   //echo $monto_numero_oferta.", ".$radio_garantia_oferta.", ".$otros_garantia_oferta;
   if ($radio_garantia_oferta || $otros_garantia_oferta!="" || $monto_porcentaje_oferta!="")
      {$id_tipo_garantia="NULL";
       $sql = "select nextval('garantia_de_oferta_id_garantia_de_oferta_seq') as id_garantia_de_oferta";
       $id_rec = $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
       $id_garantia_de_oferta=$id_rec->fields['id_garantia_de_oferta'];
       if ($otros_garantia_oferta!="")
          {$sql = "select nextval('tipo_garantia_id_tipo_garantia_seq') as id_tipo_garantia";
           $id_rec = sql($sql) or fin_pagina();
           $id_tipo_garantia=$id_rec->fields['id_tipo_garantia'];
           $sql = "insert into licitaciones_datos_adicionales.tipo_garantia 
                   (id_tipo_garantia,nombre,defecto) values ($id_tipo_garantia,'$otros_garantia_oferta',0)";
           $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
          }
       if ($radio_garantia_oferta) $id_tipo_garantia=$radio_garantia_oferta; else $id_tipo_garantia="NULL";
       if ($radio_garantia_oferta!=1 || $otros_garantia_oferta!="") $exige_reaseguro_oferta=-1;          
       if ($exige_reaseguro_oferta=="si") $exige_reaseguro_oferta=1;
       elseif ($exige_reaseguro_oferta=="no") $exige_reaseguro_oferta=0; else $exige_reaseguro_oferta="NULL";        
       if ($monto_porcentaje_oferta=="") $monto_porcentaje_oferta="NULL";
       if ($monto_numero_oferta=="") $monto_numero_oferta="NULL";
       
       $sql = "insert into licitaciones_datos_adicionales.garantia_de_oferta
               (id_garantia_de_oferta,id_tipo_garantia_ga_oferta,id_moneda_ga_oferta,monto_ga_oferta,porcentaje_ga_oferta,reaseguro_ga_oferta)
               values ($id_garantia_de_oferta,$id_tipo_garantia,$monto_moneda_oferta,$monto_numero_oferta,$monto_porcentaje_oferta,$exige_reaseguro_oferta)";       
       $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
      }
    if ($radio_garantia_contrato || $otros_garantia_contrato!="" || $monto_porcentaje_contrato!="")
       {$id_tipo_garantia="NULL";
       	$sql = "select nextval('garantia_contrato_id_garantia_contrato_seq') as id_garantia_contrato";
        $id_rec = $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
        $id_garantia_contrato=$id_rec->fields['id_garantia_contrato'];
        if ($otros_garantia_contrato!="")
           {$sql = "select nextval('tipo_garantia_id_tipo_garantia_seq') as id_tipo_garantia";
            $id_rec = $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
            $id_tipo_garantia=$id_rec->fields['id_tipo_garantia'];
            $sql = "insert into licitaciones_datos_adicionales.tipo_garantia 
                    (id_tipo_garantia,nombre,defecto) values ($id_tipo_garantia,'$otros_garantia_contrato',0)";
            $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
           }
        if ($radio_garantia_contrato) $id_tipo_garantia=$radio_garantia_contrato; else $id_tipo_garantia="NULL";
        if ($radio_garantia_oferta!=1 || $otros_garantia_oferta!="") $exige_reaseguro_oferta=-1;
        if ($exige_reaseguro_contrato=="si") $exige_reaseguro_contrato=1; 
        elseif ($exige_reaseguro_contrato=="no") $exige_reaseguro_contrato=0; else $exige_reaseguro_contrato="NULL";
        if ($monto_porcentaje_contrato=="") $monto_porcentaje_contrato="NULL";
        if ($monto_numero_contrato=="") $monto_numero_contrato="NULL";
        if ($dias_vigencia=="") $dias_vigencia="NULL";
        $sql = "insert into licitaciones_datos_adicionales.garantia_contrato
                (id_garantia_contrato,id_tipo_garantia_ga_contrato,id_moneda_ga_contrato,monto_ga_contrato,porcentaje_ga_contrato,reaseguro_ga_contrato,vigencia,dias_tipo_ga_contrato)
                values ($id_garantia_contrato,$id_tipo_garantia,$monto_moneda_contrato,$monto_numero_contrato,$monto_porcentaje_contrato,$exige_reaseguro_contrato,$dias_vigencia,'$dias_vigencia_como')";
        $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
      }

    if ($dias_impugnacion!="" || $plazo_impugnacion!="" || $porcentaje_impugnacion!="" || $garantia_impugnacion!="" || $monto_presupuesto_oficial!="")
       {$sql = "select nextval('impugnacion_id_impugnacion_seq') as id_impugnacion";
        $id_rec = $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
        $id_impugnacion = $id_rec->fields['id_impugnacion'];
        if ($dias_impugnacion=="") $dias_impugnacion=-1;
        if ($porcentaje_impugnacion=="") $porcentaje_impugnacion=-1;
        if ($monto_presupuesto_oficial=="") $monto_presupuesto_oficial=-1;
        $sql = "insert into impugnacion (id_impugnacion,cant_dias,dias_tipo_imp,plazo,porcentaje_imp,monto_imp,id_moneda_imp,porcentaje_texto)
                values ($id_impugnacion,$dias_impugnacion,'$dias_impugnacion_como','$plazo_impugnacion',$porcentaje_impugnacion,$monto_presupuesto_oficial,$moneda_presupuesto_oficial,'$garantia_impugnacion')";
        $id_rec = $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
       }
   $fecha_modif = date("Y-m-d H:i:s",mktime());    
   if ($renovacion_automatica) $renovacion_automatica=1; else $renovacion_automatica=0;    
   if ($cotizar_alternativas=="Si") $cotizar_alternativas=1; elseif  ($cotizar_alternativas=="No") $cotizar_alternativas=0; else $cotizar_alternativas=-1;
   if ($exige_muestras=="Si") $exige_muestras=1; elseif ($exige_muestras=="No") $exige_muestras=0; else $exige_muestras="NULL";
   if ($avisar_antes=="on") $avisar_antes=1; else $avisar_antes=0;   
   $fecha_de_apertura=fecha_db($fecha_apertura);
   $fecha_de_apertura.=" ".$hora.":".$minutos.":00"; 
   $values = "($id_licitacion,$entidad,$moneda,$componentes_tipo,$alternativas_oferta";
   $sql = "insert into licitacion (id_licitacion,id_entidad,id_moneda,id_componentes_nueva_lic,id_normas";
   if ($id_garantia_de_oferta!="") {$sql.=",id_garantia_de_oferta"; $values.=",$id_garantia_de_oferta";}
   if ($id_garantia_contrato!="") {$sql.=",id_garantia_contrato"; $values.=",$id_garantia_contrato";}
   if ($id_impugnacion!="") {$sql.=",id_impugnacion"; $values.=",$id_impugnacion";}
   if ($vencimiento_presentacion!="") {$sql.=",vencimiento_muestras"; $vencimiento_de_presentacion=fecha_db($vencimiento_presentacion)." 00:00:00"; $values.=",'$vencimiento_de_presentacion'";}
   $sql .= ",id_estado,nro_lic_codificado,fecha_apertura,valor_pliego,mantenimiento_oferta,prorroga_automatica
            ,forma_de_pago,plazo_entrega,mant_oferta_especial,observaciones,dir_entidad,exp_lic_codificado
            ,cotizar_alternativas,comentario_garantia_bienes,exige_muestras,comentarios_muestras
            ,registro_proveedores,avisar_antes,ultimo_usuario,ultimo_usuario_fecha,id_responsable_apertura)";
   $values .=",0,'$proceso_numero','$fecha_de_apertura',$valor_pliego,$dias_mantenimiento,'$renovacion_automatica'
              ,'$forma_pago','$plazo_entrega','$dias_mantenimiento_como','$observaciones','$direccion','$expediente'
              ,$cotizar_alternativas,'$garantia_bienes',$exige_muestras,'$vencimiento_de_presentacion'
              ,'$hay_inscripcion',$avisar_antes,'".$_ses_user['name']."','$fecha_modif',$id_responsable_apertura)";
   $sql .=" values ".$values;
   $id_rec = $db->Execute($sql) or die($db->ErrorMsg("Se produjo un error en la consulta").$sql);
   
   //agregamos una entrada para esta licitacion en la tabla entregar_lic
   $query="insert into entregar_lic(id_licitacion,orden_subida,vence,mostrar,oferta_subida)values($id_licitacion,0,NULL,1,0)";
   $db->Execute($query) or die($db->ErrorMsg().$query);
   //agregamos una entrada para esta licitacion en la tabla candado
   $query="insert into candado(id_licitacion,estado)values($id_licitacion,0)";
   $db->Execute($query) or die($db->ErrorMsg().$query); 
   echo "<table align=center><tr><td><font size='4'><b>Se inserto correctamente la licitación número: $id_licitacion</b></font></td></tr></table>";
   ////////////////////////////////////////////////////////////////////////////////////////
   if ($control_fecha==1)
      {$mensaje="La Licitación Nº $id_licitacion, ha sido cargada con un día de apetura que no corresponde a un dia hábil,\n la fecha ingresada es $fecha_apertura, el usuario que cargo la Licitacion es ".$_ses_user['name'];
        enviar_mail("licitaciones@coradir.com.ar","Autorizacion Adelanto",$mensaje,' ',' ',' ',0);
      } 
   //////////////////////////////////////////////////////////////////////////////////////////
   $distrito="";
   $entidad="";
   $direccion="";
   $guardar_por_defecto="";
   $dias_mantenimiento="";
   $dias_mantenimiento_como="";
   $renovacion_automatica="";
   $forma_pago="";
   $plazo_entrega="";
   $proceso_numero="";
   $expediente="";
   $valor_pliego="";
   $moneda="";
   $fecha_apertura="";
   $hora="";
   $minutos="";
   $cotizar_alternativas="";
   $alternativas_oferta="";
   $componentes_tipo="";
   $garantia_bienes="";
   $exige_muestras="";
   $vencimiento_presentacion="";
   $vencimiento_presentacion_comentarios="";
   $hay_inscripcion="";
   $avisar_antes="";
   $dias_impugnacion="";
   $dias_impugnacion_como="";
   $plazo_impugnacion="";
   $porcentaje_impugnacion="";
   $garantia_impugnacion="";
   $moneda_presupuesto_oficial="";
   $monto_presupuesto_oficial="";
   $monto_moneda_oferta="";
   $monto_numero_oferta="";
   $monto_porcentaje_oferta="";
   $exige_reaseguro_oferta="";
   $radio_garantia_oferta="";
   $otros_garantia_oferta="";
   $monto_moneda_contrato="";
   $monto_numero_contrato="";
   $monto_porcentaje_contrato="";
   $exige_reaseguro_contrato="";
   $dias_vigencia="";
   $dias_vigencia_como="";
   $radio_garantia_contrato="";
   $otros_garantia_contrato="";
   $observaciones="";
   
 $db->CompleteTrans();   
	
}	
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
$sql = "select * from licitaciones_datos_adicionales.normas";
$consulta_normas = $db->Execute($sql) or die($db->ErrorMsg("No se pudo ejecutar la consulta ".$sql)); 

$sql = "select * from  licitaciones_datos_adicionales.componentes_nueva_lic";
$consulta_componentes = $db->Execute($sql) or die($db->ErrorMsg("No se pudo ejecutar la consulta ".$sql)); 

$sql = "select * from  licitaciones_datos_adicionales.tipo_garantia where defecto=1 order by id_tipo_garantia";
$consulta_tipo_garantia = $db->Execute($sql) or die($db->ErrorMsg("No se pudo ejecutar la consulta ".$sql)); 


?>
<SCRIPT language='JavaScript' src='funcion.js'></SCRIPT>
<script>
var feriados=Array(),armo_arre=0;
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
  if (!document.all.radio_garantia_oferta[0].checked) document.all.exige_reaseguro_oferta.disabled=1;
  else document.all.exige_reaseguro_oferta.disabled=0;
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
  if (!document.all.radio_garantia_contrato[0].checked) document.all.exige_reaseguro_contrato.disabled=1;
  else document.all.exige_reaseguro_contarto.disabled=0;
 }
 
 
 function controles()
 {var texto, retorno=true, control=0, chequeado=0;
  
 ////////////////////////////////////////////////////////////////////
 armo_arre=0;
  tamano=feriados.length;
  fecha=document.all.fecha_apertura.value;
  fecha_partes=fecha.split("/");
  dia=fecha_partes[0].replace(/^0/,"");
  mes=fecha_partes[1].replace(/^0/,"");      
  anio=fecha_partes[2].replace(/^0/,"");     
  mitad=anio+"-"+mes+"-"+dia;
  while (armo_arre<tamano)
        {if (feriados[armo_arre]==mitad) 
            {if (confirm ("La Fecha de Apertura no corresponde a un dia Hábil.\nEsta Seguro?")) {armo_arre=tamano+1; document.all.control_fecha.value=1;}
             else {armo_arre=tamano+1; document.all.control_fecha.value=0; return false;}
            }	 
         armo_arre++;   
        }	
  prueba = new Date(fecha_partes[2], fecha_partes[1]-1, fecha_partes[0]);    
  prueba_a = prueba.getDay();
  if (prueba_a==6) {if (confirm ("La Fecha de Apertura no corresponde a un dia Hábil.\nEsta Seguro?")) document.all.control_fecha.value=1; else {return false; document.all.control_fecha.value=0;}}
  if (prueba_a==0) {if (confirm ("La Fecha de Apertura no corresponde a un dia Hábil.\nEsta Seguro?")) document.all.control_fecha.value=1; else {return false; document.all.control_fecha.value=0;}}    
 ////////////////////////////////////////////////////////////////////
 
  /*if (document.all.vencimiento_presentacion_comentario.value=!"" && document.all.exige_muestras.value=="-1")
     {alert ("Lleno el Comentario en la Parte de Muestras pero no selecciono si las exige o no.")
     }*/	
   
  control=0; 
   
   	
  
  /*if(document.all.direccion.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección de la entidad');
     return false;
    }
  if(document.all.proceso_numero.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Proceso Número');
     return false;
    }  
 if(document.all.expediente.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Expediente');
     return false;
    }
 if(document.all.valor_pliego.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Valor del Pliego');
     return false;
    }
 if(document.all.otros_garantia_oferta.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Otros, en Garantía de Oferta');
     return false;
    }
 if(document.all.otros_garantia_contrato.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Otros, de Garantía de Contrato y/o Adjudicación');
     return false;
    }
 if(document.all.plazo_impugnacion.value.indexOf('"')!=-1)
    {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Plazo de Impugnación');
     return false;
    }                        
 if(document.all.garantia_impugnacion.value.indexOf('"')!=-1)
   {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Garantía de Impugnación');
    return false;
   } 
 if(document.all.garantia_bienes.value.indexOf('"')!=-1)
   {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Garantía de los Bienes');
    return false;
   }  
 if(document.all.vencimiento_presentacion_comentario.value.indexOf('"')!=-1)
   {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Comentarios en Exige Muestras');
    return false;
   }
 if(document.all.observaciones.value.indexOf('"')!=-1)
   {alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Observaciones');
    return false;
   }*/    
    
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
  if (document.all.dias_mantenimiento.value=="")
     {texto=texto+"Mantenimiento de Oferta\n";
      retorno=false;
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
  if (!chequeado)               
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
  if (!chequeado)               
     {texto=texto+"Componentes de la Oferta\n";
      retorno=false;
     }             
  if (document.all.garantia_bienes.value=="")
     {texto=texto+"Comentarios en Garantía de los Bienes\n";
      retorno=false;
     }          
      
  if (!retorno)
     {alert (texto);
      return retorno;
     }
 }	 
</script>

<html>
<body>
<form action='licitaciones_new.php' method=post>
<br>
<script>
<?
 foreach ($_ses_feriados as $fecha => $descripciones)
         {
         ?>    
          feriados[armo_arre]="<?=$fecha?>";          
          armo_arre++;         
         <?	
         }	 
?>
</script>
<br>
 <input name="dir_cambio" value="0" type="hidden">
 <input name="control_fecha" value="<?=$control_fecha?>" type="hidden">
 <table width=95% class="bordes" border="1" cellspacing="1" cellpadding="2" bgcolor="<?=$bgcolor_out?>" align="center" >
  <tr id=mo>
   <td align="center" colspan="2"><b><font size="3"> Nueva Licitación</font></b></td>
  </tr>
  <tr>
   <td align="left" colspan="2"><b><font color="Red">No ingresar datos con comillas dobles ("")</font></b></td>
  </tr>
  <tr>
   <td align="left" colspan="2"><b>Los campos marcados con <font size="4" color="red">*</font> son <font zise="3" color="red">OBLIGATORIOS</font></b></td>
  </tr>
  <tr>
   <td colspan="2">
    <table  width="100%">
     <tr>
      <td ><b><font size="3" color="red">*</font>&nbsp;Distrito:</b></td>
      <td colspan="2">
       <select name="distrito" onchange='document.all.dir_cambio.value=1;document.forms[0].submit()'>        
        <?if (!$distrito) {?><option value=""></option><?}
          $result = "select id_distrito,nombre from distrito order by nombre";
          $resultado_consulta=$db->Execute($result) or die($db->ErrorMsg("No se pudo ejecutar la consulta ".$result));
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
       <select name="entidad" onchange='document.all.dir_cambio.value=1;document.forms[0].submit()'>
        <?if (!$distrito) {?><option selected value="-1">Seleccione Distrito</option>  
        <?} else 
             {$result = "select id_entidad, nombre, direccion from entidad where ((id_distrito=$distrito) and (activo_entidad=1)) order by nombre";
              $resultado_consulta=$db->Execute($result) or die($db->ErrorMsg("No se pudo ejecutar la consulta ".$result));
	          while (!$resultado_consulta->EOF) 
	           {if ($resultado_consulta->fields['id_entidad']==$entidad) {$selected="selected";$direccion=$resultado_consulta->fields['direccion'];}
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
      <td><input name="direccion" type="text" size="70" value="<?=$direccion?>"></td>
      <td align="right"><input name="guardar_por_defecto" type="checkbox" <?if ($guardar_por_defecto) echo "checked"?>>&nbsp;&nbsp;<b>Guardar por Defecto</b> </td>
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
       <select name="dias_mantenimiento_como">
        <option <?if ($dias_mantenimiento_como=="Hábiles") echo "selected"?> value="Hábiles">Hábiles</option>
        <option <?if ($dias_mantenimiento_como=="Corridos") echo "selected"?> value="Corridos">Corridos</option>
       </select>  
      </td>
      <td align="left"> 
       &nbsp;&nbsp;<input name="renovacion_automatica" <?if ($renovacion_automatica) echo "checked"?> type="checkbox">&nbsp;<b>Renovación Automatica</b>
      </td>
     </tr>
     <tr>
      <td><b><font size="3" color="red">*</font>&nbsp;Forma de Pago:</b></td>
      <td align="left" colspan="2">
       <select name='forma_pago' OnChange='beginEditing(this);'>
	   <?foreach ($array_forma as $val) 
		         {
				  ?>
				  <option <?if ($val==$forma_pago) echo "selected"?> value="<?=$val?>"><?=$val?></option>
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
        <?foreach ($array_plazo as $val) 
		         {
				  ?>
				  <option <?if ($val==$plazo_entrega) echo "selected"?> value="<?=$val?>"><?=$val?></option>
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
      <td><b>Moneda:</b></td>
      <td colspan="5">
       <?echo "<select name=moneda>\n";
	     $result1 = $db->Execute("select id_moneda,nombre from moneda") or die($db->ErrorMsg());
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
      <td>
     <?
     $sql_responsable="select id_responsable_apertura,id_usuario,nombre,apellido,interno,mail,movil
	      			   from responsables_apertura
					   join usuarios using(id_usuario)";
	 $res_responsable=sql($sql_responsable) or fin_pagina();
	 ?> 
     <b>Responsable Apertura:</b> <select  name='responsables'  onKeypress='buscar_op(this);' onblur='borrar_buffer();' onclick='borrar_buffer();'>";
	       <option value=-1>Seleccionar ... </option>
	  <?
			while (!$res_responsable->EOF) { ?>
               <option value='<?=$res_responsable->fields['id_responsable_apertura'];?>'>
                         <?=$res_responsable->fields['apellido'].' '.$res_responsable->fields['nombre'];?>
               </option>
            <?    
               $res_responsable->MoveNext();
            }?>
      </select>
     </tr>     
    </table>
   </td>
  </tr>
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
	     $result1 = $db->Execute("select id_moneda,nombre from moneda") or die($db->ErrorMsg());
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
                 <? if ($primero==1) {?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Exige Reaseguro?</b><select name="exige_reaseguro_oferta" ><option value="-1" <?if ($exige_reaseguro_oferta=="-1") echo "selected"?>></option><option <?if ($exige_reaseguro_oferta=="si") echo "selected"?> value="si">Si</option><option <?if ($exige_reaseguro_oferta=="no") echo "selected"?> value="no">No</option></select></td></tr><?}
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
	     $result1 = $db->Execute("select id_moneda,nombre from moneda") or die($db->ErrorMsg());
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
                 <? if ($primero==1) {?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Exige Reaseguro?</b><select name="exige_reaseguro_contrato"><option value="-1" <?if ($exige_reaseguro_contrato=="-1") echo "selected"?>></option><option <?if ($exige_reaseguro_contrato=="si") echo "selected"?> value="si">Si</option><option <?if ($exige_reaseguro_contrato=="no") echo "selected"?> value="no">No</option></select></td></tr><?}
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
        <option <?if ($dias_impugnacion_como=="Hábiles") echo "selected"?> value="Hábiles">Hábiles</option>
        <option <?if ($dias_impugnacion_como=="Corridos") echo "selected"?> value="Corridos">Corridos</option>
       </select>  
      </td>
      <td><input name="plazo_impugnacion" value="<?=$plazo_impugnacion?>" type="text" size="40"></td>
     </tr>
     <tr>
      <td><b>Garantía de Impugnación</b></td>
      <td>&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="procentaje_impugnacion" value="<?=$porcentaje_impugnacion?>" type="text" onkeypress="return filtrar_teclas(event,'0123456789.');" size="5"><b>%</b></td>
      <td><input name="garantia_impugnacion" value="<?=$garantia_impugnacion?>" type="text" size="40">
     </tr>
     <tr>
      <td><b>Presupuesto Oficial</b></td>
      <td colspan="2">
       <?
        echo "<select name='moneda_presupuesto_oficial'>\n";
	    $result1 = $db->Execute("select id_moneda,nombre from moneda") or die($db->ErrorMsg());
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
      <td colspan="2"><b>Normas de la Oferta:</b><td>
     </tr>
     <tr>
      <td width="35%"><b>Se pueden Cotizar Alternativas</b></td>      
      <td>
       <select name="cotizar_alternativas">
        <option value="-1"></option>
        <option value="Si">Si</option>
        <option value="No">No</option>
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
     <tr>
      <td colspan="2"><b>(Monitor, CPU, Teclado y Mouse)</b></td>
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
      <select name="exige_muestras">
       <option value="-1"></option>
       <option value="Si">Si</option>
       <option value="No">No</option>
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
   <td align="center"><input name="guardar" type="submit" value="Guardar" onclick="return controles()"> </td>
   <!--<td align="center"><input name="guardar" type="submit" value="Guardar" > </td>-->
   <td align="center"><input name="cancelar" type="reset" value="Cancelar"> </td>
  </tr>
 </table>
</form>
</body>
</html>