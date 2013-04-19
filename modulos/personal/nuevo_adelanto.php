<?php

/*
Autor: Broggi
Creado: 01/06/2004

$Author: broggi $
$Revision: 1.10 $
$Date: 2004/08/31 20:56:07 $
*/

require_once "../../config.php";

//print_r($_POST);

//******Cargo todo el $_POST en variables********************
$personal=$_POST['personal'];
$monto_solicitado=$_POST['monto_solicitado'];
$cantidad_de_pagos=$_POST['cantidad_de_pagos'];
$plan_de_pago=$_POST['plan_de_pago'];
$pendiente=$_POST['pendiente'];
$observacion=$_POST['observacion'];
//$id_cuenta=$_POST['paso_id'];
if ($parametros['pagina']) $id_cuenta=$parametros['id']; else $id_cuenta=$_POST['paso_id'];
if ($parametros['pagina']) $pagina=$parametros['pagina']; else $pagina=$_POST['pagina_vuelve']; 
//***********************************************************


if ($_POST['guardar_comentario']=="Guardar Comentario")
{$fecha=date("Y-m-d H:i:s");
 $sql = "update cuenta set comentarios='$observacion' where id_cuenta=".$id_cuenta;
 $result_consulta = sql($sql) or fin_pagina();
}


//******Esto es por si viene de la pagian adelanto_cuenta****
if ($parametros['pagina']=="adelanto_cuenta" or $pagina=="adelanto_cuenta")
{$id_cuenta=$id_cuenta;
 $sql = "select cuenta.* from cuenta where id_cuenta=$id_cuenta";
 $result_consulta=sql($sql) or fin_pagina();
 $personal=$result_consulta->fields['id_legajo'];
 $monto_solicitado=$result_consulta->fields['monto'];
 $cantidad_de_pagos=$result_consulta->fields['cantidad_pagos'];
 $estado2=$result_consulta->fields['estado'];
 $observacion=$result_consulta->fields['comentarios'];
}
//***********************************************************

if($plan_de_pago=="Armar Plan de Pago")
{$cuota=$monto_solicitado/$cantidad_de_pagos;
 $cuotas=array($cantidad_de_pagos);
 for ($i=0;$i<$cantidad_de_pagos;$i++)
  $cuotas[$i]=$cuota;
}

//*****Paso los adelantos del estado por autorizar al estado**
//*****autorizados*******************************************
if ($_POST['pendiente']=="Autorizar")
 {$fecha=date("Y-m-d H:i:s");
  $db->StartTrans();
   $sql = "update cuenta set estado=3, comentarios='$observacion' where id_cuenta=".$_POST['idcuenta'];
   $result_consulta = sql($sql) or fin_pagina();
   $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
   $sql .= "values (3,'$_ses_user[name]','$fecha',".$_POST['idcuenta'].")";
   $resultconsulta = sql($sql) or fin_pagina(); 
  $db->CompleteTrans(); 
  $sql = "select id_legajo from cuenta where id_cuenta=".$_POST['idcuenta'];
  $result_consulta = sql($sql) or fin_pagina();
  $sql = "select apellido, nombre from legajos where id_legajo=".$result_consulta->fields['id_legajo'];
  $result_consulta = sql($sql) or fin_pagina();
  $nombre_para_mail='"'.$result_consulta->fields['apellido'].', '. $result_consulta->fields['nombre'].'"';
  $mensaje="Se le informa que el pedido de adelanto con núero de ID: ".$_POST['idcuenta']."realizado por $nombre_para_mail, ha sido Autorizado.";
  enviar_mail("juanmanuel@coradir.com.ar","Autorizacion Adelanto",$mensaje,' ',' ',' ',0);
  enviar_mail("corapi@coradir.com.ar","Autorizacion Adelanto",$mensaje,' ',' ',' ',0);
  enviar_mail("noelia@pcpower.com.ar","Autorizacion Adelanto",$mensaje,' ',' ',' ',0);
  header("location:adelanto_cuenta.php"); 
 }	
//***********************************************************


