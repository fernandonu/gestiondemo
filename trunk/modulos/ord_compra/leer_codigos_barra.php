<?
/*
Autor: MAC
Fecha: 17/11/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.12 $
$Date: 2006/02/17 22:02:37 $

*/
require_once("../../config.php");

/*********************************************************************
Este Archivo se usa desde ord_compra_fin.php y desde rma_historial.php
**********************************************************************/
if($_POST["guardar"]=="Guardar")
{$db->StartTrans();
 $error_cb_duplicado="";
 $id_fila=$_POST["id_fila"];
// $link_cb=encode_link("leer_codigos_barra.php",array("total_comprado"=>$_POST['total_comprado'],"producto_nombre"=>$_POST['nombre_producto'],"id_producto"=>$_POST['id_producto'],"nro_orden"=>$_POST['nro_orden'],"nro_rma"=>$_POST['nro_rma']));

 //traemos la info de cada evento de recepcion de productos para la fila, y los codigos de barra ya cargados
 //para cada log
 $query="select id_log_recibido,cant,producto_especifico.descripcion as producto_nombre,id_prod_esp
	         from compras.log_rec_ent join general.producto_especifico using(id_prod_esp)
		     join compras.recibido_entregado using(id_recibido)
	         where id_fila=$id_fila and ent_rec=1 and recepcion_confirmada=1
	        ";
 $logs_recepciones=sql($query,"<br>Error al traer los datos del log de recepciones<br>") or fin_pagina();
 //por cada log de recepcion, insertamos los nuevos codigos de barra ingresados
 while (!$logs_recepciones->EOF && $error_cb_duplicado=="")
 {
     $id_log_recibido=$logs_recepciones->fields["id_log_recibido"];
     $id_prod_esp=$logs_recepciones->fields["id_prod_esp"];
     $primer_nuevo=$_POST["primer_nuevo_cb_$id_log_recibido"];
     $total_comprado=$_POST["total_comprado_$id_log_recibido"];
     //le ponemos cartel para los casos que no guarde ningun Codigo de barra
     $msg="<center><b>Los datos se cargaron con éxito</b></center>";
	 for($j=$primer_nuevo;$error_cb_duplicado=="" && $j<$total_comprado;$j++)
	 {
	  //como guardamos un codigo de barra al menos, reseteamos el cartel, que se seteara mas abajo
	  $msg="";
	  $codigo_actual=$_POST['cod_barra_'.$id_log_recibido.'_'.$j];
	  if($codigo_actual!="")
	  {
	   //controlamos si el codigo de barras fue insertado o no
	   $query="select codigo_barra from codigos_barra where codigo_barra='$codigo_actual'";
	   $esta_cb=sql($query,"<br>Error al buscar CB ya insertado") or fin_pagina();

	   if($esta_cb->fields["codigo_barra"]!="")
	   {$error_cb_duplicado="<font color=red>
	                        <b>
	                        EL CÓDIGO DE BARRAS INGRESADO: $codigo_actual, YA EXISTE\n
                            </b></font>\n
	                        ";
	    $cb_con_error=$codigo_actual;
	   }//de if($esta_cb->fields["codigo_barra"]!="")
	   else
	   {$cb_con_error="";
	   	$query="insert into codigos_barra (codigo_barra,id_prod_esp,codigo_padre)
	          values('$codigo_actual',$id_prod_esp,'$codigo_actual')";
	    sql($query,"<br>Error al insertar CB<br>") or fin_pagina();

	    $fecha_hoy=date("Y-m-d H:i:s");
	    if($_POST["nro_orden"])
	    {$oc=$_POST["nro_orden"];
	     $tipo="Producto Ingresado mediante la OC Nº $oc";
	    }
	    else
	     $oc="null";
	    if($_POST["nro_rma"])
	    {$rma=$_POST["nro_rma"];
	     //consultamos datos de ese rma, para poder ponerlo en el log
	     $query="select nrocaso,nro_ordenc,nro_ordenp from info_rma where id_info_rma=$rma";
	     $datos_rma=sql($query,"<br>Error al traer datos <br>") or fin_pagina();

	     $tipo="Producto Ingresado mediante el RMA con Nº de Orden de Compra ".$datos_rma->fields["nro_ordenc"]." ";
	     if ($datos_rma->fields["nrocaso"])
	      $tipo.="y el Nº de C.A.S ".$datos_rma->fields["nrocaso"];
	     elseif ($datos_rma->fields["nro_ordenp"])
	      $tipo.="y Nº de Orden de Producción ".$datos_rma->fields["nro_ordenp"];
	     }
	     else
	      $rma="null";
	     $query="insert into log_codigos_barra(codigo_barra,usuario,fecha,tipo,nro_orden,id_info_rma)
	            values('$codigo_actual','".$_ses_user["name"]."','$fecha_hoy','$tipo',$oc,$rma)";
	     sql($query,"<br>Error al insertar log de codigos de barra<br>") or fin_pagina();
	     $error_cb_duplicado="";

	     //insertamos la relacion entre el log de recepcion y el codigo de barras
	     //(Los campos id_garantia y nro_despacho se actualizan mas abajo)
	     $query="insert into adicional_recepcion (id_log_recibido,codigo_barra) values ($id_log_recibido,'$codigo_actual')";
	     sql($query,"<br>Error al insertar el adicional de la recepcion<br>") or fin_pagina();

	    }//de if($esta_cb->fields["codigo_barra"]!="")
	   }//de if($codigo_actual!="")
	 }//de for($j=$_POST["primer_nuevo_cb"];$j<$_POST["total_comprado"];$j++)

	 //actualizamos el nro de despacho para cada codigo de barra atado al log de recepcion
	 //(tantos los que se acaban de cargar en el for de arriba, como los que ya se habian cargado anteriormente)
	 $nro_despacho=$_POST["nro_despacho_$id_log_recibido"];
	 if($_POST["garantia_$id_log_recibido"]!=-1)
	   $id_garantia=$_POST["garantia_$id_log_recibido"];
	 else
	   $id_garantia="NULL";
	 $query="update log_rec_ent set nro_despacho='$nro_despacho',id_garantia=$id_garantia where id_log_recibido=$id_log_recibido";
	 sql($query,"<br>Error al actualizar los despachos y garantias de los codigos de barra<br>") or fin_pagina();

  $logs_recepciones->MoveNext();
 }//de while(!$logs_recepciones->EOF)
 if($error_cb_duplicado=="")
 { $db->CompleteTrans();
   if($msg=="")
     $msg="<center><b>Los códigos de barra ingresados se cargaron con éxito</b></center>";
 }
 else
 {
  //forzamos el rollback porque se encontroun Codigo de Barra duplicado
  $db->CompleteTrans(false);
  $msg=$error_cb_duplicado;
 }

}//DE if($_POST["guardar"]=="Guardar")

