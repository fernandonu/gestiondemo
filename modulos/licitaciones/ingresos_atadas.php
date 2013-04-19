<?
/*
$Author: mari $
$Revision: 1.4 $
$Date: 2006/11/28 19:27:38 $
*/


//MUESTRA LOS MONTOS PROPOCIONALES PARA INGRESOS Y EGRESOS 

require_once("../../config.php");
require_once("fun_cobranzas_atadas.php");
require_once("func_cobranzas.php");
echo $html_header;

if ($_GET['first']==1) {?>
<html>
<head>
</head>
<body>
<form name="form1" action="ingresos_atadas.php" method="POST">
 <input type='hidden' name="fila" value="">
 <input type="hidden" name="monto_pago_parcial" value="">
 <input type="hidden" name="datos_vta" value="">
 <input type="hidden" name="simbolo" value="">
 <input type="hidden" name="moneda_factura" value="">
 <input type="hidden" name="dolar_ingreso" value="">
 <input type="hidden" name="monto_original" value="">
 <input type="hidden" name="id_vta_atada" value="">
 
 <input type="hidden" name="iva" value="">
 <input type="hidden" name="ganancia" value="">
 <input type="hidden" name="rib" value="">
 <input type="hidden" name="suss" value="">
 <input type="hidden" name="multas" value="">
 <input type="hidden" name="deposito" value="">
 <input type="hidden" name="otro" value="">
 <input type="hidden" name="devolucion" value="">
 <input type="hidden" name="interes" value="">
 <input type="hidden" name="gastoadm" value="">
 <input type="hidden" name="comisiones" value="">
 <input type="hidden" name="diferido" value="">
 <input type="hidden" name="id_moneda" value="">
 
</form>
<script>

document.all.fila.value=window.opener.document.all.num_fila.value; 
f=window.opener.document.all.num_fila.value; 
num=eval("window.opener.document.all.parcial_"+f); 
simb=eval("window.opener.document.all.simbolo_"+f); 
dol=eval("window.opener.document.all.dolaractual_"+f); 

document.all.monto_pago_parcial.value=num.value; 
document.all.simbolo.value=simb.value; 
document.all.dolar_ingreso.value=dol.value; 

document.all.datos_vta.value=window.opener.document.all.datos_vta.value; 
document.all.moneda_factura.value=window.opener.document.all.moneda_factura.value; 
document.all.monto_original.value=window.opener.document.all.monto_original.value; 
document.all.id_vta_atada.value=window.opener.document.all.id_vta_atada.value; 

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
if(window.opener.document.all.chk_devp.checked==true)
 document.all.devolucion.value=window.opener.document.all.devolucion.value;
if(window.opener.document.all.chk_int.checked==true)
 document.all.interes.value=window.opener.document.all.interes.value; 
if(window.opener.document.all.chk_adm.checked==true)
 document.all.gastoadm.value=window.opener.document.all.gastoadm.value; 
if(window.opener.document.all.chk_com.checked==true)
 document.all.comisiones.value=window.opener.document.all.comisiones.value;  
 
document.all.id_moneda.value=window.opener.document.all.moneda_pago.value;

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
$monto_pago_parcial=$_POST['monto_pago_parcial']; 
$simbolo=$_POST['simbolo'];
$fila=$_POST['fila'];
$moneda_factura=$_POST['moneda_factura'];
$dolar_ingreso=$_POST['dolar_ingreso'];
$monto_original=$monto_factura=$_POST['monto_original'];
$id_vta_atada=$_POST['id_vta_atada'];
$id_moneda=$_POST['moneda_pago'];

$sql="select id_moneda from moneda where simbolo='$simbolo'";
$res=sql($sql,"$sql") or fin_pagina();
$id_moneda=$res->fields['id_moneda'];
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

if ($_POST['devolucion']) {
  $detalles[$i]['nombre']='DEVOLUCION';
  $detalles[$i]['monto_detalles']=$_POST['devolucion'];
  $i++;
}  

if ($_POST['interes']) {
  $detalles[$i]['nombre']='INTERESES';
  $detalles[$i]['monto_detalles']=$_POST['interes'];
  $i++;
} 
if ($_POST['gastoadm']) {
  $detalles[$i]['nombre']='GASTOS';
  $detalles[$i]['monto_detalles']=$_POST['gastoadm'];
  $i++;
} 
if ($_POST['comisiones']) {
  $detalles[$i]['nombre']='COMISIONES';
  $detalles[$i]['monto_detalles']=$_POST['comisiones'];
  $i++;
} 

$cant_detalle=count($detalles);
$colspan=$cant_detalle+1;



?>

<script>
function control_datos(pagado,monto_total) {

var cant=document.all.cantidad_factura.value;
var simbolo=document.all.simbolo.value;
var id_moneda=document.all.id_moneda.value;
var moneda_factura=document.all.moneda_factura.value;

var i;
var sum=0;


for (i=0;i<cant;i++) {

var monto=eval("document.all.monto_"+i);

if ((isNaN(monto.value)) || (monto.value=="") || (parseFloat(monto.value==0))) {
    alert ("Ingrese número valido para el monto del ingreso");
   return false;
}
else sum+=parseFloat(monto.value);
}
var suma_ingreso= new String(sum.toFixed(2));

p=new String(pagado.toFixed(2));
m=new String(monto_total.toFixed(2));

if (moneda_factura==2 && id_moneda==1) {
   var dolar=parseFloat(document.all.dolar_ingreso.value);
   dol=new String(dolar.toFixed(2));
   suma=(parseFloat(suma_ingreso) / dol)+ parseFloat(p);
}
else if (moneda_factura==1 && id_moneda==2) {
   var dolar=parseFloat(document.all.dolar_ingreso.value);
   dol=new String(dolar.toFixed(2));
   suma=(parseFloat(suma_ingreso) * dol)+ parseFloat(p);
}
else {
	  suma=parseFloat(suma_ingreso)+parseFloat(p);
}

total=new String(suma.toFixed(2));
if ( total - m > 0.03 ) {
 alert ("ERROR: La suma de los egresos supera el monto de las facturas");
 return false;
}
//egresos
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

var entrar = confirm("La suma total de los ingresos es "+ simbolo +" "+ suma_ingreso +" ¿Confirma los ingresos?");
 if (entrar){ 
 	    var entrar1 = confirm( msg + "\n¿Confirma los egresos?");
           if ( entrar1 ){ 
	 	     return true;
	        }
	      else {
	 	    return false;
	      }
	 	return true;
 }
	 else{
	 	return false;
	 }


}

function cargar_datos() {

var cant_factura=document.all.cantidad_factura.value;
var cant_detalle=document.all.cantidad_detalle.value;

for (i=0;i<cant_factura;i++) {
    var monto=eval("document.all.monto_"+i);
    var nro=eval ("document.all.nro_"+i);	
    var m=eval ("window.opener.document.all.montos_"+nro.value); 
    if (monto.value!="")
      m.value=monto.value;
    else m.value="";
  
}
for (j=0;j<cant_detalle;j++) {

  var desc=eval ("document.all.desc_"+j);	
  for (i=0;i<cant_factura;i++) {
    var monto=eval("document.all.monto_"+i+"_"+j);
    var nro=eval ("document.all.nro_eg_"+i);	
    var m=eval ("window.opener.document.all.montos_"+desc.value+"_"+nro.value); 
    if (monto.value!="")
      m.value=monto.value;
    else m.value="";
  }
}
window.opener.document.all.boton_aceptar.value=1;

}


if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}
	

