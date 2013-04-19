<?
/*
Creado por: Quique

Modificada por
$Author: nazabal $
$Revision: 1.59 $
$Date: 2007/04/20 19:24:36 $
*/


require_once("../../config.php");
require_once("funciones.php");

$id_proveedor=$parametros['id_proveedor'] or $id_proveedor=$_POST['id_proveedor'];
//$id_proveedor=$parametros['id_proveedor'] or $id_proveedor=$_POST['id_proveedor'];
$pagina=$parametros['pagina'] or $pagina=$_POST['pagina'];
$id_prod_esp=$parametros['id_producto'] or $id_prod_esp=$_POST['id_producto'];
$id_deposito=$parametros['id_deposito'] or $id_deposito=$_POST['id_deposito'];
$id_info_rma=$parametros['id_info_rma'] or $id_info_rma=$_POST['id_info_rma'];
$cant_cb=$parametros['cant_cb'] or $cant_cb=$_POST['cant_cb'];
$bus_barra=0;
$onclick_cargar="window.opener.document.all.descri_prod.value=document.all.nombre_producto_elegido.value;
                 window.opener.document.all.marca.value=document.all.marca_producto_elegido.value;
                 window.opener.document.all.modelo.value=document.all.modelo_producto_elegido.value;
                 window.opener.document.all.tipo.value=document.all.tipo_producto_elegido.value;
                 window.opener.document.all.prod_nuevo.value=document.all.id_producto_seleccionado.value;
                 window.close();";


if(permisos_check("inicio","permiso_boton_recibir"))
  $permiso_recibir=1;
 else $permiso_recibir=0;

