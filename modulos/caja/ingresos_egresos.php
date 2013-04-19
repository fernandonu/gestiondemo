<?php
/*
$Author: fernando $
$Revision: 1.26 $
$Date: 2007/02/22 17:08:47 $
*/

/* Esta pagina es referenciada desde:

	 licitaciones/lic_cobranza.php
	 licitaciones/elegir_caja.php
	 caja/listado.php
	 caja/func.php
	 ord_compra/ord_compra_pagar.php
	 ord_compra/ord_compra_conf_pago.php
	 ord_pago/ord_pago_pagar.php
	 ord_pago/ord_pago_conf_pago.php
	 personal/pago_sueldo.php

*/

require_once("../../config.php");
variables_form_busqueda("ingreso_egreso");


//si viene de Ordenes de pago incluir este
if ($parametros["pagina_viene"]=="ord_pago")
	require_once("../ord_pago/fns.php");
else
	require_once("../ord_compra/fns.php");

require_once("../contabilidad/funciones.php");
require_once("func.php");

//$distrito=$parametros['distrito'] or $distrito=$_POST['distrito'];
//id=1 es San Luis


$distrito=$_GET['distrito'] or $distrito=$parametros['distrito'] or die("No se indico el distrito");//tiene que venir si o si

switch ($distrito)
{
	case 1: define("CAJA","San Luis");
					$nbre_distrito="San Luis";
					break;
 	case 2:	define("CAJA","Buenos Aires");
				 	$nbre_distrito="Buenos Aires - GCBA";
				 	break;
	default: die("No se indico el distrito");
}

$query="select id_distrito from distrito where nombre='$nbre_distrito'";
$dist_nbre=$db->Execute($query) or die($db->ErrorMsg());
$distrito=$dist_nbre->fields['id_distrito'];

$pagina_viene=$parametros['pagina_viene'] or $pagina_viene=$_POST['pagina_viene'];
$id_sueldo=$parametros['id_sueldo'] or $id_sueldo=$_POST['id_sueldo'];
$id_cobranza=$parametros['id_cobranza'] or $id_cobranza=$_POST['id_cobranza'];
$id_cob=$parametros['id_cob'] or $id_cob=$_POST['id_cob'];
$nro_factura=$parametros['nro_factura'] or $nro_factura=$_POST['nro_factura'];
$id_licitacion=$parametros['id_licitacion'] or $id_licitacion=$_POST['id_licitacion'];
$cotizacion_dolar=$parametros['cotizacion_dolar'] or $cotizacion_dolar=$_POST['cotizacion_dolar'];
$dolar_actual=$parametros['dolar_actual'] or $dolar_actual=$_POST['dolar_actual'];

if($parametros["pagina"]=="egreso")
{$cmd="egresos";
 phpss_svars_set("_ses_ingreso_egreso_cmd", $cmd);
}
elseif ($cmd == "" || $parametros["pagina"]=="ingreso") {
	$cmd="ingresos";
	phpss_svars_set("_ses_ingreso_egreso_cmd", $cmd);
}
elseif ($cmd == "")
{
    $cmd="ingresos";
    phpss_svars_set("_ses_ingreso_egreso_cmd", $cmd);
}


  if($cmd=="egresos")
		$in="egreso";
  elseif($cmd=="ingresos")
		$in="ingreso";

	$link=encode_link("ingresos_egresos.php",array("id_ingreso_egreso"=> $parametros["id_ingreso_egreso"],"pagina"=>$in,"cuotas"=>$parametros['cuotas'],
	                  "cuentas"=>$parametros['cuentas'],"cantidad_cuentas"=>$parametros['cantidad_cuentas'],"distrito"=>$distrito));

   if ($parametros["pagina_viene"]=="orden_de_compra")
	 {
	  $link=encode_link("./ingresos_egresos.php",
	   array("id_ingreso_egreso"=> $parametros["id_ingreso_egreso"],
			 "pagina"=>"egreso",
			 "pagina_viene"=>"orden_de_compra",
			 "nro_orden"=>$parametros['nro_orden'],
			 "valor_dolar"=>$parametros['valor_dolar'],
			 "id_pago"=>$parametros['id_pago'],
			 "distrito"=>$distrito));
	  }
	 elseif ($parametros["pagina_viene"]=="ord_pago")
	 {
	  $link=encode_link("./ingresos_egresos.php",
	   array("id_ingreso_egreso"=> $parametros["id_ingreso_egreso"],
			 "pagina"=>"egreso",
			 "pagina_viene"=>"ord_pago",
			 "nro_orden"=>$parametros['nro_orden'],
			 "valor_dolar"=>$parametros['valor_dolar'],
			 "id_pago"=>$parametros['id_pago'],
			 "distrito"=>$distrito));
	  }

