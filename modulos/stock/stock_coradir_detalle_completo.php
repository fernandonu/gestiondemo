<?
/*
AUTOR: MAC
FECHA: 30/05/06

MODIFICADO POR:
$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2006/07/18 16:24:43 $
*/

require_once("../../config.php");
require_once("funciones_stock_completo.php");

$id_prod_esp=$_POST["id_prod_esp"] or $id_prod_esp=$parametros["id_prod_esp"];


$query_depositos="select depositos.nombre,depositos.id_deposito
			   from general.depositos
			   where tipo>=0
			   order by depositos.nombre";
$dep_en_cuenta=sql($query_depositos,"<br>Error al traer los depósitos tomados en cuenta<br>") or fin_pagina();

$first=$_POST["filtrar"];
$add_where=agregar_dep_consulta($dep_en_cuenta,$first);
if($add_where)
	$add_consulta="and ($add_where) ";


$sql="
    select en_stock.cant_disp,en_stock.cant_reservada,en_stock.cant_a_confirmar,
    	   (en_stock.cant_disp + en_stock.cant_reservada+en_stock.cant_a_confirmar)  as cant_total,
    	   pe.id_prod_esp,pe.descripcion,pe.precio_stock,depositos.nombre as  nombre_deposito,
    	   depositos.id_deposito,depositos.tipo
   from stock.en_stock
   join general.producto_especifico as pe using(id_prod_esp)
   join general.depositos using (id_deposito)
   where pe.id_prod_esp = $id_prod_esp
   		 and (en_stock.cant_disp + en_stock.cant_reservada + en_stock.cant_a_confirmar) > 0 $add_consulta
   order by depositos.nombre
    ";
$res=sql($sql,"<br>Error al traer los datos del stock para el producto") or fin_pagina();

echo $html_header;
$link_form=encode_link("stock_coradir_detalle_completo.php",array("checks_default"=>$first))
?>

<script>
var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla,oimg)
{

	if (obj_tabla.style.display=='none')
	{
		obj_tabla.style.display='block';
		oimg.show=0;
		oimg.src=img_ext;
	}
	else
	{
		obj_tabla.style.display='none';
		oimg.show=1;
		oimg.src=img_cont;
	}
}//de function muestra_tabla(obj_tabla,nro)
</script>

<form name=form1 method=post action="<?=$link_form?>">
<input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">

<table width="90%" align="center">
 <tr>
  <td>
   <?
	depositos_considerados($dep_en_cuenta,$first);
	?>
  </td>
 </tr>
 <tr>
  <td align="center">
	<input type="submit" name="filtrar" value="Filtrar Depósitos">
  </td>
 </tr>
</table>
<hr>
<table width="60%" align="center" class=bordes>
   <tr id="mo">
      <td>Cantidades por stock</td>
   </tr>
   <tr>
     <td>
     <table width="100%" align="center">
        <tr>
           <td id=ma_sf>Producto</td>
           <td><font color="Blue"><b><?=$res->fields["descripcion"]?></b></font></td>
        </tr>
        <tr>
           <td id=ma_sf>Precio Unitario</td>
           <td><b>U$S &nbsp;<?=formato_money($res->fields["precio_stock"])?></b></td>
        </tr>
     </table>
     </td>
   </tr>
   <tr id=mo><td>Cantidades</td></tr>
   <tr>
      <td>
         <table width="100%" align="center">
            <tr id=ma>
               <td>Stock          </td>
               <td>Cant. Disp     </td>
               <td>Cant. Reservada</td>
               <td>Cant. a Confirmar</td>
               <td>Cant. Total    </td>
               <td>Precio Total</td>
            </tr>
            <?
            $cant_reservada=$cant_disponible=$cant_total=0;

            for($i=0;$i<$res->recordcount();$i++)
            {

                switch ($res->fields["tipo"]){
                    case 0:
                        $link=encode_link("stock_descontar.php",array("id_prod_esp"=>$res->fields["id_prod_esp"],"id_deposito"=>$res->fields["id_deposito"]));
                        break;
                     case 2:
                        $link=encode_link("listar_rma.php", array("keyword"=>$res->fields["descripcion"],"filter"=>"desc_gral","cmd"=>"tran"));
                        break;
                     case 4:
                        $link=encode_link("stock_produccion.php",array("keyword"=>$res->fields["descripcion"],"filter"=>"descripcion","cmd"=>"real"));
                        break;

                }

	             $precio=$res->fields['cant_total']*$res->fields["precio_stock"];
	            ?>
	               <tr <?=atrib_tr()?> onclick="window.open('<?=$link?>')">
	                   <td align="left"> <b><?=$res->fields["nombre_deposito"]?> </b></td>
	                   <td align="right"><b><?=$res->fields['cant_disp'];?>      </b></td>
	                   <td align="right"><b><?=$res->fields['cant_reservada'];?> </b></td>
	                   <td align="right"><b><?=$res->fields['cant_a_confirmar'];?> </b></td>
	                   <td align="right"><b><?=$res->fields['cant_total']?>      </b></td>
	                   <td align="right">
	                    <table width="100%">
	                     <tr>
	                      <td>
	                       <b>U$S</b>
	                      </td>
	                      <td align="right">
	                   		<b><?=formato_money($precio)?></b>
	                   	  </td>
	                   	 </tr>
	                   	</table>
	                   </td>
	               </tr>
	            <?
	            $cant_reservada+=$res->fields["cant_reservada"];
	            $cant_disponible+=$res->fields["cant_disp"];
	            $cant_a_confirmar+=$res->fields["cant_a_confirmar"];
	            $cant_total+=$res->fields["cant_total"];
	            $precio_total+=$precio;
	            $res->movenext();
            }//de for($i=0;$i<$res->recordcount();$i++)
            ?>
            <tr bgcolor="white">
              <td align="left"><b>Totales</b></td>
              <td align="right"><b><font color=red><?=$cant_disponible?></font><b></td>
              <td align="right"><b><font color=red><?=$cant_reservada?></font><b></td>
              <td align="right"><b><font color=red><?=$cant_a_confirmar?></font><b></td>
              <td align="right"><b><font color=red><?=$cant_total?></font><b></td>
              <td align="right">
                    <table width="100%">
                     <tr>
                      <td>
                       <b><font color="Red">U$S</font></b>
                      </td>
                      <td align="right">
                   		<b><font color="Red"><?=formato_money($precio_total)?></font></b>
                   	  </td>
                   	 </tr>
                   	</table>
              </td>
            </tr>
         </table>
       </td>
   </tr>
</table>
<?
mostrar_ing_egr_global($id_prod_esp,$add_where);
mostrar_reservas_stock($id_prod_esp,$add_where);
mostrar_a_confirmar_stock($id_prod_esp,$add_where);
?>
<table width="100%">
   <tr><td align=center><input type=button name=cerrar value="Cerrar" onclick="window.close()"></td></tr>
</table>
</form>
<?fin_pagina() ?>