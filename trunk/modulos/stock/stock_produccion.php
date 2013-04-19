<?php
/*
$Author: fernando $
$Revision: 1.27 $
$Date: 2007/02/14 20:28:46 $
*/
require_once("../../config.php");

$msg=$parametros["msg"];

if($_POST["ajustar_log_mov_stock"]=="Ajustar log_mov stock")
{
	$db->StartTrans();

	$fecha_hoy=date("Y-m-d H:i:s");
	$query="select en_stock.id_en_stock,en_stock.id_prod_esp,en_stock.cant_disp
				from stock.en_stock
				where id_deposito=13";
	$dat_disp_prod=sql($query,"<br>Error al traer las cantidades disponibles de RMA<br>") or fin_pagina();

	while (!$dat_disp_prod->EOF)
	{
		$total_ing=$total_eg=$dif_insertar=0;
	 	//revisamos las cantidades de ingreos y egresos y las comparamos con la cantidad total en stock.
		$query="select 	sum(log_movimientos_stock.cantidad) as total_mov,tipo_movimiento.clase_mov,en_stock.id_prod_esp
		     from stock.log_movimientos_stock
			  join stock.en_stock using (id_en_stock)
			  join stock.tipo_movimiento using(id_tipo_movimiento)
		      where id_en_stock=".$dat_disp_prod->fields["id_en_stock"]." and (clase_mov=1 or clase_mov=2) and ( id_deposito=13)
		      group by tipo_movimiento.clase_mov,en_stock.id_prod_esp";
		$ing_eg=sql($query,"<br>Error al traer las sumas de los ingresos y egresos para el producto<br>") or fin_pagina();

		while (!$ing_eg->EOF)
		{
		 	if($ing_eg->fields["clase_mov"]==1)
		 	 	$total_ing=$ing_eg->fields["total_mov"];
		 	if($ing_eg->fields["clase_mov"]==2)
		 		$total_eg=$ing_eg->fields["total_mov"];

		 	$ing_eg->MoveNext();
		}//de while(!$ing_eg->EOF)

		if($total_ing=="")
			$total_ing=0;
		if($total_eg=="")
			$total_eg=0;

		$dif_ing_eg=$total_ing-$total_eg;

		//si la diferencia entre ingresos y egresos es mayor que la cantidad disponible en stock,
		//agregamos un egreso para ajustar esa discrepancia
		if(($dif_ing_eg!="" && $dif_ing_eg!=0) &&($dif_ing_eg > $dat_disp_prod->fields["cant_disp"]))
		{
			$dif_insertar=$dif_ing_eg-$dat_disp_prod->fields["cant_disp"];
			//agregamos el egreso de ajuste
			$query="insert into stock.log_movimientos_stock(id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario)
					values(".$dat_disp_prod->fields["id_en_stock"].",32,$dif_insertar,'$fecha_hoy','".$_ses_user["name"]."','Se agregó egreso de Producción para ajustar las cantidades registradas en el movimiento de este deposito, debido a una serie de bugs ocurridos en el sistema de Stock Producción')";
			sql($query,"<br>Error al agregar egreso de ajuste en los movimientos de stock<br>") or fin_pagina();

			$id_stock_afectados_mov.=$dat_disp_prod->fields["id_en_stock"]." - ";
		}//de if(($total_ing-$total_eg) > $datos_rma->fields["cant_disp"]);
		else if(($dif_ing_eg!="" && $dif_ing_eg!=0) &&($dif_ing_eg < $dat_disp_prod->fields["cant_disp"]))
		{
			$id_stock_negativos_mov.=$dat_disp_prod->fields["id_en_stock"]." - ";
		}

		echo "<br>Para ".$dat_disp_prod->fields["id_en_stock"].", dif_ing_eg: $dif_ing_eg - Cantidad disp: ".$dat_disp_prod->fields["cant_disp"]." - Cantidad a insertar en el log: $dif_insertar<br>";

	 	$dat_disp_prod->MoveNext();
	}//de while(!$dat_disp_prod->EOF)

	echo "Id en Stock afectados por ajuste egresos de mov: $id_stock_afectados_mov<br><br>";
	echo "Id en Stock afectados por ajuste ingresos de mov: $id_stock_negativos_mov<br><br>";

	echo "<br>LA TRANSACCION NO FUE CERRADA, NO SE REALIZARON CAMBIOS EN EL STOCK<br>";
	//$db->CompleteTrans();
}//de if($_POST["ajustar_log_mov_stock"]=="Ajustar log_mov stock")

