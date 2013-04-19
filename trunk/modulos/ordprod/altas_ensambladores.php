<?php
include("../../config.php");

switch ($_POST['caso'])
{
 case "Modificar Ensamblador":$sql="update ensamblador set nombre='".$_POST['nombre_ensamblador']."', tel='".$_POST['tel_ensamblador']."', direccion='".$_POST['direccion_ensamblador']."', email='".$_POST['email_ensamblador']."', comentarios='".$_POST['comentarios_ensamblador']."', letra='".$_POST['letra_ensamblador']."' where id_ensamblador=".$_POST['select_ensamblador'];
                              $db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                              break;
 case "Eliminar Ensamblador" :$sql="delete from ensamblador where id_ensamblador=".$_POST['select_ensamblador'];
                              $db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                              break;
 case "Insertar Ensamblador" :$sql="insert into ensamblador (nombre,tel,direccion,email,comentarios,letra) values ('".$_POST['nombre_ensamblador']."','".$_POST['tel_ensamblador']."','".$_POST['direccion_ensamblador']."','".$_POST['email_ensamblador']."','".$_POST['comentarios_ensamblador']."','".$_POST['letra_ensamblador']."');";
                              $db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                              break;
}

?>

<html>
<head>
<title>Altas de Ensambladores</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<?php
include("../ayuda/ayudas.php");
?>
<script language="javascript">
<?php
$sql="select * from ensamblador order by nombre";
$resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
while (!$resultado->EOF)
{
?>
var e<?php echo $resultado->fields['id_ensamblador']; ?>=new Array(7);
e<?php echo $resultado->fields['id_ensamblador']; ?>[0]='<?php echo $resultado->fields['id_ensamblador']; ?>';
e<?php echo $resultado->fields['id_ensamblador']; ?>[1]='<?php echo $resultado->fields['nombre']; ?>';
e<?php echo $resultado->fields['id_ensamblador']; ?>[2]='<?php echo $resultado->fields['direccion']; ?>';
e<?php echo $resultado->fields['id_ensamblador']; ?>[3]='<?php echo $resultado->fields['tel']; ?>';
e<?php echo $resultado->fields['id_ensamblador']; ?>[4]='<?php echo $resultado->fields['email']; ?>';
e<?php echo $resultado->fields['id_ensamblador']; ?>[5]='<?php echo $resultado->fields['comentarios']; ?>';
e<?php echo $resultado->fields['id_ensamblador']; ?>[6]='<?php echo $resultado->fields['letra']; ?>';
<?php
$resultado->MoveNext();
}
?>
function cambiar(valor)
{if (valor=="Editar Ensamblador")
 {document.all.select_ensamblador.disabled=false;
 document.all.boton[2].disabled=false;
 document.all.boton[1].disabled=false;
  switch(document.all.select_ensamblador.options[document.all.select_ensamblador.selectedIndex].value)
  {
<?php
  $resultado->Move(0);
  while (!$resultado->EOF) 
  {
?>
   case "<?php echo $resultado->fields['id_ensamblador']; ?>":{document.all.nombre_ensamblador.value='<?php echo $resultado->fields['nombre']; ?>';
  															   document.all.direccion_ensamblador.value='<?php echo $resultado->fields['direccion']; ?>';
															   document.all.tel_ensamblador.value='<?php echo $resultado->fields['tel']; ?>';
															   document.all.email_ensamblador.value='<?php echo $resultado->fields['email']; ?>';
															   document.all.comentarios_ensamblador.value='<?php echo $resultado->fields['comentarios']; ?>';
															   document.all.letra_ensamblador.value='<?php echo $resultado->fields['letra']; ?>';
													           document.all.nombre_ensamblador.style.position='relative'; 
                                                               document.all.nombre_ensamblador.style.visibility='visible';
                                                               document.all.direccion_ensamblador.style.position='relative'; 
                                                               document.all.direccion_ensamblador.style.visibility='visible';
                                                               document.all.tel_ensamblador.style.position='relative'; 
                                                               document.all.tel_ensamblador.style.visibility='visible';
                                                               document.all.email_ensamblador.style.position='relative'; 
                                                               document.all.email_ensamblador.style.visibility='visible';
                                                               document.all.comentarios_ensamblador.style.position='relative'; 
                                                               document.all.comentarios_ensamblador.style.visibility='visible';
                                                               document.all.letra_ensamblador.style.position='relative'; 
                                                               document.all.letra_ensamblador.style.visibility='visible';
                                                               document.getElementById('n').style.position='relative';
                                                               document.getElementById('n').style.visibility='visible';
                                                               document.getElementById('l').style.position='relative';
                                                               document.getElementById('l').style.visibility='visible';
                                                               document.getElementById('d').style.position='relative';
                                                               document.getElementById('d').style.visibility='visible';
                                                               document.getElementById('e').style.position='relative';
                                                               document.getElementById('e').style.visibility='visible';
                                                               document.getElementById('c').style.position='relative';
                                                               document.getElementById('c').style.visibility='visible';
                                                               document.getElementById('t').style.position='relative';
                                                               document.getElementById('t').style.visibility='visible';
                                                               //document.all.boton[3].style.position='relative';
                                                               //document.all.boton[3].style.visibility='visible';
                                                               //document.all.boton[3].value="Modificar Ensamblador";
                                                               break;
                                                               }//fin case
<?php
$resultado->Movenext();
  }
?>  
  }//fin switch
 }//fin if (valor=="Editar Ensamblador")
if (valor=="Nuevo Ensamblador")
 {document.all.nombre_ensamblador.style.position='relative'; 
  document.all.nombre_ensamblador.style.visibility='visible';
  document.all.direccion_ensamblador.style.position='relative'; 
  document.all.direccion_ensamblador.style.visibility='visible';
  document.all.tel_ensamblador.style.position='relative'; 
  document.all.tel_ensamblador.style.visibility='visible';
  document.all.email_ensamblador.style.position='relative'; 
  document.all.email_ensamblador.style.visibility='visible';
  document.all.comentarios_ensamblador.style.position='relative'; 
  document.all.comentarios_ensamblador.style.visibility='visible';
  document.all.letra_ensamblador.style.position='relative'; 
  document.all.letra_ensamblador.style.visibility='visible';
  document.getElementById('n').style.position='relative';
  document.getElementById('n').style.visibility='visible';
  document.getElementById('l').style.position='relative';
  document.getElementById('l').style.visibility='visible';
  document.getElementById('d').style.position='relative';
  document.getElementById('d').style.visibility='visible';
  document.getElementById('e').style.position='relative';
  document.getElementById('e').style.visibility='visible';
  document.getElementById('c').style.position='relative';
  document.getElementById('c').style.visibility='visible';
  document.getElementById('t').style.position='relative';
  document.getElementById('t').style.visibility='visible';
  document.all.boton[1].style.position='relative';
  document.all.boton[1].disabled=false;
  document.all.nombre_ensamblador.value="";
  document.all.direccion_ensamblador.value="";
  document.all.tel_ensamblador.value="";
  document.all.email_ensamblador.value="";
  document.all.comentarios_ensamblador.value="";
  document.all.letra_ensamblador.value="";
  }//fin if (valor=="Nuevo Ensamblador")
}

