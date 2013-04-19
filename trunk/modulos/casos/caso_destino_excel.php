<?php
/*
$Author: diegoinga $
$Revision: 1.3 $
$Date: 2004/12/06 13:49:25 $
idate,cas_ate.nombre as nombre,contacto,direccion, tel,mail,comentario,activo, distrito.nombre as provincia
*/
require_once("../../config.php");
$sql=$parametros["sql"] or $sql=$_POST["sql"];
$resultado=sql($sql) or fin_pagina();
$tamaño_titulo=6;
$tamaño_filas=4;
//echo $html_header;
excel_header("stock-$desc_gral-$nombre_deposito.xls");
//echo $sql2;
?>
<html>
<body>
<style>
 .xl31
      {mso-style-parent:style0;
       border-top:.5pt solid windowtext;
       border-right:none;
       border-bottom:.5pt solid windowtext;
       border-left:.5pt solid windowtext;
       white-space:normal;}
.xl42
	{mso-style-parent:style0;
	font-weight:700;
	mso-number-format:"\@";
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid black;
	border-right:.5pt solid black;
	border-bottom:none;
	border-left:.5pt solid black;
	white-space:normal;}

</style>
<form name=form1 method=post>
<?
for($i=0;$i<$resultado->recordcount();$i++){
?>
<table width=100% align=center border=0  cellspacing="1" cellpadding="1" bordercolor=black>
   <tr>
      <td width=15% align=left>&nbsp;</td>
      <td <?=excel_style("texto")?> colspan=2><font size=<?=$tamaño_titulo?>><b>Destino</b></font></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
      <td><font size=<?=$tamaño_filas?>>Nombre:</font></td>
      <td  colspan=3 ><?=$resultado->fields["nombre"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Dirección:</font></td>
     <td <?=excel_style("texto")?> colspan=2><?=$resultado->fields["direccion"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Ciudad</font></td>
     <td <?=excel_style("texto")?> colspan=2><?=$resultado->fields["ciudad"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Provincia</font></td>
     <td <?=excel_style("texto")?> colspan=2><?=$resultado->fields["provincia"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>C.P.</font></td>
     <td <?=excel_style("texto")?> colspan=2><?=$resultado->fields["cp"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Contacto</font></td>
     <td <?=excel_style("texto")?> colspan=1><?=$resultado->fields["contacto"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Teléfono</font></td>
     <td <?=excel_style("texto")?> colspan=2><?=$resultado->fields["tel"]?></td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Motivo/CASO</font></td>
     <td colspan=2>&nbsp;</td>
   </tr>
   <tr><td colspan=3>&nbsp;</td></tr>
   <tr>
     <td <?=excel_style("texto")?>><font size=<?=$tamaño_filas?>>Tipo de Envío&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></td>
     <td colspan=2>&nbsp;</td>
   </tr>
  <tr><td colspan=3>&nbsp;</td></tr>
   <tr>

      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>

      <td align=right <?=excel_style("texto")?>>
        <table width=30%>
             <tr>
                  <td align=left <?=excel_style("texto")?>>
                  <font size=<?=$tamaño_filas?>><b>Remite</b></font>
                  </td>
             </tr>
             <tr>
                 <td align=left <?=excel_style("texto")?>>
                 <font size=<?=$tamaño_filas?>>
                 <b>CORADIR S.A.</b>
                 </font>
                 </td>
             </tr>
             <tr>
                <td align=left <?=excel_style("texto")?>>
                <font size=<?=$tamaño_filas?>>
                <b>San Martin 454</b>
                </font>
                </td>
             </tr>
             <tr>
               <td align=left <?=excel_style("texto")?>>
               <font size=<?=$tamaño_filas?>>
               <b>San Luis</b>
               </font>
               </td>
             </tr>
             <tr>
               <td align=left <?=excel_style("texto")?>>
               <font size=<?=$tamaño_filas?>>
                <b>5700</b>
               </font>
               </td>
             </tr>
             <tr>
                <td align=left <?=excel_style("texto")?>>
                <font size=<?=$tamaño_filas?>>
                <b>Contacto: Ariel Estrada</b>
                </font>
                </td>
             </tr>
             <tr>
              <td align=left <?=excel_style("texto")?>>
              <font size=<?=$tamaño_filas?>>
              <b>0810 - 22 - CORADIR (2672347)</b>
              </font>
              </td>
             </tr>
        </table>
      </td>
   </tr>
<tr><td colspan=3>&nbsp;</td></tr>
<tr><td colspan=3>&nbsp;</td></tr>
</table>
<br clear=all style='page-break-before:always'>
<?
$resultado->movenext();
}
?>
</form>