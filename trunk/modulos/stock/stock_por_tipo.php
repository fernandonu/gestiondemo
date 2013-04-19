<?
/*
AUTOR: MAC
FECHA: 30/05/06

MODIFICADO POR:
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2006/06/01 12:39:41 $
*/

require_once("../../config.php");

$msg=$parametros['msg'];
//print_r($parametros);

echo $html_header;

$var_sesion=array(
                  "tipo_movi"=>"todos",
                  "tipo_producto"=>"todos",
                  "es_inventario"=>0,
                  "ingresos" =>-1,
                  "egresos" =>-1,
                  );

variables_form_busqueda("stock_coradir",$var_sesion);


if ($cmd == "")
{
	$cmd="real";
	$_ses_stock_coradir["cmd"]=$cmd;
	phpss_svars_set("_ses_stock_coradir", $_ses_stock_coradir);
}

/*para el menu con los tipos de productos*/
$codigo_post=$_ses_stock["tipo_producto"];
if ($codigo_post=="")
          $codigo_post="todos";
/* para el menu con los tipos de productos*/

/*para el menu con los tipos de movimiento*/
$tipo_mov_post=$_ses_stock["tipo_movi"];
if ($tipo_mov_post=="")
          $tipo_mov_post="todos";


$datos_barra = array(
					array(
						"descripcion"	=> "Real",
						"cmd"			=> "real"
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						)
				 );
?>
<center>
<div align="center" class="bordes" style="width:95%">
 <font color="Blue" size="3"><b>Listado Completo de Productos en Stock Agrupados por Tipo</b></font>
</div>
</center>
<?
//generar_barra_nav($datos_barra);
$link_form=encode_link("stock_por_tipo.php",array());

$sql=" select sum((cant_disp + en_stock.cant_reservada)*precio_stock)  as total,
                      sum(cant_disp*precio_stock) as total_disponible
                from depositos
                join en_stock using(id_deposito)
                join producto_especifico using(id_prod_esp)
                where depositos.tipo>0
                ";

        $resultados=sql($sql) or fin_pagina();
        $monto_stock=array();
        $monto_stock["total"]=$resultados->fields["total"];
        $monto_stock["disponible"]=$resultados->fields["total_disponible"];

?>
<form name="form1" method="POST" action="<?=$link_form?>">

<?

$orden=array(
		  "default" => "1",
		  "default_up" => "$up",
		  "1" => "tipo_descripcion",
		  "2" => "cant_total",
		  "3" => "cant_disp",
		  "4" => "cant_reservada",
          "5" => "monto_total",
           );


   $filtro= array(
		"tipo_descripcion" => "Tipo Producto",
		"cant_total"=>"Cantidad Total",
		"cant_disp"=>"Cantidad Disponible",
		"cant_reservada"=>"Cantidad Reservada",
        );


$query="select stock_por_tipo.tipo_descripcion,stock_por_tipo.id_tipo_prod,
	sum(monto_total) as monto_total,sum(monto_total_disp) as monto_total_disp,
	sum(cant_total)as cant_total,sum(cant_reservada) as cant_reservada,sum(cant_disp) as cant_disp
