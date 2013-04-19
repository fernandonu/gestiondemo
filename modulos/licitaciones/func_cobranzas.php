<?

/*
$Author: mari $
$Revision: 1.28 $
$Date: 2006/11/10 18:53:00 $
*/

//funcion para mandar el arreglo por post
function array_envia($array) {
    $tmp = serialize($array);
    $tmp = urlencode($tmp);
    return $tmp;
}

//funcion para recibir un arreglo del post
function array_recibe($array) {
    $tmp = urldecode($array);
    $tmp = unserialize($tmp);
   return $tmp;
}


/*****************************************************************************
 *retorna_id
 *arma una consulta segun los parametros y retorna id correspondiente 
 * @ tabla: tabla en la que se realiza la consulta
 * @ campo_sel: campo a seleccionar (id) 
 * @ campo_comp: nombre del campo por el cual se compara (es un arrrego si la compracion se realzia por dos campos)
 * @ valor: valor para comparar (es un arrrego si la compracion se realzia por dos campos)
 * @ $op: operador para realizar la consulta (ilike o =)
 ****************************************************************************/
function retorna_id ($tabla,$campo_sel,$campo_comp,$datos_ingor,$op) {
 
if (is_array($campo_comp)) {
      if (!strcmp($op,'ilike')) {
      	if (count($campo_comp) > 2) $and= " and $campo_comp[2] $op '$datos_ingor[2]'";
        $sql="select $campo_sel from $tabla where $campo_comp[0] $op '$datos_ingor[0]' and $campo_comp[1] $op '$datos_ingor[1]'".$and;
      }
       else {
        	if (count($campo_comp) > 2) $and= " and $campo_comp[2] $op $datos_ingor[2]";
       	$sql="select $campo_sel from $tabla where $campo_comp[0] $op $datos_ingor[0] and $campo_comp[1] $op $datos_ingor[1]".$and;
       }
      
} else {
	  if (!strcmp($op,'ilike')) 
        $sql="select $campo_sel from $tabla where $campo_comp $op '$datos_ingor%'";
	  else 
        $sql="select $campo_sel from $tabla where $campo_comp $op $datos_ingor";
}
 $res=sql($sql,"$sql") or fin_pagina();
 
return ($res->fields[$campo_sel]);
}

/*****************************************************************************
 *gen_select
 *genera un select de nombre $nombre 
 *@ resultados es el resultado del query que selecciona los datos del selector
 *@ id es el id a seleccionar
 *@ campo campo que se recupera de la consulta para el valor de cada option del select
 *@ campo1 campo que se recupera de la consulta para el valor del texto 
****************************************************************************/
function gen_select ($nombre,$resultados,$id,$campo,$campo1,$des="") {
 
 $resultados->MoveFirst();
 $cantidad=$resultados->RecordCount();
 
 echo "<select name='$nombre' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' $des>";
  for($i=0;$i<$cantidad;$i++) {
	$datos_ingor=$resultados->fields["$campo"];
	$string=$resultados->fields["$campo1"];
	  ?>
	  <option value='<?=$datos_ingor?>' <? if ($id==$datos_ingor) echo 'selected'?> > <?= $string?> </option>
	 <? 
	$resultados->MoveNext();
 }
  echo "</select>";
}//fin de gen_select_tipo


/*****************************************************************************
 *gen_select_concepto
 *genera un select de nombre $nombre con los concepto
 $resultados es el resultado del query que selecciona concepto y plan 
****************************************************************************/
function gen_select_concepto ($nombre,$resultados,$id) {
 $resultados->MoveFirst();
 $cantidad=$resultados->RecordCount();
 
 echo "<select name='$nombre' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()'>";
  for($i=0;$i<$cantidad;$i++) {
	$datos_ingor=$resultados->fields['numero_cuenta'];
	$string=$resultados->fields['concepto']."&nbsp;[ ".$resultados->fields['plan']." ]";
	?>
	<option value='<?=$datos_ingor?>' <?if ($id==$datos_ingor) echo 'selected'?> > <?=$string ?> </option>
	<? 
	$resultados->MoveNext();
 }
  echo "</select>";
}

function tabla_filtros_nombres($flag=0,$des="") {
	$abc=array("a","b","c","d","e","f","g","h","i","j","k","l","m",
			"n","ñ","o","p","q","r","s","t","u","v","w","x","y","z");
$cantidad=count($abc);
if (!isset($_POST['det_filtro']) || $_POST['det_filtro']!=1)
  $visib="none";
  
echo "<table align='right'><tr>"; 
echo" <td colspan=3 align=right><b>Filtro Cliente:</b>
      <input type=checkbox $des class='estilos_check' name=det_filtro value=1 onclick='javascript:(this.checked)?Mostrar(\"tabla_filtro\"):Ocultar(\"tabla_filtro\");'"; if ($_POST['det_filtro']==1) echo 'checked'; echo ">";
echo " </td></tr></table>";
echo "<div id=tabla_filtro style='display:$visib'>";
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
      <td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';<?if ($flag==1){?>document.all.ver_detalles.value=1;<?}?>document.form1.submit();"><?=$letra?></td>
    <?
  }//del for
   echo "</tr>";
   echo "<tr>";?>
    <td colspan='14' style='cursor:hand' onclick='<?if ($flag==1){?>document.all.ver_detalles.value=1;<?}?>document.form1.submit();'><font color='#FDF2F3'><b> Todos</b></font>
   <?echo "</td>";
   echo "</tr>";
   echo "</table>";
   echo "</div>";
}  //de la funcion  para las letras en la lista de entidades y proveedores



/*****************************************************************
  *@  compara datos de la factura y todos los ingresos,si hay diferencia 
  *@  en los datos manda un mail avisando cual es la diferencia
  *@  si no hay diferencia el mail se nanda avisndo que se realizo el ingreso
  *@  val_Anterior -> datos de la factura
  *@  val-> datos del ingreso 
  
*********************************************************/

