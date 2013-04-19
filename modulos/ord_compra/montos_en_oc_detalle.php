<?
/*
Autor: MAC
Fecha: 06/09/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2005/09/06 19:30:00 $
*/

require_once("../../config.php");

variables_form_busqueda("montos_en_oc");

$string_oc=$parametros["string_oc"];
$fecha_desde=$parametros["fecha_desde"];
$fecha_hasta=$parametros["fecha_hasta"];

//traemos los datos de las OC pasadas como parametros, para mostrar en el listado
$query="select nro_orden,id_licitacion,es_presupuesto,flag_stock,flag_honorario,orden_prod,internacional,nrocaso,orden_de_compra.fecha_entrega,
         estado,cliente,razon_social,plantilla_pagos.descripcion as forma_pago,id_moneda,simbolo,monto_filas
         from orden_de_compra join plantilla_pagos using(id_plantilla_pagos)
         join proveedor using(id_proveedor) join moneda using(id_moneda)
         join( select sum(cantidad*precio_unitario) as monto_filas,nro_orden
               from fila group by nro_orden
         	 )as montos using(nro_orden)
         ";
	 
$where=" nro_orden in ($string_oc)";

$orden=array(
            "default"=>"3",
            "default_up"=>"0",
            "1"=>"nro_orden",
            "2"=>"id_licitacion",
            "3"=>"fecha_entrega",
            "4"=>"cliente",
            "5"=>"razon_social",
            "6"=>"monto_filas",
            "7"=>"plantilla_pagos.descripcion"
            );
            
$filtro=array(
             "nro_orden"=>"Nº Orden",
             "id_licitacion"=>"ID Licitación",
             "cliente"=>"Cliente",
             "razon_social"=>"Proveedor",
             "plantilla_pagos.descripcion"=>"Forma de Pago",
             );            

$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "monto_filas",
 		"mask" => array ("\$","U\$S")
			  );

echo $html_header;
?>
<table width="100%" cellpadding="5">
 <tr id="mo">
  <td>
   <font size="2">
   Ordenes de Compra adeudadas 
   <?
   
   if($fecha_desde!="")
   {
    echo "desde el ".Fecha($fecha_desde)." hasta el ";
   }
   elseif($fecha_hasta!="")
   	 echo "al ";
   echo Fecha($fecha_hasta);
   ?>
   </font>
  </td>
 </tr>
</table> 
<?
$link=encode_link("montos_en_oc_detalle.php",array("string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta));
?>
<form action="<?=$link?>" method="POST" name="form1">
<table width="100%">
 <tr>
  <td align="center">
	<?			  
	$link_tmp=array("string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta);
	if($string_oc=="")
	 die("<b>Error: No se pudieron especificar las OC incluidas en el cálculo</b>");
	list($query,$total,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar",$sumas);
	$datos_oc=sql($query,"<br>Error al ejecutar consulta de busqueda<br>") or fin_pagina();             
	?>
    <input type="submit" name="buscar" value="Buscar">
  </td>
 </tr>
</table>  
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr id=ma>
	<td style="text-align:left" >
     <b>
      Total Ordenes de Compra: <?=$total?>
	 </b>
    </td>
    <td>
     <font color="Black">Total <?=$suma?> </font>
    </td>
    <td>
     <?=$link_pagina?>
    <td>
  </tr>
</table>  
<table border="0" cellspacing="2" cellpadding="0" width="100%">
  <tr id=mo>
    <td width="1%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>Nº Orden</a>
    </td>
    <td width="1%">
     TIPO
    </td>
    <td width="1%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>
      ID Licitación
     </a>
    </td>
    <td width="10%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>
      Fecha Entrega
     </a>
    </td>
    <td width="30%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"4","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>
      Cliente
     </a>
    </td>
    <td width="30%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"5","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>
      Proveedor
     </a>
    </td>
    <td width="10%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>
      Monto
     </a>
    </td>
    <td width="20%">
     <a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"7","up"=>$up,"string_oc"=>$string_oc,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta)) ?>'>
      Forma de Pago
     </a>
    </td>
  </tr>
  <?
  while (!$datos_oc->EOF)
  {
   $ref=encode_link("ord_compra.php",array("nro_orden"=>$datos_oc->fields["nro_orden"]));	 	
   ?>
   <tr <?=atrib_tr()?>>	
    <a href='<?=$ref?>' target="_blank">
    <td>
     <?=$datos_oc->fields["nro_orden"]?>
    </td>
    <td>
     <?if($datos_oc->fields["id_licitacion"])
       {
       	 if($datos_oc->fields["es_presupuesto"])
       	  $tipo="Pres";
       	 else 
       	  $tipo="Lic";
       }
       elseif($datos_oc->fields["flag_stock"])
        $tipo="Stock";
       elseif($datos_oc->fields["flag_honorario"])
        $tipo="HST";
       elseif($datos_oc->fields["nrocaso"])
        $tipo="ServT";  
       elseif($datos_oc->fields["orden_prod"])
        $tipo="RMA";
       elseif($datos_oc->fields["internacional"])
        $tipo="INT";
       else 
        $tipo="Otro";
       
       echo $tipo;    
     ?>
    </td>
    <td>
     <?=$datos_oc->fields["id_licitacion"]?>
    </td>
    <td>
     <?=Fecha($datos_oc->fields["fecha_entrega"])?>
    </td>
    <td>
     <?=$datos_oc->fields["cliente"]?>
    </td>
    <td>
     <?=$datos_oc->fields["razon_social"]?>
    </td>
    <td>
     <?=$datos_oc->fields["simbolo"]." ".number_format($datos_oc->fields["monto_filas"],2,'.','')?>
    </td>
     <?if(strlen($datos_oc->fields['forma_pago'])>20)
      {$titulo_pago=substr($datos_oc->fields['forma_pago'],0,17);
       $titulo_pago.="...";
       $title_titulo_pago=$datos_oc->fields['forma_pago'];
      }
      else 
      {$titulo_pago=$datos_oc->fields['forma_pago'];
       $title_titulo_pago="";
      }
      ?>
     <td title='<?=$title_titulo_pago?>'>
      <?=$titulo_pago?>
     </td>
    </a> 
   </tr>
   <?
   $datos_oc->MoveNext();
  }//de while(!$datos_oc->EOF)
?>
</table>
</form>
</body>
</html>
