<?
/*
Author: GACZ

MODIFICADA POR
$Author: nazabal $
$Revision: 1.94 $
$Date: 2007/05/23 21:21:29 $
*/

require_once("../../config.php");



//funcion para mandar el arreglo por post
function array_envia($array) {
    $tmp = serialize($array);
    $tmp = urlencode($tmp);
    return $tmp;
}
//DATOS DE LA FACTURA se extraen del arreglo POST
extract($_POST,EXTR_SKIP);

//borra la variable en caso de que venga desde licitaciones
if ($_ses_global_backto){
	extract($_ses_global_extra,EXTR_SKIP);
 	phpss_svars_set("_ses_global_backto", "");
 	phpss_svars_set("_ses_global_extra", array());
}

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
	
//si hay que hacer la facturacion de seguimiento de produccion
if ($cmd=="facturar_prod"){
	$renglones_oc=PostvartoArray("chk_");
	//genero una lista de los renglones
	$list=implode(",",$renglones_oc);

	$diferencias=PostvartoArray("dif_");
	//genero una lista de las diferencias
	if (is_array($diferencias)) {
		$list2=implode(",",$diferencias);
	}


	//selecciono los renglones que se deben facturar
    /* $q ="SELECT r1.*, r2.* FROM ";
	$q.="renglones_oc r1  ";
	$q.="join renglon r2 on r1.id_renglones_oc in ($list) AND r1.id_renglon=r2.id_renglon ";*/

	$q="SELECT r1.*, r2.titulo,r2.id_licitacion,r2.codigo_renglon as cod FROM
        licitaciones.renglones_oc r1
        join licitaciones.renglon r2 on r1.id_renglones_oc in ($list) AND r1.id_renglon=r2.id_renglon
	    order by cod";


	$renglones=sql($q) or fin_pagina();

	$id_subir=$renglones->fields['id_subir'];
	$licitacion=$renglones->fields['id_licitacion'];

	$q ="SELECT l.*,e.*,ci.nombre as iva_tipo,ti.porcentaje as iva_tasa FROM ";
	$q.="licitacion l ";
	$q.="join entidad e using(id_entidad) ";
	$q.="left join condicion_iva ci using(id_condicion) ";
	$q.="left join tasa_iva ti using(id_iva) ";
	$q.="where id_licitacion=$licitacion ";
	$lic=sql($q) or fin_pagina();

	$fecha_factura=date("d/m/Y");
	$cliente=$lic->fields['nombre'];
	$direccion=$lic->fields['direccion'];
	$cuit=$lic->fields['cuit'];
	$iib=$lic->fields['iib'];
	$condicion_iva=$lic->fields['iva_tipo'];
	$iva=$lic->fields['iva_tasa'];
	$otros=$lic->fields['otros'];
	if ($seg) {  
		//recupera lugar de entrega y comentario adicional
		$query="select lugar_entrega,comentario_adicional from subido_lic_oc where id_entrega_estimada=$seg";
	    $res=sql($query) or fin_pagina();
		$otros.="\n Lugar de entrega ".$res->fields['lugar_entrega'];
		$comentario_adicional=$res->fields['comentario_adicional'];
	}

	$tipo_factura='b';
	$id_moneda=$lic->fields['id_moneda'];
	$id_entidad=$lic->fields['id_entidad'];
	$cotiz_valor=($lic->fields['valor_dolar_lic'])?number_format($lic->fields['valor_dolar_lic'],2,".","") :"";

}

?>
<?=$html_header?>

<script src="../../lib/popcalendar.js"></script>
<script>
//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
	if (document.all.nbre.value==''||document.all.nbre.value==' ')
 {
  alert('Falta seleccionar el nombre del Cliente');
  return false;
 }
	if(document.all.nbre.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
        return 0;
    }
    if(document.all.dir.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
        return 0;
    }
    if(document.all.cuit.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Cuit');
        return 0;
    }
    if(document.all.iva.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Tasa de I.V.A.');
        return 0;
    }
    if(document.all.condicion_iva.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Condición de I.V.A.');
        return 0;
    }
    if(document.all.iib.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo teléfono del I.I.B.');
        return 0;
    }
    if(document.all.otros.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Otros');
        return 0;
    }
    return 1;
}
/*****************************************************/
function total()
{
	var total=0;
	 for (var i=0; i < document.factura.length ; i++)
	 {
		  if (document.factura.elements[i].name.indexOf("subtotal_")!=-1)
		  {
			 var subtotal=document.factura.elements[i];
		  	 var id_item=subtotal.name.substring(subtotal.name.indexOf("_")+1,subtotal.name.lenght);
			 total+=parseFloat(subtotal.value);
		  }
	 }
	var t1= new String(total.toFixed(2));
	if (t1.indexOf(".")==-1)
		document.all.total.value=t1+".00";
	else
	  document.all.total.value=t1
}

