<?php
/*>
AUTOR: Broggi 
FECHA: 15/09/2004

$Author: ferni $
$Revision: 1.30 $
$Date: 2005/11/02 19:26:05 $
*/
require_once("../../config.php");
require_once("funciones.php");


$usuario=$_ses_user["name"];
$fecha_actual=date("Y-m-d H:m:s",mktime());
$keyword=$parametros["keyword"] or $keyword="";
$filter=$parametros["filter"] or $filter="";
$id_deposito=$parametros["id_deposito"];
$deposito="RMA";
$pagina_listado="listado_rma.php";

if($_POST["borrar_rma"]=="Eliminar de RMA")
{ 	
	$db->StartTrans(); //inicia la transaccion
	$guardado=0;
	for ($cont=0; $cont < 50 ;$cont++)
	{
		if ($_POST["selec_$cont"]=="on")
		{
			$guardado=1; 
			//lo uso para mprimir valores
			/*printf ("check: %s ",$_POST["selec_$cont"]);
			printf ("id info rma: %s ",$_POST["id_info_rma_$cont"]);
			printf ("id deposito: %s ",$_POST["id_deposito_$cont"]);
			printf ("id producto: %s ",$_POST["id_producto_$cont"]);
			printf ("id proveedor: %s ",$_POST["id_proveedor_$cont"]);
			printf ("cantidad: %s ---> ",$_POST["cantidad_$cont"]);*/
			//cargo las variables para la sentecia sql
			$id_info_rma=$_POST["id_info_rma_$cont"];
			$id_deposito=$_POST["id_deposito_$cont"];
			$id_producto=$_POST["id_producto_$cont"];
 			$id_proveedor=$_POST["id_proveedor_$cont"];
 			$cantidad_1=$_POST["cantidad_$cont"];
 			//sql
 			descontar_stock($cantidad_1,$id_producto,$id_proveedor,$id_deposito,$id_info_rma,2);		
 			$fecha_hoy=date("Y-m-d H:i:s",mktime());
			$sql="update info_rma set user_historial='".$_ses_user['name']."',fecha_historial='$fecha_hoy',garantia_vencida=1 where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
			$consulta_sql=sql($sql) or fin_pagina();
		}//del if si esta chequeado
	}//del for recorre 50 veces para ver lo que estan chequeados (mejora: pasar la variable i con un hidden)
	$db->CompleteTrans();//completa transaccion
	
	if($guardado)
	 echo "<b><center>Los productos de RMA seleccionados se eliminaron con éxito</center></b>";
}//de if($_POST["borrar_rma"]=="Eliminar de RMA")

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

$sql=" select sum(precio_stock*cant_disp) as monto,id_deposito
        from general.precios
        join stock.stock on (precios.id_producto=stock.id_producto and precios.id_proveedor=stock.id_proveedor)
        join general.productos   on (stock.id_producto=productos.id_producto)
        where id_deposito=$id_deposito
        group by id_deposito
        order by id_deposito
        ";
$resultado=sql($sql) or fin_pagina();
$monto=$resultado->fields["monto"];
//echo $sql;
return $monto;
}

$pagina_oc=$parametros["pagina_oc"] or $pagina_oc=$_POST['pagina_oc'];

variables_form_busqueda("listado_rma");
if ($cmd == "" ){
				$cmd="real";
				phpss_svars_set("_ses_listado_rma_cmd", $cmd);
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
if ($_POST["precios"]=="precios"){
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
					"descripcion"    => "Transito",
					"cmd"            => "tran"
					),
				array(
					"descripcion"    => "Coradir",
					"cmd"            => "cor"
					),
				array(
					"descripcion"    => "Proveedor",
					"cmd"            => "prov"
					),  
				array(
					"descripcion"    => "Todas",
					"cmd"            => "real"
					),
				array(
					"descripcion"    => "Historial",
					"cmd"            => "historial"
					)
				 );

