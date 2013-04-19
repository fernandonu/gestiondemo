<?

if (!isset($_POST["ID"])) {
	echo "Imposible ver los reportes, no se especifico el identificador del grupo de reportes.";
	exit;	
}
require_once("datos_server_quemado.php");


$sql = 'select * from ordenes.reporte_detalle where id_reporte ='.$_POST['ID'];

$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);


$cant = $resultado->RecordCount();
echo '#';
echo $cant;
echo '|';
for($i=0;$i < $cant; $i++) {
	echo $resultado->fields['detalle'];
	echo '|';
	$resultado->MoveNext();
}


?>