if($_POST['Guardar']=="Guardar")
{
	$db->StartTrans();
	$pagina="";
	$id=$_POST["id_ingreso_egreso"];
	$tipo_pago=$_POST["tipo_pago"];
	if($id)
	 $state_guardar=1;

 	$stay=0;//se modifica dentro de guardar_ie

  if($cmd=="egresos")
   	$state_guardar=guardar_ie("egreso",$distrito);
  elseif($cmd=="ingresos")
		$state_guardar=guardar_ie("ingreso",$distrito);

	if (!$stay)
 	{
 		if ($parametros['pagina_viene']=="orden_de_compra") {
			  $id_pago=$parametros["id_pago"];
			  $sql="select monto from ordenes_pagos where id_pago=$id_pago";
			  $resultado=$db->execute($sql) or die($sql);
			  $monto=$resultado->fields['monto'];
			  $nro_orden=$parametros["nro_orden"];
			  $sql="select id_proveedor from orden_de_compra join general.proveedor using (id_proveedor) where nro_orden=$nro_orden";
			  $resultado=$db->execute($sql) or die($sql);
			  $id_proveedor=$resultado->fields['id_proveedor'];

			  //$select_proveedor=$_POST["select_proveedor"];
			  $select_proveedor=$id_proveedor;
			  $select_tipo=$_POST['select_tipo'];
			  //$select_concepto=$_POST['select_concepto'];
			  //$select_plan=$_POST['select_plan'];
			  $select_moneda=$_POST["select_moneda"];
			  $text_fecha=$_POST["text_fecha"];
			  $monto_mostrar=$_POST["text_monto"];
			  $text_monto=$monto;
			  $text_item=$_POST["text_item"];
			  $observaciones=$_POST["observaciones"];
			  $cuentas=$_POST['cuentas'];
			  $id_proveedor_pago=$_POST["id_proveedor_pago"];
			  if ($select_moneda==""){
									  $select_moneda=retornar_moneda("Pesos");
									  }
			  $sql="Select simbolo from moneda where id_moneda=$select_moneda";
			  $resultado=sql($sql) or die();
			  $simbolo=$resultado->fields['simbolo'];

			  //generamos el arreglo para retener los datos de imputacion, y lo enviamos por parametro
			  //Asi podremos guardarlos correctamente
              $valores_imputacion=retener_datos_imputacion();

			  $link=encode_link("../ord_compra/ord_compra_conf_pago.php",array(
						   "pagina"=>$parametros['pagina'],
						   "nro_orden"=>$parametros['nro_orden'],
						   "valor_dolar"=>$parametros['valor_dolar'],
						   "pagina_pago"=>"efectivo",
						   "id_pago"=>$parametros['id_pago'],
						   "select_proveedor"=>$select_proveedor,
						   "id_proveedor_pago"=>$id_proveedor_pago,
						   "select_tipo"=>$select_tipo,
						   "select_concepto"=>$select_concepto,
						   "select_plan"=>$select_plan,
						   "select_moneda"=>$select_moneda,
						   "simbolo"=>$simbolo,
						   "text_fecha"=>$text_fecha,
						   "monto_mostrar"=>$monto_mostrar,
						   "text_monto"=>$text_monto,
						   "text_item"=>$text_item,
						   "id_distrito"=>$distrito,
						   "observaciones"=>$observaciones,
						   "cuentas"=>$cuentas,
						   "idbanco"=>$_POST['idbanco'],
						   "idtipodepbanco"=>$_POST['select_tipodep_banco'],
						   "volver"=>"ingresos_egresos.php",
						   "valores_imputacion"=>$valores_imputacion
						   ));
						   header("location:$link");
		}
		elseif ($parametros['pagina_viene']=="ord_pago") {
			  $id_pago=$parametros["id_pago"];
			  $sql="select monto from ordenes_pagos where id_pago=$id_pago";
			  $resultado=$db->execute($sql) or die($sql);
			  $monto=$resultado->fields['monto'];
			  $nro_orden=$parametros["nro_orden"];
			  $sql="select id_proveedor from orden_de_compra join general.proveedor using (id_proveedor) where nro_orden=$nro_orden";
			  $resultado=$db->execute($sql) or die($sql);
			  $id_proveedor=$resultado->fields['id_proveedor'];

			  //$select_proveedor=$_POST["select_proveedor"];
			  $select_proveedor=$id_proveedor;
			  $select_tipo=$_POST['select_tipo'];
			  //$select_concepto=$_POST['select_concepto'];
			  //$select_plan=$_POST['select_plan'];
			  $select_moneda=$_POST["select_moneda"];
			  $text_fecha=$_POST["text_fecha"];
			  $monto_mostrar=$_POST["text_monto"];
			  $text_monto=$monto;
			  $text_item=$_POST["text_item"];
			  $observaciones=$_POST["observaciones"];
			  $cuentas=$_POST['cuentas'];
			  $id_proveedor_pago=$_POST["id_proveedor_pago"];
			  if ($select_moneda==""){
									  $select_moneda=retornar_moneda("Pesos");
									  }
			  $sql="Select simbolo from moneda where id_moneda=$select_moneda";
			  $resultado=sql($sql) or die();
			  $simbolo=$resultado->fields['simbolo'];

			   //generamos el arreglo para retener los datos de imputacion, y lo enviamos por parametro
			  //Asi podremos guardarlos correctamente
              $valores_imputacion=retener_datos_imputacion();


			  $link=encode_link("../ord_pago/ord_pago_conf_pago.php",array(
						   "pagina"=>$parametros['pagina'],
						   "nro_orden"=>$parametros['nro_orden'],
						   "valor_dolar"=>$parametros['valor_dolar'],
						   "pagina_pago"=>"efectivo",
						   "id_pago"=>$parametros['id_pago'],
						   "select_proveedor"=>$select_proveedor,
						   "id_proveedor_pago"=>$id_proveedor_pago,
						   "select_tipo"=>$select_tipo,
						   "select_concepto"=>$select_concepto,
						   "select_plan"=>$select_plan,
						   "select_moneda"=>$select_moneda,
						   "simbolo"=>$simbolo,
						   "text_fecha"=>$text_fecha,
						   "monto_mostrar"=>$monto_mostrar,
						   "text_monto"=>$text_monto,
						   "text_item"=>$text_item,
						   "id_distrito"=>$distrito,
						   "observaciones"=>$observaciones,
						   "cuentas"=>$cuentas,
						   "idbanco"=>$_POST['idbanco'],
						   "idtipodepbanco"=>$_POST['select_tipodep_banco'],
						   "volver"=>"ingresos_egresos.php",
						   "valores_imputacion"=>$valores_imputacion
						   ));
						   header("location:$link");
		}
 }//de if (!$stay)
 else
 	$link=encode_link($_SERVER['SCRIPT_NAME'],$parametros_new=array_merge($parametros,array("idbanco"=>$_POST['idbanco'],"idtipodepbanco"=>$_POST['select_tipodep_banco'])));

 if($reenviar_por_imputacion)
 {
  $link_ingresos_egresos=encode_link("ingresos_egresos.php",array("pagina"=>$cmd,"distrito"=>$distrito));
  ?>
  <script>
   document.location.href='<?=$link_ingresos_egresos?>';
  </script>
  <?
 }

 $db->CompleteTrans();
}//de if($_POST['Guardar']=="Guardar")

