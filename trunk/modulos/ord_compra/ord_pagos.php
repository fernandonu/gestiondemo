<?php
/*
Esta Pantalla sirve para configurar los pagos
de las ordenes de compra
Autor: MAC - Fernando

MODIFICADO POR
$Author: marco_canderle $
$Revision: 1.95 $
$Date: 2005/09/03 16:29:31 $
*/
require_once("../../config.php");
include("./fns.php");

//en los arreglos $datos_forma, $datos_nc,$datos_pagos se guardan datos de la orden de compra 
// para mantener un log de las modificaciones de la forma de pago
//todos los datos se guardan en esquema compras_adicional al presionar el boton Aceptar o Separar Ordenes

//Obtenemos el estado de la orden
$estado_orden=$_POST['estado'];

//Si el estado de la orden es parcialmente pagada (d), entramos en modo
//control de pagos, lo cual permite modificar los pagos que aun no se
//han realizado para esta orden, y agregar nuevos pagos, de ser necesario.
if($estado_orden=='d')
 $control_pagos=1;
else
 $control_pagos=0;

$reset=$_POST['reseteado'];
$pagina=$parametros['pagina_viene'] or $pagina=$_POST['pagina'];

$nro=$_POST['nro_orden'] or $nro=$_POST["chk_orden"] or $nro=$parametros['ordenes_multiple'] or $nro=" ";

if(!is_array($nro))
{$nro_orden=array();
 $nro_orden[0]=$nro;
}
else
 $nro_orden=$nro;


$select_moneda=$_POST['id_moneda'];

//si venimos de pago multiple, obtenemos los datos de la orden necesarios
//mas adelante, para que funcione todo como debe ser
if($pagina=="pago_multiple")
{
    $query="select moneda.id_moneda,orden_de_compra.valor_dolar from orden_de_compra join moneda using (id_moneda) where nro_orden=".$nro_orden[0];
    $datos_orden=$db->Execute($query) or die ($db->ErrorMsg()." $query<br>error al recuperar datos de la orden, para el pago multiple");
    $select_moneda=$datos_orden->fields['id_moneda'];

}

//para saber cual es el id de la moneda Dólar
$q="select id_moneda from moneda where nombre='Dólares'";
$moneda=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");

if($_POST['boton']=="Aceptar")
{
 //solo se guarda si hubo cambios, sino no
 if($_POST['cambios_forma']=='si')
 {
  $db->StartTrans();
  //si la orden esta parcialmente pagada, solo se actuliza la forma de pago
  if($control_pagos)
  {
    actualizar_forma_pago($nro_orden,$_POST['forma_pago']);
  }
  else//sino, se crea la nueva forma de pago desde 0.
  {
   $id_forma;$id_plantilla;
   $cantidad_pagos=$_POST['select_cantidad_pagos'];
   if($_POST['cambios_forma']=="si" && $_POST['chk_default']=="1")
    $cambios=1;
   else
    $cambios=0;
  	//dependiendo de si es pago multiple o no, se guardan los pagos de la orden
    if($pagina=="pago_multiple")
     $msg=PM_guardar_forma_pago($cantidad_pagos,$nro_orden,$cambios);
    else
     $msg=guardar_forma_pago($cantidad_pagos,$nro_orden[0],$cambios);


   //guardamos la orden de pago para cada orden de compra en el arreglo
   //si es que esta habilitada la edicion de los montos.
   if((($_POST['guardar_pago']=="si"  && $estado_orden!="")||$pagina=="pago_multiple") && $msg=="")
   {
     $nro_pagos=$cantidad_pagos;
     $cuotas=array($nro_pagos);
     $dolar=array($nro_pagos);
     for($i=0;$i<$nro_pagos;$i++)
     {
      $cuotas[$i]=($_POST["monto_".$i]!="")?$_POST["monto_".$i]:0;
      $dolar[$i]=$_POST["valor_dolar_".$i];
     }

     if($pagina=="pago_multiple")
     {
      if($select_moneda==$moneda->fields['id_moneda'])
       PM_insertar_ordenes_pagos($nro_orden,$cuotas,$dolar);
     else
      PM_insertar_ordenes_pagos($nro_orden,$cuotas);
     }
     else
     {
      if($select_moneda==$moneda->fields['id_moneda'])
       insertar_ordenes_pagos($nro_orden[0],$cuotas,$dolar);
      else
       insertar_ordenes_pagos($nro_orden[0],$cuotas);
     }//del if
     //si hubo error en los montos, envia un  aviso por mail.

     if ($_POST['monto_error']=="true") {
                                //si hubo un error con los montos
                                //envio un mail para confirmar esto

                                $query="select simbolo from moneda where id_moneda=$select_moneda";
                                $simbolo_mon=$db->Execute($query) or die ($db->ErrorMsg()."seleccion del simbolo moneda");
                                $monto_acreditado_mail=0;

                                for($i=0;$i<$nro_pagos;$i++)   $monto_acreditado_mail+=$cuotas[$i];

                                $monto_nc=($_POST['total_nc'])?$_POST['total_nc']:0;
                                $monto_total_mail=monto_a_pagar($nro_orden[0]);
                                //$monto_total_mail=monto_a_pagar($_POST['nro_orden_padre']);
                                $direccion=array();
                                 //$direccion[0]="marco@pcpower.com.ar";

                                 //Si le diferencia es mayor a 1000 le manda a corapi
                                 //Para los demas la diferencia es mayor a 0.10 centavos
                                 $diferencia=abs($_POST["diferencia_montos"]);

                                 if ($diferencia>=1000)
                                                     {
                                                     $direccion[]="corapi@coradir.com.ar";
                                                     }

                                 $direccion[]="noelia@pcpower.com.ar";
                                 $direccion[]="tedeschi@coradir.com.ar";
                                 $direccion[]="juanmanuel@coradir.com.ar";
                                 

                                 mandar_mail($direccion,$_POST['nro_orden_padre'],$monto_total_mail,$monto_acreditado_mail,$monto_nc,$simbolo_mon->fields['simbolo'],$nro_orden,$_ses_user['name']);

                           }//de mandar mail

    }//del if((($_POST['guardar_pago']=="si"  && $estado_orden!="")||$pagina=="pago_multiple") && $msg=="")
  }//del else de if($control_pagos)

  //agregamos la relacion entre las ordenes de compra
  //y las notas de credito seleccionadas (si es que hay alguna)

  admin_notas_c($nro_orden,$_POST['and_nc'],$parametros['armando']);

  
   /********************************************************************************/

   //guarda log por cambios en forma de pago
   if ($estado_orden != null || $estado_orden!="") { 
       guardar_forma_pago_ant('Modificación');
   }
  /********************************************/
  $db->CompleteTrans();
 }//de if($_POST['cambios_forma']=='si')
 
 echo "<script>";
 if($nro_orden[0]==-1 || $nro_orden[0]==0 && $msg!="")
 {
   echo"
    window.opener.document.all.select_pago.length++;
    window.opener.document.all.select_pago.options[window.opener.document.all.select_pago.length-1].text ='".$_POST['titulo_pago']."';
    window.opener.document.all.select_pago.options[window.opener.document.all.select_pago.length-1].value ='$id_plantilla';
    window.opener.document.all.select_pago.options[window.opener.document.all.select_pago.length-1].selected =true;";
 }//de  if($nro_orden!="")
 if($msg=="")
 {
  if($_POST['recargar_padre'])
  {
   $link_padre=encode_link("ord_compra.php",array("nro_orden"=>$_POST['nro_orden_padre']));
   echo "window.opener.location.href='$link_padre';";
  }
 if($pagina=="pago_multiple" &&$_POST['volver_a_padre']!=1)
 {
  $link_pagar=encode_link("./ord_compra_pagar.php",array("nro_orden"=>$nro_orden[0]));
  echo  "document.location.href='$link_pagar';";
 }
 else
  echo "window.close();";

 }
 echo "</script>";


}//de if($_POST['boton']=="Aceptar")

