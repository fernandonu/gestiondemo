<?
/*
Autor: Broggi
Modificado por:
$Author: mari $
$Revision: 1.207 $
$Date: 2007/01/19 19:52:11 $
*/
//A TODO EL QUE VEA ESTE ARCHIVO LE PIDO DISCULPA PORQUE QUEDO MUY HORRIBLE, DE POR SI YA ESTABA FEO, AHORA ESTA PEOR.
//PERDON. DIEGO BROGGI, SI TENGO TIEMPO TRATO DE MEJORARLO.
//linesa broggi 760,934,1077 es donde estan las consultas que traen las distintas ordenes de compra
require_once("../../config.php");
  // print_r($parametros);
require_once("funciones.php");
if ($parametros['downloadfile'])
{

$filenamefull=UPLOADS_DIR."/Licitaciones/{$parametros['id_lic']}/{$parametros['filename']}.zip";
  $filenamefull=enable_path($filenamefull);
	if ($parametros['zip'])
	{
			$filename=$parametros['filename'].".zip";
			$filetype="application/zip";
	}
	else
	{
			$filename=$parametros['filename'].".doc";
			$filetype="application/doc";
	}
	FileDownload($parametros['zip'], $filename, $filenamefull, $filetype,
$parametros['filesize']);
	die;
}

//para enviar correo
if ($_POST['benviar'])
{	$id=$_POST['id'];
	$buffer="A continuación te paso el listado de la mercadería correpondiente a la licitación Nº $id, esta mercadería te está llegando en estos días \n\n\n";

$buffer.="-----------------------------------------------------------------------------------------\n" ;
    $q="select estado, fecha_entrega, nro_orden, razon_social from orden_de_compra join proveedor using(id_proveedor) where id_licitacion=$id;";
    $r1=sql($q, "<br>$q") or fin_pagina();//$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
    while (!$r1->EOF)
    {
     $nro_oc=$r1->fields['nro_orden'];
     $prov=$r1->fields['razon_social'];
     $q="select sum(fila.cantidad) as pedidos,descripcion_prod as producto from fila where fila.nro_orden=$nro_oc group by nro_orden,descripcion_prod";
     $r2=sql($q) or fin_pagina();
     $buffer.="OC: $nro_oc ($prov)\n";
     $buffer.="Fecha de entrega: ".date2("L",$r1->fields['fecha_entrega'])."\n";
     $buffer.="Productos:\n";
     while (!$r2->EOF)
     {
     	$buffer.="\t".$r2->fields['pedidos']."  ".$r2->fields['producto']."\n";
     	$r2->movenext();
     }


$buffer.="-----------------------------------------------------------------------------------------\n" ;
     $r1->movenext();
    }
   $mail_header="";
   $mail_header .= "MIME-Version: 1.0";
   $mail_header .= "\nfrom: Sistema Inteligente de CORADIR <".$_ses_user['mail'].">";
   $mail_header .= "\nReply-To: ".$_ses_user['name']." <".$_ses_user['mail'].">";
    $msg="<font color=red size=+1>No se pudo enviar su correo</font>";
	if (mail($_POST['correo'],$_POST['asunto'],$buffer,$mail_header))
	{
	 $q="insert into mail_seg_ord (user_login,user_name,fecha_envio,mail,id_licitacion) ";
	 $q.="values ('$_ses_user[login]','$_ses_user[name]','".date("Y-m-j H:i:s")."','$_POST[correo]',$id)";
	 if (sql($q))
	  $msg="Su correo se envió correctamente";
	}
}
//enviar el mail de listo para entregar
if ($_POST["Mail_entrega"])
{
	$entidad = $_POST["entidad"];
	$para= "
	noelia@coradir.com.ar,
	corapi@coradir.com.ar,
	carlos@coradir.com.ar,
	juanmanuel@coradir.com.ar,
	dalsanto@coradir.com.ar
	";
	$asunto="Listo para entrega, Licitación $id...";
	$contenido="Licitación $id correspondiente a la entidad $entidad esta lista para ser entregada...";
 	//echo $contenido."-----";
 	$id_entrega_estimada = $parametros["id_entrega_estimada"];
 	$fecha = date("Y-m-j H:i:s");
 	$usuario = $_ses_user["name"];
 	$tipo = 1;
 	$comentario = "Envio mail de listo para entregar";
	if (enviar_mail($para,$asunto,$contenido,"","","",0)){
		$sql="Insert into log_mail_listo_entrega (id_entrega_estimada,fecha,usuario,tipo,comentario) values ($id_entrega_estimada,'$fecha','$usuario',$tipo,'$comentario')";
		if (sql($sql,"  $sql"))
	  	$msg="Su correo se envió correctamente";
	}
}

$q="select mail from mail_seg_ord where id_mail=( select max(id_mail) from mail_seg_ord where user_login='$_ses_user[login]')";
$mail=sql($q) or fin_pagina();
$last_mail=$mail->fields['mail'];

if ($_POST["bandera"])
{
    $valor=$_POST["chk_bandera"];
    if (!$valor) $valor=0;
    $sql="update entrega_estimada set flag_compras_consolidadas=$valor where ";
    $sql.=" id_licitacion=".$_POST['id']." and nro=".$_POST['nro'];
    sql($sql) or fin_pagina();
}

if ($_POST["gua_presentacion"])
{
	$id_ent = $parametros["id_entrega_estimada"];
    $nom_pre=$_POST["nom_par"];
    $tele_pre=$_POST["tele_par"];
    $mail_pre=$_POST["mail_par"];
    $otros=$_POST['otros'];
  	$nro_cuit=$_POST['nro_cuit'];
  	$razon_social_para_factura=$_POST['razon_social_para_factura'];
  	$domicilio_para_factura=$_POST['domicilio_para_factura'];
  	$fact_orig=$_POST['fact_orig'];
  	$rem_orig=$_POST['rem_orig'];
  	if ($_POST['libre_deuda']) $libre_deuda=1;
  	else $libre_deuda=0;
  	if ($_POST['ultimo_sus']) $ultimo_sus=1;
  	else $ultimo_sus=0;
  	if ($_POST['ing_brutos']) $ing_brutos=1;
  	else $ing_brutos=0;
  	$lugar_pres_fact=$_POST['lugar_pres_fact'];
  	guardar_contactos_segumientos($id_ent,2,$nom_pre,$tele_pre,$mail_pre,$otros,$nro_cuit,$razon_social_para_factura,$domicilio_para_factura,$fact_orig,$rem_orig,$libre_deuda,$ultimo_sus,$ing_brutos,$lugar_pres_fact);
}

if ($_POST["gua_entrega"])
{
	$id_ent = $parametros["id_entrega_estimada"];
    $nom_entre=$_POST["nom_entrega"];
    $tele_entre=$_POST["tele_entrega"];
    $mail_entre=$_POST["mail_entrega"];
    if($_POST['fecha']!="")
    	$fecha1="'".Fecha_db($_POST['fecha'])."'";
    else
    	$fecha1="null";
    $sql="update licitaciones.entrega_estimada set fecha_estimada=$fecha1 where id_entrega_estimada=".$id_ent;
	$result=sql($sql, "c16 ".$sql) or fin_pagina();
    $otros='';
  	$nro_cuit='';
  	$razon_social_para_factura='';
  	$domicilio_para_factura='';
  	$fact_orig='';
  	$rem_orig='';
  	if ($_POST['libre_deuda']) $libre_deuda=1;
  	else $libre_deuda=0;
  	if ($_POST['ultimo_sus']) $ultimo_sus=1;
  	else $ultimo_sus=0;
  	if ($_POST['ing_brutos']) $ing_brutos=1;
  	else $ing_brutos=0;
  	$lugar_pres_fact='';

    guardar_contactos_segumientos($id_ent,1,$nom_entre,$tele_entre,$mail_entre,$otros,$nro_cuit,$razon_social_para_factura,$domicilio_para_factura,$fact_orig,$rem_orig,$libre_deuda,$ultimo_sus,$ing_brutos,$lugar_pres_fact);
}

if($_POST["guardar_com"]) {
 $id_subir=$parametros['id_subir'];
 $sql="insert into comentarios_seguimientos(id_subir,id_usuario,comentario,fecha_comentario) values($id_subir,".$_ses_user["id"].",'".$_POST["nuevo_coment"]."','".date("Y-m-d H:i:s")."')";
 sql($sql) or fin_pagina();
}

if($_POST['valor_boton'])
 $_POST['boton']=$_POST['valor_boton'];



switch ($_POST['boton'])
{
	case "Guardar":
                  require_once("guardar_seguimiento.php");

                  break;
	case "Finalizar Seguimiento":
                  require_once("guardar_seguimiento.php");
                  break;
	case "Volver":
                 header("location: ver_seguimiento_ordenes.php");break;
	default:{
//viene de seg. de prod
//de licitaciones
//de benviar mail
 $id=$parametros['id'] or $id=$parametros['licitacion'] or $id=$_POST['id'];
//de seg. de prod			//de enviar mail	 //nunca se inserto ninguna licitacion

 $nro=$parametros['nro'] or $nro=$_POST['nro'] or $nro=1;

if (!$_POST['benviar'] && $id && $nro=="")  //viene de licitaciones y no envio de mail
 {
  $sql="select max(nro) from entrega_estimada where id_licitacion=$id";
  $resultado_max=sql($sql, "<br>$sql") or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
  $nro=$resultado_max->fields['max'] + 1;
 }

echo $html_header;
cargar_calendario();

?>
<script language="javascript">
var cargar='si';

//para mostrar la ventanita del mail
function show_div(obj)
{
  var leftpos=0;
  var toppos=0;
  if (div_mail.style.visibility=='hidden')
  {
	  div_mail.left= form.bmail.offsetParent.offseLeft;
	  div_mail.top=form.bmail.offsetParent.offsetTop + obj.offsetHeight +2  ;
	  div_mail.style.visibility='visible';
	  document.all.correo.focus();
  }
  else
  {
  	hide_div();
  }
}
function hide_div()
{
 div_mail.style.visibility="hidden";
}
//hasta aca *****************************************************

function control_boton()
{if(cargar!='no')
{
 if (window.event.keyCode==13)
 {document.all.valor_boton.value='Guardar';
  document.all.form.submit();
 }
 if (window.event.keyCode==27)
 {document.all.valor_boton.value='Cancelar';
  document.all.form.submit();
 }
}
}

//*************aca tengo que meter codigo broggi********************************
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla,nro)
{oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 if (obj_tabla.style.display=='none')
    {obj_tabla.style.display='inline';
     oimg.show=0;
     oimg.src=img_ext;
     if (nro==4) oimg.title='Ocultar Archivos';
	 else
	 {
	if (nro==8) oimg.title='Ocultar Contactos';
	else
	{
	if (nro==9) oimg.title='Ocultar Contactos';
	else
	 oimg.title='Ocultar Ordenes';
	}
    }
    }
 else
    {obj_tabla.style.display='none';
    oimg.show=1;
	oimg.src=img_cont;
	if (nro==4) oimg.title='Mostrar Archivos';
	else
	{
	if (nro==8) oimg.title='Mostrar Contactos';
	else
	{
	if (nro==9) oimg.title='Mostrar Contactos';
	else
	oimg.title='Mostrar Ordenes';
	}
	}
    }
}

function control_entrega1()
{
 if(document.all.nom_par.value=="")
 {
  alert('Debe llenar el campo nombre del Contacto Presentación de la Factura');
  return false;
 }
 if(document.all.tele_par.value=="")
 {
  alert('Debe llenar el campo telefono del Contacto Presentación de la Factura');
  return false;
 }
 return true;
}


function control_entrega2()
{
 if(document.all.nuevo_coment.value=="")
 {
  alert('Debe llenar el campo comentario');
  return false;
 }
 return true;
}
</script>
<?
//**************************************hasta aca*********************************

$id_entrega_estimada=$parametros['id_entrega_estimada'] or
$id_entrega_estimada=$_POST['id_entrega_estimada'];
$sql="select vence_oc as vence, nombre,subido_lic_oc.lugar_entrega from ((licitaciones.licitacion join licitaciones.subido_lic_oc on
	subido_lic_oc.id_entrega_estimada=$id_entrega_estimada
      and licitaciones.licitacion.id_licitacion=subido_lic_oc.id_licitacion)
      join licitaciones.entidad on entidad.id_entidad=licitacion.id_entidad)";
