<?php
/*
$Author: fernando $
$Revision: 1.3 $
$Date: 2006/07/04 20:19:33 $
*/
require_once("../../config.php");
 /*******************************************
 ** Configuración de la base de datos.
 *******************************************/

//$db_host = '200.47.8.43';
$db_user_pymes = 'projekt';				// Usuario.
$db_password_pymes = 'propcp';			// Contraseña.

$db_name_pymes = 'pymes';

// Arreglo que contiene los nombres de los esquemas en la
// base de datos para poder acceder a las tablas sin tener
// que usar en nombre del esquema.
$db_schemas_pymes = array("empresas","general");
$db_debug_pymes = FALSE;					// Debugger de las consultas.
//Establezco conexion
$db_pymes = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db_pymes->Connect($db_host, $db_user_pymes, $db_password_pymes, $db_name_pymes);
$db_pymes->cacheSecs = 3600;
$result_pymes=$db_pymes->Execute("SET search_path=".join(",",$db_schemas_pymes)) or die($db_pymes->ErrorMsg());
unset($result_pymes);
$db_pymes->debug = $db_debug_pymes;
?>