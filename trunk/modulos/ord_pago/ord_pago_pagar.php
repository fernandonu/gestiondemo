<?
/*
MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.4 $
$Date: 2006/06/20 14:10:20 $
*/

require_once("../../config.php");
require_once("../ord_pago/fns.php");
//variables que necesito para la informacion general
$nro_orden=$parametros["nro_orden"];
$comentarios=$_POST['comentario_db'];
$pagina_viene=$parametros["pagina_viene"];

//seleccionamos la moneda de la orden
$query="select id_moneda from orden_de_compra where nro_orden=$nro_orden";
$money=$db->Execute($query) or die($db->ErrorMsg()."seleccion de moneda para la orden");

$id_moneda=$money->fields['id_moneda'];

//obtengo los datos de la orden de compra y los pagos que realizo
//$sql="select pago_orden.id_pago,ordenes_pagos.monto,ordenes_pagos.N�meroch,ordenes_pagos.id_ingreso_egreso,ordenes_pagos.idd�bito";
$sql="select * ";
$sql.=" from  pago_orden join ordenes_pagos using(id_pago)";
$sql.=" where pago_orden.nro_orden=$nro_orden order by id_forma";
$orden_compra_pagos=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);

//obtengo los datos relativos al pago de la orden de compra
$sql="select moneda.simbolo,moneda.nombre,orden_de_compra.habilitar_pago_especial as pago_especial,orden_de_compra.id_proveedor,orden_de_compra.comentario_pagos,";
$sql.="orden_de_compra.estado,orden_de_compra.valor_dolar,forma_de_pago.dias as dias_pago,";
$sql.= "plantilla_pagos.descripcion as nombre_pago, tipo_pago.descripcion as nombre_tipo_pago , forma_de_pago.dias ";
$sql.=" from orden_de_compra join moneda using(id_moneda)";
$sql.=" left join plantilla_pagos using (id_plantilla_pagos)";
$sql.=" join pago_plantilla using(id_plantilla_pagos) ";
$sql.=" join (select * from forma_de_pago order by id_forma) as forma_de_pago using (id_forma) ";
$sql.=" join tipo_pago using(id_tipo_pago) ";
$sql.=" where orden_de_compra.nro_orden=$nro_orden ";

$orden_inf_pago=$db->execute($sql) or die($db->Errormsg()."<br>".$sql);

$pago_especial=$orden_inf_pago->fields['pago_especial'];
$cantidad_pagos=$orden_inf_pago->RecordCount();
$nombre_tipo_pago=$orden_inf_pago->fields['nombre_pago'];
$estado=$orden_inf_pago->fields['estado'];
$valor_dolar_orden=$orden_inf_pago->fields['valor_dolar'];
$comentario_pagos=$orden_inf_pago->fields['comentario_pagos'];
$id_proveedor=$orden_inf_pago->fields['id_proveedor'];
$simbolo=$orden_inf_pago->fields['simbolo'];
$nombre_moneda=$orden_inf_pago->fields['nombre'];

$usuario=$_ses_user['name'];
//traemos, si hay, las ordenes atadas por pago multiple a la orden
$ordenes_atadas=PM_ordenes($nro_orden);

//obtengo los datos de la orden de compra con estado_orden_compra



//en esta parte realizo las acciones que vienen por post

