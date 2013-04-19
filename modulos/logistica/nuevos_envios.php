<?
/*
Autor: Enrique


MODIFICADA POR
$Author: ferni $
$Revision: 1.22 $
$Date: 2006/03/30 14:50:57 $

*/


require_once("../../config.php");
//require_once("fns.php");
//print_r($_POST);
$fecha=fecha_db(date("d/m/Y",mktime()));
$pagina=$_POST['pagina'] or $pagina=$parametros['pagina'];
$serial=$_POST['serial_1'] or $serial=$parametros['serial_1'];
$tipo=$_POST['tipo'] or $tipo=$parametros['tipo'];
$id_envio=$parametros['id_envio_renglones'] or $id_envio_renglones=$_POST['id_envio_renglones'];

if ($pagina=="nuevos_envios") {

$sql_se="select * from envio_renglones where id_envio_renglones=$id_envio";
$res12=sql($sql_se, "Error al traer envio_renglones") or fin_pagina();
$id_cli=$res12->fields['id_entidad'];
$transporte=$res12->fields['id_transporte'];

$sql_sel="select * from licitaciones_datos_adicionales.transporte where id_transporte=$transporte";
$res10=sql($sql_sel, "Error al traer los transporte") or fin_pagina();
$comentario_transporte=$res10->fields['comentarios_transporte'];

$sql_se="select * from envio_renglones where id_envio_renglones=$id_envio";
$res12=sql($sql_se, "Error al traer envio_renglones") or fin_pagina();
$id_cli=$res12->fields['id_entidad'];

$sql_s="select * from datos_envio where id_envio_renglones=$id_envio";
$res11=sql($sql_s, "Error al traer los datos de envio") or fin_pagina();
$origen_envio=$res11->fields['id_envio_origen'];
$destino_envio=$res11->fields['id_envio_destino'];
$cliente=$res11->fields['entidad_mod'];
$entrega=$res11->fields['dir_entrega_mod'];
}
extract($_POST,EXTR_SKIP);

if($_POST['asociar_cas']=='Asociar Servicio Técnico'){
	$link=encode_link("../casos/caso_admin.php",array("backto"=>"../logistica/nuevos_envios.php","pag"=>"asociar","coradir_bs_as"=>"no"));
    header("Location:$link");
}

/*if($_POST['asociar_cas1']=='Asociar Servicio Técnico'){
	$id_envio_reng=$_POST['id_envio'];
	phpss_svars_set("_ses_global_idenvio", "$id_envio_reng");
	phpss_svars_set("_ses_global_flag_envios_cas","1");
	$link=encode_link("../casos/caso_admin.php",array("backto"=>"../logistica/nuevos_envios.php","pag"=>"asociar","coradir_bs_as"=>"no"));
    header("Location:$link");
}
if ($_ses_global_flag_envios_cas==1){
	$id_envio_reng=$_ses_global_idenvio;
    $id_caso=$parametros['id_caso'];
    $num_caso=$parametros['caso'];
    phpss_svars_set("_ses_global_idenvio", "");
	phpss_svars_set("_ses_global_flag_envios_cas","");
	$sql="update licitaciones_datos_adicionales.envio_renglones set idcaso=$id_caso where id_envio_renglones=$id_envio_reng";
	sql($sql,'No se puede desvincular el caso')or fin_pagina();
	$msg="El Servicio Técnico Numero $num_caso se Vinculo Exitosamente del Envio $id_envio_reng";
	$link= encode_link("../ordprod/listado_envios.php",array('msg'=>$msg));
    header("Location:$link");
    die();
}*/

if($_POST['desvincular_cas']=='Desvincular Servicio Técnico'){
	$id_envio_reng=$_POST['id_envio'];
	$idcaso='null';
	$sql="update licitaciones_datos_adicionales.envio_renglones set idcaso=$idcaso where id_envio_renglones=$id_envio_reng";
	sql($sql,'No se puede desvincular el caso')or fin_pagina();
	$msg="El Servicio Técnico se Desvinculo Exitosamente del Envio $id_envio_reng";
	$link= encode_link("../ordprod/listado_envios.php",array('msg'=>$msg));
    header("Location:$link");
    die();
}

