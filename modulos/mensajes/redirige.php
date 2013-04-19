<?php
require_once("../../config.php");
switch ($_POST['Submit']){
	case "Cancelar":{ header('location: ./mensajes.php');
	                  break;}
    case "Reenviar mensaje":{include_once "./guardar_mens.php";
                              break;}
    default:{ 
?>
<html>
<head>
<title>Nuevo Mensaje</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<? cargar_calendario(); ?>
<SCRIPT language='JavaScript'>
function comprueba()
{if(document.form.venc.value=='') {
 alert("Debe seleccionar fecha de vencimiento.");
 return false;
 }
 if(document.form.para.value=='?') {
 alert("Debe seleccionar usuario.");
 return false;
 }
 if(document.form.nota.value=='') {
 alert("El mensaje está en blanco.");
 return false;
 }
 return true;
}
</SCRIPT>
<link rel=stylesheet type='text/css' href='../layout/css/win.css'>
</head>
<body bgcolor="#E0E0E0">
<table width="90%" border="0" align="center">
  <tr bgcolor="#c0c6c9">
      <td>
          
      <div align="left"><font color="#006699" size="2" face="Arial, helvetica, sans-serif"><b>&nbsp&nbspReenviar
        mensaje </b></font></div>
      </td>
    </tr>
  </table>
<br>
<form name="form" method="post" action="redirige.php">
<center>
    <br>
    <?php
	$id_mensaje=$_POST['radio']; 
   	$ssql_busca="select numero, nro_orden,usuario_destino,comentario,fecha_vencimiento from mensajes where id_mensaje=".$id_mensaje;
   	db_tipo_res('a');
    $result=$db->Execute($ssql_busca) or die($db->ErrorMsg());
 	?>
	<input type="hidden" name="id_m" value="<? echo $id_mensaje;?>">
	<input type="hidden" name="tipo_m" value=0>
    <input type="hidden" name="tipo2" value='MRU'>
 	<table width="90%" border="0">
      <tr> 
        <td width="42" height="40">&nbsp;Para:</td>
        <td colspan="2"> 
          <div align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"> 
            <select name="para">
              <option value='?'></option>
              <?php
				$ssql1="select nombre from usuarios where nombre!='root';";
				db_tipo_res('a');
                $result1=$db->Execute($ssql1) or die($db->ErrorMsg());
				while(!$result1->EOF){
			 ?>
              <option> 
              <? echo $result1->fields['nombre'];?>
              </option>
              <?php 
             $result1->MoveNext();
			}//while?>
              <option selected> 
              <? echo $result->fields['usuario_destino']; ?>
              </option>
              <option>Todos</option>
            </select>
            </font></div>
        </td>
        <td width="185"> 
          <div align="right">&nbsp;Fecha de Vencimieto:</div>
        </td>
        <td width="315"> 
          <? $fech=substr($result->fields['fecha_vencimiento'],0,10);
		      $hora=substr($result->fields['fecha_vencimiento'],11,16);
			?>
          <input type="hidden" name="nro_ord" value="<?php echo $result->fields['nro_orden'] +1;?>" >
          <input name="venc" value="<?php echo fecha($fech);?>" type=text >
          <?php echo link_calendario("venc"); ?>
        </td>
      </tr>
      <tr> 
        <td colspan="4" height="30" valign="top" > 
          <div align="right">Hora: </div>
        </td>
        <td valign="top" width="315" > 
          <input type="text" name="hora" value="<? echo $hora;?>">
        </td>
      </tr>
      <tr> 
        <td colspan="5" height="108"> 
          <div align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"> 
            <textarea name="nota" cols="70" rows="5"><?php echo $result->fields['comentario']; ?></textarea>
            <input type="hidden" name="anterior" value="<?php echo $result->fields['comentario']; ?>">
            
            </font></div>
        </td>
      </tr>
      <tr> 
        <td height="54" colspan="2" valign="top"> 
          <div align="left"> 
            <input type="submit" name="Submit" value="Reenviar mensaje" onClick="return comprueba();">
          </div>
        </td>
        <td valign="top" colspan="3"> 
          <div align="left"> 
            <input type="submit" name="Submit" value="Cancelar">
          </div>
        </td>
      </tr>
      <tr> 
        <td height="3" width="42"></td>
        <td width="81"></td>
        <td width="37"></td>
        <td width="185"></td>
        <td width="315"></td>
      </tr>
    </table>
    
    <hr size="10">
  </center>
  <center>
  </center>
</form>
<?php
 }//default
} //swuitch
?>
</body>
</html>