if($_POST["despagamos"]!=0)
{ 
 $cant_pagos=$_POST["hidden_cant_pagos"];
 $db->StartTrans();
 for($i=0;$i<$cant_pagos;$i++)
 {
  //si se apreto el boton para despagar la el pago i-esimo, actualizamos dicho pago 
  //y mandamos mail avisando
  if($_POST["despagar_".$i]=="D$")
  {//antes de desatar cheuque, caja o débito, traemos los datos de ese pago, para despues usarlos
   //en el mail que se va a enviar
   $query="select * from ordenes_pagos join forma_de_pago using(id_forma) 
           join tipo_pago using(id_tipo_pago)
           where id_pago=".$_POST["despagamos"];
   $datos_pago_borrado=sql($query,"<br>Error al traer los datos del pago a borrar") or fin_pagina();
  	//desligamos el cheque, débito o egreso, del pago seleccionado
   $query="update ordenes_pagos set númeroch=null,idbanco=null,id_ingreso_egreso=null,
  	        iddébito=null,fecha=null,usuario=null
  	        where id_pago=".$_POST["despagamos"];
    sql($query,"<br>Error al despagar el pago ".$_POST["despagamos"]."<br>") or fin_pagina();
    
   //volvemos la OC a estado Enviada o Parcialmente Pagada, según corresponda
   $pagos_faltan=pagos_restantes($nro_orden[0]);
   //si la cantidad de pagos restantes es igual a la cantidad de pagos de esta forma de pagos,
   //significa que no se ha realizado ningun pago mas, por lo que la OC vuelve a Enviada.
   if($pagos_faltan==$cant_pagos)
    $estado_nuevo='e';
   else //sino, la OC vuelve a estado Parcialmente Pagada
    $estado_nuevo='d';
    
   //modificamos el estado de la/s OC que este/n en el arreglo
   $tam_arr=sizeof($nro_orden);
   for($r=0;$r<$tam_arr;$r++)
   {$query="update orden_de_compra set estado='$estado_nuevo' where nro_orden=".$nro_orden[$r];
    sql($query,"<br>Error al modificar estado de la Orden de Compra<br>") or fin_pagina();
   }
   
    
   //finalmente construimos el mail avisando de estos cambios.
   //enviamos mail avisando que se despagó la OC
   
   //armamos el detalle del pago
   if($select_moneda==$moneda->fields['id_moneda'])
    $simbolo_moneda="U\$S ";
   else
    $simbolo_moneda="\$ "; 
   
   $pago_desatado="Tipo de Pago: ".$datos_pago_borrado->fields["descripcion"]."\n";
   $pago_desatado.="Cantidad de Días: ".$datos_pago_borrado->fields["dias"]."\n";
   $pago_desatado.="Monto: $simbolo_moneda".formato_money($datos_pago_borrado->fields["monto"])."\n";
   if($simbolo_moneda=="U\$S ")
    $pago_desatado.="Valor Dolar: $ ".number_format($datos_pago_borrado->fields["valor_dolar"],3,'.','')."\n";
   
  if($datos_pago_borrado->fields['id_ingreso_egreso']!="")
  {
   //guardamos la info del pago en la variable que saldrá en el mail
   $pago_desatado.="\nPago realizado el ".fecha($datos_pago_borrado->fields["fecha"])." al contado por $simbolo_moneda".formato_money($datos_pago_borrado->fields["monto"]).".\n";
  }
  //idem sin tiene un débito	
  elseif($datos_pago_borrado->fields['iddébito']!="")
  {
  //guardamos la info del pago en la variable que saldrá en el mail
   $pago_desatado.="\nPago realizado el ".fecha($datos_pago_borrado->fields["fecha"])." mediante Débito de $simbolo_moneda".formato_money($datos_pago_borrado->fields["monto"]).".\n";
  }	
  //idem si tiene un cheque
  elseif($datos_pago_borrado->fields['númeroch']!="")	
  {
   //guardamos la info del pago en la variable que saldrá en el mail
   $pago_desatado.="\nPago realizado el ".fecha($datos_pago_borrado->fields["fecha"])." con cheque a ".$datos_pago_borrado->fields["dias"]." días, Nº ".$datos_pago_borrado->fields["númeroch"]." por $simbolo_moneda".formato_money($datos_pago_borrado->fields["monto"])."\n";
  }
    
   $para="corapi@coradir.com.ar,juanmanuel@coradir.com.ar,noelia@coradir.com.ar";
   //$para="marco@coradir.com.ar";
   if(sizeof($nro_orden)==1)
   {
   	$asunto="Se Deshizo un pago de la Orden de Compra: ".$nro_orden[0]."\n"; 
   	$texto="La Orden de Compra ".$nro_orden[0]." se volvió a estado enviada.\n";
   	$texto.="El pago que se habia realizado se desató de dicha Orden de Compra, pero quedó cargado en el sistema.";
   }
   else 
   {
   	$asunto="Se Deshizo un pago de las Ordenes de Compra: ".join(", ",$nro_orden)."\n"; 
   	$texto.="Las siguientes Ordenes de Compra se volvieron a estado enviada por formar parte de un mismo Pago Múltiple:\n".join(", ",$nro_orden)."\n";
   	$texto.="El pago que se habia realizado se desató de dichas Ordenes de Compra, pero quedó cargado en el sistema.";
   }
   
   $texto.="\n\nOJO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n";
   $texto.="ASEGURESE DE ANULAR El CHEQUE O DÉBITO QUE SE HAYA UTILIZADO EN ESTE PAGO, O DE RE-INGRESAR EL MONTO RESPECTIVO EN LA CAJA CORRESPONDIENTE.\n"; 
   $texto.="\nOJO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n";
   $texto.="Datos del pago desatado:\n";
   $texto.="-----------------------------------------\n";
   $texto.=$pago_desatado;
   $texto.="-----------------------------------------\n\n\n";
   $texto.=detalle_orden($nro_orden[0],1);
   $texto.="\n\nUsuario que realizó la operación: ".$_ses_user["name"]."\n";
   $texto.="Fecha: ".date("d/m/Y H:i:s",mktime());
   //echo $texto;
   enviar_mail($para,$asunto,$texto,"","","","");
 
   $msg="<center><b>El pago de la Orden de Compra ".$nro_orden[0]." se deshizo con éxito</b></center>";
   $recargar_despago=1;
  }// de if($_POST["despagar_".$i]=="D$")
 }//de for($i=0;$i<$cant_pagos;$i++)
 
 $db->CompleteTrans();
}//de if($_POST["despagamos"]!=0)

if($_POST['desatar']=="Separar Ordenes de Compra")
{//Separa las ordenes de compra de un pago multiple a formas
 //de pago separadas (cheque a 30 dias).
 guardar_forma_pago_ant('separar orden de pago múltiple'); // para los de modificaciones
 include("separar_ordenes_PM.php");
 $link_padre=encode_link("ord_compra.php",array("nro_orden"=>$_POST['nro_orden_padre']));
 echo "<script>window.opener.location.href='$link_padre';window.close();</script>";

}

echo "<center><b>$msg</b></center>";

//si se abre por primera vez, obtenemos el valor del select_pago para guardarlo en un post,
//y recargamos la pagina, asi lo tenemos en una variable php y podemos trabajar con el valor correcto

