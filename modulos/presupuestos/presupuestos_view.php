<?
/*
AUTOR: MAC

MODIFICADO POR:
$Author: mari $
$Revision: 1.32 $
$Date: 2007/01/05 13:37:30 $

*/
//print_r($_POST);
require_once("../../config.php");
require_once("../general/funciones_contactos.php");
require_once("../stock/funciones.php");
//incluyo el modulo de tips
//global que sirve para saber si se ha seleccionado proximas o no (presentadas o historial).

variables_form_busqueda("presupuestos",array("estado"=>""));

$es_pymes=$parametros["es_pymes"] or $es_pymes=$_POST["es_pymes"];
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

if ($cmd == "") {
	$cmd="pcg";
	$_ses_presupuestos['cmd']=$cmd;
	phpss_svars_set("_ses_presupuestos",$_ses_presupuestos);
}




function traspaso_entidad($ID){
global $db,$ID;

$db->starttrans();

    $sql="select id_entidad_pyme from licitacion
           where id_licitacion=$ID";
    $res=sql($sql) or fin_pagina();
    $id_entidad_pyme=$res->fields["id_entidad_pyme"];


    $sql="select id_entidad from entidad where id_empresa=$id_entidad_pyme";
    $res=sql($sql) or fin_pagina();

    if ($res->recordcount()==0)
             {
             //traigo los datos de la entidad
             $sql="select * from entidad_pymes where id_entidad_pyme=$id_entidad_pyme";
             $datos_entidad=sql($sql) or fin_pagina();

             $id_tipo_entidad=$datos_entidad->fields["id_tipo_entidad"];
             $id_distrito=$datos_entidad->fields["id_distrito"];
             $id_iva=$datos_entidad->fields["id_iva"];


             if (!$id_tipo_entidad) $id_tipo_entidad="NULL";
             if (!$id_iva) $id_iva="NULL";
             if (!$id_distrito) $id_distrito="NULL";

             $nombre=$datos_entidad->fields["nombre"];
             $telefono=$datos_entidad->fields["telefono"];
             $fax=$datos_entidad->fields["fax"];
             $direccion=$datos_entidad->fields["direccion"];
             $codigo_postal=$datos_entidad->fields["codigo_postal"];
             $localidad=$datos_entidad->fields["localidad"];
             $observaciones=$datos_entidad->fields["observaciones"];
             $mail=$datos_entidad->fields["mail"];
             $perfil=$datos_entidad->fields["perfil"];
             $cuit=$datos_entidad->fields["cuit"];
             $iib=$datos_entidad->fields["iib"];


             $sql="select nextval('entidad_id_entidad_seq') as id_entidad";
             $res=sql($sql) or fin_pagina();
             $id_entidad=$res->fields["id_entidad"];

             $campos="id_entidad,id_distrito,id_iva,nombre,telefono,fax,direccion,codigo_postal,";
             $campos.=" localidad,observaciones,mail,perfil,cuit,iib,id_empresa";

             $values=" $id_entidad,$id_distrito,$id_iva,'$nombre','$telefono','$fax','$direccion','$codigo_postal',";
             $values.=" '$localidad','$observaciones','$mail','$perfil','$cuit','$iib',$id_entidad_pyme";
             $sql="insert into entidad ($campos) values ($values)";
             sql($sql) or fin_pagina();


             $sql="select * from contactos_generales_pymes where id_entidad_pyme=$id_entidad_pyme";
             $contactos=sql($sql) or fin_pagina();
             for($i=0;$i<$contactos->recordcount();$i++){

                $nombre=$contactos->fields["nombre"];
                $tel=$contactos->fields["tel"];
                $direccion=$contactos->fields["direccion"];
                $provincia=$contactos->fields["provincia"];
                $localidad=$contactos->fields["localidad"];
                $cod_postal=$contactos->fields["cod_postal"];
                $mail=$contactos->fields["mail"];
                $fax=$contactos->fields["fax"];
                $icq=$contactos->fields["icq"];
                $observaciones=$contactos->fields["observaciones"];



                $sql="select nextval('contactos_generales_id_contacto_general_seq') as id_contacto_general";
                $res=sql($sql) or fin_pagina();
                $id_contacto_general=$res->fields["id_contacto_general"];
                $campos="id_contacto_general,nombre,direccion,provincia,localidad,cod_postal,mail,fax,icq,observaciones";
                $values="$id_contacto_general,'$nombre','$direccion','$provincia','$localidad','$cod_postal','$mail','$mail','$icq','$observaciones'";
                $sql="insert into contactos_generales ($campos) values ($values)";
                sql($sql) or fin_pagina();


                $sql="insert into modulos_contacto (id_modulo,id_contacto_general) values (1,$id_contacto_general)";
                sql($sql) or fin_pagina();
                $sql="insert into relaciones_contacto (id_contacto_general,entidad) values ($id_contacto_general,$id_entidad)";
                sql($sql) or fin_pagina();
                $contactos->movenext();
             }//del for
             $sql="update licitacion set id_entidad=$id_entidad,es_presupuesto_pyme=0 where id_licitacion=$ID";
             sql($sql) or fin_pagina();

             }//del if que controla que no esta la entidad
             else {
                  $id_entidad=$res->fields["id_entidad"];
                  $sql="update licitacion set id_entidad=$id_entidad,es_presupuesto_pyme=0 where id_licitacion=$ID";
                  sql($sql) or fin_pagina();
                  }



 $db->completetrans();

 //die("llega");
}//de la funcion traspaso entidad


//devuelve el dia de la semana que le correponde d/m/a
// dom=0,lun=1...
function calcula_numero_dia_semana($dia,$mes,$ano){
	$nrodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
	return $nrodiasemana;
}

$datos_barra = array(
					array(
						"descripcion"	=> "PPP",
						"cmd"			=> "ppp",
						),
					array(
						"descripcion"	=> "PCG",
						"cmd"			=> "pcg",
                        ),
					array(
						"descripcion"	=> "Todos",
						"cmd"			=> "todas"
						)
				 );

