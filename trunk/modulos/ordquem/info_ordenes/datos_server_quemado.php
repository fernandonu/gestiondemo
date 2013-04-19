<?
define("ROOT_DIR", "../../..");			// Directorio raiz
define("LIB_DIR", ROOT_DIR."/lib");				// Librerias del sistema
require_once(LIB_DIR."/adodb/adodb.inc.php");

$arch_ens = "./id_ensamblador.txt";
$arch = fopen($arch_ens,"r");
$id_ensamblador = fread($arch,filesize($arch_ens));
fclose($arch);

//identificacion
$db_type = 'postgres7';				// Tipo de base de datos

$db_host = 'devel.local';			// Host en devel.

$db_user = 'projekt';				// Usuario.
$db_password = 'propcp';			// Contraseña.
$db_name = 'gestion';				// Nombre de la base de datos.

$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;

?>