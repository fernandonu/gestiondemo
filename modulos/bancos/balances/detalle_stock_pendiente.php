<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2006/04/17 23:02:05 $
*/

$sql=" select sum(log_rec_ent.cant) as cantidad,sum(log_rec_ent.cant*fila.precio_unitario) as total ,
             orden_de_compra.nro_orden,producto_especifico.descripcion,fila.precio_unitario,moneda.simbolo
             from 
             compras.orden_de_compra 
             join moneda using (id_moneda)
             join compras.fila  using (nro_orden)
		     join compras.recibido_entregado using(id_fila)
             
		     join compras.log_rec_ent using(id_recibido)
		     join general.producto_especifico on log_rec_ent.id_prod_esp=producto_especifico.id_prod_esp
             ";
        
$where  =" log_rec_ent.recepcion_confirmada=0
                group by orden_de_compra.nro_orden,producto_especifico.descripcion,fila.precio_unitario ,moneda.simbolo
             ";   


$orden = array(
		"default" => "1",
        "1"  => "orden_de_compra.nro_orden",
        "2"  => "descripcion",
        "3"  => "cantidad" ,
        "4"  => "precio_unitario",
        "5"  => "total"
	);

 
$filtro = array(
		"nro_orden" => "Nro Orden",
        "descripcion" => "Producto",
		
	);



variables_form_busqueda("detalle_stock_pendiente");   
?>
<table width=95% align=center>
   <tr>
      <td align=center colspan=6 align=center width=100% bgcolor=white>
        <table width=100% align=center >
          <tr>
            <td width=50% align=center>
              <?
                list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,0);
                
                $res = sql($sql,"error en busqueda") or fin_pagina();
               
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
       <td colspan=4 align=right><?=$link_pagina?></td>
   </tr>
   <tr id=mo>
     <td width=10% align=center><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Nro Orden </a></td>
     <td width=50%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Producto </a></td>
     <td width=10% align=center> M.</td> 
     <td width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Cantidad </a></td>     
     <td width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Precio U. </a></td>          
     <td width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Precio T. </a></td>     
   </tr>
   <?
    for($i=0;$i<$res->recordcount();$i++){
   ?>
    <tr <?=atrib_tr()?>>
     <td><?=$res->fields["nro_orden"]?></td>
     <td><?=$res->fields["descripcion"]?></td>
     <td align=center><?=$res->fields["simbolo"]?></td>
     <td align=right><?=$res->fields["cantidad"]?></td>
     <td align=right><?=formato_money($res->fields["precio_unitario"])?></td>
     <td align=right><?=formato_money($res->fields["total"])?></td>
   </tr>  
   <?
    $res->movenext();
    }
    ?>
   
</table>   