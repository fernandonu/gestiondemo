<?PHP

include("../../config.php");

$link_mostrar=encode_link("mostrar_logo.php",array("id_licitacion"=>$parametros['id_licitacion']));

if($_POST["OK"]=="OK")
{
 if($_POST["no_mostrar"]=="no_mostrar")
 {
  $query="update entregar_lic set mostrar=0 where id_licitacion=".$parametros['id_licitacion']." ";	
  $db->Execute($query) or die($db->ErrorMsg().$query);
 }
 ?>
 <script> window.close()</script>
<?  	
}

?>
<html>
<head>
<style type="text/css">
.boton{
        font-size:10px;
        font-family:Verdana,Helvetica;
        font-weight:bold;
        color:white;
        background:#638cb9;
        border:0px;
        width:160px;
        height:19px;
       }
</style>

<title>Continuar mostrando aviso</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#E0E0E0"  text="#000000">
<form name="form1" action="<?echo $link_mostrar?>" method="post">
<table border='0' width='100%'>
<tr>
<td align="center">
No mostrar el aviso de orden de compra, para esta licitación, en el futuro <input type="checkbox" name="no_mostrar" value="no_mostrar">
</td>
</tr>
<tr>
<tr></tr><tr></tr>
<td align="center">
<input type="submit" name="OK" value="OK" style="width:20%">
</td>
</tr>
