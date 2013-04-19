<?
/*
Autor: Broggi
Fecha: 15/09/2004

$Author: gonzalo $
$Revision: 1.1 $
$Date: 2006/01/20 18:43:41 $
*/
require_once("../../config.php");


if (($parametros['es_stock']==0 || $parametros['es_stock']=="") && ($_POST['es_stock_subir']==0 || $_POST['es_stock_subir']=="")) $es_stock_subir=0;
else $es_stock_subir=$parametros['es_stock'] or $es_stock_subir=$_POST['es_stock_subir'];
if (($parametros['mostrar_dolar']==0 || $parametros['mostrar_dolar']=="") && ($_POST['mostrar_dolar_subir']==0 || $_POST['mostrar_dolar_subir']=="")) $mostrar_dolar_subir=0;
else $mostrar_dolar_subir=$parametros['mostrar_dolar'] or $mostrar_dolar_subir=$_POST['mostrar_dolar_subir'];
$tipo_lic_subir=$parametros['tipo_lic'] or $tipo_lic_subir=$_POST['tipo_lic_subir'];
$nro_orden=$parametros['nro_orden'] or $nro_orden=$_POST['nro_orden'];
$cantidad_archivos=$_POST['cant_archivos'] or $cantidad_archivos=1;



//////////////////////////////////////////////////////////////////////////////////////
/*if ($parametros["download"]) {
	$sql = "select * from archivos_subidos where id_archivos_subidos = ".$parametros["FileID"];
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");


	if ($parametros["comp"]) {
		$FileName = $result->fields["nombre_archivo_comp"];
		$FileNameFull = UPLOADS_DIR."/stock/RMA/$FileName";
		$FileType="application/zip";
		$FileSize = $result->fields["filesize_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["nombre_archivo"];
		$FileNameFull = UPLOADS_DIR."/stock/RMA/$FileName";
		$FileType = $result->fields["filetype"];
		$FileSize = $result->fields["filesize"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}
}*/


//////////////////////////////////////////////////////////////////////////////////////



if ($_POST['guardar']=="Guardar")
{$db->StartTrans();
		$size=$_FILES["file_1"]["size"];
		$type=$_FILES["file_1"]["type"];
		//echo $type;
		//die();
		$name=$_FILES["file_1"]["name"];
		$temp=$_FILES["file_1"]["tmp_name"];
		$max_file_size=5242880;
		$path = UPLOADS_DIR."/ord_compra/archivos_subidos";
		$extensiones = array("doc","pdf","zip");
		function algo($uno,$dos){};
		$func = "algo";
		$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,$func,$extensiones,"","",1,0);
		switch ($ret["error"])
		{
			case 0: {$cant_aviso=sizeof($_POST['avisar_1']);
			         $inicio=0;
			         $arreglo=$_POST['avisar_1'];
			         //print_r($arreglo);
			         /*
			         while ($inicio<$cant_aviso)
			               {//echo "El valor del Arreglo es: ".$arreglo[$inicio];
			               	$sql="select mail,nombre,apellido,id_usuario from usuarios where id_usuario=".$arreglo[$inicio];
			                $resultado_sql=sql($sql) or fin_pagina();
			                while (!$resultado_sql->EOF)
			                      {$mensaje="Se le informa que ".$resultado_sql->fields['apellido'].", ".$resultado_sql->fields['nombre']." ha subido un archivo a la Orden de Compra Nro.$nro_orden";
			                       echo $mensaje;
			                       enviar_mail($resultado_sql->fields['mail'],"Subieron Archivos en Orden de Compra",$mensaje,' ',' ',' ',0);
			                       $resultado_sql->MoveNext();
			                      }
			                $inicio++;
			               }*/
				     $FileDateUp = date("Y-m-d H:i:s", mktime());
				     $user=$_ses_user['name'];
                      $sql = "select nextval('archivos_subidos_compra_id_archivo_subido_seq') as id";
                      $id_rec = sql($sql) or fin_pagina();
					  $sql = "insert into archivos_subidos_compra (id_archivo_subido,comentario,usuario,fecha,nombre_archivo_comp,nombre_archivo,nro_orden,filetype,filesize,filesize_comp) values ";
					  $sql .="(".$id_rec->fields['id'].",'".$_POST["comentario_1"]."','$user','$FileDateUp','".$ret["filenamecomp"]."','$name',$nro_orden,'$type',$size,".$ret["filesizecomp"].")";
					  $resultado_sql=sql($sql) or fin_pagina();

				     $msg="<font size='2' color='red'>El Archivo se subio Correctamente.</font>";
				}
			 break;
			case 1: {
				$msg="<font color='red'>Error: No especificó el archivo o el mismo supera el tamaño máximo soportado.</font>";
			} break;
			case 2: {
				$msg="<font color='red'>Error: Falla inesperada subiendo el archivo.</font>";
			} break;
			case 3: {
				$msg="<font color='red'>Error: El sistema no acepta archivos vacios.</font>";
			} break;
			case 5: {
				$msg="<font color='red'>Error: El sistema no acepta archivos que superen los ".($max_file_size/1024)."Kb de información.</font>";
			} break;
			case 6: {
				$msg="<font color='red'>Error: El archivo ya existe en el Sistema y no esta permitido sobreescribir el mismo.</font>";
			} break;
			case 8: {
				$msg="<font color='red'>Error: Fallo el proceso de compresión, pero el archivo fue guardado sin comprimir.</font>";
			} break;
		}//de switch ($ret["error"])
		$db->CompleteTrans();
		//echo $msg;
	}

