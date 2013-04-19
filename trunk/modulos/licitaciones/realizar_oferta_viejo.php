<?php
include("../../config.php");
require_once("./funciones.php");

$nro_licitacion=$parametros["licitacion"] or $nro_licitacion=$parametros["ID"];

$titulo=$_POST['titulo'];
$codigo=trim($_POST['codigo']);
$prueba_renglon=$_POST['noparticipa'];

$link2=encode_link("realizar_oferta.php", array("modulo" => "licitaciones",
                                                "licitacion" => $nro_licitacion));
$link4= encode_link("enviar_mensajes.php",array("renglon" => $id_renglon,
                                       "licitacion" => $nro_licitacion,
                                       "pagina"=>'realizar_oferta'));
$ref = encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$nro_licitacion));

//consulta para obtner los datos de la moneda

//$query = "Select * from (licitacion join moneda on licitacion.moneda = moneda.id and licitacion.id_licitacion = $nro_licitacion)" ;



if($_POST["eliminar_renglon"]=="Eliminar Renglon") {
                                         $db->StartTrans();
                                         // Eliminar productos agregados a la licitacion
                                         // (Tabla producto).
                                         $query="DELETE from producto WHERE id_renglon = '".$_POST["renglon"]."'";
                                         $db->execute($query)or die($quey);
                                          //elimino las ofertas
                                         $query="DELETE from oferta WHERE id_renglon = '".$_POST["renglon"]."'";
                                         $db->execute($query)or die($quey);


                                         $query = "Select * from renglon where id_licitacion = $nro_licitacion" ;
                                         $resultados=$db->execute($query);
                                         $nro_renglones=$resultados->RecordCount();
                                         $nro_renglones--;
                                         $query="UPDATE licitaciones.licitacion SET nro_renglones = $nro_renglones  where id_licitacion = $nro_licitacion";
                                         $db ->Execute($query);
                                         //Eliminar el renglon de la tabla de renglones.
                                         $query="DELETE from renglon WHERE id_renglon = '".$_POST["renglon"]."'";
                                         $db->execute($query) or die($db->ErrorMsg());
                                         $db->CompleteTrans();
                                                }

if($_POST["modificar_renglon"]=="Modificar Renglon") {
                       //habilito los botones y muestro los datos correspondientes
                      $query= "Select * from renglon where id_renglon = '".$_POST["renglon"]."' " ;
                      $resultados=$db->Execute($query);
                      $titulo = $resultados->fields['titulo'];
                      $codigo = $resultados->fields['codigo_renglon'];
                      $modifica = 1;


                   }

if($_POST["Cambiar"]=="Cambiar") {
                                   $query="update renglon SET titulo='$titulo', codigo_renglon ='$codigo' WHERE id_renglon = '".$_POST["id_renglon_modificar"]."'";
                                   $db->Execute("$query") or die($query);
                                 }

if ($_POST['agregar']=='Agregar')
                                 {
                                 $query_control="SELECT * FROM renglon WHERE id_licitacion = $nro_licitacion";
                                 $resultados=$db->Execute($query_control);
                                 $nro_renglones=$resultados->RecordCount();
                                 $nro_renglones++;
                                 //tengo que controlar que no inserte dos iguales
                                 //if ($resultados->RecordCount()== 0) {
                	                              //actualizo la tabla licitacion
                                                   $db->StartTrans();
                 		                           $query="UPDATE licitaciones.licitacion SET nro_renglones = $nro_renglones where id_licitacion = $nro_licitacion";
                 		                           $db ->Execute($query);
                		                           //inserto el nuevo renglon
                 		                           $query="INSERT INTO licitaciones.renglon (id_licitacion,titulo,codigo_renglon,no_participamos) VALUES ($nro_licitacion,'$titulo','$codigo','true')";
                 		                           $db ->Execute($query);
                                                   $db->CompleteTrans();
                                   //                                      }
                                 }
if ($_POST['noparticipa']){
                          //$nombre_select="accion".$_POST['noparticipa'];
                          $nro_renglon=$_POST['noparticipa'];
                          $query = "select * from renglon where id_renglon = $nro_renglon" ;
                          $resultado = $db->execute($query);
                          $valor_boolean = $resultado->fields['no_participamos'];
                          if  ($valor_boolean=='t') $query="UPDATE renglon SET no_participamos ='false' where id_renglon = $nro_renglon";
                          if  ($valor_boolean=='f') $query="UPDATE renglon SET no_participamos ='true'  where id_renglon = $nro_renglon";
                          $db ->Execute($query) or die($query);
                           }

