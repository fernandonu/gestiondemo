<?php
/*
$Author: gonzalo $
$Revision: 1.3 $
$Date: 2004/11/08 15:03:22 $
*/

require_once("../../config.php");
echo $html_header;
$id_licitacion_prop=$parametros["id_licitacion_pro"];

$sql="select id_renglon_prop,titulo from renglon_presupuesto_new
      join renglon using (id_renglon)
      where id_licitacion_prop=$id_licitacion_prop";
$renglones=sql($sql) or fin_pagina();

?>
<table width=100% align=center border=1 cellpading=0 cellspacing=0 bgcolor=<?=$bgcolor2?>>
 <tr id=mo>
    <td>Productos del Presupuesto</td>
 </tr>
 <tr>
    <td>
        <table width=100% align=center>
        <tr id=mo>
           <td width=35%> Renglon   </td>
           <td> Productos </td>
        </tr>
       <?
       for($i=0;$i<$renglones->recordcount();$i++){
          $id_renglon_prop=$renglones->fields["id_renglon_prop"];
       ?>
       <tr>
          <td valign=top>
            <font color=red size=2>
            <?=$renglones->fields["titulo"]?>
            </font>
          </td>
          <td valign=top>
             <table width=100% align=Center bgcolor=<?=$bgcolor3 ?>>
               <tr id=ma_sf>
                  <td width=70% align=center>Descripción </td>
                  <td width=15% align=center title="Orden de Compra">OC</td>
                  <td width=15% align=rigth title="Cantidad en la Orden de Compra">Cant. OC</td>
               </tr>
               <?
              $sql="select distinct orden_de_compra.nro_orden,desc_orig||' '||pp.desc_adic as desc_nueva,fila.cantidad from compras.oc_pp
                     join licitaciones.producto_presupuesto_new pp using(id_producto_presupuesto)
                     join compras.orden_de_compra using(nro_orden)
                     join compras.fila on fila.nro_orden=orden_de_compra.nro_orden and fila.id_producto = pp.id_producto
                     where id_renglon_prop = $id_renglon_prop and orden_de_compra.estado<>'n'
                     order by desc_nueva
                     ";
              $productos=sql($sql)  or fin_pagina();
              if ($productos->recordcount()) {
                 for($j=0;$j<$productos->recordcount();$j++){
                  $link=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$productos->fields['nro_orden']));
                 ?>
                 <tr>
                   <td><?=$productos->fields["desc_nueva"]?></td>
                   <a href=<?=$link?> target="_blank">
                   <td align=center>
                   <font color=blue>
                   <?=$productos->fields["nro_orden"]?>
                   </font>
                   </td>
                   <td align=right>
                   <?=$productos->fields["cantidad"]?>
                   </td>
                   </a>
                </tr>
                 <?
                 $productos->movenext();
                 }
              }
              else{
              ?>
              <tr><td colspan=2 align=center>No hay productos </td></tr>
              <?
              }
               ?>
              </table>
          </td>
       </tr>
       <?
       $renglones->movenext();
       }//del for
       ?>
     </table>
    </td>
 </tr>
 <tr>
    <td align=center>
       <input type=button name=Cerrar value=Cerrar onclick="window.close()">
    </td>
 </tr>
</table>