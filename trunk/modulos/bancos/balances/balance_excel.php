<?php
/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2006/03/30 23:15:58 $
*/
require_once ("../../../config.php");
require_once ("funciones_balance.php");

$sql = $_POST["sql"] or $sql = $parametros["sql"];
$cuentas_a_mostrar = $_POST["cuentas_a_mostrar"] or $cuentas_a_mostrar = $parametros["cuentas_a_mostrar"];
$res=sql($sql) or fin_pagina();

excel_header("balance.xls");

$cuentas=array_merge($cuentas_activo,$cuentas_pasivo);
?>
<form name=form1 method=post action="balance_excel.php">
    <table  align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5">
              <tr>
               <td  bgcolor=#C0C0FF align=center> Fecha</td>
               <td  bgcolor=#C0C0FF align=center> Dolar</td>
               <?
               //genero las columnas
               $cantidad_columnas=sizeof($cuentas);
               for($i=0;$i<$cantidad_columnas;$i++){
              	 if (in_array($cuentas[$i]["nombre"],$cuentas_a_mostrar)){
              ?>
                 <td align=center <?=excel_style("texto")?> bgcolor=#C0C0FF><?=$cuentas[$i]["nombre"]?></td>
              <?
              	}//del if
              }//del for
              ?>
              </tr>
              <?
              for($i=0;$i<$res->recordcount();$i++){
              $datos=$res->FetchRow();
              $fecha=fecha($datos["fecha"]);
              $hora=substr($datos["fecha"],10);
              ?>
              <tr>
              <td align=center width=170 ><?=$fecha." ".$hora?></td>
              <td align=center width=170 ><?=formato_money($res->fields["valor_dolar"])?></td>
              <?
              //muesto los datos a excepcion de la fecha
              for($y=0;$y<$cantidad_columnas;$y++){
               	if (in_array($cuentas[$y]["nombre"],$cuentas_a_mostrar)){
                ?>    
                <td align=right width=170><?=formato_money($datos[$cuentas[$y]["pesos"]] + ($datos[$cuentas[$y]["dolar"]] * $res->fields["valor_dolar"]) );?></td>
                <?    
               	}
               }
                ?>
              </tr>
              <?
              }//del for principal del recordcount()
              ?>                                          
    </table>
    
    
       
<?
//echo  fin_pagina();
?>              