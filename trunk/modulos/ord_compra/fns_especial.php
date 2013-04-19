<?
/*
Autor: MAC
Fecha: 14/02/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.23 $
$Date: 2006/07/18 15:26:43 $
*/


/**********************************************************************
ATENCION!! en este archivo solo van las funciones que se usan para
modificar OC que están en estado mayor o igual que "autorizadas".
Los cambios que estan funciones realizan sobre la OC, solo se realizan
con permisos especiales, y son cambios que no deberían ocurrir, pero
 ocurren, debido a que hacen la OC de una manera y
despues de autorizadas, las quieren cambiar....
***********************************************************************/

/****************************************************
Función que despaga la OC que se pasa como parámetro.
El efecto de aplicar esta función a la OC es que se
desatarán los cheques, egresos de caja o débitos que
se hayan generado para la OC y quedarán "descolgados"

La función envia un mail avisando que se despagó
la Orden de Compra.
@nro_orden es el número de OC que se va a despagar
@moneda es el simbolo de la moneda tiene la OC
*****************************************************/
function despagar_oc($nro_orden,$moneda)
{
global $db,$msg,$_ses_user;
$db->StartTrans();
//traemos todos los pagos de la OC $nro_orden
 $query="select ordenes_pagos.id_pago,ordenes_pagos.id_ingreso_egreso,ordenes_pagos.idDébito,
 			ordenes_pagos.NúmeroCh,ordenes_pagos.fecha,ordenes_pagos.monto,
 			ordenes_pagos.valor_dolar,forma_de_pago.dias
         from compras.ordenes_pagos join compras.pago_orden using(id_pago)
         join compras.forma_de_pago using(id_forma)
         where pago_orden.nro_orden=$nro_orden";
 $pagos=sql($query,"<br>Error en despagar_oc - seleccion de pagos<br>") or fin_pagina();

 //por cada pago de la OC, desatamos el cheque, débito o egreso de caja
 //correspondiente
 //(el cheque, débito o egreso de caja quedan cargados
 // en el sistema)
 $pagos_afectados="";
 while(!$pagos->EOF)
 {$id_pago=$pagos->fields["id_pago"];
  //si el pago está atado a un egreso de caja, lo desatamos
  if($pagos->fields['id_ingreso_egreso']!="")
  {$query="update compras.ordenes_pagos set id_ingreso_egreso=null where id_pago=$id_pago";
   //guardamos la info del pago en la variable que saldrá en el mail
   $pagos_afectados.="Pago realizado el ".fecha($pagos->fields["fecha"])." al contado por $moneda".formato_money($pagos->fields["monto"]).". ";
   if($pagos->fields["valor_dolar"]!="" && $pagos->fields["valor_dolar"]!=0)
    $pagos_afectados.="Valor de Dolar utilizado: ".$pagos->fields["valor_dolar"].".";

   $pagos_afectados.="\n";
  }
  //idem sin tiene un débito
  elseif($pagos->fields['iddébito']!="")
  {$query="update compras.ordenes_pagos set idDébito=null where id_pago=$id_pago";
   //guardamos la info del pago en la variable que saldrá en el mail
   $pagos_afectados.="Pago realizado el ".fecha($pagos->fields["fecha"])." mediante Débito de $moneda".formato_money($pagos->fields["monto"]).". ";
   if($pagos->fields["valor_dolar"]!="" && $pagos->fields["valor_dolar"]!=0)
    $pagos_afectados.="Valor de Dolar utilizado: ".$pagos->fields["valor_dolar"].".";

   $pagos_afectados.="\n";
  }
  //idem si tiene un cheque
  elseif($pagos->fields['númeroch']!="")
  {$query="update compras.ordenes_pagos set NúmeroCh=null,IdBanco=null where id_pago=$id_pago";

   //guardamos la info del pago en la variable que saldrá en el mail
   $pagos_afectados.="Pago realizado el ".fecha($pagos->fields["fecha"])." con cheque a ".$pagos->fields["dias"]." días, Nº ".$pagos->fields["númeroch"]." por $moneda".formato_money($pagos->fields["monto"]).". ";
   if($pagos->fields["valor_dolar"]!="" && $pagos->fields["valor_dolar"]!=0)
    $pagos_afectados.="Valor de Dolar utilizado: ".$pagos->fields["valor_dolar"].".";

   $pagos_afectados.="\n";
  }
  //ejecutamos el query que va a desatar el pago
  sql($query,"<br>Error al despagar la OC - update de pagos con el id $id_pago<br>") or fin_pagina();
  $pagos->MoveNext();
 }

 //traemos todas las OC atadas a $nro_orden para volverlas todas a enviada, porque estan atadas
 //al mismo pago que $nro_orden
 $oc=PM_ordenes($nro_orden);
 $ordenes_text="";
 for($g=0;$g<sizeof($oc);$g++)
 {
  $query="update compras.orden_de_compra set estado='e' where nro_orden=".$oc[$g];
  sql($query,"<br>Error al actualizar estado de la OC<br>") or fin_pagina();

  //registramos el log del despago de la OC
  $f1=date("Y-m-d H:i:s",mktime());
  $q="insert into compras.log_ordenes (nro_orden,tipo_log,user_login,fecha) values (".$oc[$g].",'en que se deshizo el pago','".$_ses_user[login]."','$f1')";
  sql($q,"Error en al insertar log de despago de la OC ".$oc[$g]) or fin_pagina();
  $ordenes_text.=$oc[$g]." ";
 }

 //las notas de creditos que hayan sido utilizadas para pagar la OC
 //se  vuelven a estado reservadas, y se sacan de estado utilizadas
 $query="select n_credito_orden.id_nota_credito
 		from compras.n_credito_orden where n_credito_orden.nro_orden=$nro_orden";
 $result=sql($query,"<br>Error al traer las notas de credito de la orden (despagar_oc)<br>") or fin_pagina();
 while(!$result->EOF)
 {$query="update general.nota_credito set estado=1 where id_nota_credito=".$result->fields['id_nota_credito']." and estado=2";
  sql($query,"<br>Error al actualizar estado de nota ".$result->fields['id_nota_credito']."(funcion pasa_nc_utilizada)") or fin_pagina();
  $result->MoveNext();
 }


 //enviamos mail avisando que se despagó la OC
 $para="corapi@coradir.com.ar,juanmanuel@coradir.com.ar,noelia@coradir.com.ar";

 if(sizeof($oc)==1)
 {
  $asunto="Se Deshizo el pago de la Orden de Compra: $nro_orden\n";
  $texto="La Orden de Compra $nro_orden se volvió a estado enviada.\n";
 }
 else
 {
  $asunto="Se Deshizo el pago de la Ordenes de Compra: $ordenes_text\n";
  $texto.="Las siguientes Ordenes de Compra se volvieron a estado enviada por formar parte de un mismo Pago Múltiple:\n$ordenes_text\n";
 }
 $texto.="Los pagos que se habian realizado se desataron de dicha/s Orden/es de Compra, pero quedaron cargados en el sistema.";
 $texto.="\n\nOJO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n";
 $texto.="ASEGURESE DE ANULAR LOS CHEQUES O DÉBITOS QUE SE HAYAN UTILIZADO EN ESTOS PAGOS, O DE RE-INGRESAR LOS MONTOS RESPECTIVOS EN LA CAJA CORRESPONDIENTE.\n";
 $texto.="\nOJO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n";
 $texto.="Pagos realizados:\n";
 $texto.=$pagos_afectados."\n";
 $texto.=detalle_orden($nro_orden,1);
 $texto.="\n\nUsuario que despagó la Orden de Compra: ".$_ses_user["name"]."\n";
 //echo $texto;
 enviar_mail($para,$asunto,$texto,"","","","");

 $msg="<center><b>El pago de la Orden de Compra $nro_orden se deshizo con éxito</b></center>";


 $db->CompleteTrans();
}//de function despagar_oc($nro_orden,$moneda)


