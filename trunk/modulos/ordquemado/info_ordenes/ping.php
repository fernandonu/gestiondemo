<?
if (!isset($_POST["PARAM"])) {
	echo "&Error en parametros de entrada";
	exit;	
}

require_once("datos_server_quemado.php");


//caso en que quema una nueva maquina

$sql = "Select 1";

$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);


//retorno id de reporte
echo "#Correcto...";



?>