<?php
/*
Author: ferni 
*/
require_once("../../config.php");

if($parametros['accion']!="") $accion=$parametros['accion'];

variables_form_busqueda("listado_muletos");

if ($cmd == "")  $cmd="pendientes";

$orden = array(
        "default" => "2",        
        "1" => "nombre",
        "2" => "apellido",
        "3" => "tel1",
        "4" => "tel2",        
        "5" => "direccion",
        "6" => "dni",
       );
$filtro = array(		
        "apellido" => "Apellido",
        "nombre" => "Nombre",
        "direccion" => "Domicilio",    
        "tel1" => "Telefono 1",    
        "tel2" => "Telefono 2",   
        "dni" => "Documento",             
       );

$datos_barra = array(
     array(
        "descripcion"=> "Pendientes",
        "cmd"        => "pendientes"
     ),
     
     array(
        "descripcion"=> "En Curso",
        "cmd"        => "en_curso",
     ), 
     
     array(
        "descripcion"=> "Historial",
        "cmd"        => "historial",
     ),    
);

generar_barra_nav($datos_barra);

$sql_tmp="select * from encuestas.llamadas_tel ";

if ($cmd=="pendientes")
    $where_tmp=" (estado='p')";

if ($cmd=="en_curso")
    $where_tmp=" (estado='e')";
    
if ($cmd=="historial")
    $where_tmp=" (estado='h')";
    
echo $html_header;
?>
<form name=form1 action="llamadas_listado.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	  </td>
      <td>
        <input type='button' name="nueva_llamada" value='Nueva Llamada' onclick="document.location='llamadas_admin.php'"> &nbsp;&nbsp;
      </td>
     </tr>
</table>

<?$result = sql($sql) or die;
echo "<center><b><font size='2' color='red'>$accion</font></b></center>";
?>
<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  <tr>
  	<td colspan=9 align=left id=ma>
     <table width=100%>
      <tr id=ma>
       <td width=30% align=left><b>Total:</b> <?=$total_muletos?></td>       
       <td width=40% align=right><?=$link_pagina?></td>
      </tr>
    </table>
   </td>
  </tr>
  
  <tr>
  	<td align=right id=mo width="20%"><a id=mo href='<?=encode_link("llamadas_listado.php",array("sort"=>"1","up"=>$up))?>'>Nombre</a></td>
    <td align=right id=mo width="20%"><a id=mo href='<?=encode_link("llamadas_listado.php",array("sort"=>"2","up"=>$up))?>'>Apellido</a></td>
    <td align=right id=mo width="15%"><a id=mo href='<?=encode_link("llamadas_listado.php",array("sort"=>"3","up"=>$up))?>'>Telefono 1</a></td>
    <td align=right id=mo width="15%"><a id=mo href='<?=encode_link("llamadas_listado.php",array("sort"=>"4","up"=>$up))?>'>Telefono 2</a></td>
    <td align=right id=mo width="30%"><a id=mo href='<?=encode_link("llamadas_listado.php",array("sort"=>"5","up"=>$up))?>'>Domicilio</a></td>    
    <td align=right id=mo width="30%"><a id=mo href='<?=encode_link("llamadas_listado.php",array("sort"=>"6","up"=>$up))?>'>Documento</a></td>    
  </tr>
 <?while (!$result->EOF) {    
 	$id_llamadas_tel=$result->fields['id_llamadas_tel']; 	
  	$ref = encode_link("llamadas_admin.php",array("id_llamadas_tel"=>$id_llamadas_tel,"estado"=>$cmd));
    $onclick_elegir="location.href='$ref'";
    ?>
	<tr <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
		<td><b><?=$result->fields['nombre']?></td>
	    <td><b><?=$result->fields['apellido'];?></td>
	    <td><b><?=$result->fields['tel1'];?></td>
	    <td><b><?=$result->fields['tel2'];?></td>	    
	    <td><b><?=$result->fields['direccion'];?></td>	
	    <td><b><?=$result->fields['dni'];?></td>    
   </tr>
   <?$result->MoveNext();
}?>

</table>
<br>
<?=fin_pagina();// aca termino ?>