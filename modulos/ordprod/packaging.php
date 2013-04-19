<?
/*
MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.4 $
$Date: 2006/05/10 18:24:19 $
*/
require_once("../../config.php");
require_once("../mov_material/func.php");

$id_licitacion=$parametros['id'] or $id_licitacion=$_POST['id_licitacion'];
$id_entrega_estimada=$parametros['id_entrega'] or $id_entrega=$_POST['id_entrega'];
$id_subir=$parametros['id_subir'] or $id_subir=$_POST['id_subir'];

if($_POST["Entregar"]=="Entregar Productos")
{
	$db->StartTrans();
	//traemos todas las filas de todos los PM de packaging de esta licitacion
	$query="select movimiento_material.deposito_origen,detalle_movimiento.id_movimiento_material,
	        detalle_movimiento.id_detalle_movimiento,detalle_movimiento.id_prod_esp,
	        detalle_movimiento.descripcion,detalle_movimiento.cantidad as cantidad_pedida,recibidos_mov.cantidad as cantidad_entregada
	        from mov_material.detalle_movimiento join mov_material.movimiento_material using(id_movimiento_material)
	        left join mov_material.recibidos_mov using(id_detalle_movimiento)
	        where id_licitacion=$id_licitacion and (ent_rec=0 or ent_rec isnull) and movimiento_material.pm_packaging=1
	        and movimiento_material.estado<>3
	        ";
	$filas_pm=sql($query,"<br>Error al traer las filas de PM para entregar los productos seleccionados<br>") or fin_pagina();

	while (!$filas_pm->EOF)
	{
		$id_detalle_movimiento=$filas_pm->fields["id_detalle_movimiento"];
		$id_movimiento_material=$filas_pm->fields["id_movimiento_material"];
		$deposito_origen=$filas_pm->fields["deposito_origen"];
		$id_prod_esp=$filas_pm->fields["id_prod_esp"];
		$descripcion=$filas_pm->fields["descripcion"];
		$cantidad_pedida=$filas_pm->fields["cantidad_pedida"];
		$cantidad_entregada=$filas_pm->fields["cantidad_entregada"];

		//si esta chequeado el check de la fila actual, la entregamos en su totalidad
	 	if($_POST["entregar_$id_detalle_movimiento"]==1)
	 	{
	 		//la cantidad a entregar es lo pedido menos lo que ya se entrego previamente
	 		$cant_insertar=$cantidad_pedida-$cantidad_entregada;
	 		//si la cantidad resultante es menor o igual que cero, algo anda mal, porque no se puede hacer una entrega
	 		//por una cantidad menor o igual que cero
	 		if($cant_insertar<=0)
	 		 die("Error interno: La cantidad a entregar es menor o igual que cero. No se puede entregar el Producto: $descripcion. Contacte a la division software.");
	 		entregar_material_sin_cb($id_movimiento_material,1,$id_detalle_movimiento,$cant_insertar,$id_prod_esp,$deposito_origen,$cantidad_pedida,$id_licitacion);

	 	}//de if($_POST["eliminar_$id_detalle_movimiento"]==1)

	 	$filas_pm->MoveNext();
	}//de while(!$filas_pm->EOF)

	echo "<div align='center'><b>Los productos seleccionados se entregaron con éxito</b></div>";

	$db->CompleteTrans();
}//de if($_POST["Entregar"]=="Entregar Productos")

echo $html_header;
?>
<title>Packaging para la Licitación Nº <?=$id_licitacion?></title>
<script>

var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido

function muestra_tabla(oimg,obj_tabla)
{
 if (obj_tabla.style.display=='none')
    {obj_tabla.style.display='inline';
     oimg.show=0;
     oimg.src=img_ext;
     oimg.title="Ocultar Tabla";

    }
 else
    {obj_tabla.style.display='none';
    oimg.show=1;
	oimg.src=img_cont;
	oimg.title="Mostrar Tabla";
    }
}//de function muestra_tabla(obj_tabla,nro)


