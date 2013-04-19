<?php
require_once("../../config.php");

if ($_POST['guardar_valor_deseable']=='Guardar'){	
	$desc_indicadores_deseable=$_POST['desc_indicadores_deseable'];
	$valor_deseable=$_POST['valor_deseable'];
	$sql="update calidad.desc_indicador set valor_deseable=$valor_deseable where id_desc_indicador=$desc_indicadores_deseable";				
	sql($sql,'NO puede insertar');	
}

if ($_POST['guardar']=='Guardar'){
	$anio=$_POST['anio'];
	$mes=$_POST['mes'];
	$desc_indicadores=$_POST['desc_indicadores'];
	$valor=$_POST['valor'];
	
	$sql="select * from calidad.indicadores 
			where id_desc_indicador = '$desc_indicadores' and mes='$mes' and anio='$anio'";	
	$result = sql($sql,'NO puede ejecutar la consulta de validacion de insercion');
	
	if ($result->EOF){
		$sql="insert into calidad.indicadores (id_desc_indicador,mes,anio,valor) 
				   values ('$desc_indicadores',$mes,'$anio','$valor')";				
	    sql($sql,'NO puede insertar');		
	}
	else{
		$sql="update calidad.indicadores set valor='$valor'
				where (id_desc_indicador='$desc_indicadores' and mes='$mes' and anio='$anio')";				
	    sql($sql,'NO puede insertar');
	}	
}

echo $html_header;
//echo "<center><b><font size='2' color='red'>$accion</font></b></center>";
variables_form_busqueda("indicadores");
if (!$cmd) {
	$cmd='20'.date(y);	
	$_ses_indicadores["cmd"] = $cmd;
	phpss_svars_set("_ses_indicadores", $cmd);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Año 2003",
						"cmd"			=> "2003",
						),
					array(
						"descripcion"	=> "Año 2004",
						"cmd"			=> "2004"
						),
						// agrego una nueva "pestaña" para el año 2005
					array(
						"descripcion"	=> "Año 2005",
						"cmd"			=> "2005"
						),
					array(
						"descripcion"	=> "Año 2006",
						"cmd"			=> "2006"
						),
					array(
						"descripcion"	=> "Año 2007",
						"cmd"			=> "2007"
						),
				   );
echo "<br>";

?>
<form action="indicadores_guardar.php" method="post" name="form_indicadores">
<? 
generar_barra_nav($datos_barra);

//genero esta consulta para saber los indicadores que hay luego genero en base a eso
$sql1="SELECT *
	   FROM calidad.desc_indicador 
	   Order By desc_indicador.id_desc_indicador ASC";
$result= sql($sql1) or fin_pagina();
?>
<script>
function control_datos()
{
 if(document.all.anio.value=="-1"){
 	alert('Debe Seleccionar un año');
  	return false;
 }
 if(document.all.mes.value=="-1"){
 	alert('Debe Seleccionar un mes');
  	return false;
 }
 if(document.all.desc_indicadores.value=="-1"){
 	alert('Debe Seleccionar un Indicador');
  	return false;
 } 
 
 if(document.all.valor.value==""){
  alert('Debe ingresar un Valor');
  return false;
 }
 
 return true;
}//de function control_nuevos()

function control_datos_deseable(){
	if(document.all.desc_indicadores_deseable.value=="-1"){
 		alert('Debe Seleccionar un Indicador');
  		return false;
 	} 
	if(document.all.valor_deseable.value==""){
  		alert('Debe ingresar un Valor');
  		return false;
 	}
 	return true;	
}

</script>
<br>
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
<tr id="mo">
    <td>
    	Agregar Valor Deseable del Indicador
    </td>