/********************************************************
Esta función elimina la fila pasada como parametro.
Si el proveedor de la OC es un stock, tambien cancela la
reservas vigentes para esa fila.
*********************************************************/
function eliminar_fila_autorizada($id_fila,$nro_orden,$id_prov_oc)
{global $db,$msg,$_ses_user;
 $db->StartTrans();
 //primero vemos si esa fila tiene entregas o recepciones
 $query="select recibido_entregado.id_recibido,fila.id_producto,fila.cantidad,fila.descripcion_prod,fila.desc_adic,
 		fila.precio_unitario,fila.es_agregado,recibido_entregado.cantidad as cant_recib
         from compras.fila left join compras.recibido_entregado using(id_fila)
         where fila.id_fila=$id_fila";
 $info_fila=sql($query,"<br>Error al buscar info para la fila Nº $id_fila (eliminar_fila_autorizada)<br>") or fin_pagina();

 $nombre_producto=$info_fila->fields["descripcion_prod"]." ".$info_fila->fields["desc_adic"];
 $cantidad=$info_fila->fields["cantidad"];
 $cantidad_mail=$cantidad;
 $es_agregado=$info_fila->fields["es_agregado"];
 $fecha=date("Y-m-d H:i:s",mktime());

 //si tiene entregas o recpciones, damos mensaje de que no se puede borrar por esta razón
 if($info_fila->fields["id_recibido"]!="" && $info_fila->fields["cant_recib"]>0)
 {$msg="<b>Para la OC $nro_orden, no se puede borrar la fila con el producto <br>'$nombre_producto'<br><font color='red'>Existen entregas o recepciones para esa fila</font><b>";
 }
 //si no tiene entregas o recepciones, revisamos el tipo del proveedor. Si es stock debemos cancelar las reservas
 //hechas por esta fila.
 else
 {//vemos el tipo de proveedor. si es stock, hacemos la cancelacion de la reserva hecha por es fila
  $query="select proveedor.razon_social from general.proveedor where id_proveedor=$id_prov_oc";
  $prov=sql($query,"<br>Error al traer el nombre del proveedor (eliminar_fila_autorizada)<br>") or fin_pagina();

  //si la fila en cuestion es un agregado, debemos volver a cero, el campo 'transporte_agregado'
  //para que puedan volver a agregar conexo si desean
  if($es_agregado)
  {$query="update compras.orden_de_compra set transporte_agregado=0 where nro_orden=$nro_orden";
   sql($query,"<br>Error al acutalizar transporte agregado<br>") or fin_pagina();
  }

  //eliminamos las entradas de la tabla recibido_entregado, que se agregan aunque no se reciba nada (o la cantidad recibida es cero)
  $query="delete from compras.recibido_entregado where id_fila=$id_fila";
  sql($query,"<br>Error al eliminar de recibido y entregados<br>") or fin_pagina();

  //Eliminamos la fila
  $query="delete from compras.fila where id_fila=$id_fila";
  sql($query,"<br>Error al eliminar la fila (eliminar_fila_autorizada)<br>") or fin_pagina();

  /*****************************************************************************************/
  //enviamos el mail avisando que se borro la fila y que debe revisar la forma de pago
  //traemos estado y moneda de la OC, para poder enviar el mail correctamente
  $query="select orden_de_compra.estado,moneda.simbolo
  		  from compras.orden_de_compra left join licitaciones.moneda using(id_moneda)
  		  where nro_orden=$nro_orden";
  $info_oc=sql($query,"<br>Error al traer info de OC (eliminar_fila_autorizada)<br>") or fin_pagina();
  $simbolo_moneda=$info_oc->fields["simbolo"];

  switch($info_oc->fields["estado"])
  {case 'a':$estado_oc="Autorizada";break;
   case 'e':$estado_oc="Enviada";break;
   case 'd':$estado_oc="Parcialmente Pagada";break;
   case 'g':$estado_oc="Totalmente Pagada";break;
  }

  $para="juanmanuel@coradir.com.ar";
  $asunto="Para la OC Nº $nro_orden, que esta en estado '$estado_oc', se eliminó una fila.";
  $texto="Para la Orden de Compra $nro_orden, que está en estado '$estado_oc', se eliminó la siguiente fila:\n";
  $texto.="\nCantidad - Descripción - Precio unitario - Precio Total\n";
  $texto.="-------------------------------------------------------\n";
  $texto.="$cantidad_mail - $nombre_producto - $simbolo_moneda ".formato_money($info_fila->fields["precio_unitario"])." - $simbolo_moneda ".formato_money($info_fila->fields["precio_unitario"]*$cantidad_mail);
  $texto.="\n-------------------------------------------------------\n";
  if($sub_texto!="")
   $texto.="\n$sub_texto";
  $texto.="\n\n--------------------------------------------------------------------------------";
  $texto.="\nATENCION: Debido a que se eliminó una fila, el monto total de la OC ha cambiado.";
  $texto.="\nPor favor revise la forma de pago para la OC $nro_orden";
  $texto.="\n--------------------------------------------------------------------------------";
  $texto.="\n\nUsuario que realizó esta operación: ".$_ses_user["name"];
  $texto.="\nFecha: ".Fecha($fecha);
  enviar_mail($para,$asunto,$texto,'','','','');

  $msg="<b>La fila con el producto <br>'$nombre_producto'<br> se eliminó con éxito, para la OC Nº $nro_orden<b>";
 }//de if($rec->RecordCount()>0)

 $db->CompleteTrans();
}//de function eliminar_fila_autorizada($id_fila,$nro_orden,$id_proveedor)


