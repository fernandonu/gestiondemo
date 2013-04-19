<?
if ((!isset($_POST["MAC"])) || (!isset($_POST["DISCOS"])) || (!isset($_POST["CPU"])) 
|| (!isset($_POST["PLACABASE"])) || (!isset($_POST["NORDEN"]))) {
	echo "&Error en parametros de entrada";
	exit;	
}

require_once("datos_server_quemado.php");

$file = './log.txt';

$open = fopen($file, 'a+');

  fwrite($open,"[".date("Y-m-d h:i:s")."] Se conecto:" . $_POST["MAC"] ."\r");

fclose($open);

//devuelvo el md5 de la identificación.
$hash = md5($_POST["MAC"].$_POST["DISCOS"].$_POST["CPU"].$_POST["PLACABASE"]);


//consulta por si la maquina ya fue quemada
$sql_report = "select * from ordenes.reportes where resultado = 'TRUE' and nro_serie = '".$hash."'";
$resultado=$db->Execute($sql_report) or die($db->ErrorMsg()."<br>".$sql_report);

if ($resultado->RecordCount() > 0) {//caso en que ya se quemo la maquina
	echo "$";
	exit;
}

$status = "#";

//consulta por si quema con componentes repetidos con otras maquinas ya quemadas
$sql_report1 = "select * from ordenes.reportes where resultado = 'TRUE' and 
mac = '".$_POST["MAC"]."' and disco = '".$_POST["DISCOS"]."'";

$resultado=$db->Execute($sql_report1) or die($db->ErrorMsg()."<br>".$sql_report1);

if ($resultado->RecordCount() > 0) {//caso en que ya se quemo la maquina
	$status = "&";
}



$db->StartTrans();
//caso en que quema una nueva maquina
$sql_next ="Select nextval('ordenes.reportes_id_reporte_seq') as id_reporte";
$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);

$id_reporte = $resultado->fields['id_reporte'];

$sql_insert1 = "Insert into ordenes.reportes (id_reporte,nro_serie,mac,disco,cpu,placabase) values 
(".$id_reporte.",'".$hash."','".$_POST["MAC"]."','".$_POST["DISCOS"]."','".$_POST["CPU"]."','".$_POST["PLACABASE"]."')";

$resultado=$db->Execute($sql_insert1) or die($db->ErrorMsg()."<br>".$sql_insert1);

//relaciono los reportes a las ordenes
$sql_insert2 = "Insert into ordenes.reporteorden(id_orden,id_reporte) values 
(".$_POST["NORDEN"].",".$id_reporte.")";

$resultado=$db->Execute($sql_insert2) or die($db->ErrorMsg()."<br>".$sql_insert2);


//registro la fecha de comienzo de quemado para la primera vez que quemo una maquina de esa orden
$sql_insert3 = "Update ordenes.orden_quemado set fecha_ini_quemado = '".date("Y-m-d h:i:s")."' 
where fecha_ini_quemado is null and nro_orden = ".$_POST["NORDEN"]; 

$resultado=$db->Execute($sql_insert3) or die($db->ErrorMsg()."<br>".$sql_insert3);

$db->CompleteTrans();

//retorno id de reporte
echo $status.$id_reporte;



?>