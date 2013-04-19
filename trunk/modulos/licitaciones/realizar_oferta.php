<?php
/*
$Author: nazabal $
$Revision: 1.297 $
$Date: 2005/04/14 14:31:56 $
*/

include("../../config.php");
require_once("funciones.php");
//obtengo el nro de la licitacion
$nro_licitacion=$parametros["licitacion"] or $nro_licitacion=$parametros["ID"];
//echo "cantidad de filas".$_POST["cantidad_renglones"];

if($_ses_global_lic_o_pres=="pres")
 $pagina_padre="../presupuestos/presupuestos_view.php";
else
 $pagina_padre="licitaciones_view.php";


switch ($_POST['boton'])
{
 case "Ver Descripciones":  $link=encode_link("vista_previa.php", array("licitacion" =>$nro_licitacion,"id_renglon"=>$_POST['radio_renglon'],"modificacion" => 1));
                             header("Location:$link");
                             break;
 case "Guardar": //guardo los datos del nuevo renglon
                 //es un quilombo;
                 require('guardar_renglon.php');
				 break;

 case "Modificar Renglon":
                  $esta_modificando=1;
                  $link=encode_link("modificar_lic.php",array("licitacion"=>$nro_licitacion,"renglon"=>$_POST['radio_renglon']));
                  require_once("modificar_lic.php");
                  die();
                  break;

 case "Eliminar Renglon": echo kill_reng($_POST['radio_renglon']);
                          break;
 case "Actualizar Dolar y Ganancia":
					  $db->StartTrans();
					  $valor_dolar=$_POST['valor_dolar'];
					  $sql="update licitacion set valor_dolar_lic = $valor_dolar where id_licitacion = $nro_licitacion" ;
					  $db->execute($sql) or die($sql."<br>".$db->errorMsg());
					  //actualizo las ganancias
					  $cantidad=$_POST["cantidad_renglones"] ;
					  $i=0;
					  //actualizo los valores
					  while ($i<$cantidad) {
					  $ganancia=$_POST["ganancia$i"];
					  if (($ganancia > 0) && ($ganancia <=1)) {
									  $id_renglon = $_POST["renglon$i"];
									  $sql="update renglon set ganancia = $ganancia where id_renglon = $id_renglon" ;
									  $db->execute($sql) or die ($sql);

									  }
									 // else echo "error con la ganancia";
					 $i++;
					  }
					$db->CompleteTrans();
					break;
 case "Actualizar  Ganancias":
					  $db->StartTrans();
					  //actualizo las ganancias
					  $cantidad=$_POST["cantidad_renglones"] ;
					  $i=0;
					  //actualizo los valores
					  while ($i<$cantidad) {
					  $ganancia=$_POST["ganancia$i"];
					  if (($ganancia > 0) && ($ganancia <=1)) {
									  $id_renglon = $_POST["renglon$i"];
									  $sql="update renglon set ganancia = $ganancia where id_renglon = $id_renglon" ;
									  $db->execute($sql) or die ($sql);

									  }
									 // else echo "error con la ganancia";
						$i++;
						}
						 $db->CompleteTrans();

							  break;
 case "Oferta":
                $link=encode_link("renglon_alternativa.php",array("licitacion"=>$nro_licitacion));
		header("location: $link");
			   break;
 case "Terminar":/*$link=encode_link("enviar_mensajes.php",array("licitacion"=>$nro_licitacion,"pagina"=>"realizar_oferta.php"));
				 header("location: $link");*/
				 $query_monto="SELECT licitacion.monto_ofertado FROM licitacion WHERE id_licitacion=$nro_licitacion";
				 $resultado_monto=$db->execute($query_monto) or die($query_monto);
				 $numero_ofertas=$resultado_monto->RecordCount();
				 if($numero_ofertas>0) $aux=$resultado_monto->fields['monto_ofertado'];
				 if(($numero_ofertas==0)||($aux==0)){
				   $monto_ofertado=$_POST['monto_ofertado'];
				   //echo "Monto ofertado a cargar en la BD: ".$monto_ofertado."<br>";
				   $actualizar_monto="UPDATE licitacion SET monto_ofertado=$monto_ofertado WHERE id_licitacion=$nro_licitacion";
				   $db->execute($actualizar_monto) or die ($db->ErrorMsg().$actualizar_monto."<br>");
				  }

				 $query_control="SELECT * from oferta_licitacion WHERE id_licitacion=$nro_licitacion";
				 $control=$db->Execute($query_control) or die($db->ErrorMsg().$query_control);
				 $cantidad_ofertas=$control->RecordCount();
				 if($cantidad_ofertas>0){
				   $nombre_excel=genera_cotizacion_licitacion($nro_licitacion);
				   //genera el excel para el cd de oferta
				   $nombre_excel_cd=genera_cotizacion_licitacion_cd($nro_licitacion);

				   //insertamos en entregar_lic el nombre del archivo  e indicamos
				   //que es la oferta de esta licitacion, y que esta lista para imprimir
				   $query_arch="update entregar_lic set oferta_subida=1, archivo_oferta='$nombre_excel' where id_licitacion=$nro_licitacion";
				   $db->Execute($query_arch) or die($db->ErrorMsg()."query nombre de oferta");

				   $link_fin = encode_link("$pagina_padre",array("cmd1"=>"detalle","ID"=>$nro_licitacion));
				   echo "<html><head><script language=javascript>";
                   echo "window.opener.document.location.href='$link_fin';window.opener.focus();window.close();";
                   echo "</script></head></html>";
				   break;
                 }
                 else {
                  $informar="<font color='red'><b>Error: No esta armada la oferta</b></font>";
                  echo "<script>alert ('Error: No esta definida la combinacion de renglones para armar la oferta')</script>";
                  break;
                 }


 case "Cancelar":$link=encode_link("realizar_oferta.php", array("licitacion" =>$nro_licitacion));
                header("location: $link");
                break;

 case "Avisar Descripcion" :$sql="select usuario_avisar from mail_aviso where usuario_avisa='$_ses_user_login' and tipo=0";
							$resultado=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                            $para=$resultado->fields['usuario_avisar'];
                            $sql="select entidad.nombre from licitacion join entidad on licitacion.id_licitacion=$nro_licitacion and licitacion.id_entidad=entidad.id_entidad";
							$resultado=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                            $entidad=$resultado->fields['nombre'];
                            $mailtext=$_POST['contenido'];
                            if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="Presupuesto";
                            else 
                             $asunto_title="Licitación"; 
                            $asunto="Descripciones listas - $asunto_title Nº $nro_licitacion - Entidad: $entidad ";
                            $mail_header="";
                            $mail_header .= "MIME-Version: 1.0";
                            $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
                            $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
                            $mail_header .="\nTo: $para";
                            $mail_header .= "\nContent-Type: text/plain";
                            $mail_header .= "\nContent-Transfer-Encoding: 8bit";
                            $mail_header .= "\n\n" . $mailtext."\n";
									 $mail_header .= "\n\n" . firma_coradir()."\n"; 
                            mail("",$asunto,"",$mail_header);
                            
                            if ($_ses_user_login=="juanmanuel")
                            {$mail_header="";
                             $mail_header .= "MIME-Version: 1.0";
                             $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
                             $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
                             $mail_header .="\nTo: juanmanuel@coradir.com.ar";
                             $mail_header .= "\nContent-Type: text/plain";
                             $mail_header .= "\nContent-Transfer-Encoding: 8bit";
                             $mail_header .= "\n\n" . $mailtext."\n";
 									  $mail_header .= "\n\n" . firma_coradir()."\n"; 
                             mail("",$asunto,"",$mail_header);
                            }
                            break;

 case "Avisar Oferta":$sql="select usuario_avisar from mail_aviso where usuario_avisa='$_ses_user_login' and tipo=1";
					  $resultado=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                      $para=$resultado->fields['usuario_avisar'];
                      $sql="select entidad.nombre from licitacion join entidad on licitacion.id_licitacion=$nro_licitacion and licitacion.id_entidad=entidad.id_entidad";
					  $resultado=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                      $entidad=$resultado->fields['nombre'];
                      $mailtext=$_POST['contenido'];
                      if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="Presupuesto";
                      else 
                             $asunto_title="Licitación"; 
                      $asunto="Oferta lista - $asunto_title Nº $nro_licitacion - Entidad: $entidad ";
                      $mail_header="";
                      $mail_header .= "MIME-Version: 1.0";
                      $mail_header .= "\nFrom: Sistema Inteligente CORADIR <>";
                      $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
                      $mail_header .="\nTo: $para";
                      $mail_header .= "\nContent-Type: text/plain";
                      $mail_header .= "\nContent-Transfer-Encoding: 8bit";
                      $mail_header .= "\n\n" . $mailtext."\n";
						    $mail_header .= "\n\n" . firma_coradir()."\n"; 
                      mail("",$asunto,"",$mail_header);
                      if ($_ses_user_login=="juanmanuel")
                            {$mail_header="";
                             $mail_header .= "MIME-Version: 1.0";
                             $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
                              $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
                             $mail_header .="\nTo: juanmanuel@coradir.com.ar";
                             $mail_header .= "\nContent-Type: text/plain";
                             $mail_header .= "\nContent-Transfer-Encoding: 8bit";
                             $mail_header .= "\n\n" . $mailtext."\n";
									  $mail_header .= "\n\n" . firma_coradir()."\n";
                             mail("",$asunto,"",$mail_header);
                            }
                      break;
}

