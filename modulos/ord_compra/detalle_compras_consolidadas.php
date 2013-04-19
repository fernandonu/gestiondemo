<?

/*

Autor: Fernando
Creado: jueves 13/05/04



MODIFICADA POR

$Author: marco_canderle $

$Revision: 1.30 $

$Date: 2006/07/18 14:38:01 $

*/

require_once("../../config.php");

require_once("funciones_compras_consolidadas.php");

$color_checked="#FF8080";



$id_producto=$parametros["id_producto"];

$estado_licitacion=$parametros["estado_licitacion"];

$desc_gral=$parametros["desc_gral"];

$fecha_menor=fecha_db($parametros["fecha_menor"]);

$fecha_mayor=fecha_db($parametros["fecha_mayor"]);











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

    /*     default:

               $estado_renglon=3;

               break;*/

        }//del switch

if (strlen($estado_licitacion)){

      $where_estado="  id_estado=$estado_licitacion and ";

      }

      else {

       $where_estado="";

      }



if ($parametros["chk_fecha"] && $estado_renglon==3) $where_fechas=" and (vence_oc >='$fecha_menor' and vence_oc<='$fecha_mayor')";

if ($parametros["chk_fecha"] && $estado_renglon!=3) $where_fechas=" and (fecha_entrega >='$fecha_menor' and fecha_entrega<='$fecha_mayor')";

if ($estado_renglon){

         if ($estado_renglon==3) {

               //es que es orden de compra (deafaul)

             $sql="

              select sum(rp.cantidad*pp.cantidad) as cantidad_producto,l.id_licitacion,

                     sl.vence_oc as fecha_entrega,sl.id_subir,precio_presupuesto.monto_unitario,

                     entrega_estimada.id_entrega_estimada

                      from licitaciones.licitacion l

                      join licitaciones.subido_lic_oc sl using(id_licitacion)

                      join licitaciones.entrega_estimada using(id_entrega_estimada)

                      join licitaciones.licitacion_presupuesto_new lp  ON (entrega_estimada.id_entrega_estimada=lp.id_entrega_estimada)

                      join licitaciones.renglon_presupuesto_new rp using (id_licitacion_prop)

                      join licitaciones.producto_presupuesto_new pp  using(id_renglon_prop)

                      join general.productos p using(id_producto)

                      left join

                           (

                            select monto_unitario,id_producto_presupuesto

 				                    from licitaciones.producto_proveedor_new

                                    where activo=1

                            ) as precio_presupuesto using(id_producto_presupuesto)



                      WHERE  l.es_presupuesto=0 and borrada='f'

                              and entrega_estimada.finalizada=0

                              and entrega_estimada.flag_compras_consolidadas=1

                              AND l.id_estado=7

                             and pp.id_producto=$id_producto

                      group by l.id_licitacion,sl.vence_oc,sl.id_subir,

                               precio_presupuesto.monto_unitario,entrega_estimada.id_entrega_estimada

                      order by fecha_entrega ASC

                      ";

                     }

                      else {

                        //quiere otro estado que es preadjudicada o presuntamente ganada

                        $sql=" select * from

                                           (

                                            select id_licitacion,fecha_entrega from

                                            licitacion

                                            where $where_estado  es_presupuesto=0 and borrada='f' $where_fechas

                                            ) as l

                                            join

                                            (

                                             select sum(renglon.cantidad*producto.cantidad) as cantidad_producto,id_licitacion

                                             from renglon

                                             join producto using(id_renglon)

                                             join historial_estados using (id_renglon)

                                             where (id_estado_renglon=$estado_renglon and activo=1 and id_producto=$id_producto)

                                             group by id_licitacion

                                             ) as p  using (id_licitacion)

                                             order by fecha_entrega";

                              }

  }

 else {

  //si no tiene un estado el renglon busco en el estado de la licitacion

      $sql=" select * from (

             select id_licitacion,fecha_entrega from

             licitaciones.licitacion where $where_estado  es_presupuesto=0 and borrada='f'

             ) as l

             join

             (

             select sum(renglon.cantidad*producto.cantidad) as cantidad_producto, id_licitacion,id_renglon

             from licitaciones.renglon join licitaciones.producto using(id_renglon)

             where codigo_renglon not ilike '%alt%' and  id_producto=$id_producto group by id_licitacion,id_renglon

             ) as r using (id_licitacion)

             order by fecha_entrega

            ";

   }

//die($sql);



$result=sql($sql) or fin_pagina();



$sql_dolar=" select valor from dolar_general";

$res_dolar=sql($sql_dolar) or fin_pagina();

$valor_dolar=$res_dolar->fields["valor"];

//echo "Valor Dolar".$valor_dolar;





$sql="select id_tipo_prod from general.productos where id_producto=$id_producto ";

$res=sql($sql) or fin_pagina();

$id_tipo_prod=$res->fields["id_tipo_prod"];



echo $html_header;

$ordenes_compra=array();

?>

