<?
/*
Autor: GACZ
Creado: martes 01/06/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2006/01/04 08:32:40 $
*/
require_once("../../config.php");

//esto es porque los checkbox no se envian con el formulario
//cuando no estan checkeados y no se detectan en variables_form_busqueda
//se debe hacer antes de variables_form_busqueda

if ($_POST)
{
	//las variables de sesion se recuperan en el lib
	$_ses_prod_comp['chk_fechas']=$_POST['chk_fechas'];
	//para que borre la fecha en caso de una anterior busqueda
	if (!$_POST['chk_fechas'])
		$_ses_prod_comp['fecha_menor']=$_POST['fecha_menor']="";
	$_ses_prod_comp['chk_tipoprod']=$_POST['chk_tipoprod'];
	$_ses_prod_comp['chk_stock']=$_POST['chk_stock'];

	if ($_POST['bborrar'])
	{
		unset($_ses_prod_comp);
		unset($_POST);
	}
	phpss_svars_set("_ses_prod_comp", $_ses_prod_comp);
}


//valores por defecto
$variables=array(
	"chk_fechas"=>"",
	"chk_stock"=>"",
	"select_fechas"=>"",
	"select_estado"=>"",
	"fecha_menor"=>"",
	"fecha_mayor"=>"",
	"chk_tipoprod"=>"",
	"select_tipoprod"=>"",
	);
	variables_form_busqueda("prod_comp",$variables);


	$q ="select p.id_producto,p.desc_gral,sum(f.cantidad) as cantidad ";
	$q.="from ";
	$q.="fila f join ";
	$q.="productos p using(id_producto) join ";
	$q.="orden_de_compra oc using(nro_orden) join ";

	if ($chk_stock)
		$q.="proveedor prov using(id_proveedor) ";
	else	//para no contar los productos descontados de stock
		$q.="(select * from proveedor where razon_social not ilike 'stock%') prov using(id_proveedor) ";

	$where="";

	if ($chk_tipoprod==1 && $select_tipoprod && $select_tipoprod!=-1)
	{
		$where.="$and p.id_tipo_prod='$select_tipoprod' ";
		$and=" AND ";
	}
	else
		unset($select_tipoprod);

	if ($chk_fechas==1)
	{
		$where.="$and oc.$select_fechas <='".Fecha_db($fecha_mayor)."' ";
		if ($fecha_menor!="")
			$where.="AND oc.$select_fechas >='".Fecha_db($fecha_menor)."' ";
		$and=" AND ";
	}
	else
	{
		unset($select_fechas);
		unset($fecha_mayor);
		unset($fecha_menor);
	}

	if ($select_estado && $select_estado!=-1)
	{
		$where.="$and estado='$select_estado' ";
	}

	$where.="group by p.id_producto,p.desc_gral ";


$orden_array= array
(
		"default" => "1",
      "default_up"=>"1",
		"1" => "desc_gral",
		"2" => "cantidad"
);
$filtro_array= array
(
		"oc.cliente"=>"Cliente",
		"oc.notas"=>"Comentarios (OC)",
		"f.descripcion_prod" => "Descripcion Producto (OC)",
		"p.desc_gral" => "Descripcion Producto (original)",
		"oc.nro_orden"=>"Nº Orden",
		"oc.lugar_entrega"=>"Lugar de Entrega",
		"prov.razon_social"=>"Proveedor"
);
//para ver el detalle de las ordenes de compra
if ($parametros['id_producto'])
{

	require("prodcomp_listaoc.php");
	die;
}
/* */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Productos Ofertados</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<?=$html_header ?>
<!--
</head>
<body>
-->
<style type="text/css">
<!--
.s1 {
	cursor:hand;
	background-color: #CC3333;
}
.s1 {
	cursor:hand;
	background-color: #CC3333;
}
.sover {
	cursor:hand;
	background-color: #CC3333;
}
-->
</style>
<script src="../../lib/funciones.js"></script>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;
}
</SCRIPT>
<form name="form1" method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>">
<script language='javascript' src='<?=$html_root.'/lib/popcalendar.js'?>'></script>
<center>
    &nbsp; &nbsp;
    <table width="90%" class="bordes" cellspacing="2" cellpadding="0" bgcolor=<?=$bgcolor_out?>>
      <tr>
        <td colspan="4">
          <?
$itemspp=100;
list($q,$total,$link,$notup)=form_busqueda($q,$orden_array,$filtro_array,$link_tmp,$where,"buscar");
$prod=sql($q) or fin_pagina();

$q ="select * from tipos_prod order by descripcion";
$tiposprod=sql($q) or fin_pagina();

