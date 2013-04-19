<?/*

----------------------------------------
 Autor: Fernando - MAC
 Fecha: 28/09/2005
----------------------------------------

MODIFICADA POR
$Author: fernando $
$Revision: 1.21 $
$Date: 2007/02/22 17:56:25 $
*/

/*******************************************************************************
 Genera en forma dinamica la tabla de imputaciones de pagos, tomando los
 campos necesarios para la imputacion de la tabla tipo_imputacion.
 @id_imputacion El ID de imputacion del cual se mostraran los datos. Vacio si
                se deben mostrar vacios los datos de los campos.
 @monto_total   El monto total del pago
 @moneda        se utiliza para especificar si el pago es en dolares o en
                pesos (el valor por default), para decidir si se
                muestra la parte de cotizacion dolar, o no.
 @reset_imput   si viene en 1 siginifica que se deben mostrar todos los campos
 				de la tabla vacios
*******************************************************************************/
function tabla_imputacion($id_imputacion="",$monto_total="",$moneda='$',$reset_imput=0,$valor_dolar=0)
{


 //traemos los distritos, necesarios para Percepcion de Ingresos Brutos
 $query="select id_distrito,nombre from distrito order by nombre";
 $provincias=sql($query,"<br>Error al traer las provincias<br>") or fin_pagina();

 //traemos los tipos de imputacion existentes en la tabla tipo_imputacion, para generar la interface de Imputacion de pagos
 $query="select * from tipo_imputacion where activo=1 order by posicion";
 $tipos_imputacion=sql($query,"<br>Error al traer los tipos de imputacion<br>") or fin_pagina();

 $finalizado_sin_discriminar=$_POST["finalizar_sin_discriminar"];
 $valores["monto_total"]=$monto_total;
 $valores["monto_dolares"]=$monto_total;
 $valores["valor_dolar"]=$valor_dolar;
 
 if($id_imputacion)
 {
  //traemos los datos de montos en dolares, por si hay algo cargado
  //traemos los datos de montos en dolares, por si hay algo cargado
  $query="select valor_dolar,monto_dolar,estado_imputacion.nombre as estado
          from imputacion join estado_imputacion using (id_estado_imputacion)
          where id_imputacion=$id_imputacion";
  $datos_imputacion=sql($query,"<br>Error al traer datos de imputacion<br>") or fin_pagina();
  $estado_imputacion=$datos_imputacion->fields["estado"];

  if($estado_imputacion=="Finalizado Completo" || $estado_imputacion=="Finalizado Sin Discriminar" ||$estado_imputacion=="Pago Anulado")
   $permiso_editar="disabled";
  else
   $permiso_editar="";

  if($estado_imputacion=="Finalizado Sin Discriminar" ||$estado_imputacion=="Sin Discriminar (por controlar)")
   $finalizado_sin_discriminar=1;
  elseif ($_POST["finalizar_sin_discriminar"]=="")
   $finalizado_sin_discriminar=0;



  /*si tenemos el id de imputacion ($id_imputacion)
	generamos el arreglo con los valores de los campos de imputacion
	El formato del arreglo es: como indice asociativo el nombre
	del campo, y como valor el valor correspondiente.
	En el caso de percepciones de IB, es un arreglo que contiene
	una entrada por cada provincia en percepcion_ib. Cada entrada
	de este sub-arreglo es a su vez un arreglo con dos campos:
	-"id_distrito"  que tiene el id de distrito correspondiente
	-"monto"        que tiene el monto correspondiente a percepcion_ib
			        para el distrito dado en el campo anterior.
	El parametro es opcional por lo que si no tiene valores,
	se mostrara la tabla de imputacion vacia.
	Un ejemplo de formato para este arreglo seria:
	   $valores["monto_total"]=1000;
	   $valores["monto_neto"]["monto"]=924;
	   $valores["monto_neto"]["id_detalle_imputacion"]=1;
	   $valores["impuestos_internos"]["monto"]=20;
	   $valores["impuestos_internos"]["id_detalle_imputacion"]=2;
	   $valores["percepcion_iva"]["monto"]=12;
	   $valores["percepcion_iva"]["id_detalle_imputacion"]=3;
	   $valores["iva_10_5"]["monto"]=5;
	   $valores["iva_10_5"]["id_detalle_imputacion"]=4;
	   $valores["iva_21"]["monto"]=10;
	   $valores["iva_21"]["id_detalle_imputacion"]=5;
	   $valores["iva_27"]["monto"]=17;
	   $valores["iva_27"]["id_detalle_imputacion"]=6;
	   $valores["percepciones_ib"][]=array();
	   $valores["percepciones_ib"][]=array();
	   $valores["percepciones_ib"][0]["id_distrito"]=3;
	   $valores["percepciones_ib"][0]["monto"]=4;
	   $valores["percepciones_ib"][0]["id_detalle_imputacion"]=7;
	   $valores["percepciones_ib"][1]["id_distrito"]=1;
	   $valores["percepciones_ib"][1]["monto"]=8;
	   $valores["percepciones_ib"][1]["id_detalle_imputacion"]=8;
	   */

   $valores[]=array();
   if($datos_imputacion->fields["monto_dolar"]!="")
   {
   	$valores["monto_dolares"]=$datos_imputacion->fields["monto_dolar"];
    $valores["valor_dolar"]=$datos_imputacion->fields["valor_dolar"];
    $valores["monto_total"]=$valores["valor_dolar"]*$valores["monto_dolares"];
   }
   else{
    $valores["monto_total"]=$monto_total;
   }


   $valores["percepciones_ib"][]=array();

   //traemos el detalle de la imputacion
   $query="select id_tipo_imputacion,tipo_imputacion.nombre,id_distrito,monto,id_detalle_imputacion
           from detalle_imputacion join tipo_imputacion using(id_tipo_imputacion)
           where id_imputacion=$id_imputacion";
   $detalles_imputacion=sql($query,"<br>Error al traer los detalles de imputaciones<br>") or fin_pagina();

   $indice=0;
   while (!$detalles_imputacion->EOF)
   {
   	if($detalles_imputacion->fields["nombre"]!="percepcion_ib")
   	{
   	 $valores[$detalles_imputacion->fields["nombre"]]["monto"]=$detalles_imputacion->fields["monto"];
   	 $valores[$detalles_imputacion->fields["nombre"]]["id_detalle_imputacion"]=$detalles_imputacion->fields["id_detalle_imputacion"];
   	}//de if($detalles_imputacion->fields["nombre"]!="percepcion_ib")
   	else //es una percepcion
   	{
   	 $valores["percepciones_ib"][$indice]["monto"]=$detalles_imputacion->fields["monto"];
   	 $valores["percepciones_ib"][$indice]["id_distrito"]=$detalles_imputacion->fields["id_distrito"];
   	 $valores["percepciones_ib"][$indice]["id_detalle_imputacion"]=$detalles_imputacion->fields["id_detalle_imputacion"];
   	 $indice++;
   	}

    $detalles_imputacion->MoveNext();
   }//de while(!$detalles_imputacion->EOF)
 }//de if($id_imputacion)
 //para el caso de la pagina de caja, que se recarga sin guardar, se deben mantener los datos.
 //Entonces si se recargo la pagina se mantienen los datos, del siguiente modo..
 else if($_POST["select_moneda"]!="" && $_POST)
 {
  $valores=retener_datos_imputacion();
 }

 
//si este parametro viene en 1, entonces se resetean los valores obtenidos, para que muestre toda la tabla en blanco
 if($reset_imput)
 {
 	
  $valores=array();
 }
?>
   <script>

    function calcular_total_neto()
    {
      var monto_neto;
      var indice=0,percepcion_ib;

      if(!isNaN(document.all.monto_total.value))
       monto_neto=parseFloat(document.all.monto_total.value);
      else
       monto_neto=0;
      <?
      $tipos_imputacion->Move(0);
      while (!$tipos_imputacion->EOF)
      {
       if($tipos_imputacion->fields["nombre"]!="percepcion_ib" && $tipos_imputacion->fields["nombre"]!="monto_neto")
       {?>
       //si esta chequeado el check correspondiente, le restamos al monto total ese campo
       if(document.all.chk_<?=$tipos_imputacion->fields["nombre"]?>.checked)
       {

       	if(document.all.<?=$tipos_imputacion->fields["nombre"]?>.value!="" && !isNaN(document.all.<?=$tipos_imputacion->fields["nombre"]?>.value))
         monto_neto-=parseFloat(document.all.<?=$tipos_imputacion->fields["nombre"]?>.value);
       }//de if(document.all.chk_$tipos_imputacion->fields["nombre"].checked)
       <?
       }//de if($tipos_imputacion->fields["nombre"]!="percepcion_ib" && $tipos_imputacion->fields["nombre"]!="monto_neto")
       else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")
       {
       	?>
       	//si esta chequeado el check de percecpiones_ib, le restamos al monto total todas las percepciones_ib presentes
       	if(document.all.chk_<?=$tipos_imputacion->fields["nombre"]?>.checked)
       	{
       	 indice=0;
       	 while(typeof(eval("document.all.ib_<?=$tipos_imputacion->fields["nombre"]?>_"+indice))!="undefined")
       	 {
       	   percepcion_ib=eval("document.all.ib_<?=$tipos_imputacion->fields["nombre"]?>_"+indice);
           if(percepcion_ib.value!="" && !isNaN(percepcion_ib.value))
       	    monto_neto-=parseFloat(percepcion_ib.value);

       	   indice++;
       	 }//de while(typeof(eval("document.all.ib_$tipos_imputacion->fields["nombre"]_"+indice))!="undefined")
       	} //de if(document.all.chk_$tipos_imputacion->fields["nombre"].checked)
       	<?
       }
       $tipos_imputacion->MoveNext();
      }//de while(!$tipos_imputacion->EOF)
      ?>

      document.all.monto_neto.value=formato_BD(monto_neto);
    }//de function calcular_total_neto()

    function agregar_percepcion_ib()
    {
     var otabla=document.all.tabla_percepciones;
     var i=document.all.ultimo_perc_ib.value;
     var fila=otabla.insertRow(otabla.rows.length);
     fila.id="fila_percepcion_"+i;

  	  	fila.insertCell(0).align="center";
  	  	fila.cells[0].innerHTML="<select name='provincias_"+i+"' onKeypress='buscar_op(this);' onblur='borrar_buffer();' onclick='borrar_buffer();'>"+document.all.provincias_0.innerHTML+"</select>"+
  	  							"<input type='button' name='borrar_p_ib' title='Elimina la entrada' value='-' onclick='eliminar_percepcion_ib("+i+")'>";
	  	fila.insertCell(1).align="right";
	  	fila.cells[1].innerHTML="<input type='text' name='ib_percepcion_ib_"+i+"' value='' size='10' onchange='if(this.value!=\"\")calcular_total_neto();' style='text-align:right'>"+
	  	                        "<input type='hidden' name='hidden_ib_percepcion_ib_"+i+"' value=''>"+
	  	                        "<input type='hidden' name='hidden_provincias_"+i+"' value=''>"+
	  	                        "<input type='hidden' name='id_det_imp_percepcion_ib_"+i+"' value=''>";

	  document.all.ultimo_perc_ib.value++;

    }//de function agregar_percepcion_ib()


    /*************************************************************
     Funcion para eliminar la fila de percepcion indicada mediante
     el parametro index. La funcion recorre todas las filas
     de la tabla de percepciones, hasta encontrar la que tiene,
     como parte del nombre de la fila, al indice indicado en
     el parametro. Luego elimina esa fila.
    **************************************************************/
    function eliminar_percepcion_ib(index)
    {
      var otabla=document.all.tabla_percepciones,fila;
	  var index_tabla=0,seguir=1;

	  //empieza en 4 para comenzar recorriendo solo desde las filas que se pueden eliminar
	  //(esto excluye las primeras dos filas de titulos, y la primera de las perc_ib, que no se puede eliminar)
	  var cont_filas=4;

      fila=eval("document.all.fila_percepcion_"+index_tabla);
      while(seguir)
      {
        if(index_tabla==index)
        {
         otabla.deleteRow(fila.rowIndex);
         seguir=0;
        }

        index_tabla++;
        //avanzamos hasta encontrar una nueva fila activa, o hasta que se termine la tabla
        while((typeof(eval("document.all.fila_percepcion_"+index_tabla))=="undefined") && cont_filas<=otabla.rows.length)
          index_tabla++;

        if(cont_filas<=otabla.rows.length)
        {fila=eval("document.all.fila_percepcion_"+index_tabla);
         cont_filas++;
        }
        else
         seguir=0;


      }//de for(index_tabla;index_tabla<cant_filas;index_tabla)



      calcular_total_neto();

    }//de function agregar_percepcion_ib()


    function habilitar_campo(check,campo)
    {
     if(check.checked==1)
     {
    	 campo.readOnly=0;
     }
     else
     {
     	 campo.readOnly=1;
     }

     calcular_total_neto();
    }

    function habilitar_percepciones_ib(check,nombre)
    {
     var select,monto;
     var index=0,boolVal;
     var max=document.all.tabla_percepciones.rows.length-2;

     boolVal=!check.checked;
     for(index=0;index<max;index++)
     {
       select=eval("document.all.provincias_"+index);
       monto=eval("document.all.ib_"+nombre+"_"+index);

       select.disabled=boolVal;
       monto.readOnly=boolVal;

       calcular_total_neto();
     }//de for(index=0;index<max;index++)

     if(check.checked==1)
     {
     	document.all.nuevo_p_ib.disabled=0;
     }
     else
     {
     	document.all.nuevo_p_ib.disabled=1;
     }
    }//de function habilitar_percepciones_ib(check,nombre)


    function calcular_valor_pesos()
    {
     var dolar_valor=0;
     var monto_total_dolar=0;
     if(document.all.valor_dolar_imp.value!="")
      dolar_valor=parseFloat(document.all.valor_dolar_imp.value);

     if(document.all.monto_dolares.value!="")
      monto_total_dolar=parseFloat(document.all.monto_dolares.value);

     if(monto_total_dolar==0 || dolar_valor==0)
      document.all.monto_total.value=0;
     else
      document.all.monto_total.value=formato_BD(monto_total_dolar*dolar_valor);

     calcular_total_neto();
    }//de function calcular_valor_pesos()


    function mostrar_ocultar_imputacion()
    {
     if(document.all.imputacion_pagos.style.display=='none')
     {document.all.imputacion_pagos.style.display='block';
      document.all.imagen_ocultar.src="../../imagenes/dropdown2.gif";
      document.all.imagen_ocultar.title="Ocultar Tabla";
     }
     else
     {document.all.imputacion_pagos.style.display='none';
      document.all.imagen_ocultar.src="../../imagenes/drop2.gif";
      document.all.imagen_ocultar.title="Mostrar Tabla";
     }
    }//de function mostrar_ocultar_imputacion()


    /*****************************************************************
     Funcion que setea los montos totales de dolar o pesos de la
     parte de imputacion.
     @tipo_pago  indica desde que tipo de pago se esta llamando a
     			 la funcion, para saber desde donde sacar los datos
     			 para setear los montos
    ******************************************************************/
    function setear_montos_imputacion(tipo_pago)
	{
	  switch (tipo_pago)
	  {
	   case "id_ingreso_egreso":
	           if(typeof(document.all.monto_total)!="undefined" && typeof(document.all.monto_neto)!="undefined")
		 	   {
	 		    if(document.all.select_moneda.options[document.all.select_moneda.options.selectedIndex].text=="Dólares")
			    {document.all.monto_dolares.value=formato_BD(document.all.text_monto.value);
			     document.all.monto_total.value="";
			     calcular_valor_pesos();
			    }
			    else
			    {document.all.monto_total.value=formato_BD(document.all.text_monto.value);
			     document.all.monto_neto.value=formato_BD(document.all.text_monto.value);
			     document.all.monto_dolares.value="";
			    }
			  }//de if(typeof(document.all.monto_total)!="undefined" && typeof(document.all.monto_neto)!="undefined")
			  break;
	   case "númeroch":
	          document.all.monto_total.value=formato_BD(document.all.Ingreso_Cheque_Importe.value);
			  document.all.monto_neto.value=formato_BD(document.all.Ingreso_Cheque_Importe.value);
			  document.all.monto_dolares.value="";
	          break;
	   case "iddébito":
	          document.all.monto_total.value=formato_BD(document.all.Ingreso_Debito_Importe.value);
			  document.all.monto_neto.value=formato_BD(document.all.Ingreso_Debito_Importe.value);
			  document.all.monto_dolares.value="";
	          break;
	   default: alert("No se reconoció el tipo de pago al intentar setear los montos de imputación\n");
	  }//de switch (tipo_pago)
	}//de function setear_montos_imputacion()


    /**********************************************
     Controla los campos obligatorios, o aquellos
     que se tornan obligatorios cuando chequean un
     checkbox.
    ***********************************************/
    function control_campos_imputacion()
    {
     var msg="--------------------------------------------------------\n";
     var ret_value=0;
     var hay_monto_dolar=0;
     msg+="Falta Completar para imputación de pago:\n";
     msg+="---------------------\n";

     //para cuando estamos en detalle imputacion
     if(typeof(document.all.tipo_pago)!="undefined")
     {if(document.all.tipo_pago.value=="id_ingreso_egreso" && document.all.tabla_dolar.style.display=='block')
       hay_monto_dolar=1;
     }

      //para el caso de un egreso de caja: si la moneda es dolar, controlamos el monto y el valor del dolar
     if((typeof(document.all.es_caja)!="undefined" && typeof(document.all.select_moneda)!="undefined")
          &&
          document.all.select_moneda.options[document.all.select_moneda.options.selectedIndex].text=="Dólares"
        )
        hay_monto_dolar=1;

     if(hay_monto_dolar)
     {
      if(document.all.valor_dolar_imp.value=="")
      {
       msg+="  Valor Dolar\n";
       ret_value++;
      }
      if(document.all.monto_dolares.value=="")
      {
       msg+="  Monto en Dólares\n";
       ret_value++;
      }
     }//de if(document.all.tabla_dolar.style.display='block')

     //monto total
     if(document.all.monto_total.value=="")
     {
      msg+="  Monto total del pago\n";
      ret_value++;
     }

      <?
      $tipos_imputacion->Move(0);
      while (!$tipos_imputacion->EOF)
      {
       if($tipos_imputacion->fields["nombre"]!="percepcion_ib" && $tipos_imputacion->fields["nombre"]!="monto_neto")
       {?>
       //si esta chequeado el check, pero el valor del monto es vacio, avisamos
       if(document.all.chk_<?=$tipos_imputacion->fields["nombre"]?>.checked==1 && document.all.<?=$tipos_imputacion->fields["nombre"]?>.value=="")
       {
       	msg+="  <?=$tipos_imputacion->fields["descripcion"]?>\n";
        ret_value++;
       }//de if(document.all.chk_$tipos_imputacion->fields["nombre"].checked==1 && document.all.$tipos_imputacion->fields["nombre"].value=="")
       <?
       }//de if($tipos_imputacion->fields["nombre"]!="percepcion_ib" && $tipos_imputacion->fields["nombre"]!="monto_neto")
       else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")
       {
       	?>
       	//si esta chequeado el check de percecpiones_ib, le restamos al monto total todas las percepciones_ib presentes
       	if(document.all.chk_<?=$tipos_imputacion->fields["nombre"]?>.checked)
       	{
       	 indice=0;
       	 while(typeof(eval("document.all.ib_<?=$tipos_imputacion->fields["nombre"]?>_"+indice))!="undefined")
       	 {
       	   percepcion_ib=eval("document.all.ib_<?=$tipos_imputacion->fields["nombre"]?>_"+indice);
           if(percepcion_ib.value=="")
           {
            msg+="  <?=$tipos_imputacion->fields["descripcion"]?> Nº "+(indice+1)+"\n";
            ret_value++;
           }
           provincia_ib=eval("document.all.provincias_"+indice);
           if(provincia_ib.value==-1)
           {
            msg+="  Provincia de <?=$tipos_imputacion->fields["descripcion"]?> Nº "+(indice+1)+"\n";
            ret_value++;
           }

       	   indice++;
       	 }//de while(typeof(eval("document.all.ib_$tipos_imputacion->fields["nombre"]_"+indice))!="undefined")
       	} //de if(document.all.chk_$tipos_imputacion->fields["nombre"].checked)
       	<?
       }
       $tipos_imputacion->MoveNext();
      }//de while(!$tipos_imputacion->EOF)
      ?>

      //ACA PUEDE PASAR CUALQUIER COSA!!! SE PERMITEN MONTOS NEGATIVOS, EN CERO...POSITIVOS....
      //Control para que el monto neto sea siempre mayor a cero
      /*if(document.all.monto_neto.value<0)
      {
       msg+="  El monto neto es menor que 0\n(Controle los montos de imputacion)\n";
       ret_value++;
      }*/

      msg+="--------------------------------------------------------\n";
      if(ret_value>0)
      { alert(msg);
        return false;
      }
      else
      {
      	if(document.all.finalizar_sin_discriminar.checked==1)//si se finaliza sin discriminar se pone al monto neto, el valor del monto total
      	 document.all.monto_neto.value=document.all.monto_total.value;
      	else//sino se recalcula el neto preventivamente
      	 calcular_total_neto();

      	if(typeof(document.all.terminar_directamente)!="undefined" && document.all.terminar_directamente.checked==1)
      	{
      	 if(!confirm("¿Está seguro que desea indicar que la imputación ya fue controlada?"))
      	  return false;
      	}

      	if(typeof(document.all.finalizar_sin_discriminar)!="undefined" && document.all.finalizar_sin_discriminar.checked==1)
      	{
      	 if(!confirm('¿Está seguro que desea pasar la imputación sin discriminar?'))
      	  return false;
      	}

      	return true;
      }//de if(ret_value>0)

    }//de function control_campos_imputacion()
   </script>
    <input type="hidden" name="estado_imputacion" value="<?=$estado_imputacion?>">
    <table width="90%" class="bordes">
        <tr id = mo>
            <td width="1%" style="cursor:hand">
             <?if($id_imputacion=="")
               { $display_tabla="none";
                 $src_imagen_ocultar="../../imagenes/drop2.gif";
                 $title_imagen_ocultar="Mostrar Tabla";
               }
               else
               { $display_tabla="block";
                 $src_imagen_ocultar="../../imagenes/dropdown2.gif";
                 $title_imagen_ocultar="Ocultar Tabla";
               }
             ?>
             <img id="imagen_ocultar" src="<?=$src_imagen_ocultar?>" onclick="mostrar_ocultar_imputacion()" title="<?=$title_imagen_ocultar?>">
            </td>
            <td>
             Imputar Pago
            </td>
        </tr>
        <tr>
         <td colspan="2">
          <div style="display:'<?=$display_tabla?>'" id="imputacion_pagos">
           <table width="100%">
            <tr>
	         <td width="100%" align="right">
	          <input type="checkbox" name="finalizar_sin_discriminar" value="1" <?if($finalizado_sin_discriminar) echo "checked";?> <?=$permiso_editar?>> <b>Sin Discriminar</b>
	         </td>
	        </tr>
	       </table>
        <?
        if($moneda=='U$S')
         $display_tabla="block";
        else
         $display_tabla="none";
        ?>
         <hr>
         <table width="90%" id="tabla_dolar" style="display:'<?=$display_tabla?>'" align="center" class="bordes">
         <tr>
	        <td>&nbsp;</td>
	        <td>
	         <b>Cotización Dolar</b>
	        </td>
	        <td align="right">
	         <input type=text name="valor_dolar_imp" size=4 value="<?if($valores["valor_dolar"])echo number_format($valores["valor_dolar"],2,'.','')?>" onchange="control_numero(this,'Valor Dolar');calcular_valor_pesos()" <?=$permiso_editar?>>
	        </td>
         </tr>
         <tr>
            <td>&nbsp;</td>
            <td>
             <b>Monto Dólares</b>
            </td>
            <td align="right">
             <font color="Red" size="2"><b>U$S</b></font> <input type=text name="monto_dolares" size=10 value="<?if($valores["monto_dolares"])echo number_format($valores["monto_dolares"],2,'.','')?>" style="text-align:right" onchange="calcular_valor_pesos()" readonly  <?=$permiso_editar?>>
            </td>
         </tr>
        </table>

        <table width="100%">
        <tr>
            <td>&nbsp;</td>
            <td>
             <b>Monto Total del Pago</b>
            </td>
            <td align="right">
             <font color="Red" size="2"><b>$</b></font> <input type=text name=monto_total size=10 readonly value="<?if($valores["monto_total"])echo number_format($valores["monto_total"],2,'.',''); elseif ($valores["monto_total"]==0) echo "0.00";?>" style="text-align:right">
            </td>
        </tr>
        <tr>
            <td colspan=3>
              <hr>
            </td>
         </tr>
        <?
        $tipos_imputacion->Move(0);
        while (!$tipos_imputacion->EOF)
        {
         if($tipos_imputacion->fields["nombre"]=="monto_neto")
         {?>
          <tr>
           <td colspan="3">
            <hr>
           </td>
          </tr>
         <?
          $onclick_neto="onclick='calcular_total_neto()'";

         }//de if($tipos_imputacion->fields["nombre"]=="monto_neto")

         if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
         {?>
          <tr>
           <td>
            <?
            if($tipos_imputacion->fields["nombre"]!="monto_neto")
            {
             if($valores[$tipos_imputacion->fields["nombre"]]["monto"]=="")
             {$checked_campo="";
              $readonly_campo="readonly";
             }
             else
             {$checked_campo="checked";
              $readonly_campo="";
             }
            ?>
             <input type="checkbox" name="chk_<?=$tipos_imputacion->fields["nombre"]?>" value="1"
              onclick="habilitar_campo(this,document.all.<?=$tipos_imputacion->fields["nombre"]?>)"
              <?=$checked_campo?>
              <?=$permiso_editar?>
             >
            <?
             $simbolo_moneda="";
             $permiso_editar_imput=$permiso_editar;
            }//de if($tipos_imputacion->fields["nombre"]!="monto_neto")
            else
            {
             echo "&nbsp;";
             $readonly_campo="readonly";
             $simbolo_moneda="<font color='Red' size='2'><b>$</b></font>";
             $permiso_editar_imput="";
            }
            ?>
           </td>
           <td>
            <b><?=$tipos_imputacion->fields["descripcion"]?></b>
           </td>
           <td align="right">
            <?if($valores!="" && $tipos_imputacion->fields["nombre"])
                $monto_aux=$valores[$tipos_imputacion->fields["nombre"]]["monto"];
              else
                $monto_aux="";

            if(!$onclick_neto)
             $evento_input="onchange=\"control_numero(this,'".$tipos_imputacion->fields["descripcion"]."');if(this.value!='')calcular_total_neto();\"";
            else
             $evento_input=$onclick_neto;
            ?>
            <?=$simbolo_moneda?> <input type="text" name="<?=$tipos_imputacion->fields["nombre"]?>" value="<?if($monto_aux)echo number_format($monto_aux,2,'.','')?>" size="10" <?=$readonly_campo?> <?=$evento_input?> style="text-align:right"  <?=$permiso_editar_imput?>>
            <input type="hidden" name="hidden_<?=$tipos_imputacion->fields["nombre"]?>" value="<?if($monto_aux)echo number_format($monto_aux,2,'.','')?>">
            <input type="hidden" name="id_det_imp_<?=$tipos_imputacion->fields["nombre"]?>" value="<?=($valores!="")?$valores[$tipos_imputacion->fields["nombre"]]["id_detalle_imputacion"]:""?>">
           </td>
          </tr>
         <?
         }//de if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
         else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")
         {
           if($valores!="" && $valores["percepciones_ib"][0]["id_distrito"]!="")
           { $disabled_perc="";
             $readonly_perc="";
           }
           else
           { $disabled_perc="disabled";
             $readonly_perc="readonly";
           }

          ?>
            <tr>
             <td>
              <input type="checkbox" name="chk_<?=$tipos_imputacion->fields["nombre"]?>" value="1"
               onclick="habilitar_percepciones_ib(this,'<?=$tipos_imputacion->fields["nombre"]?>')"
               <?if($valores!="" && $valores["percepciones_ib"][0]["id_distrito"]!="")echo "checked"?>
               <?=$permiso_editar?>
              >
             </td>
             <td colspan="2">
              <table class="bordes" width="100%" border="1" id="tabla_percepciones">
               <tr>
                <td colspan="2" align="center">
                 <b><?=$tipos_imputacion->fields["descripcion"]?></b>
                 <input type="button" name="nuevo_p_ib" title="Agregar una nueva entrada para Percepción de Ingresos Brutos" value="+" onclick="agregar_percepcion_ib()" <?=$disabled_perc?> <?=$permiso_editar?>>
                 <input type="hidden" name="borrar_elegido" value="">
                </td>
               </tr>
               <tr id="sub_tabla">
                <td>
                 Provincia
                </td>
                <td>
                 Monto
                </td>
               </tr>
               <?
               $perc_ib=0;

               $cant_percepciones=sizeof($valores["percepciones_ib"]);

               do
               {
               ?>
               <tr  id="fila_percepcion_<?=$perc_ib?>">
                <td align="center">
                 <select name="provincias_<?=$perc_ib?>" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();" <?=$disabled_perc?>  <?=$permiso_editar?>>
                   <option value="-1">Seleccione...</option>
                   <?
                   $provincias->Move(0);
                   while (!$provincias->EOF)
                   {?>
                    <option value="<?=$provincias->fields["id_distrito"]?>" <?if($valores!="" && ($valores["percepciones_ib"][$perc_ib]["id_distrito"]==$provincias->fields["id_distrito"])) echo "selected"?>>
                     <?=$provincias->fields["nombre"]?>
                    </option>
                    <?
                    $provincias->MoveNext();
                   }//de while(!$provincias->EOF)
                   ?>
                 </select>
                 <?
                 if($perc_ib!=0)
                 {?>
                  <input type="button" name="borrar_p_ib" title="Elimina la entrada" value="-" onclick="eliminar_percepcion_ib('<?=$perc_ib?>')" <?=$disabled_perc?> <?=$permiso_editar?>>
                 <?
                 }
                 else
                  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                 ?>
                </td>
                <td align="right">
                 <?
                 if($valores!="" && $valores["percepciones_ib"][$perc_ib]["monto"])
                  $monto_ib=$valores["percepciones_ib"][$perc_ib]["monto"];
                 else
                  $monto_ib="";
                 ?>
                 <input type="text" name="ib_<?=$tipos_imputacion->fields["nombre"]?>_<?=$perc_ib?>" value="<?if($monto_ib)echo number_format($monto_ib,2,'.','')?>" size="10" <?=$readonly_perc?> onchange="control_numero(this,'<?=$tipos_imputacion->fields["descripcion"]?> Nº <?=$perc_ib?>');if(this.value!='')calcular_total_neto();" style="text-align:right"  <?=$permiso_editar?>>
                 <input type="hidden" name="hidden_ib_<?=$tipos_imputacion->fields["nombre"]?>_<?=$perc_ib?>" value="<?if($monto_ib)echo number_format($monto_ib,2,'.','')?>">
                 <input type="hidden" name="hidden_provincias_<?=$perc_ib?>" value="<?=($valores!="")?$valores["percepciones_ib"][$perc_ib]["id_distrito"]:"";?>">
                 <input type="hidden" name="id_det_imp_<?=$tipos_imputacion->fields["nombre"]?>_<?=$perc_ib?>" value="<?=($valores!="")?$valores["percepciones_ib"][$perc_ib]["id_detalle_imputacion"]:"";?>">
                </td>
               </tr>
          <?
             $perc_ib++;
             }
             while ($perc_ib<$cant_percepciones);
             ?>
             </table>
            </td>
           </tr>
          <?
         }//de else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")
         $tipos_imputacion->MoveNext();
        }//de while(!$tipos_imputacion->EOF)
        ?>
        <input type="hidden" name="ultimo_perc_ib" value="<?=$perc_ib?>">
        <?
        if(permisos_check("inicio","permiso_terminar_imputacion") && $id_imputacion=="")
        {
        ?>
         <tr>
          <td colspan="3" align="right">
           <hr>
           <input type="checkbox" name="terminar_directamente" value="1"> <b>Controlado</b>
          </td>
         </tr>
        <?
        }//de if(permisos_check("inicio","permiso_terminar_imputacion"))
        ?>
        </table>
        </div>
       </td>
      </tr>

    </table>

    <script>
     calcular_total_neto();
     //solo para el caso de egreso de caja, que tiene moneda
     if(typeof(document.all.es_caja)!="undefined")
	 {
	 	  if(typeof(document.all.select_moneda)!="undefined" && document.all.select_moneda.options[document.all.select_moneda.options.selectedIndex].text=="Dólares")
	      { document.all.tabla_dolar.style.display="block";
	      }
		  else if(typeof(document.all.select_moneda)!="undefined")
		  { document.all.tabla_dolar.style.display="none";
		  }
     }
     </script>
    <?
}//de function tabla_imputacion($id_imputacion="",$monto_total="",$moneda='$')


