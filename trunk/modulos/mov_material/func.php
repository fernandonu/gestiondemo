<?
/*AUTOR: MAC
  FECHA: 02/07/04

$Author: fernando $
$Revision: 1.76 $
$Date: 2007/02/13 21:24:55 $
*/
require_once("../../config.php");
require_once("../stock/funciones.php");

/**
 *autorizar_entregar_pm 
 * funcion que para los pm de produccion san luis al autorizarlos los entrega
 * de inmediato , osea que no esta el formulario de entregas
 * @param unknown_type $id_movimiento_material
 */
function autorizar_venta_directa($id_movimiento_material) {
 global $_ses_user,$db; 

  
  $titulo_pagina = "Movimiento de Material";
  $fecha_hoy     = date("Y-m-d H:i:s",mktime());
  
  
  $sql = "select deposito_origen from movimiento_material 
          where id_movimiento_material = $id_movimiento_material";
  $res = sql($sql) or fin_pagina();
  $deposito_origen  = $res->fields["deposito_origen"];
  
  
  $sql = "select id_factura from movimiento_factura where id_movimiento_material = $id_movimiento_material";
  $fact = sql($sql) or fin_pagina();  
  $id_factura       = $fact->fields["id_factura"];
  
  $sql = " select id_detalle_movimiento,cantidad,id_prod_esp from detalle_movimiento 
           where id_movimiento_material = $id_movimiento_material";
  $detalle_mov = sql($sql) or fin_pagina();
  
  for($i=0; $i < $detalle_mov->recordcount(); $i++) {  	  
  	
  	   $id_detalle_movimiento  = $detalle_mov->fields["id_detalle_movimiento"];	
  	   $cantidad  = $detalle_mov->fields["cantidad"];
  	   $id_prod_esp = $detalle_mov->fields["id_prod_esp"];
       $sql = "select id_recibidos_mov,cantidad
               from recibidos_mov where id_detalle_movimiento=$id_detalle_movimiento 
               and ent_rec=0";
       $detalle_rec = sql($sql) or fin_pagina();
       $id_recibido_mov = $detalle_rec->fields["id_recibidos_mov"];

       if($id_recibido_mov=="") {
	 	          //insert
		          $sql    = "select nextval('recibidos_mov_id_recibidos_mov_seq') as id_recibido_mov";
		          $res_1    = sql($sql) or fin_pagina();
		          $id_recibido_mov = $res_1->fields["id_recibido_mov"];
		          $sql="insert into recibidos_mov(id_recibidos_mov,id_detalle_movimiento,cantidad,ent_rec)
		          values($id_recibido_mov,$id_detalle_movimiento,$cantidad,0)";
                 }else {
	                //update
	                $sql="update recibidos_mov set cantidad=cantidad+$cantidad
	                      where id_recibidos_mov=$id_recibido_mov";
                 }//del else
        sql($sql) or fin_pagina();
        $sql ="insert into log_recibidos_mov(id_recibidos_mov,usuario,fecha,cantidad_recibida,tipo)
               values($id_recibido_mov,'".$_ses_user["name"]."','$fecha_hoy',$cantidad,'entrega')";
        sql($sql) or fin_pagina();
        //eliminamos las reservas hechas para este movimiento
        $comentario_stock="Utilización de los productos reservados por el $titulo_pagina Nº $id_movimiento";
        $id_tipo_movimiento=7;
        
        
        //tengo que eliminar del stock los productos correspondientes        
        descontar_reserva($id_prod_esp,$cantidad,$deposito_origen,$comentario_stock,$id_tipo_movimiento,$id_fila="",$id_detalle_movimiento);
        //pongo las banderas de la factura en cuenta asi se produce
        //el movimiento correcto en el balance
  $detalle_mov->movenext();     
  }//del for 
  
  if ($id_factura) {
  
  $sql = "update licitaciones.cobranzas 
             set renglones_entregados=1, licitacion_entregada=1
             where cobranzas.id_factura=$id_factura";
  sql($sql) or fin_pagina();
  }           
        
        
  

} //de la function autorizar_venta_directa



/**
 *autorizar_entregar_pm 
 * funcion que para los pm de produccion san luis al autorizarlos los entrega
 * de inmediato , osea que no esta el formulario de entregas
 * @param unknown_type $id_movimiento_material
 */
function autorizar_entregar_pm($id_movimiento_material) {
 global $_ses_user,$db,$id_stock_rma;
  

  //$db->StartTrans();
  $sql="select id_deposito from general.depositos where nombre='RMA-Produccion-San Luis' ";
  $res = sql($sql) or fin_pagina();
  $id_stock_rma = $res->fields["id_deposito"];
  
  $titulo_pagina = "Movimiento de Material";
  $fecha_hoy     = date("Y-m-d H:i:s",mktime());
  
  
  $sql = "select deposito_origen,deposito_destino from movimiento_material 
          where id_movimiento_material = $id_movimiento_material";
  $res = sql($sql) or fin_pagina();
  
  $deposito_origen  = $res->fields["deposito_origen"];
  $deposito_destino = $res->fields["deposito_destino"];
  
  $sql = " select id_detalle_movimiento,cantidad,id_prod_esp from detalle_movimiento 
           where id_movimiento_material = $id_movimiento_material";
  $detalle_mov = sql($sql) or fin_pagina();
  
  for($i=0; $i < $detalle_mov->recordcount(); $i++){  	  
  	   $id_detalle_movimiento  = $detalle_mov->fields["id_detalle_movimiento"];	
  	   $cantidad  = $detalle_mov->fields["cantidad"];
  	   $id_prod_esp = $detalle_mov->fields["id_prod_esp"];
       $sql = "select id_recibidos_mov,cantidad
               from recibidos_mov where id_detalle_movimiento=$id_detalle_movimiento 
               and ent_rec=0";
       $detalle_rec = sql($sql) or fin_pagina();
       $id_recibido_mov = $detalle_rec->fields["id_recibidos_mov"];

       if($id_recibido_mov=="") {
	 	          //insert
		          $sql    = "select nextval('recibidos_mov_id_recibidos_mov_seq') as id_recibido_mov";
		          $res_1    = sql($sql) or fin_pagina();
		          $id_recibido_mov = $res_1->fields["id_recibido_mov"];
		          $sql="insert into recibidos_mov(id_recibidos_mov,id_detalle_movimiento,cantidad,ent_rec)
		          values($id_recibido_mov,$id_detalle_movimiento,$cantidad,0)";
                 }else {
	                //update
	                $sql="update recibidos_mov set cantidad=cantidad+$cantidad
	                      where id_recibidos_mov=$id_recibido_mov";
                 }//del else
        sql($sql) or fin_pagina();
        $sql ="insert into log_recibidos_mov(id_recibidos_mov,usuario,fecha,cantidad_recibida,tipo)
               values($id_recibido_mov,'".$_ses_user["name"]."','$fecha_hoy',$cantidad,'entrega')";
        sql($sql) or fin_pagina();
        //eliminamos las reservas hechas para este movimiento
        $comentario_stock="Utilización de los productos reservados por el $titulo_pagina Nº $id_movimiento";
        $id_tipo_movimiento=7;
        descontar_reserva($id_prod_esp,$cantidad,$deposito_origen,$comentario_stock,$id_tipo_movimiento,$id_fila="",$id_detalle_movimiento);
        //ahora incremento el stock destino
        
        if($deposito_destino==$id_stock_rma) {
	         $comentario_rma="Ingreso de productos a RMA mediante el $titulo_pagina Nº $id_movimiento";
             $tipo_log="Creacion MM Nº $id_movimiento";
             $cb_insertados=array();
             $rma_san_luis = 1;
             incrementar_stock_rma($id_prod_esp,$cantidad,"",$comentario_rma,"",$cb_insertados,"",1,"","","","null","",$nro_caso,"",$tipo_log,"",$id_movimiento_material,$rma_san_luis);
             //guardamos el id de proveedor elegido para RMA, en la fila del PM o MM (en la tabla detalle_movimiento)
             /*
             $query="update mov_material.detalle_movimiento set id_proveedor=$id_proveedor_rma where id_detalle_movimiento=$id_detalle_movimiento";
             sql($query,"<br>Error al actualizar el proveedor de RMA del producto<br>") or fin_pagina();
             */
        }
        else {        
        $comentario = " Ingreso de Productos mediante PM N°: $id_movimiento_material";
        agregar_stock($id_prod_esp,$cantidad,$deposito_destino,$comentario,13,"disponible");
        }        
        
  $detalle_mov->movenext();     
  }//del for
  
}//de la function



//Funcion que para un pm de servicio tecnico acumulado
//me cancela la reserva de los productos en el stock origen
//y los productos de lista de mercaderia entrante me los ingresa
//en rma
function autorizar_pm_acumulado_servicio_tecnico($id_mov){

global $db;	
 
  $db->starttrans();
  
  //traigo el deposito origen
  $sql = "select deposito_origen from movimiento_material where id_movimiento_material = $id_mov"; 
  $res = sql($sql) or fin_pagina();
  $id_deposito_oriden = $res->fields["deposito_origen"];
  	                     
  //traigo el detalle de los movimientos a liberar          	       
  $sql="select * from detalle_movimiento where id_movimiento_material=$id_mov";
  $res = sql($sql) or fin_pagina();

  $comentario = " Autorizacion de PM acumulado de servicio tecnico nro: $id_mov";
  $id_tipo_movimiento = 7;

  //por cada detalle voy liberando las reservas del pm autorizado 
  for($i=0;$i<$res->recordcount();$i++) {
  	    $id_prod_esp = $res->fields["id_prod_esp"];
        $cantidad = $res->fields["cantidad"];
        $id_detalle_movimiento = $res->fields["id_detalle_movimiento"];
        descontar_reserva($id_prod_esp,$cantidad,$id_deposito_oriden,$comentario,$id_tipo_movimiento,"",$id_detalle_movimiento,"");
  $res->movenext();      
  }//del for
           	             
             	         
  //Inserto la Mercaderia entrante en el stock de RMA 
  $sql = "select * from mercaderia_entrante where id_movimiento_material = $id_mov";
  $res = sql($sql) or fin_pagina();
			             
			             
  $comentario = "Producto de lista de mercaderia entrante del PM nro: $id_mov";
			             
  for($i=0;$i<$res->recordcount();$i++){
	   $id_prod_esp = $res->fields["id_prod_esp"];
	   $cantidad = $res->fields["cantidad"];
	   $descripcion = $res->fields["descripcion"];              	
	   incrementar_stock_rma($id_prod_esp,$cantidad,"",$comentario,1,"",$descripcion,1,"","","","","","","","","",$id_mov);
	   $res->movenext();
  }//del 
  $db->completetrans();

 } //de la funcion acumulado de servicio tecnico
 
function autorizar_producto_san_luis($id_mov){

global $db;	
 
  $db->starttrans();
  $fecha_hoy=date("Y-m-d H:i:s",mktime());
  
  //traigo el deposito origen
  $sql = "select deposito_origen,deposito_destino
          from movimiento_material where id_movimiento_material = $id_mov"; 
  $res = sql($sql) or fin_pagina();
  $id_deposito_origen = $res->fields["deposito_origen"];
  $id_deposito_destino = $res->fields["deposito_destino"];
  	                     
  //traigo el detalle de los movimientos a liberar          	       
  $sql="select * from detalle_movimiento where id_movimiento_material=$id_mov";
  $res = sql($sql) or fin_pagina();

  $comentario = " Autorizacion de PM Producto San Luis nro: $id_mov";
  $id_tipo_movimiento = 7;

  //por cada detalle voy liberando las reservas del pm autorizado 
  for($i=0;$i<$res->recordcount();$i++) {
  	    $id_prod_esp = $res->fields["id_prod_esp"];
        $cantidad    = $res->fields["cantidad"];
        $id_detalle_movimiento = $res->fields["id_detalle_movimiento"];   
        ///////////////////////////////////////
       $sql = "select id_recibidos_mov,cantidad
               from recibidos_mov where id_detalle_movimiento=$id_detalle_movimiento 
               and ent_rec=0";
       $detalle_rec = sql($sql) or fin_pagina();
       $id_recibido_mov = $detalle_rec->fields["id_recibidos_mov"];
       if($id_recibido_mov=="") {
	 	          //insert
		          $sql    = "select nextval('recibidos_mov_id_recibidos_mov_seq') as id_recibido_mov";
		          $res_1    = sql($sql) or fin_pagina();
		          $id_recibido_mov = $res_1->fields["id_recibido_mov"];
		          $sql="insert into recibidos_mov(id_recibidos_mov,id_detalle_movimiento,cantidad,ent_rec)
		          values($id_recibido_mov,$id_detalle_movimiento,$cantidad,0)";
                 }else {
	                //update
	                $sql="update recibidos_mov set cantidad=cantidad+$cantidad
	                      where id_recibidos_mov=$id_recibido_mov";
                 }//del else
        sql($sql) or fin_pagina();
        $sql ="insert into log_recibidos_mov(id_recibidos_mov,usuario,fecha,cantidad_recibida,tipo)
               values($id_recibido_mov,'".$_ses_user["name"]."','$fecha_hoy',$cantidad,'entrega')";
        sql($sql) or fin_pagina();
        ///////////////////////////////////////        
        descontar_reserva($id_prod_esp,$cantidad,$id_deposito_origen,$comentario,$id_tipo_movimiento,"",$id_detalle_movimiento,"");
  $res->movenext();      
  }//del for
             	         
  //Inserto la Mercaderia entrante en el stock BS AS 
  $sql = "select * from mercaderia_entrante where id_movimiento_material = $id_mov";
  $res = sql($sql) or fin_pagina();
  $comentario = "Producto San Luis perteneciente al PM nro: $id_mov";
  for($i=0;$i<$res->recordcount();$i++){
	   $id_prod_esp = $res->fields["id_prod_esp"];
	   $cantidad    = $res->fields["cantidad"];
	   $descripcion = $res->fields["descripcion"]; 
	   //el id_tipo_movimiento le hardcodeo uno de la tabla stock.tipo_movimiento
	   $id_tipo_movimiento='13';
	   //el ingreso del producto es a "disponible" por lo tanto la funcion no toma en cuenta los parametros que siguen
	   $a_stock='disponible';
	   agregar_stock($id_prod_esp,$cantidad,$id_deposito_destino,$comentario,$id_tipo_movimiento,$a_stock,$id_tipo_reserva,"",$id_detalle_movimiento,$id_licitacion,$nro_caso);
	   $res->movenext();
  }//del for
  $db->completetrans();

 } //de la funcion acumulado de servicio tecnico 
 
 
