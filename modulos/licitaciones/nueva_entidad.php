<?php
/*



ESTE ARCHIVO NO SE USA MAS!!!!


modificado por
$Author: marco_canderle
$Revision: 1.16 $
$Date: 2004/12/20 19:03:08 $
*/

include("../../config.php");
//include("./config_local.php");

extract($_POST,EXTR_SKIP);
$do=0;
if (($boton=="Guardar")&&($editar!="editar"))
	$do=1;
/*elseif ($boton=="Eliminar")
	$do=2;*/
elseif($editar=="editar")
    $do=3;

if ($do==1)
{
  //esta parte esta para sacar los caracteres invalidos

   $nombre=ereg_replace("'","\'",$nombre);
   $direccion=ereg_replace("'","\'",$direccion);
   $cod_pos=ereg_replace("'","\'",$cod_pos);
   $districto=ereg_replace("'","\'",$districto);
   $telefono=ereg_replace("'","\'",$telefono);
   $fax=ereg_replace("'","\'",$fax);
   $mail=ereg_replace("'","\'",$mail);
   $observaciones=ereg_replace("'","\'",$observaciones);
   $perfil=ereg_replace("'","\'",$perfil);

 ///fin de control para caracteres invalidos
   $campos="(nombre,direccion,codigo_postal,localidad,telefono,mail,fax,observaciones,id_distrito,perfil,id_tipo_entidad)";
   $query_insert="INSERT INTO entidad $campos VALUES ".
	"('$nombre','$direccion','$cod_pos','$localidad','$telefono','$mail','$fax','$observaciones',$distrito,'$perfil',$tipo_entidad)";
	if ($db->Execute($query_insert) or die ($query_insert."<br>".$db->ErrorMsg()))
	 $informar="<center><b>La entidad \"$nombre\" fue añadida con exito</b></center>";
	else
	 $informar="<center><b>La entidad \"$nombre\" no se pudo agregar</b></center>";
}
/*elseif ($do==2)
{
	$q="delete from entidad where id_entidad=$select_entidad";
	if ($db->Execute($q))
	 $informar="<center><b>La entidad \"$nombre\" fue eliminada con exito</b></center>";
	else
	 $informar="<center><b>El entidad \"$nombre\" no se pudo eliminar</b></center>";
}*/
elseif ($do==3){
  //esta parte esta para sacar los caracteres invalidos
   $nombre=ereg_replace("'","\'",$nombre);
   $direccion=ereg_replace("'","\'",$direccion);
   $cod_pos=ereg_replace("'","\'",$cod_pos);
   $districto=ereg_replace("'","\'",$districto);
   $telefono=ereg_replace("'","\'",$telefono);
   $fax=ereg_replace("'","\'",$fax);
   $mail=ereg_replace("'","\'",$mail);
   $observaciones=ereg_replace("'","\'",$observaciones);
   $perfil=ereg_replace("'","\'",$perfil);
 ///fin de control para caracteres invalidos
   $query="update entidad set nombre='$nombre',direccion='$direccion',codigo_postal='$cod_pos',localidad='$localidad',id_distrito=$distrito,
  telefono='$telefono',fax='$fax',mail='$mail',observaciones='$observaciones',perfil='$perfil',id_tipo_entidad=$tipo_entidad
  WHERE id_entidad=$select_entidad";
  //echo $query;
   if ($db->Execute($query) or die($query."<br>".$db->ErrorMsg()))
	 $informar="<center><b>La entidad \"$nombre\" fue actualizado con exito</b></center>";
   else
	 $informar="<center><b>La entidad \"$nombre\" no se pudo actualizar</b></center>";
  }

//datos por parametros
/*$id_entidad=$parametros['id_entidad'];
$pagina=$parametros['pagina'];
*/
$link=encode_link("nueva_entidad.php",array("id_entidad"=> $id_entidad,"pagina"=>$pagina));
?>
<center>

<html>
<head>
<title>Administración de entidades</title>
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
<?
//include("../ayuda/ayudas.php");
?>
</head>
<body bgcolor=#E0E0E0 leftmargin=4>
<SCRIPT LANGUAGE="JavaScript">
<?php

//trae los entidads junto con su informacion y los deja en la variable
//con nombre "entidad" concatenado con el id del entidad

$datos_entidad=$db->Execute("select * from entidad order by nombre") or die($db->ErrorMsg()."<br>");

