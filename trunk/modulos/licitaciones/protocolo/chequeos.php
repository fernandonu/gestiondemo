<?php
//include "lib.php";
require_once "../../../config.php";
require_once "func.inc.php";
global $boton;
if (isset($_POST['seteada']))
 require_once("guardar_chequeos.php");

$link=encode_link("chequeos.php",array ("licitacion" => $parametros['licitacion'],
                                         "renglon" => $parametros['renglon']));                                           

if ($_POST['boton'] == "Modificar Protocolo")
{
	//redireccionar a la pagina para modificar el protocolo
	
}

/**************************************/
//@usuario viene registrada o por post
//$usuario="programador";
$usuario=$_ses_user_login;
/****************************************/

//chequea si el protocolo existe
/*if (!chkprotocolo($parametros['licitacion'],$parametros['renglon'],$parametros['item']))
{
 error_mens("Protocolo Inexistente","El protocolo requerido aun no se ha creado");
 die();
}*/

//$usuario=$_POST['usuario'];
//se recuperan todas las versiones del protocolo
$query_g="SELECT * FROM protocolo where 
nro_licitacion='".$parametros['licitacion']."' AND 
id_renglon='". $parametros['renglon']."';";


$resultado = $db->Execute($query_g) or die($query_g);
$filas_encontradas=$resultado->RecordCount();

$user_ver=1;//la version del protocolo que modifica el usuario
$user_ver_check=-1; //indice de arreglo para filas con checkbox
$user_ver_index=-1;//indice de arreglo para filas con datos
$ver_max=1;
$ver_max_index=0; //indice de arreglo para las filas con datos
$msg="";//para imprimir el mensaje de modificado o no

//si se encontro 1+ se debe crear una copia de la mayor version
if ($filas_encontradas)
{
  for($i=0; $i < $filas_encontradas; $i++)
  {if ($resultado->fields['nro_version'] > $ver_max)
  	{
  	 $ver_max=$resultado->fields['nro_version'];
  	 //controla que sea el original
  	 if (($resultado->fields['check_gati']!='f' && $resultado->fields['check_gati']!='t')&&
  	 	($resultado->fields['check_imred']!='f' && $resultado->fields['check_imred']!='t'))
  	 {
  	 	  $ver_max_index=$i;
		  if ($resultado->fields['usuario']==$_ses_user_login)
  	 	  {
  	 	  	$user_ver_index=$i;
  	 	  	$user_ver_check=$i;
		    $user_ver=$resultado->fields['nro_version'];
  	 	  }
  	 }
  	}
  	if ($resultado->fields['usuario']==$usuario)
  	{
      if  ($resultado->fields['nro_version'] >= $user_ver)
      {	
       //controla que no sea una fila con checkbox (el original)
  		if (($resultado->fields['check_gati']!='f' && $resultado->fields['check_gati']!='t') &&
  	        ($resultado->fields['check_imred']!='f' && $resultado->fields['check_imred']!='t'))
  	      $user_ver_index=$i;
  	    else 
  	    {
  	     $user_ver_check=$i;
         //$user_ver_index=$i; comentado para probar
  	    } 
  	    $user_ver=$resultado->fields['nro_version'];
      }
  	}
  $resultado->MoveNext();
  }
}
if ($ver_max > 1)
	{
     //advertir que se modifico el original
	 $msg="<font color='red'><b>Protocolo Modificado</b></font>";
	}
$resultado->Move($ver_max_index);
$msg.="Autor: ".$resultado->fields['usuario']. "<br>
       Fecha: ".$resultado->fields['fecha_modif'];  

/******************************************/
//PRECONDICION: el protocolo ya existe

//si entro chequeo y no modifico
    $resultado->Move($user_ver_check);
	$check[0]=$resultado->FetchRow();
	$resultado->Move($user_ver_index);
	$resultados[0]=$resultado->FetchRow();
	//si entro chequeo y luego modifico o
//si entra por primera vez

if ($user_ver_index==-1)
{$resultado->Move($user_ver_check);
 $check[0]=$resultado->FetchRow();
 $resultado->Move($ver_max_index);
 $resultados[0]=$resultado->FetchRow(); 
 $resultado=$resultados;
}
$resultado=$resultados;
/*

echo "user_ver_check=$user_ver_check <br>";
echo "user_ver_idex=$user_ver_index <br>";
echo "user_ver=$user_ver<br>";
echo "ver_max_index=$ver_max_index<br>";
echo "ver_max =$ver_max <br>";
echo "ver_datos =".$resultado[0]['nro_version']."<br>";
echo "ver_datos2 =".$resultado[$ver_max_index]['nro_version'];
echo "ver_datos2_micro_modelo =".$resultado[$ver_max_index]['micro_modelo'];

die();
*/