/**********************************************************************************
 Funcion que recupera los datos obtenidos por POST y devuelve el arreglo $valores
 que contiene todos los datos capturados por POST, para imputacion.
 El formato es similar al que se usa en la funcion anterior para setear los campos.
 Asi se logra mantener los datos de imputacion cargados, para que
 se pueda usar luego de la pantalla de confirmacion de pagos, cuando se esta pagando
 una OC, con cualquiera de los metodos de pago existentes.
***********************************************************************************/
function retener_datos_imputacion()
{

 //traemos los tipos de imputacion existentes en la tabla tipo_imputacion
 $query="select * from tipo_imputacion where activo=1 order by posicion";
 $tipos_imputacion=sql($query,"<br>Error al traer los tipos de imputacion(imputar_pago)<br>") or fin_pagina();

 $valores[]=array();

 $valores["monto_dolares"]=$_POST["monto_dolares"];
 $valores["valor_dolar"]=$_POST["valor_dolar_imp"];
 $valores["monto_total"]=$_POST["monto_total"];
 $valores["estado_imputacion"]=$_POST["estado_imputacion"];
 $valores["finalizar_sin_discriminar"]=$_POST["finalizar_sin_discriminar"];
 $valores["terminar_directamente"]=$_POST["terminar_directamente"];
 $valores["ultimo_perc_ib"]=$_POST["ultimo_perc_ib"];

  $valores["percepciones_ib"][]=array();

 $tipos_imputacion->Move(0);
 while (!$tipos_imputacion->EOF)
 {
 	if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
    {
     $valores[$tipos_imputacion->fields["nombre"]]["monto"]=$_POST[$tipos_imputacion->fields["nombre"]];
   	 $valores[$tipos_imputacion->fields["nombre"]]["id_detalle_imputacion"]=$_POST["id_det_imp_".$tipos_imputacion->fields["nombre"]];
   	 $valores[$tipos_imputacion->fields["nombre"]]["chk"]=$_POST["chk_".$tipos_imputacion->fields["nombre"]];
   	 $valores[$tipos_imputacion->fields["nombre"]]["hidden"]=$_POST["hidden_".$tipos_imputacion->fields["nombre"]];
    }//de if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
    else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")
    {
     $valores["percepciones_ib_chk"]=$_POST["chk_".$tipos_imputacion->fields["nombre"]];
     $perc_ib=0;
     while (($_POST["provincias_$perc_ib"]!=-1 && $_POST["provincias_$perc_ib"]!="") || $_POST["ib_".$tipos_imputacion->fields["nombre"]."_$perc_ib"]!="")
     {
       $valores["percepciones_ib"][$perc_ib]["monto"]=$_POST["ib_".$tipos_imputacion->fields["nombre"]."_$perc_ib"];
   	   $valores["percepciones_ib"][$perc_ib]["id_distrito"]=$_POST["provincias_$perc_ib"];
   	   $valores["percepciones_ib"][$perc_ib]["id_detalle_imputacion"]=$_POST["id_det_imp_".$tipos_imputacion->fields["nombre"]."_$perc_ib"];
   	   $valores["percepciones_ib"][$perc_ib]["hidden_ib"]=$_POST["hidden_ib_".$tipos_imputacion->fields["nombre"]."_$perc_ib"];
   	   $valores["percepciones_ib"][$perc_ib]["hidden_provincias"]=$_POST["hidden_provincias_$perc_ib"];
       $perc_ib++;
     }//de while ($_POST["provincias_$index"]!="")
    }//de else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")

    $tipos_imputacion->MoveNext();
 }//de while (!$tipos_imputacion->EOF)

 return $valores;
}//de function retener_datos_imputacion()



