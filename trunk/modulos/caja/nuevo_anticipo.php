<?php
/*
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2005/05/04 18:37:47 $
*/

require_once("../../config.php");
$id_anticipo=$parametros["id_anticipo"] or $id_anticipo=$_POST["id_anticipo"];

if ($_POST["guardar"]=="Guardar") {
	$query="select nextval('anticipo_id_anticipo_seq') as id_anticipo";
	$res=sql($query) or fin_pagina();
	$id_anticipo=$res->fields["id_anticipo"];
	if (!FechaOk($_POST["fecha_entrega"])) $error="Debe ingresar la Fecha de Entrega.";
	if (!$_POST["monto"] or !es_numero($_POST["monto"])) $error="Debe ingresar el monto del anticipo.";
	$sql="INSERT INTO anticipo (id_anticipo,id_usuario,id_distrito,id_moneda,fecha_entrega,monto,comentario,estado) VALUES 
		($id_anticipo,".$_POST["sujeto"].",".$_POST["distrito"].",".$_POST["moneda"].",'".fecha_db($_POST["fecha_entrega"])."',".$_POST["monto"].",'".$_POST["comentario"]."','pendiente')";
	if (!$error) {
		sql($sql) or fin_pagina();
		aviso("Los datos se agregaron correctamente.");
	}
	else {
		$id_anticipo="";
		error($error);
		$id_usuario=$_POST["sujeto"];
		$id_distrito=$_POST["distrito"];
		$id_moneda=$_POST["moneda"];
		$fecha_entrega=$_POST["fecha_entrega"];
		$monto=$_POST["monto"];
		$comentario=$_POST["comentario"];
		$devuelve=$result->fields["devuelve"];
	}
}

if ($_POST["guardar"]=="Modificar") {
	if (!FechaOk($_POST["fecha_entrega"])) $error="Debe ingresar la Fecha de Entrega.";
	if (!$_POST["monto"] or !es_numero($_POST["monto"])) $error="Debe ingresar el monto del anticipo.";
	$sql="UPDATE anticipo SET comentario='".$_POST["comentario"]."' where id_anticipo=$id_anticipo";  
	
	if (!$error) {
		sql($sql) or fin_pagina();
		aviso("Los datos se modificaron correctamente.");
	}
	else {
		error($error);
	}
}

if ($id_anticipo) {
	//define("MODIFICAR",1);
	$sql="select id_usuario,id_distrito,id_moneda,fecha_entrega,monto,comentario,devuelve from anticipo where id_anticipo=$id_anticipo";
	$result=sql($sql)or fin_pagina();
	$id_usuario=$result->fields["id_usuario"];
	$id_distrito=$result->fields["id_distrito"];
	$id_moneda=$result->fields["id_moneda"];
	$fecha_entrega=fecha($result->fields["fecha_entrega"]);
	$monto=$result->fields["monto"];
	$devuelve=$result->fields["devuelve"];
	$comentario=$result->fields["comentario"];
}
if ($_POST["rendir"]=="Rendir") {
	// sacar la caja
	include ("func.php");
	if (!$_POST["devuelve"]) $devuelve="NULL";
	else $devuelve=$_POST["devuelve"];
	$sql="UPDATE anticipo SET devuelve=$devuelve WHERE id_anticipo=$id_anticipo"; 
	sql($sql) or fin_pagina();
	
	$i=1;
	$_POST["select_moneda"]=$id_moneda;
	while ($i<=$_POST["items_cant"]) {
		$_POST["select_proveedor"]=$_POST["id_proveedor_$i"];
		$_POST['select_tipo']=$_POST["tipo_$i"];
		$_POST['select_concepto']='';
		$_POST['select_plan']='';
		$_POST['cuentas']=$_POST["cuenta_$i"];
		$_POST["text_fecha"]=$_POST["fecha_$i"];
		$_POST["text_monto"]=$_POST["monto_$i"];
		$_POST["text_item"]=$_POST["item_$i"];
		if (!isset($_POST["egreso_$i"])) {
			$_POST["forcesave"]=1;
			$_POST["editar"]="";
		}
		else {
			$_POST["editar"]="ok";
			$id=$_POST["egreso_$i"];
		}
		if (guardar_ie("egreso",$id_distrito)){
			if ($_POST["editar"]!="ok") {
				$sql="insert into item_anticipo (id_anticipo,id_ingreso_egreso) VALUES ($id_anticipo,$id)";
				sql($sql) or fin_pagina();
			}
		}
		else $error=1;
		$i++;
	}
	if (!$error) {
		$sql="update anticipo set estado='historial' where id_anticipo=$id_anticipo";
		sql($sql) or fin_pagina();
	}
}

