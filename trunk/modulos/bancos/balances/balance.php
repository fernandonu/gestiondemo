<?
/*
$Author: fernando $
$Revision: 1.98 $
$Date: 2007/02/15 21:10:07 $
*/
// IMPORTANTE - PRESTAR ATENCION
//TODOS LOS CAMBIOS ECHOS EN ESTA PAGINA TIENE QUE REFLEJARSE EN BALANCE_FOTOS.PHP
//Y EN BALANCE HISTORIAL Y EN    DETALLE_CUENTAS_A_COBRAR , DETALLE_DEUDA_COMERCIAL, DETALLE_STOCK_PRODUCCION
require_once("../../../config.php");
require_once("../../caja/func.php"); //para usar dia_habil_anterior
require_once("funciones_balance.php");

echo $html_header;
cargar_calendario();


if ($_POST["sld"]){

    $fecha=date("Y-m-d H:i:s");
    $usuario=$_ses_user["name"];
    $monto=$_POST["saldo_libre_disponibilidad"];
    $sql=" insert into saldo_libre_disponibilidad (monto,usuario,fecha) values ($monto,'$usuario','$fecha')";
    sql($sql) or fin_pagina();

}

if ($_POST['datos']) {

       //actualizo el valor de saldo de disponibilidad
       $fecha   = date("Y-m-d H:i:s");
       $usuario = $_ses_user["name"];
       $monto   = $_POST["saldo_libre_disponibilidad"];
       $sql     = " insert into saldo_libre_disponibilidad (monto,usuario,fecha) values ($monto,'$usuario','$fecha')";
       sql($sql) or fin_pagina();
       
       
       $monto   = $_POST["suss"];
       $sql     = " insert into suss (monto,usuario,fecha) values ($monto,'$usuario','$fecha')";
       sql($sql) or fin_pagina();
       
       $fecha_hasta = fecha_db($_POST['fecha']);
       $valor_dolar = $_POST['valor_dolar'];
       }
        else {
        $fecha_hasta=date("Y-m-d ",mktime());
        $sql="select valor from general.dolar_general";
        $res=sql($sql,"dolar") or fin_pagina();
        $valor_dolar=$res->fields['valor'];
        }

$fecha_hasta=date("Y-m-d");


$sql="select monto from saldo_libre_disponibilidad order by fecha DESC limit 1";
$res=sql($sql) or fin_pagina();
($res->fields["monto"])?$saldo_libre_disponibilidad=$res->fields["monto"]:$saldo_libre_disponibilidad=0;


$sql="select monto from suss order by fecha DESC limit 1";
$res=sql($sql) or fin_pagina();
($res->fields["monto"])?$suss=$res->fields["monto"]:$suss=0;




/*****************************************************************************
                                        HABER
******************************************************************************/


/*********************** Cuentas a cobrar *************************/
/*   facturas en estado PENDIENTE en seguimiento de cobros
    $cuentas_a_cobrar tiene el valor de este item */
$datos_cuentas_a_cobrar = sql_cuentas_a_cobrar(1,-1);

$cuentas_a_cobrar_pesos   = $datos_cuentas_a_cobrar["monto_pesos"];
$cuentas_a_cobrar_dolares = $datos_cuentas_a_cobrar["monto_dolar"];

$cuentas_a_cobrar=$cuentas_a_cobrar_pesos+ ($cuentas_a_cobrar_dolares*$valor_dolar);



/*******************************************************************************************
                                  BANCOS
*********************************************************************************************/
$datos_bancos=sql_bancos($fecha_hasta);

$arreglo_bancos=$datos_bancos["datos"];
$bancos=$datos_bancos["monto_pesos"]; 




/*****************************************************************************
                          BIENES DE USO
*******************************************************************************/


$sql="select sum(precio_unitario*cantidad)  as total
             from stock.inventario
             join stock.estado_inventario ei using(id_estado)
      ";

$res=sql($sql)  or fin_pagina();

 $total_bienes_de_uso_dolar=0;
($res->fields["total"])?$total_bienes_de_uso_pesos=$res->fields["total"]:$total_bienes_de_uso_pesos=0;
$array_bienes_de_uso=array("nombre"=>"Bienes de Uso","total"=>$total_bienes_de_uso_pesos,"moneda"=>"\$");




/******************************************************************************
                        STOCK

stock total + stock de produccion + notas de credito pendientes
/******************************************************************************/

$datos_stock=sql_stock();


$stock_depositos=$datos_stock["datos"];
$stock_pesos=$datos_stock["monto_pesos"];
$stock_dolar=$datos_stock["monto_dolar"];

//FALTA SUMAR ESTA CANTIDAD AL STOCK EN GENERAL
//$stock_depositos[]=array("nombre"=>"RMA","moneda"=>"U\$S","total"=>32169.52);

