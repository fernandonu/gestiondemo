/*
Autor:	GACZ
Fecha:	13/01/2006

$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2006/07/18 19:31:18 $
*/
//funcion que quita los espacios al principio y al final
function trim(inputString) {
   // Removes leading and trailing spaces from the passed string. Also removes
   // consecutive spaces and replaces it with one space. If something besides
   // a string is passed in (null, custom object, etc.) then return the input.
   if (typeof inputString != "string") { return inputString; }
   var retValue = inputString;
   var ch = retValue.substring(0, 1);
   while (ch == " ") { // Check for spaces at the beginning of the string
      retValue = retValue.substring(1, retValue.length);
      ch = retValue.substring(0, 1);
   }
   ch = retValue.substring(retValue.length-1, retValue.length);
   while (ch == " ") { // Check for spaces at the end of the string
      retValue = retValue.substring(0, retValue.length-1);
      ch = retValue.substring(retValue.length-1, retValue.length);
   }
   while (retValue.indexOf("  ") != -1) { // Note that there are two spaces in the string - look for multiple spaces within the string
      retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length); // Again, there are two spaces in each of the strings
   }
   return retValue; // Return the trimmed string back to the user
} // Ends the "trim" function
/****************************************************************/
var o = null;
var isNN = (navigator.appName.indexOf("Netscape")!=-1);

function beginEditing(menu){
//finish();

if(menu[menu.selectedIndex].id == "editable"){
o = new Object();
o.editOption = menu[menu.selectedIndex];
o.editOption.old = o.editOption.text;
o.editOption.text = "_";
menu.blur();
window.focus();
document.onkeypress = keyPressHandler;
document.onkeydown = keyDownHandler;
} // fin if

/**********************************************
select: selecciona un string dentro de un listbox, sino lo encuentra lo añade
@listbox objeto lista tipo select
@string el string a buscar dentro de la lista
@añadir booleano que indica si se añade en caso de no encontrarse (default=1)
@return 1 si lo encontro
        0 si no se encontro
Autor:GACZ
***********************************************/
function select(listbox,string,añadir)
{
var selecciono=false;
var i=0;
for (i=0; i < listbox.lenght ;i++)
{
 if (string==listbox.options[i].text)
 {
  listbox.options[i].selected=true;
  selecciono=true;
  return 1;
 }
}
if (!selecciono)
{
  //añadir una nueva opcion
 if (añadir)
 {
  listbox.lenght++;
  listbox.options[i].text=string;
  listbox.options[i].selected=true;
 }
 return 0;
}
}
/***********************************************/
function keyDownHandler(e){
var keyCode = (isNN)?e.which:event.keyCode;
return (keyCode!=8 || keyPressHandler(e));
} //fin function keydownhandler
function keyPressHandler(e){
var option = o.editOption;
var keyCode = (isNN)?e.which:event.keyCode;
if(keyCode==8 || keyCode==37)
option.text = option.text.substring(0,option.text.length-2)+"_";
else if(keyCode==13){
finish();
}// fin else if
else if(keyCode!=0)
option.text = option.text.substring(0,option.text.length-1) + String.fromCharCode(keyCode) + "_";
return false;
}// fin keypresshandler
function finish(){
if(o!=null){
option = o.editOption;
if(option.text.length > 1)
option.text = option.text.substring(0,option.text.length-1);
else
option.text = option.old;
document.onkeypress = null;
document.onkeydown = null;
o = null;
}// fin if
} //fin function finish
} //fin function begineditingthis

function getvalue()
{
alert(this.options[this.selectedIndex].text);
document.form.txtoption.value = this.options[this.selectedIndex].text;
}

//MORE_ROWS BY GACZ
//aumenta dinamicamente el numero de lineas de un @textarea al tipear enter
//tiene un maximo @maxrows
//llamar en el evento onkeypress
function more_rows(textarea,maxrows)
{
if (typeof(maxrows)=='undefined' || isNaN(maxrows))
	maxrows=0;

if (event.keyCode == 13 )
{
	if (maxrows==0 || textarea.rows < maxrows)
	textarea.rows++;
}

}

