<?php
/*AUTOR: MAD
               1 julio 2004
$Author: mari $
$Revision: 1.29 $
$Date: 2006/04/19 20:18:38 $
*/
/*
Muestra los grupos de reportes que se corresponden con las maquinas quemadas de la orden de quemado
se muestra informacion de la maquina en si.
*/


require_once("../../config.php");

//buscar los reportes
if (isset($parametros["num_mac"])) {
	$sql="select id_orden from reporteorden join reportes using (id_reporte) where reportes.mac='".$parametros["num_mac"]."' order by nro_serie,fecha";
	$result = sql($sql, "$sql") or fin_pagina();//$db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	$parametros["id"]=$result->fields["id_orden"];
}
if (isset($parametros["id"])){ 
	$sql="select * from ordenes.reporteorden join ordenes.reportes using(id_reporte)  where id_orden=".$parametros["id"]." order by nro_serie,fecha";
	$result = sql($sql, "$sql") or fin_pagina();//$db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
} else {
	error("error en parametro de entrada");
	fin_pagina();
}	
?>

<SCRIPT src="../../lib/fns.js">
</SCRIPT>

<SCRIPT>
	var reportes = new Array();//arreglo de reportes

function ver_detalle(control){
	//alert(control.options[control.selectedIndex].value);
	window.open('ver_detalle.php?id_reporte='+document.all.rep.options[document.all.rep.selectedIndex].value+'&num_rep='+control.options[control.selectedIndex].value,'','left=100,top=0,width=550,height=500,resizable=1');
}	
	
function select(control) {
	document.all.fecha.value = reportes[control.selectedIndex]['fecha'];
	document.all.nro_serie.value = reportes[control.selectedIndex]['nro_serie'];
	document.all.mac.value = reportes[control.selectedIndex]['mac'];
	document.all.disco.value = reportes[control.selectedIndex]['disco'];
	//document.all.cpu.value = reportes[control.selectedIndex]['cpu'];
	document.all.placabase.value = reportes[control.selectedIndex]['placabase'];
	/*
	if (reportes[control.selectedIndex]['resultado'] == 1){
		document.all.resultado.style.background = 'green';
		document.all.resultado.style.color = 'white';
		document.all.resultado.value = 'Quemado Correcto';
	}
    else if (reportes[control.selectedIndex]['resultado'] == 0){ 
		document.all.resultado.style.background = 'yellow';
		document.all.resultado.style.color = 'black';
		document.all.resultado.value = 'No Terminado';
    } else { 
		document.all.resultado.style.background = 'red';
		document.all.resultado.style.color = 'white';
		document.all.resultado.value = 'Hubo Errores';
    } 
    */
	document.all.resultado.style.background = 'yellow';
	document.all.resultado.style.color = 'black';
	document.all.resultado.value = 'Verificando...';

	document.all.subrep.length = 0;	
	var i;
	for(i=1; i <= reportes[control.selectedIndex]['reportes'].length; i++) {
		add_option(document.all.subrep,reportes[control.selectedIndex]['reportes'][i-1],"Reporte_"+i);	
	}
	//document.all.det_errores.src='analize_error.php?id='+reportes[control.selectedIndex]['nro_serie'];	
	document.all.det_errores.src='analize_error.php?id='+control.value;
	//setTimeout("document.all.det_errores.cambia()",3000);	
	
}
<?
$cant_grupos = $result->RecordCount();

