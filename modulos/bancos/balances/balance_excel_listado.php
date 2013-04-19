<?php
/*
$Author: fernando $
$Revision: 1.10 $
$Date: 2007/02/15 21:17:44 $
*/

require_once ("../../../config.php");
require_once ("funciones_balance.php");




function cantidad_de_cuentas_en($cuentas,$cuentas_mayor){
$cantidad = 0;
	for ($i = 0;$i<sizeof($cuentas); $i++){
	  if (in_array($cuentas[$i]["nombre"],$cuentas_mayor)) $cantidad++;
	}
	return $cantidad;
}//de la function

function retornar_campo($cuentas,$value){
	for ($i=0;$i<sizeof($cuentas);$i++){
		if ($cuentas[$i]["nombre"] == $value) {
			if ($cuentas[$i]["pesos"]) $campos = ",".$cuentas[$i]["pesos"];
			if ($cuentas[$i]["dolar"]) $campos .= ",".$cuentas[$i]["dolar"];
		} //del if
	} //del for
	return $campos;
}

$fecha_desde=$_POST["fecha_desde"] or $fecha_desde=$parametros["fecha_desde"];
$fecha_hasta=$_POST["fecha_hasta"] or $fecha_hasta=$parametros["fecha_hasta"];

$hora_desde=$_POST["hora_desde"] or $hora_desde=$parametros["hora_desde"];
$hora_hasta=$_POST["hora_hasta"] or $hora_hasta=$parametros["hora_hasta"];

$solo_esta_hora = $_POST["solo_esta_hora"];
$tipo_hora      = $_POST["tipo_hora"];

if ($tipo_hora == "rangos")         $checked_rangos         = "checked";
if ($tipo_hora == "solo_esta_hora") $checked_solo_esta_hora = "checked";
if (!$tipo_hora)                    $checked_rangos         = "checked";


if (!$fecha_desde) $fecha_desde=date("d/m/Y");
if (!$fecha_hasta) $fecha_hasta=date("d/m/Y");

if (!$hora_desde) $hora_desde="09:00:00";
if (!$hora_hasta) $hora_hasta="19:00:00";
if (!$solo_esta_hora) $solo_esta_hora="19:00:00";


$cuentas = array_merge($cuentas_activo,$cuentas_pasivo);

if ($_POST["buscar"] || $parametros["viene_de_historial"] || $_POST["excel"]){

     
	$fecha_desde_aux = Fecha_db($fecha_desde);
	$fecha_hasta_aux = Fecha_db($fecha_hasta);
    $hora_desde_aux  = $hora_desde;
	$hora_hasta_aux  = $hora_hasta;		  
	$solo_esta_hora_aux = $solo_esta_hora;

	
	
	if ($_POST["tipo_hora"]=="rangos" || $parametros["viene_de_historial"]){
		  $condicion = "(fecha >= '$fecha_desde_aux $hora_desde_aux'  and fecha <='$fecha_hasta_aux $hora_hasta_aux')";
	    }elseif ($_POST["solo_esta_hora"]){
          $condicion = "(fecha >= '$fecha_desde_aux $solo_esta_hora_aux'  and fecha <='$fecha_hasta_aux $solo_esta_hora_aux') and fecha ilike '%$solo_esta_hora_aux'";	    	      	
	    }
	
	$orden=$_POST["orden"];

	//recupero los que quiero ver
	$viene_por_post = 0;
	$campos = " fecha,valor_dolar";
	$i = 0;
	//armo la consulta con los parametros que quiero mostrar
	//y en $cuentas_a_mostrar voy almacenando los datos
	while ($clave_valor=each($_POST)) {
		if (is_int(strpos($clave_valor[0],"cuentas_"))) {
			$viene_por_post = 1;
			$campos .= retornar_campo($cuentas,$clave_valor["value"]);
			$cuentas_a_mostrar[]=$clave_valor["value"];
		}
		$i++;
	} //del while
     
	//si no viene por post recupero todas las cuentas
	if (!$viene_por_post) {
		$campos = "*";
		for($i = 0 ;$i<sizeof($cuentas);$i++){
			$cuentas_a_mostrar[] = $cuentas[$i]["nombre"];
		}
	} //del if

	$sql=" select $campos from balance_historial where $condicion
             order by fecha $orden";       
	$res=sql($sql) or fin_pagina();
	$link=encode_link("balance_excel.php",array("sql"=>$sql,"cuentas_a_mostrar"=>$cuentas_a_mostrar));
	// if ($parametros["viene_de_historial"] || $_POST["excel"] ){
	if ( $_POST["excel"] ){
     ?>
     <script>
     window.open('<?=$link?>');
     </script>
     <?
	} //del post[excel]
} //del if principal


if (!$_POST["orden"]) $checked_asc = " checked";
if ($_POST["orden"] == "ASC") $checked_asc  = " checked";
if ($_POST["orden"] == "DESC") $checked_desc = " checked";

if ($parametros["viene_de_balance"]){
	//$fecha_desde = substr_replace($fecha_desde,"-","/");
	//$fecha_hasta = substr_replace($fecha_hasta,"-","/");
}