function tabla_filtros_nombres($link,$ie)
{

if($ie=='egreso')
	$color="#BA0105";


 $abc=array("a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","ñ","o","p","q",
			"r","s","t","u","v","w","x","y","z");

$cantidad=count($abc);

if ($ie=='ingreso') echo "<table align='center' width='80%' height='80%' id=mo>";
if ($ie=='egreso') echo "<table align='center' width='80%'  height='80%' bgcolor='$color'>";
echo "<input type=hidden name='filtro' value=''";
	echo "<tr>";
	for($i=0;$i<$cantidad;$i++)
	{
		$letra=$abc[$i];
	   switch ($i)
	   {
			 case 9:
			 case 18:
			 case 27:echo "</tr><tr>";
			 break;
	   } //del switch

		if ($ie=='ingreso')
		{?>
		 <td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';if(document.all.editar.value=='ok')document.all.postear.value='sipok';else document.all.postear.value='sip'; document.form1.submit();"><?=$letra?></td>
		<?
		}
		if ($ie=='egreso')
		{?>
		<td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';if(document.all.editar.value=='ok')document.all.postear.value='sipok';else document.all.postear.value='sip'; document.form1.submit();"><font color='#FDF2F3'><b><?=$letra?></b></font></td>
		<?}
	}//del for

  echo "</tr>";
  echo "<tr>";
  if($ie=='ingreso')
  {
		echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='%';if(document.all.editar.value=='ok'){document.all.postear.value='sipok';}else {document.all.postear.value='sip';}document.form1.submit();\"> Todos";
		echo "</td>";
	}
  elseif($ie=='egreso') {
   echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='%';if(document.all.editar.value=='ok'){document.all.postear.value='sipok';}else {document.all.postear.value='sip';} document.form1.submit();\"><font color='#FDF2F3'><b> Todos</b></font>";
   echo "</td>";
  }
   echo "</tr>";
   echo "</table>";
}  //de la funcion  para las letras en la lista de entidades y proveedores

$datos_barra = array(
                    array(
                        "descripcion"    => "Ingresos",
                        "cmd"            => "ingresos",
                        "extra"         => array('distrito'=>$distrito)
                        ),
                    array(
                        "descripcion"    => "Egresos",
                        "cmd"            => "egresos",
                        "extra"         => array('distrito'=>$distrito)
                        )
				 );?>
