<?
include("../../config.php");
$id_legajo=$parametros['id_legajo'];
$id_sueldo=$parametros['id_sueldo'];
$fecha_actual=date("Y-m-d",mktime());
$fecha_anterior=$parametros['fecha_anterior'];
$fecha_control=fecha_db("01/04/2005");

function cambiar ($m){
switch ($m){
case 1: {$cambio="Enero";
         break;
         }
case 2: {$cambio="Febrero";
         break;
         }
case 3: {$cambio="Marzo";
         break;
         }
case 4: {$cambio="Abril";
         break;
         }
case 5: {$cambio="Mayo";
         break;
         }
case 6: {$cambio="Junio";
         break;
         }
case 7: {$cambio="Julio";
         break;
         }
case 8: {$cambio="Agosto";
         break;
         }
case 9: {$cambio="Septiembre";
         break;
         }
case 10: {$cambio="Octubre";
         break;
         }
case 11: {$cambio="Noviembre";
         break;
         }
case 12: {$cambio="Diciembre";
         break;
         }
   }
return $cambio;
}

function setea_datos($dato){
  if($dato!="") $return=formato_money($dato);
  //$return=number_format($dato,2,".","");
  return $return;
}

function calculo_valores($resultado){
global	$fecha_pago, $fecha_control;
//echo $fecha_control." ---- ".$fecha_pago;
  if ($fecha_pago>$fecha_control) 	
     $subtotal=$resultado->fields['basico']+$resultado->fields['dec1529']+$resultado->fields['dec1347'];
  else 
     $subtotal=$resultado->fields['basico']+$resultado->fields['dec1529'] ;
  $subtotal2=$resultado->fields['presentismo']+$subtotal+$resultado->fields['acuenta']+ $resultado->fields["ajuste_anterior"];
  
  if ($resultado->fields["cantidad_dias_feriados"])
                               {
                                $feriados=($subtotal2/25)*$resultado->fields["cantidad_dias_feriados"];
                                $subtotal2=$subtotal2+$feriados;
                                }
                                else 
                                $feriados=0;

  
  //$monto=($subtotal2/30)*$resultado->fields['vacaciones_dias'];
  $total=$subtotal2+$resultado->fields['vacaciones']+$resultado->fields['ausentismo'];
  //esto es para calcular el sac
  $mes_sac=$resultado->fields['mes_sac'];
  $dias_sac=$resultado->fields['dias_sac'];
  $mxd_sac=$mes_sac*30;
  $dias_trab=$mxd_sac+$dias_sac;
  $arreglo['sac']=(($subtotal2/2)/180)*$dias_trab;
  //este muestra el total del sac
  $total-=$resultado->fields['total_inasistencia'];
  $total+=$resultado->fields['total_vac'];
  $total_desc=$resultado->fields['jubilacion']+$resultado->fields['ley19032'];
  $total_desc+=$resultado->fields['obra_social']+$resultado->fields['sindicato'];
  $total_desc+=$resultado->fields['sindicato_familiar']+$resultado->fields['faecys'];
  if ($fecha_pago>$fecha_control)
     $total_no_rem=$resultado->fields['salario_familiar']+$resultado->fields['ayuda_escolar'];
  else
     $total_no_rem=$resultado->fields['salario_familiar']+$resultado->fields['dec1347']+$resultado->fields['ayuda_escolar'];
  $total_no_rem+=$resultado->fields['gratificacion']+$resultado->fields['vac_no_gozadas'];
  $total_no_rem+=$resultado->fields['pre_aviso']+$resultado->fields['indemnizacion']+$resultado->fields['sac_sobre_vng'];
   $total_no_rem+=$resultado->fields['sac_sobre_preaviso']+$resultado->fields['dec2005_04']+$resultado->fields['sac_sobre_indemnizacion'];
  $neto=$total-$total_desc+$total_no_rem;
  //tengo que agregarle lo del sac que si no se marca es cero
  $neto=$neto+$arreglo['sac'];
  $arreglo['subtotal']=$subtotal;
  $arreglo['subtotal2']=$subtotal2;
  $arreglo['total']=$total+$arreglo['sac'] ;
  $arreglo['total_desc']=$total_desc;
  $arreglo['total_no_rem']=$total_no_rem;
  $anticipo=$resultado->fields['anticipo'];
  if ($anticipo) $arreglo['neto']=$neto-$anticipo;
  else $arreglo['neto']=$neto;
  return $arreglo;
}

