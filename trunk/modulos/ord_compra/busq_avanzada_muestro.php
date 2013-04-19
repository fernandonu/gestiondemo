<?
/*
Author: Broggi
Fecha: 28/07/2004

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.44 $
$Date: 2006/05/10 19:35:37 $
*/

//print_r($parametros);

if($_GET['first']==1)
{require_once("../../config.php");
 phpss_svars_set("contador_pagina",$contador_pagina+1);
 ?>
<html>
<head>
<title>
 Resultados de la Búsqueda
</title>
</head>
<body>
 <form name="busq_avanzada_muestro" action="busq_avanzada_muestro.php" method="post">
  <input type="hidden" name="usar_var_ses" value=1>
  <input type="hidden" name="keyword" value="">
  <input type="hidden" name="filter" value="">
  <input type="hidden" name="usar_bus_general" value="">
  <input type="hidden" name="filtrar_nro_orden" value=""> <!--2-->
  <input type="hidden" name="filtrar_estado" value=""> <!--3-->
  <input type="hidden" name="filtrar_tipo_orden" value=""> <!--4-->
  <input type="hidden" name="filtrar_productos" value=""> <!--5-->
  <input type="hidden" name="filtrar_re_en" value=""> <!--5-->
  <input type="hidden" name="filtrar_id_licitacion" value=""> <!--6-->
  <input type="hidden" name="filtrar_id_proveedor" value=""> <!--7-->
  <input type="hidden" name="filtrar_id_moneda" value=""> <!--8-->
  <input type="hidden" name="filtrar_id_entidad" value=""> <!--9-->
  <input type="hidden" name="filtrar_orden_prod" value=""> <!--10-->
  <input type="hidden" name="filtrar_nro_factura" value=""> <!--11-->
  <input type="hidden" name="filtrar_fecha_factura" value=""> <!--12-->
  <input type="hidden" name="filtrar_lugar_entrega" value=""> <!--13-->
  <input type="hidden" name="filtrar_fecha_entrega" value=""> <!--14-->
  <input type="hidden" name="filtrar_por_forma_pago" value="">
  <input type="hidden" name="ordenar_nro_orden" value=""> <!--15-->
  <input type="hidden" name="ordenar_estado" value=""> <!--16-->
  <input type="hidden" name="ordenar_id_licitacion" value=""> <!--17-->
  <input type="hidden" name="ordenar_proveedor" value=""> <!--18-->
  <input type="hidden" name="ordenar_entidad" value=""> <!--19-->
  <input type="hidden" name="ordenar_orden_prod" value=""> <!--20-->
  <input type="hidden" name="ordenar_nro_factura" value=""> <!--21-->
  <input type="hidden" name="ordenar_fecha_factura" value=""> <!--22-->
  <input type="hidden" name="ordenar_lugar_entrega" value=""> <!--23-->
  <input type="hidden" name="ordenar_fecha_entrega" value=""> <!--24-->
  <input type="hidden" name="nro_orden" value=""> <!--25-->
  <input type="hidden" name="estado" value=""> <!--26-->
  <input type="hidden" name="tipo_orden" value=""> <!--27-->
  <input type="hidden" name="productos" value=""> <!--28-->
  <input type="hidden" name="entregado_recibido" value=""> <!--28-->
  <input type="hidden" name="id_licitacion" value=""> <!--29-->
  <input type="hidden" name="proveedor" value=""> <!--30-->
  <input type="hidden" name="moneda" value=""> <!--31-->
  <input type="hidden" name="entidad" value=""> <!--32-->
  <input type="hidden" name="orden_prod" value=""> <!--33-->
  <input type="hidden" name="nro_factura" value=""> <!--34-->
  <input type="hidden" name="forma_pago" value="">
  <input type="hidden" name="forma_pago_texto" value="">
  <input type="hidden" name="fecha_factura_2" value=""> <!--35-->
  <input type="hidden" name="fecha_factura_1" value=""> <!--36-->
  <input type="hidden" name="lugar_entrega" value=""> <!--37-->
  <input type="hidden" name="fecha_entrega_2" value=""> <!--38-->
  <input type="hidden" name="fecha_entrega_1" value=""> <!--39-->
  <input type="hidden" name="filtrar_por_monto" value=""> <!--40-->
  <input type="hidden" name="monto_1" value=""> <!--41-->
  <input type="hidden" name="monto_2" value=""> <!--42-->
  <input type="hidden" name="ordenar_monto" value=""> <!--43-->
 </form>
<script>
 if(window.opener.document.busq_avanzada_armo.usar_bus_general.checked==true)
   {document.busq_avanzada_muestro.keyword.value=window.opener.document.busq_avanzada_armo.keyword.value;
    //alert ("esto es lo que hay "+window.opener.document.busq_avanzada_armo.keyword.value)  ;
    document.busq_avanzada_muestro.filter.value=window.opener.document.busq_avanzada_armo.opcion_busq_general.value;
   }
 if(window.opener.document.busq_avanzada_armo.filtrar_nro_orden.checked==true)
   {document.busq_avanzada_muestro.filtrar_nro_orden.value=window.opener.document.busq_avanzada_armo.filtrar_nro_orden.value;
    document.busq_avanzada_muestro.nro_orden.value=window.opener.document.busq_avanzada_armo.nro_orden.value;
   }
 else {document.busq_avanzada_muestro.filtrar_nro_orden.value="";
       document.busq_avanzada_muestro.nro_orden.value="";
      }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_estado.checked==true)
   {document.busq_avanzada_muestro.filtrar_estado.value=window.opener.document.busq_avanzada_armo.filtrar_estado.value;
    document.busq_avanzada_muestro.estado.value=window.opener.document.busq_avanzada_armo.estado.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_estado.checked==true)
   {document.busq_avanzada_muestro.ordenar_estado.value=window.opener.document.busq_avanzada_armo.ordenar_estado.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_tipo_orden.checked==true)
   {document.busq_avanzada_muestro.filtrar_tipo_orden.value=window.opener.document.busq_avanzada_armo.filtrar_tipo_orden.value;
    document.busq_avanzada_muestro.tipo_orden.value=window.opener.document.busq_avanzada_armo.tipo_orden.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_productos.checked==true)
   {document.busq_avanzada_muestro.filtrar_productos.value=window.opener.document.busq_avanzada_armo.filtrar_productos.value;
    document.busq_avanzada_muestro.productos.value=window.opener.document.busq_avanzada_armo.productos.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_re_en.checked==true)
   {document.busq_avanzada_muestro.filtrar_re_en.value=window.opener.document.busq_avanzada_armo.filtrar_re_en.value;
    document.busq_avanzada_muestro.entregado_recibido.value=window.opener.document.busq_avanzada_armo.entregado_recibido.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_id_licitacion.checked==true)
   {document.busq_avanzada_muestro.filtrar_id_licitacion.value=window.opener.document.busq_avanzada_armo.filtrar_id_licitacion.value;
    document.busq_avanzada_muestro.id_licitacion.value=window.opener.document.busq_avanzada_armo.id_licitacion.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_id_licitacion.checked==true)
   {document.busq_avanzada_muestro.ordenar_id_licitacion.value=window.opener.document.busq_avanzada_armo.ordenar_id_licitacion.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_id_proveedor.checked==true)
   {document.busq_avanzada_muestro.filtrar_id_proveedor.value=window.opener.document.busq_avanzada_armo.filtrar_id_proveedor.value;
    document.busq_avanzada_muestro.proveedor.value=window.opener.document.busq_avanzada_armo.proveedor.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_proveedor.checked==true)
   {document.busq_avanzada_muestro.ordenar_proveedor.value=window.opener.document.busq_avanzada_armo.ordenar_proveedor.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_id_moneda.checked==true)
   {document.busq_avanzada_muestro.filtrar_id_moneda.value=window.opener.document.busq_avanzada_armo.filtrar_id_moneda.value;
    document.busq_avanzada_muestro.moneda.value=window.opener.document.busq_avanzada_armo.moneda.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_id_entidad.checked==true)
   {document.busq_avanzada_muestro.filtrar_id_entidad.value=window.opener.document.busq_avanzada_armo.filtrar_id_entidad.value;
    document.busq_avanzada_muestro.entidad.value=window.opener.document.busq_avanzada_armo.entidad.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_entidad.checked==true)
   {document.busq_avanzada_muestro.ordenar_entidad.value=window.opener.document.busq_avanzada_armo.ordenar_entidad.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_orden_prod.checked==true)
   {document.busq_avanzada_muestro.filtrar_orden_prod.value=window.opener.document.busq_avanzada_armo.filtrar_orden_prod.value;
    document.busq_avanzada_muestro.orden_prod.value=window.opener.document.busq_avanzada_armo.orden_prod.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_orden_prod.checked==true)
   {document.busq_avanzada_muestro.ordenar_orden_prod.value=window.opener.document.busq_avanzada_armo.ordenar_orden_prod.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_nro_factura.checked==true)
   {document.busq_avanzada_muestro.filtrar_nro_factura.value=window.opener.document.busq_avanzada_armo.filtrar_nro_factura.value;
    document.busq_avanzada_muestro.nro_factura.value=window.opener.document.busq_avanzada_armo.nro_factura.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_nro_factura.checked==true)
   {document.busq_avanzada_muestro.ordenar_nro_factura.value=window.opener.document.busq_avanzada_armo.ordenar_nro_factura.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_fecha_factura.checked==true)
   {document.busq_avanzada_muestro.filtrar_fecha_factura.value=window.opener.document.busq_avanzada_armo.filtrar_fecha_factura.value;
    document.busq_avanzada_muestro.fecha_factura_1.value=window.opener.document.busq_avanzada_armo.fecha_factura_1.value;
    document.busq_avanzada_muestro.fecha_factura_2.value=window.opener.document.busq_avanzada_armo.fecha_factura_2.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_fecha_factura.checked==true)
   {document.busq_avanzada_muestro.ordenar_fecha_factura.value=window.opener.document.busq_avanzada_armo.ordenar_fecha_factura.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_lugar_entrega.checked==true)
   {document.busq_avanzada_muestro.filtrar_lugar_entrega.value=window.opener.document.busq_avanzada_armo.filtrar_lugar_entrega.value;
    document.busq_avanzada_muestro.lugar_entrega.value=window.opener.document.busq_avanzada_armo.lugar_entrega.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_lugar_entrega.checked==true)
   {document.busq_avanzada_muestro.ordenar_lugar_entrega.value=window.opener.document.busq_avanzada_armo.ordenar_lugar_entrega.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_fecha_entrega.checked==true)
   {document.busq_avanzada_muestro.filtrar_fecha_entrega.value=window.opener.document.busq_avanzada_armo.filtrar_fecha_entrega.value;
    document.busq_avanzada_muestro.fecha_entrega_1.value=window.opener.document.busq_avanzada_armo.fecha_entrega_1.value;
    document.busq_avanzada_muestro.fecha_entrega_2.value=window.opener.document.busq_avanzada_armo.fecha_entrega_2.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_fecha_entrega.checked==true)
   {document.busq_avanzada_muestro.ordenar_fecha_entrega.value=window.opener.document.busq_avanzada_armo.ordenar_fecha_entrega.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_por_monto.checked==true)
   {document.busq_avanzada_muestro.filtrar_por_monto.value=window.opener.document.busq_avanzada_armo.filtrar_por_monto.value;
    document.busq_avanzada_muestro.monto_1.value=window.opener.document.busq_avanzada_armo.monto_1.value;
    document.busq_avanzada_muestro.monto_2.value=window.opener.document.busq_avanzada_armo.monto_2.value;
   }
 if(window.opener.document.busq_avanzada_armo.ordenar_monto.checked==true)
   {document.busq_avanzada_muestro.ordenar_monto.value=window.opener.document.busq_avanzada_armo.ordenar_monto.value;
   }
 ///////////////////////////////////////////////////////////////////////
 if(window.opener.document.busq_avanzada_armo.filtrar_por_forma_pago.checked==true)
   {document.busq_avanzada_muestro.filtrar_por_forma_pago.value=window.opener.document.busq_avanzada_armo.filtrar_por_forma_pago.value;
    document.busq_avanzada_muestro.forma_pago_texto.value=window.opener.document.busq_avanzada_armo.forma_pago_texto.value;
    document.busq_avanzada_muestro.forma_pago.value=window.opener.document.busq_avanzada_armo.forma_pago.value;
   }
 ///////////////////////////////////////////////////////////////////////
 document.busq_avanzada_muestro.submit();
