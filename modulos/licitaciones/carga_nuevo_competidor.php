<?
/*
Author: Broggi
Fecha: 11/08/2004

MODIFICADA POR
$Author: broggi $
$Revision: 1.1 $
$Date: 2004/09/13 21:08:00 $
*/
require_once("../../config.php");
echo $html_header;
$sql="select nombre,id_competidor from licitaciones.competidores order by nombre";
$resultado_competidor=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$db_name);
?>
<script>
function control_boton()
{if (window.event.keyCode==13)
 {window.opener.id_competidor=document.all.competidor.options[document.all.competidor.options.selectedIndex].value;
  window.opener.texto_competidor=document.all.competidor.options[document.all.competidor.options.selectedIndex].text;
  if (!buscar_elemento(window.opener.id_competidor,window.opener.paso_id_competidor))
   {window.opener.cargar_competidor(1);
    window.opener.paso_id_competidor[window.opener.col]=window.opener.id_competidor;
   }
else
 alert('El Competidor que intenta insertar ya existe.');
 }
 if (window.event.keyCode==27)
 {window.opener.focus();
  window.close();
 }
}
</script>
<body onKeypress="control_boton()">
<font color="Blue">Cargar Competidores
<select name="competidor" onKeypress="buscar_op(this);"
onblur="borrar_buffer();"
onclick="borrar_buffer();">
<?
while(!$resultado_competidor->EOF)
{
?>
<option value="<?=$resultado_competidor->fields['id_competidor'];?>"><?=$resultado_competidor->fields['nombre'];?></option>
<?
$resultado_competidor->MoveNext();
}
?>
</select>
<br>
<script>
document.all.competidor.focus();
</script>
<center>
<input type="button" name="boton" value="Cargar" onclick="window.opener.id_competidor=document.all.competidor.options[document.all.competidor.options.selectedIndex].value;
window.opener.texto_competidor=document.all.competidor.options[document.all.competidor.options.selectedIndex].text;
if (!buscar_elemento(window.opener.id_competidor,window.opener.paso_id_competidor))
 {window.opener.cargar_competidor(1);
  window.opener.paso_id_competidor[window.opener.col]=window.opener.id_competidor;
 }  
else
 alert('El Competidor que intenta insertar ya existe.');
">
<input type="button" name="boton" value="Salir" onclick="window.close();">
</center>
</body>
</head>
</html>