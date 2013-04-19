<?php
/*AUTOR: MAD
               13 diciembre 2004
$Author: mari $
$Revision: 1.41 $
$Date: 2006/04/19 17:40:04 $
*/
require_once("../../config.php");

$id = $_GET["id"];
?>	
<html><body bgcolor="<?=$bgcolor_out?>">
<?
function parse_linea($linea) {
	$aux=split(" ",$linea);
//	print_r($aux);
//	echo "<br>";
	$i=0;
	$ret["name"]='';
	
	while($i<count($aux) && !is_numeric($aux[$i])){//descripcion
		$ret["name"].=$aux[$i]." ";
		$i++;
	}
//	echo $i;
	if(substr_count($ret["name"],"Network")>0){
		if ($i==count($aux)) return 0;
		while($i<count($aux) && is_numeric($aux[$i])){//numero de red
			$ret["name"].=$aux[$i]." ";
			$i++;
		}
		if ($i==count($aux)) return 0;
		while($i<count($aux) && !is_numeric($aux[$i])){//espacio
			$i++;
		}
	}

	if ($i==count($aux)) return 0;
	while($i<count($aux) && is_numeric($aux[$i])){//primer numero
		$i++;
	}
	if ($i==count($aux)) return 0;
	while($i<count($aux) && !is_numeric($aux[$i])){//espacio
		$i++;
	}
	if ($i==count($aux)) return 0;
	while($i<count($aux) && is_numeric($aux[$i])){//segundo numero
		$i++;
	}
	if ($i==count($aux)) return 0;
	while($i<count($aux) && !is_numeric($aux[$i])){//espacio
		$i++;
	}
	if ($i>=count($aux)) return 0;

	$ret["#errores"]="";
	while($i<count($aux) && is_numeric($aux[$i])){//tercer numero
		$ret["#errores"].=$aux[$i];
		$i++;
	}

	if ($i>=count($aux)) return 0;

	$ret["tipo"]='';
	while($i<count($aux)){
		$ret["tipo"].=$aux[$i]." ";
		$i++;
	}	
	return $ret;
} //de la funcion

if (!$id) die("No hay datos para analizar.");

//$sql="select reporte_detalle.detalle from reportes join reporte_detalle using(id_reporte) where nro_serie = '$id'";
$sql="select reporte_detalle.file_name,nro_serie,resultado from ordenes.reportes
             join ordenes.reporte_detalle using(id_reporte) where id_reporte = $id
             order by num_rep desc";
$result = sql($sql,"Error buscando errores") or fin_pagina();

if ($result->RecordCount()==0) die("No hay datos para analizar.");

$nro_serie=$result->fields["nro_serie"];
$resultado=$result->fields["resultado"];

chdir("./reportes");

if (strlen($id_reporte) < 3) {
	$id_reporte_tmp = sprintf("%03d",$id);
}
else { $id_reporte_tmp = $id; }

$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;
$zip_path_name = enable_path("$id_reporte_tmp.zip");

//if($_ses_user['login']=="marcos" || $_ses_user['login']=="fernando") echo "<br>PASO $zip_path_name<br>";

if (SERVER_OS == "linux") {
	$err = `/usr/bin/unzip "$zip_path_name"`;
} elseif (SERVER_OS == "windows"){
	$paso = ROOT_DIR."\\lib\\zip";
	$err = shell_exec("$paso\\unzip.exe -o \"$zip_path_name\"");
} else {
	die("Error en descompresión.");
}