/********************************************************
Esta función actualiza la tabla fila con las
modificaciones post-autorización, que agrega el usuario.
*********************************************************/
function cambiar_fila_especial($cant_filas,$internacional=0)
{global $db,$msg,$nro_orden,$_ses_user;
 $db->StartTrans();

 //traemos el estado de la OC, para poder enviar el mail correctamente
 $query="select orden_de_compra.estado,moneda.simbolo
 		 from compras.orden_de_compra left join licitaciones.moneda using(id_moneda)
 		 where nro_orden=$nro_orden";
 $info_oc=sql($query,"<br>Error al traer info de OC (eliminar_fila_autorizada)<br>") or fin_pagina();

 $simbolo=$info_oc->fields["simbolo"];

 //arreglo para guardar la info de las filas modifcadas para enviarlo por mail
 $info_mail=array();

 //por cada fila, actualizamos las cantidades y precios que hayan modificado
 for($i=0;$i<$cant_filas && $msg=="";$i++)
 {$id_fila=$_POST["idf_$i"];
  $cantidad=$_POST["cant_$i"];
  $precio_u=$_POST["unitario_$i"];
  if($internacional)
  {$id_posad=$_POST["select_posad_$i"];
   $proporcional_flete=$_POST["proporcional_flete_$i"];
   $base_imponible_cif=$_POST["base_imponible_cif_$i"];
   $derechos=$_POST["derechos_$i"];
   $iva=$_POST["iva_$i"];
   $ib=$_POST["ib_$i"];
  }

  //primero vemos si esa fila tiene entregas o recepciones
  $query="select recibido_entregado.id_recibido,recibido_entregado.cantidad as cantidad_recibida,fila.id_producto,fila.cantidad,
  		  fila.descripcion_prod,fila.desc_adic,fila.precio_unitario,fila.id_posad
          from compras.fila left join compras.recibido_entregado using(id_fila)
          where fila.id_fila=$id_fila";
  $info_fila=sql($query,"<br>Error al buscar info para la fila Nº $id_fila (cambiar_fila_especial)<br>") or fin_pagina();

  $nombre_producto=$info_fila->fields["descripcion_prod"]." ".$info_fila->fields["desc_adic"];

  $no_modificar=0;
  //si tiene recpciones, y si la nueva cantidad de la fila es mayor o igual que la cantidad recibida no hay problemas
  //pero si es menor, no se puede cambiar la cantidad y damos cartel de error
  if($info_fila->fields["id_recibido"]!="" && $info_fila->fields["cantidad_recibida"]>$cantidad )
  {
   	$msg="<b>Para la OC $nro_orden, no se puede cambiar la cantidad de la fila con el producto <br>'$nombre_producto'<br><font color='red'>La cantidad ingresada ($cantidad) es menor que la que ya se ha entregado (".$info_fila->fields["cantidad_recibida"].")</font><b>";
   	$no_modificar=1;
  }//de else if($info_fila->fields["id_recibido"]!="" && $info_fila->fields["cantidad_recibida"]>$cantidad )

  if($no_modificar==0 && ($info_fila->fields["cantidad"]!=$cantidad ||$info_fila->fields["precio_unitario"]!=$precio_u||$info_fila->fields["id_posad"]!=$id_posad))
  {
   $query="update compras.fila set cantidad=$cantidad, precio_unitario=$precio_u";

   $cambio_posad="";
   if($internacional)
   {
   	$query.=",id_posad=$id_posad,proporcional_flete=$proporcional_flete,base_imponible_cif=$base_imponible_cif,derechos=$derechos,iva=$iva,ib=$ib ";

   	$cambio_posad=" (Se cambió el POSAD de esta fila)";
   }
   $query.="where id_fila=$id_fila";

   sql($query,"<br>Error al actualizar la fila $i con id $id_fila<br>") or fin_pagina();

   //si la OC proviene de un presupuesto de licitacion, modificamos la cantidad tambien en la tabla oc_pp
   //(si la OC no es de este tipo esta consulta no afecta ninguna fila)
   $query="update compras.oc_pp set cantidad_oc=$cantidad where id_fila=$id_fila";
   sql($query,"<br>Error al actualizar la cantidad del presupuesto<br>") or fin_pagina();

   //Guardamos la info de la fila que se está modificando para enviarla por mail
   $info_mail[sizeof($info_mail)]="\n".$info_fila->fields["descripcion_prod"]." ".$info_fila->fields["desc_adic"]." - ".$info_fila->fields["cantidad"]." - $cantidad - $simbolo".number_format($info_fila->fields["cantidad"]*$info_fila->fields["precio_unitario"],2,'.','')." - $simbolo".number_format($cantidad*$precio_u,2,'.','')."$cambio_posad";

  }//de if($no_modificar==0 && $info_fila->fields["cantidad"]!=$cantidad ||$info_fila->fields["precio_unitario"]!=$precio_u||$info_fila->fields["id_posad"])

 }//de for($i=0;$i<$items["cantidad"];$i)

 /*************************************************************************/
 //Si Alguna fila fue modificada, enviamos un mail avisando de este hecho
 if(sizeof($info_mail)>0)
 {

  switch($info_oc->fields["estado"])
  {case 'a':$estado_oc="Autorizada";break;
   case 'e':$estado_oc="Enviada";break;
   case 'd':$estado_oc="Parcialmente Pagada";break;
   case 'g':$estado_oc="Totalmente Pagada";break;
  }
  $fecha=date("Y-m-d H:i:s",mktime());

  if($internacional)
   $texto_int="Internacional";
  else
   $texto_int="";

  $para="juanmanuel@coradir.com.ar,marco@coradir.com.ar";
  $asunto="Para la OC $texto_int Nº $nro_orden, que esta en estado '$estado_oc', se cambiaron datos de algunas filas.";
  $texto="Para la Orden de Compra  $texto_int Nº $nro_orden, que está en estado '$estado_oc', se cambiaron datos de algunas filas.\n\n";
  $texto.=detalle_orden($nro_orden,0)."\n\n";
  $texto.="\n-------------------------------------------------------------------------------------";
  $texto.="\nATENCION: Debido a que se cambió la cantidad o el precio de algunas filas, el monto total de la OC puede haber cambiado.";
  $texto.="\nPor favor revise la forma de pago para la OC  $texto_int Nº $nro_orden";
  $texto.="\nLas filas afectadas son las siguientes:";
  $texto.="\n-------------------------------------------------------------------------------------";
  $texto.="\nProducto - Cantidad Anterior - Nueva Cantidad - Precio Total Anterior - Nuevo Precio Total";
  $texto.="\n-------------------------------------------------------------------------------------";

  for($ty=0;$ty<sizeof($info_mail);$ty++)
  {
  	$texto.=$info_mail[$ty];
  }

  $texto.="\n\nUsuario que realizó esta operación: ".$_ses_user["name"];
  $texto.="\nFecha: ".Fecha($fecha)."\n\n\n";
  enviar_mail($para,$asunto,$texto,'','','','');
  //echo $texto;
  $msg="<b>Los cambios en las filas de la OC Nº $nro_orden, se realizaron con éxito</b>";
 }//de if($msg=="")
 elseif($msg=="")
  $msg="<b>No se registraron cambios en las filas de la OC Nº $nro_orden.</b>";

 $db->CompleteTrans();
}//de function cambiar_fila_especial()