//calcula el subtotal dependiendo de la cantidad
function calcular(textfield)
{
 id_item=textfield.name.substring(textfield.name.indexOf("_")+1,textfield.name.lenght);
 var subtotal=eval("document.all.subtotal_"+ id_item);
 var precio=eval("document.all.precio_"+ id_item);
 var cantidad=eval("document.all.cant_"+ id_item);
 if ( 0 >= precio.value.indexOf(','))
 {
   precio.value[precio.value.indexOf(',')]='.'; //entra pero no lo cambia
 }
 var t1= new String((cantidad.value*parseFloat(precio.value)).toFixed(2));
 if (t1.indexOf(".")==-1)
		 subtotal.value=t1+".00";
 else
	   subtotal.value=t1;

 total();
}

/********FUNCIONES QUE USA LA VENTANA HIJO**********
NOTA:
		LAS FUNCIONES TOMAN EL CONTEXTO DONDE SON DEFINIDAS
		OJO CON EL ACCESO A LAS VARIABLES*/
//variable que contiene la ventana para selecciona el cliente
var wcliente=0;

function cargar_cliente()
{
 document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.nbre.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
 if (wcliente.document.all.chk_direccion.checked)
	document.all.dir.value=wcliente.document.all.direccion.value;
 document.all.cuit.value=wcliente.document.all.cuit.value;
 document.all.condicion_iva.value=wcliente.document.all.condicioniva.options[wcliente.document.all.condicioniva.value].text;
 document.all.iib.value=wcliente.document.all.iib.value;
 document.all.iva.value=wcliente.document.all.iva.options[wcliente.document.all.iva.value].text;
 
 //indica que se debe actualizar los clientes mas usuados
 document.all.cambio_entidad.value="si_cambio";
}

//variable que contiene la ventana hijo productos
var wproductos=0;
function cargar(){//genera una fila en blanco
/*Para insertar una fila*/
var items=document.all.items.value++;
//inserta al final
var fila=document.all.productos.insertRow(document.all.productos.rows.length );
//inserta al principio
//var fila=document.all.productos.insertRow(1);


fila.insertCell(0).innerHTML="<div align='center'> <input name='chk' type='checkbox' id='chk' value='1'></div><input type='hidden' name='idp_"+
items +"' value=''>";

fila.insertCell(1).innerHTML="<div align='center'> <input name='cant_"+
items+"' type='text' id='cantidad' size='4' value='1' style='text-align:right' "+
"onchange='calcular(this)' ></div>";

fila.insertCell(2).innerHTML="<div align='center'><textarea name='desc_"+
items +"' cols='60' rows='5' wrap='VIRTUAL' id='descripcion'></textarea></div>";

fila.insertCell(3).innerHTML="<div align='center'> <input name='precio_"+
items+"' type='text' id='unitario' size='10' style='text-align:right' value='' "+
"onchange='calcular(this)'></div> ";

fila.insertCell(4).innerHTML="<div align='center'> <input name='subtotal_"+
items+"' type='text' readonly id='subtotal' size='10' style='text-align:right' value=''></div>";


if (document.all.boton[1].disabled)
	document.all.boton[1].disabled=0;

document.all.guardar.value++;
total();
}

