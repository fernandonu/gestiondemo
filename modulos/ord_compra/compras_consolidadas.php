<?
/*
Autor: Fernando
Creado: jueves 13/05/04

MODIFICADA POR
$Author: fernando $
$Revision: 1.34 $
$Date: 2006/05/18 18:19:26 $
*/
require_once("../../config.php");
require_once("funciones_compras_consolidadas.php");

//esto es porque los checkbox no se envian con el formulario
//cuando no estan checkeados y no se detectan en variables_form_busqueda
//se debe hacer antes de variables_form_busqueda

if ($_POST){
	//las variables de sesion se recuperan en el lib
	$_ses_prod_ofer['chk_fechas']=$_POST['chk_fechas'];
	$_ses_prod_ofer['chk_alt']=$_POST['chk_alt'];
	//para que borre la fecha en caso de una anterior busqueda
	if (!$_POST['chk_fechas'])
		$_ses_prod_ofer['fecha_menor']=$_POST['fecha_menor']="";
        
	$_ses_prod_ofer['chk_tipoprod']=$_POST['chk_tipoprod'];

	if ($_POST['bborrar']){
		unset($_ses_prod_ofer);
		unset($_POST);
	}
	phpss_svars_set("_ses_prod_ofer", $_ses_prod_ofer);
}


//valores por defecto
 $variables=array(
	        "chk_estado"=>1,
	        "select_estado"=>0,
	        "chk_fechas"=>"",
	        "select_fechas"=>"",
	        "fecha_menor"=>"",
	        "fecha_mayor"=>"",
	        "chk_tipoprod"=>"",
	        "select_tipoprod"=>"",
	        "chk_alt"=>""
	        );
 variables_form_busqueda("prod_ofer",$variables);

 
 if (strlen($_POST["select_estado"])==0)
                          $select_estado="7";

  $q= " select sum(rp.cantidad*pp.cantidad) as cantidad,p.id_producto,p.desc_gral
               from licitaciones.licitacion l ";

  if ($select_estado==7) 
            $q.=" join licitaciones.entrega_estimada on (l.id_licitacion=entrega_estimada.id_licitacion)";

  //busqueda con renglones alternativos
    switch ($select_estado){
                case 2:
                    //presuntamenta ganada
                    $filtrar_estado=1;
                    break;
                case 3:
                    //preadjudicada
                    $filtrar_estado=2;
                    break;
                case 7:
                     // orden de compra
                    $filtrar_estado=3;
                    break;
                }//del switch

    if ($filtrar_estado){
          //orden de compra
          if ($filtrar_estado==3){
              $q.="
                 join licitaciones.licitacion_presupuesto_new lp  using (id_entrega_estimada)
                 join licitaciones.renglon_presupuesto_new rp using (id_licitacion_prop)
                 join licitaciones.producto_presupuesto_new pp  using(id_renglon_prop)
                 ";
             }
              else{
                 $q.=" join
                        (select * from renglon join historial_estados using(id_renglon)
                           where historial_estados.id_estado_renglon=$filtrar_estado and activo=1
                         )  rp using (id_licitacion)
                         join licitaciones.producto pp using (id_renglon)
                         ";
              }
        }
        else{
	      if ($chk_alt){
                  $q.="renglon rp using(id_licitacion) join ";
                  $chk_alt=1;
 	         }
	         else {
	              $q.=" join (select * from renglon where codigo_renglon not ilike '%alt%') rp using (id_licitacion)
                        join licitaciones.producto pp using (id_renglon)
                             ";
            }
       }
      $q.="
           join licitaciones.entidad e using(id_entidad)
           join licitaciones.distrito d using(id_distrito)
           join general.productos p using(id_producto)
           ";
	//si esta el chk de estado y el estado no es todos(-1)

    if ($select_estado==7) $condicion_extra=" and entrega_estimada.finalizada=0
                                              and entrega_estimada.flag_compras_consolidadas=1  
                                              and l.id_estado=$select_estado
                                              ";

    $where=" l.es_presupuesto=0 and borrada='f' $condicion_extra";

	if ($chk_estado && ($select_estado==0 ||  $select_estado!="" && $select_estado!=-1)){
		$where.="  AND l.id_estado=$select_estado ";
		$and=" AND ";
	    }
        else
		    unset($select_estado);

    
            
	if ($chk_tipoprod==1 && $select_tipoprod && $select_tipoprod!=-1) {
        if (!$and) $and=" and ";
        
		$where.="$and p.tipo='$select_tipoprod' ";
		$and=" AND ";
	   }
	else
		unset($select_tipoprod);

	if ($chk_fechas==1){
		$where.="$and l.$select_fechas <='".Fecha_db($fecha_mayor)."' ";
		if ($fecha_menor!="")
			$where.="AND l.$select_fechas >='".Fecha_db($fecha_menor)."' ";
	    }
	    else{
		unset($select_fechas);
		unset($fecha_mayor);
		unset($fecha_menor);
	}
	$where.="group by p.id_producto,p.desc_gral";


    $orden_array= array (
		            "default" => "1",
                    "default_up"=>"1",
		            "1" => "desc_gral",
		            "2" => "cantidad"
                    );
    $filtro_array= array (
		           "l.id_licitacion" => "ID licitacion",
		           "l.observaciones"=>"Comentarios",
		           "d.nombre"=>"Distrito",
		           "e.nombre"=>"Entidad",
		           "l.nro_lic_codificado"=>"Numero licitacion",
		           "p.desc_gral"=>"Descripcion Productos"
                   );
