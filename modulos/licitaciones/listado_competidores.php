<?php
/* 
$Author: enrique $
$Revision: 1.5 $
$Date: 2005/11/01 16:34:58 $
*/

include("../../config.php");

$tipo_p=$_POST["tipo_producto"] or $tipo_p=$parametros["tipo"] or $tipo_p="";

echo $html_header;

variables_form_busqueda("transporte_codigob");
	


$orden = array(
		"default" => "1",
		"1" => "nombre",
		"2" => "direccion",		
		"3" => "tel",
		"4" => "observaciones",
		"4" => "cuit"
		
	);

$filtro = array(
		"nombre" => "Nombre Competidor",
		"direccion" => "Direcion Competidor",
		"tel" => "Telefono Competidor",
		"observaciones" => "Observaciones Competidor",
		"cuit" => "Cuit"
		
	);

$query="select * from competidores";
		
//$where="log_codigos_barra.tipo like '%Ingresado%' ";
/*if ($tipo_p != "")
$where="and productos.tipo like '$tipo_p'";*/

?>
<br>
<form name="form1" method="POST" action="listado_competidores.php">
<script>

var vent_cb=new Object();
vent_cb.closed=true;
</script>
<center>
<table width="100%">
<tr>
<td align="center">
<?
$link = encode_link("lic_nuevo_comp.php",array());
echo "&nbsp;&nbsp;<input type=button name=nuevo value='Nuevo' onclick='window.open(\"$link\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\");'>&nbsp;&nbsp;";

list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");

$result = sql($sql,"error en busqueda") or fin_pagina();

echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";


?>
</td>
</tr>
</table>
</CENTER>
<BR>

<?=$parametros["msg"];?>
<TABLE class="bordes" align="center" width="98%" cellspacing="1">
<TR id="ma">
<TD colspan="3" align="left" >Cantidad de Competidores: <?=$total?></TD>
<TD colspan="3" align="right"> <?=$link_pagina?></TD>
</TR>
<TR id="mo">
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"tipo"=>$tipo_p))?>'>Nombre</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"tipo"=>$tipo_p))?>'>Direccion</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"tipo"=>$tipo_p))?>'>Telefono</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"tipo"=>$tipo_p))?>'>Observaciones</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"tipo"=>$tipo_p))?>'>Cuit</A></TD>
<TD width="1%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"tipo"=>$tipo_p))?>'>&nbsp;</A></TD>
</TR>
<? while(!$result->EOF){
	//$link = encode_link("transporte_detalle.php",array("code"=>$result->fields["id_transporte"]));
    $puesto_rma=$result->fields["puesto_servicio_tecnico"];
   /* if ($puesto_rma) $color="#FF8080";
                else $color="#B7C7D0";*/
    $id_compet=$result->fields["id_competidor"];
    $link1 = encode_link("lic_competidores.php",array("id_competidor"=>$id_compet));
    $link = encode_link("lic_nuevo_comp.php",array("id_competidor"=>$id_compet));
     ?>

	<tr <?=atrib_tr()?>>
    
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["nombre"];?></TD>
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["direccion"];?></TD>
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["tel"];?></TD>
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["observaciones"];?></TD>
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["cuit"];?></TD>

	<TD ><? echo "<input type=button name=a value='A' onclick='window.open(\"$link1\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\")'>";?></TD>
	</TR>
	<?
	$result->MoveNext();
	}?>
</TABLE>


</FORM>

<?
fin_pagina();
?>
</BODY>