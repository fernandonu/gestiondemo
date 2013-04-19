<?php
/*
$Author: ferni $
$Revision: 1.91 $
$Date: 2006/09/19 19:01:27 $
*/
require_once("../../config.php");
require_once("funciones.php");

$usuario=$_ses_user["name"];
$fecha_actual=date("Y-m-d H:i:s",mktime());

if ($_POST["form_busqueda"])
{
 if(!$_POST["ingresos"]){
  $_POST["ingresos"]=0;

 }
 if(!$_POST["egresos"])
  $_POST["egresos"]=0;
}

$var_sesion=array(
                  "tipo_movi"=>"todos",
                  "tipo_producto"=>"todos",
                  "id_deposito"=>$_ses_stock['id_deposito'],
                  "deposito"=>$_ses_stock['deposito'],
                  "pagina_listado"=>$_ses_stock['pagina_listado'],
                  "es_inventario"=>0,
                  "ingresos" =>-1,
                  "egresos" =>-1,
                  );

$pagina_oc=$parametros["pagina_oc"] or $pagina_oc=$_POST['pagina_oc'];
$onclick_cargar= $parametros['onclick_cargar'] or $onclick_cargar= $_POST['onclick_cargar'];
//$sort = $_POST["sort"] or $sort=$parametros["sort"];
//$up   = $_POST["up"] or $up=$parametros["up"];

if ($parametros['print'])
                    {
                	$_POST=$parametros;
                	$print=$parametros['print'];
                    }

variables_form_busqueda("stock",$var_sesion);

if ($id_deposito!=""&&$_ses_stock["id_deposito"]==""){
	$_ses_stock["id_deposito"]=$id_deposito;
	$_ses_stock["deposito"]=$deposito;
	$_ses_stock["pagina_listado"]=$pagina_listado;
	phpss_svars_set("_ses_stock", $_ses_stock);
}

if ($cmd == ""){
				$cmd="real";
				$_ses_stock["cmd"]=$cmd;
				phpss_svars_set("_ses_stock", $_ses_stock);
				}

if($pagina_oc!="")
                $cmd="real";

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


if ($_POST["autorizar"])
{
         $cantidad=$_POST["cantidad"];
         for ($i=0;$i<$cantidad;$i++)
         { if ($_POST["chequeado_$i"])
           {
                $id_log_mov_stock=$_POST["chequeado_$i"];
                $comentario_pendiente=$_POST["comentario_pendiente_$i"];
                autorizar_rechazar_reserva_manual($id_log_mov_stock,"autorizar",$comentario_pendiente);
           }
         }//de for ($i=0;$i<$cantidad;$i++)

         Aviso("Los productos fueron descontados del stock en forma exitosa");
 }//del if de autorizar


if ($_POST["rechazar"])
{
         $cantidad=$_POST["cantidad"];
         for ($i=0;$i<$cantidad;$i++)
         { if ($_POST["chequeado_$i"])
           {
            	$id_log_mov_stock=$_POST["chequeado_$i"];
            	$comentario_pendiente=$_POST["comentario_pendiente_$i"];
			    autorizar_rechazar_reserva_manual($id_log_mov_stock,"rechazar",$comentario_pendiente);
           }
         }//de for ($i=0;$i<$cantidad;$i++)

         Aviso("Los productos fueron devueltos al stock disponible en forma exitosa");
 }//del if de autorizar



//fin de la base de datos
$datos_barra = array(
				array(
					"descripcion"    => "Real",
					"cmd"            => "real",
//					"extra"			 =>array ("id_deposito"=>$id_deposito,
//                  								"deposito"=>$deposito,
//                  								"pagina_listado"=>$pagina_listado,
//                  								"es_inventario"=>0)
					),
				array(
					"descripcion"    => "Pendientes",
					"cmd"            => "pendientes",
//					"extra"			 =>array ("id_deposito"=>$id_deposito,
//                  								"deposito"=>$deposito,
//                  								"pagina_listado"=>$pagina_listado,
//                  								"es_inventario"=>0)

					),
				array(
					"descripcion"    => "Historial",
					"cmd"            => "historial",
//					"extra"			 =>array ("id_deposito"=>$id_deposito,
//                  								"deposito"=>$deposito,
//                  								"pagina_listado"=>$pagina_listado,
//                  								"es_inventario"=>0)

					)
				 );