if($_POST["borrar"]=="Borrar")
{$db->StartTrans();
 //borramos el codigo de barras que indica el hidden
 $a_borrar=$_POST["cb_a_borrar"];

 $query="delete from log_codigos_barra where codigo_barra='$a_borrar'";
 sql($query,"<br>Error al borrar el log del codigo de barras: $a_borrar<br>") or fin_pagina();

 $query="delete from adicional_recepcion where codigo_barra='$a_borrar'";
 sql($query,"<br>Error al borrar la informacion de recepcion del codigo de barras: $a_borrar<br>") or fin_pagina();

 $query="delete from codigos_barra where codigo_barra='$a_borrar'";
 sql($query,"<br>Error al borrar el codigo de barras: $a_borrar<br>") or fin_pagina();
 $db->CompleteTrans();
 $msg="<center><b>El codigo de barra $a_borrar fue borrado con éxito</b></center>";
}


/*****************************************************************************
 Genera el combo desplegable de las garantias
******************************************************************************/
function generar_combo_garantia($id_log_recibido,$id_garantia="")
{
	//traemos los datos de la garantia
	$query="select id_garantia,duracion from compras.garantia order by duracion";
	$garantias=sql($query,"<br>Error al traer las garantias de productos<br>") or fin_pagina();
	?>
	<select name="garantia_<?=$id_log_recibido?>">
	 <option value="-1">Seleccione...</option>
	<?
	while (!$garantias->EOF)
	{?>
	 <option value="<?=$garantias->fields["id_garantia"]?>" <?if($id_garantia==$garantias->fields["id_garantia"]) echo "selected"?>>
	  <?=$garantias->fields["duracion"]?> Meses
	 </option>
	 <?
	 $garantias->MoveNext();
	}//de while(!$garantias->EOF)
	?>
	</select>
	<?
}//de function generar_combo_garantia($id_log_recibido)


echo $html_header;

