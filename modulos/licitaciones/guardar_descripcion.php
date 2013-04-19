<?php
require_once("../../config.php");
$id_renglon=$parametros["renglon"];
$nro_licitacion=$parametros["licitacion"];
$link= encode_link("realizar_oferta.php",array("renglon" => $id_renglon,
                                          "licitacion" => $nro_licitacion));


//esta funcion te genera el word y te sube el archivo
function genera_word($buffer,$nro_licitacion,$nro_renglon,$nro_item,$nro_alternativa){
global $db;
global $_ses_user_login;
//global $nro_licitacion;
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
$name="Descripcion";
$name.="lic_" ;
$name.=$nro_licitacion;
$name.="_r";
$name.=$nro_renglon;
$name.="_i";
$name.=$nro_item;
$name.="_a";
$name.=$nro_alternativa;
$name.=".doc";
//$temporal=$path."/".$name;
//echo $temporal;

$path1=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$nro_licitacion";
/*
if (!(is_dir($path1))) mkdir($path1,0777);
$path1.=$entidad."/";
if (!(is_dir($path1))) mkdir($path1,0777);
$path1.=$fecha."/";
if (!(is_dir($path1))) mkdir($path1,0777);
$path1.=$nro_licitacion;
if (!(is_dir($path1))) mkdir($path1,0777);
  */
mkdirs($path1);
$temporal=$path1."/".$name;  //linux

$fp = fopen($temporal,"w+");
fwrite($fp,$buffer);
fclose($fp);
$FileNameFull= $temporal;
$FileNameOld = $FileNameFull;
$FileNameFull = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
 system("/usr/bin/zip -j -9 -q \"$FileNameFull\" \"$FileNameOld\" ");
$tamaño=filesize($FileNameOld);
$nombrecomp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
$tamaño_comprimido=filesize($FileNameFull);
$tipo="application/msword";
$subidofecha=date("Y-m-d H:i:s", mktime());
$subidousuario=$_ses_user_login;
$query="INSERT INTO archivos (id_licitacion,nombre,nombrecomp,tamaño,tipo,tamañocomp,subidofecha,subidousuario)  VALUES ($nro_licitacion,'$name','$nombrecomp',$tamaño,'$tipo','$tamaño_comprimido','$subidofecha','$subidousuario')";
$resultado = $db->execute($query) or die($query);

}


///*******************************************************
//fIN DE DECLARACION DE FUNCIONES
//*******************************************************


 $id_renglon=$parametros['renglon'];
 $query="Select * from renglon where id_renglon = $id_renglon";
 $renglon = $db->execute("$query");
 $nro_renglon=$renglon->fields['nro_renglon'];
 $nro_licitacion=$renglon->fields['id_licitacion'];
 $item=$renglon->fields['nro_item'];
 $alternativa=$renglon->fields['nro_alternativa'];


 $query="Select  * from (licitaciones.producto ";
 $query.=" join licitaciones.prioridades on ";
 $query.=" producto.tipo = prioridades.titulo and producto.id_renglon = $id_renglon)";
 $query.=" order by prioridades.id_prioridad; ";

 $resultado_renglon = $db->execute($query);
 $filas_encontradas=$resultado_renglon->RecordCount();
//comienzo a generar el documento word
 $buffer= "<html> <body> <br> <b> <font face=\"Tahoma\" size='2'> " ;
 $buffer.=  "NRO RENGLON: ";
 $buffer.= $renglon->fields['nro_renglon'];
 $buffer.="<br>";
 $buffer.=$renglon->fields['titulo'];
 $buffer.="<br>";
 $buffer.="Cantidad: ";
 $buffer.=$renglon->fields['cantidad'];
 $buffer.="<br><br></b>";
 $buffer.="</font>";
 $buffer.="<table align='left' width=\"80%\" border=\"1\" cellpadding=\"1\" cellspacing=\"0\" bordercolor=\"#000000\">";
 $buffer.="<font face=\"Tahoma\" size='1'>";
 $buffer.="&nbsp&nbsp&nbsp";
 /*
 for($i=0;$i<$filas_encontradas;$i++) {
     $id_producto=$resultado_renglon->fields['id_producto'];
     $query1="Select * from descripciones where id_producto = $id_producto order by id_producto ";
     $resultados = $db->Execute("$query1") or die($db->ErrorMsg());
     $contador=$resultados->RecordCount();
               for($y=0;$y<$contador;$y++){
                   $titulo=$resultados->fields["titulo"];
                   $contenido=$resultados->fields["contenido"];
                   $buffer.="<tr>";
                   $buffer.="<td width='15%' valign='top'><b>$titulo</b></td>";
                   $buffer.="<td width='80%'>$contenido</td>";
                   $buffer.="</tr>";
                   $resultados->MoveNext();
                   }
    $resultado_renglon->MoveNext();
} //termino el for
*/
//parte nueva
for($i=0;$i<$filas_encontradas;$i++) {
     $id_producto=$resultado_renglon->fields['id_producto'];
     $query1="Select  * from (licitaciones.descripciones ";
     $query1.=" join licitaciones.prioridades on ";
     $query1.=" descripciones.titulo = prioridades.titulo and descripciones.id_producto = $id_producto)";
     $query1.=" order by prioridades.id_prioridad; ";
     $resultados = $db->Execute($query1) or die($query1);
     $contador=$resultados->RecordCount();
               for($y=0;$y<$contador;$y++){
                   $titulo=$resultados->fields["titulo"];
                   $contenido=$resultados->fields["contenido"];
                   $buffer.="<tr>";
                   $buffer.="<td width='15%' valign='top'><b>$titulo</b></td>";
                   $buffer.="<td width='80%'>$contenido</td>";
                   $buffer.="</tr>";
                   //el siguiente item dentro de esa descripcion
                   $resultados->MoveNext();
                   }
    //avanzo al  siguiente producto
    $resultado_renglon->MoveNext();
} //termino el for

$buffer.="</table>";
$buffer.="</body>";
$buffer.="</html>";
//echo $buffer;
/*
*/
//echo $buffer;
//echo $nro_licitacion;
genera_word($buffer,$nro_licitacion,$nro_renglon,$item,$alternativa);
header("Location:$link");

?>