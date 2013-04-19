<?
/*
Autor: MAC
Fecha: 17/02/06

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2006/02/20 15:58:11 $

*/

include_once("funciones_generales.php");

//buscamos aquellas OC que tienen filas con recepciones que no fueron confirmadas aun
$query="select fila.nro_orden,fila.id_fila,fila.descripcion_prod,fila.desc_adic,fila.cantidad,
               log_rec_ent.fecha as fecha_recepcion
        from compras.fila
		join compras.recibido_entregado using(id_fila)
		join compras.log_rec_ent using(id_recibido)
		where log_rec_ent.recepcion_confirmada=0
		order by nro_orden
		";
$oc_afectadas=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer las OC con recepciones no confirmadas<br>");
$ultima_oc_afectada="";
$oc_avisar=array();
$texto_mail="\nLas siguientes Ordenes de Compra tienen recepciones que aún no han sido confirmadas:\n";
//Agregamos al cuerpo del mail cada fila encontrada
while (!$oc_afectadas->EOF)
{
	//si encontramos una nueva OC, hacemos la división correspondiente
    if($ultima_oc_afectada=="" || $ultima_oc_afectada!=$oc_afectadas->fields["nro_orden"])
    {
    	//seleccionamos el nombre del creador de la OC
			$query="select usuarios.nombre,usuarios.apellido
			        from compras.orden_de_compra join compras.log_ordenes using(nro_orden)
			             join sistema.usuarios on log_ordenes.user_login=usuarios.login
					where nro_orden=".$oc_afectadas->fields["nro_orden"]." and log_ordenes.tipo_log='de creacion'";
			$creador_oc=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el mail del creador de la OC");

    	$ultima_oc_afectada=$oc_afectadas->fields["nro_orden"];
    	$oc_avisar[sizeof($oc_avisar)]=$oc_afectadas->fields["nro_orden"];

    	$texto_mail.="\n----------------------------------------------------------------------------------------------------\n";
    	$texto_mail.="Orden de Compra Nº ".$oc_afectadas->fields["nro_orden"]." - Creada por: ".$creador_oc->fields["nombre"]." ".$creador_oc->fields["apellido"]."\n";
    	$texto_mail.="****************************************************************\n";
    	$texto_mail.="Producto de la Fila || Cantidad Recibida || Fecha Recepcion\n";
    	$texto_mail.="****************************************************************\n";
    }//de if($ultima_oc_afectada=="" || $ultima_oc_afectada!=$oc_afectadas->fields["nro_orden"])

    $descrip_prod=$oc_afectadas->fields["descripcion_prod"]." ".$oc_afectadas->fields["desc_adic"];
    $cant_rec=$oc_afectadas->fields["cantidad"];
    $fecha_rec=$oc_afectadas->fields["fecha_recepcion"];

    $texto_mail.="$descrip_prod  || $cant_rec || $fecha_rec\n";

 	$oc_afectadas->MoveNext();
}//de while(!$oc_afectadas->EOF)

$texto_mail.="\n----------------------------------------------------------------------------------------------------\n";

if($oc_afectadas->RecordCount()>0)
{


	$oc_avisar_mail=implode(",",$oc_avisar);
	$array_para=array();
    $array_para[0]="juanmanuel@coradir.com.ar";
	//traemos el mail del lider la licitacion, si la OC esta asociada a la licitacion
	$query="select nro_orden,mail
			from compras.orden_de_compra left join licitaciones.licitacion using(id_licitacion)
				 left join sistema.usuarios on usuarios.id_usuario=licitacion.lider
			where nro_orden in($oc_avisar_mail)
	       ";
	$lideres=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los lideres de las OC afectadas");

	while (!$lideres->EOF)
	{
		//si la OC tiene lider, lo agregamos al array
		if($lideres->fields["mail"]!="")
		{
	 		if(!in_array($lideres->fields["mail"],$array_para))
	 			$array_para[sizeof($array_para)]=$lideres->fields["mail"];
		}//de if($lideres->fields["mail"]!="")
		else//sino, agregamos el mail del creador de la OC
		{
			//seleccionamos el mail del creador de la OC
			$query="select mail
			        from compras.orden_de_compra join compras.log_ordenes using(nro_orden)
			             join sistema.usuarios on log_ordenes.user_login=usuarios.login
					where nro_orden=".$lideres->fields["nro_orden"]." and log_ordenes.tipo_log='de creacion'";
			$creador=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el mail del creador de la OC");

			if(!in_array($creador->fields["mail"],$array_para))
	 			$array_para[sizeof($array_para)]=$creador->fields["mail"];
		}//del else de if($lideres->fields["mail"]!="")

	 	$lideres->MoveNext();
	}//de while(!$lideres->EOF)


	$para=implode(",",$array_para);
	$asunto="Hay Ordenes de Compra con recepciones sin confirmar";

	enviar_mail($para,$asunto,$texto_mail,'','','','','');
}//de if($oc_afectadas->RecordCount()>0)