//consulta para obtener todo el stock de los depositos
if ($cmd=="real" || $cmd=="tran" || $cmd=="cor" || $cmd=="prov")
   {
   $sql="
   select garantia_vencida,info_rma.cantidad, stock.id_producto,stock.comentario_inventario,ubicacion.nombre_corto,fecha,
          stock.id_deposito,productos.tipo, control_stock.fecha_modif,
          productos.desc_gral,productos.marca,productos.modelo,
          productos.precio_stock,info_rma.id_info_rma,info_rma.id_movimiento_material,
          tipos_prod.descripcion,proveedor.razon_social,proveedor.id_proveedor,info_rma.nrocaso,
          info_rma.nro_ordenp,info_rma.nro_ordenc,info_rma.id_nota_credito
    from stock
   join general.productos using(id_producto)
   join general.depositos using (id_deposito)
   join general.tipos_prod on (tipos_prod.codigo=productos.tipo)
   join info_rma using (id_deposito,id_producto,id_proveedor)
   join proveedor using (id_proveedor)
   join stock.descuento using (id_info_rma,id_deposito,id_producto,id_proveedor)
   left join ubicacion using (id_ubicacion)
   join stock.control_stock using (id_control_stock)
   left join stock.log_ubicacion using (id_info_rma,id_deposito,id_producto,id_proveedor,id_ubicacion)";
   $where=" stock.id_deposito=$id_deposito and cantidad > 0 ";
   if ($codigo_post!="todos")
			 {
			 $where.=" and  productos.tipo='$codigo_post'";
			 }
	switch ($cmd)
	{case "tran": $where.=" and ubicacion.nombre_corto='T'";
	              break;
	 case "cor": $where.=" and ubicacion.nombre_corto='C'";
	             break;
	 case "prov": $where.=" and ubicacion.nombre_corto='P'";                          	                
	};
	/*if ($cmd=="tran") $where.=" and ubicacion.nombre_corto='T'";
	if ($cmd=="cor") $where.=" and ubicacion.nombre_corto='C'";
	if ($cmd=="prov") $where.=" and ubicacion.nombre_corto='P'";*/
				 
   
$where="$where $group";

   $orden=array(
		  "default" => "2",
		  "1" => "cantidad",
		  "2" => "desc_gral",
          "3" =>  "precio",
          "4" =>  "nro_ordenc",
          "5" =>  "nro_ordenp",
          "6" =>  "nrocaso",
          "7" =>  "razon_social",
          "9" => "descripcion",
          "12" => "id_movimiento_material",
          "11" => "control_stock.fecha_modif"
           );
   if ($cmd=="real") $orden["10"]= "ubicacion.nombre_corto";        

   $filtro= array(
		"desc_gral" => "Descripción",
		"cantidad"=>"Stock Disponible ",
        "tipos_prod.descripcion" => "Tipo",
        "nro_ordenc" => "Nº Orden de Compra",
        "nro_ordenp" => "Nº Orden de Producción",
        "nrocaso" => "Nº C.A.S.",
        "razon_social" => "Proveedor",
        "id_movimiento_material"=>"Nº Mov. Material"
        //"ubicacion.nombre_corto" => "Ubicacion"
		 );
   if ($cmd=="real") $filtro["ubicacion.nombre_corto"]= "Ubicacion";
      

   } //si cmd  real 

