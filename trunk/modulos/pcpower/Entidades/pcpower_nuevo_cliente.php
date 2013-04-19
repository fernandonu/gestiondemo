<?php
/*
$Author: MAC
Fecha: 04/10/04

modificado por
$Author: ferni $
$Revision: 1.8 $
$Date: 2006/01/12 19:34:05 $

*/

include("../../../config.php");
include("pcpower_funciones.php");

$do=0;
extract($_POST,EXTR_SKIP);
if (($boton=="Guardar")&&($editar!="editar"))
	$do=1;
elseif ($boton=="Eliminar")
	$do=2;
elseif($editar=="editar")
    $do=3;
$inserto="";
if ($do==1)
{ $db->StartTrans();
   $sql = "select nextval('pcpower_entidad_id_entidad_seq') as id_entidad";
   $id_rec = sql($sql) or fin_pagina();
   $id_entidad = $id_rec->fields['id_entidad'];
   $sql = "insert into pcpower_org_entidades (id_org,id_entidad) values ($select_tipo_entidad,$id_entidad)";
   $db->Execute($sql) or die($db->ErrorMsg("No se pudo Insertar en pcpower_org_entidades"));
   $campos="(id_entidad,nombre,direccion,id_tipo_entidad,codigo_postal,localidad,cuit,contacto,telefono,mail,fax,iib,id_iva,id_condicion,id_distrito,observaciones)";
   //if($select_tipo_entidad==-1)
    $select_tipo_entidad="1";
   if($select_tasa_iva==-1)
    $select_tasa_iva="null";
   if($select_condicion_iva==-1) 
    $select_condicion_iva="null";
   if($distrito==-1)
    $distrito="null"; 
   $query_insert="INSERT INTO pcpower_entidad $campos VALUES ".
	"($id_entidad,'$nombre','$direccion',$select_tipo_entidad,'$cod_pos','$localidad','$cuit','$contacto','$telefono','$mail','$fax','$iib',$select_tasa_iva,$select_condicion_iva,$distrito,'$observaciones')";
	if ($db->Execute($query_insert) or die($db->ErrorMsg()."<br>Error al insertat <br> $query_insert"))
	{$informar="<center><b>El cliente \"$nombre\" fue añadido con éxito</b></center>";
    
	}
	else
	 $informar="<center><b>El cliente \"$nombre\" no se pudo agregar</b></center>";
  $db->CompleteTrans();	 
}
/*elseif ($do==2)
{
	$q="delete from entidad where id_entidad=$select_entidad";
	if ($db->Execute($q))
	 $informar="<center><b>La entidad \"$nombre\" fue eliminada con éxito</b></center>";
	else
	 $informar="<center><b>La entidad \"$nombre\" no se pudo eliminar</b></center>";
}*/
elseif ($do==3)
{
   $db->StartTrans(); 
   $sql = "delete from pcpower_org_entidades where id_entidad=$select_entidad";
   //echo $sql;
   $db->Execute($sql) or die($db->ErrorMsg("No se pudo Eliminar en pcpower_org_entidades"));
   
   $sql = "insert into pcpower_org_entidades (id_org,id_entidad) values ($select_tipo_entidad,$select_entidad)";
   $db->Execute($sql) or die($db->ErrorMsg("No se pudo Insertar en pcpower_org_entidades"));
      
	//if($select_tipo_entidad==-1)
    $select_tipo_entidad="1";
   if($select_tasa_iva==-1)
    $select_tasa_iva="null";
   if($select_condicion_iva==-1) 
    $select_condicion_iva="null";
   if($distrito==-1)
    $distrito="null";

$query="update pcpower_entidad set nombre='$nombre',direccion='$direccion',codigo_postal='$cod_pos',localidad='$localidad',
contacto='$contacto',telefono='$telefono',mail='$mail',observaciones='$observaciones',fax='$fax',cuit='$cuit',
iib='$iib',id_iva=$select_tasa_iva,id_distrito=$distrito,id_condicion=$select_condicion_iva,id_tipo_entidad=$select_tipo_entidad
WHERE id_entidad=$select_entidad";
//echo $query;
 if ($db->Execute($query) or die ($db->ErrorMsg()."<br>$query"))
 { $informar="<center><b>El cliente \"$nombre\" fue actualizado con éxito</b></center>";
    
 }
 else
	 $informar="<center><b>El cliente \"$nombre\" no se pudo actualizar</b></center>";
  $db->CompleteTrans();	 
}

