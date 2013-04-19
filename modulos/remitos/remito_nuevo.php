<?
/*
Author: GACZ

MODIFICADA POR
$Author: nazabal $
$Revision: 1.109 $
$Date: 2007/03/19 21:13:57 $
*/
require_once("../../config.php");
require_once("../personal/gutils.php");
//DATOS del remito se extraen del arreglo POST

/*
if ($parametros["modo"]=="reload"){
	//$html_header;
	$link=encode_link('../remitos/remito_nuevo.php',array("seg"=>$parametros["seg"],"post"=>$_POST, "cmd"=>'crear_remito',"pagina_volver"=>'seleccionar_renglon_adj.php', "modo"=>"normal_0"));
	?>
	<html>
		<body onload='document.all.h_facturas.value=window.opener.document.all.h_selected_rows.value;document.temp__1.submit();'>
			<form name="temp__1" method='POST' action="<?=$link?>">
				<input type="hidden" name="h_facturas" value="">
				<input type="hidden" name="pagina_volver" value="<?=(($_POST["pagina_volver"])?$_POST["pagina_volver"]:$parametros["pagina_volver"])?>">
			</form>
		</body>
	</html>
	<?
}else{
if ($parametros["modo"]=="normal_0"){
	$_POST=array_merge($_POST, $parametros["post"]);
}
*/

$id_factura=$_POST["id_fact"] or $id_factura=$_POST["id_factura"];

extract($_POST,EXTR_SKIP);

//borra la variable en caso de que venga desde licitaciones
if ($_ses_global_backto) {
	extract($_ses_global_extra,EXTR_SKIP);
 	phpss_svars_set("_ses_global_backto", "");
 	phpss_svars_set("_ses_global_extra", array());
}

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
if ($remito)
 $id_remito=$remito;

//borra la variable en caso de que venga desde licitaciones
if ($_ses_global_backto)
 	phpss_svars_set("_ses_global_backto", "");

