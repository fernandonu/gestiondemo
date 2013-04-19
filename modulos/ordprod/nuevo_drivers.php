<?
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: cestila $
$Revision: 1.14 $
$Date: 2004/11/18 18:35:42 $
*/

require_once("../../config.php");
function llenarModelo() {
         global $modelo;
         $fp=opendir("/extra/admin/coradir/download/drivers");
		 while ($files=readdir($fp)) {
                if (is_dir("/extra/admin/coradir/download/drivers/".$files) and $files != "." and $files != ".." and $files != "CVS" and $files != "index.html") {
                    echo "<option value='$files'";
                    if ($modelo==$files) echo " selected";
                    echo ">$files</option>\n";
                }
         }
         closedir();
}
function llenarDriver() {
         global $file,$modelo;
         $fp=opendir("/extra/admin/coradir/download/drivers/$modelo");
         while ($files=readdir($fp)) {
                if (!is_dir("/extra/admin/coradir/download/drivers/".$files) and !is_dir("/extra/admin/coradir/download/drivers/$modulo/".$files) and $files != "." and $files != ".." and $files != ".cvsignore" and $files != "CVS" and $files != "index.html") {
                    echo "<option value='$files'";
                    if ($file==$files) echo " selected";
                    echo ">$files</option>\n";
                }
         }
         closedir();
}
if ($_POST["modo"]=="Guardar driver >>") {
	if (!$file)
         $error.="Debe seleccionar un archivo.<br>";
    if (!$descripcion)
         $error.="Debe Poner una descripcion al driver.<br>";
    if (!es_numero($size))
         $error.="Debe ingresar el tamaño del archivo.<br>";
    if ($_POST["bios"]==1) $tipo="'bios'";
	else $tipo="NULL";
	$sql[]="INSERT INTO archivo_drivers (modelo,descripcion,archivo,size,tipo,sync) "
            ."VALUES ('$modelo','$descripcion','$file','$size',$tipo,1)";
	if (!$error) {
		sql($sql) or fin_pagina();
		Aviso("Los datos se ingresaron correctamente");
	}
    else
        error($error);
}
echo $html_header;
?>
<br>
<form name='nuevo_drivers' action='nuevo_drivers.php' method=post enctype='multipart/form-data'>
<input type=hidden name=volver value='<? echo $volver; ?>'>
<table align=center border=1 cellspacing=0 cellpadding=3 bgcolor=<? echo $bgcolor2; ?> width=500>
<tr>
	<td colspan=3>
		<center><h3>Agregar Drivers</h3></center>
	</td>
</tr>
<tr>
    <td width="100%" style="border-bottom-style: none; border-bottom-width: medium">
    <p style="margin: 4"><font face="Trebuchet MS" size="2">Complete los los
    campos siguientes con la descripción del driver a ingresar en el sistema.</font></p>
    <p style="margin: 4"><font face="Trebuchet MS" size="2"><b>
    <font color="#3399FF">Recomendación:</font></b> Comprima el driver en un
    solo archivo (.ZIP o .EXE) antes de agregarlo al sistema, de esta manera
    resultará mucho mas fácil para acceder al mismo desde la WEB.</font></td>
</tr>
<tr>
    <td width="100%" style="border-top-style: none; border-top-width: medium; border-bottom-style: none; border-bottom-width: medium">&nbsp;</td>
</tr>
<tr>
	<td width="100%" align=center style="border-top-style: none; border-top-width: medium; border-bottom-style: none; border-bottom-width: medium">
	    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="59%" id="AutoNumber2" height="37">
		<tr>
			<td width="100%" height="17" bgcolor="#6C6C9E" style="border-bottom-style: none; border-bottom-width: medium">
            <p style="margin: 2">
            <font color="#FFFFFF" face="Trebuchet MS" size="2">Complete los
            siguientes datos.</font></td>
		</tr>
        <tr>
			<td width="100%" height="19" style="border-top-style: none; border-top-width: medium">
				<form method="POST" action="index.php" enctype='multipart/form-data'>
				<input type=hidden name='modulo' value='drivers'>
				<input type=hidden name='modo' value='admin'>
				<input type=hidden name='cmd' value='Agregar >>'>
				<p style="margin: 6"><font face="Trebuchet MS" size="2">Modelo (
				Ej. Motherboard K7VMM+).</font></p>
				<p style="margin: 6"><font face="Trebuchet MS">
				<select name=modelo onChange="form.submit();">
				<option></option>
<?
llenarModelo();
?>
				</select>
				<p style="margin: 6"><font face="Trebuchet MS" size="2">Archivo (.ZIP
				o .EXE)
				del driver.</font></p>
				<p style="margin: 6"><font face="Trebuchet MS">
				<select name=file onChange="form.submit();">
				<option></option>
<?
llenarDriver();
?>
				</select>&nbsp;&nbsp;&nbsp;Tamaño: <input type="text" name="size" size="10" value="<? echo filesize("/extra/admin/coradir/download/drivers/$modelo/$file"); ?>"> Bytes</font></p>
				<p style="margin: 6"><font face="Trebuchet MS" size="2">
				<input type=checkbox name=bios value=1<? if ($_POST["bios"]==1) echo " checked"; ?>> Actualizacion de Bios.</font></p>
				<p style="margin: 6"><font face="Trebuchet MS" size="2">
				Observaciones (Descripción general del driver).</font></p>
				<p style="margin: 6"><font face="Trebuchet MS">
				<input type="text" name="descripcion" value="<? echo $descripcion; ?>" size="54"></font></p>
				<p style="margin: 6" align="center"><font face="Trebuchet MS">
				<? if ($volver) {?>
					<input type="Button" value="<< Volver" onClick="window.location='<?echo $volver;?>';">
				<? } ?>
				<input type="submit" value="Guardar driver &gt;&gt;" name="modo"></font></p></div>
            </td>
		</tr>
        </table>
	</td>
</tr>
<tr>
    <td width="100%" style="border-top-style: none; border-top-width: medium; border-bottom-style: none; border-bottom-width: medium">&nbsp;</td>
</tr>
</table></form>
<?
fin_pagina();
?>