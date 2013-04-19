<?
/*
AUTOR: MAC
FECHA: 29/05/06

MODIFICADO POR:
$Author: marco_canderle $
$Revision: 1.19 $
$Date: 2006/07/18 15:25:09 $
*/

require_once("../../config.php");
include_once("funciones_stock_completo.php");
$msg=$parametros['msg'];

$modo=$parametros["modo"] or $modo=$_GET["modo"] or $modo=$_POST["modo"] or $modo="por_tipo";
$id_tipo_prod=$_POST["id_tipo_prod"] or $id_tipo_prod=$_GET["id_tipo_prod"] or $id_tipo_prod=$parametros["id_tipo_prod"];


if($_POST["ajustar_rma_historial"]=="Ajustar RMA Historial (tabla en_stock)")
{
	$db->StartTrans();
	//traemos los id_en_stock de RMA que tienen diferencias entre lo que dice la cant_disp de en_stock
	//y las cantidades de info_rma para el mismo id_en_stock
	$query="select distinct id_en_stock from(
			select info_rma.id_info_rma,info_rma.id_en_stock,info_rma.cantidad,cant_comp.cant_rma,cant_comp.cant_disp
			from stock.info_rma join stock.estado_rma using(id_estado_rma)
			join(select sum(info_rma.cantidad) as cant_rma,en_stock.id_en_stock,en_stock.cant_disp
				 from stock.info_rma join stock.en_stock using(id_en_stock)
				 where id_deposito=9
				 group by en_stock.id_en_stock,en_stock.cant_disp
				)as cant_comp using(id_en_stock)
			where estado_rma.nombre_corto='B' and cant_comp.cant_rma<>cant_comp.cant_disp
			order by info_rma.id_en_stock)as a";
	$dif_cant=sql($query,"<br>Error al traer los id_en_stock con cantidades diferentes<br>") or fin_pagina();

	$diferentes=array();
	while (!$dif_cant->EOF)
	{
	 	$diferentes[sizeof($diferentes)]=$dif_cant->fields["id_en_stock"];

	 	$dif_cant->MoveNext();
	}//de while(!$dif_cant->EOF)

	$query="select info_rma.id_info_rma,info_rma.id_en_stock,info_rma.cantidad,cant_comp.cant_rma,cant_comp.cant_disp
			from stock.info_rma join stock.estado_rma using(id_estado_rma)
			join(select sum(info_rma.cantidad) as cant_rma,en_stock.id_en_stock,en_stock.cant_disp
				 from stock.info_rma join stock.en_stock using(id_en_stock)
				 where id_deposito=9
				 group by en_stock.id_en_stock,en_stock.cant_disp
				)as cant_comp using(id_en_stock)
			where estado_rma.nombre_corto='B'";
	$rma_ajust=sql($query,"<br>Error al traer los datos de rma historial para ajustar<br>") or fin_pagina();

	//por cada entrada de historial de RMA, reducimos la cantidad en la tabla de en_stock
	$contador=0;
	while (!$rma_ajust->EOF)
	{
		$cantidad_descontar=$rma_ajust->fields["cantidad"];
	 	$id_en_stock=$rma_ajust->fields["id_en_stock"];

		if(!in_array($id_en_stock,$diferentes))
		{
			$query="update stock.en_stock set cant_disp=cant_disp-$cantidad_descontar where id_en_stock=$id_en_stock";
			sql($query,"<br>Error al actualizar el id_en_stock: $id_en_stock, reduciendo la cantida en: $cantidad_descontar<br>") or fin_pagina();

			$contador++;
		}//de if(!in_array($id_en_stock,$diferentes))


	 	$rma_ajust->MoveNext();
	}//de while(!$rma_ajust->EOF)

	echo "Entradas actualizadas: $contador<br><br>";

	echo "ID en stock no afectados que hay que revisar: ";print_r($diferentes);
	echo "TRANSACCION NO CERRADA";
	//$db->CompleteTrans();
}//de if($_POST["ajustar_rma_historial"]=="Ajustar RMA Historial (tabla en_stock)")



echo $html_header;

$var_sesion=array(
                  "tipo_movi"=>"todos",
                  "tipo_producto"=>"todos",
                  "es_inventario"=>0,
                  "ingresos" =>-1,
                  "egresos" =>-1,
                  );

variables_form_busqueda("stock_coradir",$var_sesion);


