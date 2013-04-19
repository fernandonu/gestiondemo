<?php
//require_once("../../../config.php");
//la variable $sql esta la pagina que requiere a esta
if ($id_moneda && $id_moneda!=-1)
        $where_moneda=" and oc.id_moneda=$id_moneda";

$sql="

select sum(orden.total/cantidad_pagos.cantidad_ordenes) as monto,orden.nro_orden,orden.razon_social
       --,orden.nro_orden
        from
        (
	select sum(op.monto*case when oc.id_moneda=2 then op.valor_dolar else 1 end) as total, oc.nro_orden,op.id_pago,razon_social
	      from
	      compras.orden_de_compra oc
          join general.proveedor using (id_proveedor)
	      join bancos.temporal_oc_internacional using (nro_orden)
	      join compras.pago_orden on (oc.nro_orden=pago_orden.nro_orden)
	      join compras.ordenes_pagos op using(id_pago) 
	      where ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
                --$where_moneda
     	group by oc.nro_orden,op.id_pago,oc.id_moneda,razon_social
        ) as  orden		
        join 
        (
        select count(pago_orden.nro_orden) as cantidad_ordenes, pago_orden.id_pago
	      from
	      compras.orden_de_compra oc
	      join bancos.temporal_oc_internacional using (nro_orden)
	      join compras.pago_orden on (oc.nro_orden=pago_orden.nro_orden)
	      join compras.ordenes_pagos op using(id_pago) 
	      where ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
	   group by pago_orden.id_pago
        ) as cantidad_pagos using(id_pago)

 ";
$where=" group by orden.nro_orden,orden.razon_social";
variables_form_busqueda("detalle_oc_internacional");



$orden = array(
		"default" => "2",
        "1"  => "nro_orden",
        "2"  => "razon_social",
		"3" => "monto",
	);

$filtro = array(
		"nro_orden" => "Nro Orden",
		"razon_social" => "Proveedor",


	);

?>
<table width=95% align=center>
   <tr>
      <td align=center colspan=5 align=center width=100% bgcolor=white>
        <table width=100% align=center >
          <tr>
            <td width=50% align=center>
              <?
                list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
                $result = sql($sql_temp,"error en busqueda") or fin_pagina();
               ?>
            </td>
            <td align=center>
              Moneda
            </td>
            <td align=center>
              <?
              $sql=" select * from moneda";
              $res_moneda=sql($sql) or fin_pagina();
              ?>
              <select name=id_moneda>
                <option selected value=-1>Todas</option>
                 <?
                 for($i=0;$i<$res_moneda->recordcount();$i++){
                    if ($id_moneda==$res_moneda->fields["id_moneda"])
                          $selected=" selected";
                          else
                          $selected=" ";
                 ?>
                  <option value="<?=$res_moneda->fields["id_moneda"]?>" <?=$selected?>><?=$res_moneda->fields["nombre"]?></option>
                 <?
                 $res_moneda->movenext();
                 }
                 ?>
              </select>
            </td>
            <td>
              <input type=submit name=form_busqueda value='Buscar'>
            </td>
         </tr>
        </table>
      </td>
   </tr>
   <tr id=ma>
       <td colspan=2 align=left>Cantidad: <?=$total?> </td>
       <td colspan=3 align=right><?=$link_pagina?></td>
   </tr>

   <tr id=mo>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Nro Orden </a></td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Proveedor </a></td>
      <td>S</td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Monto </a></td>
   </tr>
   <?
   $monto_dolares=$monto_pesos=0;
   for($i=0;$i<$result->recordcount();$i++){
   ?>
    <tr  <?=atrib_tr();?>>
       <?
       $link = encode_link("../../ord_compra/ord_compra.php",array("nro_orden"=>$result->fields["nro_orden"]));
       ?>
       <td align=center><?=$result->fields["nro_orden"]?></td>
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["razon_social"]?></a></td>       
       <td align=center>$</td>
       <td align=right><?=formato_money($result->fields["monto"])?></td>
       <?
         $monto_pesos+=$result->fields["monto"];
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