//print_r($_POST);
for($i=0;$i<$_POST['cantidad_pagos'];$i++){
    if ($_POST["boton_pago_$i"]) {
    //A las distintas paginas les paso el nro de orden, el valor del dolar (si lo hay) y el id del pago
         $valor_dolar=$_POST["valor_dolar_$i"];
         $id_pago=$_POST["id_pago_$i"];
         //esto es para cuandopago con efectivo
         switch ($_POST["valor_boton_pago_$i"]) {
               case "Cheque":
     						guardar_montos_dolares();
                            $link=encode_link("../bancos/bancos_ing_ch.php",
                                         array("pagina"=>"ord_pago",
                                               "nro_orden"=>$nro_orden,
                                               "valor_dolar"=>$valor_dolar,
                                               "id_pago"=>$id_pago));
                             header("location: $link");
                             break; //del case cheque
               case "D�bito":
     						guardar_montos_dolares();
                            $link=encode_link("../bancos/bancos_ing_deb.php",
                                         array("pagina"=>"ord_pago",
                                               "nro_orden"=>$nro_orden,
                                               "valor_dolar"=>$valor_dolar,
                                               "id_pago"=>$id_pago));
                             header("location: $link");
                             break; //del case cheque
               case "Efectivo":
                  switch ($_POST["boton_pago_$i"]) {
				             case "Buenos Aires":
				    						guardar_montos_dolares();
				                            $link=encode_link("../caja/ingresos_egresos.php",
				                                         array("pagina"=>"egreso",
				                                               "pagina_viene"=>"ord_pago",
				                                               "nro_orden"=>$nro_orden,
				                                               "valor_dolar"=>$valor_dolar,
				                                               "id_pago"=>$id_pago,"distrito"=>2));
				                             header("location: $link");
				                             break; //del case cheque
				               case "San Luis":
				    						guardar_montos_dolares();
				                            $link=encode_link("../caja/ingresos_egresos.php",
				                                         array("pagina"=>"egreso",
				                                               "pagina_viene"=>"ord_pago",
				                                               "nro_orden"=>$nro_orden,
				                                               "valor_dolar"=>$valor_dolar,
				                                               "id_pago"=>$id_pago,
				                                               "distrito"=>1));
				                             header("location: $link");
				                             break; //del case cheque
				               default://se agrego pq en el DEMO solo guarda en San Luis
				    						guardar_montos_dolares();
				                            $link=encode_link("../caja/ingresos_egresos.php",
				                                         array("pagina"=>"egreso",
				                                               "pagina_viene"=>"ord_pago",
				                                               "nro_orden"=>$nro_orden,
				                                               "valor_dolar"=>$valor_dolar,
				                                               "id_pago"=>$id_pago,
				                                               "distrito"=>1));
				                             header("location: $link");
				                             break;
		         } //del switch de boton de pago
				break;
         }//del switch

         } //del if que selecciona los pagos
}  //del for

//guarda los montos cuando paga por primera vez
//esto es cuando edita la forma de pago cuando autorizan la orden de compra


if ($_POST['guardar']=="Guardar"){
	 $usuario=$_ses_user['name'];
     guardar_montos_dolares();
   } // del if de post guardar montos

switch ($estado){
     case 'r': //Rechazada
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="";
                $readonly_montos="";
                $control_pagos=1;

                break;
     case 'n': //Anulada
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="";
                $readonly_montos="";
                $control_pagos=1;
                break;

     case 'p': //Pendiente
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="";
                $readonly_montos="";
                $control_pagos=1;
                break;
     case 's': //Sin Terminar
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="";
                $readonly_montos="";
                $control_pagos=1;
                break;

     case 't': //Terminada
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="disabled";
                $readonly_montos="readonly";
                $control_pagos=1;
                break;
     case 'u': //Por Autorizar
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="disabled";
                $readonly_montos="";
                $control_pagos=1;
               break;

     case 'a': //Autorizada
                $disabled_pagar="disabled";
                $disabled_guardar_pagos="disabled";
                $readonly_montos="readonly";
                $control_pagos=0;
                break;
     case 'e': //Enviada
                $disabled_pagar="";
                $disabled_guardar_pagos="disabled";
                $readonly_montos="readonly";
                $control_pagos=0;
                break;

     case 'd': //Pagada
                $disabled_pagar="";
                $disabled_guardar_pagos="disabled";
                $readonly_montos="readonly";
                $control_pagos=0;
                break; //parcialmente pagada
     case 'g': //totalmente pagada
                $disabled_pagar="";
                $disabled_guardar_pagos="disabled";
                $readonly_montos="readonly";
                $control_pagos=0;
                break;

}

