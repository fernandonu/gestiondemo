<?
/*
Autor: GACZ
Creado: lunes 02/05/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.5 $
$Date: 2005/06/16 22:14:25 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");


$mes=$_POST['select_mes'] or $mes=$parametros['mes'] or $mes=date('m');
$anio=$_POST['select_anio'] or $anio=$parametros['anio'] or $anio=date('Y');
$mes_cambiar=$_POST['select_mes_cambiar'] or $mes_cambiar=date('m');
$anio_cambiar=$_POST['select_anio_cambiar'] or $anio_cambiar=date('Y');
if ($download=$parametros['download'])
	ob_start();

variables_form_busqueda("ret_per");
//variable que indica que se debe mostrar
if ($cmd=="")
{
	$_ses_ret_per['cmd']=$cmd="retenciones";
	phpss_svars_set("_ses_ret_per",$_ses_ret_per);//valor por defecto
}

$datos_barra = array(
					 array("descripcion"	=> "Retenciones", "cmd"	=> "retenciones")
					,array("descripcion"=> "Percepciones", "cmd"=> "percepciones")
					,array("descripcion"	=> "Todas",	"cmd"=> "todas")
				 );
echo $html_header;				 
?>
<style type="text/css">
<!--
.table1.head{color: #F7F7F7; background-color: #4A3C8C; font-weight: bold; text-align:center}
.table1.par{color: #4A3C8C; background-color: #E7E7FF; }
.table1.impar{color: #4A3C8C; background-color: #F7F7F7; }
td input.read {border:none; background-color:transparent; width:100%;}
td input.write {border:default; background-color:default; width:100%}
textarea.noscrolls {overflow: hidden;}
textarea.noscrollsread {overflow: hidden;border:none; background-color:transparent; width:100%}
/*td input.read {border-width:medium; width:100%}*/
-->
</style>
<!--<script src="../../lib/popcalendar.js"></script>-->
<?

$meses[1]="Enero";
$meses[2]="Febrero";
$meses[3]="Marzo";
$meses[4]="Abril";
$meses[5]="Mayo";
$meses[6]="Junio";
$meses[7]="Julio";
$meses[8]="Agosto";
$meses[9]="Setiembre";
$meses[10]="Octubre";
$meses[11]="Noviembre";
$meses[12]="Diciembre";

$select_mes=new HtmlOptionList("select_mes");
for($i=1; $i <= 12; $i++)
	$select_mes->add_option($meses[$i],$i);
$select_mes->setSelected($mes);

$select_anio=new HtmlOptionList("select_anio");
$i=date('Y')-10; //diez anios antes
$j=$i+20; //diez anios despues
$i=$i-1;
while (++$i <= $j)
  $select_anio->add_option($i);
$select_anio->setSelected($anio);

$winentidad= new JsWindow('','_blank',640,570);
$winentidad->locationBar=false;
$winentidad->toolBar=false;
$winentidad->menuBar=false;
$winentidad->varName='winentidad';

$winfactura= new JsWindow('','_blank',650,550);
$winfactura->locationBar=false;
$winfactura->toolBar=false;
$winfactura->menuBar=false;
$winfactura->varName='winfactura';

$select_distrito=new HtmlOptionList("select_distrito");
$q="select * from distrito order by nombre ";
$r=sql($q) or fin_pagina();
$select_distrito->add_option("Seleccione",-1);
$select_distrito->optionsFromResulset($r,array('text'=>'nombre','value'=>'id_distrito'));

$i=0;
$fecha_hoy=date2();

generar_barra_nav($datos_barra);

if ($download)
 ob_clean();
else 
	echo ob_get_contents();
 
switch ($cmd)
{
	case "percepciones":
		include("percepciones.ui.php");break;
	case "retenciones":
		include("retenciones.ui.php");break;
	case "todas":
		include("todas.ui.php");break;
}
if (!$download)
	fin_pagina();

?>


