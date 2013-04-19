<?php
/*
$Author: ferni $
$Revision: 1.30 $
$Date: 2005/10/18 21:38:57 $
*/

include("../../config.php");

$tipo_p=$_POST["prod"] or $tipo_p=$parametros["tipo"];
$texto=$_POST["buscar_text"] or $texto=$parametros["texto"];
$campo_sel=$_POST["select_campo"] or $campo_sel=$parametros["campo"];

if ($campo_sel=='' || !isset($campo_sel)) $campo_sel=5;  //la primera vez que entra a la pagina esta seleccionado 'Todos'
if ($tipo_p=='') $flag=0; //es la proimera vez que entra a la pagina
 else unset($flag);
if ($tipo_p=='vacio') $tipo_p='todos';

if($_POST["nuevo_producto"]=="Nuevo Producto")
{
 $link1=encode_link($html_root."/index.php",array("menu"=>"altas_prod","extra"=>array ("pagina"=>"productos", "tipo"=> $tipo_p, "texto"=> $texto, "campo"=> $campo_sel)));
 echo "<html><head><script language=javascript>";
 echo "window.parent.location='$link1';";
 echo "</script></head></html>";
}

if($_POST["modif_precio"]=="Modificar Precio")
{
 $link=encode_link("../productos/cargar_precio.php",array("id_producto"=> $_POST["producto_id"],"pagina"=>"productos","tipo_prod"=> $_POST["tipo"],"texto"=> $texto, "campo"=> $campo_sel, "precio"=>$_POST["precio"], "id_proveedor"=>$_POST["proveedor"],"observaciones"=>$_POST["observa"]));
 echo "<html><head><script language=javascript>";
 echo "location.href='$link';";
 echo "</script></head></html>";
}

if($_POST["add"]=="Agregar Precio")
{
 $link=encode_link("../productos/cargar_precio.php",array("id_producto"=>$_POST["producto_id"],"pagina"=>"precios","tipo_prod"=> $_POST["tipo"], "texto"=> $texto, "campo"=> $campo_sel));
 echo "<html><head><script language=javascript>";
 echo "location.href='$link';";
 echo "</script></head></html>";
}

if($_POST["del"]=="Eliminar Precio")
{
 if($_POST["producto"]!="")
 {
	$pr=$_POST['producto_id'];
	$pv=$_POST['proveedor'];
	$query="delete from precios where id_producto=$pr and id_proveedor=$pv";
	$db->Execute($query) or die($db->ErrorMsg().$query);
	
 }
}

if ($_POST['stock']=="Añadir Stock"){

 $link=encode_link("../stock/stock_add.php",array("id_producto" => $_POST['producto_id'],"id_proveedor" => $_POST['proveedor'],"id_deposito" => $_POST['deposito'],"tipo"=>$tipo_p, "texto"=> $texto, "campo"=> $campo_sel));
 echo "<html><head><script language=javascript>";
 echo "location.href='$link';";
 echo "</script></head></html>";
}

?>
<!--
<html>
<head>
<title>Proveedores-Producto</title>
-->
<?php
//echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
echo $html_header;
include("../ayuda/ayudas.php");
?>
<script src="<?=$html_root."/lib/funciones.js"?>" ></script>
<SCRIPT language="javascript">
function cambiar_check(fila)
{
 if (document.all.producto.length > 1)
  document.all.producto[fila].checked="true";
 else
  document.all.producto.checked="true";
}

function cambiar_prov(id_prov)
{
 document.all.proveedor.value=id_prov;
}

function cambiar_precio(id_prov,precio,id_prod,tipo,obs,dep,cant_s)
{
 document.all.proveedor.value=id_prov;
 document.all.tipo.value=tipo;
 document.all.producto_id.value=id_prod;
 document.all.precio.value=precio;
 document.all.deposito.value=dep;
 document.all.cant_s.value=cant_s;
 if(obs!="null")
  document.all.observa.value=obs;
}

function cambiar_prod(id_prod,tipo,precio)
{
 document.all.precio.value=precio;
 document.all.producto_id.value=id_prod;
 document.all.tipo.value=tipo;
}

function confirm_del_prod()
{var acepta=confirm('¿Está seguro que desea eliminar el producto?');
 if(acepta)
  return true;
 else
  return false;
}

