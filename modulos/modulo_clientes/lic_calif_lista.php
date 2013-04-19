<?
/*
Autor: GACZ

MODIFICADA POR
$Author: broggi $
$Revision: 1.19 $
$Date: 2005/03/10 19:09:37 $
*/

require_once("../../config.php");
variables_form_busqueda("encuesta");
if (!$cmd) {
	$cmd="pendientes";
	$_ses_encuesta["cmd"] = $cmd;
	phpss_svars_set("_ses_encuesta", $_ses_encuesta);
}

$datos_barra = 
array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes"
						),
					array(
						"descripcion"	=> "Terminadas",
						"cmd"			=> "terminadas"
						)/*,
					array(
						"descripcion"	=> "Estadísticas",
						"cmd"			=> "estadisticas"
						)	*/
	 );
?>
<html>
<head>
<title>Lista de Licitaciones </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header?>
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
function link_sobre(src) {
    src.id='me';
}
function link_bajo(src) {
    src.id='mi';
}
</script>
<form name="form1" method="post" action="">
<!--	  
  <table width="100%" border="1" cellpadding="1" cellspacing="0" id="tabla_nav">
    <tr> 
      <td align="center" bgcolor="#FFFFCC">Pendientes</td>
      <td align="center">Terminadas</td>
    </tr>
  </table>
-->
<? generar_barra_nav($datos_barra); ?>
  <br>
  <table align='center'><tr><td>
