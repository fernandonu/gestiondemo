<?php
/*
$Author: mari $
$Revision: 1.153 $
$Date: 2007/01/05 20:09:36 $
*/
require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");


function armo_fecha($fecha)
{switch ($fecha)
    {case 1: {$cambio="Enero";
         break;
         }
     case 2: {$cambio="Febrero";
         break;
         }
     case 3: {$cambio="Marzo";
         break;
         }
     case 4: {$cambio="Abril";
         break;
         }
     case 5: {$cambio="Mayo";
         break;
         }
     case 6: {$cambio="Junio";
         break;
         }
     case 7: {$cambio="Julio";
         break;
         }
     case 8: {$cambio="Agosto";
         break;
         }
     case 9: {$cambio="Septiembre";
         break;
         }
     case 10: {$cambio="Octubre";
         break;
         }
     case 11: {$cambio="Noviembre";
         break;
         }
     case 12: {$cambio="Diciembre";
         break;
         }
   }
 return $cambio;
}


function generar_select_tipo($ie,$nombre) {
global $db,$pagina,$parametros;

if ($_POST['postear']=='sip' || $_POST['postear']=='sipok') $tipo_valor="post";
if ($_POST['Nuevo']=='Nuevo') $tipo_valor="vacio";
if ($pagina == "listado") {$tipo_valor="base";}
if ($parametros['pagina_viene']=="orden_de_compra" || $parametros['pagina_viene']=="ord_pago") $tipo_valor=$parametros['pagina_viene'];
///////////////Codigo Broggi/////////////////////
if ($parametros['pagina_viene'] == "pago_sueldo") $tipo_valor="pago_sueldo";
/////////////////////////////////////////////////

if ($parametros['pagina_viene'] == "lic_cobranzas") $tipo_valor="lic_cobranzas";

if ($parametros['pagina_viene'] == "cheques diferidos") $tipo_valor="cheques diferidos";

if($ie=="ingreso"){
 $query="SELECT * FROM caja.tipo_ingreso";
 $tipo="id_tipo_ingreso";
 }
if($ie=="egreso")
{
 $query="SELECT * FROM caja.tipo_egreso left join bancos.tipo_banco using(idbanco) ";
 $query.="where activo=1 or activo is null ";
 $query.="order by tipo_egreso.nombre ";
 $tipo="id_tipo_egreso";
}


 $resultados=$db->Execute($query) or die($db->ErrorMsg().$query);
 $cantidad=$resultados->RecordCount();
 if($ie=="ingreso")
  $title_combo="Ingreso";
 elseif($ie=="egreso")
 {
  $title_combo="Egreso";
  $onchange="onchange=\"show_select(this)\"";
  $q="";
 }

  echo "<select name='$nombre' $onchange>
		<option value=-1>Seleccione Tipo de $title_combo</option>";
  for($i=0;$i<$cantidad;$i++) {
	$valor=$resultados->fields[$tipo];
	$string=$resultados->fields['nombre'];
	$idbanco=$resultados->fields['idbanco'];
	if (valores($tipo_valor,$nombre) == $valor) echo "<option selected value='$valor'> $string </option>";
	else echo "<option value='$valor' idbanco='$idbanco'> $string </option>";
	$resultados->MoveNext();
 }
  echo "</select>";
 if($ie=="egreso")
 {
 	$q="SELECT * FROM bancos.tipo_depósito";
	$result = sql($q,$q) or fin_pagina();
?>
	</td>
	</tr>
	<tr id="trbanco" style="display:<?=$_POST['select_tipodep_banco']?"inline":"none"?>"><td title="Tipo de deposito en el Banco"><b>Tipo de Deposito</b></td>
	<td>
<? if ($_POST['select_tipodep_banco']=="") $_POST['select_tipodep_banco']=2 ;//cheques de plaza por defecto?>
	<input type="hidden" name="idbanco" value="<?=$_POST['idbanco']?>" />
	<select name="select_tipodep_banco" title="Tipo de deposito en el Banco">
<?= make_options($result,'idtipodep','tipodepósito',$_POST['select_tipodep_banco']) ?>
	</select>
<script>
var default_onchange_action=null;//puntero al evento onchange del combo moneda
function show_select(oselect)
{ var x;
	if (oselect.options[oselect.selectedIndex].text.indexOf("Depósito")!=-1)
	{
		document.all.trbanco.style.display='inline';
		document.all.idbanco.value=oselect.options[oselect.selectedIndex].idbanco;
		document.all.select_moneda.selectedIndex=1;
		conversion_monedas(document.all.text_monto,document.all.valor_dolar.value);

		//elimina la opcion de moneda dolar
		for (x = 0; x < document.all.select_moneda.length; x++)
		{
		  if(document.all.select_moneda.options[x].text=="Dólares")
		  {document.all.select_moneda.options[x] = null;
		   document.all.select_moneda.lenght--;
		  }
        }

		//default_onchange_action=document.all.select_moneda.onchange;
        //document.all.select_moneda.onchange=function(){alert('Los Depósitos en Bancos, se hacen solo en Pesos ($)');this.selectedIndex=1;onchange_imputacion;};

	}
	else
	{
		document.all.trbanco.style.display='none';
		document.all.select_tipodep_banco.selectedIndex=-1;
		document.all.idbanco.value='';

		//si no tiene moneda dolar el select, lo agrega
		var hay_dolar=0;
		for (x = 0; x < document.all.select_moneda.length; x++)
		{
		  if(document.all.select_moneda.options[x].text=="Dólares")
		  {
		  	hay_dolar=1;
		  }
        }
        if(!hay_dolar)
        {
         var newoption = new Option("Dólares", "2", false, false);
         document.all.select_moneda.length++;
         document.all.select_moneda.options[document.all.select_moneda.length-1] = newoption;
        }
		//document.all.select_moneda.onchange=(default_onchange_action)?default_onchange_action:null;
	}
}
</script>
<?
 }

}//fin de generar_select_tipo