//function autorizar_esp_pm_acumulado_servicio_tecnico 
function autorizar_esp_pm_acumulado_servicio_tecnico($id_mov){

global $db;	
 
  $db->starttrans();
  
  //traigo el deposito origen
  $sql = "select deposito_origen from movimiento_material where id_movimiento_material = $id_mov"; 
  $res = sql($sql) or fin_pagina();
  $id_deposito_oriden = $res->fields["deposito_origen"];
  	                     
  //traigo el detalle de los movimientos a liberar          	       
  $sql="select * from detalle_movimiento where id_movimiento_material=$id_mov";
  $res = sql($sql) or fin_pagina();

  $comentario = " Autorizacion de PM acumulado de servicio tecnico nro: $id_mov";
  $id_tipo_movimiento = 7;

  //por cada detalle voy liberando las reservas del pm autorizado 
  for($i=0;$i<$res->recordcount();$i++) {
  	    $id_prod_esp = $res->fields["id_prod_esp"];
        $cantidad = $res->fields["cantidad"];
        $id_detalle_movimiento = $res->fields["id_detalle_movimiento"];
        descontar_reserva($id_prod_esp,$cantidad,$id_deposito_oriden,$comentario,$id_tipo_movimiento,"",$id_detalle_movimiento,"");
  $res->movenext();      
  }//del for
           	             
             	         
  //Inserto la Mercaderia entrante en el stock BS AS 
  
  $sql = "select * from mercaderia_entrante where id_movimiento_material = $id_mov";
  $res = sql($sql) or fin_pagina();
			             
			             
  $comentario = "Producto de lista de mercaderia entrante del PM nro: $id_mov, a travez de una Autorizacion Especial";
			             
  for($i=0;$i<$res->recordcount();$i++){
	   $id_prod_esp = $res->fields["id_prod_esp"];
	   $cantidad = $res->fields["cantidad"];
	   $descripcion = $res->fields["descripcion"]; 
	   //el deposito origen es Buenos Aires que es igual a 2 segun la tabla general.depositos 
	   $deposito_origen='2';
	   //el id_tipo_movimiento le hardcodeo uno de la tabla stock.tipo_movimiento
	   $id_tipo_movimiento='13';
	   //el ingreso del producto es a "disponible" por lo tanto la funcion no toma en cuenta los parametros que siguen
	   $a_stock='disponible';
	   agregar_stock($id_prod_esp,$cantidad,$deposito_origen,$comentario,$id_tipo_movimiento,$a_stock,$id_tipo_reserva,"",$id_detalle_movimiento,$id_licitacion,$nro_caso);
	   $res->movenext();
  }//del for
  $db->completetrans();

 } //de la funcion acumulado de servicio tecnico
 
function autorizar_esp_ckd_pm_acumulado_servicio_tecnico($id_mov){

global $db;	
 
  $db->starttrans();
  
  //traigo el deposito origen
  $sql = "select deposito_origen from movimiento_material where id_movimiento_material = $id_mov"; 
  $res = sql($sql) or fin_pagina();
  $id_deposito_oriden = $res->fields["deposito_origen"];
  	                     
  //traigo el detalle de los movimientos a liberar          	       
  $sql="select * from detalle_movimiento where id_movimiento_material=$id_mov";
  $res = sql($sql) or fin_pagina();

  $comentario = " Autorizacion de PM acumulado de servicio tecnico nro: $id_mov";
  $id_tipo_movimiento = 7;

  //por cada detalle voy liberando las reservas del pm autorizado 
  for($i=0;$i<$res->recordcount();$i++) {
  	    $id_prod_esp = $res->fields["id_prod_esp"];
        $cantidad = $res->fields["cantidad"];
        $id_detalle_movimiento = $res->fields["id_detalle_movimiento"];
        descontar_reserva($id_prod_esp,$cantidad,$id_deposito_oriden,$comentario,$id_tipo_movimiento,"",$id_detalle_movimiento,"");
  $res->movenext();      
  }//del for
           	             
             	         
  //Inserto la Mercaderia entrante en el stock BS AS 
  
  $sql = "select * from mercaderia_entrante where id_movimiento_material = $id_mov";
  $res = sql($sql) or fin_pagina();
			             
			             
  $comentario = "Producto de lista de mercaderia entrante del PM nro: $id_mov, a travez de una Autorizacion Especial";
			             
  for($i=0;$i<$res->recordcount();$i++){
	   $id_prod_esp = $res->fields["id_prod_esp"];
	   $cantidad = $res->fields["cantidad"];
	   $descripcion = $res->fields["descripcion"]; 
	   //el deposito origen es San Luis que es igual a 1 segun la tabla general.depositos 
	   $deposito_origen='1';
	   //el id_tipo_movimiento le hardcodeo uno de la tabla stock.tipo_movimiento
	   $id_tipo_movimiento='13';
	   //el ingreso del producto es a "disponible" por lo tanto la funcion no toma en cuenta los parametros que siguen
	   $a_stock='disponible';
	   agregar_stock($id_prod_esp,$cantidad,$deposito_origen,$comentario,$id_tipo_movimiento,$a_stock,$id_tipo_reserva,"",$id_detalle_movimiento,$id_licitacion,$nro_caso);
	   $res->movenext();
  }//del for
  $db->completetrans();

 } //de la funcion autorizar_esp_ckd_pm_acumulado_servicio_tecnico


/************************************************************************************************************
 * FUNCIONES PARA GENERAR LAS FILAS DEL PM DEPENDIENDO DEL TIPO DE PM REALIZADO
*************************************************************************************************************/
//recupera los productos solicitados desde lista de materiales
function get_items_producto_lista_material($id_entrega_estimada,$id_deposito, $id_licitacion="")
{
	//cantidades totales
	$consulta0="/*CANTIDADES RESERVADAS PARA LA LICITACION Y CANTIDADES YA USADAS EN PM*/
	select distinct prod.id_prod_esp, tmp0.id_licitacion,
	case when cantidad_pm is null then 0 else cantidad_pm end as cantidad_pm,
	case when cantidad_res is null then 0 else cantidad_res end as cantidad_res
from
	(
		select (plm.cantidad*renglones_oc.cantidad)as cantidad,plm.id_prod_esp, renglon.id_licitacion
		from mov_material.producto_lista_material plm
			join general.producto_especifico pe using (id_prod_esp)
			join licitaciones.renglones_oc on(renglones_oc.id_renglon=plm.id_renglon and renglones_oc.estado is null)
			join licitaciones.renglon on (renglon.id_renglon=plm.id_renglon)
			join licitaciones.subido_lic_oc using(id_subir)
		where subido_lic_oc.id_entrega_estimada=$id_entrega_estimada
        ) as prod
	left join(
	    select id_prod_esp, movimiento_material.id_licitacion, sum(detalle_movimiento.cantidad) as cantidad_pm
		from mov_material.movimiento_material
			join mov_material.detalle_movimiento using(id_movimiento_material)
		where es_pedido_material=1 and estado<>3
			and (id_entrega_estimada=$id_entrega_estimada)
		group by id_prod_esp, movimiento_material.id_licitacion
	)as tmp0 on(tmp0.id_prod_esp=prod.id_prod_esp)
	left join(
		select id_prod_esp, id_licitacion, sum(cantidad_reservada) as cantidad_res
		from stock.en_stock
			left join stock.detalle_reserva using (id_en_stock)
		where id_deposito=2 and id_licitacion=$id_licitacion and detalle_reserva.id_detalle_movimiento is null
		group by id_prod_esp, id_licitacion
	)as tmp1 on (tmp1.id_prod_esp=prod.id_prod_esp)";

	//traemos todas las reservas hechas para la licitacion, cuyos productos especificos no estan indicados en ningun
	//listado de material de esa licitacion.
	$reservas_no_indicadas="/*RESERVAS PARA LICITACION QUE NO ESTAN ESPECIFICADOS EN NINGUN LISTADO DE MATERIAL*/
		select a.id_prod_esp, a.id_licitacion,descripcion,precio_stock,
			case when cantidad_res is null then 0 else cantidad_res end as cantidad_res
		from
		(
		 select id_prod_esp, descripcion,precio_stock,id_licitacion, sum(cantidad_reservada) as cantidad_res
				from stock.en_stock
				join general.producto_especifico using(id_prod_esp)
				left join stock.detalle_reserva using (id_en_stock)

				where id_deposito=2 and id_licitacion=$id_licitacion and detalle_reserva.id_detalle_movimiento is null
				group by id_prod_esp,descripcion,precio_stock,id_licitacion
		)as a
		where id_prod_esp not in (
			select plm.id_prod_esp
			from mov_material.producto_lista_material plm
				join general.producto_especifico pe using (id_prod_esp)
				join licitaciones.renglones_oc on(renglones_oc.id_renglon=plm.id_renglon and renglones_oc.estado is null)
				join licitaciones.renglon on (renglon.id_renglon=plm.id_renglon)
				join licitaciones.subido_lic_oc using(id_subir)
			where subido_lic_oc.id_entrega_estimada=$id_entrega_estimada
        )";

	//controlamos cuantos seguimientos hay para la licitacion y de acuerdo a eso es la consulta por cantidades requeridas
	//que hacemos
	$query="select id_entrega_estimada from licitaciones.entrega_estimada where id_licitacion=$id_licitacion";
	$seg_cant=sql($query,"<br>Error al traer las entregas estimadas de la licitacion<br>") or fin_pagina();

	if($seg_cant->RecordCount()<2)
	{
		//productos requeridos
		$consulta1="/*CANTIDADES REQUERIDAS EN LOS RENGLONES DE LA OC DE LA LICITACION*/
		select prod.*, sum(case when plm.cantidad is null then 0 else plm.cantidad end) as cantidad,elegido
		from(
			select descripcion,id_prod_esp, precio_stock
			from general.producto_especifico pe
	        ) as prod
	        join(
			select sum(plm.cantidad*renglones_oc.cantidad)as cantidad,plm.id_prod_esp, renglon.id_licitacion,plm.elegido
			from mov_material.producto_lista_material plm
				join general.producto_especifico pe using (id_prod_esp)
				join licitaciones.renglones_oc on(renglones_oc.id_renglon=plm.id_renglon and renglones_oc.estado is null)
				join licitaciones.renglon on (renglon.id_renglon=plm.id_renglon)
				join licitaciones.subido_lic_oc using(id_subir)
			where subido_lic_oc.id_entrega_estimada=$id_entrega_estimada
			group by plm.id_prod_esp, renglon.id_licitacion,plm.elegido
		) plm using (id_prod_esp)
		group by prod.id_prod_esp, prod.descripcion, prod.precio_stock,elegido
	    ";
	}
	else //$seg_cant->RecordCount()>=2
	{
		$consulta1="/*CANTIDADES REQUERIDAS EN LOS RENGLONES DE LA OC DE LA LICITACION*/
		select prod.*, sum(case when plm.cantidad is null then 0 else plm.cantidad end) as cantidad,elegido
		from(
			select descripcion,id_prod_esp, precio_stock
			from general.producto_especifico pe
	        ) as prod
	        join(
			select distinct(plm.cantidad*renglones_oc.cantidad)as cantidad,plm.id_prod_esp, renglon.id_licitacion,plm.elegido
			from mov_material.producto_lista_material plm
				join general.producto_especifico pe using (id_prod_esp)
				join licitaciones.renglones_oc on(renglones_oc.id_renglon=plm.id_renglon and renglones_oc.estado is null)
				join licitaciones.renglon on (renglon.id_renglon=plm.id_renglon)
				join licitaciones.subido_lic_oc using(id_subir)
			where subido_lic_oc.id_entrega_estimada=$id_entrega_estimada

		) plm using (id_prod_esp)
		group by prod.id_prod_esp, prod.descripcion, prod.precio_stock,elegido";
	}//del else de if($seg_cant->RecordCount()<2)

	//stock disponible
	$consulta2="/*CANTIDAD DISPONIBLE EN STOCK*/
		select id_prod_esp, case when en_stock.cant_disp is null then 0 else en_stock.cant_disp end as cantidad_st
		from stock.en_stock
		where id_deposito=2
		order by id_prod_esp";

  $rta_consulta0=sql($consulta0, "c113 - func.php") or fin_pagina();
  $rta_reservas_no_indicadas=sql($reservas_no_indicadas, "<br>Error al traer las reservas no indicadas<br>") or fin_pagina();
  $rta_consulta1=sql($consulta1, "c114 - func.php") or fin_pagina();
  $rta_consulta2=sql($consulta2, "c115 - func.php") or fin_pagina();

  $i=0;
  $disponibles=array();
  $solicitados=array();
  $en_stock=array();
  //Guardamos por cada fila: CANTIDADES RESERVADAS PARA LA LICITACION Y CANTIDADES YA USADAS EN PM
  while (!$rta_consulta0->EOF)
  {
 		$disponibles[$rta_consulta0->fields["id_prod_esp"]]["cantidad_pm"]=$rta_consulta0->fields["cantidad_pm"];
 		$disponibles[$rta_consulta0->fields["id_prod_esp"]]["cantidad_res"]=$rta_consulta0->fields["cantidad_res"];
 		$rta_consulta0->moveNext();
  }//de while (!$rta_consulta0->EOF)

  //Guardamos por cada fila: CANTIDADES REQUERIDAS EN LOS RENGLONES DE LA OC DE LA LICITACION
  while (!$rta_consulta1->EOF)
  {
	  	$solicitados[$rta_consulta1->fields["id_prod_esp"]]["descripcion"]=$rta_consulta1->fields["descripcion"];
	  	$solicitados[$rta_consulta1->fields["id_prod_esp"]]["id_prod_esp"]=$rta_consulta1->fields["id_prod_esp"];
	  	$solicitados[$rta_consulta1->fields["id_prod_esp"]]["precio_stock"]=$rta_consulta1->fields["precio_stock"];
	  	$solicitados[$rta_consulta1->fields["id_prod_esp"]]["cantidad"]=$rta_consulta1->fields["cantidad"];
	  	$rta_consulta1->moveNext();
  }//de while (!$rta_consulta1->EOF)

  //Guardamos por cada producto especifico presente en el stock de bs as: CANTIDAD DISPONIBLE EN STOCK
  while (!$rta_consulta2->EOF)
  {
  		$en_stock[$rta_consulta2->fields["id_prod_esp"]]=$rta_consulta2->fields["cantidad_st"];
  		$rta_consulta2->moveNext();
  }//de while (!$rta_consulta2->EOF)

    //iteractuo con todos esos productos
    $total=0;
    $resultado=$rta_consulta1;
    $resultado->moveFirst();
    //recorremos cada producto de los requeridos en los renglones de la licitacion
    $i=0;
    for($i;$i<$resultado->recordcount();$i++)
    {
      $items[$i]["id_prod_esp"]=$resultado->fields["id_prod_esp"];

      $cantidad_pm=(($disponibles[$items[$i]["id_prod_esp"]]["cantidad_pm"])?$disponibles[$items[$i]["id_prod_esp"]]["cantidad_pm"]:0);
      $cantidad_res=(($disponibles[$items[$i]["id_prod_esp"]]["cantidad_res"])?$disponibles[$items[$i]["id_prod_esp"]]["cantidad_res"]:0);
      $cantidad_st=(($en_stock[$items[$i]["id_prod_esp"]])?$en_stock[$items[$i]["id_prod_esp"]]:0);

      $items[$i]["cantidad"]=$items[$i]["cantidad_orig"]=$resultado->fields["cantidad"];
      $items[$i]["cantidad_pm"]=$cantidad_pm;
      $items[$i]["cantidad_res"]=$cantidad_res;
      if($resultado->fields["elegido"]==1)
       $items[$i]["cantidad_st"]=$cantidad_st;
      else
       $items[$i]["cantidad_st"]=0;
      $items[$i]["descripcion"]=$resultado->fields["descripcion"];
      $items[$i]["precio"]=$resultado->fields["precio_stock"];
      $remanente=$items[$i]["cantidad"] - $items[$i]["cantidad_pm"];

      //print_r($resultado->fields);
      //echo "<br>Para el producto ".$items[$i]["id_prod_esp"].", la cantidad PM es: ".$items[$i]["cantidad_pm"]." - la cantidad reservada es:  ".$items[$i]["cantidad_res"]." y la cantidad en stock es: ".$items[$i]["cantidad_st"]."<br><br>";
      //echo "El remanente actual para ".$items[$i]["id_prod_esp"]." es $remanente<br>----------------------------------------------<br><br>";


      		if ($items[$i]["cantidad_st"]+$items[$i]["cantidad_res"]<$remanente)
			{
				$remanente=$items[$i]["cantidad_st"]+$items[$i]["cantidad_res"];
				$en_stock[$items[$i]["id_prod_esp"]]=0;
				$disponibles[$items[$i]["id_prod_esp"]]["cantidad_res"]=0;
			}//de if ($items[$i]["cantidad_st"]+$items[$i]["cantidad_res"]<$remanente)
			else
			{
				if ($disponibles[$items[$i]["id_prod_esp"]]["cantidad_res"]<=$remanente)
				{

					$disponibles[$items[$i]["id_prod_esp"]]["cantidad_res"]=0;
					$en_stock[$items[$i]["id_prod_esp"]]-=$remanente;
				}
				else
				{
					$disponibles[$items[$i]["id_prod_esp"]]["cantidad_res"]-=$remanente;
				}
			}//del else de if ($items[$i]["cantidad_st"]+$items[$i]["cantidad_res"]<$remanente)

			$items[$i]["cantidad"]=$remanente;
			//print_r($items[$i]); echo("<br>");///////////////////////////////////////////////////////////////////////////////////

			$total++;
      $resultado->movenext();
    }//de for($i=0;$i<$resultado->recordcount();$i++)

    //recorremos las reservas para esta licitacion cuyos productos especificos no fueron indicados en el listado de material
    //asi los agregamos en el PM
    while (!$rta_reservas_no_indicadas->EOF)
    {
      $items[$i]["id_prod_esp"]=$rta_reservas_no_indicadas->fields["id_prod_esp"];
      $items[$i]["cantidad"]=$items[$i]["cantidad_orig"]=$rta_reservas_no_indicadas->fields["cantidad_res"];
      $items[$i]["cantidad_res"]=$rta_reservas_no_indicadas->fields["cantidad_res"];

      $items[$i]["descripcion"]=$rta_reservas_no_indicadas->fields["descripcion"];
      $items[$i]["precio"]=$rta_reservas_no_indicadas->fields["precio_stock"];
      $i++;
      $total++;
     $rta_reservas_no_indicadas->MoveNext();
    }//de while(!$rta_reservas_no_indicadas->EOF)

    $items["cantidad"]=$total;
 return $items;
} //fin de la funcion

