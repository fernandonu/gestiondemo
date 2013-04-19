<?
/*
Author: GACZ

MODIFICADA POR
$Author: enrique $
$Revision: 1.21 $
$Date: 2005/12/01 17:45:56 $
*/

include_once("../../config.php");
//extrae las variables de POST
$id_com=$parametros["id_competidor"];

extract($_POST,EXTR_SKIP);
//print_r($_POST);
if (($boton=="Guardar")&&($editar!="editar"))
	$do=1;

elseif ($boton=="Eliminar")
	$do=2;

elseif($editar=="editar")	
	$do=3;
 
if ($do==1)
{
	
	$campos="(nombre,tel,direccion,mail,nombre_contacto,tel_contacto,observaciones,cuit)";
   $query_insert="INSERT INTO competidores $campos VALUES ".
	"('$nbre','$tel','$dir','$email','$nbre_contacto','$tel_contacto','$observacion','$cuil')";
   if ($db->Execute($query_insert))
	 $informar="<center><b>El competidor \"$nbre\" fue añadido con exito</b></center>";	
	else 
	 $informar="<center><b>El competidor \"$nbre\" no se pudo agregar</b></center>";	
}
elseif ($do==2)
{
	$q="delete from competidores where id_competidor=$select_comp";
	if ($db->Execute($q))
	 $informar="<center><b>El competidor \"$nbre\" fue eliminado con exito</b></center>";	
	else 
	 $informar="<center><b>El competidor \"$nbre\" no se pudo eliminar</b></center>";	
}
elseif ($do==3)
{
 $id_com=$select_comp;
 $query="update competidores set nombre='$nbre',tel='$tel',direccion='$dir',mail='$email',nombre_contacto='$nbre_contacto',tel_contacto='$tel_contacto',observaciones='$observacion',cuit='$cuil' where id_competidor=$select_comp";
 if ($db->Execute($query))
	 $informar="<center><b>El competidor \"$nbre\" fue actualizado con exito</b></center>";	
 else 
	 $informar="<center><b>El competidor \"$nbre\" no se pudo actualizar</b></center>";	
}
 
?>
<!--
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
-->
<?echo $html_header; ?>
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
include("../ayuda/ayudas.php");
?>
</head>
<body bgcolor=#E0E0E0 leftmargin=4>
<SCRIPT LANGUAGE="JavaScript">
<?php
   
//trae los competidores junto con su informacion y las deja en la variable
//con nombre "competidor" concatenada con el id del competidor

$datos_comp=$db->Execute("select * from competidores order by nombre,id_competidor") or die($db->ErrorMsg()."<br>");          	