?>
<html>
<head>
<title>Chequeo de Protocolos</title>
<LINK href="./style.css" type="text/css" rel="stylesheet">
<SCRIPT language="JavaScript" src="funciones.js">
</SCRIPT>
<script languaje="javascript">
function cambiar_form(etiqueta)
{var primerap;
 var longitud;
 primerap=document.all.link2.value+'#'+etiqueta;
 form1.action=primerap;
 form1.submit()
} 
</script>
</head>
<body bgcolor="#E0E0E0">
<form name="form1" action="<?php echo $link; ?>" method="POST">
<input type="hidden" name="link2" value="<?php echo $link; ?>">
<input type="hidden" name="usuario" value="<?php echo $_ses_user_login; ?>">
<input type="hidden" name="seteada" value="<?php if (!isset($_POST['seteada'])) echo "1";?>">
<input type="hidden" name="licitacion" value="<?php echo $parametros['licitacion']; ?>">
<input type="hidden" name="renglon" value="<?php echo $parametros['renglon']; ?>">
<!--<input type="hidden" name="item" value="<?php echo $_POST['item']; ?>">-->
<!-- <div style="position:relative; width=30%; height:60%; overflow:auto;"> -->
<? echo $msg;
?>
<?php
$cantidad=0;
$prefijo="check";

