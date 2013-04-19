<?php
/*
$Author: marco_canderle $
$Revision: 1.16 $
$Date: 2005/09/29 15:36:12 $
*/
require_once("../../config.php");

$fecha=$_POST["fecha"] or $fecha=date("d/m/Y",mktime()); 

$cons_admin="select id_usuario from comidas.administradores where id_usuario=".$_ses_user['id'];
$res_admin=$db->execute($cons_admin) or die($cons_admin."<br>".errormsg());
$admin=$res_admin->recordcount();
if ($admin) {
//cuando es un administrador traigo todos los usuarios para que pueda elegir comida a otro
    $cons_us_npedido="select id_usuario, nombre, apellido from usuarios where id_usuario not in 
                     (select id_usuario from comidas.pedido_usuario where fecha_pedido='".Fecha_db($fecha)."')
                      order by nombre ";
    $res_us_npedido=$db->execute($cons_us_npedido) or die ($cons_us_npedido."<br>".errormsg());
    $mostrar_select=1;
    if ($_POST['us_sp'])
     $id_usuario=$_POST['us_sp'];
    else $id_usuario=$_ses_user['id'];
   }
else {
//selecciono la persona que esta logueada
    $nombre=$_ses_user['name'];
    $mostrar_select=0;
    $id_usuario=$_ses_user['id'];
    }

$cons_lugar_pedido="select id_lugar_pedido_comida from usuarios where id_usuario=$id_usuario";
$res_cons_lugar_pedido=$db->execute($cons_lugar_pedido) or die ($cons_lugar_pedido."<br>".errormsg());
$id_lugar_pedido=$res_cons_lugar_pedido->fields['id_lugar_pedido_comida'];
   
$prov_selec=$id_lugar_pedido;

// controlar q el pedido este habilitado
if ($_POST['prov_comidas'] || $prov_selec) {
	$db->StartTrans();   
    $p_c=$_POST['prov_comidas'] or $p_c=$prov_selec;
	$control_pedido_hab="select estado_pedido from comidas.habilitar_pedidos where fecha_pedido='".Fecha_db($fecha)."'
	                     and id_proveedor_comida=$p_c";
	$res_control_pedido_hab=$db->execute($control_pedido_hab) or die ($control_pedido_hab."<br>".$db->errormsg());
	$estado_pedido=$res_control_pedido_hab->fields['estado_pedido'];
	$db->CompleteTrans(); 
	$xpost=1;
}
else {
//control para deshabilitar los pedidos cuando no esta habilitado 
$control_hab_pedido="select * from comidas.habilitar_pedidos
                     left join comidas.proveedor_comida using (id_proveedor_comida)
                     where fecha_pedido='".Fecha_db($fecha)."' 
                     and id_distrito=$id_lugar_pedido";
$res_control_hab_pedido=$db->execute($control_hab_pedido) or die($control_hab_pedido."<br>".$db->errormsg());
$control_hp=$res_control_hab_pedido->recordcount(); 
$estado_hab_pedido=$res_control_hab_pedido->fields['estado_pedido']; 
$xpost=0;
}
if ($estado_pedido==0 && $xpost){
  $disabled_boton_realizar_pedido="disabled";
  $disabled_select_comidas="disabled";
  //$disabled_select_proveedor="disabled";
  $no_mostrar_cartel=0;
  $accion="No se puede realizar el pedido, está deshabilitado ";
}

