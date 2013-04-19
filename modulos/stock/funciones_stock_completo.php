<?
/*
Autor: MAC
Fecha: 12/06/06

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2006/07/18 15:24:48 $
*/


/**************************************************************************************
Esta función se utiliza en las pantallas llamadas por stock_coradir.php
Genera una tabla con los depositos considerados en las consultas de las paginas

@dep_en_cuenta		El Resulset con los depositos que debe generar
@first				Si este parametro es distinto de vacio, todos los checks que se
					generan estaran checkeados
***************************************************************************************/
function depositos_considerados($dep_en_cuenta,$first="")
{
	global $img_ext,$img_cont,$parametros;

	?>
	<table width="100%" class="bordes">
    <tr id=mo>
     <td width="1%">
      <img id="imagen_depositos" src="<?=$img_ext?>" border=0 align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.despositos_considerados,document.all.imagen_depositos,document.all.dep_desplegados);">
     </td>
     <td>
	   <b>Depósitos Considerados</b>
     </td>
    </tr>
    <tr>
     <td colspan="2">
	   <table width="100%" id="despositos_considerados" style="display:block">
	     <?
	     if($first=="")
	       $check_no_buscar="checked";

	     $count_celda=0;$index=0;
	     $dep_en_cuenta->Move(0);
	     while (!$dep_en_cuenta->EOF)
	     {
	     	if($count_celda==0)
	     	{
	       	?>
	       	<tr>
	       	<?
	     	}

	     	if($_POST["check_stocks_".$dep_en_cuenta->fields["id_deposito"]])
	     	$checked="checked";
	     	else
	     	$checked="";
	       ?>
	       <td>
	        <input type="checkbox" name="check_stocks_<?=$dep_en_cuenta->fields["id_deposito"]?>" value="<?=$dep_en_cuenta->fields["id_deposito"]?>" <?=$checked?> <?=$check_no_buscar?>>&nbsp;
	        <b><?=$dep_en_cuenta->fields["nombre"]?></b>
	       </td>
	       <?

	       if($count_celda==2)
	       {
	       	?>
	       	</tr>
	       	<?
	       	$count_celda=-1;
	       }

	       $count_celda++;
	       $index++;
	       $dep_en_cuenta->MoveNext();
	     }//de while(!$dep_en_cuenta->EOF)
	     ?>
	   </table>
	  </td>
	 </tr>
	</table>
	<?
}//de function depositos_considerados($dep_en_cuenta)


/**************************************************************************************
Esta función se utiliza en las pantallas llamadas por stock_coradir.php
Genera un string sql para agregar en el where de una consulta, con los depositos
seleccionados mediante los check generados por la funcion depositos_considerados()
en este mismo archivo

@dep_en_cuenta		El Resulset con los depositos que debe generar
@first				Si este parametro es distinto de vacio, todos los checks que se
					generan estaran checkeados

@return				El string que formará parte del where de una la consulta sql
***************************************************************************************/
function agregar_dep_consulta($dep_en_cuenta,$first)
{
	$string_return="";
	$dep_en_cuenta->Move(0);
	$cant_en_cuenta=0;
	while (!$dep_en_cuenta->EOF)
	{
		if($_POST["check_stocks_".$dep_en_cuenta->fields["id_deposito"]] || ($_POST["check_stocks_".$dep_en_cuenta->fields["id_deposito"]]=="" && $first==""))
		{
			if($string_return!="")
				$string_return.=" or ";
			$string_return.=" id_deposito=".$dep_en_cuenta->fields["id_deposito"];
			$cant_en_cuenta++;
		}

		$dep_en_cuenta->MoveNext();
	}//de while (!$dep_en_cuenta->EOF)

	//si no se tomo ningun deposito en cuenta...no se trae nada
	if($cant_en_cuenta==0)
	 $string_return=" id_deposito=-1";

	return $string_return;
}//de function agregar_dep_consulta($dep_en_cuenta)


