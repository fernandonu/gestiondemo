<?php
/*
$Author: marco_canderle $
$Revision: 1.30 $
$Date: 2005/12/14 20:15:17 $
*/

require_once("../../config.php");
require_once("fns.php");


//obtengo todos los pagos de la orden de compra
$nro_orden=$parametros["nro_orden"];
$moneda=$parametros["moneda"];
//$moneda = 1 es igual a dolares $moneda= 0  es igual a pesos
//obtengo los datos
$sql="select comentario_pagos,internacional from orden_de_compra where nro_orden=$nro_orden";
$resultado=$db->execute($sql) or die($sql);
$comentarios=$resultado->fields['comentario_pagos'];
$internacional=$resultado->fields['internacional'];
//el siguiente if funciona de la siguiente manera
//si no hay datos en pagos busco en ordenes_pagos

$sql="select * from pago_orden join ordenes_pagos using (id_pago) where pago_orden.nro_orden=$nro_orden order by id_pago";
$resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
$filas_encontradas=$resultado->RecordCount();

if ($filas_encontradas<=0) {
                          $sql="select * from pago_orden join ordenes_pagos using(id_pago) where pago_orden.nro_orden=$nro_orden order by id_pago";
                          $resultado=$db->execute($sql) or die($sql);
                          $filas_encontradas=$resultado->RecordCount();
                          }

if ($moneda) $simbolo="u\$s";
else $simbolo="$";

?>
<html>
<body bgcolor='<? echo $bgcolor2;?>'>
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
if (!($moneda)) $colspan=5;
                else $colspan=7;

$total_a_pagar=0;
$ordenes_atadas=PM_ordenes($nro_orden);
$cant_ordenes=sizeof($ordenes_atadas);
if($cant_ordenes>1)
{$total_a_pagar=ordenes_pago_multiple($ordenes_atadas,$simbolo,"100%",1);
}

if($internacional)
 $texto_int="Internacional";
else 
 $texto_int="";
?>	
<br>
<table width="100%" align="center" align="Center" border="1" cellspacing="1"  bordercolor="#000000">
<tr id="mo">
    <td colspan='<?=$colspan; ?>' align="center">
        <b>Resumen de los Pagos - Orden de Compra <?=$texto_int?> Nº <?=$nro_orden;?>
    </td>
</tr>
<tr id='ma'>
  <td colspan='<?=$colspan;?>' align='right' >
  <?
  if($cant_ordenes<=1)
  {?>        
            <b><font size='2'>
             TOTAL A PAGAR:
             </font>
            <font size='2' color='red'>
                <?$monto_total=monto_a_pagar($nro_orden);
                  $monto_total=number_format($monto_total,"2",".","");
                  echo "$simbolo  $monto_total";
                ?>
            </font>
   <?
  }         
  ?>          
  </td>
</tr>
<tr bordercolor="#800000">
        <td colspan='7' align='center' width='100%'>          
        <?
          $total_nc=detalle_nc($nro_orden,$simbolo);
         ?>
         </td>
         </tr>
<tr>
   <td align="center" width="10%"><b>Pago    </td>
   <td align="center" width="10%"><b>Id      </td>
<? if (!($moneda)) { ?>
        <td align="center" width="20%"><b>Monto   </td>
        <td align="center" width="30%"><b>Usuario </td>
        <td align="center" width="20%"><b>Fecha   </td>
  <?  } //del then
    else {
  ?>
        <td align="center" width="20%"><b>Monto Dolares  </td>
        <td align="center" width="6%"><b>Dolar </td>
        <td align="center" width="20%"><b> Monto Pagado </td>
        <td align="center" width="20%"><b>Usuario </td>
        <td align="center" width="14%"><b>Fecha   </td>
 <?
   }//del else
 ?>
