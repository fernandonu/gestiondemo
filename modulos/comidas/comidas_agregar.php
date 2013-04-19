<?php
/*
$Author: mari $
$Revision: 1.19 $
$Date: 2006/12/29 13:56:27 $
*/
require_once("../../config.php");
echo $html_header;
$prov_comida_pedido=$_POST['prov_comida_pedido'];
$fecha=$_POST["fecha"] or $fecha=date("d/m/Y",mktime());

function cambiar ($m){
switch ($m){
case 1: {$cambio="Enero";
         break;
         }
case 2: {$cambio="Febrero";
         break;
         }
case 3: {$cambio="Marzo";
         break;
         }
case 4: {$cambio="Abril";
         break;
         }
case 5: {$cambio="Mayo";
         break;
         }
case 6: {$cambio="Junio";
         break;
         }
case 7: {$cambio="Julio";
         break;
         }
case 8: {$cambio="Agosto";
         break;
         }
case 9: {$cambio="Septiembre";
         break;
         }
case 10: {$cambio="Octubre";
         break;
         }
case 11: {$cambio="Noviembre";
         break;
         }
case 12: {$cambio="Diciembre";
         break;
         }
   }
return $cambio;
}

//para obtener el nombre de la persona q esta logueada q es administrador
$cons_us_logueado="select nombre, apellido, id_administrador, id_lugar_pedido_comida from sistema.usuarios
                   left join comidas.administradores using (id_usuario) 
                   where login='".$_ses_user['login']."' ";
$res_us_logueado=$db->execute($cons_us_logueado) or die ($cons_us_logueado."<br>".$db->errormsg());
$nya_us=$res_us_logueado->fields['nombre']." ".$res_us_logueado->fields['apellido'];
$id_adm=$res_us_logueado->fields['id_administrador'];
$id_lugar_adm=$res_us_logueado->fields['id_lugar_pedido_comida'];

//para hab o deshab el pedido
//$fecha=date("Y-m-d",mktime()); 

if ($_POST['habilitar_pedido']=="Habilitar Pedido") {
	$db->StartTrans();
	//consulto si ya se habia habilitado el pedido antes
	$cons_pedido_hab="select * from comidas.habilitar_pedidos where fecha_pedido='".Fecha_db($fecha)."' 
	                  and id_proveedor_comida=$prov_comida_pedido";
	$res_cons_pedido_hab=$db->execute($cons_pedido_hab) or die ($db->errormsg());
	$pedido_hab=$res_cons_pedido_hab->recordcount();
	if ($pedido_hab) {
	$cons_hab_pedido="update comidas.habilitar_pedidos set estado_pedido=1 where fecha_pedido='".Fecha_db($fecha)."'
	                  and id_proveedor_comida=$prov_comida_pedido";
    $res_cons_hab_pedido=$db->execute($cons_hab_pedido) or die($cons_hab_pedido."<br>".$db->errorMsg());
    
    //recuperar secuencia del log 
    $nextval="select nextval('comidas.log_pedidos_id_log_pedido_seq') as id_log_pedido";
    $res_nextval=$db->Execute($nextval) or die($db->ErrorMsg()."<br>Error al traer la secuencia del log");
    $id_log_pedido=$res_nextval->fields["id_log_pedido"];
    
    $insert_logs="insert into log_pedidos (id_log_pedido, fecha, tipo_log_pedido, fecha_pedido, nombre, id_proveedor_comida)
                  values ($id_log_pedido, '".Fecha_db($fecha)."', 'Habilitar el Pedido', '".Fecha_db($fecha)."', '$nya_us', $prov_comida_pedido)";
    $res_insert_logs=$db->execute($insert_logs) or die($insert_logs."<br>".$db->errormsg());  
	 }	
	else {
	$cons_hab_pedido="insert into habilitar_pedidos (fecha_pedido, estado_pedido, id_administrador, id_proveedor_comida) 
	                  values ('".Fecha_db($fecha)."', 1, 1, $prov_comida_pedido)";
	$res_cons_hab_pedido=$db->execute($cons_hab_pedido) or die($cons_hab_pedido."<br>".$db->errorMsg());
    
	//recuperar secuencia del log 
    $nextval="select nextval('comidas.log_pedidos_id_log_pedido_seq') as id_log_pedido";
    $res_nextval=$db->Execute($nextval) or die($db->ErrorMsg()."<br>Error al traer la secuencia del log");
    $id_log_pedido=$res_nextval->fields["id_log_pedido"];
    
    $insert_logs="insert into log_pedidos (id_log_pedido, fecha, tipo_log_pedido, fecha_pedido, nombre, id_proveedor_comida)
                  values ($id_log_pedido, '".Fecha_db($fecha)."', 'Habilitar el Pedido', '".Fecha_db($fecha)."', '$nya_us', $prov_comida_pedido)";
    $res_insert_logs=$db->execute($insert_logs) or die($insert_logs."<br>".$db->errormsg());
	}
	list($año, $mes, $dia)=split('[-]', $fecha);
	$m=cambiar($mes);
    
    $db->CompleteTrans();
    
    $accion="Se habilitó el pedido para el ".$dia." de ".$m." de ".$año;
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
    }