</script>
</body>
</html>
<?
}
else //caso de que ya cargue todos los datos de la pagina padre
{require_once("../../config.php");
 $variables_sesion=array("filter" =>"",
                         "keyword" =>"",
  						 "filtrar_nro_orden" =>"",
 						 "filtrar_estado" =>"",
 						 "filtrar_tipo_orden" =>"",
                         "filtrar_productos" =>"",
                         "filtrar_re_en" =>"",
                         "filtrar_id_licitacion" =>"", //6
                         "filtrar_id_proveedor" =>"", //7
                         "filtrar_id_moneda" =>"", //8
                         "filtrar_id_entidad" =>"", //9
                         "filtrar_orden_prod" =>"", //10
                         "filtrar_nro_factura" =>"", //11
                         "filtrar_fecha_factura" =>"", //12
                         "filtrar_lugar_entrega" =>"", //13
                         "filtrar_fecha_entrega" =>"", //14
                         "filtrar_por_forma_pago" =>"",
                         "ordenar_nro_orden" =>"", //15
                         "ordenar_estado" =>"", //16
                         "ordenar_id_licitacion" =>"", //17
                         "ordenar_proveedor" =>"", //18
                         "ordenar_entidad" =>"", //19
                         "ordenar_orden_prod" =>"", //20
                         "ordenar_nro_factura" =>"", //21
                         "ordenar_fecha_factura" =>"", //22
                         "ordenar_lugar_entrega" =>"", //23
                         "ordenar_fecha_entrega" =>"", //24
                         "nro_orden" =>"", //25
                         "estado" =>"", //26
                         "tipo_orden" =>"", //27
                         "productos" =>"", //28
                         "entregado_recibido" =>"", //28
                         "id_licitacion" =>"", //29
                         "proveedor" =>"", //30
                         "moneda" =>"", //31
                         "entidad" =>"", //32
                         "orden_prod" =>"", //33
                         "nro_factura" =>"", //34
                         "forma_pago_texto" =>"",
                         "forma_pago" =>"",
                         "fecha_factura_2" =>"", //35
                         "fecha_factura_1" =>"", //36
                         "lugar_entrega" =>"", //37
                         "fecha_entrega_2" =>"", //38
                         "fecha_entrega_1" =>"", //39
                         "filtrar_por_monto" =>"", //40
                         "monto_1" =>"", //41
                         "monto_2" =>"", //42
                         "ordenar_monto" =>"" //43
                  );
if ($parametros['contador_pagina'])
{$contador_pag=$parametros['contador_pagina'];
}
else $contador_pag=$contador_pagina;

$contador_pag=$contador_pagina;
variables_form_busqueda("busq_avanzada_muestro_".$contador_pag,$variables_sesion);

require_once("busq_avanzada_consulta.php");
//echo "<br>".$where;

//$keyword = $_POST["keyword"];
//print_r(${"_ses_busq_avanzada_muestro_".$contador_pag});
  //***********************Ordena Por*********************
  $ordeno=1;
  if ($ordenar_nro_orden!='') $ordeno=1;
  if ($ordenar_estado!='') $ordeno=2;
  if ($ordenar_fecha_entrega!='') $ordeno=3;
  if ($ordenar_id_licitacion!='') $ordeno=4;
  if ($ordenar_proveedor!='') $ordeno=6;
  if ($ordenar_entidad!='') $ordeno=7;
  if ($ordenar_orden_prod!='') $ordeno=8;
  if ($ordenar_nro_factura!='') $ordeno=9;
  if ($ordenar_fecha_factura!='') $ordeno=10;
  if ($ordenar_lugar_entrega!='') $ordeno=11;
  if ($ordenar_fecha_entrega!='') $ordeno=12;
  if ($ordenar_monto!='') $ordeno=13;
  //******************************************************
if ($parametros['control']==1)
   {$ordeno=$parametros['ordeno'];
   }



//select * from (
if ($filtrar_re_en) $query="select * from ((";
else $query="select * from (";
//else $query=" ";
$query.="select distinct oc.nro_orden, case when suma_orden is null
         then 0 else suma_orden end as suma_orden,
         oc.id_licitacion, oc.id_proveedor, oc.id_moneda, oc.id_entidad,
         oc.orden_prod, oc.fecha_entrega, oc.fecha_factura, oc.lugar_entrega,
         oc.cliente, oc.id_plantilla_pagos, oc.estado, oc.notas, oc.nro_factura, oc.nrocaso,
         oc.flag_honorario, oc.flag_stock, oc.internacional,
         razon_social, moneda.simbolo, moneda.nombre as nombre_moneda,
         licitacion.id_licitacion as nro_licitacion, entidad.nombre as nombre_entidad, licitacion.es_presupuesto as presupuesto";
if ($filter=="descripcion_prod" || $filter=="all") $query.=", (fila.descripcion_prod || fila.desc_adic) as descripcion_prod";
if ($filtrar_por_forma_pago) $query.=", plantilla_descripcion, tipo_descripcion, dias_pagos";
if ($filtrar_productos=="filtrar_productos") $query .=", tipo";
$query.=" from compras.orden_de_compra oc
         left join (select nro_orden, sum(cantidad*precio_unitario) as suma_orden from compras.fila group by (nro_orden))
         as montos using(nro_orden)
         left join licitaciones.entidad using(id_entidad)
         left join licitaciones.moneda using(id_moneda)
         left join compras.fila using(nro_orden)
         left join licitaciones.licitacion using(id_licitacion)
         left join general.proveedor using(id_proveedor)";

if ($filtrar_por_forma_pago) $query .=" left join (select id_plantilla_pagos, plantilla_pagos.descripcion as plantilla_descripcion, tipo_pago.descripcion as tipo_descripcion, forma_de_pago.dias as dias_pagos
                                        from compras.plantilla_pagos
                                        left join compras.pago_plantilla using(id_plantilla_pagos)
                                        left join compras.forma_de_pago using(id_forma)
                                        left join compras.tipo_pago using(id_tipo_pago)) as pagos using(id_plantilla_pagos)";