//para ver el detalle de las licitaciones
if ($parametros['id_producto']){
	require("lic_prod_ofer_lista_lic.php");
	die;
    }

$sql_dolar=" select dolar_general.valor from dolar_general";
$res_dolar=sql($sql_dolar,"Query que trae el valor dolar") or fin_pagina();
$valor_dolar=$res_dolar->fields["valor"];

echo $html_header;
cargar_calendario();   
?>
<form name="form1" method="post" action="compras_consolidadas.php">
<script language='javascript' src='<?=$html_root.'/lib/popcalendar.js'?>'></script>
    <table width="95%" border="0" cellspacing="2" cellpadding="0" align=center class=bordes bgcolor=<?=$bgcolor2?>>
      <tr>
        <td colspan='4' id=mo>Opciones de Búsquedas</td>
      </tr>
      <tr>
        <td colspan="4">
          <?
          $itemspp=100;
          list($q,$total,$link,$notup)=form_busqueda($q,$orden_array,$filtro_array,$link_tmp,$where,"buscar");

          if ($_POST["bbuscar"]){
            $prod=sql($q) or fin_pagina();
            $busco=1;
          }

          $sql ="select * from tipos_prod order by descripcion";
          $tiposprod=sql($sql,"Query que trae los tipos de productos") or fin_pagina();
         ?>
        </td>
      </tr>
      <tr>
        <td  colspan=4><b>Filtrar por</b></td>
      </tr>
      <tr>
        <td>Estado Licitación</td>
        <td><input name="chk_estado" type="checkbox"  value="1" checked readonly onclick="this.checked=1"></td>
        <td colspan=2>
            <?
            $sql ="select * from estado order by nombre";
            $estados=sql($sql,"Query que trae los estados ") or fin_pagina();
            ?>
            <select name="select_estado">
            <option value="-1" >Todos</option>
            <?
            for ($i=0;$i<$estados->recordcount();$i++){
             $id_estado=$estados->fields["id_estado"];
             $nombre=$estados->fields["nombre"];
             if (strlen($_POST["select_estado"])!=0){
                            if ($id_estado==$_POST["select_estado"])
                                                               $selected="selected";
                                                               else $selected="";
                         }
                         else{
                             if ($id_estado==7) $selected="selected";
                                       else  $selected="";
                         }
            ?>
            <option value="<?=$id_estado?>" <?=$selected?>><?=$nombre?></option>
            <?
            $estados->movenext();
            }
            ?>
           </select>
        </td>
      </tr>
      <tr>
        <td>Tipo de Producto</td>
        <td><input name="chk_tipoprod" type="checkbox" value="1" <? if ($chk_tipoprod==1) echo 'checked'?> ></td>
        <td colspan=2>
           <select name="select_tipoprod" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
            <option value="-1">Todos</option>
            <?= make_options($tiposprod,"codigo","descripcion",$select_tipoprod); ?>
            </select>
        </td>
      </tr>
      <tr>
        <td>Fechas</td>
        <td><input name="chk_fechas" type="checkbox" value="1" <? if ($chk_fechas==1) echo 'checked'?>></td>
        <td colspan=2>
          <select name="select_fechas" id="select_fechas">
             <option value="fecha_entrega" <? if ($select_fechas=='fecha_entrega') echo 'selected' ?>>fecha  de entrega</option>
          </select>
          entre
          <input name="fecha_menor" type="text" id="fecha_menor" size="10" value="<?=$fecha_menor?>">
          <img src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
          seleccionar la fecha'  onClick="javascript:popUpCalendar(fecha_menor, fecha_menor, 'dd/mm/yyyy');">
          y
          <input name="fecha_mayor" type="text" id="fecha_mayor" value="<?=($fecha_mayor)?$fecha_mayor:date('d/m/Y') ?>" size="10">
          <img src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
          seleccionar la fecha'  onClick="javascript:popUpCalendar(fecha_mayor, fecha_mayor, 'dd/mm/yyyy');">
        </td>
      </tr>
      <tr>
        <td>Con alternativas</td>
        <td><input name="chk_alt" type="checkbox" id="chk_alt" value="1" <? if ($chk_alt==1) echo 'checked'?>></td>
        <td colspan=2 align=left>
          <b>Use este filtro para contar también las alternativas en los renglones</b>
        </td>
      </tr>
      <tr>
        <td valign="middle" align=Center colspan=4>
           <input type="submit" name="bbuscar" value="Buscar" style="width:105">&nbsp;
           <input name="bborrar" type="submit" value="Borrar Busqueda" style="width:110">
        </td>
      </tr>
    </table>

