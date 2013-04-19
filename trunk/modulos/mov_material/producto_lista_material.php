<?
/*
Autor: Gabriel
MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.45 $
$Date: 2006/05/19 20:36:07 $
*/
require_once("../../config.php");
require_once("../stock/funciones.php");

$id=$parametros["ID"] or $id=$_POST["id"];
$id_entrega_estimada=$parametros["id_entrega_estimada"] or $id_entrega_estimada=$_POST["id_entrega_estimada"];
$id_subir=$parametros["id_subir"] or $id_subir=$_POST["id_subir"];

if (($_POST["guardar"]=="Guardar")&&($_POST["cant_i"]!=""))
{
	$lim=$_POST["cant_i"];
	for ($i=0; $i<=$lim; $i++){
		$id_renglon=$_POST["id_renglon_$i"];
		$cantidad=$_POST["cantidad_$i"];
		$id_producto=$_POST["id_p_$i"];
		$id_prod_esp=$_POST["id_pe_$i"];
		if (($id_prod_esp)&&(is_numeric($id_prod_esp))){
			$rta_consulta=sql("select * from mov_material.producto_lista_material where id_renglon=".$id_renglon." and id_producto=".$id_producto." and id_entrega_estimada=".$id_entrega_estimada, "c19") or fin_pagina();
			if ($rta_consulta->recordCount()==0){
				$rta_consulta=sql("insert into mov_material.producto_lista_material (id_producto, id_prod_esp, id_renglon, cantidad, id_entrega_estimada)values($id_producto, $id_prod_esp, $id_renglon, $cantidad, $id_entrega_estimada)", "c26") or fin_pagina();
			}else{
				$rta_consulta=sql("update mov_material.producto_lista_material set id_prod_esp=$id_prod_esp, cantidad=".(($cantidad)?$cantidad:0)." where id_producto=$id_producto and id_renglon=$id_renglon and id_entrega_estimada=$id_entrega_estimada", "c28") or fin_pagina();
			}
		}
	}
}

if ($_POST["comando"]){
	$comando=substr($_POST["comando"], 0, stripos($_POST["comando"], " "));
	$indice=substr($_POST["comando"], stripos($_POST["comando"], " ")+1);
	$lim=$_POST["cant_i"];

	if ($comando=="borrar"){
		for ($i=$_POST["desde_$indice"]; $i<=$_POST["hasta_$indice"]; $i++){
			$id_renglon=$_POST["id_renglon_$i"];
			$cantidad=$_POST["cantidad_$i"];
			$id_producto=$_POST["id_p_$i"];
			$id_prod_esp=$_POST["id_pe_$i"];

			if (stripos($_POST["id_pe_$i"], "borrar")==1)
				$rta_consulta=sql("delete from mov_material.producto_lista_material where id_renglon=".$id_renglon." and id_producto=".$id_producto." and id_entrega_estimada=$id_entrega_estimada and id_prod_esp=".substr($id_prod_esp, strpos($id_prod_esp, " ")+1), "c47") or fin_pagina();
		}
	}else if ($comando=="ocultar"){
		for ($i=$_POST["desde_$indice"]; $i<=$_POST["hasta_$indice"]; $i++){
			$id_p=$_POST["id_$i"];

			if ((stripos($_POST["id_pe_$i"], "ocultar")==1)&&($id_p))
				$rta_consulta=sql("update mov_material.producto_lista_material set visible=0 where id_producto_lista_material=".$id_p, "c54") or fin_pagina();
		}
	}else if ($comando=="ver"){
		for ($i=$_POST["desde_$indice"]; $i<=$_POST["hasta_$indice"]; $i++){
			$id_p=$_POST["id_$i"];

			if ($id_p){
				$rta_consulta=sql("select * from mov_material.producto_lista_material where id_producto_lista_material=$id_p and visible=0","c60") or fin_pagina();
				if ($rta_consulta->recordCount()==1)
					$rta_consulta=sql("update mov_material.producto_lista_material set visible=1 where id_producto_lista_material=".$id_p, "c61") or fin_pagina();
			}
		}
	}
}

