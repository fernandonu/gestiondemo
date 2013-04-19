<?
/*
$Author: $
$Revision: $
$Date:  $
*/

require_once "config.php";
require_once("./modulos/permisos/permisos.class.php");

if ($parametros['mode'] == "logout") {
	phpss_logout();
	$mode = "";
	include_once(ROOT_DIR."/login.php");
	exit;
}

if ($parametros['mode'] == "debug") {
   if (permisos_check("inicio","debug")) {
		$_ses_user["debug"] = $parametros["debug_status"];
		phpss_svars_set("_ses_user", $_ses_user);
	}
}

$res_width=$_ses_user['res_width'];
$res_height=$_ses_user['res_height'];
if ($res_width >= 1024) {
    $size=2;
    $tam=230;
    $letra="12px";
}
else {
	$size=1;    
	$tam=180;
	$letra="10px";
}
?>
<html>
<head>
<title>Sistema de gestion</title>

<link rel='icon' href='<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME']).$html_root; ?>/favicon.ico'>
<link REL='SHORTCUT ICON' HREF='<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME']).$html_root; ?>/favicon.ico'>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?

if ($res_width >= 1024) 
    echo "<link rel='STYLESHEET' type='text/css' href='$html_root/lib/dhtmlXMenu_xp.css'>";
else 
    echo "<link rel='STYLESHEET' type='text/css' href='$html_root/lib/dhtmlXMenu_xp1.css'>";  
    

echo "<script  src='$html_root/lib/dhtmlXCommon.js'></script>";
echo "<script  src='$html_root/lib/dhtmlXProtobar.js'></script>";
echo "<script  src='$html_root/lib/dhtmlXMenuBar.js'></script>";
echo "<script  src='$html_root/lib/dhtmlXTree.js'></script>";
?>
</head>
<?


//ob_start();
//$arbol=new ArbolOfPermisos("root");	
//$usuario=new user($_ses_user['login']);
//$arbol->createMenu($usuario);
//$arbol->saveXMLMenu();
//file_put_contents("menunew.xml",ob_get_contents());
//ob_end_clean();


/*****************************************************************************************/
//Cuando use menu_xml va esta linea
$usuario=new user($_ses_user['login']);
/*****************************************************************************************/

$onclick=encode_link("index.php",array("mode" => "logout")); 
$root="$html_root/imagenes/logo_coradir.jpg"; 
$accesos=$usuario->get_Accesos();

?>

