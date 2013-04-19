<?
/*
Autor: GACZ
Creado: viernes 11/02/05

MODIFICADA POR
$Author: gabriel $
$Revision: 1.4 $
$Date: 2005/10/05 19:31:37 $
*/

require_once("../../config.php");
$datos_barra = array(
                    array(
                        "descripcion"    => "Actuales",
                        "cmd"            => "actuales"
                        ),
                    array(
                        "descripcion"    => "Historial",
                        "cmd"            => "historial"
                        )
);
require_once("inventario.data.php");//aqui se hace la busqueda y los datos se dejan en un arreglo $data[fila][columna]
echo $html_header;
generar_barra_nav($datos_barra);
?>
<script>
//ventana para modificar los datos
wmodificar=new Object();
wmodificar.closed=true;
</script>
<br>
<form action="<?=$_SERVER['SCRIPT_NAME'] ?>" method="POST">
<table align="center" width="90%" class="bordes" bgcolor=<?=$bgcolor_out?>>
<tr><td align="center" bgcolor="White"><b>Total $ <?=formato_money($data['total']) ?></b></td><td align="center"><input type="button" name="bnuevo" value="Nuevo Item" onclick="if (!wmodificar.closed) wmodificar.close(); wmodificar=window.open('<?=encode_link('inventario_modif.php',array('id_inventario'=>-1))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=450,height=200');">&nbsp;&nbsp; <?=$ob_data ?>&nbsp;<input type="submit" name="bbuscar" value="Buscar"> </td></tr>
</table>
<table width="100%">
<tr><td width="50%"></td><td align="right"></td></tr>
</table>
<table width="100%" border=1 cellpadding=2 cellspacing=1 bordercolor='black'>
<tr id="ma" style="border:none"><td height="20px" colspan=4 align="left" style="border-right:none">Registros encontrados: <?=$data['busqueda']['total_recordcount']?></td><td style="border-left:none" colspan=3>&nbsp;<?=$data['busqueda']['link_pagina']?></td></tr>
<tr id="mo">
	<td height="10%" ><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up2))?>'>Nº</a></td>
	<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up2))?>'>Descripción</a></td>
	<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up2))?>'>Ubicación</a></td>
	<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up2))?>'>Cantidad</a></td>
	<td title="Precio Unitario"><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up2))?>'>Precio U</a></td>
	<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up2))?>'>Total</a></td>
	<td>&nbsp;</td>
</tr>
<? 
for ($i=0; $i < $data['busqueda']['page_recordcount'] ; $i++)
{
?>
<tr>
	<td align="center" ><?=$data[$i]['item_nro']?></td>
	<td><?=$data[$i]['descripcion']?></td>
	<td><?=$data[$i]['ubicacion']?></td>
	<td align="right"><?=$data[$i]['cantidad']?></td>
	<td align="right" title="Precio Unitario">$ <?=formato_money($data[$i]['precio_unitario'])?></td>
	<td align="right">$ <?=formato_money($data[$i]['total'])?></td>
	<td width="10px"><input type="button" name="bmodificar" <? if ($data[$i]['id_estado']==2) echo " disabled " ?> value="M" title="Modificar" onclick="if (!wmodificar.closed) wmodificar.close(); wmodificar=window.open('<?=encode_link('inventario_modif.php',array('id_inventario'=>$data[$i]['id_inventario']))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=450,height=200');" ></td></tr>
<?
}
?>
</table>
</form>
</body>
</html>