/**********************************************************************
                        CAJA
**********************************************************************/
 $mes = substr($fecha_hasta,5,2);
 $dia = substr($fecha_hasta,8,2);
 $anio = substr($fecha_hasta,0,4);
 $nrodiasemana = date('w', mktime(0,0,0,$mes,$dia,$anio));

if ($nrodiasemana==0 || feriado(fecha($fecha_hasta))) //si es domingo o feriado
 	 $fecha_caja=fecha_db(dia_habil_anterior(fecha($fecha_hasta)));
     else
     $fecha_caja=$fecha_hasta;

/**********************************************************************
                        CAJA  DE SEGURIDAD
**********************************************************************/

$sql=" select sum (monto) as total , id_moneda
       from item_caja_seguridad
       join caja_seguridad using(id_caja_seguridad)

       WHERE caja_seguridad.id_caja_seguridad='1'
              and item_caja_seguridad.estado='existente'
       group by  id_moneda";
$result=sql($sql) or fin_pagina();

for ($i=0;$i<$result->recordcount();$i++){
    if ($result->fields["id_moneda"]==1)
              $caja_de_seguridad_pesos=$result->fields["total"];
    if ($result->fields["id_moneda"]==2)
              $caja_de_seguridad_dolar=$result->fields["total"];

    $result->movenext();
}
if (!$caja_de_seguridad_pesos) $caja_de_seguridad_pesos=0;
if (!$caja_de_seguridad_dolar) $caja_de_seguridad_dolar=0;


$caja_pesos_sl = total_caja(1,1,$fecha_hasta);
$caja_pesos_bs = total_caja(2,1,$fecha_hasta);
$caja_pesos    = $caja_pesos_sl + $caja_pesos_bs + $caja_de_seguridad_pesos;

$caja_dolar_sl = total_caja(1,2,$fecha_hasta);
$caja_dolar_bs = total_caja(2,2,$fecha_hasta);
$caja_dolar    = $caja_dolar_sl + $caja_dolar_bs + $caja_de_seguridad_dolar;

$arreglo_cajas   = array();
$arreglo_cajas[] = array("caja"=>"Caja San Luis","moneda"=>"\$","id_moneda"=>1,"total"=>$caja_pesos_sl);
$arreglo_cajas[] = array("caja"=>"Caja San Luis","moneda"=>"u\$s","id_moneda"=>2,"total"=>$caja_dolar_sl);
$arreglo_cajas[] = array("caja"=>"Caja Bs. As.","moneda"=>"\$","id_moneda"=>1,"total"=>$caja_pesos_bs);
$arreglo_cajas[] = array("caja"=>"Caja Bs. As.","moneda"=>"u\$s","id_moneda"=>2,"total"=>$caja_dolar_bs);
$arreglo_cajas[] = array("caja"=>"Caja De Seguridad","moneda"=>"u\$s","id_moneda"=>2,"total"=>$caja_de_seguridad_dolar);
$arreglo_cajas[] = array("caja"=>"Caja De Seguridad","moneda"=>"\$","id_moneda"=>1,"total"=>$caja_de_seguridad_pesos);



/**********************************************************************
                      ADELANTOS
**********************************************************************/
   
 $adelantos=sql_adelantos(1); 
 
 //$array_adelantos=$adelentos[""];
 
 $array_adelantos[]=array("nombre"=>"Orden de Compra Pesos","total"=>$adelantos["monto_pesos"],"moneda"=>"\$");
 $array_adelantos[]=array("nombre"=>"Orden de Compra Dolar","total"=>$adelantos["monto_dolar"],"moneda"=>"u\$s");
 
 $adelantos_pesos+=$adelantos["monto_pesos"];
 $adelantos_dolar+=$adelantos["monto_dolar"];


/**********************************************************************
                         Depositos pendientes
***********************************************************************/
//depositos pendientes
$sql="SELECT sum(ImporteDep) as total,nombrebanco,idbanco
      FROM bancos.depósitos
      JOIN bancos.tipo_banco using(idbanco)
      WHERE bancos.depósitos.FechaCrédito IS NULL AND tipo_banco.activo=1
      and tipo_banco.idbanco<>10 and  tipo_banco.idbanco<>7 and tipo_banco.idbanco<>8
      group by nombrebanco,idbanco
      ";
$res=sql($sql) or fin_pagina();
$arreglo_depositos_pendientes=array();
$depositos_pendientes=0;
for($i=1;$i<=$res->recordcount();$i++){

  $nombre=$res->fields["nombrebanco"];
  $total=($res->fields["total"])?$res->fields["total"]:0;
  $idbanco=$res->fields["idbanco"];
  $arreglo_depositos_pendientes[]=array("nombre"=>$nombre,"total"=>$total,"idbanco"=>$idbanco);
  $res->movenext();
  $depositos_pendientes+=$total;

}



/********************************************************************
                  Cheques Diferidos Pendientes
*********************************************************************/