if($boton1=="Guardar")
{
 if($_POST['modificar']) 
 {
 $db->StartTrans();
 $trans=$_POST["transporte"];
 $entrega=$_POST["entrega"];
 $cliente=$_POST["cliente"];
 $origen=$_POST["origen"];
 $destino=$_POST["destino"];
 $id_envio=$_POST["id_envio"];
 $id_identidad=$_POST["id_identidad"];



 $query_update="UPDATE envio_renglones set id_transporte=$trans where id_envio_renglones=$id_envio";
 sql($query_update) or fin_pagina();
 $query_update1="UPDATE datos_envio set entidad_mod='$cliente',dir_entrega_mod='$entrega',id_envio_origen=$origen,id_envio_destino=$destino where id_envio_renglones=$id_envio";
 sql($query_update1) or fin_pagina(); 
 $usuario=$_ses_user['name'];
 $q_update="UPDATE licitaciones_datos_adicionales.log_envio_renglones 
                     set  id_envio_renglones=$id_envio, usuario='$usuario', tipo_log='creacion', fecha='$fecha'
                 where id_envio_renglones=$id_envio";
 sql($q_update, "Error al insertar el log del envío") or fin_pagina(); 
 $query="select id_renglones_bultos from renglones_bultos where id_envio_renglones=$id_envio";
 $rs=$db->execute($query) or die($db->errormsg());
 $cant_db=$rs->RecordCount();
 $suma=0;
 while ($fila=$rs->FetchRow()) 
  {
  $j=0;
  while ($j<$_POST["items"]) 
  {
  if ($fila["id_renglones_bultos"]==$_POST["idi_$j"])
  break;
  $j++;
  }
  if ($j>=$_POST["items"]) 
   {
   $qu=" select bultos_ocupados from renglones_bultos where id_renglones_bultos=".$fila["id_renglones_bultos"];
   $su=sql($qu) or fin_pagina();
   $sum=$su->fields['bultos_ocupados'];
   $suma=$suma-$sum; 
   $q="DELETE FROM nro_serie_renglon WHERE id_renglones_bultos=".$fila["id_renglones_bultos"];
   sql($q) or fin_pagina();
   $q="DELETE FROM renglones_bultos WHERE id_renglones_bultos=".$fila["id_renglones_bultos"];
   sql($q) or fin_pagina();
   }
	else 
	{
	$q="UPDATE renglones_bultos SET "
	."cantidad_enviada=".$_POST["cant_$j"]
	.",bultos_ocupados=".$_POST["bul_$j"]
	.",titulo_mod='".$_POST["desc_$j"]
	."' WHERE id_renglones_bultos=".$_POST["idi_$j"];
	sql($q) or fin_pagina();
	$suma=$suma+$_POST["bul_$j"];
	}
  }
  $j=0;
  while ($j<$_POST["items"]) 
   {
   if (!$_POST["idi_$j"] &&($_POST["cant_$j"])) 
   {
   $campos="(id_envio_renglones,cantidad_enviada,titulo_mod,bultos_ocupados)";	
   $query_insert="INSERT INTO renglones_bultos $campos VALUES ".
   "($id_envio,".$_POST["cant_$j"].",'".$_POST["desc_$j"]."',".$_POST["bul_$j"].")";
   $res=sql($query_insert, "Error al insertar renglones_bultos") or fin_pagina();
   $suma=$suma+$_POST["bul_$j"];
	}
   $j++;
   }
   $query_up="update envio_renglones set cantidad_total=$suma where id_envio_renglones=$id_envio";
   $res=sql($query_up, "Error al insertar renglones_bultos") or fin_pagina(); 
   if ($id_entidad=="")
    {
	$sql_se="select id_entidad from envio_renglones where id_envio_renglones=$id_envio";
    $res12=sql($sql_se, "Error al traer envio_renglones") or fin_pagina();
    $id_entidad=$res12->fields['id_entidad'];
    }
   $fecha=date("Y-m-d H:m ");
   $usuario=$_ses_user['id'];
   $sql="select * from licitaciones.usuarios_clientes where id_usuario=$usuario and id_entidad=$id_entidad";
   $resul_select=sql($sql,"No se pudo consulta si ya existia esa entrada usuario-cliente") or fin_pagina();
   if($resul_select->RecordCount()>0)
    {
     $nuevo_peso=$resul_select->fields['peso_uso']+1;
     $sql="update licitaciones.usuarios_clientes set peso_uso=$nuevo_peso,fecha_ultimo_uso='$fecha' where id_usuario=$usuario and id_entidad=$id_entidad";
     $reul_update=sql($sql,"No se pudo realizar el update en la tabla usuarios_clientes") or fin_pagina();
    }
    else 
     {
     $sql="insert into licitaciones.usuarios_clientes (id_usuario,id_entidad,fecha_ultimo_uso,peso_uso,empezo_uso_en)
     values ($usuario,$id_entidad,'$fecha',1,1)";
     $result_insert=sql($sql,"No se pudo realizar el insert en la tabla usuarios_clientes") or fin_pagina();
     }  	
   $comentario="PARA INGRESAR LOS NUMEROS DE SERIE DEBE GUARDAR LOS DATOS PRIMERO";
   $db->CompleteTrans();
   $comentarios1="Los datos se guardaron correctamente";
 }
 else 
  {	
  $db->StartTrans();
  $q="select nextval('envio_renglones_id_envio_renglones_seq') as id_envios_renglones";
  $res=sql($q, "Error la traer secuencia de id de envio_renglones") or fin_pagina();
  $id_envio_renglones=$res->fields['id_envios_renglones'];
  $trans=$_POST["transporte"];
  $entrega=$_POST["entrega"];
  $cliente=$_POST["cliente"];
  $origen=$_POST["origen"];
  $destino=$_POST["destino"];
  $id_identidad=$_POST["id_identidad"];
  if ($_POST["idcaso"]!='') $idcaso=$_POST["idcaso"];
  else $idcaso='null';
  $campos="(id_envio_renglones,id_transporte,id_entidad,envio_cerrado,idcaso)";	
  $query_insert="INSERT INTO envio_renglones $campos VALUES ".
  "($id_envio_renglones,$trans,$id_entidad,0,$idcaso)";
  $res=sql($query_insert, "Error al insertar envio_renglones") or fin_pagina();
  $campos1="(id_envio_renglones,entidad_mod,dir_entrega_mod,id_envio_origen,id_envio_destino)";	
  $query_insert1="INSERT INTO datos_envio $campos1 VALUES ".
  "($id_envio_renglones,'$cliente','$entrega',$origen,$destino)";
  $res1=sql($query_insert1, "Error al insertar datos_envio") or fin_pagina();
  $q_nextval="select nextval ('licitaciones_datos_adicionales.log_envio_renglones_id_log_envio_seq') as id_log_envio";
  $res_q=sql($q_nextval, "Error al traer secuencia de id log_envio") or fin_pagina();
  $id_log_envio=$res_q->fields['id_log_envio'];
  $usuario=$_ses_user['name'];
  $q_insert_log="insert into licitaciones_datos_adicionales.log_envio_renglones 
  (id_log_envio, id_envio_renglones, usuario, tipo_log, fecha)
  values ($id_log_envio, $id_envio_renglones, '$usuario', 'creacion', '$fecha')";
  $res_q_insert_log=sql($q_insert_log, "Error al insertar el log del envío") or fin_pagina();     
  $id_envio=$id_envio_renglones;	
  $d=0;	
  while($guardar>$d)
   {	
    $q_nextval="select nextval('renglones_bultos_id_renglones_bultos_seq') as id_renglones_bul";
    $res_q=sql($q_nextval, "Error la traer secuencia de id de renglones_bultos") or fin_pagina();
    $id_renglones_bultos=$res_q->fields['id_renglones_bul'];
    $canti=$_POST["cant_$d"];
    $bul=$_POST["bul_$d"];
    $titulo=$_POST["desc_$d"];
    $campos="(id_renglones_bultos,id_envio_renglones,cantidad_enviada,titulo_mod,bultos_ocupados)";	
    $query_insert="INSERT INTO renglones_bultos $campos VALUES ".
    "($id_renglones_bultos,$id_envio_renglones,$canti,'$titulo',$bul)";
    $res=sql($query_insert, "Error al insertar renglones_bultos") or fin_pagina();
    $d++;
    $suma=$suma+$bul;
   }
  $query_up="update envio_renglones set cantidad_total=$suma where id_envio_renglones=$id_envio_renglones";
  $res=sql($query_up, "Error al insertar envio_renglones") or fin_pagina();
  $fecha=date("Y-m-d H:m ");
  $usuario=$_ses_user['id'];
  $sql="select * from licitaciones.usuarios_clientes where id_usuario=$usuario and id_entidad=$id_entidad";
  $resul_select=sql($sql,"No se pudo consulta si ya existia esa entrada usuario-cliente") or fin_pagina();
  if($resul_select->RecordCount()>0)
   { 
   $nuevo_peso=$resul_select->fields['peso_uso']+1;
   $sql="update licitaciones.usuarios_clientes set peso_uso=$nuevo_peso,fecha_ultimo_uso='$fecha' where id_usuario=$usuario and id_entidad=$id_entidad";
   $reul_update=sql($sql,"No se pudo realizar el update en la tabla usuarios_clientes") or fin_pagina();
   }
  else 
   {
   $sql="insert into licitaciones.usuarios_clientes (id_usuario,id_entidad,fecha_ultimo_uso,peso_uso,empezo_uso_en)
   values ($usuario,$id_entidad,'$fecha',1,1)";
   $result_insert=sql($sql,"No se pudo realizar el insert en la tabla usuarios_clientes") or fin_pagina();
   }  	
     
 $db->CompleteTrans(); 
 $comentario="PARA INGRESAR LOS NUMEROS DE SERIE DEBE GUARDAR LOS DATOS PRIMERO";
 $comentarios1="Los datos se guardaron correctamente";
 }
}

 if ($_POST["terminar_envio"]=="Terminar Envío")
  {
  $db->StartTrans(); 
  $id_envio_renglones=$_POST['id_envio'];    
  $q_update_envio="update licitaciones_datos_adicionales.envio_renglones set envio_cerrado=1 where id_envio_renglones=$id_envio_renglones";
  $res_q_update_envio=sql($q_update_envio, "Error al cerrar el envio") or fin_pagina(); 
  $usuario=$_ses_user['name'];
  $q_update_log="update licitaciones_datos_adicionales.log_envio_renglones 
  set  usuario='$usuario', tipo_log='creacion', fecha='$fecha'where id_envio_renglones=$id_envio_renglones";
  $res_q_insert_log=sql($q_update_log, "Error al insertar el log del envío") or fin_pagina();
  $db->Completetrans();
  $deshab_botones_terminado="disabled";
  $usu=1;
  $id_envio=$id_envio_renglones;
  ?>
  <script>
  alert ('Los Cambios se realizaron con éxito');
  </script>
  <?
  }
  
  if ($_POST["anular_envio"]=="Anular Envío")
  {
  	
	  $db->StartTrans(); 
	  $id_envio_renglones=$_POST['id_envio'];    
	  $q_update_envio="update licitaciones_datos_adicionales.envio_renglones set envio_cerrado=2 where id_envio_renglones=$id_envio_renglones";
	  $res_q_update_envio=sql($q_update_envio, "Error al anular el envio") or fin_pagina(); 
	  $db->Completetrans();
	  $link=encode_link("../ordprod/listado_envios.php",array());
	  //header("location:$link");
	  ?>
	  <script>
	  document.location.href="<?=$link?>";
	  </script>
	  <?
  }
  
 if ($pagina=="nuevos_envios") 
  {
  $q_a="select envio_cerrado from licitaciones_datos_adicionales.envio_renglones where id_envio_renglones=$id_envio";
  $res_q_a=sql($q_a, "Error al traer campo de envio_cerrado") or fin_pagina();
  $envio_cerrado=$res_q_a->fields['envio_cerrado'];
  if ($envio_cerrado==1) 
   {
   $deshab_botones_terminado="disabled";
   $usu=1;
   $comentario="";
   }	
  else 
   {
   $deshab_botones_terminado="";
   $usu=0;
   $comentario="PARA INGRESAR LOS NUMEROS DE SERIE DEBE GUARDAR LOS DATOS PRIMERO";

   }
  }
  echo $html_header;
 ?>
 <script>
 var wcliente=0;
 function cargar_cliente()
  {
  document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
  document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
  if (wcliente.document.all.chk_direccion.checked)
  document.all.entrega.value=wcliente.document.all.direccion.value;
  }
		
