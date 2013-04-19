<?
/*
Autor: GACZ

MODIFICADA POR
$Author: cesar $
$Revision: 1.32 $
$Date: 2005/02/09 23:01:32 $
*/

require_once("../../config.php");
extract($_POST);

if ($_POST['btn_guardar']  || $_POST['btn_generar'])
 include("cd_oferta_proc.php");

$id_lic=$_POST['id_lic'] or $id_lic=$parametros['id_lic'];//945;//1210;//826;//

//datos de la licitacion y oferta
$q="select * from licitacion join entidad using(id_entidad) left join cdoferta using(id_licitacion) where id_licitacion=$id_lic";
$licitacion=sql($q) or fin_pagina();

//datos de los archivos de licitacion
$q="select a1.*,a2.idarchivo as id2,a2.id_tipo,a2.nuevo_nombre from archivos a1 left join archivos_cdoferta a2 using(idarchivo) where id_licitacion=$id_lic";
$archivos=sql($q) or fin_pagina();

//selecciono los tipos de archivos posibles
$q="select * from tipo_archivo";
$tipos=sql($q) or fin_pagina();

//selecciono los titulos de licitacion
$q="select * from titulos where id_licitacion=$id_lic order by nro_titulo";
$titulos=sql($q) or fin_pagina();

//selecciono los registros de modificaciones
$q="select * from log_oferta where id_licitacion=$id_lic order by id_log desc";
$log=sql($q) or fin_pagina();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Armado del CD de Oferta</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header ?>
<!--
</head>
<body> -->
<script>
function chk_form()
{
 for (i=0; i < document.all.total_archivos.value; i++)
 {
  if (eval("document.all.select_tipo_"+i+".value")==-1 &&
  		eval("document.all.chk_"+i+".checked"))
  	{
  		alert ("Por favor seleccione un tipo de archivo \npara cada uno de los archivos");
  		return false;
  	}
 
 }
 return true;

}
</script>
<style type="text/css">
<!--
.unnamed1 {
	font-size: 14px;
	font-weight: bold;
	font-family: "Times New Roman", Times, serif, Tahoma;
}
-->
</style>
<form name="form1" method="post" action="cd_oferta.php">
<!-- tabla de registro -->
<? if ($log->RowCount()) { ?>
<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> "  >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
while (!$log->EOF)
{
	//esto es para saber si se modifico despues de generar la imagen
	//busco solo el primer log de modificacion
	if ($log->fields['tipo_log']=="de modificación" && !$first)
		$first=1;
	elseif ($log->fields['tipo_log']=="de imagen")
	{ 
		switch ($first)
		{
			case 1:	$generar_imagen=1;break;
			default: $first=2;
		}
	}			
	
?>
<tr>
      <td height="20" nowrap>Fecha <?=$log->fields['tipo_log'].": ".date2("LH",$log->fields['fecha'])?> </td>
      <td nowrap > Usuario : <?=$log->fields['user_name'] ?> </td>
</tr>
<?
 $log->MoveNext();
}
?>
</table>
</div>
<hr>
<? } //fin log ?>
  <h2 align="center">Armado del CD de Oferta<br>Licitaci&oacute;n ID: <?=$id_lic ?></h2>
<center>
<?
	echo "<b>$msg</b><br>";
	if ($generar_imagen)
	{
?>
   <b style="color:red">Se debe generar la imagen nuevamente<br></b>
<? 
	}
?>
</center>
  <table width="90%" border="1" align="center" cellpadding="1" cellspacing="1">
    <tr height="25" id=mo> 
      <td width="3%">&nbsp;</td>
      <td width="46%" align="center">Archivos de licitaci&oacute;n
      </td>
      <td width="32%" align="center" title="Nombre sin extension" >Nombre del 
        archivo en el CD *</td>
      <td width="19%" align="center">Tipo de archivo</td>
    </tr>
    <?
