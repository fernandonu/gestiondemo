<?/*

----------------------------------------
 Autor: MAC
 Fecha: 01/07/2005
----------------------------------------

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.11 $
$Date: 2006/07/14 16:09:01 $

*/
include_once("../../config.php");
$pagina_viene=$parametros["pagina_viene"];
$onclick_cargar=$parametros["onclick_cargar"];

$var_sesion=array(
                  "tipo_producto"=>-1
				  );

variables_form_busqueda("producto_especifico",$var_sesion);

//traemos los productos

$orden = array (
    "default" => "1",
    "1" => "producto_especifico.descripcion",
    "2" => "tipo",
    "3" => "marca",
    "4" => "modelo",
    "5" => "precio_stock",
  );

$filtro = array (
    "producto_especifico.descripcion" => "Descripción",
    "marca"=>"Marca",
    "modelo"=>"Modelo",
    "precio_stock" => "Precio",
    "observaciones" => "Observaciones",
  );

//traemos los productos especificos
$query_productos="select id_prod_esp,producto_especifico.descripcion,marca,modelo,precio_stock,tipos_prod.descripcion as tipo,
                  producto_especifico.id_tipo_prod
                  from producto_especifico join tipos_prod using(id_tipo_prod)";

if($tipo_producto!=-1)
{$where.="(id_tipo_prod=$tipo_producto)";
}
else
 $where="";

$query="select id_tipo_prod,descripcion from tipos_prod order by descripcion";
$tipos_prod=sql($query,"<br>Error al traer los tipos de productos<br>") or fin_pagina();

echo $html_header;

$link_form=encode_link("listado_productos_especificos.php",array("pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar));
?>
<form action="<?=$link_form?>" method="POST" name="form1">
 <?
 //si se llamo a este listado para elegir un producto, ponemos
 //los hiddens para pasar datos a la pagina que ha llamado al listado
 if($pagina_viene)
 {?>
  <input type="hidden" name="id_producto_seleccionado" value="">
  <input type="hidden" name="nombre_producto_elegido" value="">
  <input type="hidden" name="marca_producto_elegido" value="">
  <input type="hidden" name="modelo_producto_elegido" value="">
  <input type="hidden" name="precio_producto_elegido" value="">
  <input type="hidden" name="tipo_producto_elegido" value="">
 <?
 }
 ?>
  <table width="98%" align="center" cellpadding="3">
   <tr>
    <td id=mo>
     <font size="2">Listado de Productos Específicos</font>
    </td>
   </tr>
  </table>
  <table width="98%" align="center">
   <tr>
    <td width="10%">
     <?
     if(permisos_check("inicio","permiso_agregar_nuevo_prod_esp"))
     {
      $link_nuevo_prod=encode_link("detalle_producto_especifico.php",array("es_nuevo"=>1));?>
      <input type="button" name="nuevo" value="Nuevo Producto" onclick="window.open('<?=$link_nuevo_prod?>')">
     <?
	 }
	 else
	  echo "&nbsp;";
	 ?>
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
   <td width="40%">
    <a id=mo href='<?=encode_link("listado_productos_especificos.php",array("sort"=>"1","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Descripción
    </a>
   </td>
   <td width="20%">
    <a id=mo href='<?=encode_link("listado_productos_especificos.php",array("sort"=>"2","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Tipo de Producto
    </a>
   </td>
   <td width="15%">
    <a id=mo href='<?=encode_link("listado_productos_especificos.php",array("sort"=>"3","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Marca
    </a>
   </td>
    <td width="15%">
    <a id=mo href='<?=encode_link("listado_productos_especificos.php",array("sort"=>"4","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Modelo
    </a>
   </td>
   <td width="10%">
    <a id=mo href='<?=encode_link("listado_productos_especificos.php",array("sort"=>"5","up"=>$up,"pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar))?>'>
     Precio
    </a>
   </td>
   <?
	 if(permisos_check("inicio","permiso_boton_cambiar_precio"))
	  {
	 ?>
	 <td id="mo">M.P.</td>
	 <?
	  }
	 ?>
  </tr>
  <?
  //generamos las filas de la tabla con los productos
  while (!$productos->EOF)
  {
   if($pagina_viene=="")
   {$ref=encode_link("detalle_producto_especifico.php",array("id_prod_esp"=>$productos->fields["id_prod_esp"]));
    $onclick_elegir="location.href='$ref'";
   }
   else
   {$onclick_elegir="document.all.id_producto_seleccionado.value=".$productos->fields["id_prod_esp"].";";
    $onclick_elegir.="document.all.nombre_producto_elegido.value='".str_replace("\""," Pulgadas ",$productos->fields["descripcion"])."';";
    $onclick_elegir.="document.all.marca_producto_elegido.value='".str_replace("\""," Pulgadas ",$productos->fields["marca"])."';";
    $onclick_elegir.="document.all.modelo_producto_elegido.value='".str_replace("\""," Pulgadas ",$productos->fields["modelo"])."';";
    $onclick_elegir.="document.all.tipo_producto_elegido.value='".str_replace("\""," Pulgadas ",$productos->fields["tipo"])."';";
    if($productos->fields["precio_stock"]!="")
     $onclick_elegir.="document.all.precio_producto_elegido.value=".$productos->fields["precio_stock"].";";
    $onclick_elegir.=$onclick_cargar;
   }
   ?>
   <tr style='cursor: hand;' <?=atrib_tr()?>>
    <td  onclick="<?=$onclick_elegir?>">
     <?=$productos->fields["descripcion"]?>
    </td>
    <td  onclick="<?=$onclick_elegir?>">
     <?=$productos->fields["tipo"]?>
    </td>
    <td  onclick="<?=$onclick_elegir?>">
     <?=$productos->fields["marca"]?>
    </td>
    <td onclick="<?=$onclick_elegir?>">
     <?=$productos->fields["modelo"]?>
    </td>
    <td onclick="<?=$onclick_elegir?>">
     <table width="100%">
      <tr>
       <td>
        U$S
       </td>
       <td align="right">
        <?=formato_money($productos->fields["precio_stock"])?>
       </td>
      </tr>
     </table>
    </td>
    <?
     $link=encode_link("../stock/stock_mod_precio.php",array("id_prod_esp"=>$productos->fields["id_prod_esp"]));
     if(permisos_check("inicio","permiso_boton_cambiar_precio"))
     {
     ?>
     	<td>
     		<input type=button name=boton_precio value=$ onclick="window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');" style="cursor:hand;">
     	</td>
     <?
     }//de if(permisos_check("inicio","permiso_boton_cambiar_precio"))
     ?>
   </tr>
   <?
   $productos->MoveNext();
  }//de while(!$productos->EOF)
  ?>
 </table>
</form>
</body>
<br>
<?fin_pagina();?>