/**************************************************************************************
Esta función se utiliza en las pantallas llamadas por stock_coradir.php
Genera las tablas que muestran las reservas actuales para el producto dentro de
los Stocks de Coradir. Muestra las reservas para todos los depositos del Stock

@id_prod_esp		El id del producto especifico del cual se desea mostrar sus reservas
@filtro				String con condiciones adicionales que se le agregaran al where de
					la consulta que trae los datos de las reservas (por ejemplo se puede
					usar el filtro para indicar que solo traiga un deposito determinado)
@liberar			Si este parametro esta en 1, se muestra el boton liberar, para liberar
					las reservas. El funcionamiento de este boton se debe implementar
					en la pagina que llama a esta funcion. Por default, el boton no
					se muestra.
***************************************************************************************/
function mostrar_reservas_stock($id_prod_esp,$filtro,$liberar="")
{
	global $bgcolor_out;

	if($filtro)
	 $add_filtro="and ($filtro) ";

	$sql ="select fila.nro_orden,licitacion.id_licitacion,detalle_movimiento.id_movimiento_material,en_stock.id_deposito,depositos.nombre as nbre_deposito,
      id_log_mov_stock,nrocaso,detalle_reserva.cantidad_reservada,detalle_reserva.fecha_reserva,
      detalle_reserva.id_fila,detalle_reserva.id_detalle_movimiento,detalle_reserva.id_log_mov_stock,
      detalle_reserva.usuario_reserva,tipo_reserva.nombre_tipo,id_tipo_reserva,estado.color,coment_manual
        from stock.en_stock
        join (select id_detalle_reserva,detalle_reserva.id_en_stock,detalle_reserva.id_licitacion,id_tipo_reserva,nrocaso,cantidad_reservada,fecha_reserva,
                     usuario_reserva,id_fila,id_detalle_movimiento,id_log_mov_stock,log_movimientos_stock.comentario as coment_manual
        		from stock.detalle_reserva left join stock.log_movimientos_stock using(id_log_mov_stock)
        	  ) as detalle_reserva using (id_en_stock)
        left join stock.tipo_reserva using(id_tipo_reserva)
        left join general.depositos using(id_deposito)
        left join compras.fila using(id_fila)
		left join mov_material.detalle_movimiento using(id_detalle_movimiento)
		left join licitaciones.licitacion using(id_licitacion)
        left join licitaciones.estado using(id_estado)
        where depositos.tipo=0 and en_stock.id_prod_esp=$id_prod_esp $add_filtro
        order by detalle_reserva.fecha_reserva DESC
        ";
	$reservas=sql($sql,"<br>Error al traer los datos del producto en el stock<br>") or fin_pagina();

	if($reservas->RecordCount()>0)
	{
	 ?>
					<script>
						var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
						var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
						function muestra_tabla(obj_tabla)
						{
							oimg=eval("document.all.imagen_1");//objeto tipo IMG
							if (obj_tabla.style.display=='none')
							{
								obj_tabla.style.display='block';
								oimg.show=0;
								oimg.src=img_ext;
							}
							else
							{
								obj_tabla.style.display='none';
								oimg.show=1;
								oimg.src=img_cont;
							}
						}//de function muestra_tabla(obj_tabla,nro)

						function control_liberar(index_liberar)
						{
							var aux_cant,cant_reserva;
						    var msg_error;
						    cant_reserva=parseInt(eval("document.all.cant_liberar_"+index_liberar+".value"));

							aux_cant=parseInt(prompt('Ingrese la cantidad a liberar',cant_reserva));
							if(aux_cant==null)
							   return false;

							while(aux_cant<=0 || aux_cant>cant_reserva)
							{
								if(aux_cant<=0)
								 msg_error="LA CANTIDAD INGRESADA ES CERO";
								else
								 msg_error="LA CANTIDAD INGRESADA ES MAYOR A LA RESERVADA EN ESTE CASO";

								aux_cant=prompt(msg_error+'\nIngrese nuevamente la cantidad a liberar',cant_reserva);
								if(aux_cant==null)
							 	   return false;

							}

						    document.all.index_liberar.value=index_liberar;
						    document.all.cantidad_liberar.value=aux_cant;
							return true;

						}//de function control_liberar(index_liberar)
					</script>
	                <br>
	                <table align="center" width="95%" border="1" cellspacing="0" cellpadding="0">
	                  <tr id="mo">
	                    <td width="1%">
						  <img id="imagen_1" src="<?=$img_cont?>" border=0 align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.tabla_reservas);">
						</td>
	                    <td align="center">
	                      PRODUCTOS RESERVADOS
	                    </td>
	                  </tr>
	                  <tr>
	                   <td colspan="2">
	                    <table width="100%"  id="tabla_reservas" style="display:none">
	                     <tr>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Cant.</b>
		                    </td>
		                	<td align='center'   bgcolor="#e8e8e8">
		                		<b>ID Lic.</b>
		                	</td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Nº Orden</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>ID M./P. Material</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Tipo</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Depósito</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Fecha Reserva</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Usuario</b>
		                    </td>
		                   </tr>
		                 <?

		                 $index_entregar=0;
		                 while(!$reservas->EOF)
		                 {?>
		                  <tr bgcolor="<?=$bgcolor_out?>" title="<?=$reservas->fields['coment_manual']?>">
		                   <td>
		                     <b><?=$reservas->fields['cantidad_reservada']?></b>
		                    </td>
		                  <?
		                    $color=contraste( $reservas->fields["color"],"#000000","#ffffff");

		                	echo "<td bgcolor='".$reservas->fields["color"]."'>\n";
		                	if ($reservas->fields["id_licitacion"]) {
		                		$ref_lic=encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$reservas->fields["id_licitacion"]));
		                		echo "<a href='$ref_lic' target='_new' style='color:$color'><b>".$reservas->fields["id_licitacion"]."</b></a>\n";
		                	}
		                	else
		                		echo "&nbsp";
		                	echo "</td>\n";
		                ?>
		                    <td align="center">
		                     <input type="hidden" name="nro_orden_<?=$index_entregar?>" value="<?=$reservas->fields['nro_orden']?>">
		                     <input type="hidden" name="id_fila_<?=$index_entregar?>" value="<?=$reservas->fields['id_fila']?>">
		                     <input type="hidden" name="id_proveedor_<?=$index_entregar?>" value="<?=$reservas->fields['id_proveedor']?>">
		                     <input type="hidden" name="cant_liberar_<?=$index_entregar?>" value="<?=$reservas->fields['cantidad_reservada']?>">
		                     <input type="hidden" name="tipo_reserva_<?=$index_entregar?>" value="<?=$reservas->fields['id_tipo_reserva']?>">
		                     <input type="hidden" name="id_reservado_<?=$index_entregar?>" value="<?=$reservas->fields['id_reservado']?>">
		                     <input type="hidden" name="id_licitacion_<?=$index_entregar?>" value="<?=$reservas->fields['id_licitacion']?>">
		                     <input type="hidden" name="id_mov_material_<?=$index_entregar?>" value="<?=$reservas->fields['id_movimiento_material']?>">
		                     <input type="hidden" name="id_detalle_movimiento_<?=$index_entregar?>" value="<?=$reservas->fields['id_detalle_movimiento']?>">
		                     <input type="hidden" name="id_log_mov_stock_<?=$index_entregar?>" value="<?=$reservas->fields['id_log_mov_stock']?>">
		                     <input type="hidden" name="nrocaso_<?=$index_entregar?>" value="<?=$reservas->fields['nrocaso']?>">

		                     <?

		                	 if ($reservas->fields['nro_orden'])
		                	 {
		                		 $ref_ord_comp=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$reservas->fields['nro_orden']));
		                		 echo "<a href='$ref_ord_comp' target='_new'><b>".$reservas->fields['nro_orden']."</b></a>\n";
		                	 }
		                	 else
		                		 echo "&nbsp;";
		                	 ?>
		                    </td>
		                    <td align="center">
		                     <?$link = encode_link("../mov_material/detalle_movimiento.php",array("pagina"=>"listado","id"=>$reservas->fields["id_movimiento_material"]));
		                     if ($reservas->fields['id_movimiento_material']){
		                     	echo "<a href='$link' target='_new'><b>".$reservas->fields['id_movimiento_material']."</b></a>\n";
		                     }
		                     else {
		                     	echo "&nbsp;";
		                     }
		                     ?>
		                    </td>
		                    <td>
		                       <b><?=$reservas->fields['nombre_tipo']?></b>
		                    </td>
		                    <td>
		                     <b><?=$reservas->fields["nbre_deposito"]?></b>
		                    </td>
		                    <td title="Hora: <?=hora($reservas->fields['fecha_reserva'])?>">
		                     <b><?=fecha($reservas->fields['fecha_reserva'])?></b>
		                    </td>
		                    <td>
		                     <b><?=$reservas->fields['usuario_reserva']?></b>
		                     <?
		                     if($liberar && $reservas->fields['nro_orden'] && permisos_check("inicio","permiso_boton_liberar_reserva"))
		                	 {
		                		 ?>
		                		   <input type="submit" name="liberar_reserva" value="Liberar" onclick="return control_liberar(<?=$index_entregar?>)" class="little_boton">
		                		 <?
		                	 }//de if(permisos_check("inicio","permiso_boton_entregar_desde_stock"))
		                	?>
		                    </td>
		                  </tr>
		                <?
		                  $index_entregar++;
		                  $reservas->MoveNext();
		                 }//de while(!$reservas->EOF)
		                ?>
		              </table>
	                 </td>
	                </tr>
	               </table>
	                <?

	}//de if($reservas->RecordCount()>0)

}//de function mostrar_reservas_stock($id_prod_esp,$filtro)


