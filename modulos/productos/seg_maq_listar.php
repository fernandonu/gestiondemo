<?
/*
Autor: GACZ

MODIFICADA POR
$Author: broggi $
$Revision: 1.14 $
$Date: 2005/02/15 17:27:57 $
*/

require_once("../../config.php");

if ($_POST[breset])
{
	$_POST[keyword]="";
 	$_POST[filter]=" ";	
}

variables_form_busqueda("seg_maq");

if ($_POST[filter])
{
	$filter=$_POST[filter];
	$keyword=$_POST[keyword];
	//echo $filter;
}

//para buscar por serial
if ($filter=='op.nserie_desde')
{
	//$filter="'$keyword'";
	$where_tmp="op.nro_orden in (select nro_orden from maquina where maquina.nro_serie ilike '%$keyword%')";
}
if ($filter=='all')
	$where_tmp="op.nro_orden in (select nro_orden from maquina where maquina.nro_serie ilike '%$keyword%')";


$q ="select distinct(op.nro_orden),op.*,e.nombre as entidad,d.nombre as distrito from ";
$q.="orden_de_produccion op left join renglon using (id_renglon) ";
//$q.="left join licitacion l using(id_licitacion) join ";
$q.="join entidad e USING(id_entidad) ";
$q.="left join licitaciones.licitacion l on l.id_licitacion=op.id_licitacion ";
$q.="join distrito d on e.id_distrito=d.id_distrito ";
//$q.="order by nserie_desde";

//para buscar por ensamblador
if ($filter=="en.nombre" || $filter=="all")
{
	$q.="join ";
	$q.="ensamblador en using(id_ensamblador) ";
	
}

//para busacar en Ordenes de Compra
if (ereg("oc.|p.razon_social",$filter) || $filter=="all")
{
	$q.="join ";
	//q.="orden_de_compra oc using(id_licitacion) ";
	$q.="compras.orden_de_compra oc on oc.id_licitacion=op.id_licitacion ";
	
	//para buscar por proveedor
	if ($filter=="p.razon_social" || $filter=="all")
	{
		$q.="join ";
		$q.="proveedor p using(id_proveedor) ";
	}
}


	$orden = array(
		"default" => "1",
		"default_up" => "1",
		"1" => "nserie_desde",
		"2" => "l.id_licitacion",
		"3" => "e.entidad",
		"4" => "d.distrito"
	);

	$filtro = array(
//--Licitacion
//		"0" => "-----------Licitacion------------",
		"l.id_licitacion" => "ID Lic",
		"l.nro_lic_codificado" => "Nº de licitación",
		"d.nombre" => "Distrito",
		"e.nombre" => "Entidad",
		"l.observaciones" => "Comentarios (Lic)",
		"l.mant_oferta_especial" => "Mantenimiento de oferta",
		"l.forma_de_pago" => "Forma de pago",
//--Ordenes de Produccion
//		"1" => "-----Orden de Produccion-----",
		"op.nro_orden" => "Nº de Orden (OP)",		
		"op.nserie_desde" => "Serial",
		"en.nombre" => "Ensamblador",
//--Ordenes de compra
//		"2" => "------Orden de Compra------",
		"oc.nro_orden" => "Nº de Orden (OC)",		
		"oc.notas" => "Comentarios (OC)",
		"p.razon_social" => "Proveedor",
	);

$itemspp = 50;
?>
<html>
<head>
<title>Lista de Maquinas (Lotes) </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header?>
<?php
include("../ayuda/ayudas.php");
?>

</head>
<body>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
</script>
<form name="form1" method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>">
<center>
<? 
//para que el primer formulario de busqueda no se imprima en el browser
ob_start();	

	//para buscar por serial ver al principio
	//echo $q;die();
	list($q_tmp,$total,$link,$up)=form_busqueda($q,$orden,$filtro,$link_tmp,$where_tmp); 

	//para buscar tambien por serial
	if ($filter=='all')
	{
		$exp=addcslashes("AND $where_tmp","(%.");
		$q_tmp=eregi_replace($exp,"OR $where_tmp",$q_tmp);
	}

