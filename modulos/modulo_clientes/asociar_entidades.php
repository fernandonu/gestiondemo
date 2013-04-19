<?php
/*
$Author: elizabeth

modificado por
$Author: elizabeth $
$Revision: 1.1 $
$Date: 2004/08/25 16:30:45 $
*/

include("../../config.php");
include("./funciones.php");

$do=0;
extract($_POST,EXTR_SKIP);

/////////////////////////////////////
if ($_POST['asociar']=="Asociar Entidades"){
 $nro_orden=$_POST['select_orden'];	
 $id_entidad=$_POST['select_entidades'];
 $query_update="update compras.orden_de_compra set id_entidad=$id_entidad 
                where nro_orden=$nro_orden";
  if ($db->Execute($query_update) or die ($db->ErrorMsg($query_update)))
  {$informar="<center><b>La orden se asoció</b></center>";
    }
 else
   $informar="<center><b>La orden no se asoció</b></center>";
}


//datos por parametros
//$id_cliente=$parametros['id_cliente'];
//$pagina=$parametros['pagina'];

//$link=encode_link("viejos_clientes.php",array("id_cliente"=> $id_cliente,"pagina"=>$pagina));

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
$sql="select * from licitaciones.entidad where nombre ilike '$filtro%' order by nombre";
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
<?
echo $informar;
$pagina=$parametros['pagina'];
$link=encode_link('asociar_entidades.php',array('pagina'=>$parametros['pagina']));
?>

<form name="form" method="post" action="<?=$link?>">

<TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td width="40%" align="center"><strong>Mostrar Ordenes de Compra</strong></td>
      <td width="60%" height="20" align="center" >
      <strong>CLIENTES CARGADOS</strong>
       </td>
    </tr>
    <tr>
      <td align="center">
      <? $consulta_ordenes="select nro_orden, cliente, id_entidad, fecha, estado 
                            from compras.orden_de_compra
                            where id_entidad isnull and fecha > '2004-07-01' and estado <> 'n'
                            order by nro_orden";
         $resultado_consulta=$db->Execute($consulta_ordenes) or die ($db->ErrorMsg($consulta_ordenes));
         ?>
        <div align="center">
        <? echo "Cantidad de ordenes  ".$resultado_consulta->RecordCount();
        ?>
           <select name="select_orden" size="22" style="width:85%" >
             <? $resultado_consulta->Move(0);
             while (!$resultado_consulta->EOF)
	          { ?>
             <option value="<?=$resultado_consulta->fields['nro_orden']?>" <?if($_POST['select_orden']==$resultado_consulta->fields['nro_orden']) echo "selected"?>>
                <?=$resultado_consulta->fields['nro_orden']." [".$resultado_consulta->fields['cliente']."] -- ".$resultado_consulta->fields['id_entidad']?>
             </option>
             <?    $resultado_consulta->MoveNext();
               } ?>
           </select>
          </div>
      </td>
      <!--  En esta celda van todo los clientes -->
      <td align="center" nowrap>
        <TABLE width="100%">
          <tr>
            <td>
            <INPUT type="hidden" name="editar">
             <? tabla_filtros_nombres($link); ?>
            </td>
          </tr>
          <tr>
            <td>
              <div align="center">
                <select name="select_entidades" size="22" style="width:85%">
                 <? $datos_cliente->Move(0);
                 while (!$datos_cliente->EOF)
	                { ?>
                     <option value="<?=$datos_cliente->fields['id_entidad']?>" <?if($_POST['select_entidades']==$datos_cliente->fields['id_entidad']) echo "selected"?>>
                       <?=$datos_cliente->fields['nombre']?>
                     </option>
                 <?    $datos_cliente->MoveNext();
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

<tr>
<?
if (permisos_check("inicio", "pasar_a_entidades")) {
?>
<td align="center">
<input type="submit" name="asociar" value="Asociar Entidades"></td>
<?
 }
?>
</tr>
</TABLE>
<?
 //echo "Página generada en ".tiempo_de_carga()." segundos.<br>";
 ?>
<INPUT type="hidden" name="cliente">
</form>
</body>
</html>