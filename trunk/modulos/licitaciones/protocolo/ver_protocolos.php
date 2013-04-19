<?php

//include "lib.php";

require_once "../../../config.php";
require_once "func.inc.php";

//RECIBE UNA VARIABLE PARA MODIFICAR O CREAR UN NUEVO PROTOCOLO
//@modificar: booleano
global $modificar;
$licitacion=$parametros["licitacion"] or $licitacion=$_POST["licitacion"];
switch ($_POST['boton'])
{case "Guardar":{require_once("guardar_protocolo.php");
                 break;
                }
 default:{$link=encode_link("ver_protocolos.php",array ("licitacion" => $parametros['licitacion'],
                                                        "renglon" => $parametros['renglon']));

?>

<SCRIPT LANGUAGE="JAVASCRIPT">
//chequea si el protocolo NO existe
var version_mayor;
version_mayor=<? echo chkprotocolo($parametros['licitacion'],$parametros['renglon'],$parametros['item']);?>;
if (version_mayor)
{
  //controlar primero desde que pagina viene para mandar el cartel

  if (confirm("El protocolo ya fue creado. MODIFICAR???"))
  {
   modificar=1;
  }
  else
  {
   history.go(-1);
  }

}

function dar_version()
{if (version_mayor==0)
  window.document.all.version.value=1;
 else
  window.document.all.version.value=version_mayor+1;
}
</SCRIPT>
<?

$query_g="SELECT max(nro_version) FROM protocolo where 
nro_licitacion=".$parametros['licitacion']." AND 
id_renglon=". $parametros['renglon'];


$resultado = $db->Execute($query_g) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();


if ($filas_encontradas && ($resultado->fields['max']!=''))
{
	$query_g="SELECT * FROM protocolo where 
	nro_licitacion=".$parametros['licitacion']." AND 
	id_renglon=". $parametros['renglon']." AND 
	nro_version=".$resultado->fields['max'];
	$resultado = $db->Execute($query_g) or die($db->ErrorMsg());
    $filas_encontradas=$resultado->RecordCount();
}

//NO BORRAR DECLARACION DE VARIABLE
$resultado_old;

/*********************ESTO ES LO QUE DEBE IR********************************
CONTROLAR QUE SE LE DE EL MAYOR NUMERO DE VERSION CUANDO SE GUARDA
*****************************************************************************/

/*//@usuario viene registrada o por post
$_POST['usuario']="programador";
//@version viene por POST
$_POST['version']=1;


//se recupera la version del protocolo que tiene el usuario
$query_g="SELECT * FROM protocolo where 
nro_licitacion=".$_POST['licitacion']." AND 
id_renglon=". $_POST['renglon']." AND 
nro_item=".$_POST['item']." AND "
."nro_version=". $_POST['version']. " AND 
usuario='" .$_POST['usuario']. "'";
$resultado_old;
ejecutar_query($query_g);
*/
if ($filas_encontradas)
{
  $GLOBALS['modificar']=1;
  $GLOBALS['resultado_old']=$GLOBALS['resultado'];
}
$GLOBALS['selecciono']=0;
?>

<html>
<head>
<title>Ver Protocolos</title>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
</head>
<body bgcolor="E0E0E0">
<form name="form1" action="<?php echo $link; ?>" method="post">
<p><b>Seleccione el tipo de protocolo que desea</b></p>
<select name="tipo_protocolo" onchange="form1.submit();">
<option value="1" <?php if ($_POST['tipo_protocolo']==1) echo "selected"; ?>>Protocolo PC</option>
<option value="2" <?php if ($_POST['tipo_protocolo']==2) echo "selected"; ?>>Protocolo Servidor</option>
<option value="3" <?php if ($_POST['tipo_protocolo']==3) echo "selected"; ?>>Protocola Impresora</option>
<option value="4" <?php if ($_POST['tipo_protocolo']==4) echo "selected"; ?>>Protocolo PC+Impresora</option>
<option value="5" <?php if ($_POST['tipo_protocolo']==5) echo "selected"; ?>>Protocolo Servidor+Impresora</option>
<option value="6" <?php if ($_POST['tipo_protocolo']==6) echo "selected"; ?>>Otros</option>
</select>
<br>
<input type="hidden" name="protocolo" value="<?php if (!(isset($_POST['tipo_protocolo']))) echo "1"; else echo $_POST['tipo_protocolo']; ?>">
<input type="hidden" name="licitacion" value="<?php echo $parametros['licitacion']; ?>">
<input type="hidden" name="item" value="<?php echo $_POST['item']; ?>">
<input type="hidden" name="renglon" value="<?php echo $parametros['renglon']; ?>">
<?php 

//mantengo historial para volver a la pagina indicada
if (!(isset($_POST['historial'])))
 $_POST['historial']=-1;
else
 $_POST['historial']+=-1;
?>
<input type="hidden" name="historial" value="<?php echo $_POST['historial']; ?>">
<?php
if (($_POST['tipo_protocolo']==1) || ($_POST['tipo_protocolo']==4) || !(isset($_POST['tipo_protocolo'])))
{
?>
<hr>
<left><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Protocolo PC
</b></font></left>
<hr>
<table>
<tr>
<td><left><u><font color=#006699><b>Datamation</b></font></u></left></td>

<? if($resultado->fields['datamation']=='t')
    echo "<td><input type='radio' name='check' value='1' checked></td>";
   else 
    echo "<td><input type='radio' name='check' value='1'></td>";
?> 
</tr>
<tr>
<td><u><font color=#006699><b>ISO</b></font></u></td>
<? if($resultado->fields['datamation']!='t')
    echo "<td><input type='radio' name='check' value='2' checked></td>";
   else 
    echo "<td><input type='radio' name='check' value='2'></td>";
?> 
</tr>
</table>
<?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='iso_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
?>
<table style="visibility:visible;" border="0" cellspacing="2" cellpadding="0">
    <tr bgcolor="CCCCCC"> 
      <td><u><font color=#006699><b><p id="iso_word">BIOS</p></b></font></u></td>
      <td></td>
      <td><select name="pc[iso_tipo]" id="iso_tipo" onchange="beginEditing(this)">
          <option value=-1 selected></option>
<?
$i=0;
while ($i<$filas_encontradas)
{?>
          <option 
			<? if ($resultado->fields['opcion']==$resultado_old->fields['iso_tipo'])
			   {
			   	echo " selected";
			   	$GLOBALS['selecciono']=1;
			   }	
            ?>
          > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['iso_tipo']."</option>";
}
else 
 $GLOBALS['selecciono']=0;

?>          
          <option id=editable>Edite aqui</option>
        </select>
        </td>
      <td><u><font color=#006699><b>Video</b></font></u></td>
      <td><font color=#006699>Tipo</font></td>
      <td><select name="pc[video_tipo]" id="pc_video_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='video_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['video_tipo'])
			   {
			   	echo " selected";
			   	$GLOBALS['selecciono']=1;
			   }	
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['video_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}

?>
      <option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>Tecl/Mouse</b></font></u></td>
      <td></td>
      <td><select name="pc[teclado]" id="pc_teclado" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='teclado_mouse';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['teclado_mouse'])
          							   { 
          							   	echo " selected";
          							   	$GLOBALS['selecciono']=1;
          							   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['teclado_mouse']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
      <option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><font color=#006699></font></td>
    </tr>
    <tr bgcolor="CCCCCC"> 
      <td><u><font color=#006699><b>Micro</b></font></u></td>
      <td><font color=#006699>Modelo</font></td>
      <td><select name="pc[micro_modelo]" id="pc_micro_modelo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='micro_modelo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['micro_modelo'])
			   {
			   	 echo " selected";
			   	 $GLOBALS['selecciono']=1;
			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['micro_modelo']."</option>";
}
else 
{
 $GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td><font color=#006699>Memoria</font></td>
      <td><select name="pc[video_tamaño]" id="pc_video_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='video_tamaño';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['video_tamaño'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['video_tamaño']."</option>";
 }else {$GLOBALS['selecciono']=0;
}

?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>LAN</b></font></u></td>
      <td><font color=#006699>Tipo</font></td>
      <td><select name="pc[lan_tipo]" id="pc_lan_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='lan_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['lan_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['lan_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}

?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td></td>
      <td><font color=#006699>Velocidad</font></td>
      <td><select name="pc[micro_velocidad]" id="pc_micro_velocidad" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='micro_velocidad';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{
	if (($resultado->fields['opcion']!='Ninguno') || ($resultado->fields['opcion']!='NO'))
	{
	?>
   
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['micro_velocidad'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
	}
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['micro_velocidad']."</option>";
 }else {$GLOBALS['selecciono']=0;
}

?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>Monitor</b></font></u></td>
      <td><font color=#006699>Tamaño</font></td>
      <td><select name="pc[monitor_tamaño]" id="pc_monitor_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected></option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='monitor_tamaño';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{
	if (($resultado->fields['opcion']!='Ninguno') || ($resultado->fields['opcion']!='NO'))
	{
	?>
   
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['monitor_tamaño'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
	}
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['monitor_tamaño']."</option>";
 }else {$GLOBALS['selecciono']=0;
}

?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>Software</b></font></u></td>
      <td><font color=#006699>SO</font></td>
      <td><select name="pc[software_so]" id="pc_software_so" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='software_so';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['software_so'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['software_so']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td><u><font color=#006699><b>Memoria</b></font></u></td>
      <td><font color=#006699>Tipo</font></td>
      <td><select name="pc[memoria_tipo]" id="pc_memoria_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='memoria_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['memoria_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['memoria_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>Multimedia</b></font></u></td>
      <td><font color=#006699>Sonido</font></td>
      <td><select name="pc[multimedia_sonido]" id="pc_multimedia_sonido" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='multimedia_sonido';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['multimedia_sonido'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['multimedia_sonido']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td><font color=#006699>Oficina</font></td>
      <td><select name="pc[software_oficina]" id="pc_software_oficina" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='software_oficina';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['software_oficina'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['software_oficina']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td></td>
      <td><font color=#006699>Tamaño</font></td>
      <td><select name="pc[memoria_tamaño]" id="pc_memoria_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='memoria_tam';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['memoria_tam'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['memoria_tam']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td><font color=#006699>Parlantes</font></td>
      <td><select name="pc[multimedia_parlantes]" id="pc_multimedia_parlantes" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='multimedia_parlantes';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['multimedia_parlantes'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['multimedia_parlantes']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>Gabinete</b></font></u></td>
      <td><font color=#006699>Tipo</font></td>
      <td><select name="pc[gabinete_tipo]" id="pc_gabinete_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='gabinete_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['gabinete_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['gabinete_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td><u><font color=#006699><b>Disco</b></font></u></td>
      <td><font color=#006699>Tipo</font></td>
      <td><select name="pc[disco_tipo]" id="pc_disco_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='disco_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['disco_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['disco_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td><font color=#006699>Microfono</font></td>
      <td><select name="pc[multimedia_mic]" id="pc_multimedia_microfono" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='multimedia_mic';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['multimedia_mic'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['multimedia_mic']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td><u><font color=#006699><b>Garantia</b></font></u></td>
      <td><font color=#006699>Meses</font></td>
      <td><select name="pc[garantia]" id="pc_garantia" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='garantia';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['garantia'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['garantia']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td></td>
      <td><font color=#006699>Tamaño</font></td>
      <td><select name="pc[disco_tamaño]" id="pc_disco_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='disco_tamaño';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['disco_tamaño'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['disco_tamaño']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td><font color=#006699>CD</font></td>
      <td><select name="pc[multimedia_cd]" id="pc_multimedia_cd" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='multimedia_cd';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['multimedia_cd'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['multimedia_cd']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td></td>
      <td><font color=#006699>Bus</font></td>
      <td><select name="pc[disco_bus]" id="pc_disco_bus" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='disco_bus';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['disco_bus'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['disco_bus']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
        </td>
      <td></td>
      <td><font color=#006699>CDWR</font></td>
      <td><select name="pc[multimedia_cdwr]" id="pc_multimedia_cdwr" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='multimedia_cdwr';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['multimedia_cdwr'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['multimedia_cdwr']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
        </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    <tr  bgcolor="CCCCCC"> 
      <td></td>
      <td><font color=#006699>RPM</font></td>
      <td><select name="pc[disco_rpm]" id="pc_disco_rpm" onchange="beginEditing(this)"> 
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='disco_rpm';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['disco_rpm'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['disco_rpm']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
        </td>
      <td></td>
      <td><font color=#006699>DVD</font></td>
      <td><select name="pc[multimedia_dvd]" id="pc_multimedia_dvd" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='pc' and campo='multimedia_dvd';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['multimedia_dvd'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['multimedia_dvd']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
        </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>
<br>
<?php
}
if (($_POST['tipo_protocolo']==2) || ($_POST['tipo_protocolo']==5))
{
?>
<hr>
<left><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Protocolo Servidor
</b></font></left>
<hr>
<table border="0" cellspacing="2" cellpadding="0">
<tr  bgcolor="CCCCCC">
<td><u><font color=#006699><b>Gabinete</b></font></u></td>
<td><font color=#006699>Tipo</font></td>
<td><select name="servidor[gabinete_tipo]" id="servidor_gabinete_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='gabinete_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option 
			<?  if ($resultado->fields['opcion']==$resultado_old->fields['gabinete_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['gabinete_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><font color=#006699><b>Storage</b></font></u></td>
<td><font color=#006699>Tipo</font></td>
<td><select name="servidor[storage_tipo]" id="servidor_storage_tipo" onchange="beginEditing(this)">
         <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='storage_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['storage_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['storage_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><font color=#006699><b>Monitor</b></font></u></td>
<td><font color=#006699>Tamaño</font></td>
<td><select name="servidor[monitor_tamaño]" id="servidor_monitor_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected></option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='monitor_tamaño';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['monitor_tamaño'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['monitor_tamaño']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
</tr>
<tr  bgcolor="CCCCCC">
<td><u><font color=#006699><b>Micro</b></font></u></td>
<td><font color=#006699>Cantidad</font></td>
<td><select name="servidor[micro_cantidad]" id="servidor_micro_cantidad" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='micro_cantidad';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['micro_cantidad'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['micro_cantidad']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Interface</font></td>
<td><select name="servidor[storage_interface]" id="servidor_storage_interface" onchange="beginEditing(this)">
<option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='storage_interface';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['storage_interface'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['storage_interface']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Rack</font></td>
<td><select name="servidor[monitor_rackeable]" id="servidor_monitor_rackeable" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='monitor_rackeable';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['monitor_rack'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['monitor_rack']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
</tr>
<tr  bgcolor="CCCCCC">
<td></td>
<td><font color=#006699>Tipo</font></td>
<td><select name="servidor[micro_modelo]" id="servidor_micro_tipo" onchange="beginEditing(this)">
          <option value=-1 selected></option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='micro_modelo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['micro_modelo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['micro_modelo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>rpm</font></td>
<td><select name="servidor[storage_rpm]" id="servidor_storage_rpm" onchange="beginEditing(this)">
<option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='storage_rpm';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['storage_rpm'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['storage_rpm']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><font color=#006699><b>Tecl/Mouse</b></font></u></td>
<td></td>
<td><select name="servidor[teclado]" id="servidor_teclado" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='teclado_mouse';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['teclado_mouse'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['teclado_mouse']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
</tr>
<tr  bgcolor="CCCCCC">
<td></td>
<td><font color=#006699>Cache</font></td>
<td><select name="servidor[micro_cache]" id="servidor_micro_cache" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='micro_cache';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['micro_cache'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['micro_cache']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Tamaño</font></td>
<td><select name="servidor[storage_tamaño]" id="servidor_storage_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='storage_tamaño';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['storage_tamaño'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['storage_tamaño']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><font color=#006699><b>Swich</b></font></u></td>
<td><font color=#006699>Ports</font></td>
<td><select name="servidor[switch]" id="servidor_swich_ports" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='swich_ports';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['switch_ports'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['switch_ports']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
</tr>
<tr  bgcolor="CCCCCC">
<td><u><font color=#006699><b>Memoria</b></font></u></td>
<td><font color=#006699>Tipo</font></td>
<td><select name="servidor[memoria_tipo]" id="servidor_memoria_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='memoria_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['memoria_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['memoria_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Cantidad</font></td>
<td><select name="servidor[storage_cantidad]"  id="servidor_storage_cantidad" onchange="beginEditing(this)">
<option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='storage_cantidad';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['storage_cantidad'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['storage_cantidad']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><font color=#006699><b>Sist.Operativo</b></font></u></td>
<td></td>
<td><select name="servidor[software_so]" id="servidor_software_so" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='software_so';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['software_so'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['software_so']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
</tr>
<tr  bgcolor="CCCCCC">
<td></td>
<td><font color=#006699>Tamaño</font></td>
<td><select name="servidor[memoria_tamaño]" id="servidor_memoria_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='memoria_tam';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['memoria_tam'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['memoria_tam']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Raid</font></td>
<td><select name="servidor[storage_raid]" id="servidor_storage_raid" onchange="beginEditing(this)">
<option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='storage_raid';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['storage_raid'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['storage_raid']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><b><u><font color=#006699>Garantia</font></u></b></td>
<td></td>
<td><select name="servidor[garantia]" id="servidor_garantia" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='garantia';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['garantia'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['garantia']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
</tr>
<tr  bgcolor="CCCCCC">
<td></td>
<td><font color=#006699>Expansion</font></td>
<td><select name="servidor[memoria_expansion]" id="servidor_memoria_expansion" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='memoria_expansion';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['memoria_expansion'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['memoria_expansion']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><font color=#006699><u>Backup</u></font></td>
<td><font color=#006699>Tipo</font></td>
<td><select name="servidor[storage_backup_tipo]" id="servidor_storage_backup_tipo" width="100" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='backup_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['backup_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['backup_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td></td>
<td></td>
</tr>
<tr  bgcolor="CCCCCC">
<td><u><font color=#006699><b>Video</b></font></u></td>
<td><font color=#006699>Tamaño</font></td>
<td><select name="servidor[video_tamaño]" id="servidor_video_tamaño" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='video_tamaño';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['video_tamaño'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['video_tamaño']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Modelo</font></td>
<td><select name="servidor[storage_backup_modelo]" id="servidor_storage_backup_modelo" onchange="beginEditing(this)"> 
          <option value=-1 selected></option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='backup_modelo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['backup_modelo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['backup_modelo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td></td>
<td></td>
</tr>
<tr  bgcolor="CCCCCC">
<td><u><font color=#006699><b>Expansion</b></font></u></td>
<td><font color=#006699>PCI</font></td>
<td><select name="servidor[expansion_pci]" id="servidor_expansion_pci" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='expansion_pci';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['expansion_pci'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['expansion_pci']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td><font color=#006699>Insumos</font></td>
<td><select name="servidor[storage_backup_extras]" id="servidor_storage_backup_insumos" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='servidor' and campo='backup_extras';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['backup_extras'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['monitor_tamaño']!=''))
{
 echo "<option selected>". $resultado_old->fields['backup_extras']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td></td>
<td></td>
<td></td>
</tr>
</table>
<br>
<?php
}
if (($_POST['tipo_protocolo']==3) || ($_POST['tipo_protocolo']==4) || ($_POST['tipo_protocolo']==5))
{
?>
<hr>
<left><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Protocolo Impresora
</b></font></left>
<hr>
<table border="0" cellspacing="2" cellpadding="0">
<tr  bgcolor="CCCCCC">
<td><u><b><font color=#006699>Tipo</font></b></u></td>
<td><select name="impresora[tipo]" id="impresora_tipo" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='impresora_tipo';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_tipo'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_tipo']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><b><font color=#006699>interface de red</font></b></u></td>
<td><select name="impresora[interface]" id="impresora_interface" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='interface_red';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_red'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_red']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
<td><u><b><font color=#006699>PPM</font></b></u></td>
<td><select name="impresora[ppm]" id="impresora_ppm" onchange="beginEditing(this)">
           <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='ppm';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_ppm'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_ppm']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
</tr>
<tr  bgcolor="CCCCCC">
<td><u><b><font color=#006699>Nª de hojas</font></b></u></td>
<td><select name="impresora[be]" id="bandeja" onchange="beginEditing(this)">
<option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='hojas';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_be'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_be']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
</select>
</td>
<td><u><b><font color=#006699>Duplex</font></b></u></td>
<td><select name="impresora[duplex]" id="impresora_duplex" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='duplex';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_duplex'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_duplex']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
<td><u><b><font color=#006699>Garantia</font></b></u></td>
<td><select name="impresora[garantia]" id="impresora_garantia" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='impresora_garantia';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_garantia'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_garantia']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
</tr>
<tr  bgcolor="CCCCCC">
<td><u><b><font color=#006699>Conexion</b></u></font></td>
<td><select name="impresora[conexion]" id="impresora_conexion" onchange="beginEditing(this)">
          <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='impresora_conexion';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_conexion'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_conexion']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
 </select>
</td>
<td><u><b><font color=#006699>Insumos extras</font></b></u></td>
<td><select name="impresora[extras]" id="impresora_extras" onchange="beginEditing(this)">
                    <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
          <?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='insumos';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_extras'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_extras']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
       </td>
<td><u><b><font color=#006699>RAM</font></b></u></td>
<td><select name="impresora[ram]" id="impresora_ram" onchange="beginEditing(this)">
    <option value=-1 selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
<?php
$sql="select opcion from armado_protocolos where cual_protocolo='impresora' and campo='ram';";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$i=0;
while ($i<$filas_encontradas)
{?>
          <option
			<? if ($resultado->fields['opcion']==$resultado_old->fields['impresora_ram'])
          							   { 			   	echo " selected"; 			   	$GLOBALS['selecciono']=1; 			   }	 
            ?>
		  > 
          <?php echo $resultado->fields['opcion']; ?>
          </option>
          <?php
$i++;
$resultado->MoveNext();
}//fin while
if (($GLOBALS['selecciono']==0) && ($resultado_old->fields['impresora_tipo']!=''))
{
 echo "<option selected>". $resultado_old->fields['impresora_ram']."</option>";
 }else {$GLOBALS['selecciono']=0;
}
?>
<option id=editable>Edite aqui</option>
        </select>
</td>
</tr>
</table>
<?php
}
?>
<br>
<font color=#006699><b>Comentarios:</b></font><br>
<textarea name="comentario" cols="70" rows="6">
<?PHP  
echo $resultado_old->fields['comentarios'];
?>
</textarea>
<center>
<br><br>
<input type="submit" name="boton" value="Guardar" onclick="dar_version(); return verificar(<?php echo $_POST['tipo_protocolo']; ?>);">
<input type="button" name="boton" value="Cancelar" onclick="history.go(<?php echo $_POST['historial']; ?>)">
</center>
<input type="hidden" name="version">
</form>
</body>
</html>
<?php
 }//fin default
} //fin switch
?>