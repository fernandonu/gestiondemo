<?
/*
Autor: GACZ
Fecha de Creacion: miercoles 21/04/04

MODIFICADA POR
$Author: mari $
$Revision: 1.7 $
$Date: 2006/08/01 14:02:07 $
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
$mes=$_POST['select_mes'] or $mes=$parametros['mes'] or $mes=date('m');
$anio=$_POST['select_anio'] or $anio=$parametros['anio'] or $anio=date('Y');
$download=$parametros['download'];

if ($_POST['bcerrar'])
{
	$fecha=date("Y-m-j H:i:s");
	$q ="insert into libro_iva_compras (fecha_cierre,usuario,mes,anio) ";
	$q.="values ('$fecha','$_ses_user[name]',$mes,$anio)"; //mes y anio vienen de la otra pagina
	
	if (sql($q))
		$msg="Se cerro el Libro de IVA correspondiente al mes de ".$meses[intval($mes)]." año $anio";
	else 
		$msg="<font color=red size=+1>No se pudo cerrar el Libro de iva correspondiente al mes de ".$meses[intval($mes)]." año $anio</font>";
}

$q="select fact_prov.*, proveedor.razon_social from fact_prov join proveedor using (id_proveedor) ";
//$where=" fecha_emision ilike '$anio-$mes-%' order by fecha_emision asc";
$where="anio_libro_iva=$anio AND mes_libro_iva=$mes order by fecha_emision asc";
$q.="where $where";

$datos=sql($q) or fin_pagina();

//recupero el ultimo libro de iva que se cerro
$q="select mes,anio from libro_iva_compras where fecha_cierre=(select max(fecha_cierre) from libro_iva_compras) ";
$ultimo_libro=sql($q) or fin_pagina();
//print_r($ultimo_libro->fields);

//envio la pagina como archivo excel
if($download)
	excel_header("Libro_iva_compras(".$meses[intval($mes)]." $anio).xls");	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Libro de Iva</title>
<meta name=ProgId content=Excel.Sheet>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name=Generator content="Sistema Gestion Coradir">
<? if (!$download) echo $html_header; ?>
<form name="form1" method="post" action="">
<? if (!$download) { ?>
	
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr> 
      <td width="100%" align="center" valign="middle" > <font size="2"><strong>COMPRAS: ver 
        datos correspondientes a</strong></font>&nbsp;&nbsp;&nbsp;Mes &nbsp; 
        <select name="select_mes" id="select_mes">
          <?			make_select_mes($mes); ?>
        </select> &nbsp; A&ntilde;o &nbsp; <select name="select_anio" id="select_anio">
          <? make_select_anio($anio); ?>
        </select> &nbsp;&nbsp; <input type="submit" name="Submit" value="Actualizar">
        &nbsp;&nbsp;<a title=" <?="Bajar datos ".$meses[intval($mes)]." de $anio en un excel" ?>" href="<?= encode_link("libro_iva_compras.php",array('download'=>1,'mes'=>$mes,'anio'=>$anio)) ?>" ><img src="../../imagenes/excel.gif" width="16" height="16" border="0" align="absmiddle" > 
        </a>
      </td>
    </tr>
<? if ($ultimo_libro->fields['mes']!="" && 
	   	//si el mes seleccionado es el siguiente al ultimo cierre de libro iva
		($ultimo_libro->fields['mes']+1==$mes && $ultimo_libro->fields['anio']==$anio ||
		//OR (cambio de anio) mes diciembre 
	     $ultimo_libro->fields['mes']==12 && $ultimo_libro->fields['anio']+1==$anio)) { ?>    
    <tr>
      <td align="center" valign="middle" ><table width="100%"  border="0">
        <tr>
          <td width="11%" align="right">
          <input name="bcerrar" type="submit" id="bcerrar" value="Cerrar Libro" title="Cierra el libro de Compras (<?=$meses[intval($mes)]." $anio" ?>)" onclick="return (confirm('Seguro que desea cerrar el libro de IVA'))">
          </td>
          <td width="56%">&nbsp;</td>
          <td width="33%">&nbsp;</td>
        </tr>
      </table></td>
    </tr>
<?} ?>    
  </table>
<?=($msg)?"<br><center>$msg</center>":""; //si hay mensaje, lo muestra ?>  
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
      <td><b style="color:blue">Planilla Compras Coradir S.A.</b></td>
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
	//pongo los totales en cero
	$total_neto=$total_iva27=$total_iva21=$total_iva10=$total_total=$total_imp=0;

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
			
		$total_neto+=$d['neto'];
		$total_iva27+=$d['iva27'];
		$total_iva21+=$d['iva21'];
		$total_iva10+=$d['iva10'];
		$total_total+=$d['monto'];
		$total_imp+=$d['imp_internos'];
		
?>
    <tr height=16 <? if ($download) echo ((++$i%2)?"bgcolor='#FFFFFF'":"bgcolor='#99CCFF'"); else echo ((++$i%2)?"bgcolor=$bgcolor2":"bgcolor=$bgcolor1") ?> > 
      <td  align="center"> 
        <?= date2("S",$d['fecha_emision']) ?>
      </td>
      <td  align="center" > 
        <?=($d['tipo_fact'])?$d['tipo_fact']:"&nbsp;" ?>
      </td>
      <td  align="center" > 
        <?=$d['nro_factura'] ?>
      </td>
      <td  > 
        <?=$d['razon_social'] ?>
      </td>
      <td  > 
        <?= ($d['nbre_fantasia'])?$d['nbre_fantasia']:"&nbsp;" ?>
      </td>
      <td  align="center" > 
        <?=($d['cuit'])?$d['cuit']:"&nbsp;" ?>
      </td>
      <td  align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['neto'],2,",",".") ?>
      </td>
      <td  align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['iva27'],2,",",".") ?>
      </td>
      <td  align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['iva21'],2,",",".") ?>
      </td>
      <td  align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['iva10'],2,",",".") ?>
      </td>
      <td  align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['monto'],2,",",".") ?>
      </td>
      <td  align="right" <?= $style ?>> 
        <?=$moneda.number_format($d['imp_internos'],2,",",".") ?>
      </td>
    </tr>
<?
	}
	if ($download)
		$style='style=\'mso-number-format:"\0022$\0022\\ \#\,\#\#0\.00\;\[Red\]\0022$\0022\\ \\-\#\,\#\#0\.00"\'';
	else
		$moneda="$ ";
?>
</table>    
<br> 
<br>
<table align="center" border=1 style="border-right:none;border-bottom:none;border-top:none;border-left:none;" cellpadding="2" cellspacing="0" bordercolor=black  <? if (!$download) echo "bgcolor=#E0E0E0" ?>>
	<tr>
		<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
		<td colspan=6 align="center" ><b>TOTALES </b></td>
	</tr>
	<tr>
		<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
		<td align="center" >Neto</td> 
		<td align="center" >IVA 27 </td> 
		<td align="center" >IVA 21 </td> 
		<td align="center" >IVA 10,5 </td> 
		<td align="center" >TOTAL</td> 
		<td align="center" >Impuestos</td> 
	</tr>
	<tr>
		<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
		<td align="right" <?=$style ?>><?=$moneda.number_format($total_neto,2,",",".") ?></td> 
		<td align="right" <?=$style ?>><?=$moneda.number_format($total_iva27,2,",",".") ?></td> 
		<td align="right" <?=$style ?>><?=$moneda.number_format($total_iva21,2,",",".") ?></td> 
		<td align="right" <?=$style ?>><?=$moneda.number_format($total_iva10,2,",",".") ?></td> 
		<td align="right" <?=$style ?>><?=$moneda.number_format($total_total,2,",",".") ?></td> 
		<td align="right" <?=$style ?>><?=$moneda.number_format($total_imp,2,",",".") ?></td> 
	</tr>
	<tr>
		<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
		<td style="border:none" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?>>&nbsp;</td> 
		<td style="border-right:none">&nbsp;</td> 
		<td align="center" <?=$style ?> style="border-right:none;border-left:none;border-bottom:none"><?=$moneda.number_format($total_iva27+$total_iva21+$total_iva10,2,",",".") ?></td> 
		<td style="border-left:none">&nbsp;</td> 
		<td style="border:none;border-top:none" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?>>&nbsp;</td> 
		<td style="border:none;border-top:none" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?>>&nbsp;</td> 
	</tr>
	<tr>
		<? if ($download) echo "<td colspan=5 style='border:none' >&nbsp;</td>\n"; ?>
		<td style="border:none" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?>>&nbsp;</td> 
		<td style="border-right:none">&nbsp; </td> 
		<td align="center" style="border-right:none;border-left:none;border-top:none"><b>TOTAL IVA</b></td> 
		<td style="border-left:none">&nbsp;</td> 
		<td style="border:none;border-top:none" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?>>&nbsp;</td> 
		<td style="border:none;border-top:none" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?>>&nbsp;</td> 
	</tr>
  </table>
	
</form>
<br>
<? if (!$download) fin_pagina(); ?>