<?php
/*
$Author: fernando $
$Revision: 1.30 $
$Date: 2006/08/31 21:19:29 $
*/

require_once("../../config.php");
require_once("funciones.php");

$id_licitacion=$parametros["id_licitacion"] or $_POST["id_licitacion"];
$id_renglon=$parametros["id_renglon"];
//si id_renglon es nulo es que estoy haciendo un renglon nuevo


if($id_renglon && $_POST["primera_vez"]=="") $primera_vez=1;
                                        else $primera_vez=0;



//realizo el alta
if ($_POST["guardar"]){
                      require_once("licitaciones_renglones_sql.php");
                      }

$sql="select licitacion.*,entidad.*,tipo_entidad.nombre as tipo_entidad,distrito.nombre as nbre_dist
      from licitacion join entidad on licitacion.id_entidad = entidad.id_entidad
      and id_licitacion = $id_licitacion
      join tipo_entidad using(id_tipo_entidad)
      join distrito using(id_distrito)";

//esta variable la utilizo siempre para referirme a los datos de la licitacion
$resultado_licitacion=sql($sql) or fin_pagina();

//datos globales
$nbre_dist=$resultado_licitacion->fields['nbre_dist'];
echo $html_header;
?>
<script languaje="javascript">

function eliminar(valor)
{
var objeto;

/*
objeto=eval("window.document.all.tip"+valor);
alert('tip'+objeto);
objeto.value="";
  */
objeto=eval("window.document.all.descripcion"+valor);
objeto.value="";
objeto=eval("window.document.all.precio"+valor);
objeto.value="";
objeto=eval("window.document.all.cantidad"+valor);
objeto.value="";
objeto=eval("window.document.all.tipo"+valor);
objeto.value="";

/*alert(valor);
//tbl.getElementsByTagName("TR").length;
if (window.document.all.cant_ad.value==1)
  window.document.all.productos_ad.style.visibility='hidden';
 window.document.all.productos_ad.deleteRow(valor);
 window.document.all.cant_ad.value--;
 */
}

//ventana de productos
var wproductos=false;


//funcion que recupera los datos de la ventana hijo y los setea en el padre
function agregar(valor) {
var objeto;
    objeto2=eval("document.all.estado"+valor);
    objeto3=eval("document.all.producto"+valor);
    


if ((document.all.producto.options[document.all.producto.selectedIndex].text=='Computadora Matrix' || document.all.producto.options[document.all.producto.selectedIndex].text=='Computadora Enterprise'  )&& wproductos.document.forms[0].tipo_producto_elegido.value=='Mother Board')    
     alert('No se Puede agregar Mother Board Desde Adicionales cuando es un renglon tipo computadora');    
   else {  

               if (objeto2.value==0)
                {
              //objeto3.value=objeto4.value;
              objeto2.value=3; //debo eliminar un producto e insertar otro
             }
            if (objeto2.value==1)
              objeto2.value=3; //debo eliminar un producto e insertar otro
              
            if (objeto2.value==4) //no habia nada
             objeto2.value=2; //debo insertar un producto


            //esta variable contiene el id_producto

            objeto=eval("document.all.tipo"+valor);
            objeto.value=wproductos.document.forms[0].tipo_producto_elegido.value;
            objeto=eval("document.all.producto"+valor);
            objeto.value=wproductos.document.forms[0].id_producto_seleccionado.value;
            objeto=eval("document.all.descripcion"+valor);
            objeto.value=wproductos.document.forms[0].nombre_producto_elegido.value;
            objeto=eval("document.all.precio"+valor);
            objeto.value=wproductos.document.forms[0].precio_producto_elegido.value;
            objeto=eval("document.all.cantidad"+valor);
            objeto.value=1;
    }
window.focus();
wproductos.close();
}