function seleccionar_todos_local(elegir)
{
var valor,object;
            if(elegir.checked==true)
            	valor=true;
            else
            	 valor=false;
            var i=0;
            object=eval ("document.form1.prod_"+i);
            while (typeof(object)!='undefined'){
             	object.checked=valor;
                i++;
                object=eval ("document.form1.prod_"+i);
           	}//del while

}//de function seleccionar_todos_local(elegir)

function seleccionar_todos_entregar(elegir)
{
var valor,object;
            if(elegir.checked==true)
            	valor=true;
            else
            	 valor=false;
            var i=0;
            object=document.getElementById("check_entregar_"+i);alert(object);
            while (typeof(object)!='undefined')
            {
             	object.checked=valor;
                i++;
                object=document.getElementById("check_entregar_"+i);
           	}//del while


}//de function seleccionar_todos_entregar(elegir)

function control()
{
 var valor;
 var valor1;
 var des,cantidad;
 valor=eval ("document.form1.cantidad_maquinas");
 valor1=0;
 cantidad=eval ("document.form1.cantidad");
 if(valor.value=="")
 {
  alert("Falta ingresar la cantidad de PC");
  return false;
 }
 var i=0,t=1;
 while(i<cantidad.value)
 {
  canti=eval ("document.form1.cant_"+i);
  des=eval ("document.form1.desc_"+i);
  var producto=eval ("document.form1.prod_"+i);
  valor1=parseInt(canti.value);
  if((t==1)&&(producto.checked))
  {
   t=0;
  }
  if((producto.checked)&&(parseInt(valor.value)>parseInt(valor1)))
  {
   alert("La cantidad de PC ingresada es mayor a la cantidad de '"+des.value+"' disponibles en Stock Buenos Aires");
   return false;
  }
  i++;
 }//de while(i<cantidad.value)

 if(t==1)
 {
  alert("No hay seleccionado un producto");
  return false;
 }
 return true;
}//de function control()


function agregar_seleccionados()
{
	var cantidad,i,prod,index_array=0;
	var seleccionados=new Array();
	cantidad=eval("document.form1.cantidad.value");
	for(i=0;i<cantidad;i++)
	{
		if(eval("document.all.prod_"+i)!="undefined")
		{
			prod=eval("document.all.prod_"+i);
			if(prod.checked)
			{seleccionados[index_array]=prod.value;
			 index_array++;
			}
		}
	}//de for(i=0;i<cantidad;i++)

	//asignamos el arreglo al hidden, esto hace que el hidden tenga valores separados por coma
	document.all.id_prod_seleccionados.value=seleccionados;
}//de function agregar_seleccionados()

</script>
<?
$sql="select producto_especifico.descripcion,cant_disp,id_tipo_prod,id_prod_esp,codigo
      from general.producto_especifico left join general.tipos_prod using(id_tipo_prod)
      left join stock.en_stock using(id_prod_esp) join general.depositos using(id_deposito)
      where cant_disp>0 and codigo='packaging' and depositos.nombre='Buenos Aires'
      order by producto_especifico.descripcion
      ";
$res=sql($sql) or fin_pagina();

$link_pm=encode_link("../mov_material/detalle_movimiento.php",array("pm_packaging"=>1,"id_licitacion"=>$id_licitacion,
                                                         "id_entrega_estimada"=>$id_entrega_estimada,
                                                         "pedido_material"=>1,
                                                         "deposito_origen"=>2));


//traemos el color del estado y la entidad de la licitacion
$query="select color,estado.nombre,entidad.id_entidad,entidad.nombre as nbre_entidad
        from licitacion join estado using(id_estado) join entidad using(id_entidad)
        where id_licitacion=$id_licitacion";
$estado_lic=sql($query,"<br>Error al traer el color del estado de la licitacion<br>") or fin_pagina();
$estado_lic_color=$estado_lic->fields["color"];
$estado_lic_nombre=$estado_lic->fields["nombre"];
$link_pres = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));

?>
<form name="form1" action="<?=$link_pm?>" method="POST">
<table align="center" width="80%">
 <tr>
  <td align="center">
   <b><font size="4" color="Blue">PACKAGING</font></b>
  </td>
 </tr>