//consulta para obtener todo el stock de los depositos
if (($cmd=="real"))
   {

      $sql="
           select stock.cant_disp,stock.cant_reservada,stock.cant_a_confirmar,
                  stock.cant_disp+stock.cant_reservada+stock.cant_a_confirmar as cant_total,
                  stock.id_prod_esp, stock.id_deposito,
                  producto_especifico.descripcion,producto_especifico.marca,producto_especifico.modelo,producto_especifico.precio_stock,stock.ubicacion,
                  (COALESCE(producto_especifico.precio_stock,0)*stock.cant_disp)as monto_total_disp,
                  (COALESCE(producto_especifico.precio_stock,0)*(stock.cant_disp+stock.cant_reservada+stock.cant_a_confirmar))as monto_total,
                  tipos_prod.descripcion as tipo_descripcion
           from stock.en_stock stock
           join general.producto_especifico using(id_prod_esp)
           join general.depositos using(id_deposito)
           join general.tipos_prod using (id_tipo_prod)";
   $where=" stock.id_deposito=$id_deposito and (stock.cant_disp > 0 or stock.cant_reservada > 0 or stock.cant_a_confirmar > 0)";
   if ($codigo_post!="todos")
			 {
			 $where.=" and  tipos_prod.id_tipo_prod='$codigo_post'";
			 }

   $group =" group by  stock.id_prod_esp,producto_especifico.descripcion,stock.id_deposito,stock.id_prod_esp,
                       producto_especifico.modelo,producto_especifico.marca,producto_especifico.precio_stock,
                       tipos_prod.descripcion ";


  if ($print)
                $where_print=" id_deposito=$id_deposito and (cant_disp > 0 or cant_reservada > 0 or cant_a_confirmar > 0)";
  $where="$where ";

   $orden=array(
		  "default" => "7",
		  "default_up" => "0",
		 /// "1" => "cant_total",
		  "2" => "cant_disp",
		  "3" => "tipos_prod.descripcion",
		  "4" => "producto_especifico.descripcion",
		  "5" => "marca",
		  "6" => "modelo",
          "7" =>  "precio_stock",
          "8" => "monto_total",
          "9" => "ubicacion"
           );


   $filtro= array(
		"producto_especifico.descripcion" => "Descripción",
		"cant_disp"=>"Cantidad Disponible",
		//"cant_total"=>"Cantidad Total",
		"marca" =>"Marca",
		"modelo" =>"Modelo",
		"ubicacion" =>"Ubicación",
        );

}//si cmd  real

if ($cmd=="pendientes")
{
   $sql="  select lms.id_en_stock,lms.cantidad,lms.id_log_mov_stock,lms.comentario,
            prod_esp.id_prod_esp,prod_esp.descripcion, prod_esp.marca,prod_esp.modelo,prod_esp.id_tipo_prod,
            tipos_prod.descripcion as tipo_descripcion,
            lms.cantidad as cant_desc
            from log_movimientos_stock lms
            join detalle_reserva using(id_log_mov_stock,id_en_stock)
            join tipo_movimiento using (id_tipo_movimiento)
            join en_stock using(id_en_stock)
            join producto_especifico prod_esp using (id_prod_esp)
            join general.tipos_prod using(id_tipo_prod)
            join depositos  using (id_deposito)

            ";

   $where=" id_deposito=$id_deposito and tipo_movimiento.nombre='Reserva Manual de Productos' ";
   if ($codigo_post!="todos")
			 $where.=" and tipos_prod.id_tipo_prod='$codigo_post'";

   $orden=array(
		  "default" => "5",
		  "1" => "cant_desc",
		  "2" => "prod_esp.descripcion",
		  "3" => "prod_esp.marca",
		  "4" => "prod_esp.modelo",
          "5" => "tipos_prod.descripcion"
		  );


   $filtro= array(
		"prod_esp.descripcion" => "Descripción",
		"lms.usuario_mov" => "Usuario",
		"lms.fecha_mov" => "Fecha",
		"marca" =>"Marca",
		"modelo" =>"Modelo",
        "en_stock.id_prod_esp" => "ID producto"
        );



   } //si viene de  pendientes