For($i=0;$i<$cant_grupos;$i++){
 
	$sql="select * from reporte_detalle where id_reporte = ".$result->fields["id_reporte"];
	$result_aux = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	
	
	?>
	var reporte_<?=$i?> = new Array();
	reporte_<?=$i?>['id_orden'] = '<? echo $result->fields['id_orden'];?>';
	reporte_<?=$i?>['id_reporte'] = '<? echo $result->fields['id_reporte'];?>';
	reporte_<?=$i?>['nro_serie'] = '<? echo $result->fields['nro_serie'];?>';
	reporte_<?=$i?>['mac'] = '<? echo $result->fields['mac'];?>';
	reporte_<?=$i?>['resultado'] = <? echo $result->fields['resultado'];?>;
	reporte_<?=$i?>['disco'] = '<? echo $result->fields['disco'];?>';
	reporte_<?=$i?>['cpu'] = '<? echo $result->fields['cpu'];?>';
	reporte_<?=$i?>['placabase'] = '<? echo $result->fields['placabase'];?>';
	reporte_<?=$i?>['fecha'] = '<? echo $result->fields['fecha'];?>';
	reporte_<?=$i?>['reportes'] = new Array();
	<?
    $reportes=array();
	For($j=0;$j<$result_aux->RecordCount();$j++){
		
		?>
			reporte_<?=$i?>['reportes'][<?=$j?>] = '<? echo $result_aux->fields['num_rep'];?>';
		<?
		
		$reportes[$j] = array(
		"num_rep" => $result_aux->fields["num_rep"]
		);
        $result_aux->MoveNext();
	} ?> 
	reportes[<?=$i?>] = reporte_<?=$i?>;
	<?
	$datos[$i] = array(
	"id_orden" => $result->fields["id_orden"],
	"id_reporte" => $result->fields["id_reporte"],
	"nro_serie" => $result->fields["nro_serie"],
	"mac" => $result->fields["mac"],
	"resultado" => $result->fields["resultado"],
	"disco" => $result->fields["disco"],
	"cpu" => $result->fields["cpu"],
	"placabase" => $result->fields["placabase"],
	"fecha" => $result->fields["fecha"],
	"reportes" => $reportes);
	//arreglo para series repetidos
	$series[$i] = $result->fields["nro_serie"];
	$numeros[$i] = '';	
	$result->MoveNext();
	//$cualquiera
	
}
//$result->recordCount();



?>
</SCRIPT>
<?

echo $html_header;
$alto = 12;

//////////////////////////////////////////////////////////

$id_reporte = $datos[0]['id_reporte'];
$num_rep = 0;

$sql="select file_name from reporte_detalle where id_reporte = $id_reporte and num_rep = $num_rep";
$result_r = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");

$nombre = $result_r->fields["file_name"];

chdir("./reportes");

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
//die ($nombre);

if (file_exists("$nombre.txt")) {
	$file = fopen("$nombre.txt","r");
	$length = filesize("$nombre.txt");
	if ($length > 0) {
		$detalle = fread($file,$length);
	}
	else {
		$detalle = "EL REPORTE ESTA VACIO!!!";
	}
	
	fclose($file);
	
	unlink("$nombre.txt");
}
else {
	$detalle = "NO EXISTE EL ARCHIVO DEL REPORTE!!!";
}

//echo str_replace("\n","]",$detalle);
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
$ram=substr((stristr(str_replace("\n","]",$detalle),"RAM:")),0,$parte_2);*/

if (preg_match("/RAM: (\\d+) Bytes/", $detalle, $regs)) {
	$ram = floor($regs[1]/1024/1024);
} else {
	$ram = "0";
}

//////////////////////////////////////////////////////////
?>

<FORM name="form1" action="ver_reportes.php" method="POST">
<INPUT type="hidden" name="id" value="<?=$id?>">

<TABLE align="center" width="100%" class="bordes" cellspacing="0" border=1>
<TR bgcolor=<?=$bgcolor3?>>
	<TD width="12%" align="center">
	<b>Grupos de Reportes</B>
	</TD>
	<TD width="12%" align="center">
	<b>Reportes Asociados</B>
	</TD>
	<TD width="76%" align="center">
	<b>Detalle de la Prueba de vida de la máquina. </B>
	</TD>