if ($_POST["det_del"]) {
	echo $html_header;
	$ID = $_POST["ID"];
	$msg = "";
	$sql = "update licitacion set borrada='t' where id_licitacion=$ID";
	$result = sql($sql) or $msg = 1;
	if ($msg) {
		$msg = "No se pudo enviar a la papelera el Presupuesto número $ID.";
	}
	else {
		$msg = "El Presupuesto número $ID ha sido enviada a la papelera";
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
	$sql = "update licitacion set borrada='f' where id_licitacion=$ID";
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
		 $query_candado="update candado set estado=1, fecha='$fecha_candado',usuario='".$_ses_user['login']."' where id_licitacion=$ID";

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
	     $query_candado="update candado set estado=0, fecha='$fecha_candado',usuario='".$_ses_user['login']."' where id_licitacion=$ID";

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
 $ID = $parametros["ID"];
 include("../licitaciones/resultados_coradir.php");
 echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
 echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
 echo "<font size=3>$msg</font>";
 echo "</b></td></tr>";
 echo "<tr><td>&nbsp;</td></tr>";
 echo "</table><br>\n";
 detalle($ID);
}
elseif ($_POST["det_delfile"]) {
	$ID = $_POST["ID"];
	genera_det_delfile($ID);
	detalle($ID);
}

///////////////////////////////////////////////////////////////////////
elseif ($_POST["guardar_comunicar"]) {
	echo $html_header;
	$ID=$_POST["ID"] or $ID=$parametros["ID"];
	$comentario = $_POST["comentario_nuevo"];
    $sql=nuevo_comentario($ID,"COMUNICAR_CLIENTE",$comentario);
	sql($sql) or fin_pagina();
	detalle($ID);
}
///////////////////////////////////////////////////////////////////////

elseif ($_POST["mod_edit"]) {

	$ID = $_POST["ID"];
	$mod_entidad = $_POST["mod_entidad"];
    $mod_nombre_cliente = $_POST["mod_nombre_cliente"];
	$error = 0;
	echo $html_header;
	echo "<br><table width=50% border=0 cellspacing=1 cellpadding=5 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td colspan=3 align=center bgcolor=$bgcolor2><b>";
	$mod_distrito = $_POST["mod_distrito"];
	$mod_entidad = $_POST["mod_entidad"];
	$mod_dir=str_replace("\'","",$_POST["mod_dir"]);
	$mod_dir=str_replace('\"','',$mod_dir);
//	$mod_tipo_entidad = $_POST["mod_tipo_entidad"];
	$mod_numero = $_POST["mod_numero"];
	$mod_exp=$_POST["mod_exp"];
	$mod_valor = $_POST["mod_valor"];
	$mod_mant = $_POST["mod_mant"];
	$mod_forma = $_POST["mod_forma"];
	$mod_ofertado = $_POST["mod_ofertado"];
	$mod_estimado = $_POST["mod_estimado"];
	$mod_ganado = $_POST["mod_ganado"];
	$mod_comentarios = str_replace("\'","",$_POST["mod_comentarios"]);
	$mod_comentarios = str_replace('\"','',$mod_comentarios);
	$mod_fecha_ent = $_POST["mod_fecha_ent"];
	$mod_plazo_ent = $_POST["mod_plazo_ent"];
	$mod_fecha_apertura = $_POST["mod_fecha_apertura"];
	$mod_hora_apertura = $_POST["mod_hora_apertura"];
	$mod_estado = $_POST["mod_estado"];
	$mod_moneda = $_POST["mod_moneda"];
	$file_id = $_POST["file_id"];
//	$mod_ = $_POST["mod_"];
	if ($mod_distrito == "") { echo "<font size=2 color=#ff0000>Falta seleccionar el Distrito</font><br><br>"; $error = 1; }
		if ($mod_entidad == "") { echo "<font size=2 color=#ff0000>Falta seleccionar la Entidad</font><br><br>"; $error = 1; }
		if ($mod_dir == "") { echo "<font size=2 color=#ff0000>Falta ingresar la direción de la Entidad</font><br><br>"; $error = 1; }
		//		if ($mod_tipo_entidad == "") { echo "<font size=2 color=#ff0000>Falta seleccionar el Tipo de Entidad</font><br><br>"; $error = 1; }
		if ($mod_numero == "") { echo "<font size=2 color=#ff0000>Falta ingresar el Número de Presupuesto</font><br><br>"; $error = 1; }
		if ($mod_exp == "") { echo "<font size=2 color=#ff0000>Falta ingresar el Número de expediente de Presupuesto</font><br><br>"; $error = 1; }
		if ($mod_valor == "") { $mod_valor_ok="NULL"; } else { $mod_valor_ok="'$mod_valor'"; }
		if ($mod_mant == "") { $mod_mant_ok="NULL"; } else { $mod_mant_ok="'$mod_mant'"; }
		if ($mod_forma == "") { $mod_forma_ok="NULL"; } else { $mod_forma_ok="'$mod_forma'"; }
		if ($mod_ofertado == "") { $mod_ofertado_ok="NULL"; } else { $mod_ofertado_ok=$mod_ofertado; }
		if ($mod_estimado == "") { $mod_estimado_ok="NULL"; } else { $mod_estimado_ok=$mod_estimado; }
		if ($mod_ganado == "") { $mod_ganado_ok="NULL"; } else { $mod_ganado_ok=$mod_ganado; }
		if ($mod_comentarios == "") { $mod_comentarios_ok="NULL"; } else { $mod_comentarios_ok="'$mod_comentarios'"; }
		if ($mod_fecha_ent != "") {
			if (FechaOk($mod_fecha_ent)) {
				$mod_fecha_ent = "'".Fecha_db($mod_fecha_ent)."'";
			}
			else {
				Error("El formato de la fecha de entrega no es válido");
			}
		}
		else {
			$mod_fecha_ent="NULL";
		}
		if ($mod_hora_apertura != "") {
			$hora_arr = explode(":", $mod_hora_apertura);
			if (is_numeric($hora_arr[0]))
				$hora_apertura = $hora_arr[0];
			else
				Error("El formato de la hora de apertura no es válido");
			if (is_numeric($hora_arr[1]))
				$hora_apertura .= ":".$hora_arr[1];
			else
				$hora_apertura .= ":00";
//				Error("El formato de la hora de apertura no es válido");
		}
		else {
			$hora_apertura = "00:00";
		}
		if (FechaOk($mod_fecha_apertura)) {
			$mod_fecha_apertura = Fecha_db($mod_fecha_apertura);
		}
		else {
			Error("El formato de la fecha de apertura no es válido");
		}
		if (!$error) {

			$sql_array = array();

            //echo "Mod Entidad:$mod_entidad";
			$sql = "update licitacion set ";
			$sql .= "plazo_entrega='$mod_plazo_ent',";
			$sql .= "fecha_entrega=$mod_fecha_ent,";
			$sql .= "id_estado=$mod_estado,";
			$sql .= "fecha_apertura='$mod_fecha_apertura $hora_apertura',";
			$sql .= "mant_oferta_especial=$mod_mant_ok,";
			$sql .= "nro_lic_codificado='$mod_numero',";
			$sql .= "dir_entidad='$mod_dir',";
			$sql .= "exp_lic_codificado='$mod_exp',";
//			$sql .= "id_tipo_entidad='$mod_tipo_entidad',";
			$sql .= "forma_de_pago=$mod_forma_ok,";
			$sql .= "valor_pliego=$mod_valor_ok,";
			$sql .= "observaciones=$mod_comentarios_ok,";
			$sql .= "ultimo_usuario='".$_ses_user['name']."',";
			$sql .= "ultimo_usuario_fecha='".date("Y-m-d H:i:s",mktime())."',";
			$sql .= "id_moneda='$mod_moneda',";
			$sql .= "monto_ofertado=$mod_ofertado_ok,";
			$sql .= "monto_estimado=$mod_estimado_ok,";
			$sql .= "monto_ganado=$mod_ganado_ok ";
            if ($es_pymes)
                          $sql.= ",id_entidad_pyme=$mod_entidad ";
                          else
			              $sql .= ",id_entidad=$mod_entidad ";

			$sql .= "where id_licitacion=$ID";
			$sql_array[] = $sql;
			
   //si el estado de la licitacion es Entregada, y antes era otro, se sacan del stock en produccion, los productos
   //que se fueron agrenado al mismo a medida que se entregaban los productos desde el modulo Orden de Compras
  
   if ($_POST["estado_anterior"]!=$_POST['mod_estado'])

   {
   	 //revisamos cual es el id del estado "Entragda"
   	 $query="select id_estado from estado where nombre='Entregada'";
   	 $est_ent=sql($query,"<br>Error al traer el id del estado Entregada<br>") or fin_pagina();  

   	 if($_POST['mod_estado']==$est_ent->fields["id_estado"]) {
         $sql="update cobranzas set licitacion_entregada=1 
               where cobranzas.id_licitacion=$ID and cobranzas.estado='PENDIENTE'";
      sql($sql) or fin_pagina();    
      desc_en_produccion($ID);
    }

   }//de if ($_POST['radio_estado']==1 && $_POST["estado_anterior"]!=$_POST['mod_estado'])

			

			//actualizo la direccion enla tabla entidad si esta seleccionado el checkbox
			if ($_POST["guardar_dir"]=='SI'){
                if ($es_pymes)
                         $query_ent="update entidad_pymes set direccion='$mod_dir' where id_entidad_pyme=$mod_entidad";
                         else
                         $query_ent="update entidad set direccion='$mod_dir' where id_entidad=$mod_entidad";


	        $sql_array[] = $query_ent;
		     }

		     if (is_array($file_id) and count($file_id) > 0) {
              	while (list($mod_file_id, $mod_file_imp) = each($file_id)) {
					//actualizamos la tabla de entregar_lic para indicar si el archivo
					//esta listo para imprimir o no

					if($mod_file_imp=='t')
					 $of_imp=1;
					else
					 $of_imp=0;
					$query="select nombre from archivos where idarchivo=$mod_file_id";
					$nombre_arch=sql($query,"<br>Error al traer el nombre del archivo") or fin_pagina();
					$query="update entregar_lic set oferta_lista_imprimir=$of_imp where id_licitacion=$ID and archivo_oferta='".$nombre_arch->fields['nombre']."'";
					$sql_array[] = $query;


					$sql = "update archivos set ";
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
	$sql = "SELECT licitacion.*,";
    if ($es_pymes)
           $sql.=  "entidad.id_entidad_pyme as id_entidad,";
           else
           $sql.=  "entidad.id_entidad,";

    $sql .= " entidad.id_tipo_entidad,entidad.nombre as nombre_entidad,";
	$sql .= "entidad.id_distrito,tipo_entidad.nombre as nombre_tipo_entidad ";
	$sql .= "FROM licitacion ";

    if ($es_pymes)
            $sql .= "LEFT JOIN entidad_pymes as entidad USING (id_entidad_pyme)";
            else
	        $sql .= "LEFT JOIN entidad USING (id_entidad)";
	$sql .= "LEFT JOIN tipo_entidad ";
	$sql .= "USING (id_tipo_entidad) ";
	$sql .= "WHERE licitacion.id_licitacion=$ID";
	$result = sql($sql) or die;
	if (!$_POST["mod_distrito"]) {
		$mod_distrito=$result->fields["id_distrito"];
		$mod_entidad=$result->fields["id_entidad"];
        $mod_id_tipo_entidad=$result->fields["id_tipo_entidad"];
        $mod_nombre_cliente=$result->fields["nombre_entidad"];
		$mod_dir=$result->fields["dir_entidad"];
		$mod_tipo_entidad=$result->fields["nombre_tipo_entidad"];
		$mod_numero=$result->fields["nro_lic_codificado"];
		$mod_exp=$result->fields["exp_lic_codificado"];
		$mod_fecha_apertura=Fecha($result->fields["fecha_apertura"]);
		$mod_hora_apertura=substr($result->fields["fecha_apertura"],11,5);
		$mod_mant=$result->fields["mant_oferta_especial"];
		$mod_forma=$result->fields["forma_de_pago"];
		$mod_valor=$result->fields["valor_pliego"];
		$mod_comentarios=$result->fields["observaciones"];
		$mod_moneda=$result->fields["id_moneda"];
		$mod_ofertado=$result->fields["monto_ofertado"];
		$mod_estimado=$result->fields["monto_estimado"];
		$mod_ganado=$result->fields["monto_ganado"];
		$mod_estado=$result->fields["id_estado"];
		$mod_fecha_ent=Fecha($result->fields["fecha_entrega"]);
		$mod_plazo_ent=$result->fields["plazo_entrega"];
	}
	else {
		$mod_distrito = $_POST["mod_distrito"];
        $mod_id_tipo_entidad=$_POST["mod_id_tipo_entidad"];
		$mod_entidad = $_POST["mod_entidad"];
        $mod_nombre_cliente = $_POST["mod_nombre_cliente"];
		$mod_dir= $_POST["mod_dir"];
		$mod_numero = $_POST["mod_numero"];
		$mod_exp = $_POST["mod_exp"];
		$mod_valor = $_POST["mod_valor"];
		$mod_mant = $_POST["mod_mant"];
		$mod_forma = $_POST["mod_forma"];
		$mod_ofertado = $_POST["mod_ofertado"];
		$mod_estimado = $_POST["mod_estimado"];
		$mod_ganado = $_POST["mod_ganado"];
		$mod_comentarios = $_POST["mod_comentarios"];
		$mod_fecha_ent = $_POST["mod_fecha_ent"];
		$mod_plazo_ent = $_POST["mod_plazo_ent"];
		$mod_fecha_apertura = $_POST["mod_fecha_apertura"];
		$mod_hora_apertura = $_POST["mod_hora_apertura"];
		$mod_estado = $_POST["mod_estado"];
		$mod_moneda = $_POST["mod_moneda"];
		$file_id = $_POST["file_id"];
		if ($mod_entidad){
		$sql_tipo = "SELECT tipo_entidad.nombre as nombre_tipo_entidad ";
		$sql_tipo .= "FROM entidad LEFT JOIN tipo_entidad USING (id_tipo_entidad) ";
		$sql_tipo .= "WHERE entidad.id_entidad=$mod_entidad";
		$result_tipo = sql($sql_tipo) or die;
		$mod_tipo_entidad = $result_tipo->fields["nombre_tipo_entidad"];
		}
	}
	if (!FechaOk($mod_fecha_apertura)) {
		Error("El formato de la fecha de apertura no es válido");
	}
	if ($mod_fecha_ent != "") {
		if (!FechaOk($mod_fecha_ent)) {
			Error("El formato de la fecha de entrega no es válido");
		}
	}


	cargar_calendario();
    generar_barra_nav($datos_barra);
    if ($es_pymes) $pymes=" PYMES";
             else  $pymes= " ";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
    echo "<input type=hidden name=es_pymes value=$es_pymes>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Modificación de datos del Presupuesto $pymes</b></td></tr>";
	echo "<tr>\n";
	echo "<td align=center width=50%><font size=4><b>ID:</b> $ID</font></td>\n";
	echo "<td align=right width=50%>";
	echo "<table width=100%><tr><td align=right><b>Apertura:</b></td>\n";
	echo "<td align=left width=10%><input type=text name=mod_fecha_apertura value='$mod_fecha_apertura' size=10 maxlength=10>";
	echo link_calendario("mod_fecha_apertura");
	echo "</tr><tr>\n";
	echo "<td align=right><b>Hora:</b></td>";
	echo "<td align=left><input type=text name=mod_hora_apertura value='$mod_hora_apertura' size=10 maxlength=5></td>";
	echo "</tr></table></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=left colspan=2>";
       echo "<table width=100% align=center>";
          echo "<tr><td align=left width=15%>";
               echo "<b>Distrito:</b>\n";
               echo "</td><td>";
	           echo "<select name=mod_distrito onchange='document.all.dir_cambio.value=1;document.forms[0].submit()'>\n";
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
          echo "<input type=hidden name=mod_id_tipo_entidad value='$mod_id_tipo_entidad'>";
          $link=encode_link('../modulo_clientes/nuevo_cliente.php',array('pagina'=>'presupuestos','es_pymes'=>$es_pymes));
          $onclick="window.open('$link'); ";
          echo "<tr><td>";
          echo "<b>Elegir Cliente:</b>";
          echo "</td><td>";
          echo "<input type=button name=elegir_cliente Value='Elegir Cliente' onclick=\"$onclick\">";
          echo "</td></tr>";
           echo "<tr><td>";
            echo "<b>Entidad:</b>\n";
            echo "</td><td>";
            echo "<input type=hidden name=mod_entidad value='$mod_entidad'>";
            echo "<input type=text name=mod_nombre_cliente value='$mod_nombre_cliente' readonly size='90'>";
           /*
	        echo "<select name=mod_entidad onchange='document.all.dir_cambio.value=2;document.forms[0].submit()'>\n";
	         if ($mod_distrito) {
		            $result1 = sql("select id_entidad,nombre from entidad where id_distrito=$mod_distrito order by nombre") or die;
		            echo "<option value=''>Seleccione la Entidad\n";
		            while (!$result1->EOF) {
			            echo "<option value='".$result1->fields["id_entidad"]."'";
			            if ($result1->fields["id_entidad"] == $mod_entidad) echo " selected";
			            echo ">".$result1->fields["nombre"]."\n";
                        echo "</option>";
			            $result1->MoveNext();
		            }//del while
	             }
	             else {
                     echo "<option value=''>Seleccione el Distrito\n</option>";
                   }
	      echo "</select>\n";
          */
          echo "</td></tr>";
	      if ($mod_entidad){
		             //selecciona la dir por defecto de la entidad
	                $query_dird="select direccion from entidad where id_entidad=$mod_entidad ";
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
     echo"<input type=text name=mod_dir value='$mod_dir' size=60> <b> &nbsp;&nbsp;&nbsp;&nbsp;Guardar por Defecto </b><input name='guardar_dir' type='checkbox' value='SI' title='".$titulo."'><br>\n";
     echo"</td></tr>";
    echo "<tr><td>";
	echo "<b>Tipo de Entidad:</b>";
    echo "</td><td>";
    echo " <b>$mod_tipo_entidad</b>\n";
//    echo "</td></tr></table>";




    echo "</td></tr>";
    echo "</table>";
    //fin de parte  de datos de la entidad
	echo "</td>\n";
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
	echo "<table width=100%><tr>";
	echo "<td align=right><b>Número:</b></td>\n";
	echo "<td align=left width=10%><input type=text name=mod_numero value='$mod_numero'></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Expediente:</b></td>\n";
	echo "<td align=left width=10%><input type=text name=mod_exp value='$mod_exp'></td>\n";
	echo "</tr><tr>\n";


	echo "<td align=right><b>Valor del pliego:</b></td>\n";
	echo "<td align=left><input type=text name=mod_valor value='$mod_valor' size=10 maxlength=10></td>\n";
	echo "</tr></table>\n";
	echo "</td></tr><tr>\n";
	echo "<td align=center valign=top width=50%>";
	echo "<table width=100%><tr>";
	echo "<td align=right width=10%><b>Moneda:</b></td>\n";
	echo "<td align=left>";
	echo "<select name=mod_moneda>\n";
	$result1 = sql("select id_moneda,nombre from moneda") or die;
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_moneda"]."'";
		if ($result1->fields["id_moneda"] == $mod_moneda) echo " selected";
		echo ">".$result1->fields["nombre"]."</option>"."\n";
		$result1->MoveNext();
	}
	echo "</select>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Ofertado:</b></td>\n";
	echo "<td align=left><input type=text name=mod_ofertado value='$mod_ofertado' size=10 maxlength=10></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Estimado:</b></td>\n";
	echo "<td align=left><input type=text name=mod_estimado value='$mod_estimado' size=10 maxlength=10></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=right><b>Ganado:</b></td>\n";
	echo "<td align=left><input type=text name=mod_ganado value='$mod_ganado' size=10 maxlength=10></td>\n";
	echo "</tr></table></td>";
	echo "<td align=right valign=top><b>Estado:</b> \n";
	echo "<select name=mod_estado>\n";
	$result1 = sql("select id_estado,nombre,color from estado order by id_estado") or die;
	while (!$result1->EOF) {
		echo "<option value='".$result1->fields["id_estado"]."' ";
		echo "style='background-color: ".$result1->fields["color"]."; ";
		echo "color:".contraste($result1->fields["color"],"#000000","#ffffff").";'";
		if ($mod_estado == $result1->fields["id_estado"]) echo " selected";
		echo ">".$result1->fields["nombre"]."\n";
		$result1->MoveNext();
	}
	echo "</select></td>\n";?>
	<input type='hidden' name='estado_anterior' value=<?=$mod_estado?>>
	<?echo "</tr><tr>\n";
	echo "<td align=left valign=top colspan=2><b>Comentarios/Seguimiento:</b><br>";
	echo "<textarea name='mod_comentarios' style='width:100%;' rows=10>$mod_comentarios</textarea></td>\n";
	echo "</tr><tr>\n";
	echo "<td align=left colspan=2>\n";
	echo "<b>Archivos:</b><br>\n";
	echo "<table cellpadding=3 cellspacing=3 width=100%>\n";
	echo "<tr><td colspan=5 align=left></td></tr>\n";
	$result1 = sql("select * from archivos where id_licitacion=$ID") or die;
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
			echo ">No</option>\n";
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
	echo "<input type=button name=mod_cancel value='Cancelar' onClick=\"document.location='".encode_link("presupuestos_view.php",array("cmd1"=>"detalle","ID"=>$ID))."'; return false;\"><br><br>\n";
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
	download_file($ID);
}
elseif ($_POST["files_add"]) {
	$ID = $_POST["ID"];
	$nombre_entidad = $_POST["nombre_entidad"];

	$extensiones = array("doc","obd","xls","zip");
	echo $html_header;
    $traspaso_pyme=0;
	ProcForm($_FILES,"Presupuesto");
    if ($es_pymes && $traspaso_pyme)  traspaso_entidad($ID);
	detalle($ID);
}
elseif ($_POST["files_cancel"]) {
	$ID = $_POST["ID"];
	echo $html_header;
	detalle($ID);
}
elseif ($_POST["det_addfile"]) {
	genera_det_addfile("Licitaciones View");
}
else {
	listado();
}
function detalle($ID) {
	global $parametros,$contador_consultas,$datos_barra,$bgcolor2,$bgcolor3;
    global $html_root,$ver_papelera,$permisos,$cmd,$es_pymes;
    if (!$ID) { listado(); }
	else {
		$sql = "SELECT licitacion.*, ";
        if ($es_pymes)
		      $sql.= "entidad.id_entidad_pyme as id_entidad, ";
              else
              $sql.= "entidad.id_entidad as id_entidad, ";
		$sql .= "entidad.nombre as nombre_entidad, ";
		$sql .= "entidad.perfil, ";
		$sql .= "distrito.nombre as nombre_distrito, ";
		$sql .= "moneda.nombre as nombre_moneda, ";
		$sql .= "estado.nombre as nombre_estado, ";
		$sql .= "estado.color as color_estado, ";
		$sql .= "tipo_entidad.nombre as tipo_entidad,candado.estado as candado, ";
        $sql .= "lider.nombre_lider,patrocinador.nombre_patrocinador ";
		$sql .= "FROM licitacion ";
        if ($es_pymes)
		         $sql .= "LEFT JOIN entidad_pymes as entidad USING (id_entidad_pyme) ";
                 else
                 $sql .= "LEFT JOIN entidad USING (id_entidad) ";
		$sql .= "LEFT JOIN tipo_entidad USING (id_tipo_entidad) ";
		$sql .= "LEFT JOIN distrito USING (id_distrito) ";
		$sql .= "LEFT JOIN moneda USING (id_moneda) ";
		$sql .= "LEFT JOIN estado USING (id_estado) ";
		$sql .= "LEFT JOIN candado USING (id_licitacion) ";

        $sql .=" LEFT join (
                            select (apellido || text(', ') ||nombre ) as nombre_lider,id_usuario from sistema.usuarios
                            ) as lider on (lider.id_usuario=licitacion.lider)";
        $sql .=" LEFT join (
                            select (apellido || text(', ') ||nombre ) as nombre_patrocinador,id_usuario from sistema.usuarios
                            ) as patrocinador on (patrocinador.id_usuario=licitacion.patrocinador)";




		$sql .= "WHERE licitacion.id_licitacion=$ID";
		$result = sql($sql) or fin_pagina();

		generar_barra_nav($datos_barra);

              ?>
              <!--Menu Contextual -->
              <div id="ie5menu" class="skin1" onMouseover="highlightie5()" onMouseout="lowlightie5()" onClick="jumptoie5();">
              <?
              if (!$ver_papelera) {
              ?>
              <div class="menuitems" url="javascript:document.all.det_oferta.click();">Realizar Oferta</div>
              <div class="menuitems" url="javascript:document.all.det_edit.click();">Modificar</div>
              <div class="menuitems" url="javascript:document.all.det_addfile.click();">Agregar Archivos</div>
              <?
              if($result->fields['candado']==0){
               ?>
              <div class="menuitems" url="javascript:document.all.det_protocolo.click();">Cargar Protocolo</div>
              <?
              }
              ?>
              <hr>
              <?
              if (permisos_check("inicio","eliminar_lic")){
              ?>
              <div class="menuitems" url="javascript:document.all.det_del.click();">Eliminar Licitación</div>
              <?
              }
              if($result->fields['candado']==0){
              ?>
              <div class="menuitems" url="javascript:document.all.det_delfile.click();">Eliminar Archivos</div>
              <?}?>
              <hr>
              <div class="menuitems" url="javascript:document.all.det_ver_res.click();">Ver Resultados</div>
              <div class="menuitems" url="javascript:document.all.det_cargar_res.click();">Cargar Resultados</div>
              <div class="menuitems" url="javascript:document.all.det_result_coradir.click();">Resultados Coradir</div>
              <hr>
              <?
	      if ($result->fields["nombre_estado"] == "Entregada" or $result->fields["nombre_estado"] == "Orden de compra")
              {
              ?>
              <div class="menuitems" url="javascript:document.all.cobranzas.click();">Seguimientos de Cobros</div>
              <?
              }
              ?>
              <div class="menuitems" url="javascript:document.all.ordenes_asoc.click();">O. Compras Asociadas</div>
              <div class="menuitems" url="javascript:document.all.ordenes_prod_asoc.click();">O. Producción Asociadas</div>
              <hr>
              <?
              } //del if de papelera
              else {
              ?>
              <div class="menuitems" url="javascript:document.all.det_restore.click();">Restaurar Licitación</div>
              <?
              }
              ?>
              <div class="menuitems" url="javascript:document.all.det_volver.click();">Volver</div>
              </div>
              <?

               //$result tiene los datos de la licitacion
               //incluyo la funcion de tips
               //verificar_tips($result,"licitaciones");
        $id_entidad=$result->fields["id_entidad"];


		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
        echo "<input type=hidden name=es_pymes value=$es_pymes>";
		echo "<br><table width=95%  id='detalle_licitacion' border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Detalles del Presupuesto</b></td></tr>";

		if ($result->RecordCount() == 1) {

            $lider=$result->fields["nombre_lider"];
            $patrocinador=$result->fields["nombre_patrocinador"];
			$ma = substr($result->fields["fecha_apertura"],5,2);
			$da = substr($result->fields["fecha_apertura"],8,2);
			$ya = substr($result->fields["fecha_apertura"],0,4);
			$ha = substr($result->fields["fecha_apertura"],11,5);
			echo "<tr>\n";
			echo "<td  width=50% align=left valign=middle>";
			if($result->fields['candado']!=0)
			 echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Este presupuesto solo puede verse, pero no modificarse'> ";
			echo "<font size=4><b>ID:</b> ".$result->fields["id_licitacion"]."</font>";
            echo "<br>";
            echo "<b>Lider:</b> $lider <br>";
            echo "<b>Patrocinador: </b>$patrocinador";

			echo "</td>\n";
			echo "<td  width=50% align=left><b>Apertura: <font color=#FF0000>$da/$ma/$ya</font></b><br><b>Hora: <font color=#FF0000>$ha</font></b></td>\n";
			echo "</tr><tr>\n";

			echo "<td align=left colspan=1>";
			echo "<table width='100%'>" ;
			echo "<tr>";
			echo "<td width='70%'>";
			echo "<b>Distrito:</b> ".$result->fields["nombre_distrito"]."\n";
			$nombre_entidad=$result->fields["nombre_entidad"];
			echo "<input type=hidden name='nombre_entidad' value='$nombre_entidad'>";
			echo "<br><b>Entidad:</b> $nombre_entidad";
//			echo "<br><b>Tipo de Entidad:</b> ".html_out($result->fields["tipo_entidad"])."</td>\n";
			echo "<br><b>Dirección: </b>" .html_out($result->fields["dir_entidad"]);
			echo "<br><b>Tipo de Entidad:</b> ".html_out($result->fields["tipo_entidad"]);
			echo "</td>";
			echo "<td valign='top'>";
			//$id_entidad=$result->fields["id_entidad"];
			$perfil=encode_link("../licitaciones/perfil_entidad.php",array("id_entidad"=>$result->fields["id_entidad"],"modulo"=>"Licitaciones"));
			echo "<input type='button' name='Nuevo' Value='Perfil' style=\"width:100%\" onclick=\"window.open('$perfil','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400');\">";
			echo "</td>";
			echo "</tr>";
			echo "</table>";


			echo "</td>";
			echo "<td align=left>";
//incluyo la funcion que verifica si hay contactos
            if (!$es_pymes) {
			echo "<table width='100%' align='right'>";
         	echo "<tr align='right'>";
			echo "<td align='right'>";

			   $nuevo_contacto=encode_link("../general/contactos.php",array("modulo"=>"Licitaciones",
				  						                                    "id_licitaciones"=>$ID,
										                                    "id_general"=>$result->fields['id_entidad']));
			    //echo "<input type='button' name='gestiones' value='Gestiones generales' onclick='parent.window.location=\"".encode_link("../../index.php",Array ("menu"=>"lic_gestiones","extra"=>Array ("cmd1"=>"detalle","id"=>$ID)))."\";'>\n";
			    echo "<input type='button' name='gestiones' value='Gestiones generales' onclick='location.href=\"".encode_link("../licitaciones/lic_gestiones",Array ("cmd1"=>"detalle","id"=>$ID))."\";'>\n";
			    echo "<input type='button' name='Nuevo' Value='Nuevo Contacto' style=\"width:30%\" onclick=\"window.open('$nuevo_contacto','','toolbar=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
			    echo "</td>";
			    echo "</tr>";
			    echo "<tr align='right'>";
			    echo "<td>";
                //die("id_entidad".$result->fields['id_entidad']);
			    contactos_existentes("Licitaciones",$result->fields['id_entidad']);
			    echo "</td>";
			    echo "</tr>";
			    echo "</table>";
            }//del if de es_pymes
             echo "&nbsp;";
			echo "</td>";
 			echo "</tr><tr>\n";
			echo "<td align=left><b>Mantenimiento de oferta:</b> ".$result->fields["mant_oferta_especial"]."\n";
			echo "<br><b>Forma de pago:</b> ".$result->fields["forma_de_pago"]."\n";
			echo "<br><b>Plazo de entrega</b>: \n";
//			if ($result->fields["plazo_entrega"] != "") {
/*				$me = substr($result->fields["fecha_entrega"],5,2);
				$de = substr($result->fields["fecha_entrega"],8,2);
				$ye = substr($result->fields["fecha_entrega"],0,4);
				echo "$de/$me/$ye\n";
*/
				echo $result->fields["plazo_entrega"];
//			}
//			else { echo "N/A\n"; }
			echo "<br><b>Fecha de entrega</b>: \n";
			if ($result->fields["fecha_entrega"] != "") {
				$me = substr($result->fields["fecha_entrega"],5,2);
				$de = substr($result->fields["fecha_entrega"],8,2);
				$ye = substr($result->fields["fecha_entrega"],0,4);
				echo "$de/$me/$ye\n";
			}
			else { echo "N/A\n"; }
			echo "</td>\n";
			echo "<td align=right valign=top><b>Número:</b> ".html_out($result->fields["nro_lic_codificado"])." <br>\n";
			//este control es porque las licitaciones que ya estan cargadas no tiene nuemro de expediente
			if ($result->fields["exp_lic_codificado"]=="") $expediente=0;
			else $expediente=$result->fields["exp_lic_codificado"];
			echo "<b>Expediente:</b> ".html_out($expediente)."\n";
			echo "<br><b>Valor del pliego:</b> \$".formato_money($result->fields["valor_pliego"])."</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left><b>Moneda:</b> ".$result->fields["nombre_moneda"]."</b>\n";
			echo "<br><b>Ofertado:</b> ".formato_money($result->fields["monto_ofertado"])."\n";
			echo "<br><b>Estimado:</b> ".formato_money($result->fields["monto_estimado"])."\n";
			echo "<br><b>Ganado:</b> ".formato_money($result->fields["monto_ganado"])."</td>\n";
			echo "<td align=center valign=top>";
			echo "<table width=100%> <tr> <td align=right>";
				echo "<b>Estado:</b>\n";
				echo "<span style='background-color: ".$result->fields["color_estado"]."; border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;'>&nbsp;&nbsp;&nbsp;</span> ".$result->fields["nombre_estado"];
				echo "</td></tr><tr><td>";
				mostrar_ordenes_compra($ID);
				echo "</td></tr></table>";
			echo "</td></tr>";
            echo "<tr><td colspan=2>";
            muestra_licitacion($ID);
            echo "</td>";
            echo "<tr>\n";
			echo "<td align=left colspan=2><b>Comentarios/Seguimiento:</b><br>".html_out($result->fields["observaciones"])."</td>\n";
			echo "</tr>\n";
			echo "<tr><td align=left colspan=2><b>Perfil: </b>";
			if ( $result->fields["perfil"])
				echo "<br>".html_out($result->fields["perfil"]);
			else
			 echo "<b style='color:red' >No se ha cargado el perfil </b>" ;
			echo  "</td></tr>\n";

			$sql="select * from protocolo_leg where id_licitacion=".$ID." and entidad='".$result->fields["nombre_entidad"]."';"; //verifico si llenaron el protocolo
			$resultado_ex=sql($sql) or die;
			if ($resultado_ex->RecordCount()<=0)
			{echo "<tr>\n";
			 echo "<td align=left colspan=2><b>Protocolo Legal:<font color=\"red\"> No se ha cargado el protocolo legal</font></td>\n";
			 echo "</tr>\n";
			}
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            echo "<tr>\n";
			echo "<td colspan=2><img src='../../imagenes/mas.gif' border=0 style='cursor: hand;' onClick='if (this.src.indexOf(\"mas.gif\")!=-1) {
			this.src=\"../../imagenes/menos.gif\";
			div_comunicar.style.overflow=\"visible\";
			} else {
			this.src=\"../../imagenes/mas.gif\";
			div_comunicar.style.overflow=\"hidden\";
			}'>\n";
			$sql = "SELECT id_comentario FROM ";
			$sql .= "gestiones_comentarios WHERE id_gestion=$ID ";
			$sql .= "AND tipo='COMUNICAR_CLIENTE'";
			$resu=sql($sql) or fin_pagina();
			if ($resu->recordcount()>=1)
				echo "&nbsp;<b><blink tipo='color'>Comunicaciones con el cliente</blink></b>\n";
			else
				echo "&nbsp;<b>Comunicaciones con el cliente</b>\n";
			echo "<div id='div_comunicar' style='border-width: 0;overflow: hidden;height: 1'>\n";
			gestiones_comentarios($ID,"COMUNICAR_CLIENTE",1);
			echo "<br><center><input type='submit' name='guardar_comunicar' value='Guardar Comunicación'></center>\n";
			echo "</div>\n";
			echo "</td>\n";
			echo "</tr>\n";
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			echo "<tr><td align=left colspan=2><b>CD DE OFERTA: </b>";
			//directorio donde esta la imagen del cd
			if (file_exists(UPLOADS_DIR."/Licitaciones/$ID/CD_OFERTA_$ID.zip"))
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

			echo "<tr>\n";
			echo "<td align=left colspan=2>\n";
			echo "<b>Archivos:</b><br>\n";

			lista_archivos_lic($ID);

			echo "</td></tr>\n";
			if ($result->fields["ultimo_usuario"]) {
				$mm = substr($result->fields["ultimo_usuario_fecha"],5,2);
				$dm = substr($result->fields["ultimo_usuario_fecha"],8,2);
				$ym = substr($result->fields["ultimo_usuario_fecha"],0,4);
				$hm = substr($result->fields["ultimo_usuario_fecha"],11,5);
				echo "<tr>\n";
				echo "<td colspan=2><b>Ultima modificación hecha por ".$result->fields["ultimo_usuario"]." el $dm/$mm/$ym a las $hm</b></td>\n";
				echo "</tr>\n";
			}
			echo "<tr> <td style=\"border:$bgcolor2\" colspan=2 align=center>";
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
			{ $link_self_poner= encode_link("presupuestos_view.php",array("cmd1"=>"candadoponer","ID"=>$ID));
			  echo "<input type=button name=det_candado $tiene_permiso_candado style='width:100;' value='Poner Candado' onClick=\"document.location='".$link_self_poner."';\"></td>";
			}
			else
			{$link_self_sacar = encode_link("presupuestos_view.php",array("cmd1"=>"candadosacar","ID"=>$ID));
			 echo "<input type=button name=det_candado $tiene_permiso_candado style='width:100;' value='Sacar Candado' onClick=\"document.location='".$link_self_sacar."';\"></td>";
			}
			echo "<input type=hidden name=ID value='$ID'>\n";
			echo "</td></tr></table>";
//			echo "<tr>\n";
//			echo "<td style=\"border:$bgcolor2\" colspan=2 align=center><br>\n";

			if ($ver_papelera) {
				$contenido="<table width=100% bgcolor='#ffffff' align=center border=0>";
				$contenido.="<tr>\n";
				$contenido.="<td style=\"border:$bgcolor2\" colspan=2 align=center><br>\n";
				$contenido.="<input type=submit name=det_restore style='width:160;' value='Restaurar Licitación' onClick=\"return confirm('ADVERTENCIA: Se va a restaurar el Presupuesto número $ID');\">&nbsp;&nbsp;&nbsp;";
				$contenido.="</td></tr></table>";
			}
			else {

                //chequeamos si el usuario que se ha logueado, tiene permiso
                //para ver el boton de eliminar o no. Si tiene lo mostramos, sino ,esta deshabilitado.
                if (permisos_check("inicio","eliminar_lic"))
                  $tiene_permiso_eliminar="";
                else
                  $tiene_permiso_eliminar="disabled";
	        //si el candado esta puesto, deshabilitamos los botones de
	       //Eliminar Archivos y Eliminar Licitaciones
		  $disabled="";
		  if($result->fields['candado']!=0)
		  {
                   $disabled="disabled";
		  $tiene_permiso="disabled";
		  }
		  if (permisos_check("inicio","resultados_coradir"))
                      $visibility_resultado_coradir="visible";
                      else
                     $visibility_resultado_coradir="hidden";

            //$contenido.="<tr>";
            //$contenido.="<td>";
            if ($parametros["link_volver"]) {
				$link_volver = $parametros["link_volver"];
			}
			else {
				$link_volver = "document.location='".$_SERVER["PHP_SELF"]."';";
			}


			$contenido="<table width=100% align=center border=0>";
			$contenido.="<tr><td align=left><input type=button name=det_volver style='width:160;' value='Volver' onClick=\"$link_volver\"></td>\n";
			//$contenido.="</tr>";
			//$contenido.="<tr>";
			$contenido.="<td valign=top>";
			$contenido.="<table width=30% align=right border=1 cellspacing=1 cellpading=1>";
			$contenido.="<tr bgcolor=$bgcolor3>";
			$contenido.="<td width=5% align=center>";
			$contenido.="<input type=checkbox class='estilos_check' $checked_resultado name=det  onclick='Mostrar_check(this.checked,\"tabla_detalles_tercera\");'>";
			$contenido.="</td>";
			$contenido.="<td align=center>";
			$contenido.="<font color=red>";
			$contenido.="Resultados";
			$contenido.="</font>";
			$contenido.="</td>";
			$contenido.="<td width=5% align=Center>";
			$contenido.="<input type=checkbox class='estilos_check' $checked_asociaciones name=det  onclick='Mostrar_check(this.checked,\"tabla_detalles_cuarta\");'>";
			$contenido.="</td>";
			$contenido.="<td align=center>";
			$contenido.="<font color=red>";
			$contenido.="Asociaciones";
			$contenido.="</font>";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="</table>";
            $contenido.="</td>";
			$contenido.="</tr>";
            $contenido.="<tr>";
			$contenido.="<td widht=100% colspan=2 align=left valign=top>";
			$contenido.="<div id='tabla_detalles_primera' style='display:block'>";
			$contenido.="<table width=100% align=center>";
			$contenido.="<tr>";
			$contenido.="<td width=25% align=left>";
			$link=encode_link("../licitaciones/licitaciones_renglones.php",array("ID"=>$ID));
			$contenido.="<input type=button class='estilos_boton' name=det_oferta style='width:170;' value='Realizar Oferta' onClick=\"window.open('$link','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=550');\">&nbsp;&nbsp;&nbsp;";
			$contenido.="</td>";
			$contenido.="<td width=25% align=left>";
			$contenido.="<input type=submit class='estilos_boton' name=det_edit style='width:170;' value='Modificar'>&nbsp;&nbsp;&nbsp;";
			$contenido.="</td>";
		    $contenido.="<td width=25% align=left>";
			$contenido.="<input type=submit class='estilos_boton' name=det_addfile style='width:170;' value='Agregar archivo'>&nbsp;&nbsp;&nbsp;";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="</table>";
			$contenido.="</div>";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="<tr>";
			$contenido.="<td widht=100% align=left colspan=2 valign=top>";
			$contenido.="<div id='tabla_detalles_segunda' style='display:block'>";
			$contenido.="<table width=100% align=left border=0>";
			$contenido.="<tr>";
			$contenido.="<td align=left width=33%>";
			$contenido.="<input type=button class='estilos_boton' name=det_protocolo style='width:170;' $disabled value='Cargar Protocolo' onClick=\"document.location='".encode_link("../licitaciones/protocolo_legal.php",array("id_lic"=>$ID,"num"=>$result->fields["nro_lic_codificado"]))."'; return false;\">";
			$contenido.="</td>";
			$contenido.="<td align=left width=33%>";
			$contenido.="<input type=submit class='estilos_boton' name=det_del style='width:170;' $tiene_permiso_eliminar value='Eliminar Licitación' onClick=\"return confirm('ADVERTENCIA: Se va a enviar a la papelera el Presupuesto número $ID');\">";
			$contenido.="</td>";
			$contenido.="<td align=left width=33%>";
			$contenido.="<input type=submit class='estilos_boton' name=det_delfile style='width:170;' $disabled value='Eliminar Archivos' onClick=\"return confirm('ADVERTENCIA: Se van a eliminar los archivos seleccionados');\">";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="</table>";
            $contenido.="</div>";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="<tr>";
			$contenido.="<td widht=100% align=center colspan=2 valign=top>";
			$contenido.="<div id='tabla_detalles_tercera' style='display:none'>";
			$contenido.="<table width=100% align=center bgcolor=''>";
			$contenido.="<tr>";
			$contenido.="<td align=left width=25%>";
			$contenido.="<input type=button class='estilos_boton' name=det_ver_res style='width:170;' value='Ver Resultados' onClick=\"document.location='".encode_link("../licitaciones/lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic","pagina_volver"=>"../presupuestos/presupuestos_view.php"))."';\">";
			$contenido.="</td>";
			$contenido.="<td align=left width=25%>";
			$contenido.="<input type=button class='estilos_boton' name=det_cargar_res style='width:170;' value='Cargar Resultados' onClick=\"document.location='".encode_link("../licitaciones/lic_cargar_res.php",array("id_lic"=>$ID,"pagina_volver"=>"../presupuestos/presupuestos_view.php"))."'; return false;\">";
			$contenido.="</td>";
			$contenido.="<td align=left width=25%>";
			$link_self_result_coradir = encode_link("presupuestos_view.php",array("cmd1"=>"resultado_coradir","ID"=>$ID));
			$contenido.="<input type=button class='estilos_boton' name=det_result_coradir style='width:170;' value='Resultados Coradir' style='visibility:$visibility' onClick=\"document.location='$link_self_result_coradir';\">";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="</table>";
			$contenido.="</div>";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="<tr>";
            $contenido.="<td widht=100% align=center colspan=2 valign=top>";
			$contenido.="<div id='tabla_detalles_cuarta' style='display:none'>";
			$contenido.="<table width=100% align=center bgcolor=''>";
			$contenido.="<tr>";
			$contenido.="<td align=left width=25%>";
			if ($result->fields["nombre_estado"] == "Entregada" or $result->fields["nombre_estado"] == "Orden de compra") {
				$contenido.="<input type=button class='estilos_boton' name=cobranzas style='width:170;' value='Seguimiento de cobros' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"lic_cobranzas","extra"=>array("cmd1"=>"detalle","id_lic"=>$ID)))."';\">";
			}
            $contenido.="</td>";
			$contenido.="<td align=left width=25%>";
			$contenido.="<input type=button  class='estilos_boton' name=ordenes_asoc style='width:170;' value='O. de compra asociadas' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"ord_compra_listar","extra"=>array("filtro"=>"todas","keyword"=>$ID,"filter"=>"o.id_licitacion","volver_lic"=>$ID)))."';\">";
			$contenido.="</td>";
			$contenido.="<td align=left width=25%>";
			$contenido.="<input type=button class='estilos_boton' name=ordenes_prod_asoc style='width:170;' value='O. de producción asociadas' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"ordenes_ver","extra"=>array("filtro"=>"todas","keyword"=>$ID,"volver_lic"=>$ID)))."';\">";
			$contenido.="</td>";
			$contenido.="</tr>";
			$contenido.="</table>";
			$contenido.="</div>";
			$contenido.="</td>";
			$contenido.="</tr>";
			//$contenido.="</td>";
			//$contenido.="</tr>";

			}
			$contenido.="</table>";
		}
		echo "<script src='../../lib/genMove.js' type='text/javascript'></script>\n";
		inicio_barra("botonera","Presupuesto $ID",$contenido,117,580);
		echo "</tr></table></td></table><br></form>\n";
                // Barra de botones

?>

<!-- Script para el menu contextual -->
<script>
function Mostrar_check(chk,signo) {
	var tmp=document.getElementById("botonera");
	if (chk==1) {
		Mostrar(signo);
		tmp.top=tmp.top+32;
		//alert(tmp.currentStyle.height);
		tmp.style.top=(document.body.clientHeight-tmp.top)+document.body.scrollTop;
		tmp.style.height=parseInt(tmp.currentStyle.height)+32;
		inner.style.height=parseInt(inner.currentStyle.height)+32;
	}
	else {
		Ocultar(signo);
		tmp.top=tmp.top-32;
		tmp.style.top=(document.body.clientHeight-tmp.top)+document.body.scrollTop;
		tmp.style.height=parseInt(tmp.currentStyle.height)-32;
		inner.style.height=parseInt(inner.currentStyle.height)-32
	}
}
if (document.all && window.print) {
	ie5menu.className = menuskin;
	document.all.detalle_licitacion.oncontextmenu = showmenuie5;
	document.body.onclick = hidemenuie5;
}
</script>
            <?
		fin_pagina();
	}
}
function listado()
{
	global $bgcolor3,$cmd,$cmd1,$datos_barra,$up,$db;
	global $bgcolor2,$itemspp,$parametros,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera,$permisos,$_ses_global_extra;
	global $_ses_global_backto,$_ses_global_nro_orden_asociada,$_ses_global_pag,$contador_consultas;
	echo $html_header;
	generar_barra_nav($datos_barra);
	$orden = array(
		"default" => "3",
		"default_up" => "0",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.id_estado",
		"3" => "licitacion.fecha_apertura",
		"4" => "entidad.nombre",
		"5" => "distrito.nombre",
		"6" => "licitacion.nro_lic_codificado",
		"7" => "licitacion.fecha_apertura"
	);

	$filtro = array(
		"distrito.nombre" => "Distrito",
		"entidad.nombre" => "Entidad",
		"licitacion.observaciones" => "Comentarios",
		"licitacion.mant_oferta_especial" => "Mantenimiento de oferta",
		"licitacion.forma_de_pago" => "Forma de pago",
		"licitacion.id_moneda" => "Moneda",
		"licitacion.id_licitacion" => "ID de licitación",
		"licitacion.nro_lic_codificado" => "Número de licitación",
		"licitacion.fecha_apertura" => "Fecha Apertura"
	);

	$itemspp = 50;

	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
	echo "<tr><td align=center>\n";

	$sql_tmp = "SELECT licitacion.id_licitacion,licitacion.fecha_apertura,licitacion.nro_lic_codificado,licitacion.id_estado,licitacion.check_lic,licitacion.resultados_cargados,entidad.nombre AS nombre_entidad,";
	$sql_tmp .= "monto_ofertado,monto_estimado,monto_ganado,";
	$sql_tmp .= "distrito.nombre AS nombre_distrito ";
    if($cmd=="proximas" ||$cmd=="presentadas")
    {$sql_tmp .= ",licitacion.observaciones,entregar_lic.orden_subida,entregar_lic.mostrar,entregar_lic.vence,entregar_lic.oferta_subida,";
	 $sql_tmp .= "entregar_lic.archivo_oferta,entregar_lic.oferta_lista_imprimir ";
    }
	//$sql_tmp .= "archivos.imprimir,count(renglon.id_renglon) AS cant_renglones,";
	//$sql_tmp .= "count(oferta.id) AS cant_ofertas ";
	$sql_tmp .= "FROM licitacion ";

    if ($cmd=='ppp') $sql_tmp.=" JOIN pymes.entidad_pymes as entidad using(id_entidad_pyme)";
                else $sql_tmp.=" JOIN entidad using(id_entidad)";

//$sql_tmp.=" LEFT JOIN entidad using(id_entidad)";
    //$sql_tmp .=" LEFT JOIN entidad ";
	//$sql_tmp .= "USING (id_entidad) ";
	$sql_tmp .= "LEFT JOIN distrito ";
	$sql_tmp .= "USING (id_distrito) ";
	$sql_tmp .= "LEFT JOIN estado ";
	$sql_tmp .= "USING (id_estado) ";

	$link_tmp = array("cmd"=>$cmd);
    if ($cmd=="ppp")
              $condicion_pyme=" es_presupuesto_pyme=1 and ";
              elseif($cmd=="pcg")
              $condicion_pyme=" es_presupuesto_pyme=0 and ";

	$where_tmp = " $condicion_pyme es_presupuesto=1 ";
		 $contar="SELECT count(*) FROM licitaciones.licitacion WHERE borrada='f' and es_presupuesto=1";
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
			$where_tmp .= " AND licitacion.id_estado = $estado";
		}
	}

	$where_tmp .= " GROUP BY licitacion.id_licitacion,licitacion.id_entidad,
					licitacion.id_moneda,licitacion.id_estado,
					licitacion.numero,licitacion.nro_lic_codificado,
					licitacion.fecha_apertura,licitacion.valor_pliego,
					licitacion.mantenimiento_oferta,licitacion.prorroga_automatica,
					licitacion.valor_dolar_lic,licitacion.valor_dolar_compra,
					licitacion.forma_de_pago,licitacion.fecha_entrega,
					licitacion.dias_para_entrega,licitacion.plazo_entrega,
					licitacion.tipo_dia_mant_ofeta,licitacion.tipo_dia_plazo_entre,
					licitacion.ofertado_sistema,licitacion.mant_oferta_especial,
					licitacion.usuario_realiza_licitacion,licitacion.finalizado_por_autor,
					licitacion.observaciones,licitacion.total_ofertado,
					licitacion.monto_ganado,licitacion.monto_estimado,
					licitacion.monto_ofertado,licitacion.ultimo_usuario,
					licitacion.ultimo_usuario_fecha,licitacion.nro_version,
					licitacion.nro_renglones,licitacion.es_viejo,
					licitacion.borrada,licitacion.dir_entidad,licitacion.resultados_cargados,
					licitacion.exp_lic_codificado,licitacion.check_lic,entidad.nombre,distrito.nombre";
     if($cmd=="proximas" ||$cmd=="presentadas")
       $where_tmp .=",licitacion.resultados_cargados,entregar_lic.orden_subida,entregar_lic.mostrar,entregar_lic.vence,entregar_lic.oferta_subida,entregar_lic.archivo_oferta,entregar_lic.oferta_lista_imprimir";
					//entregar_lic.archivo_oferta,archivos.imprimir";*/
	if (($cmd == "presentadas" or $cmd == "presupuesto" or $cmd == "historial" or $cmd == "todas") and ($up == "")) {
		$up = "0";
	}
	?>
    <div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_ver_lic.htm" ?>', 'LISTAR PRESUPUESTOS')" >
     </div>
     <?
    if($_POST['keyword'] || $keyword || $_POST['estado']!="all")// en la variable de sesion para keyword hay datos)
     $contar="buscar";

	list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);

	echo "&nbsp;&nbsp;Estado: <select name='estado'>\n";
	echo "<option value='all'>Todos\n";
	$sql_est = "SELECT id_estado,nombre,color FROM estado";
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
	echo "</select>";
	echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";
	echo "</td></tr></table><br>\n";
	echo "</form>\n";
	$result = sql($sql) or die($db->ErrorMsg()."<br>Error");
	//die($sql);
	//$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error");
	echo "<table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=$bgcolor3 align=center>";
	echo "<tr><td colspan=6 align=left id=ma>\n";
	echo "<table width=100%><tr id=ma>\n";
	echo "<td width=30% align=left><b>
	Total:</b> $total_lic presupuestos.</td>\n";
	echo "<td width=70% align=right>$link_pagina</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
