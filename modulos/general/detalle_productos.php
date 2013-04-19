<?PHP
/*
Author: Cesar

MODIFICADA POR
$Author: cestila $
$Revision: 1.37 $
$Date: 2004/10/25 17:59:51 $
*/
include("../../config.php");

if ($parametros['down']=='t')
          {
           $FileName=$parametros["nombre_ar"];
           $FileType=$parametros["tipo"];
           $FileSize=$parametros["tamaño"];
           $FilePath=UPLOADS_DIR."/folletos/";
           $FileNameFull="$FilePath/$FileName";
           if (substr($FileName,strrpos($FileName,".")) == ".zip")
                  {
                   Mostrar_Header($FileName,$FileType,$FileSize);
                   readfile($FileNameFull);
                  }
                 else
                    {
                    $FileNameFull = substr($FileNameFull,0,strrpos($FileNameFull,"."));
                    $fp = popen("/usr/bin/unzip -p \"$FileNameFull\"","r");
                    Mostrar_Header($FileName,$FileType,$FileSize);
                    fpassthru($fp);
                    pclose($fp);
                    }
          }//DEL PRIMER IF


$idprod=$parametros["producto"];
$query="select * from general.productos where id_producto='".$parametros["producto"]."'";
$resultados = $db->Execute("$query") or die($db->ErrorMsg());
//generamos el link
if ($parametros['modulo']=="remito")
   {
    $link=encode_link($parametros["nombre_pagina"],array("modulo"=> $parametros['modulo'],
                                                         "nombre_pagina" => $parametros["nombre_pagina"],
                                                        /*"producto"=>$resultado->fields["id_producto"],*/
                                                        "remito"=> $parametros["remito"]));
   }
    else
   {
    $link=encode_link($parametros["nombre_pagina"],array("modulo"=> $parametros['modulo'],
                                                         "nombre_pagina" => "detalle_productos.php",
                                                         "producto"=>$resultados->fields["id_producto"],
                                                         "tipo" => $parametros['tipo'],
                                                         "licitacion" => $parametros["licitacion"],
                                                         "renglon" => $parametros["renglon"],
                                                         "item" => $parametros["item"],
                                                         "moneda"=>$parametros['moneda'],
                                                         "valor_moneda"=>$parametros['valor_moneda']));

  } //del else
echo $html_header;
?>
<SCRIPT language="javascript">
function cambiar_check(fila) {
     if (document.form.contador.value>1)
                 document.all.prod[fila].checked="true";
}

function cambiar_prov(id_prov)  {
   document.all.proveedor.value=id_prov;
   obj=eval("document.form.boton1");
   obj.title='';
}
</script>

</head>
<form name="form" action="<?php echo $link; ?>" method="POST">
<br>
<input type="hidden" name="producto" value="<?php echo $idprod; ?>">
<?
aviso("Descripción del Producto");
?>

<table width="100%">
<tr>
<td align="right">
<?
$link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$idprod)); 
?>
<input type="button" name="boton_historial" value="Ver historial de este producto" onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=100,top=10,width=600,height=600,resizable=1')" style="cursor:hand">
</td>
</tr>
</table>
<table class="bordes" cellspacing="2" width="100%" >
<tr title="Vea comentarios de los productos" id="mo">
      <td  align="center" width="41%"><font color="<?php echo $bgcolor2; ?>"><b>Caracteristicas</b></font></td>
      <td  align="center" width="59%"><font color="<?php echo $bgcolor2; ?>"><b>Descripcion</b></font></td>
</tr>
<?
   $desc=$parametros['tipo'];
   if ($desc=='todos'){
            $id=$parametros['producto'];
            $q_prod=" select descripcion from general.productos join general.tipos_prod on productos.tipo=tipos_prod.codigo
                     where id_producto=$id";
            $res = $db->Execute($q_prod) or die($db->ErrorMsg().$q_prod);
            $tipo_p=$res->fields["descripcion"];
            }
            else {
              $q_prod =" select descripcion from tipos_prod where codigo='$desc' ";
              $res = $db->Execute($q_prod) or die($db->ErrorMsg().$q_prod);
              $tipo_p=$res->fields["descripcion"];
              }
