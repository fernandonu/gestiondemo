<?
/*
Autor: MAC
Fecha: 28/02/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2005/07/23 14:14:50 $

*/

require_once("../../../config.php");
require_once("funciones.php");

$id_asiento_bancos=$parametros["id_asiento_bancos"];
//traemos los datos del asiento
$query="select * from asiento_bancos where id_asiento_bancos=$id_asiento_bancos";
$asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 

$depositos_acreditados=number_format($asiento_info->fields["depositos_acreditados"],2,'.','');
  $comision_gastos=number_format($asiento_info->fields["comision_gastos"],2,'.','');
  $retencion_iva=number_format($asiento_info->fields["retencion_iva"],2,'.','');
  $retencion_imp_ganancias=number_format($asiento_info->fields["retencion_imp_ganancias"],2,'.','');
  $corapi_cta_particular=number_format($asiento_info->fields["corapi_cta_particular"],2,'.','');
  $sellados=number_format($asiento_info->fields["sellados"],2,'.','');
  $intereses_saldo_deudor=number_format($asiento_info->fields["intereses_saldo_deudor"],2,'.','');
  $impuesto_ley_25413=number_format($asiento_info->fields["impuesto_ley_25413"],2,'.','');
  $caja=number_format($asiento_info->fields["caja"],2,'.','');
  //traemos todos los datos de percepcion_retencion
  $query="select * from retencion_IB where id_asiento_bancos=$id_asiento_bancos";
  $pr=sql($query,"<br>Error al traer retencion_ib, del asiento") or fin_pagina();
  
  $valores_ret_ing_brutos=array();
  $i=0;$acum=0;
  while (!$pr->EOF) 
  {
   $valores_ret_ing_brutos[$i]["monto"]=$pr->fields["monto"];
   $valores_ret_ing_brutos[$i]["plan"]=$pr->fields["nombre_distrito"];
   $acum+=$pr->fields["monto"];
   $i++;

   $pr->MoveNext();	
  }//de while (!$pr->EOF)
$mes=$asiento_info->fields["mes_periodo"];
$año=$asiento_info->fields["anio_periodo"];

$banco_haber=$asiento_info->fields['comision_gastos']+
            $asiento_info->fields['retencion_iva']+$asiento_info->fields['retencion_imp_ganancias']+
            $acum+$asiento_info->fields['corapi_cta_particular']+$asiento_info->fields['sellados']+
            $asiento_info->fields['intereses_saldo_deudor']+$asiento_info->fields['impuesto_ley_25413']+
            $asiento_info->fields['caja'];  
$caja_haber=$asiento_info->fields['depositos_acreditados'];
$suma_haber=$caja_haber+$banco_haber;
$suma_haber=$suma_debe=number_format($suma_haber,2,'.','');            
$caja_haber=number_format($caja_haber,2,'.','');
$banco_haber=number_format($banco_haber,2,'.','');

//traemos el nombre del banco para mostrar en la hoja a imprimir
$query="select nombrebanco from tipo_banco where idbanco=".$asiento_info->fields["idbanco"];
$bank=sql($query,"<br>Error al traer el nombre del banco<br>") or fin_pagina();
$nombre_banco=$bank->fields['nombrebanco'];
?>

<html>
<head>
 <title>Asiento de Bancos</title>
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
      <b>Asiento de Bancos Período <?=$mes."/".$año?> - Banco <?=$nombre_banco?></b>
     </td>
    </tr>
   </table>
  </td>
 </tr>  
<tr>
  <td width="10%">
   Cuenta
  </td>
  <td width="40%">
   Concepto
  </td>
  <td width="25%">
   DEBE
  </td>
  <td width="25%">
   HABER
  </td>
 </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <b>por los depósitos</b>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Banco <?=$nombre_banco?>
     </td>
     <td width="25%">
      <?=$depositos_acreditados?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>   
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Caja
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      <?=$caja_haber?>
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <b>por los egresos de banco</b>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Comisiones y gastos banco
     </td>
     <td width="25%"> 
      <?=$comision_gastos?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Retenciones I.V.A.
     </td>
     <td width="25%">
      <?=$retencion_iva?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Retención Imp. a las Ganancias
     </td>
     <td width="25%">
      <?=$retencion_imp_ganancias?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%" align="center">
      <b>Retenciones de Ingresos Brutos</b>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <?
     //generamos la lista de ingresos brutos con las provincias
     //correspondientes, segun la BD
     generar_lista_provincias(0,$valores_ret_ing_brutos,0);
    ?>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Corapi Cuenta Particular
     </td>
     <td width="25%">
      <?=$corapi_cta_particular?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Sellados
     </td>
     <td width="25%">
      <?=$sellados?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Intereses s/saldo deudor
     </td>
     <td width="25%">
      <?=$intereses_saldo_deudor?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Impuesto Ley 25413
     </td>
     <td width="25%">
      <?=$impuesto_ley_25413?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Caja
     </td>
     <td width="25%">
      <?=$caja?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Banco <?=$nombre_banco?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      <?=$banco_haber?>
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan="4">
      &nbsp;
     </td>
    </tr>    
    <tr> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%" align="right">
      <b>TOTALES</b>
     </td>
     <td width="25%">
      <?=$suma_debe?>
     </td>
     <td width="25%">
      <?=$suma_haber?>
     </td>
    </tr>
</table>
<input type="button" name="imprimir1" value="Imprimir" onclick="document.all.imprimir.style.visibility='hidden';document.all.imprimir1.style.visibility='hidden';window.print(); window.close();">
</body>
</html>  