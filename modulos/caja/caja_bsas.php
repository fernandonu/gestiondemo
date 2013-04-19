<?php
/*AUTOR: mari

MODIFICADO POR:
$Author: cesar $
$Revision: 1.15 $
$Date: 2004/08/07 14:28:43 $
*/

include("func.php");

//********   INICIALIZACION DE VARIABLES:    *************

//if(($_POST['Ver']=="Ver Caja")||($_POST['Cerrar']=='Cerrar Caja')){
if($_POST['Cerrar']=='Cerrar Caja') $valor="post";
if($_POST['Nuevo']=='Nuevo') $valor="vacio";
if($pagina == "listado") $valor="base";
$distrito_nombre='Buenos Aires - GCBA';

$query_distrito="SELECT id_distrito,nombre FROM licitaciones.distrito WHERE distrito.nombre='$distrito_nombre'";
$resultados_distrito=$db->Execute($query_distrito) or die ($db->ErrorMsg().$query_distrito);
$distrito=$resultados_distrito->fields['id_distrito'];

$fecha = $_POST['text_fecha'] or $fecha=$parametros['fecha'];
$moneda=$_POST['chk_moneda'] or $moneda=$parametros['moneda'];
//echo "moneda: ".$moneda;
if($moneda=='pesos') $moneda_guardar=1;
if($moneda=='dolares') $moneda_guardar=2;

if($moneda_guardar!=''){
  $query_caja="SELECT * from caja.caja JOIN licitaciones.moneda  using(id_moneda) JOIN licitaciones.  distrito using(id_distrito) WHERE moneda.id_moneda=$moneda_guardar AND distrito.id_distrito=$distrito AND caja.fecha='".fecha_db($fecha)."'";
  $resultados_caja=$db->Execute($query_caja) or die ($db->ErrorMsg().$query_caja);
  $id_caja=$resultados_caja->fields['id_caja'];
}  elseif ($parametros['pagina']=='listado')
          { //en caso de que el id de caja venga por parametro
          $id_caja=$parametros['id_caja'];

          $query_caja="SELECT * from caja WHERE id_caja=$id_caja";
          $resultados_caja=$db->Execute($query_caja) or die ($db->ErrorMsg().$query_caja);
          $moneda_guardar=$resultados_caja->fields['id_moneda'];
          $distrito=$resultados_caja->fields['id_distrito'];
          $fecha=fecha($resultados_caja->fields['fecha']);

         }

// ********** fin de inicializacion de variables. **************


if($resultados_caja->fields['cerrada']==1) {
echo "<br>";
echo "<table width='100%' id=ma>";
echo "<tr>";
echo "<td>Estado: Caja Cerrada </td>";
echo "<td>Usuario:".$resultados_caja->fields['usuario']." </td>";
$fecha_cerrada=$resultados_caja->fields['usuario_fecha'];
$fecha_cerrada_hora=split(" ",$fecha_cerrada);
echo "<td>Fecha:".fecha($fecha_cerrada_hora[0])." ".$fecha_cerrada_hora[1]." </td>";
echo "</tr>";
echo "</table>";
 }

