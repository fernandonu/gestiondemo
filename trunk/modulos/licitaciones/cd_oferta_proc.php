<?
/*
Autor: GACZ

MODIFICADA POR
$Author: fernando $
$Revision: 1.48 $
$Date: 2007/01/05 17:40:54 $
*/

require_once("../../lib/fns.gacz.php");

$f="'".date('Y-m-j H:i:s')."'";

//----------- if ($_POST['btn_guardar']) --------

 //arreglo de checkbox
 $chk=PostvartoArray("chk_");

 //arreglo de ids de archivos
 $ids=PostvartoArray("idarchivo_");

 //arreglo de ids de archivos
 $tipos=PostvartoArray("select_tipo_");

 //arreglo con los nuevos nombres de los archivos
 $nombres=PostvartoArray("nuevo_nombre_");

 $q_tmp="";
 for ($i=0,$j=0; $i < $_POST['total_archivos']; $i++ )
 {
 	//si esta checkeado
 	if ($chk[$i])
 	{
	 	$tabla_archivos[$j]['idarchivo']=$ids[$i];
 		$tabla_archivos[$j]['id_tipo']=$tipos[$i];
 		$tabla_archivos[$j++]['nuevo_nombre']="'".$nombres[$i]."'";

 	}
 	//sino armo una lista para borrar
 	else
 	{
 		$q_tmp.="$coma".$ids[$i];
 		$coma=",";
 	}

 }

 //borro los que deseleccione
 if ($q_tmp)
 {
 	$q="delete from archivos_cdoferta where idarchivo in ($q_tmp) ";
 	sql($q) or fin_pagina();

 }

	 //modifico la tabla oferta
	 $tabla_cdoferta[0]['id_licitacion']=$id_lic;

	 if(replace("cdoferta",$tabla_cdoferta,array("id_licitacion"))!=0)
	 	die ("Ha ocurrido un error al actualizar 1");


   //ordena los titulos para insertar o modificar
	for ($i=0; $i < $_POST['total_titulos'];$i++)
 	{
 		$tabla_titulos[$i]['id_titulo']=$_POST['idtitulos'][$i];
 		$tabla_titulos[$i]['titulo']="'".$_POST['titulos'][$i]."'";
 		$tabla_titulos[$i]['nro_titulo']=$i+1;
 		$tabla_titulos[$i]['id_licitacion']=$id_lic;
 	}

  if (!(($_POST['total_titulos']) && (replace("titulos",$tabla_titulos,array("id_titulo"))==0)))
 	die ("Ha ocurrido un error al actualizar 2");

	if (is_array($tabla_archivos))
	 	if ( !(($_POST['total_archivos']) && replace("archivos_cdoferta",$tabla_archivos,array("idarchivo"))==0))
		 	die ("Ha ocurrido un error al actualizar 3");

	//inserto en la tabla de registros
 	$q="insert into log_oferta (id_licitacion,user_login,user_name,fecha,tipo_log)
 	values ($id_lic,'$_ses_user[login]','$_ses_user[name]',$f,'de modificación')";
	sql($q) or fin_pagina();


$msg='Los datos han sido guardados con éxito';
//------------  finif ($_POST['btn_guardar']) ------------


