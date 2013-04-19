<?php
/* 
$Author: ferni $
$Revision: 1.4 $
$Date: 2006/03/14 21:58:05 $
*/

include("../../config.php");

echo $html_header;

variables_form_busqueda("ultimos_cheques");

$orden = array(
		"default_up" => "0",
		"default" => "1",
		"1" => "fecha",
	);

$filtro = array(
		"log_debitos.comentario" => "Comentario",
	);

$query="select *
		FROM bancos.débitos
		left JOIN bancos.log_debitos using (iddébito)
		left join bancos.tipo_débito using (idtipodéb)
		left join bancos.tipo_banco using (idbanco)
		left join general.tipo_cuenta using (numero_cuenta)";
$where=" tipo_log=1";
?>
<br>
<form name="form1" method="POST" action="ultimos_debitos.php">
<center>
<table width="100%">
<tr>
<td align="center">
<?
list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");
$result = sql($sql,"error en busqueda") or die("$sql<br>Error en form busqueda");
echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
?>
</td>
</tr>
</table>
</CENTER>
<BR>
<TABLE class="bordes" align="center" width="100%" cellspacing="1">
	<TR id="ma">
		<TD align="left" colspan="7" >Cantidad de Débitos: Ultimos 50</TD>
	</TR>
	<TR id="mo">
		<TD width="18%">Fecha de Carga</TD>
		<TD width="15%">Usuario de Carga</TD>
		<TD width="15%">Tipo del Débito</TD>
		<TD width="15%">Tipo de Cuenta</TD>
		<TD width="10%">Importe del Débito</TD>
		<TD width="10%">Nombre del Banco</TD>
		<TD width="17%">Comentario</TD>
	</TR>
<? while(!$result->EOF){?>
	<tr id=ma>
		<TD align="center"><?=date("j/m/Y H:i:s",strtotime($result->fields['fecha']))?></TD>
		<TD align="left"><?=$result->fields["user_login"];?></TD>
		<TD align="left"><?=$result->fields["tipodébito"];?></TD>
		<TD align="left"><?echo $result->fields["concepto"] . "-" .$result->fields["plan"];?></TD>
		<TD align="center"><?echo "$ ".number_format($result->fields["importedéb"],'2',',','.');?></TD>
		<TD align="left"><?=$result->fields["nombrebanco"];?></TD>
		<TD align="left"><?=$result->fields["comentario"];?></TD>
	</TR>
	<?$result->MoveNext();
	}?>
</TABLE>
</FORM>
<?fin_pagina();?>
</BODY>