//recupera los productos reservados para OC asociadas al nrocaso pasado como parametro
function get_items_producto_lista_material_packaging($productos_seleccionados,$deposito_destino,$cantidad_maquinas)
{
	$sql="select en_stock.id_prod_esp,cant_disp,
	      precio_stock,descripcion
          from stock.en_stock
          join general.producto_especifico on  producto_especifico.id_prod_esp=en_stock.id_prod_esp
          where en_stock.id_prod_esp in ($productos_seleccionados)
          		and id_deposito=2";
   $prod_info=sql($sql,"<br>Error al traer los productos de packaging desde el stock<br>") or fin_pagina();

   $total=0;
   $i=0;
    while (!$prod_info->EOF)
    {
     //si la cantidad disponible no es mayor o igual que la cantidad de maquinas requeridas, damos el aviso y no lo
     //agregamos entre las filas del PM
	     if($cantidad_maquinas<=$prod_info->fields["cant_disp"])
		 {    $items[$i]["id_prod_esp"]=$prod_info->fields["id_prod_esp"];
		      $items[$i]["cantidad"]=$items[$i]["cantidad_orig"]=$cantidad_maquinas;

		      $items[$i]["descripcion"]=$prod_info->fields["descripcion"];
		      $items[$i]["precio"]=$prod_info->fields["precio_stock"];
		      $total++;
		 }//de if($cantidad_maquinas<=$prod_info->fields["cant_disp"])
		 else
      	  echo "<font size=2 color='red'><b>-No hay suficiente Stock disponible para el producto: ".$prod_info->fields["descripcion"]."</b></font><br>";
         $i++;
      	 $prod_info->MoveNext();
    }//de while(!$prod_info->EOF)

    $items["cantidad"]=$total;
 return $items;
} //de function get_items_producto_lista_material_caso($nrocaso,$deposito_destino)


function get_items_producto_lista_material_caso($nrocaso,$deposito_destino)
{
	$sql="select en_stock.id_prod_esp,detalle_reserva.cantidad_reservada as cantidad_res,
	      precio_stock,descripcion_prod as descripcion
          from stock.en_stock
          join stock.detalle_reserva using(id_en_stock)
          join compras.fila using(id_fila)
          join compras.orden_de_compra using (nro_orden)
          join general.producto_especifico on  producto_especifico.id_prod_esp=en_stock.id_prod_esp
          where orden_de_compra.nrocaso='$nrocaso'
          and id_detalle_movimiento is null and id_deposito=2";

   $rta_reservas_no_indicadas=sql($sql,"<br>Error al traer las reservas de OC para este caso<br>") or fin_pagina();
   $total=0;
   $i=0;
    while (!$rta_reservas_no_indicadas->EOF)
    {
      $items[$i]["id_prod_esp"]=$rta_reservas_no_indicadas->fields["id_prod_esp"];
      $items[$i]["cantidad"]=$items[$i]["cantidad_orig"]=$rta_reservas_no_indicadas->fields["cantidad_res"];
      $items[$i]["cantidad_res"]=$rta_reservas_no_indicadas->fields["cantidad_res"];

      $items[$i]["descripcion"]=$rta_reservas_no_indicadas->fields["descripcion"];
      $items[$i]["precio"]=$rta_reservas_no_indicadas->fields["precio_stock"];
      $i++;
      $total++;
     $rta_reservas_no_indicadas->MoveNext();
    }//de while(!$rta_reservas_no_indicadas->EOF)

    $items["cantidad"]=$total;
 return $items;
} //fin de la funcion



//recupera los items ya sea de la BD si se pasa el id, o del post, si no se pasa
//es similar a la de Ordenes de Compra (fns.php) pero para muestras

function get_items_mov($id_mov=false){
 global $db;
 $i=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);

 if(!($id_mov ==false)){
	 $query="select * from detalle_movimiento where id_movimiento_material=$id_mov";
     $datos=sql($query) or  fin_pagina();

	 $items; $i=0;
	 while (!$datos->EOF){
	   	$items[$i]['id_detalle_movimiento']=$datos->fields['id_detalle_movimiento'];
	   	$items[$i]['id_prod_esp']=$datos->fields['id_prod_esp'];
	   	$items[$i]['cantidad']=$datos->fields['cantidad'];
 	  	$items[$i]['descripcion']=$datos->fields['descripcion'];
	    $items[$i]['precio']=$datos->fields['precio'];

	   	$i++;
	   	$datos->MoveNext();
	 }
  	 $items['cantidad']=$i;

 }else{
	 $i=0;
	 while ($clave_valor=each($_POST)){
		   if (is_int(strpos($clave_valor[0],"idp_"))){
				 $posfijo=substr($clave_valor[0],4);
				 $items[$i]['id_detalle_movimiento']=$_POST['idf_'.$posfijo];
				 $items[$i]['id_prod_esp']=$_POST['idp_'.$posfijo];
				 $items[$i]['cantidad']=$_POST['cant_'.$posfijo];
				 $items[$i]['descripcion']=$_POST['desc_'.$posfijo];
				 $items[$i]['precio']=$_POST['precio_'.$posfijo];
				 $items[$i]['cantidad_res']=$_POST['cantidad_res_'.$posfijo];
  		         $i++;
		   }
	 }
	 $items['cantidad']=$i;

 }
 return $items;
}

