<?php

/*
Autor: quique

MODIFICADA POR
$Author: 
$Revision: 
$Date: 2006/05/09 18:32:38 $
*/
require_once("../../config.php");
echo $html_header;
variables_form_busqueda("reportes_prod");
$pag=$parametros['pag'];
$orden = array(
		"default" => "1",
		"1" => "entrega_estimada.fecha_estimada"
		
	);

$filtro = array(
		"licitacion.id_licitacion" => "ID Licitación",
		"entrega_estimada.fecha_estimada" => "Fecha Entrega",
		"usuarios.iniciales" => "Iniciales Líder",
		"entidad.nombre" => "Nombre Cliente"
		
	);
?>
<script>
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla,nro)
{oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 if (obj_tabla.style.display=='none')
    {obj_tabla.style.display='inline';
     oimg.show=0;
     oimg.src=img_ext;
    }
 else
    {obj_tabla.style.display='none';
    oimg.show=1;
	oimg.src=img_cont;
    }
}
</script>
<form name='form1' method='post' action="reportes_produccion.php"> 
<table width='100%'>
 <tr align="center">
  <td align="center">
  <b><font color="Blue" size="3">Reportes Producción <br> PM y Ordenes de Compra Sin Recibir</b></font>
  </td>
 </tr>
</table>
<br>
<?
$query="select licitaciones.licitacion.id_licitacion,entrega_estimada.fecha_estimada,entrega_estimada.id_entrega_estimada,sistema.usuarios.iniciales,licitaciones.entidad.nombre
        from licitaciones.licitacion LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion)
        LEFT JOIN licitaciones.entidad USING (id_entidad)
        LEFT JOIN sistema.usuarios on usuarios.id_usuario=lider";
if($_POST['keyword'] || $keyword){// en la variable de sesion para keyword hay datos)
	$where="$where entrega_estimada.finalizada=0 AND licitaciones.licitacion.borrada='f'";
	}
else
   	$query.=" where entrega_estimada.finalizada=0 AND licitaciones.licitacion.borrada='f'";