<div align="right">
  <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/caja/ayuda_ing_eg.htm" ?>', 'INGRESO/EGRESO <?=strtoupper(CAJA)?>')"; >
</div>

<?
generar_barra_nav($datos_barra);

if($parametros['id_ingreso_egreso']!="" && $_POST['postear']=="") {

	   $id=$parametros['id_ingreso_egreso'];
		$pagina="listado";
		$campos=" ingreso_egreso.monto,ingreso_egreso.comentarios,";
		$campos.=" ingreso_egreso.item,ingreso_egreso.id_entidad,ingreso_egreso.id_proveedor, ";
		$campos.=" ingreso_egreso.id_tipo_ingreso,ingreso_egreso.id_tipo_egreso,caja.fecha, ";
		$campos.=" caja.id_moneda,caja.id_distrito,caja.cerrada,ingreso_egreso.numero_cuenta,";
		$campos.=" ingreso_egreso.id_cuenta_ingreso,ingreso_egreso.numero_cuenta ";
		$query="SELECT $campos from ingreso_egreso join caja using(id_caja) WHERE id_ingreso_egreso=$id";
		$resultados=$db->Execute($query) or die($db->ErrorMsg().$query);
		$fecha=$resultados->fields['fecha'];
		$caja_actual_cerrada=$resultados->fields['cerrada'];
		$monto=$resultados->fields['monto'];
		$item=$resultados->fields['item'];
		$moneda=$resultados->fields['id_moneda'];
		$distrito=$resultados->fields['id_distrito'];
		$tipo_ingreso=$resultados->fields['id_tipo_ingreso'];
		$tipo_egreso=$resultados->fields['id_tipo_egreso'];
		$comentarios=$resultados->fields['comentarios'];
		$entidad=$resultados->fields['id_entidad'];
		$proveedor=$resultados->fields['id_proveedor'];
		$tipo_cuenta=$resultados->fields['id_cuenta_ingreso'];
		//$_POST['idbanco']=$resultados->fields['idbanco'];
		//$_POST['select_tipodep_banco']=$resultados->fields['idtipo'];

		if($parametros['pagina']=="ingreso")
		{$query="select nombre from entidad where id_entidad=$entidad";
		 $nombre=$db->Execute($query) or die ($db->ErrorMsg()." nombre entidad");
		 $letra=substr($nombre->fields['nombre'],0,1);

		}
		if($parametros['pagina']=="egreso")
		{
		 $query="select razon_social from proveedor where id_proveedor=$proveedor";
		 $nombre=$db->Execute($query) or die ($db->ErrorMsg()." nombre proveedor");
		 $letra=substr($nombre->fields['razon_social'],0,1);

		 //$query_cuenta="select concepto,plan from tipo_cuenta where numero_cuenta=".
		 $cuenta=$resultados->fields['numero_cuenta'];
		 //$cuenta_res=$db->Execute($query_cuenta) or die($db->ErrorMsg().$query_cuenta);

		 //$concepto_cuenta=$cuenta_res->fields['concepto'];
		 //$plan_cuenta=$cuenta_res->fields['plan'];
		}

	   $state_guardar=1;

}
////Meter codigo Diego Broggi//////////////////
elseif ($parametros['pagina_viene']=="pago_sueldo")
 {$fecha=date("d/m/Y");
  $monto=$parametros['bolsillo'];//pasa el monto a descontar de la caja, esto es lo que le queda de bolsillo al empleado
  $item="Cancelación sueldo mes ".armo_fecha($parametros['mes'])." de ".$parametros['anio'];
  $sql="select * from licitaciones.moneda where nombre='Pesos'";
  $resul=sql($sql) or fin_pagina();
  $moneda=$resul->fields['id_moneda'];
  $sql="select * from caja.tipo_egreso where nombre='Sueldos'";
  $resul=sql($sql) or fin_pagina();
  $tipo_egreso=$resul->fields['id_tipo_egreso']; //Seleciono tipo de egreso Sueldos
  $sql="select * from general.proveedor where razon_social='SUELDOS'";
  $resul=sql($sql) or fin_pagina();
  $proveedor=$resul->fields['id_proveedor'];
  $sql="select * from general.tipo_cuenta where concepto='Personal' and plan='Remuneraciones'";
  $resul=sql($sql) or fin_pagina();
  $numero_cuenta=$resul->fields['numero_cuenta'];
  $letra='S';
}

