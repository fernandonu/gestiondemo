<?
/*
$Author: mari $
$Revision: 1.6 $
$Date: 2007/01/05 13:56:08 $
*/

require_once("../../config.php");


$es_pymes=$_GET["es_pyme"] or $es_pymes=$parametros["es_pymes"] or $es_pymes=$_POST["es_pymes"] or $es_pymes=$parametros["es_pyme"] or $es_pymes=$_POST["es_pyme"];

if (!$es_pymes) $es_pymes=0;

//if ($_POST['dir_cambio']==1) unset($_POST["new_entidad"]);
if ($_POST["new_aceptar"]) {
   	echo $html_header;
	$new_distrito = $_POST["new_distrito"];
	$new_entidad = $_POST["new_entidad"];
	//$new_tipo_entidad = $_POST["new_tipo_entidad"];
    if ($es_pymes) {
                   $sql="select id_entidad from entidad where nombre='PRESUPUESTOS PEDIDOS'";
                   $re_entidad=sql($sql) or fin_pagina();
                   $new_entidad_pyme=$new_entidad;
                   $new_entidad=$re_entidad->fields["id_entidad"];
                   $es_presupuesto_pyme=1;
                   }
                   else
                      {
                      $new_entidad_pyme='NULL';
                      $es_presupuesto_pyme=0;
                      }

	$new_mant = $_POST["new_mant"];
	$new_forma = $_POST["new_forma"];
	$new_plazo_ent = $_POST["new_plazo_ent"];
	$new_numero = $_POST["new_numero"];
	$new_exp= $_POST["new_exp"];
	$new_valor = $_POST["new_valor"];
	$new_moneda = $_POST["new_moneda"];
	$new_ofertado = $_POST["new_ofertado"];
	$new_estimado = $_POST["new_estimado"];
	$new_ganado = $_POST["new_ganado"];
	$new_fecha_apertura = $_POST["new_fecha_apertura"];
	$new_hora_apertura = $_POST["new_hora_apertura"];
	$new_comentarios = str_replace("\'","",$_POST["new_comentarios"]);
	$new_comentarios = str_replace('\"','',$new_comentarios);
	//dir entidad
	$new_dir=str_replace("\'","",$_POST["new_dir"]);
	$new_dir = str_replace('\"','',$new_dir);
	$error = 0;
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	if ($new_distrito == "") { Error("Falta seleccionar el Distrito"); }
	if ($new_entidad == "") { Error("Falta seleccionar la Entidad"); }
	//if ($new_tipo_entidad == "") { Error("Falta seleccionar el Tipo de Entidad"); }
	if ($new_numero == "") { Error("Falta ingresar el Número de Presupuesto"); }
    if ($new_exp =="") { Error("Falta ingresar el expediente de Presupuesto"); }
	if ($new_valor == "") { $new_valor_ok="0"; } else { $new_valor_ok="$new_valor"; }
	if ($new_mant == "") { $new_mant_ok="NULL"; $new_mant_cond=" is "; } else { $new_mant_ok="'$new_mant'"; $new_mant_cond="="; }
	if ($new_forma == "") { $new_forma_ok="NULL"; $new_forma_cond=" is "; } else { $new_forma_ok="'$new_forma'"; $new_forma_cond="="; }
	if ($new_ofertado == "") { $new_ofertado_ok="0"; } else { $new_ofertado_ok=$new_ofertado; }
	if ($new_estimado == "") { $new_estimado_ok="0"; } else { $new_estimado_ok=$new_estimado; }
	if ($new_ganado == "") { $new_ganado_ok="0"; } else { $new_ganado_ok=$new_ganado; }
	if ($new_comentarios == "") { $new_comentarios_ok="NULL"; $new_comentarios_cond=" is "; } else { $new_comentarios_ok="'$new_comentarios'"; $new_comentarios_cond="="; }
	if ($new_dir == "") { Error("Falta ingresar dirección"); }
/*	if ($new_plazo_ent != "") {
		if (FechaOk($new_plazo_ent)) {
			$new_plazo_ent = "'".Fecha_db($new_plazo_ent)."'";
			$new_plazo_ent_cond="=";
		}
		else {
			Error("El formato de la fecha de entrega no es válido");
		}
	}
	else {
		$new_fecha_ent="NULL";
		$new_fecha_ent_cond=" is ";
	}
*/
	$new_plazo_ent = "'$new_plazo_ent'";
	$new_plazo_ent_cond="=";
	if (FechaOk($new_fecha_apertura)) {
		$new_fecha_apertura = Fecha_db($new_fecha_apertura);
	}
	else {
		Error("El formato de la fecha de apertura no es válido");
	}
	if ($new_hora_apertura != "") {
		$hora_arr = explode(":", $new_hora_apertura);
		if (is_numeric($hora_arr[0]))
			$hora_apertura = $hora_arr[0];
		else
			Error("El formato de la hora de apertura no es válido");
		if (is_numeric($hora_arr[1]))
			$hora_apertura .= ":".$hora_arr[1];
		else
			$hora_apertura .= ":00";
	}
	else {
		$hora_apertura = "00:00";
	}
	if (!$error) {
		$msg = "";
		$fecha_modif = date("Y-m-d H:i:s",mktime());
		$db->StartTrans();

		$sql = "insert into licitacion (id_entidad, nro_lic_codificado,";
		$sql .= "fecha_apertura,valor_pliego,mant_oferta_especial,";
		$sql .= "forma_de_pago,id_moneda,observaciones,monto_ofertado,";
		$sql .= "monto_estimado,monto_ganado,id_estado,ultimo_usuario,";
		$sql .= "ultimo_usuario_fecha,plazo_entrega,dir_entidad,exp_lic_codificado,es_presupuesto,";
        $sql .= "es_presupuesto_pyme,id_entidad_pyme)";
		$sql .= "values ($new_entidad,'$new_numero','$new_fecha_apertura $hora_apertura',";
		$sql .= "$new_valor_ok,$new_mant_ok,$new_forma_ok,$new_moneda,";
		$sql .= "$new_comentarios_ok,$new_ofertado_ok,$new_estimado_ok,";
		$sql .= "$new_ganado_ok,10,'".$_ses_user['name']."', '$fecha_modif',";
		$sql .= "$new_plazo_ent, '$new_dir', '$new_exp',1,$es_presupuesto_pyme,$new_entidad_pyme)";
		$result = sql($sql) or $msg .= fin_pagina()."<br>";
		$sql = "select id_licitacion from licitacion where id_entidad=$new_entidad";
		$sql .= " and nro_lic_codificado='$new_numero' and fecha_apertura='$new_fecha_apertura $hora_apertura'";
		$sql .= " and valor_pliego=$new_valor_ok and mant_oferta_especial$new_mant_cond$new_mant_ok";
		$sql .= " and forma_de_pago$new_forma_cond$new_forma_ok and id_moneda=$new_moneda";
		$sql .= " and observaciones$new_comentarios_cond$new_comentarios_ok and monto_ofertado=$new_ofertado_ok";
		$sql .= " and monto_estimado=$new_estimado_ok and monto_ganado=$new_ganado_ok";
		$sql .= " and id_estado=10 and ultimo_usuario='".$_ses_user['name']."'";
		$sql .= " and ultimo_usuario_fecha='$fecha_modif' and plazo_entrega$new_plazo_ent_cond$new_plazo_ent and es_presupuesto=1";
		//$sql .= " and id_tipo_entidad=$new_tipo_entidad";
		$result = sql($sql) or $msg .= fin_pagina()."<br>";
		if ($result) {
			$new_lic_id = $result->fields["id_licitacion"];
			//agregamos una entrada para esta licitacion en la tabla entregar_lic
			$query="insert into entregar_lic(id_licitacion,orden_subida,vence,mostrar,oferta_subida)values($new_lic_id,0,'01/01/2003',1,0)";
			sql($query) or fin_pagina();
			//agregamos una entrada para esta licitacion en la tabla candado
			$query="insert into candado(id_licitacion,estado)values($new_lic_id,0)";
			sql($query) or fin_pagina();
		}
		//guardo la dirección de la entidad por defecto en la tabla entidad
		if ($_POST["guardar_dir"]=='SI'){
            if ($es_pymes)
	            $query_ent="update entidad_pymes set direccion='$new_dir' where id_entidad_pyme=$new_entidad_pyme";
                else
                $query_ent="update entidad set direccion='$new_dir' where id_entidad=$new_entidad";

	    $rs = sql($query_ent) or fin_pagina();
		}

		$db->CompleteTrans();
		if ($msg) {
			Error("No se pudo agregar el nuevo Presupuesto.<br>$msg");
		}
		else {
			$link = encode_link($html_root."/presupuestos_view.php", array("cmd1"=>"detalle","ID"=>$new_lic_id));
			Aviso("Los datos se cargaron correctamente<br>ID Asignado al presupuesto: <a href='$link' target=_top><font size=4 color=#0000ff>$new_lic_id</font></a><br>");
			unset($_POST);
		}
	}
	echo "</b></td></tr></table>\n";
   // die($query_ent);
	form_new_lic();
}
else {
	form_new_lic();
	}