if ($_GET["modo"])
{
	$cmd="real";
	$keyword="";
	$filter="";
	$_ses_stock_coradir["cmd"]=$cmd;
	phpss_svars_set("_ses_stock_coradir", $_ses_stock_coradir);
}

/*para el menu con los tipos de productos*/
$codigo_post=$_ses_stock["tipo_producto"];
if ($codigo_post=="")
          $codigo_post="todos";
/* para el menu con los tipos de productos*/

/*para el menu con los tipos de movimiento*/
$tipo_mov_post=$_ses_stock["tipo_movi"];
if ($tipo_mov_post=="")
          $tipo_mov_post="todos";
/* para el menu con los tipos de movimiento*/



$datos_barra = array(
					array(
						"descripcion"	=> "Real",
						"cmd"			=> "real"
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						)
				 );
?>
<center>
<div align="center" class="bordes" style="width:95%">
 <?
 if($modo=="por_producto")
  $titulo_pagina="Listado Completo de Productos en Stock Coradir";
 else //modo=="por_tipo"
  $titulo_pagina="Listado Completo de Productos en Stock Agrupados por Tipo";
 ?>
 <font color="Blue" size="3"><b><?=$titulo_pagina?></b></font>
</div>
</center>
<?
//generar_barra_nav($datos_barra);

