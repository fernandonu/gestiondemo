<?
/*
Modificada por
$Author: mari $
$Revision: 
$Date: 2006/06/13 18:20:46 $
*/

require_once("../../config.php");
echo $html_header;
  
variables_form_busqueda("detalles_rma",array("fecha_d"=>"","fecha_h"=>"","select_tipo_log"=>""));

$itemspp=50;
$order=0;

$orden= array
(
	"default" => "1",
	"default_up"=>"$order",
	"1" => "id_info_rma",
	"2" => "nrocaso",
	"3" => "descripcion",
	"4" => "cantidad",
	"5" => "precio",
	"6" => "log_rma.fecha",
);

$filtro= array
(
	"id_info_rma"=>"Nº rma",
	"nrocaso"=>"Nro Caso",
	"descripcion"=>"Producto",
	"cantidad"=>"Cantidad",
	"log_rma.fecha"=>"Fecha",
	"tipo_log"=>"Tipo Ingreso"
);

$fecha_d=$parametros['fecha_d'] or $fecha_d=$_POST['fecha_d'] or $fecha_d=$_ses_detalles_rma['fecha_d'];
$fecha_h=$parametros['fecha_h'] or $fecha_h=$_POST['fecha_h'] or $fecha_h=$_ses_detalles_rma['fecha_h'];
$select_tipo_log=$parametros['select_tipo_log'] or $select_tipo_log=$_POST['select_tipo_log'] or $select_tipo_log=$_ses_detalles_rma['select_tipo_log'] or $select_tipo_log=-1;
?>

<form name='form1' action='' method="post">
<input type="hidden" name="fecha_d" value="<?=$fecha_d?>">
<input type="hidden" name="fecha_h" value="<?=$fecha_h?>">
<?
$and="";
if ($select_tipo_log!=-1) {
   switch ($select_tipo_log) {
      case 1: $log = "%Creacion";
               break; 
      case 2: $log= "%Creacion PM%";
               break; 
      case 3: $log= "%Creacion MM%";
               break;
   
   }
    
    $and=" and tipo_log ilike '$log'";
}
else $and=" and tipo_log ilike '%Creacion%'";

$sql_in=" select id_info_rma,info_rma.cantidad,producto_especifico.descripcion,
          (producto_especifico.precio_stock*info_rma.cantidad) as precio,
          log_rma.fecha,log_rma.tipo_log,nrocaso from 
          stock.en_stock 
		  join general.producto_especifico using(id_prod_esp) 
	      join stock.info_rma using (id_en_stock)
		  join (select id_info_rma,fecha,tipo_log from stock.log_info_rma
		       where fecha between '$fecha_d' and '$fecha_h' $and) as log_rma using (id_info_rma)";
$where="   en_stock.id_deposito=9 and cantidad > 0";


?>

<table align=center cellpadding=5 cellspacing=0>
 <tr>
  <td> <b>Tipo Ingreso</b>
            <select name="select_tipo_log" >
             <option value=-1 <?if ($select_tipo_log==-1) echo 'selected'?>>Todos</option>
             <option value=1  <?if ($select_tipo_log==1) echo 'selected'?>>Creación (Por RMA)</option>
             <option value=2  <?if ($select_tipo_log==2) echo 'selected'?>>Creación (Por Pedido Material)</option>
             <option value=3  <?if ($select_tipo_log==3) echo 'selected'?>>Creación (Por Movimiento Material) </option>
            </select>
     </td>
   <td>
    <? list($sql_in,$total,$link_pagina,$up)=form_busqueda($sql_in,$orden,$filtro,$link_tmp,$where,"buscar"); 
    ?>
     <input type=submit name='form_busqueda' value='Buscar' class='estilo_boton'>
   </td>
    <td>
     <input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
    </td>
 </tr>
</table>
<? $res=sql($sql_in,"$sql_in") or fin_pagina();?>
<br>
<table class="bordessininferior" width="95%" align="center" cellpadding="3" cellspacing='0'>
   <tr id=ma>
      <td align=left> <b>Total:</b>  <?=$total?> Ingresos</td>
      <td align="right"><?=$link_pagina;?></td>
   </tr>
</table>

<table width='95%' class="bordessinsuperior" cellspacing='2' align="center">   
   <tr id=mo>
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"1","up"=>$up))?>'>Nº Rma</a></td>     
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"2","up"=>$up))?>'>Nº Caso</a></td>
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"3","up"=>$up))?>'>Producto</a></td>
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"4","up"=>$up))?>'>Cantidad</a></td>
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"5","up"=>$up))?>'>Precio Total</a></td>
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"6","up"=>$up))?>'>Fecha Ingreso</a></td>  
     <td><a href='<?=encode_link('detalle_info_rma.php',array("sort"=>"7","up"=>$up))?>'>Tipo Ing</a></td>  
   </tr>
    
   <? while(!$res->EOF) {
   $link=encode_link("stock_rma.php",array("id_info_rma"=>$res->fields['id_info_rma'],"pagina"=>10));
   	?>
   <tr <?=atrib_tr();?>  style="cursor:hand">
   <a onclick="window.open('<?=$link?>','','')">
       <td align="center"><?=$res->fields['id_info_rma']?></td>   
       <td align="center"><?=$res->fields['nrocaso']?></td>   
       <td align="center"><?=$res->fields['descripcion']?></td>   
       <td align="center"><?=$res->fields['cantidad']?></td>   
       <td align="center"><?="U\$S ".formato_money($res->fields['precio'])?></td>   
       <td align="center"><?=fecha($res->fields['fecha'])?></td>   
       <td align="center"><?=$res->fields['tipo_log']?></td>   
   </a> 
   </tr>
   <? $res->MoveNext();
   }?>
</table>

</form>
