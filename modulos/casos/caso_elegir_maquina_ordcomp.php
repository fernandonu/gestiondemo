<?
/*
MODIFICADA POR
$Author: fernando $
$Revision: 1.16 $
$Date: 2005/04/04 22:02:17 $
*/

require_once("../../config.php");
$maquina=$_GET['serie'];

$nro_serie=$_POST['serial'] or $nro_serie=$_GET['serie'];
$id_licitacion=$_POST["lic_id"];

$condicion=" where maquina.nro_serie ilike '%$nro_serie%' ";
if ($id_licitacion)
  $condicion.=" and licitacion.id_licitacion=$id_licitacion";



$q="select nro_serie,nro_orden,id_licitacion
            from maquina
            join orden_de_produccion using(nro_orden)
            left join licitacion using(id_licitacion)
            $condicion
            order by maquina.nro_serie";

// print_r($_POST);
 //die($q);
$resultado_maquina=sql($q) or fin_pagina();
?>
<?=$html_header?>
<script>

 /*Armo los datos de la maquina*/
<?
while (!$resultado_maquina->EOF)
{
?>
     var maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>=new Array();

	maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nro_serie"]="<?php if($resultado_maquina->fields["nro_serie"]){echo $resultado_maquina->fields["nro_serie"];} ?>";
    maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nro_orden"]="<?php echo $resultado_maquina->fields["nro_orden"]; ?>";
    maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_licitacion"]="<?php if($resultado_maquina->fields["id_licitacion"]){echo $resultado_maquina->fields["id_licitacion"];}else echo "No se encuentra asociada a ninguna licitacion";?>";
    maquina_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["id_licitacion_ref"]="<?php if($resultado_maquina->fields["id_licitacion"]){echo encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$resultado_maquina->fields["id_licitacion"]));}else echo "No se encuentra asociada a ninguna licitacion";?>";
<?

//MAC DICE-->ESTO TAL VEZ SE PODRIA OPTIMIZAR, PERO NO HAY TIEMPO AHORA:
 //cargamos los productos de las ordenes de compra asociadas
 //a la licitacion que está asociada a la orden de producción
 //que tiene la máquina con el número de serie elegido
if($resultado_maquina->fields["id_licitacion"]!="")
 $id_lic_oc=$resultado_maquina->fields["id_licitacion"];
else
 $id_lic_oc=0;
 $query="select descripcion_prod,precio_unitario,simbolo,oc.cantidad,
         oc.nro_compra,proveedor 
         from licitaciones.licitacion
         join (select nro_orden as nro_compra,razon_social as proveedor,id_licitacion,descripcion_prod,precio_unitario,fila.cantidad,simbolo
                      from compras.orden_de_compra
                      join compras.fila using(nro_orden)
                      join proveedor using (id_proveedor) 
					  join licitaciones.moneda using(id_moneda)
                      where orden_de_compra.estado<>'n'
              )as oc using(id_licitacion)
         where  id_licitacion=$id_lic_oc
         order by descripcion_prod,nro_compra";

  $result_prod_compra=sql($query,"<br>Error<br> $query<br>") or fin_pagina();

 //con eso formamos el arreglo de productos que debemos mostrar
 ?>
 maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"]=new Array();
 <?
 $jk=0;

  while(!$result_prod_compra->EOF)
  {
  $descripcion_prod=ereg_replace("\r\n","<br>",$result_prod_compra->fields['descripcion_prod']);
  $descripcion_prod=ereg_replace("\n","<br>",$descripcion_prod);
  $descripcion_prod=ereg_replace("\""," ",$descripcion_prod);
  $descripcion_prod=ereg_replace("\"\n"," ",$descripcion_prod);
  $descripcion_prod=ereg_replace("\'"," ",$descripcion_prod);






  $descripcion_proveedor=ereg_replace("\r\n","<br>",$result_prod_compra->fields['proveedor']);
  $descripcion_proveedor=ereg_replace("\n","<br>",$descripcion_proveedor);
  $descripcion_proveedor=ereg_replace("\""," ",$descripcion_proveedor);
  $descripcion_proveedor=ereg_replace("\"\n"," ",$descripcion_proveedor);
  $descripcion_proveedor=ereg_replace("\'"," ",$descripcion_proveedor);

  ?>
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]=new Array();
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["cantidad"]="<?if($result_prod_compra->fields['cantidad']){echo $result_prod_compra->fields['cantidad'];}else echo " ";?>";
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["descripcion_prod"]="<?=$descripcion_prod?>";
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["nro_compra"]="<?if($result_prod_compra->fields['nro_compra']){echo $result_prod_compra->fields['nro_compra'];}else echo " ";?>";
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["link_ord_compra"]="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$result_prod_compra->fields['nro_compra']));?>";
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["simbolo"]="<?if($result_prod_compra->fields["simbolo"]){echo $result_prod_compra->fields["simbolo"];}else echo " ";?>";
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["precio"]="<?if($result_prod_compra->fields["precio_unitario"]){echo formato_money($result_prod_compra->fields["precio_unitario"]);}else echo " ";?>";
   maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][<?=$jk?>]["proveedor"]="<?=$descripcion_proveedor;?>";

 <?
   $jk++;
   $result_prod_compra->MoveNext();
  }//de  while(!$result_prod_compra->EOF)

 $resultado_maquina->MoveNext();
 }

