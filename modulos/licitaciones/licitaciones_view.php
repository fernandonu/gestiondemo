<?
/*
Autor: ????????
Modificado por:
$Author: mari $
$Revision: 1.386 $
$Date: 2007/01/04 18:32:56 $

*/

/*
ATENCION: TODOS LOS CAMBIOS QUE SE HAGAN EN ESTE ARCHIVO, QUE AFECTEN A OTROS
ARCHIVOS QUE SE RELACIONAN CON ESTE (POR EJEMPLO ORD_COMPRA.PHP) DEBEN TAMBIEN
REALIZARCE EN EL ARCHIVO PRESUPUESTOS_VIEW, QUE ES UN "CLON" DE ESTE ARCHIVOS
BASICAMENTE.
*/

require_once("../../config.php");
require_once("../general/funciones_contactos.php");


//incluyo el modulo de tips
//global que sirve para saber si se ha seleccionado proximas o no (presentadas o historial).

variables_form_busqueda("licitaciones",array("estado"=>""));


 //colores para los eventos de la licitacion
$vencido="#FF0000";
$dos_dias="#FF8080";
$cuatro_dias="#FFFFD9";
$hay_vencidos=0;

//variable para volver a otra pagina como ordcompra, remitos o facturas
$backto=$parametros['backto'] or $backto=$_POST['backto']	;
//$_ses_global_extra=$parametros['_ses_global_extra'];
if ($backto && $_ses_global_backto!=$backto) {
	phpss_svars_set("_ses_global_backto", $backto);
	phpss_svars_set("_ses_global_nro_orden_asociada", $parametros['nro_orden']);
	phpss_svars_set("_ses_global_pag", $parametros['pag']);
	phpss_svars_set("_ses_global_extra", $parametros['_ses_global_extra']);
}

//variable de sesion que sirve para indicar si se visito la pagina
//de presupuesto o la de licitaciones
phpss_svars_set("_ses_global_lic_o_pres", "lic");

if ($cmd == "") {
	$cmd="proximas";
	$_ses_licitaciones["cmd"] = $cmd;
	phpss_svars_set("_ses_licitaciones", $_ses_licitaciones);
}


function fechas_entregas_oc($id_subir){

     $sql=" select distinct lroc.fecha_entrega from
               renglones_oc join log_renglones_oc lroc using(id_renglones_oc)
               where id_subir=$id_subir and tipo='entrega' order by fecha_entrega DESC limit 1";
     $res=sql($sql) or fin_pagina();
     $resultado=array();
     for($i=0;$i<$res->recordcount();$i++){
         $resultado[]=$res->fields["fecha_entrega"];
         $res->movenext();
     }

   if (sizeof($resultado))
             {
             //$resultado=array_unique($resultado);
             $return="<table width=100% align=center>";
             for($i=0;$i<sizeof($resultado);$i++){
               $return.="<tr><td align=center>";
               $return.=fecha($resultado[$i]);
               $return.="</td></tr>";
             }
             $return.="</table>";
             }
             else $return="&nbsp;";
      return $return;
}//de la funcion fechas_entregas_co



function fechas_entregas($id_licitacion){
    global $bgcolor3,$html_root;

   $sql=" select * from (
            select id_subir,nro_orden,vence_oc from
            subido_lic_oc where id_licitacion=$id_licitacion and tipo_muestras=0
            ) as sl
            left join
            (
            select sum(cantidad*precio) as total,id_subir from renglones_oc
            group by id_subir
            )  as total
           using (id_subir)
           order by vence_oc ASC
           ";
   $res=sql($sql) or fin_pagina();

   $sql="select simbolo from licitacion join moneda using(id_moneda)
         where id_licitacion=$id_licitacion";
   $moneda=sql($sql) or fin_pagina();

   if ($res->recordcount()>0) {
      //es que hay ordenes

      ?>
      <table width=100% align=center border=1 cellpading=0 cellspacing=0 bordercolor='<?=$bgcolor3?>'>
        <tr>
          <td colspan=4 align=Center><b>Ordenes de Compra</b></td>
        </tr>
        <tr>
          <td width=40% align=center><b>Orden de Compra</b></td>
          <td align=center><b>Fecha de Entrega</b></td>
          <td>&nbsp;</td>
          <td align=center><b>Montos</b></td>
        </tr>
        <?
        for($i=1;$i<=$res->recordcount();$i++){
            $id_subir=$res->fields["id_subir"];
            $link=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$id_subir,"solo_lectura"=>1));
            $fechas_entregas=fechas_entregas_oc($id_subir);
        ?>
         <tr>
            <a href=<?=$link?> target="_blank">
            <td align=left >
                <font color='blue'>
                <?=$res->fields["nro_orden"]?>
                </font>
            </td>
            <td align=center><?=fechas_entregas_oc($id_subir)?></td>
            <td align=center><?=$moneda->fields["simbolo"]?></td>
            <td align=right><?=formato_money($res->fields["total"])?></td>
            </a>

         </tr>
        <?
         $cont++;
         $res->movenext();

     }  // del for
     ?>
     </table>
     <?
}

}//de la funcion de orden de compra
//devuelve el dia de la semana que le correponde d/m/a
// dom=0,lun=1...
function calcula_numero_dia_semana($dia,$mes,$ano){
	$nrodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
	return $nrodiasemana;
}

function eventos_licitacion($id){
global $db,$dos_dias,$cuatro_dias,$vencido,$hay_vencidos,$parametros;


$dos_dias_aux=0;
$cuatros_dias_aux=0;
$mas_dias=0;


 $sql="select * from eventos_lic where id_licitacion=$id and activo=0";
 $sql.=" order by fecha";
 $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
 $cantidad=$resultado->recordcount();

 for($i=0;$i<$cantidad;$i++){
      $activo=$resultado->fields["activo"];
 /*     if (!$activo) {//es que esta activo el evento
         $fecha_evento=$resultado->fields["fecha"];
         //$fecha_evento=fecha($fecha_evento);
         $fecha_hoy=date("Y-m-d H:i:s");
         $dias=diferencia_dias($fecha_hoy,$fecha_evento,1);
  */
       if (!$activo) {//es que esta activo el evento
         $fecha_evento=fecha($resultado->fields["fecha"]);
         //$fecha_evento=fecha($fecha_evento);
         $fecha_hoy=date("d/m/Y");
         $dias=diferencia_dias_habiles($fecha_hoy,$fecha_evento);


         if(($dias>2)&&($dias<=4)) $cuatro_dias_aux++;
         if(($dias>0)&&($dias<=2)) $dos_dias_aux++;
         if ($dias==0) $mas_dias++;


         } //del if
       $resultado->movenext();
         }   //del for

 if ($cantidad)
  {
  if ($mas_dias) {$hay_vencidos++; return "bgcolor='$vencido'";}
  if ($dos_dias_aux) return "bgcolor='$dos_dias'";
  if ($cuatro_dias_aux) return "bgcolor='$cuatro_dias'";

  }

} //fin de la funcion

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
											OR licitacion.id_estado=7
											OR licitacion.id_estado=12)
											AND licitacion.fecha_apertura > '".date("Y-m-d 23:59:59",mktime())."'
											AND borrada='".(($papelera)?"t":"f")."' AND es_presupuesto=0"
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
						),
					/*array(
						"descripcion"	=> "Presupuesto",
						"cmd"			=> "presupuesto"
						),*/
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );

if ($_POST["resultados_cargados"]) {

   $msg=automatizar_estados($_POST["ID"]);
   echo $html_header;
   echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
   echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
   echo "<font size=3>$msg</font>";
   echo "</b><br><br></td></tr></table><br>\n";

   detalle($_POST["ID"]);
}

