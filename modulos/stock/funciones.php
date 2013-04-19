<?php
/*
$Author: fernando $
$Revision: 1.79 $
$Date: 2006/12/19 22:02:52 $
*/
require_once("../../config.php");



//orrijola, valentino, mascioni, juan,benitez


function enviar_mail_produccion_rma($nro_rma,$id_licitacion,$id_proveedor_rma,$id_producto,$cantidad){
	global $_ses_user;

	$sql=" select razon_social from proveedor where id_proveedor=$id_proveedor_rma";
	$res=sql($sql) or fin_pagina();
	$nombre_proveedor=$res->fields["razon_social"];

	$sql="select descripcion from general.producto_especifico where id_prod_esp=$id_producto";
	$res=sql($sql) or fin_pagina();
	$producto=$res->fields["descripcion"] ;

	$para="orrijola@coradir.com.ar,juanmanuel@coradir.com.ar";
	$para.=",mascioni@coradir.com.ar,valentino@coradir.com.ar";
	$para.=",benitez@coradir.com.ar,marco@pcpower.com.ar";
	$asunto=" Ingreso  RMA Nro $nro_rma generado por descuento desde Stock de producción para la licitación Nº $id_licitacion";
	$contenido="Se genero el RMA  Nro $nro_rma  para el producto  $producto con la  cantidad: $cantidad \n";
	$contenido.="Perteneciente a la  Licitación Nº $id_licitacion  \n";
	$contenido.="Con el proveedor RMA $nombre_proveedor \n";
	$contenido.="Usuario: ".$_ses_user["name"]."\n";
	$contenido.="Fecha y Hora:".date("d/m/Y H:i:s")."\n";

	enviar_mail($para,$asunto,$contenido,0,0,0,0,0);

}


/************************************************
Parte de RMA:
-Funcion que incrementa el deposito destino,
correspondiente a los productos del RMA.
*************************************************/
function incrementar_stock_rma($id_prod,$cantidad,$id_prod_ant="",$comentario,$estado_rma,$cod,$des_cod,$id_prov,$id_info_rma="",$nro_orden="",$nuevo_coment="",$id_nota_credito="null",$ubicacion="",$caso,$defecto,$tipo_log="",$id_licitacion=0,$id_movimiento_material=0,$san_luis=0)
{
	global $_ses_user,$db;

	$db->StartTrans();
	$fecha1=date("Y-m-d");
	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];
	//print_r($_ses_user);
	($san_luis) ? $nombre_deposito = "RMA-Produccion-San Luis" : $nombre_deposito="RMA";
	$sel="select id_deposito from general.depositos where nombre='$nombre_deposito' ";
	$eje_se=sql($sel,"No se pudo recuperar el id_tipo_movimiento") or fin_pagina();
	$id_dep=$eje_se->fields['id_deposito'];
	if($estado_rma=="")
	{$estado_rma=1;}

	if($id_prov=="")
	$id_prov="null";
	if($nro_orden=="")
	$nro_orden="null";
	/*if($ubicacion!="")
	{
	$sql="update en_stock set ubicacion='$ubicacion'
	where id_deposito=$id_dep and id_prod_esp=$id_prod";
	sql($sql,"No se pudo actualizae la ubicacion") or fin_pagina();
	}*/
	if($id_info_rma=="")
	{
		$sel="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Ingreso RMA' ";
		$eje_sel=sql($sel,"No se pudo recuperar el id_tipo_movimiento") or fin_pagina();
		$id_tip_mov=$eje_sel->fields['id_tipo_movimiento'];
		agregar_stock($id_prod,$cantidad,$id_dep,$comentario,$id_tip_mov,"disponible","","","","","");
		//$db->StartTrans();
		$sql=" select id_en_stock from en_stock where id_prod_esp=$id_prod and id_deposito=$id_dep";
		$res=sql($sql,"<br>Error al traer el id del stock (agregar_stock)<br>") or fin_pagina();
		$id_en_stock=$res->fields['id_en_stock'];
		$id_nota_credito="null";
		$sql="select nextval('info_rma_id_info_rma_seq') as id_info_rma ";
		$res=sql($sql,"<br>Error al traer la la secuencia de info_rma (agregar_stock)<br>") or fin_pagina();
		$id_info_rma=$res->fields["id_info_rma"];
		$campos= "id_info_rma,desc_void,id_proveedor,nro_orden,cantidad,id_estado_rma,id_en_stock,id_nota_credito,fecha_hist,nrocaso,defecto_parte,fecha_creacion";
		if($ubicacion!="") {
			$campos.=",id_ubicacion_rma";
		}
		$values= "$id_info_rma,'$des_cod',$id_prov,$nro_orden,$cantidad,$estado_rma,$id_en_stock,$id_nota_credito,'$fecha','$caso','$defecto','$fecha'";
		if($ubicacion!="") {
			$values.=",$ubicacion";
		}
		if ($id_licitacion) {
			$campos.=",id_licitacion";
			$values.=",$id_licitacion";
		}

		if ($id_movimiento_material) {
			$campos.=",id_movimiento_material";
			$values.=",$id_movimiento_material";
		}
		$sql=" insert into stock.info_rma ($campos) values ($values)";
		sql($sql,"<br>Error al insertar en la tabla del stock(agregar_stock)<br>") or fin_pagina();



		if($tipo_log=="")
		$tipo_log="Creacion";
		$campos= "id_info_rma,void";
		$contar=0;
		while($contar<$cantidad)
		{
			$void1=$cod[$contar];
			$values= "$id_info_rma,'$void1'";
			$sql_void=" insert into void_rma ($campos) values ($values)";
			sql($sql_void,"<br>Error al insertar en la tabla de void_rma<br>") or fin_pagina();
			$contar++;
		}
		$estado_rma=1;
		//$db->CompleteTrans();
	}//finif($id_info_rma=="")

	else
	{
		if($id_prod_ant==$id_prod)
		{
			////////////////////////////////Update de Info_Rma///////////////////////////////////

			$updat="update stock.info_rma set id_proveedor=$id_prov,id_estado_rma=$estado_rma,desc_void='$des_cod',void='$cod',nro_orden=$nro_orden,nrocaso='$caso',defecto_parte='$defecto',cantidad=$cantidad";
			if($ubicacion!="")
			{
				$updat.=",id_ubicacion_rma=$ubicacion";
			}

			if($estado_rma==4)
			{
				$se_id_in_r="select id_estado_rma from stock.info_rma where id_info_rma=$id_info_rma";
				$inf_rma=sql($se_id_in_r,"No se puede seleccionar el estado del rma") or fin_pagina();
				$id_est=$inf_rma->fields['id_estado_rma'];
				$tipo_log="Entrega";

				if($id_est!=$estado_rma)
				{
					$updat.=" ,fecha_hist='$fecha'";
				}
			}
			else
			$tipo_log="Modificacion";

			$updat.=" where id_info_rma=$id_info_rma";
			sql($updat,"No se pudo guardar los datos de info_rma") or fin_pagina();
		}//////Fin if($id_prod_ant==$id_prod)

		else//si hubo cambio de producto
		{
			$sel="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Descuento de stock RMA por cambio de producto' ";
			$eje_sel=sql($sel,"No se pudo recuperar el id_tipo_movimiento") or fin_pagina();
			$id_tip_mov=$eje_sel->fields['id_tipo_movimiento'];
			descontar_stock_disponible($id_prod_ant,$cantidad,$id_dep,$id_tip_mov,"Descuento por cambio de producto en el RMA Nº $id_info_rma");
			$sel="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Ingreso RMA' ";
			$eje_sel=sql($sel,"No se pudo recuperar el id_tipo_movimiento") or fin_pagina();
			$id_tip_mov=$eje_sel->fields['id_tipo_movimiento'];
			$comentario="Se cambió el producto para el RMA Nº $id_info_rma";
			agregar_stock($id_prod,$cantidad,$id_dep,$comentario,$id_tip_mov,"disponible","","","","","");

			$sql=" select id_en_stock from en_stock where id_prod_esp=$id_prod and id_deposito=$id_dep";
			$res=sql($sql,"<br>Error al traer el id del stock (agregar_stock)<br>") or fin_pagina();
			$id_en_stock=$res->fields['id_en_stock'];

			$tipo_log="Cambio producto $id_prod_ant por $id_prod";
			$campos= "id_info_rma,id_estado_rma,fecha,usuario_log,tipo_log,comentario";
			$values= "$id_info_rma,$estado_rma,'$fecha','$usuario','$tipo_log','Cambio del producto'";
			$sql=" insert into stock.log_info_rma ($campos) values ($values)";
			sql($sql,"<br>Error al insertar en la tabla del comentario(agregar_stock)<br>") or fin_pagina();

			$updat="update stock.info_rma set id_proveedor=$id_prov,id_en_stock=$id_en_stock,id_estado_rma=$estado_rma,desc_void='$des_cod',void='$cod',nro_orden=$nro_orden,nrocaso='$caso',defecto_parte='$defecto'";

			if($ubicacion!="")
			{
				$updat.=",id_ubicacion_rma=$ubicacion";
			}

			if($estado_rma==4)
			{
				$se_id_in_r="select id_estado_rma from stock.info_rma where id_info_rma=$id_info_rma";
				$inf_rma=sql($se_id_in_r,"No se puede seleccionar el estado del rma") or fin_pagina();
				$id_est=$inf_rma->fields['id_estado_rma'];
				$tipo_log="Entrega";

				if($id_est!=$estado_rma)
				{
					$updat.=" ,fecha_hist='$fecha'";
				}
			}
			else
			$tipo_log="Modificacion";

			$updat.=" where id_info_rma=$id_info_rma";
			sql($updat,"No se pudo guardar los datos de info_rma") or fin_pagina();

			//traemos los nombres y precios del nuevo y el anterior producto de este RMA
			$query="select descripcion,precio_stock from general.producto_especifico where id_prod_esp=$id_prod_ant";
			$prod_orig=sql($query,"<br>Error al traer datos del producto original del RMA<br>") or fin_pagina();
			$nombre_prod_original=$prod_orig->fields["descripcion"];
			$precio_producto_original=$prod_orig->fields["precio_stock"];

			$query="select descripcion,precio_stock from general.producto_especifico where id_prod_esp=$id_prod";
			$prod_nuevo=sql($query,"<br>Error al traer datos del producto nuevo del RMA<br>") or fin_pagina();
			$nombre_nuevo_producto=$prod_nuevo->fields["descripcion"];
			$precio_nuevo_producto=$prod_nuevo->fields["precio_stock"];

			//si el precio del nuevo producto es distinto del precio del producto original
			if($precio_nuevo_producto!=$precio_producto_original)
			{
				$monto_original=$precio_producto_original*$cantidad;
				$monto_nuevo=$precio_nuevo_producto*$cantidad;
				$variacion=$monto_nuevo-$monto_original;

				//enviamos mail a noelia avisando del cambio de producto
				$para="noelia@coradir.com.ar";
				$asunto="Para el RMA Nº $id_info_rma se cambió el producto cargado anteriormente";
				$texto="AVISO: Para el RMA Nº $id_info_rma se cambió el producto: $nombre_prod_original\n";
				$texto.="por el producto: $nombre_nuevo_producto.\n\n";
				$texto.="El monto del RMA con el producto original ($nombre_prod_original) era: U\$S ".formato_money($monto_original).".\n";
				$texto.="El monto del RMA con el nuevo producto ($nombre_nuevo_producto) es: U\$S ".formato_money($monto_nuevo)."\n\n";
				$texto.="Esto va a provocar una variación de U\$S ".formato_money($variacion)." en el balance, en el stock de RMA.\n\n";
				$texto.="Usuario que realizó el cambio: $usuario - Fecha: ".date("d/m/Y H:i:s")."\n\n\n";
				//echo $texto;die;
				enviar_mail($para,$asunto,$texto,'','','','','');

			}//de if($precio_nuevo_producto!=$precio_producto_original)

		}///////Fin del Else de if($id_prod_ant==$id_prod)

		$del="DELETE from void_rma where id_info_rma=$id_info_rma";
		sql($del,"no se pudo eliminar los void")or fin_pagina();
		$contar=0;
		$campos= "id_info_rma,void";
		while($contar<$cantidad)
		{
			$void1=$cod[$contar];
			if($void1!="")
			{
				$values= "$id_info_rma,'$void1'";
				$sql_void=" insert into void_rma ($campos) values ($values)";
				sql($sql_void,"<br>Error al insertar en la tabla de void_rma<br>") or fin_pagina();
			}
			$contar++;
		}
	}

	if($nuevo_coment!="")
	{
		$campos= "id_info_rma,usuario,fecha,texto";
		$values= "$id_info_rma,'$usuario','$fecha1','$nuevo_coment'";
		$sql=" insert into stock.comentario_rma ($campos) values ($values)";
		sql($sql,"<br>Error al insertar en la tabla del comentario(agregar_stock)<br>") or fin_pagina();
	}
	$campos= "id_info_rma,id_estado_rma,fecha,usuario_log,tipo_log";
	$values= "$id_info_rma,$estado_rma,'$fecha','$usuario','$tipo_log'";
	$sql=" insert into stock.log_info_rma ($campos) values ($values)";
	sql($sql,"<br>Error al insertar en la tabla del comentario(agregar_stock)<br>") or fin_pagina();

	$db->CompleteTrans();

	return $id_info_rma;
}//de function incrementar_stock_rma($id_prod,$cantidad,$id_prod_ant="",$comentario,$estado_rma,$cod,$des_cod,$id_prov,$id_info_rma="",$nro_orden="",$nuevo_coment="",$id_nota_credito="null",$ubicacion,$caso,$defecto)

