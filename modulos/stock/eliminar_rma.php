<?
/*
Modificada por
$Author: marco_canderle $
$Revision:
$Date: 2006/06/23 19:59:37 $
*/

require_once("../../config.php");
require_once("funciones.php");

echo $html_header;

if ($_POST['autorizar']) {
$rma_selec=$_POST['rma_selec'];

switch ($_POST['baja']) {
   case 1: $id_proveedor=$_POST['id_proveedor'];
           $monto=$_POST['monto'];
           $link=encode_link("../ord_compra/nota_credito.php",array('pagina'=>'RMA',"rma_selec"=>$rma_selec,"id_proveedor"=>$id_proveedor,"monto"=>$monto,"descripcion"=>"Baja de RMA","monto"=>$monto,"pagina_volver"=>"../stock/listar_rma.php"));
           echo "<html><head><script language=javascript>";
           echo "document.location='$link';";
           echo "</script></head></html>";
           break;
   case 2: if(eliminar_rma(descomprimir_variable($rma_selec),'Baja por Scrap',"",1))
               $msg="Se realizó la Baja con exito";
               else $msg="Error al realizar la baja";
           $link=encode_link("listar_rma.php",array("exito"=>$msg));
           echo "<html><head><script language=javascript>";
           echo "document.location='$link';";
           echo "</script></head></html>";
           die();
   	       break;
}

}

$rma_selec=PostvartoArray('rma_'); //crea un arreglo con los checkbox chequeados

if ($rma_selec) {  //para ver si hay check seleccionados
 $list='(';
 foreach($rma_selec as $key => $value){
   $list.=$value.',';
 }
 $list=substr_replace($list,')',(strrpos($list,',')));
}

$sql_rma="select sum(cantidad * precio_stock) as total,en_stock.id_prod_esp,
          id_info_rma,descripcion,lugar,id_estado_rma,id_proveedor,cantidad
		  from stock.en_stock
		  join general.producto_especifico using(id_prod_esp)
		  join stock.info_rma using (id_en_stock)
		  join stock.estado_rma using (id_estado_rma)
		  join general.proveedor using (id_proveedor)
          where id_info_rma in $list
          group by en_stock.id_prod_esp,id_info_rma,descripcion,
          lugar,id_estado_rma,id_proveedor,cantidad order by lugar";

$res_rma=sql($sql_rma,"$sql_rma") or fin_pagina();
$lugar=$res_rma->fields['lugar'];
$id_proveedor=$res_rma->fields['id_proveedor'];
if ($lugar=='Nota de Credito') $chk=1;
   elseif ($lugar=='Pedido de Scrap') $chk=2;
      else $chk=1;
$link_volver=encode_link("listar_rma.php",array());

?>

<form name='form1' action="eliminar_rma.php" method="POST">
<br>
<input type='hidden' name='rma_selec' value='<?=comprimir_variable($rma_selec)?>'>
<input type='hidden' name='id_proveedor' value='<?=$id_proveedor?>'>

<table align="center" width="50%" border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0">
  <tr id="mo">
     <td align="center">Dar de Baja RMA</td>
  </tr>
   <tr>
     <td align="left" id="ma_sf"> <input class='estilos_check' type="radio" name="baja" value="1" <?if ($chk==1) echo 'checked'?>> Nota de Crédito  </td>
  </tr>
  <tr>
     <td align="left" id="ma_sf"> <input class='estilos_check' type="radio" name="baja" value="2" <?if ($chk==2) echo 'checked'?>> Scrap  </td>
 </tr>
</table>
<br>

<?$res_rma->Movefirst();
$monto=0;?>
<table align="center" width="50%" border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0">
 <tr id=mo>
   <td> Nº RMA </td>
   <td> Cant. </td>
   <td> Producto </td>
   <td> Monto </td>
 </tr>
 <? while(!$res_rma->EOF) {?>
   <tr <?=$atrib_tr?>>
     <td><?=$res_rma->fields['id_info_rma']?></td>
     <td><?=$res_rma->fields['cantidad']?></td>
     <td><?=$res_rma->fields['descripcion']?></td>
     <td><?="U\$S ".formato_money($res_rma->fields['total'])?></td>
   </tr>
  <?
  $monto+=$res_rma->fields['total'];
  $res_rma->Movenext();
  }?>
</table>
<br>
<table align="center">
 <tr>
    <td><input type='submit' name='autorizar' value='Autorizar' onclick="if(confirm('¿Está seguro que desea eliminar?'))return true; else return false;"></td>
    <td><input type='button' name='volver' value='Volver' onclick="document.location='<?=$link_volver?>'"></td>
 </tr>
</table>
<input type='hidden' name="monto" value='<?=$monto?>'>
</form>