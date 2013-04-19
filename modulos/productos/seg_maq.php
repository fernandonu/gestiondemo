<?
/*
Autor: GACZ

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2006/01/04 10:48:33 $
*/

/* get_data() BY GACZ
@id_lic es el id de la licitacion a buscar los datos
@retorna un arreglo asociativo con los resulset de las consultas

*/
require_once("../../config.php");

define("PATH_LIC","../licitaciones");
define("PATH_OP","../ordprod");
define("PATH_OC","../ord_compra");
define("PATH_F","../facturas");
define("PATH_R","../remitos");
define("PATH_IMG","../../imagenes");

function get_data($ordprod)
{
/*	$q="select * from
	licitacion l join
	orden_de_produccion op on l.id_licitacion=op.id_licitacion and l.id_licitacion=$id_lic join
	orden_de_compra oc on l.id_licitacion=oc.id_licitacion join
	facturas f on ll.id_licitacion=f.id_licitacion join
	remitos r l.id_licitacion=r.id_licitacion or f.id_factura=r.id_factura	";
*/
	//orden de produccion
	$q[]="select * from orden_de_produccion left join renglon using(id_renglon) where nro_orden=$ordprod";
	$res[op] =	 sql($q[0]) or fin_pagina();//orden de produccion
	$id_lic=$res[op]->fields[id_licitacion];

	//licitacion
	$q[]="select *,e.nombre as nbre_entidad from licitacion l join
	entidad e on e.id_entidad=l.id_entidad where id_licitacion=$id_lic ";

	//ordenes de compra
	$q[]="select oc.*,m.simbolo,p.razon_social as proveedor,oc_total.total from orden_de_compra oc join
	proveedor p on oc.id_proveedor=p.id_proveedor join
	moneda m on m.id_moneda=oc.id_moneda left join
	(select nro_orden,sum(cantidad*precio_unitario) as total from fila group by nro_orden) oc_total on oc_total.nro_orden=oc.nro_orden
	where id_licitacion=$id_lic and oc.estado!='a' order by nro_orden";

	//facturas
	$q[]="select *,m.simbolo from facturas f join
	moneda m on m.id_moneda=f.id_moneda	left join
	(select id_factura,sum(cant_prod*precio) as total from items_factura group by id_factura) f_total on f_total.id_factura=f.id_factura
	where id_licitacion=$id_lic and f.estado!='a' order by tipo_factura,nro_factura";

	//remitos
	$q[]="select r.*,r_total.total,m.simbolo,f.id_factura,f.nro_factura,f.tipo_factura from remitos r left join
	facturas f on f.id_factura=r.id_factura join
	moneda m on m.id_moneda=f.id_moneda	left join
	(select id_remito,sum(cant_prod*precio) as total from items_remito group by id_remito) r_total on r_total.id_remito=r.id_remito
	where r.id_licitacion=$id_lic and r.estado!='a' order by nro_remito";

	$res[l]  =   sql($q[1]) or fin_pagina();//licitacion
	$res[oc] =	 sql($q[2]) or fin_pagina();//ordenes de compra
	$res[f]  =	 sql($q[3]) or fin_pagina();//facturas
	$res[r]  =	 sql($q[4]) or fin_pagina();//remitos

	return $res;
}

extract($parametros);
//echo $ordprod;
$datos=get_data($ordprod);