//funcion para desplegar la forma de pago
function desplegar_forma_pago()
{

 var fila=document.all.table_pago.insertRow();
 var cant_pagos=document.all.cant_pagos.value;
 var cant_nc=document.all.cant_nc.value;
 var ver_dolar=eval("document.all.mostrar_dolar");
 var i=0;
 var j=0;
 var dolar_ext=0;
 var simbolo="";
 document.all.table_pago.style.visibility='visible';//mostramos la tabla
 if(cant_nc>0)
  dolar_ext=1;
 if(ver_dolar.value==1)
 {
  simbolo="U$S ";
 }
 else
 {
  simbolo="$ ";
 }
 fila.insertCell(0).colSpan=4;
 fila.cells[0].innerHTML="<font size=2><b>Forma de Pago: </b>"+document.all.nombre_forma.value+"</b></font>";

 //si hay notas de credito generamos la tabla para mostrarlas
 if(cant_nc>0)
 {
  var filanc0=document.all.table_pago.insertRow();
  filanc0.insertCell(0).colSpan=4;
  filanc0.cells[0].innerHTML="&nbsp;";

  var filanc=document.all.table_pago.insertRow();
  filanc.insertCell(0).colSpan=4;
  filanc.align="center";
  filanc.cells[0].innerHTML="<b>Notas de Crédito utilizadas para el pago</b>";

  var filanc1=document.all.table_pago.insertRow();
  filanc1.insertCell(0).align="center";
  filanc1.cells[0].innerHTML="<b>Nro. Nota de Crédito</b>";
  filanc1.insertCell(1).align="center";
  filanc1.cells[1].innerHTML="<b>Monto</b>";
  filanc1.insertCell(2).align="center";
  filanc1.cells[2].innerHTML="<b>Valor Dolar</b>";
  filanc1.insertCell(3).align="center";
  filanc1.cells[3].innerHTML="<b>Observaciones</b>";

  while(j<cant_nc)
  {var filanc2=document.all.table_pago.insertRow();
   var nro_nota=eval("document.all.notac_nro_"+j);
   var simbolo_nota=eval("document.all.notac_moneda_"+j);
   var monto_nota=eval("document.all.notac_monto_"+j);
   var obs_nota=eval("document.all.notac_obs_"+j);
   var dolar_nota=eval("document.all.notac_valor_dolar_"+j);
   filanc2.insertCell(0).align="center";
   filanc2.cells[0].innerHTML=nro_nota.value;
   filanc2.insertCell(1).align="center";
   filanc2.cells[1].innerHTML=simbolo_nota.value+" -"+monto_nota.value;
   filanc2.insertCell(2).align="center";
   if(dolar_nota.value!=-1)
    filanc2.cells[2].innerHTML=dolar_nota.value;
   else
    filanc2.cells[2].innerHTML="No se aplica";

   filanc2.insertCell(3).align="center";
   filanc2.cells[3].innerHTML=obs_nota.value;
   j++;
  }//del while

  var filanc3=document.all.table_pago.insertRow();
  filanc3.insertCell(0).colSpan=4;
  filanc3.cells[0].innerHTML="&nbsp;";

 }//del if de mostrar notas de credito

 //generamos los pagos de la forma de pago
 while(i<cant_pagos)
 {var fila1=document.all.table_pago.insertRow();
  fila1.insertCell(0).bgcolor="red";
  fila1.insertCell(0).colSpan=4;
  fila1.cells[0].innerHTML="<center><b>Pago Número: </b>"+(i+1)+"</center>";

  var fila2=document.all.table_pago.insertRow();
  fila2.insertCell(0).innerHTML="<b>Tipo de Pago</b>";
  fila2.insertCell(1).innerHTML="<b>Cantidad de Días</b>";
  if(ver_dolar.value!=1)
  {
   fila2.insertCell(2).colSpan="2";
   fila2.cells[2].innerHTML="<b>Monto</b>";
  }
  else
   fila2.insertCell(2).innerHTML="<b>Monto</b>";
  if(ver_dolar.value==1)
   fila2.insertCell(3).innerHTML="<b>Valor Dolar</b>";

  var fila3=document.all.table_pago.insertRow();
  var tipo=eval("document.all.pago_"+i+"_tipo");
  fila3.insertCell(0).innerHTML=tipo.value;
  var dias=eval("document.all.pago_"+i+"_dias");
  fila3.insertCell(1).innerHTML=dias.value;
  var monto="";
  monto=eval("document.all.pago_"+i+"_monto");
  if(monto.value=="")
   monto.value="no especificado";
  if(ver_dolar.value!=1)
  {
   fila3.insertCell(2).colSpan="2";
   fila3.cells[2].innerHTML=simbolo+monto.value;
  }
  else
   fila3.insertCell(2).innerHTML=simbolo+monto.value;
  if(ver_dolar.value==1)
  {dolar=eval("document.all.pago_"+i+"_dolar");
   if(dolar.value=="")
    dolar.value="no especificado";
   fila3.insertCell(3).innerHTML=dolar.value;
  }

  i++;

 }
 document.all.td_pago.title='Ocultar la forma de pago para esta orden de compra';
 document.all.td_pago.onclick=contraer_forma_pago;
}

