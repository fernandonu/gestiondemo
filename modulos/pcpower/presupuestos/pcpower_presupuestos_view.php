<?
/*
AUTOR: MAC

MODIFICADO POR:
$Author: marco_canderle $
$Revision: 1.16 $
$Date: 2005/03/30 13:58:27 $

*/
//print_r($_POST);
require_once("../../../config.php");
require_once("../../general/funciones_contactos.php");

//incluyo el modulo de tips
//global que sirve para saber si se ha seleccionado proximas o no (presentadas o historial).

variables_form_busqueda("pcpower_presupuestos",array("estado"=>""));


//variable para volver a otra pagina como ordcompra, remitos o facturas
$backto=$parametros['backto'] or $backto=$_POST['backto']	;
if ($backto && $_ses_global_backto!=$backto) {
	phpss_svars_set("_ses_global_backto", $backto);
	phpss_svars_set("_ses_global_nro_orden_asociada", $parametros['nro_orden']);
	phpss_svars_set("_ses_global_pag", $parametros['pag']);
	phpss_svars_set("_ses_global_extra", $parametros['_ses_global_extra']);
}

//variable de sesion que sirve para indicar si se visito la pagina 
//de presupuesto o la de licitaciones
phpss_svars_set("_ses_global_lic_o_pres", "pres");
 
