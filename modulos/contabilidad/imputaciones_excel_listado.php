<?php
/*
$Author: mari $
$Revision: 1.1 $
$Date: 2006/04/26 14:24:05 $
*/
require_once ("../../config.php");

$sql=$parametros["sql"];
$total_imputaciones=$parametros["total_imputaciones"];
$suma=$parametros["suma"];
$datos_imputaciones=sql($sql) or fin_pagina();

excel_header("imputaciones.xls");

?>
<form name=form1 method=post action="imputaciones_excel_listado.php">
<table width="100%">
  <tr>
   <td>
    <table width="100%">
     <tr>
      <td align=left>
       <b>Total imputaciones: </b><?=$total_imputaciones?> 
       </td>
       <td align="right">
        <b>  Total:</b> $ <?=formato_money($suma)?>
       </td>
      </tr>
    </table>  
   </td>
  </tr>  
 </table> 
 <br>
 <table width="100%" align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5"> 
  <tr bgcolor=#C0C0FF>
   <td width="5%" align="center">
      Fecha
   </td>
   <td width="10%" align="center">
      Monto
    </td>
   <td width="25%" align="center">
         Cuenta
   </td>
   <td width="5%" align="center">
     ID Egreso
   </td>
   <td width="20%" align="center">
     Banco
   </td>
   <td width="10%" align="center">
     Nº Cheque
   </td>
   <td width="5%" align="center">
    ID Débito
   </td>
   <td width="15%" title="Usuario que generó la Imputación" align="center">
    Usuario
    </td>
  </tr>
  <?
   
  while (!$datos_imputaciones->EOF)
  {
    ?>
     <tr >
      <td align="center">
       <?=fecha($datos_imputaciones->fields["fecha"])?>
      </td>
      <td align="center">
       <?
        $monto_fila=$datos_imputaciones->fields["monto_imputacion"];
       ?>
       <table>
        <tr>
         <td width="1%">
          <?='$'?>
         </td>
         <td align="right">
          <?=formato_money($monto_fila)?>
         </td>
        </tr>
       </table>  
      </td>
      <td>
       <?=$datos_imputaciones->fields["nombre_cuenta_imputacion"]?>
      </td>
      <td align="center">
        <?=$datos_imputaciones->fields["id_ingreso_egreso"]?>
      </td>
      <td align="center">
       <?=$datos_imputaciones->fields["nombrebanco"]?>
      </td>
      <td align="center">
       <?=$datos_imputaciones->fields["númeroch"]?>
      </td>
      <td align="center">
       <?=$datos_imputaciones->fields["iddébito"]?>
      </td>
      <td>
       <?=$datos_imputaciones->fields["usuario"]?>
      </td>
     </tr>
    <?
  	$datos_imputaciones->MoveNext();
  }//de while(!$datos_imputaciones->EOF)
  ?>
 </table>
 </form>