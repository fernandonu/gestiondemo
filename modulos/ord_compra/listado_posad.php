<?
/*
Autor: MAC 
Fecha: 12/08/05

MODIFICADO POR
$Author: ferni $
$Revision: 1.5 $
$Date: 2006/03/03 18:57:03 $
*/
include("../../config.php");

variables_form_busqueda("listado_posad");

$orden = array(
        "default" => "1",
        "1" => "descripcion",
        "2" => "codigo_ncm",
        "3" => "derechos",
        "4" => "estadistica",
        "5" => "iva_ganancias",
       );
$filtro = array(
		"descripcion" => "Descripción",
        "codigo_ncm" => "Código NCM",
        "derechos" => "Derechos",
        "estadistica" => "Estadística",
        "iva_ganancias" => "Iva Ganancias",
       );
       


//traemos los datos de la tabla posad
$sql_tmp="select * from posad ";
//$where_tmp=" estado_posad <> 0";

$index=$parametros["indice_select"] or $index=$_GET["indice_select"];
echo $html_header;
if($parametros['accion']!="") $accion=$parametros['accion'];
echo "<center><b><font size='3' color='red'>$accion</font></b></center>";

?>
<form name=form1 action="listado_posad.php" method=POST>
<title>Seleccionar la Posición Aduanera para la fila <?=$index + 1?></title>
<table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=100% align=center>
  <tr>
   <td align=center>
    <table cellspacing=2 cellpadding=5 border=0 bgcolor=<?=$bgcolor3?> width=100% align=center>
     <tr>
       <td align=center>
<?list($sql,$total,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
       </td>
       <td><input type=submit name="buscar" value='Buscar'></td>
       <td>
       	<?if ($index==""){?>
      		<input type='button' name="nuevo_posad" value='Nuevo Posad' onclick="document.location='posad_admin.php'"></td>
      	<?}?>
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
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_posad.php",array("sort"=>"1","up"=>$up))?>'>Descripción</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_posad.php",array("sort"=>"2","up"=>$up))?>'>Código NCM</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_posad.php",array("sort"=>"3","up"=>$up))?>'>Derechos</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_posad.php",array("sort"=>"4","up"=>$up))?>'>Estadística</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("listado_posad.php",array("sort"=>"5","up"=>$up))?>'>I.V.A./Ganancias</a></td>
  </tr>
<?
  while (!$result->EOF) {
  	if ($index==""){
  	$ref = encode_link("posad_admin.php",array("id_posad"=>$result->fields["id_posad"],"pagina"=>"listado_posad.php"));
    $onclick_elegir="location.href='$ref'";?>		
    
    <tr <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
  	
  <?}
  else{?>
  	<tr style="cursor:hand"  bgcolor="<?=$bgcolor_out?>" onclick="window.opener.document.all.select_posad_<?=$index?>.value='<?=$result->fields["id_posad"]?>';window.opener.set_montos_fila_oc_internacional();window.close();">
  <?}?>
  
	 <td>
    <input type="hidden" name="id_posad" value="<?=$result->fields["id_posad"]?>">
    <?=$result->fields["descripcion"]?>
   </td>
   <td align="center">
    <?=$result->fields["codigo_ncm"]?>
   </td>
   <td align="center">
    <?
    $derechos=$result->fields["derechos"]*100;
    echo $derechos." %";  
    ?>
   </td>
   <td align="center">
    <?
    $estadistica=$result->fields["estadistica"]*100;
    echo $estadistica." %";  
    ?>
   </td>
   <td align="center">
    <?
    $iva_ganancias=$result->fields["iva_ganancias"]*100;
    echo $iva_ganancias." %";  
    ?>
   </td>
    </tr>
<?  $result->MoveNext();
    }
?>
</table>
<?fin_pagina();?>