if ($_POST['guardar_comida'] == "Realizar Pedido") {
	 $db->StartTrans();   
	// controlo quien es el q esta logueado p recuperar el usuario a quien corresponde 
	// la comida
	if ($_POST['mostrar_select']==0) 
	    $usuario_pedido=$_POST['usuario_log'];
    else
	    $usuario_pedido=$_POST['us_sp'];
	
    $plato_pedido=$_POST['plato'];
    $guarnicion_pedido=$_POST['guarnicion'];
    
    $cons_grupo="select id_grupo_comidas from comidas.plato where id_plato=$plato_pedido";
    $res_cons_grupo=$db->execute($cons_grupo) or die ($cons_grupo."<br>".$db->errormsg());
	$grupo=$res_cons_grupo->fields['id_grupo_comidas'];
	if ($grupo==1) {
	   $guarnicion_pedido="null";
	}  
    
	if ($estado_pedido){
	  if ($control) {
	  	$accion="No puede realizar más de un pedido para esta fecha";
		}
	  else {	
	   $insert_pedido="insert into comidas.pedido_usuario 
	                  (fecha_pedido, id_usuario, id_plato, id_guarnicion, id_proveedor_comida)
	                  values ('".Fecha_db($fecha)."', $usuario_pedido, $plato_pedido, $guarnicion_pedido, $p_c)";
	   $res_insert_pedido=$db->execute($insert_pedido) or die ($insert_pedido."<br>".$db->errormsg());
	   //$cons_distrito_prov="select id_distrito from comidas.proveedor_comida 
	   //                     where id_proveedor_comida=$p_c";
	   //$res_cons_distrito_prov=$db->execute($cons_distrito_prov) or die ($cons_distrito_prov."<br>".$db->errormsg());
	   //$id_distrito=$res_cons_distrito_prov->fields['id_distrito'];
	   /*$update_usuarios="update sistema.usuarios set id_lugar_pedido_comida=$p_c 
	                     where id_usuario=$usuario_pedido";
	   $res_update_usuarios=$db->execute($update_usuarios) or die ($update_usuarios."<br>".$db->errormsg());*/
	   $accion="El pedido se registró con Exito"; 
	   
	  } 
	 } // if estado pedido
	else $accion="No se puede guardar el pedido, el Pedido para el día de hoy no esta habilitado ";
    //$link=encode_link('comidas_pedidos.php', array("accion"=>$accion));
    //header("Location:$link");
    
    $db->CompleteTrans();   
 } // if post guardar comida -> realizar pedido   

// controlar q el usuario no haga + d 2 pedidos en el dia
$control_pedido_us="select id_plato, nombre_plato, id_guarnicion, nombre_guarnicion from comidas.pedido_usuario
                    join comidas.plato using (id_plato)
                    left join comidas.guarnicion using (id_guarnicion)
	                where fecha_pedido='".Fecha_db($fecha)."' and id_usuario=$id_usuario"; 
$res_control_pedido_us=$db->execute($control_pedido_us) or die($control_pedido_us."<br>".$db->errormsg());
$control=$res_control_pedido_us->recordcount();
$nbre_plato=$res_control_pedido_us->fields['nombre_plato'];
$nbre_guar=$res_control_pedido_us->fields['nombre_guarnicion']; 
if ($nbre_guar) $mostrar=$nbre_plato." con ".$nbre_guar;
else $mostrar=$nbre_plato;
if ($control) {
	if (!$admin) {
      $disabled_boton_realizar_pedido="disabled";
      $disabled_select_comidas="disabled";
      $disabled_select_proveedor="disabled";
      $no_mostrar_cartel=0;
      $accion="El pedido ya fue realizado <br> Su pedido para el día de hoy: ".$mostrar; 
	}
}


echo $html_header;
echo aviso($accion);
?>
<script src="../../lib/popcalendar.js"></script>
<script LANGUAGE="JavaScript"> 
<?
// consulta para armar el arreglo en javascript de los platos y las guaarniciones asociadas 

if ($_POST['prov_comidas']!="") 
	$proveedor=" and id_proveedor_comida=".$_POST['prov_comidas'];
elseif ($admin && $prov_selec)	$proveedor=" and id_proveedor_comida=".$prov_selec;
elseif ($prov_selec)  $proveedor=" and id_proveedor_comida=".$prov_selec;