</tr>
<tr align="right">
	<td align="center">
	  <select name=desc_indicadores_deseable>
      <option value=-1>Seleccione</option>
                 <?
                 $sql= "select * from calidad.desc_indicador order by id_desc_indicador";
                 $result_indi=sql($sql) or fin_pagina();
                 while (!$result_indi->EOF){ 
                 	$id_indi=$result_indi->fields['id_desc_indicador'];
                 	$nombre=$result_indi->fields['descripcion'];
                 ?>
                   <option value=<?=$id_indi;?> ><?=$nombre?></option>
                 <?$result_indi->movenext();
                 }?>
      </select>
      
      <input type="text" name="valor_deseable" value="" style="width=60px">
		
	  <INPUT type="submit" name="guardar_valor_deseable" value="Guardar" onclick="return control_datos_deseable()">
	</td>
</tr>
</table>
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
<tr id="mo">
    <td>
    	Agregar Valor del Indicador
    </td>
</tr>
<tr align="right">
	<td align="center">
	
		<select name=anio>
	     <option value=-1>Seleccione</option>
	     <option value=2006 selected>2006</option>
	     <option value=2007>2007</option>
	     <option value=2008>2008</option>                 
	    </select>
	    
	    <select name=mes>
	     <option value=-1>Seleccione</option>
	     <option value=1>Enero</option>
	     <option value=2>Febrero</option>
	     <option value=3>Marzo</option>                 
	     <option value=4>Abril</option>                 
	     <option value=5>Mayo</option>                 
	     <option value=6>Junio</option>                 
	     <option value=7>Julio</option>                 
	     <option value=8>Agosto</option>                 
	     <option value=9>Septiembre</option>                 
	     <option value=10>Octubre</option>                 
	     <option value=11>Noviembre</option>                 
	     <option value=12>Diciembre</option>	     
	    </select>
	      
      <select name=desc_indicadores>
      <option value=-1>Seleccione</option>
                 <?
                 $sql= "select * from calidad.desc_indicador order by id_desc_indicador";
                 $result_indi=sql($sql) or fin_pagina();
                 while (!$result_indi->EOF){ 
                 	$id_indi=$result_indi->fields['id_desc_indicador'];
                 	$nombre=$result_indi->fields['descripcion'];
                 ?>
                   <option value=<?=$id_indi;?> ><?=$nombre?></option>
                 <?$result_indi->movenext();
                 }?>
      </select>
      
      <input type="text" name="valor" value="" style="width=60px">
		
	  <INPUT type="submit" name="guardar" value="Guardar" onclick="return control_datos()">		
	</td>
</tr>
</table>
<br>
<table border=1 width="100%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
<tr id="ma">
 <td><font size="1"> DESEABLE </font></td>
 <td><font size="2"> INDICADORES </font></td>
 <td> <font size="1">ENERO </font></td>
 <td> <font size="1">FEBRERO</font></td>
 <td> <font size="1">MARZO</font></td>
 <td> <font size="1">ABRIL</font></td>
 <td> <font size="1">MAYO</font></td>
 <td> <font size="1">JUNIO </font></td>
 <td> <font size="1">JULIO </font></td>
 <td> <font size="1">AGOSTO </font></td>
 <td> <font size="1">SEPTIEMBRE </font></td>
 <td> <font size="1">OCTUBRE </font></td>
 <td> <font size="1">NOVIEMBRE </font></td>
 <td> <font size="1">DICIEMBRE </font></td>
</tr>