//*****Paso del estado Por Autorizar al estado pendiente******
//*****porque se rechazo el pedido****************************
if ($_POST['listo']=="Rechazar")
 {$fecha=date("Y-m-d H:i:s");
  $db->StartTrans();
   $sql = "update cuenta set estado=4, comentarios='$observacion' where id_cuenta=".$_POST['idcuenta'];
   $result_consulta = sql($sql) or fin_pagina();
   $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
   $sql .= "values (4,'$_ses_user[name]','$fecha',".$_POST['idcuenta'].")";
   $resultconsulta = sql($sql) or fin_pagina();
  $db->CompleteTrans();   
  header("location:adelanto_cuenta.php");
 }	
//************************************************************

//********Para volver a la pagina desde donde vine************
if ($_POST['volver']=="Volver")
 header("location:adelanto_cuenta.php"); 
//************************************************************


//****Para modificar la tabla en caso de que la pase para***** 
//****autorizar directamente**********************************
if ($_POST['listo']=="Listo para Autorizar")
{$fecha=date("Y-m-d H:i:s");
 if ($_POST['pagina']!="listado")
 {$db->StartTrans();
   $sql = "select nextval('cuenta_id_cuenta_seq') as id_cuenta";
   $id_rec = sql($sql) or fin_pagina();
   $sql = "insert into cuenta (id_cuenta,monto,estado,comentarios,id_legajo,";
   $sql .= "id_tipodecuenta,cantidad_pagos,fecha_pedido,pagos_restantes,monto_adeudado)";
   $sql .= "values (".$id_rec->fields['id_cuenta'].",$monto_solicitado,2,";
   $sql .= "'$observacion',$personal,1,$cantidad_de_pagos,'$fecha',$cantidad_de_pagos,$monto_solicitado)";
   $resultado_insertar=sql($sql) or fin_pagina();
   //ingreso datos en tabla log_cuenta
   $prueba=$id_rec->fields['id_cuenta'];
   $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
   $sql .= "values (2,'$_ses_user[name]','$fecha',".$id_rec->fields['id_cuenta'].")";
   $resultado_insertar = sql($sql) or fin_pagina();
   $linea=0;
   $i=0;
   while ($i<$cantidad_de_pagos)
   {$pos=$i+1;
    $sql = "insert into cuota (cuota, id_cuenta, monto_cuota, fecha_de_pago, estado)";
    $sql .= "values ($pos,".$id_rec->fields['id_cuenta'].",".$_POST['cuota_'.$i].",'".$_POST['fecha_larga_'.$i]."',1)" ;
    $resultado_insertar = sql($sql) or fin_pagina();
    $i++;
   } 
  $db->CompleteTrans();
 }
 else
 {$sql = "select * from cuenta where id_cuenta=".$id_cuenta;
  $result_consulta = sql($sql) or fin_pagina();
  if ($result_consulta->fields['monto']==$monto_solicitado and $result_consulta->fields['cantidad_pagos']==$cantidad_de_pagos)
  {$db->StartTrans();
    $sql = "update cuenta set estado=2, comentarios='$observacion' where id_cuenta=".$_POST['idcuenta'];
    $result_consulta = sql($sql) or fin_pagina(); 
    $i=0;
     while ($i<$cantidad_de_pagos)
      {$pos=$i+1;
       $sql = "update cuota set  monto_cuota=".$_POST['cuota_'.$i]." where id_cuota=".$_POST['id_'.$i];
       $result_consulta = sql($sql) or fin_pagina();
       $i++;
      } 
    $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
    $sql .= "values (2,'$_ses_user[name]','$fecha',".$_POST['idcuenta'].")";
    $resultconsulta = sql($sql) or fin_pagina();  
   $db->CompleteTrans(); 
  }
  else 
  {$sql = "delete from cuota where id_cuenta=$id_cuenta";
   $result_consulta = sql($sql) or fin_pagina();
   $db->StartTrans();
    $sql = "update cuenta set estado=2, monto=$monto_solicitado, cantidad_pagos=$cantidad_de_pagos, 
            pagos_restantes=$cantidad_de_pagos, monto_adeudado=$monto_solicitado, comentarios='$observacion' where id_cuenta=$id_cuenta";
    $result_consulta = sql($sql) or fin_pagina();
    $i=0;
    while ($i<$cantidad_de_pagos)
    {$pos=$i+1;
     $sql = "insert into cuota (cuota, id_cuenta, monto_cuota, fecha_de_pago, estado)";
     $sql .= "values ($pos,".$id_cuenta.",".$_POST['cuota_'.$i].",'".$_POST['fecha_larga_'.$i]."',1)" ;
     $resultado_insertar = sql($sql) or fin_pagina();
     $i++;
    } 
    $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
    $sql .= "values (2,'$_ses_user[name]','$fecha',".$id_cuenta.")";
    $resultconsulta = sql($sql) or fin_pagina();
   $db->CompleteTrans();
  }
 }	
 header("location:adelanto_cuenta.php"); 		
}	
//************************************************************