</table>
<table width="80%" border="1" cellspacing="1" cellpadding="1" align="center">
<tr id="mo">
  <td align="center" valign="top" colspan="2">
   <table>
    <tr>
     <td>
      <b>Licitación</b>
     </td>
     <td bgcolor="<?=$estado_lic_color?>" title="Estado de la Licitación: <?=$estado_lic_nombre?>">
      <?
      $frente="#000000";
      $reemplazo="#ffffff";
      $color_link=contraste($estado_lic_color, $frente, $reemplazo);?>
      <b><a href="<?=$link_pres?>" target="_blank"><U><?=$id_licitacion?></U></A></b>
     </td>
     <td>
       <b>&nbsp;N° de PC's</b> <input type="text" name="cantidad_maquinas" value="" size="5">
     </td>
    </tr>
   </table>
  </td>
 </TR>
 <tr id="mo">
  <td align="left" valign="top">
   <input class='estilos_check' type=checkbox name="selec_todos" onclick="seleccionar_todos_local(this)" checked>
   &nbsp;
   <b>Producto</b>
  </td>
  <td>
   <b>Cantidad Disponible</b>
  </td>
  </tr>
 <?
 $i=0;
 $menor=-1;
 while (!$res->EOF)
 {
 	if($res->fields['cant_disp']!="")
 	{?>
	 <tr>
	 <td>
	 <input class='estilos_check' type=checkbox name="prod_<?=$i?>" value='<?=$res->fields["id_prod_esp"]?>' checked onclick="if(!this.checked)document.all.selec_todos.checked=0">
	 &nbsp;
	 <b><?=$res->fields['descripcion']?></b>
	 <input type="hidden" name="desc_<?=$i?>" value="<?=$res->fields['descripcion']?>">
	 </td>
	 <td align="right">
      <b>
	   <?=$res->fields['cant_disp'];?>
       <input type="hidden" name="cant_<?=$i?>" value="<?=$res->fields['cant_disp']?>">
      </b>
	 </td>
	 </tr>
	 <?
	 if($menor==-1){
	 $menor=$res->fields['cant_disp'];
	 }
	 if($menor>$res->fields['cant_disp']){
	 $menor=$res->fields['cant_disp'];
	 }
	 $i++;
 	}
	 $res->MoveNext();
 }//de while (!$res->EOF)
?>
<input type="hidden" name="cantidad" value="<?=$i?>">
<input type="hidden" name="menor" value="<?=$menor?>">
<input type="hidden" name="cantid" value="<?=$cantid?>">
<input type="hidden" name="id_prod_seleccionados" value="">
</table>
<table align="center" width="80%">
     <tr>
      <td width="70%">
       <table width="100%" class="bordes" align="right">
	    <tr>
	     <td>
	      <b>NOTA: Este módulo utiliza solo productos de tipo "Packaging"</b>
	     </td>
	    </tr>
	   </table>
      </td>
      <td width="20%" align="center">
       <input name="generar" type="submit"  value="Generar PM" onclick="agregar_seleccionados();return control()">
       <input name="cerrar" type="button"  value="Cerrar" onclick="window.close()">
      </td>
    </tr>
</table>
</form>
<hr>
<?
 $query="select sum(detalle_movimiento.cantidad) as cantidad_total,detalle_movimiento.descripcion,detalle_movimiento.id_prod_esp
		 from mov_material.movimiento_material join mov_material.detalle_movimiento using(id_movimiento_material)
		 where id_licitacion=$id_licitacion and pm_packaging=1 and movimiento_material.estado<>3
		 group by detalle_movimiento.descripcion,detalle_movimiento.id_prod_esp
		 order by descripcion
		  ";
 $productos_pm=sql($query,"<br>Error al traer los PM de packaging realizados para esta licitación<br>") or fin_pagina();
 ?>
<table width="90%" align="center" class="bordes">
 <tr id="mo">
  <td align="center" width="3%">
	 	<img id="imagen_4" src="<?=$img_ext?>" border=0 title="Ocultar Tabla" align="left" style="cursor:hand;" onclick="muestra_tabla(this,document.all.tabla_pm_utilizados);" >
	  </td>
  <td id="mo">
     Cantidades ya utilizada en Pedidos de Material de packaging para la Licitación Nº <?=$id_licitacion?>
  </td>
 </tr>
