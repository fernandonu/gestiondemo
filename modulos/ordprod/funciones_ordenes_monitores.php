<?
/*
AUTOR: Fernando
MODIFICADO POR:
$Author: fernando $
$Revision: 1.1 $
$Date: 2007/01/26 19:29:57 $
*/
require_once("../../config.php");


$abecedario = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

function enviar($nombre_archivo) {
	global $buffer;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate");
	header("Content-Transfer-Encoding: binary");
	Header('Content-Type: application/dummy');
	Header('Content-Length: '.strlen($buffer));
	Header('Content-disposition: attachment; filename='.$nombre_archivo);
	echo $buffer;
}


function descomponer_nro_serie($nro_serie){

	$datos = array();
	
	$planta   = substr($nro_serie,0,2);
	$año_mes  = substr($nro_serie,2,4);
	
	$alfa          = substr($nro_serie,6,3);
    $primera_letra = substr($alfa,0,1);
    $segunda_letra = substr($alfa,1,1);
    $tercera_letra = substr($alfa,2,1);
    
	$numerico       = substr($nro_serie,9,3);
	$primer_numero  = substr($numerico,0,1);
	$segundo_numero = substr($numerico,1,1);
	$tercer_numero  = substr($numerico,2,1); 
	
	$datos["planta"]        = $planta;
	$datos["año_mes"]       = $año_mes;
	$datos["alfa"]          = $alfa;
	$datos["primera_letra"] = $primera_letra;
	$datos["segunda_letra"] = $segunda_letra;
	$datos["tercera_letra"] = $tercera_letra;
	$datos["numerico"]      = $numerico;
	$datos["primer_numero"] = $primer_numero;
	$datos["segundo_numero"]= $segundo_numero;
	$datos["tercer_numero"] = $tercer_numero;
	return $datos;	
}


//generador del proximo numero de serie
//el ASCII del alfa es A = 65 .. Z = 90
function proximo_nro_serie ($nro_serie){
	
	$datos = descomponer_nro_serie($nro_serie);
	
	$planta        = $datos["planta"];
	$año_mes       = $datos["año_mes"];
	$alfa          = $datos["alfa"];
    $primera_letra = $datos["primera_letra"];
    $segunda_letra = $datos["segunda_letra"];
    $tercera_letra = $datos["tercera_letra"];	
	$numerico      = $datos["numerico"];
    $numerico      = $numerico + 1;
    $sumo_alfa = 0;
	
	if ($numerico<100 && $numerico>9) $numerico = "0".$numerico;
	              elseif ($numerico<10) $numerico = "00".$numerico;
	                     elseif ($numerico>999) {
	               	                  $numerico = "000";
	              	                  $sumo_alfa = 1;
	                     }
	              
	                     
     //echo ord($primera_letra)." --- ".ord($segunda_letra)." --- ".ord($tercera_letra);	                     
	 if ($sumo_alfa) {
	 	    //para la tercera letra
	 	    if (trim(ord($tercera_letra))<90) 
	 	    	     $tercera_letra = chr(trim(ord($tercera_letra))+1);
	 	             elseif (trim(ord($segunda_letra))<90)
	 	                $segunda_letra = chr(trim(ord($segunda_letra))+1);
	 	                elseif (trim(ord($primera_letra))<90)
	 	                    $primera_letra = chr(trim(ord($primera_letra))+1);
	 	               
} //del if ($sumo_alfa)            
	 
return	 $nro_serie_aux = $planta.$año_mes.$primera_letra.$segunda_letra.$tercera_letra.$numerico;
} //de la function



//funcion similar a gestiones comentarios
//pero accediendo a otra tabla para hacerla mas rapida
function gestiones_comentarios_monitores($nro_orden_monitores) {
	global $bgcolor3,$_ses_user_login,$html_root;
	
	if ($nro_orden_monitores) {
	$sql = "SELECT id_comentarios_monitores,fecha,comentario,usuario FROM ";
	$sql .= "comentarios_monitores WHERE nro_orden_monitores=$nro_orden_monitores ";
	$sql .= "ORDER BY fecha ASC";
	$result = sql($sql) or fin_pagina();;

	if ($result->RecordCount() > 0) {
		echo "<table width='100%' border='1' cellpadding='2' cellspacing='1' bgcolor='$bgcolor3' bordercolor='#ffffff'>";
		echo "<tr id=ma><td width='25%'>Fecha</td>";
		echo "<td width='75%'>Comentario</td></tr>";
		$result->MoveFirst();
		while (!$result->EOF) {

				echo "<tr><td align=center valign=top><b>".$result->fields["usuario"]."<br>";
				echo Fecha($result->fields["fecha"])."&nbsp;".Hora($result->fields["fecha"])."</b>";
				echo "</td>";
				echo "<td><div name='comentario_".$result->fields["id_comentarios_monitores"]."' style='width:100%;border: outset 2;background-color: white;'>".html_out($result->fields["comentario"])."</div></td>\n";
				echo "</tr>";
				$result->MoveNext();
			}
			echo "<tr><td align=right valign=top><b>Nuevo:</b></td>";
			echo "<td><textarea name='comentario_nuevo' style='width:100%;' rows=4></textarea></td>\n";
			echo "</tr>";
		echo "</table>";
	}else {
			echo "<table width='100%' border='1' cellpadding='2' cellspacing='1' bgcolor='$bgcolor3' bordercolor='#ffffff'>";
			echo "<tr id=ma><td width='25%'>Fecha</td>";
			echo "<td width='75%'>Comentario</td></tr>";
			echo "<tr><td align=right valign=top><b>Nuevo:</b></td>";
			echo "<td><textarea name='comentario_nuevo' style='width:100%;' rows=4></textarea></td>\n";
			echo "</tr>";
			echo "</table>";
		
	}
   }
   else {
	
			echo "<table width='100%' border='1' cellpadding='2' cellspacing='1' bgcolor='$bgcolor3' bordercolor='#ffffff'>";
			echo "<tr id=ma><td width='25%'>Fecha</td>";
			echo "<td width='75%'>Comentario</td></tr>";
			echo "<tr><td align=right valign=top><b>Nuevo:</b></td>";
			echo "<td><textarea name='comentario_nuevo' style='width:100%;' rows=4></textarea></td>\n";
			echo "</tr>";
			echo "</table>";
	}
	
}//de la funcion

//funcion similar a la nuevo comentario de lib.php
function nuevo_comentarios_monitores($nro_orden_monitores,$comentario) {
	global $_ses_user;
	
	$comentario=ereg_replace("'","\'",$comentario);
    $comentario=ereg_replace("\"","\\\"",$comentario);	
	
	$sql = "INSERT INTO comentarios_monitores (nro_orden_monitores,";
	$sql .= "fecha,comentario,usuario) VALUES ($nro_orden_monitores,";
	$sql .= "'".date("Y-m-d H:i:s")."','$comentario','".$_ses_user["name"]."')";
	return $sql;
}

?>