<?
/*
$Author: cestila $
$Revision: 1.1 $
$Date: 2004/02/05 21:47:23 $
*/
require_once("../../config.php");
echo $html_header;

/*function llenar(){
         global $tipo_drivers,$tipo;
         while (list($key,$valor)=each($tipo_drivers)) {
                echo "<option value='$key'";
                if ($tipo==$key) echo " selected";
                echo ">$valor</option>\n";
         }
}*/
function llenarModelo() {
         global $modelo;
		 $path="../../../coradir";
         $fp=opendir("$path/download/drivers/");
         while ($files=readdir($fp)) {
                if (is_dir("$path/download/drivers/".$files) and $files != "." and $files != ".." and $files != "CVS" and $files != "index.html") {
                    echo "<option value='$files'";
                    if ($modelo==$files) echo " selected";
                    echo ">$files</option>\n";
                }
         }
         closedir();
}
function llenarDriver() {
         global $file,$modelo;
         $path="../../../coradir";
		 $fp=opendir("$path/download/drivers/$modelo");
         while ($files=readdir($fp)) {
                if (!is_dir("$path/download/drivers/".$files) and !is_dir("$path/download/drivers/$modulo/".$files) and $files != "." and $files != ".." and $files != ".cvsignore" and $files != "CVS" and $files != "index.html") {
                    echo "<option value='$files'";
                    if ($file==$files) echo " selected";
                    echo ">$files</option>\n";
                }
         }
         closedir();
}
if ($_POST["cmd1"]) {
    $file=$_POST["file"];
	$size=$_POST["size"];
	$tipo=$_POST["tipo"];
	$modelo=$_POST["modelo"];
	$descripcion=$_POST["descripcion"];
	
	if (!$file)
         $error.="Debe seleccionar un archivo.<br>";
    if (!$descripcion)
         $error.="Debe Poner una descripcion al driver.<br>";
    if (!es_numero($size))
         $error.="Debe ingresar el tamaño del archivo.<br>";
    $sql="INSERT INTO drivers (tipo,modelo,descripcion,archivo,size) "
            ."VALUES ('$tipo','$modelo','$descripcion','$file','$size')";
    if (!$error)
         $db->execute($sql) or $error .= $db->errormsg();
    if ($error)
        error($error);
    else
        Aviso("Los datos se ingresaron correctamente");
}
?>
<br>
<table align=center border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="99%" id="AutoNumber1">
    <tr>
      <td width="100%" bgcolor="#6C6C9E">
      <p style="margin: 2"><font face="Trebuchet MS" size="2" color="#FFFFFF">
      <a name="inicio"></a>Agregar nuevos Drives</font></td>
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
      <td width="100%" style="border-top-style: none; border-top-width: medium; border-bottom-style: none; border-bottom-width: medium">
      <div align="center">
        <center>
        <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="59%" id="AutoNumber2" height="37">
          <tr>
            <td width="100%" height="17" bgcolor="#6C6C9E" style="border-bottom-style: none; border-bottom-width: medium">
            <p style="margin: 2">
            <font color="#FFFFFF" face="Trebuchet MS" size="2">Complete los
            siguientes datos.</font></td>
          </tr>
          <tr>
            <td width="100%" height="19" style="border-top-style: none; border-top-width: medium">
            <form method="POST" action="drivers_nuevo.php" enctype='multipart/form-data'>
              <p style="margin: 6"><font face="Trebuchet MS" size="2">Modelo (
              Ej. Motherboard K7VMM+).</font></p>
              <p style="margin: 6"><font face="Trebuchet MS">
              <select name=modelo onChange="form.submit();">
              <option></option>
<?
llenarModelo();
?>
              </select></p>
              <p style="margin: 6"><font face="Trebuchet MS" size="2">Archivo (.ZIP
              o .EXE)
              del driver.</font></p>
              <p style="margin: 6"><font face="Trebuchet MS">
              <select name=file onChange="form.submit();">
              <option></option>
<?
llenarDriver();
?>
              </select>&nbsp;&nbsp;&nbsp;Tamaño: <input type="text" name="size" size="10" value="<? echo filesize("../../../coradir/download/drivers/$modelo/$file"); ?>"> Bytes</font></p>
              <p style="margin: 6"><font face="Trebuchet MS" size="2">
              Observaciones (Descripción general del driver).</font></p>
              <p style="margin: 6"><font face="Trebuchet MS">
              <input type="text" name="descripcion" value="<? echo $descripcion; ?>" size="54"></font></p>
              <p style="margin: 6" align="center"><font face="Trebuchet MS">
              <input type="submit" value="Guardar driver &gt;&gt;" name="cmd1"></font></p></div>
            </form>
            </td>
          </tr>
        </table><br>
        </center>
      </div>
      </td>
    </tr>
  </table>