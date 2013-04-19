<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2005/09/22 17:56:30 $
*/
require_once ("../../config.php");

$id_en_produccion=$parametros["id_en_produccion"] or $id_en_produccion=$_POST["id_en_produccion"];



$sql=
    "
        select ep.id_licitacion,ep.nro_orden,ep.cantidad,
                p.desc_gral,fila.precio_unitario,(fila.precio_unitario*ep.cantidad) as precio_total,p.marca,p.modelo,
                pro.razon_social,moneda.simbolo
        from stock.en_produccion ep
        join general.productos p using (id_producto)
        join general.proveedor pro using (id_proveedor)
        join compras.orden_de_compra using (nro_orden)
        join compras.fila  on(ep.nro_orden=fila.nro_orden and fila.id_producto=ep.id_producto) 
        join licitaciones.moneda using (id_moneda)
        where ep.id_en_produccion=$id_en_produccion
        ";
        
$resultado=sql($sql) or fin_pagina();
$id_licitacion=$resultado->fields["id_licitacion"];
$nro_orden=$resultado->fields["nro_orden"];
$cantidad =$resultado->fields["cantidad"];
$desc_gral=$resultado->fields["desc_gral"];
$marca=$resultado->fields["marca"];
$modelo=$resultado->fields["modelo"];
$precio=$resultado->fields["precio_unitario"];
$precio_total=$resultado->fields["precio_total"];
$simbolo=$resultado->fields["simbolo"];

      
echo $html_header;


 $link_oc = encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$nro_orden));
 $link_lic = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));

?>

<form name = form1 method = post>
<input type=hidden name=id_en_produccion value=<?=$id_en_produccion?>>
    <table width = 70% align = center class = bordes bgcolor=<?=$bgcolor3?>>
        <tr id=mo>
            <td align = center>
                <font size = 3><b>Detalle Stock de Producción</b> </font>
            </td>
        </tr>
        <tr>
            <td width = 100% align = center>
                <table width = 100% align = center>
                    <tr>
                        <td id=ma_sf width=20%>Descripción:</td>
                        <td><b><?= $desc_gral ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf >Marca:</td>
                        <td> <b><?= $marca ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf >Modelo</td>
                        <td><b><?= $modelo ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf>Orden de Compra:</td>
                        <td><b><a href=<?=$link_oc?> target=_blank><?= $nro_orden ?></a></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf>Id Licitación</td>
                        <td><b><a href=<?=$link_lic?> target=_blank><?= $id_licitacion ?></a></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf>Cantidad</td>
                        <td><b><?= $cantidad ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf>Precio Unitario</td>
                        <td><b><?= "$simbolo  ".formato_money($precio) ?></b></td>
                    </tr>
                    <tr>
                        <td id=ma_sf>Precio Total</td>
                        <td><b><?= "$simbolo  ".formato_money($precio_total) ?></b></td>
                    </tr>
                    
                </table>
            </td>
        </tr>
        <tr>
            <td align = center>
                <input type = button value = Volver name = volver onclick="location.href='./stock_produccion.php';">
            </td>
        </tr>
    </table>
</form>
<?echo fin_pagina()?>