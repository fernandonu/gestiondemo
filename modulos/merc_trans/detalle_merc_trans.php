<?
/*
Autor: MAC 
Fecha: 28/07/04

$Author: elizabeth $
$Revision: 1.8 $
$Date: 2004/09/01 20:09:15 $
*/
require_once("../../config.php");

$id_proveedor=$parametros['id_proveedor'];
$id_producto=$parametros['id_producto'];
$id_deposito=$parametros['id_deposito'];
$id_mercaderia_transito=$parametros['id_mercaderia_transito'];
$id_movimiento_material=$parametros['id_m_m'];


$id_control_stock=$parametros['id_control_stock'];
$param_extra=$parametros['ubicacion'];

$msg=$parametros['msg'];
if($_POST['guardar']=="Guardar")
{
 $db->StartTrans();
 $user=$_ses_user['name'];
 $fecha=date("Y-m-d H:i:s",mktime());
 $texto=$_POST['nuevo_coment'];
 $query="insert into comentarios_merc_trans(usuario,fecha,texto,id_producto,id_deposito,id_proveedor,id_mercaderia_transito) 
         values('$user','$fecha','$texto',$id_producto,$id_deposito,$id_proveedor,$id_mercaderia_transito)";
 if($db->Execute($query))
  $msg="<center><b>El comentario se guardó con éxito</b></center>";
 else
  $msg="<center><b>El comentario no se pudo guardar</b></center>"; 
 $db->CompleteTrans();	
}



echo $html_header;

 $sql="
   select productos.desc_gral,stock.id_deposito,stock.id_producto,tipos_prod.descripcion,
          mercaderia_transito.id_movimiento_material,stock.comentario_inventario,
          mercaderia_transito.nro_orden,mercaderia_transito.comentarios,mercaderia_transito.cantidad,
          proveedor.razon_social,proveedor.id_proveedor,productos.precio_stock,
          orden_de_compra.flag_stock,orden_de_compra.fecha,orden_de_compra.fecha_entrega,
          destino_oc.nombre_destino_oc,movimiento_material.fecha_creacion
   from stock
   join general.productos using(id_producto)
   join general.tipos_prod on (tipos_prod.codigo=productos.tipo)
   join mercaderia_transito using (id_deposito,id_producto,id_proveedor)
   left join movimiento_material using(id_movimiento_material) 
   join proveedor using (id_proveedor)
   left join orden_de_compra using(nro_orden)
   left join destino_oc using(id_destino_oc)
   where mercaderia_transito.id_deposito=$id_deposito and mercaderia_transito.id_producto=$id_producto 
         and mercaderia_transito.id_proveedor=$id_proveedor and mercaderia_transito.id_mercaderia_transito=$id_mercaderia_transito";

 
$datos=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error al traer los datos del producto en RMA<BR>$sql");   

echo $msg;
?>
<br>
<table width="80%" align="center" border="1">
 <tr>
  <td>
   <table width="100%" align="center" cellpadding="3">
    <tr id=mo>
     <td colspan="2">
      <font size="3">Información del Producto</font>
     </td>
    </tr>
    <tr id=ma_sf>
     <td width="40%">
      <b>Descripción</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=$datos->fields['desc_gral']?></font></b>
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="40%">
      <b>Cantidad</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=$datos->fields['cantidad']?></font></b>
     </td> 
    </tr> 
    <tr  id=ma_sf>
     <td width="40%">
      <b>Tipo</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=$datos->fields['descripcion']?></font></b>
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="40%">
      <b>Proveedor</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=$datos->fields['razon_social']?></font></b>
     </td> 
    </tr>
    <tr id=ma_sf>
     <td width="40%">
      <b>Destino OC</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=$datos->fields['nombre_destino_oc']?></font></b>
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="40%">
      <b>Precio</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2">U$S <?=formato_money($datos->fields['precio_stock'])?></font></b>
     </td> 
    </tr>
    <?
     if($datos->fields['nro_orden'])
     {?>
      <tr id=ma_sf>
       <td width="40%">
        <b>Nº Orden de Compra</b>
       </td>
       <td width="60%">
         <b><font color="Blue" size="2"> <?=$datos->fields['nro_orden']?></font></b>
       <? $aux_nro_orden=$datos->fields['nro_orden'];
          $cons="select estado from orden_de_compra where nro_orden=$aux_nro_orden";
          $res=$db->Execute($cons) or die($db->ErrorMsg()."<br>Error al traer el estado de la OC<BR>$cons");   
          if ($res->fields['estado']!='a') {
       ?>
        
        <? $link_oc=encode_link("../ord_compra/ord_compra_fin.php",array("pagina"=>"merc_trans","nro_orden"=>$datos->fields['nro_orden'],"mt_id_mt"=>$id_mercaderia_transito,"mt_id_deposito"=>$id_deposito,"mt_id_producto"=>$id_producto,"mt_id_proveedor"=>$id_proveedor))?>
        <input type="button" name="ir_oc" value="Ir" onclick="window.open('<?=$link_oc?>')">
       <? } ?>
       </td> 
      </tr> 
      <tr id=ma_sf>
       <td width="40%">
        <b>Fecha Creación Orden de Compra</b>
       </td>
       <td width="60%">
        <b><font color="Blue" size="2"> <?=fecha($datos->fields['fecha'])?></font></b>
       </td> 
      </tr> 
      <tr id=ma_sf>
       <td width="40%">
        <b>Fecha Entrega Orden de Compra</b>
       </td>
       <td width="60%">
        <b><font color="Blue" size="2"> <?=fecha($datos->fields['fecha_entrega'])?></font></b>
       </td> 
      </tr> 
     <?
     }//de  if($datos->fields['nro_orden'])
     elseif($datos->fields['id_movimiento_material'])	 
     {
    ?>
    <tr id=ma_sf>
     <td width="40%">
       <b>Nº de Movimiento de Material</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=$datos->fields['id_movimiento_material']?></font></b>
      <? //esta hecho el link pero no se puede modificar el archivo detalle_moviento 
         //en ese archivo se debe agregar el boton de volver a detalle_merc_trans
         //tambien ver que parametros se tienen que pasar 
      $id=$datos->fields['id_movimiento_material'];   
      $link_mv=encode_link("../mov_material/detalle_movimiento.php",array("pagina"=>"listado_mt","id"=>$id,"mt_id_mt"=>$id_mercaderia_transito,"mt_id_deposito"=>$id_deposito,"mt_id_producto"=>$id_producto,"mt_id_proveedor"=>$id_proveedor));?>
        <input type="button" name="ir_mv" value="Ir" onclick="window.open('<?=$link_mv?>')"> 
     </td> 
    </tr>
    <tr id=ma_sf>
     <td width="40%">
       <b>Fecha Creación Movimiento de Material</b>
     </td>
     <td width="60%">
      <b><font color="Blue" size="2"> <?=fecha($datos->fields['fecha_creacion'])?></font></b>
     </td> 
    </tr>
  <?  
     }//de elseif($datos->fields['id_movimiento_material'])