function combo_moneda($nombre,$default="Seleccione una moneda",$ie=""){
 global $db,$pagina,$parametros,$valor_dolar,$id_moneda_para_imputacion;

 if ($_POST['postear']=='sip' || $_POST['postear']=='sipok') $tipo_valor="post";
 if($_POST['Nuevo']=='Nuevo') $tipo_valor="vacio";
 if($pagina == "listado") $tipo_valor="base";


 $evento="onchange='";
 if ($parametros['pagina_viene'] == "orden_de_compra")
	  {
	  $tipo_valor="orden_de_compra";
	  if ($valor_dolar)
			  {
			  $evento.="conversion_monedas(document.all.text_monto,document.all.valor_dolar.value);";
			  $disabled="";
			  }
			  else
				{

				$disabled="disabled";
				}
	  }//del if de orden de compra

  if($ie=="egreso")
 { $evento="onchange='setear_montos_imputacion(\"id_ingreso_egreso\");
            if(this.options[selectedIndex].text==\"Dólares\")
		  {
		   if(typeof(document.all.tabla_dolar)!=\"undefined\")
		    document.all.tabla_dolar.style.display=\"block\";
		  }
		  else
		  {
           if(typeof(document.all.tabla_dolar)!=\"undefined\")
		    document.all.tabla_dolar.style.display=\"none\";
		  }
            ';";
 }//de if($ie=="egreso")
 else
  $evento="";


 if ($parametros['pagina_viene']=="orden_de_compra" || $parametros['pagina_viene']=="ord_pago")
 {
	  $tipo_valor=$parametros['pagina_viene'];
	  if ($valor_dolar)
			  {
			  $evento="onchange='conversion_monedas(document.all.text_monto,document.all.valor_dolar.value)'";
			  $disabled="";
			  }
			  else
				{
				$evento="";
				$disabled="disabled";
				}
	  }//del if de orden de compra
 ///////////////////Codigo Broggi////////////////////////
 if ($parametros['pagina_viene'] == "pago_sueldo")
    {$tipo_valor="pago_sueldo";
    }
 ////////////////////////////////////////////////////////
/*lic_cobranzas*/
 if ($parametros['pagina_viene'] == "lic_cobranzas") {
    $tipo_valor="lic_cobranzas";
    }

 /*cheques diferidos*/
 if ($parametros['pagina_viene'] == "cheques diferidos") {
    $tipo_valor="cheques diferidos";
    }

	//traemos el tipo de la moneda
   $query="select nombre,id_moneda from moneda";
   $moneda_query=$db->Execute($query) or die ($db->ErrorMsg()."<br>".$query);

	echo "<select name='$nombre' $disabled $evento >\n";
	echo "<option value=-1>$default</option>\n";
   while(!$moneda_query->EOF)
   {echo "<option value=".$moneda_query->fields['id_moneda'];
	if(valores($tipo_valor,$nombre)==$moneda_query->fields['id_moneda'])
	echo " selected ";
	echo ">".$moneda_query->fields['nombre']."</option>";
	$moneda_query->MoveNext();
   }

 echo "</select>";
 $id_moneda_para_imputacion=valores($tipo_valor,$nombre);
}

function valores($tipo,$nombre){
global $fecha,$item,$monto,$moneda,$tipo_ingreso;
global $tipo_egreso,$comentarios,$distrito,$proveedor,$entidad,$cmd;
global $concepto_cuenta,$plan_cuenta,$concepto,$text_fecha;
global $parametros,$monto,$id_moneda,$id_proveedor,$observaciones;
global $nro_orden,$item,$id_tipo_egreso,$numero_cuenta,$tipo_cuenta,$cuenta;


if($tipo=="post") return $_POST[$nombre];
if (($tipo=='' || $tipo=='vacio') && $nombre=='text_fecha') {
    return fecha($fecha);
}
else if($tipo=='' || $tipo=='vacio') return "";

if($tipo=='base'){
   switch ($nombre) {
	  case 'select_distrito': return $distrito;break;
	  case 'text_fecha':      return  fecha($fecha);break;
	  case 'text_monto':      return $monto;break;
	  case 'text_item':       return $item;break;
	  case 'select_moneda':   return $moneda;break;
	  case 'select_tipo': if($cmd=="ingresos")
								  return $tipo_ingreso;
						  elseif($cmd=="egresos")
						  return $tipo_egreso;
						  break;
  case 'observaciones':   return $comentarios;break;
  case 'select_proveedor':return $proveedor;break;
  case 'select_entidad':  return $entidad;break;
  case 'select_concepto': return $concepto_cuenta;break;
  case 'select_plan':     return $plan_cuenta;break;
  case 'select_cuenta':   return $tipo_cuenta;break;

  } //fin del case
 }//fin del if

if ($tipo=='orden_de_compra' || $tipo=="ord_pago") {
	   switch ($nombre) {
		   case 'text_fecha': return $text_fecha;break;
		   case 'text_monto': return $monto;break;
		   case 'text_item':  return $item;break;
		   case 'select_moneda':return $id_moneda;break;
		   case 'select_tipo':  return $id_tipo_egreso;break;
		   case 'observaciones':   return $observaciones;break;
		   case 'select_proveedor':return $id_proveedor;break;
		   case 'select_entidad':  return $entidad;break;
		   case 'select_concepto': return $concepto_cuenta;break;
		   case 'select_plan':     return $plan_cuenta;;break;
		   } //fin del case
  }//del if del orden de compra
///////////////////////Codigo Broggi//////////////////////////////////
  if ($tipo=='pago_sueldo') {
	   switch ($nombre) {
		   case 'text_fecha': return $fecha;break;
		   case 'text_monto': return $monto;break;
		   case 'text_item':  return $item;break;
		   case 'select_moneda':return $moneda;break;
		   case 'select_tipo':  return $tipo_egreso;break;
		   //case 'observaciones':   return $observaciones;break;
		   case 'select_proveedor': return $proveedor;break;
		   case 'select_entidad':  return $entidad;break;
		   case 'select_concepto': return $numero_cuenta;break;
		   case 'select_plan':     return $plan_cuenta;;break;
		   } //fin del case
  }//fin del if de pago_sueldo
////////////////////////////////////////////////////////////////////
//lic_cobranzas
if ($tipo=="lic_cobranzas") {
	  switch ($nombre) {
    	   case 'select_tipo': if($cmd=="ingresos")
								  return $tipo_ingreso;
						  elseif($cmd=="egresos")
						  return $tipo_egreso;
						  break;
		   case 'text_fecha': return fecha($fecha);break;
		   case 'text_monto': return $monto;break;
		   case 'select_moneda':return $moneda;break;
		   case 'select_entidad': return $entidad;break;
		   case 'select_proveedor': return $proveedor;break;
		   case 'text_item':  return $item;break;
		   case 'select_cuenta': return $tipo_cuenta; break;
		   case 'select_concepto': return $cuenta; break;
		   case 'text_fecha': return $fecha;break;

		   } //fin del case
}   //fin lic_cobranzas

//cheques diferidos
if ($tipo=="cheques diferidos") {

	  switch ($nombre) {
    	   case 'select_tipo': if($cmd=="ingresos")
								  return $tipo_ingreso;
						  elseif($cmd=="egresos")
						  return $tipo_egreso;
						  break;
		   case 'text_fecha': return fecha($fecha);break;
		   case 'text_monto': return $monto;break;
		   case 'select_moneda':return $moneda;break;
		   case 'select_entidad': return $entidad;break;
		   case 'select_proveedor': return $proveedor;break;
		   case 'text_item':  return $item;break;
		   case 'select_cuenta': return $tipo_cuenta; break;
		 } //fin del case
}   //fin lic_cobranzas

}//function valores($tipo,$nombre)

function generar_combo_distrito()
{global $db,$pagina;
 $valor="";
 if($_POST['postear']=='sip' || $_POST['postear']=='sipok') $valor="post";
 if($_POST['Nuevo']=='Nuevo') $valor="vacio";
 if($pagina == "listado") $valor="base";

 //generamos el combo de distrito, solo con san luis y buenos aires
 $query="select id_distrito,nombre from distrito where nombre='San Luis' or nombre='Buenos Aires - GCBA' order by nombre";
 $dist=$db->Execute($query) or die ($db->ErrorMsg().$query);

 echo"<select name='select_distrito'>
	   <option value=-1>Seleccione Distrito</option>";
 while(!$dist->EOF)
 {echo"
		<option value=".$dist->fields['id_distrito'];
		 if(valores($valor,"select_distrito")==$dist->fields['id_distrito'])
		  echo " selected ";
		echo ">".$dist->fields['nombre']."</option>";
  $dist->MoveNext();
 }
 echo "
	  </select>
   ";
}

function show_datos($ie) {
global $pagina,$db,$parametros,$orden_compra,$monto_para_imputacion,$moneda,$caja_actual_cerrada,$fecha,$distrito;
//variables auxiliares:

if ($moneda=="") $moneda=$_POST['select_moneda'];
if ($caja_actual_cerrada=="") $caja_actual_cerrada=$_POST['caja_actual_cerrada'];

$valor="";
if($_POST['postear']=='sip' || $_POST['postear']=='sipok') $valor="post";
if($_POST['Nuevo']=='Nuevo') $valor="vacio";
if($pagina == "listado") $valor="base";

if ($valor !='vacio' && $valor !="" )  {
//si la caja esta cerrada no le dejo cambiar la fecha
if (!$fecha) $fecha=fecha_db($_POST['text_fecha']);
$query="select id_caja,cerrada,fecha from caja where fecha='$fecha' and id_distrito=$distrito and id_moneda=$moneda";
$caja_query=sql($query,"EN SHOW DATOS $query") or fin_pagina();
$cerrada=$caja_query->fields['cerrada'];
$fecha_hoy=date("Y-m-d",mktime());
if ($cerrada) {
    $des_fecha=' disabled';
    echo "<input type=hidden name='text_fecha' value='".fecha_db($fecha)."'>";
}
elseif (!$cerrada && compara_fechas($fecha_hoy,$fecha) == 1) {
    $des_fecha=' disabled';
    echo "<input type=hidden name='text_fecha' value='".fecha_db($fecha)."'>";
}
else {
	 $cerrada=0;
     $des_fecha=" ";
}


}
if ($parametros['pagina_viene']=="orden_de_compra" || $parametros['pagina_viene']=="ord_pago") {
											$valor=$parametros['pagina_viene'];
											$readonly="Readonly";
											 }

												  else
												   $readonly="";
/////////////////Codigo Broggi//////////////////////////
if ($parametros['pagina_viene'] == "pago_sueldo") {
											$valor="pago_sueldo";
											$readonly="Readonly";
											 }
/////////////////////////////////////////////////////////
//viene de lic_cobranzas
if ($parametros['pagina_viene'] == "lic_cobranzas" ) {
											$valor="lic_cobranzas";
											$des_monto='readonly';
													}

//viene de cheques diferidos
if ($parametros['pagina_viene'] == "cheques diferidos" ) {
											$valor="cheques diferidos";
											$des_monto='readonly';
													      }

//cuando muestra el listado deshabilito el monto si el ingreso se realizo desde cobranzas
if ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='egreso') {
  $id_ing_eg=$parametros['id_ingreso_egreso'];
  $sql=" select id_ingreso_egreso from detalle_egresos where id_ingreso_egreso=$id_ing_eg";
  $res=sql($sql) or fin_pagina();
if ($res->RecordCount()>0 ) $des_monto='readonly';
  else $des_monto= "";
} elseif ($parametros['id_ingreso_egreso'] && $parametros['pagina']=='ingreso') {
	$id_ing_eg=$parametros['id_ingreso_egreso'];
	$sql=" select id_ingreso_egreso from cobranzas where id_ingreso_egreso=$id_ing_eg";
    $res=sql($sql) or fin_pagina();
    if ($res->RecordCount()>0)  $des_monto='readonly';
       else $$des_monto="";
}


//cuerpo de la funcion:
 echo "
 <table>";
   echo "<tr>
	   <td><b>Tipo </b></td>
		   <td>
";
  
 generar_select_tipo($ie,"select_tipo");

echo"
	   </td>

	</tr>
	<tr>
		<td><b>Fecha </b></td>
		   <td> <input name='text_fecha' $des_fecha type='text' value='".valores($valor,'text_fecha')."')\" >";
if (!$cerrada) echo link_calendario('text_fecha');

echo "  </td>
	</tr>
	<tr>
		 <td><b>Monto </b></td>
		  <td>
		  <input type='text' name='text_monto' $readonly  $des_monto value='".valores($valor,'text_monto')."' ";
          if($ie=="egreso")
           echo " onchange='setear_montos_imputacion(\"id_ingreso_egreso\")'";
echo ">
     <input type='hidden' name='monto_anterior_guardado' value='".valores($valor,'text_monto')."'>
     ";
$monto_para_imputacion=valores($valor,'text_monto');
echo "   </td>
	</tr>";

  echo " <input type='hidden' name='moneda_anterior_guardada' value='".valores("base","select_moneda")."'>";

echo"<tr>
		 <td><b>Moneda </b></td>
		 <td>

		 ";
   combo_moneda("select_moneda","Seleccione una moneda",$ie); //funcion que genera un combo para seleccionar la moneda del ingreso/egreso
echo"      </td>
	</tr>
	<tr>
		 <td><b>Item </b></td><td><input type='text' name='text_item' value='".valores($valor,'text_item')."' size='60'></td>
	</tr>
	<tr>
	 <td colspan='2' align='center'><b>Observaciones</b></td>
	</tr>

	<tr align='center'>

	  <td colspan='2'>
		  <center>
			  <textarea name='observaciones' cols='75' rows='4' wrap='VIRTUAL' id='observaciones'>".valores($valor,'observaciones')."</textarea>
		  </center>
	  </td>
	</tr>
  </table>";

  return 1;
} //fin de  function show_datos()


function show_encabezado($titulo1,$titulo2,$color){
echo "<hr>
<TABLE width='100%' align='center' border='0' cellspacing='2' cellpadding='0'>";
if($color!="")
 echo "<tr bgcolor='$color'>";
else
 echo "<tr id=mo>";             //#BA0105;
echo "<td width='60%' align='center'><strong>";
if($color!=""){
			   echo "<font color='#FDF2F3'>";
			   echo $titulo1;
			   echo "</font>";
			  }
else echo $titulo1;
	  echo "</strong>
	  </td>
	  <td width='40%' height='20' align='center' ><strong>";
if($color!=""){
			   echo "<font color='#FDF2F3'>";
			   echo $titulo2;
			   echo "</font>";
			  }
else echo $titulo2;

	  echo "</strong> </td>
  </tr>
</table>
<br>";
} //fin de show_encabezado

function show_titulo($titulo,$distrito) {
	global $bgcolor1;
if($distrito=='Buenos Aires - GCBA') $color='#FF6600';
if($distrito=='San Luis') $color=$bgcolor1;
echo"
<center>
	<font size='4' color='$color'>
				  <b>";
				  echo $titulo;
				  echo "</b>
	</font>
 </center>";
}

function show_pestañas($titulo1,$titulo2,$titulo3) {

global $link_ingresos,$link_egresos,$link_caja,$tipo;

if($tipo=='Ingreso') $id_in='ma'; else $id_in='mo';
if($tipo=='Egreso') $id_eg='ma'; else $id_eg='mo';
if($tipo=='caja') $id_ca='ma'; else $id_ca='mo';

echo "
<TABLE width='100%' align='center' border='0' cellspacing='2' cellpadding='0'>";
if($color!="") echo "<tr bgcolor='$color'>";
else echo "<tr >";             //#BA0105;
echo "<td id=$id_in width='33%' align='center'><strong>";

if($color!=""){
			   echo "<font color='#FDF2F3'>";
			   echo  "<a id=$id_in href='".$link_ingresos."'>$titulo1</a>";
			   echo "</font>";
			  }
else echo  "<a href='$link_ingresos'>$titulo1</a>";
	  echo "</strong>
	  </td>
	 <td id=$id_eg width='33%' height='20' align='center' ><strong>";

if($color!=""){
				echo "<font color='#FDF2F3'>";
				echo "<a href='$link_egresos'>$titulo2</a>";
				echo "</font>";
			  }
else  echo "<a href='$link_egresos'>$titulo2</a>";

	  echo "</strong> </td>
	  <td id=$id_ca width='33%' height='20' align='center' ><strong>";
if($color!=""){
			   echo "<font color='#FDF2F3'>";
			   echo "<a href='$link_caja'>$titulo3</a>";
			   echo "</font>";
			  }
else  echo "<a href='$link_caja'>$titulo3</a>";
	  echo "</strong> </td>
  </tr>
</table>
<br>";
}
//$r es un resulset de consulta
function put_status($ie,$exito,$color,$r=false){
global $parametros;
global $forcesave;//variable que indica si debe guardar aunque haya repetidos
switch ($exito)
{
	case 1: $estado="Editando ".$ie." existente...";break;
	case 2: $estado="Cargando nuevo ".$ie."...";break;
	case 3: $estado="El ".$ie." se cargo con exito";break;
	case 4: $estado="El ".$ie." se actualizo con exito";break;
	case 5: $estado="Error: No se pudo cargar el ".$ie;break;
	case 6: $estado="Error: No se pudo actualizar el ".$ie;break;
	case 7: $estado="Error: La caja del dia $ie ya ha sido cerrada.";break;
	case 8: $estado="Error: Usted esta intentando insertar un $ie de un dia que no es habil";break;
	case 9: $estado="Error: La caja del dia habil anterior no esta cerrada";break;
	case 10: $estado="Error: La caja que intenta cerrar no corresponde a un dia habil";break;
	case 11: $estado="Error: Existen cajas de fechas posteriores a $ie que ya han sido cerradas";break;
	case 12:
		if (ereg("ingresos_egresos\.php$",$_SERVER['SCRIPT_NAME']))
		{
	  $estado="<br>Existen items de $ie con igual monto en dias anteriores<br> Desea insertar de todos Modos?&nbsp;&nbsp;";
		$estado.="<input type=button name=binsertar value='Insertar Igual' style='width:95' onclick='form1.Guardar.click()'>&nbsp";
		$estado.="<input type=button name=bcancelar value='Cancelar' style='width:95' onclick=\"document.location.href='".encode_link($_SERVER['PHP_SELF'],array("cmd"=>$$cmd,"distrito"=>$parametros["distrito"])) ."'\">";
		ob_start();
		echo "<br>";
		echo "<table width=90% border=1 bordercolor=black align=center>";
		echo "<tr>";
		echo "<td align=center><b>Fecha</b></td>";
		echo "<td align=center><b>Item</b></td>";
		echo "<td align=center><b>Monto</b></td>";
		echo "</tr>";
		while (!$r->EOF)
		{
			echo "<tr>";
			echo "<td align=center width=30%>".date2("L", $r->fields['fecha'])."</td>";
			echo "<td align=center>".$r->fields['item']."</td>";
			echo "<td align=right>".formato_money($r->fields['monto'])."</td>";
			echo "</tr>";
			$r->movenext();
		}
		echo "</table>";
		}
		$forcesave=1;//para que guarde los datos si presiona Guardar nuevamente
		break;
    case 13: $estado="La caja seleccionada se Re-abrió con éxito";break;
    case 14: $estado="No se puede cambiar el monto o la moneda o la fecha. La caja del día $ie está cerrada.";break;
}//de switch ($exito)

if ($parametros["pagina_pago"]!="efectivo"){
   $buff=ob_get_clean();
   echo "<center><b><font size='2' color=$color>";
   echo $estado;
   echo "</font></b></center>";
   echo $buff;

}
} //fin put_status;

function show_botones() {
echo "
<center>
 <input type='submit' name='Guardar' value='Guardar' style='cursor:hand' title='Presione aqui para guardar los cambios efectuados'>
 <input type='button' name='Listado' value='Ver Listado' style='cursor:hand' title='Presione aqui para ver listado' onclick=\"location.href='listado_sl.php'\">
</center>";
}

function generar_parte_derecha($ie,$distrito)
{
global $db,$bgcolor1,$pagina;
global $concepto_cuenta,$filtro,$parametros,$disabled_pagos,$cuenta;


if($ie=="egreso")
{
 $query="SELECT proveedor.id_proveedor,razon_social,numero_cuenta FROM general.proveedor left join cuentas on (proveedor.id_proveedor=cuentas.id_proveedor and es_default=1) WHERE razon_social ilike '$filtro%' order by razon_social";
 echo "<table width='100%' align='center'>
   <tr>
	 <td colspan='2' width='100%' align='center'>

		<select name='select_proveedor' size='12' style='width:85%' $disabled_pagos onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange=seteo_cuenta(this.options[this.selectedIndex].id)>";

		  if ($_POST['postear']=='sip' || $_POST['postear']=='sipok')
							  $tipo_valor="post";
		  if($_POST['Nuevo']=='Nuevo')
							  $tipo_valor="vacio";
		  if($pagina=="listado")
							  $tipo_valor="base";
		  if($parametros['pagina_viene']=="orden_de_compra" || $parametros['pagina_viene']=="ord_pago")
		  {
							   $tipo_valor=$parametros['pagina_viene'];
							   $concepto_cuenta=$_POST['select_concepto'];
							   }
           if($parametros['pagina_viene']=="pago_sueldo")
							   {//die("Hasta aca llego");
							   $tipo_valor="pago_sueldo";
							   //$concepto_cuenta=$_POST['select_concepto'];
							   //echo "El proveedor es: ".valores($tipo_valor,"select_proveedor");
							   }
           if ($parametros['pagina_viene'] == 'lic_cobranzas')	 {
		              $tipo_valor='lic_cobranzas';
		  }

		  $datos_proveedor=$db->Execute($query) or die ($db->ErrorMsg()."<br>".$query);
		  $cantidad_proveedores=$datos_proveedor->RecordCount();

		  while(!$datos_proveedor->EOF)
		  {
		   echo"
		   <option id='".$datos_proveedor->fields['numero_cuenta']."' value='".$datos_proveedor->fields['id_proveedor']."'";
		   if(valores($tipo_valor,"select_proveedor")==$datos_proveedor->fields['id_proveedor'])
			  echo "selected";
		  echo">".$datos_proveedor->fields['razon_social']."</option>";

		   $datos_proveedor->MoveNext();
		  }

   $prov_elegido=valores($tipo_valor,"select_proveedor");
   echo"</select>
     <input type='hidden' name='id_proveedor_pago' value='$prov_elegido'>
	</td>
  </tr>
  <tr>
	<td align='center' colspan='2' bgcolor='#BA0105'><font color='White'><b>Cuenta : Concepto y Plan </b></font></td>
  </tr>
  <tr>
   <td colspan='2'>";
$con="select * from general.tipo_cuenta order by concepto,plan";
$resul_con=$db->Execute($con) or die ($db->ErrorMsg()."<br>".$con);
$cant_resul_con=$resul_con->RecordCount();

//si tenemos proveedor elegido, buscamos la cuenta por defecto del mismo
if($prov_elegido)
{
 $query="select numero_cuenta from cuentas where id_proveedor=$prov_elegido and es_default=1";
 $cuenta_defecto=sql($query) or fin_pagina();
 $cuenta_default=$cuenta_defecto->fields["numero_cuenta"];
}

echo "<select name='cuentas'>
       <option value=-1> Seleccionar Concepto y Plan </option>";
      for ($j=0; $j<$cant_resul_con; $j++)
      {
         $descripcion=$resul_con->fields['concepto']."&nbsp;[ ".$resul_con->fields['plan']." ]";
         $cuenta_bd=$resul_con->fields['numero_cuenta'];
         echo "<option value='".$resul_con->fields['numero_cuenta']."'";
         /*if($cuenta_default==$cuenta_bd)
           echo " selected ";
         elseif(($cuenta_bd==$cuenta) || ($_POST['cuentas']==$cuenta_bd))
	        echo " selected ";
	     elseif ($resul_con->fields['numero_cuenta']==valores($tipo_valor,"select_concepto"))  echo " selected ";
	     echo">$descripcion </option>";*/

	    if(($cuenta_bd==$cuenta) || ($_POST['cuentas']==$cuenta_bd))
	        echo " selected ";
	    elseif(!$cuenta && $cuenta_default==$cuenta_bd)
           echo " selected ";
	     if ($resul_con->fields['numero_cuenta']==valores($tipo_valor,"select_concepto"))  echo " selected ";
	     echo">$descripcion </option>";

	     $resul_con->MoveNext();
      }
echo "</select></td></tr>";
/*echo "
	 <tr style='visibility:hidden'>
	 <td><input type='checkbox' name='chk_cuenta' value='guardar'></td>
	 <td> Agregar cuenta al proveedor </td>
	</tr>";*/
     echo "<tr> <td colspan=2>";
     echo " <input type='hidden' name='combo_cuentas' value=''>";
       $btn=new HtmlButton("nuevoconcepto","Nuevo Concepto y Plan");

 	   $win=new JsWindow(encode_link('nuevoconcepto.php',array('')));//abre una ventana
       $win->varName="wagregar";
       $win->width=500;
       $win->height=200;
       $win->locationBar=false;
       $win->statusBar=false;
       $win->toolBar=false;
       $win->menuBar=false;
       $win->openOnce=true;
       $win->resizable=false;
       $btn->style="width:150px";
       $btn->set_event("onclick","document.all.combo_cuentas.value='cuentas';".$win->open());
       $btn->toBrowser();
      echo "</td></tr>";
 echo "</table>";


 }//de if($ie=="egreso")
 elseif($ie=="ingreso")
 {    if ($_POST['postear']=='sip' || $_POST['postear']=='sipok') $tipo_valor="post";
	  if ($_POST['Nuevo']=='Nuevo') $tipo_valor="vacio";
	  if ($pagina=="listado") $tipo_valor="base";
       if ($parametros['pagina_viene']=='lic_cobranzas') $tipo_valor='lic_cobranzas';

	echo"<table width='100%' align='center'>
	   <tr>
		 <td width='100%' align='center'>
		 <select name='select_entidad' size='12' style='width:85%' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()'>";
		  //$query="SELECT id_cliente,nombre FROM general.clientes where nombre ilike '$filtro%' order by(nombre)";
		  $query="SELECT id_entidad,nombre FROM entidad where nombre ilike '$filtro%' order by(nombre)";
		  $datos_entidad=$db->Execute($query) or die ($db->ErrorMsg()."<br>".$query);
		  while(!$datos_entidad->EOF)
		  {
		   echo"<option value=".$datos_entidad->fields['id_entidad'];
			if(valores($tipo_valor,"select_entidad")==$datos_entidad->fields['id_entidad'])
			  echo " selected ";
		   echo">".$datos_entidad->fields['nombre']."</option>";
		   $datos_entidad->MoveNext();
		   }
   echo     "</select>";
   echo    "</td>";
   echo   "</tr>";
   echo  "<tr id='mo'>";
	 echo "<td>";
	  echo "Tipo de Cuenta";
	 echo "</td>";
   echo "</tr>";
   echo  "<tr>";
   echo "<td align='center'>";
   $sql="select * from caja.tipo_cuenta_ingreso";
   $resultado_cuenta=$db->execute($sql) or die($sql);

   echo "<select name='select_cuenta' style='width:85%'>
			<option value=-1> Seleccione un Tipo de Cuentas </option>";
	while (!$resultado_cuenta->EOF)
		 {
		   echo"<option value=".$resultado_cuenta->fields['id_cuenta_ingreso'];
			if(valores($tipo_valor,"select_cuenta")==$resultado_cuenta->fields['id_cuenta_ingreso'])
					 {
					 echo " selected ";
					 }
		   echo">".$resultado_cuenta->fields['nombre']."</option>";
           $resultado_cuenta->MoveNext();
        }
     echo     "</select>";
     echo   "</td>";
     echo  "</tr>";
     echo "<tr>";
     echo  "<td>";
            echo "<table width='100%' align='center'>";
            echo "<tr id='mo'>";
              echo "<td colspan='3'>" ;
               echo "<b>Nueva Cuenta";
              echo "</td>";

            echo "</tr>";
            echo "<tr>";
               echo "<td>";
               echo "<input type='checkbox'  name='ch_nueva_cuenta' value='0' onclick='habilitar_guardar();'>";
               echo "</td>";
               echo "<td>";
               if ($_POST["ch_nueva_cuenta"]==0) $disabled="disabled";
                                            else $disabled="";
			   echo "<input type='text'  name='nombre_nueva_cuenta'  size='30' value='".valores($tipo_valor,"nombre_nueva_cuenta")."' $disabled>";
               echo "</td>";
               /*
               echo "<td>";
               echo "<input type='submit'  name='cargar_nueva_cuenta' value='Guardar'>";
               echo "</td>";
			   */
			 echo "<tr>";
			echo "</table>";
	 echo "</td></tr>";
	 echo "</table>"; //de la tabla de la parte derecha la principal
 }//de elseif($ie=="ingreso")

}//de la funcion generar_parte_derecha


//devuelve el detalle del ie con id_ingreso_egreso=$id
function detalle_ie($id,$ie)
{global $db;

  $campos="ingreso_egreso.monto,ingreso_egreso.comentarios,ingreso_egreso.item,moneda.simbolo,distrito.nombre as nbre_distrito,caja.fecha, ingreso_egreso.numero_cuenta";
  if($ie=="ingreso")
  {$campos.=" ,tipo_ingreso.nombre as tipo_ie,entidad.nombre as nbre_entidad";
  }
  elseif($ie=="egreso")
  {$campos.=" ,tipo_egreso.nombre as tipo_ie,proveedor.razon_social";
  }
  $query="select $campos
         from ingreso_egreso join caja using(id_caja) join moneda using(id_moneda) join distrito using(id_distrito)";
 if($ie=="ingreso")
  $query.=" join tipo_ingreso using(id_tipo_ingreso) join entidad using (id_entidad)";
 elseif($ie=="egreso")
  $query.=" join tipo_egreso using(id_tipo_egreso) join proveedor using (id_proveedor)";

 $query.=" where id_ingreso_egreso=$id";
 $datos=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer datos del ingreso o egreso en funcion de detalle de mail");

 $string="--------------------------------------------------------------";
 $string.="\nTipo:  ".$datos->fields['tipo_ie'];
 $string.="\nFecha: ".fecha($datos->fields['fecha']);
 $string.="\nMonto: ".$datos->fields['simbolo']." ".$datos->fields['monto'];
 $string.="\nItem:  ".$datos->fields['item'];
 $string.="\nObservaciones: \n".$datos->fields['comentarios'];
 if($ie=="ingreso")
  $string.="\nEntidad: ".$datos->fields['nbre_entidad'];
 elseif($ie=="egreso")
  $string.="\nProveedor: ".$datos->fields['razon_social'];
 $string.="\n--------------------------------------------------------------";

 return $string;
}

//funcion que guarda todos los datos en la base de datos
function guardar_ie($ie,$distrito)
{

  require_once("../contabilidad/funciones.php");//archivo con funciones para imputacion de datos
  global $db,$id,$parametros,$_ses_user,$tipo_valor,$stay, $g_pagina_bancos_ing_ch,$reenviar_por_imputacion;

  $db->StartTrans();

	if ($_POST['id_cobranza']) $id_cobranza=$_POST['id_cobranza'];
	if ($_POST['id_cob']) $id_cob=$_POST['id_cob'];
	if ($_POST['nro_factura']) $nro_factura=$_POST['nro_factura'];
	if ($_POST['id_licitacion']) $id_licitacion=$_POST['id_licitacion'];
	if ($_POST['cotizacion_dolar'] !="") $cotizacion_dolar=$_POST['cotizacion_dolar'];
	else $cotizacion_dolar=0;
	if ($_POST['dolar_actual'] !="") $dolar_actual=$_POST['dolar_actual'];
 	else $dolar_actual=0;

	$caja_cerrada=1;
	if($ie=="egreso"){
		$proveedor=$_POST["select_proveedor"];
		$tipo_egreso=$_POST['select_tipo'];
		$concepto=$_POST['select_concepto'];
		$plan=$_POST['select_plan'];
		$cuenta=$_POST['cuentas'];
	}elseif($ie=="ingreso"){
		$entidad=$_POST["select_entidad"];
		$tipo_ingreso=$_POST['select_tipo'];
		$tipo_cuenta_ingreso=$_POST['select_cuenta'];
	}

	$fecha=$_POST["text_fecha"];
	if(str_count_letra("/",$fecha)>0)
	   $fecha_total=split("/",$fecha);
	else
	   $fecha_total=split("-",$fecha);

	$fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0],$fecha_total[2]));
	$fecha_total=split("/",$fecha_aux);
	$monto=$_POST["text_monto"];
	$monto_anterior_guardado=$_POST["monto_anterior_guardado"];
	$item=$_POST["text_item"];
	$moneda=$_POST["select_moneda"];
	$moneda_anterior_guardada=$_POST["moneda_anterior_guardada"];
	$caja_actual_cerrada=$_POST["caja_actual_cerrada"];
	$comentarios=$_POST["observaciones"];
	$tipo=$_POST['select_tipo'];
	$fecha_creacion = date("Y-m-d H:m:s",mktime());
	$usuario=$_ses_user['name'];

  if($fecha_total[3]!=0 && !feriado($fecha))
  {
		$fecha=fecha_db($_POST["text_fecha"]);

		//si es el primer ingreso_egreso del dia, creamos la entrada de la caja de hoy
		$query="select id_caja,fecha,cerrada from caja where fecha='$fecha' and id_distrito=$distrito and id_moneda=$moneda";
		$caja_query=$db->Execute($query) or die ($db->ErrorMsg().$query);
		if($caja_query->RecordCount()==0){
			$query="insert into caja(id_distrito,id_moneda,fecha)values($distrito,$moneda,'$fecha')";
			$db->Execute($query) or die ($db->ErrorMsg().$query);
			$query="select id_caja,cerrada,fecha from caja where fecha='$fecha' and id_distrito=$distrito and id_moneda=$moneda";
			$caja_query=$db->Execute($query) or die ($db->ErrorMsg().$query);
		}
		$id_caja=$caja_query->fields['id_caja'];
		$caja_cerrada=$caja_query->fields['cerrada'];

		$permiso_cerrada=0;

		//PERMISO PARA MODIFICAR CAJAS CERRADAS
		if(permisos_check("inicio","permiso_cambiar_caja_cerrada"))
		{
			if($caja_cerrada && ($monto_anterior_guardado!= "" && $monto_anterior_guardado==$monto) && ($moneda_anterior_guardada!="" && $moneda_anterior_guardada==$moneda))
			{ $permiso_cerrada=1;
		      $caja_cerrada=0;
			}
	 	}

	 	//si la caja a la que pertenece el ingreso/egreso, antes de guardar, esta cerrada
	 	//y se cambio la moneda, no se permite el cambio
	 	if($caja_actual_cerrada && ($moneda_anterior_guardada!="" && $moneda_anterior_guardada!=$moneda))
		 $prohibir_cambio_caja_cerrada=1;
		else
		 $prohibir_cambio_caja_cerrada=0;

		if(!$caja_cerrada && !$prohibir_cambio_caja_cerrada)
		{//revisamos que no haya una caja cerrada con fecha mayor a la del ingreso/egreso
			$query="select id_caja,cerrada from caja where fecha > '$fecha' and id_distrito=$distrito and id_moneda=$moneda and cerrada=1";
			$res_cajas=$db->Execute($query) or die($db->Error->Msg()."<br>Error al buscar cajas cerradas posteriores");
			if($res_cajas->fields['id_caja']=="" || $permiso_cerrada)
			{
			  if(!$caja_cerrada && $_POST['editar']=="")
			  {//insertamos el ingreso_egreso
					//insertamos el ingreso o egreso
					if($ie=="egreso"){
						$nro_cuenta=$cuenta;
						//si el check esta activado, creamos una nueva cuenta para el proveedor
						if($_POST['chk_cuenta']=="guardar"){
							$query="select id_cuenta from cuentas where id_proveedor=$proveedor and numero_cuenta=$nro_cuenta";
							$concepto_query=$db->Execute($query) or die($db->ErrorMsg().$query);
							if($concepto_query->RecordCount()==0){
								$query="insert into cuentas (id_proveedor,numero_cuenta) values($proveedor,$nro_cuenta)";
								$db->Execute($query) or die($db->ErrorMsg().$query);
							}
						}//del if del check
						//Verifico que no se haya hecho un ingreso similar en los dos dias habiles anteriores
							//echo "FORCESAVE ".$_POST['forcesave'];

							if (!$_POST['forcesave'] && $r=chk_similar_ie("egreso",$distrito,ereg_replace("^(.*)-(.*)-(.*)$","\\3/\\2/\\1",$fecha),$monto,-2) )
							{ //echo "if 1";
								$status=0;//para inserte los datos y no actualice
								$tipo_valor="post";
								$_POST['postear']="sip";//para que retenga los valores
								$diego=put_status("egreso",12,"black",$r);
								$stay=1; //variable que indica si se debe quedar en la pagina de ingreso_egresos
								$todo_ok=0;
								return $status;
							}
							if($parametros['pagina_viene']=="orden_de_compra")
							{
								$status=0;//para inserte los datos y no actualice
								$tipo_valor="post";
								$_POST['postear']="sipok";//para que retenga los valores
								$stay=0; //variable que indica si se debe quedar en la pagina de ingreso_egresos
								//echo "STAY ".$stay;
								$todo_ok=0;
								return $status;
							}
							elseif ($parametros['pagina_viene']=="ord_pago" ) {
								$status=0;//para inserte los datos y no actualice
								$tipo_valor="post";
								$_POST['postear']="sipok";//para que retenga los valores
								$stay=0; //variable que indica si se debe quedar en la pagina de ingreso_egresos
								//echo "SATAY ".$stay;
								$todo_ok=0;
								return $status;
						}
            //traemos y reservamos el id a insertar
						$query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
            $id_ie=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de ingreso/egreso");
            $id=$id_ie->fields['id'];
            //query para insertar en la base de datos el ingreso cargado:
					  $query="INSERT into caja.ingreso_egreso (id_ingreso_egreso,id_caja,id_proveedor,id_tipo_egreso,monto,comentarios,usuario,fecha_creacion,item,numero_cuenta)
							values($id,$id_caja,$proveedor,$tipo_egreso,$monto,'$comentarios','$usuario','$fecha_creacion','$item',$nro_cuenta)";
						if($db->Execute($query)or die($db->ErrorMsg()."consulta $query <br>")){
							if($_POST["select_proveedor"]) $prov_c=$_POST["select_proveedor"];
							elseif($_POST["id_proveedor_pago"]) $prov_c=$_POST["id_proveedor_pago"];
							//traemos el simbolo de la moneda
							//(Esto se puede usar para evitar consultas identicas
							//a esta, mas abajo. Buscar $simbolo. Por razones de tiempo no se hace ese cambio ahora)
							$sql="select simbolo from moneda where id_moneda=$moneda";
							$res_a=sql($sql,"<br>Error al traer simbolo moneda para cuenta default<br>") or fin_pagina();
							$simbolo=$res_a->fields["simbolo"];
							//traemos el nombre del distrito de esta caja
							//(Esto se puede usar para evitar consultas identicas
							//a esta, mas abajo. Buscar $district. Por razones de tiempo no se hace ese cambio ahora)
							$query="select nombre from distrito where id_distrito=$distrito";
              $dis_g=sql($query,"<br>Error Al traer nombre de distrito para cuenta default<br>") or fin_pagina();
		          $district=$dis_g->fields['nombre'];

							$tipo_pago="Pago en efectivo por $simbolo ".formato_money($monto)." para caja $district";
							//traemos el nombre del proveedor
							//(Esto se puede usar para evitar consultas identicas
							//a esta, mas abajo. Buscar $nombre_proveedor. Por razones de tiempo no se hace ese cambio ahora)
							$query="select razon_social from proveedor where id_proveedor=$prov_c";
							$prov_n=sql($query) or fin_pagina();

							//controla la cuenta por default del proveedor, para saber
							//si debe avisar por MAIL que se uso una cuenta que no
							//es la que el proveedor tiene por default
							cuenta_proveedor_default($prov_c,$nro_cuenta,"$tipo_pago",$prov_n->fields['razon_social']);

					   	//Si registro el pago , vemos que el monto > 1000
					   	//Envia el mail
					   	if ($monto>=1000){
					   		$sql="select razon_social from proveedor where id_proveedor=$proveedor";
					   		$res=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
					   		$nombre_proveedor=$res->fields["razon_social"];
					   		$sql="select simbolo from moneda where id_moneda=$moneda";
					   		$res=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
					   		$simbolo=$res->fields["simbolo"];
					   		$mail="juanmanuel@pcpower.com.ar,corapi@pcpower.com.ar,noelia@pcpower.com.ar";
					   		// $mail="fernando@pcpower.com.ar";
					   		$asunto="Pago en efectivo mayor a $simbolo 1000, cuenta nro:$nro_cuenta";
					   		$contenido .="La cuenta nro:$nro_cuenta\n";
					   		$contenido .="El usuario que registro el pago es: $usuario\n";
					   		$contenido .="Proveedor: $nombre_proveedor\n";
                $contenido .="Items: $item\n";
					   		$contenido .="Monto: $simbolo $monto\n";
					   		if (!ereg("Banco|banco|Banca|Bank",$nombre_proveedor))
						   		enviar_mail($mail,$asunto,$contenido,$nombre_archivo,$path_archivo,$type);
					   	}
	            ///////////////////////////////Por Aca tengo que meter codigo broggi/////////////////////////////////
					    /////////Esto es si viene de la pagina de pagar sueldo///////////////////////////////////////////////
					    if ($_POST['pagina_viene']=="pago_sueldo"){
								$actualizar="update personal.sueldos set id_ingreso_egreso=$id, estado_pagado=1 where id_sueldo=".$_POST['id_sueldo'];
							  $consulta_actualizar=sql($actualizar) or fin_pagina();//para poner en sueldo que ya se pago
							  $cuentas=$parametros['cuentas'];
							  $cuotas=$parametros['cuotas'];
							  $cantidad_cuentas=$parametros['cantidad_cuentas'];
							  $contador=1;
							  while ($contador<=$cantidad_cuentas){
							  	$control_ultimo=0;
							    $sql_cuotas="select id_cuota, id_cuenta from cuota where id_cuenta=".$cuentas[$contador]." and estado=1 order by id_cuota";
							    $consulta_cuotas=sql($sql_cuotas) or fin_pagina();
							    $sql_cuenta="select * from personal.cuenta where id_cuenta=".$cuentas[$contador];//obtengo la cuenta para modificarla
							    $consulta_cuenta=sql($sql_cuenta) or fin_pagina();
							    $pagos_restantes=$consulta_cuenta->fields['pagos_restantes'];
							    $monto_adeudado=$consulta_cuenta->fields['monto_adeudado'];
							    $consulta_cuotas->MoveLast();//obtengo todas las cuota impagas asociadas a esa cuenta
							    $ultima_cuota=$consulta_cuotas->fields['id_cuota'];//la ultima cuota de la cuenta
							    $cuotas_numero=$cuotas[$cuentas[$contador]];//asigno la primera posicion del arreglo cuotas
							    if ($cuotas_numero['cuota_inicio']!=0){
							    	$cuota_inicio=$cuotas_numero['cuota_inicio'];
							      while ($cuota_inicio<=$cuotas_numero['cuota_final']){
							      	$sql_2="update personal.cuota set estado=0, id_sueldo=".$_POST['id_sueldo']." where id_cuota=".$cuotas_numero[$cuota_inicio];
							        $consulta_sql_2=sql($sql_2) or fin_pagina();
							        if ($cuotas_numero[$cuota_inicio]==$ultima_cuota){//esto es para saver si es la ultima cuota de esa cuenta
							        	$control_ultimo=1;
							        }
							        $cuota_inicio++;
							      }
							    }//en caso de que tenga que pagar una cuota de esa cuenta
							    if ($control_ultimo==1){
										$sql="update personal.cuenta set estado=5 where id_cuenta=".$cuentas[$contador];
							      $ejecutar=sql($sql) or fin_pagina();
							      $query="select nextval('log_cuenta_id_log_cuenta_seq') as id";
                    $id_ie=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de log_cuenta");
							      $sql_log_cuenta="insert into personal.log_cuenta (id_log_cuenta,estado_cuenta,usuario,fecha,id_cuenta)
							      	values(".$id_ie->fields['id'].",5,'$usuario','$fecha_creacion',".$cuentas[$contador].")";
							      $ejecutar_insert=sql($sql_log_cuenta) or fin_pagina();
							    }
									$contador++;
							  }//del while para las cuentas
							}//del if que viene de pagina_viene para saber si hago los cambio en los adelantos
				     	/////////Esto es si viene de la pagina de cheques diferidos///////////////////////////////////////////////
							//if ($_POST['pagina_viene']=="cheques diferidos"){}//del if que viene de pagina_viene para saber si hago los cambio en los cheques diferidos
							$todo_ok=1;
							//inserto el egreso tipo deposito en el banco correspondiente
							//por parametros desde la ventana de OC
							if ($_POST['idbanco'] ){
								$q ="INSERT INTO bancos.depósitos ";
	        			$q.= "(IdBanco,FechaDepósito,IdTipoDep,ImporteDep,comentario) ";
	        			$q.= "VALUES ({$_POST['idbanco']},'$fecha',{$_POST['select_tipodep_banco']},$monto,'$item')";
	        			sql($q) or fin_pagina();
							}
							put_status("egreso",3,"black");
							$status=1;//la proxima vez que se apriete Guardar, sera una actualizacion
				   	}else{
				   		$todo_ok=0;
							put_status("egreso",5,"black");
							$status=0;//como hubo error, sigue siendo insersion la proxima
						}
			    }elseif($ie=="ingreso"){
						//Esto es si inserta un nuevo tipo de cuetna
						if ($_POST["ch_nueva_cuenta"]==1){
							$nombre=$_POST['nombre_nueva_cuenta'];
							$query="insert into tipo_cuenta_ingreso (nombre) values ('$nombre')";
						  $db->execute($query) or die($sql);
							$query="select id_cuenta_ingreso from caja.tipo_cuenta_ingreso ";
						  $resultado=$db->execute($query) or die($sql);
						  $cantid=$resultado->RecordCount();
						  $resultado->MoveLast();
						  $tipo_cuenta_ingreso=$resultado->fields['id_cuenta_ingreso'];
						}
            //Verifico que no se haya hecho un ingreso similar en los dos dias habiles anteriores
            // código 1215
						if (!$_POST['forcesave'] && (!$g_pagina_bancos_ing_ch) && $r=chk_similar_ie("ingreso",$distrito,ereg_replace("^(.*)-(.*)-(.*)$","\\3/\\2/\\1",$fecha),$monto,-2)){
							$status=0;//para inserte los datos y no actualice
							$tipo_valor="post";
							$_POST['postear']="sipok";//para que retenga los valores
							put_status("ingreso",12,"black",$r);
							$todo_ok=0;
							return $status;
						}
					  //traemos y reservamos el id a insertar
					  $query="select nextval('ingreso_egreso_id_ingreso_egreso_seq') as id";
						$id_ie=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de ingreso/egreso");
            $id=$id_ie->fields['id'];
					  $query="INSERT into caja.ingreso_egreso (id_ingreso_egreso,id_caja,id_entidad,id_tipo_ingreso,monto,comentarios,usuario,fecha_creacion,item,id_cuenta_ingreso)
							values($id,$id_caja,$entidad,$tipo_ingreso,$monto,'$comentarios','$usuario','$fecha_creacion','$item',$tipo_cuenta_ingreso)";
						if($db->Execute($query) or die($db->ErrorMsg()."<br>".$query)) {
					  /////////////////////////////////////////////////////////////////////////////////////////////////////
					/*	  if ($_POST['pagina_viene']=='lic_cobranzas')  {
						  	$valores_fact=array();
						  	$valores_fact['monto']=$_POST['monto_ant'];
						  	$valores_fact['tipo_ing']=$_POST['tipo_ingreso_ant'];
						  	$valores_fact['moneda']=$_POST['moneda_ant'];
						  	$valores_fact['entidad']=$_POST['entidad_ant'];
					  		$valores_fact['cuenta']=$_POST['tipo_cuenta_ant'];
					  		$valores_fact['dolar']=$_POST['cotizacion_dolar'];
						  	$valores=array();
						  	$valores_ing['monto']=$monto;
						  	$valores_ing['tipo_ing']=$tipo_ingreso;
						  	$valores_ing['moneda']=$moneda;
						  	$valores_ing['entidad']=$entidad;
						  	$valores_ing['cuenta']=$tipo_cuenta_ingreso;
					  		$valores_ing['dolar']=$dolar_actual;

						  	$sql_cob="update cobranzas set id_ingreso_egreso=$id, cotizacion_dolar=$dolar_actual where id_cobranza=$id_cobranza ";
						  	if (sql($sql_cob) or fin_pagina()) {
						  		datos_mail($id,$nro_factura,$distrito,$id_licitacion,$valores_fact,$valores_ing);
						  	}
        	   	}	*/
							$todo_ok=1;
							put_status("ingreso",3,"black");
							$status=1;//la proxima vez que se apriete Guardar, sera una actualizacion
						}else{
							$todo_ok=0;
							put_status("ingreso",5,"black");
							$status=0;//como hubo error, sigue siendo insersion la proxima
						}
					}//de elseif($ie=="ingreso")
					//si se crea un ingreso/egreso de una caja cerrada, avisamos por mail
    	    if($todo_ok){//si la caja esta cerrada y el monto es diferente al guardado en la BD
					  //entonces mandamos un mail de aviso
		    	  if($permiso_cerrada){//avisamos por mail
				  		/*$query="select nombre from distrito where id_distrito=$distrito";
				   		$dis_g=$db->Execute($query) or die($db->ErrorMsg()."<br>Error Al traer nombre de distrito");
					   	$district=$dis_g->fields['nombre'];*/
					   	$asunto="Se insertó un $ie para una caja ya cerrada de $district";
					   	$mail_header="";
					   	$mail_header .= "MIME-Version: 1.0";
					   	$mail_header .= "\nfrom: Sistema Inteligente de CORADIR <>";
				  	 	$mail_header .="\nTo: corapi@coradir.com.ar";
           		$mail_header .="\nTo: juanmanuel@coradir.com.ar";
					   	$mail_header .="\nTo: noelia@coradir.com.ar";
					   	$mail_header .= "\nContent-Type: text/plain";
					   	$mail_header .= "\nContent-Transfer-Encoding: 8bit";
					   	$mail_header .= "\n\nSe ha insertado el $ie Nº $id que pertenece a una caja cerrada previamente.\n";
					   	$mail_header .= "\n\nDetalle del $ie: \n";
				  	 	$mail_header .= detalle_ie($id,$ie);
					   	$mail_header .= "\n\n\nUsuario: ".$_ses_user['name']."\nCaja: $district";
  	         	//enviamos efectivamente el mail una vez que se realizo con exito el ingreso o egreso
			       	mail ("",$asunto,"",$mail_header);
		  			}
        	}
        	    if($ie=="egreso")
        	  	 $reenviar_por_imputacion=1;
			  }//del if(!$caja_cerrada && $_POST['editar']=="")
		    elseif(!$caja_cerrada && $_POST['editar']=="ok"){//actualizamos el ingreso_egreso
					//si la caja esta cerrada y el monto es diferente al guardado en la BD
			 		//entonces mandamos un mail de aviso
				 	$query="select monto,simbolo,id_moneda from ingreso_egreso join caja using(id_caja) join moneda using(id_moneda) where id_ingreso_egreso=$id";
				 	$monto_g=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el monto guardado");
					if($permiso_cerrada && ($monto!=$monto_g->fields['monto'] || $moneda!=$monto_g->fields['id_moneda'])){
			  	 	$query="select nombre from distrito where id_distrito=$distrito";
			   		$dis_g=$db->Execute($query) or die($db->ErrorMsg()."<br>Error Al traer nombre de distrito");
				   	$district=$dis_g->fields['nombre'];
				 		//avisamos por mail
			  		$asunto="Se modificó el $ie de una caja ya cerrada de $district";
		  			$mail_header="";
			  		$mail_header .= "MIME-Version: 1.0";
				  	$mail_header .= "\nfrom: Sistema Inteligente de CORADIR <>";
				  	$mail_header .="\nTo: corapi@coradir.com.ar";
    		    $mail_header .="\nTo: juanmanuel@coradir.com.ar";
		  			$mail_header .="\nTo: noelia@coradir.com.ar";
		  			$mail_header .= "\nContent-Type: text/plain";
				  	$mail_header .= "\nContent-Transfer-Encoding: 8bit";
				  	$mail_header .= "\n\nSe ha modificado el $ie Nº $id que pertenece a una caja cerrada previamente.\n";
				  	$query="select simbolo from moneda where id_moneda=$moneda";
		  		 	$m_g=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer simbolo de moneda");
		  			if($monto != $monto_g->fields['monto'])
		   				$mail_header .= "\nMonto Actual: ".$monto_g->fields['simbolo']." $monto - Monto Anterior: ".$m_g->fields['simbolo']." ".$monto_g->fields['monto']."\n";
					  if($moneda!=$monto_g->fields['id_moneda']){
				   		$mail_header .= "\nMoneda Actual: ".$m_g->fields['simbolo']." - Moneda Anterior: ".$monto_g->fields['simbolo']."\n";
				  	}
		  			$mail_header .= "\n\nDetalle del $ie: \n";
		  			$mail_header .= detalle_ie($id,$ie);
			  		$mail_header .= "\n\n\nUsuario: ".$_ses_user['name']."\nCaja: $district";
					}else{
						$query="select id_caja,fecha,cerrada from caja where fecha='$fecha' and id_distrito=$distrito and id_moneda=".$monto_g->fields['id_moneda'];
			  		$caja_query1=$db->Execute($query) or die ($db->ErrorMsg().$query);
		  			$permiso_cerrada=$caja_query1->fields['cerrada'];
			  		if($permiso_cerrada && ($monto!=$monto_g->fields['monto'] || $moneda!=$monto_g->fields['id_moneda'])){
			   			$query="select nombre from distrito where id_distrito=$distrito";
			   			$dis_g=$db->Execute($query) or die($db->ErrorMsg()."<br>Error Al traer nombre de distrito");
			  	 		$district=$dis_g->fields['nombre'];
			 				//avisamos por mail
			   			$asunto="Se modificó el $ie de una caja ya cerrada de $district";
				   		$mail_header="";
			  	 		$mail_header .= "MIME-Version: 1.0";
			   			$mail_header .= "\nfrom: Sistema Inteligente de CORADIR <>";
			  	 		$mail_header .="\nTo: corapi@coradir.com.ar";
  	      	  $mail_header .="\nTo: juanmanuel@coradir.com.ar";
			   			$mail_header .="\nTo: noelia@coradir.com.ar";
				   		$mail_header .= "\nContent-Type: text/plain";
			  	 		$mail_header .= "\nContent-Transfer-Encoding: 8bit";
							$mail_header .= "\n\nSe ha modificado el $ie Nº $id que antes pertenecia a una caja cerrada previamente.\n";
			  	    $query="select simbolo from moneda where id_moneda=$moneda";
			   			$m_g=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer simbolo de moneda");
			   			if($monto != $monto_g->fields['monto'])
		  	  			$mail_header .= "\nMonto Actual: ".$monto_g->fields['simbolo']." $monto - Monto Anterior: ".$m_g->fields['simbolo']." ".$monto_g->fields['monto']."\n";
							if($moneda!=$monto_g->fields['id_moneda']){
			    			$mail_header .= "\nMoneda Anterior: ".$monto_g->fields['simbolo']." - Moneda Actual: ".$m_g->fields['simbolo']."\n";
			   			}
			  	 		$mail_header .= "\n\nDetalle del $ie: \n";
			   			$mail_header .= detalle_ie($id,$ie);
			   			$mail_header .= "\n\n\nUsuario: ".$_ses_user['name']."\nCaja: $district";
					  }//de if($permiso_cerrada)
			 		}//del else
					if($ie=="egreso"){
					  //$query="select numero_cuenta from tipo_cuenta where concepto='$concepto' and plan='$plan'";
				  	//$nro_c=$db->Execute($query) or die($db->ErrorMsg().$query);
					  //$nro_cuenta=$nro_c->fields['numero_cuenta'];
  	  	    $nro_cuenta=$cuenta;
    	  	  //si el check esta activado, creamos una nueva cuenta para el proveedor
		  			if($_POST['chk_cuenta']=="guardar"){
					  	$query="select id_cuenta from cuentas where id_proveedor=$proveedor and numero_cuenta=$nro_cuenta";
			   			$concepto_query=$db->Execute($query) or die($db->ErrorMsg().$query);
				   		if($concepto_query->RecordCount()==0){
				    		$query="insert into cuentas (id_proveedor,numero_cuenta) values($proveedor,$nro_cuenta)";
				    		$db->Execute($query) or die($db->ErrorMsg().$query);
		  		 		}
		  			} //del if del check
					  //query para actualizar los datos el egreso cargado
					  $query="update caja.ingreso_egreso set id_caja=$id_caja,id_proveedor=$proveedor,id_tipo_egreso=$tipo_egreso,monto=$monto,comentarios='$comentarios',item='$item',numero_cuenta=$nro_cuenta where id_ingreso_egreso=$id";
					  if($db->Execute($query) or fin_pagina()){
					  	$todo_ok=1;
		   				put_status("egreso",4,"black");
		   				$status=1;
				  	}else{
				  		$todo_ok=0;
				   		put_status("egreso",6,"black");
		  		 		$status=1;//a pesar del error seguimos en modo actualizacion
		  			}
					}elseif($ie=="ingreso"){//query para actualizar los datos el ingreso cargado
					 	if ($_POST["ch_nueva_cuenta"]==1){
							$nombre=$_POST['nombre_nueva_cuenta'];
							$query="insert into tipo_cuenta_ingreso (nombre) values ('$nombre')";
					  	$db->execute($query) or die($sql);
							$query="select id_cuenta_ingreso from caja.tipo_cuenta_ingreso ";
							$resultado=$db->execute($query) or die($sql);
							$cantid=$resultado->RecordCount();
							$resultado->MoveLast();
							$tipo_cuenta_ingreso=$resultado->fields['id_cuenta_ingreso'];
						}
						$query="update caja.ingreso_egreso set";
						$query.=" id_caja=$id_caja,id_entidad=$entidad,";
						$query.=" id_tipo_ingreso=$tipo_ingreso,monto=$monto,";
						$query.=  "comentarios='$comentarios',item='$item',";
						$query.="id_cuenta_ingreso=$tipo_cuenta_ingreso where ";
						$query.="id_ingreso_egreso=$id";
						if($db->Execute($query) or fin_pagina()){
							$todo_ok=1;
						 	put_status("ingreso",4,"black");
				  		$status=1;
						}else{
				 			$todo_ok=0;
				  		put_status("ingreso",6,"black");
						 	$status=1;//a pesar del error seguimos en modo actualizacion
					 	}
		    	}//del elseif
		  	  //enviamos efectivamente el mail una vez que se realizo con exito el ingreso o egreso
		  	  if($todo_ok){
  		  		if($permiso_cerrada && ($monto!=$monto_g->fields['monto'] || $moneda!=$monto_g->fields['id_moneda']))
				  	mail ("",$asunto,"",$mail_header);
					}
  			}//de elseif(!$caja_cerrada && $_POST['editar']=="ok")
		  	/***********************************************************
			    Llamamos a la funcion de imputar pago
  			************************************************************/
			  if($ie=="egreso")
			  {
				  $pago[]=array();
		  		$pago["tipo_pago"]="id_ingreso_egreso";
		  		$pago["id_pago"]=$id;
				  $id_imputacion=$_POST["id_imputacion"];
  		  	imputar_pago($pago,$id_imputacion,$_POST["text_fecha"]);
				}
			}else{//de if($res_cajas->fields['id_caja']=="")
		  	put_status($_POST['text_fecha'],11,"black");//avisamos que hay una caja cerrada con fecha posterior a la del ingreso o egreso
	  	}
  	}//de if(!$caja_cerrada)
  	elseif ($caja_cerrada || $prohibir_cambio_caja_cerrada)
	{
		if(($monto_anterior_guardado=="" || $monto_anterior_guardado==$monto) && ($moneda_anterior_guardada=="" || $moneda_anterior_guardada==$moneda) )
  		 put_status($_POST['text_fecha'],7,"black");//avisamos que la caja ya esta cerrada
  		else
  		 put_status($_POST['text_fecha'],14,"black");//avisamos que no se puede cambiar el monto de un ingreso/egreso de caja cerrada
	}

	}
	else
	{//del control de la fecha
		put_status("$ie",8,"black");
	}
	$query="select max(id_ingreso_egreso) as max from caja.ingreso_egreso";
	$id_q=$db->Execute($query) or die($db->ErrorMsg().$query);
	$id=$id_q->fields['max'];

	$db->CompleteTrans();

	return $status;
} //fin de guardar_ie


