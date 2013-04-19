<?
/*
Autor: Carlitox
Creado: 11/04/2005

MODIFICADA POR
$Author: cestila $
$Revision: 1.9 $
$Date: 2005/04/13 21:30:13 $
*/

require_once("../../config.php");

$nro_orden=$parametros["orden_prod"];
$nro_caso=$parametros["caso"];
echo $html_header;
//print_r($parametros);
//altas en estock
if ($_POST["guardar"]=="Guardar") {
	$db->StartTrans();
	//agregamos cada producto por separado al stock RMA con el proveedor
	//seleccionado en la OC (el proveedor que viene en proveedor_reclamo)
	//y luego completamos la informacion correspondiente en la tabla info_rma
	//$items_rma=get_items();
	//print_r($_POST);
	//seleccionamos el id del deposito RMA
	$query="select id_deposito from depositos where nombre='RMA'";
	$dep_rma=sql($query) or fin_pagina();
	$id_dep=$dep_rma->fields['id_deposito'];
	$id_prov=$_POST['id_prov'];
	$id_prod=$_POST["producto_id"];	
	$cantidad=$_POST["cantidad"];
	$obs="Agregado desde nuevo stock RMA";
    $ordprod=$_POST["nro_orden"] or $ordprod="NULL";
    $nrocaso=$_POST["nro_caso"] or $nrocaso="NULL";
	$precio=$_POST["precio"];
	if (!$id_prov) $error="Falta ingresar un provedor.";
	if (!$id_prod) $error="Debe ingresar un producto.";
	
	if (!$error) {
		$sql=" select id_producto from precios ";
		$sql.="where id_producto=$id_prod ";
		$sql.="and id_proveedor=$id_prov ";
		$result=sql($sql) or fin_pagina();
		$cant_precios=$result->recordcount();
		if($cant_precios==0) {
			insertar_precio($id_prod,$id_prov,$precio);
		}
		
		if($cantidad!="" && $cantidad>0) {
			//revisamos si esta la entrada para ese producto, proveedor, deposito, en el stock.
			echo "$cantidad";
			$query="select count(*)as cuenta from stock where id_producto=$id_prod and id_deposito=$id_dep and id_proveedor=$id_prov";
			$esta=sql($query) or fin_pagina();
			if($esta->fields['cuenta']==0) {
				$fecha_modif=date("Y-m-d H:i:s",mktime());
				$sql="insert into stock(id_producto,id_deposito,id_proveedor,cant_disp,comentario,last_user,last_modif)
					values($id_prod,$id_dep,$id_prov,$cantidad,'$obs','".$_ses_user['login']."','$fecha_modif')";	
			}
			else {	
				$sql="update stock set ";
				$sql.="cant_disp=cant_disp+$cantidad,";
				$sql.=" comentario='$obs' ";
				$sql.=" where ";
				$sql.="id_producto=$id_prod ";
				$sql.=" AND id_deposito=$id_dep ";
				$sql.=" AND id_proveedor=$id_prov";
			}
			sql($sql) or fin_pagina();
	  
			//registramos en el historial el incremento de stock
			$query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
			$id_control_stock=sql($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock (agregar_RMA)");  
			$fecha_modif=date("Y-m-d H:i:s",mktime());
			$query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
				values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','".$_ses_user['name']."','Incremento generado nuevo stock RMA','p')";
			sql($query) or fin_pagina();
       
			//Insertamos en la tabla info_rma los datos correspondientes 
			//a la orden de compra y demas datos necesarios	  
			$query="select nextval('info_rma_id_info_rma_seq')as id_info_rma";
			$id_info=sql($query) or fin_pagina();
			$id_info_rma=$id_info->fields['id_info_rma'];
          
			$query="insert into info_rma(id_info_rma,id_deposito,id_producto,id_proveedor,nro_ordenc,nro_ordenp,nrocaso,cantidad)
				values ($id_info_rma,$id_dep,$id_prod,$id_prov,NULL,$ordprod,$nrocaso,$cantidad)";
			sql($query) or fin_pagina();
       
			$query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_info_rma)
				values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad,$id_info_rma)";
			sql($query) or fin_pagina();
	  
			$query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
				values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Incremento por nuevo stock RMA')";
			sql($query) or fin_pagina();
	     
		}//de if($cantidad!="" && $cantidad>0)
		$db->CompleteTrans();
		//echo "<script>window.location='listado_rma.php';<script>\n";
	}
	else {
		error($error);
	}
}
//fin altas en stock
?>
<script>
function nuevo_item() {
	pagina_prod="<?=encode_link('../general/productos2.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
    wproductos=window.open(pagina_prod+'&id_proveedor='+document.all.id_prov.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
}

function nuevo_item_2() {
	pagina_prod="<?=encode_link('stock_cambio_producto.php',array('onclickcargar'=>"window.opener.cargar_2()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
	wproductos_2=window.open(pagina_prod+'&id_producto='+document.all.producto_id.value,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=300,width=550,height=150');
}
function cargar() {
	insertar_ok=1;
	desc_prod.innerHTML=wproductos.document.all.descripcion.value
	tipo_prod.innerHTML=wproductos.document.all.tipo_prod.value 
	document.all.producto_id.value=wproductos.document.all.id_producto.value  
	document.all.precio.value=wproductos.document.all.precio.value;
	wproductos.close();
}
function cargar_2(){
	//document.all.precio.value=wproductos_2.document.all.precio.value;
	document.all.id_prov.value=wproductos_2.document.all.id_prov.value;
	desc_prov.innerHTML=wproductos_2.document.all.razon_social.value;
	document.all.precio.value=wproductos_2.document.all.precio.value;
	wproductos_2.close();
}
</script>
<form action="nuevo_rma.php" name="nuevo_rma" method="post"> 
<table width="90%" align="center" cellpadding="3">
<tr id=mo>
    <td colspan="2">
		<font size="3">Información del Producto</font>
	</td>
</tr>
<tr id=ma_sf>
    <td width="30%">
		<b>Asociar RMA</b>
	</td>
	<td>	
		<input type="button" name="asoc_produccion" value="Asociar con Producción" onClick="window.location='<?= encode_link("../ordprod/ordenes_ver.php",array("pag"=>"asociar","back"=>"../stock/nuevo_rma.php"));?>';">
		<input type="button" name="asoc_cas" value="Asociar con C.A.S" onClick="window.location='<?= encode_link("../casos/caso_admin.php",array("pag"=>"asociar","backto"=>"../stock/nuevo_rma.php"));?>';">
		<input type=hidden name=nro_orden value="<?= $nro_orden?>">
		<input type=hidden name=nro_caso value="<?= $nro_caso?>">
		<br>
<?
		if ($nro_orden) echo "<span>Asociado a la Orden de Porduccion Nro: $nro_orden</span>\n";
		if ($nro_caso) echo "<span>Asociado a C.A.S. Nro: $nro_caso</span>\n";
?>
	</td>
</tr>
<tr id=ma_sf>
    <td width="30%">
		<b>Descripción del Producto</b>
		&nbsp;&nbsp;&nbsp;<input name="cambiar" type="button" value="Cambiar" title="Cambiar Producto" onclick="nuevo_item()">
    </td>
	<td>
		<input type="hidden" name="producto_id"><span id="desc_prod"></span>
	</td>
</tr>
<tr id=ma_sf>
	<td width="30%">
		<b>Cantidad</b>
    </td>
    <td>
		<input type="text" name="cantidad" value="1">
	</td>
</tr>
<tr id=ma_sf>
	<td>
		<b>Tipo Producto</b><br>(Seleccione el producto).
	</td>
	<td>
		<span id="tipo_prod"></span>
	</td>
</tr>
<tr id=ma_sf>
    <td width="30%">
		<b>Proveedor</b>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="cambiar_prov" type="button" value="Cambiar" title="Cambiar Proveedor" onclick="nuevo_item_2()">
	</td>
    <td width="70%">
		<span id="desc_prov"></span>
		<input name="id_prov" type="hidden">
	</td>
</tr>
<tr id=ma_sf>
    <td width="30%">
		<b>Precio</b>
	</td>
	<td>	
		<input type="text" name="precio" value="0.00">
	</td>
</tr>
<tr>
	<td colspan=2 align=center>
		<input type="submit" name="guardar" value="Guardar">
		<hr>
	</td>
</tr>
</table>
</form>