if($parametros['reload'])
{ $link=encode_link('ord_pagos.php',array('reload'=>0,"armando"=>$parametros['armando'],"presupuesto"=>$parametros['presupuesto']));
	
?>
      <body onload='
	  document.all.forma_pago.value=window.opener.document.all.select_pago.value;
	  <?if($select_moneda==0 || $select_moneda==-1)
	     echo "document.all.id_moneda.value=window.opener.document.all.select_moneda.value;";
	  ?>
	  document.all.dolar_orden.value=window.opener.document.all.valor_dolar.value;
	  document.all.proveedor.value=window.opener.document.all.select_proveedor.options[window.opener.document.all.select_proveedor.selectedIndex].text;
	  document.all.id_proveedor.value=window.opener.document.all.select_proveedor.options[window.opener.document.all.select_proveedor.selectedIndex].value;
	  document.all.cliente.value=window.opener.document.all.cliente.value;
	  document.all.fecha.value=window.opener.document.all.fecha_entrega.value;
	  document.all.estado.value=window.opener.document.all.estado.value;
	  document.all.nro_orden.value=window.opener.document.all.nro_orden.value;
	  document.all.pago_especial.value=window.opener.document.all.pago_especial.value;
	  document.all.id_licitacion.value=window.opener.document.all.id_licitacion.value;
	  document.all.nro_orden_padre.value=window.opener.document.all.nro_orden.value;
	  
	  document.all.internacional.value=window.opener.document.all.internacional.value;
	  //si la OC es internacional generamos seteamos los hiddens de datos internacionales
	  if(window.opener.document.all.internacional.value==1)
	  {
	   document.all.total_fob_final.value=window.opener.document.all.total_fob_final.value;
	   document.all.total_flete_final.value=window.opener.document.all.total_flete_final.value;
	   document.all.total_iva_ganancias_final.value=window.opener.document.all.total_iva_ganancias_final.value;
	   document.all.total_ib_final.value=window.opener.document.all.total_ib_final.value;
	   document.all.total_derechos_final.value=window.opener.document.all.total_derechos_final.value;
	   document.all.total_honorarios_final.value=window.opener.document.all.total_honorarios_final.value;
	   document.all.total_global.value=window.opener.document.all.total_global.value;
	  }
	  
	  document.temp_form.submit();
    '>
  <form name="temp_form" method='POST' action="<?=$link?>">
  <input type="hidden" name="forma_pago">
  <input type="hidden" name="id_moneda">
  <input type="hidden" name="dolar_orden">
  <input type="hidden" name="proveedor">
  <input type="hidden" name="id_proveedor">
  <input type="hidden" name="cliente">
  <input type="hidden" name="fecha">
  <input type="hidden" name="estado">
  <input type="hidden" name="nro_orden">
  <input type="hidden" name="pago_especial">
  <input type="hidden" name="id_licitacion">
  <input type="hidden" name="nro_orden_padre">
  
  <!--hiddens para las oc internacionales-->
  <input type="hidden" name="internacional">
  <input type="hidden" name="total_fob_final">
  <input type="hidden" name="total_flete_final">
  <input type="hidden" name="total_iva_ganancias_final">
  <input type="hidden" name="total_ib_final">
  <input type="hidden" name="total_derechos_final">
  <input type="hidden" name="total_honorarios_final">
  <input type="hidden" name="total_global">

  </form>

 </body>
<?
}
else
{
 $detalle_orden=0;
 $cant_pm_orden=-1;
 if($pagina=="" && $estado_orden!="")
 { $pm_orden=PM_ordenes($nro_orden[0]);
   $cant_pm_orden=sizeof($pm_orden);
   if($cant_pm_orden>1)
   {$pagina="pago_multiple";
    $detalle_orden=1;
   }//del if
 } //del if

/**********************************************************************
Controlamos el modo de la pagina para saber qué mostrar y que permitir


***********************************************************************/

//obtenemos si el pago especial esta habilitado
$pago_especial=$parametros['pago_especial'] or $pago_especial=$_POST['pago_especial'] or $pago_especial=0;

//si el estado es pendiente, se permite editar la forma de pago pero no se muestran los montos
if($estado_orden=='p' || $estado_orden=='r' || $estado_orden=="u" || $estado_orden=="" || ($pagina=="pago_multiple" &&$detalle_orden==0))
{$permiso="";
 //los montos se muestran siempre (no como antes)
 $mostrar_montos=1;//$mostrar_montos=0;
 $boton_value="Cancelar";
 $guardar_pagos="si";
}
//si el estado es autorizada y el pago especial esta habilitado,
//se permite editar la forma de pago y se muestran los montos para editar
elseif(($estado_orden=='a' || $estado_orden=='e' || $estado_orden=='d')&& $pago_especial)
{$permiso="";
 $mostrar_montos=1;
 $boton_value="Cancelar";
 $guardar_pagos="si";
}
//para el resto de los casos,
//se permite ver la forma de pago y se muestran los montos solo lectura
else
{$permiso="disabled";
 $mostrar_montos=1;
 $boton_value="Volver";
 $guardar_pagos="";
}
$id_licitacion=$_POST['id_licitacion'];
$forma_pago=$_POST['forma_pago'];
//echo "forma de pago $forma_pago";
$cant_formas=0;
if($forma_pago!=-1 && $forma_pago!=0)
{if($nro_orden[0]!=-1 && $nro_orden[0]!=0)
 {$query="select distinct ordenes_pagos.id_pago,forma_de_pago.id_forma,dias,id_tipo_pago,plantilla_pagos.descripcion,plantilla_pagos.mostrar,ordenes_pagos.monto,ordenes_pagos.valor_dolar,id_ingreso_egreso,iddébito,idbanco,númeroch from forma_de_pago join pago_plantilla using(id_forma) join plantilla_pagos using(id_plantilla_pagos) join ordenes_pagos using(id_forma) join pago_orden using(id_pago) where id_plantilla_pagos=$forma_pago and nro_orden=".$nro_orden[0]." order by id_ingreso_egreso,iddébito,idbanco,númeroch";
  $formas_pagos=$db->Execute($query) or die($db->ErrorMsg()."en select de formas de pago".$query);
  $cant_formas=$formas_pagos->RecordCount();
 }
 if($cant_formas==0)
 {$query="select forma_de_pago.id_forma,dias,id_tipo_pago,plantilla_pagos.descripcion,plantilla_pagos.mostrar from forma_de_pago join pago_plantilla using(id_forma) join plantilla_pagos using(id_plantilla_pagos) where id_plantilla_pagos=$forma_pago";
  $formas_pagos=$db->Execute($query) or die($db->ErrorMsg()."en select de formas de pago".$query);
  $cant_formas=$formas_pagos->RecordCount();
 }

 /*echo "estado $estado_orden";
 if($mostrar_montos && ($estado_orden!='p' && $estado_orden!='r' && $estado_orden!="u" && $estado_orden!=""))
  //traemos la configuracion de la forma de pagos que esta elejida + los montos de la orden de pago
  $query="select forma_de_pago.id_forma,dias,id_tipo_pago,plantilla_pagos.descripcion,plantilla_pagos.mostrar,ordenes_pagos.monto,ordenes_pagos.valor_dolar from forma_de_pago left join pago_plantilla using(id_forma) left join plantilla_pagos using(id_plantilla_pagos) left join ordenes_pagos using(id_forma) left join pago_orden using(id_pago) where id_plantilla_pagos=$forma_pago and nro_orden=".$nro_orden[0];
 else
  //traemos la configuracion de la forma de pagos que esta elejida
  $query="select forma_de_pago.id_forma,dias,id_tipo_pago,plantilla_pagos.descripcion,plantilla_pagos.mostrar from forma_de_pago join pago_plantilla using(id_forma) join plantilla_pagos using(id_plantilla_pagos) where id_plantilla_pagos=$forma_pago";
 $formas_pagos=$db->Execute($query) or die($db->ErrorMsg()."en select de formas de pago".$query);
 $cant_formas=$formas_pagos->RecordCount();
 print_r($query);*/
}


if($_POST['select_cantidad_pagos']!="")
 $cantidad_pagos=$_POST['select_cantidad_pagos'];
elseif($_POST['select_cantidad_pagos']=="" && $cant_formas>0)
 $cantidad_pagos=$cant_formas;
else
 $cantidad_pagos=1;


//constantes que pone la cantidad de pagos posibles
$c_cant_pagos=20;

//seteamos las variables para los datos de las OC internacionales
$internacional=$_POST["internacional"];
$total_fob_final=$_POST["total_fob_final"];
$total_flete_final=$_POST["total_flete_final"];
$total_iva_ganancias_final=$_POST["total_iva_ganancias_final"];
$total_ib_final=$_POST["total_ib_final"];
$total_derechos_final=$_POST["total_derechos_final"];
$total_honorarios_final=$_POST["total_honorarios_final"];
$total_global=$_POST["total_global"];
?>
<HTML>
<HEAD>
<meta Name="generator" content="PHPEd Version 3.2 (Build 3220 )   ">
<title>Formas de Pago</title>
<META Name="description" CONTENT="Pagina que permite agregar nuevas formas de pago">
<meta Name="author" content="Fernando - Marco">
<META HTTP-EQUIV="Reply-to" CONTENT="fernando@pcpower.com.ar - marco@pcpower.com.ar">
<link rel="SHORTCUT ICON"  href="/path-to-ico-file/logo.ico">
</HEAD>
<body bgcolor="<?=$bgcolor2;?>">
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
?>
<script src="../../lib/NumberFormat150.js"></script>
<script src="../../lib/funciones.js"></script>
<script>
var temp_vd=0;
var total_notas_credito=0;
var total_oc=0;

function control_montos() {
//primero sumo los totales de los input text
var importe;
var aux;
var total;
importe=0;


<?
for($i=0;$i<$cantidad_pagos;$i++) {
echo "document.all.monto_$i.value=document.all.monto_$i.value.replace(',','.');\n";
//se hace lo siguiente solo si la moneda de la orden es dolar
if ($_POST['id_moneda']==$moneda->fields['id_moneda']){
 echo "document.all.valor_dolar_$i.value=document.all.valor_dolar_$i.value.replace(',','.');\n";
}
?>
if(document.all.monto_<?=$i?>.value!="")
{
 if (!(isNaN(parseFloat(<?echo "document.all.monto_$i.value";?>))))
     {
     importe+=parseFloat(<?echo "document.all.monto_$i.value";?>);
     }
     //del if";
     else {
        alert('Número Inválido');
        return false;
        }
}
else if(<?if ($estado_orden =='a' || $estado_orden=='e' || $estado_orden=='d' || $pagina=="pago_multiple") echo 1; else echo 0;?>){
	  alert('Número Inválido para el monto');
      return false;
} 

 <? 
 //se hace el siguiente control solo si la moneda de la orden es dolar
  if ($_POST['id_moneda']==$moneda->fields['id_moneda'])
  {
  ?>
    if (isNaN(parseFloat(<?echo "document.all.valor_dolar_$i.value";?>))|| document.all.valor_dolar_<?=$i?>.value==0)
      {
        alert('Valor Dolar Inválido');
        return false;
      }

<?
 } //del control del isNaN
 }//del $valor_dolar_orden
?>
//realizo el control
//total=parseFloat(document.all.monto_total.value);
total=<?if($nro_orden[0]!=0 && $nro_orden[0]!=-1)
        {if($pagina=="")
         {$aux=monto_a_pagar($nro_orden[0]);
          if($aux==0 || $aux=="")
           echo 0;
          else
           echo $aux;
         }
         else
         {?>
          document.all.total_a_pagar.value
         <?}
        }
        else
         echo 0;
      ?>;
//si el total es cero lo dejamos pasar
if(importe==0)
 return true;

//La variable total contiene el total de la orden de compra
//A eso le restamos el total acumulado de las notas de credito seleccionadas.
//De esa manera, el total a pagar es: total=total-total_notas_credito
total-=total_notas_credito;

//luego hacemos el control de que si la suma de los pagos (en la variable importe)
//tiene una diferencia de no mas de 0,10 del total calculado arriba. Alertamos si esa diferencia es mayor que 0,10
aux=total-importe;
//alert('Total a pagar: '+total+' Total notas credito: '+total_notas_credito+' Diferencia: '+aux);
if (((aux<=0.10)&&(aux>=0)) || ((aux>=-0.10)&&(aux<=0))){
                          document.all.monto_error.value=false;
	                      return true;
                          }
                          else
                          {
                           document.all.diferencia_montos.value=aux;
                           document.all.monto_error.value=true;
                           if(confirm('Advertencia - Los valores ingresados no se corresponden con el total, Desea Seguir?'))
                           {
                            return true;
                           }
                           else
                            return false;
                          }


}//de function control_montos()

/******************************************************************
Funcion que acumula en la variable total_nota_credito
los montos de todas las notas de credito seleccionadas
Esto se utiliza para el control de cuanto es el total a pagar
que se calcula: total_a_pagar-total_notas_credito

ESTE TOTAL SE CALCULA SIEMPRE EN LA MONEDA DE LA ORDEN DE COMPRA

Ademas si el valor dolar no se ingreso, se pide ingresar el valor
y no lo deja seleccionar la nota de credito 
*******************************************************************/
var flag_add_tr=0;
function acum_nc(monto,objeto,vd,dolar)
{if(dolar==1)//orden en pesos y nota de credito en dolares
 {//entonces multiplicamos por el valor del dolar
  if(vd.value=="" || vd.value==0)
  {alert('Debe ingresar un valor de dolar');
   objeto.checked=false;
  }
  if( control_numero(vd,'Valor dolar'))
  {
   objeto.checked=false;
  }
  else
   monto*=vd.value;
 }
 else if(dolar==2)  //orden en dolares y nota de credito en pesos
 {//entonces dividimos por el valor del dolar
  if(vd.value=="" ||vd.value==0)
  {alert('Debe ingresar un valor de dolar');
   objeto.checked=false;
  }
  if( control_numero(vd,'Valor dolar'))
  {
   objeto.checked=false;
  }
  else
   monto/=vd.value;	
 }
 total_notas_credito+=monto;
 //agregamos fila con cartel recordatorio
 if(flag_add_tr==0)
 {var fila=document.all.table_nc.insertRow();
  fila.insertCell(0).colSpan=2;
  fila.cells[0].align="center";
  fila.cells[0].innerHTML="<font size=2 color='red'><B>ATENCION: EL TOTAL A PAGAR A CAMBIADO.<BR> RECUERDE CAMBIAR LOS MONTOS DE LOS PAGOS</B></font>";
  flag_add_tr=1; 
 }
 
 document.all.total_nc.value=formato_money(total_notas_credito);
 document.all.total_sin_nc.value=formato_money(total_oc-total_notas_credito);	
}

/******************************************************************
Funcion que resta de la variable total_nota_credito
los montos de todas la nota de credito seleccionada
*******************************************************************/
function deacum_nc(monto,vd,dolar)
{if(dolar==1)
 {monto*=vd.value;
 }  
 else if(dolar==2)
  monto/=vd.value;
 total_notas_credito-=monto;
 
 //agregamos fila con cartel recordatorio
 if(flag_add_tr==0)
 {var fila=document.all.table_nc.insertRow();
  fila.insertCell(0).colSpan=2;
  fila.cells[0].align="center";
  fila.cells[0].innerHTML="<font size=2 color='red'><B>ATENCION: EL TOTAL A PAGAR A CAMBIADO.<BR> RECUERDE CAMBIAR LOS MONTOS DE LOS PAGOS</B></font>";
  flag_add_tr=1;
 }
 document.all.total_nc.value=formato_money(total_notas_credito);
 document.all.total_sin_nc.value=formato_money(total_oc-total_notas_credito);	
}


/******************************************************************
Funcion que controla si el monto pasado es menor o igual que el 
monto total a pagar (en la variable total_notas_credito)
-Alerta si el pago es mayor que el total a pagar y no permite que
 seleccionen esa nota de credito
*******************************************************************/
function control_total_nc(monto,objeto,vd,dolar)
{if(vd.value=="" ||vd.value==0)
 {alert('Debe ingresar un valor de dolar válido');
  objeto.checked=false;
  return 0;
 } 
 if(dolar==1)
 {monto*=vd.value;
 }  
 else if(dolar==2)
  monto/=vd.value;
// alert('total pagar '+total_oc+' total '+total_notas_credito+' monto '+monto);
 if((total_oc-total_notas_credito) < monto)
 {alert('El monto de la Nota de crédito supera el monto a pagar de las ordenes de compra');
  objeto.checked=false;
  return 0;
 }	
 else
  return 1;
}

//script para controlar los datos del formulario
function control_datos(recargar_padre)
{
 var i;
 var j;
 i=parseInt(document.all.select_cantidad_pagos.options[document.all.select_cantidad_pagos.selectedIndex].text);
 if(document.all.titulo_pago.value=="")
 {
  alert('Debe especificar un titulo para esta forma de pago');
  return false;
 }
 <?
 $i=$cantidad_pagos;
 for($j=0;$j<$i;$j++){
    echo "if (document.all.cantidad_dias_$j.value==\"\") {
                    alert('Debe ingresar un Número en el campo Cantidad de Dias en el Pago Nro: $j');
                    return false;}
                    \n";
    echo "if (isNaN(document.all.cantidad_dias_$j.value))
       {
       alert('Debe ingresar un Número Válido en el campo Cantidad de Dias en el Pago Nro: $j');
       return false;
       }
        ";

    }

 if($mostrar_montos)
 {echo" if(!control_montos())";
  echo " return false;";
 }
 ?>

 return true;
}//fin de la funcion que controla los datos


