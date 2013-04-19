<?
require_once("../../config.php");
echo $html_header;
$id_producto=$_GET['id_producto'] or $id_producto=$parametros['id_producto'];
if ($id_producto=="")
       echo aviso("No hay historial disponible");
       else
       {
       	//traigo los datos del producto
       	$sql_prod="select desc_gral,codigo as tipo
                  from general.productos
                  join tipos_prod using(id_tipo_prod)
                   where id_producto=$id_producto";
        $res_prod=sql($sql_prod) or fin_pagina();

        	//traigo comentario actual
       $sql="select fecha_comentario,usuarios.nombre,usuarios.apellido,comentario
             from historial_comentario_producto
             join usuarios using(id_usuario)
             where id_producto=".$id_producto." order by fecha_comentario desc";
       //echo $sql;
       $result_com=$db->execute($sql) or die($sql."<br>".$db->errorMsg());
       ?>

       <table align="center" width="100%" class=bordes>
      <tr bgcolor="<?=$bgcolor_out?>">
       <td colspan="2"> <b>TIPO PRODUCTO:</b> <?=$res_prod->fields['tipo']?> </td>
      </tr>
      <tr bgcolor="<?=$bgcolor_out?>">
        <td colspan="2"><b> DESCRIPCION GENERAL: </b><?=$res_prod->fields['desc_gral']?> </td>
      </tr>
     </table>
     <br>

       <table width=100% align=center class=bordes>
       <?
       if ($result_com->RecordCount()>=1)
       {
       ?>
        <tr>
          <td id=mo width=15%>Ultimo Comentario:</td>
          <td><font size="2" color="red"><?=$result_com->fields['comentario'];?></font></td>
       </tr>
       <tr>
         <td id=mo>Fecha:</td>
         <td><font size="2" color="red"><?=fecha($result_com->fields['fecha_comentario']);?></font></td>
      </tr>
      <tr>
      <td id=mo>Usuario:</td>
      <td><font size="2" color="red"><?=$result_com->fields['nombre']." ".$result_com->fields['apellido'];?></font></td>
      </tr>
     <?
      }
      else
       {
       ?>
       <tr><td align=center><font color=red size=3>No existen comentarios sobre este producto</font></td></tr>
       <?
       }
       ?>
     </table>
     <br>

       <?
        if ($result_com->RecordCount()>0)
           {
        ?>
        <table class="bordes" width=100% align=center>
        <tr>
           <td id=mo colspan=3>
           Historial de los Comentarios
           </td>
        </tr>
        <tr id=ma>
          <td align="center"><b>Fecha</td>
          <td align="center"><b>Usuario</td>
          <td align="center"><b>Comentario</td>
        </tr>
        <?
        $result_com->Move(0);
           while(!$result_com->EOF)
            {
        ?>
         <tr bgcolor="<?=$bgcolor_out?>">
            <td align="center" width="20%"><?=$result_com->fields['fecha_comentario'];?></td>
            <td align="center" width="20%"><?=$result_com->fields['nombre']." ".$result_com->fields['apellido'];?></td>
            <td align="center" width="60%"><?=$result_com->fields['comentario'];?></td>
         </tr>
        <?
        $result_com->MoveNext();
        }
 ?>
</table>
<?
}
?>
<br>
<?
//recupera las 10 ultimas ordenes de compra en donde se haya comprado el producto
$sql="select nro_orden, razon_social ,cantidad,precio_unitario,fecha,simbolo,estado
from compras.orden_de_compra join compras.fila using (nro_orden)
join general.proveedor using (id_proveedor)
join licitaciones.moneda using(id_moneda)
where id_producto=$id_producto
order by fecha DESC limit 10";
$res=sql($sql) or fin_pagina();
?>
<table align="center" class=bordes cellpadding="2" cellspacing="2" width="100%">
<tr id=mo><td colspan="5">ORDENES DE COMPRA</td></tr>
<tr id=ma>
<?if ($res->RecordCount()>0) { ?>
<td>Nro Orden </td>
<td>Fecha Creacion OC</td>
<td>Proveedor</td>
<td>Cantidad</td>
<td>Precio Unitario</td>
<?}
else {?>
<td colspan="5">EL PRODUCTO NO SE HA COMPRADO EN NINGUNA ORDEN DE COMPRA </td>
<?}?>
</tr>
<? while (!$res->EOF) { ?>
  <?
  if ($res->fields["estado"]=="n") $color="red";
  else $color=$bgcolor_out
  ?>
  <tr bgcolor="<?=$color?>">
  <?$link_orden = encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$res->fields['nro_orden']));?>
  <td align="center"><a href="<?=$link_orden?>" style="color='black';cursor='hand'" target="_blank"><?=$res->fields['nro_orden']?></b></a>  </td>
  <td align="center"><?=fecha($res->fields['fecha'])?> </td>
  <td align="center"><?=$res->fields['razon_social']?>  </td>
  <td align="center"><?=$res->fields['cantidad']?>  </td>
  <td align="center"><?=$res->fields['simbolo']." ".formato_money($res->fields['precio_unitario'])?>  </td>
  </tr>
<?
$res->MoveNext();
 }?>
</table>
<br>
<div style="background-color: white;border: solid ;border-width: 1;overflow-y: auto; width: 100%;">
<b><font size=2>Colores de referencia</font><b><br>
<table border=0 width=150px>
<tr>
	<td align="right" width=100%>
		Orden Anulada:
	</td>
	<td>
		<div style="background-color: red; border: solid;border-width: 1;width:30;height:10"></div>
	</td>
</tr>
</table>
</div>
<center><input type="button" value="Salir" onclick="window.close();" style="cursor:hand"></center>
<?
}
echo fin_pagina();
?>