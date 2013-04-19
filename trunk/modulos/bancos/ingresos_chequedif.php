<?php
/*
$Author: mari $
$Revision: 1.6 $
$Date: 2006/11/27 18:24:23 $
*/


require_once("../../config.php");
variables_form_busqueda("ingreso_diferido");
require_once("../ord_compra/fns.php");
require_once("../caja/func.php");

$id_chequedif = $parametros['id_chequedif'] or $id_chequedif = $_POST['id_chequedif'];

if($_POST['Guardar']=="Guardar") {
	$pagina="";
	$id=$_POST["id_ingreso_egreso"];
	if($id)
	 $state_guardar=1;

	 $stay=0;//se modifica dentro de guardar_ie
	 
	 $distrito=$_POST['distrito'];

	 $state_guardar=guardar_ie("ingreso",$distrito);
	 
	 $db->StartTrans();
	 
	 $sql="select max(id_ingreso_egreso) from ingreso_egreso";
     $result_ingreso_egreso=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

     $sql = "update cheques_diferidos set id_ingreso_egreso=".$result_ingreso_egreso->fields['max']." where id_chequedif=$id_chequedif";
     $db->query($sql) or die($db->ErrorMsg()."<br>".$sql); 
     
     $db->CompleteTrans(); 
     
     include_once("ver_chequesdif_pend.php");
     die();
}

$pagina_viene=$parametros['pagina_viene'] or $pagina_viene=$_POST['pagina_viene'];
$id_sueldo=$parametros['id_sueldo'] or $id_sueldo=$_POST['id_sueldo'];
$id_cobranza=$parametros['id_cobranza'] or $id_cobranza=$_POST['id_cobranza'];
$id_cob=$parametros['id_cob'] or $id_cob=$_POST['id_cob'];
$nro_factura=$parametros['nro_factura'] or $nro_factura=$_POST['nro_factura'];
$id_licitacion=$parametros['id_licitacion'] or $id_licitacion=$_POST['id_licitacion'];
$cotizacion_dolar=$parametros['cotizacion_dolar'] or $cotizacion_dolar=$_POST['cotizacion_dolar'];
$dolar_actual=$parametros['dolar_actual'] or $dolar_actual=$_POST['dolar_actual'];

$cmd="ingresos";
phpss_svars_set("_ses_ingreso_diferido_cmd", $cmd);



$in="egreso";
$link=encode_link("ingresos_chequedif.php",array("id_ingreso_egreso"=> $parametros["id_ingreso_egreso"],"pagina"=>$in,"cuotas"=>$parametros['cuotas'],
	                  "cuentas"=>$parametros['cuentas'],"cantidad_cuentas"=>$parametros['cantidad_cuentas'],"distrito"=>$distrito));

if ($ie=='ingreso') echo "<table align='center' width='80%' height='80%' id=mo>";
if ($ie=='egreso') echo "<table align='center' width='80%'  height='80%' bgcolor='$color'>";
echo "<input type=hidden name='filtro' value=''";
?>
<tr>
<td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';if(document.all.editar.value=='ok')document.all.postear.value='sipok';else document.all.postear.value='sip'; document.form1.submit();"><?=$letra?></td>
</tr>
</table>
<div align="right">
  <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/caja/ayuda_ing_eg.htm" ?>', 'INGRESO/EGRESO <?=strtoupper(CAJA)?>')"; >
</div>

<?

include("../ayuda/ayudas.php");


$sql_f="SELECT id_tipo_ingreso FROM caja.tipo_ingreso where nombre='Venta Financiada'"; 
$res_f=sql($sql_f) or fin_pagina();
$tipo_ingreso=$res_f->fields['id_tipo_ingreso'];
$fecha = date("Y-m-d");
$moneda =1; //Pesos
$tipo_cuenta = 5; //Venta Financiada (Cheques Posdatados por el cliente)

$sql = "select cheques_diferidos.id_entidad,cheques_diferidos.nro_cheque,cheques_diferidos.monto,entidad.nombre,
        cheques_diferidos.comentario from cheques_diferidos left join entidad using(id_entidad) where 
        cheques_diferidos.id_chequedif=$id_chequedif";

$resultado_cheque = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);


$nro_cheque=$resultado_cheque->fields['nro_cheque'];
$monto=$resultado_cheque->fields['monto'];
$entidad=$resultado_cheque->fields['nombre'];
$item = $resultado_cheque->fields['comentario'];
$id_entidad = $resultado_cheque->fields['id_entidad'];