//////////////////////////////////////////////////////////
//               FUNCIONES NUEVAS DE STOCK
//////////////////////////////////////////////////////////

/************************************************************************************
Funcion que agrega el producto pasado como parametro al deposito pasado como
parametro. Dependiendo del parametro @a_stock, el ingreso sera a stock disponible
o a stock reservado o a stock a confirmar.

@id_prod_esp            El id del producto especifico
@cantidad               La cantidad a agregar en el stock
@id_deposito            El id del deposito (stock) en donde se agregara el producto
@comentario             El comentario que se va a guardar en el log del movimiento
de stock
@id_tipo_movimiento     El tipo de movimiento que se registrara en el log (por ej:
Ingreso por Orden de Compra,Ingreso manual de stock,etc.)
El valor por default (5) es: Ingreso manual de stock.
@a_stock 			    Determina si el ingreso de los productos al stock es a
stock disponible, a stock reservado o a stock a confirmar.
Las opciones posibles para este parametro son:
"disponible", que es por default, "reservado" y "a confirmar".
@id_tipo_reserva        El id del tipo de reserva que se realizará
(Licitacion, Presupuesto, CAS).
Solo se toma en cuenta si el parametro @a_stock=="reservado".
@id_fila			    El id de la fila que realiza la reserva del producto.
Solo se toma en cuenta si el parametro @a_stock=="reservado"
o @a_stock=="a confirmar".
@id_detalle_movimiento  El id del detalle de movimiento que realiza la reserva.
Solo se toma en cuenta si el parametro @a_stock=="reservado".
@id_licitacion          La licitacion para la cual quedan reservados los productos.
Solo se toma en cuenta si el parametro @a_stock=="reservado"
o @a_stock=="a confirmar".
@nrocaso                El caso de Servicio Tecnico para la cual quedan reservados
los productos. Solo se toma en cuenta si el parametro
@a_stock=="reservado" o @a_stock=="a confirmar".
@id_tipo_reserva        El id del tipo de detalle a confirmar.
Solo se toma en cuenta si el parametro @a_stock=="a confirmar".
**************************************************************************************/
function agregar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento=5,$a_stock="disponible",$id_tipo_reserva="",$id_fila="",$id_detalle_movimiento="",$id_licitacion="",$nrocaso="",$id_tipo_detalle_a_confirmar="")
{
	global $_ses_user,$db;

	$db->StartTrans();

	$sql=" select id_en_stock from stock.en_stock where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito";
	$res=sql($sql,"<br>Error al traer el id del stock (agregar_stock)<br>") or fin_pagina();
	$cantidad_en_stock=$res->recordcount();
	$cantidad_ingresar=$cantidad;
	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];


	if ($cantidad_en_stock)
	{
		$id_en_stock=$res->fields["id_en_stock"];
		if($a_stock=="disponible")
		{
			$sql="update stock.en_stock set cant_disp=cant_disp + $cantidad_ingresar
	                                 where id_en_stock=$id_en_stock ";
			sql($sql,"<br>Error al agregar al stock disponible(agregar_stock)<br>") or fin_pagina();
		}
		else if($a_stock=="reservado")
		{
			$sql="update stock.en_stock set cant_reservada=cant_reservada + $cantidad_ingresar
	                                 where id_en_stock=$id_en_stock ";
			sql($sql,"<br>Error al agregar al stock reservado(agregar_stock)<br>") or fin_pagina();
		}
		else if($a_stock=="a confirmar")
		{
			$sql="update stock.en_stock set cant_a_confirmar=cant_a_confirmar + $cantidad_ingresar
	                                 where id_en_stock=$id_en_stock ";
			sql($sql,"<br>Error al agregar al stock reservado(agregar_stock)<br>") or fin_pagina();
		}
		else
		die("Error al actualizar stock: No se pudo determinar el ingreso de stock (disponible, reservado o a confirmar?). Contactese con la Division Software");

	}//de if ($cantidad_en_stock)
	else
	{
		$sql="select nextval('en_stock_id_en_stock_seq') as id_en_stock ";
		$res=sql($sql,"<br>Error al traer la la secuencia de stock (agregar_stock)<br>") or fin_pagina();
		$id_en_stock=$res->fields["id_en_stock"];

		if($a_stock=="disponible")
		{
			$campos= " id_en_stock,id_deposito,id_prod_esp,cant_disp";
			$values= " $id_en_stock,$id_deposito,$id_prod_esp,$cantidad_ingresar";
			$sql=" insert into stock.en_stock ($campos) values ($values)";
			sql($sql,"<br>Error al insertar en la tabla del stock(agregar_stock)<br>") or fin_pagina();
		}
		else if($a_stock=="reservado")
		{
			$campos= " id_en_stock,id_deposito,id_prod_esp,cant_reservada";
			$values= " $id_en_stock,$id_deposito,$id_prod_esp,$cantidad_ingresar";
			$sql=" insert into stock.en_stock ($campos) values ($values)";
			sql($sql,"<br>Error al insertar en la tabla del stock(agregar_stock)<br>") or fin_pagina();
		}
		else if($a_stock=="a confirmar")
		{
			$campos= " id_en_stock,id_deposito,id_prod_esp,cant_a_confirmar";
			$values= " $id_en_stock,$id_deposito,$id_prod_esp,$cantidad_ingresar";
			$sql=" insert into stock.en_stock ($campos) values ($values)";
			sql($sql,"<br>Error al insertar en la tabla del stock(agregar_stock)<br>") or fin_pagina();
		}
		else
		die("Error al insertar stock: No se pudo determinar el ingreso de stock (disponible o reservado?). Contactese con la Division Software");

	}//del else de if ($cantidad_en_stock)

	//ingresamos el log del movimiento del stock
	$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
	$values=" $id_en_stock,$id_tipo_movimiento,$cantidad_ingresar,'$fecha','$usuario','$comentario'";
	$sql="insert into stock.log_movimientos_stock ($campos) values ($values)";
	sql($sql,"<br>Error al insetar en el log de movimientos de stock (agregar_stock)<br>") or fin_pagina();

	//si los productos entran a stock reservado, agregamos la entrada correspondiente, en detalle_reserva
	if($a_stock=="reservado")
	{
		//revisamos si esta el id de detalle reserva ya cargado.
		if($id_fila!="")
		$query="select id_detalle_reserva from stock.detalle_reserva where id_fila=$id_fila and id_en_stock=$id_en_stock";
		elseif($id_detalle_movimiento!="")
		$query="select id_detalle_reserva from stock.detalle_reserva where id_detalle_movimiento=$id_detalle_movimiento and id_en_stock=$id_en_stock";
		else //esto no deberia ocurrir...pero si ocurre no dejamos seguir la ejecucion
		die("Error: No se encontro id de fila o id de movimiento al cual asociarle la reserva. Comuniquese con la division software.");
		$det_res=sql($query,"<br>Error al traer detalle de la reserva (agregar_stock)") or fin_pagina();

		//si no hay ningun detalle de reserva, lo insertamos, sino lo acualizamos
		if($det_res->fields["id_detalle_reserva"]=="")
		{
			$campos=" id_tipo_reserva,cantidad_reservada,fecha_reserva,usuario_reserva,id_en_stock";
			$values= "$id_tipo_reserva,$cantidad_ingresar,'$fecha','$usuario',$id_en_stock";
			//si es una reserva para una fila, agregamos dicho campo a la tabla de detalle_reserva
			if($id_fila!="")
			{
				$campos.=",id_fila";
				$values.=",$id_fila";
			}
			//si es una reserva para un movimiento de material, agregamos el id de detalle de movimiento a la tabla
			else if($id_detalle_movimiento!="")
			{
				$campos.=",id_detalle_movimiento";
				$values.=",$id_detalle_movimiento";
			}

			if($id_licitacion!="")
			{
				$campos.=",id_licitacion";
				$values.=",$id_licitacion";
			}
			elseif($nrocaso!="")
			{
				$campos.=",nrocaso";
				$values.=",'$nrocaso'";
			}
			$query = " insert into stock.detalle_reserva ($campos) values ($values)";

		}//de if($det_res->fields["id_detalle_reserva"]=="")
		else
		{
			if($id_fila!="")
			$query="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada+$cantidad_ingresar
	              where id_fila=$id_fila";
			elseif($id_detalle_movimiento!="")
			$query="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada+$cantidad_ingresar
	              where id_detalle_movimiento=$id_detalle_movimiento";
		}//del else de if($det_res->fields["id_detalle_reserva"]=="")

		sql($query,"<br>Error al insertar/actualizar detalle de reserva(agregar_stock)<br>") or fin_pagina();
	}//de if($a_stock=="reservado")
	else if($a_stock=="a confirmar")//si los productos entran a stock a confirmar, agregamos la entrada correspondiente, en detalle_a_confirmar
	{
		//revisamos si ya existe un detalle a confirmar para la fila
		if($id_fila!="")
		$query="select id_detalle_a_confirmar from stock.detalle_a_confirmar where id_fila=$id_fila and id_en_stock=$id_en_stock";
		else
		die("Error: No se encontro id de fila al cual asociarle el detalle a confirmar. Comuniquese con la division software.");
		$det_ac=sql($query,"<br>Error al traer detalle a confirmar (agregar_stock)") or fin_pagina();

		if($det_ac->fields["id_detalle_a_confirmar"]=="")
		{

			$campos="id_en_stock,id_tipo_detalle_a_confirmar,cant_a_confirmar,usuario_a_confirmar,fecha_a_confirmar";
			$values="$id_en_stock,$id_tipo_detalle_a_confirmar,$cantidad_ingresar,'$usuario','$fecha'";

			if($id_fila)
			{
				$campos.=",id_fila";
				$values.=",$id_fila";
			}

			if($id_licitacion!="")
			{
				$campos.=",id_licitacion";
				$values.=",$id_licitacion";
			}
			elseif($nrocaso!="")
			{
				$campos.=",nrocaso";
				$values.=",'$nrocaso'";
			}

			$query = " insert into stock.detalle_a_confirmar ($campos) values ($values)";
		}//de if($det_ac->fields["id_detalle_a_confirmar"]=="")
		else
		{
			$query="update stock.detalle_a_confirmar set cant_a_confirmar=cant_a_confirmar+$cantidad_ingresar
	              where id_fila=$id_fila";
		}//del else de if($det_ac->fields["id_detalle_a_confirmar"]=="")

		sql($query,"<br>Error al insertar/actualizar detalle a confirmar(agregar_stock)<br>") or fin_pagina();

	}//de else if($a_stock=="a confirmar")

	$db->CompleteTrans();

}//de function agregar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento=5,$a_stock="disponible",$id_tipo_reserva="",$id_fila="",$id_detalle_movimiento="",$id_licitacion="",$nrocaso="",$id_tipo_detalle_a_confirmar="")


