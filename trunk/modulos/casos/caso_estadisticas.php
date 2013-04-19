<?php
/*
$Author: fernando $
$Revision: 1.20 $
$Date: 2006/08/07 19:22:31 $
*/
require_once("../../config.php");

$año     = $_POST["año"]    or $año    = $parametros["año"];
$falla   = $_POST["falla"]  or $falla  = $parametros["falla"];
$o_falla = $_POST["o_falla"]or $o_falla= $parametros["o_falla"];
$estado  = $_POST["estado"] or $estado = $parametros["estado"];
$id_entidad    = $_POST["id_entidad"]     or  $id_entidad     = $parametros["id_entidad"]; 
$nombre_cliente= $_POST["nombre_cliente"] or  $nombre_cliente = $parametros["nombre_cliente"];
$elegir_cliente= $_POST["elegir_cliente"] or  $elegir_cliente = $parametros["elegir_cliente"];
$itemspp = 10000;

if ($_POST["id_licitacion"]) $id_licitacion=$_POST["id_licitacion"];

variables_form_busqueda("caso_estadisticas");

$sort = $_POST["sort"] or $sort= $parametros["sort"];
$up   = $_POST["up"]   or $up  = $parametros["up"];

$orden = array(
   "default" => "2",
   "default_up" =>"0",
   "1" => "fallas.desc_falla",
   "2" => "cant_falla",
);


$filtro = array(
"cas_ate.nombre"        => "Atendido por",
"casos_cdr.nrocaso"     => "Número de caso",
"casos_cdr.deperfecto"  => "Desperfecto",
"casos_cdr.nserie"      =>"Nro de Serie"
);


$campos="count(casos_cdr.idcaso) as cant_falla, fallas.desc_falla, fallas.id_falla ";

$sql="select $campos
      from casos.casos_cdr
      left join casos.fallas using(id_falla)
      join casos.dependencias using(id_dependencia)
      join licitaciones.entidad le using(id_entidad)
      join casos.cas_ate using(idate)
      left join ordenes.orden_de_produccion on 
					 (
					  (casos.casos_cdr.nserie>=ordenes.orden_de_produccion.nserie_desde)
					   and
					  (casos.casos_cdr.nserie<=ordenes.orden_de_produccion.nserie_hasta)
					 )
       ";

 $group_by="  group by fallas.desc_falla,fallas.id_falla ";
 $where="  (fallas.id_falla<>-1 or fallas.id_falla is null)";
 
 if (!$estado) $estado=1;
 if ($estado!="todos") $where.=" and casos_cdr.idestuser=$estado"; 

 if ($año!=-1 && $año){
        $año_inicio="$año-01-01";
        $año_fin="$año-12-31";
        $where.=" and (fechainicio >='$año_inicio' and fechainicio <='$año_fin') ";
       }
	if ($o_falla!=-1 && $o_falla){
        $where.=" and casos_cdr.id_origen_falla=$o_falla ";
        }
 if ($falla!=-1 && $falla){
        $where.=" and casos_cdr.id_falla=$falla ";
        }
 
 if ($id_entidad){
 	$ids=explode(";", $id_entidad);
 	$where.=" and (";
 	for ($i=0; $i<count($ids); $i++){
		$where.="le.id_entidad = ".$ids[$i];
		if ($i<count($ids)-1) $where.=" or ";
 	}
 	$where.=") ";
 }
 if ($id_licitacion){
 		$where.=" and id_licitacion=$id_licitacion";
 }
	
 if (!$estado) $estado=1;
 if ($estado!="todos") 
	$where.=" and casos_cdr.idestuser=$estado"; 

$where.=$group_by;
echo $html_header;
?>
<script>
var wcliente=0;
//variable que contiene la ventana que termina el remito
var wterminar=0;
function setear_datos() {

	if (document.all.elegir_cliente.checked==false){
	 document.all.cliente.disabled=!document.all.cliente.disabled;
	 document.all.nombre_cliente.value="";
	 document.all.id_entidad.value="";
	 document.all.sin_parametros.value="1";
	 document.form1.submit();
	}
	else{
	    document.all.cliente.disabled=!document.all.cliente.disabled;
	}
}