elseif ($_POST['habilitar_pedido']=="Cerrar Pedido") {
	$db->StartTrans();
   	$cons_hab_pedido="update comidas.habilitar_pedidos set estado_pedido=0 where fecha_pedido='".Fecha_db($fecha)."'
   	                  and id_proveedor_comida=$prov_comida_pedido";
    $res_cons_hab_pedido=$db->execute($cons_hab_pedido) or die($cons_hab_pedido."<br>".$db->errorMsg());
    
    //recuperar secuencia del log 
    $nextval="select nextval('comidas.log_pedidos_id_log_pedido_seq') as id_log_pedido";
    $res_nextval=$db->Execute($nextval) or die($db->ErrorMsg()."<br>Error al traer la secuencia del log");
    $id_log_pedido=$res_nextval->fields["id_log_pedido"];
    
    $insert_logs="insert into log_pedidos (id_log_pedido, fecha, tipo_log_pedido, fecha_pedido, nombre, id_proveedor_comida)
                  values ($id_log_pedido, '".Fecha_db($fecha)."', 'Deshabilitar el Pedido', '".Fecha_db($fecha)."', '$nya_us', $prov_comida_pedido)";
    $res_insert_logs=$db->execute($insert_logs) or die($insert_logs."<br>".$db->errormsg());

    $db->CompleteTrans();

    $accion="El pedido fue Deshabilitado";
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
    }
    
//habilitar o deshabilitar los platos y guarniciones 
if ($_POST['habilitar_plato']=="Hab/Deshab Plato") {
	$db->StartTrans();
	$prov_comida=$_POST['proveedor_comida'];
	$arreglo_platos_chequeados=PostvartoArray('hab_p_');
	
	// el estado se vuelve a cero y luego en uno los chequeados
	$actualizar_hab="update comidas.plato set habilitado=0 where id_proveedor_comida=$prov_comida";
    $res_actualizar_hab=$db->execute($actualizar_hab) or die($actualizar_hab."<br>".$db->errormsg());
       
    if ($arreglo_platos_chequeados){
       foreach ($arreglo_platos_chequeados as $key => $value) {
       	  $actualizar_hab="update comidas.plato set habilitado=1 where id_plato=$value and id_proveedor_comida=$prov_comida";
          $res_actualizar_hab=$db->execute($actualizar_hab) or die($actualizar_hab."<br>".$db->errormsg());
          } // del foreach
       } // del if $arreglo_platos_chequeados
    $db->CompleteTrans();   
    $accion="Las modificaciones fueron realizadas con Exito";
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
   } // del if _post

