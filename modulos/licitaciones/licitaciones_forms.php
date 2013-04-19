<?
/*
$Author: nazabal $
$Revision: 1.3 $
$Date: 2003/09/03 20:11:58 $
*/

// check whether the lib has been included - authentication!
if (!defined("lib_included")) { die("Please use index.php!"); }

if ($cmd1 == "newlic") {
	if ($HTTP_POST_VARS[newlic_submit]) {
	    $lic_ok = 1;
	    echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	    if ($distrito == "") { echo "<font size=2 color=#ff0000>$lic_21</font><br><br>"; $lic_ok = 0; }
//	    if ($tipoentidad == "") { echo "<font size=2 color=#ff0000>$lic_22</font><br><br>"; $lic_ok = 0; }
	    if ($entidad == "") { echo "<font size=2 color=#ff0000>$lic_23</font><br><br>"; $lic_ok = 0; }
	    if ($lic_numero == "") { echo "<font size=2 color=#ff0000>$lic_25</font><br><br>"; $lic_ok = 0; }
	    if ($lic_valor == "") { $lic_valor_ok="0"; } else { $lic_valor_ok="'$lic_valor'"; }
	    if ($lic_mant == "") { $lic_mant_ok="NULL"; } else { $lic_mant_ok="'$lic_mant'"; }
	    if ($lic_forma == "") { $lic_forma_ok="NULL"; } else { $lic_forma_ok="'$lic_forma'"; }
	    if ($lic_ofertado == "") { $lic_ofertado_ok="0"; } else { $lic_ofertado_ok=$lic_ofertado; }
	    if ($lic_estimado == "") { $lic_estimado_ok="0"; } else { $lic_estimado_ok=$lic_estimado; }
	    if ($lic_ganado == "") { $lic_ganado_ok="0"; } else { $lic_ganado_ok=$lic_ganado; }
	    if ($lic_comentarios == "") { $lic_comentarios_ok="NULL"; } else { $lic_comentarios_ok="'$lic_comentarios'"; }
		list($d,$m,$a) = explode("/",$lic_fecha);
		if (Fecha_ok($d,$m,$a)) {
			$lic_fecha = Fecha_db($lic_fecha);
		}
		else {
			Error("El formato de la fecha es inválido");
			$lic_ok = 0;
		}
	    if ($lic_ok == 1) {
			$result = db_query("select max(IDLicitación) from licitaciones.licitaciones") or db_die();
			$row = db_fetch_row($result);
			$sql = "insert into licitaciones.licitaciones (IDLicitación, IDEntidad, Número, Fecha, ValorPliego, MantenimientoOferta, FormaPago, Moneda, ComentariosL, MontoOfertado, MontoEstimado, MontoGanado, Estado, UltimaMod_Usuario, UltimaMod_Fecha) values (".($row[0] + 1).", $entidad, '$lic_numero', '$lic_fecha', $lic_valor_ok, $lic_mant_ok, $lic_forma_ok, '$lic_moneda', $lic_comentarios_ok, $lic_ofertado_ok, $lic_estimado_ok, $lic_ganado_ok, 0, '$user_firstname $user_name', '".date("Y-m-d H:i:s",mktime())."')";
			$result = db_query($sql) or db_die($sql);
			echo "<font size=3>$lic_20<br>$lic_9: <font color=#0000ff>".($row[0] + 1)."</font></font><br><br>\n";
			$distrito="";
			$lic_valor="";
			$lic_numero="";
			$lic_mant="";
			$lic_forma="";
			$lic_comentarios="";
			$lic_ofertado="";
			$lic_estimado="";
			$lic_ganado="";
		}
		echo "</b></td></tr>\n";
	}
	elseif ($HTTP_POST_VARS[newlic_cancel]) {
		header("Location: licitaciones.php");
	}
	else {
	echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
	echo "<form action=licitaciones.php method=post>\n";
	echo "<input type=hidden name=PHPSESSID value=$PHPSESSID>\n";
	echo "<input type=hidden name=mode value='$mode'>\n";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<input type=hidden name=cmd1 value='$cmd1'>\n";
	echo "<br><table border=0 cellspacing=5 cellpadding=0 align=center bgcolor=$bgcolor2>\n";
	echo "<tr><td align=right>$lic_10: <select name=distrito onchange='document.forms[0].submit()'>\n";
	if (!$distrito) echo "<option value=''>\n";
	$result = db_query("select * from licitaciones.distrito order by Distrito") or db_die();
	while ($row = db_fetch_row($result)) {
		echo "<option value='$row[0]'";
		if ($row[0] == $distrito) echo " selected";
		echo ">$row[1]\n";
	}
	echo "</select></td>\n";
/*	echo "<td align=right>\n";
	echo "$lic_11: <select name=tipoentidad onchange='document.forms[0].submit()'>\n";
	if ($distrito) {
	   	$result = db_query("select licitaciones.tipo_entidad.IDTipoE,licitaciones.tipo_entidad.TipoE from licitaciones.tipo_entidad,licitaciones.entidades,licitaciones.distrito where licitaciones.distrito.IDDistrito='$distrito' and licitaciones.distrito.IDDistrito=licitaciones.entidades.IDDistrito and licitaciones.tipo_entidad.IDTipoE=licitaciones.entidades.IDTipoE group by licitaciones.tipo_entidad.TipoE order by licitaciones.tipo_entidad.TipoE") or db_die();
    	if (db_query_rows() <= 0) { echo "<option value=''>$lic_12\n"; }
    	else {
			echo "<option value=''>\n";
			while ($row = db_fetch_row($result)) {
    	    	echo "<option value='$row[0]'";
    	        if ($row[0] == $tipoentidad) echo " selected";
    	        echo ">$row[1]\n";
    	    }
		}
	}
	else { echo "<option value=''>$lic_12\n"; }
	echo "</select>\n";
*/
	echo "<td align=right>\n";
	echo "$lic_14: <select name=entidad>\n";
	if ($distrito) {
//		if ($tipoentidad) {
//    		$result = db_query("select IDEntidad,Descripción from licitaciones.entidades where IDDistrito='$distrito' and IDTipoE=$tipoentidad order by Descripción") or db_die();
    		$result = db_query("select IDEntidad,Descripción from licitaciones.entidades where IDDistrito='$distrito' order by Descripción") or db_die();
      		if (db_query_rows($result) <= 0) { echo "<option value=''>$lic_15\n"; }
      		else {
         		while ($row = db_fetch_row($result)) {
            		echo "<option value='$row[0]'";
            		if ($row[0] == $entidad) echo " selected";
            		echo ">$row[1]\n";
            	}
      		}
//   		}
//		else { echo "<option value=''>$lic_15\n"; }
	}
	else { echo "<option value=''>$lic_12\n"; }
	echo "</select><td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right>$lic_13: ";
	if ($lic_fecha) {
		list($d,$m,$a) = explode("/",$lic_fecha);
		if (!Fecha_ok($d,$m,$a)) {
			Error("El formato de la fecha es inválido");
			$lic_fecha = date("d/m/Y");
		}
	}
	else {
		$lic_fecha = date("d/m/Y");
	}
	echo "<input type=text name=lic_fecha value='$lic_fecha' size=10 maxlength=10>";
	echo link_calendario("lic_fecha");
	echo "</td>\n";
	if (($lic_moneda == "Pesos") or (!$lic_moneda)) {
		$lic_moneda_p = " checked";
	}
	else {
		$lic_moneda_d = " checked";
	}
	echo "<td align=right>Moneda: <input type=radio name=lic_moneda value='Pesos'$lic_moneda_p> Pesos <input type=radio name=lic_moneda value='Dólares'$lic_moneda_d> Dólares</td>\n";
	echo "</tr>\n";

	echo "<tr><td align=right>$lic_16:<input type='text' name='lic_valor' size=15 maxlength=15 value='$lic_valor'></td>\n";
	echo "<td align=right>$lic_17:<input type='text' name='lic_numero' size=15 maxlength=50 value='$lic_numero'></td></tr>\n";
	echo "<tr><td align=right>Mantenimiento de oferta:<input type='text' name='lic_mant' size=15 maxlength=15 value='$lic_mant'></td>\n";
	echo "<td align=right>Forma de pago:<input type='text' name='lic_forma' size=15 maxlength=50 value='$lic_forma'></td></tr>\n";
	echo "<tr><td align=right colspan=2>Ofertado:\n";
	echo "<input type=text name=lic_ofertado value='$lic_ofertado' size=10 maxlength=10>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	echo "Estimado:\n";
	echo "<input type=text name=lic_estimado value='$lic_estimado' size=10 maxlength=10>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	echo "Ganado:\n";
	echo "<input type=text name=lic_ganado value='$lic_ganado' size=10 maxlength=10></td>\n";
	echo "</tr>\n";
	echo "<tr><td colspan=2 align=center>$lic_18:<br><textarea name='lic_comentarios' cols=60 rows=10>$lic_comentarios</textarea></td></tr>\n";
	echo "<tr><td colspan=2 align=center><input type=submit name=newlic_submit value='$lic_19'>&nbsp;&nbsp;&nbsp;<input type=submit name=newlic_cancel value='Cancelar'></td><tr>\n";
	echo "\n";
	echo "</table></form>\n";
	}
} // newlic
elseif ($cmd1 == "configuracion") {
	echo "<form action=licitaciones.php?mode=forms&cmd=$cmd method=post>\n";
	echo "<input type=hidden name=PHPSESSID value=$PHPSESSID>\n";
	echo "<input type=hidden name=cmd1 value='$cmd1'>\n";
	echo "<br><table border=0 cellspacing=5 cellpadding=0 align=center bgcolor=$bgcolor2>\n";

	if ($HTTP_POST_VARS[dis_agregar]) {
		$form_ok = 1;
		echo "<tr><td colspan=4 align=center bgcolor=$bgcolor3><b>";
		if ($dis_descripcion == "") {
			echo "<br><font size=2 color=#ff0000>$lic_27</font><br><br>"; $form_ok = 0;
		}
		if ($form_ok) {
			$result = db_query("insert into licitaciones.distrito (Distrito) values ('$dis_descripcion')") or db_die();
			echo "<font size=3>$lic_20</font>\n";
			$dis_descripcion="";
			$dis_distrito="";
		}
	}
//	if ($HTTP_POST_VARS[dis_borrar]) {
//		$form_ok = 1;
//		echo "<tr><td colspan=4 align=center bgcolor=$bgcolor3><b>";
//		if (!$dis_distrito) {
//			echo "<br><font size=2 color=#ff0000>$lic_21</font><br><br>"; $form_ok = 0;
//		}
//		if ($form_ok) {
//			$result = db_query("delete from licitaciones.distrito where IDDistrito=$dis_distrito") or db_die();
//			$result = db_query("delete from licitaciones.entidades where IDDistrito=$dis_distrito") or db_die();
//			echo "<font size=3>$lic_6</font>\n";
//			$dis_descripcion="";
//			$dis_distrito="";
//		}
//	}
/*	if ($HTTP_POST_VARS[tip_agregar]) {
		$form_ok = 1;
		echo "<tr><td colspan=4 align=center bgcolor=$bgcolor3><b>";
		if ($tip_descripcion == "") {
			echo "<br><font size=2 color=#ff0000>$lic_28</font><br><br>"; $form_ok = 0;
		}
		if ($form_ok) {
			$result = db_query("insert into licitaciones.tipo_entidad (TipoE) values ('$tip_descripcion')") or db_die();
			echo "<font size=3>$lic_20</font>\n";
			$tip_descripcion="";
			$tip_tipoentidad="";
		}
	}
*/
//	if ($HTTP_POST_VARS[tip_borrar]) {
//		$form_ok = 1;
//		echo "<tr><td colspan=4 align=center bgcolor=$bgcolor3><b>";
//		if (!$tip_tipoentidad) {
//			echo "<br><font size=2 color=#ff0000>$lic_22</font><br><br>"; $form_ok = 0;
//		}
//		if ($form_ok) {
//			$result = db_query("delete from licitaciones.tipo_entidad where IDTipoE=$tip_tipoentidad") or db_die();
//			$result = db_query("delete from licitaciones.entidades where IDTipoE=$tip_tipoentidad") or db_die();
//			echo "<font size=3>$lic_6</font>\n";
//			$tip_descripcion="";
//			$tip_tipoentidad="";
//		}
//	}
	if ($HTTP_POST_VARS[ent_agregar]) {
		$form_ok = 1;
		echo "<tr><td colspan=4 align=center bgcolor=$bgcolor3><b>";
		if ($ent_distrito == "") {
			echo "<br><font size=2 color=#ff0000>$lic_21</font><br><br>"; $form_ok = 0;
		}
/*		if ($ent_tipoentidad == "") {
			echo "<br><font size=2 color=#ff0000>$lic_22</font><br><br>"; $form_ok = 0;
		}
*/
		if ($ent_descripcion == "") {
			echo "<br><font size=2 color=#ff0000>$lic_35</font><br><br>"; $form_ok = 0;
		}
		if ($form_ok) {
//			$result = db_query("select max(IDEntidad) from licitaciones.entidades") or db_die();
//			$row = db_fetch_row($result);
//			$result = db_query("insert into licitaciones.entidades (IDEntidad,IDDistrito,IDTipoE,Descripción) values (".($row[0] + 1).",$ent_distrito,$ent_tipoentidad,'$ent_descripcion')") or db_die();
////			$result = db_query("insert into licitaciones.entidades (IDEntidad,IDDistrito,Descripción) values (".($row[0] + 1).",$ent_distrito,'$ent_descripcion')") or db_die();
			$result = db_query("insert into licitaciones.entidades (IDDistrito,Descripción) values ($ent_distrito,'$ent_descripcion')") or db_die();
			echo "<font size=3>$lic_20</font>\n";
			$ent_distrito="";
//			$ent_tipoentidad="";
			$ent_entidad="";
			$ent_descripcion="";
		}
	}
//	if ($HTTP_POST_VARS[ent_borrar]) {
//		$form_ok = 1;
//		echo "<tr><td colspan=4 align=center bgcolor=$bgcolor3><b>";
//		if ($ent_distrito == "") {
//			echo "<br><font size=2 color=#ff0000>$lic_21</font><br><br>"; $form_ok = 0;
//		}
//		if ($ent_tipoentidad == "") {
//			echo "<br><font size=2 color=#ff0000>$lic_22</font><br><br>"; $form_ok = 0;
//		}
//		if (!$ent_entidad) {
//			echo "<br><font size=2 color=#ff0000>$lic_23</font><br><br>"; $form_ok = 0;
//		}
//		if ($form_ok) {
////			$result = db_query("delete from licitaciones.tipo_entidad where IDTipoE=$tip_tipoentidad") or db_die();
//			$result = db_query("delete from licitaciones.entidades where IDEntidad=$ent_entidad") or db_die();
//			echo "<font size=3>$lic_6</font>\n";
//			$ent_distrito="";
//			$ent_tipoentidad="";
//			$ent_entidad="";
//			$ent_descripcion="";
//		}
//	}

	//Distritos
	echo "<tr><td colspan=3><b><font size=2>Nuevo Distrito:</font></b></td></tr>\n";
	echo "<tr><td align=left><select name='dis_distrito'>\n";
	$result = db_query("select * from licitaciones.distrito order by Distrito") or db_die();
	while ($row = db_fetch_row($result)) echo "<option value='$row[0]'>$row[1]\n";
	echo "</select></td>\n";
//	echo "<td align=left><input type='submit' name='dis_borrar' value='$lic_31'></td>";
	echo "<td align=right>$lic_29:<input type='text' name='dis_descripcion' size=25 maxlength=50 value='$lic_descripcion'></td>\n";
	echo "<td align=left><input type=submit name=dis_agregar value='Agregar'></td></tr>\n";
	echo "<tr><td colspan=3><hr></td></tr>\n";
	//Tipos de entidades
/*	echo "<tr><td colspan=3><b><font size=2>Nuevo Tipo de Entidad:</font></b></td></tr>\n";
	echo "<tr><td align=left>\n";
	echo "<select name=tip_tipoentidad>\n";
   	$result = db_query("select licitaciones.tipo_entidad.IDTipoE,licitaciones.tipo_entidad.TipoE from licitaciones.tipo_entidad,licitaciones.entidades where licitaciones.tipo_entidad.IDTipoE=licitaciones.entidades.IDTipoE group by licitaciones.tipo_entidad.TipoE order by licitaciones.tipo_entidad.TipoE") or db_die();
	if (db_query_rows() <= 0) { echo "<option value=''>$lic_12\n"; }
	else {
		while ($row = db_fetch_row($result)) echo "<option value='$row[0]'>$row[1]\n";
	}
	echo "</select></td>\n";
//	echo "<td align=left><input type='submit' name='tip_borrar' value='$lic_31'></td>";
	echo "<td align=right>$lic_29:<input type='text' name='tip_descripcion' size=25 maxlength=50 value='$lic_descripcion'></td>\n";
	echo "<td align=left><input type=submit name=tip_agregar value='Agregar'></td><tr>\n";
	echo "<tr><td colspan=3><hr></td></tr>\n";
*/
	//Entidades
	echo "<tr><td colspan=3><b><font size=2>Nueva Entidad:</font></b></td></tr>\n";
	echo "<tr><td align=left>$lic_10: <select name=ent_distrito onchange='document.forms[0].submit()'>\n";
	if (!$ent_distrito) echo "<option value=''>\n";
	$result = db_query("select * from licitaciones.distrito order by Distrito") or db_die();
	while ($row = db_fetch_row($result)) {
		echo "<option value='$row[0]'";
		if ($row[0] == $ent_distrito) echo " selected";
		echo ">$row[1]\n";
	}
	echo "</select></td>\n";
/*	echo "<td colspan=2 align=right width=50%>\n";
	echo "$lic_11: <select name=ent_tipoentidad onchange='document.forms[0].submit()'>\n";
	if ($ent_distrito) {
//	   	$result = db_query("select licitaciones.tipo_entidad.IDTipoE,licitaciones.tipo_entidad.TipoE from licitaciones.tipo_entidad,licitaciones.entidades,licitaciones.distrito where licitaciones.distrito.IDDistrito='$ent_distrito' and licitaciones.distrito.IDDistrito=licitaciones.entidades.IDDistrito and licitaciones.tipo_entidad.IDTipoE=licitaciones.entidades.IDTipoE group by licitaciones.tipo_entidad.TipoE order by licitaciones.tipo_entidad.TipoE") or db_die();
	   	$result = db_query("select licitaciones.tipo_entidad.IDTipoE,licitaciones.tipo_entidad.TipoE from licitaciones.tipo_entidad,licitaciones.entidades where licitaciones.tipo_entidad.IDTipoE=licitaciones.entidades.IDTipoE group by licitaciones.tipo_entidad.TipoE order by licitaciones.tipo_entidad.TipoE") or db_die();
			echo "<option value=''>\n";
			while ($row = db_fetch_row($result)) {
    	    	echo "<option value='$row[0]'";
    	        if ($row[0] == $ent_tipoentidad) echo " selected";
    	        echo ">$row[1]\n";
    	    }
	}
	else { echo "<option value=''>$lic_12\n"; }
	echo "</select></td>\n";
*/
	echo "</tr><tr>\n";
	echo "<td align=left>\n";
	echo "$lic_14: <select name=ent_entidad>\n";
	if ($ent_distrito) {
//		if ($ent_tipoentidad) {
//    		$result = db_query("select IDEntidad,Descripción from licitaciones.entidades where IDDistrito='$ent_distrito' and IDTipoE=$ent_tipoentidad order by Descripción") or db_die();
    		$result = db_query("select IDEntidad,Descripción from licitaciones.entidades where IDDistrito='$ent_distrito' order by Descripción") or db_die();
      		if (db_query_rows($result) <= 0) { echo "<option value=''>$lic_15\n"; }
      		else {
         		while ($row = db_fetch_row($result)) {
            		echo "<option value='$row[0]'";
            		if ($row[0] == $ent_entidad) echo " selected";
            		echo ">$row[1]\n";
            	}
      		}
//		}
//		else { echo "<option value=''>$lic_15\n"; }
	}
	else { echo "<option value=''>$lic_12\n"; }
	echo "</select></td>\n";
//	echo "<td align=left><input type='submit' name='ent_borrar' value='$lic_31'></td>";
	echo "<td align=right>$lic_29:<input type='text' name='ent_descripcion' size=25 maxlength=50 value='$ent_descripcion'></td>\n";
	echo "<td align=left><input type=submit name=ent_agregar value='Agregar'></td><tr>\n";
	echo "<tr><td colspan=3><hr></td></tr>\n";
/*	echo "<tr><td align=center colspan=3>\n";
	echo "<input type=submit name=conf_avanzada value='Configuración Avanzada'><br><br>\n";
	echo "</td></tr>\n";
*/
	echo "</table></form><br><br>\n";
	if ($_POST[conf_avanzada]) {
//		$sql = "SELECT L.IDLicitación,D.*,T.*,E.IDEntidad,E.Descripción ";
		$sql = "SELECT L.IDLicitación,D.*,E.IDEntidad,E.Descripción ";
		$sql .= "FROM licitaciones.licitaciones AS L,";
        $sql .= "licitaciones.distrito AS D,";
//        $sql .= "licitaciones.tipo_entidad AS T,";
        $sql .= "licitaciones.entidades AS E ";
		$sql .= "WHERE ";
        $sql .= "E.IDDistrito = D.IDDistrito AND ";
        $sql .= "L.IDEntidad = E.IDEntidad AND ";
//        $sql .= "E.IDTipoE = T.IDTipoE ";
		$sql .= "GROUP BY L.IDLicitación ";
		$sql .= "ORDER BY D.Distrito,";
//		$sql .= "T.TipoE,";
		$sql .= "E.Descripción";
		$result = db_query($sql) or db_die($sql);
		while ($row = db_fetch_row($result)) {
			$codigo[$row[1]][$row[3]][$row[5]][] = $row[0];
			$nombre[$row[2]][$row[4]][$row[6]][] = $row[0];
		}
		echo "<font face='Courier New, Courier, mono'>\n";
		foreach ($nombre as $dist => $arr1) {
			echo "|<br>\n";
			echo "+--$dist<br>|&nbsp;&nbsp;|<br>\n";
			foreach ($arr1 as $tipoe => $arr2) {
				echo "|&nbsp;&nbsp;+--$tipoe<br>\n";
				foreach ($arr2 as $ent => $arr3) {
					echo "|&nbsp;&nbsp;|&nbsp;&nbsp;|<br>\n";
					echo "|&nbsp;&nbsp;|&nbsp;&nbsp;+--$ent: (";
					echo implode(", ",$arr3);
					echo ")";
//					echo "|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Licitaciones: \n";
//					foreach ($arr3 as $lic) {
//						echo "$lic, ";
//					}
				echo "<br>\n";
				}
			}
//			echo "|&nbsp;&nbsp;|<br>\n";
		}
		echo "Total: ".db_query_rows($result);
		echo "</font>\n";
	}
} // configuracion
elseif ($cmd1 == "mod_lic") {
	$ID = $HTTP_POST_VARS[lic_id];
	if ($HTTP_POST_VARS[mod_edit]) {
	    $lic_ok = 1;
		echo "<br><table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	    echo "<tr><td colspan=3 align=center bgcolor=$bgcolor2><b>";
	    if ($mod_distrito == "") { echo "<font size=2 color=#ff0000>$lic_21</font><br><br>"; $lic_ok = 0; }
//	    if ($mod_tipoentidad == "") { echo "<font size=2 color=#ff0000>$lic_22</font><br><br>"; $lic_ok = 0; }
	    if ($mod_entidad == "") { echo "<font size=2 color=#ff0000>$lic_23</font><br><br>"; $lic_ok = 0; }
	    if ($mod_numero == "") { echo "<font size=2 color=#ff0000>$lic_25</font><br><br>"; $lic_ok = 0; }
	    if ($mod_valor == "") { $mod_valor_ok="NULL"; } else { $mod_valor_ok="'$mod_valor'"; }
	    if ($mod_mant == "") { $mod_mant_ok="NULL"; } else { $mod_mant_ok="'$mod_mant'"; }
	    if ($mod_forma == "") { $mod_forma_ok="NULL"; } else { $mod_forma_ok="'$mod_forma'"; }
	    if ($mod_ofertado == "") { $mod_ofertado_ok="NULL"; } else { $mod_ofertado_ok=$mod_ofertado; }
	    if ($mod_estimado == "") { $mod_estimado_ok="NULL"; } else { $mod_estimado_ok=$mod_estimado; }
	    if ($mod_ganado == "") { $mod_ganado_ok="NULL"; } else { $mod_ganado_ok=$mod_ganado; }
	    if ($mod_comentarios == "") { $mod_comentarios_ok="NULL"; } else { $mod_comentarios_ok="'$mod_comentarios'"; }
		if ($mod_fecha_ent != "") {
			list($d,$m,$a) = explode("/",$mod_fecha_ent);
			if (Fecha_ok($d,$m,$a)) {
				$mod_fecha_ent = "'".Fecha_db($mod_fecha_ent)."'";
			}
			else {
				Error("El formato de la fecha de entrega no es válido");
				$lic_ok = 0;
			}
		}
		else {
			$mod_fecha_ent="NULL";
		}
		list($d,$m,$a) = explode("/",$mod_fecha);
		if (!Fecha_ok($d,$m,$a)) {
			Error("El formato de la fecha de apertura no es válido");
			$lic_ok = 0;
		}
		else {
			$mod_fecha = Fecha_db($mod_fecha);
		}
	    if ($lic_ok == 1) {
			$sql = "update licitaciones.licitaciones set ";
			$sql .= "FechaEntregado=$mod_fecha_ent,";
			$sql .= "Estado=$mod_estado,";
			$sql .= "Fecha='$mod_fecha',";
			$sql .= "MantenimientoOferta=$mod_mant_ok,";
			$sql .= "Número='$mod_numero',";
			$sql .= "IDEntidad=$mod_entidad,";
			$sql .= "FormaPago=$mod_forma_ok,";
			$sql .= "ValorPliego=$mod_valor_ok,";
			$sql .= "ComentariosL=$mod_comentarios_ok,";
			$sql .= "UltimaMod_Usuario='$user_firstname $user_name',";
			$sql .= "UltimaMod_Fecha='".date("Y-m-d H:i:s",mktime())."',";
			$sql .= "Moneda='$mod_moneda',";
			$sql .= "MontoOfertado=$mod_ofertado_ok,";
			$sql .= "MontoEstimado=$mod_estimado_ok,";
			$sql .= "MontoGanado=$mod_ganado_ok ";
			$sql .= "where IDLicitación=$ID";
			$result = db_query($sql) or db_die($sql);
			if (is_array($file_id) and count($file_id) > 0) {
				while (list($mod_file_id, $mod_file_imp) = each($file_id)) {
					$sql = "update licitaciones.archivos set ";
					$sql .= "imprimir='$mod_file_imp' ";
					$sql .= "where idarchivo=$mod_file_id";
					$result = db_query($sql) or db_die($sql);
				}
			}
			echo "<font size=3>$lic_20</font>\n";
		}
		echo "</b></td></tr></table>\n";
		$cmd1 = "detalle";
		include_once("./licitaciones_view.php");
	}
	elseif ($HTTP_POST_VARS[mod_cancel]) {
		$cmd1 = "detalle";
		include_once("./licitaciones_view.php");
	}
	elseif ($HTTP_POST_VARS[det_del]) {
		$result = db_query("delete from licitaciones.licitaciones where IDLicitación=$ID") or db_die();
		$result = db_query("delete from licitaciones.archivos where IDLicitación=$ID") or db_die();
//		while ($row = db_fetch_row($result)) {
//			$err = `rm -f
		echo "<br><table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	    echo "<tr><td colspan=3 align=center bgcolor=$bgcolor2><b>";
	    echo "<font size=3>Se eliminó la Licitación número $ID</font>";
	    echo "</b></td></tr></table>\n";
	    $cmd1 = "";
	    include_once("./licitaciones_view.php");
	}
	elseif ($HTTP_POST_VARS[det_delfile]) {
		if ($file_id) {
			$result = db_query("delete from licitaciones.archivos where IDArchivo=$file_id") or db_die();
		}
		$cmd1 = "detalle";
	    include_once("./licitaciones_view.php");
	}
	elseif ($HTTP_POST_VARS[det_addfile]) {
		include_once("./licitaciones_files.php");
	}
	else {
//		$sql = "select licitaciones.licitaciones.*,licitaciones.entidades.* from licitaciones.licitaciones,licitaciones.entidades,licitaciones.tipo_entidad,licitaciones.distrito where licitaciones.licitaciones.IDLicitación = $ID and licitaciones.licitaciones.IDEntidad=licitaciones.entidades.IDEntidad and licitaciones.entidades.IDDistrito=licitaciones.distrito.IDDistrito and licitaciones.entidades.IDTipoE=licitaciones.tipo_entidad.IDTipoE group by licitaciones.licitaciones.IDLicitación";
		$sql = "SELECT licitaciones.licitaciones.*,licitaciones.entidades.* ";
		$sql .= "FROM licitaciones.licitaciones ";
		$sql .= "INNER JOIN licitaciones.entidades ";
		$sql .= "ON licitaciones.licitaciones.IDEntidad=licitaciones.entidades.IDEntidad ";
		$sql .= "WHERE licitaciones.licitaciones.IDLicitación=$ID";
		$result = db_query($sql) or db_die();
		if (db_query_rows($result) == 1) {
			$row = db_fetch_row($result);

			if (!$mod_distrito) {
				$mod_distrito=$row[21];
//				$mod_tipoentidad=$row[21];
				$mod_entidad=$row[19];
				$mod_numero=$row[2];
				$mod_fecha=Fecha($row[3]);
				$mod_mant=$row[16];
				$mod_forma=$row[17];
				$mod_valor=$row[4];
				$mod_comentarios=$row[11];
				$mod_moneda=$row[18];
				$mod_ofertado=$row[5];
				$mod_estimado=$row[6];
				$mod_ganado=$row[7];
				$mod_estado=$row[8];
				$mod_fecha_ent=Fecha($row[9]);
			}
			list($d,$m,$a) = explode("/",$mod_fecha);
			if (!Fecha_ok($d,$m,$a)) {
				Error("El formato de la fecha de apertura es inválido");
//				$mod_fecha = date("d/m/Y");
			}
			list($d,$m,$a) = explode("/",$mod_fecha_ent);
			if ($mod_fecha_ent != "") {
				if (!Fecha_ok($d,$m,$a)) {
					Error("El formato de la fecha de entrega es inválido");
//					$mod_fecha_ent = "";
				}
			}

			echo "<script language='javascript' src='../lib/popcalendar.js'></script>\n";
			echo "<br><table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
			echo "<form action='licitaciones.php?mode=forms&cmd=$cmd' method=post>\n";
			echo "<input type=hidden name=PHPSESSID value=$PHPSESSID>\n";
			echo "<tr><td colspan=3 align=center id=ma><font size=3><b>Modificación de datos de la Licitación</b></td></tr>";
			echo "<tr>\n";
			echo "<td align=left><b>ID:</b> $row[0]</td>\n";
			echo "<td align=center><b>Apertura:</b>\n";
			echo "<input type=text name=mod_fecha value='$mod_fecha' size=10 maxlength=10>";
			echo link_calendario("mod_fecha");
			echo "</td>\n";
			echo "<td align=right><b>Mantenimiento de oferta:</b>\n";
			echo "<input type=text name=mod_mant value='$mod_mant' size=20 maxlength=100></td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left><b>Número:</b>\n";
			echo "<input type=text name=mod_numero value='$mod_numero'></td>\n";
			echo "<td align=right colspan=2><b>Entidad:</b>\n";
			echo "<select name=mod_entidad>\n";
			if ($mod_distrito) {
//				if ($mod_tipoentidad) {
//		    		$result1 = db_query("select IDEntidad,Descripción from licitaciones.entidades where IDDistrito='$mod_distrito' and IDTipoE=$mod_tipoentidad order by Descripción") or db_die();
		    		$result1 = db_query("select IDEntidad,Descripción from licitaciones.entidades where IDDistrito='$mod_distrito' order by Descripción") or db_die();
//      				if (db_query_rows($result1) <= 0) { echo "<option value=''>$lic_15\n"; }
//		      		else {
        		 		while ($row1 = db_fetch_row($result1)) {
        		    		echo "<option value='$row1[0]'";
        		    		if ($row1[0] == $mod_entidad) echo " selected";
        		    		echo ">$row1[1]\n";
        		    	}
//      				}
//				}
//				else { echo "<option value=''>$lic_15\n"; }
			}
			else { echo "<option value=''>$lic_12\n"; }
			echo "</select></td>\n";
			echo "</tr><tr>\n";
/*			echo "<td align=left colspan=2><b>Tipo de Entidad:</b>\n";
			echo "<select name=mod_tipoentidad onchange='document.forms[0].submit()'>\n";
			if ($mod_distrito) {
			   	$result1 = db_query("select licitaciones.tipo_entidad.IDTipoE,licitaciones.tipo_entidad.TipoE from licitaciones.tipo_entidad,licitaciones.entidades,licitaciones.distrito where licitaciones.distrito.IDDistrito='$mod_distrito' and licitaciones.distrito.IDDistrito=licitaciones.entidades.IDDistrito and licitaciones.tipo_entidad.IDTipoE=licitaciones.entidades.IDTipoE group by licitaciones.tipo_entidad.TipoE order by licitaciones.tipo_entidad.TipoE") or db_die();
		    	if (db_query_rows() <= 0) { echo "<option value=''>$lic_12\n"; }
    			else {
					echo "<option value=''>\n";
					while ($row1 = db_fetch_row($result1)) {
    			    	echo "<option value='$row1[0]'";
    			        if ($row1[0] == $mod_tipoentidad) echo " selected";
    			        echo ">$row1[1]\n";
    			    }
				}
			}
			else { echo "<option value=''>$lic_12\n"; }
			echo "</select></td>\n";
*/
			echo "<td align=right><b>Distrito:</b>\n";
			echo "<select name=mod_distrito onchange='document.forms[1].submit()'>\n";
			if (!$mod_distrito) echo "<option value=''>\n";
			$result1 = db_query("select * from licitaciones.distrito order by Distrito") or db_die();
			while ($row1 = db_fetch_row($result1)) {
				echo "<option value='$row1[0]'";
				if ($row1[0] == $mod_distrito) echo " selected";
				echo ">$row1[1]\n";
			}
			echo "</select></td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left colspan=2><b>Forma de pago:</b>\n";
			echo "<input type=text name=mod_forma value='$mod_forma' size=30 maclength=100></td>\n";
			echo "<td align=right><b>Valor del pliego:</b> \n";
			echo "<input type=text name=mod_valor value='$mod_valor' size=10 maxlength=10></td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left colspan=2><b>Ofertado:</b>\n";
			echo "<input type=text name=mod_ofertado value='$mod_ofertado' size=10 maxlength=10>&nbsp;&nbsp;&nbsp;\n";
			echo "<b>Estimado:</b>\n";
			echo "<input type=text name=mod_estimado value='$mod_estimado' size=10 maxlength=10>&nbsp;&nbsp;&nbsp;\n";
			echo "<b>Ganado:</b>\n";
			echo "<input type=text name=mod_ganado value='$mod_ganado' size=10 maxlength=10></td>\n";
			echo "<td align=right><b>Moneda: </b>\n";
			if (($mod_moneda == "Pesos") or (!$mod_moneda)) {
				$mod_moneda_p = " checked";
			}
			else {
				$mod_moneda_d = " checked";
			}
			echo "<input type=radio name=mod_moneda value='Pesos'$mod_moneda_p> Pesos\n";
			echo "<input type=radio name=mod_moneda value='Dólares'$mod_moneda_d> Dólares\n";
			echo "</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left><b>Estado:</b>\n";
			echo "<select name=mod_estado>\n";
			foreach ($estados as $est => $arr) {
				//echo "<option value='$est' style='background-color: {$estados[$est][color]};'>{$estados[$est][texto]}\n";
				echo "<option value='$est' style='background-color: {$estados[$est][color]}; color:".contraste($estados[$est][color],"#000000","#ffffff").";'";
				if ($mod_estado == $est) echo " selected";
				echo ">{$estados[$est][texto]}\n";
			}
			echo "</select></td>\n";
			echo "<td align=left><b>Fecha de entrega:</b>\n";
			echo "<input type=text name=mod_fecha_ent value='$mod_fecha_ent' size=10 maxlength=10>";
			echo link_calendario("mod_fecha_ent");
			echo "</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=right valign=top><b>Comentarios/Seguimiento:</b></td>";
			echo "<td align=left colspan=2><textarea name='mod_comentarios' cols=60 rows=10>$mod_comentarios</textarea></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td align=left colspan=3>\n";
			echo "<b>Archivos:</b><br>\n";
			echo "<table cellpadding=3 cellspacing=3 width=100%>\n";
			echo "<tr><td colspan=5 align=left></td></tr>\n";
			$result1 = db_query("select * from licitaciones.archivos where IDLicitación=$ID");
			if (db_query_rows($result1) > 0) {
				echo "<tr bgcolor=$bgcolor3>\n";
				echo "<td align=center><b>Imprimir</b></td>\n";
				echo "<td align=left><b>Nombre</b></td>\n";
				echo "<td align=center><b>Comprimido</b></td>\n";
				echo "<td align=center><b>Fecha de cargado</b></td>\n";
				echo "<td align=left><b>Cargado por</b></td>\n";
				echo "</tr>\n";
				while ($row1 = db_fetch_row($result1)) {
					$mc = substr($row1[7],5,2);
					$dc = substr($row1[7],8,2);
					$yc = substr($row1[7],0,4);
					$hc = substr($row1[7],11,5);
					$imprimir = $row1["imprimir"];
					if ($imprimir == "t") $color_imprimir = "#00cc00";
					else $color_imprimir = "#cc2222";
					echo "<tr bgcolor=$bgcolor3>\n";
					echo "<td align=center bgcolor='$color_imprimir'>\n";
					echo "<select name=file_id['$row1[0]']>";
					echo "<option value='t'";
					if ($imprimir == "t") echo " selected";
					echo ">Sí\n";
					echo "<option value='f'";
					if ($imprimir == "f") echo " selected";
					echo ">No\n";
					echo "</select>";
					echo "</td>\n";
					echo "<td align=left>\n";
					echo "<a href='licitaciones_down.php?ID=$ID&FileID=$row1[0]&cmd=$cmd'>$row1[2]</a> ($row1[5] bytes)\n";
					echo "</td>\n";
					echo "<td align=right>\n";
					echo "$row1[9] bytes\n";
					echo "<a href='licitaciones_down.php?ID=$ID&FileID=$row1[0]&Comp=1'>\n";
					echo "<img align=middle src=$img_path/zip.gif border=0>\n";
					echo "</a></td>\n";
					echo "<td align=center>$dc/$mc/$yc $hc</td>\n";
					echo "<td align=left>$row1[8]</td>\n";
					echo "</tr>\n";
				}
			}
			else {
				echo "<tr><td colspan=5 align=center><b>No hay archivos disponibles para esta licitación</b></td></tr>\n";
			}
			echo "</table>\n";
			echo "</td></tr>\n";
			echo "<tr>\n";
			echo "<td colspan=3 align=center><br>\n";
			echo "<input type=hidden name=lic_id value='$ID'>\n";
			echo "<input type=hidden name=cmd1 value='mod_lic'>\n";
			echo "<input type=submit name=mod_edit value='Aceptar'>&nbsp;&nbsp;&nbsp;\n";
			echo "<input type=submit name=mod_cancel value='Cancelar'><br><br>\n";
			echo "</td>";
			echo "</tr>\n";
		}
		echo "</table><br>\n";
	}
} // mod_lic
elseif ($cmd1 == "presentadas") {
	include_once("./licitaciones_view.php");
} // presentadas
elseif ($cmd1 == "proximas") {
	include_once("./licitaciones_view.php");
} // proximas
else {
	$cmd1 = "proximas";
	include_once("./licitaciones_view.php");
}
?>