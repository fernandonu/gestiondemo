<?
/*
Autor: mariela
Modificado por:
$Author: ferni $
$Revision: 1.2 $
$Date: 2007/01/25 17:59:56 $
*/

require_once("../../config.php");
echo $html_header;

$db->StartTrans();
  $id_subir=$_GET['id_subir'];
  $col=$_GET['col'];
  $chk=$_GET['chk'];
  
  $fecha_hoy = date("Y-m-d",mktime());
  $usuario= $_ses_user['name'];
  
 
  $sql="select id_control_seguimiento from ordenes.control_seguimiento
        where id_subir=$id_subir";
  $res=sql($sql,"$sql") or fin_pagina();  

if ($res->recordCount()>0) {  //update
  switch ($col) {
    case 1:$dato="falta_oc=$chk";
         break;
    case 2:$dato="falta_pm=$chk";
         break;
    case 3:$dato="falta_op=$chk";
         break;
    case 4:$dato="no_llego_oc=$chk";
         break;
    case 5:$dato="todo_ok=$chk";
         break;
    }
    $dato.=",ultima_modificacion_fecha='$fecha_hoy',ultima_modificacion_usuario='$usuario'";
    $sql="update ordenes.control_seguimiento set $dato where id_subir=$id_subir";
  }
else {
  	$campos="falta_oc,falta_pm,falta_op,no_llego_oc,todo_ok,ultima_modificacion_fecha,ultima_modificacion_usuario,id_subir";
  	
  	switch ($col) {
    case 1:$valores="1,0,0,0,0";
         break;
    case 2:$valores="0,1,0,0,0";
         break;
    case 3:$valores="0,0,1,0,0";
         break;
    case 4:$valores="0,0,0,1,0";
         break;
    case 5:$valores="0,0,0,0,1";
         break;
    }
    $valores.=",'$fecha_hoy','$usuario',$id_subir";
  	
  $sql="insert into ordenes.control_seguimiento ($campos) values($valores)";
  }
  

sql($sql,"$sql") or fin_pagina();
if ($db->CompleteTrans()) {
        ?>
        <script>
           window.close();
        </script>        
        <?
}
else {
 Error ("Error al guardar datos");
}?>

</body>
