<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.9 $
$Date: 2004/11/04 15:08:53 $
*/
require_once("../../config.php");

$meses[1]="Enero";
$meses[2]="Febrero";
$meses[3]="Marzo";
$meses[4]="Abril";
$meses[5]="Mayo";
$meses[6]="Junio";
$meses[7]="Julio";
$meses[8]="Agosto";
$meses[9]="Setiembre";
$meses[10]="Octubre";
$meses[11]="Noviembre";
$meses[12]="Diciembre";

function make_select_mes($selected)
{
	global $meses;
	for ($i=1; $i <= 12 ; $i++)
     echo "<option value=".(($i<10)?"0$i":$i) .(($i==$selected)?' selected>':'>')."$meses[$i]</option>";
	
}

function make_select_anio($selected)
{
	$i=date('Y')-10; //diez anios antes
	$j=$i+20; //diez anios despues
	$i=$i-1;
	while (++$i <= $j)
     echo "<option ".(($i==$selected)?'selected>':'>')."$i</option>";
	
}

$q="select fact_prov.*, proveedor.razon_social from fact_prov join proveedor using (id_proveedor) ";

$mes=$_POST['select_mes'] or $mes=$parametros['mes'] or $mes=date('m');
$anio=$_POST['select_anio'] or $anio=$parametros['anio'] or $anio=date('Y');
$download=$parametros['download'];

$where=" fecha_emision ilike '$anio-$mes-%' order by fecha_emision asc";

$q.="where $where";

$datos=sql($q) or fin_pagina();

//envio la pagina como archivo excel
if($download)
{
	if (isset($_SERVER["HTTPS"])) {
		 /**
		  * We need to set the following headers to make downloads work using IE in HTTPS mode.
		  */
		header("Pragma: ");
		header("Cache-Control: ");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
	}

	header("Content-Type: application/xls");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=\"Libro_iva_".$meses[intval($mes)]."_$anio.xls\"");
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Libro de Iva</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<? if (!$download) echo $html_header; ?>
<form name="form1" method="post" action="">
<? if (!$download) { ?>
	<table width="60%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr align="center"> 
      <td height="49"><font size="+1"><strong>Ver datos correspondientes a</strong></font></td>
    </tr>
    <tr> 
      <td align="center" valign="middle" > Mes &nbsp; 
        <select name="select_mes" id="select_mes">
          <?			make_select_mes($mes); ?>
        </select> &nbsp; A&ntilde;o &nbsp; <select name="select_anio" id="select_anio">
          <? make_select_anio($anio); ?>
        </select>
        &nbsp;&nbsp; 
        <input type="submit" name="Submit" value="Actualizar">
        &nbsp;&nbsp;<a title=" <?="Bajar datos ".$meses[intval($mes)]." de $anio en un excel" ?>" href="<?= encode_link("fact_prov_libro_iva.php",array('download'=>1,'mes'=>$mes,'anio'=>2004)) ?>" ><img src="../../imagenes/excel.gif" width="16" height="16" border="0" > 
        </a></td>
    </tr>
  </table>
  <br>  
<? } 
if ($download)
{
?>
	<table width="150%" border="0" cellspacing="1" cellpadding="0">
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><b>Planilla Compras Coradir S.A</b></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><b><font color="Red"><?= strtoupper("Periodo  ". $meses[intval($mes)]." de $anio") ?></font></b></td>
      <td>&nbsp;</td>
    </tr>    
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><b><?="Total de Facturas: ".$datos->RowCount() ?></b></td>
      <td>&nbsp;</td>
    </tr>    
    <tr>
      <td colspan="4">&nbsp;</td>
    </tr>
  </table>	
<?
}
else 
{
?>
	<table width="150%" border="0" cellspacing="1" cellpadding="0">
    <tr>
      <td ><?="Total de Facturas: ".$datos->RowCount() ?></td>
      <td>&nbsp;</td>
    </tr>
  </table>
<?
}
?>
  <table width="150%" border="<?= (($download)?1:0) ?>" cellspacing="1" cellpadding="0">
    <tr align="center" style="color:#E9E9E9;font-weight: bold;" bgcolor="black">
    	<td width="4%">Fecha</td>
      <td width="6%">Tipo Fact</td>
      <td width="10%">N&ordm; Factura</td>
      <td width="18%">Contribuyente</td>
      <td width="8%">Fantasia</td>
      <td width="12%">N&ordm; C.U.I.T</td>
      <td width="7%">Neto</td>
      <td width="7%">IVA 27</td>
      <td width="7%">IVA 21</td>
      <td width="7%">IVA 10,5</td>
      <td width="9%">TOTAL</td>
      <td width="9%">Imp. Inter</td>
<?
	while (!$datos->EOF)
	{
		$d=$datos->FetchRow();
		//si la moneda es dolar y monto_dolar es cero => son los datos con la version
		//vieja de la pagina
		if ($d['moneda']==2 && $d['monto_dolar']==0)
		{
			//$moneda=($d['moneda']==1)?"$ ":'U$S ';
			$moneda=	'U$S ';
			$style='style=\'mso-number-format:"\[$USD\]\\ \#\,\#\#0\.00\;\[Red\]\[$USD\]\\ \\-\#\,\#\#0\.00"\'';
		}
		else 
		{
			$moneda='$ ';
			$style='style=\'mso-number-format:"\0022$\0022\\ \#\,\#\#0\.00\;\[Red\]\0022$\0022\\ \\-\#\,\#\#0\.00"\'';
		}
		//si es para el excel no se debe poner el simbolo de moneda
		if ($download)
			$moneda="";
?>
    <tr height=10 <? if ($download) echo ((++$i%2)?"bgcolor='#CCFFFF'":"bgcolor='#33CCCC'"); else echo ((++$i%2)?"bgcolor=$bgcolor2":"bgcolor=$bgcolor1") ?> > 
      <td height="16" align="center" style='mso-number-format:"Short Date"'> 
        <?= date2("S",$d['fecha_emision']) ?>
      </td>
      <td height="16" align="center" > 
        <?=($d['tipo_fact'])?$d['tipo_fact']:"&nbsp;" ?>
      </td>
      <td height="16" align="center" > 
        <?=$d['nro_factura'] ?>
      </td>
      <td height="16" > 
        <?=$d['razon_social'] ?>
      </td>
      <td height="16" > 
        <?= ($d['nbre_fantasia'])?$d['nbre_fantasia']:"&nbsp;" ?>
      </td>
      <td height="16" align="center" > 
        <?=($d['cuit'])?$d['cuit']:"&nbsp;" ?>
      </td>
      <td height="16" align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['neto'],2,",",".") ?>
      </td>
      <td height="16" align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['iva27'],2,",",".") ?>
      </td>
      <td height="16" align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['iva21'],2,",",".") ?>
      </td>
      <td height="16" align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['iva10'],2,",",".") ?>
      </td>
      <td height="16" align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['monto'],2,",",".") ?>
      </td>
      <td height="16" align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['imp_internos'],2,",",".") ?>
      </td>
    </tr>
<?
	}
?>
  </table>
</form>
<br>
<? if (!$download) fin_pagina(); ?>