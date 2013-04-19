<?php
/*AUTOR: Fernando - MAC
  Fecha: 14/10/04

$Author: mari $
$Revision: 1.8 $
$Date: 2006/04/21 14:29:26 $
*/


require_once("../../../config.php");
require_once("pcpower_funciones.php");
//obtengo el nro de la licitacion
$id_licitacion=$parametros["licitacion"] or $id_licitacion=$parametros["ID"];
//echo "cantidad de filas".$_POST["cantidad_renglones"];

$pagina_padre="pcpower_presupuestos_view.php";


switch ($_POST['boton'])
{
 case "Agregar Renglon":
                $link=encode_link("pcpower_licitaciones_renglones_oferta.php",array("id_licitacion"=>$id_licitacion,"volver"=>"pcpower_licitaciones_renglones.php","ganancia_oculta"=>$_POST["ganancia_oculta"]));
                header("Location:$link");
                break;

 case "Ver Descripciones":
               $link=encode_link("pcpower_vista_previa.php", array("licitacion" =>$id_licitacion,"id_renglon"=>$_POST['radio_renglon'],"modificacion" => 1,"volver"=>"pcpower_licitaciones_renglones.php","ganancia_oculta"=>$_POST["ganancia_oculta"]));
               header("Location:$link");
               break;
 /*
 case "Guardar": //guardo los datos del nuevo renglon
                 //es un quilombo;
                 require('guardar_renglon.php');
		 break;
*/
 case "Modificar Renglon":
                $link=encode_link("pcpower_licitaciones_renglones_oferta.php",array("id_licitacion"=>$id_licitacion,"id_renglon"=>$_POST['radio_renglon'],"ganancia_oculta"=>$_POST["ganancia_oculta"]));
                header("Location:$link");
                break;

               break;

 case "Eliminar Renglon":
               echo kill_reng($_POST['radio_renglon']);
               break;
 case "Actualizar Dolar y Ganancia":
	      $db->StartTrans();
	      $valor_dolar=$_POST['valor_dolar'];
	      $sql="update pcpower_licitacion set valor_dolar_lic = $valor_dolar where id_licitacion = $id_licitacion" ;
	      sql($sql) or fin_pagina();
	      //actualizo las ganancias
	      $cantidad=$_POST["cantidad_renglones"] ;
	      $i=0;
	      //actualizo los valores
	      while ($i<$cantidad) {
	          $ganancia=$_POST["ganancia$i"];
		  if (($ganancia > 0) && ($ganancia <=1)) {
		         $id_renglon = $_POST["renglon$i"];
			 $sql="update pcpower_renglon set ganancia = $ganancia where id_renglon = $id_renglon" ;
			 sql($sql) or fin_pagina();
                         }
		 $i++;
 	         }//del while
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
			      $sql="update pcpower_renglon set ganancia = $ganancia where id_renglon = $id_renglon" ;
			      sql($sql) or fin_pagina();
                              }
		$i++;
		}
        	$db->CompleteTrans();
                break;
 case "Oferta":
               $link=encode_link("pcpower_renglon_alternativa.php",array("licitacion"=>$id_licitacion,"volver"=>"pcpower_licitaciones_renglones.php"));
	       header("location: $link") or die();
	       break;
 case "Terminar":
   	       $query_control="SELECT * from pcpower_oferta_licitacion WHERE id_licitacion=$id_licitacion";
	       $control=sql($query_control) or fin_pagina();
	       $cantidad_ofertas=$control->RecordCount();
	     //if($cantidad_ofertas>0) {
		 //  $nombre_excel=genera_cotizacion_licitacion($id_licitacion);
		   //genera el excel para el cd de oferta
		 //  $nombre_excel_cd=genera_cotizacion_licitacion_cd($id_licitacion);
                   //insertamos en entregar_lic el nombre del archivo  e indicamos
		  //que es la oferta de esta licitacion, y que esta lista para imprimir
		   //$query_arch="update pcpower_entregar_lic set oferta_subida=1, archivo_oferta='$nombre_excel' where id_licitacion=$id_licitacion";
		   $query_arch="update pcpower_entregar_lic set oferta_subida=1 where id_licitacion=$id_licitacion";
		   sql($query_arch) or fin_pagina();
           $link_fin = encode_link("$pagina_padre",array("cmd1"=>"detalle","ID"=>$id_licitacion));
		   echo "<html><head><script language=javascript>";
                   echo "window.opener.document.location.href='$link_fin';window.opener.focus();window.close();";
                   echo "</script></head></html>";
		   break;
                 /*}
                 else {
                  $informar="<font color='red'><b>Error: No esta armada la oferta</b></font>";
                  echo "<script>alert ('Error: No esta definida la combinacion de renglones para armar la oferta')</script>";
                  break;
                 }*/

 case "Cancelar":
                $link=encode_link("pcpower_licitaciones_renglones.php", array("licitacion" =>$id_licitacion));
                header("location: $link") or die();
                break;

 case "Avisar Descripcion":
                $sql="select usuario_avisar from pcpower_mail_aviso where usuario_avisa='$_ses_user_login' and tipo=0";
                $resultado=sql($sql) or fin_pagina();
                $para=$resultado->fields['usuario_avisar'];
                $sql="select pcpower_entidad.nombre from pcpower_licitacion join pcpower_entidad on pcpower_licitacion.id_licitacion=$id_licitacion and pcpower_licitacion.id_entidad=entidad.id_entidad";
		$resultado=sql($sql) or fin_pagina();
                $entidad=$resultado->fields['nombre'];
                $mailtext=$_POST['contenido'];
                if($_ses_global_lic_o_pres=="pres")
                        $asunto_title="Presupuesto";
                        else
                        $asunto_title="Licitación";
                $asunto="Descripciones listas - $asunto_title Nº $id_licitacion - Entidad: $entidad ";
                //**************************************//
                //LLEVAR A LA FUNCION MAIL
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
                            {
                             $mail_header="";
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

 case "Avisar Oferta":
             $sql="select usuario_avisar from pcpower_mail_aviso where usuario_avisa='$_ses_user_login' and tipo=1";
  	         $resultado=sql($sql) or fin_pagina();
             $para=$resultado->fields['usuario_avisar'];
             $sql="select pcpower_entidad.nombre from pcpower_licitacion join pcpower_entidad on pcpower_licitacion.id_licitacion=$id_licitacion and pcpower_licitacion.id_entidad=pcpower_entidad.id_entidad";
	         $resultado=sql($sql) or fin_pagina();
             $entidad=$resultado->fields['nombre'];
             $mailtext=$_POST['contenido'];
             $asunto_title="Presupuesto";

             $asunto="Oferta lista - $asunto_title Nº $id_licitacion - Entidad: $entidad ";
             //********************************************
             //LLEVAR A MAIL
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
	require_once("pcpower_duplicar_renglon.php");
        die();
}

