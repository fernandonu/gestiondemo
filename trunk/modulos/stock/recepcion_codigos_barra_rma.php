<?
/*
Autor: MAC
Fecha: 19/05/06

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2006/06/23 20:03:46 $

*/
require_once("../../config.php");


$desde_pagina=$parametros["desde_pagina"] or $desde_pagina=$_POST["desde_pagina"];

//estas variables tienen que tomar los valores desde ord_compra_fin.php
$producto_nombre=$parametros["producto_nombre"] or $producto_nombre=$_POST["producto_nombre"];
$total_comprado=$parametros["total_comprado"] or $total_comprado=$_POST["total_comprado"];
$total_recibido=$parametros["total_recibido"] or $total_recibido=$_POST["total_recibido"];
$id_prod_esp=$parametros["id_prod_esp"] or $id_prod_esp=$_POST["id_prod_esp"];
$id_proveedor_rma=$parametros["id_proveedor_rma"] or $id_proveedor_rma=$_POST["select_proveedor_rma"];
$id_info_rma=$parametros["id_info_rma"] or $id_info_rma=$_POST["id_info_rma"];
$id_deposito=$parametros["id_deposito"] or $id_deposito=$_POST["id_deposito"];

//si el parametro recargar esta en 1, entonces tomamos el id del producto elegido y lo enviamos por POST
if($parametros["recargar"]==1)
{
	$link_cb=encode_link("recepcion_codigos_barra_rma.php",array("total_comprado"=>$total_comprado,"total_recibido"=>$total_recibido,"producto_nombre"=>"$producto_nombre","id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma,"id_prod_esp"=>$id_prod_esp));
	?>
	<form method="POST" action="<?=$link_cb?>" name="reenviar">
	 <input type="hidden" name="id_prod_esp" value="">
	</form>
	<script>
	 document.all.id_prod_esp.value=window.opener.document.all.id_prod_rec.value;
	 document.reenviar.submit();
	</script>
	<?
}

if($_POST["guardar"]=="Guardar")
{
	 $db->StartTrans();
	 $fecha_hoy=date("Y-m-d H:i:s",mktime());
	 $cant_insertada=0;
	 $cb_insertados=array();

	 //registramos la entrega de los productos con el codigo de barra indicado en el campo de texto
	 for($j=$_POST["primer_nuevo_cb"];!$error_cb && $j<$total_comprado;$j++)
	 {
	  $cb_insertar=$_POST['cod_barra_'.$j];
	  if($cb_insertar!="" && $cb_insertar!="Entregado sin CB")
	  {
	   //controlamos que el codigo de barras ingresado, no exista. Si existe, damos cartel de error
	   $query="select codigo_barra,id_prod_esp from codigos_barra where codigo_barra='$cb_insertar'";
       $hay=sql($query,"<br>Error al revisar si el codigo de barras Nº $cb_insertar existe<br>") or fin_pagina();
	   if($hay->fields["codigo_barra"]=="")
	   {

	  	 if(!$error_cb)
	  	 {
          $tipo="Producto ingresado mediante el RMA Nº $id_info_rma por cambio de parte dañada";

	       	//insertamos el codigo de barras en la BD
	       	$query="insert into general.codigos_barra (codigo_barra,codigo_padre,id_prod_esp)
	       			values('$cb_insertar','$cb_insertar',$id_prod_esp)";
	       	sql($query,"<br>Error al insertar el codigo de barras $cb_insertar<br>") or fin_pagina();

	       	$query="insert into general.log_codigos_barra(codigo_barra,usuario,fecha,tipo)
	              values('$cb_insertar','".$_ses_user["name"]."','$fecha_hoy','$tipo')";
	        sql($query,"<br>Error al insertar el log del codigo de barras<br>") or fin_pagina();

	        $cb_insertados[$cant_insertada]=$cb_insertar;

	        $cant_insertada++;
	  	  }//de if(!$error_cb)
	     }//de if($hay->fields["codigo_barra"]!="")
	     else
	     {$error_cb="<font color=red><b>-----------------------------------------<BR>\n
	                        El código de barras: '$cb_insertar'<br>ya está cargado en el sistema.<br>\n
	                        <BR>-----------------------------------------<BR><BR></b></font>\n
	                        ";
	      $cb_con_error=$cb_insertar;
	     }
	  }//de if($cb_insertar!="" && $cb_insertar!="Entregado sin CB")
	 }//de for($j=$_POST["primer_nuevo_cb"];$j<$_POST["total_comprado"];$j++)

	 if(!$error_cb && $cant_insertada>0)
	 {
	   	  //una vez que se insertan los codigos de barra, insertamos o actualizamos
	      //en la tabla rma_recepcion, la cantidad entregada, y el log correspondiente
         $query="select rma_recepcion.id_rma_recepcion,rma_recepcion.cantidad
                 from stock.rma_recepcion where id_info_rma=$id_info_rma";
	      $recepcion=sql($query,"<br>Error al traer las recepciones hechas previamente para el RMA<br>") or fin_pagina();
	      $id_recibido=$recepcion->fields["id_rma_recepcion"];

	      //controlamos que la cantidad recibida + la que se va a recibir, sea menor que la cantidad comprada
	      $ya_recibido=($recepcion->fields["cantidad"])?$recepcion->fields["cantidad"]:0;
	      if($ya_recibido+$cant_insertada>$total_comprado)
	      {echo "Cantidad de RMA para esta fila: $total_comprado<br>Cantidad ya recibida para esta fila: $ya_recibido<br>Cantidad que se intenta recibir: $cant_insertada<br>";
	       $error_cb1="<font color='red'>\n
	                     LA CANTIDAD RECIBIDA SUPERA LA DEL RMA. NO SE PUEDE RECIBIR ESTA CANTIDAD.
	                   </font>\n
	                        ";
	       die($error_cb1);
	      }


		  include_once("../stock/funciones.php");
          guardar_recepcion($id_info_rma,$cant_insertada,$id_prod_esp,$cb_insertados);

          $comentario="Ingreso a Stock generado por recepción de productos en RMA Nº $id_info_rma para cambio de partes";
		  //traemos el id del tipo de movimiento de stock para indicar la baja del producto en RMA
		  $query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Ingreso a Stock por recepción en RMA'";
		  $id_tipo_log=sql($query,"<br>Error al traer el id del tipo de movimento<br>") or fin_pagina();

		  if($id_tipo_log->fields["id_tipo_movimiento"]!="")
				$id_tipo_movimiento=$id_tipo_log->fields["id_tipo_movimiento"];
		  else
				die("<br>Error Interno rmacb120: No se pudo determinar el tipo de movimiento en stock. Contacte a la División Software<br>");

          agregar_stock($id_prod_esp,$cant_insertada,$id_deposito,$comentario,$id_tipo_movimiento);

          $id=array();
          $id[0]=$id_info_rma;
          //solo cuando la cantidad del RMA es igual que la recibida pasamos el RMA a historial, sino se mantiene
          //en el estado actual
          if($ya_recibido+$cant_insertada==$total_comprado)
          {
    	  	if (eliminar_rma($id,'Baja por cambio por nueva'))
       			$msg='La Baja se realizó con exito';
       	    else
       	  		$error_cb='Error al realizar la baja de RMA';
          }//de if($ya_recibido+$cant_insertada==$total_comprado)

	 }//de if(!$error_cb && $cant_insertada>0)


	 if(!$error_cb)
	 {$db->CompleteTrans();
	  $link_detalle =encode_link("stock_rma.php",array("id_info_rma"=>$id_info_rma));
	  echo "<script>window.opener.location.href='$link_detalle';window.close();</script>";
	  echo "<center><b>Los códigos de barra se entregaron con éxito</b></center>";
	 }
	 else
	  //esto obliga a hacer un rollback, aun si no ocurrieron errores
	  $db->CompleteTrans(false);
}//DE if($_POST["guardar"]=="Guardar")

if($_POST["borrar"]=="Borrar")
{
 include_once("../stock/funciones.php");

 $fecha_hoy=date("Y-m-d H:i:s",mktime());

 $db->StartTrans();
 //agregamos los codigos de barra seleccionados con los checkbox para borrarlos
 $ind=0;$borrar_cb=array();
 while ($ind<$_POST["primer_nuevo_cb"])
 {
   //si el checkbox esta checkeado, borramos el codigo de barra
   if($_POST["borrar_$ind"]==1)
   	$borrar_cb[sizeof($borrar_cb)]="'".$_POST["cod_barra_$ind"]."'";
   $ind++;
 }//de while ($ind<$_POST["primer_nuevo_cb"])

  //lo pasamos a string para usarlo en la consultas para borrar
   $a_borrar=implode(",",$borrar_cb);
   $cant_borrar=sizeof($borrar_cb);

   $query="delete from log_codigos_barra where codigo_barra in($a_borrar) and tipo ilike '%entregado%$titulo_pagina Nº $id_movimiento%'";
   sql($query,"Error al borrar el log del codigo de barras") or fin_pagina();

   //borramos el codigo de barra de la tabla codigos_barra_entregados
   $query="delete from codigos_barra_entregados where codigo_barra in($a_borrar) and id_detalle_movimiento=$id_detalle_movimiento";
   sql($query,"<br>Error al borrar de la tabla de cb entregados<br>") or fin_pagina();


   //reducimos en uno la cantidad entregada, por el cb borrado
   $query="select id_recibidos_mov from recibidos_mov where id_detalle_movimiento=$id_detalle_movimiento and ent_rec=0";
   $recibido_mov_id=sql($query,"<br>Error al consultar el id de recibidos<br>") or fin_pagina();
   $id_recs_mov=$recibido_mov_id->fields["id_recibidos_mov"];

   $query="update recibidos_mov set cantidad=cantidad-$cant_borrar where id_recibidos_mov=$id_recs_mov";
   sql($query,"<br>Error al actualizar la cantidad de recibidos<br>") or fin_pagina();

   //insertamos en el log este hecho
   $query_ins="insert into log_recibidos_mov(id_recibidos_mov,usuario,fecha,cantidad_recibida,tipo)
	            values($id_recs_mov,'".$_ses_user["name"]."','$fecha_hoy',-$cant_borrar,'borrado de CB')";
   sql($query_ins,"<br>Error al insertar el log de recibido<br>") or fin_pagina();

   //si es pedido de material debemos descontar del stock de produccion el producto que se esta eliminando
   if($es_pedido_material && $id_licitacion!="")
   {
   	   /********************************************************************
	    Reducimos en uno la cantidad de ese producto en stock de produccion
	   *********************************************************************/
	   //seleccionamos la informacion de la tabla en_produccion para actualizar la entrada correspondiente
	   $query="select en_produccion.cantidad,id_en_stock,id_en_produccion
	           from stock.en_produccion join stock.en_stock using(id_en_stock)
	           where id_prod_esp=$id_prod_esp and id_licitacion=$id_licitacion
	          ";
	   $d_prod=sql($query,"<br>Error al traer datos del producto en stock de produccion<br>") or fin_pagina();
	   $cant_en_prod=$d_prod->fields["cantidad"];
	   $id_en_stock=$d_prod->fields["id_en_stock"];

	   //si la cantidad en stock de produccion para este producto es menor o igual que cero damos cartel de error, porque
	   //no se puede descontar de produccion si la cantidad actual es cero.
	   if($cant_en_prod<=0)
	    	die("Error Interno: La cantidad en stock de produccion para el producto $producto_nombre es menor o igual que cero. Contacte a la División Software<br>");
	   else
	   {
	   		//sino, descontamos de stock en produccion, para la licitacion $id_licitacion,
	   		//ese producto cuyo codigos de barra se estan eliminando
	   		$comentario="Se eliminaron códigos de barra que se habían entregado para el $titulo_pagina Nº $id_movimiento";
	   		descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cant_borrar,$comentario);
	   }

      }//de if($es_pedido_material)

      /*********************************************************************************
       Volvemos a agregar a stock reservado los productos correspondiente a los CB eliminados
      **********************************************************************************/
      //traemos el id del tipo de movimiento a realizar: Cancelación de Entrega de Productos para PM o MM
	  $query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Cancelación de Entrega de Productos para PM o MM'";
      $tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();

      if($tipo_mov->fields["id_tipo_movimiento"]!="")
		  $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];
	  else
		  die("Error Interno ECB233: no se pudo determinar el tipo de movimiento de stock. Consulte a la División Software<br>");

      //traemos el id del tipo de reserva que vamos a realizar
      if($es_pedido_material)
       $nombre_reserva="Reserva Para Pedido de Material";
      else
       $nombre_reserva="Reserva Para Movimiento de Material";

      $query="select id_tipo_reserva from stock.tipo_reserva where nombre_tipo='$nombre_reserva'";
      $tipo_res=sql($query,"<br>Error al traer el id del tipo de reserva<br>") or fin_pagina();
      if($tipo_res->fields["id_tipo_reserva"]!="")
       $id_tipo_reserva=$tipo_res->fields["id_tipo_reserva"];
      else
       die("Error Interno ECB246: no se pudo determinar el tipo de reserva de stock. Consulte a la Divisiónm Software<br>");

      //volvemos a agregar como reservado para la fila correspondiente, este producto descontado
      $comentario="Se eliminaron códigos de barra que se habían entregado para el $titulo_pagina Nº $id_movimiento. Se volvieron a dejar como reservados los productos.";
      agregar_stock($id_prod_esp,$cant_borrar,$deposito_origen,$comentario,$id_tipo_movimiento,"reservado",$id_tipo_reserva,"",$id_detalle_movimiento,$id_licitacion,$nro_caso);

      //si el PM esta asociado a caso o el MM tiene como destino a RMA, mandamos un mail avisando que borraron
      //un codigo de barras que estaba entregado. Esto implica que un producto entregado que se agrego a RMA automaticamente
      //fue borrado, pero el RMA sigue estando en el sistema. En ese caso deben revisar el RMA generado
      if($nro_caso!="" || $deposito_destino==$id_stock_rma)
      {
      	$para="juanmanuel@coradir.com.ar,marco@coradir.com.ar";
      	if($nro_caso!="")
      	{
      	  $asunto="En el $titulo_pagina Nº $id_movimiento asociado a un Caso, se borraron Códigos de Barra entregados";
      	  $texto="Para el $titulo_pagina Nº $id_movimiento asociado al Caso Nº $nro_caso, se borraron los siguientes códigos de barras del producto '$producto_nombre':\n $a_borrar.\n";
      	  $texto.="\n\nPor favor revisar los RMA generados para este Nº de Caso.";
      	  $texto.="\n\nUsuario que borró el Código de Barras: ".$_ses_user["name"]." - Fecha: ".date("d/m/Y H:i:s")."\n\n\n";

      	}//de if($nro_caso!="")
      	elseif($deposito_destino==$id_stock_rma)
      	{
      	  $asunto="En el $titulo_pagina Nº $id_movimiento con destino RMA, se borraron Códigos de Barra";
      	  $texto="Para el $titulo_pagina Nº $id_movimiento asociado al Caso Nº $nro_caso, se borraron los siguientes códigos de barras del producto '$producto_nombre':\n $a_borrar.\n";
      	  $texto.="\n\nPor favor revisar los RMA generados para este Nº de Caso.";
      	  $texto.="\n\nUsuario que borró el Código de Barras: ".$_ses_user["name"]." - Fecha: ".date("d/m/Y H:i:s")."\n\n\n";

      	}//de elseif($deposito_destino==$id_stock_rma)
		enviar_mail($para,$asunto,$texto,'','','','','');

      }//de if($nro_caso!="" || $deposito_destino==$id_stock_rma)

   $db->CompleteTrans();
   echo "<script>window.opener.location.reload();this.close();</script>";
   echo "<center><b>El codigo de barra $a_borrar fue borrado con éxito</b></center>";

}//de if($_POST["borrar"]=="Borrar")