/******************************************************************************************
Funcion que descuenta del stock disponible la cantidad del producto pasado como parametro
@id_prod_esp            El producto que se va a descontar
@cantidad               La cantidad a descontar
@id_deposito            El deposito donde se va a descontar el producto
@id_tipo_movimiento     El tipo de movimiento que se realizara
@comentario             El comentario que se agrega en el log para describir el movimiento
*******************************************************************************************/
function descontar_stock_disponible($id_prod_esp,$cantidad,$id_deposito,$id_tipo_movimiento=18,$comentario)
{
	global $_ses_user,$db;

	$db->StartTrans();

	//seleccionamos el id_en_stock del producto-deposito correspondiente
	$query="select id_en_stock,cant_disp,producto_especifico.descripcion,depositos.nombre
            from stock.en_stock
            join general.producto_especifico using(id_prod_esp)
            join general.depositos using(id_deposito)
            where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito";
	$en_stock=sql($query,"<br>Error al traer el id del stock<br>") or fin_pagina();
	$id_en_stock=$en_stock->fields["id_en_stock"];
	$cant_disp_actual=$en_stock->fields["cant_disp"];
	$descrip_prod=$en_stock->fields["descripcion"];
	$deposito=$en_stock->fields["nombre"];

	//controlamos que la cantidad actual disponible sea mayor o igual a la que se quiere descontar
	//para evitar que se descuente algo que no existe. Si no hay cantidad suficiente damos error.
	if($cantidad<=$cant_disp_actual)
	{
		$query="update stock.en_stock set cant_disp=cant_disp-$cantidad where id_en_stock=$id_en_stock";
		sql($query,"<br>Error al descontar de stock disponible (descontar_stock_disponible)<br>") or fin_pagina();


		//registramos el descuento en el log de movimientos de stock
		$usuario=$_ses_user["name"];
		$fecha=date("Y-m-d H:i:s",mktime());
		$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
		$values=" $id_en_stock,$id_tipo_movimiento,$cantidad,'$fecha','$usuario','$comentario'";
		$sql="insert into log_movimientos_stock ($campos) values ($values)";
		sql($sql,"<br>Error al insetar en el log de movimientos de stock (agregar_stock)<br>") or fin_pagina();
	}//de if($cantidad<=$cant_disp_actual)
	else//no hay cantidad suficiente para descontar
	die("Error: No hay suficiente cantidad de stock disponible para: '$descrip_prod' en el Stock $deposito.<br> Posiblemente, antes de que usted guardara los cambios alguien utilizó los productos que figuraban como disponibles.<br>Revise las cantidades actuales y vuelva a intentarlo.");

	$db->CompleteTrans();
}//de function descontar_stock_disponible($id_prod_esp,$cantidad,$id_deposito,$id_tipo_movimiento=18,$comentario)

/******************************************************************************************
Funcion que realiza la reserva del producto pasado como parametro, en el deposito pasado
como parametro.
@id_prod_esp            El producto que se va a reservar
@cantidad               La cantidad a reservar
@id_deposito            El deposito donde se va a reservar el producto
@comentario             El comentario que se agrega en el log para describir el movimiento
@id_tipo_movimiento     El tipo de movimiento que se realizara
@id_tipo_reserva        El tipo de reserva que se va a realizar
@id_fila                El id de la fila que generó la reserva
@id_detalle_movimiento  El id de movimiento de material que generó la reserva
*******************************************************************************************/
function reservar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_tipo_reserva=6,$id_fila="",$id_detalle_movimiento="", $id_licitacion="")
{
	global $_ses_user,$db;

	$db->StartTrans();

	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];

	$sql=" select id_en_stock,cant_disp,producto_especifico.descripcion,depositos.nombre
               from en_stock
               join general.producto_especifico using(id_prod_esp)
      		   join general.depositos using(id_deposito)
               where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito";
	$res=sql($sql,"<br>Error al traer el id de la tabla de stock(reservar_stock)<br>") or fin_pagina();

	$id_en_stock=$res->fields["id_en_stock"];
	$cant_disp_actual=$res->fields["cant_disp"];
	$descrip_prod=$res->fields["descripcion"];
	$deposito=$res->fields["nombre"];

	//controlamos que la cantidad actual disponible sea mayor o igual a la que se quiere descontar
	//para evitar que se descuente algo que no existe. Si no hay cantidad suficiente damos error.
	if($cantidad<=$cant_disp_actual)
	{
		$sql="update en_stock set cant_disp=cant_disp - $cantidad,
	                                  cant_reservada=cant_reservada + $cantidad
	                                  where id_en_stock=$id_en_stock ";
		sql($sql,"<br>Error al realizar la reserva en el stock(reservar_stock)<br>") or fin_pagina();

		//log lo ingreso como tipo de ingreso manual
		$sql="select nextval('stock.log_movimientos_stock_id_log_mov_stock_seq') as id_log_mov_stock";
		$res=sql($sql,"<br>Error al traer la secuencia del log de movimiento de stock (reservar_stock)<br>") or fin_pagina($sql);
		$id_log_mov_stock=$res->fields["id_log_mov_stock"];


		$campos=" id_log_mov_stock,id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario ";
		$values=" $id_log_mov_stock,$id_en_stock,$id_tipo_movimiento,$cantidad,'$fecha','$usuario','$comentario' ";
		$sql="insert into log_movimientos_stock ($campos) values ($values)";
		sql($sql,"<br>Error al insertar en el log de movimientos de stock (reservar_stock)<br>") or fin_pagina();

		//ingreso los datos de la reserva

		$sql="select nextval('stock.detalle_reserva_id_detalle_reserva_seq') as id_detalle_reserva";
		$res=sql($sql,"<br>Error al traer la secuencia del detalle de reserva(reservar_stock)<br>") or fin_pagina($sql);
		$id_detalle_reserva=$res->fields["id_detalle_reserva"];

		$campos=" id_detalle_reserva,id_tipo_reserva,cantidad_reservada,fecha_reserva,usuario_reserva,id_en_stock";
		$values= "$id_detalle_reserva,$id_tipo_reserva,$cantidad,'$fecha','$usuario',$id_en_stock";
		//si es una reserva para una fila, agregamos dicho campo a la tabla de detalle_reserva
		if($id_fila!="")
		{
			$campos.=",id_fila";
			$values.=",$id_fila";
		}
		//si es una reserva para un movimiento de material, agregamos el id de detalle de movimiento a la tabla
		else if($id_detalle_movimiento!="")
		{
			$campos.=",id_detalle_movimiento";
			$values.=",$id_detalle_movimiento";
		}
		//si es una reserva por un descuento manual, agregamos el id del log de movimiento para tener la referencia correcta
		//al descontar la reserva o rechazarla
		else if($id_log_mov_stock!="")
		{
			$campos.=",id_log_mov_stock";
			$values.=",$id_log_mov_stock";
		}

		if($id_licitacion!=""){
			$campos.=",id_licitacion";
			$values.=",$id_licitacion";
		}
		$sql = " insert into detalle_reserva ($campos) values ($values)";
		sql($sql,"<br>Error al insertar el detalle de la reserva (reservar_stock)<br>") or fin_pagina();
	}//de if($cantidad<=$cant_disp_actual)
	else//no hay cantidad suficiente para descontar
	die("Error: No hay suficiente cantidad de stock disponible para: '$descrip_prod' en el Stock $deposito.<br> Posiblemente, antes de que usted guardara los cambios alguien utilizó los productos que figuraban como disponibles.<br>Revise las cantidades actuales y vuelva a intentarlo.");

	$db->CompleteTrans();
} //de function reservar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_tipo_reserva=6,$id_fila="",$id_detalle_movimiento="")