if ($_POST["det_del"]) {
	echo $html_header;
	$ID = $_POST["ID"];
	$msg = "";
	$sql = "update licitacion set borrada='t' where id_licitacion=$ID";
	$result = sql($sql) or $msg = 1;
	if ($msg) {
		$msg = "No se pudo enviar a la papelera la Licitación número $ID.";
	}
	else {
		$msg = "La Licitación número $ID ha sido enviada a la papelera";
	}
	echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
	echo "<font size=3>$msg</font>";
	echo "</b><br><br></td></tr></table><br>\n";
	listado();
}
if ($_POST["sinc_entidad"]) {
	$query[]="UPDATE orden_de_compra SET id_entidad=".$_POST["id_entidad"].",cliente='".$_POST["nombre_entidad"]."' where id_licitacion=".$_POST["ID"];
	$query[]="UPDATE facturas SET id_entidad=".$_POST["id_entidad"].",cliente='".$_POST["nombre_entidad"]."' where id_licitacion=".$_POST["ID"];
	$query[]="UPDATE remitos SET id_entidad=".$_POST["id_entidad"].",cliente='".$_POST["nombre_entidad"]."' where id_licitacion=".$_POST["ID"];
	$query[]="UPDATE orden_de_produccion SET id_entidad=".$_POST["id_entidad"]." where id_licitacion=".$_POST["ID"];
	sql($query) or fin_pagina();
	Aviso("La licitacion se sincronizó con exito.");
	$cmd1="detalle";
}
if ($_POST["det_restore"]) {
	echo $html_header;
	$ID = $_POST["ID"];
	$msg = "";
	$sql = "update licitacion set borrada='f' where id_licitacion=$ID";
	$result = sql($sql) or $msg = 1;
	if ($msg) {
		$msg = "No se pudo restaurar de la papelera la Licitación número $ID.";
	}
	else {
		$msg = "La Licitación número $ID ha sido restaurada de la papelera";
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
		  $msg = "Se puso el candado para esta licitación";
		 else
		  $msg = "No se pudo poner el candado para esta licitación";
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
		  $msg = "Se sacó el candado para esta licitación";
		 else
		  $msg = "No se pudo sacar el candado para esta licitación";
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
 include("./resultados_coradir.php");
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
elseif ($_POST["mod_edit"]) {

	$ID = $_POST["ID"];
	$mod_entidad = $_POST["mod_entidad"];
	$error = 0;
	echo $html_header;
	echo "<br><table width=50% border=0 cellspacing=1 cellpadding=5 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td colspan=3 align=center bgcolor=$bgcolor2><b>";
	$mod_distrito = $_POST["mod_distrito"];
	$mod_entidad = $_POST["mod_entidad"];
	$mod_dir=$_POST["mod_dir"];
//	$mod_tipo_entidad = $_POST["mod_tipo_entidad"];
	$mod_numero = $_POST["mod_numero"];
	$mod_exp=$_POST["mod_exp"];
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
	$mod_estado = $_POST["mod_estado"] ;
	$mod_moneda = $_POST["mod_moneda"];
	$file_id = $_POST["file_id"];

    //guardo el nuevo evento
//            print_r($_POST);

     //traemos el id del estado "Perdida"
     $query="select id_estado from estado where nombre='Perdida'";
     $estado=$db->Execute($query) or die($db->Errormsg()."<br>Error al traer el id del estado Perdida");
     //Si el estado es igual a perdida, controlamos que todos los renglones
     //tengan como ganador a algun comeptidor que no sea CORADIR
     if($_POST['validar_control']=="")
          $validar_control=0;
     else $validar_control=1;
     if($mod_estado==$estado->fields['id_estado']) {
       	 $reng_ctrl=control_resultados_renglon($ID, $validar_control);
         if($reng_ctrl!=1) {
           Error("No se puede cambiar el estado de la Licitación - $reng_ctrl");
           }

     }

//	$mod_ = $_POST["mod_"];
    	if ($mod_distrito == "") { echo "<font size=2 color=#ff0000>Falta seleccionar el Distrito</font><br><br>"; $error = 1; }
		if ($mod_entidad == "") { echo "<font size=2 color=#ff0000>Falta seleccionar la Entidad</font><br><br>"; $error = 1; }
		if ($mod_dir == "") { echo "<font size=2 color=#ff0000>Falta ingresar la direción de la Entidad</font><br><br>"; $error = 1; }
		//		if ($mod_tipo_entidad == "") { echo "<font size=2 color=#ff0000>Falta seleccionar el Tipo de Entidad</font><br><br>"; $error = 1; }
		if ($mod_numero == "") { echo "<font size=2 color=#ff0000>Falta ingresar el Número de Licitación</font><br><br>"; $error = 1; }
		if ($mod_exp == "") { echo "<font size=2 color=#ff0000>Falta ingresar el Número de expediente de Licitación</font><br><br>"; $error = 1; }
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
			$check=array("xt"=>"t","x"=>"f");
	                $iso=$check['x'.$_POST['iso']];
			$sql = "update licitacion set ";
			$sql .= "plazo_entrega='$mod_plazo_ent',";
			$sql .= "fecha_entrega=$mod_fecha_ent,";
                        if ($mod_estado!=""){
			$sql .= "id_estado=$mod_estado,";
                        }
			$sql .= "fecha_apertura='$mod_fecha_apertura $hora_apertura',";
			$sql .= "mant_oferta_especial=$mod_mant_ok,";
			$sql .= "nro_lic_codificado='$mod_numero',";
			$sql .= "id_entidad=$mod_entidad,";
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
			$sql .= "iso9001='$iso',";
			$sql .= "monto_ganado=$mod_ganado_ok ";
			$sql .= "where id_licitacion=$ID";
			$sql_array[] = $sql;

			//actualizo la direccion enla tabla entidad si esta seleccionado el checkbox
			if ($_POST["guardar_dir"]=='SI'){
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
					$nombre_arch=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el nombre del archivo");

					$query="update entregar_lic set oferta_lista_imprimir=$of_imp where id_licitacion=$ID and archivo_oferta='".$nombre_arch->fields['nombre']."'";
					$sql_array[] = $query;

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
				$msg = "No se pudo modificar la Licitación número $ID.<br>$msg";
			}
			echo $msg;
		}
		echo "</b></td></tr></table>\n";
		detalle($ID);
}
elseif ($_POST["det_edit"]) {
include("licitaciones_editar.php");

}
elseif ($_POST["guardar_comunicar"]) {
	echo $html_header;
	$ID=$_POST["ID"] or $ID=$parametros["ID"];
	$comentario = $_POST["comentario_nuevo"];
    $sql=nuevo_comentario($ID,"COMUNICAR_CLIENTE",$comentario);
	sql($sql) or fin_pagina();
	detalle($ID);
}
elseif ($cmd1 == "detalle") {
	echo $html_header;
	$ID = $parametros["ID"] or $ID = $_POST["ID"];
	detalle($ID);
}
elseif ($cmd1 == "download") {
	$ID = $parametros["ID"];
	download_file($ID);
	/*$FileID = $parametros["FileID"];
	$Comp = $parametros["Comp"];
	if ((!$ID) or (!$FileID)) {
		listado();
	}
	$sql = "SELECT archivos.*,licitacion.fecha_apertura ";
	$sql .= "FROM archivos ";
	$sql .= "INNER JOIN licitacion ";
	$sql .= "ON archivos.id_licitacion=licitacion.id_licitacion ";
	$sql .= "WHERE archivos.idarchivo=$FileID";
	$result = sql($sql,1001) or die();
	if ($result->RecordCount() <= 0) {
		Mostrar_Error("No se encontró el archivo");
	}
	else {
		if ($Comp) {
			$FileName=$result->fields["nombrecomp"];
			$FileType="application/zip";
			$FileSize=$result->fields["tamañocomp"];
		}
		else {
			$FileName=$result->fields["nombre"];
			$FileType=$result->fields["tipo"];
			$FileSize=$result->fields["tamaño"];
		}
        if ($result->fields['id_producto']==""){
		$fecha = substr($result->fields["fecha_apertura"],0,4);
		$sql = "SELECT entidad.nombre as nombre_entidad,";
		$sql .= "distrito.nombre as nombre_distrito ";
		$sql .= "FROM (licitacion ";
		$sql .= "INNER JOIN entidad ";
		$sql .= "ON licitacion.id_entidad=entidad.id_entidad) ";
		$sql .= "INNER JOIN distrito ";
		$sql .= "ON entidad.id_distrito=distrito.id_distrito ";
		$sql .= "WHERE licitacion.id_licitacion=$ID";
		$result = sql($sql) or die;
		$distrito = $result->fields["nombre_distrito"];
		$entidad = $result->fields["nombre_entidad"];
//		$FilePath=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$ID";
		$FilePath=UPLOADS_DIR."/Licitaciones/$ID";

		$FileNameFull="$FilePath/$FileName";
        }
        else {
             $FilePath=UPLOADS_DIR."/folletos";
             $FileNameFull="$FilePath/$FileName";
             }

		if (($Comp) or (substr($FileName,strrpos($FileName,".")) == ".zip")) {
			if (file_exists($FileNameFull)) {
				Mostrar_Header($FileName,$FileType,$FileSize);
				readfile($FileNameFull);
			}
			else {
				Mostrar_Error("Se produjo un error al intentar abrir el archivo comprimido");
			}
		}
		else {
			$FileNameFull = substr($FileNameFull,0,strrpos($FileNameFull,"."));
			$fp = popen("/usr/bin/unzip -p \"$FileNameFull\" 2> /dev/null","r");
			if (!$fp) {
				Mostrar_Error("Se produjo un error al intentar descomprimir el archivo");
			}
			else {
				Mostrar_Header($FileName,$FileType,$FileSize);
				fpassthru($fp);
				pclose($fp);
			}
		}
	}*/
}
elseif ($_POST["files_add"]) {
	$ID = $_POST["ID"];
	$nombre_entidad = $_POST["nombre_entidad"];

	$extensiones = array("doc","obd","xls","zip");
	echo $html_header;

	ProcForm($_FILES);

	detalle($ID);
	/*$filename="orden_de_compra_".$clave_valor[1].".pdf";
	$mailtext="Este es un mail enviado automaticamente por orden de compra";
	$mail_header="";
	$mail_header .= "MIME-Version: 1.0";
	$mail_header .= "\nfrom: Sistema Inteligente CORADIR";
	$mail_header .="\nTo: ".$clave_valor2[1];
	$mail_header .="\nBcc: daingara@unsl.edu.ar";
	$mail_header .= "\nReply-To: gonzalo@pcpower.com.ar";
	$mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary";
	$mail_header .= "\n\nThis is a multi-part message in MIME format ";
	// Mail-Text
	$mail_header .= "\n--$boundary";
	$mail_header .= "\nContent-Type: text/plain";
	$mail_header .= "\nContent-Transfer-Encoding: 8bit";
	$mail_header .= "\n\n" . $mailtext."\n";
	// Your File
	$mail_header .= "\n--$boundary";
	$mail_header .= "\nContent-Type: application/pdf; name=\"$filename\"";
	// Read from Array $contenttypes the right MIME-Typ
	$mail_header .= "\nContent-Transfer-Encoding: base64";
	$mail_header .= "\n\n".$archivo=chunk_split(base64_encode(fread(fopen($filepath.$filename, "r"), filesize($filepath.$filename))));
	// End
	$mail_header .= "\n--$boundary--";
	mail("","Nueva Orden de Compra Autorizada","",$mail_header)
	*/
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
	global $parametros,$contador_consultas,$datos_barra,$bgcolor2,$bgcolor3,$html_root;
        global $ver_papelera,$permisos,$_ses_user;
        global $cuatro_dias,$dos_dias,$vencido,$cmd,$bgcolor_out;
	if (!$ID) { listado(); }
	else {
		$sql = "SELECT licitacion.*, impugnacion.*, garantia_contrato.*, garantia_de_oferta.*,";
		$sql.= "entidad.id_entidad as id_entidad, normas.nombre as nombre_normas, componentes_nueva_lic.nombre as nombre_componentes,";
		$sql .= "entidad.nombre as nombre_entidad,lider.nombre_lider,patrocinador.nombre_patrocinador, ";
		$sql .= "entidad.perfil,id_responsable_apertura, ";
		$sql .= "distrito.nombre as nombre_distrito, ";
		$sql .= "moneda.nombre as nombre_moneda, ";
		$sql .= "estado.nombre as nombre_estado, ";
		$sql .= "estado.color as color_estado, ";
		$sql .= "tipo_entidad.nombre as tipo_entidad,candado.estado as candado, exp_lic_codificado ";
		$sql .= "FROM licitacion ";
		$sql .= "LEFT JOIN entidad ";
		$sql .= "USING (id_entidad) ";
		$sql .= "LEFT JOIN tipo_entidad ";
		$sql .= "USING (id_tipo_entidad) ";
		$sql .= "LEFT JOIN distrito ";
		$sql .= "USING (id_distrito) ";
		$sql .= "LEFT JOIN moneda ";
		$sql .= "USING (id_moneda) ";
		$sql .= "LEFT JOIN estado ";
		$sql .= "USING (id_estado) ";
		$sql .= "LEFT JOIN candado ";
		$sql .= "USING (id_licitacion) ";
		/////////////////////////Broggi
		$sql .= "LEFT JOIN licitaciones_datos_adicionales.normas ";
		$sql .= "USING (id_normas) ";
		$sql .= "LEFT JOIN licitaciones_datos_adicionales.componentes_nueva_lic ";
		$sql .= "USING (id_componentes_nueva_lic) ";
		$sql .= "LEFT JOIN licitaciones_datos_adicionales.garantia_de_oferta ";
		$sql .= "USING (id_garantia_de_oferta) ";
		$sql .= "LEFT JOIN licitaciones_datos_adicionales.garantia_contrato ";
		$sql .= "USING (id_garantia_contrato) ";
		$sql .= "LEFT JOIN licitaciones_datos_adicionales.impugnacion ";
		$sql .= "USING (id_impugnacion) ";
		/////////////////////////////////////

        $sql .=" LEFT join (
                            select (apellido || text(', ') ||nombre ) as nombre_lider,id_usuario from sistema.usuarios
                            ) as lider on (lider.id_usuario=licitacion.lider)";
        $sql .=" LEFT join (
                            select (apellido || text(', ') ||nombre ) as nombre_patrocinador,id_usuario from sistema.usuarios
                            ) as patrocinador on (patrocinador.id_usuario=licitacion.patrocinador)";

		$sql .= "WHERE licitacion.id_licitacion=$ID";
		$result = sql($sql) or die;
		//print_r($result->fields);
		$sql = "select * from licitaciones.moneda";
		$result_moneda = sql($sql) or die($sql);
		$sql = "select * from licitaciones_datos_adicionales.tipo_garantia";
		$result_tipo_garantia = sql($sql) or die($sql);
  	      generar_barra_nav($datos_barra);
              //cargo los permisos para la botonera principal
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
              <?
              if (permisos_check("inicio","resultados_coradir")){
              ?>
              <div class="menuitems" url="javascript:document.all.det_result_coradir.click();">Cargar Resultados Coradir</div>
              <?
              }
              if (permisos_check("inicio","resultados_cargados")){
              ?>
              <div class="menuitems" url="javascript:document.all.resultados_cargados.click();">Finalizar Carga Resultados</div>
              <?
              }
              ?>
              <hr>
              <div class="menuitems" url="javascript:document.all.remitos_asociados.click();">Remitos Asociados</div>
              <?
              if ($result->fields["nombre_estado"] == "Entregada" or $result->fields["nombre_estado"] == "Orden de compra"){
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
        //echo "<font size='4'>Diego general</font>";
        ?>
        <script language="javascript">

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
		tmp.top=tmp.top-30;
		tmp.style.top=(document.body.clientHeight-tmp.top)+document.body.scrollTop;
		tmp.style.height=parseInt(tmp.currentStyle.height)-30;
		inner.style.height=parseInt(inner.currentStyle.height)-30
	}
}

