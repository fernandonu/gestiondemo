<?PHP
require_once("../../config.php");
//esta funcion te genera el word y te sube el archivo
function genera_excel($buffer,$nro_licitacion){
global $db;
global $_ses_user_login;


 $sql = "SELECT entidad.nombre as nombre_entidad,";
 $sql .= "distrito.nombre as nombre_distrito,";
 $sql .= "licitacion.fecha_apertura ";
 $sql .= "FROM (licitacion ";
 $sql .= "INNER JOIN entidad ";
 $sql .= "ON licitacion.id_entidad=entidad.id_entidad) ";
 $sql .= "INNER JOIN distrito ";
 $sql .= "ON entidad.id_distrito=distrito.id_distrito ";
 $sql .= "WHERE licitacion.id_licitacion=$nro_licitacion";
 $result = $db->Execute($sql) or die($sql);
 $distrito = $result->fields["nombre_distrito"];
 $entidad = $result->fields["nombre_entidad"];
 $fecha = substr($result->fields["fecha_apertura"],0,4);
 $path=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$nro_licitacion";    // linux
 //$name="Descripcion";
 $name="Descripciones_";
 $name.=".xls";
 $path1=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$nro_licitacion";
 mkdirs($path1);
 $temporal=$path1."/".$name;  //linux

 $fp = fopen($temporal,"w+");
 fwrite($fp,$buffer);
 fclose($fp);
 $FileNameFull= $temporal;
 $FileNameOld = $FileNameFull;
 $FileNameFull = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
 system(" /usr/bin/zip -j -9 -q \"$FileNameFull\" \"$FileNameOld\" ");
 $tamaño=filesize($FileNameOld);
 $nombrecomp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
 $tamaño_comprimido=filesize($FileNameFull);
 $tipo="application/ms-excel";
 $subidofecha=date("Y-m-d H:i:s", mktime());
 $subidousuario=$_ses_user_login;
 $query="INSERT INTO archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario)  VALUES ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario')";
 $resultado = $db->execute($query) or die($query);

  unlink($FileNameOld);

}


$db->StartTrans();
//query para recuperar todos los datos de la licitacion.
$query1="SELECT * from licitacion WHERE id_licitacion=12000";
$resultados_lic=$db->Execute($query1) or die($query1);

//query para seleccionar la entidad de la licitacion.
$entidad=$resultados_lic->fields['id_entidad'];
$query2="SELECT * from entidad WHERE id_entidad=$entidad";
$resultados_entidad=$db->Execute($query2) or die($query2);

//query para traer los datos de la licitacion.
$query="SELECT * from renglon WHERE id_licitacion=12000";
$resultados=$db->Execute("$query") or  die("$query");
$filas_encontradas=$resultados->RecordCount();

//query para traer de la BD el distrito
$distrito=$resultados_entidad->fields['id_distrito'];
$query_distrito="SELECT nombre from distrito WHERE id_distrito=$distrito";
$resultados_distrito=$db->Execute($query_distrito) or die($query_distrito);

$db->CompleteTrans();

$buffer= "<html>";
$buffer.= "<head>";
$buffer.= "<SCRIPT language='JavaScript' src='funciones.js'></SCRIPT>";
$buffer.= "<title>Untitled Document</title>";
$buffer.= "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
$buffer.= "</head>";
$buffer.= "<body bgcolor='' text='#000000'>";
$buffer.= "<form name='form1' method='post' action='guardar_cotizacion.php'>";
$buffer.= "<tabe border='0' width='100%'>";
$buffer.= "<tr>";
$nombre=$resultados_entidad->fields['nombre'];
$buffer.= "<td width='30%' border='1' bordercolor='#000000'>$nombre</td>";
     for($i=0;$i<60;$i++) {$buffer.= "<td>&nbsp;</td>";} 
$buffer.= "<td width='30%'>Contratacion Directa N&ordm;68<br></td>";
$buffer.= "</tr>";
$buffer.= "<tr>";
$buffer.= "<td>Direccion de Contrataciones</td>";
    for($i=0;$i<60;$i++) {$buffer.= "<td>&nbsp;</td>";} 