function contraer_forma_pago()
{
  var long=document.all.table_pago.rows.length;
  var i=0;
  for(i;i<long;i++)
   document.all.table_pago.deleteRow();

 document.all.table_pago.style.visibility='hidden';//ocultamos la tabla

 document.all.td_pago.title='Desplegar la forma de pago para esta orden de compra';
 document.all.td_pago.onclick=desplegar_forma_pago;
}

//ventana de cambios de productos para una fila
var vent_cambio_prod=new Object();
vent_cambio_prod.closed=true;


//controla que la fecha de entrega que el usuario ingresa no sea menor que
//la fecha de creacion de la OC.
function check_fecha_legal()
{fecha_creacion=document.all.fecha_creacion.value;

 fecha_entrega=new String();
 fecha_entrega=document.all.fecha_entrega.value;
 fecha=new Array();
 fecha=fecha_entrega.split("/");
 fecha_ent=fecha[2]+"-"+fecha[1]+"-"+fecha[0];
 if(fecha_ent<fecha_creacion)
  return 0;
 else
  return 1;
}

//esto es para truncar los flotantes a x posiciones
//la condicion es para saber si la funcion esta definida para la clase Number
if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}

function total()
{
	var total=0;
	 for (var i=0; i < document.form1.length ; i++)
	 {
		  if (document.form1.elements[i].name.indexOf("subtotal_")!=-1)
		  {
			 var subtotal=document.form1.elements[i];
		  	 var id_item=subtotal.name.substring(subtotal.name.indexOf("_")+1,subtotal.name.lenght);
			 var cantidad=eval("document.all.cant_"+id_item);
			 var unitario=eval("document.all.unitario_"+id_item);
			 total+=parseFloat(subtotal.value);
		  }
	 }
	var t1= new String(total.toFixed(2));
	if (t1.indexOf(".")==-1)
		document.all.total.value=t1+".00";
	else
	  document.all.total.value=t1;
}

