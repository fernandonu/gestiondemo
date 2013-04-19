<?
/*
$Author: mari $
$Revision: 1.5 $
$Date: 2006/06/13 15:32:19 $
*/

/*****************************************************************************
 *@ datos_ingreso_atadas
 *@ guarda los datos del ingreso 
 *@ id_distrito,id_moneda, id_cobranza,cant_pagos, cant_facturas 
****************************************************************************/
function datos_ingreso_atadas() {
 global $db;
 
 $id_vta_atada=$_POST['id_vta_atada'];
 $id_distrito=$_POST['caja'];
 $id_moneda=$_POST['moneda_sel'];
 $cant_facturas=$_POST["cant_facturas"]; 
 $cant_pagos=$_POST['cant_pagos'] or $cant_pagos;
 $cant_pagos_hechos=$_POST['cant_pagos_hechos'];  
 $id_cobranza=$_POST['id_cobranza'];  //cobranza primaria
 $cant_pagos_hechos=$_POST['cant_pagos_hechos'];

 $db->StartTrans();
 $sql="";
 $sql_datos="";
 
 if ($id_vta_atada !=null || $id_vta_atada!= "") {
 	
  if ($cant_pagos_hechos > 0) {  // si ya se realizaron pagos solo se actualiza a cantidad de pagos
    $sql_datos[]="update venta_fac_atadas set cant_pagos=$cant_pagos 
                  where id_vta_atada=$id_vta_atada";
  
  } else {
     $sql_datos[]="update venta_fac_atadas set id_distrito=$id_distrito,
                   id_moneda=$id_moneda,cant_pagos=$cant_pagos,cant_facturas=$cant_facturas  
                   where id_vta_atada=$id_vta_atada";
     
    for ($i=0;$i<$cant_facturas;$i++) {
 	$id_tipo_ingreso=$_POST["tipo_ing_$i"];
    $id_entidad=$_POST["id_cliente_$i"];
    $id_cuenta_ingreso=$_POST["cuentaing_$i"];
    $id_cob=$_POST["id_cobranza_$i"];
    
     
     $sql_datos[]="update detalles_vta_atada set id_tipo_ingreso=$id_tipo_ingreso,id_entidad=$id_entidad,
                  id_cuenta_ingreso=$id_cuenta_ingreso where id_vta_atada=$id_vta_atada and id_cobranza=$id_cob"; 
    }    
  }	

  if ($sql_datos!="") sql($sql_datos,"error en update endatos_ingreso_atadas") or fin_pagina();
 
  } else { //nueva venta 
  
  $query="select nextval('venta_fac_atadas_id_vta_atada_seq') as id_vta_atada";
  $res_query=sql($query,"$query") or fin_pagina();
  $id_vta_atada=$res_query->fields['id_vta_atada'];	
  
  $sql_datos[]="insert into venta_fac_atadas 
             (id_vta_atada,id_cobranza,id_distrito,id_moneda,cant_pagos,cant_facturas) 
              values 
             ($id_vta_atada,$id_cobranza,$id_distrito,$id_moneda,$cant_pagos,$cant_facturas)";
  $sql_datos[]="update cobranzas set id_vta_atada=$id_vta_atada where id_cobranza=$id_cobranza";

  $res=sql($sql_datos,"error en datos ing atadas") or fin_pagina(); 
 
  
  for ($i=0;$i<$cant_facturas;$i++) {
 	$id_tipo_ingreso=$_POST["tipo_ing_$i"];
    $id_entidad=$_POST["id_cliente_$i"];
    $id_cuenta_ingreso=$_POST["cuentaing_$i"];
    $id_cob=$_POST["id_cobranza_$i"];
   
    
   $sql[]="insert into detalles_vta_atada
           (id_vta_atada,id_tipo_ingreso,id_entidad,id_cuenta_ingreso,id_cobranza) 
           values 
           ($id_vta_atada,$id_tipo_ingreso,$id_entidad,$id_cuenta_ingreso,$id_cob)";
    
    $sql[]="update cobranzas set id_vta_atada=$id_vta_atada where id_cobranza=$id_cob";
 }

 if ($sql!="") 
     sql($sql,"datos ingresos") or fin_pagina();
 }
 
 if ($db->CompleteTrans()) {
   return $id_vta_atada;
 }
} //fin datos_ingresos
 

//guarda los detalles de los ingresos 
function guardar_detalle_ingresos_atadas($i,$id_vta_atada,$id_pagos_atadas="") {
global $db;

$sql_detalle="";

  $monto_ing=$_POST["parcial_".$i] or $monto_ing='NULL';  
  $fecha_ing=fecha_db($_POST["fecha_".$i]) or $fecha_ing=date("Y-m-d",mktime()); 
  $comentarios=$_POST["comentarios_".$i];
  
  if ($_POST["dolaractual_".$i])
              $dolar=$_POST["dolaractual_".$i];
             else 
              $dolar=0;
  
$db->StartTrans();
 if ($id_pagos_atadas!= "") {
    $sql_detalle=" update pagos_atadas set monto=$monto_ing,comentarios='$comentarios',
                    valor_dolar=$dolar,fecha_ing='$fecha_ing'
                    where id_pagos_atadas=$id_pagos_atadas";
 }
 else {
  $query="select nextval('pagos_atadas_id_pagos_atadas_seq') as id_pagos_atadas";
  $res_id=sql($query) or fin_pagina();
  $id_pagos_atadas=$res_id->fields['id_pagos_atadas'];	

  $sql_detalle[]="insert into pagos_atadas 
                  (id_pagos_atadas,id_vta_atada,monto,comentarios,valor_dolar,fecha_ing,ingresos,egresos)  values 
                  ($id_pagos_atadas,$id_vta_atada,$monto_ing,'$comentarios',$dolar,'$fecha_ing',0,0)";
 }
if ($sql_detalle!="") 
           sql($sql_detalle,"Error en guardar detalle $sql_detalle") or fin_pagina();
           
$db->CompleteTrans();  

 return $id_pagos_atadas;     
}  //fin guardar_detalle_ingresos