//generar imagen ISO para grabar
if ($_POST['btn_generar'])
{

$FilePath=UPLOADS_DIR."/Licitaciones/$id_lic";//directorio de los arch. de lic.
$mainpath=UPLOADS_DIR."/Licitaciones/CD_OFERTA";//directorio del CD de oferta

//datos de los archivos de licitacion
$q="select * from
archivos join
archivos_cdoferta using(idarchivo) join
tipo_archivo using(id_tipo)
where id_licitacion=$id_lic";
$archivos=sql($q) or fin_pagina();

//print_r($archivos->fields);

//copio la estructura del cd en el direcorio de la licitacion
$cmd="cp -a \"$mainpath\" \"$FilePath\"/";
exec($cmd);

	//copio los archivos seleccionados dentro del directorio para la imagen
	while (!$archivos->EOF)
	{
		$FileName=$archivos->fields['nombrecomp'];

		if ($archivos->fields['id_producto'])
         $FileNameFull=UPLOADS_DIR."/folletos/$FileName";
		else
  			$FileNameFull="$FilePath/$FileName";

		//descomprime el archivo al path determinado
		//$cmd="unzip -o \"$FileNameFull\" -d \"$FilePath/CD_OFERTA/folletos/\"";

		//obtengo la posicion del ultimo punto (separador de extension)
		$pos=strrpos($archivos->fields['nombre'],'.');

		//separo el nombre y la extension (quita el punto)
		$nbre=substr($archivos->fields['nombre'],0,$pos);//pos es la cantidad de caracteres
		$ext=substr($archivos->fields['nombre'],$pos+1);

		//reemplazo la extension del archivo por html si es necesario
		if ($archivos->fields['extension']!="*")
			$nuevo_nbre=($archivos->fields['nuevo_nombre'])?$archivos->fields['nuevo_nombre'].".html":$nbre.".html";
		else
			$nuevo_nbre=($archivos->fields['nuevo_nombre'])?$archivos->fields['nuevo_nombre'].".$ext":$archivos->fields['nombre'];

		//descomprime y renombra el archivo
		//OJO: si hay mas de un archivo en el zip van a quedar pegados
		$cmd="unzip -p \"$FileNameFull\" > \"$FilePath/CD_OFERTA/AutoPlay/".$archivos->fields['dir_dest']."/$nuevo_nbre\"";
		exec($cmd);

	 $archivos->MoveNext();
	}

//genero el archivo titulos.xml
$xml=
"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<root>";
$q="select * from titulos where id_licitacion=$id_lic order by nro_titulo";
$titulos=sql($q) or fin_pagina();
while (!$titulos->EOF)
{
$islas.=
"
<isla>
	<titulo>".$titulos->fields['titulo']."</titulo>
</isla>
";
 $titulos->MoveNext();
}
$xml.="$islas</root>";

if ($file=fopen("$FilePath/CD_OFERTA/AutoPlay/Docs/titulos.xml",'w'))
{
	fwrite($file,$xml);
	fclose($file);
}
//-------------fin titulos.xml-------------------------

//creo la imagen iso
$cmd= "mkisofs -J -l -allow-multidot -input-charset iso8859-1 -r -V \"CD-OL_$id_lic\" -o \"$FilePath/CD_OFERTA_$id_lic.iso\"  \"$FilePath/CD_OFERTA/\"";
exec($cmd);

//obtengo el tamaño del archivo ISO
$tamaño=filesize("$FilePath/CD_OFERTA_$id_lic.iso");

//Comprimo la imagen
$cmd="zip -m -j \"$FilePath/CD_OFERTA_$id_lic.zip\" \"$FilePath/CD_OFERTA_$id_lic.iso\"";
exec($cmd);

//obtener el tamaño del archivo ZIP
$tamañocomp=filesize("$FilePath/CD_OFERTA_$id_lic.zip");

//Borro los archivo temporales
$cmd="rm -r \"$FilePath/CD_OFERTA/\"";
exec($cmd);

//agrego el archivo como archivo de licitacion
//si no se habia generado antes
if (!$_POST['id_iso_file'])
{
$cons_tipo="select id_tipo_archivo
            from licitaciones.tipo_archivo_licitacion
            where tipo='Imagen CD Oferta'";
$res_cons_tipo=sql($cons_tipo) or fin_pagina();
$id_tipo_archivo=$res_cons_tipo->fields['id_tipo_archivo'];
//se agrego un campo en el insert - ahora se guarda el tipo de archivo q se subio=Imagen CD OFerta
$q="insert into archivos
(id_licitacion,nombre,nombrecomp,tipo,subidofecha,subidousuario,tamaño,tamañocomp,id_tipo_archivo)
values
($id_lic,'CD_OFERTA_$id_lic.iso','CD_OFERTA_$id_lic.zip','application/iso',
$f,'".$_ses_user['name']."',$tamaño,$tamañocomp,$id_tipo_archivo)";
}
//sino solo lo actualizo
else
{
	$q="update archivos set
	subidofecha=$f,
	subidousuario='".$_ses_user['name']."',
	tamaño=$tamaño,
	tamañocomp=$tamañocomp
	where idarchivo=".$_POST['id_iso_file'];
}

sql($q) or fin_pagina();

	//inserto en la tabla de registros
 	$q="insert into log_oferta (id_licitacion,user_login,user_name,fecha,tipo_log)
 	values ($id_lic,'$_ses_user[login]','$_ses_user[name]',$f,'de imagen')";
	sql($q) or fin_pagina();

/*
$msg='La imagen ha sido generada con éxito.';
$msg_mail="Mensaje del sistema: $msg\n";
enviar_mail("marcelo@coradir.com.ar","CD de Oferta Lic. ID: $id_lic",$msg_mail,"","","",0);
*/
//enviar_mail("cestila@pcpower.com.ar","CD de Oferta Lic. ID: $id_lic",$msg_mail,"","","",0);
}
$refresh=1;
//header("location: ".encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_lic)));

?>