/**********************************************************************************
 Funcion que genera los hiddens necesarios para mantener los valores de imputacion
 que deben ser guardados. El formato del parametro $valores es el mismo que la
 salida que produce la funcion retener_datos_imputacion(). Con esta funcion podemos
 mantener los datos ingresados en la pagina del pago respectivo, y pedir confirma-
 cion antes de guardar un pago de una OC.
***********************************************************************************/
function generar_hiddens_datos_imputacion($valores)
{
  //traemos los tipos de imputacion existentes en la tabla tipo_imputacion
 $query="select * from tipo_imputacion where activo=1 order by posicion";
 $tipos_imputacion=sql($query,"<br>Error al traer los tipos de imputacion(imputar_pago)<br>") or fin_pagina();

 //generamos los hiddens correspondientes, de la misma manera que en la funcion anterior
 //se generan los campos correspondientes
 ?>
 <input type="hidden" name="valor_dolar_imp" value="<?=$valores["valor_dolar"]?>">
 <input type="hidden" name="monto_dolares" value="<?=$valores["monto_dolares"]?>">
 <input type="hidden" name="monto_total" value="<?=$valores["monto_total"]?>">
 <input type="hidden" name="estado_imputacion" value="<?=$valores["estado_imputacion"]?>">
 <input type="hidden" name="ultimo_perc_ib" value="<?=$valores["ultimo_perc_ib"]?>">
 <input type="hidden" name="finalizar_sin_discriminar" value="<?=$valores["finalizar_sin_discriminar"]?>">
 <input type="hidden" name="terminar_directamente" value="<?=$valores["terminar_directamente"]?>">
 <?
 $tipos_imputacion->Move(0);
 while (!$tipos_imputacion->EOF)
 {
 	if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
    {?>
     <input type="hidden" name="chk_<?=$tipos_imputacion->fields["nombre"]?>" value="<?=$valores[$tipos_imputacion->fields["nombre"]]["chk"]?>">
     <input type="hidden" name="<?=$tipos_imputacion->fields["nombre"]?>" value="<?=$valores[$tipos_imputacion->fields["nombre"]]["monto"]?>">
     <input type="hidden" name="hidden_<?=$tipos_imputacion->fields["nombre"]?>" value="<?=$valores[$tipos_imputacion->fields["nombre"]]["hidden"]?>">
     <input type="hidden" name="id_det_imp_<?=$tipos_imputacion->fields["nombre"]?>" value="<?=$valores[$tipos_imputacion->fields["nombre"]]["id_detalle_imputacion"]?>">
     <?
    }//de if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
    else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")
    {
     ?>
     <input type="hidden" name="chk_<?=$tipos_imputacion->fields["nombre"]?>" value="<?=$valores["percepciones_ib_chk"]?>">
     <?
     $perc_ib=0;
     for($perc_ib;$perc_ib<sizeof($valores["percepciones_ib"]);$perc_ib++)
     {
     ?>
      <input type="hidden" name="provincias_<?=$perc_ib?>" value="<?=$valores["percepciones_ib"][$perc_ib]["id_distrito"]?>">
      <input type="hidden" name="ib_<?=$tipos_imputacion->fields["nombre"]?>_<?=$perc_ib?>" value="<?=$valores["percepciones_ib"][$perc_ib]["monto"]?>">
      <input type="hidden" name="hidden_ib_<?=$tipos_imputacion->fields["nombre"]?>_<?=$perc_ib?>" value="<?=$_POST["hidden_ib_".$tipos_imputacion->fields["nombre"]."_$perc_ib"]?>">
      <input type="hidden" name="hidden_provincias_<?=$perc_ib?>" value="<?=$_POST["hidden_provincias_$perc_ib"]?>">
      <input type="hidden" name="id_det_imp_<?=$tipos_imputacion->fields["nombre"]?>_<?=$perc_ib?>" value="<?=$_POST["id_det_imp_".$tipos_imputacion->fields["nombre"]."_$perc_ib"]?>">
     <?
     }//de for($perc_ib;$perc_ib<sizeof($valores["percepciones_ib"]);$perc_ib++)
    }//de else if($tipos_imputacion->fields["nombre"]=="percepcion_ib")

    $tipos_imputacion->MoveNext();
 }//de while (!$tipos_imputacion->EOF)

}//de function retener_datos_imputacion()