$q ="select distinct(estado) as id_estado,";
$q.="case when estado='p' then 'Pendientes'";
$q.="	  when estado='t' then 'Terminadas'"; //no esta mas terminadas
$q.="	  when estado='a' then 'Autorizadas'";
$q.="	  when estado='r' then 'Rechazadas'";
$q.="	  when estado='e' then 'Enviadas'";
$q.="	  when estado='d' then 'Pagadas (Parcialmente)'";
$q.="	  when estado='g' then 'Pagadas (Totalmente)'";
$q.="	  when estado='n' then 'Anuladas'";
$q.="	  when estado='u' then 'Por Autorizar' ";
$q.="end as nombre ";
$q.="from orden_de_compra order by nombre";
$estados=sql($q) or fin_pagina();
?>
        </td>
      </tr>
      <tr>
        <td width="17%">&nbsp;</td>
        <td width="6%">&nbsp;</td>
        <td width="71%">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td height="26"><strong>Filtrar por</strong></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Estado Ordenes</td>
        <td><input name="chk_estado" type="checkbox"  value="1" checked readonly onclick="this.checked=1"></td>
        <td><select name="select_estado">
            <option value="-1" >Todos</option>
            <?= make_options($estados,"id_estado","nombre",$select_estado); ?>
          </select></td>
        <td>&nbsp;</td>
      </tr>

      <tr>
        <td>Tipo de Producto</td>
        <td><input name="chk_tipoprod" type="checkbox" value="1" <? if ($chk_tipoprod==1) echo 'checked'?> ></td>
        <td><select name="select_tipoprod" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
            <option value="-1">Todos</option>
            <?= make_options($tiposprod,"id_tipo_prod","descripcion",$select_tipoprod); ?>
          </select></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Fechas</td>
        <td><input name="chk_fechas" type="checkbox" value="1" <? if ($chk_fechas==1) echo 'checked'?>></td>
        <td><select name="select_fechas" id="select_fechas">
<!--            <option selected value=0>Seleccione el tipo de fecha</option>-->
            <option value="fecha_entrega" <? if ($select_fechas=='fecha_entrega') echo 'selected' ?>>fecha
            de entrega</option>
          </select>
          entre
          <input name="fecha_menor" type="text" id="fecha_menor" size="10" value="<?=$fecha_menor?>">
          <img src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
seleccionar la fecha'  onClick="javascript:popUpCalendar(fecha_menor, fecha_menor, 'dd/mm/yyyy');">
          y
          <input name="fecha_mayor" type="text" id="fecha_mayor" value="<?=($fecha_mayor)?$fecha_mayor:date('d/m/Y') ?>" size="10">
          <img src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
seleccionar la fecha'  onClick="javascript:popUpCalendar(fecha_mayor, fecha_mayor, 'dd/mm/yyyy');"></td>
        <td width="6%">&nbsp;</td>
      </tr>
      <tr>
        <td>Stock</td>
        <td><input name="chk_stock" type="checkbox" value="1" <? if ($chk_stock==1) echo 'checked'?>></td>
        <td>Use este filtro para sumar los productos comprados a stock</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td height="29">&nbsp;</td>
        <td>&nbsp;</td>
        <td valign="middle"> <input type="submit" name="bbuscar" value="Buscar" style="width:105">&nbsp;
          <input name="bborrar" type="submit" value="Borrar Busqueda" style="width:110"> </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </center>
  <br>
  <table width="95%" class="bordessininferior" align="center" cellpadding="0" cellspacing="2">
    <tr id="ma">
      <td>Total: <?=$total ?></td>
      <td align="right"><?=$link ?></td>
    </tr>
	</table>
  <table width="95%" class="bordessinsuperior" align="center" cellpadding="0" cellspacing="2">
    <tr id=mo align="center" height=20>
<a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$notup)) ?>"><td width="89%" style="cursor:hand" title="Ordenar por Producto (<?=(!$up)?"ASCENDENTE":"DESCENDENTE" ?>)">Producto</td></a>
<a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$notup)) ?>"><td width="11%" style="cursor:hand" align="center" title="Ordenar por Cantidad (<?=(!$up)?"ASCENDENTE":"DESCENDENTE" ?>)">Cantidad</td></a>
    </tr>
    <?
	while (!$prod->EOF)
	{
?>
<a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("id_producto"=>$prod->fields['id_producto'],"total"=>$prod->fields['cantidad'])) ?>">
	<!--<tr <?="bgcolor=".$color=(++$i%2)?"#E0E0E0":"#5090C0" ?> height=15 style="cursor:hand" onmouseover="sobre(this,'white')" onmouseout="bajo(this,'<?=$color ?>')"> -->
        <tr <?echo atrib_tr();?>>
        <td align="left">
        <?= $prod->fields['desc_gral'] ?>
      </td>
      <td align="right">
        <?= $prod->fields['cantidad'] ?>
      </td>
    </tr>
</a>
    <?
		$prod->MoveNext();
	}
?>
  </table>
</form>
<br>
<?= fin_pagina(); ?>
<!--</body>
</html>-->