$sql="select pcpower_licitacion.*,pcpower_entidad.*,pcpower_tipo_entidad.nombre as tipo_entidad,
        pcpower_candado.estado as candado,pcpower_distrito.nombre as nbre_dist
        from (pcpower_licitacion join pcpower_entidad on pcpower_licitacion.id_entidad = pcpower_entidad.id_entidad and id_licitacion = $id_licitacion)
        join pcpower_candado using (id_licitacion)
        join pcpower_tipo_entidad using(id_tipo_entidad)
        join pcpower_distrito using(id_distrito)";
$resultado_licitacion=sql($sql) or fin_pagina();


if($_POST['poner_check']=="Poner Check")
          {//agregamos el check a la licitacion
          $query="update pcpower_licitacion set check_lic=1 where id_licitacion=$id_licitacion";
          sql($query) or fin_pagina();
          $link=encode_link("pcpower_licitaciones_renglones.php", array("licitacion" =>$id_licitacion));
          header("location: $link") or die();
          }
          elseif($_POST['sacar_check']=="Sacar Check")
                 {//quitamos el check a la licitacion
                 $query="update pcpower_licitacion set check_lic=0 where id_licitacion=$id_licitacion";
                 $sql($query) or fin_pagina();
                 $link=encode_link("pcpower_licitaciones_renglones.php", array("licitacion" =>$id_licitacion));
                 header("location: $link") or die();
                 }

if ($_POST['producto']!="") $_POST['boton']="Agregar Renglon";

