<?
/*
$Author: ferni $
$Revision: 1.10 $
$Date: 2005/11/24 18:30:21 $
*/

require_once("../../config.php");
echo $html_header;
/*****************************************************************/
/**
 * Function agregar_foto_cv()
 */
function agregar_foto_cv() {
	global $html_root;
	echo "<script>
		function mostrar_foto(st) {
			if (st.value.indexOf('.jpg')>=0) {
				document.all.foto.src=st.value;
				document.all.subir_foto.value='si';
			}
			else {
				document.all.subir_foto.value='no';
				alert ('Debe Subir un archivo jpg');
				document.all.foto.src='$html_root/imagenes/sin_imagen1.jpg';
			}
			document.all.guardar.focus();
		}
	</script>\n";
	echo "<table border='1' align='center' cellpadding='4'>
	<tr><td colspan=2 align='center' title='Presione el botón Examinar y seleccione la foto'> <b>ADJUNTE SU FOTO </b></td></tr>
	<tr>
		<td align=center> 
			<b>Foto Personal:</b><br><i>(Solo es posible subir<br>fotos en formato jpg).</i>
		</td>
		<td align=center>
			<input type=hidden name='subir_foto' value='no'>
			<br><img src='$html_root/imagenes/sin_imagen1.jpg' name=foto id=foto width=150 height=150 border=1><br><br>
			<input type=file onchange='mostrar_foto(this);' onkeypress='return false;' onpaste='return false;' id='ft_file' name='ft_file'>
		</td>
	</tr>
	</table>
	<br>
	";
}

/**********************************************************************/
if ($_POST['baja']=="Dar de Baja") {
	$id_tecnico_visita=$_POST['tecnicos'];
	if (($_POST['tecnicos'] != -1) and ($id_tecnico_visita)){
			$sql = "update tecnicos_visitas set activo=0 where id_tecnico_visita=$id_tecnico_visita";
			$res=sql($sql,"Baja Tecnicos") or fin_pagina();
 			$msg_baja="Lo datos se actualizarón con exito.";
 			echo "<div align='center'> <font color='red' size='2+'>$msg_baja</font></div>";
	}
}

if ($_POST['guardar']) {
	$nombre=$_POST['nombre'];
	$apellido=$_POST['apellido'];
	$direccion=$_POST['direccion'];
	$iut=$_POST['iut'];
	$telefono=$_POST['telefono'];
	$documento=$_POST['documento'];
	$mail=$_POST['mail'];
	$idate=$_POST['atendido'];
	$id_usuario=$_POST['id_usuario'];

	$fecha_inicio_mostrar=$_POST['text_fecha'];
	$fecha_inicio_db=Fecha_db($fecha_inicio_mostrar);
 
	$db->StartTrans();
if (!$error) {
 	if ($_POST['tecnicos'] != -1) { //update
 	$id_tecnico_visita=$_POST['tecnicos'];
 	$sql="update tecnicos_visitas set idate=$idate, nombre='$nombre', apellido='$apellido', telefono='$telefono',
          email='$mail', direccion='$direccion',iut='$iut',dni='$documento',fecha_inicio_tec_visitas='$fecha_inicio_db',"; 
 	if ($id_usario != "" || $id_usuario != null)
     	$sql.="id_usuario=$id_usuario";
 	else $sql.="id_usuario=null";          
 	$sql.="  where id_tecnico_visita=$id_tecnico_visita";
 	$res=sql($sql,"update tecnicos ") or fin_pagina();
 	$msg=" Lo datos se actualizarón con exito.";
	}
	else { //insert
		$sql="SELECT nextval('casos.tecnicos_visitas_id_tecnico_visita_seq') as id";
		$res=sql($sql) or fin_pagina();
		$id_tecnico_visita=$res->fields['id'];
		$sql="insert into tecnicos_visitas
   			  (id_tecnico_visita,idate,nombre,apellido,telefono,email,direccion,dni,fecha_inicio_tec_visitas,iut,id_usuario) values
   		  	($id_tecnico_visita, $idate,'$nombre','$apellido','$telefono','$mail','$direccion','$documento','$fecha_inicio_db','$iut',";
	
		if ($id_usuario !="" || $id_usuario !=null)
    		$sql.="$id_usuario)";
			else $sql.="null)";    
		$res_tecnicos=sql($sql,"inserta tecnicos ") or fin_pagina();  
		$msg=" Los datos se insertarón con exito.";
	}  //fin else insert
}

if (!$error) {
		$path = MOD_DIR."/casos/fotos";
		$name = $_FILES["ft_file"]["name"];
		$temp = $_FILES["ft_file"]["tmp_name"];
		$size = $_FILES["ft_file"]["size"];
		$type = $_FILES["ft_file"]["type"];
		$extensiones = array("jpg");
		if ($name) {
			$name = strtolower($name);
			$ext = substr($name,-3);
			if ($ext != "jpg") {
				Error("El formato de la imagen debe ser jpg");
			}
			$name = "foto_$id_tecnico_visita.jpg";
			$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,"",$extensiones,"",1,0);
			if ($ret["error"] != 0) {
				Error("No se pudo subir el archivo");
			}
		}
}

if ($db->CompleteTrans()) {
 echo "<div align='center'> <font color='red' size='2+'>$msg</font></div>";
}
}//del boton guardar
/***********************************************************/

