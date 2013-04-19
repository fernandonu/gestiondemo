<?php
/* 
Autor: enrique
Modificado por:
$Author: gabriel $
$Revision: 1.5 $
$Date: 2005/11/08 18:54:44 $
*/

include("../../config.php");
include_once("../../lib/funciones_monitoreo_cfc.php");
//$tipo_p=$_POST["tipo_producto"] or $tipo_p=$parametros["tipo"] or $tipo_p="";

echo $html_header;
if($_POST["monitorear_cfc"]){
	monitorear_cfcs(true);
}
variables_form_busqueda("transporte_codigob");
	


$orden = array(
		"default" => "1",
		"1" => "fecha_certificado",
		"2" => "nombre",
		"3" => "cuit"
		
	);

$filtro = array(
		"nombre" => "Nombre Competidor",
		"direccion" => "Direcion Competidor",
		"tel" => "Telefono Competidor",
		"observaciones" => "Observaciones Competidor",
		"cuit" => "Cuit",
		"fecha_certificado" =>"Fecha Certificado"
		
	);

$query="select * from competidores";
		
//$where="log_codigos_barra.tipo like '%Ingresado%' ";
/*if ($tipo_p != "")
$where="and productos.tipo like '$tipo_p'";*/

?>
<br>
<form name="form1" method="POST" action="listado_comp_cert.php">
<script>

var vent_cb=new Object();
vent_cb.closed=true;
</script>
<center>
<table width="100%">
<tr>
<td align="center">
<?
$link = encode_link("seguimientos_competidores.php",array());
echo "&nbsp;&nbsp;<input type=button name=seguir value='Seguimiento' onclick='window.open(\"$link\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\");'>&nbsp;&nbsp;";
$where="competidor_activo=1";
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
<TD colspan="2" align="left" >Cantidad de Competidores en seguimiento: <?=$total?></TD>
<TD align="right"> <?=$link_pagina?></TD>
</TR>
<TR id="mo">
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"tipo"=>$tipo_p))?>'>Nombre</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"tipo"=>$tipo_p))?>'>Fecha de vencimiento certificado</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"tipo"=>$tipo_p))?>'>Cuit</A></TD>

</TR>
<? while(!$result->EOF){
	//$link = encode_link("transporte_detalle.php",array("code"=>$result->fields["id_transporte"]));
    $puesto_rma=$result->fields["puesto_servicio_tecnico"];
   /* if ($puesto_rma) $color="#FF8080";
                else $color="#B7C7D0";*/
    $id_compet=$result->fields["id_competidor"];
    $fecha=fecha_db(date("d/m/Y",mktime()));
    $fec_cer=$result->fields["fecha_certificado"];
    $link1 = encode_link("lic_competidores.php",array("id_competidor"=>$id_compet));
    $link = encode_link("detalle_competidores.php",array("id_competidor"=>$id_compet));
    if((compara_fechas($fecha,$fec_cer)==1)||($fec_cer==""))
    {
     ?>

	<tr <?=atrib_tr()?>>
    
	<TD bgcolor="Red" onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["nombre"];?></TD>
	<TD bgcolor="Red" align="center" onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=Fecha($result->fields["fecha_certificado"]);?></TD>
	<TD bgcolor="Red" onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["cuit"];?></TD>
	</TR>
	<?
    }
    else 
    {
     ?>

	<tr <?=atrib_tr()?>>
    
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["nombre"];?></TD>
	<TD align="center" onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=fecha($result->fields["fecha_certificado"]);?></TD>
	<TD onclick="window.open('<?=$link?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')"><?=$result->fields["cuit"];?></TD>
	</TR>
	<?
    }
	$result->MoveNext();
	}?>
</TABLE>
<br>
<center><input type="submit" name="monitorear_cfc" value="Verificar Certificados Vencidos"></center>

</FORM>

<?
fin_pagina();
?>
</BODY>