$resultado=sql($sql, "<br>$sql") or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
$luga=$resultado->fields['lugar_entrega'];
/*$fecha1=$parametros['fe_estimada'] or
$fecha1=$_POST['fecha'];*/
?>
<form name="form" method="post">
<input type="hidden" name="id" value="<? echo $id; ?>">
<input type="hidden" name="nro" value="<? echo $nro; ?>">
<?
if ($_ses_user["login"]=="juanmanuel" || $_ses_user["login"]=="fernando" || $_ses_user["login"]=="broggi"){
   $sql="select flag_compras_consolidadas from entrega_estimada where id_entrega_estimada=$id_entrega_estimada";
   $bandera=sql($sql) or fin_pagina();
   if ($bandera->fields["flag_compras_consolidadas"]) $checked="checked";
                                                else  $checked="";

?>
<b>Considerar Productos en  Compras Consolidadas </b>&nbsp;
<input type=checkbox name=chk_bandera value=1 <?=$checked?>>
&nbsp;
<input type=submit name=bandera value=Aceptar>
<?
}
?>
<center>
<?
if ($id_entrega_estimada){
	/*$sql = "select * from log_cambio_fecha where id_entrega_estimada=$id_entrega_estimada";
	$log = sql($sql) or fin_pagina();*/

	/*if ($log->RecordCount()>0) {
		echo "<b>Log de cambios de fecha:</B>\n
		<div style='position:relative; width:90%; height:11%; overflow:auto;\n'>
		<table width='100%' cellpadding='1' cellspacing='1' align='center'>\n";

		while(!$log->EOF){
			list($fecha,$hora)=split(" ",$log->fields['fecha']);
			?>
 			<tr id=ma>
  			<td align="left" width="25%">
   			Usuario: <?=$log->fields['usuario']?>
  			</td>
  			<td width="25%">
   			<?=fecha($fecha)?> <?=$hora?>
  			</td>
  			<td align="left" width="50%">
   			Comentario: <?=$log->fields['comentario']?>
  			</td>
 			</tr>
 			<?
 			$log->MoveNext();
		}
	echo "</table></div>\n";
	}*/
	////////cambie en la consulta * por campos/////////////////////////////////
	$sql = "select fecha,usuario,comentario from log_mail_listo_entrega where id_entrega_estimada=$id_entrega_estimada";
	$log = sql($sql) or fin_pagina();
	if ($log->RecordCount()>0) {
		echo "<b>Log de mail de listo para entrega:</B>\n
		<div style='position:relative; width:90%; height:11%; overflow:auto;\n'>
		<table width='100%' cellpadding='1' cellspacing='1' align='center'>\n";

		while(!$log->EOF){
			list($fecha,$hora)=split(" ",$log->fields['fecha']);
			?>
 			<tr id=ma>
  			<td align="left" width="25%">
  	 		Usuario: <?=$log->fields['usuario']?>
  			</td>
  			<td width="25%">
   			<?=fecha($fecha)?> <?=$hora?>
  			</td>
  			<td align="left" width="50%">
   			Comentario: <?=$log->fields['comentario']?>
  			</td>
 			</tr>
 			<?
 			$log->MoveNext();
		}
		echo "</table> </div>\n";
	}
}
?>
</center>


<div align="right">
<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/ordprod/ayuda_seg_prod.htm" ?>', 'SEGUIMIENTO DE PRODUCCIÓN')" >
 </div>

<table width="100%">
<?
//si se envio correo, muestro mensaje de log
if ($msg)
	echo "<tr><td colspan=4 align=center><b>$msg</b></td></tr>";
?>

<TR>
<td>
<table width="100%" align="left">

<tr>
<?
$link=encode_link("../licitaciones/detalle_presupuesto.php",array("ID"=>$id,"id_lic_prop"=>-1,"id_entrega_estimada"=>$parametros['id_entrega_estimada'],"id_subir"=>$parametros['id_subir'],"nro_orden_cliente"=>$parametros['nro_orden_cliente'],"pagina"=>"listado"));

$link_numero=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$parametros['id_subir'],"solo_lectura"=>1));
?>
<td align="left" colspan="2"><b>SEGUIMIENTO DE ORDEN: <a href="<?=$link_numero?>" target="_blank"><?echo $parametros['nro_orden_cliente'];?></b></a><input type="button" name="lista_expedicion" value="Lista de Expedición" onclick="window.open('<?=encode_link("listado_expedicion.php",array("id_subir"=>$parametros['id_subir']))?>','','left=40,top=80,width=800,height=600,resizable=1,status=1,scrollbars=1')">
</td>
<?
$sql_temp = "select monto_ganado,id_moneda, valor_dolar_lic from licitacion where id_licitacion=$id";
$resultado_temp=sql($sql_temp, "<br>$sql_temp")or fin_pagina();//$db->Execute($sql_temp) or die ($db->ErrorMsg()."<br>".$sql_temp);


$nro_or=$parametros['nro_orden_cliente'];
$sel_ren="select id_subir from licitaciones.subido_lic_oc
          WHERE id_licitacion = $id and nro_orden='$nro_or'";
$res_ren=sql($sel_ren,"No se pudo recuperar los renglones")or fin_pagina();
$subir=$res_ren->fields['id_subir'];
if($subir!="")
{
$mont="select sum(renglones_oc.precio*renglones_oc.cantidad) as precio
                                           from  licitaciones.subido_lic_oc
                                           join licitaciones.renglones_oc using(id_subir)
                                           join licitaciones.renglon using(id_renglon)
                                           where subido_lic_oc.id_subir=$subir
                                           ";
$ganado=sql($mont,"No se pudo recuperar el monto total")or fin_pagina();
$gan_monto=$ganado->fields['precio'];
}

if($parametros['fin']==1)
{
 $id_en=$parametros['id_entrega_estimada'];
 $sel_foto="select monto_prod from foto_seguimiento where id_entrega_estimada=$id_en";
 $foto=sql($sel_foto,"No se pudo recuperar los datos de la foto") or fin_pagina();
 $monto_prod=$foto->fields['monto_prod'];

}
else
{
$mon_prod="select sum(precio_stock*en_produccion.cantidad) as monto
from stock.en_stock join general.producto_especifico using(id_prod_esp)
     join stock.en_produccion using(id_en_stock)
where id_licitacion=$id";
$prod=sql($mon_prod,"No se pudo recuperar el monto de Bs As") or fin_pagina();
$monto_prod=$prod->fields['monto'];
}
$mon_bs="select sum(precio_stock*stock.detalle_reserva.cantidad_reservada) as monto
     from stock.en_stock join general.producto_especifico using(id_prod_esp)
     join stock.detalle_reserva using(id_en_stock)
     join general.depositos using(id_deposito)
where general.depositos.nombre='Buenos Aires' and id_licitacion=$id";
$bs=sql($mon_bs,"No se pudo recuperar el monto de Produccion") or fin_pagina();
$sql="select valor from general.dolar_general";
$cons=sql($sql) or fin_pagina();
$val=formato_money($cons->fields['valor']);

if($gan_monto!=0)
{
$val=$val*$monto_prod;
$val=$val/$gan_monto;
}
else
$val=0;

$totales1=0;
while(!$res_ren->EOF)
{
$subir=$res_ren->fields['id_subir'];
$res="SELECT
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion,
  SUM(licitaciones.producto.cantidad * licitaciones.renglones_oc.cantidad) AS cantidad
FROM
  licitaciones.producto
  LEFT JOIN licitaciones.renglones_oc ON (licitaciones.producto.id_renglon = licitaciones.renglones_oc.id_renglon)
  LEFT JOIN general.productos ON (licitaciones.producto.id_producto = general.productos.id_producto)
WHERE
  licitaciones.renglones_oc.id_subir = $subir and producto.tipo='conexos'
  GROUP BY
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion";
$res=sql($res,"No se pudo recuperar los renglones")or fin_pagina();
$restar=$res->fields['precio_licitacion'];
$restar_cant=$res->fields['cantidad'];
$restar=$restar_cant*$restar;
$suma="SELECT
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion,
  SUM(licitaciones.producto.cantidad * licitaciones.renglones_oc.cantidad) AS cantidad
FROM
  licitaciones.producto
  LEFT JOIN licitaciones.renglones_oc ON (licitaciones.producto.id_renglon = licitaciones.renglones_oc.id_renglon)
  LEFT JOIN general.productos ON (licitaciones.producto.id_producto = general.productos.id_producto)
WHERE
  licitaciones.renglones_oc.id_subir = $subir and producto.tipo<>'conexos'
  GROUP BY
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion";
$suma=sql($suma,"No se pudo recuperar los renglones")or fin_pagina();
$sumar1=0;
while(!$suma->EOF)
{
$sumar=$suma->fields['precio_licitacion'];
$sumar_cant=$suma->fields['cantidad'];
$sumar=$sumar*$sumar_cant;
$sumar1=$sumar+$sumar1;
$suma->MoveNext();
}
$totales=($sumar1-$restar)+$totales;
$res_ren->MoveNext();
}
$totales1=$totales+$totales1;
if($gan_monto!=0)
{
$totales1=($totales1*$resultado_temp->fields['valor_dolar_lic'])/$gan_monto;
}
else
$totales1=0;


if ($parametros['id_subir']!="")
{
$sql="select nro_orden from subido_lic_oc where id_subir=".$parametros['id_subir'];
$resultado_subir=sql($sql, "<br>$sql") or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

$link_entrega=encode_link('seleccionar_renglon_adj.php',array("id_entrega_estimada"=>$parametros['id_entrega_estimada'],"licitacion"=>$id,"numero"=>$nro,"pagina_volver"=>'entregas.php',"oc"=>$resultado_subir->fields['nro_orden'],"cliente"=>$resultado->fields['nombre'],"vencimiento"=>$resultado->fields['vence'],"monto_prod"=>$monto_prod));
}
else $link_entrega="";
$sql="select id_prorroga from prorroga where id_entrega_estimada = ".$parametros['id_entrega_estimada'];
$result_prorroga=sql($sql, "<br>$sql") or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

if($result_prorroga->recordcount()>0)