?>
<tr bgcolor=<?=$bgcolor_out?>>
      <td width="41%"><font color="<?php echo "black"; ?>"><b> Tipo de producto </b> </td>
      <td width="59%"><font color="<?php echo $bgcolor1; ?>">
       <b><?php echo $tipo_p; ?>
      </td>
</tr>
<tr bgcolor=<?=$bgcolor_out?>>
      <td width="41%"><font color="<?php echo "black"; ?>"><b> Marca </b> </td>
      <td width="59%"><font color="<?php echo $bgcolor1; ?>">
       <b><?php echo $resultados->fields["marca"]; ?>
      </td>
</tr>
<tr bgcolor=<?=$bgcolor_out?>>
      <td width="41%"><font color="<?php echo "black"; ?>"><b> Modelo </b> </td>
      <td width="59%"><font color="<?php echo $bgcolor1; ?>">
      <b><?php echo $resultados->fields["modelo"]; ?>
      </td>
</tr>

<tr bgcolor=<?=$bgcolor_out?>>
      <td width="41%"><font color="<?php echo "black"; ?>"><b> Descripci&oacute;n</b></td>
      <td width="59%"><font color="<?php echo $bgcolor1; ?>"> 
      <b><?php echo $resultados->fields["desc_gral"]; ?>
      </td>
</tr>

<?
$color = $bgcolor1;
$fuente = $bgcolor3;	

$velocidad=$resultados->fields["desc1"];
	if($resultados->fields["desc1"] != "") {
		echo "<tr bgcolor='$color'>";
		echo "<td><font color='$fuente'><b> Velocidad </b> </td>";
		echo "<td><font color='$fuente'><b>$velocidad</td>";
		echo "</tr>"; 
		if($color == $bgcolor3) $color = $bgcolor1; 
		if($color == $bgcolor1) $color = $bgcolor3; 
		if($fuente == $bgcolor3) $fuente = $bgcolor1;
		if($fuente == $bgcolor1) $fuente = $bgcolor3;
	}

$capacidad=$resultados->fields["desc2"];

if($resultados->fields["desc2"] != "") {	
	echo "<tr bgcolor='$color'>";
	echo "<td><font color='$fuente'><b>Capacidad </b> </td>";
	echo "<td><font color='$fuente'>$capacidad</td>";
	echo "</tr>"; 
	if($color == $bgcolor3) $color = $bgcolor1; 
	if($color == $bgcolor1) $color = $bgcolor3;
	if($fuente == $bgcolor1) $fuente = $bgcolor3;
	if($fuente == $bgcolor3) $fuente = $bgcolor1;
	}
?>

      <td width="41%">
    </tr>