$buffer.= "<td>Apertura: 04/09/2003<br></td>";
$buffer.= "</tr>";
$buffer.= "<tr>";
$buffer.= "<td>Ruta 36 Km 601</td>";
    for($i=0;$i<60;$i++) {$buffer.="<td>&nbsp;</td>";} 
$buffer.= "<td>Exp N&ordm;71.385<br></td>";
$buffer.= "</tr>";
$buffer.= "<tr>";
$localidad=$resultados_entidad->fields['localidad'];
$distrito=$resultados_distrito->fields['nombre'];
$buffer.= "<td>$localidad-$distrito</td>";
    for($i=0;$i<60;$i++) {$buffer.= "<td>&nbsp;</td>";} 
$buffer.= "<td>&nbsp;</td>";
$buffer.= "</tr>";
$buffer.= "</table>";
$buffer.= "<br>";
$buffer.= "<br>";
$buffer.= "<div align='center'>";
$buffer.= "PLANILLA DE COTIZACION<br>";
$buffer.= "<br>";
$buffer.= "</div>";
$buffer.= "<table border='1' bordercolor='#000000' width='100%'>";
$buffer.= "<tr>";
$buffer.= "<td colspan='3'>&nbsp;</td>";
$buffer.= "<td align='center' colspan='2' bgcolor='#B0AEBB'><b>Precio</b></td>";
$buffer.= "</tr>";
$buffer.= "<tr bgcolor='#B0AEBB'>";
$buffer.= "<td width='5%'><b>Reng</b></td>";
$buffer.= "<td><b>Cant</b></td>";
$buffer.= "<td align='center'><b>Caracteristica y descipciones Tecnicas</b></td>";
$buffer.= "<td><b>Unitario</b></td>";
$buffer.= "<td><b>Total</b></td>";
$buffer.= "</tr>";

$i=0;
while ($i<=$filas_encontradas) {
	$buffer.= "<tr bordercolor='#000000'>";
	$buffer.= "	<td></td>";
	$renglon=$resultados->fields['id_renglon'];
	$buffer.= "	<td>$renglon</td>";
	$titulo=$resultados->fields['titulo'];
	$buffer.= "<td align='center'>$titulo</td>";
	$total=$resultados->fields['total'];
	$buffer.= "<td></td>";
	$buffer.= "<td>$total</td>";
	$buffer.= "</tr>";
	$resultados->Movenext();
  $i++;
}
$resultados->Movefirst();
$buffer.="<tr>";
$buffer.="<td colspan='3'>&nbsp;</td>";
$buffer.="</tr>";
$buffer.="<tr bgcolor=''>";
$buffer.="<td colspan='2'> </td>";
$buffer.="<td align='right'><b>TOTAL OFERTA 'A'</b></td>";
$buffer.="<td align='right' colspan='2'>30.2514</td>";
$buffer.="</tr>";
$buffer.="<tr bgcolor=''>";
$buffer.="<td colspan='2'> </td>";
$buffer.="<td align='right'><b>TOTAL OFERTA 'B'</b></td>";
$buffer.="<td align='right' colspan='2'>2021.021</td>";
$buffer.="</tr>";
$buffer.="</table>";
$buffer.="<br>";
$buffer.="<br>";
$buffer.="<table>";
$buffer.="<tr>";
$buffer.="<td>Total Oferta 'A' son pesos:</td>";
$buffer.="</tr>";
$buffer.="<tr>";
$buffer.="<td>Total Oferta 'B' son pesos:</td>";
$buffer.="</tr>";
$buffer.="</table>";
$buffer.="<br>";
$buffer.="<b>Los precios son al contado e incluyen I.V.A, flete, embalaje y seguros.</b><br>";
$buffer.="<br>";
$buffer.="PLAZO DE MANTENIMIENTO DE OFERTA: <br> ";
$buffer.="PLAZO DE ENTREGA: <br>";
$buffer.="FORMA DE PAGO: <br>";
$buffer.="LUGAR DE ENTREGA: <br>";
$buffer.="<br>";
$buffer.="</form>";
$buffer.="</body>";
$buffer.="</html>";

echo $buffer;
$licitacion=$parametros['licitacion'];
echo $licitacion;
genera_excel($buffer,$licitacion);
?> 