<!-- Tabla con los resultados -->
  <br>
  <table width=95% align=center>
     <tr id=ma_sf>
        <td width=40%>&nbsp;</td>
        <td align=right>Total Comprado: U$S</td>
        <td align=right><input type=text name=totales_comprado value="" class="text_3" size=10></td>
        <td align=right>Total a Comprar: U$S</td>
        <td align=right><input type=text name=totales_a_comprar value="" class="text_3" size=10></td>
     </tr>
  </table>
  <br>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="2" class=bordes>
    <tr id=ma_sf>
      <td width=95% >Total: <?  if ($busco) echo $total; else echo 0 ?></td>
      <td align="right" colspan=5><? if ($busco) echo $link?></td>
    </tr>
    <tr id=mo align="center" height=20>
    <a href="<?= encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$notup,"estado"=>$select_estado)) ?>">
        <td width="70%" style="cursor:hand" title="Ordenar por Producto (<?=(!$up)?"ASCENDENTE":"DESCENDENTE" ?>)">Producto</td>
    </a>
    <a href="<?= encode_link($_SERVER['SCRIPT_NAME'] ,array("sort"=>"2","up"=>$notup,"estado"=>$select_estado)) ?>">
         <td width="10%" style="cursor:hand" align="center" title="Ordenar por Cantidad (<?=(!$up)?"ASCENDENTE":"DESCENDENTE" ?>)">Cantidad</td>
    </a>
    <td width="10%">A Comprar</td>
    <td width="10%" title="Montos comprados">Montos C</td>
    <td width="10%" title="Montos a comprar por presupuestos">Montos AC</td>
    </tr>
    <?
    while (!$prod->EOF && $busco){
    $montos_menores=0;
    $montos_multiples=0;
    $montos_simples=0;
    $ref=encode_link("detalle_compras_consolidadas.php",array("id_producto"=>$prod->fields["id_producto"],"estado_licitacion"=>$select_estado,"desc_gral"=>$prod->fields['desc_gral'],"chk_fecha"=>$_POST['chk_fechas'],"fecha_menor"=>$_POST["fecha_menor"],"fecha_mayor"=>$_POST["fecha_mayor"]));
    ?>
    <a href="<?=$ref?>" target="_blank">
    <tr <?=atrib_tr()?>>
    <td align="left">
    <?= $prod->fields['desc_gral'] ?>
    </td>
    <td align="right">
       <?
        $cantidad_producto=$prod->fields['cantidad'];
        echo $cantidad_producto;
       ?>
    </td>
    <td align="right">
      <?
      $datos_lic_productos=array();
      $ordenes=array();
      $datos_ordenes=array();
  
      $cantidad=0;
      $cantidad_comprada=0;
      $cant_aux=0;
      
      $id_producto=$prod->fields["id_producto"];
      if ($select_estado==7) 
                         $flag=1;
                         else
                         $flag=0;
       //traigo la relacion entre productos y licitaciones
      $datos_lic_productos=busqueda_cantidades_simples($id_producto,$flag);
      //traigo los montos de los productos que compre y de los pm      
      if (count($datos_lic_productos)){
               $datos=busca_cantidades_productos($id_producto,$datos_lic_productos);
               $cantidad_oc=$datos["cantidad_oc"];
               $cantidad_pm=$datos["cantidad_pm"];
               $monto_comprado=$datos["monto_comprado"];     
               }
               else{
                  $cantidad_oc=0;
                  $cantidad_pm=0;
                  $monto_comprado=0;     
               }
       
       $cantidad=0;
       
       if ($cantidad_oc == $cantidad_producto)
                           {$cantidad=$cantidad_oc;}
                           elseif ($cantidad_pm == $cantidad_producto)
                                  {$cantidad=$cantidad_pm;}
                                  elseif ($cantidad_oc + $cantidad_pm==$cantidad_producto)
                                     {$cantidad=$cantidad_oc + $cantidad_pm;}
                                     else {$cantidad=$cantidad_oc + $cantidad_pm;}
       
     
       if ($filtrar_estado==3)
                $a_comprar=$cantidad_producto - $cantidad;
                 else
                 $a_comprar=$cantidad_producto;
      echo $a_comprar;
      ?>
    </td>
    <td align="right">

       <table width=100% align=center>
         <tr>
            <td align=center width="5%">$</td>
            <td align=rigth><?=formato_money($monto_comprado);?></td>
         </tr>
       </table>
    </td>
    <td>
      <table width=100% align=center>
         <tr>
            <td align=center width="5%">$</td>
            <td align=rigth><?=formato_money($montos_a_comprar);?></td>
         </tr>
       </table>

    </td>
    </tr>
    </a>
    <?
    $montos_comprados_totales+=$montos;
    $montos_a_comprar_totales+=$montos_a_comprar;
    $prod->MoveNext();
    }//del while
    ?>

  </table>
</form>
<br>
<script>
   document.all.totales_comprado.value=<?=number_format($montos_comprados_totales,"2",".","")?>;
   document.all.totales_a_comprar.value=<?=number_format($montos_a_comprar_totales,"2",".","")?>;
</script>
<?=fin_pagina();?>