if ($parametros["accion"]=="download") {
    $file=$parametros["file"];
    $size=$parametros["size"];
    Mostrar_Header($file,"application/octet-stream",$size);
    $filefull = UPLOADS_DIR ."/remitos/". $file;
    readfile($filefull);
    exit();
}
?>
<html>
<head>
<title>Remitos</title>
<link rel=stylesheet type='text/css' href='/gestion/lib/estilos.css'><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<?=$html_header?>
</head>
<script src="../../lib/popcalendar.js"></script>
<script language="JavaScript" src="funciones.js"></script>
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

    if (typeof(document.all.nro_factura) =='undefined') 
    //&& (document.all.nro_factura.value=="") )
    {
      aux=confirm('Seguro que desea guardar el remito sin numero de factura?');
      if(aux) {valor=prompt('Por favor ingrese el motivo por el cual desea guardar el remito sin numero de factura','');
               if((valor=="") || (valor==null)) return 0;
               else document.all.valor.value=valor;
    		  }
      else
      return aux;
    }
    return 1;
}
/*****************************************************/
function total()
{
	var total=0;
	 for (var i=0; i < document.remito.length ; i++)
	 {
		  if (document.remito.elements[i].name.indexOf("subtotal_")!=-1)
		  {
			 var subtotal=document.remito.elements[i];
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

//variable que contiene la ventana que termina el remito
var wterminar=0;

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
function cargar()
{
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

//document.location.href="#botones";
//document.all.descripcion[0].value=wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text;
//document.all.chk[0].value=wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].value;
}

/*************************************************/

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
function chk_campos(terminar)
{
var ret_value=0;
msg="---------------------------------------------\t\n";
msg+="Falta Completar:\n\n";

if (document.all.nbre.value=="" || document.all.nbre.value==" " || document.all.nbre.value=="Haga click en Cliente para ver la lista")
 {
  msg+="\tNombre del cliente\n";
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
if (terminar && document.all.nro_remito.value=="")
 {
  msg+="\tNumero de Remito\n";
  ret_value++;
 }
 
if (document.all.nro_remito.value.length<8){
	msg+="\tEl Nro de remito debe ser de 8 digítos\n";
	ret_value++;
} 

//maximo de 25 productos
if (document.all.productos.rows.length > 26 && ret_value==0)
{
  msg="----------------------------------------------------------------------\t\n";
  msg+="Solo puede agregar un maximo de 25 productos diferentes\n";
  msg+="Haga otra remito para los demas!\n";
  ret_value=27;
}
if (ret_value < 27)
	msg+="---------------------------------------------\t";
else
  msg+="----------------------------------------------------------------------\t";

 return ret_value;
}

</script>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
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

<?

//if ($parametros['cmd'] == 'asociar_factura' ) 
  // echo ch_menu($_SERVER['SCRIPT_NAME']); ?>
   
</head>
<body bgcolor="#E0E0E0">
<form name="remito" method="post" action="../remitos/remito_proc.php" enctype='multipart/form-data'>
<!-- Para saber de que seguimiento viene la factura -->
<input type=hidden name="seg" value='<?=$seg;?>'>
<input type="hidden" name="h_facturas" value="<?=$_POST["h_facturas"]?>">
<input type="hidden" name="pagina_volver" value="<?=(($_POST["pagina_volver"])?$_POST["pagina_volver"]:$parametros["pagina_volver"])?>">

<?
 $estado='p';
 $nuevo=1;
 $q="SELECT * FROM remitos WHERE id_remito=$id_remito";
 if ($id_remito && $id_remito!=-1) {
 	$remito = sql($q) or fin_pagina();
	$fecha_remito = date("d/m/Y",strtotime($remito->fields['fecha_remito']));
	$nro_remito = $remito->fields['nro_remito'];
    //Añadir las condiciones si se permite cargar al remito ya creado
    //los items de una factura
	if (!$nro_factura)
		$nro_factura=$remito->fields['nro_factura'];
    
	$id_factura = $remito->fields['id_factura'];
    $asoc_fact  = ($remito->fields['id_factura'])?1:0;

	$cliente    = $remito->fields['cliente'];
	$direccion  = $remito->fields['direccion'];
	$cuit       = $remito->fields['cuit'];
	$iib        = $remito->fields['iib'];
	$condicion_iva = $remito->fields['iva_tipo'];
	$iva	    = $remito->fields['iva_tasa'];
	$otros	    = $remito->fields['otros'];
	$pedido		= $remito->fields['pedido'];
	$venta	    = $remito->fields['venta'];
	$id_moneda	= $remito->fields['id_moneda'];
	$id_entidad	= $remito->fields['id_entidad'];
	$estado		= $remito->fields['estado'];
	$imprime	= $remito->fields['chk_precios'];
	$numeracion_remito = $remito->fields["id_numeracion_sucursal"];

	if (!$licitacion)
		$licitacion=$remito->fields['id_licitacion'];
	 unset($remito);
	 $nuevo=0;
 } //del if

if (!$fecha_remito)
	$fecha_remito = date("d/m/Y");

if (!$id_remito || $id_remito==-1 || $estado=='p')
	$can_finish = 1;

//original -> ($can_finish && (!$id_remito || $id_remito==-1))
//la nueva le permite a noelia cambiar el nro_facura
if (($can_finish && (!$id_remito || $id_remito==-1))||  $estado!='t' && ($_ses_user[login]=='noelia' || $_ses_user[login]=='gonzalo'))
	$can_search = 1;

$q="select * from log where id_remito=";
$q.=($id_remito)?$id_remito:-1;
$q.=" order by fecha desc";

$log=sql($q) or fin_pagina();


$q="select * from facturas join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
   where ";
if ($nro_factura){
	$nro_factura = str_replace(" ","",$nro_factura);
	$q.= "nro_factura='$nro_factura'";
	$factura = sql($q) or fin_pagina;
}
elseif ($id_factura && $id_factura!=-2) {
	$q.= "id_factura=$id_factura";
	$factura = sql($q) or fin_pagina();
}
if ($factura && $factura->RowCount()==0) {
		$id_factura = -2; //no se encontro el nro de factura
		$asoc_fact  = 0;
}
elseif ($factura) {
	$id_factura  = $factura->fields['id_factura'];
	$nro_factura = $factura->fields['numeracion']."-".$factura->fields['nro_factura'];
	if (!$licitacion)
		$licitacion = $factura->fields[id_licitacion];
	    $sql    = "select id_producto,id_renglones_oc from items_factura where id_factura=$id_factura";
	    $result = sql($sql) or fin_pagina();
//	$cmd="facturar_prod";
//	if ($cmd!="facturar_prod") {
	if ($result->fields["id_producto"]) {
		//SE BUSCA LA FACTURA
		//selecciono el id_producto y cuantos faltan
		$q="select if.id_producto,if.descripcion,if.precio,case when if.cant_prod is not null AND ir.cant_prod is not null
		    then if.cant_prod-ir.cant_prod
		    else if.cant_prod end as cant_prod  from  ";

		//selecciono los productos de la factura
		$q.="(select id_producto,descripcion,sum(cant_prod) as cant_prod,precio from items_factura where id_factura=$id_factura group by id_producto,precio,descripcion) if left join ";

		//selecciono los productos de los que se hizo remito
		$q.="(select id_producto,sum(cant_prod) as cant_prod,precio from items_remito where id_remito in ";

		//selecciono primero los remitos asociados a la factura en caso de haber
		$q.="(select id_remito from remitos where id_factura=$id_factura and remitos.estado!='a') group by id_producto,precio) ir on if.id_producto=ir.id_producto and (if.precio=ir.precio or ir.precio is null) ";

		//solo aquellos que falten
		$q.="where (if.cant_prod > ir.cant_prod OR ir.cant_prod is null) ";
	}
	else {
		//$q="select id_renglones_oc,descripcion,precio,cant_prod from items_factura where id_factura=$id_factura";
		$q="select if.id_renglones_oc,if.descripcion,if.precio,
			case when if.cant_prod is not null AND ir.cant_prod is not null then if.cant_prod-ir.cant_prod
    		else if.cant_prod end as cant_prod from (select id_renglones_oc,descripcion,sum(cant_prod) as cant_prod,
   			precio from facturacion.items_factura where id_factura=$id_factura
    		group by id_renglones_oc,precio,descripcion) if
   			left join (select id_renglones_oc,sum(cant_prod) as cant_prod,precio
     		from facturacion.items_remito where id_remito in
    		(select id_remito from facturacion.remitos where id_factura=$id_factura and remitos.estado!='a')
    		group by id_renglones_oc,precio) ir on if.id_renglones_oc=ir.id_renglones_oc
    		and (if.precio=ir.precio or ir.precio is null)
    		 where (if.cant_prod > ir.cant_prod OR ir.cant_prod is null) ";

	}

	//para que no cargue los items de factura cuando se agrega la factura
	//despues de haber creado el remito, solo si es nuevo
	if ($nuevo){
		$items_fac=sql($q) or fin_pagina();
	}

	if ($items_fac && $items_fac->RowCount()) {
		$cliente    = $factura->fields['cliente'];
		$id_entidad = $factura->fields['id_entidad'];
		$direccion  = $factura->fields['direccion'];
		$cuit       = $factura->fields['cuit'];
		$condicion_iva = $factura->fields['iva_tipo'];
		$iva   = $factura->fields['iva_tasa'];
		$otros = $factura->fields['otros'];
		$id_moneda = $factura->fields['id_moneda'];
		$pedido = $factura->fields['pedido'];
		$venta  = $factura->fields['venta'];
	}
 }
if ($cmd=="crear_remito") {
	$renglones_oc = PostvartoArray("chk_");
	//genero una lista de los renglones
	$list=implode(",",$renglones_oc);
	//selecciono los renglones que se deben facturar
	$q ="SELECT r1.*, r2.titulo,r2.id_licitacion,r2.codigo_renglon as cod FROM ";
	$q.="renglones_oc r1  ";
	$q.="join renglon r2 on r1.id_renglones_oc in ($list) AND r1.id_renglon=r2.id_renglon
	     order by cod";
	$renglones=sql($q, "493: ") or fin_pagina();

	$id_subir=$renglones->fields['id_subir'];
	$licitacion=$renglones->fields['id_licitacion'];

	$q ="SELECT l.*,e.*,ci.nombre as iva_tipo,ti.porcentaje as iva_tasa FROM ";
	$q.="licitacion l ";
	$q.="join entidad e using(id_entidad) ";
	$q.="left join condicion_iva ci using(id_condicion) ";
	$q.="left join tasa_iva ti using(id_iva) ";
	$q.="where id_licitacion=$licitacion ";
	$lic=sql($q, "500: ") or fin_pagina();

	$fecha_remito=date("d/m/Y");
	$cliente=$lic->fields['nombre'];
	$direccion=$lic->fields['direccion'];
	$cuit=$lic->fields['cuit'];
	$iib=$lic->fields['iib'];
	$condicion_iva=$lic->fields['iva_tipo'];
	$iva=$lic->fields['iva_tasa'];
	$otros=$lic->fields['otros'];
	if ($seg) {  //recupera lugar de entrega
		$query="select lugar_entrega,comentario_adicional
		        from subido_lic_oc where id_entrega_estimada=$seg";
	    $res=sql($query, "517: ") or fin_pagina();
		$otros.="\n Lugar de entrega ".$res->fields['lugar_entrega'];
		$comentario_adicional=$res->fields['comentario_adicional'];
	}
	$tipo_factura='b';
	$id_moneda=$lic->fields['id_moneda'];
	$id_entidad=$lic->fields['id_entidad'];
	$cotiz_valor=($lic->fields['valor_dolar_lic'])?number_format($lic->fields['valor_dolar_lic'],2,".","") :"";

}
?>
<!-- tabla de registro -->
<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> "  >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
//hidden con el valor del prompt:
//cuando se desea gardar un remito sin numero de factura entonces se coloca el valor del prompt
//en este hidden.
echo "<input type='hidden' name='valor' value=''>";
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

?>
</table>
</div>

<hr align="center" size="0">
<?$color='blue';?>
  <table align="center" width="95%">
    <tr>
       <td align="center">
          <?switch ($estado) {
               case 'a':$nombre_estado='Anulado';
                        $color='red';
                        $tam='size=+1';
                        break;
          	   case 'p':$nombre_estado='Pendiente';
                        break;
               case 't':$nombre_estado='En transito';
                        break;
               case 'r':$nombre_estado='Recibido';
                        break;
          
          }
          ?>
          <?="<font color='$color' $tam> Estado del Remito: ".$nombre_estado."</font>"?>
       </td>
       <td width="10%" align="right">
          <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/remitos/ayuda_rem_nuevo.htm" ?>', 'NUEVOS REMITOS')" >
       </td>   
    </tr>      
  <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
  <input name="cambio_entidad" type="hidden" value="no_cambio">
  <table width="100%" border="0" cellspacing="1" cellpadding="1" align="center">
    <tr>
    <td height="29" colspan="2" align="center" valign="top">
    <table>
    <tr>
<?
   if ($id_factura!="") {
?>
    <td valign="top">
    <input type="hidden" name="id_fact" value="<?=$id_factura;?>">
    <?$link=encode_link("../facturas/factura_nueva.php",array("id_factura"=>$id_factura,"volver_remito"=>1));?>
    <input type="button" name="bfactura" value="ver factura" onclick="window.open('<?=$link?>','','');">
    </td>
 <?
 }
 if ($id_factura || $_POST["pagina_volver"] == "seleccionar_renglon_adj.php") {?>
    <td valign="top">
    <font color="Red" <? if (!$can_search) echo " disabled " ?>> <strong>N&ordm;
        factura</strong> </font>
    </td>
    <td>
 <?}  	
     	    $g_facturas=array_merge(array(""), explode(",", $_POST["h_facturas"]));
    		$cant=count($g_facturas);
    		$n_fact=array();
    		$tipo_fact=array();
    		$ind=1;
    		$j=1;
    		$n_fact[0]="";
    		$id_fact[0]="";
    		while ($ind <= $cant-1) {
    		 $n_fact[$j]=$g_facturas[$ind]; //nro fact
    		 $tipo_fact[$g_facturas[$ind]]=$g_facturas[++$ind]; //id_fact
             $j++; 
    		 $ind++;
    		}
    		 echo "<input type='hidden' name='tipos' value='".comprimir_variable($tipo_fact)."'> ";   		
			 if ($_POST["pagina_volver"]=="seleccionar_renglon_adj.php"){
					g_draw_value_select("nro_factura", (($_POST["nro_factura"])?$_POST["nro_factura"]:$n_fact[0]), $n_fact, $n_fact, 1);
			 } else {
				$link=encode_link("../facturas/factura_listar.php",array("backto"=>"remito_nuevo","cmd"=>"pendientes","_ses_global_extra"=>array()));
   				if ($id_factura) {?>
   			    <input  readonly type='text' name='nro_factura' value='<?=$nro_factura?>' <?if (!$can_search) echo(' disabled '); ?>>
            	&nbsp; 
            	<?}?>
        	    <input type="button" name="traer_factura" value="Asociar Factura" title="Recupera la informacion de la factura" <? if (!$can_search) echo " disabled " ?> onclick="document.all.guardar.value=1;location.href='<?=$link?>'" >
        	    <?
				 //si se encontro la factura AND (faltan remitos OR asociada)
                if ($items_fac && $items_fac->RowCount() || $asoc_fact) {
                 ?>
             	&nbsp;<input type="checkbox" name="chk_asociar" value="1" checked <? if (!$can_finish) echo " disabled " ?>> &nbsp;Asociar
                <?
       	      }
              ?>
              <br><? if ($id_factura==-2)
        			  echo "<br>No se encontro la factura";
         			  elseif ($items_fac && $items_fac->RowCount()==0)
        				echo "<br>Ya estan los remitos para esa factura";
        			    else
        				echo "<br>$msg";
			  }
       		?>
      </td>
      </tr>
      </table>
    </tr>
    <tr>
      <td width="54%" align="center" valign="top" nowrap>
      <table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr align="center" class="tablaEnc">
            <td height="20" colspan="3"> <strong>Cliente </strong> </td>
          </tr>
          <tr>
            <td colspan="2"> <table>
                <tr>
                  <td align="left" width="80%">
                   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;               
                    <input type="button" name="clientes" value="Elegir cliente" <? if (!$can_finish) echo " disabled " ?> title="Permite elegir cliente para el remito" 
                     onclick="if (wcliente==0 || wcliente.closed)
	                                    wcliente=window.open('<?=encode_link('../general/seleccionar_clientes.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
                       else
	                   if (!wcliente.closed)     
	 	               wcliente.focus();"
                    >  
                    
                    
                    </td>
                </tr>
                <tr>
                  <td> <strong>Nombre&nbsp;&nbsp;&nbsp;</strong>
                   <input name="nbre" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<? if($cliente!=""){echo $cliente;}?>" <? if (!$can_finish) echo " disabled " ?>  size="47">
                  </td>
                </tr>
              </table></td>
          </tr>
          <tr align="left">
            <td height="30" colspan="3"> <strong>Dirección</strong>
             <input name="dir" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $direccion ?>"  <? if (!$can_finish) echo " disabled " ?> size="30"> <font color=red><i>(No se imprime en el pdf).</i></font>
            </td>
          </tr>
          <tr>
            <td width="52%" height="24" align="left" nowrap><strong>C.U.I.T</strong>
              &nbsp; &nbsp; <input name="cuit" readonly title="Para editar los campos del cliente presione el boton elegir cliente" type="text"  value="<?= $cuit ?>"  <? if (!$can_finish) echo " disabled " ?> size="18" >
            </td>
            <td height="24" colspan="2" align="left" nowrap>&nbsp; <strong>I.I.B.</strong>
              <input name="iib" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iib ?>" <? if (!$can_finish) echo " disabled " ?> size="17" >
            </td>
          </tr>
          <tr align="left">
            <td height="35" colspan="3"><strong>Condición I.V.A</strong>
             <input name="condicion_iva"  readonly title="Para editar los campos del cliente presione el boton elegir cliente" type="text"  value="<?= $condicion_iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="20" >
              &nbsp;&nbsp; <strong> I.V.A %</strong>
               <input name="iva" type="text" readonly title="Para editar los campos del cliente presione el boton elegir cliente" value="<?= $iva ?>"  <? if (!$can_finish) echo " disabled " ?> size="6" ></td>
          </tr>
          <tr>
            <td colspan="3"> <div align="left"> <strong>Otros</strong>
                <textarea name="otros" readonly title="Para editar los campos del cliente presione el boton elegir cliente" cols="50" rows="2" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><? echo $otros; ?></textarea>
              </div></td>
          </tr>
          <?if ($cmd=='anulados'){
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
          <?}?>
        </table>
      </td>
      <td width="46%" align="center" valign="top" nowrap>
      <table width="90%" border="1" cellpadding="0" cellspacing="0" bgcolor=<?=$bgcolor_out?>>
          <tr class="tablaEnc">
            <td colspan="2" height="20" align="center"><strong>Remito</strong></td>
          </tr>
          <tr>
            <td id="td_fecha_remito" height="25"><strong>Fecha Remito</strong></td>
            <td align="right"> <input name="fecha_remito" type="text" <? if (!$can_finish) echo " disabled " ?> value="<?= $fecha_remito ?>" size="10">
              <img <? if (!$can_finish) echo " disabled " ?> src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para
			   seleccionar la fecha'  onClick="javascript:popUpCalendar(td_fecha_remito, fecha_remito, 'dd/mm/yyyy');">
            </td>
          </tr>
          <?
          $sql="select * from numeracion_sucursal where activo=1";
          $res=sql($sql) or fin_pagina();
          ?>          
          <tr>
            <td width="45%" height="25"><strong>Numeración</strong></td>
            <td width=55% align="right">
            <select name='numeracion' style="width:55%">
               <?
               for ($i=0;$i<$res->recordcount();$i++){
               	 $numeracion    = $res->fields["numeracion"];
               	 $id_numeracion = $res->fields["id_numeracion_sucursal"];
               	 $sucursal      = $res->fields["sucursal"];
               	 ($id_numeracion==$numeracion_remito)?$selected_num="selected":$selected_num="";
               	 ?>
               	 <option value="<?=$id_numeracion?>" <?=$selected_num?>><?=$numeracion?></option>
               	 <?
               	$res->movenext(); 
               }
               ?>
            </td>
          </tr>
          <tr>
            <td width="45%" height="25"><strong>Numero Remito</strong></td>
            <td width="55%" align="right">
			<input name="nro_remito" type="text" value="<?= $nro_remito ?>" <? if (!$can_finish) echo " disabled " ?> size="18"></td>
          </tr>
          <tr>
            <td height="25"><strong>Pedido N&ordm;</strong></td>
            <td align="right"><input name="pedido" type="text" value="<?= $pedido ?>" <? if (!$can_finish) echo " disabled " ?> size="18"></td>
          </tr>
          <tr>
            <td height="25"><strong>Venta</strong></td>
            <td align="right"><input name="venta" type="text"  value="<?= $venta ?>" <? if (!$can_finish) echo " disabled " ?> size="18"></td>
          </tr>
          <tr>
            <td height="25"><strong>Moneda</strong></td>
            <td align="right"> <select  name="select_moneda" <? if (!$can_finish) echo " disabled " ?>>
                <option value="-1">Seleccione el tipo</option>
                <?
				$q="select * from moneda";
				$moneda=sql($q) or fin_pagina();
				while (!$moneda->EOF) {
				?>
				                <option value="<?= $moneda->fields['id_moneda'] ?>" <? if ($moneda->fields['id_moneda']==$id_moneda) echo " selected" ?> >
				                <?= $moneda->fields['nombre'] ?>
				                </option>
				                <?
				 $moneda->MoveNext();
				}
				?>
              </select> </td>
          </tr>
        </table>
        <div>
          <table width="90%" border="0" cellspacing="1" cellpadding="1">
            <tr>
            <td width="45%">&nbsp;</td>
            <td width="55%">&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td align="right">Imprimir con precios
                <input type="checkbox" name="chk_precios" value="1" <?if ($imprime==1) echo 'checked';?><? if (!$can_finish) echo " disabled " ?>></td>
            </tr>
       </table>
       </div>
      </td>
    </tr>
  </table>
  <br>
  <table width="106%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td align="left" >&nbsp;Id Licitacion &nbsp; <input name="licitacion" type="text" size="6" value="<?=$licitacion ?>" <? if (!$can_finish) echo " disabled " ?>>
        &nbsp; <input name="boton_buscar" type="button" title="Ir a ver licitaciones y traer el ID" value="Buscar"<? if (!$can_finish) echo " disabled " ?>
        onclick=
		"
		if (document.all.guardar.value!=0)
		{
			if(confirm('Se perderan los valores modificados desea continuar?'))
				location.href='<?=encode_link("$html_root/modulos/licitaciones/licitaciones_view.php",array('backto'=>$_SERVER['SCRIPT_NAME'],"_ses_global_extra"=>array('id_remito'=>$id_remito) )) ?>';
		}
		else
		{
			location.href='<?=encode_link("$html_root/modulos/licitaciones/licitaciones_view.php",array('backto'=>$_SERVER['SCRIPT_NAME'],"_ses_global_extra"=>array('id_remito'=>$id_remito) )) ?>';
		}
		"> &nbsp;
        <input type="checkbox" name="asociar" <? if ($licitacion) echo " checked " ?> <? if (!$can_finish) echo " disabled " ?> value="1" >
        <font title="Asocia la licitacion a este Remito"> Asociar </font>
		<?
		if ($licitacion) {
			$link_volver = encode_link($html_root."/index.php",array("menu"=>"remito_listar","extra"=>array("cmd"=>"detalle","id_remito"=>$id_remito)));
			echo "<input type='button' name='ir_a_lic' value='Ir' onClick=\"location.href='".encode_link("../licitaciones/licitaciones_view",array("cmd1"=>"detalle","ID"=>$licitacion,"link_volver"=>"parent.document.location='$link_volver';"))."';\">";
		}
		?>
      </td>
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
	$q="select * from items_remito where id_remito=$id_remito";
	
	if ($id_remito && $id_remito!=-1)
	 	$items_remito=sql($q) or fin_pagina();
	
	$i=0;
	$total=0;
	while ($items_remito && !$items_remito->EOF) {
	?>
    <tr>
      <td valign="middle" align="center"> <input type="checkbox" name="chk" <? if (!$can_finish) echo " disabled " ?>></td>
      <td height="25"> <div align="center">
          <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $items_remito->fields['cant_prod'] ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
          <?
		  if ($items_remito->fields['id_producto']) {
		  ?>
		  <input type="hidden" name="idp_<?=$i ?>" value="<?= $items_remito->fields['id_producto'] ?>" >
		  <?
		  }
		  else {
		  ?>
		  <input type="hidden" name="idr_<?=$i ?>" value="<?= $items_remito->fields['id_renglones_oc'] ?>" >
		  <?
		  }
		  ?>
          <input type="hidden" name="idi_<?=$i ?>" value="<?= $items_remito->fields['id_item'] ?>" >
        </div></td>
      <td height="25"> <div align="center">
          <textarea name="desc_<?=$i ?>" cols="60" rows="5" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $items_remito->fields['descripcion'] ?></textarea>
        </div></td>
      <td height="25"> <div align="center">
          <input name="precio_<?=$i ?>" type="text" style="text-align: right" size="10" value="<?= number_format($items_remito->fields['precio'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
        </div></td>
      <td height="25"> <div align="center">
          <input name="subtotal_<?=$i++ ?>" type="text" style="text-align:right" readonly  size="10" value="<?= number_format($items_remito->fields['precio']*$items_remito->fields['cant_prod'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?>>
        </div></td>
    </tr>
<?
	$total+=$items_remito->fields['precio']*$items_remito->fields['cant_prod'];
	$items_remito->MoveNext();
}

//LA CONSULTA SE HACE MAS ARRIBA

while ($items_fac && !$items_fac->EOF && !$asoc_fact) {
?>
    <tr>
      <td valign="middle" align="center"> <input type="checkbox" name="chk" <? if (!$can_finish) echo " disabled " ?>></td>
      <td height="25"> <div align="center">
          <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $items_fac->fields['cant_prod'] ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
          <?
		  if ($items_fac->fields['id_renglones_oc']) {
		  ?>
		  <input type="hidden" name="idr_<?=$i ?>" value="<?= $items_fac->fields['id_renglones_oc'] ?>" >
		  <?
		  }
		  else {//ni se les ocurra borrar este campo
		  	?>
		  <input type="hidden" name="idg_<?=$i ?>" value="-7" >
		  <?
		  }

		  ?>
		  <input type="hidden" name="idi_<?=$i ?>" value="<?= $items_fac->fields['id_item'] ?>" >
        </div></td>
      <td height="25"> <div align="center">
          <textarea name="desc_<?=$i ?>" cols="60" rows="5" wrap="VIRTUAL" <? if (!$can_finish) echo " disabled " ?>><?= $items_fac->fields['descripcion'] ?></textarea>
        </div></td>
      <td height="25"> <div align="center">
          <input name="precio_<?=$i ?>" type="text" style="text-align: right" size="10" value="<?= number_format($items_fac->fields['precio'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
        </div></td>
      <td height="25"> <div align="center">
          <input name="subtotal_<?=$i++ ?>" type="text" style="text-align:right" readonly  size="10" value="<?= number_format($items_fac->fields['precio']*$items_fac->fields['cant_prod'],2,".","") ?>" <? if (!$can_finish) echo " disabled " ?>>
        </div></td>
    </tr>
<?
	$total+=$items_fac->fields['precio']*$items_fac->fields['cant_prod'];
	$items_fac->MoveNext();
}
//si hay que hacer la facturacion de seguimiento de produccion
if ($cmd=="crear_remito") {
	$cant_prod=$renglones->RecordCount();
	while (!$renglones->EOF) {
?>
    <tr>
      <td valign="middle" align="center"> <input type="checkbox" name="chk" <? if (!$can_finish) echo " disabled " ?>></td>
      <td height="25"> <div align="center">
          <input name="cant_<?=$i ?>" type="text" style="text-align: right" size="4" value="<?= $renglones->fields['cantidad'] ?>" <? if (!$can_finish) echo " disabled " ?> onchange="calcular(this)">
          <input type="hidden" name="idr_<?=$i ?>" value="<?= $renglones->fields['id_renglones_oc'] ?>" >
          <input type="hidden" name="idi_<?=$i ?>" value="" >
        </div></td>
        <? if ($i==$cant_prod-1) $concat=$comentario_adicional; //concateno, al ultimo producto, el comentario cargado en entregas
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
?>
  </table>
  <br>
<?
// Para la parte de subir los archivos de escaneados
if ($id_remito) {
	$sql="select id_archivo_remito,fecha,usuarios.nombre || ' ' || usuarios.apellido as nombre,archivo,size from archivo_remito join usuarios using(id_usuario) where id_remito=$id_remito";
	$resultado=sql($sql, "868: ") or fin_pagina();
?>
<table width=100% border=0 cellpadding="2" cellspacing="2">
<tr id=mo>
	<td>Fecha   </td>
	<td>Usuario </td>
	<td>Archivo </td>
	<td>Tamaño  </td>
</tr>
<?
	while ($fila=$resultado->fetchrow()) {
		$lin=encode_link("remito_nuevo.php",array ("file" =>$fila["archivo"],"size" => $fila["size"],"accion" => "download"));
		//echo "<tr id=ma style='cursor:hand;' onClick=\"window.open('../../uploads/remitos/".$fila["archivo"]."','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');\">\n";
		tr_tag($lin);
		echo "<td>".$fila["fecha"]."</td>\n";
		echo "<td>".$fila["nombre"]."</td>\n";
		echo "<td>".$fila["archivo"]."</td>\n";
		echo "<td>".$fila["size"]." Bytes</td>\n";
		echo "</tr>\n";
	}
?>
<tr>
	<td align=right colspan=4>
		<b>Subir archivo:</b> <input type=file name=archivo>
	</td>
</tr>
</table>
<br>
<? } ?>
  <table width="100%" height="28" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td height="26" align="right">
<?
if ($seg) {
	$sql="SELECT subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion as licitacion,subido_lic_oc.vence_oc as vencimiento,subido_lic_oc.nro_orden as oc,entidad.nombre as cliente,entrega_estimada.nro as numero FROM subido_lic_oc ";
	$sql.="LEFT JOIN licitacion USING (id_licitacion) LEFT JOIN entidad USING (id_entidad) ";
	$sql.="LEFT JOIN entrega_estimada USING (id_entrega_estimada) WHERE subido_lic_oc.id_entrega_estimada=$seg";
	$result=sql($sql) or fin_pagina();
	$fila=$result->fetchrow();
	$fila["pagina_volver"]="entregas.php";
}
if ($items_fac) $items_fac->MoveFirst();

if ($seg) {
 ?>
		<input name="boton" type="button" value="Cerrar" title="Cerrar" onclick="window.close();">
<?
}
if (permisos_check("inicio","anula_remito")){
?>
        	<input name="boton" type="button" <? if ($estado=='p' || $estado=='a') echo " disabled " ?> value="Anular" title="Se anulara el remito y dejara de ser valido"
        	onclick="if (confirm('¿Está seguro que desea Anular el Remito?'))
     			window.open('<?=encode_link("comentario_anulacion_remito.php",array("nro"=>$nro_remito,"tipo"=>"remito"))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');"
		   	>
<?} //if ($items_fac->fields["id_renglones_oc"] || $seg) $can_finish=false; 
if ($seg) $can_finish=false; 
?>
 		<input name="boton" type="button"  value="Eliminar" <? if ((!$can_finish)||($i==0)) echo " disabled " ?> onclick=
		"if (confirm('Seguro que quieres eliminar los Items seleccionados'))
		   borrar_items();
		" title="Elimina los elementos seleccionados">
				<input name="boton" type="button"  value="Agregar" title="Agrega uno o mas productos" <? if (!$can_finish) echo " disabled " ?> onclick="cargar()">
				<input name="boton" type="submit"  value="Guardar" title="Guarda los cambios y permite posteriores modificaciones" <? if (!$can_finish) echo " disabled " ?> onclick=
		"
		if (chk_campos()!=0)
		{
		alert (msg);
		msg='';
		return false;
		}
		if(control_datos()==0)
			return false;
		if (document.all.licitacion.value=='')
			return (confirm('El Remito no esta asociado con una licitacion\n¿desea continuar?')) ;
		
		">
		<?
if ($estado!="t" and $estado!="a" and $estado!="r") $can_finish=true; 
?>
        <input name="boton" type="submit" value="Terminar" title="No se permitiran posteriores modificaciones" <? if (!$can_finish) echo " disabled " ?> onclick=
		"
		if (chk_campos(1)!=0)
		{
		alert (msg);
		msg='';
		return false;
		}
		if(control_datos()==0)
			return false;
		
		
		">
        <input name="subir_remito" type="submit" value="Subir remito escaneado" <? if ($estado!='t' && $estado!='r') echo " disabled " ?>>
      </td>
      <td width="87" align="right"><strong>TOTAL</strong></td>
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
  <input type='hidden' name='guardar' value='<?if ($parametros['cmd']=='asociar_factura') echo 1;else echo 0;?>'>
  <input type='hidden' name='id_remito' value='<?= ($id_remito)?$id_remito:-1 ?>'>
  <input type='hidden' name='id_factura' value='<?=$id_factura?>'>
  <input type="hidden" name="estado" value="<?= $estado ?>">
  </form>
  </body>
  </html>
  <?
	//}
  ?>