for($i=0;$i<1/*$result->RecordCount()*/;$i++) {

            	$nombre = $result->fields["file_name"];
            	if (!file_exists("$nombre.txt")) {
            		$result->MoveNext();
            		continue;
            	}
            	$file = fopen("$nombre.txt","r");
            	$length = filesize("$nombre.txt");

            	if ($length > 0) {
            		$report = fread($file,$length);
            		$detalle=$report;

            		//$report=$result->fields['detalle'];
            		$report=explode("DETAILED ERROR LOG:\n",$report);
            		$report = $report[0];
            		$report = split("\n",$report);
            		$t=0;

            		for($j=25;$j<33;$j++){
            			$result_linea=parse_linea($report[$j]);
            			$result_total[$t]["name"]=trim($result_linea["name"]);
            			$result_total[$t]["#errores"]+=$result_linea["#errores"];
            	//		$result_total[$t][$result_linea["tipo"]]+=$result_linea["#errores"];
            			$t++;
            		}

                    //con esta linea no muestra mas errores de 2d graphics
                    /*
            		for($j=25;$j<33;$j++){
            			$result_linea=parse_linea($report[$j]);
            			$result_total[$t]["name"]=$result_linea["name"];
                        if (substr_count($result_total[$t]["name"],"2D Graphics"))
            			                   $result_total[$t]["#errores"]=0;
                                           else
                                           $result_total[$t]["#errores"]+=$result_linea["#errores"];
                       //$result_total[$t]["#errores"]=0;
                		$t++;
            		}
                    */

            	}
            	fclose($file);
            	unlink("$nombre.txt");
            	$result->MoveNext();


}
	///////////////////////////////////////////////////////////////////////

/*$parte_1=strlen(stristr(str_replace("\n","]",$detalle),"CPU type:"));
$parte_2=strlen(stristr(stristr(str_replace("\n","]",$detalle),"CPU type:"),"]"));
$parte_2=$parte_1-$parte_2;
$cpu=substr((stristr(str_replace("\n","]",$detalle),"CPU type:")),0,$parte_2);*/
if (preg_match("/CPU type: (.+)\r\n/", $detalle, $regs)) {
	$cpu = $regs[1];
} else {
	$cpu = "Indefinido";
}


/*$parte_1=strlen(stristr(str_replace("\n","]",$detalle),"CPU Level 2 Cache:"));
$parte_2=strlen(stristr(stristr(str_replace("\n","]",$detalle),"CPU Level 2 Cache:"),"]"));
$parte_2=$parte_1-$parte_2;
$cache=substr((stristr(str_replace("\n","]",$detalle),"CPU Level 2 Cache:")),0,$parte_2);*/
if (preg_match("/CPU Level 2 Cache: (\\d+KB)/", $detalle, $regs)) {
	$cache = $regs[1];
} else {
	$cache = "0KB";
}


/*$parte_1=strlen(stristr(str_replace("\n","]",$detalle),"RAM:"));
$parte_2=strlen(stristr(stristr(str_replace("\n","]",$detalle),"RAM:"),"]"));
$parte_2=$parte_1-$parte_2;
$ram=str_replace("\n","d",substr((stristr(str_replace("\n","]",$detalle),"RAM:")),0,$parte_2));*/

/*$parte_1=strlen(stristr($detalle,"RAM:"));
//echo "<br>".stristr($detalle,"RAM:")."<br>";
$parte_2=strlen(stristr(stristr($detalle,"RAM:"),"\n"));
//echo "<br>".stristr(stristr($detalle,"RAM:"),"\n")."<br>";
$parte_2=$parte_1-$parte_2;
$ram=substr(stristr($detalle,"RAM:"),0,($parte_2-1));*/
if (preg_match("/RAM: (\\d+) Bytes/", $detalle, $regs)) {
	$ram = floor($regs[1]/1024/1024);
} else {
	$ram = "0";
}
//echo "<br>".$ram."<br>";
	///////////////////////////////////////////////////////////////////////

//echo $nombre;
?>
<script>

function cambia()
{
 

 parent.document.all.cpu.value='<? echo $cpu; ?>';
 parent.document.all.ram_ar.value='<? echo $ram; ?>';
 parent.document.all.cache.value='<? echo $cache; ?>';
 
 

}
cambia();
</script>

