<?/*

----------------------------------------
 Autor: MAC
 Fecha: 20/10/2005
----------------------------------------

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.7 $
$Date: 2006/06/01 18:06:10 $
*/

include_once("../../config.php");

$db->StartTrans();
//traemos todos los egresos de caja hechos desde 01/07/2005, que no tienen imputacion
$query="select id_ingreso_egreso,caja.id_moneda,monto,caja.fecha,numero_cuenta
		from caja.ingreso_egreso join caja.caja using(id_caja)
		where caja.fecha>='2005-07-01' and id_tipo_egreso is not null and id_ingreso_egreso not in
		(select id_ingreso_egreso from contabilidad.imputacion where id_ingreso_egreso is not null)
		order by caja.fecha";
$caja=sql($query,"<br>Error al traer los egresos que no tienen imputacion<br>") or fin_pagina();

//traemos todos los cheques hechos desde 01/07/2005, que no tienen imputacion
$query="select idbanco,númeroch,importech,fechaemich,numero_cuenta
		from bancos.cheques
		where fechaemich>='2005-07-01' and (númeroch,idbanco) not in
		(select númeroch,idbanco from contabilidad.imputacion where númeroch is not null)
		order by fechaemich";
$cheques=sql($query,"<br>Error al traer los cheques que no tienen imputacion<br>") or fin_pagina();

//traemos todos los debitos hechos desde 01/07/2005, que no tienen imputacion
$query="select iddébito,importedéb,fechadébito,numero_cuenta
		from bancos.débitos
		where fechadébito>='2005-07-01' and iddébito not in
		(select iddébito from contabilidad.imputacion where iddébito is not null)
		order by fechadébito";
$debitos=sql($query,"<br>Error al traer los cheques que no tienen imputacion<br>") or fin_pagina();

 $estado="Pendiente";
 $titulo_estado=$estado;
 //traemos el id del estado
 $query="select id_estado_imputacion from estado_imputacion where nombre='$estado'";
 $estado_id=sql($query,"<br>Error al traer el id de estado de imputacion (parametro)<br>") or fin_pagina();
 $id_estado_imputacion=$estado_id->fields["id_estado_imputacion"];

 $fecha=date("Y-m-d",mktime());
$contador_pagos=0;
//por cada egreso de caja traido, insertamos una imputacion, en estado pendiente
while (!$caja->EOF)
{
 $titulo_pago="Egreso de Caja Nº ".$caja->fields["id_ingreso_egreso"];


 	if($caja->fields["id_moneda"]==2)//si es moneda dolar
 	{$valor_dolar=2.99;
 	 $monto_dolar=$caja->fields["monto"];
 	 $monto_total=$caja->fields["monto"];
 	}
 	else
 	{$valor_dolar=1;
 	 $monto_dolar="null";
 	 $monto_total=$caja->fields["monto"];
 	}
 	$numero_cuenta=$caja->fields["numero_cuenta"];

 	 //Insertamos la entrada en la tabla imputacion
 	$query="select nextval('imputacion_id_imputacion_seq') as id_imputacion";
 	$id=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
 	$id_imputacion=$id->fields["id_imputacion"];

 	$query="insert into imputacion (id_imputacion,id_ingreso_egreso,valor_dolar,fecha,id_estado_imputacion,monto_dolar)
 	        values($id_imputacion,".$caja->fields["id_ingreso_egreso"].",$valor_dolar,'".$caja->fields["fecha"]."',$id_estado_imputacion,$monto_dolar)";
 	sql($query,"<br>Error al insertar la imputacion<br>") or fin_pagina();

 	//registramos en el log la insercion de la imputacion
 	$query="insert into log_imputacion (tipo,detalle,fecha,usuario,id_imputacion)
 	        values('creación','Creación de la imputación para el pago con $titulo_pago, con estado: $titulo_estado','".$caja->fields["fecha"]."','Automático por Sistema',$id_imputacion)";
    sql($query,"<br>Error al insertar el log de imputacion<br>") or fin_pagina();

    //insertamos el detalle de la imputacion
    $query="insert into detalle_imputacion(id_imputacion,id_tipo_imputacion,usuario,fecha,monto,numero_cuenta)
  	 	  values($id_imputacion,7,'".$_ses_user['name']."','".$caja->fields["fecha"]."',$monto_total,$numero_cuenta)";
    sql($query,"<br>Error al insertar detalle de imputacion para monto_neto $titulo_pago<br>") or fin_pagina();

    $contador++;
 $caja->MoveNext();
}//de while(!$caja->EOF)