////Hata Aca///////////////////////////////////
//TOMA LOS VALORES DE LA PAGINA lic_cobranzas
elseif ($parametros['pagina_viene']=='lic_cobranzas') {

$fecha=date("Y/m/d");

$sql_f="SELECT id_tipo_ingreso FROM caja.tipo_ingreso where nombre='Cobros'";
$res_f=sql($sql_f) or fin_pagina();
$tipo_ingreso=$res_f->fields['id_tipo_ingreso'];

$monto=$parametros['monto'];
$moneda=$parametros['id_moneda'];
$monto_factura=$parametros['monto_factura'];
$moneda_factura=$parametros['moneda_factura'];
$entidad=$parametros['id_cliente'];
$tipo_fact=strtoupper($parametros['tipo_fact']);
if ($entidad != null || $entidad != "") {
    $sql="select nombre from entidad where id_entidad=$entidad";
    $res_ent=sql($sql) or fin_pagina();
    $nombre_entidad=$res_ent->fields['nombre'];
    $letra=substr($res_ent->fields['nombre'],0,1);

}
$sql="select id_cuenta_ingreso  from caja.tipo_cuenta_ingreso where nombre ilike 'Cobros (Facturas Clientes)%' ";
$res=sql($sql) or fin_pagina ();
$tipo_cuenta=$res->fields['id_cuenta_ingreso'];
$nro_factura=$parametros['nro_factura'];
$item="F".$tipo_fact." ".$nro_factura." - ".$nombre_entidad;
$id_licitacion=$parametros['id_licitacion'];
if (es_numero($id_licitacion))
  $item.=" - ID $id_licitacion";

}
if ($parametros["pagina_viene"]=="ord_pago") {
	   $disabled_pagos="disabled";


	   $orden_compra=datos_orden_compra($parametros);
	   $id_moneda=$_POST["select_moneda"] or $id_moneda=retornar_moneda("Pesos");
	   $id_proveedor=$_POST["select_proveedor"] or $id_proveedor=$orden_compra["proveedor"];
	   $nro_orden=$orden_compra["nro_orden"];
	   //item para pagos multiples o simples
	   $ordenes_atadas=PM_ordenes($nro_orden);
	   $string_ordenes="";
	   $tam=sizeof($ordenes_atadas);
	   for($i=0;$i<$tam;$i++)
		 $string_ordenes.=" ".$ordenes_atadas[$i];

	   $item=$_POST["text_item"] or $item="Orden/es de Pago Nro: $string_ordenes";
	   if ($parametros['valor_dolar']) {
										$importe=$orden_compra["importe_dolares"];

									   }
									   else
									   {
									   $importe=$orden_compra["importe"];

									   }
	   $importe_dolares=$orden_compra["importe_dolares"];
	   $importe_pesos=$orden_compra["importe"];
	   $monto=$_POST["text_monto"] or  $monto=$importe;
	   //$id_tipo_egreso=$orden_compra["id_tipo_egreso"];
	   $id_tipo_egreso=$_POST["select_tipo"] or $id_tipo_egreso=$orden_compra["id_tipo_egreso"];;
	   $observaciones=$_POST["observaciones"];
	   $text_fecha=$_POST['text_fecha'] or $text_fecha=date("d/m/Y",mktime());
	   $numero_cuenta=$orden_compra["numero_cuenta"];
	   $valor_dolar=$orden_compra["valor_dolar"];
	   $letra=$_POST['filtro'] or $letra=$orden_compra["filtro_proveedor"];
		}   //fin del if cuando viene de orden de pago
if ($parametros["pagina_viene"]=="orden_de_compra") {
	   $disabled_pagos="disabled";

	   /*
	   $query_cuenta="select concepto,plan from tipo_cuenta where concepto='Comerciales' and plan='Mercaderias'";
	   $cuenta_res=$db->Execute($query_cuenta) or die($db->ErrorMsg().$query_cuenta);
	   $concepto_cuenta=$cuenta_res->fields['concepto'];
	   $plan_cuenta=$cuenta_res->fields['plan'];
		*/
	   $orden_compra=datos_orden_compra($parametros);
	   $id_moneda=$_POST["select_moneda"] or $id_moneda=retornar_moneda("Pesos");
	   $id_proveedor=$_POST["select_proveedor"] or $id_proveedor=$orden_compra["proveedor"];
	   $nro_orden=$orden_compra["nro_orden"];
	   //item para pagos multiples o simples
	   $ordenes_atadas=PM_ordenes($nro_orden);
	   $string_ordenes="";
	   $tam=sizeof($ordenes_atadas);
	   for($i=0;$i<$tam;$i++)
		 $string_ordenes.=" ".$ordenes_atadas[$i];

	   $item=$_POST["text_item"] or $item="Orden/es de Compra Nro: $string_ordenes";
	   if ($parametros['valor_dolar']) {
										$importe=$orden_compra["importe_dolares"];

									   }
									   else
									   {
									   $importe=$orden_compra["importe"];

									   }
	   $importe_dolares=$orden_compra["importe_dolares"];
	   $importe_pesos=$orden_compra["importe"];
	   $monto=$_POST["text_monto"] or  $monto=$importe;
	   //$id_tipo_egreso=$orden_compra["id_tipo_egreso"];
	   $id_tipo_egreso=$_POST["select_tipo"] or $id_tipo_egreso=$orden_compra["id_tipo_egreso"];;
	   $observaciones=$_POST["observaciones"];
	   $text_fecha=$_POST['text_fecha'] or $text_fecha=date("d/m/Y",mktime());
	   $numero_cuenta=$orden_compra["numero_cuenta"];
	   $valor_dolar=$orden_compra["valor_dolar"];
	   $letra=$_POST['filtro'] or $letra=$orden_compra["filtro_proveedor"];
		}   //fin del if cuando viene de orden de compra

