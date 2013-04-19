<?php
require_once("../../config.php");

//Inicio de transaccion
$db->StartTrans();


//actualizamos los datos de la configuracion de maquina
$prod=$_POST["producto"];
$mod=$_POST["modelo_cdr"];
$adi=$_POST["adicionales"];
$man=$_POST["manuales"];
$nro_orden=$_POST['numero'];
$garantia_equipo=$_POST['garantia'];
$ult_query="select id_configuracion as id_conf from orden_de_produccion where nro_orden= ".$nro_orden;
$resultado=$db->Execute($ult_query) or die($db->ErrorMsg()."en 0");
$id_conf=$resultado->fields["id_conf"];
$query_maq="update configuracion_maquina set producto='".$prod."' ,modelo='".$mod."',adicionales='".$adi."' ,manuales='".$man."' where id_configuracion=".$id_conf;
$db->Execute($query_maq) or die($db->ErrorMsg()."en 1");

$tipo=0;//el tipo comienza en 0, insertamos una motherboard.
        //Luego se va incrementando para insertar los otros componentes.
//este switch decide cual es el postfijo correspondiente a las variables
//de componente de acuerdo a la variable $tipo.
while($tipo<12)
{switch($tipo)
 {case(0):$postfijo="so";break;
  case(1):$postfijo="mother";break;
  case(2):$postfijo="video";break;
  case(3):$postfijo="sonido";break;
  case(4):$postfijo="red";break;
  case(5):$postfijo="modem";break;
  case(6):$postfijo="micro";break;
  case(7):$postfijo="mem";break;
  case(8):$postfijo="hdd";break;
  case(9):$postfijo="graba";break;
  case(10):$postfijo="dvd";break;
  case(11):$postfijo="cd";break;
 }//fin switch
 $marca=$_POST["esp1_".$postfijo];
 $modelo=$_POST["esp2_".$postfijo];
 $prov=$_POST["proveedor_".$postfijo];
 $especificacion3=$_POST["esp3_".$postfijo];
 $especificacion4=$_POST["esp4_".$postfijo];
 $garantia=$_POST["garantia_".$postfijo];
 $observ=$_POST["observ_".$postfijo];
 $query_comp="update componentes set esp1='$marca',esp2='$modelo',esp3='$especificacion3',esp4='$especificacion4',observaciones_componente='$observ',garantia='$garantia',nombre_proveedor='$prov' where id_configuracion=$id_conf and tipo=$tipo";
 $db->Execute($query_comp) or die($db->ErrorMsg()."en 2");
 
 $fact=$_POST["fact_".$postfijo];

  $query_fact="select id_componente as id_c from componentes where id_configuracion=".$id_conf." and tipo=".$tipo;
  $resultado=$db->Execute($query_fact) or die($db->ErrorMsg()."en 3");
  $id_comp=$resultado->fields["id_c"];
  
  if($fact!=''){
  $query_fact="update factura_componente set nro_factura='".$fact."' where id_componente=".$id_comp;
  $db->Execute($query_fact) or die($db->ErrorMsg()."en 4");
  }
 $tipo++;
}

//actualizamos el cliente en la BD con solo el nombre por ahora. En una version futura,
$query_cl="select id_cliente from orden_de_produccion where nro_orden=".$nro_orden;
$resultado=$db->Execute($query_cl) or die($db->ErrorMsg()."en 5");
$id=$resultado->fields["id_cliente"];
$nomb_cl=$_POST["cliente"];
$query_cl="update cliente_final set nombre='".$nomb_cl."' where id_cliente=".$id;
$db->Execute($query_cl) or die($db->ErrorMsg()."en 6");


$f_ini=Fecha_db($_POST['f_inicio']); //aqui se debe tomar la fecha actual (falta implementar)
$f_ent=Fecha_db($_POST['f_entrega']); //aqui se deberia tomar el valor del campo f_inicio
                       //que viene desde la pagina "nueva_orden"
$l_ent=$_POST["l_entrega"]; //el lugar de entrega
$tgar=encod_garantia($_POST["garantia_cdr"]);//tipo de garantia
$fin_gar=$_POST["fin_garantia"];//fin de la garantia
$status=0; //las ordenes de produccion se guardan siempre con estado de
           //pendiente.