if ($cmd=="historial")
   {

   $sql="  select producto_especifico.descripcion,
            producto_especifico.marca,producto_especifico.modelo,tipos_prod.descripcion as tipo_descripcion,
            lms.id_log_mov_stock,lms.fecha_mov,lms.usuario_mov,lms.comentario,
            tipo_movimiento.nombre,
            lms.cantidad as cant_desc
            from en_stock
            join producto_especifico using (id_prod_esp)
            join general.tipos_prod using (id_tipo_prod)
            join depositos  using (id_deposito)
            join log_movimientos_stock lms using(id_en_stock)
            join tipo_movimiento using(id_tipo_movimiento)
            ";
   $where=" depositos.id_deposito=$id_deposito and cantidad>0 ";
   if ($codigo_post!="todos")
			 {
			 $where.=" and id_tipo_prod='$codigo_post'";
			 }

   if ($tipo_mov_post!="todos")
			 {
			 $where.=" and  tipo_movimiento.id_tipo_movimiento='$tipo_mov_post'";
			 }
  if ($ingresos && $egresos ) {
             $where.= " and (clase_mov=1 or clase_mov=2)";
  }
  else if ($ingresos) {
             $where.= " and clase_mov=1";
  }
  else if ($egresos) {
             $where.= " and clase_mov=2";
  }
   $orden=array(
		  "default" => "6",
		  "default_up" => "0",
		  "1" => "cant_desc",
		  "2" => "producto_especifico.descripcion",
		  "3" => "producto_especifico.marca",
		  "4" => "producto_especifico.modelo",
		  "5" => "tipo_movimiento.nombre",
		  "6" => "fecha_mov",
          "8" => "tipo_descripcion",
		  );

   $filtro= array(
		"producto_especifico.descripcion" => "Tipo de Producto",
		"fecha_mov" => "Fecha",
		"producto_especifico.marca" => "Marca",
		"producto_especifico.modelo" => "Modelo"
		 );

   } //si viene  de historial


//esto es global para todos los estados
   if($pagina_oc!="")
   {
   	$link_tmp["pagina_oc"]=$pagina_oc;
	$link_tmp["onclick_cargar"]=$onclick_cargar;
   }
   $contar="buscar";
   $link=encode_link("listado_depositos.php",array("id_deposito"=>$id_deposito,"deposito"=>$deposito,"cmd"=>$cmd,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));

	if ($print)
	{
       //para que haga la consulta y no imprima el form
       ob_start();
	   $itemspp=1000000;

     $where_print.=" $group_print";
	   list($q_print,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where_print,$contar);
	   ob_clean();
	   $q_print=eregi_replace("ORDER.*","order by descripcion",$q_print);
	   require("stock_imprimir.php");die;
	}

?>
<script>
function control_datos(datos){
return confirm('Esta seguro que desea  '+datos+' el/los productos');
}
</script>

<?=$html_header;?>
<form action='<?=$_SERVER["PHP_SELF"]?>' method='post' name=form1>

<input type=hidden name="id_deposito" value="<?=$id_deposito?>">
<input type=hidden name="deposito" value="<?=$deposito?>">
<input type=hidden name="pagina_listado" value="<?=$pagina_listado?>">
<input type=hidden name="es_inventario" value="<?=$es_inventario?>">
<!--Este hidden se usa en paginas de muestra NO BORRAR-->
<input type="hidden" name="pagina_oc" value="<?=$pagina_oc?>">
<input type="hidden" name="onclick_cargar" value="<?=$onclick_cargar?>">
<input type="hidden" name="onclicksalir" value="<?=$onclicksalir?>">
<input type=hidden name="id_dep_ext" value="<?=$deposito?>">

