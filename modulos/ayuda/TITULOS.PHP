<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>P&aacute;gina de ayuda</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript">
function cerrar(){


if (window.opener) {
	if(!window.opener.closed) {
	//	window.opener.redim_ventana();
		 window.opener.top.resizeBy(valor,0); 
}
}
self.close();
}


</script>
</head>

<body bgcolor="#1254C2" link="#000000" vlink="#000000" alink="#000000" onUnload="cerrar()" marginheight="0" marginwidth="0">
<script language="JavaScript">
//calcula el valor que achica la pantalla
//que es el mismo que se calcula en el archivo que llama a la ayuda
var winW=window.screen.Width;
var valor=(winW*25)/100;
</script>
<div align="center">
 <img src='logo_coradir.jpg' alt='' border='0' width='150' height='28'>
 </div>
 
  <center>
   <strong><u><font color="#FFFFFF" size="2+"><span id=titulo></span></font></u></strong> 
   <br>
   </center>

<iframe name='frame1' height="75%" allowtransparency=true marginheight="0" marginwidth="0" scrolling="auto" width="100%" frameborder="0" src="pagina.htm"></iframe>
<br>
<br>    
<p>&nbsp;</p>
</body>
</html>
