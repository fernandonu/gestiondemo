<?
require_once("../../config.php");
$check=array("xt"=>"t","x"=>"f");
//actualizo y elimino los productos anteriormente insertados


$cant_filas_insertadas=$_POST['cant_act'];
$db->StartTrans();
while($cant_filas_insertadas>0)
{if ($check['x'.$_POST['borrar_'.$cant_filas_insertadas]]=="t") //elimino el producto
 {$sql="delete from material_produccion where id_material_produccion=".$_POST['id_material_produccion_'.$cant_filas_insertadas];
  $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 else //actualizo
 {$sql="update material_produccion set producto='".$_POST['producto_'.$cant_filas_insertadas]."', tiene='".$check['x'.$_POST['tiene_'.$cant_filas_insertadas]]."' where id_material_produccion=".$_POST['id_material_produccion_'.$cant_filas_insertadas];
  $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 $cant_filas_insertadas--;
}
//inserto los nuevos productos
$cant_filas=$_POST['cant_filas'];
while($cant_filas>0)
{if ($_POST['producto1_'.$cant_filas]!="")
 {$sql="insert into material_produccion (id_entrega_estimada,producto,tiene) values(".$_POST['id_entrega_estimada'].",'".$_POST['producto1_'.$cant_filas]."','".$check['x'.$_POST['check_'.$cant_filas]]."')";
  $resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
 }
 $cant_filas--;
}
if ($_POST['viene']=="borrar")
{$link=encode_link("detalle_material.php",array("id"=>$_POST['id'],"id_entrega_estimada"=>$_POST['id_entrega_estimada']));
 header("location: $link");
}
else
{$link=encode_link("seguimiento_orden.php",array("id"=>$_POST['id'],"id_entrega_estimada"=>$_POST['id_entrega_estimada']));
 header("location: $link");
}
$db->CompleteTrans();
?>