//suma de los ingresos del dia     ok
if($moneda_guardar!='') {
 $query_ingresos="SELECT sum(ingreso_egreso.monto) as ingreso FROM ingreso_egreso JOIN caja using(id_caja) JOIN distrito using(id_distrito) JOIN moneda using(id_moneda) WHERE caja.fecha = '".fecha_db($fecha)."' AND ingreso_egreso.id_tipo_egreso isnull AND distrito.id_distrito=$distrito AND moneda.id_moneda=$moneda_guardar";
 $resultados_ingresos=$db->Execute($query_ingresos) or die ($db->ErrorMsg().$query_ingresos);
 $total_ingresos=$resultados_ingresos->fields['ingreso'];
// echo $query_ingresos."<br>";
//suma de los egresos del dia      ok
 $query_egresos="SELECT sum(ingreso_egreso.monto) as egreso FROM ingreso_egreso JOIN caja using(id_caja) JOIN distrito using(id_distrito) JOIN moneda using(id_moneda) WHERE caja.fecha = '".fecha_db($fecha)."' AND ingreso_egreso.id_tipo_ingreso isnull AND distrito.id_distrito=$distrito AND moneda.id_moneda=$moneda_guardar";
 $resultados_egresos=$db->Execute($query_egresos) or die ($db->ErrorMsg().$query_egresos);
 $total_egresos=$resultados_egresos->fields['egreso'];
//echo $query_egresos."<br>";
//query para traer la caja anterior:

 $fecha_anterior=dia_habil_anterior($fecha);
 $caja_anterior="SELECT * FROM caja.caja WHERE fecha='".fecha_db($fecha_anterior)."'
  and id_moneda=$moneda_guardar and id_distrito=$distrito";
 $resultado_caja_anterior=$db->Execute($caja_anterior) or die ($db->ErrorMsg().$caja_anterior);
 $cantidad=$resultado_caja_anterior->RecordCount();
// echo $caja_anterior;
 $caja_cerrada=$resultado_caja_anterior->fields['cerrada'];
  if($cantidad==0) $saldo_anterior=0;
  else
  $saldo_anterior=$resultado_caja_anterior->fields['saldo_total'];
  //echo "saldo anterior: ".$saldo_anterior;
  $saldo_actual=($saldo_anterior+$total_ingresos)-$total_egresos;
  $fecha_creacion = date("Y-m-d H:m:s",mktime());
//}
 //update para caja.
}
 if($_POST['Cerrar']=="Cerrar Caja") {
  $fechaA=$fecha;
  $fecha_total=split("/",$fechaA);
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
  $fecha_total=split("/",$fecha_aux);
 if($fecha_total[3]!=0 && !feriado($fecha))
 {if($caja_cerrada) {
   if($resultados_caja->fields['cerrada']==0){
   $control_caja="SELECT * from caja.caja WHERE id_moneda=$moneda_guardar AND id_distrito=$distrito
   AND fecha='".fecha_db($fecha)."'";
   $resultados_control_caja=$db->Execute($control_caja) or die($db->ErrorMsg."<br>".$control_caja);
   $cantidad_control_caja=$resultados_control_caja->RecordCount();
   if($cantidad_control_caja==0) {
        $nueva_caja="INSERT INTO caja.caja (id_moneda,id_distrito,fecha,saldo_total,usuario,usuario_fecha,cerrada)
        values ($moneda_guardar,$distrito,'".fecha_db($fecha)."',$saldo_actual,'$_ses_user_name','$fecha_creacion',1)";
        $db->Execute($nueva_caja) or die($db->ErrorMsg().$nueva_caja);
        $query_set_id_caja="SELECT max(id_caja) as id_caja FROM caja.caja WHERE id_distrito=$distrito AND fecha='".fecha_db($fecha)."'
        AND id_moneda='$moneda_guardar'";
        $resultado_set_id_caja=$db->Execute($query_set_id_caja) or die($db->ErrorMsg().$query_set_id_caja);
        $id_caja=$resultado_set_id_caja->fields['id_caja'];
   }
   else {
           $cerrar_caja="UPDATE caja.caja SET usuario='$_ses_user_name', saldo_total=$saldo_actual,usuario_fecha='$fecha_creacion',cerrada=1  WHERE id_caja=$id_caja";
          $db->Execute($cerrar_caja) or die($db->ErrorMsg().$cerrar_caja);
        }
   $link=encode_link("caja_bsas.php",array("id_caja"=> $id_caja,"pagina"=>"listado"));
   header("Location:$link");
   }
  }
  else
  {
   put_status("",9,"black"); $ctrl=1;
  }
 }
 else
  put_status("",10,"black"); $ctrl=1;
//echo $cerrar_caja;
}

echo $html_header;
?>
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/funciones.js"></script>
<hr>

<script languaje='javascript'>
function fecha_value() {
  if(document.all.text_fecha.value=='') {
                                        alert("Debe ingresar una fecha valida");
                                        return false;
                                        }
  else
          return true;
}
</script>

<center>

<form name="form1" method="post" action="caja_bsas.php">
<? 
 if ($moneda =="" && $moneda_guardar==1) $moneda= 'pesos';
 elseif ($moneda =="" && $moneda_guardar==2) $moneda= 'dolares';
 
 $fe_ant=dia_habil_anterior($fecha);
 $fe_post=dia_habil_posterior($fecha);
 $link_post=encode_link("caja_bsas.php",Array('link'=>'poste','fecha'=>$fe_post,'moneda'=>$moneda));	 
 $link_ant=encode_link("caja_bsas.php",Array('link'=>'ant','fecha'=>$fe_ant,'moneda'=>$moneda));  

 ?>
 <table  id=ma class="bordes">
