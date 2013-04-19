<?

/*AUTOR: MAC
  FECHA: 10/06/04

$Author: marco_canderle $
$Revision: 1.6 $
$Date: 2006/01/04 10:45:40 $
*/

require_once("../../config.php");
echo $html_header;
variables_form_busqueda("muestras");

if ($cmd == "") {
	$cmd="en_curso";
    phpss_svars_set("_ses_muestras_cmd", $cmd);
}



$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes",
						),
					array(
						"descripcion"	=> "En Curso",
						"cmd"			=> "en_curso",
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );
echo "<br>";
generar_barra_nav($datos_barra);
?>
<br>
<form name="form1" method="POST" action="seguimiento_muestras.php">
<?
if($cmd=="pendientes")
{$orden = array(
		"default" => "1",
		"default_up"=>"0",
		"1" => "muestra.descripcion",
		"2" => "muestra.costo",
		"3" => "muestra.id_licitacion",
		"4" => "entidad.nombre"

	);

}
elseif($cmd=="en_curso")
{$orden = array(
		"default" => "1",
		"default_up"=>"0",
		"1" => "muestra.descripcion",
		"2" => "muestra.costo",
		"3" => "muestra.id_licitacion",
		"4" => "entidad.nombre",
		"5" => "fecha_vencimiento"
	);

}
elseif($cmd=="historial")
{$orden = array(
		"default" => "1",
		"default_up"=>"0",
		"1" => "muestra.descripcion",
		"2" => "muestra.costo",
		"3" => "muestra.id_licitacion",
		"4" => "entidad.nombre",
		"5" => "fecha_devolucion"
	);

}
elseif($cmd=="todas")
{$orden = array(
		"default" => "1",
		"default_up"=>"0",
		"1" => "muestra.descripcion",
		"2" => "muestra.costo",
		"3" => "muestra.id_licitacion",
		"4" => "entidad.nombre",
		"5" => "fecha_vencimiento",
		"6" => "fecha_devolucion"
	);

}

 $filtro = array(
		"muestra.descripcion" => "Descripción",
		"muestra.costo" => "Costo",
		"muestra.id_licitacion" => "ID Licitación",
		"entidad.nombre" => "Entidad"
 );

$query="select * from muestras.muestra join licitaciones.entidad using(id_entidad)";// join licitacion using(id_licitacion) join estado using(id_estado)";

if($cmd=="pendientes")
{ $where=" muestra.estado=0";
  $contar="select count(*) from muestra where estado=0";
}
elseif($cmd=="en_curso")
{ $where=" muestra.estado=1";
  $contar="select count(*) from muestra where estado=1";
}
elseif($cmd=="historial")
{$where=" muestra.estado=2";
 $contar="select count(*) from muestra where estado=2";
}
else//todas
{ $contar="select count(*) from muestra";
}

if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos
     $contar="buscar";
?>

<table width="95%" align="center">
<tr>
<td>
 <table>
  <tr>

 <td  width="80%">
<?
list($sql,$total_muestras,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar);

$muestras=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error al traer datos de las muestras");
?>

&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
</td>
<td  width="10%">
<input type="button" name="nueva_muestra" value="Nueva Muestra" onclick="document.location='detalle_muestras.php'">
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>
<?=$parametros['msg'];?>
<table border=0 width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
 <tr id=ma>
    <td align="left">
     <b>Total:</b> <?=$total_muestras?>.
    </td>
	<td align=right>
	 <?=$link_pagina?>
	</td>
  </tr>
</table>

<table width='95%' border='0' cellspacing='2' align="center">
<tr id=mo>
<?
 if($cmd=="en_curso" || $cmd=="todas")
 {
 ?>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Fecha Prevista Devolución</a></b></td>
 <?
 }
 if($cmd=="historial" || $cmd=="todas")
 {if($cmd=="todas")
   $orden_nro="7";
  else
   $orden_nro="6";
  ?>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>$orden_nro,"up"=>$up))?>'>Fecha Devolución</a></b></td>
 <?
 }
 ?>
 <td width='33%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Descripción</a></b></td>
 <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Costo en U$S</a></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Licitación</a></b></td>
 <td width='32%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Entidad</a></b></td>


</tr>
<?
$cnr=1;
$max=0;
$referencia[0]["nombre"] = '';
$referencia[0]["color"] = '';

while(!$muestras->EOF)
{$link = encode_link("detalle_muestras.php",array("pagina"=>"listado","id_muestra"=>$muestras->fields["id_muestra"]));
 tr_tag($link,"");
 ?>
    <?
  $color_td="";
  if($cmd=="en_curso" || $cmd=="todas")
  {
  //controlamos si la fecha de devolucion es menor que la actual (es decir que
  //se vencio el plazo de devolucion
  $fecha_hoy=date("Y-m-d",mktime());
  if($muestras->fields['estado']==1 && $muestras->fields['fecha_vencimiento']<$fecha_hoy)
   $color_td="bgcolor='red' title='Se venció la fecha de devolución'";
   ?>
   <td <?=$color_td?>>
    <?=fecha($muestras->fields['fecha_vencimiento'])?>
   </td>
  <?
  }
  if($cmd=="historial" || $cmd=="todas")
  {?>
   <td>
    <?=fecha($muestras->fields['fecha_devolucion'])?>
   </td>
  <?
  }
  ?>
  <td>
   <?=$muestras->fields['descripcion']?>
  </td>
  <td>
   <?=formato_money($muestras->fields['costo']);?>
  </td>
  <?
  if ($muestras->fields['id_licitacion']!= '') {
  	$sql = "select nombre,color from estado join licitacion using(id_estado) where id_licitacion =".$muestras->fields['id_licitacion'];
  	$res = sql($sql) or fin_pagina();

    echo "<td bgcolor='".$res->fields["color"]."'>";
  } else {
    echo "<td>";
  }
  ?>
   <?=$muestras->fields['id_licitacion']?>
  </td>
  <td>
   <?=$muestras->fields['nombre']?>
  </td>

 </tr>
 </a>
<?
 $muestras->MoveNext();

 $esta = 0;
 foreach ($referencia as $aux){
 	if ($aux["color"]== $res->fields["color"]) $esta=1;
 }
 if (!$esta) {
  	$referencia[$max]["nombre"] = $res->fields["nombre"];
  	$referencia[$max]["color"] = $res->fields["color"];
  	$max++;
 }

}
?>


</table>
<br>
<table align="center" width="95%" bgcolor="White" border="1" bordercolor='#FFFFFF'>
<?
if($cmd=="en_curso" || $cmd=="todas")
{
?>
 <tr>
  <td colspan="2">
   <b>Colores de referencia para Devoluciones</b><br>
  </td>
 </tr>
 <tr>
  <td width=3%  bgcolor="Red" bordercolor='#000000'>&nbsp;
  </td>
  <td>
   Fecha de Devolución Vencida
  </td>
 </tr>

 <?} if ($max > 0) { ?>
 <tr>
  <td colspan="2">
   <b>Colores de referencia de Licitaciones</b><br>
  </td>
 </tr>
 <TR>
<?	$cont=0;
	foreach ($referencia as $estados) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td bordercolor='#FFFFFF' colspan=2><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
		echo "<td width=15 bgcolor='".$estados["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados["nombre"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	} //del foreach?>
  </TR>
  <?} //del count?>
</table>
<? fin_pagina();?>
</body>
</html>
