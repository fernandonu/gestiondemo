<?php
/*
$Author: mari $
$Revision: 1.5 $
$Date: 2005/02/28 20:55:42 $
*/

require_once("../../config.php");
variables_form_busqueda("cargar_proveedor");

$link = encode_link("cargar_proveedores.php",array());

$pagina_viene=$parametros['pagina_viene'] or $pagina_viene=$_POST['pagina_viene'];
$onclick_salir = $parametros['onclick_salir'] or $onclick_salir=$_POST['onclick_salir'];
$onclick_cargar = $parametros['onclick_cargar'] or $onclick_cargar=$_POST['onclick_cargar'];


function generar_parte_proveedores()
{
global $db,$bgcolor1,$pagina;
global $concepto_cuenta,$filtro,$parametros,$disabled_pagos,$cuenta;

 $query="SELECT nbre_fantasia,proveedor.observaciones,proveedor.cuit,proveedor.iva,proveedor.id_proveedor,razon_social,numero_cuenta FROM general.proveedor left join cuentas on (proveedor.id_proveedor=cuentas.id_proveedor and es_default=1) WHERE razon_social ilike '$filtro%' order by razon_social";		  
 $datos_proveedor=$db->Execute($query) or die ($db->ErrorMsg()."<br>".$query);
 ?>
 <script>
 <?
 while(!$datos_proveedor->EOF)
 {
 	
 	$prov_name=ereg_replace("\r\n","<br>",$datos_proveedor->fields['razon_social']);
    $prov_name=ereg_replace("\n","<br>",$prov_name);
    $prov_name=ereg_replace("\""," ",$prov_name);
    $prov_name=ereg_replace("'"," ",$prov_name);
 	$prov_obs=ereg_replace("\r\n","<br>",$datos_proveedor->fields['observaciones']);
    $prov_obs=ereg_replace("\n","<br>",$prov_obs);
    $prov_obs=ereg_replace("\""," ",$prov_obs);
    $prov_obs=ereg_replace("'"," ",$prov_obs);
 	$prov_iva=ereg_replace("\r\n","<br>",$datos_proveedor->fields['iva']);
    $prov_iva=ereg_replace("\n","<br>",$prov_iva);
    $prov_iva=ereg_replace("\""," ",$prov_iva);
    $prov_iva=ereg_replace("'"," ",$prov_iva);
 	$prov_cuit=ereg_replace("\r\n","<br>",$datos_proveedor->fields['cuit']);
    $prov_cuit=ereg_replace("\n","<br>",$prov_cuit);
    $prov_cuit=ereg_replace("\""," ",$prov_cuit);
    $prov_cuit=ereg_replace("'"," ",$prov_cuit);
 	$prov_fanta=ereg_replace("\r\n","<br>",$datos_proveedor->fields['nbre_fantasia']);
    $prov_fanta=ereg_replace("\n","<br>",$prov_fanta);
    $prov_fanta=ereg_replace("\""," ",$prov_fanta);
    $prov_fanta=ereg_replace("'"," ",$prov_fanta);
   
    
 ?>
 proveedores[<?=$datos_proveedor->fields['id_proveedor'];?>] = new Array();
 proveedores[<?=$datos_proveedor->fields['id_proveedor'];?>]['nombre'] = '<?=$prov_name;?>';
 proveedores[<?=$datos_proveedor->fields['id_proveedor'];?>]['cuit'] = '<?=$prov_cuit;?>';
 proveedores[<?=$datos_proveedor->fields['id_proveedor'];?>]['iva'] = '<?=$prov_iva;?>';
 proveedores[<?=$datos_proveedor->fields['id_proveedor'];?>]['observaciones'] = '<?=$prov_obs;?>';
 proveedores[<?=$datos_proveedor->fields['id_proveedor'];?>]['fantasia'] = '<?=$prov_fanta;?>';
 <?
 $datos_proveedor->MoveNext();
 }
 ?>
 </script>
 <?
 echo "<table width='100%' align='center'>
   <tr>
	 <td colspan='2' width='100%' align='center'>
        
		<select name='select_proveedor' size='12' style='width:85%' onKeypress='buscar_op(this);' onblur='borrar_buffer()' onclick='borrar_buffer()' onchange='buscar_prov(this.options[this.selectedIndex].value)'>";

		  if ($_POST['postear']=='sip' || $_POST['postear']=='sipok')
							  $tipo_valor="post";
		  if($_POST['Nuevo']=='Nuevo')
							  $tipo_valor="vacio";
		  if($pagina=="listado")
							  $tipo_valor="base";

           $cantidad_proveedores=$datos_proveedor->RecordCount();
		 $datos_proveedor->Move(0);
		  while(!$datos_proveedor->EOF)
		  {?>
		   
		   <option value='<?=$datos_proveedor->fields['id_proveedor']?>'>
		     <?=$datos_proveedor->fields['razon_social']?>
		   </option>
          <?
		   $datos_proveedor->MoveNext();
		  }
?>
   </select>

	</td>
  </tr>
 <? 
}//de la funcion generar_parte_derecha


