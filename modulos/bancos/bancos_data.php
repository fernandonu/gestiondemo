<?
/* 
$Revision: 1.2 $ 
$Id: bancos_data.php,v 1.2 2006/03/13 21:41:34 ferni Exp $ 
*/
if (!defined("lib_included")) { die("Use index.php!"); }

session_start();

/**
 * @return string
 * @param msg string
 * @desc Formatea $msg para mostrarlo centrado en la pagina
 *       y en una fuente de tama�o 4
 */
switch ($_POST[cmd]) {
case "Cheques_Pendientes_Actualizar":
	while (list($var,$val) = each($_POST)) {
	  if (ereg("^Fecha_Cheques_Pendientes_NC",$var)) {
		$num_cheque = str_replace("Fecha_Cheques_Pendientes_NC","",$var);
		$fecha = $val;
	  	if ($fecha != "") {
			list($d,$m,$a) = explode("/",$fecha);
			if (Fecha_ok($d,$m,$a)) {
				$sql = "UPDATE bancos.cheques ";
				$sql .= "SET FechaD�bCh='$a-$m-$d' ";
				$sql .= "WHERE FechaD�bCh IS NULL AND ";
				$sql .= "N�meroCh=$num_cheque";
				$result = db_query($sql) or db_die($sql);
				$actualizado = 1;
				Aviso("Los datos del cheque n�mero $num_cheque se actualizaron correctamente.");
			}
			else {
				Error("Formato de fecha inv�lido para el cheque n�mero $num_cheque");
			}
		}
	  }
	}
	if (!$actualizado) {
		if (!$error) {
			Aviso("No hab�a ning�n dato para actualizar");
		}
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Cheques_Pendientes>\n";
	echo "<input type=hidden name=Mov_Cheques_Pendientes_Banco value=".$_POST[IdBanco].">\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Depositos_Pendientes_Actualizar":
	while (list($var,$val) = each($_POST)) {
	  if (ereg("^Fecha_Depositos_Pendientes_ND",$var)) {
		$num_deposito = str_replace("Fecha_Depositos_Pendientes_ND","",$var);
		$fecha = $val;
		if ($fecha != "") {
			list($d,$m,$a) = explode("/",$fecha);
			if (Fecha_ok($d,$m,$a)) {
				$sql = "UPDATE bancos.dep�sitos ";
				$sql .= "SET FechaCr�dito='$a-$m-$d' ";
				$sql .= "WHERE FechaCr�dito IS NULL AND ";
				$sql .= "IdDep�sito=$num_deposito";
				$result = db_query($sql) or db_die($sql);
				$actualizado = 1;
				Aviso("Los datos del dep�sito n�mero $num_deposito se actualizaron correctamente.");
			}
			else {
				Error("Formato de fecha inv�lido para el deposito n�mero $num_deposito");
			}
		}
	  }
	}
	if (!$actualizado) {
		if (!$error) {
			Aviso("No hab�a ning�n dato para actualizar");
		}
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Depositos_Pendientes>\n";
	echo "<input type=hidden name=Mov_Depositos_Pendientes_Banco value=".$_POST[IdBanco].">\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Tarjetas_Pendientes_Actualizar":
	while (list($var,$val) = each($_POST)) {
	  if (ereg("^Fecha_Tarjetas_Pendientes_NT",$var)) {
		$num_tarjeta = str_replace("Fecha_Tarjetas_Pendientes_NT","",$var);
		$fecha = $val;
		$importe = $_POST[Importe_Tarjetas_Pendientes][$num_tarjeta];
		if ($fecha != "") {
			if (!es_numero($importe)) {
				Error("Falta ingresar el Importe para la tarjeta ID $num_tarjeta");
				break;
			}
			list($d,$m,$a) = explode("/",$fecha);
			if (Fecha_ok($d,$m,$a)) {
				$sql = "UPDATE bancos.tarjetas ";
				$sql .= "SET FechaCr�dTar='$a-$m-$d', ";
				$sql .= "ImporteCr�dTar=$importe ";
				$sql .= "WHERE FechaCr�dTar IS NULL AND ";
				$sql .= "IdTarjeta=$num_tarjeta";
				$result = db_query($sql) or db_die($sql);
				$actualizado = 1;
				Aviso("Los datos de la tarjeta ID $num_tarjeta se actualizaron correctamente");
			}
			else {
				Error("Formato de fecha inv�lido para la tarjeta ID $num_tarjeta");
			}
		}
		else {
			if ($importe != "" and es_numero($importe)) {
				Error("Falta ingresar la Fecha para la tarjeta ID $num_tarjeta");
				break;
			}
		}
	  }
	}
	if (!$actualizado) {
		if (!$error) {
			Aviso("No hab�a ning�n dato para actualizar");
		}
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Mov_Tarjetas_Pendientes>\n";
	echo "<input type=hidden name=Mov_Tarjetas_Pendientes_Banco value=".$_POST[IdBanco].">\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Cheques_de_Terceros_Actualizar":
	while (list($id,$estado) = each($_POST[Estado_Cheque])) {
		$estado_old = $_POST[Estado_Cheque_Old][$id];
		$num_cheque = substr($id,2);
		if ($estado != "") {
			if ($estado != $estado_old) {
				$sql = "UPDATE bancos.cheques_de_terceros ";
				$sql .= "SET Estado='$estado' ";
				$sql .= "WHERE N�mChe=$num_cheque ";
				$result = db_query($sql) or db_die($sql);
				$actualizado = 1;
			}
		}
		else {
			Error("Falta ingresar el Estado del cheque n�mero $num_cheque");
			break;
		}
	}
	if (!$error) {
		if ($actualizado) {
			Aviso("Los datos se actualizaron correctamente");
		}
		else {
			Aviso("No hab�a ning�n dato para actualizar");
		}
	}
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Val_Cheque_de_Terceros>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Cheques_de_Terceros_Ingresar":
	$fecha_ven = $_POST[Cheques_de_Terceros_Vencimiento];
	$fecha_ing = date("Y-m-d",mktime());
	$banco = $_POST[Cheques_de_Terceros_Banco];
	$numero = $_POST[Cheques_de_Terceros_Numero];
	$importe = $_POST[Cheques_de_Terceros_Importe];
	$librador = $_POST[Cheques_de_Terceros_Librador];
	list($d,$m,$a) = explode("/",$fecha_ven);
	if (Fecha_ok($d,$m,$a)) {
		$fecha_ven = "$a-$m-$d";
	}
	else {
		Error("La fecha de vencimiento ingresada es inv�lida");
	}
	if ($banco == "") {
		Error("Falta ingresar el nombre del Banco");
	}
	if ($numero == "") {
		Error("Falta ingresar el N�mero del Cheque");
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe del Cheque");
	}
	if ($librador == "") {
		Error("Falta ingresar el Librador del Cheque");
	}
	$sql = "SELECT * FROM bancos.cheques_de_terceros WHERE N�mChe=$numero";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe un Cheque con el N�mero $numero!");
	}
	if (!$error) {
		$sql = "INSERT INTO bancos.cheques_de_terceros ";
		$sql .= "(FechaIng, FechaVto, Banco, N�mChe, Importe, Librador) ";
		$sql .= "VALUES ('$fecha_ing','$fecha_ven','$banco',$numero,$importe,'$librador')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Val_Ingreso_Cheque>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Nuevo_Banco":
	$nombre = $_POST[Nombre_Banco];
	if ($nombre == "") {
		Error("Falta ingresar el Nombre del Banco");
	}
	$sql = "SELECT * FROM bancos.tipo_banco WHERE NombreBanco LIKE '$nombre'";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe un Banco con el Nombre '$nombre'!");
	}
	if (!$error) {
		$sql = "INSERT INTO bancos.tipo_banco ";
		$sql .= "(NombreBanco) ";
		$sql .= "VALUES ('$nombre')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	$cmd="Nue_Banco";
	include "./bancos_forms.php";
	break;
case "Modificar_Banco":
	$id = $_POST[Modificacion_IdBanco];
	$nombrebanco = $_POST[Modificacion_NombreBanco];
	if (!$id) {
		Error("Debe marcar un banco para modificarlo");
	}
	else {
		if (!$error) {
			$sql = "UPDATE bancos.tipo_banco SET ";
			$sql .="NombreBanco='$nombrebanco' ";
			$sql .="Where IdBanco=$id";
			$result = db_query($sql) or db_die($sql);
			Aviso("Los datos se Modificaron correctamente");
		}
	}
	$cmd="Nue_Banco";
	include "./bancos_forms.php";	
	break;
case "Nuevo_Tipo_Deposito":
	$nombre = $_POST[Nombre_Tipo_Deposito];
	if ($nombre == "") {
		Error("Falta ingresar el Nombre del Tipo de Deposito");
	}
	$sql = "SELECT * FROM bancos.tipo_dep�sito WHERE TipoDep�sito LIKE '$nombre'";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe un Tipo de Dep�sito con el Nombre '$nombre'!");
	}
	if (!$error) {
		$sql = "INSERT INTO bancos.tipo_dep�sito ";
		$sql .= "(TipoDep�sito) ";
		$sql .= "VALUES ('$nombre')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	$cmd="Nue_Tipo_Deposito";
	include "./bancos_forms.php";
	break;
case "Modificar_Tipo_Deposito":
	$id = $_POST[Modificacion_IdTipoDep];
	$nombredepo = $_POST[Modificacion_TipoDeposito];
	if (!$id) {
		Error("Debe marcar el tipo de dep�sito para modificarlo");
	}
	else {
		if (!$error) {
			$sql = "UPDATE bancos.tipo_dep�sito SET ";
			$sql .="TipoDep�sito='$nombredepo' ";
			$sql .="Where IdTipoDep=$id";
			$result = db_query($sql) or db_die($sql);
			Aviso("Los datos se Modificaron correctamente");
		}
	}
	$cmd="Nue_Tipo_Deposito";
	include "./bancos_forms.php";	
	break;
case "Nuevo_Tipo_Debito":
	$nombre = $_POST[Nombre_Tipo_Debito];
	if ($nombre == "") {
		Error("Falta ingresar el Nombre del Tipo de D�bito");
	}
	$sql = "SELECT * FROM bancos.tipo_d�bito WHERE TipoD�bito LIKE '$nombre'";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe un Tipo de D�bito con el Nombre '$nombre'!");
	}
	if (!$error) {
		$sql = "INSERT INTO bancos.tipo_d�bito ";
		$sql .= "(TipoD�bito) ";
		$sql .= "VALUES ('$nombre')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	$cmd="Nue_Tipo_Debito";
	include "./bancos_forms.php";
	break;
case "Modificar_Tipo_Debito":
	$id = $_POST[Modificacion_IdTipoDeb];
	$nombredeb = $_POST[Modificacion_TipoDebito];
	if (!$id) {
		Error("Debe marcar el tipo de d�bito para modificarlo");
	}
	else {
		if (!$error) {
			$sql = "UPDATE bancos.tipo_d�bito SET ";
			$sql .="TipoD�bito='$nombredeb' ";
			$sql .="Where IdTipoD�b=$id";
			$result = db_query($sql) or db_die($sql);
			Aviso("Los datos se Modificaron correctamente");
		}
	}
	$cmd="Nue_Tipo_Debito";
	include "./bancos_forms.php";	
	break;
case "Nuevo_Tipo_Tarjeta":
	$nombre = $_POST[Nombre_Tipo_Tarjeta];
	if ($nombre == "") {
		Error("Falta ingresar el Nombre de la Tarjeta");
	}
	$sql = "SELECT * FROM bancos.tipo_tarjeta WHERE TipoTarjeta LIKE '$nombre'";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe una Tarjeta con el Nombre '$nombre'!");
	}
	if (!$error) {
		$sql = "INSERT INTO bancos.tipo_tarjeta ";
		$sql .= "(TipoTarjeta) ";
		$sql .= "VALUES ('$nombre')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	$cmd="Nue_Tipo_Tarjeta";
	include "./bancos_forms.php";
	break;
case "Modificar_Tipo_Tarjeta":
	$id = $_POST[Modificacion_IdTipoTar];
	$nombretar = $_POST[Modificacion_TipoTarjeta];
	if (!$id) {
		Error("Debe marcar el tipo de tarjeta para modificarlo");
	}
	else {
		if (!$error) {
			$sql = "UPDATE bancos.tipo_tarjeta SET ";
			$sql .="TipoTarjeta='$nombretar' ";
			$sql .="Where IdTipoTar=$id";
			$result = db_query($sql) or db_die($sql);
			Aviso("Los datos se Modificaron correctamente");
		}
	}
	$cmd="Nue_Tipo_Tarjeta";
	include "./bancos_forms.php";	
	break;
case "Ingreso_Deposito":
	$banco = $_POST[Ingreso_Deposito_Banco];
	$tipo = $_POST[Ingreso_Deposito_Tipo];
	$fecha = $_POST[Ingreso_Deposito_Fecha];
	$importe = $_POST[Ingreso_Deposito_Importe];
	list($d,$m,$a) = explode("/",$fecha);
	if (Fecha_ok($d,$m,$a)) {
		$fecha = "$a-$m-$d";
	}
	else {
		Error("La fecha de dep�sito es inv�lida");
	}
	if ($tipo == "") {
		Error("Falta ingresar el Tipo de Dep�sito");
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe del Dep�sito");
	}
	elseif (!es_numero($importe)) {
		Error("El Importe ingresado es inv�lido");
	}
	
	if (!$error) {
		$sql = "INSERT INTO bancos.dep�sitos ";
		$sql .= "(IdBanco, FechaDep�sito, FechaCr�dito, IdTipoDep, ImporteDep) ";
		$sql .= "VALUES ($banco,'$fecha',NULL,$tipo,$importe)";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Ing_Depositos>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Ingreso_Debito":
	$banco = $_POST[Ingreso_Debito_Banco];
	$tipo = $_POST[Ingreso_Debito_Tipo];
	$fecha = $_POST[Ingreso_Debito_Fecha];
	$importe = $_POST[Ingreso_Debito_Importe];
	list($d,$m,$a) = explode("/",$fecha);
	if (Fecha_ok($d,$m,$a)) {
		$fecha = "$a-$m-$d";
	}
	else {
		Error("La fecha de d�bito es inv�lida");
	}
	if ($tipo == "") {
		Error("Falta ingresar el Tipo de D�bito");
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe del D�bito");
	}
	elseif (!es_numero($importe)) {
		Error("El Importe ingresado no es v�lido");
	}
	
	if (!$error) {
		
		$query="select nextval('d�bitos_idd�bito_seq') as id_debito";
        $id_deb=sql($query,"<br>Error al traer id de debito<br>") or fin_pagina();
        $iddebito=$id_deb->fields["id_debito"];
        
		$sql = "INSERT INTO bancos.d�bitos ";
		$sql .= "(idd�bito,IdBanco, FechaD�bito, IdTipoD�b, ImporteD�b) ";
		$sql .= "VALUES ($iddebito,$banco,'$fecha',$tipo,$importe)";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
		
		//inserto el log de debitos
        $user_login=$_ses_user_name;       
		$fecha_log_debito=date('Y-m-d H:i:s');
		$tipo_log=1;//alta del debito
		$sql = "INSERT INTO bancos.log_debitos
		(idd�bito,user_login,fecha,tipo_log,comentario) 
		VALUES ($iddebito,'$user_login','$fecha_log_debito',$tipo_log,'Alta de Debito')";
		sql ($sql,"No se puede insertar el log de debitos")or fin_pagina();				       
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Ing_Debitos>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Ingreso_Tarjeta":
	$banco = $_POST[Ingreso_Tarjeta_Banco];
	$tipo = $_POST[Ingreso_Tarjeta_Nombre];
	$fecha = $_POST[Ingreso_Tarjeta_Fecha];
	$importe = $_POST[Ingreso_Tarjeta_Importe];
	list($d,$m,$a) = explode("/",$fecha);
	if (Fecha_ok($d,$m,$a)) {
		$fecha = "$a-$m-$d";
	}
	else {
		Error("La fecha de dep�sito es inv�lida");
	}
	if ($tipo == "") {
		Error("Falta ingresar la Tarjeta");
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe");
	}
	elseif (!es_numero($importe)) {
		Error("El Importe ingresado no es v�lido");
	}
	
	if (!$error) {
		$sql = "INSERT INTO bancos.tarjetas ";
		$sql .= "(IdBanco, FechaDepTar, IdTipoTar, ImporteDepTar, FechaCr�dTar) ";
		$sql .= "VALUES ($banco,'$fecha',$tipo,$importe,NULL)";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Ing_Tarjetas>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Ingreso_Cheque":
	$banco = $_POST[Ingreso_Cheque_Banco];
	$proveedor = $_POST[Ingreso_Cheque_Proveedor];
	$fecha_e = $_POST[Ingreso_Cheque_Fecha_Emision];
	$fecha_v = $_POST[Ingreso_Cheque_Fecha_Vencimiento];
	$fecha_p = $_POST[Ingreso_Cheque_Fecha_Debito];
	$numero = $_POST[Ingreso_Cheque_Numero];
	$importe = $_POST[Ingreso_Cheque_Importe];
	$comentarios = $_POST[Ingreso_Cheque_Comentarios];
	list($d,$m,$a) = explode("/",$fecha_e);
	if (Fecha_ok($d,$m,$a)) {
		$fe_db = "$a-$m-$d";
	}
	else {
		Error("La fecha de Emisi�n ingresada es inv�lida");
	}
	list($d,$m,$a) = explode("/",$fecha_v);
	if (Fecha_ok($d,$m,$a)) {
		$fv_db = "$a-$m-$d";
	}
	else {
		Error("La fecha de Vencimiento ingresada es inv�lida");
	}
	if ($fecha_p == "") {
		$fp_db = "2000-00-00";
	}
	else {
		list($d,$m,$a) = explode("/",$fecha_p);
		if (Fecha_ok($d,$m,$a)) {
			$fp_db = "$a-$m-$d";
		}
		else {
			Error("La fecha de D�bito ingresada es inv�lida");
		}
	}
	if ($proveedor == "") {
		Error("Falta ingresar el Proveedor");
	}
	if ($numero == "") {
		Error("Falta ingresar el N�mero del Cheque");
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe");
	}
	elseif (!es_numero($importe)) {
		Error("El Importe ingresado es inv�lido");
	}
	
	if (!$error) {
		$sql = "INSERT INTO bancos.cheques ";
		$sql .= "(IdBanco, FechaEmiCh, FechaVtoCh, FechaPrev, FechaD�bCh, N�meroCh, ImporteCh, IdProv, Comentarios) ";
		$sql .= "VALUES ($banco,'$fe_db','$fv_db','$fp_db',NULL,$numero,$importe,$proveedor,'$comentarios')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=cmd value=Ing_Cheques>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Nuevo_Proveedor":
	$nombre = $_POST[Nuevo_Proveedor_Nombre];
	$cuit = $_POST[Nuevo_Proveedor_CUIT];
	$domicilio = $_POST[Nuevo_Proveedor_Domicilio];
	$cp = $_POST[Nuevo_Proveedor_CP];
	$localidad = $_POST[Nuevo_Proveedor_Localidad];
	$provincia = $_POST[Nuevo_Proveedor_Provincia];
	$contacto = $_POST[Nuevo_Proveedor_Contacto];
	$mail = $_POST[Nuevo_Proveedor_Mail];
	$telefono = $_POST[Nuevo_Proveedor_Telefono];
	$fax = $_POST[Nuevo_Proveedor_Fax];
	$comentarios = $_POST[Nuevo_Proveedor_Comentarios];
	if ($nombre == "") {
		Error("Falta ingresar el Nombre del Nuevo Proveedor");
	}
	if (!es_numero($cuit)) {
		Error("El N�mero de CUIT ingresado no es v�lido");
	}
	if (!es_numero($cp)) {
		Error("El C�digo Postal ingresado no es v�lido");
	}
	$sql = "SELECT * FROM bancos.proveedores WHERE Proveedor LIKE '$nombre'";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe un Proveedor con el Nombre '$nombre'!");
	}
	if (!$error) {
		$sql = "INSERT INTO bancos.proveedores ";
		$sql .= "(Proveedor, CUIT, Domicilio, CP, Localidad, Provincia, Contacto, Mail, Tel�fono, Fax, Comentarios) ";
		$sql .= "VALUES ('$nombre', $cuit, '$domicilio', $cp, '$localidad', '$provincia', '$contacto', '$mail', '$telefono', '$fax', '$comentarios')";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
		$sql = "SELECT IdProv FROM bancos.proveedores WHERE ";
		$sql .= "Proveedor='$nombre'";
		$result = db_query($sql) or db_die($sql);
		$row = db_fetch_row($result);
		$id_prov = $row[0];
	}
	if (!$_POST[Ingreso_Cheque_Banco]) {
		$cmd="Mant_Proveedores";
		include "./bancos_forms.php";
		break;
	}
	$Ing_Banco=$_POST[Ingreso_Cheque_Banco];
	$Ing_Fecha_Emision = $_POST[Ingreso_Cheque_Fecha_Emision];
	$Ing_Fecha_Vencimiento = $_POST[Ingreso_Cheque_Fecha_Vencimiento];
	$Ing_Fecha_Debito = $_POST[Ingreso_Cheque_Fecha_Debito];
	$Ing_Importe = $_POST[Ingreso_Cheque_Importe];
	$Ing_Comentarios = $_POST[Ingreso_Cheque_Comentarios];
	$Ing_Numero = $_POST[Ingreso_Cheque_Numero];
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Proveedor value=$id_prov>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Banco value='$Ing_Banco'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$Ing_Fecha_Emision'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$Ing_Fecha_Vencimiento'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Fecha_Debito value='$Ing_Fecha_Debito'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Importe value='$Ing_Importe'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Comentarios value='$Ing_Comentarios'>\n";
	echo "<input type=hidden name=Ingreso_Cheque_Numero value='$Ing_Numero'>\n";
	echo "<input type=hidden name=cmd value=Ing_Cheques>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Modificar_Proveedor":
	$id = $_POST[Nuevo_Proveedor_Id];
	$nombre = $_POST[Nuevo_Proveedor_Nombre];
	$cuit = $_POST[Nuevo_Proveedor_CUIT];
	$domicilio = $_POST[Nuevo_Proveedor_Domicilio];
	$cp = $_POST[Nuevo_Proveedor_CP];
	$localidad = $_POST[Nuevo_Proveedor_Localidad];
	$provincia = $_POST[Nuevo_Proveedor_Provincia];
	$contacto = $_POST[Nuevo_Proveedor_Contacto];
	$mail = $_POST[Nuevo_Proveedor_Mail];
	$telefono = $_POST[Nuevo_Proveedor_Telefono];
	$fax = $_POST[Nuevo_Proveedor_Fax];
	$comentarios = $_POST[Nuevo_Proveedor_Comentarios];
	if ($nombre == "") {
		Error("Falta ingresar el Nombre del Nuevo Proveedor");
	}
	if (!es_numero($cuit)) {
		Error("El N�mero de CUIT ingresado no es v�lido");
	}
	if (!es_numero($cp)) {
		Error("El C�digo Postal ingresado no es v�lido");
	}
	$sql = "SELECT * FROM bancos.proveedores WHERE Proveedor='$nombre' and IdProv<>$id";
	$result = db_query($sql) or db_die($sql);
	if (db_query_rows($result) > 0) {
		Error("Ya existe un Proveedor con el Nombre '$nombre'!");
	}
	if (!$error) {
		$sql = "UPDATE bancos.proveedores SET ";
		$sql .= "Proveedor='$nombre', ";
		$sql .= "CUIT=$cuit, ";
		$sql .= "Domicilio='$domicilio', ";
		$sql .= "CP=$cp, ";
		$sql .= "Localidad='$localidad', ";
		$sql .= "Provincia='$provincia', ";
		$sql .= "Contacto='$contacto', ";
		$sql .= "Mail='$mail', ";
		$sql .= "Tel�fono='$telefono', ";
		$sql .= "Fax='$fax', ";
		$sql .= "Comentarios='$comentarios'";
		$sql .= "WHERE IdProv=$id";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
		$cmd="Mant_Proveedores";
		include "./bancos_forms.php";
	}
	break;
case "Modificar_Cheque":
	$fecha_debito = $_POST[Modificacion_Cheque_Fecha_Debito];
	$numero = $_POST[Modificacion_Cheque_Numero];
	$importe = $_POST[Modificacion_Cheque_Importe];
	$comentarios = $_POST[Modificacion_Cheque_Comentarios];
	$idbanco = $_POST[Modificacion_Cheque_IdBanco];
	if ($fecha_debito == "") {
		$fecha_debito = "NULL";
	}
	else {
		list($d,$m,$a) = explode("/",$fecha_debito);
		if (Fecha_ok($d,$m,$a)) {
			$fecha_debito = "'$a-$m-$d'";
		}
		else {
			Error("La fecha de D�bito ingresada es inv�lida");
		}
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe");
	}
	elseif (!es_numero($importe)) {
		Error("El Importe ingresado es inv�lido");
	}
	
	if (!$error) {
		$sql = "UPDATE bancos.cheques SET ";
		$sql .= "FechaD�bCh=$fecha_debito,";
		$sql .= "ImporteCh=$importe,";
		$sql .= "Comentarios='$comentarios'";
		$sql .= "WHERE N�meroCh=$numero";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=Mov_Cheques_Pendientes_Banco value='$idbanco'>\n";
	echo "<input type=hidden name=cmd value=Mov_Cheques_Pendientes>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Modificar_Deposito":
	$fecha_credito = $_POST[Modificacion_Deposito_Fecha_Credito];
	$idbanco = $_POST[Modificacion_Deposito_IdBanco];
	$iddep = $_POST[Modificacion_Deposito_IdDeposito];
	$importe = $_POST[Modificacion_Deposito_Importe];

	if ($fecha_credito == "") {
		$fecha_credito = "NULL";
	}
	else {
		list($d,$m,$a) = explode("/",$fecha_credito);
		if (Fecha_ok($d,$m,$a)) {
			$fecha_credito = "'$a-$m-$d'";
		}
		else {
			Error("La fecha de Cr�dito ingresada es inv�lida");
		}
	}
	if ($importe == "") {
		Error("Falta ingresar el Importe");
	}
	elseif (!es_numero($importe)) {
		Error("El Importe ingresado es inv�lido");
	}
	
	if (!$error) {
		$sql = "UPDATE bancos.dep�sitos SET ";
		$sql .= "FechaCr�dito=$fecha_credito,";
		$sql .= "ImporteDep=$importe ";
		$sql .= "WHERE IdDep�sito=$iddep";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=Mov_Depositos_Pendientes_Banco value='$idbanco'>\n";
	echo "<input type=hidden name=cmd value=Mov_Depositos_Pendientes>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
case "Modificar_Tarjeta":
	$fecha_deposito = $_POST[Modificacion_Tarjeta_Fecha_Deposito];
	$importe_deposito = $_POST[Modificacion_Tarjeta_Importe_Deposito];
	$fecha_credito = $_POST[Modificacion_Tarjeta_Fecha_Credito];
	$importe_credito = $_POST[Modificacion_Tarjeta_Importe_Credito];
	$idbanco = $_POST[Modificacion_Tarjeta_IdBanco];
	$idtar = $_POST[Modificacion_Tarjeta_IdTarjeta];

	if ($fecha_deposito == "") {
		Error("Falta ingresar la fecha de Dep�sito");
	}
	else {
		list($d,$m,$a) = explode("/",$fecha_deposito);
		if (Fecha_ok($d,$m,$a)) {
			$fecha_deposito = "'$a-$m-$d'";
		}
		else {
			Error("La fecha de Dep�sito ingresada es inv�lida");
		}
	}
	if ($fecha_credito == "") {
		$fecha_credito = "NULL";
	}
	else {
		list($d,$m,$a) = explode("/",$fecha_credito);
		if (Fecha_ok($d,$m,$a)) {
			$fecha_credito = "'$a-$m-$d'";
		}
		else {
			Error("La fecha de Cr�dito ingresada es inv�lida");
		}
	}
	if ($importe_deposito == "") {
		Error("Falta ingresar el Importe del Dep�sito");
	}
	elseif (!es_numero($importe_deposito)) {
		Error("El Importe del Dep�sito ingresado es inv�lido");
	}
	if ($importe_credito == "") {
		$importe_credito = 0;
	}
	elseif (!es_numero($importe_deposito)) {
		Error("El Importe del Dep�sito ingresado es inv�lido");
	}
	
	if (!$error) {
		$sql = "UPDATE bancos.tarjetas SET ";
		$sql .= "FechaDepTar=$fecha_deposito,";
		$sql .= "ImporteDepTar=$importe_deposito,";
		$sql .= "FechaCr�dTar=$fecha_credito,";
		$sql .= "ImporteCr�dTar=$importe_credito ";
		$sql .= "WHERE IdTarjeta=$idtar";
		$result = db_query($sql) or db_die($sql);
		Aviso("Los datos se ingresaron correctamente");
	}	
	echo "<form action=bancos.php method=post>\n";
	echo "<input type=hidden name=mode value=forms>\n";
	echo "<input type=hidden name=Mov_Tarjetas_Pendientes_Banco value='$idbanco'>\n";
	echo "<input type=hidden name=cmd value=Mov_Tarjetas_Pendientes>\n";
	echo "<br><center>\n";
	echo "<input type=submit name=Volver value='Volver'>\n";
	echo "</center></form>\n";
	break;
default:
	Error($_POST[cmd].": Comando desconocido");
	break;
}
?>