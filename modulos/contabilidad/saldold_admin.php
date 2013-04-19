<?

require_once ("../../config.php");
require_once("funciones.php");

$id_saldold=$parametros["id_saldold"];

if ($_POST['guardar']=="Guardar Nuevo Saldo LD"){
   $db->StartTrans();
   $fecha_db=Fecha_db($_POST['fecha']);
   $monto=$_POST['monto'];
   $periodo=$_POST['periodo'];

    $q="select nextval('saldold_id_saldold_seq') as id_saldold";
    $result=sql($q) or fin_pagina();
    $id_saldold=$result->fields['id_saldold'];

    $query="insert into contabilidad.saldold
             (id_saldold, fecha, periodo, monto)
             values
             ($id_saldold, '$fecha_db', '$periodo', '$monto')";

    sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    
    $accion="Los datos del Saldo LD $id_saldold se Guardaron con Exito";
	
    $db->CompleteTrans();

    $link=encode_link('saldold_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}//de if ($_POST['guardar']=="Guardar Nuevo Saldo LD")

if ($_POST['guardar']=="Guardar"){
   $db->StartTrans();
   $id_saldold=$_POST['id_saldold'];
   $fecha_db=Fecha_db($_POST['fecha']);
   $monto=$_POST['monto'];
   $periodo=$_POST['periodo'];

    $query="update contabilidad.saldold set 
            id_saldold=$id_saldold, fecha='$fecha_db', periodo='$periodo', monto='$monto' where id_saldold=$id_saldold ";
    sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    
    $accion="Los datos del Saldo LD $id_saldold se Actualizaron con Exito";
	
    $db->CompleteTrans();

    $link=encode_link('saldold_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}//de if ($_POST['guardar']=="Guardar")

if ($id_saldold) {
$sql="select * from contabilidad.saldold where id_saldold=$id_saldold";
$result_edit=sql($sql, "Error al traer los datos del caso") or fin_pagina();

$fecha=$result_edit->fields['fecha'];
$periodo=$result_edit->fields['periodo'];
$monto=$result_edit->fields['monto'];

}

echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 if(document.all.fecha.value=="")
 {alert('Debe ingresar una Fecha');
  return false;
 }
 if(document.all.periodo.value=="")
 {alert('Debe ingresar un Periodo');
  return false;
 }
 if((document.all.monto.value=="")||(document.all.monto.value=="0.00"))
 {alert('Debe ingresar un Monto');
  return false;
 }
 
 return true;
}//de function control_nuevos()

</script>

<form name='form1' action='saldold_admin.php' method='POST'>
<input type="hidden" name="id_saldold" value="<?=$id_saldold?>">
<br>
<table width=70% align="center" class="bordes" bgcolor=<?=$bgcolor2?>>
 <tr>
    <td bgcolor="<?=$bgcolor3?>" align=center id=mo>
    <?
    if (!$id_saldold) {
    ?>
     <font size=+1><b> Nuevo Saldo de Libre Disponibilidad de la AFIP</b></font>
    <? }
        else {
    ?>
      <font size=+1><b>Editar Saldo de Libre Disponibilidad de la AFIP</b></font>
    <? } ?>
    </td>
 </tr>
 <tr align="center">
 	<td>
 		<?cargar_calendario();?>
 		<br><b>Fecha: </b><input type='text' name='fecha' value='<?=fecha($fecha);?>' size=26 readonly>&nbsp;&nbsp;<?=link_calendario("fecha")?>
 	</td>
 </tr>
 <tr align="center">
 	<td><br><b>Monto: </b><input type='text' name='monto' value='<?=number_format($monto, 2, '.', '');?>' size=30></td>
 </tr>
 
 <tr align="center"><td><br><b>Periodo: </b></td></tr>
 <tr align="center"><td><textarea cols='50' rows='10' name='periodo'><?=$periodo?></textarea></td></tr>
    
<?
if ($id_saldold){
	$value_button="Guardar";
}
else{
	$value_button="Guardar Nuevo Saldo LD";
}
?>
<tr align="center">
  <td align="center" colspan="2">
   <table width=95% align="center">
    <tr align="center">
     <td align="center"><input type='submit' name='guardar' value='<?=$value_button?>' onclick="control_numero(monto,'Monto'); return control_nuevos()"
         title="Guardar Datos de Saldo LD"></td>
    </tr>
  </table>
 </td>
</tr>

<table align="center"><br>
 <tr align="center">
    <td align="center">
     <center>
      <input type=button name="volver" value="Volver" onclick="document.location='saldold_listado.php'"
        title="Volver al Listado de Saldo LD">
     </center>
    </td>
 </tr> 
</table> 
</form>
<?=fin_pagina();// aca termino ?>
