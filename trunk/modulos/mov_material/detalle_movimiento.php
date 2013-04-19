<?
/*AUTOR: MAC - FECHA: 01/07/04

AMB DE MOVIMIENTO DE MATERIAL (mov_material) ENTRE DEPOSITOS DE LA EMPRESA

MODIFICADO POR:
$Author: nazabal $
$Revision: 1.127 $
$Date: 2007/05/28 21:20:41 $
*/

require_once("../../config.php");
include("func.php");
require_once("../general/func_seleccionar_cliente.php");

$es_pedido_material=$_POST["es_pedido_material"] or $es_pedido_material=$parametros["pedido_material"];
if($es_pedido_material=="" && $parametros["_ses_global_extra"]) $es_pedido_material=$parametros["_ses_global_extra"]["pedido_material"];

$modo=$_POST["modo"] or $modo=$parametros["modo"] or $modo=$parametros["pagina"];
$id_mov=$parametros["id"] or $id_mov=$_POST["id_mov"];
$estado=$parametros['estado'] or $estado=$_POST["estado"];
$id_entrega_estimada=$parametros["id_entrega_estimada"] or $id_entrega_estimada=$_POST["id_entrega_estimada"];
$flag_muestra_seg_prod=0;//setea un flag para saber si entra por licitacion o presupuesto

$pm_packaging=$parametros["pm_packaging"] or $pm_packaging=$_POST["pm_packaging"];
$productos_seleccionados=$_POST["id_prod_seleccionados"];
$cantidad_maquinas=$_POST["cantidad_maquinas"];

//estas son todas las variables que obtengo por parametro
//para poder volver a la pagina de detalle de mercaderia en transito
$pagina=$parametros['pagina'] or $pagina=$_POST['pagina'];
$boton_cerrar=$parametros['boton_cerrar'] or $boton_cerrar=$_POST['boton_cerrar'];

$usuario=$_ses_user['name'];
$fecha_hoy=date("Y-m-d H:i:s");

$onclick_cargar=" window.opener.document.all.id_prod_esp.value=document.descontar.id_prod_esp.value;
                  window.opener.document.all.descripcion.value=document.descontar.name_producto.value;
                  window.opener.document.all.cantidad.value=document.descontar.cant_reserv.value;
                  window.opener.document.all.id_deposito.value=document.descontar.deposito.value;
                  window.opener.document.all.precio.value=document.descontar.precio.value;                  
                  window.opener.insertar_fila();                  
                  document.descontar.Volver.onclick();
                  ";

$onclick  = "window.opener.document.form1.nro_factura.value = document.all.nro_factura_cargar.value;
             window.opener.document.form1.id_factura.value = document.all.id_factura_cargar.value;
             window.close();           
            ";

$titulo           = $_POST['titulo'];
$cliente          = $_POST["cliente"];
$id_entidad       = $_POST["id_entidad"];
$deposito_origen  = $_POST['select_origen'] or $deposito_origen=$parametros["deposito_origen"] or $deposito_origen=$_POST['deposito_origen'];
$deposito_destino = $_POST['select_destino'];
$comentarios      = $_POST["observaciones"];
$compras          = $_POST["compras"];

if ($parametros["pagina_viene"]=="producto_lista_material.php" || $parametros["pm_packaging"]==1)
 $deposito_origen=$parametros["deposito_origen"];

$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$_POST["id_licitacion"] or $id_licitacion=$parametros["licitacion"];
$es_presupuesto=$parametros["es_presupuesto"] or $es_presupuesto=$_POST["es_presupuesto"] or $es_presupuesto=$parametros["presupuesto"];
$nrocaso=$parametros["caso"] or $nrocaso=$_POST["nrocaso"];


($_POST["acumulado_servicio_tecnico"])?$acumulado_servicio_tecnico=$_POST["acumulado_servicio_tecnico"]:$acumulado_servicio_tecnico=0;
($_POST["pm_produccion_sl"])?$pm_produccion_sl=$_POST["pm_produccion_sl"]:$pm_produccion_sl=0;
($_POST["pm_producto_sl"])?$pm_producto_sl=$_POST["pm_producto_sl"]:$pm_producto_sl=0;
($_POST["pm_rma_producto_sl"])?$pm_rma_producto_sl=$_POST["pm_rma_producto_sl"]:$pm_rma_producto_sl=0;
($_POST["pm_auditoria_ckd"])?$pm_auditoria_ckd=$_POST["pm_auditoria_ckd"]:$pm_auditoria_ckd=0;
($_POST["pm_venta_directa"])?$pm_venta_directa=$_POST["pm_venta_directa"]:$pm_venta_directa=0;


$titulo_pagina=($es_pedido_material==1)?"Pedido de Material":"Movimiento de Material"; 
$id_factura   = $_POST["id_factura"]; 
$nro_factura  = $_POST["nro_factura"];


$array_parametros_fijos=array("pedido_material"=>$es_pedido_material);

if($_POST["boton_asociar"]=="Asociar"){
 switch	($_POST['radio_asociar']){
 	case "lic":
 		$link=encode_link("../licitaciones/licitaciones_view.php",array("backto"=>"../mov_material/detalle_movimiento.php","modo"=>"asociado_lic","pag"=>"asociado_lic","_ses_global_extra"=>$array_parametros_fijos));
        header("Location:$link");
        break;
  case "pres":
  	    $array_parametros_fijos["es_presupuesto"]=1;
        $link=encode_link("../presupuestos/presupuestos_view.php",array("backto"=>"../mov_material/detalle_movimiento.php","modo"=>"asociado_lic","pag"=>"asociado_lic","_ses_global_extra"=>$array_parametros_fijos));
        header("Location:$link");
        break;
  case "caso":
  	    $link=encode_link("../casos/caso_admin.php",array("backto"=>"../mov_material/detalle_movimiento.php","modo"=>"asociado_caso","coradir_bs_as"=>"no","pag"=>"asociado_caso","_ses_global_extra"=>$array_parametros_fijos));
        header("Location:$link");
        break;
  case "acumulado":
  	    $acumulado_servicio_tecnico=1;
  	    break;      
  //producccion san luis	    
  	    
  case "pm_produccion_sl":
  	    $pm_produccion_sl = 1;
  	    $sql = " select id_deposito from depositos where nombre ='San Luis'";
  	    $res = sql($sql) or fin_pagina();
  	    $deposito_origen = $res->fields["id_deposito"];
  	    break;
  case "pm_producto_sl":
  	    $pm_producto_sl = 1;
  	    $sql = " select id_deposito from depositos where nombre ='Produccion-San Luis'";
  	    $res = sql($sql) or fin_pagina();
  	    $deposito_origen = $res->fields["id_deposito"];
  	    
  	    break;
  case "pm_rma_producto_sl":
  	    $pm_rma_producto_sl = 1;
  	    $sql = " select id_deposito from depositos where nombre ='Produccion-San Luis'";
  	    $res = sql($sql) or fin_pagina();
  	    $deposito_origen = $res->fields["id_deposito"];
  	    
  	    break;
  case "pm_auditoria_ckd":
  	    $pm_auditoria_ckd = 1;
  	    $sql = " select id_deposito from depositos where nombre ='Produccion-San Luis'";
  	    $res = sql($sql) or fin_pagina();
  	    $deposito_origen = $res->fields["id_deposito"];
   	    break;  	    
  //fin de produccion san luis	  
  //Venta Directa
  case "pm_venta_directa":
  	    $pm_venta_directa = 1;
  	    $sql = " select id_deposito from depositos where nombre ='San Luis'";
  	    $res = sql($sql) or fin_pagina();
  	    $deposito_origen = $res->fields["id_deposito"];  	    
   	    break;  	    
  
 }//de switch ($_POST['radio_asociar'])
}//de if($_POST["boton_asociar"]=="Asociar")

$fecha=date("Y-m-d H:m:s");
$usuario=$_ses_user['id'];
if ($_POST['cambio_entidad']=="si_cambio") {
   actualizar_clientes_mas_usuados($id_entidad,$usuario,$fecha);
}//de que se cambio la entidad