/**************************************************************************************
Esta función se utiliza en las pantallas llamadas por stock_coradir.php
Genera las tablas que muestran los productos a confirmar actuales para el producto
dentro de los Stocks de Coradir, o del deposito pasado como parametro.
Muestra las reservas para todos los depositos del Stock

@id_prod_esp		El id del producto especifico del cual se desea mostrar sus reservas
@filtro				String con condiciones adicionales que se le agregaran al where de
					la consulta que trae los datos de los productos a confirmar
					(por ejemplo se puede usar el filtro para indicar que solo traiga
					 un deposito determinado)
***************************************************************************************/
function mostrar_a_confirmar_stock($id_prod_esp,$filtro)
{
	global $bgcolor_out;

	if($filtro)
	 $add_filtro="and ($filtro) ";

	$sql="select detalle_a_confirmar.id_fila,detalle_a_confirmar.id_detalle_a_confirmar,
		       detalle_a_confirmar.cant_a_confirmar,detalle_a_confirmar.id_licitacion,fila.nro_orden,
		       tipo_detalle_a_confirmar.nombre_tipo_detalle,depositos.nombre as nbre_deposito,
		       detalle_a_confirmar.fecha_a_confirmar,detalle_a_confirmar.usuario_a_confirmar,estado.color
		from stock.detalle_a_confirmar
		join stock.tipo_detalle_a_confirmar using(id_tipo_detalle_a_confirmar)
		join compras.fila using(id_fila)
		join stock.en_stock using(id_en_stock)
		join general.depositos using(id_deposito)
		left join licitaciones.licitacion using(id_licitacion)
        left join licitaciones.estado using(id_estado)
        where depositos.tipo=0 and en_stock.id_prod_esp=$id_prod_esp $add_filtro
        order by detalle_a_confirmar.fecha_a_confirmar DESC
        ";
	$prods=sql($sql,"<br>Error al traer los datos del producto a confirmar en el stock<br>") or fin_pagina();

	if($prods->RecordCount()>0)
	{
	 ?>
					<script>
						var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
						var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
						function muestra_tabla(obj_tabla)
						{
							oimg=eval("document.all.imagen_1");//objeto tipo IMG
							if (obj_tabla.style.display=='none')
							{
								obj_tabla.style.display='block';
								oimg.show=0;
								oimg.src=img_ext;
							}
							else
							{
								obj_tabla.style.display='none';
								oimg.show=1;
								oimg.src=img_cont;
							}
						}//de function muestra_tabla(obj_tabla,nro)
					</script>
	                <br>
	                <table align="center" width="95%" border="1" cellspacing="0" cellpadding="0">
	                  <tr id="mo">
	                    <td width="1%">
						  <img id="imagen_1" src="<?=$img_cont?>" border=0 align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.tabla_a_confirmar);">
						</td>
	                    <td align="center">
	                      PRODUCTOS A CONFIRMAR
	                    </td>
	                  </tr>
	                  <tr>
	                   <td colspan="2">
	                    <table width="100%"  id="tabla_a_confirmar" style="display:none">
	                     <tr>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Cant.</b>
		                    </td>
		                	<td align='center'   bgcolor="#e8e8e8">
		                		<b>ID Lic.</b>
		                	</td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Nº Orden</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Tipo</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Depósito</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Fecha Ingreso</b>
		                    </td>
		                    <td align="center"   bgcolor="#e8e8e8">
		                     <b>Usuario</b>
		                    </td>
		                   </tr>
		                 <?

		                 $index_entregar=0;
		                 while(!$prods->EOF)
		                 {?>
		                  <tr bgcolor="<?=$bgcolor_out?>">
		                   <td>
		                     <b><?=$prods->fields['cant_a_confirmar']?></b>
		                    </td>
		                  <?
		                    $color=contraste( $prods->fields["color"],"#000000","#ffffff");

		                	echo "<td bgcolor='".$prods->fields["color"]."'>\n";
		                	if ($prods->fields["id_licitacion"])
		                	{
		                		$ref_lic=encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$prods->fields["id_licitacion"]));
		                		echo "<a href='$ref_lic' target='_new' style='color:$color'><b>".$prods->fields["id_licitacion"]."</b></a>\n";
		                	}
		                	else
		                		echo "&nbsp";
		                	echo "</td>\n";
		                ?>
		                    <td align="center">
		                     <input type="hidden" name="nro_orden_<?=$index_entregar?>" value="<?=$prods->fields['nro_orden']?>">
		                     <input type="hidden" name="id_fila_<?=$index_entregar?>" value="<?=$prods->fields['id_fila']?>">
		                     <input type="hidden" name="id_licitacion_<?=$index_entregar?>" value="<?=$prods->fields['id_licitacion']?>">
		                     <input type="hidden" name="nrocaso_<?=$index_entregar?>" value="<?=$prods->fields['nrocaso']?>">

		                     <?

		                	 if ($prods->fields['nro_orden'])
		                	 {
		                		 $ref_ord_comp=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$prods->fields['nro_orden']));
		                		 echo "<a href='$ref_ord_comp' target='_new'><b>".$prods->fields['nro_orden']."</b></a>\n";
		                	 }
		                	 else
		                		 echo "&nbsp;";
		                	 ?>
		                    </td>
		                    <td>
		                       <b><?=$prods->fields['nombre_tipo_detalle']?></b>
		                    </td>
		                    <td>
		                     <b><?=$prods->fields["nbre_deposito"]?></b>
		                    </td>
		                    <td title="Hora: <?=hora($prods->fields['fecha_a_confirmar'])?>">
		                     <b><?=fecha($prods->fields['fecha_a_confirmar'])?></b>
		                    </td>
		                    <td>
		                     <b><?=$prods->fields['usuario_a_confirmar']?></b>
		                    </td>
		                  </tr>
		                <?
		                  $index_entregar++;
		                  $prods->MoveNext();
		                 }//de while(!$prods->EOF)
		                ?>
		              </table>
	                 </td>
	                </tr>
	               </table>
	                <?

	}//de if($prods->RecordCount()>0)

}//de function mostrar_a_confirmar_stock($id_prod_esp,$filtro)