/*if ($cmd == "") {
	$cmd="proximas";
	$_ses_licitaciones["cmd"] = $cmd;
	phpss_svars_set("_ses_licitaciones", $_ses_licitaciones);
}
*/
//devuelve el dia de la semana que le correponde d/m/a
// dom=0,lun=1...
function calcula_numero_dia_semana($dia,$mes,$ano){
	$nrodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
	return $nrodiasemana;
}
/*
$datos_barra = array(
					array(
						"descripcion"	=> "Próximas",
						"cmd"			=> "proximas",
						"sql_contar"	=> "SELECT count(*) as cant
											FROM (licitacion INNER JOIN entidad
											USING (id_entidad))
											INNER JOIN distrito
											USING (id_distrito)
											WHERE (licitacion.id_estado=0
											OR licitacion.id_estado=2
											OR licitacion.id_estado=3
											OR licitacion.id_estado=4
											OR licitacion.id_estado=7)
											AND licitacion.fecha_apertura > '".date("Y-m-d 23:59:59",mktime())."'
											AND borrada='".(($papelera)?"t":"f")."'"
						),
					array(
						"descripcion"	=> "Presentadas",
						"cmd"			=> "presentadas"
						/*"sql_contar"	=> "SELECT count(*) as cant
											FROM (licitacion INNER JOIN entidad
											USING (id_entidad))
											INNER JOIN distrito
											USING (id_distrito)
											WHERE (licitacion.id_estado=0
											OR licitacion.id_estado=2
											OR licitacion.id_estado=3
											OR licitacion.id_estado=4
											OR licitacion.id_estado=7)
											AND licitacion.fecha_apertura <= '".date("Y-m-d 23:59:59",mktime())."'
											AND borrada='".(($papelera)?"t":"f")."'"*/
						/*),
					array(
						"descripcion"	=> "Presupuesto",
						"cmd"			=> "presupuesto"
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );
*/
if ($_POST["det_del"]) {
	echo $html_header;
	$ID = $_POST["ID"];
	$msg = "";
	$sql = "update pcpower_licitacion set borrada='t' where id_licitacion=$ID";
	$result = sql($sql) or $msg = 1;
	if ($msg) {
		$msg = "No se pudo enviar a la papelera el Presupuesto número $ID.";
	}
	else {
		$msg = "El Presupuesto número $ID ha sido enviado a la papelera";
	}
	echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
	echo "<font size=3>$msg</font>";
	echo "</b><br><br></td></tr></table><br>\n";
	listado();
}
if ($_POST["det_restore"]) {
	echo $html_header;
	$ID = $_POST["ID"];
	$msg = "";
	$sql = "update pcpower_licitacion set borrada='f' where id_licitacion=$ID";
	$result = sql($sql) or $msg = 1;
	if ($msg) {
		$msg = "No se pudo restaurar de la papelera el presupuesto número $ID.";
	}
	else {
		$msg = "El Presupuesto número $ID ha sido restaurada de la papelera";
	}
	echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
	echo "<font size=3>$msg</font>";
	echo "</b><br><br></td></tr></table><br>\n";
	listado();
}
elseif($cmd1=="candadoponer") {
	echo $html_header;
	$ID = $parametros["ID"];

		 //ponemos el candado en la tabla candado de la BD
		 $fecha_candado = date("Y-m-d H:i:s",mktime());
		 $query_candado="update pcpower_candado set estado=1, fecha='$fecha_candado',usuario='$_ses_user_login' where id_licitacion=$ID";

		 echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		 if(sql($query_candado))
		  $msg = "Se puso el candado para este presupuesto";
		 else
		  $msg = "No se pudo poner el candado para este presupuesto";
		 echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
		 echo "<font size=3>$msg</font>";
		 echo "</b></td></tr>";
		 echo "<tr><td>&nbsp;</td></tr>";
		 echo "</table><br>\n";

	detalle($ID);
}
elseif($cmd1=="candadosacar") {
	echo $html_header;
	$ID = $parametros["ID"];

	     //sacamos el candado en la tabla candado de la BD
	     $fecha_candado = date("Y-m-d H:i:s",mktime());
	     $query_candado="update pcpower_candado set estado=0, fecha='$fecha_candado',usuario='$_ses_user_login' where id_licitacion=$ID";

		 echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		 if(sql($query_candado))
		  $msg = "Se sacó el candado para este prespuesto";
		 else
		  $msg = "No se pudo sacar el candado para este presupuesto";
		 echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
 		 echo "<font size=3>$msg</font>";
	 	 echo "</b></td></tr>";
	  	 echo "<tr><td>&nbsp;</td></tr>";
		 echo "</table><br>\n";

	detalle($ID);
}
elseif($cmd1=="resultado_coradir"){
 echo $html_header;
 echo "<br>No se ha implementado aún. Consulte a la División Software.";
 /*$ID = $parametros["ID"];
 include("../licitaciones/resultados_coradir.php");
 echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
 echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
 echo "<font size=3>$msg</font>";
 echo "</b></td></tr>";
 echo "<tr><td>&nbsp;</td></tr>";
 echo "</table><br>\n";*/
 detalle($ID);
}
elseif ($_POST["det_delfile"]) {
	$ID = $_POST["ID"];
	include_once('../pcpower_lib.php');
	pcpower_genera_det_delfile($ID);
	detalle($ID);
}
elseif ($_POST["mod_edit"]) {
	$ID = $_POST["ID"];
	$mod_entidad = $_POST["id_entidad"];
	$error = 0;
	echo $html_header;
	echo "<br><table width=50% border=0 cellspacing=1 cellpadding=5 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td colspan=3 align=center bgcolor=$bgcolor2><b>";
	$mod_entidad = $_POST["id_entidad"];
	$mod_numero = $_POST["mod_numero"];
	$mod_exp=$_POST["mod_exp"];
	$mod_valor = $_POST["mod_valor"];
	$mod_forma = $_POST["mod_forma"];
	$mod_mant = $_POST["mod_mant"];
	$mod_comentarios = $_POST["mod_comentarios"];
	$mod_fecha_ent = fecha_db($_POST["mod_fecha_ent"]);
	$mod_plazo_ent = $_POST["mod_plazo_ent"];
	$mod_estado = $_POST["mod_estado"];
	$mod_moneda = $_POST["mod_moneda"];
	$nbre = $_POST["nbre"];
	$dir = $_POST["dir"];
	$file_id = $_POST["file_id"];
//	$mod_ = $_POST["mod_"];
	
		if ($mod_entidad == "") { echo "<font size=2 color=#ff0000>Falta seleccionar el cliente</font><br><br>"; $error = 1; }
		if ($mod_valor == "") { $mod_valor_ok="NULL"; } else { $mod_valor_ok="'$mod_valor'"; }
		if ($mod_mant == "") { $mod_mant_ok="NULL"; } else { $mod_mant_ok="'$mod_mant'"; }
		if ($mod_forma == "") { $mod_forma_ok="NULL"; } else { $mod_forma_ok="'$mod_forma'"; }
		if ($mod_ofertado == "") { $mod_ofertado_ok="NULL"; } else { $mod_ofertado_ok=$mod_ofertado; }
		if ($mod_estimado == "") { $mod_estimado_ok="NULL"; } else { $mod_estimado_ok=$mod_estimado; }
		if ($mod_ganado == "") { $mod_ganado_ok="NULL"; } else { $mod_ganado_ok=$mod_ganado; }
		if ($mod_comentarios == "") { $mod_comentarios_ok="NULL"; } else { $mod_comentarios_ok="'$mod_comentarios'"; }
		if (!$error) {
			
			//actualizamos preventivamente el cliente por si han 
			//modificado algun dato del mismo
			$query="update pcpower_entidad set
			        nombre='$nbre',direccion='$dir' where id_entidad=$mod_entidad";
			sql($query) or fin_pagina();
						
			$sql_array = array();
			$sql = "update pcpower_licitacion set ";
			$sql .= "plazo_entrega='$mod_plazo_ent',";
			if($mod_fecha_ent)
			 $sql .= "fecha_entrega='$mod_fecha_ent',";
			//$sql .= "id_estado=$mod_estado,";
			$sql .= "mant_oferta_especial=$mod_mant_ok,";
			$sql .= "id_entidad=$mod_entidad,";
			$sql .= "id_moneda=$mod_moneda,";
			$sql .= "forma_de_pago=$mod_forma_ok,";
			$sql .= "observaciones=$mod_comentarios_ok,";
			$sql .= "ultimo_usuario='$_ses_user_name',";
			$sql .= "ultimo_usuario_fecha='".date("Y-m-d H:i:s",mktime())."'";
			$sql .= "where id_licitacion=$ID";
			$sql_array[] = $sql;
			
			if (is_array($file_id) and count($file_id) > 0) {
				while (list($mod_file_id, $mod_file_imp) = each($file_id)) {
					//actualizamos la tabla de entregar_lic para indicar si el archivo
					//esta listo para imprimir o no
					
					if($mod_file_imp=='t')
					 $of_imp=1;
					else 
					 $of_imp=0;
					$query="select nombre from pcpower_archivos where idarchivo=$mod_file_id";
					$nombre_arch=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el nombre del archivo");
					$query="update pcpower_entregar_lic set oferta_lista_imprimir=$of_imp where id_licitacion=$ID and archivo_oferta='".$nombre_arch->fields['nombre']."'";	
					$sql_array[] = $query;					

					
					$sql = "update pcpower_archivos set ";
					$sql .= "imprimir='$mod_file_imp' ";
					$sql .= "where idarchivo=$mod_file_id";
					$sql_array[] = $sql;
				}
			}
			if (sql($sql_array,1000)) {
				$msg = "<font size=3>Los datos se cargaron correctamente</font>\n";
			}
			else {
				$msg = "No se pudo modificar el Presupuesto número $ID.<br>$msg";
			}
			echo $msg;
		}
		echo "</b></td></tr></table>\n";
		detalle($ID);
}
elseif ($_POST["det_edit"]) {
	$ID = $_POST["ID"];
	if ($ID == "") {
		listado();
		exit;
	}
	echo $html_header;
	echo "<SCRIPT language='JavaScript' src='../licitaciones/funcion.js'></SCRIPT>";
	$sql = "SELECT pcpower_licitacion.*,pcpower_entidad.id_entidad,";
	$sql .= "pcpower_entidad.id_distrito,pcpower_entidad.nombre as nombre_entidad,pcpower_entidad.direccion as dir_entidad ";
	$sql .= "FROM pcpower_licitacion ";
	$sql .= "LEFT JOIN pcpower_entidad ";
	$sql .= "USING (id_entidad) ";
	$sql .= "LEFT JOIN pcpower_tipo_entidad ";
	$sql .= "USING (id_tipo_entidad) ";
	$sql .= "WHERE pcpower_licitacion.id_licitacion=$ID";
	$result = sql($sql) or die;
	if (!$_POST["id_entidad"]) {
		$mod_entidad=$result->fields["id_entidad"];
		$mod_nombre_entidad=$result->fields["nombre_entidad"];
		$mod_dir=$result->fields["dir_entidad"];
		$mod_tipo_entidad=$result->fields["nombre_tipo_entidad"];
		$mod_numero=$result->fields["nro_lic_codificado"];
		$mod_mant=$result->fields["mant_oferta_especial"];
		$mod_forma=$result->fields["forma_de_pago"];
		$mod_valor=$result->fields["valor_pliego"];
		$mod_comentarios=$result->fields["observaciones"];
		$mod_moneda=$result->fields["id_moneda"];
		$mod_estado=$result->fields["id_estado"];
		$mod_fecha_ent=Fecha($result->fields["fecha_entrega"]);
		$mod_plazo_ent=$result->fields["plazo_entrega"];
	}
	else {
		$mod_distrito = $_POST["mod_distrito"];
		$mod_entidad = $_POST["mod_entidad"];
		$mod_nombre_entidad=$_POST["nombre_entidad"];
		$mod_dir= $_POST["mod_dir"];
		$mod_numero = $_POST["mod_numero"];
		$mod_mant = $_POST["mod_mant"];
		$mod_forma = $_POST["mod_forma"];
		$mod_comentarios = $_POST["mod_comentarios"];
		$mod_fecha_ent = $_POST["mod_fecha_ent"];
		$mod_plazo_ent = $_POST["mod_plazo_ent"];
		$mod_estado = $_POST["mod_estado"];
		$mod_moneda = $_POST["mod_moneda"];
		$file_id = $_POST["file_id"];
	}

	if ($mod_fecha_ent != "") {
		if (!FechaOk($mod_fecha_ent)) {
			Error("El formato de la fecha de entrega no es válido");
		}
	}
	
	
	cargar_calendario();
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Modificación de datos del Presupuesto</b></td></tr>";
	echo "<tr>\n";
	echo "<td align=center width=50% colspan=2>
	        <font size=4><b>ID:</b> $ID</font></td>\n";
	echo "</tr><tr>\n";
	?>
	<script>
	 var wcliente=0;

     function cargar_cliente()
     {
      document.all.id_cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
      document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
      if (wcliente.document.all.chk_direccion.checked)
	   document.all.entrega.value=wcliente.document.all.direccion.value; 
     }
	</script>
	<td colspan="2">
	 <table width="100%" cellspacing="1" cellpadding="1" border="1">
     <tr> 
        <tr align="center"> 
        <? $link=encode_link('../pcpower/Entidades/pcpower_nuevo_cliente.php',array('pagina'=>'facturas'))?>
           <td colspan="3" height="20">
            <strong>Cliente </strong>
           </td>
          </tr>
          <tr> 
           <td colspan="2">
            <input type="hidden" name="id_entidad" value="<?=$mod_entidad?>">
            <table align="center">
              <tr> 
               <td align="right"> 
                 <strong>Nombre&nbsp;&nbsp;</strong> 
                 <input name="nbre" type="text" title="Para editar los campos del cliente presione el boton elegir cliente" value="<?=$mod_nombre_entidad?>" size="67">
              </td>
              <td align="left" width="80%"> 
                <?$link=encode_link('../Entidades/pcpower_nuevo_cliente.php',array('pagina'=>'facturas'))?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" name="clientes" value="Elegir cliente" title="Permite elegir cliente para la factura" onclick="if(wcliente==0 || wcliente.closed) wcliente=window.open(<?echo "'$link'"?>,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=190,top=0,width=600,height=550'); else wcliente.focus()"> 
               </td> 
             </tr>
            </table> 
           </td>  
          </tr>
          <tr align="left"> 
            <td height="30" colspan="3"> <strong>Dirección</strong> 
              <input name="dir" title="Para editar los campos del cliente presione el boton elegir cliente" type="text"  value="<?=$mod_dir?>" size="67">
            </td>
          </tr>
        <!--  <tr> 
            <td width="52%" height="24" align="left" nowrap><strong>C.U.I.T</strong> 
              &nbsp; &nbsp; 
              <input name="cuit" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $cuit ?>"  <? if (!$can_finish) echo " disabled " ?> size="18" > </td>
            <td width="48%" height="24" colspan="2" align="left" nowrap>&nbsp; 
              <strong>I.I.B.</strong> 
              <input name="iib" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iib ?>" <? if (!$can_finish) echo " disabled " ?> size="17" > </td>
          </tr>
          <tr align="left"> 
            <td height="35" colspan="3"><strong>Condición I.V.A</strong> 
              <input name="condicion_iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $condicion_iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="20" >
              &nbsp;&nbsp; <strong> I.V.A %</strong> 
              <input name="iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="6" ></td>
          </tr>
          <tr> 
            <td height="60" colspan="3"> 
              <div align="left"> <strong>Otros</strong> 
                <textarea name="otros" title="Para editar los campos del cliente presione el boton elegir cliente"  cols="50" rows="3" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $otros ?></textarea>
              </div></td>
          </tr>-->
    </table>
   </td>
 <?
	echo "</tr><tr>\n";
	echo "<td align=left>";
	echo "<table width=100%><tr>";
	echo "<td align=right width=50% nowrap><b>Mantenimiento de oferta:</b></td>\n";
	echo "<td align=left width=50%>";
	echo "<select name=mod_mant OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_mant = array("5 días hábiles",
						"5 días corridos",
						"10 días hábiles",
						"10 días corridos",
						"15 días hábiles",
						"15 días corridos",
						"30 días hábiles",
						"30 días corridos",
						"60 días hábiles",
						"60 días corridos",
						"90 días hábiles",
						"90 días corridos");
	$mant_edit = 1;
	foreach ($array_mant as $key => $val) {
		echo "<option value='$val'";
		if ("$val" == "$mod_mant") {
			echo " selected";
			$mant_edit = 0;
		}
		echo ">$val";
	}
	if ($mod_mant != "") {
		if ($mant_edit) {
			echo "<option selected>$mod_mant";
		}
		echo "<option id=editable>Edite aquí";
	}
	else {
		echo "<option id=editable>Edite aquí";
	}
	echo "</select></td>\n";
	//"<input type=text name=mod_mant value='$mod_mant' size=20 maxlength=100></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Forma de pago:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=mod_forma OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_forma = array("Contado contra entrega",
						"10 días de la fecha de entrega",
						"10 días de la recepción de los bienes",
						"15 días de la fecha de entrega",
						"15 días de la recepción de los bienes",
						"30 días de la fecha de entrega",
						"30 días de la recepción de los bienes",
						"60 días de la fecha de entrega",
						"60 días de la recepción de los bienes");
	$forma_edit = 1;
	foreach ($array_forma as $key => $val) {
		echo "<option value='$val'";
		if ("$val" == "$mod_forma") {
			echo " selected";
			$forma_edit = 0;
		}
		echo ">$val";
	}
	if ($mod_forma != "") {
		if ($forma_edit) {
			echo "<option selected>$mod_forma";
		}
		echo "<option id=editable>Edite aquí";
	}
	else {
		echo "<option id=editable>Edite aquí";
	}
	echo "</select></td>\n";
	//"<input type=text name=mod_forma value='$mod_forma' size=20 maclength=100></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Plazo de entrega:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=mod_plazo_ent OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_plazo = array("Inmediato",
						"5 días corridos",
						"5 días hábiles",
						"10 días corridos",
						"10 días hábiles",
						"15 días corridos",
						"15 días hábiles",
						"30 días corridos",
						"30 días hábiles",
						"45 días corridos",
						"45 días hábiles");
	$plazo_edit = 1;
	foreach ($array_plazo as $key => $val) {
		echo "<option value='$val'";
		if ("$val" == "$mod_plazo_ent") {
			echo " selected";
			$plazo_edit = 0;
		}
		echo ">$val";
	}
	if ($mod_plazo_ent != "") {
		if ($plazo_edit) {
			echo "<option selected>$mod_plazo_ent";
		}
		echo "<option id=editable>Edite aquí";
	}
	else {
		echo "<option id=editable>Edite aquí";
	}
	echo "</select></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Fecha de entrega:</b></td>\n";
	echo "<td align=left>";
	echo "<input type=text name=mod_fecha_ent value='$mod_fecha_ent' size=10 maxlength=10>";
	echo link_calendario("mod_fecha_ent");
	echo "</td>";
	echo "</tr></table>\n";
	echo "</td><td align=center valign=top>";
	echo "<table width=100% cellpadding=5><tr>";
	/*echo "<td align=left width=30%><b>Número:</b></td>\n";
	echo "<td align=left width=70%><input type=text name=mod_numero value='$mod_numero'></td>\n";*/
	echo "</tr><tr>\n";
	echo "<td align=left width=30%><b>Moneda:</b></td>\n";
	echo "<td align=left width=70%>";
	echo "<select name=mod_moneda>\n";
	$result1 = sql("select id_moneda,nombre from pcpower_moneda") or die;
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_moneda"]."'";
		if ($result1->fields["id_moneda"] == $mod_moneda) echo " selected";
		echo ">".$result1->fields["nombre"]."\n";
		$result1->MoveNext();
	}
	echo "</select>\n";
	echo "</tr>";
	/*echo "<td align=left width=30%><b>Estado:</b></td>\n";
	echo "<td align=left width=70%>
	       <select name=mod_estado>\n";
	$result1 = sql("select id_estado,nombre,color from pcpower_estado order by id_estado") or die;
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_estado"]."' ";
		echo "style='background-color: ".$result1->fields["color"]."; ";
		echo "color:".contraste($result1->fields["color"],"#000000","#ffffff").";'";
		if ($mod_estado == $result1->fields["id_estado"]) echo " selected";
		echo ">".$result1->fields["nombre"]."\n";
		$result1->MoveNext();
	}
	echo "</select></td>\n";*/
	
	
	echo "</tr></table>\n";
	echo "</td></tr>\n";
	echo "<tr>\n";
	echo "<td align=left valign=top colspan=2><b>Comentarios/Seguimiento:</b><br>";
	echo "<textarea name='mod_comentarios' style='width:100%;' rows=10>$mod_comentarios</textarea></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=left colspan=2>\n";
	echo "<b>Archivos:</b><br>\n";
	echo "<table cellpadding=3 cellspacing=3 width=100%>\n";
	echo "<tr><td colspan=5 align=left></td></tr>\n";
	$result1 = sql("select * from pcpower_archivos where id_licitacion=$ID") or die;
	if ($result1->RecordCount() > 0 ) {
		echo "<tr bgcolor=$bgcolor3>\n";
		echo "<td width=10% align=center><b>Imprimir</b></td>\n";
		echo "<td width=45% align=left><b>Nombre</b></td>\n";
		echo "<td width=20% align=center><b>Fecha de cargado</b></td>\n";
		echo "<td width=25% align=left><b>Cargado por</b></td>\n";
		echo "</tr>\n";
		while (!$result1->EOF) {
			$mc = substr($result1->fields["subidofecha"],5,2);
			$dc = substr($result1->fields["subidofecha"],8,2);
			$yc = substr($result1->fields["subidofecha"],0,4);
			$hc = substr($result1->fields["subidofecha"],11,5);
			$imprimir = $result1->fields["imprimir"];
			if ($imprimir == "t") $color_imprimir = "#00cc00";
			else $color_imprimir = "#cc2222";
			echo "<tr bgcolor=$bgcolor3>\n";
			echo "<td align=center bgcolor='$color_imprimir'>\n";
			echo "<select name=file_id[".$result1->fields["idarchivo"]."]>";
			echo "<option value='t'";
			if ($imprimir == "t") echo " selected";
			echo ">Sí\n";
			echo "<option value='f'";
			if ($imprimir == "f") echo " selected";
			echo ">No\n";
			echo "</select>";
			echo "</td>\n";
			echo "<td align=left>\n";
			echo "<a title='Archivo: ".$result1->fields["nombrecomp"]."\nTamaño: ".number_format($result1->fields["tamañocomp"]/1024)." Kb' href='".encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download","Comp"=>1))."'>";
			echo "<img align=middle src=$html_root/imagenes/zip.gif border=0>";
			echo "</a>&nbsp;&nbsp;";
			echo "<a title='Archivo: ".$result1->fields["nombre"]."\nTamaño: ".number_format($result1->fields["tamaño"]/1024)." Kb' href='".encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download"))."'>".$result1->fields["nombre"]."</a>\n";
			echo "</td>\n";
			echo "<td align=center>$dc/$mc/$yc $hc hs.</td>\n";
			echo "<td align=left>".$result1->fields["subidousuario"]."</td>\n";
			echo "</tr>\n";
			$result1->MoveNext();
		}
	}
	else {
		echo "<tr><td colspan=5 align=center><b>No hay archivos disponibles para este presupuesto</b></td></tr>\n";
	}
	echo "</table>\n";
	echo "</td></tr>\n";
	echo "<tr>\n";
	echo "<td style=\"border:$bgcolor3;\" colspan=2 align=center><br>\n";

	//dir cambio es definido para saber si cambia distrito o entidad
	echo "<input name='dir_cambio' type='hidden' value='0'>";

	echo "<input type=hidden name=ID value='$ID'>\n";
	echo "<input type=hidden name=det_edit value='1'>\n";