//script para controlar los datos del formulario
function habilita_campos(){
 <?
 $i=$cantidad_pagos;
 for($j=0;$j<$i;$j++){
    echo "document.all.cantidad_dias_$j.disabled=0;\n";
    echo "document.all.select_tipo_pago_$j.disabled=0;\n";
 }
 ?>                    
}//fin de la funcion que limpia campos

//script para controlar los datos del formulario
function resetear(){
 <?
 $i=$cantidad_pagos;
 for($j=0;$j<$i;$j++){
    echo "document.all.cantidad_dias_$j.value='';\n";
    echo "document.all.select_tipo_pago_$j.value='';\n";
 }
 ?>
 document.all.titulo_pago.value="";
 document.all.chk_default.checked=0;
 document.all.reseteado.value=1;
}//fin de la funcion que limpia campos

function limit_cant_pagos()
{
 if(parseInt(document.all.cant_pagos_hechos.value)>=parseInt(document.all.select_cantidad_pagos.options[document.all.select_cantidad_pagos.options.selectedIndex].text))	
 {
 	document.all.select_cantidad_pagos.options.selectedIndex=<?if($cantidad_pagos) echo $cantidad_pagos - 1;else echo "0";?>;
 	return 1;  
 }
 else
  return 0; 
}	

function control_despago(id_pago)
{
 if(confirm('Al despagar una pago de Orden de Compra, se borrarán egresos de caja o se anularán cheques cargados, que hayan sido usados para pagar esa Orden de Compra\n¿Está seguro que desea despagar este pago?'))
   {document.all.despagamos.value=id_pago;
    return true;
   }
   else
    return false;
   return true; 
}
</script>

