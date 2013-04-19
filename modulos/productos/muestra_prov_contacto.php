<?php
/*
Autor: Broggi
Creado: 01/07/2004

$Author: broggi $
$Revision: 1.4 $
$Date: 2004/06/17 17:48:03 $
*/

include("../../config.php");
echo $html_header;
$sql = "select *,contactos.id_contacto,contactos.nombre as nombre_cont,proveedor.nombre as nombre_prov from proveedor left join contactos using(id_proveedor) where id_proveedor=".$parametros['id_prov'];
$result_consulta = sql($sql) or fin_pagina();
$cantidad = $result_consulta->recordcount();

?>

<form name="lista_prov_contactos" action="muestra_prov_contacto.php" method="post">

<table width="100%" align="center" border="1" cellpadding="5" cellspacing="1">
 <tr  id="mo">
  <td align="center" >
   <b>Proveedores y contactos</b>
  </td>
 </tr> 
</table>
&nbsp
&nbsp
&nbsp

<table id="ma" width="50%" align="center" border="0" cellspacing="1">
 <tr id="mo">
  <td align="center">
   <b>Proveedor</b>
  </td> 
 </tr>
</table>
&nbsp
&nbsp

<table bgcolor=<?=$bgcolor_out?> width="80%" align="center" border="0" cellspacing="1">
 <tr align="left">
  <td width="25%"><b>
   Razón Social:
  </b></td>
  <td width="60%">
   <?=$result_consulta->fields['razon_social']?>
  </td>
  <td>
   <?
    if ($result_consulta->fields['activo']=='t') echo "Activo  ";
    else echo "No Activo"
   ?>
  </td>
 </tr>
 <tr align="left">
  <td><b>
   Nombre Fantasia:
  </b></td>
  <td>
   <?=$result_consulta->fields['nbre_fantasia']?>
  </td>
 </tr>
 <tr align="left">
  <td><b>
   Cuit:
  </b></td>
  <td>
   <?=$result_consulta->fields['cuit']?>
  </td>
 </tr>
 <tr align="left">
  <td><b>
   I.V.A:
  </b></td>
  <td>
   <?=$result_consulta->fields['iva']?>
  </td>
 </tr>
</table>
&nbsp;
<hr>
<table id="ma" width="50%" align="center" border="0" cellspacing="1">
 <tr id="mo">
  <td align="center">
   <? if ($result_consulta->fields['id_contacto']!="") echo "<b>Contactos</b>";
   else echo "<b> NO HAY CONTACTOS CARGADOS</b>" ?>
  </td>
 </tr>
</table>
<!--<hr size="2" style="color: black">-->
&nbsp
<?
 if ($result_consulta->fields['id_contacto']!="") {
 $i=1;
 while (!$result_consulta->EOF)
 {
?>
  <table width="20" height="20" align="center" >
   <tr id="mo">
    <td>
     <b><?=$i?></b>
    </td>
   </tr>
  </table>
&nbsp;  
  <table bgcolor=<?=$bgcolor_out?> width="80%" align="center" border="0" cellspacing="1">
   <tr align="left">
    <td width="25%"><b>
     Nombre:
    </b></td>
    <td width="60%">
     <?=$result_consulta->fields['nombre_cont']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     Telefono:
    </b></td>
    <td>
     <?=$result_consulta->fields['tel']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     F.A.X:
    </b></td>
    <td>
     <?=$result_consulta->fields['fax']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     Dirección:
    </b></td>
    <td>
     <?=$result_consulta->fields['direccion']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     Provincía:
    </b></td>
    <td>
     <?=$result_consulta->fields['provincia']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     Localidad:
    </b></td>
    <td>
     <?=$result_consulta->fields['localidad']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     Codigo Postal:
    </b></td>
    <td>
     <?=$result_consulta->fields['cod_postal']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     Correo Electrónico:
    </b></td>
    <td>
     <?=$result_consulta->fields['mail']?>
    </td>
   </tr>
   <tr align="left">
    <td><b>
     I.C.Q:
    </td>
    </b><td>
     <?=$result_consulta->fields['ICQ']?>
    </td>
   </tr>
  </table>
 &nbsp  
<hr size="2" style="color: black">  
<?
  $i++;
  $result_consulta->MoveNext();
 }
 }
?>  
<? $link=encode_link("carga_prov.php",array("id_prov"=>$parametros['id_prov']));
 if(permisos_check("inicio","permiso_editar_prov")) 
   $permiso_editar="";
   else 
   $permiso_editar="disabled"; 
?>
<table width="10%" align="center">
 <tr>
  <td> <input type="button" value="Editar Proveedor"  <?=$permiso_editar?> onclick="window.opener.document.location='<?=$link?>';window.close()"></td>
  <td> <input type="button" value="Cerrar" onclick="window.close()"> </td>
 </tr>
</table>
</form>
<?
//fin_pagina(false);
?>