</script>

<form name='form1' action='ingresos_atadas.php' method='post'>
<?

?>
<input type='hidden' name='datos_vta' value="<?=comprimir_variable($datos_vta)?>">
<input type='hidden' name='monto_pago_parcial' value="<?=$monto_pago_parcial?>">
<input type='hidden' name='fila' value="<?=$fila?>">
<input type="hidden" name="simbolo" value='<?=$simbolo?>' >
<input type="hidden" name="cantidad_factura" value='<?=$cantidad_fact?>' >
<input type="hidden" name="id_moneda" value='<?=$id_moneda?>' >
<input type="hidden" name="moneda_factura" value='<?=$moneda_factura?>' >
<input type="hidden" name="dolar_ingreso" value='<?=$dolar_ingreso?>' >
<input type="hidden" name="monto_original" value='<?=$monto_original?>' >
<input type="hidden" name="id_vta_atada" value='<?=$id_vta_atada?>' >
<input type="hidden" name="cantidad_detalle" value='<?=$cant_detalle?>' >
<input type="hidden" name="id_vta_atada" value='<?=$id_vta_atada?>' >

<table align="center"  border="1" cellspacing="2"  bordercolor="#000000" width="90%">
  <tr id=mo>
    <td colspan="2">INGRESOS: Monto Proporcional para cada factura de un total de <?echo $simbolo." ".$monto_pago_parcial?></td>
  </tr>
  <?
   for($i=0;$i<$cantidad_fact;$i++) {
   	$parcial=$datos_vta[$i]['monto_factura']*$monto_pago_parcial/$monto_original;
   	?>
    <tr id=ma>
      <td> <?=$datos_vta[$i]['nro_factura']?> </td>
      <td> <?=$simbolo ?> <input type="text" name="monto_<?=$i?>" value='<?=number_format($parcial,"2",".","")?>'> </td>
    </tr>
      <input type="hidden" name="nro_<?=$i?>" value='<?=$datos_vta[$i]['id_factura']?>'>
  <? }
  
  if($id_vta_atada) {
  if ($moneda_factura == $id_moneda) {
        
        $sql_sum="select sum (ingreso_egreso.monto) as total_ingresado
				  from licitaciones_datos_adicionales.pagos_atadas
				  join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
				  join caja.ingreso_egreso using (id_ingreso_egreso)
				   where id_vta_atada=$id_vta_atada and ingresos=1";
       
        }
        else { 
         if ($id_moneda==1)  //es de dolares a peso 
           $sql_sum="select sum(ingreso_egreso.monto / valor_dolar) as total_ingresado
                     from licitaciones_datos_adicionales.pagos_atadas
				     join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
				     join caja.ingreso_egreso using (id_ingreso_egreso)
				     where id_vta_atada=$id_vta_atada and ingresos=1";
         else  ///es de pesos a dolares 
            $sql_sum="select sum(ingreso_egreso.monto * valor_dolar) as total_ingresado
                      from licitaciones_datos_adicionales.pagos_atadas
				      join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
				      join caja.ingreso_egreso using (id_ingreso_egreso)
				      where id_vta_atada=$id_vta_atada and ingresos=1";        
        }
      
        $res_sum=sql($sql_sum,"$sql_sum") or fin_pagina();
        if ($res_sum->fields['total_ingresado'])
           $total_pagado=$res_sum->fields['total_ingresado'];
           else $total_pagado='0.00';
  }
  else $total_pagado='0.00';
  
  ?>
  <br>
  <table align="center"  border="1" cellspacing="2"  bordercolor="#000000" width="90%">
  <tr id=mo>
    <td colspan="<?=$colspan?>">EGRESOS:Monto Proporcional para cada factura de un total de <?echo $simbolo." ".$monto_pago_parcial?></td>
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
        <input type="hidden" name="nro_eg_<?=$i?>" value='<?=$datos_vta[$i]['id_factura']?>'>
      <?for($j=0;$j<$cant_detalle;$j++) {?>
        <td>
         <?=$simbolo ?> <input type="text" name="monto_<?=$i?>_<?=$j?>" value=''>
         </td>
      <?}?>
      </tr>
   
  <? }
 
  if ($id_vta_atada) {
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
  }
  else $total_pagado=0; 
 ?>
  <?/*window.opener.form1.submit();window.close();*/?>
  
  <tr>
    <td colspan="<?=$colspan?>" align="center">
        <input type='button' name='aceptar' value='Aceptar' onclick="if (control_datos(<?=$total_pagado?>,<?=$monto_original?>)) 
             {  cargar_datos();window.opener.form1.submit();window.close()}
 
            ">
        <input type='button' name='cerrar' value='Cerrar' onclick="window.close();">
    </td>
  </tr>
</table>
<?}?>
</form>