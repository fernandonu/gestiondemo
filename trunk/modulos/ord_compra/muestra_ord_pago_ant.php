<?php
/*
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2005/02/19 18:51:06 $
*/
require_once("../../config.php");
include("./fns.php");
//echo $html_header;
 //$boton_separar=0;  => mostrar monto

$nro_orden=$parametros['ord_compra'][0];

$sql="select mostrar,titulo_forma_pago,cant_pagos,nro_orden,es_presupuesto
      from log_pagos_oc join orden_de_compra using (nro_orden)
      where id_log_pago=".$parametros['id_log_pago'];
$res=sql($sql,"datos forma pago") or fin_pagina();


?>
<HTML>
<HEAD>
<meta Name="generator" content="PHPEd Version 3.2 (Build 3220 )   ">
<title>Formas de Pago</title>
<link rel="SHORTCUT ICON"  href="/path-to-ico-file/logo.ico">
</HEAD>
<body bgcolor="<?=$bgcolor2;?>">
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
?>

<script src="../../lib/funciones.js"></script>

<form name="form1" method="POST" action="muestra_ord_pago_ant" >

<?
$sql="select proveedor,cliente,fecha_entrega,nro_lic,simbolo_orden,boton_separar_orden,monto
from datos_orden
where id_log_pago=".$parametros['id_log_pago'];
$res_orden=sql($sql) or fin_pagina();
$simbolo_orden=$res_orden->fields['simbolo_orden'];
$total=0;
?>
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">
<tr>
   <td style=<?="border:$bgcolor3;"?>  align="center" id=history colspan=2>
     <b>Orden de Compra</b>
   </td>
</tr> 
 <tr>
   <td>
    <b>Nro Orden</b>  <?=$nro_orden?>
   </td>
   <td align=right>
    <b>Fecha de Entrega</b> <?=fecha($res_orden->fields['fecha_entrega']);?>
   </td>
 </tr>
  <tr>
  <td>
   <b>Proveedor</b> <?=$res_orden->fields['proveedor'];?>
  </td>
  <td align="right">
   <b>Cliente</b> <?=$res_orden->fields['cliente'];?>
  </td>
 </tr>
<tr>
  <? 
  if($res_orden->fields['monto'])
  {?>
    <td> <b>Monto <?echo "<font color=red size=2>";
                    echo $simbolo_orden." ";
                   echo formato_money($res_orden->fields['monto']);
                   ?>
    </font> </b>                  
   </td>
  <?
   $colspan="";
   $total=$res_orden->fields['monto'];
  }
  else 
  {$colspan="colspan=2";
  }
  ?>
  <td align="right" <?=$colspan?>>
   <?if($res->fields['es_presupuesto']==1) {
      $asociado_con="Presupuesto asociado";
     }
     else {
      $asociado_con="Licitación asociada";
     }
   ?>   
   <b><?=$asociado_con?></b> <?if($res_orden->fields['nro_lic'])echo $res_orden->fields['nro_lic'];else echo "no posee";?>
  </td>
 </tr>   
</table>


<!-- Muestra ordenes asociadas en pago multiple-->
<? $sql_pm="select * from detalle_orden_pm where id_log_pago=".$parametros['id_log_pago'];
   $res_pm=sql($sql_pm) or fin_pagina();
if ($res_pm->RecordCount() >0) {
   ?>
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">	
  <tr>
   <td style=<?="border:$bgcolor3;"?>  align="center" id=history colspan=2>
     <b>Ordenes de Compra que se incluyen en el pago</b>
   </td>
 </tr> 
 <?//generamos la tabla con detalle de las ordenes de compra
  $tam=$res_pm->RecordCount();
  $total_a_pagar=0;
  for($i=0;$i<$tam;$i++)
  {?>
   <tr>
    <td >
      <b>Nro Orden: </b><?=$res_pm->fields['nro_orden']?>
    </td>
    <td >
      <b>Monto: </b><?
                      echo "$simbolo_orden ";
                      echo formato_money($res_pm->fields['monto']);
                      $total_a_pagar+=$res_pm->fields['monto'];
                    ?>
    </td>
   </tr>
 <?
 $res_pm->MoveNext();
 }//del for
 ?>
  <tr>
    <td colspan=2 align="center">
     <b>Total a Pagar <?echo "<font color=red size=2>";
                         echo "$simbolo_orden ";
                         echo formato_money($total_a_pagar);
                       ?>
   </td>
  </tr>
  </table>  
 
  <?
   $total=$total_a_pagar;
  }?>