function alerta(str)
{
 var valor;
 valor=confirm(str);
 if (valor)
  return true;
 else
  return false;
}

function caso1(valor)
{if (valor=="modificar")
  window.document.all.caso.value="Modificar Ensamblador";
 if (valor=="insertar")
  window.document.all.caso.value="Insertar Ensamblador";
 if (valor=="eliminar")
  window.document.all.caso.value="Eliminar Ensamblador";
}

</script>
</head>
<?=$html_header;?>
<form name="form" action="altas_ensambladores.php" method="POST">
<input type="hidden" name="caso" value="">

<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/ordprod/alta_ensam.htm" ?>', 'ALTA DE NUEVOS ENSAMBLADORES')" >
    </div>
<br>
<center>
<br>
<p><font size="5">Altas de Ensambladores</p>
</center>
<hr>
<table cellspacing="3" width="100%" align="center">
<tr id=mo>
<td width="50%" align="center"><strong>INFORMACION DEL CLIENTE</strong>
</td>
<td align="center" ><strong>CLIENTES CARGADOS</strong> </td>
</tr>
<tr>
<td>
<table>
 <tr>
 <td>
 <b>
 <p id=n>Nombre</p>
 </td>
 <td>
 <input type="text" name="nombre_ensamblador" value="<?php if ($_POST['caso']!="Eliminar Ensamblador") echo $_POST['nombre_ensamblador']; ?>">
 </td>
 </tr>
 <tr>
 <td>
 <b>
 <p id=l>Letra</p>
 </td>
 <td>
 <input type="text" name="letra_ensamblador" value="<?php if ($_POST['caso']!="Eliminar Ensamblador") echo $_POST['letra_ensamblador']; ?>">
 </td>
 </tr>
 <tr>
 <td>
 <b>
 <p id=d>Direccion</p>
 </td>
 <td>
 <input type="text" name="direccion_ensamblador" value="<?php if ($_POST['caso']!="Eliminar Ensamblador") echo $_POST['direccion_ensamblador']; ?>">
 </td>
 </tr>
 <tr>
 <td>
 <b>
 <p id=t>Telefono</p>
 </td>
 <td>
 <input type="text" name="tel_ensamblador" value="<?php if ($_POST['caso']!="Eliminar Ensamblador") echo $_POST['tel_ensamblador']; ?>">
 </td>
 </tr>
 <tr>
 <td>
 <b>
 <p id=e>E-Mail</p>
 </td>
 <td>
 <input type="text" name="email_ensamblador" value="<?php if ($_POST['caso']!="Eliminar Ensamblador") echo $_POST['email_ensamblador']; ?>">
 </td>
 </tr>
 <tr>
 <td>
 <b>
 <p id=c>Comentario</p>
 </td>
 <td>
 <input type="text" name="comentarios_ensamblador" value="<?php if ($_POST['caso']!="Eliminar Ensamblador") echo $_POST['comentarios_ensamblador']; ?>">
 </td>
 </tr>
 </table>
