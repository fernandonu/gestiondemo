<?
/*
Autor: GACZ
Creado: miercoles 05/05/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2005/06/09 18:13:12 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

echo $html_header;
if ($_POST["form_busqueda"]) 
{
 if(!$_POST["filtro_fecha"]){
  $_POST["filtro_fecha"]=0;
  
 }
}  
 $var_sesion=array(
               "select_tipo_factura"=>"",
               "select_moneda"=>"",
               "filtro_fecha"=>"0",
               "fecha_desde"=>"",
               "fecha_hasta"=>date2(),
               "filtro_monto"=>"0",
               "monto_desde"=>"",
               "monto_hasta"=>"",
               );	
variables_form_busqueda("facturas_lista",$var_sesion);

$orden = array (
    "default" => "1",
    "1" => "fecha_factura",
    "2" => "nro_factura",
    "3" => "tipo_factura",
    "4" => "cliente",
    "5" => "total",
    "6" => "usuario"
  );
  
$filtro = array (
    "nro_factura" => "Nº Factura",
    "nro_remito" => "Nº Remito",
    "id_licitacion" => "ID Licitación",
    "pedido" => "Nº Pedido",
    "venta" => "Venta",
    "cliente" => "Cliente - Nombre",
    "direccion" => "Cliente - Direccion",
    "cuit" => "Cliente - C.U.I.T.",
    "iib" => "Cliente - Nº I.I.B.",
    "iva_tipo" => "Cliente - Condición I.V.A.",
    "iva_tasa" => "Cliente - Tasa I.V.A.",
    "otros" => "Cliente - Otros"
  ); 

// esta es la consulta q se ejecuta siempre a la cual hay q adicionarle los where dep
// de los campos q elija  
$campos="facturas.id_factura,facturas.nro_factura,facturas.cliente,facturas.tipo_factura,
         facturas.fecha_factura,facturas.estado,facturas.id_moneda,facturas.id_licitacion,moneda.simbolo,id_renglones_oc,
         log.usuario,log.fecha,log.tipo_log,case when estado='a' then 0 else total end as total ";
$sql_tmp="select $campos FROM facturas
          left join (select count(id_renglones_oc) as id_renglones_oc,id_factura from facturacion.items_factura group by id_factura) as roc using(id_factura)
          left join log on log.id_factura=facturas.id_factura and log.tipo_log='creacion' 
          left join moneda using(id_moneda)
          left join 
             (select sum(precio*cant_prod) as total,id_factura from items_factura group by id_factura) mtotal 
               on facturas.id_factura=mtotal.id_factura ";
$where_tmp="";

//filtro por tipo de factura
if ($select_tipo_factura && $select_tipo_factura!='todas')
  $where_tmp.="facturas.tipo_factura='$select_tipo_factura'";

if ($select_moneda && $select_moneda!='todas')
{
	if ($where_tmp!="")
      $where_tmp.=" and ";
  $where_tmp.="facturas.id_moneda=$select_moneda";
}

if ($filtro_fecha == "1") 
{
		if ($where_tmp!="")
			$where_tmp.=" and facturas.fecha_factura between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";
		else
			$where_tmp.=" facturas.fecha_factura between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";	
}

$query.=" ORDER BY";
if ($orden)
	$query.=" $orden";
else 
	$query.=" fecha_factura";

cargar_calendario();
?>
</head>
<script> 
function RowClick(id,tipo,nro)
{
	document.forms[0].id_factura.value=id;
  document.forms[0].tipo_factura.value=tipo;
  document.forms[0].nro_factura.value=nro;
  <?$onrowclick=$parametros['onrowclick'] or $onrowclick=$_GET['onrowclick']; echo $onrowclick?>;
}
</script>
<form name="form" method="post" action="<?= encode_link($_SERVER['SCRIPT_NAME'],array("onrowclick"=>$onrowclick))?>">
<table width="100%" >
 <tr>
  <td align="center">
   <? 
    $contar="buscar"; 
    $link_tmp['onrowclick']=$onrowclick;
    list($sql,$total_facturas,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);
    $facturas=sql($sql) or fin_pagina();
   ?>
	<input type="submit" name="boton" value="Buscar" >  
  </td>
  <td>
	</td>
 </tr>
</table>
<table align="center" >
 <tr>
  <td>
   <table class="bordes">
    <tr>
     <td><input type="checkbox" name="filtro_fecha" value="1" <?=($filtro_fecha==1)?"checked":""?> ><b>Filtrar por fechas:</b></td> 
    </tr>
    <tr>
     <td> 
      <?if (!$filtro_fecha) {$fecha_desde="";$fecha_hasta="";}?>
      <b>Desde</b><input type="text" size="10" name="fecha_desde" value="<?=$fecha_desde?>" readonly>
      <?=link_calendario("fecha_desde")?>
      <b>Hasta</b><input type="text" size="10" name="fecha_hasta" value="<?=$fecha_hasta?>" readonly>
      <?=link_calendario("fecha_hasta")?>
     </td>
    </tr>
   </table>   
  </td>
  <td>&nbsp; </td>
  <td>
   <table class="bordes">
    <tr>
     <td width="15%">
      <b>Tipo Factura</b>
<? 
//echo $select_tipo_factura;
$otipo_factura= new HtmlOptionList("select_tipo_factura");
$otipo_factura->add_option("Todas","todas");
$otipo_factura->add_option("A","a");
$otipo_factura->add_option("B","b");
$otipo_factura->setSelected($select_tipo_factura);
$otipo_factura->toBrowser();
?>
     </td>
    </tr>
    <tr>
     <td width="10%">
      <b>Moneda&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
<? 
//echo $select_moneda;
$omoneda= new HtmlOptionList("select_moneda");
$omoneda->add_option("Todas","todas");
$omoneda->add_option("Pesos","1");
$omoneda->add_option("Dólares","2");
$omoneda->setSelected($select_moneda);
$omoneda->toBrowser();
?>
     </td>
    </tr>
   </table>
  </td>     
 </tr>    
</table>
<table width=100% cellspacing=0 cellpadding=2 class='bordes'>
  <tr id=ma height=20px >
   <td align="left"><b>Total: <?=$total_facturas?> facturas encontradas</b></td>
   <td align="right"> <?=($link_pagina)?$link_pagina:"&nbsp;"?></td>
  </tr>
</table>  
<table width=100% cellspacing=2 cellpadding=2 class='bordes'>  
  <tr>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up,"onrowclick"=>$onrowclick))?>'>Fecha</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$up,"onrowclick"=>$onrowclick))?>'>Nº Factura</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up,"onrowclick"=>$onrowclick))?>'>Tipo</a>
   </td>
   <td align=right id=mo>Estado</td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"4","up"=>$up,"onrowclick"=>$onrowclick))?>'>Cliente</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"5","up"=>$up,"onrowclick"=>$onrowclick))?>'>Monto Total</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up,"onrowclick"=>$onrowclick))?>'>Creada por</a>
   </td>
 </tr>
<? 	$l=1;
    while (!$facturas->EOF ) { 
?>
     <tr <?=atrib_tr($bgcolor_out)?>>
      <a onclick="RowClick(<?=$facturas->fields['id_factura']?>,'<?=$facturas->fields['tipo_factura']?>','<?=$facturas->fields['nro_factura']?>');">
      <td align="center"><?=Fecha($facturas->fields['fecha_factura']) ?></td>
      <td align="center"><?=$facturas->fields['nro_factura']?></td>
      <td align="center"><?=strtoupper($facturas->fields['tipo_factura'])?></td>
      <td align="center">
      <? switch ($facturas->fields['estado']) {
        		case 'a':
        		case 'A': echo "Anulada";break;
        		case 'p':
			  	case 'P': echo "Pendiente";	break;
        		case 't':
			  	case 'T': echo "Terminada";	break;		
         } ?>
      </td>
      <td align="center"><?=$facturas->fields['cliente'] ?></td>
      <td><table width="100%"><tr><td width="20%" align="left"><?=$facturas->fields['simbolo']?></td><td width="80%" align="right"><?= number_format($facturas->fields['total'], 2, ',', '.') ?></td></tr></table></td>
      <td align="center"><?=$facturas->fields['usuario'] ?></td>
			</a>
  		</tr>
<? 		$facturas->MoveNext();
  		$l++;
  }
?>
    </table>
<input type="hidden" name="id_factura" >    
<input type="hidden" name="nro_factura" >    
<input type="hidden" name="tipo_factura" >    
</form>
<br>
