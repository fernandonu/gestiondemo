<?php
/*
$Author: marco_canderle $
$Revision: 1.4 $
$Date: 2004/07/01 15:25:17 $
*/
require_once("../../config.php");

$id_deposito=$parametros["id_deposito"];
$deposito="New Tree";
$pagina_listado="stock_new_tree.php";
if ($id_deposito=="") {
					   $sql="select id_deposito from depositos where nombre='New Tree'";
					   $resultado=$db->execute($sql) or die ($db->errormsg()."<br>".$sql);
					   $id_deposito=$resultado->fields["id_deposito"];
					   }
phpss_svars_set("_ses_id_deposito", $id_deposito);
phpss_svars_set("_ses_deposito", $deposito);
phpss_svars_set("_ses_pagina_listado",$pagina_listado);
phpss_svars_set("_ses_es_inventario",0);
include("listado_depositos.php");
?>