/***************************************************************
FUNCION que inserta los productos del movimiento o el pedido
de materiales, y actualiza o elimina las filas en caso
de que hayan sido cambiadas o eliminadas de la pantalla, respectivamente.
****************************************************************/
function insertar_productos($id,$id_dep,$id_dep_destino=0,$es_pedido_material=0,$id_licitacion=-1,$nrocaso=-1)
{
global $db,$_ses_user;

$db->StartTrans();

 if($es_pedido_material)
 { $titulo_material="Pedido de Material";
   //traemos el id del tipo de reserva
   $query="select id_tipo_reserva from tipo_reserva where nombre_tipo='Reserva Para Pedido de Material'";
   $res=sql($query,"<br>Error al traer el id de la reserva del pedido<br>") or fin_pagina();
   $tipo_reserva=$res->fields["id_tipo_reserva"];
 }
 else
 { $titulo_material="Movimiento de Material";
   //traemos el id del tipo de reserva
   $query="select id_tipo_reserva from tipo_reserva where nombre_tipo='Reserva Para Movimiento de Material'";
   $res=sql($query,"<br>Error al traer el id de la reserva del pedido<br>") or fin_pagina();
   $tipo_reserva=$res->fields["id_tipo_reserva"];
 }

 //traemos las filas que estan en la BD
 $items_en_bd=get_items_mov($id);
 //traemos los items que estan en la tabla
 $items=get_items_mov();
//print_r($items);
$fecha=date("Y-m-d H:i:s",mktime());
$usuario=$_ses_user["name"];

 //primero borramos los productos que se borraron en la tabla
 $a_borrar="";
 $cantidades_acum=0;
 for($j=0;$j<$items_en_bd['cantidad'];$j++)
 {
              for($i=0;$i<$items['cantidad'];$i++)
              {
               //si el id es igual al de la bd,
               //entonces esta insertada de antes
               if($items[$i]['id_detalle_movimiento']==$items_en_bd[$j]['id_detalle_movimiento'])
                       {//controlamos si las cantidad en la fila difiere de la que esta guardada.
                        //si lo hace debemos actualizar la cantidad reservada
                        if($items[$i]['id_prod_esp']!="" && $items[$i]['cantidad']!=$items_en_bd[$j]['cantidad'])
                             {
                  	         //no se puede cambiar la cantidad de la fila
                 	         die("Error: No se puede cambiar las cantidades de la fila.");
                             }//de if($items[$i]['id_producto']!="" && $items[$i]['cantidad']!=$items_en_bd[$j]['cantidad'])
                	        break;
                       }//de if($items[$i]['id_detalle_movimiento']==$items_en_bd[$j]['id_detalle_movimiento'])
               }//de for($i=0;$i<$items['cantidad'];$i++)

              //si $i==$items['cantidad'] significa que la fila se borro, y
              //hay que eliminarla de la BD
              if($i==$items['cantidad'])
                 {
              	  $a_borrar.=$items_en_bd[$j]['id_detalle_movimiento'].",";
                  $cantidades_acum+=$items_en_bd[$j]['cantidad'];
                  $cantidades.=$items_en_bd[$j]['cantidad'].",";
                  $id_prod_esp_a_borrar.=$items_en_bd[$j]['id_prod_esp'].",";

                 }
              //si habia un producto y se borro, hay que eliminar la fila
              //(este caso extremo falla si no se agrega este if
              if($i==0 && $items['cantidad']==1 && $items[$i]['id_prod_esp']=="")
                  {
                  $a_borrar.=$items_en_bd[$j]['id_detalle_movimiento'].",";
                  $cantidades_acum+=$items_en_bd[$j]['cantidad'];
                  $cantidades.=$items_en_bd[$j]['cantidad'].",";
                  $id_prod_esp_a_borrar.=$items_en_bd[$j]['id_prod_esp'].",";
                  }

   }//de for($j=0;$j<$items_en_bd['cantidad'];$j++)

 //si hay filas de movimiento a borrar, eliminamos las reservas
 //y luego la fila del movimiento insertar_recibidos_mov

   /*
 echo "<br>";
 echo "a borrar :   *** ";
 print_r($a_borrar);
 echo " ****  ";
     */
 if($a_borrar!=""){
  $a_borrar=substr($a_borrar,0,strlen($a_borrar)-1);
  $filas_borrar=split(",",$a_borrar);
  $cantidades=substr($cantidades,0,strlen($cantidades)-1);
  $cantidades_b=split(",",$cantidades);
  $tam=sizeof($filas_borrar);
  $array_id_prod_esp=split(",",$id_prod_esp_a_borrar);
  for($g=0;$g<$tam;$g++)
                 cancelar_reserva($array_id_prod_esp[$g],$cantidades_b[$g],$id_dep,"Se cancelo el $titulo_material Nº $id",9,"",$filas_borrar[$g]);

  //luego borramos todas las filas que haya que borrar, del
  $query="delete from detalle_movimiento where id_detalle_movimiento in ($a_borrar)";
  sql($query) or fin_pagina("<br>Error al borrar los productos del movimiento <br>$query");
 }//de if($a_borrar!="")
 else
  $filas_borrar=array();

  $tam_filas_borrar=sizeof($filas_borrar);
  //luego insertamos los productos nuevos
  for($i=0;$i<$items['cantidad'];$i++) {
   //si el id de detalle_movimiento es vacio, entonces hay que insertarlo
   if($items[$i]['id_detalle_movimiento']=="")
              {
               if($items[$i]['id_prod_esp']!="")
                        {
                          $query="select nextval('detalle_movimiento_id_detalle_movimiento_seq') as id_detalle_movimiento";
                          $id_det=sql($query)or fin_pagina();
   	                      $query="insert into detalle_movimiento(id_detalle_movimiento,id_movimiento_material,id_prod_esp,descripcion,cantidad,precio)
                                  values(".$id_det->fields['id_detalle_movimiento'].",$id,".$items[$i]['id_prod_esp'].",'".$items[$i]['descripcion']."',".$items[$i]['cantidad'].",".(($items[$i]['precio']!="")?$items[$i]['precio']:0).")
                                  ";
   	                      sql($query) or fin_pagina("<br>Error al insertar el producto $i del movimiento <br>$query");
   	                      //si la cantidad ya reservada para la fila es vacia, ingresamos una nueva reserva por la cantidad de la fila
   	                      if ($items[$i]["cantidad_res"]=="" || $items[$i]["cantidad_res"]==0)
   	                      {	reservar_stock($items[$i]['id_prod_esp'],$items[$i]['cantidad'],$id_dep,"Reserva de Productos para $titulo_material Nº $id",6,$tipo_reserva,"",$id_det->fields['id_detalle_movimiento'], $id_licitacion);
   	                      }
						  //en cambio, si la cantidad reservada es mayor que cero, en caso de tener que reservar mas, desde el stock disponible
						  //lo que se hace es agregar a la reserva ya hecha, los nuevos productos,
						  //(PORQUE SOLO PUEDE HABER UN DETALLE RESERVA POR FILA DE PM O DE OC)
						  //Ademas se corrigen en consecuencia las
						  //cantidades disponibles y reservadas de ese producto en ese stock, si es necesario.
                          else if ($items[$i]["cantidad_res"]>0){
							if ($nrocaso) {

						  	$consulta="select id_detalle_reserva,id_en_stock,cantidad_reservada
									   from stock.en_stock
									   join stock.detalle_reserva using (id_en_stock)
									   where nrocaso='$nrocaso'
									   and id_prod_esp=".$items[$i]['id_prod_esp']."
									   and id_deposito=2 and id_detalle_movimiento isnull
									   order by cantidad_reservada ASC";

							}
						  	else{
						  	$consulta="select id_detalle_reserva,id_en_stock,cantidad_reservada
									from stock.en_stock
										join stock.detalle_reserva using (id_en_stock)
									where id_licitacion= $id_licitacion
										and id_prod_esp=".$items[$i]['id_prod_esp']."
										and id_deposito=2 and id_detalle_movimiento isnull
									order by cantidad_reservada ASC
								";
						    }


							$rta_consulta=sql($consulta, "c311") or fin_pagina();

							//en la primera entrada viene la reserva de mayor cantidad, que es la que afectaremos mas abajo
							// por lo tanto la saltamos
							$cantidad_primer_reserva=$rta_consulta->fields["cantidad_reservada"];
							$id_primer_reserva=$rta_consulta->fields["id_detalle_reserva"];
							$rta_consulta->MoveNext();
							//luego acumulamos las demas cantidades de las reservas restantes, si es que hay
							$acum_otras_reservas=0;$index=0;
							//y creamos un arreglo con los id de detalle reserva que debemos descontar, y sus respectivas cantidades actuales
							$reservas_a_bajar=array();
 							while (!$rta_consulta->EOF)
 							{
 							  $acum_otras_reservas+=$rta_consulta->fields["cantidad_reservada"];
 							  $reservas_a_bajar[$index]=array();
 							  $reservas_a_bajar[$index]["id"]=$rta_consulta->fields["id_detalle_reserva"];
 							  $reservas_a_bajar[$index]["cantidad_reservada"]=$rta_consulta->fields["cantidad_reservada"];
 							  $index++;

 							  $rta_consulta->MoveNext();
 							}//de while(!$rta_consulta->EOF)
 							$rta_consulta->Move(0);

							    $id_en_stock=$rta_consulta->fields["id_en_stock"];
								//si la cantidad de la fila es mayor que la reservada, entonces se necesita reservar la diferencia
								//desde el stock disponible. Para eso aumentamos la cantidad de la reserva ya presente y movemos
								//las cantidades de la tabla en_stock, como corresponda

								if($items[$i]['cantidad']>$items[$i]["cantidad_res"]){

									$cantidad_aumentar=$items[$i]['cantidad']-$items[$i]["cantidad_res"];
									$nueva_reserva_pm=0;
									$sacar_de_stock_disp=1;
								}
								//si la cantidad de la fila es menor que la reservada
								else if($items[$i]['cantidad']<$items[$i]["cantidad_res"]){

									$cantidad_aumentar=$items[$i]['cantidad'];
									//indicamos que solo se debe usar parte de la reserva del producto hecha desde OC, por lo que
									//solo se resta esa cantidad. Y ademas, se debe generar una nueva reserva para esos productos
									//que se van a usar para este PM
									$nueva_reserva_pm=1;
									$sacar_de_stock_disp=0;
								}
								else
								{
									$cantidad_aumentar=0;
									$nueva_reserva_pm=0;
									$sacar_de_stock_disp=0;
								}

								//Si hay una sola reserva hecha y si la cantidad a usar para la fila del PM es menor que la reservada, significa que solo se usa una
								//parte de dicha reserva, entonces....
								if($acum_otras_reservas==0 && $nueva_reserva_pm)
								{
									//Solo se descuenta de la reserva atada a la licitacion para ese producto, generada por la OC,
									//la cantidad que se va a utilizar.
									//Primero se descuenta de la cantidad reservada de la tabla en_stock
									$query="update stock.en_stock set cant_reservada=cant_reservada-$cantidad_aumentar
										                                  where id_en_stock=$id_en_stock";
									sql($query,"<br>Error al actualizar cantidades del stock para reserva de productos<br>") or fin_pagina();

									//luego se decrementa la cantidad de la reserva asociada a la licitacion de ese producto
									$consulta="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada-$cantidad_aumentar
									                                        where id_detalle_reserva=$id_primer_reserva";
									sql($consulta, "<br>c317: Error al actualizar la reserva para licitacion de la fila<br>") or fin_pagina();

									$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Se utilizaron los productos reservados para OC o para Movimiento de material'";
									$tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();

									if($tipo_mov->fields["id_tipo_movimiento"]!="")
							 		  $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];
							 		else
							 		  die("Error Interno PM485: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

									//luego registramos el cambio en el log de movimientos de stock
									//(se utilizan esos productos de la reserva hecha por la OC)
									$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
								    $values=" $id_en_stock,$id_tipo_movimiento,$cantidad_aumentar,'$fecha','$usuario','Utilización de los productos para $titulo_material Nº $id'";
								    $sql="insert into log_movimientos_stock ($campos) values ($values)";
								    sql($sql,"<br>Error al insetar en el log de movimientos de stock (insertar488)<br>") or fin_pagina();

									//Y luego se genera una nueva reserva, para la fila del pedido de material actual, con esa cantidad,
									//que se desconto de la reserva hecha por la OC
									reservar_stock($items[$i]['id_prod_esp'],$cantidad_aumentar,$id_dep,"Reserva de Productos para $titulo_material",6,$tipo_reserva,"",$id_det->fields['id_detalle_movimiento'], $id_licitacion);

									//como la funcion reservar_stock descuenta de la cantidad disponible
									//y agrega a la cantidad reservada del stock la cantidad pasada como parametro, es necesario volver
									//a incrementar la cantidad disponible para compensar,
									// debido a que los productos que ahora estan reservados para el PM antes lo estaban para la OC,
									// por lo que los productos ya fueron descontados de stock disponibles y agregados a la cantidad reservada
									//cuando se hizo la reserva desde la OC al recibir los productos
									$query="update stock.en_stock set cant_disp=cant_disp+$cantidad_aumentar
									                                  where id_en_stock=$id_en_stock
									                                  ";
									sql($query,"<br>Error al compensar las cantidades del stock<br>") or fin_pagina();

								}//de if($nueva_reserva_pm)
								else//no se hace una nueva reserva y el PM pasara a tener toda la reserva generada desde la OC
								{
									//si no hay nada que sacar del stock disponible entonces ponemos esa cantidad en cero
   								   if($sacar_de_stock_disp==0)
									 $cantidad_aumentar=0;

									 //agregamos a la reserva actual hecha para la licitacion, los productos disponibles necesarios para
									//completar la cantidad que requiere la fila de PM (si es necesario),
									// y le sacamos la relacion que tenia con la fila de OC
									$consulta="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada+$cantidad_aumentar,
									                                            id_detalle_movimiento=".$id_det->fields['id_detalle_movimiento'].",
									                                            id_fila=null,id_tipo_reserva=$tipo_reserva
									                                            where id_detalle_reserva=$id_primer_reserva";
									sql($consulta, "<br>c516: Error al actualizar la reserva de la fila<br>") or fin_pagina();

									//luego actualizamos las cantidades disponibles y reservadas en stock, si la cantidad a aumentar es mayor que cero
									if($cantidad_aumentar>0)
									{

										//restamos los productos que se reservan de la cantidad disponible y los sumamos a la cantidad
										//reservada
										$query="update stock.en_stock set cant_disp=cant_disp-$cantidad_aumentar,
										                                  cant_reservada=cant_reservada+$cantidad_aumentar
										                                  where id_en_stock=$id_en_stock";
										sql($query,"<br>Error al actualizar cantidades del stock para reserva de productos<br>") or fin_pagina();

										$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Reserva de productos para OC o para Movimiento/Pedido de material'";
										$tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();

										if($tipo_mov->fields["id_tipo_movimiento"]!="")
								 		  $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];
								 		else
								 		  die("Error Interno PM539: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

										//luego registramos el cambio en el log de movimientos de stock
										$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
									    $values=" $id_en_stock,$id_tipo_movimiento,$cantidad_aumentar,'$fecha','$usuario','Reserva de productos para Pedido de Material Nº $id'";
									    $sql="insert into log_movimientos_stock ($campos) values ($values)";
									    sql($sql,"<br>Error al insetar en el log de movimientos de stock (insertar537)<br>") or fin_pagina();

									}//de if($cantidad_aumentar>0)

								   if($acum_otras_reservas)
								   {//si habia mas de una reserva por el mismo producto provenientes de OC distintas, se eliminan, excepto la primera, todas
									//las reservas restantes que se utilizaron, de las que estan indicadas en $reservas_a_bajar, para que se mantenga
									//la idea de una unica reserva por detalle_movimiento
									//y se agrega el registro del movimiento en el log de movimientos de stock
									//(Se pone el movimiento de que se utilizaron esas reservas)
									//El procedimiento es sumar a la primer reserva la cantidad de las otras, necesaria para satisfacer la cantidad
									//que la fila del PM requiere. Lo que se use de las otras reservas se pondra como utilizado en el log del stock
									//y se descontara o eliminara de detalle_reserva. Lo que quede sin usarse, queda atado a la OC como estaba.
									//En caso de que se use una parte de una reserva y la otra no, se genera un nuevo log de stock parar la parte
							        //que se utilizó de la OC, y se decuenta la cantidad usada, de la reserva afectada. Esto se explica mejor
							        //durante la ejecucion del codigo que sigue:

										//la cantidad pedida por la fila
										$cantidad_pedida_para_fila=$items[$i]['cantidad'];
										//la cantidad acumulada para satisfacer la cantidad pedida por la fila
										//es hasta ahora la cantidad de la primer reserva
										$cantidad_acumulada_de_reservas=$cantidad_primer_reserva;

										//la cantidad que se saca de las restantes reservas, para agregarle a la primera
										$cantidad_total_a_sumar=0;

                                        //vamos acumulando el resto de las cantidades de las otras reservas, hasta que se acumule la cantidad pedida
                                        //por la fila, o hasta que se acaben las reservas disponibles
										for($j=0;$j<sizeof($reservas_a_bajar);$j++ )
                                        {
                                        	//si falta acumular para completar la cantidad requerida por la fila
                                            if($cantidad_acumulada_de_reservas<$cantidad_pedida_para_fila)
                                            {
                                            	$cant_reserva_actual=$reservas_a_bajar[$j]["cantidad_reservada"];
                                            	//utilizamos de la reserva actual, solo la cantidad necesaria que es:
                                            	$cantidad_faltante=$cantidad_pedida_para_fila-$cantidad_acumulada_de_reservas;

                                            	if($cant_reserva_actual<=$cantidad_faltante)
                                            	{
                                            		//si la cantidad para la reserva que estamos viendo es justo
                                            		//lo que esta faltando, o no alcanza para cubrir todo lo que esta faltando
                                            		//acumulamos toda esa reserva para la fila (es decir: se utilizan todos los productos de esa reserva)
                                            		$cantidad_a_utilizar=$cant_reserva_actual;
                                            		$cantidad_no_utilizada=0;
                                            	}
                                            	else if($cantidad_faltante>0)
                                            	{
                                            		//si en cambio, la cantidad faltante (que es mayor que cero) para cubrir lo requerido por la fila es
                                            		//menor que la cantidad en la reserva que estamos viendo,
                                            		//utilizamos de esa reserva solo la cantidad faltante, pero el resto la dejamos como estaba en la reserva
                                            		$cantidad_a_utilizar=$cantidad_faltante;
                                            		$cantidad_no_utilizada=$cant_reserva_actual-$cantidad_faltante;
                                            	}
                                            	else
                                            	 die("Error interno: la cantidad acumulada ($cantidad_acumulada_de_reservas) no es mayor o igual que la pedida por la fila($cantidad_pedida_para_fila)<br>
                                            	 	  Pero la cantidad faltante es igual a cero. Consulte a la División Software por este error. ");

												$cantidad_acumulada_de_reservas+=$cantidad_a_utilizar;
												$reservas_a_bajar[$j]["cantidad_utilizada"]=$cantidad_a_utilizar;
												$reservas_a_bajar[$j]["cantidad_no_utilizada"]=$cantidad_no_utilizada;

												$cantidad_total_a_sumar+=$cantidad_a_utilizar;
                                            }//de if($cantidad_acumulada_de_reservas<$cantidad_pedida_para_fila)
                                            else//si ya se acumulo todo lo que la fila pedia, la reserva queda como esta y lo indicamos en el arreglo
                                            {
                                            	$reservas_a_bajar[$j]["cantidad_utilizada"]=0;
												$reservas_a_bajar[$j]["cantidad_no_utilizada"]=$reservas_a_bajar[$j]["cantidad_reservada"];
                                            }//de if($cantidad_acumulada_de_reservas<$cantidad_pedida_para_fila)

                                        }//de for($j=0;$j<$reservas_a_bajar;$j++)

                                        //agregamos a la primer reserva, la cantidad que se utilizara de las demas reservas
                                        $query="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada+$cantidad_total_a_sumar,
                                                                                id_detalle_movimiento=".$id_det->fields['id_detalle_movimiento'].",
									                                            id_fila=null,id_tipo_reserva=$tipo_reserva
									                                            where id_detalle_reserva=$id_primer_reserva";
									    sql($query, "<br>c612: Error al actualizar la reserva de la fila con las otras reservas<br>") or fin_pagina();

                                        //luego, por cada reserva afectada extra, descontamos las cantidades utilizadas
                                        for($j=0;$j<sizeof($reservas_a_bajar);$j++)
                                        {
                                        	//si de esta reserva se utilizo algo de su cantidad debemos descontar esa cantidad
                                        	//ya sea actualizando la cantidad de la reserva en cuestion, si aun queda una parte
                                        	//sin utilizar; o eliminando ese detalle de reserva porque se utilizó completa
                                        	if($reservas_a_bajar[$j]["cantidad_utilizada"]>0)
                                        	{
                                        		//si se utilizó toda la cantidad para esa reserva, simplemente se elimina la reserva,
                                        		//debio a que ya quedó esa cantidad registrada como parte de la primer reserva con id: $id_primer_reserva
                                        		if($reservas_a_bajar[$j]["cantidad_reservada"]==$reservas_a_bajar[$j]["cantidad_utilizada"])
                                        		{
                                        			$query="delete from stock.detalle_reserva where id_detalle_reserva=".$reservas_a_bajar[$j]["id"];
                                        			sql($query,"<br>Error al eliminar la reserva con id ".$reservas_a_bajar[$j]["id"]."<br>") or fin_pagina();
                                        		}//de if($reservas_a_bajar[$j]["cantidad_reservada"]==$reservas_a_bajar[$j]["cantidad_utilizada"])
                                        		elseif($reservas_a_bajar[$j]["cantidad_utilizada"]<$reservas_a_bajar[$j]["cantidad_reservada"])
                                        		{
                                        			//si la cantidad de la reserva a utilizar es menor que la reservada, entonces se resta de dicha reserva
                                        			//la cantidad utilizada, y se registra en el log de stock que se utilizo esa cantidad
                                        			$query="update stock.detalle_reserva set cantidad_reservada=cantidad_reservada-".$reservas_a_bajar[$j]["cantidad_utilizada"]." where id_detalle_reserva=".$reservas_a_bajar[$j]["id"];
                                        			sql($query,"<br>Error al actualizar la cantidad del detalle de la reserva<br>") or fin_pagina();

                                        			//la cantidad utilizada entra a la primer reserva, por lo que solo es necesario registrar el log en
                                        			//los movimientos de stock la utilizacion de $reservas_a_bajar[$j]["cantidad_utilizada"]
                                        			//para la reserva $reservas_a_bajar[$j]["id"]

                                        			$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Reserva de productos para OC o para Movimiento/Pedido de material'";
													$tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();

													if($tipo_mov->fields["id_tipo_movimiento"]!="")
											 		  $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];
											 		else
											 		  die("Error Interno PM654: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

											 		//generamos el log
													$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
												    $values=" $id_en_stock,$id_tipo_movimiento,".$reservas_a_bajar[$j]["cantidad_utilizada"].",'$fecha','$usuario','Reserva de productos para Pedido de Material Nº $id'";
												    $sql="insert into log_movimientos_stock ($campos) values ($values)";
												    sql($sql,"<br>Error al insetar en el log de movimientos de stock (insertar649)<br>") or fin_pagina();


			                                        //y generamos el correspondiente log indicando que se utilizaron productos reservados
			                            			$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Se utilizaron los productos reservados para OC o para Movimiento de material'";
													$tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();

													if($tipo_mov->fields["id_tipo_movimiento"]!="")
											 		  $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];
											 		else
											 		  die("Error Interno PM700: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

													$campos=" id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario";
												    $values=" $id_en_stock,$id_tipo_movimiento,".$reservas_a_bajar[$j]["cantidad_utilizada"].",'$fecha','$usuario','Utilización de los productos para $titulo_material Nº $id'";
												    $sql="insert into log_movimientos_stock ($campos) values ($values)";
											        sql($sql,"<br>Error al insetar en el log de movimientos de stock (insertar649)<br>") or fin_pagina();

                                        		}//de elseif($reservas_a_bajar[$j]["cantidad_utilizada"]<$reservas_a_bajar[$j]["cantidad_reservada"])
                                        		else
                                        		{
                                        			//SI ENTRA POR ACA SIGNIFICA QUE LA CANTIDAD UTILIZADA ES MAYOR A LA RESERVADA PARA ESA RESERVA,
                                        			//POR LO TANTO LA DECISION ANTERIOR AL ARMAR EL ARREGLO $reservas_a_bajar FUE INCORRECTA. ENTONCES
                                        			//NO SE PUEDE CONTINUAR CON LA EJECUCION PORQUE ALGO ESTA MAL HECHO
                                        			die("Error interno: La cantidad que se decidio utilizar para la reserva con id ".$reservas_a_bajar[$j]["id"]." es mayor a la que estaba reservada originalmente.<br>
														Cantidad a utilizar: ".$reservas_a_bajar[$j]["cantidad_utilizada"]." - Cantidad reservada: ".$reservas_a_bajar[$j]["cantidad_reservada"]."<br>
                                        			    No se puede continuar la ejecución. Contacte a la División Software");
                                        		}//del else

                                        	}//de if($reservas_a_bajar[$j]["cantidad_utilizada"]>0)

                                        }//de for($j=0;$j<sizeof($reservas_a_bajar);$j++)

									}//if($acum_otras_reservas)

								}//del else de if($nueva_reserva_pm)
///////////////////////////////

						  }//de if ($items[$i]["cantidad_res"]>0)
                        }//de  if($items[$i]['id_producto'])
               }//de if($items[$i]['id_detalle_movimiento']=="")
               //sino, si la fila no fue borrada, la actualizamos
               elseif($tam_filas_borrar>0 && !in_array($items[$i]['id_detalle_movimiento'],$filas_borrar))
               {

	                for($j=0;$j<$items_en_bd['cantidad'];$j++){

	                    if ($items_en_bd[$j]["id_detalle_movimiento"]==$items[$i]["id_detalle_moviento"])
	                           $cantidad_reservada_bd=$items_en_bd[$j]["cantidad"];

	                }

	                if ($cantidad_reservada_bd==$items[$i]["cantidad"])
	                          {
	                           //se modifico la cantidad y tendriamos que borrar los reservados
	                           //cancelar_reserva
	                           //reservar_stock
	                          }

	                $query="update detalle_movimiento set descripcion='".$items[$i]['descripcion']."',cantidad=".$items[$i]['cantidad'].",precio=".(($items[$i]['precio']!="")?$items[$i]['precio']:0)."
	                        where id_detalle_movimiento=".$items[$i]['id_detalle_movimiento'];
	                sql($query) or fin_pagina("<br>Error al actualizar el producto $i del movimiento <br>$query");


               }//de elseif($tam_filas_borrar>0 && !in_array($items[$i]['id_detalle_movimiento'],$filas_borrar))
  }//de for($i=0;$i<$items['cantidad'];$i++)

 $db->CompleteTrans();
}//de insertar_productos($id,$id_dep)

/*********************************************************
Funcion que descuenta las reservas de los productos
seleccionados, descontando las entradas correspondientes
en la tabla reservados y  eliminando las correspondientes
de detalle_reserva, ligados a la entrada dentro de
la tabla stock.
(Es aca cuando los productos dejan de pertenecer
 efectivamente al stock al que estan ligados)

Tambien se utiliza para cancelar las reservas si el
movimiento es anulado (por eso el parametro $anular)
**********************************************************/
function descontar_reservados_mov($id_mov,$id_dep,$anular=0)
{
global $db,$_ses_user,$titulo_pagina;

 $db->StartTrans();
 //por cada producto seleccionado desde el proveedor de tipo stock
 //ingresamos la cantidad dada, en la parte de reservados de ese
 //producto, en ese stock, bajo ese deposito (todo esto contenido)
 //en diferentes variables
 $items_stock=get_items_mov($id_mov);

      for($i=0;$i<$items_stock['cantidad'];$i++)
      {
       $id_prod_esp=$items_stock[$i]["id_prod_esp"];
       $cantidad=$items_stock[$i]["cantidad"];
       $id_detalle_movimiento=$items_stock[$i]["id_detalle_movimiento"];
       if($anular){
         $comentario_stock="Cancelacion de reserva de productos por anulación del $titulo_pagina Nº $id_mov";
         $id_tipo_movimiento=9;
         }
         else {
         $comentario_stock="Utilización de los productos reservados por el $titulo_pagina Nº $id_mov";
         $id_tipo_movimiento=7;
         }

       cancelar_reserva($id_prod_esp,$cantidad,$id_dep,$comentario_stock,$id_tipo_movimiento,"",$id_detalle_movimiento);

      }//de for($i=0;$i<$items_rma['cantidad'];$i++)

 $db->CompleteTrans();
}//de function descontar_reservados_mov($id_mov,$id_dep,$anular=0)




 /**********************************************************
Funcion que inserta en la tabla recibidos_mov, los productos
que se va recibiendo hasta el momento.
***********************************************************/
function insertar_recibidos_mov($id_mov)
 {
  global $db,$_ses_user;

  $ok=1;

  $db->StartTrans();

  $items=get_items_mov($id_mov);

  for($i=0;$i<$items['cantidad'];$i++)
   {
   $cantidad=$_POST["cant_recib_$i"];
   if($cantidad>0)
       {
       $desc_recib=$_POST["desc_recib_$i"];
       $id_prod_esp=$items[$i]["id_prod_esp"];
       $id_detalle_mov=$items[$i]['id_detalle_movimiento'];
       //vemos si ya se recibieo alguno de este producto. Si es asi, se actualiza
       //es entrada en recibidos_mov, sino, se inserta una nueva entrada
       $query="select id_recibidos_mov from recibidos_mov
               where id_detalle_movimiento=$id_detalle_mov and ent_rec=1";
       $res=sql($query) or fin_pagina();
       if($res->fields['id_recibidos_mov'])
            {
            $query="update recibidos_mov set cantidad=cantidad+$cantidad , observaciones='$desc_recib' where id_recibidos_mov=".$res->fields['id_recibidos_mov']." and ent_rec=1";
            $tipo_log="actualización";
            $id_recibidos_mov=$res->fields['id_recibidos_mov'];
            }
            else
               {
                $query="select nextval('recibidos_mov_id_recibidos_mov_seq') as id";
                $id=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la seucencia de recibidos_mov");
                $id_recibidos_mov=$id->fields['id'];
                $query="insert into recibidos_mov(id_recibidos_mov,cantidad,observaciones,id_detalle_movimiento,ent_rec)
                        values($id_recibidos_mov,$cantidad,'$desc_recib',$id_detalle_mov,1)";
                $tipo_log="inserción";
               }
     sql($query) or fin_pagina();

    $fecha_hoy=date("Y-m-d H:i:s",mktime());
    $query="insert into log_recibidos_mov(fecha,usuario,tipo,id_recibidos_mov,cantidad_recibida)
            values('$fecha_hoy','".$_ses_user['name']."','$tipo_log',$id_recibidos_mov,$cantidad)";
    sql($query) or fin_pagina();
    }
   }
 if (!$db->CompleteTrans()) $ok=0;

 return $ok;
}//de function insertar_recibidos_mov($id_mov)





/**********************************************************
Funcion que envia mail a los responsables del movimiento .
***********************************************************/
function enviar_mail_mov($datos)
{
	global $db,$titulo_pagina;
	//ver el origen y destino
	$sql = "select temp1.nombre as origen, temp1.id_deposito as id_origen,temp2.nombre as destino, temp2.id_deposito as id_destino from movimiento_material join depositos as temp1 on (temp1.id_deposito = deposito_origen) join depositos as temp2 on (temp2.id_deposito = deposito_destino) where id_movimiento_material = ".$datos['Id'];
    $result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail");

    $origen = $result->fields['origen'];
	$destino = $result->fields['destino'];
    $id_origen = $result->fields['id_origen'];
    $id_destino = $result->fields['id_destino'];

	//buscar el responsable del deposito origen
	$sql = "select usuarios.mail from depositos join responsable_deposito  using (id_deposito) join usuarios using (id_usuario) where id_deposito = ".$id_origen;
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail");
	$responsable_origen = array();
	while (!$result->EOF) {
		$responsable_origen[] = $result->fields['mail'];
		$result->MoveNext();
	}

	//buscar el responsable del deposito origen
	$sql = "select usuarios.mail from depositos join responsable_deposito  using (id_deposito) join usuarios using (id_usuario) where id_deposito = ".$id_destino;
    $result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail");
	$responsable_destino = array();
	while (!$result->EOF) {
		$responsable_destino[] = $result->fields['mail'];
		$result->MoveNext();
	}

	//tengo que prepara el "para" para enviar el mail
	//el problema es que se pueden repetir los mail
	//armo un arreglo con los mail
	$mail_para = array ();
    //la variable "$responsable_origen" es arreglo por eso hago un merge para que me quede solo un arreglo
    $mail_para= array_merge($mail_para,$responsable_origen);
    //la variable "$responsable_destino" es arreglo por eso hago un merge para que me quede solo un arreglo
    $mail_para= array_merge($mail_para,$responsable_destino);
    //saco el tamaño del arreglo por que no se como me quedo despues de los merge
    $i=sizeof($mail_para);
	//$para = "juanmanuel@coradir.com.ar,".join(",",$responsable_origen).",".join(",",$responsable_destino);
	if($origen=="New Tree" || $destino=="New Tree"){
	  $mail_para[$i]="cinthia@newtree.com.ar";
	  $i++;
	  $mail_para[$i]="nestor@newtree.com.ar";
	  $i++;
	  //$para.=",cinthia@newtree.com.ar,nestor@newtree.com.ar";
	}
	//agregar responsables

	$asunto = "$titulo_pagina Nº: ".$datos['Id'];
	$contenido = "$titulo_pagina Nº ".$datos['Id'].".\n";
	$contenido.= "Depósito de Origen: ".$origen.".\n";
	$contenido.= "Depósito de Destino: ".$destino.".\n";
	$contenido.= "Autorizado por ".$datos['Usuario']." el día ".fecha($datos["Fecha"]).".\n";
	$contenido.= "\nDetalle del movimiento: \n";


	//obtener el detalle del movimiento
	$sql = "select cantidad,descripcion from detalle_movimiento where id_movimiento_material = ".$datos['Id'];
    $result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail");

    $contenido.= "Cantidad  | Descripción \n";
    $contenido.= "--------------------------------------------------------------\n";

    while (!$result->EOF) {
    	$contenido.="     ".$result->fields['cantidad']."             ".$result->fields['descripcion']."\n";
    	$result->MoveNext();
    }

	//agrego datos si tiene logistica integrada
	if ($datos['id_logistica_integrada']!=''){
		//todo lo que sigue es para el para

		$mail_para[$i]="valentino@coradir.com.ar";
		$i++;
		$mail_para[$i]="pietragalla@coradir.com.ar";
		$i++;
		//$para = $para. " valentino@coradir.com.ar";
		//$para = $para. ", pietragalla@coradir.com.ar";
		$sql = "select * from
				 (select (nombre|| ' ' ||apellido) as usuario, mail from sistema.usuarios)as lado_a
				join
				 (select usuario from mov_material.log_movimiento where id_movimiento_material = ".$datos['Id']."
				  group by usuario
				 )as lado_b
				using (usuario)";
    	$result_mail = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail (consulta trae mail a partir de log)");

    	while (!$result_mail->EOF){
    		//$para = $para. ", " . $result_mail->fields['mail'];
    		$mail_para[$i]=$result_mail->fields['mail'];
    		$i++;
    		$result_mail->MoveNext();
    	}

    	//saco el tamaño del arreglo para asegurarme que esta en la posicion correcta para seguir
		$i=sizeof($mail_para);

    	//si esta asociado a una licitacion
		if ($datos['asociado_a']=='lic') {
			$sql = "select mail as mail_patrocinador, mail_lider from (
					select mail as mail_lider, patrocinador from
					(
					select id_licitacion, lider, patrocinador from
					 (select id_licitacion from  mov_material.movimiento_material where id_movimiento_material = ".$datos['Id'].") as a
					join
					 licitaciones.licitacion
					using (id_licitacion)
					)as b
					left join sistema.usuarios
					on id_usuario = lider
					)as c
					left join sistema.usuarios
					on id_usuario = patrocinador";
		    $result_mail = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail (consulta trae mail a partir de log)");
		    if ($result_mail->fields['mail_lider']!=''){
		    	$mail_para[$i]=$result_mail->fields['mail_lider'];
				$i++;
		    	//$para = $para. ", ".$result_mail->fields['mail_lider'];
		    }
		    if ($result_mail->fields['mail_patrocinador']!=''){
		    	$mail_para[$i]=$result_mail->fields['mail_patrocinador'];
				$i++;
		    	//$para = $para. ", ".$result_mail->fields['mail_patrocinador'];
		    }
		}

		//si esta asociado a un caso
		if ($datos['asociado_a']=='caso'){
			$mail_para[$i]="estrada@coradir.com.ar";
			$i++;
			$mail_para[$i]="dcristian@coradir.com.ar";
			$i++;
			//$para = $para. ", estrada@coradir.com.ar, dcristian@coradir.com.ar ";
		}

		//todo lo que sigue es para el contenido
		$sql = "select * from mov_material.logistica_integrada where id_logistica_integrada = ". $datos['id_logistica_integrada'];
		$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error Enviando mail (consulta trae mail a partir de log)");
		$contenido.="\n\n\n";
		$contenido.="----------------------- Logistica Integrada -------------------\n\n";
		$contenido.="Dirección: ". $result->fields['direccion'] ."\n";
		$contenido.="Fecha de envio: ". fecha ($result->fields['fecha_envio_logis']) ."\n";
		$contenido.="Contacto: ". $result->fields['contacto'] ."\n";
		$contenido.="Telefono: ". $result->fields['telefono'] ."\n";
		$contenido.="Código Postal: ". $result->fields['cod_pos'] ."\n\n";
		$contenido.= "--------------------------------------------------------------\n";
	}
	//fin de agrego datos si tiene logistica integrada
	//print_r ($mail_para);
	$para=elimina_repetidos($mail_para,0);
	//no envia el mail a juan manuel lo saco del string, en caso que se encuentre ultimo el mail
	$para=ereg_replace("juanmanuel@coradir.com.ar","",$para);
	//no envia el mail a juan manuel lo saco del string, en caso que se encuentre distinto del ultimo el mail
	$para=ereg_replace("juanmanuel@coradir.com.ar,","",$para);
/*	echo "<br> los ok: ".$para;
	die();*/
	enviar_mail($para, $asunto, $contenido,'','','',0);
}//de function enviar_mail_mov($datos)

/************************************************************************************************
  Funcion que genera el formulario para entregar los productos de un MM o PM
  @id_mov                   El id de movimiento de material para el cual se genera el formulario
  @es_pedido_material       Para indicar que la entrega es para un PM se pone este campo en 1.
  						    Sino se pone en 0
  @mostrar_botones_entregar Con este campo seteado en 1 se muestran los botones de entregar y
  							entregar sin CB. Si viene en 0 no se muestran.
*************************************************************************************************/
function generar_form_entrega($pagina_llamado,$id_mov,$es_pedido_material,$mostrar_botones_entregar=1)
{
	global $estado,$deposito_origen,$deposito_destino,$id_licitacion,$nrocaso,$permiso,$hay_entregas_hechas;
    global $pm_rma_producto_sl;
    
    
	 $flag=0;
	 $items=get_items_mov($id_mov);
	?>
	  <script>
	   function des_entregar_func(id_detalle_movimiento)
	   {
	   	  if(confirm('¿Está seguro que desea Des-Entregar esta fila?'))
	   	  {
	   	  	document.all.des_entregar_fila.value=id_detalle_movimiento;
	   	  	return true;
	   	  }
	   	  else
	   	    return false;
	   }//de function des_entregar_func(id_detalle_movimiento)

	   function ajustar_cantidad_fila(id_fila_ajustar,cant_pedida,cant_entregada)
	   {

	   	if(confirm('La cantidad de esta fila será reducida de '+cant_pedida+' a '+cant_entregada+' prodcutos, y los descontados quedarán disponibles en Stock.\n¿Está seguro que desea continuar?'))
	   	{
	   		document.all.fila_ajustar_cantidad.value=id_fila_ajustar;
	   		return true
	   	}
	   	else
	   	 return false;

	   }//de ajustar_cantidad_fila(id_fila_ajustar,cant_pedida,cant_entregada)

	  </script>
	  <input type="hidden" name="des_entregar_fila" value="">

	  <table align="center" width="100%" bordercolor="Black" border="1">
			   <tr>
			    <td colspan="3" align="center">
			     <font size="3"><b>Entrega de Productos</b></font>
			     <input type="hidden" name="fila_ajustar_cantidad" value="">
			    </td>
			   </tr>
			  <?
			  $hay_sin_entregar=0;
			   for($x=0;$x<$items['cantidad'];$x++)
			   {
			     //traemos el log de los recibidos + la cantidad de productos recibidos
			     //y las observaciones
			     $query="select cantidad,observaciones,log_recibidos_mov.* from recibidos_mov join log_recibidos_mov using(id_recibidos_mov)
			              where id_detalle_movimiento=".$items[$x]['id_detalle_movimiento']." and recibidos_mov.ent_rec=0";
			     $datos_entregados_mov=sql($query) or fin_pagina();
                 if ($datos_entregados_mov->fields['cantidad'] && !$flag)
                      $flag=1;

	           //las siguientes variables se usan para el control de los totales recibidos
	           //un poco mas abajo en el codigo
	           $entregados=($datos_entregados_mov->fields['cantidad'])?$datos_entregados_mov->fields['cantidad']:0;
	           $enviados=$items[$x]['cantidad'];
	           $producto=$items[$x]['descripcion'];
	           $id_detalle_movimiento=$items[$x]['id_detalle_movimiento'];
	           $id_prod_esp=$items[$x]['id_prod_esp'];
	           $total_pedido=$items[$x]['cantidad'];

	           if($entregados==0)
	            $hay_sin_entregar=1;
			   else
			    $hay_entregas_hechas=1;

	           $todo_recib="";
	           if($items[$x]['cantidad']<=$datos_entregados_mov->fields['cantidad'])
	            $todo_recib="readonly";

                 ?>
			    <tr>
			     <td>
				 <table width="100%" align="center">
		          <tr id=mx>
		           <td>
		            <table width="100%">
		             <tr id=mx>
		              <td>
			            <?
			              if($pagina_llamado=="packaging")
				          {
				          	   if($entregados<$total_pedido)
				          	   {?>
				          	   <input type="checkbox" name="entregar_<?=$id_detalle_movimiento?>" value="1" class="estilos_check" id="check_entregar_<?=$x?>">
						       <?
				          	   }
			              }//de if($pagina_llamado=="packaging")
			              else //$pagina_llamado=="entregar_sin_cb"
			              {
			              	 if($es_pedido_material && $entregados==0 && permisos_check("inicio","permiso_eliminar_fila_pm_autorizada"))
						      {?>
						        <input type="checkbox" name="eliminar_<?=$id_detalle_movimiento?>" value="1" class="estilos_check">
						       <?
						      }
			              }//del else de if($pagina_llamado=="packaging")
					     ?>
			            Producto: <?=$producto?>
			           </td>
			           <td width="1%" align="right">
			            <?
			            if($pagina_llamado=="entregar_sin_cb" && $es_pedido_material && permisos_check("inicio","permiso_ajustar_cantidad_PM") && $entregados>0 && ($entregados<$total_pedido))
		              	 {?>
		              	 	<input type="submit" name="ajustar_cantidad_PM" value="A" title="Ajustar Cantidad de la Fila" class="little_boton" onclick="return ajustar_cantidad_fila(<?=$id_detalle_movimiento?>,<?=$total_pedido?>,<?=$entregados?>)">
		              	 <?
		              	 }//de if($es_pedido_material && permisos_check("inicio","permiso_ajustar_cantidad_PM"))
						?>
					   </td>
					  </tr>
					 </table>
		           </td>
		           <td width="10%" nowrap>U$S <?=(($items[$x]['precio'])!="")?number_format($items[$x]['precio'],2,'.',''):"";?></td>
		           <td width="13%" nowrap>
		            Total Pedidos <?=$total_pedido?>
		           </td>
		           <td width="13%" nowrap>
		            Entregados <?=$entregados?>
		           </td>
		          </tr>
		         </table>
		        <script>
					//ventana de codigos de barra entrega
					var vent_cb_<?=$id_detalle_movimiento?>=new Object();
					vent_cb_<?=$id_detalle_movimiento?>.closed=true;

					//ventana de entrega sin codigos de barra
					var vent_sin_cb_<?=$id_detalle_movimiento?>=new Object();
					vent_sin_cb_<?=$id_detalle_movimiento?>.closed=true;
				</script>
		         <table width="100%" align="center">
				  <tr>
				   <td colspan="2">
				   	<table align="center" width="100%" border="1" bordercolor=#E0E0E0 bgcolor="#ACACAC">
				   	 <?
				   	 $observaciones_recib=$datos_entregados_mov->fields['observaciones'];
				   	 //generamos el log de recibidos para cada producto
				   	 while(!$datos_entregados_mov->EOF)
				   	 {?>
				   	  <tr>
				   	   <td>
				   	    <font color="Black">
				   	     <b>
					   	     Cantidad Entregada: <?=$datos_entregados_mov->fields['cantidad_recibida']?>
					   	     <?
					   	     if($datos_entregados_mov->fields['cantidad_recibida']<0 && abs($datos_entregados_mov->fields['cantidad_recibida'])==$total_pedido)
					   	     {
					   	     ?>
					   	        (Fila Des-Entregada)
					   	     <?
					   	     }
					   	     elseif($datos_entregados_mov->fields['cantidad_recibida']<0)
					   	     {
					   	     	echo "(Código de Barras borrado)";
					   	     }
					   	     ?>
				   	     </b>
				   	    </font>
				   	   </td>
				   	   <td>
				   	    <font color="Black">
				   	     <b>Usuario: <?=$datos_entregados_mov->fields['usuario']?></b>
				   	    </font>
				   	   </td>
				   	   <td align="right">
				   	    <font color="Black">
				   	     <b>Fecha: <?=fecha($datos_entregados_mov->fields['fecha'])?></b>
				   	    </font>
				   	   </td>
				   	  </tr>
				   	 <?
				   	  $datos_entregados_mov->MoveNext();
				   	 }//de while(!$datos_entregados_mov->EOF)
		             ?>
		              </table>
		             </td>
		            </tr>
		            <tr>
		             <td>
		              <?
		              //si hay que mostrar los botones de entregar
		              if($mostrar_botones_entregar) { 
		              	
	                      //me fijo si es rma san luis para diferenciar los casos
	                      ($pm_rma_producto_sl) ? $rma_san_luis =1 : $rma_san_luis = 0;
		              	  
		              	  $link_cb=encode_link("entregar_codigos_barra.php",array("total_comprado"=>$total_pedido,"total_entregado"=>$entregados,"producto_nombre"=>"$producto","id_prod_esp"=>$id_prod_esp,"id_movimiento"=>$id_mov,"id_detalle_movimiento"=>$id_detalle_movimiento,"es_pedido_material"=>$es_pedido_material,"deposito_origen"=>$deposito_origen,"deposito_destino"=>$deposito_destino,"id_licitacion"=>$id_licitacion,"nro_caso"=>$nrocaso,"rma_san_luis"=>$rma_san_luis));
	                      $link_sin_cb=encode_link("entregar_sin_codigos_barra.php",array("total_comprado"=>$total_pedido,"total_entregado"=>$entregados,"producto_nombre"=>"$producto","id_prod_esp"=>$id_prod_esp,"id_movimiento"=>$id_mov,"id_detalle_movimiento"=>$id_detalle_movimiento,"es_pedido_material"=>$es_pedido_material,"deposito_origen"=>$deposito_origen,"deposito_destino"=>$deposito_destino,"id_licitacion"=>$id_licitacion,"nro_caso"=>$nrocaso,"rma_san_luis"=>$rma_san_luis));
			              ?>
			              <input type="button" name="entregar_cb_<?=$items[$x]['id_detalle_movimiento']?>" value="Entregar Productos" onclick="if(vent_cb_<?=$id_detalle_movimiento?>.closed)vent_cb_<?=$id_detalle_movimiento?>=window.open('<?=$link_cb?>','','top=130, left=250, width=450px, height=500px, scrollbars=1, status=1,directories=0');else vent_cb_<?=$id_detalle_movimiento?>.focus();" <?=$permiso?>>
			              <?
			              if(permisos_check("inicio","permiso_entregar_sin_cb"))
			              {
			              ?>
			                <input type="button" name="entregar_sin_cb_<?=$items[$x]['id_detalle_movimiento']?>" value="Entregar sin CB"  onclick="if(vent_sin_cb_<?=$id_detalle_movimiento?>.closed)vent_sin_cb_<?=$id_detalle_movimiento?>=window.open('<?=$link_sin_cb?>','','top=130, left=250, width=400px, height=350px, scrollbars=1, status=1,directories=0');else vent_sin_cb_<?=$id_detalle_movimiento?>.focus();" <?=$permiso?>>
			              <?
			              }//de if(permisos_check("inicio","permiso_entregar_sin_cb"))
			              if($entregados>0 && permisos_check("inicio","permiso_des_entregar_PM_MM"))
			              {?>
			              	<input type="submit" name="des_recibir_<?=$items[$x]['id_detalle_movimiento']?>" value="Des-Entregar" onclick="return des_entregar_func(<?=$items[$x]['id_detalle_movimiento']?>)" <?=$permiso?>>
			              <?
			              }//de if(permisos_check("inicio","permiso_des_recibir_PM_MM"))
		              }//de if($mostrar_botones_entregar)
					  ?>

		             </td>
		            </tr>
		           </table>
		           </td>
		          </tr>
			<?
			   }//de for($x=0;$x<$items['cantidad'];$x++)

			if($mostrar_botones_entregar)
			{?>
			 <tr>
			  <td align="center">
		       <?
	            if($es_pedido_material && permisos_check("inicio","permiso_eliminar_fila_pm_autorizada") && $hay_sin_entregar && $estado!=3)
	            {?>
	               	&nbsp;<input type="submit" name="eliminar_fila" value="Eliminar Filas Autorizadas" onclick="if(confirm('¿Está seguro que desea eliminar las filas seleccionadas?'))return true; else return false;">
	             <?
	            }
	           ?>
	           </td>
	          </tr>
	        <?
			}//de if($mostrar_botones_entregar)
			?>
           </table>
	<?
}//de function generar_form_entrega($pagina_llamado,$id_mov,$es_pedido_material,$mostrar_botones_entregar=1)

function des_entregar_fila_M($id_mov,$deposito_origen,$deposito_destino,$id_detalle_movimiento,$es_pedido_material,$id_licitacion="",$nro_caso="")
{
	global $db,$_ses_user,$titulo_pagina;

	include_once("../stock/funciones.php");

	$db->StartTrans();

	 $avisar_mail_caso=0;
	 $fecha_hoy=date("Y-m-d H:i:s",mktime());
	 
	  $sql = "select produccion_sl,producto_sl,rma_producto_sl from movimiento_material
	            where id_movimiento_material = $id_mov";
	  $detalle = sql($sql) or fin_pagina();
	  
	  $pm_produccion_sl = $detalle->fields["produccion_sl"];
	  $pm_producto_sl   = $detalle->fields["producto_sl"];
	  $pm_rma_producto_sl = $detalle->fields["producto_rma_sl"];


	   //eliminamos los logs de los CB a borrar que hacen referencia a la entrega del producto mediante este movimiento
	   $query="delete from log_codigos_barra
	           where codigo_barra in(select codigo_barra
	                 from codigos_barra_entregados where id_detalle_movimiento=$id_detalle_movimiento)
	                 and tipo ilike '%entregado%$titulo_pagina Nº $id_mov%'";
	   sql($query,"Error al borrar el log de los codigos de barra") or fin_pagina();

	   //borramos los codigos de barra de la tabla codigos_barra_entregados
	   $query="delete from codigos_barra_entregados where id_detalle_movimiento=$id_detalle_movimiento";
	   sql($query,"<br>Error al borrar los CB de la tabla de cb entregados<br>") or fin_pagina();


	   //reducimos la cantidad entregada, por el cb borrado
	   $query="select recibidos_mov.id_recibidos_mov,recibidos_mov.cantidad,detalle_movimiento.id_prod_esp,
	   				  detalle_movimiento.descripcion
	   		   from recibidos_mov join detalle_movimiento using(id_detalle_movimiento)
	           where id_detalle_movimiento=$id_detalle_movimiento and ent_rec=0";
	   $recibido_mov_id=sql($query,"<br>Error al consultar el id de los recibidos<br>") or fin_pagina();
	   $id_recs_mov=$recibido_mov_id->fields["id_recibidos_mov"];
	   $cant_entregada=$recibido_mov_id->fields["cantidad"];
	   $id_prod_esp=$recibido_mov_id->fields["id_prod_esp"];
	   $producto_nombre=$recibido_mov_id->fields["descripcion"];

	   $query="update recibidos_mov set cantidad=0 where id_recibidos_mov=$id_recs_mov";
	   sql($query,"<br>Error al actualizar la cantidad de los recibidos<br>") or fin_pagina();

	   //insertamos en el log este hecho
	   $query_ins="insert into log_recibidos_mov(id_recibidos_mov,usuario,fecha,cantidad_recibida,tipo)
		            values($id_recs_mov,'".$_ses_user["name"]."','$fecha_hoy',-$cant_entregada,'des-entrega de fila')";
	   sql($query_ins,"<br>Error al insertar el log de recibido<br>") or fin_pagina();

	   //-------------------------------------------------------------------------------------------------------
	   //Descontamos los productos des-entregados del stock destino si este es RMA o Produccion.
	   //si es pedido de material debemos descontar del stock de produccion el producto que se esta eliminando
	   //-------------------------------------------------------------------------------------------------------

	    //traemos el id del deposito RMA
	    ($pm_rma_producto_sl)?$nombre_deposito_rma="RMA-Produccion-San Luis":$nombre_deposito_rma="RMA";
		$query="select id_deposito from depositos where nombre='$nombre_deposito_rma'";
    	$id_dep_produccion=sql($query,"<br>Error al traer el id del deposito de RMA<br>") or fin_pagina();
    	$deposito_rma=$id_dep_produccion->fields["id_deposito"];

	   //si es un PM de licitacion o presupuesto, los productos deben descontarse del stock en produccion
	   if($es_pedido_material && $id_licitacion!="")
   	   {
	   	   /********************************************************************
		    Reducimos la cantidad entregada de ese producto en stock de produccion
		   *********************************************************************/
		   //seleccionamos la informacion de la tabla en_produccion para actualizar la entrada correspondiente
		   $query="select en_produccion.cantidad,id_en_stock,id_en_produccion
		           from stock.en_produccion join stock.en_stock using(id_en_stock)
		           where id_prod_esp=$id_prod_esp and id_licitacion=$id_licitacion
		          ";
		   $d_prod=sql($query,"<br>Error al traer datos del producto en stock de produccion<br>") or fin_pagina();
		   $cant_en_prod=$d_prod->fields["cantidad"];
		   $id_en_stock=$d_prod->fields["id_en_stock"];

		   //si la cantidad en stock de produccion para este producto es menor que la cantidad en produccion
		   // damos cartel de error, porque
		   //no se puede des-entregar si la cantidad actual en produccion no es la misma que la entregada.
		   if($cant_en_prod<$cant_entregada)
		    	die("La cantidad en stock de produccion para el producto '$producto_nombre' es menor que la cantidad originalmente entregada. No se puede des-entregar esta fila.<br>");
		   else
		   {
		   		//sino, descontamos de stock en produccion, para la licitacion $id_licitacion,
		   		//ese producto que se esta des-entregando
		   		$comentario="Se des-entregó la fila del producto: $producto_nombre. $titulo_pagina Nº $id_mov";
		   		descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cant_entregada,$comentario);
		   }

      }//de if($es_pedido_material && $id_licitacion!="")
      elseif(($es_pedido_material && $nro_caso!="") || $deposito_destino==$deposito_rma)//si es un PM de CASO o un MM con deposito destino RMA
      {
      	   $avisar_mail_caso=1;
      	   if(($es_pedido_material && $nro_caso!=""))
      	     $es_pm_mm="está asociado al Caso de Servicio Técnico Nº $nro_caso";
      	   else
      	     $es_pm_mm="tiene como depósito destino a RMA";

      }//de elseif($es_pedido_material && $nro_caso!="")
      elseif ($pm_produccion_sl || $pm_producto_sl || $pm_rma_producto_sl){
      	//descontamos del stock destino si se puede esa cantidad
	  	   //solo descontamos del stock destino lo que ya se ha recibido para esta fila
	  	   //Para eso revisamos cuanto de este producto se ha recibido en destino
	  	   $query="select recibidos_mov.id_recibidos_mov,recibidos_mov.cantidad,detalle_movimiento.id_prod_esp
 		   		   from recibidos_mov join detalle_movimiento using(id_detalle_movimiento)
		           where id_detalle_movimiento=$id_detalle_movimiento and ent_rec=1";
	  	   $ya_recibidos=sql($query,"<br>Error al buscar los productos recibidos en deposito destino<br>") or fin_pagina();

	  	   //Si la cantidad recibida es mayor que cero, damos error porque no se puede des-entregar una fila que ya fue recibida
	  	   if($ya_recibidos->fields["cantidad"])
	  	   	die("Esta fila posee recepciones hechas en el depósito destino. No se puede des-entregar la fila");
	  	   	else 
	  	   	descontar_stock_disponible($id_prod_esp,$cant_entregada,$deposito_destino,18,"Se desconto la reserva por des-entrega de pm n°:$id_mov");
      	
      	
      }
	  elseif (!$es_pedido_material)//si es un PM no asociado a nada
	  {
	  	   //solo descontamos del stock destino lo que ya se ha recibido para esta fila
	  	   //Para eso revisamos cuanto de este producto se ha recibido en destino
	  	   $query="select recibidos_mov.id_recibidos_mov,recibidos_mov.cantidad,detalle_movimiento.id_prod_esp
 		   		   from recibidos_mov join detalle_movimiento using(id_detalle_movimiento)
		           where id_detalle_movimiento=$id_detalle_movimiento and ent_rec=1";
	  	   $ya_recibidos=sql($query,"<br>Error al buscar los productos recibidos en deposito destino<br>") or fin_pagina();

	  	   //Si la cantidad recibida es mayor que cero, damos error porque no se puede des-entregar una fila que ya fue recibida
	  	   if($ya_recibidos->fields["cantidad"])
	  	   	die("Esta fila posee recepciones hechas en el depósito destino. No se puede des-entregar la fila");
	  }//de elseif ($es_pedido_material)

	   /*********************************************************************************
       Volvemos a agregar a stock reservado los productos des-entregados
      **********************************************************************************/
      //traemos el id del tipo de movimiento a realizar: Cancelación de Entrega de Productos para PM o MM
	  $query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Cancelación de Entrega de Productos para PM o MM'";
      $tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();

      if($tipo_mov->fields["id_tipo_movimiento"]!="")
		  $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];
	  else
		  die("Error Interno DFPM1405: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

      //traemos el id del tipo de reserva que vamos a realizar
      if($es_pedido_material)
       $nombre_reserva="Reserva Para Pedido de Material";
      else
       $nombre_reserva="Reserva Para Movimiento de Material";

      $query="select id_tipo_reserva from stock.tipo_reserva where nombre_tipo='$nombre_reserva'";
      $tipo_res=sql($query,"<br>Error al traer el id del tipo de reserva<br>") or fin_pagina();
      if($tipo_res->fields["id_tipo_reserva"]!="")
       $id_tipo_reserva=$tipo_res->fields["id_tipo_reserva"];
      else
       die("Error Interno DFPM1419: no se pudo determinar el tipo de reserva de stock. Consulte a la Divisiónm Software<br>");

      //volvemos a agregar como reservado para la fila correspondiente, este producto descontado
      $comentario="Se des-entregó la fila del producto: $producto_nombre. $titulo_pagina Nº $id_mov. Se volvieron a dejar como reservados los productos.";
      agregar_stock($id_prod_esp,$cant_entregada,$deposito_origen,$comentario,$id_tipo_movimiento,"reservado",$id_tipo_reserva,"",$id_detalle_movimiento,$id_licitacion,$nro_caso);

      /****************************************************************
       * Enviamos el mail avisando del hecho
      *****************************************************************/
      $para="juanmanuel@coradir.com.ar,marco@coradir.com.ar";
  	  $asunto="En el $titulo_pagina Nº $id_mov se des-entregó una fila";
  	  $texto="Para el $titulo_pagina Nº $id_mov se des-entregró la fila con el producto '$producto_nombre'\n";

  	  //si esta variable esta seteada significa que el PM está asociado a CAS o el destino del MM es RMA
  	  if($avisar_mail_caso)
  	  {
  	  	$texto.="\n---------------------------------------------------------------------------------------------------\n";
  	  	$texto.="  ATENCION: La fila que se des-entregó para el $titulo_pagina Nº $id_mov $es_pm_mm.\n";
  	  	$texto.="  Por lo tanto, cuando se entregó esta fila se generaron automáticamente uno o más RMA \n";
  	  	$texto.="  que deben ser eliminados porque los productos fueron ahora des-entregados.";
  	  	$texto.="\n---------------------------------------------------------------------------------------------------\n";
  	  }//de if($avisar_mail_caso)

  	  $texto.="\n\nUsuario que des-entregó la fila: ".$_ses_user["name"]." - Fecha: ".date("d/m/Y H:i:s")."\n\n\n";

      //echo $texto;
      enviar_mail($para,$asunto,$texto,'','','','','');

	$db->CompleteTrans();

}//de function des_entregar_fila_PM($id_mov,$id_detalle_movimiento)

