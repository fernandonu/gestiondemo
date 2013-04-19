<?php

/*
$Author: mari $
$Revision: 1.5 $
$Date: 2006/10/11 13:46:29 $
*/
require_once ("../../config.php");

$sql=$parametros["sql"];
$total=$parametros["total"];
$fecha_desde=$parametros['fecha_desde'];
$fecha_hasta=$parametros['fecha_hasta'];
$datos=sql($sql) or fin_pagina();

excel_header("informe_casos.xls");

?>
<form name=form1 method=post action="informe_excel_listado.php">

<table width="100%">
  <tr>
    <td align=left colspan="2"> <font color='red'> <b>Total Casos: <?=$total?> </b> </font></td>
    <td colspan=3 align="right"><b>Informe de casos derivados desde el día <?=$fecha_desde?> al <?=$fecha_hasta?></b> </td>
  </tr>
</table> 

 <br>
 <table width="100%" align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5"> 
  <tr bgcolor=#C0C0FF>
   <td align="center">Nº </td>
   <td align="center"> Caso   </td>
   <td align="center"> Fecha Inicio  </td>
   <td align="center"> Id Lic.   </td>
   <td align="center"> Ensamblador </td>
   <td align="center"> Nro Serie </td>
   <td align="center"> Cliente  </td>
   <td align="center"> Falla  </td>
   <td align="center"> Origen Falla  </td>
   <td align="center"> Diagnostico / Solución  </td>
   <td align="center"> Estado</td>
   <td align="center"> CAS </td>
   <td align="center">  Observaciones </td>
   
  </tr>
  <?
  $i=1; 
  while (!$datos->EOF)
  {
  	    ?>
   <tr>
     <td align="center"><?=$i++;?></td>
     
   <?$ca=substr($datos->fields["nrocaso"],-5)?>
    <td> <?=$ca=str_pad($ca,5,"0",STR_PAD_LEFT);?></td>
     <td align="center"><?=fecha($datos->fields["fechainicio"])?> </td>
     <td align="center"><?=$datos->fields["id_licitacion"]?> </td>
     <td align="center"><? if ($datos->fields["ensamblador"]) echo $datos->fields["ensamblador"]; else echo "&nbsp;"?>  </td>
     <td align="center"><?=$datos->fields["nserie"]?> </td>
     <td align="center"><?=$datos->fields["cliente"]?> </td>
     <td align="center"><?=$datos->fields["falla"]?> </td>
     <td align="center"><?=$datos->fields["desc_origen_falla"]?> </td>
     <td align="center">&nbsp; </td>
     <td align="center">
       <? switch ($datos->fields["idestuser"])
        {
         case "1":$estado_caso='En curso';break;
         case "2":$estado_caso='Finalizado';break;
         case "7":$estado_caso='Pendiente';break;
        }
  	   echo $estado_caso;?>
      </td>
      <?$cas=$datos->fields["cas"];
        if (!stristr($datos->fields["cas"], "Coradir"))
             $cas.=" - ".$datos->fields["ciudad"];?>
      <td align="center"> <?=$cas?>  </td>
      <td align="center"> &nbsp; </td>
     </tr>
    <?
  	$datos->MoveNext();
  }//de while(!$datos->EOF)
  ?>
 </table>
 </form>