<script language="JavaScript" src="../../lib/NumberFormat150.js"></script>

<script>



//funcion que me calcula los montos a considerar

function calcular_subtotal(){

var suma,sent,id;

suma=0;

if (typeof(document.form1.chk.length)!='undefined'){

                           for(i=0;i<document.form1.chk.length;i++){

                                  if (document.form1.chk[i].checked){

                                       suma+=parseInt(document.form1.considerar[i].value);

                                       sent="document.all.fila_"+i;

                                       id=eval(sent);

                                       id.style.background="<?=$color_checked?>";

                                  }

                                   else{

                                       sent="document.all.fila_"+i;

                                       id=eval(sent);

                                       id.style.background="<?=$bgcolor3?>";

                                     }

                             }



                      }//del then

                       else{



                          if (document.form1.chk.checked){

                                       suma+=parseInt(document.form1.considerar.value);

                                       sent="document.all.fila_0";

                                       id=eval(sent);

                                       id.style.background="<?=$color_checked?>";

                                  }

                                   else{

                                       sent="document.all.fila_0";

                                       id=eval(sent);

                                       id.style.background="<?=$bgcolor3?>";

                                     }

                 }

 document.form1.cantidad_considerar.value=suma;

}//de la function



</script>

<form name=form1 method=post action='detalle_compras_consolidadas.php'>

<input type=hidden name=valor_dolar value="<?=$valor_dolar?>">



<table width=100% align=center class=bordes bgcolor=<?=$bgcolor2?>>

  <tr id=mo><td colspan=3>Tabla de Compra Consolidada (TCC)</td></tr>

  <tr id=ma>

     <td width=30%>Descripción del Producto</td>

     <td>Datos</td>

  </tr>

  <tr>

  <td align=center valign=top>



    <table width=100% >

      <tr>

         <td colspan=2 align=center><font color=red><b><?=$desc_gral?></b></font></td>

      </tr>

      <tr id=ma><td colspan=2 >Cantidades</td></tr>

      <tr bgcolor=#E0E0E0>

         <td align=left ><b>Cantidad:</b></td>

         <td align=right><input type=text name=cantidad_total size=4 value=0 class="text_3"></td>

      </tr>

      <tr bgcolor=#E0E0E0>

        <td align=left><b>Cantidad a Comprar</b></td>

        <td align=right><input type=text name=cantidad_comprar size=4 value=0 class="text_3"></td>

      </tr>



      <tr bgcolor=#E0E0E0>

        <td align=left><b>Cantidad a Considerar</b></td>

        <td align=right><input type=text name=cantidad_considerar size=4 value=0 class="text_3"></td>

      </tr>



      <tr bgcolor=#E0E0E0>

        <td align=left><b>Cantidad en Stock</b></td>

        <td align=right><input type=text name=cantidad_stock size=4 value=0 class="text_3"></td>

      </tr>

     <tr id=ma><td colspan=2 >Montos</td></tr>

      <tr bgcolor=#E0E0E0>

        <td align=left><b>Monto Comprado</b></td>

        <td align=right><input type=text name=monto_comprado value=0 size=10 class="text_3"></td>

      </tr>

      <tr bgcolor=#E0E0E0>

        <td align=left><b>Monto a Comprar</b></td>

        <td align=right><input type=text name=monto_a_comprar value=0 size=10 class="text_3"></td>

      </tr>

  </table>



  </td>

  <td widht=100% align=center valign=top bgcolor="<?=$bgcolor3?>" >

      <table width=100% align=center  cellpadding=0 cellspacing=0 border=1>

       <tr id=ma>

             <td width=1%>&nbsp</td>

             <td width=15%>Id</td>

             <td width=30% title='cantidad por presupuesto'>Cant</td>

             <td width=30% title='cantidad comprada'>OC</td>

             <td width=30% title='cantidad a comprar'>Cant. a Comprar</td>

             <td >Fecha</td>

             <td width=3%>Considerar</td>

       </tr>

      <input type=hidden name=cantidad value=<?=$result->recordcount();?>>

      <?

      $monto_comprado=$total_a_comprar=$cantidad_oc=$cantidad_pm=0;

      $total=$total_a_comprar=0;

      $index=0;

      $cantidad_comprada_pm=$cantidad_comprada_pm_acumulada=0;

      $cantidad=0;

      $datos_generales=genera_arreglo($result);



      for($i=0;$i<sizeof($datos_generales);$i++){



          $cantidad_producto=$datos_generales[$i]["cantidad_producto"];

          $acumulado += $datos_generales[$i]["cantidad_producto"];



          $datos_oc = genera_descripcion_oc($datos_generales[$i]["id_licitacion"],$id_producto);

          $datos_pm = genera_descripcion_pm($datos_generales[$i]["id_licitacion"],$id_tipo_prod);





          $cantidad_oc_acumulado += $datos_oc["cantidad"];

          $cantidad_pm_acumulado += $datos_pm["cantidad"];

          $cantidad_oc = $datos_oc["cantidad"];

          $cantidad_pm = $datos_pm["cantidad"];





          if ($cantidad_oc == $cantidad_producto)

                           {$cantidad=$cantidad_oc;}

                           elseif ($cantidad_pm == $cantidad_producto)

                                  {$cantidad=$cantidad_pm;}

                                  elseif ($cantidad_oc + $cantidad_pm==$cantidad_producto)

                                     {$cantidad=$cantidad_oc + $cantidad_pm;}

                                     else {$cantidad=$cantidad_oc + $cantidad_pm;}





          //cantidad_compra_pm y acumulada es el que me dice lo que tengo realmente en compras

          //o en pm

          $cantidad_comprada_pm = $cantidad;

          $cantidad_comprada_pm_acumulada += $cantidad;

          $cantidad_a_comprar=$cantidad_comprada_pm - $cantidad;

          if ($cantidad == $cantidad_producto)

                                              $bgcolor="bgcolor='$color_checked'";

                                              else

                                              $bgcolor="";



          ?>

          <tr <?=$bgcolor?> id=fila_<?=$i?>>

             <td>

             <input class='estilos_check' type=checkbox name=chk value="<?=$cantidad?>"  onclick="calcular_subtotal();" >

             </td>

              <td align=center valign=top >

                 <? $link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));?>

                 <a href='<?=$link?>'  target="_blank"><?=$datos_generales[$i]["id_licitacion"]?></a>

              </td>

              <td align=center valign=top>

                  <b><?=$cantidad_producto?></b>

              </td>

              <td>

                 <table width=100% align=center>

                 <?

                   genera_html($datos_oc,$i,1);

                   genera_html($datos_pm,$i,0);

                 ?>

                </table>

          </td>

          <td align=center valign=top><b><?=$cantidad_a_comprar?></b></td>

          <td align=center valign=top><b><?=fecha($datos_generales[$i]["fecha_entrega"])?></b></td>

          <td align=center valign=top><input type=text size=5 name=considerar value="<?=$datos_generales[$i]["cantidad_producto"]?>"   onblur="calcular_subtotal()"></td>

        </tr>

      <?

      }

      ?>



      </table>

    </td>

  </tr>

  <tr>

  <td colspan=4 align=left>

       <table width=200>

         <tr>

           <td width=20 bgcolor=#FFFFC0>&nbsp</td>

           <td><b>Pedido de Material</b></td>

         </tr>

       </table>

  </td>

  </tr>

