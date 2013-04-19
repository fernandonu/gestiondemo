<?
/*
Autor: GACZ
Fecha de Creacion: miercoles 05/05/04

MODIFICADA POR
$Author: fernando $
$Revision: 1.10 $
$Date: 2006/12/13 15:45:39 $
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

$q="select facturas.fecha_factura,(numeracion_sucursal.numeracion || text('-') ||facturas.nro_factura) as nro_factura ,
           facturas.tipo_factura,
           facturas.cliente,facturas.cuit,facturas.iva_tasa,facturas.id_factura,
           facturas.cotizacion_dolar,
     entidad.localidad,case when estado='a' then 0 else total end as total ".
	"from facturas left join
	facturacion.numeracion_sucursal using (id_numeracion_sucursal)
	left join 
	 (select id_factura,sum(precio*cant_prod) as total from items_factura group by id_factura) as totales using (id_factura)  ";
$q.="left join entidad using(id_entidad)";
/*
$q="select facturas.*,
     entidad.localidad,case when estado='a' then 0 else total end as total ".
	"from facturas left join 
	 (select id_factura,sum(precio*cant_prod) as total from items_factura group by id_factura) as totales using (id_factura)  ";
$q.="left join entidad using(id_entidad)";
*/
$mes=$_POST['select_mes'] or $mes=$parametros['mes'] or $mes=date('m');
$anio=$_POST['select_anio'] or $anio=$parametros['anio'] or $anio=date('Y');
$download=$parametros['download'];

$where=" fecha_factura ilike '$anio-$mes-%' order by fecha_factura";

$q.="where $where";

$datos=sql($q) or fin_pagina();