if ($_POST['habilitar_guarnicion']=="Hab/Deshab Guarnición") {
	$db->StartTrans();   
	$prov_comida=$_POST['proveedor_comida'];
	$arreglo_guarniciones_chequeadas=PostvartoArray('hab_g_');
	
	// el estado se vuelve a cero y luego en uno los chequeados
	$actualizar_hab="update comidas.guarnicion set habilitado=0 where id_proveedor_comida=$prov_comida";
    $res_actualizar_hab=$db->execute($actualizar_hab) or die($actualizar_hab."<br>".$db->errormsg());
   
    if ($arreglo_guarniciones_chequeadas){
       foreach ($arreglo_guarniciones_chequeadas as $key => $value) {
       	  $actualizar_hab="update comidas.guarnicion set habilitado=1 where id_guarnicion=$value and id_proveedor_comida=$prov_comida";
          $res_actualizar_hab=$db->execute($actualizar_hab) or die($actualizar_hab."<br>".$db->errormsg());
           } // del foreach
       } // del if $arreglo_guarniciones_chequeadas
        
    $db->CompleteTrans();   
           
    $accion="Las modificaciones fueron realizadas con Exito";
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>"; 
   } // del if _post

// agregar nuevos proveedores seleccionando el distrito al q pertenece
if ($_POST['guardar']=="Guardar Proveedor") {
    $db->StartTrans();   
	$nombre_prov=$_POST['nombre_prov'];
    $distrito_prov=$_POST['distrito'];
    
    //tengo q hacer next val y usarlo en el proveedor
    $nextval="select nextval('comidas.proveedor_comida_id_proveedor_comida_seq') as id_proveedor_comida";
    $res_nextval=$db->Execute($nextval) or die($db->ErrorMsg()."<br>Error al traer la secuencia de proveedor");
    $id_proveedor=$res_nextval->fields["id_proveedor_comida"];
    
    $insert_proveedor="insert into proveedor_comida (id_proveedor_comida, nombre_proveedor_comida, id_distrito) 
                       values ($id_proveedor, '$nombre_prov', $distrito_prov)";
    $e_insert_proveedor=$db->execute($insert_proveedor) or die($insert_proveedor."<br>".$db->errormsg()); 
    
    $db->CompleteTrans();   
    
    $accion="El nuevo Proveedor fue ingresado con Exito";
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
}


//para guardar los nuevos platos, guarniciones y las asociaciones entre ellos
if ($_POST['guardar']=="Guardar Nuevo Plato") {
	$db->StartTrans();   
    $nplato=$_POST['nombre_plato'];
    $asociado_guar=$_POST['asociar_guar'];
    $prov_comida=$_POST['prov_comida'];
    
    //controlar q no se den de alta + de un plato con el mismo nombre
    $control_nombre_plato="select nombre_plato from comidas.plato 
                           where id_proveedor_comida=$prov_comida
                           and nombre_plato='$nplato'";
    $res_control_nombre_plato=$db->execute($control_nombre_plato) or die ($db->errormsg()."<br>".$control_nombre_plato);
    $cant_plato_idem=$res_control_nombre_plato->RecordCount();
    
    if ($cant_plato_idem==0) {
    //tengo q hacer next val y usarlo en el plato y luego en comida 
    $nextval="select nextval('comidas.plato_id_plato_seq') as id_plato";
    $res_nextval=$db->Execute($nextval) or die($db->ErrorMsg()."<br>Error al traer la secuencia de plato");
    $id_plato=$res_nextval->fields["id_plato"];

    $insert_plato="insert into plato (id_plato, nombre_plato, habilitado, id_grupo_comidas, id_proveedor_comida)
                   values ($id_plato, '$nplato', 1, $asociado_guar, $prov_comida) ";
    $e_insert_plato=$db->execute($insert_plato) or die($insert_plato."<br>".$db->errormsg()); 
    $accion="El nuevo Plato fue ingresado con Exito";
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
     }
    else { 
      $accion="El plato no puede agregarse a la lista del Proveedor porque ya existe";
      echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
     }
    
    $db->CompleteTrans();    
    }
