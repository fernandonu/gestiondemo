<?PHP
include("../../config.php");

$proveedor = $parametros["proveedor"] or $proveedor = $_POST["proveedor"];
$desde = $parametros["desde"] or $desde = $_POST["desde"];

switch ($desde) {
	case 1: {$titulo="'Para Autorizar'";} break;
	case 2: {$titulo="'Recibo de Material'";} break;
	case 3: {$titulo="'Pagar'";} break;
}

$sql="Select razon_social from proveedor where id_proveedor=$proveedor";
$result = sql($sql,"Error consultando el proveedor".$sql);

if ($result->RecordCount()>0)
	$nombre_prov = $result->fields["razon_social"];
	else die("Error el proveedor no existe");

echo $html_header;
?>
<CENTER><h5>
<?/*Lista de calificaciones emitidas desde '<?=$titulo?>' para el proveedor <?=$nombre_prov?> 
*/?>
Lista de calificaciones emitidas para el proveedor <?=$nombre_prov?>
</H5></CENTER>
<form name="form1" method="POST" action="lista_calificacion_proveedor.php">
<INPUT type="hidden" name="proveedor" value="<?=$proveedor?>">
<INPUT type="hidden" name="desde" value="<?=$desde?>">
<?
	
variables_form_busqueda("calificado");

$orden = array(
		"default" => "1",
		"1" => "calificacion_proveedor.fecha",		
		"2" => "usuarios.apellido",		
		"3" => "calificacion_proveedor.calificado"		
	);

$filtro = array("calificacion_proveedor.fecha" => "fecha de calif.",
				"usuarios.apellido" => "apellido de usuario",
				"calificacion_proveedor.calificado" => "Calificación"
				);
//$where = "id_proveedor = $proveedor and desde = $desde";
$where = "id_proveedor = $proveedor";
$query="Select calificacion_proveedor.*,usuarios.nombre,usuarios.apellido from calificacion_proveedor join usuarios using(id_usuario)";


list($sql,$total_logs,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar"); 

$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error en form busqueda <br>".$sql);

?>
<INPUT type="submit" name="buscar" value="Buscar">
<TABLE width="100%" cellspacing="0">
<TR>
<td id="mo">
Cantidad: <?=$total_logs?>
</TD>
<td id="mo" colspan="2">
<?=$link_pagina?>
</TD>
</TR>
<TR>
<td id="ma" width="35%">
<a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"proveedor"=>"$proveedor","desde"=>"$desde"))?>'>Fecha</a>
</TD>
<td id="ma">
<a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"proveedor"=>"$proveedor","desde"=>"$desde"))?>'>Usuario</a>
</TD>
<td id="ma" width="10%">
<a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"proveedor"=>"$proveedor","desde"=>"$desde"))?>'>Calif.</a>
</TD>
</TR>

<? while(!$result->EOF) {
	$comentario=$result->fields["comentario"];
	?>
<TR <?=atrib_tr();?> onclick="alert('Comentario: <?=$comentario?>')">
<td >
<?=fecha($result->fields["fecha"])." ".Hora($result->fields["fecha"]);?>
</TD>
<td>
<?=$result->fields["nombre"]." ".$result->fields["apellido"];?>
</TD>
<td>
<?=$result->fields["calificado"];?>
</TD>
</TR>
<?$result->MoveNext();}?>
</TABLE>
</FORM>
<center><INPUT type="button" value="Cerrar" onclick="window.close();"></CENTER>