<?
while (!$result->EOF){ //itera hasta que se genere el ultimo indocador
$id=$result->fields['id_desc_indicador'];//lo usa en la consulta de abajo

//consulta que recupera los indicadores que tenga de acuerdo al año y al tipo de indicador
$sql="SELECT indicadores.id_desc_indicador, indicadores.mes, indicadores.anio, indicadores.valor 
      FROM calidad.indicadores 
	  WHERE (indicadores.id_desc_indicador=$id) AND (indicadores.anio=$cmd)
	  Order By indicadores.mes ASC";
$sat_cliente= sql($sql) or fin_pagina();
/*
*el while itera tantos indicadores existan
*dentro de while verifica el mes que pertenece el indicador y lo pone.
*luego si el mes de indicador corresponde al correcto lo imprime y avanza un lugar sino no avanza (se podria imprimir un 0)
*se asegura de avanzar 12 veces por la cantidad de meses
*/
?> 
 <tr align="center"> <!--cargo una fila en la grilla-->
 <? 
 	if (($result->fields['id_desc_indicador']==1) or  ($result->fields['id_desc_indicador']==6) or ($result->fields['id_desc_indicador']==7) or  
 		($result->fields['id_desc_indicador']==9) or  ($result->fields['id_desc_indicador']==10)){
 		$formato_txt="";
 	}
 	else{
 	 $formato_txt="%";
 	}
 	
 	if (($result->fields['id_desc_indicador']==3) or  ($result->fields['id_desc_indicador']==6) or ($result->fields['id_desc_indicador']==7) or  
 		($result->fields['id_desc_indicador']==9)or($result->fields['id_desc_indicador']==10)){
 		$forma_medicion="mayor";
 	}
 	else{
 	 	$forma_medicion="menor";
 	}
 	
 	if ( $result->fields['descripcion'] != ""){//imprime el indicador si viene distinto de vacio
 		?>
 		<td align="left">
 			<b><?=number_format($result->fields['valor_deseable'],2,',','.')?></b>
 		</td> 		
 		<td align="left"><b>
 		<a target="_blank" id=graf href='<?=encode_link("indicadores_grafico.php",array("anio"=>$cmd,"id_indi"=>$result->fields['id_desc_indicador'],"tamaño"=>"large"))?>'><?=$result->fields['descripcion']?>
 		</a></b></td>
 		<? 		
 	}
 	//else echo "<td>Vacio</td>";
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 ?>
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 1 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt;
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 2 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 3 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 4 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 5 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 6 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 7 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 8 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 9 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 10 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 11 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 <?
 if ($forma_medicion=='menor'){
 	if (($sat_cliente->fields['valor']<$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }
 else{
 	if (($sat_cliente->fields['valor']>$result->fields['valor_deseable'])&& ($sat_cliente->fields['valor']!='')){
	 	$color_indi='#FF6666';
 	}
 		else $color_indi='';
 }?> 
 <td bgcolor=<?=$color_indi?>>
 <?
 if ($sat_cliente->fields['mes'] == 12 ) {
 			echo $sat_cliente->fields['valor'] . $formato_txt; 
		 	$sat_cliente->MoveNext();
 	}
 else echo "&nbsp;";
 ?>
 </td>
 
 </tr>
<?
$result->MoveNext();//para que me imprima por el siguiente indicador
}//del while
?>
 </table> <!--termina la grilla que almacena los indicadores-->
 <br>
<?if ($cmd>=2005) { ?>

<table border=1 width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
<br>
 <tr id="ma">
 <td><font size="2"> INDICADORES </font></td>
 <td> <font size="1">ENERO </font></td>
 <td> <font size="1">FEBRERO</font></td>
 <td> <font size="1">MARZO</font></td>
 <td> <font size="1">ABRIL</font></td>
 <td> <font size="1">MAYO</font></td>
 <td> <font size="1">JUNIO </font></td>
 <td> <font size="1">JULIO </font></td>
 <td> <font size="1">AGOSTO </font></td>
 <td> <font size="1">SEPTIEMBRE </font></td>
 <td> <font size="1">OCTUBRE </font></td>
 <td> <font size="1">NOVIEMBRE </font></td>
 <td> <font size="1">DICIEMBRE </font></td>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Satisfacción Clientes</b></font></td>
<?

$anio_actual='20'.date(y);// es el anio actual

$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

if (date(y) != substr($cmd,2)) $mes_fin=12;
while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	if (($mes+1)==13){
				$mes_fecha_inicio_db = 1;
				$anio_fecha_inicio_db=$anio;
				$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes_fecha_inicio_db."/".$anio_fecha_inicio_db);
	}
	else $fecha_inicio_db=Fecha_db($dia_inicio."/".($mes+1)."/".($anio-1));
	
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql="select CAST (sum (promedio_individual)/count(promedio_individual) AS numeric (30,2)) as promedio 
		from (
			select licitacion.fecha_entrega,CAST ((res.puntaje_suma / puntaje_cantidad) AS numeric(30,2) ) as promedio_individual
			from licitaciones.licitacion 
			join licitaciones.entidad 
			using(id_entidad) 
			join licitaciones.distrito 
			using(id_distrito) 
			join (select id_licitacion from licitaciones.renglon 
				join licitaciones.historial_estados 
				using(id_renglon) 
				join licitaciones.estado_renglon 
				using(id_estado_renglon) 
				where renglon.tipo ilike '%Computadora%' and estado_renglon.nombre='Orden de Compra' group by id_licitacion
	      		) as renglones 
			using (id_licitacion) 
			join encuestas.encuesta_lic using (id_licitacion) 
				left join (select id_encuesta, CAST (sum(puntaje) AS numeric(30,2)) as puntaje_suma, count(puntaje) as puntaje_cantidad 
						from encuestas.resultados where puntaje>0 group by id_encuesta
			   		) as res 
				using (id_encuesta) 
			WHERE ((extract(year from fecha_entrega) >= extract(year from current_date)-1) and (fecha_entrega BETWEEN '$fecha_inicio_db' AND '$fecha_fin_db'))
     		) as sub1
		where promedio_individual >0 ";
	
	$result_a_a= sql($sql) or fin_pagina();
	//echo $sql;
	$mes++;?>
	
	<td><?=number_format($result_a_a->fields["promedio"],2,'.','')?></td>
	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Cumplimento de Entregas</b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

if (date(y) != substr($cmd,2)) $mes_fin=12;
while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes."/".$anio);
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql="select ((sum (entregado)*100)/sum(total)) as res
from (
(
select count (fecha_entrega) as total, id_subir 
from 
licitaciones.subido_lic_oc 
left join (
          select max (fecha_entrega) as fecha_entrega,id_subir 
	  from(
	       select id_subir,fecha_entrega
	       from licitaciones.log_renglones_oc 
               join licitaciones.renglones_oc 
               using (id_renglones_oc)
               ) as res
          group by id_subir) as a
using (id_subir)
where (fecha_entrega is not null) and (vence_oc BETWEEN '$fecha_inicio_db' AND '$fecha_fin_db')
group by id_subir
)as aa
left join
(
select count (fecha_entrega) as entregado, id_subir
from 
licitaciones.subido_lic_oc 
left join (
          select max (fecha_entrega) as fecha_entrega,id_subir 
	  from(
	       select id_subir,fecha_entrega
	       from licitaciones.log_renglones_oc 
               join licitaciones.renglones_oc 
               using (id_renglones_oc)
               ) as res
          group by id_subir) as a
using (id_subir)
where (fecha_entrega is not null) and (vence_oc BETWEEN '$fecha_inicio_db' AND '$fecha_fin_db') and ((vence_oc + 5) >= fecha_entrega)
group by id_subir
)as bb
using (id_subir)
) ";
	
	$result_a_b= sql($sql) or fin_pagina();
	//echo $sql;
	$mes++;?>
	
	<td><?=number_format($result_a_b->fields["res"],2,'.','')?>%</td>
	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Licitaciones Ganadas </b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

if (date(y) != substr($cmd,2)) $mes_fin=12;
while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	if (($mes+1)==13){
				$mes_fecha_inicio_db = 1;
				$anio_fecha_inicio_db=$anio;
				$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes_fecha_inicio_db."/".$anio_fecha_inicio_db);
	}
	else $fecha_inicio_db=Fecha_db($dia_inicio."/".($mes+1)."/".($anio-1));
	
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql1="select count(id_licitacion) as total1 from licitaciones.licitacion 
			where (fecha_apertura BETWEEN '$fecha_inicio_db' AND '$fecha_fin_db')";
	
	$sql2="select count(id_licitacion) as total2 from licitaciones.licitacion 
			where (id_estado=1 or  id_estado=3 or id_estado=7) and (fecha_apertura BETWEEN '$fecha_inicio_db' AND '$fecha_fin_db')";
	
	$result1= sql($sql1) or fin_pagina();
	$result2= sql($sql2) or fin_pagina();
	$total=($result2->fields["total2"]/$result1->fields["total1"])*100;
	//echo $sql;
	$mes++;?>
	
	<td><?=number_format($total,0,'.','').'%'?></td>
	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Ganancia (Estimado/Ganado) </b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