$sql="select * from legajos join sueldos using (id_legajo)";
$sql.=" join tareas_desemp using (id_tarea)";
$sql.=" join calificacion using (id_calificacion)";
$sql.=" left join tipo_banco using (idbanco)";
$sql.=" left join afjp using (id_afjp)";
$sql.=" where id_sueldo=$id_sueldo";
$result=$db->execute($sql) or die($sql);
$apellido=$result->fields['apellido'];
$nombre=$result->fields['nombre'];
$fecha=$result->fields['fecha'];
$fecha_ingreso=$result->fields['fecha_ingreso'];
$fecha_pago=$result->fields['fecha_pago'];
$fecha_jubilacion=$result->fields['fecha_jubilacion'];
$cuil=$result->fields['cuil'];
$afjp=$result->fields['nombre_afjp'];
$caja_ahorro=$result->fields ['caja_ahorro_pesos_nro'];
$calificacion=$result->fields['nombre_calificacion'];
$tarea=$result->fields['nombre_tarea'];
$banco=$result->fields['nombrebanco'];
$basico=$result->fields['basico'];
$dec1529=$result->fields['dec1529'];
$acuenta=$result->fields['acuenta'];
$presentismo=$result->fields['presentismo'];
$vacaciones_dias=$result->fields['vacaciones_dias'];
$vacaciones=$result->fields['vacaciones'];
$jubilacion=$result->fields['jubilacion'];
$obra_social=$result->fields['obra_social'];
$ley19032=$result->fields['ley19032'];
$sindicato=$result->fields['sindicato'];
$sindicato_familiar=$result->fields['sindicato_familiar'];
$dec1347=$result->fields['dec1347'];
$salario_familiar=$result->fields['salario_familiar'];
$faecys=$result->fields['faecys'];
$estado=$result->fields['estado_liquidacion'];
$ausentismo=$result->fields['ausentismo'];
$ayuda=$result->fields['ayuda_escolar'];
$gratificacion=$result->fields['gratificacion'];
$indemnizacion=$result->fields['indemnizacion'];
$pre_aviso=$result->fields['pre_aviso'];
$vac_ng=$result->fields['vac_no_gozadas'];
$sac_sobre_vng=$result->fields['sac_sobre_vng'];
$sac_indemnizacion=$result->fields['sac_sobre_indemnizacion'];
//estas variables solo las uso cuando se liquida un sueldo a alguien que no trabaja mas
$dias_inasistencia=$result->fields['dias_inasistencia'];
$total_inasistencia=$result->fields['total_inasistencia'];
$total_vac=$result->fields['total_vac'];
$vac=$result->fields['dias_vac'];
/////////////
$sac=$result->fields['sac'];
$sac_preaviso=$result->fields['sac_sobre_preaviso'];
$dec2005_04=$result->fields['dec2005_04'];
$fecha_pago=$result->fields['fecha_pago'];
 $anticipo=$result->fields['anticipo'];
 $ajuste_anterior=$result->fields["ajuste_anterior"];
 $feriados=$result->fields["feriados"];  
 
$arreglo=calculo_valores($result);



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<script language="javascript">

function imprimir(){
 document.all.imprimir.style.visibility="hidden";
 window.print();
 window.close();
}