$total_cheques_diferidos=0;
$sql="SELECT sum(monto) as total,nombre,id_banco
	  FROM bancos.cheques_diferidos
          join bancos.bancos_cheques_dif using(id_banco)
	  WHERE cheques_diferidos.IdDepósito IS NULL
	  and cheques_diferidos.id_ingreso_egreso IS NULL and activo=1 
      group by nombre,id_banco ";

$res=sql($sql) or fin_pagina();
$arreglo_cheques_diferidos=array();
$cheques_diferidos_pendientes=0;
for($i=1;$i<=$res->recordcount();$i++){
    $total=($res->fields["total"])?$res->fields["total"]:0;
    $nombre=$res->fields["nombre"];
    $id_banco=$res->fields["id_banco"];
    $arreglo_cheques_diferidos[]=array("nombre"=>$nombre,"total"=>$total,"id_banco"=>$id_banco);
    $cheques_diferidos_pendientes+=$total;
    $res->movenext();

}





/*****************************************************************************
                                        DEBE
******************************************************************************/

/*******************************************************************************
                        Cheques
*******************************************************************************/
/*
$sql="SELECT sum(ImporteCh) AS total,nombrebanco,idbanco
             FROM bancos.cheques
             join bancos.tipo_banco using(idbanco)
             WHERE FechaDébCh IS NULL and fechaemich <='$fecha_hasta' and
                   tipo_banco.activo=1
                   and  tipo_banco.idbanco<>10  and tipo_banco.idbanco<>7 and tipo_banco.idbanco<>8
             group by nombrebanco,idbanco";
$res=sql($sql) or fin_pagina();

$arreglo_cheques=array();
$cheques=0;
for($i=1;$i<=$res->recordcount();$i++){
    $total=($res->fields["total"])?$res->fields["total"]:0;
    $nombre=$res->fields["nombrebanco"];
    $idbanco=$res->fields["idbanco"];
    $arreglo_cheques[]=array("nombre"=>$nombre,"total"=>$total,"idbanco"=>$idbanco);
    $cheques+=$total;
    $res->movenext();
   }
 */
$datos=sql_cheques_pendientes(1);
$arreglo_cheques = $datos["montos_por_banco"];
$cheques = $datos["monto_pesos"] ;



/*******************************************************************************
                        Deuda Comercial
*******************************************************************************/
//Ordenes de Compra que se recibieron los productos y no se pago nada
//Si se cambia la consulta aca, cambiarla en detalle_deuda_comercial
//ahora tiene en cuenta las ordenes internacionales

$deuda_comercial= sql_deuda_comercial(0,1);
$array_deuda_comercial[]=array("nombre"=>"Deuda Comercial Pesos","monto"=>$deuda_comercial["monto_pesos"],"moneda"=>"\$","internacional"=>0);
$deuda_comercial_pesos=$deuda_comercial["monto_pesos"];

$array_deuda_comercial[]=array("nombre"=>"Deuda Comercial Dolar","monto"=>$deuda_comercial["monto_dolar"],"moneda"=>"U\$S","internacional"=>0);
$deuda_comercial_dolar+=$deuda_comercial["monto_dolar"];

$deuda_comercial_internacional= sql_deuda_comercial(1,1);
$array_deuda_comercial[]=array("nombre"=>"Deuda Comercial Internacional Pesos","monto"=>$deuda_comercial_internacional["monto_pesos"],"moneda"=>"\$","internacional"=>1);
$deuda_comercial_pesos+=$deuda_comercial_internacional["monto_pesos"];

$array_deuda_comercial[]=array("nombre"=>"Deuda Comercial Internacional Dolar","monto"=>$deuda_comercial_internacional["monto_dolar"],"moneda"=>"U\$S","internacional"=>1);
$deuda_comercial_dolar+=$deuda_comercial_internacional["monto_dolar"];



 /************************************************************************************************************/
 /******************************************* Deuda Financiera  **********************************************/
 /************************************************************************************************************/
 /*

$sql="select sum(monto_prestamo) as total,moneda.simbolo,moneda.id_moneda
		        from bancos.facturas_venta fv
                        join licitaciones.moneda on moneda.id_moneda=fv.moneda
                WHERE  estado_venta=1
                group by moneda.simbolo,moneda.id_moneda
        ";
$res=sql($sql) or fin_pagina();

for ($i=0;$i<$res->recordcount();$i++){
    if ($res->fields["id_moneda"]==1){
          $deuda_financiera_pesos=$res->fields["total"];
    }
    else{
         $deuda_financiera_dolar=$res->fields["total"];
    }
 $res->movenext();
}
  */
  
 $deuda_financiera=sql_deuda_financiera(1);
 $deuda_financiera_pesos=$deuda_financiera["monto_pesos"];
 $deuda_financiera_dolar=$deuda_financiera["monto_dolar"];
 
  


?>
<script>

var img_mas='<?=$img_mas=$html_root.'/imagenes/mas.gif'?>';
var img_menos='<?=$img_menos=$html_root.'/imagenes/menos.gif'?>';

