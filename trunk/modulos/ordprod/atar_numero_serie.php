<?
/*
Autor: enrique
Fecha: 17/11/04

MODIFICADA POR
$Author: enrique $
$Revision: 1.4 $
$Date: 2006/05/17 17:41:25 $

*/
require_once("../../config.php");
$numero=$parametros['id_prod'] or $numero=$_POST['id_prod'];
$pagina_ant=$parametros['pagina'] or $pagina_ant=$_POST['pagina'];
$cant=$_POST['cant_hijos'] or $cant=1;
//$cant=$_POST['cant_hijos'] or $cant=1;
//$esta_padre=1;
$esta_hijo=0;
?>
<script>
 var cont;
</script>
<?

/////Guardar codigos de barra ya existentes

if($_POST["guardar"]=="Guardar")
{ 
  $db->StartTrans();     
  $campo1 =$_POST['campo_1'];
  $campo2 =$_POST['campo_2'];
  $select_hijo="select codigo_barra,id_producto_compuesto from codigos_barra where codigo_barra='$campo1'";   
  $controlh=sql($select_hijo) or fin_pagina();          
  if($controlh->fields['id_producto_compuesto']!="") 
   {
   	$no=1;
   	$id_producto=$controlh->fields['id_producto_compuesto'];
   	
   }
  else
   {
   $select_hijo="select codigo_barra,id_producto_compuesto from codigos_barra where codigo_barra='$campo2'";   
   $controlh=sql($select_hijo) or fin_pagina();
   if($controlh->fields['id_producto_compuesto']!="") 
   {
   	$no=1;
    $id_producto=$controlh->fields['id_producto_compuesto'];
   }
   else
   {
    $no=0;
   }   
   }
  
  $select_nro="select nro_serie from ordenes.maquina where nro_serie='$campo1'";   
  $control_nro=sql($select_nro) or fin_pagina();          
  if($control_nro->fields['nro_serie']!="") 
   {
   	$con_serie=1;
   	$numero_serie=$campo1;  	
   }
  else
   {
   $select_nro="select nro_serie from ordenes.maquina where nro_serie='$campo2'";   
   $control_nro=sql($select_nro) or fin_pagina();  
   if($control_nro->fields['nro_serie']!="") 
   {
   	$con_serie=1;
   	$numero_serie=$campo2;
   }
   else
   {
    $con_serie=0;
   }   
   } 
   if(($con_serie==1)&&($no==1))
   {
   $select_id="select nro_serie from productos_compuestos where id_producto_compuesto=$id_producto";
   $sel_id	=sql($select_id,"No se pudo recuperar el numero de serie") or fin_pagina();
   $nro=$sel_id->fields["nro_serie"];
   
   $update_serie="update productos_compuestos set nro_serie='$numero_serie' where id_producto_compuesto=$id_producto";	
   $up_serie=sql($update_serie,"No se pudo actualizar el numero de serie") or fin_pagina();
   $comentarios="El Nro de Serie y el Producto Compuesto se Ataron Correctamente";
   $error=1;

   /*else 
   {
   $comentarios="El codigo de barra ya esta atado a otro Numero de serie";	
   $error=0;
   }*/
   }
   else 
   {
   if(($con_serie==0)&&($no==1))
   {	
   $comentarios="El numero de serie es Incorrecto";
   $error=0;	
   }
   else 
    {
     if(($con_serie==1)&&($no==0))
     {	
     $comentarios="El Codigo de Barra es Incorrecto";
     $error=0;	
     }
     else 
     {
     $comentarios="El Codigo de Barra y el Numero de serie son Incorrecto";
     $error=0;	
     }	
    }
   }
  
  $db->CompleteTrans(); 
 } 

///Borrar codigos ya existentes 
 
if(($_POST["borrar"]=="Borrar")&&($pagina_ant==1))
{     
  $barra=$_POST['cod_borrar'];
  $numero=$_POST['id_prod'];
  $borrar_cod="update codigos_barra set id_producto_compuesto=null where codigo_barra='$barra' and id_producto_compuesto=$numero";
  $barra=sql($borrar_cod,"no de pudo dar de baja los codigos de barra") or fin_pagina();  
}
echo $html_header;

