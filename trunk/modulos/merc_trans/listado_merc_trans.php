<?php 
/*
AUTOR: MAC 
FECHA: 28/07/04

$Author: elizabeth $
$Revision: 1.6 $
$Date: 2004/09/01 20:08:47 $
*/
require_once("../../config.php");
require_once("../stock/funciones.php");

$usuario=$_ses_user["name"];
$fecha_actual=date("Y-m-d H:m:s",mktime());

$id_deposito=$parametros["id_deposito"];
$deposito="Mercadería en Tránsito";
$pagina_listado="listado_merc_trans.php";
if ($id_deposito=="") {
					   $sql="select id_deposito from depositos where nombre='$deposito'";
					   $resultado=$db->execute($sql) or die ($db->errormsg()."<br>".$sql);
					   $id_deposito=$resultado->fields["id_deposito"];
					   }
phpss_svars_set("_ses_id_deposito", $id_deposito);
phpss_svars_set("_ses_deposito", $deposito);
phpss_svars_set("_ses_pagina_listado",$pagina_listado);
phpss_svars_set("_ses_es_inventario",0);

function  calcular_monto_stock($id_deposito){
global $db;
$sql=" select sum(precio*cant_disp) as monto,id_deposito ";
$sql.=" from general.precios ";
$sql.=" join stock on";
$sql.=" (precios.id_producto=stock.id_producto and precios.id_proveedor=stock.id_proveedor)";
$sql.="  where id_deposito=$id_deposito";
$sql.="  group by id_deposito";
$sql.="  order by id_deposito";

$resultado=$db->execute($sql) or die($sql);
$monto=$resultado->fields["monto"];
//echo $sql;
return $monto;
}

$pagina_oc=$parametros["pagina_oc"] or $pagina_oc=$_POST['pagina_oc'];
/*
if ($parametros['print'])
{
	$_POST=$parametros;
	$print=$parametros['print'];
}
*/
//print_r($parametros);

variables_form_busqueda("listado_merc_trans");
if ($cmd == "" ){
				$cmd="mov_entre_stock";
				phpss_svars_set("_ses_listado_merc_trans_cmd", $cmd);
				}

$pagina_listado=$_ses_pagina_listado;
$sort = $_POST["sort"] or $sort=$parametros["sort"];
$up   = $_POST["up"] or $up=$parametros["up"];
if ($id_deposito==""){
	$id_deposito=$_ses_id_deposito;
	$deposito=$_ses_deposito;
}

/*para el menu con los tipos de productos*/
$codigo_post=$_POST["tipo_producto"];
if ($codigo_post=="") $codigo_post="todos";

/* para el menu con los tipos de productos*/
if ($_POST["precios"]=="precios") 
{
    $db->starttrans();
    $sql="select * from productos";
    $productos=$db->execute($sql) or die($sql."<br>".$db->errormsg());

    for($i=0;$i<$productos->recordcount();$i++)
     {
      $id_producto=$productos->fields["id_producto"];

      $sql="select sum(precio) as precios,count(id_producto) as cantidad,id_producto
             from precios where id_producto=$id_producto
             group by id_producto
             ";
      $precios=$db->execute($sql) or die($sql."<br>".$db->errormsg());
      $nuevo_precio_stock=0;
      if ($precios->recordcount()>0) {
                $nuevo_precio_stock=($precios->fields["precios"]/$precios->fields["cantidad"]);
                $sql="update productos set precio_stock=$nuevo_precio_stock where id_producto=$id_producto";
                $db->execute($sql) or die($sql."<br>".$db->errormsg());

      }

     $productos->movenext();
     }


    $db->completetrans();
} // del scrip que actualiza lso precios

//fin de la base de datos
$datos_barra = array(
				array(
					"descripcion"    => "Movimientos Entre Stock",
					"cmd"            => "mov_entre_stock"
					),
				array(
					"descripcion"    => "Movimientos de OC para Stock",
					"cmd"            => "mov_oc_stock"
					),
			    array(
					"descripcion"    => "Movimientos Restantes",
					"cmd"            => "mov_restantes"
					)
				);