function aumentar_menu(signo)
{Mostrar(signo);
		//top=top+26;
		//if (document.all.mos_ocu.value=="O") mover.style.top=(document.body.clientHeight-top)+document.body.scrollTop;
}

function mostrar_tabla(){
if (document.all.tabla.value==0)
  Mostrar('tabla_signo');
else
  Ocultar('tabla_signo');
if (document.all.tabla.value==0)
  document.all.tabla.value=1;
else
  document.all.tabla.value=0;
}

//function scroll() {
//	alert(document.body.clientHeight);
//	if (document.all.mos_ocu.value=="O")
//		mover.style.top=(document.body.clientHeight-top)+document.body.scrollTop;
//	else
//		mover.style.top=(document.body.clientHeight-27)+document.body.scrollTop;
//}

//window.onscroll = scroll;
</script>
<script src="../../lib/genMove.js" type="text/javascript"></script>
<?

		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";

		echo "<br><table align=center width=95% id='detalle_licitacion' width=100% border=0><tr><td>";
		echo "<table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center >";
		echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Detalles de la Licitación</b></td></tr>";
		if ($result->RecordCount() == 1) {
            $lider=$result->fields["nombre_lider"];
            $patrocinador=$result->fields["nombre_patrocinador"];

            $ma = substr($result->fields["fecha_apertura"],5,2);
			$da = substr($result->fields["fecha_apertura"],8,2);
			$ya = substr($result->fields["fecha_apertura"],0,4);
			$ha = substr($result->fields["fecha_apertura"],11,5);
			echo "<tr>\n";
			echo "<td  width=50% align=left valign=middle>";
			echo "<table width=100% border=0>";
			echo "<tr> <td>";
			$link=encode_link('datos_apertura.php',array("id_licitacion"=>$result->fields['id_licitacion']));
			$id_responsable_apertura=$result->fields['id_responsable_apertura'];

			if ($id_responsable_apertura !="") {
			$sql_responsable="select nombre,apellido,interno,mail,movil
							  from responsables_apertura
					 		  join usuarios using(id_usuario)
					 		  where id_responsable_apertura=$id_responsable_apertura";
			$res_responsable=sql($sql_responsable) or fin_pagina();
			        $value="   En la\n apertura\n VER";
			        $estilo="style='width:65px';'height:50px'";
			        $interno=$res_responsable->fields['interno'];
			        $mail=$res_responsable->fields['mail'];
			        $celular=$res_responsable->fields['movil'];
			        $nombre=$res_responsable->fields['apellido']." ".$res_responsable->fields['nombre'];
			        $apertura="<td align='right' rowspan='3' width='25%'> <input type='button' name='ver_apertura' value='$value' onClick=\"window.open('$link','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=450');\" $estilo> </td>
			                   <td align='right' rowspan=3 width='50%'> <b>Responsable de Apertura:</b><br>$nombre <br> <b>Int: </b>$interno <br> <b> Movil: </b>$celular <br> <b>Mail: </b>$mail <br>
			                   </td>";
			} else {
	               $apertura="<td align='right' width='25%' colspan=2>
	                <input type='button' name='ver_apertura' value='En la apertura VER' style='width:160;'
	                 onClick=\"window.open('$link','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=450');\"> </td>";
			}

			if($result->fields['candado']!=0)
			     echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Esta licitacion solo puede verse, pero no modificarse'> ";
			echo "<font size=4><b>ID:</b> ".$result->fields["id_licitacion"]."</font><br></td>";
			echo $apertura;
			echo "</tr>";
			echo "<tr><td>";
			if (permisos_check("inicio","permiso_candado"))
                  $tiene_permiso_candado="";
                else
                  $tiene_permiso_candado="disabled";
			if($result->fields['candado']==0)
			{ $link_self_poner= encode_link("licitaciones_view.php",array("cmd1"=>"candadoponer","ID"=>$ID));
			  echo "<input type=button name=det_candado $tiene_permiso_candado style='width:160;' value='Poner Candado' onClick=\"document.location='".$link_self_poner."';\"></td>";
			}
			else
			{$link_self_sacar = encode_link("licitaciones_view.php",array("cmd1"=>"candadosacar","ID"=>$ID));
			 echo "<input type=button name=det_candado $tiene_permiso_candado style='width:160;' value='Sacar Candado' onClick=\"document.location='".$link_self_sacar."';\"></td>";
			}
			echo "</td>";

			echo "</td>";
			echo "</tr>";
			echo "<tr> <td>";
            echo "<b>Lider:</b> $lider <br>";
            echo "<b>Patrocinador: </b>$patrocinador";
            echo "</td>";

            echo "</tr>";
			echo "</table>";
			echo "</td>\n";
			echo "<td  width=50% align=left><b>Apertura: <font color='blue'>$da/$ma/$ya</font></b><br><b>Hora: <font color='blue'>$ha</font></b><br><b>Tipo Norma: "; if ($result->fields['nombre_normas']!="") echo "<font color='blue'>".$result->fields['nombre_normas']."</font>"; elseif ($result->fields['iso9001']=="t") echo "<font color='blue'>Exige la Norma ISO9001</font>"; else echo "<font color='red'><b>?????</b></font>";
            echo "<br><b>Se pueden cotizar Alternativas: </b>"; if ($result->fields['cotizar_alternativas']==1) echo "<font color='blue'><b>Si</b></font>"; elseif ($result->fields['cotizar_alternativas']==0) echo "<font color='blue'><b>No</b></font>"; else echo "<font color='red'><b>?????</b></font>";
            echo "<br><b>Monitor, CPU, Teclado y Mouse: </b>"; if ($result->fields['nombre_componentes']!="") echo "<b><font color='blue'>".$result->fields['nombre_componentes']."</font></b>"; else echo "<font color='red'><b>?????</b></font>";
			echo "</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left colspan=1>";
			echo "<table width='100%'>" ;
			echo "<tr>";
			echo "<td valign='top'>";
            $id_entidad=$result->fields["id_entidad"];
			echo "<input type=hidden name='id_entidad' value='$id_entidad'>";
			$perfil=encode_link("./perfil_entidad.php",array("id_entidad"=>$id_entidad,"modulo"=>"Licitaciones"));
			echo "<input type='button' name='Nuevo' Value='Perfil' style=\"width:100%\" onclick=\"window.open('$perfil','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400');\">";
			//Boton para ver las licitaciones por clientes
            echo "<br><br>";
			$link=encode_link("licitaciones_por_entidad.php",array("id_entidad"=>$id_entidad));
            echo "<input type=button name=lic_entidades value='A.E.' style=\"width:100%\" title='Analizar Entidad' onclick=\"window.open('$link')\">";
			if (permisos_check("inicio","boton_sinc_entidad")) $sinc_entidad="";
			else $sinc_entidad="disabled";
			echo "</td>";
			echo "<td width='95%'>";
			echo "<b>Distrito:</b> ".$result->fields["nombre_distrito"]."\n";
			$nombre_entidad=$result->fields["nombre_entidad"];
			echo "<input type=hidden name='nombre_entidad' value='$nombre_entidad'>";
			echo "<br><b>Entidad:</b> $nombre_entidad &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<input $sinc_entidad type=submit style='background: red;color:white' name='sinc_entidad' value='Sincronizar Entidad'>\n";
//			echo "<br><b>Tipo de Entidad:</b> ".html_out($result->fields["tipo_entidad"])."</td>\n";
			echo "<br><b>Dirección: </b>" .html_out($result->fields["dir_entidad"]);
			echo "<br><b>Tipo de Entidad:</b> ".((html_out($result->fields["tipo_entidad"])=="")?"<br><font color=red size=+1>Falta definir un tipo para la Entidad</font>":html_out($result->fields["tipo_entidad"]));

			echo "</td>";
			echo "</tr>";
			echo "</table>";
            echo "</td>";
			echo "<td align=left>";
                        //incluyo la funcion que verifica si hay contactos
			echo "<table width='100%' align='right' border=0>";
			echo "<tr align='right'>";
			echo "<td align='right'>";
			$nuevo_contacto=encode_link("../general/contactos.php",array("modulo"=>"Licitaciones",
										 "id_licitaciones"=>$ID,
										 "id_general"=>$result->fields['id_entidad']));
			//echo "<input type='button' name='gestiones' value='Gestiones generales' onclick='parent.window.location=\"".encode_link("../../index.php",Array ("menu"=>"lic_gestiones","extra"=>Array ("cmd1"=>"detalle","id"=>$ID)))."\";'>\n";
			echo "<input type='button' name='Nuevo' Value='Nuevo Contacto' style=\"width:30%\" onclick=\"window.open('$nuevo_contacto','','toolbar=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
			echo "</td>";
			echo "</tr>";
			echo "<tr align='right'>";
			echo "<td>";
			contactos_existentes("Licitaciones",$result->fields['id_entidad']);
			echo "</td>";
			echo "</tr>";
			echo "</table>";
			echo "</td>";
			echo "</tr><tr>\n";
			echo "<td align=left><b>Mantenimiento de oferta:</b> "; if ($result->fields["mantenimiento_oferta"]!="") echo "".$result->fields["mantenimiento_oferta"]." días ".$result->fields["mant_oferta_especial"]."\n"; else echo $result->fields["mant_oferta_especial"];
			echo "<br><b>Forma de pago:</b> ".$result->fields["forma_de_pago"]."\n";
			echo "<br><b>Plazo de entrega</b>: \n";
			echo $result->fields["plazo_entrega"];
			echo "<br><b>Fecha de entrega</b>: \n";
   			if ($result->fields["fecha_entrega"] != "") {
				echo fecha($result->fields["fecha_entrega"])."\n";
			    }
			    else {
                     echo "N/A\n";
                     }
			echo "</td>\n";
			echo "<td align=right valign=top><b>Número:</b> ".html_out($result->fields["nro_lic_codificado"])." <br>\n";
			//este control es porque las licitaciones que ya estan cargadas no tiene nuemro de expediente
			if ($result->fields["exp_lic_codificado"]=="") $expediente=0;
			else $expediente=$result->fields["exp_lic_codificado"];
			echo "<b>Expediente:</b> ".html_out($expediente)."\n";
			echo "<br><b>Valor del pliego:</b> \$".formato_money($result->fields["valor_pliego"])."</td>\n";
			echo "</tr>\n";

//Muestra los montos ganados, estimados etc, mas el estado de la licitacion
			echo "<tr>";
			echo "<td align=left valign=top>";
            echo "<b>Moneda:</b> ".$result->fields["nombre_moneda"]."</b>\n";
			echo "<br><b>Ofertado:</b> ".formato_money($result->fields["monto_ofertado"])."\n";
			echo "<br><b>Estimado:</b> ".formato_money($result->fields["monto_estimado"])."\n";
			echo "<br><b>Ganado:</b> ".formato_money($result->fields["monto_ganado"]);
            echo "</td>\n";
            echo "<td  align=center valign=middle >";
            $link_estados_renglon=encode_link("lic_est_esp.php",array("id"=>$ID,"ver"=>1));
            echo "<a href=$link_estados_renglon target='_blank'>";
            echo "<b><font color=blue size=2><u>Estado:</u></font></b>\n";
            echo "<span style='background-color: ".$result->fields["color_estado"]."; border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;'>";
            echo "&nbsp;&nbsp;&nbsp;</span> ";
            echo "<font size=4 color=red>";
            echo $result->fields["nombre_estado"];
            echo "</font>";
            echo "</a>";
            echo "</td>";
            echo "</tr>";
            echo  "</td>";
			echo "</tr>";

            echo "<tr><td colspan=2>";
             fechas_entregas($ID);
            echo "</td></tr>";
            echo "<tr><td colspan=2><br></td></tr>";
            echo "<tr><td colspan=2>";
              muestra_licitacion($ID);
            echo "</td></tr>";
/*
			echo "<tr>";
			echo "<td align=left valign=top>";
            echo "<b>Moneda:</b> ".$result->fields["nombre_moneda"]."</b>\n";
			echo "<br><b>Ofertado:</b> ".formato_money($result->fields["monto_ofertado"])."\n";
			echo "<br><b>Estimado:</b> ".formato_money($result->fields["monto_estimado"])."\n";
			echo "<br><b>Ganado:</b> ".formato_money($result->fields["monto_ganado"]);
            echo "</td>\n";
            echo "<td  align=right valign=top>";
            echo "<table width=100% align=center>";
            echo "<tr>";
            $link_estados_renglon=encode_link("lic_est_esp.php",array("id"=>$ID,"ver"=>1));
            echo "<td align=right>";
            echo "<a href=$link_estados_renglon target='_blank'>";
            echo "<b><font color=blue><u>Estado:</u></font></b>\n";
            echo "<span style='background-color: ".$result->fields["color_estado"]."; border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;'>&nbsp;&nbsp;&nbsp;</span> ".$result->fields["nombre_estado"];
            echo "</a>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>";
            mostrar_ordenes_compra($ID);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            echo  "</td>";
			echo "</tr>";
*/
			echo "<input type=hidden name=ID value='$ID'>\n";
			//echo "</table>";
///////////////////////////////////////////////////////////////
// se arman 2 tablas una con los items q faltan cargar q se muestran con signos ? TABLA_SIGNO
// la otra tabla muestra la informacion q haya sido cargada TABLA_DETALLE
            $mostrar_signo=0;
            $no_mostrar_signo=0;
           	$tabla_signo="<div id='tabla_signo' style='display:none'><table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center id='tabla_menu'>";
			$tabla_detalle="<table id='tabla_menu' width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center>";

			if ($result->fields['id_garantia_de_oferta']=="") {
				$tabla_signo.="<tr><td><u><b>Garantía de Oferta</b></u>&nbsp;&nbsp;<font size='5' color='red'><b>?</b></font></td>";
			    $mostrar_signo=1;
			 }
			else {
			    $tabla_detalle.="<tr><td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;<u><b>Garantía de Oferta</b></u>";
			    $tabla_detalle.="<br><b>Tipo Moneda: </b>"; while (!$result_moneda->EOF && $result_moneda->fields['id_moneda']!=$result->fields['id_moneda_ga_oferta']) {$result_moneda->MoveNext();} $tabla_detalle.=$result_moneda->fields['nombre'];
			    $tabla_detalle.="<br><b>Monto Garantía: </b>"; if ($result->fields['monto_ga_oferta']!="") $tabla_detalle.=$result->fields['monto_ga_oferta']; else $tabla_detalle.="<font size='2' color='red'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Porcentaje de la Garantía: </b>"; if ($result->fields['porcentaje_ga_oferta']!="") $tabla_detalle.=$result->fields['porcentaje_ga_oferta']; else $tabla_detalle.="<font size='2' color='red'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Exige Reaseguro: </b>"; if ($result->fields['reaseguro_ga_oferta']==1) $tabla_detalle.="Si"; elseif ($result->fields['reaseguro_ga_oferta']==0 && $result->fields['reaseguro_ga_oferta']!="") $tabla_detalle.="No"; else $tabla_detalle.="<font color='red' size='2'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Tipo de Garantía: </b>"; if ($result->fields['id_tipo_garantia_ga_oferta']!="") {while (!$result_tipo_garantia->EOF && $result_tipo_garantia->fields['id_tipo_garantia']!=$result->fields['id_tipo_garantia_ga_oferta']) {$result_tipo_garantia->MoveNext();} $tabla_detalle.=$result_tipo_garantia->fields['nombre'];} else $tabla_detalle.="<font color='red' size='2'><b>?????</b></font>";
			    $tabla_detalle.="</td>";
			    $no_mostrar_signo=1;
			}

			if ($result->fields['id_garantia_contrato']=="") {
			    $tabla_signo.="<td><u><b>Garantía de Contrato y/o Adjudicación</b></u>&nbsp;&nbsp;<font size='5' color='red'><b>?</b></font></td></tr>";
			    $mostrar_signo=1;
			 }
			else {
			    $tabla_detalle.="<td>&nbsp;&nbsp;&nbsp;&nbsp;<u><b>Garantía de Contrato y/o Adjudicación</b></u>";
			    $tabla_detalle.="<br><b>Tipo Moneda: </b>"; while (!$result_moneda->EOF && $result_moneda->fields['id_moneda']!=$result->fields['id_moneda_ga_contrato']) {$result_moneda->MoveNext();} $tabla_detalle.=$result_moneda->fields['nombre'];
			    $tabla_detalle.="<br><b>Monto Garantía: </b>"; if($result->fields['monto_ga_contrato']!="") $tabla_detalle.=$result->fields['monto_ga_contrato']; else $tabla_detalle.="<font size='2' color='red'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Porcentaje de la Garantía: </b>"; if($result->fields['porcentaje_ga_contrato']!="") $tabla_detalle.=$result->fields['porcentaje_ga_contrato']; else $tabla_detalle.="<font size='2' color='red'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Exige Reaseguro: </b>"; if ($result->fields['reaseguro_ga_contrato']==1) $tabla_detalle.="Si"; elseif ($result->fields['reaseguro_ga_contrato']==0 && $result->fields['reaseguro_ga_contrato']!="") $tabla_detalle.="No"; else $tabla_detalle.="<font color='red' size='2'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Tipo de Garantía: </b>"; if ($result->fields['id_tipo_garantia_ga_contrato']!="") {while (!$result_tipo_garantia->EOF && $result_tipo_garantia->fields['id_tipo_garantia']!=$result->fields['id_tipo_garantia_ga_contrato']) {$result_tipo_garantia->MoveNext();} $tabla_detalle.=$result_tipo_garantia->fields['nombre'];} else $tabla_detalle.="<font color='red' size='2'><b>?????</b></font>";
			    $tabla_detalle.="<br><b>Vigencia: </b>"; if ($result->fields['vigencia']!="") $tabla_detalle.=$result->fields['vigencia']." días ".$result->fields['dias_tipo_ga_contrato']; else $tabla_detalle.="<font size='2' color='red'><b>?????</b></font>";
			    $tabla_detalle.="</td></tr>";
			    $no_mostrar_signo=1;
			}

			if ($result->fields["id_impugnacion"]=="") {
			    $tabla_signo.="<tr><td colspan=2><u><b>Impugnación</b></u>&nbsp;&nbsp;<font size='5' color='red'><b>?</b></font></td></tr>";
			    $mostrar_signo=1;
			 }
			else {
			    $tabla_detalle.="<tr><td colspan=2><u><b>Impugnación</b></u>";
			    $tabla_detalle.="<br><b>Plazo de Impugnación: </b>".$result->fields['cant_dias']." días ".$result->fields['dias_tipo_imp']; $tabla_detalle.="&nbsp;&nbsp;&nbsp;<b>Comentario:</b> ".$result->fields['plazo'];
			    $tabla_detalle.="<br><b>Garantía de Impugnación: </b>"; if ($result->fields['porcentaje_imp']!=-1) $tabla_detalle.=$result->fields['porcentaje_imp']; else $tabla_detalle.="&nbsp;&nbsp;&nbsp;&nbsp;"; $tabla_detalle.=" %"; $tabla_detalle.="&nbsp;&nbsp;&nbsp;<b>Comentario:</b> ".$result->fields['porcentaje_texto'];
			    $tabla_detalle.="<br><b>Presupuesto Oficial: </b>"; $tabla_detalle.="  ".$result->fields['monto_imp']."&nbsp;&nbsp;&nbsp;";  while (!$result_moneda->EOF && $result_moneda->fields['id_moneda']!=$result->fields['id_moneda_imp']) {$result_moneda->MoveNext();} $tabla_detalle.=$result_moneda->fields['nombre'];
			    $tabla_detalle.="</td></tr>";
			    $no_mostrar_signo=1;
			}

			if ($result->fields['comentario_garantia_bienes']=="") {
			    $tabla_signo.="<tr><td colspan=2><u><b>Garantía de los Bienes</b></u>&nbsp;&nbsp;<font size='5' color='red'><b>?</b></font></td></tr>";
			    $mostrar_signo=1;
			 }
			else {
			    $tabla_detalle.="<tr><td colspan='2' ><u><b>Garantía de los Bienes</b></u>";
			    $tabla_detalle.="<br>".$result->fields['comentario_garantia_bienes'];
			    $tabla_detalle.="</td></tr>";
			    $no_mostrar_signo=1;
			}

			if ($result->fields['exige_muestras']=="" || $result->fields['exige_muestras']==-1) {
			    $tabla_signo.="<tr><td colspan=2><u><b>Muestras</b></u>&nbsp;&nbsp;<font size='5' color='red'><b>?</b></font></td></tr>";
			    $mostrar_signo=1;
			 }
			else {
                $tabla_detalle.="<tr><td colspan=2><u><b>Muestras</b></u>";
			    $tabla_detalle.="<br><b>Exige Muestras:</b> "; if ($result->fields['exige_muestras']==1) $tabla_detalle.="Si"; else $tabla_detalle.="No";
			    $tabla_detalle.="<br><b>Vencimiento de la Presentación de las Muestras: </b>".fecha($result->fields['vencimiento_muestras']);
			    $tabla_detalle.="<br><u><b>Comentarios sobre las Muestras:</b></u><br>".$result->fields['comentarios_muestras'];
			    $tabla_detalle.="</td></tr>";
			    $no_mostrar_signo=1;
			}

			if ($result->fields['registro_proveedores']==-1) {
			    $tabla_signo.="<tr><td colspan=2><u><b>Para los Distritos</b></u>&nbsp;&nbsp;<font size='5' color='red'><b>?</b></font></td></tr>";
			    $mostrar_signo=1;
			 }
			else {
			    $tabla_detalle.="<tr><td colspan=2><u><b>Para los Distritos</b></u>";
				$tabla_detalle.="<br><b>Tenemos la Inscripción en el Registro de Proveedores:</b> "; if ($result->fields['registro_proveedores']=="si") $tabla_detalle.="Si"; else $tabla_detalle.="Hay que Gestionar";
			    if ($result->fields['avisar_antes']==1) $tabla_detalle.="<br><b>Avisar 3 Días Hábiles anteriores a la apertura porque debe salir antes.</b>";
			    $tabla_detalle.="</td></tr>";
			    $no_mostrar_signo=1;
			}

			$tabla_signo.="</table></div>";
			//$tabla_detalle.="</table>";
///////////////////////////////////////////////////////////////

			if ($mostrar_signo==1) {
				echo "<tr>";
			    echo "<td colspan=2>";
			    echo "<input type=hidden name='tabla' value='0'>";
			    echo "<img name='signo' src='$html_root/imagenes/signo_preg.JPG' border='0' width='20' height='20' title='Ver Detalles' onClick='mostrar_tabla();'>";
			    echo "&nbsp;&nbsp;<u><b>Detalles</b></u></td></tr></table>";
			    echo $tabla_signo;
			  }

			if ($no_mostrar_signo==1) echo $tabla_detalle;
			//echo "</td></tr>";
			echo "<table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center>";
			//////////////////////////////////////////////////////////////////
                        /*muestro los eventos*/
                        $sql="select * from eventos_lic where id_licitacion=$ID";
                        $sql.=" order by fecha";
                        $resultado=sql($sql) or fin_pagina();
                        $cant_eventos=$resultado->recordcount();
                        if ($cant_eventos) {
                        echo "<tr>";
                        echo "<td valign=top colspan=2>";
                        echo "<table width='100%'  border='1' cellpadding='2' cellspacing='1'>";
                        echo "<tr><td colspan=2 align=center>";
                        //echo "<input type=checkbox class='estilos_check' name=eventos onclick='javascript:(this.checked)?Mostrar(\"detalles_muestras\"):Ocultar(\"detalles_muestras\");' title=\"Detalles de Muestras\">
			            //      &nbsp;&nbsp;&nbsp;";
                        echo "<b>Eventos</b></td></tr>";
                        //echo "<tr><td><div id='eventos' style='display:none'><table>";
			   	        if ($cmd=="proximas") {
                        echo "<tr>";
                        echo "<td colspan=2>";
                        echo "<table width=100% align=right  border=0>";

                        echo "<tr>";

                        echo "<td width=33% >";
                        echo "<table  cellspacing=0 cellpadding=0 wdith=100%><tr>";
                        echo "<td width=15 bgcolor='$cuatro_dias' bordercolor='#000000' height=15>&nbsp;</td>\n";
                        echo "<td ><b>Cuatro Días</td>\n";
                        echo "</tr></table></td>";

                        echo "<td width=33% >";
                        echo "<table cellspacing=0 cellpadding=0 wdith=100%><tr>";
                        echo "<td width=15 bgcolor='$dos_dias' bordercolor='#000000' height=15>&nbsp;</td>\n";
                        echo "<td ><b>Dos Días</td>\n";
                        echo "</tr></table></td>";


                        echo "<td width=33% >";
                        echo "<table  cellspacing=0 cellpadding=0 wdith=100%><tr>";
                        echo "<td width=15 bgcolor=$vencido bordercolor='#000000' height=15>&nbsp;</td>\n";
                        echo "<td ><b>Vencido</td>\n";
                        echo "</tr></table></td>";
                        echo "</tr>";
                        echo "</table>";


                       echo "</td>";
                       echo "</tr>";
                        }
                        echo "<tr>";
                        echo "<td  align=center width=20%> <b> Fecha       </td>";
                        echo "<td  align=center width=75%> <b>Descripción  </td>";
                        echo "</tr>";
                         for($i=0;$i<$cant_eventos;$i++){
                              $id_evento     =$resultado->fields["id_eventos"];
                              $fecha=$resultado->fields["fecha"];
                              $fecha_evento  =substr($fecha,0,10);
                              $hora_evento   =substr($fecha,11,8);

                              $usuario_evento=$resultado->fields["usuario"];
                              $estado=$resultado->fields["activo"];
                              $evento        = str_replace(chr(13).chr(10),"<n>",$resultado->fields["evento"]);
                              $fecha_hoy=date("d/m/Y");
                              $dias=diferencia_dias_habiles($fecha_hoy,fecha($fecha));

                              $bgcolor="$bgcolor3";
                              if(($dias>2)&&($dias<=4)) $bgcolor="$cuatro_dias";
                              if(($dias>0)&&($dias<=2)) $bgcolor="$dos_dias";
                              if($dias==0) $bgcolor="$vencido";
                              if ($estado==1 || $cmd!="proximas") $bgcolor="$bgcolor3";

                              echo "<tr bgcolor='$bgcolor'>\n";
                              echo "<td align=center valign=top>";
                              echo "<b>".Fecha($fecha_evento)." $hora_evento<br>";
                              echo "<br>$usuario_evento</b>";
                              echo "</td>\n";
                              echo "<td>";
                              echo "<textarea  disabled name=evento_$i style='width:100%;heigth=100%' rows=4>";
                              echo $evento.$dias;
                              echo "</textarea>";
                              echo "</td>\n";
                              echo "</tr>\n";
                              $resultado->MoveNext();
                              }

                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                        //echo "</table></div></td></tr>";
                        } //del if de cantidad de eventos
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
			echo "<tr>\n";
			echo "<td align=left colspan=2><b>Comentarios/Seguimiento</b><br>".html_out($result->fields["observaciones"])."</td>\n";
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
			   {
                            echo "<tr>\n";
			    echo "<td align=left colspan=2><b>Protocolo Legal:<font color=\"red\"> No se ha cargado el protocolo legal</font></td>\n";
			    echo "</tr>\n";
			  }

			echo "<tr><td align=left colspan=2><b>CD DE OFERTA: </b>";
			//directorio donde esta la imagen del cd
			if (file_exists(UPLOADS_DIR."/Licitaciones/$ID/CD_OFERTA_$ID.zip"))
			{
				echo "<b style='color:#006600;'>Ya se creó el CD DE OFERTA  </b>";
 				echo "<input type='button' value='Ver archivos' name='btn_ver' onclick=\"window.open('".encode_link("cd_oferta.php",array("id_lic"=>$ID))."','','toolbar=1,location=1,directories=0,status=1, menubar=1,scrollbars=1')\">";
			}
			else
			{
			 echo "<b style='color:red' >No se ha creado el CD DE OFERTA  </b>" ;
			 //echo "<input type='button' value='Crear CD' name='btn_cargar' onclick=\"window.open('".encode_link("cd_oferta.php",array("id_lic"=>$ID))."','','toolbar=1,location=1,directories=0,status=1, menubar=1,scrollbars=1')\">";
                         echo "<input type='button' value='Crear CD' name='btn_cargar' onclick=\"window.open('".encode_link("cd_oferta.php",array("id_lic"=>$ID))."')\">";
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
	   /*echo "<tr>";
       echo "<td colspan=2 align=center>";
	     if($result->fields['candado']!=0)
	       echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Esta licitacion solo puede verse, pero no modificarse'> ";
	       echo "<font size=3><b>ID: ".$result->fields["id_licitacion"]."</b></font>";
             echo "</td>";
             echo "</tr>";
	     echo "<tr>\n";
             echo "<input type=hidden name=ID value='$ID'>\n";
	     echo "<td  colspan=2 align=center width=100% valign=top>\n";
	     echo "<tr>";*/

     echo "<tr>";
     echo "<td colspan=2 >";
     echo "<table> <tr><td align='left' width='75%'>";
	   if($result->fields['candado']!=0)
	        echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Esta licitacion solo puede verse, pero no modificarse'> ";
	 echo "<font size=3><b>ID: ".$result->fields["id_licitacion"]."</b></font>";
     //echo "</td>";
	 echo "<input type=hidden name=ID value='$ID'>\n";
     echo "</td>";
	 if ($parametros["link_volver"])
	         $link_volver = $parametros["link_volver"];
	 else
	         $link_volver = "document.location='".$_SERVER["PHP_SELF"]."';";
	echo "<td align='right'>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type=submit name=det_delfile  $disabled value='Eliminar Archivos' onClick=\"return confirm('ADVERTENCIA: Se van a eliminar los archivos seleccionados');\">";
            echo "<input type=button name=det_volver style='width:160;' value='Volver' onClick=\"$link_volver\">";

    echo "</td></tr></table>";
    echo "</td>\n";
	echo "</tr>";

    }

     echo"</td>";
     echo"</table>";
	echo "</td></tr></table>\n";
		///////////////////// Barra de botones flotantes /////////////////////////

		//echo "<div id='mover' style='background-color: white;border: solid;border-width: 1px;position: absolute;float: left;'>";
		$inner="<table width=100% bgcolor='#ffffff' align=center border=0>";
		     //
if ($ver_papelera) {
          $inner.="<tr>";
          $inner.="<td  colspan=2 align=center>";
		  $inner.="<input type=submit name=det_restore style='width:160;' value='Restaurar Licitación' onClick=\"return confirm('ADVERTENCIA: Se va a restaurar la Licitación número $ID');\">&nbsp;&nbsp;&nbsp;";
          $inner.="</td>";
          $inner.="</tr>";
}
else {
    //chequeamos si el usuario que se ha logueado, tiene permiso para ver el boton de eliminar o no. Si tiene lo mostramos, sino ,esta deshabilitado.
  if (permisos_check("inicio","eliminar_lic"))
                                 $disabled_eliminar_lic="";
                                 else
                                 $disabled_eliminar_lic="disabled";
     //si el candado esta puesto, deshabilitamos los botones de Eliminar Archivos y Eliminar Licitaciones
  if($result->fields['candado']==0)
                               {
                               $disabled_cargar_protocolo="";
                               $disabled_eliminar_archivos="";
                               }
                               else
                               {
                               $disabled_eliminar_archivos =" disabled";
                               $disabled_cargar_protocolo=" disabled";
                               }




   /*
   $disabled="";
   if($result->fields['candado']!=0)  {
                                $disabled="disabled";
                                $tiene_permiso="disabled";
	                            }
   */
    //link y disableds para resultados coradir
   if (permisos_check("inicio","resultados_coradir"))
                                $disabled_resultados_coradir="";
                                else
                                $disabled_resultados_coradir="disabled";
   if (permisos_check("inicio","resultados_cargados"))
                                $disabled_resultados_cargados="";
                                else
                                $disabled_resultados_cargados="disabled";

   if ($result->fields["nombre_estado"] == "Entregada" or $result->fields["nombre_estado"] == "Orden de compra")
                                $disabled_seguimientos_cobros="";
	                            else
                                $disabled_seguimientos_cobros="disabled";
   if (obtener_preferencias($_ses_user['login'],"resultado"))
                            {
                             $display_tercera="block";
                             $checked_resultado="checked";
                            }
                             else {
                                 $display_tercera="none";
                                 $checked_resultado="";
                                 }

   if (obtener_preferencias($_ses_user['login'],"asociaciones"))
                           {
                           $display_cuarta="block";
                           $checked_asociaciones="checked";
                           }
                           else{
                               $display_cuarta="none";
                               $checked_asociaciones="";
                               }
     $style="style='width:80%;'";
     $style_2="style='width:90%;'";
     $style_3="style='width:98%;'";
	 $style_4="style='width:48%;'";



      if ($parametros["link_volver"])
	               $link_volver = $parametros["link_volver"];
	               else
	               $link_volver = "document.location='".$_SERVER["PHP_SELF"]."';";

     $inner.="<table width=100% align=center border=0>";
     $inner.="<tr>";
      $inner.="<td>";
      $inner.="<input type=button name=det_volver style='width:160;' value='Volver' onClick=\"$link_volver\">";
      ////////////////////////////// GABRIEL /////////////////////////////////
      $inner.="<input type='button' name='mailto' value='Mail To ...' onclick='document.location.href=
				\"mailto:?subject=Mail%20licitación%20id%20".$ID."&body=Id:%20".$ID."%0D%0A\"+
				\"Entidad:%20".$result->fields["nombre_entidad"]."%0D%0A\"+
				\"Dirección:%20".$result->fields["dir_entidad"]."%0D%0A\"+
				\"Número:%20".$result->fields["nro_lic_codificado"]."%0D%0A\"+
				\"Expediente:%20".$result->fields["exp_lic_codificado"]."%0D%0A\"+
				\"Fecha%20de%20apertura:%20".Fecha(substr($result->fields["fecha_apertura"],0, 10))."%0D%0A\"+
				\"Hora%20de%20apertura:%20".substr($result->fields["fecha_apertura"], 11)."%0D%0A\"+
				\"Líder:%20".$result->fields["nombre_lider"]."%0D%0A\"+
				\"Patrocinador:%20".$result->fields["nombre_patrocinador"]."%0D%0A\"'>";
			////////////////////////////////////////////////////////////////////////
             $inner.="</td>\n";
                 $inner.="<td  valign=top>";
                    $inner.="<table id='loscheck' width=30% align=right border=1 cellspacing=1 cellpading=1>\n";
                      $inner.="<tr bgcolor=$bgcolor3>\n";
                        $inner.="<td width=5% align=center>\n";
                        $inner.="<input type=checkbox class='estilos_check' $checked_resultado name=det  onclick='Mostrar_check(this.checked,\"tabla_detalles_tercera\");'>\n";                        $inner.="</td>";
                        $inner.="<td align=center>\n";
                        $inner.="<font color=red>\n";
                        $inner.="Resultados\n";
                        $inner.="</font>\n";
                        $inner.="</td>\n";
                        $inner.="<td width=5% align=Center>\n";
                        $inner.="<input type=checkbox class='estilos_check' $checked_asociaciones name=det  onclick='Mostrar_check(this.checked,\"tabla_detalles_cuarta\");'>\n";
                        $inner.="</td>\n";
                        $inner.="<td align=center>\n";
                        $inner.="<font color=red>\n";
                        $inner.="Asociaciones\n";
                        $inner.="</font>\n";
                        $inner.="</td>\n";
                     $inner.="</tr>\n";
                    $inner.="</table>\n";
                 $inner.="</td>\n";
				 //$inner.="<td>";
						/*$inner.="<input title='Ocultar Barra de Botones' type=button name=mos_ocu value='O' onclick='
		if (this.value==\"O\") {
			this.value=\"M\";
			this.title=\"Mostrar Barra de Botones\";
			mover.style.top=(document.body.clientHeight-27)+document.body.scrollTop;
			mover.style.heigth=27;
			loscheck.style.visibility=\"hidden\";
		}
		else {
			this.value=\"O\";
			this.title=\"Ocultar Barra de Botones\";
			mover.style.top=(document.body.clientHeight-top)+document.body.scrollTop;
			loscheck.style.visibility=\"visible\";
		}'>";*/
					//	$inner.="</td>";
                    $inner.="</tr>\n";
                    $inner.="<tr>\n";
                       $inner.="<td widht=100% align=left colspan=3 valign=top>\n";
                        $inner.="<div id='tabla_detalles_primera' style='display:block'>\n";
                        $inner.="<table width=100% align=center>\n";
                        $inner.="<tr>\n";
                        $inner.="<td width=25% align=left>\n";
                        //$link=encode_link("realizar_oferta.php",array("ID"=>$ID));
                        //echo "<input type=button name=det_oferta1 class='estilos_boton' $style value='Realizar Oferta' onClick=\"window.open('$link','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=550');\">";
                        $link=encode_link("licitaciones_renglones.php",array("ID"=>$ID,"pv"=>1));
                        $inner.="<input type=button name=det_oferta class='estilos_boton' $style value='Realizar Oferta' onClick=\"window.open('$link','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=550');\">\n";
                        $inner.="</td>\n";
                        $inner.="<td width=25% align=center>\n";
                        $inner.="<input type=submit name=det_edit  class='estilos_boton' $style value='Modificar'>\n";
                        $inner.="</td>\n";
                        $inner.="<td width=25% align=right>\n";
                        $inner.="<input type=submit name=det_addfile class='estilos_boton' $style value='Agregar archivo'>\n";
                        $inner.="</td>\n";
                        $inner.="</tr>\n";
                        $inner.="</table>\n";
                        $inner.="</div>\n";
                   $inner.="</td>\n";
               $inner.="</tr>\n";
               $inner.="<tr>\n";
                 $inner.="<td widht=100% align=left colspan=3 valign=top>\n";
                 $inner.="<div id='tabla_detalles_segunda' style='display:block'>\n";
                 $inner.="<table width=100% align=left border=0>\n";
                 $inner.="<tr>\n";
                 $inner.="<td align=left width=33% >\n";
		         $inner.="<input type=button name=det_protocolo class='estilos_boton'  $style $disabled_cargar_protocolo value='Cargar Protocolo' onClick=\"document.location='".encode_link("protocolo_legal.php",array("id_lic"=>$ID,"num"=>$result->fields["nro_lic_codificado"]))."'; return false;\">\n";
                 $inner.="</td>\n";
                 $inner.="<td align=center width=33% >\n";
		         $inner.="<input type=submit name=det_del  class='estilos_boton'  $style $disabled_eliminar_lic value='Eliminar Licitación' onClick=\"return confirm('ADVERTENCIA: Se va a enviar a la papelera la Licitación número $ID');\">\n";
                 $inner.="</td>\n";
                 $inner.="<td align=right width=33% >\n";
		         $inner.="<input type=submit name=det_delfile class='estilos_boton' $style $disabled_eliminar_archivos value='Eliminar Archivos' onClick=\"return confirm('ADVERTENCIA: Se van a eliminar los archivos seleccionados');\">\n";
                 $inner.="</td>\n";
                 $inner.="</tr>\n";
                 $inner.="</table></div>\n";
                 $inner.="</td>\n";
               $inner.="</tr>\n";
            $inner.="<tr>\n";
               $inner.="<td colspan=3 widht=100% align=center  valign=top>\n";

              $inner.="<div id='tabla_detalles_tercera' style='display:$display_tercera'>\n";
                        $inner.="<table width=100% align=center bgcolor=''>\n";
                        $inner.="<tr>\n";
                        $inner.="<td align=left width=25% >\n";
	                    $inner.="<input type=button name=det_ver_res class='estilos_boton' $style_3 value='Ver Resultados' onClick=\"document.location='".encode_link("lic_ver_res.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic","pagina_volver"=>"licitaciones_view.php"))."';\">\n";
						// $inner.="<input type=button name=det_ver_res class='estilos_boton' $style_3 value='Ver Resultados' onClick=\"document.location='".encode_link("lic_ver_res2.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic","pagina_volver"=>"licitaciones_view.php"))."';\">\n";
	                    $inner.="</td>\n";
                        $inner.="<td align=center width=25% >\n";
	                $inner.="<input type=button name=det_cargar_res class='estilos_boton' $style_3 value='Cargar Resultados' onClick=\"document.location='".encode_link("lic_cargar_res.php",array("id_lic"=>$ID,"pagina_viene"=>"licitaciones_view.php"))."'; return false;\">\n";
                        $inner.="</td>\n";
                        $inner.="<td align=center width=25% >\n";
                        $link_self_result_coradir = encode_link("licitaciones_view.php",array("cmd1"=>"resultado_coradir","ID"=>$ID));
	                $inner.="<input type=button name=det_result_coradir class='estilos_boton' $style_3 value='Cargar Resultados Coradir' $disabled_resultados_coradir onClick=\"document.location='$link_self_result_coradir';\">\n";
                        $inner.="</td>\n";
                        $inner.="<td align=center width=25% >\n";
                        $inner.="<input type=submit name='resultados_cargados' class='estilos_boton' $style_3 $disabled_resultados_cargados value='Finalizar Carga Resultados'>\n";
                        $inner.="</td>\n";

                        // echo "<td align=right width='20%'>";
                       // $link_resultados_licitaciones=encode_link('imprimir_planilla.php',array('ID'=>$ID,'entidad'=>$result->fields['nombre_entidad']));
                       // echo "<input type='button' class='estilos_boton' $style_3  name='imprimir_resultado' value='Imprimir Planilla' onclick=\"window.open('$link_resultados_licitaciones','','left=40,top=80,width=700,height=250,resizable=1,status=0');\">";
                       // echo "</td>";

                    $inner.="</tr>\n";
                    $inner.="</table>\n";
                $inner.="</div>\n";
            $inner.="</td></tr>\n";
          $inner.="<tr>\n";
            $inner.="<td widht=100% align=center  colspan=3 valign=top>\n";
            $inner.="<div id='tabla_detalles_cuarta' style='display:$display_cuarta'>\n";
                       $inner.="<table width=100% align=center bgcolor=''>\n";
                       $inner.="<tr>\n";
                       $inner.="<td align=left width=25% >\n";
  	                   $inner.="<input type=button name=remitos_asociados class='estilos_boton' $style_3 value='Remitos Asociados' onClick=\"window.open('".encode_link("../remitos/remito_listar.php",array ("keyword"=>$ID,"filter"=>"res.id_licitacion","volver_lic"=>$ID,"cmd"=>"todos"))."');\">\n";
                       $inner.="</td>\n";
                       $inner.="<td align=center width=25% >\n";
                       $inner.="<input type=button name=cobranzas class='estilos_boton' $style_3 value='Seguimiento de cobros' $disabled_seguimientos_cobros onClick=\"window.open('".encode_link("../licitaciones/lic_cobranzas.php",array("cmd1"=>"detalle","id_lic"=>$ID))."');\">\n";
                       $inner.="</td>\n";
                       $inner.="<td width=25% align=center>\n";
                       $inner.="<input type=button name=pm_asoc class='estilos_boton' $style_4 value='P.M. asoc.' onClick=\"window.open('".encode_link("../mov_material/listado_mov_material.php",array("keyword"=>$ID,"filter"=>"id_licitacion","cmd"=>"todos","volver_lic"=>$ID,"pedido_material"=>1))."');\">\n";
                       $inner.="<input type=button name=ordenes_asoc class='estilos_boton' $style_4 value='O.C. asoc.' onClick=\"window.open('".encode_link("../ord_compra/ord_compra_listar.php",array("filtro"=>"todas","keyword"=>$ID,"filter"=>"o.id_licitacion","volver_lic"=>$ID, "cmd"=>"todas"))."');\">\n";
                       $inner.="</td>\n";
                       $inner.="<td align=right width=25% >\n";
	                   $inner.="<input type=button name=ordenes_prod_asoc class='estilos_boton' $style_3 value='O. de producción asociadas' onClick=\"window.open('".encode_link("../ordprod/ordenes_ver.php",array("filter"=>"orden_de_produccion.id_licitacion","keyword"=>$ID,"volver_lic"=>$ID, "cmd"=>"ta"))."');\">\n";
                       $inner.="</td>\n";

                     $inner.="</tr>\n";
                   $inner.="</table></div>\n";
           //echo "</td>";
           //echo "</tr>";
          //echo "</table>";
     }
       $inner.="</td>\n";
	$inner.="</tr>\n";
          $inner.="</table>";
    if(!$checked_resultado && !$checked_asociaciones)
    {$barra_height=115;

    }
    elseif (($checked_resultado && !$checked_asociaciones)
           || (!$checked_resultado && $checked_asociaciones))
    {$barra_height=145;

    }
    elseif ($checked_resultado && $checked_asociaciones)
    {$barra_height=175;

    }
    $barra_width=580;
	inicio_barra("botonera","ID Licitación $ID",$inner,$barra_height,$barra_width);
