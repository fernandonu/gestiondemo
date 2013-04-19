<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: ferni $
$Revision: 1.20 $
$Date: 2007/06/14 20:20:51 $
*/

	require_once("../../config.php");
	require_once("../personal/gutils.php");
	//////////////////////////////////////////////////////////////////////////////
	variables_form_busqueda("ventafacturas");
	$datos_barra = array(
		array("descripcion"=> "Pendientes", "cmd"=> "1"),
		array("descripcion"=> "Historial", "cmd"=> "-1")
	);
	if ($cmd == ""){
		$cmd="1";
		$_ses_ventafacturas["cmd"]=$cmd;
  	phpss_svars_set("_ses_ventafacturas", $_ses_ventafacturas);
	}
	$orden = array(
		"default_up"=>"0",
		"default" => "2",
		"1" => "fecha",
		"2" => "fac.nombre",
		"3" => "cliente",
		"4" => "id_venta_factura",
		"5" => "id_licitacion",
		"6" => "nro_factura",
		"7" => "monto_prestamo",
		"8" => "fecha_vencimiento"
	);
	$filtro = array(
  	"fecha" => "Fecha",
  	"fecha_vencimiento"=>"Fecha de vencimiento",
		"nro_factura" => "Nro. factura",
		"cliente" => "Entidad",
		"fac.nombre" => "Comprador",
		"fac.nombre" => "Factoring",
		"id_licitacion" => "ID Licitación"
	);
	/*$sql_tmp="select id_venta_factura, id_factoring, monto_prestamo, ingresos_brutos, ganancias, multas, comisiones,
		intereses, gastos_escribano, gastos_varios, gastos_varios_detalle, fecha, estado_venta, moneda, nro_factura,
		fac.nombre as factoring, cliente, id_factura, signo as simbolo_venta, comentario, fecha_cierre, usuario_cerrador,
		moneda.simbolo as simbolo_factura, facturas.id_moneda as moneda_factura
		from bancos.facturas_venta fv
			left join bancos.facturas_venta_lista fvl using (id_venta_factura)
			left join (select simbolo as signo, id_moneda as idmoneda from licitaciones.moneda) as moneda1 on (moneda=idmoneda)
			left join licitaciones.factoring fac using (id_factoring)
			left join facturacion.facturas using (id_factura)
			left join licitaciones.moneda using (id_moneda) ";*/
	
	//(monto_prestamo*(sum (precio*cant_prod)))/(t1.sumatoria+0.00000000000001) as monto_pesado,
	$sql_tmp="select id_venta_factura, id_factoring, monto_prestamo, ingresos_brutos, ganancias, multas, comisiones,
			intereses, gastos_escribano, gastos_varios, gastos_varios_detalle, fecha, estado_venta, moneda, 
			(numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,
			fac.nombre as factoring, cliente, id_factura, signo as simbolo_venta, comentario, fecha_cierre, usuario_cerrador,
			moneda.simbolo as simbolo_factura, facturas.id_moneda as moneda_factura, id_licitacion, fecha_vencimiento,finalizada_en_seguimiento,
			sum (precio*cant_prod) as monto_factura, t1.sumatoria, 
			
			(case when facturas.cotizacion_dolar is null then 1
				when facturas.cotizacion_dolar = 0 then 1
				else facturas.cotizacion_dolar
				end) as cotiz_dolar, valor_dolar
		from bancos.facturas_venta fv
			left join bancos.facturas_venta_lista fvl using (id_venta_factura)
			left join (select simbolo as signo, id_moneda as idmoneda from licitaciones.moneda) as moneda1 on (moneda=idmoneda)
			left join licitaciones.factoring fac using (id_factoring)
			left join facturacion.facturas using (id_factura)
			left join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
			left join facturacion.items_factura using (id_factura)
			left join licitaciones.moneda using (id_moneda)
			left join (select id_venta_factura,sum (suma) as sumatoria
                       from 
                          (select id_venta_factura, 
                           (case when moneda=1 then (case when id_moneda=2 then  sum (precio * valor_dolar * cant_prod)
                                                                           else  sum (precio * cant_prod) end ) 
                                               else (case when id_moneda=1 then  sum (precio / valor_dolar * cant_prod)
                                                                           else  sum (precio * cant_prod) end ) 
                            end ) as suma
						   from bancos.facturas_venta_lista fvl 
						   join facturacion.facturas f using (id_factura) join facturacion.items_factura itf using (id_factura) 
						   join bancos.facturas_venta using (id_venta_factura) 
						   group by id_venta_factura,id_moneda,moneda) as c
						   group by id_venta_factura
				       )as t1 using(id_venta_factura)";
	if ($cmd==-1) $where_tmp=" estado_venta=0 ";
	else $where_tmp=" estado_venta=1 ";
	$where_tmp.=" group by id_venta_factura, id_factoring, monto_prestamo, ingresos_brutos, ganancias, multas, comisiones,
		intereses, gastos_escribano, gastos_varios, gastos_varios_detalle, fecha, fecha_vencimiento, estado_venta, moneda, nro_factura,
		numeracion_sucursal.numeracion,
		fac.nombre, cliente, id_factura, signo, comentario, fecha_cierre, usuario_cerrador, t1.sumatoria,
		moneda.simbolo, facturas.id_moneda, id_licitacion, cotizacion_dolar, valor_dolar,finalizada_en_seguimiento";
	//////////////////////////////////////////////////////////////////////////////	
	echo $html_header;
	if($parametros['accion']!=""){ Aviso($parametros['accion']);}
	
?>
<form name="form1" method="POST" action="facturas_venta.php">

	<table cellspacing=2 cellpadding=5 border=0 bgcolor=<? echo $bgcolor3 ?> width=95% align=center>
		<tr>
			<td>
				<? generar_barra_nav($datos_barra);?>
			</td>
		</tr>
		<tr>
			<td align=center>
				<? list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); 
				//echo $sql;
				?>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input type=submit name=buscar value='Buscar'>
			</td>
		</tr>
	</table>
	<?  //obtengo facturas vendidas en Seguimiento de cobros 
		$consulta="(select id_factura, (ns.numeracion || '-' || f.nro_factura) as nro_factura
                    from (
	                   select max(fecha), id_cobranza, id_estado_cobranza
		               from licitaciones.historial_estados_cobranzas supc
		               where fecha=(select max(fecha) 
                                    from licitaciones.historial_estados_cobranzas where id_cobranza=supc.id_cobranza)
		               group by id_cobranza, id_estado_cobranza
	                   )as hec
	                   join licitaciones.cobranzas c using (id_cobranza)
	                   left join licitaciones.estado_cobranzas ec using(id_estado_cobranza)
	                   left join facturacion.facturas f using (id_factura)
                       join facturacion.numeracion_sucursal ns using (id_numeracion_sucursal)
	                   where (id_estado_cobranza=6) and (c.estado='PENDIENTE') and (f.estado='t')
                     )except(select id_factura, (numeracion ||'-'|| nro_factura) as nro_factura
							from bancos.facturas_venta_lista
							join bancos.facturas_venta using (id_venta_factura)
							join facturacion.facturas using (id_factura) 
							join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
                    )";

		$rta_consulta=sql($consulta, "<br>103") or fin_pagina();
		if ($rta_consulta->recordCount()>0){
	?>
			<center><h3><font color="Red"><b>Las siguientes facturas no han sido asignadas a alguna venta:&nbsp;</b>
	<?
			echo $rta_consulta->fields["nro_factura"];
			$rta_consulta->moveNext();
			while (!$rta_consulta->EOF){
				echo ", ".$rta_consulta->fields["nro_factura"];
				$rta_consulta->moveNext();
			}
	?>
		</font></h3></center>
	<?
		}
	?>
	<table align="center" width="95%" cellpadding="1" cellspacing="0" border="1">
		<tr id=mo>
 			<td width="5%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Nro.</a></b></td>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Fecha</a></b></td>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>$up))?>'>Vencimiento</a></b></td>
 			<td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Id. Lic.</a></b></td>
 			<td width="20%"5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Entidad</a></a></td>
 			<td width="10%"5%"><b>Días transcurridos</b></td>
 			<td width="10%"5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Nro. Factura</a></b></td>
 			<td width="10%"5%"><b>Monto Factura</b></td>
 			<td width="10%"5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Factoring</a></b></td>
 			<td width="10%"5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Monto Préstamo (*)</a></b></td>
		</tr>

		<?
			$db->SetFetchMode(ADODB_FETCH_ASSOC);

			$result=sql($sql,"$sql") or fin_pagina();
			$i=0;
			$fila_corriente=array();
			while (!$result->EOF){
				$fila_corriente[$i++]=$result->fields;
				$result->moveNext();
			}
				
			for ($i=0; $i<count($fila_corriente); $i++){
				if ($fila_corriente[$i]["id_factura"]=="")  //si es una sola factura 
					$fila_corriente[$i]["monto_prestamo_pesado"]=$fila_corriente[$i]["monto_prestamo"];
				else
					 if ($fila_corriente[$i]["monto_factura"]!=0) {// mas de una factura saco un proporcional
					 switch ($fila_corriente[$i]["moneda"]) {
						     case 1:if ($fila_corriente[$i]["moneda_factura"] == 2)
						                $monto_factura=$fila_corriente[$i]["monto_factura"] * $fila_corriente[$i]["valor_dolar"];                                    else   
						                $monto_factura=$fila_corriente[$i]["monto_factura"];
						     break;          
						     case 2: if ($fila_corriente[$i]["moneda_factura"] ==1)
	                                  $monto_factura=$fila_corriente[$i]["monto_factura"] / $fila_corriente[$i]["valor_dolar"];
	                                 else   
						              $monto_factura=$fila_corriente[$i]["monto_factura"];   
						    break;
						 } //fin switch 
						 
					$fila_corriente[$i]["monto_prestamo_pesado"]=($monto_factura*$fila_corriente[$i]["monto_prestamo"])/$fila_corriente[$i]["sumatoria"]; 
					}
					else $fila_corriente[$i]["monto_prestamo_pesado"]="?";
			}
	
			$sumatoria_monto_prestamo=0;
			$sumatoria_monto_factura=0;
		
			for ($i=0; $i<count($fila_corriente); $i++){
				$id_venta_factura=$fila_corriente[$i]["id_venta_factura"];
				$fecha=$fila_corriente[$i]["fecha"]=Fecha($fila_corriente[$i]["fecha"]);
				$fecha_venc=$fila_corriente[$i]["fecha_vencimiento"]=Fecha($fila_corriente[$i]["fecha_vencimiento"]);
				if ($fila_corriente[$i]["estado_venta"]!="0")	$dias_transcurridos=diferencia_dias($fila_corriente[$i]["fecha"], date("d/m/Y"));
				else $dias_transcurridos="-";
				if ($fila_corriente[$i]["id_factura"]!=""){
					$id_factura=$fila_corriente[$i]["id_factura"];
					$id_licitacion=$fila_corriente[$i]["id_licitacion"];
					$cliente=$fila_corriente[$i]["cliente"];
					$nro_factura=$fila_corriente[$i]["nro_factura"];
					$monto_factura=$fila_corriente[$i]["simbolo_factura"]." ".formato_money($fila_corriente[$i]["monto_factura"]);
				}else{
					$id_factura="-";
					$cliente="-";
					$nro_factura="-";
					$monto_factura="-";
				}
				$factoring=$fila_corriente[$i]["factoring"];
				if ($fila_corriente[$i]["finalizada_en_seguimiento"]==1 && $cmd==1) { //si esta finalizada en seguimiento de cobros la muestra en cero
				 $monto_prestamo=$fila_corriente[$i]["simbolo_venta"]." ".formato_money(0);
				}
				elseif ($fila_corriente[$i]["monto_prestamo_pesado"]=="?") $monto_prestamo="?";
				else $monto_prestamo=$fila_corriente[$i]["simbolo_venta"]." ".formato_money($fila_corriente[$i]["monto_prestamo_pesado"]);

				if ($fila_corriente[$i]["finalizada_en_seguimiento"]!=1) { //si esta en una no lo suma porque esta finalizada
				if ($fila_corriente[$i]["moneda"]==1) $sumatoria_monto_prestamo+=$fila_corriente[$i]["monto_prestamo_pesado"];
				else $sumatoria_monto_prestamo+=$fila_corriente[$i]["monto_prestamo_pesado"]*$fila_corriente[$i]["valor_dolar"];
				} 
				
				if ($fila_corriente[$i]["moneda_factura"]==1) $sumatoria_monto_factura+=$fila_corriente[$i]["monto_factura"];
				else $sumatoria_monto_factura+=$fila_corriente[$i]["monto_factura"]*$fila_corriente[$i]["valor_dolar"];

				$ref = encode_link("facturas_venta_detalle.php",array_merge($fila_corriente[$i], array("modo"=>"modif", "pagina"=>"facturas_venta_detalle.php")));
				tr_tag($ref);
				if (($fila_corriente[$i+1]["id_venta_factura"]==$id_venta_factura)||($fila_corriente[$i-1]["id_venta_factura"]==$id_venta_factura)) $color_lista="bgcolor='#ece078'";
				else $color_lista="";
				echo "<td $color_lista>".$id_venta_factura."</td>";
				echo "<td $color_lista>".$fecha."</td>";
				echo "<td $color_lista>";							
				if (Fecha_db($fecha_venc)<=date('Y-m-d')){
					$color_fuente="red";
					$tam_fuente="size='+1'";
				}
				else{
					$color_fuente="black";
					$tam_fuente="";
				}
				?>
					<font color="<?=$color_fuente?>" <?=$tam_fuente?>>
				      <?if ($fecha_venc) echo $fecha_venc; else echo "&nbsp;";?>
				    </font>
				<?echo "</td>";
				echo "<td align='center' $color_lista>";
				     if ($id_licitacion) echo $id_licitacion; else echo "&nbsp;";
				echo "</td>";
				echo "<td align='center' $color_lista>".$cliente."</td>";
				echo "<td align='center' $color_lista>".$dias_transcurridos."</td>";
				echo "<td align='center' $color_lista>".$nro_factura."</td>";
				echo "<td align='center' nowrap $color_lista>".$monto_factura."</td>";
				echo "<td nowrap $color_lista>".$factoring."</td>";
				if ($fila_corriente[$i]["finalizada_en_seguimiento"]==1 && $cmd==1) 
				     $color_monto="bgcolor='#FF4443' title='Finalizada en seguimiento de cobros'";
				else $color_monto=$color_lista;
				echo "<td align='right' $color_monto>".$monto_prestamo."</td>";
			}
			$rta=sql("select nextval('facturas_venta_id_venta_factura_seq') as id_venta");
			$next_venta=$rta->fields["id_venta"];
?>
		<tr>
			<td colspan="10">
				<table width="100%" border="1" bgcolor="<?=$bgcolor2?>" cellpadding="3" bordercolor="black">
					<tr>
						<td id="mo">
							Total Monto Facturas:
						</td>
						<td>
							$ <?=formato_money($sumatoria_monto_factura)?>
						</td>
						<td id="mo">
							Total Monto Préstamos:
						</td>
						<td>
							$ <?=formato_money($sumatoria_monto_prestamo)?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="10" bgcolor="<?=$bgcolor3?>">
				(*): El total del pr&eacute;stamo se distribuye proporcionalmente con respecto a los montos de cada una de las facturas de la venta respectiva.
			</td>
		</tr>
		<tr>
			<td colspan="10" align="center" bgcolor="<?=$bgcolor3?>">
				<input type="button" name="nueva_venta" value="Nueva venta" onclick="document.location.href='<?=encode_link("facturas_venta_detalle.php",array("modo"=>"nuevo", "id_venta_factura"=>$next_venta, "pagina"=>"facturas_venta_detalle.php"))?>'">
			</td>
		</tr>
	</table>

</form>
<?
fin_pagina("false");
?>
