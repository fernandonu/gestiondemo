<?
/*
Autor: MAC
Creado: 06/10/04

MODIFICADA POR
$Author: mari $
$Revision: 1.4 $
$Date: 2006/04/20 21:19:55 $
*/


/***************************************
Genera la entrada en la base de datos 
en la tabla archivo para licitaciones
****************************************/
function insertar_arch_pres($ID, $FileName, $FileNameComp, $FileSize, $FileSizeComp, $FileType,$id_tipo){
    global $_ses_user;
    
	if ($ID) {

		$FileDateUp = date("Y-m-d H:i:s", mktime());
                error_reporting(0);
		$sql = "select idarchivo,id_licitacion,nombre from pcpower_archivos where id_licitacion = $ID and nombre = '$FileName'";
		$result = sql($sql) or fin_pagina();
//                $id_archivo=$result->fields["idarchivo"];

		$cant_filas = $result->RecordCount();
		if ($cant_filas == 0) {
	//		$sql = "select nombre,apellido from usuarios where login='$_ses_user_login'";
	//		$result = $db->Execute($sql) or die($db->ErrorMsg());
	//		$user_name = $result->fields["nombre"]." ".$result->fields["apellido"];
			$sql = "insert into pcpower_archivos 
			        (id_licitacion, nombre, nombrecomp, tamaño, tamañocomp, tipo, subidofecha, subidousuario,id_tipo_archivo) 
			        values ($ID, '$FileName', '$FileNameComp', $FileSize, $FileSizeComp, '$FileType', '$FileDateUp', '".$_ses_user["name"]."',$id_tipo)";
			$result = sql($sql) or fin_pagina();
/*
                        $sql = "select max(idarchivo)  from archivos ";
                        $res_archivo=sql($sql) or fin_pagina();
                        $id_archivo=$res_archivo->fields["idarchivo"];
*/
		}
		else {
			$sql = "update pcpower_archivos set tamaño=$FileSize, tamañocomp=$FileSizeComp, 
			        subidofecha='$FileDateUp', subidousuario='".$_ses_user["name"]."' 
			        where id_licitacion = $ID and nombre = '$FileName'";
			$result = sql($sql) or fin_pagina();
		}
	}

}



/**********************************************
FUNCIONES PARA SUBIR ARCHIVOS DE LICITACIONES
ProcForm,FileUpload,GetExt
***********************************************/
function pcpower_ProcForm($FVARS,$tipo="PcPower_Presupuesto") {
	global $max_file_size,$extensiones,$ID,$bgcolor2,$db;
	global $html_root;
	global $_ses_user_name;
	global $nombre_entidad;



        $db->StartTrans();
        //print_r($_POST);
	echo "<table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2  align=center>";
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor2 id=ma>
                  Agregando archivos</td></tr>\n";
	$path=UPLOADS_DIR."/PcPower/Presupuesto/$ID";	// linux
	$files_arr = array();
    $id_archivos_subidos = array();
	for($i=0;$i<count($FVARS["archivo"]["tmp_name"]);$i++) {
		$size=$FVARS["archivo"]["size"][$i];
		$type=$FVARS["archivo"]["type"][$i];
		$name=$FVARS["archivo"]["name"][$i];
		$temp=$FVARS["archivo"]["tmp_name"][$i];
		$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,"",$extensiones,"",1);

		$id_tipo_archivo=$_POST["tipo_archivo"][$i];
		//$sql="select tipo from tipo_archivo_licitacion
          //    where id_tipo_archivo=$id_tipo_archivo";
        //$res_arch=sql($sql) or fin_pagina();
        //$archivos_tipos[]=$res_arch->fields["tipo"];
		insertar_arch_pres($ID, $name, $ret["filenamecomp"], $size, $ret["filesizecomp"], $type,$id_tipo_archivo);

        $sql="select idarchivo as id_archivo from pcpower_archivos where id_licitacion = $ID and nombre = '$name'";
        $res=sql($sql) or fin_pagina();
        $id_archivos_subidos[]=$res->fields["id_archivo"];
		//$ret = 0;
		if ($ret["error"] == 0) $files_arr[$i] = $name;
	}

	echo "</table>\n";
$db->CompleteTrans();
}//fin de la funcion

/******************************************************************
Funciones para download de archivos de presupuestos
*******************************************************************/
function pcpower_download_file($ID)
{ global $parametros;
	$FileID = $parametros["FileID"];
	$Comp = $parametros["Comp"];
	if ((!$ID) or (!$FileID)) {
		listado();
	}
	$sql = "SELECT pcpower_archivos.*,pcpower_licitacion.fecha_apertura ";
	$sql .= "FROM pcpower_archivos ";
	$sql .= "INNER JOIN pcpower_licitacion ";
	$sql .= "ON pcpower_archivos.id_licitacion=pcpower_licitacion.id_licitacion ";
	$sql .= "WHERE pcpower_archivos.idarchivo=$FileID";
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
		$sql = "SELECT pcpower_entidad.nombre as nombre_entidad,";
		$sql .= "pcpower_distrito.nombre as nombre_distrito ";
		$sql .= "FROM (pcpower_licitacion ";
		$sql .= "INNER JOIN pcpower_entidad ";
		$sql .= "ON pcpower_licitacion.id_entidad=pcpower_entidad.id_entidad) ";
		$sql .= "INNER JOIN pcpower_distrito ";
		$sql .= "ON pcpower_entidad.id_distrito=pcpower_distrito.id_distrito ";
		$sql .= "WHERE pcpower_licitacion.id_licitacion=$ID";
		$result = sql($sql) or die;
		$distrito = $result->fields["nombre_distrito"];
		$entidad = $result->fields["nombre_entidad"];
		$FilePath=UPLOADS_DIR."/PcPower/Presupuesto/$ID";

		$FileNameFull="$FilePath/$FileName";
        }
        else {
             $FilePath=UPLOADS_DIR."/folletos";
             $FileNameFull="$FilePath/$FileName";
             }

	}
	$FileNameFull=enable_path($FileNameFull);
	//echo "comp $Comp - filename $FileName- filenamefull $FileNameFull - filetype $FileType - filesize $FileSize";
	FileDownload($Comp, $FileName, $FileNameFull, $FileType, $FileSize);
}


/******************************************************************
Genera la parte de eliminar archivos relacionados con el presupuesto
*******************************************************************/
function pcpower_genera_det_delfile($ID)
{   global $html_header,$bgcolor2;
	echo $html_header;
	if (is_array($_POST["file_id"])) {
		echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		foreach ($_POST["file_id"] as $id_archivo) {
			if ($result=sql("select nombre from pcpower_archivos where idarchivo=$id_archivo")) {
				$nombre_archivo = $result->fields["nombre"];
				if (sql("delete from pcpower_archivos where idarchivo=$id_archivo")) {
					$msg = "Se elimino el archivo \"$nombre_archivo\"";
				}
				else {
					$msg = "<font color='#FF0000'>No se pudo eliminar el archivo \"$nombre_archivo\"</font>";
				}
			}
			else {
				$msg = "<font color='#FF0000'>No existe ningún archivo con el ID $id_archivo</font>";
			}
			echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
			echo "<font size=3>$msg</font>";
			echo "</b></td></tr>";
		}
		echo "<tr><td>&nbsp;</td></tr>";
		echo "</table><br>\n";
	}

}

?>