<?
/*
Autor: quique
Creado: viernes 18/10/05
*/

require_once("../../config.php");



extract($_POST,EXTR_OVERWRITE);

if (!$select_tipo)
	$select_tipo=$parametros['select_tipo'];



variables_form_busqueda("prod");
$onclick['cargar']=$parametros['onclickcargar'] or $onclick['cargar']=$_POST['onclickcargar'];
$onclick['cancelar']=$parametros['onclickcancelar'] or $onclick['cancelar']=$_POST['onclickcancelar'] or $onclick['cancelar']="window.close()";
$viene=$parametros['viene'];
$id_proveedor=$parametros['id_proveedor'] or $id_proveedor=$_GET['id_proveedor']   or $id_proveedor=$proveedor or $id_proveedor="licitaciones";
if ($viene!="rma") $parametros['id_proveedor']=$id_proveedor;
$parametros['select_tipo']=$select_tipo;
$parametros['viene']=$viene;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Productos</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../lib/estilos.css" rel="stylesheet" type="text/css">
<?=$html_header ?>
</head>
<body>
<script>
function select(pos)
{
 var radio_idprod;
 var span_precio,precio;
 var span_desc,desc;
 var span_marca,marca;
 var span_modelo,modelo;
 var htipo_prod;
 if (typeof(document.all.radio_idprod.length)!='undefined')
 {
 	radio_idprod=document.all.radio_idprod[pos];
 	span_precio=document.all.spanprecio[pos];
 	span_desc=document.all.spandesc[pos];
 	span_marca=document.all.spanmarca[pos];
 	span_modelo=document.all.spanmodelo[pos];
 	htipo_prod=document.all.htipo_prod[pos];
 }
 else
 {
    radio_idprod=document.all.radio_idprod;
    span_precio=document.all.spanprecio;
 	span_desc=document.all.spandesc;
 	span_marca=document.all.spanmarca;
 	span_modelo=document.all.spanmodelo;
 	htipo_prod=document.all.htipo_prod;
 }
 	 
    radio_idprod.checked=1
  	precio=span_precio.innerHTML;
	//quito los puntos, supongo un maximo de 3 puntos
 	precio=precio.replace('.','');
 	precio=precio.replace('.','');
 	precio=precio.replace('.','');
	//cambio la coma por punto
 	precio=precio.replace(',','.');

 	//asigno los valores
 	form1.id_producto.value=radio_idprod.value;
 	form1.precio.value=precio;
 	form1.descripcion.value=span_desc.innerHTML;
 	form1.marca.value=span_marca.innerHTML;
 	form1.modelo.value=span_modelo.innerHTML;
 	form1.tipo_prod.value=htipo_prod.value;

    form1.bcargar.disabled=0;
}
</script>
<form name="form1" method="post" action="<?= encode_link($_SERVER['SCRIPT_NAME'],$parametros) ?>">

  
<?
$q="select * from producto_especifico order by descripcion";
$productos = sql($q) or fin_pagina();
?>
  <table width=98%  border=0 cellspacing=2 align=center class=bordes>
    <tr align="center" id=mo>
      <td colspan=5>
        <table width="100%">
        <tr>
	      <td align="left">Resultados: <?=$total ?> registros </td>
	      <td align="right"><?=$link_pagina?> </td>
	     </tr>
	    </table>
      </td>
    </tr>
    <tr align="center" id=mo>
      <td width="4%" height="20" >&nbsp; </td>
 	  <td width="50%">
		 <b>
	 	  Descripci&oacute;n
		 </b>
	 </td>
     <td width="15%">
		 <b>
	      Marca
	     </b>
     </td>
     <td width="25%">
		 <b>
	      Modelo
	     </b>
     </td>
     <td width="6%">
		 <b>
	      Precio
	     </b>
     </td>
     </tr>
<?
 $i=0;
 if ($productos)
 while (!$productos->EOF) 
  {
?>    
    <tr <?= "$bgcolor_out ". atrib_tr() ?> onclick="select(<?=$i ?>)" ondblclick="<?=$onclick['cargar']?>">
      <td>
      <input type="radio" name="radio_idprod" value="<?=$productos->fields['id_prod_esp'] ?>">
      <input type="hidden" name="htipo_prod" value="<?=$productos->fields['tipo_prod'] ?>">      
      
      </td>
      <td><span id="spandesc"><?=$productos->fields['descripcion'] ?></span> </td>
      <td><span id="spanmarca"><?=$productos->fields['marca'] ?></span></td>
      <td><span id="spanmodelo"><?=$productos->fields['modelo'] ?></span></td>
      <td>
	      <table width="100%" border="0" cellpadding="1" >
	      <tr>
	       <td width="50%" align="left">
	       <?//=$productos->fields['precio_stock'] ?>
	       </td>
	       <td width="50%" align="right">
	       <span id="spanprecio"> <?=formato_money($productos->fields['precio_stock']) ?></span>
	       </td>
	      </tr>
	  </table>      
	  </td>
    </tr>
<?
	$i++;
	$productos->movenext();
  }	
?>
  </table>

<br>
<center>
<input type="hidden" name="id_producto" value="">
<input type="hidden" name="descripcion" value="">
<input type="hidden" name="tipo_prod" value="">
<input type="hidden" name="marca" value="">
<input type="hidden" name="modelo" value="">
<input type="hidden" name="precio" value=0>
<?/*<input type="hidden" name="id_proveedor" value="<?=$id_proveedor?>">

 $sql = "select razon_social from proveedor where id_proveedor=$id_proveedor";
 $re = sql($sql) or fin_pagina();

<input type="hidden" name="prov" value="<?=$re->fields['razon_social']?>">*/?>
<input name="bcargar" type="button" value="Cargar" disabled onclick="<?=$onclick['cargar']?>" >&nbsp;&nbsp;&nbsp;
<input name="bcancelar" type="button" value="Cancelar" onclick="<?=$onclick['cancelar']?>">
</center>
</form>
</body>
</html>
