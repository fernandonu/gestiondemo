<?
/*
Autor: GACZ
Creado: viernes 11/02/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.2 $
$Date: 2005/02/15 16:09:44 $
*/

require_once("../../config.php");

$id_inventario=$parametros['id_inventario'] or $id_inventario=$_POST['hid_inventario'] or $id_inventario=-1;
$readonly=$parametros['readonly'] or $readonly=0;;

$q="select * from inventario where id_inventario=$id_inventario ";
$r=sql($q) or fin_pagina();

extract($_POST);

if ($_POST['bguardar'])
{
	//modificar
	if ($id_inventario > 0)
	{
		$q ="update inventario set descripcion='$descripcion', precio_unitario=$precio_unitario, cantidad=$cantidad, ubicacion='$ubicacion' ";
		$q.="where id_inventario=$id_inventario";
	}
	else //nuevo item
	{
		$q ="insert into inventario (descripcion,ubicacion,precio_unitario,cantidad) values ";	
		$q.="('$descripcion','$ubicacion',$precio_unitario,$cantidad) ";	
	}
}
if ($_POST['bhistorial'])
	$q ="update inventario set id_estado=2 where id_inventario=$id_inventario";
	
if ($_POST['bhistorial'] || $_POST['bguardar'])
{
	sql($q) or fin_pagina();
	echo "<script>window.opener.location.reload();window.close();</script>";	
}
	
echo $html_header;
$q="select distinct ubicacion from inventario";
$r2=sql($q) or fin_pagina();
?>
<script language="javascript" type="text/javascript" src="../../lib/actb.js"></script>
<script language="javascript" type="text/javascript" src="../../lib/NumberFormat150.js"></script>
<script> 
var ubicaciones=new Array();
<?
	$i=0; 
  while ($f=$r2->fetchrow()) 
	{
		echo "ubicaciones[$i]='{$f['ubicacion']}';\n";
		$i++;
	}	
?>
function chk_form()
{
	if ((document.all.descripcion.value=="")	|| (document.all.ubicacion.value=="")	|| (document.all.precio_unitario.value=="")	|| (document.all.cantidad.value==""))
		return false;
	else
		return true
}
function fntotal()
{
	document.all.spantotal.innerText=formato_money(parseInt(document.all.cantidad.value)*parseFloat(document.all.precio_unitario.value));
}
</script>
<form name="form1" method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>">
  <table width="50%"  border=1 cellpadding=2 cellspacing=1 bordercolor='black'>
  <input type="hidden" name="hid_inventario" value="<?=$id_inventario ?>">
    <tr>
      <td width="50%" valign="middle">Descripcion&nbsp;&nbsp;        <input name="descripcion" type="text" size="50" value="<?=$r->fields['descripcion'] ?>"></td>
      <script>fnNoQuotes(document.all.descripcion) </script>
    </tr>
    <tr>
      <td>Ubicacion&nbsp;&nbsp;<input type="text" name="ubicacion" value="<?=$r->fields['ubicacion'] ?>" autocomplete=off onfocus="actb(this,event,ubicaciones)" ></td>
    </tr>
    <tr>
      <td>Cantidad&nbsp;&nbsp;<input name="cantidad" style="text-align:right" type="text" size="6" value="<?=($r->fields['cantidad']?$r->fields['cantidad']:1) ?>" onkeypress="return filtrar_teclas(event,'0123456789')" onchange="fntotal()"> 
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Precio unitario $ <input name="precio_unitario" style="text-align:right" type="text" size="10" onkeypress="return filtrar_teclas(event,'0123456789.')" value="<?=number_format($r->fields['precio_unitario'],2,".","") ?>" onchange="fntotal()"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
  </table>
	<table width="100%" >
    <tr>
      <td align="left"><b>Total $ <span id=spantotal><?=formato_money($r->fields['cantidad']*$r->fields['precio_unitario'])?></span>&nbsp;&nbsp;&nbsp;</b></td>
		</tr>
      <td align="center"><input name="bguardar" type="submit" value="Guardar" onclick="if (!chk_form()){alert('Por favor complete todos los campos'); return false}" />
      &nbsp;&nbsp;
      <input name="bcancelar" type="button" value="Cancelar" onclick="window.close()" /></td>
      </tr>
    </tr>
<?
	//si el item ya existe
	if ($id_inventario > 0 && permisos_check("inicio","inventario_bhistorial"))
	{
?>    
    <tr><td align="center"> <input type="submit" name="bhistorial" style="width:110px" value="Pasar al Historial" /></td></tr>
<? }?>
  </table>
</form>