?>
<html>
<script>


function desplegar_distritos(boton,hidden)
{
//cuando paga con efectivo me agrega o quita una columna para
//habilitar el pago de san luis o buenos aires

if ( document.all.check_efectivo.value!=1){
    document.all.check_efectivo.value=1;
    var fila=document.all.botonera.insertRow();
    fila.insertCell(0);
    fila.cells[0].innerHTML="<div align='center'><b><font color='red' size='3'>Seleccione el Distrito </font><input type='submit' style='width:90' name="+boton.name+" Value='San Luis' ><input type='submit' style='width:90' name="+boton.name+" Value='Buenos Aires' > </div>";
    }
    else
    {
    document.all.botonera.deleteRow();
    document.all.check_efectivo.value=0;
    }

}

function control_montos(){
//primero sumo los totales de los input text
var importe;
var aux;
var total;
importe=0;

<?
for($i=0;$i<$cantidad_pagos;$i++){
echo "document.all.monto_$i.value=document.all.monto_$i.value.replace(',','.');";
// if ($valor_dolar_orden){
 if ($nombre_moneda=="D�lares"){
echo "document.all.valor_dolar_$i.value=document.all.valor_dolar_$i.value.replace(',','.');";
}
?>

if (!(isNaN(parseFloat(<?echo "document.all.monto_$i.value";?>))))
     {
     importe+=parseFloat(<?echo "document.all.monto_$i.value";?>);

     }
     //del if";
     else {
        alert('N�mero Inv�lido');
        return false;
        }

<?
// if ($valor_dolar_orden)
 if ($nombre_moneda=="D�lares")
 {
 ?>
 if (isNaN(parseFloat(<?echo "document.all.valor_dolar_$i.value";?>)))
      {
        alert('Valor Dolar  Inv�lido');
        return false;
        }


<?
 } //del control del isNaN
 }//del $valor_dolar_orden
?>
//realizo el control
<?
if($ordenes_atadas>1)
{
?>
total=document.all.total_a_pagar.value;
<?
}
else
{?>
 total=parseFloat(document.all.monto_total.value);
<?}?>
aux=total-importe;

if (((aux<=0.10)&&(aux>=0)) || ((aux>=-0.10)&&(aux<=0))){
                          document.all.monto_error.value=false;
                          }
                          else
                          {
                           document.all.monto_acreditado.value=importe;
                           document.all.monto_error.value=confirm('Advertencia - Los valores ingresados no se corresponden con el total, Desea Seguir?');
                          if (document.all.monto_error.value=='true'){
                                                              return true;
                                                              }
                                                                else {
                                                               return false;
                                                                }

                          }


  }

function recargar_padre(){
window.opener.document.location.reload();
window.close();
}

 </script>
 <body bgcolor='<? echo $bgcolor2;?>'  >
<?
//obtengo el monto a pagar
$monto_total=monto_a_pagar($nro_orden);
//para la accion del formulario
$link=encode_link($_SERVER['SCRIPT_NAME'],array("nro_orden"=>$nro_orden,"id_moneda"=>$id_moneda,"comentario_pagos"=>$parametros['comentario_pagos'],"pagina_viene"=>$parametros["pagina_viene"]));

?>
<form name="form1" id="form1" method="Post" action="<?echo $link;?>">
<input type="hidden" name="monto_total" value="<?=$monto_total;?>">
<input type="hidden" name="monto_acreditado" value="0">
<input type="hidden" name="moneda_dolares" value="<?if ($nombre_moneda=="D�lares") echo $valor_dolar_orden; else echo"0";?>">

<!--
     check_efectivo es para que me despliegue correctamente los
     botones de BUENOS AIRES- SAN LUIS
-->
<input type="hidden" name="check_efectivo" value="0">
<!--
     monto_error es para ver si la persona acepto con errores el monto
     manda un mail avisando tal situacion
