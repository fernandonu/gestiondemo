<?PHP
/*
$Author: nazabal $
$Revision: 1.3 $
$Date: 2003/08/27 13:46:21 $
*/
require_once("../../config.php");

echo $html_header;

if ($_POST['AgregarUsuario'])
{
 if (!$HTTP_POST_VARS["NewLogin"]) { Error("Falta ingresar el Login."); }
        if (!$HTTP_POST_VARS["NewNombre"]) { Error("Falta ingresar el Nombre.");
 }
        if (!fechaOk($HTTP_POST_VARS["NewFechaIni"])) { Error("Formato de la Fecha de Inicio incorrecto."); }
        if (!fechaOk($HTTP_POST_VARS["NewFechaVen"])) { Error("Formato de la Fecha de Vencimiento incorrecto."); }
        $comando="SELECT * FROM internet.internet WHERE Login='$NewLogin'";
        $result=$db->Execute($comando);
        if ($result->RecordCount() > 0) { Error("Ya existe un usuario con el login '$NewLogin'"); }
        if ($error) { exit; }
        $comando="SELECT max(IDCLiente) AS MaxIDCliente FROM internet.internet";
        $result=$db->Execute($comando) or db_die();
        $row=$result->FetchRow();
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
        $result=$db->Execute($comando);
        if ($result) {
            Aviso("Los datos del nuevo cliente se cargaron correctamente");
            //include_once("internet_view.php");
            //exit;
        }
        else { Error("Se produjo un error al actualizar la base de datos:<br>".mysql_error()); }
}
?>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">Nuevo
  cliente<br>
  </font></b></font></p>
<form method="post" action="internet_add.php">
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
<!--    <input type="button" OnClick="window.location='internet_view.php';" name="BtnVolver" value="Ver Lista de Usuarios"> --></p>
  </div>
</form>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">
  </font></b></font></p>
<p>&nbsp;</p>