//por cada cheque traido, insertamos una imputacion, en estado pendiente
while (!$cheques->EOF)
{
 $titulo_pago="Cheque Nº ".$cheques->fields["númeroch"];
    //traemos el nombre del banco
    $query="select nombrebanco from tipo_banco where idbanco=".$cheques->fields["idbanco"];
    $bank=sql($query,"<br>Error al traer el nombre del banco<br>") or fin_pagina();
    $titulo_pago.=", del banco ".$bank->fields["nombrebanco"];

     $valor_dolar=1;
 	 $numero_cuenta=$cheques->fields["numero_cuenta"];
 	 $monto_total=$cheques->fields["importech"];

 	 //Insertamos la entrada en la tabla imputacion
 	$query="select nextval('imputacion_id_imputacion_seq') as id_imputacion";
 	$id=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
 	$id_imputacion=$id->fields["id_imputacion"];

 	$query="insert into imputacion (id_imputacion,númeroch,idbanco,valor_dolar,fecha,id_estado_imputacion)
 	        values($id_imputacion,".$cheques->fields["númeroch"].",".$cheques->fields["idbanco"].",$valor_dolar,'".$cheques->fields["fechaemich"]."',$id_estado_imputacion)";
 	sql($query,"<br>Error al insertar la imputacion<br>") or fin_pagina();

 	//registramos en el log la insercion de la imputacion
 	$query="insert into log_imputacion (tipo,detalle,fecha,usuario,id_imputacion)
 	        values('creación','Creación de la imputación para el pago con $titulo_pago, con estado: $titulo_estado','".$cheques->fields["fechaemich"]."','Automático por Sistema',$id_imputacion)";
    sql($query,"<br>Error al insertar el log de imputacion<br>") or fin_pagina();

    //insertamos el detalle de la imputacion
    $query="insert into detalle_imputacion(id_imputacion,id_tipo_imputacion,usuario,fecha,monto,numero_cuenta)
  	 	  values($id_imputacion,7,'".$_ses_user['name']."','".$cheques->fields["fechaemich"]."',$monto_total,$numero_cuenta)";
    sql($query,"<br>Error al insertar detalle de imputacion para ".$tipos_imputacion->fields["nombre"]."<br>") or fin_pagina();

    $contador++;
 $cheques->MoveNext();
}//de while(!$cheques->EOF)


//por cada cheque traido, insertamos una imputacion, en estado pendiente
while (!$debitos->EOF)
{

     $titulo_pago="Débito Nº ".$debitos->fields["iddébito"];
     $valor_dolar=1;
 	 $numero_cuenta=$debitos->fields["numero_cuenta"];
 	 $monto_total=$debitos->fields["importedéb"];

 	 //Insertamos la entrada en la tabla imputacion
 	$query="select nextval('imputacion_id_imputacion_seq') as id_imputacion";
 	$id=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
 	$id_imputacion=$id->fields["id_imputacion"];

 	$query="insert into imputacion (id_imputacion,iddébito,valor_dolar,fecha,id_estado_imputacion)
 	        values($id_imputacion,".$debitos->fields["iddébito"].",$valor_dolar,'".$debitos->fields["fechadébito"]."',$id_estado_imputacion)";
 	sql($query,"<br>Error al insertar la imputacion<br>") or fin_pagina();

 	//registramos en el log la insercion de la imputacion
 	$query="insert into log_imputacion (tipo,detalle,fecha,usuario,id_imputacion)
 	        values('creación','Creación de la imputación para el pago con $titulo_pago, con estado: $titulo_estado','".$debitos->fields["fechadébito"]."','Automático por Sistema',$id_imputacion)";
    sql($query,"<br>Error al insertar el log de imputacion<br>") or fin_pagina();

    //insertamos el detalle de la imputacion
    $query="insert into detalle_imputacion(id_imputacion,id_tipo_imputacion,usuario,fecha,monto,numero_cuenta)
  	 	  values($id_imputacion,7,'".$_ses_user['name']."','".$debitos->fields["fechadébito"]."',$monto_total,$numero_cuenta)";
    sql($query,"<br>Error al insertar detalle de imputacion para ".$tipos_imputacion->fields["nombre"]."<br>") or fin_pagina();

    $contador++;
 $debitos->MoveNext();
}//de while(!$debitos->EOF)


// $db->CompleteTrans();
echo "POR RAZONES DE SEGURIDAD LA TRANSACCION ABIERTA NO SE CERRO. NO SE REALIZO NINGUN CAMBIO EN LA BASE DE DATOS.";

 echo "<br><br>Operacion completada. Pagos afectados: $contador<br>";


 fin_pagina();
?>