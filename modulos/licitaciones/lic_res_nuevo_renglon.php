<? include_once("../../config.php");
/*
Author: GACZ

MODIFICADA POR
$Author: elizabeth $
$Revision: 1.12 $
$Date: 2005/02/08 19:44:15 $
*/

//print_r($parametros);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
extract($_POST,EXTR_SKIP);
//visibilidad para el boton Continuar
$visible=0;

$link=encode_link("lic_res_nuevo_renglon.php",array('id_licitacion'=>$id_licitacion));

if ($guardar)
{
	$q="insert into renglon (id_licitacion,codigo_renglon,titulo,cantidad,tipo) values ".
	"($licitacion,'$renglon','$titulo',$cantidad,'$tipo')";
	if ($db->Execute($q))
	{ $msg="<center><b>SU RENGLON SE GUARDO EXITOSAMENTE</b></center>"; 
 
	}
	else 
	 $msg="<center><b>NO SE PUDO GUARDAR EL RENGLON</b></center>".$db->ErrorMsg()." <br>$q";
	$visible=1; 
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Agregar renglon de Resultado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
	font-weight: bold;
}
-->
</style>
</head>
<body bgcolor=#E0E0E0 onUnload="/*window.opener.document.all.agregar_r.disabled=0;*/">
<script>
function enviar()
{
document.all.guardar.value=1;
document.all.licitacion.value=window.opener.document.all.id_lic.value;
if (isNaN(document.all.cantidad.value) || (document.all.cantidad.value==""))
{
	alert ('Debe poner un numero valido como cantidad');
	return false;
}
if (document.all.renglon.value=="")
{
 alert ('Ingrese el campo renglon');
 return false;
}
if (document.all.titulo.value=="")
{
 alert ('Ingrese el titulo');
 return false;
}

window.opener.document.all.agregar_r.disabled=0;
return true;
}

</script>
<form name="formulario" id="formulario" method="post" action="<? echo $link ?>">
 <table border="0" cellspacing="5" cellpadding="2" align="center" width="90%">
  <tr> 
    <td height="10%"align="left" width="100%"><font size=-1><?=$msg ?></font></td>
    <td align="right"></td>
  </tr>
</table>

 <table align="center" width="100%">
    <tr bgcolor="#5090C0" class="tablaEnc"> 
      <td colspan="4" align="center"> <b> Información del 
        Renglon </b></td>
    </tr>
    <tr> 
      <td> Renglón </td>
      <td> <input type="text" name="renglon"  value="<?=$_POST['renglon']?>" size="10"> </td>
      <td> Título </td>
      <td> <input type="text" name="titulo"  value="<?=$_POST['titulo']?>" size="40"> </td>
    </tr>
    <tr> 
      <td> Cantidad: </td>
      <td> <input name="cantidad" type="text" id="cantidad" size="5" value="<?=$_POST['cantidad']?>"> </td>
      <td>Tipo</td>
      <td><select name="tipo">
      <option>Computadora Matrix</option>
      <option>Computadora Enterprise</option>
      <option>Impresora</option>
      <option>Otro</option>
      </select>
      </td>
    </tr>
  </table>

<br>
  <table width="20%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr>
    <td><input name="boton" type="submit" id="boton" value="Aceptar" onclick="return (enviar())"></td>
    <td><input name="boton" type="submit" id="boton" value="Cancelar" onclick="/*window.opener.document.all.agregar_r.disabled=0;*/window.close()"></td>
    <?
     if($visible)
     {?>   <td><input name="boton" type="button" id="boton" value="Continuar" <?=$visible?> onclick="window.opener.document.location.reload(); window.opener.focus(); window.close();"></td>
     <?}?>
  </tr>
</table>
   <input type="hidden" name="guardar" value="0">
   <input type="hidden" name="licitacion" >
<hr>
<table width="100%" border="0" cellspacing="1" cellpadding="1">
<tr class="tablaEnc">
      <td width="20%" align="center">Renglon</td>
      <td width="10%"><div align="center">Cantidad</div></td>
      <td width="57%"><div align="center">Titulo</div></td>
</tr>
</table>
<div style="position:relative; width:100%; height:40%; overflow:auto">
<table width="100%" border="0" cellspacing="1" cellpadding="1">

<?php
$query="SELECT * FROM renglon WHERE id_licitacion=$id_licitacion";
$datos_lic=$db->execute($query) or die($query);

 while (!$datos_lic->EOF)
  {
?>
<tr>
      <td width="20%" align="center"><?=$datos_lic->fields['codigo_renglon'] ?></td>
      <td width="10%" align="center"><?=$datos_lic->fields['cantidad'] ?></td>
      <td width="57%" align="center"><?=$datos_lic->fields['titulo'] ?></td>
</tr>

  <?$datos_lic->MoveNext();
  }
  ?>
</form>
</body>
</html>