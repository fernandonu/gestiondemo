<?
/*
Autor: MAC
Fecha: 21/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2004/12/22 00:52:37 $

*/

require_once("../../../config.php");

$id_asiento_venta=$parametros["id_asiento_venta"];
$query="select * from asiento_ventas where id_asiento_ventas=$id_asiento_venta";
$asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 

$resp_ins_21=number_format($asiento_info->fields["resp_inscripto_21"],2,'.','');
$resp_ins_105=number_format($asiento_info->fields["resp_inscripto_105"],2,'.','');
$cons_final_21=number_format($asiento_info->fields["cons_final_21"],2,'.','');
$cons_final_105=number_format($asiento_info->fields["cons_final_105"],2,'.','');
$neto_a=number_format($asiento_info->fields["neto_a"],2,'.','');
$neto_b=number_format($asiento_info->fields["neto_b"],2,'.','');

$mes=$asiento_info->fields["mes_periodo"];
$año=$asiento_info->fields["anio_periodo"];

$suma_haber=$asiento_info->fields["resp_inscripto_21"]+$asiento_info->fields["resp_inscripto_105"]+
             $asiento_info->fields["cons_final_21"]+$asiento_info->fields["cons_final_105"];
$ventas=$asiento_info->fields["neto_a"]+$asiento_info->fields["neto_b"];
$caja=$suma_haber+$ventas;
             
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
  <td colspan="6">
   <table width="100%">
    <tr>
     <td align="center" >
      <b>Asiento de Remuneraciones Período <?=$mes."/".$año?></b>
     </td>
    </tr>
   </table>
  </td>
 </tr>  
 <tr align="center">
  <td width="10%">
   Cuenta
  </td>
  <td width="30%">
   Denominación
  </td>
  <td width="30%">
   Tasa
  </td>
  <td width="10%">
   Parcial
  </td>
  <td width="15%">
   DEBE
  </td>
  <td width="15%">
   HABER
  </td>
 </tr>
    <tr> 
     <td width="10%">
      225
     </td>
     <td width="35%">
      <b>I.V.A. Débito Fiscal</b>
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="10%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($suma_haber,2,'.','')?>
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Responsable Inscripto
     </td>
     <td width="30%">
      21%
     </td>
     <td width="15%">
      <?=$resp_ins_21?>
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
      Responsable Inscripto
     </td>
     <td width="30%">
      10.5%
     </td>
     <td width="15%">
      <?=$resp_ins_105?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      Consumidor Final
     </td>
     <td width="30%">
      21%
     </td>
     <td width="15%">
      <?=$cons_final_21?>
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
      Consumidor Final
     </td>
     <td width="30%">
      10.5%
     </td>
     <td width="15%">
      <?=$cons_final_105?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      <b>VENTAS</b>
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($ventas,2,'.','')?>
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      NETO "A"
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$neto_a?>
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
      NETO "B"
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$neto_b?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="30%">
      CAJA
     </td>
     <td width="30%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($caja,2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="6">
      &nbsp;
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<input type="button" name="imprimir1" value="Imprimir" onclick="document.all.imprimir.style.visibility='hidden';document.all.imprimir1.style.visibility='hidden';window.print(); window.close();">
</body>
</html>  