function entregar_material_sin_cb($id_movimiento,$es_pedido_material,$id_detalle_movimiento,$cant_insertada,$id_prod_esp,$deposito_origen,$total_comprado,$id_licitacion="",$nro_caso="",$id_proveedor_rma="",$deposito_destino="")
{
 global $_ses_user,$db,$id_stock_rma;

 if($es_pedido_material==1)
  $titulo_pagina="Pedido de Material";
 else
  $titulo_pagina="Movimiento de Material";

 $db->StartTrans();
 $fecha_hoy=date("Y-m-d H:i:s",mktime());

 $query="select id_recibidos_mov,cantidad
         from recibidos_mov where id_detalle_movimiento=$id_detalle_movimiento and ent_rec=0";
 $entrego=sql($query,"<br>Error al traer las entregas hechas previamente para el detalle de movimiento nº $id_detalle_movimiento<br>") or fin_pagina();
 $id_recibido_mov=$entrego->fields["id_recibidos_mov"];
 //controlamos que la cantidad entregada + la que se va a entregar, sea menor que la cantidad comprada
 $ya_entregado=($entrego->fields["cantidad"])?$entrego->fields["cantidad"]:0;
 if($ya_entregado+$cant_insertada>$total_comprado) {
 	    echo "Cantidad Comprada para esta fila: $total_comprado<br>Cantidad ya entregada para esta fila: $ya_entregado<br>Cantidad que se intenta entregar: $cant_insertada<br>";
	    $error_cb1="<font color='red'>\n
	                 LA CANTIDAD ENTREGADA SUPERA A LA ORIGINALMENTE COMPRADA. NO SE PUEDE ENTREGAR ESTA CANTIDAD.<br><br>POSIBLEMENTE YA HA SIDO ENTREGADA LA TOTALIDAD DE ESTA FILA.<br>REVISE EL LOG DE ENTREGA CORRESPONDIENTE.<br>
	                </font>\n";
	    die($error_cb1);
 }
 if($id_recibido_mov=="") {
 	       //insert
	       $query="select nextval('recibidos_mov_id_recibidos_mov_seq') as id_recibido_mov";
	       $id_rec=sql($query,"<br>Error al traer la secuencia del recibido") or fin_pagina();
	       $id_recibido_mov=$id_rec->fields["id_recibido_mov"];
	       $query="insert into recibidos_mov(id_recibidos_mov,id_detalle_movimiento,cantidad,ent_rec)
	       values($id_recibido_mov,$id_detalle_movimiento,$cant_insertada,0)";
 } else {
	    //update
	     $query="update recibidos_mov set cantidad=cantidad+$cant_insertada where id_recibidos_mov=$id_recibido_mov";
	    }

  sql($query,"<br>Error al insertar/actualizar los entregados<br>") or fin_pagina();
  $query_ins="insert into log_recibidos_mov(id_recibidos_mov,usuario,fecha,cantidad_recibida,tipo)
              values($id_recibido_mov,'".$_ses_user["name"]."','$fecha_hoy',$cant_insertada,'entrega')";
  sql($query_ins,"<br>Error al insertar el log de recibido<br>") or fin_pagina();
  //eliminamos las reservas hechas para este movimiento
  $comentario_stock="Utilización de los productos reservados por el $titulo_pagina Nº $id_movimiento";
  $id_tipo_movimiento=7;
  include_once("../stock/funciones.php");
  descontar_reserva($id_prod_esp,$cant_insertada,$deposito_origen,$comentario_stock,$id_tipo_movimiento,$id_fila="",$id_detalle_movimiento);
  if($es_pedido_material==1 && $id_licitacion) {
	      	$comentario_en_produccion="Ingreso de productos a stock de produccion para el Pedido de Material Nº $id_movimiento";
	        //si es pedido de material, agregamos dichos productos al stock de en_produccion, mediante la funcion correspondiente
	        agregar_a_en_produccion($id_prod_esp,$cant_insertada,$comentario_en_produccion,$id_licitacion);
   }//de if($es_pedido_material==1)
   //si no tenemos id de stock de rma lo traemos desde la BD
  if($id_stock_rma=="") {
    //traemos el id del deposito de RMA
	$query="select id_deposito from depositos where nombre='RMA'";
	$st_rma=sql($query,"<br>Error al traer el id de deposito RMA<br>") or fin_pagina();
	$id_stock_rma=$st_rma->fields["id_deposito"];
  }
  if($nro_caso!="" || $deposito_destino==$id_stock_rma) {
	$comentario_rma="Ingreso de productos a RMA mediante el $titulo_pagina Nº $id_movimiento";
	if($es_pedido_material)
	   	 $tipo_log="Creacion PM Nº $id_movimiento";
	     else
	     $tipo_log="Creacion MM Nº $id_movimiento";

  $cb_insertados=array();
  $rma_san_luis = 0;
  if ( is_numeric($id_stock_rma)){
	   $sql = "select nombre from depositos where id_deposito = $id_stock_rma";
	   $res = sql($sql) or fin_pagina();
	   $nombre = $res->fields["nombre"];
	   ($nombre == "RMA-Produccion-San Luis") ? $rma_san_luis = 1 : $rma_san_luis = 0;	      	   
  }	     
    incrementar_stock_rma($id_prod_esp,$cant_insertada,"",$comentario_rma,"",$cb_insertados,"",$id_proveedor_rma,"","","","null","",$nro_caso,"",$tipo_log,"",$id_movimiento,$rma_san_luis);
    //guardamos el id de proveedor elegido para RMA, en la fila del PM o MM (en la tabla detalle_movimiento)
   $query="update mov_material.detalle_movimiento set id_proveedor=$id_proveedor_rma where id_detalle_movimiento=$id_detalle_movimiento";
   sql($query,"<br>Error al actualizar el proveedor de RMA del producto<br>") or fin_pagina();
  }//de if($nro_caso!=""  || $deposito_destino==$id_stock_rma)
	      
  $sql = " select produccion_sl from mov_material.movimiento_material where id_movimiento_material = $id_movimiento";
  $res = sql($sql) or fin_pagina();
  if ($id_licitacion == "" && $nro_caso == "" && $res->fields["produccion_sl"]) {
 	agregar_stock($id_prod_esp,$cant_insertada,$deposito_destino,"Ingreso desde PM",5,"disponible");
  }


if(!$error_cb) {
	 $db->CompleteTrans();
	 }
	 else
	  //esto obliga a hacer un rollback, aun si no ocurrieron errores
	  $db->CompleteTrans(false);
}//de function entregar_material_sin_cb($id_movimiento,$es_pedido_material,$id_detalle_movimiento,$cant_insertada,$id_prod_esp,$deposito_origen,$total_comprado,$id_licitacion="",$nro_caso="",$id_proveedor_rma)