function tabla_filtros_nombres($link,$ie) {

$color="#BA0105";
 $abc=array("a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","ñ","o","p","q",
			"r","s","t","u","v","w","x","y","z");

$cantidad=count($abc);

echo "<table align='center' width='80%'  height='80%' bgcolor='$color'>";
echo "<input type=hidden name='filtro' value=''";
	echo "<tr>";
	for($i=0;$i<$cantidad;$i++){
		$letra=$abc[$i];
	   switch ($i) {
					 case 9:
					 case 18:
					 case 27:echo "</tr><tr>";
						  break;
				   default:
				  } //del switch
?>
<td style='cursor:hand' onclick="document.all.filtro.value='<?=$letra?>';document.form1.submit();"><font color='#FDF2F3'><b><?=$letra?></b></font></td>
<?
}//del for
   echo "</tr>";
   echo "<tr>";
   echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='%';document.form1.submit();\"><font color='#FDF2F3'><b> Todos</b></font>";
   echo "</td>";
   echo "</tr>";
   echo "</table>";
}
//de la funcion  para las letras en la lista de entidades y proveedores

include("../ayuda/ayudas.php");
echo $html_header;
?>

<body bgcolor="#E0E0E0">
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/funciones.js"></script>
<SCRIPT>

var proveedores = new Array();

function buscar_prov(id_proveedor)
{
 document.all.nombre_prov.value = proveedores[id_proveedor]['nombre'];
 document.all.cuit_prov.value = proveedores[id_proveedor]['cuit'];
 document.all.iva_prov.value = proveedores[id_proveedor]['iva'];
 document.all.observaciones_prov.value = proveedores[id_proveedor]['observaciones'];
 document.all.fantasia_prov.value = proveedores[id_proveedor]['fantasia'];
}

function control_select()
{

 if(document.all.select_proveedor.value=="")
  {alert('Debe seleccionar un proveedor');
   return false;
  }
 return true;
}

</SCRIPT>



<form name="form1" method="POST" action="<?=$link?>">
<input type="hidden" name="onclick_salir" value="<?=$onclick_salir;?>">
<input type="hidden" name="onclick_cargar" value="<?=$onclick_cargar;?>">
<?php

if($letra!="")
 $filtro=$letra;
elseif($_POST['filtro']=="")
 $filtro="a";
elseif($_POST['filtro']=="todos")
 $filtro=""; 
else
 $filtro=$_POST['filtro'];
?>
<TABLE width='100%' align='center' border='0' cellspacing='2' cellpadding='4'>
<tr id=mo>
<td ><b>
			   <font color='#FDF2F3' size="2">
			   PROVEEDORES
               </font>
              </b>
	  </td>
  </tr>
</table>
<br>
<table width='100%'>
<tr>
 <td width='60%' valign="top">
 <table>
 <tr>
 <td><strong>Nombre</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <input type="text" name="nombre_prov" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
 </td>
 </tr>
 <tr>
 <td><strong>CUIT</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <input type="text" name="cuit_prov" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
 </td>
 </tr>
 <tr>
 <td><strong>IVA</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <input type="text" name="iva_prov" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
 </td>
 </tr>
 <tr>
 <td><strong>Observaciones</strong>&nbsp;&nbsp;
 <input type="text" name="observaciones_prov" value="" size="40" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
 </td>
 </tr>
 <tr>
 <td><strong>Fantasia</strong>
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <input type="text" name="fantasia_prov" value="" size="40" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
 </td>
 </tr>
 </table>
 </td>
 <td width='40%'>
  <?
	tabla_filtros_nombres("","egreso");
	generar_parte_proveedores();
  echo "<script>document.all.filtro.value='$filtro';</script>";
 ?>
 </td>
 </tr>
<tr>
<td colspan="2">
<br>
<hr>
<input type="button" name="cargar_prov" value="Cargar Proveedor" onclick="if (control_select()){<?=$onclick_cargar;?>}">&nbsp;&nbsp;
<input type="button" name="Salir" value="Salir" onclick="<?=$onclick_salir;?>">
</td>
</tr>
</table>
</form>
<?=fin_pagina();?>