$link_form=encode_link("stock_completo.php",array("modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod));
?>
<form name="form1" method="POST" action="<?=$link_form?>">
<input type="hidden" name="modo" value="<?=$modo?>">
<?
if($modo=="por_producto")
{
	$orden=array(
			  "default" => "3",
			  "default_up" => "$up",
			  "1" => "cant_total",
			  "2" => "cant_disp",
			  "3" => "descripcion",
			  "4" => "tipo_descripcion",
	          "5" =>  "precio_stock",
	          "6" => "monto_total",
	           );


	   $filtro= array(
			"descripcion" => "Descripción"
			        );
}//de if($modo=="por_producto")
else //if($modo=="por_tipo")
{

	$orden=array(
		  "default" => "1",
		  "default_up" => "$up",
		  "1" => "tipo_descripcion",
		  "2" => "cant_total",
		  "3" => "cant_disp",
		  "4" => "cant_reservada",
		  "5" => "cant_a_confirmar",
          "6" => "monto_total",
           );


   $filtro= array(
		"tipo_descripcion" => "Tipo Producto"
        );
}//del else de if($modo=="por_producto")

//obtenemos el id del deposito RMA
$query="select depositos.id_deposito from general.depositos where depositos.nombre='RMA'";
$dep_rma=sql($query,"<br>Error al traer el id del deposito RMA<br>") or fin_pagina();
$id_deposito_rma=$dep_rma->fields["id_deposito"];

$query_depositos="select depositos.nombre,depositos.id_deposito
			   from general.depositos
			   where tipo>=0
			   order by depositos.nombre";
$dep_en_cuenta=sql($query_depositos,"<br>Error al traer los depósitos tomados en cuenta<br>") or fin_pagina();

$first=$_POST["form_busqueda"];

$where="";

$add_where=agregar_dep_consulta($dep_en_cuenta,$first);
if($add_where!="")
	$add_where="where ($add_where)";


if($id_tipo_prod!="" && $id_tipo_prod!="todos")
{
	if($where!="")
		$where.=" and ";
	$where.=" stock.id_tipo_prod=$id_tipo_prod";
}

$add_filtro_avanzado=add_consulta_filtro_avanzado();

if($add_filtro_avanzado!="")
{
	if($where!="")
		$where.=" and ";
	$where.="($add_filtro_avanzado)";
}//de if($add_filtro_avanzado!="")


if($modo=="por_producto")
{
			$query="select stock.descripcion,stock.precio_stock,stock.tipo_descripcion,stock.id_tipo_prod,2 as id_moneda,stock.id_prod_esp,
					COALESCE(stock.monto_total,0) as monto_total,
					COALESCE(stock.monto_total_disp,0) as monto_total_disp,
					COALESCE(stock.monto_total_res,0) as monto_total_res,
					COALESCE(stock.monto_total_conf,0) as monto_total_conf,
					COALESCE(stock.cant_total,0) as cant_total,
					COALESCE(stock.cant_reservada,0) as cant_reservada,
					COALESCE(stock.cant_a_confirmar,0) as cant_a_confirmar,
					COALESCE(stock.cant_disp,0) as cant_disp
				from(
				    select producto_especifico.descripcion,producto_especifico.precio_stock,tipos_prod.descripcion as tipo_descripcion,producto_especifico.id_prod_esp,tipos_prod.id_tipo_prod,
				       (sum(en_stock.cant_disp)+sum(en_stock.cant_reservada)+sum(en_stock.cant_a_confirmar))*precio_stock as monto_total,
				       (sum(en_stock.cant_disp))*precio_stock as monto_total_disp,
				       (sum(en_stock.cant_reservada))*precio_stock as monto_total_res,
				       (sum(en_stock.cant_a_confirmar))*precio_stock as monto_total_conf,
				       (sum(en_stock.cant_disp)+sum(en_stock.cant_reservada)+sum(en_stock.cant_a_confirmar))as cant_total,
					   sum(en_stock.cant_reservada) as cant_reservada,sum(en_stock.cant_disp)as cant_disp,sum(en_stock.cant_a_confirmar)as cant_a_confirmar
					from stock.en_stock join general.producto_especifico using(id_prod_esp)
					join general.tipos_prod using(id_tipo_prod) join general.depositos using (id_deposito)
					$add_where
					group by producto_especifico.descripcion,producto_especifico.precio_stock,tipos_prod.descripcion,producto_especifico.id_prod_esp,tipos_prod.id_tipo_prod
				)as stock

				";
}//de if($modo=="por_producto")
else //if($modo=="por_tipo")
{
	$query="select stock_por_tipo.tipo_descripcion,stock_por_tipo.id_tipo_prod,2 as id_moneda,monto_total,monto_total_disp,
			monto_total_conf,monto_total_res,cant_total,cant_reservada,cant_disp,cant_a_confirmar
			from
			(
				select stock_por_tipo.tipo_descripcion,stock_por_tipo.id_tipo_prod,2 as id_moneda,
				sum(monto_total) as monto_total,sum(monto_total_disp) as monto_total_disp,sum(monto_total_res) as monto_total_res,sum(monto_total_conf) as monto_total_conf,
				sum(cant_total)as cant_total,sum(cant_reservada) as cant_reservada,sum(cant_disp) as cant_disp,sum(cant_a_confirmar) as cant_a_confirmar
				from(select producto_especifico.descripcion,producto_especifico.precio_stock,tipos_prod.descripcion as tipo_descripcion,producto_especifico.id_prod_esp,tipos_prod.id_tipo_prod,
			               (sum(en_stock.cant_disp)+sum(en_stock.cant_reservada)+sum(en_stock.cant_a_confirmar))*precio_stock as monto_total,
			               (sum(en_stock.cant_disp))*precio_stock as monto_total_disp,
			               (sum(en_stock.cant_reservada))*precio_stock as monto_total_res,
			               (sum(en_stock.cant_a_confirmar))*precio_stock as monto_total_conf,
			               (sum(en_stock.cant_disp)+sum(en_stock.cant_reservada)+sum(en_stock.cant_a_confirmar))as cant_total,
			       		   sum(en_stock.cant_reservada) as cant_reservada,sum(en_stock.cant_disp)as cant_disp,sum(en_stock.cant_a_confirmar) as cant_a_confirmar
					 from stock.en_stock join general.producto_especifico using(id_prod_esp)
					 join general.tipos_prod using(id_tipo_prod) join general.depositos using (id_deposito)
					 $add_where
	   				 group by producto_especifico.descripcion,producto_especifico.precio_stock,tipos_prod.descripcion,producto_especifico.id_prod_esp,tipos_prod.id_tipo_prod
					) as stock_por_tipo
				group by stock_por_tipo.tipo_descripcion,stock_por_tipo.id_tipo_prod
			)as stock_por_tipo
			";

}//del else de if($modo=="por_producto")

?>
<script src="../../lib/NumberFormat150.js"></script>
<script>
function seleccionar_suma(elegir,check)
{
	var valor;

	if (typeof(check)!='undefined')
	{
	             if(elegir.checked==true)
	             {
                     valor=true;
	             }
                 else
                 {
	                 valor=false;
	             }

	            if (typeof(check.length)!='undefined')
	            {
	            	for(i=0;i<check.length;i++)
	            	{
	                    check[i].checked=valor;
	                }//del for
	            }//de if (typeof(check.length)!='undefined')
	            else
	            {
	             	check.checked=valor;
	            }
	 }//de if (typeof(check)!='undefined')

	 //refrescamos la suma de las filas seleccionadas
	 sumar_seleccion();

}//de function seleccionar_suma(elegir,check)

function sumar_seleccion()
{
	var subtotal_monto_total=parseFloat(0);
	var subtotal_monto_disp=parseFloat(0);
	var subtotal_cant_total=parseFloat(0);
	var subtotal_cant_disp=parseFloat(0);
	var mt,md,ct,cd,check_suma;

	check_suma=document.all.check_suma;

	//si hay un solo check, no se debe usar como arreglo sino como objeto comun
	if(document.all.cant_checks.value==1)
	{
		 if(check_suma.checked==1)
        {
        	mt=eval("document.all.h_monto_total_0.value");
        	md=eval("document.all.h_monto_total_disp_0.value");
        	ct=eval("document.all.h_cant_total_0.value");
        	cd=eval("document.all.h_cant_disp_0.value");
         	subtotal_monto_total+=parseFloat(mt);
         	subtotal_monto_disp+=parseFloat(md);
         	subtotal_cant_total+=parseFloat(ct);
         	subtotal_cant_disp+=parseFloat(cd);
        }//de if(check_suma[i].checked==1)
	}//de if(document.all.cant_checks.value==1)
	else
	{
		//sino, lo accedemos como arreglo
		for(i=0;i<parseInt(document.all.cant_checks.value);i++)
		{
	        if(check_suma[i].checked==1)
	        {
	        	mt=eval("document.all.h_monto_total_"+i+".value");
	        	md=eval("document.all.h_monto_total_disp_"+i+".value");
	        	ct=eval("document.all.h_cant_total_"+i+".value");
	        	cd=eval("document.all.h_cant_disp_"+i+".value");
	         	subtotal_monto_total+=parseFloat(mt);
	         	subtotal_monto_disp+=parseFloat(md);
	         	subtotal_cant_total+=parseFloat(ct);
	         	subtotal_cant_disp+=parseFloat(cd);
	        }//de if(check_suma[i].checked==1)

	    }//del for(i=0;i<check_suma.length;i++)
	}//del else de if(document.all.cant_checks.value==1)

    document.all.suma_total.value=formato_money(subtotal_monto_total);
    document.all.suma_disp.value=formato_money(subtotal_monto_disp);
    document.all.cant_total.value=subtotal_cant_total;
    document.all.cant_disp.value=subtotal_cant_disp;

}//de function sumar_seleccion()


var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla,oimg,h_desp)
{

	if (obj_tabla.style.display=='none')
	{
		obj_tabla.style.display='block';
		oimg.show=0;
		oimg.src=img_ext;
		h_desp.value=1;
	}
	else
	{
		obj_tabla.style.display='none';
		oimg.show=1;
		oimg.src=img_cont;
		h_desp.value=0;
	}
}//de function muestra_tabla(obj_tabla,oimg,h_desp)
</script>


<input type="hidden" name="id_tipo_prod" value="<?=$id_tipo_prod?>">
<table width="95%" align="center">
 <tr>
  <td align="center">
   <?depositos_considerados($dep_en_cuenta,$first)?>
  </td>
 </tr>
 <tr>
  <td align="center">
	<?

	$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "monto_total",
 		"mask" => array("U\$S")
		);

	$contar="buscar";
	$link_extra=array("modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod);
    list($sql,$total_stock_coradir,$link_pagina,$up,$suma_total) = form_busqueda($query,$orden,$filtro,$link_extra,$where,$contar,$sumas);
	$result=sql($sql,"<br>Error en la consutla de busqueda Stock Coradir<br>") or fin_pagina();
	?>
  </td>
 </tr>
 <tr>
  <td>
   <?$parametros_filtro=mostrar_filtro_avanzado();?>
  </td>
 </tr>
 <tr>
   <td align="center">
	<input type=submit name=form_busqueda value='Buscar'>
   </td>
 </tr>
 <tr>
  <td>
	<table bgcolor="White" width="100%">
	 <tr>
	  <td>
	  	<b>Suma Total: <font color="Blue">U$S <input type="text" name="suma_total" value="0.00" class="text_8" readonly size="15"></b></font>
	  </td>
	  <td align="right">
	  	<b>Cantidad Total: <font color="Blue"><input type="text" name="cant_total" value="0" class="text_8" readonly size="8"></b></font>
	  </td>
	 </tr>
	 <tr>
	  <td>
	    <b>Suma Disponible: <font color="Blue">U$S <input type="text" name="suma_disp" value="0.00" class="text_8" readonly size="15"></b></font>
	  </td>
	  <td align="right">
	    <b>Cantidad Disponible: <font color="Blue"><input type="text" name="cant_disp" value="0" class="text_8" readonly size="8"></b></font>
	  </td>
	 </tr>
	</table>
  </td>
 </tr>
</table>
<?
if ($msg) Aviso($msg);

if($modo=="por_producto")
 $colspan=8;
else
 $colspan=7;
?>
<table width='95%' border='0' cellspacing='2' align="center">
 <tr id=ma>
  <td colspan="<?=$colspan?>">
    <table width="100%">
     <tr id=ma>
	    <td align="left" width="20%">
	     <b>Total:</b> <?=$total_stock_coradir?>
	    </td>
	    <td width="60%" align="center">
	     Monto Total en Listado: <?=$suma_total?>
	    </td>
		<td align=right width="20%">
		 <?=$link_pagina?>
		</td>
	 </tr>
	</table>
  </td>
 </tr>
 <tr id=mo>
  <?
  if($modo=="por_producto")
  {
  ?>
      <td width="1%"><input type="checkbox" name="seleccionar_todos" value="1" onclick="seleccionar_suma(this,document.all.check_suma)"></td>
	  <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"1","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>' title="Cantidad Total en Stock">Total</a></b></td>
	  <td width='5%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"2","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>' title="Cantidad Disponible">Disp.</a></b></td>
	  <td width='30%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"4","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>'>Descripción Producto</a></b></td>
	  <td width='25%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"3","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>'>Tipo de Producto</a></b></td>
	  <td width='15%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"5","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>'>Precio U.</a></b></td>
	  <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"6","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>'>Monto Total</a></b></td>
  <?
  }//de if($modo=="por_producto")
  else //if($modo=="por_tipo")
  {
  ?>
  	  <td width="1%"><input type="checkbox" name="seleccionar_todos" value="1" onclick="seleccionar_suma(this,document.all.check_suma)"></td>
      <td width='50%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"1","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>'>Tipo de Producto</a></b></td>
	  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"2","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>' title="Cantidad Total en Stock">Total</a></b></td>
	  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"3","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>' title="Cantidad Disponible">Disponible</a></b></td>
	  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"4","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>' title="Cantidad Reservada">Reservada</a></b></td>
	  <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"5","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>' title="Cantidad a Confirmar">A Confirmar</a></b></td>
	  <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array_merge(array("sort"=>"6","up"=>$up,"modo"=>$modo,"id_tipo_prod"=>$id_tipo_prod),$parametros_filtro))?>'>Monto Total</a></b></td>
  <?
  }//del else de if($modo=="por_producto")
  ?>
 </tr>