echo $html_header;
?>
<!--
<html>
<head>
<title>Seguimiento de maquinas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../lib/estilos.css" rel="stylesheet" type="text/css">
-->
<style type="text/css">
<!--
-->
</style>
</head>
<script src="../../lib/funciones.js"></script>
<script>
var tablas=new Array();
var img_mas='<?=$img_mas=PATH_IMG.'/mas.gif' ?>';
var img_menos='<?=$img_menos=PATH_IMG.'/menos.gif' ?>';
tablas['licitacion']=true;//true visible;false no visible
tablas['ordprod']=true;
tablas['ordcompra']=true;
tablas['facturas']=true;
tablas['remitos']=true;
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
 src.style.backgroundColor=color_default;src.style.cursor="default";
}
</script>
<body bgcolor="#E0E0E0">
<form name="form1" method="post" action="">
  <table width="100%" border="1" cellpadding="1" cellspacing="0" bordercolor="#000000">
    <tr>
      <td id=mo style="text-align:right"><font size="3">Nº Serial</font></td>
      <td bgcolor=<?=$bgcolor3?>><!--Serial que se paso por parametros&nbsp;--><tt>&nbsp;</tt><b><?=(($datos[op]->fields[cantidad] >1)?$datos[op]->fields[nserie_desde]."......".$datos[op]->fields[nserie_hasta]:$datos[op]->fields[nserie_desde]);  ?></b></td>
    </tr>
<a target="_blank"  href="<?= encode_link(PATH_LIC."/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$datos[l]->fields[id_licitacion])) ?>">
    <tr style="cursor:hand" title="Ver Detalles de la Licitación">
      <td id=mo style="text-align:right"><font size="3">Licitacion </font></td>
<!-- onmouseover="sobre(this,'white')" onmouseout="bajo(this,false)"-->
		<td bgcolor=<?=$bgcolor3?>><tt>&nbsp;</tt><b>ID:</b>&nbsp;<?=$datos[l]->fields[id_licitacion] ?>&nbsp;&nbsp;&nbsp; <b>Nº</b>&nbsp; <?= $datos[l]->fields[nro_lic_codificado]?></td>
    </tr>
</a>
    <tr>
      <td width="21%" id=mo style="text-align:right"><font size="3"  >Entidad</font></td>
      <td width="79%" bgcolor=<?=$bgcolor3?>><!--Entidad de la licitacion--><tt>&nbsp;</tt><?= $datos[l]->fields[nbre_entidad] ?></td>
    </tr>
    <tr>
      <td id=mo style="text-align:right"><font size="3">Cliente</font></td>
      <td bgcolor=<?=$bgcolor3?>><!--Cliente de la factura--><tt>&nbsp;</tt><?=$datos[f]->fields[cliente] ?></td>
    </tr>
    <tr>
      <td id=mo style="text-align:right"><font size="3">Fecha entrega</font></td>
      <td bgcolor=<?=$bgcolor3?>><!--Fecha de entrega de la licitacion--><tt>&nbsp;</tt><?=  date2("L",$datos[l]->fields[fecha_entrega]); ?></td>
    </tr>
  </table>
<br>
 <br>

<!-- Tabla de Licitacion
 <table id=tabla_lic width="100%" border="0" cellpadding="1" cellspacing="1" bordercolor="#000000">
    <tr align="center" >
      <td width="14%" colspan="2"  style="text-align:left;color:#006699;font-weight:bold;" ><font size="3" >Licitaci&oacute;n
        </font> <img src="<?=$img_menos ?>" width="12" height="12" align="absmiddle" style="cursor:hand;" onclick="if (tablas['licitacion']=!tablas['licitacion']) { this.src=img_menos ;Mostrar('div_licitacion')} else {this.src=img_mas; Ocultar('div_licitacion');}"></td>
    </tr>
    <tr align="center">
      <td colspan="2" ><div id=div_licitacion style="display:block">
          <table width="100%" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000" dwcopytype="CopyTableRow">
            <tr id=mo>
      <td width="10%">ID</td>
      <td width="23%">Fecha Apertura</td>
      <td width="36%">Numero</td>
      <td width="31%">Monto Ganado</td>
    </tr>
    <tr id=ma onmouseover="sobre(this,'white')" onmouseout="bajo(this,'#cccccc')" >
      <td>&nbsp;</td>
      <td>12-15-18</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table></div></td>
    </tr>
  </table>
  <br>
