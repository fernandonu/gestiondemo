<?
/*
Autor: GACZ
Creado: lunes 20/12/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.5 $
$Date: 2005/01/04 21:32:26 $
*/

require_once("../../config.php");
if ($_POST['bterminar'])
{
	require("op_problemas_proc.php");
	echo "<script>window.close()</script>";
}
?>
<html>
<head>
<!-- Esto se agrega para que al abrir la ventana como modal, haga el submit en la misma pagina y no una nueva -->
<base target="_self">
<title>Problemas en seguimiento de producción</title>
<script src="../../lib/fns.js"></script>
<?=$html_header ?>
<body>
<form name="form1" action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST">
<table width="95%"  border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" >&nbsp;
    </td>
    </tr>
  <tr>
    <td align="center">      <b>Postergar Todos</b>
      <select name="select_postergar_all" onChange="if (typeof form1.select_postergar!='undefined') for (var i=0; i < form1.select_postergar.length; i++) {form1.select_postergar[i].selectedIndex=form1.select_postergar_all.selectedIndex}" >
        <option value="0,0,5" style="text-align:center" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5min</option>
        <option value="0,0,15" style="text-align:right" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;15min</option>
        <option value="0,0,30" style="text-align:right" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;30min</option>
        <option value="0,1,0" style="text-align:right" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
        <option value="0,2,0" style="text-align:right" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
        <option value="0,3,0" style="text-align:right" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
        <option value="0,6,0" style="text-align:right" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
        <option value="1,0,0" style="text-align:right" >1dia&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
        <option value="2,0,0" style="text-align:right" >2dias&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
        <option value="7,0,0" style="text-align:right" >7dias&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
      </select>
	  &nbsp;&nbsp;&nbsp;
      <b>Descartar Todos</b>
      <input name="chk_descartar_all" type="checkbox" value="checkbox" onClick="if (typeof form1.chk_descartar!='undefined') for (var i=0; i < form1.chk_descartar.length; i++) {form1.chk_descartar[i].checked=form1.select_postergar[i].disabled=form1.chk_descartar_all.checked}">	  
      <hr>
      <input type="submit" name="bterminar" value="Terminar" ></td>
    </tr>
</table>
<br />
<table id=datatable width="100%" border="1" width="95%"  border="1" align="center" cellpadding="2" cellspacing="0" bordercolor="#000000">
<!--  <th colspan="6">Problemas  de Producci&oacute;n </th>-->
  <thead align="center">
  <tr >
    <th colspan="7">Problemas de Producción(<span id="span_total"></span>)</th>
  </tr>
  <tr id="mo" style="font-size=12px">
    <th width="9%" scope="col">ID Licitacion </th>
    <th width="10%" scope="col">Venc OC </th>
    <th width="10%" scope="col">N&ordm; Seg. </th>
    <th width="45%" scope="col">Descripci&oacute;n del Problema</th>
    <th width="19%" scope="col">Revisado</th>
    <th width="19%" scope="col">Postergar</th>
    <th width="7%" scope="col">Descartar</th>
  </tr>
  </thead>
<?
//se usa para crear un <TR> de la tabla con los datos en el arreglo aproblem y usa el indice j
function write_tr($aproblem,$j)
{
		 echo 
		"	<tr bgcolor='{$aproblem['trcolor']}' style=\"cursor:'hand'\" onmouseover=\"alternar_color(this,'white')\" onmouseout=\"alternar_color(this,'white')\" >
			<a target='_blank' href='{$aproblem['link']}'>
			<input type='hidden' name='id_entrega[$j]' value='{$aproblem['id_entrega']}'>
			<input type='hidden' name='id_tipo_prob[$j]' value='{$aproblem['id_tipo_prob']}'>
			<input type='hidden' name='id_prob[$j]' value='{$aproblem['id_prob']}'>
			<input type='hidden' name='hcambio[$j]' id='hcambio' value=1>		
		   <td>{$aproblem['id_lic']}</td>
		   <td align='right'>{$aproblem['vence_oc']}</td>
		   <td align='center'>{$aproblem['nro_seg']}<b>/</b>{$aproblem['nro_orden_cliente']}</td>
		   <td><b>{$aproblem['descripcion']}</b></td>
		   <td align=center><b>{$aproblem['last_review']}</b></td>
		  </a>		
		   <td align='center' style='cursor:default'>
		   <select name='select_postergar[$j]' id='select_postergar' style='text-align:right' ".($aproblem['descartar']==1?"disabled":"").">
		   <option value='0,0,5' ".($aproblem['postergar']=='0,0,5'?"selected":"")." style='text-align:center' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5min</option>
		   <option value='0,0,15' ".($aproblem['postergar']=='0,0,15'?"selected":"")." style='text-align:right' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;15min</option>
		   <option value='0,0,30' ".($aproblem['postergar']=='0,0,30'?"selected":"")." style='text-align:right' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;30min</option>
		   <option value='0,1,0' ".($aproblem['postergar']=='0,1,0'?"selected":"")." style='text-align:right' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   <option value='0,2,0' ".($aproblem['postergar']=='0,2,0'?"selected":"")." style='text-align:right' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   <option value='0,3,0' ".($aproblem['postergar']=='0,3,0'?"selected":"")." style='text-align:right' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   <option value='0,6,0' ".($aproblem['postergar']=='0,6,0'?"selected":"")." style='text-align:right' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6h &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   <option value='1,0,0' ".($aproblem['postergar']=='1,0,0'?"selected":"")." style='text-align:right' >1dia&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   <option value='2,0,0' ".($aproblem['postergar']=='2,0,0'?"selected":"")." style='text-align:right' >2dias&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   <option value='7,0,0' ".($aproblem['postergar']=='7,0,0'?"selected":"")." style='text-align:right' >7dias&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
		   </select>
		   </td>
	     <td align='center' style='cursor:default' ><input name='chk_descartar[$j]' id='chk_descartar' value=1 ".($aproblem['descartar']==1?"checked":"")." type='checkbox' onclick=\"if (this.checked) form1.select_postergar[$j].disabled=true; else form1.select_postergar[$j].disabled=false; \"></td>
		  </tr>";
}
require("op_problemas_xml.php");
$total=0;
$prob_count=count($aproblemas);//variable que se setea en op_problemas_xml.php

for($j=0; $j< $prob_count; $j++)
{
		write_tr($aproblemas[$j],$total);
		$total++;
}
?>
<script>
document.all.span_total.innerHTML=<?=$total ?>;
</script>
</table>
<br />
<br />
<center>
  <input type="submit" name="bterminar" value="Terminar" >
<!--  <input type="button" name="bconfig" value="Configuracion" onclick="window.open('op_problemas_status.php','_self','height=600px, width=800,resizable=1,scrollbars=1')" >-->
</center>
</form>
<?
fin_pagina();
?>