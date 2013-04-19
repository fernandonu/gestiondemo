<?/*

----------------------------------------
 Autor: MAC
 Fecha: 01/07/2005
----------------------------------------

MODIFICADA POR
$Author: fernando $
$Revision: 1.5 $
$Date: 2006/01/04 20:14:30 $

*/
include_once("../../config.php");
$pagina_viene=$parametros["pagina_viene"];
$onclick_cargar=$parametros["onclick_cargar"];

//este boton es temporal solo se usa una vez. despues debe ser eliminado
if($_POST["pasar_tipo"]!="")
{
 $db->StartTrans();
 //traemos todos los productos con su tipo
 $query="select id_producto,tipo from productos order by id_producto";
 $prod=sql($query,"<br>Error al traer los productos<br>") or fin_pagina();

 //por cada producto, traemos el id del tipo correspondiente y actualizamos el id de tipo del tipo de producto
 $contador_tipos=0;
 while (!$prod->EOF)
 {
  $id_producto=$prod->fields["id_producto"];
  $tipo=$prod->fields["tipo"];

  //traemos el id del tipo del producto y lo actualizamos en la tabla productos
  $query="select id_tipo_prod from tipos_prod where codigo = '$tipo'";
  $tipo_p=sql($query,"<br>Error al traer el id de tipo<br>") or fin_pagina();
  $id_tipo_prod=$tipo_p->fields["id_tipo_prod"];
  if($id_tipo_prod!="")
  {$query="update productos set id_tipo_prod=$id_tipo_prod where id_producto=$id_producto";
   sql($query,"<br>Error al actualizar tipo prod<br>") or fin_pagina();
   $contador_tipos++;
  }
  else
   die("nooooo $id_producto con tipo $tipo falló");

  $prod->MoveNext();
 }//de while(!$prod->EOF)
 //contamos cuantos productos quedaron con el tipo vacio para arreglarlos
 $query="select id_producto from productos where id_tipo_prod is null";
 $vacios=sql($query,"<br>Error al traer los id de productos con tipo vacio<br>") or fin_pagina();
 if($vacios->RecordCount()>0)
 {echo "<center>ID de productos que quedaron con el id de tipo vacio: ".$vacios->RecordCount()."</center><br>";
  while (!$vacios->EOF)
  {
  	echo $vacios->fields["id_producto"]." - ";

   $vacios->MoveNext();
  }//de while(!$vacios->EOF)
 }//de if($vacios->RecordCount()>0)

 echo "<br><b>Productos afectados: $contador_tipos<br><font color=red>Recordar agregar la restriccion entre tipos_prod y productos</font></b><br><br>";
 $db->CompleteTrans();
}//de if($_POST["pasar_tipo"]!="")
/*------------------------------------------------------------------------------------------------------------------------*/

$var_sesion=array(
                  "tipo_producto"=>-1
				  );

variables_form_busqueda("producto_general",$var_sesion);

//traemos los productos

$orden = array (
    "default" => "1",
    "1" => "desc_gral",
    "2" => "tipo",
    "3" => "precio_licitacion",
  );

$filtro = array (
    "desc_gral" => "Descripción",
    "precio_licitacion" => "Precio",
    "observaciones" => "Observaciones",
  );

//traemos los productos
$query_productos="select id_producto,desc_gral,precio_licitacion,tipos_prod.descripcion as tipo,productos.id_tipo_prod
                  from productos join tipos_prod using(id_tipo_prod)";

if($tipo_producto!=-1)
{$where.="(id_tipo_prod=$tipo_producto)";
}
else
 $where="";

$query="select id_tipo_prod,descripcion from tipos_prod order by descripcion";
$tipos_prod=sql($query,"<br>Error al traer los tipos de productos<br>") or fin_pagina();

echo $html_header;

