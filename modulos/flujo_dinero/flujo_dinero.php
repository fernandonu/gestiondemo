<?php
/*
$Author: fernando $
$Revision: 1.13 $
$Date: 2005/01/03 13:19:23 $
*/
require_once("../../config.php");
include("funciones.php");


$dias_a_sumar=$_POST["dias_a_sumar"];

if (!$_POST["fecha_base"])
           $fecha_base=date("Y-m-d");
           else
           $fecha_base=$_POST["fecha_base"];

if ($_POST["aceptar"]) {
        $fecha_base=fecha_db($_POST["fecha"]);
       }

if (($_POST["flag"]!="0")&&($_POST["flag"]!=""))
                    {
                    $date=explode("-",$fecha_base);
                    $dias=$date[2];
                    $mes=$date[1];
                    $año=$date[0];
                    if ($_POST["suma_resta"])
                        $fecha_base=date("Y-m-d",mktime(0,0,0,$mes,$dias+$dias_a_sumar,$año));
                        else
                        $fecha_base=date("Y-m-d",mktime(0,0,0,$mes,$dias-$dias_a_sumar,$año));
                    }


if ($_POST["valor_dolar"]=="")
                $valor_dolar="2.95";
                else
                $valor_dolar=$_POST["valor_dolar"];


/*
$mes=$_POST["mes"];
$años=$_POST["años"];
if ($mes=="")$mes=date("n",mktime());
if ($años=="")$años=date("Y",mktime());
$datos  = datos_generales($mes,$años);



if (($_POST["flag"]!="0")&&($_POST["flag"]!=""))
                  {
                  //$semana=$_POST["semana"];
                  if ($_POST["suma_resta"]) $suma_resta="suma";
                                            else
                                            $suma_resta="resta";
                  $cantidad_dias=$_POST["dias_a_sumar"];
                  $semana_inicio=forma_semana_base($_POST["semana_base_inicio"],$suma_resta,$cantidad_dias);
                  $semana_fin=forma_semana_base($_POST["semana_base_fin"],$suma_resta,$cantidad_dias);
                  }
                  else
                  {
                  //$semana=$datos["semanas"]["comienzo"];
                   $semana_inicio=$datos["semanas"]["comienzo"];
                   $semana_fin=$datos["semanas"]["fin"];
                  }
$fechas = forma_semanas($semana_inicio,$semana_fin,0,0);
$cantidad_semanas=sizeof($fechas);
//echo "<br>";
//print_r($datos);
//print_r($fechas);
*/
echo $html_header;

?>
<script src="../../lib/NumberFormat150.js"></script>
<script>
  function cambia_datos(accion,dias){
  if (accion==0){
                 //resta
                 document.form1.suma_resta.value=0;
                 }
                 else
                 {
                 //suma
                 document.form1.suma_resta.value=1;
                 }

  document.form1.dias_a_sumar.value=dias;
  document.form1.flag.value=1;
  document.form1.submit();
  }

function actualizar_precios(){

var valor_dolar;
var total_dolares;
var total_pesos;
var total_cobranzas;
var total;
var pagos;
var saldo_banco;
var cantidad_semanas;



cantidad_semanas=parseInt(document.form1.cantidad_semanas.value);
valor_dolar=parseFloat(document.form1.valor_dolar.value);


if (isNaN(valor_dolar)) {
                         alert('Debe ingresar un dolar válido');
                         document.form1.valor_dolar.value=2.95
                         return false;
                         }

for(i=0;i<4;i++){
  //recupero los valores
  total_dolares=parseFloat(document.form1.total_cobranza_dolares_aux[i].value);
  total_pesos=parseFloat(document.form1.total_cobranza_pesos_aux[i].value);

  saldo=parseFloat(document.form1.saldo_aux[i].value);

  pagos_pesos=parseFloat(document.form1.pagos_pesos_aux[i].value);
  pagos_dolares=parseFloat(document.form1.pagos_dolares_aux[i].value);
  pagos=parseFloat(document.form1.pagos_aux[i].value);


  //saco las cuentas necesarias
  total_dolares=total_dolares*valor_dolar;
  total_cobranzas=total_pesos+total_dolares;

  total=saldo+total_cobranzas;
  pagos_dolares=pagos_dolares*valor_dolar;
  pagos=pagos_pesos+pagos_dolares;


  saldo_banco=total-pagos;


  //actualizo los cambios en los datos correspondientes

  document.form1.total_cobranza[i].value=formato_money(total_cobranzas);

  document.form1.total_cobranza_dolares[i].value=formato_money(total_dolares);
  document.form1.total[i].value=formato_money(total);
  document.form1.pagos[i].value=formato_money(pagos);
  document.form1.pagos_dolares[i].value=formato_money(pagos_dolares);
  document.form1.saldo_banco[i].value=formato_money(saldo_banco);
  //cambio el color de la celda del saldo si es menor
  //que 20000
  if (saldo_banco<=200000) celda_banco[i].style.background='#FF8080';
  }

//celda_banco_0.style.background='#FF8080';

}//fin de la funcion de actualizar precios

