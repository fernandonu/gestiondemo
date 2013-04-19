<?

/*AUTOR: MAC

$Author: cesar $
$Revision: 1.6 $
$Date: 2004/08/06 22:53:09 $
*/

require_once("../../config.php");
echo $html_header;
variables_form_busqueda("reclamo_partes");
	
if ($cmd == "") {
	$cmd="pendientes";
    phpss_svars_set("_ses_reclamo_partes_cmd", $cmd);
}



$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes",
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						)
				 );
echo "<br>";
generar_barra_nav($datos_barra);
?>
<br>
<form name="form1" method="POST" action="seguimiento_reclamo_partes.php">
<?
$orden = array(
		"default" => "1",
		"default_up"=>"0",
		"1" => "reclamo_partes.id_reclamo_partes",
                "2" => "fecha_orden",
		"3" => "reclamo_partes.descripcion",
		"4" => "pr.razon_social",
		"5" => "reclamo_partes.nro_orden",
                "6" => "monto",
		"7" => "caso.nrocaso"
	);


$filtro = array(
		"reclamo_partes.id_reclamo_partes" => "ID",
		"reclamo_partes.nro_orden" => "Nº de Orden",
		"caso.nrocaso" => "Nº de C.A.S.",
		"pr.razon_social" => "Proveedor" ,
                "monto" => "Monto",
                "reclamo_partes.descripcion"=>"Descripción"
);



$query="select reclamo_partes.id_reclamo_partes,reclamo_partes.nro_orden,
               reclamo_partes.descripcion,pr.razon_social,caso.nrocaso,
               orden_de_compra.fecha  as fecha_orden,moneda.simbolo
               ,tablas_monto.monto
               from reclamo_partes
               join (select razon_social,id_proveedor from proveedor) as pr
               using(id_proveedor)
               left join (select nrocaso,idcaso from casos_cdr) as caso
               using(idcaso)
               left join orden_de_compra using(nro_orden)
               left join
               (
                select sum(cantidad*precio_unitario) as monto,nro_orden
                  from fila join orden_de_compra
                using(nro_orden) group by nro_orden
                ) as tablas_monto
               using(nro_orden)
               left join licitaciones.moneda using(id_moneda)
        ";

if($cmd=="pendientes")
{ $where=" reclamo_partes.estado=0";
  $contar="select count(*) from reclamo_partes where estado=0";
}
else //historial
{$where=" reclamo_partes.estado=1";
 $contar="select count(*) from reclamo_partes where estado=1";
}


if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos
     $contar="buscar";
?>

<table width="90%" align="center" class="bordes" cellpaddindg=2 cellspacing=2 bgcolor=<?=$bgcolor3?>>
        <tr><td>
<?
list($sql,$total_reclamos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar);


$reclamo_partes=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error al traer datos de los reclamos de partes");
?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
        </td></tr>
</table>
<br>
<?=$parametros['msg'];?>
<div style='position:relative; width:100%; height:63%; overflow:auto;'>
<table class="bordessininferior" width="95%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
 <tr id=ma>
    <td align="left">
     <b>Total:</b> <?=$total_reclamos?>.
    </td>
	<td align=right>
	 <?=$link_pagina?>
	</td>
  </tr>
</table>
<!--<div style='position:relative; width:100%; height:63%; overflow:auto;'>-->
<table width='95%' class="bordessinsuperior" cellspacing='2' align="center">
<tr id=mo>
 <td width='1%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
 <td width=8%><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Fecha</a></b></td>
 <td width=31%><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Descripción</a></b></td>
 <td width=15%><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Proveedor</a></b></td>
 <td width=15%><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Nº Orden</a></b></td>
 <td width=15%><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Monto</a></b></td>
 <td width=15%><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Nº Caso</a></b></td>
</tr>

<?
$cnr=1;
while(!$reclamo_partes->EOF)
{$link = encode_link("detalle_reclamo_partes.php",array("pagina"=>"listado","id_reclamo_partes"=>$reclamo_partes->fields["id_reclamo_partes"]));
 tr_tag($link,"");
 ?>
  <td align="center" width='1%'>
   <?=$reclamo_partes->fields['id_reclamo_partes']?>
  </td>
  <td align="center" width=8%>
   <?=fecha($reclamo_partes->fields['fecha_orden'])?>
  </td>

  <td width=31%>
   <?=$reclamo_partes->fields['descripcion']?>
  </td>
  <td width=15%>
   <?=$reclamo_partes->fields['razon_social']?>
  </td>
  <td width=15%>
   <?=$reclamo_partes->fields['nro_orden']?>
  </td>
  <td width=15%>
     <table width=100% align=center>
     <tr>
       <td align=center><?=$reclamo_partes->fields["simbolo"]?></td>
       <td align=right width=90%><?=formato_money($reclamo_partes->fields['monto'])?></td>
     </tr>
     </table>
  </td>

  <td width=15%>
   <?=$reclamo_partes->fields['nrocaso']?>
  </td>
 </tr>
 </a>
<?
 $reclamo_partes->MoveNext();
}
?>
</table>
</div>
<center>
<input type="button" name="nueva_parte" value="Nuevo Reclamo de partes" onclick="document.location='detalle_reclamo_partes.php'">
</center>
</body>
</html>