/*****************************************************/
 var wcliente=0;
 var wterminar=0;
 var wproductos=0;

 function cargar()
  {
  /*Para insertar una fila*/
  var items=document.all.items.value++;
  //inserta al final
  var fila=document.all.productos.insertRow(document.all.productos.rows.length );
  fila.insertCell(0).align="center";
  fila.cells[0].innerHTML="<input name='chk' type='checkbox' id='chk' value='"+wproductos.document.all.select_producto.value+"'>";
  fila.insertCell(1).align="center";
  fila.cells[1].innerHTML="<input type='button' name='nro_serie_"+items+"' value='ns' disabled onclick=\"window.open('../ordprod/numeros_serie.php','','top=130, left=250, width=420px, height=450px, scrollbars=1, status=1,directories=0')\">";
  fila.insertCell(2).align="center";
  fila.cells[2].innerHTML="<input name='cant_"+
  items+"' type='text' id='cantidad' size='4' value='' style='text-align:right' "+
  "onchange='calcular(this)'>";
  fila.insertCell(3).align="center";
  fila.cells[3].innerHTML="<input name='bul_"+
  items+"' type='text' id='bultos' size='4' value='' style='text-align:right' "+
  "onchange='calcular(this)'>";
  fila.insertCell(4).align="center";
  fila.cells[4].innerHTML="<textarea name='desc_"+
  items +"' cols='100' rows='2' wrap='VIRTUAL' id='descripcion'>"+
  wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text +"</textarea>";

  if (document.all.boton[0].disabled)
   document.all.boton[0].disabled=0;
  document.all.guardar.value++;
  }
