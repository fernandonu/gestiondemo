<?include("../../config.php");
$nro_licitacion=$parametros['licitacion'];
$nro_renglon=$parametros['id_renglon'];


$datos_licitacion="SELECT licitacion.id_licitacion,licitacion.ultimo_usuario,licitacion.ultimo_usuario_fecha FROM licitaciones.licitacion WHERE id_licitacion=".$nro_licitacion;
$resultados=$db->Execute($datos_licitacion) or die($db->ErrorMsg().$datos_licitacion); 
//recupero los datos del renglon
$datos_renglon="SELECT licitaciones.entidad.nombre,licitaciones.renglon.usuario,renglon.codigo_renglon,renglon.usuario_time,renglon.cantidad,renglon.titulo,renglon.nro_version FROM (renglon join licitacion on licitacion.id_licitacion=renglon.id_licitacion) join entidad on entidad.id_entidad=licitacion.id_entidad WHERE id_renglon=$nro_renglon";
$resultados_renglon=$db->Execute($datos_renglon) or die($db->ErrorMsg().$datos_renglon); 
    $nro=$resultados_renglon->fields["codigo_renglon"];
    $query_productos="SELECT id FROM producto WHERE id_renglon=$nro_renglon";
	$resultados_productos=$db->Execute($query_productos) or die($db->ErrorMsg().$query_productos); 
	$id_prod=$resultados_productos->fields['id'];
	$cant_prod=$resultados_productos->RecordCount();
	$c=0;
	$resultados_productos->Move(0);
	while(!$resultados_productos->EOF)
	{$ids_prod[$c]=$resultados_productos->fields['id'];
     $c++;
     $resultados_productos->MoveNext();	
	} 
?>

<html>
<head>
<title>Imprimir</title>
</head>
<body onload="window.print(); window.close();">

<?php
$buffer="<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nº Renglon: ".$resultados_renglon->fields["codigo_renglon"]."</b><br><center>
<TABLE border='0' width='90%'>
 <tr>
 <td><b>Cantidad:".$resultados_renglon->fields['cantidad']."</b></td>
 </tr>
 <tr>
     <td align='left'><b>Titulo del renglon:".$resultados_renglon->fields['titulo']."</b></td>
  </tr>		
 </table>";
$buffer.="<TABLE border='1' bordercolor='black' BORDER CELPADDING=10 CELLSPACING=0 width='90%'>
		<tr>
 		<th>Producto</th><th>Descripcion</th>
		</tr>";

if($resultados_renglon->fields['nro_version']==0)
 $modificacion=0;
else  
 $modificacion=1;

if($_POST["desc_org"]=="traer")
 $modificacion=1;
 
$query="select descripciones_renglon.contenido,descripciones_renglon.titulo 
from (licitaciones.renglon join licitaciones.producto on producto.id_renglon=renglon.id_renglon and renglon.id_renglon=$nro_renglon) 
join descripciones_renglon using (id) join prioridades on descripciones_renglon.titulo=prioridades.titulo order by id_prioridad";
$resultados=$db->Execute($query) or die($db->ErrorMsg()."$query");  

while(!$resultados->EOF)
{
 $titulo=$resultados->fields['titulo'];	
 $contenido=$resultados->fields['contenido'];	
 $buffer.="<tr>
	 	    <td>
		     $titulo
		    </td>
		    <td>
             $contenido
            </td>
           </tr>";
 $resultados->MoveNext();
} 






$buffer.="</table></center>";

echo $buffer;
?>

</body>
</html>