function switch_func(valor,link)
{var objeto;
 var objeto2;
 var objeto3;
 var objeto4;
 objeto=eval("window.document.all.boton"+valor);
 objeto2=eval("window.document.all.estado"+valor);
 objeto3=eval("window.document.all.producto"+valor);
 
 //objeto4=eval("window.document.all.tipo"+valor);
 if (objeto.value=="agregar")
 {
  wproductos=window.open(link,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=40,top=100,width=700,height=400,resizable=1');
  objeto.value="eliminar";
 }
 else //elimino fila
 {
   if (objeto2.value==0)
   {
    //objeto3.value=objeto4.value;
    objeto2.value=1; //debo eliminar un producto
   }
   if (objeto2.value==2)
    objeto2.value=4;
   eliminar(valor);
   objeto.value="agregar";
 }
// alert(objeto2.value);
}



function verificar_precios()
{
if (typeof(document.all.select_etap)!='undefined' && document.all.select_etap.selectedIndex==0)
  {
  alert('Falta llenar Norma ETAP');
  return false;
 }


 if (window.document.all.renglon.value=="")
     {
     alert("Falta llenar el campo de renglon");
     return false;
     }
 if (window.document.all.titulo.value=="")
     {
     alert("Falta llenar el campo de titulo");
     return false;
     }
 if (window.document.all.cantidad_renglon.value=="")
     {
     alert("Falta llenar el campo cantidad");
     return false;
     }

 if ((window.document.all.ganancia.value=="") || (window.document.all.ganancia.value<=0) || (window.document.all.ganancia.value > 1) )
     {
     alert("Dato invalido en el campo ganancia");
     return false;
     }

//alert(document.all.producto.options[document.all.producto.selectedIndex].text);
switch(document.all.producto.options[document.all.producto.selectedIndex].text){

    case 'Impresora':
      if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value!=0) && (document.all.precio_impresora.value==""))
          {
          alert("Falta Precio en Impresora");
          return false;
          }
         document.all.precio_impresora.value=document.all.precio_impresora.value.replace(',','.');
         if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value==0) && (document.all.precio_impresora.value!=""))
            {
            alert("Falta elegir Impresora");
            return false;
            }
         if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value!=0) &&(document.all.cantidad_impresora.value==""))
            {
            alert ("Falta Cantidad en Impresora");
            return false;
            }
            //parte de cables
         if ((document.all.select_cables.options[document.all.select_cables.selectedIndex].value!=0) && (document.all.precio_cables.value==""))
            {
            alert("Falta Precio en Cables");
            return false;
            }
           document.all.precio_cables.value=document.all.precio_cables.value.replace(',','.');
         if ((document.all.select_cables.options[document.all.select_cables.selectedIndex].value==0) && (document.all.precio_cables.value!=""))
            {
            alert("Falta elegir Cables");
            return false;
            }
         if ((document.all.select_cables.options[document.all.select_cables.selectedIndex].value!=0) &&(document.all.cantidad_cables.value==""))
            {
            alert ("Falta Cantidad en Cables");
            return false;
            }

 return true;
 break;
 case 'Otro':
 case 'Software':
                  break;
 default:
 
     if (document.all.select_conexo.options[document.all.select_conexo.selectedIndex].value==0)
     {
     alert("Debe elegir Conexo");
     return false;
     }
     document.all.precio_conexo.value=document.all.precio_conexo.value.replace(',','.');     
     if ((parseFloat(document.all.precio_conexo.value)<=0) || (document.all.precio_conexo.value==''))
     {
     alert("Precio Conexo Debe Ser Mayor que 0");
     return false;
     }
     
    /*
    if (document.all.select_conexo.options[document.all.select_conexo.selectedIndex].value==0)
     {
     alert("Debe elegir Conexo");
     return false;
     }
     document.all.precio_conexo.value=document.all.precio_conexo.value.replace(',','.');
     if (parseFloat(document.all.precio_conexo.value)<0  or document.all.precio_conexo.value==''))
     {
     alert("Precio Conexo Debe Ser Mayor que 0");
     return false;
     }
     
      */
     
     
 
    if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value!=0) && (document.all.precio_sistemaoperativo.value==""))
     {
     alert("Falta Precio en Sistema Operativo");
     return false;
     }
     document.all.precio_sistemaoperativo.value=document.all.precio_sistemaoperativo.value.replace(',','.');
     if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value==0) && (document.all.precio_sistemaoperativo.value!=""))
     {
     alert("Falta elegir Sistema Operativo");
     return false;
     }
 if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value!=0) &&(document.all.cantidad_sistemaoperativo.value==""))
    {
    alert ("Falta Cantidad en Sistema Operativo");
    return false;
    }
 if (document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value==0)
   {
   alert("Debe elegir Sistema Operativo");
   return false;
   }
   
 //etiquetas
    if ((document.all.select_etiquetas_so.options[document.all.select_etiquetas_so.selectedIndex].value!=0) && (document.all.precio_etiquetas_so.value==""))
     {
     alert("Falta Precio en Etiquetas Sistema Operativo");
     return false;
     }
     document.all.precio_etiquetas_so.value=document.all.precio_etiquetas_so.value.replace(',','.');
     if ((document.all.select_etiquetas_so.options[document.all.select_etiquetas_so.selectedIndex].value==0) && (document.all.precio_etiquetas_so.value!=""))
     {
     alert("Falta elegir Etiquetas Sistema Operativo");
     return false;
     }
 if ((document.all.select_etiquetas_so.options[document.all.select_etiquetas_so.selectedIndex].value!=0) &&(document.all.cantidad_etiquetas_so.value==""))
    {
    alert ("Falta Cantidad en Etiquetas Sistema Operativo");
    return false;
    }
 if (document.all.select_etiquetas_so.options[document.all.select_etiquetas_so.selectedIndex].value==0)
   {
   alert("Debe elegir Etiquetas Sistema Operativo");
   return false;
   }

 //etiquetas  

 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value!=0) && (document.all.precio_kit.value==""))
   {
   alert("Falta Precio en Kit");
   return false;
   }
 document.all.precio_kit.value=document.all.precio_kit.value.replace(',','.');
 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value==0) && (document.all.precio_kit.value!=""))
 {
   alert("Falta elegir Kit");
   return false;
 }
 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value!=0) &&(document.all.cantidad_kit.value=="")) {
  alert ("Falta Cantidad en Kit");
  return false;
  }

 
  if ((document.all.select_floppy.options[document.all.select_floppy.selectedIndex].value!=0) && (document.all.precio_floppy.value==""))
   {
   alert("Falta Precio en Floppy");
   return false;
   }
 document.all.precio_floppy.value=document.all.precio_floppy.value.replace(',','.');
 if ((document.all.select_floppy.options[document.all.select_floppy.selectedIndex].value==0) && (document.all.precio_floppy.value!=""))
 {
   alert("Falta elegir Floppy");
   return false;
 }
 if ((document.all.select_floppy.options[document.all.select_floppy.selectedIndex].value!=0) &&(document.all.cantidad_floppy.value=="")) {
  alert ("Falta Cantidad en Floppy");
  return false;
  }

 

 if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value!=0) && (document.all.precio_madre.value==""))
 {
   alert("Falta Precio en Placa Madre");
   return false;
 }
 document.all.precio_madre.value=document.all.precio_madre.value.replace(',','.');
 if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value==0) && (document.all.precio_madre.value!=""))
  {
  alert("Falta elegir Placa Madre");
  return false;
  }
  if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value!=0) &&(document.all.cantidad_madre.value=="")) {
  alert ("Falta Cantidad en Madre");
  return false;
  }

 if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value!=0) && (document.all.precio_micro.value==""))
  {
  alert("Falta Precio en Micro");
  return false;
  }
 document.all.precio_micro.value=document.all.precio_micro.value.replace(',','.');
 if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value==0) && (document.all.precio_micro.value!=""))
  {
  alert("Falta elegir Micro");
  return false;
  }
  if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value!=0) &&(document.all.cantidad_micro.value==""))
 {
  alert ("Falta Cantidad en Micro");
  return false;
 }

 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value!=0) && (document.all.precio_memoria.value==""))
 {
  alert("Falta Precio en Memoria");
  return false;
 }
 document.all.precio_memoria.value=document.all.precio_memoria.value.replace(',','.');
 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value==0) && (document.all.precio_memoria.value!=""))
 {
  alert("Falta elegir Memoria");
  return false;
 }
 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value!=0) &&(document.all.cantidad_memoria.value==""))
  {
  alert ("Falta Cantidad en Memoria");
  return false;
  }

 if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value!=0) && (document.all.precio_disco.value==""))
   {
   alert("Falta Precio en Disco");
   return false;
   }
 document.all.precio_disco.value=document.all.precio_disco.value.replace(',','.');
 if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value==0) && (document.all.precio_disco.value!=""))
  {
  alert("Falta elegir Disco");
  return false;
  }
  if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value!=0) &&(document.all.cantidad_disco.value==""))
  {
  alert ("Falta Cantidad en Disco");
  return false;
  }

 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value!=0) && (document.all.precio_cd.value==""))
 {
  alert("Falta Precio en CD-Rom");
  return false;
 }
 document.all.precio_cd.value=document.all.precio_cd.value.replace(',','.');
 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value==0) && (document.all.precio_cd.value!=""))
 {
 alert("Falta elegir CD-Rom");
 return false;
 }
 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value!=0) &&(document.all.cantidad_cd.value==""))
 {
 alert ("Falta Cantidad en Cdrom");
 return false;
 }

 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value!=0) && (document.all.precio_monitor.value==""))
 {
  alert("Falta Precio en Monitor");
  return false;
 }
 if (document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value==0)
  {
  alert("Debe elegir Monitor");
  return false;
  }
 document.all.precio_monitor.value=document.all.precio_monitor.value.replace(',','.');
 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value==0) && (document.all.precio_monitor.value!=""))
 {
  alert("Falta elegir Monitor");
  return false;
 }
 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value!=0) &&(document.all.cantidad_monitor.value==""))
  {
  alert ("Falta Cantidad en Monitor");
  return false;
  }



 if ((document.all.select_usb.options[document.all.select_usb.selectedIndex].value!=0) && (document.all.precio_usb.value==""))
 {
  alert("Falta Precio en Usb");
  return false;
 }
 
 document.all.precio_usb.value=document.all.precio_usb.value.replace(',','.');
 if ((document.all.select_usb.options[document.all.select_usb.selectedIndex].value==0) && (document.all.precio_usb.value!=""))
 {
  alert("Falta elegir Usb");
  return false;
 }
 if ((document.all.select_usb.options[document.all.select_usb.selectedIndex].value!=0) &&(document.all.cantidad_usb.value==""))
  {
  alert ("Falta Cantidad en Usb");
  return false;
  }

 
 if ((document.all.select_video.options[document.all.select_video.selectedIndex].value!=0) && (document.all.precio_video.value==""))
   {
   alert("Falta Precio en Video");
   return false;
   }
 document.all.precio_video.value=document.all.precio_video.value.replace(',','.');
 if ((document.all.select_video.options[document.all.select_video.selectedIndex].value==0) && (document.all.precio_video.value!=""))
  {
  alert("Falta elegir Placa de Video");
  return false;
  }
  if ((document.all.select_video.options[document.all.select_video.selectedIndex].value!=0) &&(document.all.cantidad_video.value==""))
  {
  alert ("Falta Cantidad en Video");
  return false;
  }

 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value!=0) && (document.all.precio_grabadora.value==""))
  {
  alert("Falta Precio en grabadora");
  return false;
  }
 document.all.precio_grabadora.value=document.all.precio_grabadora.value.replace(',','.');
 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value==0) && (document.all.precio_grabadora.value!=""))
 {
 alert("Falta elegir Placa de Video");
 return false;
 }
 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value!=0) &&(document.all.cantidad_grabadora.value=="")) {
  alert ("Falta Cantidad en Grabadora");
  return false;
 }

 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value!=0) && (document.all.precio_dvd.value==""))
  {
  alert("Falta Precio en DVD");
  return false;
  }
 document.all.precio_dvd.value=document.all.precio_dvd.value.replace(',','.');
 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value==0) && (document.all.precio_dvd.value!=""))
 {
 alert("Falta elegir DVD");
 return false;
 }
 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value!=0) &&(document.all.cantidad_dvd.value==""))
 {
 alert ("Falta Cantidad en DVD");
 return false;
 }

 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value!=0) && (document.all.precio_red.value==""))
 {
  alert("Falta Precio en Placa de Red");
  return false;
 }
 document.all.precio_red.value=document.all.precio_red.value.replace(',','.');
 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value==0) && (document.all.precio_red.value!=""))
 {
  alert("Falta elegir Placa de Red");
  return false;
 }
 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value!=0) &&(document.all.cantidad_red.value==""))
  {
  alert ("Falta Cantidad en Placa de Red");
  return false;
  }


 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value!=0) && (document.all.precio_modem.value==""))
 {
  alert("Falta Precio en Modem");
  return false;
 }
 document.all.precio_modem.value=document.all.precio_modem.value.replace(',','.');
 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value==0) && (document.all.precio_modem.value!=""))
 {
  alert("Falta elegir Modem");
  return false;
 }
 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value!=0) &&(document.all.cantidad_modem.value==""))
 {
  alert ("Falta Cantidad en Modem");
  return false;
  }

 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value!=0) && (document.all.precio_zip.value==""))
 {
  alert("Falta Precio en Zip");
  return false;
 }
 document.all.precio_zip.value=document.all.precio_zip.value.replace(',','.');
 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value==0) && (document.all.precio_zip.value!=""))
 {
  alert("Falta elegir ZIP");
  return false;
 }
 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value!=0) &&(document.all.cantidad_zip.value==""))
  {
  alert ("Falta Cantidad en Zip");
  return false;
  }
  
 break;
 }//del switch
