<?php
/*
$Author: fernando $
$Revision: 1.9 $
$Date: 2006/04/17 22:59:48 $
*/
//require_once("../../../config.php");
//la variable $sql esta la pagina que requiere a esta
if ($id_moneda && $id_moneda!=-1)
        $where_moneda=" and oc.id_moneda=$id_moneda";
       
$res=sql_adelantos(0);
$sql=$res["sql"];

variables_form_busqueda("detalle_oc_parcialmente_recibidas");

 

$orden = array(
		"default" => "1",
        "1"  => "nro_orden",
        "2"  => "razon_social",
        "3"  => "id_licitacion",
		"4" => "total",
	);

$filtro = array(
		"nro_orden" => "Nro Orden",
        "razon_social" => "Proveedor",
		"id_licitacion" => "ID Licitacion",
	);

?>
<table width=95% align=center>
   <tr>
      <td align=center colspan=5 align=center width=100% bgcolor=white>
        <table width=100% align=center >
          <tr>
            <td width=50% align=center>
              <?
                list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,0);
                
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
      <td width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Nro Orden </a></td>
      <td width=60%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Proveedor </a></td>
      <td width=10%><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Id  </a></td>
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
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["nro_orden"]?></a></td>
       <td align=left>&nbsp;<?=$result->fields["razon_social"]?></td>
       <?
       $link = encode_link("../../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
       ?>       
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>       
        <td align=center><?=$result->fields["simbolo"]?></td> 
       <td align=right><?=formato_money($result->fields["total"])?></td>
       <?
         ($result->fields["id_moneda"]==1)?$monto_pesos+=$result->fields["total"]:$monto_dolares+=$result->fields["total"];
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