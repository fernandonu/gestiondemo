<?
/*
Autor: elizabeth

MODIFICADA POR
$Author: nazabal $
$Revision: 1.4 $
$Date: 2005/04/30 15:54:02 $
*/
require_once("../../config.php");
// echo $html_header;
$producto=$_GET['posicion'];

$onclick['guardar']=$_GET['onclickguardar'] or $onclick['guardar']="control()";
$onclick['cerrar']=$_GET['onclickcerrar'] or $onclick['cerrar']="window.close()";

$sql="select nombre as nombre_prov, sum(cant_disp) as cant_disp, desc_gral, id_deposito 
      from stock.stock 
	  left join general.depositos using (id_deposito)
      left join general.productos using (id_producto)
      where id_producto=$producto and depositos.tipo=0
      group by id_deposito, nombre, desc_gral
      order by id_deposito";
$res_sql=sql($sql, "Error al traer los datos de los productos disponibles en el Stock de Coradir") or fin_pagina();
$cant_res=$res_sql->RecordCount();
//echo $sql;
?>
<html>
<head>
<link rel=stylesheet type='text/css' href='<?=$html_root?>/lib/estilos.css'>
<script languaje='javascript' src='<?=$html_root?>/lib/funciones.js'></script>

<script>
var op = 1;
function alerta(){
	if (op == 1 ) {
		// #3c97c4   #36b3c9   #29afd6
		document.all.tt1.style.border = '5px solid  #29afd6';
		op = 0;
     } else {
		document.all.tt1.style.border = '5px solid #ffffff';
		op = 1;
	}
	alerta_sinc();
}
function alerta_sinc(){
		setTimeout("alerta()",500);
}
</script>
</head>

<body bgcolor="<?=$bgcolor3?>" onload='document.focus(); alerta_sinc();'>
<table width="100%" id="tt1" cellspacing="0" align="center"><tr><td> 
<table align="center" width="100%">
<tr id=mo>
<td align="center" colspan="2"><h4>Productos en Stock de Coradir</h4></td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr id=ma>
 <td align="center" colspan="2"><font size="3"><b>Producto</b><br><?=$res_sql->fields['desc_gral']?></font></td>
</tr>
</table>
<br>
<table align="center" width="100%" bgcolor=#cccccc border=1 bordercolor=#E0E0E0 cellspacing="0">
<tr id=mo>
 <!-- <td align="center"><b>ID Depósito</b></td> -->
 <td align="center"><b>Depósito</b></td>
 <td align="center""><b>Cant. Disponible</b></td>
</tr>
<? for ($i;$i<$cant_res;$i++) { 
	if ($res_sql->fields['cant_disp']!=0) { ?>
<tr>
 <!-- <td align="center"><?//=$res_sql->fields['id_deposito'];?></td> --> 
 <td align="center" width="70%"><?=$res_sql->fields['nombre_prov'];?></td>
 <td align="center" width="30%"><?=$res_sql->fields['cant_disp'];?></td>
</tr>
   <? } ?> 
<? $res_sql->MoveNext();} ?>
</table>
</td></tr></table>
<br> 
<table align="center">
<tr>
 <td><input name="boton" type="button" value="Cerrar" onclick="<?=$onclick['cerrar']?>"></td>
</tr> 
</table> 
</body>
</html>