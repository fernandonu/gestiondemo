<?
/*
$Author: mari $
$Revision: 1.13 $
$Date: 2007/01/05 20:02:08 $
*/


/*VENTA FACTURA PARA UNA FACTURAS ATADAS */
require_once("../../config.php");

require_once("func_cobranzas.php");
require_once("fun_cobranzas_atadas.php");
require_once(LIB_DIR."/class.gacz.php");
require_once("../contabilidad/funciones.php");

echo $html_header;
cargar_calendario();

//guardo los detalle ingresos
if ($_POST['boton_aceptar']) {

   $db->StartTrans();
   $id_cobranza=$_POST['id_cobranza'];
   $id_cob_volver=$_POST['id_cobranza'];
   $monto_original=$_POST['monto_original']; //suma de los montos de las facturas atadas
   $id_vta_atada=datos_ingreso_atadas();
   $cant_pagos=$_POST['cant_pagos'];
   $datos_vta=descomprimir_variable($_POST['datos_vta']); //datos de las facturas
   $cant_facturas=count($datos_vta);
   $valores=descomprimir_variable($_POST['valores_defecto']);
   
   $atadas=array();
   $nro_fact=array();
   $list_cob='(';
   for($ind=0;$ind<$cant_facturas;$ind++) {
       $atadas[$ind]=$datos_vta[$ind]['id_cobranza'];
       $nro_fact[$ind]=$datos_vta[$ind]['nro_factura'];
       $list_cob.=$datos_vta[$ind]['id_cobranza'].",";  //concateno cob primaria
   }
   $list_cob=substr_replace($list_cob,')',(strrpos($list_cob,',')));

   $id_moneda=$_POST['moneda_sel'] or $id_moneda=$_POST['moneda_pago']; //moneda del pago
   $simbolo=$_POST['simbolo_total']; //simbolo del pago
   $id_distrito=$_POST['caja'] or $id_distrito=$_POST['id_distrito'];
   $monto_total=$_POST['monto_total'];  //es el monto del pago parcial 
   $moneda_factura=$_POST['moneda_factura'];
   $simbolo_factura=$_POST['simbolo_factura'];
  
$sql="select nombre from distrito where id_distrito=$id_distrito";
$res=sql($sql,"$sql") or fin_pagina();
$nombre_distrito=$res->fields['nombre'];
  
   $num_fila=$_POST['num_fila'];
   $dolar_ingreso=$_POST["dolaractual_$num_fila"];
   $nro_pago=$num_fila+1;
   $id_pagos_atadas=$_POST["id_pagos_atadas_$num_fila"];
   $fecha=fecha_db($_POST["fecha_$num_fila"]);
   $id_pagos_atadas=guardar_detalle_ingresos_atadas($num_fila,$id_vta_atada,$id_pagos_atadas);
   $fecha=fecha_db($_POST["fecha_$num_fila"]);
   list($anio,$mes,$dia)=split("-",$fecha);
   $dia = date('w', mktime(0,0,0,$mes,$dia,$anio));
   if($dia!=0 && !feriado(fecha($fecha)))  {
         $query="select id_caja,fecha,cerrada from caja 
                where fecha='$fecha' and id_distrito=$id_distrito and id_moneda=$id_moneda";
        
        $caja_query=sql($query,"$query") or fin_pagina();
        $caja_cerrada=0;
                                         
        if($caja_query->RecordCount()==0) { 
 	       $query="select nextval('caja.caja_id_caja_seq') as id";
           $id_caja=sql($query,"$query") or fin_pagina();
           $id_caja=$id_caja->fields['id'];
	       $query="insert into caja(id_caja,id_distrito,id_moneda,fecha) values ($id_caja,$id_distrito,$id_moneda,'$fecha')";
	       sql($query,"$query") or fin_pagina();
	       $caja_cerrada=0;
        }
        else {
           $id_caja=$caja_query->fields['id_caja'];
           $caja_cerrada=$caja_query->fields['cerrada'];
        }
         
  	if ($caja_cerrada==0) {

  	$cantidad_detalle=$_POST['cant_facturas'];

     for($i=0;$i<$cantidad_detalle;$i++) {
     
      	$id_entidad=$_POST["id_cliente_$i"] or $id_entidad=$datos_vta[$i]['id_entidad'];
     	$tipo_factura=strtoupper($datos_vta[$i]['tipo_factura']);
     	$id_licitacion=$datos_vta[$i]['id_licitacion'];
     	$nro_factura=$datos_vta[$i]['nro_factura'];
     	$nombre_cliente=$datos_vta[$i]['nombre'];
     	$id_tipo_ingreso=$_POST["tipo_ing_$i"] or $id_tipo_ingreso=$datos_vta[$i]['id_tipo_ingreso'];
     	$id_cuenta_ingreso=$_POST["cuentaing_$i"] or $id_cuenta_ingreso=$datos_vta[$i]['id_cuenta_ingreso'];
     	$id_cobranza=$datos_vta[$i]['id_cobranza'];
     	$nro=$datos_vta[$i]['id_factura'];
     	$monto=$_POST["montos_$nro"];
     	$comentarios=$_POST["comentarios_$num_fila"];
     	     	
     	
     	if (!$dolar_ingreso) $dolar_ingreso=0; 
     	 	   	
     	$suma=number_format($monto,"2",".","");
     	
        $usuario=$_ses_user['name'];
        $item="F$tipo_factura $nro_factura - $nombre_cliente  ";
        if (es_numero($id_licitacion))
                      $item.=" - ID $id_licitacion";
       $item.= " PROPORCIONAL AL PAGO PARCIAL Nº $nro_pago de $simbolo $monto_total";  

       if ($dolar_ingreso > 0) $item.=" valor dolar $dolar_ingreso";
               
            $query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
            $id_ie=sql($query,"$quey") or fin_pagina();
            $id=$id_ie->fields['id'];

            $sql_detalle="INSERT into caja.ingreso_egreso 
                               (id_ingreso_egreso,id_caja,id_entidad,id_tipo_ingreso,monto,comentarios,usuario,fecha_creacion,item,id_cuenta_ingreso) values
                               ($id,$id_caja,$id_entidad,$id_tipo_ingreso,$monto,'$comentarios','$usuario','$fecha','$item',$id_cuenta_ingreso)";
                  /// actualizar detalle_ingresos con el id !!!!!!!!!!!!!!!!!
                  
           $sql_ing="insert into detalle_pagos_atadas (id_ingreso_egreso,id_pagos_atadas) values ($id,$id_pagos_atadas)";
           $sql_up="update pagos_atadas set ingresos=1 where id_pagos_atadas=$id_pagos_atadas";
                                               
                   
           sql($sql_detalle,"error 1 $sql_detalle ") or fin_pagina (); //inserta en tabla detalle_ingresos cada uno de los ingresos 
           sql($sql_ing,"error 2 $sql_ing ") or fin_pagina();
           sql($sql_up,"error 3 $sql_up ") or fin_pagina();
             
                  
        }

       ////egresos 
       $detalles=array();
       $detalles=guarda_detalle_egresos_atadas($id_cobranza,$id_pagos_atadas,$id_moneda,$dolar_ingreso,$fecha);
 
       
 $comentario="";
    
 $egreso=array();
 $val_actual==array();  //guardo los valores seleccionados antes de guardar el egreso
 $eg=0;

$sql_detalle="";
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
     $item.="-Pago proporcianal $simbolo $monto_total";
            
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_IVA_$nro"]; 
           
 	 if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
   $item.="-Pago proporcianal $simbolo $monto_total";      
   $nro=$datos_vta[$i]['id_factura'];
   $monto=$_POST["montos_GANANCIA_$nro"];       
   
   if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
     $item.="-Pago proporcianal $simbolo $monto_total";      
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_RIB_$nro"];  
     
    if ($monto != "") {
		 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
     $item.="-Pago proporcianal $simbolo $monto_total";
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_SUSS_$nro"];  
 
     if ($monto != "") {     	
	   $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
     $item.="-Pago proporcianal $simbolo $monto_total";    
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_MULTAS_$nro"];  
 
 if ($monto != "") {
	 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
     $item.="-Pago proporcianal $simbolo $monto_total"; 
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_DEPOSITO_$nro"];  
 
     if ($monto != "") {
		 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
     $item.="-Pago proporcianal $simbolo $monto_total";    
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_OTROS_$nro"];  
     
 if ($monto != "") {
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
 $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Otros Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['OTROS'].",$id)";
 }
 }
 $val_actual['OTROS']['prov']=$proveedor;
 $val_actual['OTROS']['cta']=$nro_cuenta;
 $val_actual['OTROS']['tipo']=$tipo_egreso;
 }
 
 if ($_POST['chk_devp']==1) {
 // recupera datos del devolucon prestamo
 $proveedor=$_POST['prov_devolucion'];
 $tipo_egreso=$_POST['tipo_devolucion'];
 $monto=$_POST['devolucion'];
 $nro_cuenta=$_POST['concepto_devolucion'];
 
  for ($i=0;$i<$cant_facturas;$i++) {
   	 $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $simbolo $monto_total";
            
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_DEVOLUCION_$nro"]; 
           
 	 if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
     $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Devolucion Prestamo  Monto: ".$simbolo." ".formato_money($monto);  
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['DEVOLUCION'].",$id)";
 	 }
 }
  $val_actual['Devolucion prestamo']['prov']=$proveedor;
  $val_actual['Devolucion prestamo']['cta']=$nro_cuenta;
  $val_actual['Devolucion prestamo']['tipo']=$tipo_egreso;
 
 }
 
 
 if ($_POST['chk_int']==1) {
 // recupera datos de intereses
 $proveedor=$_POST['prov_interes'];
 $tipo_egreso=$_POST['tipo_interes'];
 $monto=$_POST['interes'];
 $nro_cuenta=$_POST['concepto_interes'];
 
  for ($i=0;$i<$cant_facturas;$i++) {
   	 $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $simbolo $monto_total";
            
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_INTERESES_$nro"]; 
           
 	 if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
     $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Intereses Monto: ".$simbolo." ".formato_money($monto);  
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['INTERESES'].",$id)";
     //imputar pagos
     $pago[]=array();
	 $pago["tipo_pago"]="id_ingreso_egreso";
	 $pago["id_pago"]=$id;
	 $_POST['cuentas']=$nro_cuenta;
	 if ($id_moneda==2) { //si esta en dolares
	  $_POST["valor_dolar_imp"]=$dolar_ingreso;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_ingreso; 
     }
     else  $_POST['monto_neto']=$monto;
	 imputar_pago($pago,"",fecha($fecha));
 	 }
 }
  $val_actual['intereses']['prov']=$proveedor;
  $val_actual['intereses']['cta']=$nro_cuenta;
  $val_actual['intereses']['tipo']=$tipo_egreso;
 }
 
 if ($_POST['chk_adm']==1) {
// recupera datos de Gastos administrativos
 $proveedor=$_POST['prov_gastoadm'];
 $tipo_egreso=$_POST['tipo_gastoadm'];
 $monto=$_POST['gastoadm'];
 $nro_cuenta=$_POST['concepto_gastoadm'];
 
  for ($i=0;$i<$cant_facturas;$i++) {
   	 $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $simbolo $monto_total";
            
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_GASTOS_$nro"]; 
           
 	 if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
     $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Devolucion Prestamo  Monto: ".$simbolo." ".formato_money($monto);  
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['GASTOS'].",$id)";
     //imputar pagos
	 $pago[]=array();
	 $pago["tipo_pago"]="id_ingreso_egreso";
	 $pago["id_pago"]=$id;
	 $_POST['cuentas']=$nro_cuenta;
	 if ($id_moneda==2) { //si esta en dolares
	  $_POST["valor_dolar_imp"]=$dolar_ingreso;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_ingreso; 
     }
     else  $_POST['monto_neto']=$monto;	 
	 imputar_pago($pago,"",fecha($fecha));
	 }
 }
  $val_actual['gastos adm']['prov']=$proveedor;
  $val_actual['gastos adm']['cta']=$nro_cuenta;
  $val_actual['gastos adm']['tipo']=$tipo_egreso;
 }
 
 if ($_POST['chk_com']==1) {
 // recupera datos de Comisiones
 $proveedor=$_POST['prov_comisiones'];
 $tipo_egreso=$_POST['tipo_comisiones'];
 $monto=$_POST['comisiones'];
 $nro_cuenta=$_POST['concepto_comisiones'];
 
  for ($i=0;$i<$cant_facturas;$i++) {
   	 $item="F".$datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'];
 	 $item.="-".$datos_vta[$i]['nombre'];
 	 if (es_numero($datos_vta[$i]['id_licitacion']))
         $item.= " ID". $datos_vta[$i]['id_licitacion']." \n" ;
     $item.="-Pago proporcianal $simbolo $monto_total";
            
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_COMISIONES_$nro"]; 
           
 	 if ($monto != "") {
     $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
     $egreso[$eg++]="\nId Egreso: ".$id." Descripción: Comisiones  Monto: ".$simbolo." ".formato_money($monto);  
     $sql_detalle[]="insert into egresos_atadas (id_detalle_eg_atadas,id_ingreso_egreso) values (".$detalles['COMISIONES'].",$id)";
     //imputar pagos
	 $pago[]=array();
	 $pago["tipo_pago"]="id_ingreso_egreso";
	 $pago["id_pago"]=$id;
	 $_POST['cuentas']=$nro_cuenta;
	 if ($id_moneda==2) { //si esta en dolares
	  $_POST["valor_dolar_imp"]=$dolar_ingreso;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_ingreso; 
     }
     else  $_POST['monto_neto']=$monto;
	 imputar_pago($pago,"",fecha($fecha));
 	 }
 }
  $val_actual['comisiones']['prov']=$proveedor;
  $val_actual['comisiones']['cta']=$nro_cuenta;
  $val_actual['comisiones']['tipo']=$tipo_egreso;

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
     $item.="-Pago proporcianal $simbolo $monto_total";  
     $nro=$datos_vta[$i]['id_factura'];
     $monto=$_POST["montos_DIFERIDO_$nro"];  
 
 if ($monto != "") {
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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

        
        } else   Error ("La Caja esta Cerrada. Seleccione fecha de la caja"); 

        } 
        else Error("Usted esta intentando insertar un ingreso de un día que no es habil");
        
//manda mail y finaliza las facturas  
if ($moneda_factura == $id_moneda) {
      //ingresos  
        $sql_sum="select sum (ingreso_egreso.monto) as total_ingresado
				  from licitaciones_datos_adicionales.pagos_atadas
				  join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
				  join caja.ingreso_egreso using (id_ingreso_egreso)
				   where id_vta_atada=$id_vta_atada and ingresos=1";
       //egresos
       $sql="select sum(ingreso_egreso.monto) as total_pagado from 
	         licitaciones_datos_adicionales.detalle_egresos_atadas
	         join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	         join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
             join caja.ingreso_egreso using (id_ingreso_egreso)  
	         where id_vta_atada=$id_vta_atada";
     
        //suma el monto de ficticio y cheque 
       $sql_sin_eg="select sum(monto_detalle) as total_pagado from 
	                licitaciones_datos_adicionales.detalle_egresos_atadas
	                join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	                join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	                where id_vta_atada=$id_vta_atada and (id_cob_egreso=7 or id_cob_egreso=8)";
   }
   else { 
         if ($id_moneda==1)  {//es de dolares a peso 
           $sql_sum="select sum(ingreso_egreso.monto / valor_dolar) as total_ingresado
                     from licitaciones_datos_adicionales.pagos_atadas
				     join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
				     join caja.ingreso_egreso using (id_ingreso_egreso)
				     where id_vta_atada=$id_vta_atada and ingresos=1";
           //egresos
           $sql="select sum(ingreso_egreso.monto / dolar_egreso) as total_pagado from 
                 licitaciones_datos_adicionales.detalle_egresos_atadas
	             join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	             join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
                 join caja.ingreso_egreso using (id_ingreso_egreso)  
	             where id_vta_atada=$id_vta_atada";
         
           $sql_sin_eg="select sum(monto_detalle / dolar_egreso) as total_pagado from 
	                   licitaciones_datos_adicionales.detalle_egresos_atadas
	                   join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	                   join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	                   where id_vta_atada=$id_vta_atada and (id_cob_egreso=7 or id_cob_egreso=8)";
         }
         else { ///es de pesos a dolares 
              
            $sql_sum="select sum(ingreso_egreso.monto * valor_dolar) as total_ingresado
                      from licitaciones_datos_adicionales.pagos_atadas
				      join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
				      join caja.ingreso_egreso using (id_ingreso_egreso)
				      where id_vta_atada=$id_vta_atada and ingresos=1";
            
           $sql="select sum(ingreso_egreso.monto * dolar_egreso) as total_pagado from 
                 licitaciones_datos_adicionales.detalle_egresos_atadas
	             join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	             join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
                 join caja.ingreso_egreso using (id_ingreso_egreso)  
	             where id_vta_atada=$id_vta_atada";
         
          $sql_sin_eg="select sum(monto_detalle * dolar_egreso) as total_pagado from 
	                   licitaciones_datos_adicionales.detalle_egresos_atadas
	                   join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	                   join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	                   where id_vta_atada=$id_vta_atada and (id_cob_egreso=7 or id_cob_egreso=8)";
         
         }        
        }
//ingresos     
$res_sum=sql($sql_sum,"$sql_sum") or fin_pagina();       
$total_ingresado=$res_sum->fields['total_ingresado'];
$total_ingresado=number_format($total_ingresado,"2",".","");

//egresos
$res=sql($sql,"$sql") or fin_pagina();
$res_sin_eg=sql($sql_sin_eg,"$sql_sin_eg") or fin_pagina();
$total_pagado=$res->fields['total_pagado'] + $res_sin_eg->fields['total_pagado'];
$total_pagado=number_format($total_pagado,"2",".","");

if ((abs($total_ingresado - $monto_original)  < 0.04) && (abs($total_pagado - $monto_original) < 0.04 )) {  //monto_original es el total de las facturas atadas    


$para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";
if ($monto_original > 5000) {
        $para.=',corapi@coradir.com.ar';
}          
            mail_ingresos_atadas($datos_vta,$id_vta_atada,$id_distrito,$monto_original);
            mail_egresos_parciales_atadas ($id_vta_atada,$nombre_distrito,$valores,$nro_fact,$para);
            $sql="update venta_fac_atadas set ctrl_ingreso=1,ctrl_egreso=1 where id_vta_atada=$id_vta_atada"; 
            sql($sql,"$sql") or fin_pagina($sql);
            
            $sql= "UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='".$_ses_user['name']."',
                   fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza in $list_cob";
           //finalizar_vta_factura($id_cobranza,1);
           sql($sql,"$sql") or fin_pagina();
           
           $ref = encode_link('../licitaciones/lic_cobranzas.php',array("cmd"=>'finalizada',"cmd1"=>"detalle_cobranza","id"=>$id_cob_volver));
         if (!$error) { ?>
            <script>
               window.opener.location.href='<?=$ref?>';
               window.close();
            </script>
            <?
           }
}
   
       
     
        
if ($db->CompleteTrans()) 
          $msg="LOS DETALLES SE GUARDARON CON ÉXITO";
   else {
	      $msg="ERROR AL GUARDAR LOS DETALLES";  
	      $error=1;
}         
}  //fin del post de boton acptar



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
<script language="JavaScript" src="../../lib/NumberFormat150.js"></script>
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


//cambia el simbolo del monto de cada ingreso 
function cambiar_simbolo(mon_sel) {
        moneda_fact=document.all.moneda_factura.value;
        cant=document.all.cant_pagos.value;
        for(i=0;i<cant;i++) {
          simb=eval("document.all.simbolo_"+i);
          dolar=eval("document.all.dolaractual_"+i);
          if ((mon_sel.value==2)) {
  	            simb.value="U$S";
  	            dolar.disabled=false;
  	           document.all.moneda_pago.value=2;
          }
          else if((mon_sel.value==1) )  {
  	         simb.value="$";
  	         document.all.moneda_pago.value=1;
          }
        }
 }	
	

//control que se el monto no este vacio
function control_monto(i) {
var monto=eval("document.all.parcial_"+ i);
if ((isNaN(monto.value)) || (monto.value=="") || (parseFloat(monto.value==0))) {
    alert ("Ingrese número valido para el campo monto");
   return false;
}
return true;
}

//controla que el dolar sean valido sean numeros validos
function control_campos(i) {
        if (!i)	
              var i=document.all.num_fila.value;	
        if ((document.all.moneda_factura.value==2) || (document.all.moneda_sel[0].checked==false))
            {
	        var dolar=eval("document.all.dolaractual_"+ i);
	        if ((isNaN(dolar.value)) || (dolar.value=="") || (parseFloat(dolar.value)==0)) 
              {
              alert ("Ingrese número valido para el campo dolar");
              dolar.value="";
              return false;
              }
            } 
            
       var fecha=eval("document.all.fecha_"+i);   
        if (fecha.value=="" || fecha.value==null) {
           alert ("Ingrese fecha");
           return false;
        }   
        return true;
}

function control_datos() {
        var cant=document.all.cant_pagos.value;	   
        var i=document.all.cant_pagos_hechos.value;
        var retornar=true
        for (;i<cant;i++) {
	        ret=((control_campos(i)) && (control_monto(i)));
	        if (ret==false) retornar=false;
           }

        return retornar;
}


function limit_cant_pagos() {	
        if(parseInt(document.all.cant_pagos_hechos.value) >= parseInt(document.all.cant_pagos.options[document.all.cant_pagos.options.selectedIndex].text))	
          { 
            document.all.cant_pagos.options.selectedIndex=parseInt(document.all.cant_pagos_anterior.value) - 2 ; 
  	        return 1;  
          }
          else
          return 0; 
}	

//limpia los campos dolar y porcentaje cuando se cambia el simbolo
function limpiar (valor) {
    var cant=document.all.cant_pagos.options[document.all.cant_pagos.selectedIndex].text;
    mon_sel=valor.value;
    mon_factura=parseFloat(document.all.moneda_factura.value);
      for(i=0;i<cant;i++) {
          dolar=eval("document.all.dolaractual_"+i);
          dolar.value="";
          parcial=eval("document.all.parcial_"+i);
          parcial.value="";
          com=eval("document.all.comentarios_"+i);
          com.value="";
     }
}

//si cambia el valor del dolar o el valor del porcentaje 
// limpia el campo el monto
function limpiar_monto(i) {
          parcial=eval("document.all.parcial_"+i);
          parcial.value="";
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
<?
$ventana=new JsWindow('','_blank');
$ventana->varName = "ventana";
$ventana->linkBar = false;
$ventana->locationBar = false;
$ventana->menuBar = false;
$ventana->toolBar = false;
$ventana->scrollBars = false;
$ventana->resizable = false;
$ventana->width=400;
$ventana->height=200;

?>   
function control_fecha_venta(i) {
 if (!i)	
    var i=document.all.num_fila.value;	

 var fecha=eval("document.all.fecha_"+ i);
	       
 if(fecha.value=="")
 {alert('Debe ingresar una fecha valida');
  return false;
 }
  else if (!es_mayor('fecha_'+i)) {
  alert('Los ingresos o egresos deben tener fecha mayor o igual a la fecha de hoy');
  return false;
 }
 return true;
}


</script>

 
<form name="form1" action="venta_factura_atadas.php" method="post">
<input type="hidden" name="cheques_diferidos" id="cheques_diferidos" value="">
<input type="hidden" name="num_fila" value="<?=$_POST['num_fila']?>">

<? 
echo "<div align='center'> <font color=red> $msg </font></div>";

$id_cobranza = $parametros['id_cobranza'] or $id_cobranza=$_POST['id_cobranza']; //cobranza primaria
$atadas = descomprimir_variable($parametros['atadas']) or $atadas=descomprimir_variable($_POST['atadas']); //id de todas las facturas atadas y la primaria
$monto_original=$parametros['monto_original'] or $monto_original=$_POST["monto_original"];  //suma monto de las facturas atadas
$moneda_factura=$parametros['moneda_factura'] or $moneda_factura=$_POST['moneda_factura'];  //moneda original de las facturas atadas
$simbolo_factura=$parametros['simbolo_factura'] or $simbolo_factura=$_POST['simbolo_factura'];  //simbolo original de las facturas
if ($_POST['valores_defecto']) 
    $valores_defecto=descomprimir_variable($_POST['valores_defecto']);
else 
   $valores_defecto=armar_valores_defecto();
  
//serializo el arreglo
$valores=comprimir_variable($valores_defecto);
?>
<input type="hidden" name="valores_defecto" value="<?=$valores?>">
<?

$cant_facturas=count($atadas);
$fact_atadas='('.implode(",", $atadas).')';

// ID DE LA VENTA DE FACTURA 
$sql_vta_fac="select id_vta_atada,id_moneda,cant_pagos,id_distrito,simbolo,ctrl_ingreso,ctrl_egreso
              from venta_fac_atadas
              left join licitaciones.moneda using (id_moneda)
              where id_cobranza=$id_cobranza";

$res_venta=sql($sql_vta_fac,"$sql_vta_fac") or fin_pagina();
$id_vta_atada=$res_venta->fields['id_vta_atada'];
$control_ingreso=$res_venta->fields['ctrl_ingreso']; //indica que se realizaron todos los ingresos
$control_egreso=$res_venta->fields['ctrl_egreso'];  //indica que se realizaron todos los egresos

if ($res_venta->RecordCount() > 0) {
    $hay_detalle=1; //se la venta de factura 
    $id_moneda=$res_venta->fields['id_moneda'];
    $simbolo_moneda=$res_venta->fields['simbolo'];
    $id_distrito=$res_venta->fields['id_distrito'];
  
    if ($cant_pagos=="")  {
   	    $cant_pagos=$_POST['cant_pagos'] or $cant_pagos=$res_venta->fields['cant_pagos']; 
    }
}
else {
    $hay_detalle=0; //no se ha guardado la venta de factura
    if ($cant_pagos =="") {
   	 	 $cant_pagos=$_POST['cant_pagos'] or $cant_pagos=2;
   	}
    $id_moneda=$_POST['moneda_sel'] or $id_moneda=$moneda_factura;
    $id_distrito=$_POST['caja'] or $id_distrito=1;
                  }
$fecha=date("Y-m-d"); 


?>
<input type="hidden" name="id_cobranza" value="<?=$id_cobranza?>">  <? //cobranza primaria */?>
<input type="hidden" name="atadas" value="<?=comprimir_variable($atadas)?>">  <? //id de todas las facturas atadas y la primaria?>
<input type="hidden" name="monto_original" value="<?=$monto_original?>">  <?//suma de monto facturas atadas?>
<input type="hidden" name="moneda_factura" value="<?=$moneda_factura?>">
<input type="hidden" name="simbolo_factura" value="<?=$simbolo_factura?>"> 
<input type="hidden" name="cant_facturas" value="<?=$cant_facturas?>"> 
<input type="hidden" name="id_vta_atada" value="<?=$id_vta_atada?>"> 
<input type="hidden" name="moneda_pago" value="<?=$id_moneda?>"> 
<input type="hidden" name="id_distrito" value="<?=$id_distrito?>"> 

<?

//busco los datos de las facturas atadas 
$datos="select cobranzas.nro_factura,cobranzas.id_moneda,cobranzas.id_licitacion,tipo_factura,
cobranzas.id_entidad,cobranzas.monto_original,cobranzas.monto,cobranzas.id_cobranza,cobranzas.id_factura,
facturas.cotizacion_dolar,iva_tasa,tipo_factura,simbolo,entidad.nombre,entidad.id_entidad,facturas.id_entidad as entidad_factura 
from licitaciones.cobranzas 
left join facturacion.facturas using (id_factura)
left join licitaciones.entidad on entidad.id_entidad=cobranzas.id_entidad
left join licitaciones.moneda on cobranzas.id_moneda=moneda.id_moneda
where id_cobranza in $fact_atadas order by cobranzas.nro_factura";

$res_datos=sql($datos,"$datos") or fin_pagina();

if ($id_vta_atada) {
	$sql_detalles="select detalles_vta_atada.id_detalle_vta_atada,cobranzas.id_cobranza,tipo_factura,
                 detalles_vta_atada.id_cobranza,id_tipo_ingreso,id_cuenta_ingreso,entidad.nombre,cobranzas.id_factura,
                 detalles_vta_atada.id_entidad,facturas.nro_factura,cobranzas.id_licitacion,monto_original
                 from licitaciones_datos_adicionales.detalles_vta_atada
                 left join licitaciones.cobranzas using (id_cobranza)
                 left join licitaciones.entidad on detalles_vta_atada.id_entidad=entidad.id_entidad 
                 left join facturacion.facturas using (id_factura)
                 where detalles_vta_atada.id_vta_atada=$id_vta_atada order by id_factura";
	
	$res=sql($sql_detalles,"$sql_detalles") or fin_pagina(); 
    $datos_vta=array();
    $i=0;
    while (!$res->EOF) {
    $datos_vta[$i]['id_factura']=$res->fields['id_factura'];
    if ($res->fields['id_entidad']) {
         $datos_vta[$i]['id_entidad']=$res->fields['id_entidad'];
         $datos_vta[$i]['nombre']=$res->fields['nombre'];
    }
    else {
    	 $datos_vta[$i]['id_entidad']=$res->fields['entidad_factura'];
         $datos_vta[$i]['nombre']=retorna_id('entidad','nombre','id_entidad',$res->fields['entidad_factura'],'=');
    }
    $datos_vta[$i]['nro_factura']=$res->fields['nro_factura'];
    $datos_vta[$i]['id_tipo_ingreso']=$res->fields['id_tipo_ingreso'];
    $datos_vta[$i]['id_cuenta_ingreso']=$res->fields['id_cuenta_ingreso'];
    $datos_vta[$i]['id_cobranza']=$res->fields['id_cobranza'];
    $datos_vta[$i]['id_licitacion']=$res->fields['id_licitacion'];
    $datos_vta[$i]['tipo_factura']=$res->fields['tipo_factura'];
    $datos_vta[$i]['monto_factura']=$res->fields['monto_original'];
    $res->Movenext();
    $i++;
    } 
}
else {
	$res_datos->Movefirst();
    $datos_vta=array();
    $i=0;
    $id_tipo=retorna_id('tipo_ingreso','id_tipo_ingreso','nombre','Cobros','ilike');
    $id_cuenta=retorna_id('tipo_cuenta_ingreso','id_cuenta_ingreso','nombre','Cobros (Facturas Clientes)','ilike');
      
    while (!$res_datos->EOF) {
    $datos_vta[$i]['id_factura']=$res_datos->fields['id_factura'];
    if ($res_datos->fields['id_entidad']) {
        $datos_vta[$i]['id_entidad']=$res_datos->fields['id_entidad'];
        $datos_vta[$i]['nombre']=$res_datos->fields['nombre'];
    } 
    else {
        $datos_vta[$i]['id_entidad']=$res_datos->fields['entidad_factura'];
        $datos_vta[$i]['nombre']=retorna_id('entidad','nombre','id_entidad',$res_datos->fields['entidad_factura'],'=');
    }   
    $datos_vta[$i]['nro_factura']=$res_datos->fields['nro_factura'];
    $datos_vta[$i]['id_tipo_ingreso']=$id_tipo;
    $datos_vta[$i]['id_cuenta_ingreso']=$id_cuenta;
    $datos_vta[$i]['id_cobranza']=$res_datos->fields['id_cobranza'];
    $datos_vta[$i]['id_licitacion']=$res_datos->fields['id_licitacion'];
    $datos_vta[$i]['tipo_factura']=$res_datos->fields['tipo_factura'];
    $datos_vta[$i]['monto_factura']=$res_datos->fields['monto_original'];
    $res_datos->Movenext();
    $i++;
    }
}

$cantidad_detalle=count($datos_vta);
$res_datos->Movefirst();

echo "<input type='hidden' name='datos_vta' value='".comprimir_variable($datos_vta)."'>";
$cant_pagos_hechos=0;
if ($id_vta_atada != null || $id_vta_atada!="") {
$sql="select id_pagos_atadas,id_vta_atada,monto,valor_dolar,fecha_ing,comentarios,
        id_moneda,id_distrito,ingresos,egresos
		from pagos_atadas 
		join venta_fac_atadas using (id_vta_atada)
		where id_vta_atada=$id_vta_atada order by id_pagos_atadas";

$res_detalles=sql($sql,"$sql") or fin_pagina();

$sql_control="select sum(ingresos) as control
      from pagos_atadas
      where id_vta_atada=$id_vta_atada"; 
$res_control=sql($sql_control,"$sql_control") or fin_pagina();

if ($res_control->fields['control'] > 0 )  {
    $cant_pagos_hechos=$res_control->fields['control']; 
    $des='disabled';
}
else {
	$cant_pagos_hechos=0; 
    $des=""; 
}   
}

?>

<table align="center">
  <tr><td>
          <table class="bordes" align="center"> 
             <tr id=mo>
              <td colspan="2"> SELECCIONE DISTRITO </td>
             <tr>
             <tr bgcolor=<?=$bgcolor_out?>>
                <td><input type="radio" name="caja" value=1 <?if ($id_distrito==1) echo 'checked' ?>  <?if ($cant_pagos_hechos > 0 && $id_distrito==2) echo "disabled"?>> </td>
                <td> CAJA SAN LUIS </td>
             </tr>
             <tr bgcolor=<?=$bgcolor_out?>>
                <td><input type="radio" name="caja" value=2 <?if ($id_distrito==2) echo 'checked'?>  <?if ($cant_pagos_hechos > 0 && $id_distrito==1) echo "disabled"?>> </td>
                <td> CAJA BS AS </td>
             </tr>
           </table>
       </td>
       <td>&nbsp; </td>
      <td>
        <table class="bordes" align="center"> 
           <tr id=mo>
              <td colspan="2"> SELECCIONE MONEDA </td>
           </tr>
        		
		<tr bgcolor=<?=$bgcolor_out?>>
			<td><input type="radio" name="moneda_sel" value=1 <?if ($id_moneda==1) echo 'checked'?>  <?if ($cant_pagos_hechos > 0 && $id_moneda==2) echo "disabled"?>
             onclick="limpiar(this);cambiar_simbolo(this)">  </td>
			<td> PESOS </td>
		</tr>
		<tr bgcolor=<?=$bgcolor_out?>>
			<td><input type="radio" name="moneda_sel" value=2 <?if ($id_moneda==2) echo 'checked'?>  <?if ($cant_pagos_hechos > 0 && $id_moneda==1) echo "disabled"?>
                   onclick="limpiar(this);cambiar_simbolo(this)";> </td>
			
			<td> DOLARES </td>
		</tr>
   </table>
  </td> 
 </tr>
</table>
<br>

<table align="center">
<tr><td>
<table align="center">	
	<tr id="mo">
	    <td> Nro Factura</td>
        <td> ID LIC</td>
        <td> Cliente</td>
        <td> Tipo Factura</td>
        <td> Monto</td>
        <?if ($moneda_factura==2) {?>
        <td> Dolar Factura</td>
           <? } ?>
        <td> Tasa iva</td> 
        </tr>
        
     <?
     while (!$res_datos->EOF) {
     
     ?>   
     <tr bgcolor=<?=$bgcolor_out?>>
        <td align="center"> <?=$res_datos->fields['nro_factura'] ?></td>
        <td align="center"> <?=$res_datos->fields['id_licitacion']?></td>
        <?
          if (!$res_datos->fields['id_entidad'])
             $nombre_entidad=retorna_id('entidad','nombre','id_entidad',$res_datos->fields['entidad_factura'],'=');
          else 
             $nombre_entidad=$res_datos->fields['nombre'];      
        ?>
        <td align="center"> <?=$nombre_entidad?></td>
        <td align="center"> <?=strtoupper($res_datos->fields['tipo_factura'])?></td>
        <td align="center"> <?=$res_datos->fields['simbolo']." ".formato_money($res_datos->fields['monto_original'])?></td>
        <?if ($moneda_factura==2) { ?>
        <td align="center"> <?=formato_money($res_datos->fields['cotizacion_dolar'])?></td>
        <? } ?>
        <td align="center"><?=$res_datos->fields['iva_tasa']?> %</td>
     </tr>
 <? 
     $res_datos->MoveNext();
     } ?>
</table>  
</td>   
</tr>
</table>

<?

 if ($hay_detalle==1) {
    $simbolo_moneda=$res_venta->fields['simbolo'];
 }
    else  {
    $simbolo_moneda=$_POST['simbolo_moneda'] or $simbolo_moneda=$simbolo_factura;
    }

   
?>


<input type="hidden" name="simbolo_moneda" value="<?=$simbolo_moneda?>">

<br>
<table align="center">
<tr>
   <td> <font color="Red"><b>MONTO TOTAL</b> &nbsp; </font> </td>
   <td> 
      <input name="simbolo_total_factura" type="text" style="text-align:right; background:inherit; border:none" value="<?=$simbolo_factura;?>" readonly size="3" >
      <input name="monto_total_factura"   type="text" style="text-align:left; background:inherit; border:none" value="<? echo formato_money($monto_original);?>" readonly size="10" >
   </td>
</tr>
</table>   

<?//---------------------------- Detalles facturas  ---------------------------------/  
?>
<table align="center" border="1" cellspacing="2"  bordercolor="#000000">
<tr id="mo"><td colspan="7"> DETALLES VENTA DE FACTURA</td></tr>
<tr id=ma>
       <td colspan="7" align="right"> 
       Cantidad de pagos 
        <select name=cant_pagos onchange="var limite=eval(parseInt(document.all.cant_pagos_hechos.value) +1);if(limit_cant_pagos())alert('No se puede seleccionar menos de ' + eval(limite) +' pagos. Debe quedar al menos un pago libre para terminar de pagar la factura .');else document.all.form1.submit()">
           <? $total_pagos=10;
              for($i=2;$i<=$total_pagos;$i++){ ?>
              <option value='<?=$i?>' <?if ($i==$cant_pagos) echo 'selected'?>> <?=$i?> </option>
          <? }
           ?>
        </select> 
       </td>
</tr>
<tr id=ma>
        <td>Fecha</td>
        <td>Dólar</td>
        <td>Monto</td>
        <td>Observaciones</td>
        <td colspan="2">&nbsp; </td>
</tr>

<input type='hidden' name='id_pagos_atadas' value=0> 
<?

if (!permisos_check("inicio","licitaciones_ingreso_cob")) {
       //permiso para realizar ingresos
       $permiso_guardar_ing=0;
       } 
       else {
       $permiso_guardar_ing=1;
       }
   

?> 
<br>
<?

$monto_pagado=0;

//$cant_pagos_realizados=0;
for ($i=0;$i<$cant_pagos;$i++) {

if ($hay_detalle) {
    $monto=$res_detalles->fields['monto'] or $monto=$_POST["parcial_$i"];
    $comentarios=$res_detalles->fields['comentarios'] or  $comentarios=$_POST["comentarios_".$i];
    $id_vta_atada=$res_detalles->fields['id_vta_atada'];
    $id_pagos_atadas=$res_detalles->fields['id_pagos_atadas'];
    $moneda_ingreso=$res_detalles->fields['id_moneda'];
    $fecha=$res_detalles->fields['fecha_ing'] or $fecha=fecha_db($_POST["fecha_$i"]) or $fecha=date("Y-m-d");
    $id_pagos_atadas=$res_detalles->fields['id_pagos_atadas'];
  
    if ($res_detalles->fields['valor_dolar']) {
       $dolar_ingreso=$res_detalles->fields['valor_dolar'];
    }
    elseif ($_POST["dolaractual_".$i]) 
        $dolar_ingreso=$_POST["dolaractual_".$i];
    else $dolar_ingreso="";
    $ctrl_ing=$res_detalles->fields['ingresos']; 
    $ctrl_eg=$res_detalles->fields['egresos'];
    
    $res_detalles->MoveNext();
    
    if ($ctrl_ing == 1)  {
    	 $disabled=" disabled"; 
    	 if ($dolar_ingreso!="") {
    	 	if ($moneda_factura==2 && $moneda_ingreso==1) {
    	 	    $parcial=$monto/$dolar_ingreso;
    	 	}
    	 	elseif ($moneda_factura==1 && $moneda_ingreso==2) {
    	 		$parcial=$monto*$dolar_ingreso;
    	 	}
    	 	 else {
    	 	 	  $parcial=$monto;
    	 	 }

         $monto_pagado+=$parcial;
         
       
    	 }
    	 else  
    	 	$monto_pagado+=$monto;
   
    }
    else {
    	 $disabled="";
    	 $disable_egreso=" disabled";
    }
    
   }
   else {
   	
   $monto=$_POST["parcial_$i"];
   $fecha=fecha_db($_POST["fecha_$i"]) or $fecha=date("Y-m-d");
   $comentarios=$_POST["comentarios_".$i];
   if ($_POST["simbolo_".$i]) $simbolo_moneda=$_POST["simbolo_".$i];
   $dolar_ingreso=$_POST["dolaractual_".$i];
   $disable_egreso=" disabled ";
   $moneda_ingreso=$_POST['moneda_sel'];
   $monto_pagado=0;
  
   }
   
   
    if ($dolar_ingreso) $dolar_ingreso=number_format($dolar_ingreso,"2",".","");
    if ($monto) $monto=number_format($monto,"2",".","");  
    
   
       
    $link1=encode_link('elegir_caja_egresos_atadas.php',array("monto"=>$monto,"id_moneda"=>$id_moneda,"dolar_ingreso"=>$dolar_ingreso,"id_distrito"=>$id_distrito,"id_pagos_atadas"=>$id_pagos_atadas,
                        "moneda_factura"=>$moneda_factura,"id_vta_atada"=>$id_vta_atada,"monto_factura"=>$monto_original,"control_egreso"=>$ctrl_eg,"id_cobranza"=>$id_cobranza,"datos_vta"=>comprimir_variable($datos_vta)));
    $onclick_eg="window.open(\"$link1\",\"\",\"\")"; ?>
  <tr id=ma>
   <td>
     <input type="text" readonly name="fecha_<?=$i?>" value="<?=fecha($fecha)?>" size='10' <?=$disabled?> ><?= link_calendario("fecha_$i")?>
   </td>
   <td>
    <input type='text' name="dolaractual_<?=$i?>" size="6"  value="<? if ($id_moneda==2 || $moneda_factura==2) echo $dolar_ingreso?>"   <?if($moneda_factura==1 && $id_moneda==1) echo "disabled"?> <?=$disabled?>>
   </td>
   <td align="right">
     <input name="simbolo_<?=$i?>" type="text" style="text-align:right; background:inherit; border:none" value="<?=$simbolo_moneda?>" size="3" > 
     &nbsp;<input type="text" name="parcial_<?=$i?>" value="<?=$monto?>" size="10"  <?=$disabled?> > 

   </td> 
   <td> 
     <textarea name="comentarios_<?=$i?>" rows="3" cols="25" <?=$disabled?>><?=$comentarios?></textarea>
   </td>
 
     <input type="hidden" name="id_pagos_atadas_<?=$i?>" value='<?=$id_pagos_atadas?>'>
  
    <td> 
    <input type='submit' name='datos' value='Detalles' <?=$disabled?> onclick="document.all.num_fila.value=<?=$i?>;return (control_campos(<?=$i?>) && (control_fecha_venta(<?=$i?>)) && control_monto(<?=$i?>))">
 </td>  
    
 
</tr>

<?
}
echo "<input type='hidden' name='monto_pagado' value='$monto_pagado'>";
echo "<input type='hidden' name='cant_pagos_hechos' value='$cant_pagos_hechos'>"; 
echo "<input type='hidden' name='cant_pagos_anterior' value='$cant_pagos'>";
?>
</table>
 
<?

if ($_POST['datos'] || $_POST['ver_detalles']) {

$fila=$_POST['num_fila'];
$monto_parcial=$_POST["parcial_$fila"];

//busco simbolo de la moneda
$sql_moneda="select simbolo from moneda where id_moneda=".$_POST['moneda_pago'];
$res_moneda=sql($sql_moneda,"$sql_moneda") or fin_pagina();
$simbolo_ingreso=$res_moneda->fields['simbolo'];
echo "<input type='hidden' name='simbolo_ingreso' value='$simbolo_ingreso' ";

?>
<br><br>
<table  align="center">
  <tr>
      <td> &nbsp;&nbsp;&nbsp;&nbsp;<font color="Red" size="4px"><b>MONTO PARCIAL </b> </font> </td>
      <td> 
        <input name="simbolo_total" type="text" style="text-align:right; background:inherit; border:none" value="<?=$simbolo_ingreso?>" readonly size="3" >
        <input name="monto_total"   type="text" style="text-align:left; background:inherit; border:none" value="<?=$monto_parcial;?>" readonly size="15" >
      </td>
  </tr>
</table>   
<?

//query para generar los select
 $query="SELECT id_tipo_ingreso,nombre FROM caja.tipo_ingreso order by nombre";
 $res_ingreso=sql($query) or fin_pagina();
 $sql="select nombre,id_cuenta_ingreso from tipo_cuenta_ingreso order by nombre";
 $res_cuenta=sql($sql) or fin_pagina();

?>
<br>
<table align="center" border="1" cellspacing="2"  bordercolor="#000000" id='tabla_listado'>

<tr id=ma> 
   <td align='center'> NRO FACTURA</td> 
   <td>
     <table>
       <tr id=ma>  
          <td colspan=5> DETALLES INGRESOS </td>
       </tr>
     </table>
   </td>
</tr>
<?

$i=0;

while ($i < $cantidad_detalle) { 
	
$nombre_entidad =$_POST["nombre_entidad_$i"] or $nombre_entidad=$datos_vta[$i]['nombre'];
$id_tipo=$_POST["tipo_ing_$i"] or  $id_tipo=$datos_vta[$i]['id_tipo_ingreso'];
$id_cuenta=$_POST["cuentaing_$i"] or $id_cuenta=$datos_vta[$i]['id_cuenta_ingreso'];
$filtro=$_POST["filtro_$i"] or $filtro=$filtro=strtoupper(substr($nombre_entidad,0,1));	
$id_entidad =$_POST["id_entidad_$i"] or $id_entidad=$datos_vta[$i]['id_entidad'];
echo "<input type='hidden' name='filtro_$i' value='$filtro'";   
echo "<input type='hidden' name='nombre_entidad_$i' value='$nombre_entidad'";   
echo "<input type='hidden' name='id_cliente_$i' value='$id_entidad'";   
 
?>
<tr> 
   <td align='center'> <?=$datos_vta[$i]['nro_factura']?></td> 
   <td>
     <table>
       <tr>  
          <td> <b>Tipo Ingreso: </b>  <?=gen_select("tipo_ing_$i",$res_ingreso,$id_tipo,'id_tipo_ingreso','nombre',$des);?></td>
          <td> <b>Tipo Cuenta: </b><?=gen_select("cuentaing_$i",$res_cuenta,$id_cuenta,'id_cuenta_ingreso','nombre',$des);?></td>
          <td> <? tabla_filtros_nombres_atadas($i,$des);?></td>
       </tr>
      <tr>
          <td colspan=3> <b>Cliente: </b><?=gen_select_cliente($i,$filtro,$id_entidad,$des);?></td>
      </tr>
     </table>
   </td>
   
</tr>
<input type=hidden name="id_cobranza_<?=$i?>" value='<?=$datos_vta[$i]['id_cobranza']?>'>
<? 

$i++;
} ?>
</table>
<?
  echo "<br>";
  $link_cheques='chequesdif_cobranza_atadas.php';
  include_once('detalles_eg.php');
}

$ref = encode_link('lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_cobranza));
if ($control_ingreso==1) {   //si se realizaron todos los ingresos  
       	   $disabled_detalles=" disabled";
         ?>
            <script>
            document.all.cant_pagos.disabled=true;
            </script>
         <?
        }
          else  $disabled_detalles="";


?>

<table align="center"> 
<tr>
  <td colspan="2" align="center">
  <? if($_POST['datos'] || $_POST['ver_detalles']) {?>
      <input type="button" name="guardar" value="Guardar" onclick="if ( (cant_chequeados()) && (control_num()) && (calcular_total(1)))  {window.open('ingresos_atadas.php?first=1','','');}; ">
  <?}?>
  <input type="button" name="cerrar" value="Cerrar" onclick="window.opener.location.href='<?=$ref?>';window.close();">
  </td>
</tr>
</table>

<input type='hidden' name='boton_aceptar' value='0'>
<input type='hidden' name='ver_detalles' value='0'>

<?
$cant_facturas=count($datos_vta);

for($i=0;$i<$cant_facturas;$i++) {?>
    <input type='hidden' name='montos_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_IVA_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_GANANCIA_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_RIB_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_SUSS_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_MULTAS_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_DEPOSITO_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_OTROS_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_DIFERIDO_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_CHEQUE_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_EFECTIVO_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_DEVOLUCION_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_INTERESES_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_COMISIONES_<?=$datos_vta[$i]['id_factura']?>' value="">
    <input type='hidden' name='montos_GASTOS_<?=$datos_vta[$i]['id_factura']?>' value="">
<?}?>

</form>
</body>
</html>

<?=fin_pagina();?>