////////////////////////////////////Guardar Archivo///////////////////////////////////////////
if ($parametros["download"])
{
	$sql = "select * from archivo_rma where id_archivo_rma = ".$parametros["FileID"];
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");


	if ($parametros["comp"]) {
		$FileName = $result->fields["nombre_archivo_comp"];
		$FileNameFull = UPLOADS_DIR."/stock/RMA/$FileName";
		$FileType="application/zip";
		$FileSize = $result->fields["filesize_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["nombre_archivo"];
		$FileNameFull = UPLOADS_DIR."/stock/RMA/$FileName";
		$FileType = $result->fields["filetype"];
		$FileSize = $result->fields["filesize"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}
}//de if ($parametros["download"])

///////////////////////////////Fin Guardar Archivo///////////////////////////////////////////

/////////////////////////////// Guardar /////////////////////////////////////////////////////
if($_POST['guardar']=="Guardar")
{
 $db->StartTrans();
 $id_dep=$_POST['id_deposito'];
 $id_prod=$_POST['prod_nuevo'];

 $id_prod_ant=$_POST['prod_anterior'];

 $id_prov=$_POST['id_prov_nuevo'];
 $des_cod=$_POST['desc_cod'];
 $cod=$_POST['codigo'];
 $ubi_par=$_POST['ubicacion_parte'];
 $idestado=$_POST['idestado'];
 $id_info_rma=$_POST['id_info_rma'];
 $cantidad=$_POST['cantidad_1'];
 $nro_orden=$_POST['oc'];
 $nuevo_coment=$_POST['nuevo_coment'];
 $caso=$_POST['caso'];
 $defecto=$_POST['defecto'];
 $ubicacion=$_POST["ubicacion"];
 $cont=0;
 $cont1=0;
 while($cont<$cantidad)
 {
  if($_POST["codigo_$cont"]!="")
  {
  $cod[$cont1]=$_POST["codigo_$cont"];
  $cont1++;
  }
  $cont++;
 }

 incrementar_stock_rma($id_prod,$cantidad,$id_prod_ant,"Incremento manual de stock de RMA",$ubi_par,$cod,$des_cod,$id_prov,$id_info_rma,$nro_orden,$nuevo_coment,$id_nota_credito,$ubicacion,$caso,$defecto);

 if($id_info_rma=="")
 {

 $exito="Se guardaron los datos con éxito";
 $ref1 = encode_link("listar_rma.php",array("exito"=>$exito));
 ?>
 <script>
 document.location='<?=$ref1?>';
 </script>
 <?
 }
 else
  Aviso ("Se guardaron los datos con éxito");

     $id_proveedor=$id_prov;
     $id_prod_esp=$id_prod;
     $id_deposito=$id_dep;
     $id_info_rma=$id_info_rma;
     $id_control_stock=$id_control_stock_nuevo;
     $nuevo_coment="";
  $db->CompleteTrans();
}
///////////////////////////////Fin Guardar///////////////////////////////////////////


if($_POST["guardar_comentario"]=="Guardar Comentario")
{
	$db->StartTrans();

	$nuevo_coment=$_POST['nuevo_coment'];
	$usuario=$_ses_user["name"];
	$fecha=date("Y-m-d H:i:s");

	if($nuevo_coment!="")
	{
		$campos= "id_info_rma,usuario,fecha,texto";
		$values= "$id_info_rma,'$usuario','$fecha','$nuevo_coment'";
		$sql=" insert into stock.comentario_rma ($campos) values ($values)";
		sql($sql,"<br>Error al insertar en la tabla del comentario(agregar_stock)<br>") or fin_pagina();
	}

	$nuevo_coment="";

	echo "<center><h5>El nuevo comentario se guardó con éxito</h5</center>";

	$db->CompleteTrans();
}//de if($_POST["guardar_comentario"]=="Guardar Comentario")


/////////////////////////////// Atomizar /////////////////////////////////////////////////////
if($_POST['atomizar']=="Atomizar")
{
 $db->StartTrans();
 $id_dep=$_POST['id_deposito'];
 $social_n=$_POST['proveedor'];
 $descri_prod1=$_POST['descri_prod'];
 $estado_actual=$_POST['est_act'];
 $id_prod=$_POST['prod_nuevo'];
 $id_prod_ant=$_POST['prod_anterior'];
 $id_prov=$_POST['id_prov_nuevo'];
 $des_cod=$_POST['desc_cod'];
 $cod=$_POST['codigo'];
 $ubi_par=$_POST['ubicacion_parte'];
 $id_info_rma=$_POST['id_info_rma'];
 $cantidad=$_POST['cantidad_1'];
 $nro_orden=$_POST['oc'];
 $nuevo_coment=$_POST['nuevo_coment'];
 $caso=$_POST['caso'];
 $defecto=$_POST['defecto'];
 $ubicacion=$_POST["ubicacion"];
 $fecha_base=$_POST['fecha_base'];
 $fecha_crea=$_POST['fecha_crea'];

 $cont=0;
 $cont1=0;
 ////////////////////////Arreglo para Archivos///////////////////////////////
 $sel_archiv="select * from stock.archivo_rma where id_info_rma=$id_info_rma";
 $sel_arc=sql($sel_archiv,"Error al traer los archivos para el Rma")or fin_pagina();
 $c_ar=-1;
 while (!$sel_arc->EOF) {
 	$c_ar++;
 	$non_ar[$c_ar]=$sel_arc->fields['nombre_archivo'];
 	$fec_sub[$c_ar]=$sel_arc->fields['fecha_subido'];
 	$tip_ar[$c_ar]=$sel_arc->fields['tipo'];
 	$usu_ar[$c_ar]=$sel_arc->fields['usuario'];
 	$prod_ar[$c_ar]=$sel_arc->fields['id_prod_esp'];
 	$prov_ar[$c_ar]=$sel_arc->fields['id_proveedor'];
 	$dep_ar[$c_ar]=$sel_arc->fields['id_deposito'];
 	$sel_arc->MoveNext();

 }
////////////////////////Fin Arreglo para Archivos///////////////////////////////
////////////////////////Arreglo para comentarios///////////////////////////////
 $sel_archiv="select * from stock.comentario_rma where id_info_rma=$id_info_rma";
 $sel_arc=sql($sel_archiv,"Error al traer los comentarios para el Rma")or fin_pagina();
 $c_com=-1;
 while (!$sel_arc->EOF) {
 	$c_com++;
 	$text_com[$c_com]=$sel_arc->fields['texto'];
 	$fec_com[$c_com]=$sel_arc->fields['fecha'];
 	$usu_com[$c_com]=$sel_arc->fields['usuario'];
 	$prod_com[$c_com]=$sel_arc->fields['id_prod_esp'];
 	$prov_com[$c_com]=$sel_arc->fields['id_proveedor'];
 	$dep_com[$c_com]=$sel_arc->fields['id_deposito'];
 	$sel_arc->MoveNext();

 }
////////////////////////Fin Arreglo para comentarios///////////////////////////////
////////////////////////Arreglo para log info rma//////////////////////////////////
 $sel_archiv="select * from stock.log_info_rma where id_info_rma=$id_info_rma";
 $sel_arc=sql($sel_archiv,"Error al traer los archivos para el Rma")or fin_pagina();
 $c_log=-1;
 while (!$sel_arc->EOF) {
 	$c_log++;
 	$com_log[$c_log]=$sel_arc->fields['comentario'];
 	$fec_log[$c_log]=$sel_arc->fields['fecha'];
 	$tip_log[$c_log]=$sel_arc->fields['tipo_log'];
 	$usu_log[$c_log]=$sel_arc->fields['usuario_log'];
 	$est_log[$c_log]=$sel_arc->fields['id_estado_rma'];
 	$sel_arc->MoveNext();

 }
////////////////////////Fin Arreglo para log info rma///////////////////////////////
////////////////////////Arreglo para remitos rma///////////////////////////////
$sel_archiv="select * from stock.remito_rma where id_info_rma=$id_info_rma";
$sel_arc=sql($sel_archiv,"Error al traer los archivos para el Rma")or fin_pagina();
$c_rem=-1;
while (!$sel_arc->EOF) {
	$c_rem++;
 	$num_rem[$c_rem]=$sel_arc->fields['nro_remito'];
 	$sel_arc->MoveNext();

}
////////////////////////Fin Arreglo para remito rma///////////////////////////////
 $ajuste_por_atomizar=0;
 while($cont<$cantidad)
 {
  if($_POST["codigo_$cont"]!="")
  {
   $cod[0]=$_POST["codigo_$cont"];
  }
  else
  {
  	$cod="";
  }
  if($cont==0)
  {
  	incrementar_stock_rma($id_prod,1,$id_prod_ant,"Atomización de RMA Nº $id_info_rma",$ubi_par,$cod,$des_cod,$id_prov,$id_info_rma,$nro_orden,$nuevo_coment,$id_nota_credito,$ubicacion,$caso,$defecto);
    $sql="update info_rma set fecha_hist='$fecha_base' where id_info_rma=$id_info_rma";
    sql($sql,"No se pudo actualizar info_rma") or fin_pagina();
  }
  else
  {
  	$ajuste_por_atomizar++;
  	$id_info_rma1="";
  	incrementar_stock_rma($id_prod,1,$id_prod_ant,"Atomización de RMA Nº $id_info_rma",$ubi_par,$cod,$des_cod,$id_prov,$id_info_rma1,$nro_orden,$nuevo_coment,$id_nota_credito,$ubicacion,$caso,$defecto);
  	$se_id_in_r="select id_info_rma from stock.info_rma order by (id_info_rma)DESC";
  	$inf_rma=sql($se_id_in_r,"No se puede seleccionar el id_info_rma") or fin_pagina();
  	$id_inf=$inf_rma->fields['id_info_rma'];
  	$a[$cont]=$id_inf;
  	$sql="update info_rma set fecha_hist='$fecha_base',fecha_creacion='$fecha_crea' where id_info_rma=$id_inf";
    sql($sql,"No se pudo actualizar info_rma") or fin_pagina();
  	$var_con=0;
  	while($c_ar>=$var_con)
  	{
  	$nom_arc=$non_ar[$var_con];
 	$fec_arc=$fec_sub[$var_con];
 	$tip_arc=$tip_ar[$var_con];
 	$usu_arc=$usu_ar[$var_con];
 	$prod_arc=$prod_ar[$var_con];
 	$prov_arc=$prov_ar[$var_con];
 	$dep_arc=$dep_ar[$var_con];
 	$var_con++;
 	$campos="nombre_archivo,fecha_subido,tipo,usuario,id_info_rma,id_prod_esp,id_proveedor,id_deposito";
 	$valores="'$nom_arc','$fec_arc','$tip_arc','$usu_arc',$id_inf,$prod_arc,$prov_arc,$dep_arc";
  	$ins_ar="insert into archivo_rma ($campos) values ($valores)";
  	sql($ins_ar,"No se pudo guardar los archivos en el nuevo rma") or fin_pagina();
  	}

  	$var_con=0;
  	while($c_com>=$var_con)
  	{
  	$nom_arc=$text_com[$var_con];
 	$fec_arc=$fec_com[$var_con];
 	$usu_arc=$usu_com[$var_con];
 	$prod_arc=$prod_com[$var_con];
 	$prov_arc=$prov_com[$var_con];
 	$dep_arc=$dep_com[$var_con];
 	$var_con++;
 	$campos="texto,fecha,usuario,id_info_rma";
 	$valores="'$nom_arc','$fec_arc','$usu_arc',$id_inf";
  	$ins_ar="insert into comentario_rma ($campos) values ($valores)";
  	sql($ins_ar,"No se pudo guardar los comentarios en el nuevo rma") or fin_pagina();
  	}

  	$var_con=0;
  	while($c_log>=$var_con)
  	{
  	$nom_arc=$com_log[$var_con];
 	$fec_arc=$fec_log[$var_con];
 	$usu_arc=$usu_log[$var_con];
 	$tip_log1=$tip_log[$var_con];
 	$est_log1=$est_log[$var_con];
 	$var_con++;
 	$campos="comentario,usuario_log,tipo_log,id_estado_rma,id_info_rma,fecha";
 	$valores="'$nom_arc','$usu_arc','$tip_log1',$est_log1,$id_inf,'$fec_arc'";
  	$ins_ar="insert into log_info_rma ($campos) values ($valores)";
  	sql($ins_ar,"No se pudo guardar los log en el nuevo rma") or fin_pagina();
  	}

  	$var_con=0;
  	while($c_rem>=$var_con)
  	{
  	$num_rem1=$num_rem[$var_con];
 	$var_con++;
 	$campos="nro_remito,id_info_rma";
 	$valores="$num_rem1,$id_inf";
  	$ins_ar="insert into remito_rma ($campos) values ($valores)";
  	sql($ins_ar,"No se pudo guardar los remitos en el nuevo rma") or fin_pagina();
  	}

  }//del else de if($cont==0)
  $cont++;
 }//de while($cont<$cantidad)

 if($ajuste_por_atomizar>0)
 {
 	 //traemos el id de deposito de RMA
 	 $query="select depositos.id_deposito from general.depositos where nombre='RMA'";
 	 $dep_rma=sql($query,"<br>Error al traer el id del deposito RMA<br>") or fin_pagina();
 	 $id_dep_rma=$dep_rma->fields["id_deposito"];

 	 //traemos el id del movimiento de stock: Descuento de RMA por ajuste de cantidades al atomizar
 	 $query="select tipo_movimiento.id_tipo_movimiento from stock.tipo_movimiento where nombre='Descuento de RMA por ajuste de cantidades al atomizar'";
 	 $id_mov_ajust=sql($query,"<br>Error al traer el tipo de movimiento para ajustar cantidades de RMA<br>") or fin_pagina();
 	 if($id_mov_ajust->fields["id_tipo_movimiento"])
 	 	$id_tipo_movimiento=$id_mov_ajust->fields["id_tipo_movimiento"];
 	 else
 	 	die("Error Interno AJR313: No se pudo determinar el tipo de movimiento de stock. Contactese con la División Software");

	 //Ajustamos la cantidad de la tabla stock y el log de movimientos de stock, ya que sino nunca se
	 //descuentan de dichas tablas la cantidad que se resta del rma original. Es decir, si estamos atomizando
	 //un rma de 20 productos, se generan 19 nuevos de cantidad 1, y el original de cantidad 20 pasa a tener cantidad 1.
	 //Esa diferencia no se resta nunca de en_stock ni de log_movmientos_stock, si no hacemos este ajuste
	 $comentario_ajust="Se atomizó el RMA Nº $id_info_rma. Se ajustaron las cantidades para que el registro de movimientos de stock esté correcto";
	 descontar_stock_disponible($id_prod,$ajuste_por_atomizar,$id_dep_rma,$id_tipo_movimiento,$comentario_ajust);
 }//de if($ajuste_por_atomizar>0)

 if($id_info_rma=="")
 {

 $exito="Se guardaron los datos con éxito";
 $ref1 = encode_link("listar_rma.php",array("exito"=>$exito));
 ?>
 <script>
 document.location='<?=$ref1?>';
 </script>
 <?
 }
 else
  Aviso ("Se guardaron los datos con éxito");

     $id_proveedor=$id_prov;
     $id_prod_esp=$id_prod;
     $id_deposito=$id_dep;
     $id_info_rma=$id_info_rma;
     $id_control_stock=$id_control_stock_nuevo;
     $nuevo_coment="";
  $db->CompleteTrans();
  $sel_mail="select mail from sistema.mail_botones left join sistema.mail_usuarios using(id_mail_botones) left join sistema.usuarios using(id_usuario) where mail_botones.nombre='atomizar'";
  $resul_lider=sql($sel_mail,"no se pudo recuperar los mail") or fin_pagina();
  $i=0;
  while (!$resul_lider->EOF)
     {
    	$mail[$i]=$resul_lider->fields['mail'];
    	$i++;
    	$resul_lider->MoveNext();
     }
    $fechas1=date("d/m/Y");
    $para=elimina_repetidos($mail,0);
    $asunto="Se atomizó el RMA Nº ".$id_info_rma."";
    $mensaje="Se atomizó el RMA Nº ".$id_info_rma."";
    $mensaje.="\n--------------------------Breve Descripción del RMA--------------------------";
    $mensaje.="\nDescripción del producto:        ".$descri_prod1;
    $mensaje.="\nEl RMA fue dividido en:        ".$cantidad;
    $mensaje.="\nEn los  RMA";
    $mensaje.="\nN°:        ".$id_info_rma;
    $i=1;
    while($i<$cont)
    {
     $mensaje.="\nN°:        ".$a[$i];
     $i++;
    }
    $mensaje.="\nProveedor:               ".$social_n;
    $mensaje.="\nEstado Actual:               ".$estado_actual;
    $mensaje.="\n---------------------------------------------------------------------------------------";
    $mensaje.="\nEl cambio se realizo el día $fechas1, por el Usuario ".$_ses_user['name'];
    //echo"$para <br>$mensaje<br>";
    enviar_mail($para,$asunto,$mensaje,"","","",0);
}
///////////////////////////////Fin Atomizar///////////////////////////////////////////

///////////////////////////////Pasar a Monitor RMA///////////////////////////////////
if($_POST["pasar_a_monitor_rma"]=="Pasar a Monitor RMA")
{

	$db->StartTrans();

	//traigo datos del RMA antes de que elimine el RMA
	$q="select info_rma.cantidad, en_stock.id_prod_esp,estado_rma.nombre_corto,estado_rma.lugar,
          en_stock.id_deposito,en_stock.ubicacion,id_nota_credito,id_estado_rma,
          producto_especifico.descripcion,producto_especifico.marca,producto_especifico.modelo,
          producto_especifico.precio_stock,info_rma.id_info_rma,info_rma.id_proveedor,info_rma.nro_orden
          ,proveedor.razon_social,producto_especifico.id_tipo_prod,tipos_prod.codigo,info_rma.void,info_rma.desc_void,info_rma.nrocaso,info_rma.defecto_parte,fecha_hist,fecha_creacion,ubicacion_rma.id_ubicacion_rma
    from stock.en_stock
    join general.producto_especifico using(id_prod_esp)
    join general.depositos using (id_deposito)
    join stock.info_rma using (id_en_stock)
    join general.tipos_prod using(id_tipo_prod)
    left join general.proveedor using (id_proveedor)
    left join stock.estado_rma using (id_estado_rma)
    left join stock.ubicacion_rma using (id_ubicacion_rma)
    where id_info_rma=$id_info_rma";
	$result_precio_stock=sql($q,'no se puede traer el precio stock') or fin_pagina();
	//recupero el recorset del PM
	$sql_2="select tipo_log from stock.log_info_rma where (id_info_rma=$id_info_rma) and (tipo_log like '%Creacion PM%')";
 	$res_pm=sql($sql_2) or fin_pagina($sql_2);
 	//recupero la fecha de creacion del rma
	$sql_3="select usuario_log, fecha from stock.log_info_rma where (id_info_rma=$id_info_rma) and (tipo_log like '%Creacion%')";
 	$res_3=sql($sql_3) or fin_pagina($sql_3);
 	$fecha_creacion_rma=$res_3->fields['fecha'];
 	$fecha_creacion_rma=fecha($fecha_creacion_rma);
 	$usu_rma=$res_3->fields['usuario_log'];
	//elimino el rma
	$justificacion='Se Paso a Monitores RMA';
	borrar_rma_del_sistema($id_info_rma,$justificacion);
	//inserto el monitor RMA
	$marca=$_POST['marca'];
  $modelo=$_POST['modelo'];
  $numcaso=$result_precio_stock->fields['nrocaso'];
  if ($numcaso!='') $nro_serie="RMA N $id_info_rma Caso: $numcaso";
  else $nro_serie="RMA N $id_info_rma No tiene caso Asociado";
  $id_estado_muleto=4;
  $precio_stock_mul=$result_precio_stock->fields['precio_stock'];
  if ($precio_stock_mul=='') $precio_stock_mul=0;
  $fecha=date("Y-m-d H:i:s");
  //armo el comentario para insertar
  $comentario_mon_rma="Desde RMA N $id_info_rma";
  $numcaso=$result_precio_stock->fields['nrocaso'];
  if ($numcaso=='') $comentario_mon_rma.=" ,No Caso Asociado";
  else $comentario_mon_rma.=" ,asociado el caso $numcaso";
  $raz_social=$result_precio_stock->fields['razon_social'];
  $comentario_mon_rma.=" ,el Proveedor es $raz_social";
 	$res_pm_insert=$res_pm->fields['tipo_log'];
  if ($res_pm_insert!='')$comentario_mon_rma.=", $res_pm_insert";
  else $comentario_mon_rma.=", no Asociado un PM";
  //fin de armar comentario
	$q="select nextval('muletos_id_muleto_seq') as id_muleto";
    $id_muleto=sql($q) or fin_pagina();
    $id_muleto=$id_muleto->fields['id_muleto'];
    $query="insert into casos.muletos
             (id_muleto, observaciones, marca, modelo, nro_serie, id_estado_muleto, flag_prueba_vida, idcaso,precio_stock,fecha_llegada_estado)
             values
             ($id_muleto, '$comentario_mon_rma', '$marca', '$modelo', '$nro_serie', $id_estado_muleto,0, NULL,'$precio_stock_mul','$fecha')";
    sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    //cargo los log
    $usuario=$_ses_user['name'];
    $log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso)
			values ($id_muleto, '$usu_rma', '$fecha','Creacion RMA: $fecha_creacion_rma',NULL,NULL)";
	sql($log) or fin_pagina();
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso)
			values ($id_muleto, '$usuario', '$fecha','Desde RMA N $id_info_rma',NULL,NULL)";
	sql($log) or fin_pagina();
	//fin de insertar monitor RMA

	$db->CompleteTrans();

	//Enviamos el mail correspondiente para avisar esto
  	$fechas1=date("d/m/Y H:i:s");
  	$descri_prod1=$_POST['descri_prod'];
    $cantidad=$_POST['cantidad_1'];
	$para="juanmanuel@coradir.com.ar,ferni@coradir.com.ar";
    $asunto="Se eliminó del sistema el RMA Nº $id_info_rma por que paso a Monitores RMA con ID $id_muleto";
    $mensaje="$asunto\n";
    $mensaje.="\nProducto: $descri_prod1\n";
    $mensaje.="Cantidad: $cantidad\n";
    $mensaje.="El RMA fue eliminado por ".$_ses_user["name"]." con la siguiente justificación: \n   $justificacion\n";
    $mensaje.="\nFecha de eliminación: $fechas1\n\n";
    //echo "$para <br>$mensaje<br>";die;
    enviar_mail($para,$asunto,$mensaje,"","","",0);

	$exito="El RMA Nº $id_info_rma se paso a Monitores RMA con ID $id_muleto exitosamente";
 	$ref1 = encode_link("listar_rma.php",array("exito"=>$exito));
	header("location: $ref1");
}
////////////////////////////Fin de Pasar a Monitor RMA///////////////////////////////

