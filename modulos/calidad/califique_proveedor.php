<?php
  /*
$Author: ferni $
$Revision: 1.10 $
$Date: 2006/12/07 17:49:34 $
*/

//el parametro desde tiene uno de los siguientes valores
// 1  si se llama el script desde Para Autorizar
// 2  si se llama el script desde Recibo de Material
// 3  si se llama el script desde Pagar


include("../../config.php");


$proveedor=$parametros["proveedor"] or $proveedor=$_POST["proveedor"]; 
$desde = $parametros["desde"] or $desde = $_POST["desde"];
$id_no_calificacion = $parametros["id_no_calificacion"] or $id_no_calificacion = $_POST["id_no_calificacion"];


$sql="Select razon_social,clasificado as calificado from proveedor where id_proveedor=$proveedor";
$result = sql($sql,"Error consultando el proveedor");

if ($result->RecordCount()>0){
	$nombre_prov = $result->fields["razon_social"];
	$calif_prov = $result->fields["calificado"];
}
else die("Error el proveedor no existe");
	
$fecha = date("Y-m-d H:i:s",mktime());
$usuario = $_ses_user["id"];
$usuario_name = $_ses_user["name"];

switch ($desde) {
	case 1: {$titulo_desde="'Para Autorizar'";} break;
	case 2: {$titulo_desde="'Recibo de Material'";} break;
	case 3: {$titulo_desde="'Pagar'";} break;
}

echo "<script>var califico=0;</script>";
//==================================================================
//    Parte de Guardar la calificacion 
//==================================================================	
if ($_POST["guardar"]=="Guardar calificación" || $_POST['guardar_forzado']=="guardar") {	
	
	$calificacion=$_POST["calif"];
	$comentario=$_POST["comentario"];
	
	$sql1[]="insert into calificacion_proveedor (fecha,id_usuario,comentario,calificado,id_proveedor,desde) values
	('$fecha',$usuario,'$comentario','$calificacion',$proveedor,$desde)";
	$sql1[]="delete from no_calificacion_proveedor where id_no_calificacion=$id_no_calificacion";
	sql($sql1,"Error guardando la calificación. ".$sql1) or die();
	
	echo "<script>window.close();</script>";
//	echo $html_header; 
//	echo "<center><h3>El Proveedor $nombre_prov fue calificado con un puntaje de $calificacion</h3><br>";
//	echo "<input type='button' onclick='window.close();' value='Cerrar'></center>";

/*	switch ($calif_prov) {
		case 'A': {$rango = ($calificacion>=8);} break;
		case 'B': {$rango = ($calificacion>=6);} break;
		case 'C': {$rango = ($calificacion>=4);} break;
		case 'D': {$rango = ($calificacion>=2);} break;
		case 'E': {$rango = ($calificacion>=0);} break;
	}	
*/
	die();
}
//fin de guardar

//==================================================================
//    Parte de Registrar la entrada a calificacion
//==================================================================	
if(!$id_no_calificacion) {
	$sql_next ="Select nextval('no_calificacion_proveedor_id_no_calificacion_seq') as id_no_calificacion";
	$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);

	$id_no_calificacion = $resultado->fields["id_no_calificacion"];
	
	$sql_insert="Insert into no_calificacion_proveedor(id_no_calificacion,fecha,usuario,desde,proveedor) values
	($id_no_calificacion,'$fecha','$usuario_name',$titulo_desde,'$nombre_prov')";
	
	sql($sql_insert,"Error en registrar entrada de calificacion ".$sql_insert);		
}
//fin de preparar entrada

$link=encode_link("califique_proveedor.php",array("proveedor"=>"$proveedor","desde"=>"$desde","id_no_calificacion"=>"$id_no_calificacion"));

echo "
<html>
  <head>
	<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>
    <script languaje='javascript' src='$html_root/lib/funciones.js'></script>
  </head>
  <body background='$html_root/imagenes/fondo.gif' bgcolor=\"$bgcolor3\" onload='document.focus(); alerta_sinc();' onunload=salir_check('$link');>";
//onload="document.all.guardar.focus()"

$sql="select fecha,calificado from general.calificacion_proveedor 
		where id_proveedor=$proveedor 
		order by fecha DESC ";
$resultado_ult_calif=$db->Execute($sql) or die($db->ErrorMsg());
$resultado_ult_calif->MoveFirst();
$ult_calif_prov=$resultado_ult_calif->fields["calificado"];
?>

