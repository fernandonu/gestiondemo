<?
/*
$Author: mari $
$Revision: 1.14 $
$Date: 2007/01/05 20:15:29 $
*/
require_once("../../config.php");
$abonos=array(
		"A"     => "Servicio Gold",
		"B"     => "Servicio Gold",
		"C"     => "Servicio Gold",
		"D"     => "Servicio Gold",
		"E"     => "Servicio Gold",
		"F"     => "Cuenta Gratis",
		"H10"   => "Servicio Basic",
		"H50"   => "Servicio Medium",
		"X50"   => "Servicio Medium (Cuenta Extra)",
		"XF"    => "Servicio Gold (Cuenta Extra)"
);


$colorOk="#7CB656"; //"#CCFFFF"; //"#66CC66";
$color7dias="#FFCC00";
$colorVencido="#FF0000";
$colorBorrado="#FFFFFF";
$colorCerrado="#CCFFFF";

function gen_tr_tag ($link) {
  global $cnr, $bgcolor1, $bgcolor2, $color_fila;
  if ($color_fila) { $color = $color_fila; }
  else { $color = $bgcolor1; }
  $tr_hover_on = "onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\" onClick=\"location.href = '$link'\"";
  echo "<tr bgcolor=$color $tr_hover_on>\n";
}
function ActualizarEstado($id,$estado) {
	global $db;
	$sql = "update internet set estado='$estado' where idcliente=$id;";
	$result=$db->Execute($sql);
	if (!$result) {
		Error("Error al actualizar el estado del cliente $id");
		return 0;
	}
	return 1;
}
echo $html_header;
// Recuperar Variable por Metodo Post y Get
$DetalleUsuario=$_POST['DetalleUsuario'] or $DetalleUsuario=$parametros['DetalleUsuario'];
// Fin Blocke de Recuperar Variables
if($parametros['DetalleUsuario'] or $_POST['DetalleUsuario']) {
//  print_r ($parametros);
  if ($BuscarUsuario) {
          $Usuario=$BuscarUsuario;
  }
  else { $Usuario = $DetalleUsuario; }
  if (!$Usuario) {
        Error("No seleccionó ningún usuario.");
          exit;
  }
  $comando="SELECT * FROM internet.internet WHERE Login='$Usuario'";
  $result=$db->execute($comando);
  if ($result->RecordCount() == 0) { Error("No existe el usuario $Usuario"); exit; }
  $row=$result->FetchRow();
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


if (permisos_check("inicio","internet_modi")) {
?>
        <td>
        <form method="post" action="internet_modi.php">
          <input type=hidden name=login value='<? echo $Usuario; ?>'>
          <input type="submit" name="BtnModificar" value="      Modificar      ">
        </form>
        </td>
<?
}
?>
    <td>
        <form method="post" action="internet_view.php">
          <input type="submit" name="BtnListado" value="Volver al listado">
        </form>
        </td>
        </tr>
  </table>
<p align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><b><font size="4">
  </font></b></font></p>
<p>&nbsp;</p>
<?
exit;
}
$DEstActivo = $_POST["DEstActivo"];
$DEstBorrado = $_POST['DEstBorrado'];
$DEstVencido = $_POST['DEstVencido'];
$DEstCerrado = $_POST['DEstCerrado'];
$DEstGratis = $_POST['DEstGratis'];
if (!($DEstActivo || $DEstBorrado || $DEstVencido || $DEstCerrado || $DEstGratis)) {
        $DEstActivo=1;
        $DEstVencido=1;
}
if (!$DOrden) { $DOrden = "FechaVen"; }

?>
<form action="internet_view.php" method="POST">
  <input type=hidden name=mode value=view>
  <table border="2" cellspacing="5" cellpadding="2" align="center" width="90%" bordercolor="<? echo $bgcolor3; ?>">
    <tr>
      <td valign="top" colspan="3"><font face="Verdana, Arial, Helvetica, sans-serif">
        <?
        $orden = array(
                        "default" => "2",
						"1" => "internet.login",
						"2" => "internet.nombre",
						"3" => "internet.fechaven",
						"4" => "internet.abono",
						"5" => "internet.estado"
                );

                $filtro = array(
						"internet.login" => "Login",
						"internet.nombre" => "Nombre",
						"internet.telefono" => "Teléfono",
						"internet.direccion" => "Dirección",
						"internet.aclaraciones" => "Comentarios"
                );
                $page = $_GET["page"] or $page = 0;                                                                //pagina actual
                $filter = $_POST["filter"] or $filter = $_GET["filter"];                //campo por el que se esta filtrando
                $keyword = $_POST["keyword"] or $keyword = $_GET["keyword"];        //palabra clave
                $_GET["sort"]=$_POST["DOrden"] or $_GET["sort"] = 1;
                $sort=$_GET["sort"];
