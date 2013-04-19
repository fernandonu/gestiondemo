<?

/*
$Author:  $
$Revision:  $
$Date:  $
*/


require "../../config.php";
echo $html_header;

$menu = $parametros['menu'];
$param_menu = array("menu" => $menu);
$mode=$parametros['mode'];

if ($mode == "cerrar") {
	$mode = "";
	?>
	<script>
	   window.close();
	</script>
	<?
	exit;
}

$src_fr1 = encode_link("$html_root/menu_para_ayuda.php",array());

if ($menu)
     $src_fr2 = encode_link($html_root."/modulos/ayuda_menu/mostrar_ayuda_menu.php",array("menu"=>$menu));
    
else 
     $src_fr2 = "$html_root/modulos/ayuda_menu/seleccionar_item.php";

echo "<iframe name='frame' id='frame' allowTransparency=true style='{z-index:1; position:absolute; top:0; width:100%; height:100%}' marginwidth=0 marginheight=0 frameborder=0 scrolling=no align='center' src='$src_fr1'></iframe>\n";
echo "<iframe name='frame2' id='frame2' onClick='javascript: frame.cleanUpMenuBar();' allowTransparency=true style='{z-index:2; position:absolute; top:90; width:100%; height:80%}' marginwidth=0 marginheight=0 frameborder=0 align='center' src='$src_fr2'></iframe>\n";

?>