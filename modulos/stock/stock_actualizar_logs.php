<?php
/*
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/07/27 21:34:58 $
*/
require_once("../../config.php");


$id_deposito=$parametros["id_deposito"] or $id_deposito=$_POST["id_deposito"];


if ($_POST["button"]=="Actualizar")
      {
       $db->starttrans();
       $cantidad=$_POST["cantidad"];
       $fecha=date("2004-02-01 h:i:s");
       $usuario="Sistema";

       for ($i=0;$i<$cantidad;$i++)
           {
           if ($_POST["ch_producto_$i"]!="")

            {
            $id_producto=$_POST["ch_producto_$i"];
            $sql="select * from stock where id_producto=$id_producto and id_deposito=$id_deposito";
            $stock_producto=$db->execute($sql) or die($sql."<br>".$db->errormsg());

            $cant_productos=$stock_producto->recordcount();


            $cant_disp=0;
            for($y=0;$y<$cant_productos;$y++)
            {

            $id_proveedor=$stock_producto->fields["id_proveedor"];
            $cant_disp=$stock_producto->fields["cant_disp"];

            $sql="select sum(cant_desc) as cantidad,id_producto ";
            $sql.=" from descuento join control_stock using (id_control_stock)";
            $sql.=" where ";
            $sql.=" id_producto=$id_producto and ";
            $sql.=" id_proveedor=$id_proveedor and";
            $sql.=" id_deposito=$id_deposito and";
            $sql.=" estado='a'";
            $sql.=" group by id_producto";
            $descuento=$db->execute($sql) or die($sql."<br>".$db->errormsg());
            $cant_desc=$descuento->fields["cantidad"];
            $cant_disp+=$cant_desc;

            //INSERTO LOS LOG
            $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
            $resultado=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");
            $id_control_stock=$resultado->fields["id_control_stock"];

            $query="insert into control_stock
                  (id_control_stock,fecha_modif,usuario,comentario,estado)
                   values($id_control_stock,'$fecha','$usuario','Actualizacion stock inicial por el sistema','as')";
            $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

            $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
                 values($id_deposito,$id_producto,$id_proveedor,$id_control_stock,$cant_disp)";
            $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento<br>".$query);
            //echo "<br>$query<br>";
            $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
                 values ($id_control_stock,'$usuario','$fecha','Stock Anterior')";
            $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");
            $stock_producto->movenext();
            }//del segundo for

           }


      $db->completetrans();
           }

      }//del if de actualizar


$sql="
  select distinct(stock.id_producto),
         stock.id_deposito,productos.tipo,
         productos.desc_gral,productos.marca,productos.modelo
         from stock
   join general.productos using(id_producto)
   join general.depositos using(id_deposito)
   ";
  $where="    id_deposito=$id_deposito";
$orden=array(
                 "default" => "6",
                  "2" => "desc_gral",
                  "3" => "marca",
                  "4" => "modelo",
                  "5" =>  "precio",
                  "6" => "tipo",
                  );

$filtro= array(
                "desc_gral" => "Descripción",
                "cant_disp"=>"Stock Disponible ",
                "marca" =>"Marca",
                "modelo" =>"Modelo"
                 );


echo $html_header;


?>
<form action="stock_actualizar_logs.php" method=post>
<input type=hidden name=id_deposito value="<?=$id_deposito?>">
<table width=100% align=center>
<tr id=mo>
   <td>
    Actualiza los log iniciales de los productos - OJO!!!!!!!!
   </td>
</tr>
<tr>
  <td align=center>
  <?
  $contar="buscar";
  list($query_productos,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
  $resultado=$db->execute($query_productos) or die($query_productos."<br>".$db->errormsg());
  $cantidad=$resultado->recordcount();
  ?>
  <input type=submit name=form_busqueda value='Buscar'>
  </td>
</tr>
<input type=hidden name=cantidad value="<?=$cantidad?>">
<tr>
   <td>
   <table width=100% align=center>
     <tr><td colspan=5 align=left id="mo_sf"> <b>Total:<?=$total?> </td></tr>

       <tr>
             <td id="mo">&nbsp;</td>
             <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>6,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclickcargar"=>$onclickcargar,"onclicksalir"=>$onclicksalir));?>">Tipo</a></td>
             <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>2,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclickcargar"=>$onclickcargar,"onclicksalir"=>$onclicksalir));?>">Descripci&oacute;n Producto  </a></td>
             <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>3,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclickcargar"=>$onclickcargar,"onclicksalir"=>$onclicksalir));?>">Marca     </a></td>
             <td id="mo"><a href="<?=encode_link("listado_depositos.php",array("sort"=>4,"up"=>$up,"pagina_oc"=>$pagina_oc,"onclickcargar"=>$onclickcargar,"onclicksalir"=>$onclicksalir));?>">Modelo    </a></td>
      </tr>
   <?

   for($i=0;$i<$cantidad;$i++){


   ?>
      <tr bgcolor=#FFC0C0>
          <td><input type=checkbox name="ch_producto_<?=$i?>" value=<?=$resultado->fields["id_producto"]?>></td>
          <td align=left>   <b><?echo $resultado->fields["tipo"]?>   </b></td>
          <td align=left>   <b><?echo $resultado->fields["desc_gral"]?>   </b></td>
          <td align=left>   <b><?echo $resultado->fields["marca"]?>       </b></td>
          <td align=left>   <b><?echo $resultado->fields["modelo"]?>      </b></td>
      </tr>
   <?
   $resultado->movenext();
   }
   ?>
   </table>
  </td>
</tr>
<tr>
   <td align=center>
   <input type=submit name=button value="Actualizar">
   </td>
</tr>
</table>
</form>