$link_prorroga=encode_link('prorrogas.php',array("id_prorroga"=>$result_prorroga->fields['id_prorroga'],"nro_orden_cliente"=>$parametros['nro_orden_cliente']));
//$link_materiales=encode_link("seguimiento_orden_materiales.php",array("id_licitacion"=>$id));
$link_materiales=encode_link("../ordprod/seguimiento_orden_materiales_pm.php",array("id_licitacion"=>$id,"mostrar_pedidos"=>1));
$link_pedido=encode_link("../mov_material/producto_lista_material.php", array("ID"=>$id, "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "id_subir"=>$parametros['id_subir']));
$link_pack=encode_link("packaging.php", array("id"=>$id, "id_entrega"=>$parametros['id_entrega_estimada'], "id_subir"=>$parametros['id_subir']));

$link_costo = encode_link("../licitaciones/costo_real.php",array("id_entrega_estimada"=>$id_entrega_estimada));
?>

<td align="right">
    <input type="button" name="boton_costo_real" value="Costo Real" onclick="window.open('<?=$link_costo?>')">&nbsp;&nbsp;&nbsp;
	<? if (permisos_check("inicio","seguimiento_boton_materiales")) { ?>
	<input type="button" name="boton_materiales" value="Materiales" onclick="window.open('<?=$link_materiales;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">&nbsp;&nbsp;&nbsp;
	<? } ?>
	<input type="button" name="boton_mate" <?if ($link_entrega=="") {echo " disabled "; echo "title='No tiene orden Subida'";}?> value="Lista de materiales" onclick="window.open('<?=$link_pedido;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">&nbsp;&nbsp;&nbsp;
	<input type="button" name="boton_entrega" <?if ($link_entrega=="") {echo " disabled "; echo "title='No tiene orden Subida'";}?> value="Ver entrega" onclick="window.open('<?=$link_entrega;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">&nbsp;&nbsp;&nbsp;
	<?
	if($result_prorroga->recordcount()>0)
	{
	?>
	<input type="button" name="boton_prorroga" value="Ver Prorroga" onclick="window.open('<?=$link_prorroga;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">
	<?
	}
	////////cambie en la consulta * por campos select nro_orden,id_licitacion,/////////////////////////////////
	$consulta="select u1.apellido||', '||u1.nombre as nombre_lider, u2.apellido||', '||u2.nombre as nombre_patrocinador, fecha_apertura,
			ordenes.ord_prod, compras.ord_compras, nro, nro_lic_codificado, exp_lic_codificado, dir_entidad,entrega_estimada.fecha_estimada
		from licitaciones.licitacion l
			left join licitaciones.entrega_estimada using (id_licitacion)
			left join sistema.usuarios u1 on (lider=u1.id_usuario)
			left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
			left join (
				select id_licitacion, licitaciones.unir_texto(nro_orden||', ') as ord_prod
				from ordenes.orden_de_produccion op
				where estado<>'AN' and id_licitacion=$id
				group by id_licitacion
			)as ordenes using (id_licitacion)
			left join (
				select id_licitacion, id_entrega_estimada, licitaciones.unir_texto(nro_orden||', ') as ord_compras
				from (select nro_orden,id_licitacion,id_entrega_estimada from compras.orden_de_compra where estado<>'n' order by nro_orden)as oc
				where id_entrega_estimada=$id_entrega_estimada
				group by id_licitacion, id_entrega_estimada
			)as compras using (id_entrega_estimada)
		where l.id_licitacion=".$id." and id_entrega_estimada=".$id_entrega_estimada;
	$rta_consulta=sql($consulta, "C352") or fin_pagina();
	?>
	<input type="button" name="mailto" value="Mail to ..." onclick='document.location.href="mailto:?subject=Mail%20licitación%20id%20<?=$id?>&body=Id%20licitación:%20<?=$id?>%0D%0A"+
			"Entidad:%20<?=$resultado->fields['nombre']?>%0D%0A"+
			"Dirección:%20<?=$rta_consulta->fields['dir_entidad']?>%0D%0A"+
			"Número:%20<?=$rta_consulta->fields['nro_lic_codificado']?>%0D%0A"+
			"Expediente:%20<?=$rta_consulta->fields['exp_lic_codificado']?>%0D%0A"+
			"Fecha%20de%20apertura:%20<?=Fecha(substr($rta_consulta->fields["fecha_apertura"],0, 10))?>%0D%0A"+
			"Hora%20de%20apertura:%20<?=substr($rta_consulta->fields["fecha_apertura"],11)?>%0D%0A"+
			"Líder:%20<?=$rta_consulta->fields["nombre_lider"]?>%0D%0A"+
			"Patrocinador:%20<?=$rta_consulta->fields["nombre_patrocinador"]?>%0D%0A"+
			"Órdenes%20de%20producción:%20<?=substr($rta_consulta->fields["ord_prod"], 0, strlen($rta_consulta->fields["ord_prod"])-2)?>%0D%0A"+
			"Órdenes%20de%20compra:%20<?=substr($rta_consulta->fields["ord_compras"], 0, strlen($rta_consulta->fields["ord_compras"])-2)?>%0D%0A"+
			"Nro.%20de%20seguimiento:%20<?=$rta_consulta->fields["nro"]?>%0D%0A"'>
	</td>
</tr>
<?
$fec_est=$rta_consulta->fields['fecha_estimada'];
if ($parametros['id_subir']!="")
{
?>
<tr>
<td align="left" colspan="3" >
<a href="<? echo $link; ?>" target="_blank"><u><b>Presupuestar Licitación</b></a></u>&nbsp;&nbsp;&nbsp;&nbsp;
</td>
</tr>
<?
}
else
{
?>
<tr>
<td align="left" colspan="3" >
&nbsp;
</td>
</tr>
<?
}
$sql="select id_licitacion_prop,titulo
      from licitacion_presupuesto_new
      where id_entrega_estimada=$id_entrega_estimada
      order by id_licitacion_prop";
$resultado_presupuesto=sql($sql, "<br>$sql")or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
$i=1;
while (!$resultado_presupuesto->EOF)
{
 if(($i%3)==1)
  {
?>
  <tr>
<?
  }
?>
<td align="left" valign="top" width="33%"><? $link=encode_link("../licitaciones/detalle_presupuesto.php",array("id_lic_prop"=>$resultado_presupuesto->fields['id_licitacion_prop'],"ID"=>$id,"id_entrega_estimada"=>$parametros['id_entrega_estimada'],"id_subir"=>$parametros['id_subir'],"nro_orden_cliente"=>$parametros['nro_orden_cliente'])); ?><a href="<? echo $link; ?>" target="_blank"><b><u><font color="Black">Titulo: </font><? echo $resultado_presupuesto->fields['titulo']; ?></u></a></td>
<?
$resultado_presupuesto->MoveNext();
if(($i%3)==0)
  {
?>
  </tr>
<?
  }
$i++;
}
if((($i%3)==2) || (($i%3)==0))
  {
?>
  </tr>
<?
  }
?>
</table>
</TD>
</TR>
<TR>
<TD>
<INPUT type="hidden" name="entidad" value="<?=$resultado->fields['nombre'];?>">
<table border="1">
<tr>
<td><a href="<? echo encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id)); ?>" target="_blank"><font size="3"><b>ID:<? echo $id; ?></b></font></a></td>
    <td colspan="3"><font color="Red" size="3"><b>Vencimiento OC: <? echo Fecha($resultado->fields['vence']); ?></b></font></td>
 </tr>
 <tr>
  <td colspan="4">
   <?$sql="select nombre,apellido from licitaciones.licitacion
           left join sistema.usuarios on (lider=id_usuario)
           where id_licitacion=$id";
     $resul_lider=sql($sql,"Error al traer el lider de la Licitación") or fin_pagina();
     if ($resul_lider->RecordCount()>0) $lider=$resul_lider->fields['apellido'].", ".$resul_lider->fields['nombre'];
     else $lider="Lider no Cargado";
   ?>
   <font size="3"><b>Lider Licitación:&nbsp;<?=$lider?></b></font>
  </td>
 </tr>
  <tr>
    <td width="30%"><b>Entidad:<? echo $resultado->fields['nombre']; ?></b></td>
    <?
    /*$sql_temp = "select monto_ganado,id_moneda, valor_dolar_lic from licitacion where id_licitacion=$id";
    $resultado_temp=sql($sql_temp, "<br>$sql_temp")or fin_pagina();//$db->Execute($sql_temp) or die ($db->ErrorMsg()."<br>".$sql_temp);
*/
    ?>
    <td width="30%"><b>Monto Facturado
     <?
    $sql3="select cobranzas.nro_factura,id_factura,facturas.estado from cobranzas join facturas using (id_factura) where cobranzas.id_licitacion=$id;";
    $resultado3=sql($sql3, "<br>$sql3")or fin_pagina();//$db->Execute($sql3) or die ($db->ErrorMsg()."<br>".$sql3);

   $cantidad_facturas=$resultado3->RecordCount();
   if ($cantidad_facturas >0){

   $where="where ";
   $where.=" facturas.estado <> 'a' and (";
     for ($i=0;$i<$cantidad_facturas;$i++){
     if ($i==$cantidad_facturas-1)
        $where.=" id_factura=".$resultado3->fields['id_factura'].")";
     else
        $where.=" id_factura=".$resultado3->fields['id_factura']." or ";
     $resultado3->MoveNext();
       }
   $where.="  GROUP BY cobranzas.id_moneda ";
   $sql="select cobranzas.id_moneda,sum(monto) as total";
   $sql.=" from facturacion.facturas ";
   $sql.=" join licitaciones.cobranzas using (id_factura) ";
   $sql.=$where;
   $resultado=sql($sql, "<br>$sql")or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

 if ($resultado->fields['id_moneda'] == 1){
        echo "<b> "."\$ ".formato_money($resultado->fields['total'])."</b>";
        $resultado->MoveNext();
        }

    if ($resultado->fields['id_moneda'] == 2)
        {
        echo "<b> "."U\$S ".formato_money($resultado->fields['total'])."</b>";
        }
   }
  ?>
     </b></td>
     <td width="25%">
     <b>Monto ganado (Lic.)
     <?  echo "\$ ".formato_money($gan_monto);

     ?>
     </b></td>
     <td width="20%"><b>Ganancia Real</b>
</td>
</tr>
  <tr><td>
    <table class="bordes" cellpadding="0" cellspacing="0" width="100%"><tr class="bordes"><td class="bordes"><b>Orden de Produccion&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
    <td align="center" class="bordes"><b>Cant.</b></td>
    <?
    $sql="select estado_bsas,orden_de_produccion.cantidad,orden_de_produccion.desc_prod,orden_de_produccion.estado,nro_orden,id_ensamblador from orden_de_produccion where orden_de_produccion.estado<>'AN' and id_licitacion=$id";
    $resultado_prov=$resultado=sql($sql, "<br>$sql")or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
    //$resultado_prov=sql($sql, "<br>")or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);

    //consulta para saber el el id_ensamblador
    ////////cambie en la consulta * por campos/////////////////////////////////
    $sql="select id_ensamblador from entrega_estimada where id_licitacion=$id and nro=$nro";
    $res_control_estado=sql($sql) or fin_pagina();
    $fecha1=$res_control_estado->fields['fecha_estimada'];
    if (($res_control_estado->fields['id_ensamblador']==4) || ($resultado_prov->fields['id_ensamblador']==4)){
    	echo "<td align='center' class='bordes'><b>Estado</b></td>";
    }?>

    </tr>
    <?
	   if ($resultado->RecordCount()>0) //devolvió algun registro
        {if ($resultado->fields['estado_bsas']=="") $completo="bgcolor='red' title='Pendiente'";
         if ($resultado->fields['estado_bsas']==1) $completo="bgcolor='yellow' title='En Producción'";
         if ($resultado->fields['estado_bsas']==2) $completo="bgcolor='green' title='Historial'";
         if ($resultado->fields['estado_bsas']==3) $completo="bgcolor='yellow' title='Embalaje'";
         if ($resultado->fields['estado_bsas']==4) $completo="bgcolor='yellow' title='Calidad'";
         if ($resultado->fields['estado_bsas']==5) $completo="bgcolor='yellow' title='En Inspección'";
         if ($resultado->fields['estado_bsas']=="") $estado_prod_bsas="Pendiente";
         if ($resultado->fields['estado_bsas']==1) $estado_prod_bsas="En Producción";
         if ($resultado->fields['estado_bsas']==2) $estado_prod_bsas="Terminada";
         if ($resultado->fields['estado_bsas']==3) $estado_prod_bsas="Embalaje";
         if ($resultado->fields['estado_bsas']==4) $estado_prod_bsas="Calidad";
         if ($resultado->fields['estado_bsas']==5) $estado_prod_bsas="En Inspección";
    ?>
    <tr>
    <?
    //echo $resultado->fields['nro_orden'];
    if ($resultado->fields['nro_orden']<1141){
    	$link=encode_link("ordenes_nueva_gestion_2.php",Array("nro_orden"=>$resultado->fields['nro_orden'],"modo"=>"modificar","volver"=>encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$parametros["id"], "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$parametros["nro_orden"],"nro"=>$parametros["nro"],"id_subir"=>$parametros["id_subir"],"nro_orden_cliente"=>$parametros["nro_orden_cliente"]))));
    }
    else{
    	$link=encode_link("ordenes_nueva.php",Array("nro_orden"=>$resultado->fields['nro_orden'],"modo"=>"modificar","volver"=>encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$parametros["id"], "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$parametros["nro_orden"],"nro"=>$parametros["nro"],"id_subir"=>$parametros["id_subir"],"nro_orden_cliente"=>$parametros["nro_orden_cliente"]))));
    }
    ?>
    <td class="bordes" align="center" <?=$completo?>><font color="blue"><b><?php echo $resultado->fields['nro_orden']; ?></font> <input type="button" name="boton" value="Ver" onclick="window.open('<?=$link?>','','')" style="cursor:hand">
    <input type="button" name="boton" value="Q" onclick="document.location='<? echo encode_link("../ordquem/ver_orden.php",Array("id"=>$resultado->fields['nro_orden'],"volver"=>encode_link("../ord_prod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$parametros["id"], "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$parametros["nro_orden"],"nro"=>$parametros["nro"],"id_subir"=>$parametros["id_subir"],"nro_orden_cliente"=>$parametros["nro_orden_cliente"])))); ?>'" style="cursor:hand"></td>
    <td align="center" class="bordes"><font color="blue"><b><?php echo
$resultado->fields['cantidad']; ?></b></font></td>

	<?if (($res_control_estado->fields['id_ensamblador']==4) || ($resultado_prov->fields['id_ensamblador']==4)){
    	echo "<td align='center' class='bordes'><font color='blue'><b>$estado_prod_bsas</b></font></td>";
	}?>
    </tr>
    <? $resultado->MoveNext();


    while (!$resultado->EOF)
     {if ($resultado->fields['estado_bsas']=="") $completo="bgcolor='red' title='Pendiente'";
      if ($resultado->fields['estado_bsas']==1) $completo="bgcolor='yellow' title='En Producción'";
      if ($resultado->fields['estado_bsas']==2) $completo="bgcolor='green' title='Historial'";
      if ($resultado->fields['estado_bsas']==3) $completo="bgcolor='yellow' title='Embalaje'";
      if ($resultado->fields['estado_bsas']==4) $completo="bgcolor='yellow' title='Calidad'";
      if ($resultado->fields['estado_bsas']==5) $completo="bgcolor='yellow' title='En Inspección'";
      if ($resultado->fields['estado_bsas']=="") $estado_prod_bsas="Pendiente";
      if ($resultado->fields['estado_bsas']==1) $estado_prod_bsas="En Producción";
      if ($resultado->fields['estado_bsas']==2) $estado_prod_bsas="Terminada";
      if ($resultado->fields['estado_bsas']==3) $estado_prod_bsas="Embalaje";
      if ($resultado->fields['estado_bsas']==4) $estado_prod_bsas="Calidad";
      if ($resultado->fields['estado_bsas']==5) $estado_prod_bsas="En Inspección";
    ?>
    <tr >
    <?
    //echo $resultado->fields['nro_orden'];
    if ($resultado->fields['nro_orden']<1141){
    	$link=encode_link("ordenes_nueva_gestion_2.php",Array("nro_orden"=>$resultado->fields['nro_orden'],"modo"=>"modificar","volver"=>encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$parametros["id"], "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$parametros["nro_orden"],"nro"=>$parametros["nro"],"id_subir"=>$parametros["id_subir"],"nro_orden_cliente"=>$parametros["nro_orden_cliente"]))));
    }
    else{
    	$link=encode_link("ordenes_nueva.php",Array("nro_orden"=>$resultado->fields['nro_orden'],"modo"=>"modificar","volver"=>encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$parametros["id"], "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$parametros["nro_orden"],"nro"=>$parametros["nro"],"id_subir"=>$parametros["id_subir"],"nro_orden_cliente"=>$parametros["nro_orden_cliente"]))));
    }
    ?>
    <td align="center" class="bordes" <?=$completo?>><font color="blue"><b><?php echo $resultado->fields['nro_orden']; ?> <input type="button" name="boton" value="Ver" onclick="document.location='<?=$link?>'" style="cursor:hand">
	<input type="button" name="boton" value="Q" onclick="document.location='<? echo encode_link("../ordquem/ver_orden.php",Array("id"=>$resultado->fields['nro_orden'],"volver"=>encode_link("../ord_prod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$parametros["id"], "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$parametros["nro_orden"],"nro"=>$parametros["nro"],"id_subir"=>$parametros["id_subir"],"nro_orden_cliente"=>$parametros["nro_orden_cliente"])))); ?>'" style="cursor:hand"></td>
    <td align="center" class="bordes"><font color="blue"><b><?php echo
$resultado->fields['cantidad']; ?></b></font></td>

	<?if (($res_control_estado->fields['id_ensamblador']==4) || ($resultado_prov->fields['id_ensamblador']==4)){
    	echo "<td align='center' class='bordes'><font color='blue'><b>$estado_prod_bsas</b></font></td>";
	}?>


    </tr>
    <?
    $resultado->MoveNext();
     } //fin while
    }//fin if
    else //no encontre ninguna
     {
    ?>
    <tr>
    <td></td>
    <td></td>
    </tr>
    <?
     }
    ?>
    </table></td>
<!-- aca empieza lo que agrego de ordenes de compras -->
 <td>
<table>
<?
$sql2="select estado, fecha_entrega, nro_orden, razon_social from orden_de_compra join proveedor using(id_proveedor)
    	where id_licitacion=$id";
    $resultado2=sql($sql2, "<br>$sql2")or fin_pagina();//$db->Execute($sql2) or die ($db->ErrorMsg()."<br>".$sql2);
   //$resultado2->Move ;
   $cantidad_ordenes=$resultado2->RecordCount();
   if ($cantidad_ordenes >0){
   echo  "<tr>";
   $where="where ";
   $where.=" estado <> 'n' and (";
     for ($i=0;$i<$cantidad_ordenes;$i++){
     if ($i==$cantidad_ordenes-1)
        $where.=" orden_de_compra.nro_orden=".$resultado2->fields['nro_orden'].")";
     else
        $where.=" orden_de_compra.nro_orden=".$resultado2->fields['nro_orden']." or ";
     $resultado2->MoveNext();
       }
   $where.=" GROUP BY simbolo ";
   $sql="select simbolo,sum(cantidad * precio_unitario) as total";
   $sql.=" from compras.fila join compras.orden_de_compra using(nro_orden)";
   $sql.=" join licitaciones.moneda using (id_moneda) ".$where;
   $resultado=sql($sql, "<br>$sql")or fin_pagina();//$db->
   }
?>
<tr><td><b>Bs.As: U$S  <?=formato_money($bs->fields['monto'])?></b></td></tr>
<tr><td><b>Prod: U$S  <?=formato_money($monto_prod)?></b></td></tr>
<tr><td><b>OC: U$S  <?=formato_money($resultado->fields['total'])?></b></td></tr>
</table>

</td>
<!-- aca termina -->
<!-- aca empieza lo que agrego de facturas -->
<td>
<table>
<tr><td><b>Supuesto Licitacion:</b></td></tr>
<tr><td title="Este es el valor del dolar utilizado al momento de realizar la oferta"><? if ($resultado_temp->fields['valor_dolar_lic']!=0) { ?><b>Valor del dolar : $ <?=$resultado_temp->fields['valor_dolar_lic']?></b>
<? } else  ?> &nbsp;</td></tr>
<tr><td title="Esta es la ganancia promedio de los renglones adjudicados"><b>
 <?if($totales1!=0){?>
 Ganancia:<?=formato_money($totales1)?></b></td></tr>
 <input type="hidden" name="gp" value="<?=formato_money($totales1)?>">
 <?}
 else{
 ?>
 Nota:<font size="1">La Ganancia es cero verifique los datos</font></b>
 <?}
 ?>

</table>

</td>
 <td>
 <table>
 <tr><td title="Este es el resultado real de ganancia calculada con el dolar de hoy y los gastos reales realizados">
 <br><b>

 <font size="2"><?=formato_money($val)?> </font><br>
 Nota:<font size="0">Si el stock de Bs As es distinto de cero la ganancia presentada arriba no es la final</font></b>
 </td>

  </tr>

 </table>
</td>
<!-- aca termina -->

<? //traigo campos de la BD si es que existen
 ////////cambie en la consulta * por campos/////////////////////////////////
 $sql="select id_entrega_estimada,id_licitacion,fecha_estimada,comprado,responsable,id_ensamblador,observaciones from entrega_estimada where id_licitacion=$id and nro=$nro";
 $resultado_entrega=sql($sql) or fin_pagina();
 /*$sql="select * from entrega_estimada where id_licitacion=$id and nro=$nro";
 $resultado_entrega=sql($sql) or fin_pagina();*/
 if ($resultado_entrega->fields['id_licitacion']!="") //se inserto ensamblador
  $hay_ensamblador=1;
 else
  $hay_ensamblador=0;

?>
  <tr>
    <td><b>Ensamblador:</b><select name="ensamblador">
    <option value="0"></option>
    <?
     $sql="select id_ensamblador,nombre from ensamblador";
     $resultado=sql($sql, "<br>$sql")or fin_pagina();//$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
     if ($hay_ensamblador==1) //al haber ensamblador cargado vacio
       $resultado_prov->fields['id_ensamblador']="";
    while (!$resultado->EOF)
     {
    if(($resultado->fields['nombre']=="SIN PRODUCCION")||($resultado->fields['nombre']=="CORADIR BS. AS.")||($resultado->fields['nombre']=="PCPOWER"))
    {
    ?>
    <option value="<? echo $resultado->fields['id_ensamblador']; ?>" <? if(($resultado_entrega->fields['id_ensamblador']==$resultado->fields['id_ensamblador']) || ($resultado->fields['id_ensamblador']==$resultado_prov->fields['id_ensamblador'])) echo "selected"; ?>><? echo $resultado->fields['nombre']; ?></option>
    <?
    }
     $resultado->MoveNext();
     }
    ?>
    </select></td>
    <td colspan="3"><b>Fecha Estimada de entrega:</b>&nbsp
    <input type=text name=fecha_entrega size=10 maxlength=10 class="text_4" readonly value="<? echo Fecha($resultado_entrega->fields['fecha_estimada']); ?>" style="width:100"></td>
  </tr>
</table>
</TD>
</TR>
</TABLE><!-- tabla -->

<?
//////////cambie la consulta * por campos renglones_oc.*, renglon.*,///////////////////////////////////////
if ($parametros['id_subir']!="")
{
$q= "SELECT  nombre, nombrecomp, tamaño, tamañocomp
	FROM licitaciones.renglones_oc
		join licitaciones.renglon using(id_renglon)
		join licitaciones.archivos on (nombrecomp='Desc_lic_'||renglon.id_licitacion||'_renglon_'||replace(codigo_renglon, ' ', '_')||'.zip')
	where id_subir=".$parametros['id_subir']."
	order by codigo_renglon";
$reng=sql($q) or fin_pagina();
}
else $reng="";
?>
<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_4" src="<?=$img_cont?>" border=0 title="Mostrar Archivos" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.archivos,4);" >
  </td>
  <td align="center">
   <b>Archivos de Renglones</b>
  </td>
 </tr>
</table>
<table id="archivos" border="1" width="100%" style="display:none;border:thin groove" border=1 bordercolor=black cellpadding=0 cellspacing=1 rules="none">

<? if ($reng=="" || $reng->RecordCount()==0){
      	?>
      	 <tr><td align="center"><font color="Red"><b>Este Renglon no tiene Archivos Asociados.</b></font></td></tr>
      	<?
      }
    else {
	while (!$reng->EOF) {
		$file_name=substr($reng->fields["nombrecomp"], 0, stripos($reng->fields["nombrecomp"], ".zip"));
?>
		<tr>
			<td width="40%" align="right">
				<a target="_blank" title="<?= "Archivo: ".$reng->fields['nombrecomp']." \nTamaño: ".$reng->fields['tamañocomp']." bytes" ?>" href="<?=encode_link("seguimiento_orden.php",array("downloadfile"=>1,"zip"=>1,"filename"=>$file_name,"id_lic"=>$id,'filesize'=>$reng->fields['tamañocomp'])); ?>" ><img border=0 src="../../imagenes/zip.gif" /></a>&nbsp;</td><td valign="middle" ><a target="_blank" title="<?= "Archivo: $file_name.doc\nTamaño: ".$reng->fields['tamaño']." bytes" ?>" href="<?=encode_link("seguimiento_orden.php",array("downloadfile"=>1,"zip"=>0,"filename"=>$file_name,"id_lic"=>$id,'filesize'=>$reng->fields['tamaño'])); ?>" ><img border=0 src="../../imagenes/word.gif" />&nbsp;<?=$reng->fields['nombre']?></a>
			</td>
		</tr>
<?
		$reng->moveNext();
	}
	//////////////////////////////////////////////////////////////////////////////////////////
    }

?>
</table>

<?//////////////////////////////////////////quique/////////////////////////?>
<?
$id_ent = $parametros["id_entrega_estimada"];

$query="select finalizada from licitaciones.entrega_estimada where id_entrega_estimada=$id_ent";
$estado_finalizada=sql($query,"<br>Error al traer el estado del seguimiento<br>") or fin_pagina();
$seguimiento_finzalizado=$estado_finalizada->fields["finalizada"];

$ejecutar->fields["finalizada"];

if($seguimiento_finzalizado)
 $disabled_contacto="disabled";
else
 $disabled_contacto="";

$tabla=mostrar_contactos_segumientos($id_ent,$luga,1,$disabled_contacto,$fec_est);
echo $tabla;
?>

<?
$id_ent = $parametros["id_entrega_estimada"];
$tabla1=mostrar_contactos_segumientos1($id_ent,$disabled_contacto);
echo $tabla1;
$sql = "select comentario_prorroga.comentario,comentario_prorroga.fecha_comentario,comentario_prorroga.id_usuario,comentario_prorroga.id_prorroga from prorroga join comentario_prorroga using(id_prorroga) where id_entrega_estimada=$id_entrega_estimada";
$sql2 = "select comentarios_seguimientos.comentario,comentarios_seguimientos.fecha_comentario,
         comentarios_seguimientos.id_usuario,NULL as id_prorroga from entrega_estimada
         join subido_lic_oc using(id_entrega_estimada)
         join comentarios_seguimientos using(id_subir)
         where id_entrega_estimada=$id_ent";
$sql3 = "$sql UNION ALL $sql2 order by fecha_comentario asc";

//$comentarios=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

$comentarios=sql($sql3) or fin_pagina();
?>
<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_10" src="<?=$img_cont?>" border=0 title="Mostrar Comentarios" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.comentarios,10);" >
  </td>
  <td align="center">
   <b>Comentarios</b>
  </td>
 </tr>
</table>
<table id="comentarios" border="1" width="100%" style="display:none;border:thin groove" border=1 bordercolor=black cellpadding=0 cellspacing=1 rules="none">
<tr><td>
 <table align="center" width="100%">
    <?
    //generamos los comentarios ya cargados
    while(!$comentarios->EOF)
    {

	$long_desc=ceil(strlen($comentarios->fields["comentario"])/64);
	if($descripcion!="")
	 $cant_barra_n=substr_count("\n",$descripcion);
	else
	 $cant_barra_n=0;
	$rows=$cant_barra_n+$long_desc+1;

	$usuario = $comentarios->fields["id_usuario"];
     $sql = "select  (nombre || ' ' || apellido) as nombre from usuarios where id_usuario = $usuario";
     $result_usuario = sql($sql) or fin_pagina();
     $usuario = $result_usuario->fields['nombre'];

     if ($comentarios->fields['id_prorroga']=="")
      $modulo = "<font color='blue'><b>Cargado en Entregas</b></font>";
     else
      $modulo = "<font color='blue'><b>Cargado en Prorrogas</b></font>";

     if ($comentarios->fields['comentario']!="") { ?>
     <tr>
      <td width=20% valign=top>
       <table width="100%">
        <tr  id="ma_sf">
          <td width="65%" align="right">
          <b>
          <?
           $fecha=split(" ",$comentarios->fields['fecha_comentario']);
           echo fecha($fecha[0])." ".$fecha[1];
          ?>
          </b>
          </td>
         </tr>
         <tr id="ma_sf">
          <td align="right">
           <?="$usuario<br>$modulo";?>
          </td>
        </tr>
       </table>
      </td>
      <td>
       <textarea rows="<?= $rows?> " style="width:100%" readonly name="coment_<?=$comentarios->fields['id_comentarios_seguimientos']?>"><?=$comentarios->fields['comentario']?></textarea>
      </td>
     </tr>
     <?
     }
     $comentarios->MoveNext();
    }
    //y luego damos la opcion a guardar uno mas
    ?>
    <tr>
     <td colspan="2">
      <table>
       <tr>
        <td width="25%"  id="ma_sf">
         <b>Nuevo Comentario</b>
        </td>
        <td width="75%">
         &nbsp;<textarea rows="4" cols="70" name="nuevo_coment"></textarea>
        </td>
       </tr>
      </table>
     </td>
    </tr>

 <tr>
  <td align="center" colspan="2">
   <input type="submit" name="guardar_com" value="Guardar" onclick="return (control_entrega2());">
   </td>
   </tr>
   </table>
</td></tr>
</table>
<!--********************************FIN QUIQUE*****************************************-->
<!--**************Referencia-->
<table border=1 width='100%' cellpadding=0>
  <tr>
   <td colspan=10 >
    <b>Colores de referencia :</b>
   </td>
  </tr>
  <tr>
   <td width=25% >
    <table border=1 cellspacing=0 cellpadding=0 wdith=100%>
     <tr>
      <td width=15 bgcolor="" height=15>
       &nbsp
      </td>
      <td >
       Faltan entregar todos los productos.
      </td>
     </tr>
    </table>
   </td>
   <td width=25% >
    <table border=1 cellspacing=0 cellpadding=0 wdith=100%>
     <tr>
      <td width=15 bgcolor='yellow' height=15>
       &nbsp
      </td>
      <td >
       Faltan entregar algunos productos
      </td>
     </tr>
    </table>
   </td>
   <td width=25% >
    <table border=1 cellspacing=0 cellpadding=0 wdith=100%>
     <tr>
      <td width=15 bgcolor='#00C100' bordercolor='#000000' height=15>
       &nbsp
      </td>
      <td >
       Se entregaron todos los productos
      </td>
     </tr>
    </table>
   </td>
   <td width=25% >
    <table border=1 cellspacing=0 cellpadding=0 wdith=100%>
     <tr>
      <td width=15 bgcolor='#CC66FF' bordercolor='#000000' height=15>
       &nbsp
      </td>
      <td >
       N° de pedido no autorizado
      </td>
     </tr>
    </table>
   </td>
  </tr>
 </table>
<!--************************-->

<!--/////////////////////-->
<br>
<?$cantidad_entregados=array();
    $cantidad_productos=array();
    $ctrl_arre_entregados=0;

    $sql4="select tmp0.nro_orden,estado,fecha_entrega,razon_social, tmp0.renglones
from compras.orden_de_compra
	left join general.proveedor using(id_proveedor)
	left join(
		select fila.nro_orden, licitaciones.unir_texto(fila.nro_orden||'ç '||coalesce(desc_adic, '')||'ç '||fila.cantidad||'ç '||coalesce(recibidos, 0)||'ç '||coalesce(entregados, 0)||'ç '||coalesce(tiene, '')||'ç'||id_fila||'ç'||coalesce(descripcion_prod, '')||'¿ ') as renglones
		from compras.fila
			left join(
				select id_fila, sum(
					case when ent_rec=1 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as recibidos, sum(
					case when ent_rec=0 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as entregados
				from compras.recibido_entregado
				group by id_fila
			) r using(id_fila)
			left join(
				select case when tiene is null then 'f' else
							case when tiene is true then 't' else 'f' end
						end as tiene, id_fila
				from licitaciones.material_produccion2
				where id_entrega_estimada=$id_entrega_estimada
			)as tmp0 using(id_fila)
		where (es_agregado=0 or es_agregado is null)
		group by fila.nro_orden
		order by fila.nro_orden desc
	)as tmp0 using(nro_orden)
where id_entrega_estimada=$id_entrega_estimada and estado<>'n' order by razon_social, nro_orden";

    $resultado4=sql($sql4, "<br>$sql4")or fin_pagina();
    ?>

<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_6" src="<?=$img_ext?>" border=0 title="Ocultar Pedidos" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.pedido_materiales,6);" >
  </td>
  <td align="center">
   <b>Pedido de Materiales</b>
<input type=button name="pedidos material" value="Pedidos material" onclick="window.open('<?=encode_link("../mov_material/detalle_movimiento.php",array("id_licitacion"=>$id,
                                                         "id_entrega_estimada"=>$id_entrega_estimada,
                                                         "pedido_material"=>1,
                                                         "pagina_viene"=>"seguimiento_orden.php",
                                                         "deposito_origen"=>2))?>')">
  <input type="button" name="packaging" value="Packaging" onclick="window.open('<?=$link_pack;?>')" style="cursor:hand">
  </td>
 </tr>
</table>
<?
$ped_mat="select descripcion,detalle_movimiento.cantidad,id_licitacion,id_movimiento_material,id_detalle_movimiento,
		case when tmp0.cantidad is null then 0 else tmp0.cantidad end as pedido_cant,
		case when tmp1.cant_aut is null then 0 else tmp1.cant_aut end as cant_aut
	from mov_material.detalle_movimiento
		left join mov_material.movimiento_material using(id_movimiento_material)
		left join (
			select id_detalle_movimiento, SUM(cantidad)as cantidad
			from mov_material.recibidos_mov
			where ent_rec=0
			group by id_detalle_movimiento
		)as tmp0 using(id_detalle_movimiento)
		left join(
			select id_movimiento_material, count(id_log_movimiento) as cant_aut
			from mov_material.log_movimiento
			where tipo='autorización'
			group by id_movimiento_material
		)as tmp1 using (id_movimiento_material)
	where id_licitacion=$id
	and mov_material.movimiento_material.estado <> 3";

$pedido=sql($ped_mat,"No se pudo recuperar los pedidos de materiales") or fin_pagina();
?>
<?//****************************************************************?>
<table id="pedido_materiales" border="1" width="100%" style="display:inline;border:thin groove">
<?if ($pedido->RecordCount()==0)
 {
?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Pedido de materiales</b></font>
  </td>
 </tr>
 <?
 }
 else
 {
 ?>
<tr>
<td width="10%" align="center"><b>Cantidad</b></td>
<td width="60%" align="center"><b>Descripcion</b></td>
<td width="10%" align="center"><b>N° de Pedido</b></td>
</tr>
<?
while (!$pedido->EOF)
{
$nro=$pedido->fields['id_movimiento_material'];
$id_det=$pedido->fields['id_detalle_movimiento'];

$cant=$pedido->fields["pedido_cant"];
$cantidad1=$pedido->fields['cantidad'];
//$refer=encode_link("../mov_material/detalle_movimiento.php",Array("id"=>"$nro"));
	?>
   <tr>
   <?if($cant==$cantidad1)?>
    <td align="left" <?if($cant==$cantidad1){?> bgcolor="Green"<?}?>><b><?=$pedido->fields['cantidad']?> </b></b>
    </td>
    <td align="left"><b><?=$pedido->fields['descripcion'];?></b></b>
    </td>
    </td>
    <?
    //if ($log->RecordCount()!=0)
    if($pedido->fields["cant_aut"]!=0){
    ?>
    <td align="left" >
    <b><font color="Blue"><a href="<?=encode_link("../mov_material/detalle_movimiento.php",Array("id"=>"$nro"));?>" target="_blank"><?=$pedido->fields['id_movimiento_material']?></a></font></b>
    </td>
    <?
    }
    else
    {
    ?>
    <td align="left" bgcolor="#CC66FF">
    <b><font color="White"><a href="<?=encode_link("../mov_material/detalle_movimiento.php",Array("id"=>"$nro"));?>" target="_blank"><?=$pedido->fields['id_movimiento_material']?></a></font></b>
    </td>
    <?
    }
    ?>
   </tr>
 <?
$pedido->MoveNext();
}
}
?>
</table>



<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_1" src="<?=$img_ext?>" border=0 title="Ocultar Ordenes" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.este_seguimiento,1);" >
  </td>
  <td align="center">
   <b>Ordenes de Compras de este Seguimiento</b>
  </td>
 </tr>
</table>
<?//****************************************************************?>
<table id="este_seguimiento" border="1" width="100%" style="display:inline;border:thin groove">
<?if ($resultado4->RecordCount()==0)
 {
?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Ordenes para Mostrar</b></font>
  </td>
 </tr>
 <?
 }
 else
 {
 ?>
<tr>
<td width="20%" align="center"><b>Orden de Compra</b></td>
<td width="60%" align="center"><b>Productos</b></td>
<td width="20%" align="center"><b>Recibido en Ensamblador</b></td>
</tr>
<?
if ($resultado_entrega->RecordCount()<=0)
 $id_entrega=-10;
else
$id_entrega=$resultado_entrega->fields['id_entrega_estimada'];
//******************Aca controlo el color que le pongo para armar la lista mas abajo
$resultado4->Move(0);
$ctrl=0;
//while ($ctrl_arre_entregados!=0)
while (!$resultado4->EOF)
 {?>
   <tr bgcolor="<?=$bgcolor_out;?>">
    <td colspan="3">
     <!--Aca tengo que poner lo que saque-->
     <table width="100%" >
      <tr>
       <td align="left" width="5%" <? if($resultado4->fields['estado']=="p" || $resultado4->fields['estado']=="u" || $resultado4->fields['estado']=="a") echo "bgcolor='violet' title='OC en estado Pendiente o Para Autorizar o Autorizada'"?>>
        <b><FONT color="Blue"><a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado4->fields['nro_orden']));?>" target="_blank"><?=$resultado4->fields['nro_orden'];?></a></font></b>
       </td>
       <td align="left" width="40%">
        <b>Proveedor: <FONT color="Blue">  <?=$resultado4->fields['razon_social'];?></font></b>
       </td>
       <td align="left" width="25%">
        <b>Fecha Entrega: <FONT color="Blue"> <?=fecha($resultado4->fields['fecha_entrega']);?></font></b>
       </td>
       <td align="left" width="30%">
        <b>(F)=Cantidad que falta Entregar</b>
       </td>
      </tr>
     </table>
     <!--********************************-->
    </td>
   </tr>
   <?
   $tmp=explode("¿", substr($resultado4->fields["renglones"], 0, strlen($resultado4->fields["renglones"])-2));
   $resultado_recibidos=array();
  for ($i=0; $i<count($tmp); $i++){
   	$tmp2=explode("ç", $tmp[$i]);
   	$resultado_recibidos[$i]["nro_orden"]=$tmp2[0];
   	$resultado_recibidos[$i]["desc_adic"]=$tmp2[1];
   	$resultado_recibidos[$i]["cantidad_pedida"]=$tmp2[2];
   	$resultado_recibidos[$i]["recibidos"]=$tmp2[3];
   	$resultado_recibidos[$i]["entregados"]=$tmp2[4];
   	$resultado_recibidos[$i]["tiene"]=$tmp2[5];
   	$resultado_recibidos[$i]["id_fila"]=$tmp2[6];
   	$resultado_recibidos[$i]["descripcion_prod"]=$tmp2[7];
  }
  for ($i=0; $i<count($resultado_recibidos); $i++){
  	?>
   <tr>
    <?
    if (substr_count($resultado4->fields['razon_social'],"Stock")>0) $cantidad_usada=$resultado_recibidos[$i]['entregados'];
    else $cantidad_usada=$resultado_recibidos[$i]['recibidos'];
	 	$faltaran=($resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada);
     $color="";
     if ($faltaran<$resultado_recibidos[$i]['cantidad_pedida']) $color="yellow";
     if ($faltaran==0) $color="'#00C100'";
    ?>
    <td bgcolor="<?=$color?>"><b>Cant.:<font color="blue">&nbsp;<?=$resultado_recibidos[$i]['cantidad_pedida'];?>&nbsp</font> (F <font color="Blue"><?=$resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada?></font>)
    </td>

<td><b><?=$resultado_recibidos[$i]['descripcion_prod'];?>&nbsp;<?=$resultado_recibidos[$i]['desc_adic'];?></td>
    <td><input type="checkbox" name="check[]" value="<?=$resultado_recibidos[$i]['id_fila']; ?>" <? if ($resultado_recibidos[$i]['tiene']=='t') echo "checked"; ?>>
    <input type="hidden" name="material[<?=$resultado_recibidos[$i]['id_fila']; ?>]" value="<?=$resultado_recibidos[$i]['id_detalle_material']; ?>"></td>
   </tr>
   <?
  }
$resultado4->MoveNext();
}
 }
?>
</table>
<!--/////////////////////-->

<?$sql_seg="select distinct(id_entrega_estimada),id_licitacion from licitaciones.entrega_estimada
            where id_licitacion=$id";

  $resul_seg=sql($sql_seg,"No se pudo ejecutar la consulta ".$sql_seg) or fin_pagina();
  $id_entrega_estimada_resto="(id_entrega_estimada=";
  $control_on=1;
  while (!$resul_seg->EOF)
        {if ($resul_seg->fields['id_entrega_estimada']!=$id_entrega_estimada)
            {if ($control_on)
                {$id_entrega_estimada_resto.=$resul_seg->fields['id_entrega_estimada'];
                 $control_on=0;
                 $resul_seg->MoveNext();
                }
             else {$id_entrega_estimada_resto.=" or id_entrega_estimada=".$resul_seg->fields['id_entrega_estimada'];
                   $resul_seg->MoveNext();
                  }
            }
         else {$resul_seg->MoveNext();}
        }
  $id_entrega_estimada_resto.=")";

  ?>
<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_2" src="<?=$img_cont?>" border=0 title="Mostrar Ordenes" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.compras_extraordinarias,2);" >
  </td>
  <td align="center">
   <b>Ordenes de Compras Extraordinarias (No están Asociadas a ningún seguimiento de Producción)</b>
  </td>
 </tr>
</table>
 <?$cantidad_entregados=array();
    $cantidad_productos=array();
    $ctrl_arre_entregados=0;

    $sql4="select tmp0.nro_orden,estado,fecha_entrega,razon_social, tmp0.renglones
from compras.orden_de_compra
	left join general.proveedor using(id_proveedor)
	left join(
		select fila.nro_orden, licitaciones.unir_texto(fila.nro_orden||'ç '||coalesce(desc_adic, '')||'ç '||fila.cantidad||'ç '||coalesce(recibidos, 0)||'ç '||coalesce(entregados, 0)||'ç '||coalesce(tiene, '')||'ç'||id_fila||'ç'||coalesce(descripcion_prod, '')||'¿ ') as renglones
		from compras.fila
			left join(
				select id_fila, sum(
					case when ent_rec=1 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as recibidos, sum(
					case when ent_rec=0 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as entregados
				from compras.recibido_entregado
				group by id_fila
			) r using(id_fila)
			left join(
				select case when tiene is null then 'f' else
							case when tiene is true then 't' else 'f' end
						end as tiene, id_fila
				from licitaciones.material_produccion2
			)as tmp0 using(id_fila)
		where (es_agregado=0 or es_agregado is null)
		group by fila.nro_orden
		order by fila.nro_orden desc
	)as tmp0 using(nro_orden)
where id_licitacion=$id and id_entrega_estimada is null and estado<>'n' order by razon_social, nro_orden";

    $resultado4=sql($sql4, "<br>$sql4")or fin_pagina();//$db->Execute($sql4) or die ($db->ErrorMsg()."<br>".$sql4);

   ?>


<table id="compras_extraordinarias" border="1" width="100%" style="display:none;border:thin groove">
<?// if ($resultado4->RecordCount()==0 || $control_on==1)

if ($resultado4->RecordCount()==0 )
 {
 ?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Ordenes para Mostrar</b></font>
  </td>
 </tr>
 <?
 }
 else
 {
 ?>
<tr>
<td width="20%" align="center"><b>Orden de Compra</b></td>
<td width="60%" align="center"><b>Productos</b></td>
<td width="20%" align="center"><b>Recibido en Ensamblador</b></td>
</tr>
<?
if ($resultado_entrega->RecordCount()<=0)
 $id_entrega=-10;
else
$id_entrega=$resultado_entrega->fields['id_entrega_estimada'];
//******************Aca controlo el color que le pongo para armar la lista mas abajo
$resultado4->Move(0);
while (!$resultado4->EOF)
 {?>
   <tr bgcolor="<?=$bgcolor_out;?>">
    <td colspan="3">
     <!--Aca tengo que poner lo que saque-->
     <table width="100%" >
      <tr>
       <td align="left" width="5%" <? if($resultado4->fields['estado']=="p" || $resultado4->fields['estado']=="u" || $resultado4->fields['estado']=="a") echo "bgcolor='violet' title='OC en estado Pendiente o Para Autorizar o Autorizada'"?>>
        <b><FONT color="Blue"><a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado4->fields['nro_orden']));?>" target="_blank"><?=$resultado4->fields['nro_orden'];?></a></font></b>
       </td>
       <td align="left" width="40%">
        <b>Proveedor: <FONT color="Blue">  <?=$resultado4->fields['razon_social'];?></font></b>
       </td>
       <td align="left" width="25%">
        <b>Fecha Entrega: <FONT color="Blue"> <?=fecha($resultado4->fields['fecha_entrega']);?></font></b>
       </td>
       <td align="left" width="30%">
        <b>(F)=Cantidad que falta Entregar</b>
       </td>
      </tr>
     </table>
     <!--********************************-->
    </td>
   </tr>
   <?
   $tmp=explode("¿", substr($resultado4->fields["renglones"], 0, strlen($resultado4->fields["renglones"])-2));
   $resultado_recibidos=array();
  for ($i=0; $i<count($tmp); $i++){
   	$tmp2=explode("ç", $tmp[$i]);
   	$resultado_recibidos[$i]["nro_orden"]=$tmp2[0];
   	$resultado_recibidos[$i]["desc_adic"]=$tmp2[1];
   	$resultado_recibidos[$i]["cantidad_pedida"]=$tmp2[2];
   	$resultado_recibidos[$i]["recibidos"]=$tmp2[3];
   	$resultado_recibidos[$i]["entregados"]=$tmp2[4];
   	$resultado_recibidos[$i]["tiene"]=$tmp2[5];
   	$resultado_recibidos[$i]["id_fila"]=$tmp2[6];
   	$resultado_recibidos[$i]["descripcion_prod"]=$tmp2[7];
  }
  for ($i=0; $i<count($resultado_recibidos); $i++){
  	?>
   <tr>
    <?
    if (substr_count($resultado4->fields['razon_social'],"Stock")>0) $cantidad_usada=$resultado_recibidos[$i]['entregados'];
    else $cantidad_usada=$resultado_recibidos[$i]['recibidos'];
	 	$faltaran=($resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada);
     $color="";
     if ($faltaran<$resultado_recibidos[$i]['cantidad_pedida']) $color="yellow";
     if ($faltaran==0) $color="'#00C100'";
    ?>
    <td bgcolor="<?=$color?>"><b>Cant.:<font color="blue">&nbsp;<?=$resultado_recibidos[$i]['cantidad_pedida'];?>&nbsp</font> (F <font color="Blue"><?=$resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada?></font>)
    </td>

<td><b><?=$resultado_recibidos[$i]['descripcion_prod'];?>&nbsp;<?=$resultado_recibidos[$i]['desc_adic'];?></td>
    <td><input type="checkbox" name="check[]" value="<?=$resultado_recibidos[$i]['id_fila']; ?>" <? if ($resultado_recibidos[$i]['tiene']=='t') echo "checked"; ?>>
    <input type="hidden" name="material[<?=$resultado_recibidos[$i]['id_fila']; ?>]" value="<?=$resultado_recibidos[$i]['id_detalle_material']; ?>"></td>
   </tr>
   <?
  }
$resultado4->MoveNext();
}
 }
?>
</table>
<!--/////////////////////-->

<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_3" src="<?=$img_cont?>" border=0 title="Mostrar Ordenes" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.demas_ordenes,3);" >  </td>
  <td align="center">
   <b>Demás Ordenes de Compra (Ordenes de compra Asociadas a otros seguimientos de Producción)</b>
  </td>
 </tr>
</table>
<?$cantidad_entregados=array();
    $cantidad_productos=array();
    $ctrl_arre_entregados=0;
    $id_entrega_estimada_resto.=" or id_entrega_estimada=$id_entrega_estimada)"; //aca traigo las ordenes que estan con algun seguimiento, puede ser este mismo o cualquier otro
                                                                                 //de la misma licitacion
  $sql4="select tmp0.nro_orden,estado,fecha_entrega,razon_social, tmp0.renglones
from compras.orden_de_compra
	left join general.proveedor using(id_proveedor)
	left join(
		select fila.nro_orden, licitaciones.unir_texto(fila.nro_orden||'ç '||coalesce(desc_adic, '')||'ç '||fila.cantidad||'ç '||coalesce(recibidos, 0)||'ç '||coalesce(entregados, 0)||'ç '||coalesce(tiene, '')||'ç'||id_fila||'ç'||coalesce(descripcion_prod, '')||'¿ ') as renglones
		from compras.fila
			left join(
				select id_fila, sum(
					case when ent_rec=1 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as recibidos, sum(
					case when ent_rec=0 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as entregados
				from compras.recibido_entregado
				group by id_fila
			) r using(id_fila)
			left join(
				select case when tiene is null then 'f' else
							case when tiene is true then 't' else 'f' end
						end as tiene, id_fila
				from licitaciones.material_produccion2
				where id_entrega_estimada=$id_entrega_estimada
			)as tmp0 using(id_fila)
		where (es_agregado=0 or es_agregado is null)
		group by fila.nro_orden
		order by fila.nro_orden desc
	)as tmp0 using(nro_orden)
where id_licitacion=$id and id_entrega_estimada is not null and id_entrega_estimada<>$id_entrega_estimada and estado<>'n'
           order by razon_social, nro_orden";

    $resultado4=sql($sql4, "<br>$sql4<br>")or fin_pagina();
   ?>
<table id="demas_ordenes" border="1" width="100%" style="display:none;border:thin groove">
<? if ($resultado4->RecordCount()==0)
 {
 ?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Ordenes para Mostrar</b></font>
  </td>
 </tr>
<?
 }
 else
 {
 ?>
<tr>
<td width="20%" align="center"><b>Orden de Compra</b></td>
<td width="60%" align="center"><b>Productos</b></td>
<td width="20%" align="center"><b>Recibido en Ensamblador</b></td>
</tr>
<?
if ($resultado_entrega->RecordCount()<=0)
 $id_entrega=-10;
else
$id_entrega=$resultado_entrega->fields['id_entrega_estimada'];

//******************Aca controlo el color que le pongo para armar la lista mas abajo
$resultado4->Move(0);
while (!$resultado4->EOF)
 {?>
   <tr bgcolor="<?=$bgcolor_out;?>">
    <td colspan="3">
     <!--Aca tengo que poner lo que saque-->
     <table width="100%" >
      <tr>
       <td align="left" width="5%" <? if($resultado4->fields['estado']=="p" || $resultado4->fields['estado']=="u" || $resultado4->fields['estado']=="a") echo "bgcolor='violet' title='OC en estado Pendiente o Para Autorizar o Autorizada'"?>>
        <b><FONT color="Blue"><a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado4->fields['nro_orden']));?>" target="_blank"><?=$resultado4->fields['nro_orden'];?></a></font></b>
       </td>
       <td align="left" width="40%">
        <b>Proveedor: <FONT color="Blue">  <?=$resultado4->fields['razon_social'];?></font></b>
       </td>
       <td align="left" width="25%">
        <b>Fecha Entrega: <FONT color="Blue"> <?=fecha($resultado4->fields['fecha_entrega']);?></font></b>
       </td>
       <td align="left" width="30%">
        <b>(F)=Cantidad que falta Entregar</b>
       </td>
      </tr>
     </table>
     <!--********************************-->
    </td>
   </tr>
   <?
   $tmp=explode("¿", substr($resultado4->fields["renglones"], 0, strlen($resultado4->fields["renglones"])-2));
   $resultado_recibidos=array();
  for ($i=0; $i<count($tmp); $i++){
   	$tmp2=explode("ç", $tmp[$i]);
   	$resultado_recibidos[$i]["nro_orden"]=$tmp2[0];
   	$resultado_recibidos[$i]["desc_adic"]=$tmp2[1];
   	$resultado_recibidos[$i]["cantidad_pedida"]=$tmp2[2];
   	$resultado_recibidos[$i]["recibidos"]=$tmp2[3];
   	$resultado_recibidos[$i]["entregados"]=$tmp2[4];
   	$resultado_recibidos[$i]["tiene"]=$tmp2[5];
   	$resultado_recibidos[$i]["id_fila"]=$tmp2[6];
   	$resultado_recibidos[$i]["descripcion_prod"]=$tmp2[7];
  }
  for ($i=0; $i<count($resultado_recibidos); $i++){
  	?>
   <tr>
    <?
    if (substr_count($resultado4->fields['razon_social'],"Stock")>0) $cantidad_usada=$resultado_recibidos[$i]['entregados'];
    else $cantidad_usada=$resultado_recibidos[$i]['recibidos'];
	 	$faltaran=($resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada);
     $color="";
     if ($faltaran<$resultado_recibidos[$i]['cantidad_pedida']) $color="yellow";
     if ($faltaran==0) $color="'#00C100'";
    ?>
    <td bgcolor="<?=$color?>"><b>Cant.:<font color="blue">&nbsp;<?=$resultado_recibidos[$i]['cantidad_pedida'];?>&nbsp</font> (F <font color="Blue"><?=$resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada?></font>)
    </td>

<td><b><?=$resultado_recibidos[$i]['descripcion_prod'];?>&nbsp;<?=$resultado_recibidos[$i]['desc_adic'];?></td>
    <td><input type="checkbox" name="check[]" value="<?=$resultado_recibidos[$i]['id_fila']; ?>" <? if ($resultado_recibidos[$i]['tiene']=='t') echo "checked"; ?>>
    <input type="hidden" name="material[<?=$resultado_recibidos[$i]['id_fila']; ?>]" value="<?=$resultado_recibidos[$i]['id_detalle_material']; ?>"></td>
   </tr>
   <?
  }
$resultado4->MoveNext();
}
 }

 //,orden_de_compra.nro_orden
$sql4="select ordenes.orden_de_produccion.nro_orden, ordenes.orden_de_produccion.id_licitacion, compras.orden_de_compra.fecha_entrega, general.proveedor.razon_social, renglones
from compras.orden_de_compra
	left join general.proveedor using(id_proveedor)
	left join ordenes.orden_de_produccion on ordenes.orden_de_produccion.nro_orden=orden_prod
	left join(
		select nro_orden, licitaciones.unir_texto(fila.nro_orden||'ç '||coalesce(desc_adic, '')||'ç '||fila.cantidad||'ç '||coalesce(recibidos, 0)||'ç '||coalesce(entregados, 0)||'ç '||coalesce(tiene, '')||'ç'||id_fila||'ç'||coalesce(descripcion_prod, '')||'¿ ') as renglones
		from compras.fila
			left join(
				select id_fila, sum(
					case when ent_rec=1 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as recibidos, sum(
					case when ent_rec=0 then
						case when cantidad is null then 0 else cantidad end
					else 0 end
				)as entregados
				from compras.recibido_entregado
				group by id_fila
			) r using(id_fila)
			left join(
				select case when tiene is null then 'f' else
							case when tiene is true then 't' else 'f' end
						end as tiene, id_fila
				from licitaciones.material_produccion2
			)as tmp0 using(id_fila)
		where (es_agregado=0 or es_agregado is null)
		group by nro_orden
	)as tmp0 on(tmp0.nro_orden=orden_de_produccion.nro_orden)
where ordenes.orden_de_produccion.id_licitacion=$id";

    $resultado4=sql($sql4, "<br>$sql4")or fin_pagina();//$db->Execute($sql4) or die ($db->ErrorMsg()."<br>".$sql4);
?>
</table>
<table border=1 width='100%' cellpadding=0>
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_5" src="<?=$img_cont?>" border=0 title="Mostrar Ordenes" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.compras_rma,5);" >
  </td>
  <td align="center">
   <b>Compras de RMA</b>
  </td>
 </tr>
</table>

<table id="compras_rma" border="1" width="100%" style="display:none;border:thin groove">
<?
if ($resultado4->RecordCount()==0 )
 {
 ?>
 <tr>
  <td align="center">
   <font size="3" color="Red"><b>No hay Ordenes para Mostrar</b></font>
  </td>
 </tr>
 <?
 }else{
 ?>
<tr>
<td width="20%" align="center"><b>Orden de Compra</b></td>
<td width="60%" align="center"><b>Productos</b></td>
<td width="20%" align="center"><b>Recibido en Ensamblador</b></td>
</tr>
<?
if ($resultado_entrega->RecordCount()<=0)
 $id_entrega=-10;
else
$id_entrega=$resultado_entrega->fields['id_entrega_estimada'];
//******************Aca controlo el color que le pongo para armar la lista mas abajo
$resultado4->Move(0);
while (!$resultado4->EOF)
 {?>
   <tr bgcolor="<?=$bgcolor_out;?>">
    <td colspan="3">
     <table width="100%" >
      <tr>
       <td align="left" width="5%" <? if($resultado4->fields['estado']=="p" || $resultado4->fields['estado']=="u" || $resultado4->fields['estado']=="a") echo "bgcolor='violet' title='OC en estado Pendiente o Para Autorizar o Autorizada'"?>>
        <b><FONT color="Blue"><a href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado4->fields['nro_orden']));?>" target="_blank"><?=$resultado4->fields['nro_orden'];?></a></font></b>
       </td>
       <td align="left" width="40%">
        <b>Proveedor: <FONT color="Blue">  <?=$resultado4->fields['razon_social'];?></font></b>
       </td>
       <td align="left" width="25%">
        <b>Fecha Entrega: <FONT color="Blue"> <?=fecha($resultado4->fields['fecha_entrega']);?></font></b>
       </td>
       <td align="left" width="30%">
        <b>(F)=Cantidad que falta Entregar</b>
       </td>
      </tr>
     </table>
    </td>
   </tr>
   <?
   $tmp=explode("¿", substr($resultado4->fields["renglones"], 0, strlen($resultado4->fields["renglones"])-2));
   $resultado_recibidos=array();
  for ($i=0; $i<count($tmp); $i++){
   	$tmp2=explode("ç", $tmp[$i]);
   	$resultado_recibidos[$i]["nro_orden"]=$tmp2[0];
   	$resultado_recibidos[$i]["desc_adic"]=$tmp2[1];
   	$resultado_recibidos[$i]["cantidad_pedida"]=$tmp2[2];
   	$resultado_recibidos[$i]["recibidos"]=$tmp2[3];
   	$resultado_recibidos[$i]["entregados"]=$tmp2[4];
   	$resultado_recibidos[$i]["tiene"]=$tmp2[5];
   	$resultado_recibidos[$i]["id_fila"]=$tmp2[6];
   	$resultado_recibidos[$i]["descripcion_prod"]=$tmp2[7];
  }
  for ($i=0; $i<count($resultado_recibidos); $i++){
  	?>
   <tr>
    <?
    if (substr_count($resultado4->fields['razon_social'],"Stock")>0) $cantidad_usada=$resultado_recibidos[$i]['entregados'];
    else $cantidad_usada=$resultado_recibidos[$i]['recibidos'];
	 	$faltaran=($resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada);
     $color="";
     if ($faltaran<$resultado_recibidos[$i]['cantidad_pedida']) $color="yellow";
     if ($faltaran==0) $color="'#00C100'";
    ?>
    <td bgcolor="<?=$color?>"><b>Cant.:<font color="blue">&nbsp;<?=$resultado_recibidos[$i]['cantidad_pedida'];?>&nbsp</font> (F <font color="Blue"><?=$resultado_recibidos[$i]['cantidad_pedida']-$cantidad_usada?></font>)
    </td>

<td><b><?=$resultado_recibidos[$i]['descripcion_prod'];?>&nbsp;<?=$resultado_recibidos[$i]['desc_adic'];?></td>
    <td><input type="checkbox" name="check[]" value="<?=$resultado_recibidos[$i]['id_fila']; ?>" <? if ($resultado_recibidos[$i]['tiene']=='t') echo "checked"; ?>>
    <input type="hidden" name="material[<?=$resultado_recibidos[$i]['id_fila']; ?>]" value="<?=$resultado_recibidos[$i]['id_detalle_material']; ?>"></td>
   </tr>
   <?
  }
$resultado4->MoveNext();
}
 }
?>
</table
<br>


<table width="90%"  border="1">
  <tr>
    <td><b>Compras:</b></td>
    <td><select name="comprado">
        <option value="0"></option>
        <option value="1" <?=($resultado_entrega->fields['comprado']==1)?"selected":"";?>>Falta Comprar</option>
        <option value="2" <?=($resultado_entrega->fields['comprado']==2)?"selected":"";?>>Todo Comprado</option>
        </select>
    </td>
  </tr>
  <tr>
    <td valign="top"><b>Responsable:</b></td>
    <td><select name="responsable">
        <option value="">Seleccione el responsable</option>
		<?
			$sql_resp = "SELECT phpss_account.username,nombre,apellido FROM usuarios LEFT JOIN phpss_account ON usuarios.login=phpss_account.username WHERE phpss_account.active='true' ORDER BY nombre,apellido";
			$result_resp = sql($sql_resp) or fin_pagina();
			while (!$result_resp->EOF) {
				echo "<option value='".$result_resp->fields["nombre"]." ".$result_resp->fields["apellido"]."'";
				if ($resultado_entrega->fields["responsable"] == $result_resp->fields["nombre"]." ".$result_resp->fields["apellido"]) {
					echo " selected";
				}
				echo ">".$result_resp->fields["nombre"]." ".$result_resp->fields["apellido"]."</option>";
				$result_resp->MoveNext();
			}
		?>
        </select>
    </td>
  </tr>
</table>
<br>
<b>Observaciones:</b><br><textarea name="observaciones" cols="100" rows="5" onkeypress="cargar='no';" onblur="cargar='si';"><? echo $resultado_entrega->fields['observaciones']; ?></textarea><br><br>
<?
$consulta="SELECT licitacion.id_licitacion,entrega_estimada.nro,prioridad,comentarios_lic,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado,vence_oc,fecha_subido,
entidad.nombre,sistema.usuarios.iniciales,lider,ini_compra,ini_compra1,id_subir,fin_compra,ini_entrega,fin_entrega,ini_cdr,fin_cdr,guardado,entidad.id_distrito,entrega_estimada.finalizada
FROM licitaciones.licitacion
LEFT JOIN licitaciones.entidad USING (id_entidad)
left JOIN licitaciones.entrega_estimada USING (id_licitacion)
LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider
JOIN licitaciones.subido_lic_oc USING (id_entrega_estimada) left join (select ini_compra1,compra1.id_subir,nro_orden from (select min(fecha) as ini_compra1,id_subir from compras.orden_de_compra group by (id_subir))as compra1 join compras.orden_de_compra on compra1.id_subir=orden_de_compra.id_subir and compra1.ini_compra1=orden_de_compra.fecha)as compras2 using(id_subir)
where entrega_estimada.finalizada=0 AND borrada='f' and licitacion.id_licitacion=$id and guardado=1";

$ejecutar=sql($consulta,"no se pudo recuperar los datos de las licitaciones") or fin_pagina();

	 $has=$ejecutar->fields['vence_oc'];
	 $des=$ejecutar->fields['fecha_subido'];

 	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 $des=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1-4,$anio_1));
	 list($anio,$mes,$dia)=explode("-",$has);
	 $has=date("Y-m-d",mktime(0,0,0,$mes,$dia+4,$anio));
	 $des=Fecha($des);
	 $has=Fecha($has);
	 $dia_actu=date("Y-m-d",mktime());
	 $contador=diferencia_dias($des,$has);
	 $cont=diferencia_dias($des,$has);
	 $Fecha_Desde=$des;
	 $Fecha_Hasta=$has;
	 $diferencia1=diferencia_dias($Fecha_Desde, $Fecha_Hasta);

	 $des=Fecha_db($des);
	 $has=Fecha_db($has);
	 list($anio_1,$mes_1,$dia_1)=explode("-",$des);
	 list($anio,$mes,$dia)=explode("-",$has);
	 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ant1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia1=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1,$anio_1));
	 $dia_ult=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));
	 $ejecutar->movefirst();
$guardado_base=$ejecutar->fields['guardado'];
$inic=$ejecutar->fields['ini_compra'];
if(($guardado_base==1)&&($inic!=""))
{
?>

<table id="tabla_p" name="tabla_p" border="1" width="80" style="font-size:12px">
<tr>

	 <td align="center" bgcolor="<?=$bgcolor_out?>"><b>Lic/Cli</b></td>
	 <td bgcolor="<?=$bgcolor_out?>"><b>Lid</b></td>
	 <?
	 $fondo="#cccccc";
	 $ar_mes[1]="Enero";
	 $ar_mes[2]="Febrero";
	 $ar_mes[3]="Marzo";
	 $ar_mes[4]="Abril";
	 $ar_mes[5]="Mayo";
	 $ar_mes[6]="Junio";
	 $ar_mes[7]="Julio";
	 $ar_mes[8]="Agosto";
	 $ar_mes[9]="Septiembre";
	 $ar_mes[10]="Octubre";
	 $ar_mes[11]="Noviembre";
	 $ar_mes[12]="Diciembre";
	 while($contador>=0)
	{
		 list($anio,$mes,$dia)=explode("-",$dia_ant);
		 $dia_letras=date("D",mktime(00,0,0,$mes,$dia,$anio));
		 $mes_letras=date("n",mktime(0,0,0,$mes,$dia,$anio));
		 $fe_com=date("r",mktime(0,0,0,$mes,$dia,$anio));
		 $dia_feriado=Fecha($dia_ant);
		 if(($dia==01)&&($fondo=="#cccccc"))
		 {
	     $fondo='#FFFFFF';
		 }
		 else
		 {
		 if(($dia==01)&&($fondo=="#FFFFFF"))
		 {
	     $fondo="#cccccc";
		 }
		 }
		 if($dia==01)
		 {
		 if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
		 {
			 ?>
			 <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>" width="2"><font color="Red"><b><?echo "$dia/$mes";?></b></font></td>
			 <?
		 }
		 else
		 {
		  if($dia_actu==$dia_ant)
		 {
			 ?>
			 <td bgcolor="Blue" class="actual" title="<?=$fe_com?>"><font color="Red"><?echo "$dia/$mes";?></font></td>
			 <?
		 }
		 else
		 {

			 ?>
			  <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>"><b><?echo "$dia/$mes";?></b></td>
			 <?
		 }
		 }
		 }
		 else
		 {
          if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
		 {
			 ?>
			 <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>"><font color="Red"><?echo $dia;?></font></td>
			 <?
		 }
		 else
		 {
		  if($dia_actu==$dia_ant)
		 {
			 ?>
			 <td bgcolor="Blue" class="actual" title="<?=$fe_com?>"><b><?echo $dia;?></b></td>
			 <?
		 }
		 else
		 {
			 ?>
			  <td bgcolor="<?=$fondo?>" title="<?=$fe_com?>" width="4"><b><?echo $dia;?></b></td>
			 <?
		 }
		 }
		 }
		 $dia_ant=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	     $contador--;

     }
    ?>
</tr>
<?
$cantidad_lic=$ejecutar->RecordCount();
$i=1;
$int1=0;
while(!$ejecutar->EOF)
{   $co=1;

	$int1++;
    $j=$ejecutar->RecordCount();
	$fin_compra=$ejecutar->fields['fin_compra'];
    $ini_compras=$ejecutar->fields['ini_compra'];
    $lic_com=$ejecutar->fields['comentarios_lic'];
    $fin_cdr=$ejecutar->fields['fin_cdr'];
    $ini_cdr=$ejecutar->fields['ini_cdr'];
    $fin_entrega=$ejecutar->fields['fin_entrega'];
    $ini_entrega=$ejecutar->fields['ini_entrega'];
    $vence_oc=$ejecutar->fields['vence_oc'];
    $id_subir=$ejecutar->fields['id_subir'];
    $id_distrito=$ejecutar->fields['id_distrito'];
    $id_sub=$ejecutar->fields['id_subir'];
    $guardado_base=$ejecutar->fields['guardado'];
    $priorid=$ejecutar->fields['prioridad'];
    $recup="select fecha,color from fecha_color where id_subir=$id_sub";
    $color_dia=sql($recup,"no se pudo recuperar el color del dia")or fin_pagina();
    $sumador=0;
    while(!$color_dia->EOF)
	{
		$fec_col[$sumador]=$color_dia->fields['fecha'];
		$col_d[$sumador]=$color_dia->fields['color'];
		$sumador++;
		$color_dia->MoveNext();
	}
    if($guardado_base==1)
    {
    $fin_entrega=Fecha_db(Fecha($fin_entrega));
    $ini_compra=Fecha_db(Fecha($ini_compras));
    $ini_entrega=Fecha_db(Fecha($ini_entrega));
    $fin_cdr=Fecha_db(Fecha($fin_cdr));
    $ini_cdr=Fecha_db(Fecha($ini_cdr));
    $fin_compra=Fecha_db(Fecha($fin_compra));
    }
    else
    {
    $fin_entrega=$vence_oc;
    $ini_compra1=Fecha($ejecutar->fields['ini_compra1']);
    if($ini_compra1!="")
    {
    	$ini_compra=Fecha_db($ini_compra1);
    }
    else
    {
    	$ini_compra=Fecha_db(Fecha($ejecutar->fields["fecha_subido"]));
    }

    //un dia de entrega para id_distrito=12 => Prov Bs As
    //2 dias de entregas en otro distrito
    $fin_entrega1=Fecha($fin_entrega);
    if ($id_distrito==12)
        $ini_entrega=Fecha_db(dias_habiles_anteriores($fin_entrega1,1)); //resta 1 dias
    else
       $ini_entrega=Fecha_db(dias_habiles_anteriores($fin_entrega1,2)); //resta 2 dia


    //$fin_entrega=Fecha_db($fin_entrega);
	//fin_cdr

     	list($anio,$mes,$dia)=explode("-",$ini_entrega);
     	$fin_cdr=date("Y-m-d",mktime(0,0,0,$mes,$dia,$anio));

//ini_cdr

 	if ($ini_cdr == "" || $ini_cdr == NULL) {
      	$sql_cdr="select sum (renglones_oc.cantidad) as cantidad from licitaciones.renglones_oc
                  join licitaciones.renglon using (id_renglon) where id_subir=$id_subir and
                  (tipo='Computadora Enterprise' or tipo='Computadora Matrix')";
        $res_cdr=sql($sql_cdr) or fin_pagina();
        if ($res_cdr->fields['cantidad'] !=NULL && $res_cdr->fields['cantidad']!="")
        {
        	$cantidad=$res_cdr->fields['cantidad'];
            $sql_cant="select cant_dias from licitaciones.dias_armado_cdr where $cantidad between lim_inf and lim_sup";
            $res_cant=sql($sql_cant,"no se pudo recuperar las cantidades") or fin_pagina();
            $dias=$res_cant->fields['cant_dias'];

            $fin_cdr1=Fecha($fin_cdr);

            $id_l=$ejecutar->fields["id_licitacion"];
            $ini_cdr=Fecha_db(dias_habiles_anteriores($fin_cdr1,$dias));
            list($anio,$mes,$dia)=explode("-",$ini_cdr);
     	    $ini_cdr=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));

        }
        else
        {
        	$ini_cdr=$ini_entrega;
        }
      }



	$fin_compra=$ini_cdr;
	list($anio,$mes,$dia)=explode("-",$fin_compra);
    $fin_compra=date("Y-m-d",mktime(0,0,0,$mes,$dia-1,$anio));


    }

    if($ordenar_listado==1)
    {

    	$update_p="update licitaciones.subido_lic_oc set prioridad=$i where id_subir=$id_sub";
		$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();
    }
    else
    {
    if($priorid==0)
    {
	    $update_p="update licitaciones.subido_lic_oc set prioridad=$i where id_subir=$id_sub";
		$update_p1=sql($update_p,"No se pudo actualizar la prioridad1")or fin_pagina();
    }
    }
    $vence=$ejecutar->fields["vence_oc"];
	$comien=$ejecutar->fields["fecha_subido"];
	$id=$ejecutar->fields['id_licitacion'];
	$nombre_e=cortar($ejecutar->fields["nombre"],10);
    $nombre_cli=$ejecutar->fields["nombre"];
	?>
	<tr>
	<input type="hidden" name="id_<?=$i?>" value="<?=$id_sub?>">

	<input type="hidden" name="idlic_<?=$i?>" value="<?=$id?>">



	<td rowspan="2" align="center"  title="<?echo $ejecutar->fields["id_licitacion"]." ".$nombre_cli?>"><b> 	N° <?=$ejecutar->fields["id_licitacion"];?><br><?=$nombre_e;?></A>
	</b>

	</td>
	<?
	if($ejecutar->fields["iniciales"]=="")
	{
		?>
		<td rowspan="2" align="center" title="<?=$ejecutar->fields["iniciales"]?>"><b>Sin Lider</b></td>
		<?
	}
	else
	{
		?>
		<td rowspan="2" align="center" title="<?=$ejecutar->fields["iniciales"]?>"><b> <?=$ejecutar->fields["iniciales"];?>
</b></td>
		<?
	}
	?>


  <?
while(compara_fechas($dia_ult,$dia1)!=-1)
{	list($anio,$mes,$dia)=explode("-",$dia1);
	$dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	$dia_feriado=Fecha($dia1);
	if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	{
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		?>
		<td bgcolor="#FFCC00" class="feriado" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		else
		{
		?>
		<td class="feriado" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		}
		else
		{
		?>
		<td class="feriado" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
	}

	else
	{

		if($dia_actu==$dia1)
	    {
		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		?>
		<td bgcolor="#FFCC00" class="actual" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		else
		{
		?>
		<td class="actual" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		}
		else
		{
		?>
		<td class="actual" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
	    }

	else
	{

		if(compara_fechas($dia1,$comien)!=-1)//dia1 mayor que comien
		{
		if(compara_fechas($vence,$dia1)!=-1)	//vence mayor que dia1
		{
		?>
		<td bgcolor="#FFCC00" title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		else
		{
		?>
		<td title="<?=$lic_com?>">&nbsp;</td>
		<?
		}
		}
		else
		{
		?>
		<td title="<?=$lic_com?>">&nbsp;</td>
		<?
		}

	}
   }
	list($anio,$mes,$dia)=explode("-",$dia1);
	$dia1=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));

}
?>
</tr>
<tr>
<?
while(compara_fechas($dia_ult,$dia2)!=-1)
{
	$control=0;
    list($anio,$mes,$dia)=explode("-",$dia2);
	$dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	$dia_feriado=Fecha($dia2);
	$dia_che=date("Y_m_d",mktime(0,0,0,$mes,$dia,$anio));
    $anterior=date("Y_m_d",mktime(0,0,0,$mes,$dia-1,$anio));
	$posterior=date("Y_m_d",mktime(0,0,0,$mes,$dia+1,$anio));
	$comparar=$dia2;
	$control1=0;
	$conta=0;
	$sumador1=$sumador;
	$si_dia=0;
	while($conta<$sumador1)
	{
		if(compara_fechas($fec_col[$conta],$comparar)==0)
		{
		$si_dia=1;
		$col_f=$conta;
		$conta=$sumador1;
		}
		$conta++;
	}
	if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	{
		if($si_dia==1)
		{
		?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="<?=$col_d[$col_f]?>"  class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="<?=$col_d[$col_f]?>">
			</td>
			<?
		}
	else
	{
		if ($ini_compra !=NULL ||$ini_compra !="")
		{
		    if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>"  class="feriado"  title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
			$control1=1;
			}
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{

			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			?>
			<td id="td_<?$i.'_'.$dia_che?>"  class="feriado"  title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
			$control1=1;
			}
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{
			$control=1;
			$colores="#FF00FF";
			?>
			<td id=td_"<?$i.'_'.$dia_che?>"  class="feriado"  title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
			}
			}
		}
		if($control==0)
		{$colores="transparent";
		?>
		<td id="td_<?$i.'_'.$dia_che?>" class="feriado" onClick="alternar_color(this,'#FFFFFF','#00C823','#FF00FF','#00FFFF','<?=$i?>','<?=$dia_che?>')" title="<?=$lic_com?>">&nbsp;
		<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
		</td>
		<?
		}
	}
	}

	else
	{

		if($dia_actu==$dia2)
	      {
		if ($ini_compra !=NULL ||$ini_compra !="")
		{
		    if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="#00C823" class="actual"  title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00C823">
			</td>
			<?
			$control1=1;
			}
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{

			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			?>
			<td id="td_<?$i.'_'.$dia_che?>" bgcolor="#00FFFF" class="actual" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00FFFF">
			</td>
			<?
			$control1=1;
			}
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{
			$control=1;
			$colores="#FF00FF";
			?>
			<td id=td_"<?$i.'_'.$dia_che?>" bgcolor="#FF00FF" class="actual" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#FF00FF">
			</td>
			<?
			}
			}
		 }
		if($control==0)
		{$colores="transparent";
		?>
		<td id="td_<?$i.'_'.$dia_che?>" class="actual" title="<?=$lic_com?>">&nbsp;
		<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
		</td>
		<?
		}
	    }

	    else
	    {

		if ($ini_compra !=NULL ||$ini_compra !="")
		{
			if(compara_fechas($dia2,$ini_compra)!=-1)
			{
			if(compara_fechas($fin_compra,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
		  	 $colores="#00C823";
			?>
			<td id="td_<?=$i.'_'.$dia_che?>" bgcolor="#00C823" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00C823">
			</td>
			<?
			$control1=1;
			}
			}
		}
		if ($ini_cdr !=NULL ||$ini_cdr !="")
		{
			if((compara_fechas($dia2,$ini_cdr)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_cdr,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			 $colores="#00FFFF";
			?>
			<td id="td_<?$i.'_'.$dia_che?>" bgcolor="#00FFFF" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#00FFFF">
			</td>
			<?
			$control1=1;
			}
			}
		}
		if ($ini_entrega !=NULL ||$ini_entrega !="")
		{
			if((compara_fechas($dia2,$ini_entrega)!=-1)&&(!$control1))
			{
			if(compara_fechas($fin_entrega,$dia2)!=-1)	//vence mayor que dia1
			{$control=1;
			$colores="#FF00FF";
			?>
			<td id=td_"<?$i.'_'.$dia_che?>" bgcolor="#FF00FF" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="#FF00FF">
			</td>
			<?
			}
			}
		}
		if($control==0)
		{
			$colores="transparent";
			?>
			<td id="td_<?$i.'_'.$dia_che?>" title="<?=$lic_com?>">&nbsp;
			<input type="hidden" name="d_<?=$i.'_'.$dia_che?>" value="">
			</td>
			<?
		}
	}
   }
	list($anio,$mes,$dia)=explode("-",$dia2);
	$dia2=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	}

	?>
	</tr>
	<tr>

	<td colspan="2" class="separador" title="<?=$lic_com?>">
	<textarea id="co" class="achicar" name="comentario_<?=$i?>" title="<?=$lic_com?>" rows="1" readonly><? echo "$lic_com";?>
	</textarea>
	</td>

	<?

	while(compara_fechas($dia_ult,$dia3)!=-1)
	{
		list($anio,$mes,$dia)=explode("-",$dia3);
	    $dia_letras=date("D",mktime(0,0,0,$mes,$dia,$anio));
	    $dia_feriado=Fecha($dia3);
	    $can_come=0;
	    if(($dia_letras=='Sun')||(feriado($dia_feriado))||($dia_letras=='Sat'))
	     {
		?>
		<td  class="feriado" align="center"" title="<?=$lic_com?>">&nbsp;&nbsp;</td>
		<?
	     }
	     else
	     {
	     if($dia_actu==$dia3)
	      {
	      	?>
	     	<td class="actual" align="center"" title="<?=$lic_com?>">&nbsp;&nbsp;</td>
			<?
	      }
	      else
	      {
			 ?>
			 <td class="separador" align="center"" title="<?=$lic_com?>">&nbsp;&nbsp;</td>
			 <?
	     }
	     }
		 $dia3=date("Y-m-d",mktime(0,0,0,$mes,$dia+1,$anio));
	}
	$ejecutar->MoveNext();
}
	?>