/******************************************************************************************
Funcion que descuenta la reserva hecha para el producto en el deposito pasado
como parametro. Lo que hace es descontar la cantidad pasada como parametro de las cantidad
reservada para ese producto en ese stock. Esto significa que los productos salen,
efectivamente, del stock.

@id_prod_esp            El producto del cual se tiene que descontar la reserca
@cantidad               La cantidad a descontar
@id_deposito            El deposito de donde se va a descontar la reserva del producto
@comentario             El comentario que se agrega en el log para describir el movimiento
@id_tipo_movimiento     El tipo de movimiento que se realizara
@id_fila                El id de la fila que generó la reserva
@id_detalle_movimiento  El id de movimiento de material que generó la reserva
@id_log_mov_stock       El id del log de movimiento de stock, que se guardo cuando se hizo
el descuento manual, que quedo en estado pendiente
*******************************************************************************************/
function descontar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila="",$id_detalle_movimiento="",$id_log_mov_stock="")
{
	global $_ses_user,$db;

	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];

	$db->StartTrans();

	//Traemos los datos del detalle de reserva generado por la fila, o el detalle de movimiento de material,
	//o en forma manual (identificado por el id de log de movimiento de stock). Esto para el deposito y el producto
	//pasados como parametro
	$sql="select id_en_stock,cantidad_reservada,id_detalle_reserva,cantidad_reservada
              from stock.en_stock
              join detalle_reserva using(id_en_stock)
              where id_deposito=$id_deposito and id_prod_esp=$id_prod_esp";

	if($id_fila!="")
	{
		$sql.=" and id_fila=$id_fila";
	}
	else if($id_detalle_movimiento!="")
	{
		$sql.=" and id_detalle_movimiento=$id_detalle_movimiento";
	}
	else if($id_log_mov_stock!="")
	{
		$sql.=" and id_log_mov_stock=$id_log_mov_stock";
	}

	$res=sql($sql,"<br>Error al traer los datos del detalle de la reserva (descontar_reserva)<br>") or fin_pagina($sql);

	$id_en_stock=$res->fields["id_en_stock"];
	$cantidad_reservada=$res->fields["cantidad_reservada"];
	$id_detalle_reserva=$res->fields["id_detalle_reserva"];

	//Descontamos del stock la cantidad reservada previamente (los productos salen aqui efectivamente del stock)
	$sql="update en_stock set cant_reservada=cant_reservada - $cantidad
                                  where id_en_stock=$id_en_stock ";
	sql($sql,"<br>Error al descontar la reserva en el stock (descontar_reserva)<br>") or fin_pagina();

	//log lo ingreso como confirmacion de reserva
	$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
	$values=" $id_en_stock,$id_tipo_movimiento,$cantidad,'$fecha','$usuario','$comentario'";
	$sql="insert into log_movimientos_stock ($campos) values ($values)";
	sql($sql,"<br>Error al insertar el log de movimiento de stock (descontar_reserva)<br>") or fin_pagina();

	//si la cantidad pasada como parametro para descontar es igual a la cantidad reservada en el detalle de la reserva,
	//entonces eliminamos la entrada correspondiente porque ya no es necesaria
	if($cantidad==$cantidad_reservada)
	{
		$sql = " delete from detalle_reserva where id_detalle_reserva=$id_detalle_reserva";
	}
	else//descontamos del detalle de reserva la cantidad reservada
	{
		$sql="update detalle_reserva set cantidad_reservada=cantidad_reservada-$cantidad where id_detalle_reserva=$id_detalle_reserva";
	}
	sql($sql,"<br>Error al eliminar/acutalizar el detalle de la reserva (descontar_reserva)<br>") or fin_pagina();

	$db->CompleteTrans();
}//de function descontar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento=1,$id_fila="",$id_detalle_movimiento="",$id_log_mov_stock="")


/******************************************************************************************
Funcion que cancela la reserva hecha para el producto en el deposito pasado
como parametro. Lo que hace es volver a agregar la cantidad pasada como parametro
a la cantidad disponible del stock, y descuenta esa misma cantidad de la reservada
para ese producto en ese stock. Esto significa que los productos vuelven a estar
disponibles en el stock, por haber cancelado la reserva.
@id_prod_esp            El producto que se va a volver a disponible
@cantidad               La cantidad que se va a volver a disponible
@id_deposito            El deposito donde se va a volver a disponible
@comentario             El comentario que se agrega en el log para describir el movimiento
@id_tipo_movimiento     El tipo de movimiento que se realizara
@id_fila                El id de la fila que generó la cancelación de la reserva
@id_detalle_movimiento  El id de movimiento de material que generó la cancelación de la reserva
@id_log_mov_stock       El id del log de movimiento de stock, que se guardo cuando se hizo
el descuento manual, que quedo en estado pendiente
*******************************************************************************************/
function cancelar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila="",$id_detalle_movimiento="",$id_log_mov_stock="")
{
	global $_ses_user,$db;

	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];

	$db->StartTrans();

	//Traemos los datos del detalle de reserva generado por la fila, o el detalle de movimiento de material,
	//o en forma manual (identificado por el id de log de movimiento de stock). Esto para el deposito y el producto
	//pasados como parametro
	$sql="select id_en_stock,cantidad_reservada,id_detalle_reserva
              from stock.en_stock
              join detalle_reserva using(id_en_stock)
              where id_deposito=$id_deposito and id_prod_esp=$id_prod_esp";


	if($id_fila!="")
	{
		$sql.=" and id_fila=$id_fila";
	}
	else if($id_detalle_movimiento!="")
	{
		$sql.=" and id_detalle_movimiento=$id_detalle_movimiento";
	}
	else if($id_log_mov_stock!="")
	{
		$sql.=" and id_log_mov_stock=$id_log_mov_stock";
	}

	$res=sql($sql,"<br>Error al traer los datos del detalle de reserva (cancelar_reserva)<br>") or fin_pagina($sql);


	$id_detalle_reserva=$res->fields["id_detalle_reserva"];
	$id_en_stock=$res->fields["id_en_stock"];
	$cantidad_reservada=$res->fields["cantidad_reservada"];

	//Devolvemos los productos reservados a la cantidad disponible, y los descontamos de la cantidad reservada
	//(asi vuelven a estar disponibles los productos
	$sql="update en_stock set cant_reservada=cant_reservada - $cantidad,
                                  cant_disp=cant_disp + $cantidad
                                  where id_en_stock=$id_en_stock ";
	sql($sql) or fin_pagina();

	//log de la cancelacion de la reserva
	$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
	$values=" $id_en_stock,$id_tipo_movimiento,$cantidad,'$fecha','$usuario','$comentario'";
	$sql="insert into log_movimientos_stock ($campos) values ($values)";

	sql($sql) or fin_pagina();

	//si la cantidad reservada es igual que la pasada como parametero (que se va a cancelar)
	if($cantidad_reservada==$cantidad)
	{
		//elimino el detalle de reserva porque la cantidad actual sera cero
		$sql="delete from detalle_reserva where id_detalle_reserva=$id_detalle_reserva";
	}
	elseif($cantidad_reservada>$cantidad)
	{
		//sino, si la cantidad a cancelar es menor que la reservada reduzco la cantidad de la reserva
		$sql="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada-$cantidad where id_detalle_reserva=$id_detalle_reserva";
	}
	else
	{
		//sino, la cantidad que se quiere cancelar es mayor que la reservada, por lo que hay algo mal
		//que posiblemente sea el parametro mal pasado
		die("Error: LA CANTIDAD QUE SE INTENTA CANCELAR ES MAYOR A LA ACTUALMENTE RESERVADA.<BR>-LA CANTIDAD RESERVADA ES: $cantidad_reservada<br>-LA CANTIDAD QUE SE INTENTA CANCELAR ES: $cantidad<BR>CONSULTE A LA DIVISION SOFTWARE");
	}

	sql($sql,"<br>Error al actualizar/eliminar el detalle de la reserva (cancelar_reserva)<br>") or fin_pagina();


	$db->CompleteTrans();

}//de function cancelar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila="",$id_detalle_movimiento="",$id_log_mov_stock="")

/***********************************************************************************************
Funcion usada para la parte de autorizacion de descuentos de stock en forma manual.
Se invoca cuando se autoriza un descuento de productos que estaba pendiente.
@id_log_mov_stock   El id de log movimiento que genero la reserva manual del producto
@accion 			 La accion a realizar con la reserva. Si se autorizo el descuento
(el parametro viene con la palabra "autorizar"), se descuenta el producto
de las reservas para ese stock, sino, se rechazo el descuento de
los productos (el parametro viene con la palabra "rechazar"), por lo que
el producto vuelve a estar disponible, y se elimina la reserva realizada
@comentario        Opcional, para agregarle un comentario especifico
************************************************************************************************/
function autorizar_rechazar_reserva_manual($id_log_mov_stock,$accion,$comentario="")
{
	global $db;

	$db->StartTrans();
	//traemos los datos necesarios para confirmar la reserva  del producto en el deposito respectivo
	//esto se hace de esta manera para que la funcion confirmar_reserva sea utilizada desde varios lugares. No se puede cambiar
	//esta manera de trabajar
	$query="select en_stock.id_prod_esp,en_stock.id_deposito,log_movimientos_stock.cantidad
	          from stock.en_stock join stock.log_movimientos_stock using(id_en_stock)
	          where id_log_mov_stock=$id_log_mov_stock
	          ";
	$datos_reserva=sql($query,"<br>Error al traer los datos de la reserva<br>") or fin_pagina();
	$id_prod_esp=$datos_reserva->fields["id_prod_esp"];
	$id_deposito=$datos_reserva->fields["id_deposito"];
	$cantidad=$datos_reserva->fields["cantidad"];


	//Si la accion es autorizar el descuento manual, se descuenta el producto de las reservas para ese stock.
	if($accion=="autorizar")
	{   $comentario.=" --- Autorización del descuento manual de los productos";


	//descontamos la reserva realizada, para que los productos salgan efectivamente del stock
	//(el parametro cuyo valor es 1, es el tipo de movimiento, que en este caso es: Autorizado)
	descontar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,1,"","",$id_log_mov_stock);
	}
	//Si la accion es rechazar el descuento manual, se vuelven a poner disponibles las cantidades de
	//producto de las reservas para ese stock, y se elimina la reserva realizada
	else
	{
		$comentario.=" --- Rechazo del descuento manual de los productos";


		//cancelamos la reserva realizada
		//(el parametro cuyo valor es 3, es el tipo de movimiento, que en este caso es: Rechazado)
		cancelar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,3,"","",$id_log_mov_stock);
	}

	$db->CompleteTrans();
}//de function descontar_reserva_manual($id_log_mov_stock)


