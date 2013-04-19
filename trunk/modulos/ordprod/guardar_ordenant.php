<?php
require_once("../../config.php");
/* modificacion de guardar orden temporal para que se cargen ordenes viejas
*/

//Inicio de transaccion
$db->StartTrans();


//insertamos los datos de la configuracion de maquina
$nro_orden=$_POST["nrord"];//nro de orden que ingresa data entry
$ssql="select nro_orden from orden_de_produccion where nro_orden=".$nro_orden;
$resultado = $db->Execute($ssql) or Error($ssql);
if($resultado->fields["nro_orden"]==""){
$prod=$_POST["producto"];
$mod=$_POST["modelo_cdr"];
$adi=$_POST["adicionales"];
$man=$_POST["manuales"];
$query_maq="insert into configuracion_maquina (producto,modelo,adicionales,manuales) values ('$prod','$mod','$adi','$man');";
$db->Execute($query_maq) or Error($db->ErrorMsg());
//el query que sigue deberia ser reemplazado por una variable que 
//contiene el ultimo id_configuracion utilizado,
//que es el que se acaba de insertar.
db_tipo_res('a');
$ult_query="select MAX(id_configuracion) from configuracion_maquina";
$resultado=$db->Execute($ult_query) or Error($ult_query);
$id_conf=$resultado->fields["max"];

//insertamos los 13 componentes y las facturas correspondientes, si es que 
//fueron completadas.
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
 $query_comp="insert into componentes(id_configuracion,tipo,esp1,esp2,esp3,esp4,observaciones_componente,garantia,nombre_proveedor)
 values($id_conf,$tipo,'$marca','$modelo','$especificacion3','$especificacion4','$observ','$garantia','$prov')";
 $db->Execute($query_comp) or Error($query_comp);
 
 //si el componente tiene factura, la insertamos (si no existe ya)
 //y la relacionamos con el componente que acabamos de insertar.
 $fact=$_POST["fact_".$postfijo];
 //el query que sigue deberia ser reemplazado por una variable que 
 //contiene el ultimo id_componente utilizado,
 //que es el que se acaba de insertar.
  db_tipo_res('a');
  $query_fact="select MAX(id_componente) from componentes";
  $resultado=$db->Execute($query_fact) or Error($query_fact);
  $id_comp=$resultado->fields["max"];
  //luego se debe relacionar esa factura insertada, 
  //con el componente correspondienteque fue el ultimo insertado.
  $query_fact="insert into factura_componente (id_componente,nro_factura)
              values($id_comp,'$fact')"; 
 $db->Execute($query_fact) or Error($query_fact);
 $tipo++;
}

//insertamos el cliente en la BD con solo el nombre por ahora. En una version futura,
//el cliente se insertara solo si no existe antes, en la BD de clientes.
$nomb_cl=$_POST["cliente"];
$query_cl="insert into cliente_final (nombre,direccion,tel,comentarios,email)
values('$nomb_cl','falta completar en el programa','','','')";
$db->Execute($query_cl) or Error($query_cl);

//insertamos la orden de produccion propiamente dicha (la insersion
//es en la tabla orden_de_produccion)
//busco id del cliente
db_tipo_res('a');
$query_cl="select MAX(id_cliente) from cliente_final";
$resultado=$db->Execute($query_cl) or Error($query_cl);
$cliente=$resultado->fields["max"];

$f_ini=Fecha_db($_POST['f_inicio']); //aqui se debe tomar la fecha actual (falta implementar)
$f_ent=Fecha_db($_POST['f_entrega']); //aqui se deberia tomar el valor del campo f_inicio
                       //que viene desde la pagina "nueva_orden"
$l_ent=$_POST["l_entrega"]; //el lugar de entrega
$tgar=$_POST["garantia_cdr"];//tipo de garantia
$fin_gar=$_POST["fin_garantia"];//fin de la garantia
$status=0; //las ordenes de produccion se guardan siempre con estado de
           //pendiente.

//dado el nombre del ensamblador obtenemos el codigo del ensamblador
$primer_ser=$_POST["serial1"];
$ultimo_ser=$_POST["serial2"];
$nb_ens=$_POST["ensamble"];
$query_ens="select id_ensamblador from ensamblador where nombre='$nb_ens'";
$resultado=$db->Execute($query_ens) or Error($query_ens);
//modificacion temporaria para asignar un nº de ensamblador
$nro_ens=$resultado->fields["id_ensamblador"];
$cantidad=$_POST["cant"];

//el cliente se llena "a mano" por ahora, hasta que se implemente la 
//parte de cargar clientes desde una bd ya existente (desde un desplegable).


