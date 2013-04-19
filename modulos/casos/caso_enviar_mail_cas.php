<?php
/*
$Author: fernando $
$Revision: 1.10 $
$Date: 2005/05/13 15:16:03 $
*/
require_once("../../config.php");


if ($_POST["aceptar"]) {
    $sql="select licitaciones.unir_texto(mail || text(',' ) ) as direcciones
                 from casos.cas_ate where activo=1";
    $datos=sql($sql) or fin_pagina();
    $bcc=$datos->fields["direcciones"];
    $bcc=ereg_replace(";",",",$bcc);
    //echo $bcc;

    $contenido=$_POST["contenido"];
    $asunto=$_POST["asunto"];
    $return=enviar_mail("serviciotecnico@coradir.com.ar",$asunto,$contenido,'','','',0,$bcc);
    if ($return) Aviso("Se ha enviado el mail correspondiente a los CAS");
            else Aviso("Error:No se envio el mail correspondiente a los CAS");

 }
echo $html_header;
?>
<script>
   function control_datos(){
     if (document.form1.asunto.value=="")
         {
         alert("Debe ingresar un asunto");
         return false;
         }
     if (document.form1.contenido.value=="")
         {
         alert("Debe ingresar un Contenido");
         return false;
         }
return true;
   }
</script>
<form name=form1 method=post>
   <table width=100% align=center class=bordes>
      <tr>
         <td id=mo>Escriba el texto a enviar a todos los C.A.S</td>
      </tr>
      <tr>
         <td><b>Asunto: </b></td>
      </tr>
      <tr>
         <td><input type=text name=asunto value="<?=$asunto?>" size=160> </td>
      </tr>

      <tr>
         <td><b>Contenido: </b></td>
      </tr>
      <tr>
         <td align=center>
           <textarea name=contenido rows=30 style="width:100%"><?=$contenido?></textarea>
         </td>
      </tr>
      <tr>
        <td align=center>
         <input type=submit name=aceptar value=Aceptar onclick="return control_datos()">
         &nbsp;
         <input type=button name=cancelar value=Cancelar onclick="window.close()">
        </td>
      </tr>
   </table>
</form>