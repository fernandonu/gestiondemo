<?
/*
Author: GACZ

MODIFICADA POR
$Author: ferni $
$Revision: 1.20 $
$Date: 2005/08/18 15:28:41 $
*/

require_once("../../config.php");

//guardamo la pagina a donde se debe volver 
$pagina_volver=$parametros['pagina_volver'];

extract($_POST,EXTR_SKIP);
if ($parametros)
 extract($parametros,EXTR_OVERWRITE);

 if ($download)
 {
  	excel_header("Resultados Lic_$keyword.xls");	
  	require("lic_ver_res2_xls.php");
  	die;
 }
 
 
function transformar($valor1,$valor2)
{$diferencia=abs(strlen($valor1)-strlen($valor2));
 $division=($diferencia/2);
 $i=0;
 $cadena="";
 while ($i<$diferencia-1)
 {$cadena.="&nbsp";
  $i++;
 }
 $i=0;
 $cadena.=$valor2;
 while ($i<$diferencia)
 {$cadena.="&nbsp";
  $i++;
 }
 return $cadena;
}

//$link=encode_link("lic_ver_res.php",array('keyword'=>$keyword,'pagina'=>"cargar_resultados","pag_ant"=>$pag_ant)); 

?>
<html>
<head>
<script language='javascript' src='../../lib/popcalendar.js'></script>
<script languaje="javascript">

function swap1(columna1,columna2)
{var valor1,valor2,color1,color2,aux,dolar1,dolar2,fila,filas;
 valor1=eval("document.all.nombre_"+columna1);
 valor2=eval("document.all.nombre_"+columna2);
 aux=valor2.value;
 valor2.value=valor1.value;
 valor1.value=aux;
 filas=eval("document.all.cant_filas");
 fila=1;
 while(fila <= filas.value)
 {valor1=eval("document.all.v_"+fila+columna1);
  valor2=eval("document.all.v_"+fila+columna2);
  aux=valor2.value;
  valor2.value=valor1.value;
  valor1.value=aux;
  valor1=eval("document.all.d_"+fila+columna1);
  valor2=eval("document.all.d_"+fila+columna2);
  aux=valor2.size;
  valor2.size=valor1.size;
  valor1.size=aux;
  aux=valor2.value;
  valor2.value=valor1.value;
  valor1.value=aux;
  aux=valor2.style.color;
  valor2.style.color=valor1.style.color;
  valor1.style.color=aux;
  dolar1=eval("document.all.dolar_"+fila+columna1);
  dolar2=eval("document.all.dolar_"+fila+columna2);
  aux=dolar2.value;
  dolar2.value=dolar1.value;
  dolar1.value=aux;
  valor1=eval("document.all.h_"+fila+columna1);
  valor2=eval("document.all.h_"+fila+columna2);
  aux=valor2.value;
  valor2.value=valor1.value;
  valor1.value=aux;
  valor1=eval("document.all.color_"+fila+columna1);
  valor2=eval("document.all.color_"+fila+columna2);
  aux=valor2.value;
  valor2.value=valor1.value;
  valor1.value=aux;
  valor1=eval("document.all.v1_"+fila+columna1);
  valor2=eval("document.all.v1_"+fila+columna2);
  aux=valor2.value;
  valor2.value=valor1.value;
  valor1.value=aux;
  valor1=eval("document.all.td_"+fila+columna1);
  valor2=eval("document.all.td_"+fila+columna2);
  aux=valor2.title;
  valor2.title=valor1.title;
  valor1.title=aux;
  fila++;
 }
}


function ordenar_fila(fila)
{var columnas=eval("document.all.cant_col");
 var valor_comp;
 var pivot=0;
 var i=0;
 var f;
 var valor_min=eval("document.all.v_"+fila+"0");
 while(pivot < columnas.value)
 {i=pivot;
  valor_min=eval("document.all.v_"+fila.toString()+i.toString());
  while (i < columnas.value)
  {valor_comp=eval("document.all.v_"+fila.toString()+i.toString());
   if ((parseInt(valor_min.value)>parseInt(valor_comp.value)) && (parseInt(valor_comp.value)!=""))
    swap1(pivot,i);
   i++;
  }//fin while
  pivot++;
 }//fin while2
}

</script>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<title>Buscar Resultados de Licitaciones</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php
include("../ayuda/ayudas.php");

?>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}

//funcion para transformar de dolares a pesos

if (!Number.toFixed) 
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}


