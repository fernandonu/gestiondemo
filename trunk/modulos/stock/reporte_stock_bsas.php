<?PHP
require_once("../../config.php");

variables_form_busqueda("seguimiento_produccion_bsas");
echo $html_header; 
   
$orden = array(
       "default" => "3",
       //"default_up" => "1",
       "1" => "id_licitacion",
       "2" => "id_movimiento_material", 
       "3" => "descripcion",
       "4" => "monto", 
       "5" => "cantidad_reservada"
      ); 
      
$filtro = array(        
        "id_licitacion" => "ID de licitación", 
        "id_movimiento_material" => "ID Movimiento Material", 
        "descripcion" => "Descripción",        
        "monto" => "Monto",        
    );     
    
$sql_tmp="select * 
		  from(
			select nro_orden,id_licitacion,id_movimiento_material,productos.descripcion,precio_stock,(detalle_reserva.cantidad_reservada*precio_stock) as monto,licitaciones.estado.color,
      			id_log_mov_stock,nrocaso,detalle_reserva.cantidad_reservada,detalle_reserva.fecha_reserva,
      			detalle_reserva.id_fila,detalle_reserva.id_detalle_movimiento,detalle_reserva.id_log_mov_stock,
      			detalle_reserva.usuario_reserva,tipo_reserva.nombre_tipo,id_tipo_reserva,estado.color,coment_manual
        	from stock.en_stock
			join general.producto_especifico productos using(id_prod_esp)
        	join (select id_detalle_reserva,detalle_reserva.id_en_stock,detalle_reserva.id_licitacion,id_tipo_reserva,nrocaso,cantidad_reservada,fecha_reserva,
            	         usuario_reserva,id_fila,id_detalle_movimiento,id_log_mov_stock,log_movimientos_stock.comentario as coment_manual
        			from stock.detalle_reserva left join stock.log_movimientos_stock using(id_log_mov_stock)
        	  		) as detalle_reserva using (id_en_stock)
        	left join stock.tipo_reserva using(id_tipo_reserva)
        	left join compras.fila using(id_fila)
			left join mov_material.detalle_movimiento using(id_detalle_movimiento)
			left join licitaciones.licitacion using(id_licitacion)
        	left join licitaciones.estado using(id_estado)
        	where id_deposito=2 and (cant_disp > 0 or cant_reservada > 0)
		) as a";

//$where_tmp=" id_deposito=2 and (cant_disp > 0 or cant_reservada > 0) ";

$contar="buscar";
if($_POST['keyword'] || $keyword) $contar="buscar";
     
?>
<form name="reporte_stock_bsas" action='reporte_stock_bsas.php' method='post'>
	
<table align="center" >
 <tr>
  <td>
<?
 list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar); 
 //echo "<br>".$sql."<br>";  
 $resul_consulta=sql($sql,"No se pudo realizar la consulta del form busqueda") or fin_pagina();
 
 	$sql_est = "SELECT id_estado,nombre,color FROM estado";
 	$result = sql($sql_est) or die($db->ErrorMsg());
 	$estados = array();
 	while (!$result->EOF) {
		$estados[$result->fields["id_estado"]] = array(
				"color" => $result->fields["color"],
				"texto" => $result->fields["nombre"]
			);
		$result->MoveNext();
	}
 ?>
  </td>
  <td>
   <input type=submit name=form_busqueda value='Buscar'>&nbsp;
  </td>
 </tr>
</table> 
<br>
<table width='90%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left" colspan="3">
   <b>Total:</b> <?=$total_lic?> <b>Producto/s.</b>   
  </td>
  <td align="right" colspan="3">
   <?=$link_pagina;?>
  </td>
 </tr>
 
 <tr id=mo>
  <td width="10%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
  <td width="10%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Nro. PM</b></td>
  <td width="40%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Producto</b></td>
  <td width="5%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Cantidad</b></td>
  <td width="35%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Monto</b></td>
 </tr>
  
    <? 
    while (!$resul_consulta->EOF){
    ?>
    <tr id=ma<?//atrib_tr()?>>
    	   
           <?$frente="#000000";
     		$reemplazo="#ffffff";
     		$estado_lic_color=$resul_consulta->fields['color'];
     		$color_link=contraste($estado_lic_color, $frente, $reemplazo);
     		$ID=$resul_consulta->fields['id_licitacion'];
     		$link= encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"candadoponer","ID"=>$ID));
     		if ($ID==''){?>
					<td align="center" bgcolor="<?=$estado_lic_color?>" style="font-size='12'; color='<?=$color_link?>';"><?=$resul_consulta->fields['id_licitacion']?></td>				     		
				<?}
				else{?>
		    	<td align="center" bgcolor="<?=$estado_lic_color?>" style="font-size='12'; color='<?=$color_link?>'; cursor:'hand';" onclick="window.open('<?=$link?>')"><?=$resul_consulta->fields['id_licitacion']?></td>
		    <?}
		    $id_mov_material=$resul_consulta->fields['id_movimiento_material'];
		    $link= encode_link("../mov_material/detalle_movimiento.php",array("pagina"=>"listado","id"=>$id_mov_material));
		    if ($id_mov_material==''){?>
		    	<td align="center"><?=$resul_consulta->fields['id_movimiento_material']?></td>
		    <?}
		    else{?>
		    	<td align="center" <?=atrib_tr();?> onclick="window.open('<?=$link?>')"><?=$resul_consulta->fields['id_movimiento_material']?></td>
		    <?}?>
		   	<td align="left"><?=$resul_consulta->fields['descripcion']?></td>
		   	<td align="left"><?=$resul_consulta->fields['cantidad_reservada']?></td>
           <td align="center"><font size="2"><b><?echo 'U$S ' . number_format($resul_consulta->fields['monto'],2,',','.')?></b></font></td>
    </tr> 
    
  	<?$resul_consulta->MoveNext();
    }
  	?>
</table> 
<br>


<table width='95%' border=0 align=center>
<tr><td colspan=6 align=center><br>
<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia ID/Est:</b></td></tr>
<tr>
	<?
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}?>
</tr>
</table>
</td></tr>
</table><br>
	
<br>
</form>
<?=fin_pagina();?>