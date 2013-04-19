<?
/*
Author: GACZ

MODIFICADA POR
$Author: nazabal $
$Revision: 1.41 $
$Date: 2007/05/23 21:18:45 $
*/


require_once("../../config.php");
require_once("fns.php");
require_once("../general/func_seleccionar_cliente.php");

/******PARAMETROS DE ENTRADA DE LA PAGINA***********
@boton indica que tipo de accion se debe hacer
****************************************************/

extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$dif=$parametros['datos'];
//print_r($dif);
//die();

$f1=date("Y/m/j H:i:s");
$nro_factura=str_replace(" ","",$nro_factura);
$iva=str_replace(",",".",$iva);



if ($_POST['cambiar_fecha']=="Guardar"){
	if ($id_factura){
	$sql="SELECT fecha_factura, nro_factura FROM facturas WHERE id_factura=$id_factura";
	$result=sql($sql,"No se pudo ejecutar la consulta");
	
	$fecha_factura_vieja=fecha ($result->fields['fecha_factura']);
	$nro_factura_1=$result->fields['nro_factura'];
	
	$sql="update facturas set fecha_factura = '". Fecha_db($fecha_factura) ."' WHERE id_factura=$id_factura";
	sql($sql,"No se pudo ejecutar la consulta $sql");
	
	$para= "baretto@coradir.com.ar, noelia@coradir.com.ar";
	$asunto="Cambio de Fecha de la Factura Finalizada";
	$usuario_mail=$_ses_user['name'];
	$contenido="El Usuario $usuario_mail cambió la Fecha de la Factura Número: $nro_factura_1, con Fecha Anterior: $fecha_factura_vieja y con Nueva Fecha: $fecha_factura";
	
	enviar_mail($para, $asunto, $contenido,'','','',0);

	$msg="Se Cambio Exitosamente la Fecha de una Factura Finalizada";
	header("location: ".encode_link("factura_listar.php",array("informar"=>$msg)));
	
	}
}

$db->starttrans();
$fecha=date("Y-m-d H:m:s");
$usuario=$_ses_user['id'];

if ($_POST['cambio_entidad']=="si_cambio") {
   actualizar_clientes_mas_usuados($id_entidad,$usuario,$fecha);
}//de que se cambio la entidad


