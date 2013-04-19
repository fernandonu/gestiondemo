<?
/*
$Creador: El programador desconocido john doe$

$Author: fernando $
$Revision: 1.14 $
$Date: 2006/01/05 20:11:32 $
*/

require_once("../../config.php");

//print_r($_POST);
$db->StartTrans();
$sql="select id_licitacion,id_entrega_estimada from entrega_estimada where id_licitacion=".$_POST['id']." and nro=".$_POST['nro'];
$resultado=sql($sql) or fin_pagina();
if ($resultado->RecordCount()>0) //verifico que se haya insertado anteriormente
{if ($_POST['ensamblador']!=0)
  {$sql="update entrega_estimada set id_ensamblador=".$_POST['ensamblador'].", fecha_quemado=".(($_POST['fecha_quemado']=="")?'NULL':"'".fecha_db($_POST['fecha_quemado'])."'").", fecha_auditoria=".(($_POST['fecha_auditoria']=="")?'NULL':"'".fecha_db($_POST['fecha_auditoria'])."'").", resultado_quemado='".$_POST['resultado_quemado']."', resultado_auditoria='".$_POST['resultado_auditoria']."', productos='".$_POST['productos']."', transporte='".$_POST['transporte']."', observaciones='".$_POST['observaciones']."', fecha_estimada=".(($_POST['fecha_entrega']=="")?'NULL':"'".fecha_db($_POST['fecha_entrega'])."'").",comprado=".$_POST['comprado'].",responsable='".$_POST['responsable']."' where id_licitacion=".$_POST['id']." and nro=".$_POST['nro'];
   sql($sql) or fin_pagina();
  }
 else
  {$sql="update entrega_estimada set id_ensamblador=NULL,fecha_quemado=".(($_POST['fecha_quemado']=="")?'NULL':"'".fecha_db($_POST['fecha_quemado'])."'").", fecha_auditoria=".(($_POST['fecha_auditoria']=="")?'NULL':"'".fecha_db($_POST['fecha_auditoria'])."'").", resultado_quemado='".$_POST['resultado_quemado']."', resultado_auditoria='".$_POST['resultado_auditoria']."', productos='".$_POST['productos']."', transporte='".$_POST['transporte']."', observaciones='".$_POST['observaciones']."', fecha_estimada=".(($_POST['fecha_entrega']=="")?'NULL':"'".fecha_db($_POST['fecha_entrega'])."'").", comprado=".$_POST['comprado'].",responsable='".$_POST['responsable']."' where id_licitacion=".$_POST['id']." and nro=".$_POST['nro'];
   sql($sql) or fin_pagina();
  }
}
else //inserto nuevo registro
{if ($_POST['ensamblador']!=0)
  {$sql="insert into entrega_estimada (nro,id_licitacion, id_ensamblador, fecha_quemado,fecha_auditoria,resultado_quemado,resultado_auditoria,productos,transporte,observaciones,fecha_estimada,comprado,responsable) values(".$_POST['nro'].",".$_POST['id'].",".$_POST['ensamblador'].",".(($_POST['fecha_quemado']=="")?'NULL':"'".fecha_db($_POST['fecha_quemado'])."'").",".(($_POST['fecha_auditoria']=="")?'NULL':"'".fecha_db($_POST['fecha_auditoria'])."'").",'".$_POST['resultado_quemado']."','".$_POST['resultado_auditoria']."','".$_POST['productos']."','".$_POST['transporte']."','".$_POST['observaciones']."',".(($_POST['fecha_entrega']=="")?'NULL':"'".fecha_db($_POST['fecha_entrega'])."'").",".$_POST['comprado'].",'".$_POST['responsable']."')";
   sql($sql) or fin_pagina();
   $sql2="select max(id_entrega_estimada) as id_entrega_estimada from entrega_estimada";
   $resultado=sql($sql2) or fin_pagina();
  }
 else
  {$sql="insert into entrega_estimada (nro,id_licitacion, fecha_quemado,fecha_auditoria,resultado_quemado,resultado_auditoria,productos,transporte,observaciones,fecha_estimada,comprado,responsable) values(".$_POST['nro'].",".$_POST['id'].",".(($_POST['fecha_quemado']=="")?'NULL':"'".fecha_db($_POST['fecha_quemado'])."'").",".(($_POST['fecha_auditoria']=="")?'NULL':"'".fecha_db($_POST['fecha_auditoria'])."'").",'".$_POST['resultado_quemado']."','".$_POST['resultado_auditoria']."','".$_POST['productos']."','".$_POST['transporte']."','".$_POST['observaciones']."',".(($_POST['fecha_entrega']=="")?'NULL':"'".fecha_db($_POST['fecha_entrega'])."'").",".$_POST['comprado'].",'".$_POST['responsable']."')";
   sql($sql) or fin_pagina();
   $sql2="select max(id_entrega_estimada) as id_entrega_estimada from entrega_estimada";
   $resultado=sql($sql2) or fin_pagina();
  }
}
//$resultado=


//borro los productos en material_produccion2 para insertar los nuevos chequeos


$sql="delete from material_produccion2 where id_entrega_estimada=".$resultado->fields['id_entrega_estimada'];
sql($sql) or fin_pagina();
$cant=count($_POST['check']);
$cant--;
while ($cant>=0)
 {$sql="insert into material_produccion2 (tiene,id_entrega_estimada,id_fila) values('t',".$resultado->fields['id_entrega_estimada'].",".$_POST['check'][$cant].")";
  sql($sql) or fin_pagina();
  $cant--;
 }

if ($_POST['boton']=="Finalizar Seguimiento")
{
  $sql="update entrega_estimada set finalizada=1 where id_entrega_estimada=".$resultado->fields['id_entrega_estimada'];
  sql($sql) or fin_pagina();
}

$db->CompleteTrans();

//fin_pagina();
header("location: ver_seguimiento_ordenes.php");
?>