/******************************************************************************************
Funcion que descuenta productos a confirmar del deposito pasado como parametro.
Lo que hace es descontar la cantidad pasada como parametro de las cantidad
a confirmar para ese producto en ese stock.

@id_prod_esp            El producto que se va a descontar
@cantidad               La cantidad a descontar
@id_deposito            El deposito donde se va a descontar el producto
@comentario             El comentario que se agrega en el log para describir el movimiento
@id_tipo_movimiento     El tipo de movimiento que se realizara
@id_fila                El id de la fila que generó la reserva
*******************************************************************************************/
function descontar_a_confirmar($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila="")
{
	global $_ses_user,$db;

	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];

	$db->StartTrans();

	//Traemos los datos del detalle a confirmar generado por la fila. Esto para el deposito y el producto
	//pasados como parametro
	$sql="select en_stock.id_en_stock,detalle_a_confirmar.cant_a_confirmar,detalle_a_confirmar.id_detalle_a_confirmar
              from stock.en_stock
              join stock.detalle_a_confirmar using(id_en_stock)
              where en_stock.id_deposito=$id_deposito and en_stock.id_prod_esp=$id_prod_esp";

	if($id_fila!="")
	{
		$sql.=" and detalle_a_confirmar.id_fila=$id_fila";
	}

	$res=sql($sql,"<br>Error al traer los datos del detalle a confirmar (descontar_a_confirmar)<br>") or fin_pagina($sql);

	$id_en_stock=$res->fields["id_en_stock"];
	$cant_a_confirmar=$res->fields["cant_a_confirmar"];
	$id_detalle_a_confirmar=$res->fields["id_detalle_a_confirmar"];

	//Descontamos del stock la cantidad a confirmar (los productos salen aqui efectivamente del stock)
	$sql="update stock.en_stock set cant_a_confirmar=cant_a_confirmar - $cantidad
                                  where id_en_stock=$id_en_stock ";
	sql($sql,"<br>Error al descontar la cantidad a confirmar en el stock (descontar_a_confirmar)<br>") or fin_pagina();

	$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
	$values=" $id_en_stock,$id_tipo_movimiento,$cantidad,'$fecha','$usuario','$comentario'";
	$sql="insert into log_movimientos_stock ($campos) values ($values)";
	sql($sql,"<br>Error al insertar el log de movimiento de stock (descontar_reserva)<br>") or fin_pagina();

	//si la cantidad pasada como parametro para descontar es igual a la cantidad a confirmar en el detalle,
	//entonces eliminamos la entrada correspondiente porque ya no es necesaria
	if($cantidad==$cant_a_confirmar)
	{
		$sql = " delete from stock.detalle_a_confirmar where id_detalle_a_confirmar=$id_detalle_a_confirmar";
	}
	else//restamos del detalle a confirmar la cantidad descontada
	{
		$sql="update stock.detalle_a_confirmar set cant_a_confirmar=cant_a_confirmar-$cantidad where id_detalle_a_confirmar=$id_detalle_a_confirmar";
	}
	sql($sql,"<br>Error al eliminar/acutalizar el detalle a confirmar (descontar_reserva)<br>") or fin_pagina();

	$db->CompleteTrans();
}//de function descontar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento=1,$id_fila="",$id_detalle_movimiento="",$id_log_mov_stock="")


/***************************************************************************************
Función que devuelve el monto total (disponible + reservado + a confirmar)
y el monto disponible del stock pasado como parametro

@id_deposito		El id del deposito para el cual se quiere averiguar los montos
****************************************************************************************/
function  calcular_monto_stock($id_deposito)
{
	$sql=" select sum((cant_disp + en_stock.cant_reservada +en_stock.cant_a_confirmar)*precio_stock)  as total,
                      sum(cant_disp*precio_stock) as total_disponible
                from depositos
                join en_stock using(id_deposito)
                join producto_especifico using(id_prod_esp)
                where id_deposito=$id_deposito
                ";

	$resultados=sql($sql) or fin_pagina();
	$monto=array();
	$monto["total"]=$resultados->fields["total"];
	$monto["disponible"]=$resultados->fields["total_disponible"];
	return $monto;
}  // de la funcion calcular monto


/***********************************************************************************************
Funcion que inserta el producto pasado como parametro al stock en produccion y lo ata al renglon
de licitacion pasado como parametro.
************************************************************************************************/
function agregar_a_en_produccion($id_prod_esp,$cantidad,$comentario,$id_licitacion)
{
	globaL $db;
	$db->StartTrans();

	//buscamos el id del stock Produccion
	$query="select id_deposito from depositos where nombre='Produccion'";
	$id_prod_deposito=sql($query,"<br>Error al traer el id del deposito de produccion<br>") or fin_pagina();
	$id_dep_en_produccion=$id_prod_deposito->fields["id_deposito"];

	//buscamos el tipo de movimiento: Ingreso a Stock en Produccion
	$query="select id_tipo_movimiento from tipo_movimiento where nombre='Ingreso a Stock en Produccion'";
	$id_tip_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();
	$id_tipo_movimiento=$id_tip_mov->fields["id_tipo_movimiento"];

	//insertamos en el stock en_produccion el producto pasado como parametro
	agregar_stock($id_prod_esp,$cantidad,$id_dep_en_produccion,$comentario,$id_tipo_movimiento);

	//luego agregamos la entrada correspondiente en stock de produccion, seleccionando primero por si existe ya la entrada y sino
	//creandola
	$query="select id_en_produccion,id_en_stock
            from en_stock left join
            (select * from en_produccion where id_licitacion=$id_licitacion) as en_produccion
             using(id_en_stock)
            where id_prod_esp=$id_prod_esp and id_deposito=$id_dep_en_produccion";
	$hay_en_produccion=sql($query,"<br>Error al consultar si hay en produccion<br>") or fin_pagina();

	if($hay_en_produccion->fields["id_en_produccion"])
	$query="update en_produccion set cantidad=cantidad+$cantidad where id_en_produccion=".$hay_en_produccion->fields["id_en_produccion"];
	else
	$query="insert into en_produccion(id_en_stock,id_licitacion,cantidad)
    	        values(".$hay_en_produccion->fields["id_en_stock"].",$id_licitacion,$cantidad)";
	sql($query,"<br>Error al insertar/actualizar en produccion<br>") or fin_pagina();

	$db->CompleteTrans();
}//de function agregar_a_en_produccion($id_prod_esp,$cantidad,$comentario,$id_renglon)


/**
 *  Funcion que descuenta el producto pasado como parametro del stock en produccion para la
 	licitacion pasada como parametro
 *
 * @param integer $id_prod_esp         -El producto que se va a descontar del stock en produccion
 * @param integer $id_licitacion       -La licitacion para la cual se va a descontar el producto
 * @param integer $cantidad_descontar  -La cantidad que se va a descontar
 * @param text	  $comentario          -El comentario que se utilizará para registrar el movimiento del stock
 									    en la tabla log_movimiento_stock
 */
function descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cantidad_descontar,$comentario)
{
	global $db,$_ses_user;
	$db->StartTrans();

	//buscamos el id del stock Produccion
	$query="select id_deposito from depositos where nombre='Produccion'";
	$id_prod_deposito=sql($query,"<br>Error al traer el id del deposito de produccion<br>") or fin_pagina();
	$id_dep_en_produccion=$id_prod_deposito->fields["id_deposito"];

	//buscamos el tipo de movimiento: Descuento de Stock en Produccion
	$query="select id_tipo_movimiento from tipo_movimiento where nombre='Descuento de Stock en Produccion'";
	$id_tip_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();
	$id_tipo_movimiento=$id_tip_mov->fields["id_tipo_movimiento"];

	//traemos el id_en_stock correspondiente para hacer los cambios
	$query="select id_en_stock,cant_disp,producto_especifico.descripcion
	        from stock.en_stock join stock.en_produccion using(id_en_stock)
            join general.producto_especifico using(id_prod_esp)
	        where id_prod_esp=$id_prod_esp and id_deposito=$id_dep_en_produccion and id_licitacion=$id_licitacion";
	$id_stock=sql($query,"<br>Error al traer el id del stock que se va a actualizar<br>") or fin_pagina();
	$id_en_stock=$id_stock->fields["id_en_stock"];
	$cant_disp_actual=$id_stock->fields["cant_disp"];
	$descrip_prod=$id_stock->fields["descripcion"];
	$deposito="Produccion";

	//controlamos que la cantidad actual disponible sea mayor o igual a la que se quiere descontar
	//para evitar que se descuente algo que no existe. Si no hay cantidad suficiente damos error.
	if($cantidad_descontar<=$cant_disp_actual)
	{
		//descontamos la cantidad de la tabla en_produccion y de la tabla en_stock
		$query="update stock.en_produccion set cantidad=cantidad-$cantidad_descontar
		        where id_en_stock=$id_en_stock and id_licitacion=$id_licitacion";
		sql($query,"<br>Error al actualizar la tabla en produccion para el producto: $id_prod_esp y la licitacion: $id_licitacion (descontar_producto_en_produccion)<br>") or fin_pagina();

		$query="update stock.en_stock set cant_disp=cant_disp-$cantidad_descontar
		        where id_en_stock=$id_en_stock";
		sql($query,"<br>Error al actualizar la tabla de stock para el producto: $id_prod_esp y la licitacion: $id_licitacion (descontar_producto_en_produccion)<br>") or fin_pagina();

		//luego registramos el descuento del stock de produccion
		$fecha_modif=date("Y-m-d H:i:s",mktime());
		$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
		$values=" $id_en_stock,$id_tipo_movimiento,$cantidad_descontar,'$fecha_modif','".$_ses_user["name"]."','$comentario'";
		$sql="insert into log_movimientos_stock ($campos) values ($values)";
		sql($sql,"<br>Error al insetar en el log de movimientos de stock (agregar_stock)<br>") or fin_pagina();
	}//de if($cantidad<=$cant_disp_actual)
	else//no hay cantidad suficiente para descontar
	die("Error: No hay suficiente cantidad de stock disponible para: '$descrip_prod' en el Stock $deposito.<br> Posiblemente, antes de que usted guardara los cambios alguien utilizó los productos que figuraban como disponibles.<br>Revise las cantidades actuales y vuelva a intentarlo.");

	$db->CompleteTrans();
}//de function descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cantidad_descontar,$comentario)

/*
----------------------------------------------------------------------
----------------------------------------------------------------------
Funciones traidas desde el lib
----------------------------------------------------------------------
----------------------------------------------------------------------
*/

/*************************************************************
Funcion para indicar si hay stock disponible en el deposito
pasado, para el producto pasado.

@id_producto es el id del producto del que se desea saber si
hay stock
@id_deposito es el deposito donde se revsia si hay productos
disponibles
@cantidad    es la cantidad que debe haber disponibles para que
la funcion devuelva que si hay lugar disponible
@adicional   en algunos casos se usa este parametro para comparar
$cantidad con la cantidad disponible + el $adicional
este parametro es opcional
**************************************************************/
function hay_stock_disp($id_prod_esp,$id_deposito,$cantidad,$adicional=0)
{global $db;
$query="select cant_disp as cantidad from en_stock where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito";
$control=sql($query) or fin_pagina();
if($control->fields['cantidad']!="" && ($control->fields['cantidad']+$adicional)>=$cantidad)
return 1;
else
return 0;
}