</TR>
<TR bgcolor=<?=$bgcolor_out?>>
	<TD align="center">
	<SELECT name="rep" size="<?=$alto?>"  onChange="select(this);">
	<?
	$i=1;
	$rep = 1;
	foreach($datos as $item) {
		//Busqueda si es repetida con otra
		$status = "";
		$selec = "";
		if ($item['resultado']==1) {
			$status = "style='background:green'";
		}
		/*switch ($item['resultado']){
			case 0: $status = "style='background:yellow'"; break;
			case 1: $status = "style='background:green'";	break;
			default: $status = "style='background:red'";
		}*/
		if ($item['mac']==$parametros["num_mac"]) {
			$selec = "Selected";
			$it=$i-1;
		}
		if ($i==1) $option = "<OPTION value=".$item['id_reporte']." selected $status>  Grupo_$i ";
		else $option = "<OPTION value=".$item['id_reporte']." $selec $status>  Grupo_$i ";
		//parte de identificacion de pares de maquinas repetiadas
		$aux_series = array_keys($series,$item['nro_serie']);
		$ent=0;
		foreach($aux_series as $auxx) {
			if ($numeros[$auxx] == ""){
				$numeros[$auxx] = $rep;
				$ent=1;
			}
		}
		if($ent==1) $rep++;
		$option .= "[".$numeros[$i-1]."]";
		echo $option;
		$i++;
		$result->MoveNext();
	}
	if (!$it) $it=0;
	
	?>
	
	</SELECT>
	
	</TD>
	<TD align="center">
    <SELECT name="subrep" size="<?=$alto?>" onchange="ver_detalle(this)">
	<?
	$i=1;
	foreach($datos[$it]["reportes"] as $item){
		if ($i==1)
		echo "<OPTION value=".$item['num_rep']." selected>  Reporte_$i ";
		else 
		echo "<OPTION value=".$item['num_rep'].">  Reporte_$i ";
		$i++;
		$result->MoveNext();
	}
	?>
	</SELECT>
	<?
	 /*$sql_d="select descripcion,tipo from ordenes.filas_ord_prod 
             left join general.productos using(id_producto)
             where nro_orden=".$parametros['id'];*/
	 
	 $sql_d="select filas_ord_prod.descripcion,
              case when filas_ord_prod.id_producto is null then tipos_prod.codigo 
              else tipo end as tipo
              from ordenes.filas_ord_prod 
              left join general.productos using(id_producto) 
              left join general.producto_especifico using(id_prod_esp) 
              left join general.tipos_prod on tipos_prod.id_tipo_prod=producto_especifico.id_tipo_prod
              where nro_orden=".$parametros['id'];
	
	 $datos_tabla=sql($sql_d,"Error al consultar la descripcion de la orden de produccion") or fin_pagina();
	 while (!$datos_tabla->EOF)
	       {if ($datos_tabla->fields['tipo']=="disco rigido") $disco=$datos_tabla->fields['descripcion'];
	        if ($datos_tabla->fields['tipo']=="memoria") $memoria=$datos_tabla->fields['descripcion'];
	        if ($datos_tabla->fields['tipo']=="placa madre") $placa_madre=$datos_tabla->fields['descripcion'];
	        if ($datos_tabla->fields['tipo']=="micro") $micro=$datos_tabla->fields['descripcion'];
	       	$datos_tabla->MoveNext();
	       }	
	 /*$sql_d="select cantidad from ordenes.orden_de_produccion 
             where nro_orden=".$parametros['id'];
	 $cant_maquinas=sql($sql_d,"Error al consultar la cantidad de maquinas de la orden de produccion") or fin_pagina();*/
    ?>
	</TD>
	<TD align="center" width="100%" >
	<TABLE width="100%" class="bordes" cellspacing="0" cellpadding="0">
	    <tr>
	     <td colspan="2" >
	      <table class="bordes" width="100%">
	       <tr>
	        <td><b>Orden de Producción:&nbsp;</b><input name="ord_prod" size="5" redonly class="text_8" value="<?=$parametros["id"]?>"></td>
	        <td><b>Cant. Maquinas:&nbsp;</b><input name="cant_maquina" size="4" readonly class="text_8" value="<?=$parametros["cant"]?>"></td>
	        <td><b>N° de id.(md5):&nbsp;</b><input name="nro_serie" size="38" readonly class="text_8" value="<?=$datos[0]["nro_serie"]?>"></td>
	        
	       </tr>
	       <tr>
	        <td colspan="2"><b>Comienzo de la prueba de vida:&nbsp;</b><input name="fecha" readonly class="text_8" value="<?=$datos[0]["fecha"]?>"></td>	        
	        <td><b>Número MAC:&nbsp;</b><input name="mac" readonly class="text_8" value="<?=$datos[0]["mac"]?>"></td>
	        
	       </tr>
	      </table>
	     </td>
	    </tr>
	    <tr>
	     <td >
	      <table class="bordes" width="100%">
	       
	       <tr><td><b>Procesador:&nbsp;</b><input name="cpu" class="text_8" size="30" readonly value="<?=$cpu?>"></td></tr>
	       
	       <tr><td><b>Cache L2:&nbsp;</b><input name="cache" readonly size="30" class="text_8" value="<?=$cache?>"></td></tr>
	       
	       <?
	        $prueba=substr($datos[0]["placabase"],7,-33);
	     
           ?>
            <tr><td title="<?=$prueba?>"><b>Placa Madre:&nbsp;</b><input name="placabase" class="text_8" size="30" readonly value="<?=$prueba?>"></td></tr>	      	     
	       <tr><td><b>Memoria:&nbsp;</b><input name="ram_ar" readonly class="text_8" value="<?=$ram?>">&nbsp;<b>MB</b></td></tr>
	       <tr><td title="<?=$datos[0]["disco"]?>"><b>Serie de Disco:&nbsp;</b><input name="disco"  size="30" readnloy class="text_8" value="<?=$datos[0]["disco"]?>"></td></tr>	       
	      
	      </table>
	     </td>
	   
	     <td >
	      <table class="bordes" width="100%">
	       
	       <tr><td><b>Procesador:&nbsp;</b><?=$micro?></td></tr>	       
	       <tr><td>&nbsp;</td></tr>
	       <tr><td><b>Placa Madre:&nbsp;</b><?=$placa_madre?></td></tr>  
	       <tr><td>&nbsp;</td></tr>    
	       <tr><td><b>Memoria:&nbsp;</b><?=$memoria?></td></tr>
	       
	       <tr><td><b>Serie de Disco:&nbsp;</b><?=$disco?></td></tr>
	       <tr><td>&nbsp;</td></tr>
	      </table>
	     </td>
	    </tr>
		<?/*<!--<TR align="center">
		<TD width="50%" align="right">
		<B>Orden de producción:</B> 
		</TD>
		<TD width="50%">
		<INPUT type="text" name="id_orden" readonly value="<?=$parametros["id"]?>">
		</TD>
		</TR>
		<TR align="center">
		<TD width="50%" align="right">
		<B>Fecha de comienzo del quemado:</B> 
		</TD>
		<TD width="50%">
		<INPUT type="text" name="fecha" readonly value="<?=$datos[$it]["fecha"]?>">
		</TD>
		</TR>
		<TR align="center">
		<TD align="right">
		<B>Número de id.(md5):</B> 
		</TD>
		<TD >
		<INPUT type="text" name="nro_serie" readonly value="<?=$datos[$it]["nro_serie"]?>" title="<?=$datos[$it]["nro_serie"]?>">
		</TD>
		</TR>
		<TR align="center">
		<TD align="right">
		<B>Número MAC:</B> 
		</TD>
		<TD>
		<INPUT type="text" name="mac" readonly value="<?=$datos[$it]["mac"]?>">
		</TD>
		</TR>
		<TR align="center">
		<TD align="right">
		<B>Series de Disco:</B> 
		</TD>
		<TD>
		<INPUT type="text" name="disco" readonly value="<?=$datos[$it]["disco"]?>" title="<?=$datos[$it]["disco"]?>">
		</TD>
		</TR>
		<TR align="center">
		<TD align="right">
		<B>Info. CPU:</B> 
		</TD>
		<TD>
		<INPUT type="text" name="cpu" readonly value="<?=$datos[$it]["cpu"]?>" title="<?=$datos[$it]["cpu"]?>">
		</TD>
		</TR>
		<TR align="center">
		<TD align="right">
		<B>Info. Placa Madre :</B> 
		</TD>
		<TD>
		<INPUT type="text" name="placabase" readonly  value="<?=$datos[$it]["placabase"]?>" title="<?=$datos[$it]["placabase"]?>">
		</TD>
		</TR>-->*/?>
		<TR align="center">
		<td colspan="2" align="center" class="bordes">
		<B>Estado actual de la prueba de vida:&nbsp;</B> 
		
		
		<?
