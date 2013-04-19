<?
/*
$Author: mari $
$Revision: 1.25 $
$Date: 2006/11/29 20:54:15 $
*/

require_once("../../config.php");
require_once("func_cobranzas.php");
require_once("../contabilidad/funciones.php");

//guarda ingresos y egresos para 1 factura o para facturas atadas cuando no se realiza venta factura

//registra el egreso de cada detalle
if ($_POST['boton_aceptar']) {

$fecha=fecha_db($_POST['fecha']);
list($anio,$mes,$dia)=split("-",$fecha);
$dia = date('w', mktime(0,0,0,$mes,$dia,$anio));

$distrito=$_POST['caja'];
$id_moneda=$_POST['moneda_sel'];

$monto_factura=$_POST['monto_factura'];
$moneda_factura=$_POST['moneda_factura'];
$id_tipo_ingreso =$_POST['tipo_ing'];
$cuentaing=$_POST['cuentaing'];
$cliente=$_POST['cliente'];
$comentarios=$_POST['comentarios'];
$simbolo=$_POST['simbolo_total'];
$monto_total=$_POST['monto_total'];
$nombre_distrito=$_POST['nombre_distrito'];
$monto_total_factura=$_POST['monto_total_factura'];
$usuario=$_ses_user_name;

if ($_POST['dolar_actual'] !="" || $_POST['dolar_actual'] !=null) {
   $dolar_actual=$_POST['dolar_actual'];
   $dolar =" Valor Dolar ".formato_money($dolar_actual);
}
  else {
  	$dolar_actual=0; 
  	$dolar="";
  }


$valores=array_recibe($_POST['valores_defecto']);

   
//$monto=number_format($monto,"2",".","");

if($dia!=0 && !feriado(fecha($fecha)))   { //si dia no  es feriado o no es domingo 
 $db->StartTrans();
   //si la caja no exite la crea, sino recupera el id
    $query="select id_caja,fecha,cerrada from caja 
            where fecha='$fecha' and id_distrito=$distrito and id_moneda=$id_moneda";
    $caja_query=sql($query,"$query") or fin_pagina();
    $caja_cerrada=0;
                                         
    if($caja_query->RecordCount()==0) { 
 	     $query="select nextval('caja.caja_id_caja_seq') as id";
         $id_caja=sql($query) or fin_pagina();
         $id_caja=$id_caja->fields['id'];
	     $query="insert into caja(id_caja,id_distrito,id_moneda,fecha)values($id_caja,$distrito,$id_moneda,'$fecha')";
	     sql($query) or fin_pagina();
	     $caja_cerrada=0;
    }
    else {
         $id_caja=$caja_query->fields['id_caja'];
         $caja_cerrada=$caja_query->fields['cerrada'];
        }
                           
    if ($caja_cerrada==0) {
      if ($_POST['id_cobranza']) { 
    	$id_cobranza=$_POST['id_cobranza'];
    	$tipo_fact=$_POST['tipo_fact'];
    	$nro_factura=$_POST['nro_factura'];
        $id_licitacion=$_POST['id_licitacion'] or $id_licitacion=$_POST['lic'];
        $nombre_entidad=$_POST['nombre_entidad'];
    	if ($moneda_factura==1 and $id_moneda == 2)  {//pasa de pesos a dolares
   $monto_ingreso=$_POST['monto'] / $_POST['dolar_actual'];
}
 elseif ($moneda_factura==2 and $id_moneda == 1) { //pasa de dolares a pesos
   $monto_ingreso=$_POST['monto'] * $_POST['dolar_actual'];
 }
 else 
   $monto_ingreso=$_POST['monto'];
   
    	
        $item="F".$tipo_fact." ".$nro_factura." - ".$nombre_entidad;
        if (es_numero($id_licitacion))
        $item.=" - ID $id_licitacion";  
     
       
          $query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
          $id_ie=sql($query) or fin_pagina();
          $id=$id_ie->fields['id'];
          $sql_ingreso="INSERT into caja.ingreso_egreso 
                        (id_ingreso_egreso,id_caja,id_entidad,id_tipo_ingreso,monto,comentarios,usuario,fecha_creacion,item,id_cuenta_ingreso) values
                        ($id,$id_caja,$cliente,$id_tipo_ingreso,$monto_ingreso,'$comentarios','$usuario','$fecha','$item',$cuentaing)";
          sql($sql_ingreso) or fin_pagina();
          $sql_update="update cobranzas set id_ingreso_egreso=$id,cotizacion_dolar=$dolar_actual where id_cobranza=$id_cobranza";	
          sql($sql_update) or fin_pagina();
     } 
     elseif ($_POST['id_cob']) {
     	  $sql_ingreso="";
     	  $sql_update="";
          $id_cobranza=$_POST['id_cob']; //cobranza primaria
          $datos_fact=descomprimir_variable($_POST['datos_fact']);
          
         // print_r($datos_fact);
          $cant=count($datos_fact);
          $i=0;
          while ($i < $cant ) {
          	$monto_ingreso=$datos_fact[$i]['monto'];
          	
          	if ($moneda_factura==1 and $id_moneda == 2)  {//pasa de pesos a dolares
                $monto_ingreso=$datos_fact[$i]['monto'] / $_POST['dolar_actual'];
            }
           elseif ($moneda_factura==2 and $id_moneda == 1) { //pasa de dolares a pesos
                $monto_ingreso=$datos_fact[$i]['monto'] * $_POST['dolar_actual'];
           }
           else 
               $monto_ingreso=$datos_fact[$i]['monto'];
          	      	
          	$tipo_fact=$datos_fact[$i]['tipo_factura'];
          	$nro_factura=$datos_fact[$i]['nro_factura'];
          	$nombre_entidad=$datos_fact[$i]['nombre_entidad'];
          	$id_licitacion=$datos_fact[$i]['id_licitacion'];
          	$id_sec=$datos_fact[$i]['id'];
          	$cliente=$datos_fact[$i]['id_cliente'];
          	$item="F".$tipo_fact." ".$nro_factura." - ".$nombre_entidad;
            if (es_numero($id_licitacion))
             $item.=" - ID $id_licitacion";  
            $query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
           $id_ie=sql($query) or fin_pagina();
           $id=$id_ie->fields['id'];
           $sql_ingreso[]="INSERT into caja.ingreso_egreso 
                        (id_ingreso_egreso,id_caja,id_entidad,id_tipo_ingreso,monto,comentarios,usuario,fecha_creacion,item,id_cuenta_ingreso) values
                        ($id,$id_caja,$cliente,$id_tipo_ingreso,$monto_ingreso,'$comentarios','$usuario','$fecha','$item',$cuentaing)";
        
          $sql_update[]="update cobranzas set id_ingreso_egreso=$id,cotizacion_dolar=$dolar_actual where id_cobranza=$id_sec";	
          
          $i++;
          }
           sql($sql_ingreso) or fin_pagina();
           sql($sql_update) or fin_pagina();
     }
//egresos
          guarda_detalle_egresos();
          $fecha_creacion = date("Y-m-d H:m:s",mktime());
            
 $list_cobranza="";
 $list_factura="";
 if ($_POST['id_cob'] !="") { //facturas atadas
     $list_cob=$_POST['list_cob'];
     $l=strlen($lis_cob) -1;
     $lis=substr($list_cob,1,$l);
     $list_cobranza=explode(",",$lis);  //arreglo con todas los id de las facturas
     
     $list_fact=$_POST['list_fact'];
     $l=strlen($lis_fact) -1;
     $lis_f=substr($list_fact,1,$l);
     $list_factura=explode(",",$lis_f);  //arreglo con todas los nro de las facturas
    }    
    
    
 $tipo_f=strtoupper($tipo_fact);
 
 $egreso=array();
 $val_actual=array();  //guardo los valores seleccionados antes de guardar el egreso
 $i=0;
  
 if ($_POST['chk_iva']==1) {
 // recupera datos del iva
 $proveedor=$_POST['prov_iva'];
 $tipo_egreso=$_POST['tipo_iva'];
 $monto=$_POST['iva'];
 $nro_cuenta=$_POST['concepto_iva'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: IVA  Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=1 $and";
 $val_actual['IVA']['prov']=$proveedor;
 $val_actual['IVA']['cta']=$nro_cuenta;
 $val_actual['IVA']['tipo']=$tipo_egreso;
 
 }
 
 if ($_POST['chk_gan']==1) {
 // recupera datos de ganancias
 $proveedor=$_POST['prov_gan'];
 $tipo_egreso=$_POST['tipo_gan'];
 $monto=$_POST['ganancia'];
 $nro_cuenta=$_POST['concepto_gan'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Ganancias Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=2 $and";
 $val_actual['GANANCIAS']['prov']=$proveedor;
 $val_actual['GANANCIAS']['cta']=$nro_cuenta;
 $val_actual['GANANCIAS']['tipo']=$tipo_egreso;
 
 }
 
 if ($_POST['chk_rib'] ==1) {
 // recupera datos de rib
 $proveedor=$_POST['prov_rib'];
 $tipo_egreso=$_POST['tipo_rib'];
 $monto=$_POST['rib'];
 $nro_cuenta=$_POST['concepto_rib'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: RIB Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=3 $and";
 $val_actual['RIB']['prov']=$proveedor;
 $val_actual['RIB']['cta']=$nro_cuenta;
 $val_actual['RIB']['tipo']=$tipo_egreso;
  }
  
    
 if ($_POST['chk_suss'] ==1) {
 // recupera datos de suss
 $proveedor=$_POST['prov_suss'];
 $tipo_egreso=$_POST['tipo_suss'];
 $monto=$_POST['suss'];
 $nro_cuenta=$_POST['concepto_suss'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: SUSS Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=10 $and";
 $val_actual['SUSS']['prov']=$proveedor;
 $val_actual['SUSS']['cta']=$nro_cuenta;
 $val_actual['SUSS']['tipo']=$tipo_egreso;
  }
 
 if ($_POST['chk_mul'] ==1) {
 // recupera datos de multas
 $proveedor=$_POST['prov_multas'];
 $tipo_egreso=$_POST['tipo_multas'];
 $monto=$_POST['multas'];
 $nro_cuenta=$_POST['concepto_multas'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Multas Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=4 $and";
 $val_actual['MULTAS']['prov']=$proveedor;
 $val_actual['MULTAS']['cta']=$nro_cuenta;
 $val_actual['MULTAS']['tipo']=$tipo_egreso;
 }
 
 if ($_POST['chk_dep'] ==1)  {
 // recupera datos de deposito
 $proveedor=$_POST['prov_dep'];
 $tipo_egreso=$_POST['tipo_dep'];
 $monto=$_POST['deposito'];
 $nro_cuenta=$_POST['concepto_dep'];
 $banco=$_POST['banco'];
 $tipo=$_POST['tipo_deposito'];
 $tipo_f=strtoupper($tipo_fact);
 
 if (is_array($list_factura)) {
 	
  $comen= " F$tipo_f ";	
   foreach($list_factura as $key => $value)
     $comen.= $value."//\n";
  $comen=substr_replace($comen,'',(strrpos($comen,'/')));  
  $comen=substr_replace($comen,'',(strrpos($comen,'/')));  
  $comen.= "- $nombre_entidad - "; 
  $comen.= " ID $id_licitacion \n" ;
 }
 else {
 	
    $comen.=" F$tipo_f $nro_factura - $nombre_entidad - ID $id_licitacion";
 }
 
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $id_dep=guardar_deposito($banco,$fecha,$tipo,$monto,$comen);
 
 $egreso[$i++]="Id Egreso: ".$id." Descripción: transferencia Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,iddepósito=$id_dep,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=5 $and";
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
 $monto=$_POST['otro'];
 $nro_cuenta=$_POST['concepto_otro'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Otros Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=6 $and";
 $val_actual['OTROS']['prov']=$proveedor;
 $val_actual['OTROS']['cta']=$nro_cuenta;
 $val_actual['OTROS']['tipo']=$tipo_egreso;
 }
  
 if ($_POST['chk_diferido'] ==1) {
 // recupera datos de diferidos
 $proveedor=$_POST['prov_diferido'];
 $tipo_egreso=$_POST['tipo_diferido'];
 $monto=$_POST['diferido'];
 $nro_cuenta=$_POST['concepto_diferido'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Cheque Diferido Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=9";
 $val_actual['Cheques diferidos']['prov']=$proveedor;
 $val_actual['Cheques diferidos']['cta']=$nro_cuenta;
 $val_actual['Cheques diferidos']['tipo']=$tipo_egreso;
 
 $sql="update cheques_diferidos set activo = 1 where id_chequedif in (select id_chequedif from cheque_cobranza where id_cobranza=$id_cobranza);";
 sql($sql,"$sql") or fin_pagina();
 }
 if ($_POST['chk_ficticio'] ==1) {
 // recupera datos de efectivo
 
 $monto=$_POST['ficticio'];
 //$egreso[$i++]="Id Egreso: ".$id." Descripción: EFECTIVO (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
 $egreso[$i++]=" Descripción: EFECTIVO (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=7 $and";
 
 }
 
 if ($_POST['chk_cheque'] ==1) {
 // recupera datos de cheque
 $monto=$_POST['cheque'];
 //$egreso[$i++]="Id Egreso: ".$id." Descripción: CHEQUE (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
 $egreso[$i++]=" Descripción: CHEQUE (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=8 $and";
 }
 

 if ($_POST['chk_devp'] ==1) {
 // recupera datos de devolucion Prestamo
 $proveedor=$_POST['prov_devolucion'];
 $tipo_egreso=$_POST['tipo_devolucion'];
 $monto=$_POST['devolucion'];
 $nro_cuenta=$_POST['concepto_devolucion'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Devolución prestamo Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=11 $and";
 $val_actual['Devolucion prestamo']['prov']=$proveedor;
 $val_actual['Devolucion prestamo']['cta']=$nro_cuenta;
 $val_actual['Devolucion prestamo']['tipo']=$tipo_egreso;
 }
 if ($_POST['chk_int'] ==1) {  
 // recupera datos de intereses
 $proveedor=$_POST['prov_interes'];
 $tipo_egreso=$_POST['tipo_interes'];
 $monto=$_POST['interes'];
 $nro_cuenta=$_POST['concepto_interes'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Intereses Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=12 $and";
 $val_actual['intereses']['prov']=$proveedor;
 $val_actual['intereses']['cta']=$nro_cuenta;
 $val_actual['intereses']['tipo']=$tipo_egreso;
 
 // imputar pagos
 $pago[]=array();
 $pago["tipo_pago"]="id_ingreso_egreso";
 $pago["id_pago"]=$id;
 $_POST['cuentas']=$nro_cuenta;
 if ($id_moneda==2) { //si esta en dolares
	  $_POST["valor_dolar_imp"]=$dolar_actual;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_actual; 
 }
 else  $_POST['monto_neto']=$monto;
 imputar_pago($pago,"",fecha($fecha));
 }
 
 if ($_POST['chk_adm'] ==1) {
 // recupera datos de Gastos administrativos
 $proveedor=$_POST['prov_gastoadm'];
 $tipo_egreso=$_POST['tipo_gastoadm'];
 $monto=$_POST['gastoadm'];
 $nro_cuenta=$_POST['concepto_gastoadm'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Gastos Administrativos Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=13 $and";
 $val_actual['gastos adm']['prov']=$proveedor;
 $val_actual['gastos adm']['cta']=$nro_cuenta;
 $val_actual['gastos adm']['tipo']=$tipo_egreso;

 //imputar pagos
 $pago[]=array();
 $pago["tipo_pago"]="id_ingreso_egreso";
 $pago["id_pago"]=$id;
 $_POST['cuentas']=$nro_cuenta;
 if ($id_moneda==2) { //si esta en dolares
	  $_POST["valor_dolar_imp"]=$dolar_actual;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_actual; 
 }
 else  $_POST['monto_neto']=$monto;
 imputar_pago($pago,"",fecha($fecha));
 }
 
 if ($_POST['chk_com'] ==1) {
 // recupera datos de Comisiones
 $proveedor=$_POST['prov_comisiones'];
 $tipo_egreso=$_POST['tipo_comisiones'];
 $monto=$_POST['comisiones'];
 $nro_cuenta=$_POST['concepto_comisiones'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Comisiones Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=14 $and";
 $val_actual['comisiones']['prov']=$proveedor;
 $val_actual['comisiones']['cta']=$nro_cuenta;
 $val_actual['comisiones']['tipo']=$tipo_egreso;
 
 //imputar pagos
 $pago[]=array();
 $pago["tipo_pago"]="id_ingreso_egreso";
 $pago["id_pago"]=$id;
 $_POST['cuentas']=$nro_cuenta;
 if ($id_moneda==2) { //si esta en dolares
	  $_POST["valor_dolar_imp"]=$dolar_actual;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_actual; 
 }
 else  $_POST['monto_neto']=$monto;
 imputar_pago($pago,"",fecha($fecha));
 }
 
 
 if ($sql_detalle!="") {
 	 sql ($sql_detalle,"ERROR en $sql_detalle") or fin_pagina ();  
 }
 
 $para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";
  //no hace venta factura
  if ($_POST['id_cobranza']) { //una sola factura
//valores por defecto
    $valores_fact=array();
	$valores_fact['monto']=$_POST['monto_ant'];
    $valores_fact['tipo_ing']=$_POST['tipo_ingreso_ant'];
	$valores_fact['moneda']=$_POST['moneda_ant'];
	$valores_fact['entidad']=$_POST['entidad_ant'];
	$valores_fact['cuenta']=$_POST['tipo_cuenta_ant'];
	$valores_fact['dolar']=$_POST['dolar_ant'];
	
	$valores=array();
	$valores_ing['monto']=$monto_ingreso;
	$valores_ing['tipo_ing']=$id_tipo_ingreso;
	$valores_ing['moneda']=$id_moneda;
	$valores_ing['entidad']=$cliente;
	$valores_ing['cuenta']=$cuentaing;
	$valores_ing['dolar']=$dolar_actual;

	
    if ($valores_fact['moneda']==1) {
	   if ($valores_fact['monto'] > 5000) {
         $para.=',corapi@coradir.com.ar';
       }
    } elseif (($valores_fact['monto'] * $valores_fact['dolar']) > 5000) {
         $para.=',corapi@coradir.com.ar'; 
    }
	      $contenido1=datos_mail($id,$nro_factura,$distrito,$id_licitacion,$valores_fact,$valores_ing);
    }
    else { // facturas atadas
     if ($moneda_factura==1) {
	   if ($monto_total > 5000) {
         $para.=',corapi@coradir.com.ar';
       }
    } elseif (($monto_total * $dolar_actual) > 5000) {
         $para.=',corapi@coradir.com.ar'; 
    }
	$contenido1=datos_mail_atadas($_POST['list_cob'],$distrito);  
}	

$sql="select nombre from distrito where id_distrito=$distrito";
$res=sql($sql,"despues de mail de ingresos $sql") or fin_pagina();
$nombre_distrito=$res->fields['nombre'];
$asunto="Egresos desde cobranzas";
$contenido .="Se han realizado egresos desde cobranzas \n";
$contenido .="EN LA CAJA DE  ".str_replace("- GCBA"," ",$nombre_distrito);
$contenido.=" MONTO: ". $simbolo." ".formato_money($monto_total);
$contenido.= $dolar;
if (is_array($list_factura)) {
	$contenido.="Para las facturas: ";
foreach($list_factura as $key => $value)
  $contenido.= " Nro: ".$value."\n";
  if (es_numero($id_licitacion))
     $contenido.= " asociadas a licitacion $id_licitacion \n" ;
}
else {
  $contenido.=" Para la factura: NRO: ".$nro_factura;
  if (es_numero($id_licitacion))
      $contenido.= " asociada a licitacion $id_licitacion \n" ;
}

foreach($egreso as $key => $value)
  $contenido.= $value."\n";

  control_datos($val_actual);

  $contenidos=$contenido1."\n".$contenido;
  enviar_mail($para,$asunto,$contenidos,$nombre_archivo,$path_archivo,$type);

   
//cierra transaccion
if ($db->CompleteTrans()) { //finalizo la factura
   if ($_POST['id_cobranza']) { //una sola factura
       $sql= "UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='$_ses_user_name',
            fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza=".$_POST['id_cobranza'];
    
    if ($id_cobranza) $id_volver=$id_cobranza;
    else $id_volver=$_POST['id_cob'];
    
    //finaliza la venta de factura de bancos
    //cero indica que no esta atada en seguimiento de cobros
    //finalizar_vta_factura($id_volver,0);
    $ref = encode_link('../licitaciones/lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_volver));
   }
   else {
       $list_cob=$_POST['list_cob'];
       $sql= "UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='$_ses_user_name',
              fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza in $list_cob";
       if ($id_cobranza) $id_volver=$id_cobranza;
          else $id_volver=$_POST['id_cob'];
          $ref = encode_link('../licitaciones/lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_volver));
     //finaliza la venta de factura de bancos
    //uno indica que esta atada en seguimiento de cobros
    //finalizar_vta_factura($id_volver,1);     
   }
   sql($sql,"$sql") or fin_pagina();
 

if (!$error) { ?>
    <script>
      window.opener.location.href='<?=$ref?>';
      window.close();
  </script>
<?
}
}

} else   Error ("La Caja esta Cerrada. Seleccione fecha de la caja"); 

} else Error("Usted esta intentando insertar un egreso de un dia que no es habil");

//$db->CompleteTrans();
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
</script>

<script>
//controla que se haya ingresado un numero valido para el valor del dolar del ingreso
function control_dolar (moneda) {
if (document.all.moneda_sel[0].checked== true )  //pesos
    valor=document.all.moneda_sel[0].value;
if  (document.all.moneda_sel[1].checked == true ) //dolares
 valor=document.all.moneda_sel[1].value;
	
if ( (moneda==1) && (valor == 1)) { //si la factura es $ i se selecciona moneda $
	return true;
} else {
  if (isNaN(document.all.dolar_actual.value) || document.all.dolar_actual.value=="" || document.all.dolar_actual.value=='0.00'){
      alert ("Ingrese un número valido para el valor del dolar");
      return false;   
  }
}
return true;
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
if (document.all.chk_devp.checked==true) 
    sum++;    
if (document.all.chk_int.checked==true) 
    sum++;    
if (document.all.chk_adm.checked==true) 
    sum++;    
if (document.all.chk_com.checked==true) 
    sum++;    
if (sum==0) alert ('Debe Elegir al menos un detalle');     
wddxSerializer = new WddxSerializer();
MyWDDXPacket = wddxSerializer.serialize(cheques_diferidos);
document.all.cheques_diferidos.value=MyWDDXPacket;
return sum;     
}	


//muestra el monto de la afctura en caso que se cambie la moneda
function convertir() {
var num
var mon_factura;

mon_factura=document.all.moneda_factura.value;
if (document.all.moneda_sel[0].checked== true )  //pesos
    mon_sel=document.all.moneda_sel[0].value;
if  (document.all.moneda_sel[1].checked == true ) //dolares
 mon_sel=document.all.moneda_sel[1].value;
if (mon_factura != mon_sel) {
if (mon_factura==1 && mon_sel==2) {  //se pesos a dolares
  document.all.simbolo_total.value='U$S';	
  num=document.all.monto_factura.value/document.all.dolar_actual.value;
  
}
else if (mon_factura==2 && mon_sel==1)  {//de dolares a pesos 
  document.all.simbolo_total.value='$';	
  num=document.all.monto_factura.value*document.all.dolar_actual.value;
}
var s= new String(num.toFixed(2));
document.all.monto_total.value=s;
}
}

function mostrar_original() {
 var num;
 document.all.simbolo_total.value='$';
 document.all.monto_total.value=document.all.monto_factura.value;
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

if (document.all.chk_devp.checked==true) {
if (isNaN(document.all.devolucion.value) || parseFloat(document.all.devolucion.value)=='0' || document.all.devolucion.value=="") {
    alert ('Debe ingresar número valido para el campo Devolución Prestamo ');
    document.all.devolucion.value="";
    return false;
}

if (document.all.tipo_devolucion.options[document.all.tipo_devolucion.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el Devolución Prestamo ');
   return false;
}
if (document.all.prov_devolucion.options[document.all.prov_devolucion.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el Devolución Prestamo ');
   return false;
}
if (document.all.concepto_devolucion.options[document.all.concepto_devolucion.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el Devolución Prestamo');
   return false;
}
}

if (document.all.chk_int.checked==true) {
if (isNaN(document.all.interes.value) || parseFloat(document.all.interes.value)=='0' || document.all.interes.value=="") {
    alert ('Debe ingresar número valido para el campo Intereses ');
    document.all.interes.value="";
    return false;
}

if (document.all.tipo_interes.options[document.all.tipo_interes.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el campo Intereses ');
   return false;
}
if (document.all.prov_interes.options[document.all.prov_interes.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el campo Intereses ');
   return false;
}
if (document.all.concepto_interes.options[document.all.concepto_interes.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el campo Intereses');
   return false;
}
}

if (document.all.chk_adm.checked==true) {
if (isNaN(document.all.gastoadm.value) || parseFloat(document.all.gastoadm.value)=='0' || document.all.gastoadm.value=="") {
    alert ('Debe ingresar número valido para el campo Gastos Adm.');
    document.all.gastoadm.value="";
    return false;
}

if (document.all.tipo_gastoadm.options[document.all.tipo_gastoadm.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el Gastos Adm.');
   return false;
}
if (document.all.prov_gastoadm.options[document.all.prov_gastoadm.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el Gastos Adm.');
   return false;
}
if (document.all.concepto_gastoadm.options[document.all.concepto_gastoadm.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el Gastos Adm.');
   return false;
}
}


if (document.all.chk_com.checked==true) {
if (isNaN(document.all.comisiones.value) || parseFloat(document.all.comisiones.value)=='0' || document.all.comisiones.value=="") {
    alert ('Debe ingresar número valido para el campo Comisiones');
    document.all.comisiones.value="";
    return false;
}

if (document.all.tipo_comisiones.options[document.all.tipo_comisiones.selectedIndex].value==-1) {
   alert ('Debe seleccionar Tipo de egreso para el Comisiones');
   return false;
}
if (document.all.prov_comisiones.options[document.all.prov_comisiones.selectedIndex].value==-1) {
   alert ('Debe seleccionar Proveedor para el Comisiones');
   return false;
}
if (document.all.concepto_comisiones.options[document.all.concepto_comisiones.selectedIndex].value==-1) {
   alert ('Debe seleccionar Concepto y plan para el Comisiones');
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
if (document.all.chk_devp.checked==true)    
     sum+=parseFloat (document.all.devolucion.value);
if (document.all.chk_int.checked==true)    
     sum+=parseFloat (document.all.interes.value);
if (document.all.chk_adm.checked==true)    
     sum+=parseFloat (document.all.gastoadm.value);
if (document.all.chk_com.checked==true)    
     sum+=parseFloat (document.all.comisiones.value);
    
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

function control_fecha() {

 if(document.all.fecha.value=="")
 {alert('Debe ingresar una fecha valida');
  return false;
 }
  else if ( (typeof(document.all.fecha.value)!='undefined') && (!es_mayor('fecha'))) {
  alert('Los ingresos o egresos deben tener fecha mayor o igual a la fecha de hoy');
  return false;
 }
 return true;
}

</script>

<form action="elegir_caja.php" method="post" name='form1'>
<input type="hidden" name="cheques_diferidos" id="cheques_diferidos" value="">
 
<? 
if ($error) 
   $fecha=fecha_db($_POST['fecha']);
else 
   $fecha=date("Y-m-d");
   
$id_cobranza = $parametros['id_cobranza'] or $id_cobranza=$_POST['id_cobranza'];
$id_cob=$parametros['id_cob'] or $id_cob=$_POST['id_cob'];
$id_comp=$id_cobranza or $id_comp=$id_cob;

if ($id_cob != "" || $id_cob!=null)  { //se esta realizando  ingreso/egreso de una factura atada
//falta sacar las anuladas
$list='(';
$list.=$id_cob.",";
$sql="select id_secundario from licitaciones.atadas where id_primario=$id_cob";
$res=sql($sql,"$sql") or fin_pagina();
while (!$res->EOF) {
	$list.=$res->fields['id_secundario'].",";
    $res->MoveNext();
}

$list=substr_replace($list,')',(strrpos($list,',')));

$sql="select id_distrito,caja.id_moneda,cotizacion_dolar
      from licitaciones.cobranzas
      left join caja.ingreso_egreso using (id_ingreso_egreso)
      left join caja.caja using (id_caja)
      where id_cobranza in $list";

$res=sql($sql,"$sql") or fin_pagina();

$id_caja=$res->fields['id_distrito'];
$id_moneda=$res->fields['id_moneda'];
$cotizacion_dolar=number_format($res->fields['cotizacion_dolar'],"2",".","");
}


if ($id_moneda=="" || $id_moneda==null) { //no hay ingresos anteriores 
  $id_moneda=$_POST['moneda_sel'] or $id_moneda=$parametros['moneda_factura']; 
  $deshabilitar=0; 
}
else $deshabilitar=1;  //hay ingresos anteriores

$monto=$parametros['monto'] or $monto=$_POST['monto'] ;
$tipo_fact=$parametros['tipo_fact'] or $tipo_fact=$_POST['tipo_fact'];
$id_cliente=$parametros['id_cliente'] or $id_cliente=$_POST['id_cliente']; 
$id_licitacion=$parametros['id_licitacion'] or $id_licitacion=$_POST['id_licitacion'];
$nro_factura=$parametros['nro_factura'] or $nro_factura=$_POST['nro_factura'];
$id_factura=$parametros['id_factura'] or $id_factura=$_POST['id_factura'];
$monto_factura=$parametros['monto'] or $monto_factura=$_POST['monto'];
$moneda_factura=$parametros['moneda_factura'] or $moneda_factura=$_POST['moneda_factura']; //moneda de la factura original
$id_caja=$_POST['caja']; //moneda de la factura original

?>

<input type="hidden" name="monto" value="<?=$monto?>">
<input type="hidden" name="tipo_fact" value="<?=$tipo_fact ?>">
<input type="hidden" name="moneda_factura" value="<?=$moneda_factura?>">
<input type="hidden" name="id_cliente" value="<?=$id_cliente?>">
<input type="hidden" name="id_cobranza" value="<?=$id_cobranza?>">
<input type="hidden" name="id_cob" value="<?=$id_cob?>">
<input type="hidden" name="nro_factura" value="<?=$nro_factura?>">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<input type="hidden" name="id_factura" value="<?=$id_factura?>">
<input type="hidden" name="id_caja" value="<?=$id_caja?>">
<input type="hidden" name="moneda_ingreso" value="<?=$id_moneda?>">

<?  /*if ($deshabilitar==1)
   echo "<div align='center'><font color=blue><b>LOS INGRESOS DE FACTURAS ATADAS SE REALIZAN EN IGUAL CAJA Y CON IGUAL MONEDA. <br> SI LOS DATOS NO COINCIDEN DEBERA DESATARLAS</b></font></div><br>"; */?>

<table align="center">
 <tr><td>
 <table class="bordes" align="center"> 
  <tr id=mo> <td colspan="2"> SELECCIONE DISTRITO </td></tr>
  <tr bgcolor=<?=$bgcolor_out?>>
   <td><input type="radio" name="caja" value=1 <?if ($id_caja=="") echo 'checked'; elseif ($id_caja==1) echo 'checked'; elseif ($deshabilitar==1) echo 'disabled' ?> > </td>
   <td> CAJA SAN LUIS </td>
  </tr>
  <tr bgcolor=<?=$bgcolor_out?>>
   <td><input type="radio" name="caja" value=2 <? if ($id_caja==2) echo 'checked'; elseif($deshabilitar==1) echo 'disabled' ?>> </td>
   <td> CAJA BS AS </td>
  </tr>
  </table>
  </td>
<td>&nbsp; </td>
<td>
<table class="bordes">
<? $nbre_tabla='tabla_dolar';  ?>
<tr id=mo><td colspan="2"> SELECCIONE MONEDA </td></tr>
<tr bgcolor=<?=$bgcolor_out?>>
<td><input type="radio" name="moneda_sel" value=1 <? if ($id_moneda==1) echo 'checked';elseif($deshabilitar==1) echo 'disabled'?>  
  onclick='<? if ($deshabilitar!=1 && $id_moneda==1) { ?> if (this.checked) {Ocultar("<?=$nbre_tabla?>");mostrar_original("<?=$id_moneda?>");} <?}?>' > </td>
<td> PESOS </td>
</tr>
<tr bgcolor=<?=$bgcolor_out?>>
<td><input type="radio" name="moneda_sel" value=2 <? if($id_moneda==2) echo 'checked';elseif($deshabilitar==1) echo 'disabled' ?> 
  <?if ($deshabilitar!=1 && $id_moneda==2) {?> onclick='mostrar_original("<?=$id_moneda?>");' <?} 
    else if ($deshabilitar!=1) {?>  onclick='javascript:(this.checked)?Mostrar("tabla_dolar"):Ocultar("tabla_dolar");' <?}?>> </td>
<td> DOLARES </td>
</tr>
</table>
</td>
</tr>
</table>

<?
$sql_moneda="select simbolo from moneda where id_moneda=$moneda_factura";
$res_moneda=sql($sql_moneda,"EN elegir caja 1 $sql_moneda") or fin_pagina();

if ($moneda_factura==2) { //si es dolar
if ($id_factura!="" || $id_factura!=null) {
$sql_fact="select cotizacion_dolar from facturas where id_factura=$id_factura";
$res_fact=sql($sql_fact,"$sql_fact") or fin_pagina();
}
}

if ($id_cliente !=null || $id_cliente!="") {
	$sql_cliente="select nombre from entidad where id_entidad=$id_cliente";
}
elseif ($id_factura!=null || $id_factura!="") {
  $sql_cliente="select cliente as nombre from facturas where id_factura=$id_factura";
}
if ($sql_cliente != "")
 $res_cliente=sql($sql_cliente) or fin_pagina();

?>
<? if ($id_moneda==1 && $moneda_factura!=2) {
	$det_visib = "none"; 
}

if  ($deshabilitar==1 && $id_moneda==2) 
	 $des_dolar=1;
else $des_dolar=0;

?>
<input type="hidden" name="cotizacion_dolar" value='<?=$res_fact->fields['cotizacion_dolar']?>'>
<br>
<? if ($id_cob != "" || $id_cob!=null)  { 
    //facturas atadas 
$datos_fact=array();
$ind=0;
//FACTURAS PRIMARIAS
  $sql="SELECT id_cobranza,cobranzas.id_licitacion,cobranzas.id_moneda,moneda.simbolo,entidad.nombre,
        cobranzas.id_entidad,cobranzas.nro_factura,
        cobranzas.monto_original as monto,id_factura, 
        facturas.estado,facturas.tipo_factura, 
        facturas.id_entidad as entidad_factura,facturas.cotizacion_dolar
        FROM licitaciones.cobranzas 
        left join licitaciones.moneda using (id_moneda) 
        left join licitaciones.entidad using (id_entidad) 
        left join facturacion.facturas using (id_factura)
        WHERE id_cobranza=$id_cob";
  
  $res_cob=sql($sql) or fin_pagina();

if ($res_cob->fields['estado'] !='a') {
   if ($res_cob->fields['id_entidad'] != null || $res_cob->fields['id_entidad'] != "")  {
      $entidad_prim=$res_cob->fields['id_entidad']; 
      $nombre_entidad_p=$res_cob->fields['nombre'];
   }
   elseif ($res_cob->fields['entidad_factura']!=null || $res_cob->fields['entidad_factura']!= "") { 
   	 $entidad_prim=$res_cob->fields['entidad_factura'];
  	 $sql="select nombre from entidad where id_entidad=$entidad_prim";
     $res=sql($sql) or fin_pagina();
     $nombre_entidad_p=$res->fields['nombre'];
   }
   $datos_fact[$ind]['id_licitacion']=$res_cob->fields['id_licitacion'];
   $datos_fact[$ind]['nro_factura']=$res_cob->fields['nro_factura'];
   $datos_fact[$ind]['tipo_factura']=$res_cob->fields['tipo_factura'];
   $datos_fact[$ind]['nombre_entidad']=$nombre_entidad_p;
   $datos_fact[$ind]['monto']=$res_cob->fields['monto'];
   $datos_fact[$ind]['id']=$res_cob->fields['id_cobranza'];
   $datos_fact[$ind]['id_cliente']=$entidad_prim;
   $ind++;
   
  } 
 
//facturas atadas
 	$sql_atadas="SELECT id_cobranza,cobranzas.id_licitacion,cobranzas.id_moneda,moneda.simbolo,entidad.nombre,
		         facturas.estado, cobranzas.id_entidad,cobranzas.nro_factura,cobranzas.monto,id_factura,
				 facturas.tipo_factura,facturas.id_entidad as entidad_factura,facturas.cotizacion_dolar, 
				 cobranzas.cotizacion_dolar as dolar_ingreso,cobranzas.monto_original as monto 
				 FROM licitaciones.atadas join licitaciones.cobranzas on atadas.id_secundario=cobranzas.id_cobranza 
				 left join licitaciones.moneda using (id_moneda) left join licitaciones.entidad using (id_entidad) 
		 		 left join facturacion.facturas using (id_factura) 
				 WHERE id_primario=$id_cob";
 	
$res_atadas=sql($sql_atadas) or fin_pagina();   
$tipo_fact=$res_cob->fields['tipo_factura'] or $tipo_fact=$res_atadas->fields['tipo_factura'];
 
?>

<input type="hidden" name="id_distrito" value='<?=$id_distrito?>'>

<br>
<table align="center">	
  <tr id="ma" bordercolor="#000000"> 
    <td align="center" colspan=6> <font color="blue"> DATOS DE LA FACTURA  </font></td>
  </tr>
  <tr id="mo">
     <td> Nro Factura</td>
     <td> ID LIC</td>
     <td> Cliente</td>
     <td> Tipo Factura</td>
     <td> Monto</td>
     <td> Dolar Factura</td>
  </tr>
<? if ($res_cob->fields['id_licitacion'] !=null || $res_cob->fields['id_licitacion']!="")
	    $lic=$res_cob->fields['id_licitacion'];
	    else $lic=-1;
	
	$monto_total=0;
	$list_cob='(';
	$list_fact='(';    
	    ?>
	<?if ($res_cob->RecordCount()>0 && $res_cobl->fields['estado'] != 'a') { 
		?>    
	<tr bgcolor=<?=$bgcolor_out?>>
	<td align="center"> <?=$res_cob->fields['nro_factura']?></td>
    <td align="center"> <?=$res_cob->fields['id_licitacion']?></td>
    <td align="center"> <?=$nombre_entidad_p?></td>
    <td align="center"> <?=strtoupper($res_cob->fields['tipo_factura'])?></td>
    <td align="center"> <?=$res_cob->fields['simbolo']." ".formato_money($res_cob->fields['monto'])?></td>
    <td align="center"><?=formato_money($res_cob->fields['cotizacion_dolar'])?> </td>
    </tr>
<?
 
    $monto_total+=$res_cob->fields['monto'];
    $list_cob.=$id_cob.",";  //concateno cob primaria
    $list_fact.=$res_cob->fields['nro_factura'].",";  //concateno nro_factura primaria
	}
	
	   
 $res_atadas->MoveFirst();
      while (!$res_atadas->EOF) { 
      
      if ($res_atadas->fields['estado'] !='a') {
      if ($res_atadas->fields['id_entidad'] !=null || $res_atadas->fields['id_entidad'] !="")  {
          $id_entidad_s=$res_atadas->fields['id_entidad'];
          $nombre_entidad_s=$res_atadas->fields['nombre'];
          }
      elseif ($res_atadas->fields['entidad_factura'] !=null || $res_atadas->fields['entidad_factura']!="") { 
      	 $id_entidad_s=$res_atadas->fields['entidad_factura'];
         $sql="select nombre from entidad where id_entidad=$id_entidad_s";
         $res=sql($sql) or fin_pagina();
         $nombre_entidad_s=$res->fields['nombre'];
         } 	
      
       $list_cob.=$res_atadas->fields['id_cobranza'].",";
       $list_fact.=$res_atadas->fields['nro_factura'].",";
     
    ?>
    <tr bgcolor=<?=$bgcolor_out?>>
      <? if ($res_atadas->fields['id_licitacion'] !=null || $res_atadas->fields['id_licitacion']!="")
	    $lic=$res_atadas->fields['id_licitacion'];
	    else $lic=-1;
	    ?>
      <td align="center"> <?=$res_atadas->fields['nro_factura']?></td>
      <td align="center"> <?=$res_atadas->fields['id_licitacion']?></td>
      <td align="center"> <?=$nombre_entidad_s?></td>
      <td align="center"> <?=strtoupper($res_atadas->fields['tipo_factura'])?></td>
      <td align="center"> <?=$res_atadas->fields['simbolo']." ".formato_money($res_atadas->fields['monto'])?></td>
      <td align="center"><?=formato_money($res_atadas->fields['cotizacion_dolar'])?> </td>
     
    </tr>
    <? 
     $monto_total+=$res_atadas->fields['monto'];
     
    }
   $datos_fact[$ind]['id_licitacion']=$res_atadas->fields['id_licitacion'];
   $datos_fact[$ind]['nro_factura']=$res_atadas->fields['nro_factura'];
   $datos_fact[$ind]['tipo_factura']=$res_atadas->fields['tipo_factura'];
   $datos_fact[$ind]['nombre_entidad']=$nombre_entidad_s;
   $datos_fact[$ind]['id_cliente']=$id_entidad_s;
   $datos_fact[$ind]['monto']=$res_atadas->fields['monto'];
   $datos_fact[$ind]['id']=$res_atadas->fields['id_cobranza'];
   $ind++;
$res_atadas->MoveNext(); 
} ?>
</table>
<?
$list_cob=substr_replace($list_cob,')',(strrpos($list_cob,',')));
$list_fact=substr_replace($list_fact,')',(strrpos($list_fact,',')));
?>
<input type="hidden" name="list_cob" value="<?=$list_cob?>">
<input type="hidden" name="list_fact" value="<?=$list_fact?>">
<input type="hidden" name="lic" value="<?=$lic?>">
<input type="hidden" name="nombre_cliente" value='<?=$nombre_entidad_s?>'>
<input type="hidden" name="tipo_fact" value='<?=$tipo_fact?>'>
<input type="hidden" name="datos_fact" value='<?=comprimir_variable($datos_fact)?>'>


<? 
$monto_total=number_format($monto_total,"2",".","");
echo "<input type='hidden' name='monto_factura' value='$monto_total'>";
}
  else {?>
 <input type="hidden" name="monto_factura" value="<?=$monto_factura?>">
<table align="center">
<tr><td>
  <table align="center" class="bordes">	
	<tr>
	    <td id=mo> Nro Factura</td>
        <td id=mo> ID LIC</td>
        <td id=mo> Cliente</td>
        <td id=mo> Tipo Factura</td>
        <td id=mo> Monto</td>
        <td id=mo> Valor Dolar</td> 
    </tr>
    <tr bgcolor=<?=$bgcolor_out?>>
        <td align="center" ><?=$nro_factura ?></td>
        <td align="center"><?=$id_licitacion?></td>
        <td align="center"> <?=$res_cliente->fields['nombre']?></td>
        <td align="center"> <?=strtoupper($tipo_fact)?></td>
        <td align="center"> <?=$res_moneda->fields['simbolo']." ".formato_money($monto)?></td>
        <td align="center"> <?=formato_money($res_fact->fields['cotizacion_dolar'])?></td>
    </tr>
</table>  
</td>   
</tr>
</table>
<?
  $monto_total=number_format($monto_factura,"2",".","");}  

$nombre_entidad=$res_cliente->fields['nombre'] or $nombre_entidad=$_POST['nombre_entidad'];
?>
<input type='hidden' name="nombre_entidad" value='<?=$nombre_entidad?>'>
<br>
<?
if (($id_cob != "" || $id_cob!=null)) $id_entidad=$entidad_prim;
 else $id_entidad=$id_cliente;
$id_tipo=retorna_id('tipo_ingreso','id_tipo_ingreso','nombre','Cobros','ilike');
$id_cuenta=retorna_id('tipo_cuenta_ingreso','id_cuenta_ingreso','nombre','Cobros (Facturas Clientes)','ilike');
     
 if ($id_entidad!="")
      $nombre_entidad=retorna_id('entidad','nombre','id_entidad',$id_entidad,'=');
$filtro=$_POST['filtro'] or $filtro=$filtro=strtoupper(substr($nombre_entidad,0,1));
 echo "<input type='hidden' name='filtro' value='$filtro'>";  
//query para generar los select
 $query="SELECT id_tipo_ingreso,nombre FROM caja.tipo_ingreso order by nombre";
 $res_ingreso=sql($query) or fin_pagina();
 $query="SELECT id_entidad,nombre FROM entidad where nombre ilike '$filtro%' order by nombre";
 $res_entidad=sql($query) or fin_pagina();
 $sql="select nombre,id_cuenta_ingreso from tipo_cuenta_ingreso order by nombre";
 $res_cuenta=sql($sql) or fin_pagina();
 
 
if ($moneda_factura!=$id_moneda) { 
$sql_moneda="select simbolo from moneda where id_moneda=$id_moneda";
$res_moneda=sql($sql_moneda,"en elegir caja 2$sql_moneda") or fin_pagina();
}
$simbolo_ingreso=$res_moneda->fields['simbolo'];
?>
<table align="center">
   <tr>
      <td> FECHA <input type="text" size=11 name="fecha" readonly value=<?=fecha($fecha);?>><?= link_calendario("fecha")?></td>
      <td> &nbsp;&nbsp;&nbsp;<font color="Red"><b>MONTO TOTAL</b> </font> </td>
      <td> 
        <input name="simbolo_total" type="text" style="text-align:right; background:inherit; border:none" value="<?=$simbolo_ingreso?>" readonly size="3" >
        <input name="monto_total"   type="text" style="text-align:left; background:inherit; border:none" value="<?if ($_POST['monto_total']) echo $_POST['monto_total']; else echo $monto_total;?>" readonly size="15" >
      </td>
      <td  rowspan="2">
           <div id='tabla_dolar' style='display:<?=$det_visib?>'>
           <table border=0 cellspacing=0 cellpadding=0>
           <tr bgcolor=<?=$bgcolor_out?>> <td > <input type='text' name="dolar_actual" size="10" onblur="convertir()" value="<?=$cotizacion_dolar?>" <?if ($des_dolar==1) echo 'readonly'?>> </td></tr>
           </table>
           </div> 
       </td>
  </tr>
</table>   
<br>

<table align="center" border="1" cellspacing="2"  bordercolor="#000000">
<tr id="mo"><td colspan="3"> DETALLES DEL INGRESO </td></tr>
<tr><td colspan=3><? tabla_filtros_nombres();?></td></tr>
<tr id='ma'>
    <td>Tipo Ingreso</td>
    <td>Cliente</td>
    <td>Tipo Cuenta</td>
</tr>
<tr>
   <td> <?=gen_select('tipo_ing',$res_ingreso,$id_tipo,'id_tipo_ingreso','nombre',$des);?></td>
   <td> <?=gen_select('cliente',$res_entidad,$id_entidad,'id_entidad','nombre',$des);?></td>
   <td> <?=gen_select('cuentaing',$res_cuenta,$id_cuenta,'id_cuenta_ingreso','nombre',$des);?></td>
</tr>
</table>
 
   <input type="hidden" name="monto_ant" value="<?=$monto_factura?>">  <!---monto de la factura--->
   <input type="hidden" name="tipo_cuenta_ant" value="<?=$id_cuenta?>">
   <input type="hidden" name="moneda_ant" value="<?=$id_moneda?>"> <!--moneda de la factura -->
   <input type="hidden" name="tipo_ingreso_ant" value="<?=$id_tipo?>">
   <input type="hidden" name="entidad_ant" value="<?=$id_entidad?>">
   <input type="hidden" name="dolar_ant" value="<?=$res_fact->fields['cotizacion_dolar']?>">
 

<?//EGRESOS
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

if ($_POST['valores_defecto']) 
    $valores_defecto=array_recibe($_POST['valores_defecto']);
else 
   $valores_defecto=armar_valores_defecto();
   
//serializo el arreglo
$valores=array_envia($valores_defecto);
 
?> <input type="hidden" name="valores_defecto" value="<?=$valores?>"> <?
if (!$error) {
 	
 if ($id_cobranza !="") $id=$id_cobranza;
     else $id=$id_cob;  //id_primaria
 
  //recupera los datos almacenados en detalle_egresos   
  //$valores_detalle=armar_valores_detalle ($id,$id_detalle_ingreso);
  $valores_detalle=armar_valores_detalle($id);
    
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
  
  $text_devp=$valores_detalle['Devolucion Prestamo']['monto_detalle'];
   if ($text_devp !="" || $text_devp!=null) {
      $c_devp=1;
      $text_devp=number_format($text_devp,2,".","");
   }
  
   $text_int=$valores_detalle['Intereses']['monto_detalle'];
   if ($text_int !="" || $text_int!=null) {
      $c_int=1;
      $text_int=number_format($text_int,2,".","");
  }
  
  $text_adm=$valores_detalle['Gastos Administrativos']['monto_detalle'];
   if ($text_adm !="" || $text_adm!=null) {
      $c_adm=1;
      $text_adm=number_format($text_adm,2,".","");
  }
  
  $text_com=$valores_detalle['Comisiones']['monto_detalle'];
   if ($text_com !="" || $text_com!=null) {
      $c_com=1;
      $text_com=number_format($text_com,2,".","");
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

 $id_devp=$valores_detalle['Devolucion Prestamo']['id_tipo_egreso'] or 
     $id_devp=$valores_defecto['Devolucion Prestamo']['tipo']; 
 
 $id_int=$valores_detalle['Intereses']['id_tipo_egreso'] or 
     $id_int=$valores_defecto['Intereses']['tipo'];     
 
 $id_adm=$valores_detalle['Gastos Administrativos']['id_tipo_egreso'] or 
     $id_adm=$valores_defecto['Gastos Administrativos']['tipo']; 
    
 $id_com=$valores_detalle['Comisiones']['id_tipo_egreso'] or 
     $id_com=$valores_defecto['Comisiones']['tipo'];      
     
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

  $prov_devp=$valores_detalle['Devolucion Prestamo']['id_proveedor'] or 
     $prov_devp=$valores_defecto['Devolucion Prestamo']['prov']; 
 
  $prov_int=$valores_detalle['Intereses']['id_proveedor'] or 
     $prov_int=$valores_defecto['Intereses']['prov'];     
 
  $prov_adm=$valores_detalle['Gastos Administrativos']['id_proveedor'] or 
     $prov_adm=$valores_defecto['Gastos Administrativos']['prov']; 
    
  $prov_com=$valores_detalle['Comisiones']['id_proveedor'] or 
     $prov_com=$valores_defecto['Comisiones']['prov'];   
   
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
   
 $cto_dep=$valores_detalle['Transferencia']['numero_cuenta'] or 
      $cto_dep=$valores_defecto['Transferencia']['cta'];

 $cto_otro=$valores_detalle['Otros']['numero_cuenta'] or 
     $cto_otro=$valores_defecto['Otros']['cta'];
     
 $cto_diferido=$valores_detalle['Cheque Diferido']['numero_cuenta'] or 
   $cto_diferido=$valores_defecto['Cheque Diferido']['cta']; 
   
 $cto_devp=$valores_detalle['Devolucion Prestamo']['numero_cuenta'] or 
     $cto_devp=$valores_defecto['Devolucion Prestamo']['cta']; 
 
  $cto_int=$valores_detalle['Intereses']['numero_cuenta'] or 
     $cto_int=$valores_defecto['Intereses']['cta'];     
 
  $cto_adm=$valores_detalle['Gastos Administrativos']['numero_cuenta'] or 
     $cto_adm=$valores_defecto['Gastos Administrativos']['cta']; 
    
  $cto_com=$valores_detalle['Comisiones']['numero_cuenta'] or 
     $cto_com=$valores_defecto['Comisiones']['cta'];   
   
} else {  // si hubo error recupera datos del post
 $text_iva=$_POST['iva'];
 $text_gan=$_POST['ganancia'];
 $text_rib=$_POST['rib'];
 $text_suss=$_POST['suss'];
 $text_mul=$_POST['multas'];
 $text_dep=$_POST['deposito'];
 $text_otro=$_POST['otro'];
 $text_devp=$_POST['devolucion'];
 $text_int=$_POST['interes'];
 $text_com=$_POST['comisiones'];
 $text_adm=$_POST['gastoadm'];
 $text_ficticio=$_POST['text_ficticio'];
 $text_cheque=$_POST['text_cheque'];
 $text_diferido=$_POST['text_diferido'];
 $id_iva=$_POST['tipo_iva'];
 $id_gan=$_POST['tipo_gan'];
 $id_mul=$_POST['tipo_multas'];
 $id_rib=$_POST['tipo_rib'];
 $id_suss=$_POST['tipo_suss'];
 $id_dep=$_POST['tipo_dep'];
 $id_otro=$_POST['tipo_otro'];
 $id_devp=$_POST['tipo_devolucion'];
 $id_int=$_POST['tipo_interes'];
 $id_com=$_POST['tipo_comisiones'];
 $id_adm=$_POST['tipo_gastoadm'];
 $prov_iva=$_POST['prov_iva'];
 $prov_gan=$_POST['prov_gan'];
 $prov_rib=$_POST['prov_rib'];
 $prov_suss=$_POST['prov_suss'];
 $prov_mul=$_POST['prov_multas'];
 $prov_dep=$_POST['prov_dep'];
 $prov_otro=$_POST['prov_otro'];
 $prov_devp=$_POST['prov_devolucion'];
 $prov_int=$_POST['prov_interes'];
 $prov_com=$_POST['prov_comisiones'];
 $prov_adm=$_POST['prov_gastoadm'];
 $cto_iva=$_POST['concepto_iva'];
 $cto_gan=$_POST['concepto_gan'];
 $cto_rib=$_POST['concepto_rib'];
 $cto_suss=$_POST['concepto_suss'];
 $cto_mul=$_POST['concepto_multas'];
 $cto_dep=$_POST['concepto_dep'];
 $cto_otro=$_POST['concepto_otro'];
 $cto_devp=$_POST['concepto_devolucion'];
 $cto_int=$_POST['concepto_interes'];
 $cto_com=$_POST['concepto_comisiones'];
 $cto_adm=$_POST['concepto_gastoadm'];
 
 $id_deposito=$_POST['tipo_deposito'];
 
 } 

if ($c_dep==1 || $_POST['chk_dep']==1) $det_visib = "";
  else $det_visib = "none";
?>
<br>
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
  <td><input type="checkbox" class="estilos_check" name="chk_iva" value=1 onclick="if (!this.checked) document.all.iva.value=''" <?if ( $c_iva==1 || $_POST['chk_iva']==1) echo 'checked' ?>> </td>
  <td align="right">IVA:</td>
  <td><input type="text" name='iva' value='<?=$text_iva?>' size="15" ></td>
  <td> <?=gen_select('tipo_iva',$res_egreso,$id_iva,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_iva',$res_prov,$prov_iva,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_iva',$res_concepto,$cto_iva);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_gan" value=1 onclick="if (!this.checked) document.all.ganancia.value=''"  <?if ($c_gan==1 || $_POST['chk_gan']==1) echo 'checked' ?>> </td>
  <td align="right">GANANCIAS:</td>
  <td><input type="text" name='ganancia' value='<?=$text_gan?>' size="15"></td>
  <td><?=gen_select('tipo_gan',$res_egreso,$id_gan,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_gan',$res_prov,$prov_gan,'id_proveedor','razon_social');?> </td>
  <td><?=gen_select_concepto('concepto_gan',$res_concepto,$cto_gan);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_rib" value=1 onclick="if (!this.checked) document.all.rib.value=''" <?if ($c_rib==1 || $_POST['chk_rib']==1) echo 'checked' ?>> </td>
  <td  align="right">RIB:</td>
  <td><input type="text" name='rib' value='<?=$text_rib?>' size="15"></td>
  <td> <?=gen_select('tipo_rib',$res_egreso,$id_rib,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_rib',$res_prov,$prov_rib,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_rib',$res_concepto,$cto_rib);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_suss" value=1 onclick="if (!this.checked) document.all.suss.value=''" <?if ($c_suss==1 || $_POST['chk_suss']==1) echo 'checked' ?>> </td>
  <td  align="right">SUSS:</td>
  <td> <input type="text" name='suss' value='<?=$text_suss?>' size="15"></td>
  <td> <?=gen_select('tipo_suss',$res_egreso,$id_suss,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_suss',$res_prov,$prov_suss,'id_proveedor','razon_social');?></td>
  <td> <?=gen_select_concepto('concepto_suss',$res_concepto,$cto_suss);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_mul" value=1 onclick="if (!this.checked) document.all.multas.value=''" <?if ($c_mul==1 || $_POST['chk_mul']==1) echo 'checked' ?> > </td>
  <td  align="right">MULTAS:</td>
  <td><input type="text" name='multas' value='<?=$text_mul?>' size="15" ></td>
  <td> <?=gen_select('tipo_multas',$res_egreso,$id_mul,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_multas',$res_prov,$prov_mul,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_multas',$res_concepto,$cto_mul);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_dep" value=1 
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
  <td><input type="checkbox" class="estilos_check" name="chk_otro" value=1 onclick="if (!this.checked) document.all.otro.value=''" <?if ($c_otro==1 || $_POST['chk_otro']==1) echo 'checked' ?>> </td>
  <td  align="right">OTROS:</td>
  <td><input type="text" name='otro' value='<?=$text_otro?>' size="15" ></td>
  <td><?=gen_select('tipo_otro',$res_egreso,$id_otro,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_otro',$res_prov,$prov_otro,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_otro',$res_concepto,$cto_otro);?></td>
</tr>

<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_diferido" value=1 onclick="if (!this.checked) {document.all.diferido_monto.value=document.all.diferido.value;document.all.diferido.value='';}else document.all.diferido.value=document.all.diferido_monto.value;" <?if ($c_diferido==1 || $_POST['chk_diferido']==1) echo 'checked' ?>> </td>
  <td align="right" onclick="ventana_cheques = window.open('chequesdif_cobranza.php','','');" style="cursor:hand" title="Haga click aqui para ingresar cheques diferidos">CH. DIFERIDO:</td>
  <td><input type="text" name='diferido' value='<?=$text_diferido?>' readonly size="15">
  <input type="hidden" name='diferido_monto' value='<?=$text_diferido?>'>
  </td>
  <td><?=gen_select('tipo_diferido',$res_egreso,$id_diferido,'id_tipo_egreso','nombre');?></td>
  <td><?=gen_select('prov_diferido',$res_prov,$prov_diferido,'id_proveedor','razon_social');?></td>
  <td><?=gen_select_concepto('concepto_diferido',$res_concepto,$cto_diferido);?></td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_devp" value=1 onclick="if (!this.checked) document.all.devolucion.value=''" <?if ($c_devp==1 || $_POST['chk_devp']==1) echo 'checked' ?> > </td>
  <td  align="right">DEVOLUCION PRESTAMO:</td>
  <td><input type="text" name='devolucion' value='<?=$text_devp?>' size="15" ></td>
  <td> <?=gen_select('tipo_devolucion',$res_egreso,$id_devp,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_devolucion',$res_prov,$prov_devp,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_devolucion',$res_concepto,$cto_devp);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_int" value=1 onclick="if (!this.checked) document.all.interes.value=''" <?if ($c_int==1 || $_POST['chk_int']==1) echo 'checked' ?> > </td>
  <td  align="right">INTERESES:</td>
  <td><input type="text" name='interes' value='<?=$text_int?>' size="15" ></td>
  <td> <?=gen_select('tipo_interes',$res_egreso,$id_int,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_interes',$res_prov,$prov_int,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_interes',$res_concepto,$cto_int);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_adm" value=1 onclick="if (!this.checked) document.all.gastoadm.value=''" <?if ($c_adm==1 || $_POST['chk_adm']==1) echo 'checked' ?> > </td>
  <td  align="right">GTOS. ADMINIST.:</td>
  <td><input type="text" name='gastoadm' value='<?=$text_adm?>' size="15" ></td>
  <td> <?=gen_select('tipo_gastoadm',$res_egreso,$id_adm,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_gastoadm',$res_prov,$prov_adm,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_gastoadm',$res_concepto,$cto_adm);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_com" value=1 onclick="if (!this.checked) document.all.comisiones.value=''" <?if ($c_com==1 || $_POST['chk_com']==1) echo 'checked' ?> > </td>
  <td  align="right">COMISIONES:</td>
  <td><input type="text" name='comisiones' value='<?=$text_com?>' size="15" ></td>
  <td> <?=gen_select('tipo_comisiones',$res_egreso,$id_com,'id_tipo_egreso','nombre');?></td>
  <td> <?=gen_select('prov_comisiones',$res_prov,$prov_com,'id_proveedor','razon_social');?> </td>
  <td> <?=gen_select_concepto('concepto_comisiones',$res_concepto,$cto_com);?> </td>
</tr>
<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_ficticio" value=1 onclick="if (!this.checked) document.all.ficticio.value=''" <?if ($c_ficticio==1 || $_POST['chk_ficticio']==1) echo 'checked' ?>> </td>
  <td  align="right">EFECTIVO:</td>
  <td><input type="text" name='ficticio' value='<?=$text_ficticio?>' size="15" ></td>
  <td colspan=3>&nbsp; </td>
</tr>

<tr id=ma>
  <td><input type="checkbox" class="estilos_check" name="chk_cheque" value=1 onclick="if (!this.checked) document.all.cheque.value=''" <?if ($c_cheque==1 || $_POST['chk_cheque']==1) echo 'checked' ?>> </td>
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
 <?if (permisos_check("inicio","licitaciones_ingreso_cob"))
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
     
                
     ?>
   <td colspan="2" align="center"><input type="button" title="ingresa monto de los detalles en caja" name="aceptar" value="Guardar Ingresos/Egresos" <?=$permiso." ". $permiso_egresos;?> onclick="if ( control_dolar(<?=$moneda_factura?>) && (cant_chequeados()) && (control_num()) && (control_fecha()) && (calcular_total(1)) ) {document.all.boton_aceptar.value=1; this.disabled=true;document.form1.submit();};else return false; " >
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


</form>
</body>
</html>