function control_datos(){


    if (document.all.valor_dolar.value.indexOf(',')!=-1)
     {
       alert("Especifique la parte fraccionada con .");
       return false;
     }
    if (isNaN(document.all.valor_dolar.value) || parseFloat(document.all.valor_dolar.value)=='0' || document.all.valor_dolar.value=="") {
        alert ('Debe ingresar número valido para el campo valor dolar ');
        document.all.valor_dolar.value="";
        return false;
    }

    if (document.all.saldo_libre_disponibilidad.value.indexOf(',')!=-1)
     {
       alert("Especifique la parte de saldo libre disponibiliadd fraccionada con .");
       return false;
     }
    if (isNaN(document.all.saldo_libre_disponibilidad.value)  || document.all.valor_dolar.value=="")
       {
        alert ('Debe ingresar número valido para el campo saldo libre disponibilidad');
       return false;
     }
return true;
}

function mostrar_texto(texto){
var fila=document.all.informacion.insertRow(document.all.informacion.rows.length );
fila.insertCell(0).innerHTML="<table width=100% align=Center class=bordes><tr><td align=center>"+texto+"</td></tr></table>";

//document.all.informacion.innerHTML="<br><br>"+texto + "<br><br>";
}
function ocultar_texto(){
document.all.informacion.innerText="";
}

function calcular(imagen){
  alert(imagen.src);
  alert(img_mas);
  if (imagen.src==img_mas)   imagen.src=imagen_menos;
  if (imagen.src==img_menos) imagen.src=imagen_mas;

}

</script>

<form name='form1' method='post' action='balance.php'>

<input type='hidden' name='flag_imagen' value=0>

<?$visib="none";?>
<br>
<?
//  <img src=$img_mas width="10" height="10" align="absmiddle" >";
$total_debe_pesos=0;
$total_debe_dolar=0;
$total_haber_pesos=0;
$total_haber_dolar=0;
?>