/*		
		if ($cant_grupos == 0)
			$sent = "value='No Quemado' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';		 
		else
			if ($datos[$it]["resultado"] == 0 ) 
				$sent = "value='No Terminado' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';
			elseif ($datos[$it]["resultado"] == 1 )
				$sent = "value='Quemado Correcto' style=".'"background-color='."'green'; color='white'; font-weight='bold'; text-align='center'".'"';
			else 
				$sent = "value='Hubo Errores' style=".'"background-color='."'red'; color='white'; font-weight='bold'; text-align='center'".'"';
*/
		if ($cant_grupos == 0)
			$sent = "value='No hecha Prueba de vida' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';		 
		else
			$sent = "value='Verificando...' style=".'"background-color='."'yellow'; color='black'; font-weight='bold'; text-align='center'".'"';
		?>
		<input type="text" name="resultado"  readonly <?echo $sent;?>>
		</td>
		</TR>
	</TABLE>
	</TD>
</TR>
<TR bgcolor=<?=$bgcolor3?>>
	<TD align="center">
	<INPUT type="button" name="Cerrar" value="Cerrar" onclick="window.close()">
	</TD>
	<TD colspan="2" align="center">
	Existen <?=$cant_grupos;?> grupos de reportes para <?=$parametros["cant"]?> máquinas distintas de la Orden de Prueba de vida.
	</TD>
</TR>
</TABLE>

<CENTER>Tip: [x] donde x marca que grupo de reportes pertenecen a la misma máquina.</CENTER>
</FORM>
<CENTER>
<IFRAME id="det_errores" src="analize_error.php?id=<?=$datos[$it]['id_reporte']?>" height="175px" width="95%">
</IFRAME>
</CENTER>
<?
fin_pagina();
?>

