<?
/*
$Author: fernando $
$Revision: 1.22 $
$Date: 2007/01/26 14:28:09 $
*/
require_once("../../../config.php");
require_once("funciones_balance.php");
 
$mensaje="";
$hora_obtuvo=-1;
$hora_obtuvo_comparacion=-1;

$comparar=0;

$sacar_foto = $parametros["sacar_foto"] or $sacar_foto = $_POST["sacar_foto"];

if ($_POST["datos"])
            {
            $fecha_desde=$_POST["fecha_desde"];
            $fecha_desde=fecha_db($fecha_desde);
            $hora=$_POST["hora"];
            }
if ($_POST["fecha_comparacion"])
           {
            $fecha_comparacion=$_POST["fecha_comparacion"];
            $fecha_comparacion=fecha_db($fecha_comparacion);
            $comparar=1;
            $hora_comparacion=$_POST["hora_comparacion"];
            $datos_comp=obtener_datos($fecha_comparacion,$hora_comparacion,1);
            }

if ($_POST["excel"] || $parametros["excel"]) {
       $fecha_desde=$_POST["fecha_desde"];
       $hora_desde="09:00:00";
       $hora_hasta="19:00:00";
       $fecha_hasta=$_POST["fecha_comparacion"];  
       $link=encode_link("balance_excel_listado.php",array("viene_de_historial"=>1,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta,"hora_desde"=>$hora_desde,"hora_hasta"=>$hora_hasta));
       $fecha_desde=fecha_db($fecha_desde);
       $fecha_comparacion=fecha_db($_POST["fecha_comparacion"]);
       
       ?>
       <script>
          window.open('<?=$link?>');
       </script>
       <?
       }   //del post de excel

       
if (!$fecha_desde && !$hora){       
        $fecha_desde=date("Y-m-d");
        $hora=date("G:00:00");
        }

if( ($fecha_desde." ".$hora==date("Y-m-d G:00:00")) && $sacar_foto) {
    $foto_del_dia=1;    
    require_once("balance_fotos.php");
    }

$datos=obtener_datos($fecha_desde,$hora) ;

 


echo $html_header;


if (!$sacar_foto) Aviso("Muestra la última foto obtenida");
if (!$hora) $hora=date("G");
if (!$hora_comparacion) $hora_comparacion=date("G"); 
$hora=trim($hora);
$hora_comparacion=trim($hora_comparacion);

 if($mensaje) Aviso ($mensaje);
 cargar_calendario();
?>
<script>
  function control_datos(){
  return true;
  }
</script>
<form name='form1' method='post' action='balance_historial.php'>

<input type='hidden' name='flag_imagen' value=0>
<input type='hidden' name='excel'       value=0>
<input type='hidden' name='sacar_foto' 	value=<?=$sacar_foto?>>

<?$visib="none";?>
<br>
<?
$total_debe_pesos=0;
$total_debe_dolar=0;
$total_haber_pesos=0;
$total_haber_dolar=0;
$hora_aux = date("Y-m-d G:00:00");
?>


