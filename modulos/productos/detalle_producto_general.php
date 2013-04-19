<?/*

----------------------------------------
 Autor: MAC
 Fecha: 01/07/2005
----------------------------------------

MODIFICADA POR
$Author: fernando $
$Revision: 1.11 $
$Date: 2006/09/04 17:27:11 $

*/
include_once("../../config.php");


$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"];
$desc_gral=$_POST["desc_gral"];
$id_tipo_prod=$_POST["tipo_producto"];
$precio=$_POST["precio_licitacion"];
$observaciones=$_POST["observaciones"];
($_POST["mostrar_licitaciones"])?$mostrar_licitaciones=1:$mostrar_licitaciones=0;

if ($parametros['down']=='t')
          {
           $FileName=$parametros["nombre_ar"];
           $FileType=$parametros["tipo"];
           $FileSize=$parametros["tamaño"];
           $FilePath=UPLOADS_DIR."/folletos/";
           $FileNameFull="$FilePath/$FileName";
           if (substr($FileName,strrpos($FileName,".")) == ".zip")
                  {
                   Mostrar_Header($FileName,$FileType,$FileSize);
                   readfile($FileNameFull);
                  }
            else
                    {
                    $FileNameFull = substr($FileNameFull,0,strrpos($FileNameFull,"."));
                    $fp = popen("/usr/bin/unzip -p \"$FileNameFull\"","r");
                    Mostrar_Header($FileName,$FileType,$FileSize);
                    fpassthru($fp);
                    pclose($fp);
                    }
            exit();

}//DEL PRIMER IF

if($_POST["guardar"]!="")
{
 $db->StartTrans();
 if($id_producto)//si tenemos el id de producto, actualizamos
 {
  $query="update productos set desc_gral='$desc_gral',id_tipo_prod=$id_tipo_prod,precio_licitacion=$precio,observaciones='$observaciones',mostrar_licitaciones=$mostrar_licitaciones
          where id_producto=$id_producto
         ";
  $msg="El producto se actualizó con éxito";
 }
 else//sino tenemos el id del producto, insertamos
 {
  $query="select nextval('productos_id_producto_seq') as id_producto";
  $id=sql($query,"<br>Error al generar el nuevo numero de secuenciabr>") or fin_pagina();
  $id_producto=$id->fields["id_producto"];
  $query="insert into productos (id_producto,desc_gral,id_tipo_prod,precio_licitacion,observaciones,activo_productos)
          values($id_producto,'$desc_gral',$id_tipo_prod,$precio,'$observaciones',1)";
  $msg="El producto se insertó con éxito";
 }
 sql($query,"<br>Error al insertar/actualizar el producto<br>") or fin_pagina();

 $db->CompleteTrans();
}



//traemos los datos del producto, si esta presente el id de producto y no lo tenemos ya por POST
//(o sea, si viene desde el listado)
if($id_producto && $_POST["id_producto"]==""){
 $query="select desc_gral,id_tipo_prod,precio_licitacion,observaciones,mostrar_licitaciones 
         from productos
         where id_producto=$id_producto";
 $datos_producto=sql($query,"<br>Error al traer los datos del producto<br>") or fin_pagina();

 $desc_gral = $datos_producto->fields["desc_gral"];
 $id_tipo_prod = $datos_producto->fields["id_tipo_prod"];
 $precio = $datos_producto->fields["precio_licitacion"];
 $observaciones = $datos_producto->fields["observaciones"];
 $mostrar_licitaciones = $datos_producto->fields["mostrar_licitaciones"];

}//de if($id_producto)


echo $html_header;
?>
<script>
//control de que los campos esten completados correctamente
function control_datos()
{
 if(document.all.desc_gral.value=='')
 {alert('Debe ingresar una Descripcion para el producto');
  return false;
 }
 if(document.all.precio_licitacion.value=='')
 {alert('Debe ingresar un Precio para el producto');
  return false;
 }
 if(document.all.tipo_producto[document.all.tipo_producto.selectedIndex].value==-1)
 {alert('Debe elegir un Tipo de producto');
  return false;
 }

 return true;
}//de function control_datos()

//variable para abrir la ventana de cargar nuevo hijo
var wnuevo_hijo="";

//despliega y oculta la tabla de productos especificos
//si mostrar=0, oculta la tabla, si es =1, la despliega
function ver_prod_esp()
{
 if(document.all.ver_hijos.value=='Mostrar Productos Hijos')
 {document.all.productos_especificos.style.display='block';
  document.all.ver_hijos.value='Ocultar Productos Hijos';
  document.all.ver_hijos.title='Oculta los productos específicos asociados al producto general que se está visualizando';
 }
 else
 {
  document.all.productos_especificos.style.display='none';
  document.all.ver_hijos.value='Mostrar Productos Hijos';
  document.all.ver_hijos.title='Muestra los productos específicos asociados al producto general que se está visualizando';
 }

}//de function ver_prod_esp(mostrar=0)
</script>

