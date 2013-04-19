<?php
/*
$Author: diegoinga $
$Revision: 1.71 $
$Date: 2004/09/15 22:04:25 $
*/
require_once("../../config.php");
//obtengo el nro de la licitacion
$nro_licitacion=$parametros["licitacion"] or $nro_licitacion=$parametros["ID"];
$renglon=$_POST['radio_renglon'];

?>
<html>
<head>
<script languaje="javascript" >

function eliminar(valor)
{
var objeto;
objeto=eval("window.document.all.tip"+valor);
objeto.value="";
objeto=eval("window.document.all.tipo"+valor);
objeto.value="";
objeto=eval("window.document.all.descripcion"+valor);
objeto.value="";
objeto=eval("window.document.all.precio"+valor);
objeto.value="";
objeto=eval("window.document.all.cantidad"+valor);
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
objeto4=eval("document.all.tipo"+valor);
if (objeto2.value==0) 
 {objeto3.value=objeto4.value;
  objeto2.value=3; //debo eliminar un producto e insertar otro
 }
if (objeto2.value==1)
  objeto2.value=3; //debo eliminar un producto e insertar otro
if (objeto2.value==4) //no habia nada
 objeto2.value=2; //debo insertar un producto


objeto=eval("document.all.tip"+valor);
objeto.value=wproductos.document.forms[0].tipo_prod.value;

//esta variable contiene el id_producto
objeto=eval("document.all.tipo"+valor);
objeto.value=wproductos.document.forms[0].id_producto.value;
objeto=eval("document.all.descripcion"+valor);
objeto.value=wproductos.document.forms[0].descripcion.value;
objeto=eval("document.all.precio"+valor);
objeto.value=wproductos.document.forms[0].precio.value;
objeto=eval("document.all.cantidad"+valor);
objeto.value=1;
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
 objeto4=eval("window.document.all.tipo"+valor);
 if (objeto.value=="agregar")
 {
  wproductos=window.open(link,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=40,top=100,width=700,height=400,resizable=1');
  objeto.value="eliminar";
 }
 else //elimino fila
 {
   if (objeto2.value==0)
   {objeto3.value=objeto4.value;
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
 switch(document.all.producto.value){

 case 'Impresora':
    if (window.document.all.codigo_renglon.value=="")
    {
    alert("Falta llenar el campo de renglon");
    return false;
   }
  if (window.document.all.titulo.value=="")
   {
    alert("Falta llenar el campo de titulo");
    return false;
    }
  if ((window.document.all.ganancia.value=="") || (window.document.all.ganancia.value<=0) || (window.document.all.ganancia.value > 1) )
   {
   alert("Dato invalido en el campo ganancia");
   return false;
    }
  if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value!=0) && (document.all.precio_impresora.value==""))
 {alert("Falta Precio en Impresora");
  return false;
 }
 document.all.precio_impresora.value=document.all.precio_impresora.value.replace(',','.');
 if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value==0) && (document.all.precio_impresora.value!=""))
 {alert("Falta elegir Impresora");
  return false;
 }
 if ((document.all.select_impresora.options[document.all.select_impresora.selectedIndex].value!=0) &&(document.all.cantidad_impresora.value=="")) {
  alert ("Falta Cantidad en Impresora");
  return false;
  }
 return true;
 break;
case 'Otro':
     if (window.document.all.codigo_renglon.value=="")
    {
    alert("Falta llenar el campo de renglon");
    return false;
   }
  if (window.document.all.titulo.value=="")
   {
    alert("Falta llenar el campo de titulo");
    return false;
    }
  if ((window.document.all.ganancia.value=="") || (window.document.all.ganancia.value<=0) || (window.document.all.ganancia.value > 1) )
   {
   alert("Dato inválido en el campo ganancia");
   return false;
    }


 break;
case 'Software':
  if (window.document.all.codigo_renglon.value=="") {
    alert("Falta llenar el campo de renglon");
    return false;
   }
  if (window.document.all.titulo.value==""){
    alert("Falta llenar el campo de titulo");
    return false;
    }
  if ((window.document.all.ganancia.value=="") || (window.document.all.ganancia.value<=0) || (window.document.all.ganancia.value > 1) ){
   alert("Dato inválido en el campo ganancia");
   return false;
    }
 break;
//entra por computadora CDR o Enterprise
 default:
 if (window.document.all.codigo_renglon.value=="")
 {alert("Falta llenar el campo de renglon");
  return false;
 }
 if (window.document.all.titulo.value=="")
 {alert("Falta llenar el campo de título");
  return false;
 }
 if ((window.document.all.ganancia.value=="") || (window.document.all.ganancia.value<=0) || (window.document.all.ganancia.value > 1) )
   {
   alert("Dato invalido en el campo ganancia");
   return false;
    }
   if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value!=0) && (document.all.precio_sistemaoperativo.value==""))
 {alert("Falta Precio en Sistema Operativo");
  return false;
 }
 document.all.precio_sistemaoperativo.value=document.all.precio_sistemaoperativo.value.replace(',','.');
 if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value==0) && (document.all.precio_sistemaoperativo.value!=""))
 {alert("Falta elegir Sistema Operativo");
  return false;
 }
 if ((document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value!=0) &&(document.all.cantidad_sistemaoperativo.value=="")) {
  alert ("Falta Cantidad en Sistema Operativo");
  return false;
  }
  if (document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].value==0)
  {alert("Debe elegir Sistema Operativo");
  return false;
   }



 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value!=0) && (document.all.precio_kit.value==""))
 {alert("Falta Precio en Kit");
  return false;
 }
 document.all.precio_kit.value=document.all.precio_kit.value.replace(',','.');
 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value==0) && (document.all.precio_kit.value!=""))
 {alert("Falta elegir Kit");
  return false;
 }
 if ((document.all.select_kit.options[document.all.select_kit.selectedIndex].value!=0) &&(document.all.cantidad_kit.value=="")) {
  alert ("Falta Cantidad en Kit");
  return false;
  }
 if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value!=0) && (document.all.precio_madre.value==""))
 {alert("Falta Precio en Placa Madre");
  return false;
 }
 document.all.precio_madre.value=document.all.precio_madre.value.replace(',','.');
 if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value==0) && (document.all.precio_madre.value!=""))
 {alert("Falta elegir Placa Madre");
  return false;
 }
  if ((document.all.select_madre.options[document.all.select_madre.selectedIndex].value!=0) &&(document.all.cantidad_madre.value=="")) {
  alert ("Falta Cantidad en Madre");
  return false;
  }

 if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value!=0) && (document.all.precio_micro.value==""))
 {alert("Falta Precio en Micro");
  return false;
 }
 document.all.precio_micro.value=document.all.precio_micro.value.replace(',','.');
 if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value==0) && (document.all.precio_micro.value!=""))
 {alert("Falta elegir Micro");
  return false;
 }
  if ((document.all.select_micro.options[document.all.select_micro.selectedIndex].value!=0) &&(document.all.cantidad_micro.value=="")) {
  alert ("Falta Cantidad en Micro");
  return false;
  }

 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value!=0) && (document.all.precio_memoria.value==""))
 {alert("Falta Precio en Memoria");
  return false;
 }
 document.all.precio_memoria.value=document.all.precio_memoria.value.replace(',','.');
 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value==0) && (document.all.precio_memoria.value!=""))
 {alert("Falta elegir Memoria");
  return false;
 }
 if ((document.all.select_memoria.options[document.all.select_memoria.selectedIndex].value!=0) &&(document.all.cantidad_memoria.value=="")) {
  alert ("Falta Cantidad en Memoria");
  return false;
  }

 if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value!=0) && (document.all.precio_disco.value==""))
 {alert("Falta Precio en Disco");
  return false;
 }
 document.all.precio_disco.value=document.all.precio_disco.value.replace(',','.');
 if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value==0) && (document.all.precio_disco.value!=""))
 {alert("Falta elegir Disco");
  return false;
 }
  if ((document.all.select_disco.options[document.all.select_disco.selectedIndex].value!=0) &&(document.all.cantidad_disco.value=="")) {
  alert ("Falta Cantidad en Disco");
  return false;
  }

 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value!=0) && (document.all.precio_cd.value==""))
 {alert("Falta Precio en CD-Rom");
  return false;
 }
 document.all.precio_cd.value=document.all.precio_cd.value.replace(',','.');
 if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value==0) && (document.all.precio_cd.value!=""))
 {alert("Falta elegir CD-Rom");
  return false;
 }
  if ((document.all.select_cd.options[document.all.select_cd.selectedIndex].value!=0) &&(document.all.cantidad_cd.value=="")) {
  alert ("Falta Cantidad en Cdrom");
  return false;
  }

 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value!=0) && (document.all.precio_monitor.value==""))
 {alert("Falta Precio en Monitor");
  return false;
 }
 if (document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value==0)
  {
  alert("Debe elegir Monitor");
  return false;
   }
 document.all.precio_monitor.value=document.all.precio_monitor.value.replace(',','.');
 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value==0) && (document.all.precio_monitor.value!=""))
 {alert("Falta elegir Monitor");
  return false;
 }
 if ((document.all.select_monitor.options[document.all.select_monitor.selectedIndex].value!=0) &&(document.all.cantidad_monitor.value=="")) {
  alert ("Falta Cantidad en Monitor");
  return false;
  }

 if ((document.all.select_video.options[document.all.select_video.selectedIndex].value!=0) && (document.all.precio_video.value==""))
 {alert("Falta Precio en Video");
  return false;
 }
 document.all.precio_video.value=document.all.precio_video.value.replace(',','.');
 if ((document.all.select_video.options[document.all.select_video.selectedIndex].value==0) && (document.all.precio_video.value!=""))
 {alert("Falta elegir Placa de Video");
  return false;
 }
  if ((document.all.select_video.options[document.all.select_video.selectedIndex].value!=0) &&(document.all.cantidad_video.value=="")) {
  alert ("Falta Cantidad en Video");
  return false;
  }

 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value!=0) && (document.all.precio_grabadora.value==""))
 {alert("Falta Precio en grabadora");
  return false;
 }
 document.all.precio_grabadora.value=document.all.precio_grabadora.value.replace(',','.');
 if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value==0) && (document.all.precio_grabadora.value!=""))
 {alert("Falta elegir Placa de Video");
  return false;
 }
  if ((document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].value!=0) &&(document.all.cantidad_grabadora.value=="")) {
  alert ("Falta Cantidad en Grabadora");
  return false;
  }

 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value!=0) && (document.all.precio_dvd.value==""))
 {alert("Falta Precio en DVD");
  return false;
 }
 document.all.precio_dvd.value=document.all.precio_dvd.value.replace(',','.');
 if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value==0) && (document.all.precio_dvd.value!=""))
 {alert("Falta elegir DVD");
  return false;
 }
  if ((document.all.select_dvd.options[document.all.select_dvd.selectedIndex].value!=0) &&(document.all.cantidad_dvd.value=="")) {
  alert ("Falta Cantidad en DVD");
  return false;
  }

 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value!=0) && (document.all.precio_red.value==""))
 {alert("Falta Precio en Placa de Red");
  return false;
 }
 document.all.precio_red.value=document.all.precio_red.value.replace(',','.');
 if ((document.all.select_red.options[document.all.select_red.selectedIndex].value==0) && (document.all.precio_red.value!=""))
 {alert("Falta elegir Placa de Red");
  return false;
 }
  if ((document.all.select_red.options[document.all.select_red.selectedIndex].value!=0) &&(document.all.cantidad_red.value=="")) {
  alert ("Falta Cantidad en Placa de Red");
  return false;
  }


 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value!=0) && (document.all.precio_modem.value==""))
 {alert("Falta Precio en Modem");
  return false;
 }
 document.all.precio_modem.value=document.all.precio_modem.value.replace(',','.');
 if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value==0) && (document.all.precio_modem.value!=""))
 {alert("Falta elegir Modem");
  return false;
 }
  if ((document.all.select_modem.options[document.all.select_modem.selectedIndex].value!=0) &&(document.all.cantidad_modem.value=="")) {
  alert ("Falta Cantidad en Modem");
  return false;
  }

 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value!=0) && (document.all.precio_zip.value==""))
 {alert("Falta Precio en Zip");
  return false;
 }
 document.all.precio_zip.value=document.all.precio_zip.value.replace(',','.');
 if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value==0) && (document.all.precio_zip.value!=""))
 {alert("Falta elegir ZIP");
  return false;
 }
  if ((document.all.select_zip.options[document.all.select_zip.selectedIndex].value!=0) &&(document.all.cantidad_zip.value=="")) {
  alert ("Falta Cantidad en Zip");
  return false;
  }

 break;
 }//del switch
