<?php

/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2005/09/22 17:35:53 $
*/
require_once ("../../config.php");

$id_producto=$parametros["id_producto"]  or $id_producto=$_POST["id_producto"];
$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$_POST["id_licitacion"];
$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];


$sql="select desc_gral,marca,modelo from productos where id_producto=$id_producto";
$res=sql($sql) or fin_pagina();

$marca=$res->fields["marca"];
$modelo=$res->fields["modelo"];
$desc_gral=$res->fields["desc_gral"];



$sql=" select   cs.estado,cs.fecha_modif,cs.usuario,
                cs.comentario,cs.estado,
                descuento.cant_desc,
                sp.nro_orden,sp.id_licitacion
         from stock.control_stock cs
         join stock.descuento using (id_control_stock)
         join stock.en_produccion sp using (id_en_produccion) 
         where  (estado='oc' or estado='a') and
                 sp.id_producto=$id_producto 
                 and sp.id_licitacion=$id_licitacion 
                 and sp.nro_orden=$nro_orden";
$result=sql($sql) or fin_pagina();


echo $html_header;  
?>
<form name=form1 method=post>
<input type=hidden name=id_producto value=<?=$id_producto?>>
<input type=hidden name=id_licitacion value=<?=$id_licitacion?>>
<input type=hidden name=nro_orden value=<?=$nro_orden?>>
  <table width=90% align=center class=bordes>
      <tr id=mo>
         <td>Detalle Historial de Stock de Producción</td>
      </tr>
      <tr>
         <td width=100%>

                <table width = 100% align = center>
                    <tr>
                        <td id=ma_sf width=20%>Descripción:</td>
                        <td><b><?= $desc_gral ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf >Marca:</td>
                        <td> <b><?= $marca ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf >Modelo</td>
                        <td><b><?= $modelo ?></b></td>
                    </tr>
             </table>
         </td>
      </tr>
      <tr>
         <td>
           <table width=100% align=center>
              <tr id=mo>
                 <td>OC</td>
                 <td>Id</td>
                 <td>Cant.</td>
                 <td>Estado</td>
                 <td>Comentario</td>
                 <td>Fecha</td>
                 <td>Usuario</td>
                
              </tr>
           <?
           for($i=0;$i<$result->recordcount();$i++){
           ?>
           <tr <?=atrib_tr()?>>
               <td width=6%><?=$result->fields["nro_orden"]?></td>
               <td width=6%><?=$result->fields["id_licitacion"]?></td>
               <td width=5%><?=$result->fields["cant_desc"]?></td>
               <td><?=$result->fields["estado"]?></td>
               <td width=50%><?=$result->fields["comentario"]?></td>
               <td width=15% align=center><?=fecha($result->fields["fecha_modif"])?></td>
               <td align=right><?=$result->fields["usuario"]?></td>
           </tr>    
           <?    
           $result->movenext();
           }
           ?>   
              
           </table>
         </td>
      </tr>
        <tr>
            <td align = center>
                <input type = button value = Volver name = volver onclick="location.href='./stock_produccion.php';">
            </td>
        </tr>
      
  </table>
</form>
<?
echo fin_pagina();
?>