while (!$datos_comp->EOF)
{
?>
var competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>=new Array();
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["nombre"]="<?php if($datos_comp->fields["nombre"]){echo $datos_comp->fields["nombre"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["tel"]="<?php if($datos_comp->fields["tel"]){echo $datos_comp->fields["tel"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["direccion"]="<?php if($datos_comp->fields["direccion"]){echo $datos_comp->fields["direccion"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["mail"]="<?php if($datos_comp->fields["mail"]){echo $datos_comp->fields["mail"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["nombre_contacto"]="<?php if($datos_comp->fields["nombre_contacto"]){echo $datos_comp->fields["nombre_contacto"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["tel_contacto"]="<?php if($datos_comp->fields["tel_contacto"]){echo $datos_comp->fields["tel_contacto"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["observaciones"]="<?php if($datos_comp->fields["observaciones"]){echo $datos_comp->fields["observaciones"];}else echo "null";?>";
competidor_<?php echo $datos_comp->fields["id_competidor"]; ?>["cuit"]="<?php if($datos_comp->fields["cuit"]){echo $datos_comp->fields["cuit"];}else echo "null";?>";
<?
$datos_comp->MoveNext();
}
?>

function set_datos()
{  
    switch(document.all.select_comp.options[document.all.select_comp.selectedIndex].value)
    {<?PHP
     $datos_comp->Move(0);
     while(!$datos_comp->EOF)
     {?>
      case '<?echo $datos_comp->fields["id_competidor"]?>': info=competidor_<?echo $datos_comp->fields["id_competidor"];?>;break; 
     <?
      $datos_comp->MoveNext();
     }
     ?>
    }
    if(info["nombre"]!="null")
     document.all.nbre.value=info["nombre"];
    else
     document.all.nbre.value="";
    if(info["tel"]!="null")
     document.all.tel.value=info["tel"];
    else
     document.all.tel.value=""; 
    if(info["direccion"]!="null")
     document.all.dir.value=info["direccion"];
    else
     document.all.dir.value=""; 
    if(info["mail"]!="null")
     document.all.email.value=info["mail"];
    else
     document.all.email.value="";
    if(info["nombre_contacto"]!="null")
     document.all.nbre_contacto.value=info["nombre_contacto"];
    else
     document.all.nbre_contacto.value=""; 
    if(info["tel_contacto"]!="null")
     document.all.tel_contacto.value=info["tel_contacto"];
    else
     document.all.tel_contacto.value=""; 
    if(info["observaciones"]!="null")
     document.all.observacion.value=info["observaciones"];
    else
     document.all.observacion.value="";
    if(info["cuit"]!="null")
     document.all.cuil.value=info["cuit"];
    else
     document.all.cuil.value="";
     
   document.all.editar.value="editar";    
}

//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (") 
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
	if (document.all.nbre.value=='' || document.all.nbre.value==' ')
    {
	 alert ('Debes completar el nombre del competidor');
	 return false;
    }
	if(document.all.nbre.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo nombre');
        return false;
    }
    if(document.all.tel.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo teléfono');
        return false;
    }
    if(document.all.dir.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo dirección');
        return false;
    }
    if(document.all.email.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo e-mail');
        return false;
    }
    if(document.all.nbre_contacto.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo nombre del contacto');
        return false;
    }
    if(document.all.tel_contacto.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo teléfono del contacto');
        return false;
    }
    if(document.all.observacion.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo observaciones');
        return false;
    }	
}

function filtrar_teclas(e, goods)
{
var key, keychar;
key = getkey(e);
if (key == null) return false;

// get character
keychar = String.fromCharCode(key);
keychar = keychar.toLowerCase();
goods = goods.toLowerCase();

// check goodkeys
if (goods.indexOf(keychar) != -1)
	return true;

// control keys
if ( key==null || key==0 || key==8 || key==9 || key==13 || key==27 )
   return true;

// else return false
return false;


}

function limpiar()
{
	document.all.nbre.value='';
 	document.all.dir.value='';
 	document.all.email.value='';
 	document.all.cuil.value='';
 	document.all.tel_contacto.value='';
 	document.all.nbre_contacto.value='';
 	document.all.observacion.value='';
 	document.all.editar.value='';
 	
 
}
</SCRIPT>
<?=$informar?>
<form name="form" method="post" action="lic_nuevo_comp.php">
<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_competidor.htm" ?>', 'CARGAR COMPETIDOR')" >
    </div>
<br>
<input type="hidden" name="editar" value="">

<?
if($id_com!="")
{
$quer="select * from competidores where id_competidor=$id_com";	
$res_q=sql($quer,"No se recuperaron los datos del envio") or fin_pagina();
$nomb=$res_q->fields["nombre"];	
$tel_co=$res_q->fields["tel"];	
$dire=$res_q->fields["direccion"];	
$ob=$res_q->fields["observaciones"];	
$cui=$res_q->fields["cuit"];	
$nom_con=$res_q->fields["nombre_contacto"];	
$te_con=$res_q->fields["tel_contacto"];	
$mai=$res_q->fields["mail"];	
$editar="editar";
}


?>
  <table width="100%" align="center" border=0 cellspacing="2" cellpadding="0">
    <tr><td width="50%" valign="top">
        <table align="center" class="bordes">
            <tr>
                <td  align="center" id="mo">INFORMACION DEL COMPETIDOR</td>
            </tr>
            <tr>
                <td>
                        <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=<?=$bgcolor_out?>>
                                <tr >
                                        <td width="20%" nowrap><strong>Nombre</strong></td>
                                        <td width="70%" nowrap> <input name="nbre" type="text" id="nbre" value="<?=$nomb?>" size="35"></td>
                                        <td width="10%" nowrap><input type="button" name="nuevo" value="Nuevo" title="Limpia el formulario, para agregar un nuevo competidor" onclick="limpiar()"></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Teléfono</strong></td>
                                        <td nowrap> <input name="tel" type="text" value="<?=$tel_co?>" id="tel"></td>
                                </tr>
                                <tr>
                                        <td  nowrap> <strong>Dirección</strong></td>
                                        <td nowrap> <input name="dir" type="text" value="<?=$dire?>" id="dir"></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>E-mail</strong></td>
                                        <td nowrap> <input name="email" type="text" value="<?=$mai?>" id="email"></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Cuit</strong></td>
                                        <td nowrap> <input name="cuil" type="text" value="<?=$cui?>" id="cuil" onKeypress="return filtrar_teclas(event,'1234567890');"></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Nombre del Contacto</strong></td>
                                        <td nowrap> <input name="nbre_contacto" type="text" value="<?=$nom_con?>" id="nbre_contacto" size="35"></td>
                                </tr>
                                <tr>
                                        <td  nowrap><strong>Teléfono del Contacto</strong></td>
                                        <td nowrap> <input name="tel_contacto" type="text" value="<?=$te_con?>" id="tel_contacto"></td>
                                </tr>
                                <tr>
                                        <td colspan="2" align="center" nowrap><strong>Observaciones</strong></td>
                                </tr>
                                <tr>
                                        <td colspan="2" nowrap> <div align="center">
                                                <textarea name="observacion" cols="40" wrap="VIRTUAL" id="observacion"><?echo"$ob";?></textarea>
                                                </div></td>
                                </tr>
                        </table>
                </td>
            </tr>
        </table></td>
      <td width="5%">&nbsp;</td>
      <td width="45%" valign="top">
                <table class="bordes" align="center">
                        <tr>
                                <td align="center" id="mo">COMPETIDORES CARGADOS</strong> </td>
                        </tr>
                        <tr>
                                <td width="50%" align="center" nowrap bgcolor=<?=$bgcolor_out?>>
                                <div align="center">
                                <select name="select_comp" size="14" onchange="set_datos()" onKeypress="buscar_op(this);" 
                                onblur="borrar_buffer();"onclick="borrar_buffer();">
                                <? $datos_comp->Move(0);
                                while (!$datos_comp->EOF)
                                {?>
                                        <option value="<?=$datos_comp->fields['id_competidor']?>"
                                <?if($datos_comp->fields['id_competidor']==$id_com){?>selected<?}?>>
                                        <?=$datos_comp->fields['nombre']?>
                                        </option>
                                <?         $datos_comp->MoveNext();
                                } ?>
                                </select>
                                </div></td>
                        </tr>
                </table>
      </td>

    </tr>
    </table>

<br>
<?
if($do==3)
{
?>
<script>
limpiar();
</script>
<?	
}
?>

<TABLE align="center" cellspacing="0">
<tr><td> <input type="submit" name="boton" value="Guardar" onClick="return control_datos()">
</TD>
<td>
<input type="submit" name="boton" value="Eliminar" onClick=
"
var nombre_comp;
if (document.all.select_comp.selectedIndex==-1)
 {
	alert ('Debes elegir un competidor');
	return false;
 }
 else
 {
  competidor.value=nombre_comp=document.all.select_comp.options[document.all.select_comp.selectedIndex].text;
 }
 return (confirm ('¿Está seguro que desea eliminar '+nombre_comp+'?'))
">
</td>
</tr>
</TABLE>
<INPUT type="hidden" name="competidor">
</form>
</body>
</html>