//************************************************************************
//                         funciones para listados
//************************************************************************

function put_listado($ie,$distrito,$moneda) {
global $db,$add_where;
global $bgcolor1;
global $bgcolor2;
global $desde,$hasta,$fechas;
global $parametros; //cuando viene de cobranzas
global $desde_m,$hasta_m,$montos;
global $avanzada;
global $id_t_cuenta,$id_t_ingreso,$id_t_egreso;
global $entidades,$entidad,$letra;
global $orden_default,$orden_by;

cargar_calendario();
if ($ie=="ingreso")
{
 $sql_tmp="select caja.fecha,ingreso_egreso.id_ingreso_egreso,ingreso_egreso.item,ingreso_egreso.monto,ingreso_egreso.comentarios,
           moneda.simbolo,caja.id_moneda,entidad.nombre as nombre,tipo_cuenta_ingreso.nombre as cuenta_ingreso
           from caja join ingreso_egreso using(id_caja) left join entidad using(id_entidad) join moneda using(id_moneda)
           left join caja.tipo_cuenta_ingreso using(id_cuenta_ingreso)
           ";
 $where_tmp="(caja.id_distrito=$distrito and id_tipo_egreso isnull $add_where)";
 $contar="select count(*) from ingreso_egreso join caja using(id_caja) where id_tipo_egreso isnull and caja.id_distrito=$distrito";
 $title="Cliente";
 $orden = array(
		"default_up" => "$orden_by",
		"default" => "$orden_default",
		"1" => "ingreso_egreso.id_ingreso_egreso",
		"2" => "caja.fecha",
		"3" => "ingreso_egreso.item",
		"4" => "ingreso_egreso.monto",
	);
 $filtro = array(
		"caja.fecha" => "Fecha",
		"entidad.nombre" => "Cliente",
		"ingreso_egreso.item" => "Item",
		"ingreso_egreso.id_ingreso_egreso" => "ID",
		"ingreso_egreso.monto" => "Monto",
		"ingreso_egreso.comentarios" => "Comentarios"
	);
}
elseif ($ie=="egreso")
{$sql_tmp="select caja.fecha,ingreso_egreso.id_ingreso_egreso,ingreso_egreso.item,ingreso_egreso.monto,ingreso_egreso.comentarios,moneda.simbolo,caja.id_moneda,proveedor.razon_social as nombre from caja join ingreso_egreso using(id_caja) join proveedor using(id_proveedor) join moneda using(id_moneda) ";
 $where_tmp="(caja.id_distrito=$distrito and id_tipo_ingreso isnull $add_where)";
 $contar="select count(*) from ingreso_egreso join caja using(id_caja) where id_tipo_ingreso isnull and caja.id_distrito=$distrito";
 $title="Proveedor";
 $orden = array(
		"default_up" => "$orden_by",
		"default" => "$orden_default",
		"1" => "ingreso_egreso.id_ingreso_egreso",
		"2" => "caja.fecha",
		"3" => "ingreso_egreso.item",
		"4" => "ingreso_egreso.monto",
	);
 $filtro = array(
		"caja.fecha" => "Fecha",
		"proveedor.razon_social" => "Proveedor",
		"ingreso_egreso.item" => "Item",
		"ingreso_egreso.id_ingreso_egreso" => "ID",
		"ingreso_egreso.monto" => "Monto",
		"ingreso_egreso.comentarios" => "Comentarios"
	);
}
elseif($ie=="caja")
{$sql_tmp="SELECT moneda.simbolo,caja.fecha,caja.saldo_total,caja.cerrada,caja.id_caja FROM caja join moneda using(id_moneda)";
 $where_tmp="(caja.id_distrito=$distrito $add_where)";
 $contar="select count(*) from caja where id_distrito=$distrito";
 $title="Proveedor";
 $orden = array(
		"default_up" => "$orden_by",
		"default" => "$orden_default",
		"1" => "caja.id_caja",
		"2" => "caja.fecha",
		"3" => "caja.cerrada",
	);
 $filtro = array(
		"caja.id_caja" => "ID",
		"caja.fecha" => "Fecha",
		"caja.saldo_total" => "Saldo Total",
		"caja.usuario" => "Usuario (solo si esta cerrada)",
	);
}

$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "monto",
 		"mask" => array ("\$","U\$S")
);

