<?php
/*
$Author: ferni $
$Revision: 1.5 $
$Date: 2006/02/08 20:26:52 $
*/

require_once("../../config.php");

$renglones_oc_array=PostvartoArray("chk_");

$id_renglones_oc=$_POST["id_renglones_oc"] or $id_renglones_oc=implode(",",$renglones_oc_array);

if ($_POST["atar_factura"]){
	$id_renglones_oc=$_POST["id_renglones_oc"];
	$id_items=$_POST['id_items'];
	if ($_POST['id_items']){
		$sql="update facturacion.items_factura 
		      set id_renglones_oc=$id_renglones_oc where id_item=$id_items ";
 		
		$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);?>
 		
 		<br>
		<div align="center">
			<font color="Green" size="3"><b>La Factura se Ato con Exito</b></font>
		</div>
		<br>
 		<?
	}
	else {?>
		<br>
		<div align="center">
			<font color="Red" size="3"><b>Debe Seleccionar un Items de una Factura</b></font>
		</div>
		<br>
	<?}
}//del if ($_POST["atar_factura"])

echo $html_header;
?>
<script>
	function confirmaAtado(){
		
	    var radio;
	    var indice;
	    radio=document.all.id_items;
		i=0;
		if (radio.length==undefined){
			hidden_conf = eval ("document.all.id_hidden_"+i+".value");
			hidden_conf_lic = eval ("document.all.id_hidden_lic_"+i+".value");
			hidden_conf_nombre = eval ("document.all.id_hidden_nombre_"+i+".value");
			
			if (hidden_conf=="1"){
				res = confirm("Esta Factura ya se Encuentra Vinculada al Id: "+ hidden_conf_lic +" Cliente: "+ hidden_conf_nombre +",   Esta Seguro que Desea Atar?");
				if (res==true){
					return true;
				}
				else{
					return false;
				}
			}
		}
		else{
			while (radio[i].checked==false){
				if (radio.length==(i+1)) return true;
				i++;
			}
		    	
			hidden_conf = eval ("document.all.id_hidden_"+i+".value");
			hidden_conf_lic = eval ("document.all.id_hidden_lic_"+i+".value");
			hidden_conf_nombre = eval ("document.all.id_hidden_nombre_"+i+".value");
			
			if (hidden_conf=="1"){
				res = confirm("Esta Factura ya se Encuentra Vinculada al Id: "+ hidden_conf_lic +" Cliente: "+ hidden_conf_nombre +",   Esta Seguro que Desea Atar?");
				if (res==true){
					return true;
				}
				else{
					return false;
				}
			}
		}
		
		return true;
}
</script>
<form name=form1 method=post>
 	
	<input type="hidden" name="id_renglones_oc" value="<?=$id_renglones_oc?>">
 
<?
           variables_form_busqueda("atar_factura");

            $orden = array(
            		"default" => "1",
                    "default_up"=>"0",
            		"1" => "nro_factura",
            		"2" => "cliente",
                    "3" => "direccion",
                    "4" => "fecha_factura"
            	);

            $filtro = array(
            		"nro_factura" => "Número de Factura"
            	);

    $sql="select id_factura, nro_factura, cliente, direccion, fecha_factura 
    		from facturacion.facturas";