</tr>
<?
$total_monto=0;
$total_monto_pagado=0;
$cantidad_pagos=0;
for($i=0;$i<$filas_encontradas;$i++){
//realizo el resumen con los pagos
$usuario=$resultado->fields['usuario'];
$fecha=fecha($resultado->fields['fecha']);
if ($resultado->fields['númeroch'] || $resultado->fields['iddébito']||$resultado->fields['id_ingreso_egreso'])
{
$cantidad_pagos++;//contador para ver cuantos pagos hizo la persona
$monto=$resultado->fields['monto'];
$total_monto+=$monto;
$monto=number_format($resultado->fields['monto'],"2",".","");

echo "<tr>";
if (!($moneda)) {
   if ($resultado->fields["númeroch"]){
                                     $nro_cheque=$resultado->fields['númeroch'];
                                     echo "<td>Cheque       </td>";
                                     $link_ch = encode_link("../bancos/bancos_movi_chdeb.php",Array("Modificar_Cheque_Numero"=>$nro_cheque,"pagina"=>"mail","disabled"=>"disabled" ));
                                     echo "<td align='right' onclick = window.open('$link_ch','','left=100,top=50,height=360,width=600'); title='Ver este cheque' style='cursor:hand; color:blue;'> $nro_cheque </td>";
                                     echo "<td align='right'> \$ $monto      </td>";
                                     echo "<td align='center'> $usuario    </td>";
                                     echo "<td align='right'> $fecha       </td>";
                                     }
   if ($resultado->fields["id_ingreso_egreso"]){
                                     $nro_ingreso=$resultado->fields['id_ingreso_egreso'];
                                     echo "<td>Efectivo       </td>";
                                     echo "<td align='right'> $nro_ingreso </td>";
                                     echo "<td align='right'> $simbolo $monto      </td>";
                                     echo "<td align='center'> $usuario    </td>";
                                     echo "<td align='right'> $fecha       </td>";
                                     }
  if ($resultado->fields["iddébito"])  {
                                     $nro_debito=$resultado->fields['iddébito'];
                                     echo "<td>Transferencia       </td>";
                                     echo "<td align='right'> $nro_debito </td>";
                                     echo "<td align='right'> \$ $monto      </td>";
                                     echo "<td align='center'> $usuario    </td>";
                                     echo "<td align='right'> $fecha       </td>";
                                      }
    }
    else {
         $valor_dolar=$resultado->fields["valor_dolar"];
         $monto_pagado=$monto*$valor_dolar;
         $valor_dolar=number_format($valor_dolar,"3",".","");
         $total_monto_pagado+=$monto_pagado;
         $monto_pagado=number_format($monto_pagado,"2",".","");
         if ($resultado->fields["númeroch"]){
                                     $nro_cheque=$resultado->fields['númeroch'];
                                     echo "<td>Cheque       </td>";
                                     $link_ch = encode_link("../bancos/bancos_movi_chdeb.php",Array("Modificar_Cheque_Numero"=>$nro_cheque,"pagina"=>"mail","disabled"=>"disabled" ));
                                     echo "<td align='right' onclick = window.open('$link_ch','','left=100,top=50,height=360,width=600'); title='Ver este cheque' style='cursor:hand; color:blue;'> $nro_cheque </td>";
                                     echo "<td align='right'> $simbolo $monto      </td>";
                                     echo "<td align='right'> $valor_dolar </td>";
                                     echo "<td align='right'> \$ $monto_pagado </td>";
                                     echo "<td align='center'> $usuario    </td>";
                                     echo "<td align='right'> $fecha       </td>";
                                     }
        if ($resultado->fields["id_ingreso_egreso"]){
                                     $nro_ingreso=$resultado->fields['id_ingreso_egreso'];
                                     echo "<td>Efectivo       </td>";
                                     echo "<td align='right'> $nro_ingreso </td>";
                                     echo "<td align='right'> $simbolo $monto       </td>";
                                     echo "<td align='right'> $valor_dolar </td>";
                                     echo "<td align='right'> \$ $monto_pagado </td>";
                                     echo "<td align='center'> $usuario    </td>";
                                     echo "<td align='right'> $fecha       </td>";
                                     }
      if ($resultado->fields["iddébito"])  {
                                     $nro_debito=$resultado->fields['iddébito'];
                                     echo "<td>Transferencia       </td>";
                                     echo "<td align='right'> $nro_debito </td>";
                                     echo "<td align='right'> $simbolo $monto      </td>";
                                     echo "<td align='right'> $valor_dolar </td>";
                                     echo "<td align='right'> \$ $monto_pagado </td>";
                                     echo "<td align='center'> $usuario    </td>";
                                     echo "<td align='right'> $fecha       </td>";
                                       }
    } //del else
echo "</tr>";
}//del if grandote donde estan todos los |||||
$resultado->MoveNext();
}//fin del for
$total_monto=number_format($total_monto,"2",".","");
$total_monto_pagado=number_format($total_monto_pagado,"2",".","");
/*
if (!($moneda)) {
                 echo "<tr id='mo'>";
                 echo "<td>Pagos</td>";
                 echo "<td align='right'>". $cantidad_pagos ."</td>";
                 echo "<td align='right'> $simbolo $total_monto      </td>";
                 echo "<td >&nbsp; </td>";
                 echo "<td >&nbsp; </td>";
                 echo "</tr>";
                }
                else{
                     echo "<tr id='mo'>";
                     echo "<td>Pagos </td>";
                     echo "<td align='right'>". $cantidad_pagos ."</td>";
                     echo "<td align='right'> $simbolo $total_monto      </td>";
                     echo "<td > &nbsp;</td>";
                     echo "<td align='right'> \$ $total_monto_pagado</td>";
                     echo "<td>&nbsp; </td>";
                     echo "<td> &nbsp;</td>";
                     echo "</tr>";
                }
*/

                 echo "<tr id='mo'>";
                 echo "<td>Pagos</td>";
                 echo "<td align='right'>". $cantidad_pagos ."</td>";
                 echo "<td align='right'> $simbolo $total_monto      </td>";
                 echo "<td >&nbsp; </td>";