</tr>

</table>
<?
}
?>

<table width="95%" align="center">
<tr>



<td>
<? if (permisos_check('inicio','boton_finalizar_seguimiento'))
    $permiso_fin="";
   else $permiso_fin=" disabled ";
if ($_ses_user['login'] == 'juanmanuel' || $_ses_user['login'] == 'marcos' || $_ses_user['login'] == 'quique')
{
?>

    <input type="submit" name="boton" value="Finalizar Seguimiento" <?=$permiso_fin?>>
<?
}
?>
<td align="right"><input  type="submit" name="boton" value="Guardar"></td>
<?if ($parametros["pagina"]=="control_seguimiento") {?>
   <td><input type="button" name="cerrar" value="Cerrar"  onclick="window.close()">
   <?}
   else {
   ?>
        <td><input type="submit" name="boton" value="Volver"></td>
   <?}?>     
<td colspan="2" align="center"><input type="button" name="ordenes_asoc" style='width:200;' value='Ordenes de compra asociadas' onClick="window.open('<?=encode_link("../ord_compra/ord_compra_listar.php",array("filtro"=>"todas","keyword"=>$id,"filter"=>"o.id_licitacion","volver_lic"=>$id))?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand">
</td>
</tr>
</table>
 <!--
 <input type="hidden" name="nro" value="<?=$nro ?>">
 -->
</center>
</body>
</form>
</html>
<?
 }//fin default
}//fin switch
?>