?>

<script>
function control_datos() {
	
 if(document.all.nombre.value=="") {
  alert('Ingrese el nombre del técnico.');
  return false;
 } 	
 if(document.all.apellido.value=="") {
  alert('Ingrese el apellido del técnico.');
  return false;
 } 	
 if(document.all.text_fecha.value=="") {
  alert('Ingrese una Fecha de Ingreso.');
  return false;
 } 
 
 /*
 if (document.all.subir_foto.value=="no"){
  alert ('Debe ingresar una foto personal');
  return false;
 }*/
 
 return true;
}

function control_datos_1(){
 if(document.all.tecnicos.options[document.all.tecnicos.selectedIndex].value==-1) {
     alert('Seleccione un técnico.');
  return false;
 } 
return true;
}

function limpiar () {
  document.all.nombre.value="";
  document.all.apellido.value="";
  document.all.telefono.value="";
  document.all.iut.value="";
  document.all.documento.value="";
  document.all.mail.value="";
  document.all.direccion.value="";
  document.all.id_usuario.value="";
  document.all.text_fecha.value="";
  document.all.tecnicos.options[0].selected=true;  
  document.all.form1.submit();
}

function confirma_baja(){
	 var entrar = confirm("¿Confirma la BAJA?")
	 if ( entrar ){ 
	 	return true;
	 }
	 else{
	 	return false;
	 }
}
</script>
<form name='form1' action="nuevo_tecnico_visita.php" method='post' enctype="multipart/form-data">
   
<br>
<?

if ($_POST['mostrar']) {
	$id_usuario=$_POST['usuarios'];
	$id_tecnico_visita=-1;
	$sql="select id_usuario,nombre,apellido,direccion,telefono,mail from casos.permisos_tecnicos join 
	sistema.usuarios using(id_usuario) where id_usuario=$id_usuario";
	$res_tecnicos=sql($sql,"selecciona tecnicos ") or fin_pagina();  
	$nombre=$res_tecnicos->fields['nombre'];
	$apellido=$res_tecnicos->fields['apellido'];
	$direccion=$res_tecnicos->fields['direccion'];
	$telefono=$res_tecnicos->fields['telefono'];
	$documento=$res_tecnicos->fields['dni'];
	$mail=$res_tecnicos->fields['mail'];
	$idate=$res_tecnicos->fields['idate'];
} elseif (($_POST['datos']) or ($parametros['pagina_viene']=="muestra_seleccionado"))  {

	if ($parametros['pagina_viene']=="muestra_seleccionado"){
		$id_tecnico_visita=$parametros['id_tecnicos_visitas'];
	}
	else $id_tecnico_visita=$_POST['tecnicos'];

	$sql="select id_tecnico_visita,idate,id_usuario,nombre,apellido,telefono,
    	  email,direccion,dni,activo,fecha_inicio_tec_visitas, iut from tecnicos_visitas where id_tecnico_visita=$id_tecnico_visita and activo=1 order by apellido";
	$res_tecnicos=sql($sql,"selecciona tecnicos ") or fin_pagina();  
	$nombre=$res_tecnicos->fields['nombre'];
	$apellido=$res_tecnicos->fields['apellido'];
	$iut=$res_tecnicos->fields['iut'];
	$direccion=$res_tecnicos->fields['direccion'];
	$telefono=$res_tecnicos->fields['telefono'];
	$documento=$res_tecnicos->fields['dni'];
	$mail=$res_tecnicos->fields['email'];
	$idate=$res_tecnicos->fields['idate'];
	$id_usuario=$res_tecnicos->fields['id_usuario'];
	$fecha_inicio_mostrar=$res_tecnicos->fields['fecha_inicio_tec_visitas'];
} 
elseif (!$id_tecnico_visita) $id_tecnico_visita=-1;


