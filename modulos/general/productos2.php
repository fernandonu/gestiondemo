<?
/*
Autor: GACZ
Creado: viernes 13/08/04

MODIFICADA POR
$Author: ferni $
$Revision: 1.8 $
$Date: 2005/10/20 15:46:54 $
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

  <table width="80%" align=center cellspacing="2" bgcolor=#E0E0E0 class="bordes" >
    <?
     /*if ($viene=="rma")
        {$sql = "select id_proveedor, razon_social from proveedor order by razon_social";
         $resul = sql($sql) or fin_pagina($sql);
      ?> 
       <tr>
        <td>
         <b>Proveedor:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="proveedor" style="width:310px" onKeypress="buscar_op(this);" onblur="borrar_buffer()" onclick="borrar_buffer()" >          
          <?while (!$resul->EOF)
                  {
                  ?>
                  <option <?if ($id_proveedor==$resul->fields['id_proveedor']) echo "selected"?> value="<?=$resul->fields['id_proveedor']?>"><?=$resul->fields['razon_social']?></option>
                  <?
                  $resul->MoveNext();	
                  }	
          ?>
         </select>         
        </td>
       </tr>
      <?  	
        }*/	 
    ?>
    <tr>
      <td><b>Tipo Producto:</b>
	  <select name="select_tipo" style="width:310px" onKeypress="buscar_op(this);" onblur="borrar_buffer()" onclick="borrar_buffer()">
       <option value="Todos" >Todos</option>
<?
$q="select descripcion, codigo from tipos_prod order by descripcion";
$tipos = sql($q) or fin_pagina();
echo make_options($tipos,"codigo","descripcion",$select_tipo);	
?>
        </select>
        <br>
        <br>
<?
if ($id_proveedor=="licitaciones")
{
	$q=" select * from proveedor where trim(proveedor.razon_social)='licitaciones'";
	$r=sql($q) or fin_pagina();
	$id_proveedor=$r->fields['id_proveedor'];
}


/*if ($viene=="rma")
   {$q="select distinct productos.id_producto,productos.desc_gral,(case when precios.id_proveedor=$id_proveedor then precios.precio else 0 end)
        as precio,cast('U$S' as text) as moneda,marca,modelo,tipos_prod.descripcion as tipo_prod 
        from general.productos join general.precios using (id_producto)
        join general.proveedor using (id_proveedor)
        join general.tipos_prod on tipos_prod.codigo=productos.tipo";
    $where_tmp="id_proveedor=$id_proveedor and productos.tipo='$select_tipo'";     
   }	
   
else 
{*/$q ="select distinct pp.id_producto,pp.desc_gral,(case when precios.id_proveedor=$id_proveedor then precios.precio else 0 end) as precio,cast('U\$S' as text) as moneda,marca,modelo,tipos_prod.descripcion as tipo_prod ";
$q.="from ";
$q.="(productos p ";
$q.="left join proveedor on proveedor.id_proveedor=$id_proveedor) pp ";
$q.="join tipos_prod on pp.tipo=tipos_prod.codigo ";
if ($select_tipo && $select_tipo!="Todos") $q.="AND pp.tipo='$select_tipo' ";
$q.= "and activo_productos='t' ";//agrego esto para filtrar los productos activos
$q.="left join precios on pp.id_producto=precios.id_producto AND pp.id_proveedor=precios.id_proveedor ";
//}

$filtro = array(
		"desc_gral" => "Descripción Gral.",
		"marca" => "Marca",
		"modelo" => "Modelo"
	);
	
$orden = array(
		"default" => "1",
		"1" => "desc_gral",
		"2" => "marca",
		"3" => "modelo",
		"4" => "precio"
	);

if (!$select_tipo)	
 $keyword="";
list($q,$total,$link_pagina,$up) = form_busqueda($q,$orden,$filtro,$parametros,$where_tmp,"buscar"); 
if ($select_tipo){
  $productos=sql($q) or fin_pagina();
}
else 
{
 $total=0;
 $link_pagina="&nbsp";
}


?>
	<input name="bbuscar" type="submit" value="Buscar">
      </td>
    </tr>
  </table>
<br>
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
		 <a href="<?=encode_link($_SERVER['SCRIPT_NAME'],array_merge($parametros,array("sort"=>1,"up"=>$up))) ?>"> 
	 	  Descripci&oacute;n
		 </a>
	 </td>
     <td width="15%">
		 <a href="<?=encode_link($_SERVER['SCRIPT_NAME'],array_merge($parametros,array("sort"=>2,"up"=>$up))) ?>"> 
	      Marca
	     </a>
     </td>
     <td width="25%">
		 <a href="<?=encode_link($_SERVER['SCRIPT_NAME'],array_merge($parametros,array("sort"=>3,"up"=>$up))) ?>"> 
	      Modelo
	     </a>
     </td>
     <td width="6%">
		 <a href="<?=encode_link($_SERVER['SCRIPT_NAME'],array_merge($parametros,array("sort"=>4,"up"=>$up))) ?>"> 
	      Precio
	     </a>
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
      <input type="radio" name="radio_idprod" value="<?=$productos->fields['id_producto'] ?>">
      <input type="hidden" name="htipo_prod" value="<?=$productos->fields['tipo_prod'] ?>">      
      
      </td>
      <td><span id="spandesc"><?=$productos->fields['desc_gral'] ?></span> </td>
      <td><span id="spanmarca"><?=$productos->fields['marca'] ?></span></td>
      <td><span id="spanmodelo"><?=$productos->fields['modelo'] ?></span></td>
      <td>
	      <table width="100%" border="0" cellpadding="1" >
	      <tr>
	       <td width="50%" align="left">
	       <?=$productos->fields['moneda'] ?>
	       </td>
	       <td width="50%" align="right">
	       <span id="spanprecio"> <?=formato_money($productos->fields['precio']) ?></span>
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
