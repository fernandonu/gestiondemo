<?
/*
Autor: GACZ
Creado: sabado 07/08/04

MODIFICADA POR
$Author: mari $
$Revision: 1.4 $
$Date: 2006/12/29 12:27:55 $
*/

require_once("../../config.php");

$idate=$parametros['idate'];
$idfile=$parametros['id'];
$path=CAS_DIR."/$idate";
if ($_POST['baceptar'])
{
    $fecha=date("Y/m/d");
    $files_total=count($_FILES['archivo']["name"]);
    $error_vector=array();
    for ($i=0; $i < $files_total ; $i++ )
    {
    	$filename=$_FILES["archivo"]["name"][$i];
    	$tamanio=$_FILES['archivo']["size"][$i];
	    if (!$filename)
           $error_msg="Debe seleccionar un archivo";
	    elseif ($_FILES["archivo"]["error"][$i])
           $error_msg="El archivo '$filename' es muy grande ";
    	
	    if (!$error_msg) 
	    {
	    	if (subir_archivo($_FILES["archivo"]["tmp_name"][$i],"$path/$filename",$error_msg)===true)
	    	{
	         $sql="select nextval('archivos_cas_id_seq') as idfile ";
	         $res=sql($sql) or $db->errormsg()."<br>";
	         $idfile=$res->fields['idfile'];
	         $q="INSERT INTO archivos_cas 
	              (id,idate,nombre,creadopor,fecha,size) values
	              ($idfile,$idate,'$filename','".$_ses_user['login']."','$fecha','$tamanio');";

	         if (!sql($q))
	           $error_msg="No se pudo insertar el archivo ".$db->errormsg()."<br>$q ";	
	         else 
	           $ok_msg="El archivo '$filename' se subio con éxito";
	         }
	    }
	     $error_vector[]=$error_msg;
	     $error_msg="";
	     $ok_vector[]=$ok_msg;
	     $ok_msg="";
    }
}

?>