<tr>
<? if (($_POST['Ver'] == 'Ver Caja') || ($parametros['pagina']=='listado') || $parametros['link'] || $ctrl==1) {?> 
 <td width="44" align="left"> <a title="ver caja dia <?=$fe_ant?>" href='<?=$link_ant?>'> << </a> </td>
 <? } 
 else 
 echo "<td>&nbsp;</td>" ;?>
<td width="615">
<TABLE width='97%' id=ma>
 <tr>
  <td width="13%"> Moneda:</td>
    <td width="14%"><input type='radio' name='chk_moneda' value='pesos' <?if ($_POST['chk_moneda'] == 'pesos') echo 'checked'; elseif(($parametros['pagina']=='listado')&&($moneda_guardar==1))echo 'checked'; elseif ($parametros ['link'] && $moneda_guardar== 1) echo 'checked';else echo 'checked'?>> Pesos </td>
    <td width="17%"><input type='radio' name='chk_moneda' value='dolares' <?if ($_POST['chk_moneda'] == 'dolares') echo 'checked'; elseif(($parametros['pagina']=='listado')&&($moneda_guardar==2))echo 'checked'; elseif ($parametros ['link'] && $moneda_guardar == 2) echo 'checked'?>> Dólares </td>
    <td width="9%">Fecha: </td> <td width="27%"> <input type='text' name='text_fecha'
<?
if($parametros['pagina']=='listado') $valor=$fecha;
 elseif ($parametros['link']) $valor=$parametros['fecha'];
 else $valor=$_POST['text_fecha'];
   //control de parametro
?>
    value='<?=$valor?>'>
    <td width="5%"><?echo link_calendario('text_fecha');?> </td>
    <td width="15%"> <input type='submit' name='Ver' value='Ver Caja' style='cursor:hand'
    onclick="return fecha_value();"></td>

 </tr>
</table>
</td>
<? if (($_POST['Ver'] == 'Ver Caja') || ($parametros['pagina']=='listado') || $parametros['link'] || $ctrl==1) {?> 
<td width="32" align="right"> <a title="ver caja dia <?=$fe_post?>" href='<?=$link_post?>'> >> </a></td>
<? } 
 else 
 echo "<td>&nbsp;</td>" ;?>
</tr>
</table>
<br>
<TABLE width='50%' align='center' cellspacing='2' cellpadding='2' class="bordes">
  <tr id=mo>
      <td width='50%' align='center'><strong>
      Informacion de la caja
      </strong>
      </td>
      <td width='40%' height='20' align='center' ><strong>
      Valores
     </strong> </td>
  </tr>
<!--</table>

<table width='50%'>-->
 <tr  bgcolor=<?=$bgcolor_out?>>
  <td align='left' width='50%'>Saldo Anterior: </td>
  <td width='40%'><input type='text' name='text_saldo' value='<?=$saldo_anterior?>' readonly></td>
 </tr>
 <tr  bgcolor=<?=$bgcolor_out?>>
  <td align='left'>Ingresos: </td>
  <td><input type='text' name='text_ingresos' value='<?=$total_ingresos ?>' readonly></td>
 </tr>
 <tr  bgcolor=<?=$bgcolor_out?>>
  <td align='left'>Egresos: </td>
  <td><input type='text' name='text_egresos' value='<?=$total_egresos ?>' readonly></td>
 </tr>
 <tr  bgcolor=<?=$bgcolor_out?>>
  <td align='left'>Saldo Actual: </td>
  <td><input type='text' name='text_saldo_actual' value='<?=$saldo_actual?>' readonly></td>
 </tr>
</table>

<br>
   <input type='submit' name='Cerrar' value='Cerrar Caja' style="cursor:hand" title='Presione aqui para cerrar la caja' onclick="return confirm('¿Está seguro que desea cerrar la caja del día '+document.all.text_fecha.value+'?')" <?if($resultados_caja->fields['cerrada']==1) echo 'disabled'?>>