<?
for($i=1;$i<=$cantidad_adicionales;$i++){
?>

  if (window.document.all.tipo<?=$i?>.value!="") //entonces hay cargado un tipo

 {

  if (window.document.all.cantidad<?=$i?>.value=="")
  {
   alert("Falta llenar la cantidad de "+window.document.all.descripcion<?=$i?>.value);
   return false;
  }
  if (window.document.all.precio<?=$i?>.value=="")
  {
    alert("Falta llenar el precio de "+window.document.all.descripcion<?=$i?>.value);
   return false;
  }

 }//del primer if

 <?
  } //del for
 ?>
 return true;
} //del verificar_precio_computadora


function incluir(objeto,texto,value,id){
objeto.length++;
objeto.options[objeto.length-1].text=texto;
objeto.options[objeto.length-1].value=value;
objeto.options[objeto.length-1].id=id;
}

function limpiar_select(){

window.document.all.select_madre.options.length=0;
window.document.all.precio_madre.value="";
incluir(window.document.all.select_madre,"Seleccione Placa Madre",0,0);
}
<?php
//genero el arreglo de java script que voy a usar de indexes

$sql="select p.id_producto,p.desc_gral,p.precio_licitacion,codigo as tipo
             from productos p 
             join tipos_prod using(id_tipo_prod)
             where tipos_prod.codigo='placa madre' 
             order by desc_gral";