$lim=$_POST["cant_i"];
for ($i=0; $i<=$lim; $i++){
	if (($_POST["elegido_$i"]!="")&&($_POST["id_$i"])){
		$consulta="update mov_material.producto_lista_material set elegido=".$_POST["elegido_$i"]." where id_producto_lista_material=".$_POST["id_$i"];
		sql($consulta, "c71") or fin_pagina();
	}
}

$q ="select licitacion.id_licitacion,vence_oc,entidad.nombre as nombre_entidad,valor_dolar_lic,nro_orden,nro
	     from licitacion
		 left join subido_lic_oc on subido_lic_oc.id_licitacion=licitacion.id_licitacion AND subido_lic_oc.id_entrega_estimada=$id_entrega_estimada
		 left join entidad using(id_entidad)
		 left join entrega_estimada using(id_entrega_estimada)
		 where subido_lic_oc.id_licitacion=$id";

$resultado_licitacion=sql($q,"<br>Error al traer los datos de la licitacion<br>") or fin_pagina();

echo $html_header;
?>
<script>
	var flagguardar=0;

	function check_oc(){
		if (flagguardar==1) return confirm('SE HAN DETECTADO CAMBIOS EN LA PAGINA.\n¿Continuar sin guardar los cambios?');
		else return true;
	}

	function oc(){
		flagguardar=1;
	}

	function reset_oc(){
		flagguardar=0;
		return true;
	}

	function ocultar_tabla(id_tabla){
		var obj=document.getElementById('tabla_renglon_'+id_tabla);
		var cnt=document.getElementById('tabla_contenedor_'+id_tabla);
		var hid=document.getElementById('tabla_'+id_tabla);

		if (typeof obj!='undefined'){
			if (obj.style.display=='none'){
				obj.style.display='inline';
				cnt.rows[0].cells[0].childNodes[0].src='<?=$html_root?>/imagenes/dropdown2.gif';
				hid.value='<?=$html_root?>/imagenes/dropdown2.gif';
			}else{
				obj.style.display='none';
				cnt.rows[0].cells[0].childNodes[0].src='<?=$html_root?>/imagenes/drop2.gif';
				hid.value='<?=$html_root?>/imagenes/drop2.gif';
			}
		}
	}

	function alternar_color(obj, color, fila) {
		color=color.toLowerCase();
		if (obj.style.backgroundColor == color){
			obj.style.backgroundColor = "";
			(document.getElementById('elegido_'+fila)).value=0;
		}else{
			obj.style.backgroundColor = color;
			(document.getElementById('elegido_'+fila)).value=1;
		}
	}

	function setear_filas(k, comando){
		var desde=(document.getElementById("desde_"+k)).value;
		var hasta=(document.getElementById("hasta_"+k)).value;

		for (i=desde; i<=hasta; i++){
			if ((document.getElementById("ch_borrar_"+i)).checked){
				obj=document.getElementById("id_pe_"+i);
				obj2=document.getElementById("pe_"+i);

				obj.value='-'+comando+' '+obj.value;
				obj2.value='';
			}
		}
	}
</script>
<form id="form1" method="POST" action="producto_lista_material.php">
<input type="hidden" name="id" value="<?=$id?>">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<input type="hidden" name="id_subir" value="<?=$id_subir?>">
<input type="hidden" name="id_lic_prop" value="<?=$id_lic_prop?>">
<input type="hidden" name="comando" value="">

<table width="100%"  border="0" bgcolor=<?=$bgcolor2?>>
  <tr>
    <td align="left" ><b> Licitación ID </b></td>
    <td align=left>
       <font color="Blue" size="2">
       <a href='<?=encode_link("../licitaciones/licitaciones_view.php",array('cmd1'=>'detalle',"ID"=>$id));?>'><?=$id?></a>
       </font>
    </td>
    <td align="left" ><b>Entidad:</b></td>
    <td align=left><font color="Blue" size="2"><? echo $resultado_licitacion->fields['nombre_entidad']; ?></font></td>

  </tr>
  <tr>
    <td align="left" ><font color="red" size="3"><b>Vencimiento OC:</b></font></td>
    <td align=left>
     <font color="red" size="3"><b><?=Fecha($resultado_licitacion->fields['vence_oc']); ?></b></font>
    </td>
    <td align="left" ><b>Seguimiento de Orden: </b></td>
    <td align=left>
    	<font color="Blue" size="2" ><a href="<?=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$id_subir,"solo_lectura"=>1));?>" target="_blank"><?=$resultado_licitacion->fields['nro_orden']?></font>
    </td>
  </tr>
  <tr>
		<td align='left' colspan='5'>
			<b>Fecha de entrega estimada de los productos:&nbsp;<? echo Fecha($resultado_licitacion->fields['entrega_estimada_producto']); ?>
		</td>
  </tr>