//dado el nombre del ensamblador obtenemos el codigo del ensamblador
$primer_ser=$_POST["serial1"];
$ultimo_ser=$_POST["serial2"];
$nb_ens=$_POST["ensamble"];
$query_ens="select id_ensamblador from ensamblador where nombre='$nb_ens'";
$resultado=$db->Execute($query_ens) or die($db->ErrorMsg()."en 7");
$nro_ens=$resultado->fields["id_ensamblador"];
$cantidad=$_POST["cant"];
$query_orden="update orden_de_produccion set id_ensamblador=".$nro_ens.",id_cliente=".$id.",id_configuracion=".$id_conf.",fecha_inicio='".$f_ini."',fecha_entrega='".$f_ent."',lugar_entrega='".$l_ent."',primera_maquina='".$primer_ser."',ultima_maquina='".$ultimo_ser."',garantia='".$garantia_equipo."' where nro_orden=".$nro_orden;
$db->Execute($query_orden) or die($db->ErrorMsg()."en 8");

//actualizamos el monitor
if (($_POST['marca_monitor']!="Ninguno") && ($_POST['marca_monitor']!=""))
 {//usamos el nro de orden que acabamos de insertar.
  $marca=$_POST['marca_monitor'];
  $modelo=$_POST['modelo_monitor'];
  $prov=$_POST['proveedor_monitor'];
  $pulgadas=$_POST['pulgadas_monitor'];
  $garantia=$_POST['garantia_monitor'];
  if ($_POST['exist_monitor']==0)
  {$query_mon="insert into monitor (nombre_proveedor,marca,modelo,pulgadas,garantia,nro_orden) values('".$prov."','".$marca."','".$modelo."',".$pulgadas.",'".$garantia."',".$nro_orden.");";
   $db->Execute($query_mon) or die($db->ErrorMsg()."<br>".$query_mon);
  } 
  else 
  {$query_mon="update monitor set nombre_proveedor='".$prov."',marca='".$marca."',modelo='".$modelo."',pulgadas=".$pulgadas.",garantia='".$garantia."' where nro_orden=".$nro_orden;
   $db->Execute($query_mon) or die($db->ErrorMsg()."en 9");
  }
  $query_fact="select id_monitor from monitor where nro_orden=".$nro_orden;
  $resultado=$db->Execute($query_fact) or die($db->ErrorMsg()."en 10-1");
  $id_comp=$resultado->fields["id_monitor"];
  $fact=$_POST['fact_monitor'];
  if ($fact!=''){
  $query_fact="update factura_monitor set nro_factura=".$fact." where id_monitor=".$id_comp;
  $res=$db->Execute($query_fact) or die($db->ErrorMsg()."en 11");
  if ($db->Affected_Rows()==0){
    $query_fact="insert into factura_monitor (nro_factura,id_monitor) values (".$fact.",".$id_comp.");";
    $db->Execute($query_fact) or die($db->ErrorMsg()."en 11");
   }
  }
  
 }// fin if monitor
else //hay que borrar el monitor
{$query_fact="select id_monitor from monitor where nro_orden=".$nro_orden;
 $resultado=$db->Execute($query_fact) or die($db->ErrorMsg()."en 10-2");
 $id_comp=$resultado->fields["id_monitor"];
 if ($id_comp) {
	 $query_fact="delete from factura_monitor where id_monitor=".$id_comp;
	 $resultado=$db->Execute($query_fact) or die($db->ErrorMsg()."en 10-3");
 }
 $query_fact="delete from monitor where nro_orden=".$nro_orden;
 $resultado=$db->Execute($query_fact) or die($db->ErrorMsg()."en 10-4");
}

//actualizamos accesorios
$tipo=0;//el tipo comienza en 0, insertamos una motherboard.
        //Luego se va incrementando para insertar los otros componentes.
//este switch decide cual es el postfijo correspondiente a las variables
//de componente de acuerdo a la variable $tipo.
while($tipo<=2)
{switch($tipo)
 {case(0):$postfijo="tecla";break;
  case(1):$postfijo="mouse";break;
  case(2):$postfijo="parla";break;
 }// fin switch

$query_comp="select id_accesorio from accesorios where nro_orden=".$nro_orden." and tipo=".$tipo;
 $resultado=$db->Execute($query_comp) or die($db->ErrorMsg()."en 12");
 $id_a=$resultado->fields["id_accesorio"];
 $modelo=$_POST["esp1_".$postfijo];
 $desc=$_POST["observ_".$postfijo];	
 if ($id_a) {
	 $query_comp="update accesorios set esp1='".$modelo."',descripcion='".$desc."'where tipo=".$tipo." and id_accesorio=".$id_a;
	 $db->Execute($query_comp) or die($db->ErrorMsg()."en 13");
 }
 $tipo++;
}//fin while

