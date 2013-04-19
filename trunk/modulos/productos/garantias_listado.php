<?
/*
Author: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.3 $
$Date: 2006/10/18 21:53:31 $
*/

require_once("../../config.php");

echo  $html_header;

// me devuelve el tiempo de vencimiento de un producto
function vencimiento($fecha_creacion,$garantia){
	
	$fecha = split("/",fecha($fecha_creacion));
    $fecha_vencimiento = mktime(0,0,0,$fecha[1],$fecha[0]+($garantia*30),$fecha[2]);
    $fecha_hoy = mktime(0,0,0,date("m"),date("d"),date("Y"));
    /*echo date("Y-m-d",mktime(0,0,0,$fecha[1],$fecha[0]+($garantia*30),$fecha[2]));
    echo "<br>";
    echo date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")));
    echo "<br>";
    */
    $diferencia = $fecha_vencimiento - $fecha_hoy;              
    $ndias=floor($diferencia/(24*60*60));
                
    return $ndias;
}//de la funcion

variables_form_busqueda("garantia_producto");

//traemos los productos
$orden = array (
    "default" => "1",
    "1" => "descripcion",
    "2" => "nro_serie",
    "3" => "tiempo_garantia",
    "4" => "nombre",
  );

$filtro = array (
    "descripcion" => "Descripción",
    "nro_serie"   => "Nro Serie",
    "tiempo_garantia" => "Garantía",
    "nombre" =>"Entidad"
);

$sql = "select gp.id_garantia_producto,gp.fecha_creacion,pe.descripcion,gp.tiempo_garantia,gpns.nro_serie,
        entidad.nombre
        from garantia_producto gp
        join producto_especifico pe using (id_prod_esp)
        join garantia_prod_numeros_series gpns using (id_garantia_producto)
        left join licitaciones.entidad using (id_entidad)
        ";


$link = encode_link("garantias_nueva.php",array("modo"=>"nueva"));
?>
<form name=form1 method="POST" action="garantias_listado.php">

<table class="bordes" width="95%" align="center">
  <tr id="mo"><td>Listado Con Las Garantias</td></tr>
  <tr>
    <td align="center">
    <?
    list($sql,$total_productos,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
    $res = sql($sql) or fin_pagina();
    ?>
    &nbsp;
    <input type="submit" name="Buscar" value="Buscar">
    &nbsp;
    <input type="button" name="nueva_garantia" value="Nueva Garantia" onclick="window.open('<?=$link?>')">
    </td>
  </tr>
  <tr>
    <td width="100%" align="center">
       <table width="100%" align="center">
          <tr id=ma>
            <td colspan="3" align="left"><?=$total_productos?> Garantias</td>
            <td colspan="2" align="right"><?=$link_pagina?></td>
         </tr>
         <tr id=mo>
           <a id=mo href='<?=encode_link("garantias_listado.php",array("sort"=>"2","up"=>$up))?>'> <td width="5%">Nro Serie</td> </a>
           <a id=mo href='<?=encode_link("garantias_listado.php",array("sort"=>"1","up"=>$up))?>'> <td width="40%">Producto</td> </a>
           <a id=mo href='<?=encode_link("garantias_listado.php",array("sort"=>"4","up"=>$up))?>'> <td width="40%">Entidad</td> </a>
           <a id=mo href='<?=encode_link("garantias_listado.php",array("sort"=>"3","up"=>$up))?>'> <td>Garantía</td> </a>
           <td>Venc.</td>
         </tr>
         <?
         for($i=0;$i<$res->recordcount();$i++){
         	$link = encode_link("garantias_nueva.php",array("id_garantia_producto"=>$res->fields["id_garantia_producto"],"viene_de_listado"=>1));
         	tr_tag($link);
         	$dias = vencimiento($res->fields["fecha_creacion"],$res->fields["tiempo_garantia"])
	 	 ?>
            <td><?=$res->fields["nro_serie"]?>      </td>  
            <td><?=$res->fields["descripcion"]?>    </td>
            <td><?=$res->fields["nombre"]?>    </td>
            <td><?=$res->fields["tiempo_garantia"]?> Meses</td>
            <td title="<?=$dias?>" align="center">
            <b><?
               if ($dias>=0) echo " SV";
                   else echo  "<font color=red> V </font>";
            ?>
            </b>
            </td>
	 	    </tr>
	 	 <?
	 	 $res->movenext();
         }
		 ?>
       </table>      
    </td>
  </tr>
</table>
<br>
<table width="40%" class="bordes" align="center">
  <tr id=ma><td>Garantia Vencida = <font color="Red">V</font></td><td> Garantia sin Vencer = SV </tr>
</table>

<br>
</form>
<?
echo fin_pagina();
?>