<?
$index_check=0;
while(!$result->EOF)
{
  if($modo=="por_producto")
  {
	 $color_env_rec="";
	 $link = encode_link("stock_coradir_detalle_completo.php",array("pagina"=>"listado","id_prod_esp"=>$result->fields["id_prod_esp"]));
	 $onclick_celda="onclick='window.open(\"$link\")'";
	?>
	 <tr <?=atrib_tr()?> style="cursor:hand">
	  <td width="1%">
	  	<input type="checkbox" name="check_suma" value="1" onclick="sumar_seleccion()">
	  	<input type="hidden" name="h_cant_total_<?=$index_check?>" value="<?=$result->fields["cant_total"]?>">
	  	<input type="hidden" name="h_cant_disp_<?=$index_check?>" value="<?=$result->fields['cant_disp'];?>">
	  	<input type="hidden" name="h_monto_total_<?=$index_check?>" value="<?=$result->fields['monto_total']?>">
	  	<input type="hidden" name="h_monto_total_disp_<?=$index_check?>" value="<?=$result->fields['monto_total_disp']?>">
	  </td>
	  <td align="center" <?=$onclick_celda?>>
	   <b>
	    <?=$result->fields["cant_total"]?>
	   </b>
	  </td>
	  <?
	  $title_fila="Cantidad Reservada: ".$result->fields['cant_reservada']."\nCantidad a Confirmar: ".$result->fields['cant_a_confirmar'];
	  ?>
	  <td align="center" title="<?=$title_fila?>" <?=$onclick_celda?>>
	   <b>
	    <?=$result->fields['cant_disp'];?>
	   </b>
	  </td>
	  <td <?=$onclick_celda?>>
	   <b>
	    <?=$result->fields['descripcion']?>
	   </b>
	  </td>
	  <td <?=$onclick_celda?>>
	   <b>
	    <?=$result->fields['tipo_descripcion']?>
	   </b>
	  </td>
	  <td <?=$onclick_celda?>>
	   <table width="100%">
	    <tr>
	     <td>
		  <b>U$S</b>
		 </td>
		 <td align="right">
		  <b><?=formato_money($result->fields['precio_stock'])?></b>
		 </td>
		</tr>
	   </table>
	  </td>
	  <?$title_monto_fila="Monto Total Disponible: U\$S ".formato_money($result->fields['monto_total_disp']);
	    $title_monto_fila.="\nMonto Total Reservado: U\$S ".formato_money($result->fields['monto_total_res']);
	    $title_monto_fila.="\nMonto Total a Confirmar: U\$S ".formato_money($result->fields['monto_total_conf']);
	  ?>
	  <td title="<?=$title_monto_fila?>" <?=$onclick_celda?>>
	   <table width="100%">
	    <tr>
	     <td>
		  <b>U$S</b>
		 </td>
		 <td align="right">
		  <b><?=formato_money($result->fields['monto_total'])?></b>
		 </td>
		</tr>
	   </table>
	  </td>
	 </tr>
	 <?
  }//de if($modo=="por_producto")
  else //if($modo=="por_tipo")
  {
  	$link = encode_link("stock_completo.php",array("id_tipo_prod"=>$result->fields['id_tipo_prod'],"modo"=>"por_producto"));
	?>
  	<tr <?=atrib_tr()?> style="cursor:hand">
	  <td width="1%">
	  	<input type="checkbox" name="check_suma" value="1" onclick="sumar_seleccion()">
	  	<input type="hidden" name="h_cant_total_<?=$index_check?>" value="<?=$result->fields["cant_total"]?>">
	  	<input type="hidden" name="h_cant_disp_<?=$index_check?>" value="<?=$result->fields['cant_disp'];?>">
	  	<input type="hidden" name="h_monto_total_<?=$index_check?>" value="<?=$result->fields['monto_total']?>">
	  	<input type="hidden" name="h_monto_total_disp_<?=$index_check?>" value="<?=$result->fields['monto_total_disp']?>">
	  </td>
	  <a href="<?=$link?>">
	  <td>
	   <b>
	    <?=$result->fields['tipo_descripcion']?>
	   </b>
	  </td>
	  <td align="center">
	   <b>
	    <?=$result->fields["cant_total"]?>
	   </b>
	  </td>
	  <td align="center" title="Cantidad Total Reservada: <?=$result->fields["cant_reservada"]?>">
	   <b>
	    <?=$result->fields['cant_disp'];?>
	   </b>
	  </td>
	  <td align="center">
	   <b>
	    <?=$result->fields['cant_reservada'];?>
	   </b>
	  </td>
	  <td align="center">
	   <b>
	    <?=$result->fields['cant_a_confirmar'];?>
	   </b>
	  </td>
	  <td title="Monto Total Disponible: U$S <?=formato_money($result->fields['monto_total_disp'])?>">
	   <table width="100%">
	    <tr>
	     <td>
		  <b>U$S</b>
		 </td>
		 <td align="right">
		  <b><?=formato_money($result->fields['monto_total'])?></b>
		 </td>
		</tr>
	   </table>
	  </td>
	  </a>
	 </tr>
  <?
  }//del else de if($modo=="por_producto")

  $index_check++;

  $result->MoveNext();
}//de while(!$result->EOF)
?>
<input type="hidden" name="cant_checks" value="<?=$index_check?>">
</table>
<?/*
if($_ses_user["login"]=="marcos")
{?>
	<input type="submit" name="ajustar_rma_historial" value="Ajustar RMA Historial (tabla en_stock)">
<?
}//de if($_ses_user["login"]=="marcos")*/
?>
</form>
<?fin_pagina();?>