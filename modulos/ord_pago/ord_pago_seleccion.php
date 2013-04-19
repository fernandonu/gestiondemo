<?php
require_once("../../config.php");
require_once("fns.php");
//obtengo las ordenes de compra que estan para ser pagadas

$id_proveedor=$parametros['id_proveedor'] or $_POST['id_proveedor'];
$nro_orden=$parametros['nro_orden'];
$id_moneda=$parametros['id_moneda'];

$sql="select * from (compras.orden_de_compra join general.proveedor using(id_proveedor)) join moneda using(id_moneda)";
$sql.=" where (estado='e' or estado='m') and proveedor.id_proveedor=$id_proveedor and ord_pago is not null";
$resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
$filas_encontradas=$resultado->RecordCount();


//almacenamos los nro de orden que se elijieron, para pasarlos a la pagina
//de formas de pago
/* (ESTO ES NECESARIO PORQUE JAVASCRIPT NO TOMA NOMBRES DEL ESTILO chk_orden[], ENTONCES NO
    SE PUEDE HACER EL CONTROL DE MONEDA, DE MANERA SIMILAR AL DE LISTAR ORDENES DE COMPRA,
    Y A SU VEZ ENVIAR UN ARREGLO POR POST -usando chk_orden[] de una- POR ESO ESTA PARTE
    HACE QUE TODO FUNCIONE DE LA MANERA NECESARIA, PORQUE PERMITE EL CONTROL DE JAVASCRIPT,
    Y A SU VEZ, ENVIA UN ARREGLO CON LAS ORDENES SELECIONADAS A LA PAGINA ord_pago_pagos.php)*/
if($_POST['boton']=="Agregar Ordenes")
{
 $link=encode_link("ord_pago_pagos.php",array("pagina_viene"=>"pago_multiple","armando"=>"si"));

 echo "<form name='form1' method='Post' action='$link'>";
 echo "<input type='hidden' name='id_proveedor' value='$id_proveedor'>";
 for($i=0;$i<$filas_encontradas;$i++)
 {if($_POST["selec_orden_".$i]!="")
   echo "<input type='hidden' name='chk_orden[]' value=".$_POST["selec_orden_".$i].">";
 }
 echo '<input type="hidden" name="nro_orden_orig" value="'.$nro_orden.'" />';
 echo "</from>";
 ?>
 <script>
  document.form1.submit();
 </script>
 	<?
}



?>
<html>
<head>
<script>
function cantidad_chekeados()
{
 var cantidad=0;
<?
 for($i=0;$i<$filas_encontradas;$i++)
 {?>
  if(document.all.selec_orden_<?=$i?>.checked) cantidad++;
 <?
 }
 ?>
 return cantidad;
}

</script>
</head>
<body bgcolor='<? echo $bgcolor2;?>' >
<?
$link=encode_link("ord_pago_seleccion.php",array("id_proveedor"=>$id_proveedor,"nro_orden"=>$nro_orden,"id_moneda"=>$id_moneda));
?>
<form name="form1" method="Post" action="<?=$link?>">
<input type='hidden' name='id_proveedor' value='<?=$id_proveedor?>'>
<? echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";?>
<table align="center"  width="100%" border="1" cellspacing="1"  bordercolor="#000000">
<tr>
  <td colspan='5' align="center" id="mo">
  Listado de las Ordenes de Pago
  </td>
</tr>
<tr>
  <td width="1%">
  </td>

  <td width="5%" align="center">
  <b> Nro Orden
  </td>
  <td width="40%" align="center">
  <b> Cliente
  </td>
  <td width="40%" align="center">
  <b> Proveedor
  </td>
  <td width="15%" align="center">
  <b> Monto
  </td>
</tr>
<input type="hidden" name="moneda_seleccion" value="<?=$id_moneda?>">
<?
 for($i=0;$i<$filas_encontradas;$i++){
 $nro_chk=$resultado->fields['nro_orden'];
 $moneda=$resultado->fields['id_moneda'];
 $simbolo_money=$resultado->fields['simbolo'];
 $monto=monto_a_pagar($nro_chk);
 $cliente=$resultado->fields['cliente'];
 $proveedores=$resultado->fields['razon_social'];
 echo "<tr bgcolor='#c0c6c9'>";
  echo "<td>";

   echo "<input type='checkbox' name='selec_orden_$i' value=$nro_chk";
   if($nro_chk==$nro_orden)
    echo " checked ";
   echo "  onclick='
                    if(document.all.moneda_seleccion.value==0 || document.all.moneda_seleccion.value==$moneda)
                    { document.all.moneda_seleccion.value=$moneda;
                      if(cantidad_chekeados()==0)
                        document.all.moneda_seleccion.value=0;
                    }
                    else
                    { this.checked=0;
                      alert(\"No se puede incluir en un pago múltiple, ordenes de pago con monedas distintas\");
                    }'";
   echo "  >";
   echo "<input type='hidden' name='moneda[]' value=$moneda>";
  echo "</td>";
  echo "<td>";
    echo  "<font color='#006699'><b>$nro_chk </font>";
  echo "</td>";
  echo "<td>";
    echo  "<font color='#006699'><b>Coradir S.A. </font>";
  echo "</td>";
  echo "<td>";
    echo  "<font color='#006699'><b>$proveedores </font> ";
  echo "</td>";
   echo "<td><table><tr><td width='1%'>";
    echo  "<font color='#006699'><b>$simbolo_money</td><td align='right'><font color='#006699'><b>".formato_money($monto)."</font> ";
  echo "</td></tr></table></td>";

 echo "</tr>";
 $resultado->MoveNext();
 }

?>
<tr>
 <td colspan='5' align='center'>
  <?
  $link=encode_link("./ord_pago_pagar.php",array("nro_orden"=>$nro_orden));
  ?>
  <input type='submit' name='boton' style="width=20%" value='Agregar Ordenes'>
  <input type="hidden" name="nro_orden_orig" value="<?=$nro_orden ?>" />
  <input type='button' name='boton' style="width=20%" value='Volver' onclick="document.location.href='<?=$link?>';">
 </td>
</tr>
</table>

</form>
</body>
</html>