$q="select id_tipo_egreso,nombre from tipo_egreso";
$egresos=sql($q) or fin_pagina();
$q="select numero_cuenta,concepto,plan from tipo_cuenta";
$cuentas=sql($q) or fin_pagina();

echo $html_header;
echo cargar_calendario();
?>
<script>
// variables
var wproveedor=0;
var cuenta_default=new Array();

// Cuetas por default
<?
$sql="select id_proveedor,numero_cuenta from cuentas";
$re=sql($sql) or fin_pagina();
while ($cuen=$re->fetchrow()) {
	if ($cuen["id_proveedor"]) {
		echo "cuenta_default[".$cuen["id_proveedor"]."]=".$cuen["numero_cuenta"].";";
	}
}
?>

function cargar() {
	var items=eval("document.all.items_cant.value");
	items++;
	var fila=document.all.egresos.insertRow(document.all.egresos.rows.length);
	fila.id='ma';
	fila.insertCell(0).innerHTML='<input type=checkbox name=chk value=\"1\">';
	fila.insertCell(1).innerHTML='<select name="tipo_'+items+'"><?
	while ($fila=$egresos->fetchrow()) {
		echo "<option value=\"".$fila["id_tipo_egreso"]."\">".$fila["nombre"]."</option>";
	}
	?></select>';
	fila.insertCell(2).innerHTML='<input type="text" name="fecha_'+items+'" value="<?=date("d/m/Y");?>">';
	fila.insertCell(3).innerHTML='<input type="text" name="monto_'+items+'">';
	fila.insertCell(4).innerHTML='<textarea name="item_'+items+'" rows=1 cols=35></textarea>';
	var pag='nuevo_proveedor("../ordprod/elegir_prov.php?onclickcargar=window.opener.cargar_proveedor('+items+');window.close();&onclicksalir=window.close();");';
	fila.insertCell(5).innerHTML='<input type=hidden name="id_proveedor_'+items+'" value=""><input size=15 readonly name="proveedor_'+items+'" value=""><input type=button name=elegir_proveedor value=P onclick=\''+pag+'\'>';
	fila.insertCell(6).innerHTML='<select name="cuenta_'+items+'"><?
	while ($fila=$cuentas->fetchrow()) {
		echo "<option value=\"".$fila["numero_cuenta"]."\">".$fila["concepto"]." ".$fila["plan"]."</option>";
	}
	?></select>';
	var text=new String(items);
	document.all.items_cant.value=text;
	document.all.eliminar.disabled=0;
	//document.all.guardar.disabled=0;
}

function calcular() {
	var tmp=eval('document.all.items_cant');
	var i=1;
	var total=0;
	
	//alert(tmp);
	while (i<=tmp.value){
		var obj=eval('document.all.monto_'+i);
		if (typeof(obj)!='undefined')
			total=parseFloat(total)+parseFloat(obj.value);
		i++;
	}
	total=parseFloat(total)+parseFloat(document.all.devuelve.value);
	//alert(total);
	if (parseFloat(total)==parseFloat(document.all.monto.value))
		return true;
	else {
		alert('No coinciden los Montos. porfavor \n verifique los datos.');
		return false;
	}
}