if ($_POST['aceptar_moneda']=='Aceptar'){
                                   $valor_moneda = $_POST['valor_moneda'];
                                   $db->StartTrans();
                                   //obtengo el viejo valor del dolar
                                   $query="Select  valor_dolar_lic from licitacion where id_licitacion =  $nro_licitacion ";
                                   $resultado=$db->execute($query) or die($query);
                                   $viejo_valor = $resultado->fields['valor_dolar_lic'];
                                   //selecciono todos los renglones de la licitacion para actualizar sus precios
                                   $query="Select * from renglon where id_licitacion =  $nro_licitacion ";
                                   $resultado=$db->execute($query) or die($query);
                                   $filas_encontradas = $resultado->RecordCount();
                                   //modifico con los nuevos valores de la licitacion
                                   if ($viejo_valor!=0){
                                       for ($i=0;$i<$filas_encontradas;$i++) {
                                                            $renglon_modificar=$resultado->fields['id_renglon'];
                                                            //lo vuelvo a dolares o a la moneda que corresponda
                                                            $total = $resultado->fields['total']/$viejo_valor;
                                                            //nuevo valor de la moneda
                                                            $total=$total*$valor_moneda;
                                                            $query="UPDATE renglon SET total = $total where id_renglon = $renglon_modificar";
                                                            $db->execute($query) or die($query);
                                                            $resultado->MoveNext();
                                                                             }//fin del for
                                                       } //del then del if

                                   //actualizo la licitacion con el nuevo valor
                                   $query="UPDATE licitacion SET valor_dolar_lic = $valor_moneda  where id_licitacion = $nro_licitacion";
                                   $db->execute($query) or die ($query);


                                   $db->CompleteTrans();
                                  }




if ($_POST['terminar_licitacion']=="Terminar Licitacion"){
                                               header("Location:$link4");
                                                }


?>
<html>
<head>
<SCRIPT language='JavaScript' src="funciones.js">
 </SCRIPT>
<title>Realizar Oferta</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#E0E0E0"  text="#000000">
<font color=$bgcolor1>
<b>
Licitación nro: <? echo $nro_licitacion; ?>
</b>
</font>
<FORM name="form" method="POST" action=" <? echo  $link2; ?> " >
<input type="hidden" name="noparticipa" value="0">

<?
$query="Select nombre,valor_dolar_lic from (licitaciones.licitacion INNER join licitaciones.moneda on licitacion.id_moneda = moneda.id_moneda and licitacion.id_licitacion = $nro_licitacion)";
$resultado = $db->execute($query) or die ($query);
$moneda=$resultado->fields['nombre'];
$valor_dolar=$resultado->fields['valor_dolar_lic'];
//if ($valor_dolar=="") $valor_dolar=0;
if ($moneda=='Pesos') {
echo "<TABLE align=\"right\" with=\"100%\">";
 echo "<tr>";
   echo "<td>";
   echo "Valor Dolar";
   echo "</td>";
   echo "<td>";
   echo "<INPUT TYPE=\"TEXT\"  name=\"valor_moneda\" size=\"5\" value=\"$valor_dolar\">";
   echo "</td>";
   echo "<td>";
   echo "<INPUT TYPE=\"submit\"  name=\"aceptar_moneda\" value=\"Aceptar\">";
   echo "</td>";
   echo "</tr>";
   echo "</TABLE>";

echo "<br>";
echo "<br>";
}
?>
<TABLE align="center" with="100%">
  <tr bgcolor="#c0c6c9" border="0" align="center" >
           <TD width="50">
            <FONT color="#006699" size="2" face="Arial, helvetica, sans-serif">
            Renglon
            </FONT>
           <td width="90" align="center">
             <font color="#006699" size="2" face="Arial, helvetica, sans-serif">
             Titulo
             </font>
           </td>
           <td>
           </td>
           <td>
           </td>
  </tr>
  <tr bgcolor="#c0c6c9" >
           <TD>
           <INPUT TYPE="text"  name="codigo" value="<? if ($modifica) echo $codigo;?>  " size="25">
           </TD>
            <td>
          <INPUT TYPE="text"  name="titulo" value="<? if ($modifica) echo $titulo;?> " size="55">
          </td>
          <td>
          <INPUT TYPE="submit"  name="agregar" value="Agregar" <? if ($modifica) echo "disabled";?>>
          </td>
          <td>
          <INPUT TYPE="submit"  name="Cambiar" value="Cambiar"  <? if (!($modifica)) echo "disabled";?> >
          </td>
 </tr>
 </table>
