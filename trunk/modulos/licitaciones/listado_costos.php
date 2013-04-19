<?
/*
Author: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.4 $
$Date: 2007/01/17 18:50:46 $
*/

require_once("../../config.php");
variables_form_busqueda("listado_costos");


if ($_POST["pasar_a_historial"]){
	
	$db->starttrans();
	$cantidad = $_POST["cantidad_costos"];
    for ($i=0;$i<$cantidad;$i++){
		
		if ($_POST["check_$i"]){
			$sql = " update costo_real set cerrar_2 = 1 where id_costo_real = ".$_POST["check_$i"];
			sql($sql) or fin_pagina();
		}//del if
	}//del for
	$db->completetrans();
}//del if

if ($_POST["pasar_a_pendientes"]){
	$db->starttrans();
	$cantidad = $_POST["cantidad_costos"];
    for ($i=0;$i<$cantidad;$i++){
		if ($_POST["check_$i"]){
			$sql = " update costo_real set cerrar_2 = 0 where id_costo_real = ".$_POST["check_$i"];
			sql($sql) or fin_pagina();
		}//del if
	}//del for
	$db->completetrans();	
}


echo $html_header;
if (!$cmd)
        $cmd="pendientes";
          


$datos_barra = array (
                   array ("descripcion"=>"Pendientes","cmd"=>"pendientes"),
                   array ("descripcion"=>"Finalizados","cmd"=>"finalizados")
               );


generar_barra_nav($datos_barra);


$orden = array(
        "default" => "1",
        "1" => "licitacion.id_licitacion",
        "2" => "nombre_entidad",
        "3" => "costo_presunto",
        "4" => "costo_real",
        "5" => "monto_factura"
        
       
        );
$filtro = array(
        "id_licitacion" => "ID",
        "entidad.nombre" => "Entidad",
        );

$sql = " select costo_real.*,licitacion.id_licitacion,entidad.nombre as nombre_entidad,estado.color
            from licitaciones.costo_real 
            join licitaciones.entrega_estimada using (id_entrega_estimada)
            join licitaciones.licitacion using (id_licitacion)
            join licitaciones.estado using (id_estado)
            join licitaciones.entidad using (id_entidad)";

($cmd == "pendientes") ? $condicion = " cerrar_2 = 0": $condicion = " cerrar_2 = 1";
$where = " $condicion ";

?>
<script>
 function control_datos(){
 var cantidad,chk,cerrar,retorno;

 retorno = true;
 cantidad = parseInt(document.form1.cantidad_costos.value);
  
 if ( cantidad == 1) {
  	if (document.form1.check_0.checked && document.form1.cerrar_1_0.value == 0) {
 	    alert("Para pasar a historial debe tener el cerrar 1 ");
 	    retorno = false;
 	}
 } else {
 		 for(i=0;i<cantidad;i++){
 		 	chk = eval ("document.form1.check_"+i);
 		 	cerrar = eval ("document.form1.cerrar_1_"+i);
 		 	
 		 	if (chk.checked && cerrar.value == 0){
 	            retorno = false ;
 		 	}
 		 	
   	     }//del for
   	
 } //del else
 	
 	
   	if ( retorno == false ) {
   		alert("Para pasar a historial debe tener el cerrar 1 ");
   		
   	} 
   	     
    return retorno;
 }//del control datos
</script>
<form name="form1" method="POST" action="listado_costos.php">
  <table class="bordes" width="100%" align="center">
    <tr>
      <td align="center">
      <?
      list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
      $res = sql($sql) or fin_pagina();
      ?>
      <input type=submit name=buscar value='Buscar'>
      </td>
    </tr>
    <tr id=ma>
      <td>
        <table width="100%">
          <tr id=ma>
            <td width="50%" align="left">Cantidad :<?=$total?></td>
             <td width="50%" align="right"><?=$link_pagina?></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
     <td width="100%" align="center">
          <table width="100%" align="center">
                 <tr id="mo">
			        <td width="1">&nbsp;</td>
			        <td><a id=mo href='<?=encode_link("listado_costos.php",array("sort"=>"1","up"=>$up))?>'>ID </a></td>
			        <td><a id=mo href='<?=encode_link("listado_costos.php",array("sort"=>"2","up"=>$up))?>'>Entidad</a></td>
			        <?if ($cmd == "pendientes"){?>
			        <td>Cerrar 1</td>
			        <?}?>
  			        <td><a id=mo href='<?=encode_link("listado_costos.php",array("sort"=>"3","up"=>$up))?>'>Costo Presunto</a></td>			        
			        <td><a id=mo href='<?=encode_link("listado_costos.php",array("sort"=>"4","up"=>$up))?>'>Costo Real</a></td>
			        <td><a id=mo href='<?=encode_link("listado_costos.php",array("sort"=>"5","up"=>$up))?>'>Monto Factura</a></td>  			        
			        			        			        
			    </tr>
			    <input type="hidden" name="cantidad_costos" value="<?=$res->recordcount()?>">
			    <?
			     for($i = 0;$i<$res->recordcount();$i++) {
			       	$link = encode_link("costo_real.php",array("id_costo_real"=>$res->fields["id_costo_real"],"id_entrega_estimada"=>$res->fields["id_entrega_estimada"]));
			     	$onclick = " onclick = 'window.open(\"$link\")'";
                ?>
			        <tr <?=atrib_tr()?>>
			        <td width="1"> <input class="estilos_check" type="checkbox" name="check_<?=$i?>" value="<?=$res->fields["id_costo_real"]?>"> </td>
			        <td align="center" <?=$onclick?> bgcolor="<?=$res->fields["color"]?>">
			        <?=$res->fields["id_licitacion"]?>
			        </td>
			        <td align="left"   <?=$onclick?>><?=$res->fields["nombre_entidad"]?> </td>
			        <?if ($cmd=="pendientes"){?>
			        <td align="center">
			        <input type="hidden" name="cerrar_1_<?=$i?>" value="<?=($res->fields["cerrar_1"])?"1":"0"?>">
			        <font color="<?=($res->fields["cerrar_1"])?"Red":"Green";?>">
			        <?=($res->fields["cerrar_1"])?"Si":"No";?>
			        </font>
			        </td>
			        <?}?>
			        <td align="right"  <?=$onclick?>><?=formato_money($res->fields["costo_presunto"]*$res->fields["dolar_presunto"])?></td>			        
			        <td align="right"  <?=$onclick?>><?=formato_money($res->fields["costo_real"]*$res->fields["dolar_real"])?>       </td>
			        <td align="right"  <?=$onclick?>><?=formato_money($res->fields["monto_factura"])?> </td>
			        </tr>
			     <?
			        $res->movenext();
			     } //del for
			     ?>
        </table>       
     </td>
    </tr>
    <tr>
      <td align="center">
        <?
        if ($cmd == "pendientes") {
        ?>
        <input type="submit" name="pasar_a_historial" value="Pasar a Historial" title="Las que tiene cerrar 1 les pone cerrar 2" onclick="return control_datos();"> 
        <?
        }else {
        ?>
        <input type="submit" name="pasar_a_pendientes" value="Pasar a Pendientes" title="Pasa los costos a pendientes"> 
        <?
        }
        ?>
      </td>
    </tr>
  </table>
</form>

<?
echo fin_pagina();
?>