/***************************************************************************************************
FUNCIONES PARA DES-RECIBIR/DES-ENTREGAR PRODUCTOS DE UNA OC

/************************************************************
Funcion que libera o borra los codigos de barra que se hayan
recibido y/o entregado con una OC pasada como parametro,
para el id de producto dado, tambien como parametro.

Lo que esta funcion hace es buscar todos los CB del producto
pasado como parametro que esten relacionados con la OC pasada
ya sea por entrega o recepcion del mismo. Luego desvincula
ambos, borrando todas las entradas de log_codigo_barra (de
ingreso o egreso del producto, al sistema). Asi, el CB
quedara como que nunca se recibio/entrego mediante la OC que
se paso como parametro. A parte de esto, si el CB entro al
sistema y salio con la misma OC, es eliminado del sistema
*************************************************************/
function liberar_cbs_de_fila($nro_orden,$id_prod_esp)
{
 global $db;
 $db->StartTrans();

 //primero traemos los nros de codigos de barra (CB) relacionados a esta fila, para la OC $nro_orden, con sus respectivos logs
 $query="select codigos_barra.codigo_barra,log_codigos_barra.tipo,log_codigos_barra.nro_orden
 		 from general.codigos_barra
         join general.log_codigos_barra using(codigo_barra)
         where codigos_barra.id_prod_esp=$id_prod_esp and log_codigos_barra.nro_orden=$nro_orden
         order by codigos_barra.codigo_barra
         ";
 $cb_info=sql($query,"<br>Error al traer info de los codigos de barra<br>") or fin_pagina();

 //armamos el arreglo con los cb, poniendoel nro de cb como indice del arreglo, y en su contenido,
 // un 1 si el cb ingreso al sistema con la OC $nro_orden, y un 0 si no lo hizo
 $cb_a_borrar=array();

 //por cada CB encontrado
 while (!$cb_info->EOF)
 {//vemos cual es el origen del mismo
  $cb_actual=$cb_info->fields["codigo_barra"];
  $cb_tipo_log=$cb_info->fields["tipo"];

  //si el cb ingreso al sistema por la OC $nro_orden, entonces encontraremos seguro en el log de cb, una entrada
  //del con tipo="Producto Ingresado mediante la OC Nº $nro_orden". Entonces buscamos en el campo tipo que trajimos,
  //la palabra "Ingresado", lo que asegura que el CB entro por la orden que estamos tratando.
  if(substr_count($cb_tipo_log,"Ingresado")>0)
  {
  	//indicamos con 1 que el cb fue ingresado por la OC $nro_orden
  	$cb_a_borrar[$cb_actual]=1;
  }
  //si el cb no esta usado por la OC, entonces en el arreglo, la entrada $cb_a_borrar[$cb_actual] del mismo estara vacia
  elseif($cb_a_borrar[$cb_actual]=="")
  {
   //ponemos 0 para indicar que el cb esta usado por la OC $nro_orden (lo agregamos al arreglo)
   $cb_a_borrar[$cb_actual]=0;
  }

  $cb_info->MoveNext();
 }//de while(!$cb_info->EOF)

 /*****************************************************************************************************************************
 ******************************************************************************************************************************
 Recorremos el arreglo de codigos de barra que obtuvimos y borramos o bien solo el log de entrega de este cb, si el campo
 del arreglo esta en 0 (porque no ingreso al sistema por esta OC), o bien borramos todo el log del cb, y el cb mismo, si el
 ingreso y el egreso fueron hecho por esta mismoa OC $nro_orden. Si el egreso del CB fue hecho por otra OC, pero ingreso por
 la OC pasada como parametro, no borrarmos nada de nada y retornamos el codigo de barras que trajo el conflicto. retornamos 0 si
 todo anduvo perfecto.
 *****************************************************************************************************************************
 ******************************************************************************************************************************/
 //string que contendra los cb a borrar del sistema
 $a_borrar_cb="";
 //string que contendra los cb de los que solo se borrar el log de entrega
 $a_borrar_log_entrega="";
 $cb_info->Move(0);$first_cb=1;$first_log_cb=1;
 while (!$cb_info->EOF)
 {
  $cb_actual=$cb_info->fields["codigo_barra"];
  //si el cb no ingreso por esta OC (valor=0), solo le borramos la entrada de entrega en la tabla log_codigos_barra
  if($cb_a_borrar[$cb_actual]==0)
  {
   if(!$first_log_cb)
    $a_borrar_log_entrega.=",";
   else
    $first_log_cb=0;

   $a_borrar_log_entrega.="'".$cb_info->fields["codigo_barra"]."'";
  }//de if($cb_a_borrar[$cb_actual]==0)
  elseif($cb_a_borrar[$cb_actual]==1)
  {
   //como el CB se ingreso por la OC pasada como parametro ($nro_orden), debemos asegurarnos que si hay una entrega de este CB,
   //se haya realizado solo por la OC $nro_orden. Si en cambio el CB se entrego en otra OC, no se puede elminar, en cuyo caso
   //retornamos el nro de CB que estamos consultando, para indicar que no se pudo borrar del sistema porque ingreso por la OC
   //pasada como parametro pero no egreso por esta, sino por otra OC diferente.
   $query="select log_codigos_barra.nro_orden from general.log_codigos_barra where log_codigos_barra.codigo_barra='$cb_actual' and log_codigos_barra.tipo ilike '%entregado%' ";
   $control_entrega=sql($query,"<br>Error al consultar la entrega del cb Nº $cb_actual<br>") or fin_pagina();
   if($control_entrega->fields["nro_orden"]!="" && $control_entrega->fields["nro_orden"]!=$nro_orden)
   {
   	 $cb_mal["cb"]=$cb_actual;
   	 $cb_mal["OC"]=$control_entrega->fields["nro_orden"];
   	 return $cb_mal;
   }
   else
   {
   	if(!$first_cb)
  	 $a_borrar_cb.=",";
  	else
  	 $first_cb=0;

    $a_borrar_cb.="'".$cb_info->fields["codigo_barra"]."'";
   }
  }//de elseif($cb_a_borrar[$cb_actual]==1)
  else
  {
  	//SI ENTRA POR ESTE ELSE, HAY ALGO QUE NO ESTA BIEN PROGRAMADO PORQUE SIEMPRE TIENE QUE SER O UN CERO O UN UNO EL VALOR
  	//QUE GUARDE EN EL ARREGLO (EN EL WHILE ANTERIOR). NUNCA DEBERIA APARECER ESTE PROBLEMA!!
  	 die("<font color='red'><b><br>ERROR INESPERADO: Codigo 710 <br> Por favor contacte a la Divión Software.");
  }

  $cb_info->MoveNext();
 }//de while(!$cb_info->EOF)

 //a continuacion, como todo anduvo correctamente, eliminamos aquellos logs de los cb que solo fueron entregados por la OC
 //pasada como parametro ($nro_orden), pero que no ingresaron al sistema por dicha OC
 if($a_borrar_log_entrega!="")
 {
  $query="delete from general.log_codigos_barra where codigo_barra in($a_borrar_log_entrega) and tipo ilike '%entregado%'";
  sql($query,"<br>Error al borrar logs de CB entregados pero no recibidos<br>") or fin_pagina();
 }

 //luego borramos los CB (y sus logs) que ingresaron por la OC pasada como parametro y pudieron haber salido por la misma,
 //o aun no fueron entregados (Recordar que si un CB ingreso por esta OC pero se entrego en otra, ya hubieramos retornado ese CB,
 //para indicar este hecho, y no estariamos a esta altura de la funcion).
 if($a_borrar_cb!="")
 {$query="delete from general.log_codigos_barra where codigo_barra in($a_borrar_cb)";
  sql($query,"<br>Error al borrar logs de CB ingresados mediante esta OC (logs)<br>") or fin_pagina();

  $query="delete from compras.adicional_recepcion where codigo_barra in($a_borrar_cb)";
  sql($query,"<br>Error al eliminar codigos de barra de la tabla de adicional recepción<br>") or fin_pagina();

   $query="delete from general.codigos_barra where codigo_barra in($a_borrar_cb)";
   sql($query,"<br>Error al borrar CB ingresados mediante esta OC<br>") or fin_pagina();
 }

 $db->CompleteTrans();
 //con "ok" indicamos que todo anduvo OK. Si no anduvo OK, se habra devuelto el CB que trajo conflictos
 return "ok";

}//de function liberar_cbs_de_fila($nro_orden,$id_producto)


