<?
/*
Author: Cesar

MODIFICADA POR
$Author: elizabeth $
$Revision: 1.4 $
$Date: 2004/08/18 22:58:05 $
*/

require_once("../../config.php");

if ($_POST["nuevo"]=="Nuevo"){

        $link=encode_link("remito_int_asociar.php",array());
        header("Location:$link");
}


if ($parametros["cmd"] == "detalle") {
	include("remito_int_nuevo.php");
	exit;
}

echo $html_header;

//$informar="";

define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs

variables_form_busqueda("rem_lis");
if ($cmd == "") {
	$cmd="todos";
	$_ses_rem_lis["cmd"] = $cmd;
	phpss_svars_set("_ses_rem_lis", $_ses_rem_lis);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes"
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						),
					array(
						"descripcion"	=> "Todos",
						"cmd"			=> "todos"
						)
				 );

$filtro = array(
			"remito_interno.id_remito" => "Nº Remito",
			"fecha_remito" => "Fecha Remito",
			"remito_interno.direccion" => "Dirección",
			"remito_interno.cliente" => "Cliente - Nombre",
			"remito_interno.id_licitacion" => "ID Licitacion"
		);

$orden = array(
			"default_up" => "0",
			"default" => "1",
			"1" => "remito_interno.id_remito",
			"2" => "remito_interno.cliente",
			"3" => "usuario",
			"4" => "remito_interno.id_licitacion"
		);

$sql_tmp = "SELECT remito_interno.*,log_remito_interno.usuario,log_remito_interno.fecha ";
$sql_tmp .= "FROM remito_interno ";
$sql_tmp .= "JOIN log_remito_interno ON log_remito_interno.id_remito=remito_interno.id_remito and log_remito_interno.tipo_log='creacion'";

switch ($cmd) {

	case 'pendientes':
					$where_tmp = "remito_interno.estado='p'";
					break;
	case 'historial':
					$where_tmp = "remito_interno.estado='h'";
					break;
	default:
                                        $where_tmp = "";
                                        break;

}



?>
<form name="form" method="post" action="remito_int_listar.php">
<br>

<? generar_barra_nav($datos_barra); ?>
<br>
<table width="100%">
<tr>
<td>
<table align="center" class="bordes" cellpaddindg=2 cellspacing=2 bgcolor=<?=$bgcolor3?>>
<tr>
<td>
<?
list($sql,$total_remitos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,"",$where_tmp,"buscar");
$remitos = sql($sql) or die;
if(($remitos->RecordCount()==0)&&($_POST["boton"]=="Buscar")) {
	$informar="<center>No se encontró ningun remito que concuerde con lo buscado</center>";
}
?>
&nbsp;&nbsp;&nbsp;<input type="submit" name="boton" value="Buscar">
</td>
</tr>
</table>
</td>
<td><input name="nuevo" type="submit" value="Nuevo" title="Crea un nuevo Remito Interno"></td>
<tr>
</table>
<br>
<?="<b>$informar</b>"?>
    <table class="bordes" cellspacing="2" cellpadding="3" width="95%" align="center">
	<tr><td colspan="<? echo ($cmd=="todos")?"6":"5"; ?>" id="ma">
	<table width="100%" border="0">
	 <tr id="ma">
      <td align="left">Total de Remitos <? if ($cmd) echo ": ".$total_remitos; ?></td>
      <td align="right"><? echo $link_pagina; ?></td>
	 </tr>
	</table>
	</td></tr>
      <tr align="center" id="mo">
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up)) ?>'>
      <td style="cursor:hand;" title="Ordenar por Nº de Remito" width="106">Nº Remito</td>
</a>
	<?
		if($cmd=="todos") {
			echo "<td width='106'>Estado</td>";
		}
	?>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por id licitacion"  >ID LIC</td>
</a>	
	
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por nombre de Cliente"  >Cliente</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por usuario de creacion" width="128">Creado por</td>
</a>
	<?
		if ($cmd=="todos"||$cmd=="historial") {
			echo "<td>&nbsp;</td>";
 		}
	?>
</tr>
<?
	while (!$remitos->EOF ) {
		$ref = encode_link("remito_int_nuevo.php",array("remito"=>$remitos->fields['id_remito'] ));
		tr_tag($ref);
        echo "<td align='center'>".$remitos->fields['id_remito']."</td>";
        if($cmd=="todos") {
			echo "<td align='center'>";
			switch ($remitos->fields['estado']) {

        		case 'h':
			  	case 'H': echo "Historial";     break;
        		case 'p':
			  	case 'P': echo "Pendiente";	break;

			}
			echo "</td>";
        }
        echo "<td align='center'>".$remitos->fields['id_licitacion']."</td>";
        echo "<td align='left'>".$remitos->fields['cliente']."</td>";
        echo "<td align='center'>".$remitos->fields['usuario']."</td>";
		if ($cmd=="todos"||$cmd=="historial") {
			echo "<td align='center' width='3%'>";
			switch ($remitos->fields['estado']) {
				case 'h':

//			  	echo "<A href='".encode_link(OUTPUT."/Remito".$remitos->fields['id_remito'].".pdf",array())."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'></a>";
                $link=encode_link("remito_interno_pdf.php", array("id_remito"=>$remitos->fields['id_remito']));	
		        echo "<A target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
//              echo "<A href='".OUTPUT."/Remito".$remitos->fields['id_remito'].".pdf"."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
			}
			echo "</td></tr>";
		}
  		$remitos->MoveNext();
	}
?>
    </table>
</form>
<br><? echo "Página generada en ".tiempo_de_carga()." segundos.<br>"; ?>
</body>
</html>