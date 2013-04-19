<?
define("ROOT_DIR", "../..");			// Directorio raiz
define("LIB_DIR", ROOT_DIR."/lib");				// Librerias del sistema
require_once(LIB_DIR."/adodb/adodb.inc.php");

//identificacion
$db_type = 'postgres7';				// Tipo de base de datos

$db_host = 'devel.local';			// Host en devel.

$db_user = 'projekt';				// Usuario.
$db_password = 'propcp';			// Contraseña.
$db_name = 'gestion';				// Nombre de la base de datos.
$db_schemas = array(
	"ordenes"
	);
	
$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;
?>