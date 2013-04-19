<?
/*
Autor: MAC
Fecha: 20/04/05

MODIFICADA POR
$Author:  $
$Revision:  $
$Date:  $

*/

/**************************************************************************************************
 Genera una tabla con los datos propios de la OC, como son: Proveedor, Cliente, Forma de Pago, etc.
***************************************************************************************************/
?>
<table width="100%" class="tabla_datos">
 <tr>
  <td width="100%">
   &nbsp;
  </td>
 </tr>
 <tr>
  <td width="100%">
   <table class="bordes" width="100%">
    <tr class="tabla_datos">
     <td width="15%" style="font-size:14px" align="right">
      Proveedor
     </td>
     <td width="40%" class="bordes" style="font-size:14px">
      <font color="Blue"><?=$proveedor?></font>
     </td>
     <td style="font-size:14px" width="8%" align="right">
      Contacto
     </td>
     <td width="30%" class="bordes" style="font-size:14px">
      <font color="Blue"><?=($contacto)?$contacto:"&nbsp;"?></font>
     </td>
    </tr>
   </table>
  </td>  
 </tr>  
 <tr>
  <td width="100%">
   <table class="bordes" width="100%">
    <tr class="tabla_datos">
     <td width="15%" style="font-size:14px" align="right">
      Forma de Pago
     </td>
     <td width="40%" class="bordes" style="font-size:14px">
      <font color="Blue"><?=$forma_pago?></font>
     </td>
     <td width="8%" style="font-size:14px" align="right">
      Moneda
     </td>
     <td width="30%" class="bordes" style="font-size:14px">
      <font color="Blue"><?=$moneda?></font>
     </td>
     <?
     if($valor_dolar>0)
     {
     ?>
      <tr class="tabla_datos">
       <td width="65%" align="right" colspan="3" style="font-size:14px">
        Valor Dolar&nbsp;
       </td>
       <td width="30%" class="bordes" style="font-size:14px">
        <font color="Blue"><?=number_format($valor_dolar,3,'.','')?></font>
       </td>
      </tr> 
     <?
     }//de if($valor_dolar!="")
     ?> 
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td width="100%">
   <table class="bordes" width="100%">
    <tr class="tabla_datos">
     <td width="16%" style="font-size:14px" align="right">
      Fecha de Entrega
     </td>
     <td width="84%" class="bordes" style="font-size:14px">
      <font color="Blue"><?=fecha($fecha_entrega)?></font>
     </td>
    </tr> 
    <tr class="tabla_datos">
     <td style="font-size:14px" align="right">
      Cliente
     </td>
     <td class="bordes" style="font-size:14px" colspan="3">
      <font color="Blue"><?=$cliente?></font>
     </td>
    </tr> 
    <tr class="tabla_datos">
     <td style="font-size:14px" align="right">
      Lugar de Entrega
     </td>
     <td class="bordes" style="font-size:14px">
      <textarea rows="<?=row_count($lugar_entrega,100)?>" cols="101" class="text_8" readonly ><?=$lugar_entrega?></textarea>
     </td>
    </tr> 
   </table>
  </td>
 </tr>  
 <tr>
  <td width="100%">
   <table class="bordes" width="100%">
    <tr class="tabla_datos">
     <td width="16%" style="font-size:14px" align="right">
      Comentarios
     </td> 
     <td width="84%" class="bordes" style="font-size:14px">
      <textarea rows="<?=row_count($comentarios,100)?>" cols="101" class="text_8" readonly ><?=$comentarios?></textarea>
     </td>
    </tr> 
   </table>
  </td>
 </tr> 
 <tr>
  <td width="100%">
   <table class="bordes" width="100%">
    <tr class="tabla_datos">
     <td width="16%" style="font-size:14px" align="right">
      Notas Internas
     </td> 
     <td width="84%" class="bordes" style="font-size:14px">
      <textarea rows="<?=row_count($internas,100)?>" cols="101" class="text_8" readonly ><?=$internas?></textarea>
     </td>
    </tr> 
   </table>
  </td>
 </tr> 
 </table> 
