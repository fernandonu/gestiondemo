/*
$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2006/01/04 08:30:21 $
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


/**************************************************************************************************
 Para la parte de recepcion y entregas:
 Funcion que autocompleta los campos de codigos de barra, para evitar que el usuario tenga que
 cargar muchos codigos de barra. Se asume para que esto funcione bien, que los codigos de
 barra a ingresar seran todos consecutivos y solo numerales.
 @primer_codigo     El primer codigo de barras del rango que se ingresara
 @nombre_text       El nombre del campo desde donde se ingresaran los codigos. Este es la parte escrita
 					del nombre. Si por ejemplo, el campo se llama codigos_0, en este parametro
 					se pasara solo: codigos_
 @indice_text		El subindice que compone el nombre del campo. Este se usa para indicar desde
 					cual campo se comenzaran a ingresar los codigos de barra consecutivos.
 					Por ejemplo, si se pasa 3, se comenzara a agregar los codigos de barra
 					desde el campo codigos_3 (si en el parametro nombre_text venia: codigos_)
 @id_log_recibido   El id del log de recibido para el cual se van a autocompletar los codigos
                    de barra
***************************************************************************************************/
function autocompletar_codigos_barra(primer_codigo,nombre_text,indice_text,id_log_recibido)
{

	var aux_campo,arr_codigo,i,aux_string;
	var k,aux_cant;
	var cant_vacios=eval("document.all.cant_vacios_"+id_log_recibido);
	var cantidad_campos=prompt('Ingrese la cantidad de codigos de barra a completar\n(por favor ingrese solo números)',cant_vacios.value-indice_text);
	var codigo_insertar=parseFloat(primer_codigo) + 1;

	if(cantidad_campos>cant_vacios.value-1)
	{
	 alert("La cantidad de codigos de barra a ingresar es mayor a la disponible");
	 return false;
	}

	for(i=0;i<cantidad_campos;i++,indice_text++)
	{
		aux_campo=eval('document.all.'+nombre_text+id_log_recibido+"_"+indice_text);
		aux_string=String(codigo_insertar);

		//completamos con ceros a la izquierda el numero, para llegar a la longitud del codigo de barra pasado por parametro
		//(en general la longitud es 9)
		aux_cant=primer_codigo.length-aux_string.length;
		for(k=0;k<aux_cant;k++)
		{
		 aux_string="0"+aux_string;
		}
		aux_campo.value=aux_string;
		//seteamos el proximo codigo a insertar
		codigo_insertar=parseFloat(codigo_insertar) + 1;

	}//de for(indice_text;indice_text<cantidad_campos;indice_text++)


}//de function autocompletar_codigos_barra(primer_numero,ultimo_numero,nombre_text,indice,cantidad_campos)