$placas_madres=sql($sql) or fin_pagina();
?>
var pm=new Array(<?=$placas_madres->recordcount()?>);

<?
$i=0;
while (!$placas_madres->EOF){
 ($placas_madres->fields['precio_licitacion'])?$precio_licitacion=$placas_madres->fields['precio_licitacion']:$precio_licitacion=0;                     
 ?>
 pm[<?=$i?>]=new Array(7);
 pm[<?=$i?>][0]=<?=$placas_madres->fields["id_producto"]?>;	
 pm[<?=$i?>][1]="<?=$placas_madres->fields["tipo"]?>";	
 pm[<?=$i?>][2]="<?=$placas_madres->fields["marca"]?>";	
 pm[<?=$i?>][3]="<?=$placas_madres->fields["modelo"]?>";	
 pm[<?=$i?>][4]=<?=$precio_licitacion?>;	
 pm[<?=$i?>][5]="<?=$placas_madres->fields["desc_gral"]?>";	
 pm[<?=$i?>][6]=<?=$placas_madres->recordcount()?>;
 <?	
 $placas_madres->movenext();
 $i++;
}//del for


$sql="select p.id_producto, p.desc_gral,p.precio_licitacion
             from productos p join tipos_prod using (id_tipo_prod) 
             where codigo='micro' order by desc_gral";

