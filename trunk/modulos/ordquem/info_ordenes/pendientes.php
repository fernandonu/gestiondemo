<?php

require_once("datos_server_quemado.php");

$sql_info = "Select id_ensamblador,nombre,direccion,tel,email from public.ensamblador";

$resultado=$db->Execute($sql_info) or Error($db->ErrorMsg()."<br>".$sql);
echo '#';
echo $resultado->fields['id_ensamblador'];
echo "|";
echo $resultado->fields['nombre'];
echo "|";
echo $resultado->fields['direccion'];
echo "|";
echo $resultado->fields['tel'];
echo "|";
echo $resultado->fields['email'];
echo "|";


$sql_ordenes = "Select nro_orden,maq_quemadas,fecha_orden,cantidad from public.orden_quemado 
 where maq_quemadas < cantidad";

$resultado=$db->Execute($sql_ordenes) or die($db->ErrorMsg()."<br>".$sql);

$cant = $resultado->RecordCount();

echo $cant.'|'; //tamaño

for($i=0;$i < $cant; $i++) {
	echo $resultado->fields['nro_orden'];
	echo "|";
	echo $resultado->fields['maq_quemadas'];
	echo "|";
	echo $resultado->fields['fecha_orden'];
	echo "|";
	echo $resultado->fields['cantidad'];
	echo "|"; 	
	$resultado->MoveNext();
}

?>