if($pendiente=="Guardar")
{$fecha=date("Y-m-d H:i:s");
 //ingreso datos en tabla cuenta
 if ($_POST['pagina']=="listado")
  {$sql = "select * from cuenta where id_cuenta=".$id_cuenta;
   $result_consulta = sql($sql) or fin_pagina();
   if ($result_consulta->fields['monto']==$monto_solicitado and $result_consulta->fields['cantidad_pagos']==$cantidad_de_pagos)
    {$db->StartTrans();
      $i=0;
      while ($i<$cantidad_de_pagos)
      {$pos=$i+1;
       $sql = "update cuota set  monto_cuota=".$_POST['cuota_'.$i]." where id_cuota=".$_POST['id_'.$i];
       $resultado_insertar = sql($sql) or fin_pagina();
       $i++;
      }
     $db->CompleteTrans(); 
    }
   else 
   {$sql = "delete from cuota where id_cuenta=$id_cuenta";
    $result_consulta = sql($sql) or fin_pagina();
    $db->StartTrans();
     $sql = "update cuenta set estado=1, monto=$monto_solicitado, cantidad_pagos=$cantidad_de_pagos,
             pagos_restantes=$cantidad_de_pagos, monto_adeudado=$monto_solicitado, comentarios='$observacion' where id_cuenta=$id_cuenta";
     $result_consulta = sql($sql) or fin_pagina();
     $i=0;
     while ($i<$cantidad_de_pagos)
     {$pos=$i+1;
      $sql = "insert into cuota (cuota, id_cuenta, monto_cuota, fecha_de_pago, estado)";
      $sql .= "values ($pos,".$id_cuenta.",".$_POST['cuota_'.$i].",'".$_POST['fecha_larga_'.$i]."',1)" ;
      $resultado_insertar = sql($sql) or fin_pagina();
      $i++;
     } 
     $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
     $sql .= "values (1,'$_ses_user[name]','$fecha',".$id_cuenta.")";
     $resultconsulta = sql($sql) or fin_pagina();
    $db->CompleteTrans();
   }	  
  }
 else 
 {$db->StartTrans();
   $sql = "select nextval('cuenta_id_cuenta_seq') as id_cuenta";
   $id_rec = sql($sql) or fin_pagina();
   $sql = "insert into cuenta (id_cuenta,monto,estado,comentarios,id_legajo,";
   $sql .= "id_tipodecuenta,cantidad_pagos,fecha_pedido,pagos_restantes,monto_adeudado)";
   $sql .= "values (".$id_rec->fields['id_cuenta'].",$monto_solicitado,1,";
   $sql .= "'$observacion',$personal,1,$cantidad_de_pagos,'$fecha',$cantidad_de_pagos,$monto_solicitado)";
   $resultado_insertar=sql($sql) or fin_pagina();
   $prueba=$id_rec->fields['id_cuenta'];
   $sql = "insert into log_cuenta (estado_cuenta,usuario,fecha,id_cuenta)";
   $sql .= "values (1,'$_ses_user[name]','$fecha',".$id_rec->fields['id_cuenta'].")";
   $resultado_insertar = sql($sql) or fin_pagina();
   $linea=0;
   $i=0;
   while ($i<$cantidad_de_pagos)
   {$pos=$i+1;
    $sql = "insert into cuota (cuota, id_cuenta, monto_cuota, fecha_de_pago, estado)";
    $sql .= "values ($pos,".$id_rec->fields['id_cuenta'].",".$_POST['cuota_'.$i].",'".$_POST['fecha_larga_'.$i]."',1)" ;
    $resultado_insertar = sql($sql) or fin_pagina();
    $i++;
   } 
 $db->CompleteTrans();
 }
 header("location:adelanto_cuenta.php");
}




echo $html_header;

