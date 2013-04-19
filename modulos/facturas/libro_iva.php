<?
/*
Autor: GACZ
Fecha de Creacion: miercoles 05/05/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2004/05/07 15:42:13 $
*/
require_once("../../config.php");


//variable que indica que se debe mostrar
if (!$cmd)
$cmd=$parametros['cmd'] or $cmd=$_POST['cmd'] or $cmd=0; //default ventas


$datos_barra = array(
					array(
						"descripcion"	=> "Compras",
						"cmd"		=> "1"
						),
					array(
						"descripcion"	=> "Ventas",
						"cmd"			=> "0"
						)
				 );

generar_barra_nav($datos_barra);
echo "<br>";

if ($cmd==1)
	include("libro_iva_compras.php");
else 
	include("libro_iva_ventas.php");
	

?>