/////////////////////////////// Eliminar RMA ////////////////////////////////////////////////
if($_POST["h_eliminar"]=="EliminarRMA")
{
	$db->StartTrans();

	$justificacion=$_POST["comentario_eliminar"];
	borrar_rma_del_sistema($id_info_rma,$justificacion);

	$db->CompleteTrans();

	//Enviamos el mail correspondiente para avisar esto
  	$fechas1=date("d/m/Y H:i:s");
  	$descri_prod1=$_POST['descri_prod'];
    $cantidad=$_POST['cantidad_1'];
	$para="juanmanuel@coradir.com.ar,marco@coradir.com.ar";
    $asunto="Se eliminó del sistema el RMA Nº $id_info_rma";
    $mensaje="$asunto\n";
    $mensaje.="\nProducto: $descri_prod1\n";
    $mensaje.="Cantidad: $cantidad\n";
    $mensaje.="El RMA fue eliminado por ".$_ses_user["name"]." con la siguiente justificación: \n   $justificacion\n";
    $mensaje.="\nFecha de eliminación: $fechas1\n\n";
    //echo "$para <br>$mensaje<br>";die;
    enviar_mail($para,$asunto,$mensaje,"","","",0);

	$exito="El RMA Nº $id_info_rma se eliminó del sistema con éxito";
 	$ref1 = encode_link("listar_rma.php",array("exito"=>$exito));
	header("location: $ref1");
}//de if($_POST["h_eliminar"]=="EliminarRMA")
/////////////////////////////// Fin Eliminar RMA ////////////////////////////////////////////////

