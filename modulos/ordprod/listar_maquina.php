<?
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: ferni $
$Revision: 1.48 $
$Date: 2006/11/21 19:53:42 $
*/

require_once("../../config.php");
variables_form_busqueda("ordenes_listar_maquinas");

$orden = array(
	"default" => "2",
 	"default_up" => "0",
	"1"	=> "maquina.nro_serie",
	"2"	=> "nro_orden",
	"3"	=> "fecha_entrega",
	"4"	=> "nombre",
	"5" => "desc_gral"
);
$filtro = array(
	"maquina.nro_serie" => "Nro. de Serie",
	"nro_orden" => "Nro. Orden de Prod.",
	"fecha_entrega" => "Fecha Entregada",
	"nombre" => "Cliente",
	"desc_gral" => "Placa Madre"
);
if ($_POST["sync"]) {
	$db_name = 'coradir';
	$dbtemp = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
	$dbtemp->Connect($db_host, $db_user, $db_password, $db_name);
	$dbtemp->BeginTrans();
	
	// Borrar los drivers de la maquina
	$q="select nro_serie,id_archivo from drivers where sync=2 or sync=3";
	$rs=sql($q) or fin_pagina();
	$nro_serie=$rs->fields["nro_serie"];
	while ($fila=$rs->fetchrow()) {
		$sql="DELETE FROM maquina.driver where nro_serie='".$fila["nro_serie"]."' and id_archivo=".$fila["id_archivo"];
		$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
		/*if ($nro_serie!=$fila["nro_serie"]) {
			$sql="DELETE FROM maquina.maquina where nro_serie='$nro_serie'";
			$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
			$nro_serie=$fila["nro_serie"];
		}*/
	}
	// Borrar archivo de drivers
	$q="select id_archivo from archivo_drivers where sync=2";
	$rs=sql($q) or fin_pagina();
	while ($fila=$rs->fetchrow()) {
		$sql="DELETE FROM maquina.archivo where id_archivo=".$fila["id_archivo"];
		$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
	}
	
	// Agregar nuevos archivos de drivers
	$q="select * from archivo_drivers where sync=1";
	$rs=sql($q) or fin_pagina();
	while ($fila=$rs->fetchrow()) {
		$sql="insert into maquina.archivo(id_archivo,nombre,descripcion,tipo,modelo,size) VALUES 
		(".$fila["id_archivo"].",'".$fila["archivo"]."','".$fila["descripcion"]."','".$fila["tipo"]."','".$fila["modelo"]."',".$fila["size"].")";
		$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
	}
	// Agregar los drivers a nuevas maquinas
	$q="select id_archivo,drivers.nro_serie,entidad.id_entidad,entidad.id_distrito,entidad.cuit,entidad.contacto,
		nombre,direccion,telefono,fax,codigo_postal,localidad,mail,entidad.observaciones from drivers
		left join maquina USING (nro_serie) left join orden_de_produccion USING (nro_orden)
		left join entidad USING(id_entidad) where sync=1 or sync=3 order by nro_serie";
	$rs=sql($q) or fin_pagina();
	$paso=1;
	$pasa=0;
	while ($fila=$rs->fetchrow()) {
		// Actualizando las entidades 
		$sql="select id_entidad from general.entidad where id_entidad=".$fila["id_entidad"];
		$saber=$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
		if (strlen($saber->fields["id_entidad"])<1) {
			$sql="insert into general.entidad (id_entidad,id_distrito,nombre,direccion,localidad,codigo_postal,cuil,contacto,telefono,fax,mail) VALUES (
				".$fila["id_entidad"].",".$fila["id_distrito"].",'".$fila["nombre"]."','".$fila["direccion"]."','".$fila["localidad"]."','".$fila["codigo_postal"]."','".$fila["cuit"]."','".$fila["contacto"]."','".$fila["telefono"]."','".$fila["fax"]."','".$fila["mail"]."')";
			$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
		}
		else {
			$sql="UPDATE general.entidad SET id_distrito=".$fila["id_distrito"].",
				nombre='".$fila["nombre"]."',
				direccion='".$fila["direccion"]."',
				localidad='".$fila["localidad"]."',
				codigo_postal='".$fila["codigo_postal"]."',
				cuil='".$fila["cuit"]."',
				contacto='".$fila["contacto"]."',
				telefono='".$fila["telefono"]."',
				fax='".$fila["fax"]."',
				mail='".$fila["mail"]."' where id_entidad=".$fila["id_entidad"];
			$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
		}
		// fin de actualizar entidades
		// Agregar drivers
		//if ($paso!=$fila["nro_serie"]) {
			$sql="select nro_serie from maquina.maquina where nro_serie='".$fila["nro_serie"]."'";
			$r=$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
			if ($r->fields["nro_serie"]!=$fila["nro_serie"]) {
				$sql="insert into maquina.maquina(nro_serie,id_entidad) VALUES ('".$fila["nro_serie"]."',".$fila["id_entidad"].")";
				$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
			}
			//$paso=$fila["nro_serie"];
		//}
		$sql="insert into maquina.driver(id_archivo,nro_serie) VALUES (".$fila["id_archivo"].",'".$fila["nro_serie"]."')";
		$dbtemp->execute($sql) or $error.=$dbtemp->errormsg()." - ".$sql;
		// fin agregar drivers
	}
	if ($error) {
		$dbtemp->RollBackTrans();
		error($error);
		fin_pagina();
	}
	else {
		sql("UPDATE archivo_drivers SET sync=NULL where sync=1") or fin_pagina();
		sql("UPDATE drivers SET sync=NULL where sync=1") or fin_pagina();
		sql("UPDATE archivo_drivers SET sync=NULL where sync=3") or fin_pagina();
		sql("UPDATE drivers SET sync=NULL where sync=3") or fin_pagina();
		sql("DELETE FROM drivers where sync=2") or fin_pagina();
		sql("DELETE FROM archivo_drivers where sync=2") or fin_pagina();
		$dbtemp->CommitTrans();
		aviso ("Los datos se sincronizaron con exito");
	}
}

