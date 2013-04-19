<?
/*

$Author: ferni $
$Revision: 1.6 $
$Date: 2005/08/18 15:28:47 $

*/

require_once("../../config.php");

function transformar($valor1,$valor2)
{$diferencia=abs(strlen($valor1)-strlen($valor2));
 $division=($diferencia/2);
 $i=0;
 $cadena="";
 while ($i<$diferencia-1)
 {$cadena.="&nbsp";
  $i++;
 }
 $i=0;
 $cadena.=$valor2;
 while ($i<$diferencia)
 {$cadena.="&nbsp";
  $i++;
 }
 return $cadena;
}
?>
<html>
<head>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<title>Buscar Resultados de Licitaciones</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php
include("../ayuda/ayudas.php");

extract($_POST,EXTR_SKIP);
if ($parametros)
 extract($parametros,EXTR_OVERWRITE);
?>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
</script>
</head>
<body bgcolor=#E0E0E0 text="#000000" topmargin="0" >
<br>
<table width="100%">
<tr>
<td align="left">
<table cellpadding="4">
<tr>
<td>
<a href='<?php echo encode_link("lic_ver_res.php",array("keyword"=>$keyword,"pag_ant"=>"lic","pagina_volver"=>$pagina_volver)); ?>' onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">Vista 1</a>
</td>
<td>
<a href='<?php echo encode_link("lic_ver_res2.php",array("keyword"=>$keyword,"pag_ant"=>$pagina_volver,"pagina_volver"=>$pagina_volver)); ?>' onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">Ver Monto Unitario</a>
</td>
</tr>
</table>
</td>
<td align="right">
<table cellpadding="4">
<tr>
<td>
<p onclick="window.location='<?=$pagina_volver?>'" style="cursor:hand;" onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')"><font color="Blue">Volver</p>
</td>
<td>

</td>
</tr>
</table>
</td>
</tr>
</table>
<?php 
$link=encode_link("lic_ver_res.php",array('keyword'=>$keyword,'pagina'=>"cargar_resultados","pag_ant"=>$pag_ant,"pagina_volver"=>$pagina_volver)); 
?>
<form name="formulario" method="post" action="<?=$link?>" >
 <table cellspacing=0 border=1 bordercolor=#000000>
  <?php $parametros=array('ID'=>$keyword,'cmd1'=>'detalle',"pagina"=>$parametros["pag_ant"]); ?>
  <tr title="Haga click para ver el detalle de la licitacion">    
  <td id=mo colspan="20" align="left" bordercolor='#000000'>
<?php
$sql="select entidad.nombre as nombre_ent,licitacion.id_licitacion,distrito.nombre  from (licitacion join entidad on entidad.id_entidad=licitacion.id_entidad) join distrito on distrito.id_distrito=entidad.id_distrito  where id_licitacion=$keyword";
$resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
?>
      <a href="<?=encode_link($pagina_volver,$parametros) ?>"> 
      &nbsp;Resultados Licitacion N&ordm; &nbsp;<?=$resultado->fields['id_licitacion'] ?>
      &nbsp; - &nbsp; Entidad: &nbsp;<?=$resultado->fields['nombre_ent'] ?>
      &nbsp;&nbsp; - &nbsp; Disrito: &nbsp;<?=$resultado->fields['nombre'] ?>
      </a>
      </td>
    </tr>
   <tr id='ma'>
   <td width="2%">Renglon</td>
   <td width="2%">Cantidad</td>
<?php 
 $sql="select distinct competidores.nombre,competidores.id_competidor from renglon join oferta on renglon.id_licitacion=$keyword and renglon.id_renglon=oferta.id_renglon join competidores on oferta.id_competidor=competidores.id_competidor";
 $resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
 while (!$resultado->EOF)
 {
?>
 <td align="center" width="10%"> 
 <?php echo $resultado->fields['nombre']; ?>
 </td>
<?php
 $resultado->MoveNext();
 }
?>
</tr>
<?php
$sql="select * from renglon where id_licitacion=$keyword order by codigo_renglon asc";
$resultado_ren=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
 while (!$resultado_ren->EOF)
 {
?>
 <tr class="td" onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">
 <td align="center" title='<?php echo $resultado_ren->fields['titulo']; ?>'>
 <b> 
 <?php echo $resultado_ren->fields['codigo_renglon']; ?>
 </td>
 <td align="center">
 <b>
 <?php echo $resultado_ren->fields['cantidad']; ?>
 </td>
<?php
$resultado->Move(0);
while (!$resultado->EOF)
{$sql="select oferta.*,moneda.simbolo from (oferta join moneda on moneda.id_moneda=oferta.id_moneda) where id_renglon=".$resultado_ren->fields['id_renglon']." and id_competidor=".$resultado->fields['id_competidor'];
 $resultado_aux=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
 $resultado_aux=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
 $parametros['id_moneda']=$resultado_aux->fields['id_moneda'];
 $parametros['id_lic']=$keyword;
 $parametros['id_renglon']=$resultado_ren->fields['id_renglon'];
 $parametros['id_competidor']=$resultado->fields['id_competidor'];
 $parametros['pagina_viene']="lic_ver_res";
 $parametros['pagina_volver']=$pagina_volver;
 $link=encode_link("lic_cargar_res.php",$parametros);
if ($resultado_aux->fields['simbolo'])
{
?>
<a href="<? echo $link; ?>" >
<?php
}
?>
 <td align="center" title='<?php echo $resultado_aux->fields['observaciones']; ?>'>
 <b>
<?php
 if ($resultado_aux->fields['ganada']=='t')
 {
?>
 <font color="Red">
<?php
 }
?>
 <?php echo $resultado_aux->fields['simbolo'].'&nbsp;'.(($resultado_aux->fields['monto_unitario'])?number_format($resultado_ren->fields['cantidad']*$resultado_aux->fields['monto_unitario'],2,".",""):''); ?>
 </td>
 </a>
<?php
$resultado->MoveNext();
}
?>
 </tr>
<?php
 $resultado_ren->MoveNext();
 }
?>
</table>
</form>
</body>
</html>