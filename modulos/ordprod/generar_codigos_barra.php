<?
/*
Autor: MAC
Fecha: 18/05/06

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2006/05/22 15:37:58 $

*/
require_once("../../config.php");

$cant_generar=$_POST["cant_generar"];
$titulo_producto=$_POST["titulo_producto"];

if($_POST["generar"]=="Generar")
{
	include_once("generador_codigos_barra.php");
	exit;

}


echo $html_header;


	?>
	<script>
	function alProximoInput(elmnt,content,next,index)
	{
	  var boton;
	  var posfijo=new String();

	  if (content.length==elmnt.maxLength)
		{

		  if (typeof(next)!="undefined")
			{
			  next.focus();
			}
		  else
		   document.all.guardar.focus();

		  //obtenemos el posfijo del nombre del campo de codigo de barra, para habilitar el boton de autocompletar correspondiente
		  posfijo=elmnt.name;
		  posfijo=posfijo.substr(10,posfijo.length-10);
	      if(typeof(boton=eval("document.all.autocompletar_consecutivos_"+posfijo))!="undefined")
	      {
	         boton.style.visibility='visible';
	      }

		}//de if (content.length==elmnt.maxLength)

	}//de function alProximoInput(elmnt,content,next)


	/**************************************************************************************************
	 Para la parte de recepcion y entregas:
	 Funcion que autocompleta los campos de codigos de barra, para evitar que el usuario tenga que
	 cargar muchos codigos de barra. Se asume para que esto funcione bien, que los codigos de
	 barra a ingresar seran todos consecutivos y solo numerales.
	 @primer_codigo     El primer codigo de barras del rango que se ingresara
	 @indice_text		El subindice que compone el nombre del campo. Este se usa para indicar desde
	 					cual campo se comenzaran a ingresar los codigos de barra consecutivos.
	 					Por ejemplo, si se pasa 3, se comenzara a agregar los codigos de barra
	 					desde el campo codigos_3 (si en el parametro nombre_text venia: codigos_)
	***************************************************************************************************/
	function autocompletar_codigos_barra(primer_codigo,indice_text)
	{

		var aux_campo,arr_codigo,i,aux_string;
		var k,aux_cant;
		var cantidad_campos=eval("document.all.cant_generar.value");
		var codigo_insertar=parseFloat(primer_codigo) + 1;

		for(indice_text;indice_text<cantidad_campos;indice_text++)
		{
			aux_campo=eval('document.all.cod_barra_'+indice_text);
			aux_string=String(codigo_insertar);

			//completamos con ceros a la izquierda el numero, para llegar a la longitud del codigo de barra pasado por parametro
			//(en general la longitud es 9)
			aux_cant=primer_codigo.length-aux_string.length;
			for(k=0;k<aux_cant;k++)
			{
			 aux_string="0"+aux_string;
			}
			aux_campo.value=aux_string;
			//seteamos el proximo codigo a insertar
			codigo_insertar=parseFloat(codigo_insertar) + 1;

		}//de for(indice_text;indice_text<cantidad_campos;indice_text++)


	}//de function autocompletar_codigos_barra(primer_numero,ultimo_numero,nombre_text,indice,cantidad_campos)



	function habilitar_deshabilitar_ingreso_serial(valor_checked,id_log_recibido)
	{
	 var i=eval("document.all.primer_nuevo_cb_"+id_log_recibido+".value");
	 var cb_text;

	 while(typeof(eval("document.all.cod_barra_"+id_log_recibido+"_"+i))!="undefined")
	 {
	  cb_text=eval("document.all.cod_barra_"+id_log_recibido+"_"+i);
	  if(valor_checked==1)
	   cb_text.maxLength=100;
	  else
	   cb_text.maxLength=9;

	  i++;
	 }

	}//de function habilitar_ingreso_serial()

	function cargarSeries()
	{
		var arregloaux = new Array();
		var arreglo = new Array();
		var tamArreglo;
		arregloaux=window.clipboardData.getData("Text");
		arreglo=arregloaux.split("\n");
		tamArreglo=arreglo.length;
		var i=eval ("document.all.rango.value");
		var j=0;
		var error=0;
		var errorCont=0;
		while (j<tamArreglo-1){
			var res = eval("document.all.cod_barra_"+i);

			if (typeof (res)=="undefined"){
				error=1;
				errorCont++;
			}
			else{
				res.value=arreglo[j];
			}
			i++;
			j++;
		}
		if (error==1){
			alert ("La Cantidad de Datos del Portapapeles es MAYOR a los Cuadros de Textos Disponibles en la Pagina.\nLo Sobrepasa en "+errorCont+" Fila/s.");
		}
	}//de function cargarSeries()

	</script>
	<script src="funciones.js"></script>
	<form name="form1" method="POST" action="generar_codigos_barra.php">


	 <table width="100%" align="center" border="1">
	  <tr>
	   <td id="ma">
	    Generación de Códigos de Barra
	   </td>
	  </tr>
	  <tr>
	   <td>
	    <table width="100%" class="bordes">
	      <tr>
		   <td id="mo_sf">
		    Nombre del Producto <input type="text" name="titulo_producto" value="<?=$titulo_producto?>" size="100">
		   </td>
		  </tr>
	     <tr id=mo>
	      <td>
	       Ingrese la cantidad de códigos de barra a generar:
			<input type="text" name="cant_generar" value="<?=$cant_generar?>" onclick="control_numero(this,'Cantidad a generar');" size="10">
			<input type="submit" name="ok" value="OK">
	      </td>
	     </tr>
	  <?
	  	if($cant_generar>0)
	  	{?>
	  	  <tr>
	  	   <td>
	  		<table width="100%" align="center" class="bordes">
			  <tr align="center">
			  	  <td align="center" colspan="2">
			  	  	<strong>
			  	  	<font color="Red">
			  	  	Presionar el Boton despues de Copiar los Datos de Excel
			  	  	</font>
			  	  	</strong>
			  	  </td>
			  </tr>
			  <tr align="center">
			  	<td align="center" colspan="2">
			  		<b>Ingrese Numero de Inicio:&nbsp;</b>
			  		<input type="text" value="0" name="rango" title="Ingrese el Numero Desde" size="4">
			  	</td>
			  </tr>

			  <tr align="center">
			  	  <td align="center" colspan="2">
			  	  	<input type="button" name="cargar_series" value="Cargar Series del Portapapeles" onclick="cargarSeries();">
			  	    <br>
			  	  </td>
			  </tr>
			 </table>
			</td>
		   </tr>
	  	  <?
	  	  }
	  	  $acum_cb_ingresados=0;

		  $io=0;
		  ?>
		  <input type="hidden" name="primer_nuevo_cb_<?=$id_log_recibido?>" value="<?=$io?>">
		  <?
		  for($io;$io<$cant_generar;$io++)
		  {?>
		   <tr>
		    <td>
		     <?
		     if($_POST["cod_barra_$io"])
		     {$valor_cb=$_POST["cod_barra_$io"];
		     }
		   	 else
		   	 {$valor_cb="";
		   	 }

			?>
		      <input type="button" name="autocompletar_consecutivos_<?=$io?>" value="V" title="Autocompletar codigos de barra consecutivos" onclick="autocompletar_codigos_barra(document.all.cod_barra_<?=$io?>.value,<?=$io+1?>)">
		     <input type="text" tabindex="<?=$io+1?>" name="cod_barra_<?=$io?>" value="<?=$valor_cb?>" <?=$estilo_error?> size="30" onkeyup="alProximoInput(this,this.value,<?=$third_par?>,<?=$io?>);">
		     <input type="button" name="limpiar_<?=$io?>" value="Limpiar" onclick="document.all.cod_barra_<?=$io?>.value=''">
		    </td>
		   </tr>
		  <?
		  }//de for($io;$io<$cant_generar;$io++)
		  ?>
		    </table>
		   </td>
		  </tr>
	 </table>
	 <input type="hidden" name="cb_a_borrar" value="">
	 <table width="100%" align="center">
	  <tr>
	   <td align="center">
	    <input type="submit" name="generar" value="Generar" >
	   </td>
	  </tr>
	 </table>
	 <script>
	  if(typeof(document.all.cod_barra_<?=$foco?>)!="undefined")
	   document.all.cod_barra_<?=$foco?>.focus();
	 </script>
	</from>
	</body>
	</html>
<?
fin_pagina();
?>