<?php
/*
$Author: mari $
$Revision: 1.54 $
$Date: 2004/11/12 18:33:18 $
*/
require_once("func.php");
require_once("../ord_compra/fns.php");
require_once("../../config.php");

$query="select id_distrito from distrito where nombre='San Luis'";
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


if($_POST['Guardar']=="Guardar") {
	
 if ($parametros['pagina_viene']=="orden_de_compra"){
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
			  if ($select_moneda==""){
									  $select_moneda=retornar_moneda("Pesos");
									  }
			  $sql="Select simbolo from moneda where id_moneda=$select_moneda";
			  $resultado=sql($sql) or die();
			  $simbolo=$resultado->fields['simbolo'];

			  $link=encode_link("../ord_compra/ord_compra_conf_pago.php",array(
						   "pagina"=>$parametros['pagina'],
						   "nro_orden"=>$parametros['nro_orden'],
						   "valor_dolar"=>$parametros['valor_dolar'],
						   "pagina_pago"=>"efectivo",
						   "id_pago"=>$parametros['id_pago'],
						   "select_proveedor"=>$select_proveedor,
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
						   "volver"=>"ingresos_sl.php"));
						   header("location:$link");

	}
	
}




function tabla_filtros_nombres($link,$ie) {

if($ie=='egreso') {
$color="#BA0105";
}

 $abc=array("a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","ñ","o","p","q",
			"r","s","t","u","v","w","x","y","z");

$cantidad=count($abc);

if ($ie=='ingreso') echo "<table align='center' width='80%' height='80%' id=mo>";
if ($ie=='egreso') echo "<table align='center' width='80%'  height='80%' bgcolor='$color'>";
echo "<input type=hidden name='filtro' value=''";
	echo "<tr>";
	for($i=0;$i<$cantidad;$i++){
		$letra=$abc[$i];
	   switch ($i) {
					 case 9:
					 case 18:
					 case 27:echo "</tr><tr>";
						  break;
				   default:
				  } //del switch
//echo "<a id='link_load' href=$link><td style='cursor:hand' onclick=\"document.all.filtro.value='$letra'\">$letra</td></a>\n";
if ($ie=='ingreso')
{?>
 <td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';if(document.all.editar.value=='ok')document.all.postear.value='sipok';else document.all.postear.value='sip'; document.form1.submit();"><?=$letra?></td>
<?
}
if ($ie=='egreso'){?>
<td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';if(document.all.editar.value=='ok')document.all.postear.value='sipok';else document.all.postear.value='sip'; document.form1.submit();"><font color='#FDF2F3'><b><?=$letra?></b></font></td>
<?}
}//del for
   echo "</tr>";
   echo "<tr>";
   if($ie=='ingreso') {
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



variables_form_busqueda("ingreso_egreso");

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

$datos_barra = array(
                    array(
                        "descripcion"    => "Ingresos",
                        "cmd"            => "ingresos"
                        ),
                    array(
                        "descripcion"    => "Egresos",
                        "cmd"            => "egresos"
                        )
				 );
?>
<div align="right">
  <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/caja/ayuda_ing_eg.htm" ?>', 'INGRESO/EGRESO SAN LUIS')" >
</div>

<?
generar_barra_nav($datos_barra);

$pagina="";
$id=$_POST["id_ingreso_egreso"];
if($id)
 $state_guardar=1;
if($_POST['Guardar']=="Guardar") {
	
   if($cmd=="egresos"){
    $state_guardar=guardar_ie("egreso",$distrito);
    
   }
     elseif($cmd=="ingresos")
	$state_guardar=guardar_ie("ingreso",$distrito);
}



if($parametros['id_ingreso_egreso']!="" && $_POST['postear']==""){

	   $id=$parametros['id_ingreso_egreso'];
		$pagina="listado";
		$campos=" ingreso_egreso.monto,ingreso_egreso.comentarios,";
		$campos.=" ingreso_egreso.item,ingreso_egreso.id_entidad,ingreso_egreso.id_proveedor, ";
		$campos.=" ingreso_egreso.id_tipo_ingreso,ingreso_egreso.id_tipo_egreso,caja.fecha, ";
		$campos.=" caja.id_moneda,caja.id_distrito,ingreso_egreso.numero_cuenta ,ingreso_egreso.id_cuenta_ingreso, ingreso_egreso.numero_cuenta ";
		$query="SELECT $campos from ingreso_egreso join caja using(id_caja) WHERE id_ingreso_egreso=$id";
		$resultados=$db->Execute($query) or die(ErrorMsg().$query);
		$fecha=$resultados->fields['fecha'];
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
if ($entidad != null || $entidad != "") {
    $sql="select nombre from entidad where id_entidad=$entidad";
    $res_ent=sql($sql) or fin_pagina();
    $letra=substr($res_ent->fields['nombre'],0,1);
    
}
$sql="select id_cuenta_ingreso  from caja.tipo_cuenta_ingreso where nombre ilike 'Cobros (Facturas Clientes)%' "; 
$res=sql($sql) or fin_pagina ();
$tipo_cuenta=$res->fields['id_cuenta_ingreso'];
$nro_factura=$parametros['nro_factura'];
$item="COBRANZAS: FACT NRO ".$nro_factura;
$id_licitacion=$parametros['id_licitacion'];
if (es_numero($id_licitacion)) 
  $item.=" - LIC ASOCIADA $id_licitacion";
  
}

if ($parametros["pagina_viene"]=="orden_de_compra"){
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
	   $text_fecha=$_POST['text_fecha'] or $text_fecha=date("d/m/Y",mktime());;
	   $numero_cuenta=$orden_compra["numero_cuenta"];
	   $valor_dolar=$orden_compra["valor_dolar"];
	   $letra=$_POST['filtro'] or $letra=$orden_compra["filtro_proveedor"];
		}   //fin del if cuando viene de orden de compra

	

include("../ayuda/ayudas.php");
echo $html_header;
?>

<body bgcolor="#E0E0E0">
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/funciones.js"></script>
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
if (document.all.moneda.value==1) {
				document.all.moneda.value=0;
				importe.value=document.all.importe_dolares.value;
                }
                else{
                document.all.moneda.value=1;
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
  }else
	 {
	  if(document.all.select_cuenta.value==-1)
		 {
		  alert('Debe seleccionar un plan para la cuenta');
		  return false;
		 }
	 }

 <?
 }
 ?>
  
 return true;
}
</SCRIPT>
<?
  if($cmd=="egresos")
	$in="egreso";
  elseif($cmd=="ingresos")
	$in="ingreso";
	$link=encode_link("ingresos_sl.php",array("id_ingreso_egreso"=> $parametros["id_ingreso_egreso"],"pagina"=>$in,"cuotas"=>$parametros['cuotas'],
	                  "cuentas"=>$parametros['cuentas'],"cantidad_cuentas"=>$parametros['cantidad_cuentas']));


   if ($parametros["pagina_viene"]=="orden_de_compra")
	 {
	  $link=encode_link("./ingresos_sl.php",
	   array("id_ingreso_egreso"=> $parametros["id_ingreso_egreso"],
			 "pagina"=>"egreso",
			 "pagina_viene"=>"orden_de_compra",
			 "nro_orden"=>$parametros['nro_orden'],
			 "valor_dolar"=>$parametros['valor_dolar'],
			 "id_pago"=>$parametros['id_pago']));
	  }
