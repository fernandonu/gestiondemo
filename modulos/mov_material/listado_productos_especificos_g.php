<?/*

----------------------------------------
 Autor: Gabriel
 Fecha: 07/01/2006
 basada en /productos/listado_productos_especificos_g.php
----------------------------------------

MODIFICADA POR
$Author: gabriel $
$Revision: 1.4 $
$Date: 2006/01/18 20:25:13 $

*/
include_once("../../config.php");
$pagina_viene=$parametros["pagina_viene"] or $pagina_viene=$_POST["pagina_viene"];
$onclick_cargar=$parametros["onclick_cargar"] or $onclick_cargar=$_POST["onclick_cargar"];
$id=$parametros["id"] or $id=$_POST["id"];
$id_p=$parametros["id_p"] or $id_p=$_POST["id_p"];
$var_sesion=array("tipo_producto"=>-1, "tabIndex"=>$_POST["tabIndex"]);

variables_form_busqueda("producto_especifico",$var_sesion);

if (!$tabIndex){
	$tabIndex=2;
	
	$_ses_producto_especifico["tabIndex"]=$tabIndex;
  phpss_svars_set("_ses_producto_especifico", $_ses_producto_especifico);
}
$orden = array (
    "default" => "1",
    "1" => "producto_especifico.descripcion",
    "2" => "tipo",
    "3" => "producto_especifico.marca",
    "4" => "producto_especifico.modelo",
    "5" => "producto_especifico.precio_stock",
  );

$filtro = array (
    "producto_especifico.descripcion" => "Descripción",
    "producto_especifico.marca"=>"Marca",
    "producto_especifico.modelo"=>"Modelo",
    "producto_especifico.precio_stock" => "Precio",
    "producto_especifico.observaciones" => "Observaciones",
  );

//traemos los productos especificos
$query_productos="select id_prod_esp,producto_especifico.descripcion,marca,modelo,precio_stock,tipos_prod.descripcion as tipo,
                  producto_especifico.id_tipo_prod
                  from producto_especifico join tipos_prod using(id_tipo_prod)";

if($tipo_producto!=-1){
	$where.="(id_tipo_prod=$tipo_producto)";
}

$query="select id_tipo_prod,descripcion from tipos_prod order by descripcion";
$tipos_prod=sql($query,"<br>Error al traer los tipos de productos<br>") or fin_pagina();

