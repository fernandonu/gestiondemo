<?
/*AUTOR: MAD
               1 julio 2004
$Author: marcelo $
$Revision: 1.8 $
$Date: 2004/09/27 23:12:02 $
*/
/*
Este script sirve para subir archivos de configuracion de quemado al gestion.
*/
require_once("../../config.php");


$sql="Select ensamblador_quemado.*,ensamblador.nombre from ensamblador_quemado join ensamblador using(id_ensamblador)";
$result_ensambladores = sql($sql,"Error:".$sql);

$sql2="Select * from config_quemado order by duracion asc";
$result_config =  sql($sql2,"Error:".$sql2);


$msg = "Seleccione la operación a realizar";
//parte de Subir archivos de configuracion
if (isset($_POST["actualizar"])) {
	while (!$result_config->EOF) {
		if ($HTTP_POST_FILES['archivo_'.$result_config->fields["id_config"]]['tmp_name']!=''){
			//print_r($HTTP_POST_FILES['archivo_'.$result->fields["id_config"]]['tmp_name']);			
			
			$arch_config = $HTTP_POST_FILES['archivo_'.$result_config->fields["id_config"]]['tmp_name'];
			//carga del archivo de configuracion
			$arch = fopen($arch_config,"r");
			$configuracion = base64_encode(fread($arch,filesize($arch_config)));
			fclose($arch);			

			$sql_update = "update config_quemado set data='$configuracion' where id_config = ".$result_config->fields["id_config"];
			$db->Execute($sql_update) or die ($db->ErrorMsg().'<br>'.$sql_update);
			$msg = "<font color='green'>Archivos de configuración actualizados</font>";
		}
		$result_config->MoveNext();
	}
$result_config->MoveFirst();
}

//parte de actualizar los ensambladores con servicio de quemado
if (isset($_POST["actualizar_ensamblador"])) {
	while (!$result_ensambladores->EOF){
		$activo = $_POST["chk_".$result_ensambladores->fields["id_entrada"]]=='on';
		if ($activo=='') $activo = 0;
		$http = $_POST["ens_".$result_ensambladores->fields["id_entrada"]];
		if ($_POST["ens_".$result_ensambladores->fields["id_entrada"]]!=''){
			$sql_update = "update ensamblador_quemado set activo='$activo',http='$http' where id_entrada = ".$result_ensambladores->fields["id_entrada"];
			$db->Execute($sql_update) or die ($db->ErrorMsg().'<br>'.$sql_update);
			$msg = "<font color='green'>Entradas de servidores de Ensambladores actualizadas</font>";
		}
		$result_ensambladores->MoveNext();
	}
	$result_ensambladores = sql($sql,"Error:".$sql);
	$result_ensambladores->MoveFirst();
}

//parte de nueva entrada de servidor
if (isset($_POST["nuevo_ensamblador"])) {
	$id_ensamblador = $_POST["ensambladores"];
	$sql_insert = "insert into ensamblador_quemado (id_ensamblador,http,activo) values ($id_ensamblador,'No definido',0)";
	$db->Execute($sql_insert) or die ($db->ErrorMsg().'<br>'.$sql_insert);
	$msg = "<font color='green'>Se Agrego un nuevo servidor de quemado</font>";

	$result_ensambladores = sql($sql,"Error:".$sql);
	$result_ensambladores->MoveFirst();
}

//parte de eliminar una entrada de servidor
if ($parametros["cmd"]=="eliminar_ensamblador") {
	$id_entrada = $parametros["ID"];
	$sql_delete = "delete from ensamblador_quemado where id_entrada=$id_entrada";
	$db->Execute($sql_delete) or die ($db->ErrorMsg().'<br>'.$sql_delete);
	$msg = "<font color='green'>Se Elimino una entrada de Servidor de Quemado</font>";

	$result_ensambladores = sql($sql,"Error:".$sql);
	$result_ensambladores->MoveFirst();
}

$sql3="Select nombre,id_ensamblador from ensamblador where id_ensamblador not in (select id_ensamblador from ensamblador_quemado) order by nombre asc";
$result_lista_ens =  sql($sql3,"Error:".$sql3);

$functions = "onload='document.focus();' onunload='window.opener.form1.submit();'";
echo str_replace("onload='document.focus();'",$functions,$html_header);
?>
<CENTER><B>
<?=$msg?>
</B></CENTER><BR>
<TABLE width="95%" align="center" class="bordes" id="mo"> 
<TR>
	<TD id="ma" class="bordes">
	Subir archivos de configuracion del Quemado.
	</TD>
	<TD id="ma" class="bordes" width="10%">
	<INPUT type="checkbox" onclick="activar(this,document.all.div)">
	</TD>