function transformar()
{var objeto,objeto2,aux1,aux2,dol;
 var columnas=eval("document.all.cant_col");
 var filas=eval("document.all.cant_filas");
 var a=0;
 var fila,columna;
 fila=1;
 while(fila<=filas.value)
 {columna=0;
  while(columna < columnas.value)
  {objeto=eval("document.all.d_"+fila.toString()+columna.toString());
   objeto2=eval("document.all.h_"+fila.toString()+columna.toString());
   objeto3=eval("document.all.v1_"+fila.toString()+columna.toString());
   objeto4=eval("document.all.v_"+fila.toString()+columna.toString());
   dol=eval("document.all.dolar_"+fila.toString()+columna.toString());
   if (dol.value==1)
   {aux1=parseFloat(document.all.valor_dolar.value);
    aux2=parseFloat(objeto2.value);
    aux2=aux1*aux2;
    aux2=aux2.toFixed(2);
    objeto.value='$ '+aux2;
    if (objeto.value.indexOf(".")==-1)
     objeto.value=objeto.value+".00";
    objeto.style.color='blue';
    objeto4.value=parseInt(document.all.valor_dolar.value)*parseInt(objeto3.value);
   }
  columna++;
  }//fin while
 fila++
 }//fin while2
}

function resetear()
{var objeto,objeto2,aux1,aux2,dol;
 var columnas=eval("document.all.cant_col");
 var filas=eval("document.all.cant_filas");
 var a=0;
 var fila,columna;
 fila=1;
 while(fila<=filas.value)
 {columna=0;
  while(columna < columnas.value)
  {objeto=eval("document.all.d_"+fila.toString()+columna.toString());
   objeto2=eval("document.all.h_"+fila.toString()+columna.toString());
   objeto3=eval("document.all.color_"+fila.toString()+columna.toString());
   dol=eval("document.all.dolar_"+fila.toString()+columna.toString());
  if (dol.value==1)
  {objeto.value='U$S '+objeto2.value;
   if (objeto.value.indexOf(".")==-1)
	objeto.value=objeto.value+".00";
   objeto.style.color=objeto3.value;
  }
   columna++;
  }//fin while
  fila++;
 }//fin while2
}

function habilitar()
{if (document.all.check_precios.checked)
 {document.all.boton_trans.disabled=0;
 }
 else
 {document.all.boton_trans.disabled=1;
  resetear();
 }
}
</script>
</head>
<body bgcolor=#E0E0E0 text="#000000" topmargin="0" >
<br>
<?php
$sql="select valor_dolar_lic from licitacion where id_licitacion=$keyword";
$resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
$valor_dolar=$resultado->fields['valor_dolar_lic'];
?>
<table align="center">
<tr>
<td>
<input type="checkbox" name="check_precios" onclick="habilitar();">
</td>
<td width="40%">
<input type="button" name="boton_trans" value="transformar $" style="width:100" onclick="transformar();" disabled>
</td>
<td align="right">
Valor Dolar
</td>
<td>
<input type="text" name="valor_dolar" value="<?php echo $resultado->fields['valor_dolar_lic']; ?>" size="4">
</td>
</tr>
</table>
<table width="100%">
<tr>
<td align="left">
<table cellpadding="4">
<tr>
<td>
<a href='<?php echo encode_link("lic_ver_res.php",array("keyword"=>$keyword,"pag_ant"=>"lic","pagina_volver"=>$pagina_volver)); ?>' onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">Vista 1</a>
</td>

<td>
<a href='<?php echo encode_link("lic_ver_res3.php",array("keyword"=>$keyword,"pag_ant"=>$pagina_volver,"pagina_volver"=>$pagina_volver)); ?>' onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">Ver Monto Total</a>
</td>

</tr>
</table>
</td>
<td align="right">
<table cellpadding="4">
<tr>
<td>

<p onclick="window.location='<?=$pagina_volver?>'" style="cursor:hand;" onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')"><font color="Blue">Volver</p>
</td>
<td>
<img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_ver_res2.htm" ?>', 'RESULTADOS DE LA LICITACION')" >
</td>
</tr>
</table>
</td>
</tr>
</table>
<?php 
//$link=encode_link("lic_ver_res.php",array('keyword'=>$keyword,'pagina'=>"cargar_resultados","pag_ant"=>$pag_ant)); 
?>
<form name="formulario" method="post" action="lic_ver_res2.php">
<input type="hidden" name="id_renglon" value="">
 <table cellspacing=0 border=1 bordercolor=#000000>
  <?php $parametros=array('ID'=>$keyword,'cmd1'=>'detalle',"pagina"=>$parametros["pag_ant"]); ?>
  <tr title="Haga click para ver el detalle de la licitacion">    
  <td id=mo colspan="20" align="left" bordercolor='#000000'>
