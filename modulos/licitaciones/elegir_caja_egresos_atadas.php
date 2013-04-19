<?

/*
$Author: mari $
$Revision: 1.2 $
$Date: 2006/01/13 22:23:00 $
*/
require_once("../../config.php");
global $contenido;

require_once("func_cobranzas.php");
require_once("fun_cobranzas_atadas.php");
require_once(LIB_DIR."/class.gacz.php");

//guarda los detalles del seguimineto
if ($_POST['seguimiento']) {
  if (guarda_detalle_egresos_atadas())
    $msg='LOS DETALLES DEL EGRESO SE GUARDARON CORRECTAMENTE ';
    else $msg='ERROR AL GUARDAR LOS DETALLES ';
}

//registra el egreso de cada detalle
if ($_POST['boton_aceptar']) {

$datos_vta=descomprimir_variable($_POST['datos_vta']);
$cant_facturas=count($datos_vta);
$valores=descomprimir_variable($_POST['valores_defecto']);
$id_vta_atada=$_POST['id_vta_atada'];
$atadas=array();
$nro_fact=array();
for($ind=0;$ind<$cant_facturas;$ind++) {
  $atadas[$ind]=$datos_vta[$ind]['id_cobranza'];
  $nro_fact[$ind]=$datos_vta[$ind]['nro_factura'];
}

$fecha=fecha_db($_POST['fecha']);
list($anio,$mes,$dia)=split("-",$fecha);
$dia = date('w', mktime(0,0,0,$mes,$dia,$anio));

$id_moneda=$_POST['id_moneda']; //moneda del pago
$monto_factura=$_POST['monto_factura']; //monto de las facturas atadas
$simbolo=$_POST['simbolo_total']; //simbolo del pago
$distrito=$_POST['id_distrito'];
$monto_total=$_POST['monto_total'];  //es el monto del pago parcial 
$moneda_factura=$_POST['moneda_factura'];
if ($_POST['dolar_ingreso'] !="" || $_POST['dolar_ingreso'] !=null) {
   $dolar_actual=$_POST['dolar_ingreso'];
   $dolar =" Valor Dolar ".formato_money($dolar_actual);
}
  else {
  	$dolar_actual=0; 
  	$dolar="";
  }
$id_cobranza=$_POST['id_cobranza'];
$id_pagos_atadas=$_POST['id_pagos_atadas'];


$sql="select nombre from distrito where id_distrito=$distrito";
$res=sql($sql,"$sql") or fin_pagina();
$nombre_distrito=$res->fields['nombre'];

if($dia!=0 && !feriado(fecha($fecha))) { //si dia es feriado o domingo 
//iniciamos transaccion
 $db->StartTrans();
 $detalles=array();
 $detalles=guarda_detalle_egresos_atadas();
 

//si la caja no exite la crea, sino recupera el id
 
 $query="select id_caja,fecha,cerrada from caja where fecha='$fecha' and id_distrito=$distrito and id_moneda=$id_moneda";
 $caja_query=sql($query) or fin_pagina();
 $caja_cerrada=0;
 
 if($caja_query->RecordCount()==0) { 
 	$query="select nextval('caja.caja_id_caja_seq') as id";
    $id_caja=sql($query) or fin_pagina();
    $id_caja=$id_caja->fields['id'];
	$query="insert into caja(id_caja,id_distrito,id_moneda,fecha)values($id_caja,$distrito,$id_moneda,'$fecha')";
	sql($query,"$query") or fin_pagina();
	$caja_cerrada=0;
 }
 else {
      $id_caja=$caja_query->fields['id_caja'];
      $caja_cerrada=$caja_query->fields['cerrada'];
 }

 if ($caja_cerrada==0) {
 $fecha_creacion = date("Y-m-d H:m:s",mktime());
 $usuario=$_ses_user_name;
 
 $comentario="";
 
 $list_cobranza="";
 $list_factura="";
  
 $egreso=array();
 $val_actual==array();  //guardo los valores seleccionados antes de guardar el egreso
 $eg=0;
  
if ($_POST['chk_iva']==1) {
 // recupera datos del iva
 $proveedor=$_POST['prov_iva'];
 $tipo_egreso=$_POST['tipo_iva'];
 $nro_cuenta=$_POST['concepto_iva'];
 
  for ($i=0;$i<$cant_facturas;$i++) {
 	 $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total";
            
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_IVA_$nro"]; 
           
 	 if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
     $egreso[$eg++]="\nId Egreso: ".$id." Descripción: IVA  Monto: ".$simbolo." ".formato_money($monto);
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['IVA'].",$id)";
 	 }
 }
 
 $val_actual['IVA']['prov']=$proveedor;
 $val_actual['IVA']['cta']=$nro_cuenta;
 $val_actual['IVA']['tipo']=$tipo_egreso;
 }
 

if ($_POST['chk_gan']==1) {
 // recupera datos de ganancias
 $proveedor=$_POST['prov_gan'];
 $tipo_egreso=$_POST['tipo_gan'];
 $nro_cuenta=$_POST['concepto_gan'];
 for ($i=0;$i<$cant_facturas;$i++) {
   $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
   $item.="-".$datos_vta[$i]['nombre'];
   if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
   $item.="-Pago proporcianal $monto_total";      
   $nro=$datos_vta[$i]['nro_factura'];
   $monto=$_POST["montos_GANANCIA_$nro"];       
   
   if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
     $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Ganancias Monto: ".$simbolo." ".formato_money($monto);
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['GANANCIAS'].",$id)";
   }  
 }
 $val_actual['GANANCIAS']['prov']=$proveedor;
 $val_actual['GANANCIAS']['cta']=$nro_cuenta;
 $val_actual['GANANCIAS']['tipo']=$tipo_egreso;    
 }
 
 if ($_POST['chk_rib'] ==1) {
 // recupera datos de rib
 $proveedor=$_POST['prov_rib'];
 $tipo_egreso=$_POST['tipo_rib'];
 $nro_cuenta=$_POST['concepto_rib'];
 for ($i=0;$i<$cant_facturas;$i++) {
     $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total";      
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_RIB_$nro"];  
     
    if ($monto != "") {
		 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
    	 $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['RIB'].",$id)";
		 $egreso[$eg++]="\n Id Egreso: ".$id." Descripción: RIB Monto: ".$simbolo." ".formato_money($monto);
    }
 }
 $val_actual['RIB']['prov']=$proveedor;
 $val_actual['RIB']['cta']=$nro_cuenta;
 $val_actual['RIB']['tipo']=$tipo_egreso;
}
  
    
if ($_POST['chk_suss'] ==1) {
 // recupera datos de suss
 $proveedor=$_POST['prov_suss'];
 $tipo_egreso=$_POST['tipo_suss'];
 $nro_cuenta=$_POST['concepto_suss'];
 for ($i=0;$i<$cant_facturas;$i++) {
     $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total";
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_SUSS_$nro"];  
 
     if ($monto != "") {     	
	   $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
	   $egreso[$eg++]="\nId Egreso: ".$id." Descripción: SUSS Monto: ".$simbolo." ".formato_money($monto);
	   $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['SUSS'].",$id)";
     }	
 } 
 $val_actual['SUSS']['prov']=$proveedor;
 $val_actual['SUSS']['cta']=$nro_cuenta;
 $val_actual['SUSS']['tipo']=$tipo_egreso;
}
 
if ($_POST['chk_mul'] ==1) {
 // recupera datos de multas
 $proveedor=$_POST['prov_multas'];
 $tipo_egreso=$_POST['tipo_multas'];
 $nro_cuenta=$_POST['concepto_multas'];
 for ($i=0;$i<$cant_facturas;$i++) {
     $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
   	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total";    
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_MULTAS_$nro"];  
 
 if ($monto != "") {
	 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
	 $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['MULTAS'].",$id)";
	 $egreso[$eg++]="Id Egreso: ".$id." Descripción: Multas Monto: ".$simbolo." ".formato_money($monto);
 }
 }	  
 $val_actual['MULTAS']['prov']=$proveedor;
 $val_actual['MULTAS']['cta']=$nro_cuenta;
 $val_actual['MULTAS']['tipo']=$tipo_egreso;
 }
 
if ($_POST['chk_dep'] ==1) {
 // recupera datos de deposito
 $proveedor=$_POST['prov_dep'];
 $tipo_egreso=$_POST['tipo_dep'];
 $nro_cuenta=$_POST['concepto_dep'];
 $banco=$_POST['banco'];
 $tipo=$_POST['tipo_deposito'];
 
 for ($i=0;$i<$cant_facturas;$i++) {
     $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
  	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total"; 
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_DEPOSITO_$nro"];  
 
     if ($monto != "") {
		 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
		 $id_dep=guardar_deposito($banco,$fecha,$tipo,$monto,$comen);
		 $egreso[$eg++]="\nId Egreso: ".$id." Descripción: transferencia Monto: ".$simbolo." ".formato_money($monto);
		 $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['DEPOSITO'].",$id)";
     }
 }
 $val_actual['TRANSFERENCIA']['prov']=$proveedor;
 $val_actual['TRANSFERENCIA']['cta']=$nro_cuenta;
 $val_actual['TRANSFERENCIA']['tipo']=$tipo_egreso;
 $val_actual['TRANSFERENCIA']['banco']=$banco;
 $val_actual['TRANSFERENCIA']['tipo_dep']=$tipo;
 }
 
 if ($_POST['chk_otro'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_otro'];
 $tipo_egreso=$_POST['tipo_otro'];
 $nro_cuenta=$_POST['concepto_otro'];
 for ($i=0;$i<$cant_facturas;$i++) {
     $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total";    
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_OTROS_$nro"];  
     
 if ($monto != "") {
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Otros Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['OTROS'].",$id)";
 }
 }
 $val_actual['OTROS']['prov']=$proveedor;
 $val_actual['OTROS']['cta']=$nro_cuenta;
 $val_actual['OTROS']['tipo']=$tipo_egreso;
 }
 
 if ($_POST['chk_diferido'] ==1) {
 // recupera datos de diferidos
 $proveedor=$_POST['prov_diferido'];
 $tipo_egreso=$_POST['tipo_diferido'];
 $nro_cuenta=$_POST['concepto_diferido'];
 for ($i=0;$i<$cant_facturas;$i++) {
     $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $monto_total";  
     $nro=$datos_vta[$i]['nro_factura'];
     $monto=$_POST["montos_DIFERIDO_$nro"];  
 
 if ($monto != "") {
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Cheque Diferido Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['DIFERIDO'].",$id)";
  //activo en uno significa que se hizo el egreso 
 }
 }
 $sql="update cheques_diferidos set activo = 1 
       where id_chequedif in (select id_chequedif from cheque_cobranza_atadas where id_pagos_atadas=$id_pagos_atadas);";
 sql($sql,"$sql") or fin_pagina();
 $val_actual['Cheques diferidos']['prov']=$proveedor;
 $val_actual['Cheques diferidos']['cta']=$nro_cuenta;
 $val_actual['Cheques diferidos']['tipo']=$tipo_egreso;
}
 
// recupera datos de efectivo
 if ($_POST['chk_ficticio'] ==1) {
     $egreso[$eg++]=" Descripción: EFECTIVO (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['FICTICIO'].",NULL)";
 }


// recupera datos de cheque
if ($_POST['chk_cheque'] ==1) {  
     $egreso[$eg++]="Id Egreso: ".$id." Descripción: CHEQUE (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['CHEQUE'].",NULL)";
 }
 
 if ($sql_detalle!="") {
 	 sql ($sql_detalle,"ERROR en $sql_detalle") or fin_pagina (); //inserta en tabla detalle_ingresos cada uno de los egresos 
 }
 
 $sql="update pagos_atadas set egresos=1 where id_pagos_atadas=$id_pagos_atadas";
 sql($sql,"$sql") or fin_pagina();
 


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
elseif  ($id_moneda==2 && $moneda_factura==1) {
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
$total_pagado=number_format($total_pagado,"2",".","");
$diff=abs($total_pagado - $monto_factura);

if (($total_pagado == $monto_factura) || ($diff <= 0.03 )) {
   
   mail_egresos_parciales_atadas ($id_vta_atada,$nombre_distrito,$valores,$nro_fact);
   $sql="update venta_fac_atadas set ctrl_egreso=1 where id_vta_atada=$id_vta_atada";  //indica que se realizaron todos los egresos
   sql($sql,"$sql") or fin_pagina();
   //cuando se realizaron los egresos por el total de la factura
}


//cierra transaccion
$db->CompleteTrans();
    
} else   Error ("La Caja esta Cerrada. Seleccione fecha de la caja"); 

} else Error("Usted esta intentando insertar un egreso de un dia que no es habil");