function borrar_items()
{
var i=0;
while (typeof(document.all.chk)!='undefined' &&
		 typeof(document.all.chk.length)!='undefined' &&
		 i < document.all.chk.length)
{
   /*Para borrar una fila*/
  if (document.all.chk[i].checked)
   document.all.productos.deleteRow(i+1);
  else
  	i++;
}

if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
{
   document.all.productos.deleteRow(1);
   document.all.boton[1].disabled=1;
}
else if (typeof(document.all.chk)=='undefined')
   		document.all.boton[1].disabled=1;

total();
}
//--------------------------------------------------------
//FUNCION PARA CHECKEAR LOS CAMPOS
var msg;
function chk_campos()
{
var ret_value=0;


msg="---------------------------------------------\t\n";
msg+="Falta Completar:\n\n";

if (document.all.nbre.value=="" || document.all.nbre.value==" " || document.all.nbre.value=="Haga click en Cliente para ver la lista")
 {
  msg+="\tNombre del cliente\n";
  ret_value++;
 }
if (document.all.select_tipo_factura[document.all.select_tipo_factura.selectedIndex].value==-1)
{  msg+="\tTipo de factura\n";
  ret_value++;
}

if (document.all.nro_factura.value==""){
	msg+="\tNro de Factura\n";
	ret_value++;
}

if (document.all.nro_factura.value.length<8){
	msg+="\tEl Nro de Factura debe ser de 8 digítos\n";
	ret_value++;
}

if (document.all.select_moneda[document.all.select_moneda.selectedIndex].value==-1)
 {
  msg+="\tTipo de Moneda\n";
  ret_value++;
 }
if (document.all.iva.value=="" || document.all.iva.value==" " )
 {
  msg+="\tPorcentaje de IVA\n";
  ret_value++;
 }
if (document.all.asociar.checked && document.all.licitacion.value=="")
 {
  msg+="\tEl ID de Licitacion\n";
  ret_value++;
 }
if (!document.all.cotiz_valor.disabled && document.all.cotiz_valor.value=="")
 {
  msg+="\tEl valor o cotizacion del dolar\n";
  ret_value++;
 }


/*
 if (document.all.productos.rows.length <= 1)
 {
  msg+="\tAgregar productos\n";
  ret_value++;
 }
*/
//maximo de 25 productos
if (document.all.productos.rows.length > 26 && ret_value==0)
{
  msg="----------------------------------------------------------------------\t\n";
  msg+="Solo puede agregar un maximo de 25 productos diferentes\n";
  msg+="Haga otra factura para los demas!\n";
  ret_value=27;
}
if (ret_value < 27)
	msg+="---------------------------------------------\t";
else
  msg+="----------------------------------------------------------------------\t";

 return ret_value;
}

</script>
<script language="JavaScript1.2">
//funciones para busqueda abreviada utilizando teclas en la lista que muestra los clientes.
var digitos=15 //cantidad de digitos buscados
var puntero=0
var buffer=new Array(digitos) //declaración del array Buffer
var cadena=""