echo $error_cb;
echo $html_header;

//si no tenemos id de producto especifico, no se puede recibir los codigos de barra
//porque no hay producto especifico al cual atarlo
if($id_prod_esp=="")
 die("Error falta el id del producto especifico elegido");

//traemos los codigos de barra ya cargados junto con el id del producto especifico correspondiente,
//y permitimos agregar los que faltan, si es que falta alguno.
if($id_info_rma)
 $query="select rma_cb_recibidos.codigo_barra,log_rma_recepcion.id_prod_esp,producto_especifico.descripcion
         from stock.rma_cb_recibidos join stock.log_rma_recepcion using(id_log_recepcion)
         	  join stock.rma_recepcion using(id_rma_recepcion)
         	  join general.producto_especifico using(id_prod_esp)
         where rma_recepcion.id_info_rma=$id_info_rma
 		 order by log_rma_recepcion.id_prod_esp
        ";
else
 die("Falta ID RMA.");
$codigos_guardados=sql($query,"<br>Error al traer los códigos de barra ya cargados<br>") or fin_pagina();

$cantidad_ingresar=$total_comprado - $total_recibido;
echo $msg;

?>
<script>
function seleccionar_all(padre)
{
 	var check_name,i=0,cant_check,checkear;
 	if(padre.checked==true)
 	 checkear=true;
 	else
 	 checkear=false;
 	cant_check=eval('document.all.primer_nuevo_cb.value');
	if(typeof(eval('document.all.borrar_'+i))!="undefined")
	 check_name=eval('document.all.borrar_'+i);
	for(i;i<=cant_check;i++)
	{
	  check_name.checked=checkear;

	  if(typeof(eval('document.all.borrar_'+i))!="undefined")
	    check_name=eval('document.all.borrar_'+i);
	}//de for(i;i<cant_check;i++)

}//de function seleccionar_all(padre)