</script>
</head>
<body bgcolor="<?php echo $bgcolor3; ?>">
<div align="right">
		<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/productos/ayuda_detalle_prod.htm" ?>', 'DETALLE DE LOS PRODUCTOS')" >
	</div>
<center>
<br>
<?php
//modificamos el form dependiendo de donde se llamo la pagina
if ($parametros["modulo"]=="remito") //pagina llamada desde remitos
{$link=encode_link($parametros["nombre_pagina"],array("modulo" => $parametros["modulo"],
												 "tipo" => '',
												 "remito" => $parametros["remito"],
												 "nombre_pagina" => $parametros["nombre_pagina"]));

 $link2=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
									"tipo" => '',
									"remito" => $parametros["remito"],
									"nombre_pagina" => $parametros["nombre_pagina"]));
}
elseif($parametros["modulo"]=="licitaciones") // viene de licitaciones
{
$link=encode_link($parametros["nombre_pagina"],array("modulo" => $parametros["modulo"],
												 "tipo" => $tipo_p,
												 "licitacion" => $parametros["licitacion"],
												 "renglon" => $parametros["renglon"],
												 "item" => $parametros["item"],
												 "nombre_pagina" => "productos1.php",
												 "producto"=>'',
												 "moneda"=>$parametros['moneda'],
												 "valor_moneda"=>$parametros['valor_moneda']));

$link2=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
								 //  "tipo" => $parametros['tipo'],
								   "licitacion" => $parametros["licitacion"],
								   "renglon" => $parametros["renglon"],
								   "item" => $parametros["item"],
								   "nombre_pagina" => $parametros["nombre_pagina"],
								   "moneda"=>$parametros['moneda'],
									"valor_moneda"=>$parametros['valor_moneda']));



}
else
{
 $link2=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
								  "tipo" => $tipo_p

								   ));
 $link=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
								  "tipo" => $tipo_p,
								   "texto" => $texto,
								   "campo" => $campo_sel

								   ));
}
?>
<form name="form1" action="<?php echo $link2;  ?>" method="POST"  onload="document.all.buscar_text.focus()">

<input type="hidden" name="prod" value="">
<table class="bordes" cellspacing="2" bgcolor=<?=$bgcolor3?>>
<tr>
<td><b>Tipo Producto:</b></td>
<td><select name="tipo_producto" onKeypress= "buscar_op(this);" onblur="borrar_buffer()" onclick="borrar_buffer()">
<?php
$sql="select descripcion, codigo from tipos_prod order by descripcion";
$resultado_desc = $db->Execute($sql) or die($db->ErrorMsg());
while (!$resultado_desc->EOF){
?>
  <option value="<?php echo $resultado_desc->fields['codigo']; ?>" <?php if ($tipo_p==$resultado_desc->fields['codigo']) echo "selected" ?>><?php echo $resultado_desc->fields['descripcion']; ?>
<?php
  $resultado_desc->MoveNext();
}
?>
 <option value="todos" <?php if ($_POST['tipo_producto']=="todos" || $tipo_p=='' || $tipo_p=="todos" ) echo "selected" ?>>Todos
</select>
</td>
<td align="left">
<b>
Buscar:
</b>
<input type="text" name="buscar_text" value="<?php echo $texto; ?>" onkeypress="if (getkey(event) == 13) {document.all.prod.value=document.all.tipo_producto.options[document.all.tipo_producto.options.selectedIndex].value;}">
</td>
<td><b>
en:
</b>
<select name="select_campo">
 <option value=1 <? if ($campo_sel=='1') echo 'selected'?>> Marca  </option>
 <option value=2 <? if ($campo_sel=='2') echo 'selected'?>> Modelo </option>
 <option value=3 <? if ($campo_sel=='3') echo 'selected'?>> Descripcion general </option>
 <option value=4 <? if ($campo_sel=='4') echo 'selected'?>> Proveedor </option>
 <option value=5 <? if ($campo_sel=='5') echo 'selected'?>> Todos </option>
</select>