$resultados=sql($sql,"Error: Consulta de micros en compatibilidades") or fin_pagina();

while (!$resultados->EOF){
          $id_micro=$resultados->fields["id_producto"] ;  
          $sql="select p.id_producto,p.desc_gral,p.precio_licitacion,codigo as tipo
                       from compatibilidades 
                       join productos p on p.id_producto=motherboard 
                       join tipos_prod using(id_tipo_prod)
                       where componente=$id_micro
                       order by desc_gral";
          $resultado_comp=sql($sql,"Error: Consulta de compatibilidades") or fin_pagina();
        ?>
        var m_<?=$resultados->fields["id_producto"]; ?>=new Array(<?=$resultado_comp->RecordCount(); ?>);
        <?php
        $i=0;
        while (!$resultado_comp->EOF){
                 ($resultado_comp->fields['precio_licitacion'])?$precio_licitacion=$resultado_comp->fields['precio_licitacion']:$precio_licitacion=0;                     
                 ?>
                  m_<?=$resultados->fields["id_producto"]; ?>[<?=$i;?>]=<?=$resultado_comp->fields['id_producto'];?>;                 
                 <?
                 $i++;
                 $resultado_comp->MoveNext();
                 }
        $resultados->MoveNext();
 }
 

?>

//funcion que me cambia las placas madres con los micros 
//dependiendo de su compatibilidad
function cambiar_comp(valor){
  var arreglo;
  var i=0,y=0;
  switch (valor){
  <?
  $resultados->Move(0);
  while (!$resultados->EOF){
  ?>
  case '<?=$resultados->fields['id_producto']; ?>':arreglo=m_<?=$resultados->fields["id_producto"]; ?>;break;
  <?
  $resultados->MoveNext();
  }
  ?>
  }// fin switch
 
 
  //cargo el combo con las placas madres 
  if (typeof(arreglo)!="undefined"){
		while (i<arreglo.length){
		    y = 0;
			while(y<pm.length){
	 		  if (arreglo[i] == pm[y][0])
		          incluir(window.document.all.select_madre,pm[y][5],pm[y][0],pm[y][4]);
		      y++;  
	         } 	  
	    i++;
	    }//fin de while
   }//fin if
}//fin funcion cambiar_comp