echo "<center>";

if($_POST['keyword'] || $keyword || $_POST['fechas'] || $_POST['select_moneda']!=-1 || $_POST['select_estado']!= -1 )
     $contar="buscar";
list($sql,$total_lic,$link_pagina,$up,$suma) = form_busqueda($sql_tmp,$orden,$filtro,array("distrito"=>$distrito),$where_tmp,$contar,$sumas);
$resultado = sql($sql,"Error en busqueda: $sql");
?>
&nbsp;<b>Avanzada</b> <INPUT name="avanzada" type='checkbox' value="1" <? if ($avanzada==1) echo 'checked'?> onclick='activar(this,document.all.div)'>
&nbsp;<input type="submit" name=form_busqueda value="Buscar" style="font-family :
georgia, garamond, serif; font-size : 10px; font-weight : bold; color : white; border : solid 3px white;
background-color: blue; cursor: hand">
<div id='div'  style='display:<?if ($avanzada==1) echo "all"; else echo "none";?>'><br>
<table width='95%' border='0' bgcolor='#E0E0E0' cellspacing='0' class="bordes">
<TR>
	<TD width="40%" rowspan="3">
	<TABLE id="ma" width="100%">
	<TR><TD width="40%">
 <b> Moneda </b>
 </TD><TD align="right">
  <?combo_moneda('select_moneda',"Todas");?>
  </TD></TR>
  <?if($ie=="caja")
  {?>
    <TR>
    <TD align="center">
   <b>Estado</b>
 	</TD><TD>
    <select name="select_estado" style="width:100%">
     <option value=-1 <?if($_POST['select_estado']==-1)echo "selected"?>>Todas</option>
     <option value=1 <?if($_POST['select_estado']==1)echo "selected"?>>Abiertas</option>
     <option value=2 <?if($_POST['select_estado']==2)echo "selected"?>>Cerradas</option>
    </select>
    </TD></TR>
 <?}
 if ($ie=="ingreso"){//combo de selección del tipo de ingreso
 	?>
    <TR>
    <TD align="center">
   <b>T. Cuenta</b>
 	</TD><TD align="right">
    <select name="select_t_cuenta" style="width:100%">
     <option value=-1 <?if($id_t_cuenta == -1)echo "selected";?>>Todas</option>
	<?
	$res_t_cuenta = sql("Select * from tipo_cuenta_ingreso","Error en combo tipo de cuenta");
	while (!$res_t_cuenta->EOF){
		$text_t_cuenta = $res_t_cuenta->fields["nombre"];
		$id_tipo_cuenta = $res_t_cuenta->fields["id_cuenta_ingreso"];
		if($id_tipo_cuenta == $id_t_cuenta)
     		echo "<option value=$id_tipo_cuenta selected>$text_t_cuenta</option>";
     		else
     		echo "<option value=$id_tipo_cuenta>$text_t_cuenta</option>";
     		$res_t_cuenta->MoveNext();
	}
     ?>
    </select>
    </TD></TR>
    <TR>
    <TD align="center">
   <b>T. Ingreso</b>
 	</TD><TD align="right">
    <select name="select_t_ingreso" style="width:100%" >
     <option value=-1 <?if($id_t_ingreso == -1)echo "selected";?>>Todas</option>
	<?
	$res_t_ingreso = sql("Select * from tipo_ingreso","Error en combo tipo de ingreso");
	while (!$res_t_ingreso->EOF){
		$text_t_ingreso = $res_t_ingreso->fields["nombre"];
		$id_tipo_ingreso = $res_t_ingreso->fields["id_tipo_ingreso"];
		if($id_tipo_ingreso == $id_t_ingreso)
     		echo "<option value=$id_tipo_ingreso selected title='$text_t_ingreso'>$text_t_ingreso</option>";
     		else
     		echo "<option value=$id_tipo_ingreso>$text_t_ingreso</option>";
     		$res_t_ingreso->MoveNext();
	}
     ?>
    </select>
    </TD></TR>
 <?}
 if ($ie=="egreso"){//combo de selección del tipo de egreso
 	?>
    <TR>
    <TD align="center">
   <b>T. Cuenta</b>
 	</TD><TD align="right">
    <select name="select_t_cuenta" style="width:100%">
     <option value=-1 <?if($id_t_cuenta == -1)echo "selected";?>>Todas</option>
	<?
	$res_t_cuenta = sql("Select * from tipo_cuenta","Error en combo tipo de cuenta");
	while (!$res_t_cuenta->EOF){
		$text_t_cuenta = $res_t_cuenta->fields["concepto"]."[".$res_t_cuenta->fields["plan"]."]";
		$id_tipo_cuenta = $res_t_cuenta->fields["numero_cuenta"];
		if($id_tipo_cuenta == $id_t_cuenta)
     		echo "<option value=$id_tipo_cuenta selected>$text_t_cuenta</option>";
     		else
     		echo "<option value=$id_tipo_cuenta>$text_t_cuenta</option>";
     		$res_t_cuenta->MoveNext();
	}
     ?>
    </select>
    </TD></TR>
    <TR>
    <TD align="center">
   <b>T. Egreso</b>
 	</TD><TD align="right">
    <select name="select_t_egreso" style="width:100%">
     <option value=-1 <?if($id_t_egreso == -1)echo "selected";?>>Todas</option>
	<?
	$res_t_egreso = sql("Select * from tipo_egreso","Error en combo tipo de egreso");
	while (!$res_t_egreso->EOF){
		$text_t_egreso = $res_t_egreso->fields["nombre"];
		$id_tipo_egreso = $res_t_egreso->fields["id_tipo_egreso"];
		if($id_tipo_egreso == $id_t_egreso)
     		echo "<option value=$id_tipo_egreso selected>$text_t_egreso</option>";
     		else
     		echo "<option value=$id_tipo_egreso>$text_t_egreso</option>";
     		$res_t_egreso->MoveNext();
	}
     ?>
    </select>
    </TD></TR>
 <?}
 ?>
 	<TR>
 	<TD>
 	Ordenar Por:
 	</TD>
 	<TD align="right">
 	<SELECT name="orden">
	<?if($ie!="caja"){?>
 	<OPTION <?if($orden_default == 1)echo "selected";?> value="1">ID</OPTION>
 	<OPTION <?if($orden_default == 2)echo "selected";?> value="2">Fecha</OPTION>
 	<OPTION <?if($orden_default == 3)echo "selected";?> value="3">Item</OPTION>
 	<OPTION <?if($orden_default == 4)echo "selected";?> value="4">Monto</OPTION>
 	<?} else {?>
 	<OPTION <?if($orden_default == 1)echo "selected";?> value="1">ID Caja</OPTION>
 	<OPTION <?if($orden_default == 2)echo "selected";?> value="2">Fecha</OPTION>
 	<OPTION <?if($orden_default == 3)echo "selected";?> value="3">Estado</OPTION>
 	<?}?>
 	</SELECT>
 	</TD>
 	</TR>
 	<TR>
 	<TD colspan="2" align="right">
 	Orden Ascendente:
	<INPUT type="checkbox" name="orden_by" <?if($orden_by==1) echo "checked"?> value="1">
 	</TD>
 	</TR>
 	</TABLE>
 	</TD>
 <TD width="60%">

  <table id="ma">
 <tr>
  <td align="left" colspan="2"> <input name="fechas" type="checkbox" value="1" <? if ($fechas==1) echo 'checked'?> onclick="if (!this.checked) { document.all.desde.value='';document.all.hasta.value='';} " > <b>Entre fechas: </b></td>
  <td colspan="2"> <b>Desde: </b> <input type='text' size=10 name='desde' value='<?=$desde?>' readonly>
	                <? echo link_calendario('desde');?>
	  <b>Hasta: </b><input type='text' size=10 name='hasta' value='<?=$hasta?>' readonly>
                    <? echo link_calendario('hasta'); ?>
  </td>
 </tr>