?>
<!--***************Controles java script********* -->
<script>
function control_datos()
{if(document.all.personal.value=="-1")
 {alert('Debe seleccionar el empleado solicitante');
  return false;
 }
 //alert (document.all.monto_solicitado.value=="")
 if(document.all.monto_solicitado.value=="")
 {alert('Debe especificar el monto a pedir');
  return false;
 } 
 return true;
}

function control_suma()
{var total, orden,sentencia,solicitado,total1,total2;
 orden=0;
 total=0;
 if(document.all.personal.value=="-1")
 {alert('Debe seleccionar el empleado solicitante');
  return false;
 }
 if (document.all.cantidad_de_pagos.value!=document.all.control_cant_pagos.value)
  {alert('La cantidad de cuotas se modifico, debe crear un nuevo plan de pago');
   return false;
  }
 if(document.all.monto_solicitado.value=="")
 {alert('Debe especificar el monto a pedir');
  return false;
 } 
 while (orden<document.all.cantidad_de_pagos.value)
 {sentencia="parseFloat(document.all.cuota_"+orden+".value)";
  total+=eval(sentencia);
  orden++;
 }
 solicitado=parseFloat(document.all.monto_solicitado.value);
 total1=total+0.05;
 total2=total-0.05;
 if (solicitado<total2 || solicitado>total1)
 {alert('La suma de las cuotas no coincide con el monto solicitado, modifique los montos de las cuotas o presione "Armar Plan de Pago"');
  return false;
 } 
 return true;
}
</script>
<!--**********************************************-->
<?
$link=encode_link("nuevo_adelanto.php", array("id"=>$id));
?>
<form name="form_nuevo_adelanto" action="<?=$link?>" method="post">


<!--********************************************************************-->
<?
if($id_cuenta!="")
{$query="select * from log_cuenta where id_cuenta=".$id_cuenta;
 $log = sql($query) or fin_pagina();
?>
<center>
<div style='position:relative; width:90%; height:11%; overflow:auto;'>
<table width="100%" cellpadding="1" cellspacing="1" align="center">
<?
while(!$log->EOF)
{list($fecha,$hora)=split(" ",$log->fields['fecha']);
 if ($log->fields['estado_cuenta']==1) $estado=" paso a Pendiente";
 if ($log->fields['estado_cuenta']==2) $estado=" paso para Autorizar";
 if ($log->fields['estado_cuenta']==3) $estado=" Autorizo";
 if ($log->fields['estado_cuenta']==4) $estado=" Rechazo";
 if ($log->fields['estado_cuenta']==5) $estado=" Cancelo";
 ?>
 <tr id=ma>
  <td align="left">
   Fecha en que se<?=$estado?>: <?=fecha($fecha)?> <?=$hora?>
  </td>
  <td align="right">
   Usuario: <?=$log->fields['usuario']?>
  </td>
 </tr>
 <?
 $log->MoveNext();
}
}
?>
</table>
</div>
</center>
<!--********************************************************************-->

