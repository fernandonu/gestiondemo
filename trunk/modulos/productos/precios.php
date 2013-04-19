<?php
/*
$Author: marco_canderle $
$Revision: 1.24 $
$Date: 2003/09/11 18:28:51 $
*/
include("../../config.php");

?>
<html>
<head>
<title>Precios</title>
<style type="text/css">
<!--
a {
	cursor: hand;text-decoration:none;
	color: #000000;
}
-->
</style>

</head>
<body bgcolor="#E0E0E0">
<center>
<br>
<form name="form1" action="precios.php" method="POST">

<?
/*echo "producto ".$parametros["tipo_prod"];
echo "el post es \n".$_POST["select_producto"];*/
echo "Seleccione el Tipo de Producto ";
echo "<select name='select_producto' Onchange=document.form1.submit()>";
echo "<option value=''> </option>";
db_tipo_res("a");
     $query_prod="SELECT DISTINCT codigo,descripcion FROM tipos_prod";
     $resultados_prod = $db->Execute("$query_prod") or die($db->ErrorMsg());
     $filas_encontradas = $resultados_prod->RecordCount();
     $sel=0;    	
        for($i=0;$i<$filas_encontradas;$i++)  {
        	if(($resultados_prod->fields["codigo"] == $_POST["select_producto"])||($resultados_prod->fields["codigo"] == $parametros["tipo_prod"])) {$sel=1;
                $string=$resultados_prod->fields["descripcion"];
                echo "<option selected value='".$resultados_prod->fields['codigo']."'> $string </option>";
                $resultados_prod->Movenext();
            }
           else {
                $string=$resultados_prod->fields["descripcion"];
                echo "<option value='".$resultados_prod->fields['codigo']."' > $string  </option>";
                $resultados_prod->Movenext();
                }
   }
 
 ?>
 <option value='todos' <?php if (!$sel) echo "selected"; ?>> Todos </option>
</select>
</center>
<hr>
<?
//seleccionamos los productos de acuerdo al tipo que se especifico
 if($parametros["tipo_prod"]!="") 
  $tipos=$parametros["tipo_prod"];
else 
 {if ($_POST['select_producto']=="")
   $tipos="todos";
  else
  $tipos=$_POST['select_producto'];
 } 
if($tipos!="todos")
  $query="select * from productos WHERE tipo = '".$tipos."'";
else 
  $query="select * from productos order by tipo";
	 


db_tipo_res("a");
$resultado = $db->Execute($query) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
?>
<table width="100%" border="0">
      <tr bgcolor="#c0c6c9">
      <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Cantidad
            de Productos:<? echo $filas_encontradas; ?> </b></font>
      </td>
    </tr>
  </table>
<div style="position:relative; width:100%; height:75%; overflow:auto;">
<table width="100%"  border="0" cellspacing="2">
<tr title="Vea comentarios de los productos" bgcolor="<?php echo "#006699";?>">
<td width="60%"><font color="<?php echo $bgcolor2; ?>"><b>Descripci&oacute;n General</b></font></td>
<td width="20%"><font color="<?php echo $bgcolor2; ?>"><b>Marca</b></font></td>
<td width="20%"><font color="<?php echo $bgcolor2; ?>"><b>Modelo</b></font></td>

<?

$cnr=1;
while (!$resultado->EOF)
{
/*$comentario="Observaciones:".$resultado->fields['desc_gral'];

// Para pasar a la página de proveedores el id del proveedor.*/
$link=encode_link("cargar_precio.php",array("id_producto"=> $resultado->fields["id_producto"],"pagina"=>"precios",
                                            "tipo_prod"=> $tipos));
/*
if ($cnr==1)
{$color2=$bgcolor2;
 $color="#c0c6c9";
 $atrib ="bgcolor=#c0c6c9";
 $cnr=0;
}
else
{$color2=$bgcolor1;
$color="#c0c6c9";
$atrib ="bgcolor=#c0c6c9";
$cnr=1;
}*/
$atrib ="bgcolor='white'";
$color2="Black";
//$atrib.=" onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\"";
$atrib.=" style=cursor:hand";
?>

<tr <?php echo $atrib; ?>>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><b><?php echo $resultado->fields["desc_gral"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";";  ?>><b><?php echo $resultado->fields["marca"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><b><?php echo $resultado->fields["modelo"]; ?></b></font></td>
</tr>
<?php

   //seleccionamos la lista de proveedores y precios del producto seleccionado
 if($filas_encontradas!=0)
 {
 /*NOTA: SI EL QUERY ANTERIOR DA UN RESULTADO VACIO, 
   EL QUERY SIGUIENTE LARGA UN ERROR DE PARSE. 
   POR ESO EL CONTROL DE LAS FILAS ENCONTRADAS
   EN EL QUERY ANTERIOR
 */
  $id_prod=$resultado->fields["id_producto"];
  $query_resultado_final="SELECT razon_social,precio,precios.id_proveedor,precios.observaciones from precios join proveedor on precios.id_proveedor = proveedor.id_proveedor WHERE id_producto = $id_prod" ;
  $resultado_final=$db->Execute("$query_resultado_final") or die($db->ErrorMsg());
  $nro_filas=$resultado_final->RecordCount();
 }//del if
 if($nro_filas!=0)
 {
/* 	
  <tr>
  <td bgcolor="<?php echo "#E0E0E0";?>" ></td>
  <td bgcolor="<?php echo "#006699";?>"> <b><font color="<?php echo $bgcolor2; ?>">Proveedores</font> </b></td>
  <td bgcolor="<?php echo "#006699";?>"> <b><font color="<?php echo $bgcolor2; ?>">Precio </font></b></td>
  </tr>
*/
?>
<?PHP 
 }//de if($nro_filas!=0)
$cnr=1;
?>
<tr>
<td colspan="4">
<table style="position:relative;left:20;" align="left" width="80%">
<?php
 while(!$resultado_final->EOF)
{ 
 //segun el proveedor que seleccione se actualizan las variables para 
 //pasar en el link en los td que siguen (en la funcion encode_link)
 $precio_prov=$resultado_final->fields["precio"];
 $prov_prov=$resultado_final->fields["id_proveedor"];
 $desc_prov=$resultado_final->fields["observaciones"]; 
 
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
$cnr=1;}
//$atrib.=" onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\"";
$atrib.="style=cursor:hand";
?>  
 <tr <?php echo $atrib; ?>>
 <?php echo "<a href='".encode_link("cargar_precio.php",array("id_producto"=> $resultado->fields["id_producto"],"pagina"=>"precios","tipo_prod"=> $tipos, "precio"=>$precio_prov, "id_proveedor"=>$prov_prov, "observaciones"=>$desc_prov))."'>"; ?>
 <td width="25%"><b><font color="<?php echo $color2; ?>"> <?php echo "U\$S ".$resultado_final->fields["precio"]; ?></A></font></b></td>
 <td width="75%"><b><font color="<?php echo $color2; ?>"> <?php echo $resultado_final->fields["razon_social"]; ?></A></font></b></td>
 </a>
 </tr>
<?PHP
 $resultado_final->MoveNext();
}//del while(!$resultado_final->EOF)
?>
</table>
</div>
</td>
</tr>
<?php
$resultado->MoveNext();
}   //del while de proveedores.

?>
</table>
</div>
</form>
</html>