$query_orden="insert into orden_de_produccion(nro_orden,id_ensamblador,id_cliente,id_configuracion,fecha_inicio,fecha_entrega,lugar_entrega,estado,cantidad,primera_maquina,ultima_maquina,aprobada)
values($nro_orden,$nro_ens,$cliente,$id_conf,'$f_ini','$f_ent','$l_ent',0,$cantidad,'$primer_ser','$ultimo_ser',0)";
$db->Execute($query_orden) or Error($query_orden."<br>".$db->ErrorMsg());
/*
$query_orden="select MAX(nro_orden) from orden_de_produccion";
ejecutar_query($query_orden);
$nro_orden=$resultado[0]["max"];  
*/
//insertamos el monitor
if (($_POST['marca_monitor']!="Ninguno") && ($_POST['marca_monitor']!=""))
 {//usamos el nro de orden que acabamos de insertar.
  $marca=$_POST['marca_monitor'];
  $modelo=$_POST['modelo_monitor'];
  $prov=$_POST['proveedor_monitor'];
  $pulgadas=$_POST['pulgadas_monitor'];
  $garantia=$_POST['garantia_monitor'];

  $query_mon="insert into monitor(nro_orden,nombre_proveedor,marca,modelo,pulgadas,garantia) values($nro_orden,'$prov','$marca','$modelo',$pulgadas,'$garantia');";
  $db->Execute($query_mon) or Error($query_mon);
  $query_fact="select MAX(id_monitor) from monitor";
  db_tipo_res('a');
  $resultado=$db->Execute($query_fact) or Error($query_fact);
  $id_comp=$resultado->fields["max"];
  $fact=$_POST['fact_monitor']; 
  $query_fact="insert into factura_monitor(id_monitor,nro_factura)
  values($id_comp,'$fact')"; 
  $db->Execute($query_fact) or Error($query_fact);
 }// fin if monitor
 
//insertamos accesorios
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
 
 $modelo=$_POST["esp1_".$postfijo];
 $desc=$_POST["observ_".$postfijo];
 $query_comp="insert into accesorios(nro_orden,tipo,esp1,descripcion)
 values($nro_orden,$tipo,'$modelo','$desc')";
 $db->Execute($query_comp) or Error($query_comp);
 $tipo++;
}

//insertamos microfono y floppy
$tipo=3;
$lleva=$_POST['lleva_microfono'];
if ($lleva!='on')
 $lleva='off';
$desc=$_POST['observ_microfono'];
$query_comp="insert into accesorios(nro_orden,tipo,esp1,descripcion)values($nro_orden,$tipo,'$lleva','$desc')";
$db->Execute($query_comp) or Error($query_comp);

$tipo=4;
$lleva=$_POST['lleva_floppy'];
if ($lleva!='on')
 $lleva='off';
$desc="";
$query_comp="insert into accesorios(nro_orden,tipo,esp1,descripcion)values($nro_orden,$tipo,'$lleva','$desc')";
$db->Execute($query_comp) or Error($query_comp);

//insertamos las maquinas 
$cantidad=$_POST["cant"];
$i=1;
$s=$_POST["primero"];  //obtengo primer maquina a insertar
$parte=$_POST["parte"]; //obtengo la primera parte del serial
$ser='';
$ser.=$parte; //concateno primer parte
if ($s<100) //concateno valor con los 0 que pueden llegar a faltar
  $ser.='0';
if ($s<10)
  $ser.='0';
$ser.=$s;
$query_maq="insert into maquina values('$ser',$nro_orden,'f');";
$db->Execute($query_maq) or Error($query_maq);
$i++;
while ($i<=$cantidad)
 {$s=$s+1%1000; //obtengo siguiente maquina a insertar
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
  $query_maq="Insert into maquina values('$ser',$nro_orden,'f');";
  $db->Execute($query_maq) or Error($query_maq);
  $i++; 
  }// fin while

//insertamos los software adicionales si los hay
if ($_POST['desc_soft']!="")
{$desc="";
 $desc.=$_POST['desc_soft']." ";
 $observ=$_POST['observ_soft']." ";;
 $query_soft="Insert into software (descripcion,observaciones,nro_orden)
 values('$desc','$observ',$nro_orden);";
 $db->Execute($query_soft) or Error($query_soft);
}

 
//una vez guardada la orden, incrementamos el nro para
//seguir llenando ordenes.
$nro_orden++;
}//if nro_orden no existe


//cierra transaccion
$db->CompleteTrans();

header('location: ./ordenes_ver.php');

?>