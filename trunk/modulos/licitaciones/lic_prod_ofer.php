<?
/*
Autor: GACZ
Creado: jueves 13/05/04

MODIFICADA POR
$Author: fernando $
$Revision: 1.8 $
$Date: 2004/07/14 22:12:32 $
*/
require_once("../../config.php");

//esto es porque los checkbox no se envian con el formulario
//cuando no estan checkeados y no se detectan en variables_form_busqueda
//se debe hacer antes de variables_form_busqueda

if ($_POST)
{
	//las variables de sesion se recuperan en el lib
	$_ses_prod_ofer['chk_fechas']=$_POST['chk_fechas'];
	$_ses_prod_ofer['chk_alt']=$_POST['chk_alt'];
	//para que borre la fecha en caso de una anterior busqueda
	if (!$_POST['chk_fechas'])
		$_ses_prod_ofer['fecha_menor']=$_POST['fecha_menor']="";
	$_ses_prod_ofer['chk_tipoprod']=$_POST['chk_tipoprod'];
	
	if ($_POST['bborrar'])
	{
		unset($_ses_prod_ofer);
		unset($_POST);
	}
	phpss_svars_set("_ses_prod_ofer", $_ses_prod_ofer);
}


//valores por defecto
$variables=array(
	"chk_estado"=>1,//siempre se filtra por estado
	"select_estado"=>0,//estado de licitaciones en curso
	"chk_fechas"=>"",
	"select_fechas"=>"",
	"fecha_menor"=>"",
	"fecha_mayor"=>"",
	"chk_tipoprod"=>"",
	"select_tipoprod"=>"",
	"chk_alt"=>""
	);
	variables_form_busqueda("prod_ofer",$variables);

	$q ="select * from estado order by nombre";// where ubicacion!='HISTORIAL'";//estados de licitaciones
	$estados=sql($q) or fin_pagina();

	$q ="select p2.id_producto,p2.desc_gral,sum(r.cantidad*p1.cantidad) as cantidad from ";
	$q.="licitacion l join ";
//	$q.="estado e using(id_estado) join ";

	//busqueda con renglones alternativos
        switch ($select_estado)
        {
         case 2:
                $filtrar_estado=1;//presuntamenta ganada
                break;
         case 3:
                $filtrar_estado=2;//preadjudicada
                break;
         case 7:
               $filtrar_estado=3; // orden de compra
        }//del switch

        if ($filtrar_estado)
         {
         $q.="(select * from renglon join historial_estados using(id_renglon)
              where historial_estados.id_estado_renglon=$filtrar_estado and activo=1
              ) r using (id_licitacion) join ";
         }
        else{
	if ($chk_alt)
	{
               	$q.="renglon r using(id_licitacion) join ";
         	$chk_alt=1;
	}
	else
	$q.="(select * from renglon where codigo_renglon not ilike '%alt%') r using (id_licitacion) join ";
        }
	$q.="producto p1 using(id_renglon) join ";
	$q.="productos p2 using(id_producto) ";

//	if ($filter=='all' || $filter=='d.nombre' || $filter=='e.nombre')
//	{
		$q.="join entidad e using(id_entidad) join ";
		$q.="distrito d using(id_distrito) ";

//	}

	$where="";
        //si el estado es presuntamente ganada
        //orden de compra o preadjudicado filtro los
        //renglones que tienen ese estado

	//si esta el chk de estado y el estado no es todos(-1)
	if ($chk_estado && ($select_estado==0 ||  $select_estado!="" && $select_estado!=-1))
	{
		$where.="  l.id_estado=$select_estado ";
		$and=" AND ";
	}

	else
		unset($select_estado);
