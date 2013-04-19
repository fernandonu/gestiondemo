<?
/*AUTOR: MAD
               1 julio 2004
$Author: marcelo $
$Revision: 1.2 $
$Date: 2004/07/14 19:28:24 $
*/

require_once("../../config.php");

echo $html_header;
$itemspp = 8; //cantidad del form busqueda
variables_form_busqueda("logs"); ?>
<form name="form1" method="POST" action="logs.php">
<?
$orden = array(
		"default" => "1",
		"1" => "logs_quemado.nro_orden",
		"2" => "logs_quemado.fecha",
		"3" => "logs_quemado.usuario"
	);

$filtro = array(
		"logs_quemado.nro_orden" => "Número Orden",
		"logs_quemado.fecha" => "Fecha del evento",
		"logs_quemado.usuario" => "Usuario"
	);
	
$query ="select * from logs_quemado";

echo "<center>";

list($sql,$total_logs,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar"); 

$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error en form busqueda");

?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>

<BR>

<TABLE width="95%" align="center" class="bordes" id="mo"> 
<TR id="mo">
	<TD width="10%"> <A href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>
	<B>OP</B></A>
	</TD>
	<TD width="10%"> <A href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>
	<B>Fecha</B></A>
	</TD>
	<TD width="20%"> <A href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>
	<B>Usuario</B></A>
	</TD>
	<TD width="60%">
	<B>Tipo</B>
	</TD>
</TR>

<? while (!$result->EOF){ ?>
<TR id="ma">
	<TD width="10%">
	<?=$result->fields['nro_orden']?>
	</TD>
	<TD width="20%">
	<?=$result->fields['fecha']?>
	</TD>
	<TD width="20%">
	<?=$result->fields['usuario']?>
	</TD>
	<TD width="50%">
	<?=$result->fields['tipo']?>
	</TD>
</TR>
<? $result->MoveNext(); } ?>
<TR id="mo">
	<TD colspan="2" align="left">
	<B>Total de Logs: <?=$total_logs?></B>
	</TD>
	<TD colspan="2" align="right">
	<?=$link_pagina?>	
	</TD>
</TR>
<TR>
	<TD colspan="4" align="left">
	<INPUT type="button" value="Cerrar" onclick="window.close()">
	</TD>
</TR>

</TABLE>