<TR>
  <td align="left" colspan="2"> <input name="montos" type="checkbox" value="1" <? if ($montos==1) echo 'checked'?> onclick="if (!this.checked) { document.all.desde_m.value='';document.all.hasta_m.value='';} " > <b>Entre montos: </b></td>
  <td colspan="2"> <b>Mínimo: </b> <input type='text' size=10 name='desde_m' value='<?=$desde_m?>' onkeypress="return filtrar_teclas(event,'0123456789.')">
	  <b>Máximo: </b><input type='text' size=10 name='hasta_m' value='<?=$hasta_m?>' onkeypress="return filtrar_teclas(event,'0123456789.')">
  </td>
</tr>
 <?if($ie=="ingreso")
  {?>
	<TR>
  	<td align="left" colspan="4">
  	<input name="entidades" type="checkbox" value="1" <? if ($entidades==1) echo 'checked'?> onclick="document.form1.submit();"> <b>Por cliente: </b>
  	</td>
	</tr>
  	<?
  	if ($entidades) {
  		$sql_entidades = "Select id_entidad,nombre from entidad where nombre ilike '$letra%' order by nombre asc";
  		$result_entidades = sql($sql_entidades,"Error en $sql_entidades");
  	?>
	<TR>
  	<td width="20%">
  	Comienza con:
  	</td>
  	<td width="10%">
  	<SELECT name="letras" onchange="document.form1.submit();">
  	<OPTION value="a"<?if($letra == "a")echo "selected";?>>A</OPTION>
  	<OPTION value="b"<?if($letra == "b")echo "selected";?>>B</OPTION>
  	<OPTION value="c"<?if($letra == "c")echo "selected";?>>C</OPTION>
  	<OPTION value="d"<?if($letra == "d")echo "selected";?>>D</OPTION>
  	<OPTION value="e"<?if($letra == "e")echo "selected";?>>E</OPTION>
  	<OPTION value="f"<?if($letra == "f")echo "selected";?>>F</OPTION>
  	<OPTION value="g"<?if($letra == "g")echo "selected";?>>G</OPTION>
  	<OPTION value="h"<?if($letra == "h")echo "selected";?>>H</OPTION>
  	<OPTION value="i"<?if($letra == "i")echo "selected";?>>I</OPTION>
  	<OPTION value="j"<?if($letra == "j")echo "selected";?>>J</OPTION>
  	<OPTION value="k"<?if($letra == "k")echo "selected";?>>K</OPTION>
  	<OPTION value="l"<?if($letra == "l")echo "selected";?>>L</OPTION>
  	<OPTION value="m"<?if($letra == "m")echo "selected";?>>M</OPTION>
  	<OPTION value="n"<?if($letra == "n")echo "selected";?>>N</OPTION>
  	<OPTION value="ñ"<?if($letra == "ñ")echo "selected";?>>Ñ</OPTION>
  	<OPTION value="o"<?if($letra == "o")echo "selected";?>>O</OPTION>
  	<OPTION value="p"<?if($letra == "p")echo "selected";?>>P</OPTION>
  	<OPTION value="q"<?if($letra == "q")echo "selected";?>>Q</OPTION>
  	<OPTION value="r"<?if($letra == "r")echo "selected";?>>R</OPTION>
  	<OPTION value="s"<?if($letra == "s")echo "selected";?>>S</OPTION>
  	<OPTION value="t"<?if($letra == "t")echo "selected";?>>T</OPTION>
  	<OPTION value="u"<?if($letra == "u")echo "selected";?>>U</OPTION>
  	<OPTION value="v"<?if($letra == "v")echo "selected";?>>V</OPTION>
  	<OPTION value="w"<?if($letra == "w")echo "selected";?>>W</OPTION>
  	<OPTION value="x"<?if($letra == "x")echo "selected";?>>X</OPTION>
  	<OPTION value="y"<?if($letra == "y")echo "selected";?>>Y</OPTION>
  	<OPTION value="z"<?if($letra == "z")echo "selected";?>>Z</OPTION>
  	<select>
  	</td>
 	<TD width="10%">
  	Nombre:
  	</td>
  	<td width="60%">
  	<SELECT name="entidades_list" style="width:100%">
  	<OPTION value="-1" <?if($entidad == -1)echo "selected";?>>Todos</OPTION>
	<?
	while(!$result_entidades->EOF) {
		if ($entidad==$result_entidades->fields["id_entidad"])
			echo "<OPTION value='".$result_entidades->fields["id_entidad"]."' selected >".$result_entidades->fields["nombre"]."</OPTION>";
		else
			echo "<OPTION value='".$result_entidades->fields["id_entidad"]."'>".$result_entidades->fields["nombre"]."</OPTION>";
		$result_entidades->MoveNext();
	}?>
  	<select>
	</TD>
	</TR>
<?	}
  }?>

  <?if($ie=="egreso")
  {?>
	<TR>
  	<td align="left" colspan="4">
  	<input name="entidades" type="checkbox" value="1" <? if ($entidades==1) echo 'checked'?> onclick="document.form1.submit();"> <b>Por Proveedor: </b>
  	</td>
	</tr>
  	<?
  	if ($entidades) {
  		$sql_entidades = "Select id_proveedor,razon_social as nombre from proveedor where razon_social ilike '$letra%' order by nombre asc";
  		$result_entidades = sql($sql_entidades,"Error en $sql_entidades");
  	?>
	<TR>
  	<td width="20%">
  	Comienza con:
  	</td>
  	<td width="10%">
  	<SELECT name="letras" onchange="document.form1.submit();">
  	<OPTION value="a"<?if($letra == "a")echo "selected";?>>A</OPTION>
  	<OPTION value="b"<?if($letra == "b")echo "selected";?>>B</OPTION>
  	<OPTION value="c"<?if($letra == "c")echo "selected";?>>C</OPTION>
  	<OPTION value="d"<?if($letra == "d")echo "selected";?>>D</OPTION>
  	<OPTION value="e"<?if($letra == "e")echo "selected";?>>E</OPTION>
  	<OPTION value="f"<?if($letra == "f")echo "selected";?>>F</OPTION>
  	<OPTION value="g"<?if($letra == "g")echo "selected";?>>G</OPTION>
  	<OPTION value="h"<?if($letra == "h")echo "selected";?>>H</OPTION>
  	<OPTION value="i"<?if($letra == "i")echo "selected";?>>I</OPTION>
  	<OPTION value="j"<?if($letra == "j")echo "selected";?>>J</OPTION>
  	<OPTION value="k"<?if($letra == "k")echo "selected";?>>K</OPTION>
  	<OPTION value="l"<?if($letra == "l")echo "selected";?>>L</OPTION>
  	<OPTION value="m"<?if($letra == "m")echo "selected";?>>M</OPTION>
  	<OPTION value="n"<?if($letra == "n")echo "selected";?>>N</OPTION>
  	<OPTION value="ñ"<?if($letra == "ñ")echo "selected";?>>Ñ</OPTION>
  	<OPTION value="o"<?if($letra == "o")echo "selected";?>>O</OPTION>
  	<OPTION value="p"<?if($letra == "p")echo "selected";?>>P</OPTION>
  	<OPTION value="q"<?if($letra == "q")echo "selected";?>>Q</OPTION>
  	<OPTION value="r"<?if($letra == "r")echo "selected";?>>R</OPTION>
  	<OPTION value="s"<?if($letra == "s")echo "selected";?>>S</OPTION>
  	<OPTION value="t"<?if($letra == "t")echo "selected";?>>T</OPTION>
  	<OPTION value="u"<?if($letra == "u")echo "selected";?>>U</OPTION>
  	<OPTION value="v"<?if($letra == "v")echo "selected";?>>V</OPTION>
  	<OPTION value="w"<?if($letra == "w")echo "selected";?>>W</OPTION>
  	<OPTION value="x"<?if($letra == "x")echo "selected";?>>X</OPTION>
  	<OPTION value="y"<?if($letra == "y")echo "selected";?>>Y</OPTION>
  	<OPTION value="z"<?if($letra == "z")echo "selected";?>>Z</OPTION>
  	<select>
  	</td>
 	<TD width="10%">
  	Nombre:
  	</td>
  	<td width="60%">
  	<SELECT name="entidades_list" style="width:100%">
  	<OPTION value="-1" <?if($entidad == -1)echo "selected";?>>Todos</OPTION>
	<?
	while(!$result_entidades->EOF) {
		if ($entidad==$result_entidades->fields["id_proveedor"])
			echo "<OPTION value='".$result_entidades->fields["id_proveedor"]."' selected >".$result_entidades->fields["nombre"]."</OPTION>";
		else
			echo "<OPTION value='".$result_entidades->fields["id_proveedor"]."'>".$result_entidades->fields["nombre"]."</OPTION>";
		$result_entidades->MoveNext();
	}?>
  	<select>
	</TD>
	</TR>
<?	}
  }?>