</table>
<table width="90%" align="center" class="bordes" id="tabla_pm_utilizados">
  <tr id="ma">
   <td align="center">
    Producto
   </td>
   <td>
    Cantidad
   </td>
  </tr>
  <?
  while (!$productos_pm->EOF)
  {
  	?>
  	<tr bgcolor='<?=$bgcolor_out?>'>
  	 <td>
  	  <input type="hidden" name="id_prod_esp_<?=$i?>" value="<?=$productos_pm->fields["id_prod_esp"]?>">
  	  <?=$productos_pm->fields["descripcion"]?>
  	 </td>
  	 <td align="right">
  	  <b><?=$productos_pm->fields["cantidad_total"]?></b>
  	 </td>
  	</tr>
  	<?
    $productos_pm->MoveNext();
  }//de while(!$productos_pm->EOF)
  ?>
</table>
<hr>
<!--LOS DOS FORMULARIOS SON NECESARIOS PORQUE LOS BOTONES SUBMIT VAN A PAGINAS DIFERENTES
  	NO SE PUEDE SACAR ESTO PORQUE DEJA DE FUNCIONAR LA PAGINA
  -->
<form name="form_entregas" method="POST" action="packaging.php">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<input type="hidden" name="id_subir" value="<?=$id_subir?>">

<table width="90%" align="center" class="bordes">
 <tr id="mo">
  <td align="center" width="3%">
	 	<img id="imagen_2" src="<?=$img_cont?>" border=0 title="Mostrar Tabla" align="left" style="cursor:hand;" onclick="muestra_tabla(this,document.all.tabla_entregar_pm);" >
	  </td>
  <td id="mo">
    <?/*<input class='estilos_check' type=checkbox name="selec_todos_entregar" onclick="seleccionar_todos_entregar(this,'form_entregas.entregar')" title="Seleccionar todos">*/?>
    Pedidos de Material de packaging pendientes de entrega para la Licitación Nº <?=$id_licitacion?>
  </td>
 </tr>
</table>
<table width="90%" align="center" class="bordes" id="tabla_entregar_pm" style="display:none">
  <?
  //tramoes todos los PM en estado autorizados, para mostrar la parte de entregas
  $query="select id_movimiento_material
          from mov_material.movimiento_material
          where id_licitacion=$id_licitacion and estado=2 and pm_packaging=1
          order by id_movimiento_material
         ";
  $pm_lic=sql($query,"<br>Error al traer los PM autorizados de packaging para esta Licitación<br>") or fin_pagina();

  while (!$pm_lic->EOF)
  {
  	$id_mov=$pm_lic->fields["id_movimiento_material"];
  	$link = encode_link("../mov_material/detalle_movimiento.php",array("pagina"=>"listado","id"=>$id_mov));
  	?>
  	<tr>
  	 <td>
  	  <table width="100%" class="bordes">
	  	<tr id="ma">
	  	 <td>
	  	  Pedido de Material Nº <a href="<?=$link?>" target="_blank"><?=$id_mov?></a>
	  	 </td>
	  	</tr>
	  	<tr>
	     <td>
	      <?generar_form_entrega("packaging",$id_mov,1,0)?>
	     </td>
	    </tr>
	  </table>
	 </td>
	</tr>
	<tr>
  	 <td>
  	  <hr>
  	 </td>
  	</tr>
  	<?

   	$pm_lic->MoveNext();
  }//de while(!$pm_lic->EOF)*/
  ?>
  <tr>
   <td align="center">
     <?
     if(permisos_check("inicio","permiso_entregar_sin_cb"))
     {
      ?>
      <input type="submit" name="Entregar" value="Entregar Productos">
     <?
     }//de if(permisos_check("inicio","permiso_entregar_sin_cb"))
     ?>
     <input name="cerrar" type="button"  value="Cerrar" onclick="window.close()">
   </td>
  </tr>
</table>
</form>
<?fin_pagina();?>