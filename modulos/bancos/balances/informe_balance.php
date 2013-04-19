<?php
/*
$Author: fernando $
$Revision: 1.5 $
$Date: 2006/04/17 23:19:20 $
*/
require_once("../../../config.php");
require_once("funciones_balance.php");
echo $html_header;



$fecha_hasta=date("Y-m-d");
$pagina=$_POST["pagina"] or $pagina=$parametros["pagina"];
$titulo=$parametros["titulo"] or $titulo=$_POST["titulo"];
$id_moneda=$parametros["id_moneda"]  or $id_moneda=$_POST["id_moneda"];
$itemspp=100000;

?>
<form name=form1 method=post>
<input type=hidden name=pagina value="<?=$pagina?>">
<input type=hidden name=sql value="<?=$sql?>">
<input type=hidden name=titulo value="<?=$titulo?>">
<table class=bordes width=90% align=center>
  <tr>
     <td id=mo>Información Detallada del Balance</td>
  </tr>
  <tr bgcolor=white>
     <td align=Center>
      <font color=red size=3><b><?=$titulo?></b></font>
     </td>
  </tr>
  <tr>
    <td>
     <?
     switch ($pagina){
         case "cuentas_a_cobrar":
               include("detalle_cuentas_a_cobrar.php");
               break;
         case "stock_produccion":
              include("detalle_stock_produccion.php");
              break;
         case "stock_pendiente":
              include("detalle_stock_pendiente.php");
              break;    
         case "deuda_comercial":
              include("detalle_deuda_comercial.php");
              break;
          case "oc internacional":
              include("detalle_oc_internacionales.php");
              break;
                  
         case "oc parcialmente recibidas":
              include("detalle_oc_no_recibidas.php");
              break;

         default:
               break;
     }
     ?>
    </td>
  </tr>
  <tr><td align=center><input type=button name=cerrar value=Cerrar onclick='window.close()'</td></tr>
</table>
</form>
<?
echo fin_pagina();
?>