</table>

 </td>
</tr></table> </div><br>
<SCRIPT>
function activar(obj1,obj2){
	if (obj1.checked) {
		obj2.style.display = 'block';
	} else {
		obj2.style.display= 'none';
	}
}
</SCRIPT>

<?
//vemos en que distrito estamos
$query="select nombre from distrito where id_distrito=$distrito";
$res_dist=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

if($ie!='caja') {

	echo "
<center>

<table width=95% cellspacing=2 class='bordes'><tr id=ma>
	<td align=left colspan=2><b>
	Total:</b> $total_lic ".$ie."s</td>
	<td align=left><b>
	Suma:</b> $suma </td>
	<td align=right colspan=3>$link_pagina</td>
	</tr>
<!--</table>
<table width='95%' border='0' cellspacing='2'>-->
<tr title='Vea comentarios de los ".$ie."' id=mo>
<td width='1%'><a id=mo  href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"1","up"=>$up,"distrito"=>$distrito))."'><b>ID</b></a></td>
<td width='5%'><a id=mo href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"2","up"=>$up,"distrito"=>$distrito))."'><b>Fecha</b></a></td>
<td width='40%'><a id=mo href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"3","up"=>$up,"distrito"=>$distrito))."'><b>Item</b></a></td>
<td width='15%'><a id=mo href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"4","up"=>$up,"distrito"=>$distrito))."'><b>Montos</b></a></td>
<td width='25%'><b>$title</b></td>
<td width='10%'><b>Cuenta</b></td>
</tr>";
 }
