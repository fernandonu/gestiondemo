<?
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2003/07/07 19:10:07 $
*/
session_start();
$error=0;

if($_GET[DetalleUsuario] or $_POST[DetalleUsuario]) {
  if ($BuscarUsuario) {
          $Usuario=$BuscarUsuario;
  }
  else { $Usuario = $DetalleUsuario; }
  if (!$Usuario) {
        Error("No seleccionó ningún usuario.");
          exit;
  }
  $comando="SELECT * FROM internet.internet WHERE Login='$Usuario'";
  $result=db_query($comando);
  if (db_query_rows($result) == 0) { Error("No existe el usuario $Usuario"); exit; }
  $row=db_fetch_row($result);
  $IDCliente=$row[10] or $IDCliente="&nbsp;";
  $Login=$row[0] or $Login="&nbsp;";
  $Nombre=$row[2] or $Nombre="&nbsp;";
  $Direccion=$row[4] or $Direccion="&nbsp;";
  $Telefono=$row[3] or $Telefono="&nbsp;";
  $Observaciones=$row[8] or $Observaciones="&nbsp;";
  $Abono=$abonos[$row[7]] or $Abono="&nbsp;";
  $FechaIni=$row[5] or $FechaIni="&nbsp;";
  $FechaVen=$row[6] or $FechaVen="&nbsp;";
  $Estado=$row[9] or $Estado="&nbsp;";
  $Observaciones=ereg_replace(" // ","<br>",$Observaciones);
  $Observaciones=ereg_replace("\n","<br>\n",$Observaciones);

?>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">Datos
  del cliente<br>
  </font></b></font></p>
  <table width="90%" border="2" cellspacing="1" cellpadding="2" align="center" bgcolor="#FFCC99" bordercolor="<? echo $bgcolor3; ?>">
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Login</font></b></font></td>
      <td width="39%">
        <? echo $Login; ?>
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Abono</font></b></td>
      <td width="30%">
        <? echo $Abono; ?>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Nombre</font></b></font></td>
      <td width="39%">
        <? echo $Nombre; ?>
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Fecha
        Inicio</font></b></td>
      <td width="30%">
        <? echo ConvFecha($FechaIni); ?>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Direcci&oacute;n</font></b></font></td>
      <td width="39%">
        <? echo $Direccion; ?>
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Fecha
        Venc.</font></b></td>
      <td width="30%">
        <? echo ConvFecha($FechaVen); ?>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Tel&eacute;fono</font></b></font></td>
      <td width="39%">
        <? echo $Telefono; ?>
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Estado</font></b></td>
      <td width="30%">
        <? echo $Estado; ?>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Observaciones</font></b></font></td>
      <td colspan="3">
        <? echo $Observaciones; ?>
      </td>
    </tr>
  </table>
  <br>
  <table border=0 align="center">
    <tr align=center>
<?
if (ereg("i",$user_access)) {
?>
        <td>
        <form method="post" action="internet.php">
          <input type=hidden name=mode value='forms'>
          <input type=hidden name=login value='<? echo $Usuario; ?>'>
          <input type="submit" name="BtnModificar" value="      Modificar      ">
        </form>
        </td>
<?
}
?>
    <td>
        <form method="post" action="internet.php">
          <input type=hidden name=mode value='view'>
          <input type="submit" name="BtnListado" value="Volver al listado">
        </form>
        </td>
        </tr>
  </table>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">
  </font></b></font></p>
<p>&nbsp;</p>
<?
}
elseif ($_POST[BtnModificar]) {
  if ($BuscarUsuario) {
        $Usuario=$BuscarUsuario;
  }
  else {
          $Usuario=$_POST[login];
  }
  if (!$Usuario) {
        Error("No seleccionó ningún usuario.");
        exit;
  }
  $comando="SELECT * FROM internet.internet WHERE Login='$Usuario'";
  $result=db_query($comando);
  if (db_query_rows($result) == 0) { Error("No existe el usuario $Usuario"); exit; }
  $row=db_fetch_row($result);
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
<form method="post" action="internet.php" name="Modificar">
  <input type=hidden name=mode value=forms>
  <input type=hidden name=cmd value='<? echo $cmd; ?>'>
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
    <input type="submit" name="DetalleUsuario" value="    Volver    ">
  </div>
</form>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">
  </font></b></font></p>
<p>&nbsp;</p>
<?
}
elseif ($_POST[ListadoAgregar]) {
?>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">Nuevo
  cliente<br>
  </font></b></font></p>
<form method="post" action="internet.php">
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value='<? echo $cmd; ?>'>
  <table width="90%" border="2" cellspacing="1" cellpadding="2" align="center" bgcolor="#FFCC99" bordercolor="<? echo $bgcolor3; ?>">
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Login</font></b></font></td>
      <td width="39%">
        <input type="text" name="NewLogin" size="30">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Abono</font></b></td>
      <td width="30%">
        <select name="NewAbono" size="1">
          <option value="D">1 Mes Full</option>
          <option value="E">2 Meses Full</option>
          <option value="A">3 Meses Full</option>
          <option value="B">6 Meses Full</option>
          <option value="C" selected>12 Meses Full</option>
          <option value="F">Cuenta Gratis</option>
          <option value="H10">10 Horas/Mes</option>
          <option value="H50">50 Horas/Mes</option>
          <option value="X50">50 Horas(Cuenta Extra)</option>
          <option value="XF">Acceso Full(Cuenta Extra)</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Nombre</font></b></font></td>
      <td width="39%">
        <input type="text" name="NewNombre" size="30">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Fecha
        Inicio</font></b></td>
      <td width="30%">
        <input type="text" name="NewFechaIni" size="15" maxlength="10" value="01-01-2003">
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Direcci&oacute;n</font></b></font></td>
      <td width="39%">
        <input type="text" name="NewDireccion" size="30">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Fecha
        Venc.</font></b></td>
      <td width="30%">
        <input type="text" name="NewFechaVen" size="15" maxlength="10" value="01-01-2003">
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Tel&eacute;fono</font></b></font></td>
      <td width="39%">
        <input type="text" name="NewTelefono" size="30">
      </td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Estado</font></b></td>
      <td width="30%">
        <select name="NewEstado" size="1">
          <option value="ACTIVO" selected>Activo</option>
          <option value="VENCIDO">Vencido</option>
          <option value="CERRADO">Cerrado</option>
          <option value="BORRADO">Borrado</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width="17%"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Observaciones</font></b></font></td>
      <td colspan="3">
        <input type="text" name="NewObservaciones" size="75">
      </td>
    </tr>
  </table>
  <div align="center"> <br>
    <input type="submit" name="AgregarUsuario" value="Agregar">
    <input type="reset" name="NewBtnCancelar" value="Cancelar">
    <input type="submit" name="BtnVolver" value="  Volver  ">
  </div>
</form>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">
  </font></b></font></p>
<p>&nbsp;</p>
<?
}
elseif ($_POST[ModificarDatos]) {
        if (!$HTTP_POST_VARS["ModLogin"]) { Error("Falta ingresar el Login."); }
        if (!$HTTP_POST_VARS["ModNombre"]) { Error("Falta ingresar el Nombre."); }
        if (!fechaOk($HTTP_POST_VARS["ModFechaIni"])) { Error("Formato de la Fecha de Inicio incorrecto."); }
        if (!fechaOk($HTTP_POST_VARS["ModFechaVen"])) { Error("Formato de la Fecha de Vencimiento incorrecto."); }
        $comando="SELECT * FROM internet.internet WHERE Login='$ModLogin' and IDCliente<>'$ModIDCliente'";
        $result=db_query($comando);
        if (db_query_rows($result) > 0) { Error("Ya existe un usuario con el login '$ModLogin'"); }
        if ($error) { exit; }
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
        $result=db_query($comando);
        Aviso("Los datos se actualizaron correctamente");
        if ($result) {
                include_once("internet_view.php");
                exit;\
        }
        else { Error("Se produjo un error al actualizar la base de datos:<br>".mysql_error()); }
}
elseif ($_POST[AgregarUsuario]) {
if (!$HTTP_POST_VARS["NewLogin"]) { Error("Falta ingresar el Login."); }
        if (!$HTTP_POST_VARS["NewNombre"]) { Error("Falta ingresar el Nombre.");
 }
        if (!fechaOk($HTTP_POST_VARS["NewFechaIni"])) { Error("Formato de la Fecha de Inicio incorrecto."); }
        if (!fechaOk($HTTP_POST_VARS["NewFechaVen"])) { Error("Formato de la Fecha de Vencimiento incorrecto."); }
        $comando="SELECT * FROM internet.internet WHERE Login='$NewLogin'";
        $result=db_query($comando);
        if (db_query_rows($result) > 0) { Error("Ya existe un usuario con el login '$NewLogin'"); }
        if ($error) { exit; }
        $comando="SELECT max(IDCLiente) AS MaxIDCliente FROM internet.internet";
        $result=db_query($comando) or db_die();
        $row=db_fetch_row($result);
        $MaxIDCliente=$row[0];
        $MaxIDCliente++;
        $NewLogin=$HTTP_POST_VARS[NewLogin];
        $NewAbono=$HTTP_POST_VARS[NewAbono];
        $NewNombre=$HTTP_POST_VARS[NewNombre];
        $NewDireccion=$HTTP_POST_VARS[NewDireccion];
        $NewTelefono=$HTTP_POST_VARS[NewTelefono];
        $NewFechaIni=$HTTP_POST_VARS[NewFechaIni];
        $NewFechaIni=ConvFecha($NewFechaIni);
        $NewFechaVen=$HTTP_POST_VARS[NewFechaVen];
        $NewFechaVen=ConvFecha($NewFechaVen);
        $NewEstado=$HTTP_POST_VARS[NewEstado];
        $NewObservaciones=$HTTP_POST_VARS[NewObservaciones];
        $comando="INSERT INTO internet.internet VALUES ('$NewLogin','','$NewNombre','$NewTelefono','$NewDireccion','$NewFechaIni','$NewFechaVen','$NewAbono','$NewObservaciones','$NewEstado',$MaxIDCliente,NULL,NULL,NULL)";
        $result=db_query($comando);
        if ($result) {
            Aviso("Los datos del nuevo cliente se cargaron correctamente");
            include_once("internet_view.php");
            exit;
        }
        else { Error("Se produjo un error al actualizar la base de datos:<br>".mysql_error()); }
}
else {
        include_once("internet_view.php");
        exit;
}


function fechaOk($fecha) {
        list($dia,$mes,$anio)=split("-", $fecha);
        if (($dia >= 1) and ($dia <= 31) and ($mes >= 1) and ($mes <= 12) and ($anio >= 1997)) {
                return 1;
        }
        else { return 0; }
}
?>