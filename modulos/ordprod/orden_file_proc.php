<?
/*
Autor:  CarlitoX
Creado: viernes 06/08/04

MODIFICADA POR
$Author: mari $
$Revision: 1.2 $
$Date: 2007/01/04 20:31:59 $
*/

//require_once("../../config.php");
$nro_orden=$parametros['nro_orden'];
if ($_POST['baceptar'])
{
    $acceso="Todos";
    $comentario="Archivo de Orden de Producci�n";
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
	    	if (subir_archivo($_FILES["archivo"]["tmp_name"][$i],UPLOADS_DIR."/archivos/$filename",$error_msg)===true)
	    	{
	         $sql="select nextval('subir_archivos_id_seq') as idfile ";
	         $res=sql($sql) or $db->errormsg()."<br>";
	         $idfile=$res->fields['idfile'];
	         $q="INSERT INTO subir_archivos
	              (id,nombre,comentario,creadopor,fecha,size,acceso) Values
	              ($idfile,'$filename','$comentario','".$_ses_user['login']."','$fecha','$tamanio','$acceso');";
	         $q.="insert into archivos_ordprod (id_archivo,nro_orden) values ($idfile,$nro_orden);";

	         if (!sql($q))
	           $error_msg="No se pudo insertar el archivo ".$db->errormsg()."<br>$q ";	
	         else 
	           $ok_msg="El archivo '$filename' se subio con �xito";
	         }
	         
	    	
	    }
	     $error_vector[]=$error_msg;
	     $error_msg="";
	     $ok_vector[]=$ok_msg;
	     $ok_msg="";
    }
}

?>