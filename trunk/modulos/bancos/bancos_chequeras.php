<?
/*AUTOR: MAC

Esta página maneja el ABM de las chequeras de la empresa

MODIFICADO POR:
$Author: ferni $
$Revision: 1.3 $
$Date: 2005/10/05 19:37:05 $
*/

require_once("../../config.php");

if($parametros['pagina']=="listado")
{//seleccionamos los datos de la caja
 $query="select * from chequera join log_chequera using(id_chequera) where id_chequera=".$parametros['id_chequera'];
 $resultado=$db->Execute($query) or die($db->ErrorMsg()."<br>Error en selección de datos de chequera");
 
 $select_banco=$resultado->fields['idbanco'];
 $primer_cheque=$resultado->fields['primer_cheque'];
 $select_cant_cheques=$resultado->fields['ultimo_cheque'] - $resultado->fields['primer_cheque'];
 $ultimo_cheque=$resultado->fields['ultimo_cheque'];
 $nro_chequera=$resultado->fields['id_chequera'];
 $nro_cheq=$resultado->fields['nro_chequera'];
 if($resultado->fields['cerrada']==1)
 {$permiso="disabled";
  $estado="Cerrada";
 } 
 else 
 {$permiso=""; 
  $estado="En Uso";
 } 
}
elseif($parametros['pagina']=="")
{//si es una nueva chequera,
 //seleccionamos el proximo número de chequera a usarse
 $query="select max(id_chequera) as maxid from chequera";
 $maxid=$db->Execute($query) or die($db->ErrorMsg()."<br>Error en consulta de maximo id de chequera");
 $nro_chequera=$maxid->fields['maxid'] + 1;
 
 $select_banco="";
 $primer_cheque="";
 $select_cant_cheques="";
 $ultimo_cheque="";
 $nro_cheq="";
}	

if($_POST['Guardar']=="Guardar")
{
 $error = 0;
 $select_banco=$_POST['select_banco'];
 $primer_cheque=$_POST['primer_cheque'] or Error("Falta el número del primer cheque");
 $select_cant_cheques=$_POST['select_cant_cheques'];
 $ultimo_cheque=$_POST['ultimo_cheque'] or Error("Falta el número del último cheque");
 $nro_cheq=$_POST['numero_chequera'] or Error("Falta el número de la chequera");
 
 if ($select_banco == -1) {
	Error("Debe seleccionar el banco");
 }
 
 $sql="select * from bancos.chequera where idbanco=$select_banco and nro_chequera=$nro_cheq";
 $res_validacion=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error: no se Puede Ejecutar la Consulta");

if (!$res_validacion->EOF) {
	$error = 1;
	echo "<br>";
	$msg=Error("El Número de Chequera para el Banco Seleccionado YA EXISTE");	
}

 if (!$error) {
	 $db->StartTrans();
	 if($_POST['estado_guardar']=="guardar")
	 {
	  //insertamos la nueva chequera
	  $query="insert into chequera (idbanco,primer_cheque,ultimo_cheque,cerrada,ultimo_cheque_usado,nro_chequera) values($select_banco,$primer_cheque,$ultimo_cheque,0,".($primer_cheque-1).",'".$nro_cheq."')";
	  if($db->Execute($query))
	  {
	   //seleccionamos el id de la nueva chequera
	   $query="select max(id_chequera) as maxid from chequera";
	   $maxid=$db->Execute($query) or die($db->ErrorMsg()."<br>seleccion del maximo id de chequera");
	  
	   $fecha_hoy=date("Y-m-d H:i:s",mktime());
	   //guaramos el log correspondiente
	   $query="insert into log_chequera(id_chequera,tipo,fecha,usuario) values(".$maxid->fields['maxid'].",'apertura','$fecha_hoy','".$_ses_user['name']."')";
	   if($db->Execute($query))
		$msg="<b><center>La chequera con ID ".$maxid->fields['maxid']." se guardó con éxito</center></b>";
	   else 
	   {$msg="<b><center>La chequera no se pudo guardar</center></b>";
		//print_r($db->ErrorMsg().$query);
	   }
	  }//de if($db->Execute($query))
	  else   
	  {$msg="<b><center>La chequera no se pudo guardar</center></b>";
	   //print_r($db->ErrorMsg().$query);
	  } 
	  $db->CompleteTrans();
	 }
	 elseif($_POST['estado_guardar']=="actualizar")
	 {
	  //insertamos la nueva chequera
	  $query="update chequera set idbanco=$select_banco,primer_cheque=$primer_cheque,ultimo_cheque=$ultimo_cheque, nro_chequera='$nro_cheq',ultimo_cheque_usado=".($primer_cheque-1)." where id_chequera=".$_POST['nro_chequera'];
	 
	  if($db->Execute($query))
	  {
	   $fecha_hoy=date("Y-m-d H:i:s",mktime());
	   //guaramos el log correspondiente
	   $query="insert into log_chequera(id_chequera,tipo,fecha,usuario) values(".$_POST['nro_chequera'].",'modificación','$fecha_hoy','".$_ses_user['name']."')";
	   if($db->Execute($query))
		$msg="<b><center>La chequera con ID  ".$_POST['nro_chequera']." se actualizó con éxito</center></b>";
	   else 
	   {$msg="<b><center>La chequera no se pudo actualizar</center></b>";
		//print_r($db->ErrorMsg().$query);
	   }
	  }//de if($db->Execute($query))
	  else   
	  {$msg="<b><center>La chequera no se pudo actualizar</center></b>";
	   //print_r($db->ErrorMsg().$query);
	  }
	  $db->CompleteTrans();
  $link=encode_link("bancos_listado_chequeras.php",array("msg"=>$msg));
  echo "<script>document.location='$link'</script>";
 } 
 
 $query="select max(id_chequera) as maxid from chequera";
 $maxid=$db->Execute($query) or die($db->ErrorMsg()."<br>Error en consulta de maximo id de chequera");
 $nro_chequera=$maxid->fields['maxid'] + 1;
 
 $select_banco="";
 $primer_cheque="";
 $select_cant_cheques="";
 $ultimo_cheque="";
 $nro_cheq="";
 }//del if (!$error)
 
 
}//de if($_POST['Guardar']=="Guardar")	

