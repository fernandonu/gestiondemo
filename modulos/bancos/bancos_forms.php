<?
/*
$Revision: 1.2 $ 
$Id: bancos_forms.php,v 1.2 2003/09/03 20:11:58 nazabal Exp $
*/

/*
 * @return array
 * @param sql string
 * @param orden array
 * @param filtro array
 * @param link_pagina string
 * @param where_extra string (opcional)
 * @desc Esta funcion genera el formulario de busqueda y divide el resultado
         de una consulta sql por paginas
         Ejemplo:
         // variables que contienen los datos actuales de la busqueda
         $page = $_GET["page"] or $page = 0;								//pagina actual
		 $filter = $_POST["filter"] or $filter = $_GET["filter"];		//campo por el que se esta filtrando
		 $keyword = $_POST["keyword"] or $keyword = $_GET["keyword"];	//palabra clave

		 $orden = array(					//campos que voy a mostar
			"default" => "2",				//campo por defecto
			"1" => "IdProv",
			"2" => "Proveedor"
		 );

		 $filtro = array(					
			"Proveedor"		=> "Proveedor",		//elementos en donde se van a hacer las busquedas
			"Contacto"		=> "Contacto",		//el formato del aarreglo es:
			"Mail"			=> "Mail"			//$filtro=array("nombre de la columna en la base de datos" => "nombre a mostrar en el formulario");
		 );
		 //sentencia sql que sin ninguna condicion
		 $sql_tmp = "SELECT IdProv,Proveedor,Contacto,Mail,Teléfono,Comentarios FROM bancos.proveedores";
		 //prefijo para los links de paginas siguiente y anterior
		 $link_tmp = "<a id=ma href='bancos.php?mode=$mode&cmd=$cmd";
		 //condiciones extras de la consulta
		 $where_tmp = "";
		 
		 list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp);

*/
function form_busqueda($sql,$orden,$filtro,$link_pagina,$where_extra="") {
	global $bgcolor2,$page,$filter,$keyword;
	$itemspp = 20;			// Items por pagina
	$sort = $_GET["sort"] or $sort = "default";
	if ($_GET["up"] == "0") {
		$up = $_GET["up"];
		$direction="DESC";
		$up2 = "1";
	}
	else {
		$up = "1";
		$direction = "ASC";
		$up2 = "0";
	}

	if ($sort == "default") { $sort = $orden[$sort]; }
	
	echo "<table border=0 bgcolor=$bgcolor2 width=80% align=center>\n";
	echo "<tr><td align=center>\n";
	echo "Buscar: <input type='text' name='keyword' value='$keyword' size=20 maxlength=20>\n";
	echo "en: <select name='filter'>\n";
	echo "<option value='all'";
	if (!$filter) echo " selected";
	echo ">Todos los campos\n";
	while (list($key, $val) = each($filtro)) {
		echo "<option value='$key'";
		if ($filter == "$key") echo " selected";
		echo ">$val\n";
	}
	echo "</select>\n";
	echo "<input type=submit name=buscar value='   Buscar   '>\n";
	echo "</td></tr></table>\n";

	if ($keyword) {
		$where = " WHERE ";
		if ($filter == "all" or !$filter) {
			$where_arr = array();
			$where .= "(";
			reset($filtro);
			while (list($key, $val) = each($filtro)) {
				$where_arr[] = "$filtro[$key] like '%$keyword%'";
			}
			$where .= implode(" or ", $where_arr);
			$where .= ")";
		}
		else {
			$where .= "$filtro[$filter] like '%$keyword%'";
		}
	}
	
	$sql .= " $where";
	if ($where_extra != "") {
		$sql .= " AND $where_extra";
	}

	$sql_cont = eregi_replace("^SELECT (.+) FROM", "SELECT COUNT(*) AS total FROM", $sql);
	$result = db_query($sql_cont) or db_die($sql_cont);
	$row = db_fetch_row($result);
	$total = $row["total"];

	$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);


	$page_n = $page + 1;
	$page_p = $page - 1;
	$link_pagina_p = "";
	$link_pagina_n = "";
	if ($page > 0) {
		$link_pagina_p .= $link_pagina."&sort=$sort&up=$up&page=$page_p&keyword=$keyword&filter=$filter'><<</a>";
	}
	$sum=0;
	if (($total % $itemspp)>0) $sum=1;
	$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)." de ". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
	if ($total > $page_n*$itemspp) {
		$link_pagina_n = $link_pagina."&sort=$sort&up=$up&page=$page_n&keyword=$keyword&filter=$filter'>>></a>";
	}
	
	$link_pagina = $link_pagina_p.$link_pagina_num.$link_pagina_n;

	return array($sql,$total,$link_pagina,$up2);
}
function NuevProv ($id=0) {
	global $bgcolor2,$PHPSESSID;
	echo "<br><table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	// Titulo del Formulario
	if ($id!=0) echo "<tr bordercolor='#000000'><td id=mo align=center>Datos del Proveedor</td></tr>";
	else echo "<tr bordercolor='#000000'><td id=mo align=center>Nuevo Proveedor</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	if ($id!=0) {
		$sql="SELECT * FROM bancos.proveedores WHERE IdProv=$id";
		$result = db_query($sql) or db_die($sql);
		$fila=db_fetch_row($result);
		echo "<tr><td align=right><b>Id Proveedor</b></td>";
		echo "<td align=left><input type=hidden name=Nuevo_Proveedor_Id value='$fila[idprov]'>$fila[idprov]</td</tr>";
	}
	echo "<tr><td align=right><b>Nombre</b></td>";
	echo "<td align=left>";
	echo "<input type=text name=Nuevo_Proveedor_Nombre value='$fila[proveedor]' size=30 maxlength=100>\n";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Número C.U.I.T.</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_CUIT value='$fila[cuit]' size=30 maxlength=20>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Domicilio</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Domicilio value='$fila[domicilio]' size=30 maxlength=100>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Código Postal</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_CP value='$fila[cp]' size=30 maxlength=6>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Localidad</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Localidad value='$fila[localidad]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Provincia</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Provincia value='$fila[provincia]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Contacto</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Contacto value='$fila[contacto]' size=30 maxlength=100>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>E-Mail</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Mail value='$fila[mail]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Teléfono</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Telefono value='$fila[teléfono]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Fax</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Nuevo_Proveedor_Fax value='$fila[fax]' size=30 maxlength=50>";
	echo "</td></tr>\n";
	echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
	echo "</td><td>";
	echo "<textarea name=Nuevo_Proveedor_Comentarios cols=21 rows=5>$fila[comentarios]</textarea>";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Nuevo_Proveedor_Aceptar value='Aceptar'>&nbsp;&nbsp;&nbsp;\n";
	if ($_POST[Ingreso_Cheque_Nuevo_Proveedor]=="Nuevo Proveedor"){
		echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=forms&cmd=Ing_Cheques';\">\n";
	}
	else {
		echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=forms&cmd=Mant_Proveedores';\">\n";
	}
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table></form><br>\n";	
}

if (!defined("lib_included")) { die("Use index.php!"); }