//si flag == 20 indica que cambio el valor seleccionado para el micro
function llamada_funciones(valor,flag){

if (flag==20){
     limpiar_select();
     }

cambiar_comp(valor);

} //fin de llamada_funciones

function change_producto(){
document.form1.cambio_producto.value=1;
document.form1.submit();
}

function ocultar(imagen,div){
if (imagen.src.indexOf("drop2.gif")!=-1) 
            {
	        imagen.src="../../imagenes/dropdown2.gif";
	        
            div.style.display='block';
	        } 
	        else 
	        {
	        imagen.src="../../imagenes/drop2.gif";
	        div.style.display='none';
	        }
}

</script>
<?
//setee el valor del titulo
$titulo=setear_titulo($_POST["producto"]);

//traigo los datos del renglon
if ($id_renglon && $primera_vez) {
         $sql="select renglon.*,renglon.tipo as tipo_renglon,etaps.id_etap,etaps.titulo as titulo_etap,etaps.texto as texto_etap
                     from renglon left join etaps using (id_etap)
                     where id_renglon=$id_renglon ";
         $resultado_renglon=sql($sql) or fin_pagina();
         $titulo=$resultado_renglon->fields["titulo"];
         $renglon=$resultado_renglon->fields["codigo_renglon"];
         $id_etaps=$resultado_renglon->fields["id_etap"];
         $cantidad=$resultado_renglon->fields["cantidad"];
         $ganancia=$resultado_renglon->fields["ganancia"];
         $tipo=$resultado_renglon->fields["tipo_renglon"];
          if ($resultado_renglon->fields["sin_descripcion"])
                     $checked_sd="checked";
                     else
                     $checked_sd="";
         }

//ya cargo los datos del post solamente
if (!$primera_vez) {
         $tipo    =$_POST["producto"];
         $titulo_post=$_POST["titulo"];
         $renglon =$_POST["renglon"];
         $id_etaps=$_POST["id_etap"];
         $ganancia=$_POST["ganancia"];
         $cantidad=$_POST["cantidad_renglon"];
         $id_etaps=$_POST["select_etap"];
         if ($_POST["sin_descripcion"]==1) $checked_sd="checked";
                                    else   $checked_sd="";
}

if (!$ganancia) $ganancia="0.8";
if (!$cantidad) $cantidad="1";
if ($titulo_post) $titulo=$titulo_post;

if ($_POST["cambio_producto"]) $titulo=setear_titulo($_POST["producto"]);

$link_volver=encode_link("licitaciones_renglones.php",array("id_licitacion"=>$id_licitacion,"ID"=>$id_licitacion));

?>
<form name=form1 method=post>
<input type=hidden name=cambio_producto value='0'>
<input type=hidden name=id_licitacion value=<?=$id_licitacion?>>
<input type=hidden name=primera_vez value=<?=$primera_vez?>>
  <table width=100% align=center class=bordes>
  <tr>
     <!--Aca va la tabla que me mantiene la informacion del renglon -->
     <td width=100%>
           <table align="center" width="100%" border=0>
                 <tr bgcolor="#5090C0" id="mo">
                     <td colspan="4">
                     <? //script para modificar el titulo segun la maquina elegida

if(!$id_renglon)
{
 switch ($_POST['producto'])
 {
  case "Computadora Enterprise":
                               $titulo="Computadora Personal CDR Modelo Enterprise";
                               if($nbre_dist=="Buenos Aires - GCBA")
                                                     $titulo.=" Porteña";
                                                      elseif ($nbre_dist=="Federal")
                                                      $titulo.=" Argentina";
                                
							    break;
  case "Computadora Matrix":
                           $titulo="Computadora Personal CDR Modelo Matrix";
 						   break;
  case "Impresora":
                   $titulo="Titulo Impresora";
				    break;

  case "Software":
                 $titulo="Titulo Software";
				  break;
  case "Otro":
                $titulo="Otro";
                break;
  default:
         $titulo="Computadora Personal CDR Modelo Enterprise";
         if($nbre_dist=="Buenos Aires - GCBA")
            $titulo.=" Porteña";
            elseif ($nbre_dist=="Federal")
            $titulo.=" Argentina";
          break;
 }
}//de if(!$id_renglon)