/**********************************************************************************
 Funcion que inserta o actualiza las imputaciones de un pago determinado,
 y setea el estado pasado como parametro.
 @pago				Un arreglo que especifica el tipo de pago que se imputara,
  					y el id correspondiente. En el caso de que el pago sea un cheque,
  					tambien tiene un campo adicional con el banco correspondiente
  					Ejemplos de este arreglo son:
  							$pago[]=array();
  							$pago["tipo_pago"]="id_ingreso_egreso";
  							$pago["id_pago"]=42;
  										o
  							$pago[]=array();
  							$pago["tipo_pago"]="númeroch";
  							$pago["id_pago"]=1378899512;
  							$pago["id_banco"]="4";
 					El tipo de pago puede ser:
 					 	-un egreso de caja (id_ingreso_egreso)
 						-un debito(iddébito)
 						-un cheque (idbanco,númeroch).
 @id_imputacion		especifica el id de imputacion que se esta actualizando.
 					En caso de que venga vacio implica que se insertara una nueva
 					imputacion. Si se pasa como parametro, entonces se realizara
 					una actualizacion de las entradas previamente cargadas.
 @fecha				La fecha con la que se debe almacenar/actualizar la imputación
***********************************************************************************/
function imputar_pago($pago,$id_imputacion="",$fecha="")
{
 global $db,$_ses_user,$msg;

 $fecha_hoy=date("Y-m-d H:i:s");

 $estado=$_POST["estado"];
 $titulo_estado=$estado;
 //si el estado de la imputacion es finalizado, entonces la funcion no debe hacer nada, y por eso retorna inmediatamente
 if($_POST["estado_imputacion"]=="Finalizado Completo" || $_POST["estado_imputacion"]=="Finalizado Sin Discriminar")
  return false;

 $db->StartTrans();

 $a_por_controlar=0;

 //traemos los tipos de imputacion existentes en la tabla tipo_imputacion
 $query="select * from tipo_imputacion where activo=1 order by posicion";
 $tipos_imputacion=sql($query,"<br>Error al traer los tipos de imputacion(imputar_pago)<br>") or fin_pagina();


 $id_banco=($pago["id_banco"])?$pago["id_banco"]:"null";
 $valor_dolar=($_POST["valor_dolar_imp"])?$_POST["valor_dolar_imp"]:1;
 $monto_dolar=($_POST["monto_dolares"])?$_POST["monto_dolares"]:"null";
 if($fecha=="")
  $fecha=date("Y-m-d H:i:s",mktime());
 else
  $fecha=Fecha_db($fecha);

 //determinamos el tipo de pago para formar el string que se usa en el log
 switch ($pago["tipo_pago"]) {
 	case "númeroch":$titulo_pago="Cheque Nº ".$pago["id_pago"];
 	                //traemos el nombre del banco
 	                $query="select nombrebanco from tipo_banco where idbanco=$id_banco";
 	                $bank=sql($query,"<br>Error al traer el nombre del banco<br>") or fin_pagina();
 	                $titulo_pago.=", del banco ".$bank->fields["nombrebanco"];
 	                break;
 	case "id_ingreso_egreso":$titulo_pago="Egreso de Caja Nº ".$pago["id_pago"];break;
 	case "iddébito":$titulo_pago="Débito Nº ".$pago["id_pago"];break;
  	default:die("<br>Error: no se reconoce el parametro tipo de pago");break;
 }

 //si se pasa a sin discriminar, se trae el id de ese estado de imputacion
 if($_POST["finalizar_sin_discriminar"]==1) //se pasa a sin discriminar
 {
 	//si ademas del check de sin discriminar se chequeo el de controlado, pasamos la imputacion a estado: Finalizado Sin Discriminar
 	if($_POST["terminar_directamente"]==1)
 		$titulo_estado="Finalizado Sin Discriminar";
 	else//sino, la pasamos a estado: Sin Discriminar (por controlar)
 		$titulo_estado="Sin Discriminar (por controlar)";

     $query="select id_estado_imputacion from estado_imputacion where nombre='$titulo_estado'";
	 $estado_id=sql($query,"<br>Error al traer el id de estado de imputacion<br>") or fin_pagina();
	 $id_estado_imputacion=$estado_id->fields["id_estado_imputacion"];
 }//del else de if($_POST["finalizar_sin_discriminar"]!=1)
 else if($_POST["terminar_directamente"]==1) //si se termina directamente, buscamos el id del estado Finalizado Completo
 {
     $query="select id_estado_imputacion from estado_imputacion where nombre='Finalizado Completo'";
	 $estado_id=sql($query,"<br>Error al traer el id de estado de imputacion<br>") or fin_pagina();
	 $id_estado_imputacion=$estado_id->fields["id_estado_imputacion"];
	 $titulo_estado="Finalizado Completo";
 }//del else de if($_POST["finalizar_sin_discriminar"]!=1)
 else //se busca el id del estado por default (Pendiente)
 {   //traemos el id del estado pasado como parametro
 	 if($estado=="")
 	 	$estado="Pendiente";
	 $query="select id_estado_imputacion from estado_imputacion where nombre='$estado'";
	 $estado_id=sql($query,"<br>Error al traer el id de estado de imputacion (parametro)<br>") or fin_pagina();
	 $id_estado_imputacion=$estado_id->fields["id_estado_imputacion"];
 }

 //si no pasaron el id de imputacion, entonces insertamos una nueva imputacion
 if($id_imputacion=="")
 {
 	//Insertamos la entrada en la tabla imputacion
 	$query="select nextval('imputacion_id_imputacion_seq') as id_imputacion";
 	$id=sql($query,"<br>Error al traer el id de imputacion<br>") or fin_pagina();
 	$id_imputacion=$id->fields["id_imputacion"];

 	$query="insert into imputacion (id_imputacion,".$pago["tipo_pago"].",idbanco,valor_dolar,fecha,id_estado_imputacion,monto_dolar)
 	        values($id_imputacion,".$pago["id_pago"].",$id_banco,$valor_dolar,'$fecha',$id_estado_imputacion,$monto_dolar)";
 	sql($query,"<br>Error al insertar la imputacion<br>") or fin_pagina();

 	//registramos en el log la insercion de la imputacion
 	$detalle_log_imputacion="Creación de la imputación para el pago con $titulo_pago, con estado: ";
 	$tipo_log_imputacion="creación";

    $msg="La imputación se cargó con éxito para el pago con $titulo_pago";

 }//de if($id_imputacion=="")
 else //sino, al tener el id de imputacion, actualizamos las entradas correspondientes
 {
  //actualizamos la entrada de la tabla imputacion, si tiene valor dolar
  $query="update imputacion set id_estado_imputacion=$id_estado_imputacion,valor_dolar=$valor_dolar,monto_dolar=$monto_dolar where id_imputacion=$id_imputacion";
  sql($query,"<br>Error al actualizar la imputacion<br>") or fin_pagina();

  //registramos en el log la actualizacion de la imputacion
  $detalle_log_imputacion="Actualización de la imputación para el pago con $titulo_pago. Estado actual: ";
  $tipo_log_imputacion="actualización";

  $msg="La imputación se actualizó con éxito para el pago con $titulo_pago";

 }//del else de if($id_imputacion=="")

 $monto_neto_negativo=0;
 $tipos_imputacion->Move(0);
 while (!$tipos_imputacion->EOF)
 {
   $id_tipo_imputacion=$tipos_imputacion->fields["id_tipo_imputacion"];

   //si el check correspondiente esta checkeado, insertamos el detalle de imputacion
   if($_POST["chk_".$tipos_imputacion->fields["nombre"]]==1 || $tipos_imputacion->fields["nombre"]=="monto_neto")
   {
   	  //Si se eligio la opcion: "Finalizar Sin Discriminar", se ignoran todas las imputaciones ingresadas, salvo la de monto neto
   	  if($_POST["finalizar_sin_discriminar"]!=1 || ($_POST["finalizar_sin_discriminar"]==1 && $tipos_imputacion->fields["nombre"]=="monto_neto"))
   	  {//si hay al menos un item cargado, entonces en lugar de estado Pendiente, le ponemos estado Por Controlar
       if($tipos_imputacion->fields["nombre"]!="monto_neto")
       {$a_por_controlar=1;
        $titulo_estado="Por Controlar";
       }
       else //si la imputacion actual es el monto neto, y su valor es negativo, seteamos la variable para luego avisar por mail
       {
        if($_POST[$tipos_imputacion->fields["nombre"]]<0)
         $monto_neto_negativo=1;
       }


	   if($tipos_imputacion->fields["nombre"]!="percepcion_ib")//si no estamos en tipo percepcion, insertamos directamente
	   {
	   	$monto=$_POST[$tipos_imputacion->fields["nombre"]];
	    $id_detalle_imputacion=$_POST["id_det_imp_".$tipos_imputacion->fields["nombre"]];

	    if($tipos_imputacion->fields["nombre"]!="monto_neto")//si el tipo de imptuacion no es monto neto, le asignamos la cuenta por default
	     $numero_cuenta=$tipos_imputacion->fields["numero_cuenta"];
	    else //sino le asignamos al monto neto, la cuenta que se eligio para el pago
	     $numero_cuenta=$_POST["cuentas"] or $numero_cuenta=$_POST["nro_cuenta"];

	   	//si el valor del id_detalle_imputacion, entonces no existia antes por lo que hay que insertarlo
	   	if($id_detalle_imputacion=="")
	   	{//insertamos el detalle de la imputacion
	      $query="insert into detalle_imputacion(id_imputacion,id_tipo_imputacion,usuario,fecha,monto,numero_cuenta)
	   	 	  values($id_imputacion,$id_tipo_imputacion,'".$_ses_user['name']."','$fecha',$monto,$numero_cuenta)";
	      sql($query,"<br>Error al insertar detalle de imputacion para ".$tipos_imputacion->fields["nombre"]."<br>") or fin_pagina();
	   	}//de if($_POST["hidden_".$tipos_imputacion->fields["nombre"]]=="")
	   	//si existia y el monto guardado antes es diferente al monto pasado por post, entonces hay que actualizar
	   	else if($_POST["hidden_".$tipos_imputacion->fields["nombre"]]!=$monto)
	   	{
	   	  $query="update detalle_imputacion set monto=$monto,usuario='".$_ses_user['name']."',fecha='$fecha' where id_detalle_imputacion=$id_detalle_imputacion";
	   	  sql($query,"<br>Error al actualizar el detalle de imputacion Nº $id_detalle_imputacion<br>") or fin_pagina();
	   	}//de else if($_POST["hidden_".$tipos_imputacion->fields["nombre"]]!=$monto)

	   }//de if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
	   else //en cambio si estamos en tipo percepcion, hacemos el while para insetar todas las percepciones
	   {
	   	$index=0;
	   	$imp_id=array();
	   	while($index<$_POST["ultimo_perc_ib"])
	   	{
	   	 if($_POST["ib_".$tipos_imputacion->fields["nombre"]."_$index"]!="")
	   	 {
	   	  $monto=$_POST["ib_".$tipos_imputacion->fields["nombre"]."_$index"];
	   	  $id_distrito=$_POST["provincias_".$index];
	   	  $id_detalle_imputacion=$_POST["id_det_imp_".$tipos_imputacion->fields["nombre"]."_$index"];

	   	  //si el valor del id_detalle_imputacion, entonces no existia antes por lo que hay que insertarlo
	   	  if($id_detalle_imputacion=="")
	   	  {
	   	   $query="select nextval ('detalle_imputacion_id_detalle_imputacion_seq') as id_detalle_imp";
	   	   $id_imp_id=sql($query,"<br>Error al traer secuencia de detalle imputacion<br>") or fin_pagina();
	   	   $id_detalle_imputacion=$id_imp_id->fields["id_detalle_imp"];

	   	   //como el tipo de imputacion es una percepcion ib, buscamos dado el id de distrito,
	   	   // la cuenta de Ingresos Brutos a Pagar, correspondiente a la provincia elegida en la imputacion
	   	   $query="select numero_cuenta from tipo_cuenta where concepto='Impuestos' and plan ilike 'Ingresos Brutos a Pagar%' and id_distrito=$id_distrito";
	   	   $cuenta=sql($query,"<br>Error al traer la cuenta correspondiente para la percepcion de IB<br>") or fin_pagina();
	   	   $numero_cuenta=$cuenta->fields["numero_cuenta"];

	   	   //insertamos el detalle de la imputacion
	       $query="insert into detalle_imputacion(id_detalle_imputacion,id_imputacion,id_tipo_imputacion,usuario,fecha,monto,id_distrito,numero_cuenta)
	   	 	  values($id_detalle_imputacion,$id_imputacion,$id_tipo_imputacion,'".$_ses_user['name']."','$fecha',$monto,$id_distrito,$numero_cuenta)";
	       sql($query,"<br>Error al insertar detalle de imputacion para ".$tipos_imputacion->fields["nombre"]."<br>") or fin_pagina();
	   	  }
	   	  //si existia y el monto guardado antes es diferente al monto pasado por post, o la provincia se modifico,
	   	  //entonces hay que actualizar
	   	  else if($_POST["hidden_ib_".$tipos_imputacion->fields["nombre"]."_$index"]!=$monto || $_POST["hidden_provincias_$index"]!=$id_distrito)
	   	  {
	   	    $query="update detalle_imputacion set monto=$monto,id_distrito=$id_distrito,usuario='".$_ses_user['name']."',fecha='$fecha' where id_detalle_imputacion=$id_detalle_imputacion";
	   	    sql($query,"<br>Error al actualizar el detalle de imputacion Nº $id_detalle_imputacion<br>") or fin_pagina();
	   	  }
	      $imp_id[$index]=$id_detalle_imputacion;
	   	 }//de if($_POST["ib_".$tipos_imputacion->fields["nombre"]."_$index"]!="")
	     $index++;

	   	}//de  while($_POST["ib_".$tipos_imputacion->fields["nombre"]."_".$index]!="")

	   	//por cada id de detalle imputacion, si no esta en el arreglo $imp_id, significa que antes estaba pero ahora
	   	//fue eliminado, por lo que hay que borrarlo efectivamente de la BD.
	   	$query="select id_detalle_imputacion from detalle_imputacion join tipo_imputacion using(id_tipo_imputacion)
	   	        where id_imputacion=$id_imputacion and tipo_imputacion.nombre='percepcion_ib'";
	   	$antes_guardados=sql($query,"<br>Error al traer los detalles de imputacion guardados<br>") or fin_pagina();

	   	$eliminar_perc_ib=array();
	   	$ind=0;
	   	while (!$antes_guardados->EOF)
	   	{
		   	 if(!in_array($antes_guardados->fields["id_detalle_imputacion"],$imp_id))
		   	 {
		   	  $eliminar_perc_ib[$ind]=$antes_guardados->fields["id_detalle_imputacion"];
		   	  $ind++;
		   	 }

		   	 $antes_guardados->MoveNext();
	   	}//de while(!$antes_guardados->EOF)

	   	if(sizeof($eliminar_perc_ib)>0)
	   	{//eliminamos las percepciones IB que no figuran mas en la tabla
	   	 $query="delete from detalle_imputacion where id_detalle_imputacion in(".implode(',',$eliminar_perc_ib).")";
	   	 sql($query,"<br>Error al eliminar Percepciones IB<br>") or fin_pagina();
	   	}

	  }//del else de if($tipos_imputacion->fields["nombre"]!="percepcion_ib")
	 }//de if($_POST["finalizar_sin_discriminar"]!=1 || ($_POST["finalizar_sin_discriminar"]==1 && $tipos_imputacion->fields["nombre"]=="monto_neto"))
   }//de if($_POST["chk_".$tipos_imputacion->fields["nombre"]]==1 || $tipos_imputacion->fields["nombre"]=="monto_neto")
   //si no esta chequeado el chk y existia el valor (estaba chequeado pero el usuario lo deschequeo), borramos la entrada
   else if($_POST["chk_".$tipos_imputacion->fields["nombre"]]=="" && ($_POST[$tipos_imputacion->fields["nombre"]]!="" || $_POST["ib_".$tipos_imputacion->fields["nombre"]."_0"]!=""))
   {
    //eliminamos para el id de imputacion pasado ($id_imputacion), todas las entradas de detalle_imputacion cuyo id es $id_tipo_imputacion
    $query="delete from detalle_imputacion where id_imputacion=$id_imputacion and id_tipo_imputacion=$id_tipo_imputacion";
    sql($query,"<br>Error al eliminar los detalles de imputacion con tipo: $id_tipo_imputacion<br>") or fin_pagina();

   }
   $tipos_imputacion->MoveNext();
 }//de while(!$tipos_imputacion->EOF)

 //si cargaron al menos una imputacion, se pone en estado "Por Controlar" (solo si no se especifico que se terminara directamente la imputacion)
 if($a_por_controlar && $_POST["terminar_directamente"]!=1)
 {
  //traemos el id del estado "Por Controlar"
  $query="select id_estado_imputacion from estado_imputacion where nombre='Por Controlar'";
  $estado_id=sql($query,"<br>Error al traer el id de estado de imputacion: Por Controlar<br>") or fin_pagina();
  $id_estado_controlar=$estado_id->fields["id_estado_imputacion"];

  $query="update imputacion set id_estado_imputacion=$id_estado_controlar where id_imputacion=$id_imputacion";
  sql($query,"<br>Error al actualizar el estado a Por Controlar<br>") or fin_pagina();

 }//de if($a_por_controlar)

 //si se eligio finalizar sin discriminar, borramos las imputacion previamente cargadas (excepto el monto neto)
 //(asi nos aseguramos que si antes habian guardado alguna, como se finaliza sin discriminar, no quede ninguna imputacion cargada erroneamente)
 if($_POST["finalizar_sin_discriminar"]==1)
 {
  //traemos el id del tipo de imputacion "monto_neto"
  $query="select id_tipo_imputacion from tipo_imputacion where nombre='monto_neto'";
  $neto_id=sql($query,"<br>Error al traer el id del tipo de imputacion de monto neto<br>") or fin_pagina();
  $id_imp_neto=$neto_id->fields["id_tipo_imputacion"];

  $query="delete from detalle_imputacion where id_imputacion=$id_imputacion and id_tipo_imputacion<>$id_imp_neto";
  sql($query,"<br>Error al eliminar los detalles de imputacion con tipo: $id_tipo_imputacion<br>") or fin_pagina();
 }//de if($_POST["finalizar_sin_discriminar"]==1)

 if($titulo_estado=="" && $a_por_controlar)
 	$titulo_estado="Por Controlar";
 elseif ($titulo_estado=="")
 	$titulo_estado="Pendiente";

 //registramos en el log la insercion de la imputacion
 $query="insert into log_imputacion (tipo,detalle,fecha,usuario,id_imputacion)
         values('$tipo_log_imputacion','$detalle_log_imputacion $titulo_estado','$fecha_hoy','".$_ses_user['name']."',$id_imputacion)";
 sql($query,"<br>Error al insertar el log de imputacion<br>") or fin_pagina();

 //si el monto neto y/o el monto total de la imputacion es menor que cero, enviamos un mail avisando
 if($monto_neto_negativo==1 || $_POST["monto_total"]<0)
 {
  $para="corapi@coradir.com.ar;juanmanuel@coradir.com.ar;noelia@coradir.com.ar";
  $asunto="Se imputó un pago cuyo monto total o monto neto es negativo";
  $texto="La imputación para el pago con $titulo_pago, se cargó con Monto Total y/o Monto Neto negativos.\nPor favor verifique que esto no se haya ocasionado por error, al cargar los datos.";
  $texto.="\n\n\nUsuario que generó esta imputación: ".$_ses_user["name"];

  enviar_mail($para,$asunto,$texto,"","","");
 }//de if($monto_neto_negativo==1 || $_POST["monto_total"]<0)

 $db->CompleteTrans();
}//function imputar_pago($pago,$estado="Pendiente",$id_imputacion="",$fecha="")