if ($_POST["barra"])
{
  $nuevo_coment=$_POST["nuevo_coment"];
  $prod_nue=$_POST["prod_nuevo"];
  $id_p_esp=$_POST['prod_nuevo'];
  $defecto=$_POST['defecto'];
  $canti=$_POST['cantidad_1'];
  $descri_prod=$_POST['descri_prod'];
  $marca=$_POST['marca'];
  $modelo=$_POST["modelo"];
  $tipo=$_POST["tipo"];
  $bus_barra=1;
  $sumar=0;
  $sumar1=0;
  while ($sumar<$canti){
  		$codigo[$sumar]=$_POST["codigo_$sumar"];
  		$cod=$_POST["codigo_$sumar"];
  		if($cod!="")
  		{
  		if($id_info_rma!="")
  		{
  		$select="select * from void_rma where void='$cod' and id_info_rma<>$id_info_rma";
  		}
  		else
  		{
  		$select="select * from void_rma where void='$cod'";
  		}
  		$eje=sql($select,"No se pudo recuperar los void") or fin_pagina();
  		if($eje->RecordCount()>0)
  		{

  			$codigo[$sumar]=" ";
  			$error_void[$sumar1]=$eje->fields['void'];
  			$error_info[$sumar1]=$eje->fields['id_info_rma'];
  			$sumar1++;
  		}
  		}
  		$sumar++;
  	}

 $codigo1=$_POST["codigo_0"];
     //echo "$codigo codigo";
 // echo"desc $descri_prod marca $marca";
  if($codigo1!="")
  {
  $sql="select tipos_prod.descripcion as tipo,id_prod_esp,codigos_barra.codigo_barra, codigos_barra.codigo_padre, codigos_barra.comentario,orden_de_compra.id_contacto,
        codigos_barra.puesto_servicio_tecnico, log_codigos_barra.nro_orden, orden_de_compra.fecha_entrega,
		(case when (codigos_barra.id_producto is not null) then productos.desc_gral
			else producto_especifico.descripcion
		end) as descripcion,
		(case when (codigos_barra.id_producto is not null) then productos.precio_licitacion
			else producto_especifico.precio_stock
		end) as precio
	from general.codigos_barra
		left join general.producto_especifico using(id_prod_esp)
		left join general.productos on(productos.id_producto=codigos_barra.id_producto)
		join general.log_codigos_barra using(codigo_barra)
		left join compras.orden_de_compra using(nro_orden)
		join general.tipos_prod on (tipos_prod.id_tipo_prod=productos.id_tipo_prod or tipos_prod.id_tipo_prod=producto_especifico.id_tipo_prod)
	where codigos_barra.codigo_barra ='$codigo1'
	and log_codigos_barra.tipo like '%Ingresado%'";
    $resul = sql($sql) or fin_pagina();
    $contar=$resul->RecordCount();
    if($contar!=0)
    {
    $oc = $resul->fields["nro_orden"];
    if($oc!="")
    {
    $sql_ord_compra="select id_proveedor from orden_de_compra where nro_orden =$oc";
	$result_ord_compra=sql($sql_ord_compra,"Error obteniendo datos de la Orden de Compra") or fin_pagina();
	//asigno la variable del id_proveedor para usar en la consulta para traer la razon social del proveedor
	$idProveedor = $result_ord_compra->fields["id_proveedor"];
	//levanto la razon social para mostrar
	$sql_proveedor="select razon_social from proveedor where id_proveedor = $idProveedor";
	$result_proveedor=sql($sql_proveedor,"Error obteniendo datos del Proveedor") or fin_pagina();
    $descrip=$resul->fields["descripcion"];
    $social=$result_proveedor->fields["razon_social"];
    $id_pr=$result_ord_compra->fields["id_proveedor"];
    $id_prod_es=$result_ord_compra->fields["id_prod_esp"];
    $contacto=$result_ord_compra->fields["id_contacto"];
    }

    }

   else {
  	?>
  	<script>
  	alert("El codigo de barra ingresado es incorrecto");
  	</script>
  	<?
  }
  }
  else {
  	?>
  	<script>
  	alert("Debe Ingresar un codigo de barra");
  	</script>
  	<?
  }
  $descri_prod=$_POST['descri_prod'];
  $modelo=$_POST['modelo'];
  $marca=$_POST['marca'];
  $tipo=$_POST['tipo'];
  $fecha_base=$_POST['fecha_base'];
} //del if de codigo de barra

////////////////////////////////Generar/////////////////////////////////////////////

if ($_POST["generar"])
{
  $nuevo_coment=$_POST["nuevo_coment"];
  $prod_nue=$_POST["prod_nuevo"];
  $id_p_esp=$_POST['prod_nuevo'];
  $defecto=$_POST['defecto'];
  $canti=$_POST['cantidad_1'];
  $descri_prod=$_POST['descri_prod'];
  $marca=$_POST['marca'];
  $modelo=$_POST["modelo"];
  $tipo=$_POST["tipo"];
  $bus_barra=1;
  $descri_prod=$_POST['descri_prod'];
  $modelo=$_POST['modelo'];
  $marca=$_POST['marca'];
  $tipo=$_POST['tipo'];
} //del if de codigo de barra

//////////////////////////////Consulta para llenar campos///////////////////////////
echo $html_header;
if($id_info_rma!="")
{
	$cons=" select info_rma.cantidad, en_stock.id_prod_esp,estado_rma.nombre_corto,estado_rma.lugar,
	          en_stock.id_deposito,en_stock.ubicacion,id_nota_credito,id_estado_rma,
	          producto_especifico.descripcion,producto_especifico.marca,producto_especifico.modelo,
	          producto_especifico.precio_stock,info_rma.id_info_rma,info_rma.id_proveedor,info_rma.nro_orden
	          ,proveedor.razon_social,producto_especifico.id_tipo_prod,tipos_prod.codigo,info_rma.void,
	          info_rma.desc_void,info_rma.nrocaso,info_rma.defecto_parte,fecha_hist,fecha_creacion,
	          ubicacion_rma.id_ubicacion_rma
	    from stock.en_stock
	    join general.producto_especifico using(id_prod_esp)
	    join general.depositos using (id_deposito)
	    join stock.info_rma using (id_en_stock)
	    join general.tipos_prod using(id_tipo_prod)
	    left join general.proveedor using (id_proveedor)
	    left join stock.estado_rma using (id_estado_rma)
	    left join stock.ubicacion_rma using (id_ubicacion_rma)
		where id_info_rma=$id_info_rma";

	$datos=sql($cons,"Error al traer los datos del producto en RMA") or fin_pagina();

	$id_proveedor=$datos->fields["id_proveedor"];
	$id_deposito=$datos->fields["id_deposito"];
	$id_prod_esp=$datos->fields["id_prod_esp"];

	$sel="select * from void_rma where id_info_rma=$id_info_rma";
	$sel_void=sql($sel,"Error al traer los void del producto en RMA") or fin_pagina();
}//de if($id_info_rma!="")
?>
<script>
var contador=0;
var wproductos=0;
var wproductos_2=0;
var wproductos_3=0;
var wproductos_5=0;
var insertar_ok=0;
var control_log=0;

/*****************************************************************/
function cargar_2()
{
 //document.all.precio.value=wproductos_2.document.all.precio.value;
 document.all.id_prov_nuevo.value=wproductos_2.document.all.id_prov.value;
 document.all.proveedor.value=wproductos_2.document.all.razon_social.value;
 //document.all.precio_nuevo.value=wproductos_2.document.all.precio.value;
 //eval("document.all.oculta_comentario.style.display='block'");
 wproductos_2.close();
}

/*****************************************************************/
function cargar_5()
{
 document.all.caso.value=wproductos_5.document.all.caso.value;
 wproductos_5.close();
}

/*****************************************************************/

function cargar_3()
{
 //document.all.precio.value=wproductos_2.document.all.precio.value;
 document.all.oc.value=wproductos_3.document.all.oc.value;
 //document.all.precio_nuevo.value=wproductos_2.document.all.precio.value;
 wproductos_3.close();
}
/*****************************************************************/

