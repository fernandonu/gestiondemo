<?
/*
$Author: nazabal $
$Revision: 1.146 $
$Date: 2007/06/28 18:50:57 $
*/

/*******************************************
 ** Constantes para usar en los include/require
 ** (Directorios relativos al sistema)
 *******************************************/

define("ROOT_DIR", dirname(__FILE__));			// Directorio raiz
define("LIB_DIR", ROOT_DIR."/lib");				// Librerias del sistema
define("MOD_DIR", ROOT_DIR."/modulos");			// Modulos del sistema
define("UPLOADS_DIR", ROOT_DIR."/uploads");		// Directorio para uploads
define("CAS_DIR",str_replace("\\","/",UPLOADS_DIR)."/cas/"); //directorio de archivos CAS
define("CASOS_DIR",str_replace("\\","/",UPLOADS_DIR)."/casos/");//directorio de archivos CASOS
define("CORADIR", "Coradir S.A.");
/*******************************************
 ** Headers para que el explorador no guarde
 ** las páginas en cache.
 *******************************************/

header("Cache-control: no-cache");
header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");

/*******************************************
 ** Colores del sistema.
 *******************************************/

//$bgcolor_frames = "#A6BAD1";  //color de frames del sistema
$bgcolor_frames = "#B7CEEF";  //color de frames del sistema

$bgcolor1 = "#5090C0";		// Primer color de fondo
$bgcolor2 = "#D5D5D5";		// Segundo color de fondo
$bgcolor3 = "#E0E0E0";		// Tercer color de fondo
$bgcolor4 = "#FF0000";		// Color de fondo para tareas vencidas
$bgcolor  = "#d1c294";


$bgcolor_out  = "#B7C7D0"; // Color de fondo (onmouseout)
$bgcolor_over = "#EEFFE6"; // Color de fondo (onmouseover)
$text_color_out  = "#000000";
$text_color_over = "#004962";//"#006699";
$fondo = "fondo.gif"; //imagen de fondo
$fondo1 = "fondo1.jpg"; //imagen de fondo del encabezado
//$fondo = "fondo2.jpg"; //imagen de fondo


// atributo de los tr de los listados
$atrib_tr="bgcolor=$bgcolor_out onmouseover=\"this.style.backgroundColor = '$bgcolor_over'; this.style.color = '$text_color_over'\" onmouseout=\"this.style.backgroundColor = '$bgcolor_out'; this.style.color = '$text_color_out'\"";

/*******************************************
 ** Cantidad de items a mostrar por página.
 *******************************************/

$itemspp = 50;

/*******************************************
 ** Configuración de la base de datos.
 *******************************************/

$db_type = 'postgres7';				// Tipo de base de datos.
if (
	$_SERVER["HTTP_HOST"]=="admin.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion-devel.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion-telmex.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion-netway.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion-velocom.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion-backup.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion2.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion2007-alt.coradir.com.ar" ||
	$_SERVER["HTTP_HOST"]=="gestion.local" ||
	$_SERVER["HTTP_HOST"]=="banco.local" ||
	$_SERVER["HTTP_HOST"]=="bancociudad.local" ||
	$_SERVER["HTTP_HOST"]=="devel.local" ||
	$_SERVER["HTTP_HOST"]=="prueba1.local"||
	$_SERVER["HTTP_HOST"]=="192.168.1.44"||
	$_SERVER["SERVER_ADDR"]=="192.168.1.44"||
	$_SERVER["HTTP_HOST"]=="200.47.8.44"||
	$_SERVER["SERVER_ADDR"]=="200.47.8.44"||
	$_SERVER["HTTP_HOST"]=="200.47.8.50"||
   	$_SERVER["SERVER_ADDR"]=="200.47.8.50"||
	$_SERVER["HTTP_HOST"]=="190.3.23.14"||
	$_SERVER["SERVER_ADDR"]=="190.3.23.14"||
	$_SERVER["HTTP_HOST"]=="190.3.23.13"||
	$_SERVER["SERVER_ADDR"]=="190.3.23.13"||
    $_SERVER["HTTP_HOST"]=="200.47.8.57"||
    $_SERVER["SERVER_ADDR"]=="200.47.8.57" ||
    $_SERVER["HTTP_HOST"]=="gestion2007.coradir.com.ar" ||
    $_SERVER["SERVER_ADDR"]=="gestion2007.coradir.com.ar"
   ) {
	$db_host = 'localhost';			// Host de la página.
}
else {
	$db_host = 'localhost';		// Host para desarrollo.
}

//$db_host = '200.47.8.43';
$db_user = 'projekt';				// Usuario.
$db_password = 'propcp';			// Contraseña.
$db_name = 'gestiondemo';


// IPs permitidas para conectarse al gestion, si el ip no esta aca, se envia un mail
$ip_permitidas = array(
	"127.0.0.1/32" => "Localhost",
	"192.168.1.0/24" => "Red Interna S.L.",
	"10.0.0.0/24" => "Red Interna S.L. (Planta)",
	"200.47.8.32/27" => "Coradir S.L. (Comsat)",
	"200.47.171.0/26" => "Coradir S.L. (Comsat)",
	"200.47.13.64/26" => "Red Netway",
	"190.3.23.8/29" => "Coradir S.L. (Telmex)",
	"200.47.22.172/27" => "Coradir Bs. As."
);