?>
                     <b>Información del Renglon
                     </td>
                 </tr>
                 <tr id=ma_sf>
                   <td colspan=4 align=center><font color=red size=3><b>Licitacion N°&nbsp;<?=$id_licitacion?></b></font></td>
                 </tr>
                 <tr>
                    <td width=10%><b>Renglon:</b></td>
                    <td>
                      <input type="text" name="renglon"  value="<?=$renglon; ?>" size="80">
                    </td>
                    <td><b>Sin Descripción:</b></td>
                    <td>
                     <input type="CHECKBOX"  name="sin_descripcion" value=1 <?=$checked_sd?>>
                   </td>
                </tr>
                <tr>
                    <td><b>Titulo:</b></td>
                    <td colspan=3>
                      <input type="text" name="titulo"  value="<?=$titulo; ?>" size="80">

                    </td>
                </tr>
                <tr>
                  <td><b>Cantidad:</b></td>
                  <td >
                       <table width=100% align=center>
                          <tr>
                            <td><input type="text" name="cantidad_renglon"  value="<?=$cantidad?>" size="5"></td>
                            <td><b>Ganancia:</b></td>
                            <td><input type="text" name="ganancia"  value="<?=$ganancia?>" size="5"></td>
                            <?
                            if (strtolower($resultado_licitacion->fields['tipo_entidad'])=='federal')
                               {
	                        $sql="select * from etaps ORDER BY TITULO";
	                        $etaps=sql($sql) or fin_pagina();
                            ?>
                             <td><b>ETAPS:</b></td>
                             <td>
                             <select name="select_etap">
                              <option selected value="-1">Seleccione</option>
                              <?=make_options($etaps,"id_etap","titulo",$id_etaps) ?>
                            </select>
                            </td>
                            <?
                            }
                            ?>
                          <td>
                          </td>
                          </tr>
                          </table>
                    </td>
                          <td colspan=2 align=center>
                            <input type="button" name="boton_resumen" value="Resumen" style="width:60%"
                            <?
                            if ($id_renglon)
                                      $onclick="window.opener.document.all.resumen.value=document.all.resumen.value;";
                                      else
                                      $onclick="window.opener.document.all.resumen.value=document.all.resumen.value;window.close();return false";


                            $link=encode_link('renglon_resumen.php',array('onclickguardar'=>$onclick,
                                                                          'id_renglon'=>$id_renglon));

                            ?>
                            onclick="window.open('<?=$link?>'+
                                     '&codigo_renglon='+document.all.renglon.value+'&resumen='+document.all.resumen.value
                                     )"  >
                          <input type="hidden" name="resumen" value="" >
                          </td>

                </tr>
                <tr>
                <td>
                <b>Producto</b>
                </td>
                <td >
                <SELECT name="producto" style="background-color:#0000bb;color:#ffffff;font-family:arial;font-size:10;"  onChange="change_producto();" >
	               <Option <?php if ($_POST['producto']=="Computadora Enterprise" || $tipo=="Computadora Enterprise") echo "selected"; ?> >Computadora Enterprise  </Option>
	               <Option <?php if ($_POST['producto']=="Computadora Matrix" || $tipo=="Computadora Matrix") echo "selected"; ?>>Computadora Matrix  </Option>
	               <Option <?php if ($_POST['producto']=="Impresora" || $tipo=="Impresora") echo "selected"; ?>>Impresora  </Option>
	               <Option <?php if ($_POST['producto']=="Software" || $tipo=="Software") echo "selected"; ?>>Software  </Option>
	               <Option <?php if ($_POST['producto']=="Otro" || $tipo=="Otro") echo "selected"; ?>>Otro </Option>
              </SELECT>
             </td>
             <td colspan=2 align=Center>
             <input type=button name="volver" value="<< Volver" onclick="document.location='<?=$link_volver?>'" style="width:60%">
             </td>
          </tr>
 </table>