//	else
//		$q.="where e.ubicacion!='HISTORIAL' ";
        //$where="";
	if ($chk_tipoprod==1 && $select_tipoprod && $select_tipoprod!=-1)
	{
		$where.="$and p2.tipo='$select_tipoprod' ";
		$and=" AND ";
	}
	else
		unset($select_tipoprod);

	if ($chk_fechas==1)
	{
		$where.="$and l.$select_fechas <='".Fecha_db($fecha_mayor)."' ";
		if ($fecha_menor!="")	
			$where.="AND l.$select_fechas >='".Fecha_db($fecha_menor)."' ";
	}
	else 
	{
		unset($select_fechas);
		unset($fecha_mayor);
		unset($fecha_menor);
	}
	$where.="group by p2.id_producto,p2.desc_gral ";

	
	
$orden_array= array
(
		"default" => "1",
      "default_up"=>"1",
		"1" => "desc_gral",
		"2" => "cantidad"
);
$filtro_array= array
(
		"l.id_licitacion" => "ID licitacion",
		"l.observaciones"=>"Comentarios",
		"d.nombre"=>"Distrito",
		"e.nombre"=>"Entidad",
		"l.nro_lic_codificado"=>"Numero licitacion",
		"r.titulo"=>"Titulo Renglon",
		"p2.desc_gral"=>"Descripcion Productos"
);
//para ver el detalle de las licitaciones
if ($parametros['id_producto'])
{

	require("lic_prod_ofer_lista_lic.php");
	die;
}
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
<form name="form1" method="post" action="<?//=$_SERVER['SCRIPT_NAME'] ?>">
<script language='javascript' src='<?=$html_root.'/lib/popcalendar.js'?>'></script>
<center>
    &nbsp; &nbsp; 
    <table width="90%" border="0" cellspacing="2" cellpadding="0">
      <tr>
        <td colspan="4">
          <?
$itemspp=100;

list($q,$total,$link,$notup)=form_busqueda($q,$orden_array,$filtro_array,$link_tmp,$where,"buscar");
//echo "<br>$q</br>";
$prod=sql($q) or fin_pagina();
//echo "<br>$q</br>";
$q ="select * from tipos_prod order by descripcion";
$tiposprod=sql($q) or fin_pagina();

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
        <td>Estado Licitacion</td>
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
            <?= make_options($tiposprod,"codigo","descripcion",$select_tipoprod); ?>
          </select></td>
        <td>&nbsp;</td>
      </tr>
      <tr> 
        <td>Fechas</td>
        <td><input name="chk_fechas" type="checkbox" value="1" <? if ($chk_fechas==1) echo 'checked'?>></td>
        <td><select name="select_fechas" id="select_fechas">
<!--            <option selected value=0>Seleccione el tipo de fecha</option>-->
            <option value="fecha_apertura" <? if ($select_fechas=='fecha_apertura') echo 'selected' ?>>fecha 
            de apertura</option>
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
        <td>Con alternativas</td>
        <td><input name="chk_alt" type="checkbox" id="chk_alt" value="1" <? if ($chk_alt==1) echo 'checked'?>></td>
        <td> Use este filtro para contar tambi&eacute;n las alternativas en los 
          renglones</td>
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
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr> 
      <td>Total: <?=$total ?></td>
      <td align="right"><?=$link ?></td>
    </tr>
	</table>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr id=mo align="center" height=20> 
<a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$notup,"estado"=>$select_estado)) ?>"><td width="89%" style="cursor:hand" title="Ordenar por Producto (<?=(!$up)?"ASCENDENTE":"DESCENDENTE" ?>)">Producto</td></a>
<a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$notup,"estado"=>$select_estado)) ?>"><td width="11%" style="cursor:hand" align="center" title="Ordenar por Cantidad (<?=(!$up)?"ASCENDENTE":"DESCENDENTE" ?>)">Cantidad</td></a>
    </tr>
    <?
	while (!$prod->EOF)
	{
?>
<a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("estado"=>$select_estado,"id_producto"=>$prod->fields['id_producto'],"total"=>$prod->fields['cantidad'])) ?>">
	<tr <?="bgcolor=".$color=(++$i%2)?"#E0E0E0":"#5090C0" ?> height=15 style="cursor:hand" onmouseover="sobre(this,'white')" onmouseout="bajo(this,'<?=$color ?>')">
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