if (date(y) != substr($cmd,2)) $mes_fin=12;
while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	if (($mes+1)==13){
				$mes_fecha_inicio_db = 1;
				$anio_fecha_inicio_db=$anio;
				$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes_fecha_inicio_db."/".$anio_fecha_inicio_db);
	}
	else $fecha_inicio_db=Fecha_db($dia_inicio."/".($mes+1)."/".($anio-1));
	
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql1="select (sum (monto_ganado)/sum (monto_estimado)) as total from licitaciones.licitacion 
			where (id_estado=1 or  id_estado=8 or  id_estado=5 or  id_estado=9 or id_estado=6) 
					and (fecha_apertura BETWEEN '$fecha_inicio_db' AND '$fecha_fin_db')";
	$result1= sql($sql1) or fin_pagina();
	$total=$result1->fields["total"];	
	$mes++;?>	
	<td><?=number_format($total,2,'.','')?></td>	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Tiempo Promedio (en Dias) de Finalizacion de Casos. Coradir Bs. As.</b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

if (date(y) != substr($cmd,2)) $mes_fin=12;
while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes."/".$anio);
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	
	$sql="select count (idcaso) as cant_caso
			from casos.casos_cdr
			where ((fechacierre >= '$fecha_inicio_db') and (fechacierre <= '$fecha_fin_db')) and (idate=3) ";
	$result_cant= sql($sql) or fin_pagina();
	$cant_caso_mes = $result_cant->fields["cant_caso"];
	//echo $sql;
	
	$sql="select sum ((fechacierre - fechainicio)) as suma 
			from casos.casos_cdr 
			where ((fechacierre >= '$fecha_inicio_db') and (fechacierre <= '$fecha_fin_db')) and (idate=3) ";
	$result_suma_mes = sql($sql) or fin_pagina();
	$suma_mes = $result_suma_mes->fields["suma"];
	//echo $sql;
	$resultado_indi_a=0;
	if ($cant_caso_mes!=0){
		 $resultado_indi_a=($suma_mes / $cant_caso_mes);
	}
	else{
		$resultado_indi_a=0;
	}
	//echo $resultado_indi_a."   ";
	//echo number_format($resultado_indi_a,2,'.','')."  ";
	$mes++;?>
	
	<td><?=number_format($resultado_indi_a,2,'.','')?></td>
	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Casos que se Resolvieron en Una o Ninguna Visita </b> </font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes."/".$anio);
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql="select count (idcaso) as cant_caso
			from casos.casos_cdr 
			where ((fechacierre >= '$fecha_inicio_db') and (fechacierre <= '$fecha_fin_db')) and (idate=3) ";
	$result_total = sql($sql) or fin_pagina();
	$total_casos = $result_total->fields["cant_caso"];
	//echo $sql."   ";
	
	$sql="select count (idcaso) as mayor_una_visita
			from casos.casos_cdr 
			join ( select idcaso, cant_visitas.cant_visitas 
				from casos.visitas_casos 
				join ( 
					select count (idcaso) as cant_visitas, idcaso 
					from casos.visitas_casos group by idcaso )as cant_visitas 
				using (idcaso) 
				where cant_visitas >1  
				group by idcaso, cant_visitas.cant_visitas) as a 
			using (idcaso) 
			where ((fechacierre >= '$fecha_inicio_db') and (fechacierre <= '$fecha_fin_db')) and (idate=3) ";
	$result_mayor_una_visita= sql($sql) or fin_pagina();
	$mayor_una_vis1ta = $result_mayor_una_visita->fields["mayor_una_visita"];
	//echo $sql."   ";
	$numerador=0;
	$numerador=	($total_casos-$mayor_una_vis1ta);//saco lo que me interesa que son todos los casos que se resuelven en 1 o menos visitas
	
	
	$resultado_indi_b=0;
	if ($total_casos!=0){
		 $resultado_indi_b=($numerador / $total_casos);
	}
	else{
		$resultado_indi_b=0;
	}
		
	if ( (($mes == 1) || ($mes == 2) || ($mes == 3) || ($mes == 4)) && ($cmd==2005) ){//los meses uno y dos no se tienen datos por eso la condicion para que no imprima nada?>
		<td><? echo "&nbsp;";?></td>
	<?}//del if (($mes == 1) || ($mes == 2))
	else {?>	
		<td><?=(number_format($resultado_indi_b,2,'.','')*100);?>%</td>
	<?}//del else
	$mes++;//incrementa el mes
	$resultado_indi_b=0;
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Tiempo Promedio de Cobro (en dias) por Unidad Monetaria (Fecha Creacion a Fecha Cierre)</b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	if (($mes+1)==13){
				$mes_fecha_inicio_db = 1;
				$anio_fecha_inicio_db=$anio;
				$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes_fecha_inicio_db."/".$anio_fecha_inicio_db);
	}
	else $fecha_inicio_db=Fecha_db($dia_inicio."/".($mes+1)."/".($anio-1));
	
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql="select sum (diasxplata) as diasxplata 
			from (select case when (id_moneda = 1 ) then  float8( ( (date(fin_fecha) - date(fecha_factura)) * monto) ) 
	     			else   float8( ( (date(fin_fecha) - date(fecha_factura)) * (monto * 3) ) )  end as diasXplata 
      				from licitaciones.cobranzas 
      				where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_factura is not Null) and 
      				((cobranzas.fin_fecha >= '$fecha_inicio_db') and (cobranzas.fin_fecha <= '$fecha_fin_db'))
      				group by id_moneda , cobranzas.fin_fecha , cobranzas.fecha_factura, cobranzas.monto 
     			)as a ";
	$result_diasXplata= sql($sql) or fin_pagina();
	//echo $sql;
	
	$sql="select sum (plata) as plata
			from (
					select case when (id_moneda=1) then sum  (monto) 
							else sum  (monto*3) end as plata
					from licitaciones.cobranzas 
					where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_factura is not Null) and 
					((cobranzas.fin_fecha >= '$fecha_inicio_db') and (cobranzas.fin_fecha <= '$fecha_fin_db'))
					group by id_moneda
     			) as a	";
	
	$result_plata = sql($sql) or fin_pagina();
	//echo $sql;
	
	$diasXplata=$result_diasXplata->fields["diasxplata"];
	$plata=$result_plata->fields["plata"];		
	
	
	//echo "X ".$diasXplata."  ";
	//echo "plat ". $plata;
	$resultado_indi_c=0;
	if ($plata!=0){
		 $resultado_indi_c=($diasXplata / $plata);
	}
	else{
		$resultado_indi_c=0;
	}
	
	$mes++;?>
	
	<td><?=number_format($resultado_indi_c,2,'.','')?></td>
	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Tiempo Promedio de Cobro (en dias) por Unidad Monetaria (Fecha Presentacion a Fecha Cierre)</b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	if (($mes+1)==13){
				$mes_fecha_inicio_db = 1;
				$anio_fecha_inicio_db=$anio;
				$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes_fecha_inicio_db."/".$anio_fecha_inicio_db);
	}
	else $fecha_inicio_db=Fecha_db($dia_inicio."/".($mes+1)."/".($anio-1));
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql="select sum (diasxplata) as diasxplata 
			from (select case when (id_moneda = 1 ) then  float8( ( (date(fin_fecha) - date(fecha_presentacion)) * monto) ) 
	     			else   float8( ( (date(fin_fecha) - date(fecha_presentacion)) * (monto * 3) ) )  end as diasXplata 
      				from licitaciones.cobranzas 
      				where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_presentacion is not Null) and 
      				((cobranzas.fin_fecha >= '$fecha_inicio_db') and (cobranzas.fin_fecha <= '$fecha_fin_db'))
      				group by id_moneda , cobranzas.fin_fecha , cobranzas.fecha_presentacion, cobranzas.monto 
     			)as a ";
	$result_diasXplata= sql($sql) or fin_pagina();
	//echo $sql;
	
	$sql="select sum (plata) as plata
			from (
					select case when (id_moneda=1) then sum  (monto) 
							else sum  (monto*3) end as plata
					from licitaciones.cobranzas 
					where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_presentacion is not Null) and 
					((cobranzas.fin_fecha >= '$fecha_inicio_db') and (cobranzas.fin_fecha <= '$fecha_fin_db'))
					group by id_moneda
     			) as a	";
	
	$result_plata = sql($sql) or fin_pagina();
	//echo $sql;
	
	$diasXplata=$result_diasXplata->fields["diasxplata"];
	$plata=$result_plata->fields["plata"];		
	
	
	//echo "X ".$diasXplata."  ";
	//echo "plat ". $plata;
	$resultado_indi_c=0;
	if ($plata!=0){
		 $resultado_indi_c=($diasXplata / $plata);
	}
	else{
		$resultado_indi_c=0;
	}
	
	$mes++;?>
	
	<td><?=number_format($resultado_indi_c,2,'.','')?></td>
	