elseif ($_POST['guardar']=="Guardar Nueva Guarnición") {
	$db->StartTrans();   
    $nguarnicion=$_POST['nombre_guarnicion'];
    $asociado_plato=$_POST['asociar_plato'];
    //$prov_comida=1;
    $prov_comida=$_POST['prov_comida'];
    
    //tengo q hacer next val y ese valor usarlo p gaurdar la guarnicion y luego usarlo en comida
    $nextval="select nextval('comidas.guarnicion_id_guarnicion_seq') as id_guarnicion";
    $res_nextval=$db->Execute($nextval) or die($db->ErrorMsg()."<br>Error al traer la secuencia de guarnicion");
    $id_guar=$res_nextval->fields["id_guarnicion"];
 
    $insert_guarnicion="insert into guarnicion (id_guarnicion, nombre_guarnicion, habilitado, id_grupo_comidas, id_proveedor_comida)
                        values ($id_guar, '$nguarnicion', 1, $asociado_plato, $prov_comida) ";
    $e_insert_guarnicion=$db->execute($insert_guarnicion) or die($insert_guarnicion."<br>".$db->errormsg());
    
    $db->CompleteTrans();   
    
    $accion="La nueva Guarnición fue ingresada con Exito";
    echo "<table align='center'><tr><td align='center' colspan='2' size='3'><font color='Red'><b>".$accion."</b></font></td></tr></table>";
    }    
?>

<script language="JavaScript">
function control_nuevo_proveedor(){
  var i=0, j=0;
  var cant_radios=2;
  var mostrar_alert;
  var proveedor, distrito;	
  if (document.all.nombre_prov.value==""){
   alert("Debe ingresar un nombre para el Nuevo Proveedor");
   return false;
  };
  while(i<cant_radios){
     if (document.all.distrito[i].checked!=true) {
     	i++;
     	j=0;
     	mostrar_alert=1;
     	    }
     else {
     	j=i;
     	i=cant_radios;
     	mostrar_alert=0;
    }
  }
  if (mostrar_alert) {
  	alert ("Debe seleccionar un Distrito para el Proveedor");
    return false;
  }
  if (j!=1) distrito=document.all.distrito_sl.value;
  else distrito=document.all.distrito_bsas.value;
  proveedor=document.all.nombre_prov.value;
  return confirm('El nuevo Proveedor "'+proveedor+'" pertecene al Distrito "'+distrito+'"'); 
 
}

function control_nuevo_plato(){
  var i=0;
  var cant_grupos=document.all.cant_grupos.value;
  var mostrar_alert;
  var plato, proveedor;	
  if (document.all.nombre_plato.value==""){
   alert("Debe ingresar un nombre para el Nuevo Plato");
   return false;
  };
  while(i<cant_grupos){
     if (document.all.asociar_guar[i].checked!=true) {
     	i++;
     	mostrar_alert=1;
     }
     else {
     	i=cant_grupos;
     	mostrar_alert=0;
     }
  }
  if (mostrar_alert) {
  	alert ("Debe seleccionar alguna opción para el plato");
    return false;
  }
   if (document.all.prov_comida.options[document.all.prov_comida.selectedIndex].value==-1){
     alert("Debe selecionar un Proveedor para el Nuevo Plato");
     return false;
   };
  plato=document.all.nombre_plato.value; 
  proveedor=document.all.prov_comida.options[document.all.prov_comida.selectedIndex].text;
  return confirm('El plato "'+plato+'" se agregará a la lista de comidas del Proveedor "'+proveedor+'"'); 
 
} 