/*********************************************************************/
function nuevo_item_2()
{pagina_prod="<?=encode_link('stock_cambio_producto.php',array('onclickcargar'=>"window.opener.cargar_2()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
 wproductos_2=window.open(pagina_prod+'&id_producto='+document.all.prod_nuevo.value,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=300,width=550,height=150');
}
/*********************************************************************/

function nuevo_item_3()
{
 pagina_prod="<?=encode_link('stock_buscar_codigo.php',array('onclickcargar'=>"window.opener.cargar_2()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
 wproductos_3=window.open(pagina_prod+'&oc='+document.all.oc.value,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=300,width=550,height=150');
}

function nuevo_item_5()
{
 pagina_prod="<?=encode_link('stock_buscar_caso.php',array('onclickcargar'=>"window.opener.cargar_5()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
 wproductos_5=window.open(pagina_prod+'&oc='+document.all.caso.value,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=300,width=550,height=150');
}
/*********************************************************************/


/*********************************************************************/

function nuevo_item_4()
{
 var or_comp=document.all.oc.value;
 alert("<?=$oc?>");
 pagina_prod1="<?=$ref_ord_comp=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$oc));?>"
 window.open(pagina_prod1+'&oc='+document.all.oc.value,target='_new');
}

/*********************************************************************/
function nuevo_item()
{var pagina_prod;
 var nbre_prov;
 var stock_page;
 pagina_prod="<?=encode_link("../productos/listado_productos_especificos.php",array("pagina_viene"=>"stock_rma.php","onclick_cargar"=>$onclick_cargar))?>"
 wproductos=window.open(pagina_prod);
}

function control_datos()
{if (document.all.cantidad_1.value==0)
    {
     alert ("Debe llenar el campo Cantidad")
     return false;
    }
    if (document.all.proveedor.value=="")
    {
     alert ("Debe llenar el campo proveedor")
     return false;
    }
    if (document.all.descri_prod.value=="")
    {
     alert ("Debe llenar el campo producto")
     return false;
    }
    if (typeof(document.all.ubicacion_parte.value)!=undefined)
    {
    if (document.all.ubicacion_parte.value==6)
    {
     if (document.all.defecto.value=="")
     {
      alert ("Debe llenar el campo Defecto de la Parte")
      return false;
     }
    }
    }
    if(document.all.nuevo_coment.value.indexOf('"')!=-1)
     {
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Comentario');
       return false;
     }
     if(document.all.defecto.value.indexOf('"')!=-1)
     {
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Defecto de la parte');
       return false;
     }

 return true;
}

function mostrar_logs()
{if (control_log==0)
    {eval("document.all.oculta_log.style.display='block'");
     document.all.muestra_log.value="Ocultar log de Cambios";
     control_log=1;
    }
 else {eval("document.all.oculta_log.style.display='none'");
       document.all.muestra_log.value="Muestrar log de Cambios";
       control_log=0;
      }
}

function control_datos_rec(){

  if(document.all.cant_rec.value=="") {
   alert('Debe ingresar un número valido para el campo cantidad');
   return false;
  }
  if (document.all.cant_rec.value != document.all.comprado.value ) {
   alert('La cantidad de productos recibida debe ser igual a la cantidad de productos en el RMA');
   return false;
  }
  if (document.all.prod_rec.value=="") {
   alert('Debe seleccionar un producto');
   return false;

}
return true;
}
function control_ato()
{

   if(document.all.cantidad_1.value==1) {
   alert('Para Atomizar el RMA la cantidad debe ser mayor que uno');
   return false;
  }
  return true;
}

</script>
<form action="stock_rma.php" method="POST" name="form1">

<?

function cargar(&$arr)
{
 $i=0;
 while($i<sizeof($arr)){
    if ($arr[$i][0]!=-1){
	   $id=$arr[$i];
	   $arr[$i][0]=-1;
	   return $id;
	}
	else $i++;
	}
 return false;
}//de function cargar(&$arr)


if($id_info_rma!="")
{
 $sql_2="select * from stock.log_info_rma where id_info_rma=$id_info_rma order by (fecha)";
 $resul_log=sql($sql_2) or fin_pagina($sql_2);
 if ($resul_log->RecordCount()!=0)
    {
    ?>
   <table border="1" cellpadding="0" cellspacing="0" width="80%" align="center" bgcolor="<?=$bgcolor3?>">
				<tr>
					<td id="mo" align="center">Logs de Stock RMA</td>
				</tr>
			</table>
			<div style="overflow:auto; <?=(($resul_log->recordCount()>3)?" height:60;":"")?>">
				<table border="1" cellpadding="0" cellspacing="0" width="80%" align="center" bgcolor="<?=$bgcolor3?>">
				<?

				 while (!$resul_log->EOF)
				 {

				 	$tipo=$resul_log->fields["tipo_log"];

					//vemos el tipo de log para hacer el link correspondiente
				 	$pos_id = strpos($tipo,'Nº ')+3;

				 	$es_pm=strpos($tipo,"PM");
				 	$es_mm=strpos($tipo,"MM");
				 	$es_lic=strpos($tipo,"Licitación");

					//si está alguno de estos strings en el tipo de log, ponemos el link correspondiente
                    if($es_pm)
                    {
                    	$id= substr($tipo,$pos_id);
                    	$tipo= substr($tipo,0,$pos_id);

                    	$link_pm=encode_link("../mov_material/detalle_movimiento.php",array("id"=>$id,"pagina"=>"stock_rma"));
                    	$tipo.=" <a href=$link_pm target='_blank'>$id</a>";
                    }
                    if($es_mm)
                    {
                    	$id= substr($tipo,$pos_id);
                    	$tipo= substr($tipo,0,$pos_id);

                    	$link_mm=encode_link("../mov_material/detalle_movimiento.php",array("id"=>$id,"pagina"=>"stock_rma"));
                    	$tipo.=" <a href=$link_mm target='_blank'>$id</a>";
                    }
                    if($es_lic)
                    {
                          $id= substr($tipo,$pos_id);
                    	  $tipo= substr($tipo,0,$pos_id);
                          $link_lic=encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id));
                          $tipo.=" <a href=$link_lic target='_blank'>$id</a>";
                    }


					?>
					<tr>
						<td> <?echo "Tipo: ".$tipo?></td>
						<td> <?echo "Usuario: ".$resul_log->fields["usuario_log"]?></td>
						<td> <?echo "Fecha: ".fecha($resul_log->fields["fecha"])?></td>
					</tr>
				<?
					$resul_log->moveNext();
				}//de while (!$resul_log->EOF)
				?>
				</table>
			</div>
			<br>
<?}
}

if($sumar1>0)
{?>

<table border="1" cellpadding="0" cellspacing="0" width="80%" align="center" bordercolor="Red">
				<tr id="mo">
					<td align="center" colspan="3"><font color="Red"><b>Void Repetidos</b></font></td>
				</tr>
<? $int=0;
 while ($int<$sumar1) {
    ?>
   	<tr>
	<td align="center"><b><?echo "void: ".$error_void[$int]?></b></td>
	<td align="center"><b><?echo "Nro RMA: ".$error_info[$int]?></b></td>
	<td align="center">
	<? $link = encode_link("stock_rma.php",array("id_info_rma"=>$error_info[$int],"pagina"=>10));
    $link1="window.open('$link','','top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0');";?>
    <input type="button" name=nuevo value='Ver' onclick="<?=$link1?>">
    </td>
	</tr>


<?
 $int++;
 }?>
 </table>
<?}?>
<table width="80%" align="center">
 <tr>
  <td>
   <table width="100%" align="center" cellpadding="3" border="1">
    <tr id=mo>
     <td align="center" <?if($id_info_rma==""){?>colspan="2"<?}?>>
      Información del Producto
     </td>
     <?
     if($id_info_rma!="")
     {
     ?>
     <td align="center">
      <b>N° RMA: <?=$id_info_rma?></b>
     </td>
     <?}?>
    </tr>
    <?if($bus_barra!=1)
    {
      $descri_prod=$datos->fields['descripcion'];
      $marca=$datos->fields['marca'];
      $modelo=$datos->fields["modelo"];
      $tipo=$datos->fields["codigo"];
      $id_p_esp=$datos->fields['id_prod_esp'];
      $canti=$datos->fields['cantidad'];
      $defecto=$datos->fields['defecto_parte'];
      $fecha_base=$datos->fields['fecha_hist'];

      $fecha_crea=$datos->fields['fecha_creacion'];
    }?>
    <tr id=ma_sf>
    <input type="hidden" name="fecha_base" value="<?=$fecha_base?>">
    <input type="hidden" name="fecha_crea" value="<?=$fecha_crea?>">
     <td width="100%"0%" colspan="2">
      <b>Producto</b>
       &nbsp;&nbsp;&nbsp;<b><font color="black" size="2">
      <input name="descri_prod" type="text" readonly style="width=80%" style="font-size: 10pt" class="text_4" value="<?=$descri_prod?>" size="80"></font></b>
      <input name="cambiar" type="button" value="C" title="Cambiar Producto" onclick="nuevo_item()">
      <input name="prod_nuevo" type="hidden" value="<?=$id_p_esp?>">
      <input name="prod_anterior" type="hidden" value="<?=$id_p_esp?>">
     </td>
    </tr>
    <tr id=ma_sf>
     <td width="60%">
      <b>Marca</b>
       &nbsp;&nbsp;&nbsp;<b><font color="black" size="2">
       <input name="marca" type="text" readonly style="width=80%" style="font-size: 10pt" class="text_4"  value="<?=$marca?>"></font></b>
     </td>
     <td width="30%">
      <b>Modelo</b>
       &nbsp;&nbsp;&nbsp;<b><font color="black" size="2">
       <input name="modelo" type="text" style="width=80%" style="font-size: 10pt" class="text_4" readonly value="<?=$modelo?>" size=6></font></b>
     </td>
     </tr>
    <tr id=ma_sf>
     <td width="60%">
      <b>Cantidad: </b>
      <b><font color="Black" size="2"> <?if($id_prod_esp!=""){echo($canti);?>
      <input name="cantidad_1" type="hidden" value="<?=$canti?>">
      <?} else{?>
      <input type="text" name="cantidad_1" size="5" value="<?=$canti?>">
      <input type="submit" name="generar" value="Habilitar Campos Para Cargar Void" title="Generar Cantidad Void">
      <?}?>
      </font></b>

     </td>
     <td width="60%">
      <b>Tipo</b>
      <b><font color="Black" size="2"> <input name="tipo" type="text" style="width=80%" style="font-size: 10pt" class="text_4" readonly value="<?=$tipo?>"></font></b>
      <input name="tipo_nuevo" type="hidden" value="<?=$datos->fields['descripcion']?>">
      <input name="tipo_viejo" type="hidden" value="<?=$datos->fields['descripcion']?>">
     </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>

    <?////////////////////Tabla de iformacion del codigo de barra/////////////////
    if($bus_barra!=1)
    {
      $descrip=$datos->fields["desc_void"];
      $social=$datos->fields["razon_social"];
      $id_pr=$datos->fields["id_proveedor"];
      $oc=$datos->fields["nro_orden"];

      //$codigo=$datos->fields['void'];
      $caso=$datos->fields['nrocaso'];
      if($oc==0)
      $oc="";
      if($id_info_rma!="")
      {
      $total=$sel_void->RecordCount();
      if($total>0)
      {
      $sum=0;
      while (!$sel_void->EOF) {
      	$valor=$sel_void->fields['void'];
      	$codigo[$sum]=$valor;
      	$sum++;
      	$sel_void->MoveNext();
      }
      }
      else
      {
      	$codigo[0]=$datos->fields['void'];
      }
      }
    }
    ?>
    <table width="80%" align="center">
    <tr>
    <td>
    <table width="100%" align="center" cellpadding="3" border="1">
    <tr id=ma_sf>
    <td colspan="2">

    <table width="100%" align="center" cellpadding="3" border="1">
    <tr id=ma_sf>
    <td width="35%">
     <b>Codigo de Barra</b>
    </td>
    <td>
    <div style="overflow:auto;width:100%;height:50">
    <?
    if($id_info_rma=="")
    {

    $cont=0;
    while($cont<$canti)
    {?>
    <input type="text" name="codigo_<?=$cont?>" size="15" value="<?=$codigo[$cont]?>">&nbsp;
    <?if($cont==0)
    {?>
    <input name="barra" type="submit" value="B" title="Buscar Codigo de Barra">
    <?}?>
    <br>
    <?
    $cont++;
    }
    }

    else
    {
    if($canti=="")
    {
    ?>
    <input type="text" name="codigo_<?=$cont?>" size="15" value="<?=$codigo[1]?>">&nbsp;
    <input name="barra" type="submit" value="B" title="Buscar Codigo de Barra">
    <?
    }
    $cont=0;
    while($cont<$canti)
    {?>
    <input type="text" name="codigo_<?=$cont?>" size="15" value="<?=$codigo[$cont]?>">&nbsp;
    <?if($cont==0)
    {?>
    <input name="barra" type="submit" value="B" title="Buscar Codigo de Barra">
    <?}?>
    <br>
    <?
    $cont++;
    }
    }
    ?>
    </div>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td width="75%" colspan="2">
    <b>Descripcion</b><input type="text" name="desc_cod" size="70" value="<?=$descrip?>">
    </td>
    </tr>
    <tr id=ma_sf>
     <td width="100%" colspan="2">
      <b>Proveedor</b>
     <input name="proveedor" readonly type="text" value="<?=$social?>" size="50"></font></b>
     <input name="id_prov_nuevo" type="hidden" value="<?=$id_pr?>">
     <input name="id_prov_viejo" type="hidden" value="<?=$id_pr?>">
     &nbsp;<input name="cambiar_prov" type="button" value="C" title="Cambiar Proveedor" onclick="nuevo_item_2()">
     &nbsp;<input type="button" value="Política de RMA" onClick="if (ventana.style.visibility=='visible') ventana.style.visibility='hidden'; else {
		   ventana.style.visibility='visible';
		   ventana.style.top=((document.body.clientHeight/2)-(300/2))+document.body.scrollTop;}">
	   <div id="ventana" style="background-color: white;position: absolute;overflow: auto;width: 600;height: 300;border: outset 2;visibility: hidden;">
		<table width=100% cellspacing=0 cellpadding=0 border=0>
		<tr bgcolor="#006699">
			<td width=100% align="center">
				<font size=2 color='#cdcdcd'><b>Política de RMA</b></font>
			</td>
			<td>
				<img src="../../imagenes/salir.gif" style="cursor: hand;" onClick="ventana.style.visibility='hidden'">
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<p><?echo html_out($datos->fields["politica_rma"]);?></p>
			</td>
		</tr>
		</table>
		<script>
			ventana.style.left=((document.body.clientWidth/2)-(600/2));
		</script>
	   </div>
     </td>
    </tr>
    <tr id=ma_sf>
     <td width="40%">
      <?
       $link = encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$oc));
       if ($oc!=""){
       	?>
       	<input type="button" name="configurar" value="IR"  onclick="window.open('<?=$link?>');">
       	<?}?>
      <b>OC:&nbsp;</b>

      <b><font color="Black" size="2">
      <input name="oc" type="text" style="width=20%" style="font-size: 10pt" class="text_4" readonly value="<?=$oc?>">
     </font></b>
      <?
       if ($oc!=""){
      /*?>
      <input name="cambiar_oc" type="button" value="Ir" title="Cambiar OC" onclick="<?=nuevo_item_4($oc)?>">
     <?*/}?>
     <input name="cambiar_oc" type="button" value="C" title="Cambiar OC" onclick="nuevo_item_3()">

     </td>
     <td width="60%">
      <?
      if($caso!="")
      {
      	$sql1="Select idcaso from casos_cdr where nrocaso='$caso'";
		$rs11=sql($sql1) or fin_pagina();
      	$link1 = encode_link("../casos/caso_estados.php",array("id"=>$rs11->fields['idcaso'],"id_entidad"=>2556));
      	?>
      	<input type="button" name="configurar" value="IR"  onclick="window.open('<?=$link1?>');">
      	<?
      }
      ?>
      <b>CASO:&nbsp;</b>
      <b><font color="Black" size="2"> <input name="caso" type="text" style="width=40%" style="font-size: 10pt" class="text_4" readonly value="<?=$caso?>" ></font></b>
      <input name="cambiar_caso" type="button" value="C" title="Cambiar Caso" onclick="nuevo_item_5()">

     </td>
    </tr>
    </table>
    </td>
    </tr>
  </table>
  <?
  //$nro_orden=$datos->fields['nro_orden'];
  if($oc!="")
  {
  $query_asoc="select fact_prov.fecha_emision, compras.factura_asociadas.* from compras.factura_asociadas join general.fact_prov using(id_factura) where nro_orden=$oc";
  $res_asoc=sql($query_asoc) or fin_pagina();
  ?>
  <table width="80%" align="center">
  <tr>
  <td>
  <table border="1" align="center" width="100%">
		   <tr id=ma_sf>
		    <td width="49%" align="center"><strong> ID Factura </strong> </td>
		    <td width="47%" align="center" id="td_fecha_factura"><strong>Fecha Factura</strong>&nbsp;
		    <td width="47%" align="center" id="archi"><strong>Archivos</strong>&nbsp;
		    </td>
		   </tr>
		   <?
		    $filas=$res_asoc->RecordCount();
		    $cant_factura=$res_asoc->RecordCount();
			if ($filas>0){ //armo un arreglo con los id  y las fechas asociados a la orden
			  for ($i=0;$i<$filas;$i++){
			     $aux=array();
				 $aux[0]=$res_asoc->fields['id_factura'];
				 $aux[1]=$res_asoc->fields['fecha_emision'];
		         $list[$i]=$aux;
				 $res_asoc->MoveNext();
		      }
		    }

			for ($i=0; $i<$cant_factura;$i++)
			{
				$id=cargar($list);
				if ($datos_orden->fields['nro_factura']) $value=$datos_orden->fields['nro_factura'];
				   else
				  $value=$_POST["id_factura_$i"] or $value=$id[0];

				$query_arch="select * from arch_prov where id_factura=$value";
		        $res_arch=sql($query_arch,"al seleccionar archivos") or fin_pagina();

				if ($datos_orden->fields['fecha_factura']) $value_fecha=Fecha($datos_orden->fields['fecha_factura']);
			    else
				 $value_fecha= $_POST["fecha_factura_$i"] or $value_fecha=Fecha($id[1]);
				 //Fecha($parametros['fecha']) or
				 ?>
				 <tr>
			    <td align="center"> <input name="ver_factura_<?=$i?>" type="button"  value="ir" title='ver detalles de la factura'  onclick="<? if ($datos_orden->fields['nro_factura']) {?>alert ('La factura asociada no está cargada'); return false; <? }?> if (document.all.id_factura_<?=$i?>.value=='') return false; window.open('<?=encode_link("../factura_proveedores/fact_prov_subir.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>$i)) ?>&id_fact='+document.all.id_factura_<?=$i ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
			      <input name="id_factura_<?=$i?>" type="text" value="<?=$value?>">
			    </td>
			    <td align="center"><input name="fecha_factura_<?=$i ?>" <?= $permiso ?> readonly="true" type="text" value="<?=$value_fecha;?>" size="10">
			    </td>
			    <td>
			    <?
			    while (!$res_arch->EOF) {
			     if (is_file("../../uploads/facturacion/".$value.'-'.$res_arch->fields["nbre_arch"]))
			     {
                  echo "<a href='".encode_link("./../factura_proveedores/fact_prov_subir.php",array ("rma"=>1,"fact"=>$value,"file" =>$res_arch->fields["nbre_arch"],"size" => $res_arch->fields["tam_arch"],"cmd" => "download","fila"=>$parametros['fila']))."'>";
                  echo $res_arch->fields["nbre_arch"]."</a>";
                  echo"<br>";
			     }
			     $res_arch->MoveNext();
			    }
			    ?>
			    </td>
			   </tr>
		   <?


		   }//de for ($i=0; $i<$cant_factura;$i++)
		   ?>
		 </table>
	</td>
    </tr>
  </table>

 <?}////////////////////Tabla informacion del estado del Prod Esp//////////////////////?>