function buscar_op(obj){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13){
       borrar_buffer();
      // if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       for (var opcombo=0;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}

function borrar_buffer(){
   //inicializa la cadena buscada
    cadena="";
    puntero=0;
}
</script>

<style type="text/css">
<!--
.tablaEnc {
	color: #FDF2F3;
	background-color: #BA0105;
}
-->
</style>
<script language='javascript'>
var winW=window.screen.Width;
var valor=(winW*25)/100;
var nombre1;
var titulo1;
function insertar(){
 ventana.document.all.titulo.innerText=titulo1;
 ventana.frames.frame1.location=nombre1;
}

</script>

</head>
<?
$link=encode_link("factura_proc.php", array("datos"=>$diferencias));
?>
<form name="factura" method="post" action="<?=$link?>">
<!-- Para saber de que seguimiento viene la factura -->
<input type='hidden' name="seg" value='<?=$seg?>'>
<input type="hidden" name='id_renglon_oc' value='<?=$id_renglon?>'>
<!--
<div align='right'>
<a href="#" onClick="abrir_ventana('/gestion/modulos/ayuda/remitos/ayuda_rem_nuevo.htm', 'LLENAR UN REMITO')"> Ayuda </a>
</div>
-->
<?
 $estado='p';
 $q="SELECT * FROM facturas WHERE id_factura=$id_factura";
 if ($id_factura && $id_factura!=-1){
 	
 	$factura = sql($q) or fin_pagina();
	$fecha_factura = ($factura->fields['fecha_factura'])?date("d/m/Y",strtotime($factura->fields['fecha_factura'])):"";
	$nro_factura = $factura->fields['nro_factura'];
	$nro_remito  = $factura->fields['nro_remito'];
	$cliente 	 = $factura->fields['cliente'];
	$direccion   = $factura->fields['direccion'];
	$cuit 	     = $factura->fields['cuit'];
	$iib         = $factura->fields['iib'];
	$condicion_iva = $factura->fields['iva_tipo'];
	$iva         = $factura->fields['iva_tasa'];
	$otros       = $factura->fields['otros'];
	$pedido      = $factura->fields['pedido'];
	$venta       = $factura->fields['venta'];
	$tipo_factura= $factura->fields['tipo_factura'];
	$id_moneda   = $factura->fields['id_moneda'];
	$estado      = $factura->fields['estado'];
	$id_entidad  = $factura->fields['id_entidad'];
	$numeracion_factura = $factura->fields['id_numeracion_sucursal'];
	$cotiz_valor =($factura->fields['cotizacion_dolar']!=0)?number_format($factura->fields['cotizacion_dolar'],2,".","") :"";
	if (!$licitacion)
		$licitacion=$factura->fields['id_licitacion'];
    /////////esto ahy que bortrar luego
	if ($estado=='p' || ($estado=='t' && permisos_check("inicio","modificar_factura_terminada") && $parametros['modificar_terminada']=="1"))
	 $can_finish=1;

	 unset($factura);
 }
 if (!$fecha_factura)
    $fecha_factura=date("d/m/Y");

 if (!$id_factura || $id_factura==-1)
		$can_finish=1;
$q="select * from log where id_factura=";
$q.=($id_factura)?$id_factura:-1;
$q.=" order by fecha desc";
$log=sql($q) or fin_pagina();
?>
<!-- tabla de registro -->
<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> "  >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
do
{
?>
<tr>
      <td height="20" nowrap>Fecha de <?=(($log->fields['tipo_log'])?$log->fields['tipo_log'].": ":"creacion: ").(($log->fields['fecha'])?date("d/m/Y H:i:s",strtotime($log->fields['fecha'])):date("d/m/Y H:i:s"))?> </td>
      <td nowrap > Usuario : <?=(($log->fields['usuario'])?$log->fields['usuario']:$_ses_user['name']); ?> </td>
</tr>
<?
 $log->MoveNext();
}
while (!$log->EOF);


$link=encode_link("administrar_banderas_cobranzas.php",array("id_factura"=>$id_factura,"nro_factura"=>$nro_factura));
?>
</table>
</div>
<hr>
<table width=100% align=center>
 <tr>
    <!--
    Este boton solo lo ve juan ,corapi
    sirve para cambiar las columnas licitacion_entregada, renglones_entregados
    en licitaciones_cobranzas
    -->
    <td width=50% align=left>
        <?
		
       if ($_ses_user["login"]=="fernando" ||$_ses_user["login"]=="noelia" ||$_ses_user["login"]=="corapi" || $_ses_user["login"]=="juanmanuel" || $_ses_user["login"]=="marcos" || $_ses_user["login"]=="mariela")
        {
        ?>
        <input type=button name="sacar_bandera" value="Balance" title="Abre Pantalla para modificar datos para que la factura figure en el balance " onclick="window.open('<?=$link?>')">
        <?
        } else echo "&nbsp;";
        ?>
    </td>
    <td width=50% align=right>
       <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/facturas/fact_nueva.htm" ?>', 'NUEVA FACTURA')" >
    </td>
 </tr>
</table>
<div align="right">

</div>
<? if ($msg) echo "<center>$msg<br></center>"; ?>
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr>
      <td width="54%" align="center" valign="top" nowrap height="211">
        <table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr align="center" class="tablaEnc">
           <td colspan="3" height="20">
           <? //esto hay que borrarlo luego	?>
           <strong>Cliente </strong>
           <? if ($_ses_user['login']=="ferni" || $_ses_user['login']=="fernandos" || $_ses_user['login']=="nazabal"){
           ?> <input name="guarda_cliente" type="submit" value="Modifica cliente"> 
           <? } ?>          
          </td>
          </tr>
          <tr>
           <td colspan="2">
            <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">   
            <input name="cambio_entidad" type="hidden" value="no_cambio">
           
            <table>
             <tr>
               <td align="left" width="80%">
                
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;               
                <input type="button" name="clientes" value="Elegir cliente" <? if (!$can_finish) echo " disabled " ?> title="Permite elegir cliente para la factura" 
                 onclick="if (wcliente==0 || wcliente.closed)
	                                    wcliente=window.open('<?=encode_link('../general/seleccionar_clientes.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
                       else
	                   if (!wcliente.closed)     
	 	               wcliente.focus();
"
                 >
                
                </td>
              </tr>
              <tr>
               <td>
                 <strong>Nombre&nbsp;&nbsp;</strong>
                 <input name="nbre" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<? if($cliente!=""){echo $cliente;}?>" <? if (!$can_finish) echo " disabled " ?>  size="47">
              </td>
             </tr>
            </table>
           </td>
          </tr>
          <tr align="left">
            <td height="30" colspan="3"> <strong>Dirección</strong>
              <input name="dir" readonly title="Para editar los campos del cliente presione el boton elegir cliente" type="text"  value="<?= $direccion ?>"  <? if (!$can_finish) echo " disabled " ?> size="47">
            </td>
          </tr>
          <tr>
            <td width="52%" height="24" align="left" nowrap><strong>C.U.I.T</strong>
              &nbsp; &nbsp;
              <input name="cuit" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $cuit ?>"  <? if (!$can_finish) echo " disabled " ?> size="18" > </td>
            <td width="48%" height="24" colspan="2" align="left" nowrap>&nbsp;
              <strong>I.I.B.</strong>
              <input name="iib" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iib ?>" <? if (!$can_finish) echo " disabled " ?> size="17" > </td>
          </tr>
          <tr align="left">
            <td height="35" colspan="3"><strong>Condición I.V.A</strong>
              <input name="condicion_iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $condicion_iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="20" >
              &nbsp;&nbsp; <strong> I.V.A %</strong>
              <input name="iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="6" ></td>
          </tr>
          <tr>
            <td height="60" colspan="3">
              <div align="left"> <strong>Otros</strong>
                <textarea name="otros" title="Para editar los campos del cliente presione el boton elegir cliente"  cols="50" rows="3" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $otros ?></textarea>
              </div></td>
          </tr>
          <? if ($cmd=='anuladas'){
          		$log->MoveFirst();
          		while ($log->fields['tipo_log']!='anulacion') {
          			$log->MoveNext();
          		}
          	?>
          <tr>
            <td height="60" colspan="3">
              <div align="left"> <strong>Comentario Anulación</strong>
                <textarea name="com_anulacion"cols="45" rows="3" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?=$log->fields['otros']?></textarea>
              </div></td>
          </tr>
          <? } ?>
        </table>
      </td>

      <td width="46%" align="center" valign="top" nowrap>
        <table width="308" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr class="tablaEnc">
            <td colspan="2" height="20" align="center"><strong>Factura</strong></td>
          </tr>
          <tr>
            <td id="td_fecha_factura" height="25"><strong>Fecha Factura</strong></td>
            <? 
            //esto va por que permite guardar la fecha de los login siguientes
        	$can_finish_fact=false;
			if (($estado=='t')&&($_ses_user['login']=='noelia'||$_ses_user['login']=='juanmanuel'||$_ses_user['login']=='corapi'))
        	$can_finish_fact=true;
            ?>
            <td align="right"> <input name="fecha_factura" type="text" <? if ((!$can_finish)&&(!$can_finish_fact)) echo " disabled " ?> value="<?= $fecha_factura ?>" size="10">
              <img <? if ((!$can_finish)&&(!$can_finish_fact)) echo " disabled " ?> src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
               seleccionar la fecha'  onClick="javascript:popUpCalendar(td_fecha_factura, fecha_factura, 'dd/mm/yyyy');">
            </td>
          </tr>
          <tr>
            <td width="148" height="25"><strong>Tipo Factura</strong></td>
            <td width="154" align="right"> <select name="select_tipo_factura" <? if (!$can_finish) echo " disabled " ?>>
                <option value=-1>Seleccione el tipo</option>
                <option value='a' <? if ($tipo_factura=='a') echo " selected"?>>Tipo
                A</option>
                <option value='b' <? if ($tipo_factura=='b') echo " selected"?>>Tipo
                B</option>
              </select> </td>
          </tr>
          <?
          $sql="select * from numeracion_sucursal where activo=1";
          $res=$db->execute($sql) or die($db->errormsg()."<br>".$sql);   
          ?>
          <tr>
            <td><strong>Numeración</strong></td>
            <td align="right">
            <select name='numeracion' style="width:80%" <? if (!$can_finish) echo " disabled " ?>>
               <?
               for ($i=0;$i<$res->recordcount();$i++){
               	 $numeracion    = $res->fields["numeracion"];
               	 $id_numeracion = $res->fields["id_numeracion_sucursal"];
               	 $sucursal      = $res->fields["sucursal"];
               	 ($id_numeracion==$numeracion_factura)?$selected_num="selected":$selected_num="";
               	 ?>
               	 <option value="<?=$id_numeracion?>" <?=$selected_num?>><?=$numeracion?></option>
               	 <?
               	$res->movenext(); 
               }
               ?>
            </select>
            </td>
          </tr>
          <tr>
            <?
              $parte=ereg_replace("\*","",$nro_factura);
              $esta=substr($nro_factura,0,1);
              $contt=0;
              if ($esta=="*" ) $contt=1;
            ?>
            <td width="148" height="25"><strong>Número Factura</strong></td>
            <td width="154" align="right"><? if ($contt) { ?><b><font color="red" size="2">R&nbsp;&nbsp; </font></b><?}?><input name="nro_factura" type="text" value="<?= $parte ?>" <? if (!$can_finish) echo " disabled " ?> size="18" maxlength="8"></td>
          </tr>
          <tr>
            <td height="25"><strong>Pedido N&ordm;</strong></td>
            <td align="right"><input name="pedido" type="text" value="<?= $pedido ?>" <? if (!$can_finish) echo " disabled " ?> size="18"></td>
          </tr>
          <tr>
            <td height="25"><strong>Remito N&ordm;</strong></td>
            <td align="right"><input name="nro_remito" type="text" value="<?= $nro_remito ?>" <? if (!$can_finish) echo " disabled " ?> size="18"></td>
          </tr>
          <tr>
            <td height="25"><strong>Venta</strong></td>
            <td align="right"><input name="venta" type="text"  value="<?= $venta ?>" <? if (!$can_finish) echo " disabled " ?> size="18"></td>
          </tr>
          <tr>
            <td height="25"><strong>Moneda</strong></td>
            <td align="right"> <select  name="select_moneda" <? if (!$can_finish) echo " disabled " ?> onchange="if (this.options[this.selectedIndex].value==2) {document.all.cotiz_text.disabled=0;document.all.cotiz_valor.disabled=0; } else {document.all.cotiz_text.disabled=1;document.all.cotiz_valor.disabled=1; }" >
                <option value="-1">Seleccione el tipo</option>
                <?
$q="select * from moneda";
$moneda=sql($q) or fin_pagina;
echo make_options($moneda,'id_moneda','nombre',$id_moneda);
?>
              </select> </td>
          </tr>
          <tr>
            <td height="25"><span id=cotiz_text <? if ($id_moneda!=2) echo "disabled" ?>><b>Cotizaci&oacute;n Dolar</b></span></td>
            <td align="right">
            <input type="text" name="cotiz_valor" size="10" style="text-align:right" value="<?=$cotiz_valor ?>" <? if ($id_moneda!=2 || !$can_finish) echo "disabled" ?> ></td>
          </tr>
        </table>
    </td>
    </tr>
  </table>
  <br>
  <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td align="left">&nbsp;Id Licitacion &nbsp; <input name="licitacion" type="text" size="6" value="<?=$licitacion ?>" <? if (!$can_finish) echo " disabled " ?>>
        &nbsp; <input name="boton_buscar" type="button" title="Ir a ver licitaciones y traer el ID" value="Buscar" <? if (!$can_finish) echo " disabled " ?>
        onclick=
"
if (document.all.guardar.value!=0)
{
	if(confirm('Se perderan los valores modificados desea continuar?'))
		location.href='<?=encode_link("$html_root/modulos/licitaciones/licitaciones_view.php",array('backto'=>$_SERVER['SCRIPT_NAME'],"_ses_global_extra"=>array('id_factura'=>$id_factura) )) ?>';
}
else
	location.href='<?=encode_link("$html_root/modulos/licitaciones/licitaciones_view.php",array('backto'=>$_SERVER['SCRIPT_NAME'],"_ses_global_extra"=>array('id_factura'=>$id_factura))) ?>';
"> &nbsp;
        <input type="checkbox" name="asociar" <? if ($licitacion) echo " checked " ?> value="1" <? if (!$can_finish) echo " disabled " ?>>
        <font title="Asocia la licitacion a esta Factura"> Asociar </font>
		<?
		if ($licitacion) {
			$link_volver = encode_link($html_root."/index.php",array("menu"=>"factura_listar","extra"=>array("cmd"=>"detalle","id_factura"=>$id_factura)));
			echo "<input type='button' name='ir_a_lic' value='Ir' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"licitaciones_view","extra"=>array("cmd1"=>"detalle","ID"=>$licitacion,"link_volver"=>"parent.document.location='$link_volver';")))."';\">";
		}
		?>
      </td>
      <? if ($contt)
      {
      ?>
      <td align="left">
       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="red" size="2">R&nbsp; = &nbsp; Factura Repetida</font></b>
      </td>
      <?
       }
      ?>
    </tr>
  </table>
  <br>
  <table id="productos" width="100%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
    <tr bgcolor="#006699" class="tablaEnc">
      <td valign="middle" width="26" height="18">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td width="78"><div align="center"><strong>Cantidad</strong></div></td>
      <td width="360"><div align="center"><strong>Item - Descripci&oacute;n</strong></div></td>
      <td width="144"><div align="center"><strong>Precio Unitario</strong></div></td>
      <td width="114"><div align="center"><strong>Monto Parcial</strong></div></td>
    </tr>
<?
$q="select * from items_factura where id_factura=$id_factura";

if ($id_factura && $id_factura!=-1) {
 	$items_fac=sql($q) or fin_pagina();
}


$i=0;
$total=0;
$concat="";
while ($items_fac && !$items_fac->EOF)
{

?>
    <tr>
      <td valign="middle" align="center"> <input type="checkbox" name="chk" <? if (!$can_finish) echo " disabled " ?>></td>
      <td height="25"> <div align="center">
          <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $items_fac->fields['cant_prod'] ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
          <input type="hidden" name="idp_<?=$i ?>" value="<?= $items_fac->fields['id_producto'] ?>" >
          <input type="hidden" name="idi_<?=$i ?>" value="<?= $items_fac->fields['id_item'] ?>" >
        </div></td>
      <td height="25"> <div align="center">

          <textarea name="desc_<?=$i ?>" cols="60" rows="5" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $items_fac->fields['descripcion']?></textarea>
        </div></td>
      <td height="25"> <div align="center">
          <input name="precio_<?=$i ?>" type="text" style="text-align: right" size="10" value="<?= number_format($items_fac->fields['precio'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
        </div></td>
      <td height="25"> <div align="center">
          <input name="subtotal_<?=$i++ ?>" type="text" style="text-align:right" readonly  size="10" value="<?= number_format($items_fac->fields['precio']*$items_fac->fields['cant_prod'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?>>
        </div></td>
    </tr>
<?  $precio=$items_fac->fields['precio'];
	$total+=$items_fac->fields['precio']*$items_fac->fields['cant_prod'];
	$items_fac->MoveNext();
}

//si hay que hacer la facturacion de seguimiento de produccion

if ($cmd=="facturar_prod")
{   $cant_prod=$renglones->RecordCount();
	while (!$renglones->EOF)
	{
?>
    <tr>
      <td valign="middle" align="center"> <input type="checkbox" name="chk" <? if (!$can_finish) echo " disabled " ?>></td>
      <td height="25"> <div align="center">
          <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $renglones->fields['cantidad'] ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
          <input type="hidden" name="idr_<?=$i ?>" value="<?= $renglones->fields['id_renglones_oc'] ?>" >
          <input type="hidden" name="idi_<?=$i ?>" value="" >
        </div></td>
         <?
           if ($i==$cant_prod-1) $concat=$comentario_adicional; //concateno, al ultimo producto, el comentario cargado en entregas
          ?>
      <td height="25"> <div align="center">
          <textarea name="desc_<?=$i ?>" cols="60" rows="5" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $renglones->fields['titulo']."\n\n".$concat;?></textarea>
        </div></td>
      <td height="25"> <div align="center">
          <input name="precio_<?=$i ?>" type="text" style="text-align: right" size="10" value="<?= number_format($renglones->fields['precio'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
        </div></td>
      <td height="25"> <div align="center">
          <input name="subtotal_<?=$i++ ?>" type="text" style="text-align:right" readonly  size="10" value="<?= number_format($renglones->fields['precio']*$renglones->fields['cantidad'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?>>
        </div></td>
    </tr>
<?  $precio=$renglones->fields['precio'];
	$total+=$renglones->fields['precio']*$renglones->fields['cantidad'];
	$renglones->MoveNext();
	}
}
?>  </table>
  <br>
  <table width="100%" height="28" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td height="26" align="left">
<?
if ($seg) {
	$sql="SELECT subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion as licitacion,subido_lic_oc.vence_oc as vencimiento,subido_lic_oc.nro_orden as oc,entidad.nombre as cliente,entrega_estimada.nro as numero FROM subido_lic_oc ";
	$sql.="LEFT JOIN licitacion USING (id_licitacion) LEFT JOIN entidad USING (id_entidad) ";
	$sql.="LEFT JOIN entrega_estimada USING (id_entrega_estimada) WHERE subido_lic_oc.id_entrega_estimada=$seg";
	$result=sql($sql) or fin_pagina();
	$fila=$result->fetchrow();
	$fila["pagina_volver"]="entregas.php";

}
?>
<?if ($seg) {?>
		<input name="boton" type="button"  value="Cerrar" title="Volver" onclick="window.close()">
        <?}
         if (permisos_check("inicio","anula_factura")) {
        //el boton llama a la  pagina que pide motivo de la anulacion setea el valores en
        //dos hidden luego hace un submit
        ?>
		<input name="boton" type="button" id="anular"  <? if ($estado=='p' || $estado=='a') echo " disabled " ?> value="Anular" title="Se anulara la factura y dejara de ser valida" 
		onclick="if (confirm('¿Está seguro que desea anular la Factura?'))
     			window.open('<?=encode_link("comentario_anulacion.php",array("nro"=>$nro_factura,"tipo"=>"factura"))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');"
		>
<?} if ($seg) $can_finish=false; ?>
        <input name="boton" type="button" id="eliminar"   value="Eliminar" <? if ((!$can_finish)||($i==0)) echo " disabled " ?> onclick=
"if (confirm('Seguro que quieres eliminar los Items seleccionados'))
   borrar_items();
" title="Elimina los elementos seleccionados">
        <input name="boton" type="button" value="Agregar" title="Agrega uno o mas productos" <? if (!$can_finish) echo " disabled " ?> onclick="cargar()">
        
        <?//esto va por que permite guardar la fecha de las facturas finalizadas los login siguientes
        if (($estado=='t')&&($_ses_user['login']=='prueba'||$_ses_user['login']=='noelia'||$_ses_user['login']=='juanmanuel'||$_ses_user['login']=='corapi')){
        ?>
			<input type="submit" name="cambiar_fecha" value="Guardar" title="Cambia Fecha de Factura Finalizada" >
       <?}
       else{?>
        <input name="boton" type="submit" value="Guardar" title="Guarda los cambios y permite posteriores modificaciones" <? if (!$can_finish) echo " disabled " ?> 
        onclick="
		if (chk_campos()!=0)
		{
		alert (msg);
		msg='';
		return false;
		}
		if(control_datos()==0)
			return false;
		if (document.all.licitacion.value=='')
		return (confirm('La factura no esta asociada con una licitacion\n¿desea continuar?')) ;
		
		">
        <?}?>
        <? if ($estado!="t" and $estado!="a") $can_finish=true; ?>
		<input name="boton" type="submit" id="terminar"  value="Terminar" <? if (!$can_finish) echo " disabled " ?> onclick=
"
if (chk_campos()!=0)
{
alert (msg);
msg='';
return false;
}
else
{
 if (document.all.nro_factura.value=='' || document.all.nro_factura.value==' ')
 {
	msg='---------------------------------------------\t\n';
	msg+='Falta Completar:\n\n';
 	msg+='\tNumero de Factura\n';
	msg+='---------------------------------------------\t';
	alert(msg);
	msg='';
	return false;
 }
}
if(control_datos()==0)
	return false;

if (document.all.iva.value==21){
	if(confirm('Esta Factura contiene IVA del 21% \nestá seguro que quiere Finalizarla ?'))return true
	else  return false
}
" title="Terminar y guardar la factura">
<?
if ($cmd!="facturar_prod" && $seg) {
	$q="select estado from renglones_oc where id_renglones_oc=$id_renglon";
	$renglones=sql($q) or fin_pagina();
}
?>
        <input name="boton" type="submit" value="Crear Remito" style="width:95" title="Crea el remito para la factura" <? if ($renglones->fields["estado"]==1) echo " disabled "; if ($can_finish) echo " disabled "; ?> onclick="factura.action='<?= encode_link('../remitos/remito_nuevo.php',array()) ?>';">

        <input name="boton" type="button" value="Seguir Cobros" style="width:95" title="Hacer seguimiento de cobros para esta factura" <? if ($can_finish || $seg) echo " disabled " ?> onclick="location.href='<?=encode_link("../licitaciones/lic_cobranzas.php",array('id_factura'=>$id_factura,"terminar_factura"=>"auto")) ?>'">
        <?
			if ($estado=="t" && permisos_check("inicio","modificar_factura_terminada")) {
				?>
				<input name="boton" type="button" value="Modificar" style="width:95" title="Habilitar la modificación de datos para la factura terminada" onclick="location.href='<?=encode_link("factura_nueva.php",array('id_factura'=>$id_factura,"cmd"=>$cmd,"modificar_terminada"=>1)) ?>'">
				<?
			}
		?>
		 </td>
      <?if ($parametros['volver_remito']) {?>
       <td> <input type='button' name='cerrar' value='Cerrar' onclick='window.close();'></td>
      <?}?>
        <td width="56" align="right"><strong>TOTAL</strong></td>

      <td width="124" align="center"> <div align="center">
          <input name="total" type="text" style="text-align: right" <? if (!$can_finish) echo " disabled " ?> value="<?= number_format($total,2,".","")?>" size="10" readonly="true">
        </div></td>
    </tr>
  </table>
<?/*los dos primeros hidden los utiliza para la anulacion el primero le da valor al hidden
el segundo es un auxiliar debido a que cuando la pagina que pide comentario hace un submit
no va por el post el id ni el nombre del botondel boton por lo tanto tengo que 
preguntar por el sugundo hidden para anular*/?>
<input type="hidden" name="comentario_anular" value="">
<input type="hidden" name="anular_aux" value="">
<input type='hidden' name='items' value='<?= $i ?>'>
<input type='hidden' name='nuevo' value='1'>
<input type='hidden' name='guardar' value='0'>
<input type='hidden' name='id_factura' value='<?= ($id_factura)?$id_factura:-1 ?>'>
<input type="hidden" name='precio' value='<?=$precio?>'>
<?
$aux=serialize($diferencias);
$aux=str_replace("\"","\\\"",$aux);
?>
<input type="hidden" name="dif" value="<?=$aux?>">
<input type="hidden" name="renglones_chk" value="<?=$renglones_oc?>">
<input type="hidden" name="renglones_oc" value="<?=array_envia($renglones_oc)?>">
</form>
<br>
<?=fin_pagina(); ?>