function control_nueva_guarnicion(){
   var i=0;
   var cant_grupos=2;
   var mostrar_alert;
   var guarnicion, proveedor;		
   if (document.all.nombre_guarnicion.value==""){
    alert("Debe ingresar un nombre para la Nueva Guarnición");
    return false;
   };
    while(i<cant_grupos){
     if (document.all.asociar_plato[i].checked!=true) {
     	i++;
     	mostrar_alert=1;
     }
     else {
     	i=cant_grupos;
     	mostrar_alert=0;
     }
   }
   if (mostrar_alert) {
  	alert ("Debe seleccionar uno de los grupos para la nueva Guarnición");
    return false;
   }
   if (document.all.prov_comida.options[document.all.prov_comida.selectedIndex].value==-1){
     alert("Debe selecionar un Proveedor para la Nueva Guarnición");
     return false;
   };
  guarnicion=document.all.nombre_guarnicion.value; 
  proveedor=document.all.prov_comida.options[document.all.prov_comida.selectedIndex].text;
  return confirm('La guarnición "'+guarnicion+'" se agregará a la lista de comidas del Proveedor "'+proveedor+'"'); 
} 

function control_habilitar_pedido(){
  var fecha=document.all.fecha.value; 
  var proveeedor;	
  if (document.all.prov_comida_pedido.options[document.all.prov_comida_pedido.selectedIndex].value==-1){
     alert("Debe selecionar un Proveedor para Habilitar/Cerrar el Pedido de hoy");
     return false;
   };
  proveedor=document.all.prov_comida_pedido.options[document.all.prov_comida_pedido.selectedIndex].text; 
  return confirm('Habilitar/Cerrar el pedido de la fecha '+fecha+' para el Proveedor "'+proveedor+'"'); 
}

function control_imprimir_pedido(){
  if (document.all.prov_comida_pedido.options[document.all.prov_comida_pedido.selectedIndex].value==-1){
     alert("Debe selecionar un Proveedor para Imprimir el Pedido de hoy");
     return false;
   };
  return true; 
}

</script>

<script src="../../lib/popcalendar.js"></script>
<HTML>

<HEAD>
<meta Name="generator" content="PHPEd Version 3.2 (Build 3220 )">
<title>Listado de Comidas</title>
<meta Name="author" content="Elizabeth Ferreira">
<link rel="SHORTCUT ICON"  href="/path-to-ico-file/logo.ico">
</HEAD>
<BODY bgcolor="<?=$bgcolor3?>">
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<script language="JavaScript" src="../../lib/NumberFormat150.js"></script>

<FORM name="form1" action="comidas_agregar.php" method="POST">