/**************************************************************************************
Esta función se utiliza en las pantallas llamadas por stock_coradir.php
Genera las tablas que muestran los movimientos para el producto dentro de
los Stocks de Coradir. Muestra los ingresos y egresos para todos los depositos del Stock

@id_prod_esp		El id del producto especifico del cual se desea mostrar
					sus movimientos
@filtro				String con condiciones adicionales que se le agregaran al where de
					la consulta que trae los datos de las reservas
***************************************************************************************/
function mostrar_ing_egr_global($id_prod_esp,$filtro)
{
	global $atrib_tr;

	if($filtro)
	 $add_filtro="and ($filtro) ";

	$sql_mov="select log_movimientos_stock.usuario_mov, log_movimientos_stock.comentario, log_movimientos_stock.fecha_mov,
				log_movimientos_stock.cantidad, tipo_movimiento.id_tipo_movimiento,tipo_movimiento.descripcion as tipo_mov,
				tipo_movimiento.clase_mov,depositos.nombre as deposito
		      from stock.log_movimientos_stock
			  join stock.en_stock using (id_en_stock)
			  join general.depositos using(id_deposito)
			  join stock.tipo_movimiento using(id_tipo_movimiento)
		      where id_prod_esp=$id_prod_esp and (clase_mov=1 or clase_mov=2) and depositos.tipo>=0 $add_filtro
		      order by fecha_mov, comentario ASC";
	//clase_mov 1 es ingreso, 2 egreso

	$entradas_salidas=sql($sql_mov) or fin_pagina();

	//mostramos los ingresos y egresos
	echo "<hr>\n";
	?>
	<table align="center" width="85%" class="bordes">
	 <tr id=ma_sf>
	  <td align=center>
	<?
	echo "<img src='../../imagenes/mas.gif' border=0 style='cursor: hand;'
	     onClick='if (this.src.indexOf(\"mas.gif\")!=-1) {
		this.src=\"../../imagenes/menos.gif\";
		div_ing_eg.style.overflow=\"visible\";
		} else {
		this.src=\"../../imagenes/mas.gif\";
		div_ing_eg.style.overflow=\"hidden\";
		}'>\n";
	echo "&nbsp;<b><font size='2'>Movimientos de Stock</font></b>\n";?>
	  </td>
	 </tr>
	</table>
	<?
	echo "<div id='div_ing_eg' style='border-width: 0;overflow: hidden;height: 1'>\n";
	?>
	<table align="center" width="85%" class="bordes">
	  <tr id=mo>
	    <td align=center> <b> Ingresos</b>  </td>
	  </tr>
	<tr>
	  <td >
	  <table width=100% align=Center border=1 cellspacing="0" cellpadding="1" bordercolor=#ACACAC class="bordes">
	    <tr id=ma_sf>
	       <td width=10% align=Center><b>Cant.</b></td>
	       <td width=20% align=Center><b>Tipo de Mov.</b></td>
	       <td width=40% align=Center><b>Comentarios</b></td>
	       <td width=10% align=Center><b>Depósito</b></td>
	       <td width=10% align=Center><b>Fecha</b></td>
	       <td width=10% align=Center><b>Usuario</b></td>
	    </tr>
	<?
	$cantidad=$entradas_salidas->recordcount();
	$total_ingresos=0;

	for($i=0;$i<$cantidad;$i++)
	{
		//Saco el log de los  ingresos
		if($entradas_salidas->fields["clase_mov"]==1)
		{
			$total_ingresos+=$entradas_salidas->fields["cantidad"];
			?>
		    <tr <?=$atrib_tr?>>
		      <td align=right><?=$entradas_salidas->fields["cantidad"];?></td>
		      <td align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
		      <td align=left><?if ($entradas_salidas->fields["comentario"]) echo $entradas_salidas->fields["comentario"]; else echo "&nbsp;";?> </td>
		      <td align=left><?=$entradas_salidas->fields["deposito"]?></td>
		      <td align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
		      <td align=center><?=fecha($entradas_salidas->fields["fecha_mov"])." ".Hora($entradas_salidas->fields["fecha_mov"])?></td>

		    </tr>
			<?
		}//de if($entradas_salidas->fields["clase_mov"]==1)
		$entradas_salidas->movenext();
	}//de for($i=0;$i<$cantidad;$i++)
	?>
	 </table>
	 </td>
	</tr>
	<tr>
	  <td>&nbsp;</td>
	</tr>
	<?$entradas_salidas->move(0);?>
	<tr id=mo><td align=center><b> Egresos</b></td></tr>
	<tr>
	  <td >
	  <table width=100% align=Center border=1 cellspacing="0" cellpadding="1" bordercolor=#ACACAC class="bordes">
	    <tr id=ma_sf>
	       <td width=10% align=Center><b>Cant.</b></td>
	       <td width=20% align=Center><b>Tipo de Mov.</b></td>
	       <td width=40% align=Center><b>Comentarios</b></td>
	       <td width=10% align=Center><b>Depósito</b></td>
	       <td width=10% align=Center><b>Fecha</b></td>
	       <td width=10% align=Center><b>Usuario</b></td>
	    </tr>
	<?
	$cantidad=$entradas_salidas->recordCount();
	$total_egresos=0;
	for($i=0;$i<$cantidad;$i++)
	{
		//Obtengo el log de los egresos
		if ($entradas_salidas->fields["clase_mov"]==2)
		{
			$total_egresos+=$entradas_salidas->fields["cantidad"];
		?>
		    <tr <?=$atrib_tr?>>
		      <td align=right><?=$entradas_salidas->fields["cantidad"];?></td>
		      <td align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
		      <td align=left><?if ($entradas_salidas->fields["comentario"]) echo $entradas_salidas->fields["comentario"]; else echo "&nbsp;";?> </td>
		      <td align=left><?=$entradas_salidas->fields["deposito"]?></td>
		      <td align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
		      <td align=center><?=fecha($entradas_salidas->fields["fecha_mov"])." ".Hora($entradas_salidas->fields["fecha_mov"])?></td>
		    </tr>
		<?
		}//de if ($entradas_salidas->fields["clase_mov"]==2)
		$entradas_salidas->movenext();
	}//de for($i=0;$i<$cantidad;$i++)
	?>
	</td>
	</tr>
	</table>
	<br>
	  <tr>
	   <td>
	       <table width=40% align=right bgcolor="#e8e8e8" class="bordes">
	          <tr>
	            <td><b>Total Ingresos</b></td>
	            <td  align=right><b><?=$total_ingresos?></b></td>
	          </tr>
	          <tr>
	            <td><b>Total Egresos</b></td>
	            <td  align=right><b><?=$total_egresos?></b></td>
	          </tr>
	          <tr>
	           <td><b>Total Stock</b></td>
	           <td  align=right><b><?=$total_ingresos-$total_egresos?></b></td>
	          </tr>
	       </table>
	   </td>
	</tr>

	</table>
	<?
	echo "</div>\n";
	echo "<hr>\n";
}//de function mostrar_ing_egr_global($id_prod_esp)