if(($_POST['boton_guardar']=="Guardar" || $_POST['guardar']) && $estado!=2) {
	$db->StartTrans();
   	if($id_entidad=="")
      $id_entidad="null";
    
    
    
    
	if(!$id_mov){
		if($id_licitacion=="") $id_licitacion="NULL";
		if($es_presupuesto=="") $es_presupuesto=0;
		if($nrocaso){
			//traemos el id del caso correspondiente
	     $query="select casos_cdr.idcaso from casos.casos_cdr where nrocaso='$nrocaso'";
	     $cas=sql($query,"<br>Error al traer el id del caso<br>") or fin_pagina();
	     $idcaso=$cas->fields["idcaso"];
	    }
	    else
	     $idcaso="NULL";

	    $query="select nextval('movimiento_material_id_movimiento_material_seq') as id_mov";
	    $id_val=sql($query) or fin_pagina();
	    $id_mov=$id_val->fields['id_mov'];
	    $tipo="creación";
	    $msg_op="insertó";
	    $msg_op2="insertar";
	    if($es_pedido_material=="")
	            $es_pedido_material=0;
        if (!$id_entrega_estimada)
                $values_entrega_estimada="NULL";
                else
                $values_entrega_estimada=$id_entrega_estimada;
        if($pm_packaging!=1)
                $pm_packaging=0;

	    $query="insert into movimiento_material(id_movimiento_material,titulo,nombre_cliente,id_entidad,deposito_origen,deposito_destino,fecha_creacion,estado,comentarios,id_licitacion,es_presupuesto,idcaso,es_pedido_material,id_entrega_estimada,pm_packaging,acumulado_servicio_tecnico,produccion_sl,producto_sl,rma_producto_sl,auditoria_ckd,venta_directa,compras)
	            values($id_mov,'$titulo','$cliente',$id_entidad,$deposito_origen,$deposito_destino,'$fecha_hoy',0,'$comentarios',$id_licitacion,$es_presupuesto,$idcaso,$es_pedido_material,$values_entrega_estimada,$pm_packaging,$acumulado_servicio_tecnico,$pm_produccion_sl,$pm_producto_sl,$pm_rma_producto_sl,$pm_auditoria_ckd,$pm_venta_directa,'$compras')";
        }//de if(!$id_mov)
        else{
         	$tipo="actualización";
            $msg_op="actualizó";
            $msg_op2="actualizar";
         	$query="update movimiento_material
                           set titulo='$titulo',deposito_origen=$deposito_origen,
                           deposito_destino=$deposito_destino,comentarios='$comentarios',
                           nombre_cliente='$cliente',id_entidad=$id_entidad,compras='$compras'
                           where id_movimiento_material=$id_mov";
        }//del else de if(!$id_mov)

    sql($query,"<br>Error al insertar/actualizar el $titulo_pagina<br>") or fin_pagina() ;
    $usuario=$_ses_user['name'];
    //agregamos el log de creción del reclamo de partes
    $query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
            values('$fecha_hoy','$usuario','$tipo',$id_mov)";
    sql($query) or fin_pagina();


    //como todos los queries se hicieron correctamente, inseratmos los productos
    insertar_productos($id_mov,$deposito_origen,$deposito_destino,$es_pedido_material,$id_licitacion,$nrocaso);

    //si es acumulado de servicio tecnico
    if ($acumulado_servicio_tecnico || $pm_auditoria_ckd || $pm_producto_sl) {
    	
    	$items_mercaderia_entrante=$_POST["items_mercaderia_entrante"];
    	$sql="delete from mercaderia_entrante where id_movimiento_material=$id_mov";
    	sql($sql) or fin_pagina();
    	   
    	$campos = "id_movimiento_material,id_prod_esp,descripcion,cantidad,precio";
    	for($i=0;$i<$items_mercaderia_entrante;$i++){
    		$id_p_me = $_POST["id_p_me_$i"];
    		$cant_me = $_POST["cant_me_$i"];
    		$desc_me = $_POST["desc_me_$i"];
    		$precio_me = $_POST["precio_me_$i"];
     		if ($id_p_me){
     			$values = "$id_mov,$id_p_me,'$desc_me',$cant_me,$precio_me";
    			$sql="insert into mercaderia_entrante ($campos) values ($values)";	
    			sql($sql) or fin_pagina();    			
    			//echo "$sql<br>";
    		}
    	}//del for
     } //del if
    
    if ($pm_venta_directa && $id_factura) {    	
    	$sql = " delete from movimiento_factura where id_movimiento_material = $id_mov";
    	sql($sql) or fin_pagina();
    	$sql = "insert into movimiento_factura (id_factura,id_movimiento_material) values ($id_factura,$id_mov)";
    	sql($sql) or fin_pagina();
    }
    
    //inserto productos en campo de tabla para ver en el listado de PM
    $items_tabla=get_items_mov();
    $productos_muestra="";
    for($i=0;$i<$items_tabla['cantidad'];$i++){
    	$productos_muestra = $productos_muestra . $items_tabla[$i]['descripcion'] . "\n";
    }
    $sql = "update mov_material.movimiento_material set producto_pedido='$productos_muestra'
    		where id_movimiento_material=$id_mov";
    sql($sql) or fin_pagina();

    //empieza integrar logistica
    if ($_POST['chk_logistica_integrada']=='on'){
    	$id_logistica_integrada = $_POST['id_logistica_integrada'];
    	$con_logis=$_POST['con_logis'];
    	$tel_logis=$_POST['tel_logis'];
    	$dir_logis=$_POST['dir_logis'];
    	$cp_logis=$_POST['cp_logis'];
    	$fecha_envio_logis=Fecha_db($_POST['fecha_envio_logis']);
    	//por aca es una insercion
    	if ($id_logistica_integrada==''){
    		$sql = "insert into mov_material.logistica_integrada
    				(contacto,telefono,direccion,cod_pos,fecha_envio_logis,id_movimiento_material)
            values('$con_logis','$tel_logis','$dir_logis','$cp_logis','$fecha_envio_logis',$id_mov)";
    		sql($sql) or fin_pagina();

    	}
    	//por aca es un update
    	else{
    		$sql = "update mov_material.logistica_integrada
                           set contacto='$con_logis',telefono='$tel_logis',
                           direccion='$dir_logis',cod_pos='$cp_logis',
                           fecha_envio_logis='$fecha_envio_logis'
                           where id_logistica_integrada=$id_logistica_integrada";
    		sql($sql,'No se pudo actualizar Integrar Logistica') or fin_pagina();
    	}

    }//de if ($_POST['chk_logistica_integrada']=='on')
    else{
    //si no esta checkeado y tiene un id_logistica integrada borra
    	$id_logistica_integrada = $_POST['id_logistica_integrada'];
    	if ($id_logistica_integrada!=''){
    		$sql = "delete from mov_material.logistica_integrada
    				where id_logistica_integrada=$id_logistica_integrada ";
    		sql($sql,"No se puede elimiar la logistica integrada") or fin_pagina();
    	}
    }//del else de if ($_POST['chk_logistica_integrada']=='on')
    //termina integrar logistica

    $db->CompleteTrans();
    $msg="El $titulo_pagina Nº $id_mov se insertó/actualizó con éxito";

    if($_POST['boton_guardar']=="Guardar"){
     $link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material,"boton_cerrar"=>$boton_cerrar));
     header("location: $link");

    }

}//de if(($_POST['boton_guardar']=="Guardar" || $_POST['guardar']) && $est!=2)
//si guarda en el estado autorizada es
elseif($estado==2 && $_POST['boton_guardar']=="Guardar") {
	 
	 $db->StartTrans();
	 $comentarios=$_POST["observaciones"];
	 $query="update movimiento_material set comentarios='$comentarios'
	                where id_movimiento_material=$id_mov";
	 sql($query) or fin_pagina();
	 //agregamos los productos al stock destino y lo eliminamos de la mercaderia
	 //en transito
	 $items=get_items_mov($id_mov);
	 for($i=0;$i<$items['cantidad'];$i++) {
	   $id_prod_esp=$items[$i]['id_prod_esp'];
	   $cantidad=$_POST["cant_recib_$i"];
	   $comentario=" Se recibieron productos para el $titulo_pagina Nº $id_mov";

	   if($cantidad>0) {
	               //incrementamos el deposito destino
	               agregar_stock($id_prod_esp,$cantidad,$_POST["select_destino"],$comentario,13);
	               }//de if($cantidad>0)
	   }//de for($i=0;$i<$items['cantidad'];$i++)
	  //agregamos el log de creción del reclamo de partes
	   $query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
	               values('$fecha_hoy','$usuario','actualización',$id_mov)";
	   sql($query) or fin_pagina();
	   if(insertar_recibidos_mov($id_mov))
       	$msg="<center><b>El $titulo_pagina Nº $id_mov se actualizó con éxito</b></center>";
       else
       	$msg="<center><b>El $titulo_pagina Nº $id_mov no se pudo actualizar</b></center>";


    //inserto productos en campo de tabla para ver en el listado de PM
    $items_tabla=get_items_mov();
    $productos_muestra="";
    for($i=0;$i<$items_tabla['cantidad'];$i++){
    	$productos_muestra = $productos_muestra . $items_tabla[$i]['descripcion'] . "\n";
    }
    $sql = "update mov_material.movimiento_material set producto_pedido='$productos_muestra'
    		where id_movimiento_material=$id_mov";
    sql($sql) or fin_pagina();
    //empieza integrar logistica
    if ($_POST['chk_logistica_integrada']=='on') {
    	$id_logistica_integrada = $_POST['id_logistica_integrada'];
    	$con_logis=$_POST['con_logis'];
    	$tel_logis=$_POST['tel_logis'];
    	$dir_logis=$_POST['dir_logis'];
    	$cp_logis=$_POST['cp_logis'];
    	$fecha_envio_logis=Fecha_db($_POST['fecha_envio_logis']);
    	//por aca es una insercion
    	if ($id_logistica_integrada=='') {
    		$sql = "insert into mov_material.logistica_integrada
    				(contacto,telefono,direccion,cod_pos,fecha_envio_logis,id_movimiento_material)
            values('$con_logis','$tel_logis','$dir_logis','$cp_logis','$fecha_envio_logis',$id_mov)";
    		sql($sql) or fin_pagina();

    	}
    	//por aca es un update
    	else {
    		$sql = "update mov_material.logistica_integrada
                           set contacto='$con_logis',telefono='$tel_logis',
                           direccion='$dir_logis',cod_pos='$cp_logis',
                           fecha_envio_logis='$fecha_envio_logis'
                           where id_logistica_integrada=$id_logistica_integrada";
    		sql($sql,'No se pudo actualizar Integrar Logistica') or fin_pagina();
    	}

    }//de if ($_POST['chk_logistica_integrada']=='on')
    else {
         //si no esta checkeado y tiene un id_logistica integrada borra
    	$id_logistica_integrada = $_POST['id_logistica_integrada'];
    	if ($id_logistica_integrada!=''){
    		$sql = "delete from mov_material.logistica_integrada
    				where id_logistica_integrada=$id_logistica_integrada ";
    		sql($sql,"No se puede elimiar la logistica integrada") or fin_pagina();
    	}
    }//del else de if ($_POST['chk_logistica_integrada']=='on')
    //termina integrar logistica


	  $db->CompleteTrans();

	  $link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
	  header("location: $link");
}//de elseif($estado==2 && $_POST['boton_guardar']=="Guardar")

if($_POST["para_autorizar"]=="Para Autorizar" || $_POST['boton_guardar_paraautorizar']) {
                 $db->StartTrans();
                 $query="update movimiento_material set comentarios='$comentarios',estado=1 where id_movimiento_material=$id_mov";
                 if(sql($query)) {
                           $usuario=$_ses_user['name'];
                           $fecha_hoy=date("Y-m-d H:i:s",mktime());
                           //agregamos el log de creción del reclamo de partes
                           $query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
   	                                values('$fecha_hoy','$usuario','pasado a para autorizar',$id_mov)";
                           if(sql($query) or fin_pagina())
                                        $msg="<center><b>El $titulo_pagina Nº $id_mov se actualizó con éxito</b></center>";
                                         else
                                         $msg="<center><b>El $titulo_pagina Nº $id_mov no se pudo actualizar</b></center>";
                         }
                         else
                         $msg="<center><b>El $titulo_pagina no se pudo actualizar</b></center>";
                 $db->CompleteTrans();
                 $msg="El $titulo_pagina Nº $id_mov, se pasó a Para Autorizar";
                 $link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
                 header("location: $link");
}//de if($_POST["para_autorizar"]=="Para Autorizar")

//boton rechazar
if($_POST["Rechazar"]=="Rechazar") {
                 $db->StartTrans();
                 $query="update movimiento_material set estado=0 where id_movimiento_material=$id_mov";
                 if(sql($query)) {
                           $usuario=$_ses_user['name'];
                           $fecha_hoy=date("Y-m-d H:i:s",mktime());
                           //agregamos el log de creción del reclamo de partes
                           $query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
   	                                values('$fecha_hoy','$usuario','Volvio a Pendientes',$id_mov)";
                           if(sql($query) or fin_pagina())
                                        $msg="<center><b>El $titulo_pagina Nº $id_mov se actualizó con éxito</b></center>";
                                         else
                                         $msg="<center><b>El $titulo_pagina Nº $id_mov no se pudo actualizar</b></center>";
                         }
                         else
                         $msg="<center><b>El $titulo_pagina no se pudo actualizar</b></center>";
                 $db->CompleteTrans();
                 $msg="El $titulo_pagina Nº $id_mov, se pasó a Pendientes";
                 $link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
                 header("location: $link");
}//de if($_POST["Rechazar"]

if($_POST["Autorizar"]=="Autorizar") {
	        $db->starttrans();   
	        $msg=autorizar_PM_MM($id_mov,$comentarios);
	        
            if ($acumulado_servicio_tecnico || $pm_auditoria_ckd)
                autorizar_pm_acumulado_servicio_tecnico($id_mov);
                
            if ($pm_produccion_sl|| $pm_rma_producto_sl)
               autorizar_entregar_pm($id_mov);        
            if ($pm_producto_sl)
               autorizar_producto_san_luis($id_mov);    
            
            if ($pm_venta_directa)
                autorizar_venta_directa($id_mov);   
               
                  
            $db->completetrans();
    
            $link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
             header("location: $link");
}//del if de para autorizar

if($_POST["Autorizar_Especial"]=="Autorizar Especial") {
	$db->starttrans(); 
	
	$msg=autorizar_PM_MM($id_mov,$comentarios);
	autorizar_esp_pm_acumulado_servicio_tecnico($id_mov);
	
	$db->completetrans();
	$link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
    header("location: $link");
}//del if de para autorizar

if($_POST["Autorizar_Especial_CKD"]=="Autorizar Especial") {
	$db->starttrans(); 
	
	$msg=autorizar_PM_MM($id_mov,$comentarios);
	autorizar_esp_ckd_pm_acumulado_servicio_tecnico($id_mov);
	
	$db->completetrans();
	$link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
    header("location: $link");
}//del if de para autorizar



if($_POST["Anular"]=="Anular Movimiento") {
 	$msg=anular_PM_MM($id_mov);
    $link=encode_link("listado_mov_material.php",array("msg"=>$msg,"pedido_material"=>$es_pedido_material));
    header("location: $link");
}//de if($_POST["Anular"]=="Anular Movimiento")

