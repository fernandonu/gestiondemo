<?php

require_once("datos_server_quemado.php");

$sql_info = "Select id_ensamblador,nombre,direccion,tel,email from ordenes.Ensamblador 
where id_ensamblador =" . $id_ensamblador;

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


$sql_ordenes = "Select nro_orden,maq_quemadas,fecha_orden,cantidad from ordenes.orden_quemado 
join (select nro_orden as nro_orden1,id_ensamblador,cantidad from ordenes.orden_de_produccion where 
id_ensamblador =".$id_ensamblador.") as temp1 on ordenes.orden_quemado.nro_orden = temp1.nro_orden1
 where maq_quemadas < temp1.cantidad";

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