<br>
<input name="paso_id" type="hidden" value=<?=$parametros['id']?>>
<table width="97%" align="center" border="1" cellspacing="1" cellpadding="10">
<tr id=mo >
   <td bordercolor=<?=$bgcolor3?> align="center" colspan="3">
    <font size=3><b>Adelantos</b>
   </td>
 </tr>
 <?
  if ($parametros['cmd']=="at")
  {switch ($estado2)
   {case 1 : $estado_letra="Pendiente";
    break; 
    case 2 : $estado_letra="Por Autorizar";
    break;
    case 3 : $estado_letra="En Curso";
    break;
    case 4 : $estado_letra="Rechazada";
    break;
    case 5 : $estado_letra="Cancelada";
   }	     
 ?>
   <tr id=ma_sf>
    <td bordercolor=<?=$bgcolor3?> colspan="3">
    <b>Estado de la Cuenta: <font color="red"><?=$estado_letra?></font></b>
   </td>
   </tr> 
  <?
   }
  ?>  	
 <br>  
 <tr id=ma>
  <td align="left" colspan="3">
   <b>Solicitante: </b>
   <?
     $query="select id_legajo,apellido,nombre,dni from personal.legajos order by apellido,nombre";
     $resultado_empleados=sql($query) or fin_pagina();
     $cantidad=$resultado_empleados->RecordCount();
   ?>
   <select name="personal" <?if ($estado2==2 or $estado2==3) echo "disabled"?>>
   <option selected value=-1>Elija un Empleado</option>
   <?
     while(!$resultado_empleados->EOF)
     {if ($personal==$resultado_empleados->fields['id_legajo'])
       $selected="selected";
      else 
       $selected=" ";
             	
   ?>
       <option <?=$selected?> value="<?=$resultado_empleados->fields['id_legajo']?>" >
       <?=$resultado_empleados->fields['apellido'].", ".$resultado_empleados->fields['nombre']?></option>
   <?
     $resultado_empleados->MoveNext();
     }
     
   ?>
   </select>
  </td>
 </tr>
 <tr id=ma>
  <td>
   <b>Monto Solicitado: </b> <input name="monto_solicitado" type="text" size="10" maxlength="10" value="<?if ($monto_solicitado!=0) echo number_format($monto_solicitado,2,'.','')?>" <?if ($estado2==2 or $estado2==3) echo "readonly"?> onchange="this.value=this.value.replace(',','.')" >
  </td>
  <!--<td >
   <b>Interes: </b><input name="interes" type="text" size="3" maxlength="4" value="<?=$_POST['interes']?>">&nbsp<b>%</b>
  </td>-->
  <td>
   <b>Cantidad de Pagos: </b>
   <input name="control_cant_pagos" type="hidden" value="<?=$cantidad_de_pagos?>">
   <select name="cantidad_de_pagos" <?if ($estado2==2 or $estado2==3) echo "disabled"?>>
   <?$i=1?>
   <?for ($i=1;$i<=12;$i++)
    {if ($cantidad_de_pagos==$i)
         $selected="selected";
     else
         $selected=1;   
   ?>
     <option <?=$selected?> value="<?=$i?>" ><?=$i?> </option>
   <?  
    } 
   ?>
   </select>
  </td>
 </tr>
 <?
  if($plan_de_pago=="Armar Plan de Pago" or $pagina=="adelanto_cuenta")
  {$hora=date("H:i:s");
  ?> 
  <tr id=ma>
   <td colspan=3>
    <table width=90% border=1 align=center cellspacing=1>
     <tr id=mo>
     <!-- <td align=center bordercolor=$bgcolor3> <b>Número de Cuota</b></td>-->
      <td align=center bordercolor=$bgcolor3> <b>Monto a Pagar</b></td>
      <td align=center bordercolor=$bgcolor3> <b>Fecha de Pago</b></td>
      <?
       if ($estado2==3)
       {
      ?>
       	<td align=center bordercolor=$bgcolor3> <b>Estado</b></td>
       	<td align="center" bordercolor=$bgcolor3><b>Fecha en que se Pago</b></td>
       	<td align="center" bordercolor=$bgcolor3><b>Nro. Egreso</b></td>
       	<td align="center" bordercolor=$bgcolor3><b>Nro. Liquidacion</b></td>
       	
      <? 	
       }
      ?> 	
     </tr>
  <? 
   if ($plan_de_pago=="Armar Plan de Pago")
   {for ($i=0;$i<$cantidad_de_pagos;$i++)
    {$numcuota=$i+1;
  ?>  
     <tr bgcolor=#E0E0E0>
      <input name="pagina" type="hidden" value="<?=$_POST['pagina']?>">     
      <td align=center>$&nbsp<input name="cuota_<?=$i?>" type="text" size="7" value="<?=number_format($cuotas[$i],2,'.','')?>" onchange="this.value=this.value.replace(',','.')"></td>
      <input name="fecha_larga_<?=$i?>" type="hidden" value="<?=date("Y-m-d H:i:s",mktime(date("H"),date("i"),date("s"),date("m")+$numcuota,0,date("Y")))?>">
      <td align=center><input name="fecha_<?=$i?>" type="text" size="9" value="<?=date("m/Y", mktime(date("H"),date("i"),date("s"),date("m")+$numcuota,0,date("Y")))?>"></td>
      <input name="paso_id" type="hidden" value=<?=$id_cuenta?>>
     </tr> 
   <?  
    }//del while
   }//del if $plan_de_pago=="Armar Plan de Pago"
   if ($pagina=="adelanto_cuenta")
   {$sql = "select cuota.*, id_sueldo, id_ingreso_egreso, fecha_creacion 
            from cuota left join sueldos using(id_sueldo)  
            left join ingreso_egreso using(id_ingreso_egreso) where id_cuenta=$id_cuenta";
    $result_consulta = sql($sql) or fin_pagina(); 
    $i=0;
    while (!$result_consulta->EOF)
    {
   ?> 
     <tr bgcolor=#E0E0E0>
      <td align=center>$&nbsp<input name="cuota_<?=$i?>" type="text" size="7" value="<?=number_format($result_consulta->fields['monto_cuota'],2,'.','')?>" <?if ($estado2==2 or $estado2==3) echo "readonly"?> onchange="this.value=this.value.replace(',','.')"></td>
      <td align=center><input name="fecha2_<?=$i?>" type="text" size="9" value="<?=date_spa("m/Y" ,$result_consulta->fields['fecha_de_pago'])?>" <?if ($estado2==2 or $estado2==3) echo "readonly"?>></td>
      <?
       if ($estado2==3)
       {
      ?>
        <td align=center > <b><?if ($result_consulta->fields['estado']==1) echo "Adeudada"; else echo "Pagada" ?></b></td>
        <td align=center > <b><?if ($result_consulta->fields['estado']==1) echo "------"; else echo fecha($result_consulta->fields['fecha_creacion'])?></b></td>
        <td align=center > <b><?if ($result_consulta->fields['estado']==1) echo "------"; else echo $result_consulta->fields['id_ingreso_egreso'] ?></b></td>
        <td align=center > <b><?if ($result_consulta->fields['estado']==1) echo "------"; else echo $result_consulta->fields['id_sueldo'] ?></b></td>
       <?
       }
      ?>             	
      <input name="fecha_<?=$i?>" type="hidden" value="<?=$result_consulta->fields['fecha_de_pago']?>">
      <input name="id_<?=$i?>" type="hidden" value="<?=$result_consulta->fields['id_cuota']?>">
      <input name="pagina" type="hidden" value="listado">
      <input name="idcuenta" type="hidden" value=<?=$id_cuenta?>>
     </tr> 
    <?
     $result_consulta->MoveNext();
     $i++;
    }
   }   
    ?>
    </table>
   </td>
  </tr>   
  <?
  }//del if
  ?>
 <?
  if ($estado2!=2 and $estado2!=3 and $estado2!=5)   
  {?>
   <tr>
    <td align="center" colspan="3">
     <input name="plan_de_pago" type="submit" value="Armar Plan de Pago" onclick="return control_datos()">
    </td> 
   </tr>
  <? 
  }
  ?> 