if ($filtrar_productos=="filtrar_productos") $query .="left join productos using (id_producto)";



///////////////////////////////
if ($filtrar_re_en) $query.=") as t1 left join (
select nro_orden,sum(recibidos) as recibidos_oc,sum(entregados) as entregados_oc,sum(comprados) as comprados_oc,
case when sum(comprados)-sum(recibidos)=0 then -1 else sum(comprados)-sum(recibidos) end as falta_recibir,
case when sum(comprados)-sum(entregados)=0 then -1 else sum(comprados)-sum(entregados) end as falta_entregar
from (select nro_orden,id_fila,sum(cantidad) as comprados
from compras.fila where (es_agregado isnull or es_agregado<>1)
group by id_fila,nro_orden) f left join (select id_fila,sum(cantidad) as recibidos
from compras.recibido_entregado where ent_rec=1 group by id_fila) r using(id_fila)
left join (select id_fila,sum(cantidad) as entregados
from compras.recibido_entregado where ent_rec=0 group by id_fila) e using(id_fila) group by nro_orden) t2 using(nro_orden))";
else $query.=") as t1";
///////////////////////////////

//echo "<br><br>".$query;

$orden = array
(		"default" => "$ordeno",
        "default_up"=>"0",
		"1" => "nro_orden",
		"2" => "estado",
		"3" => "fecha_entrega",
		"4" => "id_licitacion",
		"5" => "cliente",
		"6" => "razon_social",
        "7" => "nombre_entidad",
        "8" => "orden_prod",
        "9" => "nro_factura",
        "10" => "fecha_factura",
        "11" => "lugar_entrega",
        "12" => "fecha_entrega",
        "13" => "suma_orden"
);

