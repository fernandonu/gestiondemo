<?php
/*
$Author: mari $
$Revision: 1.1 $
$Date: 2006/04/19 20:18:24 $
*/

require_once("../../config.php");
$nro_orden=$parametros['id'];
echo $html_header
?>
<form name='form1' action='post' method='post'>
<?

chdir("./reportes");

$sql="select file_name,id_reporte,placabase 
      from ordenes.reporteorden 
      join ordenes.reportes using(id_reporte)
      left join ordenes.reporte_detalle using (id_reporte)
      where id_orden=$nro_orden and (num_rep=0 or num_rep is null) 
      order by  nro_serie,fecha";
$result=sql($sql,"$sql") or fin_pagina();

$cant_grupos = $result->RecordCount();

$cpu_ant="";
$cache_ant="";
$ram_ant="";
$placa_ant="";
for($i=0;$i<$cant_grupos;$i++) {
	$nombre = $result->fields["file_name"];
	$placabase = $result->fields["placabase"];
	$id_reporte = $result->fields["id_reporte"];
	
if (strlen($id_reporte) < 3) {
	$id_reporte_tmp = sprintf("%03d",$id_reporte);
}
else { $id_reporte_tmp = $id_reporte; }
$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;
$zip_path_name = enable_path("$id_reporte_tmp.zip");

if (SERVER_OS == "linux") {
	$err = `/usr/bin/unzip "$zip_path_name" "$nombre.txt"`;
} elseif (SERVER_OS == "windows"){
	$paso = ROOT_DIR."\\lib\\zip";
	//die ("$paso\\unzip.exe \"$zip_path_name\" \"$nombre.txt\"");
	$err = shell_exec("$paso\\unzip.exe -o \"$zip_path_name\" \"$nombre.txt\"");
} else {
	die("Error en descompresión.");
}
            	if (!file_exists("$nombre.txt")) {
            		$result->MoveNext();
            		continue;
            	}
            	$file = fopen("$nombre.txt","r");
            	$length = filesize("$nombre.txt");
    
    if ($length > 0) {
    $report = fread($file,$length);
    $detalle=$report;
            		
	if (preg_match("/CPU type: (.+)\r\n/", $detalle, $regs)) {
	$cpu = $regs[1];
    } else {
	$cpu = "Indefinido";
    }

   if (preg_match("/CPU Level 2 Cache: (\\d+KB)/", $detalle, $regs)) {
	$cache = $regs[1];
   } else {
	$cache = "0KB";
   }


   if (preg_match("/RAM: (\\d+) Bytes/", $detalle, $regs)) {
	 $ram = floor($regs[1]/1024/1024);
   } else {
	 $ram = "0";
   }
   
   $msg="";
   if ($cpu_ant!="" && $cpu_ant !=$cpu) { 
   	  Error("ERROR AL LEER CPU. Grupo ".($i+1));
   }
   else {
      $cpu_ant=$cpu;
   }
   if ($cache_ant!="" && $cache_ant!= $cache) {
     Error("ERROR AL LEER CACHE. Grupo ".($i+1));
   }
   else {
     $cache_ant=$cache;
   }
   if ($ram_ant!="" && $ram_ant != $ram) {
       Error("ERROR AL LEER RAM. Grupo ".($i+1));
   }
   else {
     $ram_ant=$ram;
   }
    $pa=split(",",$placa_ant);
   $p=split(",",$placabase);
   //controlo modelo y version 
   $pa=$pa[0].$pa[1];
   $p=$p[0].$p[1];
   
   if ($placa_ant!="" && $pa != $p) {
       Error("ERROR AL LEER  Placa Base. Grupo ".($i+1));
   }
   else {
     $placa_ant=$placabase;
   }
 
  }
   
  fclose($file);
  unlink("$nombre.txt");
  $result->MoveNext();
}
 if (!$error) {
       Aviso("CONFIGURACION CORRECTA");
    }
?>

<div align="center">
   <br>
   <input type='button' name='Cerrar' value='Cerrar' onclick='window.close();'> 
   </div>

</form>