if (!($moneda)) {
                 echo "<td >&nbsp; </td>";
                 }
                 else
                    {
                     echo "<td align='right'> \$ $total_monto_pagado</td>";
                     echo "<td>&nbsp; </td>";
                     echo "<td> &nbsp;</td>";
                     }
                echo "</tr>";
            
      
                
if ($comentarios!=""){
?>
<tr>
 <td colspan='<?=$colspan;?>' id='ma'>
 &nbsp;
 </td>
</tr>
<tr id='mo'>
  <td colspan='<?=$colspan?>'>
  Comentarios
  </td>
</tr>
<tr>
  <td colspan='<?=$colspan?>' align='center'>
  <b>
  <textarea rows='3' cols='120' readonly>
  <?=$comentarios?>
  </textarea>
  </td>
</tr>
<tr>
 <td colspan='<?=$colspan;?>' id='ma'>
 &nbsp;
 </td>
</tr>

<?
}
//chequeo  si hay diferencia en los montos
$datos=control_montos_pagados($nro_orden,$total_a_pagar,$total_nc);

if (!($datos["correcto"]))
{
?>
<tr bgcolor='white'>
  <td colspan='<?=$colspan;?>'>
       <b> Diferencia Monto de la Orden con Montos Pagados:
      <font color='red'>
      <?
      echo $simbolo." ".$datos["diferencia"];
      ?>
     </font>
     <?
     if($datos["diferencia"]>0)
     {echo "<br><font color='red'>     ATENCIÓN: EL MONTO PAGADO ES MAYOR QUE EL MONTO QUE SE DEBIA PAGAR.</font>";
     }	
     ?>

  </td>
<tr>

<?

}
?>
</tr>
<tr>
 <td colspan='<?=$colspan;?>' align="center">

 <?if ($parametros['pagina']!="ord_compra") {
       $link=encode_link("../ord_compra/ord_compra_pagar.php",array("nro_orden"=>$parametros["nro_orden"]));?>
       <input type=button name=Volver value='   Volver   '   style='width:18%' OnClick="javascript:window.location='<?=$link?>';">
 <? }
     else { ?>
            <input type=button name=Salir value='   Salir   ' style='width:18%' OnClick="window.close();">
<? } ?>
 </td>
</tr>

</table>
</body>
</html>