/***********************Nuevo-Item**************************************/
 function nuevo_item()
  {
   var items=document.all.items.value++;
   var cantidad_item=document.all.cantidad_item.value++;
   var fila=document.all.productos.insertRow(document.all.productos.rows.length);
   fila.insertCell(0).align="center";
   fila.cells[0].innerHTML="<input name='chk' type='checkbox' id='chk' value=''>";
   fila.insertCell(1).align="center";
   fila.cells[1].innerHTML="<input type='button' name='nro_serie_"+items+"' value='ns' disabled onclick=\"window.open('../ordprod/numeros_serie.php','','top=130, left=250, width=420px, height=450px, scrollbars=1, status=1,directories=0')\">";
   fila.insertCell(2).align="center";
   fila.cells[2].innerHTML="<input name='cant_"+
   items+"' type='text' id='cantidad' size='4' value='' style='text-align:right'>";
   fila.insertCell(3).align="center";
   fila.cells[3].innerHTML="<input name='bul_"+
   items+"' type='text' id='bultos' size='4' value='' style='text-align:right'>";
   fila.insertCell(4).align="center";
   fila.cells[4].innerHTML="<textarea name='desc_"+
   items +"' cols='100' rows='2' wrap='VIRTUAL' id='descripcion'></textarea>";
   if (document.all.boton[0].disabled)
	document.all.boton[0].disabled=0;
   document.all.guardar.value++;

  }
/*********************Borrar-item****************************/
 function borrar_items()
  {
  var i=0,p=0;
  var t=0;
  //alert(document.all.chk.length);
  while (typeof(document.all.chk)!='undefined' &&
		 typeof(document.all.chk.length)!='undefined' &&
		 i < document.all.chk.length)
  {
  if (document.all.chk[i].checked)
  t++;
  i++;
  p++;
 }
 if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
			{
				t++;
			}

 if(t==0)
  {
  alert('No hay productos seleccionados');
  return false;
  }
  else
  {
  var i=0,p=0;
  while (typeof(document.all.chk)!='undefined' &&
  		 typeof(document.all.chk.length)!='undefined' &&
		 i < document.all.chk.length)
  {
   /*Para borrar una fila*/
  if (document.all.chk[i].checked)
  {
   document.all.productos.deleteRow(i+1);
   p++;   
  } 
  else
   i++;
  }

  if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
  {
   document.all.productos.deleteRow(1);
   document.all.boton[0].disabled=1;
  }
  else if (typeof(document.all.chk)=='undefined')
   		document.all.boton[0].disabled=1;
        document.all.item.value=document.all.item.value+p;   		
  }
  alert("Para confirmar los cambios debe guardar antes de salir");
 }
