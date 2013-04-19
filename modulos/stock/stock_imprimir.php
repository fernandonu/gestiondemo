<?
/*
Autor: GACZ
Fecha de Creacion: jueves 13/05/04

MODIFICADA POR
$Author: ferni $
$Revision: 1.6 $
$Date: 2006/04/20 15:19:46 $
*/
require_once("../../config.php");?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Impresion de Stock - <?=$deposito?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<?//Este archivo depende de variables en listado_depositos
$prod=sql($q_print) or fin_pagina();?>

<body onload="print();window.close()">
<form action="" method="POST">
<table width="100%" border="0" cellspacing="2" cellpadding="0">	
 <tr align="center">
  <td align="center">
	<font color="Black" size="+2"><b>Impresion de Stock - <?=$deposito?></b></font>
  </td>
 </tr>
</table>
<br>	
<?  
$prod->movefirst();
while (!$prod->EOF){
	$id_prod=$prod->fields[id_prod_esp];
	$total=0;
	$prov_text=0;
?>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
    <tr>
      <td bgcolor="#0099CC">
		<li>
	  	<font size="+1">
	  	<strong>
        <?=$prod->fields[descripcion] ?>
        </strong></font>
		</li>
	  </td>
    </tr>
    <tr>
      <td align="left" width="100%">
        <table width="100%" border="0" align="right" cellpadding="0" cellspacing="0" bordercolor="#333333">
          <tr>
            <td align="left" width="90%">
				&nbsp;
			</td>
            <td width="10%">
            	<strong>Cantidad</strong>
            </td>
          </tr>
          <tr>
            <td width="91%" align="right">
            	<strong>Disponible</strong>&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td width="9%" align="right" style="border-top-style: solid; border-top-width: 1px;"> 
            	<b><?=$prod->fields[cant_disp];?></b>
            </td>
          </tr>
          <tr>
            <td width="91%" align="right">
            	<strong>Total</strong>&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td width="9%" align="right" style="border-top-style: solid; border-top-width: 1px;">
            	<b><?=$prod->fields[cant_total]?></b>
            </td>
          </tr>
        </table>
      </td>
    </tr>
</table>
  <hr>
  <?$prod->Movenext();
}//del while(!$prod->EOF)?>
<tr>
 <td>		
  <font color="Red" size="3"><b>Cantidad Total: <?=$prod->RecordCount();?></b></font>
 </td>
</tr>
</form>
</body>
</html>
<?//=fin_pagina();?>