if ($cmd=="historial"){
   $sql="  select garantia_vencida,productos.id_producto,productos.desc_gral,";
   $sql.=" productos.marca,productos.modelo,tipos_prod.descripcion,";
   $sql.=" control_stock.fecha_modif,control_stock.usuario,";
   $sql.=" cant_desc,proveedor.id_proveedor,info_rma.id_info_rma,";
   $sql.=" control_stock.id_control_stock,";
   $sql.=" control_stock.estado,control_stock.comentario,info_rma.id_movimiento_material,
           info_rma.nro_ordenc,info_rma.nro_ordenp,info_rma.nrocaso,info_rma.id_nota_credito,proveedor.razon_social";
   $sql.=" from info_rma ";
   $sql.=" join descuento using(id_deposito,id_producto,id_proveedor,id_info_rma)
           join control_stock using(id_control_stock)";
   $sql.=" join productos using (id_producto) ";
   $sql.=" join general.tipos_prod on (tipos_prod.codigo=productos.tipo)";
   $sql.=" join depositos  using (id_deposito)           
           join proveedor using(id_proveedor)";
   $where=" depositos.id_deposito=$id_deposito and cant_desc > 0  and";
   $where.="(estado='a' or estado='r' or estado='oc' or estado='is' or estado='as') ";
   if ($codigo_post!="todos")
			 {
			 $where.=" and productos.tipo='$codigo_post'";
			 }

   $orden=array(
		  "default" => "8",
		  "1" => "cant_desc",
		  "2" => "productos.desc_gral",
		  "3" => "control_stock.estado",
          "4" => "tipos_prod.descripcion",
          "5" =>  "nro_ordenc",
          "6" =>  "nro_ordenp",
          "7" =>  "nrocaso",
          "8" =>  "razon_social",
          "10"=>   "id_movimiento_material"

		  );

		  
   $filtro= array(
		"productos.desc_gral" => "Tipo de Producto",
		"cant_desc" => "Cantidad",
		"control_stock.fecha_modif" => "Fecha",
        "descripcion"=>"Tipo",
        "nro_ordenc"=>"Nº Orden de Compra",
        "nro_ordenp"=>"Nº Orden de Producción",
        "nrocaso"=>"Nº C.A.S.",
        "razon_social"=>"Proveedor",
        "id_movimiento_material"=>"Nº Mov. Material"

		 );

   } //si viene  pendientes

   $contar="buscar";
   $link=encode_link("listado_rma.php",array("id_deposito"=>$id_deposito,"deposito"=>$deposito,"cmd"=>$cmd));

?>
<script>
//funcion que se le pasa dos checked
//el primero es el checked que sirve para elegir los check (segundo parametro)}
//estilo como funcionan los mail
function seleccionar_todos_local(elegir){
var valor;
            if(elegir.checked==true){
            	valor=true;
                var i=0;
                loco=eval ("document.form1.selec_"+i);
                while (typeof(loco)!='undefined'){
                 	loco.checked=valor;
                    i++;
                    loco=eval ("document.form1.selec_"+i);
               	}//del while
             }
             else{
             	valor=false;
                var i=0;
                loco=eval ("document.form1.selec_"+i);
                while (typeof(loco)!='undefined'){
                 	loco.checked=valor;
                    i++;
                    loco=eval ("document.form1.selec_"+i);
               	}//del while
             }
}//de la funcion

</script>

<?=$html_header?>
<?
if(($_ses_user["login"]=="juanmanuel" ||$_ses_user["login"]=="marcos"||$_ses_user["login"]=="ferni")&& $cmd!="historial")
 $mostrar_eliminar_rma=1;
else 
 $mostrar_eliminar_rma=0;