<?
$campos="licitacion.id_licitacion,licitacion.fecha_entrega,entidad.nombre as nbre_entidad,distrito.nombre as nbre_distrito";
 $q = "licitacion join 
		entidad using(id_entidad) join 
		distrito using(id_distrito) ";
 $q.=" join (select id_licitacion from renglon join historial_estados using(id_renglon) join estado_renglon using(id_estado_renglon) where renglon.tipo ilike '%Computadora%' and estado_renglon.nombre='Orden de Compra' group by id_licitacion) as renglones using (id_licitacion)";
 $itemspp = 10000;
 //if ($cmd=='terminadas' || $cmd=="estaditicas")
 if ($cmd=='terminadas')
 {
 	$orden = array(
		"default" => "2",
		"default_up" => "0",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.fecha_entrega",
		"3" => "entidad.nombre",
		"4" => "distrito.nombre"
	);
	
	$filtro = array(
		"licitacion.id_licitacion" => "ID de licitación",
		"entidad.nombre" => "Entidad",
		"distrito.nombre" => "Distrito"
	);
	$campos.=",res.*";
	$q="select $campos from $q ";
	$q.=" join encuesta_lic using (id_licitacion) left join ";
	$q.="(select id_encuesta,sum(puntaje) as puntaje_suma, count(puntaje) as puntaje_cantidad from resultados where puntaje>0 group by id_encuesta) as res using (id_encuesta)";
	$where_tmp = "(extract(year from fecha_entrega) >= extract(year from current_date)-1)";
//	$q.=" order by id_encuesta ";
	list($sql,$total_usr,$link_pagina,$up) = form_busqueda($q,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
	echo "&nbsp;&nbsp;<input type=submit name=buscar value='Buscar'>\n";
	$licitaciones= sql($sql) or reportar_error($sql,__FILE__,__LINE__);
	$suma=0;
	$i=0;
	while (!$licitaciones->EOF)
	{if($licitaciones->fields['puntaje_cantidad']>0)
	 {$promedios[$i]=number_format($licitaciones->fields['puntaje_suma']/$licitaciones->fields['puntaje_cantidad'],2,".","");
	  $suma+=$promedios[$i++];
	 } 
	 $licitaciones->MoveNext();
	}
	$media=($i)?number_format($suma/$i,2,",",""):0;	
 }
 elseif ($cmd=='pendientes') //pendientes
 {
 	$orden = array(
		"default" => "2",
		"default_up" => "1",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.fecha_entrega",
		"3" => "entidad.nombre",
		"4" => "distrito.nombre"
	);
	
	$filtro = array(
		"licitacion.id_licitacion" => "ID de licitación",
		"entidad.nombre" => "Entidad",
		"distrito.nombre" => "Distrito"
	);
	$q="select $campos from $q ";
	$where=" fecha_entrega is not null and fecha_entrega < (current_date - 30) and (extract(year from fecha_entrega) >= extract(year from current_date)-1)";
	$where.=" and id_licitacion not in (select id_licitacion from encuesta_lic)";
	list($sql,$total_usr,$link_pagina,$up) = form_busqueda($q,$orden,$filtro,$link_tmp,$where,"buscar");
	echo "&nbsp;&nbsp;<input type=submit name=buscar value='Buscar'>\n";
	$licitaciones= sql($sql) or reportar_error($sql,__FILE__,__LINE__);
 }
if($cmd!="estadisticas")
{//funciona como antes...

 echo "<center>{$parametros['msg']}</center>";
 ?>
 </td></tr></table>

  <table width="100%" border=0 cellpadding=1 cellspacing=1 id="tabla_resumen">
    <tr id=ma height=20 >
      <td align="left">Resultado: <? echo (($licitaciones)?$licitaciones->RecordCount():0 )." Licitaciones encontradas"; ?> </td>
 <? if ($cmd=='terminadas')
	{
 ?>
		<td align="right" width='50%'>Promedio: <?= $media ?></td>
 <?
	}
 ?>
    </tr>
  </table>

  <table width="100%" border="0" cellpadding="1" cellspacing="2" id="tabla_resultados">
    <tr id="mo" height=20 align="center"> 
      <td><a id=mo href='<? echo encode_link($_SERVER['PHP_SELF'],array("sort"=>"1","up"=>$up)); ?>'>ID</a></td>
      <td><a id=mo href='<? echo encode_link($_SERVER['PHP_SELF'],array("sort"=>"2","up"=>$up)); ?>'>Entrega</a></td>
      <td width=40%><a id=mo href='<? echo encode_link($_SERVER['PHP_SELF'],array("sort"=>"3","up"=>$up)); ?>'>Entidad</a></td>
      <td width=30%><a id=mo href='<? echo encode_link($_SERVER['PHP_SELF'],array("sort"=>"4","up"=>$up)); ?>'>Distrito</a></td>
 <? if ($cmd=='terminadas') 
	{
 ?>

      <td width=10%>Calificacion</td>
 <?
	}
 ?>
    </tr>
 <? 
 $i=0;
 $licitaciones->MoveFirst();
	while (!$licitaciones->EOF)
	{
		 $tr_color=(((++$i)%2)==0)?$bgcolor1:$bgcolor2;
 ?>
    <tr bgcolor='<?= $tr_color ?>' onMouseOver="sobre(this,'#FFFFFF')" onMouseOut="bajo(this,'<?= $tr_color ?>')" onclick="location.href='<?= encode_link("lic_calif_nueva.php",array("id_licitacion"=>$licitaciones->fields[id_licitacion])) ?>'">
      <td align=center><?= $licitaciones->fields['id_licitacion'] ?></td>
      <td align=center><?= Fecha($licitaciones->fields['fecha_entrega']) ?></td>
      <td align=center><?= $licitaciones->fields['nbre_entidad'] ?></td>
      <td align=center><?= $licitaciones->fields['nbre_distrito'] ?></td>
 <? if ($cmd=='terminadas') 
	{
 ?>
      <td align=right> &nbsp; <?=number_format($promedios[$i-1],2,",","") ?></td>
 <?
	}
 ?>
    </tr>
    
 <? 
	 $licitaciones->MoveNext();	
	}
 ?>
  </table>
 </form>
 <?
}//de if($cmd!="estadisticas")
else
{
 $j=0;
?>
</td></tr></table>
<br>
 <table width="95%" border=0 cellpadding=1 cellspacing=1 id="tabla_promedios" align="center">
  <tr id=mo>
   <td width="90%">
    Pregunta
   </td>
   <td>
    Promedio
   </td> 
 <tr> 	
<?
 //traigo la cantidad de id de preguntas que hay
 $sql="select id_pregunta,pregunta,posicion,parent from preguntas where mostrar='s' order by posicion";
 $preguntas=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql); 
 //calculo estadistica por preguntas
 while(!$preguntas->EOF)
 {
   
  if ($preguntas->fields['parent']=="") //caso pregunta no opcional
  {
   //traemos estadistica de esta pregunta
   $sql="select sum(puntaje)as suma,count(puntaje)as cant from resultados where id_pregunta = ".$preguntas->fields['id_pregunta'];
   $resultado_pregunta=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 
  $tr_color=(((++$j)%2)==0)?$bgcolor1:$bgcolor2;
  ?>
  <tr  bgcolor='<?=$tr_color?>'>
   <td>
   <font size=2> <?=$preguntas->fields['posicion']." ".$preguntas->fields['pregunta']?></font>
   </td>
   <td align="right">
    <font size=2><?=number_format($resultado_pregunta->fields['suma']/$resultado_pregunta->fields['cant'],2,'.',',')?></font>
   </td>
  </tr> 
<?  
  }
  else //caso pregunta opcional
  {
   
   //cantidad de preguntas opcionales
   $cantidad_opcionales = $preguntas->fields['parent'];
   //traigo encuestas que la opcion esta habilitada
   $sql="select id_encuesta from resultados where id_pregunta = ".$preguntas->fields['id_pregunta']." and puntaje=0";
   $resultado_encuesta=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
   
   $encuesta = $resultado_encuesta->fields['id_encuesta'];
   $resultado_encuesta->MoveNext();
   while(!$resultado_encuesta->EOF)
    {$encuesta .= ",".$resultado_encuesta->fields['id_encuesta'];
     $resultado_encuesta->MoveNext();
    }
   $i = 0;
   while($i<$cantidad_opcionales)
   {$preguntas->MoveNext(); //paso a la siguiente pregunta que es opcional
    //traemos estadistica de la pregunta opcional
    if ($encuesta!="")
    {
    $sql="select sum(puntaje)as suma,count(puntaje)as cant from resultados where id_pregunta = ".$preguntas->fields['id_pregunta']." and id_encuesta in($encuesta)";
    $resultado_pregunta=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
    }
    $tr_color=(((++$j)%2)==0)?$bgcolor1:$bgcolor2;
?>

   <tr  bgcolor='<?=$tr_color?>'>
   <td>
   <font size=2> <?=$preguntas->fields['posicion']." ".$preguntas->fields['pregunta']?></font>
   </td>
   <td align="right">
    <font size=2><?=($encuesta!="")?(number_format($resultado_pregunta->fields['suma']/$resultado_pregunta->fields['cant'],2,'.',',')):0;?></font>
   </td>
   </tr> 
<?    
    
    $i++;
   }
  }
  $preguntas->MoveNext();
 }	
}	
?>
</table>
<?=fin_pagina(false);?>