<?
if($pagina=="pago_multiple")
 {if($cant_pm_orden>1)
 	  $ordenes=$pm_orden;
  else  
    $ordenes=$nro_orden;
      
  $link=encode_link('./ord_pagos.php',array("pagina_viene"=>$pagina,"ordenes_multiple"=>$ordenes,"armando"=>$parametros['armando']));
 }
 else
  $link=encode_link('./ord_pagos.php',array("pagina_viene"=>$pagina,"armando"=>$parametros['armando']));
  
?>
<form name="form1" method="POST" action="<?=$link?>" >
<input type="hidden" name="diferencia_montos" value=0>
<input type="hidden" name="forma_pago" value="<?=$forma_pago?>">
<input type="hidden" name="cambios_forma" value="no">
<input type="hidden" name="reseteado" value=<?=$_POST['reseteado']?>>
<input type="hidden" name="pago_especial" value="<?=$pago_especial?>">
<input type="hidden" name="id_moneda" value="<?=$select_moneda?>">
<input type="hidden" name="estado" value="<?=$estado_orden?>">
<input type="hidden" name="nro_orden" value="<?if($pagina=="pago_multiple")echo "";else echo $nro_orden[0]?>">
<input type="hidden" name="guardar_pago" value="<?=$guardar_pagos?>">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<input type="hidden" name="dolar_orden" value="<?=$_POST['dolar_orden']?>">
<input type="hidden" name="proveedor" value="<?=$_POST['proveedor']?>">
<input type="hidden" name="id_proveedor" value="<?=$_POST['id_proveedor']?>">
<input type="hidden" name="cliente" value="<?=$_POST['cliente']?>">
<input type="hidden" name="fecha" value="<?=$_POST['fecha']?>">
<input type="hidden" name="pago_especial" value="<?=$_POST['pago_especial']?>">
<input type="hidden" name="pagina" value="<?=$pagina?>">
<input type="hidden" name="volver_a_padre" value="<?if(($cant_pm_orden>1 && $detalle_orden)||$control_pagos)echo 1?>">
<input type="hidden" name="nro_orden_padre" value="<?=$_POST['nro_orden_padre']?>">
<input type="hidden" name="monto_error" value=false>
<input type="hidden" name="despagamos" value="0">

<!--Hiddens para las OC Internacionales-->
<input type="hidden" name="internacional" value="<?=$internacional?>">
<input type="hidden" name="total_fob_final" value="<?=$total_fob_final?>">
<input type="hidden" name="total_flete_final" value="<?=$total_flete_final?>">
<input type="hidden" name="total_iva_ganancias_final" value="<?=$total_iva_ganancias_final?>">
<input type="hidden" name="total_ib_final" value="<?=$total_ib_final?>">
<input type="hidden" name="total_derechos_final" value="<?=$total_derechos_final?>">
<input type="hidden" name="total_honorarios_final" value="<?=$total_honorarios_final?>">
<input type="hidden" name="total_global" value="<?=$total_global?>">


<?  //muestra logs

if ($estado_orden != "" || $estado_orden != null) {
$sql_log_pagos="select usuario,fecha,tipo_log,id_log_pago from log_pagos_oc where nro_orden=".$nro_orden[0]. " order by fecha DESC";
$res_log_pagos=sql($sql_log_pagos,"") or fin_pagina();
if ($res_log_pagos->RecordCount() > 0 ) {
?>
<div style="overflow:auto;<? if ($res_log_pagos->RecordCount() > 3) echo 'height:60;' ?> "  >


<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
$onclick="";
while (!$res_log_pagos->EOF) { 
$fecha=$res_log_pagos->fields['fecha'];
$usuario=$res_log_pagos->fields['usuario'];
$tipo_log=$res_log_pagos->fields['tipo_log'];
$log=$res_log_pagos->fields['id_log_pago'];
?>
<tr title="">
    <td height="20" nowrap><b>Fecha:</b> <? echo fecha($fecha)." ".Hora($fecha)?> </td>
    <td nowrap > <b>Usuario:</b> <?=$usuario?> </td>
    <td nowrap > <b>Tipo:</b> <?=$tipo_log?> </td>
    <td nowrap align='center' > <? if ($onclick!="")echo "<input type='button' name='detalle' value='D' style='font-size:9' onclick='$onclick' > "?> </td>
 </tr>
<?	
$link1=encode_link('muestra_ord_pago_ant.php',array("id_log_pago"=>$res_log_pagos->fields['id_log_pago'],"ord_compra"=>$nro_orden));
$onclick="ventana=window.open(\"$link1\",\"\",\"\")";	

$res_log_pagos->MoveNext();
}

$sql="select fecha,tipo_log,nombre,apellido from log_ordenes 
join usuarios on log_ordenes.user_login=usuarios.login
where nro_orden=$nro_orden[0] and tipo_log='de creacion'";
$res=sql($sql) or fin_pagina();

$fecha=$res->fields['fecha'];
$usuario=$res->fields['nombre']." ".$res->fields['apellido'];
$tipo_log='creacion';
?>
<tr title="">
    <td height="20" nowrap><b>Fecha:</b> <? echo fecha($fecha)." ".Hora($fecha)?> </td>
    <td nowrap > <b>Usuario:</b> <?=$usuario?> </td>
    <td nowrap > <b>Tipo:</b> <?=$tipo_log?> </td>
    <td nowrap align='center'> <? if ($onclick!="")echo "<input type='button' name='detalle' value='D' style='font-size:9' onclick='$onclick' > "?> </td>
</tr>
</table>

</div> 
<hr>
<? }
}	 ?> 
<?  $datos_forma=array(); //arreglo para guardar datos anterior a un cambio para el log ?> 
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">
<?if($pagina!="pago_multiple"||$detalle_orden) 
{?>
 <tr>
   <td style=<?="border:$bgcolor3;"?>  align="center" id=mo colspan=2>
     <?
     //si es una OC comun, mostramos el cartel comun
     if(!$internacional)
     {?>
      <b><font size="3">Orden de Compra</font></b>
     <?
     }
     else //sino indicamos que la OC es internacional
     {?>
      <b><font color="#00C021" size="3">Orden de Compra Internacional</font></b> 
      <?
     }
     ?> 
   </td>
 </tr> 
 <tr>
   <td width="50%">
    <b>Nro Orden</b> <?if ($nro_orden[0]!=-1&&$nro_orden[0]!=0)echo $nro_orden[0];else echo "no se especificó";?>
   </td>
   <td align=right width="50%">
    <b>Fecha de Entrega</b> <?if($_POST['fecha'])echo $_POST['fecha'];else echo "no se especificó";?>
   </td>
 </tr>
 <tr>
  <td>
   <b>Proveedor</b> <?if($_POST['proveedor']!=-1)echo $_POST['proveedor'];else echo "no se especificó";?>
  </td>
  <td align="right">
   <b>Cliente</b> <?if($_POST['cliente']!="Haga click en la palabra cliente para ver la lista")echo $_POST['cliente'];else echo "no se especificó";?>
  </td>
 </tr>
 <tr>
  <? 
  if($cant_pm_orden<=1)
  {
   //si la OC es internacional, mostramos el detalle de los montos
   if($internacional)
   {
    if($select_moneda==$moneda->fields['id_moneda'])
                           $simbolo="U\$S"; 
                         else 
                           $simbolo="\$";	
   	?>
   	<td width="50%"> 
     <table width="100%" class="bordes">
      <tr> 
       <td colspan="2" bgcolor="<?=$bgcolor_out?>" align="center">
        <b>Montos de la OC Internacional</b>
       </td>
      </tr>
     <tr>
       <td>
        <b>Total F.O.B.</b>
       </td>
       <td>
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_fob_final);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      <tr>
       <td>
        <b>Flete</b>
       </td>
       <td align="right">
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_flete_final);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      <tr>
       <td>
        <b>Total I.V.A./Ganancias</b>
       </td>
       <td align="right">
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_iva_ganancias_final);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      <tr>
       <td>
        <b>Total I.B.</b>
       </td>
       <td align="right">
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_ib_final);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      <tr>
       <td>
        <b>Total Derechos</b>
       </td>
       <td align="right">
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_derechos_final);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      <tr>
       <td>
        <b>Honorarios y Gastos</b>
       </td>
       <td align="right">
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_honorarios_final);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      <tr>
       <td>
        <b>TOTAL GLOBAL</b>
       </td>
       <td align="right">
        <table width="100%">
         <tr>
          <td style="color:'red';font-weight: bold;">
           U$S 
          </td>
          <td style="color:'red';font-weight: bold;" align="right"> 
           <?=formato_money($total_global);?>
          </td>
         </tr>  
        </table> 
       </td>
      </tr>
      
     </table>  
    </td> 
   <?
   }
   else 
   {
   ?>
   <td>
    <b>Monto <?echo "<font color=red size=2>";
                         if($select_moneda==$moneda->fields['id_moneda'])
                           echo "U\$S "; 
                         else 
                           echo "\$ ";
                           $monto_orden=monto_a_pagar($nro_orden[0]);
                           //echo formato_money(monto_a_pagar($nro_orden[0]));
                           echo formato_money($monto_orden);
                           $datos_forma['monto']=$monto_orden;
                       ?>
    </font> </b>                  
   </td>
  <?
   }
   $colspan="";
  }
  else 
  {$colspan="colspan=2";
  }
  ?>
  <td align="right" <?=$colspan?>>
   <?if($parametros['presupuesto']) 
      $asociado_con="Presupuesto asociado";
     else 
      $asociado_con="Licitación asociada";
   ?>   
   <b><?=$asociado_con?></b> <?if($id_licitacion)echo $id_licitacion;else echo "no posee";?>
  </td>
 </tr>  