echo $html_header;
?>
<script>
 function calcular_ultimo()
 {if (!(isNaN(parseFloat(document.all.primer_cheque.value))) && document.all.primer_cheque.value.indexOf('.')==-1 && document.all.primer_cheque.value.indexOf(',')==-1)
     {
     document.all.ultimo_cheque.value=eval(document.all.primer_cheque.value) + eval(document.all.select_cant_cheques.value) - 1;
     }
     //del if";
     else 
     {
       alert('Número Inválido');
       document.all.primer_cheque.value="";
     }
  
 }	
</script>
<?
if($parametros['pagina']=="listado")
{
?>
 <div style='position:relative; width:100%; height:15%; overflow:auto;'>
 <table width="100%">
   <?
   while(!$resultado->EOF)
   {?>
    <tr id=ma>
      <td>
       Fecha de <?=$resultado->fields['tipo']?>: <?=fecha($resultado->fields['fecha'])?>
     </td>
     <td>
      Usuario: <?=$resultado->fields['usuario']?>
     </td>
    </tr>
  <?
   $resultado->MoveNext();
   }
  ?>
 </table>
 </div>  
<?
}
?>
<br><?=$msg?><br>
<form name="form1" method=post action="bancos_chequeras.php">
 <input name="estado_guardar" type="hidden" value="<?if($parametros['pagina']=="listado")echo "actualizar";else echo "guardar"?>">
 <input name="nro_chequera" type="hidden" value="<?=$nro_chequera?>">
 <table align="center" width="70%" class="bordes">
  <tr>
   <td id=mo align="center" colspan="2">
    <font size=3><b>Datos de la chequera</b></font>
   </td>
  </tr>
  <tr ><td>
  <table width="100%" bgcolor=<?=$bgcolor_out?>>
  <tr>
   <td>
    <b>ID  Chequera</b>
   </td>
   <td>
    <?=$nro_chequera;?>
   </td>
  </tr>
  <tr>

   <td>
    <b>Nº de Chequera</b>
   </td>
   <td><input name="numero_chequera" type="text" value="<?=$nro_cheq?>"  <?=$permiso?>>
   </td>
  </tr>
  <tr>

   <td width="40%">
    <b>Banco</b>
   </td>
   <td>
    <?//traemos los bancos
     $query="select * from tipo_banco where activo=1 order by nombrebanco ";
     $bancos=$db->Execute($query) or die ($db->ErrorMsg()."<br>error al traer los bancos");
    ?>
    <select name="select_banco"  <?=$permiso?>>
     <option value=-1>Seleccione Banco</option>
     <?
      while(!$bancos->EOF)
      {?>
       <option value=<?=$bancos->fields['idbanco']?> <?if($select_banco==$bancos->fields['idbanco'])echo "selected";?>><?=$bancos->fields['nombrebanco']?></option>
      <? 	
       $bancos->MoveNext();	
      }	
     ?> 
    </select> 
   </td>
  </tr> 
  <tr>
   <td>
    <b>Primer Nº de Cheque</b>
   </td>
   <td>
    <input type="text" name="primer_cheque" value="<?=$primer_cheque?>"  <?=$permiso?> onchange="calcular_ultimo()">
   </td> 
  </tr>
  <tr>
   <td>
    <b>Cantidad de Cheques</b>
   </td>
   <td>
    <select name="select_cant_cheques"  <?=$permiso?> onchange="calcular_ultimo()">
     <option value=25 <?if($select_cant_cheques==25)echo "selected";?>>25</option>
     <option value=50 <?if($select_cant_cheques=="")echo "selected";elseif($select_cant_cheques==50)echo "selected";?>>50</option>
    </select>
   </td>
  </tr>
  <tr>
   <td>
    <b>Último Nº de Cheque</b>
   </td>
   <td>
    <input type="text" name="ultimo_cheque" value="<?=$ultimo_cheque?>"  <?=$permiso?> readonly>
   </td> 
  </tr>
  <tr>
   <td><b>Estado</b></td>
   <td><?if($estado) echo $estado;else echo "Nueva"?></td>
  </tr>
  <tr>
  <td colspan="2" align="center"><br>
   <input type="submit" name="Guardar" value="Guardar" <?=$permiso?>>
<? if($parametros['pagina']=="listado")
  {?>
   <input type="button" name="Volver" value="Volver" onclick="document.location='bancos_listado_chequeras.php'">
<?}?>
  </td>
  </tr>
  </table>
 </td>
 </tr>
 </table>
</form>
</body>
</html>