from(
select stock.descripcion,stock.precio_stock,stock.tipo_descripcion,stock.id_tipo_prod,
			COALESCE(monto_total-(cant_rma_historial*stock.precio_stock),monto_total,0) as monto_total,
			COALESCE(monto_total_disp-(cant_rma_historial*stock.precio_stock),monto_total_disp,0) as monto_total_disp,
			COALESCE(cant_total-(cant_rma_historial),cant_total,0) as cant_total,cant_reservada,
			COALESCE(cant_disp-(cant_rma_historial),cant_disp,0) as cant_disp
		from(
			select producto_especifico.descripcion,producto_especifico.precio_stock,tipos_prod.descripcion as tipo_descripcion,producto_especifico.id_prod_esp,tipos_prod.id_tipo_prod,
		               (sum(en_stock.cant_disp)+sum(en_stock.cant_reservada))*precio_stock as monto_total,
		               (sum(en_stock.cant_disp))*precio_stock as monto_total_disp,
		               (sum(en_stock.cant_disp)+sum(en_stock.cant_reservada))as cant_total,
		       		   (sum(en_stock.cant_reservada)) as cant_reservada,sum(en_stock.cant_disp)as cant_disp
				from stock.en_stock join general.producto_especifico using(id_prod_esp)
				join general.tipos_prod using(id_tipo_prod) join general.depositos using (id_deposito)
				where tipo>=0
				group by producto_especifico.descripcion,producto_especifico.precio_stock,tipos_prod.descripcion,producto_especifico.id_prod_esp,tipos_prod.id_tipo_prod
		)as stock
		left join
				(select sum(cantidad)as cant_rma_historial,
					info_rma.id_en_stock,en_stock.id_prod_esp
					from stock.en_stock left join stock.info_rma using(id_en_stock)
					join stock.estado_rma using(id_estado_rma)
					where estado_rma.nombre_corto='B'
					group by info_rma.id_en_stock,en_stock.id_prod_esp
				) as rma using(id_prod_esp)
)as stock_por_tipo
";

$where=" cant_total>0
		 group by stock_por_tipo.tipo_descripcion,stock_por_tipo.id_tipo_prod";

?>
<input type="hidden" name="id_tipo_prod" value="<?=$id_tipo_prod?>">
<table width="95%" align="center">
 <tr>
  <td align="center">
	<?
	$contar="buscar";
    list($sql,$total_stock_coradir,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar);
	$result=sql($sql,"<br>Error en la consutla de busqueda Stock Coradir<br>") or fin_pagina();
	?>
	&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
  </td>
 </tr>
 <tr>
  <td>
	<table bgcolor="White" width="100%">
	 <tr>
	  <td>
	  	<b>Monto Total: U$S <?=formato_money($monto_stock["total"])?></b>
	  </td>
	  <td align="right">
	   <b>Monto Disp.: U$S <?=formato_money($monto_stock["disponible"])?></b>
	  </td>
	 </tr>
	</table>
  </td>
 </tr>
</table>
<br>
<?
if ($msg) Aviso($msg);

?>
<table width='95%' border='0' cellspacing='2' align="center">
 <tr id=ma>
    <td align="left" colspan="3">
     <b>Total:</b> <?=$total_stock_coradir?> </td>
	<td align=right colspan="3">
	 <?=$link_pagina?>
	</td>
 </tr>
 <tr id=mo>
  <td width='50%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Tipo</a></b></td>
  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>' title="Cantidad Total en Stock">Total</a></b></td>
  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>' title="Cantidad Disponible">Disponible</a></b></td>
  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>' title="Cantidad Reservada">Reservada</a></b></td>
  <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Monto Total</a></b></td>
 </tr>
<?
while(!$result->EOF)
{
$color_env_rec="";
$link = encode_link("stock_coradir_detalle.php",array("pagina"=>"listado","id_prod_esp"=>$result->fields["id_prod_esp"]));

tr_tag($link);

?>
  <td>
   <b>
    <?=$result->fields['tipo_descripcion']?>
   </b>
  </td>
  <td align="center">
   <b>
    <?=$result->fields["cant_total"]?>
   </b>
  </td>
  <td align="center">
   <b>
    <?=$result->fields['cant_disp'];?>
   </b>
  </td>
  <td align="center">
   <b>
    <?=$result->fields['cant_reservada'];?>
   </b>
  </td>
  <td title="Monto Total Disponible: ".<?=formato_money($result->fields['monto_total_disp'])?>>
   <b>
    U$S <?=formato_money($result->fields['monto_total'])?>
   </b>
  </td>
 </tr>
 <?
 $result->MoveNext();
}//de while(!$result->EOF)
?>
</table>
</form>
<?fin_pagina();?>