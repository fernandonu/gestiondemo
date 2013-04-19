<?
if ((!isset($_POST["IDREPORTE"]))|| (!isset($_POST["NORDEN"]))|| (!isset($_POST["ESTADO"]))) {
	echo "&Error en parametros de entrada";
	exit;	
}

require_once("datos_server_quemado.php");


//marco el fin de quemado de la orden total
// esto es posible porque cada maquina guarda su fin, por tanto
// quedara la ultima
if ($_POST["ESTADO"] == "Quemado OK...") {
	
	//set estado de reporte a verdadero	
	$sql_update = "update ordenes.reportes set resultado = 'true' where id_reporte =".$_POST["IDREPORTE"];
	$resultado=$db->Execute($sql_update) or die($db->ErrorMsg()."<br>".$sql_update);
	
	$sql_update1 = "update ordenes.orden_quemado set fecha_fin_quemado = '".date("Y-m-d h:i:s")."',
 	maq_quemadas = (maq_quemadas + 1) where nro_orden =".$_POST["NORDEN"];

} else {//si el quemado no es Ok no incremento numero de quemadas
	$sql_update1 = "update ordenes.orden_quemado set fecha_fin_quemado = '".date("Y-m-d h:i:s")."'
 	where nro_orden =".$_POST["NORDEN"];
}

$resultado=$db->Execute($sql_update1) or die($db->ErrorMsg()."<br>".$sql_update1);


Echo "#Ok recibido..";
?>