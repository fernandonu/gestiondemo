<?php
/*
$Author: ferni $
$Revision: 1.3 $
$Date: 2006/03/16 22:52:18 $
*/
require_once("../../config.php");

$id_deposito=$parametros["id_deposito"];
$deposito="San Luis - Incorriente";
$pagina_listado="stock_sl_incorriente.php";
if ($id_deposito=="") {
					   $sql="select id_deposito from depositos where nombre='San Luis - Incorriente'";
					   $resultado=$db->execute($sql) or die ($db->errormsg()."<br>".$sql);
					   $id_deposito=$resultado->fields["id_deposito"];
					   }
					   
$_ses_stock["id_deposito"]=$id_deposito;
$_ses_stock["deposito"]=$deposito;
$_ses_stock["pagina_listado"]=$pagina_listado;				
phpss_svars_set("_ses_stock", $_ses_stock);

include("listado_depositos.php");
?>