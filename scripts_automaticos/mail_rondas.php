<?php

include("funciones_generales.php");

$servers = array(
		"Coradir SL" => array(
						"host" => "recepcion.local",
						"user" => "usr_sistema_bio",
						"pass" => "bio",
						"base" => "sistema_bio"						
					)
);

foreach ($servers as $server => $datos) {
	$db_r = &ADONewConnection($db_type) or die("Error al conectar a la base de datos remota (server: $server)\n");
	if (!$db_r->Connect($datos['host'], $datos['user'], $datos['pass'], $datos['base'])) continue;
		$sql = "SELECT public.datos_personales.apellido,public.datos_personales.nombre,public.rondas.fecha_hora
				FROM public.datos_personales
  				INNER JOIN public.rondas ON (public.datos_personales.idempleado = public.rondas.idempleado)
				WHERE (public.datos_personales.hace_rondas = 1) and (date(fecha_hora) BETWEEN CURRENT_DATE-1 and CURRENT_DATE)";
		$result = $db_r->Execute($sql) or $error .= "Error borrando los datos viejos (server: $server)\n";
		
		$result->moveFirst();
		$contenido="Nombre: ".$result->fields['nombre']."  Apellido: ".$result->fields['apellido']."\n\n\n";
		while (!$result->EOF){
			$contenido .= "Hora de la Ronda: ".$result->fields['fecha_hora']."\n";
			//echo $result->fields['fecha_hora'];
			$result->moveNext();
		}
		$para = "ferni@coradir.com.ar,ruben@coradir.com.ar";
		$asunto = "Rondas Personal de Seguridad";
		enviar_mail($para,$asunto,$contenido,'','','',0);		
	 }
	 $db_r->Disconnect();
?>