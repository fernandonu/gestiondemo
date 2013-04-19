<?php
/*
$Author: fernando $
$Revision: 1.4 $
$Date: 2006/05/30 21:35:29 $
*/

require_once ("../../../config.php");


$id_detalle_balance_historial=$parametros["id_detalle_balance_historial"] or $id_detalle_balance_historial=$_POST["id_detalle_balance_historial"];
$titulo = $_POST["titulo"] or $titulo = $parametros["titulo"];

variables_form_busqueda("detalle_items_balance");


$sql="select * from items_detalle_balance";

$where=" id_detalle_balance_historial=$id_detalle_balance_historial";

$orden = array(
		"default" => "1",
        "default_up" => 0, 
        "1"  => "nro_orden",
		"2"  => "id_licitacion",
		"3"  => "id_cobranza",
        "4"  => "descripcion",
        "5"  => "cantidad",
        "6"  => "moneda",
        "7"  => "monto",
		"8"  => "nro_factura",
	);

$filtro = array(
		"nro_orden" => "Nro Orden",
		"id_licitacion" =>"ID Licitacion",
        "id_cobranza" => "Cobranza",
        "descripcion" => "Descripcion",
        "cantidad" => "Cantidad",
        "monto" => "Monto",
		"nro_factura" => "Nro Factura",
		);     
        
$link_tmp =array (
        "id_detalle_balance_historial"=>$id_detalle_balance_historial
        );
        
echo $html_header;
$itemspp=10000;
?>
<form name=form1 method=post action=detalle_items_balance.php>
<input type=hidden name=id_detalle_balance_historial value="<?=$id_detalle_balance_historial?>">
   <table width=100% align=center class=bordes>
     <tr id=mo>
       <td>Detalle Historial <?=$titulo?></td>
     </tr>
     <tr>
       <td align=center>
       <?
       list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
       $result = sql($sql_temp,"error en busqueda") or fin_pagina();
       ?>
       <input type=submit name=form_busqueda value='Buscar'>
       &nbsp;
       <input type=button name=cerrar value="Cerrar" onclick="window.close()"> 
       </td>
     </tr>
   <tr id=ma>
     <td align=center>
      <table width=100% align=center>
      <tr id=ma>
       <td  align=left>Cantidad: <?=$total?> </td>
       <td  align=right><?=$link_pagina?></td>
      </tr>
     </table>
    </td>  
      
   </tr>

     <tr>
       <td align=center>
          <table width=100% align=center>
            <tr id=mo>
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>Nro Orden </a></td>
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>ID </a></td>              
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>Cobranza </a></td>
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>Nro Factura</a></td>			  
              <td ><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>Descripción </a></td>
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>M</a></td>
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>Cant.</a></td>
              <td width=5%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"id_detalle_balance_historial"=>$id_detalle_balance_historial))?>'>Monto</a></td>
           </tr>
           <?
           for($i=0;$i<$result->recordcount();$i++){
           ?>
           <tr <?=atrib_tr()?>>
              <?
              $link = encode_link("../../ord_compra/ord_compra.php",array("nro_orden"=>$result->fields["nro_orden"]));
              ?>
              <td align=center><a href="<?=$link?>" target="_blank"><?=$result->fields["nro_orden"]?></a></td>
              <?$link = encode_link("../../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));?>              
              <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>
              <?$link = encode_link("../../licitaciones/lic_cobranzas.php",array("cmd1"=>"detalle_cobranza","id"=>$result->fields["id_cobranza"],"cmd"=>strtolower($result->fields["estado_cobranzas"])));?>
              <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_cobranza"]?></a></td>

              <?$link = encode_link("../../facturas/factura_nueva.php",array("nro_factura"=>$result->fields["nro_factura"]));?>
              <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["nro_factura"]?></a></td>
			  
              <td><?=$result->fields["descripcion"]?></td>
              <td align=center><?=$result->fields["moneda"]?></td>
              <td align=right><?=$result->fields["cantidad"]?></td>
              <td align=right><?=formato_money($result->fields["monto"])?></td>
              <?($result->fields["moneda"]=="\$")?$monto_pesos+=$result->fields["monto"]:$monto_dolares+=$result->fields["monto"]?>
            </tr>  
           <?
           $result->movenext();
           }?>
           
          </table>       
       </td>
     </tr>
     <tr>
        <td>
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
</form>
<?
echo fin_pagina();
?>