/*****************************************************************************
 *gen_select_cliente
 *genera un select de nombre cliente_$i 
 *@ filtro letra para el query
 *@ des indica si va habilitado o no
 *@ id_entidad id a seleccinar
****************************************************************************/
function gen_select_cliente ($i,$filtro,$id_entidad,$des="") {
 
 $query="SELECT id_entidad,nombre FROM entidad where nombre ilike '$filtro%' order by nombre";
 $resultados=sql($query) or fin_pagina();
 
 $cantidad=$resultados->RecordCount();
 
 echo "<select name='id_cliente_$i' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' $des>";
  for($i=0;$i<$cantidad;$i++) {
	$id=$resultados->fields['id_entidad'];
	$string=$resultados->fields['nombre'];
	  ?>
	  <option value='<?=$id?>' <? if ($id_entidad==$id) echo 'selected'?> > <?=$string?> </option>
	 <? 
	$resultados->MoveNext();
 }
  echo "</select>";
}//fin de gen_select_tipo

function tabla_filtros_nombres_atadas($j,$des) {
 $abc=array("a","b","c","d","e","f","g","h","i","j","k","l","m",
			"n","ñ","o","p","q","r","s","t","u","v","w","x","y","z");
$cantidad=count($abc);
if (!isset($_POST["det_filtro_$j"]) || $_POST["det_filtro_$j"]!=1)
  $visib="none";
  
echo "<b>Filtro Cliente:</b>
      <input type=checkbox $des class='estilos_check' name=\"det_filtro_$j\" value=1 onclick='javascript:(this.checked)?Mostrar(\"tabla_filtro_$j\"):Ocultar(\"tabla_filtro_$j\");'"; if ($_POST["det_filtro_$j"]==1) echo 'checked'; echo ">";
echo "<div id=tabla_filtro_$j style='display:$visib'>";
echo "<table id=mo ' align='center' width='80%' class='bordes'>";

	echo "<tr >";
	for($i=0;$i<$cantidad;$i++){
		$letra=$abc[$i];
	   switch ($i) {
					 case 13:
					 case 27:echo "</tr><tr>";
						  break;
				   default:
				  } //del switch
    ?>
      <td style='cursor:hand' onclick="document.all.filtro_<?=$j?>.value='<?=$letra?>';document.all.ver_detalles.value=1;document.form1.submit();"><?=$letra?></td>
    <?
  }//del for
   echo "</tr>";
   echo "<tr>";
   echo "</td>";
   echo "</tr>";
   echo "</table>";
   echo "</div>";
}  //de la funcion  para las letras en la lista de entidades y proveedores



/*****************************************************************
  *@  compara datos de la factura y todos los ingresos,si hay diferencia 
  *@  en los datos manda un mail avisando cual es la diferencia
  *@  si no hay diferencia el mail se manda avisando que se realizo el ingreso
  *@  $datos_vta -> datos de la venta
  *@  id_vta_atada -> id de la venta
  *@  $distrito  -> nombre de la caja
  *@  monto_total -> monto_total de las facturas
  
*********************************************************/