function cargar_cliente(){
 document.all.id_cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
 if (wcliente.document.all.chk_direccion.checked)
        document.all.entrega.value=wcliente.document.all.direccion.value;
}
</script>
<form name=form1 action="" method="POST">
<input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
<input type=hidden name="sin_parametros" value="0">
<table width=100% alig=center>
 <tr id=mo>
    <td width=100%>Estadisticas de los C.A.S. </td>
 </tr>
 <tr>
   <td>
      <table width=100% align=left border=0 bgcolor=<?=$bgcolor3?>>
        <tr>
	        <td align="center">
          <?
            list($query,$total,$link_pagina,$up) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar);
  	        $estadisticas=sql($query) or fin_pagina();
          ?>
          </td>
         </tr>
         <tr>
 		  <td align="center">
		  <b>Estados:</b> 
		  <select name="estado">
			  <option value="1" <? if ($estado==1) echo "selected"; ?>>En Curso</option>
			  <option value="7" <? if ($estado==7) echo "selected"; ?>>Pendientes</option>
	   		  <option value="2" <? if ($estado==2) echo "selected"; ?>>Finalizados</option>
			  <option value="todos" <? if ($estado=="todos") echo "selected"; ?>>Todos</option>
	       </select>
					
		  <b>Falla</b>
          <?
           $sql="select * from fallas order by desc_falla";
           $resultado=sql($sql) or fin_pagina();
           $cantidad_fallas=$resultado->recordcount();
          ?>
          <select name=falla onKeypress="buscar_op(this);"onblur="borrar_buffer();"onclick="borrar_buffer();">
            <option selected value=-1>Todas<option>
                <?
                for ($i=0;$i<$cantidad_fallas;$i++){
                 $id_falla=$resultado->fields["id_falla"];
                 $desc_falla=$resultado->fields["desc_falla"];
                 ($id_falla==$falla)?$selected="selected":$selected="";
                ?>
                <option value="<?=$id_falla?>" <?=$selected?>><?=$desc_falla?></option>
                <?
                $resultado->movenext();
                }
                ?>
         </select>
				<b>Origen Falla</b>
		 <?
           $sql="select * from origen_falla order by descripcion";
           $resultado=sql($sql) or fin_pagina();
           $cantidad_fallas=$resultado->recordcount();
          ?>
          <select name=o_falla onKeypress="buscar_op(this);"onblur="borrar_buffer();"onclick="borrar_buffer();">
            <option selected value=-1>Todas<option>
                <?
                for ($i=0;$i<$cantidad_fallas;$i++){
                 $id_o_falla=$resultado->fields["id_origen_falla"];
                 $desc_o_falla=$resultado->fields["descripcion"];
                 ($id_o_falla==$o_falla)?$selected="selected":$selected="";
                ?>
                <option value="<?=$id_o_falla?>" <?=$selected?>><?=$desc_o_falla?></option>
                <?
                $resultado->movenext();
                }
                ?>
         </select>
         <b>Año<b>
         <select name=año onKeypress="buscar_op(this);"onblur="borrar_buffer();"onclick="borrar_buffer();">
         >
              <option value=-1>Todos</option>
              <?
              for($i=2003;$i<2011;$i++){
              ($año==$i)?$selected="selected":$selected="";
              ?>
              <option <?=$selected?>><?=$i?></option>
              <?
              }
              ?>
         </select>
         &nbsp;
         Licitación: <input type="text" name="id_licitacion" value="<?=$id_licitacion?>">
         </td>
         </tr>
      <tr>
       <td colspan=7>
         <table width=100% border=0>
           <tr>
           <?
		    $link=encode_link('../modulo_clientes/nuevo_cliente.php',array('pagina'=>'caso_estadisticas'));
            ($elegir_cliente)?$checked="checked":$checked="";
            ($elegir_cliente)?$disabled="":$disabled="disabled";
           ?>
          <td align=left width="10%" nowrap>
          <input type=checkbox name=elegir_cliente value=1  <?=$checked?> onclick="setear_datos();">
          <b>Elegir Cliente</b>
          </td>
          <td align=center width="10%" nowrap>
          <?
          ?>
          <input type=button name=cliente value="Elegir Clientes" <?=$disabled?> onclick="if(wcliente==0 || wcliente.closed) wcliente=window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=150,top=0,width=600,height=600'); else wcliente.focus()">
          </td>
          <td align=left colspan=4 nowrap><b>Cliente(s):&nbsp;
          <input type=text name="nombre_cliente" value="<?=$nombre_cliente?>" style="width:90%" >
          </td>
        </tr>
        <tr>
         <td align="center" colspan="6">
         <input type=submit name=form_busqueda value='Buscar'>
         </td>
      </tr>
       </table>
      </td>
    </tr>
   </table>
   </td>
 </tr>
 <tr>
   <td>
     <table width=70% align=center border=1 cellspacing=0 cellspading=0 bordercolor=<?=$bgcolor1?>>
      <tr id='ma'>
       <?$link=encode_link("caso_estadisticas.php",array("sort"=>1,
                                                         "up"=>$up,
                                                         "id_entidad"=>$id_entidad,
                                                         "elegir_cliente"=>$elegir_cliente,
                                                         "nombre_cliente"=>$nombre_cliente,
                                                         "falla"=>$falla,
                                                         "estado"=>$estado,
                                                         "año"=>$año))
       ?>
       <a href='<?=$link?>'><td style='cursor:hand'> Falla </td></a>
       <?$link=encode_link("caso_estadisticas.php",array("sort"=>2,
                                                         "up"=>$up,
                                                         "id_entidad"=>$id_entidad,
                                                         "elegir_cliente"=>$elegir_cliente,
                                                         "nombre_cliente"=>$nombre_cliente,
                                                         "falla"=>$falla,
                                                         "estado"=>$estado,
                                                         "año"=>$año))
       ?>

       <a href='<?=$link?>'><td style='cursor:hand'align=center> Cantidad </td> </a>
       </tr>
       <?
       $cant_fallas=$estadisticas->recordcount();
       $campos_casos=" idcaso, ".$campos;
       $group_by_casos=$group_by.",idcaso";
       $query_casos=str_replace($campos,$campos_casos,$query);
       $query_casos=str_replace($group_by,$group_by_casos,$query_casos);
       $nrocasos=array();
       $resultado=sql($query_casos) or fin_pagina();
       for ($i=0;$i<$cant_fallas;$i++){
        $link=encode_link("caso_fallas_asociadas.php",array("id_falla"=>$estadisticas->fields["id_falla"],"consulta"=>"$query_casos"));
       ?>
      <tr <?=$atrib_tr?> onclick="window.open('<?=$link?>')">
         <td align=left style='cursor:hand'>  <b><? if ($estadisticas->fields["desc_falla"]) echo $estadisticas->fields["desc_falla"]; else echo "Otros"; ?></td>
         <td align=center style='cursor:hand'><b><?=$estadisticas->fields["cant_falla"]?></td>
      </tr>
      <?
       $total+=$estadisticas->fields["cant_falla"];
       $estadisticas->movenext();
       }
      ?>
      <tr id=ma_sf>
      <td>Total:</td>
      <td align=center><?=$total;?></td>
     </tr>
     </table>
   </td>
 </tr>
</table>
</form>
<?
fin_pagina();
?>