//expresion regular para contar permite el uso de la clausula where in
//y tambien el uso de distinct GACZ.
$q_count=eregi_replace("select distinct\((.*)\).* from (.*where .* in .*)order by.*limit.*$|select distinct\((.*)\).* from (.*)order by.*limit.*$","select count(distinct(\\1\\3)) as total from \\2\\4 ",$q_tmp);	
//														1								2															3				 4						5	

//borro lo del buffer de headers
ob_clean();
echo "<br>";
//consulta definitiva
list($q,$total,$link,$up)=form_busqueda($q,$orden,$filtro,$link_tmp,$where_tmp,$q_count); 

	//para buscar tambien por serial
	if ($filter=='all')
	{
		$exp=addcslashes("AND $where_tmp","(%.");
		$q=eregi_replace($exp,"OR $where_tmp",$q);
	}

//echo "<br>CONTAR: ".$q_count;
//echo "<br><br>SQL: ".$q;


$maquinas= sql($q) or fin_pagina();
?>
<input type="submit" name="bbuscar" style="width:100" value="Buscar" onclick="return chk_select()">
<input type="submit" name="breset" style="width:110" value="Borrar Búsqueda">

<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/productos/ayuda_compu_cdr.htm" ?>', 'COMPUTADORAS CDR')" >
    </div>
</center>
  <br>
  <table width="99%" class='bordessininferior' align="center" cellpadding=2 cellspacing=2 id="tabla_resumen">
    <tr id=ma height=20 >
      <td align="left">Resultado: <?= $total." Ordenes de Producción encontradas" ?> </td>
      <td align="right" width="50%" >&nbsp;<?=$link ?></td>
      </tr>
  </table>

  <table width="99%" class='bordessinsuperior' align="center" cellpadding="2" cellspacing="2" id="tabla_resultados">
    <tr id="mo" height=20 align="center">
      <td width=10%>ID Lic.</td>
      <td>Serial</td>
      <td width=40%>Entidad</td>
      <td width=30%>Distrito</td>
    </tr>
    <? 
$i=0;
$maquinas->MoveFirst();
	while (!$maquinas->EOF)
	{
		 //$tr_color=(((++$i)%2)==0)?$bgcolor1:$bgcolor2;
?>
    <!--<tr bgcolor='<?= $tr_color ?>' onMouseOver="sobre(this,'#FFFFFF')" onMouseOut="bajo(this,'<?= $tr_color ?>')" onclick="location.href='<?= encode_link("seg_maq.php",array("ordprod"=>$maquinas->fields[nro_orden])) ?>'">-->
<?    $link=encode_link("seg_maq.php",array("ordprod"=>$maquinas->fields[nro_orden]));
      tr_tag($link);
?>
      <td align=right> 
        <?=$maquinas->fields[id_licitacion] ?>
      </td>
      <td align=center> 
        <?=(($maquinas->fields[cantidad] >1)?$maquinas->fields[nserie_desde]."<br>...<br>".$maquinas->fields[nserie_hasta]:$maquinas->fields[nserie_desde]);  ?>
      </td>
      <td align=center> 
        <?= $maquinas->fields[entidad] ?>
      </td>
      <td align=center> 
        <?= $maquinas->fields[distrito] ?>
      </td>
    </tr>
    <? 
	 $maquinas->MoveNext();	
	}
?>
  </table>
</form>
<script>
//Los valores de las opciones existentes se mueven hacia abajo
function add_option(listbox_name,position,text,value)
{
	listbox=eval("document.all."+listbox_name);	

	var selected=(listbox.selectedIndex >=position)?listbox.selectedIndex+1:listbox.selectedIndex;
	var j=++listbox.length;
	for (; j > position ;j--)
	{
	 	listbox.options[j-1].text=listbox.options[j-2].text;
  		listbox.options[j-1].value=listbox.options[j-2].value;
	}
		var opt1=new Option(text, value);
	 	listbox.options[position]=opt1;
	 	listbox.selectedIndex=selected;
}

add_option("filter",1,"-----------Licitacion------------",0);
add_option("filter",9,"-----Orden de Produccion-----",0);
add_option("filter",13,"------Orden de Compra------",0);

function chk_select()
{

if (document.all.filter.options[document.all.filter.selectedIndex].value==0)
	return false;
else
	return true;
	
}
</script>

<?//=fin_pagina() ?>  