</table>

<table width="97%" align="center" cellpadding="10"> 
 <tr>
  <td width="10%"  align="left" valign="top">
   <b>Observación:</b>
  </td> 
  <td>  
   <textarea name="observacion" rows="4" cols="70"><?=$observacion?></textarea>
  </td>
 </tr>
</table> 
<?if ($estado2!=2 )
   {$valor_boton1="Guardar";
    $valor_boton2="Listo para Autorizar";
   }
  else
  {$valor_boton1="Autorizar";
   $valor_boton2="Rechazar";
  } 
?> 	
<table width="50%" align="center">
<?
 if ($plan_de_pago=="Armar Plan de Pago" or $estado2!=0 and $estado2!=3 and $estado2!=5)
 {
?> 	
  <tr>
  <?
   if (permisos_check("inicio","autorizar_adelanto"))
   {
  ?> 	
   	<td align="center">
     <input name="pendiente" type="submit" value="<?=$valor_boton1?>" onclick="return control_suma() ">
    </td>
   <? 
   } 
   ?>
   <td align="center">
    <input name="listo" type="submit" value="<?=$valor_boton2?>" onclick="return control_suma()">
   </td>
<?
 }
?>    
  <td align="center">
   <input name="volver" type="submit" value="Volver" >
  </td>
 <?
  if ($estado2==3)
  {
 ?>
  <td align="center">
    <input name="guardar_comentario" type="submit" value="Guardar Comentario">
    <input name="pagina_vuelve" type="hidden" value="adelanto_cuenta">
    <input name="paso_id" type="hidden" value=<?=$id_cuenta?>>
   </td>
  <?
  }
  ?> 
 </tr>  
</table>

</form>
<?=fin_pagina(false);?>