//consulta para obtener todo el stock de los depositos
   $sql="
   select productos.desc_gral,stock.id_deposito,stock.id_producto,orden_de_compra.notas_internas,
          mercaderia_transito.id_movimiento_material,stock.comentario_inventario,mercaderia_transito.id_mercaderia_transito,
          mercaderia_transito.nro_orden,mercaderia_transito.comentarios,mercaderia_transito.cantidad,mercaderia_transito.fecha_inicio,
          proveedor.razon_social,proveedor.id_proveedor,productos.precio_stock,orden_de_compra.flag_stock,
          orden_de_compra.orden_prod,orden_de_compra.nrocaso,orden_de_compra.id_licitacion,
          orden_de_compra.es_presupuesto, destino_oc.nombre_destino_oc
   ";
   if($cmd=="mov_entre_stock")
    $sql.=",movimiento_material.comentarios as coment_mat";
   $sql.=" from stock
   join general.productos using(id_producto)
   join general.tipos_prod on (tipos_prod.codigo=productos.tipo)
   join mercaderia_transito using (id_deposito,id_producto,id_proveedor)
   join proveedor using (id_proveedor)
   left join orden_de_compra using(nro_orden)
   left join destino_oc using(id_destino_oc)";
   if($cmd=="mov_entre_stock")
    $sql.=" left join movimiento_material using(id_movimiento_material)";
   $where=" id_deposito=$id_deposito and mercaderia_transito.cantidad > 0";
   if ($codigo_post!="todos")
			 {
			 $where.=" and  tipo='$codigo_post'";
			 }

  if ($print)
	// if ($print)
		$where_print=$where;
$where="$where $group";

/*
filtros los datos de acuerdo a si son: 
   Movimientos entre stocks (tienen id_movimiento_material)
   Movimientos de OC para Stock (tienen el nro_orden y estas estan 
                                 acosiadas a stock)
   Movimientos restantes (tienen nro de orden pero no estan asociada a stock)
*/

switch ($cmd)
{//la variable param_extra es para avisarle a la pagina detalle_merc_trans
 //desde cual estamos viniendo
 case "mov_entre_stock":
                       $param_extra=1;
                       $where.=" and (not id_movimiento_material isnull)";
                       break;
 case "mov_oc_stock":  $param_extra=2;
                       $where.=" and (not nro_orden isnull and flag_stock=1)";
                       break;
 case "mov_restantes": $param_extra=3;
                       $where.=" and (not nro_orden isnull and flag_stock=0 or (nro_orden isnull and id_movimiento_material isnull))";
                       break;
 
} 
   
   $orden=array(
		  "default" => "2",
		  "1" => "cantidad",
		  "2" => "desc_gral",
          "3" =>  "precio_stock",
          "4" =>  "nro_orden",
          "5" =>  "id_movimiento_material",
          "6" =>  "razon_social",
          "7" => "fecha_inicio"
           );

   $filtro= array(
		"desc_gral" => "Descripción",
		"cantidad"=>"Stock Disponible",
        "nro_orden" => "Nº Orden de Compra",
        "id_movimiento_material" => "Nº Movimiento Material",
        "razon_social" => "Proveedor",
        "id_licitacion"=>"ID Licitación"
		 );

   $contar="buscar";
   $link=encode_link("listado_merc_trans.php",array("id_deposito"=>$id_deposito,"deposito"=>$deposito,"cmd"=>$cmd));
/*
	if ($print)
	{
	   //para que haga la consulta y no imprima el form
		ob_start();
		$itemspp=1000000;
	   $q=" select cantidad, stock.id_producto, productos.desc_gral,razon_social ";
	   $q.=" from stock ";
	   $q.=" join general.productos using(id_producto)";
	   $q.=" join general.depositos using(id_deposito)";
	   $q.=" left join general.proveedor using(id_proveedor)";
           $q.=" join general.tipos_prod on (tipos_prod.codigo=productos.tipo)";
	   list($q_print,$total,$link_pagina,$up) = form_busqueda($q,$orden,$filtro,$link_tmp,$where_print,$contar);
	   ob_clean();
	   $q_print=eregi_replace("ORDER.*","order by desc_gral",$q_print);
	   require("stock_imprimir.php");die;
	}
*/
?>
<script>
function control_datos(datos){
return confirm('Esta seguro que desea  '+datos+' el/los productos');
}
</script>
<?=$html_header?>
<br>
<input type=hidden name="id_deposito" value="<?=$id_deposito?>">
<input type=hidden name="deposito" value="<?=$deposito?>">
<?
$exito=$parametros["exito"] or $exito=$exito;
if ($exito) Aviso($exito);
?>


