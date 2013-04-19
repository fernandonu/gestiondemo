<?php
require_once("../../config.php");
switch ($_POST['bot']){
	case "Cancelar":{ header('location: ./mensajes.php');
	                  break;}
    case "Enviar mensaje":{require "../mensajes/guardar_mens.php";
                           break;
                          }
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
 alert("El mansaje está en blanco.");
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
          
      <div align="left"><font color="#006699" size="2" face="Arial, helvetica, sans-serif"><b>&nbsp&nbspEnviar 
        nuevo mensaje</b></font></div>
      </td>
    </tr>
  </table>
<br>
<form name="form" action="nuevo_mens.php" method="post">
<center>
    <br>
   	
    <table width="90%" border="0">
      <tr> 
        <td width="53" height="49" valign="top">&nbsp;Para:</td>
        <td width="153" valign="top" > 
          <input type="hidden" name="tipo_m" value=1>
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
              <?php echo $result1->fields['nombre'];?>
              </option>
              <?php 
            $result1->MoveNext();
			}//while?>
			  <option>Todos</option>
            </select>
            </font></div>
        </td>
        <td colspan="2" valign="top" > 
          <div align="left"> 
            <p align="right">&nbsp;Fecha deVencimieto:</p>
          </div>
        </td>
        <td valign="top" width="309"> 
          <div align="left"> 
            <input name="venc" type=text >
            <?php echo link_calendario("venc"); ?>
          </div>
        </td>
      </tr>
      <tr> 
        <td colspan="4" height="30" valign="top" > 
          <div align="right">Hora: </div>
        </td>
        <td valign="top" width="309" > 
          <input type="text" name="hora" value="00:00">
        </td>
      </tr>
      <tr> 
        <td colspan="5" height="108"> 
          <div align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"> 
            <textarea name="nota" cols="70" rows="5"></textarea>
            </font></div>
        </td>
      </tr>
      <tr> 
        <td height="54" valign="top" colspan="3"> 
          <div align="left"> 
            <input type="submit" name="bot" value="Enviar mensaje" onClick="return comprueba();">
          </div>
        </td>
        <td colspan="2" valign="top"> 
          <div align="left"> 
            <input type="submit" name="bot" value="Cancelar">
          </div>
        </td>
      </tr>
      <tr> 
        <td height="3" width="53"></td>
        <td width="153"></td>
        <td width="108"></td>
        <td width="37"></td>
        <td width="309"></td>
      </tr>
    </table>
    
    <hr size="10">
  </center>
  <center>
  </center>
</form>
<?php
 }//default
} //fin switch
?>
</body>
</html>