$link=encode_link("venta_factura_atadas.php",array("id_cobranza"=>$id_cobranza,"monto_original"=>$monto_factura,
                  "moneda_factura"=>$moneda_factura,"atadas"=>comprimir_variable($atadas),
                  "simbolo_factura"=>$simbolo));

                       
 if (!$error) { ?>
  <script>
  window.opener.location.href='<?=$link?>';
  window.close();
  </script>
<?
}
}  //fin de $_POST[aceptar]



echo $html_header;
cargar_calendario();


//defino clase cheques_diferidos
class cheques_diferidos {
   var $cheques;
     
   function cheques_diferidos() {
       $this->cheques = Array();
   }
}



?>
<script src="<?=$html_root."/lib/wddx.js"?>" language="javascript"></script>
<script language="javascript">
var cheques_diferidos = new Object();
cheques_diferidos.php_class_name = 'cheques_diferidos';
cheques_diferidos.cheques = new Object();
cheques_diferidos.cheques["monto"] = new Array();
cheques_diferidos.cheques["nro"] = new Array();
cheques_diferidos.cheques["comentario"] = new Array();
cheques_diferidos.cheques["ubicacion"] = new Array();
cheques_diferidos.cheques["fecha_vencimiento"] = new Array();
cheques_diferidos.cheques["banco"] = new Array();
cheques_diferidos.cheques["pertenece"] = new Array();
cheques_diferidos.cheques["id_cliente"] = new Array();
cheques_diferidos.cheques["cliente"] = new Array();