</td>
</tr>
<tr>
    <td>
        <!-- Aca empiezo la descripcion de los renglones de la licitacion-->
        <table align="center" width="100%">
            <tr id="mo">
                 <td colspan="5" align="center"><b>Descripción  Renglon</b></td>
            </tr>

           <!--Aca empiezo las descripcion de las renglones -->
           <tr>
             <td colspan=5>
              <table width=100% align=center>
                <tr id="ma">
                     <td width="10%"><b>Cantidad</b> </td>
                     <td width="10%"><b>Producto</b> </td>
                     <td width="55%"><b>Descripcion</b></td>
                     <td width="20%"><b>Precio</b></td>
                     <td width="5%">&nbsp;</td>
               </tr>              
           <?
             
           switch($tipo){
              case 'Impresora':
                        $tipo=$_POST['producto'];
                        if ($id_renglon)
                            generar_descripcion_renglon($impresora,$id_renglon);
                            else
                            generar_descripcion_renglon($impresora,-1);

                        break;//de Impresora
              case 'Software':
                        $tipo=$_POST['producto'];
                        if ($id_renglon)
                            generar_descripcion_renglon($software,$id_renglon);
                            else
                            generar_descripcion_renglon($software,-1);
                        break;//Software
              case 'Otro':
                        $tipo=$_POST['producto'];
                        if ($id_renglon)
                               generar_descripcion_renglon($otro,$id_renglon);
                               else
                               generar_descripcion_renglon($otro,-1);

                        breaK;//Otro
              default:   
                        $tipo='Computadora Enterprise';
                        if ($id_renglon)
                                generar_descripcion_renglon($maquina_basica,$id_renglon);
                                else
                                generar_descripcion_renglon($maquina_basica,-1);

                           
                        ?>
                        </table>
                        </td>
                        </tr>
                        
                        <tr id=mo>
                        <td><img name='img1' src="<?=$html_root."/imagenes/drop2.gif"?>" onclick="ocultar(this,descripcion_renglon_maquina)"></td>
                        <td colspan=4>Productos Adicionales</td>
                        </tr>
                       <tr>
                       <td colspan=5>
                       <div id='descripcion_renglon_maquina' style='display:none'>
                       <table width=100% align=center>
                         <tr id="ma">
                             <td width="10%"><b>Cantidad</b> </td>
                             <td width="10%"><b>Producto</b> </td>
                             <td width="55%"><b>Descripcion</b></td>
                             <td width="20%"><b>Precio</b></td>
                            <td width="5%">&nbsp;</td>
                         </tr>
 
                       <?  
                       
                       if ($id_renglon)
                              $mostrar_tabla=generar_descripcion_renglon($maquina_adicional,$id_renglon);
                              else
                              $mostrar_tabla=generar_descripcion_renglon($maquina_adicional,-1);
                       ?>
                       </table>
                       </div>
                       </td>
                       </tr> 
                       <?
                       breaK;//Computadora Enterprise o MAtrix

           }//del switch
           
           if ($mostrar_tabla){
           ?>
           <script>
          if (document.all.producto.options[document.all.producto.selectedIndex].text=='Computadora Matrix' || document.all.producto.options[document.all.producto.selectedIndex].text=='Computadora Enterprise')    
           {
          document.all.descripcion_renglon_maquina.style.display='block';
          document.all.img1.src='../../imagenes/dropdown2.gif';
          }
           </script>
           <?    
           }
           ?>
          
          
          <tr id=mo>
                 <td><img name='img2' src="<?=$html_root."/imagenes/drop2.gif"?>" onclick="ocultar(this,descripcion_renglon_adicionales)"></td>
                 <td colspan=5>Productos Adicionales</td>
          </tr>
          <tr>
          <td colspan=5>
          <div id='descripcion_renglon_adicionales' style='display:none'>  
          <table width=100% align=center>
          <tr>
          <tr id="ma">
                 <td width="10%"><b>Cantidad</b> </td>
                 <td width="10%"><b>Producto</b> </td>
                 <td width="55%"><b>Descripcion</b></td>
                 <td width="20%"><b>Precio</b></td>
                 <td width="5%">&nbsp;</td>
           </tr>
          <?
         
          if ($id_renglon) $mostrar_tabla=generar_descripcion_adicionales($id_renglon);
                   else   $mostrar_tabla=generar_descripcion_adicionales(-1);
           
          ?>
          </table>
          </div>
          </td>
          </tr>
           <?
            if ($mostrar_tabla){
           ?>
           <script>
           document.all.descripcion_renglon_adicionales.style.display='block';
           document.all.img2.src="../../imagenes/dropdown2.gif";
           </script>
           <?    
           }
           if (!$id_renglon){
           ?>
           <script>
           document.all.descripcion_renglon_adicionales.style.display='block';
           document.all.img2.src="../../imagenes/dropdown2.gif";
          
          if (document.all.producto.options[document.all.producto.selectedIndex].text=='Computadora Matrix' || document.all.producto.options[document.all.producto.selectedIndex].text=='Computadora Enterprise')    
             {
           
              document.all.descripcion_renglon_maquina.style.display='block';
              document.all.img1.src="../../imagenes/dropdown2.gif";
              }
           </script>
           <?                   
           }
           ?>
        </table>
        <!--Aca termina la tabla con las descripciones -->
    </td>
</tr>
<tr>
   <td align=Center>
      <input type=submit name="guardar" value="Guardar" onclick="return verificar_precios(); ">
      &nbsp;
      <input type=button name="cancelar" value="Cancelar" onclick="document.location='<?=$link_volver?>'">
   </td>
</tr>
</table>
</form>
<?
echo fin_pagina();
?>