<?if($msg)echo "<center><b>$msg</b></center>";else echo "<br>";
 $link_nuevo_prod=encode_link("detalle_producto_general.php",array("es_nuevo"=>$parametros["es_nuevo"]));
?>
<form action="<?=$link_form?>" method="POST">
  <input type="hidden" name="id_producto" value="<?=$id_producto?>">
  <table width="95%" cellspacing=0 align="center" bgcolor=<?=$bgcolor_out?> id="tabla_info" class="bordes">
   <tr id=mo>
    <td colspan="4">
     <font size="2">Detalle de Producto General</font>
    </td>
   </tr>
   <tr>
    <td width="15%">
     <b>Producto</b>
    </td>
    <td width="45%" colspan="3">
     <input name="desc_gral" value="<?=$desc_gral?>" size="135">
    </td>
   </tr>
   <tr>
    <td width="15%">
     <b>Precio U$S</b>
    </td>
    <td width="40%">
     <input name="precio_licitacion" value="<?=($precio)?number_format($precio,2,'.',''):"";?>" size="20" onchange="control_numero(this,'Precio')">
     <?
     if($id_producto)
     {?>
      &nbsp;<input type='button' name='historial' value='H' onclick="window.open('<?=encode_link("../general/historial_comentarios.php",array("id_producto"=>$id_producto));?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
     <?
     }
     ?>
    </td>
     <td width="15%">
     <b>Tipo de Producto</b>
    </td>
    <td width="40%" align="left">
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
   <tr>
    <td colspan="4">
     <hr>
    </td>
   </tr>
   <?
   ($mostrar_licitaciones)?$checked_mostrar_licitaciones='checked':$checked_mostrar_licitaciones='';
   ?>
   <tr>
     <td colspan="4">
     <input type="checkbox" name="mostrar_licitaciones" <?=$checked_mostrar_licitaciones?> value='1'> &nbsp;<b>Mostrar Licitaciones</b>
     </td>
   </tr>
   <tr>
    <td colspan="4">
     <hr>
    </td>
   </tr>
   <tr>
    <td width="10%" onclick="alert(document.all.ver_hijos.onclick);">
     <b>Observaciones</b>
    </td>
    <td width="50%" colspan="3" >
     <textarea style="width:100%" rows="4" name="observaciones"><?=$observaciones?></textarea>
    </td>
   </tr>
  </table>
  <table align="center" width="95%">
   <tr>
    <td width="10%">
     <input type="button" name="nuevo" value="Nuevo Producto" onclick="document.location='detalle_producto_general.php'">
    </td>
    <td align="right" width="40%">
     <input type="submit" name="guardar" value="Guardar" onclick="return control_datos()">
    </td>
    <td width="10%">
     <?
      if($parametros["es_nuevo"]!="")
      {?>
      	<input type="button" name="volver" value="Cerrar" onclick="window.opener.location.reload();window.close();">
       <?
      }
      else
      {
      ?>
      <input type="button" name="volver" value="Volver" onclick="document.location='listado_productos.php'">
      <?
      }
      ?>
    </td>
    <td>
     &nbsp;
    </td>
    <?/*<td width="40%" align="right">
     <input type="button" name="ver_hijos" value="Mostrar Productos Hijos" title="Muestra los productos específicos asociados al producto general que se está visualizando" style="width:150px;" onclick="ver_prod_esp()">
     <?
     if($id_producto)
     {
      $link_cargar_hijo=encode_link("detalle_producto_especifico.php",array("con_padre"=>1,"producto_padre"=>$desc_gral,"id_producto"=>$id_producto,"id_tipo_prod"=>$id_tipo_prod));
     ?>
     <input type="button" name="cargar_hijo" value="Cargar Producto Hijo" title="Permite asociar un nuevo producto específico al producto general que se está visualizando" onclick="if(wnuevo_hijo=='' || wnuevo_hijo.closed)wnuevo_hijo=window.open('<?=$link_cargar_hijo?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=950,height=600');else wnuevo_hijo.focus();"  style="width:150px">
     <?
     }
     else
      echo "&nbsp;";
     ?>
    </td>*/?>
   </tr>
  </table>
  <?/*
  if($id_producto)
  {//traemos los productos especificos asociados a este producto
   $query="select id_prod_esp,producto_especifico.descripcion,marca,modelo,precio_stock,tipos_prod.descripcion as tipo,
           producto_especifico.id_tipo_prod
           from producto_especifico join tipos_prod using(id_tipo_prod)
           where id_producto=$id_producto";
   $productos_hijos=sql($query,"<br>Error al traer los productos hijos<br>") or fin_pagina();
   ?>
   <br>
   <div id="productos_especificos" style="display:none">
    <table width="95%" align="center" class="bordes">
     <tr id="mo" align="center">
      <td colspan="5">
       Productos Específicos Asociados a: <?=$desc_gral?>
      </td>
     </tr>
     <tr id="ma">
      <td width="40%">
       Descripción
      </td>
      <td width="20%">
       Tipo de Producto
      </td>
      <td width="15%">
       Marca
      </td>
      <td width="15%">
       Modelo
      </td>
      <td width="10%">
       Precio
      </td>
     </tr>
     <?
     while (!$productos_hijos->EOF)
     {?>
      <tr bgcolor="<?=$bgcolor_out?>">
       <td>
        <?=$productos_hijos->fields["descripcion"]?>
       </td>
       <td>
        <?=$productos_hijos->fields["tipo"]?>
       </td>
       <td>
        <?=$productos_hijos->fields["marca"]?>
       </td>
       <td>
        <?=$productos_hijos->fields["modelo"]?>
       </td>
       <td>
        <?=$productos_hijos->fields["precio_stock"]?>
       </td>
      </tr>
      <?
      $productos_hijos->MoveNext();
     }//de while(!$productos_hijos->EOF)
     ?>
    </table>
   </div>
  <?
  }//de if($id_producto)
  */
  ?>

  <br>
  <hr>
  <?
  if ($id_producto)
  {
  $sql = "select titulo,contenido from descripciones where id_producto=$id_producto ";
  $rs = sql($sql,"<br>Error al traer las descripciones<br>") or fin_pagina();

  $link=encode_link("../licitaciones/desc_productos.php",array("id_producto"=>$id_producto,"pagina_viene"=>"detalle_producto_general"));
?>
	  <table class="bordes" width=90% align=Center>
	    <tr>
	    <tr>
	       <td colspan=2 id=mo>
	        <table width="100%">
	         <tr id=mo>
	          <td align="right" width="62%">
	          	Descripción de las Licitaciones
	          </td>
	          <td align="right">
	            <input type=button name=desc_productos value="Agregar Descripción" onclick="window.open('<?=$link?>')">
	          </td>
	         </tr>
	        </table>
	       </td>
	      </tr>
         </td>
	    </tr>
	    <tr id="ma">
	      <td> Titulo    </td>
	      <td>Descripción </td>
	    </tr>
		<?php
		while (!$rs->EOF)
		{?>
	        <tr bgcolor=<?=$bgcolor_out?>>
	          <td> <b><?php echo $rs->fields["titulo"];?> </b> </td>
	          <td> <b><?php echo $rs->fields["contenido"];?> </b></td>
	        </tr>
			<?
			$rs->MoveNext();
		}//de while (!$rs->EOF)
	}//de if ($id_producto)
	?>
  </table>
  <br><hr>
  <?
  if($id_producto!="")
  {
	  	$sql="select * from folletos where id_producto=$id_producto";

	   $resultado = sql($sql,"<br>Error al traer los folletos<br>") or fin_pagina();


	  $link_carga_folleto=encode_link("../licitaciones/desc_folletos.php",array("pagina"=>"../productos/detalle_producto_general.php","id_producto"=>$id_producto,"desc_prod"=>$desc_gral));
	  ?>
			  <table class="bordes" width=90% align=center>
			   <tr >
			    <td colspan="4" id=mo>
			     <table width="100%">
			      <tr id=mo>
				   	<td align="right">Folletos Asociados al producto</td>
				   	<td align="right">
				   	 <input type="button" name="cargar_folleto" value="Cargar Folleto" onclick="window.open('<?=$link_carga_folleto?>')">
				   	</td>
				   </tr>
				  </table>
				 </td>
			   </tr>
			   <tr id="ma" title="Haga click sobre el nombre del archivo para abrirlo">
			   <td width="30%"><b>Nombre</td>
			   <td width="20%"><b>Tamaño</td>
			   <td width="30%"><b>Tipo</td>
			   <td width="20%"><b>Tamaño Comprimido</td>
			</tr>
			<?php
			    $cont=0;
			   while (!$resultado->EOF)
			    {
			     ?>
			     <tr bgcolor="<?=$bgcolor_out?>" title="Haga click sobre el nombre del archivo para abrirlo">
			     <a href="<?php echo encode_link($_SERVER["PHP_SELF"],array("down"=>'t',"nombre_ar"=>$resultado->fields["nombre_ar"],"tamaño"=>$resultado->fields['tamaño'],"tipo"=>$resultado->fields['tipo'])); ?>"><td style="cursor:hand;"><b><font color="<?php if ($mod!=0) echo $bgcolor3; else echo $bgcolor1;?>"><?php echo $resultado->fields['nombre_ar']; ?></b></td></a>
			     <td><b><?php echo sprintf("%01.2lf",$resultado->fields['tamaño']/1024); ?> Kbyte</b></td>
			     <td><b><?php echo $resultado->fields['tipo']; ?></b></td>
			     <td><b><?php echo sprintf("%01.2lf",$resultado->fields['tamaño_comp']/1024); ?> Kbyte</b></td>
			     <?php
			     $resultado->MoveNext();
			     $cont++;
			   }
			?>
			 </table>
			<?php
  }//de if($id_producto!="")
 ?>


</form>
</body>
<br>
<?fin_pagina();?>