$comando="SELECT * FROM internet";
$comando_tmp="";
if ($DEstActivo) {
        $comando_tmp.=" (Estado = 'ACTIVO'";
}
if ($DEstCerrado) {
        if ($comando_tmp) { $comando_tmp.=" OR "; }
        else { $comando_tmp.=" ("; }
        $comando_tmp.="Estado='CERRADO'";
}
if ($DEstBorrado) {
        if ($comando_tmp) { $comando_tmp.=" OR "; }
        else { $comando_tmp.=" ("; }
        $comando_tmp.="Estado='BORRADO'";
}
if ($DEstVencido) {
        if ($comando_tmp) { $comando_tmp.=" OR "; }
        else { $comando_tmp.=" ("; }
        $comando_tmp.="Estado='VENCIDO'";
}
if ($DEstGratis) {
        if ($comando_tmp) { $comando_tmp.=" AND "; }
        else { $comando_tmp.=" ("; }
        $comando_tmp.="Abono='F'";
}
else {
        if ($comando_tmp) { $comando_tmp.=" ) AND "; }
        $comando_tmp.="(Abono <> 'F'";
}
if ($comando_tmp) { $comando_tmp.=")"; }

/*
if ($_POST[ListadoBuscar]) {
        $comando_tmp = "(Login like '%".$_POST[BuscarUsuario]."%'";
        $comando_tmp .= " OR Nombre like '%".$_POST[BuscarUsuario]."%')";
}
*/
//$comando.=$comando_tmp;

//if ($DOrden) { $comando.=" ORDER BY $DOrden"; }
//else { $comando.=" ORDER BY Login"; }
                $link_tmp = "<a id=ma href='internet.php?mode=$mode&cmd=$cmd";


                list($sql,$total,$link_pagina,$up) = form_busqueda($comando,$orden,$filtro,$link_tmp,$comando_tmp,"buscar");

        ?>
                <br>
        <b>Ordenar por:</b>
        <select name="DOrden">
          <option value="1" <? if ($DOrden=="1" || !$DOrden) echo "selected"; ?>>Login</option>
          <option value="2" <? if ($DOrden=="2") echo "selected"; ?>>Nombre</option>
<!--
          <option value="FechaIni" <? if ($DOrden=="3") echo "selected"; ?>>Fecha
          Inicio</option>
-->
          <option value="3" <? if ($DOrden=="3") echo "selected"; ?>>Fecha
          Vencimiento</option>
          <option value="4" <? if ($DOrden=="4") echo "selected"; ?>>Abono</option>
          <option value="5" <? if ($DOrden=="5") echo "selected"; ?>>Estado</option>
        </select>
<!--
        <input type="submit" name="DBtnOrden" value="Actualizar">
-->
                </td>
      <td valign="top" rowspan="2" width="0">&nbsp;</td>
      <td valign="top" rowspan="2" width="300" bordercolor="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="3">Referencia:</font></b><br>
        </font>
		<table border="2" cellspacing="5" bordercolor="<? echo $bgcolor3; ?>">
          <tr>
            <td width="17" bgcolor="<? echo $colorOk; ?>" bordercolor="#000000" height="17">&nbsp;</td>
            <td><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="2">Al
              d&iacute;a</font></b></font></td>
          </tr>
          <tr>
            <td width="17" bgcolor="<? echo $color7dias; ?>" bordercolor="#000000" height="17">&nbsp;</td>
            <td><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="2">Vence
              en una semana</font></b></font></td>
          </tr>
          <tr>
            <td width="17" bgcolor="<? echo $colorVencido; ?>" bordercolor="#000000" height="17">&nbsp;</td>
            <td><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="2">Vencida</font></b></font></td>
          </tr>
          <tr>
            <td width="17" bgcolor="<? echo $colorCerrado; ?>" bordercolor="#000000" height="17">&nbsp;</td>
            <td><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="2">Cerrada</font></b></font></td>
          </tr>
          <tr>
            <td width="17" bgcolor="<? echo $colorBorrado; ?>" bordercolor="#000000" height="17">&nbsp;</td>
            <td><font face="Verdana, Arial, Helvetica, sans-serif"><b><font size="2">Borrada</font></b></font></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td valign="top" align="center" width="40">
        <input type="submit" name="form_busqueda" value="      Buscar     ">
<?

if (permisos_check('usuarios',$_ses_user['login'])) {
?>
                <br>
        <input type="submit" name="ListadoAgregar" value="Nuevo Cliente">
<?
}
?>
          </td>
      <td valign="top" align="right" width="123"><font face="Verdana, Arial, Helvetica, sans-serif"><b>Mostrar:</b></font></td>
      <td valign="top" width="159"> <font face="Verdana, Arial, Helvetica, sans-serif">
        <b>
        <input type="checkbox" name="DEstActivo" <? if ($DEstActivo) echo "checked"; ?> >
        Activos<br>
        <input type="checkbox" name="DEstVencido" <? if ($DEstVencido) echo "checked"; ?> >
        Vencidos<br>
        <input type="checkbox" name="DEstCerrado" <? if ($DEstCerrado) echo "checked"; ?> >
        Cerrados<br>
        <input type="checkbox" name="DEstBorrado" <? if ($DEstBorrado) echo "checked"; ?> >
        Borrados<br>
        <input type="checkbox" name="DEstGratis" <? if ($DEstGratis) echo "checked"; ?> >
        Gratis</b></font></td>
    </tr>
  </table>
    </form>
