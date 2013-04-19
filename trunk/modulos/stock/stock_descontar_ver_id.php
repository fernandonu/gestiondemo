<?
require_once("../../config.php");
$id_prod_esp=$parametros["id_prod_esp"];
$id_deposito=$parametros["id_deposito"];
echo $html_header;
?>
<form name="descontar_ver" action="stock_descontar_ver_id" method="POST">
<?
	$sql_mov="select usuario_mov, comentario, fecha_mov, cantidad, id_tipo_movimiento,
	          tipo_movimiento.descripcion as tipo_mov,clase_mov
		      from stock.log_movimientos_stock
			  join stock.en_stock using (id_en_stock)
			  join stock.tipo_movimiento using(id_tipo_movimiento)
		      where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito and
			  (clase_mov=1 or clase_mov=2)
		      order by fecha_mov, comentario ASC";
	//clase_mov 1 es ingreso, 2 egreso

	$entradas_salidas=sql($sql_mov) or fin_pagina();

	//mostramos los ingresos y egresos
	echo "<hr>\n";
	?>
	<table align="center" width="85%" class="bordes">
	 <tr id=ma_sf>
	  <td align=center>	
	 	 <b><font size='2'>Movimientos de Stock</font></b>
	  </td>
	 </tr>
	</table>
	
	<table align="center" width="85%" class="bordes">
	<?$entradas_salidas->move(0);?>
	<tr id=mo><td align=center><b> Egresos</b></td></tr>
	<tr>
	  <td >
	  <table width=100% align=Center border=1 cellspacing="0" cellpadding="1" bordercolor=#ACACAC class="bordes">
	    <tr id=ma_sf>
	       <td width=30% align=Center><b>Tipo de Mov.</b></td>
	       <td width=10% align=Center><b>Usuario</b></td>
	       <td width=32% align=Center><b>Comentarios</b></td>
	       <td width=10% align=Center><b>Fecha</b></td>
	       <td width=10% align=Center><b>Cantidad</b></td>
	       <td width=8% align=Center><b>ID</b></td>
	    </tr>
	<?
	$cantidad=$entradas_salidas->recordCount();
	$total_egresos=0;
	for($i=0;$i<$cantidad;$i++) {
		//Obtengo el log de los egresos
		if ($entradas_salidas->fields["clase_mov"]==2) {
			$total_egresos+=abs($entradas_salidas->fields["cantidad"]);
			$string=$entradas_salidas->fields["comentario"];
			if (ereg('Pedido de Material Nº',$string)){
				$id_lic_parceado=strstr($string,'º');
				$id_lic_parceado=trim($id_lic_parceado,'º');
				$id_lic_parceado=trim($id_lic_parceado);
				$sql="select id_licitacion, idcaso from mov_material.movimiento_material where id_movimiento_material='$id_lic_parceado'";
				$result=sql($sql,'No se puede ejecutar mov material');				
				if ($result->fields['id_licitacion']==''){
					$id_lic_parceado='&nbsp;';
				}
				else $id_lic_parceado=$result->fields['id_licitacion'];
			}
			else $id_lic_parceado='&nbsp;'
	?>
	    <tr <?=$atrib_tr?>>
	      <td align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
	      <td align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
	      <td align=left><? if ($entradas_salidas->fields["comentario"]) echo $entradas_salidas->fields["comentario"]; else echo "&nbsp;" ?></td>
	      <td align=center><?=fecha($entradas_salidas->fields["fecha_mov"])?></td>
	      <td align=right><?=abs($entradas_salidas->fields["cantidad"]);?></td>
	      <td align=right><?=$id_lic_parceado;?></td>
	    </tr>
	<?
		}//del if
		$entradas_salidas->movenext();
	}//del for
	?>
	</td>
	</tr>
	</table>	
	</table>
	</form>
	</body>
	</html>	
<?fin_pagina();?>