/***********************************************************************************
 Función que pone en cero todos los valores de la imputacion atada a un pago
 determinado. Esta es invocada cuando se anula un cheque o un débito
 @pago				Un arreglo que especifica el tipo de pago que se va a anular,
  					y el id correspondiente. En el caso de que el pago sea un cheque,
  					tambien tiene un campo adicional con el banco correspondiente
  					Ejemplos de este arreglo son:
  							$pago[]=array();
  							$pago["tipo_pago"]="id_ingreso_egreso";
  							$pago["id_pago"]=42;
  										o
  							$pago[]=array();
  							$pago["tipo_pago"]="númeroch";
  							$pago["id_pago"]=1378899512;
  							$pago["id_banco"]="4";
 					El tipo de pago puede ser:
 					 	-un egreso de caja (id_ingreso_egreso)
 						-un debito(iddébito)
 						-un cheque (idbanco,númeroch).
***********************************************************************************/
function anular_imputacion($pago)
{
	global $db,$_ses_user;
	$db->StartTrans();
	$id_pago=$pago["id_pago"];
	$id_banco=$pago["id_banco"];

	//traemos el id de la imputacion que debemos anular, dependiendo del tipo de pago
	switch ($pago["tipo_pago"])
	{
		case "id_ingreso_egreso":$query="select id_imputacion from contabilidad.imputacion where id_ingreso_egreso=$id_pago";
								$titulo_pago="Egreso de Caja Nº $id_pago";
								break;
		case "númeroch":		$query="select id_imputacion from contabilidad.imputacion where númeroch=$id_pago and idbanco=$id_banco";
								$titulo_pago="Cheque Nº $id_pago";
								//traemos el nombre del banco
			 	                $query_banco="select nombrebanco from tipo_banco where idbanco=$id_banco";
			 	                $bank=sql($query_banco,"<br>Error al traer el nombre del banco<br>") or fin_pagina();
			 	                $titulo_pago.=", del banco ".$bank->fields["nombrebanco"];
								break;
		case "iddébito":		$query="select id_imputacion from contabilidad.imputacion where iddébito=$id_pago";
								$titulo_pago="Débito Nº $id_pago";
								break;
		default:				die("Error Interno: No se pudo determinar el tipo de pago para el cual anular la imputación. Contacte a la División Software");
								break;
	}//de switch ($pago["tipo_pago"])

	//ejecutamos la consulta para obtener el id de imputacion
	$id_imp=sql($query,"<br>Error al obtener el id de la imputacion que se desea anular<br>") or fin_pagina();
	$id_imputacion=$id_imp->fields["id_imputacion"];

	//ponemos en cero todas las entradas que componen la imputcion, en la tabla detalle_imputacion
	$query="update contabilidad.detalle_imputacion set monto=0 where id_imputacion=$id_imputacion";
	sql($query,"<br>Error al anular la imputacion, en sus detalles<br>") or fin_pagina();

	//luego ponemos en estado "pago anulado" a la imputacion
	$query="select id_estado_imputacion from contabilidad.estado_imputacion where nombre='Pago Anulado'";
	$estado_imp=sql($query,"<br>Error al traer el id del estado anulado de la imputacion<br>") or fin_pagina();
	$id_estado_imputacion=$estado_imp->fields["id_estado_imputacion"];

	$query="update contabilidad.imputacion set id_estado_imputacion=$id_estado_imputacion
			where id_imputacion=$id_imputacion";
	sql($query,"<br>Error al actualizar el estado de la imputacion<br>") or fin_pagina();

	$fecha=date("Y-m-d H:i:s");
	//registramos en el log la anulacion de la imputacion
 	$query="insert into log_imputacion (tipo,detalle,fecha,usuario,id_imputacion)
 	        values('anulación','Anulación de la imputación para el pago con $titulo_pago','$fecha','".$_ses_user['name']."',$id_imputacion)";
    sql($query,"<br>Error al insertar el log de imputacion<br>") or fin_pagina();

	$db->CompleteTrans();
}//de function anular_imputacion($pago)