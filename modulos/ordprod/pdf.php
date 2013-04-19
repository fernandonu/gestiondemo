<?
require_once("../../config.php");
require('orden_produccion.php');
$query="select entidad.nombre,renglon.id_licitacion,orden_de_produccion.nserie_desde,
	orden_de_produccion.nserie_hasta,orden_de_produccion.lugar_entrega,orden_de_produccion.fecha_inicio,
	orden_de_produccion.fecha_entrega,orden_de_produccion.cantidad,ensamblador.nombre as nombre_en,
	orden_de_produccion.adicionales,orden_de_produccion.desc_prod as producto,renglon.codigo_renglon
    ,sistema_operativo.descripcion as sistema_operativo
	from orden_de_produccion left join entidad USING (id_entidad)
	left join renglon USING (id_renglon)
	left join ensamblador USING (id_ensamblador)
	left join sistema_operativo using(id_sistema_operativo)
	WHERE nro_orden=$nro_orden";
//$query="select modelo, marca, pulgadas, cliente_final.nombre,orden_de_produccion.primera_maquina,orden_de_produccion.ultima_maquina,orden_de_produccion.lugar_entrega,orden_de_produccion.fecha_inicio,orden_de_produccion.fecha_entrega,orden_de_produccion.lugar_entrega,orden_de_produccion.cantidad,ensamblador.nombre as nombre_en,configuracion_maquina.producto,configuracion_maquina.modelo,configuracion_maquina.id_configuracion,configuracion_maquina.adicionales from (monitor where monitor.nro_orden=orden_de_produccion.nro_orden and (((cliente_final join orden_de_produccion on orden_de_produccion.nro_orden=".$nro." and cliente_final.id_cliente=orden_de_produccion.id_cliente) join ensamblador on ensamblador.id_ensamblador=orden_de_produccion.id_ensamblador) join configuracion_maquina on configuracion_maquina.id_configuracion=orden_de_produccion.id_configuracion));";
$resultado1=$db->Execute($query) or die($db->errormsg() ." - $query");
$garantia=$resultado1->fields['garantia'];
$pdf=new orden_produccion();
$pdf->dibujar_planilla();
$pdf->nro_produccion("$nro_orden");
$ens=$resultado1->fields['nombre_en'];
$pdf->ensamblador("$ens");
$idlicitacion=$resultado1->fields['id_licitacion'];
$pdf->pasa_id_lic("$idlicitacion");
$c=$resultado1->fields['nombre'];
$pdf->cliente("$c");
$prod=$resultado1->fields['producto'];
$pdf->producto("$prod");
$l=$resultado1->fields['lugar_entrega'];
$pdf->lugar_entrega("$l");
$f_ini=$resultado1->fields['fecha_inicio'];
$pdf->fecha_inicio(fecha($f_ini));
$f_ent=$resultado1->fields['fecha_entrega'];
$pdf->fecha_entrega(fecha($f_ent));
//$modelo=$resultado->fields['modelo'];
//$pdf->modelo($modelo);
$cantidad=$resultado1->fields['cantidad'];
$pdf->Cantidad("$cantidad");
$pdf->Renglon($resultado1->fields['codigo_renglon']);
$pdf->sistema_operativo($resultado1->fields['sistema_operativo']);
$serialp=$resultado1->fields['nserie_desde'];
$pdf->numero_de_serie("$serialp");
$pdf->numero_de_serie("  ....");
$pdf->numero_de_serie("  ....");
$serialu=$resultado1->fields['nserie_hasta'];
$pdf->numero_de_serie("$serialu");
//$configuracion=$resultado->fields['id_configuracion'];
$query="select id_fila,productos.tipo,tipos_prod.descripcion as tipo_desc,filas_ord_prod.cantidad,filas_ord_prod.descripcion from filas_ord_prod 
	left join productos USING (id_producto)
	left join tipos_prod on (productos.tipo=tipos_prod.codigo) where nro_orden=$nro_orden order by orden";
$resultado=$db->Execute($query) or die($db->errormsg() ." - $query");
$sistema_operativo="";
$hay_modem=0;
while ($datos=$resultado->FetchRow()) {
	if ($datos["tipo"]!="monitor" && $datos["tipo"]!="garantia" && $datos["tipo"]!="kit") {
		$pdf->items($datos["cantidad"],$datos["tipo_desc"],$datos["descripcion"]);
	}
	//controles para saber si va con licencia del sistema operativo
	if ($datos["tipo"]=="sistema operativo")
	 $sistema_operativo=$datos["descripcion"];
	 
	//control para saber si va con cable de telefono(modem)
	if ($datos["tipo"]=="modem")
	 $hay_modem=$datos["descripcion"];
	
}
if ($resultado1->fields['adicionales']) $pdf->adicionales($resultado1->fields['adicionales']);
$tipo= Array(
	0=>"KB",
	1=>"Mouse",
	2=>"Parlantes",
	3=>"Microfono",
	4=>"FDD"
);
$query="select tipo,esp1,descripcion from accesorios where nro_orden=$nro_orden order by tipo";
$res=$db->execute($query) or die ($db->errormsg()." - $query");
while ($acce=$res->FetchRow()) {
	if($acce["esp1"]=="on")
     $string="si";	
    if($acce["esp1"]=="off")
     $string="no";
    if (($acce["esp1"]!="off") && ($acce["esp1"]!="on"))
	 $string=$acce["esp1"]." ".$acce["descripcion"];
	$pdf->accesorio($tipo[$acce["tipo"]],$string);
}
$resultado->Movefirst();
while ($datos=$resultado->FetchRow()) {
	if ($datos["tipo"]=="monitor") {
		$pdf->monitor($datos["descripcion"]);
	}
}
$resultado->Movefirst();
while ($datos=$resultado->FetchRow()) {
	if ($datos["tipo"]=="garantia") {
		$pdf->garantia($datos["descripcion"]);
	}
}

$pdf->accesorios_adicionales($sistema_operativo,$hay_modem);

$nombre="";
$nombre="ordendeproduccion_";
$nombre.=$nro_orden;
$nombre.=".pdf";
$pdf->guardar_servidor($nombre);
?>