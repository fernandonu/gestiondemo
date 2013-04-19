<?php
require_once ("../../config.php");
include ("../../lib/imagenes_stat/jpgraph.php");
include ("../../lib/imagenes_stat/jpgraph_bar.php");

function suma_fechas($fecha,$ndias){
      if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha))
      	list($dia,$mes,$año)=split("/", $fecha);
      if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha))
        list($dia,$mes,$año)=split("-",$fecha);
      $nueva = mktime(0,0,0, $mes,$dia,$año) + $ndias * 24 * 60 * 60;
      $nuevafecha=date("d-m-Y",$nueva);
      return ($nuevafecha);  
}
function compara_fechas1($fecha1,$fecha2){
	if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha1))
    	list($dia1,$mes1,$año1)=split("/",$fecha1);
    if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha1))
    	list($dia1,$mes1,$año1)=split("-",$fecha1);
    if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha2))
    	list($dia2,$mes2,$año2)=split("/",$fecha2);
    if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha2))
    	list($dia2,$mes2,$año2)=split("-",$fecha2);
    $dif = mktime(0,0,0,$mes1,$dia1,$año1) - mktime(0,0,0, $mes2,$dia2,$año2);
    return ($dif);
}

if ($_POST['redibujar']=='Redibujar'){
	$fecha_desde=$_POST['fecha_desde'];
	$fecha_hasta=$_POST['fecha_hasta'];
	$porcentaje=$_POST['porcentaje'];
	if (compara_fechas1($fecha_desde,$fecha_hasta) >0){
		$no_dibujar=1;
		echo "<center><b><font size='2' color='red'>La Fecha Desde no Puede ser MAYOR a la Fecha Hasta</font></b></center>";
	}	
}
echo $html_header;
cargar_calendario();
if (!$fecha_desde) $fecha_desde=suma_fechas(date("d/m/Y"),-10);
if (!$fecha_hasta) $fecha_hasta=date("d/m/Y");
if (!$porcentaje) $porcentaje=100;
?>
<form name='form1' action="caso_graficos.php" method="POST">
<br>

<table align="center" width="95%" class="bordes" bgcolor="White">
 <tr>
   <td> <b>Desde</b> <input type="text" name="fecha_desde" value="<?=$fecha_desde?>" size="10" readonly><?=link_calendario("fecha_desde")?> </td>
   <td> <b>Hasta</b> <input type="text" name="fecha_hasta" value="<?=$fecha_hasta?>" size="10" readonly><?=link_calendario("fecha_hasta")?> </td>
   <td> <b>porcentaje</b> 
   		<select name="porcentaje">
   		<option value="75"<?if($porcentaje=='75') echo 'selected'?>>75%</option>
   		<option value="100"<?if($porcentaje=='100') echo 'selected'?>>100%</option>
   		<option value="150"<?if($porcentaje=='150') echo 'selected'?>>150%</option>
   		<option value="200"<?if($porcentaje=='200') echo 'selected'?>>200%</option>
   		</select>
   </td>
   <td><input type='submit' name="redibujar" value='Redibujar'></td>   
   </tr>
</table>
<br>
<br>
<?
if ($no_dibujar!=1){
	$fecha_desde_db=Fecha_db($fecha_desde);
	$fecha_hasta_db=Fecha_db($fecha_hasta);
	
	$sql1="select count(idcaso) as valor from casos.casos_cdr 
			  where (fechainicio BETWEEN '$fecha_desde_db' AND '$fecha_hasta_db')";
	$result_1=sql($sql1,'no se puede');
	
	$sql2="select count (distinct (idcaso)) as valor
				from casos.log_casos 
				where (descripcion = 'Finalizado') and (date(fecha) BETWEEN '$fecha_desde_db' AND '$fecha_hasta_db')";
	$result_2=sql($sql2,'no se puede');

	
	$link_s=encode_link("caso_graficos_gen.php",array("fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta,"porcentaje"=>$porcentaje,"abierto"=>1));?>
	<b><font size="2">Total de Casos Abiertos: <?=$result_1->fields['valor']?></font></b>
	<br>
	<img src='<?=$link_s?>' border=0 align=top>
	<br>
	<br>
	<?$link_s=encode_link("caso_graficos_gen.php",array("fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta,"porcentaje"=>$porcentaje,"abierto"=>0));?>
	<b><font size="2">Total de Casos Cerrados: <?=$result_2->fields['valor']?></font></b>
	<br>
	<img src='<?=$link_s?>' border=0 align=top>
	<?
}
echo $html_footer;?>