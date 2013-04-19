<?
/*
Autor: MAC
Fecha: 13/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.14 $
$Date: 2005/07/29 15:14:17 $

*/

require_once("../../../config.php");

if($_POST["guardar"]=="Guardar")
{$db->StartTrans();
 extract($_POST,EXTR_SKIP);
 //insertamos el asiento de remuneracion
 $fecha_hoy=date("Y-m-d H:i:s",mktime());
 
 //ponemos en null todos los campos que no son obligatorios
 //si es que no tienen un valor
 if($rem_sac=="")
  $rem_sac="null";
 if($rem_vacaciones=="")
  $rem_vacaciones="null";
 if($rem_cta_futuro=="")
  $rem_cta_futuro="null";
 if($apers_adic_sindicato=="")
  $apers_adic_sindicato="null";  
 if($nrem_vac_no_goz=="")
  $nrem_vac_no_goz="null";
 if($nrem_indemnizacion=="")
  $nrem_indemnizacion="null";  
 if($nrem_sal_familiar=="")
  $nrem_sal_familiar="null"; 
 if($nrem_carga_social=="")
  $nrem_carga_social="null";  
 if($nrem_redondeo=="")
  $nrem_redondeo="null";
  
 if($actualizar=="")//si es un asiento nuevo, lo insertamos
 {
 	$query="select nextval('asiento_remuneraciones_nro_asiento_seq') as id_log_asiento";
    $idlog=sql($query,"<br><br>Error al traer secuencia de asiento de remuneracion<br><br>") or fin_pagina();
 	$nro_asiento=$idlog->fields['id_log_asiento'];
 	$query="insert into asiento_remuneraciones (nro_asiento,fecha_asiento,basico,
  	presentismo,sac,vacaciones,decreto1529,a_cta_futuros,vacaciones_no_goz,indemnizaciones,
  	dto1347,salario_familiar,redondeo,cargas_sociales,jubilacion,ley19032,obra_social,sindicato,
  	adic_sindicato,faecys,ap_pers_suss_pagar,ap_pers_obra_social_pagar,ap_pers_sindicato_pagar,
  	ap_pers_faecys_pagar,cont_pers_suss_a_pagar,cont_pers_obra_social_pagar,art_pagar,
  	sueldos_pagar,mes_periodo,anio_periodo)
  	values ($nro_asiento,'$fecha_hoy',$rem_basico,$rem_presentismo,$rem_sac,$rem_vacaciones,$rem_dec1529,
  	$rem_cta_futuro,$nrem_vac_no_goz,$nrem_indemnizacion,$nrem_dec1347,$nrem_sal_familiar,
  	$nrem_redondeo,$nrem_carga_social,$apers_jubilacion,$apers_ley19032,$apers_obra_social,
  	$apers_sindicato,$apers_adic_sindicato,$apers_faecys,
  	$apers_suss_pagar,$apers_obra_social_pagar,$apers_sindicato_pagar,$apers_faecys_pagar,
  	$cemple_suss_pagar,$cemple_obra_social_pagar,$cemple_art_pagar,$cemple_sueldos_pagar,$mes,$año)";	
	
  	sql($query,"<br><br>Error al insertar los datos del asiento de remuneraciones<br><br>") or fin_pagina();
  	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_remuneraciones (nro_asiento,tipo,fecha,usuario)
    	    values($nro_asiento,'Creación','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de creación<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se insertó con éxito el asiento de remuneraciones del período $mes/$año</center></b>";
 }//de if($actualizar=="")
 elseif($actualizar==1)//sino actualizamos el asiento
 {
 	$query="update asiento_remuneraciones set basico=$rem_basico,
  	presentismo=$rem_presentismo,sac=$rem_sac,vacaciones=$rem_vacaciones,decreto1529=$rem_dec1529,
 	a_cta_futuros=$rem_cta_futuro,vacaciones_no_goz=$nrem_vac_no_goz,indemnizaciones=$nrem_indemnizacion,
  	dto1347=$nrem_dec1347,salario_familiar=$nrem_sal_familiar,redondeo=$nrem_redondeo,
 	cargas_sociales=$nrem_carga_social,jubilacion=$apers_jubilacion,ley19032=$apers_ley19032,
 	obra_social=$apers_obra_social,sindicato=$apers_sindicato,adic_sindicato=$apers_adic_sindicato,
 	faecys=$apers_faecys,ap_pers_suss_pagar=$apers_suss_pagar,ap_pers_obra_social_pagar=$apers_obra_social_pagar,
 	ap_pers_sindicato_pagar=$apers_sindicato_pagar,ap_pers_faecys_pagar=$apers_faecys_pagar,
 	cont_pers_suss_a_pagar=$cemple_suss_pagar,cont_pers_obra_social_pagar=$cemple_obra_social_pagar,
 	art_pagar=$cemple_art_pagar,sueldos_pagar=$cemple_sueldos_pagar,mes_periodo=$mes,anio_periodo=$año
    where nro_asiento=$nro_asiento";	
	
  	sql($query,"<br><br>Error al actualizar los datos del asiento de remuneraciones<br><br>") or fin_pagina();
  	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_remuneraciones (nro_asiento,tipo,fecha,usuario)
    	    values($nro_asiento,'Actualización','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de actualización<br><br>") or fin_pagina();
  	
  	$msg="<br><b><center>Se actualizó con éxito el asiento de remuneraciones del período $mes/$año</center></b>";
 }//del else if($actualizar=="")	
 $db->CompleteTrans();
}	



if($_POST["Borrar"]=="Borrar")
{
 $nro_asiento=$_POST["nro_asiento"];	
 $db->StartTrans();
 $query="delete from log_asiento_remuneraciones where nro_asiento=$nro_asiento";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from asiento_remuneraciones where nro_asiento=$nro_asiento";
 sql($query,"<br>Error <br>") or fin_pagina();
 $nro_asiento="";
 unset($_POST);
?>
<table align="center">
<tr>
<td>
<b>El proceso de borrado se ha realizado Correctamente</b>
</td>
</tr>
</table>
<?
 $db->CompleteTrans();
 
}

if($_POST["traer_datos"]=="Traer Datos" ||$_POST["guardar"]=="Guardar")
{
 
 $mes=$_POST["mes"];
 $año=$_POST["año"];
 //controlamos si el asiento para el periodo elegido, ya esta cargado
 $query="select * from asiento_remuneraciones where mes_periodo=$mes and anio_periodo=$año";
 $asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 
 $nro_asiento=$asiento_info->fields["nro_asiento"];
 $con_id=1; 
 //traemos los datos para llenar todos los campos correspondientes
 if($nro_asiento=="")
 {$con_id=0;
  $query="select last_value from asientos.asiento_remuneraciones_nro_asiento_seq";
  $id=sql($query,"<br><br>Error al traer valor aproximado de nro de asiento<br><br>") or fin_pagina();
  $nro_asiento=$id->fields["last_value"]+1;
  
  
  $query="select basico,dec1529,acuenta,salario_familiar,dec1347,
          presentismo,vacaciones,jubilacion,ley19032,obra_social,
          sindicato,sindicato_familiar,faecys,mes_sac,dias_sac,vac_no_gozadas,gratificacion
          from sueldos
          where fecha ilike '$año-$mes-%'";
  $datos_sueldos=sql($query) or fin_pagina();
  
  //hacemos las sumas respectivas para poder llenar los campos necesarios
  $basico=0;$dec1529=0;$acuenta=0;$salario_familiar=0;$dec1347=0;
  $presentismo=0;$vacaciones=0;$jubilacion=0;$ley19032=0;$obra_social=0;
  $sindicato=0;$adicional_sindicato=0;$faecys=0;$sac=0;$vac_no_gozadas=0;$indemnizacion=0;
  while(!$datos_sueldos->EOF)
  {
   $basico+=$datos_sueldos->fields["basico"];
   $dec1529+=$datos_sueldos->fields["dec1529"];
   $acuenta+=$datos_sueldos->fields["acuenta"];
   $salario_familiar+=$datos_sueldos->fields["salario_familiar"];
   $dec1347+=$datos_sueldos->fields["dec1347"];
   $presentismo+=$datos_sueldos->fields["presentismo"];
   $vacaciones+=$datos_sueldos->fields["vacaciones"];
   $jubilacion+=$datos_sueldos->fields["jubilacion"];
   $ley19032+=$datos_sueldos->fields["ley19032"];
   $obra_social+=$datos_sueldos->fields["obra_social"];
   $sindicato+=$datos_sueldos->fields["sindicato"];
   $adicional_sindicato+=$datos_sueldos->fields["sindicato_familiar"];
   $faecys+=$datos_sueldos->fields["faecys"];
   //calculo del sac
   $mes_sac=$datos_sueldos->fields['mes_sac'];
   $dias_sac=$datos_sueldos->fields['dias_sac'];
   $mxd_sac=$mes_sac*30;
   $dias_trab=$mxd_sac+$dias_sac;
   
   $sac+=((($datos_sueldos->fields["basico"]+$datos_sueldos->fields["dec1529"]+$datos_sueldos->fields["presentismo"]+$datos_sueldos->fields["acuenta"])/2)/180)*$dias_trab;
   //fin calculo del sac
   
   $vac_no_gozadas+=$datos_sueldos->fields["vac_no_gozadas"];	
   $indemnizacion+=$datos_sueldos->fields["gratificacion"];	
   
   $datos_sueldos->MoveNext();
  }//de while(!$datos_sueldos->EOF)
  
  //le damos formato a los numeros
  $basico=number_format($basico,2,'.','');
  $dec1529=number_format($dec1529,2,'.','');
  $acuenta=number_format($acuenta,2,'.','');
  $salario_familiar=number_format($salario_familiar,2,'.','');
  $dec1347=number_format($dec1347,2,'.','');
  $presentismo=number_format($presentismo,2,'.','');
  $vacaciones=number_format($vacaciones,2,'.','');
  $jubilacion=number_format($jubilacion,2,'.','');
  $ley19032=number_format($ley19032,2,'.','');
  $obra_social=number_format($obra_social,2,'.','');
  $sindicato=number_format($sindicato,2,'.','');
  $adicional_sindicato=number_format($adicional_sindicato,2,'.','');
  $faecys=number_format($faecys,2,'.','');
  $sac=number_format($sac,2,'.','');
  $vac_no_gozadas=number_format($vac_no_gozadas,2,'.','');
  $indemnizacion=number_format($indemnizacion,2,'.','');
 }//de if($nro_asiento=="")
 else 
 {
  $cartel="<br><B><center><font color='red'>ATENCION: El asiento para este período ya fue realizado</font></center></b>";	
  //si existe el nro_asiento, entonces llenamos las variables con valores
  //traidos desde la BD
  $basico=number_format($asiento_info->fields["basico"],2,'.','');
  $presentismo=number_format($asiento_info->fields["presentismo"],2,'.','');
  $sac=number_format($asiento_info->fields["sac"],2,'.','');
  $vacaciones=number_format($asiento_info->fields["vacaciones"],2,'.','');
  $dec1529=number_format($asiento_info->fields["decreto1529"],2,'.','');
  $acuenta=number_format($asiento_info->fields["a_cta_futuros"],2,'.','');
  $vac_no_gozadas=number_format($asiento_info->fields["vacaciones_no_goz"],2,'.','');
  $indemnizacion=number_format($asiento_info->fields["indemnizaciones"],2,'.','');
  $dec1347=number_format($asiento_info->fields["dto1347"],2,'.','');
  $salario_familiar=number_format($asiento_info->fields["salario_familiar"],2,'.','');
  $redondeo=number_format($asiento_info->fields["redondeo"],2,'.','');
  $carga_social=number_format($asiento_info->fields["cargas_sociales"],2,'.','');
  $jubilacion=number_format($asiento_info->fields["jubilacion"],2,'.','');
  $ley19032=number_format($asiento_info->fields["ley19032"],2,'.','');
  $obra_social=number_format($asiento_info->fields["obra_social"],2,'.','');
  $sindicato=number_format($asiento_info->fields["sindicato"],2,'.','');
  $adicional_sindicato=number_format($asiento_info->fields["adic_sindicato"],2,'.','');
  $faecys=number_format($asiento_info->fields["faecys"],2,'.','');
  $apers_suss_pagar=number_format($asiento_info->fields["ap_pers_suss_pagar"],2,'.','');
  $apers_obra_social_pagar=number_format($asiento_info->fields["ap_pers_obra_social_pagar"],2,'.','');
  $apers_sindicato_pagar=number_format($asiento_info->fields["ap_pers_sindicato_pagar"],2,'.','');
  $apers_faecys_pagar=number_format($asiento_info->fields["ap_pers_faecys_pagar"],2,'.','');
  $cemple_suss_pagar=number_format($asiento_info->fields["cont_pers_suss_a_pagar"],2,'.','');
  $cemple_obra_social_pagar=number_format($asiento_info->fields["cont_pers_obra_social_pagar"],2,'.','');
  $cemple_art_pagar=number_format($asiento_info->fields["art_pagar"],2,'.','');
  $cemple_sueldos_pagar=number_format($asiento_info->fields["sueldos_pagar"],2,'.','');
  
  $actualizar=1;
 }//del else de if($nro_asiento=="")
 
}//de if($_POST["traer_datos"]=="Traer Datos")

echo $html_header;
?>
<script language="JavaScript" src="../../../lib/NumberFormat150.js"></script>
<script>

//funcion que calcula  el total de remuneraciones
function calcular_total_remun()
{var basico;
 var presentismo;
 var sac;
 var vacaciones;
 var dec1529;
 var cta_futuro;
 //si estan vacios los campos, le damos 0 a las variables, 
 //para que la cuenta la siga haciendo con los otros campos
 if(document.all.rem_basico.value=="")
  basico=0;
 else
  basico=parseFloat(document.all.rem_basico.value);
 if(document.all.rem_presentismo.value=="")
  presentismo=0;
 else
  presentismo=parseFloat(document.all.rem_presentismo.value);
 if(document.all.rem_sac.value=="")
  sac=0;
 else 
  sac=parseFloat(document.all.rem_sac.value);
 if(document.all.rem_vacaciones.value=="")
  vacaciones=0;
 else
  vacaciones=parseFloat(document.all.rem_vacaciones.value); 
 if(document.all.rem_dec1529.value=="") 
  dec1529=0;
 else 
  dec1529=parseFloat(document.all.rem_dec1529.value);
 if(document.all.rem_cta_futuro.value=="")
  cta_futuro=0;
 else 
  cta_futuro=parseFloat(document.all.rem_cta_futuro.value);
  
 document.all.rem_tot_rem.value=formato_BD(basico+presentismo+sac+vacaciones+dec1529+cta_futuro);
 
 sueldos_a_pagar();
}	

//calcula el total de sueldos a pagar
function sueldos_a_pagar()
{
 var vac_no_goz;
 var indemnizacion;
 var dec1347;
 var sal_familiar;
 var redondeo;
 var carga_social;	
	
 var apers_suss_pagar;
 var apers_obra_social_pagar;
 var apers_sindicato_pagar;
 var apers_faecys_pagar;
 var cemple_suss_pagar;
 var cemple_obra_social_pagar;
 var cemple_art_pagar;

 var total_no_rem;
 var total_ap_ce;
 
 //si estan vacios los campos, le damos 0 a las variables, 
 //para que la cuenta la siga haciendo con los otros campos
  if(document.all.nrem_vac_no_goz.value=="")
   vac_no_goz=0;
  else 
   vac_no_goz=parseFloat(document.all.nrem_vac_no_goz.value);
  if(document.all.nrem_indemnizacion.value=="")
    indemnizacion=0;
  else 
   indemnizacion=parseFloat(document.all.nrem_indemnizacion.value);
  if(document.all.nrem_dec1347.value=="")
   dec1347=0;
  else  
   dec1347=parseFloat(document.all.nrem_dec1347.value);
  if(document.all.nrem_sal_familiar.value=="")
   sal_familiar=0;
  else 
   sal_familiar=parseFloat(document.all.nrem_sal_familiar.value);
  if(document.all.nrem_redondeo.value=="")
       redondeo=0;
  else 
   redondeo=parseFloat(document.all.nrem_redondeo.value);
  if(document.all.nrem_carga_social.value=="") 
   carga_social=0;
  else 
   carga_social=parseFloat(document.all.nrem_carga_social.value); 
   
 total_no_rem=vac_no_goz+indemnizacion+dec1347+sal_familiar+redondeo+carga_social;
   
   
 if(document.all.apers_suss_pagar.value=="")
  apers_suss_pagar=0;
 else
  apers_suss_pagar=parseFloat(document.all.apers_suss_pagar.value);
 if(document.all.apers_obra_social_pagar.value=="")
  apers_obra_social_pagar=0;
 else
  apers_obra_social_pagar=parseFloat(document.all.apers_obra_social_pagar.value); 
 if(document.all.apers_sindicato_pagar.value=="")
  apers_sindicato_pagar=0;
 else
  apers_sindicato_pagar=parseFloat(document.all.apers_sindicato_pagar.value)  
 if(document.all.apers_faecys_pagar.value=="")
  apers_faecys_pagar=0;
 else
  apers_faecys_pagar=parseFloat(document.all.apers_faecys_pagar.value); 
 if(document.all.cemple_suss_pagar.value=="")
  cemple_suss_pagar=0;
 else
  cemple_suss_pagar=parseFloat(document.all.cemple_suss_pagar.value);
 if(document.all.cemple_obra_social_pagar.value=="")
  cemple_obra_social_pagar=0;
 else
  cemple_obra_social_pagar=parseFloat(document.all.cemple_obra_social_pagar.value); 
 if(document.all.cemple_art_pagar.value=="")
  cemple_art_pagar=0;
 else
  cemple_art_pagar=parseFloat(document.all.cemple_art_pagar.value); 

 total_ap_ce=apers_suss_pagar+apers_obra_social_pagar+
                             apers_sindicato_pagar+apers_faecys_pagar+
                             cemple_suss_pagar+cemple_obra_social_pagar+
                             cemple_art_pagar;
                            
 document.all.cemple_sueldos_pagar.value=formato_BD(parseFloat(document.all.rem_tot_rem.value)+total_no_rem-total_ap_ce); 
}	

//calcula el total de aportes del personal
function total_aportes_personal()
{var apers_jubilacion;
 var apers_ley19032;
 var apers_obra_social;
	
 if(document.all.apers_jubilacion.value=="")
  apers_jubilacion=0;
 else
  apers_jubilacion=parseFloat(document.all.apers_jubilacion.value);
 if(document.all.apers_ley19032.value=="")
  apers_ley19032=0;
 else 
  apers_ley19032=parseFloat(document.all.apers_ley19032.value);
 if(document.all.apers_obra_social.value=="")
  apers_obra_social=0;
 else
  apers_obra_social=parseFloat(document.all.apers_obra_social.value); 
  
 document.all.apers_total_aportes_personal.value=
                 formato_BD(apers_jubilacion+apers_ley19032+apers_obra_social);	
}	

//calcula la suma de todo el credito o todo el debito, dependiendo
//del parametro que se le pase, y lo pone en el input correspondiente.
function sumas_iguales(cred_deb)
{
 if(cred_deb==0)
 {
  var basico;
  var presentismo;
  var sac;
  var vacaciones;
  var dec1529;
  var cta_futuro;
  var vac_no_goz;
  var indemnizacion;
  var dec1347;
  var sal_familiar;
  var redondeo;
  var carga_social;	

  //si estan vacios los campos, le damos 0 a las variables, 
  //para que la cuenta la siga haciendo con los otros campos
  if(document.all.rem_basico.value=="")
   basico=0;
  else
   basico=parseFloat(document.all.rem_basico.value);
  if(document.all.rem_presentismo.value=="")
   presentismo=0;
  else
   presentismo=parseFloat(document.all.rem_presentismo.value);
  if(document.all.rem_sac.value=="")
   sac=0;
  else 
   sac=parseFloat(document.all.rem_sac.value);
  if(document.all.rem_vacaciones.value=="")
   vacaciones=0;
  else
   vacaciones=parseFloat(document.all.rem_vacaciones.value); 
  if(document.all.rem_dec1529.value=="") 
   dec1529=0;
  else 
   dec1529=parseFloat(document.all.rem_dec1529.value);
  if(document.all.rem_cta_futuro.value=="")
   cta_futuro=0;
  else 
   cta_futuro=parseFloat(document.all.rem_cta_futuro.value);
  if(document.all.nrem_vac_no_goz.value=="")
   vac_no_goz=0;
  else 
   vac_no_goz=parseFloat(document.all.nrem_vac_no_goz.value);
  if(document.all.nrem_indemnizacion.value=="")
    indemnizacion=0;
  else 
   indemnizacion=parseFloat(document.all.nrem_indemnizacion.value);
  if(document.all.nrem_dec1347.value=="")
   dec1347=0;
  else  
   dec1347=parseFloat(document.all.nrem_dec1347.value);
  if(document.all.nrem_sal_familiar.value=="")
   sal_familiar=0;
  else 
   sal_familiar=parseFloat(document.all.nrem_sal_familiar.value);
  if(document.all.nrem_redondeo.value=="")
       redondeo=0;
  else 
   redondeo=parseFloat(document.all.nrem_redondeo.value);
  if(document.all.nrem_carga_social.value=="") 
   carga_social=0;
  else 
   carga_social=parseFloat(document.all.nrem_carga_social.value);
  document.all.suma_credito.value=formato_BD(
   basico+presentismo+sac+vacaciones+dec1529+cta_futuro+vac_no_goz+indemnizacion+dec1347+sal_familiar+redondeo+carga_social);
 }//de if(cred_deb==0)	
 else if(cred_deb==1)
 {
  var suss_pagar;	
  var obra_social_pagar;
  var sindicato_pagar;
  var faecys_pagar;
  var cemple_suss_pagar;
  var cemple_obra_social_pagar;
  var cemple_art_pagar;
  var sueldos_pagar;	

  if(document.all.apers_suss_pagar.value=="")
   suss_pagar=0;
  else 
   suss_pagar=parseFloat(document.all.apers_suss_pagar.value);
  if(document.all.apers_obra_social_pagar.value=="")
   obra_social_pagar=0;
  else 
   obra_social_pagar=parseFloat(document.all.apers_obra_social_pagar.value);
  if(document.all.apers_sindicato_pagar.value=="") 
   sindicato_pagar=0; 
  else 
   sindicato_pagar=parseFloat(document.all.apers_sindicato_pagar.value);
  if(document.all.apers_faecys_pagar.value=="")
   faecys_pagar=0;
  else 
   faecys_pagar=parseFloat(document.all.apers_faecys_pagar.value);
  if(document.all.cemple_suss_pagar.value=="")
   cemple_suss_pagar=0;
  else  
   cemple_suss_pagar=parseFloat(document.all.cemple_suss_pagar.value); 
  if(document.all.cemple_obra_social_pagar.value=="")
   cemple_obra_social_pagar=0;
  else 
   cemple_obra_social_pagar=parseFloat(document.all.cemple_obra_social_pagar.value); 
  if(document.all.cemple_art_pagar.value=="")
   cemple_art_pagar=0;
  else  
   cemple_art_pagar=parseFloat(document.all.cemple_art_pagar.value); 
  if(document.all.cemple_sueldos_pagar.value=="")
   sueldo_pagar=0;
  else 	
   sueldo_pagar=parseFloat(document.all.cemple_sueldos_pagar.value);

  document.all.suma_debito.value=formato_BD(suss_pagar+obra_social_pagar+
                                     sindicato_pagar+faecys_pagar+cemple_suss_pagar+
                                     cemple_obra_social_pagar+cemple_art_pagar+sueldo_pagar
                                           );
                                           
 }//de else if(cred_deb==1)
 		
}//de function sumas_iguales(cred_deb)	


//funcion que controla que los campos obligatorios sean llenados
function control_campos()
{var msg;
 var faltan;
 faltan=0;
 msg="Faltan llenar los siguientes campos\n";
 msg+="-------------------------------------------------------\n";
 if(document.all.rem_basico.value=="")
 {msg+="Remunerativo: Básico\n";
  faltan=1;
 }
 if(document.all.rem_presentismo.value=="")
 {msg+="Remunerativo: Presentismo\n";
  faltan=1;
 }		
 if(document.all.rem_dec1529.value=="")
 {msg+="Remunerativo: Decreto 1529\n";
  faltan=1;
 }  
 if(document.all.nrem_dec1347.value=="")
 {msg+="No Remunerativo: Decreto 1347\n";
  faltan=1;
 } 
 if(document.all.apers_jubilacion.value=="")
 {msg+="Aportes del Personal: Jubilación\n";
  faltan=1;
 } 
 if(document.all.apers_ley19032.value=="")
 {msg+="Aportes del Personal: Ley 19032\n";
  faltan=1;
 } 
 if(document.all.apers_obra_social.value=="")
 {msg+="Aportes del Personal: Obra Social\n";
  faltan=1;
 }
 if(document.all.apers_sindicato.value=="")
 {msg+="Aportes del Personal: Sindicato\n";
  faltan=1;
 }  
 if(document.all.apers_faecys.value=="")
 {msg+="Aportes del Personal: FAECYS\n";
  faltan=1;
 }  
 if(document.all.apers_suss_pagar.value=="")
 {msg+="Aportes del Personal: SUSS a Pagar\n";
  faltan=1;
 }  
 if(document.all.apers_obra_social_pagar.value=="")
 {msg+="Aportes del Personal: Obra Social a Pagar\n";
  faltan=1;
 }  
 if(document.all.apers_sindicato_pagar.value=="")
 {msg+="Aportes del Personal: Sindicato a Pagar\n";
  faltan=1;
 }  
 if(document.all.apers_faecys_pagar.value=="")
 {msg+="Aportes del Personal: FAECYS a Pagar\n";
  faltan=1;
 } 
 if(document.all.cemple_suss_pagar.value=="")
 {msg+="Contribución Empleador: SUSS a Pagar\n";
  faltan=1;
 } 
 if(document.all.cemple_obra_social_pagar.value=="")
 {msg+="Contribución Empleador: Obra Social a Pagar\n";
  faltan=1;
 } 
 if(document.all.cemple_art_pagar.value=="")
 {msg+="Contribución Empleador: A.R.T. a Pagar\n";
  faltan=1;
 } 
 if(faltan)
 {msg+="-------------------------------------------------------\n";
  alert(msg);
  return false;
 }	
 else
  return true;
 
}//de function control_campos()

//funcion que deshabilita el botón de imprimir y avisa que hubo cambios
function hay_cambios()
{
 document.all.cambios.value=1;	
 if(typeof(document.all.imprimir)!='undefined')
 {document.all.imprimir.disabled=1;	
  document.all.imprimir.title="Debe guardar para poder imprimir";
 } 
}	

</script>
<?
if($actualizar)
{
 $fondo=$bgcolor_out; 
 $query="select nro_asiento,tipo,fecha,usuario 
 from log_asiento_remuneraciones where nro_asiento=$nro_asiento order by fecha DESC";
 $log_info=sql($query,"<br>Error al traer el log del asiento<br>") or fin_pagina();
 ?>
<center>
<div align="right" style='position:relative; width:95%; height:10%; overflow:auto;'>
 <table  width="100%">
  <?
   while(!$log_info->EOF)
   {?>
    <tr id="ma">
     <td align="left">
      Fecha de <?=$log_info->fields["tipo"]?>: <?=fecha($log_info->fields["fecha"])?> <?=Hora($log_info->fields["fecha"])?>
     </td>
     <td align="right">
      Usuario: <?=$log_info->fields["usuario"]?>
     </td>
    </tr>
   	<?
   	$log_info->MoveNext();
   }//de while(!$log_info->EOF)	
  ?>
 </table>
</div> 
</center>
 <?
}//de if($actualizar)
else  
 $fondo="#a6c2fc";	 


if($msg=="")
 echo $cartel;
else 
 echo $msg; 
?>
<br>
<table align="center" width="95%" border="1">
 <tr>
  <td id="mo">
   <font size="3">Asiento de Remuneraciones</font>
  </td>
 </tr>
</table>
<form name="form1" action="asiento_remuneracion.php" method="POST">
<input type="hidden" name="actualizar" value="<?=$actualizar?>">
<input type="hidden" name="nro_asiento" value="<?=$nro_asiento?>">
<input type="hidden" name="cambios" value="0">
<table align="center" width="95%" border="1" class="bordes">
 <tr> 
  <td colspan="5">
   <table width="100%" bgcolor="White" cellpadding="3">
    <tr>
     <td colspan="2">
      <font color="Blue"><b>Asiento Nº <?=$nro_asiento?> </b></font>
     </td>
     <td colspan="3" align="right">
      <font color="Blue"><b>Fecha <?=date("d/m/Y",mktime())?></b></font>
     </td>
    </tr>
    <tr>
     <td align="right" colspan="3">
      <table border="1" width="60%">
       <tr>
        <td>
         <font color="Blue"><b>Período</b></font>
        </td>
        <td>
         <b>Mes</b>&nbsp; 
         <select name=mes onchange="document.all.guardar.disabled=1">
          <option value='01' <?if ($mes==1) echo "selected"?>>Enero</option>
          <option value='02' <?if ($mes==2) echo "selected"?>>Febrero</option>
          <option value='03' <?if ($mes==3) echo "selected"?>>Marzo</option>
          <option value='04' <?if ($mes==4) echo "selected"?>>Abril</option>
          <option value='05' <?if ($mes==5) echo "selected"?>>Mayo</option>
          <option value='06' <?if ($mes==6) echo "selected"?>>Junio</option>
          <option value='07' <?if ($mes==7) echo "selected"?>>Julio</option>
          <option value='08' <?if ($mes==8) echo "selected"?>>Agosto</option>
          <option value='09' <?if ($mes==9) echo "selected"?>>Septiembre</option>
          <option value='10' <?if ($mes==10) echo "selected"?>>Octubre</option>
          <option value='11' <?if ($mes==11) echo "selected"?>>Noviembre</option>
          <option value='12' <?if ($mes==12) echo "selected"?>>Diciembre</option>
         </select>
        </td>
        <td colspan="2" onchange="document.all.guardar.disabled=1">
         <b>Año</b>&nbsp;
         <select name=año>
          <option value='2003' <?if ($año==2003) echo "selected"?>>2003</option>
          <option value='2004' <?if ($año==2004) echo "selected"?>>2004</option>
          <option value='2005' <?if ($año==2005) echo "selected"?>>2005</option>
          <option value='2006' <?if ($año==2006) echo "selected"?>>2006</option>
          <option value='2007' <?if ($año==2007) echo "selected"?>>2007</option>
          <option value='2008' <?if ($año==2008) echo "selected"?>>2008</option>
          <option value='2009' <?if ($año==2009) echo "selected"?>>2009</option>
          <option value='2010' <?if ($año==2010) echo "selected"?>>2010</option>
          <option value='2011' <?if ($año==2011) echo "selected"?>>2011</option>
          <option value='2012' <?if ($año==2012) echo "selected"?>>2012</option>
         </select>
        </td>
       </tr>
      </table> 
     </td>
     <td>
      <input type="submit" name="traer_datos" value="Traer Datos" 
          onclick="if(document.all.cambios.value==1)
                   {if(confirm('Ha realizado cambios en este Asiento de Remuneraciones. Si continúa se perderán los cambios.\n¿Está Seguro que desea continuar?'))
                    {
                     document.all.actualizar.value=0;
                     return true;
                    } 
                    else
                     return false; 
                   }
                  "
      >
     </td>
    </tr>
   </table>  
  </td>
 </tr> 
 <?
 if($nro_asiento=="")
 {$disabled_form="disabled";
  ?>
  <tr>
   <td colspan="5" align="center">
    <font size='3' color='red'><b>Seleccione el período del asiento de remuneraciones que desea completar y presione el botón traer datos</b></font>
   </td>
  </tr> 
  <?
 }
?> 
 <tr id="ma">
  <td width="10%">
   Cuenta
  </td>
  <td width="30%">
   Título
  </td>
  <td width="30%">
   &nbsp;
  </td>
  <td width="15%">
   Débito
  </td>
  <td width="15%">
   Crédito
  </td>
 </tr>
 <tr>
  <td colspan="5">
   <table width="100%" <?=$disabled_form?>>
    <tr>
     <td align="center" id="ma_sf2" colspan="5">
       REMUNERATIVO
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      575
     </td>
     <td width="30%">
      Básico
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="rem_basico" value="<?=$basico?>" size="10" onchange="calcular_total_remun();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      578
     </td>
     <td width="30%">
      Presentismo
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="rem_presentismo" value="<?=$presentismo?>"  size="10" onchange="calcular_total_remun();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>   
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      563
     </td>
     <td width="30%">
      S.A.C
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="rem_sac" value="<?=$sac?>"  size="10" onchange="calcular_total_remun();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      564
     </td>
     <td width="30%">
      Vacaciones
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="rem_vacaciones" value="<?=$vacaciones?>"  size="10" onchange="calcular_total_remun();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Decreto 1529
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="rem_dec1529" value="<?=$dec1529?>"  size="10" onchange="calcular_total_remun();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      A cuenta futuros
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="rem_cta_futuro" value="<?=$acuenta?>"  size="10" onchange="calcular_total_remun();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>  
    <tr bgcolor="White">
     <td colspan="5" align="center"> 
      <b>Total Remuneraciones</b> <input type="text" name="rem_tot_rem" value="" readonly  size="10">
     </td>
    </tr>   
   </table>
  </td>
 </tr>
 
 <tr>
  <td colspan="5">
   <table width="100%" <?=$disabled_form?>>
    <tr>
     <td align="center" id="ma_sf2" colspan="5">
       NO REMUNERATIVO
     </td>
    </tr> 
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      562
     </td>
     <td width="30%">
      Vacaciones no gozadas
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="nrem_vac_no_goz" value="<?=$vac_no_gozadas?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      565
     </td>
     <td width="30%">
      Indemnizaciones
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="nrem_indemnizacion" value="<?=$indemnizacion?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      565
     </td>
     <td width="30%">
      Decreto 1347/03
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="nrem_dec1347" value="<?=$dec1347?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      560
     </td>
     <td width="30%">
      Salario Familiar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="nrem_sal_familiar" value="<?=$salario_familiar?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      577
     </td>
     <td width="30%">
      Redondeo
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="nrem_redondeo" value="<?=$redondeo?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      576
     </td>
     <td width="30%">
      Cargas Sociales
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="nrem_carga_social" value="<?=$carga_social?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(0);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
   </table>
  </td>
 </tr>   

 <tr>
  <td colspan="5" <?=$disabled_form?>>
   <table width="100%">
    <tr>
     <td align="center" id="ma_sf2" colspan="5">
       Aportes del Personal
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Jubilación
     </td>
     <td width="30%">
      <input type="text" name="apers_jubilacion" value="<?=$jubilacion?>"  size="10" onchange="total_aportes_personal();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');"> 
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Ley 19032
     </td>
     <td width="30%">
      <input type="text" name="apers_ley19032" value="<?=$ley19032?>"  size="10" onchange="total_aportes_personal();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Obra Social
     </td>
     <td width="30%">
      <input type="text" name="apers_obra_social" value="<?=$obra_social?>"  size="10" onchange="total_aportes_personal();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
     <tr bgcolor="White">
     <td colspan="2" align="right">
      <b>Total</b>
     </td> 
     <td colspan="3">
      <input type="text" name="apers_total_aportes_personal" readonly value="" onclick="total_aportes_personal();" size="10">
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Sindicato
     </td>
     <td width="30%">
      <input type="text" name="apers_sindicato" value="<?=$sindicato?>"  size="10" onchange="hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Adicional Sindicato
     </td>
     <td width="30%">
      <input type="text" name="apers_adic_sindicato" value="<?=$adicional_sindicato?>"  size="10" onchange="hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      FAECYS
     </td>
     <td width="30%">
      <input type="text" name="apers_faecys" value="<?=$faecys?>"  size="10" onchange="hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      250
     </td>
     <td width="30%">
      SUSS a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="apers_suss_pagar" value="<?=$apers_suss_pagar?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      252
     </td>
     <td width="30%">
      Obra Social a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="apers_obra_social_pagar" value="<?=$apers_obra_social_pagar?>" size="10" onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      254
     </td>
     <td width="30%">
      Sindicato a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="apers_sindicato_pagar" value="<?=$apers_sindicato_pagar?>" size="10" onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      255
     </td>
     <td width="30%">
      FAECYS a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="apers_faecys_pagar" value="<?=$apers_faecys_pagar?>"  size="10"  onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
   </table>
  </td>
 </tr>   
 
 <tr>
  <td colspan="5">
   <table width="100%" <?=$disabled_form?>>
    <tr>
     <td align="center" id="ma_sf2" colspan="5">
       Contribución Empleador
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      270
     </td>
     <td width="30%">
      SUSS a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="cemple_suss_pagar" value="<?=$cemple_suss_pagar?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      271
     </td>
     <td width="30%">
      Obra Social a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="cemple_obra_social_pagar" value="<?=$cemple_obra_social_pagar?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      260
     </td>
     <td width="30%">
      A.R.T. a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="cemple_art_pagar" value="<?=$cemple_art_pagar?>"  size="10" onchange="sueldos_a_pagar();sumas_iguales(1);hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$fondo?>"> 
     <td width="10%">
      240
     </td>
     <td width="30%">
      Sueldos a Pagar
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="cemple_sueldos_pagar" value="<?=$cemple_sueldos_pagar?>"  size="10" readonly onclick="sueldos_a_pagar();sumas_iguales(1);">
     </td>
    </tr>    
   </table>
  </td>
 </tr>  
</table>
<table width="95%" align="center" class="bordes" cellpadding="3" <?=$disabled_form?>> 
 <tr bgcolor="White">
  <td width="70%" align="center">
   <font color="Blue"><b>Sumas Iguales</b></font>
  </td>
  <td width="15%">
   <font color="Blue"><b><input type="text" readonly name="suma_credito" value="" class="text_8" size="10"></b></font>
  </td>
  <td width="15%">
   <font color="Blue"><b><input type="text" readonly name="suma_debito" value="" class="text_8" size="10"></b></font>
  </td>
 </tr>
</table>
<table align="center" border="1" width="95%">
 <tr>
   <?
  if($con_id && permisos_check("inicio","permiso_boton_borrar_asientos"))
  {
   ?>
   <td width="1%">
     <input type="submit" name="Borrar"   value="Borrar" onclick="if(confirm ('Se borraran los datos de este asiento.\n¿Está seguro que desea continuar?'))
																  {document.all.actualizar.value=0;
																   return true;
																  }
																  else
																   return false  
																 " 
     >
   </td>
  <?
  }
 ?>
  <td align="<?if($actualizar) echo "right";else echo "center"?>">
   <?
   if(!permisos_check("inicio","permiso_boton_guardar_asiento_rem"))
    $disabled_guardar="disabled";
   ?>
   <input type="submit" name="guardar" <?=$disabled_guardar?> <?=$disabled_form?> value="Guardar" onclick="return control_campos();">
  </td>
  <?
  if($actualizar)
  {
   $link_imprimir=encode_link("imprimir_remuneracion.php",array("nro_asiento"=>$nro_asiento));
   ?>
   <td align="left">
    <input type="button" name="imprimir" value="Imprimir" onclick="window.open('<?=$link_imprimir?>')">
   </td>
  <?
  }
  ?>
 </tr>  
</table>
</form>
<script>
calcular_total_remun(); 
total_aportes_personal();
//calculamos la suma total de debito (con parametro=0)
sumas_iguales(0);
//calculamos la suma total de credito (con parametro=1)
sumas_iguales(1);
</script>

<br>
<?fin_pagina();?>