/**************************************************************************************
Realiza un control para saber si el producto esta en algunos de los stocks.
La funcion devuelve la suma total de este producto que hay en todos los stocks.
@id_prod_esp  El id del producto especifico que queremos ver si esta en algun stock
***************************************************************************************/
function en_stock_coradir($id_prod_esp)
{
	$sql="select sum(cant_disp) as hay_en_stock from stock.en_stock
	      left join general.depositos  using (id_deposito)
	      where id_prod_esp=$id_prod_esp and tipo=0";
	$res_sql=sql($sql, "Error al traer datos del produto en los distintos stocks") or fin_pagina();
	$hay_en_stock=$res_sql->fields['hay_en_stock'];
	return $hay_en_stock;

} // fin function control_prod_oc_stock()


/*******************************************************************
Funcion que descuenta del stock en produccion, todos los productos
de las OC asociadas a una licitacion determinada, que se cargaron
al stock de produccion a medida que se fueron entregando
los productos de cada OC. Esta funcion solo se debe usar para
licitaciones con estado "Entregada".

@id_licitacion  el ID con el cual obtenemos todas las OC asociadas,
para poder saber cuales son los productos que estan
en produccion, y asi descontarlos de dicho stock
********************************************************************/
function descontar_en_produccion($id_licitacion,$id_renglon)
{global $db,$_ses_user;

$db->StartTrans();

$fecha_modif=date("Y-m-d H:i:s",mktime());
$comentario="Descuento a Stock de Producción generado por Entrega del renglon $id_renglon de la licitación Nº $id_licitacion";

//traemos todas las OC atadas a la licitacion pasada como parametro, que tienen entradas en la tabla en_produccion
$query="select id_en_stock,id_prod_esp,en_produccion.cantidad from en_produccion join en_stock using(id_en_stock)
          where id_renglon=$id_renglon and en_produccion.cantidad>0";
$productos_en_produccion=sql($query,"<br>Error al traer los productos en produccion del renglon Nº $id_renglon<br>") or fin_pagina();

//buscamos el id del stock Produccion
$query="select id_deposito from depositos where nombre='Produccion'";
$id_prod_deposito=sql($query,"<br>Error al traer el id del deposito de produccion<br>") or fin_pagina();
$id_dep_en_produccion=$id_prod_deposito->fields["id_deposito"];

//descontamos todas las entradas de en_produccion, y del stock en produccion
while (!$productos_en_produccion->EOF)
{
	$cantidad=$productos_en_produccion->fields["cantidad"];
	$id_prod_esp=$productos_en_produccion->fields["id_prod_esp"];
	$id_en_produccion=$productos_en_produccion->fields["id_en_produccion"];



	//descontamos de la tabla stock
	$query="update en_stock set cant_disp=cant_disp-$cantidad
  	        where id_deposito=$id_dep_en_produccion and id_prod_esp=$id_prod_esp";
	sql($query,"<br>Error al actualizar stock(descontar_en_produccion)<br>") or fin_pagina();

	//descontamos de la tabla en_produccion
	$query="update en_produccion set cantidad=cantidad-$cantidad
  	        where id_en_produccion=$id_en_produccion";
	sql($query,"<br>Error al actualizar en produccion(descontar_en_produccion)<br>") or fin_pagina();

	//log lo ingreso como tipo de ingreso manual
	$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
	$values=" $id_en_stock,$id_tipo_movimiento,$cantidad_ingresar,'$fecha_modif','".$_ses_user["name"]."','$comentario'";
	$sql="insert into log_movimientos_stock ($campos) values ($values)";
	sql($sql,"<br>Error al insetar en el log de movimientos de stock (agregar_stock)<br>") or fin_pagina();

	$productos_en_produccion->MoveNext();
}//de while(!$productos_en_produccion->EOF)

$db->CompleteTrans();
}//de function descontar_en_produccion($id_licitacion)




/*******************************************************************
Funcion que descuenta del stock en produccion, asociadas a una licitacion
determinada, que se cargaron
al stock de produccion.
Esta funcion solo se debe usar para licitaciones con estado "Entregada".

@id_licitacion  el ID con el cual obtenemos todas las OC asociadas,
para poder saber cuales son los productos que estan
en produccion, y asi descontarlos de dicho stock
********************************************************************/
function desc_en_produccion($id_licitacion)
{global $db,$_ses_user;

$db->StartTrans();

$fecha_modif=date("Y-m-d H:i:s",mktime());
$comentario="Descuento a Stock de Producción generado por Entrega de la licitación Nº $id_licitacion";

//traemos los productos de la licitacion pasada como parametros de la tabla en_produccion
$query="select id_en_stock,en_produccion.cantidad,id_prod_esp,descripcion,producto_especifico.precio_stock
          from en_produccion join en_stock using (id_en_stock)
          join general.producto_especifico using (id_prod_esp)
          where id_licitacion=$id_licitacion and en_produccion.cantidad>0";
$productos_en_produccion=sql($query,"<br>Error al traer los productos en produccion la licitación Nº $id_licitacion<br>") or fin_pagina();

//buscamos el id del stock Produccion
$query="select id_deposito from depositos where nombre='Produccion'";
$id_prod_deposito=sql($query,"<br>Error al traer el id del deposito de produccion<br>") or fin_pagina();
$id_dep_en_produccion=$id_prod_deposito->fields["id_deposito"];

//descontamos todas las entradas de en_produccion, y del stock en produccion
if ($productos_en_produccion->RecordCount() > 0)
{
	$contenido="<table border=1 cellpading=0 cellspacing=0 width=100% align=center><tr><td colspan=3 align=center><b>Id: $id_licitacion, Tablas en produccion y en_stock</b></td></tr>";
	$contenido.="<tr><td width=70%> Descripcion</td><td width=15%>Cantidad</td><td width=15%>Precio</td></tr>";
}
else
{
	$contenido="<table><tr><td>No se realizaron descuentos en stock de produccion para el id:$id_licitacion</td></tr></table>";
}
$precio_acumulado=0;
while (!$productos_en_produccion->EOF)
{
	$cantidad=$productos_en_produccion->fields["cantidad"];
	$id_en_stock=$productos_en_produccion->fields["id_en_stock"];
	$id_prod_esp=$productos_en_produccion->fields["id_prod_esp"];
	$descripcion=$productos_en_produccion->fields["descripcion"];
	$precio=$productos_en_produccion->fields["precio_stock"];
	$precio_acumulado+=$precio*$cantidad;

	//descontamos de la tabla stock
	$query="update en_stock set cant_disp=cant_disp-$cantidad
  	        where id_deposito=$id_dep_en_produccion and id_en_stock=$id_en_stock";
	sql($query,"<br>Error al actualizar stock(descontar_en_produccion)<br>") or fin_pagina();

	//descontamos de la tabla en_produccion
	$query="update en_produccion set cantidad=cantidad-$cantidad
  	        where id_en_stock=$id_en_stock and id_licitacion=$id_licitacion";
	sql($query,"<br>Error al actualizar en produccion(descontar_en_produccion)<br>") or fin_pagina();

	$contenido.="<tr><td>$descripcion</td><td align=right>$cantidad</td><td align=right>".formato_money($precio)."</td></tr> ";

	//log lo ingreso como tipo de ingreso manual
	$sql_mov="select id_tipo_movimiento from stock.tipo_movimiento where nombre ilike 'Descuento de Stock en Produccion'";
	$sql_movi=sql($sql_mov,"$sql_mov") or fin_pagina();
	$id_tipo_movimiento=$sql_movi->fields['id_tipo_movimiento'];
	$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
	$values=" $id_en_stock,$id_tipo_movimiento,$cantidad,'$fecha_modif','".$_ses_user["name"]."','$comentario'";
	$sql="insert into log_movimientos_stock ($campos) values ($values)";
	sql($sql,"<br>Error al insetar en el log de movimientos de stock (agregar_stock)<br>") or fin_pagina();

	$productos_en_produccion->MoveNext();
}//de while(!$productos_en_produccion->EOF)

if ($productos_en_produccion->RecordCount() > 0)
{
	$contenido.="<tr><td>&nbsp;</td><td align=center>Monto total Descontado</td><td>".formato_money($precio_acumulado)."</td></tr>";
}


if ($db->CompleteTrans()) {
	$para="fernando@coradir.com.ar";
	$asunto="Descuento a Stock de Producción generado por Entrega de la licitación Nº $id_licitacion";
	enviar_mail_html($para,$asunto,$contenido,$adjunto,$path,$tipo);
}
}//de function desc_en_produccion($id_licitacion)

//modifica el precio $precio_stock (nuevo precio insertado )
//de un producto $id_prod_esp
// guarda logs al modificar precio de stock
function modif_precio($id_prod_esp,$precio_stock,$comentario="Modificación Manual")
{
	global $_ses_user,$db;

	$db->StartTrans();
	$usuario=$_ses_user["name"];
	$fecha=date("Y-m-d H:i:s",mktime());

	//obtenemos el precio de stock anterior
	$sql="select descripcion,precio_stock from producto_especifico
        where id_prod_esp=$id_prod_esp";
	$res=sql($sql,"<br>Error al traer el precio anterior del producto especifico (modif_precio)<br>") or fin_pagina();
	$producto=$res->fields["descripcion"];
	if($res->fields["precio_stock"]!="")
	$precio_anterior=$res->fields["precio_stock"];
	else
	$precio_anterior=0;


	$sql="update producto_especifico set precio_stock=$precio_stock
       where id_prod_esp=$id_prod_esp";
	sql($sql,"Error al actualizar precio $sql") or fin_pagina();

	$sql="insert into log_cambio_precio (id_prod_esp,precio_anterior,usuario,fecha,comentario)
        values ($id_prod_esp,$precio_anterior,'$usuario','$fecha','$comentario')";
	sql($sql,"Error al guardar log cambio precio $sql") or fin_pagina();

	$db->CompleteTrans();
}//de function modif_precio($id_prod_esp,$precio_anterior,$precio_stock)

function mostrar_log_cambio_precio($id_prod_esp)
{

	$q="select usuario,fecha,precio_anterior,comentario from
	    log_cambio_precio where id_prod_esp=$id_prod_esp
	    order by fecha desc ";
	$log=sql($q,"$q") or fin_pagina();
	if ($log->RecordCount()>0)
	{
	?>
	<div align="right">
	<input name="mostrar_ocultar_log" class='estilos_check' type="checkbox" value="1" onclick="if(!this.checked)
																		  document.all.tabla_logs.style.display='none'
																		 else
																		  document.all.tabla_logs.style.display='block'
																		  "> Mostrar Logs
	</div>


	<!-- tabla de Log  -->
	<div style="display:'none';width:98%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;'?> " id="tabla_logs" >
	<table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
	 <tr id="ma">
	  <td>
	   Fecha Modificación
	  </td>
	  <td>
	   Precio Anterior
	  </td>
	  <td>
	   Comentario
	  </td>
	  <td>
	   Usuario
	  </td>
	 </tr>
	<?
	while (!$log->EOF)
	{?>
	  <tr>
	     <td><?=fecha($log->fields['fecha'])." ".Hora($log->fields['fecha']);?> </td>
	     <td><?="U\$S ".formato_money($log->fields['precio_anterior']);?> </td>
	     <td><?=($log->fields['comentario'])?$log->fields['comentario']:"&nbsp;"?></td>
	     <td><?=$log->fields['usuario'];?> </td>
	  <tr>
	<?
	$log->Movenext();
	}
	?>
	</table>
	<br>
	</div>
	<?
	}//de if ($log->RecordCount()>0)
}//de function mostrar_log_cambio_precio($id_prod_esp)

