<?php
/*
$Author: fernando $
$Revision: 1.38 $
$Date: 2004/06/17 22:43:09 $
*/
include("../../config.php");

$pag=$parametros["pag_ant"];
$nro_producto=$parametros["id_producto"];
$tipo_prod=$parametros["tipo_prod"] or $tipo_prod=$parametros["tipo_producto"];
$id_proveedor=$parametros['id_proveedor'] or $_POST["select_proveedor"] or 0;
$state=$_POST["tipo_prov"] or $state=$parametros['estado'] or $state="todos";

$link2=encode_link("cargar_precio.php",
                  array("id_producto" => $nro_producto,
                        "tipo_prod" => $tipo_prod,"pagina"=>$parametros["pagina"],"pag_ant"=>$pag,"estado"=>$state, "texto" => $parametros["texto"],
                         "campo" => $parametros["campo"]));
                          

//vuelta del boton volver, a productos1.php (se llego a esta pagina desde alli).                        
$link3=encode_link("productos1.php",
                  array("producto" => $nro_producto,
                        "tipo_prod" => $tipo_prod,
                         "texto" => $parametros["texto"],
                         "campo" => $parametros["campo"]));    
                        

if($pag=="")
 $pag="precio";   
//vuelta del boton volver, a alta_prod.php (se llego a esta pagina desde alli).
$link5=encode_link("altas_prod.php",
                  array("id_producto" => $nro_producto,"pagina"=>$pag,"tipo" => $tipo_prod, "texto" => $parametros["texto"],
                         "campo" => $parametros["campo"]));

$link6=encode_link($html_root."/index.php",array("menu"=>"productos1","extra"=>array("pagina"=>$parametros["pagina"],"tipo"=>$tipo_prod, "texto" => $parametros["texto"],
                         "campo" => $parametros["campo"]) ));

$query="SELECT * FROM productos where id_producto = $nro_producto";
$resultados = $db->Execute("$query") or die($db->ErrorMsg().$query);

if($_POST["Aceptar"]=="Aceptar") {
   //UPDATE general.precios SET precio=150  WHERE id_producto=6;
   $query_consulta="SELECT id_proveedor,id_producto from precios WHERE id_producto=$nro_producto and id_proveedor='".$_POST['select_proveedor']."'";
   $resultados_consulta=$db->Execute("$query_consulta") or die($db->ErrorMsg().$query_consulta);
   $cantidad=$resultados_consulta->RecordCount();

  /*
   if($cantidad>0){
	    $query2="UPDATE precios SET precio='".$_POST['text_precio']."',observaciones='".$_POST['text_descripcion'] ."' WHERE id_producto=$nro_producto and id_proveedor='".$_POST['select_proveedor']."'";
   		$db->Execute("$query2") or die($db->ErrorMsg().$query2);
   }
   else {
   	      $insert="INSERT INTO precios (id_producto,id_proveedor,precio,observaciones) VALUES ($nro_producto,'".$_POST['select_proveedor']."','".$_POST['text_precio']."','".$_POST['text_descripcion']."')";
          $db->Execute("$insert") or die($db->ErrorMsg().$insert);
   	     }
  */

  insertar_precio($nro_producto,$_POST['select_proveedor'],$_POST['text_precio']);

   if(($_POST["page"]=="productos") ||($_POST["page"]=="precios")) //si viene de productos.php
   {echo "<html><head><script language=javascript>";
	echo "window.parent.location='$link6';";
	echo "</script></head></html>";
   }
   else//si viene de altas_prod.php
      header("Location: $link5");
}

if($_POST["Volver"]=="Volver")
{if(($parametros["pagina"]=="productos")||($parametros["pagina"]=="precios"))
 {echo "<html><head><script language=javascript>";
	echo "window.parent.location='$link6';";
	echo "</script></head></html>";
 }
 else//si viene de altas_prod.php
      header("Location: $link5");
}

?>
<head>
<title>Precios</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";?>
<script src="<?=$html_root."/lib/funciones.js"?>" ></script>
<script languaje="javascript">
<!--
function verificar()
{
var aux;
aux=parseFloat(document.all.text_precio.value);

if ((document.all.select_proveedor.value=="0") || (document.all.select_proveedor.value==""))
 {alert("Debe ingresar un proveedor válido");
  return false;
 }
 if (document.all.text_precio.value=="")
 {alert("Debe ingresar un precio válido");
  return false;
 }
 if (document.all.text_precio.value.indexOf(',')!=-1)
 {alert("Especifique la parte fraccionada con .");
  return false;
 }

 if (!isNaN(aux)) {
    //alert("Usted ha actualizado el precio con exito");
    return true;
 }
 else { alert("El valor del campo precio debe ser un numero");
         return false;
 }  
  
 
} //final de la funcion verificar

//funcion para cargar el precio del producto, segun el proveedor seleccionado
function set_value (prov,prod){

if (prov!=0){
info=eval ("precio_"+prod); 
if (!info[prov])
    document.all.text_precio.value="";
 else document.all.text_precio.value=info[prov];
}
}

-->

</script>
</head>
<body bgcolor="#E0E0E0">

<br>
<form name="form1" action=" <? echo $link2; ?> " method="POST">
<div align="center">
<hr>
<table width="100%" border="0">
      <tr bgcolor="#c0c6c9">
      <td width="50%" align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Descripcion
      del producto:</b></font>
      </td>
      <td width="50%" align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Proveedor:
      </b></font>
      </td>
    </tr>
    <tr>
  </table>