if (!$fecha) 
  $fecha=date("Y-m-d",mktime());

echo $html_header;

?>

<body bgcolor="#E0E0E0">
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/funciones.js"></script>
<script src="../../lib/NumberFormat150.js"></script>
<SCRIPT>
//Este script aparece unicamente si la moneda es dolares
//si es pesos no puede elegir entre las monedas

function habilitar_guardar(){


if (document.all.ch_nueva_cuenta.value==0)
								 {
								 document.all.select_cuenta.disabled=true;
								 document.all.nombre_nueva_cuenta.disabled=false;
								 document.all.ch_nueva_cuenta.value=1;
								 }
								 else
								 {
                                 document.all.select_cuenta.disabled=false;
                                 document.all.nombre_nueva_cuenta.value="";
                                 document.all.nombre_nueva_cuenta.disabled=true;
                                 document.all.ch_nueva_cuenta.value=0;

                                 }

}

function conversion_monedas(importe,valor_dolar){
	//cero=pesos uno=dolares
	var moneda=(parseFloat(document.all.valor_dolar.value)==0)?0:1;

	//si selecciona pesos y la moneda es dolares
	if (document.all.select_moneda.value==1) {
		//si la moneda original es dolares
		if (moneda)
			importe.value=document.all.importe_dolares.value;
		else
			importe.value=document.all.importe_pesos.value;

  }//sino si selecciona dolares
  else if(document.all.select_moneda.value==2)
  {
		//si la moneda original es pesos
		if (moneda==0)
			importe.value=0;
		else
			importe.value=document.all.importe_pesos.value;
  }
}

function pesos_a_dolares(importe,valor_dolar){
importe.value=paseFloat(importe.value) / parseFloat(valor_dolar);
}


function control_campos()
{

 if(document.all.select_tipo.value==-1)
 {alert('Debe seleccionar un tipo de <?if($cmd=="egresos")echo "egreso";elseif($cmd=="ingresos")echo "ingreso";?>');
  return false;
 }
 if(document.all.text_fecha.value=="")
 {alert('Debe ingresar una fecha valida');
  return false;
 }
  else if ( (typeof(document.all.text_fecha.value)!='undefined') && (!es_mayor('text_fecha'))) {
  alert('Los ingresos o egresos deben tener fecha mayor o igual a la fecha de hoy');
  return false;
 }

 if(document.all.text_monto.value=="")
 {alert('Debe ingresar un monto valido');
  return false;
 }

 if(document.all.select_moneda.value==-1)
 {alert('Debe seleccionar una moneda');
  return false;
 }
 if(document.all.text_item.value=="")
 {alert('Debe ingresar un item');
  return false;
 }
 <?if($cmd=="egresos")
 {?>
  if(document.all.select_proveedor.value=="")
  {alert('Debe seleccionar un proveedor');
   return false;
  }
  if(document.all.cuentas.value==-1)
  {alert('Debe seleccionar un concepto y un plan para la cuenta');
   return false;
  }

  //controlamos los campos de imputacion
  if(!control_campos_imputacion())
   return false;

  if(parseFloat(document.all.text_monto.value)>=1000)
 {
  return confirm('<?=$_ses_user['name'];?> - Está Prohibido pagar con un monto mayor a 1000, Desea Seguir?');
 }
 <?
 }
 elseif($cmd=="ingresos")
 { ?>
  if(document.all.select_entidad.value=="")
  {alert('Debe seleccionar un cliente');
   return false;
  }
  if(document.all.ch_nueva_cuenta.value==1){
	   if(document.all.nombre_nueva_cuenta.value==""){
				 alert("Debe ingresar un Campo en Nueva Cuenta");
				 return false;
				 }
  }
  else
	 {
	  if(document.all.select_cuenta.value==-1)
		 {
		  alert('Debe seleccionar un plan para la cuenta');
		  return false;
		 }
	 }

 <?
 }//de elseif($cmd=="ingresos")
 ?>
 return true;

}//de function control_campos()

