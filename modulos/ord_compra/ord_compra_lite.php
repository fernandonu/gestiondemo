<?
/*
Autor: Broggi

MODIFICADA POR
$Author: broggi $
$Revision: 1.4 $
$Date: 2005/04/25 22:41:43 $
*/

require_once("../../config.php");
require_once("fns.php");
include("../modulo_clientes/funciones.php");

echo $html_header;
variables_form_busqueda("ord_compra_lite");//para que funcione el form busqueda

?>
<script language="JavaScript" src="../../lib/NumberFormat150.js"></script>
<script>
</script>
<?
include("../ayuda/ayudas.php");
?>

<form name="ord_compra_lite" method="post" >
<?

$orden= array
(
		"default" => "3",
                "default_up"=>"0",
		"1" => "orden_de_compra.nro_orden",
		//"2" => "orden_de_compra.estado",
		"3" => "orden_de_compra.fecha_entrega",
		"4" => "orden_de_compra.id_licitacion",
		"5" => "orden_de_compra.cliente",
		"6" => "proveedor.razon_social",
        "7" => "total_orden"

);
$filtro= array
(
		"orden_de_compra.nro_orden"=>"Nº de Orden",
		"orden_de_compra.nro_factura"=>"Nº de Factura",
		"orden_de_compra.id_licitacion"=>"ID Licitacion",
		"orden_de_compra.lugar_entrega"=>"Lugar de entrega",
		"orden_de_compra.cliente"=>"Cliente",
		"orden_de_compra.notas"=>"Comentarios",
		"proveedor.razon_social"=> "Proveedor",

);

 

$query="select proveedor.razon_social,nrocaso,flag_honorario,flag_stock,orden_prod,nro_orden,nro_factura,id_licitacion,
        lugar_entrega,cliente,notas,es_presupuesto,fecha_entrega 
        from orden_de_compra left join proveedor using (id_proveedor)";	
$where="(estado='g' or estado='d')";


echo "<br>";
echo "<center>";
$contar="buscar";


list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar,"","",""); 
//echo $sql."<br>";
$resultado=sql($sql) or fin_pagina();

echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
echo "</center>"
?>	
<br>
<table width='95%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left"  colspan='4'>
   <b>Total:</b> <?=$total_pedidos?> <b>Orden/es Encontrada/s.</b>
   <input name="total_pedidos" type="hidden" value=<?=$total_pedidos?>>
  </td>
  <td align="right" colspan="4">
   <?=$link_pagina?>
  </td>
 </tr>
 <tr id=mo>
  <td width="5%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nro. Orden</a></b></td>
  <td width="5%"><b>TIPO</b></td>
  <td width="5%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Id. Licitación</a></b></td>  
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Fecha Entrega</b></td>
  <td width="40%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Cliente</b></td> 
  <td width="20%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Proveedor</b></td>  
 </tr>
<?
 while (!$resultado->EOF)
       {
  ?>  
  <tr <?echo atrib_tr();?> style="cursor:hand">
  <a href="<? echo encode_link("oc_lite.php",array("nro_orden"=>$resultado->fields['nro_orden'])); ?>">
  <td align="center"><b><?=$resultado->fields['nro_orden']?></b></td>  
  <?$tipo="Otro";
  $titulo="Orden no Asociada";
  if ($resultado->fields['id_licitacion']!='' && $resultado->fields['es_presupuesto']==1) 
     {$tipo="Pres";
      $titulo="Presupuesto";
     }
  elseif ($resultado->fields['id_licitacion']!='') 
         {$tipo="Lic";
          $titulo="Licitacion";      
         }	 
  if ($resultado->fields['nrocaso']!='')
     {$tipo="ServT";
      $titulo="Servicio Técnico\nNro Caso: ".$resultado->fields['nrocaso'];          
     }	     
  if ($resultado->fields['flag_honorario']==1)
     {$tipo="HST";
      $titulo="Honorario Servicio Técnico";          
     }	        
  if ($resultado->fields['flag_stock']==1)
     {$tipo="Stock";
      $titulo="Stock Coradir";          
     }	             
  if ($resultado->fields['orden_prod']!='')
     {$tipo="RMA";
      $titulo="RMA de Producción";          
     }	           
?>
  <td title="<?=$titulo?>" align="center" ><b><?=$tipo?></b></td>
  <td align="center"><b><?=$resultado->fields['id_licitacion']?></b></td>
  <td align="center"><b><?=fecha($resultado->fields['fecha_entrega'])?></b></td>
  <td align="center"><b><?=$resultado->fields['cliente']?></b></td>
  <td align="center"><b><?=$resultado->fields['razon_social']?></b></td>
  </a>
  </tr>
  <?
        $resultado->MoveNext();
       }     	
?> 
 
</table>
<?=fin_pagina(); ?>