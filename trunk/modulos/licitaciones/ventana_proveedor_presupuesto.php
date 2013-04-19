<?
require_once("../../config.php");
$onclick['cargar']=$parametros['onclickcargar'];
$onclick['salir']=$parametros['onclicksalir'] or $onclick['salir']="window.opener.focus();window.close()";
echo $html_header;
$sql="select razon_social,id_proveedor from proveedor where activo=true and razon_social not ilike 'Stock%' order by razon_social";
$resultado_prov=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$db_name);
?>
<script>
function control_boton()
{
	if (window.event.keyCode==13)
	{
		<?= $onclick['cargar'] ?>;
	}
  else if (window.event.keyCode==27)
  {
		<?= $onclick['salir'] ?>;
  }
}
</script>
<body onKeypress="control_boton()">
<font color="Blue">Cargar Proveedores
<select name="proveedor" onKeypress="buscar_op(this);"
onblur="borrar_buffer();"
onclick="borrar_buffer();">
<?
while(!$resultado_prov->EOF)
{
?>
<option value="<?=$resultado_prov->fields['id_proveedor'];?>"><?=$resultado_prov->fields['razon_social'];?></option>
<?
$resultado_prov->MoveNext();
}
?>
</select>
<br>
<script>
document.all.proveedor.focus();
</script>
<center>
<input type="button" name="boton" value="Cargar" onclick="<?=$onclick['cargar'] ?>">
<input type="button" name="boton" value="Salir" onclick="<?=$onclick['salir'] ?>">
</center>
</body>
</head>
</html>