//echo "<script>
//top=95;
//mover.style.top=document.body.clientHeight-top;";
//if($checked_resultado)
// echo"aumentar_menu(\"tabla_detalles_tercera\");";
//if($checked_asociaciones)
// echo "aumentar_menu(\"tabla_detalles_cuarta\");";
//echo "</script>";
		//////////////////// Fin de la barra de botones /////////////////////////
echo"<br><br><br><br><br><br><br><br><br><br></form>\n";
	 ?>
            <!-- Script para el menu contextual -->
            <script>
            if (document.all && window.print) {
                 ie5menu.className = menuskin;
                 document.all.detalle_licitacion.oncontextmenu = showmenuie5;
                 <? if ($mostrar_signo==1) {?>  
                    document.all.tabla_signo.oncontextmenu = showmenuie5;
                 <?}?>
                 document.body.onclick = hidemenuie5;
                }
             </script>
      <?

	fin_pagina();
	}
}

function listado() {
	global $bgcolor3,$cmd,$cmd1,$datos_barra,$up,$db;
	global $bgcolor2,$itemspp,$parametros,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera,$permisos;
	global $_ses_global_backto,$_ses_global_nro_orden_asociada,$_ses_global_pag,$contador_consultas;
    global $dos_dias,$cuatro_dias,$vencido,$hay_vencidos,$_ses_global_extra;

	echo $html_header;


    generar_barra_nav($datos_barra);
   if($cmd=="presentadas1")
   {
     $orden = array(
		"default" => "3",
//		"default_up" => "1",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.id_estado",
		"3" => "entregar_lic.vence",//fecha de entrega de los productos de la licitacion
		"4" => "entidad.nombre",
		"5" => "entregar_lic.lugar_entrega_productos",
		"6" => "licitacion.nro_lic_codificado"
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
         //"subido_lic_oc.nro_orden" => "Número Orden de Compra",//se saco porque se ponia muy lerda la busqueda Broggi
		"licitacion.fecha_apertura" => "Fecha Apertura",
		"entregar_lic.vence" => "Fecha Entrega",
		"entregar_lic.lugar_entrega_productos"=>"Lugar Entrega"
	);
   }
   else
   {
   	$orden = array(
		"default" => "3",
//		"default_up" => "1",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.id_estado",
		"3" => "licitacion.fecha_apertura",
		"4" => "entidad.nombre",
		"5" => "distrito.nombre",
		"6" => "licitacion.nro_lic_codificado",
		"7" => "licitacion.fecha_apertura",
        "8" => "licitacion.ultimo_usuario_fecha",
        "9" => "usuarios.nombre"
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
//		"subido_lic_oc.nro_orden" => "Número Orden de Compra",
		"licitacion.fecha_apertura" => "Fecha Apertura",
        "licitacion.exp_lic_codificado"=>"Expediente",
        "usuarios.nombre" => "Lider"
	);
   }
	$itemspp = 50;

	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
	//echo "<font size='4'>Diego esto es el listado</font>";
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
        echo"<br>\n";
        echo "<table cellspacing=2 cellpadding=5 class='bordes' width=100% align=center bgcolor=$bgcolor3>\n";
	echo "<tr><td align=center>\n";

	$sql_tmp = "SELECT usuarios.iniciales,usuarios.nombre,usuarios.apellido,licitacion.id_licitacion,licitacion.exp_lic_codificado,licitacion.fecha_apertura,licitacion.nro_lic_codificado,licitacion.id_estado,licitacion.check_lic,licitacion.resultados_cargados,entidad.nombre AS nombre_entidad,licitacion.id_entidad, ";
	$sql_tmp .= "distrito.nombre AS nombre_distrito ";
        if($cmd=="proximas" ||$cmd=="presentadas")
        {
         $sql_tmp .= ",licitacion.observaciones,entregar_lic.orden_subida,entregar_lic.mostrar,entregar_lic.vence,";
         $sql_tmp .=" entregar_lic.lugar_entrega_productos,entregar_lic.oferta_subida,";
	     $sql_tmp .= "entregar_lic.archivo_oferta,entregar_lic.oferta_lista_imprimir, ";
	     $sql_tmp .= "entregar_lic.garantia_contrato_subida ";
         //pe
         }

        if($cmd=="presentadas")
         {
          $sql_tmp .=" ,moneda.simbolo,monto_ofertado,monto_ganado,monto_estimado ";
         }

	$sql_tmp .= "FROM licitacion LEFT JOIN entidad ";
	$sql_tmp .= "USING (id_entidad) ";
	/////////////////////////////traigo el lider de la Licitacion
	$sql_tmp .= "LEFT JOIN sistema.usuarios ";
	$sql_tmp .= "on (lider=id_usuario) ";

    /*
    $sql_tmp .= "FROM licitacion ";
    $sql_tmp .=" left join (select * from entidad where id_tipo_entidad<>6) as entidad using (id_entidad)";
     */
	$sql_tmp .= "LEFT JOIN distrito ";
	$sql_tmp .= "USING (id_distrito) ";
	$sql_tmp .= "LEFT JOIN estado ";
	$sql_tmp .= "USING (id_estado) ";

	//$sql_tmp .= "LEFT JOIN	subido_lic_oc ";//se saco porque se ponia muy lerda la busquda Broggi
	//$sql_tmp .= "USING (id_licitacion)";

	if($cmd=="proximas" ||$cmd=="presentadas")
	{

         $sql_tmp .= "LEFT JOIN entregar_lic ";
	 $sql_tmp .= "USING (id_licitacion) ";
         $sql_tmp .= " join moneda on(licitacion.id_moneda=moneda.id_moneda) ";

	}
	$link_tmp = array("cmd"=>$cmd);
	if ($cmd == "historial") {
		$where_tmp = " (estado.ubicacion = 'HISTORIAL') ";
		$contar="SELECT count(*) FROM licitaciones.licitacion LEFT JOIN licitaciones.estado USING (id_estado) WHERE borrada='f' and ubicacion='HISTORIAL'";
	}
	elseif ($cmd == "proximas") {
		$where_tmp = " (estado.ubicacion = 'ACTUALES') ";
		$where_tmp .= " and licitacion.fecha_apertura > '$fecha_hoy'";
		$contar="SELECT count(*) FROM licitaciones.licitacion LEFT JOIN licitaciones.estado USING (id_estado) WHERE borrada='f' and ubicacion='ACTUALES' and licitacion.fecha_apertura > '$fecha_hoy'";
	}
	elseif ($cmd == "presentadas") {
		$where_tmp = " (estado.ubicacion = 'ACTUALES') ";
		$where_tmp .= " and licitacion.fecha_apertura <= '$fecha_hoy'";
		$contar="SELECT count(*) FROM licitaciones.licitacion LEFT JOIN licitaciones.estado USING (id_estado) WHERE borrada='f' and ubicacion='ACTUALES' and licitacion.fecha_apertura <= '$fecha_hoy'";
	}
	elseif ($cmd == "todas") {
		$where_tmp = "";
		 $contar="SELECT count(*) FROM licitaciones.licitacion WHERE borrada='f'";
	}
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
	$where_tmp.=" and es_presupuesto=0 ";
	$where_tmp .= " GROUP BY usuarios.iniciales,usuarios.apellido,usuarios.nombre,licitacion.id_licitacion,licitacion.id_entidad,
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
     if($cmd=="proximas" ||$cmd=="presentadas") {

          $where_tmp .=",moneda.simbolo,licitacion.resultados_cargados,entregar_lic.orden_subida";
          $where_tmp .=",entregar_lic.mostrar,entregar_lic.vence,entregar_lic.oferta_subida,entregar_lic.archivo_oferta,entregar_lic.oferta_lista_imprimir";
          $where_tmp .=",entregar_lic.lugar_entrega_productos,entregar_lic.garantia_contrato_subida";
       //pe
	}
	if (($cmd == "presentadas" or $cmd == "presupuesto" or $cmd == "historial" or $cmd == "todas") and ($up == "")) {
		$up = "0";
	}

    if($_POST['keyword'] || $keyword || $_POST['estado']!="all")// en la variable de sesion para keyword hay datos)
     $contar="buscar";
	list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);
    //die($sql);
	echo "&nbsp;&nbsp;Estado: <select name='estado'>\n";
	echo "<option value='all'>Todos\n";
	$sql_est = "SELECT id_estado,nombre,color FROM estado";
	if ($cmd == "presentadas" or $cmd == "proximas") {
		$sql_est .= " WHERE ubicacion='ACTUALES'";
	}
	/*elseif ($cmd == "presupuesto") {
		$sql_est .= " WHERE ubicacion='PRESUPUESTO'";
	}*/
	elseif ($cmd == "historial") {
		$sql_est .= " WHERE ubicacion='HISTORIAL'";
	}
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
	echo "&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";
	?><img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_ver_lic.htm" ?>', 'LISTAR LICITACIONES')" >
    <?  $link_ordenar=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>0));
        echo "<input type='button' name='ordenar' value='UM' onclick=\"document.location='$link_ordenar'\" title='ordena por la última modificación'>";

	echo "</td></tr>";