<?php
$sql="select entidad.nombre as nombre_ent,licitacion.id_licitacion,distrito.nombre  from (licitacion join entidad on entidad.id_entidad=licitacion.id_entidad) join distrito on distrito.id_distrito=entidad.id_distrito  where id_licitacion=$keyword";
$resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
?>
		<table width="100%">
		<tr>
		  <td width="10">
<?	if (permisos_check("inicio","excel_res_lic"))  {?>
		  <a target="_blank" href="<?=encode_link("lic_ver_res2.php",array("download"=>true,"keyword"=>$resultado->fields['id_licitacion'])) ?>">
			  	<img src="../../imagenes/excel.gif" border="0" align="middle" title="Bajar Resultados en un Excel"></img>
		  </a>
<?  }
	else {
		echo "&nbsp;";	
	}	
?>		  
		  </td>
	 <td align="center">
      <a href="<?=encode_link($pagina_volver,$parametros) ?>"> 
      &nbsp;Resultados Licitacion N&ordm; &nbsp;<?=$resultado->fields['id_licitacion'] ?>
      &nbsp; - &nbsp; Entidad: &nbsp;<?=$resultado->fields['nombre_ent'] ?>
      &nbsp;&nbsp; - &nbsp; Disrito: &nbsp;<?=$resultado->fields['nombre'] ?>
      </a>
      </td>
      </tr>
      </table>
      </td>
    </tr>
   <tr id='ma'>
   <td width="1%">Ordenar</td>
   <td width="2%">Renglon</td>
   <td width="2%">Cantidad</td>
<?php 
 $i=0;
 $sql="select distinct competidores.nombre,competidores.id_competidor from renglon join oferta on renglon.id_licitacion=$keyword and renglon.id_renglon=oferta.id_renglon join competidores on oferta.id_competidor=competidores.id_competidor";
 $resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
 while (!$resultado->EOF)
 {
?>
 <td align="center" width="10%"> 
 <input type="text" name="nombre_<?php echo $i; ?>" size="<?php echo strlen($resultado->fields['nombre']); ?>" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" value="<?php echo $resultado->fields['nombre']; ?>" readonly>
 </td>
<?php
 $i++;
 $resultado->MoveNext();
 }
