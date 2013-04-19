<?
/*
Autor: MAC
Fecha: 17/11/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2006/01/04 07:57:07 $

*/
require_once("../../config.php");
$cant=$_POST['cant_hijos'] or $cant=1;
$esta_padre=1;
$esta_hijo=0;
?>
<script>
 var cont;
</script>
<?
if($_POST["guardar"]=="Guardar"){
   $db->StartTrans();
   $select_padre="select codigo_barra,id_producto,id_prod_esp,tipo as tipo_log,nro_orden,id_log_codigos_barra
    from codigos_barra left join log_codigos_barra using(codigo_barra) where codigo_barra='".$_POST['cod_barra_padre']."'";
   $controlp=sql($select_padre) or fin_pagina();
   $chequeo=0;
   $esta_padre=0;
   if($controlp->fields['codigo_barra']==$_POST['cod_barra_padre']){
   		$controlp->fields['codigo_barra'];
   	  	$esta_padre=1;
   	  	$chequeo=1;
   }
   $control=1;
   $arreglo = array ();
   $chequeo2=0;
   while ($control<=$cant){
   		  $cod_bar='cod_barra_hijo_'.$control;
          $select_hijo="select codigo_barra from codigos_barra where codigo_barra='".$_POST[$cod_bar]."'";
          $controlh=sql($select_hijo) or fin_pagina();
          $esta_hijo=0;
          if($controlh->fields['codigo_barra']==$_POST[$cod_bar]){
	   	      $esta_hijo=1;
	   	      $arreglo[$control]=$esta_hijo;
	   	      $chequeo2=1;
          }
   	      else $arreglo[$control]=0;
   	      $control++;
   }

   if ($chequeo==1 && $chequeo2==0){//chequea para poder insertar
   	$control=1;
    while ($control<=$cant){
    	   $cod_bar='cod_barra_hijo_'.$control;
           if($controlp->fields["id_producto"])
            $query="insert into codigos_barra (codigo_barra,id_producto,codigo_padre)
                    values('$_POST[$cod_bar]',".$controlp->fields["id_producto"].",'".$_POST['cod_barra_padre']."')";
           else
            $query="insert into codigos_barra (codigo_barra,id_prod_esp,codigo_padre)
                    values('$_POST[$cod_bar]',".$controlp->fields["id_prod_esp"].",'".$_POST['cod_barra_padre']."')";

           $error_cb_padre="<BR>-----------------------------------------<BR>\n
                            NO SE PUDO HERMANAR EL CODIGO: ".$_POST['cod_barra_padre'].", CON EL CÓDIGO ".$_POST[$cod_bar].".\n
                            <input type='button' value='Volver' onclick=\"document.location='hermanar_codigos_barra.php'\">\n
                            <BR>-----------------------------------------<BR><BR>\n
                        ";
           sql($query,"$error_cb_padre") or fin_pagina();

           if($controlp->fields["nro_orden"])
            	$nro_orden=$controlp->fields["nro_orden"];
           else
            	$nro_orden="null";
           $query="insert into log_codigos_barra (codigo_barra,usuario,fecha,tipo,nro_orden)
                   values('".$_POST[$cod_bar]."','".$_ses_user['name']."','".date("Y-m-d H:i:s",mktime())."','".$controlp->fields["tipo_log"]."',$nro_orden)";
           sql($query) or fin_pagina();
           $control++;
    }//de while
 	$db->CompleteTrans();

 	$msg="<center><b>Los códigos de barra se hermanaron con éxito</b></center>";
 	if ($controlp->fields["id_log_codigos_barra"]=="")
 		$msg.="<br> <b><font color='red'>Atención: El código Padre ".$_POST['cod_barra_padre']." no tiene asociado un Log.<br> Por favor envie este mensaje a la División Software</font></b>";
 	$cant=1;
 	$_POST['cod_barra_padre']="";
 	$_POST['cod_barra_hijo_1']="";
 }//inserta si esta toda bien
}//del guardar

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
 if (document.all.cod_barra_padre.value=="")
    {alerto+="Debe Ingresar el valor del Código de barra Padre\n";
     chequeo=1;
     retorno="false";
    }
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


</script>

<form name="form1" method="POST" action="hermanar_codigos_barra.php">

 <table width="100%" align="center" border="1">

  <tr>
   <td id="ma_sf">
    <input type="text" name="mensaje" size="50" class="text_6" value="Hermanar Códigos de Barras">
   </td>
  </tr>
  <tr>
   <td>
    <table width="100%">
     <?if ($esta_padre==0) {?>
       <tr>
        <td colspan="2" align="center">
         <font size="2" color="Red"><b>El código padre no está cargado en el sitema</b></font>
       </td>
      </tr>
      <?}?>
     <tr>
      <td>

       <b>Código de Barras padre   </b>
      </td>
      <td>
       <b><font color="red" size="4"><input type="text" maxlength="9" <?if ($esta_padre==0) {?>class="text_9" <?}?> tabindex="2" name="cod_barra_padre" size="20"   value="<?=$_POST['cod_barra_padre']?>" onkeyup="alProximoInput(this,this.value,cod_barra_hijo_1);" ></font></b>
      </td>
     </tr>
     <tr>
      <td colspan="2">
       <hr></hr>
      </td>
     </tr>
     <tr>
      <td>
       <b>Cantidad de Códigos Hijos</b>
      </td>
      <td>
       <input type="text" name="cant_hijos" tabindex="1" size="2" value="<?=$cant?>" onkeypress="return filtrar_teclas(event,'1234567890');" >&nbsp;&nbsp;<input name="generar" value="Generar" type="submit">
      </td>
     </tr>
     <tr>
      <td colspan="2">
       <hr></hr>
      </td>
     </tr>
     <?if ($chequeo2==1) {?>
       <tr>
        <td colspan="2" align="center">
         <font size="2" color="Red"><b>Los Códigos en Rojo ya están en el sistema</b></font>
       </td>
      </tr>
      <?}
      $i=1;
       while ($i<=$cant)
      {$orden="cod_barra_hijo_$i";
      ?>
     <tr>
      <td>
       <b>Código de Barras Hijo Nº <?=$i?></b>
      </td>
      <td>

      <?if ($cant==$i)
           {?><input type="text" maxlength="9" name="cod_barra_hijo_<?=$i?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" onkeyup="alProximoInput(this,this.value,document.all.guardar);" >
      <? }else
          {?> <input type="text" maxlength="9" name="cod_barra_hijo_<?=$i?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" onkeyup="alProximoInput(this,this.value,cod_barra_hijo_<?=$i+1?>);" ><?}?>
      </td>
     </tr>
      <?
       $i++;
      }
     ?>
    </table>
   </td>
  </tr>
 </table>
 <table width="100%" align="center">
  <tr>
   <td align="center">
    <input type="submit" name="guardar" value="Guardar" onclick="return controles(); ">
   </td>
   <td align="center">
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr>
 </table>
 <script>
  if(typeof(document.all.cod_barra_padre)!="undefined")
   document.all.cod_barra_padre.focus();
   cont=document.all.cant_hijos.value;
 </script>
</from>
</body>
</html>