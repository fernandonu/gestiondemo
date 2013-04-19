<?php
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2003/09/03 21:35:46 $
*/
require_once("../../config.php");
$error=0;

  if ($BuscarUsuario) {
        $Usuario=$BuscarUsuario;
  }
  else {
          $Usuario=$_POST['login'] or $Usuario=$_POST["ModLogin"];
  }
  if (!$Usuario) {
        Error("No seleccionó ningún usuario.");
        exit;
  }
if ($_POST['ModificarDatos']) {
        if (!$HTTP_POST_VARS["ModLogin"]) { Error("Falta ingresar el Login."); }
        if (!$HTTP_POST_VARS["ModNombre"]) { Error("Falta ingresar el Nombre."); }
        if (!fechaOk($HTTP_POST_VARS["ModFechaIni"])) { Error("Formato de la Fecha de Inicio incorrecto."); }
        if (!fechaOk($HTTP_POST_VARS["ModFechaVen"])) { Error("Formato de la Fecha de Vencimiento incorrecto."); }
        $ModLogin=$HTTP_POST_VARS[ModLogin] or $error=1;
        $ModAbono=$HTTP_POST_VARS[ModAbono] or $error=1;
        $ModEstado=$HTTP_POST_VARS[ModEstado] or $error=1;
        $ModIDCliente=$HTTP_POST_VARS[ModIDCliente] or $error=1;

        $ModNombre=$HTTP_POST_VARS[ModNombre] or $error=1;
        $ModTelefono=$HTTP_POST_VARS[ModTelefono] or $error=1;
        $ModDireccion=$HTTP_POST_VARS[ModDireccion] or $error=1;
        $ModFechaIni=$HTTP_POST_VARS[ModFechaIni] or $error=1;
        $ModFechaVen=$HTTP_POST_VARS[ModFechaVen] or $error=1;
        $ModObservaciones=$HTTP_POST_VARS[ModObservaciones] or $error=1;
        $ModFechaIni=ConvFecha($ModFechaIni);
        $ModFechaVen=ConvFecha($ModFechaVen);
//echo "$ModFechaIni $ModFechaVen"; exit;
        if ($error==1) {
                Error("Error al modificar los datos");
                exit;
        }
        $comando="UPDATE internet.internet SET Login='$ModLogin', Nombre='$ModNombre', Telefono='$ModTelefono', Direccion='$ModDireccion', FechaIni='$ModFechaIni', FechaVen='$ModFechaVen', Abono='$ModAbono', Aclaraciones='$ModObservaciones', Estado='$ModEstado' WHERE IDCliente=$ModIDCliente";
        $result=$db->Execute($comando);
        Aviso("Los datos se actualizaron correctamente");
        if ($result) {
                //include_once("internet_view.php");
                //exit;
                echo $comando;
        }
        else { Error("Se produjo un error al actualizar la base de datos:<br>".mysql_error()); }
}
  $comando="SELECT * FROM internet.internet WHERE Login='$Usuario'";
  $result=$db->Execute($comando);
  if ($result->RecordCount() == 0) { Error("No existe el usuario $Usuario"); exit; }
  $row=$result->FetchRow();
  $IDCliente=$row[10] or $IDCliente="&nbsp;";
  $Login=$row[0] or $Login="&nbsp;";
  $Nombre=$row[2] or $Nombre="&nbsp;";
  $Direccion=$row[4] or $Direccion="&nbsp;";
  $Telefono=$row[3] or $Telefono="&nbsp;";
  $Observaciones=$row[8] or $Observaciones="&nbsp;";
//  $Abono=$abonos[$row[7]] or $Abono="&nbsp;";
  $Abono=$row[7] or $Abono="&nbsp;";
  $FechaIni=$row[5] or $FechaIni="&nbsp;";
  $FechaVen=$row[6] or $FechaVen="&nbsp;";
  $Estado=$row[9] or $Estado="&nbsp;";
  $Observaciones=ereg_replace(" // ","\n",$Observaciones);

?>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">Modificaci&oacute;n
  de datos del cliente<br>
  </font></b></font></p>