<? for($i=1;$i<=15;$i++){
?>

 if (window.document.all.tipo<?=$i?>.value!="") //entonces hay cargado un tipo

 {
  if (window.document.all.cantidad<?=$i?>.value=="")
  {alert("Falta llenar la cantidad de "+window.document.all.tip<?=$i?>.value);
   return false;
  }
  if (window.document.all.precio<?=$i?>.value=="")
  {alert("Falta llenar el precio de "+window.document.all.tip<?=$i?>.value);
   return false;
  }

 }//del primer if

 <?
  } //del for
 ?>
 return true;
} //del verificar_precio_computadora


function incluir(objeto,texto,value,id) //incluye valor en el select objeto
{
objeto.length++;
objeto.options[objeto.length-1].text=texto;
objeto.options[objeto.length-1].value=value;
objeto.options[objeto.length-1].id=id;
}

function limpiar_select()
{

// window.document.all.select_monitor.options.length=0;
// window.document.all.select_disco.options.length=0;
// window.document.all.select_memoria.options.length=0;
// window.document.all.select_modem.options.length=0;
// window.document.all.select_dvd.options.length=0;
// window.document.all.select_cd.options.length=0;
// window.document.all.select_video.options.length=0;
window.document.all.select_madre.options.length=0;
window.document.all.precio_madre.value="";
incluir(window.document.all.select_madre,"Seleccione Placa Madre",0,0);
 //window.document.all.select_grabadora.options.length=0;
// window.document.all.select_red.options.length=0;
// window.document.all.select_zip.options.length=0;
// window.document.all.select_kit.options.length=0;

}


