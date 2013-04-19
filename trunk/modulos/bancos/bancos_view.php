<?
/*
$Revision: 1.2 $
$Id: bancos_view.php,v 1.2 2003/10/28 21:07:23 cestila Exp $
*/

if (!defined("lib_included")) { die("Use index.php!"); }

//session_start();

/*if (!$user_Pdialup) {
	echo "<b>Usted no tiene acceso a este sector!</b>\n";
	exit;
}
*/
?>

<!-- 0 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Cheques_Debitados>
</form>
<!-- 1 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Cheques_Pendientes>
</form>
<!-- 2 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Debitos>
</form>
<!-- 3 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Cheques_entre_Fechas>
</form>
<!-- 4 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Depositos_Acreditados>
</form>
<!-- 5 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Depositos_Pendientes>
</form>
<!-- 6 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Tarjetas_Acreditadas>
</form>
<!-- 7 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Tarjetas_Pendientes>
</form>
<!-- 8 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mov_Saldo>
</form>
<!-- 9 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Ing_Cheques>
</form>
<!-- 10 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Ing_Depositos>
</form>
<!-- 11 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Ing_Debitos>
</form>
<!-- 12 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Ing_Tarjetas>
</form>
<!-- 13 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Nue_Banco>
</form>
<!-- 14 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Nue_Tipo_Deposito>
</form>
<!-- 15 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Nue_Tipo_Debito>
</form>
<!-- 16 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Nue_Tipo_Tarjeta>
</form>
<!-- 17 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Val_Cheque_de_Terceros>
</form>
<!-- 18 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Val_Ingreso_Cheque>
</form>
<!-- 19 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Imp_Cheques_por_Fecha>
</form>
<!-- 20 -->
<form action=bancos.php method=post>
<input type=hidden name=mode value=forms>
<input type=hidden name=cmd value=Mant_Proveedores>
</form>
<table align=center width="770" border="0" cellpadding="5" cellspacing="5" bordercolor="<? echo $bgcolor1; ?>" bgcolor="<? echo $bgcolor2; ?>">
<tr>
<td width=50% valign=top>
<table width="100%" border="1" cellpadding="6" cellspacing="5" bordercolor="<? echo $bgcolor1; ?>" bgcolor="<? echo $bgcolor3; ?>">
  <tr> 
    <td colspan="2" id=mo><div align="center"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Movimientos</strong></font></div></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td width=50% id=boton_ma onClick="JS('document.forms[0].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Cheques Debitados</font></strong></td>
    <td width=50% id=boton_ma onClick="JS('document.forms[1].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Cheques Pendientes</font></strong></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[2].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">D&eacute;bitos</font></strong></td>
    <td id=boton_ma onClick="JS('document.forms[3].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Cheques entre Fechas</strong></font></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[4].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Depósitos Acreditados</font></strong></td>
    <td id=boton_ma onClick="JS('document.forms[5].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Depósitos Pendientes</font></strong></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[6].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Tarjetas Acreditadas</strong></font></td>
    <td id=boton_ma onClick="JS('document.forms[7].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Tarjetas Pendientes</strong></font></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[8].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'" colspan=2><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Saldos</strong></font></td>
  </tr>
</table>
<br>
<table width="100%" border="1" cellpadding="6" cellspacing="5" bordercolor="<? echo $bgcolor1; ?>" bgcolor="<? echo $bgcolor3; ?>">
  <tr> 
    <td colspan="2" id=mo><div align="center"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Valores de Terceros</strong></font></div></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td width=50% id=boton_ma onClick="JS('document.forms[17].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Cheque de Terceros</font></strong></td>
    <td width=50% id=boton_ma onClick="JS('document.forms[18].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Ingreso Cheque</font></strong></td>
  </tr>
</table>
</td>
<td width=50% valign=top>
<table width="100%" border="1" cellpadding="6" cellspacing="5" bordercolor="<? echo $bgcolor1; ?>" bgcolor="<? echo $bgcolor3; ?>">
  <tr> 
    <td colspan="2" id=mo><div align="center"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Ingresos</strong></font></div></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td width=50% id=boton_ma onClick="JS('document.forms[9].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Cheques</font></strong></td>
    <td width=50% id=boton_ma onClick="JS('document.forms[10].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Depósitos</font></strong></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[11].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">D&eacute;bitos</font></strong></td>
    <td id=boton_ma onClick="JS('document.forms[12].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Tarjetas</strong></font></td>
  </tr>
</table>
<br>
<table width="100%" border="1" cellpadding="6" cellspacing="5" bordercolor="<? echo $bgcolor1; ?>" bgcolor="<? echo $bgcolor3; ?>">
  <tr> 
    <td colspan="2" id=mo><div align="center"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Mantenimiento</strong></font></div></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td width=50% id=boton_ma onClick="JS('document.forms[13].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Bancos</font></strong></td>
    <td width=50% id=boton_ma onClick="JS('document.forms[14].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Depósitos</font></strong></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[15].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">D&eacute;bitos</font></strong></td>
    <td id=boton_ma onClick="JS('document.forms[16].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Tarjetas</strong></font></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[20].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'" colspan=2><font face="Verdana, Arial, Helvetica, sans-serif"><strong>Proveedores</strong></font></td>
  </tr>
</table>
<!--
<br>
<table width="100%" border="1" cellpadding="6" cellspacing="5" bordercolor="<? echo $bgcolor1; ?>" bgcolor="<? echo $bgcolor3; ?>">
  <tr> 
    <td colspan="2" id=mo><div align="center"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Estadísticas</strong></font></div></td>
  </tr>
  <tr align="center" valign="middle" bordercolor="#000000" bgcolor="#CCCCCC"> 
    <td id=boton_ma onClick="JS('document.forms[19].submit()')" onMouseOut="this.style.backgroundColor = '<? echo $bgcolor2; ?>'" onMouseOver="this.style.backgroundColor = '#ffffff'"><strong><font face="Verdana, Arial, Helvetica, sans-serif">Cheques por Fecha</font></strong></td>
  </tr>
</table>
-->
</td>
</tr>
</table>