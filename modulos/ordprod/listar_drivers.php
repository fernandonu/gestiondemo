<?
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: cestila $
$Revision: 1.15 $
$Date: 2004/11/18 22:22:37 $
*/

require_once("../../config.php");
variables_form_busqueda("ordenes_listar_drivers");

if ($parametros["modo"]=="eliminar") {
	$sql="select id_drivers from drivers where (sync is null or sync<>2) and id_archivo=".$parametros["id"];
	$rs=sql($sql) or fin_pagina();
	if ($rs->fields["id_drivers"]>0) {
		error("El driver no se puede eliminar esta siendo usado por alguna Maquina.");
	}
	else {
		$sq="select sync from archivo_drivers WHERE id_archivo=".$parametros["id"];
		$rs=sql($sq) or fin_pagina();
		if ($rs->fields["sync"]==1)
			$sql="DELETE FROM archivo_drivers WHERE id_archivo=".$parametros["id"];
		else
			$sql="UPDATE archivo_drivers SET sync=2 WHERE id_archivo=".$parametros["id"];
		if (!$db->execute($sql))
			error("El driver no se puede eliminar esta siendo usado por alguna Maquina.");
		else 
			aviso("El drivers se borro corectamente.");
	}
}
$orden = array(
	"default" => "2",
 	"default_up" => "0",
	"1"	=> "id_archivo",
	"2"	=> "modelo",
	"3"	=> "descripcion",
	"4"	=> "size"
);
$filtro = array(
	"id_archivo" => "ID",
	"modelo" => "Modelo de Mother",
	"descripcion" => "Descripcion",
	"size" => "Tamaño"
);
$sql_tmp="select id_archivo,modelo,descripcion,size from archivo_drivers";
$where_tmp="sync is null or sync<>2";
echo $html_header;
?>
<form name="drivers" method="POST" action="listar_drivers.php">
<br><div width=100% align=center>
<?
list($sql,$total_pedidos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
$resultado=sql($sql) or fin_pagina();
?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
</div><br>
<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
<tr id=ma>
  <td align="left" colspan="2">
   <b>Total: <?=$total_pedidos?> Drivers Encontrado/s.</b>
  </td>
  <td align="right" colspan="3">
   <?=$link_pagina?>
  </td>
</tr>
<tr id=mo>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Modelo</a></b></td>
	<td width=40%><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Descripción</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Tamaño</a></b></td>
	<td>Func.</td>
</tr>
<?
while ($fila=$resultado->FetchRow()) {
	$ref=encode_link("nuevo_drivers.php",Array("id_archivo"=>$fila["id_archivo"]));
	//trtag($ref,"");
?>
<tr id=ma>
	<td><? echo $fila["id_archivo"];?></td>
	<td><? echo $fila["modelo"];?></td>
	<td><? echo $fila["descripcion"];?></td>
	<?
	$size=number_format(($fila["size"] / 1024));
	?>
	<td><? echo $size ." Kb";?></td>
	<td><a href="<? echo encode_link("listar_drivers.php",array("modo"=>"eliminar","id"=>$fila["id_archivo"])); ?>"><img src="../../imagenes/sin_desc.gif" border=0></a></td>
<tr>
<?
}
?>
</table></form>
<?
fin_pagina();
?>