<?php
$sql="select * from productos where tipo='micro' order by desc_gral";
$resultados=$db->execute($sql) or die($query);
while (!$resultados->EOF)
 {$sql="select * from (((compatibilidades join productos on productos.id_producto=motherboard) join precios on precios.id_producto=productos.id_producto) join proveedor on proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones') where componente=".$resultados->fields["id_producto"]." order by desc_gral";
  $resultado_comp=$db->execute($sql) or die($sql);
?>
var micro_<?php echo $resultados->fields["id_producto"]; ?>=new Array(<?php echo $resultado_comp->RecordCount(); ?>);
<?php
$i=0;
while (!$resultado_comp->EOF)
 {?>
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>]=new Array(6);
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][0]=<?php echo $resultado_comp->fields['id_producto']; ?>;
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][1]="<?php echo $resultado_comp->fields['tipo']; ?>";
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][2]="<?php echo $resultado_comp->fields['marca']; ?>";
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][3]="<?php echo $resultado_comp->fields['modelo']; ?>";
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][4]=<?php echo $resultado_comp->fields['precio']; ?>;
 micro_<?php echo $resultados->fields["id_producto"]; ?>[<?php echo $i; ?>][5]="<?php echo $resultado_comp->fields['desc_gral']; ?>";
<?php
$i++;
$resultado_comp->MoveNext();
 }
$resultados->MoveNext();
 }
?>