function alProximoInput(elmnt,content,next,index)
{
  if (content.length==elmnt.maxLength)
	{

	  if (typeof(next)!="undefined")
		{
		  next.focus();
		}
	  else
	   document.all.guardar.focus();

      if(typeof(boton=eval("document.all.autocompletar_consecutivos_"+index))!="undefined")
      {
         boton.style.visibility='visible';
      }

	}
}

function control_datos(controlar_prov_rma)
{
 if(controlar_prov_rma && document.all.select_proveedor_rma.value==-1)
 {alert('Debe elegir un proveedor para agregar los productos a RMA');
  return false;
 }

 return true;
}//de function control_datos(controlar_prov_rma)

/**************************************************************************************************
 Funcion que autocompleta los campos de codigos de barra, para evitar que el usuario tenga que
 cargar muchos codigos de barra. Se asume para que esto funcione bien, que los codigos de
 barra a ingresar seran todos consecutivos y solo numerales.
 @primer_codigo     El primer codigo de barras del rango que se ingresara
 @nombre_text       El nombre del campo desde donde se ingresaran los codigos. Este es la parte escrita
 					del nombre. Si por ejemplo, el campo se llama codigos_0, en este parametro
 					se pasara solo: codigos_
 @indice_text		El subindice que compone el nombre del campo. Este se usa para indicar desde
 					cual campo se comenzaran a ingresar los codigos de barra consecutivos.
 					Por ejemplo, si se pasa 3, se comenzara a agregar los codigos de barra
 					desde el campo codigos_3 (si en el parametro nombre_text venia: codigos_)
 @id_log_recibido   El id del log de recibido para el cual se van a autocompletar los codigos
                    de barra
***************************************************************************************************/
function autocompletar_codigos_barra(primer_codigo,nombre_text,indice_text)
{

	var aux_campo,arr_codigo,i,aux_string;
	var k,aux_cant;
	var cant_vacios=eval("document.all.cant_vacios");
	var cantidad_campos=prompt('Ingrese la cantidad de codigos de barra a completar\n(por favor ingrese solo números)',cant_vacios.value-indice_text);
	var codigo_insertar=parseFloat(primer_codigo) + 1;

	if(cantidad_campos>cant_vacios.value-1){
	 alert("La cantidad de códigos de barra a ingresar es mayor a la disponible");
	 return false;
	}

	for(i=0;i<cantidad_campos;i++,indice_text++){
		aux_campo=eval('document.all.'+nombre_text+"_"+indice_text);
		aux_string=String(codigo_insertar);
		//completamos con ceros a la izquierda el numero, para llegar a la longitud del codigo de barra pasado por parametro
		//(en general la longitud es 9)
		aux_cant=primer_codigo.length - aux_string.length;
		for(k=0;k<aux_cant;k++){ aux_string="0"+aux_string;}
		aux_campo.value=aux_string;
		//seteamos el proximo codigo a insertar
		codigo_insertar=parseFloat(codigo_insertar) + 1;
	}//de for(indice_text;indice_text<cantidad_campos;indice_text++)
}//de function autocompletar_codigos_barra(primer_numero,ultimo_numero,nombre_text,indice,cantidad_campos)