//cargo productos asociadas a cada maquina
/*$resultado_maquina->Move(0);
while(!$resultado_maquina->EOF)
{
}
	*/
//cargo ordenes asociadas a cada maquina
$resultado_maquina->Move(0);
while (!$resultado_maquina->EOF)
{
?>
var ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>=new Array();
ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nro_orden"]=new Array();
ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["link_orden"]=new Array();
ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nombre_prov"]=new Array();
ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["fecha_entrega"]=new Array();
<?
if ($resultado_maquina->fields['id_licitacion']!="")
{
$q="select fecha_entrega,nro_orden,razon_social from orden_de_compra join factura_asociadas using(nro_orden) join fact_prov using (id_factura) join proveedor on fact_prov.id_proveedor=proveedor.id_proveedor where id_licitacion=".$resultado_maquina->fields['id_licitacion'];
$resultado_orden=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");

 $i=0;
 while (!$resultado_orden->EOF)
 {
$nombre_prov=ereg_replace("\r\n","<br>",$resultado_orden->fields['razon_social']);
$nombre_prov=ereg_replace("\""," ",$nombre_prov);
$nombre_prov=ereg_replace("'"," ",$nombre_prov);
$nombre_prov=ereg_replace("\n"," ",$nombre_prov);
?>
 ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nro_orden"][<?=$i;?>]="<?=$resultado_orden->fields['nro_orden'];?>";
 ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["nombre_prov"][<?=$i;?>]="<?=$nombre_prov?>";
 ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["link_orden"][<?=$i;?>]="<?=(encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$resultado_orden->fields['nro_orden'])));?>";
 ordenes_<?php echo $resultado_maquina->fields["nro_serie"]; ?>["fecha_entrega"][<?=$i;?>]="<?=$resultado_orden->fields['fecha_entrega'];?>";
 //cargo facturas asociadas a la orden
 <?/*
 //var facturas_<?php echo $resultado_orden->fields["nro_orden"]; ?>=new Array();
 //facturas_<?php echo $resultado_orden->fields["nro_orden"]; ?>["nro_factura"]=new Array();
 //facturas_<?php echo $resultado_orden->fields["nro_orden"]; ?>["link_factura"]=new Array();
 <?
 $q="select fact_prov.nro_factura,fact_prov.id_factura from factura_asociadas join fact_prov using(id_factura) where nro_orden=".$resultado_orden->fields['nro_orden'];
 $resultado_facturas=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
 $j=0;
 while (!$resultado_facturas->EOF)
 {
  $link=encode_link("../factura_proveedores/fact_prov_subir.php", array("fact" =>$resultado_facturas->fields["id_factura"]));
?>
 facturas_<?php echo $resultado_orden->fields["nro_orden"]; ?>["nro_factura"][<?=$j;?>]="<?=$resultado_facturas->fields['nro_factura'];?>";
 facturas_<?php echo $resultado_orden->fields["nro_orden"]; ?>["link_factura"][<?=$j;?>]="<?=$link;?>";
 <?
$resultado_facturas->MoveNext();
$j++;
 }
 */
$resultado_orden->MoveNext();
$i++;
 }
}
$resultado_maquina->MoveNext();
}
?>

