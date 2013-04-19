<?php
/*
AUTOR: Norberto
MODIFICADO POR:
$Author: ferni $
$Revision: 1.8 $
$Date: 2006/12/06 19:43:53 $
*/

include("funciones_generales.php");

$servers = array(
		"Coradir SL" => array(
						"host" => "recepcion.local",
						"user" => "usr_sistema_bio",
						"pass" => "bio",
						"base" => "sistema_bio",
						"id" => "1"
					),
		"Coradir BA" => array(
						"host" => "200.47.22.184",
						"user" => "postgres",
						"pass" => "root",
						"base" => "sistema_bio",
						"id" => "2"
					)
);

$sql = "SELECT id_usuario, login FROM usuarios ";
$sql .= "LEFT JOIN phpss_account ON usuarios.login=phpss_account.username ";
$sql .= "WHERE phpss_account.active='true'";
$result = $db->Execute($sql) or die("Error obteniendo los ids de usuarios");
$usuarios = array();
while ($fila = $result->fetchRow()) {
	$usuarios[$fila['login']] = $fila['id_usuario'];
}

foreach ($servers as $server => $datos) {
	$db_r = &ADONewConnection($db_type) or die("Error al conectar a la base de datos remota (server: $server)\n");
	if (!$db_r->Connect($datos['host'], $datos['user'], $datos['pass'], $datos['base'])) continue;
	
	//print_r($usuarios);
	$sql_r = "SELECT LOCALTIMESTAMP AS diff";
	$result_r = $db_r->Execute($sql_r);
	$diff = abs(strtotime($result_r->fields['diff'])-strtotime(date("Y-m-d H:i:s")));
	if ($diff > 60*60) { // si la diferencia de horas es mayor a 1 hora
		// enviar mail avisando y no cargar los datos
		$para = "programadores@coradir.com.ar";
		$asunto = "Error en la hora del servidor de control de asistencia ($server)";
		$contenido = "Hora en el servidor de gestion: ".date("d/m/Y H:i:s")."\n";
		$contenido .= "Hora en el servidor $server: ".date("d/m/Y H:i:s",strtotime($result_r->fields['diff']))."\n";
		$contenido .= "Diferencia: ".sprintf("%0.2f",$diff / 60 / 60)." horas\n";
		enviar_mail($para,$asunto,$contenido,'','','',0);
	}
	else {
		$sql_r = "SELECT login_gestion, fecha, hora_entra, hora_sale ";
		$sql_r .= "FROM asistencia ";
		$sql_r .= "WHERE fecha BETWEEN CURRENT_DATE - INTERVAL '1 day' AND CURRENT_DATE";
		$result_r = $db_r->Execute($sql_r) or die("Error trayendo los datos de la base remota (server: $server)\n");
		
		$error = "";
		$db->begintrans();
		$sql = "DELETE FROM asistencia WHERE (fecha BETWEEN CURRENT_DATE - INTERVAL '1 day' AND CURRENT_DATE) AND id_servidor=".$datos['id'];
		$result = $db->Execute($sql) or $error .= "Error borrando los datos viejos (server: $server)\n";
	    if (!$error) {
	    	$sql = 'PREPARE guardarAsistencia (int, date, time, time, int) AS ';
	  		$sql .= 'INSERT INTO asistencia (id_usuario, fecha, hora_entra, hora_sale, id_servidor) ';
	  		$sql .= 'VALUES ($1, $2, $3, $4, $5)';
	  		$result = $db->Execute($sql) or $error .= "Error en la preparacion para insertar (server: $server)\n";
	    }
	  	if (!$error) {
	  		while ($fila = $result_r->fetchRow()) {
	  			if ($usuarios[$fila['login_gestion']] != "" && $error == "") {
	  				$sql = "EXECUTE guardarAsistencia (".$usuarios[$fila['login_gestion']].", ";
	  				$sql .= "'".$fila['fecha']."', ".(($fila['hora_entra'])?"'".$fila['hora_entra']."'":"NULL");
	  				$sql .= ", ".(($fila['hora_sale'])?"'".$fila['hora_sale']."'":"NULL").", ".$datos['id'].")";
	  				$result = $db->Execute($sql) or $error .= "Error insertando datos (server: $server)\n";
//	  				echo "sql: $sql<br>";
	  			}
	  		}
	  	}
	  	if (!$error) {
	  		$sql = "DEALLOCATE guardarAsistencia";
	  		$result = $db->Execute($sql) or $error .= "Error al eliminar la funcion de insert (server: $server)\n";
	    }
	    if ($error) {
			$db->rollbacktrans();
			die($error);
	    }
	    else {
			//echo "Sincronizacion correcta";
			$db->committrans();
	    }
	    $db_r->Disconnect();
	}
}
?>