//actualizamos microfono y floppy
$tipo=3;
$query_comp="select id_accesorio from accesorios where nro_orden=".$nro_orden." and tipo=3";
$db->Execute($query_comp) or die($db->ErrorMsg()."en 14");
$id_a=$resultado->fields["id_accesorio"];
$lleva=$_POST['lleva_microfono'];
if ($lleva!='on')
$lleva='off';
$desc=$_POST['desc_fono'];
if ($id_a) {
	$query_comp="update accesorios set esp1='".$lleva."',descripcion='".$desc."' where id_accesorio=".$id_a." and tipo=3";
	$db->Execute($query_comp) or die($db->ErrorMsg()."en 15");
}

 $tipo=4;
$lleva=$_POST['lleva_floppy'];
if ($lleva!='on')
 $lleva='off';
$desc="";
$query_comp="select id_accesorio from accesorios where nro_orden=".$nro_orden." and tipo=4";
 $resultado=$db->Execute($query_comp) or die($db->ErrorMsg()."en 16");
 $id_a=$resultado->fields["id_accesorio"];
 if ($id_a) {
	 $query_comp="update accesorios set esp1='".$lleva."',descripcion='".$desc."' where id_accesorio=".$id_a." and tipo=4";
	 $db->Execute($query_comp) or die($db->ErrorMsg()."en 17");
 }
//insertamos las maquinas
$cantidad=$_POST["cant"];
$i=1;
$s=$_POST["primero"];  //obtengo primer maquina a insertar
$parte=$_POST["parte"]; //obtengo la primera parte del serial
$dato=$_POST["dato"];
if($_POST['dato']==1){
/* borro los viejos numeros para volver a insertarlos*/
$query_maq="delete from maquina where nro_orden=".$nro_orden;
$db->Execute($query_maq) or die($db->ErrorMsg()."<br>".$query_maq);
$ser='';
$ser.=$parte; //concateno primer parte
if ($s<100) //concateno valor con los 0 que pueden llegar a faltar
  $ser.='0';
if ($s<10)
  $ser.='0';
$ser.=$s;
//$query_maq="update maquina set nro_serie='".$ser."'where nro_orden=".$nro_orden;
//ejecutar_query($query_maq);
$query_maq="insert into maquina (nro_serie,nro_orden,disponible) values('$ser',$nro_orden,'f');";
$db->Execute($query_maq) or die($db->ErrorMsg()."<br>".$query_maq."19");
$i++;
while ($i<=$cantidad)
 {$s=($s+1)%1000; //obtengo siguiente maquina a inserta
   if ($s==000)         //debo cambiar la letra del serial
  {$p1=substr($parte,0,7);  //obtengo la primer parte del string
   $l=substr($parte,8);     //obtengo la letra actual del serial
   $l=chr(ord($l)+1);
   $parte='';
   $parte.=$pl;
   $parte.=$l;
  }//fin if
  $ser='';
  $ser.=$parte; //concateno primer parte
  if ($s<100) //concateno valor con los 0 que pueden llegar a faltar
   $ser.='0';
  if ($s<10)
   $ser.='0';
  $ser.=$s;
 $query_maq="insert into maquina (nro_serie,nro_orden,disponible) values('$ser',$nro_orden,'f');";
 $db->Execute($query_maq) or die($db->ErrorMsg()."<br>".$query_maq."20");
 $i++;
 }// fin while
}//end if($dato)
 $desc="";
 $desc=$_POST['desc_soft'];
 $observ=$_POST['observ_soft'];
//actualizamos los software adicionales si los hay
$ssql="select id_soft from software where nro_orden=".$nro_orden;
$resultado=$db->Execute($ssql) or die($db->ErrorMsg()."en 21");
$id_soft=$resultado->fields['id_soft'];
if($id_soft!=''){
$query_soft="update software set descripcion='".$desc."' ,observaciones='".$observ."' where id_soft=".$id_soft;
$db->Execute($query_soft) or die($db->ErrorMsg()."en 22");
}
else{
	$query_soft="insert into software (descripcion,observaciones,nro_orden)
    values('$desc','$observ',$nro_orden);";
    $db->Execute($query_soft) or die($db->ErrorMsg()."en 23");
} 

//cierra transaccion
$db->CompleteTrans();
header('location: ./ordenes_ver.php');
?>