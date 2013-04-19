<?php
/*
$Author: nazabal $
$Revision: 1.4 $
$Date: 2005/02/23 18:28:52 $
*/
include "head.php";

variables_form_busqueda("casos_admin");

if (!$sort) $sort="1";

if ($cmd == "") {
	$cmd="en_curso";
	$_ses_casos_admin["cmd"] = $cmd;
	phpss_svars_set("_ses_casos_admin", $_ses_casos_admin);
}

$datos_barra = array(
					array (
					  "descripcion" => "En Curso",
					  "cmd" => "en_curso"
						),

					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "pendiente"
						),
					array(
						"descripcion"	=> "Todos",
						"cmd"			=> "todos"
						),

				 );


$orden = array(
"default" => "1",
"default_up" => 0,
"1" => "reporte_tecnico.fecha",
"2" => "reporte_tecnico.id_licitacion",
"3" => "entidad.nombre",
"4" => "reporte",
"5" => "usuarios.nombre",
"6" => "reporte_tecnico.estado",
);

$filtro = array(
"reporte_tecnico.fecha" => "Fecha",
"reporte_tecnico.id_licitacion" => "Id Licitacion",
"entidad.nombre" => "Entidad",
"reporte" => "Reporte Técnico",
"usuarios.nombre" => "Reportado por",
"reporte_tecnico.estado" => "Estado",
);

$sql_tmp="Select id_reporte,reporte_tecnico.fecha,licitacion.id_licitacion,entidad.nombre,reporte,usuarios.nombre as usuario,usuarios.apellido,reporte_tecnico.estado FROM reporte_tecnico "
	."LEFT JOIN licitacion USING (id_licitacion) INNER JOIN entidad USING (id_entidad) "
	."LEFT JOIN usuarios USING (id_usuario)";

if ($cmd=="en_curso")
    $where_temp="estado='Pendiente'";
if ($cmd=="pendiente")
    $where_temp="estado='Solucionado'";

$link_temp = Array(
	"sort" => $sort,
	"up" => $up,
	"filter" => $filter,
	"keyword" => $keyword,
);

echo "<form action='reporte_tecnico.php' name='buscar' method='post'>";
echo "<input type=hidden name=sort value='$sort'>\n";
echo "<table width=99% border=0 cellspacing=5 cellpadding=5>\n";
echo "<tr><td colspan=6 align=center>\n";

generar_barra_nav($datos_barra);


list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_temp,$where_temp,"buscar");

$rs1 = $db->Execute($sql) or die($db->ErrorMsg());
$rs1->MoveFirst();
echo "<input type='submit' name=enviar value=Buscar>\n";
echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#ffffff' align=center>";
echo "<tr><td style='border-right: 0;' colspan=2 align=left id=ma>\n";
echo "<b>Total:</b> ".$total." Reportes.</td>\n";
echo "<td style='border-left: 0;' colspan=5 align=right id=ma>$link_pagina</td></tr>\n";
$link_temp["page"]=$page;
$link_temp["up"]=$up2;
$link_temp["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("reporte_tecnico.php",$link_temp)."'>Fecha</a></td>\n";
$link_temp["sort"]=2;
echo "<td align=right id=mo><a id=mo href='".encode_link("reporte_tecnico.php",$link_temp)."'>Id Lic.</a></td>\n";
$link_temp["sort"]=3;
echo "<td align=right width=60 id=mo><a id=mo href='".encode_link("reporte_tecnico.php",$link_temp)."'>Entidad</a></td>\n";
$link_temp["sort"]=4;
echo "<td align=right id=mo><a id=mo href='".encode_link("reporte_tecnico.php",$link_temp)."'>Reporte</a></td>\n";
$link_temp["sort"]=5;
echo "<td align=right id=mo><a id=mo href='".encode_link("reporte_tecnico.php",$link_temp)."'>Reportado por</a></td>\n";
$link_temp["sort"]=6;
echo "<td id=mo><a id=mo href='".encode_link("reporte_tecnico.php",$link_temp)."'>Estado</a></td>\n";
$link_temp["sort"]=7;
echo "<td id=mo>Tiempo Transcurrido</td>\n";
echo "</tr>\n";

while (!$rs1->EOF) {
	$ref = encode_link("reporte.php",Array("id"=>$rs1->fields["id_reporte"]));
	tr_tag($ref,"title=\"$title\"");
	//echo "<tr>";
	$fecha=substr($rs1->fields["fecha"],0,10);
	$hora=substr($rs1->fields["fecha"],11,8);
	$fech=fecha($fecha);
	echo "<td align=center style='font-size: 9pt;'>$fech</td>\n";
	echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs1->fields["id_licitacion"]."</td>\n";
	echo "<td align=left width=80 style='font-size: 9pt;'>&nbsp;".$rs1->fields["nombre"]."</td>\n";
	echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs1->fields["reporte"]."</td>\n";
	echo "<td align=left style='font-size: 9pt;' $bgcolo>&nbsp;".$rs1->fields["usuario"]." ".$rs1->fields["apellido"]."</td>\n";
	echo "<td align=left style='font-size: 9pt;' $bgcolo>&nbsp;".$rs1->fields["estado"]."</td>\n";
	$fechahoy=date ("d-m-Y");
	echo "<td align=center>".diferencia_dias($fech,$fechahoy)." Dias.</td>";
	echo "</tr>\n";
	$rs1->MoveNext();
}
echo "</table>\n";
?>