?>
<form name="form1" method="POST" action="<?=$link?>">
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
 {$titulo_principal="EGRESOS - CAJA SAN LUIS";
  $param1="EGRESO";
  $param2="PROVEEDORES";
  $param3="#BA0105";
 }
 elseif($cmd="ingresos")
 {$titulo_principal="INGRESOS - CAJA SAN LUIS";
  $param1="INGRESO";
  $param2="CLIENTES";
  $param3=$bgcolor1;
 }

echo "<br>";
 show_titulo($titulo_principal,"San Luis");
 echo show_encabezado("INFORMACIÓN DEL $param1","$param2","$param3");
 ?>
<table width='100%'>
 <td width='60%'>
 <?
 if($cmd=="egresos") {
  muestra_saldo ("egreso",$distrito,$param3);	
  show_datos("egreso");
 }
 elseif($cmd=="ingresos") {
  muestra_saldo ("ingreso",$distrito,$param3);	
  show_datos("ingreso");
 }
  ?>
 </td>
 <td width='40%'>
  <?
   if($cmd=="egresos") {
	tabla_filtros_nombres("","egreso");
	generar_parte_derecha("egreso","San Luis");
	}
   elseif($cmd=="ingresos"){
	tabla_filtros_nombres("","ingreso");
	generar_parte_derecha("ingreso","San Luis");
	}
  echo "<script>document.all.filtro.value='$filtro';</script>";
 ?>
 </td>
</table>
<?
if ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='egreso') {
	$id_ing_det=$parametros['id_ingreso_egreso'];
$sql=" select id_ingreso_egreso from detalle_egresos where id_ingreso_egreso=$id_ing_det";
$res=sql($sql) or fin_pagina(); 
if ($res->RecordCount()>0) $des_guardar= " disabled" ;
  else $des_guardar= "";
} elseif ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='ingreso') {
	$id_ing_det=$parametros['id_ingreso_egreso'];
	$sql=" select id_ingreso_egreso from cobranzas where id_ingreso_egreso=$id_ing_det";
    $res=sql($sql) or fin_pagina(); 
   if ($res->RecordCount()>0) $des_guardar= " disabled" ;
  else $des_guardar= "";
}
?>
<hr>
<center>
 <input type="hidden" name="postear" value="">
 <input type="hidden" name="forcesave" value="<?=$forcesave?>">
 <input type="submit" name="Guardar" value="Guardar" style='cursor:hand' title='Presione aqui para guardar los cambios efectuados' onclick='return control_campos()' <?= $des_guardar?>>
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
   
 <?}?>
 

<?
if ($orden_compra){
				   $link=encode_link("../ord_compra/ord_compra_pagar.php",array("nro_orden"=>$parametros["nro_orden"]));
				   echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='$link';\">\n";
				   }

?>


</center>
</form>
<?=fin_pagina();?>