function borrar_items() {
	var i=0;
	var items=eval('document.all.items_cant');
	while ((typeof(document.all.chk)!='undefined') && (typeof(document.all.chk.length)!='undefined') && (i<document.all.chk.length)) {
		var it=i+1;
		var tmp=eval('document.all.egreso_'+it);
		if ((typeof(tmp)=='undefined') && (typeof(document.all.chk[i])!='undefined') && (document.all.chk[i].checked)) {
			document.all.egresos.deleteRow(i+1);
			//items.value--;	
		}
		else 
			i++;
	}
	var tmp=eval('document.all.egreso_1');
	if ((typeof(tmp)=='undefined') && (typeof(document.all.chk)!='undefined') && document.all.chk.checked) {
		document.all.egresos.deleteRow(1);
	 	//items.value--;
		document.all.eliminar.disabled=1;
		//document.all.modo.disabled=1;
	}
	else if (typeof(document.all.chk)=='undefined') {
		document.all.eliminar.disabled=1;
		//document.all.modo1.disabled=1;
	}
	var text=new String(items.value);
	//document.all.item.value=text;
}
function nuevo_proveedor(pagina) {
	//var pagina=
	if (wproveedor==0 || wproveedor.closed)
		wproveedor=window.open(pagina,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=300');
	}
function cargar_proveedor(registro){
	var idprov=eval('document.all.id_proveedor_'+ registro);
	var prov=eval('document.all.proveedor_'+ registro);
	var cuenta=eval('document.all.cuenta_'+ registro);
	prov.value=wproveedor.document.all.select_proveedor[wproveedor.document.all.select_proveedor.selectedIndex].text;
	idprov.value=wproveedor.document.all.select_proveedor.value;
	for (var i = 0; i < cuenta.options.length; i++) {
		//alert(cuenta.options[i].value);
		if (cuenta_default[idprov.value]==cuenta.options[i].value)
			cuenta.selectedIndex=i;
		i++;
	}
}
</script>
<br>
<form action="nuevo_anticipo.php" name="nuevo_rma" method="post"> 
<input type=hidden name="id_anticipo" value="<?=$id_anticipo;?>">
<table width="90%" align="center" cellpadding="3">
<tr id=mo>
    <td colspan="2">
		<font size="3"><? if ($id_anticipo) echo "Modificar anticipo a rendir"; else echo "Nuevo anticipo a rendir";?></font>
	</td>
</tr>
<tr id=ma_sf>
	<td>
		<b>Usuario (autorizado):</b>&nbsp;&nbsp;&nbsp;
<?
	//$query="select id_usuario,login,nombre,apellido from usuarios order by nombre";
	$query="select usuarios.login,usuarios.nombre,usuarios.apellido,usuarios.id_usuario
	        from permisos.phpss_account join sistema.usuarios on usuarios.login=phpss_account.username
            where active='true' order by usuarios.nombre,apellido";
	$user=sql($query) or fin_pagina();
?>
		<select name="sujeto" <? if ($id_anticipo) echo "disabled";?>>
		 <option value="-1" selected>Seleccione Usuario...</option>
<?
	while ($fil=$user->fetchrow())
	{
		//if ($permisos->acl_check("usuarios", $fil["login"], "inicio", "usuarios_anticipo")) {
			echo "<option value='".$fil["id_usuario"]."'";
			if ($id_usuario==$fil["id_usuario"]) echo " Selected";
			echo ">".$fil["nombre"]." ".$fil["apellido"]."</option>";
		//}
	}
?>
		</select>
	</td>
	<td>
		<b>Fecha de Entrega:</b>&nbsp;&nbsp;&nbsp;
		<input type="text" name="fecha_entrega" <? if ($id_anticipo) echo "disabled";?> value="<? if ($fecha_entrega) echo $fecha_entrega; else echo date("d/m/Y");?>">
		<? if (!$id_anticipo) link_calendario("fecha_entrega");?>
	</td>
</tr>
<tr id=ma_sf>
	<td>
		<b>Caja Emisora:</b>&nbsp;&nbsp;&nbsp;
		<select name="distrito" <? if ($id_anticipo) echo "disabled";?>>
			<option value="2" <? if ($id_distrito==2) echo "selected";?>>Buenos Aires</option>
			<option value="1" <? if ($id_distrito==1) echo "selected";?>>San Luis</option>
		</select>
	</td>
	<td>
		<b>Monto:</b>&nbsp;&nbsp;&nbsp;
		<select name="moneda" <? if ($id_anticipo) echo "disabled";?>>
			<option value="1" <? if ($id_moneda==1) echo "selected";?>>Pesos</option>
			<option value="2" <? if ($id_moneda==2) echo "selected";?>>Dólares</option>
		</select>	
		<input type="text" name="monto" value="<?=$monto?>" <? if ($id_anticipo) echo "disabled";?>>
	</td>
</tr>
<tr id=ma_sf>
	<td colspan=2>
		<b>comentario:</b><br>
		<textarea name="comentario" cols=120 rows=7><?=$comentario;?></textarea>
	</td>
</tr>
<tr id=ma_sf>
	<td colspan=2 align="right">
		<input type="submit" name="guardar" value="<? if(!$id_anticipo) echo "Guardar"; else echo "Modificar";?>" onclick="
																												   if(document.all.sujeto.options.value==-1)
																												   {
																												     alert('Debe elegir un usuario para el anticipo');
	 																											     return false;
 																											       }"
         >
	</td>
</tr>
</table>
<?
if ($id_anticipo) {
$query="select id_anticipo,id_ingreso_egreso,id_tipo_egreso,caja.fecha,proveedor.id_proveedor,proveedor.razon_social,monto,item,numero_cuenta from item_anticipo  
	left join ingreso_egreso using (id_ingreso_egreso) 
	left join proveedor using(id_proveedor) 
	right join caja using(id_caja) where id_anticipo=$id_anticipo";
	
$ite=sql($query) or fin_pagina();
?>
<table width="90%" align="center" cellpadding="3">
<tr id=mo>
    <td colspan="2">
		<font size="3">Rendir</font>
	</td>
</tr>
<tr id=ma_sf>
	<td>
		<b>Importe devuelto:</b>&nbsp;&nbsp;&nbsp;<?if ($id_moneda=="1") echo "$"; else echo "U\$S";?>
		<input type="text" name="devuelve" value="<?=$devuelve;?>">
	</td>
</tr>
<tr id=ma_sf>
	<td>
		<table width="98%" align="center" cellpadding="2" cellspacing="2" id="egresos">
		<tr id=mo>
			<td>
				&nbsp;
			</td>
			<td>
				Tipo
			</td>
			<td>
				Fecha
			</td>
			<td>
				Monto
			</td>
			<td>
				Items
			</td>
			<td>
				Proveedor
			</td>
			<td>
				Cuenta
			</td>
		</tr>
<?
$i=0;
while ($filas=$ite->fetchrow()) {
	$i++;
	echo "<tr id=ma><td><input type='hidden' name='egreso_$i' value='".$filas["id_ingreso_egreso"]."'><input type='checkbox' disabled name='chk' value=1></td>\n";
	echo "<td><select name='tipo_$i'>\n";
	$egresos->movefirst();
	while ($f=$egresos->fetchrow()) {
		echo "<option value='".$f["id_tipo_egreso"]."'";
		if ($f["id_tipo_egreso"]==$filas["id_tipo_egreso"]) echo " selected";
		echo ">".$f["nombre"]."</option>\n";
	}
	echo "</select></td>\n<td><input type='text' name='fecha_$i' value='".fecha($filas["fecha"])."'></td>\n";
	echo "<td><input type='text' name='monto_$i' value='".$filas["monto"]."'></td>\n";
	echo "<td><textarea name='item_$i' rows=1 cols=35>".$filas["item"]."</textarea></td>\n";
	$pag="nuevo_proveedor(\"../ordprod/elegir_prov.php?onclickcargar=window.opener.cargar_proveedor($i);window.close();&onclicksalir=window.close();\");";
	echo "<td><input type=hidden name='id_proveedor_$i' value='".$filas["id_proveedor"]."'><input size=15 readonly name='proveedor_$i' value='".$filas["razon_social"]."'><input type=button name=elegir_proveedor value=P onclick='$pag'></td>\n";
	echo "<td><select name='cuenta_$i'>\n";
	$cuentas->movefirst();
	while ($f=$cuentas->fetchrow()) {
		echo "<option value='".$f["numero_cuenta"]."'";
		if ($f["numero_cuenta"]==$filas["numero_cuenta"]) echo " selected";
		echo ">".$f["concepto"]." ".$f["plan"]."</option>\n";
	}
	echo "</select></td></tr>\n";
}
?>
		</table>
	</td>
</tr>
<tr id=ma_sf>
	<td>
		<input type="hidden" name="items_cant" value='<?= $i;?>'>
		<input type="button" <? if($i==0) echo "disabled";?> name="eliminar" value="Eliminar" onClick="borrar_items();">
		<input type="button" name="agregar" value="Agregar" onClick="cargar();">
	</td>
</tr>
<tr id=ma_sf align=center>
	<td>
		<input type="submit" name="rendir" value="Rendir" onClick="return calcular();">
	</td>
</tr>
</table>
</form>
<?
}
fin_pagina();
?>