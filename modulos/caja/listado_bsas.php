<?php
/*
$Author: marcelo $
$Revision: 1.13 $
$Date: 2004/10/04 17:06:28 $
*/

define("MAX_MONTO","9999999999");
include("func.php");

//valores por defecto
$variables=array(
	"fechas"=>"",
	"desde"=>"",
	"hasta"=>"",
	"montos"=>"",
	"desde_m"=>"0",
	"hasta_m"=>MAX_MONTO,
	"avanzada"=>"",
	"entidades"=>"",
	"orden_default"=>2,
	"orden_by"=>0
	);


variables_form_busqueda("list_caja",$variables);

if($_POST["entidades"]) $_POST['form_busqueda'] = "Buscar";
if($_POST["letras"]) $_POST['form_busqueda'] = "Buscar";

if  ($_POST['form_busqueda']) {//limpieza de campos
	if (!$_POST["avanzada"]) {
		$avanzada = "";
	} 
	if (!$_POST["fechas"]) { 
		$fechas="";
  	  	$desde="";
      	$hasta="";
	}
	if (!$_POST["montos"]) { 
      $desde_m="0";
      $hasta_m=MAX_MONTO;
      $montos="";
	}
	if (!$_POST["entidades"]) {// 
		$letra="a";
		$entidades="";
		$entidad = -1;
	} else {
		if(!$_POST["letras"]) $letra="a";
		else $letra=$_POST["letras"];
		if(!$_POST["entidades_list"]) $entidad=-1;
		else $entidad= $_POST["entidades_list"];
	}
	if(!$_POST["orden"])
		$orden_default = 2;
	else{ 
		$orden_default = $_POST["orden"];
	}

	if(!$_POST["orden_by"]){
		$orden_by=0;
	} else $orden_by = $_POST["orden_by"];
	
	
	 //limpieza de variables de seción  
	  $_ses_list_caja['hasta']="";
  	  $_ses_list_caja['desde']="";
  	  $_ses_list_caja['fechas']="";
  	  $_ses_list_caja['entidades']="";
  	  $_ses_list_caja['avanzada']="";
  	  $_ses_list_caja['montos']="";
  	  $_ses_list_caja['desde_m']="0";
  	  $_ses_list_caja['hasta_m']=MAX_MONTO;
  	  $_ses_list_caja['orden_default']=2;
  	  $_ses_list_caja['orden_by']=0;

phpss_svars_set("_ses_list_caja", $_ses_list_caja);

} 




if ($cmd == "") {
	$cmd="list_ingresos";
	phpss_svars_set("_ses_list_caja_cmd", $cmd);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Lista de Ingresos",
						"cmd"			=> "list_ingresos"
						),
					array(
						"descripcion"	=> "Lista de Egresos",
						"cmd"			=> "list_egresos"
						),
					array(
						"descripcion"	=> "Caja",
						"cmd"			=> "list_caja"
						)
				 );
generar_barra_nav($datos_barra);

$pagina="listado";
$moneda=$_POST["select_moneda"];
$id_t_cuenta = $_POST['select_t_cuenta'];
$id_t_ingreso = $_POST['select_t_ingreso'];
$id_t_egreso = $_POST['select_t_egreso'];
$estado_caja = $_POST["select_estado"];



//if ($letra == "") $letra="a";//letra por defecto para entidades

$add_where="";

if($_POST['form_busqueda']=="Buscar"){
	
	if($moneda!=-1)//filtrado por el tipo de moneda
  		$add_where.=" and id_moneda = $moneda";

  	if($cmd=="list_caja"){//si es caja
  		switch($estado_caja){//filtrado por caja abierta o cerrada
  			case 1: $add_where.=" and cerrada=0";break; 	
   			case 2: $add_where.=" and cerrada=1";break; 	
   			default:break;
  		}
 	}  	
 	if($cmd=="list_ingresos"){
		if($id_t_ingreso >= 0)//filtrado por tipo de ingreso
  			$add_where.=" and ingreso_egreso.id_tipo_ingreso = $id_t_ingreso";
		if($id_t_cuenta!=-1)//filtrado por tipo_cuenta_ingreso
  			$add_where.=" and ingreso_egreso.id_cuenta_ingreso = $id_t_cuenta"; 		
   	}
  	if($cmd=="list_egresos"){
		if($id_t_egreso >= 0) //filtrado por tipo de egreso
  			$add_where.=" and ingreso_egreso.id_tipo_egreso = $id_t_egreso"; 		
		if($id_t_cuenta!=-1)//filtrado por tipo_cuenta_egreso
  			$add_where.=" and ingreso_egreso.numero_cuenta = $id_t_cuenta"; 		
    }


	if ($fechas==1) //filtrado por fechas
		if ($desde && $hasta) {
 	  		$add_where.=" and caja.fecha >= '".fecha_db($desde)."' and caja.fecha <= '".fecha_db($hasta)."'";
 		}
 	
	if ($montos==1) //filtrado por montos
		if ($desde_m && $hasta_m) {
			if ($cmd=="list_caja")
 	  			$add_where.=" and caja.saldo_total >= '$desde_m' and caja.saldo_total <= '$hasta_m'";
			else
 	  			$add_where.=" and ingreso_egreso.monto >= '$desde_m' and ingreso_egreso.monto <= '$hasta_m'";
 	}
 	
 	if ($entidades==1){//filtrado por entidades sean proveedores en egresos o clientes en ingresos
		if ($entidad == -1) {
			if($cmd=="list_ingresos")
				$add_where.=" and id_entidad in (Select id_entidad from entidad where nombre ilike '$letra%')";  
			elseif($cmd=="list_egresos")
				$add_where.=" and id_proveedor in (Select id_proveedor from proveedor where razon_social ilike '$letra%')";  
		} else{
			if($cmd=="list_ingresos")
				$add_where.=" and id_entidad = $entidad";
			elseif($cmd=="list_egresos")
				$add_where.=" and id_proveedor = $entidad";
		}
 	}
}


echo $html_header;
?>

<form name="form1" method="post" action="listado_bsas.php">

<?
$tipo="";
if($cmd=="list_ingresos")
 $tipo="ingreso";
elseif ($cmd=="list_egresos") 
 $tipo="egreso";
elseif ($cmd=="list_caja") 
 $tipo="caja";
 
$query="select id_distrito from distrito where nombre='Buenos Aires - GCBA'";
$dist_nbre=$db->Execute($query) or die($db->ErrorMsg());
put_listado($tipo,$dist_nbre->fields['id_distrito'],$moneda);

fin_pagina();
?>
</form>
</body>
</html>