//--------------------------------------------------------
//FUNCION PARA CHECKEAR LOS CAMPOS
var msg;
function chk_campos(terminar)
{
var ret_value=0;
msg="---------------------------------------------\t\n";
msg+="Falta Completar:\n\n";

if (document.all.nbre.value=="" || document.all.nbre.value==" " || document.all.nbre.value=="Haga click en Cliente para ver la lista")
 {
  msg+="\tNombre del cliente\n";
  ret_value++;
 }


//maximo de 25 productos
if (document.all.productos.rows.length > 26 && ret_value==0)
{
  msg="----------------------------------------------------------------------\t\n";
  msg+="Solo puede agregar un maximo de 25 productos diferentes\n";
  msg+="Haga otra remito para los demas!\n";
  ret_value=27;
}
if (ret_value < 27)
	msg+="---------------------------------------------\t";
else
  msg+="----------------------------------------------------------------------\t";

 return ret_value;
}


//--------------------------------------------------------
//FUNCION PARA CONTRLO DE LOS CAMPOS
 function control_campos()
 {
 var j=0;	
 var n_cliente="Haga click en la palabra cliente para ver la lista";
 if((document.all.cliente.value==n_cliente)||(document.all.cliente.value==""))
 {
  alert('No hay un cliente seleccionado');
  return false;
 }
 if(document.all.transporte.value==-1)
 {
  alert('No hay un transporte seleccionado');
  return false;
 }
 var t=document.all.items.value;
 var t1=document.all.guardar.value;
 var t2=t + t1;
 if(t2==0)
 {
  alert('No hay renglones asociados al envio');
  return false;
 }
 var cont=0;
 while(t>cont)
 {
  var cantidad =eval("document.all.cant_"+cont+".value");
  if(cantidad=="")
  {
   alert('Falta ingresar la cantidad de productos');
   return false;
  }
 var bultos =eval("document.all.bul_"+cont+".value");
 if(bultos=="")
 {
  alert('Falta ingresar la cantidad de bultos');
  return false;
 }
 var descripcion =eval("document.all.desc_"+cont+".value");
 if(descripcion=="")
 {
  alert('Falta ingresar el producto');
  return false;
 } 
 cont++;  
 }
 return true;
 }
</script>

<script language="JavaScript" src="funciones_de_ord_compra.js">

