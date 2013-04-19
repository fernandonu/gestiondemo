<html>
<head>
<title>Lista de Protocolos</title>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
</head>
<body bgcolor="E0E0E0">
<left><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Protocolos
</b></font></left>
<hr>
<form name="form" action="ver_protocolos.php" method="post">
<table border="0">
<tr>
<td><font color="#006699">Nro de Licitacion:</font></td><td><input type="text" name="licitacion" size="5"><br></td>
</tr>
<tr>
<td><font color="#006699">Nro de Renglon:</font></td><td><input type="text" name="renglon" size="5"></td>
</tr>
<tr>
<td><font color="#006699">Nro de Item:</font></td><td><input type="text" name="item" size="5" value="0"><br></td>
</tr>
</table>
<center>
<p><b>Seleccione el tipo de protocolo que desea</b></p>
<select name="tipo_protocolo">
<option value="1">Protocolo PC</option>
<option value="2">Protocolo Servidor</option>
<option value="3">Protocola Impresora</option>
<option value="4">Protocolo PC+Impresora</option>
<option value="5">Protocolo Servidor+Impresora</option>
<option value="6">Otros</option>
</select>
<input type="submit" name="boton" value="Llenar Protocolo" onclick="return verificar2();">
</form>
</body>
</html>