if($_POST["eliminar_fila"]=="Eliminar Filas Autorizadas") {
	$db->StartTrans();
	if($deposito_origen=="")
		$deposito_origen=$_POST['h_deposito_origen'];

	$query="select detalle_movimiento.id_detalle_movimiento,detalle_movimiento.id_prod_esp,detalle_movimiento.cantidad,recibidos_mov.cantidad as cant_recibido,recibidos_mov.id_recibidos_mov
	        from mov_material.detalle_movimiento
	        left join mov_material.recibidos_mov using(id_detalle_movimiento)
	        where id_movimiento_material=$id_mov";
	$filas_mov=sql($query,"<br>Error al traer las filas del movimiento de material, para eliminar las seleccionadas<br>") or fin_pagina();

	$se_elimino_algo=0;
	//recorremos todas las filas y eliminamos las seleccionadas
	while (!$filas_mov->EOF) {
		//si el checkboxesta en uno, la fila se selcciono para eliminar
	 	if($_POST["eliminar_".$filas_mov->fields["id_detalle_movimiento"]]==1)	{

	 		if($filas_mov->fields["cant_recibido"]==""||$filas_mov->fields["cant_recibido"]==0)
		 	{
				$se_elimino_algo=1;

		 		$comentario="Cancelación de reserva por eliminación de fila autorizada para el Pedido de Material Nº $id_mov";
		 		//traemos el tipo de movimiento de cancelacion de reserva
		 		$query="select tipo_movimiento.id_tipo_movimiento from stock.tipo_movimiento where nombre='Se canceló la reserva hecha previamente para este producto'";
		 		$tipo_mov_cancelar=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
		 		if($tipo_mov_cancelar->fields["id_tipo_movimiento"]!="")
		 		  $id_tipo_movimiento=$tipo_mov_cancelar->fields["id_tipo_movimiento"];
		 		else
		 		  die("Error Interno DM323: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

		 		//cancelamos la reserva hecha para esta fila de mov material
		 		cancelar_reserva($filas_mov->fields["id_prod_esp"],$filas_mov->fields["cantidad"],$deposito_origen,$comentario,$id_tipo_movimiento,'',$filas_mov->fields["id_detalle_movimiento"]);

		 		//eliminamos las entradas de la tabla log_recibidos_mov y la tabla recibidos_mov, asi podemos luego eliminar
		 		//la fila del MM o PM
		 		if ($filas_mov->fields["id_recibidos_mov"]) {
		 		 $query="delete from mov_material.log_recibidos_mov where id_recibidos_mov=".$filas_mov->fields["id_recibidos_mov"];
		 		 sql($query,"<br>Error al eliminar el log de recepciones de la fila autorizada<br>") or fin_pagina();

  		 		 $query="delete from mov_material.recibidos_mov where id_recibidos_mov=".$filas_mov->fields["id_recibidos_mov"];
		 		 sql($query,"<br>Error al eliminar las recepciones de la fila autorizada<br>") or fin_pagina();
		 		}
		 		//eliminamos el detalle de movimiento del PM
		 		$query="delete from mov_material.detalle_movimiento where id_detalle_movimiento=".$filas_mov->fields["id_detalle_movimiento"];
		 		sql($query,"<br>Error al eliminar la fila de PM con id: ".$filas_mov->fields["id_detalle_movimiento"]."<br>") or fin_pagina();
		 	}//de if($filas_mov->fields["cant_recibido"]==""||$filas_mov->fields["cant_recibido"]==0)
		 	elseif($filas_mov->fields["cant_recibido"]>0)
		 	{   echo "Cantidad recibidos: ".$filas_mov->fields["cant_recibido"]."<br>";
		 		die("Error Interno: No se puede eliminar una fila que tiene recepciones");
		 	}

	 	}//de if($_POST["eliminar_".$filas_mov->fields["id_detalle_movimiento"]]==1)

	 	$filas_mov->MoveNext();
	}//de while(!$filas_mov->EOF)

	//registramos la eliminación de las filas en el log del PM
	if($se_elimino_algo)
	{
		$fecha_hoy=date("Y-m-d H:i:s",mktime());
	 	$usuario=$_ses_user["name"];
	 	$query="insert into log_movimiento(fecha,usuario,tipo,id_movimiento_material)
 	            values('$fecha_hoy','$usuario','eliminación de filas autorizadas',$id_mov)";
      	sql($query,"<br>Error al registrar en el log de PM<br>") or fin_pagina();

      	echo "<center><b>Las filas seleccionadas se eliminaron con éxito</b></center>";
	}//de if($se_elimino_algo)

	$db->CompleteTrans();
}//de if($_POST["eliminar_fila"]=="Eliminar Filas Autorizadas")


if($_POST["guardar_cambios"]=="Guardar Cambios") {
	$db->StartTrans();
	//para cada fila del Pedido o Moviemiento de Material actualizamos el precio
	$items=get_items_mov($id_mov);
	for($i=0;$i<$items["cantidad"];$i++) {
		$precio=$_POST["precio_$i"];
		$id_detalle_movimiento=$items[$i]["id_detalle_movimiento"];
		$query="update mov_material.detalle_movimiento set precio=$precio where id_detalle_movimiento=$id_detalle_movimiento";
		sql($query,"<br>Error al actualizar el precio para la fila Nº: $i <br>") or fin_pagina();
	}//de for($i=0;$i<$items["cantidad"];$i++)
	$db->CompleteTrans();
	echo "<center><b>Los precio fueron actualizados con éxito</b></center>";
}//de if($_POST["guardar_cambios"]=="Guardar Cambios"])

//des-entregar una fila
if($_POST["des_entregar_fila"]!="") {
	$db->StartTrans();

	 $id_detalle_movimiento=$_POST["des_entregar_fila"];
	 //des-entregamos la fila correspondiente
	 des_entregar_fila_M($id_mov,$deposito_origen,$deposito_destino,$id_detalle_movimiento,$es_pedido_material,$id_licitacion,$nrocaso);

	$db->CompleteTrans();

	echo "<center><b>La fila se des-entregó con éxito</b></center>";
}//de if($_POST["des_entregar_fila"]!="")

if($_POST["ajustar_cantidad_PM"]=="A") {
	include_once("../stock/funciones.php");
	$db->StartTrans();
	$id_fila_ajustar=$_POST["fila_ajustar_cantidad"];
	//traemos la cantidad de la fila, la cantidad entregada
	$query="select detalle_movimiento.cantidad,detalle_movimiento.id_prod_esp,detalle_movimiento.descripcion,
				   recibidos_mov.cantidad as cantidad_entregada
			from mov_material.detalle_movimiento
			left join mov_material.recibidos_mov using(id_detalle_movimiento)
			where id_detalle_movimiento=$id_fila_ajustar
			";
	$datos_fila=sql($query,"<br>Error al traer los datos de la fila para ajustar cantidades<br>") or fin_pagina();
	$cant_pedida=$datos_fila->fields["cantidad"];
	$cant_entregada=$datos_fila->fields["cantidad_entregada"];
	$id_prod_esp=$datos_fila->fields["id_prod_esp"];
	$id_deposito=$datos_fila->fields["id_deposito"];
	$nombre_producto=$datos_fila->fields["descripcion"];

	if($cant_entregada=="" || $cant_entregada<=0) {
		echo "<center><b>No se ha entregado ningún producto para la fila seleccionada ($nombre_producto)</b></center>";
	}
	//si la cantidad entregada es menor que la cantidad pedida se puede ajustar la cantidad
	else if($cant_entregada<$cant_pedida) {
		//modificamos la cantidad de la fila, para que tenga la cantidad entregada
		$query="update mov_material.detalle_movimiento set cantidad=$cant_entregada where id_detalle_movimiento=$id_fila_ajustar";
		sql($query,"<br>Error al ajustar la cantidad de la fila<br>") or fin_pagina();

		//traemos el tipo de movimiento de cancelacion de reserva
 		$query="select tipo_movimiento.id_tipo_movimiento from stock.tipo_movimiento where nombre='Se canceló la reserva hecha previamente para este producto'";
 		$tipo_mov_cancelar=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
 		if($tipo_mov_cancelar->fields["id_tipo_movimiento"]!="")
 		  $id_tipo_movimiento=$tipo_mov_cancelar->fields["id_tipo_movimiento"];
 		else
 		  die("Error Interno PMA525: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");


		$comentario="Cancelación de reserva por ajuste de cantidad (tomando la cantidad entregada como referencia) para el Pedido de Material Nº $id_mov";

		$cantidad_cancelar=$cant_pedida-$cant_entregada;
		//liberamos las reservas de esa fila que aun quedan en el sistema (la cantidad a liberar es $cant_pedida-$cant_entregada)
		cancelar_reserva($id_prod_esp,$cantidad_cancelar,$deposito_origen,$comentario,$id_tipo_movimiento,"",$id_fila_ajustar,"");

		//enviamos el mail avisando de este hecho
		$para="juanmanuel@coradir.com.ar";
  	  	$asunto="Se ajustó la cantidad de una fila del $titulo_pagina Nº $id_mov";
  	  	$texto="Para el $titulo_pagina Nº $id_mov se ajustó la cantidad de la fila con el producto '$nombre_producto'.\n";
  	  	$texto.="La cantidad original era: $cant_pedida.\nLa nueva cantidad es: $cant_entregada (equivalente a la cantidad que se entregó para esta fila).\n";
  	  	$texto.="\nLOS PRODUCTOS DESCONTADOS DE LA FILA QUEDARON COMO STOCK DISPONIBLE EN EL DEPOSITO DE ORIGEN.\n";
  	  	$texto.="Usuario que ajustó la cantidad: ".$_ses_user["name"]." - Fecha: ".date("d/m/Y H:i:s")."\n\n\n\n";

  	  	//echo $texto;
  	  	enviar_mail($para,$asunto,$texto,'','','','','');

		echo "<center><b>Se ajustó con éxito la cantidad de la fila: '$nombre_producto'</b></center>";
	}//de if($cant_entregada<$cant_pedida)
	else// la cantidad entregada es igual a la pedida (o mayor...aunque esto no debería suceder jamas)
		echo "<center><b>No hay nada que ajustar para la fila seleccionada ($nombre_producto). La cantidad entregada y la pedida son iguales.</b></center>";

	$db->CompleteTrans();
}//de if($_POST["ajustar_cantidad_PM"]=="A")

echo $html_header;

$link_form=encode_link("detalle_movimiento.php",$array_parametros_fijos);
if($modo=="asociar") {
	?>
	<br>
	<form name='form1' action='<?=$link_form?>' method='POST'>
	<input type='hidden' name='modo' value='asociado'>
	<input type='hidden' name='pagina' value='<?=$pagina?>'>


	<table width='60%' align='center' class='bordes'>
	 <tr id=mo>
        <td>
	    <font size=3>Asociar Pedido de Material a:</font>
	    </td>
     </tr>
	 <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[0].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='lic' onclick="document.all.boton_asociar.disabled=0;"> Licitación
	    </td>
     </tr>
	 <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[1].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='pres' onclick="document.all.boton_asociar.disabled=0;"> Presupuesto
	    </td>
     </tr>
	 <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[2].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='caso' onclick="document.all.boton_asociar.disabled=0;"> Caso de Servicio Técnico
	    </td>
     </tr>
	
     <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[3].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='acumulado' onclick="document.all.boton_asociar.disabled=0;"> Acumulado Servicio Técnico
	    </td>
     </tr>
	 <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[4].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='otro' onclick="document.all.boton_asociar.disabled=0;"> No asociado
	    </td>
     </tr>
     
   </table>  
   <br>  
	<table width='60%' align='center' class='bordes'>
	 <tr id=mo>
        <td>
	    <font size=3>Produccion San Luis</font>
	    </td>
     </tr>	
     <!--
	PM asociados a la produccion de san luis
	-->
     <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[5].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='pm_produccion_sl' onclick="document.all.boton_asociar.disabled=0;"> Producción San Luis
	    </td>
     </tr>
     
     <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[6].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='pm_producto_sl' onclick="document.all.boton_asociar.disabled=0;"> Producto San Luis
	    </td>
     </tr>

     <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[7].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='pm_rma_producto_sl' onclick="document.all.boton_asociar.disabled=0;"> RMA - Producto San Luis
	    </td>
     </tr>
     
     <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[8].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='pm_auditoria_ckd' onclick="document.all.boton_asociar.disabled=0;"> PM de auditoria CKD Monitores
	    </td>
     </tr>
    <!-- Fin de Pm de produccion de San Luis -->
     <tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.boton_asociar.disabled=0;document.all.radio_asociar[9].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='pm_venta_directa' onclick="document.all.boton_asociar.disabled=0;"> PM Venta Directa
	    </td>
     </tr>
	
	 <tr bgcolor=<?=$bgcolor3?>>
	  <td align='center'>
	   <input type='submit' name='boton_asociar' value='Asociar' disabled>
	  </td>
	 </tr>
    </table>
    </form>
    <?
}//de if($modo=="asociar")
else{
    if ($_ses_global_backto) {
    	if (is_array($_ses_global_extra))
               extract($_ses_global_extra,EXTR_SKIP);
        phpss_svars_set("_ses_global_backto", "");
        phpss_svars_set("_ses_global_extra", array());
    }

	//traemos los datos del movimiento de material, si tenemos el id
	if($id_mov) {
	         $query="select movimiento_material.*,casos_cdr.nrocaso, logistica_integrada.*
			         from mov_material.movimiento_material
			         left join casos.casos_cdr using(idcaso)
			         left join mov_material.logistica_integrada using (id_movimiento_material)
			         where movimiento_material.id_movimiento_material=$id_mov";
			 $datos=sql($query) or fin_pagina();
		
			 $titulo  = $datos->fields['titulo'];
			 $cliente = $datos->fields['nombre_cliente'];
			 $id_entidad = $datos->fields['id_entidad'];
			 $deposito_origen  = $datos->fields['deposito_origen'];
			 $deposito_destino = $datos->fields['deposito_destino'];
			 $fecha_creacion   =$datos->fields['fecha_creacion'];
			 $estado = $datos->fields['estado'];
			 $comentarios = $datos->fields['comentarios'];
			 $id_licitacion = $datos->fields["id_licitacion"];
			 $es_presupuesto = $datos->fields["es_presupuesto"];
			 $nrocaso = $datos->fields["nrocaso"];
		     $es_pedido_material = $datos->fields["es_pedido_material"];
		     $fecha_creacion = $datos->fields["fecha_creacion"];
		     $id_logistica_integrada = $datos->fields["id_logistica_integrada"];
		     $con_logis = $datos->fields["contacto"];
		     $tel_logis = $datos->fields["telefono"];
		     $dir_logis = $datos->fields["direccion"];
		     $cp_logis  = $datos->fields["cod_pos"];
		     $dir_logis = $datos->fields["direccion"];
		     $fecha_envio_logis = $datos->fields["fecha_envio_logis"];
		     $pm_packaging = $datos->fields["pm_packaging"];
		     $acumulado_servicio_tecnico = $datos->fields["acumulado_servicio_tecnico"];
		     $pm_produccion_sl   = $datos->fields["produccion_sl"];
		     $pm_producto_sl     = $datos->fields["producto_sl"];
		     $pm_rma_producto_sl = $datos->fields["rma_producto_sl"]; 
		     $pm_auditoria_ckd = $datos->fields["auditoria_ckd"]; 
		     $pm_venta_directa = $datos->fields["venta_directa"];
		     $compras          =$datos->fields["compras"];
		     
		     if ($pm_venta_directa) {
		     	
		     	     $sql = "select (numeracion_sucursal.numeracion || text('-') || facturas.nro_factura ) as nro_factura,
		     	                    movimiento_factura.id_factura
		     	              from mov_material.movimiento_factura
		     	              join facturacion.facturas using (id_factura)
		     	              join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
		     	              where movimiento_factura.id_movimiento_material = $id_mov";
		     	     $fact = sql($sql) or fin_pagina(); 
		     	     $id_factura  = $fact->fields["id_factura"];
		     	     $nro_factura = $fact->fields["nro_factura"];
		     }
		
		     if(compara_fechas($fecha_creacion,"2006-01-04 00:00:00")==-1)//si la fecha de creacion es anterior a 2006-01-04
		      $disabled_gestion2="disabled title='CREADO CON GESTION 2 - NO COMPATIBLE CON LA BASE DE DATOS ACTUAL'";
		     else
		      $disabled_gestion2="";
		
			 if($es_pedido_material==1)
		       $titulo_pagina="Pedido de Material";
			 else
		  	   $titulo_pagina="Movimiento de Material";
			 //traemos los productos de la muestra
			 $items=get_items_mov($id_mov);
	}//de if($id_mov)

	if($estado==3) $permiso="disabled";

    //traemos los depositos
	$query="select * from depositos where tipo<=2 and tipo>=0 order by nombre";
	$depositos=sql($query) or fin_pagina();

	?>
	<script src="../../lib/NumberFormat150.js"></script>
	<script>
   	var wcliente=new Object();
	wcliente.closed=true;

	//FUNCIONES PARA AGREGAR Y ELIMINAR PRODUCTOS
	//variable que contiene la ventana hijo productos
	var wproductos=new Object();
	wproductos.closed=true;

	//variable para mantener el total de los productos de la muestra
	function insertar_fila()
	{
	  var largo,i=0,index,prod,prov,insertar_ok=1,aux_prod_esp,insertar_ok=1;
	  var id_prod_esp,descripcion,cantidad,precio;
	  //recupero los valores de los hidden
	  id_prod_esp=document.all.id_prod_esp.value;
	  descripcion=document.all.descripcion.value;
	  cantidad=document.all.cantidad.value;
	  precio=document.all.precio.value;

	  //controlamos que el producto no este ya insertado. Si lo esta no dejamos insertarlo
	  largo=document.all.items.value;
	  for(index=0;index<largo;index++){
	  	if(typeof(eval("document.all.idp_"+index))!="undefined"){
	  	 aux_prod_esp=eval("document.all.idp_"+index+".value");
	  	 if(aux_prod_esp==id_prod_esp){
	  	   insertar_ok=0;
	  	 }
	  	}
	  }//de for(index;index<largo;index++)

      if(insertar_ok){
	      /*Para insertar una fila*/

		  var items=document.all.items.value++;
		  //inserta al final
		  var fila=document.all.productos.insertRow(document.all.productos.rows.length );
		  //inserta al principio

		  fila.insertCell(0).innerHTML=" <div align='center'> <input name='chk' type='checkbox' value='1'></div><input type='hidden' name='idp_"+items+"' value='"+id_prod_esp +"'>";
		  fila.insertCell(1).width='5%';
		  fila.cells[1].innerHTML="<div align='center'> <input name='cant_"+items+"' type='text' readonly id='cantidad' size='6' value='"+cantidad+"' style='text-align:right'></div>";
		  fila.insertCell(2).width='40%';
		  fila.cells[2].innerHTML="<div align='center'><textarea name='desc_"+items +"' style='width:90%' rows='1' wrap='VIRTUAL' id='descripcion' readonly>"+descripcion+"</textarea></div>";
		  fila.insertCell(3).width='7%';
		  fila.cells[3].innerHTML="<div align='center'> <input name='precio_"+items+"' type='text' readonly id='precio' size='11' value='"+precio+"' style='text-align:right'></div>";

      }//de if(insertar_ok)
      else//no se puede insertar porque ya esta insertado...avisamos
       alert("El producto seleccionado ya fue agregado antes. No se puede volver a agregar.");
 	   wproductos.focus();
	} // de la funcion insertar fila


	//para agregar mercaderia a mercaderia entrante
	var wmercaderia_entrante;
    
function nuevo_item_mercaderia(){
    	
    var descripcion,id_prod_esp,precio,largo,aux_prod_esp;
    var items=document.all.items_mercaderia_entrante.value++;
	//inserta al final
	var fila_m=document.all.mercaderia_entrante.insertRow(document.all.mercaderia_entrante.rows.length );
	//inserta al principio
	
	  //controlamos que el producto no este ya insertado. Si lo esta no dejamos insertarlo
	
 	 id_prod_esp=wmercaderia_entrante.document.all.id_producto_seleccionado.value;
	 descripcion=wmercaderia_entrante.document.all.nombre_producto_elegido.value;
	 precio=wmercaderia_entrante.document.all.precio_producto_elegido.value;
	 
	  

	 insertar_ok=1;
     for(index=0;index<items;index++){
	  	if(typeof(eval("document.all.id_p_me_"+index))!="undefined"){
	  	 aux_prod_esp=eval("document.all.id_p_me_"+index+".value");
         if(aux_prod_esp==id_prod_esp){
	  	   insertar_ok=0;
	  	 }
	  	}
	  }//de for(index;index<largo;index++)

	
	if (insertar_ok){

	fila_m.insertCell(0).innerHTML=" <div align='center'> <input name='chk_me' type='checkbox' value='1'></div><input type='hidden' name='id_p_me_"+items+"' value='"+id_prod_esp +"'>";
	fila_m.insertCell(1).width='5%';
	fila_m.cells[1].innerHTML="<div align='center'> <input name='cant_me_"+items+"' type='text'  id='cantidad' size='6' value='1' style='text-align:right'></div>";
	fila_m.insertCell(2).width='40%';
	fila_m.cells[2].innerHTML="<div align='center'><textarea name='desc_me_"+items +"' style='width:90%' rows='1' wrap='VIRTUAL' id='descripcion_me' readonly>"+descripcion+"</textarea></div>";
	fila_m.insertCell(3).width='7%';
	fila_m.cells[3].innerHTML="<div align='center'> <input name='precio_me_"+items+"' type='text'  id='precio' size='11' value='"+precio+"' style='text-align:right'></div>";
	alert(" Se agregó una nueva fila para el PM Acumulado de Servicio Tecnico");
	wmercaderia_entrante.focus();
	} //del if
	else{
	  alert("Ese producto ya existe para el PM Acumulado de Servicio Tecnicos");
	  wmercaderia_entrante.focus();
	}
   
} //de la funcion
   
   
    //para eliminar de la tabla	         
function borrar_items_mercaderia_entrante() {
		
	var i=0,j=0;var aux,aux1;
	var precio,cantidad;
	
	//cantidad=parseInt(document.all.items_mercaderia_entrante.value);
	
	  while (typeof(document.all.chk_me)!='undefined' &&
			 typeof(document.all.chk_me.length)!='undefined' &&
			 i < document.all.chk_me.length)
	   {
	   /*Para borrar una fila*/
	   if (typeof(document.all.chk_me[i])!='undefined' && document.all.chk_me[i].checked)
	    {
	   	//eliminamos el id del producto del hidden, para indicar que no se debe
	    //volver a insertar
	    aux=eval("document.all.id_p_me_"+j);
	    if(typeof(aux)!='undefined')
	     aux.value="";
	     
	    
/*	    aux1=eval("document.all.idf_"+j);
	    if(typeof(aux1)!='undefined')
	     aux1.value="";*/
	  	document.all.mercaderia_entrante.deleteRow(i+1);
	  	//cantidad--;
	    }
	   else
	     i++;
	   
	   j++;
	  }//del while


	  if (typeof(document.all.chk_me)!='undefined' && document.all.chk_me.checked)
	   {
	  	//eliminamos el id del producto del hidden, para indicar que no se debe
	    //volver a insertar
	    aux=eval("document.all.id_p_me_"+i);
	    if(typeof(aux)!='undefined')
	        aux.value="";
	    document.all.mercaderia_entrante.deleteRow(1);
        //cantidad--; 
	  }
	// document.all.items_mercaderia_entrante.value=cantidad; 
	// alert(document.all.items_mercaderia_entrante.value); 
	} //de la  funcion borrar items
	


	//Llama al archivo correspondiente de stock, segun select_origen
	function nuevo_item() {

	 var pagina_prod;
	 var nbre_deposito;
	 var stock_page;
	 var d=0;


	 if (wproductos==0 || wproductos.closed)
	  {
	   nbre_deposito=document.all.select_origen[document.all.select_origen.selectedIndex].text;

	    //si el nombre del proveedor empieza con la palabra 'Stock' entonces
	    //los productos a seleccionar deben ser solo los que esten en ese stock seleccionado

	   switch(nbre_deposito) {
	         case "San Luis": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_san_luis.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "Buenos Aires": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_buenos_aires.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "New Tree": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_new_tree.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "ANECTIS": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_anectis.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "SICSA": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_sicsa.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "Serv. Tec. Bs. As.": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_st_ba.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "Inventario Buenos Aires": pagina_prod="<?=encode_link($html_root.'/modulos/stock/inventario_bsas.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	          case "Produccion-San Luis": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_produccion_san_luis.php',array('onclick_cargar'=>"$onclick_cargar",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
	                           break;
	                           
	                           
	          default:
	                   d=1;
	                   alert('Para agregar productos, debe seleccionar un Depósito de origen.');

	         }

	         if (!d)  wproductos=window.open(pagina_prod);
	 }
	 else
	  if (!wproductos.closed)

	  wproductos.focus();
	} //de la funcion nuevo item
	/*************************************************/

	function borrar_items()	{
		
	var i=0,j=0;var aux,aux1;
	var precio;
	  while (typeof(document.all.chk)!='undefined' &&
			 typeof(document.all.chk.length)!='undefined' &&
			 i < document.all.chk.length)
	  {
	   /*Para borrar una fila*/
	   if (typeof(document.all.chk[i])!='undefined' && document.all.chk[i].checked)
	   {
	   	//eliminamos el id del producto del hidden, para indicar que no se debe
	    //volver a insertar
	    aux=eval("document.all.idp_"+j);
	    if(typeof(aux)!='undefined')
	     aux.value="";
	    aux1=eval("document.all.idf_"+j);
	    if(typeof(aux1)!='undefined')
	     aux1.value="";
	  	document.all.productos.deleteRow(i+1);

	   }
	   else
	  	i++;
	   j++;
	  }//del while


	  if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
	   {
	  	//eliminamos el id del producto del hidden, para indicar que no se debe
	    //volver a insertar
	    aux=eval("document.all.idp_"+i);
	    if(typeof(aux)!='undefined')
	     aux.value="";
	    document.all.productos.deleteRow(1);

	  }
	} //de la  funcion borrar items


	//funcion que controla que se carguen algunos datos obligatorios
	function control_datos()
	{
	  if(document.all.select_origen.value==-1)
	  {alert('Debe Seleccionar un depósito de origen');
	   return false;
	  }
	  if(document.all.select_destino.value==-1)
	  {alert('Debe Seleccionar un depósito de destino');
	   return false;
	  }

	  //controla integrar logistica
	  if (document.all.chk_logistica_integrada.checked==true){
		  if(document.all.dir_logis.value==''){
		  	alert('Debe Seleccionar una Dirección para Integrar Logística');
		    return false;
		  }
		  if(document.all.fecha_envio_logis.value==''){
		  	alert('Debe Seleccionar una Fecha para Integrar Logística');
		    return false;
		  }
	  }
	  //fin de control integrar logistica

	 return true;
	}

	//al cambiar de stock de origen, se invoca a esta funcion para
	//eliminar todas las filas de la tabla de productos
	function limpiar_tabla()
	{


	 var i=0;
	 var aux;
	 var tam=document.all.productos.rows.length-1;
	 for(i;i<tam;i++)
	 {//eliminamos el id del producto del hidden, para indicar que no se debe
	  //volver a insertar
	  if(typeof(eval("document.all.idp_"+i))!="undefined")
	  {aux=eval("document.all.idp_"+i);
	   aux.value="";
	   document.all.productos.deleteRow(1);
	  }
	 }

	}  //de la funcion limpiar_tabla()

	/***************************************************************************
	Controla que lo que se indique como recibido, no supere la cantidad enviada
	-"posicion" es la posicion que tiene en la tabla el input que toma la
	  cantidad que ingresa el usuario
	-"total_recibido" es el total del producto recibido hasta ahora
	-"total_enviado" es lo que se envio del deposito origen
	-"producto" es el nombre del producto al que se le aplica el control
	-"error_cant_recib" es el nombre del hidden que indica si hubo un error en
	 las cantidades recibidas de alguno de los productos, para que no permita guardar
	****************************************************************************/
	function control_cant_recib(posicion,total_recibido,total_enviado,producto,error_cant_recib)
	{

		//cantidad ingresada por el usuario
		var cantidad=eval("document.all.cant_recib_"+ posicion);
	    var total=total_recibido;
	    //sumamos lo ingresado por el usuario a lo recibido hasta ahora
	    total+=parseInt(cantidad.value);
	    //si supera el total pedido, damos alert y avisamos el error
		if(total>total_enviado)
		{alert('Para el producto \"'+producto+'\":\nel total recibido es mayor que el total enviado.');
		 error_cant_recib.value=1;
		}
		else//sino decimos que no hay error para este producto
		 error_cant_recib.value=0;
	}  //de la control_cant_recib

	function habilitar_cambios_fila(check)
	{
		var cant_filas=document.all.items.value;
		var aux_p,i,valor,visible;

		if(check.checked==true)
		{ valor=0;
		  visible="visible";
		}
		else
		{ valor=1;
		  visible="hidden";
		}

		for(i=0;i<cant_filas;i++)
		{
			aux_p=eval("document.all.precio_"+i);
			aux_p.readOnly=valor;

		}//de for(i=0;i<cant_filas;i++)

		document.all.guardar_cambios.style.visibility=visible;

	}//de function habilitar_cambios_fila()

	function control_precios()
	{
		var cant_filas=document.all.items.value;
		var aux_precio,aux_desc,i;

		for(i=0;i<cant_filas;i++)
		{
			aux_precio=eval("document.all.precio_"+i);
			aux_desc=eval("document.all.desc_"+i);
			if(aux_precio.value=="" || aux_precio.value<=0)
			{
				alert("El precio para el producto '"+aux_desc.value+"' no es válido");
				return false;
			}//de if(aux_precio.value=="" or aux_precio.value<=0)

		}//de for(i=0;i<cant_filas;i++)

		return true;
	}//de function control_precios()
	
function cargar_cliente() {
 document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
 //indica que se debe actualizar los clientes mas usuados
 document.all.cambio_entidad.value="si_cambio";

}	
</script>
	<?//traemos y luego generamos el log del reclamo de partes


if($id_mov) {
   $query="select * from log_movimiento where id_movimiento_material=$id_mov order by id_log_movimiento DESC";
    $log=sql($query) or fin_pagina();
?>

<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> ">
<table width="95%" cellpadding="1" cellspacing="1" align="center">
<?
while(!	$log->EOF) {
  list($fecha,$hora)=split(" ",$log->fields['fecha']);
  ?>
  <tr id=ma>
	 <td align="left">
	   Fecha de <?=$log->fields['tipo']?>: <?=fecha($fecha)?> <?=$hora?>
	 </td>
	 <td align="right">
	   Usuario: <?=$log->fields['usuario']?>
	 </td>
  </tr>
  <?
  $log->MoveNext();
 }
?>
</table>
</div>
<?	}//de if($id_mov)  ?>
<br>
<form name="form1" method="POST" action="<?=$link_form?>" <?=$disabled_gestion2?>>
	<input type='hidden' name=id_prod_esp value="">
	<input type='hidden' name=id_mov value="<?=$id_mov?>">
	<input type='hidden' name=descripcion value="">
	<input type='hidden' name=cantidad value="">
	<input type='hidden' name=precio value="">
	<input type='hidden' name="id_deposito" value="">
	<input type='hidden' name='modo' value='asociado'>
	<input type='hidden' name='id_licitacion' value='<?=$id_licitacion?>'>
	<input type='hidden' name='es_presupuesto' value='<?=$es_presupuesto?>'>
	<input type='hidden' name='nrocaso' value='<?=$nrocaso?>'>
	<input type='hidden' name='estado' value='<?=$estado?>'>
	<input type='hidden' name='es_pedido_material' value='<?=$es_pedido_material?>'>
	<input type='hidden' name='h_deposito_origen' value='<?=$deposito_origen?>'>
	<input type='hidden' name='boton_cerrar' value='<?=$boton_cerrar?>'>
	<input type='hidden' name='id_entrega_estimada' value='<?=$id_entrega_estimada?>'>
	<input type='hidden' name='pm_packaging' value='<?=$pm_packaging?>'>
	<input type='hidden' name='acumulado_servicio_tecnico' value='<?=$acumulado_servicio_tecnico?>'>
	<input type='hidden' name='pm_produccion_sl'   value='<?=$pm_produccion_sl?>'>
	<input type='hidden' name='pm_producto_sl'     value='<?=$pm_producto_sl?>'>
	<input type='hidden' name='pm_rma_producto_sl' value='<?=$pm_rma_producto_sl?>'>
	<input type='hidden' name='pm_auditoria_ckd' value='<?=$pm_auditoria_ckd?>'>
    <input type='hidden' name='pm_venta_directa' value='<?=$pm_venta_directa?>'>	
    <input type="hidden" name="id_factura" value="<?=$id_factura?>">
	<?
	//determinamos a que esta asociado el pedido de material
	if($id_licitacion && $es_presupuesto) {
		$flag_muestra_seg_prod=1;
		$link_pres = encode_link("../presupuestos/presupuestos_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));
		$asociado="Presupuesto Nº $id_licitacion";
		//traemos el color del estado y la entidad de la licitacion
		$query="select estado.color,estado.nombre,entidad.id_entidad,entidad.nombre as nbre_entidad
		        from licitaciones.licitacion join licitaciones.estado using(id_estado)
		        join licitaciones.entidad using(id_entidad)
		        where licitacion.id_licitacion=$id_licitacion";
		$estado_lic=sql($query,"<br>Error al traer el color del estado de la licitacion<br>") or fin_pagina();
        $estado_lic_color=$estado_lic->fields["color"];
        $estado_lic_nombre=$estado_lic->fields["nombre"];
        $nombre_entidad=$estado_lic->fields["nbre_entidad"];
        $id_entidad=$estado_lic->fields["id_entidad"];
		?>
		<table align='center'>
	    <tr>
	     <td>
	      &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' ><b>Pedido de Material asociado a Presupuesto</font>
	     </td>
	     <td bgcolor="<?=$estado_lic_color?>" title="Estado de la Licitación: <?=$estado_lic_nombre?>">
	     <?
	      $frente="#000000";
	      $reemplazo="#ffffff";
	      $color_link=contraste($estado_lic_color, $frente, $reemplazo);?>
	      <font size=3 color='red' >
	       <b><a href="<?=$link_pres?>" style="font-size='16'; color='<?=$color_link?>';" target="_blank"><U><?=$id_licitacion?></U></A>
	      </font>
	     </td>
	    </tr>
	   </table>
	   <?
	}
	elseif ($id_licitacion)
	{

        $flag_muestra_seg_prod=1;
		$asociado="Licitación Nº $id_licitacion";
		$link_lic = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));
		//traemos el color del estado y la entidad de la licitacion
		$query="select estado.color,estado.nombre,entidad.id_entidad,entidad.nombre as nbre_entidad
		        from licitaciones.licitacion
		        	 join licitaciones.estado using(id_estado)
		             join licitaciones.entidad using(id_entidad)
		        where licitacion.id_licitacion=$id_licitacion";
		$estado_lic=sql($query,"<br>Error al traer el color del estado de la licitacion<br>") or fin_pagina();
        $estado_lic_color=$estado_lic->fields["color"];
        $estado_lic_nombre=$estado_lic->fields["nombre"];
        $nombre_entidad=$estado_lic->fields["nbre_entidad"];
        $id_entidad=$estado_lic->fields["id_entidad"];
		?>
		<table align='center'>
	    <tr>
	     <td>
	      &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' ><b>Pedido de Material asociado a Licitación</font>
	     </td>
	     <td bgcolor="<?=$estado_lic_color?>" title="Estado de la Licitación: <?=$estado_lic_nombre?>">
	     <?
	      $frente="#000000";
	      $reemplazo="#ffffff";
	      $color_link=contraste($estado_lic_color, $frente, $reemplazo);?>
	      <font size=3 color='red' >
	       <b><a href="<?=$link_lic?>" style="font-size='16'; color='<?=$color_link?>';" target="_blank"><U><?=$id_licitacion?></U></A>
	      </font>
	     </td>
	    </tr>
	   </table>
	 <?
	}//de elseif ($id_licitacion)
	elseif ($nrocaso) {
		$asociado="Caso de Servicio Técnico Nº $nrocaso";

		//traemos el id del caso correspondiente
	    $query="select casos_cdr.idcaso,entidad.id_entidad,entidad.nombre as nbre_entidad
			      from casos.casos_cdr
			      join casos.dependencias using (id_dependencia)
			      join licitaciones.entidad using (id_entidad)
			      where casos_cdr.nrocaso=$nrocaso";
	    $cas=sql($query,"<br>Error al traer el id del caso<br>") or fin_pagina();
	    $idcaso=$cas->fields["idcaso"];
	    $id_entidad=$cas->fields["id_entidad"];
	    $nombre_entidad=$cas->fields["nbre_entidad"];

		$link = encode_link("../casos/caso_estados.php",array("id"=>$idcaso,"id_entidad"=>$id_entidad));
    ?>
      <b><div align='center'>
        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Pedido de Material asociado al Número de Caso <a href="<?=$link?>" style="font-size='16'; color='red';" target="_blank" title="Ver el caso de Servicio Técnico."><U><?=$nrocaso?></U></A> de Servicio Técnico</font></div>
      </b>
    <?
	}//de elseif ($nrocaso)
	elseif ($acumulado_servicio_tecnico) {
		$asociado   = "Acumulado Servicio Tecnico";
		$id_entidad = "441";
		$cliente    = "Coradir S.A.";
		if($es_pedido_material) {
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Acumulado de Servicio Tecnico</font></div>
	      </b>
    <?
		}//de if($es_pedido_material)
    } //del elseif
    elseif ($pm_produccion_sl) {
    	$asociado = "PM Producción San Luis";
    	$id_entidad = "441";
    	$cliente = "Coradir S.A.";
		if($es_pedido_material) {
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >PM Producción San Luis</font></div>
	      </b>
    <?
		}//de if($es_pedido_material)
    	
    }
    elseif ($pm_producto_sl){
    	$asociado = "PM Productos San Luis";
    	$id_entidad = "441";
    	$cliente = "Coradir S.A.";
		if($es_pedido_material) {
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >PM Productos -  San Luis</font></div>
	      </b>
    <?
		}//de if($es_pedido_material)
    	
    }
    elseif ($pm_rma_producto_sl){
    	$asociado = "PM RMA Productos San Luis";
    	$id_entidad = "441";
    	$cliente = "Coradir S.A.";
		if($es_pedido_material) {
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >PM RMA - Productos -  San Luis</font></div>
	      </b>
    <?
		}//de if($es_pedido_material)
    	
    }

    elseif ($pm_auditoria_ckd){
    	$asociado = "PM de auditoria CKD Monitores";
    	$id_entidad = "441";
    	$cliente = "Coradir S.A.";
		if($es_pedido_material) {
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >PM de auditoria CKD Monitores</font></div>
	      </b>
    <?
		}//de if($es_pedido_material)
    	
    } elseif ($pm_venta_directa ){
    	$asociado = "PM Venta Directa";
		if($es_pedido_material) {
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >PM Venta Directa</font></div>
	      </b>
    <?    	
		}
    }  
    
	else
	{
		$asociado="No está asociado a nada";
	
		if($es_pedido_material)
		{
	?>
	      <b><div align='center'>
	        &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Pedido de Material NO asociado a nada</font></div>
	      </b>
    <?
		}//de if($es_pedido_material)
	}//del else de elseif ($nrocaso)
    
	
	/*if($es_pedido_material==1 && $asociado=="No está asociado a nada")
	 die("Error: El PEDIDO DE MATERIAL NO ESTA ASOCIADO A NADA.");*/

	if($cliente=="")
	{
	 	$cliente=$nombre_entidad;
	}
	?>

	<table width="95%" align="center" border="1">
	 <tr>
	  <td align="center">
	   <table width="100%" cellpadding="5">
	    <tr>
	     <td align="center" colspan="2" id=mo>
	      <font size="3"><b><?=$titulo_pagina?> Nº <?=$id_mov?></b></font>
	     </td>
	    </tr>
	   </table>
	  </td>
	 </tr>
	 <tr>
	  <td>
	   <table width="100%" cellpadding="3">
		<?
		if ($estado == 3)
		{?>
			<tr>
				<td align="center" colspan="2">
				<font color="Red"><font size=2><B><?=$titulo_pagina?> Anulado</B></font></FONT>
				</td>
			</tr>
		<?
		}

		if($es_pedido_material)
		{
			?>
		    <tr>
		     <td>
		      <?
		      if($asociado=="No está asociado a nada" || $pm_venta_directa)
		      {
		      ?>
		       <a title="Haga click para ver elegir/editar el cliente" style="cursor:hand"
                 onclick="if (wcliente==0 || wcliente.closed)
	                                    wcliente=window.open('<?=encode_link('../general/seleccionar_clientes.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
                       else
	                   if (!wcliente.closed)     
	 	               wcliente.focus();">

	            <b><u>Cliente</u></b>
	           </a>
		      <?
		       }
		       else
		       {
		      ?>
        	     <b>Cliente</b>
        	  <?
		       }
        	  ?>
		     </td>
		     <td>
		      <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
		      <input name="cambio_entidad" type="hidden" value="no_cambio">
		      <input type="text" name="cliente" value="<?=$cliente?>" size="80" <?=$permiso?> readonly>
		     </td>
		    </tr>
		 <?
		}//de if($es_pedido_material)
		else
		{
		 ?>
		    <tr>
		     <td>
		      <b>Título</b>
		     </td>
		     <td>
		      <input type="text" name="titulo" value="<?=$titulo?>" size="80" <?=$permiso?>>
		     </td>
		    </tr>
		<?
		}//del else de if($es_pedido_material)
		?>
	    <tr>
	     <td>
	      <b>Depósito Origen</b>
	     </td>
	     <td>
	      <select name="select_origen" onchange="limpiar_tabla()" <?=$permiso?>>
	       <option value="-1">Seleccione depósito origen</option>
	       <?
	       
	      while(!$depositos->EOF){
	       	if($depositos->fields['tipo']==0){?>
	         <option value="<?=$depositos->fields['id_deposito']?>" <?if($deposito_origen==$depositos->fields['id_deposito'])echo "selected"?>><?=$depositos->fields['nombre']?></option>
	         <?
	        }//de if($depositos->fields['tipo']==0)
	        $depositos->MoveNext();
	       }
	       ?>
	      </select>
	     </td>
	    </tr>
	    <?
	    if($es_pedido_material==1 && !$pm_producto_sl)
	    {
	    	//si es pedido de material traemos el id del stock  rma si esta asociado  un caso
	    	// o el de de Produccion
	    	if ($nrocaso || $acumulado_servicio_tecnico || $pm_auditoria_ckd) $dep='RMA';
	    	 elseif ($pm_produccion_sl) $dep = "Produccion-San Luis";  
	    	 elseif ($pm_rma_producto_sl) $dep= "RMA-Produccion-San Luis";
	    	 
	    	else $dep="Produccion";

	    	$query="select id_deposito from depositos where nombre='$dep'";
	    	$id_dep_produccion=sql($query,"<br>Error al traer el id del deposito de $dep<br>") or fin_pagina();
	    	$deposito_destino=$id_dep_produccion->fields["id_deposito"];
	    	//y solo generamos un hidden con el id de deposito de produccion, el cual es el destino en todos los pedidos de material
	    	?>
	    	<input type="hidden" name="select_destino" value="<?=$deposito_destino?>">
	    	<?
	    }//de if($es_pedido_material==1)
	    else
	    {
		    ?>
		    <tr>
		     <td>
		      <b>Depósito Destino</b>
		     </td>
		     <td>
		      <select name="select_destino"  <?=$permiso?>>
		       <option value="-1">Seleccione depósito destino</option>
		       <?
		       $depositos->Move(0);
		       while(!$depositos->EOF){
		       	?>
		        <option value="<?=$depositos->fields['id_deposito']?>" <?if($deposito_destino==$depositos->fields['id_deposito'])echo "selected"?>>
		          <?=$depositos->fields['nombre']?>
		        </option>
		        <?
		        if($depositos->fields['nombre']=="RMA")
		         $id_stock_rma=$depositos->fields['id_deposito'];

		        $depositos->MoveNext();
		       }//de while(!$depositos->EOF)
		       ?>
		      </select>
		      <?
		      if($estado==2){//generamos el hidden para que tome el id de deposito destino, ya que
		       //el select esta deshabilitado
		       ?>
		       <input type="hidden" name="select_destino" value="<?=$deposito_destino?>">
		       <?
		      }
		      ?>
		     </td>
		    </tr>
		 <?
	    }//del else de if($es_pedido_material==1)
		 ?>
	   </table>
	  </td>
	 </tr>

	 <?//Empieza la seccion de integrar logistica?>
	 <tr>
	  <td>
	   <input type="hidden" name="id_logistica_integrada" value="<?=$id_logistica_integrada?>">
	   <?//le damos un valor a hidden de acuerdo a que esta asociado (sirve para el mail)
	   $value_hidden_asociado="otro";
	   if ($nrocaso)$value_hidden_asociado="caso";
	   //if ($nro_caso)$value_hidden_asociado="caso";
	   if ($id_licitacion)$value_hidden_asociado="lic";
	   ?>
	   <input type="hidden" name="asociado_a" value="<?=$value_hidden_asociado?>">
	   <input type="checkbox" name="chk_logistica_integrada" onclick="javascript: (this.checked)?Mostrar('muestra_tabla') :Ocultar ('muestra_tabla');" <?if ($id_logistica_integrada)echo "checked ";?> <?=$permiso?>>
	   <b><font color="Blue">Integrar Logistica</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	   <?if ($id_logistica_integrada) echo "<font size='2'>Este Material Debe ser Enviado a:</font></b>";
	  	   	 else echo "<font size='2' color='red'>Este Material NO Tiene Logistica Integrada</font></b>";
	   ?>
	  </td>
	 </tr>

	 <tr>
	  <td>
	  	   <?if ($id_logistica_integrada) $display_div="display:block";
	  	   	 else $display_div="display:none";
	  	   ?>
		   <div align="center" style='<?=$display_div?>' id="muestra_tabla">
		 	  <table width=100% align="center">
		       <tr align="center">
		         <td>
		           <b><font color="Red">* </font>Dirección:</b>&nbsp;<textarea name='dir_logis' cols="65" rows="2" <?=$permiso?>><?=$dir_logis?></textarea>
		         </td>
		         <td>
		           <?cargar_calendario();
		           $dia_default = date('d/m/Y');
		           ?>
		           &nbsp;&nbsp;&nbsp;<b><font color="Red">* </font>Fecha Envio:</b>&nbsp;<input type='text' name='fecha_envio_logis' id='fecha_envio_logis' size=15 value='<?=($id_logistica_integrada)?Fecha($fecha_envio_logis):date('d/m/Y',mktime(0, 0, 0, date("m") , date("d")+1, date("Y")));?>' readonly <?=$permiso?>> <?=link_calendario("fecha_envio_logis");?>
		         </td>
		         <td>
		         	<b><font color="Red">* Son Datos Obligatorios</font></b>
		         </td>
		        </tr>

		        <tr align="center">
		          <td>
		            <b>&nbsp;&nbsp;&nbsp;Contacto:</b>&nbsp;<input type='text' name='con_logis' value='<?=$con_logis?>' size=65  <?=$permiso?>>
		          </td>
		          <td>
		            <b>Telefono:</b>&nbsp;<input type='text' name='tel_logis' value='<?=$tel_logis?>' size=20  <?=$permiso?>>
		          </td>
		          <td>
		            <b>Código Postal:</b>&nbsp;<input type='text' name='cp_logis' value='<?=$cp_logis?>' size=10  <?=$permiso?>>
		          </td>
		        </tr>
		      </table>
		   </div>
      </td>
	 </tr>
   </table>	
	<?
	if ($pm_venta_directa) {
		$link = encode_link("../facturas/factura_listar.php",array("backto"=>"detalle_movimiento.php","onclick"=>$onclick));
	?>
	<br>
	<table width="95%" align="center" class="bordes" border="0">
	  <tr id="mo">
	    <td colspan="2">Detalles Venta Directa</td>
	  </tr>
	  <tr>
         <td width="15%">
            <b>Facturas Cliente</b>
          </td>
          <td align="left">
          <input type="button" name="atar_factura" value="Atar Factura" onclick="window.open('<?=$link?>')">
          &nbsp;
          <select name="select_atar_facturas">
             <option>Nro de Factura</option>
          </select>
          </td>
       </tr>
	        <tr>
	          <td > <b> Facturas </b> </td>
	          <td align="left"><input type="text" name="nro_factura" value="<?=$nro_factura?>" size="20" readonly>
	          	<? if ($id_factura) {
	          		$link_fact=encode_link("../facturas/factura_nueva.php",array("id_factura"=>$id_factura,"volver_remito"=>1));
	          	?>
					<input type="button" name="bfactura" value="Ver factura" onclick="window.open('<?=$link_fact?>','','');">
				<? }?>
			  </td>
	        </tr>
	  </tr>	  
	  <tr>
	    <td><b>Compras</b></td>
	    <td><input type="text" name="compras" value="<?=$compras?>" size="80"></td>
	  </tr>	  
	</table>
	<br>
	<?
	}
	?>
	   

	<table width="95%" align="center" border="1">
	 <tr>
	  <td>
	   <table width="100%" align="center">
	    <tr id=mo>
	     <td align="center">
	      <font size="2"><b>Productos del <?=$titulo_pagina?></b></font>
        </td>
	    </tr>
	    <?
	    if($id_mov && $estado!=2 && $estado!=3 && $estado!=4 && permisos_check("inicio","permiso_editar_fila_pedido_material"))
	    {
	    ?>
	    <tr>
	     <td>
	     	<input type="checkbox" name="modif_filas" value="1" onclick="habilitar_cambios_fila(this)" class="estilos_check"> Modificar Filas
	     </td>
	    </tr>
	    <?
	    }//de if(permisos_check("inicio","permiso_editar_fila_pedido_material"))
	    ?>
	    <tr>
	     <td>
	     <?
	     //si el estado es distinto de autorizado o de anulado o de finalizado
	     if($estado!=2 && $estado!=3 && $estado!=4) {
	     ?>
	       <table width="100%" align="center" id="productos">
	        <tr id=ma>
	         <td width="1%"><input type="checkbox" name="chk_all" onclick="seleccionar_todos(this,document.all.chk)"></td>
	         <td width="5%">Cantidad</td>
	         <td width="40%">Descripción</td>
	         <td width="7%">Precio</td>
	        </tr>
	        <?
	        if($id_mov && !$id_entrega_estimada) {
	         $cnr=1;
 	         //SI SE LLEGA A RECARGAR LA PAGINA EN ALGUN MOMENTO, SE PIERDEN ESTOS
	         //DATOS. ENTONCES HAY QUE AGREGAR LA PARTE QUE TOMA DATOS DEL POST.
	         //FUNCIONAMIENTO SIMILAR AL DE ORDENES DE COMPRA
	         $total_precio = 0;
	         for($x=0;$x<$items['cantidad'];$x++){
	        ?>
	          <tr>
	           <td>
	            <input type="hidden" name="idp_<?=$x?>" value="<?=$items[$x]['id_prod_esp']?>">
	            <input type="hidden" name="idf_<?=$x?>" value="<?=$items[$x]['id_detalle_movimiento']?>">
	            <input type="checkbox" <?=$permiso?> name="chk" value="1">
	           </td>
	           <td align="center">
	            <input name="cant_<?=$x?>" type="text" size="6" <?=$permiso?>  style='text-align:right' value="<?=$items[$x]['cantidad']?>" readonly >
	           </td>
	            <td>
	            <div align="center">
	             <textarea name="desc_<?=$x?>" style="width:90%" rows="1" wrap="VIRTUAL" <?=$permiso?> id="descripcion" readonly><?=stripcslashes($items[$x]['descripcion'])?></textarea>
	            </div>
	           </td>
	           <td align="center">
	            <input name="precio_<?=$x?>" type="text" size="11" <?=$permiso?>  style='text-align:right' value="<?=(($items[$x]['precio'])!="")?number_format($items[$x]['precio'],2,'.',''):"";?>" readonly onchange="control_numero(this,'Precio para el producto <?=stripcslashes($items[$x]['descripcion'])?>')">
	           </td>
	          </tr>
	        <?
	        $total_precio+=$items[$x]['precio'];
	         }//de for($x=0;$x<$items['cantidad'];$x++)
	         $items=$x;
	         if ($pm_produccion_sl || $pm_producto_sl || $pm_auditoria_ckd || $pm_rma_producto_sl){
	         ?>
	           <tr>
	             <td colspan="3" align="right"><b>Total:</b></td>
	             <td align="center">
	               <input name="total" type="text" size="11" style='text-align:right' value="<?=number_format($total_precio,2,'.','')?>">
	             </td>	             
	           </tr>
	         <?
	         }
		 	}//de if($id_mov && !$id_entrega_estimada)
		 	elseif ($nrocaso) {

		 	   $items=get_items_producto_lista_material_caso($nrocaso,$deposito_destino);
	            for($x=0;$x<$items['cantidad'];$x++) {
	            	if ($items[$x]["cantidad"]>0) {
			            ?>
			             <tr>
				           <td>
				            <input type="hidden" name="idp_<?=$x?>" value="<?=$items[$x]['id_prod_esp']?>">
				            <input type="hidden" name="cantidad_res_<?=$x?>" value="<?=$items[$x]['cantidad_res']?>">
				            <input type="checkbox" <?=$permiso?> name="chk" value="1">
				           </td>
				           <td align="center">
				            <input name="cant_<?=$x?>" type="text" size="6" <?=$permiso?>  style='text-align:right' value="<?=$items[$x]["cantidad"]?>" readonly >
				           </td>
				            <td>
				            <div align="center">
				             <textarea name="desc_<?=$x?>" style="width:90%" rows="1" wrap="VIRTUAL" <?=$permiso?> id="descripcion" readonly><?=stripcslashes($items[$x]['descripcion'])?></textarea>
				            </div>
				           </td>
				           <td align="center">
				            <input name="precio_<?=$x?>" type="text" size="11" <?=$permiso?>  style='text-align:right'  value="<?=(($items[$x]['precio'])!="")?number_format($items[$x]['precio'],2,'.',''):"";?>" readonly onchange="control_numero(this,'Precio para el producto <?=stripcslashes($items[$x]['descripcion'])?>')">
				           </td>
				          </tr>
			            <?
	                }//de if ($items[$x]["cantidad"]>0)

	            }//de for($x=0;$x<$items['cantidad'];$x++)

	             $items=$x;
	        ?>     
	        </table>	             
	        <?
		  	}//de elseif ($nrocaso)
            elseif($id_entrega_estimada) {
				if($pm_packaging)//si es un PM de packaging generamos las filas con los productos pasados como parametro
				  $items=get_items_producto_lista_material_packaging($productos_seleccionados,$deposito_destino,$cantidad_maquinas);
				else
	              $items=get_items_producto_lista_material($id_entrega_estimada,$deposito_destino, $id_licitacion);
				$index_r=0;
	            for($x=0;$x<$items['cantidad'];$x++) {
	            	if ($items[$x]["cantidad"]>0) {
			            ?>
			             <tr>
				           <td>
				            <input type="hidden" name="idp_<?=$index_r?>" value="<?=$items[$x]['id_prod_esp']?>">
				            <input type="hidden" name="cantidad_res_<?=$index_r?>" value="<?=$items[$x]['cantidad_res']?>">
				            <input type="checkbox" <?=$permiso?> name="chk" value="1">
				           </td>
				           <td align="center">
				            <input name="cant_<?=$index_r?>" type="text" size="6" <?=$permiso?>  style='text-align:right' value="<?=$items[$x]["cantidad"]?>" readonly >
				           </td>
				            <td>
				            <div align="center">
				             <textarea name="desc_<?=$index_r?>" style="width:90%" rows="1" wrap="VIRTUAL" <?=$permiso?> id="descripcion" readonly><?=stripcslashes($items[$x]['descripcion'])?></textarea>
				            </div>
				           </td>
				           <td align="center">
				            <input name="precio_<?=$index_r?>" type="text" size="11" <?=$permiso?>  style='text-align:right'  value="<?=(($items[$x]['precio'])!="")?number_format($items[$x]['precio'],2,'.',''):"";?>" readonly onchange="control_numero(this,'Precio para el producto <?=stripcslashes($items[$index_r]['descripcion'])?>')">
				           </td>
				          </tr>
			            <?
			            $index_r++;
	              }//de if ($items[$x]["cantidad"]>0)
	              
            }//de for($x=0;$x<$items['cantidad'];$x++)
	            $items=$index_r;
	        ?>
         
	        </table>
	        <?    
            }//de elseif($id_entrega_estimada)
    	    if($es_pedido_material && permisos_check("inicio","permiso_editar_fila_pedido_material"))
	        {
	        ?>
	        <table>
	         <tr>
              <td align="right" colspan="4">
	        	<input type="submit" name="guardar_cambios" value="Guardar Cambios" class="little_boton" style="visibility='hidden'" onclick="return control_precios()">
	          </td>
	         </tr>
	        </table> 
	        <?
	        }//de if(permisos_check("inicio","permiso_editar_fila_pedido_material"))
	        ?>
	        </table>
	        <table align=center>
	        <tr>
	         <td colspan="5" align="center">
	          <input type="button" name="Agregar" value="Agregar"  <?=$permiso?> <?if($pm_packaging)echo "disabled"?> onclick="nuevo_item()">
	          <input type="button" name="Eliminar" value="Eliminar" <?=$permiso?>
	           onclick=
	           "
	           if (confirm('¿Está seguro que desea eliminar los items seleccionados ?'))
		        borrar_items()
		       "
	          >
	         </td>
	        </tr>
	        
	       <input type="hidden" name="items" value="<?=($items)?$items:0?>">
           </table>
           
<? 
   if ($acumulado_servicio_tecnico || $pm_auditoria_ckd || $pm_producto_sl) {
            if ($id_mov){
              	$sql = "select * from mercaderia_entrante where id_movimiento_material = $id_mov";
            	$mercaderia_entrante = sql($sql) or fin_pagina();
            	$items_mercaderia_entrante = $mercaderia_entrante->recordcount();
            }
            else 
            $items_mercaderia_entrante = 0;  	
?>           
	       <table width="100%">
	         <tr id=mo><td><font size="2">Lista de Mercaderia Entrante</td></td></tr>
    	     <tr>
	             <td> 
 	                 <table width="100%" align="center" id="mercaderia_entrante">
	                 <tr id=ma>
	                 <td width="1%"><input type="checkbox" name="chk_all" onclick="seleccionar_todos(this,document.all.chk_me)"></td>
	                 <td width="5%">Cantidad</td>
	                 <td width="40%">Descripción</td>
	                 <td width="7%">Precio</td>
	                 </tr>
           <?
             $total_mercaderia = 0;
	         for ($i=0;$i<$items_mercaderia_entrante;$i++) {
	         	$id_p_me = $mercaderia_entrante->fields["id_prod_esp"];
	         	$desc_me = $mercaderia_entrante->fields["descripcion"];
	         	$cant_me = $mercaderia_entrante->fields["cantidad"];
	         	$precio_me  = $mercaderia_entrante->fields["precio"];
	        ?>
	        <input type="hidden" name="id_p_me_<?=$i?>" value="<?=$id_p_me?>">
	            <tr>
	               <td align="center"><input type="checkbox" name="chk_me" value=1></td>
	               <td align="center">
	                  <input name="cant_me_<?=$i?>" type="text" size="6" style='text-align:right' value="<?=$cant_me?>">
	               </td>
	               <td align="center">
	                  <textarea style="width:90%;text-align:left" rows="1" wrap="VIRTUAL" name="desc_me_<?=$i?>"><?=$desc_me?></textarea>
	               </td>
	               <td align="center">
                     <input name="precio_me_<?=$i?>" type="text" size="11" style='text-align:right'  value="<?=number_format($precio_me,2,'.','')?>">	                 
	               </td>
	            </tr>
	        <?
              $total_mercaderia+=$precio_me;	        
 	          $mercaderia_entrante->movenext();
	        }//del for
	        
	        if ($pm_auditoria_ckd || $pm_producto_sl) {
			?>
            <tr>
              <td colspan="3" align="right"><font color="Black" size="2"><b>Total:</b></font> </td>
              <td align="center">
              <input readonly name="total" type="text" size="11" style='text-align:right'  value="<?=number_format($total_mercaderia,2,'.','')?>">
              </td>
            </tr>    	                 
            <?
	        }
            ?>
	        </table>
	        </td>    
	        </tr>
            <?
	        $link=encode_link("../productos/listado_productos_especificos.php",array("pagina_viene"=>"detalle_movimiento.php","onclick_cargar"=>"window.opener.nuevo_item_mercaderia()"));
			?>
	        <table align=center>
	        <tr>
	         <td align="center">
	          <input type="button" name="Agregar" value="Agregar" onclick="wmercaderia_entrante=window.open('<?=$link?>')">
	          <input type="button" name="Eliminar" value="Eliminar" 
	           onclick=
	           "
	           if (confirm('¿Está seguro que desea eliminar los items seleccionados ?'))
		       borrar_items_mercaderia_entrante()
		       "
	          >
	         </td>
	        </tr>

	       </table>
           <input type="hidden" name="items_mercaderia_entrante" value="<?=($items_mercaderia_entrante)?$items_mercaderia_entrante:0?>">	       
           
    	   <?if ((permisos_check("inicio","permiso_autorizar_esp"))&&$acumulado_servicio_tecnico){?>
	           <input type="submit" name="Autorizar Especial" value="Autorizar Especial" title="Lista de Mercaderia Entrante se Suma al Stock BS AS" onclick='document.all.guardar.value=1;return control_datos()'> 
	       <?}
	        if ((permisos_check("inicio","permiso_autorizar_esp"))&&$pm_auditoria_ckd){?>
	           <input type="submit" name="Autorizar Especial CKD" value="Autorizar Especial" title="Lista de Mercaderia Entrante se Suma al Stock San Luis" onclick='document.all.guardar.value=1;return control_datos()'> 
	       <?}

   }
?>	       
	       
		<?
	     }//de if($estado!=2)
	     else //el estado es autorizada ($estado==2) o finalizado ($estado==4)
		 {
           //GENERAMOS LA PARTE DE ENTREGA DE PRODUCTOS
           $hay_entregas_hechas=0;
           if (!$acumulado_servicio_tecnico && !$pm_auditoria_ckd && !$pm_producto_sl)
           //if (!$acumulado_servicio_tecnico)
               {
		        generar_form_entrega("entregar_sin_cb",$id_mov,$es_pedido_material,1);
              }
              else {  
              ?>
	          <table width="100%" align="center" id="productos">
	           <tr id=ma>
                  <td width="5%">Cantidad</td>
	              <td width="40%">Descripción</td>
	              <td width="7%">Precio</td>
	           </tr>
            <?  
            $total_mercaderia  = 0;             	
	          for($x=0;$x<$items['cantidad'];$x++){
 	          ?>
	          <tr>
	           <td align="center">
	            <input name="cant_<?=$x?>" type="text" size="6" <?=$permiso?>  style='text-align:right' value="<?=$items[$x]['cantidad']?>" readonly >
	           </td>
	            <td>
	            <div align="center">
	             <textarea name="desc_<?=$x?>" style="width:90%" rows="1" wrap="VIRTUAL" <?=$permiso?> id="descripcion" readonly><?=stripcslashes($items[$x]['descripcion'])?></textarea>
	            </div>
	           </td>
	           <td align="center">
	            <input name="precio_<?=$x?>" type="text" size="11" <?=$permiso?>  style='text-align:right' value="<?=(($items[$x]['precio'])!="")?number_format($items[$x]['precio'],2,'.',''):"";?>" readonly onchange="control_numero(this,'Precio para el producto <?=stripcslashes($items[$x]['descripcion'])?>')">
	           </td>
	          </tr>
	        <?
	        $total_mercaderia += $items[$x]['precio'];
	         }//de for($x=0;$x<$items['cantidad'];$x++)
	         if ($pm_auditoria_ckd || $pm_producto_sl){
	         ?>
            <tr>
              <td colspan="2" align="right"><font color="Black" size="2"><b>Total:</b></font> </td>
              <td align="center">
              <input readonly name="total" type="text" size="11" style='text-align:right'  value="<?=number_format($total_mercaderia,2,'.','')?>">
              </td>
            </tr>
            <?
	         }
            ?>    
	         
	         </table>
	         <? 
		      	//elimino las reservas del deposito origen y inserto en rma la
		      	//lista de mercaderia entrante		      	
              	$sql = "select * from mercaderia_entrante where id_movimiento_material = $id_mov";
            	$mercaderia_entrante = sql($sql) or fin_pagina();
            	$items_mercaderia_entrante = $mercaderia_entrante->recordcount();
                ?>           
	            <table width="100%">
	                <tr id=mo><td><font size="2">Lista de Mercaderia Entrante</td></td></tr>
    	            <tr>
	                 <td> 
 	                    <table width="100%" align="center" id="mercaderia_entrante">
	                       <tr id=ma>
	                          <td width="5%">Cantidad</td>
	                          <td width="40%">Descripción</td>
	                          <td width="7%">Precio</td>
	                        </tr>
                  <?
                  $precio_total = 0;
	              for ($i=0;$i<$items_mercaderia_entrante;$i++){
	         	             $id_p_me = $mercaderia_entrante->fields["id_prod_esp"];
 				         	 $desc_me = $mercaderia_entrante->fields["descripcion"];
				         	 $cant_me = $mercaderia_entrante->fields["cantidad"];
				         	 $precio_me  = $mercaderia_entrante->fields["precio"];
	               ?>
	              <input type="hidden" name="id_p_me_<?=$i?>" value="<?=$id_p_me?>">
	              <tr>
	                  <td align="center">
	                  <input readonly name="cant_me_<?=$i?>" type="text" size="6" style='text-align:right' value="<?=$cant_me?>">
	                  </td>
	                  <td align="center">
	                  <textarea readonly style="width:90%;text-align:left" rows="1" wrap="VIRTUAL" name="desc_me_<?=$i?>"><?=$desc_me?></textarea>
	                  </td>
	                  <td align="center">
                      <input readonly name="precio_me_<?=$i?>" type="text" size="11" style='text-align:right'  value="<?=number_format($precio_me,2,'.','')?>">	                 
	                  </td>
	              </tr>
	        <?
	        $precio_total+=$precio_me;
	        $mercaderia_entrante->movenext();
	    }//del for
	    ?>
	    <tr>
	      <td colspan="2" align="right"><b>Total</b></td>
	      <td align="center">
          <input readonly name="preciototal" type="text" size="11" style='text-align:right'  value="<?=number_format($precio_total,2,'.','')?>">	                 
	      </td>
	    </tr>
      	
         </table>
		  <?
		  }
	      if($es_pedido_material!=1 && $id_stock_rma!=$deposito_destino)//si no es pedido de material y el stock destino no es RMA, mostramos la parte de recepcion para el deposito destino
		  {
		  	//GENERAMOS LA PARTE DE RECEPCION DE PRODUCTOS
			  ?>
			  <hr>
			  <table align="center" width="100%" bordercolor="Black" border="1">
			   <tr>
			    <td colspan="3" align="center">
			     <font size="3"><b>Recepción de Productos</b></font>
			    </td>
			   </tr>
			  <?
			   for($x=0;$x<$items['cantidad'];$x++)
			   {
			     //traemos el log de los recibidos + la cantidad de productos recibidos
			     //y las observaciones
			     $query="select recibidos_mov.cantidad,recibidos_mov.observaciones,log_recibidos_mov.*
						 from mov_material.recibidos_mov
						 join mov_material.log_recibidos_mov using(id_recibidos_mov)
						 where recibidos_mov.id_detalle_movimiento=".$items[$x]['id_detalle_movimiento']." and recibidos_mov.ent_rec=1";
			     $datos_recibidos_mov=sql($query) or fin_pagina();
					?>
			    <tr>
			     <td>
				 <table width="100%" align="center">
		          <tr id=mo_sf>
		           <td>
		            Producto: <?=$items[$x]['descripcion']?>
		           </td>
		           <td width="10%" nowrap>U$S <?=(($items[$x]['precio'])!="")?number_format($items[$x]['precio'],2,'.',''):"";?></td>
		           <td width="13%" nowrap>
		            Enviados <?=$items[$x]['cantidad']?>
		           </td>
		           <td width="13%" nowrap>
		            Recibidos <?=($datos_recibidos_mov->fields['cantidad'])?$datos_recibidos_mov->fields['cantidad']:0?>
		           </td>
		           <?
		           //las siguientes variables se usan para el control de los totales recibidos
		           //un poco mas abajo en el codigo
		           $recibidos=($datos_recibidos_mov->fields['cantidad'])?$datos_recibidos_mov->fields['cantidad']:0;
		           $enviados=$items[$x]['cantidad'];
		           $producto=$items[$x]['descripcion'];

		           $todo_recib="";
		           if($items[$x]['cantidad']<=$datos_recibidos_mov->fields['cantidad'])
		            $todo_recib="readonly";
		           ?>
		          </tr>
		         </table>
		         <table width="100%" align="center">
				  <tr>
				   <td colspan="2">
				   	<table align="center" width="100%" border="1" bordercolor=#E0E0E0 bgcolor="#ACACAC">
				   	 <?
				   	 $observaciones_recib=$datos_recibidos_mov->fields['observaciones'];
				   	 //generamos el log de recibidos para cada producto
				   	 while(!$datos_recibidos_mov->EOF)
				   	 {?>
				   	  <tr>
				   	   <td>
				   	    <font color="Black">
				   	     <b>Cantidad Recibida: <?=$datos_recibidos_mov->fields['cantidad_recibida']?></b>
				   	    </font>
				   	   </td>
				   	   <td>
				   	    <font color="Black">
				   	     <b>Usuario: <?=$datos_recibidos_mov->fields['usuario']?></b>
				   	    </font>
				   	   </td>
				   	   <td align="right">
				   	    <font color="Black">
				   	     <b>Fecha: <?=fecha($datos_recibidos_mov->fields['fecha'])?></b>
				   	    </font>
				   	   </td>
				   	  </tr>
				   	 <?
				   	  $datos_recibidos_mov->MoveNext();
				   	 }//de while(!$datos_recibidos_mov->EOF)
		             ?>
		              </table>
		             </td>
		            </tr>
		            <tr id=ma>
		              <td align="center">
		               Se reciben
		              </td>
		              <td align="center">
		               Observaciones
		              </td>
		            </tr>
		            <tr>
		              <td align="center">
		               <input name="cant_recib_<?=$x?>" <?=$todo_recib?> type="text" size="6" style='text-align:right' value="0"
		                onchange="control_cant_recib(<?=$x?>,<?=$recibidos?>,<?=$enviados?>,'<?=$producto?>',error_cant_recib_<?=$x?>)"
		               >
		               <input type="hidden" name="error_cant_recib_<?=$x?>" value="0">
		              </td>
		              <td align="center">
		               <textarea name="desc_recib_<?=$x?>" <?=$todo_recib?> cols="90" rows="1" wrap="VIRTUAL" id="descripcion"><?=$observaciones_recib?></textarea>
		              </td>
		            </tr>
		           </table>
		           </td>
		          </tr>
			<?
			   }//de for($x=0;$x<$items['cantidad'];$x++)
		  }//de if(!$es_pedido_material==1)
		}//del else de if($estado!=2)
	     ?>
	      </table>
	     </td>
	    </tr>
	   </table>
	  </td>
	 </tr>
	</table>

	<?
	if ($items['cantidad'])
	{
	?>
	 <script>
	/****************************************************
	Para controlar si hubo algun error en las cantidades
	(Se ubica aca porque aprovechamos $items['cantidad'])
	****************************************************/

	function control_recib_pedido()
	{ var campo;
	  var i=0;
	  var hay_error=0;
	  for(i; i < <?=$items['cantidad']?>;i++)
	  { campo=eval("document.all.error_cant_recib_"+i);
	    if(campo.value==1)
		 hay_error=1;
	  }
	  if(hay_error)
	  {alert('Existen productos para los cuales las cantidades recibidas superan las cantidades enviadas.\nDebe revisar las cantidades ingresadas antes de guardar.');
	   return false;
	  }
	  else
	   return true;
	}
	</script>
	<?
	}//de if ($items['cantidad'])
	?>
	<table width="95%" align="center" border="1">
	 <tr>
	  <td width="65%">
	   <table align="center">
	    <tr>
	     <td align="center">
	      <b>Observaciones</b>
	     </td>
	    </tr>
	    <tr>
	     <td align="center">
	      <textarea cols="90" rows="5" name="observaciones"><?=$comentarios?></textarea>
	     </td>
	    </tr>
	   </table>
	  </td>
	  <!--aca empieza el "td" que muetra las ordenes de Produccion-->
	  <?
	  if ($id_mov) {
	  $q= "select id_entrega_estimada from movimiento_material
		  	  where id_movimiento_material=$id_mov ";
	  $result_q=sql($q) or fin_pagina();
	  $id_ent_est=$result_q->fields['id_entrega_estimada'];

	  //hace la consulta solo si es licitacion y tiene un ID_entrega_estimada
	  if (($flag_muestra_seg_prod==1) && ($id_ent_est)){
	  	  $sql_seg_prod="select entrega_estimada.nro, subido_lic_oc.nro_orden as numero
							from licitaciones.entrega_estimada
							LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)
							where id_entrega_estimada = $id_ent_est";
		  $result_seg_prod=sql($sql_seg_prod) or fin_pagina();
		  $cant_reg=$result_seg_prod->RecordCount();
	  }
	  ?>

	  <td width="35%">
	  	<table align="center">

	  	    <?//pongo el tr si existe alguna orden de produccion asociada
	  	    if ($flag_muestra_seg_prod==1) {?>
	  		<tr>
	  			<td>
	  			 <center><b><font size="2" color="Green">Seguimientos de Producción Asociados </font> </b></center>
	  			</td>
	  		</tr>
	  		<?}?>

	  		<tr>
	  		 <td align="center">

	  		  <?//entra si es una licitacion y tiene un seguimiento de produccion asociado
	  		  if (($flag_muestra_seg_prod==1) && ($cant_reg==1)){
          		 if ($result_seg_prod->fields['nro']>0)
          		    $num_seg_prod = $result_seg_prod->fields['nro']." <b>/</b>&nbsp";
                 else $num_seg_prod.= "0 <b>/</b>&nbsp";
                 $num_seg_prod.=$result_seg_prod->fields['numero'];
                 $ref = encode_link("../ordprod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$id_licitacion, "id_entrega_estimada"=>$id_ent_est,"nro"=>$result_seg_prod->fields['nro'],"nro_orden_cliente"=>$result_seg_prod->fields['numero']));
                 ?>
                 <br>
                 <a target=_blank title='Link al Seguimiento de Producción' href='<?=$ref?>'>
                 	<?echo $num_seg_prod . "<br>";?>
                 </a>
                 <b><font size='1' color="Green">Seguimientos de Producción Asociados al PM</font></b>
                 <?
	  		  }
	  		  if (($flag_muestra_seg_prod==1) && ($cant_reg==0)){//va por aca si es licitacion y no tiene seguimiento de produccion asociado al pedido de material
	  		  	$sql="select entrega_estimada.nro, subido_lic_oc.nro_orden as numero,entrega_estimada.id_licitacion, id_entrega_estimada
								from licitaciones.entrega_estimada
								LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)
								where entrega_estimada.id_licitacion = $id_licitacion";
	  		  	$result=sql($sql) or fin_pagina();

				$cant_reg_1=$result->recordCount();
				//entra por el if si tiene registros
				if ($cant_reg_1>0){
					while(!$result->EOF){
						if ($result->fields['nro']>0)
          		    	$num_seg_prod = $result->fields['nro']." <b>/</b>&nbsp";
                 		else $num_seg_prod.= "0 <b>/</b>&nbsp";
                 		$num_seg_prod.=$result->fields['numero'];
                 		$ref = encode_link("../ordprod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$id_licitacion, "id_entrega_estimada"=>$result->fields['id_entrega_estimada'],"nro"=>$result->fields['nro'],"nro_orden_cliente"=>$result->fields['numero']));
                 		?>
                 		<br>
                 		<a target=_blank title='Link al Seguimiento de Producción' href='<?=$ref?>'>
                 		<?echo $num_seg_prod . "<br>";?>
                 		</a>
                 		<?
					$result->MoveNext();
					}
					echo "<b><font size='1' color='Red'>Atencion!!: Son Seguimientos de Producción Asociados a la Licitacion ". $id_licitacion ." NO al PM</font></b>";
				}
				else{
					echo "<b><font size='1' color='Red'>Atencion!!: No Existen Seguimientos de Produccion Asociadas ni al PM ni a la Licitacion</font></b>";
				}
	  		  }
	  		  //entra por aca si es un caso o un presupuesto
	  		  if ($flag_muestra_seg_prod==0) {?>
	  		  	<center><b><font size="2" color="Red">No hay Seguimientos de Producción Asocialdos, no está asociado a Licitación o Presupuesto</font> </b></center>
	  		  <?}?>
	  		 </td>
	  		</tr>
	  	</table>
	  </td>
	  <?}?>
	  <!--aca termina el "td" que muetra las ordenes de Produccion-->
	 </tr>
	</table> <br>
	<input type="hidden" name="guardar" value="0">
	<div align="center">

	 <input type="submit" name="boton_guardar" value="Guardar" <? if ($estado > 2) echo "disabled"; ?>
	  onclick='<?if($estado<2)
	             {?>return control_datos();
	             <?
	             }
	             elseif(!$es_pedido_material)
	             {
	             ?>
	             return control_recib_pedido();
	             <?
	             }
	             else
	             {?>
	              return true;
	             <?
	             }
	             ?>
	          '
	 >
    <?  if ($es_pedido_material && $estado=="")  {   //si es pedido de material y la orden es nueva  ?>
	  <input type="submit" name="boton_guardar_paraautorizar" value="Guardar-Para Autorizar"
	  onclick='document.all.guardar.value=1;return control_datos();'>
	 <?}?>
	 <?
	 //si el estado es pendiente mostramos el boton de
	 if($estado>0 || $estado=="")
	  $disabled_para_autorizar="disabled";
	 ?>
	 <input type="submit" name="para_autorizar" value="Para Autorizar" <?=$disabled_para_autorizar?> onclick='document.all.guardar.value=1;return control_datos()' <?=$permiso?>>
	 <?
	 if(((($estado>=2 || $estado=="") && ($flag)) || $id_mov=="")||$hay_entregas_hechas)
	 {     $disabled_autorizar="disabled";
	 	   $disabled_anular_pm="disabled";
	 }
     elseif($estado>=2)
          $disabled_autorizar="disabled";
	 //permiso boton autorizar
	 if(!permisos_check("inicio","permiso_autorizar_mov"))
	  $permiso_aut="disabled";

	  $link_listado=encode_link("listado_mov_material.php",array('pedido_material'=>$es_pedido_material));
	  ?>
	 <input type="submit" name="Autorizar" value="Autorizar" <?=$disabled_autorizar?> <?=$permiso_aut?> onclick='document.all.guardar.value=1;return control_datos()'>
	 <input type="submit" name="Anular"    value="Anular Movimiento" <?=$disabled_anular_pm?> <?=$permiso_aut?> <?=$permiso?>>
	 
	 <?$disabled_rechazar=($estado==1)?"":"disabled";?>
	 <input type="submit" name="Rechazar"  value="Rechazar" <?=$disabled_rechazar?> title="Vuelve a Pendientes el PM">	 
	 <?
	if ($es_pedido_material==1 && $id_licitacion && permisos_check("inicio","seguimiento_boton_materiales"))
	 {
	 	$link_materiales=encode_link("../ordprod/seguimiento_orden_materiales_pm.php",array("id_licitacion"=>$id_licitacion,"mostrar_pedidos"=>1,"pm"=>$id_mov));
	 	?>
		<input type="button" name="boton_materiales" value="Materiales" onclick="window.open('<?=$link_materiales;?>','','')" style="cursor:hand">&nbsp;&nbsp;&nbsp;
		<?
	 }

	if ($pagina=='stock_rma') {?>
	  <input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
	<?}
	else {?>
	 <input type="button" name="volver" value="Volver" onclick="document.location.href='<?=$link_listado?>'">
     <?}?>
	</div>
<?/*Nro de pedido de material al final de la pagina */
if ($id_mov && $es_pedido_material) {
?>	
<br>
<table align='center' bgcolor="white">
  <tr>
     <td>
      <font size=3 color='blue' ><b>Pedido de Material </font>
            <font size=3 > Nº <?=$id_mov?> </font>
        
 <?if($id_licitacion && $es_presupuesto) {?>       
        
     <font size=3 color='blue'> asociado a Presupuesto</font>
     </td>
     <td title="Estado de la Licitación: <?=$estado_lic_nombre?>">
	   <a href="<?=$link_pres?>" style="font-size='16'; color='black';" target="_blank">
	     <b> <font size=3> Nº <?=$id_licitacion?> </font></b>
	   </a>
	  </font>
<?}
 elseif ($id_licitacion) {?>
 	  <font size=3 color='blue'> asociado a Licitación</font>
	 </td>
	 <td  title="Estado de la Licitación: <?=$estado_lic_nombre?>">
	   <font size=3 color='blue' >
	     <b><a href="<?=$link_lic?>" style="font-size='16'; color='black';" target="_blank"> Nº <?=$id_licitacion?></A>
	   </font>
<? }
  elseif ($nrocaso) {?>
    <font size=3 color='blue'> asociado al Caso</font>
	 </td>
	 <td>
	   <font size=3 color='blue' >
	     <b><a href="<?=$link?>" style="font-size='16'; color='black';" target="_blank"> Nº <?=$nrocaso?></A>
	   </font>
  <?}
  elseif ($acumulado_servicio_tecnico) {?>
   <font size=3 color='blue'> - Acumulado de Servicio Tecnico</font>
	 </td>
	 <td>
	  &nbsp;
  <?}
  elseif ($pm_produccion_sl){?>
   <font size=3 color='blue'> - PM Producción San Luis</font>
	 </td>
	 <td>
	  &nbsp;
   <?} 
   elseif ($pm_producto_sl){?>
   <font size=3 color='blue'> - PM Productos -  San Luis</font>
	 </td>
	 <td>
	  &nbsp;
   <?} 
    elseif ($pm_rma_producto_sl){?>
   <font size=3 color='blue'> - PM RMA - Productos -  San Luis</font>
	 </td>
	 <td>
	  &nbsp;
   <?}
   elseif ($pm_auditoria_ckd){?>
   <font size=3 color='blue'> - PM de auditoria CKD Monitores</font>
	 </td>
	 <td>
	  &nbsp;
   <?}
    ?>
  </td>
 </tr>
</table>
<? }?>
</form>
<?
}//del else de if($modo=="asociar")
echo  fin_pagina();
?>