<?
/*
$Author: mari $
$Revision: 1.35 $
$Date: 2006/03/17 20:58:39 $
*/
require_once("../../config.php");

$id_prod_esp=$parametros["id_prod_esp"] or $id_prod_esp=$_POST["id_prod_esp"];
$id_deposito=$parametros["id_deposito"] or $id_deposito=$_POST["id_deposito"];

/* agrego a la consulta la cantidad de productos resercados
$sql="select sum(cant_disp) as cantidad ";
$sql.=" from stock where id_producto=$id_producto and id_deposito=$id_deposito";
$sql.=" left join (select sum(cantidad_total) as cantidad_reservada
                   from stock.reservados where id_deposito=$id_deposito and
                                               id_producto=$id_producto)";
*/
$sql="select descripcion, id_en_stock, id_prod_esp, nombre as nombre_deposito, cant_disp, cant_reservada, (cant_disp+cant_reservada) as total_cant
	from stock.en_stock
		join general.depositos using (id_deposito)
		join general.producto_especifico using (id_prod_esp)
		where id_prod_esp=$id_prod_esp and id_deposito=$id_deposito
	group by descripcion, id_prod_esp, id_en_stock, nombre, cant_disp, cant_reservada";

$datos_generales=sql($sql, "c27 ") or fin_pagina();

//Consulta que me trae los movimientos  de los productos
//desde control stock , descuento, y la informacion de los productos
//junto con el de los depositos
/***************************************************************************************
  Tipos de movimientos tomados en cuenta
  1=  Autorizado (descuento manual)
  4=  Ingreso por Orden de Compra
  5=  Ingreso manual de stock
  7=  Se utilizaron los productos reservados para OC o para Movimiento de material
  10= Se recibieron productos de una Orden de Compra
  11= Se entregaron productos de una Orden de Compra
  13= Ingreso por Movimiento de Material
  17= Descuento de Stock disponible por des-recepcion de una fila de OC de tipo Stock
  18= Descuento directo de stock disponible
****************************************************************************************/

$sql="select usuario_mov, comentario, fecha_mov, cantidad, id_tipo_movimiento,
      tipo_movimiento.descripcion as tipo_mov,clase_mov
	  from stock.log_movimientos_stock
	  join stock.en_stock using (id_en_stock)
	  join stock.tipo_movimiento using(id_tipo_movimiento)
	  where id_en_stock=".$datos_generales->fields["id_en_stock"]."
	  and (clase_mov=1 or clase_mov=2)
	  order by fecha_mov, comentario ASC";

$entradas_salidas=sql($sql, "c40") or fin_pagina();
$desc_gral=$datos_generales->fields["descripcion"];
$nombre_deposito=$entradas_salidas->fields["nombre_deposito"];

$nombre="stock-$desc_gral-$nombre_deposito.xls";
$aux=array("/",",");
$aux1=array("","");
$nombre_arch = str_replace($aux,$aux1,$nombre);

excel_header("$nombre_arch");

?>
<html>
<body>
<table width=100% align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5">
  <tr>
    <td  <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
          <b>Información del Producto</b>
    </td>
  </tr>
  <tr>
      <td <?=excel_style("texto")?> width=20%>
       <b>Producto :</b>
       <b><?=$desc_gral;?></b>
     </td>
  </tr>
  <tr>
      <td <?=excel_style("texto")?>>
       <b>Deposito</b>
       <b><?=$datos_generales->fields["nombre_deposito"]?></b>
      </td>
  </tr>
<tr>
  <td <?=excel_style("texto")?>>
    <b>Cantidad Disponibles :</b>
    <b><?="&nbsp;&nbsp;&nbsp;&nbsp; ".$datos_generales->fields["cant_disp"]?></b>
  </td>
</tr>
<tr>
  <td <?=excel_style("texto")?>>
    <b>Cantidad Reservados :</b>
    <b><?if ($datos_generales->fields["cant_reservada"]) echo "&nbsp;&nbsp;&nbsp;&nbsp; ".$datos_generales->fields["cant_reservada"]; else echo "&nbsp;&nbsp;&nbsp;&nbsp; 0";?></b>
  </td>
</tr>
<tr>
  <td <?=excel_style("texto")?>>
    <b>Cantidad Total en Stock :</b>
    <b><?echo $datos_generales->fields["total_cant"];?></b>
  </td>
</tr>
<tr>
    <td <?=excel_style("texto")?> bgcolor=#C0C0FF align=center>
         <b> Resumen</b>
    </td>
  </tr>
<tr>
  <td <?=excel_style("texto")?> bgcolor=#C0FFFF align=center>
         <b> Ingresos</b>
  </td>