if ($boton=="Guardar" || $guardar > 0 || $boton=="Terminar"){
	if ($id_factura==-1){
		
		
        if($id_entidad=="")
			$id_entidad="null";
		if ($boton=="Terminar") $est='t'; else $est='p';
		
		$campos="id_factura,cliente,direccion,cuit,iib,iva_tipo,iva_tasa,pedido,venta,otros,estado,nro_remito,id_moneda,id_entidad";
		$fecha=date("Y/m/j H:i:s");
		$valores="'$nbre','$dir','$cuit','$iib','$condicion_iva',$iva,'$pedido','$venta','$otros','$est','$nro_remito',$select_moneda,$id_entidad";
		
		if ($select_tipo_factura){
		 $campos.=",tipo_factura";
		 $valores.=",'$select_tipo_factura'";
		}
		if ($nro_factura){
		 $campos.=",nro_factura";
		 $valores.=",'$nro_factura'";
		}
		if ($fecha_factura){
		 $campos.=",fecha_factura";
		 $valores.=",'".Fecha_db($fecha_factura)."'";
		 }
		if ($licitacion && $asociar){
		 $campos.=",id_licitacion";
		 $valores.=",$licitacion";
		}
		if ($cotiz_valor){
			$campos.=",cotizacion_dolar";
			$valores.=",$cotiz_valor";
		}
 	    if ($numeracion){
			$campos.=",id_numeracion_sucursal";
			$valores.=",$numeracion"; 
		}

		// esto solo se usa para controlar el total de la factura con el del renglon
		// y la diferencia
		if ($seg){
			$supera_total=0;
			$items=get_items();
			$precio_fact=0; 
			$cant_items=$items['cantidad'];
			//$id_r=$items['id_renglones_oc'];
		    for ($j=0;$j<$cant_items;$j++){
		    	$precio_fact=$items[$j]['subtotal'];
		    	//if ($dif[$j]=="" || $dif[$j]>=$items[$j]['subtotal']) $supera_total=0;
		    	$id_r=$items[$j]['id_renglones_oc']; 
		    	if ($dif[$id_r]>=$precio_fact && $supera_total!=1)
		    	     $supera_total=0;
		    	else $supera_total=1;
		    } // esta llave no va cuando se descomentan los echo
			$sql="SELECT subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion as licitacion,subido_lic_oc.vence_oc as vencimiento,subido_lic_oc.nro_orden as oc,entidad.nombre as cliente,entrega_estimada.nro as numero FROM subido_lic_oc ";
			$sql.="LEFT JOIN licitacion USING (id_licitacion) LEFT JOIN entidad USING (id_entidad) ";
			$sql.="LEFT JOIN entrega_estimada USING (id_entrega_estimada) WHERE subido_lic_oc.id_entrega_estimada=$seg";
			$result=sql($sql) or fin_pagina();;
			$fila=$result->fetchrow();
		}

		//recupero el id que se le asignara a la factura


		
		$q="select id_factura from facturas where nro_factura='$nro_factura' 
		                                     and  tipo_factura='$select_tipo_factura' 
		                                     and id_numeracion_sucursal=$numeracion";
		$res=sql($q) or fin_pagina();
		
		
		if ($res->recordcount()<=0) {

			$q="select nextval('facturas_id_factura_seq') as id_factura";
		    $id_factura=sql($q) or fin_pagina();;
		    $id_factura=$id_factura->fields['id_factura'];
		    $valores="$id_factura,$valores";
  		    $q="insert into facturas ($campos) values ($valores)";
		    sql($q) or fin_pagina();
		    $q="insert into log (id_factura,tipo_log,usuario,fecha) values ($id_factura,'creacion','".$_ses_user['name']."','$f1')";
		    sql($q) or fin_pagina();;
		}
		else{ 
		    $msg="NO SE PUDO GUARDAR LA FACTURA, YA EXISTE EL NUMERO \"$nro_factura\" para el Tipo ".strtoupper($select_tipo_factura);
			if ($seg){
				$exito=0;
				$fila["msg"]=$msg;	
				$fila["exito"]=$exito;	
	            $fila["pagina_volver"]="entregas.php";
				$link4=encode_link("../ordprod/seleccionar_renglon_adj.php",$fila);
		        echo "<html><head><script language=javascript>"; 
	            echo "window.opener.location.href='$link4';";
	            echo "window.close();";
	            echo "</script></head></html>"; 
			    }
			    else
				  header("location: ".encode_link("factura_nueva.php",array("msg"=>$msg)));
			
			die;
		}

	}
	else
	{
		if($id_entidad=="")
         $id_entidad="null";

		$q="update facturas set ".
		   "cliente='$nbre',direccion='$dir',cuit='$cuit',iib='$iib',iva_tipo='$condicion_iva',".
		   "iva_tasa=$iva,pedido='$pedido',venta='$venta',otros='$otros',nro_remito='$nro_remito',".
		   "nro_factura='$nro_factura',id_entidad=$id_entidad,id_numeracion_sucursal=$numeracion";
		

		if ($licitacion && $asociar)
 			$q.=",id_licitacion=$licitacion ";
 		    else
 			$q.=",id_licitacion=null ";

		if ($boton=="Terminar"){
		   $q.=",estado='t'";
	       $tipo_log="finalizacion";
		}
		else {
			$tipo_log="modificacion";
		}

		if ($select_moneda && $select_moneda!=-1)
			$q.=",id_moneda=$select_moneda";
		if ($select_tipo_factura && $select_tipo_factura!=-1)
		 $q.=",tipo_factura='$select_tipo_factura'";
		if ($fecha_factura)
		 $q.=",fecha_factura='".Fecha_db($fecha_factura)."'";
		if ($cotiz_valor)
 		 $q.=",cotizacion_dolar=$cotiz_valor";
 		 else
 		 $q.=",cotizacion_dolar=0";

		$q.=" where id_factura=$id_factura";
		sql($q) or fin_pagina();;

		$q="insert into log (id_factura,tipo_log,usuario,fecha) values ($id_factura,'$tipo_log','$_ses_user_name','$f1')";
		sql($q) or fin_pagina();

	}
	//organiza los datos provenientes de la pagina anterior
	$items=get_items();

	//elimina los items que no estan en items
	del_items($items,$id_factura);

	//eliminar los campos inecesarios para poder usar la funcion replace
	prepare_factura($id_factura,$items);
	
	//la funcion hace su trabajo
	if (replace("items_factura",$items,array("id_item")) == 0){
		if ($boton=="Terminar"){
			//se pasa automaticamente la factura a seguimiento de cobros
			if($licitacion){
		 	 //traemos la entidad de la licitacion para insertar en cobranzas
			 $query="select id_entidad from licitacion where id_licitacion=$licitacion";
			 $result_entidad=sql($query) or fin_pagina();;
			 $id_entidad=$result_entidad->fields['id_entidad'];
			}
			else{
			$licitacion="null";
			 $id_entidad="null";
			}
			
			if ($numeracion){
			    $sql=" select numeracion from numeracion_sucursal 
			           where id_numeracion_sucursal=$numeracion";
			    $res=sql($sql) or fin_pagina();
			    $numeracion_sucursal=$res->fields["numeracion"];
			    $nro_factura=$numeracion_sucursal."-".$nro_factura;	
			}
		
			$sql = "INSERT INTO cobranzas (id_licitacion,nro_factura,monto,monto_original,";
            $sql .= "fecha_factura,id_moneda,id_entidad,estado,nro_remitos,id_factura) ";
		    $sql .= "VALUES ($licitacion,'$nro_factura',$total,$total,'".fecha_db($fecha_factura)."',$select_moneda,$id_entidad,'PENDIENTE','$nro_remito',$id_factura)";
			sql($sql) or fin_pagina();
			
		
		}
		$msg2=($nro_factura)?" Nº $nro_factura":"";
		$msg="Su Factura$msg2 se guardo exitosamente";
		if ($seg) $exito=1;	
	}
	else {
		$msg="NO SE PUDO GUARDAR LA FACTURA";	
		if ($seg) $exito=0;
	}
}
//anula la factura, pregunto por anular_aux que es un hidden que setea la pagina comentario_anular
elseif ($boton=="Anular" || $anular_aux=="true"){
	$q="update facturas set estado='a' where id_factura=$id_factura";	
	$msg2=($nro_factura)?" Nº $nro_factura":"";
	if	(sql($q)){
		$msg="Su Factura$msg2 se guardo exitosamente";	
		if ($seg) $exito=2;
		$q="insert into log (id_factura,tipo_log,usuario,fecha,otros) values ($id_factura,'anulacion','$_ses_user_name','$f1','$comentario_anular')";
		sql($q) or fin_pagina();;
	}
}
/////////esto ahy que bortrar luego		
    if ($guarda_cliente=="Modifica cliente"){
    	$diego="update facturas set ".
		"cliente='$nbre',id_entidad=$id_entidad,direccion='$dir',cuit='$cuit',iib='$iib',iva_tipo='$condicion_iva',".
		"iva_tasa=$iva,otros='$otros' where id_factura=$id_factura";
		$resul=sql($diego) or fin_pagina($diego);
		$msg="El cambio se realizo con exito";	
       		       }	 
 ////////////////////////////////////////      		       
