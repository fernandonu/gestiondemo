<?php



variables_form_busqueda("detalle_stock_produccion");

 /*

$sql="select sum(filas.total) as total,oc.nro_orden,licitacion.id_licitacion,oc.id_moneda

      from

      compras.orden_de_compra oc

      join licitaciones.licitacion using(id_licitacion)

            join licitaciones.estado using(id_estado)

      join

          (

           select sum(fila.cantidad) as cantidad,sum(fila.precio_unitario*fila.cantidad * case when oc.id_moneda=2  then oc.valor_dolar else 1 end) as total , oc.nro_orden

               from compras.orden_de_compra oc

               join licitaciones.licitacion using(id_licitacion)

               join compras.fila using(nro_orden)

               group by nro_orden

            ) as filas using(nro_orden)

     join

	    (

	      select sum(recibidos.cantidad) as cantidad,oc.nro_orden

	       from compras.orden_de_compra oc

	       join licitaciones.licitacion using(id_licitacion)

	       join compras.fila using(nro_orden)

	       join compras.recibidos using(id_fila)

	       where recibidos.ent_rec=0

	       group by oc.nro_orden order by nro_orden

	     ) as entregados

    using (nro_orden)

    ";

 $where="

     filas.cantidad=entregados.cantidad and  oc.estado<>'n'

         and estado.nombre<>'Entregada' and

             (estado.nombre='En curso' or

              estado.nombre='Presuntamente ganada' or

              estado.nombre='Preadjudicada' or estado.nombre='Orden de compra')

    group by oc.nro_orden,licitacion.id_licitacion,oc.id_moneda

    ";

   */

   $sql="

    select  sum(ep.cantidad*fila.precio_unitario* case when oc.id_moneda=2  then oc.valor_dolar else 1 end) as total,ep.nro_orden,ep.id_licitacion

         from stock.en_produccion ep

         join compras.orden_de_compra oc using (nro_orden)

         join compras.fila using (id_producto,nro_orden)

    ";

    $where=" ep.cantidad > 0

              group by ep.nro_orden,ep.id_licitacion

            ";

$orden = array(

		"default" => "1",

        "1"  => "nro_orden",

		"2"  => "id_licitacion",

		"3"  => "total",

	);



$filtro = array(

		"ep.id_licitacion" => "ID Licitación",

		"ep.nro_orden" =>"Nro Orden",

		);



?>

<table width=95% align=center>

   <tr>

      <td align=center colspan=4>

        <?

        list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");

        $result = sql($sql_temp,"error en busqueda") or fin_pagina();

        echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";

        ?>

      </td>

   </tr>

   <tr id=ma>

       <td colspan=2 align=left>Cantidad: <?=$total?> </td>

       <td colspan=2 align=right><?=$link_pagina?></td>

   </tr>



   <tr id=mo>

      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Nro Orden </a></td>

      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>ID Licitación </a></td>

      <td>S</td>

      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Monto </a></td>

   </tr>

   <?

   $monto_dolares=$monto_pesos=0;

   for($i=0;$i<$result->recordcount();$i++){

   ?>

    <tr  <?=atrib_tr();?>>

       <?

       $link = encode_link("../../ord_compra/ord_compra.php",array("nro_orden"=>$result->fields["nro_orden"]));

       ?>

       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["nro_orden"]?></a></td>

       <?

       $link = encode_link("../../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));

       ?>

       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>

       <td align=center>$ </td>

       <td align=right><?=formato_money($result->fields["total"])?></td>

       <?

         $monto_pesos+=$result->fields["total"];

       ?>

    </tr>

   <?

   $result->movenext();

   }

   ?>

   <tr>

       <td colspan=4>

          <table width=50% align=center class=bordes>

             <tr >

                <td id=ma_sf>Monto Pesos </td>

                <td align=right><b> $ &nbsp;<?=formato_money($monto_pesos)?></b></td>

             </tr>

             <tr >

                <td id=ma_sf>Monto Dolares </td>

                <td align=right><b> U$S &nbsp;<?=formato_money($monto_dolares)?></b></td>

             </tr>

          </table>

       </td>

   </tr>

</table>