</table>
<?
 
 if($cant_pm_orden>1)
 {if($select_moneda==$moneda->fields['id_moneda'])
                       $simbolo_moneda="U\$S ";
                      else
                       $simbolo_moneda="\$ "; 
                 
 $total_a_pagar=ordenes_pago_multiple($pm_orden,$simbolo_moneda,"80%",1);
 }	

}
else 
{ 
 if($select_moneda==$moneda->fields['id_moneda'])
                       $simbolo_moneda="U\$S ";
                      else
                       $simbolo_moneda="\$ "; 
                      
 $total_a_pagar=ordenes_pago_multiple($nro_orden,$simbolo_moneda,"80%",1);
}
?> 
<input type="hidden" name="total_a_pagar" value=<?=$total_a_pagar?>>
<script>total_oc=<?if($total_a_pagar!="")echo $total_a_pagar; elseif($nro_orden[0]!="-1" && !$internacional) echo monto_a_pagar($nro_orden[0]);elseif ($internacional) echo $total_global; else echo 0;?>;</script>
<br>

<?
//Se traen las notas de creditos disponibles (pendientes o reservadas)
//para el proveedor de la orden de compra.
//$query="select id_nota_credito,nota_credito.monto,nota_credito.estado,n_credito_orden.nro_orden,n_credito_orden.valor_dolar,nota_credito.observaciones,id_moneda,simbolo from nota_credito join moneda using (id_moneda) left join n_credito_orden using(id_nota_credito) where id_proveedor=".$_POST['id_proveedor']." and(estado=0 or estado=1)";
$t_nc=sizeof($nro_orden);
$and_nc="";
//si se esta armando el pago multiple, traemos las notas de credito que estan 
//relacionadas con cada orden que participan en el pago

if($parametros["armando"]=="si")
{for($h=0;$h<$t_nc;$h++)
 { if($h!=0)
    $and_nc.=" or";
   $and_nc.=" nro_orden=".$nro_orden[$h];
 }
}
//sino traemos las que estan relacionadas con la primera de ellas 
//(ya que son las mismas que estan relacioandas con las otras
//ordenes que participan en el pago mutiple)
//Y en el caso de que no haya pago multiple tambien se aplica este caso
else
{$and_nc=" nro_orden=".$nro_orden[0]; 
}
//si el estado de la orden es totalmente pagada, solo se muestran las notas de credito
//que se usaron para pagar esa orden
if($estado_orden=='g')
{$and_where="and (estado=2 and ($and_nc))";
}
//sino, se muestran aquellas disponibles + las que ya estan relacionadas con 
//las ordenes de compra
else 
{$and_where="and ((estado=1 and ($and_nc)) or estado=0)";
}		

$query="select id_nota_credito,nota_credito.monto,nota_credito.estado,n_credito_orden.nro_orden,n_credito_orden.valor_dolar,nota_credito.observaciones,id_moneda,simbolo from general.nota_credito join licitaciones.moneda using (id_moneda) left join compras.n_credito_orden using(id_nota_credito) where id_proveedor=".$_POST['id_proveedor']." $and_where";
$notas_credito=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer las notas de credito de los proveedores");