?>
<script languaje="javascript">
//oculta los montos de precios y ganancias
//de la pagina
function ocultar_monetarios()
{
 document.all.tabla_actualizar_ganancia.style.display='none';
 document.all.td_ganancia.style.display='none';

 var f=0;
 var dato_ganancia;
 var dato_p_unitario;
 var dato_p_total;
 var desc;
 var tam_tabla_prod,tabla_prod;
 var g;
 while(f<document.all.cantidad_renglones.value)
 {dato_ganancia=eval("document.all.dato_ganancia"+f);
  dato_ganancia.style.display="none";
  
 
  //RECORRER LA TABLA Y BORRAR LA TERCERA Y CUARTA FILA, PARA ESCONDER PRECIO
  tabla_prod=eval("document.all.tabla_productos_renglon"+f);
  if(typeof(tabla_prod)!="undefined")
  {tam_tabla_prod=tabla_prod.rows.length;
   g=0;
   while(g<tam_tabla_prod)
   {
   	if(typeof(tabla_prod.rows[g].cells[2])!="undefined")
   	 tabla_prod.rows[g].cells[2].style.display='none';
   	if(typeof(tabla_prod.rows[g].cells[3])!="undefined")
   	 tabla_prod.rows[g].cells[3].style.display='none';
   	g++;
   }	
  }//de if(typeof(tabla_prod)!="undefined")
  total_renglon=eval("document.all.td_total_renglon"+f);
  if(typeof(total_renglon)!="undefined")
   total_renglon.style.display="none";
  precio_dolar=eval("document.all.input_precio_dolares"+f);
  if(typeof(precio_dolar)!="undefined")
   precio_dolar.style.display="none";
  
  f++;
 } 
 document.all.ocultar_mostrar_monetarios.title="Muestra la ganancia y los precios de cada producto";
 document.all.ocultar_mostrar_monetarios.onclick=muestra_monetarios;
 document.all.ocultar_mostrar_monetarios.style.background='green';
 document.all.ganancia_oculta.value=1;
}	

//muestra los montos de precios y ganancias
//de la pagina
function muestra_monetarios()
{
 document.all.tabla_actualizar_ganancia.style.display='block';
 document.all.td_ganancia.style.display='block';

 var f=0;
 var dato_ganancia;
 var dato_p_unitario;
 var dato_p_total;
 var desc;
 var tam_tabla_prod,tabla_prod;
 var g;
 while(f<document.all.cantidad_renglones.value)
 {dato_ganancia=eval("document.all.dato_ganancia"+f);
  dato_ganancia.style.display="block";
  
 
  //RECORRER LA TABLA Y AGREGAR LA TERCERA Y CUARTA FILA, PARA ESCONDER PRECIO
  tabla_prod=eval("document.all.tabla_productos_renglon"+f);
  if(typeof(tabla_prod)!="undefined")
  {tam_tabla_prod=tabla_prod.rows.length;
   g=0;
   while(g<tam_tabla_prod)
   {
   	if(typeof(tabla_prod.rows[g].cells[2])!="undefined")
   	 tabla_prod.rows[g].cells[2].style.display='block';
   	if(typeof(tabla_prod.rows[g].cells[3])!="undefined")
   	 tabla_prod.rows[g].cells[3].style.display='block';
   	g++;
   }	
  }//de if(typeof(tabla_prod)!="undefined")
  total_renglon=eval("document.all.td_total_renglon"+f);
  if(typeof(total_renglon)!="undefined")
   total_renglon.style.display="block";
  precio_dolar=eval("document.all.input_precio_dolares"+f);
  if(typeof(precio_dolar)!="undefined")
   precio_dolar.style.display="block";
  f++;
 } 
 
 document.all.ocultar_mostrar_monetarios.title="Oculta la ganancia y los precios de cada producto";
 document.all.ocultar_mostrar_monetarios.onclick=ocultar_monetarios;
 document.all.ocultar_mostrar_monetarios.style.background='red';
 document.all.ganancia_oculta.value=0;
}

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

<meta Name="generator" content="PHPEd Version 3.2 (Build 3220 )   ">
<title>Renglones</title>
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
$estilos_select="style='width:100%;height:50px;'";
 ?>
<style type="text/css">
</style>