if ($_POST['boton_duplicar'] == "Duplicar Renglon") {
	require_once("duplicar_renglon.php");
    
}

  $sql="select licitacion.*,entidad.*,tipo_entidad.nombre as tipo_entidad,
        candado.estado as candado,distrito.nombre as nbre_dist
        from (licitacion join entidad on licitacion.id_entidad = entidad.id_entidad and id_licitacion = $nro_licitacion)
        join candado using (id_licitacion)
        join tipo_entidad using(id_tipo_entidad)
        join distrito using(id_distrito)";
  $resultado_licitacion=$db->execute($sql) or die ($sql);


if($_POST['poner_check']=="Poner Check")
{//agregamos el check a la licitacion
 $query="update licitacion set check_lic=1 where id_licitacion=$nro_licitacion";
 $db->Execute($query) or die ($db->ErrorMsg());
 $link=encode_link("realizar_oferta.php", array("licitacion" =>$nro_licitacion));
 header("location: $link");
}
elseif($_POST['sacar_check']=="Sacar Check")
{//quitamos el check a la licitacion
 $query="update licitacion set check_lic=0 where id_licitacion=$nro_licitacion";
 $db->Execute($query) or die ($db->ErrorMsg());
 $link=encode_link("realizar_oferta.php", array("licitacion" =>$nro_licitacion));
 header("location: $link");

}

if ($_POST['producto']!="") $_POST['boton']="Agregar Renglon";

$link=encode_link("realizar_oferta.php", array("licitacion" => $nro_licitacion));
//comentatrio
?>
<HTML>
<HEAD>
<script languaje="javascript">

function cuerpo()
{var valor;
 valor=prompt("Ingrese el texto a enviar en el mail","");
 if (!valor)
  return false;
 document.all.contenido.value=valor;
 return true;
}

//funciones de cargar firmante
var ventana_firmante="";
function cargar_firmante()
{
document.all.firmante_text.value=ventana_firmante.document.all.select_firmante.options[ventana_firmante.document.all.select_firmante.options.selectedIndex].text;
document.all.id_activo.value=ventana_firmante.document.all.select_firmante.options[ventana_firmante.document.all.select_firmante.options.selectedIndex].value;
}
</script>
<?php
include("../ayuda/ayudas.php");
?>
<meta Name="generator" content="PHPEd Version 3.2 (Build 3220 )   ">
<title>Renglones</title>
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
 $estilos_select="style='width:100%;height:50px;'";
 ?>
<style type="text/css">";
</style>
<link rel="SHORTCUT ICON"  href="/path-to-ico-file/logo.ico">
<script languaje="javascript">

<?
//si el candado esta puesto, la funcion habilita_botones()
//no debe habilitar ningun boton salvo el de Ver Descripciones
if($resultado_licitacion->fields['candado']==0)
{echo"
function habilita_botones(){
document.all.boton[1].disabled=0;
document.all.boton[2].disabled=0;
document.all.boton[3].disabled=0;
document.all.boton[4].disabled=0;
document.all.boton[5].disabled=0;
document.all.boton[6].disabled=0;
document.all.boton[7].disabled=0;
document.all.boton[8].disabled=0;
document.all.boton_duplicar.disabled=0;
}";
}
else
{echo "
function habilita_botones(){
document.all.boton[7].disabled=0;
}";
}
?>
function chequea_radio(indice)
{if (document.all.radio_renglon.length>1)
  document.all.radio_renglon[indice].checked="true";
 else
  document.all.radio_renglon.checked="true";
 habilita_botones();
}


function eliminar(valor)
{
var objeto;
objeto=eval("window.document.all.tip"+valor);
objeto.value="";
objeto=eval("window.document.all.tipo"+valor);
objeto.value="";
objeto=eval("window.document.all.descripcion"+valor);
objeto.value="";
objeto=eval("window.document.all.precio"+valor);
objeto.value="";
objeto=eval("window.document.all.cantidad"+valor);
objeto.value="";
/*
//tbl.getElementsByTagName("TR").length;
if (window.document.all.cant_ad.value==1)
  window.document.all.productos_ad.style.visibility='hidden';
 window.document.all.productos_ad.deleteRow(valor);
 window.document.all.cant_ad.value--;
 */
}

//ventana de productos
var wproductos=false;


//funcion que recupera los datos de la ventana hijo y los setea en el padre
function agregar(valor) {
var objeto;
objeto2=eval("document.all.estado"+valor);
objeto3=eval("document.all.producto"+valor);
objeto4=eval("document.all.tipo"+valor);
if (objeto2.value==0) 
 {objeto3.value=objeto4.value;
  objeto2.value=3; //debo eliminar un producto e insertar otro
 }
if (objeto2.value==1)
  objeto2.value=3; //debo eliminar un producto e insertar otro
if (objeto2.value==4) //no habia nada
 objeto2.value=2; //debo insertar un producto


objeto=eval("document.all.tip"+valor);
objeto.value=wproductos.document.forms[0].tipo_prod.value;

//esta variable contiene el id_producto
objeto=eval("document.all.tipo"+valor);
objeto.value=wproductos.document.forms[0].id_producto.value;
objeto=eval("document.all.descripcion"+valor);
objeto.value=wproductos.document.forms[0].descripcion.value;
objeto=eval("document.all.precio"+valor);
objeto.value=wproductos.document.forms[0].precio.value;
objeto=eval("document.all.cantidad"+valor);
objeto.value=1;
window.focus();
wproductos.close();
}

function switch_func(valor,link)
{var objeto;
 objeto=eval("window.document.all.boton"+valor);
 if (objeto.value=="agregar")
 {
  wproductos=window.open(link,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=40,top=100,width=700,height=400,resizable=1');
  objeto.value="eliminar";
 }
 else
 {eliminar(valor);
  objeto.value="agregar";
 }
}



<?php
$sql="select * from productos where tipo='micro' order by desc_gral";
$resultados=$db->execute($sql) or die($query);
while (!$resultados->EOF)
 { $sql="select * from (((compatibilidades join productos on productos.id_producto=motherboard) join precios on precios.id_producto=productos.id_producto) join proveedor on proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones') where componente=".$resultados->fields["id_producto"]. " order by desc_gral";
 $resultado_comp=$db->execute($sql) or die($sql);
?>
var micro_<?php echo $resultados->fields["id_producto"]; ?>=new Array(<?php echo $resultado_comp->RecordCount(); ?>);
<?php
$i=0;
while (!$resultado_comp->EOF)
 {?>
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>]=new Array(6);
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][0]=<?php echo $resultado_comp->fields['id_producto']; ?>;
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][1]="<?php echo $resultado_comp->fields['tipo']; ?>";
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][2]="<?php echo $resultado_comp->fields['marca']; ?>";
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][3]="<?php echo $resultado_comp->fields['modelo']; ?>";
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][4]=<?php echo $resultado_comp->fields['precio']; ?>;
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][5]="<?php echo $resultado_comp->fields['desc_gral']; ?>";
<?php
$i++;
$resultado_comp->MoveNext();
 }
$resultados->MoveNext();
 }
?>

function chequear_dolar()
{

if (window.document.all.valor_dolar.value==""){
  alert('Debe ingesar un valor para el Dolar');
  return false;
  }
  else
	   return true;
}

function verificar_precios()
{
 if (typeof(document.all.select_etap)!='undefined' && document.all.select_etap.selectedIndex==0)
 {
  alert('Falta llenar Norma ETAP');
  return false;
 }

 switch(document.all.producto.options[document.all.producto.selectedIndex].text){

 case 'Impresora':
  if (window.document.all.renglon.value=="")
     {
       alert("Falta llenar el campo de renglon");
	  return false;
     }
  if (window.document.all.titulo.value=="")
     {
     alert("Falta llenar el campo de titulo");
	 return false;
     }

  if ((window.document.all.ganancia_renglon.value=="") || (window.document.all.ganancia_renglon.value<=0) || (window.document.all.ganancia_renglon.value > 1) )
     {
     alert("Dato invalido en el campo ganancia");
	 return false;
     }
  if (window.document.all.cantidad_renglon=="")
  {
  alert("Falta Ingresar la Cantidad del Renglon");
  return false;
  }
  if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value!=0) && (document.all.precio_impresora.value==""))
 {alert("Falta Precio en Impresora");
  return false;
 }
 document.all.precio_impresora.value=document.all.precio_impresora.value.replace(',','.');
 if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value==0) && (document.all.precio_impresora.value!=""))
 {alert("Falta elegir Impresora");
  return false;
 }
 if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value!=0) &&(document.all.cantidad_impresora.value=="")) {
  alert ("Falta Cantidad en Impresora");
  return false;
  }
 return true;
 break;
 case 'Otro':
     if (window.document.all.renglon.value=="")
    {
    alert("Falta llenar el campo de renglon");
    return false;
   }
  if (window.document.all.titulo.value=="")
   {
    alert("Falta llenar el campo de titulo");
    return false;
    }
  if ((window.document.all.ganancia_renglon.value=="") || (window.document.all.ganancia_renglon.value<=0) || (window.document.all.ganancia_renglon.value > 1) )
   {
   alert("Dato inválido en el campo ganancia");
   return false;
    }


 break;
 case 'Software':
  if (window.document.all.renglon.value=="") {
    alert("Falta llenar el campo de renglon");
    return false;
   }
  if (window.document.all.titulo.value==""){
    alert("Falta llenar el campo de titulo");
    return false;
    }
  if ((window.document.all.ganancia_renglon.value=="") || (window.document.all.ganancia_renglon.value<=0) || (window.document.all.ganancia_renglon.value > 1) ){
   alert("Dato inválido en el campo ganancia");
   return false;
    }
 break;
//entra por computadora CDR o Enterprise

default:
//es que es matrix o enterprise
if (window.document.all.cantidad_renglon=="")
  {
  alert("Falta Ingresar la Cantidad del Renglon");
  return false;
  }


 if (window.document.all.renglon.value=="")
 {alert("Falta llenar el campo de renglon");
  return false;
 }
 if (window.document.all.titulo.value=="")
 {alert("Falta llenar el campo de titulo");
  return false;
 }
