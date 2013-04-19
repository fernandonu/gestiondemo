<?php
/*
Author: ferni 
*/
require_once("../../config.php");

if($parametros['accion']!="") $accion=$parametros['accion'];

variables_form_busqueda("listado_muletos");

$fecha_hoy=date("Y-m-d H:i:s");
$fecha_hoy=fecha($fecha_hoy);

if ($cmd == "")  $cmd="en_curso";

$orden = array(
        "default" => "1",        
        "1" => "id_adelantos_clientes",
        "2" => "id_entidad",
        "3" => "monto",        
        "4" => "comentario",        
       );
$filtro = array(
		"monto" => "Monto",        
       );


$datos_barra = array(
     array(
        "descripcion"=> "En Curso",
        "cmd"        => "en_curso"
     ),     
     array(
        "descripcion"=> "Historial",
        "cmd"        => "historial",
     ),      
);
generar_barra_nav($datos_barra);

$sql_tmp=" select id_adelantos_clientes,id_entidad,monto,comentario,estado,nombre
 			from bancos.adelantos_clientes
 			left join licitaciones.entidad using (id_entidad)";

if ($cmd=="en_curso")
    $where_tmp=" (adelantos_clientes.estado=1)";
    
if ($cmd=="historial")
    $where_tmp=" (adelantos_clientes.estado=2)";

echo $html_header;
?>

<form name=form1 action="ad_cliente_lis.php" method=POST>

<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
		<?list($sql,$total_muletos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");?>
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>
	  </td>
      <td>      	
        <input type='button' name="nuevo_adelanto" value='Nuevo Adelanto' onclick="document.location='ad_cliente_admin.php'"> &nbsp;&nbsp;
      </td>
     </tr>
</table>

<?$result = sql($sql,'error groso');
echo "<center><b><font size='2' color='red'>$accion</font></b></center>";
?>

<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  <tr>
  	<td colspan=5 align=left id=ma>
     <table width=100%>
      <tr id=ma>
       <td width=30% align=left><b>Total:</b> <?=$total_muletos?></td>       
       <td width=40% align=right><?=$link_pagina?></td>
      </tr>
    </table>
   </td>
  </tr>
  <tr>
  	<td align=right id=mo><a id=mo href='<?=encode_link("ad_cliente_lis.php",array("sort"=>"1","up"=>$up))?>'>ID</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("ad_cliente_lis.php",array("sort"=>"2","up"=>$up))?>'>Cliente</a></td>
    <td align=right id=mo><a id=mo href='<?=encode_link("ad_cliente_lis.php",array("sort"=>"3","up"=>$up))?>'>Monto</a></td>    
    <td align=right id=mo><a id=mo href='<?=encode_link("ad_cliente_lis.php",array("sort"=>"4","up"=>$up))?>'>Comentario</a></td>    
  </tr>
 <?while (!$result->EOF) {  	
  	$id_adelantos_clientes=$result->fields['id_adelantos_clientes'];
  	$estado=$result->fields['estado'];
  	
  	$ref = encode_link("ad_cliente_admin.php",array("id_adelantos_clientes"=>$id_adelantos_clientes));
    $onclick_elegir="location.href='$ref'";    
    ?>
	<tr <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
		<td><b><?=$result->fields['id_adelantos_clientes']?></td>	    
	    <td><b><?=$result->fields['nombre'];?></td>
	    <td><b><?=number_format($result->fields['monto'],2,',','.');?></td>
	    <td><b><?=$result->fields['comentario'];?></td>	    
   </tr>
   <?$result->MoveNext();
    }?>
</table>
<br>
<?=fin_pagina();// aca termino ?>