<?php
/*
$Author: ferni $
$Revision: 1.1 $
$Date: 2007/03/02 20:00:22 $
*/

require_once("../../config.php");


if ($_POST["asignar_oc"]){
	if ($_POST['id_items']){
	?>
	<script>
		window.opener.document.all.oc.value=<?=$_POST['id_items']?>;		
		window.close();
	</script>
	<?}
	else{?>
		<br>
		<div align="center">
			<font color="Red" size="3"><b>Debe Seleccionar un Items</b></font>
		</div>
		<br>	
	<?}
}//del if ($_POST["atar_factura"])

echo $html_header;
?>
<script>
</script>
<form name=form1 method=post>
<?
           variables_form_busqueda("atar_factura");

            $orden = array(
            		"default" => "1",
                    "default_up"=>"0",
            		"1" => "id_licitacion",
            		"2" => "entidad.nombre",
                    "3" => "nro_lic_codificado",
                    "4" => "fecha_apertura"
            	);

            $filtro = array(
            		"id_licitacion" => "ID"
            	);

    $sql="select id_licitacion, entidad.nombre, nro_lic_codificado, fecha_apertura
    		from licitaciones.licitacion
    		left join licitaciones.entidad using(id_entidad)";
    $where=" id_estado=1 or id_estado=7";

?>
    <table width=100% align=center class=bordes>
       <tr>
          <td colspan=5 id=mo>Elija la Orden de Compra que desea Asignar a la Garantia</td>
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
      <tr>
          <td colspan=6 align=center>
            <input type=submit name='asignar_oc' value='Asignar Orden de Compra' >
            &nbsp;
            <input type=button name=cerrar value="Cerrar" onclick="window.close();">
          </td>
       </tr>
      <tr id=ma>
       <td colspan=3 align=left>Cantidad: <?=$total?> </td>
       <td colspan=3 align=right><?=$link_pagina?></td>
       </tr>

       <tr id=mo>
          <td width=1%>&nbsp;</td>
          <td><a id=mo href='<?=encode_link("garantia_sel_ord_compra.php",array("sort"=>"1","up"=>$up))?>'>ID</a></td>
          <td><a id=mo href='<?=encode_link("garantia_sel_ord_compra.php",array("sort"=>"2","up"=>$up))?>'>Entidad</a></td>
          <td><a id=mo href='<?=encode_link("garantia_sel_ord_compra.php",array("sort"=>"3","up"=>$up))?>'>Nro. Lic. Codif.</a></td>
          <td><a id=mo href='<?=encode_link("garantia_sel_ord_compra.php",array("sort"=>"4","up"=>$up))?>'>Fecha de Apertura</a></td>
       </tr>
       <?       	
       $cantidad=$result->recordcount();
       $cont_hidden_confirm=0;       
       for($i=0;$i<$cantidad;$i++){
	       $id_tabla="tabla_factura_".$result->fields["id_licitacion"];
	
	       $onclick_check=" javascript:(this.checked)?Mostrar('$id_tabla'):Ocultar('$id_tabla')";
	
	       ?>
	       <tr <?=atrib_tr()?> >
	          <td>
	           <input type=checkbox name=check_id_factura value="<?=$result->fields["id_licitacion"]?>" onclick="<?=$onclick_check?>" class="estilos_check">
	          </td>
	          <td><?=$result->fields["id_licitacion"]?></td>
	          <td><?=$result->fields["nombre"]?></td>
	          <td><?=$result->fields["nro_lic_codificado"]?></td>
	          <td><?=Fecha($result->fields["fecha_apertura"]);?></td>
	        </tr>
	
	        <tr>
	          <td colspan=6>
	
	                  <?
	                  $id_licitacion=$result->fields['id_licitacion'];
	                  $sql="select id_subir,nro_orden, lugar_entrega from licitaciones.subido_lic_oc	                  		
	                  		where id_licitacion = $id_licitacion order by nro_orden";
	                  $result_items=sql($sql) or fin_pagina();
	                  ?>
	                  <div id=<?=$id_tabla?> style='display:none'>
	                  <table width=80% align=center class=bordes>
	                           <tr id=ma>
	                               <td width=1%>&nbsp;</td>
	                               <td>Nro Orden</td>	                               
	                               <td>Lugar Entrega</td>
	                            </tr>
	                            <?
	                            $cantidad_items=$result_items->recordcount();
	                            for ($y=0;$y<$cantidad_items;$y++){	                            	
	                            	?>
		                            <tr <?=atrib_tr()?>>        
		                                 <td><input type="radio"  name=id_items value=<?=$result_items->fields["id_subir"]?> class="estilos_check"></td>
		                                 <td><?=$result_items->fields["nro_orden"]?></td>		                                 
		                                 <td><?=$result_items->fields["lugar_entrega"]?></td>
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
    </table>
</form>
<?fin_pagina();?>