<td>
<input type="submit" name="boton" value="Buscar" onclick="document.all.prod.value=document.all.tipo_producto.options[document.all.tipo_producto.options.selectedIndex].value">
</td>
</tr>
</table>
<hr>
<input type="hidden" name="cant" value="<?php if(!isset($_POST['cant'])) echo "-1"; else echo ($_POST['cant']-1); ?>">
<b>Lista de Proveedores para <?php if ($tipo_p=="") echo "productos"; else echo $tipo_p; ?></b>
<script>
document.all.buscar_text.focus();
</script>

</form>
<br>
<div style="position:relative; width:100%; height:63%; overflow:auto;">
<form name="form" action="<?php echo $link;  ?>" method="POST">

<table class="bordes" cellspacing="2" width="100%" bgcolor=<?=$bgcolor2?>>
<tr title="Vea comentarios de los productos"  id=mo>
<td><font color="<?php echo $bgcolor3; ?>"><b>Descripcion Gral.</b></font></td>
<td><font color="<?php echo $bgcolor3; ?>"><b>Marca</b></font></td>
<td><font color="<?php echo $bgcolor3; ?>"><b>Modelo</b></font></td>
<td><font color="<?php echo $bgcolor3; ?>"><b>Stock</b></font></td>
</tr>
<?php

