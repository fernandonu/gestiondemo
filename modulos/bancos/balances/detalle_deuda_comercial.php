<?php
/*
$Author: fernando $
$Revision: 1.17 $
$Date: 2006/04/17 22:57:23 $
*/
variables_form_busqueda("detalle_deuda_comercial");
$internacional=$parametros["internacional"] or $internacional=$_POST["internacional"];


if ($internacional)
          {
          $res=sql_deuda_comercial(1,0);
          $sql=$res["sql"];
          $where=$res["where"];
          }
          else
          {
          $res=sql_deuda_comercial(0,0);
          $sql=$res["sql"];    
          $where=$res["where"];
          }
$orden = array(
		"default" => "1",
        "1"  => "nro_orden",
		"2"  => "id_licitacion",
		"3"  => "monto",
        "4"  => "razon_social"
	);

$filtro = array(
		"id_licitacion" => "ID Licitación",
		"nro_orden" =>"Nro Orden",
        "razon_social" => "Proveedor"
		);

?>
<input type=hidden name=internacional value="<?=$internacional?>">
<table width=95% align=center>
    <tr>
      <td align=center colspan=6 align=center width=100% bgcolor=white>
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
       <td colspan=4 align=right><?=$link_pagina?></td>
   </tr>

   <tr id=mo>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"internacional"=>$internacional))?>'>Nro Orden </a></td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"internacional"=>$internacional))?>'>Proveedor </a></td>      
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"internacional"=>$internacional))?>'>ID Licitación </a></td>
      <td>S</td>
      <td><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","pagina"=>$pagina,"up"=>$up,"titulo"=>$titulo,"internacional"=>$internacional))?>'>Monto </a></td>
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
       <td align=left><?=$result->fields["razon_social"]?></td>       
       <?
       $link = encode_link("../../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
       ?>
       <td align=center><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>
       <td align=center><?=$result->fields["simbolo"]  ?></td>
       <td align=right><?=formato_money($result->fields["monto"])?></td>
       <?
       if ($result->fields["id_moneda"]==1)
              $monto_pesos+=$result->fields["monto"];
              else
              $monto_dolares+=$result->fields["monto"];
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