<CENTER><STRONG>Análisis del último <?/*$i*/?> reporte para la máquina '<?=$nro_serie?>'</STRONG></CENTER>
<TABLE align="center" width="70%" cellpadding="0" cellspacing="0" style="border:'solid'; border-width='1'; border-color='black'">
<TR bgcolor="<?=$bgcolor1?>" style="font-size:'8pt'; font-weight:'bold'">
<TD align="center">Test</TD>
<TD align="center">Errores</TD>
</TR>
<?
$color_linea=$bgcolor3;
$hubo_errores = false;
/*
for($t=0;$t<count($result_total);$t++){
	if ($result_total[$t]["#errores"]>0) {
		if ($result_total[$t]["name"] == "2D Graphics") {
			$estilo_fila = "color:white;background-color:green;font-size:8pt; font-weight:bold;";
		}
		else {
			$estilo_fila = "color:white;background-color:red;font-size:8pt; font-weight:bold;";
			$hubo_errores = true;
		}
	   }
	   else
		  $estilo_fila = "background-color: $color_linea;font-size:8pt; font-weight:bold;";

	echo "<TR style='$estilo_fila'>";
	echo "<TD>".$result_total[$t]["name"]."</TD>";
	echo "<TD align='center'>".$result_total[$t]["#errores"]."</TD></TR>";
	if($color_linea==$bgcolor3) $color_linea=$bgcolor2; else $color_linea=$bgcolor3;
}          //del for
*/

for($t=0;$t<count($result_total);$t++){
  //los errores de 2D Graphics no los cuento
  if (!substr_count($result_total[$t]["name"],"2D Graphics")) {
	if ($result_total[$t]["#errores"]>0) {
		$estilo_fila = "color:white;background-color:red;font-size:8pt; font-weight:bold;";
		$hubo_errores = true;
  }else $estilo_fila = "background-color: $color_linea;font-size:8pt; font-weight:bold;";

  echo "<TR style='$estilo_fila'>";
	echo "<TD>".$result_total[$t]["name"]."</TD>";
	echo "<TD align='center'>".$result_total[$t]["#errores"]."</TD></TR>";
	if($color_linea==$bgcolor3) $color_linea=$bgcolor2; else $color_linea=$bgcolor3;
  } //del if ($result_total[$t]["name"] != "2D Graphics")
}          //del for

/*
		if ($cant_grupos == 0)
			$sent = "value='No Quemado' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';
		else
			if ($datos[0]["resultado"] == 0 )
				$sent = "value='No Terminado' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';
			elseif ($datos[0]["resultado"] == 1 )
				$sent = "value='Quemado Correcto' style=".'"background-color='."'green'; color='white'; font-weight='bold'; text-align='center'".'"';
			else
				$sent = "value='Hubo Errores' style=".'"background-color='."'red'; color='white'; font-weight='bold'; text-align='center'".'"';
*/
	if ($resultado == 0) {
		$res_texto = "No Terminado";
		$res_bgcolor = "yellow";
		$res_color = "black";
		//$sent = "value='No Terminado' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';
	}
	elseif ($resultado == 1 or !$hubo_errores) {
		$res_texto = "Quemado Correcto";
		$res_bgcolor = "green";
		$res_color = "white";
		//$sent = "value='Quemado Correcto' style=".'"background-color='."'green'; color='white'; font-weight='bold'; text-align='center'".'"';
	}
	else {
		$res_texto = "Hubo Errores";
		$res_bgcolor = "red";
		$res_color = "white";
		//$sent = "value='Hubo Errores' style=".'"background-color='."'red'; color='white'; font-weight='bold'; text-align='center'".'"';
	}
	echo "<script language=javascript>
			parent.document.all.resultado.value = '$res_texto';
			parent.document.all.resultado.style.background = '$res_bgcolor';
			parent.document.all.resultado.style.color = '$res_color';
			</script>";
?>
</TABLE>

</body></html>
<?
	fin_pagina();
?>