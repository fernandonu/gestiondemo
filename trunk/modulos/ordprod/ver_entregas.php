<?
/*
$Author: mari $
$Revision: 1.6 $
$Date: 2004/10/19 16:59:35 $
*/
require_once("../../config.php");
echo $html_header;

?>
<form name='form1' action="ver_entregas.php" method="post">
<? 
 
 $total=$parametros['total'] or $total=$_POST['total'];
 $offset=$_POST['offset'] or $offset=0; 
 if ($_POST['ant'])
       if ($offset > 0)
         $offset=$offset -7 ;
       else $offset=0;
     elseif ($_POST['sig']) 
      $offset=$offset + 7;
  echo "<input type='button' name='cerrar' value='Cerrar' onclick='window.close()'>";
   
  echo "<div align='center'><b>SEGUIMIENTO DE PRODUCCION DE LICITACIONES EN ESTADO ORDEN DE COMPRA</b></div>";?>
  <div align='right'>
 <? 
  $lic=$offset + 1;
  $lic1=$offset + 7;
  echo  "<b>Licitaciones:</b>".$lic."-".$lic1. "/".$total; ?>
  <input type='submit' name='ant' value='<<' <?if ($offset==0) echo 'disabled'?>>
  <input type='submit' name='sig' value='>>' <? if ($offset + 7 > $total ) echo 'disabled' ?>> </div>
<? echo "<br>";
    $link=encode_link("prod_graficas.php",array("limit"=>7,"offset"=>$offset));
    echo "<div align='center'><img src='$link'  border=0 align=top></div>\n";

    
?>
 <br>
 <input type='hidden' name='offset' value='<?=$offset?>'>
 <input type='hidden' name='total' value='<?=$total?>'>
  <table align='center' bordercolor='#000000' border=1 bgcolor='#FFFFFF' cellspacing=0 cellpadding=0>
  <tr> <td color='#FFFFFF'><b>Colores de referencia:</b></td> </tr>
  <tr><td>
     <table><tr>
            <td><b>Seguimiento</b> <font size="1">(parametros modificados)</font></td>
            <td width=15 bgcolor='#919100'  height=15>&nbsp;</td>
            <td><b>Seguimiento</b> <font size="1">(parametros sin modificados)</font></td>
            <td width=15 bgcolor='#004080'  height=15>&nbsp;</td>
            </tr>
      </table>
</td></tr> 
 
 <tr><td>
     <table><tr>  
            <td><b>Realización orden de compra</b>  </td>
            <td width=15 bgcolor='#C4FFC4' bordercolor='#000000' height=15>&nbsp;</td>
            <td><b>Armado CDR </b> </td>
            <td width=15 bgcolor='#4F95FA' bordercolor='#000000' height=15>&nbsp;</td>
            <td> <b>Entrega del seguimiento </b> </td>
            <td width=15 bgcolor='#FEBCBC' bordercolor='#000000' height=15>&nbsp;</td>
            </tr>
     </table>
 </td></tr>
</table>
</form>
</body>
</html>