/**************************************************************************************
 Genera los campos necesarios para realizar una búsqueda avanzada sobre los datos
 del Stock de Coradir

 La funcion retorna un arreglo con los valores de los filtros, que se puede usar para
 ser pasados como parametros en los links que se necesiten
***************************************************************************************/
function mostrar_filtro_avanzado()
{
	global $img_ext,$img_cont,$parametros;

	//obtenemos los valores ingresados para los distintos filtros, desde POST o desde parametros
	$filtro_desplegado=$_POST["filtro_desplegado"] or $filtro_desplegado=$parametros["filtro_desplegado"];
	$productos_considerar=$_POST["productos_considerar"] or $productos_considerar=$parametros["productos_considerar"];
	$monto_desde=$_POST["monto_desde"] or $monto_desde=$parametros["monto_desde"];
	$monto_hasta=$_POST["monto_hasta"] or $monto_hasta=$parametros["monto_hasta"];
	$cant_desde=$_POST["cant_desde"] or $cant_desde=$parametros["cant_desde"];
	if($_POST["cant_desde"]!="" && $_POST["cant_desde"]==0)
	  $cant_desde=0;
	$cant_hasta=$_POST["cant_hasta"] or $cant_hasta=$parametros["cant_hasta"];
	if($_POST["cant_hasta"]!="" && $_POST["cant_hasta"]==0)
	  $cant_hasta=0;
	$combo_cant_prod=$_POST["combo_cant_prod"] or $combo_cant_prod=$parametros["combo_cant_prod"];


	if($filtro_desplegado==1)
	{
		$img_src=$img_ext;
		$style_tabla="style='display:block'";
	}
	else
	{
		$img_src=$img_cont;
		$style_tabla="style='display:none'";
	}
	?>
	<input type="hidden" name="filtro_desplegado" value="<?=$filtro_desplegado?>">
	<table width="100%" class="bordes">
     <tr id=mo>
      <td width="1%">
       <img id="imagen_filtro_avanzado" src="<?=$img_src?>" border=0 align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.filtro_busqueda,document.all.imagen_filtro_avanzado,document.all.filtro_desplegado);">
      </td>
      <td>
	    <b>Búsqueda Avanzada</b>
      </td>
     </tr>
     <tr>
      <td colspan="2">
      	<table width="100%" align="center" id="filtro_busqueda" <?=$style_tabla?>>
      		<tr>
	      		<td width="20%">
	      			<b>Monto entre</b>
	      		</td>
	      		<td width="10%">
	      			<input type="text" name="monto_desde" value="<?=$monto_desde?>" size="10" onchange="control_numero(this,'Monto Inferior')">
	      		</td>
	      		<td width="5%" align="center">
	      			<b>y</b>
	      		</td>
	      		<td width="15%">
	      			<input type="text" name="monto_hasta" value="<?=$monto_hasta?>" size="10" onchange="control_numero(this,'Monto Superior')">
	      		</td>
	      		<td width="50%" align="right">
	      		   <b>Considerar Productos</b>
	      		   <?
	      			switch ($productos_considerar)
	      			{
	      				case 1: $selected_todos="selected";
	      						break;
	      				case 2: $selected_disp="selected";
	      						break;
	      				case 3: $selected_res="selected";
	      						break;
	      				case 4: $selected_conf="selected";
	      						break;
	      				default:$selected_todos="selected";
	      						break;
	      			}//de switch ($productos_considerar)
	      			?>
	      			<select name="productos_considerar">
	      				<option value="1" <?=$selected_todos?>>Todos</option>
	      				<option value="2" <?=$selected_disp?>>Disponibles</option>
	      				<option value="3" <?=$selected_res?>>Reservados</option>
	      				<option value="4" <?=$selected_conf?>>A Confirmar</option>
	      			</select>
	      		</td>
	      	</tr>
	      	<tr>
	      	 	<td width="20%">
      		 		<b>Cantidad entre</b>
      		 	</td>
      		 	<td width="10%">
      		 		<input type="text" name="cant_desde" value="<?=$cant_desde?>" size="10" onchange="control_numero(this,'Cantidad Inferior')">
	      		</td>
	      		<td width="5%" align="center">
	      			<b>y</b>
	      		</td>
	      		<td width="15%">
	      			<input type="text" name="cant_hasta" value="<?=$cant_hasta?>" size="10" onchange="control_numero(this,'Cantidad Superior')">
	      		</td>
	      		<td width="50%" align="right">
					 <b>
						Mostrar Cantidades
				      </b>
				      <?
				      switch($combo_cant_prod)
				      {
				      	case 1: $select_m0="selected";
				      			break;
				      	case 2: $select_i0="selected";
				      			break;
				      	case 3: $select_todos="selected";
				      			break;
				      	default:
				      			break;
				      }//de switch($_POST["combo_cant_prod"])
				      ?>
				      <select name="combo_cant_prod">
				       <option value="1" <?=$select_m0?>>>0</option>
				       <option value="2" <?=$select_i0?>>=0</option>
				       <option value="3" <?=$select_todos?>>Todas</option>
				      </select>
	      		</td>
	      </tr>
      	</table>
      </td>
     </tr>
    </table>
	<?

	$parametros_filtro=array();
	$parametros_filtro["filtro_desplegado"]=$filtro_desplegado;
	$parametros_filtro["monto_desde"]=$monto_desde;
	$parametros_filtro["monto_hasta"]=$monto_hasta;
	$parametros_filtro["cant_desde"]=$cant_desde;
	$parametros_filtro["cant_hasta"]=$cant_hasta;
	$parametros_filtro["combo_cant_prod"]=$combo_cant_prod;
	$parametros_filtro["productos_considerar"]=$productos_considerar;

	//retornamos el arreglo de parametros para que sean pasados por parametro en los links correspondientes
	return $parametros_filtro;

}//de function mostrar_filtro_avanzado()


