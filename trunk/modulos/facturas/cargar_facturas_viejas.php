<?
/*AUTOR: MAC
  Fecha: 01/10/04

$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/11/23 17:28:56 $
*/

require_once("../../config.php");

$db->StartTrans();

//traemos todas las facturas viejas
$query="select * from facturacion.facturas_viejas";
$facturas_viejas=sql($query) or fin_pagina();
echo "Recordcount ".$facturas_viejas->RecordCount();

//obtenemos el id del cliente ficticio
$query="select id_entidad from entidad where nombre='Cliente Facturas Viejas'";
$entity=sql($query) or fin_pagina();
$id_entidad=$entity->fields['id_entidad'];

//obtenemos el id del producto ficticio
$query="select id_producto from productos where desc_gral='Producto para facturas viejas'";
$prod=sql($query) or fin_pagina();
$id_producto=$prod->fields['id_producto'];

$cant_fact_ins=0;
$facturas_insertadas="Las facturas insertadas son:";
$ff=0;
$fact_repA=array();
$fact_repB=array();
$fact_exists=array();
//por cada factura vieja nos fijamos si existe en el sistema el numero de dicha 
//factura. Si no existe la insertamos.
while(!$facturas_viejas->EOF)
{$ff++;
	$nro_fvieja=$facturas_viejas->fields['numerofactura'];
	$fecha_factura=$facturas_viejas->fields['fechafactura'];
	$tipo_factura=strtolower($facturas_viejas->fields['idtipofactura']);
	echo "<br>Nro vieja $nro_fvieja - Fecha vieja $fecha_factura - Tipo $tipo_factura";
	
	//revisamos si la factura nro $nro_fvieja no esta ya insertada en la 
	//tabla de facturas del sistema. 
	//Si no está, la insertamos con el cliente 
	//y el producto ficticios para esta ocasion
	$esta="";
	$query="select nro_factura,tipo_factura,fecha_factura from facturacion.facturas where nro_factura=$nro_fvieja and tipo_factura='$tipo_factura'";
	$esta=sql($query) or fin_pagina();
	echo "<br>Query $query";
	echo "<br>Numero fact facturas ".$esta->fields['nro_factura'];
	echo "<br>$ff - ".($cant_fact_ins+1)."<br><br>";
	if($esta->fields['nro_factura']=="")
	 $insertame=1;
	elseif($esta->fields['fecha_factura']!=$fecha_factura)
	{$insertame=1;
	 $nro_fvieja="*$nro_fvieja"; 
	 if($tipo_factura=="a")
	 {while(in_array($nro_fvieja,$fact_repA))
	   $nro_fvieja="*$nro_fvieja";
	  $fact_repA[sizeof($fact_repA)]=$nro_fvieja; 
	 }
	 else 
	 {while(in_array($nro_fvieja,$fact_repB))
	   $nro_fvieja="*$nro_fvieja";
	  $fact_repB[sizeof($fact_repB)]=$nro_fvieja; 
	 } 
	}//de elseif($esta->fields['fecha_factura']!=$fecha_factura)
	else 
	{$insertame=0;
	 $fact_exists[sizeof($fact_exists)]=$esta->fields['nro_factura'];
	}
	
	if($insertame)
	{  $facturas_insertadas.="<br>Nro $nro_fvieja";
	   $cant_fact_ins++;

	   //insertamos la factura		
	   $query="select nextval('facturas_id_factura_seq') as id_factura";
	   $id_fact=sql($query) or fin_pagina();
	   $id_factura=$id_fact->fields['id_factura'];
	   //moneda: $
	   $id_moneda=1;
	   $estado="t";
	   
	   
	   $cliente="Cliente Facturas Viejas";
	   $query="insert into facturas(id_factura,id_moneda,nro_factura,cliente,tipo_factura,fecha_factura,estado,id_entidad) 
	          values($id_factura,$id_moneda,'$nro_fvieja','$cliente','$tipo_factura','$fecha_factura','$estado',$id_entidad)";	
	   sql($query) or fin_pagina();
	  
	   //insertamos el item de la factura
	   $cant_prod=1;
	   $precio=number_format($facturas_viejas->fields['importefactura'],2,'.','');
	   $descripcion="Producto para facturas viejas";
	   $query="insert into items_factura(id_producto,id_factura,precio,cant_prod,descripcion)
	           values($id_producto,$id_factura,$precio,$cant_prod,'$descripcion')"; 
	   sql($query) or fin_pagina();
	   
	   //insertamos el log de creacion
	   $hoy=date("Y-m-d",mktime());
	   $log="insert into facturacion.log(id_factura,usuario,fecha,tipo_log)
	         values($id_factura,'".$_ses_user['name']."','$hoy','creación (factura access)')";
	   sql($log) or fin_pagina();
	}	
	$facturas_viejas->MoveNext();
}//de while(!$facturas_viejas->EOF)
echo "<br><br>Total facturas viejas $ff<br>";
echo "La cantidad de facturas viejas insertadas es $cant_fact_ins<br>";
echo "Nº de Facturas repetidas pero insertadas:<br>";
print_r(sizeof($fact_rep));
echo "<br>Nº Facturas repetidas existentes en facturas:<br>";
print_r(sizeof($fact_exists));

$db->CompleteTrans();
echo"<br><br>";
fin_pagina();
?>