function mail_ingresos_atadas($datos_vta,$id_vta_atada,$distrito,$monto_total) {

//$datos_vta tiene los datos guardados	
$para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";


$cant=count($datos_vta);
$asunto="Ingreso desde venta de factura ";

$sql="select nombre from distrito where id_distrito=".$distrito;
$res_d=sql($sql,"$sql") or fin_pagina();
$dist=$res_d->fields['nombre'];
$dist = str_replace("- GCBA"," ",$dist);

//tipo de cuenta y tipo de egreso por defecto
$id_tipo=retorna_id('tipo_ingreso','id_tipo_ingreso','nombre','Cobros','ilike');
$id_cuenta=retorna_id('tipo_cuenta_ingreso','id_cuenta_ingreso','nombre','Cobros (Facturas Clientes)','ilike');

//$sql_datos_fact="select "

$list.="(";
$contenido ="Se realizarón ingresos parciales en la caja de ". $dist." \n Para las facturas ";
for($i=0;$i<$cant;$i++) {
  $list.=$datos_vta[$i]['id_cobranza'].",";	
  $contenido.=" \nNRO:  ". $datos_vta['tipo_factura']." ".$datos_vta[$i]['nro_factura'] ; 
  if (es_numero($datos_vta[$i]['id_licitacion']))$contenido.=" - Asociada a la Licitacion: ". $datos_vta[$i]['id_licitacion'] ." \n ";
}
  $contenido.=$datos_vta[$i]['nro_factura'];
$list=substr_replace($list,')',(strrpos($list,',')));

//datos originales de las facturas 
$sql_fact="select cobranzas.nro_factura,cobranzas.id_moneda,cobranzas.id_licitacion,tipo_factura, 
           cobranzas.id_entidad,cobranzas.monto_original,
           facturas.cotizacion_dolar,tipo_factura,simbolo,entidad.nombre,entidad.id_entidad 
           from licitaciones.cobranzas 
           left join facturacion.facturas using (id_factura) 
           left join licitaciones.entidad on entidad.id_entidad=cobranzas.id_entidad 
           left join licitaciones.moneda on cobranzas.id_moneda=moneda.id_moneda 
           where id_cobranza in $list order by cobranzas.nro_factura";
$res_fact=sql($sql_fact) or fin_pagina();
$simbolo_factura=$res_fact->fields['simbolo'];
$moneda_factura=$res_fact->fields['id_moneda'];
$dolar_factura=$res_fact->fields['cotizacion_dolar'];


if ($moneda_factura==1) {
	if ($monto_total > 5000) {
        $para.=',corapi@coradir.com.ar';
    }
} elseif (($monto_total * $dolar_factura) > 5000) {
        $para.=',corapi@coradir.com.ar'; 
}	


//selecciono los ingresos realizados
$sql="select id_ingreso_egreso,simbolo,ingreso_egreso.monto,valor_dolar,
      ingreso_egreso.usuario,fecha_creacion,caja.id_moneda
	  from licitaciones_datos_adicionales.pagos_atadas
      join licitaciones_datos_adicionales.detalle_pagos_atadas using (id_pagos_atadas)
      join caja.ingreso_egreso using (id_ingreso_egreso)
	  join caja.caja using (id_caja)
	  join licitaciones.moneda using (id_moneda)
	  where id_vta_atada=$id_vta_atada 
      order by id_ingreso_egreso";
$res=sql($sql) or fin_pagina();
$simbolo_ingreso=$res->fields['simbolo'];
$id_moneda=$res->fields['id_moneda'];


//armo un arreglo con los datos del id_cliente, nro factura y nombre de cliente
$datos_fact=array();
$monto_a_pesos=0;
while (!$res_fact->EOF) {
  $nro=$res_fact->fields['nro_factura'];	
  $datos_fact[$nro]['id_cliente']=$res_fact->fields['id_entidad'];
  $datos_fact[$nro]['cliente']=$res_fact->fields['nombre'];
  $datos_fact[$nro]['dolar_factura']=$res_fact->fields['cotizacion_dolar'];
   
  if ($moneda_factura==2 && $id_moneda==2) {
     $a_pesos=$res_fact->fields['monto_original'] * $res_fact->fields['cotizacion_dolar'];  //monto de las facturas pasadas a pesos
     $monto_a_pesos+=$a_pesos;    
  }
 $res_fact->moveNext(); 
}

$monto_ingreso=0;
$total_ingreso=0;

if ($moneda_factura==2) {  //la factura esta en dolares
   if ($id_moneda==1)  //de dol a pesos => mult 
          $mult=0; 
   else $mult=1; //pago en dolares
}
elseif ($id_moneda==2) {
   $mult=1;
}
else $mult=-1;

while (!$res->EOF) { 
	$fecha=$res->fields['fecha_creacion'];
   	$dia=fecha($fecha);
	$usuario=$res->fields['usuario'];
	$contenido.= "\n INGRESO ID: ".$res->fields['id_ingreso_egreso'];
	$contenido.= "  MONTO ". $res->fields['simbolo']." ".formato_money($res->fields['monto']);
	if ($id_moneda==2) $contenido.=" VALOR DOLAR : ".formato_money($res->fields['valor_dolar']);
	$contenido.= "  Ingreso Realizado por el usuario ". $usuario  ." el día $dia. \n";
	$contenido.= "\n";
	$total_ingreso+=$res->fields['monto'];
	if ($mult==1) 
	   $parcial=$res->fields['monto'] * $res->fields['valor_dolar']; // factura en dolares y pago en dolares
	elseif($mult==0) 
	   $parcial=$res->fields['monto'] / $res->fields['valor_dolar'];
	 
    $monto_ingreso+=$parcial;  //la suma ingresada en pesos (en el caso que las facturas sean en dolares)
	$res->MoveNext();
}

if ($moneda_factura==2)  //facturas en dolares

  if($id_moneda==2) {	  //pago en dolares
   $contenido.="\n MONTO DE LAS FACTURAS => $ ".formato_money($monto_a_pesos);
   $contenido.="\n MONTO DE LOS INGRESOS => $ ".formato_money($monto_ingreso);
  }
   else {  //pago en pesos
    $contenido.="\n MONTO DE LAS FACTURAS =>". $simbolo_factura." ".formato_money($monto_total);
    $contenido.="\n MONTO DE LOS INGRESOS =>". $simbolo_ingreso." ".formato_money($monto_ingreso);
 } 
else { //factura en pesos
  if ($id_moneda==2) {  //pago en dolares
  $contenido.="\n MONTO DE LAS FACTURAS => $ ".formato_money($monto_total);
  $contenido.="\n MONTO DE LOS INGRESOS => $". formato_money($monto_ingreso);
  }
   else {
  $contenido.="\n MONTO DE LAS FACTURAS =>". $simbolo_factura." ".formato_money($monto_total);
  $contenido.="\n MONTO DE LOS INGRESOS =>". $simbolo_ingreso." ".formato_money($total_ingreso);
  } 

}


//INDICA SI HAY DIFERENCIA ENTRE EL MONTO DE LAS FACTURAS Y EL MONTO DE LOS INGRESOS

if  ($moneda_factura==2) { //factura en dolares
 
  //factura en dolares
  switch ($id_moneda) {
     case 1: { //ingreso en pesos
            $diff=$monto_ingreso - $monto_total;
            break;
            }
      case 2: { //ingreso en dolares
             $diff= $monto_ingreso - $monto_a_pesos;
             break;
            }
 } 

} //fin switch 

if ($diff < 0 ) {
$contenido.= "\n DIFERENCIA FALTANTE PARA NOTA DE CREDITO $ ".formato_money($diff)."\n";
}
elseif ($diff > 0  && formato_money($diff)!='0,00') {
$contenido.= "\n DIFERENCIA (SOBRA)  PARA NOTA DE DEBITO  $ ".formato_money($diff)."\n";
}

for ($i=0;$i<$cant;$i++) {
if ($id_tipo != $datos_vta[$i]['id_tipo_ingreso']) {
  $contenido.="\n Se ha modificado el tipo de ingreso en la factura: ". $datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura']."\n";
  $sql="select nombre from tipo_ingreso where id_tipo_ingreso=".$datos_vta[$i]['id_tipo_ingreso']; 
  $res=sql($sql) or fin_pagina ();
  $ing=$res->fields['nombre'];
  $contenido.="     TIPO DE INGRESO: Cobros   TIPO DE INGRESO MODIFICADO:". $ing."\n";
} 

if ($id_cuenta != $datos_vta[$i]['id_cuenta_ingreso']) {
	$contenido.=" \nSe ha modificado el Tipo de Cuenta ". $datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura']."\n:  \n";
  	$sql="select nombre from caja.tipo_cuenta_ingreso where id_cuenta_ingreso=".$datos_vta[$i]['id_cuenta_ingreso']; 
    $res=sql($sql) or fin_pagina ();
    $cta=$res->fields['nombre'];  
	$contenido.="TIPO CUENTA: Cobros (Facturas Clientes) TIPO CUENTA MODIFICADO:". $cta."\n";
 }
 
 $nro=$datos_vta[$i]['nro_factura'];
 if ($datos_fact[$nro]['id_cliente'] != $datos_vta[$i]['id_entidad'] ) {
   $contenido.= "\nSe ha modificado la entidad para la factura: ". $datos_vta[$i]['tipo_factura']." ".$datos_vta[$i]['nro_factura'].":  \n";
   $contenido.= "ENTIDAD : ".$datos_fact[$nro]['cliente']."  ENTIDAD MODIFICADA: ".$datos_vta[$i]['nombre']." \n";
 }
}

 enviar_mail($para,$asunto,$contenido,'','','',0);   

}