$db->completetrans();
if ($seg) { 
	$sql="SELECT subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion as licitacion,subido_lic_oc.vence_oc as vencimiento,subido_lic_oc.nro_orden as oc,entidad.nombre as cliente,entrega_estimada.nro as numero FROM subido_lic_oc ";
	$sql.="LEFT JOIN licitacion USING (id_licitacion) LEFT JOIN entidad USING (id_entidad) ";
	$sql.="LEFT JOIN entrega_estimada USING (id_entrega_estimada) WHERE subido_lic_oc.id_entrega_estimada=$seg";
	$result=sql($sql) or fin_pagina();;
	$fila=$result->fetchrow();
	$fila["pagina_volver"]="entregas.php";
	$fila["msg"]=$msg;
	$fila["exito"]=$exito;
	if ($exito==2) $fila["renglones"]=$_POST['id_renglon_oc'];
	else $fila["renglones"]=$_POST['renglones_oc'];
	$fila['factura']=$id_factura;
	//$fila["id_entrega_estimada"]=$seg;
	$link4=encode_link("../ordprod/seleccionar_renglon_adj.php",$fila);
	echo "<html><head><script language=javascript>"; 
    echo "window.opener.location.href='$link4';";
    echo "window.close();";
    echo "</script></head></html>"; 
}
else header("location: ".encode_link("factura_listar.php",array("informar"=>$msg)));
?>