<table width=95% border=0 cellspacing=0 cellpadding=3 bgcolor="<?=$bgcolor2?>" align="center">
<?
$con_prov_comidas="select * from comidas.proveedor_comida where id_distrito=$id_lugar_adm and activo=1 order by nombre_proveedor_comida";
$res_prov_comidas=sql($con_prov_comidas,"<br>Error al traer los proveedores") or fin_pagina();
?>
  <tr id=mo>
    <td colspan="2"><b>Administración de Pedidos</b></td>
  </tr>
  <tr>
    <td width="30%">
      <select name="prov_comida_pedido" onchange="document.form1.submit()">
          <option value=-1>Seleccionar un Proveedor</option>
        <? $res_prov_comidas->Move(0);
          while (!$res_prov_comidas->EOF) { ?>
          <option value="<?=$res_prov_comidas->fields['id_proveedor_comida']?>"
<? if ($res_prov_comidas->fields['id_proveedor_comida']==$_POST['prov_comida_pedido']) echo "selected"; ?>>
            <?=$res_prov_comidas->fields['nombre_proveedor_comida']?>
          </option>
        <? $res_prov_comidas->MoveNext(); } ?>
      </select>
    </td>
  </tr> 
  <?
  //aca tengo que obtener el valor de habilitar_pedido de la tabla 
  //y determinar si hab o deshab el pedido
  //$fecha=date("Y-m-d",mktime());
  //si no hay proveedor seleccionado trae uno elegido
  if ($_POST['prov_comida_pedido']) 
     $prov_comida_pedido=$_POST[prov_comida_pedido]; 
  	
  $cons_estado_pedido="select estado_pedido from comidas.habilitar_pedidos where fecha_pedido='".Fecha_db($fecha)."'";
  if ($_POST['prov_comida_pedido']) $and=" and id_proveedor_comida=$prov_comida_pedido"; 
  $cons_estado_pedido.=$and;
  $res_estado_pedido=$db->execute($cons_estado_pedido) or die($cons_estado_pedido."<br>".$db->errormsg());
  if ($res_estado_pedido->fields['estado_pedido']==1)
        $habilitar_pedido="Cerrar Pedido";
   else $habilitar_pedido="Habilitar Pedido";
  if (!$_POST['prov_comida_pedido']) $habilitar_pedido="Habilitar/Cerrar Pedidos";
  ?>
  
  <tr>
    <td align="left" width="60%">
     &nbsp;&nbsp;<b>Para Habilitar o Cerrar el Pedido de Comidas del día</b>
     <input type="text" name="fecha" readonly value="<?=$fecha?>" size="10">
     <?=link_calendario("fecha")?><input type="submit" name="cambiar_fecha" value="GO" class="little_boton">
    </td>
    <td align="left">
         <input type="submit" name="habilitar_pedido" value="<?=$habilitar_pedido?>" onclick="return control_habilitar_pedido();"> 
  </td></tr> 
  <tr><td colspan="2"><hr></td></tr> 
  <tr>
   <?
   $link_realizar_pedido=encode_link("listado_pedido_comidas.php", array("fecha" => $fecha, "proveedor" => $prov_comida_pedido));
   ?>
    <td align="left">&nbsp;&nbsp;<b>Para Imprimir el Pedido de Comidas del día</b></td>
    <td align="left">
      <input type="button" name="pedido" value="Realizar Pedido" 
       onclick="if (control_imprimir_pedido()) 
                   window.open('<? echo $link_realizar_pedido; ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,width=800,height=600');
                else return false">
    </td>
  </tr>
</table>
<br><br>

<?
$proveedor_comida=$_POST['proveedor_comida'];

$where="where id_proveedor_comida=$proveedor_comida";
$order_by_platos=" order by nombre_plato";
$order_by_guarniciones=" order by nombre_guarnicion";

if($proveedor_comida){
  $cons_platos="select * from comidas.plato ";
  $cons_guarniciones="select * from comidas.guarnicion ";
	
  $cons_platos.=$where;
  $cons_guarniciones.=$where; 
  
  $cons_platos.=$order_by_platos;
  $cons_guarniciones.=$order_by_guarniciones;
  
  
  $res_platos=$db->execute($cons_platos) or die($cons_platos."<br>".$db->errormsg());
  $cant_platos=$res_platos->RecordCount();
 
  $res_guarniciones=$db->execute($cons_guarniciones) or die($cons_guarniciones."<br>".$db->errormsg());
  $cant_guarniciones=$res_guarniciones->RecordCount();  
// } lo saco y lo llevo al final

//tengo q armar un arreglo con los id de platos q estan habilitados, y otro p los id de guarniciones
$arreglo_platos=array();
$arreglo_guarniciones=array();
for($i=0;$i<$cant_platos;$i++){
	if ($res_platos->fields['habilitado']==1)
        $arreglo_platos[$i]=$res_platos->fields['id_plato'];
       // echo "plato  -> ".$i."-".$arreglo_platos[$i]."<br>";
	$res_platos->MoveNext();   
   };
   
for($i=0;$i<$cant_guarniciones;$i++){
	if ($res_guarniciones->fields['habilitado']==1)
        $arreglo_guarniciones[$i]=$res_guarniciones->fields['id_guarnicion'];
       // echo "guarnicion -> ".$i."-".$arreglo_guarniciones[$i]."<br>";
	$res_guarniciones->MoveNext();   
   };
$res_platos->Move(0);
$res_guarniciones->Move(0); 
}   
?>

