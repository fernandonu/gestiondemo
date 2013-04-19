<?
/*
AUTOR: Fernando
MODIFICADO POR:
$Author: ferni $
$Revision: 1.6 $
$Date: 2007/03/14 20:04:24 $
*/
require_once("../../config.php");
require_once("funciones_ordenes_monitores.php");

$nro_orden_monitores = $_POST["nro_orden_monitores"] or $nro_orden_monitores = $parametros["nro_orden_monitores"];

$fecha   = Fecha_db($_POST["fecha"]);
$hora    = date("H:i:s");
$usuario = $_ses_user["name"];


if ($_POST['guardar_archivo']){		
	if ($_FILES["archivo"]["name"]){
	    $size=$_FILES["archivo"]["size"];
	    $type=$_FILES["archivo"]["type"];
	    $name=$_FILES["archivo"]["name"];
		$temp=$_FILES["archivo"]["tmp_name"];
	   
	    if ($size >  $max_file_size)
	           Error ("El archivo  $name es muy grande");
	    else{	   
		    if (is_file(UPLOADS_DIR."/mon_ordenes/".$nro_orden_monitores.'-'.$name))
		    	Error("El Archivo Nº $name ya existe.");
		    else{	
			    mkdirs(UPLOADS_DIR."/mon_ordenes");
			    if (!copy($temp,UPLOADS_DIR."/mon_ordenes/".$nro_orden_monitores.'-'.$name)){
			    	Error("No se pudo Subir el archivo $name");
			    }
			    else{
			    	$fecEmision=fecha_db(date("d/m/Y",mktime()));
		            $query_insert="insert into ordenes.archivos_monitores (nro_orden_monitores,archivo,tam_arch,subidopor,fecha_carga) 
		             												VALUES ($nro_orden_monitores,'$name',$size,'".$_ses_user['name']."','$fecEmision')";
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

$cmd=$parametros["cmd"];
if ($cmd=="download") {
    $file=$parametros["file"];
    $size=$parametros["size"];
    Mostrar_Header($file,"application/octet-stream",$size);
    $filefull = UPLOADS_DIR ."/mon_ordenes/". $file;
    readfile($filefull);
    exit();
}

if ($_POST["aceptar"]){
	$db->starttrans(); 		
	$fecha       = Fecha_db($_POST["fecha"]);
	$hora        = date("H:i:s");
	$cantidad    = $_POST["cantidad"];	
	$descripcion = $_POST["descripcion"];
	$descripcion = ereg_replace("'","\'",$descripcion);
    $descripcion = ereg_replace("\"","\\\"",$descripcion);	   
    
	if (!$nro_orden_monitores){
		//inserto la orden de produccion para monitores

		$sql = "select nextval('ordenes.ordenes_monitores_nro_orden_monitores_seq') as nro_orden_monitores ";
		$res = sql($sql) or fin_pagina();
		
		$nro_orden_monitores = $res->fields["nro_orden_monitores"];
		
		$campos = "nro_orden_monitores,fecha,cantidad,descripcion,activo";
		$values = "$nro_orden_monitores,'$fecha',$cantidad,'$descripcion',1";
		$sql    = " insert into ordenes_monitores ($campos) values ($values)";
		sql($sql) or fin_pagina();
		
		$tipo   = "Creación";		
		$campos = "nro_orden_monitores,usuario,tipo,fecha";
		$values = "$nro_orden_monitores,'$usuario','$tipo','$fecha $hora'";
		$sql    = "insert into log_monitores ($campos) values ($values)"; 
		sql($sql) or fin_pagina();
	} else {
	  //ya existe la orden de produccion para monitores asi que guardo los cambios	
	  
	  //modifico la orden
	  $campos = "fecha = '$fecha',cantidad = $cantidad,descripcion = '$descripcion'";
	  $sql    = "update ordenes_monitores set $campos where nro_orden_monitores = $nro_orden_monitores";
	  sql($sql) or fin_pagina();
	  
	  //creo el log de modificacion
 	  $tipo   = "Modificación";		
	  $campos = "nro_orden_monitores,usuario,tipo,fecha";
	  $values = "$nro_orden_monitores,'$usuario','$tipo','$fecha $hora'";
	  $sql    = "insert into log_monitores ($campos) values ($values)"; 
	  sql($sql) or fin_pagina();  	  
	}
	
	//guardo los numeros de serie
	
	
	$sql = "delete from numeros_series_monitores where nro_orden_monitores = $nro_orden_monitores";
	sql($sql) or fin_pagina();
	
	$planta = $_POST["planta"];
	$anio   = $_POST["anio"];
	$mes    = $_POST["mes"];
	$alfa   = $_POST["alfabeto1"].$_POST["alfabeto2"].$_POST["alfabeto3"];
	$nume   = "".$_POST["numerico1"].$_POST["numerico2"].$_POST["numerico3"]."";
	$numero_serie = $planta.$anio.$mes.$alfa.$nume;
    $primer_numero = $numero_serie ;
	
	for ($i = 0;$i<$cantidad;$i++){		
		$campos = "nro_orden_monitores,numero";
		$values = "$nro_orden_monitores,'$numero_serie'";		
		$sql = "insert into numeros_series_monitores ($campos) values ($values)";
		sql($sql) or fin_pagina();				
		if ($i == $cantidad - 1) $ultimo_numero = $numero_serie;				
		$numero_serie = proximo_nro_serie($numero_serie);	
	}	
	$sql = "update ordenes_monitores set nro_serie_desde = '$primer_numero',nro_serie_hasta = '$ultimo_numero' 
	        where nro_orden_monitores = $nro_orden_monitores";
	sql($sql) or fin_pagina();	
	
	if ($_POST["comentario_nuevo"]) {	  
		$sql = nuevo_comentarios_monitores($nro_orden_monitores,$_POST["comentario_nuevo"])	;		
		sql($sql) or fin_pagina();		
	}	
	$db->completetrans();		
}

if ($_POST["eliminar"]){
	$db->starttrans();
	
	$sql = "update ordenes_monitores set activo = 2 where nro_orden_monitores = $nro_orden_monitores";
    sql($sql) or fin_pagina();
    //creo el log de eliminacion
 	$tipo   = "Eliminacion";		
	$campos = "nro_orden_monitores,usuario,tipo,fecha";
	$values = "$nro_orden_monitores,'$usuario','$tipo','$fecha $hora'";
	$sql    = "insert into log_monitores ($campos) values ($values)"; 
	sql($sql) or fin_pagina();          	
	$db->completetrans();		
}//del post[eliminar]

if ($_POST["restaurar"]){
	$db->starttrans();	
	$sql = "update ordenes_monitores set activo = 1 where nro_orden_monitores = $nro_orden_monitores";
    sql($sql) or fin_pagina();
    //creo el log de eliminacion
 	$tipo   = "Restaurar";		
	$campos = "nro_orden_monitores,usuario,tipo,fecha";
	$values = "$nro_orden_monitores,'$usuario','$tipo','$fecha $hora'";
	$sql    = "insert into log_monitores ($campos) values ($values)"; 
	sql($sql) or fin_pagina();          	
	$db->completetrans();		
}//del post[eliminar]




if ($nro_orden_monitores) {
	
	$sql  = " select cantidad,fecha,descripcion,nro_serie_desde,nro_serie_hasta,activo
	          from ordenes_monitores where nro_orden_monitores = $nro_orden_monitores";
	$res = sql($sql) or fin_pagina();
	
	$cantidad    = $res->fields["cantidad"];
	$descripcion = $res->fields["descripcion"];
	$fecha       = fecha($res->fields["fecha"]);
	$nro_serie_desde = $res->fields["nro_serie_desde"];
	$nro_serie_hasta = $res->fields["nro_serie_hasta"];
	$activo      = $res->fields["activo"];   

	$sql = "select numero from ordenes.numeros_series_monitores 
	       where nro_orden_monitores = $nro_orden_monitores
	       order by id_numeros_series_monitores ASC limit 1";
	$res = sql($sql) or fin_pagina();
	
	$numero_generado = $res->fields["numero"];
	$disable_etiquetas = "";
	
}//del if
else{
	//traigo el ultimo numero de serie generado 
	$sql = "select numero from ordenes.numeros_series_monitores 
	       order by id_numeros_series_monitores DESC Limit 1";
	$res = sql($sql) or fin_pagina();
	
	$numero_generado = $res->fields["numero"];
	$numero_generado = proximo_nro_serie($numero_generado);
	$disable_etiquetas = "disabled";
}
cargar_calendario();
echo $html_header;
?>
<script>
function control_datos(){
var control,msg;

msg = "--------------------------\n";
control = false;

	if (document.form1.fecha.value==""){
		msg += 'En el campo Fecha debe ingresar una fecha válida \n';
		control = true;
	}

	if (isNaN(document.form1.cantidad.value)){
		msg += 'En el campo número debe ingresar un número válido \n';		
		control = true;
	}
	

	
	if (control) {
		alert(msg);
		return false;
	}
return true;	
}
</script>
<form name="form1" method="POST" action="ordenes_monitores.php" enctype='multipart/form-data'>
<input type="hidden" name="nro_orden_monitores" value="<?=$nro_orden_monitores?>">
<? if ($nro_orden_monitores){
    $sql=  "select usuario,tipo,fecha from log_monitores 
                  where nro_orden_monitores = $nro_orden_monitores order by fecha DESC";
    $log = sql($sql) or fin_pagina();    
 ?>   
<div align="center">
<div style="display:'display';width:70%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;'?> " id="tabla_logs">
    <table class="bordes" width="99%" align="center" bgcolor="<?=$bgcolor2?>">
      <tr id=mo><td>Logs</td></tr>
      <tr> 
        <td>
          <table width="100%" align="center">
             <tr id=ma>
                <td>Usuario </td>
                <td>Fecha   </td>
                <td>tipo    </td>
             </tr>
             <?
             for($i=0; $i < $log->recordcount() ; $i++){
             ?>
               <tr <?=atrib_tr()?>>
                 <td><?=$log->fields["usuario"]?></td>
                 <td><?=Fecha($log->fields["fecha"])." ".Hora($log->fields["fecha"])?></td>
                 <td><?=$log->fields["tipo"]?></td>
               </tr>	
             <?
             $log->movenext();	
             }//del for             
             ?>
          </table>
        </td>
      </tr>
      
    </table>
 </div>   
 </div>
<?}?>

<table class="bordes" width="70%" align="center" bgcolor="<?=$bgcolor2?>">
  <tr id=mo>
     <td colspan="2"> Monitores - Planta San Luis</td></tr>
  <tr>
  <tr>
     <td bgcolor="white" align="right" width="3%"> <b>Nro Orden :</b></td>
     <td > <font color=red><b><?=$nro_orden_monitores?></b></font></td>
  </tr>
  
  <tr>
     <td bgcolor="white" align="right" width="5%"> <b>Fecha: </b></td>
     <td > <input type="text" name="fecha" value="<?=$fecha?>" size="10"><?=link_calendario("fecha")?></td>
  </tr>

  <tr>
     <td bgcolor="white" align="right" valign="top"> <b>Descripción: </b></td>
     <td align="left"    valign="top"> 
       <textarea name="descripcion" rows="5" style="width:95%"><?=$descripcion?></textarea>
     </td>
  </tr>  
  
  <?
  $datos = descomponer_nro_serie($numero_generado);
  ?>
  
  <tr>
      <td id=mo colspan="2"><b>Nros de Series</b></td>
  </tr>
  <?if ($nro_orden_monitores){?>
  <tr>
     <td bgcolor="white" align="right" width="5%"> <b>Nros de Series: </b></td>
     <td><font color=red><b><?=$nro_serie_desde?>........<?=$nro_serie_hasta?></b></font></td>
  </tr>     
  <?}?>
  <tr>
      <td bgcolor="white" align="right"><b>Cantidad:</b></td>
      <td>
       <input type="text" name="cantidad" value="<?=$cantidad?>" size="10">
        <b>Usar múltiplos de 9 para aprovechar mejor las hojas</b>
       </td>
  </tr>
  
  <tr>
     <td bgcolor="white" align="right"><b>Planta :</b></td>
     <td width="20%">
           <select name="planta" style="width:50">
               <option> SL</option>
           </select>
     </td>
 </tr>
 <tr>
     <td bgcolor="white" align="right""><b>Año: </b></td>
     <td>
       <select name="anio" style="width:50">
         <?for($i=2007; $i < 2030; $i++){
         	$selected=(substr($i,2,2)==substr($datos["año_mes"],0,2))?"selected":"";
         ?>
         <option <?=$selected?>> <?=substr($i,2,2)?></option>
         <?}//del for?>
	   </select>             
      </td>
 </tr>
 <tr>
      <td bgcolor="white" align="right"><b>Mes: </b></td>
      <td>
        <select name="mes" style="width:50">
          <?for($i=1; $i < 13; $i++){
            ($i<10)?$mes="0".$i:$mes=$i;
         	$selected=($mes==substr($datos["año_mes"],2,2))?"selected":"";               
          ?>
          <option <?=$selected?>><?=$mes?></option>
          <?}//del for?>
          </select>
       </td>
 </tr>
 <tr>
      <td bgcolor="white" align="right"><b>Serie: </b></td>
      <td >
          <table align="left">
            <tr>
              <td>
                 <select name="alfabeto1">
	                 <?foreach ($abecedario as $letra){
	                   $selected = ($datos["primera_letra"]==$letra)?"selected":"";	
	                 ?>
				      <option <?=$selected?>><?=$letra?></option>	
				     <?}?>	
                 </select>
             </td>
             <td>
                <select name="alfabeto2">
	                <?foreach ($abecedario as $letra){
	                  $selected = ($datos["segunda_letra"]==$letra)?"selected":"";		                	
	                ?>
				    <option <?=$selected?>><?=$letra?></option>	
				    <?}?>	               
                </select>			    
			 </td>
			 <td>
                <select name="alfabeto3">
	                <?foreach ($abecedario as $letra){
	                  $selected = ($datos["tercera_letra"]==$letra)?"selected":"";		                	
	                ?>
					 <option <?=$selected?>><?=$letra?></option>	
					 <?}?>	               
                </select>
             </td>
             <td>
                <select name="numerico1">
	                <?for($i=0; $i < 10 ; $i++){
	                  $selected = ($datos["primer_numero"]==$i)?"selected":"";		                		                	
	               	?>
	                <option <?=$selected?>><?=$i?></option>
	                <?}//del for?>
                </select>
             </td>   
             <td>
                <select name="numerico2">
	                <?for($i=0; $i < 10 ; $i++){
	                  $selected = ($datos["segundo_numero"]==$i)?"selected":"";		                		                		                	
	                ?>
	                <option <?=$selected?>><?=$i?></option>
	                <?}//del for?>
                </select>
             </td>             
             <td>
                <select name="numerico3">
	                <?for($i=0; $i < 10 ; $i++){
	                 $selected = ($datos["tercer_numero"]==$i)?"selected":"";		                		                		                	
	                ?>
	                <option <?=$selected?>><?=$i?></option>
	                <?}//del for?>
                </select>
             </td>
             </tr>
            </table>             
       </td>
    </tr>
    <?
    $link=encode_link("etiquetas_monitores.php",array("nro_orden_monitores"=>$nro_orden_monitores));
    ?>
    <tr>
     <td colspan=2><input type="button" name="etiquetas" value="Ver Etiquetas" <?=$disable_etiquetas?> onclick="window.open('<?=$link?>')"></td>
    </tr>
    <?
    
    ?>
    <tr>
      <td colspan="2" id="mo">Comentarios</td>
    </tr>
    <tr>
      <td colspan="2">
      <?
      gestiones_comentarios_monitores($nro_orden_monitores);
      ?>
      </td>
    </tr>
    
    
    <?
    if ($nro_orden_monitores){
    	$sql = "select * from ordenes.archivos_monitores 
    	        where nro_orden_monitores = $nro_orden_monitores";
    	$res =sql($sql) or fin_pagina();
    ?>
    <tr><td colspan="2">&nbsp;</tr>
    <tr>
      <td colspan="2" id="mo">Archivos</td>
    </tr>
   
    <tr>
      <td colspan="5">
	     	<table width='100%' border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
	     	<tr id=ma>
			  <!--<td width='1'>&nbsp;</td>-->
			  <td width="40%">Archivo</td>		
			  <td width="10%">Tamaño (byte)</td>		
			  <td width="40%">Subido Por</td>		
			  <td width="10%">Fecha Carga</td>					  
			</tr>
			<?
			 for($i=0; $i < $res->recordcount(); $i++){
			 	$id_archivo = $res->fields["id_archivo_monitores"];
			 	$nombre     = $res->fields["archivo"];
			 	$tamano     = $res->fields["tam_arch"];
			 	$subidopor  = $res->fields["subidopor"];
			 	$fecha_carga= $res->fields["fecha_carga"];
			?> 	
			  <tr>
			    <?/*<td><input type="checkbox" name="check_archivos_<?=$i?>" value="<?=$id_archivo?>"></td>*/?>
			    <td>
			    	<?if (is_file("../../uploads/mon_ordenes/".$nro_orden_monitores.'-'.$nombre))?>
        		<a href='<?=encode_link("ordenes_monitores.php",array ("file" =>$nro_orden_monitores.'-'.$nombre,"size" => $tamano,"cmd" => "download"))?>'>
    			<?=$nombre?></a>		    
			    </td>
			    <td><?=$tamano?></td>
			    <td><?=$subidopor?></td>
			    <td><?=fecha($fecha_carga)?></td>
			  </tr>
			<?	
			  $res->movenext();
			 }//del for			 
	     	?>
			 <tr>
			    <td colspan="5" align="center">
			      <input type="file" name="archivo" style="width=350px">
			      &nbsp;
			      <input type="submit" name="guardar_archivo" value="Guardar">
			      <!--<input type="submit" name="eliminar" value="Eliminar">-->
			    </td>            
			 </tr>
	        </table>
      </td>
    </tr>
    <?}//del if ($nro_orden_monitores){
     if ($activo == 2) $disabled_eliminar = " disabled";
    ?>
    <tr>
      <td colspan="2" align="center">
      <input type="submit" name="aceptar" value="Aceptar" onclick="return control_datos();">
      &nbsp;
      <input type="button" name="volver" value="Volver" onclick="document.location='listado_ordenes_monitores.php'">
      &nbsp;
      <? if ($activo == 1) {?>
      <input type="submit" name="eliminar" value="Eliminar" onclick="return confirm('Esta Seguro que desea eliminar la orden')">      
      <? }
         if ($activo == 2) {         	
      ?>
      <input type="submit" name="restaurar" value="Restaurar" onclick="return confirm('Esta Seguro que desea restaurar la orden')">            
      <?
         }
      ?>
      </td>
    </tr>
</table>
</form>
<? echo fin_pagina()?>

