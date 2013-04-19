<?php
require_once("../../config.php");
include ("../../lib/imagenes_stat/jpgraph.php");
include ("../../lib/imagenes_stat/jpgraph_bar.php");

if ($_POST['guardar_comentario']=='Guardar'){
	$anio_comentario=$_POST['anio_comentario'];
	$comentario=$_POST['comentario'];
	
	$sql="select * from calidad.comentario_indicador 
			where anio = '$anio_comentario'";	
	$result = sql($sql,'NO puede ejecutar la consulta de validacion de insercion');
	
	if ($result->EOF){
		$sql="insert into calidad.comentario_indicador (anio,comentario) 
				   values ('$anio_comentario','$comentario')";				
	    sql($sql,'NO puede insertar');		
	}
	else{
		$sql="update calidad.comentario_indicador set comentario='$comentario'
				where (anio='$anio_comentario')";				
	    sql($sql,'NO puede insertar');
	}	
}

echo $html_header;
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
<form action="indicadores.php" method="post" name="form_indicadores">
<? 
generar_barra_nav($datos_barra);

//genero esta consulta para saber los indicadores que hay luego genero en base a eso
$sql1="SELECT *
	   FROM calidad.desc_indicador 
	   Order By desc_indicador.id_desc_indicador ASC";
$result= sql($sql1) or fin_pagina();
?>

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
 		<?
		$link_mail=encode_link("indicadores_mail.php",array ("id_indi"=>$id));
		?>
 		<input type="button" value="e" name="email"  onclick="window.open('<?=$link_mail?>')" title="e-mail">&nbsp;
 		<a target="_blank" id=graf href='<?=encode_link("indicadores_grafico_int.php",array("anio"=>$cmd,"id_indi"=>$result->fields['id_desc_indicador'],"tamaño"=>"large"))?>'><?=$result->fields['descripcion']?>
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
<?if (($_ses_user['login']=="ferni") or ($_ses_user['name']=="Juan Manuel Baretto")) {?>
<table width="100%" align="center" cellpadding="3" cellspacing='0'>
<tr align="right">	
	<td align="right">
		<input type="button" name="$" value="$" onclick="var entrar=confirm('CUIDADO!! Usted esta por ingresar a un área de configuración de sistema de indicadores esto solo debe hacerse con autorización del directorio y el gerente de calidad.'); if ( entrar ) window.open ('indicadores_guardar.php');">
	</td>
</tr>
</table>
<?}?>
<table width="92%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
<tr align="center" id="mo">
	<td align="center">
		Comentarios
	</td>	
</tr>
<tr align="center">
	<td align="center">
		<?$sql="select * from calidad.comentario_indicador 
			where anio = '$cmd'";	
		  $result_com = sql($sql,'NO puede ejecutar la consulta de validacion de insercion');
		?>
		<textarea name="comentario" cols="140" rows="5"><?=$result_com->fields['comentario']?></textarea>
	</td>
</tr>
<tr align="center">
	<td>
		<?if (($_ses_user['login']=="ferni") or ($_ses_user['name']=="Juan Manuel Baretto")) {?>
			<INPUT type="button" name="mail" value="Enviar Mail" onclick="var entrar=confirm('¿Esta Seguro que desea Enviar Mail/s?.\n Esta Tarea se Realiza los Primeros Dias del Mes.\n Debe Tener los Indicadores Cargados del Mes Anterior'); if ( entrar ) window.open ('indicadores_mail_envia.php');">		
		<?}?>
		<INPUT type="submit" value="Guardar" name="guardar_comentario">	
		<input type="hidden" value="<?=$cmd?>" name="anio_comentario">
	</td>
</tr>
</table>
<br>
<!-- Genero otra tabla para contener los graficos-->
<table border=1 width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
<div align="center">
<?
//en result tengo todos los indicadores
$result->MoveFirst();//lo muevo al principio ya que el while anterior lo llevo a final
while (!$result->EOF){//itera hasta el ultimo indicador
	
	//realizo consuta para verificar que tenga datos para poder graficar
	$id_descripcion_indicador = $result->fields['id_desc_indicador'];
	$sql="SELECT id_indicador 
			FROM calidad.indicadores 
			where anio=$cmd and id_desc_indicador = $id_descripcion_indicador";
	$result_verifica = sql($sql) or fin_pagina();
	
	//verifico que tenga valor el inficador para poder generar el grafico
	if (!$result_verifica->EOF){
	//genera dos link a la pagina donde se genera el grafico (uno chico y otro grande)
	$link_s=encode_link("indicadores_grafico.php",array("anio"=>$cmd,"id_indi"=>$result->fields['id_desc_indicador'],"tamaño"=>"small"));
	$link_l=encode_link("indicadores_grafico_int.php",array("anio"=>$cmd,"id_indi"=>$result->fields['id_desc_indicador'],"tamaño"=>"large"));
	//ACA IMPRIME EL GRAFICO EN LA PAGINA ACTUAL REDIRECCIONA EL LINK CON AL GRAFICO CHICO Y LO IMPRIME
	echo "<a href='$link_l' target='_blank'><img src='$link_s'  border=0 align=top></a>\n";
	}//del if que me dice si tengo datos para graficar
	//avanzo result 
	$result->MoveNext();
}//del while
?>
</div>
</table> <!--finalizo la tabla que contiene el grafico-->
<br>
</form>

<?fin_pagina();?>