<?
/*
MODIFICADA POR
$Author: nazabal $
$Revision: 1.3 $
$Date: 2006/01/05 19:09:45 $
*/

require_once("../../config.php");
$id_remito=$_POST['id_remito'] or $id_remito=$parametros['id_remito'];
function enviar($nombre_archivo)
{
global $buffer;
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate");
header("Content-Transfer-Encoding: binary");
Header('Content-Type: application/dummy');
Header('Content-Length: '.strlen($buffer));
Header('Content-disposition: attachment; filename='.$nombre_archivo);
echo $buffer;
}


$sql="select * from (facturacion.remitos join licitaciones.moneda on facturacion.remitos.id_moneda = moneda.id_moneda 
 and id_remito=$id_remito)";
$res=sql($sql) or fin_pagina();

$non_cli=$res->fields['cliente'];
$direccion=$res->fields['direccion'];
$cuit=$res->fields['cuit'];
$iva=$res->fields['iva_tipo'];
$iib=$res->fields['iib'];
$id_l=$res->fields['id_licitacion'];
/*if($id_l!="")
	{
		$sql_co="select nro_lic_codificado from licitaciones.licitacion
		 where id_licitacion=$id_l";
		$res_co=sql($sql_co) or fin_pagina();
	}*/
//$pdf->otros($res->fields['otros']);

//Formamos la fecha correspondiente en castellano
$dia=date('D',strtotime( $res->fields['fecha_remito']));
switch($dia)
{case "Sun":$dia="Domingo";break;
 case "Mon":$dia="Lunes";break;
 case "Tue":$dia="Martes";break;
 case "Wed":$dia="Miercoles";break;
 case "Thu":$dia="Jueves";break;
 case "Fri":$dia="Viernes";break;
 case "Sat":$dia="Sabado";break;
}
$dia.=" ".date('j',strtotime( $res->fields['fecha_remito']))." de ";
$mes=date('M',strtotime( $res->fields['fecha_remito']));
switch($mes)
{case "Jan":$dia.="Enero";break;
 case "Feb":$dia.="Febrero";break;
 case "Mar":$dia.="Marzo";break;
 case "Apr":$dia.="Abril";break;
 case "May":$dia.="Mayo";break;
 case "Jun":$dia.="Junio";break;
 case "Jul":$dia.="Julio";break;
 case "Aug":$dia.="Agosto";break;
 case "Sep":$dia.="Septiembre";break;
 case "Oct":$dia.="Octubre";break;
 case "Nov":$dia.="Noviembre";break;
 case "Dec":$dia.="Diciembre";break;
}

//obtenemos la moneda que se esta usando
$query_moneda="select simbolo from remitos join moneda using(id_moneda) where id_remito=$id_remito";
$moneda=$db->Execute($query_moneda) or die ($db->ErrorMsg());

$dia.=" del ".date('Y',strtotime($res->fields['fecha_remito']));
$fec=fecha($dia);

$pedido=$res->fields['id_licitacion']?"ID Lic: ".$res->fields['id_licitacion']:$res->fields['pedido'];
$venta=$res->fields['venta'];

//if ($seg==1) {
$sql="select cant_prod,descripcion,items_remito.precio,chk_precios
	from facturacion.remitos join facturacion.items_remito
    on facturacion.remitos.id_remito =facturacion.items_remito.id_remito
    and items_remito.id_remito=$id_remito";
//}
//else {
//$sql1="select * from 
//       facturacion.remitos join 
//	   (facturacion.items_remito join general.productos on 
//       items_remito.id_producto = productos.id_producto and items_remito.id_remito=$id_remito)
//       using (id_remito)";
//}

$resultado_productos = $db->execute($sql) or die($db->ErrorMsg());
$cantidad_productos=$resultado_productos->RecordCount();
//$resultado_productos1 = $db->execute($sql1) or die($db->ErrorMsg());
//$cantidad_productos1=$resultado_productos1->RecordCount();
$imprime=$resultado_productos->fields['chk_precios'];
//$imprime1=$resultado_productos1->fields['chk_precios'];
$otros=$res->fields['otros'];

$buffer="<br><br><br><br><br><br><br><table align='center' width='100%'>
<tr><td width='65%'>".$non_cli."</td><td width='35%'></td></tr><tr><td align='right' width='65%'></td>
<td width='35%' align='right'>".$dia."</td></tr><tr><td align='left' width='65%'>".$cuit."<br>".$iva."</td><td align='right' width='35%'>".$pedido."</td></tr>
<br><br><br>";
while ($i<$cantidad_productos)
{
 //$descr=ereg_replace('(#10)','<br>',$resultado_productos->fields['descripcion']);	
 $descr=nl2br($resultado_productos->fields['descripcion']);
 //$subtotal=number_format($resultado_productos->fields['precio']*$resultado_productos->fields['cant_prod'],2,".","");
 $buffer.="<br><tr><td width='65%'>".$resultado_productos->fields['cant_prod']."  ".$descr."</td><td width='35%'></td></tr>";
 $i++;
 $resultado_productos->MoveNext();
}
$buffer.="
<tr><td align='left' width='65%'><b>".$otros."
</b></td><td width='35%'></td></tr>
</table>
</div>
</body>
</html>";


enviar("etiqueta_".$id_remito.".doc");
//echo "$buffer";
?>