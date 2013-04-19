<?php
/* MAD
$Author: ferni $
$Revision: 1.4 $
$Date: 2006/01/25 15:29:40 $
*/

include("../../config.php");

$tipo_p=$_POST["tipo_producto"] or $tipo_p=$parametros["tipo"] or $tipo_p="";

echo $html_header;

variables_form_busqueda("transporte_codigob");
	


$orden = array(
		"default" => "1",
		"1" => "nombre_transporte",
		"2" => "direccion_transporte",		
		"3" => "telefono_transporte",
		"4" => "comentarios_transporte"
		
	);

$filtro = array(
		"nombre_transporte" => "Nombre Transporte",
		"direccion_transporte" => "Direcion Transporte",
		"telefono_transporte" => "Telefono Transporte",
		"comentarios_transporte" => "Comentarios Transporte"
		
	);

$query="select id_transporte,comentarios_transporte,telefono_transporte,nombre_transporte,direccion_transporte 
		from transporte ";
		
//$where="log_codigos_barra.tipo like '%Ingresado%' ";
/*if ($tipo_p != "")
$where="and productos.tipo like '$tipo_p'";*/

?>
<br>
<form name="form1" method="POST" action="transporte_listado.php">
<script>

var vent_cb=new Object();
vent_cb.closed=true;
</script>
<center>
<table width="100%">
<tr>
<td align="center">
<?
$link = encode_link("transporte_editor_avanzado.php",array());
echo "&nbsp;&nbsp;<input type=button name=buscar1 value='Editor Avanzado' onclick='window.open(\"$link\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\");'>&nbsp;&nbsp;";

list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");

$result = sql($sql,"error en busqueda") or die("$sql<br>Error en form busqueda");

echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";


?>
</td>
</tr>

<tr>	
	<td>
		&nbsp;&nbsp;<input type=button name=listado value='Listado Transporte' onclick='window.open("transporte_listado_monto_totales.php");'>&nbsp;&nbsp;
	</td>
</tr>

</table>
</CENTER>
<BR>

<?=$parametros["msg"];?>
<TABLE class="bordes" align="center" width="98%" cellspacing="1">
<TR id="ma">
<TD colspan="3" align="left" >Cantidad de transporte: <?=$total?></TD>
<TD colspan="3" align="right"> <?=$link_pagina?></TD>
</TR>
<TR id="mo">
<TD width="8%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"tipo"=>$tipo_p))?>'>Nombre</A></TD>
<TD width="8%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"tipo"=>$tipo_p))?>'>Direccion</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"tipo"=>$tipo_p))?>'>Telefono</A></TD>
<TD width="17%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up,"tipo"=>$tipo_p))?>'>Comentario</A></TD>
</TR>
<? while(!$result->EOF){
	$link = encode_link("transporte_detalle.php",array("code"=>$result->fields["id_transporte"]));
    $puesto_rma=$result->fields["puesto_servicio_tecnico"];
   /* if ($puesto_rma) $color="#FF8080";
                else $color="#B7C7D0";*/

     ?>

	<tr <?=atrib_tr();?> onclick="document.location='<?=$link?>'">
	<TD ><?=$result->fields["nombre_transporte"];?></TD>
	<TD ><?=$result->fields["direccion_transporte"];?></TD>
	<TD ><?=$result->fields["telefono_transporte"];?></TD>
	<TD ><?=$result->fields["comentarios_transporte"];?></TD>
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