/**************************************************************************************
 Genera los filtros SQL para las consultas de Stock Completo de Coradir, según el
 filtro de búsqueda producido por la funcion mostrar_filtro_avanzado
 Devuelve un string con las condiciones necesarias para anexar a la consulta sobre los
 datos del stock de coradir, según el filtro elegido por el usuario.
***************************************************************************************/
function add_consulta_filtro_avanzado()
{
	global $parametros;

	//obtenemos los valores ingresados para los distintos filtros, desde POST o desde parametros
	$productos_considerar=$_POST["productos_considerar"] or $productos_considerar=$parametros["productos_considerar"];
	$monto_desde=$_POST["monto_desde"] or $monto_desde=$parametros["monto_desde"];
	$monto_hasta=$_POST["monto_hasta"] or $monto_hasta=$parametros["monto_hasta"];
	$cant_desde=$_POST["cant_desde"] or $cant_desde=$parametros["cant_desde"];
	if($_POST["cant_desde"]!="" && $_POST["cant_desde"]==0)
	  $cant_desde=0;
	$cant_hasta=$_POST["cant_hasta"] or $cant_hasta=$parametros["cant_hasta"];
	if($_POST["cant_hasta"]!="" && $_POST["cant_hasta"]==0)
	  $cant_hasta=0;
	$combo_cant_prod=$_POST["combo_cant_prod"] or $combo_cant_prod=$parametros["combo_cant_prod"];

	$filtro_consulta="";

	//dependiendo de los productos considerados (disponibles, reservados o ambos), cambia los campos del filtro
	//que se deben usar para armar la consulta
	switch ($productos_considerar)
	{
		case 2: //solo productos disponibles
				//si el MONTO DESDE tiene valor, lo agregamos a la consulta
				if($monto_desde===0 || $monto_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total_disp>=$monto_desde";
				}
				//si el MONTO HASTA tiene valor, lo agregamos a la consulta
				if($monto_hasta===0 || $monto_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total_disp<=$monto_hasta";
				}
				//si la CANTIDAD DESDE tiene valor, lo agregamos a la consulta
				if($cant_desde===0 || $cant_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_disp>=$cant_desde";
				}
				//si la CANTIDAD HASTA tiene valor, lo agregamos a la consulta
				if($cant_hasta===0 || $cant_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_disp<=$cant_hasta";
				}
				//Filtramos las cantidades que se desean mostrar (>0, =0 o todas)
				if($combo_cant_prod==1)//si las cantidades a mostrar son las mayores a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_disp>0";
				}//de if($_POST["combo_cant_prod"]==1)
				else if($combo_cant_prod==2)//si las cantidades a mostrar son las iguales a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_disp=0";
				}//de else if($_POST["combo_cant_prod"]==2)

				break;
		case 3: //solo productos reservados
				//si el MONTO DESDE tiene valor, lo agregamos a la consulta
				if($monto_desde===0 || $monto_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total_res>=$monto_desde";
				}
				//si el MONTO HASTA tiene valor, lo agregamos a la consulta
				if($monto_hasta===0 || $monto_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total_res<=$monto_hasta";
				}
				//si la CANTIDAD DESDE tiene valor, lo agregamos a la consulta
				if($cant_desde===0 || $cant_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_reservada>=$cant_desde";
				}
				//si la CANTIDAD HASTA tiene valor, lo agregamos a la consulta
				if($cant_hasta===0 || $cant_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_reservada<=$cant_hasta";
				}
				//Filtramos las cantidades que se desean mostrar (>0, =0 o todas)
				if($combo_cant_prod==1)//si las cantidades a mostrar son las mayores a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_reservada>0";
				}//de if($_POST["combo_cant_prod"]==1)
				else if($combo_cant_prod==2)//si las cantidades a mostrar son las iguales a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_reservada=0";
				}//de else if($_POST["combo_cant_prod"]==2)
				break;
		case 4: //solo productos a confirmar
				//si el MONTO DESDE tiene valor, lo agregamos a la consulta
				if($monto_desde===0 || $monto_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total_conf>=$monto_desde";
				}
				//si el MONTO HASTA tiene valor, lo agregamos a la consulta
				if($monto_hasta===0 || $monto_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total_conf<=$monto_hasta";
				}
				//si la CANTIDAD DESDE tiene valor, lo agregamos a la consulta
				if($cant_desde===0 || $cant_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_a_confirmar>=$cant_desde";
				}
				//si la CANTIDAD HASTA tiene valor, lo agregamos a la consulta
				if($cant_hasta===0 || $cant_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_a_confirmar<=$cant_hasta";
				}
				//Filtramos las cantidades que se desean mostrar (>0, =0 o todas)
				if($combo_cant_prod==1)//si las cantidades a mostrar son las mayores a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_a_confirmar>0";
				}//de if($_POST["combo_cant_prod"]==1)
				else if($combo_cant_prod==2)//si las cantidades a mostrar son las iguales a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_a_confirmar=0";
				}//de else if($_POST["combo_cant_prod"]==2)
				break;
		case 1: //monto total: se ejecuta lo mismo que por default
		default:
				//si el MONTO DESDE tiene valor, lo agregamos a la consulta
				if($monto_desde===0 || $monto_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total>=$monto_desde";
				}
				//si el MONTO HASTA tiene valor, lo agregamos a la consulta
				if($monto_hasta===0 || $monto_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" monto_total<=$monto_hasta";
				}
				//si la CANTIDAD DESDE tiene valor, lo agregamos a la consulta
				if($cant_desde===0 || $cant_desde!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_total>=$cant_desde";
				}
				//si la CANTIDAD HASTA tiene valor, lo agregamos a la consulta
				if($cant_hasta===0 || $cant_hasta!="")
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_total<=$cant_hasta";
				}
				//Filtramos las cantidades que se desean mostrar (>0, =0 o todas)
				if($combo_cant_prod==1)//si las cantidades a mostrar son las mayores a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_total>0";
				}//de if($_POST["combo_cant_prod"]==1)
				else if($combo_cant_prod==2)//si las cantidades a mostrar son las iguales a cero
				{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_total=0";
				}//de else if($_POST["combo_cant_prod"]==2)
				break;

	}//de switch ($productos_considerar)

	//si el combo de cant prod no tiene datos, es la primera vez que se carga el listado, entonces traemos
	//la consulta por default, que trae solo productos con cantidad total>0
	if($combo_cant_prod=="")
	{
					if($filtro_consulta!="")
						$filtro_consulta.=" and ";
					$filtro_consulta.=" cant_total>0";
	}//de if($_POST["combo_cant_prod"]==1)

	return $filtro_consulta;

}//de function add_consulta_filtro_avanzado()
?>