$link_form=encode_link("listado_productos.php",array("pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar));
?>
<form action="<?=$link_form?>" method="POST">
 <?
 //si se llamo a este listado para elegir un producto, ponemos
 //los hiddens para pasar datos a la pagina que ha llamado al listado
 if($pagina_viene)
 {?>
  <input type="hidden" name="id_producto_seleccionado" value="">
  <input type="hidden" name="nombre_producto_elegido" value="">
  <input type="hidden" name="precio_producto_elegido" value="">
  <input type="hidden" name="tipo_producto_elegido" value="">
 <?
 }
 ?>
  <table width="98%" align="center" cellpadding="3">
   <tr>
    <td id=mo>
     <font size="2">Listado de Productos Generales</font>
    </td>
   </tr>
  </table>
  <table width="98%" align="center">
   <tr>
    <td width="10%">
     <?$link_nuevo_prod=encode_link("detalle_producto_general.php",array("es_nuevo"=>1));?>
     <input type="button" name="nuevo" value="Nuevo Producto" onclick="window.open('<?=$link_nuevo_prod?>')">
    </td>
    <td align="center">
     <?
     $link_tmp=array("pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar);
     list($query,$total_productos,$link_pagina,$up) = form_busqueda($query_productos,$orden,$filtro,$link_tmp,$where,"buscar");
?>
     &nbsp;<b>Tipo de Producto</b>&nbsp;
     <select name="tipo_producto"
      onKeypress="buscar_op(this);"
      onblur="borrar_buffer();"
      onclick="borrar_buffer();"
     >
      <option value=-1 <?if($tipo_producto==-1) echo "selected"?>>Todos</option>
      <?
      while (!$tipos_prod->EOF)
      {?>
       <option value="<?=$tipos_prod->fields["id_tipo_prod"]?>" <?if($tipos_prod->fields["id_tipo_prod"]==$tipo_producto) echo "selected"?>>
        <?=$tipos_prod->fields["descripcion"]?>
       </option>
       <?
       $tipos_prod->MoveNext();
      }//de while(!$tipos_prod->EOF)
      ?>
     </select>&nbsp;
     <input type="submit" name="Buscar" value="Buscar">
    </td>
   </tr>
  </table>
<?
 //mostramos el listado con los datos
 $productos=sql($query,"<br>Error al traer los productos<br>") or fin_pagina();
 ?>
 <table border=0 width=98% align=center>
  <tr>
   <td id=ma_sf>
    <table width="100%">
     <tr  id=ma_sf>
      <td align=left>
       <b>Total: </b><?=$total_productos?> productos
       <?
       if($_ses_user["login"]=="marcos")
       {?>
        <input type="submit" name="pasar_tipo" value="pasar_tipo" onclick="confirm('Esta seguro que desea actualizar los tipos de productos???')">
        <?
       }
       ?>
       </td>
       <td align="right">
        <?=($link_pagina)?$link_pagina:"&nbsp;"?>
       </td>
      </tr>
    </table>
   </td>
  </tr>
 </table>
 <table align="center" width="98%" class="bordes">
  <tr id="mo">
   <td width="70%">
    <a id=mo href='<?=encode_link("listado_productos.php",array("sort"=>"1","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Descripción
    </a>
   </td>
   <td width="20%">
    <a id=mo href='<?=encode_link("listado_productos.php",array("sort"=>"2","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Tipo de Producto
    </a>
   </td>
   <td width="10%">
    <a id=mo href='<?=encode_link("listado_productos.php",array("sort"=>"3","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Precio
    </a>
   </td>
  </tr>
  <?
  //generamos las filas de la tabla con los productos
  while (!$productos->EOF)
  {
   if($pagina_viene=="")
   {$ref=encode_link("detalle_producto_general.php",array("id_producto"=>$productos->fields["id_producto"]));
    $onclick_elegir="location.href='$ref'";
   }
   else //pagina_viene tiene un valor a donde debe volver
   {
    $onclick_elegir="document.all.id_producto_seleccionado.value=".$productos->fields["id_producto"].";";
    $onclick_elegir.="document.all.nombre_producto_elegido.value='".$productos->fields["desc_gral"]."';";
    $onclick_elegir.="document.all.tipo_producto_elegido.value='".$productos->fields["tipo"]."';";
    if($productos->fields["precio_licitacion"]!="")
     $onclick_elegir.="document.all.precio_producto_elegido.value=".$productos->fields["precio_licitacion"].";";
    $onclick_elegir.=$onclick_cargar;
   }

   ?>
   <tr style='cursor: hand;' <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
    <td>
     <?=$productos->fields["desc_gral"]?>
    </td>
    <td>
     <?=$productos->fields["tipo"]?>
    </td>
    <td>
     <table width="100%">
      <tr>
       <td>
        U$S
       </td>
       <td align="right">
        <?=formato_money($productos->fields["precio_licitacion"])?>
       </td>
      </tr>
     </table>
    </td>
   </tr>
   <?
   $productos->MoveNext();
  }//de while(!$productos->EOF)
  ?>
 </table>

<?
if($pagina_viene=="ord_compra.php")
{
?>
     <div align="center">
      <input type="button" name="Salir" value="Salir" onclick="window.close()">
     </div>
<?
}
?>
</form>
</body>
<br>
<?fin_pagina();?>