?>
    <table width=100% align=center class=bordes>
       <tr>
          <td colspan=5 id=mo>Elija la Factura que desea Atar</td>
          </td>
       </tr>
      <tr>
        <td colspan=6 align=center>
            <?
            list($sql_temp,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
            $result = sql($sql_temp,"error en busqueda") or fin_pagina();
            echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
            ?>
         </td>
      </tr>
      <tr id=ma>
       <td colspan=3 align=left>Cantidad: <?=$total?> </td>
       <td colspan=3 align=right><?=$link_pagina?></td>
       </tr>

       <tr id=mo>
          <td width=1%>&nbsp;</td>
          <td><a id=mo href='<?=encode_link("atar_factura.php",array("sort"=>"1","up"=>$up))?>'>Número de Factura</a></td>
          <td><a id=mo href='<?=encode_link("atar_factura.php",array("sort"=>"2","up"=>$up))?>'>Cliente</a></td>
          <td><a id=mo href='<?=encode_link("atar_factura.php",array("sort"=>"3","up"=>$up))?>'>Dirección</a></td>
          <td><a id=mo href='<?=encode_link("atar_factura.php",array("sort"=>"4","up"=>$up))?>'>Fecha de la Factura</a></td>
       </tr>
       <?
       $cantidad=$result->recordcount();
       $cont_hidden_confirm=0;       
       for($i=0;$i<$cantidad;$i++){
	       $id_tabla="tabla_factura_".$result->fields["id_factura"];
	
	       $onclick_check=" javascript:(this.checked)?Mostrar('$id_tabla'):Ocultar('$id_tabla')";
	
	       ?>
	       <tr <?=atrib_tr()?> >
	          <td>
	           <input type=checkbox name=check_id_factura value="<?=$result->fields["id_factura"]?>" onclick="<?=$onclick_check?>" class="estilos_check">
	          </td>
	          <td><?=$result->fields["nro_factura"]?></td>
	          <td><?=$result->fields["cliente"]?></td>
	          <td><?=$result->fields["direccion"]?></td>
	          <td><?=Fecha($result->fields["fecha_factura"]);?></td>
	        </tr>
	
	        <tr>
	          <td colspan=6>
	
	                  <?
	                  $sql=" select facturacion.items_factura.*, id_licitacion, nombre
								from facturacion.items_factura 
								left join licitaciones.renglones_oc using (id_renglones_oc)
								left join licitaciones.subido_lic_oc using (id_subir)
								left join licitaciones.licitacion using (id_licitacion)
							    left join licitaciones.entidad using (id_entidad) 
								where id_factura=". $result->fields["id_factura"]." order by id_item";
	                  $result_items=sql($sql) or fin_pagina();
	                  ?>
	                  <div id=<?=$id_tabla?> style='display:none'>
	                  <table width=80% align=center class=bordes>
	                           <tr id=ma>
	                               <td width=1%>&nbsp;</td>
	                               <td>Cantidad de Productos</td>
	                               <td>Descripción</td>
	                            </tr>
	                            <?
	                            $cantidad_items=$result_items->recordcount();
	                            for ($y=0;$y<$cantidad_items;$y++){?>
		                            <tr <?=atrib_tr()?>>
		                            	 <input type="hidden" value="<?=$result_items->fields['nombre']?>" name=id_hidden_nombre_<?=$cont_hidden_confirm?>>
		                                 <input type="hidden" value="<?=$result_items->fields['id_licitacion']?>" name=id_hidden_lic_<?=$cont_hidden_confirm?>>
		                            	 <input type="hidden" value="<?=($result_items->fields['id_renglones_oc']=='')?0:1?>" name=id_hidden_<?=$cont_hidden_confirm?>>
		                                 <td><input type="radio"  name=id_items value=<?=$result_items->fields["id_item"]?> class="estilos_check"></td>
		                                 <td><?=$result_items->fields["cant_productos"]?></td>
		                                 <td><?=$result_items->fields["descripcion"]?></td>
		                            </tr>
	                            <?
	                            $cont_hidden_confirm++;
	                            $result_items->movenext();
	                            }?>
	               </table>
	               </div>
	
	         </td>
	      </tr> 
	      <?$result->movenext();
       }?>
       <tr>
          <td colspan=6 align=center>
            <input type=submit name='atar_factura' value='Atar Factura' onclick="return confirmaAtado()">
            &nbsp;
            <input type=button name=cerrar value="Cerrar" onclick="window.opener.location.reload(); window.close();">
          </td>
       </tr>
    </table>
</form>
<?fin_pagina();?>