//se usa en la parte de egresos y según el proveedor elegido, si tiene
//cuenta por default, setea el select de cuentas, con la cuenta por
//default del proveedor
function seteo_cuenta(objeto)
{var tam_cuentas=document.all.cuentas.options.length;
 var i;
 var indice=-1;
 if(objeto!="")//si tiene cuenta por default, seteamos el select correspondiente
 {
    //recorremos el select de cuentas para ver cuál es la posicion de la
    //cuenta que es default del proveedor elegido. Asi seteamos con ese
    //indice al select de cuentas
    for(i=0;i<tam_cuentas;i++)
    {
     if(document.all.cuentas.options[i].value==objeto)
     {indice=i;
      i=tam_cuentas;
     }
    }

 	document.all.cuentas.selectedIndex=indice;
 }
 if(indice==-1)
  document.all.cuentas.selectedIndex=0;

}//de function seteo_cuenta(objeto)

</SCRIPT>


<form name="form1" method="POST" action="<?=$link?>">
<?

   if ($_POST['state_guardar']) {
       $state_guardar=$_POST['state_guardar'];
    }
    if ($_POST['id']) {
       $id=$_POST['id'];
    }


?>
<input type="hidden" name="tipo_pago" value="id_ingreso_egreso">
<input type="hidden" name="caja_actual_cerrada" value="<?=$caja_actual_cerrada?>">
<input type="hidden" name="state_guardar" value="<?=$state_guardar?>">
<input type="hidden" name="id" value="<?=$id?>">
<input type='hidden' name='editar' value='<?if(($parametros['id_ingreso_egreso']!="" || $_POST["postear"]=="sipok")&& $state_guardar==1) echo "ok";?>'>

<input type="hidden" name="id_ingreso_egreso" value="<?=$id?>">
<input type="hidden" name="valor_dolar" value="<?=$valor_dolar?>">
<input type="hidden" name="importe_pesos" value="<?=$importe_pesos?>">
<input type="hidden" name="importe_dolares" value="<?=$importe_dolares?>">
<?
if ($_POST["moneda"]) $value=$_POST["moneda"];
					  else $value=0;
?>
<input type="hidden" name="moneda"  value="<?=$value?>">

<?php

if($letra!="")
 $filtro=$letra;
elseif($_POST['filtro']=="")
 $filtro="a";
elseif($_POST['filtro']=="todos")
 $filtro="";
else
 $filtro=$_POST['filtro'];


 if($cmd=="egresos")
 {$titulo_principal="EGRESOS - CAJA ".strtoupper(CAJA);
  $param1="EGRESO";
  $param2="PROVEEDORES";
  $param3="#BA0105";
 }
 elseif($cmd="ingresos")
 {$titulo_principal="INGRESOS - CAJA ".strtoupper(CAJA);
  $param1="INGRESO";
  $param2="CLIENTES";
  $param3=$bgcolor1;
 }

echo "<br>";
 show_titulo($titulo_principal,"black");
 echo show_encabezado("INFORMACIÓN DEL $param1","$param2","$param3");
 ?>
<table width='100%'>
 <td width='60%'>
  <input type="hidden" name="es_caja" value="1">
  <?
 if($cmd=="egresos") {
  muestra_saldo ("egreso",$distrito,$param3);
  $monto_para_imputacion="";
  $id_moneda_para_imputacion="";
  show_datos("egreso");

  //revisamos si existe un id de imputacion para este pago
  if($id)
  {$query="select id_imputacion from imputacion where id_ingreso_egreso=$id";
   $imputacion=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
   $id_imputacion=$imputacion->fields["id_imputacion"];
   //traemos el simbolo de la moneda
   if($id_moneda_para_imputacion)
   {$query="select simbolo from moneda where id_moneda=$id_moneda_para_imputacion";
    $moneda_im=sql($query,"<br>Error al traer el simbolo de la moneda para imputacion<br>") or fin_pagina();
    $simbolo_moneda_para_imputacion=$moneda_im->fields["simbolo"];
   }
  }
  else
  {$id_imputacion="";
   $simbolo_moneda_para_imputacion="";
  }
  $reset_imputacion=0;
  //si este campo esta vacio implica que no se debe mostrar nada en la tabla imputacion
  if($_POST["editar"]=="" && $_POST["postear"]=="" && $parametros["pagina_viene"]=="" && $parametros["id_ingreso_egreso"]=="")
  {
  	$id_imputacion="";
  	$reset_imputacion=1;
  }

  tabla_imputacion($id_imputacion,$monto_para_imputacion,$simbolo_moneda_para_imputacion,$reset_imputacion,$valor_dolar);
 }
 elseif($cmd=="ingresos") {
  muestra_saldo ("ingreso",$distrito,$param3);
  show_datos("ingreso");
 }
  ?>
  <input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
 </td>
 <td width='40%'>
  <?
   if($cmd=="egresos") {
	tabla_filtros_nombres("","egreso");
	generar_parte_derecha("egreso",CAJA);
	}
   elseif($cmd=="ingresos"){

	tabla_filtros_nombres("","ingreso");
	generar_parte_derecha("ingreso",CAJA);
	}
  echo "<script>document.all.filtro.value='$filtro';</script>";
 ?>
 </td>