</td>
<td valign="top">
<table>
 <tr>
 <td>
 <input type="button" name="boton" value="Nuevo Ensamblador" onclick="cambiar('Nuevo Ensamblador'); caso1('insertar');" style="width=100%">
 </td>
 <td>
 </td>
 </tr>
 <tr> 
 <td>
 <select name="select_ensamblador" size="<?php echo $resultado->RecordCount(); ?>" onchange="cambiar('Editar Ensamblador'); caso1('modificar');">
 <?php
 $resultado->Move(0);
 while (!$resultado->EOF)
 {
 ?>
 <option value="<?php echo $resultado->fields['id_ensamblador']; ?>" <? if (($_POST['select_ensamblador']==$resultado->fields['id_ensamblador']) && ($_POST['caso']!="Insertar Ensamblador")) echo "selected"; ?>><?php echo $resultado->fields['nombre']; ?></option>
 <?php
 $resultado->MoveNext();
 }
 ?>
 </select>
 </td>
 <td>
 </td>
 </tr>
 <tr>
 <td>
 </td>
 <td>
 </td>
 </tr>
</table>
</td>
</tr>
</table>
<br>
<center>
<input type="submit" name="boton" value="Guardar" disabled>
<input type="submit" name="boton" value="Eliminar" onclick="caso1('eliminar'); return alerta('Desea usted eliminar el ensamblador '+document.all.select_ensamblador.options[document.all.select_ensamblador.selectedIndex].text);" disabled>
</center>
</form>
</body>
</html>