?>
 <input type='hidden' name='id_usuario' value='<?=$id_usuario?>'>
<? $visib = "none";?>

<table width=325 align=center border="1" cellpadding="2" cellspacing="0" style="border-collapse: collapse; " bordercolor="#9A9A9A">
<tr>
  <td align=center align=center bgcolor="<?=$bgcolor1?>" colspan=2>
   <p class=menutitulo style='margin-bottom: 0;'><font color="White">
        <b>Datos Personales</b></font></p>
  </td>
</tr>
<tr bgcolor="<?=$bgcolor3?>">
 <?
       echo "<td  align=left><b>Cargar técnico desde el sistema:</b>
       <input type=checkbox name=det_ing  onclick='javascript:(this.checked)?Mostrar(\"tabla_det_ing\"):Ocultar(\"tabla_det_ing\");'></td><td>";
        echo " <div id='tabla_det_ing' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr>";
	    echo "<td><select  name='usuarios'  onKeypress='buscar_op(this);' onblur='borrar_buffer();' onclick='borrar_buffer();'>";
   
   $sql="select id_usuario,nombre,apellido,direccion,telefono,mail 
         from permisos_tecnicos join 
         usuarios using(id_usuario)
         where id_usuario not in 
         (select id_usuario from casos.tecnicos_visitas where id_usuario is not null)
         order by apellido";
     $res=sql($sql,"selecciona tecnicos ") or fin_pagina();  
  
   while (!$res->EOF)
    {
    ?>
     <option value="<?=$res->fields['id_usuario']; ?>"><?=$res->fields['apellido']." ".$res->fields['nombre'];?></option>
    <?
    $res->MoveNext();
    }
   ?>
  </select>
  &nbsp;&nbsp;
  <input type='submit' name='mostrar' value='Cargar Datos'>
  <? 
  echo"</td>";
  echo "</table></div></td>";