</center>
<hr>
<? if (($_POST['Ver'] == 'Ver Caja') || ($parametros['pagina']=='listado') || $parametros['link'] || $ctrl==1)   {
$fech=fecha_db($fecha);
//recupero ingresos del dia
$sql_ingresos="select caja.fecha,ingreso_egreso.id_ingreso_egreso,ingreso_egreso.item,ingreso_egreso.monto,
ingreso_egreso.comentarios,moneda.simbolo,entidad.nombre as nombre from caja.caja join 
caja.ingreso_egreso using(id_caja) join licitaciones.entidad using(id_entidad) join licitaciones.moneda 
using(id_moneda) 
where caja.id_distrito=$distrito and id_tipo_egreso isnull and id_moneda=$moneda_guardar and caja.fecha='$fech'";
$res_i=$db->Execute($sql_ingresos) or die ($db->ErrorMsg().$sql_ingresos);
$cant_i=$res_i->RecordCount();

//recupero egresos del dia
$sql_egresos="select caja.fecha,ingreso_egreso.id_ingreso_egreso,ingreso_egreso.item,ingreso_egreso.monto,
ingreso_egreso.comentarios,moneda.simbolo,proveedor.razon_social as nombre 
from caja.caja join caja.ingreso_egreso using(id_caja) join general.proveedor using(id_proveedor) join 
licitaciones.moneda using(id_moneda) 
where caja.id_distrito=$distrito and id_tipo_ingreso isnull and id_moneda=$moneda_guardar and caja.fecha='$fech'";
$res_e=$db->Execute($sql_egresos) or die ($db->ErrorMsg().$sql_egresos);
$cant_e=$res_e->RecordCount();

?>
<!--MUESTRA INGRESOS -->
<? if ($cant_i > 0) { ?>
<table width=95% cellspacing='0' align="center"><tr id=ma>
	<td align=left><b>
	Lista de Ingresos para la caja del día <?=Fecha($res_i->fields['fecha']) ?>
    </td>
	<td align="right"> <font color="#CC0000">Total Ingresos <?=$res_i->fields['simbolo']." ".formato_money($total_ingresos) ?></font></td>
	</tr></table>
<table width='95%' border='0' cellspacing='2' align="center">
<tr id=mo>
<td width='45%'><b>Item</b></td>
<td width='15%'><b>Monto</b></td>
<td width='25%'><b>Cliente</b></td>
</tr>

<? $cnr=1;
while (!$res_i->EOF) {
if ($cnr==1)
  {$atrib ="bgcolor='$bgcolor1'";
   $cnr=0;
  }
  else
  {$atrib ="bgcolor='$bgcolor2'";
   $cnr=1;
  }
 ?>
<tr <?=$atrib?>>
<td align="center"><?=$res_i->fields['item'] ?></td>
<td align="center"><?=$res_i->fields['simbolo']." ".formato_money($res_i->fields['monto']) ?></td>
<td align="center"><?=$res_i->fields['nombre'] ?></td>
</tr>
<? $res_i->MoveNext ();} ?>
</table>
<? } //fin $cant_i?>
<br>
<!--MUESTRA EGRESOS -->
<? if ($cant_e > 0) { ?>
<table width=95% cellspacing='0' align="center"><tr id=ma>
	<td  align=left><b>
	Lista de Egresos para la caja del día <?=fecha($res_e->fields['fecha'])?>
	</td>
	<td align="right"><font color="#CC0000">Total Egresos <?=$res_e->fields['simbolo']." ".formato_money($total_egresos)?></font></td>
	</tr></table>
<table width='95%' border='0' cellspacing='2' align="center">
<tr id=mo>
<td width='45%'><b>Item</b></td>
<td width='15%'><b>Monto</b></td>
<td width='25%'><b>Proveedor</b></td>
</tr>
<? $cnr=1;
 while (!$res_e->EOF) {
 if ($cnr==1)
  {$atrib ="bgcolor='$bgcolor1'";
   $cnr=0;
  }
  else
  {$atrib ="bgcolor='$bgcolor2'";
   $cnr=1;
  }
 ?>
<tr <?=$atrib?>>
<td align="center"><?=$res_e->fields['item'] ?></td>
<td align="center"><?= $res_e->fields['simbolo']." ".formato_money($res_e->fields['monto'])?></td>
<td align="center"><?=$res_e->fields['nombre'] ?></td>
</tr>
<? $res_e->MoveNext ();} ?>
</table>
<?
 $res_e->MoveNext ();} 
 }?>


</form>
</body>
</html>
