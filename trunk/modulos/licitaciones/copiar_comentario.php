<?
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: cestila $
$Revision: 1.1 $
$Date: 2004/09/16 18:33:40 $
*/
require_once("../../config.php");
echo $html_header;
variables_form_busqueda("copiar_comentario");
$id_cobranza=$_POST["id_cobranza"] or $id_cobranza=$parametros["id_cobranza"];
if ($parametros["modo"]=="Copiar") {
	//print_r($parametros);
	$sql=Array();
	$query="select id_primario from atadas where id_secundario=".$parametros["copiar"];
	$rs=$db->execute($query) or die($db->errormsg());
	if ($rs->RecordCount()>0)
		$id_copiar=$rs->fields["id_primario"];
	else
		$id_copiar=$parametros["copiar"];
	$query="select id_comentario from gestiones_comentarios where tipo='COBRANZAS' and id_gestion=$id_cobranza";
	$comentarios=$db->execute($query) or die($db->errormsg());
	while ($fila=$comentarios->fetchrow()) {
		$sql[]="UPDATE gestiones_comentarios SET id_gestion=$id_copiar WHERE id_comentario=".$fila["id_comentario"];
		if ($id_copiar!=$parametros["copiar"]) {
			$sql[]="UPDATE atadas_comentarios SET id_cobranza=".$parametros["copiar"]." WHERE id_comentario=".$fila["id_comentario"];
		}
	}
	sql($sql) or fin_pagina();
	echo "<script>window.close();</script>\n";
}
if (!$sort) $sort="1";	
$orden = array(
	"default" => "1",
	"default_up" => "1",
	"1" => "nro_carpeta",
	"2" => "id_licitacion",
	"3" => "entidad.nombre",
	"4" => "nro_factura",
	"5" => "cobranzas.nombre",
);
$filtro = array(
	"nro_carpeta" => "Carpeta",
	"id_licitacion" => "ID Licitación",
	"entidad.nombre" => "Cliente",
	"nro_factura" => "Número de factura",
);

echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<input type=hidden name=sort value='$sort'>\n";
echo "<input type=hidden name=id_cobranza value='$id_cobranza'>\n";
$sql_temp="SELECT id_cobranza,nro_carpeta,cobranzas.nro_factura,entidad.nombre,cobranzas.nombre as nombre_cobranza,cobranzas.id_licitacion from cobranzas";
$sql_temp.=" LEFT JOIN entidad USING (id_entidad)";
$where_temp="cobranzas.estado='PENDIENTE'";

$link_temp = Array(
	"sort" => $sort,
	"up" => $up,
	"filter" => $filter,
	"keyword" => $keyword
);
list($sql,$total_reg,$link_pagina,$up) = form_busqueda($sql_temp,$orden,$filtro,$link_temp,$where_temp,"buscar");
$rs1 = $db->Execute($sql) or die($db->ErrorMsg(). "- $sql");
$rs1->MoveFirst();
echo "<input type='submit' name=enviar value=Buscar>\n";
echo "</form>\n";
echo "<table border=1 width=99% cellspacing=0 cellpadding=1 bordercolor='#ffffff' align=center>";
echo "<tr><td id=mo colspan=5>\n";
echo "<font size=3><b>Seleccione a que cobranza copia los comentarios</b></font>\n";
echo "</td></tr>\n";
echo "<tr><td style='border-right: 0;' align=left colspan=2 id=ma>\n";
echo "<b>Total:</b> ".$total." Renglones.</td>\n";
echo "<td style='border-left: 0;' colspan=3 align=right id=ma>&nbsp;$link_pagina&nbsp;</td></tr>\n";
$link_temp["page"]=$page;
$link_temp["up"]=$up2;
$link_temp["sort"]=1;
echo "<tr><td align=right id=mo><a id=mo href='".encode_link("copiar_comentario.php",$link_temp)."'>Nro Carpeta</a></td>\n";
$link_temp["sort"]=2;
echo "<td align=right id=mo><a id=mo href='".encode_link("copiar_comentario.php",$link_temp)."'>Id Lic.</a></td>\n";
$link_temp["sort"]=3;
echo "<td align=right id=mo><a id=mo href='".encode_link("copiar_comentario.php",$link_temp)."'>Cliente</a></td>\n";
$link_temp["sort"]=4;
echo "<td align=right id=mo><a id=mo href='".encode_link("copiar_comentario.php",$link_temp)."'>Nro Factura</a></td>\n";
$link_temp["sort"]=5;
echo "<td align=right id=mo><a id=mo href='".encode_link("copiar_comentario.php",$link_temp)."'>Nombre</a></td>\n";
echo "</tr>\n";
while (!$rs1->EOF) {
	$ref = encode_link("copiar_comentario.php",Array("id_cobranza"=>$id_cobranza,"copiar"=>$rs1->fields["id_cobranza"],"modo"=>"Copiar"));
	tr_tag($ref,"title=\"$title\"");
	//echo "<tr>";
	echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs1->fields["nro_carpeta"]."</td>\n";
	echo "<td align=left width=80 style='font-size: 9pt;'>&nbsp;".$rs1->fields["id_licitacion"]."</td>\n";
	echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs1->fields["nombre"]."</td>\n";
	echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs1->fields["nro_factura"]."</td>\n";
	echo "<td align=left style='font-size: 9pt;'>&nbsp;".$rs1->fields["nombre_cobranza"]."</td>\n";
	echo "</tr>\n";
	$rs1->MoveNext();
}
echo "</table>\n";
fin_pagina();
?>