</script>
<form name="form1" method="post" action="nuevos_envios.php">  
 <input type='hidden' name='cantidad_item' value=0>   
 <input type='hidden' name='identidad1' value=0>   
 <input type='hidden' name='serial_1' value=""> 
    <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor="<?=$bgcolor_out?>" class="bordes">
    <tr>
    <td align="center"><b>
    <?=$comentarios1?> </b>
    </td>
    </tr>
     <tr id="mo">
    <td width="100%" align='center' colspan="3">
    <font size="3"><b>Envio por Afuera N° <?=$serial?></b></font>
    </td>
    </tr>
   
    </table> 

     <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=<?=$bgcolor_out?> id="tabla_info" class="bordes">
     <?if($usu==1){
      $sql_sel="select * from log_envio_renglones where id_envio_renglones=$id_envio";
	  $res18=sql($sql_sel, "Error al traer los datos") or fin_pagina();
	  $usuario=$res18->fields['usuario'];
      ?>
     <tr>
     <td align="center" colspan="2"><strong>
     Usuario que cerro el envío <?=$usuario?>
     </strong></td>
     </tr>
     <?}?>
     
     
	<!-- Empieza la parte de Asociar Servicio Tecnico    -->
	<?
	//borra la variables de sesion una vez que se asocio el muleto al envio
	if($_ses_global_backto)
	{
		//extract($_ses_global_extra,EXTR_SKIP);
	 	phpss_svars_set("_ses_global_backto", "");
	 	phpss_svars_set("_ses_global_extra", array());
	}//de if ($_ses_global_backto)
	
	$idcaso = $res12->fields['idcaso'] or $idcaso=$parametros['id_caso'] or $idcaso=$_POST['idcaso'];
	if (!(($id_envio!='')&&($idcaso==''))){?>
     <tr align="center" id="sub_tabla">
	    <td colspan="2">   
	     	Asociar Servicio Técnico
	    </td>
	 </tr>
	 <tr>
     	<td colspan="2">
     		<?if ($idcaso==''){?>
     		<input type="submit" name="asociar_cas" value="Asociar Servicio Técnico" title="Asocia Caso de Servicio Técnico">&nbsp;&nbsp;(<---Haga click en el Boton para Asociar Servicio Técnico)
     		<?}
     		else{?>
     		<input type="submit" name="desvincular_cas" value="Desvincular Servicio Técnico" title="Desvincular Caso de Servicio Técnico" onclick="return confirm('Esta Seguro que Desea Desvincular')">&nbsp;&nbsp;(<---Haga click en el Boton para Desvincular Servicio Técnico)
     		<?}?>
     	</td>
     </tr>
     <?
     if ($idcaso!=''){
     	$sql="select casos_cdr.*,entidad.id_entidad,entidad.nombre
		      from casos.casos_cdr
		      join dependencias using (id_dependencia)
		      join entidad using (id_entidad)
		      where casos_cdr.idcaso=$idcaso";
     	$result_caso=sql($sql,'No se puede traer el caso') or fin_pagina();
     	$id_entidad_caso=$result_caso->fields["id_entidad"];
     	$nro_caso=$result_caso->fields['nrocaso'];
     ?>
     <tr>	
     	<td>
     		<?$link = encode_link("../casos/caso_estados.php",array("id"=>$idcaso,"id_entidad"=>$id_entidad_caso));?>
     		<b>Número de Caso: </b><a href="<?=$link?>" style="font-size='16'; color='red';" target="_blank" title="Ver el caso de Servicio Técnico."><U><?=$nro_caso?></U></A>
     	</td>
     	<td>	
     		<b>Desperfecto: </b> <textarea name="desperfecto" cols="65" rows="4" readonly><?=$result_caso->fields['deperfecto']?></textarea>
     	</td>
     </tr>
     <tr>	
     	<td>
     		<b>Nombre de la Entidad: </b><input type="text" name="nom_ent" value="<?=$result_caso->fields['nombre']?>" size="65">
     	</td>
     	<td>	
     		<b>Número de Serie: </b><input type="text" name="nro_serie" value="<?=$result_caso->fields['nserie']?>" size="65">
     	</td>
     </tr>
     <?}
	}
	else{?>
		<tr align="center" id="sub_tabla">
	    <td colspan="2">   
	     	Asociar Servicio Técnico
	    </td>
	 </tr>
	 <tr>
     	<td colspan="2">
     		<input type="submit" name="asociar_cas1" value="Asociar Servicio Técnico" title="Asocia Caso de Servicio Técnico">&nbsp;&nbsp;(<---Haga click en el Boton para Asociar Servicio Técnico)
     	</td>
     </tr>
	<?}?>
     <!-- Termina la parte de Asociar Servicio Tecnico    -->
     
     
     <tr align="center" id="sub_tabla">
     <td colspan="3">Cliente</td>
     </tr> 
     <tr>
     <td width="50%">
     <a style='cursor:hand' title="Haga click para ver elegir/editar el cliente"
     <?
     if ($permiso=="")
	 {
     ?> 
     onclick="
      wcliente=window.open('<?=encode_link('../ord_compra/elegir_cliente.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclikaceptar2'=>"window.opener.cargar_cliente_mas_usados();window.close()",'onclicksalir'=>'window.close()')) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
      "
     <?
     }//de if ($permiso=="")
     ?>
     > 
     <b><u>Cliente</u></b> (<---Haga click en la palabra para editar/elegir el cliente)
     </a>
     </td>
     <td width="50%">
     <b>Lugar y Forma de Entrega</b>
     </td>
     </tr>
     <tr>   
     <td align="center">
     <?
     $max_800_600=50;
     $max_1024_768=65;
     $max_otro=90;
	 if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800)) 
	  $longitud_fila=$max_800_600;
	 elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	  $longitud_fila=$max_1024_768;
	 else//si es una resolucion mayor a 1024 
	  $longitud_fila=$max_otro;
	 if($cliente=="")
	  $cliente="Haga click en la palabra cliente para ver la lista";
     ?>
     <textarea name="cliente" style="width:95%" rows="<?=row_count($cliente,$longitud_fila)?>"  wrap="VIRTUAL"  <?=$permiso ?>><?=$cliente?></textarea>
     <input name="id_entidad" type="hidden" value="<?=$id_entidad?>"> 
     </td>
     <td align="center">
     <?
     if($entrega=="")
      $entrega="Se lleva a Coradir BsAs Patagones 2538 - Parque Patricios Coordinar la entrega con Graciela Tedeschi 011-5354-0300"; 
      $entrega=ajustar_lineas_texto($entrega,$longitud_fila);   
     ?>
     <textarea name="entrega" style="width:95%" rows="<?=row_count($entrega,$longitud_fila)?>" wrap="VIRTUAL" id="entrega" <?=$permiso ?>><?=$entrega?></textarea>
     </td>
     </tr>
     <tr>
     <?
    $q_origen="select nombre_envio_origen, id_envio_origen from licitaciones_datos_adicionales.envio_origen order by nombre_envio_origen";
    $res_q_origen=sql($q_origen, "Error al traer Sucursales de Coradir") or fin_pagina();
    $q_destino="select nombre, id_distrito, id_envio_destino from licitaciones.distrito
    left join licitaciones_datos_adicionales.envio_destino using (id_distrito) order by nombre";
    $res_q_destino=sql($q_destino, "Error al traer las provincias destino del envío") or fin_pagina();
    ?>
    <td colspan="2">
    <table width="100%"><tr> 
    <td align="center" id='ma'>Origen Envío:&nbsp;&nbsp;
    <select name="origen">
    <? 
    while (!$res_q_origen->EOF) {
   	 $id_or=$res_q_origen->fields['id_envio_origen'];
    ?>
    <option value="<?=$id_or?>"
    <? 
    if ($id_or==$origen_envio) echo "selected"?>><?=$res_q_origen->fields['nombre_envio_origen']?></option>
    <? $res_q_origen->MoveNext(); } ?>  
    </select>
    </td>
    <td align="center" id='ma'>Provincia Destino Envío:&nbsp;&nbsp;
    <select name="destino">
    <? 
    while (!$res_q_destino->EOF) 
    { 
     $id_dest=$res_q_destino->fields['id_envio_destino'];
    ?>
     <option value="<?=$id_dest?>"
    <? 
    if ($id_dest==$destino_envio) echo "selected"?>><?=$res_q_destino->fields['nombre']?></option>
    <? $res_q_destino->MoveNext(); } ?>    
    </select>
    </td>
    </tr>
    </table>
    <br>
    <br>
    <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor="<?=$bgcolor_out?>" class="bordes">
    <tr id="mo">
    <td width="100%" align='center' colspan="3">
    <font size="3"><b>Datos del Transporte</b></font>
    </td>
    </tr>
    <tr>
    <td colspan=4>
    <table width="100%">
    </table>
    </td>
    </tr>
    </table>
    
    
    <? // consulta para traer los datos de los transportes
     $q_trans="select nombre_transporte, id_transporte, comentarios_transporte,direccion_transporte,telefono_transporte from licitaciones_datos_adicionales.transporte order by nombre_transporte";
     $res_q_trans=sql($q_trans, "Error al traer datos de los Transportes") or fin_pagina();
     $comentarios_arreglo=array();
     //$i=0;?>
     <script>
        var comentarios=new Array();
        var telefono=new Array();
        var direccion=new Array();
        var contacto1=new Array();
        
     <? while (!$res_q_trans->EOF) {
	       $i=$res_q_trans->fields['id_transporte']; 
	       $nom_tran="select nombre from licitaciones_datos_adicionales.transporte_contactos where id_transporte=$i";
           $tran=sql($nom_tran, "Error al traer datos de los Transportes") or fin_pagina();
	        ?>	
	       direccion[<?=$i?>]='<?=$res_q_trans->fields['direccion_transporte'];?>'; 
	       telefono[<?=$i?>]='<?=$res_q_trans->fields['telefono_transporte'];?>'; 	
	       contacto1[<?=$i?>]='<?=$tran->fields['nombre'];?>';
	       
	 <?  $res_q_trans->MoveNext(); } ?> 

		function comentarios_transporte2() {
		  var id_transporte;
		  if (document.all.transporte.value!=-1) {  
		        id_transporte=document.all.transporte.options[document.form1.transporte.selectedIndex].value;
		        document.all.direccion1.value=direccion[id_transporte];
		        document.all.telefono.value=telefono[id_transporte];
		        var nom=telefono[id_transporte];
		        document.all.contacto2.value=contacto1[id_transporte];
		  } 
		  else {
                document.all.comentarios_transporte.value="";
		  }   
		} //de la funcion
     </script>  
     <?  
     $res_q_trans->Move(0);
    // print_r($comentarios_arreglo);
   ?>
   <table align="center" width="100%">
    <tr>
     <td align="center" id='ma'>Datos del Transporte
     <td align="center" id='ma'>Direccion</td>
     <td align="center" id='ma'>Telefono</td>
     <td align="center" id='ma'>Contacto</td>
    </tr> 
     <tr align="center"> 
     <td align="center" id='ma'>
      <select name="transporte" onchange="comentarios_transporte2();">
        <option value="-1">Seleccionar un Transporte</option>
       <? while (!$res_q_trans->EOF) {
             	
       	?>
        <option value='<?=$res_q_trans->fields['id_transporte']?>' 
           <? if ($transporte==$res_q_trans->fields['id_transporte']) echo "selected"?>>
           <?=$res_q_trans->fields['nombre_transporte']?>
        </option>
          <? $res_q_trans->MoveNext();
         } ?> 
       </select><br>
       <? 	$link_transporte=encode_link("../logistica/transporte_editor_avanzado.php",array('pagina_viene'=>'preparar_envios.php'));  ?>

        <input type="button" name="nuevo_transporte" value="Nuevo Transporte" title="Agregar un Nuevo Transporte para los Envíos"
        onclick="window.open('<?=$link_transporte?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')">
       </td>
      <?
      
      ?> 
     
     <td align="center" id="ma"><input class="text_3" readonly type="text" name="direccion1" value=""></td>
     <td align="center" id="ma"><input class="text_3" readonly type="text" name="telefono" readonly value=""></td>
     <td align="center" id="ma"><input class="text_3" readonly type="text" name="contacto2"  value=""></td>
     </tr>
     </table> 
    <br>
    <br>
    <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor="<?=$bgcolor_out?>" class="bordes">
    <tr>
    <td  align="center"><font size='2' color='red'>
    <?=$comentario?>
    </font>
    </td>
    </tr>
    <tr id="mo">  
    <td width="100%" align='center' colspan="3">
    <font size="3"><b>Productos de la Orden de Compra</b></font>
    </td>
    </tr>
    </table>
    <table id="productos" width="100%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
    <tr bgcolor="#006699" class="tablaEnc">
    <td valign="middle" width="5%" height="18"><strong>Borrar</strong></td>
    <td valign="middle" width="5%" height="18"><strong>Números de serie</strong></td>
    <td width="10%"><div align="center"><strong>Cantidad</strong></div></td>
    <td width="10%"><div align="center"><strong>Bultos que ocupa</strong></div></td>
    <td width="80%"><div align="center"><strong>Item - Descripci&oacute;n</strong></div></td>
    </tr>
    <?
    $q="select * from renglones_bultos where id_envio_renglones=$id_envio";
    if ($id_envio && $id_envio!=-1){
 	$items_envio=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
    echo "<input type='hidden' name='modificar' value='1'>";
    ?>
    <?
    }	
    $i=0; 	
    $total=0;
    while ($items_envio && !$items_envio->EOF)                  
    {
     $link_ns=encode_link("../ordprod/numeros_serie.php",array("cant"=>$items_envio->fields['cantidad_enviada'],"id_envio_renglon"=>$id_envio, "id_renglones_bultos"=>$items_envio->fields['id_renglones_bultos'], "pagina"=>"nuevos_envios")); 
    ?>
    <tr>
    <td valign="middle" align="center"> <input type="checkbox" name="chk" <?=$deshab_botones_terminado?>></td>
    <td height="15"> <div align="center">   
    <input type='button' name='nro_serie_<?=$i ?>' value='ns'  onclick="window.open('<?=$link_ns?>','','top=130, left=250, width=420px, height=450px, scrollbars=1, status=1,directories=0')">
    </div>
    </td>
    <td height="15"> <div align="center">
    <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $items_envio->fields['cantidad_enviada'] ?>" <?=$permiso?>>
    <input type="hidden" name="idi_<?=$i ?>" value="<?= $items_envio->fields['id_renglones_bultos'] ?>" ></div></td>  
    <td height="15"> <div align="center">
    <input name="bul_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $items_envio->fields['bultos_ocupados'] ?>" <?=$permiso?>>
    </td>
    <td height="15"> <div align="center">
    <textarea name="desc_<?=$i ?>" cols="100" rows="2" wrap="VIRTUAL" <?=$permiso?>><?= $items_envio->fields['titulo_mod'] ?></textarea>
    </div>
    </td>
    </tr>
    <?
	$items_envio->MoveNext();
    $i++;
   }
   if($i==0)
   {
    $deshab=disabled;	
   }
   else 
   {
    $deshab='';
   }
  ?>
  </table>
  <br>
  <?
  if($tipo!="anulados")
  {
  ?>
  <table width="95%" height="28" border="0" cellpadding="1" cellspacing="1" align="center">
  <tr> 
  <td height="26" align="center">
  <input name="boton" type="button"  value="Eliminar" <?=$deshab_botones_terminado?>  onclick=
  "if (confirm('Seguro que quieres eliminar los Items seleccionados'))
   borrar_items();
   " title="Elimina los elementos seleccionados">
   <input name="boton" type="button"  value="Agregar" <?=$deshab_botones_terminado?> title="Agrega uno o mas productos" onclick="nuevo_item()" <?=$permiso?>>
   <input name="boton1" type="submit"  value="Guardar" <?=$deshab_botones_terminado?> title="Guarda los cambios y permite posteriores modificaciones"  onclick="return control_campos();">
   <? 
   $link=encode_link("../ordprod/listado_envios.php");	
   ?>	 
   <input type="submit" name="terminar_envio" value="Terminar Envío" title="Cerrar el Envío, no se pueden agregar más productos" 
   <?=$deshab?><?=$deshab_botones_terminado?> onclick="return (confirm('¿Está seguro que quiere cerrar este Envío ? \nCuando el Envío esté cerrado solo estará disponible para consulta en el Listado Historial'))"></td>
   </tr>
   <tr>
   <?  $link_adjunto=encode_link("../ordprod/adjunto_remito_envio.php", array("id_envio_renglones"=>$id_envio));	?>
   <td align="center">
   <input type="button" name="adjunto_remito" value="Imprimir Adjunto Remitos" title="Imprimir Adjuntos para los Remitos Asociados a este Envío"
   <?=$deshab?><?=$deshab_botones_sin_guardar_datos?> onclick="window.open('<?=$link_adjunto?>','','toolbar=1,location=0,directories=1,status=1, menubar=1,scrollbars=1,left=125,top=10,width=800,height=600')"> 
   <?  $link_etiqueta=encode_link("../ordprod/etiquetas_envios.php", array("id_envio_renglones"=>$id_envio));    ?>
   <input type="button" name="etiquetas" value="Imprimir Etiquetas" title="Configurar las Etiquetas para los Envíos" 
   onclick="window.open('<?=$link_etiqueta?>','','toolbar=1,location=0,directories=1,status=1, menubar=1,scrollbars=1,left=125,top=10,width=400,height=300')"
   <?=$deshab?> <?=$deshab_botones_sin_guardar_datos?>>
   </td>
   </tr>
   <tr>
   <td align="center">
   <?
     if(permisos_check("inicio","permisos_anular_envios"))
     {
     ?>
   <input type="submit" name="anular_envio" value="Anular Envío" title="Anular el Envío" 
    <?=$deshab?> <?=$deshab_botones_sin_guardar_datos?> onclick="return (confirm('¿Está seguro que quiere anular este Envío ?'))">
   <?}?>
   &nbsp;&nbsp;
   <input type="button"  name="boton_volver" value="Volver"  onclick="window.location='../ordprod/listado_envios.php'"> 
   </td>
   </tr>
   </table>
   <?
  }
  else 
  {
  ?>
  <table width="50%" align="center">
   <tr align="center">
   <td align="center">
   <input type="button"  name="boton_volver" value="Volver"  onclick="document.location='../ordprod/listado_envios.php'"> 
   </td>
   </tr>
   </table>
  
  <?} ?>
   <input type='hidden' name='items' value='<?=$i?>'>
   <input type='hidden' name='guardar' value='0'>
   <input type='hidden' name='serial_1' value="<?=$serial?>"> 
   <input type='hidden' name='pagina' value=''>
   <input type='hidden' name='id_envio' value='<?=$id_envio?>'>
   <input type='hidden' name='idcaso' value='<?=$idcaso?>'>
   </form>
   <?
fin_pagina();
?>
