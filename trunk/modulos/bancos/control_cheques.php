<?
require_once("../../config.php");

switch ($_POST['boton'])
{case "Modificar": $_POST["Modificar_Cheque_Numero"]=$_POST['númeroch'];
                   $_POST['id_banco']=$_POST['id_banco_'.$_POST['númeroch']];
                   $_POST['Modificar']="Modificar";
                   $volver_cheque=1;
                   require_once("bancos_movi_chpen.php");
                   break;
 case "OK":$sql="update cheques set cheque_ok='t' where númeroch=".$_POST['númeroch'];
           $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 default :
 {
echo $html_header;
?>
<script>
var contador=0;
function habilitar()
{document.all.boton[0].disabled=false;
 document.all.boton[1].disabled=false;
}

function chequea_radio(indice)
 {
 if (document.all.númeroch.length>1)
  document.all.númeroch[indice].checked="true";
      else
  document.all.númeroch.checked="true";
 habilitar();
 }

</script>
<!--Menu Contextual -->
<div id="ie5menu" class="skin1" onMouseover="highlightie5()" onMouseout="lowlightie5()" onClick="jumptoie5();">
<div class="menuitems" url="javascript:document.all.boton[0].click();">OK</div>
<div class="menuitems" url="javascript:document.all.boton[1].click()">Modificar</div>
</div>
<!--Fin menu contextual-->

<script>
//llama al menu contextual
if (document.all && window.print) {
ie5menu.className = menuskin;
document.body.oncontextmenu = showmenuie5;
document.body.onclick = hidemenuie5;
}
</script>
<form name="form1" action="control_cheques.php" method="POST">
<br>
<font size="3"><b>Controlar Ingresos</b></font>
<hr>
<?
//actualizo desplazamiento
$offset=($_GET['offset']=="")?0:$_GET['offset'];
?>
<table width="90%" align="center">
<tr id="mo">
<td align="left">
<?
if($offset!=0)
{
?>
<a href="control_cheques.php?offset=<?=$offset-30;?>"><-</a>
<?
}
$sql="select tipo_banco.idbanco,pago_orden.nro_orden,cheques.númeroch,proveedor.razon_social,cheques.importech,(tipo_cuenta.concepto || tipo_cuenta.plan) as plan from cheques join tipo_banco using(idbanco) left join tipo_cuenta using(numero_cuenta) left join ordenes_pagos using(númeroch) left join pago_orden using(id_pago) left join orden_de_compra using(nro_orden) left join proveedor using(id_proveedor) where cheques.cheque_ok='f' and fechaemich >= '2004-07-01' order by cheques.fechaemich desc limit 30 offset $offset";
$resultado_cheques=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
?>
</td>
<td></td>
<td>OC</td>
<td>N Cheque</td>
<td>Proveedor</td>
<td>Importe</td>
<td>Concepto y Plan&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?if($resultado_cheques->recordcount()==30){?><a href="control_cheques.php?offset=<?=$offset+30;?>">-></a><?}?></td>
</tr>
<?
$i=0;
while(!$resultado_cheques->EOF)
{
?>
<tr bgcolor='<?=$bgcolor_out?>' oncontextmenu='chequea_radio(<?=$i;?>)'>
<td><?if($resultado_cheques->fields['nro_orden']!=""){?><input type=checkbox class='estilos_check' name=chk value=1 onclick="javascript:(this.checked)?Mostrar('fila_<?=$resultado_cheques->fields['nro_orden']?>'):Ocultar('fila_<?=$resultado_cheques->fields['nro_orden']?>');" style="cursor:hand"><?}?></td>
<td><input type="radio" name="númeroch" value="<?=$resultado_cheques->fields['númeroch'];?>" onclick="habilitar()" style="cursor:hand">
    <input type="hidden" name="id_banco_<?=$resultado_cheques->fields['númeroch'];?>" value="<?=$resultado_cheques->fields['idbanco'];?>">
</td>
<td><a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado_cheques->fields['nro_orden']));?>" target="_blank"><?=$resultado_cheques->fields['nro_orden'];?></a></td>
<td><?=$resultado_cheques->fields['númeroch'];?></td>
<td><?=$resultado_cheques->fields['razon_social'];?></td>
<td><?=$resultado_cheques->fields['importech'];?></td>
<td><?=$resultado_cheques->fields['plan'];?> <?=$resultado_cheques->fields['cheque_ok'];?></td>
</tr>
                   <?
                 //Traigo los renglones de esta licitacion
   
   if ($resultado_cheques->fields['nro_orden']!="") //controlo que este asociado al pago de una orden de compra
   {
   $sql="select descripcion_prod,cantidad,desc_adic from fila where nro_orden=".$resultado_cheques->fields['nro_orden'];
   
   $resultado_productos=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   
   
                     ?>
                 <tr>
                 <td></td>
                 <td colspan=6>
                 <div id='fila_<?=$resultado_cheques->fields['nro_orden'];?>' style='display:none'>
                      <?
                      //No hay renglones  que mostrar
                      if ($resultado_productos->recordcount()<=0){
                      ?>
                         <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspading=0 cellpading=0 class="bordes" border=0>
                         <tr><td colspan=6 align=center><b>No hay productos asociados a esta Orden de Compra</b></td></tr>
                         </table>
                      <?
                      }//del then
                      else{
                     ?>
                           <table  width=100% align=Center bgcolor=<?=$bgcolor3?> cellspacing=0 cellpading=0 border=1 bordercolor=#ACACAC>
                                <tr>
                                 <td width="10%">Cantidad</td>
                                 <td width="60%">Descripcion</td>
                                 <td width="20%">Descripcion Adicional</td>
                                </tr>
                               <?
                               //muestro los renglones
                                  for($j=0;$j<$resultado_productos->recordcount();$j++){

                                  ?>
                                  <?//$link=encode_link("licitaciones_view.php",array("ID"=>$resultado_licitacion->fields["id_licitacion"],"pag_ant"=>"cargar_competidores","pagina_volver"=>"cargar_competidores.php","cmd1"=>"detalle"));?>
                                  <!--<a href="<?=$link?>" target='_blank'>-->
                                  <tr <?=atrib_tr();?>>
                                    <td>
                                    <b><?=$resultado_productos->fields["cantidad"]?></b></td>
                                    <td><b><?=$resultado_productos->fields["descripcion_prod"]?></b></td>
                                    <td align=center><b><?=$resultado_productos->fields["desc_adic"]?></b></td>
                                  </tr>
                                  <!--</a>-->
                                 <?
                                  $resultado_productos->movenext();
                                   }
                                   ?>
                               </table>
                               <?
                               } //del else
                               ?>
                    </div>
                  </td>
              </tr>

<?
  }
$resultado_cheques->MoveNext();
$i++;
}
?>
</table>
<center>
<input type="submit" name="boton" value="OK" disabled style="cursor:hand">
<input type="submit" name="boton" value="Modificar" disabled style="cursor:hand">
</center>
</body>
</form>
</html>
<?
 }
}
?>