function cargar_ventana_produccion()
{window.open('../ordprod/control_orden.php?nro_orden='+document.all.produccion.value,'','menubar=1,toolbar=1,resizable=1,location=1,scrollbars=1');
}

function cargar_ventana_licitacion()
{window.open(document.all.ref_licitacion.value,'','menubar=1,toolbar=1,resizable=1,location=1,scrollbars=1');
}

function cargar_orden()
{divi=this.name.split("_");
 serie=divi[2];
 cont=divi[3];
 link=eval("ordenes_"+serie+"['link_orden']["+cont+"]");
 window.open(link,'','');
}

function cargar_factura()
{divi=this.name.split("_");
 orden=divi[2];
 cont2=divi[3];
 link=eval("facturas_"+orden+"['link_factura']["+cont2+"]");
 window.open(link,'','');
}

var info=new Array();

function set_datos()
{var i=0;
 switch(document.all.select_maquina.options[document.all.select_maquina.selectedIndex].value)
	{<?PHP
	 $resultado_maquina->Move(0);
	 while(!$resultado_maquina->EOF)
	 {?>
	  case '<? echo $resultado_maquina->fields["nro_serie"]?>':info=maquina_<? echo $resultado_maquina->fields["nro_serie"];?>;
	                                                          table = document.getElementById("tabla_1");
	                                                          fila=100;
                                                              fila=table.rows.length;
                                                              for(i=2;i<(fila+2);i++)
	                                                           {
                                                                 if (typeof(table.rows[i])!="undefined")
                                                                    table.deleteRow(i);
                                                               }//DEL FOR

	                                                           fila=2;

	                                                           cont=0;

                                                              //HASTA ACA BORRO

                                                             //ACA BORRA LOS PRODUCTOS

  															  cant_prod=0;

  															  var jk=0;
  															  var rm;

  															  var tabla_2_del=document.all.tabla_2;
  															   rm=tabla_2_del.rows.length;
  															  for(jk;jk<rm;jk++)
  															  {
                                                               tabla_2_del.deleteRow(0);
  															  }

                                                              //ACA MUESTRA LOS PRODUCTOS

  															  //mostramos todos los productos que estan bajo ese numero de serie
  															  var head=document.all.tabla_2.insertRow(document.all.tabla_2.rows.length);
  															  head.style.backgroundColor="#EEEEEE";

  												     		  head.insertCell(0).align="center";
  												     		  head.cells[0].innerHTML="Cant";
  												     		  head.insertCell(1).align="center";
  												     		  head.cells[1].innerHTML="Producto";
  												     		  head.insertCell(2).align="center";
  												     		  head.cells[2].innerHTML="Nº OC";
															  head.insertCell(3).align="center";
  												     		  head.cells[3].innerHTML="Prov.";
                                                              head.insertCell(4).align="center";
                                                              head.cells[4].innerHTML="Moneda";
  												     		  head.insertCell(5).align="center";
  												     		  head.cells[5].innerHTML="Precio";

  												     		 if(typeof(maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"])!="undefined")
  												     		 {
  												     		  while(cant_prod<maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"].length)
  															  {
                                                              var fila=document.all.tabla_2.insertRow(document.all.tabla_2.rows.length);

  												     		   fila.insertCell(0).align="center";
  												     		   fila.cells[0].innerHTML=maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["cantidad"];
  												     		   fila.insertCell(1).align="left";
  												     		   fila.cells[1].innerHTML=maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["descripcion_prod"];
  												     		   fila.insertCell(2).align="left";
  												     		   fila.cells[2].innerHTML="<a href='"+maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["link_ord_compra"]+"' target='_blank'>"+maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["nro_compra"]+"</a>";
                                                               fila.insertCell(3).align="left";
  												     		   fila.cells[3].innerHTML=maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["proveedor"];
															   fila.insertCell(4).align="left";
                                                               fila.cells[4].innerHTML=maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["simbolo"];
  												     		   fila.insertCell(5).align="right";
  												     		   fila.cells[5].innerHTML=maquina_<?=$resultado_maquina->fields["nro_serie"];?>["productos_oc"][cant_prod]["precio"];

  															   cant_prod++;
  															  }//de	while(cant_prod<maquina_$resultado_maquina->fields["nro_serie"];["productos_oc"].length)
  												     		 }//de if(typeof(maquina_$resultado_maquina->fields["nro_serie"];)!="undefined")
	                                                           break;
	 <?
	  $resultado_maquina->MoveNext();
	 }
	 ?>
	} //del switch

	document.all.licitacion.value=info["id_licitacion"];
	document.all.produccion.value=info["nro_orden"];
	document.all.cargar3.disabled=false;
	document.all.cargar3.onclick=cargar_ventana_produccion;
	if(info['id_licitacion_ref']!="No se encuentra asociada a ninguna licitacion")
	 {document.all.ref_licitacion.value=info['id_licitacion_ref'];
	  document.all.cargar2.disabled=false;
	  document.all.cargar2.onclick=cargar_ventana_licitacion;
	 }
	else
	{document.all.cargar2.disabled=true;
	 document.all.cargar2.onclick="";
	}

	// muestro ordenes de compra asociadas
} //fin de la funcion set_datos()

</script>
<?
$link=encode_link("caso_elegir_maquina_ordcomp.php",array());
?>
<form name="form1" method="post" action="<? echo $link; ?>">
   <input type="hidden" name="telefono">
   <input type="hidden" name="id_entidad">
   <input type="hidden" name="ref_licitacion">
   <table width="100%" align="center">
      <tr id="mo">
        <td>Serial</td>
        <td>Licitacion ID</td>
        <td></td>
     </tr>
     <tr id="ma">
        <td>
          <input type="text" id="serial" name="serial" value="<?=$nro_serie?>">
          <input type="hidden" name="hidden_ser" value="0">
       </td>
       <td>
          <input type="text" id="lic_id" name="lic_id" value="<?=$id_licitacion?>">
          <input type="hidden" name="hidden_lic" value="0">
       </td>
       <td>
         <input type="submit" id="boton_buscar" value="Buscar" >
       </td>
     </tr>
</table>
<table width=100% align=center>
  <tr>
     <td id=mo align="center" width="100%">Selección de Maquinas</td>
  </tr>
   <tr>
      <td align=center valign="top">
         <select name="select_maquina" id="select_maquina" size="10" style="width:60%" onchange="set_datos();">
	        <?
	         $resultado_maquina->Move(0);
	         while (!$resultado_maquina->EOF)
	          {
	          ?>
			  <option value="<?=$resultado_maquina->fields['nro_serie']; ?>"><?=$resultado_maquina->fields['nro_serie']; ?></option>
	         <?
             $resultado_maquina->MoveNext();
	         }
             ?>
             </select>
      </td>
</tr>

</table>

<table id="tabla_1" class="bordes" bgcolor="<?=$bgcolor_out;?>" cellpadding="0" cellspacing="0" width=100% align=center>
    <tr><td>
        <b>Orden de Produccion:<input type="text" name="produccion" value="" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;cursor:hand" size="4"><input type="button" name="cargar3" value="Ir" style="cursor:hand" disabled>
        </td>
        <td>
        <b>Licitación:<input type="text" name="licitacion" value="" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;cursor:hand" size="4"><input type="button" name="cargar2" value="Ir" style="cursor:hand" disabled>
       </td>
    </tr>
    <tr><td id=mo colspan=2>Ordenes de Compra Asociadas</td></tr>
</table>

<table id="tabla_2" class="bordes" border="1" width="100%" bgcolor="<?=$bgcolor_out;?>" cellpadding="0" cellspacing="0">
</table>
<center>
 <input name="cancelar" type="button" value="Salir" onclick="window.close();" style="width:'10%'" style="cursor:hand">
</center>
</form>
</body>
<?=fin_pagina()?>