else {     //si es caja

echo "
<center>

<table width=95% cellspacing=2 class='bordes'><tr id=ma>
	<td align=left colspan=2><b>
	Total:</b> $total_lic cajas</td>
	<td align=right colspan=2>$link_pagina</td>
	</tr>
<!--</table>
<table width='95%' border='0' cellspacing='2'>-->
<tr id=mo>
<td width='1%'><a id=mo href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"1","up"=>$up,"distrito"=>$distrito))."'><b>ID</b></a></td>
<td width='10%'><a id=mo href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"2","up"=>$up,"distrito"=>$distrito))."'><b>Fecha</b></a></td>
<td width='25%'><b>Saldo Actual</b></td>
<td width='10%'><a id=mo href='".encode_link($_SERVER['PHP_SELF'],array("sort"=>"3","up"=>$up,"distrito"=>$distrito))."'><b>Estado</b></a></td>
</tr>";
}

$cnr=1;

while (!$resultado->EOF)
{
  //seteo los links
  if($ie!='caja')//si no es caja (es ingreso o egreso)
  {if($resultado->fields['comentarios']=="")
    $comentario="Observaciones: no tiene";
   else
    $comentario="Observaciones:".$resultado->fields['comentarios'];
   if($res_dist->fields['nombre']=="San Luis")
   	$distrito=1;
   elseif($res_dist->fields['nombre']=="Buenos Aires - GCBA")
   	$distrito=2;

   $archivo="ingresos_egresos.php";
   $link=encode_link($archivo,array("id_ingreso_egreso"=> $resultado->fields["id_ingreso_egreso"],"pagina"=>$ie,"distrito"=>$distrito));
  }
  else
  {
    $archivo="caja_diaria.php";
  	if($res_dist->fields['nombre']=="San Luis")
  		$distrito=1;
   elseif($res_dist->fields['nombre']=="Buenos Aires - GCBA")
    $distrito=2;
   $link=encode_link($archivo,array("id_caja"=> $resultado->fields["id_caja"],"pagina"=>"listado","distrito"=>$distrito));
  }


  /*
  if ($cnr==1)
  {$color2=$bgcolor2;
   $color=$bgcolor1;
   $atrib ="bgcolor='$bgcolor1'";
   $cnr=0;
  }
  else
  {$color2=$bgcolor1;
   $color=$bgcolor2;
   $atrib ="bgcolor='$bgcolor2'";
   $cnr=1;
  }
  $atrib.=" onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\"";
  $atrib.=" title='$comentario' style=cursor:hand";
  */
 //anexar luego con un join al query principal
  $comentario = str_replace("'","",$comentario);

  if($ie!='caja') {
  //tr_tag($link,"title='$comentario'");
  ?>
   <tr <?=atrib_tr();?> title="<?=$comentario?>" onclick="window.open('<?=$link?>')">
  <!--<tr <?php echo $atrib; ?>>-->
  <td width="1%"><?php echo $resultado->fields["id_ingreso_egreso"]; ?></td>
  <td width="10%"><?php echo fecha($resultado->fields["fecha"]); ?></td>
  <td width="40%"><?php echo $resultado->fields["item"]; ?></td>
  <td width="15%">
   <table border="0" width="100%"><tr  height="100%"><td align="left"><?php echo $resultado->fields["simbolo"];?></td><td align="right"><?php echo formato_money($resultado->fields["monto"]);?></td></tr></table>
  </td>
  <td width="25%"><?php echo $resultado->fields["nombre"]; ?></td>
  <td width="10%"><?=$resultado->fields["cuenta_ingreso"];?></td>
  </tr>

  <?php

   }
  else {
   //tr_tag($link,"title='$comentario'");
    ?>
   <tr <?=atrib_tr();?> title="<?=$comentario?>" onclick="window.open('<?=$link?>')">
   <td ><?php echo $resultado->fields["id_caja"]; ?></td>
   <td ><?php echo fecha($resultado->fields["fecha"]); ?></td>
   <td width="15%">
    <table border="0" width="100%"><tr  height="100%"><td align="left"><?php echo $resultado->fields["simbolo"];?></td><td align="right"><?php echo formato_money($resultado->fields["saldo_total"]);?></td></tr></table>
   </td>
   <td ><?php if($resultado->fields["cerrada"]) echo "Cerrada"; else echo "Abierta"; ?></td>
  </tr>

  <?php

   }
    $resultado->MoveNext();
}   //del while
?>
</table>
</center>

 <?
}