-->
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr align="center" >
      <td width="30%" colspan="2" style="text-align:left;color:#006699;font-weight:bold;" ><font size="3">Orden
        de Producci&oacute;n
        </font>
        <!-- Imagen del mas para expandir
			<img src="<?=$img_menos ?>" width="12" height="12" align="absmiddle" style="cursor:hand;" onclick=" if (tablas['ordprod']=!tablas['ordprod']) {this.src=img_menos; Mostrar('div_ordprod')}; else {this.src=img_mas; Ocultar('div_ordprod')}">
			-->
		</td>
    </tr>
    <tr align="center" >
      <td colspan="2">
	  <div id=div_ordprod style="display:block">
          <table width="100%" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
            <tr id=mo>
            <td width="8%">Nro</td>

            <td width="12%">Serial/es</td>
            <td width="19%">Producto</td>
            <!--<td width="22%">Modelo</td>-->
            <td width="10%">Cantidad</td>
            <td width="10%">Garantia</td>
<!--            <td width="2%">PDF</td> -->
          </tr>
<? // eval("echo strtoupper(\$datos[op]->fields[nro_orden]);");
?>
<a target="_blank" href="<?echo encode_link(PATH_OP."/ordenes_nueva.php",array("nro_orden"=>$datos[op]->fields["nro_orden"],"modo"=>"modificar")); ?>" >
          <!--<tr id=ma onmouseover="sobre(this,'white')" onmouseout="bajo(this,'#cccccc')">-->
          <tr <?echo atrib_tr();?>>
            <td><?=$nro_orden_de_produccion_lista=$datos[op]->fields[nro_orden] ?></td>
            <td><?=(($datos[op]->fields[cantidad] >1)?$datos[op]->fields[nserie_desde]."<br>...<br>".$datos[op]->fields[nserie_hasta]:$datos[op]->fields[nserie_desde]); ?></td>
            <td><?=$datos[op]->fields[desc_prod] ?></td>
            <!--<td><?=$datos[op]->fields[modelo] ?></td>-->
            <td><?=$datos[op]->fields[cantidad] ?></td>
            <td><?=($datos[op]->fields[garantia])?$datos[op]->fields[garantia]:"&nbsp;" ?></td>
<!--            <td>&nbsp;</td> -->
          </tr>
</a>
        </table>
		</div>
		</td>
    </tr>
  </table>
  <br>

  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr align="center" >
      <td width="27%" align="left" colspan="2" style="text-align:left;color:#006699;font-weight:bold;" ><font size="3">Ordenes
        de Compra </font>
<? if ($datos[oc]->RecordCount()) { ?>
        <img src="<?=$img_menos ?>" width="12" height="12" align="absmiddle" style="cursor:hand;" onclick="if (tablas['ordcompra']=!tablas['ordcompra']) {this.src=img_menos; Mostrar('div_ordcompra');} else {this.src=img_mas; Ocultar('div_ordcompra')}">
 </td>
    </tr>
    <tr align="center" >
      <td colspan="2"><div id=div_ordcompra style="display:block">
          <table width="100%" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000" dwcopytype="CopyTableRow">
            <tr id=mo>
      <td width="10%">Nro</td>
      <td width="35%">Proveedor</td>
      <td width="40%">Cliente</td>
      <td width="20%">Monto</td>
<!--      <td width="2%">PDF</td> -->
    </tr>
<?
	}
	else
	 echo "&nbsp;-&nbsp;No se encontraron Ordenes de Compra</td></tr>";

    while (!$datos[oc]->EOF)
	{
?>
<a target="_blank" href="<?=encode_link(PATH_OC."/ord_compra.php",array('nro_orden'=>$datos[oc]->fields[nro_orden])) ?>">
    <!--<tr id=ma onmouseover="sobre(this,'white')" onmouseout="bajo(this,'#cccccc')">-->
    <tr <?echo atrib_tr();?>>
            <td><?=$datos[oc]->fields[nro_orden] ?></td>
            <td><?=$datos[oc]->fields[proveedor] ?></td>
            <td><?=$datos[oc]->fields[cliente] ?></td>
            <td style="text-align:right" ><?=$datos[oc]->fields[simbolo]." ".number_format($datos[oc]->fields[total],2,",",".") ?></td>
<!--            <td>&nbsp;</td>  -->
          </tr>
</a>
<?
	 $datos[oc]->MoveNext();
	}