echo $html_header;
//$sql = "select * from archivos_subidos where id_info_rma=$id_info_rma";
//$datos = sql($sql) or fin_pagina();

$sql_usuarios="select * from usuarios order by nombre";
$consulta_sql_usuarios=sql($sql_usuarios) or fin_pagina();

?>

<form name='form1' action="<?= $_SERVER['SCRIPT_NAME']?>" method="POST" enctype="multipart/form-data">


<table align="center">
 <tr><td><b><?=$msg?></b></td></tr>
</table>

<table align="center" width="70%" class="bordes">
 <tr id=mo><td><font size="2"><b>Subir Archivos</b></font></td></tr>
 <!--<tr>
  <td align="right">
   <b>Cantidad de Archivos:&nbsp;</b>
   <select name="cant_archivos" onchange="document.form1.submit()">
    <?
     $cantidad=1;
     while ($cantidad<=20)
           {if ($cantidad==$cantidad_archivos) $selected="selected";
     ?>
      <option <?=$selected?> value="<?=$cantidad?>"><?=$cantidad?></option>
     <?
          $cantidad++;
          $selected="";
           }
    ?>
   </select>
  </td>
 </tr>-->
 <?
  $cantidad=1;
  while ($cantidad<=$cantidad_archivos)
  {
 ?>
 <tr>
  <td>
   <table width="100%" align="center" id=ma  border="1" cellspacing="1">
    <tr >
     <td align="left" colspan="2" >
      <font color="Black"><b>Archivo:&nbsp;<?=$cantidad?></b></font>
     </td>
    </tr>
    <tr >
     <td align="left" colspan="2">
      <font color="Black"><b>Nombre Archivo:&nbsp;</b></font><INPUT type="file" name="file_<?=$cantidad?>">
     </td>
    </tr>
    <tr>
     <td align="left" width="15%">
      <font color="Black"><b>Avisar a:&nbsp;</b></font>
     </td>
     <td>

      <select name="avisar_<?=$cantidad?>[]" size="5" multiple>

      <?$consulta_sql_usuarios->MoveFirst();
        while (!$consulta_sql_usuarios->EOF)
        {
      ?>
        <option value="<?=$consulta_sql_usuarios->fields['id_usuario']?>"><?=$consulta_sql_usuarios->fields['nombre']?>&nbsp;<?=$consulta_sql_usuarios->fields['apellido']?></option>
      <?
        $consulta_sql_usuarios->MoveNext();
        }
      ?>
      </select>
     </td>
    </tr>
    <tr>
     <td align="left" width="15%">
      <font color="Black"><b>Comentarios:&nbsp;</b></font>
     </td>
     <td>
      <textarea name="comentario_<?=$cantidad?>" rows="5" cols="35" ></textarea>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <?
  $cantidad++;
  }
 ?>
</table>

<input name="nro_orden" type="hidden" value="<?=$nro_orden?>">
<table align="center">
 <tr>
  <td><input name="guardar" type="submit" value="Guardar"></td>
  <td><input name="volver" type="button" value="Volver" onclick="location.href='<?= encode_link("ord_pago_recepcion.php",array("nro_orden"=>$nro_orden,"mostrar_dolar"=>$mostrar_dolar_subir)) ?>';"></td>
 </tr>
</table>

</form>