echo $html_header;
$link_form=encode_link("listado_productos_especificos_g.php",array("pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar, "id"=>$id));
?>
<script>

	function tab(indice){
		var tabla_1=document.getElementById('tabla_1');
		var tabla_2=document.getElementById('tabla_2');
		var tabla_3=document.getElementById('tabla_3');
		
		if (indice==1){
			tabla_1.style.display='inline';
			tabla_2.style.display=tabla_3.style.display='none';
			
			document.all.botones.rows[0].cells[0].style.backgroundColor='#cccccc';
			document.all.botones.rows[0].cells[0].style.color='#006699';
			document.all.botones.rows[0].cells[1].style.backgroundColor='#006699';
			document.all.botones.rows[0].cells[1].style.color='#cccccc';
			document.all.botones.rows[0].cells[2].style.backgroundColor='#006699';
			document.all.botones.rows[0].cells[2].style.color='#cccccc';
			document.all.tabIndex.value=1;
		}else if (indice==2){
			tabla_2.style.display='inline';
			tabla_1.style.display=tabla_3.style.display='none';
			
			document.all.botones.rows[0].cells[0].style.backgroundColor='#006699';
			document.all.botones.rows[0].cells[0].style.color='#cccccc';
			document.all.botones.rows[0].cells[1].style.backgroundColor='#cccccc';
			document.all.botones.rows[0].cells[1].style.color='#006699';
			document.all.botones.rows[0].cells[2].style.backgroundColor='#006699';
			document.all.botones.rows[0].cells[2].style.color='#cccccc';
			document.all.tabIndex.value=2;
		}else if (indice==3){
			tabla_3.style.display='inline';
			tabla_1.style.display=tabla_2.style.display='none';
			
			document.all.botones.rows[0].cells[0].style.backgroundColor='#006699';
			document.all.botones.rows[0].cells[0].style.color='#cccccc';
			document.all.botones.rows[0].cells[1].style.backgroundColor='#006699';
			document.all.botones.rows[0].cells[1].style.color='#cccccc';
			document.all.botones.rows[0].cells[2].style.backgroundColor='#cccccc';
			document.all.botones.rows[0].cells[2].style.color='#006699';
			document.all.tabIndex.value=3;
		}
	}
	
	function changeSort(val){
		document.all.sort.value=val;
		document.all.form1.submit();
	}
</script>
<form action="<?=$link_form?>" method="POST" id="form1">
  <input type="hidden" name="id_producto_seleccionado" value="">
  <input type="hidden" name="nombre_producto_elegido" value="">
  <input type="hidden" name="marca_producto_elegido" value="">
  <input type="hidden" name="modelo_producto_elegido" value="">
  <input type="hidden" name="precio_producto_elegido" value="">
  <input type="hidden" name="id" value="<?=$id?>">
  <input type="hidden" name="id_p" value="<?=$id_p?>">
  <input type="hidden" name="tabIndex" value="<?=$tabIndex?>">
  <input type="hidden" name="sort" value="<?=$sort?>">
  <input type="hidden" name="onclick_cargar" value="<?=$onclick_cargar?>">
  <input type="hidden" name="pagina_viene" value="<?=$pagina_viene?>">
  <input type="hidden" name="keyword" value="<?=$keyword?>">

  <table width="98%" align="center" cellpadding="3">
   <tr>
    <td id=mo>
     <font size="2">Listado de Productos Específicos</font>
    </td>
   </tr>
  </table>
  <table border="1" bordercolor="black" cellspacing="2" cellpadding="2" width="98%" align="center" id="botones">
  	<tr>
  		<td width="33%" id="<?=(($tabIndex==1)?"ma":"mo")?>" onclick="tab(1);" style="cursor:'hand'">Recomendadas</td>
  		<td width="33%" id="<?=(($tabIndex==2)?"ma":"mo")?>" onclick="tab(2);" style="cursor:'hand'">Ordenes de compras</td>
  		<td width="33%" id="<?=(($tabIndex==3)?"ma":"mo")?>" onclick="tab(3);" style="cursor:'hand'">Listado general</td>
  	</tr>
  </table>
<?
//tabla del listado de productos recientes
?>
<table width="98%" align="center">
   <tr>
    <td width="10%">
     <?if(permisos_check("inicio","permiso_agregar_nuevo_prod_esp")){?>
      <input type="button" name="nuevo" value="Nuevo Producto" onclick="window.open('<?=encode_link("../productos/detalle_producto_especifico.php",array("es_nuevo"=>1))?>')">
     <?}
	 else echo "&nbsp;";
	 ?>
    </td>
    <td align="center">
     <?
     $link_tmp=array("pagina_viene"=>$pagina_viene,"onclick_cargar"=>$onclick_cargar, "id"=>$id, "tabIndex"=>$tabIndex);
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
      while (!$tipos_prod->EOF){?>
       <option value="<?=$tipos_prod->fields["id_tipo_prod"]?>" <?if($tipos_prod->fields["id_tipo_prod"]==$tipo_producto) echo "selected"?>>
        <?=$tipos_prod->fields["descripcion"]?>
       </option>
       <?
       $tipos_prod->MoveNext();
      }//de while(!$tipos_prod->EOF)
      $tipos_prod->moveFirst();
      ?>
     </select>&nbsp;
     <input type="submit" name="Buscar" value="Buscar">
    </td>
   </tr>
  </table>
  <table border=0 width=98% align=center id="tabla_1" style="display:none" bgcolor="<?=$bgcolor3?>">
		<tr>
			<td>
<?
	$query_usados="select id_prod_esp,producto_especifico.descripcion,marca,modelo,precio_stock,tipos_prod.descripcion as tipo,
                  producto_especifico.id_tipo_prod
                  from general.producto_especifico 
			join general.tipos_prod using(id_tipo_prod)
			join (
				select distinct id_prod_esp
				from 
					(
						select *
						from mov_material.producto_lista_material
						where id_producto=$id_p
						order by fecha desc
					)as tmp0
				limit 15 offset 0
			)as tmp using(id_prod_esp)";
	if (($pos=stripos($query, "where"))>0) $query_usados.=substr($query, $pos);
	elseif (($pos=stripos($query, "order"))>0) $query_usados.=substr($query, $pos);
//mostramos el listado con los datos
$productos=sql($query_usados,"<br>Error al traer los productos recomendados<br>") or fin_pagina();
?>
				<table align="center" width="100%" class="bordes">
			  	<tr id="mo">
	  				<td width="40%" onclick="changeSort(1)" style="cursor:'hand'">Descripción</td>
			  	 	<td width="20%" onclick="changeSort(2)" style="cursor:'hand'">Tipo de Producto</td>
	   				<td width="15%" onclick="changeSort(3)" style="cursor:'hand'">Marca</td>
  	 				<td width="15%" onclick="changeSort(4)" style="cursor:'hand'">Modelo</td>
	   				<td width="10%" onclick="changeSort(5)" style="cursor:'hand'">Precio</td>
  				</tr>
  <?
  $i=0;
  while ((!$productos->EOF)&&($i<15)){
   if($pagina_viene==""){
   	$ref=encode_link("detalle_producto_especifico_g.php",array("id_prod_esp"=>$productos->fields["id_prod_esp"]));
    $onclick_elegir="location.href='$ref'";
   }else{
   	$onclick_elegir="document.all.id_producto_seleccionado.value=".$productos->fields["id_prod_esp"].";";
    $onclick_elegir.="document.all.nombre_producto_elegido.value='".$productos->fields["descripcion"]."';";
    $onclick_elegir.="document.all.marca_producto_elegido.value='".$productos->fields["marca"]."';";
    $onclick_elegir.="document.all.modelo_producto_elegido.value='".$productos->fields["modelo"]."';";
    if($productos->fields["precio_stock"]!="")
     $onclick_elegir.="document.all.precio_producto_elegido.value=".$productos->fields["precio_stock"].";";
    $onclick_elegir.=$onclick_cargar;
   }
   ?>
			   	<tr style='cursor: hand;' <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
    				<td><?=$productos->fields["descripcion"]?></td>
				    <td><?=$productos->fields["tipo"]?></td>
  	  			<td><?=$productos->fields["marca"]?></td>
    				<td><?=$productos->fields["modelo"]?></td>
	    			<td>
	    				<table width="100%">
			    	  	<tr>
		  			    	<td>U$S</td>
       						<td align="right"><?=formato_money($productos->fields["precio_stock"])?></td>
			      		</tr>
     					</table>
			    	</td>
			   	</tr>
<?
		$i++;
   $productos->MoveNext();
  }//de while(!$productos->EOF)
?>
				</table>
			</td>
		</tr>
	</table>
<?
 //tabla del listado de productos para la orden de compra desde la que se llama
?>
<table border=0 width=98% align=center id="tabla_2" style="display:inline" bgcolor="<?=$bgcolor3?>">
	<tr>
		<td>
<?
$query_oc="select producto_especifico.id_prod_esp, producto_especifico.descripcion, marca, modelo, precio_stock, 
	tipos_prod.descripcion as tipo, producto_especifico.id_tipo_prod
from general.producto_especifico
	join general.tipos_prod using(id_tipo_prod)
	left join compras.log_rec_ent using (id_prod_esp)
	left join compras.recibido_entregado using (id_recibido)
	left join compras.fila using (id_fila)
	left join compras.orden_de_compra using (nro_orden)
where (ent_rec=1) and (id_licitacion=$id)";

if (($pos=stripos($query, "where"))>0) {
	$query_oc.=" and ".substr($query, $pos+6);
}else if (($pos=stripos($query, "order"))>0) {
	$query_oc.=substr($query, $pos);
}
if (($pos=stripos($query_oc, "limit"))>0) {
	$query_oc=substr($query_oc, 0, $pos);
}
 //mostramos el listado con los datos
 $productos_oc=sql($query_oc,"<br>Error al traer los productos de las órdenes de compra para esta licitación<br>") or fin_pagina();
?>
				<table border=0 width=100% align=center>
  				<tr>
				   	<td id=ma_sf>
  	  				<table width="100%">
    	 					<tr  id=ma_sf>
      						<td align=left><b>Total: </b><?=$productos_oc->recordCount()?> productos</td>
	      				</tr>
  	  				</table>
   					</td>
 					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table align="center" width="100%" class="bordes">
			  	<tr id="mo">
	  				<td width="40%" onclick="changeSort(1)" style="cursor:'hand'">Descripción</td>
			  	 	<td width="20%" onclick="changeSort(2)" style="cursor:'hand'">Tipo de Producto</td>
	   				<td width="15%" onclick="changeSort(3)" style="cursor:'hand'">Marca</td>
  	 				<td width="15%" onclick="changeSort(4)" style="cursor:'hand'">Modelo</td>
	   				<td width="10%" onclick="changeSort(5)" style="cursor:'hand'">Precio</td>
  				</tr>
  <?
  while (!$productos_oc->EOF){
   if($pagina_viene==""){
   	$ref=encode_link("detalle_producto_especifico.php",array("id_prod_esp"=>$productos_oc->fields["id_prod_esp"]));
    $onclick_elegir="location.href='$ref'";
   }else{
   	$onclick_elegir="document.all.id_producto_seleccionado.value=".$productos_oc->fields["id_prod_esp"].";";
    $onclick_elegir.="document.all.nombre_producto_elegido.value='".$productos_oc->fields["descripcion"]."';";
    $onclick_elegir.="document.all.marca_producto_elegido.value='".$productos_oc->fields["marca"]."';";
    $onclick_elegir.="document.all.modelo_producto_elegido.value='".$productos_oc->fields["modelo"]."';";
    if($productos_oc->fields["precio_stock"]!="")
     $onclick_elegir.="document.all.precio_producto_elegido.value=".$productos_oc->fields["precio_stock"].";";
    $onclick_elegir.=$onclick_cargar;
   }
   ?>
			   	<tr style='cursor: hand;' <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
    				<td><?=$productos_oc->fields["descripcion"]?></td>
				    <td><?=$productos_oc->fields["tipo"]?></td>
  	  			<td><?=$productos_oc->fields["marca"]?></td>
    				<td><?=$productos_oc->fields["modelo"]?></td>
	    			<td>
	    				<table width="100%">
			    	  	<tr>
		  			    	<td>U$S</td>
       						<td align="right"><?=formato_money($productos_oc->fields["precio_stock"])?></td>
			      		</tr>
     					</table>
			    	</td>
			   	</tr>
<?
   $productos_oc->MoveNext();
  }//de while(!$productos->EOF)
?>
				</table>
			</td>
		</tr>
	</table>
<?
 //tabla del listado general de productos específicos
?>
<table border=0 width=98% align=center id="tabla_3" style="display:none" bgcolor="<?=$bgcolor3?>">
	<tr>
		<td>
<?
 //mostramos el listado con los datos
 $productos=sql($query,"<br>Error al traer los productos<br>") or fin_pagina();
?>
	<table border=0 width=100% align=center>
  	<tr>
	   	<td id=ma_sf>
  	  	<table width="100%">
    	 		<tr  id=ma_sf>
      			<td align=left><b>Total: </b><?=$total_productos?> productos</td>
      			<td align="right"><?=($link_pagina)?$link_pagina:"&nbsp;"?></td>
	      	</tr>
  	  	</table>
   		</td>
 		</tr>
	</table>
</td></tr>
<tr><td>
 	<table align="center" width="100%" class="bordes">
  	<tr id="mo">
	  	<td width="40%" onclick="changeSort(1)" style="cursor:'hand'">Descripción</td>
			  	 	<td width="20%" onclick="changeSort(2)" style="cursor:'hand'">Tipo de Producto</td>
	   				<td width="15%" onclick="changeSort(3)" style="cursor:'hand'">Marca</td>
  	 				<td width="15%" onclick="changeSort(4)" style="cursor:'hand'">Modelo</td>
	   				<td width="10%" onclick="changeSort(5)" style="cursor:'hand'">Precio</td>
  	</tr>
  <?
  //generamos las filas de la tabla con los productos
  while (!$productos->EOF){
   if($pagina_viene==""){
   	$ref=encode_link("detalle_producto_especifico.php",array("id_prod_esp"=>$productos->fields["id_prod_esp"]));
    $onclick_elegir="location.href='$ref'";
   }else{
   	$onclick_elegir="document.all.id_producto_seleccionado.value=".$productos->fields["id_prod_esp"].";";
    $onclick_elegir.="document.all.nombre_producto_elegido.value='".str_replace("\"", " pulgadas ", $productos->fields["descripcion"])."';";
    $onclick_elegir.="document.all.marca_producto_elegido.value='".str_replace("\"", " pulgadas ", $productos->fields["marca"])."';";
    $onclick_elegir.="document.all.modelo_producto_elegido.value='".str_replace("\"", " pulgadas ", $productos->fields["modelo"])."';";
    if($productos->fields["precio_stock"]!="")
     $onclick_elegir.="document.all.precio_producto_elegido.value=".$productos->fields["precio_stock"].";";
    $onclick_elegir.=$onclick_cargar;
   }
   ?>
   	<tr style='cursor: hand;' <?=atrib_tr()?> onclick="<?=$onclick_elegir?>">
    	<td><?=$productos->fields["descripcion"]?></td>
	    <td><?=$productos->fields["tipo"]?></td>
  	  <td><?=$productos->fields["marca"]?></td>
    	<td><?=$productos->fields["modelo"]?></td>
	    <td>
  	  	<table width="100%">
    	  	<tr>
		      	<td>U$S</td>
       			<td align="right"><?=formato_money($productos->fields["precio_stock"])?></td>
      		</tr>
     		</table>
    	</td>
   	</tr>
   <?
   $productos->MoveNext();
  }//de while(!$productos->EOF)
  ?>
	</table>
</td></tr></table>
<input type="hidden" name="up" value="<?=$up?>">
</form>
<script>tab(<?=$tabIndex?>);</script>
</body>
<br>
<?fin_pagina();?>