?>

    </table></div></td>
    </tr>
  </table>
  <br>
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr align="center" style="text-align:left;color:#006699;font-weight:bold;" >
      <td width="100%" align="left"><font size="3">Facturas </font>
<? if ($datos[f]->RecordCount()) { ?>
			<img src="<?=$img_menos ?>" width="12" height="12" align="absmiddle" style="cursor:hand;" onclick="if (tablas['facturas']=!tablas['facturas']) {this.src=img_menos; Mostrar('div_facturas');} else {this.src=img_mas; Ocultar('div_facturas')}">
	   </td>
    </tr>
<tr><td><div id=div_facturas style="display:block">
          <table width="100%" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
            <tr id=mo>
    <td width="25%">Tipo y N&ordm;</td>
    <td width="13%">Fecha</td>
    <td width="48%">Cliente</td>
    <td width="14%" >Monto</td>
  </tr>
<?
	}
	else
	 echo "&nbsp;-&nbsp;No se encontraron Facturas</td></tr>";

while (!$datos[f]->EOF)
{
?>

<a target="_blank" href="<?=encode_link(PATH_F."/factura_nueva.php",array('id_factura'=>$datos[f]->fields[id_factura])) ?>">
  <!--<tr id=ma onmouseover="sobre(this,'white')" onmouseout="bajo(this,'#cccccc')">-->
  <tr <?echo atrib_tr();?>>
    <td><?= strtoupper($datos[f]->fields[tipo_factura])."-".$datos[f]->fields[nro_factura] ?></td>
    <td><?= date_spa("j/m/Y", $datos[f]->fields[fecha_factura]) ?></td>
    <td><?= $datos[f]->fields[cliente] ?></td>
    <td style="text-align:right"> <?= $datos[f]->fields[simbolo]." ". number_format($datos[f]->fields[total],2,",",".") ?></td>
  </tr>
</a>
<?
  $datos[f]->MoveNext();
}
?>
</table></div></td></tr>
  </table>
  <br>
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr align="center" style="text-align:left;color:#006699;font-weight:bold;" >
      <td width="100%" align="left"><font size="3">Remitos </font>
<? if ($datos[r]->RecordCount()) { ?>
 	<img src="<?=$img_menos ?>" width="12" height="12" align="absmiddle" style="cursor:hand;" onclick="if (tablas['remitos']=!tablas['remitos']) {this.src=img_menos;Mostrar('div_remitos');} else {this.src=img_mas; Ocultar('div_remitos')}">
	  </td>
    </tr>
	<tr><td><div id=div_remitos style="display:block">
          <table width="100%" border="1" cellpadding="1" cellspacing="1" bordercolor="#000000">
            <tr id=mo>
    <td width="11%">N&ordm; Remito</td>
    <td width="18%">Factura (Tipo y N&ordm;)</td>
    <td width="12%">Fecha</td>
    <td width="30%">Cliente</td>
    <td width="15%">Monto</td>
  </tr>

<? }
	else
	 echo "&nbsp;-&nbsp;No se encontraron Remitos</td></tr>";

while (!$datos[r]->EOF)
{
?>

<a target="_blank" href="<?=encode_link(PATH_R."/remito_nuevo.php",array('id_remito'=>$datos[r]->fields[id_remito])) ?>">
  <!--<tr id=ma onmouseover="sobre(this,'white')" onmouseout="bajo(this,'#cccccc')">-->
  <tr <?echo atrib_tr();?>>
    <td><?= ($datos[r]->fields[nro_remito])?$datos[r]->fields[nro_remito]:"&nbsp;" ?></td>
    <td><?= ($datos[r]->fields[id_factura])?strtoupper($datos[r]->fields[tipo_factura])."-".$datos[r]->fields[nro_factura]:"&nbsp;" ?></td>
    <td><?= date_spa("j/m/Y", $datos[r]->fields[fecha]) ?></td>
    <td><?= $datos[r]->fields[cliente] ?></td>
    <td style="text-align:right"> <?= $datos[r]->fields[simbolo]." ". number_format($datos[r]->fields[total],2,",",".") ?></td>
  </tr>
</a>
<?
 $datos[r]->MoveNext();
}
?>
</table></div></td></tr>
  </table>