//elimina rma
//recibe un arreglo con los id de los RMA
//estado es el estado (lugar de la tabla estado_rma)
//id_nota de credito la nota de credito creada si es baja por nota de credito
function eliminar_rma($rma_selec,$estado,$id_nota_credito="",$scrap=0)
{
	global $_ses_user,$db;
	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user["name"];
	$db->StartTrans();
	$sql_estado ="select estado_rma.id_estado_rma from stock.estado_rma where estado_rma.lugar ilike '$estado%'";
	$res_estado=sql($sql_estado,"$sql_estado") or fin_pagina();
	$id_estado=$res_estado->fields['id_estado_rma'];

	$list='(';
	foreach($rma_selec as $key => $value)
	{
		$list.=$value.',';
	}
	$list=substr_replace($list,')',(strrpos($list,',')));

	if ($id_nota_credito)
	{
		$nota=",id_nota_credito=$id_nota_credito,fecha_hist='$fecha'";
		$nombre_log="Baja en RMA por creación de Nota de Crédito";
	}
	elseif($scrap==1)
	{
		$nota="";
		$nombre_log="Baja en RMA por paso del producto a Scrap";
	}
	else
	{
		$nota="";
		$nombre_log="Descuento de stock RMA por cambio de producto";
	}

	$sql="update stock.info_rma set id_estado_rma=$id_estado $nota
       where id_info_rma in $list";
	$res=sql($sql,"$sql") or fin_pagina();

	//traemos información necesaria de cada entrada eliminada de RMA
	$query="select info_rma.id_en_stock,info_rma.id_info_rma,info_rma.cantidad,en_stock.id_prod_esp,en_stock.id_deposito
			from stock.info_rma join stock.en_stock using(id_en_stock)
			where id_info_rma in $list";
	$info_rma=sql($query,"<br>Error al traer la informacion necesaria de RMA<br>") or fin_pagina();

	//traemos el id del tipo de movimiento de stock para indicar la baja del producto en RMA
	$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='$nombre_log'";
	$id_tipo_log=sql($query,"<br>Error al traer el id del tipo de movimento<br>") or fin_pagina();

	if($id_tipo_log->fields["id_tipo_movimiento"]!="")
	$id_tipo_movimiento=$id_tipo_log->fields["id_tipo_movimiento"];
	else
	die("<br>Error Interno rma1205: No se pudo determinar el tipo de movimiento en stock. Contacte a la División Software<br>");

	while (!$info_rma->EOF)
	{
		$cantidad_descontar=$info_rma->fields["cantidad"];
		$id_en_stock=$info_rma->fields["id_en_stock"];
		$id_prod_esp=$info_rma->fields["id_prod_esp"];
		$id_deposito=$info_rma->fields["id_deposito"];
		$id_info_rma_log=$info_rma->fields["id_info_rma"];


		if ($id_nota_credito)
		{
			$comentario_log="Se dio de baja este producto de RMA por creación de Nota de Crédito Nº $id_nota_credito. (RMA Nº $id_info_rma_log)";
		}
		elseif($scrap==1)
		{
			$comentario_log="Se dio de baja este producto de RMA para pasarlo a Scrap (RMA Nº $id_info_rma_log)";
		}
		else
		{
			$comentario_log="Descuento por cambio de partes para RMA (RMA Nº $id_info_rma_log)";
		}

		//descontamos de la tabla en_stock, la cantidad eliminada de RMA. Esa cantidad sale del stock disponible
		//pero queda almacenada en la tabla info_rma, que sigue estando realcionada con la entrada de la tabla en_stock
		descontar_stock_disponible($id_prod_esp,$cantidad_descontar,$id_deposito,$id_tipo_movimiento,$comentario_log);

		$info_rma->MoveNext();
	}//de while(!$info_rma->EOF)


	$sql_log="";
	foreach($rma_selec as $key => $value)
	{
		$sql_log[]="insert into stock.log_info_rma (comentario,usuario_log,tipo_log,id_estado_rma,id_info_rma,fecha)
               values('$estado','$usuario','$estado',$id_estado,$value,'$fecha')";
	}
	if ($sql_log != "") {
		$res_log=sql($sql_log,"$sql_log") or fin_pagina();
	}
	if ($db->CompleteTrans())
	return true;
	else return false;
}//de function eliminar_rma($rma_selec,$estado,$id_nota_credito="")

/***********************************************************************
Funcion que elimina del sistema una entrada de RMA
@id_info_rma     El RMA a eliminar
@justificacion   La justificacion de porque se elimina el RMA pasado
************************************************************************/
function borrar_rma_del_sistema($id_info_rma,$justificacion)
{
	global $db,$_ses_user;
	$db->StartTrans();
	//traemos los datos del RMA para eliminarlo
	$query="select id_prod_esp,cantidad,id_deposito
	        from stock.info_rma join stock.en_stock using(id_en_stock)
	        where id_info_rma=$id_info_rma
	        ";
	$rma_info=sql($query,"<br>Error al traer los datos del RMA a eliminar (borrar_rma_del_sistema)<br>") or fin_pagina();
	$id_prod_esp=$rma_info->fields["id_prod_esp"];
	$cantidad=$rma_info->fields["cantidad"];
	$id_deposito=$rma_info->fields["id_deposito"];
	$comentario="El usuario ".$_ses_user["name"]." eliminó el RMA Nº $id_info_rma y su justificación fue: $justificacion";

	//traemos el id del tipo de movimiento: 'Eliminación Manual de RMA'
	$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Eliminación Manual de RMA'";
	$tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento (borrar_rma_del_sistema)<br>") or fin_pagina();
	$id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];

	//Eliminamos el producto de la tabla en_stock y generamos el log de eliminacion de RMA correspondiente
	descontar_stock_disponible($id_prod_esp,$cantidad,$id_deposito,$id_tipo_movimiento,$comentario);

	//eliminamos los comentarios del RMA
	$query="delete from stock.comentario_rma where id_info_rma=$id_info_rma";
	sql($query,"<br>Error al eliminar comentarios de RMA (borrar_rma_del_sistema)<br>") or fin_pagina();

	//eliminamos las referencias a archivos subidos para el RMA
	$query="delete from stock.archivo_rma where id_info_rma=$id_info_rma";
	sql($query,"<br>Error al eliminar referencias a archivos subidos para RMA (borrar_rma_del_sistema)<br>") or fin_pagina();

	//eliminamos los codigos de barra usados en el RMA de la tabla voids_rma
	$query="delete from stock.void_rma where id_info_rma=$id_info_rma";
	sql($query,"<br>Error al eliminar voids de RMA (borrar_rma_del_sistema)<br>") or fin_pagina();

	//eliminamos el log del RMA
	$query="delete from stock.log_info_rma where id_info_rma=$id_info_rma";
	sql($query,"<br>Error al eliminar el log de RMA (borrar_rma_del_sistema)<br>") or fin_pagina();

	//eliminamos la entrada de RMA de la tabla info_rma
	$query="delete from stock.info_rma where id_info_rma=$id_info_rma";
	sql($query,"<br>Error al eliminar el RMA (borrar_rma_del_sistema)<br>") or fin_pagina();

	$db->CompleteTrans();
}//de function borrar_rma_del_sistema()

//recepcion de productos cuando se cambia por nuevo