<table width=95% border=0 cellspacing=0 cellpadding=3 bgcolor=<?=$bgcolor2?> align="center">
  <tr id=mo>
    <td colspan="2" align="center"><b>Listado de Comidas</b></td>
  </tr>
  <tr>
    <td width="30%">
      <select name="proveedor_comida" onchange="document.form1.submit()">
          <option value=-1>Seleccionar un Proveedor</option>
        <? $res_prov_comidas->Move(0); 
           while (!$res_prov_comidas->EOF) { ?>
          <option value="<?=$res_prov_comidas->fields['id_proveedor_comida']?>"
<? if ($res_prov_comidas->fields['id_proveedor_comida']==$_POST['proveedor_comida']) echo "selected"; ?>>
            <?=$res_prov_comidas->fields['nombre_proveedor_comida']?>
          </option>
        <? $res_prov_comidas->MoveNext(); } ?>
      </select>
    </td>
    <td>&nbsp;</td>
  <tr>
  </tr>  
    <td colspan="2"><hr></td>
  </tr>
  <tr>
    <td>
        <table border="0" align="center" cellpadding="0" cellspacing="0" width="80%">
          <tr><td align="center"><b>Platos</b></td></tr>
          <tr><td>&nbsp;</td></tr>
          <? if($proveedor_comida){
              while (!$res_platos->EOF) { ?>
             <tr>
               <td align="left">
                  <input type="checkbox" name="hab_p_<?=$res_platos->fields['id_plato']?>" value="<?=$res_platos->fields['id_plato']?>"
               <? if (in_array($res_platos->fields['id_plato'], $arreglo_platos)) echo "checked";?>>
               <?=$res_platos->fields['nombre_plato'];?></td>
             </tr>
          <? $res_platos->MoveNext(); }
          } ?> 
          <tr><td>&nbsp;</td></tr>  
          <tr>
            <td align="center">
               <input type="submit" name="habilitar_plato" value="Hab/Deshab Plato">
            </td>
          </tr>
      </table>
    </td>
    <td>
        <table border="0" align="center" cellpadding="0" cellspacing="0" width="80%">
          <tr><td align="center"><b>Guarniciones</b></td></tr>&nbsp;
          <tr><td>&nbsp;</td></tr>
          <? if($proveedor_comida){
              while (!$res_guarniciones->EOF) {?>
             <tr>
               <td align="left">
                  <input type="checkbox" name="hab_g_<?=$res_guarniciones->fields['id_guarnicion']?>" value="<?=$res_guarniciones->fields['id_guarnicion']?>"
               <? if (in_array($res_guarniciones->fields['id_guarnicion'], $arreglo_guarniciones)) echo "checked";?>>
               <?=$res_guarniciones->fields['nombre_guarnicion'];?></td>
             </tr>
          <? $res_guarniciones->MoveNext(); }
          } ?>  
          <tr><td>&nbsp;</td></tr> 
          <tr>
            <td align="center">
               <input type="submit" name="habilitar_guarnicion" value="Hab/Deshab Guarnición">
            </td>
          </tr>
      </table>
    </td>
  </tr>
</TABLE>
<br><br>