<?
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
$colores=array();
$colores[0]=array("color"=>"#FFFFC0","texto"=>"Caso enviado(Col. Nro. caso)");
$colores[1]=array("color"=>"#00AA00","texto"=>"Archivos subidos (Col. Atendido por)");
$colores[2]=array("color"=>"#FF8080","texto"=>"Tiene orden de compra");
$colores[3]=array("color"=>"#00ff00","texto"=>"Fue pagado con orden de compra<br>(Col. Costos)");
$colores[4]=array("color"=>"#FFC0C0","texto"=>"Tiene Repuestos");

//EJEMPLO OP 664
$resultado_lista=sql("select distinct idcaso, idestuser, casos_cdr.id_dependencia, nrocaso, fechainicio, nserie,
	deperfecto, fechacierre, entidad.nombre as organismo, dependencia, dependencias.direccion,
	dependencias.lugar, dependencias.cp, dependencias.contacto, dependencias.telefono, costofin,
	dependencias.mail, distrito.nombre as provincia, sync, cas_ate.nombre as nombre_cas, dependencias.id_entidad,
	(coalesce(fechacierre,CURRENT_DATE)-casos_cdr.fechainicio) as tiempo
	from casos.casos_cdr
		left join casos.dependencias using (id_dependencia)
		left join licitaciones.distrito using (id_distrito)
		left join licitaciones.entidad using (id_entidad)
		left join casos.cas_ate using (idate),
		ordenes.orden_de_produccion
where (sync is not null)
	and (ordenes.orden_de_produccion.nro_orden = ".$nro_orden_de_produccion_lista.")
	and (casos.casos_cdr.nserie >= ordenes.orden_de_produccion.nserie_desde)
	and (casos.casos_cdr.nserie <= ordenes.orden_de_produccion.nserie_hasta)") or fin_pagina();
?>
	<br><font size="3" color="#006699" style="font-weight:bold;">
		Casos de la orden de producci&oacuten nro. <?=$nro_orden_de_produccion_lista?>
	</font>
  <table class="bordes" width="98%" cellspacing=2 align="center">
  	<tr>
  		<td style='border-right: 0;' colspan=2 align=left id=ma><b>Total:</b> <?=$resultado_lista->recordCount()?> Casos.</td>
  		<td style='border-left: 0;' colspan=6 align=right id=ma></td>
  	</tr>
  	<tr>
 			<td align=right id=mo>Número de caso</td>
 			<td align=right id=mo>Atendido por</td>
 			<td align=right width=60 id=mo>Fecha Inicio</td>
 			<td align=right id=mo>Cliente</td>
 			<td align=right id=mo>Contacto</td>
 			<td align=right id=mo>Nro Serie</td>
 			<td id=mo>Tiempo Transcurrido</td>
 			<td id=mo>Estado</td>
 		</tr>
<?

while (!$resultado_lista->EOF) {
	$idcaso_lista=$resultado_lista->fields["idcaso"];
	$nro_caso_lista=$resultado_lista->fields["nrocaso"];
	$id_entidad_lista=$resultado_lista->fields["id_entidad"];
	$nombre_cas_lista=$resultado_lista->fields["nombre_cas"];
	$cantidad_repuestos_lista=$resultado_lista->fields["cantidad_repuestos"];
	$organismo_lista=$resultado_lista->fields["organismo"];
	$contacto_lista=$resultado_lista->fields["contacto"];
	$nserie_lista=$resultado_lista->fields["nserie"];
	$costo_fin_lista=$resultado_lista->fields["costofin"];
	$idestuser_lista=$resultado_lista->fields["idestuser"];
	$tiempo_lista=$resultado_lista->fields["tiempo"];
	$fila_lista=$resultado_lista->fields["fila"];
	$fecha_inicio_lista=$resultado_lista->fields["fechainicio"];
	//////////////////////////////////////////////////////////////////////////////////////
	$result_estadocdr=sql("select fecha,descripcion from estadocdr where idcaso='".$idcaso_lista."' order by idestcdr DESC limit 1 offset 0")or fin_pagina();
	$title=str_replace("\"","'", "Fecha: ".fecha($result_estadocdr->fields["fecha"])."\nEstado: ".$result_estadocdr->fields["descripcion"]);

	$result_caso=sql("select count(nrocaso) as cantidad_casos from compras.orden_de_compra where nrocaso=".$nro_caso_lista) or fin_pagina();
  $cantidad_casos=$result_caso->fields["cantidad_casos"];
  if ($cantidad_casos>0) $atributos=atrib_tr($colores[2]["color"])." title='$title'";
  else $atributos=atrib_tr()." title='$title'";

  $result_caso_mail=sql("select count(nrocaso) as  cantidad_mail from casos.casos_cdr join mail using(idcaso) where nrocaso=".$idcaso_lista) or fin_pagina();
  $cantidad_mail=$result_caso_mail->fields["cantidad_mail"];
  if ($cantidad_mail>0) $color_nro_caso="bgcolor='".$colores[0]["color"]."'";

  $result_caso_archivos=sql("select count(idcaso) as  cantidad_archivos from archivos_casos where  idcaso=".$idcaso_lista) or fin_pagina();
  $cantidad_archivos=$result_caso_archivos->fields["cantidad_archivos"];
  if ($cantidad_archivos>0) $color_cas="bgcolor='".$colores[1]["color"]."'";

  if ($idestuser_lista==1) $estado_caso="En Curso";
  if ($idestuser_lista==2) $estado_caso="Finalizado";
  if ($idestuser_lista==7) $estado_caso="Pendientes";
	if ($fila_lista) $col="bgcolor='#00ff00'";
	else $col="";
	$ref = encode_link("../casos/caso_estados.php",Array("id"=>$idcaso_lista,"id_entidad"=>$id_entidad_lista));
	?>
		<tr <?=$atributos?> onclick="window.open('<?=$ref?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=150,top=0,width=800,height=600');">
			<td <?=$color_nro_caso?>><?=$nro_caso_lista?></td>
			<td <?=$color_cas?>><?=$nombre_cas_lista?></td>
			<td><?=$fecha_inicio_lista?></td>
			<td <?=$color_organismo?>><?=$organismo_lista?></td>
			<td><?=$contacto_lista?></td>
			<td><?=$nserie_lista?></td>
			<td align="center"><?=(($tiempo_lista)?$tiempo_lista:0)?> días</td>
			<td><?=$estado_caso?></td>
		</tr>

	<?
		$resultado_lista->moveNext();
	//////////////////////////////////////////////////////////////////////////////////////
}
?>
	</table><br>
	<table width='95%' border=0 align=center>
		<tr>
			<td colspan=6 align=center><br>
				<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
   				<tr>
						<td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia</b></td>
   				</tr>
					<tr>
<?
	$cont=0;
	foreach ($colores as $est => $arr) {
	if (!($cont % 3)){
		echo "</tr><tr>";
	}
	?>
						<td width=33% bordercolor='#FFFFFF'>
            	<table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%>
            		<tr>
									<td width=15 bgcolor='<?=$colores[$est]["color"]?>' bordercolor='#000000' height=15>&nbsp;</td>
									<td bordercolor='#FFFFFF'><?=$colores[$est]["texto"]?></td>
								</tr>
							</table>
						</td>
		<?
	   $cont++;
	}
	?>
					</tr>
				</table>
</form>
</body>
</html>