</table>
<input type="hidden" name="proveedor" value="0">
<?php
  //echo $idprod;
   $sql="SELECT proveedor.id_proveedor, proveedor.razon_social, precios.precio FROM precios,proveedor 	where proveedor.id_proveedor=precios.id_proveedor AND precios.id_producto=$idprod";
  $rs= $db->Execute("$sql") or die($db->ErrorMsg());

 if ($rs->RecordCount()==0)
	  echo "NO HAY PROVEEDORES PARA EL PRODUCTO SELECCIONADO";
 else {

?>

<hr>
 <table width=90% align=Center class="bordes">
    <tr>
      <td colspan=2 id=mo>
      Proveedores
      </td>
    </tr>
    <tr id="ma">
      <td width=90%>Proveedor </td>
      <td >Precio     </td>
    </tr>
    <?php
    $cont=0;
    while (!$rs->EOF)  {
    $mod=$cont%2; //para intercalar el color de las filas
    ?>
    <tr bgcolor=<?=$bgcolor_out?>>
     <td align=left><b> <?php echo $rs->fields["razon_social"];?>  </td>
     <td align=right><b> <?php echo "U\$S ".$rs->fields["precio"];?> </td>
    </tr>
    <?php
     $cont++;
     $rs->MoveNext();
     }
     }
     ?>
  </table>
  <input type="hidden" name="contador" value="<?php echo $cont ?>">
  <br>
  <hr>
  <?php
  $sql="select titulo,contenido from descripciones where id_producto=$idprod";
  $rs= $db->Execute("$sql") or die($db->ErrorMsg());
  if ($rs->RecordCount()==0)
	  echo "NO HAY DESCRIPCION PARA EL PRODUCTO SELECCIONADO";
 else {
  ?>
  <table class="bordes" width=90% align=Center>
    <tr>
    <tr>
       <td colspan=2 id=mo>
          Descripción de las Licitaciones
       </td>
    </tr>
    <tr id="ma">
      <td> Titulo    </td>
      <td>Descripción </td>
    </tr>
	<?php
	while (!$rs->EOF){
	?>
        <tr bgcolor=<?=$bgcolor_out?>>
          <td> <b><?php echo $rs->fields["titulo"];?> </b> </td>
          <td> <b><?php echo $rs->fields["contenido"];?> </b></td>
        </tr>
	<?php
	$rs->MoveNext();
	}
	}
	?>
  <tr>
  <?
  $link=encode_link("../licitaciones/desc_productos.php",array("id_producto"=>$idprod));
  ?>
    <td colspan=2 align=right>
      <input type=button name=desc_productos value="Agregar Descripción" onclick="window.open('<?=$link?>')">
    </td>
  </tr>
  </table>
  <br>
  <?php
  $sql="select * from folletos where id_producto=$idprod";
  $resultado = $db->Execute("$sql") or die($db->ErrorMsg()."<br>".$sql);
  if ($resultado->RecordCount()==0)
	  echo aviso("NO HAY FOLLETO PARA EL PRODUCTO SELECCIONADO");
  else {
  ?>
  <table class="bordes" width=90% align=center>
   <tr id=mo><td colspan=4>Folletos Asociados al producto</td></tr>
   <tr id="ma" title="Haga click sobre el nombre del archivo para abrirlo">
   <td width="30%"><b>Nombre</td>
   <td width="20%"><b>Tamaño</td>
   <td width="30%"><b>Tipo</td>
   <td width="20%"><b>Tamaño Comprimido</td>
</tr>
<?php
    $cont=0;
   while (!$resultado->EOF)
    {
     ?>
     <tr bgcolor="<?php if ($mod!=0) echo $bgcolor1;?>" title="Haga click sobre el nombre del archivo para abrirlo">
     <a href="<?php echo encode_link($_SERVER["PHP_SELF"],array("down"=>'t',"nombre_ar"=>$resultado->fields["nombre_ar"],"tamaño"=>$resultado->fields['tamaño'],"tipo"=>$resultado->fields['tipo'])); ?>"><td style="cursor:hand;"><b><font color="<?php if ($mod!=0) echo $bgcolor3; else echo $bgcolor1;?>"><?php echo $resultado->fields['nombre_ar']; ?></b></td></a>
     <td><b><?php echo sprintf("%01.2lf",$resultado->fields['tamaño']/1024); ?> Kbyte</b></td>
     <td><b><?php echo $resultado->fields['tipo']; ?></b></td>
     <td><b><?php echo sprintf("%01.2lf",$resultado->fields['tamaño_comp']/1024); ?> Kbyte</b></td>
     <?php
     $resultado->MoveNext();
     $cont++;
   }
?>
 </table>
<?php
 }
?>

<?php
 if ($parametros['tipo_producto']=="") $parametros['tipo_producto']="todos";
$link=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
                                                 "tipo"=>$parametros['tipo_producto'],
                                                 "licitacion" => $parametros["licitacion"],
                                                 "renglon" => $parametros["renglon"],
                                                 "item" => $parametros["item"],
                                                 "nombre_pagina" => $parametros['nombre_pagina'],
                                                 "producto"=>'',
												 "texto"=>$parametros["texto"], "campo"=>$parametros["campo"],
                                                 "moneda"=>$parametros['moneda'],
                                                 "remito" => $parametros["remito"],
                                                 "valor_moneda"=>$parametros['valor_moneda']));
                                                 ?>
<table width=90% align=center>
<tr>
  <td align=center>
  <input type="button" name="boton" value="Volver" Onclick="location.href='<?php echo $link; ?>';">
  </td>
</tr>
</table>
</form>
</div>
</body>
</html>