//estas variables tienen que tomar los valores desde ord_compra_fin.php
$producto_nombre=$parametros["producto_nombre"] or $producto_nombre=$_POST["producto_nombre"];
$total_comprado=$parametros["total_comprado"] or $total_comprado=$_POST["total_comprado"];
$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];
$nro_rma=$parametros["nro_rma"] or $nro_rma=$_POST["nro_rma"];
$id_fila=$parametros["id_fila"] or $id_fila=$_POST["id_fila"];

//averiguamos la fecha de recepcion de la OC, para ver que archivo mostramos
$query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de recepcion'";
$fff=sql($query,"<br>Error al traer la fecha de recepcion de la OC<br>") or fin_pagina();
$fecha_recepcion=$fff->fields["fecha"];

$fecha_subida_gestion3="2006-01-04 00:00:00";
//si la fecha de recepcion de la OC es menor que la fecha en que se subio el gestion3 se muestra la pagina vieja
//de codigos de barra para que se pueda ver la informacion ya cargada, sino se muestra la nueva version
if ($fecha_recepcion !="" && compara_fechas($fecha_recepcion,$fecha_subida_gestion3)<=0)
{//FALTA ARREGLAR ALGUNAS COSAS PARA QUE MUESTRE BIEN LA INFO DEL PRODUCTO ASOCIADO A LOS CODIGOS DE BARRA QUE SE MUESTRAN
	include("leer_codigos_barra_gestion2.php");
}
else
{
	//traemos la info de cada evento de recepcion de productos para la fila, y los codigos de barra ya cargados
	//para cada log
	if($nro_orden)
	 $query="select id_log_recibido,cant,producto_especifico.descripcion as producto_nombre,log_rec_ent.fecha,nro_despacho,id_garantia
	         from compras.log_rec_ent join general.producto_especifico using(id_prod_esp)
		     join compras.recibido_entregado using(id_recibido)
	         where id_fila=$id_fila and ent_rec=1 and recepcion_confirmada=1
	        ";
	elseif($nro_rma)
	 $query="select codigo_barra from codigos_barra join log_codigos_barra using(codigo_barra)
	         where id_producto=$id_producto and id_info_rma=$nro_rma and tipo ilike '%Ingresado mediante el RMA%'";
	else
	 die("Falta Nro Orden o Nro de RMA.");
	$logs_recepciones=sql($query,"<br>Error al seleccionar info de codigos de barra<br>") or fin_pagina();

	echo $msg;
	?>
	<script>
	function alProximoInput(elmnt,content,next,index)
	{
	  var boton;
	  var posfijo=new String();

	  if (content.length==elmnt.maxLength)
		{

		  if (typeof(next)!="undefined")
			{
			  next.focus();
			}
		  else
		   document.all.guardar.focus();

		  //obtenemos el posfijo del nombre del campo de codigo de barra, para habilitar el boton de autocompletar correspondiente
		  posfijo=elmnt.name;
		  posfijo=posfijo.substr(10,posfijo.length-10);
	      if(typeof(boton=eval("document.all.autocompletar_consecutivos_"+posfijo))!="undefined")
	      {
	         boton.style.visibility='visible';
	      }

		}//de if (content.length==elmnt.maxLength)

	}//de function alProximoInput(elmnt,content,next)


	function habilitar_deshabilitar_ingreso_serial(valor_checked,id_log_recibido)
	{
	 var i=eval("document.all.primer_nuevo_cb_"+id_log_recibido+".value");
	 var cb_text;

	 while(typeof(eval("document.all.cod_barra_"+id_log_recibido+"_"+i))!="undefined")
	 {
	  cb_text=eval("document.all.cod_barra_"+id_log_recibido+"_"+i);
	  if(valor_checked==1)
	   cb_text.maxLength=100;
	  else
	   cb_text.maxLength=9;

	  i++;
	 }

	}//de function habilitar_ingreso_serial()

	</script>
	<script src="funciones.js"></script>
	<form name="form1" method="POST" action="leer_codigos_barra.php">

	 <input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
	 <input type="hidden" name="id_fila" value="<?=$id_fila?>">
	 <input type="hidden" name="nro_rma" value="<?=$nro_rma?>">
	 <input type="hidden" name="producto_nombre" value="<?=$producto_nombre?>">
	 <table width="100%" align="center" border="1">
	  <tr>
	   <td id="ma">
	    Ingrese los números de códigos de barra para los productos recibidos para la fila: <br>"<?=$producto_nombre?>"
	   </td>
	  </tr>
	  <tr>
	   <td>
	    <table width="100%" class="bordes">
	     <tr id=mo>
	      <td>
	       Productos recibidos
	      </td>
	     </tr>
	  <?
	  $acum_cb_ingresados=0;
	  while (!$logs_recepciones->EOF)
	  {
	  	  //por cada log de recepcion, traemos los datos adicionales (codigos de barra, nro de despacho y garantias
	  	  $query="select codigo_barra from adicional_recepcion where id_log_recibido=".$logs_recepciones->fields["id_log_recibido"];
	  	  $codigos_guardados=sql($query,"<br>Error al traer los codigos de barra cargados <br>") or fin_pagina();

	  	  $total_recibido_log=$logs_recepciones->fields["cant"];
	  	  $id_log_recibido=$logs_recepciones->fields["id_log_recibido"];
	  	  $nro_despacho=$_POST["nro_despacho_$id_log_recibido"] or $nro_despacho=$logs_recepciones->fields["nro_despacho"];
	  	  $id_garantia=$_POST["garantia_$id_log_recibido"] or $id_garantia=$logs_recepciones->fields["id_garantia"];
	  	  ?>
	  	  <tr>
	  	   <td>
	  	    <table width="100%" align="center" class="bordes">
	  	     <tr id="sub_tabla">
	  	      <td>
	  	        <?=$logs_recepciones->fields["producto_nombre"]?>
	  	      </td>
	  	     </tr>
	  	     <tr>
	  	      <td>
	  	       <table width="100%" class="bordes">
		  	     <tr>
		  	      <td width="35%">
		  	        <b>Fecha Recepción</b>
		  	      </td>
		  	      <td width="65%">
		  	        <b><?=fecha($logs_recepciones->fields["fecha"])?></b>
		  	      </td>
		  	     </tr>
	             <tr>
		          <td>
		           <b>Nº de Despacho</b>
		          </td>
		          <td>
		           <input type="text" name="nro_despacho_<?=$id_log_recibido?>" value="<?=$nro_despacho?>">
		          </td>
		         </tr>
		          <td>
		           <b>Garantía</b>
		          </td>
		          <td>
                    <?generar_combo_garantia($id_log_recibido,$id_garantia)?>
		          </td>
		         </tr>
			  	 <?
			     if(permisos_check("inicio","permiso_ingresar_serial_recepcion"))
			     {?>
			      <tr>
			       <td colspan="2">
			         <input type="checkbox" name="permitir_seriales_<?=$id_log_recibido?>" value="1" onclick="habilitar_deshabilitar_ingreso_serial(this.checked,<?=$id_log_recibido?>);"> <b>Ingresar Nº Serial</b>
			       </td>
			      </tr>
			     <?
			     }//de if(permisos_check("inicio","permiso_ingresar_serial_recepcion"))
			     ?>
		       </table>
		      </td>
		     </tr>
		     <tr>
		      <td>
		        &nbsp;&nbsp;&nbsp;&nbsp;
		        <font color="Blue"><b>Códigos de Barra</b></font>
		      </td>
		     </tr>
	      <?
		  $io=0;
		  while(!$codigos_guardados->EOF)
		  {
		  	$nombre_campo_cb="cod_barra_".$id_log_recibido."_log_".$io;
		  	?>
		  	<tr>
		    <td>
		     &nbsp;&nbsp;&nbsp;&nbsp;<!--para acomodar los campos correctamente-->
		     <input type="text" name="<?=$nombre_campo_cb?>" maxlength="9" tabindex="<?=$io+1?>" size="30" readonly value="<?=$codigos_guardados->fields["codigo_barra"]?>" onkeyup="toUnicode(this,this.value,<?=$nombre_campo_cb?>);" >
		     <input type="submit" name="borrar" value="Borrar" style="width:63" onclick="
		  																			if(confirm('Se borrará el código de barra <?=$codigos_guardados->fields["codigo_barra"]?> del sistema.\n¿Está seguro?'))
		  																			{document.all.cb_a_borrar.value='<?=$codigos_guardados->fields["codigo_barra"]?>';
		  																			 return true;
		  																			}
		  																			else
		  																			 return false;
		  	"
		    >
		    </td>
		   </tr>
		   <?
		   $io++;
		   $codigos_guardados->MoveNext();
		  }//de while(!$codigos_guardados->EOF)
		  //guardamos el número a partir del cuál debemos empezar a insertar
		  //los nuevos códigos de barra ingresados (el resto de los números
		  //ya fueron ingresados antes)
		  $foco=$io;
		  //acumulamos cuantos codigos de barra se recibieron para cada log de recepcion
		  $acum_cb_ingresados+=$io;
		  ?>
		  <input type="hidden" name="primer_nuevo_cb_<?=$id_log_recibido?>" value="<?=$io?>">

		  <?
		  for($io;$io<$total_recibido_log;$io++)
		  {?>
		   <tr>
		    <td>
		     <?
               //predecimos cual es el proximo campo de codigos de barra

               //si todavia quedan codigos de barra para generar para este log de recepcion, el proximo campo
               //sigue teniendo el id de log del actual
               if(($io+1)<$total_recibido_log)
                 $third_par="cod_barra_".$id_log_recibido."_".($io+1);
               //sino, avanzamos al proximo log_recepciones para ver si aun quedan productos por generar.
               else
               {
                 $logs_recepciones->MoveNext();
                 //si aun quedan logs de recepcion por generar, el proximo campo de la lista sera el primero
                 //del siguiente log de recepcion
                 if(!$logs_recepciones->EOF)
                  $third_par="cod_barra_".$logs_recepciones->fields["id_log_recibido"]."_0";
                 else //sino, lo proximo es el boton de guardar
                  $third_par="document.all.guardar";
               	 $logs_recepciones->Move($logs_recepciones->CurrentRow()-1);
               }


		     if($_POST["cod_barra_$id_log_recibido"."_".$io])
		     {$valor_cb=$_POST["cod_barra_$id_log_recibido"."_".$io];
		      if($valor_cb==$cb_con_error)
		       $estilo_error="style='color:red'";
		      else
		       $estilo_error="";
		     }
		   	 else
		   	 {$valor_cb="";
		   	  $estilo_error="";
		   	 }

		   	 if($io<$total_recibido_log-1)
		   	 {?>
		      <input type="button" name="autocompletar_consecutivos_<?=$id_log_recibido?>_<?=$io?>" value="V" title="Autocompletar codigos de barra consecutivos" onclick="autocompletar_codigos_barra(document.all.cod_barra_<?=$id_log_recibido?>_<?=$io?>.value,'cod_barra_',<?=$io+1?>,<?=$id_log_recibido?>)" style="visibility:hidden">
		     <?
		   	 }
		   	 else
		   	  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
		     ?>
		     <input type="text" maxlength="9" tabindex="<?=$io+1?>" name="cod_barra_<?=$id_log_recibido?>_<?=$io?>" value="<?=$valor_cb?>" <?=$estilo_error?> size="30" onkeyup="alProximoInput(this,this.value,<?=$third_par?>,<?=$io?>);">
		     <input type="button" name="limpiar_<?=$id_log_recibido?>_<?=$io?>" value="Limpiar" onclick="document.all.cod_barra_<?=$id_log_recibido?>_<?=$io?>.value=''">
		    </td>
		   </tr>
		  <?
		  }//de for($io;$io<$total_recibido_log;$io++)
		  ?>
		    </table>
		   </td>
		  </tr>
		  <input type="hidden" name="cant_vacios_<?=$id_log_recibido?>" value="<?=$io-$foco?>">
		  <input type="hidden" name="total_comprado_<?=$id_log_recibido?>" value="<?=$total_recibido_log?>">
		  <?

	    $logs_recepciones->MoveNext();
	   }//de while(!$logs_recepciones->EOF)

	   if($logs_recepciones->RecordCount()==0)
	   {
	    ?>
	     <tr>
	      <td align="center">
	       <H5>NO SE HAN CONFIRMADO RECEPCIONES DE PRODUCTOS PARA ESTA FILA</H5>
	      </td>
	     </tr>
	    <?
	    $disabled_guardar="disabled";
	   }//de if($logs_recepciones->RecordCount()==0)
	   ?>
	 </table>
	 <input type="hidden" name="cb_a_borrar" value="">
	 <table width="100%" align="center">
	  <tr>
	   <td align="center">
	    <input type="submit" name="guardar" value="Guardar" <?//if($acum_cb_ingresados>=$total_comprado) echo "disabled"?> <?=$disabled_guardar?>>
	   </td>
	   <td align="center">
	    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
	   </td>
	  </tr>
	 </table>
	 <script>
	  if(typeof(document.all.cod_barra_<?=$foco?>)!="undefined")
	   document.all.cod_barra_<?=$foco?>.focus();
	 </script>
	</from>
	</body>
	</html>
<?
}//de if(compara_fechas($fecha_recepcion,$fecha_subida_gestion3)<=0)
fin_pagina();
?>