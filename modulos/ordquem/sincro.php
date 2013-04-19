<?PHP
/*AUTOR: MAD
               1 julio 2004
$Author: marcelo $
$Revision: 1.8 $
$Date: 2004/09/21 20:23:07 $
*/
/*
Este script sirve para seleccionar los ensambladores con proceso de quemado 
y proceder a abrir una ventana por cada uno para comenzar la sincronización.
Permite agregar ensambladores con proceso de quemado.
*/
require_once("../../config.php");






echo $html_header;


	$sql_data = "Select ensamblador_quemado.*,ensamblador.nombre from ensamblador_quemado join ensamblador using(id_ensamblador) where activo = 1";
	$result_data = $db->Execute($sql_data) or die($db->ErrorMsg()."<br>$sql_data");

	?>

	<FORM id="f_sinc" method="POST" action="sincro.php">
	<TABLE width="90%" align="center" class="bordes" id="mo">
	
	<TR align="center">
		<TD id="mo" colspan="3">
		Lista de servidores activos para sincronizar.
		</TD>
	</TR>
	<TR align="center" id="ma">
		<TD width="50%">
		<FONT color="Black">Ensamblador</font>
		</TD>
		<TD width="50%">
		<FONT color="Black">Localización del ensamblador</font>
		</TD>
	</TR>
	
	<?
	$i = 0;
	while(!$result_data->EOF)
	{
	?>

	<TR align="center" id="ma">
		<TD>
		<?=$result_data->fields['nombre']?>
		</TD>
		<TD>
		<?=$result_data->fields['http']?>
		</TD>
	    <INPUT type="hidden" name="entrada_<?=$i?>" value="<?=$result_data->fields['id_entrada']?>">
	</TR>
	<?
	$result_data->MoveNext();
	$i++;
	}
	?>	
	<TR align="center" id="mo">
		<TD colspan="3" align="center"> 
		<INPUT type="button" name="sinc" value="Sincronizar" onclick="Sincroniz(<?=$result_data->RecordCount()?>)">
		<INPUT type="button" name="cerrar" value="Cerrar" onclick="window.close();">
		</TD>
	</TR>
	</TABLE>
	</FORM>
	<?
//}
?>

<script>
var cascada=50;
function Sincroniz(valor)
{
	while(valor > 0) {
			window.open('sincro_prueba.php?id='+eval('document.all.entrada_'+(valor-1)+'.value'),'','left='+cascada+',top='+cascada+',width=400,height=250,resizable=1,status=1,scrollbars=1');		
   			cascada+=10;
		valor--;
	}
	window.document.all.sinc.disabled=1;
}
</script>