/*****************************************************************************************
  Autoriza el PM o MM pasado como parametro

  @id_mov		El id de movimiento o pedido de material que se va a autorizar
******************************************************************************************/
function autorizar_PM_MM($id_mov,$comentarios)
{
	global $db,$_ses_user;
	
	  $db->StartTrans();

	  $query     = "update movimiento_material set comentarios='$comentarios',estado=2 where id_movimiento_material=$id_mov";
	  //die($query);
	  sql($query) or fin_pagina();
	  $usuario   = $_ses_user['name'];
	  $fecha_hoy = date("Y-m-d H:i:s",mktime());
	  //agregamos el log de creción del reclamo de partes
	  $query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
	              values('$fecha_hoy','$usuario','autorización',$id_mov)";
	  sql($query) or fin_pagina();
	  $datos_mail['Fecha']   = $fecha_hoy;
	  $datos_mail['Usuario'] = $usuario;
	  $datos_mail['Id']      = $id_mov;
	  //variables que utilizo para el mail cuando tiene logistica integrada
	  $datos_mail['id_logistica_integrada'] = $_POST['id_logistica_integrada'];
	  $datos_mail['asociado_a'] = $_POST['asociado_a'];
	  //fin de variables que utilizo cuando hay logistica integrada
	  //enviar_mail_mov($datos_mail);

	  $db->CompleteTrans();

	  return "<b>El $titulo_pagina Nº $id_mov, se autorizó con éxito.</b>";

}//de function autorizar_PM_MM($id_mov,$comentarios)

/*****************************************************************************************
  Anula el PM o MM pasado como parametro

  @id_mov		El id de movimiento o pedido de material que se va a anular
******************************************************************************************/
function anular_PM_MM($id_mov)
{
  global $db,$_ses_user;

  $db->StartTrans();
  $query="update movimiento_material set estado=3 where id_movimiento_material=$id_mov";
  sql($query) or fin_pagina();
  $usuario=$_ses_user['name'];
  $fecha_hoy=date("Y-m-d H:i:s",mktime());
  //agregamos el log de anulacion del movimiento
  $query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
          values('$fecha_hoy','$usuario','anulado',$id_mov)";
   sql($query) or fin_pagina();

   //traemos el deposito de origen del MM o PM
   $query="select movimiento_material.deposito_origen
   		   from mov_material.movimiento_material
   		   where id_movimiento_material=$id_mov";
   $orig=sql($query,"<br>Error al traer el deposito origen del PM o MM<br>") or fin_pagina();

   $deposito_origen=$orig->fields['deposito_origen'];
   descontar_reservados_mov($id_mov,$deposito_origen,1);

   $db->CompleteTrans();

   return "El $titulo_pagina Nº $id_mov fue anulado con éxito";
}//de function anular_PM_MM($id_mov)
?>
