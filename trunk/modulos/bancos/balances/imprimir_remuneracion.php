<?
/*
Autor: MAC
Fecha: 20/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2004/12/21 00:06:29 $

*/

require_once("../../../config.php");
$nro_asiento=$parametros["nro_asiento"];
$query="select * from asiento_remuneraciones where nro_asiento=$nro_asiento";
$asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 

  $fecha=$asiento_info->fields["fecha_asiento"];
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
  $mes=$asiento_info->fields["mes_periodo"];
  $año=$asiento_info->fields["anio_periodo"];
  
?>
<html>
<head>
 <title>Asiento de remuneraciones</title>
</head>
<body >
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<input type="button" name="imprimir" value="Imprimir" onclick="document.all.imprimir.style.visibility='hidden';document.all.imprimir1.style.visibility='hidden';window.print(); window.close();">
<table width="100%" border="1">
 <tr>
  <td>
   <table width="100%">
    <tr>
     <td align="center" colspan="2">
      <b>Asiento de Remuneraciones Nº <?=$nro_asiento?></b>
     </td>
    </tr>
    <tr>
     <td>
      <b>Período <?=$mes."/".$año?></b>
     </td>
     <td align="right">
      <b>Fecha <?=fecha($fecha)?></b>
     </td>
    </tr>
   </table>
  </td>
 </tr>  
 <tr>
  <td>
   <table  width="100%" border="1">
    <tr align="center">
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
     <td colspan="5" align="center"> 
      <b>REMUNERATIVO</b>
     </td>
    </tr>
    <tr>
     <td>
      575
     </td>
     <td>
      Básico
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$basico?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      578
     </td>
     <td>
      Presentismo
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$presentismo?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      563
     </td>
     <td>
      S.A.C.
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$sac?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      564
     </td>
     <td>
      Vacaciones
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$vacaciones?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      &nbsp;
     </td>
     <td>
      Decreto 1529
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$dec1529?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      &nbsp;
     </td>
     <td>
      A cuenta Futuros
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$acuenta?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="2" align="right">
      Total Remunerativo
     </td>
     <td colspan="3" align="left">
      <b><?=number_format($basico+$presentismo+$sac+$vacaciones+$dec1529+$acuenta,2,'.','')?></b>
     </td>
    </tr>
    <tr>
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td align="center" colspan="5">
      <b>NO REMUNERATIVO</b>
     </td>
    </tr>
    <tr>
     <td>
      562
     </td>
     <td>
      Vacaciones no Gozadas
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$vac_no_gozadas?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      565
     </td>
     <td>
      Indemnizaciones
     </td>
     <td>
      &nbsp;
     </td>
     <td>
      <?=$indemnizacion?>
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr> 
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
      <?=$dec1347?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
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
      <?=$salario_familiar?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
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
      <?=$redondeo?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
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
      <?=$carga_social?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="5">
      <b>Aportes del Personal</b>
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Jubilación
     </td>
     <td width="30%">
      <?=$jubilacion?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Ley 19032
     </td>
     <td width="30%">
      <?=$ley19032?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Obra Social
     </td>
     <td width="30%">
      <?=$obra_social?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
     <tr>
     <td colspan="2" align="right">
      <b>Total</b>
     </td> 
     <td colspan="3">
      <?=number_format($jubilacion+$ley19032+$obra_social,2,'.','')?>
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Sindicato
     </td>
     <td width="30%">
      <?=$sindicato?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Adicional Sindicato
     </td>
     <td width="30%">
      <?=$adicional_sindicato?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      FAECYS
     </td>
     <td width="30%">
      <?=$faecys?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
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
      <?=$apers_suss_pagar?>
     </td>
    </tr>
    <tr> 
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
      <?=$apers_obra_social_pagar?>
     </td>
    </tr>
    <tr> 
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
      <?=$apers_sindicato_pagar?>
     </td>
    </tr>
    <tr> 
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
      <?=$apers_faecys_pagar?>
     </td>
    </tr>
    <td align="center" colspan="5">
       <b>Contribución Empleador</b>
     </td>
    </tr>
    <tr> 
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
      <?=$cemple_suss_pagar?>
     </td>
    </tr>
    <tr> 
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
      <?=$cemple_obra_social_pagar?>
     </td>
    </tr>
    <tr> 
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
      <?=$cemple_art_pagar?>
     </td>
    </tr>
    <tr>
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr> 
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
      <?
      $suma_debito=$basico+$presentismo+$sac+$vacaciones+$dec1529+$acuenta+
                    $vac_no_gozadas+$indemnizacion+$dec1347+$salario_familiar+
                    $redondeo+$carga_social;
      $suma_aportes=$apers_suss_pagar+$apers_obra_social_pagar+$apers_sindicato_pagar+
                    $apers_faecys_pagar+$cemple_suss_pagar+$cemple_obra_social_pagar+
                    $cemple_art_pagar;
      $cemple_sueldos_pagar=$suma_debito-$suma_aportes;
      $suma_credito=$suma_aportes+$cemple_sueldos_pagar;
      echo number_format($cemple_sueldos_pagar,2,'.','');
      ?>
     </td>
    </tr>    
    <tr bgcolor="White">
     <td width="70%" align="center" colspan="3">
      <b>Sumas Iguales</b>
     </td>
     <td width="15%">
      <b><?=number_format($suma_debito,2,'.','')?></b>
     </td>
     <td width="15%">
      <b><?=number_format($suma_credito,2,'.','')?></b>
     </td>
    </tr>
   </table>
  </td>
 </tr>  
</table> 
<input type="button" name="imprimir1" value="Imprimir" onclick="document.all.imprimir.style.visibility='hidden';document.all.imprimir1.style.visibility='hidden';window.print(); window.close();">
</body>
</html>