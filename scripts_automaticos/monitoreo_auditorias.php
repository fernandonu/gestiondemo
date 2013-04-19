<?php
/*
AUTOR: Gabriel (15/11/2005)
MODIFICADO POR:
$Author: ferni $
$Revision: 1.2 $
$Date: 2006/11/24 17:33:17 $
*/

include("funciones_generales.php");

/////////////////////////////////// envío de email al acercarse una auditoria ////////////////////////////////
$consulta="select departamentos_afectados.id_auditoria_calidad, auditorias_calidad.tipo, auditorias_calidad.titulo, 
		auditorias_calidad.fecha_desde, auditorias_calidad.fecha_hasta, mails.mail, 
		case when ((fecha_desde-30)=CURRENT_DATE) then 1
			else 0
		end as en_un_mes,
		case when ((fecha_desde-7)=CURRENT_DATE) then 1
			else 0
		end as en_una_semana,
		case when ((fecha_desde)=CURRENT_DATE) then 1
			else 0
		end as hoy
	from calidad.auditorias_calidad
		join calidad.departamentos_afectados using(id_auditoria_calidad)
		join (
			select id_auditoria_calidad, licitaciones.unir_texto(
					case when mail_departamento!='' then mail_departamento||', '
					end
				) as mail
			from calidad.departamentos_afectados
				join general.departamentos_empresa using(id_departamento_empresa)
			group by departamentos_afectados.id_auditoria_calidad
		)as mails using (id_auditoria_calidad)
	where estado_auditoria='p'
	group by departamentos_afectados.id_auditoria_calidad, auditorias_calidad.tipo, auditorias_calidad.titulo, 
		auditorias_calidad.fecha_desde, auditorias_calidad.fecha_hasta, mails.mail";
$rta_consulta=$db->Execute($consulta) or die("c37: Error al traer las auditorías de próxima ejecución: ".$consulta);
$i=0;
if ($rta_consulta->recordCount()>0){
	$contenido="";

	while (!$rta_consulta->EOF){
		if ($rta_consulta->fields["en_un_mes"]==1){
			$contenido="Auditoría/s que se realizará/n en su departamento en un mes:\n";
			$contenido.="Auditoría nro. ".$rta_consulta->fields["id_auditoria_calidad"]." -> "
				.$rta_consulta->fields["titulo"]." (".$rta_consulta->fields["tipo"]."): desde "
				.$rta_consulta->fields["fecha_desde"]." hasta ".$rta_consulta->fields["fecha_hasta"]."\n";
		}elseif ($rta_consulta->fields["en_una_semana"]==1){
			$contenido="Auditoría/s que se realizará/n en su departamento en una semana:\n";
			$contenido.="Auditoría nro. ".$rta_consulta->fields["id_auditoria_calidad"]." -> "
				.$rta_consulta->fields["titulo"]." (".$rta_consulta->fields["tipo"]."): desde "
				.$rta_consulta->fields["fecha_desde"]." hasta ".$rta_consulta->fields["fecha_hasta"]."\n";
		}elseif ($rta_consulta->fields["hoy"]==1){
			$contenido="Auditoría/s que se realizará/n en su departamento hoy:\n";
			$contenido.="Auditoría nro. ".$rta_consulta->fields["id_auditoria_calidad"]." -> "
				.$rta_consulta->fields["titulo"]." (".$rta_consulta->fields["tipo"]."): desde "
				.$rta_consulta->fields["fecha_desde"]." hasta ".$rta_consulta->fields["fecha_hasta"]."\n";
		}
		if (($contenido)&&($rta_consulta->fields["mail"])){
			$asunto="Monitoreo automático de Auditorías de calidad";
			$contenido.="\n\n\nChequeo automático de auditorías: ".date("d/m/Y H:m")."\n";
			$avisos[$i]["contenido"]=$contenido;
			$avisos[$i]["asunto"]=$asunto;
			$avisos[$i]["to"]=substr($rta_consulta->fields["mail"], 0, strlen($rta_consulta->fields["mail"])-2);
			$i++;
		}
		$rta_consulta->moveNext();
	}
}

for($i=0; $i<count($avisos); $i++){
	//print_r($avisos[$i]["to"]."<br>".$avisos[$i]["asunto"]."<br>".$avisos[$i]["contenido"]."<br><br>");
	enviar_mail($avisos[$i]["to"], $avisos[$i]["asunto"], $avisos[$i]["contenido"], "", "", "", 0);
}
?>