//guarda los detalles de los egresos en detalle_egresos_atadas
function guarda_detalle_egresos_atadas($id_cobranza,$id_pagos_atadas,$id_moneda,$dolar_actual,$fecha_detalle="") {
global $db;

if (!$fecha_detalle) $fecha_detalle = date("Y-m-d",mktime());
if (!$dolar_actual)  $dolar_actual = "NULL";

$db->StartTrans();
 
 $sql="delete from detalle_egresos_atadas where id_pagos_atadas=$id_pagos_atadas";
 sql($sql,"$sql") or fin_pagina();

 //borro los cheques ligados a la venta 
 $sql="select id_chequedif from cheque_cobranza_atadas where id_pagos_atadas=$id_pagos_atadas";
 $resultado_idcheques = sql($sql,"$sql") or fin_pagina();
 
 $sql="delete from cheque_cobranza_atadas where id_pagos_atadas=$id_pagos_atadas";
 sql($sql,"$sql") or fin_pagina();
 
 $list="(";
 if ($resultado_idcheques->RecordCount() > 0) {
 while(!$resultado_idcheques->EOF) {
   $list.= $resultado_idcheques->fields['id_chequedif'].",";
   $resultado_idcheques->MoveNext();
 }
 
  $list=substr_replace($list,')',(strrpos($list,',')));
  $sql="delete from cheques_diferidos where id_chequedif in $list";
  sql($sql,"delete: $sql") or fin_pagina();
 }
 
 $sql_detalle="";
 $sql_detalle_dep;
 $det=array();

if ($_POST['chk_iva']==1) {
 // recupera datos del iva
 $proveedor=$_POST['prov_iva'];
 $tipo_egreso=$_POST['tipo_iva'];
 $monto=$_POST['iva'];
 $monto=number_format($monto,2,".","");
 $nro_cuenta=$_POST['concepto_iva'];
 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas
     (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
     values ($id_detalle_eg_atadas,$id_pagos_atadas,1,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['IVA']=$id_detalle_eg_atadas;
}

 if ($_POST['chk_gan']==1) {
 // recupera datos de ganancias
 $proveedor=$_POST['prov_gan'];
 $tipo_egreso=$_POST['tipo_gan'];
 $monto=$_POST['ganancia'];
 $monto=number_format($monto,2,".","");
 $nro_cuenta=$_POST['concepto_gan'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
  values ($id_detalle_eg_atadas,$id_pagos_atadas,2,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['GANANCIAS']=$id_detalle_eg_atadas;
 }

 if ($_POST['chk_rib'] ==1) {
 // recupera datos de rib
 $proveedor=$_POST['prov_rib'];
 $tipo_egreso=$_POST['tipo_rib'];
 $monto=$_POST['rib'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_rib'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
    (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
 values ($id_detalle_eg_atadas,$id_pagos_atadas,3,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['RIB']=$id_detalle_eg_atadas;
}
 
 if ($_POST['chk_suss'] ==1) {
 // recupera datos de suss
 $proveedor=$_POST['prov_suss'];
 $tipo_egreso=$_POST['tipo_suss'];
 $monto=$_POST['suss'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_suss'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
 values ($id_detalle_eg_atadas,$id_pagos_atadas,10,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['SUSS']=$id_detalle_eg_atadas;
}


 if ($_POST['chk_mul'] ==1) {
 // recupera datos de multas
 $proveedor=$_POST['prov_multas'];
 $tipo_egreso=$_POST['tipo_multas'];
 $monto=$_POST['multas'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_multas'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,4,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['MULTAS']=$id_detalle_eg_atadas;
 }
 
 if ($_POST['chk_dep'] ==1) {
 // recupera datos de deposito
 $proveedor=$_POST['prov_dep'];
 $tipo_egreso=$_POST['tipo_dep'];
 $monto=$_POST['deposito'];
 $monto=number_format($monto,2,".",""); 
 $banco=$_POST['banco'];
 $tipo=$_POST['tipo_deposito'];	
 $nro_cuenta=$_POST['concepto_dep'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
    (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,idbanco,idtipodep,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,5,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$banco,$tipo,'$fecha_detalle')";
 $det['DEPOSITO']=$id_detalle_eg_atadas;
 }
 
 if ($_POST['chk_otro'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_otro'];
 $tipo_egreso=$_POST['tipo_otro'];
 $monto=$_POST['otro'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_otro'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,6,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['OTROS']=$id_detalle_eg_atadas;
 }


if ($_POST['chk_diferido'] ==1) {
	
 // recupera datos de otros
 $proveedor=$_POST['prov_diferido'];
 $tipo_egreso=$_POST['tipo_diferido'];
 $monto=$_POST['diferido'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_diferido'];
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas
    (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,9,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 
 //guardo los cheques diferidos y asocio con cobranza
 $i = 0;
 $cheques = stripcslashes($_POST['cheques_diferidos']);
 $cheques = wddx_deserialize($cheques);
 
while ($i<sizeof($cheques->cheques['monto']))
 {$nro_cheque = $cheques->cheques['nro'][$i];
  $banco = $cheques->cheques['banco'][$i];
  $fecha_vencimiento = fecha_db($cheques->cheques['fecha_vencimiento'][$i]);
  $comentario = $cheques->cheques['comentario'][$i];
  $ubicacion = $cheques->cheques['ubicacion'][$i];
  $monto = $cheques->cheques['monto'][$i];
  $pertenece = $cheques->cheques['pertenece'][$i];
  $id_entidad = $cheques->cheques['id_cliente'][$i];
  
  $sql="select nextval('bancos.cheques_diferidos_id_chequedif_seq') as max";
  $res=sql($sql,"$sql") or fin_pagina();
  $id_chequedif=$res->fields['max'];
  
  $sql="insert into cheques_diferidos (id_chequedif,nro_cheque,id_banco,fecha_vencimiento,comentario,monto,fecha_ingreso,
           id_empresa_cheque,activo,ubicacion,id_entidad) 
        values($id_chequedif,'$nro_cheque','$banco','$fecha_vencimiento','$comentario',$monto,'".date("Y-m-d")."',$pertenece,0,
           '$ubicacion',$id_entidad);";
  sql($sql,"$sql") or fin_pagina();
 
  $sql="insert into cheque_cobranza_atadas(id_chequedif,id_pagos_atadas) values($id_chequedif,$id_pagos_atadas);";
  sql($sql,"$sql") or fin_pagina();
  
  $i++;
 }
 $det['DIFERIDO']=$id_detalle_eg_atadas;
}

if ($_POST['chk_ficticio'] ==1) {
 // recupera datos de ficticio
 
 $monto=$_POST['ficticio'];
 $monto=number_format($monto,2,".",""); 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle) 
     values ($id_detalle_eg_atadas,$id_pagos_atadas,7,NULL,NULL,NULL,$monto,$dolar_actual,'$fecha_detalle')";
 $det['FICTICIO']=$id_detalle_eg_atadas;
}

if ($_POST['chk_cheque'] ==1) {
 // recupera datos de cheque
 $monto=$_POST['cheque'];
 $monto=number_format($monto,2,".",""); 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
  (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle) 
   values ($id_detalle_eg_atadas,$id_pagos_atadas,8,NULL,NULL,NULL,$monto,$dolar_actual,'$fecha_detalle')";
 $det['CHEQUE']=$id_detalle_eg_atadas;
}
 
if ($_POST['chk_devp'] ==1) {
 // recupera datos de devolucion Prestamo
 $proveedor=$_POST['prov_devolucion'];
 $tipo_egreso=$_POST['tipo_devolucion'];
 $monto=$_POST['devolucion'];
 $nro_cuenta=$_POST['concepto_devolucion'];
 $monto=number_format($monto,2,".",""); 
 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,11,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['DEVOLUCION']=$id_detalle_eg_atadas;
 }

 
if ($_POST['chk_int'] ==1) {
 // recupera datos de intereses
 $proveedor=$_POST['prov_interes'];
 $tipo_egreso=$_POST['tipo_interes'];
 $monto=$_POST['interes'];
 $nro_cuenta=$_POST['concepto_interes'];
 $monto=number_format($monto,2,".",""); 
 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,12,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['INTERESES']=$id_detalle_eg_atadas;
 }

 if ($_POST['chk_adm'] ==1) {
// recupera datos de Gastos administrativos
 $proveedor=$_POST['prov_gastoadm'];
 $tipo_egreso=$_POST['tipo_gastoadm'];
 $monto=$_POST['gastoadm'];
 $nro_cuenta=$_POST['concepto_gastoadm'];
 $monto=number_format($monto,2,".",""); 
 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,13,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['GASTOS']=$id_detalle_eg_atadas;
 }
  if ($_POST['chk_com'] ==1) {
// recupera datos de Gastos administrativos
 // recupera datos de Comisiones
 $proveedor=$_POST['prov_comisiones'];
 $tipo_egreso=$_POST['tipo_comisiones'];
 $monto=$_POST['comisiones'];
 $nro_cuenta=$_POST['concepto_comisiones'];
 $monto=number_format($monto,2,".",""); 
 
 $sql_max="select nextval('licitaciones_datos_adicionales.detalle_egresos_atadas_id_detalle_eg_atadas_seq') as id";
 $res=sql($sql_max,"$sql_max") or fin_pagina();
 $id_detalle_eg_atadas=$res->fields['id'];
 $sql_detalle[]="insert into detalle_egresos_atadas 
   (id_detalle_eg_atadas,id_pagos_atadas,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,fecha_detalle)
   values ($id_detalle_eg_atadas,$id_pagos_atadas,14,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,'$fecha_detalle')";
 $det['COMISIONES']=$id_detalle_eg_atadas;
 }

 sql($sql_detalle,"Error en guardar detalles atadas: $sql_detalle") or fin_pagina();
  
 if ($db->CompleteTrans()) 
     return $det;
 else return 0;

} //fin_guarda_detalle_egresos_atadas



/*************************************************************************
 *@ armar_valores_detalle
 *@ retorna un arreglo con los valores guardados en detalle_egresos_atadas
 *@ id_pagos_atadas 
**************************************************************************/
function armar_valores_detalle_atadas ($id_pagos_atadas) {
$sql="select * from detalle_egresos_atadas 
      join licitaciones.egreso_cob using (id_cob_egreso)
      where id_pagos_atadas=$id_pagos_atadas ";
$res=sql($sql,"Error en armar valores detalle $sql") or fin_pagina();  

$id_transferencia=retorna_id('egreso_cob','id_cob_egreso','descripcion','Transferencia','ilike');
$valores_detalles=array();
  while (!$res->EOF) {
  	  $desc=$res->fields['descripcion'];
      $valores_detalles[$desc]['monto_detalle']=$res->fields['monto_detalle'];
      $valores_detalles[$desc]['id_tipo_egreso']=$res->fields['id_tipo_egreso'];
      $valores_detalles[$desc]['id_proveedor']=$res->fields['id_proveedor'];
      $valores_detalles[$desc]['numero_cuenta']=$res->fields['numero_cuenta'];
      if ($res->fields['id_cob_egreso']==$id_transferencia) {
         $valores_detalles[$desc]['idbanco']=$res->fields['idbanco'];
         $valores_detalles[$desc]['idtipodep']=$res->fields['idtipodep'];
      }
  $res->MoveNext();
}
return  $valores_detalles;
}


/*MAIL EGRESOS para venta atada*/
function mail_egresos_parciales_atadas($id_vta_atada,$nombre_distrito,$valores_defecto,$nro_fact,$para) {

$asunto="Egresos parciales para venta de facturas atadas desde cobranzas ";

$id_transferencia=retorna_id('egreso_cob','id_cob_egreso','descripcion','Transferencia','ilike');
$id_efectivo=retorna_id('egreso_cob','id_cob_egreso','descripcion','Efectivo','ilike');
$sql_ch= "select id_cob_egreso from egreso_cob where descripcion='Cheque'";
$res=sql($sql_ch,$sql_ch) or fin_pagina();
$id_cheque=$res->fields['id_cob_egreso'];

$contenido .="Se han realizado egresos parciales para venta de facturas atadas desde seguimiento de cobros \n";
$contenido .="EN LA CAJA DE ". str_replace("- GCBA"," ",$nombre_distrito);
$contenido.=" \npara las facturas:";
$cant=count($nro_fact);
for($i=0;$i<$cant;$i++) {
   $contenido.=$nro_fact[$i];
   if ($i<$cant-1) $contenido.=", "; 
}

  
$id_anterior="";
$i=1;
$sql="select id_pagos_atadas,descripcion,ingreso_egreso.monto,monto_detalle,dolar_egreso,caja.id_moneda,id_distrito,iddepósito,
       simbolo,caja.fecha,id_ingreso_egreso,
       detalle_egresos_atadas.iddepósito,detalle_egresos_atadas.id_proveedor,detalle_egresos_atadas.id_tipo_egreso,
       detalle_egresos_atadas.numero_cuenta,detalle_egresos_atadas.idtipodep,detalle_egresos_atadas.idbanco,id_cob_egreso
	   from  licitaciones_datos_adicionales.detalle_egresos_atadas
	   join licitaciones_datos_adicionales.pagos_atadas using(id_pagos_atadas)
	   join licitaciones.egreso_cob using (id_cob_egreso)
	   join licitaciones_datos_adicionales.egresos_atadas using (id_detalle_eg_atadas)
	   left join caja.ingreso_egreso using (id_ingreso_egreso)
	   left join caja.caja using(id_caja)
	   left join licitaciones.moneda using (id_moneda)
	   where id_vta_atada=$id_vta_atada 
	   order by id_pagos_atadas";

$contenido.=" \n DETALLES DE EGRESOS \n";
$res=sql($sql,"Error al recuperar datos egresos $sql ") or fin_pagina();
while (!$res->EOF) {
 $id_pagos_atadas=$res->fields['id_pagos_atadas'];	
 if ($id_pagos_atadas != $id_anterior) {
      $contenido.="Pago $i";
      $i++;
      $id_anterior=$id_pagos_atadas;
 }
 $desc=$res->fields['descripcion'];
 $id_cob_egreso=$res->fields['id_cob_egreso'];
 
 $total=0;
 if($id_cob_egreso==$id_efectivo || $id_cob_egreso==$id_cheque  ) {
    $contenido.= "\n Descripción ".strtoupper($res->fields['descripcion'])." Monto ".$res->fields['simbolo']." ".formato_money($res->fields['monto_detalle']); 
   if ($res->fields['id_moneda']==2)	$contenido.= " Valor Dolar: ".formato_money($res->fields['dolar_egreso'])."\n";
   if ($res->fields['id_moneda']==1) $total+=$res->fields['monto_detalle'];
           else $total+=$res->fields['monto_detalle']*$res->fields['dolar_egreso'];
   
 }
 else {
 $contenido.= "\nDescripción: ".strtoupper($desc).",  Monto " .$res->fields['simbolo']." ".formato_money($res->fields['monto']); 
 if ($res->fields['id_moneda']==2)	$contenido.= " Valor Dolar: ".formato_money($res->fields['dolar_egreso']);
 
 if ($res->fields['id_moneda']==1) $total+=$res->fields['monto'];
           else $total+=$res->fields['monto']*$res->fields['dolar_egreso'];
 
 $contenido.="\n- ID EGRESO ".$res->fields['id_ingreso_egreso'];
 $contenido.="\n";
 
 $id_prov=$res->fields['id_proveedor'];
 $tipo_eg=$res->fields['id_tipo_egreso'];
 $cuenta=$res->fields['numero_cuenta'];
 
 if ($valores_defecto[$desc]['tipo'] != $tipo_eg ) {
   $eg_sel=retorna_id('tipo_egreso','nombre','id_tipo_egreso',$tipo_eg,'=');	
   $contenido.=" \n Se ha modificado el tipo de egreso: \n";
   $contenido.=" Tipo Egreso por defecto: ". $valores_defecto[$desc]['nbre'] .", Tipo Egreso Modificado a: ".$eg_sel."\n";
 }
 
 if ($valores_defecto[$desc]['prov'] != $id_prov ) {
   $contenido.="\n Se ha modificado el proveedor: \n";
   $prov_sel=retorna_id('proveedor','razon_social','id_proveedor',$id_prov,'=');
   $contenido.=" Proveedor por defecto: ".$valores_defecto[$desc]['razon_social'].",  Proveedor Modificado a: ". $prov_sel."\n";
 }
 
 if ($valores_defecto[$desc]['cta'] != $cuenta ) {
      $sql_cto="select concepto,plan from tipo_cuenta where numero_cuenta=$cuenta";
      $res_cto=sql($sql_cto) or fin_pagina();
      $concepto_sel=$res_cto->fields['concepto'];
      $plan_sel=$res_cto->fields['plan'];	
   $contenido.="\n Se ha modificado Cuenta: \n";
   $contenido.=" Cuenta por defecto: ". $valores_defecto[$desc]['concepto'].",  Cuenta Modificado a: ". $concepto_sel."[". $plan_sel." ] .\n";
 }
 
 if ($id_cob_egreso==$id_transferencia) {  // es transferencia
  
  $tipo_dep=$res->fields['idtipodep'];
  $banco=$res->fields['idbanco'];
   
  if ($valores_defecto[$desc]['deposito'] != $tipo_dep  ) {
   $texto=$id_deposito=retorna_id('tipo_depósito','tipodepósito','idtipodep',$tipo_dep,'=');   	
   $contenido.="Se ha modificado el tipo de depósito para la transferencia: \n";
   $contenido.=" Tipo de depósito por defecto ".$valores_defecto[$desc]['nbredeposito'] ."  tipo de depósito Modificado a: ". $texto."\n";
   }
  if ($valores_defecto[$desc]['banco'] != $banco  ) {
   $texto=retorna_id('tipo_banco','nombrebanco','idbanco',$banco,'=');
   $contenido.="Se ha modificado el Banco para la transferencia: \n";
   $contenido.=" Banco por defecto ".$valores_defecto[$desc]['nbrebanco'] ."  Banco Modificado a: ". $texto."\n";
  }
 
 } //fin if transferencia
 
 
 }
 
 
$res->MoveNext();
}
if ($total > 5000) {
        $para.=',corapi@coradir.com.ar';
}

enviar_mail($para,$asunto,$contenido,'','','',0);   

}

//muestra los ingresos de venta de factura atadas
function mostrar_ingresos_atadas ($id_cob) {

$id_volver=$id_cob;

$sql_ing="select ingreso_egreso.id_ingreso_egreso,ingreso_egreso.monto,valor_dolar as dolar_ingreso,fecha_creacion,simbolo,
		  tipo_ingreso.nombre as tipo_ingreso,venta_fac_atadas.id_distrito, tipo_cuenta_ingreso.nombre as tipo_cuenta,entidad.nombre as entidad 
		  from licitaciones_datos_adicionales.venta_fac_atadas 
		  join licitaciones_datos_adicionales.pagos_atadas using (id_vta_atada) 
		  join licitaciones_datos_adicionales.detalle_pagos_atadas using(id_pagos_atadas) 
		  join caja.ingreso_egreso using (id_ingreso_egreso)
		  join licitaciones.moneda using (id_moneda)
		  left join caja.tipo_ingreso on ingreso_egreso.id_tipo_ingreso=tipo_ingreso.id_tipo_ingreso 
		  left join caja.tipo_cuenta_ingreso on ingreso_egreso.id_cuenta_ingreso=tipo_cuenta_ingreso.id_cuenta_ingreso 
		  left join licitaciones.entidad using (id_entidad)
		  where venta_fac_atadas.id_cobranza=$id_cob";
$res_ing=sql($sql_ing,"$sql_ing") or fin_pagina();

	
$visib = "none";

$archivo="../caja/ingresos_egresos.php";
$distrito=$res_ing->fields['id_distrito'];

if($res_ing->RecordCount()>0) {
echo "<br>";
	    echo "<table align=center width=95%><tr id=mo><td colspan=7><font size=+1>Detalle Ingresos</font></td></tr>";
        echo "<tr  bgcolor=$bgcolor2 >";
        echo "<td align=right><b>Mostrar Detalles:</b>
         <input type=checkbox class='estilos_check' name=det_ing  onclick='javascript:(this.checked)?Mostrar(\"tabla_det_ing\"):Ocultar(\"tabla_det_ing\");'></td></tr></table>";
        echo " <div id='tabla_det_ing' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr id=mo>";
	    echo "<td>ID</td>";
	    echo "<td>Monto</td>";
	    if (formato_money($res_ing->fields['dolar_ingreso'])!='0,00')
	        echo "<td>Dolar</td>";
	    echo "<td>Tipo Ingreso</td>";
	    echo "<td>Cliente</td>";
	    echo "<td>Tipo Cuenta</td>";
	    echo "<td>Fecha Ingreso</td>";
	    echo "</tr>";

while (!$res_ing ->EOF) {
$simbolo=$res_ing->fields['simbolo'];
     	echo "<tr id=ma>";
     	$id_ingreso_egreso=$res_ing->fields['id_ingreso_egreso'];
	    if ($estado=="") //una factura
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"ingreso","pagina_viene"=>'lic_cobranzas',"id_cobranza"=>$id_volver,"distrito"=>$distrito));
     	  else  //facturas atadas
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"ingreso","pagina_viene"=>'lic_cobranzas',"id_cob"=>$id_volver,"distrito"=>$distrito));

     	$onclick_caja="window.open(\"$link_caja\",\"\",\"\")";
     	echo "<td title='ingreso'><a style='cursor: hand;' onclick='$onclick_caja'>$id_ingreso_egreso </a>";
     	echo "</td>";
     	echo "<td>".$res_ing->fields['simbolo']." ". formato_money($res_ing->fields['monto'])."</td>";
        if (formato_money($res_ing->fields['dolar_ingreso'])!='0,00')
     	echo "<td>".$res_ing->fields['dolar_ingreso']."</td>";
     	echo "<td>".$res_ing->fields['tipo_ingreso']."</td>";
	    echo "<td>".$res_ing->fields['entidad']."</td>";
	    echo "<td>".$res_ing->fields['tipo_cuenta']." </td>";
	    echo "<td>".fecha($res_ing->fields['fecha_creacion'])." </td>";
	    echo "</tr>";
	    $total+=$res_ing->fields['monto'];
	    $res_ing->MoveNext();

}
echo "</table>";
echo "<div align='center'><b>Total Ingresos ". $simbolo." ".formato_money($total)."</b></div>";
echo "</div>";
}
}

function mostrar_ficticio_atadas($id_cob) {
global $total;
$sql="select simbolo,monto_detalle,dolar_egreso,fecha_detalle,descripcion
	  from licitaciones_datos_adicionales.venta_fac_atadas 
	  join licitaciones_datos_adicionales.pagos_atadas using (id_vta_atada) 
	  join licitaciones_datos_adicionales.detalle_egresos_atadas using(id_pagos_atadas) 
      join licitaciones_datos_adicionales.egresos_atadas using(id_detalle_eg_atadas) 
	  join licitaciones.moneda using (id_moneda)
      join licitaciones.egreso_cob using (id_cob_egreso)
	  where venta_fac_atadas.id_cobranza=$id_cob and (id_cob_egreso=7 or id_cob_egreso=8)";
$res=sql($sql) or fin_pagina();

while (!$res->EOF) {
echo "<tr id=ma>";
echo "<td align='right'><font color='black'>".$res->fields['descripcion'].":</font></td><td>&nbsp;</td><td>".$res->fields['simbolo']."".formato_money($res->fields['monto_detalle'])."</td>";
if ($res->fields['dolar_egreso']) echo "<td>".$res->fields['dolar_egreso']."</td>";
echo "<td colspan=5>&nbsp;<font size=1 color='black'>(este monto no egresó de caja)</font></td>";
echo "</tr>";
 $total+=$res->fields['monto_detalle'];
 $res->MoveNext();
}
}


function mostrar_egresos_atadas($id_cob) {

global $total;
$id_volver=$id_cob;

$sql_egreso="select detalle_egresos_atadas.id_cob_egreso,descripcion,ingreso_egreso.monto,dolar_egreso,razon_social,id_moneda,
              venta_fac_atadas.id_distrito,iddepósito,tipo_egreso.nombre,concepto,plan,simbolo,fecha_detalle as fecha,id_ingreso_egreso
		      from licitaciones_datos_adicionales.venta_fac_atadas 
		      join licitaciones_datos_adicionales.pagos_atadas using (id_vta_atada) 
		      join licitaciones_datos_adicionales.detalle_egresos_atadas using(id_pagos_atadas) 
              join licitaciones_datos_adicionales.egresos_atadas using(id_detalle_eg_atadas) 
		      join caja.ingreso_egreso using (id_ingreso_egreso)
		      join licitaciones.moneda using (id_moneda)
              join licitaciones.egreso_cob using (id_cob_egreso)
		      join caja.tipo_egreso on ingreso_egreso.id_tipo_egreso=tipo_egreso.id_tipo_egreso
		      join general.tipo_cuenta on ingreso_egreso.numero_cuenta=tipo_cuenta.numero_cuenta
              left join bancos.depósitos using (iddepósito)
		      left join general.proveedor on proveedor.id_proveedor=ingreso_egreso.id_proveedor
		      where venta_fac_atadas.id_cobranza=$id_cob
		      and id_cob_egreso!=7 and id_cob_egreso!=8 order by ingreso_egreso.id_ingreso_egreso";

$res_eg=sql($sql_egreso,"$sql_egreso") or fin_pagina();
$visib = "none";

$archivo="../caja/ingresos_egresos.php";
$distrito=$res_eg->fields['id_distrito'];

if($res_eg->RecordCount() > 0) {
	    echo "<br>";
	    echo "<table align=center width=95%><tr id=mo><td colspan=7><font size=+1>Detalle Egresos</font></td></tr>";
        echo "<tr  bgcolor=$bgcolor2 >";
        echo "<td align=right><b>Mostrar Detalles:</b>
         <input type=checkbox name=det_eg class='estilos_check' onclick='javascript:(this.checked)?Mostrar(\"tabla_det_eg\"):Ocultar(\"tabla_det_eg\");'></td></tr></table>";
        echo " <div id='tabla_det_eg' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr id=mo><td>Descripción</td>";
	    echo "<td>ID</td>";
	    echo "<td>Monto</td>";
	    if (formato_money($res_eg->fields['dolar_egreso'])!='0,00')
	       echo "<td>Dolar</td>";
	    echo "<td>Tipo Egreso</td>";
	    echo "<td>Proveedor</td>";
	    echo "<td>Concepto y Plan</td>";
	    echo "<td>Fecha</td>";
	    echo "<tr>";
$i=0;

 while (!$res_eg->EOF) {
$simbolo=$res_eg->fields['simbolo'];
     	echo "<tr id=ma>";
     	echo "<td align=right><font color=black>".strtoupper($res_eg->fields['descripcion']).":</font></td>";
	    $id_ingreso_egreso=$res_eg->fields['id_ingreso_egreso'];
	    $id_deposito=$res_eg->fields['iddepósito'];
	    if ($estado=="") //una factura
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"egreso","pagina_viene"=>'lic_cobranzas',"id_cobranza"=>$id_volver,"distrito"=>$distrito));
     	  else  //facturas atadas
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"egreso","pagina_viene"=>'lic_cobranzas',"id_cob"=>$id_volver,"distrito"=>$distrito));

     	$onclick_caja="window.open(\"$link_caja\",\"\",\"\")";
     	  $link_dep=encode_link('../bancos/bancos_movi_deppen.php',array("Modificar_Deposito_Numero"=> $id_deposito,"Modificar"=>1));
     	  $onclick_dep="window.open(\"$link_dep\",\"\",\"\")";
     	if ($id_deposito!=null || $id_deposito !="") {
     	echo "<td><table><tr><td title='egreso'><a style='cursor: hand;' onclick='$onclick_caja'>$id_ingreso_egreso </a></td>
     	       <td title='depósito'><a style='cursor: hand;' onclick='$onclick_dep'>/$id_deposito </a></td>
     	       </tr></table>";
     	} else
             echo "<td title='egreso'><a style='cursor: hand;' onclick='$onclick_caja'>$id_ingreso_egreso </a>";
     	echo "</td>";
     	echo "<td>".$res_eg->fields['simbolo']." ". formato_money($res_eg->fields['monto'])."</td>";
	    if (formato_money($res_eg->fields['dolar_egreso'])!='0,00')
     	echo "<td>".$res_eg->fields['dolar_egreso']."</td>";
     	echo "<td>".$res_eg->fields['nombre']."</td>";
	    echo "<td>".$res_eg->fields['razon_social']."</td>";
	    echo "<td>".$res_eg->fields['concepto']."[". $res_eg->fields['plan']."] </td>";
	    echo "<td>".fecha($res_eg->fields['fecha'])."</td>";
	    echo"</tr>";
       $total+=$res_eg->fields['monto'];
	   $res_eg->MoveNext();
}
}
       mostrar_ficticio_atadas($id_cob);


$sql = "select nro_cheque,fecha_ingreso,fecha_vencimiento,cheques_diferidos.monto,comentario
	            from bancos.cheques_diferidos 
                join licitaciones_datos_adicionales.cheque_cobranza_atadas using(id_chequedif) 
                join licitaciones_datos_adicionales.pagos_atadas using (id_pagos_atadas)
                join licitaciones_datos_adicionales.venta_fac_atadas using(id_vta_atada)
                where id_cobranza=$id_cob";
	
$resultado_diferido = sql($sql,"$sql") or fin_pagina();
if ($resultado_diferido->RecordCount() > 0) {
    echo "<table align=center width=95%>";
    echo "<tr><td colspan=5>CHEQUES DIFERIDOS</td></tr>";
    echo "<tr id=mo>";
	echo "<td>Numero</td>";
	echo "<td>Emision</td>";
	echo "<td>Vencimiento</td>";
	echo "<td>Monto</td>";
	echo "<td>Comentario</td>";
	echo "</tr>";
	
while(!$resultado_diferido->EOF)   {
	echo "<tr id=ma>";
	echo "<td>".$resultado_diferido->fields['nro_cheque']."</td>";
	echo "<td>".Fecha($resultado_diferido->fields['fecha_ingreso'])."</td>";
	echo "<td>".Fecha($resultado_diferido->fields['fecha_vencimiento'])."</td>";
	echo "<td>".$resultado_diferido->fields['monto']."</td>";
	echo "<td>".$resultado_diferido->fields['comentario']."</td>";
	echo "</tr>";
$resultado_diferido->MoveNext();
   }
echo "</table>";
}

echo "</table>";
echo "<div align='center'><b>Total Egresos ". $simbolo." ".formato_money($total)."</b></div>";
echo"</div>";


}//fin de mostrar egresos_atadas



?>