<?php

define('FPDF_FONTPATH','font/');

require_once("../../config.php");
include_once("remito_interno_clasepdf.php"); 

//generacion de pdf
$pdf=new remito_interno();
if ($parametros['id_remito']) $nro_remito=$parametros['id_remito'];
//$nro_remito="299"; //despues sacar !

//para saber si esta asociado a una licitacion

$con="select * from remito_interno where id_remito = $nro_remito";
$res_con=$db->Execute($con) or die($db->ErrorMsg());
$id_licitacion=$res_con->fields['id_licitacion'];
$fecha=$res_con->fields['fecha_remito'];
$entrega=$res_con->fields['entrega'];

//$id_licitacion="1232"; //despues sacar !
$pdf->dibujar_planilla();
$pdf->nro_remito_interno($nro_remito);
$pdf->fecha(Fecha($fecha));
$pdf->pasa_entrega($entrega);
$pdf->pasa_id_licitacion($id_licitacion);


//para recuperar los productos del remito interno

$con2="select * from items_remito_interno where id_remito = $nro_remito";
$res_con2=$db->Execute($con2) or die($db->ErrorMsg());
$first=1;
$total=0;
while(!$res_con2->EOF)
 {
$pdf->producto($res_con2->fields['descripcion'],$res_con2->fields['cant_prod']);
$res_con2->MoveNext();
$first=0;
}

$pdf->_final();
$pdf->Footer();
//if ($parametros['nro_orden'])
//   $mail=false; 
 //else $mail=true;
$nombre="remito_interno_".$nro_remito.".pdf";
$pdf->Output($nombre, true); 

//  $pdf->guardar_servidor($nombre);
//fin de generacion de pdf



?>