// consulta para recuperar productos proveedores precios y stock
$parte1="
select * from
(select productos.id_producto, productos.tipo, productos.desc_gral,productos.marca,productos.modelo,precios.observaciones, precios.precio,proveedor.id_proveedor, proveedor.razon_social ,stock.cant_disp, depositos.id_deposito, depositos.nombre as deposito
from general.productos left join general.precios using (id_producto) left join general.proveedor using (id_proveedor) left join stock using (id_producto,id_proveedor)  left join general.depositos using (id_deposito)";

$parte2=" ) as res

join

(select productos.id_producto, sum (stock.cant_disp) as cant_total from general.productos left join stock using (id_producto) group by productos.id_producto) as res1 using (id_producto)";

switch ($campo_sel){
	 case 1: $campo='productos.marca'; break;
	 case 2: $campo='productos.modelo'; break;
	 case 3: $campo='productos.desc_gral'; break;
	 case 4: $campo='proveedor.razon_social'; break;
	 case 5: $campo='TODOS';break;
	}

if ($_POST['boton']=="Buscar") //se presiona el boton buscar
{

if ($tipo_p!="todos")  //se selecciono un tipo de producto
{ if ($texto!="") {
	if ($campo_sel==5) //selecciono TODOS
	    $where=" where productos.tipo='".$tipo_p."' and productos.activo_productos='TRUE' and ( productos.marca ilike '%$texto%' or productos.modelo ilike '%$texto%' or productos.desc_gral ilike '%$texto%' or proveedor.razon_social ilike '%$texto%') ";
	 else $where=" where productos.tipo='".$tipo_p."' and productos.activo_productos='TRUE' and $campo ilike '%$texto%'";
   }
   else {  //campo de texto esta vacio
	 $where=" where productos.tipo='".$tipo_p."' and productos.activo_productos='TRUE' ";
   }
}
else { 
	 if ($texto !="") {
	   if ($campo_sel == 5)
		  $where=" where productos.marca ilike '%$texto%' or productos.modelo ilike '%$texto%' or productos.desc_gral ilike '%$texto%' or proveedor.razon_social ilike '%$texto%'";
	   else $where=" where $campo ilike '%$texto%'";
	 }
	 }
$query= $parte1.$where.$parte2;
$query.=" order by res.id_producto,res.razon_social";
}  //fin de boton =buscar
elseif (!isset ($flag)) {  //no se presiona buscar 

?>
<script>
document.all.boton.focus();
</script> 
<?
if (($tipo_p !='') && ($tipo_p!='todos'))	{ //esta selecciona tipo de producto
	if ($texto !="" &&  $campo_sel== 5)
	  	$where=" where productos.tipo='".$tipo_p."' and productos.activo_productos='TRUE' and ( productos.marca ilike '%$texto%' or productos.modelo ilike '%$texto%' or productos.desc_gral ilike '%$texto%' or proveedor.razon_social ilike '%$texto%')";
	  	elseif ( $texto !="") {
	  	 $where=" where productos.tipo='".$tipo_p."' and productos.activo_productos='TRUE' and $campo ilike '%$texto%'";  //tipo de prod y texto seleccionado
	    }
	    else $where=" where productos.tipo='".$tipo_p."' and productos.activo_productos='TRUE' ";
	}
	else{
		 if (($tipo_p == 'todos') && ($texto !="")) 
		   if ($campo_sel == 5 ) 
		    $where =" where productos.marca ilike '%$texto%' or productos.modelo ilike '%$texto%' or productos.desc_gral ilike '%$texto%' or proveedor.razon_social ilike '%$texto%'";
		    else $where=" where $campo ilike '%$texto%'";
		}
$query.=$where;
$query= $parte1.$where.$parte2;

$query.=" order by res.id_producto,res.razon_social";
}
else {  //la primera vez que entra en la pagina

echo " <div align=center> PARA VER TODOS LOS PRODUCTOS PRESIONE EL BOTON BUSCAR<bR>PARA REALIZAR UNA BUSQUEDA SELECCIONE LOS PARAMETROS Y PRESIONE EL BOTON BUSCAR </div><bR>";
 }

//echo $query;

$resultado=$db->Execute ($query) or die($db->ErrorMsg().$query);

$filas_encontradas=$resultado->RecordCount();

//generar variables para dividir la consulta
$productos=array();
$i=-1;
while (!$resultado->EOF){
	
	if (($i>-1) && ($productos[$i]["id_prod"]==$resultado->fields['id_producto'])){
      $tam=sizeof($productos[$i]['stock']);
	  if ($productos[$i]['stock'][$tam-1]['proveedor']==$resultado->fields['razon_social']){
	     $productos[$i]['stock'][$tam-1]['cant_stock']+=$resultado->fields['cant_disp'];
	     $tam_dep=sizeof($productos[$i]['stock'][$tam-1]['depositos']);
	 //  $productos[$i]['stock'][$tam-1]['depositos'][$tam_dep]=$resultado->fields['deposito'];
	     $productos[$i]['stock'][$tam-1]['depositos'][$tam_dep][0]=$resultado->fields['deposito'];
         $productos[$i]['stock'][$tam-1]['depositos'][$tam_dep][1]=$resultado->fields['cant_disp'];
         $productos[$i]['stock'][$tam-1]['depositos'][$tam_dep][2]=$resultado->fields['id_deposito'];
	  }
	  else {
	  $productos[$i]['stock'][$tam]=array();
	  $productos[$i]['stock'][$tam]['precio']=$resultado->fields['precio'];
	  $productos[$i]['stock'][$tam]['obs_precio']=$resultado->fields['observaciones'];
	  $productos[$i]['stock'][$tam]['proveedor']=$resultado->fields['razon_social'];
      $productos[$i]['stock'][$tam]['id_proveedor']=$resultado->fields['id_proveedor'];
	  $productos[$i]['stock'][$tam]['cant_stock']=$resultado->fields['cant_disp'];
	  $productos[$i]['stock'][$tam]['depositos']=array();
   // $productos[$i]['stock'][$tam]['depositos'][0]=$resultado->fields['deposito'];
   	  $productos[$i]['stock'][$tam]['depositos'][0][0]=$resultado->fields['deposito'];
	  $productos[$i]['stock'][$tam]['depositos'][0][1]=$resultado->fields['cant_disp'];
	  $productos[$i]['stock'][$tam]['depositos'][0][2]=$resultado->fields['id_deposito'];
	  }
	}
	else{
	 $i++;
	 $productos[$i]["id_prod"]=$resultado->fields['id_producto'];
	 $productos[$i]["descripcion"]=$resultado->fields['desc_gral'];
	 $productos[$i]["marca"]=$resultado->fields['marca'];
	 $productos[$i]["modelo"]=$resultado->fields['modelo'];
	 $productos[$i]["cant_total"]=$resultado->fields['cant_total'];
	 $productos[$i]["stock"]=array();
	 $productos[$i]['stock'][0]=array();
	 $productos[$i]['stock'][0]['precio']=$resultado->fields['precio'];
	 $productos[$i]['stock'][0]['obs_precio']=$resultado->fields['observaciones'];
	 $productos[$i]['stock'][0]['proveedor']=$resultado->fields['razon_social'];
     $productos[$i]['stock'][0]['id_proveedor']=$resultado->fields['id_proveedor'];
	 $productos[$i]['stock'][0]['cant_stock']=$resultado->fields['cant_disp'];
	 $productos[$i]['stock'][0]['depositos']=array();
  // $productos[$i]['stock'][0]['depositos'][0]=$resultado->fields['deposito'];
	 
	 $productos[$i]['stock'][0]['depositos'][0]=array();
	 $productos[$i]['stock'][0]['depositos'][0][0]=$resultado->fields['deposito'];
	 $productos[$i]['stock'][0]['depositos'][0][1]=$resultado->fields['cant_disp'];
	 $productos[$i]['stock'][0]['depositos'][0][2]=$resultado->fields['id_deposito'];
	
	}

 $resultado->MoveNext();
}

$filas_encontradas=$resultado->RecordCount();
$cnr=1;
$cont_filas=0; //contador para saber en que fila se realizo un click
$tipo_producto2=$_POST['tipo_producto'];
if ($tipo_producto=="")
 $tipo_producto2=$tipo_p;
 
$tam_prod=sizeof($productos);

for ($ind_prod=0; $ind_prod < $tam_prod;$ind_prod++)
{
$link=encode_link("detalle_productos.php",array("modulo"=> $parametros['modulo'],
                                                "nombre_pagina" => $parametros["nombre_pagina"],
                                                "producto"=>$productos[$ind_prod]["id_prod"],
                                                "tipo" => $tipo_p,
                                                "texto"=> $texto, "campo"=> $campo_sel,
                                                 "licitacion" => $parametros["licitacion"],
                                                 "renglon" => $parametros["renglon"],
                                                 "item" => $parametros["item"],
                                                 "moneda"=>$parametros['moneda'],
												 "valor_moneda"=>$parametros['valor_moneda'],
												 "tipo_producto"=>$tipo_producto2));


$atrib ="bgcolor='#eeeeee'";
$color2="Black";
$atrib.=" style=cursor:hand";


//traemos las descripciones tenicas de cada producto y lo almacenamos en
//un string, para poder mostrarlo.

$query="select titulo from descripciones where id_producto=".$productos[$ind_prod]['id_prod'];
$desc_prod=$db->Execute($query) or die ($db->ErrorMsg().$query);

if($desc_prod->RecordCount()>0)
{
 $descripcion="DESCRIPCIONES:";
 $desc_prod->Move(0);
 while(!$desc_prod->EOF)
 {if($desc_prod->CurrentRow() == 0)
   $descripcion.=" ".$desc_prod->fields["titulo"];
  else
   $descripcion.=", ".$desc_prod->fields["titulo"];
  $desc_prod->MoveNext();
 }

?>
<tr <?php echo $atrib; ?> title="<?echo $descripcion?>">
<?
}//del if
else
{?>
<tr <?php echo $atrib; ?>>
<?
}

?>

<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php echo $productos[$ind_prod]["descripcion"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php echo $productos[$ind_prod]["marca"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php echo $productos[$ind_prod]["modelo"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><? if ($productos[$ind_prod]['cant_total']=="") echo '0'; else echo $productos[$ind_prod]['cant_total'];?></b></font> </td>

</tr>
<tr>
<td colspan="4">
<table align="center" width="95%">
<?php
$cnr=0;$first=1;
/*VER ESTE IF ?????????????

if (($filas==0)) //imprimo el boton de agregar precios aunque no tenga precios de antes
{?>
<tr>   <td width="25%"></td><td width="75%"></td>
   <td width="5%"><input type="submit" name="add" value="Agregar Precio" style="width:100%" onclick="cambiar_prod(<?php echo $resultado->fields['id_producto'];?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value)"></td>
 </tr>

<?
}*/
//while para proveedores

//while (!$result->EOF){
$tam_stock=sizeof($productos[$ind_prod][stock]);
for ($ind_stock=0;$ind_stock < $tam_stock; $ind_stock++)
{
 /*
 if ($cnr==1)
  {$color2=$bgcolor1;
   $color=$bgcolor2;
   $atrib ="bgcolor='$bgcolor2'";
   $cnr=0;
   }
 else
  {$color2=$bgcolor2;
   $color=$bgcolor1;
   $atrib ="bgcolor='$bgcolor1'";
   $cnr=1;
  }
 */
  $atrib=atrib_tr();


//recupera la ovservacion del precio para cada  producto
$observac=htmlspecialchars($productos[$ind_prod]['stock'][$ind_stock]['obs_precio']);

$tam_depos=sizeof ($productos[$ind_prod]['stock'][$ind_stock]['depositos']);

$filas=$tam_depos;
 for ($ind_dep=0; $ind_dep < $tam_depos; $ind_dep++){
  if ($productos[$ind_prod]['stock'][$ind_stock]['depositos'][$ind_dep][0]==null)
	$desc_dep="";
  else{
	 if ($ind_dep==0)
	  $desc_dep="Deposito ".$productos[$ind_prod]['stock'][$ind_stock]['depositos'][$ind_dep][0]." ".$productos[$ind_prod]['stock'][$ind_stock]['depositos'][$ind_dep][1];
	 else
	  $desc_dep.="\nDeposito ".$productos[$ind_prod]['stock'][$ind_stock]['depositos'][$ind_dep][0]." ".$productos[$ind_prod]['stock'][$ind_stock]['depositos'][$ind_dep][1];
  }
 }


 if($productos[$ind_prod]['stock'][$ind_stock]['id_proveedor']!="")
 {
  if (($parametros['modulo']=='remito')||($parametros['modulo']=='licitaciones'))
  { ?>
   <tr <?php echo $atrib; ?> onclick='document.all.boton1.disabled=false; cambiar_prov(<?php echo $productos[$ind_prod]['stock'][$ind_stock]['id_proveedor']; ?>);'>


   <? }
   else {?>
   <tr <?php echo $atrib; ?> >
	<? } ?>
  <td width="5%" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $productos[$ind_prod]['stock'][$ind_stock]['id_proveedor']?>,<? echo $productos[$ind_prod]['stock'][$ind_stock]['precio']?>,<? echo $productos[$ind_prod]['id_prod']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<? echo $observac;?>',<? if($productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]  ?>,<? if($productos[$ind_prod]['stock'][$ind_stock]['cant_stock']==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['cant_stock']?>);if (document.all.modif_precio) document.all.modif_precio.disabled=false; if (document.all.del)document.all.del.disabled=false;if (document.all.stock)document.all.stock.disabled=false;">
  <input type="radio" name="producto" value="<?php  echo $productos[$ind_prod]['id_prod'] ?>" >
  </td>
  <td width="15%" title="<? echo $observac?>" onclick="cambiar_check(<?php echo $cont_filas; ?>); cambiar_precio(<?php echo $productos[$ind_prod]['stock'][$ind_stock]['id_proveedor']?>,<? echo $productos[$ind_prod]['stock'][$ind_stock]['precio']?>,<? echo $productos[$ind_prod]['id_prod']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<? echo $observac?>',<? if($productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]?>,<?if($productos[$ind_prod]['stock'][$ind_stock]['cant_stock']==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['cant_stock']?>);if (document.all.modif_precio) document.all.modif_precio.disabled=false; if (document.all.del) document.all.del.disabled=false; if (document.all.stock)document.all.stock.disabled=false;"><b><font color="<?php echo $color2; ?>"><?php echo "U\$S ".$productos[$ind_prod]['stock'][$ind_stock]['precio']; ?></font></b></td>
  <td width="63%" title="Proveedor" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $productos[$ind_prod]['stock'][$ind_stock]['id_proveedor']?>,<? echo $productos[$ind_prod]['stock'][$ind_stock]['precio']?>,<? echo $productos[$ind_prod]['id_prod']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<? echo $observac?>',<? if($productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]?>,<? if($productos[$ind_prod]['stock'][$ind_stock]['cant_stock']==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['cant_stock']?>); if (document.all.modif_precio)document.all.modif_precio.disabled=false; if (document.all.del)document.all.del.disabled=false; if (document.all.stock) document.all.stock.disabled=false;"><b><font color="<?php echo $color2; ?>"><?php echo $productos[$ind_prod]['stock'][$ind_stock]['proveedor']; ?></font></b></td>
  <td width="10%" align="center"  title="<? if ($desc_dep) echo "$desc_dep"; else echo 'Sin Stock'; ?>" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $productos[$ind_prod]['stock'][$ind_stock]['id_proveedor']?>,<? echo $productos[$ind_prod]['stock'][$ind_stock]['precio']?>,<? echo $productos[$ind_prod]['id_prod']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<? echo $observac?>',<? if($productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['depositos'][0][1]?>,<? if($productos[$ind_prod]['stock'][$ind_stock]['cant_stock']==null)echo "0";else echo $productos[$ind_prod]['stock'][$ind_stock]['cant_stock']?>); if (document.all.modif_precio) document.all.modif_precio.disabled=false; if (document.all.del)document.all.del.disabled=false; if (document.all.stock)document.all.stock.disabled=false;"><? if($productos[$ind_prod]['stock'][$ind_stock]['cant_stock']) echo $productos[$ind_prod]['stock'][$ind_stock]['cant_stock']; else echo '0';?></td>
 <?
 $cont_filas++;
 }
else {
?>
<tr>
<td  width="5%"></td>
<td width="15%"></td>
<td width="63%"></td>
<td width="10%"></td>
<?
}
if($first)
 {$first=0;
 ?>
 <td width="5%" bgcolor="<?echo $bgcolor1?>">
 <input type="submit" name="add" value="Agregar Precio" title="Agregar Precio/Agregar Proveedor" style="width:100%" onclick="cambiar_prod(<?php echo $productos[$ind_prod]['id_prod'];?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value)">
 </td>
<?
 }
?>
</tr>
<?php

//$cont_filas++;

} // fin for stock
?>
</table>
</td>
</tr>
<?php

} //fin for productos
if ($filas_encontradas==0)  //envio cartel de que no hay en stock ese producto
{
?>
  <tr><td colspan=4 bgcolor="<?php echo $bgcolor1; ?>" align="center"><font color="<?php echo $bgcolor2; ?>"><b> No hay <?php echo $tipo; ?> en stock </b></font></td></tr>
<?php
}
?>
</table>
</div>

</center>
 <hr>

<?php
if(($parametros['modulo']=="remito")||($parametros['modulo']=="licitaciones"))
{?>
<center>
  <input type="submit" name="boton1" value="Agregar" disabled>
  <?php $link=encode_link($parametros['nombre_pagina'],array("modulo" => $parametros["modulo"],
												 "tipo"=>$parametros['tipo_producto'],
												 "licitacion" => $parametros["licitacion"],
												 "renglon" => $parametros["renglon"],
												 "item" => $parametros["item"],
												 "nombre_pagina" => $parametros['nombre_pagina'],
												 "producto"=>'',
												 "moneda"=>$parametros['moneda'],
												 "remito" => $parametros["remito"],
												 "valor_moneda"=>$parametros['valor_moneda']));
  ?>
 <input type="button" name="boton2" value="Volver" Onclick="location.href='<?php echo $link; ?>';">
 </CENTER>

<?PHP

}//cerramos el if(($parametros['modulo']=="remito")||($parametros['modulo']=="licitaciones"))

else //estamos en el modulo productos
{ ?>
 <center>
   <input type="submit" name="nuevo_producto" value="Nuevo Producto">&nbsp;&nbsp;
   <input type="submit" name="modif_precio" value="Modificar Precio" disabled>&nbsp;&nbsp;
   <input type="submit" name="del" value="Eliminar Precio" disabled onclick="if(document.all.cant_s.value!='0'){ alert('No se puede eliminar un precio si aun hay stock disponible (adquirido al proveedor seleccionado).');return false;}else{return confirm('Esta seguro que desea eliminar el precio seleccionado?');}">&nbsp;&nbsp;
<?if (permisos_check("inicio","añadir_stock")){?>
   <input type="submit" name="stock" value="Añadir Stock" disabled>&nbsp;&nbsp;
<? } ?>
 </center>
 <? } ?>
<input type="hidden" name="producto_id" value="0">
<input type="hidden" name="proveedor" value="0">
<input type="hidden" name="precio" value="0">
<input type="hidden" name="tipo" value="0">
<input type="hidden" name="observa" value="0">
<input type="hidden" name="deposito" value="1">
<input type="hidden" name="cant_s" value="0">

</form>
</html>