?>
</tr>
 <tr  bgcolor="<?=$bgcolor3?>">
  <td>
   <table width=100% border=0>
	<tr  bgcolor="<?=$bgcolor3?>">
	 <td align="right"> <font color=red>*</font> Nombre:</td>
	 <td> <input type='text' name=nombre value='<?=$nombre;?>' size=60> </td>
	</tr>
	<tr  bgcolor="<?=$bgcolor3?>">
	 <td align="right"> <font color=red>*</font> Apellido: </td>
	 <td> <input type='text' name=apellido value='<?=$apellido;?>' size=60> </td>
	</tr>
	<tr  bgcolor="<?=$bgcolor3?>">
	 <td align="right"> IUT: </td>
	 <td> <input type='text' name=iut value='<?=$iut;?>' size=60> </td>
	</tr>
	<tr  bgcolor="<?=$bgcolor3?>">
	 <td align="right"> Documento: </td>
	 <td> <input type='text' name=documento value='<?=$documento;?>' size=60> </td>
	</tr>
	<tr  bgcolor="<?=$bgcolor3?>">
	 <td align="right"> Dirección: </td>
	 <td>  <input type='text' name=direccion value='<?=$direccion;?>' size=60> </td>
	</tr>
	<tr  bgcolor="<?=$bgcolor3?>">
	 <td align="right">  Teléfono: </td>
	 <td>  <input type='text' name=telefono value='<?=$telefono;?>' size=60> </td>
	</tr>
	<tr bgcolor="<?=$bgcolor3?>">
	 <td align="right"> E-mail: </td>
	 <td> <input type='text' name=mail value='<?=$mail;?>' size=60></td>
	</tr>
	<tr>
	<td align="right"> Atiende en: </td>
	<td>
   <select  name="atendido"  onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();" style="width=378">
     <?
     $sql="select idate,nombre from cas_ate where (activo=1) order by nombre";
     $res=sql($sql,"selecciona tecnicos ") or fin_pagina();  
  
   while (!$res->EOF)
    {
    ?>
     <option value="<?=$res->fields['idate']; ?>" <?if($idate==$res->fields['idate']) echo 'selected'?>><?=$res->fields['nombre'];?></option>
    <?
    $res->MoveNext();
    }
   ?>
  </select>
    </td>
	</tr>
	
	<tr bgcolor="<?=$bgcolor3?>">
	 <td align="right"><font color=red>*</font> Fecha de Inicio: </td>
	 
	 <td>  <?
	 		if (!strstr($fecha_inicio_mostrar,"/"))$fecha_inicio_mostrar=fecha($fecha_inicio_mostrar);
	 		?>
	 	   <input type='text' name='text_fecha' value='<?=$fecha_inicio_mostrar;?>'>
    	   &nbsp;<? cargar_calendario(); echo link_calendario("text_fecha"); ?>
     </td>
	 	 
	</tr>
	
   </table>
   </td>
   
   <td>
   <table>
   <tr><td><b>Filtro: </b></td></tr>   
   <tr>
    	<td>
    		<select name="filtro_atiende" style="width=250" onchange="window.document.form1.submit()">
   			<option value='%' <? if ($_POST['filtro_atiende'] == '%') echo 'selected';?> >Todos</option>
   			<?
   			$sql="select idate, nombre from casos.cas_ate order by nombre";
   			$res_ate=sql($sql,"selecciona tecnicos ") or fin_pagina();  
   			while (!$res_ate->EOF)
    		{?>
     		<option value="<?=$res_ate->fields['idate'];?>" 
     		<? if ($res_ate->fields['idate'] == $_POST['filtro_atiende']) echo 'selected';?>>
          	<?=$res_ate->fields['nombre'];?></option>
    		<?
    		$res_ate->MoveNext();
    		}
  			?>	
  			</select>
    	</td>
    </tr>
    
    <tr><td><br><br><b>Listado de Técnicos: </b></td></tr>  
    <tr>
      <td>
   		<select size="10" name="tecnicos"  onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();" style="width=250">
   		<option value='-1' <?if ($id_tecnico_visita==-1) echo 'selected'?>> seleccione un técnico</option>
   		<?
   		//viene del combo de arriva que realiza el filtro
   		if ($_POST['filtro_atiende']) $filtro_tec="'" . $_POST['filtro_atiende'] . "'";
   		else $filtro_tec="'%'";
   		
   		$sql="select id_tecnico_visita,idate,id_usuario,nombre,apellido,telefono,
             email,direccion,dni,activo,fecha_inicio_tec_visitas from tecnicos_visitas where ((activo=1) and (idate like $filtro_tec)) order by apellido";
   		$res_tecnicos=sql($sql,"selecciona tecnicos ") or fin_pagina();  
   		while (!$res_tecnicos->EOF){?>
     	<option value="<?=$res_tecnicos->fields['id_tecnico_visita'];?>"
        <? if ($res_tecnicos->fields['id_tecnico_visita'] == $id_tecnico_visita ) echo 'selected';?>>
        <?=$res_tecnicos->fields['apellido']." ".$res_tecnicos->fields['nombre'];?></option>
    	<?
    	$res_tecnicos->MoveNext();
    	}?>
  		</select>
   	   </td>
   </tr>
   
   </tr>
  
   <tr bgcolor="<?=$bgcolor3?>">
     <td colspan=2 align='right'>
     <input type='submit' name='datos' value='Ver Datos' onclick="return control_datos_1();">
     <input type='button' name='nuevo' value='Nuevo' onclick="limpiar();">
     </td>
   </tr>
   </table>
   
   <tr bgcolor="<?=$bgcolor3?>">
   <? 
   
    if (file_exists(MOD_DIR."/casos/fotos/foto_$id_tecnico_visita.jpg")) {
		$foto = "fotos/foto_$id_tecnico_visita.jpg";
		echo "<td align=center colpan=2 width=50%><img width=120 height=120 src='$foto'></td>";
	}
	//else { $foto = "fotos/sin_imagen1.jpg"; }
	else {?>
     <td align=center colspan=2>
     <? echo agregar_foto_cv();?>
    </td>
  <?} ?>
   

  </tr>
</table>

<br>
<div align="center">
  <input type="submit" name="guardar" value="Guardar" onclick="return control_datos();" style="width=150">&nbsp;&nbsp;
  <input type="submit" name="baja" value="Dar de Baja" onclick="return confirma_baja();" style="width=150">&nbsp;&nbsp;
  <?if (($parametros['pagina_viene']=='caso_organismo') or ($_POST['pagina_viene']=='caso_organismo') or ($parametros['pagina_viene']=="muestra_seleccionado")){?>
	  <input type="hidden" value="caso_organismo" name="pagina_viene">
  	  <input type="button" name="volver" value="Volver" onclick="window.opener.location.reload();window.close();" style="width=150">
  <?}?>
</div>
</form>