<?php
/*
$Author: Pablo Rojo

modificado por
$Author: mari $
$Revision: 1.6 $
$Date: 2007/01/04 13:34:37 $

A PARTIR DEL 04/06/04 SOLO SE PUEDE VER LA INFORMACION DE LOS CLIENTES
Y NO SE PUEDE EDITAR MAS

*/

include("../../config.php");
include("./funciones.php");

$do=0;
extract($_POST,EXTR_SKIP);
/////////////////////////////////////
if ($_POST['pasar']=="Pasar a Entidades"){
 $id_tipo_entidad=3;
 $query_dis="select id_distrito from distrito where nombre ilike '%$provincia%'";
 $resultado=$db->Execute($query_dis) or die ($db->ErrorMsg($query_dis)."Error");
 $id_dis=$resultado->fields['id_distrito'];
 $campos="(nombre,direccion,id_tipo_entidad,codigo_postal,localidad,cuit,contacto,telefono,mail,perfil,fax,iib,id_iva,id_condicion,id_distrito,observaciones)";
 $query_insert="INSERT INTO entidad $campos VALUES ".
 "('$nombre','$direccion',$id_tipo_entidad,'$cod_pos','$localidad','$cuit','$contacto','$telefono','$mail','$perfil','$fax','$iib',$select_tasa_iva,$select_condicion_iva,$id_dis,'$observaciones')";
 if ($db->Execute($query_insert) or die ($db->ErrorMsg($query_insert)))
  {$informar="<center><b>La entidad \"$nombre\" fue añadida con éxito</b></center>";
    }
 else
   $informar="<center><b>La entidad \"$nombre\" no se pudo agregar</b></center>";
}
//////////////////////////////////////
/*if (($boton=="Guardar")&&($editar!="editar"))
	$do=1;
elseif ($boton=="Eliminar")
	$do=2;
elseif($editar=="editar")
    $do=3;
$inserto=""; */
$do=0;
if ($do==1)
{

   $campos="(nombre,direccion,cod_pos,localidad,provincia,cuit,contacto,telefono,mail,id_condicion,id_iva,iib,observaciones)";
   $query_insert="INSERT INTO clientes $campos VALUES ".
	"('$nombre','$direccion','$cod_pos','$localidad','$provincia','$cuit','$contacto','$telefono','$mail',$select_condicion_iva,$select_tasa_iva,'$iib','$observaciones')";
	if ($db->Execute($query_insert))
	{$informar="<center><b>El cliente \"$nombre\" fue añadido con exito</b></center>";
	 /*$query="select max(id_cliente) as max from clientes";
	 $ins=$db->Execute($query) or die($db->ErrorMsg()."query max");
	 $inserto=$ins->fields['max'];
	 echo "insert: ".$inserto;*/
	}
	else
	 $informar="<center><b>El cliente \"$nombre\" no se pudo agregar</b></center>";
}
elseif ($do==2)
{
	$q="delete from clientes where id_cliente=$select_cliente";
	if ($db->Execute($q))
	 $informar="<center><b>El cliente \"$nombre\" fue eliminado con exito</b></center>";
	else
	 $informar="<center><b>El cliente \"$nombre\" no se pudo eliminar</b></center>";
}
elseif ($do==3)
{$query="update clientes set nombre='$nombre',direccion='$direccion',cod_pos='$cod_pos',localidad='$localidad',provincia='$provincia',cuit='$cuit',
contacto='$contacto',telefono='$telefono',mail='$mail',observaciones='$observaciones', id_condicion=$select_condicion_iva,id_iva=$select_tasa_iva,iib='$iib'
WHERE id_cliente=$select_cliente";
//echo $query;
 if ($db->Execute($query))
	 $informar="<center><b>El cliente \"$nombre\" fue actualizado con exito</b></center>";
 else
	 $informar="<center><b>El cliente \"$nombre\" no se pudo actualizar</b></center>";
}

//datos por parametros
$id_cliente=$parametros['id_cliente'];
$pagina=$parametros['pagina'];

$link=encode_link("viejos_clientes.php",array("id_cliente"=> $id_cliente,"pagina"=>$pagina));

//Parte para trabajar con sesiones
if ($_POST['filtro']) {
 $itemspp=$_POST['filtro'];
 phpss_svars_set("_ses_cliente",$itemspp);
 }
else
    $itemspp=$_ses_cliente or $itemspp=50;


