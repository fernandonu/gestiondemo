<?
/*
Author: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.1 $
$Date: 2007/03/06 21:20:51 $
*/

require_once("../../config.php");

$cantidad_mail = 4;

if ($_POST["aceptar"]){
	$db->starttrans();
	
	$sql = "delete from ordenes_mail";
	sql($sql) or fin_pagina();
	
	for($i=0;$i<$cantidad_mail;$i++){
		if ($_POST["mail_$i"]){
			$mail = $_POST["mail_$i"];
			$sql = "insert into ordenes_mail (mail) values ('$mail')";
			sql($sql) or fin_pagina();
		}
		
	}//del for
	
	$db->completetrans();
}//del if

echo $html_header;

$sql = "select  * from ordenes_mail"; 
$res = sql($sql) or fin_pagina();

?>
<form name="form1" method="POST" action="<?=$PHP_SELF?>">
 <table width="50%" align="center" class="bordes" bgcolor="<?=$bgcolor2?>">
   <tr id="mo"><td>Mail de Ordenes de Produccion</td></tr>
   <tr>
     <td>
        <table width="100%" align="center">
        <?
        $mail_1 = $res->fields["mail"];
        $res->movenext();
        ?>
         <tr>
           <td id="celda_detalle">Mail 1:</td>
           <td><input type="text" name="mail_1" value="<?=$mail_1?>"></td>
         </tr>          
        <?
        $mail_2 = $res->fields["mail"];
        $res->movenext();         
        ?>
         <tr>
           <td id="celda_detalle">Mail 2:</td>
           <td><input type="text" name="mail_2" value="<?=$mail_2?>"></td>
         </tr>          
        <?
        $mail_3 = $res->fields["mail"];
        $res->movenext();         
        ?>         
         <tr>
           <td id="celda_detalle">Mail 3:</td>
           <td><input type="text" name="mail_3" value="<?=$mail_3?>"></td>
         </tr> 
        <?
        $mail_4 = $res->fields["mail"];
        $res->movenext();         
        ?>                  
         <tr>
           <td id="celda_detalle">Mail 4:</td>
           <td><input type="text" name="mail_4" value="<?=$mail_4?>"></td>
         </tr>                                     
        </table>
     </td>
   </tr>
   <tr>
     <td align="center">
       <input type="submit" name="aceptar" value="Aceptar">
       &nbsp;
       <input type="button" name="cancelcar" value="Cancelar" onclick="window.close()">
     </td>
   </tr>
 </table>
</form>
<?
echo fin_pagina();
?>