echo $html_header;

variables_form_busqueda("stock_produccion");

if (!$cmd)
    $cmd="real";




$sql="select  sum((cant_disp+cant_reservada)*p.precio_stock) as total
                from stock.en_stock
                join general.producto_especifico p using (id_prod_esp)
                where id_deposito=13
      ";

$result=sql($sql) or fin_pagina();
$monto=$result->fields["total"];


$datos_barra=array
    (
    array
        (
        "descripcion" => "Real",
        "cmd"         => "real"
        ),
    array
        (
        "descripcion" => "Historial",
        "cmd"         => "historial"
        )
    );


if ($cmd=="real"){
                   $sql="select ep.id_en_produccion,ep.id_licitacion,ep.cantidad,p.id_prod_esp,p.precio_stock,(p.precio_stock*ep.cantidad) as total,
                                        p.marca,p.modelo,p.descripcion
                                from stock.en_produccion ep
                                join stock.en_stock  es using  (id_en_stock)
                                join general.producto_especifico p using (id_prod_esp)
                                ";
                        $where=" (ep.cantidad < 0 or ep.cantidad >0)";
                        $orden=array
                            (
                            "default"    => "2",
                            "default_up" => "0",
                            "1"          => "ep.cantidad",
                            "2"          => "marca",
                            "3"          => "modelo",
                            "4"          => "descripcion",
                            "5"          => "ep.id_licitacion",
                            "6"          => "total",
                            "7"          => "p.precio_stock",
                            );

                        $filtro=array
                            (
                            "ep.id_licitacion" => "Id Licitación",
                            "descripcion"     => "Descripción Producto",
                            "ep.cantidad"     => "Stock Disponible ",
                            "marca"         => "Marca",
                            "modelo"        => "Modelo",

                            );
                        /*
                        $sumas = array(
 		                        "moneda" => "id_moneda",
 		                        "campo" =>   "precio_unitario",
 		                        "mask" => array ("\$","U\$S")
                        );
                        */
      }

?>

