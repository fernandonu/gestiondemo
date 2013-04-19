<?php
/*
$Author: fernando $
$Revision: 1.8 $
$Date: 2006/05/08 14:12:47 $
*/

//require_once("../../../config.php");
//la variable $sql esta la pagina que requiere a esta



$datos=sql_cuentas_a_cobrar(0,$id_moneda);

$sql   = $datos["sql"];
$where = $datos["where"];

variables_form_busqueda("detalle_cuentas_a_cobrar");



$orden = array(
		"default" => "2",
        "1"  => "id_cobranza",
		"2" => " id_licitacion",
		"3" => " nro_factura",
		"4" => "monto",
	);

$filtro = array(
		"id_licitacion" => "ID Licitación",
		"nro_factura" => "Nro Facturas",

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
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>ID Cobranzas </a></td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>ID Licitación </a></td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Nro Factura</a></td>
      <td>S</td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","id_moneda"=>$id_moneda,"pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo))?>'>Monto </a></td>
   </tr>
   <?
   //die($sql_temp);
   $monto_dolares=$monto_pesos=0;
   for($i=0;$i<$result->recordcount();$i++){
   ?>
    <tr  <?=atrib_tr();?>>
       <?
       $link = encode_link("../../licitaciones/lic_cobranzas.php",array("cmd1"=>"detalle_cobranza","id"=>$result->fields["id_cobranza"],"cmd"=>strtolower($result->fields["estado_cobranzas"])));
       ?>
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_cobranza"]?></a></td>
       <?
       $link = encode_link("../../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
       ?>
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>
       <?
       $link = encode_link("../../facturas/factura_nueva.php",array("id_factura"=>$result->fields["id_factura"]));
       ?>
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["nro_factura"]?></a></td>
       <td align=center><?=$result->fields["simbolo"]?></td>
       <td align=right><?=formato_money($result->fields["monto"])?></td>
       <?
       ($result->fields["id_moneda"]==1)?$monto_pesos+=$result->fields["monto"]:$monto_dolares+=$result->fields["monto"];
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