<table width=95% border=0 cellspacing=0 cellpadding=3 bgcolor=<?=$bgcolor2?> align="center">
  <tr id=mo>
    <td colspan="2"><b>Nuevas Comidas</b></td>
  </tr>
  <tr>
    <td ><b>Proveedor</b>&nbsp;&nbsp;
    
      <select name="prov_comida" >
          <option value=-1>Seleccionar un Proveedor</option>
        <? 
         $res_prov_comidas->Move(0);
          while (!$res_prov_comidas->EOF) { ?>
          <option value="<?=$res_prov_comidas->fields['id_proveedor_comida']?>"
<?if ($res_prov_comidas->fields['id_proveedor_comida']==$_POST['prov_comida'])echo "selected"; ?>>
            <?=$res_prov_comidas->fields['nombre_proveedor_comida']?>
          </option>
        <? $res_prov_comidas->MoveNext(); } ?>
      </select>
    </td>
    <td><input type="checkbox" name="nuevo_prov" onclick="javascript:(this.checked)?Mostrar('tabla_proveedores'):Ocultar('tabla_proveedores');">
        <b>Agregar un Nuevo Proveedor</b> </td>
  </tr>
  <tr>
    <td>
      <div id='tabla_proveedores' style='display:none'> 
        <table>
          <tr>
            <td><b>Nuevo Proveedor: </b><input type="text" name="nombre_prov"></td>
            <td>&nbsp;&nbsp;<input type="submit" name="guardar" value="Guardar Proveedor" onclick="return control_nuevo_proveedor();"></td>
          </tr>
          <tr>
            <td><input type="radio" name="distrito" value="1"> <b>San Luis</b></td>
            <input type="hidden" name="distrito_sl" value="San Luis">
          </tr>
          <tr>
            <td><input type="radio" name="distrito" value="2"> <b>Buenos Aires</b></td>
            <input type="hidden" name="distrito_bsas" value="Buenos Aires">
          </tr>
       </table>
        </div>  
    </td>
  </tr> 
  <tr>
    <td colspan="2"><b><hr></b></td>
  </tr>
  <tr>  
    <td><b>Nuevo Plato:&nbsp;&nbsp; </b>
        <input type="text" name="nombre_plato"></td>
    <td><input type="submit" name="guardar" value="Guardar Nuevo Plato" onclick="return control_nuevo_plato();"></td>
  </tr>
<!--  
  <tr>
    <td><input type="checkbox" name="asociar_guarnicion" onclick="javascript:(this.checked)?Mostrar('tabla_guarnicion'):Ocultar('tabla_guarnicion');">
        <b>Asociar con otras Guarniciones</b></td>
    <td>&nbsp;</td>
  </tr>
  -->
  <?
  //selecciono de la tabla los grupos q corresonden a las distintas comidas
    $grupo_comida="select * from comidas.grupo_comidas";
    $res_grupo_comidas=$db->execute($grupo_comida) or die ($grupo_comida."<br>".$db->errormsg());
    $cant_grupos=$res_grupo_comidas->RecordCount();
  ?>
  <input type="hidden" name="cant_grupos" value="<?=$cant_grupos?>">
  <tr>      
    <td>
 <!--     <div id='tabla_guarnicion' style='display:none'>  -->
        <table>
          <? for ($i=0;$i<$cant_grupos;$i++) {?>
          <tr>
            <td><input type="radio" name="asociar_guar" value="<?=$res_grupo_comidas->fields['id_grupo_comidas']?>"> 
                <b><?=$res_grupo_comidas->fields['nombre_grupo']?></b>
            </td>
          <tr>
          <? $res_grupo_comidas->MoveNext(); } ?>
        </table>
<!--      </div>   -->
    </td>
  </tr>
  <tr>
    <td colspan="2"><b><hr></b></td>
  </tr>
  <tr>
    <td width="50%"><b>Nueva Guarnición:&nbsp;&nbsp; </b>
        <input type="text" name="nombre_guarnicion"></td>
    <td><input type="submit" name="guardar" value="Guardar Nueva Guarnición" onclick="return control_nueva_guarnicion();"></td>    
  </tr>
  <tr>      
    <td>
         <table>
          <tr>
            <td><input type="radio" name="asociar_plato" value="2"> 
                <b>Guarnición para Pastas</b>
            </td>
          <tr>
          <tr>
            <td><input type="radio" name="asociar_plato" value="3"> 
                <b>Guarnición para todo tipo de Carnes</b>
            </td>
          <tr>
       </table>
    </td>
  </tr>
</table>
<br><br>
</FORM>
</BODY>
</HTML>