<form name = form1 method = post>
    <table width = 100% align = center class = bordes>
        <tr>
            <td align = center><font size = 3> <b>Stock de Producción</b></font></td>
        </tr>
        <!--
        <tr>
           <td align=center>
                <?
                generar_barra_nav($datos_barra);
                ?>
           </td>
        -->
        </tr>
        <tr>
            <td align = center>
            <?
            list($sql, $total, $link_pagina, $up,$sumas)=form_busqueda($sql, $orden, $filtro, $link_tmp, $where, "buscar",$sumas);
            $result=sql($sql) or fin_pagina();

            ?>

                <input type = submit name = form_busqueda value = 'Buscar'>&nbsp;&nbsp;

                <?if (permisos_check('inicio','permiso_boton_informe_stock_en_produccion')){?>
                	<input type = button name = informe value = 'Informe' onclick="window.open('informe_stock_prod.php')">
                <?}?>

            </td>
        </tr>

        <tr>
            <td>
               <table width=20% align=left bgcolor=white class=bordes>
                  <tr><td align=center colspan=2><b>Montos Totales</b></td></tr>
                  <?
                  /*
                  for($i=0;$i<sizeof($montos);$i++){
                  ?>
                  <tr>
                     <td><b><?=$montos[$i]["nombre"]?></b></td>
                     <td align=right><b><?=formato_money($montos[$i]["monto"])?></b></td>
                  </tr>
                  <?
                  }
                  */
                  ?>
                  <tr><td align=center><b>U$S &nbsp;&nbsp;<?=formato_money($monto)?></b></td></tr>
               </table>
            </td>
        </tr>

        <tr>
           <td align=left>
	           		<?
	                 //if ($_ses_user["login"]=="fernando" or $_ses_user["login"]=="juanmanuel"){
			         $link_agregar=encode_link("stock_agregar_en_produccion.php",array("deposito"=>"En Produccion"));
	                 ?>
	                 <input type=button name=agregar_stock class="little_boton" value="Agregar a Stock En Producción" onclick="window.open('<?=$link_agregar?>')">
	                 <?
	                 //}
         	    ?>
            </td>
        </tr>
        <tr>
     	    <td align="center">
	 	     <?if($msg)
	 	     	echo "<font size=2><b>$msg</b></font>";
	 	       else
	 	        echo "&nbsp;";
	 	     ?>
	 	    </td>
        </tr>
        <tr>
            <td>
                <table align = center width = 100%>

                <?
                /*
                if ($cmd=="real") {
                */
                ?>
                    <tr id=ma_sf>
                       <td colspan=3>Cantidad : <?=$total?></td>
                       <td colspan=5 align=right><?=$link_pagina?></td>
                    </tr>
                    <tr>

                      <td id="mo" width=5%><a href="<?=encode_link("stock_produccion.php",array("sort"=>5,"up"=>$up));?>">ID</a></td>
                      <td id="mo" width=5%><a href="<?=encode_link("stock_produccion.php",array("sort"=>1,"up"=>$up));?>">Cant.</a></td>
                      <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>4,"up"=>$up));?>">Descripción</a></td>
                      <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>2,"up"=>$up));?>">Marca</a></td>
                      <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>3,"up"=>$up));?>">Modelo</a></td>
                      <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>7,"up"=>$up));?>">Precio U.</a></td>
                      <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>6,"up"=>$up));?>">Precio T.</a></td>
                      <td id="mo">M.P.</td>
                    </tr>
                    <?
                    $total_busqueda=0;
                    $cantidad=$result->recordcount();
                    for($i=0;$i<$cantidad;$i++){

                        $ref=encode_link("./descontar_stock_produccion.php",array("id_en_produccion"=>$result->fields["id_en_produccion"]));
                        if (permisos_check("inicio","permiso_descontar_produccion_manual") || permisos_check("inicio","permiso_pasar_productos_produccion_rma"))
                                $onclick="onClick=\"location.href='$ref'\";";
                        else
                                $onclick="";
                       /* if ($_ses_user["login"]!="corapi")
                            $onclick="onClick=\"location.href='$ref'\";";
                         */
                        ?>
                        <tr <?=atrib_tr()?>>
                          <?
                          $link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
                          ?>

                          <td><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>
                          <td><?=$result->fields["cantidad"]?></td>
                          <td <?=$onclick?>><?=$result->fields["descripcion"]?></td>
                          <td <?=$onclick?>><?=$result->fields["marca"]?></td>
                          <td <?=$onclick?>><?=$result->fields["modelo"]?></td>
                          <td>
                               <table width=100% align=Center>
                                        <tr>
                                          <td width=20% align=center><b>U$S</b></td>
                                          <td align=right><b><?=formato_money($result->fields["precio_stock"])?></b></td>
                                        </tr>
                                 </table>
                          </td>
                          <td>
                               <table width=100% align=Center>
                                        <tr>
                                          <td width=20% align=center><b>U$S</b></td>
                                          <td align=right><b><?=formato_money($result->fields["total"])?></b></td>
                                        </tr>
                                 </table>
                          </td>
                          <?
                          $link=encode_link("stock_mod_precio.php",array("id_prod_esp"=>$result->fields["id_prod_esp"]));
                          ?>
                           <td><input type=button name=boton_precio value=$ onclick="window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');" style="cursor:hand;"></td>

                        </tr>
                        <?
                        $result->movenext();
                    }
                /*
                }// del if de real
                elseif ($cmd=="historial"){

               ?>

                                            <tr>
                                              <td id="mo" width=5%><a href="<?=encode_link("stock_produccion.php",array("sort"=>1,"up"=>$up));?>">Cant.</a></td>
                                              <td id="mo" width=5%><a href="<?=encode_link("stock_produccion.php",array("sort"=>6,"up"=>$up));?>">OC</a></td>
                                              <td id="mo" width=5%><a href="<?=encode_link("stock_produccion.php",array("sort"=>5,"up"=>$up));?>">ID</a></td>
                                              <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>4,"up"=>$up));?>">Descripción</a></td>
                                              <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>2,"up"=>$up));?>">Marca</a></td>
                                              <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>3,"up"=>$up));?>">Modelo</a></td>
                                              <td id="mo"><a href="<?=encode_link("stock_produccion.php",array("sort"=>3,"up"=>$up));?>">Estado</a></td>
                                            </tr>
                                            <?
                                            $total_busqueda=0;
                                            $cantidad=$result->recordcount();
                                            for($i=0;$i<$cantidad;$i++){
                                                $ref=encode_link("./detalle_historial_stock_produccion.php",
                                                                       array("id_producto"=>$result->fields["id_producto"],
                                                                             "nro_orden"=>$result->fields["nro_orden"],
                                                                             "id_licitacion"=>$result->fields["id_licitacion"]));
                                                $onclick="onClick=\"location.href='$ref'\";"
                                                ?>
                                                <tr <?=atrib_tr()?>>
                                                  <td><?=$result->fields["cant_desc"]?></td>
                                                 <?
                                                 $link = encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$result->fields["nro_orden"]));
                                                 ?>
                                                  <td><a href=<?=$link?> target="_blank"><?=$result->fields["nro_orden"]?></a></td>
                                                 <?
                                                 $link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
                                                 ?>

                                                  <td><a href=<?=$link?> target="_blank"><?=$result->fields["id_licitacion"]?></a></td>
                                                  <td <?=$onclick?>><?=$result->fields["desc_gral"]?></td>
                                                  <td <?=$onclick?>><?=$result->fields["marca"]?></td>
                                                  <td <?=$onclick?>><?=$result->fields["modelo"]?></td>
                                                  <td <?=$onclick?>>
                                                     <?
                                                     switch ($result->fields["estado"]){
                                                         case "oc":
                                                                  $estado="Ingreso" ;
                                                                  break;
                                                         case "a":
                                                                  $estado="Descuento";
                                                                  break;
                                                     }
                                                     echo $estado;
                                                     ?>
                                                  </td>
                                                </tr>
                                                <?

                                                $result->movenext();
                                            }
               // } //del elseif ($cmd=="historial"){ */
               ?>
                </table>
            </td>
        </tr>
        <?
        /*
        if ($cmd=="real") {
        ?>
        <tr>
            <td>
               <table width=20% align=center  class=bordes>
                  <tr>
                     <td><b>Monto :   U$S</b></td>
                     <td align=right><b><?=formato_money($total_busqueda_dolares)?></b></td>
                  </tr>
                  <tr>
                     <td><b>Monto :   $</b></td>
                     <td align=right><b><?=formato_money($total_busqueda_pesos)?></b></td>
                  </tr>
               </table>
            </td>
        </tr>
        <?
        }
        */
        ?>

    </table>
<?
/*
if($_ses_user["login"]=="marcos")
{?>
	<input type="submit" name="ajustar_log_mov_stock" value="Ajustar log_mov stock">
<?
}*/
?>
</form>
<?fin_pagina(); ?>