</script>
<?

cargar_calendario();
?>
<form name=form1 action="" method="Post">
<!--
Si flag es igual a cero se va a tomar la semana del select
en otro casa lo de los botones que hacen subir las semanas a su
antojo
-->
<input type=hidden name="flag" value=0>
<input type=hidden name="suma_resta" value=0>
<input type=hidden name="dias_a_sumar" value=7>
<input type=hidden name="dolar" value="<?=$valor_dolar?>">
<input type=hidden name="cantidad_semanas" value="<?=$cantidad_semanas?>">
<input type=hidden name="fecha_base" value="<?=$fecha_base?>">
<table width=100% align=center >
  <tr>
   <td>
    <table cellSpacing=0 cellPadding=0 border=0>
      <tr>
         <td bgColor=#ffffff width=10%>
          <table >
             <tr>
                <td><b><<</b></td>
                <td style="cursor:hand" onclick="cambia_datos(0,7);">
                  <b>1</b>
                </td>
                <td>|</td>
                <td style="cursor:hand" onclick="cambia_datos(0,14);">
                 <b>2</b>
                </td>
                <td>|</td>
                <td style="cursor:hand" onclick="cambia_datos(0,21);">
                 <b>3</b>
                </td>
                <td>|</td>
                <td style="cursor:hand" onclick="cambia_datos(0,28);">
                <b>4</b>
                </td>
             </tr>
          </table>
        </td>
        <td width=30% alig=center bgcolor='#F0F0F0'>
          <table width=100%>
            <tr>
               <td width=60% align=center>
               <b>Valor Dolar
               </td>
               <td width=20% align=center>
               <input type=text name=valor_dolar value='<?=$valor_dolar?>' size=4>
               </td>
               <td width=20% align=center>
               <input type=button name=actualizar_valores value='Actualizar' onclick="actualizar_precios();">
               </td>
            </tr>
          </table>
        </td>
         <td with=40% align=center  bgcolor='#D0D0D0'>
              <table width=100%>
                <tr>
                  <td align=center>
                  <b>Fecha Base</b>
                  </td>
                  <td align=center>
                  <input type=text name=fecha value="<?=fecha($fecha_base)?>" size=10>
                  <?echo link_calendario("fecha")?>
                  </td>
                  <td align=left>
                  <input type=submit name=aceptar value="Aceptar">
                  </td>
                </tr>
              </table>
         </td>
   <td bgColor=#ffffff width=10%>
    <table cellSpacing=0 cellPadding=0 border=0>
      <tr>
         <td>
          <table>
             <tr>
                <td><b>>></b></td>
                <td style="cursor:hand" onclick="cambia_datos(1,7);">
                  <b>1</b>
                </td>
                <td>|</td>
                <td style="cursor:hand" onclick="cambia_datos(1,14);">
                 <b>2</b>
                </td>
                <td>|</td>
                <td style="cursor:hand" onclick="cambia_datos(1,21);">
                 <b>3</b>
                </td>
                <td>|</td>
                <td style="cursor:hand" onclick="cambia_datos(1,28);">
                <b>4</b>
                </td>
             </tr>
          </table>
        </td>
       </tr>
      </table>
    </td>
    </tr>
    </table>
    </td>
  </tr>
  <!-- Aca van las semanas que quiero observar -->
  <tr>
   <td width=100%>
   <table width=100% align=center border=1 bordercolor='#000000' cellspacing=1 cellpadding=1>
        <tr id="mo">
           <td width=20% align=center>
           Hasta
           </td>
           <td width=20% align=center>
           Cobranzas
           </td>
           <td width=20% align=center title='Saldo banco + cobranzas'>
           Total
           </td>
           <td width=20% align=center title='Pagos de ordenes de compra, cheques pendientes'>
           Pagos
           </td>
           <td width=20% align=center title='Saldo Banco - Pagos'>
           Saldo Banco
           </td>
        </tr>
   <?
   $saldo=0;
   $total_cobranzas_anteriores=0;
   $cant_semanas=sizeof($fechas);
   //prueba apartir del dia de hoy

   $fechas=arma_fecha($fecha_base);
   $cant_semanas=sizeof($fechas);
   for ($i=0;$i<$cant_semanas;$i++){
    ?>
   <tr>
         <?
         //semanas
         $desde_hasta=$fechas[$i];
         $datos=array();
         $datos=obtener_montos($fechas[$i],$i);
         $total_cobranzas=($datos["cobranzas"]["U\$S"]*$valor_dolar)+$datos["cobranzas"]["\$"];

         $total_cobranzas_pesos=$datos["cobranzas"]["\$"];
         $total_cobranzas_dolares=$datos["cobranzas"]["U\$S"];

         if ($total_cobranzas_pesos=="")   $total_cobranzas_pesos=0;
         if ($total_cobranzas_dolares=="") $total_cobranzas_dolares=0;

         //saco todas las cuentas
         if (!$datos["saldo"]) $datos["saldo"]=0;
         $saldo=$datos["saldo"];
        // echo "saldo:$saldo <br>";
         $total=$saldo + $total_cobranzas ;
         //$total_cobranzas_anteriores+=$total_cobranzas;
         //pagos
         $pagos_pesos=$datos["pagos"]["\$"];
         $pagos_pesos+=$datos["cheques_pendientes"];
         $pagos_dolares=$datos["pagos"]["U\$S"];

         if($pagos_pesos=="")  $pagos_pesos=0;
         if($pagos_dolares=="")$pagos_dolares=0;

         $pagos=($pagos_dolares*$valor_dolar)+$pagos_pesos;
         //fin de pagos

         $saldo_banco=$total-$pagos;
         $saldo_banco_aux=$saldo_banco;
       ?>
         <!-- hidden para actualizar los valores con el valor dolar -->

         <input type=hidden name='total_cobranza_aux' value='<?=$total_cobranzas?>'>
         <input type=hidden name='total_cobranza_pesos_aux' value='<?=$total_cobranzas_pesos?>'>
         <input type=hidden name='total_cobranza_dolares_aux' value='<?=$total_cobranzas_dolares?>'>
         <input type=hidden name='total_aux' value='<?=$total?>'>
         <input type=hidden name='pagos_pesos_aux' value='<?=$pagos_pesos?>'>
         <input type=hidden name='pagos_dolares_aux' value='<?=$pagos_dolares?>'>
         <input type=hidden name='pagos_aux' value='<?=$pagos?>'>
         <input type=hidden name='saldo_aux' value='<?=$saldo?>'>
       <?
         $total_cobranzas=formato_money($total_cobranzas);
         $total_cobranzas_pesos=formato_money($total_cobranzas_pesos);
         $total_cobranzas_dolares=formato_money($total_cobranzas_dolares);
         $total=formato_money($total);
         $pagos_pesos=formato_money($pagos_pesos);
         $pagos_dolares=formato_money($pagos_dolares);
         $pagos=formato_money($pagos);
         $saldo_banco=formato_money($saldo_banco);

         //semanas
         ?>
         <td width=20% align=center id='ma'> <?=fecha($desde_hasta)?> </td>
         <td width=20% bgcolor='<?=$bgcolor3?>' align='center'>
                 <!--cobranzas-->
                <table width=100% align=Center cellSpacing=0 cellPadding=0 border=0>
                   <tr >
                      <td bgcolor=#009D9D align=center > $ </td>
                      <td bgcolor=#009D9D> <input  type=text name='total_cobranza' value='<?=$total_cobranzas?>' class=text_3 readonly> </td>
                  </tr>
                  <tr>
                       <td align=center> $ </td>
                       <td> <input type=text name='total_cobranza_pesos' value='<?=$total_cobranzas_pesos?>' class=text_3 readonly> </td>
                  </tr>
                  <tr>
                       <td align=center > U$S </td>
                       <td> <input type=text name='total_cobranza_dolares' value='<?=$total_cobranzas_dolares?>' class=text_3 readonly> </td>
                  </tr>
               </table>
         </td>
         <!--total-->
         <td width=20% bgcolor='<?=$bgcolor3?>' valign=center align=center>
           <table width=100% alig=center>
              <tr>
               <td>$</td>
               <td><input type=text name=total value="<?=$total?>" class=text_3 readonly></td>
             </tr>
            </table>
           </td>
         <!--pagos-->
         <td width=20% bgcolor='<?=$bgcolor3?>' valign=center align=center>
           <table width=100% alig=center cellSpacing=0 cellPadding=0 border=0>
              <tr>
                <td bgcolor=#FFC0C0 align=center>$</td>
                <td bgcolor=#FFC0C0><input type=text name='pagos' value="<?=$pagos?>" class=text_3 readonly></td>
             </tr>
             <tr>
                <td align=center>$</td>
                <td><input type=text name='pagos_pesos' value="<?=$pagos_pesos?>" class=text_3 readonly></td>
             </tr>
             <tr>
                <td align=center>U$S </td>
                <td><input type=text name='pagos_dolares' value="<?=$pagos_dolares?>" class=text_3 readonly></td>
             </tr>
         </table>
        </td>
        <?
        if ($saldo_banco_aux>200000) $color_celda_saldo=$bgcolor3;
                                else $color_celda_saldo="#FF8080";
        ?>
        <td id=celda_banco width=20% bgcolor='<?=$color_celda_saldo?>' valign=center align=center>
           <table width=100% alig=center>
           <tr>
           <td>$</td>
           <td><input type=text name=saldo_banco value="<?=$saldo_banco?>" class=text_3 readonly></td>
           </tr>
           </table>
        </td>

  </tr>
  <?
   }
   ?>
   <!-- Fin de las semanas que quiereo observar -->
   </table>
  </td>
  </tr>
  </table>
</form>
</BODY>
</HTML>
<?
echo fin_pagina();
?>