$i=0;
while (!$archivos->EOF)
{
        $tipos->movefirst();
	if (!eregi(".iso$",$archivos->fields["nombre"]) )
	{
	
?>
    <tr id=ma> 
      <td> <input name="chk_<?=$i ?>" type="checkbox" value="1" <? if ($archivos->fields["id2"]) echo "checked"?> > 
        <input type="hidden" name="idarchivo_<?=$i ?>" value="<?=$archivos->fields["idarchivo"] ?>"> 
      </td>
      <td><span id="span_<?=$i ?>"> 
        <?=$archivos->fields["nombre"]?>
        </span></td>
      <td align="center">
		<input name="nuevo_nombre_<?=$i ?>" type="text" value="<?=$archivos->fields["nuevo_nombre"]?>" >
      </td>
      <td align="center"> <select name="select_tipo_<?=$i++ ?>" >
          <option value="-1">Seleccione</option>
          <?= make_options($tipos,"id_tipo","nbre_tipo",$archivos->fields["id_tipo"]) ?>
        </select> </td>
    </tr>
    <?
	}
	//envio por post el id del iso
	else
	{
?>
    <input type="hidden" name="id_iso_file" value="<?=$archivos->fields["idarchivo"] ?>">
    <?
	}
	$archivos->MoveNext();
}
?>
  </table>
<center>
    <font class="unnamed1">* Los nombres de archivos 
    deben ser sin extensión y con un máximo de 14 caracteres (incluyendo espacios)</font>
  </center>
  <br></br>
  
  <table width="90%" border="1" align="center" cellpadding="1" cellspacing="1">
<?
//si hay titulos cargados
if ($titulos->RecordCount())
{
	while (!$titulos->EOF)
	{
	
?>
  <tr> 
      <td width="20%" id=ma>Titulo <?=$titulos->fields['nro_titulo'] ?></td>
      <td width="80%"> 
        <input type="text" name="titulos[]" value="<?=$titulos->fields["titulo"] ?>" style="width:550">
  		  <input type="hidden" name="idtitulos[]" value="<?=$titulos->fields['id_titulo'] ?>">
      </td>
    </tr>
<? 
	 $titulos->MoveNext();
	}
}
else
{
?>
   <tr> 
      <td width="20%" id=ma>Titulo 1</td>
      <td width="80%"> 
        <input type="text" name="titulos[]" value="<?="Entidad:  " .$licitacion->fields["nombre"] ?>" style="width:550">
      </td>
    </tr>
    <tr> 
      <td id=ma >Titulo 2</td>
      <td><input type="text" name="titulos[]" value="<?="Nº Licitaci&oacute;n:  ".$licitacion->fields["nro_lic_codificado"] ?>" style="width:550"> </td>
    </tr>
    <tr> 
      <td id=ma>Titulo 3</td>
      <td>
        <input type="text" name="titulos[]" value="<?="Apertura:  ". date2("lhm",$licitacion->fields["fecha_apertura"]) ?>" style="width:550"></td>
    </tr>
<?
}
?>
  </table>
  <p align="center"> 
    <input name="btn_guardar" type="submit" id="btn_guardar" value="Guardar" onclick="return chk_form()" style="width:110">
	&nbsp;&nbsp;
    <input name="btn_generar" type="submit" id="btn_generar" value="Generar imagen" onclick="return chk_form()" style="width:110">
	&nbsp;&nbsp;
    <input name="btn_volver" type="button" id="btn_volver" value="Volver" onclick="<?=($refresh)?"window.opener.location.reload();":"" ?>window.close()" style="width:110">
  </p>
  <input type="hidden" name="id_lic" value="<?=$id_lic ?>">
  <input type="hidden" name="total_archivos" value="<?=$i ?>">
  <input type="hidden" name="total_titulos" value="<?= (($titulos->RecordCount())?$titulos->RecordCount():3)  ?>">
  
</form>
<?= fin_pagina(); ?>
<!-- </body>
</html>-->