$sql_tmp="select distinct(maquina.nro_orden),nserie_desde,nserie_hasta,fecha_entrega,nombre,
	f.desc_gral from orden_de_produccion 
	rigth join maquina Using(nro_orden)
	left join entidad USING(id_entidad)
	left join (select distinct(nro_orden),desc_gral,p.id_producto from ordenes.filas_ord_prod  
		join general.productos as p using(id_producto)
		where  p.id_tipo_prod=1
	union (select distinct(nro_orden),pe.descripcion,pe.id_prod_esp from ordenes.filas_ord_prod  
		join general.producto_especifico as pe using(id_prod_esp)
		where pe.id_tipo_prod=1)) as f using(nro_orden)";
	
//$where_tmp="producto_especifico.id_tipo_prod=1 or productos.id_tipo_prod=1";
echo $html_header;
?>
<form name="maquinas" method="POST" action="listar_maquina.php">
<br><div width=100% align=center>
<?
list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
$resultado=sql($sql) or fin_pagina();
$sql="select id_drivers from drivers where sync=1 or sync=2";
$res1=sql($sql) or fin_pagina();
$sql="select id_archivo from archivo_drivers where sync=1 or sync=2";
$res2=sql($sql) or fin_pagina();
if ($res1->fields["id_drivers"]>0) $botoncolor="style='background-color: red;'";
elseif ($res2->fields["id_archivo"]>0) $botoncolor="style='background-color: red;'";
else $botoncolor="";
?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>&nbsp;&nbsp;&nbsp;<input type=submit <?echo $botoncolor;?> name=sync value="Sincronizar">
</div><br>
<center></center>
<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
<tr id=ma>
  <td align="left" colspan="2">
   <b>Total: <?=$total_pedidos?> Maquinas Encontrado/s. <?=$total_1?></b>
  </td>
  <td align="left" colspan="2">
  	<input type="button" value="Ver Porcentaje Driver" name="ver_porcentaje_driver" onclick="window.open('ver_porcentaje_driver.php','','width=350, height=150,left=50,top=50,status=0,resizable=0,scrollbars=0')">   
  </td>
  <td align="right" colspan="3">
   <?=$link_pagina?>
  </td>
</tr>
<tr id=mo>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nro Serie</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Nro Orden de Prod.</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Fecha Entrega</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Cliente</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Placa Madre</a></b></td>
</tr>
<?
while ($fila=$resultado->FetchRow()) {
	$ref=encode_link("nueva_maquina.php",Array("nro_orden"=>$fila["nro_orden"]));
	tr_tag($ref,"Agregar drivers a las Maquinas.");
	$sql="select nro_serie from drivers left join maquina USING(nro_serie) WHERE (sync is null or sync<>2) and nro_orden=".$fila["nro_orden"];
	$res=sql($sql) or fin_pagina();
	if ($res->recordcount()>0) $color="";
	else $color="bgcolor='red'";
?>
	<td <?= $color; ?>><? echo $fila["nserie_desde"]." ... ".$fila["nserie_hasta"];?></td>
	<td <?= $color; ?>><? echo $fila["nro_orden"];?></td>
	<td <?= $color; ?>><? echo fecha($fila["fecha_entrega"]);?></td>
	<td <?= $color; ?>><? echo $fila["nombre"];?></td>
	<td <?= $color; ?>><? echo $fila["desc_gral"]; ?></td>
<tr>
<?
}
?>
</table></form><br>
<center>
<div align="left" style="background-color: white;border: solid ;border-width: 1;overflow-y: auto; width: 95%;">
<b><font size=2>Colores de referencia</font><b><br>
<table border=0 width=200px>
<tr>
	<td align="right" width=100%>
		Falta Agregar Drivers: 
	</td>
	<td>
		<div style="background-color: red; border: solid;border-width: 1;width:30;height:10"></div>
	</td>
</tr>
</table>
</div>
<center>
<?
fin_pagina();
?>