</script>
<html>
<head>
<title>liquidacion de sueldo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
<!-- este estilo es el borde izq y der -->
.estilo2 {
    border-right-width: 1px;
    border-left-width: 1px;
    border-right-style: solid;
    border-left-style: none;
    border-right-color: #000000;
    border-left-color: #000000;
}
<!-- este estilo es el borde de afuera de la tabla -->
.estilo3 {
    border-top-width: 1px;
    border-right-width: 1px;
    border-bottom-width: 1px;
    border-left-width: 1px;
    border-right-style: solid;
    border-bottom-style: solid;
    border-left-style: solid;
    border-top-color: #000000;
    border-right-color: #000000;
    border-bottom-color: #000000;
    border-left-color: #000000;
}
.estilo4 {
    border-top-width: 1px;
    border-top-style: solid;
    border-top-color: #000000;
    border-right-color: #000000;
    border-bottom-color: #000000;
    border-left-color: #000000;
}
.estilo5 {
    border-top-width: 1px;
    border-right-width: 1px;
    border-bottom-width: 1px;
    border-left-width: 1px;
    border-bottom-style: solid;
    border-top-color: #000000;
    border-right-color: #000000;
    border-bottom-color: #000000;
    border-left-color: #000000;
    border-right-style: solid;
}
.estilo6 {
    border-top-width: 1px;
    border-right-width: 1px;
    border-bottom-width: 1px;
    border-left-width: 1px;
    border-bottom-style: solid;
    border-top-color: #000000;
    border-right-color: #000000;
    border-bottom-color: #000000;
    border-left-color: #000000;
}
.Estilo7 {color: #FF0000}
.estilo1 {
    border: 1px solid #000000;
}

-->
</style>
</head>
<body>
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<table width="100%"  border="1" bordercolor="#000000">
 <tr>
 <td>
 <table width="80%"  border="0" align="center">
   <tr>
    <td><strong>CORADIR S.A.</strong></td>
    <td>San Martin 454</td>
   </tr>
   <tr>
    <td>CUIT: 30-67338016-2</td>
    <td>San Luis</td>
   </tr>
 </table>
 </td>
 <td>
 <table width="74%"  border="0" align="center">
   <tr>
    <td><strong>Original para la Empresa</strong></td>
   </tr>
   <tr>
    <td>Legajo Nº <input name="legajo" type="text" readonly=true size="10" class=text_6 value="<?=$id_legajo;?>"> </td>
   </tr>
 </table>
 </td>
 </tr>
 <tr>
  <td colspan="2">
  <table width="100%"  border="0">
   <tr><td colspan="6">&nbsp;</td></tr>
   <tr>
    <td> <!-- tabla para datos personales -->
    <table width="100%" cellpadding="0" cellspacing="0" class="estilo1">
     <tr>
      <td class="estilo2" align="center">Apellido y Nombre</td>
      <td class="estilo2" align="center">Fecha Ingreso</td>
      <td class="estilo2" align="center">CUIL</td>
      <td class="estilo2" align="center">Caja de Ahorro Nº</td>
      <td class="estilo2" align="center">AFJP</td>
      <td align="center">Remuneración Básica</td>
     </tr>
     <tr>
      <td class="estilo2" align="center" width="20%"><b><?=$apellido." ".$nombre;?></b>
      <? /*<input name="nombre_apell" type="text" size="25" class=text_6 readonly="true" value="<?=$apellido." ".$nombre;?>">*/
      ?>
      </td>
      <td class="estilo2" align="center"><input name="fecha_ing" type="text" size="15" class=text_6 readonly="true" value="<?=Fecha($fecha_ingreso);?>"></td>
      <td class="estilo2" align="center"><input name="cuil" type="text" size="15" class=text_6 readonly="true" value="<?=$cuil;?>"></td>
      <td class="estilo2" align="center"><input name="caja_ahorro" type="text" class=text_6 readonly="true" size="15" value="<?=$caja_ahorro;?>"></td>
      <td class="estilo2" align="center"><input name="afjp" type="text" class=text_6 readonly="true" size="10" value="<?=$afjp;?>"></td>
      <td align="center"><input name="remuneracion" type="text" readonly="true" size="10" class=text_6 value="<?=setea_datos($basico);?>"></td>
     </tr>
    </table>
    </td> <!-- fin tabla datos personales-->
   </tr>
   <tr><td colspan="6">&nbsp;</td></tr>
   <tr>
    <td> <!-- tabla para datos 1 -->
     <table width="100%" class="estilo1" cellpadding="0" cellspacing="0">
      <tr>
       <td class="estilo2" align="center">Período Depositado</td>
       <td class="estilo2" align="center">Banco Depósito</td>
       <td class="estilo2" align="center">Fecha Ultimo Depósito</td>
       <td align="center">Calificación Profesional</td>
      </tr>
      <tr>
      <? list($año, $mes, $dia)=split('[-]', $fecha_jubilacion);
         if ($mes==1) {
            $m=cambiar(12);
            $año--;}
         else $m=cambiar($mes-1); ?>
       <td class="estilo5" align="center"><input name="per_depo" type="text" class=text_6 readonly="true" size="20" value="<?=$m." / ".$año;?>"></td>
       <td class="estilo5" align="center"><input name="banco" type="text" class=text_6 readonly="true" size="30" value="<?=$banco;?>"></td>
       <td class="estilo5" align="center"><input name="fecha_anterior" type="text" class=text_6 readonly="true" size="15" value="<?=Fecha($fecha_jubilacion);?>"></td>
       <td class="estilo6" align="center"><input name="calificacion" type="text" size="30" class=text_6 readonly="true" value="<?=$calificacion;?>"></td>
      </tr>
      <tr>
       <td class="estilo2" align="center">Período Abonado</td>
       <td class="estilo2" align="center">Domicilio Pago</td>
       <td class="estilo2" align="center">Fecha Pago</td>
       <td align="center">Tarea Desempeñada</td>
      </tr>
      <tr>
      <? list($año_pago, $mes_pago, $dia_pago)=split('[-]', $fecha);
         $m_pago=cambiar($mes_pago); ?>
       <td class="estilo2" align="center"><input name="per_abonado" type="text" class=text_6 readonly="true" size="20" value="<?=$m_pago." / ".$año_pago;?>"></td>
       <td class="estilo2" align="center"><input name="dom_pago" type="text" size="30" class=text_6 readonly=true value="San Martín 454"></td>
       <td class="estilo2" align="center"><input name="fecha_pago" type="text" class=text_6 readonly="true" size="15" value="<?=Fecha($fecha_pago);?>"></td>
       <td align="center"><input name="tarea" type="text" size="30" class=text_6 readonly="true" value="<?=$tarea;?>"></td>
      </tr>
    </table>
    </td> <!-- fin tabla datos 1 -->
   </tr>
   <tr><td colspan="6">&nbsp;</td></tr>
   <tr>
    <td> <!-- tabla para liquidacion -->
    <table width="100%"  border="0" class="estilo1" cellpadding="0" cellspacing="0">
      <tr bordercolor="#000000">
       <td width="20%" align="center" class="estilo5"><strong>Descripción de Conceptos</strong></td>
       <td width="14%" align="center" class="estilo5"><strong>Unidades</strong></td>
       <td width="15%" align="center" class="estilo5"><strong>V. Unitario</strong></td>
       <td width="16%" align="center" class="estilo5"><strong>Remuneraciones</strong></td>
       <td width="15%" align="center" class="estilo5"><strong>Descuentos</strong></td>
       <td width="16%" align="center" class="estilo6"><strong>Conceptos no Remunerativos</strong></td>
     </tr>
     <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Sueldo Básico </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($basico);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <? if ($dec1529!=0) {?>
     <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Decreto 1529 </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($dec1529);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <? } ?>
    <? if ($acuenta!=0) {?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;A Cta. Futuros Ajustes</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($acuenta);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <? } ?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Presentismo</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($presentismo);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? if ($sac!=0) { ?>   
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;S.A.C.</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($arreglo['sac']);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? } ?>      
<? if ($feriados!=0) { ?>   
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Feriados</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($feriados);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? } ?>     
<? if ($vacaciones_dias!=0) { ?>
     <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Vacaciones Gozadas</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($vacaciones);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? } if ($ausentismo!=0) { ?>
     <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Ausentismo</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($ausentismo);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? } ?>
<? if ($dias_inasistencia!=0) { ?>
     <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Inasistencias</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($total_inasistencia);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? } ?>
<? if ($vac!=0) { ?>
     <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Vacaciones</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($total_vac);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? } ?>
<? if ($fecha_pago>$fecha_control) {  
   if ($dec1347!=0) { ?> 
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Decreto 1347/03</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($dec1347);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
<? }
}
if ($ajuste_anterior!=0) {
?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Ajuste Anterior</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($ajuste_anterior);?></td>
      <td class="estilo2">&nbsp;</td>

      <td>&nbsp;</td>
    </tr>
<?
}
?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Jubilaci&oacute;n</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($jubilacion);?></td>
      <td>&nbsp;</td>
    </tr>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Ley 19032 </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($ley19032);?></td>
      <td>&nbsp;</td>
    </tr>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Obra Social</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($obra_social);?></td>
      <td>&nbsp;</td>
    </tr>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Sindicato</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($sindicato);?></td>
      <td>&nbsp;</td>
    </tr>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Faecys</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($faecys);?></td>
      <td>&nbsp;</td>
    </tr>
    <?if ($sindicato_familiar!=0) {?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Sindicato Familiar </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($sindicato_familiar);?></td>
      <td>&nbsp;</td>
    </tr>
    <? } 
    if ($salario_familiar!=0) {?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Salario Familiar </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($salario_familiar);?></td>
    </tr>
    <?}?>
    <? if ($fecha_pago<$fecha_control) {  
   if ($dec1347!=0) { ?> 
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Decreto 1347/03</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2" align="right"><?=setea_datos($dec1347);?></td>
      <td class="estilo2">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <? }
      } ?>
    <? if ($dec2005_04!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Decreto 2005/04 </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($dec2005_04);?></td>
    </tr>
    <?}?>
     <? if ($ayuda!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Ayuda Escolar </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($ayuda);?></td>
    </tr>
    <?}?>
<!--aca va lo de gratificacion--> 
    <? if ($gratificacion!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Gratificación</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($gratificacion);?></td>
    </tr>
    <?}?>
<!--aca va lo de vacaciones no gozadas-->
    <? if ($vac_ng!=0) {?>      
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Vacaciones no Gozadas </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($vac_ng);?></td>
    </tr>
    <?}?>
    <? if ($vac_ng!=0) {?>      
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Sac Vacaciones no Gozadas </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($sac_sobre_vng);?></td>
    </tr>
    <?}?>    
<!-- incluyo indemnizacion y pre aviso--> 
<? if ($indemnizacion!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Indemnización</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($indemnizacion);?></td>
    </tr>
    <?}
    if ($sac_indemnizacion!=0) {?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Sac Indemnización </td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($sac_indemnizacion);?></td>
    </tr>
   <?  }
   if ($pre_aviso!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Pre Aviso</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($pre_aviso);?></td>
    </tr>
    <?}?> 
<? if ($pre_aviso!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Sac Pre Aviso</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($sac_preaviso);?></td>
    </tr>
    <?}?>  
<? if ($anticipo!=0){?>
    <tr bordercolor="#FFFFFF">
      <td align="left" class="estilo2">&nbsp;Anticipos</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td class="estilo2">&nbsp;</td>
      <td align="right"><?=setea_datos($anticipo);?></td>
    </tr>
    <?}?>           
    <tr bordercolor="#000000">
      <td align="center" class="estilo4"><strong> Observaciones </strong></td>
      <td colspan="5" class="estilo4">&nbsp;</td>
    </tr>
   </table>    <!-- tabla para totales y firma -->
    <table width="100%"  bordercolor="#000000">
       <tr><td colspan="6">&nbsp;</td></tr>
       <tr>
        <td width="20%" class="estilo1" align="center"><strong>Firma Empleado</strong></td>
        <td width="14%">&nbsp;</td>
        <td width="15%" class="estilo1" align="center"><strong>Subtotal</strong></td>
        <td width="16%" class="estilo1" align="right"><?=setea_datos($arreglo['total']);?></td>
        <td width="15%" class="estilo1" align="right"><?=setea_datos($arreglo['total_desc']);?></td>
        <td width="16%" class="estilo1" align="right"><?=setea_datos($arreglo['total_no_rem']);?></td>
      </tr>
      <tr>
        <td rowspan="2" width="20%" class="estilo1">&nbsp;</td>
        <td colspan="3">&nbsp;</td>
        <td class="estilo1" align="right"><strong>Total Neto</strong></td>
        <td class="estilo1" align="right"><?=setea_datos($arreglo['neto']);?></td>
      </tr>
      <tr>
      <?
      $importe=number_format($arreglo['neto'],"2",".","");
      list($p_entera, $p_decimal)=split('[\.]', $importe);
      $letras=NumerosALetras($p_entera);
      ?>
        <td colspan="5">Recib&iacute; conforme la suma de pesos <b><?=$letras." con ".$p_decimal."/100";?></b> en concepto de mis haberes correspondientes arriba indicado y seg&uacute;n la presente liquidaci&oacute;n, dejando constancia de haber recibido un duplicado de este recibo </td>
      </tr>
    </table></td> <!-- fin tabla liquidacion -->
   </tr>
  </table>
  </td>
 </tr>
</table>
<table>
  <tr>
    <td> <input type="button" name="imprimir" value="Imprimir recibo" onclick="imprimir()"></td>
  </tr>
</table>
</body>
</html>