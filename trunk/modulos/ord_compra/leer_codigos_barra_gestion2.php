<?
/*
Autor: MAC
Fecha: 17/11/04

MODIFICADA POR
$Author: gabriel $
$Revision: 1.2 $
$Date: 2006/01/05 14:45:38 $

*/
require_once("../../config.php");

/*************************************************************************
Este Archivo se usa para mostrar los datos cargados para las OC recibidas
antes de la subida de gestion3
**************************************************************************/


if($_POST["guardar"]=="Guardar")
{
 //die("No se puede guardar ningun cambio con esta ventana. Contactese con la División Software");
 $db->StartTrans();
 $error_cb_duplicado="";
 //$link_cb=encode_link("leer_codigos_barra.php",array("total_comprado"=>$_POST['total_comprado'],"producto_nombre"=>$_POST['nombre_producto'],"id_producto"=>$_POST['id_producto'],"nro_orden"=>$_POST['nro_orden'],"nro_rma"=>$_POST['nro_rma'])); 
 //insertamos los nuevos codigos de barra ingresados
 for($j=$_POST["primer_nuevo_cb"];$error_cb_duplicado=="" && $j<$_POST["total_comprado"];$j++)
 {if($_POST['cod_barra_'.$j]!="")
  {
   //controlamos si el codigo de barras fue insertado o no
   $query="select codigo_barra from codigos_barra where codigo_barra='".$_POST['cod_barra_'.$j]."'";	
   $esta_cb=sql($query,"<br>Error al buscar CB ya insertado") or fin_pagina();
  	
   if($esta_cb->fields["codigo_barra"]!="")
   {$error_cb_duplicado="<font color=red><b>-----------------------------------------<BR>\n
                        EL CÓDIGO DE BARRAS INGRESADO: ".$_POST['cod_barra_'.$j].", YA EXISTE.\n
                        <BR>-----------------------------------------<BR><BR></b></font>\n
                        ";
    $cb_con_error=$_POST['cod_barra_'.$j];
   }
   else
   {$cb_con_error="";
   	$query="insert into codigos_barra (codigo_barra,id_producto,codigo_padre)
          values('".$_POST['cod_barra_'.$j]."',".$_POST['id_producto'].",'".$_POST['cod_barra_'.$j]."')";
   
    sql($query,"<br>Error al insertar CB<br>") or fin_pagina();
    $fecha_hoy=date("Y-m-d H:i:s");
    if($_POST["nro_orden"])
    {$oc=$_POST["nro_orden"];
     $tipo="Producto Ingresado mediante la OC Nº $oc";
    }
    else 
     $oc="null";
    if($_POST["nro_rma"])
    {$rma=$_POST["nro_rma"];
     //consultamos datos de ese rma, para poder ponerlo en el log
     $query="select nrocaso,nro_ordenc,nro_ordenp from info_rma where id_info_rma=$rma";
     $error_cb_rma="<BR>-----------------------------------------<BR>\n
                         ERROR AL TRAER DATOS DE RMA.\n
                         <BR>-----------------------------------------<BR><BR>\n
                         ";
     $datos_rma=sql($query,$error_cb_rma) or fin_pagina();
    
     $tipo="Producto Ingresado mediante el RMA con Nº de Orden de Compra ".$datos_rma->fields["nro_ordenc"]." ";
     if ($datos_rma->fields["nrocaso"]) 
      $tipo.="y el Nº de C.A.S ".$datos_rma->fields["nrocaso"];
     elseif ($datos_rma->fields["nro_ordenp"])   
      $tipo.="y Nº de Orden de Producción ".$datos_rma->fields["nro_ordenp"];
     }
     else 
      $rma="null";
     $query="insert into log_codigos_barra(codigo_barra,usuario,fecha,tipo,nro_orden,id_info_rma)
            values('".$_POST['cod_barra_'.$j]."','".$_ses_user["name"]."','$fecha_hoy','$tipo',$oc,$rma)";
     $error_cb_rma="<BR>-----------------------------------------<BR>\n
                         ERROR AL GUARDAR LOGS.\n
                         <BR>-----------------------------------------<BR><BR>\n
                         ";
     sql($query) or fin_pagina();
     $error_cb_duplicado="";
    }//de if($esta_cb->fields["codigo_barra"]!="") 
   }//de if($_POST['cod_barra_'.$j]!="")
 }//de for($j=$_POST["primer_nuevo_cb"];$j<$_POST["total_comprado"];$j++)
 
 if($error_cb_duplicado=="")
 { $db->CompleteTrans();
  $msg="<center><b>Los códigos de barra ingresados se cargaron con éxito</b></center>";
 } 
 else 
 {$db->CompleteTrans(false); 
  echo $error_cb_duplicado;
 }
 
}//DE if($_POST["guardar"]=="Guardar")