//datos por parametros
$id_entidad=$parametros['id_entidad'];
$pagina=$parametros['pagina'];

$link=encode_link("pcpower_nuevo_cliente.php",array("id_entidad"=>$id_entidad,"pagina"=>$pagina));

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
</head>
<body bgcolor=#E0E0E0 leftmargin=4>

<?php
//trae los entidades junto con su informacion y los deja en la variable
//con nombre "entidad" concatenado con el id de la entidad


if ($filtro=="Todos") $filtro="";

//$sql="select pcpower_entidad.* from pcpower_entidad where nombre ilike '$filtro%' order by nombre";
$sql ="select pcpower_entidad.*,pcpower_organismos.nombre as nombre_org, pcpower_organismos.id_org 
       from pcpower_presupuesto.pcpower_entidad 
       left join pcpower_presupuesto.pcpower_org_entidades using (id_entidad)
       left join pcpower_presupuesto.pcpower_organismos using (id_org)
       where pcpower_entidad.nombre ilike '$filtro%' order by nombre";

$datos_entidad=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

?>
<SCRIPT LANGUAGE="JavaScript">
<?
$datos_entidad->MoveFirst();
while (!$datos_entidad->EOF)
{
$id_entidad=$datos_entidad->fields["id_entidad"];
$nombre=$datos_entidad->fields["nombre"];
$nombre=ereg_replace("\r\n","<br>",$nombre);
$nombre=ereg_replace("\n","<br>",$nombre);
$nombre=str_replace('"'," ",$nombre);
$direccion=$datos_entidad->fields["direccion"];
$direccion=str_replace('"'," ",$direccion);
$cod_pos=$datos_entidad->fields["codigo_postal"];
$cod_pos=str_replace('"'," ",$cod_pos);


?>
var entidad_<?=$id_entidad?>=new Array();
entidad_<?=$id_entidad?>["nombre"]="<?php if($nombre){echo $nombre;}else echo "null";?>";
entidad_<?=$id_entidad?>["id_tipo_entidad"]="<?php if($datos_entidad->fields["id_org"]){echo $datos_entidad->fields["id_org"];}else echo "null";?>";
entidad_<?=$id_entidad?>["direccion"]="<?php if($direccion){echo $direccion;}else echo "null";?>";
entidad_<?=$id_entidad?>["cod_pos"]="<?php if($cod_pos){echo $cod_pos;}else echo "null";?>";
entidad_<?=$id_entidad?>["localidad"]="<?php if($datos_entidad->fields["localidad"]){echo $datos_entidad->fields["localidad"];}else echo "null";?>";
entidad_<?=$id_entidad?>["distrito"]="<?php if($datos_entidad->fields["id_distrito"]){echo $datos_entidad->fields["id_distrito"];}else echo "null";?>";
entidad_<?=$id_entidad?>["cuit"]="<?php if($datos_entidad->fields["cuit"]){echo $datos_entidad->fields["cuit"];}else echo "null";?>";
entidad_<?=$id_entidad?>["contacto"]="<?php if($datos_entidad->fields["contacto"]){echo $datos_entidad->fields["contacto"];}else echo "null";?>";
entidad_<?=$id_entidad?>["telefono"]="<?php if($datos_entidad->fields["telefono"]){echo $datos_entidad->fields["telefono"];}else echo "null";?>";
entidad_<?=$id_entidad?>["fax"]="<?php if($datos_entidad->fields["fax"]){echo $datos_entidad->fields["fax"];}else echo "null";?>";
entidad_<?=$id_entidad?>["mail"]="<?php if($datos_entidad->fields["mail"]){echo $datos_entidad->fields["mail"];}else echo "null";?>";
entidad_<?=$id_entidad?>["iib"]="<?php if($datos_entidad->fields["iib"]){echo $datos_entidad->fields["iib"];}else echo "null";?>";
entidad_<?=$id_entidad?>["id_condicion"]="<?php if($datos_entidad->fields["id_condicion"]){echo $datos_entidad->fields["id_condicion"];}else echo "null";?>";
entidad_<?=$id_entidad?>["id_iva"]="<?php if($datos_entidad->fields["id_iva"]){echo $datos_entidad->fields["id_iva"];}else echo "null";?>";

<?
$observaciones=$datos_entidad->fields["observaciones"];
$observaciones=ereg_replace("\r\n","<br>",$observaciones);
$observaciones=str_replace('"'," ",$observaciones);
?>
entidad_<?=$id_entidad?>["observaciones"]="<?php if($observaciones){echo $observaciones;}else echo "null";?>";

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
    if(info["id_tipo_entidad"]!="null")
    //{alert ("Entro como que tiene datos");
     //alert ("el valor es: "info["id_org"])
     document.all.select_tipo_entidad.value=info["id_tipo_entidad"];
    else
     document.all.select_tipo_entidad.value=-1; 
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
    if(info["distrito"]!="null")
     document.all.distrito.value=info["distrito"];
    else
     document.all.distrito.value=-1;
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
    if(info["fax"]!="null")
     document.all.fax.value=info["fax"];
    else
     document.all.fax.value=""; 
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
     document.all.select_condicion_iva.value=-1;

    if(info["id_iva"]!="null")
     document.all.select_tasa_iva.value=info["id_iva"];
    else
     document.all.select_tasa_iva.value=-1;
    if(info["observaciones"]!="null")
     document.all.observaciones.value=info["observaciones"];
    else
     document.all.observaciones.value="";
   document.all.editar.value="editar";
} //fin de la funcion set_datos()