<body topmargin=0 leftmargin=0 rigthmargin=0 marginwidth="0" marginheight="0" style="{overflow-x:auto; overflow-y:hidden;}" onresize="fix_size();" onload="fix_size();" bgcolor='<?echo $bgcolor_frames;?>'>  
<table name="contenido" id="contenido" width='100%' align="center" cellpadding="0" cellspacing="0" border="0">
  <tr>
		<td height="54" valign="top">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" height="54"  background="<?=$html_root."/imagenes/fondo1.jpg"?>" >
			<tr>
				<td width="375">
				<img border="0" src="<?=$root?>" width="300" height="48"></td>
				<td>
				<? $cant_accesos=count($accesos);
				   $j=0;
				   ?>
				  <table border="0" cellpadding="0" cellspacing="0" width="150" >
					<tr>
				   <?
				   for($i=0;$i<$cant_accesos;$i++) {
				   	 if ($accesos[$i]) {
                      $modulo=$usuario->permisos->getmoduloByName($accesos[$i]);
                      $pagina=$usuario->permisos->getdescByName($accesos[$i]);
                      $descripcion=$usuario->permisos->getpathByName($accesos[$i]).$pagina;
                      $j++;
                      if (strpos($accesos[$i],"?") === false) 
                          $nombre_pagina=$accesos[$i].".php";
                      else 
                          $nombre_pagina=ereg_replace("\?",".php?",$accesos[$i]);
                    
                    ?> 
				       <td height="16px" nowrap align="center">
                           <a href="<?=$html_root?>/modulos/<?=$modulo?>/<?=$nombre_pagina?>" target="frame2">
                             <img src='<?=$html_root?>/imagenes/<?=($i+1)?>.jpg' alt='' border='0' title="<?=$pagina?>" 
          		             onclick="tempLinkText='<?=$descripcion?>'; tempLinkHref='<?=$html_root?>/modulos/<?=$modulo?>/<?=$nombre_pagina?>';">
                           </a>
                       </td>      
				     <? if ($j==($cant_accesos/2)) { ?>
				       </tr>
				       <tr>
				      <?}
				     }
				   }?>
				     
				 </tr>			
				 </table>
				 
				<?if (permisos_check("inicio","debug")) {
                   $debug_status = $_ses_user["debug"];
                   if ($debug_status == "on") {
                      $debug_link = encode_link($html_root."/index.php",array("mode"=>"debug","debug_status"=>"off"));
	                  $debug_desc = "Debugger Activado\nHaga click aquí para desactivarlo";
                   }
                  else {
	                  $debug_link = encode_link($html_root."/index.php",array("mode"=>"debug","debug_status"=>"on"));
	                  $debug_desc = "Debugger Desactivado\nHaga click aquí para activarlo";
                    }
				   echo "<td align='right'><A onclick='location.href=\"$debug_link&menu=\"+tempLinkHref;' target='_top'><img src='$html_root/imagenes/debug-$debug_status.gif' alt='$debug_desc' border='0'></a></td>";
				   

                   }
                 $link_ayuda_menu=encode_link("$html_root/modulos/ayuda_menu/ayuda_menu.php",array());
                 
				?>
				<td>
                  <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="window.open('<?=$link_ayuda_menu?>','','width=<?=$res_width-10?>,height=<?=$res_height-80?>,left=0,top=0',scrollbars='no')" >    
               </td>
                 <td align="right">
				<?list($dia,$mes,$anio,$dia_s) = split("-", date("j-n-Y-w",mktime()));?>
				<table border="0" cellpadding="0" cellspacing="0" width="<?=$tam?>" >
					<tr>
						<td align="right">
						<b><font face="Trebuchet MS" size="<?=$size?>" color="#FFFFFF">
						Sistema de Gestión
						</font></b></td>
					</tr>
					
					<tr>
						<td align="right">
	    					<b><font face="Trebuchet MS" size="<?=$size?>" color="#FFFFFF"><?=$_ses_user["name"]?>
	    					</font></b>
						</td>
					</tr>		
					<tr>
						<td align="right"><b>
						  <font face="Trebuchet MS" size="<?=$size?>">
						  <?=$dia_semana[$dia_s]." ".$dia." de ".$meses[$mes]." de ".$anio?> 
						  </font></b>
						</td>
					</tr>
		
				</table>
				</td>
				<? 
                 $link_eventos= encode_link("$html_root/modulos/calidad/nuevo_evento.php",array("nuevo"=>1,"id_evento"=>-1,"cmd"=>"pendienetes"));
                 $titulo_eventos="Como sabran, parte del sistema de gestion de la calidad ISO 9001:2000 de nuestra empresa implica un compromiso de cada uno de nosotros.\nPor esto, cada vez que suceda algo que no está de acuerdo a los procedimientos de nuestra empresa deberás denunciarlo. Para ello haz clic aquí...";
                 $link=encode_link("$html_root/modulos/calidad/nuevo_evento.php",array("id_evento"=>-1,"cmd"=>"pendientes"));
                   ?>
				<td width="54" align="center">
				<a href="<?=$link?>" target="frame2">
				<img border="0" src="<?=$html_root?>/imagenes/iso.jpg" width="48" height="48" alt="<?=$titulo_eventos?>"></td>
				<td width="54" align="center">
				 <a href="<?=$onclick?>"><img border="0" src="<?=$html_root?>/imagenes/salir.jpg" width="48" height="48" alt="Salir"></a>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td background="<?=$html_root."/imagenes/fondo2.jpg"?>">
	    <span id="ruta_menu" style="width:100%;padding-left:10px;color:#000000;font-weight:bold;text-align:left;">
          <a id='link_menu_actual'  style='color:#000000;font-weight:bold;text-decoration:none;font-size:<?=$letra?>;font-family:Trebuchet MS'; target='frame2'></a>
        &nbsp;
          </span>
		</td>
	</tr>
