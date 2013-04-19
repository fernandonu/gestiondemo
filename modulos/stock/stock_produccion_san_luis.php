<?php
/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2006/12/19 21:36:33 $
*/
require_once("../../config.php");

$id_deposito=$parametros["id_deposito"];
$deposito="Producción - San Luis";
$pagina_listado="stock_produccion_san_luis.php";
if ($id_deposito=="") {
					   $sql="select id_deposito from depositos where nombre='Produccion-San Luis'";
					   $resultado=sql($sql) or fin_pagina();
					   $id_deposito=$resultado->fields["id_deposito"];
					   }

$_ses_stock["id_deposito"]    = $id_deposito;
$_ses_stock["deposito"]       = $deposito;
$_ses_stock["pagina_listado"] = $pagina_listado;				
phpss_svars_set("_ses_stock", $_ses_stock);
			   
include("listado_depositos.php");
?>