if($_POST["borrar"]=="Borrar")
{
 //die("No se puede guardar ningun cambio con esta ventana. Contactese con la División Software");	
 $db->StartTrans();
 //borramos el codigo de barras que indica el hidden
 $a_borrar=$_POST["cb_a_borrar"];
 $error_cb_borrar="<BR>-----------------------------------------<BR>\n
                        NO SE PUDO BORRAR EL CÓDIGO DE BARRAS: $a_borrar.\n
                        <BR>-----------------------------------------<BR><BR>\n
                        ";
 $query="delete from log_codigos_barra where codigo_barra='$a_borrar'";
 sql($query,"$error_cb_borrar") or fin_pagina();
 
 $query="delete from codigos_barra where codigo_barra='$a_borrar'";
 sql($query,"$error_cb_borrar") or fin_pagina(); 
 $db->CompleteTrans();
 $msg="<center><b>El codigo de barra $a_borrar fue borrado con éxito</b></center>";
}

echo $html_header;

//estas variables tienen que tomar los valores desde ord_compra_fin.php
$producto_nombre=$parametros["producto_nombre"] or $producto_nombre=$_POST["producto_nombre"];
$total_comprado=$parametros["total_comprado"] or $total_comprado=$_POST["total_comprado"];
$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"];
$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];
$nro_rma=$parametros["nro_rma"] or $nro_rma=$_POST["nro_rma"];

//traemos los codigos de barra ya cargados, y permitimos agregar los
//que faltan, si es que falta alguno.
if($nro_orden)
 $query="select codigo_barra from codigos_barra join log_codigos_barra using(codigo_barra) where id_producto=$id_producto and nro_orden=$nro_orden and tipo ilike '%Ingresado mediante la OC%'";
elseif($nro_rma) 
 $query="select codigo_barra from codigos_barra join log_codigos_barra using(codigo_barra) where id_producto=$id_producto and id_info_rma=$nro_rma and tipo ilike '%Ingresado mediante el RMA%'";
else
 die("Falta Nro Orden o Nro de RMA."); 
$codigos_guardados=sql($query) or fin_pagina();

$cantidad_ingresar=$total_comprado - $codigos_guardados->RecordCount();
echo $msg;
?>
<script>
function alProximoInput(elmnt,content,next,index)
{
  var boton;
	
  if (content.length==elmnt.maxLength)
	{
	  
	  if (typeof(next)!="undefined")
		{
		  next.focus();
		}
	  else
	   document.all.guardar.focus();	
	  
      if(typeof(boton=eval("document.all.autocompletar_consecutivos_"+index))!="undefined")
      {
         boton.style.visibility='visible';
      }
  
	}//de if (content.length==elmnt.maxLength)
	
}//de function alProximoInput(elmnt,content,next)


function habilitar_deshabilitar_ingreso_serial(valor_checked)
{
 var i=document.all.primer_nuevo_cb.value;	
 var cb_text;
 
 while(typeof(eval("document.all.cod_barra_"+i))!="undefined")	
 {
  cb_text=eval("document.all.cod_barra_"+i);
  if(valor_checked==1)
   cb_text.maxLength=100;
  else 
   cb_text.maxLength=9;

  i++;
 }
 
}//de function habilitar_ingreso_serial()