<? 
$sql="select monto,valor_dolar,observaciones,simbolo,nro_nc,chk
     from detalle_notas_credito where id_log_pago=".$parametros['id_log_pago'];
$notas_credito=sql($sql) or fin_pagina();


if ($notas_credito->RecordCount() > 0 ) { ?>
<!-- NOTAS DE CREDITO -->
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">
 <tr>
    <td style=<?="border:$bgcolor3;"?>  align="center" id=history colspan=5>
        <b>Notas de Credito</b>
    </td>
 </tr>
<tr id=ma>
 <td> &nbsp; </td>
 <td width="5%">  Nro </td>
 <td width="25%"> Monto </td>
 <td> Valor Dolar </td>
 <td width="70%"> Observaciones </td>
</tr>

<?
$monto_ncsel=0;
while(!$notas_credito->EOF) {
	if ($notas_credito->fields['chk'] ==1) {
	  if ($simbolo_orden=='$' && $notas_credito->fields['simbolo']=='U\$S' )	
	          $monto_ncsel+=($notas_credito->fields['monto'] * $notas_credito->fields['valor_dolar']) ;
	  elseif ($simbolo_orden=='U$S' && $notas_credito->fields['simbolo']=='$')        
	         $monto_ncsel+=($notas_credito->fields['monto'] / $notas_credito->fields['valor_dolar']) ;
	     else $monto_ncsel+=$notas_credito->fields['monto'];    
	}
	?>
  <tr >
    <td> <input type='checkbox' disabled name='chk' value="" <? if ($notas_credito->fields['chk'] !=0) echo 'checked' ?>></td>
    <td align="center"> <?=$notas_credito->fields['nro_nc'];?> </td>
    <td>
        <table>
           <tr>
            <td> <b><?=$notas_credito->fields['simbolo']?></b> </td>
            <td width="100%" align="right">  <b>-<?=formato_money($notas_credito->fields['monto']);?></b>   </td>
          </tr>
        </table>  
    </td>
    <td>
    <? 
      if ($notas_credito->fields['simbolo'] != $simbolo_orden) {
               $dolar=number_format($notas_credito->fields['valor_dolar'],"2",".","");
               $dis_dolar=''; 
        }
        else {
        	$dolar= "No se aplica";
            $dis_dolar=' disabled';      
        }
        	?>
    <input type="text" readonly size="10" name="dolar" value="<?=$dolar?>" <?=$dis_dolar?>>
   </td>
   <td>
     <?if($notas_credito->fields['observaciones'])echo $notas_credito->fields['observaciones'];else echo "&nbsp;";?>
  </td>
  </tr>
<? 
 $notas_credito->MoveNext();
}
?>  
</table>


<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center" id="table_nc">
<tr >
 <td align="center">
  <b>Monto de Notas de Credito seleccionadas <br>
                         
  <input type="text" readonly  class=text_5 name="total_nc" value="<?=$simbolo_orden." ".formato_money($monto_ncsel);?>">
 </td>
 <td align="center">
  <b>Total a pagar descontando notas de credito <br>
    <input type="text" readonly  class=text_5 name="total_sin_nc" value="<?=$simbolo_orden." ".formato_money($total - $monto_ncsel);?>">
 </td>
</tr> 
</table>

<br>
<?}?>
<br>
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">
 <tr >
    <td style=<?="border:$bgcolor3;"?>  align="center" id=history colspan=2>
       <font size=3><b>Forma de Pago</b>
    </td>
 </tr>
 <tr >
   
    <td align=right colspan="2" > <b>Cantidad de Pagos:</b>
         <select name=select_cantidad_pagos disabled>
            <option> <?=$res->fields['cant_pagos']?></option>
         </select>
    </td>
 </tr>
 <tr >
    <td colspan=2>
        <table width="100%">
         <tr>
           <td>
            <b> Titulo de la  Forma de Pago: <input type="text" name="titulo_pago"  readonly size="25" value="<?=$res->fields['titulo_forma_pago']?>">
           </td>
           <td align="rigth">
             <b> Default <input type="checkbox" disabled name="chk_default" value="1" <?if ($res->fields['mostrar']==2 || $res->fields['mostrar']==4) echo "checked"; elseif ($res->fields['mostrar']==3 || $res->fields['mostrar']==4 ) echo 'disabled';?>>
           </td>
         </tr>
       </table>
    </td>
 </tr>
 <?
 // PARA $res->fields['mostrar']
 // 1 habilitado y sin checkear 
 // 2 habilitado y checkeado
 // 3 disabled y sin checkear 
 // 4 disabled y  checkeado  
 ?>
 
 