//envio la pagina como archivo excel
if($download)
	excel_header("Libro_iva_ventas(".$meses[intval($mes)]." $anio).xls");

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
      <td width="100%" align="center" valign="middle" > <font size="2"><strong>VENTAS: ver 
        datos correspondientes a</strong></font>&nbsp;&nbsp;&nbsp;Mes &nbsp; 
        <select name="select_mes" id="select_mes">
          <?			make_select_mes($mes); ?>
        </select> &nbsp; A&ntilde;o &nbsp; <select name="select_anio" id="select_anio">
          <? make_select_anio($anio); ?>
        </select> &nbsp;&nbsp; <input type="submit" name="Submit" value="Actualizar">
        &nbsp;&nbsp;<a title=" <?="Bajar datos ".$meses[intval($mes)]." de $anio en un excel" ?>" href="<?= encode_link("libro_iva_ventas.php" ,array('download'=>1,'mes'=>$mes,'anio'=>$anio)) ?>" ><img src="../../imagenes/excel.gif" width="16" height="16" border="0" align="absmiddle" > 
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
      <td><b style="color:green">Planilla Ventas Coradir S.A</b></td>
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
  <table width="1512" border="<?= (($download)?1:0) ?>" cellspacing="1" cellpadding="0">
    <tr align="center" style="color:#E9E9E9;font-weight: bold;" bgcolor="black"> 
		
      <td width="70">Fecha</td>
      <td width="80">Tipo Fact</td>
      <td width="90">N&ordm; Factura</td>
      <td width="170">Contribuyente</td>
      <td width="105">N&ordm; C.U.I.T</td>
      <td width="190">Domicilio</td>
      <td width="100">Neto B</td>
      <td width="100">B 21</td>
      <td width="100">B 10,5</td>
      <td width="100">TOTAL B</td>
      <td width="100">Neto A</td>
      <td width="100">A 21</td>
      <td width="100">A 10,5</td>
      <td width="100">TOTAL A</td>
      <?
    //pongo las variables en cero 
    $bneto=$b21=$b105=$btotal=$aneto=$a21=$a105=$atotal=0;
    $arreglo_netos=array();//indice=localidad
	while (!$datos->EOF)
	{
		$d=$datos->FetchRow();
		
		//si la moneda es dolar y cotiz existe (no es la version anterior de facturas sin cotiz)
		if ($d['id_moneda']==2 && $d['cotizacion_dolar']==0)
		{
			//$moneda=($d['moneda']==1)?"$ ":'U$S ';
			$moneda=	'U$S ';
			$ratio_dolar=1;
			$style='style=\'mso-number-format:"\[$USD\]\\ \#\,\#\#0\.00\;\[Red\]\[$USD\]\\ \\-\#\,\#\#0\.00"\'';
		}
		else 
		{
			$moneda='$ ';
			if ($d['cotizacion_dolar']!=0)
			 $ratio_dolar=$d['cotizacion_dolar'];
			else
			 $ratio_dolar=1;;
			$style='style=\'mso-number-format:"\0022$\0022\\ \#\,\#\#0\.00\;\[Red\]\0022$\0022\\ \\-\#\,\#\#0\.00"\'';
		}
		//si es para el excel no se debe poner el simbolo de moneda
		if ($download)
			$moneda="";
?>
    <tr height=16 <? if ($download) echo ((++$i%2)?"bgcolor='#CCFFFF'":"bgcolor='#33CCCC'"); else echo ((++$i%2)?"bgcolor=$bgcolor2":"bgcolor=$bgcolor1") ?> > 
      <td  align="center" style='mso-number-format:"Short Date"'> 
        <?= date2("S",$d['fecha_factura']) ?>
      </td>
      <td   align="center" > 
        <?= strtoupper($d['tipo_factura']) ?>
      </td>
      <td   align="center" > 
        <?=$d['nro_factura'] ?>
      </td>
      <td   align="left" > 
        <?=$d['cliente'] ?>
      </td>
      <td   align="center" > 
        <?=($d['cuit'])?$d['cuit']:"&nbsp;" ?>
      </td>
      <td    > 
        <?=$d['localidad'] ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- B NETO -->
        <? //if ($d['tipo_factura']=='b') { echo $moneda.number_format($aux=$ratio_dolar*$d['total']*(1-$d['iva_tasa']/100),2,",","."); $bneto+=$aux; $arreglo_netos[$d['localidad']]+=$aux; }else "&nbsp;";  ?>
        <? if ($d['tipo_factura']=='b') { echo $moneda.number_format($aux=($ratio_dolar*$d['total'])/(1+($d['iva_tasa']/100)),2,",","."); $bneto+=$aux; $arreglo_netos[$d['localidad']]+=$aux; }else "&nbsp;";  ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- B 21 -->
        <?// if ($d['tipo_factura']=='b' && $d['iva_tasa']==21) { echo $moneda.number_format($aux=$ratio_dolar*$d['total']*$d['iva_tasa']/100,2,",","."); $b21+=$aux; }else "&nbsp;"; ?>
        <? if ($d['tipo_factura']=='b' && $d['iva_tasa']==21) { echo $moneda.number_format($aux=($ratio_dolar*$d['total'])*(($d['iva_tasa']/100)/(1+$d['iva_tasa']/100)),2,",","."); $b21+=$aux; }else "&nbsp;"; ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- B 10.5 -->
        <?// if ($d['tipo_factura']=='b' && $d['iva_tasa']==10.5) {echo $moneda.number_format($aux=$ratio_dolar*$d['total']*$d['iva_tasa']/100,2,",","."); $b105+=$aux; } else "&nbsp;"; ?>
        <? if ($d['tipo_factura']=='b' && $d['iva_tasa']==10.5) {echo $moneda.number_format($aux=($ratio_dolar*$d['total'])*(($d['iva_tasa']/100)/(1+$d['iva_tasa']/100)),2,",","."); $b105+=$aux; } else "&nbsp;"; ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- B TOTAL -->
        <? if ($d['tipo_factura']=='b') { echo $moneda.number_format($aux=$ratio_dolar*$d['total'],2,",","."); $btotal+=$aux;}else "&nbsp;";  ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- A NETO -->
        <? //if ($d['tipo_factura']=='a') {echo $moneda.number_format($aux=$ratio_dolar*$d['total']*(1-$d['iva_tasa']/100),2,",","."); $aneto+=$aux;$arreglo_netos[$d['localidad']]+=$aux; }else "&nbsp;";  ?>
        <? if ($d['tipo_factura']=='a') {echo $moneda.number_format($aux=($ratio_dolar*$d['total'])/(1+($d['iva_tasa']/100)),2,",","."); $aneto+=$aux;$arreglo_netos[$d['localidad']]+=$aux; }else "&nbsp;";  ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- A 21 -->
        <?// if ($d['tipo_factura']=='a' && $d['iva_tasa']==21) { echo $moneda.number_format($aux=$ratio_dolar*$d['total']*$d['iva_tasa']/100,2,",","."); $a21+=$aux; } else "&nbsp;";  ?>
        <? if ($d['tipo_factura']=='a' && $d['iva_tasa']==21) { echo $moneda.number_format($aux=($ratio_dolar*$d['total'])*(($d['iva_tasa']/100)/(1+$d['iva_tasa']/100)),2,",","."); $a21+=$aux; } else "&nbsp;";  ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- A 10.5 -->
        <? //if ($d['tipo_factura']=='a' && $d['iva_tasa']==10.5) { echo $moneda.number_format($aux=$ratio_dolar*$d['total']*$d['iva_tasa']/100,2,",","."); $a105+=$aux; } else "&nbsp;";  ?>
        <? if ($d['tipo_factura']=='a' && $d['iva_tasa']==10.5) { echo $moneda.number_format($aux=($ratio_dolar*$d['total'])*(($d['iva_tasa']/100)/(1+$d['iva_tasa']/100)),2,",","."); $a105+=$aux; } else "&nbsp;";  ?>
      </td>
      <td   align="right" <?= $style ?>> 
        <!-- A TOTAL -->
        <? if ($d['tipo_factura']=='a') { echo  $moneda.number_format($aux=$ratio_dolar*$d['total'],2,",","."); $atotal+=$aux; } else "&nbsp;";   ?>
      </td>
    </tr>
    <?
	}
