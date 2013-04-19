<?
/*
$Author: marco_canderle $
$Revision: 1.27 $
$Date: 2006/01/04 12:15:41 $
*/
if (ereg("/login.php",$_SERVER["SCRIPT_NAME"])) {
	$tmp=explode("/login.php",$_SERVER["SCRIPT_NAME"]);
	$html_root = $tmp[0];
}
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Sistema de Gestión 2007</title>
<head>
<link rel="icon" href="<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME']).$html_root; ?>/favicon.ico">
<link REL='SHORTCUT ICON' HREF='<? echo ((($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME']).$html_root; ?>/favicon.ico'>

<link type='text/css' href='<? echo $html_root; ?>/lib/estilos.css' REL='stylesheet'>
</head>
</head>

<body style="overflow:hidden;" onLoad="javascript: document.frm.username.focus();" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0" background="<?="$html_root/imagenes/fondo.jpg"?>">
<form action='index.php' method='post' name='frm'>
<input type="hidden" name="resolucion_ancho" value="">
<input type="hidden" name="resolucion_largo" value="">
<div align="center">
	<table border="0" cellpadding="0" cellspacing="0" height="100%" id="table1">
		<tr>
			<td height="50%">&nbsp;</td>
		</tr>
		<tr>
			<td background="<?="$html_root/imagenes/login_nuevo.jpg"?>" height="375" width="465" valign="top">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table2">
			<tr>
				<td height="220" valign="top">
				<p align="center" style="margin-top: 20px; margin-bottom: 10px">
				<b><font face="Arial" size="5" color="#FFFFFF">Sistema de 
				Gestión 2007</font></b></p>
				</td>
			</tr>
			<tr>
				<td>
				<p style="margin-left: 50px; margin-top: 5px; margin-bottom: 15px">
				<font face="Arial" size="2">Ingrese su nombre de usuario y 
				contraseña.</font></td>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table3">
					<tr>
						<td width="179" align="right">
						<p style="margin-top: 6px; margin-bottom: 6px"><b>
						<font face="Arial" size="2">Nombre de usuario:</font></b></td>
						<td>&nbsp;<INPUT name=username 
          AUTOCOMPLETE="off" style="border-style: solid; border-width: 1px" size="30" tabindex="1"></td>
					</tr>
					<tr>
						<td width="179" align="right">
						<p style="margin-top: 6px; margin-bottom: 6px"><b>
						<font face="Arial" size="2">Contraseña:</font></b></td>
						<td>&nbsp;<INPUT type=password name=password 
          AUTOCOMPLETE="off" style="border-style: solid; border-width: 1px" size="30" tabindex="2"></td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td>
				<p align="center">
				&nbsp;<INPUT type=submit value="  Ingresar &gt;" name=loginform style="font-family: Tahoma; font-size: 10pt" tabindex="3"></td>
			</tr>
		</table>
		</td>
		</tr>
		<tr>
			<td height="50%" valign="bottom">
			<p align="center" style="margin-top: 0; margin-bottom: 5px"><b>
			<font color="#FFFFFF" face="Arial" size="1">Copyright © 2003-2007 Coradir 
			S.A. - División Software.</font></b></td>
		</tr>
	</table>
</div>
<script>
//guardamos la resolucion de la pantalla del usuario en los hiddens para despues recuperarlas
//y guardarlas en las variable de sesion $_ses_user
document.all.resolucion_ancho.value=screen.width;
document.all.resolucion_largo.value=screen.height;

</script>
</body>
</form>
</html>
