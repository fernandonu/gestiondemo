<?php
/* 
$Author: fernando $
$Revision: 1.7 $
$Date: 2006/08/15 21:58:03 $
*/

include("../../config.php");

echo $html_header;

variables_form_busqueda("ultimos_cheques");

$orden = array(
		"default_up" => "0",
		"default" => "1",
		"1" => "fechaemich"
	);

$filtro = array(
		"cheques.númeroch" => "Número de Cheque",
		"nombrebanco" => "Nombre del Banco"
	);

		
$query="select fechaemich, cheques.númeroch,cheques.idbanco, nombrebanco, importech, comentarios,proveedor.razon_social,
        case when ordenes_pagos.númeroch isnull then 'no' else 'si' end as orden_de_pago
		FROM bancos.cheques 
		left JOIN bancos.tipo_banco using (idbanco)
		left join general.proveedor on idProv=id_proveedor
        left join (
                   select distinct númeroch,idbanco from 
                          compras.ordenes_pagos 
		           left join compras.pago_orden using (id_pago)
		           left join compras.orden_de_compra using (nro_orden)
                   where not númeroch isnull and ord_pago='si'
                ) as ordenes_pagos on (cheques.númeroch=ordenes_pagos.númeroch and cheques.idbanco=ordenes_pagos.idbanco)
        		";
?>
<br>
<form name="form1" method="POST" action="ultimos_cheques.php">
<center>
<table width="100%">
<tr>
<td align="center">
<?
$link = encode_link("transporte_editor_avanzado.php",array());

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
<TD align="left" colspan="6" >Cantidad de Cheques: Ultimos 50</TD>
</TR>
<TR id="mo">
<TD width="10%">Fecha de Emisión</TD>
<TD width="10%">Número de Cheque</TD>
<TD width="25%">Banco</TD>
<TD width="10%">Monto</TD>
<TD width="25%">Comentario</TD>
<TD width="20%">Proveedor</TD>
</TR>
<? while(!$result->EOF){
	if (($result->fields["orden_de_pago"]=="si"))
		$color_fondo="FFCC99";
	else 
		$color_fondo="";  	
	?>
	<tr id="ma" >
		<TD bgcolor="<?=$color_fondo?>" align="center"><font size="2"><?echo Fecha($result->fields["fechaemich"]) . " " .Hora($result->fields["fechaemich"])?></font></TD>
		<TD bgcolor="<?=$color_fondo?>" align="center"><font size="2"><?=$result->fields["númeroch"];?></font></TD>
		<TD bgcolor="<?=$color_fondo?>" align="left"><font size="2"><?=$result->fields["nombrebanco"];?></font></TD>
		<TD bgcolor="<?=$color_fondo?>" align="left"><font size="2"><?echo "$ ".number_format($result->fields["importech"],'2',',','.');?></font></TD>
		<TD bgcolor="<?=$color_fondo?>" align="left"><font size="2"><?=$result->fields["comentarios"];?></font></TD>
		<TD bgcolor="<?=$color_fondo?>" align="left"><font size="2"><?=$result->fields["razon_social"];?></font></TD>
	</TR>
	<?
	$result->MoveNext();
	}?>
</TABLE>

<br>
	<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
     <tr>
      <td colspan=10 bordercolor='#FFFFFF'><b>Colores de Referencia para toda la fila:</b></td>
     <tr>
     <td width=30% bordercolor='#FFFFFF'>
      <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
       <tr>
        <td width=15 bgcolor='FFCC99' bordercolor='#000000' height=15>&nbsp;</td>
        <td bordercolor='#FFFFFF'>Cheque asociado a una orden de pago</td>
       </tr>
      </table>
     </td>
    </table>

</FORM>

<?
fin_pagina();
?>
</BODY>