</TR>
</TABLE>
<DIV id="div"  style="display:none">
<FORM id="form1" action="config_quemado.php" method="POST" enctype="multipart/form-data">
<TABLE width="95%" align="center" class="bordes" id="mo"> 
<TR id="mo">
	<TD> 
	<FONT color="Black">Tipo</FONT>	
	</TD>
	<TD> 
	<FONT color="Black">Archivo</FONT>	
	</TD>
</TR>

<? while(!$result_config->EOF) {?>
<TR id="mo">
	<TD> 
	<?=$result_config->fields("duracion")?> Hora/s
	</TD>
	<TD> 
	<INPUT type="file" name="archivo_<?=$result_config->fields("id_config")?>" onkeypress="return false;">
 	</TD>
</TR>

<?
$result_config->MoveNext();
}?>

<TR id="ma">
	<TD colspan="2">
	<INPUT type="hidden" name="cant" value="<?=$result_config->RecordCount()?>">
	<INPUT type="submit" name="actualizar" value="Actualizar">
	</TD>
</TR>
</TABLE>
</FORM>
</DIV>

<!-- Forma de configuracion de servidores activos-->
<TABLE width="95%" align="center" class="bordes" id="mo"> 
<TR>
	<TD id="ma" class="bordes">
	Configuración de servidores activos.
	</TD>
	<TD id="ma" class="bordes" width="10%">
	<INPUT type="checkbox" onclick="activar(this,document.all.div1);">
	</TD>
</TR>
</TABLE>

<DIV id="div1" style="display:none">
<FORM id="form2" action="config_quemado.php" method="POST">
<TABLE width="95%" align="center" class="bordes" id="mo"> 
<TR id="mo">
	<TD> 
	<FONT color="Black">Activo</FONT>	
	</TD>
	<TD> 
	<FONT color="Black">Ensamblador</FONT>	
	</TD>
	<TD colspan="2"> 
	<FONT color="Black">Servidor</FONT>	
	</TD>
</TR>

<? while(!$result_ensambladores->EOF) {?>
<TR id="mo">
	<TD>
	<INPUT type="checkbox" name="chk_<?=$result_ensambladores->fields["id_entrada"]?>" <?if ($result_ensambladores->fields["activo"]) echo "checked";?> onclick="return (document.all.ens_<?=$result_ensambladores->fields["id_entrada"]?>.value != 'No definido')">
	</TD>
	<TD> 
	<?=$result_ensambladores->fields["nombre"]?>
 	</TD>
	<TD> 
	<INPUT name="ens_<?=$result_ensambladores->fields["id_entrada"]?>" value="<?=$result_ensambladores->fields["http"]?>" title="<?=$result_ensambladores->fields["http"]?>" size="17">
 	</TD>
	<TD> 
	<INPUT type="button" name="eliminar_ensamblador" value="Elim" size="20" style="font-size:9px" onclick="return confirm('Esta seguro que desea borrar la entrada para el servidor de <?=$result_ensambladores->fields["nombre"]?>') && (document.location = '<?=encode_link($_SERVER["PHP_SELF"],array("ID"=>$result_ensambladores->fields["id_entrada"],"cmd"=>"eliminar_ensamblador"))?>');"> 
 	</TD>
</TR>

<?
$result_ensambladores->MoveNext();
}?>

<TR id="ma">
	<TD colspan="4">
	<INPUT type="submit" name="actualizar_ensamblador" value="Actualizar">
	</TD>
</TR>

<TR id="mo">
	<TD>
	Nuevo
	</TD>
	<TD>
	<SELECT name="ensambladores">
<?while (!$result_lista_ens->EOF){
	echo "<OPTION value = '".$result_lista_ens->fields["id_ensamblador"]."'>".$result_lista_ens->fields["nombre"]."</OPTION>";
	$result_lista_ens->MoveNext();
}?>
	</SELECT>
	</TD>
	<TD colspan="3">
	<INPUT type="submit" name="nuevo_ensamblador" value="Agregar a la lista">
	</TD>
</TR>

</TABLE>
</FORM>
</DIV>
<BR>
<CENTER>
<INPUT type="button" name="cerrar" value="Cerrar" onclick="window.close();">
</CENTER>
<SCRIPT>
function activar(obj1,obj2){
	if (obj1.checked) {
		obj2.style.display = 'block';
	} else {
		obj2.style.display= 'none';
	}
}
</SCRIPT>

<?fin_pagina(0,0,0);?>