</tr>
<tr>
  <td >
  <table width=100% align=Center border=1 cellspacing="0" cellpadding="1" bordercolor=#ACACAC>
    <tr>
       <td <?=excel_style("texto")?> width=30% align=Center><b>Tipo de Movimiento</b></td>
       <td <?=excel_style("texto")?> width=10% align=Center><b>Usuario</b></td>
       <td <?=excel_style("texto")?> width=40% align=Center><b>Comentarios</b></td>
       <td <?=excel_style("texto")?> width=15% align=Center><b>Fecha</b></td>
       <td <?=excel_style("texto")?> width=5% align=Center><b>Cantidad</b></td>
    </tr>
<?
$cantidad=$entradas_salidas->recordcount();
$total_ingresos=0;

for($i=0;$i<$cantidad;$i++)
{
	/***************************************************************************************
	  Tipos de Ingreso listados
	  4=  Ingreso por Orden de Compra
	  5=  Ingreso manual de stock
	  10= Se recibieron productos de una Orden de Compra
	  13= Ingreso por Movimiento de Material
	  //Tipos de ingresos clase_mov=1
	****************************************************************************************/
	 if($entradas_salidas->fields["clase_mov"]==1) 
	 {
	    $total_ingresos+=$entradas_salidas->fields["cantidad"];
			?>
	    <tr>
	      <td <?=excel_style("texto")?> align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
	      <td <?=excel_style("texto")?> align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
	      <td <?=excel_style("texto")?> align=left><?=$entradas_salidas->fields["comentario"];?> </td>
	      <td <?=excel_style("fecha")?> align=right><?=fecha($entradas_salidas->fields["fecha_mov"])?></td>
	      <td  align=right><?=$entradas_salidas->fields["cantidad"];?></td>
	    </tr>
		<?
	}//del if
	$entradas_salidas->movenext();
}//de for($i=0;$i<$cantidad;$i++)
?>
 	</table>
 </td>
</tr>
<tr>
  <td>&nbsp;</td>
</tr>
<?$entradas_salidas->move(0);?>
<tr><td <?=excel_style("texto")?> bgcolor=#C0FFFF align=center><b> Egresos</b></td></tr>
<tr>
  <td >
  <table width=100% align=Center border=1 cellspacing="0" cellpadding="1" bordercolor=#ACACAC>
    <tr>
       <td <?=excel_style("texto")?> width=30% align=Center><b>Tipo de Movimiento</b></td>
       <td <?=excel_style("texto")?> width=10% align=Center><b>Usuario</b></td>
       <td <?=excel_style("texto")?> width=40% align=Center><b>Comentarios</b></td>
       <td <?=excel_style("texto")?> width=15% align=Center><b>Fecha</b></td>
       <td <?=excel_style("texto")?> width=5% align=Center><b>Cantidad</b></td>
    </tr>
<?
$cantidad=$entradas_salidas->recordCount();
$total_egresos=0;
for($i=0;$i<$cantidad;$i++)
{
	/***************************************************************************************
	  Tipos de Egreso listados
	  1=  Autorizado (descuento manual)
	  7=  Se utilizaron los productos reservados para OC o para Movimiento de material
	  11= Se entregaron productos de una Orden de Compra
	  17= Descuento de Stock disponible por des-recepcion de una fila de OC de tipo Stock
	  18= Descuento directo de stock disponible
	   //Tipos de egresos clase_mov=2
	****************************************************************************************/
    if ($entradas_salidas->fields["clase_mov"]==2) 
	{
    	$total_egresos+=abs($entradas_salidas->fields["cantidad"]);
?>
    <tr>
      <td <?=excel_style("texto")?> align=left><?=$entradas_salidas->fields["tipo_mov"]?></td>
      <td <?=excel_style("texto")?> align=left><?=$entradas_salidas->fields["usuario_mov"]?></td>
      <td <?=excel_style("texto")?> align=left><?=$entradas_salidas->fields["comentario"]?></td>
      <td <?=excel_style("fecha")?> align=right><?=fecha($entradas_salidas->fields["fecha_mov"])?></td>
      <td  align=right><?=abs($entradas_salidas->fields["cantidad"]);?></td>
    </tr>
<?
	}//del if
	$entradas_salidas->movenext();
}//del for
?>
  </table>
 </td>
</tr>
<tr>
 <td>
  &nbsp;
 </td>
</tr>
<tr>
   <td>
       <table width=40% align=right>
          <tr>
            <td <?=excel_style("texto")?>><b>Total Ingresos</b></td>
            <td  align=right><b><?=$total_ingresos?></b></td>
          </tr>
          <tr>
            <td <?=excel_style("texto")?>><b>Total Egresos</b></td>
            <td  align=right><b><?=$total_egresos?></b></td>
          </tr>
          <tr>
           <td <?=excel_style("texto")?>><b>Total Stock</b></td>
           <td  align=right><b><?=$total_ingresos-$total_egresos?></b></td>
          </tr>
       </table>
   </td>
</tr>
</table>
</body>
</html>