//calcula el subtotal dependiendo de la cantidad
function calcular(textfield)
{
 id_item=textfield.name.substring(textfield.name.indexOf("_")+1,textfield.name.lenght);

 var subtotal=eval("document.all.subtotal_"+ id_item);

 var precio=eval("document.all.unitario_"+ id_item);
 var cantidad=eval("document.all.cant_"+ id_item);
 //var num_fila=parseFloat(id_item) + 1;

// if ((control_numero(cantidad,"Cantidad en la fila "+ num_fila) == 0) && (control_numero(precio,"Precio Unitario en la fila "+ num_fila)) ==0 ) {
 if ((control_numero(cantidad,"Cantidad del producto") == 0) && (control_numero(precio,"Precio Unitario del Producto")) ==0 ) {

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
}

/**************************************************************
 FUNCIONES QUE USA LA VENTANA HIJO
 NOTA:
		LAS FUNCIONES TOMAN EL CONTEXTO DONDE SON DEFINIDAS
		OJO CON EL ACCESO A LAS VARIABLES
***************************************************************/
//variable que contiene la ventana para selecciona el cliente
var wcliente=0;
var wpagar=0;

function cargar_cliente()
{
 document.all.id_cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 //document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
 document.all.cliente.value=wcliente.document.all.nbrecl.value;
 if (wcliente.document.all.chk_direccion.checked)
	document.all.entrega.value=wcliente.document.all.direccion.value;
//////////////BROGGI - para llevar control de la entidad mas usada por cada usuario
 document.all.cambio_entidad.value="si_cambio";

}//de function cargar_cliente()


//variable que contiene la ventana hijo productos
var wproductos=0;
function cargar()
{
 	var largo,i=0,prod,insertar_ok=1;

   //Para insertar una fila
   var items=document.all.items.value++;
   //inserta al final
   var fila=document.all.productos.insertRow(document.all.productos.rows.length );

   fila.insertCell(0).innerHTML="<input type='hidden' id='' value='' name='idprov_"+items +"' /><div align='center'>"+
   "<input name='chk' class='estilos_check' type='checkbox' id='chk' value='1' /></div>";

   fila.insertCell(1).innerHTML="<textarea name='desc_orig_"+items +"' style=\"width:90%\" rows='1' wrap='VIRTUAL' id='descripcion'></textarea>";

   fila.insertCell(2).innerHTML="<div align='center'> <input name='cant_"+
   items+"' type='text' id='cantidad' size='6' value='1' style='text-align:right' "+
   "onchange='calcular(this)' ></div>";

   fila.insertCell(3).innerHTML="<div align='center'><input name='unitario_"+items+"' type='text' id='unitario' size='10' style='text-align:right' "+
   "value=0 onchange='this.value=this.value.replace(\",\",\".\");calcular(this)' /></div> ";

   fila.insertCell(4).innerHTML="<div align='center'> <input name='subtotal_"+
   items+"' value=0 type='text' tabindex=-1 readonly id='subtotal' size='12' style='text-align:right'></div>";

   if (document.all.boton_eliminar.disabled)
	document.all.boton_eliminar.disabled=0;

   document.all.guardar.value++;
   location.href="#total";
   total();
}//de function cargar()


/************************************
Agregar una fila para el transporte
*************************************/
var pos_trans;
function agregar_trans()
{
  //Para insertar una fila
  var items=document.all.items.value++;
  pos_trans=items;
  //inserta al final
  var fila=document.all.productos.insertRow(document.all.productos.rows.length );
  var a="";

  fila.insertCell(0).innerHTML="<div align='center'><input name='chk' type='checkbox' readonly id='chk' value='1'></div><input type='hidden' name='idp_"+
  items +"' value='-2'>";

  fila.insertCell(1).innerHTML="<input type='hidden' value='' name='h_desc_"+items+"'><input type='hidden' value='Conexo:'"+
  a+" name='desc_orig_"+items+"'>"+
  a+"<textarea name='desc_"+items +"' style=\"width:90%\" rows='1' wrap='VIRTUAL' id='descripcion' readonly>"+
  a+"Conexo:</textarea><input type='button' name='desc_adic_"+
  items +"' value='E' title='Agregar descripción adicional del producto' onclick=\"window.open('../ord_compra/desc_adicional.php?posicion="+items+"','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')\">";

  fila.insertCell(2).innerHTML="<div align='center'> <input name='cant_"+
  items+"' type='text' readonly id='cantidad' size='6' value='1' style='text-align:right' "+
  "onchange='calcular(this)' ></div>";

  fila.insertCell(3).innerHTML="<div align='left'> <input name='unitario_"+
  items+"' type='text' id='unitario' size='10' style='text-align:right' value='0.00'"+
  a+"onchange='this.value=this.value.replace(\",\",\".\");calcular(this)'> "+
  "<input type='button' name='H_button' value='H' title='Historial del Precio' onclick='window.open(\"../licitaciones/historial_comentarios.php?id_producto=-1\",\"\",\"toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520\")'>"+
  "</div> ";

  fila.insertCell(4).innerHTML="<div align='center'> <input name='subtotal_"+
  items+"' type='text' readonly id='subtotal' size='12' style='text-align:right' value='0.00'"+
  a+"'></div>";

  //usamos el boton de agregar transporte para transformarlo al boton de
  //guardar los cambios hechos.
  document.all.agregar_transporte.disabled=1;
  document.all.guardar_transporte.style.visibility="visible";

}//de function agregar_trans()

/*************************************************************************
Control del boton guardar_transporte
@no_mostrar_confirm solo se usa para especificar que no muestre el confirm
                de 'Ha ingresado un precio negativo en el Conexo agregado'
                Si no se necesita, no hay que pasarlo (asi muestra dicho confirm)
**************************************************************************/
function control_trans(no_mostrar_confirm)
{ var precio_u,porcentaje,precio_limite;
  //controlamos que el precio sea distinto de 0 y que lo hayan completado
  precio_u=parseFloat(eval("document.all.unitario_"+pos_trans+".value"));
  if(precio_u=="" || precio_u==0)
  {alert('El precio ingresado no es válido');
   return false;
  }
  //si el precio es mayor que 0 controlamos que la cantidad ingresada sea menor que el 10%
  //de la compra o a 30 (el precio limite es el mayor de los dos)
  //Pero si el precio es menor que 0 no hacemos este control
  else if(precio_u>0)
  {

   //calculamos el 10% de la compra
   porcentaje=0.10*(parseFloat(document.all.total.value)-precio_u);
   if(porcentaje<=30)
    precio_limite=30;
   else
    precio_limite=porcentaje;

   //si el precio del conexo es mayor que el precio limite, damos alerta
   if(precio_u>precio_limite)
   {alert('El precio ingresado es mayor que el permitido ('+formato_BD(precio_limite)+').');
    return false;
   }
  }//de else if(precio_u>0)
  else//el precio es negativo
  {
   if(typeof(no_mostrar_confirm)=="undefined")
   {if(confirm("Ha ingresado un precio negativo en el Conexo agregado.\n¿Está seguro que desea continuar?"))
  	 return true;
  	else
  	 return false;
   }
  }//de else//el precio es negativo

  return true;
}//de function control_trans(no_mostrar_confirm)

/*******************************************************************
Devuelve true si el nombre pasado es un stock, y false en otro caso
********************************************************************/
function es_stock_js(nbre_prov)
{
 if(nbre_prov.substring(0,5)=="Stock")
  return 1;
 else
  return 0;
}//de function es_stock_js(nbre_prov)

function nuevo_item(links_stock)
{var pagina_prod;
 var nbre_prov;
 var stock_page;
 if (wproductos==0 || wproductos.closed)
 {   nbre_prov=document.all.select_proveedor[document.all.select_proveedor.selectedIndex].text;
    /*si el nombre del proveedor empieza con la palabra 'Stock' entonces
      los productos a seleccionar deben ser solo los que esten en ese stock seleccionado*/
    if(es_stock_js(nbre_prov))
    {    switch(nbre_prov)
         {
          case "Stock San Luis": pagina_prod=links_stock['san luis'];
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock Buenos Aires": pagina_prod=links_stock['buenos aires'];
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock New Tree": pagina_prod=links_stock['new tree'];
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock ANECTIS": pagina_prod=links_stock['anectis'];
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock SICSA": pagina_prod=links_stock['sicsa'];
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock Serv. Tec. Bs. As.": pagina_prod=links_stock['st_ba'];
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
         }//de switch(nbre_prov)
    }//de if(es_stock_js(nbre_prov))
    //En otro caso funciona como es usual, trayendo todos los productos cargados
    else
    {
        pagina_prod=links_stock["no_stock"];
    	wproductos=window.open(pagina_prod+'&id_proveedor='
	    +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	    ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
    }
 }//de if (wproductos==0 || wproductos.closed)
 else
  if (!wproductos.closed)
   wproductos.focus();
}//de function nuevo_item()

/*************************************************/
function borrar_items()
{
 var i=0;
 while(typeof(document.all.chk)!='undefined' && typeof(document.all.chk.length)!='undefined' && i < document.all.chk.length)
 {
   //Para borrar una fila
  if (typeof(document.all.chk[i])!='undefined' && document.all.chk[i].checked)
   document.all.productos.deleteRow(i+1);
  else
  	i++;
 }

 if(typeof(document.all.chk)!='undefined' && document.all.chk.checked)
 {
   document.all.productos.deleteRow(1);
   document.all.boton_eliminar.disabled=1;
 }
 else if (typeof(document.all.chk)=='undefined')
   document.all.boton_eliminar.disabled=1;

 total();
}//de function borrar_items()

/**********************************************************/
//funciones para busqueda abreviada utilizando teclas en la lista que muestra los clientes.
var digitos=10; //cantidad de digitos buscados
var puntero=0;
var buffer=new Array(digitos); //declaración del array Buffer
var cadena="";

function buscar_op_submit(obj)
{
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos)
   {
       cadena="";
       puntero=0;
   }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13)
   {
       borrar_buffer();
	 	 document.all.refresh.value=1;
		 form1.action='ord_pago.php';
		 form1.submit();


   }//de if (event.keyCode == 13)
   //sino busco la cadena tipeada dentro del combo...
   else
   {
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       //en el indice cero la opcion no es valida
       for (var opcombo=1;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }//del else de if (event.keyCode == 13)
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}//de function buscar_op_submit(obj)

function onchange_proveedor(object)
{
 document.all.refresh.value=1;
 if(es_stock_js(object.options[object.options.selectedIndex].text))
  document.all.borrar_filas_stock.value=1;
 else
  document.all.borrar_filas_stock.value=0;
 form1.action='ord_pago.php';
 form1.submit();
}//de function onchange_proveedor(object)

function montos_confirm()
{
 if(newConfirm("Ordenes de Compra","¿Desea especificar los montos de los pagos?",1,1,0))
 {document.all.destino_para_autorizar.value="ord_compra_pagar.php";
 }
 else
 {document.all.destino_para_autorizar.value="ord_pago_listar.php";
 }
}//de function montos_confirm()

function control_pagos()
{
 var total_monto;
 var total_pagos;
 var total_nc;
 var aux;
 var confirmacion;
 total_monto=parseFloat(document.all.total.value);
 total_pagos=parseFloat(document.all.montos_pagos.value);
 total_nc=parseFloat(document.all.montos_nc.value);

 aux=(total_monto-total_nc)-total_pagos;
 //alert('Total a pagar '+total_monto+' Total NC '+total_nc+' Total Pagos '+total_pagos+' Diferencia '+aux);
 //dependiendo del caso setea un valor
 if (total_pagos==0)
 {
  //aca no pregunta nada y hace la division
  document.all.destino_para_autorizar.value="ord_pago_listar.php";
  document.all.accion.value="dividir";
  return true;
 }//de if (total_pagos==0)
 else
 {
  if (((aux<=0.10)&&(aux>=0)) || ((aux>=-0.10)&&(aux<=0)))
  {
   document.all.destino_para_autorizar.value="ord_pago_listar.php";
   document.all.accion.value="nada";
   return true;
  }
  else
  {
   //si es distinto pregunta
   //si dice que si que siga, seguro que hay que mandar mail
   //si dice que no se tiene que quedar en la pagina
   confirmacion=confirm('Advertencia - Los valores ingresados no se corresponden con el total, Desea Seguir?');
   if (confirmacion==1)
   {
    document.all.destino_para_autorizar.value="ord_pago_listar.php";
    document.all.accion.value="nada";
    return true;
   } //fin del then del tercer if
   else
   {
    document.all.destino_para_autorizar.value="ord_pago_listar.php";
    document.all.accion.value="nada";
    return false;
   }//del else
  } //del else que controla el rango de error con los montos
 }//fin del else de if (total_pagos==0)

 return false;
}//de function control_pagos()

function cuerpo()
{
 var retorno;
 retorno=control_pagos();

 if (retorno==1)
 {
  var valor;
  valor=prompt("Ingrese el texto a enviar en el mail","");
  //montos_confirm();
  if (!valor)
   return false;
  document.all.contenido.value=valor;
  return true;
 }
 else
  return false;

}//de function cuerpo()


//--------------------------------------------------------
//FUNCION PARA CHECKEAR LOS CAMPOS
var msg;
function chk_campos()
{
 var ret_value=0;
 var long_prod=0;  //cantidad de productos agregados

 if (typeof(document.all.chk) !='undefined')
 {
	if (typeof(document.all.chk.length) !='undefined')
	  long_prod=document.all.chk.length;
	else long_prod=1;
 }

 if(check_fecha_legal()==0)
 {
  msg="La Fecha de Entrega de la Orden de Compra es MENOR a la Fecha de Creación de la misma.\n";
  ret_value++;
  return ret_value;
 }

 msg="---------------------------------------------------------------------\t\n";
 msg+="Falta Completar:\n\n";


 document.all.valor_dolar.value=document.all.valor_dolar.value.replace(',','.');

 if (document.all.fecha_entrega.value=='')
 {
  msg+="\tFecha de entrega\n";
  ret_value++;
 }
 if (document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value==-1)
 {
  msg+="\tNombre del Proveedor\n";
  ret_value++;
 }
 if (document.all.select_contacto[document.all.select_contacto.selectedIndex].value==-1)
 {
  msg+="\tNombre del Contacto\n";
  ret_value++;
 }


 if (document.all.select_pago[document.all.select_pago.selectedIndex].value==-1)
 {//if(document.all.es_stock.value==0)
  if(document.all.select_pago.disabled==0)
  {msg+="\tForma de Pago\n";
   ret_value++;
  }
 }
 if (document.all.select_moneda[document.all.select_moneda.selectedIndex].value==-1)
 {msg+="\tTipo de Moneda\n";
  ret_value++;
 }

 if (typeof(document.all.proveedor_reclamo)!="undefined" && document.all.proveedor_reclamo[document.all.proveedor_reclamo.selectedIndex].value==-1)
 {
  msg+="\tProveedor de productos que generan el RMA\n";
  ret_value++;
 }
 if(document.all.select_moneda[document.all.select_moneda.selectedIndex].text=='Dólares' && isNaN(document.all.valor_dolar.value))
 {
  msg+="\tEl valor del dolar debe ser un número valido\n";
  ret_value++;
 }

 if(document.all.select_moneda[document.all.select_moneda.selectedIndex].text=='Dólares' && document.all.valor_dolar.value=="")
 {
  msg+="\tEl valor del dolar\n";
  ret_value++;
 }

 var cant_veces=0;
 var i=0;
 while (cant_veces < long_prod)
 {
  c=eval("document.all.cant_"+i);
  p=eval("document.all.unitario_"+i);
  d=eval("document.all.desc_orig_"+i);

  var fila=cant_veces+1
  if (typeof(c)!='undefined' && typeof(p)!='undefined')
  {
   if (c.value=="" || isNaN(c.value))
   {
    msg+="\tIngresar un número valido para el campo Cantidad en la fila " + fila +" \n";
    ret_value++;
   }
   if (p.value=="" || isNaN(p.value))
   {
    msg+="\tIngresar un valor valido para el campo Precio Unitario en la fila " + fila +" \n";
    ret_value++;
   }
   if(d.value.indexOf('"')!=-1)
    {   msg+="\tEvite ingresar comillas dobles para la desc del producto en la " + fila +" \n";
        ret_value++;
    }

   cant_veces++;   //cantidad de checkbox
  }//de if (typeof(c)!='undefined' && typeof(p)!='undefined')
  i++;  //nombre que corresponde al nombre de los campos cant_ y unitario_
 }//de while (cant_veces < long_prod)

 if (ret_value < 27)
	msg+="-------------------------------------------------------------------\t";
 else
  msg+="----------------------------------------------------------------------\t";

 return ret_value;
}//de function chk_campos()

//muestra los botones para eliminar fila si la OC esta en estado 'a', 'e' ,'d' o 'g'
//tambien habilita el campo cantidad y el precio unitario para que lo cambien
//(y el respectivoboton de guardar esos cambios)
function habilitar_cambios_especiales()
{var i,boton_del,cant,precio,nbre_prov,es_stock;

 nbre_prov=document.all.select_proveedor[document.all.select_proveedor.selectedIndex].text;
 if(es_stock_js(nbre_prov))
  es_stock=1;
 else
  es_stock=0;

 for(i=0;i<document.all.items.value;i++)
 {
  boton_del=eval("document.all.borrar_fila_"+i);
  cant=eval("document.all.cant_"+i);
  precio=eval("document.all.unitario_"+i);

  if(document.all.check_habilitar_cambios.checked==0)
  {boton_del.style.visibility='hidden';
   if(!es_stock)
   {cant.disabled=1;
    precio.disabled=1;
   }
  }
  else
  {boton_del.style.visibility='visible';
   if(!es_stock)
   {cant.disabled=0;
    precio.disabled=0;
   }
  }
 }//de for(i=0;i<document.all.items.value;i++)
 if(!es_stock)
 {if(document.all.check_habilitar_cambios.checked==0)
   document.all.guardar_cambios_fila.style.visibility='hidden';
  else
   document.all.guardar_cambios_fila.style.visibility='visible';
 }

}//de function habilitar_cambios_especiales()

IE4 = document.all;

function newConfirm(title,mess,icon,defbut,mods)
{
   if (IE4) {
      icon = (icon==0) ? 0 : 2;
      defbut = (defbut==0) ? 0 : 1;
      retVal = makeMsgBox(title,mess,icon,4,defbut,mods);
      retVal = (retVal==6);
   }
   else {
      retVal = confirm(mess);
   }
   return retVal;
}//de function newConfirm(title,mess,icon,defbut,mods)
