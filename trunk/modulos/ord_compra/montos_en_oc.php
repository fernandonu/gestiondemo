<?
/*
Autor: MAC
Fecha: 19/01/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.21 $
$Date: 2005/09/06 19:03:19 $
*/

require_once("../../config.php");

$fecha_desde=Fecha_db($_POST["fecha_desde"]);
$fecha_hasta=Fecha_db($_POST["fecha_hasta"]);
$estado_filter=$parametros["estado"] or $estado_filter=$_POST["estado"];

echo $html_header;

 cargar_calendario();
 ?>
 <script>
  function control_datos()
  {
   /*if(document.all.fecha_desde.value=="")
   {
    alert('Debe ingresar una Fecha Inicial para el filtro de montos adeudados');
    return false;
   }*/
   if(document.all.fecha_hasta.value=="")
   {
    alert('Debe ingresar una Fecha Final para el filtro de montos adeudados');
    return false;
   }
   return true;
  }
 </script>

<form name='form1' action="montos_en_oc.php" method="POST">
 <input type="hidden" name="estado" value="<?=$estado_filter?>">
<?

if($estado_filter=="e")
{$estado="Enviadas/Parcialmente Pagadas";
 //traemos las OC enviadas o parcialmente pagadas con sus respectivos pagos
 $query="select orden_de_compra.fecha_entrega,pago_orden.nro_orden,ordenes_pagos.id_pago,
         ordenes_pagos.monto,id_ingreso_egreso,iddébito,númeroch,simbolo,forma_de_pago.dias
 from compras.ordenes_pagos join compras.pago_orden using(id_pago) 
      join forma_de_pago using(id_forma)
      join compras.orden_de_compra using (nro_orden) join licitaciones.moneda using(id_moneda)
 where (estado='e' or estado='d')
 order by pago_orden.nro_orden";
 $oc_enviadas=sql($query,"<br>Error al traer las OC enviadas o parcialmente pagadas<br>") or fin_pagina();

 $monto_total_pesos=0;
 $monto_total_dolares=0;
 $monto_pagado_pesos=0;
 $monto_pagado_dolares=0;
 $monto_deuda_pesos=0;
 $monto_deuda_dolares=0;
 $montos_deuda_fecha_pesos=0;
 $montos_deuda_fecha_dolares=0;
 //se utilizan para las OC que se incluyen en los montos adeudados, con filtro de fecha
 $ordenes_implicadas_dolares=array();$index_d=0;
 $ordenes_implicadas_pesos=array();$index_p=0;
 //este arreglo se pone para saber cuales id_pagos se usan, asi no se repiten en la cuenta, 
 //porque pertenecen a un pago de multiple
 $id_pagos_usados=array();
 while(!$oc_enviadas->EOF)
 {
  if(!in_array($oc_enviadas->fields["id_pago"],$id_pagos_usados))
  {
   if($oc_enviadas->fields["simbolo"]=="$")	
   {$monto_total_pesos+=$oc_enviadas->fields["monto"];	
    if($oc_enviadas->fields["id_ingreso_egreso"]!="" ||$oc_enviadas->fields["iddébito"]!="" ||$oc_enviadas->fields["númeroch"]!="") 
     $monto_pagado_pesos+=$oc_enviadas->fields["monto"];	
    else //si el pago no se realiza aun, controlamos para saber si hay que incluirlo en el monto adeudado con filtro
    {
       $monto_deuda_pesos+=$oc_enviadas->fields["monto"];
       
       $f_e=explode("-",$oc_enviadas->fields["fecha_entrega"]);
       $fecha_deuda=date("Y-m-d",mktime('','','',$f_e[1],$f_e[2]+$oc_enviadas->fields["dias"],$f_e[0]));
       $result_fecha_desde=compara_fechas($fecha_deuda,$fecha_desde);
       $result_fecha_hasta=compara_fechas($fecha_deuda,$fecha_hasta);
       if(($result_fecha_desde==1||$result_fecha_desde==0) && $result_fecha_hasta==-1)
       {
       	 if(!in_array($oc_enviadas->fields["nro_orden"],$ordenes_implicadas_pesos))
       	 {$ordenes_implicadas_pesos[$index_p]=$oc_enviadas->fields["nro_orden"];
       	  $index_p++;
       	 }
       	 $montos_deuda_fecha_pesos+=$oc_enviadas->fields["monto"];       	
       }//de if($oc_enviadas->fields["fecha"]>=$fecha_desde && $oc_enviadas->fields["fecha"]>=$fecha_hasta)
    }	
     
   }
   elseif($oc_enviadas->fields["simbolo"]=="U\$S")
   {
   	$monto_total_dolares+=$oc_enviadas->fields["monto"];  

    if($oc_enviadas->fields["id_ingreso_egreso"]!="" ||$oc_enviadas->fields["iddébito"]!="" ||$oc_enviadas->fields["númeroch"]!="") 
     $monto_pagado_dolares+=$oc_enviadas->fields["monto"];
    else //si el pago no se realiza aun, controlamos para saber si hay que incluirlo en el monto adeudado con filtro
    { 
       $monto_deuda_dolares+=$oc_enviadas->fields["monto"];
          	 
       $f_e=explode("-",$oc_enviadas->fields["fecha_entrega"]);
       $fecha_deuda=date("Y-m-d",mktime('','','',$f_e[1],$f_e[2]+$oc_enviadas->fields["dias"],$f_e[0]));
       $result_fecha_desde=compara_fechas($fecha_deuda,$fecha_desde);
       $result_fecha_hasta=compara_fechas($fecha_deuda,$fecha_hasta);
       if(($result_fecha_desde==1||$result_fecha_desde==0) && $result_fecha_hasta==-1)
       {
       	 if(!in_array($oc_enviadas->fields["nro_orden"],$ordenes_implicadas_dolares))
       	 {$ordenes_implicadas_dolares[$index_d]=$oc_enviadas->fields["nro_orden"];
       	  $index_d++;
       	 } 
       	 $montos_deuda_fecha_dolares+=$oc_enviadas->fields["monto"];       	
       }//de if($oc_enviadas->fields["fecha"]>=$fecha_desde && $oc_enviadas->fields["fecha"]>=$fecha_hasta) 
    }  
   }
   else 
    die("La OC ".$oc_enviadas->fields["nro_orden"]." NO tiene especificada la moneda");
   $id_pagos_usados[sizeof($id_pagos_usados)]=$oc_enviadas->fields["id_pago"];
  }//de if(!in_array($oc_enviadas->fields["id_pago"],$id_pagos_usados))

  $oc_enviadas->MoveNext();
 }//de while(!$oc_enviadas->EOF)
 
?>
 <table align="center" width="95%" cellpadding="8">
  <tr id=mo>
   <td colspan="3">
    Montos de Ordenes de Compra en estado <?=$estado?>
   </td>
  </tr>
  <tr id=ma>
   <td>
    Monto Total
   </td>
   <td>
    U$S <?=number_format($monto_total_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($monto_total_pesos,2,',','.')?>
   </td>
  </tr>
  <tr id=ma>
   <td>
    Monto Pagado
   </td>
   <td>
    U$S <?=number_format($monto_pagado_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($monto_pagado_pesos,2,',','.')?>
   </td>
  </tr>
  </tr>
  <tr id=ma>
   <td>
    Monto Adeudado
   </td>
   <td>
    U$S <?=number_format($monto_deuda_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($monto_deuda_pesos,2,',','.')?>
   </td>
  </tr>
  <tr id=ma>
   <td>
    Monto Adeudado:<br>
    desde 
    <input type="text" name="fecha_desde" value="<?=Fecha($fecha_desde)?>" size="10" readonly><?=link_calendario("fecha_desde")?>
    <br>
    hasta 
    <input type="text" name="fecha_hasta" value="<?=Fecha($fecha_hasta)?>" size="10" readonly><?=link_calendario("fecha_hasta")?>
   </td>
   <td>
     <?
    $string_oc_dolares=implode(',',$ordenes_implicadas_dolares);
    $link_ver_oc=encode_link("montos_en_oc_detalle.php",array("string_oc"=>$string_oc_dolares,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta));
    if($fecha_hasta)
    {?>
     <a href='<?=$link_ver_oc?>' target="_blank">U$S <?=number_format($montos_deuda_fecha_dolares,2,',','.')?></a>
    <?
    }
    else  
    {
     ?> 
     U$S <?=number_format($montos_deuda_fecha_dolares,2,',','.')?>
    <?
    }
    ?> 
   </td>
   <td>
    <?
    $string_oc_pesos=implode(',',$ordenes_implicadas_pesos);
    $link_ver_oc=encode_link("montos_en_oc_detalle.php",array("string_oc"=>$string_oc_pesos,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta));
    if($fecha_hasta)
    {?>
     <a href='<?=$link_ver_oc?>' target="_blank">$ <?=number_format($montos_deuda_fecha_pesos,2,',','.')?></a>
    <?
    }
    else
    {?> 
     $ <?=number_format($montos_deuda_fecha_pesos,2,',','.')?>
    <?
    }
    ?> 
   </td>
  </tr>
 </table> 
 <div align="center">
  <input type="submit" name="cambiar" value="Cambiar Fechas" onclick="return control_datos();">
  &nbsp;
  <input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
 </div> 
 <?
}//de if($parametros["estado"]=="e")
/*elseif($estado_filter=="g")
{$estado="Parcialmente Pagadas/Totalmente Pagadas";
 //traemos las OC parcial o totalmente pagadas con sus respectivos pagos
 $query="select pago_orden.nro_orden,ordenes_pagos.id_pago,
         ordenes_pagos.monto,id_ingreso_egreso,iddébito,númeroch,simbolo
 from compras.ordenes_pagos join compras.pago_orden using(id_pago) 
      join compras.orden_de_compra using (nro_orden) join licitaciones.moneda using(id_moneda)
 where (estado='g' or estado='d')
 order by pago_orden.nro_orden";
 $oc_enviadas=sql($query,"<br>Error al traer las OC enviadas o parcialmente pagadas<br>") or fin_pagina();

 $monto_total_pesos=0;
 $monto_total_dolares=0;
 $monto_pagado_pesos=0;
 $monto_pagado_dolares=0;
 $monto_deuda_pesos=0;
 $monto_deuda_dolares=0;
 //este arreglo se pone para saber cuales id_pagos se usan, asi no se repiten en la cuenta, 
 //porque pertenecen a un pago de multiple
 $id_pagos_usados=array();
 while(!$oc_enviadas->EOF)
 {
  if(!in_array($oc_enviadas->fields["id_pago"],$id_pagos_usados))
  {
   if($oc_enviadas->fields["simbolo"]=="$")	
   {$monto_total_pesos+=$oc_enviadas->fields["monto"];	
    if($oc_enviadas->fields["id_ingreso_egreso"]!="" ||$oc_enviadas->fields["iddébito"]!="" ||$oc_enviadas->fields["númeroch"]!="") 
     $monto_pagado_pesos+=$oc_enviadas->fields["monto"];	
    else //si el pago no se realiza aun, controlamos para saber si hay que incluirlo en el monto adeudado con filtro
    {
       $monto_deuda_pesos+=$oc_enviadas->fields["monto"];
       
       $f_e=explode("-",$oc_enviadas->fields["fecha_entrega"]);
       $fecha_deuda=date("Y-m-d",mktime('','','',$f_e[1],$f_e[2]+$oc_enviadas->fields["dias"],$f_e[0]));
       $result_fecha_desde=compara_fechas($fecha_deuda,$fecha_desde);
       $result_fecha_hasta=compara_fechas($fecha_deuda,$fecha_hasta);
       if(($result_fecha_desde==1||$result_fecha_desde==0) && $result_fecha_hasta==-1)
       {
       	 $montos_deuda_fecha_pesos+=$oc_enviadas->fields["monto"];       	
       }//de if($oc_enviadas->fields["fecha"]>=$fecha_desde && $oc_enviadas->fields["fecha"]>=$fecha_hasta)
    }
     
   }
   elseif($oc_enviadas->fields["simbolo"]=="U\$S")
   {
   	$monto_total_dolares+=$oc_enviadas->fields["monto"];  

    if($oc_enviadas->fields["id_ingreso_egreso"]!="" ||$oc_enviadas->fields["iddébito"]!="" ||$oc_enviadas->fields["númeroch"]!="") 
     $monto_pagado_dolares+=$oc_enviadas->fields["monto"];
    else //si el pago no se realiza aun, controlamos para saber si hay que incluirlo en el monto adeudado con filtro
    { 
       $monto_deuda_dolares+=$oc_enviadas->fields["monto"];
          	 
       $f_e=explode("-",$oc_enviadas->fields["fecha_entrega"]);
       $fecha_deuda=date("Y-m-d",mktime('','','',$f_e[1],$f_e[2]+$oc_enviadas->fields["dias"],$f_e[0]));
       $result_fecha_desde=compara_fechas($fecha_deuda,$fecha_desde);
       $result_fecha_hasta=compara_fechas($fecha_deuda,$fecha_hasta);
       if(($result_fecha_desde==1||$result_fecha_desde==0) && $result_fecha_hasta==-1)
       {
       	 $montos_deuda_fecha_dolares+=$oc_enviadas->fields["monto"];       	
       }//de if($oc_enviadas->fields["fecha"]>=$fecha_desde && $oc_enviadas->fields["fecha"]>=$fecha_hasta) 
    } 
   }
   else 
    die("La OC ".$oc_enviadas->fields["nro_orden"]." NO tiene especificada la moneda");
   $id_pagos_usados[sizeof($id_pagos_usados)]=$oc_enviadas->fields["id_pago"];
  }//de if(!in_array($oc_enviadas->fields["id_pago"],$id_pagos_usados))

  $oc_enviadas->MoveNext();
 }//de while(!$oc_enviadas->EOF)
 
 ?>
 <table align="center" width="95%" cellpadding="8">
  <tr id=mo>
   <td colspan="3">
    Montos de Ordenes de Compra en estado <?=$estado?>
   </td>
  </tr>
  <tr id=ma>
   <td>
    Monto Total
   </td>
   <td>
    U$S <?=number_format($monto_total_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($monto_total_pesos,2,',','.')?>
   </td>
  </tr>
  <tr id=ma>
   <td>
    Monto Pagado
   </td>
   <td>
    U$S <?=number_format($monto_pagado_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($monto_pagado_pesos,2,',','.')?>
   </td>
  </tr>
  </tr>
  <tr id=ma>
   <td>
    Monto Adeudado
   </td>
   <td>
    U$S <?=number_format($monto_deuda_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($monto_deuda_pesos,2,',','.')?>
   </td>
  </tr>
  <tr id=ma>
   <td>
    Monto Adeudado:<br>
    desde 
    <input type="text" name="fecha_desde" value="<?=Fecha($fecha_desde)?>" size="10" readonly><?=link_calendario("fecha_desde")?>
    <br>
    hasta 
    <input type="text" name="fecha_hasta" value="<?=Fecha($fecha_hasta)?>" size="10" readonly><?=link_calendario("fecha_hasta")?>
   </td>
   <td>
    U$S <?=number_format($montos_deuda_fecha_dolares,2,',','.')?>
   </td>
   <td>
    $ <?=number_format($montos_deuda_fecha_pesos,2,',','.')?>
   </td>
  </tr>
 </table> 
 <div align="center">
  <input type="submit" name="cambiar" value="Cambiar Fechas" onclick="return control_datos();">
  &nbsp;
  <input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
 </div> 
 <?
}//de elseif($estado_filter=="g")*/
else 
 echo "Se perdió el estado....";
?>
</form>
</body>
</html>