/*      echo "<tr>";
        echo "<td>";
        $link_ordenar=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>0));
        echo "<input type='button' name='ordenar' value='Ordenar ult. modif.' onclick=\"document.location='$link_ordenar'\">";
        echo "</td>";
        echo "</tr>";
*/
        echo "</table>\n";
	echo "</form>\n";
	$result = sql($sql) or die($db->ErrorMsg()."<br>Error");
	//die($sql);
	//$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error");
        echo "<br>\n";
        echo "<table class='bordes' width=100% cellpadding=2 align=center>";
if (permisos_check("inicio","licitaciones_columna_monto")) {
	echo "<tr><td colspan=7 align=left id=ma>\n";
}
else {
	echo "<tr><td colspan=6 align=left id=ma>\n";
}
	echo "<table width=100%><tr id=ma>\n";
	echo "<td width=30% align=left><b>
	Total:</b> $total_lic licitaciones.</td>\n";
	echo "<td width=70% align=right>$link_pagina</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
//	echo "<div style='position:relative; width:55%; overflow:auto;'>";
	echo "<tr>";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
	echo "&nbsp;/&nbsp;<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"9","up"=>$up))."'>Lider</a></td>\n";
	if($cmd=="presentadas1")
	  echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Entrega</td>\n";
	else
	  echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Apertura</td>\n";

	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Entidad</td>\n";
	if($cmd=="presentadas1")
	 echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Lugar Entrega</td>\n";
	else
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
		else
		 $title_obs="";
		$ma = substr($result->fields["fecha_apertura"],5,2);
		$da = substr($result->fields["fecha_apertura"],8,2);
		$ya = substr($result->fields["fecha_apertura"],0,4);
		$hora = substr($result->fields["fecha_apertura"],10,6);
        $dia=calcula_numero_dia_semana($da,$ma,$ya);
        switch ($dia){
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
			$ref = encode_link($_ses_global_backto,array("licitacion"=>$result->fields["id_licitacion"],"nro_orden"=>$_ses_global_nro_orden_asociada,"id_entidad"=>$result->fields["id_entidad"],"pagina"=>$_ses_global_pag,"_ses_global_extra"=>$_ses_global_extra));
		else
                 	$ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
		tr_tag($ref);

                //me fijo si hay eventos asociados a la licitacion
                if ($cmd=="proximas"){
                   $bgcolor=eventos_licitacion($result->fields["id_licitacion"]);
                 }
                echo "<td align=center bgcolor='".$estados[$result->fields["id_estado"]]["color"]."' title='".$estados[$result->fields["id_estado"]]["texto"]."'><b><a style='color=".contraste($estados[$result->fields["id_estado"]]["color"],"#000000","#ffffff").";' href='$ref'>";
		//ponemos el logo de check si corresponde
		$fecha_actual=substr($fecha_hoy,0,10);
		if($permiso_check && ($result->fields['check_lic']!=0 && $result->fields['fecha_apertura'] >= $fecha_actual))
	        echo "<img src=$html_root/imagenes/check1.gif border=0 width='15' height='14' align='absmiddle' title='Esta licitacion ya ha sido chequeada'> ";

		echo $result->fields["id_licitacion"]."</a></b>";
                echo "</td>\n";
        echo "<td align='center' title='".$result->fields['nombre']." ".$result->fields['apellido']."'><b>".$result->fields['iniciales']."</b></td>";
		if($cmd=="presentadas1")
                   echo "<td  $bgcolor align=center>".fecha($result->fields["vence"])."</td>\n";
                   else
                   echo "<td  $bgcolor align=center title='$dia - $hora'>$da/$ma/$ya</td>\n";

		echo "<td  $bgcolor align=left title='".$title_obs."'>&nbsp;".html_out($result->fields["nombre_entidad"])."</td>\n";



               if($cmd=="presentadas1")
		  echo "<td  $bgcolor align=left >&nbsp;".html_out($result->fields["lugar_entrega_productos"])."</td>\n";
		else
		  echo "<td  $bgcolor align=left >&nbsp;".html_out($result->fields["nombre_distrito"])."</td>\n";
		if (permisos_check("inicio","licitaciones_columna_monto")) {
			//si es en curso - monto ofertado
			//si p.ganada - monto estimado
			//si adj o presuntamente - monto ganado
			if ($cmd=="presentadas") {
				$monto="";
				switch ($result->fields["id_estado"]){
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
					case 12: $monto="MO:&nbsp;".$result->fields["simbolo"]."&nbsp;".$result->fields["monto_ofertado"];
							break;	
							
				}//del switch
			}
			echo "<td   $bgcolor  align=left valign=middle>$monto</td>";
		}
		echo "<td   $bgcolor  align=left valign=middle>".html_out($result->fields["nro_lic_codificado"]);
		if( $result->fields["garantia_contrato_subida"])
		{
			//echo "<img src=$html_root/imagenes/R.gif border=0 title='Garantía de Contrato cargada' width='15' height='14' align='absmiddle'></a>";
			echo "&nbsp;<span style='color:#FFD86F;background-color:#000000;font:bold;' title='Garantía de Contrato cargada'>&nbsp;G&nbsp;</span>";
		}

		if( $result->fields["resultados_cargados"])
		{ echo "<a href='".encode_link("lic_ver_res.php",array("keyword"=>$result->fields["id_licitacion"],"pag_ant"=>"lic","pagina_volver"=>"licitaciones_view.php"))."'>
				<img src=$html_root/imagenes/R.gif border=0 title='Ver Resultados' width='15' height='14' align='absmiddle'></a>";
		}

				//mostramos el logo de orden de produccion subida,
	  	//si existe la entrada correspondiente para esta licitacion
	  	//$query="select orden_subida,mostrar,vence,oferta_subida,archivo_oferta from entregar_lic where id_licitacion=".$result->fields['id_licitacion'];
		//$res1=$db->Execute($query)  or die ($db->ErrorMsg().$query);

		$link_mostrar=encode_link("mostrar_logo.php",array("id_licitacion"=>$result->fields['id_licitacion']));
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
		 echo "</td>\n";
	  	$result->MoveNext();
	}
    echo "</table><td colspan=6 align=center><br>\n";
    echo "<table width='95%' border=0 align=center>\n";
	echo "<tr><td colspan=6 align=center><br>\n";
	echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia ID/Est:</b></td></tr>\n";
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

        if ($cmd=="proximas") {
        echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores renglon completo</b></td></tr>";
        echo "<tr>";
                echo "<td width=33% bordercolor='#FFFFFF'>";
                echo "<table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
                echo "<td width=15 bgcolor='$cuatro_dias' bordercolor='#000000' height=15>&nbsp;</td>\n";
                echo "<td bordercolor='#FFFFFF'>Cuatro Días</td>\n";
                echo "</tr></table></td>";

                echo "<td width=33% bordercolor='#FFFFFF'>";
                echo "<table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
                echo "<td width=15 bgcolor='$dos_dias' bordercolor='#000000' height=15>&nbsp;</td>\n";
                echo "<td bordercolor='#FFFFFF'>Dos Días</td>\n";
                echo "</tr></table></td>";


                echo "<td width=33% bordercolor='#FFFFFF'>";
                echo "<table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
                echo "<td width=15 bgcolor=$vencido bordercolor='#000000' height=15>&nbsp;</td>\n";
                echo "<td bordercolor='#FFFFFF'>Vencido</td>\n";
                echo "</tr></table></td>";
        echo "</tr>";
        /*if ($hay_vencidos) {
          echo "<script>";
          echo "alert('Atención: Existen Licitaciones con eventos vencidos')";
          echo "</script>";
        }*/
        }
	echo "</table>\n";
	echo "</td></tr>\n";
	echo "</table><br>\n";
	fin_pagina();
} // listado
?>