//funcion para restar fechas
/*function feriado($dia_feriado) {
global $db;

$dia_fer=split("/",$dia_feriado);

$feriado=0;
$dia=$dia_fer[0];
$mes=$dia_fer[1];
$año=$dia_fer[2];

$query_feriados="SELECT * from feriados WHERE dia=$dia and mes=$mes";
$resultados_feriados=$db->Execute($query_feriados) or die($db->ErrorMsg().$query_feriados);
$feriado=$resultados_feriados->RecordCount();
return $feriado;
}*/

function dia_habil_anterior($fecha){
$fecha_aux=$fecha;
$feriado=0;
$dia_anterior=0;

while(!$dia_anterior) {
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_test=split("/",$fecha_aux);
  if($fecha_test[3]!=0 && !feriado($dfecha))
      $dia_anterior=1;
}

$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));

return $a;
//echo "el dia anterior ".$dia_anterior;

//return $dia_anterior;
//return $fecha_aux;

}

function muestra_saldo ($tipo,$distrito,$color) {
global $db;
$fecha = date("Y-m-d ",mktime());	 //fecha actual

//--------------------- saldo acumulado en pesos en pesos  -------------------------
//busca la fecha de la ultima caja cerrada
$sql="select max (fecha) as fecha_max from caja.caja join licitaciones.distrito using (id_distrito)
join licitaciones.moneda using (id_moneda) where caja.cerrada=1 and id_distrito=$distrito and id_moneda=1 ";
$res=$db->Execute($sql) or die ($db->ErrorMsg().$sql);
$cerrada_pesos=$res->fields['fecha_max'];

//suma ingresos desde ultima caja cerrada en pesos
$query_ingresos="SELECT sum(ingreso_egreso.monto) as ingreso FROM ingreso_egreso JOIN caja using(id_caja) JOIN distrito using(id_distrito) JOIN moneda using(id_moneda) WHERE caja.fecha > '".$cerrada_pesos."' AND ingreso_egreso.id_tipo_egreso isnull AND distrito.id_distrito=$distrito AND moneda.id_moneda=1";
 $resultados_ingresos=$db->Execute($query_ingresos) or die ($db->ErrorMsg().$query_ingresos);
 $ingresos_pesos=$resultados_ingresos->fields['ingreso'];

//suma de los egresos desde ultima caja cerrada en pesos
$query_egresos="SELECT sum(ingreso_egreso.monto) as egreso FROM ingreso_egreso JOIN caja using(id_caja) JOIN distrito using(id_distrito) JOIN moneda using(id_moneda) WHERE caja.fecha > '".$cerrada_pesos."' AND ingreso_egreso.id_tipo_ingreso isnull AND distrito.id_distrito=$distrito AND moneda.id_moneda=1";
 $resultados_egresos=$db->Execute($query_egresos) or die ($db->ErrorMsg().$query_egresos);
 $egresos_pesos=$resultados_egresos->fields['egreso'];

//saldo ultima caja cerrada en pesos
 $caja_anterior_pesos="SELECT saldo_total FROM caja.caja WHERE fecha='".$cerrada_pesos."'
  and id_moneda=1 and id_distrito=$distrito";
 $res1=$db->Execute($caja_anterior_pesos) or die ($db->ErrorMsg().$caja_anterior_pesos);
 $cantidad=$res1->RecordCount();

  if($cantidad==0) $saldo_anterior_pesos=0;
  else
  $saldo_anterior_pesos=$res1->fields['saldo_total'];


// --------------saldo acumulado en dolares ------------------------/
//busca fecha ultima caja cerrada caja cerrada
 $sql="select max (fecha) as fecha_max from caja.caja join licitaciones.distrito using (id_distrito)
 join licitaciones.moneda using (id_moneda) where caja.cerrada=1 and id_distrito=$distrito and id_moneda=2";
 $res=$db->Execute($sql) or die ($db->ErrorMsg().$sql);
 $cerrada_dol=$res->fields['fecha_max'];

//suma ingresos desde ultima caja cerrada en dolares
 $query_ingresos="SELECT sum(ingreso_egreso.monto) as ingreso FROM ingreso_egreso JOIN caja using(id_caja) JOIN distrito using(id_distrito) JOIN moneda using(id_moneda) WHERE caja.fecha > '".$cerrada_dol."' AND ingreso_egreso.id_tipo_egreso isnull AND distrito.id_distrito=$distrito AND moneda.id_moneda=2";
 $resultados_ingresos=$db->Execute($query_ingresos) or die ($db->ErrorMsg().$query_ingresos);
 $ingresos_dol=$resultados_ingresos->fields['ingreso'];


//suma de los egresos desde ultima caja cerrada en dolares
//caja.fecha <= '".$fecha."'
 $query_egresos="SELECT sum(ingreso_egreso.monto) as egreso FROM ingreso_egreso JOIN caja using(id_caja) JOIN distrito using(id_distrito) JOIN moneda using(id_moneda) WHERE caja.fecha > '".$cerrada_dol."' AND ingreso_egreso.id_tipo_ingreso isnull AND distrito.id_distrito=$distrito AND moneda.id_moneda=2";
 $resultados_egresos=$db->Execute($query_egresos) or die ($db->ErrorMsg().$query_egresos);
 $egresos_dol=$resultados_egresos->fields['egreso'];

 //saldo anterior en dolares
 $caja_anterior_dol="SELECT * FROM caja.caja WHERE fecha='".$cerrada_dol."'
  and id_moneda=2 and id_distrito=$distrito";
 $res2=$db->Execute($caja_anterior_dol) or die ($db->ErrorMsg().$caja_anterior_dol);
 $cantidad=$res2->RecordCount();

 if($cantidad==0) $saldo_anterior_dol=0;
 else
  $saldo_anterior_dol=$res2->fields['saldo_total'];

  $saldo_actual_pesos=($saldo_anterior_pesos+$ingresos_pesos)-$egresos_pesos;
  $saldo_actual_dolares=($saldo_anterior_dol+$ingresos_dol)-$egresos_dol;
 echo "<table align='center' width='80%' height='80%'  bgcolor='$color'>";
 echo " <tr><td> <font color='white'>FECHA ULTIMA CAJA CERRADA Pesos:". Fecha($cerrada_pesos)."</font></td></tr>";
 echo " <tr><td align='center'> <font color='white'><b> Saldo En Pesos:   $". formato_money($saldo_actual_pesos). "</b></font></td></tr>";
 echo " <tr><td> <font color='white'> FECHA ULTIMA CAJA CERRADA Dólares:". Fecha($cerrada_dol)."</font></td></tr>";
 echo " <tr><td align='center'> <font color='white'><b> Saldo En Dólares: U\$S ".formato_money($saldo_actual_dolares)." </b></font></td></tr>";

 echo "</table>";


}

/* calcula el proximo dia habil
argumento $fecha es dd/mm/aaaa */
function dia_habil_posterior($fecha){
$fecha_aux=$fecha;
$feriado=0;
$dia_post=0;

while(!$dia_post) {
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+1,$fecha_total[2]));
  $fecha_test=split("/",$fecha_aux);
  if($fecha_test[3]!=0 && !feriado($dfecha))
     $dia_post=1;
}

$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));

return $a;

}

//Autor: GACZ
//busca un ingreso o egreso similar a partir de @fecha (dd/mm/aaaa)
//retorna falso si no se encontro o un objeto de resultados con todos los campos de la tabla ingreso_egreso
//si @dias es positivo busca en los *(@dias) dias siguientes a @fecha
function chk_similar_ie($tipo,$distrito,$fecha,$monto,$dias)
{
 	$q="SELECT * FROM ingreso_egreso ";
 	$q.="join caja using(id_caja) ";
 	$q.="join distrito using(id_distrito) ";
 	$q.="WHERE id_tipo_$tipo is not null ";
 	$q.="AND monto=$monto AND distrito.id_distrito=$distrito";
	//si se debe buscar en los dias siguientes
 	if ($dias > 0)
 	{
		$use_function="dia_habil_posterior";
		$vector_fecha=split("/",$fecha);
		//par aque me tome el dia actual
		$fecha=($vector_fecha[0]-1)."/".$vector_fecha[1]."/".$vector_fecha[2];
 	}
	//o se busca en los dias anteriores
	else
	{
		$use_function="dia_habil_anterior";
		$vector_fecha=split("/",$fecha);
		//par aque me tome el dia actual
		$fecha=($vector_fecha[0]+1)."/".$vector_fecha[1]."/".$vector_fecha[2];
	}

	$dias=abs($dias);
	$i=0;
	//busco las fechas de los $dias habiles (posteriores o anteriores)
	//incluyendo el dia de la fecha
	while ($i <= $dias)
	{
		eval('$vector_dias['.$i++.']= '.$use_function."('$fecha');");
		$vector_fecha=split("/",$fecha);
		$fecha= ($vector_fecha[0]-1)."/".$vector_fecha[1]."/".$vector_fecha[2];
	}
	//agrego las fechas obtenidas a la consulta
	$q.=" AND ";
	$add_or="(";
	foreach ($vector_dias as $valor )
	{
		$q.=$add_or."fecha='".Fecha_db($valor)."'";
		$add_or=" OR ";
	}
	$q.=")";
 	$res=sql($q) or fin_pagina();
 	if ($res->recordcount())
 	{
 	  return $res;
 	}
 	else
 	  return false;

}
?>