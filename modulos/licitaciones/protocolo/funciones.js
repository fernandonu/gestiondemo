/*
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2003/07/24 15:17:59 $
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


/// funciones para verificacion de datamation-iso ///////

function get_value_radio()
{for(i=1;i<=document.all.check.length;i++)
 {if(document.all.check[i-1].checked)
  return i;
 }
}

///// Funciones de verificacion de combos  ////////

function verificar(protocolo)
{if ((protocolo==1) || (protocolo==4))
 {if (get_value_radio()=="2")
   if (document.getElementById("iso_tipo").options[document.getElementById("iso_tipo").selectedIndex].value==-1)
               {alert("Debe llenar todos los campos para guardar el protocolo");
                return false;
               }
  if ((document.getElementById("pc_micro_modelo").options[document.getElementById("pc_micro_modelo").selectedIndex].value==-1) ||
      (document.getElementById("pc_micro_velocidad").options[document.getElementById("pc_micro_velocidad").selectedIndex].value==-1) ||
      (document.getElementById("pc_memoria_tipo").options[document.getElementById("pc_memoria_tipo").selectedIndex].value==-1) ||
      (document.getElementById("pc_memoria_tamaño").options[document.getElementById("pc_memoria_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("pc_disco_tipo").options[document.getElementById("pc_disco_tipo").selectedIndex].value==-1) ||
      (document.getElementById("pc_disco_tamaño").options[document.getElementById("pc_disco_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("pc_disco_bus").options[document.getElementById("pc_disco_bus").selectedIndex].value==-1) ||
      (document.getElementById("pc_disco_rpm").options[document.getElementById("pc_disco_rpm").selectedIndex].value==-1) ||
      (document.getElementById("pc_video_tamaño").options[document.getElementById("pc_video_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("pc_video_tipo").options[document.getElementById("pc_video_tipo").selectedIndex].value==-1) ||
      (document.getElementById("pc_video_tamaño").options[document.getElementById("pc_monitor_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("pc_multimedia_sonido").options[document.getElementById("pc_multimedia_sonido").selectedIndex].value==-1) ||
      (document.getElementById("pc_multimedia_parlantes").options[document.getElementById("pc_multimedia_parlantes").selectedIndex].value==-1) ||
      (document.getElementById("pc_multimedia_microfono").options[document.getElementById("pc_multimedia_microfono").selectedIndex].value==-1) ||
      (document.getElementById("pc_multimedia_cd").options[document.getElementById("pc_multimedia_cd").selectedIndex].value==-1) ||
      (document.getElementById("pc_multimedia_dvd").options[document.getElementById("pc_multimedia_dvd").selectedIndex].value==-1) ||
      (document.getElementById("pc_multimedia_cdwr").options[document.getElementById("pc_multimedia_cdwr").selectedIndex].value==-1) ||
      (document.getElementById("pc_teclado").options[document.getElementById("pc_teclado").selectedIndex].value==-1) ||
      (document.getElementById("pc_lan_tipo").options[document.getElementById("pc_lan_tipo").selectedIndex].value==-1) ||
      (document.getElementById("pc_software_so").options[document.getElementById("pc_software_so").selectedIndex].value==-1) ||
      (document.getElementById("pc_software_oficina").options[document.getElementById("pc_software_oficina").selectedIndex].value==-1) ||
      (document.getElementById("pc_gabinete_tipo").options[document.getElementById("pc_gabinete_tipo").selectedIndex].value==-1) ||
      (document.getElementById("pc_garantia").options[document.getElementById("pc_garantia").selectedIndex].value==-1))
              {alert("Debe llenar todos los campos para guardar el protocolo");
               return false;
              }
  }// fin if
if ((protocolo==2)|| (protocolo==5))
{if ((document.getElementById("servidor_gabinete_tipo").options[document.getElementById("servidor_gabinete_tipo").selectedIndex].value==-1) ||
      (document.getElementById("servidor_micro_cantidad").options[document.getElementById("servidor_micro_cantidad").selectedIndex].value==-1) ||
      (document.getElementById("servidor_micro_tipo").options[document.getElementById("servidor_micro_tipo").selectedIndex].value==-1) ||
      (document.getElementById("servidor_micro_cache").options[document.getElementById("servidor_micro_cache").selectedIndex].value==-1) ||
      (document.getElementById("servidor_memoria_tipo").options[document.getElementById("servidor_memoria_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("servidor_memoria_tamaño").options[document.getElementById("servidor_memoria_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("servidor_memoria_expansion").options[document.getElementById("servidor_memoria_expansion").selectedIndex].value==-1) ||
      (document.getElementById("servidor_video_tamaño").options[document.getElementById("servidor_video_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("servidor_expansion_pci").options[document.getElementById("servidor_expansion_pci").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_tipo").options[document.getElementById("servidor_storage_tipo").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_interface").options[document.getElementById("servidor_storage_interface").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_rpm").options[document.getElementById("servidor_storage_rpm").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_tamaño").options[document.getElementById("servidor_storage_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_cantidad").options[document.getElementById("servidor_storage_cantidad").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_raid").options[document.getElementById("servidor_storage_raid").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_backup_modelo").options[document.getElementById("servidor_storage_backup_modelo").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_backup_tipo").options[document.getElementById("servidor_storage_backup_tipo").selectedIndex].value==-1) ||
      (document.getElementById("servidor_storage_backup_insumos").options[document.getElementById("servidor_storage_backup_insumos").selectedIndex].value==-1) ||
      (document.getElementById("servidor_monitor_tamaño").options[document.getElementById("servidor_monitor_tamaño").selectedIndex].value==-1) ||
      (document.getElementById("servidor_monitor_rackeable").options[document.getElementById("servidor_monitor_rackeable").selectedIndex].value==-1) ||
      (document.getElementById("servidor_teclado").options[document.getElementById("servidor_teclado").selectedIndex].value==-1) ||
      (document.getElementById("servidor_swich_ports").options[document.getElementById("servidor_swich_ports").selectedIndex].value==-1) ||
      (document.getElementById("servidor_software_so").options[document.getElementById("servidor_software_so").selectedIndex].value==-1) ||
      (document.getElementById("servidor_garantia").options[document.getElementById("servidor_garantia").selectedIndex].value==-1))
              {alert("Debe llenar todos los campos para guardar el protocolo");
               return false;
              }
}// fin if
if ((protocolo==3)|| (protocolo==4) || (protocolo==5)) //verificacion de la impresora
 {if ((document.getElementById("impresora_tipo").options[document.getElementById("impresora_tipo").selectedIndex].value==-1) ||
      (document.getElementById("bandeja").options[document.getElementById("bandeja").selectedIndex].value==-1) ||
      (document.getElementById("impresora_conexion").options[document.getElementById("impresora_conexion").selectedIndex].value==-1) ||
      (document.getElementById("impresora_interface").options[document.getElementById("impresora_interface").selectedIndex].value==-1) ||
      (document.getElementById("impresora_duplex").options[document.getElementById("impresora_duplex").selectedIndex].value==-1) ||
      (document.getElementById("impresora_extras").options[document.getElementById("impresora_extras").selectedIndex].value==-1) ||
      (document.getElementById("impresora_ppm").options[document.getElementById("impresora_ppm").selectedIndex].value==-1) ||
      (document.getElementById("impresora_garantia").options[document.getElementById("impresora_garantia").selectedIndex].value==-1) ||
      (document.getElementById("impresora_ram").options[document.getElementById("impresora_ram").selectedIndex].value==-1))
              {alert("Debe llenar todos los campos para guardar el protocolo");
               return false;
              }
 }//fin if 
return true;
}// fin verificar

function verificar2()
{if ((document.form.licitacion.value=="") || (document.form.renglon.value=="") || (document.form.item.value==""))
 {alert("Debe llenar los campos para llenar el protocolo");
  return false;
 }
 return true;
}
/*****************************************************************************
CHKCHECKBOX por GACZ.
Funcion que checkea que TODOS los checkbox esten marcados en un formulario
id_pref: es el prefijo de los id de checkbox (numerados desde uno)
count: es la cantidad total de checkbox a verificar
retorno de la funcion: true si todo estuvo checkeado
					   falso si falto alguno 	 
*****************************************************************************/
function chkcheckbox(id_pref,count)
{
 var i;
 var faltan=false
 var expresion1;
 var expresion2;
 for (i=1; i<=count;i++)
 {
  expresion1="if (!document.all."+ id_pref + i + ".checked)";
  expresion2=" faltan=true;"
  eval(expresion1+expresion2);
  if (faltan)
   return false;
 }
  return true;
}
//---------------------------------------------------------------------------