<?php
  /*
$Author: $
$Revision: $
$Date: $
*/
include("../../config.php");

?>
<html>
<head>
<title>Proveedores</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
a {
	cursor: hand;text-decoration:none;
	color: #006699;
}
-->
</style>



</head>
<body bgcolor="#E0E0E0">
<center>
<br>
<!-- BUSQUEDA DE CLIENTES-->
<form name="form1" method="post" action="clientes.php">
<center>
    <table  border="0" cellspacing="0" cellpadding="0" width="90%" height="35">
      <tr bgcolor="CCCCCC">
        <td width="160" height="35">
          <p align="center"><b>Clientes</b></p>
        </td>
        <td>
        <?php
        //combo para primer filtro de busqueda
		echo "<select name=''>
        	  <option></option>
              <option></option>
            </select>";

         ?>
        </td>
 <td width="109">
          <div align="right">Buscar:&nbsp;</div>
        </td>
        <td width="130">
          <div align="center">
            <input type="text" name="tbuscar" value="<?  /*como uno de los dos es vacio
															y el otro no, los concateno*/
              									echo $_POST["tbuscar"].$parametros["texto buscado"]; ?>">
          </div>
        </td>
        <td width="49">
          <div align="right">en: </div>
        </td>
        <td width="151" >

        <div align="center">
            <select name="select_en" >
              <option>Nombre</option>
              <option>Direccion</option>
              <option>Telefono</option>
              <option>Localidad</option>
              <option>Provincia</option>
            </select>
          </div>
        </td>

        <td width="149" >
          <div align="center">
            <input type="submit" name="bconsulta" value="Ver Datos">
          </div>
        </td>
      </tr>
    </table>
       </tr>

<hr>
<div style="position:relative; width:96%; overflow:auto;">
<table width="100%" border="0" cellspacing="0">
      <tr bgcolor="#c0c6c9">
      <td align="left" height="30"><b>&nbsp;&nbsp;Total: <? echo $total_clientes; ?> clientes.</b>
      </td>
      <td align="right">
      </td>
      </tr>
</table>
</div>

<!--
Encabezado estatico de la tabla que muestra el listado de clientes
-->

<div style="position:relative; width:96%; height:70%; overflow:auto;">
<table width="100%" border="0" cellspacing="2">
<tr title="Vea comentarios de los clientes" id=mo>
<th width="40%"><b>Nombre</b></th>
<th width="15%"><b>Domicilio</b></th>
<th width="15%"><b>CP</b></th>
<th width="30%"><b>Localidad</b></th>
<th width="15%"><b>Provincia</b></th>
<th width="15%"><b>Domicilio</b></th>
<th width="15%"><b>CUIT</b></th>
<th width="15%"><b>Factura</b></th>
<th width="0%"><b></b></th>
</tr>


<?

//$query="select * from general.cliente, general.factura where cliente.id_cliente=factura.id_cliente";

$query="SELECT * FROM general.cliente";
$resultado = $db->Execute($query) or die($db->ErrorMsg().$query);
$filas_encontradas=$resultado->RecordCount();


$cnr=1;
$resultado->MoveFirst();
while (!$resultado->EOF)
{

  // Para pasar a la página de clientes el id del cliente.
  //$link=encode_link("nuevo_cliente.php",array("id_cliente"=> $resultado->fields["id_cliente"],"pagina"=>"listado_clientes", "cant_filas_mostradas"=> $cant_filas_mostradas,"total_prov"=> $total_proveedores,"nro_paginas"=> $nro_paginas,"pagina_actual"=> $pagina_actual,"query"=> $pasa_query,"texto buscado"=> $buscado,"tipo_prov"=>$state));
  $link=encode_link("nuevo_cliente.php",array("id_cliente"=> $resultado->fields["id_cliente"],"pagina"=>"listado_clientes"));
  //$link.="#contacto";
  if ($cnr==1)
  {$color2=$bgcolor2;
   $color=$bgcolor1;
   $atrib ="bgcolor='$bgcolor1'";
   $cnr=0;
  }
  else
  {$color2=$bgcolor1;
   $color=$bgcolor2;
   $atrib ="bgcolor='$bgcolor2'";
   $cnr=1;
  }
  $atrib.=" onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\"";
  $atrib.=" title='$comentario' style=cursor:hand";

  ?>

  <tr <?php echo $atrib; ?>>
  <td width="40%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["nombre"]; ?></td>
  <td width="30%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["direccion"]; ?></td>
  <td width="20%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["cod_pos"]; ?></td>
  <td width="10%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["localidad"]; ?></td>
  <td width="10%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["provincia"]; ?></td>
  <td width="10%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["cuit"]; ?></td>
  <td width="10%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["tipo"]; ?></td>
  </tr>

  <?php
   $x++;
   $resultado->MoveNext();

}   //del while de clientes.
?>
</table>

</center>
</div>
</body>
</html>