</script>

<script>
//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{

       if  (document.all.select_tipo_entidad.options[document.all.select_tipo_entidad.selectedIndex].value==-1){
       alert('Debe elegir el Organismo');
       return false;
       }


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
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono');
        return false;
    }
    if(document.all.fax.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Fax');
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
    if(document.all.contacto.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Contacto');
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
 document.all.fax.value='';
 document.all.mail.value='';
 document.all.iib.value='';
 document.all.observaciones.value='';
 document.all.distrito.value=-1;
 document.all.select_tasa_iva.value=-1;
 document.all.select_condicion_iva.value=-1;
 document.all.select_tipo_entidad.value=-1;

 document.all.editar.value='';
}

//funcion para setear los valores de los campos de la entidad seleccionada, en
//la pagina de factura o remito
function pass_data(pagina)
{


  window.opener.document.all.id_entidad.value=document.all.select_entidad.value;
  window.opener.document.all.nbre.value=document.all.nombre.value;
  window.opener.document.all.nbre.disabled=0;
  window.opener.document.all.dir.value=document.all.direccion.value;
  window.opener.document.all.dir.disabled=0;
  //window.opener.document.all.cliente_cargado.value=1;
  //window.opener.document.all.cuit.value=document.all.cuit.value;
  //window.opener.document.all.cuit.disabled=0;
  //si tiene iva, lo pasamos, sino no, pasamos un 0
  /*if(document.all.select_tasa_iva.value!=-1)
   window.opener.document.all.iva.value=document.all.select_tasa_iva.options[document.all.select_tasa_iva.selectedIndex].text;
  else
    window.opener.document.all.iva.value="0";
  window.opener.document.all.iva.disabled=0;
  //si tiene condicion de iva, lo pasamos, sino no, pasamos el string "Debe cargarse"
  if(document.all.select_condicion_iva.value!=-1)
    window.opener.document.all.condicion_iva.value=document.all.select_condicion_iva.options[document.all.select_condicion_iva.selectedIndex].text;
  else
    window.opener.document.all.condicion_iva.value="Debe cargarse";
  window.opener.document.all.condicion_iva.disabled=0;
  window.opener.document.all.iib.value=document.all.iib.value;
  window.opener.document.all.iib.disabled=0;
// no se corresponde con las observaciones
//  window.opener.document.all.otros.value=document.all.observaciones.value;
  window.opener.document.all.otros.disabled=0;
*/
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
<?$link=encode_link('pcpower_nuevo_cliente.php',array('pagina'=>$parametros['pagina']));
 //echo $sql;
?>
<form name="form" method="post" action="<?=$link?>">
<input type="hidden" name="editar" value="<?if($do!=0 && $do!=2 && $do!=3)echo "editar"?>">
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
      <!-- En esta tabla se muestran los datos personales de las entidades -->
      <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=#E0E0E0>
        <tr>
         <td colspan="2">
          <table>
          <tr> 
          <td width="95%">
           <font color=red>
           <b>No ingresar datos con comillas dobles ("")</b>
           </font>
           </td>
           <td nowrap align="left"><input type="button" name="nuevo" value="Nuevo" title="Limpia el formulario, para agregar un nuevo cliente" onclick="document.all.boton.disabled=0;limpiar();"></td>
          </tr>
          </table>
         </td> 
        </tr>    
        <tr>
            <td width="20%" nowrap><strong>Nombre o Empresa</strong></td>
            <td width="70%" nowrap> <input name="nombre" type="text" id="nombre" size="35" value="<?//if($do!=2)echo $_POST['nombre']
            ?>"></td>
            
          </tr>
          <tr>
           <td width="20%" nowrap><strong>Organismo</strong></td>
            <td width="70%" nowrap>
            <?
            //traemos los tipos de entidad posibles
            $query="select id_org,nombre from pcpower_organismos order by nombre";
            $tipo_entidad=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los tipos de clientes");
            ?>
            <select name="select_tipo_entidad">
             <option value=-1>Seleccione Organismo</option>
             <?
             while(!$tipo_entidad->EOF) 
             {?>
              <option value="<?=$tipo_entidad->fields['id_org']?>"><?=$tipo_entidad->fields['nombre']?></option>
             <?
              $tipo_entidad->MoveNext();
             }	
             ?>
            </select> 
           </td>
          </tr>
          <tr>
            <td  nowrap><strong>Dirección</strong></td>
            <td nowrap> <input name="direccion" type="text" id="direccion"  size="30" value="<?//if($do!=2)echo$_POST['direccion']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap> <strong>Código Postal</strong></td>
            <td nowrap> <input name="cod_pos" type="text" id="cod_pos" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Localidad</strong></td>
            <td nowrap> <input name="localidad" type="text" id="localidad" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Distrito</strong></td>
            <td>
            <?
            //traemos los distritos de la BD
            $query="select nombre,id_distrito from pcpower_distrito order by nombre";
            $distritos=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer distritos");
            ?>
            <SELECT name="distrito">
             <option value=-1>Seleccione un Distrito</option>
            <?
            while(!$distritos->EOF)
            {?>
             <option value="<?=$distritos->fields['id_distrito']?>" <?if($_POST['distrito']==$distritos->fields['id_distrito'])echo "selected"?>><?=$distritos->fields['nombre']?></option>
             <?
             $distritos->MoveNext();	
            } 
            ?> 
            </select>
            </td>

          
          </tr>
          <tr>
            <td  nowrap><strong>CUIT</strong></td>
            <td nowrap> <input name="cuit" type="text" id="cuit" size="30"></td>

          </tr>
          <tr>
            <td  nowrap><strong>Nombre del Contacto</strong></td>
            <td nowrap> <input name="contacto" type="text" id="contacto" size="30"></td>
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
            <td  nowrap><strong>E-mail</strong></td>
            <td nowrap> <input name="mail" type="text" id="mail" size="30"></td>
          </tr>

          <tr>
            <td  nowrap><strong>Nº I.I.B.</strong></td>
            <td nowrap> <input name="iib" type="text" id="iib" size="30"></td>
          </tr>

           <tr>
            <td  nowrap><strong>Tasa IVA</strong></td>
            <td>
             <?php
              $query="SELECT * from tasa_iva";
              $resultado = $db->Execute($query) or die($db->ErrorMsg());
              $filas_encontradas=$resultado->RecordCount();
             ?>
              <select name='select_tasa_iva'>
               <option value=-1>Seleccione</option>
             <?
              for($i=0;$i<$filas_encontradas; $i++){
                  $string=$resultado->fields['porcentaje'];
                  $valor=$resultado->fields['id_iva'];
                  echo "<option value='$valor' ";if($_POST['select_tasa_iva']=="$valor"){echo "selected";}echo">$string</option>";
                  $resultado->MoveNext();
              }
              ?>
              </select>
            </td>
           </tr>
           <tr>
              <td  nowrap><strong>Condición IVA</strong></td>
              <td>
               <?php
              $query="SELECT * from condicion_iva";
              $resultado = $db->Execute($query) or die($db->ErrorMsg());
              $filas_encontradas=$resultado->RecordCount();
              ?>
              <select name='select_condicion_iva'>
               <option value=-1>Seleccione</option>
              <?
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
            <td colspan="2" nowrap width="100%"><br><strong>Observaciones</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap width="100%">
                <textarea name="observaciones" cols="52" wrap="VIRTUAL" id="observaciones"></textarea>

            </td>
          </tr>
        </table>
      </td> <!--  En esta celda van todas las entidades -->
      <td align="center" nowrap>
        <TABLE width="100%">
             <tr>
               <td>
                <?tabla_filtros_nombres($link);?>
               </td>
             </tr>
             <tr>
               <td>
                     <select name="select_entidad" size="30" style="width:98%" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="document.all.boton.disabled=0;
                                                                                                                                                                                             if(typeof(document.all.elegir)!='undefined')
                                                                                                                                                                                              document.all.elegir.disabled=0;
                                                                                                                                                                                             borrar_buffer()"
                     >
                    
                    
                        <? $datos_entidad->Move(0);
                         while (!$datos_entidad->EOF)
	                      {
	                    ?>
                        
                        <option value="<?=$datos_entidad->fields['id_entidad']?>" <?if($_POST['select_entidad']==$datos_entidad->fields['id_entidad']) echo "selected"?>>
                          <?=$datos_entidad->fields['nombre']?>
                       </option>
                          <? 	
                          $datos_entidad->MoveNext();		
                          } ?>
                     </select>
               </td>
             </tr>
        </table>
      </td>
    </tr>
  </TABLE>
<br>

</center>

<TABLE width="100%" align="center" cellspacing="0">
<tr>
<td width="50%" align="left">
<?
if(($parametros['pagina']=="remitos")||($parametros['pagina']=="facturas")||($parametros['pagina']=="caso_estadisticas"))
{?>
 <input type="button" name="elegir" disabled value="Elegir cliente" title="Traslada los datos del cliente seleccionado a la página <?=$parametros['pagina']?>" onclick="pass_data('<?=$parametros['pagina']?>');">
<?
}
?>
</td>
<td width="50%">
<input type="submit" name="boton"  value="Guardar"  onClick="return control_datos()">

</TD>
<?/*<td width="10%">
<input type="submit" name="boton" value="Eliminar" onClick=
"
var nombre_entidad;
if (document.all.select_entidad.selectedIndex==-1)
 {
	alert ('Debes elegir una entidad');
	return false;
 }
 else
 {
  entidad.value=nombre_entidad=document.all.select_entidad.options[document.all.select_entidad.selectedIndex].text;
 }
 return (confirm ('¿Está seguro que desea eliminar '+nombre_entidad+'?'))
">
</td>*/?>
<td width="40%" align="left">
<?

if(($parametros['pagina']=="remitos")||($parametros['pagina']=="facturas")||($parametros['pagina']=="caso_estadisticas"))
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
<INPUT type="hidden" name="entidad">
</form>
</body>
</html>