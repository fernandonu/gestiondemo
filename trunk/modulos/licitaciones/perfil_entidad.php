<?php
/*
$Author: Fernando

modificado por
$Author: Fernando
$Revision: 1.6 $
$Date: 2003/11/19 14:11:34 $
*/

include("../../config.php");

$id_entidad=$parametros["id_entidad"];
extract($_POST,EXTR_SKIP);
$do=0;
if (($boton=="Guardar"))
    $do=1;
if ($do==1)
{
 $query="update entidad set nombre='$nombre',direccion='$direccion',codigo_postal='$cod_pos',localidad='$localidad',id_distrito=$distrito,
  telefono='$telefono',fax='$fax',mail='$mail',observaciones='$observaciones',perfil='$perfil'
  WHERE id_entidad=$id_entidad";
//echo $query;
 if ($db->Execute($query) or die($db->ErrorMsg()."<br>".$query))
     $informar="<center><b>La entidad \"$nombre\" fue actualizado con éxito</b></center>";
 else
     $informar="<center><b>La entidad \"$nombre\" no se pudo actualizar</b></center>";

}
//datos por parametros
/*$id_entidad=$parametros['id_entidad'];
$pagina=$parametros['pagina'];
*/

$link=encode_link("perfil_entidad.php",array("id_entidad"=> $id_entidad,"pagina"=>$pagina));
?>
<center>

<html>
<head>
<title>Entidades</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
.tablaEnc {
    background-color: #006699;
    color: #c0c6c9;
    font-weight: bold;
}
-->
</style>

</head>
<body bgcolor=#E0E0E0 leftmargin=4>
<SCRIPT LANGUAGE="JavaScript">
<?php
//trae los entidads junto con su informacion y los deja en la variable
//con nombre "entidad" concatenado con el id del entidad

$sql="select * from entidad where id_entidad=$id_entidad";
$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>");
?>

//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
    if (document.all.distrito.value=='none')
    {
     alert ('Debe seleccionar un distrito para la entidad');
     return false;
    }
    if (document.all.nombre.value=='' || document.all.nombre.value==' ')
    {
     alert ('Debe completar el nombre de la entidad');
     return false;
    }
    if(document.all.nombre.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
        return false;
    }
    if(document.all.localidad.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Localidad');
        return false;
    }
    if(document.all.direccion.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
        return false;
    }
    if(document.all.mail.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo E-mail');
        return false;
    }
    if(document.all.telefono.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono del contacto');
        return false;
    }
    if(document.all.observaciones.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Observaciones');
        return false;
    }
    if(document.all.cod_pos.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Código Postal');
        return false;
    }
    if(document.all.fax.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Fax');
        return false;
    }
    if(document.all.perfil.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Perfil');
        return false;
    }

} //fin de la funcion control_datos()

//funcion para limpiar el formulario
function limpiar()
{document.all.nombre.value='';
 document.all.direccion.value='';
 document.all.cod_pos.value='';
 document.all.localidad.value='';
 document.all.telefono.value='';
 document.all.mail.value='';
 document.all.fax.value='';
 document.all.observaciones.value='';
 document.all.distrito.value='none';

 document.all.editar.value='';
}


</SCRIPT>


<?=$informar;?>

<form name="form" method="post" action="<?echo $link?>">
  <TABLE width="50%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td  align="center"><strong>INFORMACION DE LA ENTIDAD</strong>
      </td>
      <td  align="center"><strong>PERFIL</strong>
      </td>
    </tr>
    <tr>
     <td>
      <TABLE>
       <TR>
       <td align="center">
          <tr>
            <td width="20%" nowrap><strong>Nombre</strong></td>
            <td width="70%" nowrap> <input name="nombre" type="text" id="nombre" size="48" value="<? echo $resultado->fields['nombre'];?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Dirección</strong></td>
            <td nowrap> <input name="direccion" type="text" id="direccion" size="48" value="<? echo $resultado->fields['direccion'];?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Teléfono</strong></td>
            <td nowrap> <input name="telefono" type="text" id="telefono" size="25" value="<? echo $resultado->fields['telefono'];?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Fax</strong></td>
            <td nowrap> <input name="fax" type="text" id="fax" size="25" value="<? echo $resultado->fields['fax'];?>"></td>
          </tr>
          <tr>
            <td  nowrap> <strong>Código Postal</strong></td>
            <td nowrap> <input name="cod_pos" type="text" id="cod_pos"   size="25" value="<? echo $resultado->fields['codigo_postal'];?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Localidad</strong></td>
            <td nowrap> <input name="localidad" type="text" id="localidad" size="25" value="<? echo $resultado->fields['localidad'];?>" ></td>
          </tr>
          <tr>
            <td  nowrap><strong>Distrito</strong></td>
            <td>

            <SELECT name="distrito">
            <option value='none'>Seleccione un distrito</option>
            <?//traemos los distritos
             $query="select nombre,id_distrito from distrito order by nombre";
             $district=$db->Execute($query) or die($db->ErrorMsg());
             while (!$district->EOF)
             {?>
              <option value='<?=$district->fields['id_distrito']?>' <?if ($resultado->fields['id_distrito']==$district->fields['id_distrito']) echo "selected";  ?> ><?=$district->fields['nombre']?></option>
             <?
             $district->MoveNext();
             }?>
            </select>

            </td>


          </tr>
          <tr>
            <td  nowrap><strong>E-mail</strong></td>
            <td nowrap> <input name="mail" type="text" id="mail" size="25" value="<? echo $resultado->fields['mail'];?>"></td>
          </tr>
          <tr>
            <td colspan="2" align="center" nowrap><br><strong>Observaciones</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap align="center">
                <textarea name="observaciones" cols="62" rows="5" wrap="VIRTUAL" id="observaciones"><? echo $resultado->fields['observaciones'];?></textarea>
            </td>
          </tr>
         </table> 
       </TD>
            <TD colspan="2" nowrap align="center">
                <textarea name="perfil" cols="40" rows="23" wrap="VIRTUAL" id="perfil"><? echo $resultado->fields['perfil'];?></textarea>
            </TD>
      </TR>
         
  </TABLE>
  <br>
  <table>
         <tr>
          <td width="50%" align="center"> <input type="submit" name="boton" value="Guardar" style="width:100" onClick="return control_datos()">
          </TD>
          <td width="50%" align="center"> <input type="button" name="boton" value="Salir" style="width:100" onClick="window.close()">
          </TD>
         </tr>
 </table>
<br>

</center>


<INPUT type="hidden" name="entidad">

</form>
</body>
</html>