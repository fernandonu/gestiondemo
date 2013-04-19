<?
/*
$Author: mari $
$Revision: 1.1 $
$Date: 2006/01/13 22:04:12 $
*/

if ($_GET['first']==1) {?>
<html>
<head>
</head>
<body>
<form name="form1" action="egresos_atadas.php" method="POST">
 <input type="hidden" name="iva" value="">
 <input type="hidden" name="ganancia" value="">
 <input type="hidden" name="rib" value="">
 <input type="hidden" name="suss" value="">
 <input type="hidden" name="multas" value="">
 <input type="hidden" name="deposito" value="">
 <input type="hidden" name="otro" value="">
 <input type="hidden" name="diferido" value="">
  
 <input type="hidden" name="datos_vta" value="">
 <input type="hidden" name="monto" value="">  <!--monto del pago parcial-->
 <input type="hidden" name="simbolo_ingreso" value="">
 <input type="hidden" name="id_vta_atada" value="">
 <input type="hidden" name="id_moneda" value="">
 <input type="hidden" name="moneda_factura" value="">
 <input type="hidden" name="dolar_ingreso" value="">
 <input type="hidden" name="monto_factura" value="">  <!--monto total -->
</form>
<script>

if(window.opener.document.all.chk_iva.checked==true)
 document.all.iva.value=window.opener.document.all.iva.value; 
if(window.opener.document.all.chk_gan.checked==true) 
 document.all.ganancia.value=window.opener.document.all.ganancia.value;
if(window.opener.document.all.chk_rib.checked==true)
 document.all.rib.value=window.opener.document.all.rib.value;
if(window.opener.document.all.chk_suss.checked==true)
 document.all.suss.value=window.opener.document.all.suss.value;
if(window.opener.document.all.chk_mul.checked==true)
 document.all.multas.value=window.opener.document.all.multas.value;
if(window.opener.document.all.chk_dep.checked==true)
 document.all.deposito.value=window.opener.document.all.deposito.value;
if(window.opener.document.all.chk_otro.checked==true)
 document.all.otro.value=window.opener.document.all.otro.value;
if(window.opener.document.all.chk_diferido.checked==true)
 document.all.diferido.value=window.opener.document.all.diferido.value;

 
document.all.datos_vta.value=window.opener.document.all.datos_vta.value; 
document.all.monto.value=window.opener.document.all.monto.value;
document.all.simbolo_ingreso.value=window.opener.document.all.simbolo_ingreso.value;
document.all.monto_factura.value=window.opener.document.all.monto_factura.value;
document.all.id_vta_atada.value=window.opener.document.all.id_vta_atada.value;
document.all.id_moneda.value=window.opener.document.all.id_moneda.value;
document.all.moneda_factura.value=window.opener.document.all.moneda_factura.value;
document.all.dolar_ingreso.value=window.opener.document.all.dolar_ingreso.value;

document.form1.submit();
</script>
</body>
</html>
<?}
else {  //datos en post
require_once("../../config.php");

echo $html_header;

$datos_vta=descomprimir_variable($_POST['datos_vta']);
$cantidad_fact=count($datos_vta);
$monto_pago_parcial=$_POST['monto']; 
$simbolo=$_POST['simbolo_ingreso'];
$monto_factura=$_POST['monto_factura'];
$id_vta_atada=$_POST['id_vta_atada'];
$id_moneda=$_POST['id_moneda'];
$moneda_factura=$_POST['moneda_factura'];
$dolar_ingreso=$_POST['dolar_ingreso'];

$detalles=array();
$i=0;
if ($_POST['iva'])  {
  $detalles[$i]['nombre']='IVA';
  $detalles[$i]['monto_detalles']=$_POST['iva'];
  $i++;
}
if ($_POST['ganancia']) { 
  $detalles[$i]['nombre']='GANANCIA';
  $detalles[$i]['monto_detalles']=$_POST['ganancia'];
  $i++;
}
if ($_POST['rib']) {
 $detalles[$i]['nombre']='RIB';
 $detalles[$i]['monto_detalles']=$_POST['rib'];
 $i++;
}
if ($_POST['suss'])  { 
  $detalles[$i]['nombre']='SUSS';
  $detalles[$i]['monto_detalles']=$_POST['suss'];
  $i++;
}
if ($_POST['multas']) {
  $detalles[$i]['nombre']='MULTAS';
  $detalles[$i]['monto_detalles']=$_POST['multas'];
  $i++;
}
if ($_POST['deposito']) { 
  $detalles[$i]['nombre']='DEPOSITO';
  $detalles[$i]['monto_detalles']=$_POST['deposito'];
  $i++;
}
if ($_POST['otro']) {
  $detalles[$i]['nombre']='OTROS';
  $detalles[$i]['monto_detalles']=$_POST['otro'];
  $i++;
}
if ($_POST['diferido']) {
  $detalles[$i]['nombre']='DIFERIDO';
  $detalles[$i]['monto_detalles']=$_POST['diferido'];
  $i++;
}  

  
$cant_detalle=count($detalles);
$colspan=$cant_detalle+1;

?>
<script>
function control_datos(pagado,monto_total) {

var cant_detalle=document.all.cantidad_detalle.value;
var cant_factura=document.all.cantidad_factura.value;
var simbolo=document.all.simbolo.value;
var id_moneda=document.all.id_moneda.value;
var moneda_factura=document.all.moneda_factura.value;

var i,j,sum;
var msg="";
sumatotal=0;
for (j=0;j<cant_detalle;j++) {
 sum=0
  for (i=0;i<cant_factura;i++) {
    var monto=eval("document.all.monto_"+i+"_"+j);
    var desc=eval ("document.all.desc_"+j);
      if (isNaN(monto.value)) {
      alert ("Ingrese número valido para el monto del Egreso");
    return false;
    }
   else {
   	   if (monto.value!="")
   	     sum+=parseFloat(monto.value);
   	     
   }
  }
  sumatotal+=sum;
   t= new String(sum.toFixed(2));
  msg+="La suma para "+ desc.value +"  es "+ simbolo+" "+ t + "\n";
}

s= new String(sumatotal.toFixed(2));
if (typeof(s) != 'undefined') {
p=new String(pagado.toFixed(2));
m=new String(monto_total.toFixed(2));

if (moneda_factura==2 && id_moneda==1) {
   var dolar=parseFloat(document.all.dolar_ingreso.value);
   dol=new String(dolar.toFixed(2));
   suma=(parseFloat(s) / dol)+ parseFloat(p);
}
else if (moneda_factura==1 && id_moneda==2) {
   var dolar=parseFloat(document.all.dolar_ingreso.value);
   dol=new String(dolar.toFixed(2));
   suma=(parseFloat(s) * dol)+ parseFloat(p);
}
else suma=parseFloat(s)+parseFloat(p);


total=new String(suma.toFixed(2));

if ( total - m > 0.03 ) {
 alert ("ERROR: La suma de los egresos supera el monto de las facturas");
 return false;
}
}
var entrar = confirm( msg + "\n¿Confirma los egresos?");
if ( entrar ){ 
	 	return true;
	 }
	 else{
	 	return false;
	 }

}


function cargar_datos() {
var cant_detalle=document.all.cantidad_detalle.value;
var cant_factura=document.all.cantidad_factura.value;
for (j=0;j<cant_detalle;j++) {
  var desc=eval ("document.all.desc_"+j);	
  for (i=0;i<cant_factura;i++) {
    var monto=eval("document.all.monto_"+i+"_"+j);
    var nro=eval ("document.all.nro_"+i);	
    var m=eval ("window.opener.document.all.montos_"+desc.value+"_"+nro.value); 
    if (monto.value!="")
      m.value=monto.value;
    else m.value="";
  }
}
window.opener.document.all.boton_aceptar.value=1;
}
</script>


<form name='form1' method="post" action="egresos_atadas.php"> 
  <input type="hidden" name="cantidad_detalle" value='<?=$cant_detalle?>' >
  <input type="hidden" name="cantidad_factura" value='<?=$cantidad_fact?>' >
  <input type="hidden" name="simbolo" value='<?=$simbolo?>' >
  <input type="hidden" name="monto_factura" value='<?=$monto_factura?>' >
  <input type="hidden" name="id_vta_atada" value='<?=$id_vta_atada?>' >
  <input type="hidden" name="id_moneda" value='<?=$id_moneda?>' >
  <input type="hidden" name="moneda_factura" value='<?=$moneda_factura?>' >
  <input type="hidden" name="dolar_ingreso" value='<?=$dolar_ingreso?>' >
  
  <table align="center"  border="1" cellspacing="2"  bordercolor="#000000" width="90%">
  <tr id=mo>
    <td colspan="<?=$colspan?>">Monto Proporcional para cada factura de un total de <?echo $simbolo." ".$monto_pago_parcial?></td>
  </tr>
  
   	  <tr id=ma>
   	     <td >NRO DE FACTURA</td>
   	     <?for($j=0;$j<$cant_detalle;$j++) {
   	     	$total=number_format($detalles[$j]['monto_detalles'],"2",".","");?>
         <td><?=$detalles[$j]['nombre']." (".$simbolo." ".$total.")";?> </td>
          <input type="hidden" name="desc_<?=$j?>" value='<?=$detalles[$j]['nombre']?>'>
      <?}?>
   	  </tr>
   <?
   for($i=0;$i<$cantidad_fact;$i++) {
   	?>
     <tr id=ma>
      <td> <?=$datos_vta[$i]['nro_factura']?> </td>
        <input type="hidden" name="nro_<?=$i?>" value='<?=$datos_vta[$i]['nro_factura']?>'>
      <?for($j=0;$j<$cant_detalle;$j++) {?>
        <td>
         <?=$simbolo ?> <input type="text" name="monto_<?=$i?>_<?=$j?>" value=''>
         </td>
      <?}?>
      </tr>
   
  <? }
 
 //busco el monto que se ha pagado 
 $total_pagado=0;
 if ($id_moneda==1 && $moneda_factura==2) { //factura en dol y paso a pesos => recupera monto pagado en dolares
$sql="select sum(ingreso_egreso.monto / dolar_egreso) as total_pagado from 
      licitaciones_datos_adicionales.detalle_egresos_atadas
	  join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	  join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
      join caja.ingreso_egreso using (id_ingreso_egreso)  
	  where id_vta_atada=$id_vta_atada";
$res=sql($sql,"$sql") or fin_pagina();
$sql_sin_eg="select sum(monto_detalle / dolar_egreso) as total_pagado from 
	         licitaciones_datos_adicionales.detalle_egresos_atadas
	         join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	         join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	         where id_vta_atada=$id_vta_atada and (id_cob_egreso=7 or id_cob_egreso=8)";
$res_sin_eg=sql($sql_sin_eg,"$sql_sin_eg") or fin_pagina();
$total_pagado=$res->fields['total_pagado'] + $res_sin_eg->fields['total_pagado'];

}
elseif  ($id_moneda==2 && $moneda_factura==1) { //factura en pesos y pago en dolares
$sql="select sum(ingreso_egreso.monto * dolar_egreso) as total_pagado from 
      licitaciones_datos_adicionales.detalle_egresos_atadas
	   join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	   join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
       join caja.ingreso_egreso using (id_ingreso_egreso)  
	   where id_vta_atada=$id_vta_atada";
$res=sql($sql,"$sql") or fin_pagina();
$sql_sin_eg="select sum(monto_detalle * dolar_egreso) as total_pagado from 
	         licitaciones_datos_adicionales.detalle_egresos_atadas
	         join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	         join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	         where id_vta_atada=$id_vta_atada and (id_cob_egreso=7 or id_cob_egreso=8)";
$res_sin_eg=sql($sql_sin_eg,"$sql_sin_eg") or fin_pagina();
$total_pagado=$res->fields['total_pagado'] + $res_sin_eg->fields['total_pagado'];

}
else {
$sql="select sum(ingreso_egreso.monto) as total_pagado from 
	   licitaciones_datos_adicionales.detalle_egresos_atadas
	   join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	   join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
       join caja.ingreso_egreso using (id_ingreso_egreso)  
	   where id_vta_atada=$id_vta_atada";
$res=sql($sql,"$sql") or fin_pagina();
//suma el monto de ficticio y cheque 
$sql_sin_eg="select sum(monto_detalle) as total_pagado from 
	         licitaciones_datos_adicionales.detalle_egresos_atadas
	         join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	         join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	         where id_vta_atada=$id_vta_atada and (id_cob_egreso=7 or id_cob_egreso=8)";
$res_sin_eg=sql($sql_sin_eg,"$sql_sin_eg") or fin_pagina();

$total_pagado=$res->fields['total_pagado'] + $res_sin_eg->fields['total_pagado'];

}
 
 
  
  ?>
  <tr>
    <td colspan="<?=$colspan?>" align="center">
        <input type='button' name='aceptar' value='Aceptar' onclick="if (control_datos(<?=$total_pagado?>,<?=$monto_factura?>)) { cargar_datos();window.opener.form1.submit();window.close();}">
        <input type='button' name='cerrar' value='Cerrar' onclick="window.close();">
    </td>
  </tr>
</table>
  
</form>     
<?
}
?>