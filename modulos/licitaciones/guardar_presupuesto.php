<?
/*
modificado por
$Author: Fernando
$Revision: 1.21 $
$Date: 2004/09/21 15:20:36 $
*/

require_once("../../config.php");


$db->StartTrans();
//licitación
if ($_POST['id_prop']=="") //inserto nuevo presupuesto
 {
 $fecha=($_POST['fecha_cotizacion']=="")?'NULL':fecha_db($_POST['fecha_cotizacion'])." ".$_POST['hora'].":".$_POST['minutos'].":00";
 if ($fecha=='NULL')
 {
  $sql="insert into licitacion_presupuesto_new(id_licitacion,id_entrega_estimada,titulo,estado,fecha_cotizacion) values(".$_POST['id'].",$id_entrega_estimada,'".$_POST['titulo']."',0,$fecha);";
  $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 else
 {
  $sql="insert into licitacion_presupuesto_new(id_licitacion,titulo,estado,fecha_cotizacion) values(".$_POST['id'].",'".$_POST['titulo']."',0,'$fecha');";
  $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 $sql="select max(id_licitacion_prop) from licitacion_presupuesto_new;";
 $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 $id_licitacion_prop=$resultado->fields['max'];
}
else //actualizo presupuesto ya insertado anteriormente
{
 $fecha=($_POST['fecha_cotizacion']=="")?'NULL':fecha_db($_POST['fecha_cotizacion'])." ".$_POST['hora'].":".$_POST['minutos'].":00";
 if ($fecha=='NULL')
 {
  $sql="update licitacion_presupuesto_new set titulo='".$_POST['titulo']."', estado=0, fecha_cotizacion=$fecha where id_licitacion_prop=".$_POST['id_prop'];
  $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 else
 {
  $sql="update licitacion_presupuesto_new set titulo='".$_POST['titulo']."', estado=0, fecha_cotizacion='$fecha' where id_licitacion_prop=".$_POST['id_prop'];
  $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 $id_licitacion_prop=$_POST['id_prop'];
}

//renglon
$i=1;
while($i<=$_POST['cant_renglones'])
{

 if ($_POST["insertar_renglon_$i"]==1) //insertamos el renglon
           {
           if ($_POST["existe_renglon_$i"]==1)
              {
              $sql="select id_renglon_prop from
                    renglon_presupuesto_new
                    where id_renglon_prop='".$_POST['id_renglon_prop_'.$i]."' and id_licitacion_prop=".$id_licitacion_prop;
              $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
              $id_renglon_prop=$resultado->fields['id_renglon_prop'];
              $sql="update renglon_presupuesto_new set cantidad=".$_POST['cant_renglon_'.$i]."
                    where id_renglon_prop=$id_renglon_prop";
              $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

              }
              else //inserto renglon
                 {
                 $sql="insert into renglon_presupuesto_new(id_licitacion_prop,id_renglon,cantidad)
                       values($id_licitacion_prop,".$_POST['id_renglon_'.$i].",".$_POST['cant_renglon_'.$i].");";
                 $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
                 $sql="select max(id_renglon_prop) from renglon_presupuesto_new;";
                 $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
                 $id_renglon_prop=$resultado->fields['max'];

                 }
         //productos
         $j=1;
         while($j<=$_POST["cant_prod_$i"]) //actualizamos los productos
              {
              if($_POST["prod_".$i."_".$j]==1)
   	                     {
                              if ($_POST["existe_prod_".$i."_".$j]==1) //actualizo producto
   	                            {
                                        $sql="update producto_presupuesto_new set desc_nueva='".$_POST['desc_nueva'.$i."_".$j]."', cantidad=".$_POST['cantidad_'.$i."_".$j]."
                                               where id_producto_presupuesto=".$_POST['id_prod_'.$i."_".$j];
                                        $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   	                             } //inserto producto
   	                             else
   	                                {
                                        $sql="insert into producto_presupuesto_new(id_producto,desc_nueva,id_renglon_prop,cantidad)
                                              values(".$_POST['id_prod2_'.$i."_".$j].",'".$_POST['desc_nueva'.$i."_".$j]."',$id_renglon_prop,".$_POST['cantidad_'.$i."_".$j].");";
   	                                $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

   	                                }
   	                     }
   	                     else //borro producto de presupuesto
   	                    {
                            if ($_POST["existe_prod_".$i."_".$j]==1)
   	                                 {
                                         $sql="delete from producto_proveedor_new where id_producto_presupuesto=".$_POST['id_prod_'.$i."_".$j];
                                         $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   	                                 $sql="delete from producto_presupuesto_new where id_producto_presupuesto=".$_POST['id_prod_'.$i."_".$j];
                                         $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
   	                                 }
   	                   }//del else
         $j++;
   	 }

  }
 else
 {//borramos renglon si ya existía
  //echo "entra al con coca$i<br>";
  if ($_POST["existe_renglon_$i"]==1) //borro el renglon
   {//borro productos de la tabla de produxctos_proveedor
    $k=1;
    while($k<=$_POST["cant_prod_$i"])
          {
          $sql="delete from producto_proveedor_new where id_producto_presupuesto=".$_POST['id_prod_'.$i.$k];
          $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
          $k++;
          }
    $sql="delete from producto_presupuesto_new where id_renglon_prop=".$_POST['id_renglon_prop_'.$i];
    $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
    $sql="delete from renglon_presupuesto_new where id_renglon_prop=".$_POST['id_renglon_prop_'.$i];
    $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

    }
  }

 $i++;
}

if($_POST['boton']=="Comenzar pedido de presupuesto")
                   {
                   $sql="update licitacion_presupuesto_new set estado=1 where id_licitacion_prop=".$id_licitacion_prop;
                   $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
                    }


$db->CompleteTrans();
$link=encode_link("../ordprod/seguimiento_orden.php",array("id"=>$_POST['id'],"id_entrega_estimada"=>$id_entrega_estimada,"id_subir"=>$id_subir,"nro_orden_cliente"=>$nro_orden_cliente));
header("location: $link") or die();
?>