</table>

<br><br>

<?

  $sql="

       select * from (

       select pe.descripcion,en_stock.cant_reservada,en_stock.cant_disp,en_stock.cant_a_confirmar,

              (en_stock.cant_reservada + en_stock.cant_disp + en_stock.cant_a_confirmar) as cant_total,

               depositos.nombre

        from

        stock.en_stock

        join general.producto_especifico pe using (id_prod_esp)

        join general.depositos using(id_deposito)

        where pe.id_tipo_prod=$id_tipo_prod

        ) as p

        where cant_total>0 order by p.nombre ASC";

  $res=sql($sql) or fin_pagina();

?>

<table class=bordes width=90% align=center>

   <tr id=mo><td>Mismos Tipos de Productos en Stock</td></tr>

   <tr>

     <td>

     <table width=100% align=center>

        <tr id=ma>

        <td width=10%>Deposito</td>

        <td width=20%>Producto</td>

        <td width=10%>Cant Disp</td>

        <td width=10%>Cant Res.</td>
        <td width=10%>Cant a Confirmar.</td>

        <td width=10%>Total</td>

       </tr>

   <?

   $cantidad_stock=0;

   for($i=0;$i<$res->recordcount();$i++){

   ?>

       <tr <?=atrib_tr()?>>

         <td><?=$res->fields["nombre"]?></td>

         <td><?=$res->fields["descripcion"]?></td>

         <td align=right><?=$res->fields["cant_disp"]?></td>

         <td align=right><?=$res->fields["cant_reservada"]?></td>
         <td align=right><?=$res->fields["cant_a_confirmar"]?></td>

         <td align=right><?=$res->fields["cant_total"]?></td>

        </tr>

   <?

   $cantidad_stock+=$res->fields["cant_total"];

   $res->movenext();

   }

   ?>

     </table>

     </td>

  </tr>

</table>

<table width=30% align=Center>

  <tr>

      <td width=100% align=center>

        <input type=button name=cerrar value=Cerrar onclick="window.close()">

      </td>

  </tr>

</table>

</form>



<script>

   //modifico las cantidades a mostrar

   document.form1.cantidad_total.value=<?=$acumulado?>;

   document.form1.cantidad_comprar.value=<?=$acumulado-$cantidad_comprada_pm_acumulada?>;

   document.form1.cantidad_stock.value=<?=$cantidad_stock?>;

   document.form1.monto_comprado.value=<?=$monto_comprado?>;

</script>

<?=fin_pagina()?>