</script>
<script src="funciones.js"></script>
<form name="form1" method="POST" action="recepcion_codigos_barra_rma.php">
 <input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
 <input type="hidden" name="total_comprado" value="<?=$total_comprado?>">
 <input type="hidden" name="total_recibido" value="<?=$total_recibido?>">
 <input type="hidden" name="id_info_rma" value="<?=$id_info_rma?>">
 <input type="hidden" name="desde_pagina" value="<?=$desde_pagina?>">
 <input type="hidden" name="id_deposito" value="<?=$id_deposito?>">

 <table width="100%" align="center" border="1">
  <tr>
   <td id="ma" colspan="2">
    Ingrese los números de códigos de barra a recibir para el RMA Nº <?=$id_info_rma?>
   </td>
  </tr>
  <tr>
   <td>
	    <b>Cantidad de Nº a ingresar: <?=$cantidad_ingresar?></b>
   </td>
  </tr>
  <tr>
   <td>
    <table width="100%">
  <?
  //primero mostramos los codigos de barra ya insertados antes en los
  //input, pero con readonly
  $io=0;
  while(!$codigos_guardados->EOF)
  {?>

  	<?
  	if($io==0)
  	{?>
  	 <tr id="mo">
  	  <td>
  	   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  	   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  	   <!--para acomodar los campos correctamente-->
  	   <b>Seleccionar todos </b><input type="checkbox" name="seleccionar_todos" value="1" onclick="seleccionar_all(this)">
  	  </td>
  	  <td>
  	   Producto Recibido
  	  </td>
  	 </tr>
  	<?}
  	?>
  	<tr id="ma_sf">
    <td width="40%">
     &nbsp;&nbsp;&nbsp;&nbsp;<!--para acomodar los campos correctamente-->
     <input type="text" name="cod_barra_<?=$io?>" maxlength="9" tabindex="<?=$io+1?>" size="30" readonly value="<?=$codigos_guardados->fields["codigo_barra"]?>" onkeyup="toUnicode(this,this.value,cod_barra_<?=$io+1?>);" >
     <input type="checkbox" name="borrar_<?=$io?>" value="1">
    </td>
    <td align="center">
     <?=$codigos_guardados->fields["descripcion"]?>
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
  ?>
  <input type="hidden" name="primer_nuevo_cb" value="<?=$io?>">
  <?
  $contador_sin_cb=0;
  $acomodo=$io;
  for($io;$io<$total_comprado;$io++)
  {//este if es para tomar en cuenta aquellos productos que se entregan sin codigo de barras
   //(con esto se pone la leyenda "Entregado sin CB" en tantos input para codigos de barra,
   //como productos se hayan entregado sin codigo de barra).
   if($io>=$cantidad_ingresar+$acomodo)
   {$entregado="Entregado sin CB";
    $entregado_disabled="disabled";
    $entregado_readonly="readonly";
    $estilo_error="";
    $contador_sin_cb++;
   }
   else
   {if($_POST["cod_barra_$io"])
    {$entregado=$_POST["cod_barra_$io"];
     if($entregado==$cb_con_error)
      $estilo_error="style='color:red'";
     else
      $estilo_error="";
    }
   	else
   	{ $entregado="";
   	 $estilo_error="";
   	}
    $entregado_disabled="";
    $entregado_readonly="";
   }
   ?>
   <tr>
    <td>
     <?
     if($io==$total_comprado-1)
     {
      $third_par="document.all.guardar";

     }
     else
     {
      $third_par="cod_barra_".($io+1);
     }

     if($io<$total_comprado-1)
   	 {?>
      <input type="button" name="autocompletar_consecutivos_<?=$io?>" value="V" title="Autocompletar codigos de barra consecutivos" onclick="autocompletar_codigos_barra(document.all.cod_barra_<?=$io?>.value,'cod_barra',<?=$io+1?>)" style="visibility:hidden">
     <?
   	 }
   	 else
   	  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
     ?>
     <input type="text" maxlength="9" tabindex="<?=$io+1?>" name="cod_barra_<?=$io?>" value="<?=$entregado?>" <?=$estilo_error?> <?=$entregado_readonly?> size="30" onkeyup="alProximoInput(this,this.value,<?=$third_par?>,<?=$io?>);" >
     <input type="button" name="limpiar_<?=$io?>" value="Limpiar" <?=$entregado_disabled?> onclick="document.all.cod_barra_<?=$io?>.value=''">
    </td>
   </tr>
  <?
  }//for($io;$io<$total_comprado;$io++)
  ?>
  <input type="hidden" name="cant_vacios" value="<?=$io-$foco?>">
    </table>
   </td>
  </tr>
 </table>
 <table width="100%" align="center">
  <tr>
   <td align="center">
    <input type="submit" name="guardar" value="Guardar" <?if($cantidad_ingresar<=0 || $total_recibido==$total_comprado) echo "disabled"?> onclick="return control_datos(<?=$generar_combo_prov_rma?>)">
   </td>
   <td>
   	<input type="submit" name="borrar" disabled value="Borrar" style="width:63" onclick="
  																			if(confirm('Se borrarán los códigos de barra seleccionados.\n¿Está seguro?'))
  																			{
  																			 return true;
  																			}
  																			else
  																			 return false;
  	"
    <?if($contador_sin_cb==$total_recibido) echo "disabled";?>
  	>
   </td>
   <td align="center">
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr>
 </table>
 <?if($cantidad_ingresar<=0 || $total_recibido==$total_comprado)
    echo "<h5>Todos los productos de esta fila fueron entregados</h5>";
 ?>

 <script>
  if(typeof(document.all.cod_barra_<?=$foco?>)!="undefined")
   document.all.cod_barra_<?=$foco?>.focus();
 </script>
</from>
</body>
</html>