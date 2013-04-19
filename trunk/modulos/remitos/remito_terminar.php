<?
/*
Author: GACZ

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.2 $
$Date: 2003/11/26 13:29:07 $
*/
require_once("../../config.php");
//require_once("config_local.php");

//DATOS DE LA FACTURA se extraen del arreglo POST
extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
	
$q="select * from remitos where id_remito=$id_remito";
$datos=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
$nbre2[0]=substr($datos->fields['cliente2'],$coma_pos=strpos($datos->fields['cliente2'],",")+1);
$nbre2[1]=substr($datos->fields['cliente2'],0,$coma_pos=strpos($datos->fields['cliente2'],","));
$nro_remito=$datos->fields['nro_remito'];
$estado=$datos->fields['estado'];

if ($estado=='r' || $estado=='R')
 $permiso=" disabled ";
?>
<html>
<head>
<title>Recibo de Remito</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
}
-->
</style>


<body topmargin="2" leftmargin="0" rightmargin="0" bottommargin="0" bgcolor="#E0E0E0">
<form name="form1" method="post" action="remito_proc.php">
  <div align="center"><strong>Remito N&ordm;<?=" $nro_remito"?></strong> <br> <br> 

    <table width="330" border="1" cellspacing="1" cellpadding="1">
      <tr class="tablaEnc"> 
        <td height="30" colspan="2" align="center">
        <strong>Recibido por</strong>
        </td>
  		</tr>
      <tr> 
        <td width="151"><strong>Apellido/s </strong></td>
        <td width="166"><input name="apellido" type="text"  value="<?= $nbre2[0] ?>"<?= $permiso ?> size="25"></td>
      </tr>
      <tr> 
        <td><strong>Nombre/s</strong></td>
        <td><input name="nbre" type="text"  value="<?= $nbre2[1] ?>" <?= $permiso ?> size="25"></td>
      </tr>
      <tr> 
        <td><strong>Tipo de Documento</strong></td>
        <td> <div align="right"> 
            <!-- seleccionar el tipo de DNI -->
            <select name="select_tipodoc" <?= $permiso ?> onchange="beginEditing(this)">
              <? switch ($datos->fields['tipo_doc_c2'])
	{
		case "D.N.I": $dni="selected";break;
		case "C.I": $ci="selected";break;
		case "L.C": $lc="selected";break;
		case "": $dni="selected";break;
		default: $otro="<option selected>".$datos->fields['tipo_doc_c2']."</option>";break;						
	}
?>
              <option <?=$dni ?>>D.N.I</option>
              <option <?=$le ?>>L.E</option>
              <option <?=$ci ?>>C.I</option>
              <option <?=$lc ?>>L.C</option>
              <? if ($otro) echo $otro ?>
              <option id="editable">añadir</option>
            </select>
          </div></td>
      </tr>
      <tr> 
        <td><strong>N&ordm; de Documento</strong></td>
        <td><input name="nrodoc" type="text"  value="<? echo $datos->fields['nro_doc_c2'] ?>" <? echo $permiso ?> size="25"></td>
      </tr>
    </table>
    <table width="44%" border="0" cellspacing="1" cellpadding="1">
      <tr> 
        <td width="48%" height="36" align="center" valign="bottom"> 								<? //sin return false el formulario se envia igual ?>
          <input type="submit" name="boton" value="Aceptar" onclick="<? if ($estado=='r') echo "window.close();return false" ?> ">
        </td>
        <td width="52%" align="center" valign="bottom"> 
          <input type="button" name="boton" value="Cancelar" onclick="window.close();">
        </td>
      </tr>
    </table>
  </div>
  <input type="hidden" name="id_remito" value="<?= $id_remito ?>" >
  <input type="hidden" name="nro_remito" value="<?= $nro_remito ?>" >
</form>
</body>
</html>