/************************************************************************
Funcion que vuelve para atrás la recepción y entrega de los productos de
una fila que es pasada como parámetro. Esto implica que se eliminan los
productos que se ingresaron a stock disponible o a stock reservado.


@id_fila       la fila a des_recibir o des-entregar
@enviar_mail   Por default, envia el mail avisando que se des-recibio y/o
               des-entrego la fila $id_fila. Si este parametro es 0, no
               envia ese mail.
@de_disponible Indica con 0(el valor por default) que el descuento de los
			   productos desde stock deben restarse de la cantidad
			   reservada. Si este parametro esta en 1, la resta se hara
			   desde la cantidad disponible, solo si hay suficiente
			   stock disponible para descontar.
*************************************************************************/
function des_recibir_fila($id_fila,$enviar_mail=1,$de_disponible=0)
{
 include_once("../stock/funciones.php");

 global $db,$msg,$_ses_user;
 $db->StartTrans();

    /************************************************************************************
	 Para des-recibir una fila lo primero que hacemos es eliminar todas las recepciones
	 que no fueron confirmadas
 	*************************************************************************************/

    //traemos el tipo de movimiento que indica el rechazo de recepcion: Rechazo de Recepción de productos para una OC
	$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Rechazo de Recepción de productos para una OC'";
	$id_tm_rech=sql($query,"<br>Error al traer el tipo de movimiento para rechazo de recepciones<br>") or fin_pagina();
	if($id_tm_rech->fields["id_tipo_movimiento"])
		$id_tipo_movimiento=$id_tm_rech->fields["id_tipo_movimiento"];
	else
		die("Error Interno DROC544: No se pudo determinar el tipo de movimiento a realizar");


	//traemos el log de las recepciones del producto de la fila
   $query="select log_rec_ent.id_log_recibido,log_rec_ent.id_prod_esp,log_rec_ent.usuario,log_rec_ent.fecha,log_rec_ent.cant,
   			log_rec_ent.desde_stock,log_rec_ent.recepcion_confirmada,recibido_entregado.id_recibido,
   			recibido_entregado.id_deposito,fila.nro_orden
         from compras.log_rec_ent
         	  join compras.recibido_entregado using (id_recibido)
              join compras.fila using(id_fila)
         where id_fila=$id_fila and recepcion_confirmada=0";
   $log_recibidos=sql($query,"<br>Error al traer el registro de las recepciones de productos<br>") or fin_pagina();

   $id_recibido=$log_recibidos->fields["id_recibido"];

   while (!$log_recibidos->EOF)
   {
   		$cant=$log_recibidos->fields["cant"];
   		$id_log_recibido=$log_recibidos->fields["id_log_recibido"];
   		$id_prod_recibido=$log_recibidos->fields["id_prod_esp"];
   		$id_deposito=$log_recibidos->fields["id_deposito"];
   		$nro_orden=$log_recibidos->fields["nro_orden"];

   		 //eliminamos el log seleccionado y reducimos la cantidad recibida, en la tabla recibido_entregado
         $query="delete from compras.log_rec_ent where id_log_recibido=$id_log_recibido";
         sql($query,"<br>Error al eliminar la recepción elegida con id log: $id_log_recibido<br>") or fin_pagina();

         $query="update compras.recibido_entregado set cantidad=cantidad-$cant where id_recibido=$id_recibido";
         sql($query,"<br>Error al actualizar la cantidad recibida de la fila<br>") or fin_pagina();

         $comentario="Se des-recibieron los productos para la OC Nº $nro_orden";
         //descontamos los productos que se subieron al stock a confirmar, porque se rechazo la recepcion
         descontar_a_confirmar($id_prod_recibido,$cant,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila);

    	$log_recibidos->MoveNext();
   }//de while(!$log_recibidos->EOF)



 /************************************************************
 *************************************************************
  Recopilamos datos de la fila
 *************************************************************
 *************************************************************/
 //traemos los datos de la OC a la que pertenece esa fila, y los datos necesarios de la fila
 $query="select orden_de_compra.nro_orden,orden_de_compra.id_proveedor,proveedor.razon_social,
 		 fila.id_producto,fila.cantidad,fila.descripcion_prod,fila.desc_adic
         from compras.orden_de_compra join general.proveedor using(id_proveedor)
         join compras.fila using(nro_orden) where fila.id_fila=$id_fila";
 $info_fila=sql($query,"<br>Error al traer info de la fila Nº $id_fila<br>") or fin_pagina();

 $id_producto=$info_fila->fields["id_producto"];
 $nro_orden=$info_fila->fields["nro_orden"];
 $prod=$info_fila->fields["descripcion_prod"]." ".$info_fila->fields["desc_adic"];

 $es_stock=0;

 /************************************************************
  *************************************************************
   Recopilamos datos de recepciones y entregas de la fila
  *************************************************************
  *************************************************************/

  //Traemos las entradas de  recepciones y entregas que tiene la fila, en la tabla recibido_entregado, en combinacion con
  //la tabla log_rec_ent, para poder saber cuales productos especificos se recibieron
  $query="select sum(log_rec_ent.cant) as cant,recibido_entregado.id_deposito,recibido_entregado.ent_rec,log_rec_ent.id_prod_esp
         from compras.recibido_entregado join compras.log_rec_ent using(id_recibido)
         where recibido_entregado.id_fila=$id_fila
         group by log_rec_ent.id_prod_esp,recibido_entregado.id_deposito,recibido_entregado.ent_rec
         ";
  $recibidos=sql($query,"<br>Error al traer entradas de recepcion/entrega, para la fila Nº $id_fila<br>") or fin_pagina();

 $msg="<center><b>Para la OC $nro_orden, la fila con el producto '$prod' se des-recibió con éxito</b></center>";
 if(!$es_stock)// si el proveedor no es un stock
 {
  /************************************************************
  *************************************************************
   Eliminamos las entradas en las tablas de log de movimientos
   de stock.
   Y actualizamos las reservas del stock, de ser necesario
  *************************************************************
  *************************************************************/

  //por cada log de recepcion, eliminamos la entrada en el log_movimiento_stock, y lo descontamos de la cantidad reservada
  //o la cantidad disponible, dependiendo del parametro: $de_disponible
  $recibidos->Move(0);
  $error_cant_disp=0;
  while ($error_cant_disp==0 && !$recibidos->EOF)
  {

   $id_prod_esp=$recibidos->fields["id_prod_esp"];
   $id_deposito=$recibidos->fields["id_deposito"];
   $cantidad_reservada=$recibidos->fields["cant"];

   //si el descuento de los productos debe hacerse desde la cantidad disponible del stock
   if($de_disponible==1)
   {
   	//revisamos que haya suficiente cantidad disponible para sacar del stock (porque los productos se estan des-recibiendo)
	 $query="select en_stock.cant_disp,en_stock.id_en_stock from stock.en_stock
	 		 where en_stock.id_deposito=$id_deposito and en_stock.id_prod_esp=$id_prod_esp";
	 $hay_disp=sql($query,"<br>Error al consultar si hay stock disponible para des-recibir una fila de OC de Stock<br>") or fin_pagina();

	 $id_en_stock=$hay_disp->fields["id_en_stock"];

	 //si no hay suficiente cantidad, damos el error y no ejecutamos mas nada en esta funcion
	 if($hay_disp->fields["cant_disp"]<$cantidad_reservada)
	 {
	 	$msg="<center><b>La fila con el producto '$prod' no se puede des-recibir.<br>La cantidad disponible en Stock actualmente es menor que la cantidad que se recibió</center></b>";
	 	$error_cant_disp=1;
	 }

   }//de if($de_disponible==1)
   else
   {
   	  //controlamos que haya reservas para la fila pasada por parametro, y que la cantidad reservada sea la misma que figura
	  //en la cantidad de la fila. De lo contrario no se puede des-recibir la fila, porque seguramente esa reserva se uso
	  //para un PM
	  $query="select detalle_reserva.cantidad_reservada from stock.detalle_reserva where detalle_reserva.id_fila=$id_fila";
	  $res_atada=sql($query,"<br>Error al consultar por las reservas atadas a la fila que se intenta des-recibir<br>") or fin_pagina();
	  if($res_atada->fields["cantidad_reservada"]=="" || $res_atada->fields["cantidad_reservada"]!=$cantidad_reservada)
	  {
	  	$msg="<center><b>La fila con el producto '$prod' no se puede des-recibir.<br>La reserva generada para esta fila no existe más o su cantidad es distinta a la original.<br>Probablemente se haya realizado un Pedido de Material para esa reserva.</center></b>";
	 	$error_cant_disp=1;
	  }
   }//del else de if($de_disponible==1)

   if($error_cant_disp==0)
   {
	     /************************************************************
		 *************************************************************
		  Liberamos los Codigos de barra usados en la entrega y/o
		  recepcion de los productos para la fila $id_fila
		 *************************************************************
		 *************************************************************/
		  $liberar=liberar_cbs_de_fila($nro_orden,$id_prod_esp);

		  if($liberar!="ok")
		  {
		   $msg="<center><b>La fila con el producto '$prod' no se puede des-recibir.<br>El código de barras '".$liberar["cb"]."' fue ingresado al sistema mediante la OC $nro_orden pero se entrego mediante la OC";
		   $msg.="<a target=_blank href='".encode_link("ord_compra.php",array("nro_orden"=>$liberar["OC"]))."'> ".$liberar["OC"]."</b></center>";
		  }

	    /*//eliminamos todas las entradas correspondientes de la tabla log_movimientos_stock
	    $query="delete from stock.log_movimientos_stock
	            where comentario ilike '%O%C%$nro_orden%'
	                  and id_en_stock in(select id_en_stock from en_stock
	                                     where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito
	                                    )
	           ";
	    sql($query,"<br>Error al eliminar el log de movimiento de stock<br>") or fin_pagina();*/
	   if($de_disponible==0)
	   {
	   	//traemos el id de movimiento de stock: "Des-recepción de productos reservados para una fila de OC"
	   	$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Des-recepción de productos reservados para una fila de OC'";
	   	$id_tm=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
	   	if($id_tm->fields["id_tipo_movimiento"])
	   		$id_tipo_movimiento=$id_tm->fields["id_tipo_movimiento"];
	   	else
	   		die("Error Interno DROC703: No se pudo determinar el tipo de movimiento de stock. Contactese con la División Software.");

	   	//eliminamos las reservas generadas por la fila y agregamos el log del movimiento correspondiente
	   	//(todo a traves de la funcion)
	   	$comentario="Se eliminó del stock la reserva de los productos porque se des-recibió una fila de la OC Nº $nro_orden";
	    descontar_reserva($id_prod_esp,$cantidad_reservada,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila);

	   }//de if($de_disponible==1)
       else //descontamos del stock disponible la cantidad recibida previamente,ya que hay cantidad suficiente
	   {
		   	//traemos el id de movimiento de stock: "Descuento por des-recepción de una fila de OC que había agregado productos a Stock disponible"
		   	$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Descuento por des-recepción de una fila de OC que había agregado productos a Stock disponible'";
		   	$id_tm_disp=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
		   	if($id_tm_disp->fields["id_tipo_movimiento"])
		   		$id_tipo_movimiento=$id_tm_disp->fields["id_tipo_movimiento"];
		   	else
		   		die("Error Interno DROC718: No se pudo determinar el tipo de movimiento de stock. Contactese con la División Software.");

		 	//reducimos la cantidad de stock disponible, para eliminar los productos que se ingresaron
		 	//cuando se recibio la fila
		 	$comentario="Se eliminaron productos que habían ingresado a stock disponible mediante la OC Nº $nro_orden debido a que se des-recibió una fila de la misma";
			descontar_stock_disponible($id_prod_esp,$cantidad_reservada,$id_deposito,$id_tipo_movimiento,$comentario);

		}//del else de if($de_disponible==0)
    }//de if($error_cant_disp==0)

  	$recibidos->MoveNext();
  }//de while (!$recibidos->EOF)

  if($error_cant_disp==0)
  {

  	 /************************************************************
	  *************************************************************
	   Borramos todas las entradas referidas a $id_fila de las
	   tablas: recibido y log_recibido
	  *************************************************************
	  *************************************************************/

	  //eliminamos las entradas existentes de la tabla recibidos (y su log respectivo)
	  $query="delete from compras.adicional_recepcion
	          where id_log_recibido in(select id_log_recibido from log_rec_ent join recibido_entregado using(id_recibido)
	                                   where id_fila=$id_fila)";
	  sql($query,"<br>Error al eliminar los adicionales de recepcion<br>") or fin_pagina();

	  $query="delete from compras.log_rec_ent
	          where id_log_recibido in(select id_log_recibido from log_rec_ent join recibido_entregado using(id_recibido)
	                                   where id_fila=$id_fila)";
	  sql($query,"<br>Error al eliminar el log de recibidos<br>") or fin_pagina();

	  $query="delete from compras.recibido_entregado where id_fila=$id_fila";
	  sql($query,"<br>Error al borrar de recibidos<br>") or fin_pagina();

  }//de if($error_cant_disp==0)


 }//de if(!$es_stock)

 //si no hubo error por cantidad disponible, enviamos el mail y terminamos la transaccion de la BD
 if($error_cant_disp==0)
 {
	  //ponemos en cero el campo cant_scb_sr, en la tabla fila
	  $query="update compras.fila set cant_scb_sr=0 where id_fila=$id_fila";
	  sql($query,"<br>Error al actualizar el campo de entregar sin codigo de barra<br>") or fin_pagina();

	 if($enviar_mail)
	 {
	  //enviamos el mail avisando de hecho
	  //enviamos mail avisando que se despagó la OC
	  $para="juanmanuel@coradir.com.ar";

	  $asunto="Se volvió para atrás la recepción/entrega de una fila de la Orden de Compra: $nro_orden\n";
	  $texto="La fila de la OC Nº $nro_orden con el producto: $prod, fue \"des-recibida/des-entregada\".\n";
	  $texto.="---------------------------------------------------------------------------------------------\n\n";
	  $texto.=detalle_orden($nro_orden,1);
	  $texto.="\n\nUsuario: ".$_ses_user["name"]."\n";
	  $texto.="Fecha: ".date("d/m/Y H:i:s",mktime());
	  //echo $texto;
	  enviar_mail($para,$asunto,$texto,"","","","");
	 }//de if($enviar_mail)

	 $db->CompleteTrans();
 }//de if($error_cant_disp==0)
 else//sino, no podemos completar la sesion y forzamos el rollback
  $db->CompleteTrans(false);
}//de function des_recibir_fila($id_fila)


/*****************************************************************************************************
Función que des-recibe/des-entrega todas las filas de una OC pasada como parametro, haciendo uso
de las funciones existentes para des-recibir/des-entregar una unica fila de una OC.
******************************************************************************************************/
function des_recibir_oc($nro_orden)
{
 //traemos los id de fila de la OC que no son agregados, para ir des-recibiendolas/des_entregandolas una a una
 $query="select fila.id_fila from compras.fila where fila.nro_orden=$nro_orden and fila.es_agregado=0";
 $filas=sql($query,"<br>Error al traer las filas de la OC Nº $nro_orden<br>") or fin_pagina();

 //des-recibimos/des-entregamos cada fila de la OC
 while (!$filas->EOF)
 {
  des_recibir_fila($filas->fields["id_fila"],0);

  $filas->MoveNext();
 }//de while(!$filas->EOF)

}//de function des_recibir_oc($nro_orden)



/*
FUNCIONES PARA DES-RECIBIR/DES-ENTREGAR PRODUCTOS DE UNA OC
***************************************************************************************************/
?>