<table width="80%" border="1" align="center">
<input name="id_proveedor" value="<?=$id_proveedor?>" type="hidden">
<input name="id_producto" value="<?=$id_prod_esp?>" type="hidden">
<input name="id_deposito" value="<?=$id_deposito?>" type="hidden">
<input name="id_info_rma" value="<?=$id_info_rma?>" type="hidden">
<input name="id_control_stock" value="<?=$id_control_stock?>" type="hidden">
<input name="idestado" value="<?=$datos->fields['id_estado_rma']?>" type="hidden">


<?
$n_corto=$datos->fields['nombre_corto'];
$id_est_rma=$datos->fields['id_estado_rma'];

if($n_corto=='T')
{?>
  <tr>
  <td align="center" colspan="2"><b><font size='+1' color='red'>Estado Actual Transito</font></b></td>
  <input type="hidden" name="est_act" value="Transito">
  </tr>
  <tr>
  <td width="30%">
   <b>Estado:&nbsp;</b><select name="ubicacion_parte" >
   <option value="<?=$id_est_rma?>"></option>
   <?

    $sql_ubicacion="select * from stock.estado_rma order by id_estado_rma";//esto es para saber cual es el codigo de en transito
    $resultado_sql_ubicacion=sql($sql_ubicacion) or fin_pagina();
    while (!$resultado_sql_ubicacion->EOF)
          {//if ($resultado_sql_ubicacion->fields['id_estado_rma']==$datos->fields['id_estado_rma']){ $selected="selected";}
              if(($resultado_sql_ubicacion->fields['lugar']=="Recibido Sin Clasificar")||
              ($resultado_sql_ubicacion->fields['lugar']=="Pedido de Scrap")||
              ($resultado_sql_ubicacion->fields['lugar']=="Enviado a Proveedor")||
              ($resultado_sql_ubicacion->fields['lugar']=="Para Probar/Reparar")||
              ($resultado_sql_ubicacion->fields['lugar']=="Pasar a Stock"))
              {?>
               <option  <?=$selected?> value="<?=$resultado_sql_ubicacion->fields['id_estado_rma']?>"><?=$resultado_sql_ubicacion->fields['lugar']?></option>
               <?
               $selected="";
              }



           $resultado_sql_ubicacion->MoveNext();
}//del while
   ?>
   </select>
  </td>

<?
}
if($n_corto=='C')
{
$n_lugar=$datos->fields['lugar'];
?>
 <tr>
  <td align="center" colspan="2"><b><font size='+1' color='red'>Estado Actual <?=$n_lugar?>(Coradir)</font></b></td>
  <input type="hidden" name="est_act" value="Coradir">
  </tr>
  <tr>
  <td width="30%">
   <b>Estado:&nbsp;</b><select name="ubicacion_parte" >
   <option value="<?=$id_est_rma?>"></option>
   <?
    $sql_ubicacion="select * from stock.estado_rma order by id_estado_rma";//esto es para saber cual es el codigo de en transito
    $resultado_sql_ubicacion=sql($sql_ubicacion) or fin_pagina();
    while (!$resultado_sql_ubicacion->EOF)
          {//if ($resultado_sql_ubicacion->fields['id_estado_rma']==$datos->fields['id_estado_rma']){ $selected="selected";}
              if(($resultado_sql_ubicacion->fields['lugar']=="Pedido de Scrap") ||
              ($resultado_sql_ubicacion->fields['lugar']=="Enviado a Proveedor") ||
              ($resultado_sql_ubicacion->fields['lugar']=="Listo para Salir") ||
              ($resultado_sql_ubicacion->fields['lugar']=="Pasar a Stock") ||
              ($resultado_sql_ubicacion->fields['lugar']=="Nota de Credito") ||
              ($resultado_sql_ubicacion->fields['lugar']=="Para Probar/Reparar") ||
              ($resultado_sql_ubicacion->fields['lugar']=="Recibido Sin Clasificar")
               )
              {?>
               <option  <?=$selected?> value="<?=$resultado_sql_ubicacion->fields['id_estado_rma']?>"><?=$resultado_sql_ubicacion->fields['lugar']?></option>
               <?
               $selected="";
              }


           $resultado_sql_ubicacion->MoveNext();
}//del while
   ?>
   </select>
  </td>

<?
}
if($n_corto=='P')
{ $n_lugar=$datos->fields['lugar'];
  ?>
   <tr>
  <td align="center" colspan="2"><b><font size='+1' color='red'>Estado Actual <?=$n_lugar?> (Proveedor)</font></b></td>
  <input type="hidden" name="est_act" value="Proveedor">
  </tr>
  <tr>
  <td width="30%">
   <b>Estado:&nbsp;</b><select name="ubicacion_parte" >
   <option value="<?=$id_est_rma?>"></option>
   <?
    //$sql_ubicacion="select * from stock.estado_rma where estado_rma.nombre_corto='C' order by id_estado_rma";//esto es para saber cual es el codigo de en transito
    $sql_ubicacion="select * from stock.estado_rma order by id_estado_rma";//esto es para saber cual es el codigo de en transito
    $resultado_sql_ubicacion=sql($sql_ubicacion) or fin_pagina();
    while (!$resultado_sql_ubicacion->EOF)
          {echo $resultado_sql_ubicacion->fields['lugar'];
          	//if ($resultado_sql_ubicacion->fields['id_estado_rma']==$datos->fields['id_estado_rma']){ $selected="selected";}
              if(($resultado_sql_ubicacion->fields['lugar']=="Pedido de Scrap")||($resultado_sql_ubicacion->fields['lugar']=="Rechazo Sin Garantía")||($resultado_sql_ubicacion->fields['lugar']=="Rechazo Sin Defecto")
              ||($resultado_sql_ubicacion->fields['lugar']=="Pasar a Stock")
              ||($resultado_sql_ubicacion->fields['lugar']=="Nota de Credito")
              )
              {?>
               <option  <?=$selected?> value="<?=$resultado_sql_ubicacion->fields['id_estado_rma']?>"><?=$resultado_sql_ubicacion->fields['lugar']?></option>
               <?
               $selected="";
              }



           $resultado_sql_ubicacion->MoveNext();
}//del while
   ?>
   </select>
  </td>
<?

}