</div>
<table width="100%" border="0">
<tr>
<td width="50%">
<font color="#006699"><b> Tipo: </b></font>
<font color="#333333" size="2" face="Georgia, Times New Roman, Times, serif"><strong> <? echo $resultados->fields["tipo"]; ?>
</strong></font><br>
<font color="#006699"><b> Marca:</b></font>
<font color="#333333" size="2" face="Georgia, Times New Roman, Times, serif"><strong><? echo $resultados->fields["marca"]; ?>
</strong></font>  <br>
<font color="#006699"><b> Modelo:</b></font>
<font color="#333333" size="2" face="Georgia, Times New Roman, Times, serif"><strong><? echo $resultados->fields["modelo"]; ?>
</strong></font>  <br>
<font color="#006699"><b> Descripcion:</b></font>
<font color="#333333" size="2" face="Georgia, Times New Roman, Times, serif"><strong><? echo $resultados->fields["desc_gral"]; ?>
</strong></font>  <br>
</td>
<td width="50%">
<font color="#333333" size="2" face="Georgia, Times New Roman, Times, serif"><strong>Seleccione Proveedor:</strong></font>
  	<br>
	
	  <select name="select_proveedor" <? if ($id_proveedor) echo ' disabled'?> onChange="set_value(this.value,<?=$nro_producto;?>);" onKeypress= "set_value(this.value,<?=$nro_producto;?>);buscar_op(this);set_value(this.value,<?=$nro_producto;?>)" onblur="borrar_buffer()" onclick= "borrar_buffer()">
      <option value=0>Seleccione un proveedor</option>
      <?
		
     
	if($state=='todos')
     $query="SELECT * FROM proveedor where activo='true' order by razon_social";
    else
     $query="SELECT distinct proveedor.*,prov_t.tipo FROM proveedor join prov_t on proveedor.id_proveedor=prov_t.id_proveedor where activo='true' and prov_t.tipo='$state' order by razon_social";
   
     $resultados = $db->Execute("$query") or die($db->ErrorMsg().$query);
		
		while (!$resultados->EOF) 
		 { 
			 
		 if(($resultados->fields["id_proveedor"]==$id_proveedor) || ($_POST['select_proveedor']==$resultados->fields["id_proveedor"]))
		    echo "<option selected value=".$resultados->fields["id_proveedor"].">".$resultados->fields["razon_social"]."</option>";
		 else
		    echo "<option value=".$resultados->fields["id_proveedor"].">".$resultados->fields["razon_social"]."</option>";
       $resultados->MoveNext(); 
		}
		?>
		
    </select>	
    <?php 
	
	if (!$id_proveedor)
	{
	?>
	<br>
	<?/*FILTRO DE TIPOS DE PROVEEDORES....NO BORRAR!!!
  //traemos los tipos de proveedores para generar el select de tipos de proveedores
    $query="select * from tipos_prov";
    $tipos_prov=$db->Execute($query) or die ($db->ErrorMsg().$query);      
	$todos=($state=='todos')?'selected':'';*/
    ?>
	<!--<select name="tipo_prov" value="est" <? //if($id) echo 'disabled';?> onchange="document.form1.submit()">
            
	        <option value="todos" <?//echo $todos?>>Todos</option>-->
	<?/*
	while(!$tipos_prov->EOF)
	{ if(($state!='todos')&&($state==$tipos_prov->fields['tipo'])) 
	    $selected='selected';
	   else 
	    $selected='';*/
	?>          
	        <!--<option value='<?//echo $tipos_prov->fields['tipo']; echo "' ".$selected;?>><? echo $tipos_prov->fields['descripcion']?></option>-->
	 <?
    /* $tipos_prov->MoveNext();
	} */  
    ?>   
  <!--</select>-->
         	 
         
   <?PHP } //de if (!$id_proveedor  ?>
   
  </td>
</tr>
</table>

<input type="hidden" name="tipos" value="<?php echo $parametros["tipo_prod"]?>">
<div align="center">
<br>
<table width="85%" border="0">
<tr  bgcolor=<? echo $bgcolor1; ?>>
<td><font color=<?echo $bgcolor3; ?>><b>Descripcion General de precio</b></font></TD>
</tr>
 <tr  bgcolor='#CCCCCC'>
<td><input type='text' name='text_descripcion' size='120' value='<?php if($_POST["filtro"]=="activado")echo $_POST["text_descripcion"]; else echo $parametros['observaciones'];?>'></td>
</tr>
</table>
<? if (!$parametros['precio']){
$sql="select precio,id_proveedor from precios where id_producto=$nro_producto";
$res = $db->Execute($sql) or die($db->ErrorMsg().$sql); ?>
<script>var precio_<?=$nro_producto; ?>=new Array();</script>
<?
 $i=1;
 while (!$res->EOF) 
 { ?>
		<script language="javascript">
           precio_<?=$nro_producto?>[<?=$res->fields['id_proveedor']?>]="<?=$res->fields['precio']?>"; 
        </script>
<?$res->MoveNext();}
}?>

<br>
<font color="#006699"><b>Ingrese el nuevo Precio </b> U$S:
<INPUT type="text" name="text_precio" value="<?if($parametros['precio']) echo ($parametros['precio']);?>" size="10"> <b>Final</b></font>
<br>
<br>
<hr>
<input type="hidden" name="page" value="<?php if($_POST["page"]=="") echo $parametros["pagina"]; else echo$_POST["page"];?>">
<input type="submit" name="Aceptar" value="Aceptar" onclick="return verificar()" >
<INPUT type="submit"  name="Volver" value="Volver">
</div>
<? if ($id_proveedor) echo "<input type='hidden' name='select_proveedor' value=$id_proveedor >" ?>
<input type='hidden' name='filtro' value="" >
</form>
</body>
</html>