<tr>
  <td colspan=2><div id="xpstyle" style="width:100%;"></div> </td>
</tr>
<?
if ($parametros['mode'] == "debug") {  //es porque seleccionó el debugger y tiene que mantener la pagina
 	//echo "GET= ".$_GET['menu'];
    $div=ereg_replace("^$html_root","",$_GET['menu']);
    //echo " DIV despues de reemplazar= ".$div;
	$div=explode("/",$div,4);
	//echo "DIV despues del explode= ";
	//print_r($div);  
	$pagina_inicio=ereg_replace("\.php","",$div[3]);
 
}
else {
	$pagina_inicio=$usuario->get_pagina_inicio();
}


$modulo_inicio=$usuario->permisos->getmoduloByName($pagina_inicio);
$descripcion_inicio=$usuario->permisos->getpathByName($pagina_inicio).$usuario->permisos->getdescByName($pagina_inicio);

if (strpos($pagina_inicio,"?") === false) 
         $pagina_inicio=$pagina_inicio.".php";
else 
         $pagina_inicio=ereg_replace("\?",".php?",$pagina_inicio);

$src=$html_root."/modulos/$modulo_inicio/$pagina_inicio";
//if ($_ses_user['login']=='mariela')
//    $src=$html_root."/modulos/ayuda_menu/seleccionar_item.php";
//


echo "<tr><td>";
echo "<iframe name='frame2' id='frame2' style='{width:100%;height:0px;overflow-y:hidden;overflow-x:hidden;}' marginwidth=0 marginheight=0 frameborder=0 align='center' src='$src'></iframe>\n";
echo "</td></tr>\n";
?>
</table>
<script>
	  function onButtonClick(itemId,itemValue) {
		//document.all.frame2.src='';
	  };

	    menu=new dhtmlXMenuBarObject(document.getElementById('xpstyle'),'100%',20,"",0);
		menu.setOnClickHandler(onButtonClick);
		menu.setGfxPath("imagenes/menu/");
	    menu.loadXML("<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME'])."$html_root/menu_xml.php"; ?>");  
        menu.showBar();
 	
        
//	    menu=new dhtmlXMenuBarObject(document.getElementById('xpstyle'),'100%',20,"",0);
//		menu.setOnClickHandler(onButtonClick);
//		menu.setGfxPath("imagenes/menu/");
//        menu.loadXML("menunew.xml");
//		menu.showBar();
//	
        
        
function fix_size() {
	//seteamos al maximo posible el largo del frame 2, para no desperdiciar espacio al final de la pagina ?>
	var largo_body = window.document.body.clientHeight - ((document.all)?88:88);
	document.getElementById('frame2').style.height=largo_body+"px";
	document.getElementById('contenido').style.width=((document.all)?100:100)+"%";
 }
 
 //Valores para la pagina de inicio

 var tempLinkText='<?=$descripcion_inicio?>';
 var tempLinkHref='<?=$src?>';
 var tempLinkTarget='frame2';
 var html_root='<? echo $html_root;?>';

 //se pone la ruta de inicio por defecto
 //objeto de tipo <a> se usa para actualizar la ruta al menu actual
 var oPath=document.getElementById('link_menu_actual');
 
 oPath.updateLink=function ()
 {
 	this.innerText=tempLinkText;
 	this.href=tempLinkHref;
 	this.target=tempLinkTarget;
  };
 
</script>
</body>

</html>