if ($_POST['prov_comidas']!="" || $prov_selec) {
//selecciono los platos del proveedor 
$platos="select * from comidas.plato where habilitado=1 $proveedor ";
$platos.=" order by nombre_plato"; 
$res_platos=$db->execute($platos) or die($platos."<br>".$db->errormsg());

//armo la consulta de las guarniciones q estan asociadas con el plato elegido

while (!$res_platos->EOF){
	$asociado_guarnicion=$res_platos->fields['id_grupo_comidas'];
	switch ($asociado_guarnicion) {
	    case 2: $guarniciones="select * from comidas.guarnicion 
                where habilitado=1 $proveedor  and id_grupo_comidas=2  order by nombre_guarnicion ";
                $res_guarniciones=$db->execute($guarniciones) or die($guarniciones."<br>".$db->errormsg());
                $armar_array=1;
                break;
	    case 3: $guarniciones="select * from comidas.guarnicion 
                where habilitado=1 $proveedor  and id_grupo_comidas=3  order by nombre_guarnicion ";
                $res_guarniciones=$db->execute($guarniciones) or die($guarniciones."<br>".$db->errormsg());
                $armar_array=1;
                break;
        default:  $armar_array=0;
                  break;        
	    } //fin del switch
	 ?>
       var comida_<?=$res_platos->fields['id_plato']?>=new Array();
       comida_<?=$res_platos->fields['id_plato']?>[0]='<?=$res_platos->fields['nombre_plato']?>';
       comida_<?=$res_platos->fields['id_plato']?>[1]=new Array();
<?     if ($armar_array) {
       $i=0;
       while (!$res_guarniciones->EOF) {  ?>	    
            comida_<?=$res_platos->fields['id_plato']?>[1][<?=$i?>]=new Array();
            comida_<?=$res_platos->fields['id_plato']?>[1][<?=$i?>]['id']=<?=$res_guarniciones->fields['id_guarnicion']?>;
            comida_<?=$res_platos->fields['id_plato']?>[1][<?=$i?>]['nombre']='<?=$res_guarniciones->fields['nombre_guarnicion']?>';
<?          $i++;
            $res_guarniciones->MoveNext(); 
          } // del while 
       $deshab_select_guarnicion="enabled";      
    } //del if por armar_array
// si no tengo q armar el arrelgo con los platos y las guarniciones 
// deshabilito el select de las guarniciones 
    else {
      $deshab_select_guarnicion="disabled"; 
      } // fin del else
  
    $res_platos->MoveNext();
 } //fin del while por los platos 
//} // si se selecciono un proveedor 
?>

//de acuerdo al plato seleccionado, trae las guarniciones.
function guarniciones_asociadas() {
	var info;
    var x;
    var seguir=1;
    var id_plato;    
    //vaciamos el select de guarniciones
    document.form1.guarnicion.length=0;
    id_plato=document.all.plato.options[document.form1.plato.selectedIndex].value;
    info=eval("comida_"+id_plato);
    if(seguir) {
        largo=info[1].length;
        for(x=0;x<largo;x++) {
           document.all.guarnicion.length++;
           document.all.guarnicion.options[document.all.guarnicion.length-1].text=info[1][x]['nombre'];
           document.all.guarnicion.options[document.all.guarnicion.length-1].value=info[1][x]['id'];
           } //del for
        } //del if
} //de la funcion
<? }
?>
function control_datos(){
  var info, id_plato, largo;
  var plato, guarnicion; 
  var fecha=document.all.fecha.value;   
  if (document.all.mostrar_select.value==1) {
        if (document.all.us_sp.options[document.all.us_sp.selectedIndex].value==-1){
           alert ("Debe seleccionar un usuario para realizar un pedido");
           return false;
         }
	} 
  if (document.all.plato.value==""){
     alert ("Debe seleccionar un plato para el pedido");
     return false;
     }
  id_plato=document.all.plato.options[document.all.plato.selectedIndex].value; 
  info=eval("comida_"+id_plato)   
  largo=info[1].length;
  if (largo!=0) { 
    if (document.all.guarnicion.value==""){
       alert ("Debe seleccionar una guarnición para el pedido");
       return false;
     } 
  } 
 plato=document.all.plato.options[document.all.plato.selectedIndex].text; 
 if (largo!=0) {
 	guarnicion=document.all.guarnicion.options[document.all.guarnicion.selectedIndex].text;
 	return confirm('Su Pedido de hoy es: "'+plato+'" con "'+guarnicion+'"');
 }
 else return confirm('Su Pedido para la fecha '+fecha+' es "'+plato+'"');       
}
</script>
<FORM name="form1" action="comidas_pedidos.php" method="POST">

