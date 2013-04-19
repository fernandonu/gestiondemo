<?

$db->StartTrans();
$i=1;
while($i<=$_POST['cant_productos'])
{
$sql="select monto_unitario from producto_proveedor where id_producto=".$_POST['prod_'.$i]." and id_ensamblador=$sess_user_id";
$resultado_prod=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
$monto=($_POST['precio_'.$i]=="")?0.00:$_POST['precio_'.$i];
if ($resultado_prod->recordcount()>0) //actualizo
 {$sql="update producto_proveedor set monto_unitario=".$_POST['precio_'.$i]." where id_producto=$monto and id_ensamblador=$sess_user_id";
  $resultado_prod=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
 }
 else
 {$sql="insert into producto_proveedor (id_ensamblador,id_producto,monto_unitario) values($sess_user_id,".$_POST['prod_'.$i].",$monto)";
  $resultado_prod=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
 }
$i++;
}
$i=1;
while($i<=$_POST['cant_adicionales'])
{
$sql="select monto_unitario from adicionales_proveedor where id_adicional=".$_POST['adic_'.$i]." and id_ensamblador=$sess_user_id";
$resultado_prod=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
$monto=($_POST['precio2_'.$i]=="")?0.00:$_POST['precio2_'.$i];
if ($resultado_prod->recordcount()>0) //actualizo
 {$sql="update adicionales_proveedor set monto_unitario=$monto where id_adicional=".$_POST['adic_'.$i]." and id_ensamblador=$sess_user_id";
  $resultado_prod=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
 }
 else
 {$sql="insert into adicionales_proveedor (id_ensamblador,id_adicional,monto_unitario) values($sess_user_id,".$_POST['adic_'.$i].",$monto)";
  $resultado_prod=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
 }
$i++;
}
$db->CompleteTrans();
header("location: index.php?pagina=detalle");
?>