if ($notas_credito->RecordCount()>0)
{
?>
<input type="hidden" name="and_nc" value="<?=$and_nc?>">
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">
<tr>
           <td style=<?="border:$bgcolor3;"?>  align="center" id=mo colspan=5>
                <b>Notas de Crédito</b>
           </td>
</tr>
<tr id=ma>
 <td>
  &nbsp;
 </td>
 <td width="5%">
  Nro
 </td>
 <td width="25%">
  Monto
 </td>
 <td>
  Valor Dolar
 </td>
 <td width="70%">
  Observaciones
 </td>
</tr>

<?
$mostrar_total_notas=0;
$ind=0;

$datos_nc=array();
while(!$notas_credito->EOF)
{ 
 //Mostramos solo aquellas notas de credito que esten en estado pendientes
 //(no asociadas a ninguna orden de compra) 
 // + las que esten asociadas a esta orden de compra
 $checked_nc=($notas_credito->fields['nro_orden']!="")?$notas_credito->fields['nro_orden']:0;

 /*if($notas_credito->fields['estado']==0 || $checked_nc)
 {*/$mostrar_total_notas=1;
  ?>
 <tr>
  <td>  
  <?
  //si la nota de credito es en pesos y la orden es en dolares ==> tomamos en cuenta el valor del dolar        //sino no lo tomamos en cuenta
  if($notas_credito->fields['simbolo']=='$' && ($select_moneda==$moneda->fields['id_moneda']))
    $tipo_dolar="2";
  //si la nota de credito es en dolares y la orden es en pesos ==> tomamos en cuenta el valor del dolar
  elseif($notas_credito->fields['simbolo']=='U$S' && ($select_moneda!=$moneda->fields['id_moneda']))
    $tipo_dolar="1";
  else
   $tipo_dolar="0";
  ?>                                                                                                                                                                                                   									
   <input type="checkbox" name="nota_<?=$notas_credito->fields['id_nota_credito']?>" value=1 <?=$permiso?> <?if($estado_orden=="" && $pagina=="")echo " disabled ";?> 
   onclick="if(this.checked==true)
            {if(control_total_nc(<?=$notas_credito->fields['monto']?>,this,valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>,<?=$tipo_dolar?>))
              acum_nc(<?=$notas_credito->fields['monto']?>,this,valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>,<?=$tipo_dolar?>);
            }
            else 
             deacum_nc(<?=$notas_credito->fields['monto']?>,valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>,<?=$tipo_dolar?>); 
            if(this.checked==1 && document.all.valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>.value!='No se aplica')
            {document.all.valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>.readOnly=1;
             document.all.valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>.title='Para cambiar el valor del dolar debe des-seleccionar esta nota de crédito';
            }
           else 
            if(this.checked==0 && document.all.valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>.value!='No se aplica')
            {document.all.valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>.readOnly=0; 
             document.all.valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>.title='';
            } 
           document.all.cambios_forma.value='si';" <?if($checked_nc)echo "checked";?>
   >
   <?
   //si viene chequeado desde la BD, aumentamos ale acumulador de java script para
   //calcular el total en el control de la orden
 
   if($checked_nc)
   {if($tipo_dolar==0)
    { 
   ?>
    <script>total_notas_credito+=<?=$notas_credito->fields['monto']?>;</script>
   <?
    }
    elseif($tipo_dolar==2)
    {?>
    <script>total_notas_credito+=<?=$notas_credito->fields['monto']/$notas_credito->fields['valor_dolar']?></script>
    <?
    }
    elseif($tipo_dolar==1)
    {?>
    <script>total_notas_credito+=<?=$notas_credito->fields['monto']*$notas_credito->fields['valor_dolar']?></script>
    <?
    }
   }
   ?>
   </td>
  <td align="center">
   <?=$notas_credito->fields['id_nota_credito'];?>
  </td>
  <td>
  <table>
   <tr>
    <td>
     <b><?=$notas_credito->fields['simbolo']?></b>
    </td>
    <td width="100%" align="right"> 
     <b>-<?=formato_money($notas_credito->fields['monto']);?></b>
    </td>
   </tr>
  </table>  
  </td>
  <td>
   <?
    $dis_v_d_n="";
    $v_d_n=number_format($notas_credito->fields['valor_dolar'],3,'.','');
    //si la moneda de la nota de credito es peso, y la de la orden tambien, 
    //no se necesita el valor del dolar
    if($notas_credito->fields['simbolo']=='$' && ($select_moneda!=$moneda->fields['id_moneda']))
    {$v_d_n="No se aplica";
     $dis_v_d_n="disabled";
    }
    //si la moneda de la nota de credito es dolar, y la de la orden tambien, 
    //no se necesita el valor del dolar
    elseif($notas_credito->fields['simbolo']=='U$S' && ($select_moneda==$moneda->fields['id_moneda']))
    {$v_d_n="No se aplica";
     $dis_v_d_n="disabled";
    }	
    //en otro caso si se necesita (nota en dolares y orden en pesos 
    //y nota en pesos y orden en dolar)
   
   /*<input type="text" size="10" <?=$permiso?> <?if($estado_orden=="" && $pagina=="")echo " disabled ";?> name="valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>" <?=$dis_v_d_n?> value="<?=$v_d_n?>" onclick="temp_vd=<?=$notas_credito->fields['monto']?>;" onchange="if(nota_<?=$notas_credito->fields['id_nota_credito']?>.checked==true){if(control_total_nc(<?=$notas_credito->fields['monto']?>,this,valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>,<?=$tipo_dolar?>)){if(total_notas_credito>0)deacum_nc(temp_vd,this,<?=$tipo_dolar?>); acum_nc(<?=$notas_credito->fields['monto']?>,nota_<?=$notas_credito->fields['id_nota_credito']?>,this,<?=$tipo_dolar?>);}} document.all.cambios_forma.value='si';">*/
   ?>  
   <input type="text" size="10" <?=$permiso?> <?if($estado_orden=="" && $pagina=="")echo " disabled ";?> name="valor_dolar_nota_<?=$notas_credito->fields['id_nota_credito']?>" <?=$dis_v_d_n?> value="<?=$v_d_n?>" <?if($checked_nc)echo "readonly title=\"Para cambiar el valor del dolar debe des-seleccionar esta nota de crédito\" ";?>>
  </td>
  <td>
   <?if($notas_credito->fields['observaciones'])echo $notas_credito->fields['observaciones'];else echo "&nbsp;";?>
  </td>
 </tr>
<? 
 //}//del if($notas_credito->fields['estado']==0 ||(in_array($notas_credito->fields['nro_orden'],$nro_orden))

 //arreglo para guardar los datos de notas de credito antes de algun cambio
 $datos_nc[$ind]['simbolo']=$notas_credito->fields['simbolo'];
 $datos_nc[$ind]['id_nota']=$notas_credito->fields['id_nota_credito'];
 $datos_nc[$ind]['monto']=$notas_credito->fields['monto'];
 if ($notas_credito->fields['valor_dolar']) 
       $datos_nc[$ind]['dolar']=$notas_credito->fields['valor_dolar'];
 else   $datos_nc[$ind]['dolar']="NULL"; 
 $datos_nc[$ind]['obs']=$notas_credito->fields['observaciones'];
 if ($checked_nc) 
    $datos_nc[$ind]['chk']=1;  //indica si la nota de credita esta chequeada
    else $datos_nc[$ind]['chk']=0;
 $ind++;
 
 $notas_credito->MoveNext();
}

?>  
</table>
<?
if($mostrar_total_notas==1)
{
?>
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center" id="table_nc">
<tr>
 <td align="center">
  <b>Monto de Notas de Crédito seleccionadas <br>
  <?if($select_moneda==$moneda->fields['id_moneda'])
                           echo "<font color=red>U\$S </font>"; 
                         else 
                           echo "<font color=red>$ </font>";
  ?>                         
  <input type="text" class=text_5 name="total_nc" value="">
  
 </td>
 <td align="center">
  <b>Total a pagar descontando notas de crédito <br>
  <?if($select_moneda==$moneda->fields['id_moneda'])
                           echo "<font color=red>U\$S </font>"; 
                         else 
                           echo "<font color=red>$ </font>";
  ?>
  <input type="text" class=text_5 name="total_sin_nc" value="">
 </td>
</tr> 
<tr id="tr_atention" style="visibility:hidden">
 <td colspan="2" align="center">
  
 </td>
</tr>
</table>
<?
}//de if($mostrar_total_notas==1)
}//de if ($notas_credito->RecordCount>0)
?>
<br>
<table border=1 width="80%" cellspacing=0 cellpadding=5 align="center">
       <tr>
           <td style=<?="border:$bgcolor3;"?>  align="center" id=mo colspan=2>
                <font size=3><b>Forma de Pago</b>
           </td>
       </tr>
      <tr>
      <td>
       <input type="submit" name="nueva_forma_pago" <?=$permiso?> <?if($control_pagos) echo " disabled "?> value="Nueva Forma de Pago" onclick="document.all.reseteado.value=1">
      </td>
          <td align=right >
             <b>Cantidad de Pagos:</b>
             <select name=select_cantidad_pagos <?=$permiso?> onchange="document.all.forma_pago.value=<?if($forma_pago)echo $forma_pago;else echo -1;?>;document.all.cambios_forma.value='si';var limite=eval(parseInt(document.all.cant_pagos_hechos.value) +1); if(<?=$control_pagos?>==1 && limit_cant_pagos())alert('No se puede seleccionar menos de'+ eval(limite) +' pagos. Debe quedar al menos un pago libre para terminar de pagar la/s orden/es.');else window.document.form1.submit();">
             <?
             for($i=1;$i<=$c_cant_pagos;$i++){
               if ($i == $cantidad_pagos)
               { echo $selected="selected";
                 $hidden_cant_pagos=$i;
               }
               else 
                $selected="";
              echo "<option $selected > $i </option>";
             }
             ?>
             </select>
             <input type="hidden" name="hidden_cant_pagos" value="<?=$hidden_cant_pagos?>">
          </td>
    </tr>
      <tr>
        <td colspan=2>
        <table width="100%">
        <tr>
           <td>
           <b> Título de la  Forma de Pago: <input type="text" name="titulo_pago" size="25" value="<?if(!$reset)echo $formas_pagos->fields['descripcion']?>"  <?=$permiso?> <?//if($control_pagos) echo " disabled "?> onchange="document.all.cambios_forma.value='si';">
           </td>
           <?//if($formas_pagos->fields['mostrar']==1&&$mostrar_montos)$default="disabled";else $default="";?>
           <td align="rigth">
            <b> Default <input type="checkbox"  name="chk_default" value="1" title='Recordar los datos en la lista de formas de pago.' <?//if($formas_pagos->fields['mostrar']==1&&!$reset)echo "checked";?> <?=$permiso?> <?if($control_pagos) echo " disabled "?> onchange="document.all.cambios_forma.value='si';" <?//onclick="habilita_campos();"?>>
           </td>
         </tr>
      </table>
    </td>
   </tr>

<?

$default_check=$formas_pagos->fields['mostrar'];
$titulo_anterior=$formas_pagos->fields['descripcion'];
$sql="select * from tipo_pago";
$resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);

if($forma_pago!=-1 && $forma_pago!=0 && $nro_orden[0]!=-1&& $nro_orden[0]!=0)
 $formas_pagos->Move(0);

$cantidad_pagos_hechos=0;

if (!isset($_POST['datos_pagos']))  {
$datos_pagos=array();  //guarda datos de cada pago 
} else {
  $datos_pagos=descomprimir_variable($_POST['datos_pagos']);
}