function generar_form_recepcion_rma($id_info_rma)
{
	global $db;

	$query="select en_stock.id_prod_esp,precio_stock,
          id_info_rma,descripcion,id_proveedor,
          info_rma.cantidad as cant_rma,
          case when rma_recepcion.cantidad is null
          then 0 else rma_recepcion.cantidad end as cant_recibida
		  from stock.en_stock
		  join general.producto_especifico using(id_prod_esp)
		  join stock.info_rma using (id_en_stock)
		  join general.proveedor using (id_proveedor)
          left join stock.rma_recepcion using(id_info_rma)
          where id_info_rma=$id_info_rma";

	$datos_recibidos=sql($query,"<br>Error al traer los datos de la recepción<br>") or fin_pagina();
	?>
	<script>


	var wrecibir_prod=new Object();
	wrecibir_prod.closed=1;
	function elegir_producto_recibido()
	{
		var producto=eval("document.all.prod_rec");
		var id_producto=eval("document.all.id_prod_rec");
		producto.value=wrecibir_prod.document.all.nombre_producto_elegido.value;
		id_producto.value=wrecibir_prod.document.all.id_producto_seleccionado.value;
		wrecibir_prod.close();

	}//de function elegir_producto_recibido(id_fila)

	//ventana de codigos de barra entrega
	var vent_cb=new Object();
	vent_cb.closed=true;
	</script>
	<?
	$datos_recibidos->Move(0);
	while (!$datos_recibidos->EOF)
	{

		$cantidad_comprada=$datos_recibidos->fields['cant_rma'];
		$desc=$datos_recibidos->fields['descripcion'];
		$cantidad_recibida=$datos_recibidos->fields['cant_recibida'];
		$precio_unitario=$datos_recibidos->fields['precio_stock'];
		$simbolo_moneda="U\$S";
		if($cantidad_comprada<=$cantidad_recibida)
		$todo_recibido=1;
		else
		$todo_recibido=0;
		  ?>
		 <table width="100%" align="center" class="bordes">
		  <tr id="sub_tabla">
	        <td width="60%" align="left">
	         <b>Descripción</b> <input name="desc" type="text" style="text-align:left; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" readonly value="<?=$desc?>" size="60"  >
	        </td>
	        <td width="15%" <?=$simbolo_moneda?> <?=number_format($precio_unitario*$cantidad_comprada,2,'.','')?>">
	         <b>Precio <font style="color: blue;"><?=$simbolo_moneda?> <?=number_format($precio_unitario,2,'.','')?></b>
	        </td>
	        <td width="13%">
	         <b>Cantidad</b> <input name="comprado" type="text" style="text-align:right; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" readonly value="<?=$cantidad_comprada?>" size="6" >
	        </td>
	       <?/* <td width="12%">
		  <b>Recibido</b> <input name="cantidad_rec" type="text" style="text-align:right; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" value="<?=$cantidad_recibida?>" readonly size="6" >
	        </td>*/?>
		  </tr>
		</table>
		<?
		$datos_recibidos->Movenext();
	}//de while (!$datos_recibidos->EOF)

	//traemos el id del deposito Buenos Aires
	$query="select depositos.id_deposito from general.depositos where nombre='Buenos Aires'";
	$id_depo=sql($query,"<br>Error al traer el id del deposito bs as<br>") or fin_pagina();
	$id_deposito=$id_depo->fields["id_deposito"];
	?>
	<script>
	function control_recib_cb()
	{
		if(document.all.id_prod_rec.value=="")
		{
			alert("Debe elegir un producto para recibir");
		}
		else
		{
			<?
			$link_cb=encode_link("recepcion_codigos_barra_rma.php",array("total_comprado"=>$cantidad_comprada,"total_recibido"=>$cantidad_recibida,"producto_nombre"=>"$producto","id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma,"recargar"=>1));
			?>
			if(vent_cb.closed)
			vent_cb=window.open('<?=$link_cb?>','','top=70, left=200, width=650px, height=600px, scrollbars=1, status=1,directories=0');
			else
			vent_cb.focus();
		}

	}//de function control_recib_cb()
	</script>


<?
if(!$todo_recibido)
{

	  ?>  <table width="100%" align="center">
		     <tr>
		      <td width="25%">
			    <b>Recibir en</b>
			    Stock: Buenos Aires

			  </td>
			  <td width="15%">
			    <input type="button" name="codigos_barra" value="Códigos de Barra" onclick="control_recib_cb()">
			  </td>
			  <td width="60%">
			    <b>Producto</b>
			    <input type="text" name="prod_rec" size="70" readonly>
			    <input type="hidden" name="id_prod_rec">
			    <?
			    $funcion_prod_esp="window.opener.elegir_producto_recibido(".$id_fila.")";
			     $link_elegir_prod=encode_link('../productos/listado_productos_especificos.php',array('pagina_viene'=>'ord_compra_recepcion.php','onclick_cargar'=>"$funcion_prod_esp")) ?>
			    <input type="button" name="elegir_producto" value="Elegir" onclick="if(wrecibir_prod.closed)
                                                                                                   wrecibir_prod=window.open('<?=$link_elegir_prod?>','','toolbar=0,location=0,directories=0,resizable=1,status=0, menubar=0,scrollbars=1,left=25,top=10,width=950,height=500');
                                                                                                  else
                                                                                                   wrecibir_prod.focus();
                                                                                                 "
			    >
			  </td>
			 </tr>
			</table>

	 <?
}//de if(!$todo_recibido)
else
{
	 	?>
	 	<table width="100%" align="center">
	     <tr>
 		  <td width="15%">
		    <?
		    $link_cb=encode_link("recepcion_codigos_barra_rma.php",array("total_comprado"=>$cantidad_comprada,"total_recibido"=>$cantidad_recibida,"producto_nombre"=>"$producto","id_prod_esp"=>18,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma));
		    ?>
		    <input type="button" name="codigos_barra" value="Códigos de Barra" onclick="if(vent_cb.closed)vent_cb=window.open('<?=$link_cb?>','','top=70, left=200, width=650px, height=600px, scrollbars=1, status=1,directories=0');else vent_cb.focus();"
		    >
		  </td>
		 </tr>
		</table>
	 	<?
}//del else de if(!$todo_recibido)

}//de function generar_form_recepcion_rma($id_info_rma)

function mostrar_log_recepcion_rma($id_info_rma)
{

	$sql="select usuario,fecha,cantidad_recibida,descripcion,id_prod_esp
          from stock.log_rma_recepcion
          join stock.rma_recepcion using(id_rma_recepcion)
          join general.producto_especifico using(id_prod_esp)
          where id_info_rma=$id_info_rma
	      order by fecha";
	$log_recibidos=sql($sql,"$sql") or fin_pagina();
	if($log_recibidos->RecordCount()>0)
	{?>
		<br>
		<table width="95%" align="center" class="bordes">
		 <tr id="mo">
		    <td colspan='5'>
		     Registro de Recepciones del Producto en el Stock Buenos Aires
		     </td>
		  </tr>
		  <tr id="mo_sf6">
			 <td width="50%"> <b>Producto</b> </td>
			 <td width="5%"> <b>Cantidad</b> </td>
		     <td width="20%"> <b>Fecha</b> </td>
			 <td width="20%"> <b>Usuario</b> </td>
		   </tr>
		   <?

		   while (!$log_recibidos->EOF) {?>
			 <tr id="ma_mg">
			   <td align="center"> <?=$log_recibidos->fields["descripcion"]?></td>
			   <td align="center"> <?=$log_recibidos->fields["cantidad_recibida"]?></td>
			   <td align="center"> <?=fecha($log_recibidos->fields["fecha"])." ".hora($log_recibidos->fields["fecha"])?></td>
			   <td align="center"> <?=$log_recibidos->fields["usuario"]?> </td>
		     </tr>
		  <?

		  $log_recibidos->MoveNext();
		   }//de while(!$log_recibidos->EOF)
		?>
		</table>
		<?
		return $productos;
	}//de if($log_recibidos->RecordCount()>0)
}//de function mostrar_log_recepcion_rma($id_info_rma)


/**************************************************************************************
Realiza la recepcion de productos para RMA.

@id_info_rma		El RMA al que se le hara la recepcion
@cantidad			La cantidad recibida
@id_prod_esp  		El id de producto recibido
@cb_insertados		Arreglo con los codigos de barra que se usaron para recibir los productos
***************************************************************************************/
function guardar_recepcion($id_info_rma,$cantidad,$id_prod_esp,$cb_insertados)
{
	global $_ses_user,$db;
	$fecha=date("Y-m-d H:i:s");
	$db->StartTrans();
	if(is_array($cb_insertados))
	{$sql="select id_rma_recepcion from rma_recepcion where id_info_rma=$id_info_rma";
	$res=sql($sql,"$sql") or fin_pagina();

	if ($res->Recordcount()>0) { //update
		$id_rma_recepcion=$res->fields['id_rma_recepcion'];
		$sql="update rma_recepcion set cantidad=cantidad + $cantidad
	          where id_info_rma=$id_info_rma";
	}
	else {	 //insert
		$sql_id="select nextval('stock.rma_recepcion_id_rma_recepcion_seq') as id_rma_recepcion";
		$res_id=sql($sql_id," $sql_id") or fin_pagina();
		$id_rma_recepcion=$res_id->fields['id_rma_recepcion'];
		$sql="insert into rma_recepcion
	          (id_rma_recepcion,cantidad,id_info_rma,id_deposito)
	           values ($id_rma_recepcion,$cantidad,$id_info_rma,2)";
	}
	sql($sql,"<br>Error al insertar/actualizar la recepcion de RMA<br>") or fin_pagina();

	$sql_id="select nextval('stock.log_rma_recepcion_id_log_recepcion_seq') as id_log_recepcion";
	$res_id=sql($sql_id,"<br>Error al traer la secuencia de log de recepcion<br>") or fin_pagina();
	$id_log_recepcion=$res_id->fields["id_log_recepcion"];
	$sql_log="insert into log_rma_recepcion
	            (id_log_recepcion,usuario,fecha, cantidad_recibida,id_prod_esp,id_rma_recepcion)
	            values ($id_log_recepcion,'".$_ses_user['name']."','$fecha',$cantidad,$id_prod_esp,$id_rma_recepcion)";
	sql($sql_log,"<br>Error al agregar el log de recepcion de RMA<br>") or fin_pagina() ;

	foreach ($cb_insertados as $cb_insertar)
	{
		//insertamos en la tabla codigos_barra_entregados, para tener el registro de dichos datos
		$query="insert into stock.rma_cb_recibidos (codigo_barra,id_log_recepcion)
	                values('$cb_insertar',$id_log_recepcion)";
		sql($query,"<br>Error al insertar los codigos de barra recibidos (CB $cb_insertar)<br>") or fin_pagina();

	}//de foreach ($cb_insertados as $cb_insertar)
	}//de if(is_array($cb_insertados))

	$db->CompleteTrans();
}//de function guardar_recepcion($id_info_rma,$cantidad,$id_prod_especifico)

function mostrar_ing_egr($id_prod_esp,$id_deposito)
{
	global $atrib_tr;
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
		&nbsp;&nbsp;
		<?$link=encode_link("stock_descontar_ver_id.php",array("id_prod_esp"=>$id_prod_esp,"id_deposito"=>$id_deposito));?>
        <a target=_blank title='Ver detalle de ID' href='<?=$link?>'>
        Ver ID
        </a>
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
	       <td width=30% align=Center><b>Tipo de Mov.</b></td>
	       <td width=10% align=Center><b>Usuario</b></td>
	       <td width=40% align=Center><b>Comentarios</b></td>
	       <td width=10% align=Center><b>Fecha</b></td>
	       <td width=10% align=Center><b>Cantidad</b></td>
	    </tr>
	<?
	$cantidad=$entradas_salidas->recordcount();
	$total_ingresos=0;

	for($i=0;$i<$cantidad;$i++) {
		//Saco el log de los  ingresos
		if($entradas_salidas->fields["clase_mov"]==1) {
			$total_ingresos+=$entradas_salidas->fields["cantidad"];
			?>
	    <tr <?=$atrib_tr?>>
	      <td align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
	      <td align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
	      <td align=left><?if ($entradas_salidas->fields["comentario"]) echo $entradas_salidas->fields["comentario"]; else echo "&nbsp;";?> </td>
	      <td align=center><?=fecha($entradas_salidas->fields["fecha_mov"])?></td>
	      <td align=right><?=$entradas_salidas->fields["cantidad"];?></td>
	    </tr>
			<?
		}//del if
		$entradas_salidas->movenext();
	}
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
	       <td width=30% align=Center><b>Tipo de Mov.</b></td>
	       <td width=10% align=Center><b>Usuario</b></td>
	       <td width=40% align=Center><b>Comentarios</b></td>
	       <td width=10% align=Center><b>Fecha</b></td>
	       <td width=10% align=Center><b>Cantidad</b></td>
	    </tr>
	<?
	$cantidad=$entradas_salidas->recordCount();
	$total_egresos=0;
	for($i=0;$i<$cantidad;$i++) {
		//Obtengo el log de los egresos
		if ($entradas_salidas->fields["clase_mov"]==2) {
			$total_egresos+=abs($entradas_salidas->fields["cantidad"]);
	?>
	    <tr <?=$atrib_tr?>>
	      <td align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
	      <td align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
	      <td align=left><? if ($entradas_salidas->fields["comentario"]) echo $entradas_salidas->fields["comentario"]; else echo "&nbsp;" ?></td>
	      <td align=center><?=fecha($entradas_salidas->fields["fecha_mov"])?></td>
	      <td align=right><?=abs($entradas_salidas->fields["cantidad"]);?></td>
	    </tr>
	<?
		}//del if
		$entradas_salidas->movenext();
	}//del for
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
}//de function mostrar_ing_egr($id_prod_esp,$id_deposito)

?>
