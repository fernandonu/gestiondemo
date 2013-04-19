<?
/*
Autor: GACZ

MODIFICADA POR
$Author: fernando $
$Revision: 1.5 $
$Date: 2005/03/11 20:45:24 $
*/
require_once("../../config.php");

extract($_POST,EXTR_SKIP);

if ($boton=="Guardar" && $id_renglon)
{
	 $q="update renglon set resumen='$resumen' where id_renglon=$id_renglon";
     //sql($q);
	 if (sql($q))
	 	$command="<script>window.close()</script>";
	 else
	 	$command="<script>alert('No se pudo actualizar')</script>";

}
if ($parametros)
{
	$id_renglon=$parametros['id_renglon'];
	$onclickguardar=$parametros['onclickguardar'];
	$q="select id_renglon,resumen,codigo_renglon from renglon where id_renglon=$id_renglon";
	if ($id_renglon > 0)
 		$renglon=sql($q) or fin_pagina();
}
else
	die;

$codigo_renglon=$_GET['codigo_renglon'] or   $codigo_renglon=$renglon->fields['codigo_renglon'];
$resumen=$_GET['resumen'] or  $resumen=$renglon->fields['resumen'];

?>
<html>
<?=$command ?>
<head>
<title>Resumen de Renglon</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header ?>
<form name="form1" method="post" action="<?=encode_link($_SERVER['SCRIPT_NAME'],array("onclickguardar"=>$onclickguardar )) ?>">
<center>
    <h4> Resumen del Renglon codigo: <?=($codigo_renglon)?$codigo_renglon:" SIN CODIGO AUN "?> </h4>
    <p>
      <textarea name="resumen" cols="60" rows="7" wrap="PHYSICAL" id="resumen"><?=$resumen?></textarea>
    </p>
    <p>
      <input name="boton" type="submit" value="Guardar" onclick="<?=$onclickguardar ?>" >
      &nbsp;
      <!--  <input name="boton" type="submit" value="Guardar"  > -->
      <input name="boton" type="button" value="Cerrar" onclick="window.close()">
    </p>
</center>
<input type="hidden" name="id_renglon" value="<?=$id_renglon?>">
</form>
</body>
</html>
<?=fin_pagina();?>