// Nombre de la base de datos.
//$db_name = 'gestion';				// Nombre de la base de datos.
$ADODB_CACHE_DIR = LIB_DIR."/adodb/cache";		// Directorio para cache de consultas
// Arreglo que contiene los nombres de los esquemas en la
// base de datos para poder acceder a las tablas sin tener
// que usar en nombre del esquema.
$db_schemas = array(
	"bancos",
	"compras",
	"general",
	"internet",
	"licitaciones",
	"ordenes",
	"mensajes",
	"permisos",
	"sistema",
	"facturacion",
    "caja",
	"personal",
	"casos",
	"maquinas",
	"encuestas",
	"calidad",
	"reclamo_partes",
	"muestras",
	"mov_material",
	"reporte_tecnico",
	"tareas_divisionsoft",
	"stock",
    "remito_interno",
    "comidas",
    "pcpower_presupuesto",
    "licitaciones_datos_adicionales",
    "asientos",
    "compras_adicional",
    "pymes",
    "contabilidad"

);
$db_debug = FALSE;					// Debugger de las consultas.

/*******************************************
 ** Limite de tiempo de inactividad para la
 ** expiración de la sesión (en minutos).
 *******************************************/

 $session_timeout = 90;

/*******************************************
 ** Variable $html_root que contiene la ruta
 ** a la raíz de la página.
 ** (Ruta relativa al URL de la página)
 *******************************************/
//if (ereg("(/modulos)|(/lib)|(/index.php)|(/menu.php)|(/menu_xml.php)|(/aviso.php)",$_SERVER["SCRIPT_NAME"],$tmp)) {
//	$tmp=explode($tmp[1].$tmp[2].$tmp[3].$tmp[5].$tmp[6],$_SERVER["SCRIPT_NAME"]);
//	$html_root = $tmp[0];
//}
//unset($tmp);

if (ereg("(/modulos)|(/lib)|(/index.php)|(/menu_para_ayuda.php)|(/menu_xml.php)|(/aviso.php)",$_SERVER["SCRIPT_NAME"],$tmp)) {
	$tmp=explode($tmp[1].$tmp[2].$tmp[3].$tmp[4].$tmp[5].$tmp[6],$_SERVER["SCRIPT_NAME"]);
	$html_root = $tmp[0];
}
unset($tmp);



/*******************************************
 ** Variable $html_footer contiene el
 ** pie de la página.
 *******************************************/

$html_footer = "
  </body>
</html>
";


/*******************************************
 ** Libreria principal del sistema.
 *******************************************/


require LIB_DIR."/lib.php";

$programadores_rojos=array("ferni","marcos","fernando","elizabeth","mariela","broggi","nazabal","cestila","cesar","gonzalo","gaudina","quique");

//para que los programadores distingan entre gestion.local y localhost
if($_SERVER["HTTP_HOST"]=="gestion.local" && $cond2=(in_array($_ses_user["login"],$programadores_rojos) || in_array($_ses_user["original_usr"],$programadores_rojos)))
   $fondo="fondo_coradir_alter.gif";

if($_SERVER["HTTP_HOST"]=="gestion.coradir.com.ar" && $cond2=(in_array($_ses_user["login"],$programadores_rojos) || in_array($_ses_user["original_usr"],$programadores_rojos)))
   $fondo="fondo_coradir_alter.gif";

if ($cond2)	$session_timeout = 120;

if($_SERVER["HTTP_HOST"]=="devel.local" && $_ses_user["login"]=="juanmanuel")
   $fondo="fondo_coradir_alter.gif";

/*******************************************
 ** Variable $html_header contiene el
 ** encabezamiento de la página.
 *******************************************/
 
if ($_ses_cambiar_perfil_usuario == 1) 
   $actualizar_menu_perfil_usuario='false';
else $actualizar_menu_perfil_usuario='true';

$html_header = "
<html>
  <head>
	<link rel='icon' href='".((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME'])."$html_root/favicon.ico'>
	<link REL='SHORTCUT ICON' HREF='".((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME'])."$html_root/favicon.ico'>
	<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>
    <script languaje='javascript' src='$html_root/lib/funciones.js'></script>
    <script language='javascript'>
		var winW=window.screen.Width;
		var valor=(winW*25)/100;
		var nombre1;
		var titulo1;
		function insertar() {
			ventana.document.all.titulo.innerText=titulo1;
			ventana.frames.frame1.location=nombre1;
		}
		function abrir_ventana(nombre,titulo) {
			var winH=window.screen.availHeight;
			nombre1=nombre;
			titulo1=titulo;
			if ((typeof(ventana) == 'undefined') || ventana.closed) {
				ventana=window.open('$html_root/modulos/ayuda/TITULOS.htm','ventana_ayuda','width=' + valor + ',height=' + (winH)+ ', left=' + (winW - valor ) +'  ,top=0, scrollbars=0 ');
				window.top.resizeBy(-valor,0);
			}
			else { ventana.focus(); }
			setTimeout('insertar()',400);
		}
  function check_fix_size() {
			if (typeof(fix_size) == 'function') fix_size();
			    if(parent && parent.oPath && $actualizar_menu_perfil_usuario) parent.oPath.updateLink();//actualiza el link del menu actual
			   
		}
	</script>
  </head>
 <body topmargin=0 background=$html_root/imagenes/$fondo bgcolor=\"$bgcolor3\"  onload='check_fix_size();document.focus();' onresize='check_fix_size();'>";

?>