?>
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
	<font size='4' color=<?=$text_color_over?>>
	<?
	if(!$_ses_es_inventario)
	{?>
	 Stock <?=$deposito?>
	<? 
    } 
	else  
	 echo $deposito;
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

  <table width=100% align=center class='bordes' bgcolor=<?=$bgcolor3?>>
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
   //echo "<br>".$query_productos."<br>";
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
     </tr>
   </table>
 </td>
  </tr>
  <tr>
  <td> 
  <table class="bordessininferior" width=99% cellspacing=0 cellpadding=1 bordercolor='' align=center>
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
	 <table width=99% align=center class='bordessinsuperior' >
		 <?
		 if ($cmd=="real" || $cmd=="tran" || $cmd=="cor" || $cmd=="prov") {
		 ?>
		 <tr>
		 <?
		 if($mostrar_eliminar_rma)
		 {
		 ?>
		  <td id="mo">Elim.RMA <INPUT type=checkbox name="selec_todos" onclick="seleccionar_todos_local(this)"> </td>
		 <?
		 }
		 ?> 
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>1));?>">Cant</a></td>
         <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>8));?>">Tipo</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>2));?>">Descripci&oacute;n Producto</a></td>		 
		 <?if ($cmd=="real")
		 {
		 ?>	 
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>10));?>" title="Ubicación de la Parte">Ubi.</a></td><!--//Borggi-->
		 <?
		 }
		 ?>
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>11));?>" title="Días Creación">Dc</a></td><!--//Borggi-->
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>2));?>" title="Días Envio">De</a></td><!--//Borggi-->
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>4));?>" title="Nº de Orden de Compra">OC</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>5));?>" title="Nº de Orden de Producción">OP</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>12));?>" title="Nº de Movimiento de Material">Mov. Mat.</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>6));?>">C.A.S.</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>7));?>">Proveedor</a></td>
                 <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>6));?>">Monto</a></td>
                 <td id="mo">M.P.</td>
		 </tr>
		 <?
		 $resultado=$db->execute($query_productos) or die($db->errormsg()."<br>".$query_productos);
                 $cantidad=$resultado->recordcount();
         
       for ($i=0;$i<$cantidad;$i++){
                                  $id_info_rma=$resultado->fields["id_info_rma"];
       		 $ref = encode_link("stock_descontar_rma.php",array("id_producto"=>$resultado->fields["id_producto"],
	  	   					        "id_deposito"=>$id_deposito,"id_proveedor"=>$resultado->fields["id_proveedor"],
	  	   					        "id_info_rma"=>$id_info_rma,"pagina_listado"=>"real"
							 ));


               //este select se hace para que aparezca la lista
               //de los proveedores con sus precios en el title de la
               //fila

                $id_producto=$resultado->fields["id_producto"];
                $sql="  select precio,desc_gral,razon_social,id_producto,id_proveedor";
                $sql.=" from precios ";
                $sql.=" join productos using (id_producto) ";
                $sql.=" join proveedor using (id_proveedor) ";
                $sql.=" where id_producto=$id_producto";
                $precios=$db->execute($sql) or die ($sql);
                $cant_precios=$precios->recordcount();
                $comentario="Precios:\n";

              for($y=0;$y<$cant_precios;$y++){
                  $precio=formato_money($precios->fields["precio"]);
                  $razon_social=$precios->fields["razon_social"];
                  $comentario.="$razon_social:    U\$S    $precio\n";
                  $precios->movenext();
               }



   $onclick="onClick=\"location.href='$ref'\";";
   ?>
	
   <tr <?=atrib_tr();?> title='<?=$comentario?>'>
            <?
            if($mostrar_eliminar_rma)
		    {
		    ?>
        	 <td align=center> <INPUT type=checkbox name="selec_<?=$i?>"> 
        	<?
		    }
        	?> 
			<!--cargo como hidden para que pasen por el post-->
        	<input type="hidden" name="id_info_rma_<?=$i?>" value="<?=$resultado->fields["id_info_rma"]?>"> 
	       	<input type="hidden" name="id_deposito_<?=$i?>" value="<?=$resultado->fields["id_deposito"]?>">
        	<input type="hidden" name="id_producto_<?=$i?>" value="<?=$resultado->fields["id_producto"]?>">
        	<input type="hidden" name="id_proveedor_<?=$i?>" value="<?=$resultado->fields["id_proveedor"]?>">
        	<input type="hidden" name="cantidad_<?=$i?>" value="<?=$resultado->fields["cantidad"]?>"></td>
        	<!--continuo cargando la tabla-->
        	<td align=right <?=$onclick?>><b><?echo $resultado->fields["cantidad"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["descripcion"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["desc_gral"]?></b></td>
            <?
             if ($cmd=="real")
             {
             ?>	 
            <td align=center  <?=$onclick?>><b><?echo $resultado->fields["nombre_corto"]?></b></td><!--//Borggi-->
            <?
             }
             ?>
            <?$que=trim($resultado->fields['nombre_corto']);
              if ($que!="P")
                 {$fecha_actual=date("d/m/Y");              
                  $fecha_base=fecha($resultado->fields["fecha_modif"]);
                  $color="";
                  $dias=diferencia_dias_habiles($fecha_base,$fecha_actual);
                  if ($dias>3) $color="yellow";
                  if ($dias>8) $color="red";
            ?>
                  <td align=center  <?=$onclick?> bgcolor="<?=$color?>"><b><?echo $dias?></b></td><!--//Borggi-->            
                  <td align=center  <?=$onclick?>><b><?echo "&nbsp;"?></b></td><!--//Borggi-->
            <?   }
              else {$fecha_actual=date("d/m/Y");              
                    $fecha_base=fecha($resultado->fields["fecha"]);                                        
                    $color="";
                    $dias=diferencia_dias_habiles($fecha_base,$fecha_actual);
                    if ($dias>10) $color="yellow";
                    if ($dias>20) $color="red"; 
            ?>                     
                    <td align=center  <?=$onclick?>><b><?echo "&nbsp;"?></b></td><!--//Borggi-->
                    <td align=center  <?=$onclick?> bgcolor="<?=$color?>"><b><?echo $dias?></b></td><!--//Borggi-->            
            <?          
                   }
            ?>      
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["nro_ordenc"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["nro_ordenp"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["id_movimiento_material"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["nrocaso"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["razon_social"]?></b></td>
            <td align=right <?=$onclick?> <?=$coment_precio?>>
            <table width=100% align=Center>
            <tr>
              <td width=20% align=center><b>U$S</b></td>
              <td align=right><b><?echo formato_money($resultado->fields["precio_stock"])?></b></td>
            </tr>
            </table>
            </td>
            <?
            $link=encode_link("stock_mod_precio.php",array("id_producto"=>$resultado->fields["id_producto"]));
            ?>
            <td><input type=button name=boton_precio value=$ onclick="window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');" style="cursor:hand;"></td>
        </tr>
        
          <?
           $resultado->movenext();
        }//del for
      } //de real y pendientes
	  
	 
	  if ($cmd=="historial") {
	  //Este es un caso aparte hay que tener cuidado
      tr_tag("");
	  ?>
	   <td  id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>1,"up"=>$up));?>">Cant     </a></td>
       <td  id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>4,"up"=>$up));?>">Tipo     </a></td>
	   <td  id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>3,"up"=>$up));?>">Descripción Producto </a></td>
	   <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>5,"up"=>$up));?>" title="Nº de Orden de Compra">OC</a></td>
	   <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>6,"up"=>$up));?>" title="Nº de Orden de Producción">OP</a></td>
	   <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>10,"up"=>$up));?>" title="Nº de Movimiento Material">Mov. Mat.</a></td>
	   <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>7,"up"=>$up));?>">C.A.S.</a></td>
	   <td id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>8,"up"=>$up));?>">Proveedor</a></td>
	   <td  id="mo"><a href="<?=encode_link("listado_rma.php",array("sort"=>3,"up"=>$up));?>">Estado   </a></td>

	  </tr>
	  <?
	  $resultado=$db->execute($query_productos) or die($db->errormsg()."<br>".$query_productos);
	  $cantidad=$resultado->RecordCount();
	  for ($i=0;$i<$cantidad;$i++){
	   $id_control_stock=$resultado->fields["id_control_stock"];
	   $id_info_rma=$resultado->fields["id_info_rma"];
      		 $ref = encode_link("stock_descontar_rma.php",array("id_producto"=>$resultado->fields["id_producto"],
	  	   					        "id_deposito"=>$id_deposito,"id_proveedor"=>$resultado->fields["id_proveedor"],"id_info_rma"=>$id_info_rma,
								"pagina_listado"=>"historial","cant_cb"=>$resultado->fields["cant_desc"],"pagina_oc"=>$pagina_oc,"id_control_stock"=>$id_control_stock
							 ));
	$comentario=$resultado->fields["comentario"];
	 tr_tag($ref,"title='$comentario'");
	 if ($resultado->fields["estado"]=="r") $estado="Rechazado";
	 if ($resultado->fields["estado"]=="a") $estado="Aprobado";
         if ($resultado->fields["estado"]=="oc")$estado="Ingreso Orden Compra";
         if ($resultado->fields["estado"]=="is")$estado="Ingreso Manual de Stock";
         if ($resultado->fields["estado"]=="as")$estado="Actualizacion  del Stock Anterior";

	 ?>

	  <td align=center><b><?echo $resultado->fields["cant_desc"]?></b></td>
      <td align=left><b><?echo $resultado->fields["descripcion"]?></b></td>
	  <td align=left><b><?echo $resultado->fields["desc_gral"]?></b></td>
	  <td align=left><b><?echo $resultado->fields["nro_ordenc"]?></b></td>
	  <td align=left><b><?echo $resultado->fields["nro_ordenp"];?></b></td>
	  <td align=left><b><?echo $resultado->fields["id_movimiento_material"];?></b></td>
	  <td align=left><b><?echo $resultado->fields["nrocaso"]?></b></td>
	  <td align=left><b><?echo $resultado->fields["razon_social"]?></b></td>
	  <td align=center><b><?echo $estado;?></b></td>

	 </tr>
	  <?
	  $resultado->MoveNext();
	  }//del for
	  } //del if de historial
	  ?>