?>
  </table>
<br> 
<br> 
<? if ($download)
   {	
 	$style='style=\'mso-number-format:"\0022$\0022\\ \#\,\#\#0\.00\;\[Red\]\0022$\0022\\ \\-\#\,\#\#0\.00"\''; 
 	$moneda="";
   }
   else 
   {
    $moneda="$ ";
    $style="";
   }
 	?>
<table border=1 bordercolor=black align="center" <? if (!$download) echo "bgcolor=#E0E0E0" ?> cellspacing=0 cellpadding=3 style='border-right:none;border-bottom:none;border-top:none;border-left:none;'>
<tr>
	<? if ($download) echo "<td colspan=6 style='border:none'>&nbsp;</td>\n"; ?>
	<td colspan=8 align="center"><b>TOTALES</b></td>
</tr>
<tr>
	<? if ($download) echo "<td colspan=6 style='border:none'>&nbsp;</td>\n"; ?>
	<td align="center"><font color="red"><b>IVA A 21</b></font></td>	
	<td align="center"><font color="red"><b>IVA A 10,5</b></font></td>	
	<td align="center"><font color="red"><b>IVA B 21</b></font></td>	
	<td align="center"><font color="red"><b>IVA B 10,5</b></font></td>	
	<td align="center"><font color="Blue"><b>NETO A</b></font></td>	
	<td align="center"><font color="Blue"><b>NETO B</b></font></td>	
	<td align="center"><font color="green"><b>TOTAL A</b></font></td>	
	<td align="center"><font color="green"><b>TOTAL B</b></font></td>	
</tr>
<tr>
	<? if ($download) echo "<td colspan=6 style='border:none'>&nbsp;</td>\n"; ?>
	<td <?=$style ?> align="right"><?=$moneda.number_format($a21,2,",",".");  ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($a105,2,",","."); ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($b21,2,",","."); ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($b105,2,",","."); ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($aneto,2,",","."); ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($bneto,2,",","."); ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($atotal,2,",","."); ?></td>	
	<td <?=$style ?> align="right"><?=$moneda.number_format($btotal,2,",","."); ?></td>	
</tr>
<tr>
	<? if ($download) echo "<td colspan=6 style='border:none'>&nbsp;</td>\n"; ?>
	<td colspan=2 align="center"  <?=$style ?>><?=$moneda.number_format($a21+$a105,2,",","."); ?></td>
	<td colspan=2 align="center" <?=$style ?>><?=$moneda.number_format($b21+$b105,2,",","."); ?></td>
	<td colspan=2 align="center" <?=$style ?>><font color="Blue"><b> <?=$moneda.number_format($aneto+$bneto,2,",","."); ?></b></font></td>
	<td colspan=2 align="center" <?=$style ?>><font color="Green"><b><?=$moneda.number_format($atotal+$btotal,2,",","."); ?></b></font></td>
</tr>
<tr>
	<? if ($download) echo "<td colspan=6 style='border:none'>&nbsp;</td>\n"; ?>
	<td colspan=4 align="center" <?=$style ?>><font color="Red" ><b> <?=$moneda.number_format($a21+$a105+$b21+$b105,2,",","."); ?></b></font></td>
	<td colspan=4 align="center" <? if (!$download) echo "background='$html_root/imagenes/fondo.gif'"?> style="border-right:none;border-bottom:none" >&nbsp;</td> 
</tr>
</table>
<br>
<table border=1 bordercolor=black align="center" <? if (!$download) echo "bgcolor=#E0E0E0" ?> cellspacing=0 cellpadding=3 style='border-right:none;border-bottom:none;border-top:none;border-left:none;'>
<tr>
	<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
	<td colspan=3 align="center"><b>NETOS POR PROVINCIA</b></td>
</tr>
<? $aux=0;
 foreach ($arreglo_netos as $key => $value)
	{
?>
	<tr> 
	<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
	<td><?=($key)?$key:"<font color='red'>Localidad Sin Nombre</font>"; ?></td><td colspan="2" align="right" <?= $style ?>><? echo $moneda.number_format($value,2,",",".");$aux+=$value; ?></td>
	</tr>
<?
	}
?>
	<tr> 
	<? if ($download) echo "<td colspan=5 style='border:none'>&nbsp;</td>\n"; ?>
	<td colspan="3" align="right" <?= $style ?>><font color="Blue"><b><?=$moneda.number_format($aux,2,",","."); ?></b></font></td>
	</tr>

</table>
</form>
<br>
<? if (!$download) fin_pagina(); ?>