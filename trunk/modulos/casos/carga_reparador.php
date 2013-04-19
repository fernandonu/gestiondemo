<?
require_once("../../config.php");

echo $html_header;
?>
<script>
function control_comentario(){
	if(document.all.nro_remito.value.indexOf('"')==-1){
		opener.document.all.nro_remito_h.value=document.all.nro_remito.value;
		opener.document.all.id_reparador_h.value=document.all.reparadores.value;
		opener.document.all.eliminar_aux.value="true";
		opener.document.all.form1.submit();
	 	window.close();
	}
	else{
		alert("No ingrese comillas dobles en los comentarios");
	}
}//fin funcion
function control_datos_reparador(){
	if(document.all.reparadores.value=="-1"){
 		alert('Debe Seleccionar un Reparador');
  		return false;
 	}
 	if(document.all.nro_remito.value==""){
 		alert('Debe Ingresar un Número de Remito');
  		return false;
 	}
	control_comentario()
	return true;
}
</script>
<center>
    <h4>Reparador: <select name=reparadores>
     <option value=-1>Seleccione</option>
                 <?
                 $sql= "select * from casos.reparadores";
                 $result_reparadores=sql($sql) or fin_pagina();
                 while (!$result_reparadores->EOF){ 
                 	$id_rep=$result_reparadores->fields['id_reparador'];
                 	$nombre=$result_reparadores->fields['nombre_reparador'];
                 ?>
                   <option value=<?=$id_rep;?> ><?=$nombre?></option>
                 <?
                 $result_reparadores->movenext();
                 }
                 ?>
      </select></h4>
    <br>
	<h4>Número de Remito: <input type="text" name="nro_remito" value=""></h4>
    <br>
	<p> 																																												
      <input name="boton" type="button" value="Guardar" onclick="return control_datos_reparador()">
      &nbsp;
      <input name="boton" type="button" value="Cerrar" onclick="window.close();">
    </p>
</center>
</body>
</html>