$filtro = array
(
		"nro_orden"=>"Nº de Orden",
		"nro_factura"=>"Nº de Factura",
		"id_licitacion"=>"ID Licitacion",
		"lugar_entrega"=>"Lugar de entrega",
		"cliente"=>"Cliente",
		"notas"=>"Comentarios",
		"razon_social"=> "Proveedor",
		"descripcion_prod"=>"Productos"
		//"(fila.descripcion_prod || fila.desc_adic)"=>"Productos"
);
$contar="buscar";

$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "suma_orden",
 		"mask" => array ("\$","U\$S")
);


echo $html_header;


?>
<form name="busq_avanzada_muestro_<?=$contador_pag;?>" action="busq_avanzada_muestro_<?=$contador_pag; ?>.php" method="post">


<?
echo "<div style='visibility:hidden'>";
$link_tmp['contador_pagina']=$contador_pag;



list($sql,$total_pedidos,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$contar,$sumas);



$resultado_consulta = sql($sql) or fin_pagina();




echo "</div>";
//echo "<br>".$_POST['filter']."<br>";
//echo "<br>".$sql."<br>";?>

<table width="100%">
 <tr>
  <td align="center"><font size="4"><b>Resultados de la Busqueda</b></font></td>
 </tr>
 <tr>
  <td align="right"><INPUT type="button" value="Cerrar" onclick="window.close();"></td>
 </tr>
