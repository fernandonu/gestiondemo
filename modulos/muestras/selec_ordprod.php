<?php
/*
$Author: cestila $
$Revision: 1.4 $
$Date: 2004/10/30 13:54:36 $
*/

require_once "../../config.php";
echo $html_header;

variables_form_busqueda("muestra_ordprod");


//armo la barra de navegacion
if ($cmd == "") {
	$cmd="apa";
    phpss_svars_set("_ses_muestra_ordprod_cmd", $cmd);
}
if ($parametros['detalle']) {
 phpss_svars_set("_ses_muestra_ordprod"," ");
 $filter=$parametros['filter'];
 $keyword=$parametros['keyword'];
}
//apa= Pendientes
//aa= Autorizadas
//ac= Enviadas
//at= Terminadas
//ta= Todas
$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "apa",
						),
					array(
						"descripcion"	=> "ParaAutorizar",
						"cmd"			=> "ap"
						),
					array(
						"descripcion"	=> "Autorizadas",
						"cmd"			=> "aa"
						),
					//array(
					//	"descripcion"	=> "Rechazadas",
					//	"cmd"			=> "ar"
					//	),
				    array(
						"descripcion"	=> "Enviadas",
						"cmd"			=> "en"
						),
				    array(
						"descripcion"	=> "Anuladas",
						"cmd"			=> "an"
						),
				    array(
						"descripcion"	=> "Terminadas",
						"cmd"			=> "at"
						),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "ta"	
						),
			      
				     );//Prepara los datos para armar la barra de navegación
?>
<div align="right">
<input type="button" name="cerrar" value="Cerrar" style="width=100" onclick="window.close();">
</div>
<?
generar_barra_nav($datos_barra);
//fin de barra de navegacion

$link_form=encode_link("selec_ordprod.php",array("filter"=>$filter,"keyword"=>$keyword));
?>	

<form name="form1" action="<?=$link_form?>" method="post">

<?
echo $html_header;
$orden = array(
		"default" => "2",
 		"default_up" => "0",
		"1" => "orden_de_produccion.fecha_entrega ",
		"2" => "entidad.nombre",
		"3" => "orden_de_produccion.lugar_entrega",
		"4" => "orden_de_produccion.cantidad",
		"5" => "ensamblador.nombre",
		"6" => "orden_de_produccion.nro_orden",
              );

$filtro = array(
		//"maquin.nro_serie" => "Nro. de Serie",
		"ensamblador.nombre" => "Ensamblador",
		"entidad.nombre" => "Cliente",
		"orden_de_produccion.fecha_entrega" => "Fecha Entrega",
		"orden_de_produccion.fecha_inicio" => "Fecha Inicio",
		"orden_de_produccion.nro_orden" => "Nro. Orden",
		"renglon.id_licitacion" => "ID. Licitación"
	      );	
	

$query="select distinct (orden_de_produccion.nro_orden),renglon.id_licitacion,orden_de_produccion.*,entidad.nombre,ensamblador.nombre as nombre_ensamblador from orden_de_produccion
        left join renglon using (id_renglon)
        left join entidad using (id_entidad)
		left join ensamblador using (id_ensamblador)";
        	
$where="";

if($cmd=="apa")
{$where=" orden_de_produccion.estado='P'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='P'";
}
if($cmd=="ap")
{$where=" orden_de_produccion.estado='PA'";
 $contar="select count(*) from orden__prod where orden_de_produccion.estado='PA'";
}
if($cmd=="aa")
{$where=" orden_de_produccion.estado='A'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='A'";
}
/*if($cmd=="ar")
{$where=" orden_de_produccion.estado='R'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='R'";
}*/
if($cmd=="en")
{$where=" orden_de_produccion.estado='E'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='E'";
}
if($cmd=="an")
{$where=" orden_de_produccion.estado='AN'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='AN'";
}
if($cmd=="at")
{$where=" orden_de_produccion.estado='T'";
 $contar="select count(*) from orden_de_produccion where orden_de_produccion.estado='T'";
}
if($cmd=="ta")
{
 $contar="select count(*) from orden_de_produccion";
}

echo "<br>";
echo "<center>";
if($_POST['keyword'] || $keyword || $_POST['estado']!="all")// en la variable de sesion para keyword hay datos)
 $contar="buscar";


list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar); 
$resultado=sql($sql) or fin_pagina();

?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>


<br>
<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
<tr id=ma>
  <td align="left" colspan="4">
   <b>Total: <?=$total_pedidos?> Orden/es Encontrada/s.</b>
   <input name="total_pedidos" type="hidden" value=<?=$total_pedidos?>>
  
  </td>
  <td align="right" colspan="3">
   <?=$link_pagina?>
  </td>
 </tr>


 <tr id=mo>

  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"filter"=>$filter,"keyword"=>$keyword))?>'>Entrega</a></b></td>
  <td width="20%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"filter"=>$filter,"keyword"=>$keyword))?>'>Cliente</a></b></td>
  <td width="30%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"filter"=>$filter,"keyword"=>$keyword))?>'>Dirección de Entrega</a></b></td>
  <td width="7%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"filter"=>$filter,"keyword"=>$keyword))?>'>Cantidad</a></b></td>
  <td width="15%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up,"filter"=>$filter,"keyword"=>$keyword))?>'>Ensamblador</a></b></td>
  <td width="5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up,"filter"=>$filter,"keyword"=>$keyword))?>'>Nro. orden</a></b></td>
 
 </tr>
 
 
<?

while (!$resultado->EOF) 
{
  if ($resultado->fields['id_licitacion']!=NULL) $lic=$resultado->fields['id_licitacion'];
    else $lic=-1;
    
    ?>
    <tr <?=atrib_tr()?> onclick="window.opener.cargar(<?=$resultado->fields['nro_orden']?>,<?=$lic?>,'<?=$resultado->fields['desc_prod']?>',<?=$resultado->fields['cantidad']?>)" <?if ($resultado->fields['id_licitacion']) {?> title="Licitación: <?=$resultado->fields['id_licitacion']?>"<?}?>>

    <td width="8%" align="center">
   <?=fecha($resultado->fields['fecha_entrega'])?>
  </td>
  <td width="20%" align="center">
   <?=$resultado->fields['nombre']?>
  </td>
  <td width="30%" align="center">
   <?=$resultado->fields['lugar_entrega']?>
  </td>
  <td width="7%" align="center">
   <?=$resultado->fields['cantidad']?>
  </td>
  <td width="15%" align="center">
   <?=$resultado->fields['nombre_ensamblador']?>
  </td>
  <td width="5%" align="center">
   <?=$resultado->fields['nro_orden']?>
  </td>
  
 </tr>
<?
 $resultado->MoveNext();
}
?>
</table>

<center>
<br>
<input type="button" name="cerrar" value="Cerrar" style="width=100" onclick="window.close();">
<br>
</center>
</form>
</body>
</html>
