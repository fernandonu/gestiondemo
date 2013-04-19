// funciones de modificacion de los tag select, tambien para su escritura

var o = null;
var isNN = (navigator.appName.indexOf("Netscape")!=-1);

function activar(nombre,valor) //activa valor del select objeto
{
var objetoc=eval('window.document.form.'+nombre); //obtengo objeto select a cambiar
for (var i=0;i<objetoc.length;i++)
{
if (objetoc.options[i].text==valor)
objetoc.options[i].selected=true;
}
}

function activar_prov()
 {var objeto=eval('window.document.form.proveedor_mother');
  activar('proveedor_video',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_sonido',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_red',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_modem',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_micro',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_mem',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_graba',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_dvd',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_cd',objeto.options[objeto.selectedIndex].text);
  activar('proveedor_hdd',objeto.options[objeto.selectedIndex].text);
 }

function activar_gar()
{var objeto=eval('window.document.form.garantia_mother'); 
 activar('garantia_video',objeto.options[objeto.selectedIndex].text);
 activar('garantia_sonido',objeto.options[objeto.selectedIndex].text);
 activar('garantia_red',objeto.options[objeto.selectedIndex].text);
 activar('garantia_modem',objeto.options[objeto.selectedIndex].text);
 activar('garantia_micro',objeto.options[objeto.selectedIndex].text);
 activar('garantia_mem',objeto.options[objeto.selectedIndex].text);
 activar('garantia_graba',objeto.options[objeto.selectedIndex].text);
 activar('garantia_dvd',objeto.options[objeto.selectedIndex].text);
 activar('garantia_cd',objeto.options[objeto.selectedIndex].text);
 activar('garantia_hdd',objeto.options[objeto.selectedIndex].text);
}



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
//alert("hi");
//editable =this ;
alert(this.options[this.selectedIndex].text);
//document.form.elements[editable].options[document.form.elements[editable].selectedIndex].text);
document.form.txtoption.value = this.options[this.selectedIndex].text;
//document.form.this.options[document.form.this.selectedIndex].text;
}