</table>


<table align="center" class="bordes" cellpadding="2" cellspacing="2">
<tr id=ma>
  <td align="left" colspan="3">
   <b>Total:</b> <?=$total_pedidos?>
   <input name="total_pedidos" type="hidden" value=<?=$total_pedidos?>>
  </td>
  <td colspan="4">
  	Totales: <?=$suma?>
   <!--table width="100%">
    <tr>
     <td>
      Total <input type="text" class="text_4" name="total_pesos">
     </td>
     <td align="right">
      Total <input type="text" class="text_4" name="total_dolares">
     </td>
    </tr>
   </table-->
  </td>
  <?
   if ($filtrar_re_en)
      {
  ?>
  <td align="right" colspan="5">
   <?
      }
    else {
    	?>
    <td align="right" colspan="4">
    <?}?>
   <?=$link_pagina?>
  </td>
 </tr>
 <tr id=mo>
  <td></td>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>1,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Nro. de Orden</b></a></td>
  <td><b>Tipo</b></td>
  <?if ($filtrar_re_en) {?><td title="Productos Recibidos/Entregados"><b>R/E</b></td><?}?>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>2,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Estado</b></a></td>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>4,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Id. Licitación</b></a></td>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>12,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Fecha Entrega</b></a></td>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>7,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Cliente</b></a></td>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>6,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Proveedor</b></a></td>
  <td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("ordeno"=>13,"control"=>1,"up"=>$up,"contador_pagina"=>$contador_pag))?>'><b>Monto</b></a></td>
 </tr>
 <?
 //$total_pesos=0;
 //$total_dolares=0;
 while(!$resultado_consulta->EOF)
 {

 //Traigo los productos de la orden de compra
 $sql="select moneda.simbolo,fila.precio_unitario,fila.cantidad,(fila.descripcion_prod || fila.desc_adic) as descripcion_prod from fila join orden_de_compra using(nro_orden) join moneda using(id_moneda) where nro_orden=".$resultado_consulta->fields['nro_orden'];
 $productos_orden=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 $title="title='";
 while(!$productos_orden->EOF)
 {
  $title.=$productos_orden->fields['descripcion_prod']."\n";
  $productos_orden->MoveNext();
 }
 $title.="'";
 ?>

 <tr <?=atrib_tr(); echo $title;?> >
 <td><input type=checkbox class='estilos_check' name=chk value=1 onclick="javascript:(this.checked)?Mostrar('fila_<?=$resultado_consulta->fields['nro_orden'];?>'):Ocultar('fila_<?=$resultado_consulta->fields['nro_orden']?>');"></td>
 <a href="<? echo encode_link("ord_compra.php",array("nro_orden"=>$resultado_consulta->fields['nro_orden'])); ?>" target="_blank">
  <td align="center"><?=$resultado_consulta->fields['nro_orden']?></td>
  <?$tipo="Otro";
  $titulo="Orden no Asociada";
  if ($resultado_consulta->fields['id_licitacion']!='' && $resultado_consulta->fields['licitacion.es_presupuesto']==1)
     {$tipo="Pres";
      $titulo="Presupuesto";
     }
  elseif ($resultado_consulta->fields['id_licitacion']!='')
         {$tipo="Lic";
          $titulo="Licitacion";
         }
  if ($resultado_consulta->fields['nrocaso']!='')
     {$tipo="ServT";
      $titulo="Servicio Técnico";
     }
  if ($resultado_consulta->fields['flag_honorario']==1)
     {$tipo="HST";
      $titulo="Honorario Servicio Técnico";
     }
  if ($resultado_consulta->fields['flag_stock']==1)
     {$tipo="Stock";
      $titulo="Stock Coradir";
     }
  if ($resultado_consulta->fields['orden_prod']!='')
     {$tipo="RMA";
      $titulo="RMA de Producción";
     }
  if ($resultado_consulta->fields['internacional']==1)
     {$tipo="INT";
      $titulo="Orden de Compra Internacional";
     }
?>
  <td title="<?=$titulo?>"><?=$tipo?></td>

  <?if ($filtrar_re_en) {?><td align="center"><?if ($resultado_consulta->fields['recibidos_oc']=="") echo "0";else echo $resultado_consulta->fields['recibidos_oc']?>/<?if ($resultado_consulta->fields['entregados_oc']=="") echo "0"; else echo $resultado_consulta->fields['entregados_oc'] ?></td><?}?>
  <?if ($resultado_consulta->fields['estado']=='p') $estado="Pendiente";
    if ($resultado_consulta->fields['estado']=='a') $estado="Autorizada";
    if ($resultado_consulta->fields['estado']=='r') $estado="Rechazada";
    if ($resultado_consulta->fields['estado']=='e') $estado="Enviada";
    if ($resultado_consulta->fields['estado']=='d') $estado="Pagadas Parcialmente";
    if ($resultado_consulta->fields['estado']=='g') $estado="Pagadas Totalmemnte";
    if ($resultado_consulta->fields['estado']=='n') $estado="Anuladas";
    if ($resultado_consulta->fields['estado']=='u') $estado="Por Autorizar";
  ?>
  <td align="center"><?=$estado?></td>

  <td align="center"><?=$resultado_consulta->fields['id_licitacion']?></td>
  <?
  $fecha_mostrar=fecha($resultado_consulta->fields['fecha_entrega']);
  ?>
  <td align="center"><?=$fecha_mostrar?></td>
  <td align="center"><?=$resultado_consulta->fields['cliente']?></td>
  <td align="center"><?=$resultado_consulta->fields['razon_social']?></td>
  <td align="center"><b><?=$resultado_consulta->fields['simbolo']?> </b><?=number_format($resultado_consulta->fields['suma_orden'],2,".","")?></td>
  <?
  //acumulamos los montos de las OC buscadas para luego mostrarlo en los totales
 // if($resultado_consulta->fields['simbolo']=="$")
 //  $total_pesos+=$resultado_consulta->fields['suma_orden'];
 // elseif($resultado_consulta->fields['simbolo']=="U\$S")
 //  $total_dolares+=$resultado_consulta->fields['suma_orden'];
   ?>
 </tr>
 </a>
   <tr>
   <td></td>
   <td colspan=8>
   <div id='fila_<?=$resultado_consulta->fields['nro_orden']?>' style='display:none'>
  <?
   //No hay productos para mostrar
   if ($productos_orden->recordcount()<=0)
   {
   ?>
   <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspading=0 cellpading=0 class="bordes" border=0>
   <tr><td colspan=6 align=center><b>No hay productos a mostrar en esta orden de compra</b></td></tr>
   </table>
   <?
   }//del then
   else
   {
   ?>
   <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspacing=0 cellpading=0 border=1 bordercolor=#ACACAC>
   <tr>
   <td id=ma align=center width="10%">Cantidad</td>
   <td id=ma align=center width="70%">Producto</td>
   <td id=ma align=center width="20%">Precio</td>
   </tr>
   <?
    //muestro los productos
    $productos_orden->Move(0);
   for($j=0;$j<$productos_orden->recordcount();$j++)
   {
   ?>
   <tr>
   <td align=center><b><?=$productos_orden->fields["cantidad"]?></b></td>
   <td align=center><b><?=$productos_orden->fields["descripcion_prod"]?></b></td>
   <td align=center><b><?=$productos_orden->fields['simbolo']." ".$productos_orden->fields['precio_unitario'];?></b></td>
   </tr>
   <?
   $productos_orden->movenext();
   }
   ?>
   </table>
   <?
    } //del else
   ?>
   </div>
   </td>
   </tr>
 <?
  $resultado_consulta->MoveNext();
 }

 ?>
</table>
<TABLE align="right">
<TR>
<TD><INPUT type="button" value="Cerrar" onclick="window.close();"></TD>
</TR>
</TABLE>
<!--Seteamos los totales para mostrarlos por pantalla-->
<script>
<?/* document.all.total_pesos.value='$ <?=formato_money($total_pesos)?>';
// document.all.total_dolares.value='U$S <?=formato_money($total_dolares);?>';*/
?>
</script>

<?
}//del else en caso de que ya cargue todos los datos de la pagina padre
fin_pagina();
?>
