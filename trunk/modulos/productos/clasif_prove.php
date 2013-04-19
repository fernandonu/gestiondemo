<?php
  /*
$Author: ferni $
$Revision: 1.40 $
$Date: 2007/04/04 21:09:08 $
*/
include("../../config.php");

echo $html_header;

$proveedor=$_POST["proveedor"] or $proveedor=$parametros["proveedor"] or $proveedor=$_GET['proveedor'];
$clasificado=$_POST["clasificado"];
$comentario=$_POST["comentario"];
$cmd1=$_POST["cmd1"] or $cmd1=$parametros["cmd1"];

if ($cmd1 == "clasificar") {
	$sql[]="insert into historial_proveedor (fecha,usuario,clasificado,id_proveedor,comentario)"
		." values ('".date("Y-m-d H:i:s")."','".$_ses_user['login']."','$clasificado',$proveedor,'$comentario')";
	$sql[]="update proveedor set clasificado='$clasificado' where id_proveedor=$proveedor";
	sql($sql) or die;
}


function listar_proveedores() {
	global $proveedor,$db,$boton_cerrar;

	$sql="select distinct id_proveedor,razon_social,calif_prove
          from general.proveedor
          left join compras.orden_de_compra using(id_proveedor)
          where (calif_prove='t')
          order by razon_social ";
	$result=$db->execute($sql) or die($db->errormsg());
	while ($fila=$result->FetchRow()) {
		echo "<option value='".$fila["id_proveedor"]."'";
		if ($proveedor==$fila["id_proveedor"]) echo " selected";
		echo ">".$fila["razon_social"]."</option>\n";
	}
}

//permiso para noelia juan manuel corapi
if (permisos_check("inicio","proveedor_permisoClasif")or ($_ses_user["login"]=="ferni"))
	$tiene_permiso="";
else
    $tiene_permiso=" disabled";

