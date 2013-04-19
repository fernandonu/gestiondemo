<?php
require_once("../../config.php");

if($_POST['guardar']=='Guardar Cambios'){
	$nro_orden=$_POST['nro_orden'];
	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user['name'];
	$comentario=$_POST['comentario'];
	$db->StartTrans();  
	$sql="INSERT INTO compras.log_no_tiene_factura (nro_orden,fecha,usuario,comentario) VALUES ($nro_orden,'$fecha','$usuario','$comentario')";
	sql($sql,"No se puede Ejecutar el insert del log") or fin_pagina();
	$sql="UPDATE compras.orden_de_compra SET no_tiene_factura=1 WHERE nro_orden = $nro_orden ";
	sql($sql,"No se puede Ejecutar el update en orden de compra") or fin_pagina();
	$mensaje="Se Actualizo con Exito la Orden de Compra/Pago";
	$db->CompleteTrans(); 
	
	$link=encode_link("reporte_oc_pagadas.php",array('mensaje'=>$mensaje));
    header("Location:$link") or die("No se encontró la página destino");
}
?>
<script>
	function control_datos(){
		if(document.all.comentario.value==""){
			alert ('Debe Ingresar un Comentario');
			return false;
		}
		return true;
	}
</script>
<?
	echo $html_header;
	$nro_orden=$parametros['nro_orden'];
	?>
	<form name="form1" method="POST" action="comentario_marca_factura.php">
		<input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
		<table class="bordes" cellspacing="0" bgcolor="<?=$bgcolor2?>" width="90%" align="center">
			<tr>
				<td id="mo">Comentario</td>
			</tr>
			<tr>
				<td colspan="3" align="center">
					<textarea cols="90" rows="6" name="comentario"></textarea>
				</td>
			</tr>
		</table>
		<table class="bordes" cellspacing="0" bgcolor="<?=$bgcolor2?>" width="90%" align="center">
			<tr>
				<td align="center">
					<input type="submit" name="guardar" value="Guardar Cambios" onclick="return control_datos()">
					<input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
				</td>
			</tr>
		</table>
		<br>
</form>

<?fin_pagina();?>