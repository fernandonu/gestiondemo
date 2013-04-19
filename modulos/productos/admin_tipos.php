<?PHP

include("../../config.php");

$state=$_POST['tipo_prov'];
$msg="";
if($_POST['nuevo_t']=="si")
{ 
  //inicio de transaccion
  $db->StartTrans();
  //chequeamos que la letra ingresada no este en uso
  $query="select tipo from tipos_prov where tipo='".$_POST['letra']."'";
  $result=$db->Execute($query) or die ($db->ErrorMsg().$query);
  if($result->RecordCount()==0)
  { $query="insert into tipos_prov (tipo,descripcion) values('".$_POST['letra']."','".$_POST['descripcion']."')";
   $db->Execute($query) or die ($db->ErrorMsg().$query);      
   $msg="<center><b>EL TIPO SE INSERTO EXITOSAMENTE</b></center>";      
  } 
  else
   $msg="<center><b>NO SE PUDO INSERTAR. LA LETRA SELECCIONADA PARA ESTE TIPO YA ESTA EN USO</b></center>"; 
  //cierra transaccion
  $db->CompleteTrans();
  $_POST['letra_h']=$_POST['letra'];
}
elseif($_POST['guardar']=="Guardar Cambios")
{ 
  //inicio de transaccion
  $db->StartTrans();
  $query="update tipos_prov set descripcion='".$_POST['descripcion']."' where tipo='".$_POST['tipo_prov']."'";
  if($db->Execute($query))
   $msg="<center><b>EL TIPO SE ACTUALIZO EXITOSAMENTE</b></center>";
  else 
   $msg="<center><b>NO SE PUDO ACTUALIZAR</b></center>"; 
  //cierra transaccion
  $db->CompleteTrans();
  
}

if($_POST['eliminar']=="Eliminar Tipo")
{ 
  //inicio de transaccion
  $db->StartTrans();
  //chequeamos que nadie este usando ese tipo en la tabla prov_t
  $query="select count(*) as total from prov_t where tipo='".$_POST['tipo_prov']."'";
  $result=$db->Execute($query) or die ($db->ErrorMsg().$query);
  if($result->fields['total']==0)
  {$query="delete from tipos_prov where tipo='".$_POST['tipo_prov']."'";
   $db->Execute($query) or die ($db->ErrorMsg().$query);
   $msg="<center><b>EL TIPO SE ELIMINO EXITOSAMENTE</b></center>";      
  }
  else 
   $msg="<center><b>NO SE PUDO ELIMINAR. HAY PROVEEDORES CARGADOS QUE SON DE ESTE TIPO</b></center>"; 
  //cierra transaccion
  $db->CompleteTrans();
  $_POST['letra_h']="";
}

?>
<html>
<head>
<style type="text/css">
.boton{
        font-size:10px;
        font-family:Verdana,Helvetica;
        font-weight:bold;
        color:white;
        background:#638cb9;
        border:0px;
        width:160px;
        height:19px;
       }
</style>

<title>Administracion de tipos de proveedores</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#E0E0E0"  text="#000000">
<?="<font size=-1>".$msg."</font>";?>
<form name="form1" action="admin_tipos.php" method="post">
<table border='0' width='100%'>
<tr>
<td align="center">
Seleccione el tipo de proveedor:
<?
//traemos los tipos de proveedores para generar el select de tipos de proveedores
    $query="select * from tipos_prov order by descripcion";
    $tipos_prov=$db->Execute($query) or die ($db->ErrorMsg());     
?>
	<select name="tipo_prov" value="est" onchange="document.all.guardar.disabled=false;document.all.letra.value=this.value;document.all.letra_h.value=this.value;document.all.descripcion.value=this.options[this.selectedIndex].text">
        	       
<?
	while(!$tipos_prov->EOF)
	{ if($state==$tipos_prov->fields['tipo'])
	   $selected='selected';
	  else 
	   $selected='';  
	?>          
	        <option value='<?echo $tipos_prov->fields['tipo']; echo "' ".$selected;?>><? echo $tipos_prov->fields['descripcion']?></option>
	 <?
     $tipos_prov->MoveNext();
	}   
	//seteamos las variables para llenar los campos "descripcion" y "letra"
	$tipos_prov->Move(0);
	if($_POST['letra_h']=="")
    { $desc=$tipos_prov->fields['descripcion'];
      $let=$tipos_prov->fields['tipo'];
    }
    else 
    {$desc=$_POST['descripcion'];
     $let=$_POST['letra_h'];
    }
	
    ?>   
         </select>
</td>
</tr>
<tr></tr><tr></tr><tr></tr>
<tr>
<td align="center">
<input type="hidden" name="nuevo_t" value="no">
<input type="hidden" name="letra_h" value="<?=$let?>">
<input type="button" name="nuevo" value="Nuevo Tipo" onclick='document.all.tipo_prov.disabled=true;document.all.eliminar.disabled=true;document.all.descripcion.value="";document.all.letra.disabled=false;document.all.letra.value="";document.all.nuevo_t.value="si";'>&nbsp;
<input type="submit" name="eliminar" value="Eliminar Tipo"onclick="return confirm('Esta seguro que desea eliminar el tipo seleccionado');">
</td>
</tr>
</table>
<hr>
<br>
<table border='0' width='100%' id=ma>
<tr id=mo> 
<td align="center" colspan="2">
<b>Datos del Tipo seleccionado</b>
</td>   
</tr>    
<tr height="20" valign="middle">
<td >
Nombre del tipo de proveedor a guardar
</td>
<td align="left">
Letra Asociada
</td>
</tr> 
<tr height="25" valign="bottom">
<td>
<input type="text" name="descripcion" value="<?echo $desc;?>" size="40">
</td>
<td align="left">
<input type="text" name="letra" value="<?echo $let;?>" size="3" disabled>
</td>
</tr>
<tr height="30" valign="bottom">
<td align="center" colspan="2">
<input type="submit" name="guardar" value="Guardar Cambios" onclick="if((document.all.descripcion.value=='')||(document.all.letra.value=='')){alert('Debe ingresar una descripcion y una letra para poder guardar cambios');return false;}">
<input type="button" name="cerrar" value="Cerrar" onclick="window.opener.location.reload();window.close()">
</td>
</tr>
</table>
</form>

</body>
</html>