-->


<input type="hidden" name="monto_error" value=false>
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
if ($parametros["exito"]){
?>
<div align="center">
<b> EL PAGO SE REGISTRO CON �XITO
</div>
<br>
<?
  }
?>
<table width="70%"  align="center" border="1" cellspacing="1"  bordercolor="#000000">
   <tr id="mo">
        <td colspan='2'> Orden de Pago Nro: <?=$nro_orden;?></td>
   </tr>
   <tr>
    <td>
       <table width='100%' align='center'>
        <tr >
          <td width="50%" >
             <b> Forma de Pago: <?=$nombre_tipo_pago;?>
          </td>
          <td width="50%" align='right' id='sub_tabla'>
           <?
           if(sizeof($ordenes_atadas)<=1)
           {?>
           <b><font size='2'> TOTAL A PAGAR: </font>
              <font size='2' color='red'>
              <?
              $monto_total=number_format($monto_total,"2",".","");
              echo "$simbolo  $monto_total";
              ?>
              </font>
           <?
           }
           else
            echo "<b><font size='2'> PAGO M�LTIPLE </font>";
           ?>
          </td>
          <?
          if ($nombre_moneda=="D�lares")
           {
           ?>
           <td style="cursor:hand" title="Consultar Valor del Dolar">
           <img src='<?php echo "$html_root/imagenes/dolar.gif" ?>' border="0"  onclick="window.open('../../lib/consulta_valor_dolar.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=0,top=0,width=160,height=140')"  >
           </td>
          <?
          }
          ?>

        </tr>
       </table>
     </td>

<!--
Parte que muestra los botones como tipo de pagos
Recupero las formas de pago que tiene habilitadas
y creo tantos botones como seas posible
-->
<?
 if(sizeof($ordenes_atadas)>1)
 {
?>
  <tr>
   <td width="100%">
     <table width="100%">
      <tr><td>
    <?$total_a_pagar=ordenes_pago_multiple($ordenes_atadas,$simbolo,"100%",1)?>
      </tr></td>
     </table>
   </td>
  </tr>
<?
 }
?>
<tr bordercolor="#800000">
  <td colspan='7' align='center' width='100%'>
  <?
  detalle_nc($nro_orden,$simbolo);
  ?>
 </td>
 </tr>

<tr>
  <td>
     <table width='100%' align='center' border='0' cellspacing='1'  bordercolor='#000000'>
            <tr id="mo">
                <td align='center'> Forma de Pago </td>
                <td align='center'> Monto a Pagar </td>
<? if ($nombre_moneda=="D�lares"){?>
                <td align='center'> Valor Dolar </td>
<?} ?>
                <td align='center'>  </td>
            </tr>
