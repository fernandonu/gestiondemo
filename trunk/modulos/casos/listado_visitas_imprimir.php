<?php
/*
$Author: fernando $
$Revision: 1.3 $
$Date: 2005/05/12 18:44:11 $
*/
require_once("../../config.php");
$sql=$parametros["sql"];
$resultado=sql($sql) or fin_pagina();
?>
<script>
function imprimir_listado(){

 document.all.imprimir.style.visibility="hidden";
 window.print();
 window.close();

}
</script>
<form name=form1 metho=post>
   <table width=100% align=center class=bordes>
    <tr>
      <td>
       <input type=button name=imprimir value=Imprimir onclick="imprimir_listado();">
       &nbsp;
       <input type=button name=cerrar value=Cerrar onclick="window.close()">
      </td>
    </tr>
     <tr>
       <td>
         <table width="100%" align=center>
           <tr>
              <td width=10%><b>Fecha:</b></td>
              <td align=left><b>&nbsp;<?=date("d/m/Y")?></b></td>
           </tr>
           <tr>
             <td><b>Usuario:</b></td>
             <td align=left>&nbsp;<b><?=$_ses_user["name"]?></b></td>
           </tr>
         </table>
       </td>
     </tr>
     <tr>
       <td>
           <table width=100% align=center border=1 cellspacing=1 cellpading=1>
             <tr>
               <td colspan=6 width=100% align=center><b>Listado de Visitas</b></td>
             </tr>
             <tr>
                <td width=5% align=center><b>Id</b></td>
                <td width=10% align=center><b>Fecha</b></td>
                <td width=10% align=center><b>Hora</b></td>
                <td width=15% align=center><b>Técnico</b></td>
                <td width=15% align=center><b>Nro Caso</b></td>
                <td align=center><b>Dirección</b></td>
              </tr>
            <?
            $cantidad_visitas=$resultado->recordcount();

            for ($i=0;$i<$cantidad_visitas;$i++){

            $fecha=fecha($resultado->fields["fecha_visita"]);
            $hora=substr($resultado->fields["hora"],0,5);

            $cant_modulos=$resultado->fields["cant_modulos"];
            $sum=($cant_modulos) * 30;
            $horas=split(":",$hora);
            $hora_fin=date("H:i",mktime($horas[0],$horas[1]+$sum,'00'));



            $link=encode_link("config.php",array());
            ?>
              <tr>
              <td><?=$resultado->fields["id_visitas_casos"]?></td>
              <td align=center><?=$fecha?></td>
              <td align=center><?=$hora." -- ".$hora_fin?></td>
              <td>&nbsp;<?=$resultado->fields["tecnico"]?></td>
              <td align=center><?=$resultado->fields["nrocaso"]?></td>
              <td>&nbsp;<?=$resultado->fields["direccion"]?></td>
              </tr>
            <?
            $resultado->movenext();
            }
            ?>
           </table>
       </td>
     </tr>
   </table>
</form>