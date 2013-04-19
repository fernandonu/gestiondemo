<?php
/*
$Author: fernando $
$Revision: 1.4 $
$Date: 2005/03/19 16:20:42 $
*/
require_once("../../config.php");
echo $html_header;

$hora=$_POST["hora"];
$min=$_POST["min"];
$fecha=$_POST["fecha"];
$usuario=$_ses_user["name"];
$id_cobranza=$parametros["id_cobranza"];
$script_padre=$parametros["script"];
if ($_POST["aceptar"]=="Aceptar")
         {
         $hora_completa=$hora.":".$min.":00";

         if (!hora_ok($hora_completa))
                   error("Hora inválida : $hora_completa");
         if (!fechaok($fecha))
                   error("Fecha inválida");
         if (!$error)
            {
            //
            $fecha=fecha_db($fecha);
            $fecha="$fecha $hora_completa";
            $observaciones=$_POST["nuevo_comentario"];
            $sql="insert into cobranzas_llamadas (id_cobranza,fecha,usuario,observaciones)
                  values ($id_cobranza,'$fecha','$usuario','$observaciones')";
            $db->execute($sql) or die($sql."<br>".$db->errormsg());
            $script=1;
            }
        }//del post

cargar_calendario();
if (!$hora)  $hora=date("H");


if (!$min)   $min=date("i");

if (!$fecha) $fecha=date("d/m/Y");

?>
<?$link=encode_link("lic_llamadas.php",array("id_cobranza"=>$parametros["id_cobranza"],"script"=>$parametros["script"]))?>
<form name=form1 method=post action="<?=$link?>">
<?
if ($script){
 //si cargo la ventan y acepto ejecuta este script
?>
<script>
<?
  echo $script_padre;
?>
window.close();
</script>
<?
}
?>
<!--
<table width=90% border=1 cellspacing=1 cellpadding=2 bgcolor=<?=$bgcolor2?> align=center>
  <tr>
    <td id=ma>Llamadas Asociadas </td>
  </tr>
</table>
-->

<?
$sql="select * from cobranzas_llamadas
      where id_cobranza = $id_cobranza
      order by fecha DESC";
$resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$cantidad=$resultado->recordcount();
if ($cantidad>0){
?>
<table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=<?=$bgcolor2?> align=center>
  <tr>
    <td id=mo colspan=3>LLamadas Cargadas</td>
  </tr>
  <tr id=ma>
         <td width=10% align=center><b>Fecha</b></td>
         <td width=10% align=center><b>Hora</b></td>
         <td align=center><b>Observaciones</td>
 </tr>
</table>
<div style="position:relative; width:100%;height:20%; overflow:auto;">
<table width=100% border=0 cellspacing=1 cellpadding=2 bgcolor=<?=$bgcolor2?> align=center>
       <?
       for($i=0;$i<$resultado->recordcount();$i++){
       $observaciones_ant=$resultado->fields["observaciones"];
       $fecha_ant=$resultado->fields["fecha"];
       $hora_ant=substr($fecha_ant,10,9);
       $fecha_ant=fecha(substr($fecha_ant,0,10));

       ?>
         <tr>
           <td align=left width=10%><font color='red'><b> <?=$fecha_ant?></b> </font></td>
           <td align=left width=10%> <font color='red'><b><?=$hora_ant?></b> </font> </td>
           <td align=left>
           <font color='red'><b><?=$observaciones_ant?> </font> </b>
           </td>
         </tr>
       <?
       $resultado->movenext();
       }
       ?>
</table>
</div>
  <?
}
  ?>
<table width=100% border=1 cellspacing=1 cellpadding=2 bgcolor=<?=$bgcolor2?> align=center>
  <tr>
    <td id=mo>Próxima LLamada</td>
  </tr>
  <tr>
    <td >
       <table width=100% border=0 align=center>
         <tr>
           <td width=50% align=center>
              <b>Fecha</b>
              <input type=text name="fecha" value="<?=$fecha?>">
              <?echo link_calendario("fecha");?>
          </td>
          <td width=50%>
             <table width=70% align=center>
             <tr>
               <td><b>Hora</b></td>
               <td>
               <select name=hora>
               <?for($i=0;$i<=23;$i++){
                if ($hora==$i) $selected="selected";
                        else $selected="";
                ?>
               <option value='<?=$i?>' <?=$selected?> ><?=$i?></option>
               <?}?>
               </select>
               </td>
               <td>
               <b>Min.</b>
               </td>
               <td>
               <select name=min>
               <option value='0' <?if ($_POST["min"]=="0") echo "selected"?>>00</option>
               <option value='15' <?if ($_POST["min"]=="15") echo "selected"?>>15</option>
               <option value='30' <?if ($_POST["min"]=="30") echo "selected"?>>30</option>
               <option value='45' <?if ($_POST["min"]=="45") echo "selected"?>>45</option>
               </select>
                </td>
             </tr>
            </table>
           </td>
         </tr>
         <tr>
           <td align=left colspan=2><b>Observaciones</b></td>
         </tr>
         <tr>
           <td width=100% colspan=2 align=center>
           <textarea name=nuevo_comentario rows=3 style='width:100%;'><?=$_POST["nuevo_comentario"]?></textarea>
            </td>
         </tr>
         </tr>
       </table>
    </td>
  </tr>
<tr>
   <td align=center>
      <input type=submit name=aceptar value=Aceptar>
      <input type=button name=cancelar value=Cancelar onclick="window.close();">
   </td>
</tr>
</table>
</form>