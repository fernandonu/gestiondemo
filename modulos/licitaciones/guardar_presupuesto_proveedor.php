<?
/*
modificado por
$Author: Fernando
$Revision: 1.11 $
$Date: 2004/08/27 23:19:47 $
*/


require_once("../../config.php");

//print_r($_POST);
$db->StartTrans();
$k=2; //columna que ascocia proveedores
$sql="update licitacion_presupuesto_new set comentarios='$_POST[comentarios]' where id_licitacion_prop=$id_prop";
$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

while($k<($_POST['columnas_1']+2) ) //recorro por proveedores
{

      if(($_POST['hidden_insertar_'.$k]==1)&&($_POST['hidden_existe_1_'.$k]==0)) // no existe el proveedor ligado a este presupuesto asi que lo inserto en la BD
          {
           $sql="insert into proveedor_presupuesto_new(id_proveedor,id_licitacion_prop) values(".$_POST['hidden_idprov_'.$k].",".$_POST['id_prop'].")";
           $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

          }
     elseif (($_POST['hidden_insertar_'.$k]==0)&&($_POST['hidden_existe_1_'.$k]==1)) //existe pero lo elimino
            $sql_prov="delete from proveedor_presupuesto_new where id_proveedor=".$_POST['hidden_idprov_'.$k]." and id_licitacion_prop=".$_POST['id_prop'];

     $i=1;
     while($i<=$_POST['cant_renglones'])
         {
         $j=2;
            while($j<=($_POST['cant_prod_reng_'.$i])+1)
             {//El producto ya existe en la BD,actualizo
              $_POST['texto_precio_'.$i.'_'.$j.'_'.$k]=($_POST['texto_precio_'.$i.'_'.$j.'_'.$k]=="")?0:$_POST['texto_precio_'.$i.'_'.$j.'_'.$k];
              $_POST['texto_precio_'.$i.'_'.$j.'_'.$k]=ereg_replace (',','.',$_POST['texto_precio_'.$i.'_'.$j.'_'.$k]);

             if(($_POST['hidden_insertar_'.$k]==1)&&($_POST['hidden_existe_1_'.$k]==1))
                {
                 $sql="update producto_proveedor_new set monto_unitario=".$_POST['texto_precio_'.$i.'_'.$j.'_'.$k].", comentario='".$_POST['hidden_comentario_'.$i.'_'.$j.'_'.$k]."', activo=".$_POST['hidden_precio_elegido'.$i."_".$j."_".$k]."
                       where id_producto_presupuesto=".$_POST['producto_'.$i.'_'.($j)]." and id_proveedor=".$_POST['hidden_idprov_'.$k];
                }
            //El producto no existe en la BD,inserto
            if(($_POST['hidden_insertar_'.$k]==1)&&($_POST['hidden_existe_1_'.$k]==0))
               {
               $sql="insert into producto_proveedor_new
                     (monto_unitario,comentario,activo,id_producto_presupuesto,id_proveedor)
                      values(".$_POST['texto_precio_'.$i.'_'.$j.'_'.$k].",'".$_POST['hidden_comentario_'.$i.'_'.$j.'_'.$k]."',".$_POST['hidden_precio_elegido'.$i."_".$j."_".$k].",".$_POST['producto_'.$i.'_'.($j)].",".$_POST['hidden_idprov_'.$k].")";

               }
            //El proveedor fue eliminado del presupuesto
           if(($_POST['hidden_insertar_'.$k]==0)&&($_POST['hidden_existe_1_'.$k]==1))
              {
              $sql="delete from producto_proveedor_new
                    where id_producto_presupuesto=".$_POST['producto_'.$i.'_'.($j)]."
                    and id_proveedor=".$_POST['hidden_idprov_'.$k];
              }
          
          $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
         //echo "Renglon i=$i<br>";
        //echo "Producto j=$j<br>";
        //echo "Proveedor k=$k<br>";
        $j++;
       }
  if($sql_prov!="") //elimino proveedor del renglon
   $db->Execute($sql_prov) or die ($db->ErrorMsg()."<br>".$sql);
  $i++;
 }
 $k++;
}
$db->CompleteTrans();
//si viene id_proveedor => hay que hacer la Orden de Compra
if ($_POST['id_proveedor'])
{
  $link=encode_link("../ord_compra/ord_compra.php",array("licitacion"=>$_POST['id'],"select_proveedor"=>$_POST['id_proveedor'],"id_renglon_prop"=>$_POST['id_renglon_prop'],"select_contacto"=>-2,"select_pago"=>4,"select_moneda"=>2));	
}
else
	$link=encode_link("detalle_presupuesto.php",array("ID"=>$_POST['id'],"id_lic_prop"=>$_POST['id_prop'],"id_entrega_estimada"=>$id_entrega_estimada,"id_subir"=>$id_subir,"nro_orden_cliente"=>$nro_orden_cliente));
header("location: $link");
?>