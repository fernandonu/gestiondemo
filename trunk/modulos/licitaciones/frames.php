<?PHP
include("../../config.php");
//puedo hacer un frame general y invocar a la pagina con ese valor
//estos datos son para invocar a una pagina en el iframe
$id_renglon=$parametros['renglon'];
$nro_licitacion=$parametros['licitacion'];
$link = encode_link("agregar_producto.php",array("renglon" => $id_renglon,
//$link = encode_link("prueba1.php",array("renglon" => $id_renglon,
                                       "licitacion" => $nro_licitacion));
$link2= encode_link("frames.php",array("renglon" => $id_renglon,
                                       "licitacion" => $nro_licitacion));

$link3 = encode_link($html_root."/modulos/licitaciones/protocolo/chequeos.php",array("renglon" => $id_renglon,
                                       "licitacion" => $nro_licitacion));

$link4= encode_link("enviar_mensajes.php",array("renglon" => $id_renglon,
                                       "licitacion" => $nro_licitacion));
$link5= encode_link("realizar_oferta.php",array("renglon" => $id_renglon,
                                       "licitacion" => $nro_licitacion));


if (strcmp($_POST['submit'],"Aceptar")==0) {

                                            }
/*
if (strcmp($_POST['submit'],"Terminar Renglon")==0) {
                                          header("Location:$lik4");
                                          }
*/
if (strcmp($_POST['submit'],"Cancelar")) {

                                          }

?>
<html>
<head>
<SCRIPT language='JavaScript' src="funciones.js">
 </SCRIPT>
<title>Elegir Placa Madre</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#E0E0E0"  text="#000000">
<FORM action="<? echo $link2;  ?>"  method="POST"  name="form" >
<iframe name='iframe1' width="75%"  height="85%"   align="" src= <? echo $link; ?>  >
</iframe>
&nbsp;&nbsp;
<iframe name='iframe2' width="23%"  height="85%"   align=" " src=<? echo $link3; ?> >
</iframe>
<TABLE align="center">
 <tr>
 <?
  $link2=encode_link("realizar_oferta.php",
                      array("licitacion" => $parametros['licitacion'],
                       "nombre_pagina" => "frames"));

   //estos botonoes hay que revisar bien que funcion van a cumplir
   echo "<td>";
   echo "<INPUT TYPE=\"button\" name=\"submit\" value=\"Terminar Renglon\" onClick=\"location.href='$link4'\";> " ;
   echo "</td>";
   echo "<td>";
   echo "<INPUT TYPE=\"button\" name=\"submit\" value=\"Aceptar\" onClick=\"location.href='$link5'\";> " ;
   echo "</td>";
   echo "<td>";
   echo "<INPUT TYPE=\"button\" name=\"submit\" value=\"Cancelar\"   onClick=\"location.href='$link2' \"; > " ;
   echo "</td>";

   ?>
 </tr>
</TABLE>

</FORM>
</body>
</HTML>