<link rel="SHORTCUT ICON"  href="/path-to-ico-file/logo.ico">
<script languaje="javascript">
<?
//si el candado esta puesto, la funcion habilita_botones()
//no debe habilitar ningun boton salvo el de Ver Descripciones
if($resultado_licitacion->fields['candado']==0)
      {
       echo"
       function habilita_botones(){
       document.all.boton[1].disabled=0;
       document.all.boton[2].disabled=0;
       document.all.boton[3].disabled=0;
       document.all.boton[4].disabled=0;
       document.all.boton[5].disabled=0;
       document.all.boton[6].disabled=0;
       }";
       }
        else
         {
          echo "
          function habilita_botones(){
          document.all.boton[4].disabled=0;
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

function chequear_dolar()
   {
   if (window.document.all.valor_dolar.value==""){
        alert('Debe ingesar un valor para el Dolar');
        return false;
        }
   else
      return true;
   }
</script>
<?
echo $html_header;
$link=encode_link("pcpower_licitaciones_renglones.php", array("licitacion" => $id_licitacion));
?>
<FORM  action="<? echo $link; ?>" name="form1" method="POST">
<INPUT TYPE="HIDDEN"  name="accion_tomar">
<font size='3'><center>
<?
if ($informar)aviso($informar);
if($_POST['boton']=="Terminar") echo "<br>";
?>
<table align="center" border=0 width="100%">
    <tr>
    <td colspan="5" align="center" width=85% id="mo">
    <b>Datos del Presupuesto
    </td>
    <td width=2% align=center>
    <?/*<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_realizar_of.htm" ?>', 'REALIZAR OFERTA')" >*/?>
    <input type="button" id="ocultar_mostrar_monetarios" style="background='red';" value="G" title="Oculta la ganancia y los precios de cada producto" onclick="if(typeof(document.all.cantidad_renglones)!='undefined'&&parseInt(document.all.cantidad_renglones.value)>0)ocultar_monetarios()" >
    <?
    if($parametros["ganancia_oculta"])
     $ganancia_oculta=$parametros["ganancia_oculta"];
    elseif($_POST["ganancia_oculta"]) 
     $ganancia_oculta=$_POST["ganancia_oculta"];
    else 
     $ganancia_oculta=0; 
    ?>
    <input type="hidden" name="ganancia_oculta" value="<?=$ganancia_oculta?>">
    </td>
    </tr>
    <tr>
    <td>
    <b>
    <?
    $datos_de="Presupuesto";
    echo $datos_de;
   ?>
   <font color="#FF0000">
   <?
    echo $id_licitacion;
      if($resultado_licitacion->fields['candado']!=0)
        {
         $datos_de="e presupuesto";
         echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Est$datos_de solo puede verse, pero no modificarse'>";
         $candado="disabled";
        }
       else
           $candado="";
   ?>
   </font>
   </td>
   <td>
   <b>
   Entidad
   </td>
   <td>
    <font color="#FF0000">
    <b>
    <? echo $resultado_licitacion->fields['nombre'];  ?>
    </font>
    <?$nbre_dist=$resultado_licitacion->fields['nbre_dist'];?>
   </td>
   <td>
   &nbsp;
   <?/*
   if (permisos_check("inicio","poner_check_lic"))
                    $visibility="visible";
                    else
                   $visibility="hidden";
   if($resultado_licitacion->fields['check_lic']==0)
     {
     ?>
      <input type="submit" name="poner_check" style="visibility:<?=$visibility?>" value="Poner Check">
     <?
     }
     else
       {
       ?>
       <input type="submit" name="sacar_check" style="visibility:<?=$visibility?>" value="Sacar Check">
	   <?
       }*/
       ?>
    </td>
 </tr>
</table>

<table align="center" width=100%>
<?
 if ($resultado_licitacion->fields['id_moneda']==1)  {
 ?>
 <tr>
   <td align="left">
   </td>
   <td>
   </td>
   <td align="left">
   <?/*<input type="submit" name="boton" value="Avisar Descripcion" style='width:130;' <?=$candado?> onclick="return cuerpo();">*/?>&nbsp;
   <input type="hidden" name="contenido" value="">
   </td>
   <td align="left">
   <?/*<input type="submit" name="boton" value="Avisar Oferta" style='width:130;' <?=$candado?> onclick="return cuerpo();">*/?>&nbsp;
   </td>
   </tr>
   <tr>
   <td>
     <table id="tabla_valor_dolar">
      <tr>
        <td>
          <font color="#000000">
             <b> Dolar </b>
          </font>
        </td>
        <td>
        <input type="text" name="valor_dolar" size="5" value="<? echo $resultado_licitacion->fields['valor_dolar_lic'];?>">
        </td>
       </tr>
     </table>
   </td>
   <td width="60%">
   <table width=100% border=0 >
   <tr>
     <td width="50%" align="center" id="tabla_actualizar_ganancia">
     <input type="submit" name="boton" value="Actualizar Dolar y Ganancia" size="5" <?=$candado?> onclick="return chequear_dolar();" >
	 </td>
     <td  align=left style="cursor:hand" title="Consultar Valor del Dolar">
     <img src='<?php echo "$html_root/imagenes/dolar.gif" ?>' border="0"  onclick="window.open('../../../lib/consulta_valor_dolar.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=0,top=0,width=160,height=140')"  >
     </td>
   </tr>
   </table>
   </td>
   <td>
   <?/*<input type="submit" name="boton" <?=$candado?> value="Oferta" style='width:130;' >*/?>
   </td>
   <td>
   <input type="button" name="boton"  value="Salir" style='width:130;' onclick='window.close();'>
   </td>
 </tr>
 <?
 } //del if de idmoneda
   else { //coloco las filas sin valor dolar
        ?>
         <tr align="center">
          <td align="center">
          <?/*<input type="submit" name="boton" value="Avisar Descripcion" <?=$candado?> style='width:130;' onclick="return cuerpo();">*/?>&nbsp;
          <input type="hidden" name="contenido" value="">
		 </td>
         <td align="center">
         <?/*<input type="submit" name="boton" value="Avisar Oferta" <?=$candado?> style='width:130;' onclick="return cuerpo();">*/?>
        </td>
         <td id="tabla_actualizar_ganancia">
         <input type="submit" name="boton" value="Actualizar  Ganancias" <?=$candado?> size="5"  >
         </td>
         <td>
         <?/*<input type="submit" name="boton"  value="Oferta" <?=$candado?> style='width:130;' >*/?>
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
$query = "select * from (
          SELECT pcpower_renglon.*,pcpower_etaps.id_etap,pcpower_etaps.titulo as titulo_etap,
                 pcpower_etaps.texto as texto_etap
          FROM pcpower_renglon left join pcpower_etaps using(id_etap)
          WHERE id_licitacion = $id_licitacion
          ) as renglon
          left join
          (
          select sum(cantidad*precio_licitacion) as total_renglon,id_renglon
          from pcpower_producto group by id_renglon
          ) as totales
          using(id_renglon) ORDER BY codigo_renglon
          ";
$resultados=sql($query) or fin_pagina();
//Esta asignacion la uso mas adelante
$resultado_renglon=$resultados;
$i=0;
$cantidad_filas = $resultado_renglon->RecordCount();
if ($cantidad_filas > 0) {
	//verifico que los renglones no tengan el mismo nombre
	//o el mismo monto
	$warning="";//cuando el precio es el mismo y difieren los titulos
	$alert="";//cuando los titulos son iguales y difiere el monto
	$registros=0;
	//debe tener por lo menos dos renglones
	while ($registros++ < $cantidad_filas-1)
	{
		$t1=trim($resultado_renglon->fields['titulo']);
		$m1=$resultado_renglon->fields['total'];
		do
		{
			$resultado_renglon->movenext();
			if ($m1==0)
				break;

			if ($m1==$resultado_renglon->fields['total'] && $t1!=trim($resultado_renglon->fields['titulo']))
				$warning="Existen Renglones con el mismo precio y diferentes títulos<br>";

			if ($t1==trim($resultado_renglon->fields['titulo']) && $m1!=$resultado_renglon->fields['total'])
				$alert="EXISTEN RENGLONES CON EL MISMO TITULO Y DIFERENTE PRECIO<br>";
		}
		while (!$resultado_renglon->EOF);

	}
	$resultado_renglon->movefirst();
?>
<?="<center><font size=+1 color=red>$alert</font><font color=orange size=+1>$warning</font></center>" ?>
<!-- tabla que me muestra la informacion de los renglones -->
<table  align="center" border="0" width="100%" bordercolor="#580000">
<tr id="mo">
<td colspan="7"><b>Renglones existentes</b></td>
</tr>
<tr  id="mo">
           <td  width="1%">&nbsp;   </td>
           <td width="10%"><b> Renglón</b>    </td>
           <td  width="5%"><b> Cant.  </b>    </td>
           <td width="50%"><b> Título </b>    </td>
           <td id="td_ganancia"><b>Ganancia</b>    </td>
           <td id="td_p_unitario"><b>P. Unitario</b> </td>
           <td id="td_p_total"><b>P. Total </b>   </td>
</tr>
<?
$i=0;
$db->StartTrans();
while ( $i< $cantidad_filas )  {
     $id_renglon = $resultado_renglon->fields['id_renglon'];
     $titulo_renglon = $resultado_renglon->fields['titulo'];
     $nro_renglon = $resultado_renglon->fields['codigo_renglon'];
     $ganancia = $resultado_renglon->fields['ganancia'];
     $cantidad = $resultado_renglon->fields['cantidad'];
     $sin_descripcion=$resultado_renglon->fields['sin_descripcion'];
     $lista_descripcion=$resultado_renglon->fields['lista_descripcion'];
?>
    <input type='hidden' name='<?="renglon$i"?>' value='<?=$id_renglon?>' >
    <tr align='center' bgcolor='<?=$bgcolor_out?>' onclick="chequea_radio(<?=$i?>);">
    <td align='Center'>
    <input type='radio' name='radio_renglon' value='<?=$id_renglon?>' onclick="habilita_botones();"></td>
    </td>
	<td align='Center' >
       <b><?=$nro_renglon?></b>
    </td>
    <td align='Center' >
    <b><?=$cantidad?>
    </td>
    <td align='Left'>
           <table width='100%'>
            <tr>
            <td width='90%'><b><?=$titulo_renglon?></td>
            <td align='rigth' title='Sin Descripción' width='5%'>
            <?
            if ($sin_descripcion)
                   echo "<img align=middle src=../../../imagenes/sin_desc.gif border=0>";
            ?>
            </td>
            <td align='rigth' title='Descripción guardada' width='5%'>
            <?
            if ($lista_descripcion)
                   echo "<img align=middle src=../../../imagenes/descrip.gif border=0>";
            ?>
            </td>
          </tr>
         </table>
     </td>
     <td align='center' id="dato_ganancia<?=$i?>">
     <input type='text' name='<?="ganancia$i"?>' value='<?=$ganancia?>' size='5'>
     </td>
     <?
     //total renglon tiene la suma de los productos de los renglones
     $total_renglon=$resultado_renglon->fields["total_renglon"];
     $total_renglon=number_format($total_renglon,'2','.','');
     if ($resultado_licitacion->fields['id_moneda']==1) {
                  $subtotal_renglon=($total_renglon * $resultado_licitacion->fields['valor_dolar_lic'])/$ganancia;
                  $subtotal_renglon=ceil($subtotal_renglon);
                  echo "<td align='right' id='dato_p_unitario$i'> <b> \$ ".number_format($subtotal_renglon,2,'.','');
                  echo "</td>";
                  $total_cantidad_renglon=$resultado_renglon->fields['cantidad']*$subtotal_renglon;
                  echo "<td align='right' id='dato_p_total$i'> <b> \$ ".number_format($total_cantidad_renglon,2,'.','');
                  echo "</td>";
           } //del if
           else {
                $subtotal_renglon=$total_renglon /$ganancia;
                $subtotal_renglon=ceil($subtotal_renglon);
                echo "<td align='right' id='dato_p_unitario$i'> <b> U\$S ".number_format($subtotal_renglon,2,'.','');
                echo "</b></td>";
                $total_cantidad_renglon=$resultado_renglon->fields['cantidad']*$subtotal_renglon;
                echo "<td align='right' id='dato_p_unitario$i'> <b> U\$S ".number_format($total_cantidad_renglon,2,'.','');
			    echo "</b></td>";
                } //del else
  //en el renglon coloco unicamente el subototal osea el precio unitario falta multiplicarlo por la cantidad
  $sql="update pcpower_renglon set total = $subtotal_renglon where id_renglon = $id_renglon";
  sql($sql) or fin_pagina();
  echo "</tr>";
  $resultado_renglon->MoveNext();
  $i++;
  }
$db->CompleteTrans();
echo "</table>";


//query para buscar el simbolo de la moneda en la base de datos
if($filas_encontradas>0){
             $id_moneda=$resultado_licitacion->fields['id_moneda'];
             $query_moneda="SELECT simbolo from pcpower_moneda WHERE id_moneda=$id_moneda";
	     $query_moneda=sql($query_moneda) or fin_pagina();
             $moneda=$query_moneda->fields['simbolo'];
             }

//muestra el monto y nombre de la/s ofertas realizadas (si es que las hay).
//busco las ofertas en la base de datos.
$sql = " select * from pcpower_oferta_licitacion
         where id_licitacion=$id_licitacion
         order by id_oferta";
$resultado_oferta=sql($sql) or fin_pagina();

//consulta para poner un title que en la tabla que muestra las ofertas cargadas.
$totales=array();

//$resultado_renglones_oferta=sql($sql) or fin_pagina();
$filas_encontradas =$resultado_oferta->RecordCount();
echo "<table width='100%' align=center>";
if($filas_encontradas==0)
            $no_hay_ofertas=0;
            else
            $no_hay_ofertas=1;

for($i=0;$i<$filas_encontradas;$i++) {

   $id_oferta = $resultado_oferta->fields['id_oferta'];
   //calculo los totales por renglon
   $sql="select * from
        (
        select sum(renglon.total*renglon.cantidad)as total_renglon,id_renglon
        from pcpower_licitaciones.renglon
        join pcpower_elementos_oferta using (id_renglon)
        where id_oferta=$id_oferta group by id_renglon
        )
        as p
        join
        (
        select titulo,codigo_renglon,id_renglon
               from pcpower_renglon where id_licitacion=$id_licitacion
         ) as u using (id_renglon)";
   $resultado_renglones_oferta=sql($sql) or fin_pagina();

   $str_title="";
   $total=0;
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

//agregamos firmante de la licitacion
if (isset($_POST['firmante_text']))
    {
    $texto=$_POST['firmante_text'];
    $activo=$_POST['id_activo'];
    }
    else
    {
    $sql="select nombre,id_firmante_lic from pcpower_firmantes_lic where activo=1";
    $resultado_activo=sql($sql) or fin_pagina();
    $texto=$resultado_activo->fields['nombre'];
    $activo=$resultado_activo->fields['id_firmante_lic'];
    }

?>
<tr>
<td></td>
<td>

<input type="hidden" name="id_activo" value="<?=$activo;?>"></td>
<td><?/*<input type="text" name="firmante_text" value="<?=$texto;?>" style="border-style:none;background-color:'transparent'; font-weight: bold;">*/?>&nbsp;</td>
</tr>
<?
if(sizeof($totales)>0) $monto_ofertado=max($totales);
echo "<input type='hidden' name='monto_ofertado' value='$monto_ofertado'>";
echo "<input type='hidden' name='cantidad_renglones' value='$cantidad_filas'>";
echo "</table>";
} //del if de controla la cantidad de renglones y las ofertas
 else
 {
   echo "<input type='hidden' name='monto_ofertado' value='$monto_ofertado'>";
   echo "<table width=100% align=center";
   echo "<tr>";
   echo "<td align=center>";
     $datos_de="E PRESUPUESTO";
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
 <input type="submit" name="boton"  value="Agregar Renglon" <?=$candado?> style='width:120;'>
 </td>
 <td>
 <input type="submit" name="boton"  value="Modificar Renglon" style='width:120;' disabled>
 </td>
 <td>
 <!--<input type="submit" name="boton_duplicar"  value="Duplicar Renglon" style='width:120;' onClick="return confirm('ADVERTENCIA: se va a duplicar el renglon y sus datos');" disabled>-->
 &nbsp;
 </td>
 <td>
 <input type="submit" name="boton" value="Ver Descripciones" style='width:120;' disabled>
 </td>
  <td>
 <input type="submit" name="boton"  value="Eliminar Renglon" style='width:120;'  onClick="return confirm('ADVERTENCIA:Se va a eliminar el renglon y sus datos');" disabled>
 </td>
  <td>
   <?
      $datos_de="la Licitación";
   ?>
 <input type="submit" name="boton"  value="Terminar" <?=$candado?> style='width:120;'> <?/*onclick="if(document.all.monto_ofertado.value==''){alert('No se puede terminar <?=$datos_de?> porque aun no se ha cargado la oferta.');return false;}else return true;">*/?>
 </td>
</tr>
</table>
<hr>
<?
if (($_POST['producto']=="")&&(($_POST['boton']=="")|| ($_POST['boton']=="Eliminar Renglon") || ($_POST['boton']=='Actualizar Dolar y Ganancia') || ($_POST['boton']=='Actualizar  Ganancias') || ($_POST['boton']=='Avisar Descripcion') || ($_POST['boton']=='Terminar') || ($_POST['boton']=='Avisar Oferta')))
{
 $resultado_renglon->movefirst();
//Los datos de esta consulta la obtengo al principio de la pagina
$cantidad_renglones = $resultado_renglon->RecordCount();
$i =0;
//este if esta para que me pongo un cartel
if ($cantidad_renglones>=1) {
?>
 <table width=100% align=center>
  <tr><td align=center><b> RESUMEN DE LOS RENGLONES</td></tr>
 </table>
<?
}

while ($i<$cantidad_renglones) {
         $id_renglon=$resultado_renglon->fields['id_renglon'];
         $j=0;
         echo "<table bgcolor='#E9E9E9' width='100%' class='bordessininferior'>";
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
         echo "<table bgcolor='#F0F0F0' width='100%' id='tabla_productos_renglon$i' class='bordessinsuperior'>\n";
         echo "<tr bgcolor='$bgcolor2'>\n" ;
         echo "<td width='10%'> <b>\n" ;
         echo "Cantidad\n";
	 echo "</td>\n" ;
         echo "<td width='80%' > <b>\n";
         echo "Producto \n";
         if ($id_etap=$resultado_renglon->fields['id_etap'])
                {
		$titulo_etap= $resultado_renglon->fields['titulo_etap'];
    	        $title=(($resultado_renglon->fields['texto_etap'])?"title='".$resultado_renglon->fields['texto_etap']."'":"");
		$buffer="<font color=red style='cursor:hand' $title onclick=\"window.open('".encode_link('pcpower_ETAPS.php',array('id_etap'=>$id_etap))."','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=325');\" >$titulo_etap</font>\n";
       	        echo $buffer;
                }
        $title=(($resultado_renglon->fields['resumen'])?"title='".$resultado_renglon->fields['resumen']."'":"");
        //echo "<font color=blue style='cursor:hand' $title> onclick=\"window.open('".encode_link('pcpower_renglon_resumen.php',array('id_renglon'=>$id_renglon))."','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=450,height=200');\" > "Resumen del Renglón</font>";
        echo "</td>\n" ;
        echo "<td width='10%' id='td_precio_dolares' colspan=2> <b>\n" ;
        echo "Precio U\$S\n";
        echo "</td>\n" ;
        echo "</tr>\n" ;
        $suma_productos=0;
        $total_producto=0;

         $sql="select pcpower_producto.*,productos.desc_gral from pcpower_producto
               join productos using(id_producto)
               where pcpower_producto.id_renglon = $id_renglon ";
        
         $desc_renglon=sql($sql) or fin_pagina();
         $cantidad_productos = $desc_renglon->RecordCount();
        $garantia="";
        while ($j<$cantidad_productos){
             if ($desc_renglon->fields['tipo']!='garantia')
	        {
                                         echo "<tr bgcolor='$bgcolor_out'>\n";
                                         echo "<td> <b>\n";
                                         echo $desc_renglon->fields['cantidad'];
                                         echo "</td>\n";
                                         echo "<td> <b>\n";
                                         echo $desc_renglon->fields['desc_gral'];
                                         echo "</td>\n";
                                         if($desc_renglon->fields['desc_precio_licitacion']!=null || $desc_renglon->fields['desc_precio_licitacion']!="")
                                            $color="bgcolor=$bgcolor3";
                                            else
                                            $color="";
                                       $desc_aux=$desc_renglon->fields['desc_precio_licitacion'];
                                       $id_aux=$resultado_renglon->fields['id_renglon'];
                                       $prod_aux=$desc_renglon->fields['tipo'];
                                         ?>
                                        <td align='right' <?=$color?> <?if($color!=""){?>title='<?=$desc_renglon->fields['desc_precio_licitacion']?>' onclick="window.open('<?=encode_link('pcpower_renglon_comentario_mostrar.php',array('var'=>$desc_aux,'id'=>$id_aux,'producto'=>$prod_aux))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');"<?}?>>
                                        </b>
                                        <?
                                         $cantidad=$desc_renglon->fields['cantidad'];
                                         $precio=$desc_renglon->fields['precio_licitacion'];
                                         $total_producto=$precio*$cantidad;
                                         echo number_format($total_producto,2,'.','');
                                         echo "</b></td>\n";
                                         //generamos link para ver historial de comentarios
                                         $link=encode_link("pcpower_historial_comentarios.php",array("id_producto"=>$desc_renglon->fields['id_producto']));
                                        ?>
                                        <td><a href='<?=$link;?>' target="_blank">H</a></td>
                                        </tr>
                                        <?
                                        }
                                             else {
                                                  $garantia=$desc_renglon->fields['desc_gral'];
                                                  $total_producto=0;
                                                  } //del else
                                   $suma_productos+=$total_producto;
                                   $j++;
                                   $desc_renglon->MoveNext();
                               }//del while   j<$cantidad_productos
 echo "<tr  bgcolor='$bgcolor_out'>\n";
 echo "<td ><b>\n";
 echo "Garantia:\n";
 echo "</td>\n";
 echo "<td colspan=3> <b>\n";
 echo "$garantia";
 echo "</td>\n";
 echo "</tr>\n";
 echo "<tr bgcolor='$bgcolor2'>\n";
 echo "<td id='td_total_renglon$i'> <font color='red'><b>\n";
 echo "Total\n";
 echo "</td>\n";
 echo "<td colspan='3' align='right' id='input_precio_dolares$i'>  <font color='#E0E0E0'> <b>\n";
 echo "<font color='red'> U\$S ".number_format($suma_productos,'2','.','')."</font>" ;
 echo "</td>\n";
 echo "</tr>\n";
 echo "</table>\n";
 echo "<br>\n";
 echo "<br>\n";
 $resultado_renglon->MoveNext();
 $i++;
}//del while
} //del if prinicpal
?>
</FORM>
<script>
if(document.all.ganancia_oculta.value==1)
 ocultar_monetarios();
</script>
<?
fin_pagina()
?>