//	echo "<div style='position:relative; width:55%; overflow:auto;'>";
	echo "<tr>";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
	echo "&nbsp;/&nbsp;<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Apertura</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Entidad</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Distrito</td>\n";
	if (permisos_check("inicio","licitaciones_columna_monto")) {
//		echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))."'>Monto</td>\n";
		echo "<td align=right id=mo>Monto</td>\n";
	}
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))."'>Número</td>\n";

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
		$ma = substr($result->fields["fecha_apertura"],5,2);
		$da = substr($result->fields["fecha_apertura"],8,2);
		$ya = substr($result->fields["fecha_apertura"],0,4);
		$hora = substr($result->fields["fecha_apertura"],10,6);
        $dia=calcula_numero_dia_semana($da,$ma,$ya);
        switch ($dia)
        {
	        case 0:$dia="Domingo";break;
	        case 1:$dia="Lunes";break;
	        case 2:$dia="Martes";break;
	        case 3:$dia="Miercoles";break;
	        case 4:$dia="Jueves";break;
	        case 5:$dia="Viernes";break;
	        case 6:$dia="Sabado";break;
        }

//		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle","sort"=>$sort,"up"=>$parametros["up"],"page"=>$page,"keyword"=>$keyword,"estado"=>$estado,"filter"=>$filter,"ID"=>$result->fields["id_licitacion"]));
		//si viene de otra pagina como ordcompra, remitos o facturas
		if ($_ses_global_backto)
			$ref = encode_link($_ses_global_backto,array("licitacion"=>$result->fields["id_licitacion"],"nro_orden"=>$_ses_global_nro_orden_asociada,"pagina"=>$_ses_global_pag,"presupuesto"=>"1","_ses_global_extra"=>$_ses_global_extra));
		else
            {
            if ($cmd=="ppp")
                  $ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"],"es_pymes"=>1));
                  else
			      $ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"],"es_pymes"=>0));
            }

		tr_tag($ref);
		echo "<td align=center bgcolor='".$estados[$result->fields["id_estado"]]["color"]."' title='".$estados[$result->fields["id_estado"]]["texto"]."'><b><a style='color=".contraste($estados[$result->fields["id_estado"]]["color"],"#000000","#ffffff").";' href='$ref'>";

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
		echo "<td align=center title='$dia - $hora'>$da/$ma/$ya</td>\n";
		echo "<td align=left title='".$title_obs."'>&nbsp;".html_out($result->fields["nombre_entidad"])."</td>\n";
		echo "<td align=left>&nbsp;".html_out($result->fields["nombre_distrito"])."</td>\n";
		if (permisos_check("inicio","licitaciones_columna_monto")) {
			//si es en curso - monto ofertado
			//si p.ganada - monto estimado
			//si adj o presuntamente - monto ganado
				$monto="";
				switch ($result->fields["id_estado"]){
					case 10:
					case 0:
							$monto="MO:&nbsp;".$result->fields["simbolo"]."&nbsp;".$result->fields["monto_ofertado"];
							break;
					case 2:
							$monto="ME:&nbsp;".$result->fields["simbolo"]."&nbsp;".$result->fields["monto_estimado"];
							break;
					case 3:
					case 7:
							$monto="MG:&nbsp;".$result->fields["simbolo"]."&nbsp;".$result->fields["monto_ganado"];
							break;
				}//del switch
			echo "<td   $bgcolor  align=left valign=middle>$monto</td>";
		}

/*
		$query="select res.id_renglon from (select id_renglon, id_licitacion from  renglon where id_licitacion=".$result->fields['id_licitacion'].") AS res, oferta  where res.id_renglon=oferta.id_renglon";
		$res=$db->Execute($query) or die($db->ErrorMsg());
		if ($res->RecordCount() > 0 )
		$valor=1;
		else $valor=0;
*/
		echo "<td align=left valign=middle>".html_out($result->fields["nro_lic_codificado"]);
/*		switch ($valor)
		{
		  case 0:
				 break;
		  case 1: echo "<a href='".encode_link("lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic"))."'>
		     <img src=$html_root/imagenes/R.gif border=0 title='Ver Resultados' width='15' height='14' align='absmiddle'></a>";
			 break;
		}
*/

		if( $result->fields["resultados_cargados"])
		{ echo "<a href='".encode_link("../licitaciones/lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic"))."'>
				<img src=$html_root/imagenes/R.gif border=0 title='Ver Resultados' width='15' height='14' align='absmiddle'></a>";
		}

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
	echo "<tr><td colspan=6 align=center><br>\n";
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
	echo "</td></tr>\n";
	echo "</table><br>\n";
	fin_pagina();
} // listado
?>