?>
<script>
function  buscar_op_proveedor(obj){

   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13)
   {
         borrar_buffer();

		 frm.action='<?=$_SERVER['SCRIPT_NAME'] ?>';
		 frm.submit();

      // if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       //en el indice cero la opcion no es valida
       for (var opcombo=1;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter



}
</script>
<br>
<table width=95% class="bordes" cellspacing=1 cellpadding=2 align=center>
<tr>
<td align=center id="mo">
<b>Calificación de Proveedores</b>
</td></tr><tr><td align=center>
<form action='clasif_prove.php' method='post' name='frm'>
<!-- onchange='frm.submit();' -->
<select name='proveedor'   onKeypress= "buscar_op_proveedor(this)" onblur="borrar_buffer()" onclick= "borrar_buffer()">
	<?listar_proveedores();?>
	</select>
	<input type=submit name='Ver/Actualizar' value='Ver / Actualizar'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?
	$link = encode_link("clasif_prove_listado.php",array());
	echo "<a href='$link' target='_blanck'> Listado de Proveedores</a>";
	?>

	</form></td></tr>

	<?if (!$proveedor) $sql="select id_proveedor,razon_social,cuit,iva,observaciones,clasificado,nbre_fantasia from proveedor order by razon_social limit 1 offset 0";
	else $sql="select razon_social,cuit,iva,observaciones,clasificado,nbre_fantasia from proveedor where id_proveedor=$proveedor";
	$result=sql($sql) or die;

	if (!$proveedor) $proveedor=$result->fields["id_proveedor"];?>
	<tr><td align=center id="ma">
	<b>Datos del Proveedor</b>
	</td></tr><tr><td align=center>
	<table width=80% border=1 cellpadding=2><tr>
	<td colspan=2 align=center>
	<b>Razon Social</b>: <?=$result->fields["razon_social"];?>
	</td></tr><tr><td width='50%'>
	<b>C.U.I.L.</b>: <?=$result->fields["cuit"];?>
	</td><td>
	<b>Tipo Iva</b>: <?=$result->fields["iva"];?>
	</td></tr><tr><td colspan=2>
	<left><b>Nombre Fantasía: </b></left>
	<?=$result->fields["nbre_fantasia"];?>
	</td></tr><tr><td align=center colspan=2>
	<center><b>Observaciones</b></center><br>
	<?=$result->fields["observaciones"];?>
	</td></tr>
	<tr><td align='center'>
	<b>Calificado tipo</b>:
	<?=$clasificado=$result->fields["clasificado"];?>
	<form action='clasif_prove.php' method='post' name='frm1'>
	<input type=hidden name=proveedor value='<?=$proveedor?>'>
	<input type=hidden name=cmd1 value='clasificar'>
	<? if (isset($_GET['proveedor']) || $_POST['boton_cerrar'] ==1) {?>
     <input type='hidden' name="boton_cerrar" value="<? if ($_POST['boton_cerrar']) echo $_POST['boton_cerrar']; else echo 1;?>" >
     <? } ?>
	<select name='clasificado' onchange='frm1.submit();' onclick='return enviar()' <?=$tiene_permiso?>>
	<option value='A'  style='background-color:#00CC00'
	<?if ($clasificado=='A') echo " selected";?>>A</option>
	<option value='B' style='background-color:#99FF00'
	<?if ($clasificado=='B') echo " selected";?>>B</option>
	<option value='C' style='background-color:#FFFF66'
	<?if ($clasificado=='C') echo " selected";?>>C</option>
	<option value='D' style='background-color:#FF9900'
	<?if ($clasificado=='D') echo " selected";?>>D</option>
	<option value='E' style='background-color:#ff0000'
	<?if ($clasificado=='E') echo " selected";?>>E</option>
	</select>
	<font color='red'>ADVERTENCIA:</font><br>Al seleccionar la calificación, esta se guarda automaticamente.
	</td>
	<td><strong>CRITERIOS DE CALIFICACIÓN:</strong><br>
	     <table border=1 align='center'>
		 <tr><td width='27' align='center' bgcolor=#00CC00>A</td><td width='239'>El proveedor califica como Exelente</td></tr>
		 <tr><td align='center' bgcolor=#99FF00>B</td><td>El proveedor califica como Muy Bueno</td></tr>
		 <tr><td align='center' bgcolor=#FFFF66>C</td><td>El proveedor califica como Bueno</td></tr>
		 <tr><td align='center' bgcolor=#FF9900>D</td><td>El proveedor califica como Regular</td></tr>
		 <tr><td align='center' bgcolor=#ff0000>E</td><td>El proveedor califica como Malo</td></tr></table></td>
		 </td>
	</tr>
	  	<?
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
		$indicador_a=($dias_plazo_prov / $maximo_a);

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
		$indicador_b=($limite_cred_prov/$maximo_b);

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
		$indicador_g=($total_ord_compras_max_prov/$total_ord_compras_max);

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
		$indicador_h=($monto_proveedor/$monto_maximo);

		?>

</form>
	</table>

<br>
<table width="80%" cellspacing=1 cellpadding=2 align=center border="2">
		<tr>
		<td id="mo" colspan='9' align='center'>
		<strong>Evaluación de Proveedores</strong><br>
    	</td>
		<tr>

	<tr bgcolor="#CCCCFF">
		<td width="16%"><font size="1"> <strong> Calificación/Indicadores </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> A </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> B </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> C </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> D </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> E </strong> </font></td>
		<td	align="center" width="8%"><font size="2"> <strong> F </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> G </strong> </font></td>
		<td align="center" width="8%"><font size="2"> <strong> H </strong> </font></td>
	</tr>
	<?
	$sql="select * from calidad.const_eval_prov";
	$result_constante=$db->execute($sql) or die($db->errormsg());
	$a=0;
	$b=0;
	$c=0;
	$d=0;
	$e=0;
	$f=0;
	$g=0;
	$h=0;
	$a=$result_constante->fields["ka"] * $indicador_a;
	$b=$result_constante->fields["kb"] * $indicador_b;
	$c=$result_constante->fields["kc"] * $indicador_c;
	$d=$result_constante->fields["kd"] * $indicador_d;
	$e=$result_constante->fields["ke"] * $indicador_e;
	$f=(($result_constante->fields["kf"] * $indicador_f)/10);//divido por 10 debido a que el promedio de la valoracion subjetiva es de 1..10 y el indocador tiene que se de 0..1
	$g=$result_constante->fields["kg"] * $indicador_g;
	$h=$result_constante->fields["kh"] * $indicador_h;
	$calificacion=$a+$b+$c+$d+$e+$f+$g+$h;
	?>
	<tr>
		<?
		//debo darle color y una letra de acuerdo con el valor de la variable calificacion
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

		<td align="center" bgcolor=<?=$color_calif?>><font size="2" color="Black"> <strong> <?echo $letra_calif . " =  " . number_format($calificacion,2)?> </strong> </font></td>
		<td align="center"> <strong> <?=number_format($a,2)?> </strong> </td>
		<td align="center"> <strong> <?=number_format($b,2)?> </strong> </td>
		<td align="center"> <strong> <?=number_format($c,2)?> </strong> </td>
		<td align="center"> <strong> <?=$d?> </strong> </td>
		<td align="center"> <strong> <?=number_format($e,2)?> </strong> </td>
		<td	align="center"> <strong> <?=number_format($f,2)?> </strong> </td>
		<td align="center"> <strong> <?=number_format($g,2)?> </strong> </td>
		<td align="center"> <strong> <?=number_format($h,2)?> </strong> </td>
	</tr>



</table>

<table width="80%" cellspacing=1 cellpadding=2 align=center border="2">
	<tr>
		<td colspan='2' align='center' bgcolor="#CCCCFF">
		<strong>Referencias</strong><br>
    	</td>
    </tr>

	<tr>
		<td >
		<strong><font size="2">Letra A: <font color="#0000A0">Plazo de Pago</font></font></strong><br>
			<strong>
			Minimo Plazo: <font size="2" color="#ff0000"><?=$minimo_a;?></font> dias  -
			Maximo Plazo: <font size="2" color="#007800"><?=$maximo_a;?></font> dias
    		</strong>
    	</td>

    	<td >
		<strong><font size="2">Letra E: <font color="#0000A0">Incidentes de Inconformidad </font></font></strong><br>
			<strong>
			Cantidad de Incidentes: <font size="2" color="#ff0000"><?=$cantinc;?></font>
    		</strong>
		</td>
    </tr>
    <tr>
    	<td >
		<strong><font size="2">Letra B: <font color="#0000A0">Limite de Credito</font></font></strong><br>
			<strong>
			Minimo Limite: $<font size="2" color="#ff0000"><?echo number_format($minimo_b,2,',','.');?></font>  -
    		Maximo Limite: $<font size="2" color="#007800"><?echo number_format($maximo_b,2,',','.');?></font>
    		</strong>
    	</td>

    	<td >
		<strong><font size="2">Letra F: <font color="#0000A0">Valoración Subjetiva</font></font></strong><br>
			<strong>
			Promedio de la Valoración: <font size="2" color="#007800"><?echo number_format($indicador_f,2,',','.');?></font>
    		</strong>
    	</td>
    </tr>
    <tr>
    	<td >
		<strong><font size="2">Letra C: <font color="#0000A0">Cumplimento de Entregas en tiempo</font></font></strong><br>
			<strong>
			Total Cumplido en Termino: <font size="2" color="#ff0000"><?echo $total_Cumplido;?></font>  -
    	    Total Comprado: <font size="2" color="#007800"><?echo $total_comprado;?></font>
    		</strong>
    	</td>
    	<td >
		<strong><font size="2">Letra G: <font color="#0000A0">Cant. de Ord. de Compra Pagadas</font></font></strong><br>
			<strong>
			Minimo: <font size="2" color="#ff0000">0</font>
			Propio: <font size="2" color="#0000A0"><?=$total_ord_compras_max_prov?></font>
			Maximo: <font size="2" color="#007800"><?=$total_ord_compras_max;?></font>
    		</strong>
    	</td>
    </tr>
    <tr>
    	<td >
		<strong><font size="2">Letra D: <font color="#0000A0">Esta Certidicado ISO?</font></font></strong><br>
			<strong>
			Esta Certificado?: <font size="2" color="#007800"><?=$indicador_d_text?></font>
    		</strong>
    	</td>
    	<td >
		<strong><font size="2">Letra H: <font color="#0000A0">Monto Total Comprado</font></font></strong><br>
			<strong>
			Minimo: $<font size="2" color="#ff0000">0</font>
			Propio: $<font size="2" color="#0000A0"><?=number_format($monto_proveedor,2,',','.')?></font>
			Maximo: $<font size="2" color="#007800"><?echo number_format($monto_maximo,2,',','.');?></font>
    		</strong>
    	</td>
    </tr>

	<tr align="center">
    	<td colspan="2">
    	<?
		$link=encode_link("clasif_prove_mod_const.php",array());
		?>
  		<input type="button" name="boton_nuevo" value="Modificar Constantes" onclick="document.location='<?=$link?>'">
    	</td>
	</tr>

</table>
<br>

<!-- agrego la tabla que muestra la forma  de pago estandar -->

<?
$id_proveedor_consul=$proveedor;
//echo $id_proveedor_consul;
$sql1="select credito_proveedor.id_proveedor, credito_proveedor.id_plantilla_pagos, plantilla_pagos.id_plantilla_pagos, plantilla_pagos.descripcion, credito_proveedor.id_moneda, credito_proveedor.limite
		from general.credito_proveedor, compras.plantilla_pagos
		where (credito_proveedor.id_plantilla_pagos = plantilla_pagos.id_plantilla_pagos) AND (id_proveedor=$id_proveedor_consul) ";
$result_1=$db->execute($sql1) or die($db->errormsg());
?>

<TABLE class="bordes" align="center">
<TR>
<TD id="mo" colspan="2">Configuración del Límite de Crédito asignado al Proveedor</td>
</tr>

<TR id="ma">
<TD>Límite de crédito</td>
<TD align="right" bgcolor="Silver">
	<?if ($result_1->fields["id_moneda"] == 1){?>$<?}?>
	<?if ($result_1->fields["id_moneda"] == 2){?>U$S<?}?>
<INPUT readonly type="text" name="limite" value="<?=number_format($result_1->fields["limite"],2,'.','')?>" style="text-align:'right';">
</td>
</tr>

<TR id="ma">
<TD>Forma de Pago Estándar</td>
<TD align="right" bgcolor="Silver"><?=$result_1->fields["descripcion"]?></td>
</tr>

<tr align="center">
<td align="center" colspan="2">
<?
$link = encode_link("carga_prov.php",array("id_prov"=> $proveedor));
echo "<a href='$link' target='_blanck'> Modifica Datos Proveedor </a>";?>
</td>
</tr>

</table>

<br>

<table class="bordes" border=1 width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
<tr id="mo">
 <td width="100%" colspan="3"> <font size="2">Reportes de Incidentes </font></td>
</tr>
 <tr id="ma">
 <td width="15%"> <font size="1">FECHA </font></td>
 <td> <font size="1">USUARIO</font></td>
 <td> <font size="1">TITULO DEL REPORTE</font></td>
</tr>

<?
$id_proveedor_consul=$proveedor;
$sql1="select *
		from reportes_incidentes
		where (id_proveedor=$id_proveedor_consul)
		order by fecha ";
$result_1=$db->execute($sql1) or die($db->errormsg());

while(!$result_1->EOF){
            $link = encode_link("clasif_prove_incidentes.php",array("id_reportes_incidentes"=>$result_1->fields["id_reportes_incidentes"],"ver"=>"soloLectura","id_proveedor"=>$proveedor));
            tr_tag($link)?>


<tr <?tr_tag($link)?>
<td align="center"><?=fecha($result_1->fields["fecha"])?></td>
<td><?=$result_1->fields["usuario"]?></td>
<td><?=$result_1->fields["titulo"]?></td>
</tr>

<?
$result_1->MoveNext();
}
?>

</table>

<br>

<?//link nuevo temporal

$link = encode_link("clasif_prove_incidentes.php",array("id_proveedor"=>$proveedor,"alta"=>"alta"));
echo "<a href='$link' target='_blanck'> Nuevo </a>";

?>

<br>
<br>
	</td></tr>

	<tr><td align="left" id="ma_sf">
	<b><INPUT type="checkbox" onclick="if (this.checked) document.all.hist.style.display='block'; else document.all.hist.style.display='none';" <?=$tiene_permiso?>>
	Ver Historial de Cambios de Calificación Media</b>
	</td></tr>
	<tr><td>
	<div id="hist" style="display=none;">
	<?
	$sql="select id_historial,fecha,nombre,apellido,clasificado,comentario
                                    from historial_proveedor
                                    left join usuarios on login=usuario
                                    where id_proveedor=$proveedor";
	$res=sql($sql) or die;
	if (!$res->recordcount()){
	echo "<b>No hay Historial para este proveedor</b>\n";
	}
	else {
		echo "<ul>";
		while ($fil=$res->fetchrow()) {
			$fecha=substr($fil["fecha"],0,10);
			$hora=substr($fil["fecha"],11,8);
			$comentario = str_replace("'","",$fil["comentario"]);
            $id_historial=$fil["id_historial"];
			//echo "<li>".fecha($fecha)." $hora - <b>Usuario</b>: ".$fil["nombre"]." ".$fil["apellido"]
			echo "<li><b>Usuario</b>: ".$fil["nombre"]." ".$fil["apellido"]
				." - <b>Paso a tipo</b>: ".$fil["clasificado"]. "</li>\n";
		}
	}
	?>
	</ul>
	</DIV>
	</td></tr>
	<tr><td align="left" id="ma_sf">
	<b><INPUT type="checkbox" onclick="if (this.checked) document.all.diag.style.display='block'; else document.all.diag.style.display='none';" <?=$tiene_permiso?>>
	Ver Estado de Calificaciones Corrientes para este Proveedor</b>
	</td></tr>
	<tr><td>
	<div id="diag" style="display=none;">
	<TABLE width="100%">

	<TR>
	<TD align="center">
	<?
	//$link=encode_link("../calidad/gen_grafico_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"1","media"=>"$clasificado"));
	$link=encode_link("../calidad/gen_grafico_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"0","media"=>"$clasificado"));
	//$link2=encode_link("../calidad/lista_calificacion_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"1"));
	$link2=encode_link("../calidad/lista_calificacion_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"0"));
	?>
	<IMG src="<?=$link?>" onclick="window.open('<?=$link2?>','','top=80, left=150, width=500px, height=300px, scrollbars=1, status=0');"></TD>
	</TR>

	<?/*
	<TR>
	<TD align="center">
	<?$link=encode_link("../calidad/gen_grafico_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"1","media"=>"$clasificado"));
	$link2=encode_link("../calidad/lista_calificacion_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"1"));
	?>
	<IMG src="<?=$link?>" onclick="window.open('<?=$link2?>','','top=80, left=150, width=500px, height=300px, scrollbars=1, status=0');"></TD>
	</TR>
	<TR>
	<TD align="center">

	<?
	$link=encode_link("../calidad/gen_grafico_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"2","media"=>"$clasificado"));
	$link2=encode_link("../calidad/lista_calificacion_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"2"));
	?>
	<IMG src="<?=$link?>" onclick="window.open('<?=$link2?>','','top=80, left=150, width=500px, height=300px, scrollbars=1, status=0');"></TD>
	</TR>
	<TR>
	<TD align="center">
	<?$link=encode_link("../calidad/gen_grafico_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"3","media"=>"$clasificado"));
	$link2=encode_link("../calidad/lista_calificacion_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"3"));
	?>
	<IMG src="<?=$link?>" onclick="window.open('<?=$link2?>','','top=100, left=200, width=400px, height=240px, scrollbars=1, status=0');"></TD>
	</TR>
	<TR>
	<TD align="center">
	* Haz Click sobre el gráfico para ver todas las calificaciones de este proveedor emitidas desde el lugar correspondiente.
	</TR>
	*/?>

	</TABLE>
	</DIV>
	</td></tr>
	</table><br>

<?if (isset($_GET['proveedor']) || $_POST['boton_cerrar']==1) {?>

<div align="center">
<input type="button" name="Cerrar"  value="Cerrar" onclick="window.close()">
</div>
<?}?>
<SCRIPT>
function enviar(){
	/*if(document.all.comentario.value == "") {
		alert("Debe especificar un comentario para cambiar la calificación actual.");
		return false;
	}*/
	//document.all.cmd1.value='clasificar';
	return true;
}
</SCRIPT>