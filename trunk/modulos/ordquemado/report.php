<?
if ((!isset($_POST["IDREPORTE"])) || (!isset($_POST["NREPORTE"])) || (!isset($_POST["DETALLE"]))) {
	echo "&Error en parametros de entrada";
	exit;	
}

require_once("datos_server_quemado.php");


$sql_insert1 = "Insert into ordenes.reporte_detalle (id_reporte,num_rep,detalle) values 
(".$_POST["IDREPORTE"].",".$_POST["NREPORTE"].",'".$_POST["DETALLE"]."')";

$resultado=$db->Execute($sql_insert1) or die($db->ErrorMsg()."<br>".$sql_insert1);

Echo "#Ok recibido..";
?>