</script>
<script src="funciones.js"></script>
<form name="form1" method="POST" action="leer_codigos_barra.php">
 <input type="hidden" name="id_producto" value="<?=$id_producto?>">
 <input type="hidden" name="id_fila" value="<?=$id_fila?>">
 <input type="hidden" name="total_comprado" value="<?=$total_comprado?>">
 <input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
 <input type="hidden" name="nro_rma" value="<?=$nro_rma?>">
 <input type="hidden" name="producto_nombre" value="<?=$producto_nombre?>">
 <table width="100%" align="center" border="1">
  <tr>
   <td id="ma">
    Ingrese los números de códigos de barra para el producto <br>"<?=$producto_nombre?>"
   </td>
  </tr>
  <tr>
   <td>
   <b>Cantidad de Nº a ingresar: <?=$cantidad_ingresar?></b>
    <?/*if(permisos_check("inicio","permiso_ingresar_serial_recepcion"))
    {?>
     <br><br>
     <input type="checkbox" name="permitir_seriales" value="1" onclick="habilitar_deshabilitar_ingreso_serial(this.checked);"> <b>Ingresar Nº Serial</b>
    <?
    }*/
    ?>
   </td>
  </tr>
  <tr>
   <td>
    <table width="100%">
  <?
  //primero mostramos los codigos de barra ya insertados antes en los
  //input, pero con readonly
  $io=0;
  while(!$codigos_guardados->EOF)
  {?>
  	<tr>
    <td>
     &nbsp;&nbsp;&nbsp;&nbsp;<!--para acomodar los campos correctamente-->
     <input type="text" name="cod_barra_<?=$io?>" maxlength="9" tabindex="<?=$io+1?>" size="30" readonly value="<?=$codigos_guardados->fields["codigo_barra"]?>" onkeyup="toUnicode(this,this.value,cod_barra_<?=$io+1?>);" >
     <input type="submit" name="borrar" value="Borrar" style="width:63" onclick="
  																			if(confirm('Se borrará el código de barra <?=$codigos_guardados->fields["codigo_barra"]?> del sistema.\n¿Está seguro?'))
  																			{document.all.cb_a_borrar.value='<?=$codigos_guardados->fields["codigo_barra"]?>';
  																			 return true;
  																			}
  																			else
  																			 return false;
  	"
    disabled
    >
    </td>
   </tr>
   <?
   $io++;
   $codigos_guardados->MoveNext(); 
  }	
  //guardamos el número a partir del cuál debemos empezar a insertar
  //los nuevos códigos de barra ingresados (el resto de los números
  //ya fueron ingresados antes)
  $foco=$io;
  ?>
  <input type="hidden" name="primer_nuevo_cb" value="<?=$io?>">
  <input type="hidden" name="cb_a_borrar" value="">
  <? 
  for($io;$io<$total_comprado;$io++)
  {?>
   <tr>
    <td>
     <?
     if($io==$total_comprado-1)
     {$third_par="document.all.guardar";
     }
     else
     {
      $third_par="cod_barra_".($io+1);
     }

     if($_POST["cod_barra_$io"])
     {$valor_cb=$_POST["cod_barra_$io"]; 
      if($valor_cb==$cb_con_error)
       $estilo_error="style='color:red'"; 
      else 
       $estilo_error=""; 
     }
   	 else 
   	 {$valor_cb="";
   	  $estilo_error=""; 
   	 } 

   	 if($io<$total_comprado-1)
   	 {?>
      <input type="button" name="autocompletar_consecutivos_<?=$io?>" value="V" title="Autocompletar codigos de barra consecutivos" onclick="autocompletar_codigos_barra(document.all.cod_barra_<?=$io?>.value,'cod_barra_',<?=$io+1?>)" style="visibility:hidden">
     <?
   	 }
   	 else  
   	  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
     ?>
     <input type="text" maxlength="9" tabindex="<?=$io+1?>" name="cod_barra_<?=$io?>" value="<?=$valor_cb?>" <?=$estilo_error?> size="30" onkeyup="alProximoInput(this,this.value,<?=$third_par?>,<?=$io?>);"> 
     <input type="button" name="limpiar_<?=$io?>" value="Limpiar" onclick="document.all.cod_barra_<?=$io?>.value=''">
    </td>
   </tr>
  <? 
  }
  ?>
   <input type="hidden" name="cant_vacios" value="<?=$io-$foco?>">
    </table>
   </td>
  </tr> 
 </table>
 <table width="100%" align="center">
  <tr>
   <td align="center">
    <input type="submit" name="guardar" value="Guardar" <?if($cantidad_ingresar<=0) echo "disabled"//el boton se pone disabled porque solo se muestran los datos, pero no se puede usar mas esta ventana en el gestion3?>>
   </td>
   <td align="center"> 
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr> 
 </table> 
 <script>
  if(typeof(document.all.cod_barra_<?=$foco?>)!="undefined")
   document.all.cod_barra_<?=$foco?>.focus();
 </script>
</from>
</body>
<?fin_pagina();?>
</html>