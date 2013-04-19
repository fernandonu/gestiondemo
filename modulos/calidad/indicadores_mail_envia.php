<?php
require_once("../../config.php");

$mes = date(m) - 1;
$anio = "20".date(y);

if (date(m)==1) { $mes = 12; $anio=$anio-1;}

echo $html_header;
$sql="SELECT indicadores.id_desc_indicador, indicadores.mes, indicadores.anio, indicadores.valor, desc_indicador.descripcion, mail
	FROM calidad.desc_indicador 
	JOIN calidad.indicadores 
	join calidad.mail_indicador
	using (id_desc_indicador)  
	using (id_desc_indicador)  
	WHERE (indicadores.anio='$anio') and (indicadores.mes='$mes') 
	order by id_desc_indicador ";
$result= sql($sql) or fin_pagina();
//echo $sql;
?>

<br>
<table border=1 width="70%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
<tr id="ma">
	<td width="50%">
	 	Indicadores Para el Mes <?=$mes?> y el Año <?=$anio?>
	</td>
	<td width="20%">
		Valor
	</td>
	<td width="30%">
		Destinatario del Mail
	</td>
</tr>

<?
$result->MoveFirst();
$i=0;
while (!$result->EOF){ //itera hasta que se genere el ultimo indocador

if (($result->fields['id_desc_indicador']==1) or  ($result->fields['id_desc_indicador']==6)
 		or  ($result->fields['id_desc_indicador']==7 or  ($result->fields['id_desc_indicador']==9) or  ($result->fields['id_desc_indicador']==10))){
 		$formato_txt="";
 	}
 	else{
 	 $formato_txt="%";
 	}
?>
<tr>
	<td>
	 	<?=$result->fields['descripcion']?>
	</td>
	<td>
		<?=$result->fields['valor']. $formato_txt;?>
	</td>
	<td>
		<?=$result->fields['mail']?>
	</td>
</tr>
<?
	$mail_local=$result->fields['mail'];
	//$mail_local="ferni@coradir.com.ar";
	//echo $mail_local . " ";
	$asunto_local="Indicadores del Mes: $mes y Año: $anio";
	//echo $asunto_local. "  ";
	$contenido_local="El Indicador: " . $result->fields['descripcion'] . "  Tiene un valor de: " . $result->fields['valor'] . $formato_txt;
	//echo $contenido_local."  ";
	enviar_mail ($mail_local,$asunto_local,$contenido_local,'','','');
	$result->MoveNext();
	$i++;
}
?>
</table>
<br>
<table align="center">
<tr align="center">
	<td align="center"> 
		<strong> <font size="2" color="Red">Se Enviaron <?=$i?> Mail/s </strong></font>
	</td>
</tr>
</table>
<?
fin_pagina();
?>