<FORM id="calificacion" name="form1" action="califique_proveedor.php" method="POST" target="_self" >
<TABLE width="100%" id="tt1" cellspacing="0" align="center">
	<TR>
	<TD id="mo" colspan=2 width="100%">Califique el proveedor: <?=$nombre_prov?></TD>
	</TR>
	<tr>
		<td colspan="2">	
			<strong> Ultima Calificacion del Proveedor <font color="Red"><?=$ult_calif_prov?></font></strong>
		<td>	
	</tr>
	<tr>
		<td colspan="2">	
			<strong>
			<font color="Blue">
			<br>
			Esta Encuesta se Realiza para tener Registro del la Atención que Recibimos del Proveedor. 
			<br>
			<font color="Red">Su Calificación es Importante</font> y se debe Tener en Cuenta como nos Atiende, si se Ajusta a Nuestros Procedimientos y si Cumple con lo Pactado. 
			</font>
			</strong>
		<td>	
	</tr>
	<br>
	<TR>
	<TD id="ma" align="center" width="30%"> Calificación </TD>
	<TD id="ma" align="center"> Comentario</TD>
	</TR>
	<TR>
	<TD id="ma" align="center">
	<SELECT id="calif" name="calif">
	<OPTION selected>Elija valor</OPTION>
	<OPTION value="10">10</OPTION>
	<OPTION value="9">9</OPTION>
	<OPTION value="8">8</OPTION>
	<OPTION value="7">7</OPTION>
	<OPTION value="6">6</OPTION>
	<OPTION value="5">5</OPTION>
	<OPTION value="4">4</OPTION>
	<OPTION value="3">3</OPTION>
	<OPTION value="2">2</OPTION>
	<OPTION value="1">1</OPTION>	
	<OPTION value="0">0</OPTION>	
	</SELECT>
	</TD>
	<TD id="ma" align="center"><TEXTAREA name="comentario" rows="5" cols="35"></TEXTAREA></TD>
	</TR>
	<TR>
	<TD id="mo" colspan=2 width="100%" align="center">
	<INPUT type="hidden" name="proveedor" value="<?=$proveedor?>">
	<INPUT type="hidden" name="desde" value="<?=$desde?>">
	<INPUT type="hidden" name="id_no_calificacion" value="<?=$id_no_calificacion?>">
	<INPUT type="submit" name="guardar" value="Guardar calificación" onclick="return guardar_check();">
	<!--<INPUT type="submit" name="cancelar" value="No Calificar" onclick="window.close()">-->
	<INPUT type="button" name="cancelar" value="No Calificar" onclick="salir_check(<?$link?>)">
	<input name="guardar_forzado" type="hidden" value="">
	</TD>
	</TR>
</TABLE>
</FORM>

<TABLE width="100%" id="ref" cellspacing="0" bgcolor="White" class="bordes">
	<TR>
	<TD align="center" colspan="2"><B>Referencia para calificar el proveedor.</b></TD>
	</TR>
	<TR>
	<TD>[10-8] Exelente</b></TD>
	<TD>[3-2] Regular</b></TD>
	</TR>
	<TR>
	<TD>[7-6] Muy Bueno</b></TD>
	<TD>[1-0] Malo</b></TD>
	</TR>
	<TR>
	<TD>[5-4] Bueno</b></TD>
	<TD></TD>
	</TR>
	<br>
	<tr>
		<td colspan="2">
		<font color="Blue">
		<strong>
		* En Caso de no Haber Cambios en el Comportamiento del Proveedor y si su Nota va a ser
		la Misma que el Proveedor ya tiene Asignada puede Optar por no Calificar el Proveedor.
		</strong>
		</font>
		</td>
	</tr>
</TABLE>
<SCRIPT>
var op = 1;
function alerta(){
	if (op == 1 ) {
		document.all.tt1.style.border = '5px solid #ff0000';
		op = 0;
	} else {
		document.all.tt1.style.border = '5px solid #ffffff';
		op = 1;
	}
	alerta_sinc();
}
function alerta_sinc(){
		setTimeout("alerta()",500);
}
function guardar_check(){
	if (document.all.calif.selectedIndex == 0){ 
	  alert('Para calificar este proveedor debera seleccionar un valor de puntaje adecuado al proveedor y acorde a los valores detallados al pie de la página.');
	  return false;
	}
	if (document.all.comentario.value == "") {
	  alert('No olvide agregar un comentario acerca de la calificación del proveedor.')
	  return false;
	}
	califico=1;
	return true;	
}

document.all.guardar.focus();

//genera numero random
function aleatorio(inferior,superior){
    numPosibilidades = superior - inferior
    aleat = Math.random() * numPosibilidades
    aleat = Math.round(aleat)
    return parseInt(inferior) + aleat
} 

function salir_check(link) 
{if (!califico)
    {document.all.calif.value=aleatorio(6,9);
     document.all.comentario.value="Código 11";
     document.all.guardar_forzado.value="guardar";
     document.all.form1.submit();
     
    }
}


/*function salir_check(link) {
	if (!califico){
		if(!confirm('Ud no calificó el proveedor. Es de suma importancia registrar el desempeño de nuestros proveedores para elevar la calidad funcional del negocio. ¿Continuar sin calificar?'))
			window.open(link,'','top=100px, left=200px, width=400px, height=240px, scroll=0, status=0')
	}
}*/
</SCRIPT>