<?$sql="select cant_dias,monto,valor_dolar,descripcion,simbolo,pagada from 
        detalle_pagos join tipo_pago using (id_tipo_pago)
        where id_log_pago=".$parametros['id_log_pago'] ." order by id_detalle_pagos";
  $res_pagos=sql($sql,"detalle_pagos") or fin_pagina();  
  
  $i=0;
 while (!$res_pagos->EOF) { 
 	if ($res_pagos->fields['pagada']==1)
 	    $permiso=" disabled";
 	else $permiso="";
 	?>
  <tr id=ma>
    <td bgcolor="#C0C0C0" colspan="2">
     <table width="100%"> 
       <tr>
        <td> <b>Pago Nro: <?=$i+1;?></td>
        <td align="right"><b><font size=-2>Recuerde no utilizar separador de miles al insertar los montos</font></b> </td>
       </tr>
     </table> 
  </tr>
  <tr >
    <td colspan=2>
      <table width="100%" align="center">
        <tr id="history">
         <td width="40%">Tipo de Pago </td>
         <td> Cantidad de Dias </td>
      	 <td> Monto </td>
         <? if($res_pagos->fields['valor_dolar'] != null )    {?>
         <td> Valor Dolar </td>
         <? } ?>
        </tr>
       <tr>
          <td align="center" width="40%">
           <select name=select_tipo_pago_<?=$i-1;?> disabled style="width:75%" <?=$permiso?>>
              <option> <?=$res_pagos->fields['descripcion']?></option>
           </select>
          </td>
          <td align="center">
           <input type="text" name="cantidad_dias_<?=$i-1;?>" readonly  size="2" value='<?=$res_pagos->fields['cant_dias']?>' <?=$permiso?>>
          </td>
          <td> <? echo  $res_pagos->fields['simbolo']; 
           if ($res_pagos->fields['monto'] != null || $res_pagos->fields['monto'] != "") {
                $monto=number_format($res_pagos->fields['monto'],'2','.','');
                if ($monto=='0.00') $monto="";
           }
                else $monto="";?>
            <input type="text" name="monto_<?=$i-1;?>"  readonly size="7" value='<?=$monto?>' <?=$permiso?> >
          </td>
         <? if ($res_pagos->fields['valor_dolar']!=null)   {?>
          <td align='center'>
           <input type="text" name="valor_dolar_<?=$i-1;?>"  readonly size="4" value='<?=number_format($res_pagos->fields['valor_dolar'],"2",".","")?>' <?=$permiso?>>
          </td>  
          <?}?>
       </tr>
      </table>	
   </td>
   </tr>   
    <?
    $i++;
    $res_pagos->MoveNext();
    }
  ?>
<tr >
<td align='center' colspan="2"><input type='button' value='Cerrar' name='Cerrar' onclick='window.close()'></td>
</tr>
</table>
</form>
<?=fin_pagina();?>