<?

$exito=$parametros["exito"] or $exito=$exito;
if ($exito) Aviso($exito);

?>
<table width=98% align=center border=0 cellspacing=0>
      <tr>
             <td align=center>
              	<b><font size='4' color='#004962'><?=$deposito;?></font></b>
             </td>
       </tr>
       <tr>
          <td>
        	<?
        	//si viene de la pagina de ordenes de compra,
        	// no se muestra la barra de navegacion (antes real)
             if($pagina_oc=="")
        	        generar_barra_nav($datos_barra);
        	?>
          </td>
       </tr>
</table>

<table width=95% align=center class="bordes" bgcolor="<?=$bgcolor_out?>">
<?
  $montos_globales=array();
  $montos_globales=calcular_monto_stock($id_deposito);
?>
  <tr>
     <td width="<?if ($cmd=='historial') echo '10%'; else echo '15%'?>" align=left bgcolor="white" title="Monto Total de Productos en Stock">
       <b> Monto Total: <?echo "U\$S&nbsp;".formato_money($montos_globales["total"]);?></b>
     </td>
     <td align="center" <?if($cmd!='historial') echo "rowspan='2'";?>>
       <?list($query_productos,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
        $sql="select descripcion,codigo,id_tipo_prod from tipos_prod order by descripcion";
        $resultado_desc = sql($sql) or fin_pagina();
       ?>
       <b>Tipo Producto:</b>
       <select name="tipo_producto" style="width:150" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
       <?
       while (!$resultado_desc->EOF){
         $id_tipo_prod=$resultado_desc->fields['id_tipo_prod'];
         $descripcion=$resultado_desc->fields['descripcion'];
         if ($codigo_post==$id_tipo_prod)
            $selected="selected";
         else
          	$selected="";
         if ($codigo_post=="todos") $selected="selected";
         ?>
          <option value="<?=$id_tipo_prod?>" <?=$selected?> > <?=$descripcion;?></option>
          <?
         $resultado_desc->MoveNext();
       }//del while
       ?>
       <option value="todos" <?=$selected?>>Todos</option>
      </select>
      &nbsp;&nbsp;
       <input type=submit name=form_busqueda value='Buscar'>
       <?if ($deposito=="Buenos Aires"){
         if (permisos_check('inicio','permiso_boton_reporte_stock_bsas')){?>
                 		<input type="button" name="reporte" value="Reporte" onclick="window.open ('reporte_stock_bsas.php')">
                	<?}
        }?>
     </td>
  </tr>
  <tr>
     <td  width="12%" align=left bgcolor="white" title="Monto Disponible de Productos en Stock">
       <b>Monto Disp.: <?echo "U\$S&nbsp;".formato_money($montos_globales["disponible"]);?></b>
     </td>
     <?if ($cmd=='historial')  {?>
         <td align="right" colspan="3"><b>Tipo de Movimiento:</b>
         <?$sql="select id_tipo_movimiento,nombre from stock.tipo_movimiento order by nombre";
           $resultado_mov = sql($sql) or fin_pagina();?>
           <select name="tipo_movi" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
           <?while (!$resultado_mov->EOF)  {
	         $id_tipo_mov=$resultado_mov->fields['id_tipo_movimiento'];
	         $nombre=$resultado_mov->fields['nombre'];
	         if ($tipo_mov_post==$id_tipo_mov)
		         $selected="selected";
	    	 else
	              $selected="";
	         if ($tipo_mov_post=="todos") $selected="selected";
	          ?>
	           	<option value="<?=$id_tipo_mov?>" <?=$selected?> > <?=$nombre;?></option>
	          <?
	           	$resultado_mov->MoveNext();
              }//de while (!$resultado_mov->EOF)
              ?>
               <option value="todos" <?=$selected?>>Todos</option>
               </select>&nbsp;&nbsp;&nbsp;
            <b>Ingresos:</b> <input type='checkbox' class="estilos_check" name='ingresos' value="1" <?if($ingresos ==1 ) echo 'checked'?>>
            <b>Egresos:</b> <input type='checkbox' class="estilos_check" name='egresos'  value="1" <?if($egresos ==1 ) echo 'checked'?>>
          </td>
          <?
          }//de if ($cmd=='historial')
          else {?>
            <td>&nbsp;</td>
          <?}
       ?>
       </tr>
</table>
<br>
<table width="95%" align="center">
  <tr>
      <td>

           <table  class="bordes" width=100% cellspacing=2 cellpadding=2 align=center>
               <tr>
               <td  align=left id="ma_sf"><b>Total: <?=$total?></b></td>
		       <?
		       $link_agregar=encode_link("stock_agregar.php",array("id_deposito"=>$id_deposito,"deposito"=>$deposito));
		       ?>
         	   <td  align=left id="ma_sf">
         	    <?
         	    if(permisos_check("inicio","permiso_agregar_stock"))
         	    {?>
         	    	<input type=button name=agregar_stock class="little_boton" value="Agregar a Stock" onclick="window.open('<?=$link_agregar?>')">
         	    <?
         	    }
         	    else
         	     echo "&nbsp;";
				echo "&nbsp;&nbsp;";
         	    if($cmd=='real')
                {
               ?>
              		<img src="../../imagenes/imp.gif" title="Imprimir" onclick="window.open('<? $_POST['print']=1; echo encode_link("listado_depositos.php",$_POST)?>')" style="cursor:hand">
                <?
                }
               ?>
         	   </td>
               <td  align=right id="ma_sf"><?=$link_pagina?></td>
               </tr>
            </table>
     </td>
  </tr>
   <tr>
      <td >
	<table width=100%  class="bordes" align=center>
		 <?

		 if ($cmd=="real")
		 {
		 ?>
		 <input type="hidden" name="pagina_oc" value="<?=$pagina_oc?>">
		 <input type="hidden" name="onclick_cargar" value="<?=$onclick_cargar?>">

		 <tr>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>1,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>" title="Cantidad Total en Stock">Total</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>2,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>" title="Cantidad Disponible">Disp.</a></td>
         <?
		 if($deposito=="Servicio Técnico Bs. As.")
		 {?>
          <td id="mo" title="ID de producto">
           <a href="<?=encode_link("listado_depositos.php",array("sort"=>9,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>">ID Prod</a>
          </td>
         <?
		 }
		 ?>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>4,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>">Descripción Producto</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>5,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>">Marca</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>6,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>">Modelo</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>3,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>">Tipo</a></td>
		 <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>7,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>" title="Precio Unitario">Precio U.</a></td>
         <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>8,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>" title="Monto Total Disponible">Monto Total</a></td>
         <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>9,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));?>">Ubicación</a></td>
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
		 $resultado=sql($query_productos) or fin_pagina();

         $cantidad=$resultado->recordcount();

                 for ($i=0;$i<$cantidad;$i++){
                       	 $ref = encode_link("stock_descontar.php",array("id_prod_esp"=>$resultado->fields["id_prod_esp"],
                	  	          					                    "id_deposito"=>$id_deposito,
                								                        "pagina_listado"=>$pagina_listado,
                								                        "accion"=>"nuevo","pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar,'pagina_volver_muestra'=>$volver_a_muestra,'stock_selec'=>$post_deposito
                                                                        )
                                           );
                         $onclick="onClick=\"location.href='$ref'\"";
                         ?>
                         <tr <?=atrib_tr()?>>
                             <?
                              $title_reservada="title='Cantidad Reservada:  ".$resultado->fields["cant_reservada"]."\nCantidad a Confirmar: ".$resultado->fields["cant_a_confirmar"]."'";

                            ?>
                            <td align=right <?=$onclick?>><b><?=$resultado->fields["cant_total"]?></b></td>
                            <td align=right <?=$onclick?> <?=$title_reservada?>><b><?=$resultado->fields["cant_disp"]?></b></td>
                            <?
                    		if($deposito=="Servicio Técnico Bs. As.")
            		          {?>
                              	<td align=left  <?=$onclick?>>   <b><?=$resultado->fields["id_prod_esp"]?>      </b></td>
                              <?
            		          }?>
                            <td align=left  <?=$onclick?>><b><?=$resultado->fields["descripcion"]?>   </b></td>
                            <td align=left  <?=$onclick?>><b><?=$resultado->fields["marca"]?>       </b></td>
                            <td align=left  <?=$onclick?>><b><?=$resultado->fields["modelo"]?>      </b></td>
                            <td align=left  <?=$onclick?>><b><?=$resultado->fields["tipo_descripcion"]?></b></td>
                            <td align=right <?=$onclick ?> <?=$coment_precio?>>
                               <table width=100% align=Center>
                                        <tr>
                                          <td width=20% align=center><b>U$S</b></td>
                                          <td align=right><b><?=formato_money($resultado->fields["precio_stock"])?></b></td>
                                        </tr>
                                 </table>
                            </td>
                                 <?
                                  $title_prod="Monto Disponible\nU\$S ".$resultado->fields["monto_total_disp"];
                                  ?>
                            <td align=right <?=$onclick?> title="<?=$title_prod?>">
                                  <table width=100% align=Center>
                                    <tr>
                                      <td width=20% align=center><b>U$S</b></td>
                                      <td align=right><b><?echo formato_money($resultado->fields["monto_total"])?></b></td>
                                    </tr>
                                    </table>
                             </td>
                             <td><b><?=$resultado->fields["ubicacion"]?></b></td>
                             <?
                             $link=encode_link("stock_mod_precio.php",array("id_prod_esp"=>$resultado->fields["id_prod_esp"]));
                             if(permisos_check("inicio","permiso_boton_cambiar_precio"))
                             {
                             ?>
                             <td><input type=button name=boton_precio value=$ onclick="window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');" style="cursor:hand;"></td>
                             <?}?>
                      </tr>
                       <?
                           $resultado->movenext();
                      }//del for

	  } //de real y pendientes
	  if ($cmd=="pendientes"){
	  ?>
        	  <tr>
            	   <td  id="mo">&nbsp;</td>
            	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>1,"up"=>$up));?>"> Cant</a></td>
                   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>5,"up"=>$up));?>"> Tipo</a></td>
            	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>2,"up"=>$up));?>"> Producto  </a></td>
            	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>3,"up"=>$up));?>"> Marca     </a></td>
            	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>4,"up"=>$up));?>"> Modelo    </a></td>
        	  </tr>
	  <?
	  $resultado=sql($query_productos) or fin_pagina();
	  $cantidad=$resultado->RecordCount();
	  ?>
	  <input type="hidden" name="cantidad" value="<?=$cantidad?>">
	  <?
	  for ($i=0;$i<$cantidad;$i++){
             // $id_producto=$resultado->fields["id_producto"];
         	  $ref = encode_link("stock_informacion.php",
        						array("id_prod_esp"=>$id_prod_esp,
        							  "id_deposito"=>$id_deposito,
        							  "accion"=>"modificar",
                                      "id_en_stock"=>$resultado->fields["id_en_stock"],
        							  "id_log_mov_stock"=>$resultado->fields["id_log_mov_stock"],
        							  "cmd"=>$cmd,
        							  ));

              $comentario=$resultado->fields["comentario"];
              $onclick="onClick=\"location.href='$ref'\";"
               ?>
               <tr <?=atrib_tr()?> title='<?=$comentario?>'>
        	   <!-- hidden necesarios para las consultas -->
       	      <input type="hidden" name="id_producto_<?=$i?>"  value="<?=$id_producto?>" >
        	  <td align="Center" >
        	    <input type="checkbox" name="<?="chequeado_$i"?>" value="<?=$resultado->fields["id_log_mov_stock"]?>" onclick="window.event.cancelBubble=true;">
        	    <input type="hidden" name="comentario_pendiente_<?=$i?>" value="<?=$comentario?>">
        	  </td>
        	  <td align="center" <?=$onclick?>> <b><?=$resultado->fields["cant_desc"]?>     </b></td>
              <td align="left"   <?=$onclick?>> <b><?=$resultado->fields["tipo_descripcion"]?>   </b></td>
        	  <td align="left"   <?=$onclick?>> <b><?=$resultado->fields["descripcion"]?>     </b></td>
        	  <td align="left"   <?=$onclick?>> <b><?=$resultado->fields["marca"]?>         </b></td>
        	  <td align="left"   <?=$onclick?>> <b><?=$resultado->fields["modelo"]?>        </b></td>

        	  </tr>
        	  <?
        	  $resultado->MoveNext();
	  }//del for
	  //le doy permiso unicamnente a los que tienen
	  //autorizacion
	  if (permisos_check("inicio","autorizar_rechazar")) {
	  ?>
    	  <tr>
    		<td colspan="7" align=center>
    			<table width=100% align=Center>
    			 <tr>
    			 <td width=50% align=right>
    			 <input type="submit" name="autorizar" value="Autorizar" onclick="return control_datos('Autorizar');">
    			 </td>
    			 <td width=50% align=left>
    			 <input type="submit" name="rechazar" value="Rechazar" onclick="return control_datos('Rechazar');">
    			 </td>
    			 </tr>
    			</table>

    		</td>
    	  </tr>
	  <?
      }//del if de permisos

	  } //del if de PEndientes
	  if ($cmd=="historial"){
	  //Este es un caso aparte hay que tener cuidado
	  ?>
	   <tr>
    	   <td  id="mo" width="1%"><a href="<?=encode_link("listado_depositos.php",array("sort"=>1,"up"=>$up));?>">Cant</a></td>
           <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>8,"up"=>$up));?>">Tipo</a></td>
    	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>2,"up"=>$up));?>">Descripción Producto</a></td>
    	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>3,"up"=>$up));?>">Marca</a></td>
    	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>4,"up"=>$up));?>">Modelo</a></td>
    	   <td  id="mo" width="1%"><a href="<?=encode_link("listado_depositos.php",array("sort"=>6,"up"=>$up));?>">Fecha</a></td>
    	   <td  id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>5,"up"=>$up));?>">Tipo de Movimiento</a></td>
 	   </tr>
	  <?
	  $resultado=sql($query_productos) or fin_pagina();
	  $cantidad=$resultado->RecordCount();
	  for ($i=0;$i<$cantidad;$i++){
 	          $ref = encode_link("stock_informacion.php",
						         array("id_prod_esp"=>$resultado->fields["id_prod_esp"],
							           "id_deposito"=>$id_deposito,
							           "id_log_mov_stock"=>$resultado->fields["id_log_mov_stock"],
							           "accion"=>"ver",
							           "cmd"=>$cmd,
							          ));
	           $comentario=$resultado->fields["comentario"];
 	            tr_tag($ref,"title='$comentario'");
                 ?>

	             <td align=center>  <b><?=$resultado->fields["cant_desc"]?>   </b></td>
                 <td align=left>    <b><?=$resultado->fields["tipo_descripcion"]?>   </b></td>
	             <td align=left>    <b><?=$resultado->fields["descripcion"]?>   </b></td>
	             <td align=left>    <b><?=$resultado->fields["marca"]?>   </b></td>
	             <td align=left>    <b><?=$resultado->fields["modelo"]?>   </b></td>
	             <td align=left>    <b><?=fecha($resultado->fields["fecha_mov"])?>   </b></td>
	             <td align=center>  <b><?=$resultado->fields["nombre"];?>   </b></td>
            </tr>
	        <?
	        $resultado->MoveNext();
	        }//del for

	  } //del if de historial
	  ?>

 <!--fin de los diferentes casos para autorizar-->
 </table>
</td>
</tr>
<!-- Hasta aca tendria qeu dejar de separ  -->
</table>
</form>
<?
if($pagina_oc!="")
{?>
 <div align="center"><input type="button" name="Salir" value="Salir" onclick="window.close();"></div>
<?
}

echo fin_pagina();
?>