<table width=100% align=center class=bordes cellpading=0 cellspacing=0>
   <tr>
       <td id=mo>BALANCE</td>
   </tr>
   <!-- Traer datos junto con el valor dolar -->
   <tr >
       <td bgcolor=<?=$bgcolor2?>>
          <table width=70% align=center border=0>
             <tr>
                <td align="right"><b>Dolar</b></td>
                <td align="center"><input type='text' size='4' name='valor_dolar' value='<?=number_format($valor_dolar,"2",".","")?>'> </td>
                <td  align=left style="cursor:hand" title="Consultar Valor del Dolar">
                  <img src='<?php echo "$html_root/imagenes/dolar.gif" ?>' border="0"  onclick="window.open('../../../lib/consulta_valor_dolar.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=0,top=0,width=160,height=140')"  >
                </td>
                <td align="left">
                   <input type="submit" name="datos" value='Actualizar' onclick="return control_datos();">
                </td>
                <td>
                <?$link = encode_link("balance_historial.php",array("sacar_foto"=>1))?>
                 <input type="button" name="balance_old_2" value='Balance Historial' onclick="window.open('<?=$link?>')">
                </td> 
                <td>
                 <?$link = encode_link("balance_historial.php",array("sacar_foto"=>0))?>
                 <input type="button" name="balance_old_3" value='Balance Historial New' onclick="window.open('<?=$link?>')" title='no saca la foto, mas rapido'>
                </td>
                <td>
                  <?
                 $hora_desde="09:00:00";
                 $hora_hasta="19:00:00";
                 $fecha_desde=date("d/m/Y");
                 $fecha_hasta=date("d/m/Y");                  
                 $link=encode_link("balance_excel_listado.php",array("viene_de_historial"=>1,"viene_de_balance"=>1,"fecha_desde"=>$fecha_desde,"fecha_hasta"=>$fecha_hasta,"hora_desde"=>$hora_desde,"hora_hasta"=>$hora_hasta));
                  ?>
                 <input type="button" name="balance_excel_listado" value='Balance Excel' onclick="window.open('<?=$link?>')" title='no saca la foto, mas rapido'>                  
                </td>

             </tr>
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
                                &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($cuentas_a_cobrar_pesos)?></td>
                               <td id=mo_sf3>
                                &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($cuentas_a_cobrar_dolares)?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$cuentas_a_cobrar_pesos;
                             $total_haber_dolar+=$cuentas_a_cobrar_dolares;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                 <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('bancos'):Ocultar('bancos')";>
                               </td>
                               <td id=mo_sf2><b>Bancos</b></td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4><?=formato_money($bancos)?></td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4><?=formato_money(0)?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$bancos;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('stock'):Ocultar('stock')";>
                               </td>
                               <td id=mo_sf2><b>Stock</b></td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3><?=formato_money($stock_pesos)?></td>
                               <td id=mo_sf3>&nbsp;</td>
                               <td id=mo_sf3><?=formato_money($stock_dolar)?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$stock_pesos;
                             $total_haber_dolar+=$stock_dolar;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('adelantos'):Ocultar('adelantos')";>
                               </td>
                               <td id=mo_sf2><b>Adelantos</b></td>
                               <td id=mo_sf2>&nbsp; </td>
                               <td id=mo_sf4><?=formato_money($adelantos_pesos)?></td>
                               <td id=mo_sf4>
                                  &nbsp;
                               </td>
                               <td id=mo_sf4><?=formato_money($adelantos_dolar)?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$adelantos_pesos;
                             $total_haber_dolar+=$adelantos_dolar;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('bienes_de_uso'):Ocultar('bienes_de_uso')";>
                               </td>
                               <td id=mo_sf2><b>Bienes de Uso</b></td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3><?=formato_money($total_bienes_de_uso_pesos)?></td>
                               <td id=mo_sf3>
                                  &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($total_bienes_de_uso_dolar)?></td>
                             </tr>
                             <?
                             $total_haber_pesos+=$total_bienes_de_uso_pesos;
                             $total_haber_dolar+=$total_bienes_de_uso_dolar;
                             ?>

                             <tr>
                               <td id=mo_sf2>
                                    <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('caja'):Ocultar('caja')";>
                               </td>
                               <td id=mo_sf2><b>Caja</b></td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4><?=formato_money($caja_pesos)?></td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4><?=formato_money($caja_dolar)?></td>
                             </tr>
                             <?
                           $total_haber_pesos+=$caja_pesos;
                           $total_haber_dolar+=$caja_dolar;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('depositos_pendientes'):Ocultar('depositos_pendientes')";>
                               </td>
                               <td id=mo_sf2><b>Dépositos Pendientes</b></td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf3><?=formato_money($depositos_pendientes)?></td>
                               <td id=mo_sf3>&nbsp; </td>
                               <td id=mo_sf3><?=formato_money(0)?></td>
                             </tr>
                            <?
                            $total_haber_pesos+=$depositos_pendientes;
                            ?>
                             <tr>
                               <td id=mo_sf2>
                                <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('cheques_diferidos'):Ocultar('cheques_diferidos')";>
                               </td>
                               <td id=mo_sf2>Cheques Dif. Pendientes</td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4><?=formato_money($cheques_diferidos_pendientes)?></td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4><?=formato_money(0)?></td>
                             </tr>

                             <?
                             $total_haber_pesos+=$cheques_diferidos_pendientes;
                             ?>
                             <tr>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf2>Saldo Libre Disponibilidad</td>
                               <td id=mo_sf2>&nbsp; </td>
                               <td id=mo_sf3 colspan=3>
                                <input type=text name=saldo_libre_disponibilidad value="<?=number_format($saldo_libre_disponibilidad,"2",".","")?>" size=15>
                                <!--
                                <input type=submit name=sld value="A">
                                -->
                               </td>
                              </tr>
                              <?
                              $total_haber_pesos+=$saldo_libre_disponibilidad;
                              ?>
                              <!--Suss  -->
                             <tr>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf2>SUSS</td>
                               <td id=mo_sf2>&nbsp; </td>
                               <td id=mo_sf3 colspan=3>
                                <input type=text name=suss value="<?=number_format($suss,"2",".","")?>" size=15>
                                <!--
                                <input type=submit name=sld value="A">
                                -->
                               </td>
                              </tr>
                              <?
                              $total_haber_pesos+=$suss;
                              ?>                              
                              <!--Fin del Suss  -->
                              <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td id=mo_sf5>Totales por Moneda</td>
                               <td id=mo_sf2>
                                  &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($total_haber_pesos)?></td>
                               <td id=mo_sf3>&nbsp; </td>
                               <td id=mo_sf3><?=formato_money($total_haber_dolar)?></td>
                             </tr>
                             <?
                             $total_activo_corriente=($total_haber_dolar*$valor_dolar) + $total_haber_pesos;
                             ?>
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td colspan=3 id=mo_sf5><font size=2><b>Total Activo Corriente</b></font></td>
                               <td colspan=2 id=mo_sf4><?=formato_money($total_activo_corriente)?></td>
                             </tr>
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
                                  &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($cheques)?></td>
                               <td id=mo_sf3>
                                  &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money(0)?></td>
                             </tr>
                             <?
                             $total_debe_pesos+=$cheques;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                                <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('deuda_comercial'):Ocultar('deuda_comercial')";>
                               </td>
                               <td id=mo_sf2><b>Deuda Comercial</b></td>
                               <td id=mo_sf4>
                                  &nbsp;
                               </td>
                               <td id=mo_sf4><?=formato_money($deuda_comercial_pesos)?></td>
                               <td id=mo_sf4>
                                  &nbsp;
                               </td>
                               <td id=mo_sf4><?=formato_money($deuda_comercial_dolar)?></td>
                             </tr>
                             <?
                             $total_debe_pesos+=$deuda_comercial_pesos;
                             $total_debe_dolar+=$deuda_comercial_dolar;
                             ?>
                             <tr>
                               <td id=mo_sf2>
                               <input type=checkbox name=check value=1 class=estilos_check onclick="javascript:(this.checked)?Mostrar('deuda_financiera'):Ocultar('deuda_financiera')";>
                               </td>
                               <td id=mo_sf2><b>Deuda Financiera</b></td>
                               <td id=mo_sf3>
                                  &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($deuda_financiera_pesos)?></td>
                               <td id=mo_sf3>
                                 &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($deuda_financiera_dolar)?></td>
                             </tr>
                             <?
                             $total_debe_pesos+=$deuda_financiera_pesos;
                             $total_debe_dolar+=$deuda_financiera_dolar;
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
                               <td id=mo_sf3>
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
                               <td id=mo_sf3>
                               <input type=checkbox name=check value=1 class=estilos_check >
                               </td>
                               <td id=mo_sf2>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                               <td id=mo_sf4>&nbsp;</td>
                             </tr>                             
                             
                             
                             <tr>
                                <td id=mo_sf3 colspan=6>&nbsp;</td>
                             </tr>
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td id=mo_sf5>Totales por Moneda</td>
                               <td id=mo_sf3>
                                 &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($total_debe_pesos)?></td>
                               <td id=mo_sf3>
                                  &nbsp;
                               </td>
                               <td id=mo_sf3><?=formato_money($total_debe_dolar)?></td>
                             </tr>
                             <?
                             $total_pasivo_corriente=($total_debe_dolar*$valor_dolar)+ $total_debe_pesos;
                             ?>
                             <tr>
                               <td id=mo_sf5>&nbsp;</td>
                               <td colspan=3 id=mo_sf5><font size=2><b>Total Pasivo Corriente</b></font></td>
                               <td colspan=2 id=mo_sf4><?=formato_money($total_pasivo_corriente)?></td>
                             </tr>

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
               $link=encode_link("informe_balance.php",array("pagina"=>"cuentas_a_cobrar","titulo"=>"Informe  de Cuentas a Cobrar","id_moneda"=>1));
               $onclick=" onclick=\"window.open('$link')\"";
               ?>
                 <tr <?=atrib_tr();?>>
                   <td <?=$onclick?>>Cuentas a Cobrar Pesos</td>
                   <td <?=$onclick?>>$</td>
                   <td <?=$onclick?> align=right><?=$cuentas_a_cobrar_pesos?></td>
                 </tr>
               <?
               $link=encode_link("informe_balance.php",array("pagina"=>"cuentas_a_cobrar","titulo"=>"Informde  de Cuentas a Cobrar","id_moneda"=>2));
               $onclick=" onclick=\"window.open('$link')\"";
               ?>
                  <tr <?=atrib_tr();?>>
                    <td <?=$onclick?>>Cuentas a Cobrar Dólares</td>
                    <td <?=$onclick?>>u$s</td>
                    <td <?=$onclick?> align=right><?=$cuentas_a_cobrar_dolares?></td>
                 </tr>
                </table>
             </td>
          </tr>
       </table>
       </div>
       <!-- bancos -->
       <div id=bancos style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Bancos</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
                 <?
               //hago los link para bancos


                for($i=0;$i<sizeof($arreglo_bancos);$i++){

                    $nombre = $arreglo_bancos[$i]["nombre"];


                    if ($nombre!="Transferencias")
                               $link = encode_link("../../bancos/bancos_movi_saldos.php",array("idbanco"=>$arreglo_bancos[$i][idbanco]));
                                elseif($nombre=="Transferencias")
                               $link = encode_link("../bancos_listado_transferencias.php",array("cmd"=>"en_proceso"));


                    $onclick = "onclick=\"window.open('$link')\"";
                    $total = formato_money($arreglo_bancos[$i]["saldo"]);
                    echo "<tr ".atrib_tr().">
                          <td $onclick width=70% align=left>$nombre</td>
                          <td $onclick >\$</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";
                }
                ?>
               </table>
             </td>
          </tr>
       </table>
       </div>
       <!-- stock -->
       <div id=stock style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Stock</td></tr>
          <tr><td title="STOCK DE PRODUCCION: OC CUYO ID NO ESTE ENTREGADO Y QUE LA OC ESTA ENTREGADA">Stock + Stock Producción + Notas Créditos Pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
            <?
                for($i=0;$i<sizeof($stock_depositos);$i++){
                    $nombre=$stock_depositos[$i]["nombre"];
                    $total=formato_money($stock_depositos[$i]["total"]);
                    $moneda=$stock_depositos[$i]["moneda"];


                    switch ($nombre){
                        case "San Luis":
                                $link="../../stock/stock_san_luis.php";
                                break;
                        case "Produccion-San Luis":
                               $link="../../stock/stock_produccion_san_luis.php";
                                break;
                                
                        case "Buenos Aires":
                               $link="../../stock/stock_buenos_aires.php";
                                break;
                        case "Serv. Tec. Bs. As.":
                               $link="../../stock/stock_st_ba.php";
                                break;
                        case "RMA":
                               $link="../../stock/listar_rma.php";
                               break;
                        case "RMA-Produccion-San Luis":
                               $link="../../stock/listar_rma_san_luis.php";
                               break;
                               
                        case "Monitores RMA":
                               $link="../../casos/muletos_listado.php";
                               break;                               
                        case "Notas Créditos Pendientes":
                              $link="../../ord_compra/nota_credito_listar.php";
                              break;
                         case "Produccion":
                              //$link=encode_link("informe_balance.php",array("pagina"=>"stock_produccion","titulo"=>"Informe de Stock en Producción"));
                              $link="../../stock/stock_produccion.php";
                              break;
                         case "Muestras Dólares":
                               $link = "../../muestras/seguimiento_muestras.php";
                               break;
                         case "Stock Pendiente de Confirmación Pesos":
                               $link=encode_link("informe_balance.php",array("pagina"=>"stock_pendiente","titulo"=>"Stock Pendiente de Confirmación","id_moneda"=>1));
                              break;
                         case "Stock Pendiente de Confirmación Dolares":
                               $link=encode_link("informe_balance.php",array("pagina"=>"stock_pendiente","titulo"=>"Stock Pendiente de Confirmación","id_moneda"=>2));
                              break;
                         case "Stock Pendiente":
                               $link=encode_link("informe_balance.php",array("pagina"=>"stock_pendiente","titulo"=>"Stock Pendiente de Confirmación","id_moneda"=>2));
                              break;

                          default: $link="";
                                 $onclick="";
                         };

                    if ($link) $onclick=" onclick=\"window.open('$link')\"";
                    echo "<tr ".atrib_tr().">
                         <td width=70% align=left $onclick>$nombre</td>
                          <td $onclick>$moneda</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";
                }
             ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <!-- Bienes de Uso -->
       <div id=bienes_de_uso style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Bienes de Uso</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
                   $nombre=$array_bienes_de_uso["nombre"];
                   $moneda=$array_bienes_de_uso["moneda"];
                   $total=formato_money($array_bienes_de_uso["total"]);

                   $link="../../stock/inventario.html.php";
                   $onclick="onclick=\"window.open('$link')\"";
                    echo "<tr ".atrib_tr().">
                          <td width=70% align=left $onclick>$nombre</td>
                          <td $onclick>$moneda</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";


               ?>
            </table>
          </td>
          </tr>
       </table>
       </div>

       <!-- Adelantos -->

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
                for($i=0;$i<sizeof($array_adelantos);$i++){

                    $nombre=$array_adelantos[$i]["nombre"];
                    $total=formato_money($array_adelantos[$i]["total"]);
                    $moneda=$array_adelantos[$i]["moneda"];
                    $id_moneda=$array_adelantos[$i]["id_moneda"];

                    $link=encode_link("informe_balance.php",array("pagina"=>"oc parcialmente recibidas","titulo"=>"Adelantos","id_moneda"=>$id_moneda));
                    if ($link) $onclick=" onclick=\"window.open('$link')\"";
                    echo "<tr ".atrib_tr().">
                         <td width=70% align=left $onclick>$nombre</td>
                          <td $onclick>$moneda</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";
                }

             ?>
            </table>
          </td>
          </tr>
       </table>
       </div>

       <!-- cajas -->
       <div id=caja style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Cajas</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
                for($i=0;$i<sizeof($arreglo_cajas);$i++){
                    $nombre=$arreglo_cajas[$i]["caja"];
                    $total=formato_money($arreglo_cajas[$i]["total"]);
                    $moneda=$arreglo_cajas[$i]["moneda"];
                    if ($nombre=="Caja San Luis")
                              $link=encode_link(" ../../caja/listado.php",array("distrito"=>1,
                                                                                "select_moneda"=>$arreglo_cajas[$i]["id_moneda"]));
                       elseif ($nombre=="Caja Bs. As.")
                             $link=encode_link(" ../../caja/listado.php",array("distrito"=>2,
                                                                               "select_moneda"=>$arreglo_cajas[$i]["id_moneda"]));
                           elseif($nombre=="Caja De Seguridad")
                              $link=encode_link("../../caja/caja_seguridad.php",array("cmd"=>"exitentes"));

                    $onclick="onclick=\"window.open('$link')\"";
                    echo "<tr ".atrib_tr().">
                          <td width=70% align=left $onclick>$nombre</td>
                          <td align=center $onclick>$moneda</td>
                          <td align=right $onclick>&nbsp;$total</td>
                          </tr>";
                }
             ?>
            </table>
          </td>
          </tr>
       </table>
       </div>

       <!-- Depositos Pendientes -->
       <div id=depositos_pendientes style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Depósitos pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
                for($i=0;$i<sizeof($arreglo_depositos_pendientes);$i++){
                    $nombre=$arreglo_depositos_pendientes[$i]["nombre"];
                    $total=formato_money($arreglo_depositos_pendientes[$i]["total"]);
                    $idbanco=$arreglo_depositos_pendientes[$i]["idbanco"];
                    $link=encode_link("../bancos_movi_deppen.php",array("idbanco"=>$idbanco));
                    $onclick=" onclick=\"window.open('$link')\"";
                     echo "<tr ".atrib_tr().">
                          <td $onclick width=70% align=left>$nombre</td>
                          <td $onclick align=center>\$</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";
                }
             ?>
            </table>
          </td>
          </tr>
       </table>
       </div>
       <!-- cheques diferidos -->
       <div id=cheques_diferidos style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Cheques Diferidos Pendientes</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
               <?
                for($i=0;$i<sizeof($arreglo_cheques_diferidos);$i++){
                    $nombre=$arreglo_cheques_diferidos[$i]["nombre"];
                    $total=formato_money($arreglo_cheques_diferidos[$i]["total"]);
                    $id_banco=$arreglo_cheques_diferidos[$i]["id_banco"];
                    $link=encode_link("../ver_chequesdif_pend.php",array("id_banco"=>$id_banco));
                    $onclick=" onclick=\"window.open('$link')\"";
                    echo "<tr ".atrib_tr().">
                          <td $onclick width=70% align=left>$nombre</td>
                          <td $onclick align=center>\$</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";
                }
             ?>
            </table>
          </td>
          </tr>

       </table>
       </div>
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
                for($i=0;$i<sizeof($arreglo_cheques);$i++){
                    $nombre=$arreglo_cheques[$i]["nombre"];
                    $total=formato_money($arreglo_cheques[$i]["total"]);
                    $idbanco=$arreglo_cheques[$i]["idbanco"];
                    $link=encode_link("../bancos_movi_chpen.php",array("idbanco"=>$idbanco));
                    $onclick=" onclick=\"window.open('$link')\"";


                    echo "<tr ".atrib_tr().">
                          <td $onclick width=70% align=left>$nombre</td>
                          <td $onclick align=center>\$</td>
                          <td $onclick align=right>&nbsp;$total</td>
                          </tr>";
                }
             ?>
            </table>
          </td>
          </tr>


       </table>
       </div>
       <!-- deuda financiera -->
       <div id=deuda_financiera style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Deuda Financiera </td></tr>
          <?
          $link=encode_link("../facturas_venta.php",array("id_moneda"=>1));
          $onclick=" onclick=\"window.open('$link')\"";
          ?>
          <tr>
             <td width=100%>
               <table width=60% align=center>
                 <tr <?=atrib_tr();?>>
                   <td <?=$onclick?> width=70%>Deuda Financiera Pesos</td>
                   <td <?=$onclick?>>$</td>
                   <td <?=$onclick?> align=right><?=formato_money($deuda_financiera_pesos)?></td>
                 </tr>
          <?
          $link=encode_link("../facturas_venta.php",array("id_moneda"=>2));
          $onclick=" onclick=\"window.open('$link')\"";
          ?>
                 <tr <?=atrib_tr();?>>
                  <td <?=$onclick?> width=70%>Deuda Financiera Dólares</td>
                  <td <?=$onclick?>>u$s</td>
                  <td <?=$onclick?> align=right><?=formato_money($deuda_financiera_dolar)?></td>
                 </tr>
                </table>
             </td>
          </tr>

       </table>
       </div>
       <!-- deuda Comercial -->
       <div id=deuda_comercial style='display:none'>
       <table width=70% align=center  class='informacion_balance' >
          <tr id=mo><td>Deuda Comercial </td></tr>
          <tr><td>OC. que se recibieron los productos y no se pagaron (No incluye Ordenes Internacionales)</td></tr>
          <tr>
             <td width=100%>
               <table width=60% align=center>
          <?
          for($i=0;$i<count($array_deuda_comercial);$i++){
                   $link=encode_link("informe_balance.php",array("pagina"=>"deuda_comercial","titulo"=>"Informe De Deuda Comercial","id_moneda"=>2,"internacional"=>$array_deuda_comercial[$i]["internacional"]));
                   $onclick=" onclick=\"window.open('$link')\"";
                   $nombre=$array_deuda_comercial[$i]["nombre"];
                   $moneda=$array_deuda_comercial[$i]["moneda"];
                   $monto=$array_deuda_comercial[$i]["monto"];
               ?>
               <tr <?=atrib_tr()?>>
                   <td align=left <?=$onclick?>><?=$nombre?></td>
                   <td <?=$onclick?>><?=$moneda?></td>
                   <td <?=$onclick?> align=right><?=formato_money($monto)?></td>
                 </tr>
               <?
               }
               ?>


                </table>
             </td>

          </tr>          
       </table>
       </div>

</form>
<!--
<input type=button name=prueba value=foto onclick="window.open('../../../balance_fotos.php')">
-->
<?
//prueba
echo fin_pagina();
?>