for ($i=1;$i<=$cantidad_pagos;$i++)  {
  if($control_pagos&&$formas_pagos->fields['id_pago'] && $nro_orden[0])
  { $pago_hecho=pago_realizado($nro_orden[0],$formas_pagos->fields['id_pago']);
    if($pago_hecho)
     $cantidad_pagos_hechos++;
  } 	
  //se hace este if aparte, para no afectar el funcionamiento de la variable $pago_hecho
  //por eso se utiliza la variable $pago_pagado, que se utiliza exclusivamente para los botones
  //de despagar un pago (D$).
  if ($formas_pagos->fields['id_pago'] && $nro_orden[0])
  {
  	$pago_pagado=pago_realizado($nro_orden[0],$formas_pagos->fields['id_pago']);
  }
	?>
  <tr>
   <td bgcolor="#C0C0C0" colspan="2">
   <table width="100%"> <tr>
    <td>
      <b>Pago Nro: <?=$i;?>
     </td>
     <td align="right">
      <b><font size=-2>Recuerde no utilizar separador de miles al insertar los montos</font></b>
     </td>
    </tr></table> 
  </tr>
  <tr>
   <td colspan=2>
    <table width="100%" align="center">
       <tr id="mo">
         <td width="40%">
           Tipo de Pago
         </td>
         <td>
           Cantidad de Días
         </td>
      <?if($mostrar_montos)   
        {?>
      	 <td>
          Monto
         </td>
         <?if($select_moneda==$moneda->fields['id_moneda'])
         {?>
         <td>
          Valor Dolar
         </td>
      <? }
        }?>   
       </tr>
       <tr>
         <td align="center" width="40%">
         <input type="hidden" name="state_pago_<?=$i-1;?>" value="<?if($pago_hecho)echo "pagado";else echo "no_pagado"?>">
         <?$resultado->MoveFirst();?>
         <select name=select_tipo_pago_<?=$i-1;?> style="width:75%" <?if($pago_hecho)echo " disabled "?><?=$permiso?> onchange="document.all.cambios_forma.value='si';">
           <?$previo_selected=0;
             
            while (!($resultado->EOF)){
            $tipo_pago=$resultado->fields['descripcion'];
            $id_tipo_pago=$resultado->fields['id_tipo_pago'];
            ?>
           <option value='<?=$id_tipo_pago;?>' <?if($cant_formas>0&&$formas_pagos->fields['id_tipo_pago']==$id_tipo_pago&&!$reset){echo "selected";$previo_selected=1;}elseif($tipo_pago=="Cheque"&&$previo_selected==0)echo "selected";?>> <?=$tipo_pago;?> </option>
           <?
           $resultado->MoveNext();
           } //del while
            ?>
         </select>
         </td>
         <td align="center">
         <input type="text" name="cantidad_dias_<?=$i-1;?>" size="2" <?if($pago_hecho)echo " disabled "?><?=$permiso?>  value="<?if($cant_formas>0 &&!$reset) echo $formas_pagos->fields['dias']?>" onchange="document.all.cambios_forma.value='si';">
         </td>
     <?if($mostrar_montos)   
      {?>
         <td>
           <?if($select_moneda==$moneda->fields['id_moneda'])echo "U\$S"; else echo "\$";?><input type="text" name="monto_<?=$i-1;?>" size="7" <?if($pago_hecho)echo " disabled "?><?=$permiso?> <?if($estado_orden=="" && $pagina=="")echo " disabled ";?>value="<?if($cant_formas>0 && $formas_pagos->fields['monto']!=0&&!$reset) echo number_format($formas_pagos->fields['monto'],'2','.','')?>" onchange="document.all.cambios_forma.value='si';">
         </td>
         <?if($select_moneda==$moneda->fields['id_moneda'])
         {?>
          <td>
           <input type="text" name="valor_dolar_<?=$i-1;?>" size="4" <?if($pago_hecho)echo " disabled "?><?=$permiso?> value="<?if($cant_formas>0 && $formas_pagos->fields['valor_dolar']) echo number_format($formas_pagos->fields['valor_dolar'],3,'.','');else echo number_format($_POST['dolar_orden'],3,'.','')?>" onchange="document.all.cambios_forma.value='si';">
          </td>
     <?  }
      }

      if($pago_pagado && permisos_check("inicio","permiso_despagar_un_pago"))
      {
      ?>
       <td>
        <input type="submit" name="despagar_<?=$i-1?>" value="D$" onclick="return control_despago(<?=$formas_pagos->fields['id_pago']?>);">
       </td>   
      <?
      }
      ?>
       </tr>

   </table>
   </td>
  </tr>
<?
//guarda datos de cada pago 
if (!isset($_POST['datos_pagos']))  {
$datos_pagos[$i]['tipo']=$formas_pagos->fields['id_tipo_pago'];
$datos_pagos[$i]['dias']=$formas_pagos->fields['dias'];
if  ($select_moneda==$moneda->fields['id_moneda']) {
    $datos_pagos[$i]['simbolo']='U\$S';
    $datos_pagos[$i]['dolar']=$formas_pagos->fields['valor_dolar'];
}
else {
	$datos_pagos[$i]['simbolo']='$';
	$datos_pagos[$i]['dolar']="NULL";

}

if ($formas_pagos->fields['monto'])
    $datos_pagos[$i]['monto']=$formas_pagos->fields['monto'];
    else $datos_pagos[$i]['monto']="NULL";
if ($pago_hecho=="")
    $datos_pagos[$i]['pagada']=0;
    else $datos_pagos[$i]['pagada']=1;
} else {


}

 if($cant_formas>0)
  $formas_pagos->MoveNext();
} //del for

$valores_pagos=comprimir_variable($datos_pagos);
echo "<input type='hidden' name='datos_pagos' value='$valores_pagos'>";

?>
<tr>
 <td align="center" colspan="2">
  <input type="hidden" name="cant_pagos_hechos" value="<?=$cantidad_pagos_hechos?>">
  <?if(($mostrar_montos && $permiso=="" && $estado_orden!="")||$recargar_despago)
     $recargar_padre=1;
    else
     $recargar_padre=0;
   $link_padre=encode_link("ord_compra.php",array("nro_orden"=>$_POST['nro_orden_padre']));
   $recargame="window.opener.location.href='$link_padre';";
     
      ?>
  <input type="hidden" name="recargar_padre" value="<?=$recargar_padre?>">
  <input type="submit"  name="boton"  value="Aceptar" onclick="<?if($cant_pm_orden>1) echo "document.all.pagina.value='';"?>return control_datos(<?=$recargar_padre?>);" <?=$permiso?>>
  <input type="button"   name="boton"  value="<?=$boton_value?>" onclick="if(document.all.cambios_forma.value=='si')
                                                                          {var x=confirm('Se han realizado cambios al formulario. ¿Está seguro que desea salir sin guardar los cambios?');
                                                                           if(x)
                                                                           {if(recargar_padre)
                                                                             <?=$recargame?>
                                                                            window.close();
                                                                           }
                                                                          }
                                                                          else
                                                                          {
                                                                           if(recargar_padre)
                                                                            <?=$recargame?>
                                                                           window.close();
                                                                          }
                                                                          "
  >
 </td>
</tr>
</table>
<br>
<?
//el boton se muestra solo si la orden es parte de un pago multiple,
//si el usuario tiene permiso para este boton y si no se vino de pago multiple
if($estado_orden=='e' && $detalle_orden && permisos_check("inicio","permiso_desatar_PM"))
{?>
 <table width="80%" align="center">
  <tr><td align="right">
   <?
    $confirm_text="";
    $tam=sizeof($pm_orden);
    for($i=0;$i<$tam;$i++)
     $confirm_text.=" ".$pm_orden[$i];
   ?>
   <input type="submit" name="desatar" <?=$permiso?> value="Separar Ordenes de Compra" title="Separa las ordenes de compra del pago multiple a formas de pago separadas (cheque a 30 dias)" onclick="return confirm('Se van a cambiar las formas de pago de las ordenes de compra:<?=$confirm_text?>. ¿Está seguro que desea continuar?')">
  </td></tr>
 </table>
<?
}

//guardo los datos anteriores a un cambio para el log
$datos_forma['cant']=$cant_formas;
$datos_forma['titulo']=$titulo_anterior;

switch ($control_pagos) {
   case 0 : {
   if ($default_check==0) $datos_forma['mostrar']=1;  //habilitado y sin checkear 
     else $datos_forma['mostrar']=2; //habilitado y checkeado
   }
   break;
   case 1: {
   if ($default_check==0) $datos_forma['mostrar']=3; // disabled y sin checkear 
     else $datos_forma['mostrar']=4; // disabled y  checkeado 
   }
   break;
}
 
//paso los datos en un arreglo para mantener datos anteriores a algun cambio
$valores_forma=comprimir_variable($datos_forma);
echo "<input type='hidden' name='datos_forma' value='$valores_forma'>";

$cant_nc=count($datos_nc);
if ($cant_nc > 0) {
$valores_nc=comprimir_variable($datos_nc);
echo "<input type='hidden' name='datos_nc' value='$valores_nc'>";
}
?>
</form>
<?


if($mostrar_total_notas==1)
{
	
?>
<script>
document.all.total_nc.value=formato_money(total_notas_credito);
document.all.total_sin_nc.value=formato_money(total_oc-total_notas_credito);
</script>
<?
   
}
?>
</body>
</HTML>
<?
}


?>