<?
/*
$Author: mari $
$Revision: 1.19 $
$Date: 2007/01/08 15:16:25 $
*/

/*VENTA FACTURA PARA UNA SOLA FACTURA */

require_once("../../config.php");
require_once("func_cobranzas.php");
require_once("../contabilidad/funciones.php");

echo $html_header;
cargar_calendario();
 
//registra en caja el ingreso del detalle de la fila i
if ($_POST['guardar']) {

  $id_cobranza=$_POST['id_cobranza'];
  $id_datos_ingreso=retorna_id ('datos_ingresos','id_datos_ingreso','id_cobranza',$id_cobranza,'=');
  $id_detalle_ingreso=$_POST['id_detalle_ingreso'];
  $monto_pagado=$_POST['monto_pagado'];
  $i=$_POST['fila'];
  $fecha=fecha_db($_POST["fecha_".$i]);

  list($anio,$mes,$dia)=split("-",$fecha);
  $dia = date('w', mktime(0,0,0,$mes,$dia,$anio));
  $id_moneda=$_POST['moneda_sel'] or $id_moneda=$_POST['moneda_ingreso'];  
  $simbolo=$_POST['simbolo_total'];
  $distrito=$_POST['caja'] or $distrito=$_POST['id_distrito'];
  $id_licitacion=$_POST['id_licitacion'] or $id_licitacion=$_POST['lic'];
  $nro_factura=$_POST['nro_factura'];
  $monto_factura=$_POST['monto_factura'];
  $monto_original=$_POST["monto_original"];
  $moneda_factura=$_POST['moneda_factura'];
  $tipo_factura=strtoupper($_POST['tipo_factura']);
  $id_tipo_ingreso=$_POST['tipo_ing'];
  $cliente=$_POST['cliente'];
  $cuentaing=$_POST['cuentaing'];
  $dolar_ingreso=$_POST["dolaractual_".$i];
  $nombre=$_POST['nombre_cliente'] or $nombre=retorna_id('entidad','nombre','id_entidad',$cliente,'=');
  $parcial=$_POST["parcial_".$i];
  $id_cliente=$_POST['id_cliente']; //cliente de la factura     
  $valores=descomprimir_variable($_POST['valores_defecto']); 
  
  $monto_total=$_POST['monto_total'];
  $nombre_distrito=$_POST['nombre_distrito'];
    
  if ($dolar_ingreso !="" || $dolar_ingreso !=null) {
            $dolar =" Valor Dolar ".formato_money($dolar_ingreso);
  }
  else {
      $dolar_ingreso=0; 
      $dolar="";
  }
          
 if ($moneda_factura==2 && $id_moneda==1) {
          $parcial=$parcial/$dolar_ingreso;
 }
  elseif ($moneda_factura==1 && $id_moneda==2) {
          $parcial=$parcial*$dolar_ingreso;
        }

  $suma=$monto_pagado + $parcial;
  $suma=number_format($suma,"2",".","");
  $cant_pagos=$_POST['cant_pagos'];

  $db->StartTrans();//inicia transaccion
       
  if($dia!=0 && !feriado(fecha($fecha))) { //si dia es feriado o domingo 
       $id_datos_ingreso=datos_ingreso();
       $id_detalle_ingreso=guardar_detalle_ingresos_vta($i,$id_datos_ingreso,1,$id_detalle_ingreso);
      //si la caja no exite la crea, sino recupera el id
       if ($id_detalle_ingreso!="" || $id_detalle_ingreso!=null)  {
           $and=" and id_detalle_ingreso=$id_detalle_ingreso";
  }                                  
      $query="select id_caja,fecha,cerrada from caja 
              where fecha='$fecha' and id_distrito=$distrito and id_moneda=$id_moneda";     
      $caja_query=sql($query) or fin_pagina();
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
      $j=$i+1;
                                        
      if ($caja_cerrada==0) {

             $usuario=$_ses_user['name'];
             $cant_pagos=$_POST['cant_pagos'];
             $item="F$tipo_factura $nro_factura - $nombre  ";
             if (es_numero($id_licitacion))
                $item.=" - ID $id_licitacion";
             $item.= " PAGO PARCIAl $j/$cant_pagos ";  
             $comentarios=$_POST["comentarios_".$i];
             $monto=$_POST["parcial_".$i];
             $query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
             $id_ie=sql($query) or fin_pagina();
             $id=$id_ie->fields['id'];
             $sql_detalle="INSERT into caja.ingreso_egreso 
                           (id_ingreso_egreso,id_caja,id_entidad,id_tipo_ingreso,monto,comentarios,usuario,fecha_creacion,item,id_cuenta_ingreso) values
                          ($id,$id_caja,$cliente,$id_tipo_ingreso,$monto,'$comentarios','$usuario','$fecha','$item',$cuentaing)";
             /// actualizar detalle_ingresos con el id !!!!!!!!!!!!!!!!!
             $sql_ing="update detalle_ingresos set id_ingreso_egreso=$id where id_detalle_ingreso=$id_detalle_ingreso";;
             //Descuento lo ingresado del monto del seguimiento de cobros
                                          //$sql_cobranzas=" update cobranzas set monto=monto-$monto where id_cobranza=$id_cobranza";
             sql($sql_detalle,"ERROR EN SQL DETALLE") or fin_pagina (); //inserta en tabla detalle_ingresos cada uno de los egresos 
             sql($sql_ing) or fin_pagina();
                                          //sql($sql_cobranzas) or fin_pagina();
  ////egreos

       guarda_detalle_egresos($id_detalle_ingreso,$dolar_ingreso,$fecha);
       $comentario="";
 
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
    
    
 $tipo_f=strtoupper($tipo_factura);
 
 $egreso=array();
 $val_actual=array();  //guardo los valores seleccionados antes de guardar el egreso
 $i=0;
 
 $sql_detalle="";
 if ($_POST['chk_iva']==1) {   
 // recupera datos del iva
 $proveedor=$_POST['prov_iva'];
 $tipo_egreso=$_POST['tipo_iva'];
 $monto=$_POST['iva'];
 $nro_cuenta=$_POST['concepto_iva'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Multas Monto: ".$simbolo." ".formato_money($monto);
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=4 $and";
 $val_actual['MULTAS']['prov']=$proveedor;
 $val_actual['MULTAS']['cta']=$nro_cuenta;
 $val_actual['MULTAS']['tipo']=$tipo_egreso;
 }
 
 if ($_POST['chk_devp'] ==1) {
 // recupera datos de devolucion Prestamo
 $proveedor=$_POST['prov_devolucion'];
 $tipo_egreso=$_POST['tipo_devolucion'];
 $monto=$_POST['devolucion'];
 $nro_cuenta=$_POST['concepto_devolucion'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Devolución prestamo Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=11 $and";
 $val_actual['Devolucion prestamo']['prov']=$proveedor;
 $val_actual['Devolucion restamo']['cta']=$nro_cuenta;
 $val_actual['Devolucion prestamo']['tipo']=$tipo_egreso;
 }
 if ($_POST['chk_int'] ==1) {
 // recupera datos de intereses
 $proveedor=$_POST['prov_interes'];
 $tipo_egreso=$_POST['tipo_interes'];
 $monto=$_POST['interes'];
 $nro_cuenta=$_POST['concepto_interes'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Intereses Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=12 $and";
 $val_actual['intereses']['prov']=$proveedor;
 $val_actual['intereses']['cta']=$nro_cuenta;
 $val_actual['intereses']['tipo']=$tipo_egreso;

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
 
 if ($_POST['chk_adm'] ==1) {
 // recupera datos de Gastos administrativos
 $proveedor=$_POST['prov_gastoadm'];
 $tipo_egreso=$_POST['tipo_gastoadm'];
 $monto=$_POST['gastoadm'];
 $nro_cuenta=$_POST['concepto_gastoadm'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
	  $_POST["valor_dolar_imp"]=$dolar_ingreso;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_ingreso; 
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
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
	  $_POST["valor_dolar_imp"]=$dolar_ingreso;
      $_POST["monto_dolares"]=$monto;
      $_POST['monto_neto']=$monto * $dolar_ingreso; 
 }
 else  $_POST['monto_neto']=$monto;
 imputar_pago($pago,"",fecha($fecha));
 }
 
 if ($_POST['chk_dep'] ==1) {
 // recupera datos de deposito
 $proveedor=$_POST['prov_dep'];
 $tipo_egreso=$_POST['tipo_dep'];
 $monto=$_POST['deposito'];
 $nro_cuenta=$_POST['concepto_dep'];
 $banco=$_POST['banco'];
 $tipo=$_POST['tipo_deposito'];
 $tipo_f=strtoupper($tipo_factura);
 
 if (is_array($list_factura)) {
 	
  $comen= " F$tipo_f ";	
   foreach($list_factura as $key => $value)
     $comen.= $value."//\n";
  $comen=substr_replace($comen,'',(strrpos($comen,'/')));  
  $comen=substr_replace($comen,'',(strrpos($comen,'/')));  
  $comen.= "- $nombre - "; 
  $comen.= " ID $id_licitacion \n" ;
 }
 else {
 	
    $comen.=" F$tipo_f $nro_factura - $nombre - ID $id_licitacion";
 }
 
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
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
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Otros Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=6 $and";
 $val_actual['OTROS']['prov']=$proveedor;
 $val_actual['OTROS']['cta']=$nro_cuenta;
 $val_actual['OTROS']['tipo']=$tipo_egreso;
 $j++;
 }
 
 if ($_POST['chk_diferido'] ==1) {
 // recupera datos de diferidos
 $proveedor=$_POST['prov_diferido'];
 $tipo_egreso=$_POST['tipo_diferido'];
 $monto=$_POST['diferido'];
 $nro_cuenta=$_POST['concepto_diferido'];
 $id=guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha,$item,$nro_cuenta,$id_moneda);
 $egreso[$i++]="Id Egreso: ".$id." Descripción: Cheque Diferido Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set id_ingreso_egreso=$id,control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=9";
 $val_actual['Cheques diferidos']['prov']=$proveedor;
 $val_actual['Cheques diferidos']['cta']=$nro_cuenta;
 $val_actual['Cheques diferidos']['tipo']=$tipo_egreso;
 
 $sql="update cheques_diferidos set activo = 1 where id_chequedif in (select id_chequedif from cheque_cobranza where id_cobranza=$id_cobranza);";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
    
 $j++;
 }
 if ($_POST['chk_ficticio'] ==1) {
 // recupera datos de efectivo
 
 $monto=$_POST['ficticio'];
 $egreso[$i++]="Id Egreso: ".$id." Descripción: EFECTIVO (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=7 $and";
 
 }
 
 if ($_POST['chk_cheque'] ==1) {
 // recupera datos de cheque
 $monto=$_POST['cheque'];
 $egreso[$i++]="Id Egreso: ".$id." Descripción: CHEQUE (EL MONTO NO EGRESÓ de CAJA) Monto: ".$simbolo." ".$monto;
 $sql_detalle[]="update licitaciones.detalle_egresos set control_egreso=1 where id_cobranza=$id_cobranza and id_cob_egreso=8 $and";
 }
 
  
 if ($sql_detalle!="") {
 	 sql ($sql_detalle,"ERROR en $sql_detalle") or fin_pagina (); //inserta en tabla detalle_ingresos cada uno de los egresos 
 }
 
 

        } else   Error ("La Caja esta Cerrada. Seleccione fecha de la caja"); 

        } 
        else Error("Usted esta intentando insertar un ingreso de un día que no es habil");
              
        if (!$error)  {  
	
if ($id_moneda==1 && $moneda_factura==2) { //factura en dol y paso a pesos => recupera monto pagado en dolares
$sql_cant="select sum(monto_ingreso/dolar_ingreso) as total_ingresado
                     from licitaciones.cobranzas                       
                     join  licitaciones.datos_ingresos using (id_datos_ingreso)
                     join  licitaciones.pagos_ingreso using (id_datos_ingreso)
                     left join  licitaciones.detalle_ingresos using (id_detalle_ingreso)
                     where cobranzas.id_cobranza=$id_cobranza and detalle_ingresos.id_ingreso_egreso is not null";
$res_cant=sql($sql_cant) or fin_pagina();            
$total_ingresado=$res_cant->fields["total_ingresado"];

$sql="select sum(monto / dolar_egreso) as total_pagado from licitaciones.detalle_egresos 
	  join caja.ingreso_egreso using (id_ingreso_egreso) 
	  where id_cobranza=$id_cobranza and id_ingreso_egreso is not null";
$res=sql($sql) or fin_pagina();
$sql_efect="select sum(monto_detalle /dolar_egreso) as total_pagado from detalle_egresos where id_cobranza=$id_cobranza and id_cob_egreso=7";
$res_efect=sql($sql_efect) or fin_pagina();
$sql_cheque="select sum(monto_detalle / dolar_egreso) as total_pagado from detalle_egresos where id_cobranza=$id_cobranza and id_cob_egreso=8";
$res_cheque=sql($sql_cheque) or fin_pagina();
$total_pagado=$res->fields['total_pagado'] + $res_efect->fields['total_pagado']+ $res_cheque->fields['total_pagado'];
}
elseif  ($id_moneda==2 && $moneda_factura==1) {
$sql_cant="select sum(monto_ingreso*dolar_ingreso) as total_ingresado
                     from licitaciones.cobranzas                       
                     join  licitaciones.datos_ingresos using (id_datos_ingreso)
                     join  licitaciones.pagos_ingreso using (id_datos_ingreso)
                     left join  licitaciones.detalle_ingresos using (id_detalle_ingreso)
                     where cobranzas.id_cobranza=$id_cobranza and detalle_ingresos.id_ingreso_egreso is not null";
$res_cant=sql($sql_cant) or fin_pagina();            
$total_ingresado=$res_cant->fields["total_ingresado"];	
	
$sql="select sum(monto * dolar_egreso) as total_pagado from licitaciones.detalle_egresos 
	  join caja.ingreso_egreso using (id_ingreso_egreso) 
	  where id_cobranza=$id_cobranza and id_ingreso_egreso is not null";
$res=sql($sql) or fin_pagina();
$sql_efect="select sum(monto_detalle * dolar_egreso) as total_pagado from detalle_egresos where id_cobranza=$id_cobranza and id_cob_egreso=7";
$res_efect=sql($sql_efect) or fin_pagina();
$sql_cheque="select sum(monto_detalle * dolar_egreso) as total_pagado from detalle_egresos where id_cobranza=$id_cobranza and id_cob_egreso=8";
$res_cheque=sql($sql_cheque) or fin_pagina();
$total_pagado=$res->fields['total_pagado'] + $res_efect->fields['total_pagado']+ $res_cheque->fields['total_pagado'];
}
else {
$sql_cant="select sum(monto_ingreso) as total_ingresado
                     from licitaciones.cobranzas                       
                     join  licitaciones.datos_ingresos using (id_datos_ingreso)
                     join  licitaciones.pagos_ingreso using (id_datos_ingreso)
                     left join  licitaciones.detalle_ingresos using (id_detalle_ingreso)
                     where cobranzas.id_cobranza=$id_cobranza and detalle_ingresos.id_ingreso_egreso is not null";
$res_cant=sql($sql_cant) or fin_pagina();            
$total_ingresado=$res_cant->fields["total_ingresado"];

$sql="select sum(monto) as total_pagado from licitaciones.detalle_egresos 
	  join caja.ingreso_egreso using (id_ingreso_egreso) 
	  where id_cobranza=$id_cobranza and id_ingreso_egreso is not null";
$res=sql($sql) or fin_pagina();
$sql_efect="select sum(monto_detalle) as total_pagado from detalle_egresos where id_cobranza=$id_cobranza and id_cob_egreso=7";
$res_efect=sql($sql_efect) or fin_pagina();
$sql_cheque="select sum(monto_detalle) as total_pagado from detalle_egresos where id_cobranza=$id_cobranza and id_cob_egreso=8";
$res_cheque=sql($sql_cheque) or fin_pagina();
$total_pagado=$res->fields['total_pagado'] + $res_efect->fields['total_pagado']+ $res_cheque->fields['total_pagado'];
}
$total_pagado=number_format($total_pagado,"2",".","");
$total_ingresado=number_format($total_ingresado,"2",".","");

$diff=abs($total_pagado - $monto_original);
$diff_ing=abs($total_ingresado - $monto_original);

if (($total_ingresado == $monto_original || $diff_ing <= 0.03 ) && ($total_pagado == $monto_original || $diff<=0.03)) {
               
                 //arreglos con los datos del ingreso
                 $monto_factura=$_POST['monto_factura'];
                 $moneda_factura=$_POST['moneda_factura'];
                 $dolar_factura=$_POST['dolar_factura'];
                 if ($_POST['dolar_actual'] != "")
                     $dolar_ingreso=$_POST['dolar_actual'];
                     else $dolar_ingreso=0;
                 $datos_ing=array();
                 $datos_fact=array();
		         $datos_ing['nro_factura']= $nro_factura;
		         $datos_ing['distrito']= $distrito;
		         $datos_ing['id_licitacion']= $id_licitacion;
		         $datos_ing['tipo_factura']= $tipo_factura;
		         $datos_ing['simbolo']= $simbolo;
		         $datos_ing['id_moneda']= $id_moneda;
		         $datos_ing['monto_total']= $monto_total;
		         $datos_ing['nombre_cliente']= $nombre;
		         $datos_ing['tipo_ing']= $id_tipo_ingreso;
		         $datos_ing['cliente']= $cliente;
		         $datos_ing['cuentaing']= $cuentaing;
		         $datos_fact['simbolo_factura']= $_POST['simbolo_factura'];
		         $datos_fact['monto_factura']= $monto_factura;
		         $datos_fact['moneda_factura']= $moneda_factura;
		         $datos_fact['dolar_factura']= $dolar_factura;
		         $datos_fact['id_cliente']= $id_cliente;

                  mail_ingresos($datos_ing,$datos_fact,$id_datos_ingreso);
                  
                  mail_egresos_parciales($id_cobranza,$nombre_distrito,$id_licitacion,$nro_factura,$valores,$tipo_f);

   
                 $sql="update datos_ingresos set ctrl_ingreso=1,ctrl_egreso=1 where id_cobranza=$id_cobranza"; 
                 sql($sql) or fin_pagina();
                 $sql= "UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='".$_ses_user['name']."',
                        fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza =$id_cobranza";
 
                 sql($sql,"$sql") or fin_pagina();
                 
                // finalizar_vta_factura($id_cobranza,0);
                 
                 $ref = encode_link('../licitaciones/lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_cobranza));
              
          if (!$error) { ?>
            <script>
               window.opener.location.href='<?=$ref?>';
               window.close();
            </script>
            <?
           }
              }
              elseif ($total_ingresado > $monto_original){
                   aviso("Advertencia: Ha pagado un monto superior al  monto de la factura, verifique los datos");
              }

               //cierra transaccion
 	      if ($db->CompleteTrans() && !$error)  
             $msg="SE REALIZÓ CON ÉXITO EL INGRESO";
             else $msg="ERROR AL REALIZAR EL INGRESO ";
 
          }  
                    
}


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
  	            document.all.moneda_ingreso.value=2;
          }
          else if((mon_sel.value==1) )  
  	         simb.value="$";
  	         document.all.moneda_ingreso.value=2;
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

//controla que el campo porcentaje y el dolar sean valido sean numeros validos
function control_campos(i) {
        if (!i)	
              var i=document.all.fila.value;	
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
            //alert (parseInt(document.all.cant_pagos_anterior.value));
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
          /*parcial_texto=eval("document.all.parcialtexto_"+i);
          parcial_texto.value="";*/
          com=eval("document.all.comentarios_"+i);
          com.value="";
     }
}

//si cambia el valor del dolar o el valor del porcentaje 
// limpia el campo el monto
function limpiar_monto(i) {
          parcial=eval("document.all.parcial_"+i);
          parcial.value="";
          /*parcial_texto=eval("document.all.parcialtexto_"+i);
          parcial_texto.value="";*/
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
if (sum==0) return false;
else return true;
//return sum;     
}	

//controla que sean correctos los valores que se ingresan en el detalle del egreso
function control_num() {

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

function control_fecha_venta(i) {
 if (!i)	
    var i=document.all.fila.value;	

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


 
<form name="form1" action="venta_factura.php" method="post">
<input type="hidden" name="cheques_diferidos" id="cheques_diferidos" value="">
<? 
echo "<div align='center'> <font color=red> $msg </font></div>";

$id_cobranza = $parametros['id_cobranza'] or $id_cobranza=$_POST['id_cobranza'];
$monto_factura=$parametros['monto_original'] or $monto_factura=$_POST['monto_original'];
$monto_original=$parametros['monto_original'] or $monto_original=$_POST["monto_original"];
$tipo_factura=$parametros['tipo_factura'] or $tipo_factura=$_POST['tipo_factura']; 
$moneda_factura=$parametros['moneda_factura'] or $moneda_factura=$_POST['moneda_factura'];
$id_cliente=$parametros['id_cliente'] or $id_cliente=$_POST['id_cliente'];
$id_licitacion=$parametros['id_licitacion'] or $id_licitacion=$_POST['id_licitacion']; 
$nro_factura=$parametros['nro_factura'] or $nro_factura=$_POST['nro_factura']; 
$id_factura=$parametros['id_factura'] or $id_factura=$_POST['id_factura'];
$simbolo_factura=$parametros['simbolo_factura'] or $simbolo_factura=$_POST['simbolo_factura'];
$cant_pagos=$_POST['cant_pagos'];

$sql="select id_datos_ingreso,id_moneda,cant_pagos,id_distrito,simbolo
      from datos_ingresos 
      left join pagos_ingreso using (id_datos_ingreso)
      left join moneda using (id_moneda) 
      where id_cobranza=$id_cobranza";

$res=sql($sql) or fin_pagina();
$id_datos_ingreso=$res->fields['id_datos_ingreso'];
$moneda_ingreso=$res->fields['id_moneda'];

if ($res->RecordCount() > 0) {
               $hay_detalle=1; //se han guardado detalles de ingresos
               $monto_total=$monto_factura;
               if ($cant_pagos=="")  {
   	                   $cant_pagos=$res->fields['cant_pagos']; 
                       }
             $id_moneda=$res->fields['id_moneda'];
             $id_distrito=$res->fields['id_distrito'];
             }
             else {
   	                 $hay_detalle=0; //no se ha guardado detalles de ingresos
   	                 $monto_total=$monto_factura;
   	                 if ($cant_pagos =="") {
   	 	                 $cant_pagos=2;
   	                 }
   	                 $id_moneda  =$_POST['moneda_sel'] or $id_moneda=$moneda_factura;
                     $id_distrito=$_POST['caja'] or $id_distrito=1;
                  }
$fecha=date("Y-m-d"); 

if (!$moneda_ingreso) $moneda_ingreso=$id_moneda; 
 
if ($_POST['valores_defecto']) 
    $valores_defecto=descomprimir_variable($_POST['valores_defecto']);
else 
    $valores_defecto=armar_valores_defecto();

//serializo el arreglo
$valores=comprimir_variable($valores_defecto);  

?>
<input type="hidden" name="id_cobranza" value="<?=$id_cobranza?>">
<input type="hidden" name="monto_factura" value="<?=$monto_factura?>">
<input type="hidden" name="monto_original" value="<?=$monto_original?>"> 
<input type="hidden" name="tipo_factura" value="<?=$tipo_factura?>">
<input type="hidden" name="moneda_factura" value="<?=$moneda_factura?>">
<input type="hidden" name="id_cliente" value="<?=$id_cliente?>">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<input type="hidden" name="nro_factura" value="<?=$nro_factura?>">
<input type="hidden" name="id_factura" value="<?=$id_factura?>"> 
<input type="hidden" name="id_moneda" value="<?=$id_moneda?>"> 
<input type="hidden" name="valores_defecto" value="<?=$valores?>">


<input type="hidden" name="monto" value="<?=$monto?>">
<input type="hidden" name="moneda_ingreso" value="<?=$moneda_ingreso?>">
<input type="hidden" name="simbolo_factura" value="<?=$simbolo_factura?>">
<input type="hidden" name="id_detalle_ingreso" value="<?=$id_detalle_ingreso?>">
<!--<input type="hidden" name="monto_total_factura" value="<?//=$monto_total_factura?>"> --><!--ES el monto convertido a $ o a u$s-->
<input type="hidden" name="control_egreso" value="<?=$control_egreso?>">

<table align="center">
  <tr><td>
          <table class="bordes" align="center"> 
             <tr id=mo>
              <td colspan="2"> SELECCIONE DISTRITO </td>
             <tr>
             <tr bgcolor=<?=$bgcolor_out?>>
                <td><input type="radio" name="caja" value=1 <?if ($id_distrito==1) echo 'checked' ?>  <?if ($hay_detalle==1 && $id_distrito==2) echo "disabled"?>> </td>
                <td> CAJA SAN LUIS </td>
             </tr>
             <tr bgcolor=<?=$bgcolor_out?>>
                <td><input type="radio" name="caja" value=2 <?if ($id_distrito==2) echo 'checked'?>  <?if ($hay_detalle==1 && $id_distrito==1) echo "disabled"?>> </td>
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
			<td><input type="radio" name="moneda_sel" value=1 <?if ($id_moneda==1) echo 'checked'?>  <?if ($hay_detalle==1 && $id_moneda==2) echo "disabled"?>
             onclick="limpiar(this);cambiar_simbolo(this)">  </td>
			<td> PESOS </td>
		</tr>
		<tr bgcolor=<?=$bgcolor_out?>>
			<td><input type="radio" name="moneda_sel" value=2 <?if ($id_moneda==2) echo 'checked'?>  <?if ($hay_detalle==1 && $id_moneda==1) echo "disabled"?>
                   onclick="limpiar(this);cambiar_simbolo(this)";> </td>
			
			<td> DOLARES </td>
		</tr>
   </table>
  </td> 
 </tr>
</table>


<br>
   
<? 
$sql="select simbolo from moneda where id_moneda=$moneda_factura";
$res_sql=sql($sql) or fin_pagina();
$simbolo_factura=$res_sql->fields['simbolo'];
echo "<input type='hidden' name='simbolo_factura' value='$simbolo_factura'>";
if ($id_cliente !=null || $id_cliente!="") {
	$sql_cliente="select nombre from entidad where id_entidad=$id_cliente";
}
elseif ($id_factura!=null || $id_factura!="") {
  $sql_cliente="select cliente as nombre from facturas where id_factura=$id_factura";
}
if ($sql_cliente != "")
$res_cliente=sql($sql_cliente) or fin_pagina();

if ($id_factura!=null || $id_factura!="") {
  $sql="select cotizacion_dolar,iva_tasa from facturas where id_factura=$id_factura";
  $res_fact=sql($sql) or fin_pagina();
  $tasa_iva=$res_fact->fields['iva_tasa'];
}

?>

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
     <tr bgcolor=<?=$bgcolor_out?>>
        <td align="center"> <?=$nro_factura ?></td>
        <td align="center"> <?=$id_licitacion?></td>
        <td align="center"> <?=$res_cliente->fields['nombre']?></td>
        <td align="center"> <?=strtoupper($tipo_factura)?></td>
        <td align="center"> <?=$simbolo_factura." ".formato_money($monto_factura)?></td>
        <?if ($moneda_factura==2) { ?>
        <td align="center"> <?=formato_money($res_fact->fields['cotizacion_dolar'])?></td>
        <? } ?>
        <td align="center"><?=$tasa_iva?> %</td>
     </tr>
</table>  
</td>   
</tr>
</table>
<input type='hidden' name="dolar_factura" value='<?=$res_fact->fields['cotizacion_dolar']?>'>
<?
 if ($moneda_factura==1 && $id_moneda==1) 
	 $det_visib = "disabled";
	 
 if ($hay_detalle==1) 
    $simbolo_moneda=$res->fields['simbolo'];
    else  
    $simbolo_moneda=$_POST['simbolo_moneda'] or $simbolo_moneda=$simbolo_factura;


?>

<input type="hidden" name="neto" value="">
<input type="hidden" name="simbolo_moneda" value="<?=$simbolo_moneda?>">

 
<?
$datos_vta=detalle_vta($id_cobranza);  //tipo cuenta_ingreso,id_cliente,id_tipo_ongreso guardados 
$fila=$_POST['fila'] or $fila=0;
?>

<table align="center" border="1" cellspacing="2"  bordercolor="#000000">
<tr id="mo"><td colspan="7"> DETALLES VENTA DE FACTURA</td></tr>
<tr id=ma>
       <td colspan="7" align="right"> 
       Cantidad de pagos 
        <select name=cant_pagos onchange="var limite=eval(parseInt(document.all.cant_pagos_hechos.value) +1);if(limit_cant_pagos())alert('No se puede seleccionar menos de ' + eval(limite) +' pagos. Debe quedar al menos un pago libre para terminar de pagar la factura .');else document.all.form1.submit()">
           <? $total_pagos=10;
              for($i=2;$i<=$total_pagos;$i++){ ?>
              <option value='<?=$i?>' <?if ($i==$cant_pagos) echo 'selected'?>> <?= $i ?> </option>
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

<input type='hidden' name='fila' value='<?=$fila?>'> 
<input type='hidden' name='id_detalle_ingreso' value=""> 

<? 
$sql="select id_detalle_ingreso,detalle_ingresos.id_ingreso_egreso,fecha_ingreso,suma_iva,datos_ingresos.id_moneda,
		porcentaje,monto_ingreso,comentarios,id_datos_ingreso,control_egreso,dolar_ingreso
		from licitaciones.cobranzas 
		join licitaciones.datos_ingresos using (id_datos_ingreso)
 		join licitaciones.pagos_ingreso using (id_datos_ingreso)
		join licitaciones.detalle_ingresos using (id_detalle_ingreso)
		left join (
                   select distinct control_egreso,id_detalle_ingreso
 		            from licitaciones.detalle_egresos group by id_detalle_ingreso, control_egreso, id_detalle_ingreso
		           ) as res1 using (id_detalle_ingreso)
		where cobranzas.id_cobranza=$id_cobranza order by id_detalle_ingreso,id_ingreso_egreso";

$res_detalles=sql($sql) or fin_pagina();
   
if (!permisos_check("inicio","licitaciones_ingreso_cob")) {
       //permiso para realizar ingresos
       $permiso_guardar_ing=0;
       } 
       else {
       $permiso_guardar_ing=1;
       }
   
   
if ($id_datos_ingreso==null || $id_datos_ingreso=="" ) {
           $permiso_guardar="";
           }
            else {
 	          $permiso_guardar=" disabled ";
              } 


?> 
<br>
<?
$monto_pagado=0;

$res_detalles->MoveFirst();
for ($i=0;$i<$cant_pagos;$i++) {

if ($hay_detalle) {
    $monto=$res_detalles->fields['monto_ingreso'] or $monto=$_POST["parcial_$i"];
    $comentarios=$res_detalles->fields['comentarios'] or  $comentarios=$_POST["comentarios_".$i];;
    $id_ingreso_egreso=$res_detalles->fields['id_ingreso_egreso'];
    $id_detalle_ingreso=$res_detalles->fields['id_detalle_ingreso'];
    $control_egreso=$res_detalles->fields['control_egreso'];	
    $moneda_ingreso=$res_detalles->fields['id_moneda'];
    $fecha=$res_detalles->fields['fecha_ingreso'] or $fecha=fecha_db($_POST["fecha_$i"]) or  $fecha=date("Y-m-d");
    if ($res_detalles->fields['dolar_ingreso']) {
       $dolar_ingreso=$res_detalles->fields['dolar_ingreso'];
    }
    elseif ($_POST["dolaractual_".$i]) 
        $dolar_ingreso=$_POST["dolaractual_".$i];
       else $dolar_ingreso="";
   
    $res_detalles->MoveNext();
  
    if ($id_ingreso_egreso!="") {
    	 $disabled=" disabled"; 
         $cant_pagos_realizados++;
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
    
       if ($control_ingreso==1) { 
       	   $disabled_detalles=" disabled";
       	   ?>
            <script>
            document.all.cant_pagos.disabled=true;
            </script>
         <?
         
        }
          else  $disabled_detalles="";
   }
   else {
   $monto=$_POST["parcial_$i"];
   $fecha=fecha_db($_POST["fecha_$i"]) or $fecha=date("Y-m-d");
   $comentarios=$_POST["comentarios_".$i];
   $disable_egreso=" disabled ";
   $monto_pagado=0;
   if ($_POST["simbolo_".$i]) $simbolo_moneda=$_POST["simbolo_".$i];
   $dolar_ingreso=$_POST["dolaractual_".$i];
   $moneda_ingreso=$_POST['moneda_sel'];
   
   }
       
    //$monto_pagado=number_format($monto_pagado,"2",".","");
    if ($dolar_ingreso) $dolar_ingreso=number_format($dolar_ingreso,"2",".","");
    if ($monto) $monto=number_format($monto,"2",".","");  
    echo "<input type='hidden' name='monto_pagado' value='$monto_pagado'>";
    echo "<input type='hidden' name='cant_pagos_hechos' value='$cant_pagos_hechos'>";
echo "<input type='hidden' name='cant_pagos_anterior' value='$cant_pagos'>";
      ?>
      
  <tr id=ma>
   <td>
     <input type="text" name="fecha_<?=$i?>" value="<?=fecha($fecha)?>" size='10' <?=$disabled?> ><?= link_calendario("fecha_$i")?>
   </td>
   <td>
    <input type='text' name="dolaractual_<?=$i?>" size="6" onblur="limpiar_monto(<?=$i?>)" value="<? if ($id_moneda==2 || $moneda_factura==2) echo $dolar_ingreso?>"   <?if($moneda_factura==1 && $id_moneda==1) echo "disabled"?> <?=$disabled?>>
   </td>
   <td align="right">
     <input name="simbolo_<?=$i?>" type="text" style="text-align:right; background:inherit; border:none" value="<?=$simbolo_moneda?>" size="3" > 
     &nbsp;<input type="text" name="parcial_<?=$i?>" value="<?=$monto?>" size="10"  <?=$disabled?> > 
     
   </td> 
   <td> 
     <textarea name="comentarios_<?=$i?>" rows="3" cols="25" <?=$disabled?>><?=$comentarios?></textarea>
   </td>
    <td>
         <input type="submit" name="datos" value="Detalles" <?=$disabled?> onclick='document.all.fila.value=<?=$i?>;return (control_campos(<?=$i?>) && (control_fecha_venta(<?=$i?>)) && control_monto(<?=$i?>))'  >      	   
   </td>
</tr>

<?
} 
?>
</table>
<br>

<?
$ref = encode_link('lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_cobranza));
if ($_POST['datos'] || $_POST['ver_detalles']) {
$fila=$_POST['fila'];
$monto_parcial=$_POST["parcial_$fila"];

//busco simbolo de la moneda
$sql_moneda="select simbolo from moneda where id_moneda=".$_POST['moneda_ingreso'];
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
        <input name="monto_total"  type="text" style="text-align:left; background:inherit; border:none" value="<?=$monto_parcial;?>" readonly size="15" >
      </td>
  </tr>
</table>   
	
<?	
$id_entidad=$_POST['cliente'] or  $id_entidad=$datos_vta['id_entidad'] or   $id_entidad=$id_cliente;
$id_tipo=$_POST['tipo_ing'] or  $id_tipo=$datos_vta['id_tipo_ingreso'] or   $id_tipo=retorna_id('tipo_ingreso','id_tipo_ingreso','nombre','Cobros','ilike');
$id_cuenta=$_POST['cuentaing'] or   $id_cuenta=$datos_vta['id_cuenta_ingreso'] or  $id_cuenta=retorna_id('tipo_cuenta_ingreso','id_cuenta_ingreso','nombre','Cobros (Facturas Clientes)','ilike');
     
 if ($id_entidad!="")
      $nombre_entidad=retorna_id('entidad','nombre','id_entidad',$id_entidad,'=');
$filtro=$_POST['filtro'] or $filtro=$filtro=strtoupper(substr($nombre_entidad,0,1));

 //query para generar los select
 $query="SELECT id_tipo_ingreso,nombre FROM caja.tipo_ingreso order by nombre";
 $res_ingreso=sql($query) or fin_pagina();
 $query="SELECT id_entidad,nombre FROM entidad where nombre ilike '$filtro%' order by nombre";
 $res_entidad=sql($query) or fin_pagina();
 $sql="select nombre,id_cuenta_ingreso from tipo_cuenta_ingreso order by nombre";
 $res_cuenta=sql($sql) or fin_pagina();
 

echo "<input type='hidden' name='filtro' value='$filtro'>";   
$sql_cant="select detalle_ingresos.id_ingreso_egreso,ctrl_ingreso
             from licitaciones.cobranzas                       
             join  licitaciones.datos_ingresos using (id_datos_ingreso)
             join  licitaciones.pagos_ingreso using (id_datos_ingreso)
             left join  licitaciones.detalle_ingresos using (id_detalle_ingreso)
             where cobranzas.id_cobranza=$id_cobranza and detalle_ingresos.id_ingreso_egreso is not null";
$res_cant=sql($sql_cant) or fin_pagina();
$cant_pagos_hechos=$res_cant->RecordCount();
$control_ingreso=$res_cant->fields['ctrl_ingreso'];

if  ($cant_pagos_hechos >0) $des='disabled';
  else $des="";
?>

<table align="center" border="1" cellspacing="2"  bordercolor="#000000">
<tr id="mo"><td colspan="3"> DATOS DEL INGRESO </td></tr>
<tr><td colspan=3><?tabla_filtros_nombres(1,$des);?></td></tr>
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

<?
if ($des=='disabled') { ?>
<input type='hidden' name='tipo_ing' value='<?=$id_tipo?>'>
<input type='hidden' name='cliente' value='<?=$id_entidad?>'>
<input type='hidden' name='cuentaing' value='<?=$id_cuenta?>'>
<? }

echo "<br>";
$link_cheques="chequesdif_cobranza.php";
  include_once('detalles_eg.php');
}?>

<br> 
 
 
<table align="center"> 
<tr>
  <td colspan="2" align="center">
     <? if($_POST['datos'] || $_POST['ver_detalles']) {   ?>
      <input type="submit" name="guardar" value="Guardar" onclick="return ((cant_chequeados()) && (control_num()) && (calcular_total(1))) ">
      &nbsp;&nbsp;
  <?}?>
  <input type="button" name="cerrar" value="Cerrar" onclick="window.opener.location.href='<?=$ref?>';window.close();"></td>
</tr>
</table>
<input type='hidden' name='ver_detalles' value='0'>
</form>
</body>
</html>