</table>
<center>
<?
if (permisos_check("inicio","seguimiento_boton_materiales")) {
	$link_materiales=encode_link("../ordprod/seguimiento_orden_materiales_pm.php",array("id_licitacion"=>$id,"mostrar_pedidos"=>1));
	?>
	<input type="button" name="boton_materiales" value="Materiales" onclick="window.open('<?=$link_materiales;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">&nbsp;&nbsp;&nbsp;
	<?
}
$id_renglon=$rta_consulta->fields["id_renglon"];
        $id_producto_lista_material=$rta_consulta2->fields["id_producto_lista_material"];

 $link=encode_link("detalle_movimiento.php",array("id_licitacion"=>$id,
                                                         "id_entrega_estimada"=>$id_entrega_estimada,
                                                         "pedido_material"=>1,
                                                         "pagina_viene"=>"producto_lista_material.php",
                                                         "deposito_origen"=>2));
?>
<input type=button name="pedidos material" value="Pedidos material" onclick="if (check_oc()) window.open('<?=$link?>');">
</center>
<table border="0" width="95%">
<?
	$consulta="/*LISTADO DE RENGLONES DE LA OC DE CLIENTE PARA ESTA LICITACION*/
		select renglones_oc.id_renglon, renglon.titulo, renglones_oc.cantidad, codigo_renglon, renglon.tipo
		from licitaciones.renglones_oc
			join licitaciones.subido_lic_oc using (id_subir)
			join licitaciones.renglon using (id_renglon)
			join licitaciones.historial_estados he on(he.id_renglon=renglones_oc.id_renglon and he.activo=1)
			join licitaciones.estado_renglon
				on (estado_renglon.id_estado_renglon=he.id_estado_renglon and estado_renglon.nombre ilike 'Orden de Compra')
		where no_participamos='f' and id_entrega_estimada=$id_entrega_estimada

	union

		select renglon.id_renglon, renglon.titulo, 1 as cantidad, codigo_renglon, renglon.tipo
		from licitaciones.licitacion_muestra
			join licitaciones.renglones_muestra using (id_licitacion_muestra)
			join licitaciones.renglon using (id_renglon)
		where id_entrega_estimada=$id_entrega_estimada";

	$rta_consulta=sql($consulta, "c186") or fin_pagina();
	$i=0;
	$k=0;

	while (!$rta_consulta->EOF){
		$consulta2="
				/*LISTADO DE PRODUCTOS PARA ESTE RENGLON (POR ID_ENTREGA_ESTIMADA)*/
				select distinct producto.id, producto.id_producto, producto.desc_gral, producto.cantidad, plm.id_prod_esp,
                           producto_especifico.descripcion,id_producto_lista_material,
                           (case when visible is null then 1 else visible end) as visible,
				(case when elegido is null then 0 else elegido end) as elegido,
                           tmp1.cantidad_oc
			    from licitaciones.producto
				left join mov_material.producto_lista_material plm on (plm.id_renglon=".$rta_consulta->fields["id_renglon"]." and producto.id_producto=plm.id_producto and id_entrega_estimada=$id_entrega_estimada)
				left join general.producto_especifico using (id_prod_esp)
				left join licitaciones.renglon on(renglon.id_renglon=producto.id_renglon)
				left join (
					select id_producto_orig, id_renglon, id_entrega_estimada, cantidad_oc, id_licitacion
					from licitaciones.producto_presupuesto_new ppn
						join licitaciones.renglon_presupuesto_new using (id_renglon_prop)
						join licitaciones.licitacion_presupuesto_new using (id_licitacion_prop)
						left join (
							select id_producto_presupuesto, sum(cantidad_oc) as cantidad_oc
							from compras.oc_pp
							group by id_producto_presupuesto
						)as oc_pp using (id_producto_presupuesto)
				)as tmp1 on (tmp1.id_producto_orig=producto.id_producto
						and renglon.id_licitacion=tmp1.id_licitacion
						and tmp1.id_renglon=producto.id_renglon
						and tmp1.id_entrega_estimada=$id_entrega_estimada)
where producto.id_renglon=".$rta_consulta->fields["id_renglon"]." order by desc_gral";
		$rta_consulta2=sql($consulta2, "c153") or fin_pagina();

        $id_renglon=$rta_consulta->fields["id_renglon"];
        $id_producto_lista_material=$rta_consulta2->fields["id_producto_lista_material"];
        $link=encode_link("../ordprod/ordenes_nueva.php",array("id_renglon"=>$id_renglon,
                                                               "id_licitacion"=>$id,
                                                               "id_producto_lista_material"=>$id_producto_lista_material,
                                                               "id_entrega_estimada"=>$id_entrega_estimada,
                                                               "modo"=>"asociado_lic",
                                                               "modo"=>"nuevo"
                                                               ));
?>
	<tr>
		<input type="hidden" name="tabla_<?=$k?>" id="tabla_<?=$k?>" value="<?=(($_POST["tabla_$k"])?$_POST["tabla_$k"]:$html_root."/imagenes/drop2.gif")?>">
		<table width="100%" border="1" bordercolor="black" bgcolor="<?=$bgcolor2?>" id="tabla_contenedor_<?=$k?>">
			<tr>
				<td id="mo" onclick="ocultar_tabla(<?=$k?>);" width="5%" style="cursor:hand">
					<img src="<?=(($_POST["tabla_$k"])?$_POST["tabla_$k"]:$html_root."/imagenes/drop2.gif")?>">
				</td>
				<td id="mo" width="20%" nowrap>Renglón: <?=$rta_consulta->fields["codigo_renglon"]?></td>
				<td><?=$rta_consulta->fields["titulo"]?></td>
                <td align=center width="5%">
                   <input type="button" name="op_<?=$k?>" value="OP" onclick="window.open('<?=$link?>')">
                </td>

				<td id="mo" width="10%" nowrap>Cantidad: <?=$rta_consulta->fields["cantidad"]?></td>
			</tr>
			<tr>
				<td colspan="5">
<input type="hidden" name="desde_<?=$k?>" id="desde_<?=$k?>" value="<?=$i?>">
					<table width="100%" border="0" cellpadding="0" cellspacing="0" id="tabla_renglon_<?=$k?>" style="display:<?=((stripos($_POST["tabla_$k"], "dropdown2.gif")>0)?"inline":"none")?>">
						<tr id="mo">
							<td width="5%">Cant.</td>
							<td>Producto</td>
							<td width="3%">OC</td>
							<td width="3%">PM</td>
							<td width="3%" onclick="window.open('<?=encode_link("../stock/stock_buenos_aires.php", array("id_deposito"=>2))?>');" style="cursor:'hand'">S</td>
							<td>Producto específico</td>
							<td>&nbsp;</td>
						</tr>
<?
	$j=0;
	while (!$rta_consulta2->EOF) {
		if ($rta_consulta2->fields["visible"]==1){
			if ($rta_consulta2->fields["id_prod_esp"]){
				$consulta_tmp="/*CONSULTA PARA SABER SI HAY ALGÚN PM NO ANULADO PARA ESTE PRODUCTO*/
					select id_prod_esp, es_pedido_material,
						case when cantidad_pm is null then 0 else cantidad_pm end as cantidad_pm
					from mov_material.detalle_movimiento
						left join mov_material.movimiento_material using (id_movimiento_material)
						left join (
							select id_prod_esp, sum(detalle_movimiento.cantidad) as cantidad_pm
							from mov_material.movimiento_material
								join mov_material.detalle_movimiento using(id_movimiento_material)
							where es_pedido_material=1 and estado<>3
								and (id_licitacion=$id)
							group by id_prod_esp, movimiento_material.id_licitacion
					)as tmp0 using (id_prod_esp)
					where estado<>3 and es_pedido_material=1 and id_licitacion=$id and id_prod_esp=".$rta_consulta2->fields["id_prod_esp"]."
					group by id_prod_esp, es_pedido_material, cantidad_pm";
				$rta_consulta_tmp=sql($consulta_tmp, "c268") or fin_pagina();
			}

			if(($rta_consulta2->fields["id_prod_esp"])&&($rta_consulta_tmp)){
				if (!$tiene_pm[$rta_consulta2->fields["id_prod_esp"]]){
					$tiene_pm[$rta_consulta2->fields["id_prod_esp"]]=$rta_consulta_tmp->fields["cantidad_pm"];
				}
				//si la cantidad que hay en PM es mayor o igual a la cantidad
				if (($tiene_pm[$rta_consulta2->fields["id_prod_esp"]])&&($tiene_pm[$rta_consulta2->fields["id_prod_esp"]]>=$rta_consulta2->fields["cantidad"]*$rta_consulta->fields["cantidad"])){
					$pm_color="bgcolor='#efeeae' title='Se realizaron PM para este producto específico, por la cantidad indicada en el renglón'";//amarillo
					$tiene_pm[$rta_consulta2->fields["id_prod_esp"]]-=$rta_consulta2->fields["cantidad"]*$rta_consulta->fields["cantidad"];
				}else if ($tiene_pm[$rta_consulta2->fields["id_prod_esp"]]) {
					$pm_color="bgcolor='#fda173' title='Se realizaron PM para este producto específico, pero la cantidad es menor a la indicada en el renglón'";//naranja
				}
			}

			$rta_consulta_tmp="";
			$oc_color=((($rta_consulta2->fields["cantidad_oc"]>=$rta_consulta2->fields["cantidad"]))?"bgcolor='#efeeae' title='Existen Ordenes de Compra para el producto general'":"");

			$s_color="";
			if($rta_consulta2->fields["id_prod_esp"]){
				if (!$en_stock_coradir[$rta_consulta2->fields["id_prod_esp"]]){
					$en_stock_coradir[$rta_consulta2->fields["id_prod_esp"]]=en_stock_coradir($rta_consulta2->fields["id_prod_esp"]);
				}
				if ($en_stock_coradir[$rta_consulta2->fields["id_prod_esp"]]>=$rta_consulta2->fields["cantidad"]*$rta_consulta->fields["cantidad"]){
					$s_color="bgcolor='#6d78ee' title='Hay Stock disponible suficiente para cubrir la cantidad indicada en este renglón para este producto específico'";
					$en_stock_coradir[$rta_consulta2->fields["id_prod_esp"]]-=$rta_consulta2->fields["cantidad"]*$rta_consulta->fields["cantidad"];
				}
				elseif ($en_stock_coradir[$rta_consulta2->fields["id_prod_esp"]]>0)
				{
					$s_color="bgcolor='#fda173' title='Hay Stock disponible pero no cubre la cantidad indicada en este renglón para este producto específico'";//naranja
					$en_stock_coradir[$rta_consulta2->fields["id_prod_esp"]]-=$rta_consulta2->fields["cantidad"]*$rta_consulta->fields["cantidad"];
				}
			}//de if($rta_consulta2->fields["id_prod_esp"])
?>
						<tr bgcolor="<?=($j%2)?$bgcolor2:$bgcolor3;?>">
							<td width="5%"><?=$rta_consulta2->fields["cantidad"]?></td>
							<td width="35%">
								<?=$rta_consulta2->fields["desc_gral"]?>
							</td>
							<td align="center"><table cellpadding="0" cellspacing="0" border="1"><tr><td width="20" height="20" <?=$oc_color?>><font <?=$oc_color?>>&nbsp;</font></td></tr></table></td>
							<td align="center"><table cellpadding="0" cellspacing="0" border="1"><tr><td width="20" height="20" <?=$pm_color?>><font <?=$pm_color?>>&nbsp;</font></td></tr></table></td>
							<td align="center"><table cellpadding="0" cellspacing="0" border="1"><tr><td width="20" height="20" <?=$s_color?>><font <?=$s_color?>>&nbsp;</font></td></tr></table></td>
							<td>
								<input type="text" class="text_4" name="pe_<?=$i?>" id="pe_<?=$i?>" value="<?=(($rta_consulta2->fields["descripcion"])?$rta_consulta2->fields["descripcion"]:$_POST["pe_$i"])?>" style="width:'510'" readonly onchange="oc();">
							</td>
							<td align="center" nowrap width="5%">
								<input type="button" name="elegir_<?=$i?>" value="E" onclick="alternar_color(this.parentNode.parentNode.cells[5],'#a8dcb0', <?=$i?>); oc();">

								<input type="checkbox" name="ch_borrar_<?=$i?>" id="ch_borrar_<?=$i?>">
								<input type="hidden" name="id_<?=$i?>" id="id_<?=$i?>" value="<?=(($rta_consulta2->fields["id_producto_lista_material"])?$rta_consulta2->fields["id_producto_lista_material"]:$_POST["id_$i"])?>">
								<input type="hidden" name="id_pe_<?=$i?>" id="id_pe_<?=$i?>" value="<?=(($rta_consulta2->fields["id_prod_esp"])?$rta_consulta2->fields["id_prod_esp"]:$_POST["id_pe_$i"])?>">
								<input type="hidden" name="id_p_<?=$i?>" id="id_p_<?=$i?>" value="<?=$rta_consulta2->fields["id_producto"]?>">
								<input type="hidden" name="cantidad_<?=$i?>" id="cantidad_<?=$i?>" value="<?=$rta_consulta2->fields["cantidad"]?>">
								<input type="hidden" name="id_renglon_<?=$i?>" id="id_renglon_<?=$i?>" value="<?=$rta_consulta->fields["id_renglon"]?>">
								<input type="hidden" name="elegido_<?=$i?>" id="elegido_<?=$i?>" value="<?=$rta_consulta->fields["elegido"]?>">
								<input type="button" name="asignar_<?=$i?>" value="A"
									onclick="window.open('<?=encode_link("listado_productos_especificos_g.php",
										array("pagina_viene"=>"../mov_material/producto_lista_material.php", "id"=>$id, "id_p"=>$rta_consulta2->fields["id_producto"],
										"onclick_cargar"=>"window.opener.document.all.id_pe_$i.value=document.all.id_producto_seleccionado.value; window.opener.document.all.pe_$i.value=document.all.nombre_producto_elegido.value; window.close();"
										)
									)?>','','left=40,top=80,width=800,height=600,resizable=1,status=1,scrollbars=1')">
							</td>
						</tr>
<?
			if ($rta_consulta2->fields["elegido"]=="1") {
		?>
				<script>alternar_color(document.all.elegir_<?=$i?>.parentNode.parentNode.cells[5],'#a8dcb0', <?=$i?>)</script>
		<?
			}
			$j++;
		}else{
			?>
				<input type="hidden" name="id_<?=$i?>" id="id_<?=$i?>" value="<?=(($rta_consulta2->fields["id_producto_lista_material"])?$rta_consulta2->fields["id_producto_lista_material"]:$_POST["id_$i"])?>">
			<?
		}
		$i++;
		$pm_color="";
		$s_color="";
		$oc_color="";
		$rta_consulta2->moveNext();
	}
	$i--;