?>
</tr>
<?php
$i=0;
$sql="select * from renglon where id_licitacion=$keyword order by codigo_renglon asc";
$resultado_ren=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
$fila=1;
$columna=1;
 while (!$resultado_ren->EOF)
 {
?>
 <tr class="td" onmouseover="sobre(this,'#ffffff')" onmouseout="bajo(this,'transparent')">
 <td width="1%">
 <input type="button" name="boton" value="<" onclick="ordenar_fila(<?php echo $fila; ?>);">
 </td>
 <td align="center" title='<?php echo $resultado_ren->fields['titulo']; ?>'>
 <b> 
 <?php echo $resultado_ren->fields['codigo_renglon']; ?>
 </td>
 <td align="center">
 <b>
 <?php echo $resultado_ren->fields['cantidad']; ?>
 </td>
<?php
$resultado->Move(0);
$columna=0;
while (!$resultado->EOF)
{$sql="select oferta.*,moneda.simbolo,moneda.id_moneda from (oferta join moneda on moneda.id_moneda=oferta.id_moneda) where id_renglon=".$resultado_ren->fields['id_renglon']." and id_competidor=".$resultado->fields['id_competidor'];
 $resultado_aux=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
 $parametros['id_moneda']=$resultado_aux->fields['id_moneda'];
 $parametros['id_lic']=$keyword;
 $parametros['id_renglon']=$resultado_ren->fields['id_renglon'];
 $parametros['id_competidor']=$resultado->fields['id_competidor'];
 $parametros['pagina_viene']="lic_ver_res";
 $parametros['pagina_volver']=$pagina_volver;
 $link=encode_link("lic_cargar_res.php",$parametros);
if ($resultado_aux->fields['simbolo'])
{
?>
<a href="<? echo $link; ?>" >
<?php
}
?>
 <td align="left" title='<?php echo $resultado_aux->fields['observaciones']; ?>' id="td_<?php echo $fila.$columna; ?>">
 <b>
 <?php
if ($valor_dolar=='0')
 $valor_dolar=1;
 if ($resultado_aux->fields['simbolo']=="U\$S")
 { ?>
 <input type="text" name="d<?php echo $i; ?>" id="d_<?php echo $fila.$columna; ?>" size="<?php echo strlen($resultado_aux->fields['simbolo'].'&nbsp;'.(($resultado_aux->fields['monto_unitario'])?number_format($resultado_aux->fields['monto_unitario'],2,".",""):'')); ?>" style="border-style:none;background-color:'transparent';color:'<?php if ($resultado_aux->fields['ganada']=='t') echo "red"; else echo "black"; ?>'; font-weight: bold;" value="<?php echo $resultado_aux->fields['simbolo'].'&nbsp;'.(($resultado_aux->fields['monto_unitario'])?number_format($resultado_aux->fields['monto_unitario'],2,".",""):''); ?>" readonly>
 <input type="hidden" name="h<?php echo $i ?>" id="h_<?php echo $fila.$columna; ?>" value="<?php echo $resultado_aux->fields['monto_unitario']; ?>">
 <input type="hidden" name="v_<?php echo $fila.$columna; ?>" value="<?php echo $resultado_aux->fields['monto_unitario']*$valor_dolar; ?>">
 <input type="hidden" name="v1_<?php echo $fila.$columna; ?>" value="<?php echo $resultado_aux->fields['monto_unitario']; ?>">
 <input type="hidden" name="dolar_<?php echo $fila.$columna; ?>" value=1>
 
 
<?php
$i++;
 }
else
{
?>
<input type="text" id="d_<?php echo $fila.$columna; ?>" size="<?php echo strlen($resultado_aux->fields['simbolo'].'&nbsp;'.(($resultado_aux->fields['monto_unitario'])?number_format($resultado_aux->fields['monto_unitario'],2,".",""):'')); ?>" style="border-style:none;background-color:'transparent';color:'<?php if ($resultado_aux->fields['ganada']=='t') echo "red"; else echo "black"; ?>'; font-weight: bold;" value="<?php echo $resultado_aux->fields['simbolo'].'&nbsp;'.(($resultado_aux->fields['monto_unitario'])?number_format($resultado_aux->fields['monto_unitario'],2,".",""):''); ?>" readonly>
<input type="hidden" name="h<?php echo $i ?>" id="h_<?php echo $fila.$columna; ?>" value="<?php echo $resultado_aux->fields['monto_unitario']; ?>">
<input type="hidden" name="v_<?php echo $fila.$columna; ?>" value="<?php echo $resultado_aux->fields['monto_unitario']; ?>">
<input type="hidden" name="v1_<?php echo $fila.$columna; ?>" value="<?php echo $resultado_aux->fields['monto_unitario']; ?>">
<input type="hidden" name="dolar_<?php echo $fila.$columna; ?>" value=0>

<?php
}
?>
<input type="hidden" name="color<?php echo $i ?>" id="color_<?php echo $fila.$columna; ?>" value="<?php if ($resultado_aux->fields['ganada']=='t') echo "red"; else echo "black"; ?>">
 <?php /*if ($resultado_aux->fields['simbolo']=="U\$S") {?><div id="d<?php echo $i; ?>"><?php } ?><?php echo $resultado_aux->fields['simbolo'].'&nbsp;'.(($resultado_aux->fields['monto_unitario'])?number_format($resultado_aux->fields['monto_unitario'],2,".",""):''); ?><?php if ($resultado_aux->fields['simbolo']=="U\$S") { ?></div><?php } */?>
 <?php /*if ($resultado_aux->fields['simbolo']=="U\$S") {?><input type="hidden" name="h<?php echo $i ?>" value="<?php echo $resultado_aux->fields['monto_unitario']; ?>"><?php $i++; } */?>
 </td>
</a>
<?php
$resultado->MoveNext();
$columna++;
}
?>
 </tr>
<?php
 $resultado_ren->MoveNext();
 $fila++;
 }
?>
<input type="hidden" name="cant_filas" value="<?php echo $fila-1; ?>">
<input type="hidden" name="cant_col" value="<?php echo $columna; ?>">
<input type="hidden" name="cant" value="<?php echo --$i ?>">
</table>
</form>
</body>
</html>