echo $html_header;
?>

<body bgcolor="#E0E0E0">
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/funciones.js"></script>
<SCRIPT>
//Este script aparece unicamente si la moneda es dolares
//si es pesos no puede elegir entre las monedas


function conversion_monedas(importe,valor_dolar){
	//cero=pesos uno=dolares
	var moneda=(parseFloat(document.all.valor_dolar.value)==0)?0:1;
	
	//si selecciona pesos y la moneda es dolares
	if (document.all.select_moneda.value==1) {
		//si la moneda original es dolares
		if (moneda)
			importe.value=document.all.importe_dolares.value;
		else
			importe.value=document.all.importe_pesos.value;
		
  }//sino si selecciona dolares
  else if(document.all.select_moneda.value==2)
  {
		//si la moneda original es pesos
		if (moneda==0)
			importe.value=0;
		else
			importe.value=document.all.importe_pesos.value;
  }
}

function pesos_a_dolares(importe,valor_dolar){
importe.value=paseFloat(importe.value) / parseFloat(valor_dolar);
}


function control_campos()
{

 if(document.all.select_tipo.value==-1)
 {alert('Debe seleccionar un tipo de <?if($cmd=="egresos")echo "egreso";elseif($cmd=="ingresos")echo "ingreso";?>');
  return false;
 }
 if(document.all.text_fecha.value=="")
 {alert('Debe ingresar una fecha valida');
  return false;
 }
  else if ( (typeof(document.all.text_fecha.value)!='undefined') && (!es_mayor('text_fecha'))) {
  alert('Los ingresos o egresos deben tener fecha mayor o igual a la fecha de hoy');
  return false;
 }
 
 
 if(document.all.text_monto.value=="")
 {alert('Debe ingresar un monto valido');
  return false;
 }

 if(document.all.select_moneda.value==-1)
 {alert('Debe seleccionar una moneda');
  return false;
 }
 if(document.all.text_item.value=="")
 {alert('Debe ingresar un item');
  return false;
 }
return true;
}

//se usa en la parte de egresos y según el proveedor elegido, si tiene
//cuenta por default, setea el select de cuentas, con la cuenta por
//default del proveedor
</SCRIPT>


<form name="form1" method="POST" action="<?=$link?>">
<input type="hidden" name="id_chequedif" value="<?=$id_chequedif?>">
<input type='hidden' name='editar' value='<?if(($parametros['id_ingreso_egreso']!="" || $_POST["postear"]=="sipok")&& $state_guardar==1) echo "ok";?>'>
<input type="hidden" name="id_ingreso_egreso" value="<?=$id?>">
<input type="hidden" name="valor_dolar" value="<?=$valor_dolar?>">
<input type="hidden" name="importe_pesos" value="<?=$importe_pesos?>">
<input type="hidden" name="importe_dolares" value="<?=$importe_dolares?>">
<?
if ($_POST["moneda"]) $value=$_POST["moneda"];
					  else $value=0;
?>
<input type="hidden" name="moneda"  value="<?=$value?>">

<?php


if (($_ses_user['login']=='noelia') || (($_ses_user['login']=='juanmanuel')) || (($_ses_user['login']=='diego')))
{
 define("CAJA","Bs. As. / San Luis");
 $nbre_distrito="Bs. As. / San Luis";
}
else
{
 define("CAJA","Buenos Aires");
 $nbre_distrito="Buenos Aires - GCBA";
}

$titulo_principal="INGRESOS CHEQUES DIFERIDOS - CAJA ".strtoupper(CAJA);
$param1="INGRESO";
$param2="CLIENTES";
$param3=$bgcolor1;


echo "<br>";
 show_titulo($titulo_principal,"black");
echo "<hr>";
// echo show_encabezado("INFORMACIÓN DEL $param1","$param2","$param3");
 ?>


<?

