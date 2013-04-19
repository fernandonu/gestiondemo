<?PHP
/*
Autor: MAD
Creado: sabado 27/11/04

MODIFICADA POR
$Author: marcelo $
$Revision: 1.3 $
$Date: 2005/01/08 15:11:35 $
*/

require_once("../../config.php");

$id_renglon_prop=$parametros["id_renglon_prop"] or $id_renglon_prop=$_POST["id_renglon_prop"] or $id_renglon_prop=$_GET["id_renglon_prop"];
$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"] or $id_producto=$_GET["id_producto"];

if (!$id_renglon_prop) die ("No hay nada que mostrar");

//caso en que elimino uno o mas logs
if ($_POST["eliminar"]) {
	$ii=$_POST["count_logs"]-1;
	$cantid=0;
	while($ii>=0){
		if(isset($_POST["elim_$ii"])){
			$sql_delete[]="delete from log_modif_precio_presupuesto where id_log = ".$_POST["elim_$ii"];
			$cantid++;
		}
		$ii--;
	}
	sql($sql_delete,"Error eliminando logs") or fin_pagina();
	$msg = "<center><strong>Se eliminaron $cantid logs.</strong></center>";
}




$q1="Select tipo,modelo,marca from productos where id_producto = $id_producto";
$res_1 = sql($q1,"Error obteniendo el producto<br>$q1") or fin_pagina();

echo $html_header;

?>
<TABLE width="100%" class="bordes" style="font-weight:bold">
<TR>
<td id="mo" colspan="2">Producto</TD>
</TR>
<TR>
<td bgcolor="White">Tipo de producto</td>
<td bgcolor="White"><?=$res_1->fields["tipo"]?></td>
</tr>
<TR>
<td bgcolor="White">Modelo del producto</td>
<td bgcolor="White"><?=$res_1->fields["modelo"]?></td>
</tr>
<TR>
<td bgcolor="White">Marca del producto</td>
<td bgcolor="White"><?=$res_1->fields["marca"]?></td>
</tr>
</TABLE>
<br>
<form name="form1" method="POST" action="ver_log_producto_presupuesto.php">
<CENTER>
<?

variables_form_busqueda("logs_cambio_precio");

$orden = array(
		"default" => "4",
		"1" => "log_modif_precio_presupuesto.fecha",
		"2" => "usuarios.apellido",
		"3" => "proveedor.razon_social",
		"4" => "log_modif_precio_presupuesto.monto"
	);

$filtro = array(
		"log_modif_precio_presupuesto.fecha" => "Fecha",
		"usuarios.apellido" => "Usuario",
		"proveedor.razon_social" => "Ensamblador",
		"log_modif_precio_presupuesto.monto" => "Monto"
	);

	
$q="Select log_modif_precio_presupuesto.*,
usuarios.nombre,usuarios.apellido,
proveedor.razon_social 
from log_modif_precio_presupuesto 
join producto_presupuesto_new using(id_producto_presupuesto)
join usuarios using(id_usuario)
join proveedor using(id_proveedor)";

//$where = "producto_presupuesto_new.id_renglon_prop = $id_renglon_prop and
//			producto_presupuesto_new.id_producto = $id_producto";
$where = "producto_presupuesto_new.id_producto = $id_producto";

list($q,$total,$link_pagina,$up) = form_busqueda($q,$orden,$filtro,$link_tmp,$where,"buscar"); 

$res= sql($q,"Error obteniendo el listado del log") or fin_pagina();

$permiso_eliminar = permisos_check("inicio","borrar_cambio_precio");
?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>

<SCRIPT>
var act=0;
function activar(obj){
	if (obj.checked) act++;
	else act--;
	
	if (act>0) document.all.eliminar.disabled=0;
	else document.all.eliminar.disabled=1;
}
</SCRIPT>
<?if($msg) echo $msg;?>
</CENTER>
<TABLE width="100%" class="bordes">
<TR>
<td id="mo" colspan="<?=3+$permiso_eliminar?>"> Detalle de las modificaciones del precio del producto en el presupuesto</TD>
<TD id="mo"><?=$link_pagina?></TD>
</TR>
<TR id="mo">
<?if($permiso_eliminar) echo "<td width='5%'><input type='submit' name='eliminar' value='Elim' style='font-size=8px' title='Eliminar los logs seleccionados.' disabled></td>"; ?>
<td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("id_producto" => $id_producto ,"id_renglon_prop" => $id_renglon_prop ,"sort"=>"1","up"=>$up))?>'>Fecha</a></td>
<td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("id_producto" => $id_producto ,"id_renglon_prop" => $id_renglon_prop ,"sort"=>"2","up"=>$up))?>'>Usuario</a></td>
<td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("id_producto" => $id_producto ,"id_renglon_prop" => $id_renglon_prop ,"sort"=>"3","up"=>$up))?>'>Proveedor</a></td>
<td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("id_producto" => $id_producto ,"id_renglon_prop" => $id_renglon_prop ,"sort"=>"4","up"=>$up))?>'>Monto</a></td>
</tr>
<?$i=0;
while(!$res->EOF){?>
<TR id="ma">
<?if($permiso_eliminar) echo "<td><input type='checkbox' name='elim_$i' value='".$res->fields["id_log"]."' class='estilos_check' onclick='activar(this);'></td>"; ?>
<td align="center"><?=fecha($res->fields["fecha"]);?></td>
<td align="center"><? echo $res->fields["nombre"]." ".$res->fields["apellido"];?></td>
<td align="center"><?=$res->fields["razon_social"]?></td>
<td align="right" bgcolor="White">U$S <?=formato_money($res->fields["monto"]);?></td>
</tr>
<?
$res -> MoveNext();
$i++;
}?>
</TABLE>
<BR>
<CENTER>
<INPUT type="button" name="cerrar" value="Cerrar" onclick="window.close();">
</CENTER>
<INPUT type="hidden" name="id_producto" value="<?=$id_producto?>">
<INPUT type="hidden" name="id_renglon_prop" value="<?=$id_renglon_prop?>">
<INPUT type="hidden" name="count_logs" value="<?=$i?>">
</FORM>
<?fin_pagina();?>
