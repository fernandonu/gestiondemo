<?
/*
AUTOR: fernando
MODIFICADO POR:
$Author: fernando $
$Revision: 1.2 $
$Date: 2006/08/17 15:00:15 $
*/
require_once("funciones_generales.php");
/*
Esta pagina la funcionalidad que tiene es la de pasar los datos del balance historial a una tabla de backup para que no sea tan 
grande los datos en el balance historial
*/
require_once ("funciones_generales.php");

$db->startrans();


$fecha = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-15,date("Y")));


//obtengo todas las fotos del balance de 15 dias atras
$sql = " select id_balance_historial,fecha from bancos.balance_historial 
       where fecha <= '$fecha'";
$res = $db->execute($sql) or die($db->errormsg()."<br>".$sql);   

 for ($i=0;$i<$res->recordcount();$i++){

     $id_balance_historial = $res->fields["id_balance_historial"];
	 //obtengo el detalle de las fotos
	 $sql = " select * from detalle_balance_historial where id_balance_historial=$id_balance_historial";
	 $detalle = $db->execute($sql) or die($db->errormsg()."<br>".$sql);   
	 
	 for ($y=0;$y<$detalle_recordcount();$y++){
	      
          $id_detalle_balance = $detalle->fields["id_detalle_balance"];
		  $sql = " select * from items_detalle_balance where id_detalle_balance=$id_detalle_balance";
		  $items = $db->execute($sql) or die($db->errormsg()."<br>".$sql);   
		  
		  for ($j=0;$j<$items->recordcount();$j++){
		         $id_items_detalle_balance =     $items->fields["id_items_detalle_balance"];
				 $id_detalle_balance_historial = $items->fields["id_detalle_balance_historial"];
				 $descripcion = $items->fields["descripcion"];
				 $monto =       $items->fields["monto"];
				 $cantidad =    $items->fields["cantidad"];
				 $moneda =      $items->fields["moneda"];
				 $id_licitacion = $items->fields["id_licitacion"];
				 $nro_factura = $items->fields["nro_factura"];
				 $id_cobranza = $items->fields["id_cobranza"];
				 $nro_orden =   $items->fields["nro_orden"];
				 
				 $campos = " id_items_detalle_balance,id_detalle_balance_historial";
				 $campos .= " ,descripcion,monto,cantidad,moneda,id_licitacion,nro_factura,id_cobranza,nro_orden";
				 
				 $values =" $id_items_detalle_balance,$id_detalle_balance_historial";
				 $values.=" ,'$descripcion',$monto,$cantidad,$moneda,$id_licitacion,$nro_factura,$id_cobranza,$nro_orden";
				 
				 $sql = " insert into ";
				 
				 
				 $items->movenext();
		         
		  }
	      
          $detalle->movenext();	 
	 }
	 
	 
	 
 
 $res->movenext();
 }	   
  
$db->completetrans();	   
?>