</table>
<?
//permisos_check("inicio","modificar_ing_egr_cobranzas")
//este permiso permite modificar los egresos/ingresos que se hicieron desde cobranzas
if ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='egreso') {
	$id_ing_det=$parametros['id_ingreso_egreso'];
$sql=" select id_ingreso_egreso from detalle_egresos where id_ingreso_egreso=$id_ing_det";
$res=sql($sql) or fin_pagina();

if ($res->RecordCount()>0 && !(permisos_check("inicio","modificar_ing_egr_cobranzas"))) $des_guardar= " disabled" ;
  else $des_guardar= "";
} elseif ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='ingreso') {
	$id_ing_det=$parametros['id_ingreso_egreso'];
	$sql=" select id_ingreso_egreso from cobranzas where id_ingreso_egreso=$id_ing_det";
    $res=sql($sql) or fin_pagina();
    if ($res->RecordCount()>0 && !(permisos_check("inicio","modificar_ing_egr_cobranzas"))) $des_guardar= " disabled" ;
       else $des_guardar= "";
}
?>
<hr>
<center>
 <input type="hidden" name="postear" value="">
 <input type="hidden" name="forcesave" value="<?=$forcesave?>">
 <input type="submit" name="Guardar" value="Guardar" style='cursor:hand' title='Presione aqui para guardar los cambios efectuados' onclick='if (control_campos())
                                                                                                                                            {if (typeof(document.all.idbanco)!="undefined" && document.all.idbanco.value!="")
                                                                                                                                              alert ("Se hará el ingreso automaticamente en el Banco elegido");
                                                                                                                                              return true
                                                                                                                                            }
                                                                                                                                            else
                                                                                                                                             return false;
                                                                                                                                           '
  <?=$des_guardar?>
 >
 
 <input type="hidden" name="pagina_viene" value="<?=$pagina_viene?>">
 <input type="hidden" name="id_sueldo" value="<?=$id_sueldo?>">
 <?

 if ($pagina_viene=='lic_cobranzas') {
 	//para controlar si se cambia algun dato de los que venian por parametros desde cobranzas
     $mto=$_POST['monto_ant'] or $mto=$monto_factura;
 	 $tipo_cta=$_POST['tipo_cuenta_ant'] or $tipo_cta=$tipo_cuenta;
     $mda=$_POST['moneda_ant'] or $mda=$moneda_factura;
     $tipo_ing=$_POST['tipo_ingreso_ant'] or $tipo_ing=$tipo_ingreso;
     $ent=$_POST['entidad_ant'] or $ent=$entidad;

     if ($id_cob != "" || $id_cob != null )
      $id_volver=$id_cob;
      else $id_volver=$id_cobranza;
     $ref = encode_link('../licitaciones/lic_cobranzas.php',array("cmd"=>'pendiente',"cmd1"=>"detalle_cobranza","id"=>$id_volver))?>

    <input type="button" name="volver_cob" value="Volver a Cobranzas" style='cursor:hand' onclick="window.opener.location.href='<?=$ref?>';window.close();"  >

   <input type="hidden" name="monto_ant" value="<?=$mto?>">  <!---monto de la factura--->
   <input type="hidden" name="tipo_cuenta_ant" value="<?=$tipo_cta?>">
   <input type="hidden" name="moneda_ant" value="<?=$mda?>"> <!--moneda de la factura -->
   <input type="hidden" name="tipo_ingreso_ant" value="<?=$tipo_ing?>">
   <input type="hidden" name="entidad_ant" value="<?=$ent?>">
   <input type="hidden" name="id_cobranza" value="<?=$id_cobranza?>">
   <input type="hidden" name="id_cob" value="<?=$id_cob?>">
   <input type="hidden" name="nro_factura" value="<?=$nro_factura?>">
   <input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
   <input type="hidden" name="cotizacion_dolar" value="<?=$cotizacion_dolar?>">
   <input type="hidden" name="dolar_actual" value="<?=$dolar_actual?>">


<?}
if ($orden_compra){
				   $page=$parametros['pagina_viene']=="ord_pago"?"../ord_pago/ord_pago_pagar.php":"../ord_compra/ord_compra_pagar.php";
				   $link=encode_link($page,array("nro_orden"=>$parametros["nro_orden"]));
				   echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='$link';\">\n";
				   }

?>
</center>
</form>
<?=fin_pagina();?>