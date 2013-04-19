<?php
  /*
$Author: ferni $
$Revision: 1.11 $
$Date: 2007/04/04 21:08:57 $
*/
include("../../config.php");

echo $html_header;

$sql="select distinct id_proveedor,razon_social,calif_prove,clasificado
          from general.proveedor
          left join compras.orden_de_compra using(id_proveedor)
          where (calif_prove='t') 
	      order by razon_social";
$result=$db->execute($sql) or die($db->errormsg());

?>

<form name="form1" action="clasif_prove_listado.php" method="POST" enctype='multipart/form-data'>
<table width="80%"  border="1" align="center">
<tr>
   <td id=mo colspan="4">Referencias</td>
</tr> 

<tr>
 	<td width="40%">
 		<b>Letra A: <font color="#000099"> Plazo de Pago </font></b>
  	</td>
 	
 	<td width="40%">
 		<b>Letra B: <font color="#000099"> Limite de Credito</font></b>
  	</td>
</tr>

<tr>
 	<td>
 		<b>Letra C: <font color="#000099"> Cumplimiento de Entrega en Tiempo</font></b> 
 	</td>

 	<td>
 		<b>Letra D: <font color="#000099"> Esta Certificado ISO?</font></b> 
 	</td>
</tr>

<tr>
 	<td>
 		<b>Letra E: <font color="#000099"> Incidentes de Inconformidad</font></b> 
 	</td>
 	<td>
 		<b>Letra F: <font color="#000099"> Valoración Subjetiva</font></b> 
 	</td>
</tr>

<tr>
 	<td>
 		<b>Letra G: <font color="#000099"> Cantidad de Ordenes de Compra Pagadas</font></b> 
 	</td>
 	<td>
 		<b>Letra H: <font color="#000099"> Monto Total Comprado</font></b> 
 	</td>
</tr>
</table>
<br>
<table width="100%"  border="1" align="center">
<tr>
   <td id=mo colspan="12"><strong> <font size="2"> Listado de Proveedores con Calificación</font> </strong></td>
</tr> 
<tr>
   <td id=mo width="35%">Proveedor</td>
   <td id=mo width="7%">Calificación</td>
   <td id=mo width="7%">Calificación Sugerida</td>
   <td id=mo width="6%">Letra A</td>
   <td id=mo width="6%">Letra B</td>
   <td id=mo width="6%">Letra C</td>
   <td id=mo width="6%">Letra D</td>
   <td id=mo width="6%">Letra E</td>
   <td id=mo width="6%">Letra F</td>
   <td id=mo width="6%">Letra G</td>
   <td id=mo width="6%">Letra H</td>
