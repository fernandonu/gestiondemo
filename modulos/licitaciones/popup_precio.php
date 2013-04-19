<?
/*
Autor: GACZ
Creado: miercoles 05/05/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2004/12/06 19:01:46 $
*/

require_once("../../config.php");
$id_producto=$_GET['id_producto'];
$precio=$_GET['precio'];
$q= "
select * 
from
(
select id_producto, min(monto) as monto
from log_modif_precio_presupuesto 
join producto_presupuesto_new using(id_producto_presupuesto) 
where id_producto=$id_producto
group by id_producto
) t1
join
(
select id_proveedor,razon_social,id_producto,desc_gral,monto,fecha,u.nombre||' '||u.apellido as usuario,l.id_licitacion,nro,l.titulo,codigo_renglon
from licitaciones.log_modif_precio_presupuesto
join licitaciones.producto_presupuesto_new using(id_producto_presupuesto)
join licitaciones.renglon_presupuesto_new using(id_renglon_prop)
join licitaciones.renglon using(id_renglon)
join licitaciones.licitacion_presupuesto_new l using(id_licitacion_prop)
join licitaciones.entrega_estimada using(id_entrega_estimada)
join general.proveedor using(id_proveedor)
join general.productos using(id_producto)
join sistema.usuarios u using(id_usuario)
where id_producto=$id_producto
)t2 on t1.id_producto=t2.id_producto and t1.monto=t2.monto";
$info_prod=sql($q) or fin_pagina();

echo $html_header;
?>
<script>
window.returnValue=1;//por default cancelar
</script><title>Precio Sugerido</title>
<table width="90%"  border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#000000" rules="rows" >
  <tr align="center" id=mo>
    <td colspan="4"><center><?=$info_prod->fields['desc_gral'] ?><br>Precio mínimo: U$S <?= formato_money($info_prod->fields['monto']) ?></td>
  </tr>
  <tr>
    <td align="left">Conseguido el d&iacute;a</td>
    <td align="left">:</td>
    <td align="left"><?= date2("L",$info_prod->fields['fecha']) ?></td>
  </tr>
  <tr>
    <td width="29%" align="left">Licitacion ID<t></t></td>
    <td width="6%" align="left">:</td>
    <td width="65%" align="left"><?=$info_prod->fields['id_licitacion'] ?></td>
  </tr>
  <tr>
    <td align="left">Seguimiento N&ordm;<t>&nbsp;&nbsp;&nbsp;</t> </td>
    <td align="left">:</td>
    <td align="left"><?=$info_prod->fields['nro'] ?></td>
  </tr>
  <tr>
    <td align="left">Presupuesto T&iacute;tulo<t>&nbsp;&nbsp;&nbsp;</t></td>
    <td align="left">:</td>
    <td align="left"><?=$info_prod->fields['titulo'] ?></td>
  </tr>
  <tr>
    <td align="left">Renglon<t>&nbsp;&nbsp;&nbsp;</t></td>
    <td align="left">:</td>
    <td align="left"><?=$info_prod->fields['codigo_renglon'] ?></td>
  </tr>
  <tr>
    <td align="left">Proveedor<t>&nbsp;&nbsp;&nbsp;</t></td>
    <td align="left">:</td>
    <td align="left"><?=$info_prod->fields['razon_social'] ?></td>
  </tr>
  <tr>
    <td align="left">Usuario<t>&nbsp;&nbsp;&nbsp;</t></td>
    <td align="left">:</td>
    <td align="left"><?=$info_prod->fields['usuario'] ?></td>
  </tr>
</table>
<center><br><b>
&iquest;Desea utilizar su precio (U$S <?=formato_money($precio) ?>) de todos modos?</b>
<br>
<br>
<input type="button" name="baceptar" value="Aceptar" onclick="window.returnValue=0;window.close()">&nbsp;&nbsp;
<input type="button" name="bcancelar" value="Cancelar" onclick="window.returnValue=1;window.close()">
</center>