<form method="post" action="internet_modi.php" name="Modificar">
  <table width="90%" border="2" cellspacing="1" cellpadding="2" align="center" bgcolor="#FFCC99" bordercolor="<? echo $bgcolor3; ?>">
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Login</font></b></font></td>
      <td width="39%">
        <input type="text" name="ModLogin" size="30" value="<? echo $Login; ?>">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Abono</font></b></td>
      <td width="30%">
        <select name="ModAbono" size="1">
          <option value="D" <? echo "Abono=$Abono"; if (($Abono == "D") or ($Abono == "E") or ($Abono == "A") or ($Abono == "B") or ($Abono == "C")) { echo "selected"; } ?>>Servicio Gold</option>
<!--          <option value="E" <? if ($Abono == "E") { echo "selected"; } ?>>3 Meses Full</option>
          <option value="A" <? if ($Abono == "A") { echo "selected"; } ?>>3 Meses Full</option>
          <option value="B" <? if ($Abono == "B") { echo "selected"; } ?>>6 Meses Full</option>
          <option value="C" <? if ($Abono == "C") { echo "selected"; } ?>>12 Meses Full</option>
-->
          <option value="F" <? if ($Abono == "F") { echo "selected"; } ?>>Cuenta Gratis</option>
          <option value="H10" <? if ($Abono == "H10") { echo "selected"; } ?>>Servicio Basic</option>
          <option value="H50" <? if ($Abono == "H50") { echo "selected"; } ?>>Servicio Medium</option>
          <option value="X50" <? if ($Abono == "X50") { echo "selected"; } ?>>Servicio Medium (Cuenta Extra)</option>
          <option value="XF" <? if ($Abono == "XF") { echo "selected"; } ?>>Servicio Gold (Cuenta Extra)</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Nombre</font></b></font></td>
      <td width="39%">
        <input type="text" name="ModNombre" size="30" value="<? echo $Nombre; ?>">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Fecha
        Inicio</font></b></td>
      <td width="30%">
        <input type="text" name="ModFechaIni" size="15" maxlength="10" value="<? echo ConvFecha($FechaIni); ?>">
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Direcci&oacute;n</font></b></font></td>
      <td width="39%">
        <input type="text" name="ModDireccion" size="30" value="<? echo $Direccion; ?>">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Fecha
        Venc.</font></b></td>
      <td width="30%">
        <input type="text" name="ModFechaVen" size="15" maxlength="10" value="<? echo ConvFecha($FechaVen); ?>">
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Tel&eacute;fono</font></b></font></td>
      <td width="39%">
        <input type="text" name="ModTelefono" size="30" value="<? echo $Telefono; ?>">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Estado</font></b></td>
      <td width="30%">
        <select name="ModEstado" size="1">
          <option value="ACTIVO" <? if ($Estado == "ACTIVO") { echo "selected"; } ?>>Activo</option>
          <option value="VENCIDO" <? if ($Estado == "VENCIDO") { echo "selected"; } ?>>Vencido</option>
          <option value="BORRADO" <? if ($Estado == "BORRADO") { echo "selected"; } ?>>Borrado</option>
          <option value="CERRADO" <? if ($Estado == "CERRADO") { echo "selected"; } ?>>Cerrado</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Observaciones</font></b></font></td>
      <td colspan="3">
		<textarea name='ModObservaciones' cols=70 rows=5><? echo $Observaciones; ?></textarea>
      </td>
    </tr>
  </table>
  <div align="center">
    <input type="hidden" name="ModIDCliente" value="<? echo $IDCliente; ?>">
    <input type="hidden" name="BuscarUsuario" value="<? echo $Usuario; ?>">
    <br>
    <input type="submit" name="ModificarDatos" value="  Guardar ">
    <input type="reset" name="ModificarCancelar" value="Deshacer">
    <input type="button" OnClick="window.location='internet_view.php';" name="DetalleUsuario" value="    Volver    ">
  </div>
</form>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">
  </font></b></font></p>
<p>&nbsp;</p>