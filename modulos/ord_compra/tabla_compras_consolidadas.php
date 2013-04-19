<?
/*
Autor: Fernando
Creado: jueves 13/05/04

MODIFICADA POR
$Author: fernando $
$Revision: 1.4 $
$Date: 2005/03/07 17:42:10 $
*/
require_once("../../config.php");

$id_producto=$parametros["id_producto"];
$estado_licitacion=$parametros["estado_licitacion"];
$desc_gral=$parametros["desc_gral"];

//es para hacer una relacion uno a uno entre el estado del renglon y el
//estado de la licitacion
switch ($estado_licitacion)
        {
         case 2:
                $estado_renglon=1;//presuntamenta ganada
                break;
         case 3:
                $estado_renglon=2;//preadjudicada
                break;
         case 7:
               $estado_renglon=3; // orden de compra
               break;
         default:
               $estado_renglon=3;
               break;
        }//del switch



if ($estado_renglon==3) {
    //es que es orden de compra (deafaul)
  $sql=" select * from
        (
         select id_licitacion from licitacion where id_estado=$estado_licitacion
         ) as l
         join
         (
         select sum(renglones_oc.cantidad*producto.cantidad) as cantidad_producto,vence_oc as fecha_entrega,id_subir,
                id_licitacion
                from  licitaciones.subido_lic_oc
                join licitaciones.renglones_oc using (id_subir)
                join producto using(id_renglon)
                join historial_estados using (id_renglon)
                where (id_estado_renglon=$estado_renglon and activo=1) and id_producto=$id_producto
                group by id_licitacion,fecha_entrega,id_subir
           ) as p
        using (id_licitacion) order by id_licitacion,fecha_entrega
        ";
}
else {
  //quiere otro estado que es preadjudicada o presuntamente ganada
  $sql=" select * from
        (
        select id_licitacion,fecha_entrega from licitacion where id_estado=$estado_licitacion
        ) as l
        join
        (
        select sum(renglon.cantidad*producto.cantidad) as cantidad_producto,
               id_licitacion from renglon
               join producto using(id_renglon)
               join historial_estados using (id_renglon)
               where (id_estado_renglon=$estado_renglon and activo=1 and id_producto=$id_producto)
               group by id_licitacion
        ) as p
        using (id_licitacion) order by id_licitacion,fecha_entrega
        ";
}
$result=sql($sql) or fin_pagina();
echo $html_header;
?>
<table width=100% align=center class=bordes bgcolor=<?=$bgcolor2?>>
  <tr id=mo><td colspan=3>Tabla de Compra Consolidada (TCC)</td></tr>
  <tr id=ma>
     <td width=25%>Descripción del Producto</td>
     <td>Licitaciones </td>
     <td width=15%>Total</td>
  </tr>
  <tr>
  <td align=center valgin=middle>
  <font color=red>
  <b><?=$desc_gral?></b>
  </font>
  </td>
   <td widht=100% bgcolor=<?=$bgcolor3?>>

      <table width=100% align=center  cellpadding=0 cellspacing=0 border=1>
       <tr id=ma>
             <td>Id Licitación</td>
             <td>Cantidad</td>
             <td>Fecha</td>
       </tr>

      <?
      for ($i=0;!$result->EOF;$i++){
      //for ($i=0;$i<1;$i++){
       $id_licitacion=$result->fields["id_licitacion"];
            $y=0;
            $datos=array();
            do {
               $datos[$y]=array("cantidad"=>$result->fields["cantidad_producto"],"fecha_entrega"=>$result->fields["fecha_entrega"],"id_subir"=>$result->fields["id_subir"]);
               $result->movenext();
               $y++;
               }
            while($result->fields['id_licitacion']==$id_licitacion && !$result->EOF);
            //del do while
      ?>
       <tr>
       <?$link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));?>
       <td  align=center rowspan='<?=$y+1?>'><a href='<?=$link?>' target="_blank">
       <b><?=$id_licitacion?></b></a></td></tr>
       <?
       for($j=0;$j<$y;$j++){
       ?>
        <tr>
                  <td align=center><b>
                  <?
                  $total+=$datos[$j]["cantidad"];
                  if ($datos[$j]["cantidad"]) echo $datos[$j]["cantidad"];
                                         else echo "&nbsp;";
                  ?></b>
                  </td>
                   <td align=center><b>
                   <?
                   if ($estado_renglon=3){
                    $link=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$datos[$j]["id_subir"],"solo_lectura"=>1));
                   ?>
                   <a href='<?=$link?>' target="_blank">
                   <?
                   }
                   if ($datos[$j]["fecha_entrega"]) echo fecha($datos[$j]["fecha_entrega"]);
                                               else echo "&nbsp;";
                   if ($estado_renglon=3){?></a> <?}?>
                   </b></td>
                   <?}?>
      </tr>
      <?
      $result->movenext();
      }
      ?>

      </table>

    </td>
    <td align=center valign=middle>
    <font color=red size=2>
    <b><?=$total?></b>
    </font>
    </td>
  </tr>
  <tr><td colspan=4 align=center>
  <input type=button name=cerrar value='Cerrar' onclick='window.close();'>
  </td></tr>
</table>
<?fin_pagina()?>