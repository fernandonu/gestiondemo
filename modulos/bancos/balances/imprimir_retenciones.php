<?
/*
Autor: MAC
Fecha: 22/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2004/12/23 21:27:42 $

*/

require_once("../../../config.php");
require_once("funciones.php");

$id_retenciones=$parametros["id_retenciones"];
$query="select * from retenciones where id_retenciones=$id_retenciones";
$asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 

$retencion_iva=number_format($asiento_info->fields["retencion_iva"],2,'.','');
$percepcion_iva=number_format($asiento_info->fields["percepcion_iva"],2,'.','');
$retencion_imp_ganancias=number_format($asiento_info->fields["retencion_imp_ganancias"],2,'.','');

$mes=$asiento_info->fields["mes_periodo"];
$año=$asiento_info->fields["anio_periodo"];

//traemos todos los datos de percepcion_retencion
  $query="select * from percepcion_retencion where id_retenciones=$id_retenciones";
  $pr=sql($query,"<br>Error al traer percepcion_retencion, del asiento") or fin_pagina();
  
  $valores_ret_ing_brutos=array();
  $valores_per_ing_brutos=array();
  $valores_ret_bancaria=array();
  $total_ret_ib=$total_per_ib=$total_ret_bancaria=0;
  $i=$j=$k=0;
  while (!$pr->EOF) 
  {
  	//armamos los arreglos para mostrar los datos respectivos a:
  	//retencion ingresos brutos, percepcion ingresos brutos, retencion bancaria
    switch ($pr->fields["clase"])
    {
    	case 0:$valores_ret_ing_brutos[$i]["monto"]=$pr->fields["monto"];
    	       $valores_ret_ing_brutos[$i]["plan"]=$pr->fields["nombre_distrito"];
    	       $total_ret_ib+=$pr->fields["monto"];
    	       $i++;
    	       break;
    	case 1:$valores_per_ing_brutos[$j]["monto"]=$pr->fields["monto"];
    	       $valores_per_ing_brutos[$j]["plan"]=$pr->fields["nombre_distrito"];
    	       $total_per_ib+=$pr->fields["monto"];
    	       $j++;
    	       break;
    	case 2:$valores_ret_bancaria[$k]["monto"]=$pr->fields["monto"];
    	       $valores_ret_bancaria[$k]["plan"]=$pr->fields["nombre_distrito"];
    	       $total_ret_bancaria+=$pr->fields["monto"];
    	       $k++;
    	       break;
    }
  	
   $pr->MoveNext();	
  }//de while (!$pr->EOF) 
  $suma_debe=number_format($asiento_info->fields["retencion_iva"]+
             $asiento_info->fields["percepcion_iva"]+
             $asiento_info->fields["retencion_imp_ganancias"]+
             $total_ret_ib+$total_per_ib+$total_ret_bancaria,2,'.','');
  $suma_haber=$caja=$suma_debe;
             
  $total_ret_ib=number_format($total_ret_ib,2,'.','');
  $total_per_ib=number_format($total_per_ib,2,'.','');
  $total_ret_bancaria=number_format($total_ret_bancaria,2,'.','');
             
  
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
      <b>Retenciones Período <?=$mes."/".$año?></b>
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
   Descripción
  </td>
  <td width="20%">
   Detalle
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
    <tr> 
     <td width="10%">
      45
     </td>
     <td width="40%">
      Retención I.V.A.
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($retencion_iva,2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      46
     </td>
     <td width="40%">
      Percepción I.V.A.
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($percepcion_iva,2,'.','')?>
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
      55
     </td>
     <td width="40%">
      Retención Impuesto a las Ganancias
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($retencion_imp_ganancias,2,'.','')?>
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
      50
     </td>
     <td width="40%">
      <b>Retención Ingresos Brutos</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($total_ret_ib,2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <?=generar_lista_provincias(0,$valores_ret_ing_brutos,0)?>
    <tr>
     <td colspan="5">
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
      51
     </td>
     <td width="40%">
      <b>Percepción Ingresos Brutos</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($total_per_ib,2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <?=generar_lista_provincias(1,$valores_per_ing_brutos,0)?>
    <tr>
     <td colspan="5">
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
      52
     </td>
     <td width="40%">
      <b>Retención Bancaria</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($total_ret_bancaria,2,'.','')?>
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <?=generar_lista_provincias(2,$valores_ret_bancaria,0)?>
    <tr>
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr> 
     <td width="10%">
      50
     </td>
     <td width="40%">
      <b>CAJA</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <?=number_format($caja,2,'.','')?>
     </td>
    </tr>
     <tr>
     <td colspan="5">
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