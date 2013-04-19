<?/*

----------------------------------------
 Autor: MAC
 Fecha: 01/07/2005
----------------------------------------

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.19 $
$Date: 2006/06/22 15:46:50 $

*/
include_once("../../config.php");
$id_prod_esp=$parametros["id_prod_esp"] or $id_prod_esp=$_POST["id_prod_esp"];
$descripcion=$_POST["descripcion"];
$marca=$_POST["marca"];
$modelo=$_POST["modelo"];
$id_tipo_prod=$_POST["tipo_producto"];
$precio_stock=$_POST["precio_stock"];
$observaciones=$_POST["observaciones"];
$producto_padre=$_POST["producto_padre"];
$id_producto=$_POST["id_producto"];

/*//si la pagina se llamo desde producto general para cargar un hijo
if($parametros["con_padre"]==1)
{
 $id_producto=$parametros["id_producto"];
 $producto_padre=$parametros["producto_padre"];
 $id_tipo_prod=$parametros["id_tipo_prod"];
}*/

if($_POST["guardar"]!="")
{
 $db->StartTrans();

 $duplicado_msg="";

 if($id_prod_esp)//si tenemos el id de producto, actualizamos
 {
  $query="update producto_especifico set descripcion='$descripcion',id_tipo_prod=$id_tipo_prod,precio_stock=$precio_stock,
          observaciones='$observaciones',marca='$marca',modelo='$modelo'";
          /*,id_producto=$id_producto*/
  $query.=" where id_prod_esp=$id_prod_esp";

  $msg="El producto se actualizó con éxito";
 }
 else//sino tenemos el id del producto, insertamos
 {
  //antes de insertar el nuevo producto, controlamos que no exista ya en la base de datos
  $query="select marca,modelo,descripcion from producto_especifico where (marca='$marca' and modelo='$modelo') or descripcion='$descripcion'";
  $repetido=sql($query,"<br>Error al buscar producto duplicado<br>") or fin_pagina();

  //si ya existe un producto con la misma marca y modelo o la misma descripcion general, damos el aviso, y no insertamos
  if($repetido->RecordCount()>0)
  {
  	if($repetido->fields["marca"]==$marca && $repetido->fields["modelo"]==$modelo)
  	 $duplicado_msg="la misma marca y modelo. No puede ser insertado nuevamente.";
  	else
  	 $duplicado_msg="la misma descripción. No puede ser insertado nuevamente.";
  	$msg="Ya existe un producto cargado, con $duplicado_msg";
  }//de if($repetido->RecordCount()>0)
  else//sino, insertamos el producto
  {
   $query="select nextval('producto_especifico_id_prod_esp_seq') as id_prod_esp";
   $id=sql($query,"<br>Error al generar el nuevo numero de secuencia<br>") or fin_pagina();
   $id_prod_esp=$id->fields["id_prod_esp"];
   $usuario_carga=$_ses_user['name'];
   $fecha_carga=$fecha=date("Y-m-d H:i:s");
/*  $query="insert into producto_especifico (id_prod_esp,descripcion,id_tipo_prod,precio_stock,marca,modelo,observaciones,id_producto,activo)
          values($id_prod_esp,'$descripcion',$id_tipo_prod,$precio_stock,'$marca','$modelo','$observaciones',$id_producto,1)";*/
   $query="insert into producto_especifico (id_prod_esp,descripcion,id_tipo_prod,precio_stock,marca,modelo,observaciones,activo,usuario_carga,fecha_carga)
           values($id_prod_esp,'$descripcion',$id_tipo_prod,$precio_stock,'$marca','$modelo','$observaciones',1,'$usuario_carga','$fecha_carga')";
   $msg="El producto se insertó con éxito";
  }//del else de if($repetido->RecordCount()>0)
 }
 if($duplicado_msg=="")
  sql($query,"<br>Error al insertar/actualizar el producto<br>") or fin_pagina();

 $db->CompleteTrans();
}//de if($_POST["guardar"]!="")



//traemos los datos del producto, si esta presente el id de producto y no lo tenemos ya por POST
//(o sea, si viene desde el listado)
if($id_prod_esp || $_POST["id_prod_esp"]!="")
{
 $query="select producto_especifico.usuario_carga,producto_especifico.fecha_carga,producto_especifico.descripcion,producto_especifico.marca,producto_especifico.modelo,comentario_foto,id_prod_esp,nombre_archivo,
         producto_especifico.id_tipo_prod as tipo_producto,producto_especifico.precio_stock,producto_especifico.observaciones,
         producto_especifico.id_producto,productos.desc_gral as producto_padre
 		from general.producto_especifico
 			left join general.productos using(id_producto)
    	left join general.foto_producto using(id_prod_esp)
         where producto_especifico.id_prod_esp=$id_prod_esp";
 $datos_producto=sql($query,"<br>Error al traer los datos del producto<br>") or fin_pagina();

 $descripcion=$datos_producto->fields["descripcion"];
 $marca=$datos_producto->fields["marca"];
 $modelo=$datos_producto->fields["modelo"];
 $id_tipo_prod=$datos_producto->fields["tipo_producto"];
 $precio_stock=$datos_producto->fields["precio_stock"];
 $observaciones=$datos_producto->fields["observaciones"];
 $id_producto=$datos_producto->fields["id_producto"];
 $producto_padre=$datos_producto->fields["producto_padre"];
 $usuario_carga=$datos_producto->fields["usuario_carga"];
 $fecha_carga=$datos_producto->fields["fecha_carga"];
}//de if($id_producto)