<br>
<form method="post" action="index.php">
  <input type=hidden name=mode value='<? echo $mode; ?>'>
  <input type=hidden name=cmd value='forms'>
  <table width="100%" border="2" cellspacing="1" cellpadding="2" align="center" bordercolor="#000000" bgcolor="#FFFFFF">
  <tr id=ma>
  <td align="center" width="50%">Total: <? echo $total; ?> clientes</td>
  <td align="center" width="50%"><? echo $link_pagina; ?>&nbsp;</td>
  </tr>
  </table>
  <table width="100%" border="2" cellspacing="1" cellpadding="2" align="center" bordercolor="#000000" bgcolor="#FFFFFF">
    <tr bgcolor="#CCCCCC">
      <td><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Login</font></b></font></td>
      <td><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Nombre</font></b></font></td>
      <td bgcolor="#CCCCCC" width="89"><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Fecha
        Venc.</font></b></font></td>
      <td><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Abono</font></b></font></td>
      <td><font size="2"><b><font face="Verdana, Arial, Helvetica, sans-serif">Estado</font></b></font></td>
    </tr>
    <?
db_tipo_res("a");
$result = $db->Execute($sql) or Error($db->ErrorMsg());
$cant_total=0;
$cant_activos=0;
$cant_vence7dias=0;
$cant_vencidos=0;
$cant_cerrados=0;
$cant_borrados=0;
while(!$result->EOF) {
		$fecha=check_fecha($result->fields["fechaven"]);
		$cant_total++;
        $result->fields["aclaraciones"]=ereg_replace(" // ","<br>",$result->fields["aclaraciones"]);
        $result->fields["aclaraciones"]=ereg_replace("\n","<br>\n",$result->fields["aclaraciones"]);
        if ($fecha == 0) {
                $color=$colorOk;
        }
        elseif ($fecha == 1) {
                $color=$color7dias;
                $cant_vence7dias++;
                $cant_activos--;
        }
        else {
                $color=$colorVencido;
                if ($result->fields["estado"] == "ACTIVO") {
                        if (ActualizarEstado($result->fields["idcliente"],"VENCIDO")) {
                                $result->fields["estado"] = "VENCIDO";
                        }
                }
        }
        if ($result->fields["estado"] == "BORRADO") {
                $color=$colorBorrado;
                $cant_borrados++;
        }
        if ($result->fields["estado"] == "CERRADO") {
                       $color=$colorCerrado;
                $cant_cerrados++;
        }
        if ($result->fields["estado"] == "VENCIDO") {
                $cant_vencidos++;
        }
        if ($result->fields["estado"] == "ACTIVO") {
                $cant_activos++;
        }

        $color_fila = $color;
        $link_detalles = encode_link("internet_view.php",Array ("PHPSESSID" => $PHPSESSID,"mode" => "forms","DetalleUsuario" => $result->fields["login"]));
        gen_tr_tag($link_detalles);

  ?>
      <td><font face="Times New Roman, Times, serif">
        <a href="<? echo $link_detalles; ?>">
        <font color="#000000"><b>
        <? echo $result->fields["login"]; ?>
        </b></font>
        </a>
        </font></td>
      <td><font face="Times New Roman, Times, serif">
        <? echo $result->fields["nombre"]; ?>
        </font></td>
      <td width="89">
        <div align="center"><font face="Times New Roman, Times, serif">
          <? echo ConvFecha($result->fields["fechaven"]); ?>
          </font></div>
      </td>
      <td><font face="Times New Roman, Times, serif">
        <? echo "<b>".$abonos[$result->fields["abono"]]."</b><br>".$result->fields["aclaraciones"].""; ?>
        </font></td>
      <td><font face="Times New Roman, Times, serif">
        <? echo $result->fields["estado"]; ?>
        </font></td>
    </tr>
    <?
	$result->MoveNext();
}
?>
  <tr>
    <td colspan=5>
        Cuentas: <? echo $cant_total; ?>&nbsp;&nbsp;
        Activas: <? echo $cant_activos; ?>&nbsp;&nbsp;
        Vence en 7 dias: <? echo $cant_vence7dias; ?>&nbsp;&nbsp;
        Vencidas: <? echo $cant_vencidos; ?>&nbsp;&nbsp;
        Cerradas: <? echo $cant_cerrados; ?>&nbsp;&nbsp;
        Borradas: <? echo $cant_borrados; ?>&nbsp;&nbsp;
    </td>
  </tr>
  </table>
  <div align="center"><br>
  <?
//    <input type="submit" name="ListadoAgregar" value="   Nuevo Cliente    ">
  ?>
  </div>
</form>