echo $html_header;
cargar_calendario();
?>
<form name=form1 method=post action=balance_excel_listado.php>
<input type=hidden name=excel value=0>
   <table width=100% align=center class=bordes>
    <tr id=mo><td>Excel del Balance</td></tr>
    <tr id=ma><td align=center>Opciones</td></tr>
    <tr>
      <td>
         <table width=50% align=center bgcolor="<?=$bgcolor2?>">
            <tr> 
                 <td>&nbsp;</td> 
                 <td><b>Desde:</b></td>
                 <td><input type=text name=fecha_desde value="<?=$fecha_desde?>" size=10><?=link_calendario("fecha_desde");?> </td>
                 <td><b>Hasta:</b></td>
                 <td><input type=text name=fecha_hasta value="<?=$fecha_hasta?>" size=10><?=link_calendario("fecha_hasta");?>  </td>
                 <td><b>Ascendente</b></td>
                 <td><input type=radio name=orden value="ASC" <?=$checked_asc?>></td>
                 <td rowspan=2 valign=middle><input type=submit value="Buscar" name=buscar></td>
                 <td rowspan=2 valign=middle><input type=button value="Cerrar" name=Cerrar onclick="window.close()"></td>
                 <td rowspan=2 valign=middle><img src="../../../imagenes/excel.gif" style='cursor:hand;'  onclick='document.form1.excel.value=1;document.form1.submit();'></td>
            </tr>
            <tr>
                <td><input type="radio" name="tipo_hora" value="rangos"  <?=$checked_rangos?>></td>
                <td><b>Desde:</b></td>
                <td>
                  <select name=hora_desde>
                  <?
                  for($i=0;$i<=23;$i++) {
                  	($i<=9)?$y="0$i":$y=$i;
                  	("$y:00:00"==$hora_desde)?$selected="selected":$selected="";
                  ?>
                     <option <?=$selected?> value="<?="$y:00:00"?>">
                     <?="$i:00"?>
                     </option>
                  <?}?>
                  </select>
                 </td>
                <td><b>Hasta:</b></td>
                <td>
                  <select name=hora_hasta>
                  <?
                  for($i=0;$i<=23;$i++){
                  	($i<=9)?$y="0$i":$y=$i;
                  	("$y:00:00"==$hora_hasta)?$selected="selected":$selected="";
                  ?>
                     <option <?=$selected?> value="<?="$y:00:00"?>">
                     <?="$i:00"?>
                     </option>
                  <? } ?>                  
                  </select>
                </td>
                 <td><b>Descendente</b></td>
                 <td><input type=radio name=orden value="DESC" <?=$checked_desc?>></td>
                 <td>&nbsp;</td>
            </tr>
            <tr>
             <td><input type="radio"  name="tipo_hora" value="solo_esta_hora" <?=$checked_solo_esta_hora?>></td>            
             <td colspan="3" align="left">
                <b>Solo Esta Hora</b>
             </td>
                <td>
                  <select name=solo_esta_hora>
                  <?
                  for($i=0;$i<=23;$i++){
                  	($i<=9)?$y="0$i":$y=$i;
                  	("$y:00:00"==$solo_esta_hora)?$selected="selected":$selected="";
                  ?>
                     <option <?=$selected?> value="<?="$y:00:00"?>">
                     <?="$i:00"?>
                     </option>
                  <? } ?>                  
                  </select>
                </td>
             
            </tr>
            
         </table>
      </td>
    </tr>
    </table>
    <br>
    <table width="100%" align="center" cellpadding="0" cellspacing="0">
       <tr id=mo>
       <td width="95%">Filtros</td>
       <td width="5%" align="center"><input type="checkbox" onclick="javascript:(this.checked)?Mostrar('filtros'):Ocultar('filtros')"></td>
       </tr>
    </table>
    <div id=filtros style='display:visible'>
    <table width="100%" class="bordes" bgcolor="<?=$bgcolor2?>">
      <tr id=ma><td>Activos</td></tr>
      <tr>
        <td width="100%" valign="top">
          <table width="100%">
           <tr>
             <?
             for($i = 0; $i<sizeof($cuentas_activo);$i++){

             	if ($parametros["viene_de_historial"])
             	$checked = " checked";
             	else{
             		if ($_POST["cuentas_activo_$i"])
             		$checked = "checked";
             		else
             		$checked = "";
             	}
             ?>
               <td width="10" valign="top">
                 <input type="checkbox" <?=$checked?> value="<?=$cuentas_activo[$i]["nombre"]?>" name="cuentas_activo_<?=$i?>">
               </td>
               <td width="10%" valign="top">
                 <b><?=$cuentas_activo[$i]["nombre"]?></b>
               </td>
             <?
             }//del for
             ?>
             </tr>
          </table>
        </td>
      </tr>
      <tr id=ma><td>Pasivos</td></tr>      
      <tr>
        <td width="100%" valign="top">
          <table width="100%">
           <tr>
             <?
             for($i = 0; $i <sizeof($cuentas_pasivo);$i++) {

             	if ($parametros["viene_de_historial"])
             	$checked = " checked";
             	else{
             		if ($_POST["cuentas_pasivo_$i"])
             		$checked = "checked";
             		else
             		$checked = "";
             	}

             ?>
               <td width="10" valign="top">
                 <input type="checkbox" <?=$checked?> value="<?=$cuentas_pasivo[$i]["nombre"]?>" name="cuentas_pasivo_<?=$i?>">
               </td>
               <td width="30%" align="left" valign="top">
                 <b><?=$cuentas_pasivo[$i]["nombre"]?></b>
               </td>
             <?
             }//del for
             ?>
             </tr>
          </table>
        </td>
      </tr>      
    </table>
    </div>
    <br>
    <table width=100% class=bordes>
    <tr id=mo><td>Activos</td></tr>
    <tr><td>
        <?
        //$cantidad_columnas=sizeof($cuentas_activo);
        //print_r($cuentas_a_mostrar);
        $cantidad_columnas = cantidad_de_cuentas_en($cuentas_activo,$cuentas_a_mostrar);
        $width=floor(100/($cantidad_columnas+1));
        ?>
        <table  align=center width=100%>
           <tr id=ma>
              <td width="50"> Fecha</td>
              <td width="50"> Dolar</td>
              <?
              //genero las columnas
              for($i=0;$i<sizeof($cuentas_activo);$i++) {
              	if (in_array($cuentas_activo[$i]["nombre"],$cuentas_a_mostrar)){ 
              ?>
                <td align=center width="<?=$width?>%" >
                <?=$cuentas_activo[$i]["nombre"];?>
                </td>
              <?
              	}//del if
              }//del for
              ?>
           </tr>
              <?
              for($i=0;$i<$res->recordcount();$i++){
              	$datos=$res->FetchRow();
              	//print_r($datos);
              	$fecha=fecha($datos["fecha"]);
              	$hora=substr($datos["fecha"],10);
                  ?>
                  <tr <?=atrib_tr()?>>
                      <td align=center><?=$fecha." ".$hora?></td>
                      <td align=right><?= formato_money($res->fields["valor_dolar"])?> </td>
                      <?
                      //muesto los datos a excepcion de la fecha
                      for($y=0;$y<count($cuentas_activo);$y++){
                       if ($datos[$cuentas_activo[$y]["pesos"]] || $datos[$cuentas_activo[$y]["dolar"]]){
                      ?>    
                      <td align=right><?=formato_money($datos[$cuentas_activo[$y]["pesos"]] + ($datos[$cuentas_activo[$y]["dolar"]] * $res->fields["valor_dolar"]));?></td>
                      <?    
                      	}//del if
                      } //del for
                      ?>
                 </tr>
                  <?
              }//del for
              ?>                                          
    </table>    
    <!-- PASIVOS -->            
    <table width=100% >
    <tr id=mo><td>Pasivos</td></tr>
    <tr><td width=100%>
        <?
        //$cantidad_columnas=sizeof($cuentas_pasivo);
        $cantidad_columnas = cantidad_de_cuentas_en($cuentas_pasivo,$cuentas_a_mostrar);
        $width=floor(100/($cantidad_columnas+1));
        ?>
        <table  align=center width=100%>
           <tr id=ma>
              <td width="50"> Fecha</td>
              <td width="50"> Dolar</td>
              <?
              //genero las columnas
              for($i=0;$i<sizeof($cuentas_pasivo);$i++) {
              	if (in_array($cuentas_pasivo[$i]["nombre"],$cuentas_a_mostrar)){ 
              ?>
                <td align=center width="<?=$width?>%" >
                <?=$cuentas_pasivo[$i]["nombre"];?>
                </td>
              <?
              	}//del if
              }//del for
              ?>
          </tr>
              <?
              $res->move(0);              
              for($i=0;$i<$res->recordcount();$i++){
              	$datos=$res->FetchRow();
              	//print_r($datos);
              	$fecha=fecha($datos["fecha"]);
              	$hora=substr($datos["fecha"],10);
                  ?>
                  <tr <?=atrib_tr()?>>
                      <td align=center><?=$fecha." ".$hora?></td>
                      <td align=right><?= formato_money($res->fields["valor_dolar"])?> </td>
                      <?
                      //muesto los datos a excepcion de la fecha
                      for($y=0;$y<count($cuentas_pasivo);$y++){
                       if ($datos[$cuentas_pasivo[$y]["pesos"]] || $datos[$cuentas_pasivo[$y]["dolar"]]){
                      ?>    
                      <td align=right><?=formato_money($datos[$cuentas_pasivo[$y]["pesos"]] + ($datos[$cuentas_pasivo[$y]["dolar"]] * $res->fields["valor_dolar"]));?></td>
                      <?    
                      	}
                      } //del for
                      ?>
                 </tr>
                  <?
              }//del for
              ?>                                                        
              
        </table>
       
<?echo  fin_pagina();?>              