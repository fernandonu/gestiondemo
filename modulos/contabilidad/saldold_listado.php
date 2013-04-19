<?php
require_once("../../config.php");

if($parametros['accion']!="") $accion=$parametros['accion'];

variables_form_busqueda("listado_muletos");

$orden = array(
        "default" => "1",
        "1" => "fecha",
        "2" => "periodo",
        "3" => "monto",
       );
$filtro = array(
        "fecha" => "Fecha",
        "periodo" => "Periodo",
        "monto" => "Monto",
       );

/*
$datos_barra = array(
     array(
        "descripcion"=> "Disponibles",
        "cmd"        => "disponibles"
     ),
     array(
        "descripcion"=> "En Uso",
        "cmd"        => "en_uso"
     ),
);
generar_barra_nav($datos_barra);*/

$sql_tmp=" select * from contabilidad.saldold ";

/*
if ($cmd=="disponibles")
    $where_tmp=" (muletos.id_estado_muleto=1)";
    
if ($cmd=="en_uso")
    $where_tmp=" (muletos.id_estado_muleto=2 or muletos.id_estado_muleto=6)";
    
*/
echo $html_header;

echo "<center><b><font size='3' color='red'>$accion</font></b></center>";
?>

<form name=form1 action="saldold_listado.php" method=POST>

<table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=100% align=center>
  <tr>
   <td align=center>
    <table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=100% align=center>
     <tr>
       <td align=center>
<?list($sql,$total,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
       </td>
       <td><input type=submit name="buscar" value='Buscar'></td>
       <td><input type='button' name="nuevo_saldold" value='Nuevo Saldo LD' onclick="document.location='saldold_admin.php'"></td>
   	 </tr>
   	</table>
   </td>
  </tr>
</table>
<?$result = sql($sql) or die;?>
<table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=<?=$bgcolor3?> align=center>
  <tr>
  	<td colspan=9 align=left id=ma>
    	<table width=100%>
      		<tr id=ma>
       			<td width=30% align=left><b>Total:</b> <?=$total?></td>
       			<td width=70% align=right><?=$link_pagina?></td>
      		</tr>
    	</table>
  	</td>
  </tr>
  
  <tr>
    <td align=right id=mo><a id=mo href='<?=encode_link("saldold_listado.php",array("sort"=>"1","up"=>$up))?>' title="Nº de Muleto">Fecha</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("saldold_listado.php",array("sort"=>"2","up"=>$up))?>'>Periodo DJ</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("saldold_listado.php",array("sort"=>"3","up"=>$up))?>'>Monto</a></td>
  </tr>
<?
  while (!$result->EOF) {
  	$id_saldold=$result->fields['id_saldold'];
  	
  	$ref = encode_link("saldold_admin.php",array("id_saldold"=>$id_saldold));
    $onclick_elegir="location.href='$ref'";		

?>
    <tr <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
	 <td align="center"><b><?=fecha($result->fields['fecha']);?></td>
     <td><b><?=$result->fields['periodo']?></td>
     <td><b><?=formato_money($result->fields['monto']);?></td>
    </tr>
<?  $result->MoveNext();
    }
?>
</table>
<?=fin_pagina();// aca termino ?>
