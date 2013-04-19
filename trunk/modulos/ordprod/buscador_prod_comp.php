<?
require_once("../../config.php");
echo $html_header;
if($_POST['aceptar']=="Aceptar")
{
   
   $void=$_POST['void'];
   $void1=$_POST['void1'];
   $serie=$_POST['serie'];
   $serie1=$_POST['serie1'];
   $cuantos=$_POST['cuantos'];
   $filtro_serie=$_POST['filtro_serie'];
   $filtro_void=$_POST['filtro_void'];
   $filtro_todos=$_POST['filtro_todos'];
   $pasar=0;
   if($filtro_serie)
   {
   	$tipo=1;
   	$filtro_serie=1;
   	$filtro_todos=0;
   	$filtro_void=0;
   }
   if($filtro_void)
   {
   	$tipo=2;
   	$filtro_void=1;
   	$filtro_serie=0;
   	$filtro_todos=0;
   	if($void>$void1)
   	{
   	 $pasar=1;	
   	}
   	else {
   	$sql1="select id_producto_compuesto,codigos_barra.codigo_barra,descripcion,nro_serie
	from general.codigos_barra 
	join general.productos_compuestos using (id_producto_compuesto)
	left join general.producto_especifico using (id_prod_esp)
	where id_producto_compuesto 
	in(select id_producto_compuesto from general.codigos_barra 
	where codigo_barra >='$void' and codigo_barra<='$void1') order by id_producto_compuesto";
   	$result_sql1= sql($sql1) or fin_pagina();
   	$suma=$result_sql1->RecordCount();
   	}
   }
   if($filtro_todos)
   {
   	$tipo=3;
   	$filtro_todos=1;
   	$filtro_void=0;
   	$filtro_serie=0;
   }
   if($filtro_serie)
   {
   	$select_serie="select oid from ordenes.maquina where nro_serie ILIKE '%$serie%' order by(oid)asc   LIMIT 1 OFFSET 0";
   	$result_serie = sql($select_serie) or fin_pagina();
   	
   	$nro_serie=$result_serie->fields['oid'];
   	$select_serie="select oid from ordenes.maquina where nro_serie ILIKE '%$serie1%' order by(oid)desc LIMIT 1 OFFSET 0";
   	$result_serie = sql($select_serie) or fin_pagina();
   	$nro_serie1=$result_serie->fields['oid'];
   	if($nro_serie>$nro_serie1)
   	{
   	 $pasar=1;
   	}
   	if(($nro_serie=="")||($nro_serie1==""))
   	{
   	 $pasar=1;
   	}
   	else 
   	{
   	  $sql1="select nro_serie,oid from ordenes.maquina where oid>=$nro_serie and oid<=$nro_serie1 order by oid";
      $result_sql1= sql($sql1) or fin_pagina();
      $suma=$result_sql1->RecordCount();
   	}
    
   }
   if($pasar==0)
   {		
   $link=encode_link("producto_compuesto_excel.php",array("void"=>"$void","void1"=>"$void1","serie"=>"$nro_serie","serie1"=>"$nro_serie1","cuantos"=>$cuantos,"tipo"=>$tipo));
   if($suma>250)
   {
   ?>
   <script>
   if (confirm('Seguro que quiere continuar la cantidad de archivos a mostrar es <?=$suma?>'))
   {
   window.location='<?=$link?>';
   }
   </script>  
   <?
   }
   else
   {?>
   <script>
    window.location='<?=$link?>';
   </script>  
   <?}
   }
}	
?>
<script>
function chequear_todos()
{
//alert(document.all.chequeado.value);
if(document.all.filtro_serie.checked==true)
{
	
		var chec=eval("document.all.filtro_void");	
		chec.disabled=true;
	    var chec1=eval("document.all.filtro_todos");	
		chec1.disabled=true;
		var chec2=eval("document.all.filtro_serie");	
		chec2.value=1;
			
}
if(document.all.filtro_void.checked==true)
{
	
		var chec=eval("document.all.filtro_serie");	
		chec.disabled=true;
	    var chec1=eval("document.all.filtro_todos");	
		chec1.disabled=true;
		var chec2=eval("document.all.filtro_void");	
		chec2.value=1;
			
}
if(document.all.filtro_todos.checked==true)
{
	
		var chec=eval("document.all.filtro_void");	
		chec.disabled=true;
	    var chec1=eval("document.all.filtro_serie");	
		chec1.disabled=true;
		var chec2=eval("document.all.filtro_todos");	
		chec2.value=1;
			
}

if((document.all.filtro_todos.checked==false)&&(document.all.filtro_serie.checked==false)&&(document.all.filtro_void.checked==false))
{
	
		var chec=eval("document.all.filtro_void");	
		chec.disabled=false;
		chec.value=0;
	    var chec1=eval("document.all.filtro_serie");	
		chec1.disabled=false;
		chec1.value=0;
		var chec2=eval("document.all.filtro_todos");	
		chec2.disabled=false;
		chec2.value=0;	
}

}
function control()
{
 if((document.all.filtro_todos.checked==false)&&(document.all.filtro_serie.checked==false)&&(document.all.filtro_void.checked==false))
 {
	alert("Debe seleccionar un criterio de Búsqueda");
	return false;
 }
 else return true;
}
</script>
<form name="form" method="post" action="buscador_prod_comp.php">
<?IF($pasar==1){?>
<table align="center" width="80%">
<tr><td align="center">
<font color="Red" size="3" align="center"><?IF($pasar==1){?>
LOS NUMEROS NO HAN SIDO INGRESADOS CORRECTAMENTE VERIFIQUE EL ORDEN DE LOS MISMOS
<?
}
else 
{
?>
No se encontraron resultados entre los numeros solicitados
<?}?>
</font>
</td></tr>
</table>
<?}?>
<table align="center" border="1" bordercolor="Blue">
<tr id="mo"><td align="center" colspan="3" id="<?=$id?>">
<b>Buscador de Productos Compuestos</b>
</td></tr>
<tr><td>
<table class="bordes">
    <tr>
     <td>
      <input type="checkbox" name="filtro_serie" value="<?=$filtro_serie?>" <?=($filtro_serie==1)?"checked":""?> onclick="chequear_todos()">
      <b>Filtrar por Nro Serie:</b> 
     </td> 
    </tr>
    <tr>
     <td> 
      <b>Desde</b><input type="text" size="10" name="serie" value="<?=$serie?>">
      <b>Hasta</b><input type="text" size="10" name="serie1" value="<?=$serie1?>">
     </td>
    </tr>
   </table>   
  </td>
  <td>
   <table class="bordes">
    <tr>
     <td>
      <input type="checkbox" name="filtro_void" value="<?=$filtro_void?>" <?=($filtro_void==1)?"checked":""?> onclick="chequear_todos()">
      <b>Filtrar por Void:</b>
     </td>
    </tr>
    <tr> 
     <td> 
      <b>Desde</b><input type="text" size="10" name="void" value="<?=$void?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
      <b>Hasta</b><input type="text" size="10" name="void1" value="<?=$void1?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
     </td>
    </tr>
   </table>   
</td>
<td>
   <table class="bordes">
    <tr>
     <td>
      <input type="checkbox" name="filtro_todos" value="<?=$filtro_todos?>" <?=($filtro_todos==1)?"checked":""?> onclick="chequear_todos()">
      <b>Todos:</b>
     </td>
    </tr>
    <tr> 
     <td> 
      <b>Cuantos Campos</b><input type="text" size="10" name="cuantos" value="<?=$cuantos?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
     </td>
    </tr>
   </table>   
</td>
</tr>
<tr><td colspan="3" align="center">
 <input type="submit" name="aceptar" value="Aceptar" onclick="return control();"> 
 <input type=button name=cerrar value=Cerrar onclick="window.close()">
</td></tr>
</table>   
<?	
fin_pagina();