<table width=98% align=Center border=0>
  <tr>
   <td colspan=4  align=center>
	<b>
	<font size='4' color='blue'>
    <?
	 echo "$deposito - En Curso";
	?>
	</font>
	</b>
   </td>
  </tr>
  <tr>
  <td colspan=4>
	<?
	generar_barra_nav($datos_barra);
	?>

  </td>
  </tr>

  <tr>
  <td align=Center  colspan=4>

  <!-- Tendria que separarlos a todos y pasarles el cmd -->
  <table width=100% align=center border=0>
  <tr>
   <td align=center bgcolor="white">

   <b>
   <font color=black>
   Monto: <?echo "u\$s&nbsp;".formato_money(calcular_monto_stock($id_deposito));?>
   </font>
   </b>
   </td>
   <form action='<?=$_SERVER["PHP_SELF"]?>' method='post'>
   <input type=hidden name="id_dep_ext" value="<?=$deposito?>">
   <td align=center>
   <?
   list($query_productos,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
   
   ?>
   </td>
   <td align=center>
  <select name="tipo_producto" style="width:120">
   <?php
   $sql="select descripcion, codigo from tipos_prod order by descripcion";
   $resultado_desc = $db->Execute($sql) or die($db->ErrorMsg());
   while (!$resultado_desc->EOF){

   $codigo=$resultado_desc->fields['codigo'];
   $descripcion=$resultado_desc->fields['descripcion'];
   
   if ($codigo_post==$codigo){
						$selected="selected";
						}
						else
						$selected="";
  if ($codigo_post=="todos") $selected="selected";
   ?>
   <option value="<?=$codigo?>" <?=$selected?> > <?=$descripcion;?></option>
   <?php
   $resultado_desc->MoveNext();
   }
   ?>
   <option value="todos" <?=$selected?>>Todos
   </select>

   </td>
  <td>
  <input type=submit name=form_busqueda value='Buscar'>
   &nbsp;
  </td>
</form>
<form name="form1" method=POST action="<?=$link?>">
<?/*  
<td>
   if($cmd=='mov_entre_stock') 
  { ?>
		<b>
        <a target="_blank" href="<? $_POST['print']=1; echo encode_link("listado_merc_trans.php",$_POST) ?>">
        <!--
        <input name="imagen_print" type="image" src="../../imagenes/printer.GIF" width="24" height="24" border="0">
        -->Imprimir
	    </a>
        </b>
		<?
        }
     </td>*/
        ?>
     </tr>
   </table>
 </td>
  </tr>
  <tr>
  <td>
  <table border=0 width=99% cellspacing=0 cellpadding=1 bordercolor='' align=center>
  <tr>
  <td  align=left id="ma_sf">
	<b>Total: <?=$total?></b></td>
  <td align=right id="ma_sf">
  <?=$link_pagina?>
  </td>
  </tr>
  </table>

  </td>
  </tr>
  <tr>
   <td colspan=4>
	 <table width=100% align=Center>
    	 <tr>
		 <td id="mo"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>1,"up"=>$up));?>">Cant</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>2,"up"=>$up));?>">Descripci&oacute;n Producto</a></td>
		<?
		if($cmd!="mov_entre_stock")//si es movimiento de material, no mostramos la OC porque no se aplica
		{?>
		 <td id="mo"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>4,"up"=>$up));?>" title="Nº de Orden de Compra">OC</a></td>
		 <td id="mo">Tipo OC</td>
        <?}
        if($cmd=="mov_entre_stock")
        {?>
		 <td id="mo"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>5,"up"=>$up));?>" title="ID Movimiento Material">ID Mov Mat</a></td>
		<?
        }
        ?>
		 <td id="mo"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>6,"up"=>$up));?>">Proveedor</a></td>
                 <td id="mo"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>3,"up"=>$up));?>">Monto</a></td>
         <!--        <td id="mo" title="Días del producto en Tránsito">Días</td>   -->
                 <td id="mo" title="Días del producto en Tránsito"><a href="<?=encode_link("listado_merc_trans.php",array("sort"=>7,"up"=>$up));?>">Días</a></td>
                 <td id="mo">M.P.</td>
         <? if ($cmd!="mov_entre_stock")
            { ?>
              <td id="mo">Destino OC</td> 
         <? }?>           
		 </tr>
		 <?
		 $resultado=$db->execute($query_productos) or die($db->errormsg()."<br>".$query_productos);
                 $cantidad=$resultado->recordcount();
                 for ($i=0;$i<$cantidad;$i++){
                                  $id_info_rma=$resultado->fields["id_info_rma"];
       		 $ref = encode_link("detalle_merc_trans.php",array("id_producto"=>$resultado->fields["id_producto"],
	  	   					    "id_deposito"=>$id_deposito,"id_proveedor"=>$resultado->fields["id_proveedor"],
	  	   					    "id_mercaderia_transito"=>$resultado->fields["id_mercaderia_transito"],"pagina_listado"=>"en_curso","ubicacion"=>$param_extra
							 ));


   $onclick="onClick=\"location.href='$ref'\";";
  
   //calculamos hace cuanto días que el producto está en tránsito
   $hoy=date("d/m/Y",mktime());
   $fecha_init=fecha($resultado->fields["fecha_inicio"]);
   $dif_dias=diferencia_dias($fecha_init,$hoy); 
   $fondo=$bgcolor_out;
   if($dif_dias>4)
    $fondo="red";
   ?>
   <tr <?=atrib_tr($fondo);?> title='<?=$comentario?>'>
            <td align=right <?=$onclick?>><b><?echo $resultado->fields["cantidad"];?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["desc_gral"]?></b></td>
           <?  
           if($cmd!="mov_entre_stock")//si es movimiento de material, no mostramos la OC porque no se aplica
		   {?>
		    <td align=left <?=$onclick?> title="<?=$resultado->fields["notas_internas"]?>"><b><?echo $resultado->fields["nro_orden"]?></b></td>
		    <?
		    //decidimos el tipo de la orden de compra
		    if ($resultado->fields['id_licitacion']!='' && $resultado->fields['es_presupuesto']==1) 
            {$tipo="Pres";
             $titulo="Presupuesto";
            }
            elseif ($resultado->fields['id_licitacion']!='') 
            {$tipo="Lic";
             $titulo="Licitacion";      
            }	 
            if ($resultado->fields['nrocaso']!='')
            {$tipo="ServT";
             $titulo="Servicio Técnico";          
            }	     
            if ($resultado->fields['flag_stock']==1)
            {$tipo="Stock";
             $titulo="Stock Coradir";          
            }	             
           if ($resultado->fields['orden_prod']!='')
           {$tipo="RMA";
            $titulo="RMA de Producción";          
           }	 
		    
            ?>
		    <td align=left <?=$onclick?> title="<?=$titulo?>"><b><?=$tipo?></b></td>
         <?}
           if($cmd=="mov_entre_stock")
           {?>
	  	    <td align=left  <?=$onclick?> title="<?=$resultado->fields["coment_mat"]?>"><b><?echo $resultado->fields["id_movimiento_material"]?></b></td>
		   <?
           }
           ?> 
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["razon_social"]?></b></td>
            <td align=right <?=$onclick?> <?=$coment_precio?>>
            <table width=100% align=Center>
            <tr>
              <td width=20% align=center><b>U$S</b></td>
              <td align=right><b><?echo formato_money($resultado->fields["precio_stock"])?></b></td>
            </tr>
            </table>
            </td>
           <td <?=$onclick?> align="center"><b><?=$dif_dias?></b></td>
            <?
            $link=encode_link("../stock/stock_mod_precio.php",array("id_producto"=>$resultado->fields["id_producto"]));
            ?>
            <td><input type=button name=boton_precio value=$ onclick="window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1');" style="cursor:hand;"></td>
            <? if ($cmd!="mov_entre_stock"){?>
             <td align=left  title="<?=$resultado->fields["coment_mat"]?>"><b><?echo $resultado->fields["nombre_destino_oc"]?></b></td> 
            <? } ?>        
        </tr>
          <?
           $resultado->movenext();
        }//del for
	 ?>

</table>
</td>
</tr>
</table>
</form>
<?
fin_pagina();
?>