</tr> 
<?
$result->MoveFirst();
while (!$result->EOF){
?>
<tr>
   	<td width="35%" align="left"><strong><font size="2" color="#0000A0"><?=$result->fields["razon_social"];?> </font></strong></td>
   <?
   switch ($result->fields["clasificado"]){
		case A: 
		{
			$color_calif="#00CC00";
			break;
		}
		case B: 
		{
			$color_calif="#99FF00";
			break;
		}
		case C: 
		{
			$color_calif="#FFFF66";
			break;
		}
		case D: 
		{
			$color_calif="#FF9900";
			break;
		}
		case E: 
		{
			$color_calif="#ff0000";
			break;
		}
		}//del switch
		?>	
   
	<td align="center" bgcolor=<?=$color_calif?>><strong><font size="2" color="Black"> <?=$result->fields["clasificado"];?></font></strong></td>
   
   <?
    	$proveedor=$result->fields["id_proveedor"];
   		//*****************Plazo de Pago*****************************//
		//realizo la consulta recuperando el maximo y minimo de los periodos de pagos de los proveedores
    	//los proveedores que utilizo son los (locales) y (Nacionales)
    	$sql1="select Max(forma_de_pago.dias) AS maximo,  Min(forma_de_pago.dias) as minimo
			   from general.proveedor, general.credito_proveedor, compras.plantilla_pagos, compras.pago_plantilla, compras.forma_de_pago 
			   where (proveedor.id_proveedor = credito_proveedor.id_proveedor) AND 
			   ((proveedor.proveedor_local = 1) OR (proveedor.proveedor_internacional = 1)) AND 
			   (credito_proveedor.id_plantilla_pagos = plantilla_pagos.id_plantilla_pagos) AND 
			   (plantilla_pagos.id_plantilla_pagos = pago_plantilla.id_plantilla_pagos) AND 
			   (pago_plantilla.id_forma = forma_de_pago.id_forma)";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$maximo_a=$result_1->fields["maximo"];
		$minimo_a=$result_1->fields["minimo"];
		
		//recupero el periodo de pago del proveedor
		$sql1="select forma_de_pago.dias 
				from general.proveedor, general.credito_proveedor, compras.plantilla_pagos, compras.pago_plantilla, compras.forma_de_pago 
				where (proveedor.id_proveedor = credito_proveedor.id_proveedor) AND 
			    ((proveedor.proveedor_local = 1) OR (proveedor.proveedor_internacional = 1)) AND 
				(credito_proveedor.id_plantilla_pagos = plantilla_pagos.id_plantilla_pagos) AND 
				(plantilla_pagos.id_plantilla_pagos = pago_plantilla.id_plantilla_pagos) AND 
				(pago_plantilla.id_forma = forma_de_pago.id_forma) AND
				(proveedor.id_proveedor=$proveedor)";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$dias_plazo_prov = $result_1->fields["dias"];
		if ($maximo_a != 0)	$indicador_a=($dias_plazo_prov / $maximo_a);
		else $indicador_a = 0;		
		
		//*****************Limite de Credito*****************************//
		//realizo la consulta recuperando el maximo y minimo de los creditos en pesos
    	$sql1="select Max(credito_proveedor.limite) AS maximo,  Min(credito_proveedor.limite) as minimo
				from general.proveedor,general.credito_proveedor 
				where (proveedor.id_proveedor = credito_proveedor.id_proveedor) AND 
			    ((proveedor.proveedor_local = 1) OR (proveedor.proveedor_internacional = 1)) AND 
				(credito_proveedor.id_moneda=1)";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$maximo_pesos=$result_1->fields["maximo"];
		$minimo_pesos=$result_1->fields["minimo"];
		
		//realizo la consulta recuperando el maximo y minimo de los creditos en dolar ya convertidos a pesos
		$sql1="select Max(credito_proveedor.limite) * 3 AS maximo,  Min(credito_proveedor.limite) as minimo
				from general.proveedor,general.credito_proveedor 
				where (proveedor.id_proveedor = credito_proveedor.id_proveedor) AND 
			    ((proveedor.proveedor_local = 1) OR (proveedor.proveedor_internacional = 1)) AND 
				(credito_proveedor.id_moneda=2)";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$maximo_dolar=$result_1->fields["maximo"];
		$minimo_dolar=$result_1->fields["minimo"];
		
		//me quedo con el maximo y minimo comparado con lo que viene en dolar
		$maximo_b=max($maximo_pesos,$maximo_dolar);
		$minimo_b=min($minimo_pesos,$minimo_dolar);
				
		//recupero el periodo de pago del proveedor
		$sql1="select credito_proveedor.limite, credito_proveedor.id_moneda
				from general.proveedor,general.credito_proveedor 
				where (proveedor.id_proveedor = credito_proveedor.id_proveedor) AND 
			    ((proveedor.proveedor_local = 1) OR (proveedor.proveedor_internacional = 1)) AND 
				(credito_proveedor.id_proveedor=$proveedor)";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		
		//recupero el limite de credito del proveedor
		$limite_cred_prov=$result_1->fields["limite"];
		//si esta en dolar lo multiplico por 3 y lo trasformo a pesos
		if ($result_1->fields["id_moneda"]==2) $limite_cred_prov = ($limite_cred_prov*3);
		//me quedo con el indicador b
		if ($maximo_b!=0) $indicador_b=($limite_cred_prov/$maximo_b);
		else $indicador_b=0;

		//*****************Cumplimiento de Entrega en Tiempo**************************//
		//recupero todas las ordenes de compra del proveedor que tengan mercaderias entregadas
		//y con los estados correctos
		$sql1="select (orden_de_compra.nro_orden)
				from compras.orden_de_compra
				where ((orden_de_compra.id_proveedor=$proveedor) AND
					   (orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
				AND (orden_de_compra.nro_orden in (
													select orden_de_compra.nro_orden
													from compras.orden_de_compra
													inner join compras.fila
													inner join compras.recibido_entregado
													inner join compras.log_rec_ent
													on (recibido_entregado.id_recibido=log_rec_ent.id_recibido)
													on (fila.id_fila=recibido_entregado.id_fila)
													on (orden_de_compra.nro_orden=fila.nro_orden)
													where (
															(orden_de_compra.id_proveedor=$proveedor)
															AND
															(orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
															)
													order by orden_de_compra.nro_orden
													)
				)
				)
				order by orden_de_compra.nro_orden ";

		$result_1=$db->execute($sql1) or die($db->errormsg());
		$total_comprado=$result_1->RecordCount();//recupero la cantidad de ordenes de compras

		//tengo 2 consultas para una me trae la fecha de entrega
		//y la otra me trae la fecha en que se entrego efectivamente el primer producto
		$sql1="select orden_de_compra.nro_orden, orden_de_compra.fecha_entrega
				from compras.orden_de_compra
				where ((orden_de_compra.id_proveedor=$proveedor) AND (orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
				AND (orden_de_compra.nro_orden in (
													select orden_de_compra.nro_orden
													from compras.orden_de_compra
													inner join compras.fila
													inner join compras.recibido_entregado
													inner join compras.log_rec_ent
													on (recibido_entregado.id_recibido=log_rec_ent.id_recibido)
													on (fila.id_fila=recibido_entregado.id_fila)
													on (orden_de_compra.nro_orden=fila.nro_orden)
													where (
															(orden_de_compra.id_proveedor=$proveedor)
															AND
															(orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
														)
													order by orden_de_compra.nro_orden
													)
				)
				)
				order by orden_de_compra.nro_orden ";

		$result_1=$db->execute($sql1) or die($db->errormsg());

		$sql2="select orden_de_compra.nro_orden, min (log_rec_ent.fecha) as min_fecha_cumplido
				from compras.orden_de_compra
				inner join compras.fila
				inner join compras.recibido_entregado
				inner join compras.log_rec_ent
				on (recibido_entregado.id_recibido=log_rec_ent.id_recibido)
				on (fila.id_fila=recibido_entregado.id_fila)
				on (orden_de_compra.nro_orden=fila.nro_orden)
				where (
						(orden_de_compra.id_proveedor=$proveedor)
						AND
						(orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
						)
				group by orden_de_compra.nro_orden
				order by orden_de_compra.nro_orden  ";

		$result_2=$db->execute($sql2) or die($db->errormsg());

		$total_Cumplido = 0;
		$result_1->MoveFirst();
		$result_2->MoveFirst();

		while ((!$result_1->EOF) or (!$result_2->EOF)){


			if ($result_1->fields["fecha_entrega"]){
				$fech_ent = Fecha($result_1->fields["fecha_entrega"]);

				//armo un array con la fecha para incrementar despues
				list($dia,$mes,$anio)=explode("/",$fech_ent);
				//le sumo 3 dias a la fecha de entrega
				$fech_ent=date("d/m/Y",mktime(0,0,0,$mes,$dia+3,$anio));

			}
			if ($result_2->fields["min_fecha_cumplido"]){
				$fech_cump = Fecha($result_2->fields["min_fecha_cumplido"]);
				//$fech_cump = strtotime($result_2->fields["min_fecha_cumplido"]);
			}
			/*
			if ($result_1->fields["nro_orden"]==5408){
			 echo "Fecha entrega" . $fech_ent . "  ";
			 echo "Fecha cumplida" . $fech_cump. "  ";
			 if ($fech_ent >= $fech_cump) echo "fecha entrega es mayor o igual que fecha cumplida";
			 else echo"fecha entrega es menor que fecha cumplida";
			}
			*/
			//compara_fechas suma uno si cumple la condicion
			if (($fech_ent >= $fech_cump) && ($result1->fields["id_proveedor"] == $result2->fields["id_proveedor"])) $total_Cumplido++;
			$result_1->MoveNext();
			$result_2->MoveNext();
		}
		
		if ($total_comprado!=0) $indicador_c = ($total_Cumplido/$total_comprado);		
		else $indicador_c=0;
		//*****************Certificación ISO **************************//		
		$sql1="select id_proveedor, iso 
				from general.proveedor  
				where ((proveedor.proveedor_local = 1) OR (proveedor.proveedor_internacional = 1)) AND 
				(proveedor.id_proveedor=$proveedor)";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		
		if ($result_1->fields["iso"]==1) {
			$indicador_d=1;
			$indicador_d_text="SI";
		}
		else {
			$indicador_d=0;
			$indicador_d_text="NO";
		}
		
		//*****************Incidentes de Conformidad**************************//
		$sql1="select count (id_reportes_incidentes) as cantinc
				from calidad.reportes_incidentes 
				where id_proveedor=$proveedor ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$cantinc=$result_1->fields["cantinc"];
		$indicador_e= 1/($cantinc+1);
		//*****************Valoracion Subjetiva*****************//
		$sql1="select count (calificacion_proveedor.id_calificacion) as cant_calificacion
				from general.calificacion_proveedor 
				where id_proveedor=$proveedor ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$cant_calificacion=$result_1->fields["cant_calificacion"];
		
		$sql1="select sum (calificado) as suma_calidicacion
				from general.calificacion_proveedor 
				where id_proveedor=$proveedor ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$suma_calificacion=$result_1->fields["suma_calidicacion"];
		
		if ($cant_calificacion!=0) $indicador_f=($suma_calificacion/$cant_calificacion);
		else $indicador_f=0;
		
		
		//*****************Cant de Ordenes de Compras Pagadas*****************//
		$sql1="select max (total_ord_compras) as total_ord_compras_max 
				from (
				select orden_de_compra.id_proveedor, count (orden_de_compra.nro_orden) as total_ord_compras
 				from compras.orden_de_compra 
				where (orden_de_compra.estado='g')  
				group by orden_de_compra.id_proveedor) as sub1 ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$total_ord_compras_max = $result_1->fields["total_ord_compras_max"];
		$sql1="select max (total_ord_compras) as total_ord_compras_max 
				from (
				select orden_de_compra.id_proveedor, count (orden_de_compra.nro_orden) as total_ord_compras
 				from compras.orden_de_compra 
				where (
						(orden_de_compra.id_proveedor=$proveedor) AND 
						(orden_de_compra.estado='g')
						)  
				group by orden_de_compra.id_proveedor) as sub1 ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$total_ord_compras_max_prov = $result_1->fields["total_ord_compras_max"];
		if ($total_ord_compras_max!=0)
			$indicador_g=($total_ord_compras_max_prov/$total_ord_compras_max);
		else 
			$indicador_g=0;		
		//*****************Monto Total Comprado*****************//
		$sql1="select sum (monto_total) as monto_proveedor, id_proveedor
			from (
				select orden_de_compra.nro_orden, 

				case when (valor_dolar is null or valor_dolar < 1 ) then sum (fila.precio_unitario * fila.cantidad) 
				else sum (fila.precio_unitario * fila.cantidad * orden_de_compra.valor_dolar) end  as monto_total

				from compras.orden_de_compra 
				join compras.fila 
				using(nro_orden)
				where (
						(orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
						) 
				group by orden_de_compra.nro_orden, orden_de_compra.valor_dolar
				order by monto_total DESC) as sub1 
			join
				compras.orden_de_compra 
			using (nro_orden)
			group by id_proveedor 
			order by monto_proveedor DESC ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$result_1->MoveFirst();
		$monto_maximo=$result_1->fields["monto_proveedor"];
		
		
		
		$sql1="select sum (monto_total) as monto_proveedor, id_proveedor
			from (
				select orden_de_compra.nro_orden, 

				case when (valor_dolar is null or valor_dolar < 1 ) then sum (fila.precio_unitario * fila.cantidad) 
				else sum (fila.precio_unitario * fila.cantidad * orden_de_compra.valor_dolar) end  as monto_total

				from compras.orden_de_compra 
				join compras.fila 
				using(nro_orden)
				where (
						(orden_de_compra.id_proveedor=$proveedor) AND
						(orden_de_compra.estado='e'OR orden_de_compra.estado='d' OR orden_de_compra.estado='g')
						) 
				group by orden_de_compra.nro_orden, orden_de_compra.valor_dolar
				order by monto_total DESC) as sub1 
			join
				compras.orden_de_compra 
			using (nro_orden)
			group by id_proveedor 
			order by monto_proveedor DESC ";
		$result_1=$db->execute($sql1) or die($db->errormsg());
		$monto_proveedor=$result_1->fields["monto_proveedor"];
		
		if ($monto_maximo!=0) $indicador_h=($monto_proveedor/$monto_maximo);
		else $indicador_h=0;
		
		
/*--------------------------------------------------------------------------------*/				
		$sql="select * from calidad.const_eval_prov";
		$result_constante=$db->execute($sql) or die($db->errormsg());
		$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;$g=0;$h=0;
		$a=$result_constante->fields["ka"] * $indicador_a;
		$b=$result_constante->fields["kb"] * $indicador_b;
		$c=$result_constante->fields["kc"] * $indicador_c;
		$d=$result_constante->fields["kd"] * $indicador_d;
		$e=$result_constante->fields["ke"] * $indicador_e;
		$f=(($result_constante->fields["kf"] * $indicador_f)/10);//divido por 10 debido a que el promedio de la valoracion subjetiva es de 1..10 y el indocador tiene que se de 0..1
		$g=$result_constante->fields["kg"] * $indicador_g;
		$h=$result_constante->fields["kh"] * $indicador_h;
		$calificacion=$a+$b+$c+$d+$e+$f+$g+$h;
		//tengo que poner un color segun la calificacion
		
		switch ($calificacion){
		case ((100>=$calificacion)&& ($calificacion>80)): 
		{
			$color_calif="#00CC00";
			$letra_calif="A";
			break;
		}
		case ((80>=$calificacion)&& ($calificacion>60)): 
		{
			$color_calif="#99FF00";
			$letra_calif="B";
			break;
		}
		case ((60>=$calificacion)&& ($calificacion>40)): 
		{
			$color_calif="#FFFF66";
			$letra_calif="C";
			break;
		}
		case ((40>=$calificacion)&& ($calificacion>20)): 
		{
			$color_calif="#FF9900";
			$letra_calif="D";
			break;
		}
		case ((20>=$calificacion)&& ($calificacion>0)): 
		{
			$color_calif="#ff0000";
			$letra_calif="E";
			break;
		}
		}//del switch
		?>	
   
   
   <td align="center" bgcolor=<?=$color_calif?>> <strong><font size="2" color="Black"> <?echo $letra_calif . " = " . number_format($calificacion,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($a,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($b,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($c,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($d,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($e,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($f,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($g,2);?> </font></strong></td>
   <td align="center"> <strong><font size="2" color="Black"> <?echo number_format($h,2);?> </font></strong></td>
</tr> 


<?
	$result->MoveNext();
	$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;$g=0;$h=0;	
}
?>



</table>


<div align="center">
    <br>
	<input type=button name='Cerrar' value='Cerrar' onclick="window.close()" title="Cerrar">
</div>
<?fin_pagina()?>
</form>
</body>
<br>