//	echo "<input type=hidden name=cmd1 value='detalle'>\n";
	echo "<input type=submit name=mod_edit value='Aceptar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=button name=mod_cancel value='Cancelar' onClick=\"document.location='".encode_link("pcpower_presupuestos_view.php",array("cmd1"=>"detalle","ID"=>$ID))."'; return false;\"><br><br>\n";
	echo "</td>";
	echo "</tr>\n";
	echo "</table><br>\n";
}
elseif ($cmd1 == "detalle") {
	echo $html_header;
	$ID = $parametros["ID"] or $ID = $_POST["ID"];
	detalle($ID);
}
elseif ($cmd1 == "download") {
	$ID = $parametros["ID"];
	include_once('../pcpower_lib.php');
	pcpower_download_file($ID);
	
}
elseif ($_POST["files_add"]) {
	$ID = $_POST["ID"];
	$nombre_entidad = $_POST["nombre_entidad"];

	$extensiones = array("doc","obd","xls","zip");
	include_once('../pcpower_lib.php');
	echo $html_header;

	pcpower_ProcForm($_FILES);
	detalle($ID);
	
}
elseif ($_POST["files_cancel"]) {
	$ID = $_POST["ID"];
	echo $html_header;
	detalle($ID);
}
elseif ($_POST["det_addfile"]) {
	genera_det_addfile("PcPower");
}
else {
	listado();
}
function detalle($ID) {
	global $parametros,$contador_consultas,$datos_barra,$bgcolor2,$bgcolor3,$html_root,$ver_papelera,$permisos,$_ses_user_login,$bgcolor_out;
	if (!$ID) { listado(); }
	else {
		$sql = "SELECT pcpower_licitacion.*, ";
		$sql.= "pcpower_entidad.id_entidad as id_entidad, ";
		$sql .= "pcpower_entidad.nombre as nombre_entidad,pcpower_entidad.direccion as dirc_entidad, ";
		$sql .= "pcpower_entidad.perfil, ";
		$sql .= "pcpower_distrito.nombre as nombre_distrito, ";
		$sql .= "pcpower_moneda.nombre as nombre_moneda, ";
		$sql .= "pcpower_estado.nombre as nombre_estado, ";
		$sql .= "pcpower_estado.color as color_estado, ";
		$sql .= "pcpower_tipo_entidad.nombre as tipo_entidad,pcpower_candado.estado as candado ";
		$sql .= "FROM pcpower_licitacion ";
		$sql .= "LEFT JOIN pcpower_entidad ";
		$sql .= "USING (id_entidad) ";
		$sql .= "LEFT JOIN pcpower_tipo_entidad ";
		$sql .= "USING (id_tipo_entidad) ";
		$sql .= "LEFT JOIN pcpower_distrito ";
		$sql .= "USING (id_distrito) ";
		$sql .= "LEFT JOIN pcpower_moneda ";
		$sql .= "USING (id_moneda) ";
		$sql .= "LEFT JOIN pcpower_estado ";
		$sql .= "USING (id_estado) ";
		$sql .= "LEFT JOIN pcpower_candado ";
		$sql .= "USING (id_licitacion) ";
		$sql .= "WHERE pcpower_licitacion.id_licitacion=$ID";
		$result = sql($sql) or die;
		//generar_barra_nav($datos_barra);

//$result tiene los datos de la licitacion
//incluyo la funcion de tips
//verificar_tips($result,"licitaciones");

		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
		echo "<br>
		      <table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center>";
		echo "<tr>
		       <td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3>
		        <b>Detalles del Presupuesto</b>
		       </td>
	          </tr>";
		if ($result->RecordCount() == 1) {
			$ma = substr($result->fields["fecha_apertura"],5,2);
			$da = substr($result->fields["fecha_apertura"],8,2);
			$ya = substr($result->fields["fecha_apertura"],0,4);
			$ha = substr($result->fields["fecha_apertura"],11,5);
			echo "<tr>\n";
			echo "<td  width=50% align=center valign=middle colspan=2>";
			if($result->fields['candado']!=0)
			 echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Este presupuesto solo puede verse, pero no modificarse'> ";
			echo "<font size=4><b>ID:</b> ".$result->fields["id_licitacion"]."</font>";
			echo "</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left>";
			 echo "<table width='100%'>" ;
			  echo "<tr>";
			   echo "<td width='70%'>";
				 $nombre_entidad=$result->fields["nombre_entidad"];
			     echo "<input type=hidden name='nombre_entidad' value='$nombre_entidad'>";
			     echo "<br><b>Entidad:</b> $nombre_entidad";
                 echo "<br><b>Dirección: </b>" .html_out($result->fields["dirc_entidad"]);
			    echo "</td>";
			  /*echo "<td valign='top'>";
			     $id_entidad=$result->fields["id_entidad"];
			     $perfil=encode_link("../pcpower_perfil_entidad.php",array("id_entidad"=>$id_entidad,"modulo"=>"Licitaciones"));
			     echo "<input type='button' name='Nuevo' Value='Perfil' style=\"width:100%\" onclick=\"window.open('$perfil','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400');\">";
			    echo "</td>";*/
			  echo "</tr>";
			 echo "</table>";
			echo "</td>";
			/*echo "<td align=left>";
//incluyo la funcion que verifica si hay contactos
			 /*echo "<table width='100%' align='right'>";
			   echo "<tr align='right'>";
			     echo "<td align='right'>";
			      $nuevo_contacto=encode_link("../general/contactos.php",array("modulo"=>"Licitaciones",
										 "id_licitaciones"=>$ID,
										 "id_general"=>$result->fields['id_entidad']));
			      echo "<input type='button' name='gestiones' value='Gestiones generales' onclick='parent.window.location=\"".encode_link("../../index.php",Array ("menu"=>"lic_gestiones","extra"=>Array ("cmd1"=>"detalle","id"=>$ID)))."\";'>\n";*/
			      /*echo "<input type='button' name='Nuevo' Value='Nuevo Contacto' style=\"width:30%\" onclick=\"window.open('$nuevo_contacto','','toolbar=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
			     echo "</td>";
			    echo "</tr>";
			    echo "<tr align='right'>";
			     echo "<td>";
			      contactos_existentes("Licitaciones",$result->fields['id_entidad']);
			     echo "</td>";
			    echo "</tr>";
			 echo "</table>";
			echo "</td>";*/
           echo "</tr><tr>\n";
			echo "<td align=left><b>Mantenimiento de oferta:</b> ".$result->fields["mant_oferta_especial"]."\n";
			 echo "<br><b>Forma de pago:</b> ".$result->fields["forma_de_pago"]."\n";
			 echo "<br><b>Plazo de entrega</b>: \n";
             echo $result->fields["plazo_entrega"];
    		 echo "<br><b>Fecha de entrega</b>: \n";
			 if ($result->fields["fecha_entrega"] != "") {
				$me = substr($result->fields["fecha_entrega"],5,2);
				$de = substr($result->fields["fecha_entrega"],8,2);
				$ye = substr($result->fields["fecha_entrega"],0,4);
				echo "$de/$me/$ye\n";
		     } 
			 else { echo "N/A\n"; }
			echo "</td>\n";
			/*echo "<td align=right valign=top><b>Número:</b> ".html_out($result->fields["nro_lic_codificado"])." <br>\n";
     		  echo "<br>
			       <table width=100%> 
			        <tr> 
			          <td align=right>";
				       echo "<b>Estado:</b>\n";
				       echo "<span style='background-color: ".$result->fields["color_estado"]."; border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;'>&nbsp;&nbsp;&nbsp;</span> ".$result->fields["nombre_estado"];
				       echo "</td></tr><tr><td>";
				       mostrar_ordenes_compra($ID);
			echo "    </td>
			        </tr>";
			 echo "</table>";
		   echo"</td>";	*/
		  echo "</tr>\n";
		  echo "<tr>\n";
		   echo "<td align=left colspan=2>
			      <b>Comentarios/Seguimiento:</b><br>".html_out($result->fields["observaciones"])."
		         </td>\n";
		  echo "</tr>\n";
		  	/*
		  echo "<tr>
		         <td align=left colspan=2><b>Perfil: </b>";
			       if ( $result->fields["perfil"])
				    echo "<br>".html_out($result->fields["perfil"]);
			       else
			        echo "<b style='color:red' >No se ha cargado el perfil </b>" ;
		   echo  "</td>
		        </tr>\n";
		
			$sql="select * from pcpower_protocolo_leg where id_licitacion=".$ID." and entidad='".$result->fields["nombre_entidad"]."';"; //verifico si llenaron el protocolo
			$resultado_ex=sql($sql) or die;
			if ($resultado_ex->RecordCount()<=0)
			{echo "<tr>\n";
			  echo "<td align=left colspan=2><b>Protocolo Legal:<font color=\"red\"> No se ha cargado el protocolo legal</font></td>\n";
			 echo "</tr>\n";
			}

			echo "<tr><td align=left colspan=2><b>CD DE OFERTA: </b>";
			//directorio donde esta la imagen del cd
			if (file_exists(UPLOADS_DIR."/PCPOWER/Presupuesto/$ID/CD_OFERTA_$ID.zip"))
			{
				echo "<b style='color:#006600;'>Ya se creó el CD DE OFERTA  </b>";
 				echo "<input type='button' value='Ver archivos' name='btn_ver' onclick=\"window.open('".encode_link("../licitaciones/cd_oferta.php",array("id_lic"=>$ID))."','','toolbar=1,location=1,directories=0,status=1, menubar=1,scrollbars=1')\">";			 
			}
			else
			{
			 echo "<b style='color:red' >No se ha creado el CD DE OFERTA  </b>" ;
			 echo "<input type='button' value='Crear CD' name='btn_cargar' onclick=\"window.open('".encode_link("../licitaciones/cd_oferta.php",array("id_lic"=>$ID))."','','toolbar=1,location=1,directories=0,status=1, menubar=1,scrollbars=1')\">";			 
			}
			echo  "</td></tr>\n";
*/
			echo "<tr>\n";
			 echo "<td align=left colspan=2>\n";
			  echo "<b>Archivos:</b><br>\n";
			
			   lista_archivos_lic($ID,"pcpower");
			
			  echo "</td>
			      </tr>\n";
			if ($result->fields["ultimo_usuario"]) {
				$mm = substr($result->fields["ultimo_usuario_fecha"],5,2);
				$dm = substr($result->fields["ultimo_usuario_fecha"],8,2);
				$ym = substr($result->fields["ultimo_usuario_fecha"],0,4);
				$hm = substr($result->fields["ultimo_usuario_fecha"],11,5);
				echo "<tr>\n";
				 echo "<td colspan=2><b>Ultima modificación hecha por ".$result->fields["ultimo_usuario"]." el $dm/$mm/$ym a las $hm</b></td>\n";
				echo "</tr>\n";
			}
			echo "<tr>
			       <td style=\"border:$bgcolor2\" colspan=2 align=center>";
 			        if($result->fields['candado']!=0)
        			  echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Este presupuesto solo puede verse, pero no modificarse'> ";
		            echo "<font size=4><b>ID:</b> ".$result->fields["id_licitacion"]."</font> ";

		            //chequeamos si el usuario que se ha logueado, tiene permiso
		            //para ver el boton de eliminar o no. Si tiene lo mostramos, sino ,esta deshabilitado.

		            if (permisos_check("inicio","permiso_candado"))
		            $tiene_permiso_candado="";
		            else
		            $tiene_permiso_candado="disabled";

		            //Poner o sacar candado
		            if($result->fields['candado']==0)
		            { $link_self_poner= encode_link("pcpower_presupuestos_view.php",array("cmd1"=>"candadoponer","ID"=>$ID));
		            echo "<input type=button name=det_candado $tiene_permiso_candado style='width:100;' value='Poner Candado' onClick=\"document.location='".$link_self_poner."';\"></td>";
		            }
		            else
		            {$link_self_sacar = encode_link("pcpower_presupuestos_view.php",array("cmd1"=>"candadosacar","ID"=>$ID));
		            echo "<input type=button name=det_candado $tiene_permiso_candado style='width:100;' value='Sacar Candado' onClick=\"document.location='".$link_self_sacar."';\"></td>";
		            }
		    echo " </td>
		          </tr>";
//			echo "<tr>\n";
//			 echo "<td style=\"border:$bgcolor2\" colspan=2 align=center><br>\n";
			 echo "<input type=hidden name=ID value='$ID'>\n";
			 if ($ver_papelera) {
				echo "<tr>\n";
	    		   echo "<td style=\"border:$bgcolor2\" colspan=2 align=center><br>\n";
    				echo "<input type=submit name=det_restore style='width:160;' value='Restaurar Presupuesto' onClick=\"return confirm('ADVERTENCIA: Se va a restaurar el Presupuesto número $ID');\">&nbsp;&nbsp;&nbsp;";
				echo "  </td>
				      </tr>";
			}
			else {
				$link=encode_link("pcpower_licitaciones_renglones.php",array("ID"=>$ID));

				 
                //chequeamos si el usuario que se ha logueado, tiene permiso
                //para ver el boton de eliminar o no. Si tiene lo mostramos, sino ,esta deshabilitado.
               
                if (permisos_check("inicio","eliminar_lic")) 
                  $tiene_permiso="";
                else   
                  $tiene_permiso="disabled";
				
                  
				//si el candado esta puesto, deshabilitamos los botones de
				//Eliminar Archivos y Eliminar Licitaciones
				$disabled="";
				if($result->fields['candado']!=0)
				{$disabled="disabled";
				 $tiene_permiso="disabled";
				} 

				$link_self_result_coradir = encode_link("pcpower_presupuestos_view.php",array("cmd1"=>"resultado_coradir","ID"=>$ID));
			
				if (permisos_check("inicio","resultados_coradir")) 
                  $visibility_resultado_coradir="visible";
                else   
                  $visibility_resultado_coradir="hidden";  

               echo "<tr>";
               echo "<td colspan=2>";
               echo "<table width=100% border=0>";
              /* echo "<tr>";
                echo "<td colspan=2 valign=top>";
                    echo "<table width=30% align=right border=1 cellspacing=1 cellpading=1>";
                      echo "<tr bgcolor=$bgcolor3>";
                        echo "<td width=5% align=center>";
                        echo "<input type=checkbox class='estilos_check' $checked_resultado name=det  onclick='javascript:(this.checked)?Mostrar(\"tabla_detalles_tercera\"):Ocultar(\"tabla_detalles_tercera\");'>";
                        echo "</td>";
                        echo "<td align=center>";
                        echo "<font color=red>";
                        echo "Resultados";
                        echo "</font>";
                        echo "</td>";
                        echo "<td width=5% align=Center>";
                        echo "<input type=checkbox class='estilos_check' $checked_asociaciones name=det  onclick='javascript:(this.checked)?Mostrar(\"tabla_detalles_cuarta\"):Ocultar(\"tabla_detalles_cuarta\");'>";
                        echo "</td>";
                        echo "<td align=center>";
                        echo "<font color=red>";
                        echo "Asociaciones";
                        echo "</font>";
                        echo "</td>";
                     echo "</tr>";
                   echo "</table>";
                 echo "</td>";
               echo "</tr>";
*/
               echo "<tr>";
                   echo "<td widht=100% align=left colspan=2 valign=top>";
                        echo "<div id='tabla_detalles_primera' style='display:block'>";
                        echo "<table width=100% align=center>";
			               	echo "<tr>";
		                        echo "<td width=33% align=center>";
									echo "<input type=button name=det_oferta style='width:170;' value='Realizar Oferta' onClick=\"window.open('$link','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=550');\">&nbsp;&nbsp;&nbsp;";
			               		echo "</td>";
		                        echo "<td width=35% align=center>";
									echo "<input type=submit name=det_edit style='width:170;' value='Modificar'>&nbsp;&nbsp;&nbsp;";
			               		echo "</td>";
		                        echo "<td width=33% align=center>";
									echo "<input type=submit name=det_addfile style='width:170;' value='Agregar archivo'>&nbsp;&nbsp;&nbsp;";
			               		echo "</td>";
			               	echo "</tr>";
                        echo "</table>";
               			echo "</div>";
               		echo "</td>";
               	echo "</tr>";
               	echo "<tr>";
                 	echo "<td widht=100% align=left colspan=2 valign=top>";
                 	echo "<div id='tabla_detalles_segunda' style='display:block'>";
                        echo "<table width=100% align=left border=0>";
                        echo "<tr>";
                        	echo "<td align=right width=45%>";
								echo "<input type=submit name=det_del style='width:170;' ".$tiene_permiso." value='Eliminar Presupuesto' onClick=\"return confirm('ADVERTENCIA: Se va a enviar a la papelera el Presupuesto número $ID');\">";
							echo "</td>";
							echo "<td width=10%>";
								echo "&nbsp;";
							echo "</td>";
                        	echo "<td align=left width=45% >";
								echo "<input type=submit name=det_delfile style='width:170;' ".$disabled." value='Eliminar Archivos' onClick=\"return confirm('ADVERTENCIA: Se van a eliminar los archivos seleccionados');\">";
							echo "</td>";
			               	echo "</tr>";
                        echo "</table>";
               			echo "</div>";
               		echo "</td>";
               	echo "</tr>";
	            echo "<tr>";
               		echo "<td colspan=2 widht=100% align=center  valign=top>";
               		echo "<div id='tabla_detalles_tercera' style='display:none'>";
                        echo "<table width=100% align=center bgcolor=''>";
                        echo "<tr>";
                        	echo "<td align=left width=25%>";
								echo "<input type=button name=det_ver_res style='width:170;' value='Ver Resultados' onClick=\"document.location='".encode_link("../licitaciones/lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic","pagina_volver"=>"../presupuestos/presupuestos_view.php"))."';\">";
							echo "</td>";
                        	echo "<td align=left width=25%>";
								echo "<input type=button name=det_cargar_res style='width:170;' value='Cargar Resultados' onClick=\"document.location='".encode_link("../licitaciones/lic_cargar_res.php",array("id_lic"=>$ID,"pagina_volver"=>"../presupuestos/presupuestos_view.php"))."'; return false;\">";
							echo "</td>";
                        	echo "<td align=left width=25%>";
								echo "<input type=button name=det_result_coradir style='width:170;' value='Resultados Coradir' style='visibility:$visibility' onClick=\"document.location='".$link_self_result_coradir."';\">";
                        	echo "</td>";
			               	echo "</tr>";
                        echo "</table>";
               			echo "</div>";
               		echo "</td>";
               	echo "</tr>";
            	echo "<tr>";
            		echo "<td widht=100% align=center  colspan=2 valign=top>";
            			echo "<div id='tabla_detalles_cuarta' style='display:none'>";
                       	echo "<table width=100% align=center bgcolor=''>";
                       		echo "<tr>";
                   			echo "<td align=left width=25%>";
							if ($result->fields["nombre_estado"] == "Entregada" or $result->fields["nombre_estado"] == "Orden de compra") {
								echo "<input type=button name=cobranzas style='width:170;' value='Seguimiento de cobros' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"lic_cobranzas","extra"=>array("cmd1"=>"detalle","id_lic"=>$ID)))."';\">";
							}
                        	echo "</td>";
                   			echo "<td align=left width=25%>";
								echo "<input type=button name=ordenes_asoc style='width:170;' value='O. de compra asociadas' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"ord_compra_listar","extra"=>array("filtro"=>"todas","keyword"=>$ID,"filter"=>"o.id_licitacion","volver_lic"=>$ID)))."';\">";
                        	echo "</td>";
                   			echo "<td align=left width=25%>";
								echo "<input type=button name=ordenes_prod_asoc style='width:170;' value='O. de producción asociadas' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"ordenes_ver","extra"=>array("filtro"=>"todas","keyword"=>$ID,"volver_lic"=>$ID)))."';\">";
                        	echo "</td>";
			               	echo "</tr>";
                        echo "</table>";
               			echo "</div>";
               		echo "</td>";
               	echo "</tr>";
			echo "</table>";
			echo "</td>";
			echo "</tr>";
			}

			if ($parametros["link_volver"]) {
				$link_volver = $parametros["link_volver"];
			}
			else {
				$link_volver = "document.location='".$_SERVER["PHP_SELF"]."';";
			}
			echo "<tr>
			       <td align=center colspan=2>
			         <br><input type=button name=det_volver style='width:160;' value='Volver' onClick=\"$link_volver\">
			       </td>\n";
			echo "</tr>";
		}
		echo "</table></form>\n";
		fin_pagina();
	}
} 
function listado() {
	global $bgcolor3,$cmd,$cmd1,$datos_barra,$up,$_ses_user_login,$db;
	global $bgcolor2,$itemspp,$parametros,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera,$permisos;
	global $_ses_global_backto,$_ses_global_nro_orden_asociada,$_ses_global_pag,$contador_consultas;
	echo $html_header;
	

	//generar_barra_nav($datos_barra);

	$orden = array(
		"default" => "1",
		"default_up" => "0",
		"1" => "pcpower_licitacion.id_licitacion",
		"2" => "pcpower_licitacion.id_estado",
		"3" => "pcpower_entidad.nombre",
		"4" => "pcpower_distrito.nombre",
	);

	$filtro = array(
		"pcpower_distrito.nombre" => "Distrito",
		"pcpower_entidad.nombre" => "Entidad",
		"pcpower_licitacion.observaciones" => "Comentarios",
		"pcpower_licitacion.id_moneda" => "Moneda",
		"pcpower_licitacion.id_licitacion" => "ID de Presupuesto",
	);

	$itemspp = 50;

	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
	echo "<tr><td align=center>\n";

	$sql_tmp = "SELECT pcpower_licitacion.id_licitacion,pcpower_licitacion.fecha_apertura,pcpower_licitacion.nro_lic_codificado,pcpower_licitacion.id_estado,pcpower_licitacion.check_lic,pcpower_licitacion.resultados_cargados,pcpower_entidad.nombre AS nombre_entidad,";
	$sql_tmp .= "pcpower_distrito.nombre AS nombre_distrito ";
    if($cmd=="proximas" ||$cmd=="presentadas")
    {$sql_tmp .= ",pcpower_licitacion.observaciones,pcpower_entregar_lic.orden_subida,pcpower_entregar_lic.mostrar,pcpower_entregar_lic.vence,pcpower_entregar_lic.oferta_subida,";
	 $sql_tmp .= "pcpower_entregar_lic.archivo_oferta,pcpower_entregar_lic.oferta_lista_imprimir ";
    }
	//$sql_tmp .= "archivos.imprimir,count(renglon.id_renglon) AS cant_renglones,";
	//$sql_tmp .= "count(oferta.id) AS cant_ofertas ";
	$sql_tmp .= "FROM pcpower_licitacion LEFT JOIN pcpower_entidad ";
	$sql_tmp .= "USING (id_entidad) ";
	$sql_tmp .= "LEFT JOIN pcpower_distrito ";
	$sql_tmp .= "USING (id_distrito) ";
	$sql_tmp .= "LEFT JOIN pcpower_estado ";
	$sql_tmp .= "USING (id_estado) ";
	if($cmd=="proximas" ||$cmd=="presentadas")
	{$sql_tmp .= "LEFT JOIN pcpower_entregar_lic ";
	 $sql_tmp .= "USING (id_licitacion) ";
	} 

	$link_tmp = array("cmd"=>$cmd);

	$where_tmp = " es_presupuesto=1 ";
		 $contar="SELECT count(*) FROM pcpower_licitacion WHERE borrada='f' and es_presupuesto=1";
	if ($where_tmp != "") {
		$where_tmp .= " AND ";
	}
	if ($ver_papelera) {
		$where_tmp .= "borrada='t'";
	}
	else {
		$where_tmp .= "borrada='f'";
	}
	if ($estado != "all") {
		if($estado != "") {
			$where_tmp .= " AND pcpower_licitacion.id_estado = $estado";
		}
	}
	$where_tmp .= " GROUP BY pcpower_licitacion.id_licitacion,pcpower_licitacion.id_entidad,
					pcpower_licitacion.id_moneda,pcpower_licitacion.id_estado,
					pcpower_licitacion.numero,pcpower_licitacion.nro_lic_codificado,
					pcpower_licitacion.fecha_apertura,pcpower_licitacion.valor_pliego,
					pcpower_licitacion.mantenimiento_oferta,pcpower_licitacion.prorroga_automatica,
					pcpower_licitacion.valor_dolar_lic,pcpower_licitacion.valor_dolar_compra,
					pcpower_licitacion.forma_de_pago,pcpower_licitacion.fecha_entrega,
					pcpower_licitacion.dias_para_entrega,pcpower_licitacion.plazo_entrega,
					pcpower_licitacion.tipo_dia_mant_ofeta,pcpower_licitacion.tipo_dia_plazo_entre,
					pcpower_licitacion.ofertado_sistema,pcpower_licitacion.mant_oferta_especial,
					pcpower_licitacion.usuario_realiza_licitacion,pcpower_licitacion.finalizado_por_autor,
					pcpower_licitacion.observaciones,pcpower_licitacion.total_ofertado,
					pcpower_licitacion.monto_ganado,pcpower_licitacion.monto_estimado,
					pcpower_licitacion.monto_ofertado,pcpower_licitacion.ultimo_usuario,
					pcpower_licitacion.ultimo_usuario_fecha,pcpower_licitacion.nro_version,
					pcpower_licitacion.nro_renglones,pcpower_licitacion.es_viejo,
					pcpower_licitacion.borrada,pcpower_licitacion.dir_entidad,pcpower_licitacion.resultados_cargados,
					pcpower_licitacion.exp_lic_codificado,pcpower_licitacion.check_lic,pcpower_entidad.nombre,pcpower_distrito.nombre";
     if($cmd=="proximas" ||$cmd=="presentadas")
       $where_tmp .=",pcpower_licitacion.resultados_cargados,pcpower_entregar_lic.orden_subida,pcpower_entregar_lic.mostrar,pcpower_entregar_lic.vence,pcpower_entregar_lic.oferta_subida,pcpower_entregar_lic.archivo_oferta,pcpower_entregar_lic.oferta_lista_imprimir";
					//entregar_lic.archivo_oferta,archivos.imprimir";*/
	if (($cmd == "presentadas" or $cmd == "presupuesto" or $cmd == "historial" or $cmd == "todas") and ($up == "")) {
		$up = "0";
	}
	/*?>
    <div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_ver_lic.htm" ?>', 'LISTAR PRESUPUESTOS')" >
     </div>
     <?	*/
    if($_POST['keyword'] || $keyword || $_POST['estado']!="all")// en la variable de sesion para keyword hay datos)
     $contar="buscar";

	list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);
	
	/*echo "&nbsp;&nbsp;Estado: <select name='estado'>\n";
	echo "<option value='all'>Todos\n";
	$sql_est = "SELECT id_estado,nombre,color FROM pcpower_estado";
	if ($cmd == "presentadas" or $cmd == "proximas") {
		$sql_est .= " WHERE ubicacion='ACTUALES'";
	}
	elseif ($cmd == "presupuesto") {
		$sql_est .= " WHERE ubicacion='PRESUPUESTO'";
	}
	elseif ($cmd == "historial") {
		$sql_est .= " WHERE ubicacion='HISTORIAL'";
	}
	//$sql_est .= " WHERE ubicacion='PRESUPUESTO'";
	$result = sql($sql_est) or die($db->ErrorMsg());
	$estados = array();
	while (!$result->EOF) {
		$estados[$result->fields["id_estado"]] = array(
				"color" => $result->fields["color"],
				"texto" => $result->fields["nombre"]
			);
		$result->MoveNext();
	}
	foreach ($estados as $est => $arr) {
		echo "<option value=$est";
		if ((string)$est == (string)$estado) { echo " selected"; }
		echo ">".$estados[$est]["texto"];
	}
	echo "</select>";*/
	echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";
	echo "</td></tr></table><br>\n";
	echo "</form>\n";
	$result = sql($sql) or die($db->ErrorMsg()."<br>Error");
	//die($sql);
	//$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error");
	echo "<table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=$bgcolor3 align=center>";
	echo "<tr><td colspan=5 align=left id=ma>\n";
	echo "<table width=100%><tr id=ma>\n";
	echo "<td width=30% align=left><b>
	Total:</b> $total_lic presupuestos.</td>\n";
	echo "<td width=70% align=right>$link_pagina</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