if ((window.document.all.ganancia_renglon.value=="") || (window.document.all.ganancia_renglon.value<=0) || (window.document.all.ganancia_renglon.value > 1) )
   {
   alert("Dato invalido en el campo ganancia");
   return false;
	}


 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value!=0) && (document.all.precio_kit.value==""))
 {alert("Falta Precio en Kit");
  return false;
 }
 document.all.precio_kit.value=document.all.precio_kit.value.replace(',','.');
 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value==0) && (document.all.precio_kit.value!=""))
 {alert("Falta elegir Kit");
  return false;
 }
 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value!=0) &&(document.all.cantidad_kit.value=="")) {
  alert ("Falta Cantidad en Kit");
  return false;
  }
 if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value!=0) && (document.all.precio_madre.value==""))
 {alert("Falta Precio en Placa Madre");
  return false;
 }
 document.all.precio_madre.value=document.all.precio_madre.value.replace(',','.');
 if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value==0) && (document.all.precio_madre.value!=""))
 {alert("Falta elegir Placa Madre");
  return false;
 }
  if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value!=0) &&(document.all.cantidad_madre.value=="")) {
  alert ("Falta Cantidad en Madre");
  return false;
  }

 if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value!=0) && (document.all.precio_micro.value==""))
 {alert("Falta Precio en Micro");
  return false;
 }
 document.all.precio_micro.value=document.all.precio_micro.value.replace(',','.');
 if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value==0) && (document.all.precio_micro.value!=""))
 {alert("Falta elegir Micro");
  return false;
 }
  if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value!=0) &&(document.all.cantidad_micro.value=="")) {
  alert ("Falta Cantidad en Micro");
  return false;
  }

 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value!=0) && (document.all.precio_memoria.value==""))
 {alert("Falta Precio en Memoria");
  return false;
 }
 document.all.precio_memoria.value=document.all.precio_memoria.value.replace(',','.');
 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value==0) && (document.all.precio_memoria.value!=""))
 {alert("Falta elegir Memoria");
  return false;
 }
 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value!=0) &&(document.all.cantidad_memoria.value=="")) {
  alert ("Falta Cantidad en Memoria");
  return false;
  }

 if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value!=0) && (document.all.precio_disco.value==""))
 {alert("Falta Precio en Disco");
  return false;
 }
 document.all.precio_disco.value=document.all.precio_disco.value.replace(',','.');
 if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value==0) && (document.all.precio_disco.value!=""))
 {alert("Falta elegir Disco");
  return false;
 }
  if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value!=0) &&(document.all.cantidad_disco.value=="")) {
  alert ("Falta Cantidad en Disco");
  return false;
  }

 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value!=0) && (document.all.precio_cd.value==""))
 {alert("Falta Precio en CD-Rom");
  return false;
 }
 document.all.precio_cd.value=document.all.precio_cd.value.replace(',','.');
 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value==0) && (document.all.precio_cd.value!=""))
 {alert("Falta elegir CD-Rom");
  return false;
 }
  if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value!=0) &&(document.all.cantidad_cd.value=="")) {
  alert ("Falta Cantidad en Cdrom");
  return false;
  }

 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value!=0) && (document.all.precio_monitor.value==""))
 {alert("Falta Precio en Monitor");
  return false;
 }
 document.all.precio_monitor.value=document.all.precio_monitor.value.replace(',','.');
 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value==0) && (document.all.precio_monitor.value!=""))
 {alert("Falta elegir Monitor");
  return false;
 }
 if (document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value==0)
  {
  alert("Debe elegir Monitor");
  return false;
   }

 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value!=0) &&(document.all.cantidad_monitor.value=="")) {
  alert ("Falta Cantidad en Monitor");
  return false;
  }

//nuevo
  if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value!=0) && (document.all.precio_sistemaoperativo.value==""))
 {alert("Falta Precio en Sistema Operativo");
  return false;
 }
 document.all.precio_sistemaoperativo.value=document.all.precio_sistemaoperativo.value.replace(',','.');
 if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value==0) && (document.all.precio_sistemaoperativo.value!=""))
 {alert("Falta elegir Sistema Operativo");
  return false;
 }
  if (document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value==0)
  {alert("Debe elegir Sistema Operativo");
  return false;
   }


 if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value!=0) &&(document.all.cantidad_sistemaoperativo.value=="")) {
  alert ("Falta Cantidad en Sistema Operativo");
  return false;
  }
//fin de nuevo

 if ((document.all.select_video.options[document.all.select_video.selectedIndex].value!=0) && (document.all.precio_video.value==""))
 {alert("Falta Precio en Video");
  return false;
 }
 document.all.precio_video.value=document.all.precio_video.value.replace(',','.');
 if ((document.all.select_video.options[document.all.select_video.selectedIndex].value==0) && (document.all.precio_video.value!=""))
 {alert("Falta elegir Placa de Video");
  return false;
 }
  if ((document.all.select_video.options[document.all.select_video.selectedIndex].value!=0) &&(document.all.cantidad_video.value=="")) {
  alert ("Falta Cantidad en Video");
  return false;
  }

 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value!=0) && (document.all.precio_grabadora.value==""))
 {alert("Falta Precio en grabadora");
  return false;
 }
 document.all.precio_grabadora.value=document.all.precio_grabadora.value.replace(',','.');
 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value==0) && (document.all.precio_grabadora.value!=""))
 {alert("Falta elegir Placa de Video");
  return false;
 }
  if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value!=0) &&(document.all.cantidad_grabadora.value=="")) {
  alert ("Falta Cantidad en Grabadora");
  return false;
  }

 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value!=0) && (document.all.precio_dvd.value==""))
 {alert("Falta Precio en DVD");
  return false;
 }
 document.all.precio_dvd.value=document.all.precio_dvd.value.replace(',','.');
 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value==0) && (document.all.precio_dvd.value!=""))
 {alert("Falta elegir DVD");
  return false;
 }
  if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value!=0) &&(document.all.cantidad_dvd.value=="")) {
  alert ("Falta Cantidad en DVD");
  return false;
  }

 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value!=0) && (document.all.precio_red.value==""))
 {alert("Falta Precio en Placa de Red");
  return false;
 }
 document.all.precio_red.value=document.all.precio_red.value.replace(',','.');
 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value==0) && (document.all.precio_red.value!=""))
 {alert("Falta elegir Placa de Red");
  return false;
 }
  if ((document.all.select_red.options[document.all.select_red.selectedIndex].value!=0) &&(document.all.cantidad_red.value=="")) {
  alert ("Falta Cantidad en Placa de Red");
  return false;
  }


 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value!=0) && (document.all.precio_modem.value==""))
 {alert("Falta Precio en Modem");
  return false;
 }
 document.all.precio_modem.value=document.all.precio_modem.value.replace(',','.');
 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value==0) && (document.all.precio_modem.value!=""))
 {alert("Falta elegir Modem");
  return false;
 }
  if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value!=0) &&(document.all.cantidad_modem.value=="")) {
  alert ("Falta Cantidad en Modem");
  return false;
  }

 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value!=0) && (document.all.precio_zip.value==""))
 {alert("Falta Precio en Zip");
  return false;
 }
 document.all.precio_zip.value=document.all.precio_zip.value.replace(',','.');
 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value==0) && (document.all.precio_zip.value!=""))
 {alert("Falta elegir ZIP");
  return false;
 }
  if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value!=0) &&(document.all.cantidad_zip.value=="")) {
  alert ("Falta Cantidad en Zip");
  return false;
  }

 break;
 }//del switch
 <? for($i=1;$i<=15;$i++){
?>

 if (window.document.all.tipo<?=$i?>.value!="") //entonces hay cargado un tipo

 {
  if (window.document.all.cantidad<?=$i?>.value=="")
  {alert("Falta llenar la cantidad de "+window.document.all.tip<?=$i?>.value);
   return false;
  }
  if (window.document.all.precio<?=$i?>.value=="")
  {alert("Falta llenar el precio de "+window.document.all.tip<?=$i?>.value);
   return false;
  }

 }//del primer if

 <?
  } //del for
 ?>
 return true;

}


function incluir(objeto,texto,value,id) //incluye valor en el select objeto
{
objeto.length++;
objeto.options[objeto.length-1].text=texto;
objeto.options[objeto.length-1].value=value;
objeto.options[objeto.length-1].id=id;
}

function limpiar_select()
{

// window.document.all.select_monitor.options.length=0;
// window.document.all.select_disco.options.length=0;
// window.document.all.select_memoria.options.length=0;
// window.document.all.select_modem.options.length=0;
// window.document.all.select_dvd.options.length=0;
// window.document.all.select_cd.options.length=0;
// window.document.all.select_video.options.length=0;
window.document.all.select_madre.options.length=0;
window.document.all.precio_madre.value="";
incluir(window.document.all.select_madre,"Seleccione Placa Madre",0,0);
 //window.document.all.select_grabadora.options.length=0;
// window.document.all.select_red.options.length=0;
// window.document.all.select_zip.options.length=0;
// window.document.all.select_kit.options.length=0;

}

