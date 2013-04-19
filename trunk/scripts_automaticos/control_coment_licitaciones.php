<?
/*
Autor: MAC
Fecha: 31/07/06

MODIFICADA POR
$Author: mari $
$Revision: 1.5 $
$Date: 2006/11/21 19:48:01 $

*/

include_once("funciones_generales.php");

$fecha_hoy=date("Y-m-d");

//traemos los ultimos comentarios de todas las licitaciones
$query="select max(fecha) as ultima_fecha,id_gestion
		from licitaciones.licitacion
		left join general.gestiones_comentarios on licitacion.id_licitacion=gestiones_comentarios.id_gestion
		join licitaciones.estado using(id_estado)
		left join licitaciones.subido_lic_oc on id_gestion=subido_lic_oc.id_licitacion
		where estado.ubicacion = 'ACTUALES' and licitacion.fecha_apertura <= '$fecha_hoy 23:59:59' AND licitacion.borrada='f'
		and licitacion.es_presupuesto=0 and gestiones_comentarios.tipo='COMUNICAR_CLIENTE' and subido_lic_oc.id_licitacion is null
		group by id_gestion,subido_lic_oc.id_licitacion
		order by id_gestion
";
$lic_coment=$db->Execute($query) or die("<br>Error a al traer los datos de las licitaciones y sus comentarios<br>");

//obtenemos la fecha de hace 1 mes
$fecha_limite_lider=date("Y-m-d 0:00:00",mktime(0, 0, 0, date("m")-1, date("d"),  date("Y")));
$fecha_limite_admin=date("Y-m-d 0:00:00",mktime(0, 0, 0, date("m")-1, date("d")-7,  date("Y")));
//echo "fecha limite lider $fecha_limite_lider - fecha limite admin $fecha_limite_admin<br>";

$licitaciones_sin_comentarios=array();
$licitaciones_aviso_admin=array();
while (!$lic_coment->EOF)
{
	//si la fecha del ultimo comentario es anterior a 1 mes atras, ponemos en la lista para enviar
	//mail de aviso al lider y patrocinador
 	if($lic_coment->fields["ultima_fecha"]<$fecha_limite_lider)
 	{
		$licitaciones_sin_comentarios[sizeof($licitaciones_sin_comentarios)]=$lic_coment->fields["id_gestion"];
 	}

 	//si la fecha del ultimo comentario tiene mas de 1 mes y 7 dias, ponemos en la lista para enviar
 	//mail de aviso a Adrian
 	if($lic_coment->fields["ultima_fecha"]<$fecha_limite_admin)
 	{
 		$licitaciones_aviso_admin[sizeof($licitaciones_aviso_admin)]=$lic_coment->fields["id_gestion"];
 	}

 	$lic_coment->MoveNext();
}//de while(!$lic_coment->EOF)

$tam_arr_lic=sizeof($licitaciones_sin_comentarios);

//para cada licitacion afectada, obtenemos el lider y el patrocinador para enviarle el mail correspondiente
for($i=0;$i<$tam_arr_lic;$i++)
{
	$id_lic_mail=$licitaciones_sin_comentarios[$i];

	$query="select dlider.lider,dpatro.patrocinador,id_licitacion
			from licitaciones.licitacion
			join (select id_usuario,mail as lider from sistema.usuarios)as dlider
				on licitacion.lider=dlider.id_usuario
			join (select id_usuario,mail as patrocinador from sistema.usuarios)as dpatro
				on licitacion.patrocinador=dpatro.id_usuario
			where id_licitacion=$id_lic_mail";
	$datos_resp=$db->Execute($query) or die("<br>Error al traer los datos del lider y patrocinador de la licitacion (numero de iteración: $i)");
	$para_lider=array();
	while (!$datos_resp->EOF)
	{
	 	$para_lider[sizeof($para_lider)]=$datos_resp->fields["lider"];
	 	$para_lider[sizeof($para_lider)]=$datos_resp->fields["patrocinador"];

	 	$datos_resp->MoveNext();
	}//de while(!$datos_resp->EOF)

	$para_lider=elimina_repetidos($para_lider,0);

	$asunto_lider="Hace más de 30 días que no se cargan comentarios de comunicación con el cliente para la licitación Nº $id_lic_mail";
	$texto_lider="Atención: han pasado más de 30 días y no ha tenido comunicación con el cliente de la Licitación Nº $id_lic_mail.\n\n";
	$texto_lider.="Por favor comuníquese con el cliente en forma URGENTE.\n";
	$texto_lider.="Fecha de aviso: ".date("d/m/Y H:i:s")."\n\n\n";

	//enviamos el mail al lider y al patrocinador
	enviar_mail($para_lider,$asunto_lider,$texto_lider,'','','','','');

}//de for($i=0;$i<$tam_arr_lic;$i++)

$tam_arr_admin=sizeof($licitaciones_aviso_admin);
//si hay alguna licitacion con mas de 1 mes y 7 dias sin agregar un comentario, enviamos mail a adrian para avisarle
if($tam_arr_admin>0)
{
	$para_admin="adrian@coradir.com.ar";

	$asunto_admin="Existen Licitaciones presentadas que no tienen nuevos comentarios de comunicación con el cliente desde hace más de 37 días";
	$texto_admin="Las siguientes Licitaciones (en estado Presentadas) no tienen nuevos comentarios de comunicación con el cliente desde hace más de 37 días:\n";
	$texto_admin.=implode(", ",$licitaciones_aviso_admin).".\n\n";
	$texto_admin.="Hace 7 días que se le está enviando un mail de aviso sobre estas licitaciones al Lider y al Patrocinador de cada Licitación.\n\n\n";

	enviar_mail($para_admin,$asunto_admin,$texto_admin,'','','','','');
}//de if($tam_arr_admin>0)


?>