echo $msg."<br>";
?>
<script>

function alProximoInput(elmnt,content,next)
{
  if (content.length==elmnt.maxLength)
	{
	  
	  if (typeof(next)!="undefined")
		{
		  next.readOnly=0;
		  next.focus();
		  //document.all.mensaje.value="Ingrese el código de barras a hermanar";
		}
	  else
	  {
	  
	   document.all.guardar.focus();	
	  } 
	}
}

function controles()
{var aux=1;
 var chequeo=0;
 var retorno="true";
 var alerto="";
 alerto+="--------------------------------------------------------------\n" ;  	
 while (aux<=cont)
       {texto=eval("document.all.cod_barra_hijo_"+aux);
        if (texto.value=="")
           {alerto+="Debe ingresar el valor del Código de barra Hijo Nº "+aux+"\n";           
            retorno="false";
            chequeo=1;
           }
        aux++;
       }
 
 if (chequeo) 
    {alerto+="--------------------------------------------------------------\n";  
   	 alert(alerto);
    } 
           
 if (retorno=="false") return false;      	   
 else return true;  
 
}
function anular_enter()
{ 
   var campo=eval("document.all.campo_2");
   campo.focus();


}

</script>

<form name="form1" method="POST" action="atar_numero_serie.php" onkeypress="if(getkey(event)==43) document.all.guardar.click();">
 <input type='hidden' name='pagina' value='1'>
 <input type='hidden' name='id_prod' value='<?=$numero?>'>
 <table align="center">
 <tr>
 <td align="center">
 <font color="Green" size="2"><b>Para Guardar presioné la tecla "+" o el botón Guardar</b></font> 
 </td>
 </tr>
 </table>
 
 <table width="65%" align="center" border="1" bordercolor="Black">
 
   <?
   if($error==0)
   {?>
	   <tr>
	   <td align="center"> <font size="3" color="Red"><b>
	   <?=$comentarios?>
	   </font></b></td>
	   </tr>
	   <tr align="center">
	   <td id="ma_sf">
	   <font color="Blue" size="2"><b>Atar Nro de Serie a un Producto Compuesto</b></font> 
	   </td>
	   </tr>
	   <tr>
	   <td align="center">
	   <table width="100%" align="center">
	     <tr>
	   <td align="center">  
	    <!--boton para anular el enter -->
       <input type="submit" name="oculto" value="" onclick="anular_enter();return false;" style="width:0px"> 
	   <input type="text" maxlength="15" name="campo_1" value="<?=$campo1?>">
	   </td>
	   <td align="center">   
	   <input type="text" maxlength="15" name="campo_2" value="<?=$campo2?>">
	   </td>
	   </tr>
   <?
   }
   else
   {
   ?> 
	  <tr>
	  <td align="center"> <font size="3" color="Green"><b>
	  <?=$comentarios?>
	  </font></b></td>
	  </tr>
	  <tr align="center">
	  <td id="ma_sf">
	  <font color="Blue" size="2"><b>Atar Nro de Serie a un Producto Compuesto</b></font> 
	  </td>
	  </tr>
	  <tr>
	   <td align="center">
	   <table width="100%" align="center">
	   <tr>
	   <td align="center">   
	   <input type="text" maxlength="15" name="campo_1" value="">
	   </td>
	   </tr>
	   <tr>
	   <td align="center">   
	   <input type="text" maxlength="15" name="campo_2" value="">
	   </td>
	   </tr>
   <?
   }
   ?>
   </table>
   <input type='hidden' name='error' value=''>
   <input type='hidden' name='campo1 value=''>
   <input type='hidden' name='campo2' value=''>
   </td>
   </tr> 
   </table>
   <table width="65%" align="center">
   <tr>
   <td align="center">
    

   <input type="submit" name="guardar" value="Guardar" onclick="return controles(); ">
   </td>
   <td align="center"> 
   <input type="button" name="volver" value="Volver" onclick="document.location='productos_compuestos.php'">
   </td>
   </tr> 
   </table> 
</from>
<script> 
   document.all.campo_1.focus();  
 </script>
</body>
</html>