if($n_corto=='B')
{ $n_lugar=$datos->fields['lugar'];
  $disabled_guardar=" disabled";
  $link_nc=encode_link("../ord_compra/nota_credito.php",array("id_nota_credito"=>$datos->fields['id_nota_credito'],"pagina"=>"stock_rma"));
  ?>
   <tr>
     <td align="center" colspan="2"><b><font size='+1' color='red'>Estado Actual: <?=$n_lugar;?> (Historial)</fonT></b></td>
  </tr>
  <?/* if ($datos->fields['id_estado_rma']==13) {?>
   <tr>
     <td align="center" colspan="2"> <font size="+1"> ID Nota de Crédito: </font>
        <b> <a href="<?=$link_nc?>" style="font-size='16'; color='blue';" target="_blank" ><U><?=$datos->fields['id_nota_credito']?></b></A>

     </td>
  </tr>
  <?}*/?>
  <tr>
<?}
$sel_ubi="select * from ubicacion_rma where activo=1 ";
$sel_ubi.="or ".(($datos->fields["id_ubicacion_rma"]!="")?"id_ubicacion_rma=".$datos->fields["id_ubicacion_rma"]:"comentario='".$datos->fields["ubicacion"]."'");
$sql_ubi.="order by comentario";
$select_ubic=sql($sel_ubi,"No se pudo recuperar las ubicaciones") or fin_pagina();
if($datos->fields["id_ubicacion_rma"]!="")
{
?>
<td width="50%"><b>Ubicación</b>
  <select name="ubicacion">
  <option value=""></option>
  <?
  while(!$select_ubic->EOF)
  {
  ?>
  <option value="<?=$select_ubic->fields['id_ubicacion_rma']?>"<?if($datos->fields["id_ubicacion_rma"]==$select_ubic->fields['id_ubicacion_rma']){?>selected<?}?> ><?=$select_ubic->fields['comentario']?></option>
  <?
  $select_ubic->MoveNext();
  }
  ?>
  </select>
  <!-- <textarea name="ubicacion" rows=1 style="width:30%"></textarea>-->
  </td>
<?
}
else
{
?>
  <td width="50%"><b>Ubicación</b>
  <select name="ubicacion">
  <option value="" selected></option>
  <?
  while(!$select_ubic->EOF)
  {
  ?>
  <option value="<?=$select_ubic->fields['id_ubicacion_rma']?>"<?if($datos->fields["ubicacion"]==$select_ubic->fields['comentario']){?>selected<?}?> ><?=$select_ubic->fields['comentario']?></option>
  <?
  $select_ubic->MoveNext();
  }
  ?>
  </select>
  <!-- <textarea name="ubicacion" rows=1 style="width:30%"></textarea>-->
  </td>
<?}?>
 </tr>
</table>
<br>
<?/////////////////////////Defecto de la parte////////////////////////////////?>
<table width="80%" align="center" border="1" bordercolor="Red">
 <tr>
  <td id="mo" align="center">
  <b>Defecto de la Parte</b>
  </td>
 </tr>
 <tr>
  <td align="center">
  <textarea name="defecto" rows="2" style="width:80%"><?=$defecto?></textarea>
  </td>
 </tr>
</table>
<br>



<?//////////////////////////Nuevo Comentarios////////////////////////////?>

<br>
<table width="80%" align="center" border="1" >
<?
if($id_info_rma!="" && $pagina!=10)
{
$query="select * from comentario_rma where id_info_rma=$id_info_rma";
$comentarios=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los comentarios del RMA");
?>

<!--//////////////////////////////////////////////////-->


 <tr id=mo bgcolor="<?=$bgcolor_out?>">
  <td>
   Comentarios
  </td>
 </tr>
 <tr>
  <td>
   <table align="center" width="100%">
    <?
    //generamos los comentarios ya cargados
    while(!$comentarios->EOF)
    {?>
     <tr>
      <td>
       <table width="100%">
        <tr  id="ma_sf" bgcolor="<?=$bgcolor_out?>">
          <td width="65%" align="right">
          <b>
          <?
           $fecha=split(" ",$comentarios->fields['fecha']);
           echo fecha($fecha[0])." ".$fecha[1];
          ?>
          </b>
          </td>
         </tr>
         <tr id="ma_sf">
          <td align="right">
           <?=$comentarios->fields['usuario']?>
          </td>
        </tr>
       </table>
      </td>
      <td>
       <textarea rows="4" cols="90" readonly name="coment_<?=$comentarios->fields['id_comentario_rma']?>"><?=$comentarios->fields['texto']?></textarea>
      </td>
     </tr>
     <?
     $comentarios->MoveNext();
    }
}
    //y luego damos la opcion a guardar uno mas
    ?>
    <tr>
     <td colspan="2" bgcolor=<?=$bgcolor_out?>>

      <table>
       <tr>
        <td width="25%"  id="ma_sf">
         <b>Nuevo Comentario</b>
        </td>
        <td width="75%">
         &nbsp;<textarea rows="4" cols="70" name="nuevo_coment"><?=$nuevo_coment?></textarea>
        </td>
       </tr>
      </table>

     </td>
    </tr>
    <?
    if($id_info_rma)
    {
    ?>
    <tr>
	    <td align="center" colspan="2">
	     <input type="submit" name="guardar_comentario" value="Guardar Comentario">
	    </td>
    </tr>
    <?
    }//de if($id_info_rma)
    ?>
   </table>
  </td>
 </tr>
