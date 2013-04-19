/*
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2003/08/07 15:49:17 $
*/

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