</table>
</td>
</tr>
</table>
<?
if($mostrar_eliminar_rma)
{?>
  <div align="center">
    <input type="submit" name="borrar_rma" value="Eliminar de RMA" onclick="if(confirm('¿Está seguro que desea eliminar los productos seleccionados?'))return true; else return false;">
   </div> 
<?
}
?>

<table bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0 class="bordes">
 <tr>
  <td align="center">
   <table width="100%">
    <tr>
     <td align="center"><font size="2"><b>Referencias</b></font></td>
    </tr>
   </table>
  </td>  
 </tr>
 <tr>
  <td align="center">
   <table width="100%">
    <tr>
     <td><font size="2"><b>Ubicación:</b></font></td>
     <td><font size="2"><b>T&nbsp;</b></font>= En Tránsito.</td>
     <td><font size="2"><b>C&nbsp;</b></font>= Coradir (SL o Bs. As.)</td>
     <td><font size="2"><b>P&nbsp;</b></font>= Proveedor</td>     
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td>
   <table>
    <tr>
     <td><font size="2"><b>Días Creación (Dc):&nbsp;&nbsp; </b></font></td>
     <td width=15 bgcolor='yellow' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 3 Días.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>     
     <td width=15 bgcolor='Red' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 8 Días.&nbsp;&nbsp;</b></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td>
   <table>
    <tr>
     <td><font size="2"><b>Días Envio (De):&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b></font></td>
     <td width=15 bgcolor='yellow' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 10 Días.&nbsp;&nbsp;&nbsp;</b></td>     
     <td width=15 bgcolor='Red' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 20 Días.&nbsp;&nbsp;</b></td>
    </tr>
   </table>
  </td>
 </tr>

</table>

<?
/*if($_ses_user["login"]=="marcos")
{?>
  <input type="button" name="boton_borrar" value="borrar_rma" onclick="window.open('borrar_rma_con_fecha.php')">
<?
}*/
?>
</form>
<?
fin_pagina();
?>