</table>

<?
////////////////////////////////////////////////Fin Nuevo Comentario///////////////////////////////////

///////////////////////// Recepcion de productos ///////////////////////////////
//$productos=array();
if ($n_lugar=='Pasar a Stock' || $n_lugar=='Baja por cambio por nueva')
{?>
	<br>
	<table width="100%" class="bordes" align="center" bgcolor='<?=$bgcolor_out?>'>
	  <tr>
	     <td align="center" id=mo colspan="2">
		  <font size="3">
		  Recepción de Productos
		  </font>
		 </td>
	  </tr>
	  <tr>
		 <td>
		 <? generar_form_recepcion_rma($id_info_rma);?>
		 </td>
	  </tr>
	  <tr>
	     <td>
	     <? $productos=mostrar_log_recepcion_rma($id_info_rma);?>
	     </td>
	  </tr>
	</table>
	<input type='hidden' name="productos" value="<?=comprimir_variable($productos)?>">
<?
}//de if ($n_lugar=='Pasar a Stock' || $n_lugar=='Baja por cambio por nueva')

if($id_info_rma!="" && $pagina!=10)
{
 $sql_archivos="select * from stock.archivo_rma where id_deposito=$id_deposito and id_info_rma=$id_info_rma and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
 $consulta_sql_archivos=sql($sql_archivos) or fin_pagina();

if ($consulta_sql_archivos->RecordCount()!=0)
{
?>
<!--/////////////////////////////////////////////////////////////////-->
 <table width="80%" align="center" border="1">
  <tr id=mo>
   <td align="center" colspan="5"><font size="2"><b>Archivos Subidos</b></font></td>
 <tr id=ma >
    <td align="left" colspan="5">
     <b>Documentos:</b> <?=$consulta_sql_archivos->RecordCount()?>.
    </td>

  </tr>
<tr id=mo>
 <td width='10%'><b><INPUT type="submit" name="borrar" value="Borrar" title="Eliminar Seleccioneados" disabled onclick="return eliminar();"></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nº.</a></b></td>
 <td width='40%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Nombre.</a></b></td>
 <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Fecha.</A></b></td>
 <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Responsable.</A></b></td>
</tr>

<?  $i=0;
	while(!$consulta_sql_archivos->EOF){
	$link = encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_atchivos_subidos"],"download"=>1,"comp"=>0));
	?>

<TR id="ma" title="<?=$consulta_sql_archivos->fields["comentario"]?>">
	<TD>
    <input type="checkbox" name="select_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivos_subidos']; ?>" <?=$permiso_borrar?> onclick="habilitar_borrar(this);" title="Seleccione para eliminar">
	</TD>
	<TD>
	<?=$consulta_sql_archivos->fields["id_archivos_subidos"]?>
	</TD>
	<TD>
	<a title='<?=$consulta_sql_archivos->fields["nombre_archivo"]?> [<?=number_format($consulta_sql_archivos->fields["filesize_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_rma"],"download"=>1,"comp"=>1))?>'>
	<img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0></A>
	<a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_rma"],"download"=>1,"comp"=>0))?>'>
	<? echo $consulta_sql_archivos->fields["nombre_archivo"]." (".number_format(($consulta_sql_archivos->fields["filesize_comp"]/1024),"2",".","")."Kb)"?>
	</A>
	</TD>
	<TD>
	<?=Fecha($consulta_sql_archivos->fields["fecha_subido"])?>
	</TD>
	<TD>
	<? echo $consulta_sql_archivos->fields["usuario"]?>
	</TD>
</TR>
<? $consulta_sql_archivos->MoveNext(); $i++;}?>
	<INPUT type="hidden" name="Cantidad" value="<?=$i?>">
	</table>

<?
}//de if ($consulta_sql_archivos->RecordCount()!=0)

else echo "<table align=center><tr><td><b><font size=3>No hay Archivos para este Seguimiento.</font></b></td></tr></table>";

}
/////////////////////////////Remitos RMA////////////////////////////////////////////////
?>
<br>
<?
if($id_info_rma!="" && $pagina!=10)
{
 $sql_remitos="select nro_remito,usuario,fecha from remito_rma
 join remito_interno.log_remito_interno on id_remito=nro_remito
 where id_info_rma=$id_info_rma";
 $remitos=sql($sql_remitos) or fin_pagina();
 if ($remitos->RecordCount()!=0)
 {
 ?>

 <table width="80%" align="center" border="1">
  <tr id=mo>
   <td align="center" colspan="5"><font size="2"><b>Remitos</b></font></td>
    </tr>
    <tr id=mo>
    <td width='10%'><b>Nº.</b></td>
    <td width='20%'><b>Fecha.</b></td>
    <td width='40%'><b>Usuario.</b></td>
   </tr>
 <?
	while(!$remitos->EOF){
	$link = encode_link("./../remito_interno/remito_int_nuevo.php",array("remito"=>$remitos->fields['nro_remito'] ));
	?>
    <TR id="ma">
	<TD>
	<a target='_blank' href="<?=$link?>" >
	Remito Nro: <?=$remitos->fields["nro_remito"]?></a>
	</TD>
	<TD>
	<b><?=Fecha($remitos->fields["fecha"])?></b>
	</TD>
	<TD>
	<b><?=$remitos->fields["usuario"]?></b>
	</TD>
</TR>
<?
$remitos->MoveNext();
}?>

	</table>

<?
}
}



$link8=encode_link("remito_rma.php", array("id_info_rma"=>$id_info_rma,"formato"=>'new'));

?>
<!--/////////////////////////////////////////////////////////////////-->

<table align="center">
<?if($pagina!=10)
{
?>
  <tr><td>
  <input type="submit" <?=$disabled_guardar?> name="guardar" value="Guardar" onclick="return control_datos()" >
  <input type="button" name="volver" value="Volver" onclick="document.location='listar_rma.php'">
  <input name="subir_archivos" <?=$disabled_guardar?> type="button" value="Subir Archivos" onclick='window.open("<?=encode_link("subir_archivo_rma.php",array("id_producto"=>$id_prod_esp,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma));?>","","resizable=1,scrollbars=yes,width=700,height=300,left=20,top=50,status=yes");'>

  <?/*
  if ($permiso_recibir && $n_lugar=='Pasar a Stock')  {?>
      <input type="submit" name="recibir" value="Recibir" onclick="return (control_datos_rec());">
  <?}*/
  if($id_info_rma!="")
  {
  ?>
	  <input name="remito_rma" <?=$disabled_guardar?> type="button" value="Crear Remito Interno" onclick='window.open("<?=encode_link("remito_rma.php", array("id_info_rma"=>$id_info_rma,"formato"=>'new',"contacto"=>$contacto));?>")'>
	  <input type="submit" value="Atomizar" name="atomizar" onclick="return (control_ato());">

	  <input type="hidden" name="comentario_eliminar" value="">
	  <input type="hidden" name="h_eliminar" value="">
	  <?
	  //Si estamos en estado Transito y tiene permiso, mostramos el boton para eliminar RMA del Sistema
	  if($n_corto=='T' && permisos_check("inicio","permiso_eliminar_RMA"))
	  {?>
	  <input type="button" value="Eliminar RMA" name="eliminar_rma" title="Eliminar este RMA del sistema"
	    onclick="
	    //si confirma la eliminación del rma debe justificar el porque
	    if (confirm('¿Está seguro que desea ELIMINAR DEL SISTEMA el RMA Nº <?=$id_info_rma?>?'))
	     window.open('<?=encode_link("comentario_eliminar_rma.php",array("id_info_rma"=>$id_info_rma))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');"
	  >
	  <?
	  }

	  /*if (($tipo=='monitor')&&(!permisos_check('inicio','boton_pasar_a_muleto_rma'))){?>
      	<input type="submit" value="Pasar a Monitor RMA" name="pasar_a_monitor_rma" onclick="return confirm('¿Está seguro que desea PASAR A MONITOR RMA, el RMA Nº <?=$id_info_rma?>?')">
      <?}*/?>
	</td>
  <?
  }

  ?>
  </tr>
  <?}
  else
  {?>
  <tr><td align="center"><input type="button" name="cer" value="Cerrar" onclick="window.close()"> </td></tr>

  <?}?>
 </table>
<br>
<br>
</form>
<?=fin_pagina();?>