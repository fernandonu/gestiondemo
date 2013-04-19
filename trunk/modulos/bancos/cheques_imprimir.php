<?php
//modulo facturas

require_once("../../config.php");

$id_banco = $parametros["id_banco"];
$num_cheque = $parametros["numero_cheque"];

if ($id_banco == "" || $num_cheque == "") {
	Error("Parametros incorrectos");
	exit();
}

$sql = "SELECT fechaemich,fechavtoch,importech,no_a_la_orden,proveedor.razon_social ";
$sql.= "FROM bancos.cheques LEFT JOIN general.proveedor	ON cheques.idprov=proveedor.id_proveedor ";
$sql.= "WHERE cheques.idbanco=$id_banco AND númeroch=$num_cheque";
$result = sql($sql) or die("ERROR AL OBTENER LOS DATOS DEL CHEQUE");

if ($result->RecordCount() != 1) {
	Error("No se encontró el cheque especificado");
	exit();
}

$monto = number_format($result->fields['importech'], 2, ',', '');
$nombre = $result->fields['razon_social'];
if ($result->fields['no_a_la_orden'] == "1") {
	$nombre .= " NO A LA ORDEN";
}

list($anio,$mes,$dia) = split("-",$result->fields['fechaemich']);

list($anio_pago,$mes_pago,$dia_pago) = split("-",$result->fields['fechavtoch']);


if ($id_banco == 21) {
	$word_template = file_get_contents("cheque_supervielle.doc");
	$nombre=strtoArray($nombre,62,false);
	$nombre_linea1 = $nombre[0];
	$nombre_linea2 = $nombre[1];
	
	$cantidad = strtoArray(ucfirst(NumerosALetras($monto)),62,false);
	$cantidad_linea1 = $cantidad[0];
	$cantidad_linea2 = $cantidad[1];
	
	$search = array ("/{monto}/",
	                 "/{dia}/",
	                 "/{mes}/",
	                 "/{anio}/",
	                 "/{dia_pago}/",
	                 "/{mes_pago}/",
	                 "/{anio_pago}/",
	                 "/{nombre_linea1}/",
	                 "/{nombre_linea2}/",
	                 "/{cantidad_linea1}/",
	                 "/{cantidad_linea2}/"
	                 );
	
	$replace = array ($monto,
					  $dia,
	                  $meses[intval($mes)],
	                  $anio,
	                  $dia_pago,
	                  $meses[intval($mes_pago)],
	                  $anio_pago,
	                  $nombre_linea1,
	                  $nombre_linea2,
	                  $cantidad_linea1,
	                  $cantidad_linea2
	                 );
	
	$file_tmp = preg_replace($search, $replace, $word_template);
	if (isset($_SERVER["HTTPS"])) {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
	}
	
	header("Content-Type: application/cheque");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=\"cheque_supervielle_$num_cheque.doc\"");
	echo $file_tmp;
	exit();
}
else {
	Error("El banco especificado no tiene un template asignado");
	exit();
}


/*
$file_name = $num_cheque.".doc";

$handle = @fopen($file_name, 'w');
@fwrite($handle, $file_tmp);
@fclose($handle);
*/
?>