//	echo "<div style='position:relative; width:55%; overflow:auto;'>";
	echo "<tr>";
	echo "<td align=right id=mo width=1%><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
	echo "&nbsp;/&nbsp;<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Entidad</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Distrito</td>\n";

	echo "</tr>\n";
	//echo "<div style=\"width:100%; height:100; overflow:auto;\">";
	//echo "<table border=0 width=100% cellspacing=2 cellpadding=3 bgcolor=$bgcolor3 align=center>";
	
	$permiso_check = permisos_check("inicio","logo_check_licitacion");
	while (!$result->EOF) {
		if ($result->fields["observaciones"]) {
			//guardamos en esta variable, las observaciones de la licitacion
			//para mostrarlos en title del nombre de la licitacion
			$title_obs=$result->fields["observaciones"];
			//LIMITAR OBSERVACIONES: controlamos el ancho y la cantidad de
			//lineas que tienen las observaciones y cortamos el string si
			//se pasa de alguno de los limites
			$long_title=strlen($title_obs);
			//cortamos si el string supera los 600 caracteres
			if($long_title>600)
			{$title_obs=substr($title_obs,0,600);
			 $title_obs.="   SIGUE >>>";
			}
			$count_n=str_count_letra("\n",$title_obs);
			//cortamos si el string tiene mas de 12 lineas
			if($count_n>12)
			{$cn=0;$j=0;
			 for($i=0;$i<$long_title;$i++)
			 {
			  if($cn>12)
			   $i=$long_title;
			  if($title_obs[$i]=="\n")
			   $cn++;
			  $j++;
	
			 }
			 $title_obs=substr($title_obs,0,$j);
			 $title_obs.="   SIGUE >>>";
			}
			//FIN DE LIMITAR OBSERVACIONES
		}
		
//		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle","sort"=>$sort,"up"=>$parametros["up"],"page"=>$page,"keyword"=>$keyword,"estado"=>$estado,"filter"=>$filter,"ID"=>$result->fields["id_licitacion"]));
		//si viene de otra pagina como ordcompra, remitos o facturas
		if ($_ses_global_backto)
			$ref = encode_link($_ses_global_backto,array("licitacion"=>$result->fields["id_licitacion"],"nro_orden"=>$_ses_global_nro_orden_asociada,"pagina"=>$_ses_global_pag,"presupuesto"=>"1"));
		else
			$ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
	
		tr_tag($ref);
		echo "<td align=center bgcolor='white' ><b><a style='color=black;' href='$ref'>";
		
		/*pone el candado al lado  del nro de lic
		if($result->fields['candado']!=0)
		 echo "<img src=$html_root/imagenes/candado1.gif border=0 width='15' height='14' align='absmiddle' title='Esta licitacion solo puede verse, pero no modificarse'> ";
		*/

		//ponemos el logo de check si corresponde
		$fecha_actual=substr($fecha_hoy,0,10);
		if($permiso_check && ($result->fields['check_lic']!=0 && $result->fields['fecha_apertura'] >= $fecha_actual))
		 echo "<img src=$html_root/imagenes/check1.gif border=0 width='15' height='14' align='absmiddle' title='Este presupuesto ya ha sido chequeada'> ";
		
		echo $result->fields["id_licitacion"]."</a></b>";
        echo "</td>\n";
		echo "<td align=left title='".$title_obs."'>&nbsp;".html_out($result->fields["nombre_entidad"])."</td>\n";
		echo "<td align=left>&nbsp;".html_out($result->fields["nombre_distrito"])."</td>\n";
/*
		$query="select res.id_renglon from (select id_renglon, id_licitacion from  renglon where id_licitacion=".$result->fields['id_licitacion'].") AS res, oferta  where res.id_renglon=oferta.id_renglon";
		$res=$db->Execute($query) or die($db->ErrorMsg());
		if ($res->RecordCount() > 0 )
		$valor=1;
		else $valor=0;
*/
/*		switch ($valor)
		{
		  case 0:
				 break;
		  case 1: echo "<a href='".encode_link("lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic"))."'>
		     <img src=$html_root/imagenes/R.gif border=0 title='Ver Resultados' width='15' height='14' align='absmiddle'></a>";
			 break;
		}
*/
/*
		if( $result->fields["resultados_cargados"])	
		{ echo "<a href='".encode_link("../licitaciones/lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic"))."'>
				<img src=$html_root/imagenes/R.gif border=0 title='Ver Resultados' width='15' height='14' align='absmiddle'></a>";
		}*/
		
				//mostramos el logo de orden de produccion subida,
	  	//si existe la entrada correspondiente para esta licitacion
	  	//$query="select orden_subida,mostrar,vence,oferta_subida,archivo_oferta from entregar_lic where id_licitacion=".$result->fields['id_licitacion'];
		//$res1=$db->Execute($query)  or die ($db->ErrorMsg().$query);

		$link_mostrar=encode_link("../licitaciones/mostrar_logo.php",array("id_licitacion"=>$result->fields['id_licitacion']));
	  	if(($result->fields['orden_subida']==1)&&($result->fields['mostrar']==1))
		 echo "&nbsp;<a href=\"#\" onmousedown=\"window.open('$link_mostrar','','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,left=170,top=220,width=500,height=100');return false;\"> <img src=$html_root/imagenes/peso.gif border=0 title='La orden de compra ha sido cargada' width='15' height='14' align='absmiddle'></a>";


		$fecha=explode(' ',$result->fields["fecha_apertura"]);
		$fechahoy=date("Y-m-d");
		if(($cmd == "proximas")&&($result->fields['oferta_subida']==1)||(($cmd != "proximas")&&($fecha[0]==$fechahoy)&&($result->fields['oferta_subida']==1)))
		{//revisamos si el archivo de oferta esta listo para imprimir
		 ///$query="select imprimir from archivos where id_licitacion=".$result->fields['id_licitacion']." and nombre='".$res1->fields['archivo_oferta']."'";
	  	 //$res_n=$db->Execute($query)  or die ($db->ErrorMsg().$query);
	  	 if($result->fields['oferta_lista_imprimir']==1)
		  echo "&nbsp;<img src=$html_root/imagenes/enviar1.gif border=0 title='La oferta ha sido cargada' width='15' height='14' align='absmiddle'>";
	  	}
	  	//mostramos el logo de impresora, si el archivo correspondiente
		//a esta licitacion, esta listo para imprimir
	  	/*
		$nombre="oferta_lic_";
    	$nombre.=$result->fields['id_licitacion'];
		$nombre.=".xls";
		$archivo="select imprimir from archivos where nombre='$nombre'	";
		$res1=$db->Execute($archivo)  or die ($db->ErrorMsg().$archivo);
		if($res1->fields['imprimir']=='t')
		 echo "&nbsp; <img src=$html_root/imagenes/imp2.gif border=0 title='La licitación esta lista para imprimir' height='14'>\n";
		*/
		 echo "</td>\n";
	  	$result->MoveNext();
	}
	/*echo "<tr><td colspan=6 align=center><br>\n";
	echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
	echo "<tr>\n";
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td></tr>\n";*/
	echo "</table><br>\n";
	fin_pagina();
} // listado
?>