function mail_ingresos($datos_ing,$datos_fact,$id_datos_ingreso) {

$para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";	

$asunto="Ingreso parcial desde seguimiento de cobros ";
$sql="select nombre from distrito where id_distrito=".$datos_ing['distrito'];
$res_d=sql($sql,"en mail ingreos $sql") or fin_pagina();
$dist=$res_d->fields['nombre'];

$dist = str_replace("- GCBA"," ",$dist);

//tipo de cuenta y tipo de egreso por defecto
$id_tipo=retorna_id('tipo_ingreso','id_tipo_ingreso','nombre','Cobros','ilike');
$id_cuenta=retorna_id('tipo_cuenta_ingreso','id_cuenta_ingreso','nombre','Cobros (Facturas Clientes)','ilike');

$contenido ="Se realizarón ingresos parciales en la caja de ". $dist." \n Para la factura ". $datos_ing['tipo_factura']." NRO:  ". $datos_ing['nro_factura']; 

if (es_numero($id_licitacion))
  $contenido.=" Asociada a la Licitacion: ". $datos_ing['id_licitacion'] ." \n ";
$contenido.= " ENTIDAD DEL INGRESO:". $datos_ing['nombre_cliente'] ."\n";

$sql="select  detalle_ingresos.id_ingreso_egreso,simbolo,monto,dolar_ingreso,
      ingreso_egreso.usuario,fecha_creacion
	  from licitaciones.pagos_ingreso 
      join licitaciones.detalle_ingresos using (id_detalle_ingreso)
      join caja.ingreso_egreso using (id_ingreso_egreso)
	  join caja.caja using (id_caja)
	  join licitaciones.moneda using (id_moneda)
	  where id_datos_ingreso=$id_datos_ingreso order by detalle_ingresos.id_ingreso_egreso";
$res=sql($sql) or fin_pagina();
$simbolo_ingreso=$res->fields['simbolo'];
$monto_ingreso=0;
$total_ingreso=0;
if ($datos_fact['moneda_factura']==2) {
   if ($datos_ing['id_moneda']==1)  $mult=1; //de dol a pesos => mult 
   else $mult=0; //de pesos a dol => divide
 }
while (!$res->EOF) { 
	$fecha=$res->fields['fecha_creacion'];
   	$dia=fecha($fecha);
	$usuario=$res->fields['usuario'];
	$contenido.= "\n INGRESO ID: ".$res->fields['id_ingreso_egreso'];
	$contenido.= "  MONTO ". $res->fields['simbolo']." ".formato_money($res->fields['monto']);
	if ($datos_ing['id_moneda']==2) $contenido.=" VALOR DOLAR : ".formato_money($res->fields['dolar_ingreso']);
	$contenido.= "  Ingreso Realizado por el usuario ". $usuario  ." el día $dia. \n";
	$contenido.= "\n";
	$total_ingreso+=$res->fields['monto'];
	if ($mult==1) 
	   $parcial=$res->fields['monto'] * $res->fields['dolar_ingreso'];
	elseif($mult==0) 
	   $parcial=$res->fields['monto'] * $res->fields['dolar_ingreso'];
	 
    $monto_ingreso+=$parcial;
	$res->MoveNext();
}

if ($datos_fact['moneda_factura']==1) {
	if ($datos_fact['monto_factura'] > 5000) {
        $para.=',corapi@coradir.com.ar';
    }
} elseif (($datos_fact['monto_factura'] * $datos_fact['dolar_factura']) > 5000) {
        $para.=',corapi@coradir.com.ar'; 
}
$contenido.="\n MONTO DE LA FACTURA =>". $datos_fact['simbolo_factura']." ".formato_money($datos_fact['monto_factura']);
if ($datos_fact['moneda_factura']==2) 
     $contenido.= "    VALOR DOLAR DE LA FACTURA ".formato_money($datos_fact['dolar_factura'])." \n";

$contenido.="\n MONTO DEL INGRESO =>". $simbolo_ingreso." ".formato_money($total_ingreso);

//INDICA SI HAY DIFERENCIA ENTRE EL MONTO DE LA FACTURA Y EL MONTO DEL INGRESO

if  ($datos_fact['moneda_factura']==2) { //factura en dolares
 
  //factura en dolares
  switch ($datos_ing['id_moneda']) {
     case 1: { //ingreso en pesos
            $monto_fact= $datos_fact['dolar_factura'] * $datos_fact['monto_factura'];
            $diff=$monto_ingreso - $monto_fact;
            break;
            }
      case 2: { //ingreso en dolares
             $monto_fact=$datos_fact['monto_factura'] * $datos_fact['dolar_factura']; 
             $diff= $monto_ingreso - $monto_fact;
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

if ($id_tipo != $datos_ing['tipo_ing']) {
  $contenido.="\n Se ha modificado el tipo de ingreso:  \n";
  $sql="select nombre from tipo_ingreso where id_tipo_ingreso=".$datos_ing['tipo_ing']; 
  $res=sql($sql) or fin_pagina ();
  $ing=$res->fields['nombre'];
  $contenido.="     TIPO DE INGRESO: Cobros   TIPO DE INGRESO MODIFICADO: $ing \n";
} 

if ($id_cuenta != $datos_ing['cuentaing']) {
	  $contenido.=" \nSe ha modificado el Tipo de Cuenta:  \n";
	  $sql="select nombre from caja.tipo_cuenta_ingreso where id_cuenta_ingreso=".$datos_ing['cuentaing']; 
      $res=sql($sql) or fin_pagina ();
      $cta=$res->fields['nombre'];
  	  $contenido.="TIPO CUENTA: Cobros (Facturas Clientes) TIPO CUENTA MODIFICADO: $cta \n";
 }
 
if ($datos_fact['id_cliente'] != $datos_ing['cliente'] ) {
  $contenido.= "\nSe ha modificado la entidad:  \n";
  if ($datos_fact['id_cliente'] != null || $datos_fact['id_cliente'] != "") {
   $sql="select nombre from entidad where id_entidad=".$datos_fact['id_cliente']; 
   $res=sql($sql) or fin_pagina ();
   $ent_ant=$res->fields['nombre'];
  }
  $contenido.= "ENTIDAD : ".$ent_ant."  ENTIDAD MODIFICADA: ".$datos_ing['nombre_cliente']." \n";
 }
 
 enviar_mail($para,$asunto,$contenido,'','','',0);   
  
}

/*****************************************************
  *@ armar_valores_defecto
*********************************************************/
function armar_valores_defecto() {
$valores_defecto=array();
//el valor asociativo de valores_defecto['descripcion'] descripcion es tal cual esta en la base de datos tabla egreso_cob
$sql="select id_tipo_egreso,nombre from caja.tipo_egreso 
	  where nombre ilike 'IVA' or nombre ilike 'Impuestos'
	  or nombre ilike 'Licitaciones' or nombre ilike 'Licitaciones'
	  or nombre ilike 'Depósito Ciudad' or nombre ilike 'otros'
      or nombre ilike 'Cheque Diferido'
      or nombre ilike 'Devolución de dinero'";
$res=sql($sql) or fin_pagina();


while (!$res->EOF) {
  switch ($res->fields['nombre']) {
    case 'IVA':{
    	       $valores_defecto['iva']['tipo']=$res->fields['id_tipo_egreso'];
    	       $valores_defecto['iva']['nbre']=$res->fields['nombre'];
        }  
      break;
    case 'Impuestos':{
                   $valores_defecto['ganancias']['tipo']=$res->fields['id_tipo_egreso'];
                   $valores_defecto['ganancias']['nbre']=$res->fields['nombre'];
                   $valores_defecto['rib']['tipo']=$res->fields['id_tipo_egreso'];
                   $valores_defecto['rib']['nbre']=$res->fields['nombre'];
                   $valores_defecto['suss']['tipo']=$res->fields['id_tipo_egreso'];
                   $valores_defecto['suss']['nbre']=$res->fields['nombre'];
              }
     break;                     
    case 'Licitaciones':{
    	 $valores_defecto['Multas']['tipo']=$res->fields['id_tipo_egreso'];
    	 $valores_defecto['Multas']['nbre']=$res->fields['nombre'];
    }
      break;
    case 'Depósito Ciudad':{
    	$valores_defecto['Transferencia']['tipo']=$res->fields['id_tipo_egreso'];
    	$valores_defecto['Transferencia']['nbre']=$res->fields['nombre'];
    }
      break; 
    case 'Otros': {
    	   $valores_defecto['Otros']['tipo']=$res->fields['id_tipo_egreso'];
    	   $valores_defecto['Otros']['nbre']=$res->fields['nombre']; 
    	   $valores_defecto['Intereses']['tipo']=$res->fields['id_tipo_egreso'];
    	   $valores_defecto['Intereses']['nbre']=$res->fields['nombre']; 
    	   $valores_defecto['Gastos Administrativos']['tipo']=$res->fields['id_tipo_egreso'];
    	   $valores_defecto['Gastos Administrativos']['nbre']=$res->fields['nombre']; 
    	   $valores_defecto['Comisiones']['tipo']=$res->fields['id_tipo_egreso'];
    	   $valores_defecto['Comisiones']['nbre']=$res->fields['nombre'];
    }
      break; 
    case 'Cheque Diferido':  {
    	   $valores_defecto['Cheque Diferido']['tipo']=$res->fields['id_tipo_egreso'];
    	   $valores_defecto['Cheque Diferido']['nbre']=$res->fields['nombre'];
    }
      break; 
    case 'Devolución de dinero':  {
    	   $valores_defecto['Devolucion Prestamo']['tipo']=$res->fields['id_tipo_egreso'];
    	   $valores_defecto['Devolucion Prestamo']['nbre']=$res->fields['nombre'];
    }
      break; 
  } 
  $res->Movenext();
}  //fin while 
$prov_diferido=retorna_id('proveedor','id_proveedor','razon_social',"'1. CORADIR'",'=');
$sql="select id_proveedor,razon_social
from general.proveedor 
where razon_social ilike 'IVA' or razon_social ilike 'Ganancias'
or razon_social ilike 'Ingresos Brutos' or razon_social ilike 'Licitaciones'
or razon_social ilike 'Banco Ciudad de Buenos Aires' 
or razon_social ilike 'Gastos de Licitaciones'
or razon_social ilike '1. CORADIR'
or razon_social ilike 'Cargas Sociales'
or razon_social ilike 'Banco Supervielle'";
$res=sql($sql) or fin_pagina();

while (!$res->EOF) {
  switch ($res->fields['razon_social']) {
    case 'IVA': {
    	$valores_defecto['iva']['prov']=$res->fields['id_proveedor'];
    	$valores_defecto['iva']['razon_social']=$res->fields['razon_social'];
    }
      break;
    case 'GANANCIAS': {
    	   $valores_defecto['ganancias']['prov']=$res->fields['id_proveedor'];
    	   $valores_defecto['ganancias']['razon_social']=$res->fields['razon_social'];
    }
      break; 
    case 'Ingresos Brutos': {
    	$valores_defecto['rib']['prov']=$res->fields['id_proveedor'];
    	$valores_defecto['rib']['razon_social']=$res->fields['razon_social'];
    }
      break;                        
    case 'licitaciones': {
    	 $valores_defecto['Multas']['prov']=$res->fields['id_proveedor'];
    	 $valores_defecto['Multas']['razon_social']=$res->fields['razon_social'];
    }
      break;
    case 'Banco Ciudad de Buenos Aires': {
    	$valores_defecto['Transferencia']['prov']=$res->fields['id_proveedor'];
    	$valores_defecto['Transferencia']['razon_social']=$res->fields['razon_social'];
    }
      break; 
    case 'Gastos de Licitaciones': {
    	  $valores_defecto['Otros']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['Otros']['razon_social']=$res->fields['razon_social'];
    } 
      break; 
    case '1. CORADIR':{
    	  $valores_defecto['Cheque Diferido']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['Cheque Diferido']['razon_social']=$res->fields['razon_social'];
    } 
      break;     
    case 'Cargas Sociales': {
    	  $valores_defecto['suss']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['suss']['razon_social']=$res->fields['razon_social'];
    } 
      break; 
    case 'Banco Supervielle': {
    	  $valores_defecto['Devolucion Prestamo']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['Devolucion Prestamo']['razon_social']=$res->fields['razon_social'];
    	  $valores_defecto['Intereses']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['Intereses']['razon_social']=$res->fields['razon_social'];
    	  $valores_defecto['Gastos Administrativos']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['Gastos Administrativos']['razon_social']=$res->fields['razon_social'];
    	  $valores_defecto['Comisiones']['prov']=$res->fields['id_proveedor'];
    	  $valores_defecto['Comisiones']['razon_social']=$res->fields['razon_social'];
    } 
      break;      
  } 
  $res->MoveNext();
}


$sql="select  numero_cuenta,concepto,plan
from general.tipo_cuenta 
where
(concepto ilike 'Impuestos' and plan ilike 'Retenciones I.V.A.') or
(concepto ilike 'Impuestos' and plan ilike 'Retenciones Ganancia') or
(concepto ilike 'Impuestos' and plan ilike 'RIB Capital Federal') or
(concepto ilike 'Comerciales' and plan ilike 'Multa/Entrega') or
(concepto ilike 'Bancos' and plan ilike 'Depósito Banco Ciudad') or
(concepto ilike 'Bancos' and plan ilike'Comisiones') or
(concepto ilike 'Comerciales' and plan ilike 'Venta Financiada') or
(concepto ilike 'Impuestos' and plan ilike 'Retencion de Cargas Sociales') or
(concepto ilike 'Comerciales' and plan ilike 'Devolución de Préstamo') or 
(concepto ilike 'Financieros' and plan ilike 'Intereses Financieros') or 
(concepto ilike 'Personal' and plan ilike 'Honorarios a Prof')";

$res=sql($sql) or fin_pagina();

while (!$res->EOF) {
	$concepto=$res->fields['concepto']."[".$res->fields['plan']."]";
	
  switch ($res->fields['concepto']) {
    case 'Impuestos': {
           switch ($res->fields['plan']) {
               case 'Retenciones I.V.A.': {
               	     $valores_defecto['iva']['cta']=$res->fields['numero_cuenta'];
               	     $valores_defecto['iva']['concepto']=$concepto;
               }  
                 break;
               case 'Retenciones Ganancia': {
               	 $valores_defecto['ganancias']['cta']=$res->fields['numero_cuenta']; 
               	 $valores_defecto['ganancias']['concepto']=$concepto; 
               }
                 break;
               case 'RIB Capital Federal': {
               	   $valores_defecto['rib']['cta']=$res->fields['numero_cuenta']; 
               	   $valores_defecto['rib']['concepto']=$concepto; 
               }
                 break;  
               case 'Retencion de Cargas Sociales': {
               	   $valores_defecto['suss']['cta']=$res->fields['numero_cuenta']; 
               	   $valores_defecto['suss']['concepto']=$concepto; 
               }
                 break;  
           }
    }
    break; 
    case 'Comerciales': { 
     switch ($res->fields['plan']) {
    	 case 'Multa/Entrega': {
    	     $valores_defecto['Multas']['cta']=$res->fields['numero_cuenta'];
    	     $valores_defecto['Multas']['concepto']=$concepto;
             } 
            break;
         case 'Venta Financiada': {
    	     $valores_defecto['Cheque Diferido']['cta']=$res->fields['numero_cuenta'];
    	     $valores_defecto['Cheque Diferido']['concepto']=$concepto;
             } 
         break;    
           case 'Devolución de Préstamo': {
    	     $valores_defecto['Devolucion Prestamo']['cta']=$res->fields['numero_cuenta'];
    	     $valores_defecto['Devolucion Prestamo']['concepto']=$concepto;
             } 
         break; 
     }
    }
     break;
     case 'Bancos': {
           switch ($res->fields['plan']) {
               case 'Depósito Banco Ciudad':{
               	  $valores_defecto['Transferencia']['cta']=$res->fields['numero_cuenta'];
               	  $valores_defecto['Transferencia']['concepto']=$concepto;
               }
                 break;
               case 'Comisiones': {
               	   $valores_defecto['Otros']['cta']=$res->fields['numero_cuenta']; 
               	   $valores_defecto['Otros']['concepto']=$concepto;  
               	   $valores_defecto['Comisiones']['cta']=$res->fields['numero_cuenta']; 
               	   $valores_defecto['Comisiones']['concepto']=$concepto; 
               }
                 break;         
           }
       }
    break;
    case 'Financieros': {
               	  $valores_defecto['Intereses']['cta']=$res->fields['numero_cuenta'];
               	  $valores_defecto['Intereses']['concepto']=$concepto;
    }
    break;
   case 'Personal': {
               	  $valores_defecto['Gastos Administrativos']['cta']=$res->fields['numero_cuenta'];
               	  $valores_defecto['Gastos Administrativos']['concepto']=$concepto;
    }
    break; 
  }
  $res->MoveNext(); 
}  //fin while 

$id_banco=retorna_id('tipo_banco','idbanco','nombrebanco','Ciudad de Buenos Aires','ilike');   
$id_deposito=retorna_id('tipo_depósito','idtipodep','tipodepósito','Pago Proveedores del Estado','ilike');      

$valores_defecto['Transferencia']['banco']=$id_banco;
$valores_defecto['Transferencia']['nbrebanco']="Ciudad de Buenos Aires";
$valores_defecto['Transferencia']['deposito']=$id_deposito;
$valores_defecto['Transferencia']['nbredeposito']="Pago Proveedores del Estado";

return $valores_defecto;

}

/*******************************************************************
 *@ armar_valores_detalle
 *@ retorna un arreglo con los valores guardados en detalle_egresos
 *@ id_cobranza 
 *@ id_detalle_ingreso (si el detalle es de venta_factura)
********************************************************************/
function armar_valores_detalle ($id_cobranza,$id_detalle_ingreso="") {
$sql="select monto_detalle,id_tipo_egreso,id_proveedor,numero_cuenta,
      descripcion,id_cob_egreso,idtipodep,idbanco,dolar_egreso
	  from licitaciones.detalle_egresos
      join licitaciones.egreso_cob using (id_cob_egreso) 
	  where id_cobranza=$id_cobranza ";
if ($id_detalle_ingreso!="") 
  $sql.=" and id_detalle_ingreso=$id_detalle_ingreso";
$res=sql($sql,"Error en armar valores detalle $sql") or fin_pagina();  

$id_transferencia=retorna_id('egreso_cob','id_cob_egreso','descripcion','Transferencia','ilike');

$valores_defecto=array();
  while (!$res->EOF) {
  	  $desc=$res->fields['descripcion'];
      $valores_defecto[$desc]['monto_detalle']=$res->fields['monto_detalle'];
      $valores_defecto[$desc]['id_tipo_egreso']=$res->fields['id_tipo_egreso'];
      $valores_defecto[$desc]['id_proveedor']=$res->fields['id_proveedor'];
      $valores_defecto[$desc]['numero_cuenta']=$res->fields['numero_cuenta'];
      if ($res->fields['id_cob_egreso']==$id_transferencia) {
         $valores_defecto[$desc]['idbanco']=$res->fields['idbanco'];
         $valores_defecto[$desc]['idtipodep']=$res->fields['idtipodep'];
      }
     $valores_defecto['dolar']=$res->fields['dolar_egreso'];
  $res->MoveNext();
}

return  $valores_defecto;
}

/*****************************************************************
   mail_egresos_parciales
  *@  Manda un mail avisando que se realizaron egresos parciales
  *@  Controla si se han cambiado los datos por defecto 
   
*********************************************************/
function mail_egresos_parciales($id_cobranza,$nombre_distrito,$id_licitacion,$nro_factura,$valores_defecto,$tipo_f) {
	global $contenido_egreso;
$para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";
$asunto="Egresos parciales desde cobranzas ";
//retorna un arreglo con los valores por defecto

$id_transferencia=retorna_id('egreso_cob','id_cob_egreso','descripcion','Transferencia','ilike');

$contenido .="Se han realizado egresos parciales desde seguimiento de cobros \n";
$contenido .="EN LA CAJA DE ". str_replace("- GCBA"," ",$nombre_distrito);
$contenido.=" Para la factura:". $tipo_f. " NRO: ".$nro_factura;
if (es_numero($id_licitacion))
     $contenido.= " asociada a licitacion $id_licitacion \n" ;
  

$sql="select descripcion,monto,dolar_egreso,caja.id_moneda,id_distrito,iddepósito,
			 simbolo,caja.fecha,id_ingreso_egreso,id_detalle_ingreso,
             detalle_egresos.iddepósito,detalle_egresos.id_proveedor,detalle_egresos.id_tipo_egreso,detalle_egresos.numero_cuenta,detalle_egresos.idtipodep,detalle_egresos.idbanco,id_cob_egreso
			 from licitaciones.detalle_egresos
		     join caja.ingreso_egreso using (id_ingreso_egreso) 
			 join caja.caja using (id_caja)
			 join licitaciones.moneda on caja.id_moneda=moneda.id_moneda
			 join licitaciones.egreso_cob using (id_cob_egreso)
             left join bancos.depósitos using (iddepósito)
			 where id_cobranza=$id_cobranza and id_cob_egreso!=7 and id_cob_egreso!=8 order by id_detalle_ingreso,ingreso_egreso.id_ingreso_egreso";

$contenido.=" \n DETALLES DE EGRESOS \n";
$res=sql($sql,"Error al recuperar datos egresos $sql ") or fin_pagina();
$total=0;
while (!$res->EOF) {
 $desc=$res->fields['descripcion'];
 $id_cob_egreso=$res->fields['id_cob_egreso'];
 $contenido.= "\nDescripción: ".strtoupper($desc).",  Monto " .$res->fields['simbolo']." ".formato_money($res->fields['monto']); 
 if ($res->fields['id_moneda']==2)	$contenido.= " Valor Dolar: ".formato_money($res->fields['dolar_egreso']);
 
 if ($res->fields['id_moneda']==1) $total+=$res->fields['monto'];
           else $total+=$res->fields['monto']*$res->fields['dolar_egreso'];
 
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
 
$res->MoveNext();
}
$sql="select id_moneda,id_cobranza,dolar_egreso,monto_detalle,fecha_detalle,simbolo,descripcion
      from licitaciones.detalle_egresos
	  join licitaciones.moneda using (id_moneda)
	  join licitaciones.egreso_cob using (id_cob_egreso)
	  where id_cobranza=$id_cobranza and (id_cob_egreso=7 or id_cob_egreso=8) 
	  order by id_detalle_ingreso";

$res=sql($sql) or fin_pagina();
while (!$res->EOF) {
 $contenido.= "\n Descripción ".strtoupper($res->fields['descripcion'])." Monto ".$res->fields['simbolo']." ".formato_money($res->fields['monto_detalle']); 
 if ($res->fields['id_moneda']==2)	$contenido.= " Valor Dolar: ".formato_money($res->fields['dolar_egreso'])."\n";
 $res->MoveNext();
}

if ($total > 5000) {
        $para.=',corapi@coradir.com.ar';
    }

enviar_mail($para,$asunto,$contenido,'','','',0);   

}

/*****************************************************************************
 *concatenar_datos_mail
 *concatena a la variable $contenido informacion en el caso que se hayan cambiado 
  los valores por defecto de tipo_ingreso, proveedor y concepto/plan 
 *@ $val_sel: arreglo con tipo tipo_ingreso, proveedor y concepto/plan seleccionados
 *@ por ejemplo si se seleciono el campo ganancia el arreglo:
 *@          [gan] => Array ( [prov] => 757 [cta] => 602 [tipo] => 11 )
 *@ $desc: descripcion del egreso (iva ganacia....)
 *@ $nom_tipo_defecto: nombre por defecto del tipo de ingreso
 *@ $nom_prov_defecto: nombre por defecto del proveedor
 *@ $valor:arreglo con concepto y plan seleccionado
****************************************************************************/
function concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor) {
global $contenido; 

   $comp1=array();
   $comp1[0]='concepto';
   $comp1[1]='plan';
   $tipo_sel=$val_sel[$desc]['tipo'];	
   $prov_sel=$val_sel[$desc]['prov'];	
   $cto_sel=$val_sel[$desc]['cta'];	
   $tipo_defecto=retorna_id('tipo_egreso','id_tipo_egreso','nombre',$nom_tipo_defecto,'ilike');
   $sql="select id_proveedor from proveedor where razon_social='$nom_prov_defecto'";
   $res=sql($sql,"en concatenar $sql") or fin_pagina();
   $prov_defecto=$res->fields['id_proveedor'];
   
   $cto_defecto=retorna_id('tipo_cuenta','numero_cuenta',$comp1,$valor,'ilike');
  
   if ($tipo_defecto !=$tipo_sel) {
      $contenido.= "\n Se modificó el Tipo de Egreso para la descripción $desc:";
      $texto=retorna_id('tipo_egreso','nombre','id_tipo_egreso',$tipo_sel,'=');
      $contenido.= "\n TIPO EGRESO POR DEFECTO $nom_tipo_defecto , MODIFICADO A =>".$texto;
   }
   if ($prov_defecto !=$prov_sel){
      $contenido.= "\n Se modificó el Proveedor para la descripción $desc:";
      $texto=retorna_id('proveedor','razon_social','id_proveedor',$prov_sel,'=');
      $contenido.= "\n PROVEEDOR POR DEFECTO $nom_prov_defecto , MODIFICADO A => ".$texto;              
   }
   if ($cto_defecto !=$cto_sel) {
      $contenido.= "\n Se modificó el Concepto y Plan para la descripción $desc:";
      $sql="select concepto,plan from tipo_cuenta where numero_cuenta=$cto_sel";
      $res=sql($sql) or fin_pagina();
      $texto1=$res->fields['concepto'];
      $texto2=$res->fields['plan'];
      $contenido.= "\n CONCEPTO Y PLAN POR DEFECTO ". $valor[0]." [".$valor[1]."], MODIFICADO A => ". $texto1. "[".$texto2."]";
   }

}

/*****************************************************************************
 *concat_dep_mail
 *concatena a la variable $contenido informacion en el caso que se hayan cambiado 
  los valores por defecto del deposito de tranferecia
 *@ $val_sel: arreglo con banco y tipo_dep
 *@ $desc: descripcion del egreso (iva ganacia....)
****************************************************************************/
function  concat_dep_mail($val_sel,$desc) {
global $contenido;
$banco_sel=$val_sel[$desc]['banco'];	
$dep_sel=$val_sel[$desc]['tipo_dep'];
$banco_defecto=retorna_id('tipo_banco','idbanco','nombrebanco','Ciudad de Buenos Aires','ilike');   
$dep_defecto=retorna_id('tipo_egreso','id_tipo_egreso','nombre','Depósito Ciudad','ilike');	
$dep_defecto=retorna_id('tipo_depósito','idtipodep','tipodepósito','Pago Proveedores del Estado','ilike');   

if ($banco_sel != $banco_defecto) {
      $contenido.= "\n Se modificó el BANCO  para el depósito realizado para la descripción TRANSFERENCIA:";
      $texto=retorna_id('tipo_banco','nombrebanco','idbanco',$banco_sel,'=');   
      $contenido.= "\n BANCO POR DEFECTO Depósito Ciudad , MODIFICADO A =>".$texto;
   }
if ($dep_sel != $dep_defecto) {
      $contenido.= "\n Se modificó el TIPO DE DEPOSITO  para el depósito realizado para la descripción TRANSFERENCIA:";
      $texto=$id_deposito=retorna_id('tipo_depósito','tipodepósito','idtipodep',$dep_sel,'=');   
      $contenido.= "\n TIPO DE DEPOSITO  POR DEFECTO Pago Proveedores del Estado , MODIFICADO A =>".$texto;
   }   
   
}


/*****************************************************************************
 *control_datos
 * arma los parametros segun el boton de check seleccionado para invocar
 * a la funccion   concatenar_datos_mail() (y  a concat_dep_mail() para 
 * armar la variable contenido con los datos del mail
 * @ val_sel: arreglo con los valores seleccionados para cada descripcion
 * @ $contenido: contiene el texto del mail al que se concatena los cambios de los campos
    proveedor, tipo_egreso y concepto/plan
****************************************************************************/
function  control_datos($val_sel)  {
global $contenido;
print_r($val_sel);
$cant_iva=count ($val_sel['IVA']);
$cant_ganancia=count ($val_sel['GANANCIAS']);
$cant_rib=count ($val_sel['RIB']);
$cant_multa=count ($val_sel['MULTAS']);
$cant_tranferencia=count ($val_sel['TRANSFERENCIA']);
$cant_otro=count ($val_sel['OTROS']);
$cant_cheque_dif=count ($val_sel['Cheque Diferido']);
$cant_suss=count ($val_sel['SUSS']);
$cant_devp=count ($val_sel['Devolucion prestamo']);
$cant_int=count ($val_sel['intereses']);
$cant_adm=count ($val_sel['gastos adm']);
$cant_com=count ($val_sel['comisiones']);

$valor1=array();

if ($cant_iva >0) {
   $desc="IVA";
   $nom_tipo_defecto="IVA" 	;
   $nom_prov_defecto="IVA";
   $valor[0]='Impuestos';
   $valor[1]='Retenciones I.V.A.';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}
if ($cant_ganancia >0) {
   $desc="GANANCIAS";
   $nom_tipo_defecto="Impuestos" 	;
   $nom_prov_defecto="GANANCIAS";
   $valor[0]='Impuestos';
   $valor[1]='Retenciones Ganancia';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_rib >0) {
   $desc="RIB";
   $nom_tipo_defecto="Impuestos" 	;
   $nom_prov_defecto="Ingresos Brutos";
   $valor[0]='Impuestos';
   $valor[1]='RIB Capital Federal';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_multa >0) {
   $desc="MULTAS";
   $nom_tipo_defecto="Licitaciones";
   $nom_prov_defecto="licitaciones";
   $valor[0]='Comerciales';
   $valor[1]='Multa/Entrega';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}
if ($cant_tranferencia >0) {
   $desc="TRANSFERENCIA";
   $nom_tipo_defecto="Depósito Ciudad" 	;
   $nom_prov_defecto="Banco Ciudad de Buenos Aires";
   $nom_banco_defecto="Ciudad de Buenos Aires";
   $nom_dep_defecto="Ciudad de Buenos Aires";
   
   $valor[0]='Bancos';
   $valor[1]='Depósito Banco Ciudad';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
   concat_dep_mail($val_sel,$desc);
}
if ($cant_otro >0) {
   $desc="OTROS";
   $nom_tipo_defecto="Otros";
   $nom_prov_defecto="Gastos de Licitaciones";
   $valor[0]='Bancos';
   $valor[1]='Comisiones';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_suss >0) {
   $desc="SUSS";
   $nom_tipo_defecto="Impuestos";
   $nom_prov_defecto="Cargas Sociales";
   $valor[0]='Impuestos';
   $valor[1]='Retencion de Cargas Sociales';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_devp >0) {
   $desc="Devolucion prestamo";
   $nom_tipo_defecto="Devolución de dinero";
   $nom_prov_defecto="Banco Supervielle";
   $valor[0]='Comerciales';
   $valor[1]='Devolución de Préstamo';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}


if ($cant_int >0) {
   $desc="intereses"; 
   $nom_tipo_defecto="otros";
   $nom_prov_defecto="Banco Supervielle";
   $valor[0]='Financieros';
   $valor[1]='Intereses Financieros';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_adm >0) {
   $desc="gastos adm";
   $nom_tipo_defecto="otros";
   $nom_prov_defecto="Banco Supervielle";
   $valor[0]='Personal';
   $valor[1]='Honorarios a Prof';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_com >0) {
   $desc="comisiones";
   $nom_tipo_defecto="otros";
   $nom_prov_defecto="Banco Supervielle";
   $valor[0]='Bancos';
   $valor[1]='Comisiones';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

if ($cant_cheque_dif > 0) {
   $desc="Cheques Diferidos";
   $nom_tipo_defecto="Cheque Diferido";
   $nom_prov_defecto="1. CORADIR";
   $nom_banco_defecto="";
   $valor[0]='Comerciales';
   $valor[1]='Venta Financiada';
   concatenar_datos_mail($val_sel,$desc,$nom_tipo_defecto,$nom_prov_defecto,$valor);
}

}

function guarda_detalle_egresos($id_detalle_ingreso="",$dolar_actual=0,$fecha_detalle="") {
global $db;

if (!$fecha_detalle) $fecha_detalle = date("Y-m-d",mktime());

if ($_POST['id_cobranza']!="")
	$id_cobranza=$_POST['id_cobranza'];
 else  //cobranza primaria
  $id_cobranza=$_POST['id_cob'];

if ($id_detalle_ingreso!="" || $id_detalle_ingreso!=null) {
   $and= " and id_detalle_ingreso=$id_detalle_ingreso";
} else
    $id_detalle_ingreso='NULL';

$id_moneda=$_POST['moneda_ingreso'];
 
$db->StartTrans();

 $sql="delete from licitaciones.detalle_egresos where id_cobranza=$id_cobranza $and";
 sql($sql) or fin_pagina();

 //borro los cheques ligados a la cobranza
 $sql="select id_chequedif from cheque_cobranza where id_cobranza=$id_cobranza";
 $resultado_idcheques = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $sql="delete from cheque_cobranza where id_cobranza=$id_cobranza";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 while(!$resultado_idcheques->EOF)
  {
   $sql="delete from cheques_diferidos where id_chequedif=".$resultado_idcheques->fields['id_chequedif'];
   $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
   $resultado_idcheques->MoveNext();
  }

 $sql_detalle="";
 $sql_detalle_dep;
if ($_POST['chk_iva']==1) {
 // recupera datos del iva
 $proveedor=$_POST['prov_iva'];
 $tipo_egreso=$_POST['tipo_iva'];
 $monto=$_POST['iva'];
 $monto=number_format($monto,2,".","");
 $nro_cuenta=$_POST['concepto_iva'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos
     (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
     values ($id_cobranza,1,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}

 if ($_POST['chk_gan']==1) {
 // recupera datos de ganancias
 $proveedor=$_POST['prov_gan'];
 $tipo_egreso=$_POST['tipo_gan'];
 $monto=$_POST['ganancia'];
 $monto=number_format($monto,2,".","");
 $nro_cuenta=$_POST['concepto_gan'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
  values ($id_cobranza,2,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}

 if ($_POST['chk_rib'] ==1) {
 // recupera datos de rib
 $proveedor=$_POST['prov_rib'];
 $tipo_egreso=$_POST['tipo_rib'];
 $monto=$_POST['rib'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_rib'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
 values ($id_cobranza,3,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}
 
 if ($_POST['chk_suss'] ==1) {
 // recupera datos de suss
 $proveedor=$_POST['prov_suss'];
 $tipo_egreso=$_POST['tipo_suss'];
 $monto=$_POST['suss'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_suss'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
 values ($id_cobranza,10,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}


 if ($_POST['chk_mul'] ==1) {
 // recupera datos de multas
 $proveedor=$_POST['prov_multas'];
 $tipo_egreso=$_POST['tipo_multas'];
 $monto=$_POST['multas'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_multas'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,4,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
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
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,idbanco,idtipodep,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,5,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,$banco,$tipo,0,'$fecha_detalle',$id_detalle_ingreso)";
 }
 
 if ($_POST['chk_otro'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_otro'];
 $tipo_egreso=$_POST['tipo_otro'];
 $monto=$_POST['otro'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_otro'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,6,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}

 
 if ($_POST['chk_devp'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_devolucion'];
 $tipo_egreso=$_POST['tipo_devolucion'];
 $monto=$_POST['devolucion'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_devolucion'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,11,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}
 
 if ($_POST['chk_int'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_interes'];
 $tipo_egreso=$_POST['tipo_interes'];
 $monto=$_POST['interes'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_interes'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,12,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}
 
 if ($_POST['chk_adm'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_gastoadm'];
 $tipo_egreso=$_POST['tipo_gastoadm'];
 $monto=$_POST['gastoadm'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_gastoadm'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,13,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}
 
 if ($_POST['chk_com'] ==1) {
 // recupera datos de otros
 $proveedor=$_POST['prov_comisiones'];
 $tipo_egreso=$_POST['tipo_comisiones'];
 $monto=$_POST['comisines'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_otro'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso)
   values ($id_cobranza,14,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}

if ($_POST['chk_diferido'] ==1) {
	
 // recupera datos de otros
 $proveedor=$_POST['prov_diferido'];
 $tipo_egreso=$_POST['tipo_diferido'];
 $id_entidad=$_POST['id_cliente'];
 
 if (!$id_entidad) {
 $sql="select case when facturas.id_entidad is null then cobranzas.id_entidad
       else facturas.id_entidad end as id_entidad,id_cobranza
       from licitaciones.cobranzas 
       left join facturacion.facturas using (id_factura)
       where id_cobranza=$id_cobranza";
 $res=sql($sql,"$sql") or fin_pagina();
 $id_entidad=$res->fields['id_entidad'];
 }
 
 $monto=$_POST['diferido'];
 $monto=number_format($monto,2,".",""); 
 $nro_cuenta=$_POST['concepto_diferido'];
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso) values ($id_cobranza,9,$proveedor,$tipo_egreso,$nro_cuenta,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
 
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
  
  $sql="insert into cheques_diferidos(nro_cheque,id_banco,fecha_vencimiento,comentario,monto,fecha_ingreso,id_empresa_cheque,activo,ubicacion,id_entidad) values('$nro_cheque','$banco','$fecha_vencimiento','$comentario',$monto,'".date("Y-m-d")."',$pertenece,0,'$ubicacion',$id_entidad);";
  $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
  $sql="select max(id_chequedif) from cheques_diferidos;";
  $resultado_max = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
  $sql="insert into cheque_cobranza(id_chequedif,id_cobranza) values(".$resultado_max->fields['max'].",$id_cobranza);";
  $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
  
  $i++;
 }
}

if ($_POST['chk_ficticio'] ==1) {
 // recupera datos de ficticio
 $monto=$_POST['ficticio'];
 $monto=number_format($monto,2,".",""); 
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso) 
 values ($id_cobranza,7,NULL,NULL,NULL,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}
if ($_POST['chk_cheque'] ==1) {
 // recupera datos de cheque
 $monto=$_POST['cheque'];
 $monto=number_format($monto,2,".",""); 
 $sql_detalle[]="insert into licitaciones.detalle_egresos (id_cobranza,id_cob_egreso,id_proveedor,id_tipo_egreso,numero_cuenta,monto_detalle,dolar_egreso,id_moneda,control_egreso,fecha_detalle,id_detalle_ingreso) 
   values ($id_cobranza,8,NULL,NULL,NULL,$monto,$dolar_actual,$id_moneda,0,'$fecha_detalle',$id_detalle_ingreso)";
}


sql($sql_detalle,"Error en guardar detalles: $sql_detalle") or fin_pagina();
  
 if ($db->CompleteTrans()) 
     return 1;
 else return 0;

} //fin_guarda_detalle_egresos


function guardar_egreso($id_caja,$proveedor,$tipo_egreso,$monto,$comentarios,$usuario,$fecha_creacion,$item,$nro_cuenta,$id_moneda) { 
	
 $query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
 $id_ie=sql($query) or fin_pagina();
 $id=$id_ie->fields['id'];
 //query para insertar en la base de datos el egreso
 $query="INSERT into caja.ingreso_egreso (id_ingreso_egreso,id_caja,id_proveedor,id_tipo_egreso,monto,comentarios,usuario,fecha_creacion,item,numero_cuenta)
  values($id,$id_caja,$proveedor,$tipo_egreso,$monto,'$comentarios','$usuario','$fecha_creacion','$item',$nro_cuenta)";

 
  sql($query,"ERROR EN GUARDAR EGRESO $query")or fin_pagina();
  return ($id); 

} //fin de guardar_egreso


// GUARDAR EL EGRESO EN BANCOS 
function guardar_deposito($banco,$fecha,$tipo,$monto,$comen) {
 $sql_max= "SELECT nextval('bancos.depósitos_iddepósito_seq') as max";
 $res_max=sql($sql_max) or fin_pagina();
 $max=$res_max->fields['max'];
 $sql_dep = "INSERT INTO bancos.depósitos (iddepósito,IdBanco,FechaDepósito,FechaCrédito,IdTipoDep,ImporteDep,comentario) 
             VALUES ($max,$banco,'$fecha','$fecha',$tipo,$monto,'$comen')";

 sql($sql_dep) or fin_pagina();
 return ($max);
}



//compara datos de la factura y del ingreso,si hay diferencia
// en los datos manda un mail avisando cual es la diferencia
//si no hay diferencia el mail se manda avisndo que se realizo el ingreso
//val_Anterior -> datos de la factura
//val-> datos del ingreso
function datos_mail($id,$nro_factura,$distrito,$id_licitacion,$val_ant,$val) {

$asunto="Ingreso desde seguimiento de cobros ";
$sql="select nombre from distrito where id_distrito=$distrito";
$res_d=sql($sql,"En datos mail $sql") or fin_pagina();

$dist=str_replace("- GCBA"," ",$res_d->fields['nombre']);

$sql_time="select usuario,fecha_creacion from caja.ingreso_egreso where id_ingreso_egreso=$id";
$res_time=sql($sql_time) or fin_pagina();
$fecha=$res_time->fields['fecha_creacion'];
$dia=fecha($fecha);
$hora=hora($fecha);
$hora=substr($hora,0,5);
$usuario=$res_time->fields['usuario'];

//selecciona la entidad que tiene el ingreso
$sql="select nombre from entidad where id_entidad=".$val['entidad'];
  $res=sql($sql,"$sql") or fin_pagina ();
  $ent=$res->fields['nombre']; 
$contenido .="Se realizó el ingreso con id $id  en la caja de $dist \n Para la factura Nro: $nro_factura  "; 
if (es_numero($id_licitacion))
  $contenido.=" Asociada a la Licitacion: $id_licitacion. \n ";
$contenido.= "ENTIDAD DEL INGRESO: $ent \n";

$contenido.= "Ingreso Realizado por el usuario $usuario  el día $dia a las $hora hs. \n";
$sql="select simbolo from moneda where id_moneda=".$val_ant['moneda'];
$res=sql($sql,"$sql") or fin_pagina();

$contenido.="\n MONTO DE LA FACTURA =>". $res->fields['simbolo']." ".formato_money($val_ant['monto']);
if ($val_ant['moneda']==2) 
     $contenido.= "    VALOR DOLAR DE LA FACTURA ".formato_money($val_ant['dolar'])." \n";

if ($val_ant['moneda'] != $val['moneda'])  {
$sql="select simbolo from moneda where id_moneda=".$val['moneda'];
$res=sql($sql,"En datos mail $sql") or fin_pagina();
}    
$contenido.="\n MONTO DEL INGRESO =>". $res->fields['simbolo']." ".formato_money($val['monto']);
if ($val_ant['moneda'] !=1 || $val['moneda'] !=1) 
    $contenido.= "   VALOR DOLAR DEL INGRESO ".formato_money($val['dolar'])." \n";     

//INDICA SI HAY DIFERENCIA ENTRE EL MONTO DE LA FACTURA Y EL MONTO DEL INGRESO

switch ($val_ant['moneda']) {
   
   case 2: {  //factura en dolares
   	         switch ($val['moneda']) {
               case 1: { //ingreso en pesos
                         $monto_ctr_ant= $val_ant['dolar'] * $val_ant['monto'];
                         $diff=$val['monto'] - $monto_ctr_ant;
               	        break;
                       }
               case 2: { //ingreso en dolares
                       $monto_ctr_ant= $val_ant['dolar'] * $val_ant['monto'];
                       $monto_ctrl= $val['dolar'] * $val['monto'];
                       $diff= $monto_ctrl - $monto_ctr_ant;
               	       break;
                        }
             }
   break;
   } 

} //fin switch 


if ($diff < 0 ) {
$contenido.= "\n DIFERENCIA FALTANTE PARA NOTA DE CREDITO $ ".formato_money($diff)."\n";
}
elseif ($diff > 0  && formato_money($diff)!='0,00') {
$contenido.= "\n DIFERENCIA (SOBRA)  PARA NOTA DE DEBITO  $ ".formato_money($diff)."\n";
}

if ($val_ant['tipo_ing'] != $val['tipo_ing']) {
  $contenido.="<br>Se ha modificado el tipo de ingreso:  \n";
  $sql="select nombre from tipo_ingreso where id_tipo_ingreso=".$val_ant['tipo_ing']; 
  $res=sql($sql,"$sql") or fin_pagina ();
  $ing_ant=$res->fields['nombre'];
  $sql="select nombre from tipo_ingreso where id_tipo_ingreso=".$val['tipo_ing']; 
  $res=sql($sql,"$sql") or fin_pagina ();
  $ing=$res->fields['nombre'];
  $contenido.="     TIPO DE INGRESO: ".$ing_ant. "   TIPO DE INGRESO MODIFICADO: $ing \n";
} 

if ($val_ant['cuenta'] != $val['cuenta']) {
	  $contenido.="<br>Se ha modificado el Tipo de Cuenta:  \n";
	  $sql="select nombre from caja.tipo_cuenta_ingreso where id_cuenta_ingreso=".$val_ant['cuenta']; 
      $res=sql($sql,"$sql") or fin_pagina ();
      $cta_ant=$res->fields['nombre'];
      $sql="select nombre from caja.tipo_cuenta_ingreso where id_cuenta_ingreso=".$val['cuenta']; 
      $res=sql($sql,"$sql") or fin_pagina ();
      $cta=$res->fields['nombre'];
  	  $contenido.="TIPO CUENTA: ".$cta_ant." TIPO CUENTA MODIFICADO: $cta \n";
 }
 

if ($val_ant['entidad'] != $val['entidad'] ) {
  $contenido.= "<br>Se ha modificado la entidad:  \n";
  if ($val_ant['entidad'] != null || $val_ant['entidad'] != "") {
   $sql="select nombre from entidad where id_entidad=".$val_ant['entidad']; 
   $res=sql($sql,"$sql") or fin_pagina ();
   $ent_ant=$res->fields['nombre'];
  }
  $contenido.= "ENTIDAD: ".$ent_ant."  ENTIDAD MODIFICADA: $ent \n";
 }
 
  return $contenido;
}

//retorna los datos del detalle de venta factura
function detalle_vta($id_cobranza) {
        $sql="select id_entidad,id_tipo_ingreso,id_cuenta_ingreso
              from datos_ingresos where id_cobranza=$id_cobranza";	
        $res=sql($sql) or fin_pagina(); 

        $datos_vta=array();
        $datos_vta['id_entidad']=$res->fields['id_entidad'];
        $datos_vta['id_tipo_ingreso']=$res->fields['id_tipo_ingreso'];
        $datos_vta['id_cuenta_ingreso']=$res->fields['id_cuenta_ingreso'];
  return $datos_vta;
} // de la funcion detalle_vta

//compara datos de la factura y de los ingreso,si hay diferencia
// en los datos manda un mail avisando cual es la diferencia
//si no hay diferencia el mail se manda avisndo que se realizo el ingreso

function datos_mail_atadas($list_cob,$distrito) {
$tipo_ing=retorna_id('tipo_ingreso','id_tipo_ingreso','nombre','Cobros','ilike');
$cuenta=retorna_id('tipo_cuenta_ingreso','id_cuenta_ingreso','nombre','Cobros (Facturas Clientes)','ilike');

$para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";
$asunto="Ingresos desde seguimiento de cobros ";

$sql="select nombre from distrito where id_distrito=$distrito";
$res_d=sql($sql,"$sql en datos mail atadas") or fin_pagina();
$dist=str_replace("- GCBA"," ",$res_d->fields['nombre']);

$sql="select id_ingreso_egreso,cobranzas.id_licitacion,facturas.id_entidad as entidad_factura,cobranzas.id_moneda as moneda_factura,
      fecha_creacion,cobranzas.nro_factura,cobranzas.monto_original as monto_factura,ingreso_egreso.monto as monto_ingreso,
      ingreso_egreso.usuario,ingreso_egreso.id_entidad as entidad_ingreso,id_tipo_ingreso,id_cuenta_ingreso,
      entidad.nombre as nbre_entidad_ingreso,simbolo as simbolo_factura,cobranzas.cotizacion_dolar as dolar_ingreso,
      facturas.cotizacion_dolar as dolar_factura,caja.id_moneda as moneda_ingreso
      from licitaciones.cobranzas 
	  join caja.ingreso_egreso using(id_ingreso_egreso)
	  join caja.caja using (id_caja)
	  join licitaciones.entidad on entidad.id_entidad=ingreso_egreso.id_entidad
	  join licitaciones.moneda on moneda.id_moneda=cobranzas.id_moneda
      join facturacion.facturas using (id_factura)
	  where id_cobranza in $list_cob";

$res=sql($sql,"EN DATOS MAIL ATADAs $sql") or fin_pagina();
while (!$res->EOF) {
  $fecha=$res->fields['fecha_creacion'];
  $dia=fecha($fecha);
  $hora=hora($fecha);
  $hora=substr($hora,0,5);
  $usuario=$res->fields['usuario'];
  //selecciona la entidad que tiene el ingreso	
  $ent=$res->fields['nbre_entidad_ingreso'];	
  $id=$res->fields['id_ingreso_egreso'];

  $contenido .="Se realizó el ingreso con id $id  en la caja de $dist \n Para la factura Nro:".$res->fields['nro_factura']; 
if (es_numero($res->fields['id_licitacion']))
  $contenido.=" Asociada a la Licitacion:". $res->fields['id_licitacion']." \n ";
  $contenido.= "ENTIDAD DEL INGRESO: $ent \n";

$contenido.= "Ingreso Realizado por el usuario $usuario el día $dia a las $hora hs. \n";

$contenido.="\n MONTO DE LA FACTURA =>". $res->fields['simbolo_factura']." ".formato_money($res->fields['monto_factura']);
if ($res->fields['moneda_factura']==2) 
     $contenido.= "    VALOR DOLAR DE LA FACTURA ".formato_money($res->fields['dolar_factura'])." \n";

if ($res->fields['moneda_factura'] != $res->fields['moneda_ingreso'])  {
$sql="select simbolo from moneda where id_moneda=".$res->fields['moneda_ingreso'];
$res_mon=sql($sql,"en datos mail atadas $sql") or fin_pagina();
$sim=$res_mon->fields['simbolo'];
}    
else $sim=$res->fields['simbolo_factura'];
$contenido.="\n MONTO DEL INGRESO =>". $sim." ".formato_money($res->fields['monto_ingreso']);
if ($res->fields['moneda_factura'] !=1 || $res->fields['moneda_ingreso'] !=1) 
    $contenido.= "   VALOR DOLAR DEL INGRESO ".formato_money($res->fields['dolar_ingreso'])." \n";     

 
//controla si se modificaron los valores por defecto
if ($tipo_ing != $res->fields['id_tipo_ingreso']) {
  $contenido.="<br>Se ha modificado el tipo de ingreso:  \n";
  $sql="select nombre from tipo_ingreso where id_tipo_ingreso=".$tipo_ing; 
  $res_t=sql($sql,"EN DATOS MAIL 1 $sql") or fin_pagina ();
  $ing_ant=$res_t->fields['nombre'];
  $sql="select nombre from tipo_ingreso where id_tipo_ingreso=".$res->fields['id_tipo_ingreso']; 
  $res_ing=sql($sql,"EN DATOS MAIL 2 $sql") or fin_pagina ();
  $ing=$res_ing->fields['nombre'];
  $contenido.="     TIPO DE INGRESO: ".$ing_ant. "   TIPO DE INGRESO MODIFICADO: $ing \n";
} 

if ($cuenta != $res->fields['id_cuenta_ingreso']) {
	  $contenido.="<br>Se ha modificado el Tipo de Cuenta:  \n";
	  $sql="select nombre from caja.tipo_cuenta_ingreso where id_cuenta_ingreso=".$cuenta; 
      $res_t=sql($sql,"$sql") or fin_pagina ();
      $cta_ant=$res_t->fields['nombre'];
      $sql="select nombre from caja.tipo_cuenta_ingreso where id_cuenta_ingreso=".$res->fields['id_cuenta_ingreso']; 
      $res_cta=sql($sql,"$sql") or fin_pagina ();
      $cta=$res_cta->fields['nombre'];
  	  $contenido.="TIPO CUENTA: ".$cta_ant." TIPO CUENTA MODIFICADO: $cta \n";
 }
 
if ($res->fields['entidad_factura'] != $res->fields['entidad_ingreso'] ) {
  $contenido.= "<br>Se ha modificado la entidad:  \n";
  if ($res->fields['entidad_factura'] != null || $res->fields['entidad_factura'] != "") {
   $sql="select nombre from entidad where id_entidad=".$res->fields['entidad_factura']; 
   $res_ent=sql($sql,"$sql") or fin_pagina ();
   $ent_ant=$res_ent->fields['nombre'];
  }
  $contenido.= "ENTIDAD: ".$ent_ant."  ENTIDAD MODIFICADA: $ent \n";
 }    
     
    
 $moneda_factura=$res->fields['moneda_factura'];   
 $moneda_ingreso=$res->fields['moneda_ingreso'];  
 $res->movenext();
}
//INDICA SI HAY DIFERENCIA ENTRE EL MONTO DE LA FACTURA Y EL MONTO DEL INGRESO
if ($moneda_factura==2) {
$sql_total="select sum(monto_original * facturas.cotizacion_dolar) as monto
           from licitaciones.cobranzas 
           join facturacion.facturas using (id_factura)
           where id_cobranza in $list_cob";
$res_total=sql($sql_total,"$sql_total") or fin_pagina();
$monto_ctr_ant=$res_total->fields['monto'];
}

switch ($moneda_factura) {
   
   case 2: {  //ingreso en dolares
   	         switch ($moneda_ingreso) {
               case 1: { //ingreso en pesos
                        $sql_ing="select sum(ingreso_egreso.monto) as monto from licitaciones.cobranzas 
                                  join caja.ingreso_egreso using(id_ingreso_egreso)
                                  where id_cobranza in $list_cob";
                        $res_ing=sql($sql_ing,"$sql_ing") or fin_pagina();
                        $diff=$res_ing->fields['monto'] - $monto_ctr_ant;
               	        break;
                       }
               case 2: { //ingreso en dolares
                       $sql_ing="select sum(ingreso_egreso.monto * cotizacion_dolar) as monto from licitaciones.cobranzas 
                                 join caja.ingreso_egreso using(id_ingreso_egreso)
                                 where id_cobranza in $list_cob";
                       $res_ing=sql($sql_ing,"$sql_ing") or fin_pagina(); 
                       $diff= $res_ing->fields['monto'] - $monto_ctr_ant;
               	       break;
                        }
             }
   break;
   } 

} //fin switch 

if ($diff < 0 ) {
$contenido.= "\n DIFERENCIA FALTANTE PARA NOTA DE CREDITO $ ".formato_money($diff)."\n";
}
elseif ($diff > 0  && formato_money($diff)!='0,00') {
$contenido.= "\n DIFERENCIA (SOBRA)  PARA NOTA DE DEBITO  $ ".formato_money($diff)."\n";
}

 
  return $contenido;
}

/**************************************************************************************
 *datos_ingreso
 *guarda los datos del ingreso  de una venta de factura de una sola factura
 *id_distrito,id_moneda,id_tipo_ingreso,id_entidad, id_cuenta_ingreso, id_cobranza,
  dolar_ingreso,cant_pagos
 *retorna id_datos_ingreso 
**************************************************************************************/
function datos_ingreso() {
global $db;

        $id_distrito=$_POST['caja'];
        $id_moneda=$_POST['moneda_sel'];
        $id_tipo_ingreso=$_POST['tipo_ing'];
        $id_entidad=$_POST['cliente'];
        $id_cuenta_ingreso=$_POST['cuentaing'];
        $id_cobranza=$_POST['id_cobranza'];
        $cant_pagos=$_POST['cant_pagos'];
        $cant_pagos_hechos=$_POST['cant_pagos_hechos']; 
        $db->StartTrans();
        $id_datos_ingreso=retorna_id ('datos_ingresos','id_datos_ingreso','id_cobranza',$id_cobranza,'=');
        if ($id_datos_ingreso!=null || $id_datos_ingreso!="" ) {
  	               if ($cant_pagos_hechos > 0) // si ya hay ingresos en caja solo cambia  cant_pagos
	                              $sql=" update datos_ingresos set cant_pagos=$cant_pagos 
                                                where id_datos_ingreso=$id_datos_ingreso";
	                              else 
                                  $sql=" update datos_ingresos set id_tipo_ingreso=$id_tipo_ingreso,
                                                                   id_entidad=$id_entidad,id_cuenta_ingreso=$id_cuenta_ingreso,
                                                                   cant_pagos=$cant_pagos 
                                                 where id_datos_ingreso=$id_datos_ingreso";
         }
         else {
              $query="select nextval('datos_ingresos_id_datos_ingreso_seq') as id_datos";
              $res_datos=sql($query) or fin_pagina();
              $id_datos_ingreso=$res_datos->fields['id_datos'];
              $sql[]="insert into datos_ingresos 
                       (id_datos_ingreso,id_distrito,id_moneda,id_tipo_ingreso,id_entidad,id_cuenta_ingreso,id_cobranza,cant_pagos,ctrl_ingreso,ctrl_egreso) 
                        values 
                        ($id_datos_ingreso,$id_distrito,$id_moneda,$id_tipo_ingreso,$id_entidad,$id_cuenta_ingreso,$id_cobranza,$cant_pagos,0,0)";
              $sql[]="update cobranzas set id_datos_ingreso=$id_datos_ingreso where id_cobranza=$id_cobranza";
         }
        sql($sql) or fin_pagina();
        
        if ($db->CompleteTrans()) 
             return $id_datos_ingreso;
} //fin datos_ingresos

//guarda los detalles de los ingresos de una venta de una sola factura
function guardar_detalle_ingresos_vta($i,$id_datos_ingreso,$ing=0,$id_detalle_ingreso="") {
global $db;
$sql_detalle="";

  $monto_ing=$_POST["parcial_".$i];
  $fecha_ing=fecha_db($_POST["fecha_".$i]);
  $comentarios=$_POST["comentarios_".$i];
    
  if ($_POST["dolaractual_".$i])
              $dolar=$_POST["dolaractual_".$i];
             else 
              $dolar=0;
  
$db->StartTrans();
  
  if ($ing==1 && $id_detalle_ingreso!="") {  //cuando se invoca desde el boton ingreso y el detalle ya estaba guardado
       $sql_detalle[]="update detalle_ingresos set  fecha_ingreso='$fecha_ing',
       monto_ingreso=$monto_ing,comentarios='$comentarios',dolar_ingreso=$dolar where id_detalle_ingreso=$id_detalle_ingreso";
       }
  else {
       $query="select nextval('detalle_ingresos_id_detalle_ingreso_seq') as id_detalle_ingreso";
       $res_id=sql($query) or fin_pagina();
       $id_detalle_ingreso=$res_id->fields['id_detalle_ingreso'];	
       $sql_detalle[]="insert into detalle_ingresos 
                       (id_detalle_ingreso,fecha_ingreso,monto_ingreso,comentarios,dolar_ingreso)  values 
                       ($id_detalle_ingreso,'$fecha_ing',$monto_ing,'$comentarios',$dolar)";
       $sql_detalle[]="insert into pagos_ingreso (id_detalle_ingreso,id_datos_ingreso) values
                     ($id_detalle_ingreso,$id_datos_ingreso)";
  }
  
if ($sql_detalle!="") 
           sql($sql_detalle,"Error en guardar detalle ingresos vta  $sql_detalle") or fin_pagina();
           
$db->CompleteTrans();  

return $id_detalle_ingreso;     
}  //fin guardar_detalle_ingresos_vta


/******************************* finalizar_vta_factura ************************/
/* finaliza la venta de factura de bancos
cero indica que no esta atada en seguimiento de cobros
uno indica que esta atada en seguimiento de cobros*/
function finalizar_vta_factura ($id_cobranza,$atada) {
	global $_ses_user;
	$usuario=$_ses_user['login'];
    $fecha=date("Y-m-d H:i:s");
   
if ($atada==0) { //una sola factura
  //ver si estan atadas en bancos.facturas_venta_lista
  //obtengo id_venta_factura
  $sql="select id_venta_factura,id_factura 
        from bancos.facturas_venta_lista
        join licitaciones.cobranzas 
        using (id_factura)
        where id_cobranza=$id_cobranza";
  $res=sql($sql,"$sql") or fin_pagina();
  $id_venta_factura=$res->fields['id_venta_factura'];
  
  if ($id_venta_factura) {
  $sql_ventas="select id_venta_factura 
               from bancos.facturas_venta_lista
               where id_venta_factura=$id_venta_factura";
  
  $res_ventas=sql($sql_ventas,"$sql_ventas") or fin_pagina();
  
  
  if ($res_ventas->recordCount() == 1) {  //una sola factura en venta factura de Banco
    $sql="update bancos.facturas_venta set estado_venta=0,usuario_cerrador='$usuario',fecha_cierre='$fecha'
          where id_venta_factura=$id_venta_factura";
    sql($sql,"$sql") or fin_pagina();
  }
  elseif($res_ventas->recordCount() > 1) {  //mas de una factura en venta factura de Banco
    $id_factura=$res->fields['id_factura'];
    $sql="update bancos.facturas_venta_lista set finalizada_en_seguimiento=1
          where id_factura=$id_factura and id_venta_factura=$id_venta_factura";
    sql($sql,"$sql") or fin_pagina();
  }
    $sql="select id_factura from bancos.facturas_venta_lista
          where id_venta_factura=$id_venta_factura and finalizada_en_seguimiento=1";
    $res=sql($sql,"sql") or fin_pagina();
    
    if ($res->RecordCount()== $res_ventas->recordCount()) { //si estan todas 
      $sql="update bancos.facturas_venta set estado_venta=0,usuario_cerrador='$usuario',fecha_cierre='$fecha'
            where id_venta_factura=$id_venta_factura";
      sql($sql,"$sql") or fin_pagina();
      $res=sql($sql,"sql") or fin_pagina();
    }
  }
}
else { //si esta atada en seguimiento tambien esta atada en venta de facturas
 $sql="select id_venta_factura,id_factura 
        from bancos.facturas_venta_lista
        join licitaciones.cobranzas 
        using (id_factura)
        where id_cobranza=$id_cobranza";
  $res=sql($sql,"$sql") or fin_pagina();
  $id_venta_factura=$res->fields['id_venta_factura'];

 																																																																																											
  if ($id_venta_factura) {
    $sql_update="update bancos.facturas_venta set estado_venta=0,usuario_cerrador='$usuario',fecha_cierre='$fecha'
          where id_venta_factura=$id_venta_factura";
    
    sql($sql_update,"EN ELSE $sql_up") or fin_pagina();
  }
}
}

?>