<br>
<br>
<TABLE  align="center" border="0" with="100%">
<tr align="left" bgcolor="#c0c6c9">
           <td>
           </td>
           <td width="290">
           <font color="#006699" size="2" face="Arial, helvetica, sans-serif">
           Renglon
           </FONT>
           </td>
           <td width="290">
           <font color="#006699" size="2" face="Arial, helvetica, sans-serif">
           Titulo
           </FONT>
           </td>
           <td width="100" colspan="2" >
           <font color="#006699" size="2" face="Arial, helvetica, sans-serif">
           Acción a Tomar
           </FONT>
           </td>
</tr>
<?
//hidden para actualizar el renglon
$valor=$_POST["renglon"];
//echo $valor;
echo "<input type=\"hidden\" name=\"id_renglon_modificar\" value='$valor'>";

$query = "SELECT * FROM licitaciones.renglon WHERE id_licitacion = $nro_licitacion ORDER BY codigo_renglon";
$resultados=$db->execute("$query") or die("$query");
$i=0;
$cantidad_filas = $resultados->RecordCount();
while ( $i< $cantidad_filas )  {
     $id_renglon = $resultados->fields['id_renglon'];
     $nro_accion=$resultados->fields['nro_renglon'];
     $nro_item = $resultados->fields['nro_item'];
     $titulo = $resultados->fields['titulo'];
     $codigo = $resultados->fields['codigo_renglon'];
     $nombre_select="accion".$id_renglon;
     $nombre_boton="boton".$id_renglon;
     echo "<input type=\"hidden\" name=\"control\" value=\"false\">";
     echo "<tr align='center' bgcolor='$bgcolor2'>";
     echo "<td align=\"Center\">";
     echo "<input type=\"radio\" name=\"renglon\" value=\"$id_renglon\" Onclick='activar_botones();' ></td>";
     echo "</td>";
     echo "<td align=\"Left\">";
     echo $codigo;
     echo "</td>";
     echo "<td align=\"Left\">";
          echo $titulo;
     echo "</td>";
     echo "<td>";
     if($resultados->fields['no_participamos']=='f'){
     //Onchange=\" desactiva_recarga1($nombre_select,boton$id_renglon,$id_renglon); \"
                echo "<Select name=\"$nombre_select\" onchange=\"desactiva_recarga($nombre_select,$id_renglon)\" >";
                echo "<option selected>Lamentamos no participar</option>";
                echo "<option>Realizar Oferta  </option>";
                echo  "</select>";
                }
  //Onchange=\" desactiva_recarga2($nombre_select,boton$id_renglon,$id_renglon); \"                                           }
              else{
                  echo "<Select name=\"$nombre_select\" onchange=\"desactiva_recarga($nombre_select,$id_renglon)\" >";
                  echo "<option selected>Realizar Oferta  </option>";
                  echo "<option>Lamentamos no participar</option>";
                  echo  "</select>";
                  }

      echo "</td>";
      echo "<td>";
      $link = encode_link("agregar_producto.php",array("renglon" => $id_renglon,
                                                "licitacion" => $nro_licitacion,
                                                "moneda"=>$moneda,
                                                "valor_moneda"=>$valor_dolar));
      if($resultados->fields['no_participamos']=='f')
                 echo "<INPUT TYPE=\"button\"name=\"boton$id_renglon\" value=\"Realizar\"   onClick=\"location.href='$link' \";\ disabled='true'> " ;
                 else
                 echo "<INPUT TYPE=\"button\"name=\"boton$id_renglon\" value=\"Realizar\"   onClick=\"location.href='$link' \";\ > " ;
      echo "</td>";
      echo "</tr>";


$resultados->MoveNext();
$i++;
}
?>
</TABLE>
<br>
<br>
<br>
<div align="center">
  <INPUT type="button" name="volver" style='width:140;'   value="Volver" <? if ($modifica) echo "disabled";  ?>  onClick="document.location='<?echo  $ref; ?>'; return false;" >
  <INPUT type="submit" name="terminar_licitacion" style='width:140;'   value="Terminar Licitacion"  <? if ($modifica) echo "disabled";  ?>>
  <INPUT type="submit" name="eliminar_renglon" style='width:140;'  value="Eliminar Renglon" disabled <? if ($modifica) echo "disabled";  ?>>
  <INPUT type="submit" name="modificar_renglon" style='width:140;' value="Modificar Renglon" disabled <? if ($modifica) echo "disabled";  ?>  >
</div>
</FORM>
</body>
</HTML>