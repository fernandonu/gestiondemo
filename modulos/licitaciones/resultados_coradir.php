<?
//iniciamos la transaccion
$db->StartTrans();

//traemos los datos de todos los renglones para la licitacion, necesarios
//para almacenar en la tabla oferta.
$query="select id_renglon,id_moneda,total,titulo from licitacion join renglon using(id_licitacion) where id_licitacion=".$parametros["ID"];
$res_lic=$db->Execute($query) or die($db->ErrorMsg().$query);

//seleccionamos el id del competidor CORADIR
$res_comp=$db->Execute("select id_competidor from competidores where nombre='".CORADIR."'") or die ($db->ErrorMsg()."competidor error");

while(!$res_lic->EOF)
{
 //insertamos el resultado para Coradir para cada renglon,
 //si no ha sido insertado antes controlando que no exista 
 //previamente la dupla (id_renglon,id_competidor)
 $control_result="select id_competidor from oferta where id_renglon=".$res_lic->fields['id_renglon'];
 $res_control=$db->Execute($control_result) or die($db->ErrorMsg().$control_result);
 $cant_control=$res_control->RecordCount();

 if($cant_control>0)
 {
  $res_control->Move(0);
  $insertar=0;
  while(!$res_control->EOF)
  {
   if($res_control->fields['id_competidor']!=$res_comp->fields['id_competidor'])
   {$insertar=1;
    break;
   } 
   $res_control->MoveNext();	
  }
 }
 else  
  $insertar=1;

 $exito=0;
 if($insertar)
 {
  $monto_uni=($res_lic->fields['total'])?$res_lic->fields['total']:0;
 $query="insert into licitaciones.oferta (id_renglon,id_competidor,id_moneda,ganada,observaciones,monto_unitario) values(".$res_lic->fields['id_renglon'].",".$res_comp->fields['id_competidor'].",".$res_lic->fields['id_moneda'].",'false','".$res_lic->fields['titulo']."',$monto_uni)";
 if($db->Execute($query) or die($db->ErrorMsg()."<br>".$query))
  $exito=1;
 }
 $res_lic->MoveNext(); 
}  	
//finalizamos la transaccion

$sql="update licitacion set resultados_cargados=1 where id_licitacion=".$parametros["ID"];
sql($sql) or fin_pagina();
$db->CompleteTrans();

//si hubo algun problema en las inserciones, damos error, sino damos mensaje de exito
if($exito)
 $msg = "Se agregaron exitosamente los resultados de Coradir para la licitacion ".$parametros["ID"];
else
 $msg = "No se pudieron agregar los resultados de Coradir para la licitacion ".$parametros["ID"];
 
?>