?>
<input type="hidden" name="tipo_protocolo" value="<?php echo $resultado[0]['tipo']; ?>">
<?php
if (($resultado[0]['tipo']=='pc') || ($resultado[0]['tipo']=='pc+impresora'))
{
if ($resultado[0]['tipo']=='pc')
 $protocolo=1;
else
 $protocolo=4;
 if ($resultado[0]['datamation']=='t')  
    {$iso="Datamation";
?>   <input type="hidden" name="data" value="1">
     <input type="hidden" name="iso" value="0">
<?php
    }
    else 
    {
    $iso="ISO";
?>  <input type="hidden" name="data" value="0">
    <input type="hidden" name="iso" value="1">
<?php
 }
?>
  <input type="hidden" name="iso_tipo" value="<?php echo $resultado[0]['iso_tipo'] ?>">
    <table name="tabla" border="0" cellspacing="0" cellpadding="0" width="100%">
    <a name="iso"> 
    <th colspan="3" align="left"><b>Protocolo PC</b></th>
    <tr bgcolor="FFFFFF"> 
        <td valign="bottom" rowspan="2"> 
         <?php $cantidad++; ?>
         <input type="checkbox" name="pc[iso]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_iso']=="t") echo "checked"; ?> onclick="cambiar_form('iso');">
        </td>
        <td><font color=#006699><b><?php echo $iso; ?></b></font></td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
      <a name="gabinete_pc">
      <td><font color=#006699>BIOS</font></td>
      <td id="d"><b><font size="2"><?php echo $resultado[0]['iso_tipo']; ?></font></b></td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" rowspan="2" id="ar"> 
          <?php $cantidad++; ?>
        <input type="checkbox" name="pc[gabinete_tipo]" id="<?php echo $prefijo.$cantidad;?>" value="t" <?php if($check[0]['check_gati']=="t") echo "checked"; ?> onclick="cambiar_form('gabinete_pc');">
        </td>
        <td id="ar"><b><font color=#006699>Gabinete</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td><font color=#006699>Tipo</font></td> 
       <td id="d"><b><font size="2"><?php echo $resultado[0]['gabinete_tipo']; ?></font></b></td>
       <input type="hidden" name="pc_gabinete_tipo" value="<?php echo $resultado[0]['gabinete_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="micro_modelo_pc">
        <td valign="bottom" rowspan="2" id="aryayi1"> 
          <?php $cantidad++; ?>
         <input type="checkbox" name="pc[micro_modelo]" id="<?php echo $prefijo.$cantidad;?>" value="t" <?php if($check[0]['check_mimo']=="t") echo "checked"; ?> ="form1.submit();" onclick="cambiar_form('micro_modelo_pc');">
         </td>
        <td id="ar"><b><font color=#006699>Micro</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="micro_velocidad_pc">
       <td id="a1"><font color=#006699>Modelo</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['micro_modelo']; ?></font></b></td>
        <input type="hidden" name="pc_micro_modelo" value="<?php echo $resultado[0]['micro_modelo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
       <?php $cantidad++; ?>
       <td valign="bottom" rowspan="2" id="i"><input type="checkbox" name="pc[micro_velocidad]" id="<?php echo $prefijo.$cantidad;?>" value="t" <?php if($check[0]['check_mive']=="t") echo "checked"; ?> onclick="cambiar_form('micro_velocidad_pc');">
        </td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="memoria_tipo_pc">  
      <td><font color=#006699>Velocidad</font></td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['micro_velocidad'];?></font></b></td>
        <input type="hidden" name="pc_micro_velocidad" value="<?php echo $resultado[0]['micro_velocidad']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryayi1">
        <input type="checkbox" name="pc[mem_tipo]"  value="t" <?php if($check[0]['check_meti']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('memoria_tipo_pc');">
       </td>
        <td id="ar"><font color=#006699><b>Memoria</b></font></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
       <a name="memoria_tamaño_pc">
       <td id="a1"><font color=#006699>Tipo</font></td>
       <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['memoria_tipo']; ?></FONT></b></td>
       <input type="hidden" name="pc_memoria_tipo" value="<?php echo $resultado[0]['memoria_tipo']; ?>" onclick="form1.submit();">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
       <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="pc[mem_tamaño]"  value="t" <?php if($check[0]['check_meta']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('memoria_tamaño_pc');"></td>
       <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <a name="disco_tipo_pc"> 
       <td><font color=#006699>Tamaño</font></td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['memoria_tam']; ?></font></b></td>
        <input type="hidden" name="pc_memoria_tam" value="<?php echo $resultado[0]['memoria_tam']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryayi1">
        <input type="checkbox" name="pc[disco_tipo]" value="t" <?php if($check[0]['check_diti']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('disco_tipo_pc');"></td>
       <td id="ar"><font color=#006699><b>Disco</b></font></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="disco_tamaño_pc">
       <td id="a1"><font color=#006699>Tipo</font></td>
       <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['disco_tipo']; ?></font></b></td>
       <input type="hidden" name="pc_disco_tipo" value="<?php echo $resultado[0]['disco_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
      <td rowspan="2" valign="bottom" id="ayi1">
        <input type="checkbox" name="pc[disco_tamaño]" value="t" <?php if($check[0]['check_dita']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('disco_tamaño_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="disco_bus_pc">
       <td  id="a1"><font color=#006699>Tamaño</font></td>
       <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['disco_tamaño']; ?></font></b></td>
       <input type="hidden" name="pc_disco_tamaño" value="<?php echo $resultado[0]['disco_tamaño']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1">
         <input type="checkbox" name="pc[disco_bus]" value="t" <?php if($check[0]['check_dibu']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('disco_bus_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
       <a name="disco_rpm_pc"> 
       <td id="a1"><font color=#006699>Bus</font></td> 
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['disco_bus']; ?></font></b></td>
        <input type="hidden" name="pc_disco_bus" value="<?php echo $resultado[0]['disco_bus']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
         <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="pc[disco_rpm]" value="t" <?php if($check[0]['check_dirpm']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('disco_rpm_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <td><font color=#006699>RPM</font></td>
      <a name="video_tipo_pc">
        <td id="d"><b><font size="2"><?php echo $resultado[0]['disco_rpm']; ?></font></b></td>
        <input type="hidden" name="pc_disco_rpm" value="<?php echo $resultado[0]['disco_rpm']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryayi1">
        <input type="checkbox" name="pc[video_tipo]" value="t" <?php if($check[0]['check_viti']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('video_tipo_pc');"></td>
        <td id="ar"><font color=#006699><b>Video</b></font></td>
        <td id="ar">&nbsp</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="video_memoria_pc">
      <td id="a1"><font color=#006699>Tipo</font></td>
       <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['video_tipo']; ?></font></b></td>
       <input type="hidden" name="pc_video_tipo" value="<?php echo $resultado[0]['video_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
       <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="pc[video_memoria]" value="t" <?php if($check[0]['check_vita']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('video_memoria_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <a name="monitor_tamaño_pc">
      <td><font color=#006699>Memoria</font></td>
       <td  id="d"><b><font size="2"><?php echo $resultado[0]['video_tamaño']; ?></font></b></td>
       <input type="hidden" name="pc_video_tamaño" value="<?php echo $resultado[0]['video_tamaño']; ?>">
      </tr>
      <input type="hidden" name="pc_monitor_tamaño" value="<?php echo $resultado[0]['monitor_tamaño']; ?>">
      <?PHP if ($resultado[0]['monitor_tamaño']!="NO")
      		{ ?>
      <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
      <td rowspan="2" valign="bottom" id="aryi1">
        <input type="checkbox" name="pc[monitor_tamaño]" value="t" <?php if($check[0]['check_mota']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('monitor_tamaño_pc');"></td>
        <td id="ar"><font color=#006699><b>Monitor</b></font></td>
        <td id="ar">&nbsp;</td>
      </tr>
      		<tr bgcolor="FFFFFF"> 
      		<td><font color=#006699>Tamaño</font></td>
      		<td id="d"><b><font size="2"><?php echo $resultado[0]['monitor_tamaño']; ?></font></b></td></tr>
<?php
      		}
if (($resultado[0]['multimedia_sonido']!="NO") || ($resultado[0]['multimedia_parlantes']!="NO") ||
    ($resultado[0]['multimedia_mic']!="NO") || ($resultado[0]['multimedia_cd']!="NO") || ($resultado[0]['multimedia_cdwr']!="NO") ||
    ($resultado[0]['multimedia_dvd']!="NO"))
 {
?>
<tr bgcolor="FFFFFF"> 
   <td id="ar">&nbsp;</td>
   <a name="multimedia_sonido_pc">
   <td id="ar"><b><font color=#006699>Multimedia</font></b></td>
   <td id="ar">&nbsp;</td>
   </tr>
<?php
 }
?>
<input type="hidden" name="pc_multimedia_sonido" value="<?php echo $resultado[0]['multimedia_sonido']; ?>">
<?
if ($resultado[0]['multimedia_sonido']!="NO")
{
?> 
   <tr bgcolor="FFFFFF"> 
   <?php $cantidad++; ?>
   <td id="ayi1"><input type="checkbox" name="pc[multimedia_sonido]" value="t" <?php if($check[0]['check_muso']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('multimedia_sonido_pc');"></td>
   <td id="a1"><font color=#006699>Sonido</font></td>
   <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['multimedia_sonido']; ?></font></b></td>
   </tr>
<?php
}
?>
<input type="hidden" name="pc_multimedia_parlantes" value="<?php echo $resultado[0]['multimedia_parlantes']; ?>">
<?php
if ($resultado[0]['multimedia_parlantes']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
       <a name="multimedia_parlantes_pc">
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1">
        <input type="checkbox" name="pc[multimedia_parlantes]" value="t" <?php if($check[0]['check_mupa']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('multimedia_parlantes_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <td id="a1"><font color=#006699>Parlantes</font></td>
       <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['multimedia_parlantes']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_multimedia_mic" value="<?php echo $resultado[0]['multimedia_mic']; ?>">
<?php
if ($resultado[0]['multimedia_mic']!="NO")
{
?>
     <a name="multimedia_mic_pc"> 
     <tr bgcolor="FFFFFF"> 
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1">
        <input type="checkbox" name="pc[multimedia_mic]" value="t" <?php if($check[0]['check_mumic']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('multimedia_mic_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td id="a1"><font color=#006699>Microfono</font></td>
       <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['multimedia_mic']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_multimedia_cd" value="<?php echo $resultado[0]['multimedia_cd']; ?>">
<?php
if ($resultado[0]['multimedia_cd']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <a name="multimedia_cd_pc">
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="pc[multimedia_cd]" value="t" <?php if($check[0]['check_mucd']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('multimedia_cd_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <td id="a1"><font color=#006699>CD</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['multimedia_cd']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_multimedia_cdwr" value="<?php echo $resultado[0]['multimedia_cdwr']; ?>">
<?php
if ($resultado[0]['multimedia_cdwr']!="NO")
{
?>
       <tr bgcolor="FFFFFF"> 
       <a name="multimedia_cdwr_pc">
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1">
        <input type="checkbox" name="pc[multimedia_cdwr]" value="t" <?php if($check[0]['check_mucdwr']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('multimedia_cdwr_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <td id="a1"><font color=#006699>CDWR</font></td>
       <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['multimedia_cdwr']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_multimedia_dvd" value="<?php echo $resultado[0]['multimedia_dvd']; ?>">
<?php
if ($resultado[0]['multimedia_dvd']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <a name="multimedia_dvd_pc">
         <?php $cantidad++; ?>
     <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="pc[multimedia_dvd]" value="t" <?php if($check[0]['check_mudvd']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('multimedia_dvd_pc');"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      <tr bgcolor="FFFFFF">
       <td><font color=#006699>DVD</font></td>
       <td id="d"><b><font size="2"><?php echo $resultado[0]['multimedia_dvd']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_teclado_mouse" value="<?php echo $resultado[0]['teclado_mouse']; ?>">
<?php
if ($resultado[0]['teclado_mouse']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <a name="teclado_mouse_pc">
        <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryi1"><input type="checkbox" name="pc[teclado]" value="t" <?php if($check[0]['check_temo']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('teclado_mouse_pc');"></td>
        <td id="ar"><font color=#006699><b>Tecl./Mouse</b></font></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td colspan="2" id="d"><font size="2"><b><?php echo $resultado[0]['teclado_mouse']; ?></b></font></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_lan_tipo" value="<?php echo $resultado[0]['lan_tipo']; ?>">
<?php
if ($resultado[0]['lan_tipo']!="NO")
{
?> 
      <tr bgcolor="FFFFFF"> 
      <a name="lan_tipo_pc">
        <?php $cantidad++; ?>
      <td rowspan="2" valign="bottom" id="aryi1"><input type="checkbox" name="pc[lan_tipo]" value="t" <?php if($check[0]['check_lati']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('lan_tipo_pc');"></td>
        <td id="ar"><font color=#006699><b>LAN</b></font></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <td><font color=#006699>Tipo</font></td>
       <td id="d"><b><font size="2"><?php echo $resultado[0]['lan_tipo']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="pc_software_oficina" value="<?php echo $resultado[0]['software_oficina']; ?>">
<input type="hidden" name="pc_software_so" value="<?php echo $resultado[0]['software_so']; ?>">
<?php
if (($resultado[0]['software_so']!="NO") || ($resultado[0]['software_oficina']!="NO"))
{
?>
<tr bgcolor="FFFFFF"> 
        <td id="ar">&nbsp;</td>
        <a name="software_pc">
        <td id="ar"><b><font color=#006699>Software</font></b></td>
        <td  id="ar">&nbsp;</td>
      </tr>
<?php
}
if ($resultado[0]['software_so']!="NO")
{
?>     
      <tr bgcolor="FFFFFF">
      <?php $cantidad++; ?>
      <td id="ayi1"><input type="checkbox" name="pc[software_so]" value="t" <?php if($check[0]['check_soso']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('software_pc');"></td>
       <td id="a1"><font color=#006699>Sist.Operativo</font></td> 
       <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['software_so']; ?></font></b></td>
      </tr>
<?php
}
if ($resultado[0]['software_oficina']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <a name="software_oficina_pc">
        <?php $cantidad++; ?>
        <td valign="bottom" rowspan="2" id="i"><input type="checkbox" name="pc[software_oficina]" value="t" <?php if($check[0]['check_soof']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('software_oficina_pc');">
        </td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td><font color=#006699>Oficina</font></td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['software_oficina']; ?></font></b></td>
        </td>
      </tr>
<?php
}
?>
      
      <tr bgcolor="FFFFFF"> 
      <a name="garantia">
        <?php $cantidad++; ?>
        <td id="aryi1"><input type="checkbox" name="pc[garantia]" value="t" <?php if($check[0]['check_gara']=="t") echo "checked"; ?> id="<?php echo $prefijo.$cantidad;?>" onclick="cambiar_form('garantia');"></td>
        <td id="ar"><b><font color=#006699>Garantia</font></b></td>
        <td id="ar"><b><font size="2"><?php echo $resultado[0]['garantia']; ?></font></b></td>
        <input type="hidden" name="pc_garantia" value="<?php echo $resultado[0]['garantia']; ?>">
      </tr>
   </table>
<?php
}
if (($resultado[0]['tipo']=='servidor') || ($resultado[0]['tipo']=='servidor+impresora'))
{if ($resultado[0]['tipo']=='servidor')
  $protocolo=2;
 else
  $protocolo=5;
?> 
   <table name="tabla" border="0" cellspacing="0" cellpadding="0" width="100%">
    <th colspan="3" align="left" id="ab"><b>Protocolo Servidor</b></th>
     <tr bgcolor="FFFFFF"> 
        <td valign="bottom" rowspan="2" id="i"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="servidor[gabinete_tipo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_gati']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td><b><font color=#006699>Gabinete</font></b></td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td><font color=#006699>Tipo</font></td> 
       <td id="d"><b><font size="2"><?php echo $resultado[0]['gabinete_tipo']; ?></font></b></td>
       <input type="hidden" name="servidor_gabinete_tipo" value="<?php echo $resultado[0]['gabinete_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" rowspan="2" id="aryayi1"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="servidor[micro_cantidad]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_mica']=="t") echo "checked"; ?> onclick="form1.submit();"> 
        </td>
        <td id="ar"><b><font color=#006699>Micro</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td id="a1"><font color=#006699>Cantidad</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['micro_cantidad']; ?></font></b></td>
        <input type="hidden" name="servidor_micro_cantidad" value="<?php echo $resultado[0]['micro_cantidad']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td valign="bottom" rowspan="2" id="ayi1"><input type="checkbox" name="servidor[micro_tipo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_mimo']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td id="a1"><font color=#006699>Tipo</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['micro_modelo'];?></font></b></td>
        <input type="hidden" name="servidor_micro_modelo" value="<?php echo $resultado[0]['micro_modelo']; ?>">
       </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="servidor[micro_cache]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_mich']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
       <td><font color=#006699>Cache</font></td>
       <td><b><font size="2"><?php echo $resultado[0]['micro_cache']; ?></font></b></td>
       <input type="hidden" name="servidor_micro_cache" value="<?php echo $resultado[0]['micro_cache']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryayi1"><input type="checkbox" name="servidor[mem_tipo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_meti']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td id="ar"><b><font color=#006699>Memoria</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td id="a1"><font color=#006699>Tipo</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['memoria_tipo']; ?></font></b></td>
        <input type="hidden" name="servidor_memoria_tipo" value="<?php echo $resultado[0]['memoria_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="<?php if ($resultado[0]['memoria_expansion']!="NO") echo "ayi1"; ?>"><input type="checkbox" name="servidor[mem_tamaño]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_meta']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td id="<?php if ($resultado[0]['memoria_expansion']!="NO") echo "a1"; ?>"><font color=#006699>Tamaño</font></td>
       <td id="<?php if ($resultado[0]['memoria_expansion']!="NO") echo "ayd1"; ?>"><b><font size="2"><?php echo $resultado[0]['memoria_tam']; ?></font></b></td>
       <input type="hidden" name="servidor_memoria_tam" value="<?php echo $resultado[0]['memoria_tam']; ?>">
      </tr>
<input type="hidden" name="servidor_memoria_expansion" value="<?php echo $resultado[0]['memoria_expansion']; ?>">
<?php
if($resultado[0]['memoria_expansion']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="servidor[mem_expansion]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_meex']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td><font color=#006699>Expansion</font></td>
       <td><b><font size="2"><?php echo $resultado[0]['memoria_expansion']; ?></font></b></td>
      </tr>
<?php
}
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryi1" id="ar"><input type="checkbox" name="servidor[video_tamaño]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_vita']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td id="ar"><b><font color=#006699>Video</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
        <td><font color=#006699>Tamaño</font></td> 
        <td id="d"><b><font size="2"><?php echo $resultado[0]['video_tamaño']; ?></font></b></td>
        <input type="hidden" name="servidor_video_tamaño" value="<?php echo $resultado[0]['video_tamaño']; ?>">        
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryi1"><input type="checkbox" name="servidor[expansion_pci]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_expc']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td id="ar"><b><font color=#006699>Expansion</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td><font color=#006699>PCI</font></td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['expansion_pci']; ?></font></b></td>
        <input type="hidden" name="servidor_expansion_pci" value="<?php echo $resultado[0]['expansion_pci']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="aryayi1"><input type="checkbox" name="servidor[storage_tipo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_stti']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td id="ar"><font color=#006699><b>Storage</b></font></td>
        <td id="ar">&nbsp</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td id="a1"><font color=#006699>Tipo</font></td>
       <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['storage_tipo']; ?></font></b></td>
       <input type="hidden" name="servidor_storage_tipo" value="<?php echo $resultado[0]['storage_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[storage_interface]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_stin']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td id="a1"><font color=#006699>Interface</font></td>
       <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['storage_interface']; ?></font></b></td>
       <input type="hidden" name="servidor_storage_interface" value="<?php echo $resultado[0]['storage_interface']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[storage_rpm]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_rpm']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
      <td id="a1"><font color=#006699>RPM</font></td>
      <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['storage_rpm']; ?></font></b></td>
      <input type="hidden" name="servidor_storage_rpm" value="<?php echo $resultado[0]['storage_rpm']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[storage_tamaño]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_stta']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td id="a1"><font color=#006699>Tamaño</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['storage_tamaño']; ?></font></b></td>
        <input type="hidden" name="servidor_storage_tamaño" value="<?php echo $resultado[0]['storage_tamaño']; ?>">
      </tr>
<input type="hidden" name="servidor_storage_cantidad" value="<?php echo $resultado[0]['storage_cantidad']; ?>">
<?php
if ($resultado[0]['storage_cantidad']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[storage_cantidad]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_stca']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td id="a1"><font color=#006699>Cantidad</font></td>
       <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['storage_cantidad']; ?></font></b></td>
      </tr>
<?php
}
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[storage_raid]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_straid']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td id="a1"><font color=#006699>RAID</font></td>
        <td  id="ayd1"><b><font size="2"><?php echo $resultado[0]['storage_raid']; ?></font></b></td>
        <input type="hidden" name="servidor_storage_raid" value="<?php echo $resultado[0]['storage_raid']; ?>">        
      </tr>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[backup_tipo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_bati']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td><font color=#006699><b>Backup</b></font></td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td id="a1"><font color=#006699>Tipo</font></td>
        <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['backup_tipo']; ?></font></b></td>
        <input type="hidden" name="servidor_backup_tipo" value="<?php echo $resultado[0]['backup_tipo']; ?>">
      </tr>
<input type="hidden" name="servidor_backup_extras" value="<?php echo $resultado[0]['backup_extras']; ?>">
      <?php
if($resultado[0]['backup_extras']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ayi1"><input type="checkbox" name="servidor[backup_extras]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_bamo']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
       <td id="a1"><font color=#006699>Extras</font></td>
       <td id="ayd1"><b><font size="2"><?php echo $resultado[0]['backup_extras']; ?></font></b></td>
      </tr>
<?php
}
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom"><input type="checkbox" name="servidor[backup_modelo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_baex']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td><font color=#006699>Modelo</font></td>
       <td><b><font size="2"><?php echo $resultado[0]['backup_modelo']; ?></font></b></td>
       <input type="hidden" name="servidor_backup_modelo" value="<?php echo $resultado[0]['backup_modelo']; ?>">
      </tr>
<input type="hidden" name="servidor_monitor_tamaño" value="<?php echo $resultado[0]['monitor_tamaño']; ?>">
<input type="hidden" name="servidor_monitor_rack" value="<?php echo $resultado[0]['monitor_rack']; ?>">
<?php
if (($resultado[0]['monitor_tamaño']!="NO") || ($resultado[0]['monitor_rack']!="NO"))
{
?>
<tr bgcolor="FFFFFF"> 
        <td id="ar">&nbsp;</td>
        <td id="ar"><font color=#006699><b>Monitor</b></font></td>
        <td id="ar">&nbsp;</td>
      </tr>
<?php
}
if ($resultado[0]['monitor_tamaño']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
       <?php $cantidad++; ?>
       <td id="ayi1"><input type="checkbox" name="servidor[monitor_tamaño]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_mota']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
       <td id="<?php if ($resultado[0]['monitor_rack']!="NO") echo "a1"; ?>"><font color=#006699>Tamaño</font></td>
       <td id="<?php if ($resultado[0]['monitor_rack']!="NO") echo "ayd1"; ?>"><b><font size="2"><?php echo $resultado[0]['monitor_tamaño']; ?></font></b></td>
      </tr>
<?php
}

if ($resultado[0]['monitor_rack']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="i"><input type="checkbox" name="servidor[monitor_rack]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_mora']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td>&nbsp;</td>
        <td id="d">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
       <td><font color=#006699>Rackeable</font></td>
       <td id="d"><b><font size="2"><?php echo $resultado[0]['monitor_rack']; ?></font></b></td>
      </tr>
<?php
}
?>
<input type="hidden" name="servidor_teclado_mouse" value="<?php echo $resultado[0]['teclado_mouse']; ?>">
<?php
if($resultado[0]['teclado_mouse']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td rowspan="2" valign="bottom" id="ar"><input type="checkbox" name="servidor[teclado]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_tecla']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td id="ar"><b><font color=#006699>Tecl/Mouse</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF">
       <td><b><font size="2"><?php echo $resultado[0]['teclado_mouse']; ?></font></b></td>
       <td>&nbsp;</td>
      </tr>
<?php
}
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td valign="bottom" rowspan="2" id="aryi1"><input type="checkbox" name="servidor[swich_ports]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_switch']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="ar"><b><font color=#006699>Swich</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td><font color=#006699>Ports</font></td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['switch_ports']; ?></font></b></td>
        <input type="hidden" name="servidor_switch_ports" value="<?php echo $resultado[0]['switch_ports']; ?>">
        </td>
      </tr>
<input type="hidden" name="servidor_software_so" value="<?php echo $resultado[0]['software_so']; ?>">
<?php
if ($resultado[0]['software_so']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td valign="bottom" rowspan="2" id="aryi1"><input type="checkbox" name="servidor[sistema_oper]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_soso']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="ar"><b><font color=#006699>Software</font></b></td>
        <td id="ar">&nbsp;</td>
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td><font color=#006699>Sistema Operativo</td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['software_so']; ?></font></b></td>
      </tr>
<?php
}
?>
      <tr bgcolor="FFFFFF"> 
      <?php $cantidad++; ?>
        <td id="ar"><input type="checkbox" name="servidor[garantia]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_gara']=="t") echo "checked"; ?> onclick="form1.submit();"></td>
        <td id="ar"><b><font color=#006699>Garantia</font></b></td>
        <td id="ar"><b><font size="2"><?php echo $resultado[0]['garantia']; ?></font></b></td>
        <input type="hidden" name="servidor_garantia" value="<?php echo $resultado[0]['garantia']; ?>">
      </tr>
   </table>
<?php
}
if (($resultado[0]['tipo']=='impresora') || ($resultado[0]['tipo']=='pc+impresora') || ($resultado[0]['tipo']=='servidor+impresora'))
{
if ($resultado[0]['tipo']=='impresora')
  $protocolo=3;
 else
  if ($resultado[0]['tipo']=='pc+impresora')
   $protocolo=4;
  else 
  if ($resultado[0]['tipo']=='servidor+impresora')
   $protocolo=5;
?>
  
  <table name="tabla" border="0" cellspacing="0" cellpadding="0" width="100%">
   <th colspan="3" align="left" id="ab"><b>Protocolo Impresora</b></th>
    <tr bgcolor="FFFFFF"> 
        <td id="ayi"> 
         <?php $cantidad++; ?> 
         <input type="checkbox" name="impresora[tipo]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imti']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><font color=#006699><b>Tipo</b></font></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_tipo']; ?></font></b></td>
        <input type="hidden" name="impresora_tipo" value="<?php echo $resultado[0]['impresora_tipo']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
         <?php $cantidad++; ?> 
          <input type="checkbox" name="impresora[hojas]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imbe']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>Nª de Hojas</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_be']; ?></font></b></td>
        <input type="hidden" name="impresora_be" value="<?php echo $resultado[0]['impresora_be']; ?>">
      </tr>
      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[conexion]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imco']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>Conexion</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_conexion']; ?></font></b></td>
        <input type="hidden" name="impresora_conexion" value="<?php echo $resultado[0]['impresora_conexion']; ?>">
      </tr>
<input type="hidden" name="impresora_red" value="<?php echo $resultado[0]['impresora_red']; ?>">
<?php
if ($resultado[0]['impresora_red']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[interface]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imred']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>Interface</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_red']; ?></font></b></td>
       </tr>
<?php
}
?>
<input type="hidden" name="impresora_duplex" value="<?php echo $resultado[0]['impresora_duplex']; ?>">
<?php
if ($resultado[0]['impresora_duplex']!="NO")
{
?>

      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[duplex]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imdu']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>Duplex</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_duplex']; ?></font></b></td>
       </tr>
<?php
}
?>
<input type="hidden" name="impresora_extras" value="<?php echo $resultado[0]['impresora_extras']; ?>">
<?php
if ($resultado[0]['impresora_extras']!="NO")
{
?>
      <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[extras]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imex']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>Insumos Extras</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_extras']; ?></font></b></td>
       </tr>
<?php
}
?>
<input type="hidden" name="impresora_ppm" value="<?php echo $resultado[0]['impresora_ppm']; ?>">
<?php
if ($resultado[0]['impresora_ppm']!="NO")
{
?>
     <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[ppm]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imppm']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>PPM</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_ppm']; ?></font></b></td>
       </tr> 
<?php
}
?>
<input type="hidden" name="impresora_ram" value="<?php echo $resultado[0]['impresora_ram']; ?>">
<?php
if ($resultado[0]['impresora_ram']!="NO")
{
?>
    <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="ayi"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[ram]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imram']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td id="a"><b><font color=#006699>RAM</font></b></td>
        <td id="ayd"><b><font size="2"><?php echo $resultado[0]['impresora_ram']; ?></font></b></td>
       </tr>
<?php
}
?>
     <tr bgcolor="FFFFFF"> 
        <td valign="bottom" id="i"> 
        <?php $cantidad++; ?>
          <input type="checkbox" name="impresora[garantia]" value="t" id="<?php echo $prefijo.$cantidad;?>" <?php if($check[0]['check_imga']=="t") echo "checked"; ?> onclick="form1.submit();">
        </td>
        <td><b><font color=#006699>Garantia</font></b></td>
        <td id="d"><b><font size="2"><?php echo $resultado[0]['impresora_garantia']; ?></font></b></td>
        <input type="hidden" name="impresora_garantia" value="<?php echo $resultado[0]['impresora_garantia']; ?>">
       </tr>
    </table>
<?
}
?>
<br>
<font color=#006699><b>Comentarios:</b></font><br>
<textarea name="comentario"  rows="5" cols="14" readonly><?PHP echo $resultado[0]['comentarios'] ?></textarea>
<br>
<!--</div>-->
<input type="hidden" name="protocolo" value="<?php echo $protocolo; ?>"> 
<input type="hidden" name="tipo_protocolo" value="<?php echo $resultado[0]['tipo']; ?>"> 
<input type="hidden" name="cantidad" value="<?php echo $cantidad; ?>"> 
<input type="hidden" name="prefijo" value="<?php echo $prefijo; ?>"> 
<input type="hidden" name="version" value="<?php echo $user_ver; ?>">
<input type="button" name="boton" value="Modificar" onclick=
"
 if (confirm('Seguro que desea modificar el protocolo??'))
 {
 //abrir
 //  ventana_modificar=window.open('ver_protocolos.php','nombre ventana','otros parametros');
   
 };">
</form>
</html>