if (!$_POST['filtro']) {
  if (!$_ses_cliente)
            $filtro='a';
            else
            $filtro=$_ses_cliente;
}
else
 $filtro=$_POST['filtro'];


if ($_ses_cliente != $filtro) {
 phpss_svars_set("_ses_cliente",$filtro);
}



?>
<center>

<html>
<head>
<title>Administración de Clientes</title>
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

//trae los clientes junto con su informacion y los deja en la variable
//con nombre "cliente" concatenado con el id del cliente


if ($filtro=="Todos") $filtro="";


//   $sql="select * from general.clientes order by nombre";
$sql="select * from general.clientes where nombre ilike '$filtro%' order by nombre";
$datos_cliente=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

while (!$datos_cliente->EOF)
{
?>
var cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>=new Array();
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["nombre"]="<?php if($datos_cliente->fields["nombre"]){echo $datos_cliente->fields["nombre"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["direccion"]="<?php if($datos_cliente->fields["direccion"]){echo $datos_cliente->fields["direccion"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["cod_pos"]="<?php if($datos_cliente->fields["cod_pos"]){echo $datos_cliente->fields["cod_pos"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["localidad"]="<?php if($datos_cliente->fields["localidad"]){echo $datos_cliente->fields["localidad"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["provincia"]="<?php if($datos_cliente->fields["provincia"]){echo $datos_cliente->fields["provincia"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["cuit"]="<?php if($datos_cliente->fields["cuit"]){echo $datos_cliente->fields["cuit"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["contacto"]="<?php if($datos_cliente->fields["contacto"]){echo $datos_cliente->fields["contacto"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["telefono"]="<?php if($datos_cliente->fields["telefono"]){echo $datos_cliente->fields["telefono"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["mail"]="<?php if($datos_cliente->fields["mail"]){echo $datos_cliente->fields["mail"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["iib"]="<?php if($datos_cliente->fields["iib"]){echo $datos_cliente->fields["iib"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["id_condicion"]="<?php if($datos_cliente->fields["id_condicion"]){echo $datos_cliente->fields["id_condicion"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["id_iva"]="<?php if($datos_cliente->fields["id_iva"]){echo $datos_cliente->fields["id_iva"];}else echo "null";?>";
cliente_<?php echo $datos_cliente->fields["id_cliente"]; ?>["observaciones"]="<?php if($datos_cliente->fields["observaciones"]){echo $datos_cliente->fields["observaciones"];}else echo "null";?>";

<?
$datos_cliente->MoveNext();
}
?>

function set_datos()
{
    switch(document.all.select_cliente.options[document.all.select_cliente.selectedIndex].value)
    {<?PHP
     $datos_cliente->Move(0);
     while(!$datos_cliente->EOF)
     {?>
      case '<?echo $datos_cliente->fields["id_cliente"]?>': info=cliente_<?echo $datos_cliente->fields["id_cliente"];?>;break;
     <?
      $datos_cliente->MoveNext();
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
    if(info["provincia"]!="null")
     document.all.provincia.value=info["provincia"];
    else
     document.all.provincia.value="";
     if(info["cuit"]!="null")
     document.all.cuit.value=info["cuit"];
    else
     document.all.cuit.value="";
     if(info["contacto"]!="null")
     document.all.contacto.value=info["contacto"];
    else
     document.all.contacto.value="";
     if(info["telefono"]!="null")
     document.all.telefono.value=info["telefono"];
    else
     document.all.telefono.value="";
     if(info["mail"]!="null")
     document.all.mail.value=info["mail"];
    else
     document.all.mail.value="";
     if(info["iib"]!="null")
     document.all.iib.value=info["iib"];
    else
     document.all.iib.value="";

     if(info["id_condicion"]!="null")
     document.all.select_condicion_iva.value=info["id_condicion"];
    else
     document.all.select_condicion_iva.value="";

     if(info["id_iva"]!="null")
     document.all.select_tasa_iva.value=info["id_iva"];
    else
     document.all.select_tasa_iva.value="";

     if(info["observaciones"]!="null")
     document.all.observaciones.value=info["observaciones"];
    else
     document.all.observaciones.value="";
   document.all.editar.value="editar";
} //fin de la funcion set_datos()



//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
	if (document.all.nombre.value=='' || document.all.nombre.value==' ')
    {
	 alert ('Debes completar el nombre del cliente');
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
    if(document.all.contacto.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre del contacto');
        return false;
    }
    if(document.all.telefono.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono del contacto');
        return false;
    }
    if(document.all.iib.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo I.I.B. del contacto');
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
    if(document.all.cuit.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Código Postal');
        return false;
    }

} //fin de la funcion control_datos()

//funcion para limpiar el formulario
function limpiar()
{document.all.nombre.value='';
 document.all.direccion.value='';
 document.all.cod_pos.value='';
 document.all.localidad.value='';
 document.all.cuit.value='';
 document.all.contacto.value='';
 document.all.telefono.value='';
 document.all.mail.value='';
 document.all.iib.value='';
 document.all.observaciones.value='';
 document.all.provincia.value='';

 document.all.editar.value='';
}

//funcion para setear los valores de los campos del cliente seleccionado, en
//la pagina de factura o remito
function pass_data(pagina)
{
 if(pagina=='remitos' || pagina=='facturas')
 {
  window.opener.document.all.nbre.value=document.all.nombre.value;
  window.opener.document.all.dir.value=document.all.direccion.value;
  window.opener.document.all.cuit.value=document.all.cuit.value;
  window.opener.document.all.iva.value=document.all.select_tasa_iva.options[document.all.select_tasa_iva.selectedIndex].text;
  window.opener.document.all.condicion_iva.value=document.all.select_condicion_iva.options[document.all.select_condicion_iva.selectedIndex].text;
  window.opener.document.all.iib.value=document.all.iib.value;
  window.opener.document.all.otros.value=document.all.observaciones.value;
 }
}

</SCRIPT>
<script language="JavaScript1.2">
//funciones para busqueda abrebiada utilizando teclas en la lista que muestra los clientes.
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
<?$link=encode_link('viejos_clientes.php',array('pagina'=>$parametros['pagina']));

?>
<form name="form" method="post" action="<?=$link?>">
<!--
<div align='right'>
<a href="#" onClick="abrir_ventana('<?php /*echo "$html_root/modulos/ayuda/licitaciones/ayuda_competidor.htm" */ ?>', 'CARGAR COMPETIDOR')"> Ayuda </a>
</div>
-->

<input type="hidden" name="editar" value="<?//if($do!=0 && $do!=2)echo "editar"
?>">
  <TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td width="40%" align="center"><strong>INFORMACION DEL CLIENTE</strong>

      </td>
      <td width="60%" height="20" align="center" >
      <strong>CLIENTES CARGADOS</strong>
       </td>
    </tr>
    <tr>
      <td align="center">
      <!-- En esta tabla se muestran los datos personales de los clientes -->
      <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=#E0E0E0>
          <tr>
            <td width="20%" nowrap><strong>Nombre</strong></td>
            <td width="70%" nowrap> <input name="nombre" readonly type="text" id="nombre" size="25" value="<?//if($do!=2)echo $_POST['nombre']
            ?>"></td>
            <td width="10%" nowrap align="left"><input type="button" disabled name="nuevo" value="Nuevo" title="Limpia el formulario, para agregar un nuevo cliente" onclick="limpiar();"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Dirección</strong></td>
            <td nowrap> <input name="direccion" readonly type="text" id="direccion"  size="25" value="<?//if($do!=2)echo$_POST['direccion']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap> <strong>Código Postal</strong></td>
            <td nowrap> <input name="cod_pos" readonly type="text" id="cod_pos" value="<?//if($do!=2)echo$_POST['cod_pos']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Localidad</strong></td>
            <td nowrap> <input name="localidad" readonly type="text" id="localidad" value="<?//if($do!=2)echo$_POST['localidad']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Provincia</strong></td>
            <td><SELECT name="provincia">
            <option value='' <?if($_POST['provincia']=='') echo "selected"?>></option>
            <OPTION VALUE="Buenos Aires" <?if($_POST['provincia']=='Buenos Aires') echo "selected"?>>Buenos Aires</option>
            <OPTION VALUE="Catamarca" <?if($_POST['provincia']=='Catamarca') echo "selected"?>>Catamarca</option>
            <OPTION VALUE="Chaco" <?if($_POST['provincia']=='Chaco') echo "selected"?>>Chaco</option>
            <OPTION VALUE="Chubut" <?if($_POST['provincia']=='Chubut') echo "selected"?>>Chubut</option>
            <OPTION VALUE="Córdoba" <?if($_POST['provincia']=='Córdoba') echo "selected"?>>Córdoba</option>
            <OPTION VALUE="Corrientes" <?if($_POST['provincia']=='Corrientes') echo "selected"?>>Corrientes</option>
            <OPTION VALUE="Distrito Federal" <?if($_POST['provincia']=='Distrito Federal') echo "selected"?>>Distrito Federal</option>
            <OPTION VALUE="Entre Ríos" <?if($_POST['provincia']=='Entre Ríos') echo "selected"?>>Entre Ríos</option>
            <OPTION VALUE="Formosa" <?if($_POST['provincia']=='Formosa') echo "selected"?>>Formosa</option>
            <OPTION VALUE="Jujuy" <?if($_POST['provincia']=='Jujuy') echo "selected"?>>Jujuy</option>
            <OPTION VALUE="La Pampa" <?if($_POST['provincia']=='La Pampa') echo "selected"?>>La Pampa</option>
            <OPTION VALUE="La Rioja" <?if($_POST['provincia']=='La Rioja') echo "selected"?>>La Rioja</option>
            <OPTION VALUE="Mendoza" <?if($_POST['provincia']=='Mendoza') echo "selected"?>>Mendoza</option>
            <OPTION VALUE="Misiones" <?if($_POST['provincia']=='Misiones') echo "selected"?>>Misiones</option>
            <OPTION VALUE="Neuquén" <?if($_POST['provincia']=='Neuquén') echo "selected"?>>Neuquén</option>
            <OPTION VALUE="Río Negro" <?if($_POST['provincia']=='Río Negro') echo "selected"?>>Río Negro</option>
            <OPTION VALUE="Salta" <?if($_POST['provincia']=='Salta') echo "selected"?>>Salta</option>
            <OPTION VALUE="San Juan" <?if($_POST['provincia']=='San Juan') echo "selected"?>>San Juan</option>
            <OPTION VALUE="San Luis" <?if($_POST['provincia']=='San Luis') echo "selected"?>>San Luis</option>
            <OPTION VALUE="Santa Cruz" <?if($_POST['provincia']=='Santa Cruz') echo "selected"?>>Santa Cruz</option>
            <OPTION VALUE="Santa Fé" <?if($_POST['provincia']=='Santa Fé') echo "selected"?>>Santa Fé</option>
            <OPTION VALUE="Santiago del Estero" <?if($_POST['provincia']=='Santiago del Estero') echo "selected"?>>Santiago del Estero</option>
            <OPTION VALUE="Tierra del Fuego" <?if($_POST['provincia']=='Tierra del Fuego') echo "selected"?>>Tierra del Fuego</option>
            <OPTION VALUE="Tucumán" <?if($_POST['provincia']=='Tucumán') echo "selected"?>>Tucumán</option>
            </select>
            </td>

          <!--  <td nowrap> <input name="provincia" type="text" id="provincia" size="35"></td>
          -->
          </tr>
          <tr>
            <td  nowrap><strong>CUIT</strong></td>
            <td nowrap> <input name="cuit" readonly type="text" id="cuit" value="<?//if($do!=2)echo$_POST['cuit']
            ?>"></td>

          </tr>
          <tr>
            <td  nowrap><strong>Nombre del Contacto</strong></td>
            <td nowrap> <input name="contacto" readonly type="text" id="contacto" value="<?//if($do!=2)echo$_POST['contacto']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Teléfono del Cliente</strong></td>
            <td nowrap> <input name="telefono" readonly type="text" id="telefono" value="<?//if($do!=2)echo$_POST['telefono']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap><strong>E-mail</strong></td>
            <td nowrap> <input name="mail" readonly type="text" id="mail" value="<?//if($do!=2)echo$_POST['mail']
            ?>"></td>
          </tr>

          <tr>
            <td  nowrap><strong>Nº I.I.B.</strong></td>
            <td nowrap> <input name="iib" readonly type="text" id="iib" value="<?//if($do!=2)echo$_POST['iib']
            ?>"></td>
          </tr>

           <tr>
            <td  nowrap><strong>Tasa IVA</strong></td>
            <td>
             <?php
              $query="SELECT * from tasa_iva";
              $resultado = $db->Execute($query) or die($db->ErrorMsg());
              $filas_encontradas=$resultado->RecordCount();
             // echo $filas_encontradas;
              echo "<select name='select_tasa_iva'>";
              for($i=0;$i<$filas_encontradas; $i++){
                  $string=$resultado->fields['porcentaje'];
                  $valor=$resultado->fields['id_iva'];
                  echo "<option value='$valor' ";if($_POST['select_tasa_iva']=="$valor"){echo "selected";}echo">$string</option>";
                  $resultado->MoveNext();
              }
              echo "</select>";

              echo "</td>";
           echo "</tr>";
             ?>
             <tr>
              <td  nowrap><strong>Condición IVA</strong></td>
              <td>
               <?php
              $query="SELECT * from condicion_iva";
              $resultado = $db->Execute($query) or die($db->ErrorMsg());
              $filas_encontradas=$resultado->RecordCount();
             // echo $filas_encontradas;
              echo "<select name='select_condicion_iva'>";
              for($i=0;$i<$filas_encontradas; $i++){
                  $string=$resultado->fields['nombre'];
                  $valor=$resultado->fields['id_condicion'];
                  echo "<option value='$valor' ";if($_POST['select_condicion_iva']=="$valor"){echo "selected";}echo">$string</option>";
                  $resultado->MoveNext();
              }
              echo "</select>";
              echo "</td>";
           echo "</tr>";
             ?>
          <tr>
            <td colspan="2" align="center" nowrap><strong>Observaciones</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap> <div align="center">
                <textarea name="observaciones" readonly cols="40" wrap="VIRTUAL" id="observaciones"><?//if($do!=2)echo$_POST['observaciones']
                ?>
                </textarea>

              </div></td>
          </tr>
        </table>
        </td> <!--  En esta celda van todo los clientes -->
        <td align="center" nowrap>
             <TABLE width="100%">
             <tr>
               <td>
               <?
               tabla_filtros_nombres($link);
             ?>
               </td>
             </tr>
              <tr>
               <td>
                 <div align="center">
                     <select name="select_cliente" size="22" style="width:85%" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="borrar_buffer()">
                        <? $datos_cliente->Move(0);
                         while (!$datos_cliente->EOF)
	                      {
                        ?>
                        <option value="<?=$datos_cliente->fields['id_cliente']?>" <?if($_POST['select_cliente']==$datos_cliente->fields['id_cliente']) echo "selected"?>>
                          <?=$datos_cliente->fields['nombre']?>
                       </option>
                          <? 	$datos_cliente->MoveNext();
                          } ?>
                     </select>
                   </div>
                 </td>
               </tr>
            </table>
      </td>
    </tr>
  </TABLE>
<br>

</center>

<TABLE width="100%" align="center" cellspacing="0">
<!-- <tr><td> <input type="submit" name="boton" value="Guardar" onClick="set_opener_campos();return control_datos()">
-->
<tr>
<td width="40%" align="left">
<?if(($parametros['pagina']=="remitos")||($parametros['pagina']=="facturas"))
{?>
 <input type="button" name="elegir" value="Elegir cliente" title="Traslada los datos del cliente seleccionado a la página <?=$parametros['pagina']?>" onclick="pass_data('<?=$parametros['pagina']?>');">
<?
}
?>
</td>
<?
if (permisos_check("inicio", "pasar_a_entidades")) {
?>
<td align="left">
<input type="submit" name="pasar" value="Pasar a Entidades"></td>
<?
 }
//$link=encode_link('asociar_entidades.php',array());
//$onclick="ventana=window.open('$link','','left=40,top=80,width=700,height=300,resizable=1,status=1')";
if ($_ses_user['login']== "mariela" || $_ses_user['login']== "fernando"){
$link=encode_link("asociar_entidades.php", array());	
echo "<A target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
}
//                     
 
?>

<td width="10%">
<input type="submit" name="boton" value="Guardar" disabled onClick="return control_datos()">

</TD>
<td width="10%">
<input type="submit" name="boton" value="Eliminar" disabled onClick=
"
var nombre_cliente;
if (document.all.select_cliente.selectedIndex==-1)
 {
	alert ('Debes elegir un cliente');
	return false;
 }
 else
 {
  cliente.value=nombre_cliente=document.all.select_cliente.options[document.all.select_cliente.selectedIndex].text;
 }
 return (confirm ('¿Está seguro que desea eliminar '+nombre_cliente+'?'))
">
</td>
<td width="40%" align="left">
<?if(($parametros['pagina']=="remitos")||($parametros['pagina']=="facturas"))
{?>
 <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
<?
}
?>
</td>
</tr>
</TABLE>
<?
 //echo "Página generada en ".tiempo_de_carga()." segundos.<br>";
 ?>
<INPUT type="hidden" name="cliente">
</form>
</body>
</html>