echo $html_header;
?>
<script>
//control de que los campos esten completados correctamente
function control_datos()
{
 if(document.all.descripcion.value=='')
 {alert('Debe ingresar una Descripcion para el producto');
  return false;
 }
 if(document.all.marca.value=='')
 {alert('Debe ingresar una Marca para el producto');
  return false;
 }
 if(document.all.modelo.value=='')
 {alert('Debe ingresar una Modelo para el producto');
  return false;
 }
 if(document.all.precio_stock.value=='')
 {alert('Debe ingresar un Precio de Stock para el producto');
  return false;
 }
 if(document.all.tipo_producto[document.all.tipo_producto.selectedIndex].value==-1)
 {alert('Debe elegir un Tipo de producto');
  return false;
 }
 /*if(document.all.id_producto.value=='')
 {alert('Debe elegir un Poducto padre para el producto ingresado');
  return false;
 }*/

 return true;
}

//variable de la ventana para elegir padre
var wpadre="";
</script>

<?if($msg)
   echo "<center><b>$msg</b></center>";
  else
   echo "<br>";
 $link_form=encode_link("detalle_producto_especifico.php",array("con_padre"=>1,"es_nuevo"=>$parametros["es_nuevo"]));
?>
<form action="<?=$link_form?>" method="POST">
  <input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
  <table width="95%" cellspacing=0 align="center" bgcolor=<?=$bgcolor_out?> id="tabla_info" class="bordes">
   <tr id=mo>
    <td colspan="4">
     <font size="2">Detalle de Producto Específico</font>
    </td>
   </tr>
   <tr>
    <td width="20%">
     <b>Producto</b>
    </td>
    <td width="45%" colspan="3">
     <input name="descripcion" value="<?=$descripcion?>" size="131">
    </td>
   </tr>
   <tr>
   <tr>
    <td width="20%">
     <b>Marca</b>
    </td>
    <td width="30%">
     <input name="marca" value="<?=$marca?>" size="50">
    </td>
    <td width="15%">
     <b>Modelo</b>
    </td>
    <td width="35%">
     <input name="modelo" value="<?=$modelo?>" size="52">
    </td>
   </tr>
   <tr>
    <td width="20%">
     <b>Precio Stock U$S</b>
    </td>
    <td width="35%">
     <input name="precio_stock" value="<?=($precio_stock)?number_format($precio_stock,2,'.',''):"";?>" size="20" onchange="control_numero(this,'Precio de Stock')">
     <?/*
     if($id_producto)
     {?>
      &nbsp;&nbsp;<input type='button' name='historial' value='H' onclick="window.open('<?=encode_link("../general/historial_comentarios.php",array("id_producto"=>$id_producto));?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
     <?
     }*/
     ?>
    </td>
     <td width="15%">
     <b>Tipo de Producto</b>
    </td>
    <td width="30%" align="left">
     <?
     $query="select id_tipo_prod,descripcion from tipos_prod order by descripcion";
     $tipos_prod=sql($query,"<br>Error al traer los tipos de productos<br>") or fin_pagina();
     ?>

     <select name="tipo_producto"
      onKeypress="buscar_op(this);"
      onblur="borrar_buffer();"
      onclick="borrar_buffer();"
     >
      <option value=-1 <?if($id_tipo_prod=="") echo "selected"?>>Seleccione un Tipo...</option>
      <?
      while (!$tipos_prod->EOF)
      {?>
       <option value="<?=$tipos_prod->fields["id_tipo_prod"]?>" <?if($tipos_prod->fields["id_tipo_prod"]==$id_tipo_prod) echo "selected"?>>
        <?=$tipos_prod->fields["descripcion"]?>
       </option>
       <?
       $tipos_prod->MoveNext();
      }//de while(!$tipos_prod->EOF)
      ?>
     </select>
     <?
     $link_nuevo_tipo=encode_link("nuevo_tipo_producto.php",array());

     if(permisos_check("inicio","permiso_agregar_nuevo_tipo"))
     {
      ?>
	     <input type="button" name="nuevo_tipo" value="Nuevo" title="Nuevo Tipo de Producto" onclick="window.open('<?=$link_nuevo_tipo?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=210,top=190,width=700,height=200')">
	  <?
     }//de if(permisos_check("inicio","permiso_agregar_nuevo_tipo"))
	?>
    </td>
   </tr>
   <?
   if($id_prod_esp || $_POST["id_prod_esp"]!="")
   {?>
	   <tr>
	   	<td width="20%">
	     <b>Usuario Carga</b>
	    </td>
	    <td width="30%">
	     <input name="usuario_carga" value="<?=$usuario_carga?>" size="50" readonly>
	    </td>
	    <td width="20%">
	     <b>Fecha Carga</b>
	    </td>
	    <td width="30%">
	     <input name="fecha_carga" value="<?=fecha($fecha_carga). " " .Hora($fecha_carga);?>" size="50" readonly>
	    </td>
	   </tr>
   <?
   }//de if($id_prod_esp && $_POST["id_prod_esp"]=="")
   ?>
   <tr>
    <td colspan="4">
     <hr>
    </td>
   </tr>
   <?/*
   <tr>
    <td>
     <b>Producto Padre</b>
    </td>
    <td colspan="3">
     <input type="hidden" name="id_producto" value="<?=$id_producto?>">
     <input type="text" name="producto_padre" readonly value="<?=$producto_padre?>" size="112">&nbsp;
     <?//definimos el javascript que necesitamos para traer el padre
      $onclick_cargar="window.opener.document.all.id_producto.value=document.all.id_producto_seleccionado.value;
                window.opener.document.all.producto_padre.value=document.all.nombre_producto_elegido.value;
                window.close();";
      $link_elegir=encode_link("listado_productos.php",array("pagina_viene"=>"detalle_producto_especifico.php","onclick_cargar"=>$onclick_cargar));

     if($parametros["con_padre"]==1)
      $disabled_por_padre="disabled";
     else
      $disabled_por_padre="";
     ?>
     <input type="button" <?=$disabled_por_padre?> name="elegir_padre" value="Elegir Padre" onclick="if(wpadre==''||wpadre.closed)wpadre=window.open('<?=$link_elegir?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=900,height=600');else wpadre.focus();">
    </td>
   </tr>
   */
   ?>
   <tr>
    <td colspan="4">
     <hr>
    </td>
   </tr>
   <tr>
    <td width="20%">
     <b>Observaciones</b>
    </td>
    <td width="50%" colspan="3" >
     <textarea style="width:100%" rows="4" name="observaciones"><?=$observaciones?></textarea>
    </td>
   </tr>
   <tr>
    <td colspan="4">
     <table width="100%" class="bordes">
      <tr>
       <td id=ma colspan="3">
        Fotos del Producto
       </td>
      </tr>
      <tr>
       <td width="40%">
        <font size="2" color="Blue"><b> <?=$datos_producto->fields["comentario_foto"];?></b></font>
       </td>
       <td width="50%"  title="<?=$datos_producto->fields["nombre_archivo"];?>">
       <?$link_foto=encode_link("foto_ampliada.php",array("id_prod_esp"=>$datos_producto->fields["id_prod_esp"],"nombre_producto"=>$descripcion,"archivo"=>$datos_producto->fields["nombre_archivo"],"coment"=>$datos_producto->fields["comentario_foto"]))?>
        <img src="./Fotos/<?=$datos_producto->fields["id_prod_esp"];?>/<?=$datos_producto->fields["nombre_archivo"];?>" width="150" height="150" style='cursor: hand;' onclick="window.open('<?=$link_foto?>')">
       </td>
       <td width="10%">
        <?$link_fotos=encode_link("ver_fotos_productos.php",array("id_prod_esp"=>$id_prod_esp,"nombre_producto"=>$descripcion))?>
        <input type="button" name="mas_imagenes" value="Ver más fotos" onclick="window.open('<?=$link_fotos?>')"><br>
		 <?$link_fotos1=encode_link("guardar_foto.php",array("id_prod_esp"=>$id_prod_esp,"nombre_producto"=>$descripcion))?>
        <input type="button" name="nueva_fo" value="Nueva Foto" onclick="window.open('<?=$link_fotos1?>')"><br>
        </td>
      </tr>
     </table>
    </td>
   <tr>
  </table>
  <table align="center" width="95%">
   <tr>
    <td width="10%">
     <?
     if(!permisos_check("inicio","permiso_agregar_nuevo_prod_esp"))
       $disabled_por_permiso="disabled title='Usted no tiene permiso para agregar/modificar productos específicos'";
     else
     {
      $disabled_por_permiso="";
      ?>
       <input type="button" name="nuevo" value="Nuevo Producto" onclick="document.location='detalle_producto_especifico.php'">
      <?
     }
    ?>

    </td>
    <td align="right" width="40%">
     <input type="submit" name="guardar" value="Guardar" onclick="return control_datos()" <?=$disabled_por_permiso?>>
    </td>
    <td>
    <?
      if($parametros["es_nuevo"]!="")
      {?>
      	<input type="button" name="volver" value="Cerrar" onclick="window.opener.location.reload();window.close();">
       <?
      }
      else
      {
      ?>
      <input type="button" name="volver" value="Volver" onclick="document.location='listado_productos_especificos.php'">
      <?
      }
      ?>
    </td>
   </tr>
  </table>
</form>
</body>
<br>
<?fin_pagina();?>