</script>
<script src="<?=$html_root."/lib/funciones.js"?>" ></script>

<script>

if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}

//controla que se haya elegido al menos un detalle	
function cant_chequeados () {
var sum=0;

if (document.all.chk_iva.checked==true) 
    sum++;
if (document.all.chk_gan.checked==true)   
     sum++;
if (document.all.chk_rib.checked==true)   
    sum++;
if (document.all.chk_suss.checked==true)   
    sum++;
if (document.all.chk_mul.checked==true)   
     sum++;
if (document.all.chk_dep.checked==true)    
     sum++;
if (document.all.chk_otro.checked==true)    
     sum++;
if (document.all.chk_ficticio.checked==true)   
     sum++;
if (document.all.chk_cheque.checked==true)   
     sum++;
if (document.all.chk_diferido.checked==true) 
    sum++;
if (sum==0) alert ('Debe Elegir al menos un detalle');     
wddxSerializer = new WddxSerializer();
MyWDDXPacket = wddxSerializer.serialize(cheques_diferidos);
document.all.cheques_diferidos.value=MyWDDXPacket;
return sum;     
}	

//controla que sean correctos los valores que se ingresan en el detalle del egreso
function control_num () {

if (document.all.chk_iva.checked==true) {
if (isNaN(document.all.iva.value) || parseFloat(document.all.iva.value)=='0' || document.all.iva.value=="") {
    alert ('Debe ingresar número valido para el campo IVA ');
    document.all.iva.value="";
    return false;
}
if (document.all.tipo_iva.options[document.all.tipo_iva.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el IVA ');
   return false;
}
if (document.all.prov_iva.options[document.all.prov_iva.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el IVA ');
   return false;
}
if (document.all.concepto_iva.options[document.all.concepto_iva.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el IVA ');
   return false;
}
}

if (document.all.chk_gan.checked==true) {
if (isNaN(document.all.ganancia.value) || parseFloat(document.all.ganancia.value)=='0' || document.all.ganancia.value=="") {
    alert ('Debe ingresar número valido para el campo GANANCIAS ');
    document.all.ganancia.value="";
    return false;
}
if (document.all.tipo_gan.options[document.all.tipo_gan.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el GANANCIAS ');
   return false;
}
if (document.all.prov_gan.options[document.all.prov_gan.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el GANANCIAS ');
   return false;
}
if (document.all.concepto_gan.options[document.all.concepto_gan.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el GANANCIAS ');
   return false;
}
}

if (document.all.chk_rib.checked==true) {
if (isNaN(document.all.rib.value) || parseFloat(document.all.rib.value)=='0' || document.all.rib.value=="") {
    alert ('Debe ingresar número valido para el campo R.I.B ');
    document.all.rib.value="";
    return false;
}

if (document.all.tipo_rib.options[document.all.tipo_rib.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el RIB ');
   return false;
}
if (document.all.prov_rib.options[document.all.prov_rib.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el RIB ');
   return false;
}
if (document.all.concepto_rib.options[document.all.concepto_rib.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el RIB ');
   return false;
}
}


if (document.all.chk_suss.checked==true) {
if (isNaN(document.all.suss.value) || parseFloat(document.all.suss.value)=='0' || document.all.suss.value=="") {
    alert ('Debe ingresar número valido para el campo SUSS ');
    document.all.suss.value="";
    return false;
}

if (document.all.tipo_suss.options[document.all.tipo_suss.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el SUSS ');
   return false;
}
if (document.all.prov_suss.options[document.all.prov_suss.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el SUSS ');
   return false;
}
if (document.all.concepto_suss.options[document.all.concepto_suss.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el SUSS ');
   return false;
}
}


if (document.all.chk_mul.checked==true) {
if (isNaN(document.all.multas.value) || parseFloat(document.all.multas.value)=='0' || document.all.multas.value=="") {
    alert ('Debe ingresar número valido para el campo MULTAS ');
    document.all.multas.value="";
    return false; 
}
if (document.all.tipo_multas.options[document.all.tipo_multas.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el MULTAS ');
   return false;
}
if (document.all.prov_multas.options[document.all.prov_multas.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el MULTAS ');
   return false;
}
if (document.all.concepto_multas.options[document.all.concepto_multas.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el MULTAS ');
   return false;
}
}

if (document.all.chk_dep.checked==true) {
if (isNaN(document.all.deposito.value) || parseFloat(document.all.deposito.value)=='0' || document.all.deposito.value=="") {
    alert ('Debe ingresar número valido para el campo TRANSFERENCIA');
    document.all.deposito.value="";
    return false;
}
if (document.all.tipo_dep.options[document.all.tipo_dep.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el campo TRANSFERENCIA ');
   return false;
}
if (document.all.prov_dep.options[document.all.prov_dep.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el TRANSFERENCIA ');
   return false;
}
if (document.all.concepto_dep.options[document.all.concepto_dep.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el TRANSFERENCIA ');
   return false;
}
}

if (document.all.chk_diferido.checked==true) {
if (isNaN(document.all.diferido.value) || parseFloat(document.all.diferido.value)=='0' || document.all.diferido.value=="") {
    alert ('Debe ingresar número valido para el campo Ch. Diferido');
    document.all.diferido.value="";
    return false;
}
if (document.all.tipo_diferido.options[document.all.tipo_diferido.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el campo Ch. Diferido ');
   return false;
}
if (document.all.prov_diferido.options[document.all.prov_diferido.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el Ch. Diferido ');
   return false;
}
if (document.all.concepto_diferido.options[document.all.concepto_diferido.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el Ch. Diferido ');
   return false;
}
}


if (document.all.chk_otro.checked==true) {
if (isNaN(document.all.otro.value) || parseFloat(document.all.otro.value)=='0' || document.all.otro.value=="") {
    alert ('Debe ingresar número valido para el campo OTROS ');
    document.all.otro.value="";
    return false;
}
if (document.all.tipo_otro.options[document.all.tipo_otro.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el OTROS ');
   return false;
}
if (document.all.prov_otro.options[document.all.prov_otro.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el OTROS ');
   return false;
}
if (document.all.concepto_dep.options[document.all.concepto_otro.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el OTROS ');
   return false;
}
}

if (document.all.chk_ficticio.checked==true) {
if (isNaN(document.all.ficticio.value) || parseFloat(document.all.ficticio.value)=='0' || document.all.ficticio.value=="") {
    alert ('Debe ingresar número valido para el campo EFECTIVO ');
    document.all.ficticio.value="";
    return false;
}
}

if (document.all.chk_cheque.checked==true) {
if (isNaN(document.all.cheque.value) || parseFloat(document.all.cheque.value)=='0' || document.all.cheque.value=="") {
    alert ('Debe ingresar número valido para el campo CHEQUE ');
    document.all.cheque.value="";
    return false;
}
}
return true;
}   //fin control numero

//calcula el total del detalle del egreso
function calcular_total (flag) { //si flag es cero no hace controles solo muestra el total de los detalles
	var sum=0;
	var re;  

if (document.all.chk_iva.checked==true) 
    sum+=parseFloat (document.all.iva.value);
if (document.all.chk_gan.checked==true)   
     sum+=parseFloat (document.all.ganancia.value);
if (document.all.chk_rib.checked==true)   
    sum+=parseFloat (document.all.rib.value);
if (document.all.chk_suss.checked==true)   
    sum+=parseFloat (document.all.suss.value);    
if (document.all.chk_mul.checked==true)   
     sum+=parseFloat (document.all.multas.value) ;
if (document.all.chk_dep.checked==true)    
     sum+=parseFloat (document.all.deposito.value);
if (document.all.chk_otro.checked==true)    
     sum+=parseFloat (document.all.otro.value);
if (document.all.chk_ficticio.checked==true)    
     sum+=parseFloat (document.all.ficticio.value);
if (document.all.chk_cheque.checked==true)    
     sum+=parseFloat (document.all.cheque.value);
if (document.all.chk_diferido.checked==true)    
     sum+=parseFloat (document.all.diferido.value);
var moneda=document.all.simbolo_total.value;

var s= new String(sum.toFixed(2));
document.all.total.value=s;    //muestra en el total
if (flag==1) {
 total_egresos=parseFloat(document.all.total.value);
 total_fact=parseFloat(document.all.monto_total.value);
 var total_f = new String(total_fact.toFixed(2));
  //diff=total_f - total_egresos; 
  //var diff_total = new String(diff.toFixed(2));
 if (total_f != total_egresos ) {
    alert ('El monto del ingreso no coincide con el total de los egresos');
    return false;

}
}
return true
}  //fin calcular total 

</script>


<form action="elegir_caja_egresos_atadas.php" method="post" name='form1'>
<input type="hidden" name="cheques_diferidos" id="cheques_diferidos" value="">

<?
if ($parametros['control_egreso']==1) 
      echo "<div align='center'> <font color=red> LOS MONTOS FUERON REGISTRADOS EN CAJA</font></div>";
echo "<div align='center'> <font color=red> $msg </font></div>";

$id_cobranza = $parametros['id_cobranza'] or $id_cobranza=$_POST['id_cobranza']; //cobranza primaria
$id_vta_atada = $parametros['id_vta_atada'] or $id_vta_atada=$_POST['id_vta_atada'];
$moneda_factura=$parametros['moneda_factura'] or $moneda_factura=$_POST['moneda_factura']; //moneda de la factura
$monto=$parametros['monto'] or $monto=$_POST['monto'];  //monto del pago parcial
$id_moneda=$parametros['id_moneda'] or $id_moneda=$_POST['id_moneda'];  //moneda del pago parcial
$id_distrito=$parametros['id_distrito'] or $id_distrito=$_POST['id_distrito'];  //caja del pago parcial
$monto_factura=$parametros['monto_factura'] or $monto_factura=$_POST['monto_factura'];  //suma monto de las facturas atadas
$control_egreso=$parametros['control_egreso'] or $control_egreso=$_POST['control_egreso'];  // si esta en 1 ya esta realizado el egreso
$dolar_ingreso=$parametros['dolar_ingreso'] or $dolar_ingreso=$_POST['dolar_ingreso'];  // dolar del pago parcial
$datos_vta=descomprimir_variable($parametros['datos_vta']) or $datos_vta=descomprimir_variable($_POST['datos_vta']);
$id_pagos_atadas=$parametros['id_pagos_atadas'] or $id_pagos_atadas=$_POST['id_pagos_atadas'];

if ($_POST['valores_defecto']) 
    $valores_defecto=descomprimir_variable($_POST['valores_defecto']);
else 
   $valores_defecto=armar_valores_defecto();


//para cheques diferidos

$sql="select nro_cheque,fecha_vencimiento,id_banco,monto,id_empresa_cheque,
      comentario,ubicacion,id_entidad,nombre 
      from cheques_diferidos 
      join cheque_cobranza_atadas using(id_chequedif) 
      join licitaciones.entidad using (id_entidad)
      where id_pagos_atadas=$id_pagos_atadas";
$resultado_diferidos = sql($sql,"$sql") or fin_pagina();
?>

<script>
<?

$i=0;
while(!$resultado_diferidos->EOF) {
?>
cheques_diferidos.cheques["monto"][<?=$i;?>] = <?=$resultado_diferidos->fields['monto'];?>;
cheques_diferidos.cheques["nro"][<?=$i;?>] = '<?=$resultado_diferidos->fields['nro_cheque'];?>';
cheques_diferidos.cheques["comentario"][<?=$i;?>] = '<?=$resultado_diferidos->fields['comentario'];?>';
cheques_diferidos.cheques["ubicacion"][<?=$i;?>] = '<?=$resultado_diferidos->fields['ubicacion'];?>';
cheques_diferidos.cheques["fecha_vencimiento"][<?=$i;?>] = '<?=Fecha($resultado_diferidos->fields['fecha_vencimiento']);?>';
cheques_diferidos.cheques["banco"][<?=$i;?>] = <?=$resultado_diferidos->fields['id_banco'];?>;
cheques_diferidos.cheques["pertenece"][<?=$i;?>] = <?=$resultado_diferidos->fields['id_empresa_cheque'];?>;
cheques_diferidos.cheques["id_cliente"][<?=$i;?>] = <?=$resultado_diferidos->fields['id_entidad'];?>;
cheques_diferidos.cheques["cliente"][<?=$i;?>] = '<?=$resultado_diferidos->fields['nombre'];?>';
<?
$resultado_diferidos->MoveNext();
$i++;
}

?>
</script>
<?
$sql=" select id_detalle_eg_atadas from detalle_egresos_atadas 
       join pagos_atadas using (id_pagos_atadas)
       where id_vta_atada=$id_vta_atada";
$res=sql($sql) or fin_pagina();

if ($res->RecordCount() > 0)
   $hay_detalle=1; //se han guardado detalles de egresos
   else $hay_detalle=0; //no se ha guardado detalles de egresos


?>
<input type="hidden" name="id_cobranza" value="<?=$id_cobranza?>">
<input type="hidden" name="id_vta_atada" value="<?=$id_vta_atada?>">
<input type="hidden" name="monto" value="<?=$monto?>">
<input type="hidden" name="id_moneda" value="<?=$id_moneda?>">
<input type="hidden" name="id_distrito" value="<?=$id_distrito?>">
<input type="hidden" name="moneda_factura" value="<?=$moneda_factura?>">
<input type="hidden" name="simbolo_factura" value="<?=$simbolo_factura?>">
<input type="hidden" name="monto_factura" value="<?=$monto_factura?>">
<input type="hidden" name="dolar_ingreso" value="<?=$dolar_ingreso?>">
<input type="hidden" name="control_egreso" value="<?=$control_egreso?>">
<input type='hidden' name='datos_vta' value="<?=comprimir_variable($datos_vta)?>">
<input type='hidden' name='id_pagos_atadas' value="<?=$id_pagos_atadas?>">
<input type="hidden" name="valores_defecto" value="<?=comprimir_variable($valores_defecto)?>">
<?

if ($error) 
   $fecha=fecha_db($_POST['fecha']);
else 
   $fecha=date("Y-m-d");

   
$monto_total=number_format($monto,"2",".","");  //monto del pago parcial
//busco simbolo de la moneda
$sql_moneda="select simbolo from moneda where id_moneda=$id_moneda";
$res_moneda=sql($sql_moneda,"$sql_moneda") or fin_pagina();
$simbolo_ingreso=$res_moneda->fields['simbolo'];
echo "<input type='hidden' name='simbolo_ingreso' value='$simbolo_ingreso' ";
?>

<br>
<table align="center">
   <tr> <td colspan=5 align="center"><font color="blue"> DATOS DEL EGRESO </font></td></tr>
   <tr>
      <td> FECHA EGRESO <input type="text" name="fecha" readonly value=<?=fecha($fecha);?>><?= link_calendario("fecha")?></td>
      <td> &nbsp;&nbsp;&nbsp;<font color="Red"><b>MONTO EGRESO</b> </font> </td>
      <td> 
        <input name="simbolo_total" type="text" style="text-align:right; background:inherit; border:none" value="<?=$simbolo_ingreso?>" readonly size="3" >
        <input name="monto_total"   type="text" style="text-align:left; background:inherit; border:none" value="<?if ($_POST['monto_total']) echo $_POST['monto_total']; else echo $monto_total;?>" readonly size="15" >
      </td>
      <td>
    
       <table <? if ($id_moneda==1 && $moneda_factura==1 ) echo "style='visibility:hidden'"; else echo "style='visibility:visible'";  ?>>
        <tr> 
          <td> <font color="Red"> <b>VALOR DOLAR</b>&nbsp; </font> </td>
          <td> <input type='text' name="dolar_egreso"  size="10" value="<?=number_format($dolar_ingreso,2,".","")?>" readonly > </td>
        </tr>   
       </table>
      <div>
    </td>
  </tr>
</table>   
<br>


<? 

////////////////////////////////////////////////////////////// 
//query para generar los select
 $query="SELECT * FROM caja.tipo_egreso order by nombre";
 $res_egreso=sql($query) or fin_pagina();
 $query="SELECT razon_social,id_proveedor FROM general.proveedor order by razon_social";
 $res_prov=sql($query) or fin_pagina();
 $query="select * from general.tipo_cuenta order by concepto,plan";
 $res_concepto=sql($query) or fin_pagina();
 $query="SELECT idbanco,nombrebanco FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
 $res_banco=sql($query) or fin_pagina();
 $query="SELECT * FROM bancos.tipo_depósito";
 $res_deposito=sql($query) or fin_pagina();

 
  
if (!$error) {
 	
 
//recupera los datos almacenados en detalle_egresos   
$valores_detalle=armar_valores_detalle_atadas ($id_pagos_atadas);
    
  $text_iva=$valores_detalle['iva']['monto_detalle'];
  if ($text_iva !="" || $text_iva!=null) {
     $text_iva=number_format($text_iva,2,".",""); 
  	 $c_iva=1;
  }
  
  
  $text_gan=$valores_detalle['ganancias']['monto_detalle'];
  if ($text_gan !="" || $text_gan!=null) {
      $c_gan=1;
      $text_gan=number_format($text_gan,2,".","");
  }
 	
  $text_rib=$valores_detalle['rib']['monto_detalle'];
  if ($text_rib !="" || $text_rib!=null) {
      $c_rib=1;
      $text_rib=number_format($text_rib,2,".","");
  }

  $text_suss=$valores_detalle['suss']['monto_detalle'];
  if ($text_suss !="" || $text_suss!=null) {
      $c_suss=1;
      $text_suss=number_format($text_suss,2,".","");
  }
  
  $text_mul=$valores_detalle['Multas']['monto_detalle'];
  if ($text_mul !="" || $text_mul!=null) {
      $c_mul=1;
       $text_mul=number_format($text_mul,2,".","");
  }
 
   $text_dep=$valores_detalle['Transferencia']['monto_detalle'];
   if ($text_dep !="" || $text_dep!=null) {
      $c_dep=1;
      $text_dep=number_format($text_dep,2,".","");
  }
 
  $text_otro=$valores_detalle['Otros']['monto_detalle'];
   if ($text_otro !="" || $text_otro!=null) {
      $c_otro=1;
      $text_otro=number_format($text_otro,2,".","");
  }
 
  $text_ficticio=$valores_detalle['Efectivo']['monto_detalle'];
   if ($text_ficticio !="" || $text_ficticio!=null) {
      $c_ficticio=1;
      $text_ficticio=number_format($text_ficticio,2,".","");
  }
  
  $text_cheque=$valores_detalle['Cheque']['monto_detalle'];
   if ($text_cheque !="" || $text_cheque!=null) {
      $c_cheque=1;
      $text_cheque=number_format($text_cheque,2,".","");
  }
 
 
  $text_diferido=$valores_detalle['Cheque Diferido']['monto_detalle'];
 	 
  if ($text_diferido !="" || $text_diferido!=null) {
     $text_diferido=number_format($text_diferido,2,".",""); 
  	 $c_diferido=1;
  }
  
 //seleccion de valores ya cargados en tabla detalle egreso o valores  predeterminados
 
 $id_iva=$valores_detalle['iva']['id_tipo_egreso'] or 
     $id_iva=$valores_defecto['iva']['tipo'];
 
 $id_gan=$valores_detalle['ganancias']['id_tipo_egreso'] or 
     $id_gan=$valores_defecto['ganancias']['tipo'];
 
 $id_rib =$valores_detalle['rib']['id_tipo_egreso'] or 
    $id_rib=$valores_defecto['rib']['tipo'];
    
 $id_suss =$valores_detalle['suss']['id_tipo_egreso'] or 
    $id_suss=$valores_defecto['suss']['tipo'];  
 
 $id_mul=$valores_detalle['Multas']['id_tipo_egreso'] or 
     $id_mul=$valores_defecto['Multas']['tipo'];
 
 $id_dep=$valores_detalle['Transferencia']['id_tipo_egreso']  or
   $id_dep=$valores_defecto['Transferencia']['tipo'];
   
   $id_banco=$valores_detalle['Transferencia']['idbanco'] or 
       $id_banco=$valores_defecto['Transferencia']['banco'];
   $id_deposito=$valores_detalle['Transferencia']['idtipodep'] or
      $id_deposito=$valores_defecto['Transferencia']['deposito'];
 
 $id_otro=$valores_detalle['Otros']['id_tipo_egreso'] or 
     $id_otro=$valores_defecto['Otros']['tipo'];
 
 $id_diferido=$valores_detalle['Cheque Diferido']['id_tipo_egreso'] or 
     $id_diferido=$valores_defecto['Cheque Diferido']['tipo']; 
     
 $prov_iva=$valores_detalle['iva']['id_proveedor'] or
    $prov_iva=$valores_defecto['iva']['prov'];
 
  $prov_gan=$valores_detalle['ganancias']['id_proveedor'] or
     $prov_gan=$valores_defecto['ganancias']['prov'];

  $prov_rib=$valores_detalle['rib']['id_proveedor'] or 
    $prov_rib=$valores_defecto['rib']['prov'];

  $prov_suss=$valores_detalle['suss']['id_proveedor'] or 
     $prov_suss=$valores_defecto['suss']['prov'];
  
  $prov_mul=$valores_detalle['Multas']['id_proveedor'] or
    $prov_mul=$valores_defecto['Multas']['prov'];
 
  $prov_dep=$valores_detalle['Transferencia']['id_proveedor'] or
    $prov_dep=$valores_defecto['Transferencia']['prov'];

  $prov_otro=$valores_detalle['Otros']['id_proveedor'] or
     $prov_otro=$valores_defecto['Otros']['prov'];

  $prov_diferido=$valores_detalle['Cheque Diferido']['id_proveedor'] or
    $prov_diferido=$valores_defecto['Cheque Diferido']['prov'];

 $cto_iva=$valores_detalle['iva']['numero_cuenta'] or 
     $cto_iva=$valores_defecto['iva']['cta'];
 
 $cto_gan=$valores_detalle['ganancias']['numero_cuenta'] or 
     $cto_gan=$valores_defecto['ganancias']['cta'];
 
 $cto_rib=$valores_detalle['rib']['numero_cuenta'] or 
     $cto_rib=$valores_defecto['rib']['cta'];
 
 $cto_suss=$valores_detalle['suss']['numero_cuenta'] or 
     $cto_suss=$valores_defecto['suss']['cta'];
 
 $cto_mul=$valores_detalle['Multas']['numero_cuenta'] or 
   $cto_mul=$valores_defecto['Multas']['cta'];
   
 $cto_dep=$valores_detalle['transferencia']['numero_cuenta'] or 
      $cto_dep=$valores_defecto['Transferencia']['cta'];

 $cto_otro=$valores_detalle['Otros']['numero_cuenta'] or 
     $cto_otro=$valores_defecto['Otros']['cta'];
     
 $cto_diferido=$valores_detalle['Cheque Diferido']['numero_cuenta'] or 
   $cto_diferido=$valores_defecto['Cheque Diferido']['cta']; 
   
} else {  // si hubo error recupera datos del post
 $text_iva=$_POST['iva'];
 $text_gan=$_POST['ganancia'];
 $text_rib=$_POST['rib'];
 $text_suss=$_POST['suss'];
 $text_mul=$_POST['multas'];
 $text_dep=$_POST['deposito'];
 $text_otro=$_POST['otro'];
 $id_iva=$_POST['tipo_iva'];
 $id_gan=$_POST['tipo_gan'];
 $id_mul=$_POST['tipo_multas'];
 $id_rib=$_POST['tipo_rib'];
 $id_suss=$_POST['tipo_suss'];
 $id_dep=$_POST['tipo_dep'];
 $id_otro=$_POST['tipo_otro'];
 $prov_iva=$_POST['prov_iva'];
 $prov_gan=$_POST['prov_gan'];
 $prov_rib=$_POST['prov_rib'];
 $prov_suss=$_POST['prov_suss'];
 $prov_mul=$_POST['prov_multas'];
 $prov_dep=$_POST['prov_dep'];
 $prov_otro=$_POST['prov_otro'];
 $cto_iva=$_POST['concepto_iva'];
 $cto_gan=$_POST['concepto_gan'];
 $cto_rib=$_POST['concepto_rib'];
 $cto_suss=$_POST['concepto_suss'];
 $cto_mul=$_POST['concepto_multas'];
 $cto_dep=$_POST['concepto_dep'];
 $cto_otro=$_POST['concepto_otro'];
 $id_banco=$_POST['banco'];
 $id_deposito=$_POST['tipo_deposito'];
 $text_ficticio=$_POST['text_ficticio'];
 $text_cheque=$_POST['text_cheque'];
 $text_diferido=$_POST['text_diferido'];
 } 

if ($c_dep==1 || $_POST['chk_dep']==1) $det_visib = "";
  else $det_visib = "none";

  
  
?>
<table width="45%" align="center" border="1" cellspacing="2"  bordercolor="#000000">
<tr id="mo"><td colspan="6" align="center">DETALLES DEL EGRESO</td></tr>
<tr id=ma >
<td>&nbsp;</td>
<td>Descripción</td>
<td>Monto</td>
<td>Tipo Egreso</td>
<td>Proveedor</td>
<td>Cuenta y Plan</td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_iva" value=1 onclick="if (!this.checked) document.all.iva.value=''" <?if ( $c_iva==1 || $_POST['chk_iva']==1) echo 'checked' ?>> </td>
  <td align="right">IVA:</td>
  <td><input type="text" name='iva' value='<?=$text_iva?>' size="15" ></td>
  <td> <?=gen_select('tipo_iva',$res_egreso,$id_iva,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_iva',$res_prov,$prov_iva,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_iva',$res_concepto,$cto_iva);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_gan" value=1 onclick="if (!this.checked) document.all.ganancia.value=''"  <?if ($c_gan==1 || $_POST['chk_gan']==1) echo 'checked' ?>> </td>
  <td align="right">GANANCIAS:</td>
  <td><input type="text" name='ganancia' value='<?=$text_gan?>' size="15"></td>
  <td><?=gen_select('tipo_gan',$res_egreso,$id_gan,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_gan',$res_prov,$prov_gan,'id_proveedor','razon_social');?> </td>
  <td><?=gen_select_concepto('concepto_gan',$res_concepto,$cto_gan);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_rib" value=1 onclick="if (!this.checked) document.all.rib.value=''" <?if ($c_rib==1 || $_POST['chk_rib']==1) echo 'checked' ?>> </td>
  <td  align="right">RIB:</td>
  <td><input type="text" name='rib' value='<?=$text_rib?>' size="15"></td>
  <td> <?=gen_select('tipo_rib',$res_egreso,$id_rib,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_rib',$res_prov,$prov_rib,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_rib',$res_concepto,$cto_rib);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_suss" value=1 onclick="if (!this.checked) document.all.suss.value=''" <?if ($c_suss==1 || $_POST['chk_suss']==1) echo 'checked' ?>> </td>
  <td  align="right">SUSS:</td>
  <td> <input type="text" name='suss' value='<?=$text_suss?>' size="15"></td>
  <td> <?=gen_select('tipo_suss',$res_egreso,$id_suss,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_suss',$res_prov,$prov_suss,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_suss',$res_concepto,$cto_suss);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_mul" value=1 onclick="if (!this.checked) document.all.multas.value=''" <?if ($c_mul==1 || $_POST['chk_mul']==1) echo 'checked' ?> > </td>
  <td  align="right">MULTAS:</td>
  <td><input type="text" name='multas' value='<?=$text_mul?>' size="15" ></td>
  <td> <?=gen_select('tipo_multas',$res_egreso,$id_mul,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_multas',$res_prov,$prov_mul,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_multas',$res_concepto,$cto_mul);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_dep" value=1 
        onclick="if (!this.checked) { document.all.deposito.value=''; Ocultar('tabla_dep');}else Mostrar('tabla_dep'); " <?if ($c_dep==1 || $_POST['chk_dep']==1) echo 'checked' ?> > </td>
  <td  align="right">TRANSFERENCIA:</td>
  <td><input type="text" name='deposito' value='<?=$text_dep?>' size="15" ></td>
  <td><?=gen_select('tipo_dep',$res_egreso,$id_dep,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_dep',$res_prov,$prov_dep,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_dep',$res_concepto,$cto_dep);?></td>
</tr>

<tr id=ma><td colspan="6" align="left">
 <div id='tabla_dep' style='display:<?=$det_visib?>'>
   <table>
   <tr id=ma>
    <td width="20%"></td>
    <td> Banco: <?=gen_select('banco',$res_banco,$id_banco,'idbanco','nombrebanco');?> </td>
    <td>  Tipo de Depósito:<?=gen_select('tipo_deposito',$res_deposito,$id_deposito,'idtipodep','tipodepósito');?> </td>
   </tr>
   </table>
   </div>
 </td>

</tr>
<tr id=ma>
  <td><input type="checkbox" name="chk_otro" class='estilos_check' value=1 onclick="if (!this.checked) document.all.otro.value=''" <?if ($c_otro==1 || $_POST['chk_otro']==1) echo 'checked' ?>> </td>
  <td  align="right">OTROS:</td>
  <td><input type="text" name='otro' value='<?=$text_otro?>' size="15" ></td>
  <td><?=gen_select('tipo_otro',$res_egreso,$id_otro,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_otro',$res_prov,$prov_otro,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_otro',$res_concepto,$cto_otro);?></td>
</tr>

<tr id=ma>
  <td><input type="checkbox" name="chk_diferido" class='estilos_check' value=1 onclick="if (!this.checked) {document.all.diferido_monto.value=document.all.diferido.value;document.all.diferido.value='';}else document.all.diferido.value=document.all.diferido_monto.value;" <?if ($c_diferido==1 || $_POST['chk_diferido']==1) echo 'checked' ?>> </td>
  <td align="right" onclick="ventana_cheques = window.open('chequesdif_cobranza_atadas.php','','');" style="cursor:hand" title="Haga click aqui para ingresar cheques diferidos">CH. DIFERIDO:</td>
  <td><input type="text" name='diferido' value='<?=$text_diferido?>' readonly size="15">
  <input type="hidden" name='diferido_monto' value='<?=$text_diferido?>'>
  </td>
  <td><?=gen_select('tipo_diferido',$res_egreso,$id_diferido,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_diferido',$res_prov,$prov_diferido,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_diferido',$res_concepto,$cto_diferido);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class='estilos_check'  name="chk_ficticio" value=1 onclick="if (!this.checked) document.all.ficticio.value=''" <?if ($c_ficticio==1 || $_POST['chk_ficticio']==1) echo 'checked' ?>> </td>
  <td  align="right">EFECTIVO:</td>
  <td><input type="text" name='ficticio' value='<?=$text_ficticio?>' size="15" ></td>
  <td colspan=3>&nbsp; </td>
</tr>

<tr id=ma>
  <td><input type="checkbox" class='estilos_check' name="chk_cheque" value=1 onclick="if (!this.checked) document.all.cheque.value=''" <?if ($c_cheque==1 || $_POST['chk_cheque']==1) echo 'checked' ?>> </td>
  <td  align="right">CHEQUE:</td>
  <td><input type="text" name='cheque' value='<?=$text_cheque?>' size="15" ></td>
  <td colspan=3>&nbsp; </td>
</tr>
<tr id=ma>
  <td  align="right" colspan="2">TOTAL:</td>
   <td colspan="4"><input type="text" name='total' value='<?=$_POST['total']?>' size="15"  onFocus="if (control_num()) calcular_total(0)" title="Haga click para ver el total"></td>
 </tr>
</table>
<?if ($hay_detalle) {?>
<script>
calcular_total(0);
</script>
<?}?>
<br> 
<table align="center"> 
<tr>
 <?if (permisos_check("inicio","licitaciones_egreso_cob"))
               $permiso="";
            else
               $permiso=" disabled";
   if ($control_egreso==1 ) { $permiso_egresos=" disabled";
                              $title="LOS EGRESOS YA SE REGISTRARON EN CAJA "; }   
   else { 
   	  $permiso_egresos="";        
   	  $title="";
   }
  
   	  $ref = encode_link('lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_comp));

   	  $link=encode_link('egresos_atadas.php',array("datos_vta"=>comprimir_variable($datos_vta),"id_vta_atada"=>$id_vta_atada,"monto_original"=>$monto_original));
   	 //$link=encode_link('egresos_atadas.php',array("first"=>1));
      //onclick="if ( (cant_chequeados()) && (control_num()) && (calcular_total(1))) {<?=$onclick)};else return false; "
                
     ?>
   <td colspan="2" align="center"><input type="submit" name="seguimiento" value="Guardar Detalles" <?=$permiso_egresos?>  onclick="return( (cant_chequeados() > 0) && (control_num()));">          
   <td colspan="2" align="center">
       <input type="button" title="ingresa monto de los detalles en caja" name="aceptar" value="Guardar Egresos" <?=$permiso." ". $permiso_egresos;?> 
        onclick="if (cant_chequeados() && (control_num()) && (calcular_total(1))) window.open('egresos_atadas.php?first=1','','');">
   &nbsp;&nbsp;
   <? if ($id_datos_ingreso !=null || $id_datos_ingreso!="") { ?>
     <input type="button" name="cerrar" value="Cerrar" onclick="window.opener.location.href='<?=$ref?>'; window.close();">
    <? } else { ?>
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
    <? } ?>
    </td>
</tr>
</table>

<INPUT type="hidden" name="boton_aceptar" value="0">
<?
$cant_facturas=count($datos_vta);

for($i=0;$i<$cant_facturas;$i++) {?>
    <input type='hidden' name='montos_IVA_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_GANANCIA_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_RIB_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_SUSS_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_MULTAS_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_DEPOSITO_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_OTROS_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_DIFERIDO_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_CHEQUE_<?=$datos_vta[$i]['nro_factura']?>' value="">
    <input type='hidden' name='montos_EFECTIVO_<?=$datos_vta[$i]['nro_factura']?>' value="">
<? 
}?>
</form>
</body>
</html>
<?=fin_pagina()?>