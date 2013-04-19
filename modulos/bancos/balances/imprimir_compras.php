<?
/*
Autor: MAC
Fecha: 03/01/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2005/02/28 22:42:45 $

*/

require_once("../../../config.php");


$id_asiento_compras=$parametros["id_asiento_compras"];
$query="select * from asiento_compras where id_asiento_compras=$id_asiento_compras";
$asiento_info=sql($query,"<br><br>Error al traer datos de asiento de compras<br><br>") or fin_pagina(); 

$iva_cf_21=number_format($asiento_info->fields["iva_cf_21"],2,'.','');
$iva_cf_27=number_format($asiento_info->fields["iva_cf_27"],2,'.','');
$iva_cf_105=number_format($asiento_info->fields["iva_cf_105"],2,'.','');
$iva_cf_19=number_format($asiento_info->fields["iva_cf_19"],2,'.','');
$iva_cf_95=number_format($asiento_info->fields["iva_cf_95"],2,'.','');
$impuesto_interno=number_format($asiento_info->fields["impuesto_interno"],2,'.','');

$mes=$asiento_info->fields["mes_periodo"];
$año=$asiento_info->fields["anio_periodo"];

//traemos todos los datos de cuentas_compras
  $query="select sum(monto) as monto,numero_cuenta,concepto,plan
   from cuentas_compras join tipo_cuenta using (numero_cuenta)
   where id_asiento_compras=$id_asiento_compras
   group by numero_cuenta,concepto,plan";
  $datos_cuentas_compras=sql($query,"<br>Error al traer cuentas_compras, del asiento") or fin_pagina();
    
?>

<html>
<head>
 <title>Asiento de Retenciones</title>
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
      <b>Asiento de Compras Período <?=$mes."/".$año?></b>
     </td>
    </tr>
   </table>
  </td>
 </tr>  
 <tr>
  <td width="10%">
   Cuenta
  </td>
  <td width="20%">
   Concepto
  </td>
  <td width="40%">
   Plan
  </td>
  <td width="15%">
   DEBE
  </td>
  <td width="15%">
   HABER
  </td>
 </tr>
    <tr>
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
 <?
 $sub_total=0;
  while(!$datos_cuentas_compras->EOF)
  {
 ?>   
    <tr> 
     <td width="10%">
      <?=$datos_cuentas_compras->fields["numero_cuenta"]?>
     </td>
     <td width="20%">
      <?=$datos_cuentas_compras->fields["concepto"]?>
     </td>
     <td width="40%">
      <?=$datos_cuentas_compras->fields["plan"]?>
     </td>
     <td width="15%">
      <?=number_format($datos_cuentas_compras->fields["monto"],2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
   <?
   $sub_total+=$datos_cuentas_compras->fields["monto"];
   $datos_cuentas_compras->MoveNext();
  }//de while(!$datos_cuentas_compras->EOF)
  ?>
  <tr>
     <td colspan="5">
      &nbsp;
     </td>
  </tr>
  <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="20%">
      <b>SUB-TOTAL</b>
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($sub_total,2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
  <tr>
     <td colspan="5">
      &nbsp;
     </td>
  </tr>
  <tr> 
     <td width="10%">
      40
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 21%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$iva_cf_21?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      41
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 27%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$iva_cf_27?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      42
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 10.5%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$iva_cf_105?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      43
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 19%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$iva_cf_19?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      44
     </td>
     <td width="20%">
      I.V.A. Crédito Fiscal 9.5%
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$iva_cf_95?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      536
     </td>
     <td width="20%">
      Impuesto Interno
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      <?=$impuesto_interno?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      1
     </td>
     <td width="20%">
      <b>CAJA</b>
     </td>
     <td width="40%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <?$suma_haber=$suma_debe=$caja=$sub_total+$iva_cf_21+$iva_cf_27+$iva_cf_105+$iva_cf_19+$iva_cf_95+$impuesto_interno;
        echo number_format($caja,2,'.','')
      ?>
     </td>
    </tr>
     <tr>
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <b>Totales</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($suma_debe,2,'.','')?>
     </td>
     <td width="15%">
      <?=number_format($suma_haber,2,'.','')?>
     </td>
    </tr>
</table>
<input type="button" name="imprimir1" value="Imprimir" onclick="document.all.imprimir.style.visibility='hidden';document.all.imprimir1.style.visibility='hidden';window.print(); window.close();">
</body>
</html>  