<?
}
?>
</tr>

<tr align="left">
	<td><font color="Blue"><b>Producción Sin Reprobación de Calidad</b></font></td>
<?
$mes_inicio=1;
if ($cmd<$anio_actual) $mes_fin=12;
else $mes_fin = date(m);

$dia=1;
$mes=$mes_inicio;
$anio=$cmd;

while ($mes<=$mes_fin){
	$dia_inicio=1;
	//echo "  ".date("d-m-y",mktime(0,0,0,$mes,$dia,$anio));
	$dia_fin = date("t",mktime(0,0,0,$mes,$dia,$anio));//recupero la cantidad de dias que tiene el mes
	
	$fecha_inicio_db=Fecha_db($dia_inicio."/".$mes."/".$anio);
	$fecha_fin_db=Fecha_db($dia_fin."/".$mes."/".$anio);
	
	$sql="select count (nro_orden) as cant_rechazadas
			from ordenes.auditorias
			join (
				select orden_de_produccion.nro_orden 
				from ordenes.orden_de_produccion 
				left join licitaciones.entidad 
				using(id_entidad) 
				WHERE estado != 'AN' and 
						((orden_de_produccion.fecha_entrega >= '$fecha_inicio_db') and (orden_de_produccion.fecha_entrega <= '$fecha_fin_db'))
     			) as a
			using (nro_orden)
			where estado = 'f' ";
	$result_cant_rechazadas= sql($sql) or fin_pagina();
	//echo $sql;
	
	$sql="select count (orden_de_produccion.nro_orden) as cant_auditadas
			from ordenes.orden_de_produccion 
			WHERE estado != 'AN' and
					((orden_de_produccion.fecha_entrega >= '$fecha_inicio_db') and (orden_de_produccion.fecha_entrega <= '$fecha_fin_db')) ";
	
	$result_cant_auditadas = sql($sql) or fin_pagina();
	//echo $sql;
	
	$numerador=($result_cant_auditadas->fields["cant_auditadas"] - $result_cant_rechazadas->fields["cant_rechazadas"]);
	$denominador=$result_cant_auditadas->fields["cant_auditadas"];		

	//echo " num ".$numerador." ";
	//echo "den ".$denominador;
	$resultado_indi_d=0;
	if ($denominador!=0){
		 $resultado_indi_d=($numerador / $denominador);
	}
	else{
		$resultado_indi_d=0;
	}
	
	if ( (($mes == 1) || ($mes == 2) || ($mes == 3) || ($mes == 4) || ($mes == 5) || ($mes == 6)|| ($mes == 7)) && ($cmd==2005)){//los meses uno y dos no se tienen datos por eso la condicion para que no imprima nada?>
		<td><? echo "&nbsp;";?></td>
	<?}//del if (($mes == 1) || ($mes == 2))
	else {?>	
		
		<td><?=(number_format($resultado_indi_d,2,'.','')*100);?>%</td>
	<?}//del else
	$mes++;?>
	
<?
}
?>
</tr>

</table>
<?} //del if para que me oculte la tabla para años distintos de 2005?>

<br>
</form>

<?fin_pagina();?>