while (!$datos_entidad->EOF)
{
?>
var entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>=new Array();
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["nombre"]="<?php if($datos_entidad->fields["nombre"]){echo $datos_entidad->fields["nombre"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["id_tipo_entidad"]="<?php if($datos_entidad->fields["id_tipo_entidad"]){echo $datos_entidad->fields["id_tipo_entidad"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["direccion"]="<?php if($datos_entidad->fields["direccion"]){echo $datos_entidad->fields["direccion"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["cod_pos"]="<?php if($datos_entidad->fields["codigo_postal"]){echo $datos_entidad->fields["codigo_postal"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["localidad"]="<?php if($datos_entidad->fields["localidad"]){echo $datos_entidad->fields["localidad"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["telefono"]="<?php if($datos_entidad->fields["telefono"]){echo $datos_entidad->fields["telefono"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["fax"]="<?php if($datos_entidad->fields["fax"]){echo $datos_entidad->fields["fax"];}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["mail"]="<?php if($datos_entidad->fields["mail"]){echo $datos_entidad->fields["mail"];}else echo "null";?>";
<?
$observaciones=$datos_entidad->fields["observaciones"];
$observaciones=ereg_replace("\r\n","<br>",$observaciones);
?>
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["observaciones"]="<?php if($datos_entidad->fields["observaciones"]){echo $observaciones;}else echo "null";?>";
<?
$perfil=$datos_entidad->fields["perfil"];
$perfil=ereg_replace("\r\n","<br>",$perfil);
?>
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["perfil"]="<?php if($datos_entidad->fields["perfil"]){echo $perfil;}else echo "null";?>";
entidad_<?php echo $datos_entidad->fields["id_entidad"]; ?>["id_distrito"]="<?php if($datos_entidad->fields["id_distrito"]){echo $datos_entidad->fields["id_distrito"];}else echo "null";?>";

<?
$datos_entidad->MoveNext();
}
?>

function set_datos()
{
    switch(document.all.select_entidad.options[document.all.select_entidad.selectedIndex].value)
    {<?PHP
     $datos_entidad->Move(0);
     while(!$datos_entidad->EOF)
     {?>
      case '<?echo $datos_entidad->fields["id_entidad"]?>': info=entidad_<?echo $datos_entidad->fields["id_entidad"];?>;break;
     <?
      $datos_entidad->MoveNext();
     }
     ?>
    }
    if(info["nombre"]!="null")
     document.all.nombre.value=info["nombre"];
    else
     document.all.nombre.value="";
    if(info["direccion"]!="null")
     document.all.direccion.value=info["direccion"];
    else
     document.all.direccion.value="";
    if(info["cod_pos"]!="null")
     document.all.cod_pos.value=info["cod_pos"];
    else
     document.all.cod_pos.value="";
    if(info["localidad"]!="null")
     document.all.localidad.value=info["localidad"];
    else
     document.all.localidad.value="";

    if(info["telefono"]!="null")
     document.all.telefono.value=info["telefono"];
    else
     document.all.telefono.value="";

    if(info["fax"]!="null")
     document.all.fax.value=info["fax"];
    else
     document.all.fax.value="";

   if(info["mail"]!="null")
     document.all.mail.value=info["mail"];
    else
     document.all.mail.value="";
    if(info["observaciones"]!="null"){
    document.all.observaciones.value=info["observaciones"];
    document.all.observaciones.value=document.all.observaciones.value.replace("<br>","\n");
    }
    else
     document.all.observaciones.value="";
    if(info["perfil"]!="null")
    {document.all.perfil.value=info["perfil"];
     document.all.perfil.value=document.all.perfil.value.replace("<br>","\n");
    }
    else
     document.all.perfil.value="";
    if(info["id_distrito"]!="null")
     document.all.distrito.value=info["id_distrito"];
    else
     document.all.distrito.value="none";
    if(info["id_tipo_entidad"]!="null")
     document.all.tipo_entidad.value=info["id_tipo_entidad"];
    else
     document.all.tipo_entidad.value="none";
   document.all.editar.value="editar";
} //fin de la funcion set_datos()



//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
	if (document.all.distrito.value=='none')
    {
	 alert ('Debe seleccionar un distrito para la entidad');
	 return false;
    }
	if (document.all.tipo_entidad.value=='none')
    {
	 alert ('Debe seleccionar un tipo de entidad');
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
 document.all.tipo_entidad.value='none';
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
<script language="JavaScript1.2">
//funciones para busqueda abrebiada utilizando teclas en la lista que muestra las entidades.
var digitos=15 //cantidad de digitos buscados
var puntero=0
var buffer=new Array(digitos) //declaración del array Buffer
var cadena=""

function buscar_op(obj){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13){
       borrar_buffer();
      // if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       for (var opcombo=0;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}

function borrar_buffer(){
   //inicializa la cadena buscada
    cadena="";
    puntero=0;
}
</script>



<?=$informar;?>
<?$link=encode_link('nueva_entidad.php',array("id_entidad"=> $id_entidad,'pagina'=>$parametros['pagina']))?>
<form name="form" method="post" action="nueva_entidad.php<?//=$link?>">
<!--
<div align='right'>
<a href="#" onClick="abrir_ventana('<?php /*echo "$html_root/modulos/ayuda/licitaciones/ayuda_competidor.htm" */ ?>', 'CARGAR COMPETIDOR')"> Ayuda </a>
</div>
-->

<input type="hidden" name="editar" value="<?if($do!=0 && $do!=2)echo "editar"?>">
  <TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td width="40%" align="center">
         <strong>INFORMACIÓN DE LA ENTIDAD</strong>
      </td>
      <td width="60%" height="20" align="center" >
        <strong>ENTIDADES CARGADOS</strong>
      </td>
    </tr>
    <tr>
      <td colspan=2>
      <font color=red>
      <b>No ingresar datos con comillas dobles ("")</b>
      </font>
      </td>
    </tr>
    <tr>
      <td align="center"><table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=#E0E0E0>
          <tr>
            <td width="20%" nowrap><strong>Nombre</strong></td>
            <td width="80%" nowrap>
             <input name="nombre" type="text" id="nombre" size="30">
             <input type="button" name="nuevo" value="Nuevo" title="Limpia el formulario, para agregar una nueva entidad" onclick="limpiar()">
            </td>
          </tr>
          <tr>
            <td  nowrap><strong>Tipo</strong></td>
            <td>
            <SELECT name="tipo_entidad">
            <option value='none'>Seleccione un tipo de entidad</option>
            <?//traemos los tipos de entidades
             $query="select nombre,id_tipo_entidad from tipo_entidad order by nombre";
             $tipo_entidad=$db->Execute($query) or die($db->ErrorMsg());
             while (!$tipo_entidad->EOF)
             {?>
              <option value='<?=$tipo_entidad->fields['id_tipo_entidad']?>' <?//if($district->fields['id_distrito']=='') echo "selected"?>><?=$tipo_entidad->fields['nombre']?></option>
             <?
             $tipo_entidad->MoveNext();
             }?>
            </select>
            </td>
          </tr>
          <tr>
            <td  nowrap><strong>Dirección</strong></td>
            <td nowrap> <input name="direccion" type="text" id="direccion" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Teléfono</strong></td>
            <td nowrap> <input name="telefono" type="text" id="telefono" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Fax</strong></td>
            <td nowrap> <input name="fax" type="text" id="fax" size="30"></td>
          </tr>
          <tr>
            <td  nowrap> <strong>Código Postal</strong></td>
            <td nowrap> <input name="cod_pos" type="text" id="cod_pos" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Localidad</strong></td>
            <td nowrap> <input name="localidad" type="text" id="localidad" size="30" ></td>
          </tr>
          <tr>
            <td  nowrap><strong>Distrito</strong></td>
            <td><SELECT name="distrito">
            <option value='none'>Seleccione un distrito</option>
            <?//traemos los distritos
             $query="select nombre,id_distrito from distrito order by nombre";
             $district=$db->Execute($query) or die($db->ErrorMsg());
             while (!$district->EOF)
             {?>
              <option value='<?=$district->fields['id_distrito']?>' <?//if($district->fields['id_distrito']=='') echo "selected"?>><?=$district->fields['nombre']?></option>
             <?
             $district->MoveNext();
             }?>
            </select>
            </td>
          </tr>
          <tr>
            <td  nowrap><strong>E-mail</strong></td>
            <td nowrap> <input name="mail" type="text" id="mail" value="<?//if($do!=2)echo$_POST['mail']?>" size="30"></td>
          </tr>
          <tr>
            <td colspan="2" align="center" nowrap><strong>Observaciones</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap> <div align="center">
                <textarea name="observaciones" cols="50" wrap="VIRTUAL" id="observaciones"><?//if($do!=2)echo$_POST['observaciones']?></textarea>
              </div></td>
          </tr>
          <tr>
            <td colspan="2" align="center" nowrap><strong>Perfil</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap> <div align="center">
                <textarea name="perfil" cols="50" wrap="VIRTUAL" id="perfil"><?//if($do!=2)echo$_POST['observaciones']?></textarea>
              </div></td>
          </tr>
        </table></td>
        <td align="center" nowrap>
        <div align="center">
         <select name="select_entidad" size="22" style="width:85%" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="borrar_buffer()">
            <? $datos_entidad->Move(0);
    while (!$datos_entidad->EOF)
	{
?>
            <option value="<?=$datos_entidad->fields['id_entidad']?>" <?//if($_POST['select_entidad']==$datos_entidad->fields['id_entidad']) echo "selected"?>>
            <?=$datos_entidad->fields['nombre']?>
            </option>
            <? 	$datos_entidad->MoveNext();

	} ?>
          </select>
        </div></td>
    </tr>
  </TABLE>
<br>

</center>

<TABLE width="100%" align="center" cellspacing="0">
<tr>
<td width="100%" align="center"> <input type="submit" name="boton" value="Guardar" onClick="return control_datos()">
</TD>
<!--<td width="10%">
<input type="submit" name="boton" value="Eliminar" onClick=
"
var nombre_entidad;
if (document.all.select_entidad.selectedIndex==-1)
 {
	alert ('Debes elegir un entidad');
	return false;
 }
 else
 {
  entidad.value=nombre_entidad=document.all.select_entidad.options[document.all.select_entidad.selectedIndex].text;
 }
 return (confirm ('¿Está seguro que desea eliminar '+nombre_entidad+'?'))
">
</td>-->
</tr>
</TABLE>
<INPUT type="hidden" name="entidad">
</form>
</body>
</html>