session_start();
// Para aseptar por metodos post y get
if (!$cmd) { $cmd=$_POST[cmd]; }
switch ($cmd) {
case "Mov_Cheques_Debitados":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Cheques_Debitados>\n";
	if (!$_POST[Mov_Cheques_Debitados_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Cheques_Debitados_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteCh) AS total FROM bancos.cheques WHERE FechaDébCh IS NOT NULL";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=3 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Cheques_Debitados_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=3 align=right><b>Total Debitado: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=6 align=center><input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\"></td></tr>\n";
	$sql = "SELECT ";
	$sql .= "bancos.cheques.FechaVtoCh,bancos.cheques.FechaDébCh,";
	$sql .= "bancos.cheques.NúmeroCh,bancos.cheques.ImporteCh,";
	$sql .= "bancos.proveedores.Proveedor ";
	$sql .= "FROM bancos.tipo_banco ";
	$sql .= "RIGHT JOIN (bancos.proveedores ";
	$sql .= "RIGHT JOIN bancos.cheques ";
	$sql .= "ON bancos.proveedores.IdProv=bancos.cheques.IdProv) ";
	$sql .= "ON bancos.tipo_banco.idbanco=bancos.cheques.idbanco ";
	$sql .= "WHERE bancos.cheques.FechaDébCh IS NOT NULL ";
	$sql .= "AND bancos.cheques.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.cheques.FechaDébCh DESC,bancos.cheques.NúmeroCh DESC";
	$sql .= " LIMIT 100 OFFSET 0";
	/*
	$sql = "SELECT DISTINCT ";
	$sql .= "bancos.cheques.FechaVtoCh,bancos.cheques.FechaDébCh,";
	$sql .= "bancos.cheques.NúmeroCh,bancos.cheques.ImporteCh,";
	$sql .= "bancos.proveedores.Proveedor ";
	$sql .= "FROM bancos.proveedores,bancos.tipo_banco,bancos.cheques ";
	$sql .= "WHERE bancos.cheques.FechaDébCh IS NOT NULL AND ";
	$sql .= "bancos.cheques.IdProv=bancos.proveedores.IdProv AND ";
	$sql .= "bancos.cheques.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.cheques.FechaDébCh DESC,bancos.cheques.NúmeroCh DESC";
	*/
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=6 align=center>Cheques Debitados</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>Vencimiento</td>";
	echo "<td align=center>Débito</td>";
	echo "<td align=center>Número</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Proveedor</td>";
	echo "<td align=center>Días</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importech];
		list($aa,$mm,$dd) = explode("-",$fila[fechadébch]);
		$fecha1 = mktime(0,0,0,$mm,$dd,$aa);
		list($aa,$mm,$dd) = explode("-",$fila[fechavtoch]);
		$fecha2 = mktime(0,0,0,$mm,$dd,$aa);
	    $Dias=($fecha1-$fecha2) / 86400;
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center>".Fecha($fila[fechavtoch])."</td>\n";
		echo "<td align=center>".Fecha($fila[fechadébch])."</td>\n";
		echo "<td align=center>".$fila[númeroch]."</td>\n";
		echo "<td align=right>\$".formato_money($fila[importech])."</td>\n";
		echo "<td align=left>".$fila[proveedor]."&nbsp;</td>\n";
		echo "<td align=center>".$Dias."&nbsp;</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=6 align=center><b>Subtotal Debitado: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Cheques_Pendientes":
	if ($_POST[Modificar]) {
		$mod_numero = $_POST[Modificar_Cheque_Numero];
		if (es_numero($mod_numero)) {
			$sql = "SELECT bancos.tipo_banco.NombreBanco,";
			$sql .= "bancos.cheques.IdBanco,";
			$sql .= "bancos.proveedores.Proveedor,";
			$sql .= "bancos.cheques.FechaEmiCh,";
			$sql .= "bancos.cheques.FechaVtoCh,";
			$sql .= "bancos.cheques.FechaDébCh,";
			$sql .= "bancos.cheques.ImporteCh,";
			$sql .= "bancos.cheques.Comentarios ";
			$sql .= "FROM (bancos.cheques ";
			$sql .= "INNER JOIN bancos.tipo_banco ";
			$sql .= "ON bancos.cheques.IdBanco = bancos.tipo_banco.IdBanco) ";
			$sql .= "INNER JOIN bancos.proveedores ";
			$sql .= "ON bancos.cheques.IdProv = bancos.proveedores.IdProv ";
			$sql .= "WHERE bancos.cheques.NúmeroCh=$mod_numero";
			$result = db_query($sql) or db_die($sql);
			list($mod_banco,
			$mod_idbanco,
			$mod_proveedor,
			$mod_fecha_e,
			$mod_fecha_v,
			$mod_fecha_d,
			$mod_importe,
			$mod_comentarios) = db_fetch_row($result);
			$mod_fecha_d = Fecha($mod_fecha_d);
			$mod_fecha_v = Fecha($mod_fecha_v);
			$mod_fecha_e = Fecha($mod_fecha_e);
			$mod_importe = formato_money($mod_importe);
//			if ($mod_fecha_d == "00/00/2000") {
//				$mod_fecha_d = "";
//			}
			echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
			echo "<form action=bancos.php method=post>\n";
			echo "<input type=hidden name=mode value=data><br>\n";
			echo "<input type=hidden name=cmd value=Modificar_Cheque>\n";
			echo "<input type=hidden name=Modificacion_Cheque_Numero value='$mod_numero'>";
			echo "<input type=hidden name=Modificacion_Cheque_IdBanco value='$mod_idbanco'>";
			echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
			echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación de datos del Cheque</td></tr>";
			echo "<tr bordercolor='#000000'><td align=center>";
			echo "<table cellspacing=5 border=1 bordercolor='$bgcolor2'>";
			echo "<tr><td align=right><b>Banco</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_banco&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Proveedor</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_proveedor&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_fecha_e&nbsp;</td>\n";
			echo "<tr>\n";
			echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_fecha_v&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Fecha Débito</b></td>";
			echo "<td align=left>";
			echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Debito value='$mod_fecha_d' title='Ingrese la fecha de débito del cheque'>";
			echo link_calendario("Modificacion_Cheque_Fecha_Debito");
			echo "</td></tr>\n";
			echo "<tr><td align=right><b>Número</b>\n";
			echo "</td><td align=left bordercolor=#000000>$mod_numero&nbsp;</td>\n";
			echo "</tr>\n";
			echo "<tr><td align=right><b>Importe</b>\n";
			echo "</td><td align=left>";
			echo "<input type=text name=Modificacion_Cheque_Importe value='$mod_importe' size=10 maxlength=50>&nbsp;";
			echo "</td></tr>\n";
			echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
			echo "</td><td align=left>";
			echo "<textarea name=Modificacion_Cheque_Comentarios cols=30 rows=3>$mod_comentarios</textarea>";
			echo "</td></tr>\n";
			echo "<tr><td align=center colspan=2>\n";
			echo "<table border=0 width=100%>\n";
			echo "<tr><td align=center>\n";
			echo "<input type=submit name=Modificacion_Cheque_Guardar value='Guardar'>\n";
			echo "</form>\n";
			echo "</td><td align=center>\n";
			echo "<form action=bancos.php method=post>\n";
			echo "<input type=hidden name=mode value=forms>\n";
			echo "<input type=hidden name=cmd value=Mov_Cheques_Pendientes>\n";
			echo "<input type=submit name=Volver value='   Volver   '>\n";
			echo "</form>\n";
			echo "</td></tr>\n";
			echo "</table>";
			echo "</td></tr>\n";
			echo "</table>";
			echo "</td></tr>\n";
			echo "</table>\n";
			break;
		}
		else {
			Error("No hay ningún cheque seleccionado");
		}
	}
	if ($_POST[Actualizar]) {
		$_POST[cmd] = "Cheques_Pendientes_Actualizar";
		include_once("./bancos_data.php");
		break;
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Cheques_Pendientes>\n";
	if (!$_POST[Mov_Cheques_Pendientes_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Cheques_Pendientes_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteCh) AS total FROM bancos.cheques WHERE FechaDébCh IS NULL";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);
	
	//Javascript

	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=4 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Cheques_Pendientes_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=3 align=right><b>Total Pendientes: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=7 align=right>";
	echo "<table border=0 width=100%>\n";
	echo "<tr><td align=right>\n";
	echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;";
	echo "</td>\n";
	echo "<td align=center>";
	echo "<input type=hidden name=IdBanco value='$Banco'>";
	echo "<input type=submit name=Actualizar value='Actualizar Fecha'>&nbsp;&nbsp;&nbsp;";
	echo "</td>\n";
	echo "<td align=left>";
	echo "<input type=button name=Volver value='        Volver        ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</td></tr>\n";
	$sql = "SELECT DISTINCT ";
	$sql .= "bancos.cheques.FechaVtoCh,bancos.cheques.FechaDébCh,";
	$sql .= "bancos.cheques.NúmeroCh,bancos.cheques.ImporteCh,";
	$sql .= "bancos.cheques.Comentarios,bancos.proveedores.Proveedor ";
	$sql .= "FROM bancos.proveedores,bancos.tipo_banco,bancos.cheques ";
	$sql .= "WHERE bancos.cheques.FechaDébCh IS NULL AND ";
	$sql .= "bancos.cheques.IdProv=bancos.proveedores.IdProv AND ";
	$sql .= "bancos.cheques.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.cheques.FechaVtoCh ASC,bancos.cheques.NúmeroCh ASC";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=7 align=center>Cheques Pendientes</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>&nbsp;</td>";
	echo "<td align=center>Vencimiento</td>";
	echo "<td align=center>Número</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Débito</td>";
	echo "<td align=center>Proveedor</td>";
	echo "<td align=center>Comentarios</td>";
	echo "</tr>\n";
	$form_element = 1;
	while ($fila = db_fetch_row($result)) {
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center><input type=radio name=Modificar_Cheque_Numero value='".$fila[númeroch]."'></td>";
		echo "<td align=center>".Fecha($fila[fechavtoch])."</td>\n";
		echo "<td align=center>".$fila[númeroch]."</td>\n";
		echo "<td align=right>\$".formato_money($fila[importech])."</td>\n";
		echo "<td align=center>";
		echo "<input type=text size=10 maxlength=10 name=Fecha_Cheques_Pendientes_NC".$fila[númeroch]." title='Ingrese la fecha y\nhaga click en Actualizar'>";
		echo link_calendario("Fecha_Cheques_Pendientes_NC".$fila[númeroch]);
		echo "</td>\n";
		echo "<td align=left>".$fila[proveedor]."&nbsp;</td>\n";
		echo "<td align=left>".$fila[comentarios]."&nbsp;</td>\n";
		echo "</tr>\n";
		$SubTotal += $fila[importech];
		$form_element++;
	}
	echo "<tr><td colspan=7 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Debitos":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Debitos>\n";
	if (!$_POST[Mov_Debitos_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Debitos_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteDéb) AS total FROM bancos.débitos";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Debitos_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=2 align=right><b>Total Debitados: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=3 align=center><input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\"></td></tr>\n";
	$sql = "SELECT TipoDébito,FechaDébito,ImporteDéb ";
	$sql .= "FROM bancos.tipo_débito,bancos.débitos ";
	$sql .= "WHERE bancos.tipo_débito.IdTipoDéb=bancos.débitos.IdTipoDéb AND ";
	$sql .= "IdBanco=$Banco ";
	$sql .= "ORDER BY FechaDébito DESC";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Débitos</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>Débito</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Fecha</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importedéb];
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=left>".$fila[tipodébito]."</td>\n";
		echo "<td align=right>\$".formato_money($fila[importedéb])."</td>\n";
		echo "<td align=center>".Fecha($fila[fechadébito])."</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=3 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Cheques_entre_Fechas":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Cheques_entre_Fechas>\n";
	if (!$_POST[Mov_Cheques_entre_Fechas_Banco]) {
		$Banco=3;  // Banco por defecto
		$Fecha_Desde = date("d/m/Y",(mktime() - (40 * 24 * 60 * 60)));
		$Fecha_Desde_db = date("Y-m-d",(mktime() - (40 * 24 * 60 * 60)));
		$Fecha_Hasta = date("d/m/Y",mktime());
		$Fecha_Hasta_db = date("Y-m-d",mktime());
	}
	else {
		$Banco=$_POST[Mov_Cheques_entre_Fechas_Banco];
		list($d,$m,$a) = explode("/", $_POST[Mov_Cheques_entre_Fechas_Desde]);
		if (Fecha_ok($d,$m,$a)) {
			$Fecha_Desde = "$d/$m/$a";
			$Fecha_Desde_db = "$a-$m-$d";
		}
		else {
			Error("Fecha de inicio inválida");
			$Fecha_Desde = date("d/m/Y",(mktime() - (40 * 24 * 60 * 60)));
			$Fecha_Desde_db = date("Y-m-d",(mktime() - (40 * 24 * 60 * 60)));
		}
		list($d,$m,$a) = explode("/", $_POST[Mov_Cheques_entre_Fechas_Hasta]);
		if (Fecha_ok($d,$m,$a)) {
			$Fecha_Hasta = "$d/$m/$a";
			$Fecha_Hasta_db = "$a-$m-$d";
		}
		else {
			Error("Fecha de finalización inválida");
			$Fecha_Hasta = date("d/m/Y",mktime());
			$Fecha_Hasta_db = date("Y-m-d",mktime());
		}
	}

	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=2 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Cheques_entre_Fechas_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
//	echo "<form action=bancos.php method=post>\n";
//	echo "<input type=hidden name=mode value=forms>\n";
	echo "<td colspan=3 align=right><b>Desde: </b>";
	echo "<input type=text size=10 name=Mov_Cheques_entre_Fechas_Desde value='$Fecha_Desde' title='Ingrese la fecha de inicio y\nhaga click en Actualizar'>";
	echo link_calendario("Mov_Cheques_entre_Fechas_Desde");
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>Hasta: </b>\n";
	echo "<input type=text size=10 name=Mov_Cheques_entre_Fechas_Hasta value='$Fecha_Hasta' title='Ingrese la fecha de finalización\ny haga click en Actualizar'>";
	echo link_calendario("Mov_Cheques_entre_Fechas_Hasta");
	echo "</td></tr>";
	echo "<tr><td colspan=5 align=center>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Cheques_entre_Fechas>\n";
	echo "<input type=submit name=Form_Cheques_entre_Fechas value='Actualizar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</form></td></tr>\n";
	$sql = "SELECT ";
	$sql .= "bancos.cheques.FechaVtoCh,bancos.cheques.FechaPrev,";
	$sql .= "bancos.cheques.NúmeroCh, bancos.cheques.ImporteCh,";
	$sql .= "bancos.proveedores.Proveedor ";
	$sql .= "FROM bancos.cheques ";
	$sql .= "INNER JOIN bancos.proveedores ";
	$sql .= "ON bancos.cheques.IdProv = bancos.proveedores.IdProv ";
	$sql .= "WHERE bancos.cheques.FechaPrev Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db' ";
	$sql .= "AND bancos.cheques.FechaDébCh IS NULL ";
	$sql .= "AND bancos.cheques.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.cheques.FechaPrev";

	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=5 align=center>Cheques entre Fechas</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>Vencimiento</td>";
	echo "<td align=center>A Debitar</td>";
	echo "<td align=center>Número Cheque</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Beneficiario</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importech];
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center>".Fecha($fila[fechavtoch])."</td>\n";
		echo "<td align=center>".Fecha($fila[fechaprev])."</td>\n";
		echo "<td align=center>".$fila[númeroch]."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importech])."</td>\n";
		echo "<td align=left>".($fila[proveedor])."</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=5 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Depositos_Acreditados":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Depositos_Acreditados>\n";
	if (!$_POST[Mov_Depositos_Acreditados_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Depositos_Acreditados_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteDep) AS total FROM bancos.depósitos WHERE FechaCrédito IS NOT NULL";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=2 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Depositos_Acreditados_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=3 align=right><b>Total Acreditado: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=5 align=center><input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\"></td></tr>\n";
	$sql = "SELECT ";
	$sql .= "bancos.tipo_depósito.TipoDepósito,bancos.depósitos.FechaDepósito,";
	$sql .= "bancos.depósitos.ImporteDep,bancos.depósitos.FechaCrédito,";
	$sql .= "bancos.depósitos.IdDepósito ";
	$sql .= "FROM bancos.depósitos ";
	$sql .= "INNER JOIN bancos.tipo_depósito ";
	$sql .= "ON bancos.depósitos.IdTipoDep = bancos.tipo_depósito.IdTipoDep ";
	$sql .= "WHERE bancos.depósitos.FechaCrédito IS NOT NULL ";
	$sql .= "AND bancos.depósitos.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.depósitos.FechaCrédito DESC";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=5 align=center>Depósitos Acreditados</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>ID</td>";
	echo "<td align=center>Tipo Depósito</td>";
	echo "<td align=center>Fecha</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Acreditado</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importedep];
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center>".$fila[iddepósito]."</td>\n";
		echo "<td align=left>".$fila[tipodepósito]."</td>\n";
		echo "<td align=center>".Fecha($fila[fechadepósito])."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importedep])."</td>\n";
		echo "<td align=center>".Fecha($fila[fechacrédito])."</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=5 align=center><b>Subtotal Acreditado: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Depositos_Pendientes":
	if ($_POST[Modificar]) {
		$mod_numero = $_POST[Modificar_Deposito_Numero];
		if (es_numero($mod_numero)) {
			$sql = "SELECT bancos.tipo_banco.NombreBanco,";
			$sql .= "bancos.depósitos.IdBanco,";
			$sql .= "bancos.tipo_depósito.TipoDepósito,";
			$sql .= "bancos.depósitos.IdDepósito,";
			$sql .= "bancos.depósitos.FechaCrédito,";
			$sql .= "bancos.depósitos.FechaDepósito,";
			$sql .= "bancos.depósitos.ImporteDep ";
			$sql .= "FROM (bancos.depósitos ";
			$sql .= "INNER JOIN bancos.tipo_banco ";
			$sql .= "ON bancos.depósitos.IdBanco = bancos.tipo_banco.IdBanco) ";
			$sql .= "INNER JOIN bancos.tipo_depósito ";
			$sql .= "ON bancos.depósitos.IdTipoDep = bancos.tipo_depósito.IdTipoDep ";
			$sql .= "WHERE bancos.depósitos.IdDepósito=$mod_numero";
			$result = db_query($sql) or db_die($sql);
			list($mod_banco,
			$mod_idbanco,
			$mod_tipodep,
			$mod_iddep,
			$mod_fecha_c,
			$mod_fecha_d,
			$mod_importe) = db_fetch_row($result);
			$mod_fecha_d = Fecha($mod_fecha_d);
			$mod_fecha_c = Fecha($mod_fecha_c);
			$mod_importe = formato_money($mod_importe);
//			if ($mod_fecha_c == "00/00/2000") {
//				$mod_fecha_c = "";
//			}
			echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
			echo "<form action=bancos.php method=post>\n";
			echo "<input type=hidden name=mode value=data><br>\n";
			echo "<input type=hidden name=cmd value=Modificar_Deposito>\n";
			echo "<input type=hidden name=Modificacion_Deposito_IdDeposito value='$mod_iddep'>";
			echo "<input type=hidden name=Modificacion_Deposito_IdBanco value='$mod_idbanco'>";
			echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
			echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación de datos del Depósito</td></tr>";
			echo "<tr bordercolor='#000000'><td align=center>";
			echo "<table cellspacing=5 border=1 bordercolor='$bgcolor2'>";
			echo "<tr><td align=right><b>Banco</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_banco&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Tipo Depósito</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_tipodep&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Fecha de Depósito</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_fecha_d&nbsp;</td>\n";
			echo "<tr>\n";
			echo "<tr><td align=right><b>Fecha de Crédito</b></td>";
			echo "<td align=left>";
			echo "<input type=text size=10 name=Modificacion_Deposito_Fecha_Credito value='$mod_fecha_c' title='Ingrese la fecha de crédito del depósito'>";
			echo link_calendario("Modificacion_Deposito_Fecha_Credito");
			echo "</td></tr>\n";
			echo "<tr><td align=right><b>Importe</b>\n";
			echo "</td><td align=left>";
			echo "<input type=text name=Modificacion_Deposito_Importe value='$mod_importe' size=10 maxlength=50>&nbsp;";
			echo "</td></tr>\n";
			echo "<tr><td align=center colspan=2>\n";
			echo "<table border=0 width=100%>\n";
			echo "<tr><td align=center>\n";
			echo "<input type=submit name=Modificacion_Deposito_Guardar value='Guardar'>\n";
			echo "</form>\n";
			echo "</td><td align=center>\n";
			echo "<form action=bancos.php method=post>\n";
			echo "<input type=hidden name=mode value=forms>\n";
			echo "<input type=hidden name=cmd value=Mov_Depositos_Pendientes>\n";
			echo "<input type=submit name=Volver value='   Volver   '>\n";
			echo "</form>\n";
			echo "</td></tr>\n";
			echo "</table>";
			echo "</td></tr>\n";
			echo "</table>";
			echo "</td></tr>\n";
			echo "</table>\n";
			break;
		}
		else {
			Error("No hay ningún depósito seleccionado");
		}
	}
	if ($_POST[Actualizar]) {
		$_POST[cmd] = "Depositos_Pendientes_Actualizar";
		include_once("./bancos_data.php");
		break;
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Depositos_Pendientes>\n";
	if (!$_POST[Mov_Depositos_Pendientes_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Depositos_Pendientes_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteDep) AS total FROM bancos.depósitos WHERE FechaCrédito IS NULL";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Javascript

	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=3 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Depositos_Pendientes_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=3 align=right><b>Total Pendiente: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=6>\n";
	echo "<table align=center width=100%>";
	echo "<tr><td align=right>";
	echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;";
	echo "</td><td align=center>\n";
	echo "<input type=submit name=Actualizar value='     Actualizar    '>&nbsp;&nbsp;&nbsp;";
	echo "</td><td align=left>\n";
	echo "<input type=button name=Volver value='       Volver       ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
	echo "</td></tr>\n";
	echo "</table></td></tr>\n";
	$sql = "SELECT ";
	$sql .= "bancos.tipo_depósito.TipoDepósito,bancos.depósitos.FechaDepósito,";
	$sql .= "bancos.depósitos.ImporteDep,bancos.depósitos.FechaCrédito,";
	$sql .= "bancos.depósitos.IdDepósito ";
	$sql .= "FROM (bancos.depósitos ";
	$sql .= "INNER JOIN bancos.tipo_banco ";
	$sql .= "ON bancos.depósitos.IdBanco = bancos.tipo_banco.IdBanco) ";
	$sql .= "INNER JOIN bancos.tipo_depósito ";
	$sql .= "ON bancos.depósitos.IdTipoDep = bancos.tipo_depósito.IdTipoDep ";
	$sql .= "WHERE bancos.depósitos.FechaCrédito IS NULL ";
	$sql .= "AND bancos.depósitos.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.depósitos.FechaDepósito DESC";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=6 align=center>Depósitos Pendientes</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>&nbsp;</td>";
	echo "<td align=center>ID</td>";
	echo "<td align=center>Tipo Depósito</td>";
	echo "<td align=center>Fecha</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Acreditado</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[ImporteDep];
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center><input type=radio name=Modificar_Deposito_Numero value='".$fila[iddepósito]."'></td>";
		echo "<td align=center>".$fila[iddepósito]."</td>\n";
		echo "<td align=left>".$fila[tipodepósito]."</td>\n";
		echo "<td align=center>".Fecha($fila[fechadepósito])."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importedep])."</td>\n";
		echo "<td align=center>";
		echo "<input type=text size=10 maxlength=10 name=Fecha_Depositos_Pendientes_ND".$fila[iddepósito]." title='Ingrese la fecha y\nhaga click en Actualizar'>";
		echo link_calendario("Fecha_Depositos_Pendientes_ND".$fila[iddepósito]);
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=6 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Tarjetas_Acreditadas":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Tarjetas_Acreditadas>\n";
	if (!$_POST[Mov_Tarjetas_Acreditadas_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Tarjetas_Acreditadas_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteCrédTar) AS total FROM bancos.tarjetas WHERE FechaCrédTar IS NOT NULL";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=4 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Tarjetas_Acreditadas_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=4 align=right><b>Total Acreditado: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=8 align=center><input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\"></td></tr>\n";
	$sql = "SELECT DISTINCT ";
	$sql .= "bancos.tipo_tarjeta.TipoTarjeta,bancos.tarjetas.FechaDepTar,";
	$sql .= "bancos.tarjetas.ImporteDepTar,bancos.tarjetas.FechaCrédTar,";
	$sql .= "bancos.tarjetas.ImporteCrédTar,bancos.tarjetas.IdTarjeta ";
	$sql .= "FROM bancos.tarjetas ";
	$sql .= "INNER JOIN bancos.tipo_tarjeta ";
	$sql .= "ON bancos.tipo_tarjeta.IdTipoTar = bancos.tarjetas.IdTipoTar ";
	$sql .= "WHERE bancos.tarjetas.FechaCrédTar IS NOT NULL ";
	$sql .= "AND bancos.tarjetas.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.tarjetas.IdTarjeta DESC";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=8 align=center>Tarjetas Acreditadas</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>ID</td>";
	echo "<td align=center>Tarjeta</td>";
	echo "<td align=center>Fecha Depósito</td>";
	echo "<td align=center>Importe Depósito</td>";
	echo "<td align=center>Fecha Crédito</td>";
	echo "<td align=center>Importe Crédito</td>";
	echo "<td align=center>Días</td>";
	echo "<td align=center>% Descuento</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importecrédtar];
		list($aa,$mm,$dd) = explode("-",$fila[fechacrédtar]);
		$fecha1 = mktime(0,0,0,$mm,$dd,$aa);
		list($aa,$mm,$dd) = explode("-",$fila[fechadeptar]);
		$fecha2 = mktime(0,0,0,$mm,$dd,$aa);
	    $Dias=($fecha1 - $fecha2) / 86400;
	    $Porcentaje=100 - (($fila[importecrédtar] * 100)/$fila[importedeptar]);
	    $Porcentaje=sprintf("%0.2f",$Porcentaje);
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center>".$fila[idtarjeta]."</td>\n";
		echo "<td align=left>".$fila[tipotarjeta]."</td>\n";
		echo "<td align=center>".Fecha($fila[fechadeptar])."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importedeptar])."</td>\n";
		echo "<td align=center>".Fecha($fila[fechacrédtar])."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importecrédtar])."</td>\n";
		echo "<td align=center>$Dias</td>\n";
		echo "<td align=right>$Porcentaje%</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=8 align=center><b>Subtotal Acreditado: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Tarjetas_Pendientes":
	if ($_POST[Modificar]) {
		$mod_numero = $_POST[Modificar_Tarjeta_IdTarjeta];
		if (es_numero($mod_numero)) {
			$sql = "SELECT bancos.tipo_banco.NombreBanco,";
			$sql .= "bancos.tarjetas.IdBanco,";
			$sql .= "bancos.tipo_tarjeta.TipoTarjeta,";
			$sql .= "bancos.tarjetas.IdTarjeta,";
			$sql .= "bancos.tarjetas.FechaDepTar,";
			$sql .= "bancos.tarjetas.ImporteDepTar,";
			$sql .= "bancos.tarjetas.FechaCrédTar,";
			$sql .= "bancos.tarjetas.ImporteCrédTar ";
			$sql .= "FROM (bancos.tarjetas ";
			$sql .= "INNER JOIN bancos.tipo_banco ";
			$sql .= "ON bancos.tarjetas.IdBanco=bancos.tipo_banco.IdBanco) ";
			$sql .= "INNER JOIN bancos.tipo_tarjeta ";
			$sql .= "ON bancos.tarjetas.IdTipoTar=bancos.tipo_tarjeta.IdTipoTar ";
			$sql .= "WHERE bancos.tarjetas.IdTarjeta=$mod_numero";
			$result = db_query($sql) or db_die($sql);
			list($mod_banco,
			$mod_idbanco,
			$mod_tarjeta,
			$mod_idtarjeta,
			$mod_fecha_d,
			$mod_importe_d,
			$mod_fecha_c,
			$mod_importe_c) = db_fetch_row($result);
			$mod_fecha_d = Fecha($mod_fecha_d);
			$mod_fecha_c = Fecha($mod_fecha_c);
			$mod_importe_d = formato_money($mod_importe_d);
			if ($mod_importe_c != 0) {
				$mod_importe_c = formato_money($mod_importe_c);
			}
			else {
				$mod_importe_c = "";
			}
//			if ($mod_fecha_c == "00/00/2000") {
//				$mod_fecha_c = "";
//			}
			echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
			echo "<form action=bancos.php method=post>\n";
			echo "<input type=hidden name=mode value=data><br>\n";
			echo "<input type=hidden name=cmd value=Modificar_Tarjeta>\n";
			echo "<input type=hidden name=Modificacion_Tarjeta_IdTarjeta value='$mod_numero'>";
			echo "<input type=hidden name=Modificacion_Tarjeta_IdBanco value='$mod_idbanco'>";
			echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
			echo "<tr bordercolor='#000000'><td id=mo align=center>Modificación de datos de la Tarjeta</td></tr>";
			echo "<tr bordercolor='#000000'><td align=center>";
			echo "<table cellspacing=5 border=1 bordercolor='$bgcolor2'>";
			echo "<tr><td align=right><b>Banco</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_banco&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Tarjeta</b></td>";
			echo "<td align=left bordercolor=#000000>$mod_tarjeta&nbsp;</td></tr>\n";
			echo "<tr><td align=right><b>Fecha de Depósito</b></td>";
			echo "<td align=left>";
			echo "<input type=text size=10 name=Modificacion_Tarjeta_Fecha_Deposito value='$mod_fecha_d' title='Ingrese la fecha de depósito de la tarjeta'>";
			echo link_calendario("Modificacion_Tarjeta_Fecha_Deposito");
			echo "</td></tr>\n";
			echo "<tr><td align=right><b>Importe Depósito</b>\n";
			echo "</td><td align=left>";
			echo "<input type=text name=Modificacion_Tarjeta_Importe_Deposito value='$mod_importe_d' size=10 maxlength=50>&nbsp;";
			echo "</td></tr>\n";
			echo "<tr><td align=right><b>Fecha de Crédito</b></td>";
			echo "<td align=left>";
			echo "<input type=text size=10 name=Modificacion_Tarjeta_Fecha_Credito value='$mod_fecha_c' title='Ingrese la fecha de crédito de la tarjeta'>";
			echo link_calendario("Modificacion_Tarjeta_Fecha_Credito");
			echo "</td></tr>\n";
			echo "<tr><td align=right><b>Importe Crédito</b>\n";
			echo "</td><td align=left>";
			echo "<input type=text name=Modificacion_Tarjeta_Importe_Credito value='$mod_importe_c' size=10 maxlength=50>&nbsp;";
			echo "</td></tr>\n";
			echo "<tr><td align=center colspan=2>\n";
			echo "<table border=0 width=100%>\n";
			echo "<tr><td align=center>\n";
			echo "<input type=submit name=Modificacion_Tarjeta_Guardar value='Guardar'>\n";
			echo "</form>\n";
			echo "</td><td align=center>\n";
			echo "<form action=bancos.php method=post>\n";
			echo "<input type=hidden name=mode value=forms>\n";
			echo "<input type=hidden name=cmd value=Mov_Tarjetas_Pendientes>\n";
			echo "<input type=hidden name=Mov_Tarjetas_Pendientes_Banco value='$mod_idbanco'>\n";
			echo "<input type=submit name=Volver value='   Volver   '>\n";
			echo "</form>\n";
			echo "</td></tr>\n";
			echo "</table>";
			echo "</td></tr>\n";
			echo "</table>";
			echo "</td></tr>\n";
			echo "</table>\n";
			break;
		}
		else {
			Error("No hay ninguna tarjeta seleccionada");
		}
	}
	if ($_POST[Actualizar]) {
		$_POST[cmd] = "Tarjetas_Pendientes_Actualizar";
		include_once("./bancos_data.php");
		break;
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Tarjetas_Pendientes>\n";
	if (!$_POST[Mov_Tarjetas_Pendientes_Banco]) {
		$Banco=3;  // Banco por defecto
	}
	else {
		$Banco=$_POST[Mov_Tarjetas_Pendientes_Banco];
	}
	//Total	
	$sql = "SELECT sum(ImporteDepTar) AS total FROM bancos.tarjetas WHERE FechaCrédTar IS NULL";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Javascript
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";

	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=4 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Tarjetas_Pendientes_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=3 align=right><b>Total Pendiente: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=7>";
	echo "<table align=center width=100%>";
	echo "<tr><td align=right>";
	echo "<input type=submit name=Modificar value='Modificar Datos'>&nbsp;&nbsp;&nbsp;";
	echo "</td><td align=center>\n";
	echo "<input type=hidden name=IdBanco value='$Banco'>";
	echo "<input type=submit name=Actualizar value='Actualizar Fecha'>&nbsp;&nbsp;&nbsp;";
	echo "</td><td align=left>\n";
	echo "<input type=button name=Volver value='       Volver       ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
	echo "</td></tr>\n";
	echo "</table></td></tr>\n";
	$sql = "SELECT DISTINCT ";
	$sql .= "bancos.tipo_tarjeta.TipoTarjeta,bancos.tarjetas.FechaDepTar,";
	$sql .= "bancos.tarjetas.ImporteDepTar,bancos.tarjetas.FechaCrédTar,";
	$sql .= "bancos.tarjetas.ImporteCrédTar,bancos.tarjetas.IdTarjeta ";
	$sql .= "FROM bancos.tarjetas ";
	$sql .= "INNER JOIN bancos.tipo_tarjeta ";
	$sql .= "ON bancos.tarjetas.IdTipoTar=bancos.tipo_tarjeta.IdTipoTar ";
	$sql .= "WHERE bancos.tarjetas.FechaCrédTar IS NULL ";
	$sql .= "AND bancos.tarjetas.IdBanco=$Banco ";
	$sql .= "ORDER BY bancos.tarjetas.IdTarjeta DESC";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=7 align=center>Tarjetas Pendientes</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>&nbsp;</td>";
	echo "<td align=center>ID</td>";
	echo "<td align=center>Tarjeta</td>";
	echo "<td align=center>Fecha Depósito</td>";
	echo "<td align=center>Importe Depósito</td>";
	echo "<td align=center>Fecha Crédito</td>";
	echo "<td align=center>Importe Crédito</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importedeptar];
		list($aa,$mm,$dd) = explode("-",$fila[fechacrédtar]);
		$fecha1 = mktime(0,0,0,$mm,$dd,$aa);
		list($aa,$mm,$dd) = explode("-",$fila[fechadeptar]);
		$fecha2 = mktime(0,0,0,$mm,$dd,$aa);
	    $Dias=($fecha1 - $fecha2) / 86400;
	    $Porcentaje=100 - (($fila[importecrédtar] * 100)/$fila[importedeptar]);
	    $Porcentaje=sprintf("%0.2f",$Porcentaje);
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center><input type=radio name=Modificar_Tarjeta_IdTarjeta value='".$fila[idtarjeta]."'></td>";
		echo "<td align=center>".$fila[idtarjeta]."</td>\n";
		echo "<td align=left>".$fila[tipotarjeta]."</td>\n";
		echo "<td align=center>".Fecha($fila[fechadeptar])."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importedeptar])."</td>\n";
		echo "<td align=center>";
		echo "<input type=text size=10 maxlength=20 name=Fecha_Tarjetas_Pendientes_NT".$fila[idtarjeta]." title='Ingrese la fecha y\nhaga click en Actualizar'>";
		echo link_calendario("Fecha_Tarjetas_Pendientes_NT".$fila[idtarjeta]);
		echo "</td>\n";
		echo "<td align=center><input type=text size=10 maxlength=10 name=Importe_Tarjetas_Pendientes[".$fila[idtarjeta]."] title='Ingrese el importe y\nhaga click en Actualizar'></td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=7 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Mov_Saldo":
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Saldo>\n";
	if (!$_POST[Mov_Saldo_Banco]) {
		$Banco=3;  // Banco por defecto
		$Fecha_Saldo = date("d/m/Y",mktime());
		$Fecha_Saldo_db = date("Y-m-d",mktime());
	}
	else {
		$Banco=$_POST[Mov_Saldo_Banco];
		$Fecha_Saldo = $_POST[Mov_Saldo_Fecha];
		$Fecha_Saldo_db = Fecha_db($_POST[Mov_Saldo_Fecha]);
	}

	//Total	Depositos
	$sql = "SELECT sum(ImporteDep) AS total ";
	$sql .= "FROM bancos.depósitos ";
	$sql .= "WHERE FechaCrédito IS NOT NULL ";
	$sql .= "AND FechaCrédito BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
	$sql .= "AND IdBanco=$Banco";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total_Depositos = $res_tmp[total];

	//Total	Tarjetas
	$sql = "SELECT sum(ImporteCrédTar) AS total ";
	$sql .= "FROM bancos.tarjetas ";
	$sql .= "WHERE FechaCrédTar IS NOT NULL ";
	$sql .= "AND FechaCrédTar BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
	$sql .= "AND IdBanco=$Banco";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total_Tarjetas = $res_tmp[total];

	//Total	Cheques
	$sql = "SELECT sum(ImporteCh) AS total ";
	$sql .= "FROM bancos.cheques ";
	$sql .= "WHERE FechaDébCh IS NOT NULL ";
	$sql .= "AND FechaDébCh BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
	$sql .= "AND IdBanco=$Banco";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total_Cheques = $res_tmp[total];

	//Total	Debitos
	$sql = "SELECT sum(ImporteDéb) AS total ";
	$sql .= "FROM bancos.débitos ";
	$sql .= "WHERE FechaDébito IS NOT NULL ";
	$sql .= "AND FechaDébito BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
	$sql .= "AND IdBanco=$Banco";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total_Debitos = $res_tmp[total];
	
	$Saldo=($Total_Depositos + $Total_Tarjetas - $Total_Cheques - $Total_Debitos);
	if ($Saldo < 0) {
		$Color_Saldo = "#ff0000";
	}
	else {
		$Color_Saldo = "#000000";
	}

	echo "<table width=600 align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr>";
	echo "<td colspan=1 align=left><b>Banco</b>";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	echo "<select name=Mov_Saldo_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td>\n";
	echo "<td colspan=2 align=right><b>Fecha</b>";
	echo "<input type=text size=10 name=Mov_Saldo_Fecha value='$Fecha_Saldo' title='Ingrese la fecha y\nhaga click en Actualizar'>";
	echo link_calendario("Mov_Saldo_Fecha");
	echo "</td>";
	echo "</tr>";
	echo "<tr><td colspan=3 align=center>";
	echo "<input type=submit name=Saldo_Actualizar value='Actualizar'>&nbsp;&nbsp;&nbsp;";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
	echo "</td></tr>\n";
	echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Resumen a la fecha</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td width=33% align=center>Ingresos</td>";
	echo "<td width=33% align=center>Egresos</td>";
	echo "<td width=33% align=center>Resumen</td>";
	echo "</tr>\n";
	echo "<tr bordercolor='#000000'>\n";
	echo "<td align=center>";
	echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
	echo "<tr>";
	echo "<td id=mo>Depósitos</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td id=ma>$ ".formato_money($Total_Depositos)."</td>";
	echo "</tr>";
	echo "</table><br>";
	echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
	echo "<tr>";
	echo "<td id=mo>Tarjetas</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td id=ma>$ ".formato_money($Total_Tarjetas)."</td>";
	echo "</tr>";
	echo "</table>";
	echo "</td>\n";
	echo "<td align=center>";
	echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
	echo "<tr>";
	echo "<td id=mo>Cheques</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td id=ma>$ ".formato_money($Total_Cheques)."</td>";
	echo "</tr>";
	echo "</table><br>";
	echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
	echo "<tr>";
	echo "<td id=mo>Débitos</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td id=ma>$ ".formato_money($Total_Debitos)."</td>";
	echo "</tr>";
	echo "</table>";
	echo "</td>\n";
	echo "<td align=center>";
	echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
	echo "<tr>";
	echo "<td id=mo><font size=3>Saldo</font></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td id=ma><b><font size=4 color='$Color_Saldo'>$ ".formato_money($Saldo)."</font></b></td>";
	echo "</tr>";
	echo "</table>";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table></form>\n";
	break;
case "Ing_Cheques":
	if ($_POST[Ingreso_Cheque_Nuevo_Proveedor]) {
		$Banco=$_POST[Ingreso_Cheque_Banco];
		$Fecha_Emision = $_POST[Ingreso_Cheque_Fecha_Emision];
		$Fecha_Vencimiento = $_POST[Ingreso_Cheque_Fecha_Vencimiento];
		$Fecha_Debito = $_POST[Ingreso_Cheque_Fecha_Debito];
		$Importe = $_POST[Ingreso_Cheque_Importe];
		$Comentarios = $_POST[Ingreso_Cheque_Comentarios];
		$Numero = $_POST[Ingreso_Cheque_Numero];
		echo "<br><form action=bancos.php method=post>\n";
		echo "<input type=hidden name=mode value=data>\n";
		echo "<input type=hidden name=cmd value=Nuevo_Proveedor>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Banco value='$Banco'>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$Fecha_Emision'>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$Fecha_Vencimiento'>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Fecha_Debito value='$Fecha_Debito'>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Importe value='$Importe'>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Comentarios value='$Comentarios'>\n";
		echo "<input type=hidden name=Ingreso_Cheque_Numero value='$Numero'>\n";
		NuevProv();
		break;
	}
	if ($_POST[Ingreso_Cheque_Guardar]) {
		$_POST[cmd] = "Ingreso_Cheque";
		include_once("./bancos_data.php");
		break;
	}
	if (!$_POST[Ingreso_Cheque_Banco]) {
		$Banco=3;  // Banco por defecto
		$Fecha_Emision = date("d/m/Y",mktime());
		$Fecha_Vencimiento = "";
		$Fecha_Debito = "";
		$Proveedor = "";
		$Importe = "";
		$Comentarios = "";
	}
	else {
		$Banco=$_POST[Ingreso_Cheque_Banco];
		$Fecha_Emision = $_POST[Ingreso_Cheque_Fecha_Emision];
		$Fecha_Vencimiento = $_POST[Ingreso_Cheque_Fecha_Vencimiento];
		$Fecha_Debito = $_POST[Ingreso_Cheque_Fecha_Debito];
		$Proveedor = $_POST[Ingreso_Cheque_Proveedor];
		$Importe = $_POST[Ingreso_Cheque_Importe];
		$Comentarios = $_POST[Ingreso_Cheque_Comentarios];
		if ($_POST[Ingreso_Cheque_Numero] != ($Ultimo_Cheque + 1))
			$Numero = $_POST[Ingreso_Cheque_Numero];
	}

	//Ultimo Cheque
	$sql = "SELECT Max(NúmeroCh) AS ultimo ";
	$sql .= "FROM bancos.cheques ";
	$sql .= "WHERE IdBanco=$Banco";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Ultimo_Cheque = $res_tmp[ultimo];

	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms><br>\n";
	echo "<input type=hidden name=cmd value=Ing_Cheques><br>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Cheques</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	echo "<tr><td align=right><b>Banco</b></td>";
	echo "<td align=left colspan=3>";
	echo "<select name=Ingreso_Cheque_Banco OnChange=\"JS('document.forms[0].submit();')\">\n";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>A la order de</b></td>";
	echo "<td align=left colspan=3>";
	echo "<select name=Ingreso_Cheque_Proveedor>\n";
	echo "<option value='' selected></option>\n";
	$sql = "SELECT IdProv, Proveedor FROM bancos.proveedores ORDER BY Proveedor";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value='".$fila[idprov]."'";
		if ($fila[idprov] == "$Proveedor") echo " selected";
		echo ">".$fila[proveedor]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
	echo "<td align=left>";
	echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Emision value='$Fecha_Emision' title='Ingrese la fecha de emisión del cheque'>";
	echo link_calendario("Ingreso_Cheque_Fecha_Emision");
	echo "</td>\n";
	echo "<td colspan=2 align=right>\n";
	echo "<input type=submit name=Ingreso_Cheque_Nuevo_Proveedor value='Nuevo Proveedor'>\n";
	echo "</td><tr>\n";
	echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
	echo "<td align=left colspan=3>";
	echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Vencimiento value='$Fecha_Vencimiento' title='Ingrese la fecha de vencimiento del cheque'>";
	echo link_calendario("Ingreso_Cheque_Fecha_Vencimiento");
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Fecha Débito</b></td>";
	echo "<td align=left colspan=3>";
	echo "<input type=text size=10 name=Ingreso_Cheque_Fecha_Debito value='$Fecha_Debito' title='Ingrese la fecha de débito del cheque'>";
	echo link_calendario("Ingreso_Cheque_Fecha_Debito");
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Número</b>\n";
	echo "</td><td align=left>";
	echo "<input type=text name=Ingreso_Cheque_Numero size=10 maxlength=50 value='".($Ultimo_Cheque + 1)."'>&nbsp;";
	echo "</td>\n";
	echo "<td align=right><b>Ultimo Número</b>\n";
	echo "</td><td align=left>";
	echo "<input disabled type=text name=Ingreso_Cheque_Ultimo_Numero size=10 maxlength=50 value='$Ultimo_Cheque'>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Importe</b>\n";
	echo "</td><td align=left colspan=3>";
	echo "<input type=text name=Ingreso_Cheque_Importe value='$Importe' size=10 maxlength=50>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
	echo "</td><td align=left colspan=3>";
	echo "<textarea name=Ingreso_Cheque_Comentarios cols=53 rows=3>$Comentarios</textarea>";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=4>\n";
	echo "<input type=submit name=Ingreso_Cheque_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table>\n";
	break;
case "Ing_Depositos":
	$Fecha_Hoy=date("Y-m-d",mktime());
	$Banco_Default=3;
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=cmd value=Ingreso_Deposito><br>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Depósitos</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	echo "<tr><td align=right><b>Banco</b></td>";
	echo "<td align=left>";
	echo "<select tabindex=1 name=Ingreso_Deposito_Banco>\n";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco_Default)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Tipo de Depósito</b></td>";
	echo "<td align=left>";
	echo "<select tabindex=2 name=Ingreso_Deposito_Tipo>\n";
	echo "<option value='' selected></option>\n";
	$sql = "SELECT * FROM bancos.tipo_depósito";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idtipodep].">".$fila[tipodepósito]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Fecha Depósito</b></td>";
	echo "<td align=left>";
	echo "<input tabindex=4 type=text size=10 name=Ingreso_Deposito_Fecha title='Ingrese la fecha del depósito'>";
	echo link_calendario("Ingreso_Deposito_Fecha");
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Importe</b>\n";
	echo "</td><td>";
	echo "<input tabindex=3 type=text name=Ingreso_Deposito_Importe size=22 maxlength=50>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=2>\n";
	echo "<input tabindex=5 type=submit name=Ingreso_Deposito_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input tabindex=6 type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "<script language='JavaScript' type='text/JavaScript'>\n";
	echo "document.forms[0].elements[2].focus();\n";
	echo "</script>\n";
	break;
case "Ing_Debitos":
	$Fecha_Hoy=date("Y-m-d",mktime());
	$Banco_Default=3;
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=cmd value=Ingreso_Debito><br>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Débitos</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	echo "<tr><td align=right><b>Banco</b></td>";
	echo "<td align=left>";
	echo "<select name=Ingreso_Debito_Banco>\n";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco_Default)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Tipo de Débito</b></td>";
	echo "<td align=left>";
	echo "<select name=Ingreso_Debito_Tipo>\n";
	echo "<option value='' selected></option>\n";
	$sql = "SELECT * FROM bancos.tipo_débito";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idtipodéb].">".$fila[tipodébito]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Fecha Débito</b></td>";
	echo "<td align=left>";
	echo "<input type=text size=10 name=Ingreso_Debito_Fecha title='Ingrese la fecha de débito'>";
	echo link_calendario("Ingreso_Debito_Fecha");
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Importe</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Ingreso_Debito_Importe size=22 maxlength=50>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Ingreso_Debito_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table>\n";
	break;
case "Ing_Tarjetas":
	$Fecha_Hoy=date("Y-m-d",mktime());
	$Banco_Default=3;
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=cmd value=Ingreso_Tarjeta><br>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Tarjetas</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	echo "<tr><td align=right><b>Banco</b></td>";
	echo "<td align=left>";
	echo "<select name=Ingreso_Tarjeta_Banco>\n";
	$sql = "SELECT * FROM bancos.tipo_banco";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idbanco];
		if ($fila[idbanco] == $Banco_Default)
			echo " selected";
		echo ">".$fila[nombrebanco]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Tarjeta</b></td>";
	echo "<td align=left>";
	echo "<select name=Ingreso_Tarjeta_Nombre>\n";
	echo "<option value='' selected></option>\n";
	$sql = "SELECT * FROM bancos.tipo_tarjeta";
	$result = db_query($sql) or db_die($sql);
	while ($fila = db_fetch_row($result)) {
		echo "<option value=".$fila[idtipotar].">".$fila[tipotarjeta]."</option>\n";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td align=right><b>Fecha Depósito</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Ingreso_Tarjeta_Fecha size=10 title='Ingrese la fecha de depósito'>";
	echo link_calendario("Ingreso_Tarjeta_Fecha");
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Importe</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Ingreso_Tarjeta_Importe size=22 maxlength=50>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Ingreso_Debito_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table>\n";
	break;
case "Nue_Banco":
	// Mostrar tabla de bancos
	$sql = "select * from bancos.tipo_banco order by idbanco";
	$result = db_query($sql) or db_die($sql);
	echo "<form action=bancos.php method=post>\n";
	echo "<center><br><input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</center><br>\n";
	echo "<table align=center width=70%><tr><td>\n";
	echo "<table align=center width=90% cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Bancos</td></tr>";
	echo "<tr bordercolor='#000000' id=ma><td>Modificar</td><td align=center>ID</td>";
	echo "<td>Nombre</td></tr>\n";
	while ($fila = db_fetch_row($result)) {
		echo "<tr bordercolor='#000000'>";
		echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_NombreBanco.value=id_".$fila[idbanco].".value;document.forms[2].elements.Modificacion_IdBanco.value=".$fila[idbanco].";' type=radio name=Modificar_Banco_Id value='".$fila[idbanco]."'></td>";
		echo "<input type=hidden name=id_".$fila[idbanco]." value='".$fila[nombrebanco]."'>";
		echo "<td align=center>".$fila[idbanco]."</td>";
		echo "<td>".$fila[nombrebanco]."</td></tr>\n";
	}
	echo "</table>";
	echo "</form>";
	echo "</td><td align=center valign=top>";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Agregar un Banco</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center>";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=hidden name=cmd value=Nuevo_Banco>\n";
	echo "<input type=text name=Nombre_Banco size=25 maxlength=50><br>";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Nuevo_Banco value='Nuevo Banco'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</td></tr></table>\n";
	echo "</form>\n";
	// Modificar Nombre del banco
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<input type=hidden name=cmd value=Modificar_Banco>\n";
	echo "<input type=hidden name=Modificacion_IdBanco value=''>";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Modificar el Banco</td></tr>\n";
	echo "<tr bordercolor='#000000'><td align=center>\n";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Modificacion_NombreBanco size=25 maxlength=50 value=''><br>\n";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Modificacion_Banco_Guardar value='Guardar'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table>\n";
	break;
case "Nue_Tipo_Deposito":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<center><br>\n";
	echo "<input type=hidden name=cmd value=Nuevo_Tipo_Deposito>\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</center><br>\n";
	$sql = "select * from bancos.tipo_depósito order by idtipodep";
	$result = db_query($sql) or db_die($sql);
	echo "<table align=center width=70%><tr><td>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Depósitos</td></tr>";
	echo "<tr bordercolor='#000000' id=ma><td>Modificar</td><td align=center>ID</td>";
	echo "<td>Nombre</td></tr>\n";
	while ($fila = db_fetch_row($result)) {
		echo "<tr bordercolor='#000000'>";
		echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_TipoDeposito.value=id_".$fila[idtipodep].".value;document.forms[2].elements.Modificacion_IdTipoDep.value=".$fila[idtipodep].";' type=radio name=Modificar_Tipo_Dep value=''></td>";
		echo "<input type=hidden name=id_".$fila[idtipodep]." value='".$fila[tipodepósito]."'>";
		echo "<td align=center>".$fila[idtipodep]."</td>";
		echo "<td>".$fila[tipodepósito]."</td></tr>\n";
	}
	echo "</form>";
	echo "</table></td><td align=center valign=top>";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<input type=hidden name=cmd value=Nuevo_Tipo_Deposito>\n";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Agregar nuevo tipo de depósito</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center>";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Nombre_Tipo_Deposito size=25 maxlength=50>";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Nuevo_Tipo_Deposito value='Nuevo Tipo Depósito'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</td></tr></table>\n";
	echo "</form>\n";
	// Formulario de modificacion tipo depósito
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<input type=hidden name=cmd value=Modificar_Tipo_Deposito>\n";
	echo "<input type=hidden name=Modificacion_IdTipoDep value=''>";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Modificar el Tipo de Depósito</td></tr>\n";
	echo "<tr bordercolor='#000000'><td align=center>\n";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Modificacion_TipoDeposito size=25 maxlength=50 value=''><br>\n";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Modificacion_Tipo_Deposito_Guardar value='Guardar'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</table>\n";
	echo "</form>";
	break;
case "Nue_Tipo_Debito":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<center><br>\n";
	echo "<input type=hidden name=cmd value=Nuevo_Tipo_Debito>\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</center><br>\n";
	$sql = "select * from bancos.tipo_débito order by idtipodéb";
	$result = db_query($sql) or db_die($sql);
	echo "<table align=center width=70%><tr><td>\n";	
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Débitos</td></tr>";
	echo "<tr bordercolor='#000000' id=ma><td>Modificar</td><td align=center>ID</td>";
	echo "<td>Nombre</td></tr>\n";
	while ($fila = db_fetch_row($result)) {
		echo "<tr bordercolor='#000000'>";
		echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_TipoDebito.value=id_".$fila[idtipodéb].".value;document.forms[2].elements.Modificacion_IdTipoDeb.value=".$fila[idtipodéb].";' type=radio name=Modificar_TipoDebito value=''></td>";
		echo "<input type=hidden name=id_".$fila[idtipodéb]." value='".$fila[tipodébito]."'>";		
		echo "<td align=center>".$fila[idtipodéb]."</td>";
		echo "<td>".$fila[tipodébito]."</td></tr>\n";
	}
	echo "</form>";
	echo "</table></td><td align=center valign=top>";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<input type=hidden name=cmd value=Nuevo_Tipo_Debito>\n";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Agregar nuevo tipo de Débito</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center>";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Nombre_Tipo_Debito size=25 maxlength=50>";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Nuevo_Tipo_Debito value='Nuevo Tipo Débito'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</td></tr></table>\n";
	echo "</form>\n";
	// Formulario de modificacion tipo Débito
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<input type=hidden name=cmd value=Modificar_Tipo_Debito>\n";
	echo "<input type=hidden name=Modificacion_IdTipoDeb value=''>";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Modificar el Tipo de Débito</td></tr>\n";
	echo "<tr bordercolor='#000000'><td align=center>\n";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Modificacion_TipoDebito size=25 maxlength=50 value=''><br>\n";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Modificacion_Tipo_Debito_Guardar value='Guardar'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</table>\n";
	echo "</form>";
	break;
case "Nue_Tipo_Tarjeta":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<center><br><input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</center><br>\n";
	$sql = "select * from bancos.tipo_tarjeta order by idtipotar";
	$result = db_query($sql) or db_die($sql);
	echo "<table align=center width=70%><tr><td>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Tipo Tarjeta</td></tr>";
	echo "<tr bordercolor='#000000' id=ma><td>&nbsp;</td><td align=center>ID</td>";
	echo "<td>Nombre</td></tr>\n";
	while ($fila = db_fetch_row($result)) {
		echo "<tr bordercolor='#000000'>";
		echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_TipoTarjeta.value=id_".$fila[idtipotar].".value;document.forms[2].elements.Modificacion_IdTipoTar.value=".$fila[idtipotar].";' type=radio name=Modificar_TipoTarjeta value=''></td>";
		echo "<input type=hidden name=id_".$fila[idtipotar]." value='".$fila[tipotarjeta]."'>";
		echo "<td align=center>".$fila[idtipotar]."</td>";
		echo "<td>".$fila[tipotarjeta]."</td></tr>\n";
	}
	echo "</form>";
	echo "</table></td><td align=center valign=top>";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";
	echo "<input type=hidden name=cmd value=Nuevo_Tipo_Tarjeta>\n";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Agregar nuevo tipo de Tarjeta</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center>";
	echo "<table align=center cellpadding=0 cellspacing=3>\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Nombre_Tipo_Tarjeta size=25 maxlength=50>";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Nuevo_Tipo_Tarjeta value='Nuevo Tipo Tarjeta'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</td></tr></table>\n";
	echo "</form>\n";
	// Formulario de modificacion tipo Débito
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<input type=hidden name=cmd value=Modificar_Tipo_Tarjeta>\n";
	echo "<input type=hidden name=Modificacion_IdTipoTar value=''>";
	echo "<table width=90% align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Modificar el Tipo de Tarjeta</td></tr>\n";
	echo "<tr bordercolor='#000000'><td align=center>\n";
	echo "<table align=center cellpadding=0 cellspacing=5 >\n";
	echo "<tr><td>\n";
	echo "<b>Nombre</b>\n";
	echo "</td><td>\n";
	echo "<input type=text name=Modificacion_TipoTarjeta size=25 maxlength=50 value=''><br>\n";
	echo "</td></tr><tr><td align=center colspan=2>\n";
	echo "<input type=submit name=Modificacion_Tipo_Tarjeta_Guardar value='Guardar'>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</table>\n";
	echo "</form>";
	break;
case "Mant_Proveedores":
	if ($cmd1=="detalle") {
		echo "<br><form action=bancos.php method=post>\n";
		echo "<input type=hidden name=mode value=data>\n";
		echo "<input type=hidden name=cmd value=Modificar_Proveedor>\n";
		NuevProv($ID);
		break;
	}
	if ($_POST[Nuevo_Proveedor]=="Nuevo Proveedor") {
		echo "<br><form action=bancos.php method=post>\n";
		echo "<input type=hidden name=mode value=data>\n";
		echo "<input type=hidden name=cmd value=Nuevo_Proveedor>\n";
		NuevProv($ID);
		break;
	}
	// Barra de consulta para enviarle al formulario
	echo "<form action='bancos.php' method='post'>";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<input type=hidden name=mode value='$mode'>\n";
	echo "<input type=hidden name=short value='short'>\n";
	echo "<table width=100% border=1 cellspacing=5 cellpadding=5 align=center>\n";
	echo "<tr><td colspan=6 align=center>\n";

	$page = $_GET["page"] or $page = 0;
	$filter = $_POST["filter"] or $filter = $_GET["filter"];
	$keyword = $_POST["keyword"] or $keyword = $_GET["keyword"];

	$orden = array(
		"default" => "2",
		"1" => "IdProv",
		"2" => "Proveedor",
		"3" => "Contacto",
		"4" => "Mail",
		"5" => "Teléfono",
		"6" => "Comentarios"
	);

	$filtro = array(
		"Proveedor"		=> "Proveedor",
		"Contacto"		=> "Contacto",
		"Mail"			=> "Mail",
		"Teléfono"		=> "Teléfono",
		"Comentarios"	=> "Comentarios",
		"Domicilio"		=> "Domicilio",
		"Provincia"		=> "Provincia",
		"CUIT"			=> "CUIT",
		"Localidad"		=> "Localidad"
	);

	$sql_tmp = "SELECT IdProv,Proveedor,Contacto,Mail,Teléfono,Comentarios FROM bancos.proveedores";
	$link_tmp = "<a id=ma href='bancos.php?mode=$mode&cmd=$cmd";
	list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp);
//	echo "sql: $sql total: $totalProv link: $link_pagina<Br>"; exit;

echo "<input type=submit name=Nuevo_Proveedor value='Nuevo Proveedor'>&nbsp;&nbsp;";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</form>\n";
	echo "</td></tr></table><br>\n";

	$result = db_query($sql) or db_die($sql);

	echo "<table border=1 width=100% cellspacing=0 cellpadding=3 bordercolor='0' align=center>";
	echo "<tr><td colspan=2 align=left id=ma>\n";
	echo "<b>Total:</b> $total_Prov Proveedores.</td>\n";
	echo "<td colspan=4 align=right id=ma>$link_pagina</td></tr>\n";
	echo "<tr><td align=right id=mo><a id=mo href='bancos.php?mode=$mode&cmd=$cmd&sort=1&up=$up&page=$page&keyword=$keyword&filter=$filter'>ID</a></td>\n";
	echo "<td align=right id=mo><a id=mo href='bancos.php?mode=$mode&cmd=$cmd&sort=2&up=$up&page=$page&keyword=$keyword&filter=$filter'>Proveedores</td>\n";
	echo "<td align=right id=mo><a id=mo href='bancos.php?mode=$mode&cmd=$cmd&sort=3&up=$up&page=$page&keyword=$keyword&filter=$filter'>Contacto</td>\n";
	echo "<td align=right id=mo><a id=mo href='bancos.php?mode=$mode&cmd=$cmd&sort=4&up=$up&page=$page&keyword=$keyword&filter=$filter'>Mail</td>\n";
	echo "<td align=right id=mo><a id=mo href='bancos.php?mode=$mode&cmd=$cmd&sort=5&up=$up&page=$page&keyword=$keyword&filter=$filter'>Teléfono</td>\n";
	echo "<td align=right id=mo><a id=mo href='bancos.php?mode=$mode&cmd=$cmd&sort=5&up=$up&page=$page&keyword=$keyword&filter=$filter'>Comentario</td>\n";
	echo "</tr>\n";
	while ($row = db_fetch_row($result)) {
		$ref = "bancos.php?mode=forms&cmd=$cmd&cmd1=detalle&ID=$row[0]&PHPSESSID=$PHPSESSID";
	    tr_tag($ref,"title='Haga click aqui para ver o modificar los datos del proveedor'");
		echo "<td align=center id=ma><a href='$ref'>$row[0]</a></td>\n";
		echo "<td align=left>&nbsp;$row[1]</td>\n";
		echo "<td align=left>&nbsp;$row[2]</td>\n";
		echo "<td align=left>&nbsp;$row[3]</td>\n";
		echo "<td align=left>&nbsp;$row[4]</td>\n";
		echo "<td align=left>&nbsp;$row[5]</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
	break;
case "Val_Cheque_de_Terceros":
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data>\n";

	//Total	
	$sql = "SELECT sum(Importe) AS total FROM bancos.cheques_de_terceros";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total = formato_money($res_tmp[total]);

	//Total Vencido
	$sql = "SELECT sum(Importe) AS total FROM bancos.cheques_de_terceros WHERE Estado='Caja' AND FechaVto<='".date("Y-m-d",mktime())."'";
	$result = db_query($sql) or db_die($sql);
	$res_tmp = db_fetch_row($result);
	$Total_Vencido = formato_money($res_tmp[total]);

	//Javascript
	echo "<script language='JavaScript' type='text/JavaScript'>\n";
	echo "<!--\n";
	echo "function ConfirmaUpdate(Msg,Obj) {\n";
	echo "if (Obj.value != \"\") {\n";
	echo "if (confirm(Msg)) {\n";
	echo "eval('document.forms[0].submit()');";
	echo "}\n}\n";
	echo "return true;\n";
	echo "}\n-->\n</script>\n";
	
	//Datos
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor2>";
	echo "<tr><td colspan=3 align=left><b>Cheques a depositar hoy: \$ $Total_Vencido</b>";
	echo "</td>\n";
	echo "<td colspan=3 align=right><b>Valores recibidos: \$ $Total</b>";
	echo "</td></tr>";
	echo "<tr><td colspan=6 align=center>";
	echo "<input type=hidden name=cmd value='Cheques_de_Terceros_Actualizar'>";
	echo "<input type=submit name=Actualizar value='Actualizar'>&nbsp;&nbsp;&nbsp;";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
	echo "</td></tr>\n";
	$sql = "SELECT * FROM bancos.cheques_de_terceros ";
	$sql .= "WHERE bancos.cheques_de_terceros.Estado='Caja' ";
	$sql .= "ORDER BY bancos.cheques_de_terceros.FechaVto";
	$result = db_query($sql) or db_die($sql);
	$SubTotal = 0;
	echo "<tr bordercolor='#000000'><td id=mo colspan=6 align=center>Cheques de Terceros</td></tr>";
	echo "<tr bordercolor='#000000' id=ma>";
	echo "<td align=center>Vencimiento</td>";
	echo "<td align=center>Banco</td>";
	echo "<td align=center>Número</td>";
	echo "<td align=center>Importe</td>";
	echo "<td align=center>Librador</td>";
	echo "<td align=center>Estado</td>";
	echo "</tr>\n";
	while ($fila = db_fetch_row($result)) {
		$SubTotal += $fila[importe];
		echo "<tr bordercolor='#000000'>\n";
		echo "<td align=center>".Fecha($fila[fechavto])."</td>\n";
		echo "<td align=left>".$fila[banco]."</td>\n";
		echo "<td align=center>".$fila[númche]."</td>\n";
		echo "<td align=right>\$ ".formato_money($fila[importe])."</td>\n";
		echo "<td align=left>".$fila[librador]."</td>\n";
		echo "<td align=center>";
//		echo "<input type=text size=10 maxlength=50 name=Estado_Cheque[NC".$fila[IdReg]."] value='".$fila[Estado]."' onBlur=\"ConfirmaUpdate('¿Desea actualizar los datos del cheque número ".$fila[NúmChe]."?',this);\">";
		echo "<input type=text size=10 maxlength=50 name=Estado_Cheque[NC".$fila[númche]."] value='".$fila[estado]."'>";
		echo "<input type=hidden size=10 maxlength=50 name=Estado_Cheque_Old[NC".$fila[númche]."] value='".$fila[estado]."'>";
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "<tr><td colspan=6 align=center><b>Total en caja: \$ ".formato_money($SubTotal)."</b></td></tr>";
	echo "</table></form>\n";
	break;
case "Val_Ingreso_Cheque":
	$Fecha_Hoy=date("d/m/Y",mktime());
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=data><br>\n";
	echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor2'>\n";
	echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Cheques de Terceros</td></tr>";
	echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0>";
	echo "<tr><td align=right><b>Fecha Vencimiento</b></td>";
	echo "<td align=left>";
	echo "<input type=text size=10 name=Cheques_de_Terceros_Vencimiento value='$Fecha_Hoy' title='Ingrese la fecha de vencimiento del cheque'>";
	echo link_calendario("Cheques_de_Terceros_Vencimiento");
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Banco</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Cheques_de_Terceros_Banco size=22 maxlength=50 title='Ingrese el nombre del banco'>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Número</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Cheques_de_Terceros_Numero size=22 maxlength=50 title='Ingrese el número del cheque'>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Importe</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Cheques_de_Terceros_Importe size=22 maxlength=50 title='Ingrese el importe del cheque'>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=right><b>Librador</b>\n";
	echo "</td><td>";
	echo "<input type=text name=Cheques_de_Terceros_Librador size=22 maxlength=50 title='Ingrese el nombre del librador del cheque'>&nbsp;";
	echo "</td></tr>\n";
	echo "<tr><td align=center colspan=2>\n";
	echo "<input type=hidden name=cmd value=Cheques_de_Terceros_Ingresar>";
	echo "<input type=submit name=Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</td></tr>\n";
	echo "</table>";
	echo "</td></tr>\n";
	echo "</table>\n";
	break;
case "Imp_Cheques_por_Fecha":
//	phpinfo();
	echo "<br>\n";
	Error("Esta función está deshabilitada");
	echo "<center><form>\n";
	echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
	echo "</form></center>\n";
	break;
default:
	Error($cmd.": Comando desconocido");
	break;
}
?>