<TABLE width=95% border=0 cellspacing=0 cellpadding=3 bgcolor=<?=$bgcolor2?> align="center">
  <tr id=mo>
    <td align="center" colspan="4"><font size="2"><b>Pedido de Comidas</b></font></td></tr>
  <tr>  
    <td colspan="2" align="right"><font size="3"><b>Fecha de Pedido</b></font></td>
    
    <td colspan="2">
     <input type="text" name="fecha" readonly value="<?=$fecha?>" size="10">
     <?=link_calendario("fecha")?><input type="submit" name="cambiar_fecha" value="Cambiar Fecha">
    </td>
  </tr>
  <tr>
    <td><b>Apellido y Nombre:</b></td>
    <td align="left"> 
    <input type="hidden" name="mostrar_select" value="<?=$mostrar_select?>">
    <? if ($mostrar_select) {  ?>
       <select name="us_sp" onchange="document.form1.submit()">
         <option value=-1>Seleccionar</option>
         <? while (!$res_us_npedido->EOF) {?>
           <option value="<?=$res_us_npedido->fields['id_usuario']?>"
<? if ($res_us_npedido->fields['id_usuario']==$_POST['us_sp']) echo "selected"; ?>>
           <?=$res_us_npedido->fields['nombre']." ".$res_us_npedido->fields['apellido']?>
           </option>
         <? $res_us_npedido->MoveNext(); } ?>  
       </select>       
    <? } else { ?> 
      <b><input type="text" name="nombre" class=text_4 readonly value="<?=$nombre?>" size="35"></b>
      <input type="hidden" name="usuario_log" value="<?=$id_usuario?>">
    <? } ?>
    </td>
    <td><b>Proveedor</b></td>
    <?
    $con_prov_comidas="select * from comidas.proveedor_comida where activo=1 order by nombre_proveedor_comida";
    $res_prov_comidas=$db->execute($con_prov_comidas) or die($con_prov_comidas."<br>".$db->errormsg());
    ?>
    <td>
      <select name="prov_comidas" onchange="document.form1.submit()" <?=$disabled_select_proveedor;?>>
          <option value=-1>Seleccionar un Proveedor</option>
          <? while (!$res_prov_comidas->EOF) { ?>
              <option value="<?=$res_prov_comidas->fields['id_proveedor_comida']?>"
<? if ($res_prov_comidas->fields['id_proveedor_comida']==$_POST['prov_comidas']) echo "selected"; elseif ($_POST['prov_comidas']=="" && $res_prov_comidas->fields['id_proveedor_comida']==$prov_selec) echo "selected"; ?>>
              <?=$res_prov_comidas->fields['nombre_proveedor_comida']?>
              </option>
          <? $res_prov_comidas->MoveNext(); } ?>
      </select>
    </td>
  </tr>
  <tr><td colspan="4">&nbsp;</td></tr>
  <? // si no elige proveedor no tiene q armar los select  
 // if ($_POST['prov_comidas']!="") {
  ?>
  <tr>  
    <td colspan="2"><b>Platos del Día:</b></td>
    <td colspan="2"><b>Guarniciones:</b></td>
  </tr>
  <tr>  
    <? if ($_POST['prov_comidas']!="" || $prov_selec) { 
      $res_platos->Move(0); ?>
    <td colspan="2">
      <select name="plato" size="15" style="width=90%" onchange="guarniciones_asociadas();" <?=$disabled_select_comidas;?>>
        <? while (!$res_platos->EOF){ ?>
        <option value=<?=$res_platos->fields['id_plato']?>>
           <?=$res_platos->fields['nombre_plato']?>
         </option>
        <? $res_platos->MoveNext(); }
          } ?>
      </select>
    </td>
    <td colspan="2">
      <select name="guarnicion" size="15" style="width=90%" <?=$disabled_select_comidas;?>>
      
      </select>
    </td>
  </tr> 
  <tr><td colspan="4">&nbsp;</td></tr>
  <? if ($estado_pedido==0) $disable="disabled";
     if ($disabled_boton_realizar_pedido=="disabled") $disabled_boton="disabled";  ?>
  <tr align="center">
  <? //} // fin del if ($_POST['prov_comidas']!="")
  //else {
  	if ($disabled_boton_realizar_pedido=="disabled" && $no_mostrar_cartel) { ?>
    <td colspan="3" align="center"><font size="3" color="Red"><b>No puede realizar más de un pedido para esta fecha</b></font></td>
    <? } ?>
<!--    <td colspan="3" align="center"><font size="3" color="Red"><b>Debe seleccionar un Proveedor</b></font></td> -->
    <td align="right">
     <input type="submit" name="guardar_comida" value="Realizar Pedido" <?=$disabled_boton?> onclick="return control_datos();">
    </td>
  </tr>
</table>
</FORM>
</BODY>
</HTML>