function cambiar_comp(valor)
{var arreglo;
 var i=0;

switch (valor)
 {
<?php
$resultados->Move(0);
while (!$resultados->EOF)
 {?>
 case '<?php echo $resultados->fields['id_producto']; ?>':arreglo=micro_<?php echo $resultados->fields["id_producto"]; ?>;break;
 <?php
 $resultados->MoveNext();
 }
 ?>
 }// fin switch

if (typeof(arreglo)!="undefined")
{while (i<arreglo.length)
 {switch (arreglo[i][1])
  {
   case "placa madre":incluir(window.document.all.select_madre,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "monitor":incluir(window.document.all.select_monitor,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "disco rigido":incluir(window.document.all.select_disco,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "memoria":incluir(window.document.all.select_memoria,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "modem":incluir(window.document.all.select_modem,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "dvd":incluir(window.document.all.select_dvd,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "cdrom":incluir(window.document.all.select_cd,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "video":incluir(window.document.all.select_video,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "micro":incluir(window.document.all.select_micro,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "grabadora":incluir(window.document.all.select_grabadora,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;
   //case "lan":incluir(window.document.all.select_red,arreglo[i][5],arreglo[i][0],arreglo[i][4]);break;

  }//fin switch
  i++;
  }//fin de while
}//fin if
}//fin funcion cambiar_comp

//si flag == 20 indica que cambio el valor seleccionado para el micro
function llamada_funciones(valor,flag){
if (flag==20) limpiar_select();
cambiar_comp(valor);
} //fin de llamada_funciones

</script>
</HEAD>
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
$estilos_select="style='width:100%;height:50px;'";
$sql="select licitacion.*,entidad.*,tipo_entidad.nombre as tipo_entidad from (licitacion join entidad on licitacion.id_entidad = entidad.id_entidad and id_licitacion = $nro_licitacion) join tipo_entidad using(id_tipo_entidad)";
//$sql="select * from (licitacion join entidad on licitacion.id_entidad = entidad.id_entidad and id_licitacion = $nro_licitacion)";
$resultado_licitacion=$db->execute($sql) or die ($sql);
$sql="select renglon.*,etaps.id_etap,etaps.titulo as titulo_etap,etaps.texto as texto_etap  from renglon left join etaps using (id_etap) where id_renglon=$renglon ";
$resultado_renglon=$db->execute($sql) or die($sql);

$link = encode_link("modificar_renglon.php", array("licitacion"=>$nro_licitacion,"renglon"=>$renglon));
?>
</head>
<body bgcolor='<? echo $bgcolor3;   ?>'>
<FORM  action="<? echo $link; ?>" name="form1" method="POST">
<INPUT TYPE="HIDDEN"  name="producto" value="<? echo $resultado_renglon->fields['tipo']; ?>">
<table align="center" width="100%">
<tr id="mo">
<td colspan="7" align="center">
<font color="#E0E0E0">
<?
if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="del Presupuesto";
else 
                             $asunto_title="de la Licitación";
?>
<b>Datos <?=$asunto_title?></td>
</tr>
 <tr>
   <td width="20%">
   <?
   if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="Presupuesto";
   else 
                             $asunto_title="Licitación";
   ?>  
   <?=$asunto_title?>:  <? echo $nro_licitacion;?>
   </td>
   <td width="15%">
   Entidad
   </td>
   <td>
    <? echo $resultado_licitacion->fields['nombre'];  ?>
   </td>
</table>
<table  align="center" border="0" width="100%" bordercolor="#580000">
<tr id="mo">
  <td colspan="5" align="center">
  <font color="#E0E0E0">
  <b>Información del Renglon</td>
  </tr>
<tr align="left"  id="mo">

           <td>
           <font color="#E0E0E0">
           <b> Renglón
           </td>
           <td>
           <font color="#E0E0E0">
           <b> Título
           </td>
           <td>
           <font color="#E0E0E0">
           <b> Cantidad
           </td>
           <td>
           <font color="#E0E0E0">
           <b>Ganancia
           </td>
           <td>
           <font color="#E0E0E0">
           <b>Sin Desc.
           </td>


</tr>
<tr>
     <td>
     <input type="text" name="codigo_renglon" value="<? echo $resultado_renglon->fields['codigo_renglon']; ?>" size="20%">
     </td>
     <td>
     <input type="text" name="titulo" value="<? echo $resultado_renglon->fields['titulo']; ?>" size="60%">
     </td>
     <td>
     <input type="text" name="cantidad"  value="<? echo $resultado_renglon->fields['cantidad']; ?>" size="10%">
     </td>
     <td align="center">
     <input type="text" name="ganancia" value="<? echo $resultado_renglon->fields['ganancia']; ?>" size="10%" >
     </td>
     <td align="center">
     <input type="CHECKBOX"  name="sin_descripcion" value=1 <? if ($resultado_renglon->fields['sin_descripcion']) echo "checked"; ?>>
     </td>

</tr>
<tr align="left"  id="mo">
           <td>
<? 
if (strtolower($resultado_licitacion->fields['tipo_entidad'])=='federal') 
{
?>
           <font color="#E0E0E0">
           <b> ETAP
<?
}
?>
           </td>
           <td align="center" >
Resumen           
           </td>
           <td>
           </td>
           <td>
           <font color="#E0E0E0">
           <b> Sub Total U$S
           </td>
           <td>
           <font color="#E0E0E0">
           <b> Total U$S
           </td>
</tr>
<?
$sql="select * from producto where id_renglon = $renglon";
$resultados_suma=$db->execute($sql) or die($sql);
$filas_encontradas = $resultados_suma->RecordCount();
$j=0;
$total_renglon=0;
while ($j<$filas_encontradas){
    $total_producto = $resultados_suma->fields['cantidad'] * $resultados_suma->fields['precio_licitacion'];
    $total_renglon+=$total_producto;
    $j++;
    $resultados_suma->MoveNext();
     }

?>
<tr>
<?	
$q="select * from etaps ORDER BY TITULO";
$etaps=$db->Execute($q) or die($db->ErrorMsg()."<br> $q");
?>
     <td align="right">
<?
if (strtolower($resultado_licitacion->fields['tipo_entidad'])=='federal') 
{
?>
     <select name="select_etap" style="width:100%">
		<option value=""  >NO ETAPS</option>
<?=	 make_options($etaps,"id_etap","titulo",$resultado_renglon->fields['id_etap']) ?>		     
     <select>
<?
}
?>
     </td>
     <td align="center" >
<input type="button" name="boton_resumen" value="Ver Resumen" onclick="window.open('<?=encode_link('renglon_resumen.php',array('id_renglon'=>$renglon,'onclickguardar'=>"window.close();"))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=200')"  >
	</td>
     <td>
     </td>
     <td>
     <b>
     <?
     //muestro los subtotales
//    $total_renglon = ceil($total_renglon);
    echo"U\$S ".number_format($total_renglon,2,'.','');
        ?>
     </td>
     <td>
     <b>
     <?
     //muestro con la ganancia
     echo "U\$S ".number_format($total_renglon/$resultado_renglon->fields['ganancia'],2,'.','');
     ?>
     </td>
</tr>
</table>
<hr>
<?php
switch ($resultado_renglon->fields['tipo']) {
 case 'Impresora':  {
?>
<hr>
<table align="center" width="100%">
<tr id="mo">
 <td colspan="6" align="center">
 <font color="#E0E0E0">
 <b>Descripción  Renglon</td>
</tr>
<tr id="mo">
 <td width="10%">
 <font color="#E0E0E0">
 <b>Cantidad
 </td>
 <td width="10%">
 <font color="#E0E0E0">
 <b>Producto
 </td>
 <td width="55%">
 <font color="#E0E0E0">
 <b>Descripción
 </td>
 <td width="20%">
 <font color="#E0E0E0">
 <b>Precio
 </td>
 <td width="15%">
 </td>
 </tr>
<tr>
<?
 $sql="select * from producto where ((id_renglon=$renglon and tipo='impresora') and (comentarios <> 'adicionales' or comentarios IS NULL))";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='impresora' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_impresora" value="<?if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1"?>" size="5">
</td>
<td>Impresora </td>
<input type="hidden" name='flag_impresora' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_impresora' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<td><select name="select_impresora" style="width:100%;height:50px;" onchange="document.all.precio_impresora.value=document.all.select_impresora.options[document.all.select_impresora.selectedIndex].id;document.all.desc_precio_impresora.value=''">
  <option value=0>Seleccione Impresora </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="text" name="precio_impresora" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?> " size="17">
<input type="hidden" name="desc_precio_impresora" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_impresora','id'=>$renglon,'producto'=>'Impresora'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_impresora" value="1">
</td>
</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='conexos' ";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='conexos' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
</td>
<td>Conexo </td>
<td>
<input type="hidden" name='flag_conexo' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_conexo' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_conexo" <? echo $estilos_select; ?> onchange="document.all.precio_conexo.value=document.all.select_conexo.options[document.all.select_conexo.selectedIndex].id;document.all.desc_precio_conexo.value=''" >
<option selected value=0>Seleccione Conexo</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="text" name="precio_conexo" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">
<input type="hidden" name="desc_precio_conexo" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_conexo','id'=>$renglon,'prodcto'=>'Precio Conexo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300);">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_conexo" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='garantia' ";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='garantia' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
</td>
<td>Garantia </td>
<td>
<input type="hidden" name='flag_garantia' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_garantia' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_garantia" <? echo $estilos_select; ?> >
<option selected value=0>Seleccione Garantia</option>
<?
while(!($resultados->EOF)){
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
</tr>



</table>
<hr>
<font size="3"><b>Adicionales</font>
<table id="adicionales" align="center" width="100%">
</table>
<?
 break;
 }
 case 'Software': {
                    $sql="select * from producto where ((id_renglon=$renglon and tipo='garantia') and (comentarios <> 'adicionales' or comentarios IS NULL))";
                    $resultado_cargado=$db->execute($sql) or die($sql);
                     echo "<input type='hidden' name='flag_garantia' value='";if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id']; echo "'>";
                    echo "<input type='hidden' name='idproducto_garantia' value='";if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; echo "'>";
 	                $tipo=$_POST['producto'];
                    echo "<table><tr><td>Garantia</td>
                          <td>";
                    $sql="select * from productos where tipo='garantia' order by desc_gral";
                    $resultados=$db->execute($sql) or die($db->ErrorMsg().$sql);
                    echo "
                    <select name='select_garantia' >
                     <option value=0>Seleccione Garantia</option>";
                    while(!($resultados->EOF)){
                      echo "<option value=".$resultados->fields['id_producto'];
                      if($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto'])
                       echo " selected ";
                      echo ">".$resultados->fields['desc_gral']."</option>";
                      $resultados->Movenext();
                    }
                    echo "
                     </select>
                     </td>
                     </tr>
                     </table>
                    "; 
                    break;
              }
 case 'Otro': { $tipo=$_POST['producto'];
                    $sql="select * from producto where ((id_renglon=$renglon and tipo='garantia') and (comentarios <> 'adicionales' or comentarios IS NULL))";
                    $resultado_cargado=$db->execute($sql) or die($sql);
                    echo "<input type='hidden' name='flag_garantia' value='";if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id']; echo "'>";
                    echo "<input type='hidden' name='idproducto_garantia' value='";if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; echo "'>";

                    echo "<table><tr align='center'><td>Garantia</td>
                          <td>";
                    $sql="select * from productos where tipo='garantia'";
                    $resultados=$db->execute($sql) or die($db->ErrorMsg().$sql);
                    echo "
                    <select name='select_garantia' >
                     <option value=0>Seleccione Garantia</option>";
                    while(!($resultados->EOF)){
                      echo "<option value=".$resultados->fields['id_producto'];
                      if($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto'])
                       echo " selected ";
                      echo ">".$resultados->fields['desc_gral']."</option>";
                      $resultados->Movenext();
                    }
                    echo "
                     </select>
                     </td>
                     </tr>
                     </table>
                    ";
                    break;
 }
 default:{
 $tipo='Computadora Enterprise';
  ?>
 <hr>

<div style="position:relative; width:100%;height:73%; overflow:auto;">

<table align="center" width="100%">
<tr id="mo">
 <td colspan="6" align="center">
 <font color="#E0E0E0">
 <b>Descripción  Renglon</td>
</tr>
<tr id="mo">
 <td width="10%">
 <font color="#E0E0E0">
 <b>Cantidad
 </td>
 <td width="10%">
 <font color="#E0E0E0">
 <b>Producto
 </td>
 <td width="55%">
 <font color="#E0E0E0">
 <b>Descripción
 </td>
 <td id="mo" width="20%">
 <font color="#E0E0E0">
 <b>Precio
 </td>
 <td width="5%">
 </td>
 </tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='kit' and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='kit' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_kit" value="<?if ($resultado_cargado->fields['cantidad'])   echo $resultado_cargado->fields['cantidad']; else echo "1"; ?>" size="5">
</td>
<td>Kit </td>
<td>
<input type="hidden" name='flag_kit' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_kit' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_kit" <? echo $estilos_select; ?> onchange="document.all.precio_kit.value=document.all.select_kit.options[document.all.select_kit.selectedIndex].id;document.all.desc_precio_kit.value=''">
<option selected value=0 >Seleccione Kit</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_kit" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_kit" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>"size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_kit','id'=>$renglon,'producto'=>'kit'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_zip.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_kit" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='micro' and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='micro' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_micro" value="<?  if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo"1";  ?>" size="5"></td>
<td>Micro</td>
<td>
<input type="hidden" name='flag_micro' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_micro' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">

<select name="select_micro" <? echo $estilos_select; ?> onchange="llamada_funciones(document.all.select_micro.options[document.all.select_micro.selectedIndex].value,20);document.all.precio_micro.value=document.all.select_micro.options[document.all.select_micro.selectedIndex].id;document.all.desc_precio_micro.value=''">
<option value=0>Seleccione Microprocesador </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>

</select>

</td>
<td>
<input type="hidden" name="desc_precio_micro" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_micro" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_micro','id'=>$renglon,'producto'=>'Microprocesador'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_micro.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_micro" value="1">
</td>
</tr>

<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='placa madre' and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 if ($resultado_cargado->RecordCount() > 0 ){
      $sql="select * from productos where tipo='placa madre' and id_producto = ".$resultado_cargado->fields['id_producto'];
      $resultados=$db->execute($sql) or die($query);
   }
?>
<td><input type="text" name="cantidad_madre" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo"1"; ?>" size="5"></td>
<td>Placa Madre</td>
<td>
<input type="hidden" name='flag_madre' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_madre' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">

<select name="select_madre" <? echo $estilos_select; ?> onchange="document.all.precio_madre.value=document.all.select_madre.options[document.all.select_madre.selectedIndex].id;document.all.desc_precio_madre.value=''">
<option selected value=0>Seleccione placa madre</option>
<? if ($resultado_cargado->RecordCount() > 0 ) echo "<option selected value=".$resultado_cargado->fields['id_producto'] ?> id="<?php echo $resultado_precio->fields['precio']; ?>"> <? echo $resultados->fields['desc_gral']; ?> </option>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_madre" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_madre" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_madre','id'=>$renglon,'producto'=>'Placa madre'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_madre.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_madre" value="1">
</td>
<SCRIPT>
llamada_funciones(document.all.select_micro.options[document.all.select_micro.selectedIndex].value,10);
</SCRIPT>
</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='memoria'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='memoria' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_memoria" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1"; ?>" size="5"></td>
<td>Memoria</td>
<input type="hidden" name='flag_memoria' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_memoria' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<td>
<select name="select_memoria" <? echo $estilos_select; ?> onchange="document.all.precio_memoria.value=document.all.select_memoria.options[document.all.select_memoria.selectedIndex].id;document.all.desc_precio_memoria.value=''">
<option value=0>Seleccione Memoria </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_memoria" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_memoria" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_memoria','id'=>$renglon,'producto'=>'Memoria'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_memoria.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_memoria" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='disco rigido' and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='disco rigido' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_disco" value="<?  if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1";  ?>" size="5"></td>
<td>Disco</td>
<td>
<input type="hidden" name='flag_disco' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_disco' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_disco" <? echo $estilos_select; ?> onchange="document.all.precio_disco.value=document.all.select_disco.options[document.all.select_disco.selectedIndex].id;document.all.desc_precio_disco.value=''">
<option selected value=0> Seleccione un Disco Rigido </option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_disco" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_disco" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_disco','id'=>$renglon,'producto'=>'Dico rigido'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_disco.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_disco" value="1">
</td>

</tr>
<tr>
<?
  $sql="select * from producto where id_renglon=$renglon and tipo='cdrom'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
  $resultado_cargado=$db->execute($sql) or die($query);
  $sql="select * from productos where tipo='cdrom' order by desc_gral";
  $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_cd" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1"; ?>" size="5"></td>
<td>CD-Rom</td>
<td>
<input type="hidden" name='flag_cd' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_cd' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_cd" <? echo $estilos_select; ?> onchange="document.all.precio_cd.value=document.all.select_cd.options[document.all.select_cd.selectedIndex].id;document.all.desc_precio_cd.value=''">
<option selected value=0>Seleccione cd-rom</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_cd" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_cd" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_cd','id'=>$renglon,'producto'=>'Lectora cd'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_cd.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_cd" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='monitor'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='monitor' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_monitor" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1"; ?>" size="5"></td>
<td>Monitor</td>
<td>
<input type="hidden" name='flag_monitor' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_monitor' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_monitor" <? echo $estilos_select; ?> onchange="document.all.precio_monitor.value=document.all.select_monitor.options[document.all.select_monitor.selectedIndex].id;document.all.desc_precio_monitor.value=''">
<option selected value=0>Seleccione monitor</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_monitor" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_monitor" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_monitor','id'=>$renglon,'producto'=>'Monitor'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_monitor.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_monitor" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='sistema operativo'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='sistema operativo' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_sistemaoperativo" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1"; ?>" size="5"></td>
<td>Sistema Operativo</td>
<td>
<input type="hidden" name='flag_sistemaoperativo' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_sistemaoperativo' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_sistemaoperativo" <? echo $estilos_select; ?> onchange="document.all.precio_sistemaoperativo.value=document.all.select_sistemaoperativo.options[document.all.select_sistemaoperativo.selectedIndex].id;document.all.desc_precio_sistemaoperativo.value=''">
<option selected value=0>Seleccione Sistema Operativo</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_sistemaoperativo" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_sistemaoperativo" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_sistemaoperativo','id'=>$renglon,'producto'=>'Sistema operativo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_sistemaoperativo.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_sistemaoperativo" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='conexos' ";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='conexos' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
</td>
<td>Conexo </td>
<td>
<input type="hidden" name='flag_conexo' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_conexo' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_conexo" <? echo $estilos_select; ?> onchange="document.all.precio_conexo.value=document.all.select_conexo.options[document.all.select_conexo.selectedIndex].id;document.all.desc_precio_conexo.value=''" >
<option selected value=0>Seleccione Conexo</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_conexo" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_conexo" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_conexo','id'=>$renglon,'producto'=>'Precio Conexo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_conexo.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_conexo" value="1">
</td>

</tr>

<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='garantia' ";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='garantia' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td>
</td>
<td>Garantia </td>
<td>
<input type="hidden" name='flag_garantia' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_garantia' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_garantia" <? echo $estilos_select; ?> >
<option selected value=0>Seleccione Garantia</option>
<?
while(!($resultados->EOF)){
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
</tr>


</table>
<hr>
<table id="adicionales" align="center" width="100%">
<tr id="mo">
 <td colspan="6" align="center">
 <font color="#E0E0E0">
 <b>Adicionales del  Renglon</td>
</tr>
<tr id="mo">
 <td width="10%">
 <font color="#E0E0E0">
 <b>Cantidad
 </td>
 <td width="10%">
 <font color="#E0E0E0">
 <b>Producto
 </td>
 <td width="55%">
 <font color="#E0E0E0">
 <b>Descripcion
 </td>
 <td width="20%">
 <font color="#E0E0E0">
 <b>Precio
 </td>
 <td width="5%">
 </td>
 </tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='video'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($sql);
 $sql="select * from productos where tipo='video' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_video" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1";  ?>" size="5"></td>
<td>Video</td>
<td>
<input type="hidden" name='flag_video' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_video' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_video" <? echo $estilos_select; ?> onchange="document.all.precio_video.value=document.all.select_video.options[document.all.select_video.selectedIndex].id;document.all.desc_precio_video.value=''">
<option selected value=0>Seleccione video</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_video" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_video" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_video','id'=>$renglon,'producto'=>'Placa de video'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_video.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_video" value="1">
</td>

</tr>

<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='grabadora'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='grabadora' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_grabadora" value="<? if ($resultado_cargado->fields['cantidad'])  echo $resultado_cargado->fields['cantidad']; else echo"1";  ?>" size="5"></td>
<td>Grabadora</td>
<td>
<input type="hidden" name='flag_grabadora' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_grabadora' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_grabadora" <? echo $estilos_select; ?> onchange="document.all.precio_grabadora.value=document.all.select_grabadora.options[document.all.select_grabadora.selectedIndex].id;document.all.desc_precio_grabadora.value=''">
<option selected value=0 >Seleccione grabadora</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>

</select>
</td>
<td>
<input type="hidden" name="desc_precio_grabadora" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_grabadora" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_grabadora','id'=>$renglon,'producto'=>'Grabadora'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_grabadora.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_grabadora" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='dvd'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='dvd' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>

<td><input type="text" name="cantidad_dvd" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1";  ?>" size="5"></td>
<td>DVD</td>
<td>
<input type="hidden" name='flag_dvd' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_dvd' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_dvd" <? echo $estilos_select; ?> onchange="document.all.precio_dvd.value=document.all.select_dvd.options[document.all.select_dvd.selectedIndex].id;document.all.desc_precio_dvd.value=''">
<option selected value=0>Seleccione dvd</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_dvd" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_dvd" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_dvd','id'=>$renglon,'producto'=>'Lectora de DVD'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_dvd.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_dvd" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='lan'  and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='lan' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_red" value="<?if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1"; ?>" size="5"></td>
<td>Red</td>
<td>
<input type="hidden" name='flag_red' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_red' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_red" <? echo $estilos_select; ?> onchange="document.all.precio_red.value=document.all.select_red.options[document.all.select_red.selectedIndex].id;document.all.desc_precio_red.value=''">
 <option selected value=0>Seleccione placa de red</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
 </select>
</td>
<td>
<input type="hidden" name="desc_precio_red" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_red" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_red','id'=>$renglon,'producto'=>'Placa de Red'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_red.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_red" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='modem' and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='modem' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_modem" value="<?if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo"1"; ?>" size="5"></td>
<td>Modem</td>
<td>
<input type="hidden" name='flag_modem' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_modem' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_modem" <? echo $estilos_select; ?> onchange="document.all.precio_modem.value=document.all.select_modem.options[document.all.select_modem.selectedIndex].id;document.all.desc_precio_modem.value=''">
<option selected value=0>Seleccione Modem</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
 </select>
</td>
<td>
<input type="hidden" name="desc_precio_modem" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_modem" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_modem','id'=>$renglon,'producto'=>'modem'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_modem.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_modem" value="1">
</td>

</tr>
<tr>
<?
 $sql="select * from producto where id_renglon=$renglon and tipo='zip' and (comentarios <> 'adicionales' or comentarios IS NULL)";
 $resultado_cargado=$db->execute($sql) or die($query);
 $sql="select * from productos where tipo='zip' order by desc_gral";
 $resultados=$db->execute($sql) or die($query);
?>
<td><input type="text" name="cantidad_zip" value="<? if ($resultado_cargado->fields['cantidad']) echo $resultado_cargado->fields['cantidad']; else echo "1";  ?>" size="5"></td>
<td>ZIP</td>
<td>
<input type="hidden" name='flag_zip' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id'] ; ?> ">
<input type="hidden" name='idproducto_zip' value="<? if ($resultado_cargado->RecordCount()<=0) echo "0"; else echo $resultado_cargado->fields['id_producto']; ?> ">
<select name="select_zip" <? echo $estilos_select; ?> onchange="document.all.precio_zip.value=document.all.select_zip.options[document.all.select_zip.selectedIndex].id;document.all.desc_precio_zip.value=''">
<option selected value=0>Seleccione ZIP</option>
<?
while(!($resultados->EOF)){
$sql="select precios.precio from precios join proveedor on precios.id_producto=".$resultados->fields['id_producto']." and proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones'";
$resultado_precio = $db->execute($sql) or die($sql);
?>
<option value="<?php echo $resultados->fields['id_producto']; ?>" id="<?php echo $resultado_precio->fields['precio']; ?>" <?php if ($resultados->fields['id_producto']==$resultado_cargado->fields['id_producto']) echo "selected"; ?>> <?php echo $resultados->fields['desc_gral']; ?> </option>";
<?php
$resultados->Movenext();
}
?>
</select>
</td>
<td>
<input type="hidden" name="desc_precio_zip" value="<?=$resultado_cargado->fields["desc_precio_licitacion"];?>">
<input type="text" name="precio_zip" value="<?php echo $resultado_cargado->fields["precio_licitacion"]; ?>" size="17">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_zip','id'=>$renglon,'producto'=>'Zip'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.select_zip.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
</td>
<td align="center">
<input type="checkbox"  name="nuevo_p_zip" value="1">
</td>

</tr>
</table>
  <?
  break; //fin de default
  }
 } //fin de switch
?>
<table id="productos_ad" width="100%">
<tr bgcolor="#5090C0" id="mo">
<td colspan="4" align="center"><font color="#E0E0E0"><b>Productos Adicionados</td>
</tr>
<tr bgcolor="#5090C0" id="mo">
<td width="8%"><font color="#E0E0E0"><b>Cantidad</td>
<td width="10%"><font color="#E0E0E0"><b>Producto</td>
<td width="60%"><font color="#E0E0E0"><b>Descripción</td>
<td width="22%"><font color="#E0E0E0"><b>Precio</td>
</tr>
<?
$i=1;
$sql="select * from producto where id_renglon=$renglon and comentarios='adicionales'";
$resultado=$db->execute($sql) or die($sql);
while (!$resultado->EOF)
{
$link=encode_link("../general/productos2.php",array("tipo"=>$tipo,"fila"=>$i,"onclickcargar"=>"window.opener.agregar($i)"));
  ?>
<tr>
<td width="8%"><input type="text" name="cantidad<?php echo $i; ?>" size='5' value="<?php if ($resultado->fields['cantidad']) echo $resultado->fields['cantidad']; else echo "1";?>"></td>
<td width="10%"><input type="hidden" name="tipo<?php echo $i; ?>" value="<?php echo $resultado->fields['id_producto']; ?>"><input type="text" name="tip<?php echo $i; ?>" style="width=100%" value="<?php echo $resultado->fields['tipo']; ?>" readonly></td>
<input type="hidden" name="desc_precio_<?php echo $i; ?>" value="<?=$resultado->fields["desc_precio_licitacion"];?>">
<td width="60%"><input type="text" name="descripcion<?php echo $i; ?>" value="<?php echo $resultado->fields['desc_gral']; ?>" style="width=100%"></td>
<td width="22%"><input type='text' name="precio<?php echo $i; ?>" size='7' value="<?php echo $resultado->fields['precio_licitacion']; ?>">&nbsp;<input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_'.$i,'id'=>$renglon,'producto'=>'Nuevo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto='+document.all.tipo<?php echo $i; ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">&nbsp;<input type="button" name="boton<?php echo $i; ?>" style='width=51' value="eliminar" onclick="document.all.desc_precio_<?php echo $i; ?>.value='';switch_func(<?php echo $i; ?>,'<?php echo $link; ?>');">
<input type="hidden" name="producto<?php echo $i; ?>">
<input type="hidden" name="estado<?php echo $i; ?>" value="0">
<input type="hidden" name="id<?php echo $i; ?>" value="<?=$resultado->fields['id']?>">

</td>
</tr>
<?php
$i++;
$resultado->MoveNext();
}
while ($i<=15)
{
$link=encode_link("../general/productos2.php",array("tipo"=>$tipo,"fila"=>$i,"onclickcargar"=>"window.opener.agregar($i)"));
?>
<tr>
<td width="8%"><input type="text" name="cantidad<?php echo $i; ?>" value="<?if ($resultado_cargado->fields['cantidad']) echo $resultado->fields['cantidad']; else echo "1";?>"size='5'></td>
<td width="10%"><input type="hidden" name="tipo<?php echo $i; ?>" value=""><input type="text" name="tip<?php echo $i; ?>" style="width=100%" readonly></td>
<input type="hidden" name="desc_precio_<?php echo $i; ?>" value="<?=$resultado->fields["desc_precio_licitacion"];?>">
<td width="60%"><input type="text" name="descripcion<?php echo $i; ?>" value="" style="width=100%"></td>
<td width="22%"><input type='text' name="precio<?php echo $i; ?>" size='7'><input type="button" name="desc_prod_boton" value="C" title="Cargar comentario para este precio" onclick="window.open('<?=encode_link('renglon_comentario.php',array('var'=>'desc_precio_'.$i,'id'=>$renglon,'producto'=>'Nuevo'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300,resizable=1');">&nbsp;<input type="button" name="boton_comentario_prod" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array("id_producto"=>""))?>&id_producto='+document.all.tipo<?php echo $i; ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=300');">&nbsp;<input type="button" value="agregar" style='width=51' name="boton<?php echo $i; ?>" onclick="document.all.desc_precio_<?php echo $i; ?>.value='';switch_func(<?php echo $i; ?>,'<?php echo $link; ?>');">
<input type="hidden" name="producto<?php echo $i; ?>">
<input type="hidden" name="estado<?php echo $i; ?>" value="4">
</td>
</tr>
<?
$i++;
}
?>
</table>
</div>
<hr>
<center>
<input type="submit" name="boton" value="Modificar Renglon" style="width:20%" onclick="return verificar_precios();">
<input type="button" name="boton" value="Volver" style="width:20%" onclick="history.go(-1);">
</center>
<br><br><br>
</form>
</body>
</html>
