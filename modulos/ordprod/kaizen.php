<?
/*
Author: Ferni
*/

require_once("../../config.php");
variables_form_busqueda("kaizen");

if ($_POST['guardar_archivo']){		
	if ($_FILES["archivo"]["name"]){
	    $size=$_FILES["archivo"]["size"];
	    $type=$_FILES["archivo"]["type"];
	    $name=$_FILES["archivo"]["name"];
		$temp=$_FILES["archivo"]["tmp_name"];
		$comentario=$_POST['comentario'];
	   
	    if ($size >  $max_file_size)
	           Error ("El archivo  $name es muy grande");
	    else{	   
		    if (is_file(UPLOADS_DIR."/kaizen/".$name))
		    	Error("El Archivo Nº $name ya existe.");
		    else{	
			    mkdirs(UPLOADS_DIR."/kaizen");
			    if (!copy($temp,UPLOADS_DIR."/kaizen/".$name)){
			    	Error("No se pudo Subir el archivo $name");
			    }
			    else{
			    	$fecEmision=fecha_db(date("d/m/Y",mktime()));
		            $query_insert="insert into ordenes.kaizen (archivo,tam_arch,subidopor,fecha_carga,comentario) 
		             								   VALUES ('$name',$size,'".$_ses_user['name']."','$fecEmision','$comentario')";
			        sql($query_insert,"error cuando inserta en la tabla de archivos");				 			        
			        /*//compresion
			        $FileNameFull = enable_path(UPLOADS_DIR."/mon_ordenes/".$nro_orden_monitores.'-'.$name);
					$FileNameFullComp = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
					$FileNameOld = $FileNameFull;
					$FileNameFull = $FileNameFullComp;
			        if (SERVER_OS == "linux") {
							$err = `/usr/bin/zip -j -9 -q "$FileNameFull" "$FileNameOld"`;
					} 
					elseif (SERVER_OS == "windows"){
						$paso = ROOT_DIR."\\lib\\zip";
						$err = shell_exec("$paso\\zip.exe -j -9 -q  \"$FileNameFull\" \"$FileNameOld\"");
					} 
					else {
						aviso ("Error en la Compresion");	
						die();
					}
					//fin de compresion*/
			        aviso ("El archivo $name  se subio correctamente");	    	
			    }    
		    }  
	    }
	} 
	else {
	    Error ("Debe seleccionar un archivo");		    
	}
}


if ($cmd=="download") {
    $file=$parametros["file"];
    $size=$parametros["size"];
    Mostrar_Header($file,"application/octet-stream",$size);
    $filefull = UPLOADS_DIR ."/kaizen/". $file;
    readfile($filefull);
    exit();
}

echo $html_header;

$orden = array(
        "default" => "1",
        "1" => "archivo",
        "2" => "subidopor",
        "3" => "fecha_carga",
       
       
        );
$filtro = array(
        "archivo" => "Nombre del Archivo",
        "subidopor" => "Subido Por",
        "fecha_carga"    => "Fecha de Carga",
        );

$sql = " select  * from ordenes.kaizen";
?>
<form name="form1" method="POST" action="kaizen.php" enctype='multipart/form-data'>
<table cellspacing=2 cellpadding=5 class='bordes' width=100% align=center bgcolor=<?=$bgcolor3?>>
<tr>
 <td align=center>
      <?
      list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
      $res = sql($sql) or fin_pagina();
      ?>
      <input type=submit name=buscar value='Buscar'>      
      </td>
</td>

<tr>
 <td align=center class="bordes" id="mo">
 	Subir Archivo
 </td>
</td>
<tr>
 <td align=center class="bordes">
 	<input type="file" name="archivo" style="width=350px">&nbsp;
 	<b>Comentario</b>
 	<textarea name="comentario" rows="2" cols="50"></textarea> &nbsp;&nbsp;&nbsp;		
	<input type="submit" name="guardar_archivo" value="Guardar">
 </td>
</td>

</table>
<table class="bordes" width="100%" align="center">
    <tr id=ma>
      <td>
        <table width="100%">
          <tr id=ma>
            <td width="50%" align="left">Cantidad :<?=$total?></td>
             <td width="50%" align="right"><?=$link_pagina?></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
     <td width="100%" align="center">
          <table width="100%" align="center">
                 <tr id="mo">
			        <td><a id=mo href='<?=encode_link("kaizen.php",array("sort"=>"1","up"=>$up))?>'>Nombre </a></td>
			        <td><a id=mo href='<?=encode_link("kaizen.php",array("sort"=>"2","up"=>$up))?>'>Subido Por </a></td>
			        <td><a id=mo href='<?=encode_link("kaizen.php",array("sort"=>"3","up"=>$up))?>'>Fecha     </a></td>
			        <td>Comentario</td>
			     </tr>
			    <?
			     for($i = 0;$i<$res->recordcount();$i++) {
			     	
			     	$id_kaizen = $res->fields["id_kaizen"];
			 		$nombre     = $res->fields["archivo"];			 		
			 		$subidopor  = $res->fields["subidopor"];
			 		$fecha_carga= $res->fields["fecha_carga"];
			 		$comentario= $res->fields["comentario"];
			 		$tamano= $res->fields["tam_arch"];
			 		
			     	if (is_file("../../uploads/kaizen/".$nombre)){			     	
			       		$link = encode_link("kaizen.php",array ("file" =>$nombre,"size" => $tamano,"cmd" => "download"));			       	
			       		tr_tag($link,"title='$comentario'");
			     	}?>
			       	<td width="10%" align="center"><b><?=$nombre?></b></td>
                    <td width="10%" align="center"><?=$subidopor?></td>
                    <td width="10%"align="center"><?=fecha($fecha_carga)?></td>
                    <td width="10%" align="center"><?=$comentario?></td>                    
                  </tr>
			     <?
			        $res->movenext();
			     } //del for
			     ?>
        </table>       
     </td>
    </tr>
  </table>
</form>

<?
echo fin_pagina();
?>