switch ($param_extra)
{
 case 1: $tipo_merc_trans="Movimientos Entre Stock";
                       break;
 case 2: $tipo_merc_trans="Movimientos de OC para Stock";
                       break;
 case 3: $tipo_merc_trans="Movimientos Restantes";
                       break;
 
} 
?>
    <tr id=ma_sf>
     <td width="30%">
       <b>Origen de Mercadería en Tránsito</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"><?=$tipo_merc_trans?></font></b>
     </td> 
    </tr>
   </table>
  </td>
 </tr> 
<?
if($id_control_stock)
{?> 
 <tr>
 <td>
 <br>
 <table width=100% align=center>
     <tr id="mo">
       <td>Usuario</td>
       <td>Acción</td>
       <td>Fecha</td>
     </tr>
   <?
   $sql="select * from log_stock ";
   $sql.=" where id_control_stock=$id_control_stock";
   $log_stock=$db->execute($sql)  or die($db->errormsg()."<br>".$sql);
   $cant_log=$log_stock->recordcount();

   for($i=0;$i<$cant_log;$i++){
   ?>
   <tr id=ma>
    <td><b><?=$log_stock->fields["usuario"]?>     </b></td>
    <td><b><?=$log_stock->fields["tipo"]?>        </b></td>
    <td><b><?=fecha($log_stock->fields["fecha"])?></b></td>
   </tr>
   <?
   $log_stock->movenext();
   }
   ?>
   </table>
  </td>
 </tr> 
<?
}//de if($id_control_stock)
?> 
</table>  
<input type=hidden name="id_control_stock" value="<?=$id_control_stock?>">


<br>
<?
//traemos los comentarios del producto en transito
$query="select * from comentarios_merc_trans where id_producto=$id_producto 
        and id_deposito=$id_deposito and id_proveedor=$id_proveedor and id_mercaderia_transito=$id_mercaderia_transito";
$comentarios=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los comentarios del producto en transito");

$link=encode_link("detalle_merc_trans.php",array("id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_mercaderia_transito"=>$id_mercaderia_transito,"id_control_stock"=>$id_control_stock,"pagina_listado"=>$parametros['pagina_listado'],"ubicacion"=>$param_extra));
?>
<form name='form1' action="<?=$link?>" method="POST">
<table width="80%" align="center" border="1">
 <tr id=mo>
  <td>
   <font size="3">Comentarios</font>
  </td>
 </tr>
 <tr>
  <td>
   <table align="center" width="100%">
    <?
    //generamos los comentarios ya cargados
    while(!$comentarios->EOF)
    {?>
     <tr>
      <td>
       <table width="100%">
        <tr  id="ma_sf">
          <td width="65%" align="right">
          <b>
          <?
           $fecha=split(" ",$comentarios->fields['fecha']);
           echo fecha($fecha[0])." ".$fecha[1]; 
          ?>
          </b>
          </td>
         </tr> 
         <tr id="ma_sf">
          <td align="right">
           <?=$comentarios->fields['usuario']?>
          </td>
        </tr>
       </table>
      </td> 
      <td>
       <textarea rows="4" cols="70" readonly name="coment_<?=$comentarios->fields['id_comentario_merc_trans']?>"><?=$comentarios->fields['texto']?></textarea>
      </td>
     </tr>
     <?
     $comentarios->MoveNext();
    }
    //y luego damos la opcion a guardar uno mas
    ?>
    <tr>
     <td colspan="2"> 
      <table>
       <tr>
        <td width="25%"  id="ma_sf">
         <b>Nuevo Comentario</b>
        </td>
        <td width="75%">
         &nbsp;<textarea rows="4" cols="70" name="nuevo_coment"></textarea>
        </td>
       </tr>
      </table>
     </td>
    </tr>   
   </table>
  </td>
 </tr> 
</table>
<br>
<table width="80%" align="center">
 <tr>
  <td align="center">
   <input type="submit" name="guardar" value="Guardar" 
     onclick="if(document.all.nuevo_coment.value=='')
              {alert('No se puede guardar un comentario vacio');
               return false;
              } 
             "
   >
   <input type="button" name="volver" value="Volver" onclick="document.location='listado_merc_trans.php'">
  </td>
 </tr>
</table>  
</form>
</body>
</html>