<table width=100% align=center class=bordes cellpading=0 cellspacing=0>
   <tr>
       <td id=mo>BALANCE</td>
   </tr>
   <!-- Traer datos junto con el valor dolar -->
   <tr >
       <td bgcolor=<?=$bgcolor2?>>
          <table width=70% align=center border=1 >
             <tr>
                <td align=left><b>Fecha </b></td>
                <td align=center>
                <input type=text name=fecha_desde value="<?=fecha($fecha_desde)?>" size=10>
                 <?
                 echo link_calendario("fecha_desde");
                 ?>
                </td>
                <td><b>Hasta las</b></td>
                <td align="center" <?=$bgcolor_aux?>>
                  <select name=hora>
                  <?
                  for($i=0;$i<=23;$i++){
                    
                    ($i<=9)?$y="0$i":$y=$i;
                   
                    ("$y:00:00"==$hora)?$selected="selected":$selected="";                       
                  ?>
                     <option <?=$selected?> value="<?="$y:00:00"?>">
                     <?="$i:00"?>
                     </option>
                  <?}?>   
                  </select>
                  </td>

                <td align="center" rowspan=2 valign=middle>
                   <input type="submit" name="datos" value='Actualizar' onclick="return control_datos();">
                   <br>
                   
                   <img src="../../../imagenes/excel.gif" style='cursor:hand;'  onclick='document.form1.excel.value=1;document.form1.submit();'>
                </td>
                
             </tr>
             <tr>
                <td align=left>
                 <b>Fecha Comparación</b>
                 <input type=button name=limpiar value=Reset onclick="document.all.fecha_comparacion.value='';">
                </td>
                 <td align=center bgcolor=<?=$bgcolor1?>>
                 <input type=text name=fecha_comparacion value="<?=fecha($fecha_comparacion)?>" size=10>
                 <?echo link_calendario("fecha_comparacion");?>
                </td>
                <td><b>Hasta las</b></td>
                <td align="center" <?=$bgcolor_aux_comparacion?>>
                  <select name=hora_comparacion>
                  <?
                  for($i=0;$i<=23;$i++){
                   ($i<=9)?$y="0$i":$y=$i;
                   echo "$y:00:00 , $hora_comparacion";
                   ("$y:00:00"==$hora_comparacion)?$selected="selected":$selected="";                       
                       
                  ?>
                     <option <?=$selected?> value="<?="$y:00:00"?>">
                     <?="$i:00"?>
                     </option>
                  <?
                  
                  }?>   
                  </select>
                  </td>
             </tr>
           <tr>
                <td align="left" colspan=5 align=left><b>Dolar: <?=formato_money($datos["valor_dolar"])?></b></td>
           </tr>
           <?
           if ($comparar) {
           ?>
           <tr bgcolor=<?=$bgcolor1?>>
                <td align="leftt" colspan=5 align=left><b>Dolar: <?=formato_money($datos_comp["valor_dolar"])?></b></td>
           </tr>

           <?
           }
           ?>
          </table>
       </td>
   </tr>
   <!-- Datos del Balance -->
   <tr>
      <td width=100%>
          <table width=100% align=center border=0 cellpading=0 cellspacing=0>
              <tr>
                 <td colspan=2>&nbsp;</td>
              </tr>

              <tr>

                   <!--Activo-->
                   <td width=50% align=center valign=top >
                          <table width=100% align=center >
                             <tr >
                               <td id=mo_sf2 width=1%><!-- check-->&nbsp;</td>
                               <td width=40% id=mo_sf4><!-- titulo -->&nbsp;</td>
                               <td width=3%  id=mo_sf4><!-- operador-->&nbsp;</td>
                               <td width=30% id=mo_sf4 align=right>$</td>
                               <td width=3%  id=mo_sf4><!-- operador-->&nbsp;</td>
                               <td width=30% id=mo_sf4 align=right><b>U$S</b></td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cuentas_a_cobrar'):Ocultar('cuentas_a_cobrar')";>
                               </td>
                               <td id=mo_sf2><b>Cuentas a Cobrar</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["cuentas_a_cobrar_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["cuentas_a_cobrar_dolares"])?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$datos["cuentas_a_cobrar_pesos"];
                             $total_haber_dolar+=$datos["cuentas_a_cobrar_dolares"];

                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cuentas_a_cobrar_comp'):Ocultar('cuentas_a_cobrar_comp')";>
                                       </td>
                                       <td ><b>Cuentas a Cobrar</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["cuentas_a_cobrar_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["cuentas_a_cobrar_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["cuentas_a_cobrar_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["cuentas_a_cobrar_dolares"];
                             }
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                 <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('bancos'):Ocultar('bancos')";>
                               </td>
                               <td id=mo_sf2><b>Bancos</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["bancos_pesos"])?></td>
                               <td id=mo_sf4>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["bancos_dolares"])?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$datos["bancos_pesos"];
                             $total_haber_dolar+=$datos["bancos_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('bancos_comp'):Ocultar('bancos_comp')";>
                                       </td>
                                       <td ><b>Bancos</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["bancos_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["bancos_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["bancos_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["bancos_dolares"];

                             }
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('stock'):Ocultar('stock')";>
                               </td>
                               <td id=mo_sf2><b>Stock</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["stock_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["stock_dolares"])?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$datos["stock_pesos"];
                             $total_haber_dolar+=$datos["stock_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('stock_comp'):Ocultar('stock_comp')";>
                                       </td>
                                       <td ><b>Stock</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["stock_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["stock_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["stock_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["stock_dolares"];

                             }
                             //bienens de uso
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('bienes_de_uso'):Ocultar('bienes_de_uso')";>
                               </td>
                               <td id=mo_sf2><b>Bienes de Uso</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["bienes_de_uso_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["bienes_de_uso_dolares"])?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$datos["bienes_de_uso_pesos"];
                             $total_haber_dolar+=$datos["bienes_de_uso_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('bienes_de_uso_comp'):Ocultar('bienes_de_uso_comp')";>
                                       </td>
                                       <td ><b>Bienes de Uso</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["bienes_de_uso_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["bienes_de_uso_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["bienes_de_uso_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["bienes_de_uso_dolares"];

                             }
                             ?>


                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('adelantos'):Ocultar('adelantos')";>
                               </td>
                               <td id=mo_sf2><b>Adelantos</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["adelantos_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["adelantos_dolares"])?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$datos["adelantos_pesos"];
                             $total_haber_dolar+=$datos["adelantos_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('adelantos_comp'):Ocultar('adelantos_comp')";>
                                       </td>
                                       <td ><b>Adelantos</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["adelantos_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["adelantos_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["adelantos_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["adelantos_dolares"];

                             }
                             ?>

                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('caja'):Ocultar('caja')";>
                               </td>
                               <td id=mo_sf2><b>Caja</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["caja_pesos"])?></td>
                               <td id=mo_sf4>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["caja_dolares"])?></td>
                             </tr>
                             <?
                           $total_haber_pesos+=$datos["caja_pesos"];
                           $total_haber_dolar+=$datos["caja_dolares"];
                           if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('caja_comp'):Ocultar('caja_comp')";>
                                       </td>
                                       <td ><b>Caja</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["caja_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["caja_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["caja_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["caja_dolares"];

                             }
                             ?>

                             <tr>
                               <td id=mo_sf2>
                                <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('depositos_pendientes'):Ocultar('depositos_pendientes')";>
                               </td>
                               <td id=mo_sf2><b>Dépositos Pendientes</b></td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["depositos_pendientes_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["depositos_pendientes_dolares"])?></td>
                             </tr>
                            <?
                            $total_haber_pesos+=$datos["depositos_pendientes_pesos"];
                            $total_haber_dolar+=$datos["depositos_pendientes_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('depositos_pendientes_comp'):Ocultar('depositos_pendientes_comp')";>
                                       </td>
                                       <td ><b>Dépositos Pendientes</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["depositos_pendientes_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["depositos_pendientes_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["depositos_pendientes_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["depositos_pendientes_dolares"];

                             }
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cheques_diferidos'):Ocultar('cheques_diferidos')";>
                               </td>
                               <td id=mo_sf2>Cheques Dif. Pendientes</td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["cheques_diferidos_pendientes_pesos"])?></td>
                               <td id=mo_sf4>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["cheques_diferidos_pendientes_dolares"])?></td>
                             </tr>

                             <?
                             $total_haber_pesos+=$datos["cheques_diferidos_pendientes_pesos"];
                             $total_haber_dolar+=$datos["cheques_diferidos_pendientes_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cheques_diferidos_comp'):Ocultar('cheques_diferidos_comp')";>
                                       </td>
                                       <td ><b>Cheques Dif. Pendientes</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["cheques_diferidos_pendientes_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["cheques_diferidos_pendientes_dolares"])?></td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["cheques_diferidos_pendientes_pesos"];
                                $total_haber_dolar_comp+=$datos_comp["cheques_diferidos_pendientes_dolares"];

                             }
                             ?>

                             <tr>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf2>Saldo Libre Disponibilidad</td>
                               <td id=mo_sf2>&nbsp; </td>
                               <td id=mo_sf3>
                                <?=formato_money($datos["saldo_libre_disponibilidad"])?>
                               </td>
                               <td id=mo_sf3 colspan=2>&nbsp;</td>
                              </tr>
                              <?
                              $total_haber_pesos+=$datos["saldo_libre_disponibilidad"];
                              if ($comparar) {
                              ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td>&nbsp; </td>
                                       <td ><b>Saldo Libre Disponibilidad</b></td>
                                       <td >&nbsp;</td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["saldo_libre_disponibilidad"])?></td>
                                       <td colspan=2>&nbsp;</td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["saldo_libre_disponibilidad"];

                             }
                             ?>
                             


                             <tr>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf2>SUSS</td>
                               <td id=mo_sf2>&nbsp; </td>
                               <td id=mo_sf3>
                                <?=formato_money($datos["suss"])?>
                               </td>
                               <td id=mo_sf3 colspan=2>&nbsp;</td>
                              </tr>
                              <?
                              $total_haber_pesos+=$datos["suss"];
                              if ($comparar) {
                              ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td>&nbsp; </td>
                                       <td ><b>SUSS</b></td>
                                       <td >&nbsp;</td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["suss"])?></td>
                                       <td colspan=2>&nbsp;</td>
                                     </tr>

                             <?
                                $total_haber_pesos_comp+=$datos_comp["suss"];

                             }
                             ?>

      
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td id=mo_sf5>Totales por Moneda</td>
                               <td id=mo_sf2>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($total_haber_pesos)?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($total_haber_dolar)?></td>
                             </tr>
                             <?
                             if ($comparar) {
                             ?>
                            <tr bgcolor=<?=$bgcolor1?>>
                               <td >&nbsp;</td>
                               <td ><b>Totales por Moneda</b></td>
                               <td >
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf_c><?=formato_money($total_haber_pesos_comp)?></td>
                               <td >
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf_c><?=formato_money($total_haber_dolar_comp)?></td>
                             </tr>

                             <?
                             }
                             $total_activo_corriente=($total_haber_dolar*$datos["valor_dolar"]) + $total_haber_pesos;
                             if ($comparar)
                                    $total_activo_corriente_comp=($total_haber_dolar_comp*$datos_comp["valor_dolar"]) + $total_haber_pesos_comp;
                             ?>
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td colspan=3 id=mo_sf5><font size=2><b>Total Activo Corriente</b></font></td>
                               <td colspan=2 id=mo_sf4><?=formato_money($total_activo_corriente)?></td>
                             </tr>
                             <?
                             if ($comparar) {
                             ?>
                             <tr bgcolor=<?=$bgcolor1?>>
                               <td id=mo_sf_c>&nbsp;</td>
                               <td colspan=3 ><font size=2><b>Total Activo Corriente</b></font></td>
                               <td colspan=2 id=mo_sf_c><?=formato_money($total_activo_corriente_comp)?></td>
                             </tr>

                             <?
                             }
                             ?>
                          </table>
                   </td>
                   <!--Pasivo-->
                   <td width=50% align=center valign=top>
                          <table width=100% align=center border=0>
                             <tr >
                               <td id=mo_sf2 width=1%><!-- check-->&nbsp;</td>
                               <td width=40% id=mo_sf4><!-- titulo -->&nbsp;</td>
                               <td width=3%  id=mo_sf4><!-- operador-->&nbsp;</td>
                               <td width=30% id=mo_sf4 align=right>$</td>
                               <td width=3%  id=mo_sf4><!-- operador-->&nbsp;</td>
                               <td width=30% id=mo_sf4 align=right><b>U$S</b></td>
                             </tr>

                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cheques'):Ocultar('cheques')";>
                               </td>
                               <td id=mo_sf2><b>Cheques</b></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["cheques_pendientes_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["cheques_pendientes_dolares"])?></td>
                             </tr>
                             <?
                             $total_debe_pesos+=$datos["cheques_pendientes_pesos"];
                             $total_debe_dolar+=$datos["cheques_pendientes_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cheques_comp'):Ocultar('cheques_comp')";>
                                       </td>
                                       <td ><b>Cheques</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["cheques_pendientes_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["cheques_pendientes_dolares"])?></td>
                                     </tr>

                             <?
                             $total_debe_pesos_comp+=$datos_comp["cheques_pendientes_pesos"];
                             $total_debe_dolar_comp+=$datos_comp["cheques_pendientes_dolares"];
                             }
                             ?>

                             <tr>
                               <td id=mo_sf2>
                                <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('deuda_comercial'):Ocultar('deuda_comercial')";>
                               </td>
                               <td id=mo_sf2><b>Deuda Comercial</b></td>
                               <td id=mo_sf4>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["deuda_comercial_pesos"])?></td>
                               <td id=mo_sf4>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf4><?=formato_money($datos["deuda_comercial_dolares"])?></td>
                             </tr>
                             <?
                             $total_debe_pesos+=$datos["deuda_comercial_pesos"];
                             $total_debe_dolar+=$datos["deuda_comercial_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('deuda_comercial_comp'):Ocultar('deuda_comercial_comp')";>
                                       </td>
                                       <td ><b>Deuda Comercial</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["deuda_comercial_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["deuda_comercial_dolares"])?></td>
                                     </tr>

                             <?
                             $total_debe_pesos_comp+=$datos_comp["deuda_comercial_pesos"];
                             $total_debe_dolar_comp+=$datos_comp["deuda_comercial_dolares"];

                             }
                             ?>

                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('deuda_financiera'):Ocultar('deuda_financiera')";>
                               </td>
                               <td id=mo_sf2><b>Deuda Financiera</b></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["deuda_financiera_pesos"])?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($datos["deuda_financiera_dolares"])?></td>
                             </tr>
                             <?
                             $total_debe_pesos+=$datos["deuda_financiera_pesos"];
                             $total_debe_dolar+=$datos["deuda_financiera_dolares"];
                             if ($comparar) {
                             ?>
                                     <tr bgcolor=<?=$bgcolor1?>>
                                       <td >
                                            <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('deuda_financiera_comp'):Ocultar('deuda_financiera_comp')";>
                                       </td>
                                       <td ><b>Deuda Financiera</b></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["deuda_financiera_pesos"])?></td>
                                       <td >
                                          <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                                       </td>
                                       <td id=mo_sf_c><?=formato_money($datos_comp["deuda_financiera_dolares"])?></td>
                                     </tr>

                             <?
                             $total_debe_pesos_comp+=$datos_comp["deuda_financiera_pesos"];
                             $total_debe_dolar_comp+=$datos_comp["deuda_financiera_dolares"];

                             ?>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                             </tr>

                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                             </tr>

                             <?
                             }
                             ?>

                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3>&nbsp;</td>
                             </tr>
                             <tr>
                                <td id=mo_sf3 colspan=6>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td id=mo_sf5>Totales por Moneda</td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($total_debe_pesos)?></td>
                               <td id=mo_sf3>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf3><?=formato_money($total_debe_dolar)?></td>
                             </tr>
                             <?
                             if ($comparar){
                             ?>
                             <tr bgcolor=<?=$bgcolor1?>>
                               <td >&nbsp;</td>
                               <td ><b>Totales por Moneda</b></td>
                               <td >
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf_c><?=formato_money($total_debe_pesos_comp)?></td>
                               <td id=mo_sf_c>
                                  <img src=<?=$img_mas?> width="10" height="10" align="absmiddle" >
                               </td>
                               <td id=mo_sf_c><?=formato_money($total_debe_dolar_comp)?></td>
                             </tr>

                             <?
                             }

                             $total_pasivo_corriente=($total_debe_dolar*$datos["valor_dolar"])+ $total_debe_pesos;


                             ?>
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td colspan=3 id=mo_sf5><font size=2><b>Total Pasivo Corriente</b></font></td>
                               <td colspan=2 id=mo_sf4><?=formato_money($total_pasivo_corriente)?></td>
                             </tr>
                             <?
                             if ($comparar) {
                                 $total_pasivo_corriente_comp=($total_debe_dolar_comp*$datos_comp["valor_dolar"])+ $total_debe_pesos_comp;
                             ?>
                             <tr bgcolor=<?=$bgcolor1?>>
                               <td >&nbsp;</td>
                               <td colspan=3 ><font size=2><b>Total Pasivo Corriente</b></font></td>
                               <td colspan=2 id=mo_sf_c><?=formato_money($total_pasivo_corriente_comp)?></td>
                             </tr>


                             <?
                             }
                             ?>

                          </table>

                   </td>
              </tr>
              <?
              $neto=$total_activo_corriente - $total_pasivo_corriente;
              ?>
              <tr>
                 <td colspan=2>&nbsp;</td>
              </tr>
              <tr>
                <td <?=$bgcolor2?> colspan=2>
                   <table width=50% align=center>
                          <tr>
                             <td align=center bgcolor=<?=$bgcolor2?> align=right><font color=red size=3><b>Neto</b></font></td>
                             <td align=center bgcolor=<?=$bgcolor2?> align=center><font size=3><b>$</b></font></td>
                             <td align=center bgcolor=<?=$bgcolor2?> align=left><font size=3><b><?=formato_money($neto)?></b></font></td>

                          </tr>
                    </table>
             </td>
             </tr>
             <?
             if ($comparar){
                  $neto_comp=$total_activo_corriente_comp- $total_pasivo_corriente_comp;

             ?>

              <tr >
                <td <?=$bgcolor2?> colspan=2>
                   <table width=50% align=center >
                          <tr>
                             <td align=center bgcolor=<?=$bgcolor1?> align=right><font color=red size=3><b>Neto</b></font></td>
                             <td align=center bgcolor=<?=$bgcolor1?> align=center><font size=3><b>$</b></font></td>
                             <td align=center bgcolor=<?=$bgcolor1?> align=left><font size=3><b><?=formato_money($neto_comp)?></b></font></td>

                          </tr>
                    </table>
             </td>
             </tr>

             <?
             }
             ?>
          </table>
      </td>
   </tr>
</table>
     <div id=cuentas_a_cobrar style='display:none'>
     <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Cuentas a Cobrar </td></tr>
          <tr>
            <td>
            Facturas en estado PENDIENTE en seguimiento de cobros, con ID en estado Entregado
            </td>
          </tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==3) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Cuentas a Cobrar"));
                   $onclick=" onclick='window.open(\"$link\")'";                   
                   
                   
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
                </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar)  {
       ?>
      <div id=cuentas_a_cobrar_comp style='display:none'>
      <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Cuentas a Cobrar </td></tr>
          <tr>
            <td>
            Facturas en estado PENDIENTE en seguimiento de cobros, con ID en estado Entregado
            </td>
          </tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==3) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos_comp["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Cuentas a Cobrar"));
                   $onclick=" onclick='window.open(\"$link\")'";                   
                   
                   
                   
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
                </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>
       <!-- bancos -->
       <div id=bancos style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Bancos</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==1) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
               </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- bancos -->
       <div id=bancos_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Bancos</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==1) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
               </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>
       <!-- stock -->
       <div id=stock style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Stock</td></tr>
          <tr><td title="STOCK DE PRODUCCION: OC CUYO ID NO ESTE ENTREGADO Y QUE LA OC ESTA ENTREGADA">Stock + Stock Producción + Notas Créditos Pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==2) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- stock -->
       <div id=stock_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Stock</td></tr>
          <tr><td title="STOCK DE PRODUCCION: OC CUYO ID NO ESTE ENTREGADO Y QUE LA OC ESTA ENTREGADA">Stock + Stock Producción + Notas Créditos Pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==2) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
               </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>
       <!--  bienes de uso  -->
       <div id=bienes_de_uso style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Bienes de Uso</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==11) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <div id=bienes_de_uso_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Stock</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==11) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
               </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>


       <!-- adelantos -->
       <div id=adelantos style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Adelantos</td></tr>
          <tr><td title="Montos pagados de OC que se recibieron los productos y no se entregaron">
          Montos Pagados OC Internacionales + Montos Pagados de OC asociadoas a Lic <br>
          el cual no se recibieron todos los productos
          </td></tr>
          <tr>
            <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==10) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Adelantos"));
                   $onclick=" onclick='window.open(\"$link\")'";                   
                   
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
                 
       ?>

       <!-- adelantos -->
       <div id=adelantos_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Adelantos</td></tr>
          <tr><td title="Montos pagados de OC que se recibieron los productos y no se entregaron">
          Montos Pagados OC Internacionales + Montos Pagados de OC asociadoas a Lic <br>
          el cual no se recibieron todos los productos
          </td></tr>
          <tr>
            <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==10) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos_comp["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Adelantos"));
                   $onclick=" onclick='window.open(\"$link\")'";                      
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td <?=$onclick?>><?=$nombre?></td>
                           <td <?=$onclick?>><?=$moneda?></td>
                           <td <?=$onclick?> align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>
       <!-- cajas -->
       <div id=caja style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Cajas</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==4) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- cajas -->
       <div id=caja_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Cajas</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==4) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>
       <!-- Depositos Pendientes -->
       <div id=depositos_pendientes style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Depósitos pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==5) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- Depositos Pendientes -->
       <div id=depositos_pendientes_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Depósitos pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==5) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <?
       }
       ?>
       <!-- cheques diferidos -->
       <div id=cheques_diferidos style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Cheques Diferidos Pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==6) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>

       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- cheques diferidos -->
       <div id=cheques_diferidos_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Cheques Diferidos Pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==6) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>

       </table>
       </div>

       <?
       }
       ?>
       <!--
        Deuda
       -->
       <!-- cheques -->
       <div id=cheques style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Cheques </td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
               if ($datos["detalle_balance"][$i]["id"]==7) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                    $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Cheques"));
                   $onclick=" onclick='window.open(\"$link\")'";                       
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td <?=$onclick?>><?=$nombre?></td>
                           <td <?=$onclick?>><?=$moneda?></td>
                           <td <?=$onclick?> align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>

       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- cheques -->
       <div id=cheques_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Cheques </td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
               for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
               if ($datos_comp["detalle_balance"][$i]["id"]==7) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos_comp["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Cheques"));
                   $onclick=" onclick='window.open(\"$link\")'";                           
                   ?>
                         <tr <?=atrib_tr()?>>
                           <td <?=$onclick?>><?=$nombre?></td>
                           <td <?=$onclick?>><?=$moneda?></td>
                           <td <?=$onclick?> align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>
            </table>
          </td>
          </tr>

       </table>
       </div>
       <?
       }
       ?>
       <!-- deuda financiera -->
       <div id=deuda_financiera style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Deuda Financiera </td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
                <?
                for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
                if ($datos["detalle_balance"][$i]["id"]==9) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Deuda Financiera"));
                   $onclick=" onclick='window.open(\"$link\")'";                     
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>

                </table>
             </td>
          </tr>

       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- deuda financiera -->
       <div id=deuda_financiera_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Deuda Financiera </td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
                <?
                for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
                if ($datos_comp["detalle_balance"][$i]["id"]==9) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos_comp["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Deuda Financiera"));
                   $onclick=" onclick='window.open(\"$link\")'";                     
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }
                ?>

                </table>
             </td>
          </tr>

       </table>
       </div>
       <?
       }
       ?>
       <!-- deuda Comercial -->
       <div id=deuda_comercial style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Deuda Comercial </td></tr>
          <tr><td>OC. que se recibieron los productos y no se pagaron</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
                <?
                for($i=0;$i<sizeof($datos["detalle_balance"]);$i++){
                if ($datos["detalle_balance"][$i]["id"]==8) {
                   $nombre=$datos["detalle_balance"][$i]["nombre"];
                   $moneda=$datos["detalle_balance"][$i]["moneda"];
                   $monto=$datos["detalle_balance"][$i]["monto"];
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Deuda Comercial"));
                   $onclick=" onclick='window.open(\"$link\")'";                   
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }
                }  //del for
                ?>

                </table>
             </td>
          </tr>
       </table>
       </div>
       <?
       if ($comparar){
       ?>
       <!-- deuda Comercial -->
       <div id=deuda_comercial_comp style='display:none'>
       <table width=70% align=center  class='informacion_balance_comp' >
          <tr id=mo><td>Deuda Comercial </td></tr>
          <tr><td>OC. que se recibieron los productos y no se pagaron</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
                <?
                for($i=0;$i<sizeof($datos_comp["detalle_balance"]);$i++){
                if ($datos_comp["detalle_balance"][$i]["id"]==8) {
                   $nombre=$datos_comp["detalle_balance"][$i]["nombre"];
                   $moneda=$datos_comp["detalle_balance"][$i]["moneda"];
                   $monto=$datos_comp["detalle_balance"][$i]["monto"];
                   
                   $link=encode_link("detalle_items_balance.php",array("id_detalle_balance_historial"=>$datos_comp["detalle_balance"][$i]["id_detalle_balance_historial"],"titulo"=>"Deuda Comercial"));
                   $onclick=" onclick='window.open(\"$link\")'";                   
                   
                   ?>
                         <tr <?=atrib_tr()?> <?=$onclick?>>
                           <td><?=$nombre?></td>
                           <td><?=$moneda?></td>
                           <td align=right><?=$monto?></td>
                         </tr>
                  <?
                  }  //del if
                } //del for
                ?>

                </table>
             </td>
          </tr>
       </table>
       </div>

       <?
       }
       ?>
<table width=100% align=center>
   <tr>
      <td align=center>
       <input type=button name=cerrar value=Cerrar onclick="window.close()">
       </td>
   </tr>
</td>
   </tr>
</table>
</form>

<?
echo fin_pagina();
?>