function form_new_lic() {
	global $html_header, $db, $bgcolor2, $bgcolor3, $html_root,$es_pymes;
	echo $html_header;
	cargar_calendario();
	$new_distrito = $_POST["new_distrito"];
	$new_entidad = $_POST["new_entidad"];
	//$new_tipo_entidad = $_POST["new_tipo_entidad"];
	$new_mant = $_POST["new_mant"];
	$new_forma = $_POST["new_forma"];
	$new_plazo_ent = $_POST["new_plazo_ent"];
	$new_numero = $_POST["new_numero"];
	$new_exp= $_POST["new_exp"];
	$new_valor = $_POST["new_valor"];
	$new_moneda = $_POST["new_moneda"];
	$new_ofertado = $_POST["new_ofertado"];
	$new_estimado = $_POST["new_estimado"];
	$new_ganado = $_POST["new_ganado"];
	$new_fecha_apertura = $_POST["new_fecha_apertura"];
	$new_hora_apertura = $_POST["new_hora_apertura"];
	$new_comentarios = $_POST["new_comentarios"];
	$new_dir=$_POST["dir_entidad"];
	echo "<SCRIPT language='JavaScript' src='../licitaciones/funcion.js'></SCRIPT>";
    echo "<SCRIPT language='JavaScript'>";
    ?>
    function control_datos(){


       if(document.all.new_dir.value.indexOf('"')!=-1)
           {
            alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección de la entidad');
            return false;
          }
    return true;
    }
    <?
    echo "</SCRIPT>";
    $link=encode_link("presupuestos_new.php",array("es_pyme"=>$es_pymes));

	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<form action='$link' method=post>\n";
    echo "<input type=hidden name=es_pymes value=$es_pymes>";
    ?>

	<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_lic_nueva.htm" ?>', 'CARGAR NUEVA LICITACION')" >
    </div>
<?

if($es_pymes) $pymes=" PYMES";
         else $pymes="";
//hidden dir_cambio para mantener la direccion ingresada cuando se cambia el distrito
   echo "<input name='dir_cambio' type='hidden' value='0'>";
    echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3>
	<b>Nuevo Presupuesto $pymes </b></td></tr>";
    echo "<tr>";
     echo "<td colspan=2>";
     echo "<font color=red>";
      echo "<b>No ingresar datos con comillas dobles (\"\")</b>  ";
     echo "</font>";
     echo "</td>";
    echo "</tr>";
	echo "<tr>\n";
    echo "<td align=left colspan=2>";

    /*

	echo "<table width=100%><tr>";
	echo "<td align=right width=20%><b>Distrito:</b></td>\n";
	echo "<td align=left><select name=new_distrito  onchange='document.all.dir_cambio.value=1;document.forms[0].submit()'>\n";

	if (!$new_distrito) echo "<option value=''>\n";
	$result1 = $db->Execute("select id_distrito,nombre from distrito order by nombre") or die($db->ErrorMsg());
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_distrito"]."'";
		if ($result1->fields["id_distrito"] == $new_distrito) echo " selected";
		echo ">".$result1->fields["nombre"]."\n";
		$result1->MoveNext();
	}
	echo "</select></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Entidad:</b></td>\n";
	echo "<td align=left><select name=new_entidad onchange='document.forms[0].submit()'>\n";
	if ($new_distrito) {
		$result1 = $db->Execute("select id_entidad,nombre from entidad where id_distrito=$new_distrito order by nombre") or die($db->ErrorMsg());
		echo "<option value=''>Seleccione la Entidad\n";
		while (!$result1->EOF) {
			echo "<option value='".$result1->fields["id_entidad"]."'";
			if ($result1->fields["id_entidad"] == $new_entidad) echo " selected";
			echo ">".$result1->fields["nombre"]."\n";
			$result1->MoveNext();
		}
	}

	else { echo "<option value=''>Seleccione el Distrito\n"; }
	echo "</select> </td>\n";
	echo "</tr>";
	$dir='';
	if (($new_dir != "") and ($_POST["new_aceptar"] != "") and ($new_entidad!=""))
	$dir=$new_dir;

		else{
	if ($new_entidad){
	$query_dir="select direccion from entidad where id_entidad=$new_entidad ";
	$res = $db->Execute($query_dir) or die($db->ErrorMsg());
	if ($res->RecordCount()==1) {
		$dir= $res->fields['direccion'];
	}
	}
	}



	echo "<td align='right'><b>Dirección:</b> </td> <td> <input type=text name=dir_entidad value='$dir' size=60> <b> &nbsp;&nbsp;&nbsp;&nbsp;Guardar por Defecto </b><input name='guardar_dir' type='checkbox' value='SI' title='Si ud chequea esta casilla la dirección para la entidad se guardará por defecto'> </td>";
	echo "<tr>";
	if ($new_entidad){
	echo "<td align=right><b>Tipo de Entidad:</b></td>\n";
	echo "<td>";
	//selecciona el tipo de entidad

	$query_ent="select tipo_entidad.nombre from entidad join tipo_entidad on entidad.id_tipo_entidad=tipo_entidad.id_tipo_entidad where id_entidad=$new_entidad";
	$res = $db->Execute($query_ent) or die($db->ErrorMsg());
	if ($res->RecordCount()==1)
	echo $res->fields['nombre'];
	// echo "hola";
	echo"</td>";
	}


	echo "</tr> </table>";
    */



       echo "<table width=100% align=center>";
          echo "<tr><td align=left width=15%>";
               echo "<b>Distrito:</b>\n";
               echo "</td><td>";
	           echo "<select name=new_distrito>\n";
	              if (!$mod_distrito) echo "<option value=''>\n";
	              $result1 = sql("select id_distrito,nombre from distrito order by nombre") or die;
	              while (!$result1->EOF) {
		               echo "<option value='".$result1->fields["id_distrito"]."'";
		               if ($result1->fields["id_distrito"] == $mod_distrito) echo " selected";
		               echo ">".$result1->fields["nombre"];
                       echo "</option>\n";
		               $result1->MoveNext();
	              }
	              echo "</select>\n";

          echo "</td></tr>";
          echo "<input type=hidden name=new_id_tipo_entidad value='$mod_id_tipo_entidad'>";
          $pymes="pymes";
          $link=encode_link('../modulo_clientes/nuevo_cliente.php',array('pagina'=>'presupuestos_new','es_pymes'=>$es_pymes));
          $onclick="window.open('$link'); ";
          echo "<tr><td>";
          echo "<b>Elegir Cliente:</b>";
          echo "</td><td>";
          echo "<input type=button name=elegir_cliente Value='Elegir Cliente' onclick=\"$onclick\">";
          echo "</td></tr>";
           echo "<tr><td>";
            echo "<b>Entidad:</b>\n";
            echo "</td><td>";
            echo "<input type=hidden name=new_entidad value='$new_entidad'>";
            echo "<input type=text name=new_nombre_cliente value='$new_nombre_cliente' readonly size='90'>";
           echo "</td></tr>";
	      if ($new_entidad){
		             //selecciona la dir por defecto de la entidad
	                $query_dird="select direccion from entidad where id_entidad=$new_entidad ";
	                $r = sql($query_dird) or die;
	                if ($r->RecordCount()==1) {
		                      $dir_defecto= $r->fields['direccion'];
	                 }
	               //selecciono la direccion de la entidad guardada en la licitación
	               $query_dir="select dir_entidad from licitacion where id_licitacion=$ID ";
	               $res = sql($query_dir) or die;
	               if ($res->RecordCount()==1) {
		                $mod_dir= $res->fields['dir_entidad'];
	               }
	            }
        	//}
	  $titulo='La dirección por defecto es:'.$dir_defecto;
	  if ($_POST['dir_cambio']==1) {
		   $mod_dir="";
		   $titulo="";
  	       //$dir_defecto="";
	       }
	       elseif ($_POST['dir_cambio']==2) $mod_dir=$dir_defecto;

    echo "<tr><td>";
	 echo "<b>Dirección:</b> ";
     echo "</td><td>";
     echo"<input type=text name=new_dir value='$new_dir' size=60> <b> &nbsp;&nbsp;&nbsp;&nbsp;Guardar por Defecto </b><input name='guardar_dir' type='checkbox' value='SI' title='".$titulo."'><br>\n";
     echo"</td></tr>";
    echo "<tr><td>";
	echo "<b>Tipo de Entidad:</b>";
    echo "</td><td>";
    echo " <b>$new_tipo_entidad</b>\n";
//    echo "</td></tr></table>";




    echo "</td></tr>";
    echo "</table>";
    //fin de parte  de datos de la entidad





    echo "</td>";
	echo "</tr><tr>\n";
	echo "<td align=left>";
	echo "<table width=100%><tr>";
	echo "<td align=right width=50% nowrap><b>Mantenimiento de oferta:</b></td>\n";
	echo "<td align=left width=50%>";
	echo "<select name=new_mant OnChange='beginEditing(this);'>";
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
	foreach ($array_mant as $key => $val) {
		echo "<option value='$val'";
		if ($key == 6) echo " selected";
		echo ">$val";
	}
	echo "<option id=editable>Edite aquí";
	echo "</select></td>\n";
	//"<input type=text name=new_mant value='$new_mant' size=20 maxlength=100>";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Forma de pago:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=new_forma OnChange='beginEditing(this);'>";
	echo "<option></option>";
	$array_forma = array("30 dias a partir de la recepcion definitiva",
                        "Contado contra entrega",
						"10 días de la fecha de entrega",
						"10 días de la recepción de los bienes",
						"15 días de la fecha de entrega",
						"15 días de la recepción de los bienes",
						"30 días de la fecha de entrega",
						"30 días de la recepción de los bienes",
						"60 días de la fecha de entrega",
						"60 días de la recepción de los bienes"
                        );
	foreach ($array_forma as $key => $val) {
		echo "<option value='$val'";
		if ($key == 0) echo " selected";
		echo ">$val";
	}
	echo "<option id=editable>Edite aquí";
	echo "</select></td>\n";
	//"<input type=text name=new_forma value='$new_forma' size=20 maclength=100></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right nowrap><b>Plazo de entrega:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=new_plazo_ent OnChange='beginEditing(this);'>";
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
	foreach ($array_plazo as $key => $val) {
		echo "<option value='$val'";
		if ($key == 0) echo " selected";
		echo ">$val";
	}
	echo "<option id=editable>Edite aquí";
	echo "</select></td>\n";
//	echo "<input type=text name=new_plazo_ent value='$new_plazo_ent' size=10 maxlength=10>";
//	echo link_calendario("new_plazo_ent");
	echo "</tr></table>\n";
	echo "</td><td align=center valign=top>";
	echo "<table width=100%><tr>";
	echo "<td align=right><b>Número:</b></td>\n";
	echo "<td align=left width=10%><input type=text name=new_numero value='$new_numero'></td>\n";
	echo "</tr> <tr>";
	echo "<td align=right> <b> Expediente</b> </td>";
	echo "<td align=left><input type=text name=new_exp value='$new_exp'></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Valor del pliego:</b></td>\n";
	echo "<td align=left><input type=text name=new_valor value='$new_valor' size=10 maxlength=10 onkeypress = \"return filtrar_teclas(event,'0123456789.');\"></td>\n";
	echo "</tr>";
	echo "</table>\n";
	echo "</td></tr><tr>\n";
	echo "<td align=center valign=top width=50%>";
	echo "<table width=100%><tr>";
	echo "<td align=right width=10%><b>Moneda:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=new_moneda>\n";
	$result1 = sql("select id_moneda,nombre from moneda") or fin_pagina();
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_moneda"]."'";
		if ($result1->fields["id_moneda"] == $new_moneda) echo " selected";
		echo ">".$result1->fields["nombre"]."\n";
		$result1->MoveNext();
	}
	echo "</select>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Ofertado:</b></td>\n";
	echo "<td align=left><input type=text name=new_ofertado value='$new_ofertado' size=10 maxlength=10 onkeypress = \"return filtrar_teclas(event,'0123456789.');\"></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Estimado:</b></td>\n";
	echo "<td align=left><input type=text name=new_estimado value='$new_estimado' size=10 maxlength=10 onkeypress = \"return filtrar_teclas(event,'0123456789.');\"></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Ganado:</b></td>\n";
	echo "<td align=left><input type=text name=new_ganado value='$new_ganado' size=10 maxlength=10 onkeypress = \"return filtrar_teclas(event,'0123456789.');\"></td>\n";
	echo "</tr></table></td>";
	echo "<td align=right valign=top>";
	echo "<table width=100%><tr><td align=right><b>Apertura:</b></td>\n";
	echo "<td align=left width=10%><input type=text name=new_fecha_apertura value='$new_fecha_apertura' size=10 maxlength=10>";
	echo link_calendario("new_fecha_apertura");
	echo "</td>";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Hora:</b></td>";
	echo "<td align=left><input type=text name=new_hora_apertura value='$new_hora_apertura' size=10 maxlength=5 onkeypress = \"return filtrar_teclas(event,'0123456789:');\"></td>";
	echo "</tr></table></td>\n";
	echo "</td></tr><tr>\n";
	echo "<td align=left valign=top colspan=4>";
	echo "<table width=100%><tr>";
	echo "<td valign=top align=right><b>Comentarios/Seguimiento:</b></td>";
	echo "<td align=left><textarea name='new_comentarios' cols=70 rows=10>$new_comentarios</textarea></td>\n";
	echo "</tr></table></td>";
	echo "</tr><tr>\n";
	echo "<td style=\"border:$bgcolor3;\" colspan=2 align=center><br>\n";
	echo "<input type=submit name=new_aceptar value='Aceptar' onclick='return control_datos();'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=reset name=new_cancelar value='Cancelar'><br><br>\n";
	echo "</td>";
	echo "</tr>\n";
	echo "</table><br>\n";
}
?>