if (($_ses_user['login']=='noelia') || (($_ses_user['login']=='juanmanuel')) || (($_ses_user['login']=='diego')))
{
 define("CAJA","Bs. As. / San Luis");
 $nbre_distrito="Bs. As. / San Luis";
?>
<table class="bordes" align="center"> 
 <tr id=mo> <td colspan="2"> SELECCIONE DISTRITO </td></tr>
 <tr bgcolor=<?=$bgcolor_out?>>
 <td><input type="radio" name="distrito" value=1 checked> </td>
  <td> CAJA SAN LUIS </td>
 </tr>
 <tr bgcolor=<?=$bgcolor_out?>>
  <td><input type="radio" name="distrito" value=2 > </td>
  <td> CAJA BS AS </td>
 </tr>
</table>
 <?
}
else //gente de Bs. As.
{
?>
<input type="hidden" name="distrito" value="2">
<?
}
?>
 
<table width='100%'>
<tr> 
<td width='60%' valign="center">
&nbsp;<b>Nro Cheque</b> &nbsp;<input type="text" name="nro_cheque" value="<?=$nro_cheque?>" size="15" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" readonly>
</td>
</tr> 
<tr>
<td>
<?
if ($id_entidad=="")
 {
  $sql = "select entidad.nombre, entidad.id_entidad from cheques_diferidos join cheque_cobranza using(id_chequedif) join cobranzas using (id_cobranza) join entidad on cobranzas.id_entidad = entidad.id_entidad where cheques_diferidos.id_chequedif = $id_chequedif";
  $resultado_entidad = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
  $id_entidad = $resultado_entidad->fields['id_entidad'];
  $entidad = $resultado_entidad->fields['nombre'];
 }
?>
&nbsp;<b>Entidad</b> &nbsp;<input type="text" name="entidad" value="<?=$entidad?>" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" readonly>
<input type="hidden" name="select_entidad" value="<?=$id_entidad?>">
</td>
</tr>
<tr>
<td>
<?show_datos("ingreso");?>
</td>
</tr>
<tr>
 <td align='center' bgcolor='#BA0105'><font color='White'><b>Tipo de Cuenta</b></font></td>
</tr> 
<tr> 
 <td>
<?
$sql="select * from caja.tipo_cuenta_ingreso";
$resultado_cuenta=$db->execute($sql) or die($sql);

echo "<select name='select_cuenta'>
       <option value=-1> Seleccione un Tipo de Cuentas </option>";
      while (!$resultado_cuenta->EOF)
      {echo"<option value=".$resultado_cuenta->fields['id_cuenta_ingreso'];
			if(valores("cheques diferidos","select_cuenta")==$resultado_cuenta->fields['id_cuenta_ingreso'])
					 {
					 echo " selected ";
					 }
		   echo">".$resultado_cuenta->fields['nombre']."</option>";
           $resultado_cuenta->MoveNext();
      }
echo "</select></td></tr>";   
?>
</table>
<br>
<?
//permisos_check("inicio","modificar_ing_egr_cobranzas")
//este permiso permite modificar los egresos/ingresos que se hicieron desde cobranzas
if ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='egreso') {
	$id_ing_det=$parametros['id_ingreso_egreso'];
$sql=" select id_ingreso_egreso from detalle_egresos where id_ingreso_egreso=$id_ing_det";
$res=sql($sql) or fin_pagina(); 

if ($res->RecordCount()>0 && !(permisos_check("inicio","modificar_ing_egr_cobranzas"))) $des_guardar= " disabled" ;
  else $des_guardar= "";
} elseif ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='ingreso') {
	$id_ing_det=$parametros['id_ingreso_egreso'];
	$sql=" select id_ingreso_egreso from cobranzas where id_ingreso_egreso=$id_ing_det";
    $res=sql($sql) or fin_pagina(); 
    if ($res->RecordCount()>0 && !(permisos_check("inicio","modificar_ing_egr_cobranzas"))) $des_guardar= " disabled" ;
       else $des_guardar= "";
}
?>
<hr>
<center>
 <input type="hidden" name="postear" value="">
 <input type="hidden" name="forcesave" value="<?=$forcesave?>">
 <input type="submit" name="Guardar" value="Guardar" style='cursor:hand' title='Presione aqui para guardar los cambios efectuados' onclick='if (control_campos()){if (typeof(document.all.idbanco)!="undefined" && document.all.idbanco.value!="") alert ("Se hará el ingreso automaticamente en el Banco elegido"); return true} else return false;' <?= $des_guardar?>>
 <input type="hidden" name="pagina_viene" value="<?=$pagina_viene?>">
 <input type="hidden" name="id_sueldo" value="<?=$id_sueldo?>">
</center>
</form>
<?=fin_pagina();?>