function cambiar_comp(valor)
{var arreglo;
 var i=0;

switch (valor)
 {
<?php
$resultados->Move(0);
while (!$resultados->EOF)
 {?>
 case '<?php echo $resultados->fields['id_producto']; ?>':arreglo=micro_<?php echo $resultados->fields["id_producto"]; ?>;break;
 <?php
 $resultados->MoveNext();
 }
 ?>
 }// fin switch

if (typeof(arreglo)!="undefined")
{while (i<arreglo.length)
 {switch (arreglo[i][1])
  {
   case "placa madre":incluir(window.document.all.select_madre,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;

   //case "monitor":incluir(window.document.all.select_monitor,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "disco rigido":incluir(window.document.all.select_disco,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "memoria":incluir(window.document.all.select_memoria,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "modem":incluir(window.document.all.select_modem,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "dvd":incluir(window.document.all.select_dvd,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "cdrom":incluir(window.document.all.select_cd,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "video":incluir(window.document.all.select_video,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
 // case "micro":incluir(window.document.all.select_micro,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "grabadora":incluir(window.document.all.select_grabadora,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "lan":incluir(window.document.all.select_red,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;

  }//fin switch
  i++;
  }//fin de while
}//fin if
}//fin funcion cambiar_comp

function llamada_funciones(valor){
limpiar_select();
cambiar_comp(valor);
} //fin de llamada_funciones

</script>
</HEAD>
<BODY BGCOLOR='<? echo $bgcolor3;   ?>'>
<FORM  action="<? echo $link; ?>" name="form1" method="POST">
<INPUT TYPE="HIDDEN"  name="accion_tomar">

<?
echo "<font size='3'><center>";
echo $informar;
echo "</center></font>";
if($_POST['boton']=="Terminar") echo "<br>";
?>

<table align="center" border=0 width="100%">
<tr >
<td colspan="5" align="center" width=85% id="mo">
<font color="#E0E0E0">
<?if($_ses_global_lic_o_pres=="pres")
    $datos_de="del Presupuesto";
  else 
    $datos_de="de la Licitación"; 
?>
<b>Datos <?=$datos_de?></td>
<td width=2% align=center>
<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_realizar_of.htm" ?>', 'REALIZAR OFERTA')" >
</td>

</tr>
 <tr >
   <td width="20%">
   <b>
   <?if($_ses_global_lic_o_pres=="pres")
      $datos_de="Presupuesto";
     else 
      $datos_de="Licitación"; 

   echo $datos_de;
   ?>
   <font color="#FF0000">
   <? echo $nro_licitacion;
      if($resultado_licitacion->fields['candado']!=0)
      { if($_ses_global_lic_o_pres=="pres")
          $datos_de="e presupuesto";
        else 
          $datos_de="a Licitación"; 
       echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Est$datos_de solo puede verse, pero no modificarse'>";
       $candado="disabled";
      }
      else
       $candado="";
   ?>
   </td>
   <td width="15%">
   <b>
   Entidad
   </td>
   <td width="40%">
   <font color="#FF0000">
   <b>
    <? echo $resultado_licitacion->fields['nombre'];  ?>
   </font>
   <?$nbre_dist=$resultado_licitacion->fields['nbre_dist'];?>
   </td>
   <td>

   <?if (permisos_check("inicio","poner_check_lic"))
                  $visibility="visible";
                else
                  $visibility="hidden";
     if($resultado_licitacion->fields['check_lic']==0)
     {?>
      <input type="submit" name="poner_check" style="visibility:<?=$visibility?>" value="Poner Check">
     <?
     }
     else
     {?>
      <input type="submit" name="sacar_check" style="visibility:<?=$visibility?>" value="Sacar Check">
	 <?
     }
     ?>
       </td>
 </tr>
</table>

<table align="center" width=100%>

<? if ($resultado_licitacion->fields['id_moneda']==1)  { ?>
 <tr>
   <td align="left">
   </td>
   <td>
   </td>
   <td align="left">
   <input type="submit" name="boton" value="Avisar Descripcion" style='width:130;' <?=$candado?> onclick="return cuerpo();">
   <input type="hidden" name="contenido" value="">
   </td>
   <td align="left">
   <input type="submit" name="boton" value="Avisar Oferta" style='width:130;' <?=$candado?> onclick="return cuerpo();">
   </td>
   </tr>
   <tr>
   <td>
   <table>
   <tr>
   <td>
   <font color="#000000">
   <b>
   Dolar
   </td>
   <td>
   <input type="text" name="valor_dolar" size="5" value="<? echo $resultado_licitacion->fields['valor_dolar_lic'];?>">
   </td>
   </tr>
   </table>
   </td>
   <td width="60%">
   <table width=100% border=0>
   <tr>
     <td width="50%" align="center">
     <input type="submit" name="boton" value="Actualizar Dolar y Ganancia" size="5" <?=$candado?> onclick="return chequear_dolar();" >
	 </td>
     <td  align=left style="cursor:hand" title="Consultar Valor del Dolar">
     <img src='<?php echo "$html_root/imagenes/dolar.gif" ?>' border="0"  onclick="window.open('../../lib/consulta_valor_dolar.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=0,top=0,width=160,height=140')"  >
     </td>
   </tr>
   </table>
   </td>
   <td>
   <input type="submit" name="boton" <?=$candado?> value="Oferta" style='width:130;' >
   </td>
   <td>
   <input type="button" name="boton"  value="Salir" style='width:130;' onclick='window.close();'>
   </td>
 </tr>
 <? } //del if de idmoneda
   else { //coloco las filas sin valor dolar
        ?>
         <tr align="center">
          <td align="center">
          <input type="submit" name="boton" value="Avisar Descripcion" <?=$candado?> style='width:130;' onclick="return cuerpo();">
          <input type="hidden" name="contenido" value="">
		 </td>
         <td align="center">
         <input type="submit" name="boton" value="Avisar Oferta" <?=$candado?> style='width:130;' onclick="return cuerpo();">
        </td>
         <td>
         <input type="submit" name="boton" value="Actualizar  Ganancias" <?=$candado?> size="5"  >
         </td>
         <td>
         <input type="submit" name="boton"  value="Oferta" <?=$candado?> style='width:130;' >
         </td>
         <td>
         <input type="button" name="boton"  value="Salir" style='width:130;' onclick='window.close();'>
         </td>
         </tr>
           <?
          } //del else de valor moneda

        ?>

</table>

<hr>
<?
//consulta para saber los datos de los renglones
$query = "SELECT renglon.* FROM renglon WHERE id_licitacion = $nro_licitacion ORDER BY codigo_renglon";
$resultados=$db->execute($query) or die($query);
$i=0;
$cantidad_filas = $resultados->RecordCount();
if ($cantidad_filas > 0) {
	//verifico que los renglones no tengan el mismo nombre
	//o el mismo monto
	$warning="";//cuando el precio es el mismo y difieren los titulos
	$alert="";//cuando los titulos son iguales y difiere el monto
	$registros=0;
	//debe tener por lo menos dos renglones
	while ($registros++ < $cantidad_filas-1)
	{
		$t1=trim($resultados->fields['titulo']);
		$m1=$resultados->fields['total'];
		do
		{
			$resultados->movenext();
			if ($m1==0)
				break;

			if ($m1==$resultados->fields['total'] && $t1!=trim($resultados->fields['titulo']))
				$warning="Existen Renglones con el mismo precio y diferentes títulos<br>";

			if ($t1==trim($resultados->fields['titulo']) && $m1!=$resultados->fields['total'])
				$alert="EXISTEN RENGLONES CON EL MISMO TITULO Y DIFERENTE PRECIO<br>";
		}
		while (!$resultados->EOF);

	}
	$resultados->movefirst();
?>
<?="<center><font size=+1 color=red>$alert</font><font color=orange size=+1>$warning</font></center>" ?>
<!-- tabla que me muestra la informacion de los renglones -->
<table  align="center" border="0" width="100%" bordercolor="#580000">
<tr id="mo">
<td colspan="7" align="center">
 <font color="#E0E0E0">
  <b>Renglones existentes
  </td>
</tr>
<tr  id="mo">
           <td  width="3%">
           </td>
           <td align="center">
		   <font color="#E0E0E0" >
           <b> Renglón
           </td>
           <td align="center">
           <font color="#E0E0E0" width="5%" >
           <b> Cant.
           </td>
           <td>
           <font color="#E0E0E0">
           <b> Título
           </td>
           <td>
           <font color="#E0E0E0"  width="5%">
           <b>Ganancia
           </td>
           <td>
           <font color="#E0E0E0">
           <b>P. Unitario
           </td>
           <td>
           <font color="#E0E0E0">
		   <b>P. Total
           </td>
</tr>

<?
$cnr = 1;
$i=0;
$db->StartTrans();
while ( $i< $cantidad_filas )  {
     $id_renglon = $resultados->fields['id_renglon'];
     $titulo_renglon = $resultados->fields['titulo'];
     $nro_renglon = $resultados->fields['codigo_renglon'];
     $ganancia = $resultados->fields['ganancia'];
     $cantidad = $resultados->fields['cantidad'];
     $sin_descripcion=$resultados->fields['sin_descripcion'];
     $lista_descripcion=$resultados->fields['lista_descripcion'];
     echo "<input type='hidden' name='renglon$i' value='$id_renglon' >";
     echo "<tr align='center' bgcolor='$bgcolor1' onclick=\"chequea_radio($i);\">";
     echo "<td align='Center'>";
     echo "<input type='radio' name='radio_renglon' value='$id_renglon' onclick=\"habilita_botones();\"></td>";
     echo "</td>";
	 echo "<td align='Center' >";
     echo "<b>".$nro_renglon;
     echo "</td>";
     echo "<td align='Center' >";
     echo "<b>".$cantidad;
     echo "</td>";
     echo "<td align='Left'>";
         echo "<table width='100%'>";
           echo "<tr>";
            echo "<td width='90%'>";
			echo "<b>".$titulo_renglon;
            echo "</td>";
            echo "<td align='rigth' title='Sin Descripción' width='5%'>";
            if ($sin_descripcion)
                   echo "<img align=middle src=../../imagenes/sin_desc.gif border=0>";
            echo "</td>";
            echo "<td align='rigth' title='Descripción guardada' width='5%'>";
            if ($lista_descripcion)
                   echo "<img align=middle src=../../imagenes/descrip.gif border=0>";
            echo "</td>";

		  echo "</tr>";
         echo "</table>";
     echo "</td>";
     echo "<td align='center'>";
     //echo "<b>".$ganancia;
     echo "<input type='text' name='ganancia$i' value='$ganancia' size='5'>";
     echo "</td>";
     //tengo que obtner la cantidad de cada producto

     //ACA TENGO QUE CAMBIAR

     $sql="select * from producto where id_renglon = $id_renglon";
     $resultados_suma=$db->execute($sql) or die($sql);
     $filas_encontradas = $resultados_suma->RecordCount();
     $j=0;
     $total_renglon=0;
     while ($j<$filas_encontradas){
      $total_producto = $resultados_suma->fields['cantidad'] * $resultados_suma->fields['precio_licitacion'];
      $total_renglon+=$total_producto;
      $j++;
      $resultados_suma->MoveNext();
      }
     //total renglon tiene la suma de los productos de los renglones
     $total_renglon=number_format($total_renglon,'2','.','');

     if ($resultado_licitacion->fields['id_moneda']==1) {

                  $subtotal_renglon=($total_renglon * $resultado_licitacion->fields['valor_dolar_lic'])/$ganancia;
                  $subtotal_renglon=ceil($subtotal_renglon);
                  echo "<td align='right'> <b> \$ ".number_format($subtotal_renglon,2,'.','');
                  echo "</td>";
                  $total_cantidad_renglon=$resultados->fields['cantidad']*$subtotal_renglon;
                  echo "<td align='right'> <b> \$ ".number_format($total_cantidad_renglon,2,'.','');
                  echo "</td>";
           } //del if
                            else {
                                 $subtotal_renglon=$total_renglon /$ganancia;
                                 $subtotal_renglon=ceil($subtotal_renglon);
                                 echo "<td align='right'> <b> U\$S ".number_format($subtotal_renglon,2,'.','');
                                 echo "</td>";
                                 $total_cantidad_renglon=$resultados->fields['cantidad']*$subtotal_renglon;
                                 echo "<td align='right'> <b> U\$S ".number_format($total_cantidad_renglon,2,'.','');
								 echo "</td>";
                                       } //del else
  //en el renglon coloco unicamente el subototal osea el precio unitario falta multiplicarlo por la cantidad
  $sql="update renglon set total = $subtotal_renglon where id_renglon = $id_renglon";
  $db->execute($sql) or die ("Fallo en la actualizacin de totales: ".$sql);
  echo "</tr>";
  $resultados->MoveNext();
  $i++;
  }
$db->CompleteTrans();
echo "</table>";

//muestra el monto y nombre de la/s ofertas realizadas (si es que las hay).

//busco las ofertas en la base de datos.
$sql="  select * from ";
$sql.=" licitacion join oferta_licitacion ";
$sql.=" using (id_licitacion)";
$sql.=" where licitacion.id_licitacion = $nro_licitacion ";
$sql.=" order by oferta_licitacion.id_oferta";
$resultado_oferta=$db->execute($sql) or die ($sql."<br>".$db->ErrorMsg());
$filas_encontradas =$resultado_oferta->RecordCount();

//query para buscar el simbolo de la moneda en la base de datos
if($filas_encontradas>0){
       $id_moneda=$resultado_oferta->fields['id_moneda'];
       $query_moneda="SELECT simbolo from moneda WHERE id_moneda=$id_moneda";
	   $query_moneda=$db->execute($query_moneda) or die ($query_moneda."<br>".$db->ErrorMsg());
       $moneda=$query_moneda->fields['simbolo'];
}

//consulta para poner un title que en la tabla que muestra las ofertas cargadas.
$totales=array();
echo "<table width='100%' align=center>";
if($filas_encontradas==0)
 $no_hay_ofertas=0;
else
 $no_hay_ofertas=1;
for($i=0;$i<$filas_encontradas;$i++) {
   $id_oferta = $resultado_oferta->fields['id_oferta'];

$sql="  select * from ";
$sql.=" (select sum(renglon.total*renglon.cantidad)as total_renglon,id_renglon";
$sql.=" from licitaciones.renglon join licitaciones.elementos_oferta using (id_renglon)";
$sql.=" where id_oferta=$id_oferta group by id_renglon) as p";
$sql.=" join";
$sql.=" ( ";
$sql.=" select titulo,codigo_renglon,id_renglon from licitaciones.renglon ";
$sql.=" ) as u using (id_renglon)";
$resultado_renglones_oferta=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$str_title="";$total=0;
while(!$resultado_renglones_oferta->EOF)
{
   $str_title.=$resultado_renglones_oferta->fields['codigo_renglon'];
   $str_title.=" - ";
   $str_title.=$resultado_renglones_oferta->fields['titulo']."\n";

   $total+=$resultado_renglones_oferta->fields['total_renglon'];
   $resultado_renglones_oferta->MoveNext();
}
   echo "<tr  title='$str_title'>";
   echo "<td width=50%>&nbsp</td>";
   echo "<td bgcolor='#D5D5D5' width=35% align=left>";
   echo "<B>TOTAL OFERTA ".$resultado_oferta->fields['nombre']."</B>";
   echo "</td>";
   echo "<td bgcolor='#D5D5D5' width=15% align='center'>";
   $total=ceil($total);
   $totales[$i]=$total; //en este arreglo guardo todos los totales para despues buscar el mayor.
   $total=number_format($total,2,',','.');
   echo"<b>";
   echo $moneda."   "."<font color='#FF0000'>".$total."</font>";
   echo "</td>";
   echo "</tr>";
   $resultado_oferta->MoveNext();
   } //fin del for que calcula los totales de la oferta
?>
<?
//agregamos firmante de la licitacion
if (isset($_POST['firmante_text']))
{$texto=$_POST['firmante_text'];
 $activo=$_POST['id_activo'];
}
else
{$sql="select nombre,id_firmante_lic from firmantes_lic where activo=1";
 $resultado_activo=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $texto=$resultado_activo->fields['nombre'];
 $activo=$resultado_activo->fields['id_firmante_lic'];
}
?>
<tr>
<td></td>
<td><div align="right"><font color="blue" onclick="ventana_firmante=window.open('<?=encode_link('ventana_firmante.php',array('onclickcargar'=>'window.opener.cargar_firmante();','onclicksalir'=>'window.close()'))?>','','left=40,top=80,width=700,height=100,resizable=1')" style="cursor:hand;"><b><u>Firmante de la Licitación:</u></font></div>
<input type="hidden" name="id_activo" value="<?=$activo;?>"></td>
<td><input type="text" name="firmante_text" value="<?=$texto;?>" style="border-style:none;background-color:'transparent'; font-weight: bold;"></td>
</tr>
<?
if(sizeof($totales)>0) $monto_ofertado=max($totales);
echo "<input type='hidden' name='monto_ofertado' value='$monto_ofertado'>";
echo "<input type='hidden' name='cantidad_renglones' value='$cantidad_filas'>";
echo "</table>";
 } //del if de controla la cantidad de renglones
 else
 {echo "<input type='hidden' name='monto_ofertado' value='$monto_ofertado'>";
 echo "<table width=100% align=center";
  echo "<tr>";
    echo "<td align=center>";
     if($_ses_global_lic_o_pres=="pres")
      $datos_de="E PRESUPUESTO";
     else
      $datos_de="A LICITACION";
     echo "<b>NO HAY RENGLONES EN EST$datos_de";
    echo "</td>";
  echo "</tr>";
 echo "</table>";
 }
?>
<br>
<hr>
<table align="center" width="100%">
<tr align="center">
 <td>
 <input type="submit" name="boton"  value="Agregar Renglon" <?=$candado?> style='width:130;'>
 </td>
 <td>
 <input type="submit" name="boton"  value="Modificar Renglon" style='width:130;' disabled>
 </td>
 <td>
 <input type="submit" name="boton_duplicar"  value="Duplicar Renglon" style='width:130;' onClick="return confirm('ADVERTENCIA: se va a duplicar el renglon y sus datos');" disabled>
 </td>
 <td>
 <input type="submit" name="boton" value="Ver Descripciones" style='width:130;' disabled>
 </td>

  <td>
 <input type="submit" name="boton"  value="Eliminar Renglon" style='width:130;'  onClick="return confirm('ADVERTENCIA:Se va a eliminar el renglon y sus datos');" disabled>
 </td>
  <td>
   <?if($_ses_global_lic_o_pres=="pres")
      $datos_de="el Presupuesto";
     else
      $datos_de="la Licitación";
   ?>
 <input type="submit" name="boton"  value="Terminar" <?=$candado?> style='width:130;' onclick="if(document.all.monto_ofertado.value==''){alert('No se puede terminar <?=$datos_de?> porque aun no se ha cargado la oferta.');return false;}else return true;">
 </td>
</tr>
</table>
<hr>
<?
if (($_POST['producto']=="")&&(($_POST['boton']=="")|| ($_POST['boton']=="Eliminar Renglon") || ($_POST['boton']=='Actualizar Dolar y Ganancia') || ($_POST['boton']=='Actualizar  Ganancias') || ($_POST['boton']=='Avisar Descripcion') || ($_POST['boton']=='Terminar') || ($_POST['boton']=='Avisar Oferta'))) {

$sql="select renglon.*,etaps.id_etap,etaps.titulo as titulo_etap,etaps.texto as texto_etap  from renglon left join etaps using (id_etap) where id_licitacion = $nro_licitacion order by codigo_renglon ";
$resultado_renglon = $db->execute($sql) or die("error en consulta de renglon: ".$sql);
$cantidad_renglones = $resultado_renglon->RecordCount();
$i =0;
//este if esta para que me pongo un cartel
if ($cantidad_renglones>=1) {
echo "<table width=100% align=center>";
echo "<tr>";
 echo "<td align=center>";
  echo "<b> RESUMEN DE LOS RENGLONES";
 echo "</td>";
echo "</tr>";
echo "</table>";
}
while ($i<$cantidad_renglones) {
 $id_renglon=$resultado_renglon->fields['id_renglon'];
// $sql="select * from ((renglon join producto on renglon.id_renglon = producto.id_renglon and renglon.id_renglon = $id_renglon ) join productos on producto.id_producto = productos.id_producto)";
 $sql="select * from (producto join productos on producto.id_producto = productos.id_producto and producto.id_renglon = $id_renglon) ";
 $desc_renglon=$db->execute($sql) or die("Error en la descricion del renglon: "."<br>".$db->ErrorMsg() ."<br>".$sql) ;
 $cantidad_productos = $desc_renglon->RecordCount();
 $j=0;
  //echo "cantidad productos: ".$cantidad_productos."renglon: ".$id_renglon."<br>";
  echo "<table bgcolor='#E9E9E9' width='100%'>";
  echo "<tr id='mo'>" ;
    echo "<td width='25%' align='left'> <b>" ;
    echo "Renglón: ".$resultado_renglon->fields['codigo_renglon'];
    echo "</td>" ;
    echo "<td> <b>" ;
    echo "Título: ".$resultado_renglon->fields['titulo'];
    echo "</td>" ;
    echo "<td width='15%'> <b>" ;
    echo "Cantidad: ".$resultado_renglon->fields['cantidad'];
    echo "</td>" ;
  echo "</tr>" ;
 echo "</table>";
 echo "<table bgcolor='#F0F0F0' width='100%'>";
   echo "<tr bgcolor='$bgcolor2'>" ;
    echo "<td width='10%'> <b>" ;
    echo "Cantidad";
	echo "</td>" ;
    echo "<td width='80%' > <b>" ;
    echo "Producto ";
    if ($id_etap=$resultado_renglon->fields['id_etap'])
    {
		$titulo_etap= $resultado_renglon->fields['titulo_etap'];
    	$title=(($resultado_renglon->fields['texto_etap'])?"title='".$resultado_renglon->fields['texto_etap']."'":"");
		$buffer="<font color=red style='cursor:hand' $title onclick=\"window.open('".encode_link('ETAPS.php',array('id_etap'=>$id_etap))."','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=325');\" >$titulo_etap</font>";
    	echo $buffer;
    }
    $title=(($resultado_renglon->fields['resumen'])?"title='".$resultado_renglon->fields['resumen']."'":"");
    echo "<font color=blue style='cursor:hand' $title onclick=\"window.open('".encode_link('renglon_resumen.php',array('id_renglon'=>$id_renglon))."','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=450,height=200');\" > Resumen del Renglon</font>";
    echo "</td>" ;
    echo "<td width='10%'> <b>" ;
    echo "Precio U\$S";
    echo "</td>" ;
  echo "</tr>" ;
 $suma_productos=0;
 $total_producto=0;
 while ($j<$cantidad_productos){
 if ($desc_renglon->fields['tipo']!='garantia')
	   {
         echo "<tr>";
         echo "<td> <b>";
         echo $desc_renglon->fields['cantidad'];
         echo "</td>";
         echo "<td> <b>";
         echo $desc_renglon->fields['desc_gral'];
         echo "</td>";
         if($desc_renglon->fields['desc_precio_licitacion']!=null && $desc_renglon->fields['desc_precio_licitacion']!="")
          $color="bgcolor=$bgcolor3";
         else
          $color="";
         $desc_aux=$desc_renglon->fields['desc_precio_licitacion'];
         $id_aux=$resultado_renglon->fields['id_renglon'];
         $prod_aux=$desc_renglon->fields['tipo'];
           ?>
        <td align='right' <?=$color?> <?if($color!=""){?>title='<?=$desc_renglon->fields['desc_precio_licitacion']?>'<?}?> <?if ($color!=""){?> onclick="window.open('<?=encode_link('renglon_comentario_mostrar.php',array('var'=>$desc_aux,'id'=>$id_aux,'producto'=>$prod_aux))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');"<?}?>>
        <!-- title='";if($color!="")echo $desc_renglon->fields['desc_precio_licitacion'];echo "'-->
         <?
         echo "<b>";
         $cantidad=$desc_renglon->fields['cantidad'];
         $precio=$desc_renglon->fields['precio_licitacion'];
         $total_producto=$precio*$cantidad;

         echo number_format($total_producto,2,'.','');
         echo "</b></td>";
         //generamos link para ver historial de comentarios
            $link=encode_link("historial_comentarios.php",array("id_producto"=>$desc_renglon->fields['id_producto']));
            ?>
            <td><a href='<?=$link;?>' target="_blank">H</a></td>
            <?

         echo "</tr>";
		   }
         else {
              $garantia=$desc_renglon->fields['desc_gral'];
              $total_producto=0;
              } //del else
         $suma_productos+=$total_producto;
         $j++;
         $desc_renglon->MoveNext();
        }//del while   j<$cantidad_productos
 echo "<tr>";
 echo "<td ><b>";
 echo "Garantia:";
 echo "</td>";
 echo "<td> <b>";
 echo "$garantia";
 echo "</td>";
 echo "</tr>";
 echo "<tr bgcolor='$bgcolor1'>";
 echo "<td > <font color='#E0E0E0'><b>";
 echo "Total";
 echo "</td>";
 echo "<td colspan='2' align='right'>  <font color='#E0E0E0'> <b>";
 echo "U\$S ".number_format($suma_productos,'2','.','');
 echo "</td>";
 echo "</tr>";
 echo "</table>";
 echo "<br>";
  echo "<br>";

 $resultado_renglon->MoveNext();
 $i++;
}//del while


} //del if prinicpal

//si no hay monto ofertado los botones avisar oferta y avisar descripcion
//deben estar deshabilitados
if($monto_ofertado=="")
{?>
<script>
document.all.boton[0].disabled=1;
document.all.boton[1].disabled=1;
</script>
<?
}//de if(!$monto_ofertado)


switch($_POST['boton']) {
   case 'Agregar Renglon':{

?>
  <table align="center" width="100%">
  <tr bgcolor="#5090C0" id="mo">
  <td colspan="6" align="center">
  <font color="#E0E0E0">
 <? //script para modificar el titulo segun la maquina elegida

switch ($_POST['producto'])
{
 case "Computadora Enterprise":$titulo="Computadora Personal CDR Modelo Enterprise";
                               if($nbre_dist=="Buenos Aires - GCBA")
                               { $titulo.=" Porteña";
                               }
                               elseif ($nbre_dist=="Federal")
                               {$titulo.=" Argentina";
                               }
							   break;
 case "Computadora Matrix":$titulo="Computadora Personal CDR Modelo Matrix";
						   break;
 case "Impresora":$titulo="Titulo Impresora";
						   break;

 case "Software":$titulo="Titulo Software";
						   break;
 case "Otro":$titulo="Otro";break;
 default:$titulo="Computadora Personal CDR Modelo Enterprise";
         if($nbre_dist=="Buenos Aires - GCBA")
         { $titulo.=" Porteña";
         }
         elseif ($nbre_dist=="Federal")
         {$titulo.=" Argentina";
         }
							   break;
}
?>
  <b>Información del Renglon</td>
  </tr>
  <tr>
  <td>
  Renglon
  </td>
  <td>
  <input type="text" name="renglon"  value="<?php echo $_POST['renglon']; ?>" size="10">
  </td>
  <td>
  Título
  </td>
  <td>
  <input type="text" name="titulo"  value="<?php echo $titulo; ?>" size="50">
  </td>
  <td>
  Sin Descripción
  </td>
  <td>
  <input type="CHECKBOX"  name="sin_descripcion" value=1>
  </td>

  </tr>
  <tr>
   <td>
  Cantidad:
  </td>
  <td>
  <input type="text" name="cantidad_renglon"  value="1" size="5">
  </td>
  <td>
  Ganancia
  </td>
  <td>
  <input type="text" name="ganancia_renglon"  value="0.80" size="5">
<?
if (strtolower($resultado_licitacion->fields['tipo_entidad'])=='federal')
{
	$q="select * from etaps ORDER BY TITULO";
	$etaps=$db->Execute($q) or die($db->ErrorMsg()."<br> $q");

?>
  &nbsp;&nbsp;&nbsp;ETAPS&nbsp;
  <select name="select_etap">
  <option selected value="-1">Seleccione</option>
<?= 	 make_options($etaps,"id_etap","titulo") ?>
  <option value="0">NO ETAPS</option>
  </select>
<?
}
?>
&nbsp;<input type="button" name="boton_resumen" value="Resumen"
onclick="window.open('<?=encode_link('renglon_resumen.php',array('onclickguardar'=>"window.opener.document.all.resumen.value=document.all.resumen.value;window.close();return false"))?>'+
'&codigo_renglon='+document.all.renglon.value+'&resumen='+document.all.resumen.value
,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=450,height=200')"  >
<input type="hidden" name="resumen" value="" >
  </td>
  <td>
  Producto
  </td>
  <td>
  <SELECT name="producto" style="background-color:#0000bb;color:#ffffff;font-family:arial;font-size:10;"  onChange="form1.submit();" >
	<Option <?php if ($_POST['producto']=="Computadora Enterprise") echo "selected"; ?> > Computadora Enterprise  </Option>
	<Option <?php if ($_POST['producto']=="Computadora Matrix") echo "selected"; ?>> Computadora Matrix  </Option>
	<Option <?php if ($_POST['producto']=="Impresora") echo "selected"; ?>> Impresora  </Option>
	<Option <?php if ($_POST['producto']=="Software") echo "selected"; ?>> Software  </Option>
	<Option <?php if ($_POST['producto']=="Otro") echo "selected"; ?>> Otro </Option>
   </SELECT>
 </td>
</tr>
</table>
<?
$cantidad=0;
// esta parte es dinamica la cambio de acuerdo a la opcion que eligio el usuario

switch ($_POST['producto']) {
 case 'Impresora':  {
 $tipo=$_POST['producto'];?>
<hr>
<table align="center" width="100%">
<tr bgcolor="<?echo $bgcolor1;   ?>" id="mo">
 <td colspan="6" align="center">
 <font color="#E0E0E0">
 <b>Descripción  Renglon</td>
</tr>

<tr bgcolor="<?echo $bgcolor1;   ?>" id="mo">
 <td width="10%">
 <font color="#E0E0E0">
 <b>Cantidad
 </td>
 <td width="10%">
 <font color="#E0E0E0">
 <b>Producto
 </td>
 <td width="55%">
 <font color="#E0E0E0">
 <b>Descripcion
 </td>
 <td width="20%">
 <font color="#E0E0E0">
 <b>Precio
 </td>
 <td width="5%">

 </td>
 </tr>
<tr>
<td><input type="text" name="cantidad_impresora" value="1" size="5">
</td>
<?$sql="select * from productos where tipo='impresora' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>Impresora </td>
<td><select name="select_impresora" style="width:100%;height:50px;" onchange="document.all.precio_impresora.value=document.all.select_impresora.options[document.all.select_impresora.selectedIndex].id;document.all.desc_precio_impresora.value=''">
	<option value=0>Seleccione Impresora </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
	</select>
</td>
<td>
<input type="text" name="precio_impresora" value="" size="17">
<input type="hidden" name="desc_precio_impresora" value="">
<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_impresora','id'=>$renglon,'producto'=>'Impresora'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_impresora.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">

</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_impresora" value="1">
</td>
</tr>
<tr>
<td>
<td>Conexos</td>
<td  >
<?$sql="select * from productos where tipo='conexos' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_conexo" <? echo $estilos_select; ?> onchange="document.all.precio_conexo.value=document.all.select_conexo.options[document.all.select_conexo.selectedIndex].id;document.all.desc_precio_conexo.value=''">
<option selected value=0>Seleccione Conexo</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="text" name="precio_conexo" value="" size='17'>
<input type="hidden" name="desc_precio_conexo" value="">
<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_conexo','id'=>$renglon,'producto'=>'Precio Conexo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_conexo.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">

</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_conexo" value="1">
</td>
</tr>
<tr>
<td>
<td>Garantia</td>
<td  >
<?$sql="select * from productos where tipo='garantia' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_garantia" <? echo $estilos_select; ?>>
<option value=0>Seleccione Garantia</option>
<?
while(!($resultados->EOF)){
echo "<option value=".$resultados->fields['id_producto'];
	   if (($_POST['producto']=="Computadora Enterprise" || $_POST['producto']=="Computadora Matrix")&& $resultados->fields['desc_gral']=='garantía de 36 meses')
		echo " selected";
	  echo" >".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
</tr>
</table>
<hr>
<font size="3"><b>Adicionales</font>
<table id="adicionales" align="center" width="100%">
</table>
<?
 break;
 }
 case 'Software': { $tipo=$_POST['producto'];
					echo "<table><tr><td>Garantia</td>
						  <td>";
					$sql="select * from productos where tipo='garantia'";
					$resultados=$db->execute($sql) or die($db->ErrorMsg().$sql);
					echo "
					<select name='select_garantia' >
					 <option value=0>Seleccione Garantia</option>";
					while(!($resultados->EOF)){
					  echo "<option value=".$resultados->fields['id_producto'].">".$resultados->fields['desc_gral']."</option>";
					  $resultados->Movenext();
					}
					echo "
					 </select>
					 </td>
					 </tr>
					 </table>
					";
					break;
			  }
 case 'Otro': { $tipo=$_POST['producto'];
			  echo "<table><tr align='center'><td>Garantia</td>
						  <td>";
					$sql="select * from productos where tipo='garantia'";
					$resultados=$db->execute($sql) or die($db->ErrorMsg().$sql);
					echo "
					<select name='select_garantia' >
					 <option value=0>Seleccione Garantia</option>";
					while(!($resultados->EOF)){
					  echo "<option value=".$resultados->fields['id_producto'].">".$resultados->fields['desc_gral']."</option>";
					  $resultados->Movenext();
					}
					echo "
					 </select>
					 </td>
					 </tr>
					 </table>
					";


			 break;
			}
 default:{
 $tipo='Computadora Enterprise';
  ?>
 <hr>
 <!-- div nuevo warnning  -->
<div style="position:relative; width:100%;height:73%; overflow:auto;">
<table align="center" width="100%">
<tr  id="mo">
 <td colspan="6" align="center">
 <font color="#E0E0E0">
 <b>Descripción  Renglon</td>
</tr>
<tr id="mo" >
 <td width="10%">
 <font color="#E0E0E0">
 <b>Cantidad
 </td>
 <td width="10%">
 <font color="#E0E0E0">
 <b>Producto
 </td>
 <td width="55%">
 <font color="#E0E0E0">
 <b>Descripcion
 </td>
 <td width="20%">
 <font color="#E0E0E0">
 <b>Precio
 </td>
 <td width="5%">
  NP
 </td>
 </tr>
<tr>
<td><input type="text" name="cantidad_kit" value="1" size="5">
</td>
<td>Kit </td>
<?$sql="select * from productos where tipo='kit' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
<select name="select_kit" <? echo $estilos_select; ?> onchange="document.all.precio_kit.value=document.all.select_kit.options[document.all.select_kit.selectedIndex].id;document.all.desc_precio_kit.value=''">
<option selected value=0 id=0>Seleccione Kit</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>

</select>
</td>

<td>
<input type="hidden" name="desc_precio_kit" value="">
<input type="text" name="precio_kit" value="" size='17'>&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_kit','id'=>$renglon,'producto'=>'kit'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_kit.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">

</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_kit" value="1">
</td>
</tr>
<tr>
<td><input type="text" name="cantidad_micro" value="1" size="5"></td>
<td>Micro</td>
<?$sql="select * from productos where tipo='micro' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
<select name="select_micro" <? echo $estilos_select; ?> onchange="llamada_funciones(document.all.select_micro.options[document.all.select_micro.selectedIndex].value);document.all.precio_micro.value=document.all.select_micro.options[document.all.select_micro.selectedIndex].id;document.all.desc_precio_micro.value=''">
<option value=0>Seleccione Microprocesador </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>

</select>
</td>
<td>
<input type="hidden" name="desc_precio_micro" value="">
<input type="text" name="precio_micro" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_micro','id'=>$renglon,'producto'=>'Microprosesador'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_micro.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_micro" value="1">
</td>
</tr>

<tr>
<td><input type="text" name="cantidad_madre" value="1" size="5"></td>
<td>Placa Madre</td>
<td>
<select name="select_madre" <? echo $estilos_select; ?> onchange="document.all.precio_madre.value=document.all.select_madre.options[document.all.select_madre.selectedIndex].id;document.all.desc_precio_madre.value=''">
<option selected value=0>Seleccione Placa Madre</option>
</select>
</td>

<td>
<input type="hidden" name="desc_precio_madre" value="">
<input type="text" name="precio_madre" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_madre','id'=>$renglon,'producto'=>'Placa Madre'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_madre.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_madre" value="1">
</td>

</tr>
<tr>
<td><input type="text" name="cantidad_memoria" value="1" size="5"></td>
<td>Memoria</td>
<?
 $sql="select * from productos where tipo='memoria' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
<select name="select_memoria" <? echo $estilos_select; ?> onchange="document.all.precio_memoria.value=document.all.select_memoria.options[document.all.select_memoria.selectedIndex].id;document.all.desc_precio_memoria.value=''">
<option value=0>Seleccione Memoria </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</td>
<td>
<input type="hidden" name="desc_precio_memoria" value="">
<input type="text" name="precio_memoria" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_memoria','id'=>$renglon,'producto'=>'Memoria'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_memoria.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_memoria" value="1">
</td>

</tr>
<tr>
<td><input type="text" name="cantidad_disco" value="1" size="5"></td>
<td>Disco</td>
<td>
<? $sql="select * from productos where tipo='disco rigido' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_disco" <? echo $estilos_select; ?> onchange="document.all.precio_disco.value=document.all.select_disco.options[document.all.select_disco.selectedIndex].id;document.all.desc_precio_disco.value=''">
<option selected value=0> Seleccione un Disco Rigido </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>

</select>
</td>
<td>
<input type="hidden" name="desc_precio_disco" value="">
<input type="text" name="precio_disco" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_disco','id'=>$renglon,'producto'=>'Disco Rigido'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_disco.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_disco" value="1">
</td>

</tr>
<tr>
<td><input type="text" name="cantidad_cd" value="1" size="5"></td>
<td>CD-Rom</td>
<td>
<? $sql="select * from productos where tipo='cdrom' order by desc_gral";
  $resultados=$db->execute($sql) or die($query);
?>

<select name="select_cd" <? echo $estilos_select; ?> onchange="document.all.precio_cd.value=document.all.select_cd.options[document.all.select_cd.selectedIndex].id;document.all.desc_precio_cd.value=''">
<option selected value=0>Seleccione cd-rom</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>

</select>
</td>
<td>
<input type="hidden" name="desc_precio_cd" value="">
<input type="text" name="precio_cd" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_cd','id'=>$renglon,'producto'=>'Lectora CD'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_cd.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_cd" value="1">
</td>

</tr>
<tr>
<td><input type="text" name="cantidad_monitor" value="1" size="5"></td>
<td>Monitor</td>
<td>
<?$sql="select * from productos where tipo='monitor' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_monitor" <? echo $estilos_select; ?> onchange="document.all.precio_monitor.value=document.all.select_monitor.options[document.all.select_monitor.selectedIndex].id;document.all.desc_precio_monitor.value=''">
<option selected value=0>Seleccione monitor</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_monitor" value="">
<input type="text" name="precio_monitor" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_monitor','id'=>$renglon,'producto'=>'Monitor'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_monitor.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_monitor" value="1">
</td>

</tr>
<tr>
<td><input type="text" name="cantidad_sistemaoperativo" value="1" size="5"></td>
<td>Sistema Operativo</td>
<td>
<?$sql="select * from productos where tipo='sistema operativo' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_sistemaoperativo" <? echo $estilos_select; ?> onchange="document.all.precio_sistemaoperativo.value=document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].id;document.all.desc_precio_sistemaoperativo.value=''">
<option selected value=0>Seleccione Sistema Operativo</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_sistemaoperativo" value="">
<input type="text" name="precio_sistemaoperativo" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_sistemaoperativo','id'=>$renglon,'producto'=>'Sistema Operativo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_sistemaoperativo.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_sistemaoperativo" value="1">
</td>
</tr>
<tr>
<td>
<td>Conexos</td>
<td  >
<?$sql="select * from productos where tipo='conexos' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_conexo" <? echo $estilos_select; ?> onchange="document.all.precio_conexo.value=document.all.select_conexo.options[document.all.select_conexo.selectedIndex].id;document.all.desc_precio_conexo.value=''" >
<option selected value=0>Seleccione Conexo</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_conexo" value="">
<input type="text" name="precio_conexo" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_conexo','id'=>$renglon,'producto'=>'Precio Conexo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_conexo.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_conexo" value="1">
</td>

</tr>
<tr>
<td>
<td>Garantia</td>
<td  >
<?$sql="select * from productos where tipo='garantia' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_garantia" <? echo $estilos_select; ?> >
<option selected value=0>Seleccione Garantia</option>
<?
while(!($resultados->EOF)){
echo "<option value=".$resultados->fields['id_producto']; 
       if (($_POST['producto']=="" || $_POST['producto']=="Computadora Enterprise" || $_POST['producto']=="Computadora Matrix")&& $resultados->fields['desc_gral']=='garantía de 36 meses')
        echo " selected";
      echo" >".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
</tr>
</table>
<hr>
<table id="adicionales" align="center" width="100%">
<tr id="mo">
 <td colspan="6" align="center">
 <font color="#E0E0E0">
 <b>Adicionales del  Renglon</td>
</tr>
<tr id="mo">
 <td width="10%">
 <font color="#E0E0E0">
 <b>Cantidad
 </td>
 <td width="10%">
 <font color="#E0E0E0">
 <b>Producto
 </td>
 <td width="55%">
 <font color="#E0E0E0">
 <b>Descripcion
 </td>
 <td width="20%">
 <font color="#E0E0E0">
 <b>Precio
 </td>
 <td width="5%">
 </td>
 </tr>
<tr>
<td><input type="text" name="cantidad_video" value="1" size="5"></td>
<td>Video</td>
<td>
<?$sql="select * from productos where tipo='video' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_video" <? echo $estilos_select; ?> onchange="document.all.precio_video.value=document.all.select_video.options[document.all.select_video.selectedIndex].id;document.all.desc_precio_video.value=''">
<option selected value=0>Seleccione video</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>

</select>
</td>
<td>
<input type="hidden" name="desc_precio_video" value="">
<input type="text" name="precio_video" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_video','id'=>$renglon,'producto'=>'Placa de Video'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_video.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_video" value="1">
</td>
</tr>

<tr>
<td><input type="text" name="cantidad_grabadora" value="1" size="5"></td>
<td>Grabadora</td>
<td>
<?$sql="select * from productos where tipo='grabadora' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_grabadora" <? echo $estilos_select; ?> onchange="document.all.precio_grabadora.value=document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].id;document.all.desc_precio_grabadora.value=''">
<option selected value=0 >Seleccione grabadora</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>

</select>
</td>
<td>
<input type="hidden" name="desc_precio_grabadora" value="">
<input type="text" name="precio_grabadora" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_grabadora','id'=>$renglon,'producto'=>'Grabadora'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_grabadora.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_grabadora" value="1">
</td>

</tr>

<tr>
<td><input type="text" name="cantidad_dvd" value="1" size="5"></td>
<td>DVD</td>
<td>
<?$sql="select * from productos where tipo='dvd' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_dvd" <? echo $estilos_select; ?> onchange="document.all.precio_dvd.value=document.all.select_dvd.options[document.all.select_dvd.selectedIndex].id;document.all.desc_precio_dvd.value=''">
<option selected value=0>Seleccione dvd</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_dvd" value="">
<input type="text" name="precio_dvd" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_dvd','id'=>$renglon,'producto'=>'Lectora de DVD'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_dvd.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_dvd" value="1">
</td>
</tr>

<tr>
<td><input type="text" name="cantidad_red" value="1" size="5"></td>
<td>Red</td>
<td>
<?$sql="select * from productos where tipo='lan' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<select name="select_red" <? echo $estilos_select; ?> onchange="document.all.precio_red.value=document.all.select_red.options[document.all.select_red.selectedIndex].id;document.all.desc_precio_red.value=''">
 <option selected value=0>Seleccione placa de red</option>
 <?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
 </select>
</td>
<td>
<input type="hidden" name="desc_precio_red" value="">
<input type="text" name="precio_red" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_red','id'=>$renglon,'producto'=>'Placa de Red'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_red.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_red" value="1">
</td>
</tr>

<tr>
<td><input type="text" name="cantidad_modem" value="1" size="5"></td>
<td>Modem</td>
<td>
<?$sql="select * from productos where tipo='modem' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<select name="select_modem" <? echo $estilos_select; ?> onchange="document.all.precio_modem.value=document.all.select_modem.options[document.all.select_modem.selectedIndex].id;document.all.desc_precio_modem.value=''">
<option selected value=0>Seleccione Modem</option>
 <?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
 </select>
</td>
<td>
<input type="hidden" name="desc_precio_modem" value="">
<input type="text" name="precio_modem" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_modem','id'=>$renglon,'producto'=>'Modem'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_modem.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_modem" value="1">
</td>

</tr>
<tr>
<td><input type="text" name="cantidad_zip" value="1" size="5"></td>
<td>ZIP</td>
<td>
<?$sql="select * from productos where tipo='zip' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<select name="select_zip" <? echo $estilos_select; ?> onchange="document.all.precio_zip.value=document.all.select_zip.options[document.all.select_zip.selectedIndex].id;document.all.desc_precio_zip.value=''">
<option selected value=0>Seleccione ZIP</option>
 <?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
echo "<option value=".$resultados->fields['id_producto']." id=".$resultado_precio->fields['precio']." > ".$resultados->fields['desc_gral']."</option>";
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_zip" value="">
<input type="text" name="precio_zip" value="" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_zip','id'=>$renglon,'producto'=>'Zip'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_zip.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_zip" value="1">
</td>

</tr>
</table>
  <?
  break; //fin de default
  }
   } //fin de switch
?>
<table id="productos_ad" width="100%">
<tr id="mo">
<td colspan="4" align="center"><font color="#E0E0E0"><b>Productos Adicionados</td>
</tr>
<tr id="mo">
<td width="8%"><font color="#E0E0E0"><b>Cantidad</td>
<td width="10%"><font color="#E0E0E0"><b>Producto</td>
<td width="62%"><font color="#E0E0E0"><b>Descripción</td>
<td width="20%"><font color="#E0E0E0"><b>Precio</td>
</tr>
<?
for ($i=1;$i<=15;$i++) {
 $link=encode_link("../general/productos2.php",array("tipo"=>$tipo,"fila"=>$i,"onclickcargar"=>"window.opener.agregar($i)"));
  ?>
<tr>
<td width="8%"><input type="text" name="cantidad<?=$i;?>" size='5'><input type="hidden" name="estado<?=$i;?>" value="4"></td>
<td width="10%"><input type="hidden" name="tipo<?=$i;?>" value=""><input type="text" name="tip<?=$i;?>" style="width=100%" readonly></td>
<td width="62%"><input type="text" name="descripcion<?=$i;?>" value="" style="width=100%"></td>
<td width="20%">
<input type="hidden" name="desc_precio_<?=$i;?>" value="">
<input type='text' name="precio<?=$i;?>" size='8'>&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_'.$i,'id'=>$renglon,'producto'=>'Nuevo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300,resizable=1');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">&nbsp;<input type="button" value="agregar" style='width=51' name="boton<?=$i;?>" onclick="document.all.desc_precio_<?=$i;?>.value='';switch_func(<?=$i;?>,'<?php echo $link; ?>');"></td>
</tr>

<?
}//del for
?>

</table>

</div>
<hr>
<table align="center"  width="50%">
  <tr><td>
<input type="submit" name="boton" value="Guardar" style='width:160;' onclick="return verificar_precios(); ">
  </td>
  <td>
  <input type="submit" name="boton" value="Cancelar" style='width:160;'>
  </td>
  </tr>
</table>
 <? break;
   } //del case agregar producto
  } //del switch
 ?>
</FORM>
</BODY>
</HTML>
<?fin_pagina()?>