?>
<input type="hidden" name="hasta_<?=$k?>" id="hasta_<?=$k?>" value="<?=$i?>">
						<tr>
							<td colspan="7" align="center" id="mo">
								<input type="submit" name="borrar_<?=$k?>" id="borrar_<?=$k?>" value="Borrar productos esp." onclick="setear_filas(<?=$k?>, 'borrar'); document.all.comando.value='borrar <?=$k?>'; reset_oc();">
								<input type="submit" name="ocultar_<?=$k?>" id="ocultar_<?=$k?>" value="Ocultar filas" onclick="setear_filas(<?=$k?>, 'ocultar'); document.all.comando.value='ocultar <?=$k?>'; reset_oc();">
								<input type="submit" name="ver_<?=$k?>" id="ver_<?=$k?>" value="Ver Ocultos" onclick="document.all.comando.value='ver <?=$k?>'; reset_oc();">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</tr>
<?
		$i++;
		$k++;
		$rta_consulta->moveNext();
	}
?>
</table>
<input type="hidden" name="cant_i" id="cant_i" value="<?=$i-1?>">
<input type="hidden" name="cant_k" id="cant_k" value="<?=$k?>">
<br>
<center>
<input type="submit" value="Guardar" name="guardar" onclick="return reset_oc();">&nbsp;
<input type="button" value="Cerrar" onclick="if (check_oc()) window.close();">
</center>
</form>
<?
fin_pagina();
?>