<input type='hidden' name='cantidad_pagos' value='<?=$cantidad_pagos;?>'>
<?
for($i=0;$i<$cantidad_pagos;$i++) {
   $nombre_tipo_pago=$orden_inf_pago->fields['nombre_tipo_pago'];
   $dias=$orden_inf_pago->fields['dias_pago'];
   $id_pago=$orden_compra_pagos->fields['id_pago'] ;
   $monto=$orden_compra_pagos->fields['monto'];
   $valor_dolar=$orden_compra_pagos->fields['valor_dolar'];

   if ($orden_compra_pagos->fields['n�meroch'] || $orden_compra_pagos->fields['idd�bito']||$orden_compra_pagos->fields['id_ingreso_egreso'])
          {
           $disabled_pagar="disabled";
           }//fin del then que me dice si habilitar el boton o no
           else
          {
          $disabled_pagar="";
   }

echo "<tr id='sub_tabla'>";
     echo "<td align='center'>";
      if ($dias) echo "<b>$nombre_tipo_pago a $dias d�as";
                 else echo "<b>$nombre_tipo_pago";
     echo "</td>";
     echo "<td align ='center'>";
       //echo "<input type='text' size='10' name='monto_$i'   $readonly_montos value=$monto>";
    $monto=number_format($monto,"2",".","");
     echo "<input type='text' style='text-align:right' size='10' $readonly_montos name='monto_$i' readonly  value=$monto>";

     echo "</td>";
if ($nombre_moneda=="D�lares"){
     $valor_dolar=number_format($valor_dolar,"2",".","");
     echo "<td align ='center'>";
     echo "<input type='text' style='text-align:right' size='10' name='valor_dolar_$i' value='$valor_dolar'>";
     echo "</td>";
} //del valor dolar

if ($nombre_tipo_pago=="Efectivo")
{
  $tipo_boton="button";
  $evento="onclick='desplegar_distritos(this,valor_boton_pago_$i)'";
}
else
{
   $tipo_boton="submit";
   $evento="";
}
     echo "<td align='center'>";
     echo "<input type='hidden' name='valor_boton_pago_$i'  value='$nombre_tipo_pago'  >\n";
     echo "<input type='hidden' name='id_pago_$i'  value='$id_pago'>\n";
     echo "<input type='$tipo_boton' name='boton_pago_$i' $disabled_pagar  style='width:90%'  value='Pagar' $evento>\n";
     echo "</td>";
echo "</tr>";
$orden_inf_pago->MoveNext();
$orden_compra_pagos->MoveNext();
}

?>
       </table>
   </td>
 </tr>

 <tr>
 <td>
  <table id=botonera>
  </table>
 </td>
 </tr>
 <tr id="mo">
  <td colspan='2' align='center'>
  Ingresar Comentarios
  </td>
 </tr>
 <tr>
    <td align='center' colspan='2'>
    <textarea  name="comentario_pagos" rows="3" cols="60" wrap="VIRTUAL" ><?=$comentario_pagos;?></textarea>
    </td>
 </tr>
 <tr>
 <td align="center">
 <!-- Tabla que contiene los botones de accion-->
    <table>
      <tr>
         <!--
         <td  align="center">
          <input type="button" name="resumen" value="Resumen de Pagos" style="width:150" onclick="location.href='<? $link;?>'">
         </td>
         -->
         <? if ($estado=="e" || $estado="m") { //unicamente si el estado es enviada aparece este boton
            $link=encode_link("ord_pago_seleccion.php",array("id_proveedor"=>$id_proveedor,"nro_orden"=>$nro_orden,"id_moneda"=>$id_moneda));?>
            <td  align="center">
            <input type="button" name="pago_multiple" value="Pago Multiple" style="width:150" onclick="location.href='<?=$link;?>'">
            </td>
        <?
         }
        //del if
        ?>
         <td align='center'>
         <input type='submit' name='guardar'  style="width:150" Value='Guardar'>
         </td>
         <?/*
         if ($pagina_viene=="ord_compra" || $parametros['ver']!="")
         {
           $tipo_boton="button";
           $name="botom";
           $value="Volver";
           $onclick="onclick=\"location.href='ord_pago_listar.php'\"";
         }
         else{
           $tipo_boton="button";
           $name="boton";
           $value="Salir";
           $onclick="onclick='recargar_padre();'";
         }*/
         ?>
         <td>
          <input type='button' name='boton'  value='Volver' style="width:150">
         </td>
       </tr>
     </table>
    <!-- Fin de la tabla -->
  </td>
 </tr>
</table>
<input type="hidden" name="total_a_pagar" value="<?=$total_a_pagar?>">
<script language="JavaScript">
//si se abrio desde OC comun
if (window.opener)
{
	document.forms[0].boton.value="Salir";
	document.forms[0].boton.onclick=recargar_padre;
}
//si se abrio desde Pagos/cobros OC
else
{
	document.forms[0].boton.value="Volver";
	document.forms[0].boton.onclick=function(){location.href='ord_pago_listar.php'};
}

</script>
</form>
</body>
</html>