?>
<table width="100%">
<tr>
<td align="center">
<?
list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");
$result = sql($sql,"error en busqueda") or die("$sql<br>Error en form busqueda");
echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
?>
</td>
</tr>
</table>
<?
$cont1=0;
while(!$result->EOF)
{
$id=$result->fields['id_licitacion'];
$id_entrega_estimada=$result->fields['id_entrega_estimada'];
$lid=$result->fields['iniciales'];
$fec=Fecha($result->fields['fecha_estimada']);
$cliente=$result->fields['nombre'];
$cliente=cortar($cliente,80);
$sql4="select compras.fila.nro_orden,compras.fila.desc_adic,compras.fila.cantidad,recibidos,entregados,compras.fila.id_fila,descripcion_prod
,compras.orden_de_compra.fecha_entrega,general.proveedor.razon_social,compras.orden_de_compra.estado
 from compras.fila left join(
 select id_fila, sum(case when ent_rec=1 then
					case when cantidad is null then 0 else cantidad end
					else 0 end)as recibidos, 
					sum(case when ent_rec=0 then
					case when cantidad is null then 0 else cantidad end
					else 0 end)as entregados
				from compras.recibido_entregado
				group by id_fila) r using(id_fila)
left join compras.orden_de_compra using(nro_orden)
left join general.proveedor using(id_proveedor)
where (es_agregado=0 or es_agregado is null) and compras.orden_de_compra.id_entrega_estimada=$id_entrega_estimada and estado<>'n'
group by fila.nro_orden,desc_adic,fila.cantidad,recibidos,entregados,id_fila,descripcion_prod,fecha_entrega,razon_social,estado
order by fila.nro_orden desc";
$resultado4=sql($sql4, "<br>$sql4")or fin_pagina();
$ped_mat="select detalle_movimiento.descripcion,detalle_movimiento.cantidad,movimiento_material.id_licitacion,
        movimiento_material.id_movimiento_material,detalle_movimiento.id_detalle_movimiento,
		case when tmp0.cantidad is null then 0 else tmp0.cantidad end as pedido_cant
	    from mov_material.detalle_movimiento
		left join mov_material.movimiento_material using(id_movimiento_material)
		left join (select id_detalle_movimiento, SUM(cantidad)as cantidad
			from mov_material.recibidos_mov
			where ent_rec=0 group by id_detalle_movimiento
		)as tmp0 using(id_detalle_movimiento)
	    where id_licitacion=$id and mov_material.movimiento_material.estado <> 3";
$pedido=sql($ped_mat,"No se pudo recuperar los pedidos de materiales") or fin_pagina();
?>
<?//****************************************************************?>
<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_<?=$cont1?>" src="<?=$img_cont?>" border=0 align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.hola_<?=$cont1?>,<?=$cont1?>);" >
  </td>
  <td align="left">
   <b>licitacion N° <?=$id?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Entrega:<?=$fec?>&nbsp;&nbsp;&nbsp;&nbsp; Lider:<?if (strlen($lid)==2){ echo "$lid";?>&nbsp;&nbsp;<?}else echo $lid;?>&nbsp;&nbsp;&nbsp;&nbsp; Cliente:<?=$cliente?></b>
  </td>
 </tr>
</table>
<table id="hola_<?=$cont1?>" style="display:none;border:thin groove" border=1 rules="none" width="100%">
<tr>
<td>
<table border="1" width="100%" style="display:inline;border:thin groove">
<?if ($pedido->RecordCount()==0)
 {
?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Pedido de materiales</b></font>
  </td>
 </tr>
 <?
 }
 else
 {
 ?>
 <tr>
  <td align="center" colspan="3">
   <font size="3" color="Red"><b>Pedido de materiales</b></font>
  </td>
 </tr>
<tr>
<td width="10%" align="center"><b>Cantidad</b></td>
<td width="60%" align="center"><b>Descripcion</b></td>
<td width="10%" align="center"><b>N° de Pedido</b></td>
</tr>
<?
$a=0;
while (!$pedido->EOF)
{
$nro=$pedido->fields['id_movimiento_material'];
$id_det=$pedido->fields['id_detalle_movimiento'];

$cant=$pedido->fields["pedido_cant"];
$cantidad1=$pedido->fields['cantidad'];
//$refer=encode_link("../mov_material/detalle_movimiento.php",Array("id"=>"$nro"));
	if($cant<$cantidad1)
	{
	$a++;	
    ?>
    <tr>
    <?if($cant==$cantidad1)?>
    <td align="left"><b><?=$pedido->fields['cantidad']?> </b></b>
    </td>
    <td align="left"><b><?=$pedido->fields['descripcion'];?></b></b>
    </td>
    </td>
    <td align="left">
    <b><a href="<?=encode_link("../mov_material/detalle_movimiento.php",Array("id"=>"$nro"));?>" target="_blank"><?=$pedido->fields['id_movimiento_material']?></a></b>
    </td>
    </tr>
<?
	}
$pedido->MoveNext();
}
if($a==0)
{
?>
<tr>
 <td align="center" colspan="3">
   <font size="3" color="Green"><b>Todos Recibidos</b></font>
  </td>
</tr>
<?
}
}
?>
<?//****************************************************************?>
<table border="1" width="100%" style="display:inline;border:thin groove">
<?if ($resultado4->RecordCount()==0)
{
?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Ordenes para Mostrar</b></font>
  </td>
 </tr>
 <?
}
else
{
 ?>
  <tr>
  <td align="center" colspan="3">
   <font size="3" color="Red"><b>Orden de Compra</b></font>
  </td>
 </tr>
<tr>
<tr>
<td width="20%" align="center"><b>Orden de Compra</b></td>
<td width="80%" align="center" colspan="2"><b>Productos</b></td>
</tr>
<?
$resultado4->Move(0);
$ctrl=0;
$t=0;
while (!$resultado4->EOF)
{ 	
 if (substr_count($resultado4->fields['razon_social'],"Stock")>0) $cantidad_usada=$resultado4->fields['entregados'];
    else $cantidad_usada=$resultado4->fields['recibidos'];
	 	$faltaran=($resultado4->fields['cantidad']-$cantidad_usada);
     if ($faltaran<$resultado_recibidos[$i]['cantidad_pedida']) $color="yellow";
     if ($faltaran!=0) {
     $t++;	
    ?>
     <tr bgcolor="<?=$bgcolor_out;?>">
     <td colspan="3">
     <table width="100%" >
      <tr>
       <td align="left" width="5%" <? if($resultado4->fields['estado']=="p" || $resultado4->fields['estado']=="u" || $resultado4->fields['estado']=="a") echo "bgcolor='violet' title='OC en estado Pendiente o Para Autorizar o Autorizada'"?>>
        <b><FONT color="Blue"><a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado4->fields['nro_orden']));?>" target="_blank"><?=$resultado4->fields['nro_orden'];?></a></font></b>
       </td>
       <td align="left" width="40%">
        <b>Proveedor: <FONT color="Blue">  <?=$resultado4->fields['razon_social'];?></font></b>
       </td>
       <td align="left" width="25%">
        <b>Fecha Entrega: <FONT color="Blue"> <?=fecha($resultado4->fields['fecha_entrega']);?></font></b>
       </td>
       <td align="left" width="30%">
        <b>(F)=Cantidad que falta Entregar</b>
       </td>
       <br>
      </tr>
     </table>
    </td>
    </tr>
    <tr>
    <td bgcolor="<?=$color?>"><b>Cant.:<font color="blue">&nbsp;<?=$resultado4->fields['cantidad'];?>&nbsp</font> (F <font color="Blue"><?=$resultado4->fields['cantidad']-$cantidad_usada?></font>)
    </td>
    <td colspan="2"><b><?=$resultado4->fields['descripcion_prod'];?>&nbsp;<?=$resultado4->fields['desc_adic'];?></td>
    <td>
    </tr>
   <?
 }
$resultado4->MoveNext();
}
if($t==0)
{?>
<tr>
 <td align="center" colspan="3">
   <font size="3" color="Green"><b>Todos Recibidos</b></font>
  </td>
</tr>
<?
}
}
?>
</table>
</td>
</tr>
</table>
<!--/////////////////////-->
<?
$cont1++;
$result->MoveNext();
}
?>
</table>
</table>
</table>
<TABLE align="center">
<tr>
<td>
<input type=button name='cerrar_ventana' value='Cerrar Ventana'onclick="window.close();"> 
</td>
</tr>
</TABLE>
</form>
<?
fin_pagina();
?>