/*
AUTOR: MAC
FECHA: 11/05/05

$Author: marco_canderle $
$Revision: 1.10 $
$Date: 2006/01/04 08:05:32 $
*/

//ventana de cambios de productos para una fila
var vent_cambio_prod=new Object();
vent_cambio_prod.closed=true;

//controla que la fecha pasada como parametro (de entrega o de facturacion u otra)
//no sea menor que la fecha de creacion de la OC.
function check_fecha_legal(fecha_control)
{fecha_creacion=document.all.fecha_creacion.value;

 fecha_1=new String();
 fecha_1=fecha_control.value;
 fecha=new Array();
 fecha=fecha_1.split("/");
 fecha_ent=fecha[2]+"-"+fecha[1]+"-"+fecha[0];
 if(fecha_ent<fecha_creacion)
  return 0;
 else
  return 1;
}

//esto es para truncar los flotantes a x posiciones
//la condicion es para saber si la funcion esta definida para la clase Number
if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}

function total()
{
	var total=0;
	 for (var i=0; i < document.form1.length ; i++)
	 {
		  if (document.form1.elements[i].name.indexOf("subtotal_")!=-1)
		  {
			 var subtotal=document.form1.elements[i];
		  	 var id_item=subtotal.name.substring(subtotal.name.indexOf("_")+1,subtotal.name.lenght);
			 var cantidad=eval("document.all.cant_"+id_item);
			 var unitario=eval("document.all.unitario_"+id_item);
			 total+=parseFloat(subtotal.value);
		  }
	 }
	var t1= new String(total.toFixed(2));
	if (t1.indexOf(".")==-1)
		document.all.total.value=t1+".00";
	else
	  document.all.total.value=t1;
}

//calcula el subtotal dependiendo de la cantidad
function calcular(textfield)
{
 id_item=textfield.name.substring(textfield.name.indexOf("_")+1,textfield.name.lenght);

 var subtotal=eval("document.all.subtotal_"+ id_item);

 var precio=eval("document.all.unitario_"+ id_item);
 var cantidad=eval("document.all.cant_"+ id_item);
 //var num_fila=parseFloat(id_item) + 1;

// if ((control_numero(cantidad,"Cantidad en la fila "+ num_fila) == 0) && (control_numero(precio,"Precio Unitario en la fila "+ num_fila)) ==0 ) {
 if ((control_numero(cantidad,"Cantidad del producto") == 0) && (control_numero(precio,"Precio Unitario del Producto")) ==0 ) {

 if ( 0 >= precio.value.indexOf(','))
 {
   precio.value[precio.value.indexOf(',')]='.'; //entra pero no lo cambia
 }
 var t1= new String((cantidad.value*parseFloat(precio.value)).toFixed(2));
 if (t1.indexOf(".")==-1)
		 subtotal.value=t1+".00";
 else
	   subtotal.value=t1;

 total();
 }
}

/**************************************************************
 FUNCIONES QUE USA LA VENTANA HIJO
 NOTA:
		LAS FUNCIONES TOMAN EL CONTEXTO DONDE SON DEFINIDAS
		OJO CON EL ACCESO A LAS VARIABLES
***************************************************************/
//variable que contiene la ventana para selecciona el cliente
var wcliente=0;
var wpagar=0;

function cargar_cliente()
{
 document.all.id_cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 //document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
 document.all.cliente.value=wcliente.document.all.nbrecl.value;
 if (wcliente.document.all.chk_direccion.checked)
	document.all.entrega.value=wcliente.document.all.direccion.value;
//////////////BROGGI - para llevar control de la entidad mas usada por cada usuario
 document.all.cambio_entidad.value="si_cambio";

}//de function cargar_cliente()

function generar_select_posad(index)
{


	 document.all.codigo_select_posad.value=buffer;
}//de function generar_select_posad(index)

//variable que contiene la ventana hijo productos
var wproductos=0;
function cargar()
{
 var largo,i=0,prod,insertar_ok=1;


  //controlamos que no este ya insertado el producto con proveedor, antes de insertar
  largo=document.all.items.value;
  for(i;i<largo;i++)
  {
    prod=eval("document.all.idp_"+i);
    //if(wproductos.document.all.select_producto.options[wproductos.document.all.select_producto.options.selectedIndex].text!="**")
    if(wproductos.document.all.nombre_producto_elegido.value!="**")
    //radio_idprod
    {

      if(typeof(prod)!='undefined' && prod.value==wproductos.document.all.id_producto_seleccionado.value)
      {
       alert('El producto seleccionado ya fue insertado para esta '+titulo_pagina+'.\nNo se puede volver a insertar un producto que ya eligió antes.');
       insertar_ok=0;
      }
    }//de if(document.all.controlar_siempre.value=="si")
    else
    {if(document.all.controlar_siempre.value=="si")
     {
      //if(typeof(prod)!='undefined' && prod.value==wproductos.document.all.select_producto.value)
      if(typeof(prod)!='undefined' && prod.value==wproductos.document.all.id_producto_seleccionado.value)
      {
       alert('El producto seleccionado ya fue insertado para esta '+titulo_pagina+'.\nNo se puede volver a insertar un producto que ya se agregó antes.');
       insertar_ok=0;
      }
     }//de if(document.all.controlar_siempre.value=="si")
    }//del else de if(document.all.controlar_siempre.value=="si")
  }//de  for(i;i<largo;i++)

  if (insertar_ok)
  {

   if (wproductos.document.all.nombre_producto_elegido.value=="**")
   {
 	 if (!(document.all.es_lic.value!='0' || document.all.es_pres.value!='0' ||
  	       document.all.es_st.value!='0' || document.all.es_cas.value!='0' ||
  	       document.all.flag_stock.value!='0' || document.all.es_rma.value!='0')
  	    )
  	 {
	   insertar_ok=1;
  	 }
	 else
	 {
	 	   insertar_ok=0;
	 	   alert ('No se puede agregar el producto ** a este tipo de Orden')
	 }
   }//de if (wproductos.document.all.descripcion.value=="**")
  }//de if (insertar_ok)

  if (insertar_ok)
  {
   //Para insertar una fila
   var items=document.all.items.value++;
   var nro_celda=0;
   //si la OC no es internacional

   if(document.all.internacional.value!=1)
   {
    //inserta al final
    var fila=document.all.productos.insertRow(document.all.productos.rows.length );
   }
   else
   {
     //inserta en un lugar anterior al ultimo
   	 var fila=document.all.productos.insertRow(document.all.productos.rows.length-1);
   }

   fila.insertCell(nro_celda).innerHTML="<div align='center'>"+
   "<input name='chk' type='checkbox' id='chk' value='1'></div><input type='hidden' name='idp_"+
   items +"' value='"+ wproductos.document.all.id_producto_seleccionado.value+"'>";
   //items +"' value='"+ wproductos.document.all.select_producto.value+"'>";
   nro_celda++;

   fila.insertCell(nro_celda).innerHTML="<input type='hidden' value='' name='h_desc_"+items+"'><input type='hidden' value='"+
   wproductos.document.all.nombre_producto_elegido.value +"' name='desc_orig_"+items+"'>"+
   "<textarea name='desc_"+items +"' style=\"width:90%\" rows='1' wrap='VIRTUAL' id='descripcion' readonly>"+
   wproductos.document.all.nombre_producto_elegido.value +"</textarea><input type='button' name='desc_adic_"+
   items +"' value='E' title='Agregar descripción adicional del producto' onclick=\"window.open('desc_adicional.php?posicion="+items+"','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')\">";
   nro_celda++;
   /***********************************************
    Generacion el select de POSAD
   ************************************************/
   if(document.all.internacional.value==1)
   {
    //generar_select_posad(items);alert(document.all.codigo_select_posad.value);
    var posad_data,posad_generados=1,ind=1,buff;
	 buff="<select name='select_posad_"+items+"' onchange='set_montos_fila_oc_internacional()'>"+
	                              "<option value='-1' selected>Seleccione...</option>";
	 while(posad_generados<cantidad_posad)//cantidad_posad se declara en ord_compra.php
	 {

	   //si posad esta definido, agregamos un option al select e incrementamos la cantidad de posads generados
	   if(typeof(eval("posad_"+ind))!="undefined")
	   {
	    posad_data=eval("posad_"+ind);//posad_... se declara en ord_compra.php
	    posad_generados++;
	    buff+="<option value='"+ind+"'>";
	    buff+=eval("posad_data['codigo_ncm']");
	    buff+="</option>";
	   }
	   ind++;
	 }//de while(posad_generados<cantidad_posad)

	 buff+="</select> <input type='button' name='detalle_posad_"+items+"' title='Seleccionar Posición Aduanera' value='+' onclick='window.open(\"listado_posad.php?indice_select="+items+"\",\"\",\"toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=200,top=120,width=700,height=300\")'>";

     fila.insertCell(nro_celda).align="center";
     fila.cells[nro_celda].innerHTML=buff;
     nro_celda++;
     var set_montos_script="set_montos_fila_oc_internacional();";
   }//de if(document.all.internacional.value==1)
   else
     var set_montos_script="";
  /***********************************************
   Fin de Generacion el select de POSAD
  ************************************************/

   fila.insertCell(nro_celda).align="center";
   fila.cells[nro_celda].innerHTML="<input name='cant_"+
   items+"' type='text' id='cantidad' size='6' value='1' style='text-align:right' "+
   "onchange='calcular(this);"+set_montos_script+"' >";
   nro_celda++;

   fila.insertCell(nro_celda).align="right";
   fila.cells[nro_celda].innerHTML="<input name='unitario_"+
   items+"' type='text' id='unitario' size='10' style='text-align:right' value='"+
   wproductos.document.all.precio_producto_elegido.value +"' "+
   "onchange='this.value=this.value.replace(\",\",\".\");calcular(this);"+set_montos_script+"'> "+
   "<input type='button' name='H_button' value='H' title='Historial del Precio' onclick='window.open(\"../licitaciones/historial_comentarios.php?id_producto="+wproductos.document.all.id_producto_seleccionado.value+"\",\"\",\"toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520\")'>";
   nro_celda++;

   fila.insertCell(nro_celda).align="right";
   fila.cells[nro_celda].innerHTML="<input name='subtotal_"+
   items+"' type='text' readonly id='subtotal' size='10' style='text-align:right' value='"+
   wproductos.document.all.precio_producto_elegido.value +"'>";
   nro_celda++;

   if(document.all.internacional.value==1)
   {
   	 fila.insertCell(nro_celda).align="center";
     fila.cells[nro_celda].innerHTML="<input type='text' name='proporcional_flete_"+items+"' readonly style='text-align:right' value='' size='8' onchange='calcular_monto_total(0,total_proporcional_flete);'>";
     nro_celda++;

     fila.insertCell(nro_celda).align="center";
     fila.cells[nro_celda].innerHTML="<input type='text' name='base_imponible_cif_"+items+"' readonly style='text-align:right' value='' size='8' onchange='calcular_monto_total(1,total_base_imponible_cif);'>";
     nro_celda++;

     fila.insertCell(nro_celda).align="center";
     fila.cells[nro_celda].innerHTML="<input type='text' name='derechos_"+items+"' readonly style='text-align:right' value='' size='8' onchange='calcular_monto_total(2,total_derechos);'>";
     nro_celda++;

     fila.insertCell(nro_celda).align="center";
     fila.cells[nro_celda].innerHTML="<input type='text' name='iva_"+items+"' readonly style='text-align:right' value='' size='8' onchange='calcular_monto_total(3,total_iva);'>";
     nro_celda++;

     fila.insertCell(nro_celda).align="center";
     fila.cells[nro_celda].innerHTML="<input type='text' name='ib_"+items+"' readonly style='text-align:right' value='' size='8' onchange='calcular_monto_total(4,total_ib);'>";
     nro_celda++;
   }//de if(document.all.internacional.value==1)

   if (document.all.boton_eliminar.disabled)
	document.all.boton_eliminar.disabled=0;

   document.all.guardar.value++;
   total();

   alert("Se agregó una nueva fila para la Orden de Compra.");
   wproductos.focus();

  }//de if(insertar_ok)
  else
  {
   // alert('No se puede seleccionar este Producto para este tipo de Orden');
   wproductos.focus();
  }
}//de function cargar()

/*************************************
Similar a cargar() pero para stock
**************************************/
function cargar_stock()
{
 var largo,i=0,prod,prov,insertar_ok=1;

 //controlamos que no este ya insertado el producto con proveedor, antes de insertar
 largo=document.all.items.value;
  for(i;i<largo;i++)
  {
    prod=eval("document.all.id_p_esp_"+i);
    if(wproductos.document.all.name_producto.value!="**")
    {
     if(typeof(prod)!='undefined' && prod.value==wproductos.document.all.id_prod_esp.value)
     {
      alert('El producto seleccionado ya fue insertado para esta '+titulo_pagina+'.\nNo se puede volver a insertar.');
      insertar_ok=0;
     }
    }//de if(wproductos.document.all.select_producto.options.....
    else
    {if(document.all.controlar_siempre.value=="si")
     {if(typeof(prod)!='undefined' && prod.value==wproductos.document.all.id_prod_esp.value)
       { alert('El producto seleccionado ya fue insertado para esta '+titulo_pagina+'.\nNo se puede volver a insertar.');
         insertar_ok=0;
       }
     }//de if(document.all.controlar_siempre.value=="si")
    }//del else

  }//de  for(i;i<largo;i++)

  if(insertar_ok)
  {
   if (wproductos.document.all.name_producto.value=="**")
   {
   	 if (!(document.all.es_lic.value!='0' || document.all.es_pres.value!='0' ||
  	       document.all.es_st.value!='0' || document.all.es_cas.value!='0' ||
  	       document.all.flag_stock.value!='0' || document.all.es_rma.value!='0')
  	    )
  	 {
	  insertar_ok=1;
  	 }
	 else
	 {
	 	   insertar_ok=0;
	 	   alert ('No se puede agregar el producto ** a este tipo de Orden')
	 }
   }//de if (wproductos.document.all.name_producto.value=="**")
  }//de if(insertar_ok)

  if(insertar_ok)
  {
   //Para insertar una fila
   var items=document.all.items.value++;
   //inserta al final
   var fila=document.all.productos.insertRow(document.all.productos.rows.length );
   //inserta al principio
   //var fila=document.all.productos.insertRow(1);

   fila.insertCell(0).innerHTML="<div align='center'> <input name='chk' type='checkbox' id='chk' value='1'></div><input type='hidden' name='id_p_esp_"+
   items +"' value='"+ wproductos.document.all.id_prod_esp.value+"'>";

   fila.insertCell(1).innerHTML="<input type='hidden' value='' name='h_desc_"+items+"'><input type='hidden' value='"+
   wproductos.document.all.name_producto.value +"' name='desc_orig_"+items+"'>"+
   "<textarea name='desc_"+ items +"' style=\"width:90%\" rows='1' wrap='VIRTUAL' id='descripcion' readonly>"+
   wproductos.document.all.name_producto.value +"</textarea><input type='button' name='desc_adic_"+items+
   "' value='E' title='Agregar descripción adicional del producto' onclick=\"window.open('desc_adicional.php?posicion="+items+"','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')\">";

   fila.insertCell(2).innerHTML="<div align='center'> <input name='cant_"+
   items+"' readonly type='text' id='cantidad' size='6' value='"+wproductos.document.all.cant_reserv.value+"' style='text-align:right' "+
   "onchange='calcular(this)' ></div>";

   fila.insertCell(3).innerHTML="<div align='center'> <input name='unitario_"+
   items+"' type='text' id='unitario' size='10' style='text-align:right' value='"+
   wproductos.document.all.precio_producto.value +"' "+
   "onchange='this.value=this.value.replace(\",\",\".\");calcular(this)'> "+
   "<input type='button' name='H_button' value='H' title='Historial del Precio' onclick='window.open(\"../licitaciones/historial_comentarios.php?id_producto="+wproductos.document.all.id_prod_esp.value+"\",\"\",\"toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520\")'>"+
   "</div> ";

   fila.insertCell(4).innerHTML="<div align='center'> <input name='subtotal_"+
   items+"' type='text' readonly id='subtotal' size='12' style='text-align:right' value='"+
   wproductos.document.all.precio_producto.value +"'></div>";

   if (document.all.boton_eliminar.disabled)
	document.all.boton_eliminar.disabled=0;

   document.all.guardar.value++;
   total();

   //si estamos en modo Orden de Servicio tecnico, entonces bloqueamos el boton de Autorizar, si es que existe en el formulario
   if(document.all.modo.value=="oc_serv_tec")
   {
    if(typeof(document.all.boton_autorizar)!="undefined")
    {
      document.all.boton_autorizar.disabled=1;
    }

   }//de if(document.all.modo.value=="oc_serv_tec")

   alert("Se agregó una nueva fila para la Orden de Compra.");
   wproductos.focus();

   return 1;
  }//de if(insertar_ok)
  else
  {
   //alert('No se puede seleccionar este Producto para este tipo de Orden');
   wproductos.focus();
   return 0;
  }
}//de function cargar_stock()


/************************************
Agregar una fila para el transporte
*************************************/
var pos_trans;
function agregar_trans()
{
  //Para insertar una fila
  var items=document.all.items.value++;
  pos_trans=items;
  //inserta al final
  var fila=document.all.productos.insertRow(document.all.productos.rows.length);
  var a="";

  fila.insertCell(0).innerHTML="<div align='center'><input name='chk' type='checkbox' readonly id='chk' value='1'></div><input type='hidden' name='idp_"+
  items +"' value='-2'>";

  fila.insertCell(1).innerHTML="<input type='hidden' value='' name='h_desc_"+items+"'><input type='hidden' value='Conexo:'"+
  a+" name='desc_orig_"+items+"'>"+
  a+"<textarea name='desc_"+items +"' style=\"width:90%\" rows='1' wrap='VIRTUAL' id='descripcion' readonly>"+
  a+"Conexo:</textarea><input type='button' name='desc_adic_"+
  items +"' value='E' title='Agregar descripción adicional del producto' onclick=\"window.open('desc_adicional.php?posicion="+items+"','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')\">";

  fila.insertCell(2).innerHTML="<div align='center'> <input name='cant_"+
  items+"' type='text' readonly id='cantidad' size='6' value='1' style='text-align:right' "+
  "onchange='calcular(this)' ></div>";

  fila.insertCell(3).innerHTML="<div align='left'> <input name='unitario_"+
  items+"' type='text' id='unitario' size='10' style='text-align:right' value='0.00'"+
  a+"onchange='this.value=this.value.replace(\",\",\".\");calcular(this)'> "+
  "<input type='button' name='H_button' value='H' title='Historial del Precio' onclick='window.open(\"../licitaciones/historial_comentarios.php?id_producto=-1\",\"\",\"toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520\")'>"+
  "</div> ";

  fila.insertCell(4).innerHTML="<div align='center'><input name='subtotal_"+
  items+"' type='text' readonly id='subtotal' size='10' style='text-align:right' value='0.00'"+
  a+"'></div>";

  //usamos el boton de agregar transporte para transformarlo al boton de
  //guardar los cambios hechos.
  document.all.agregar_transporte.disabled=1;
  document.all.guardar_transporte.style.visibility="visible";

}//de function agregar_trans()

/*************************************************************************
Control del boton guardar_transporte
@no_mostrar_confirm solo se usa para especificar que no muestre el confirm
                de 'Ha ingresado un precio negativo en el Conexo agregado'
                Si no se necesita, no hay que pasarlo (asi muestra dicho confirm)
**************************************************************************/
function control_trans(no_mostrar_confirm)
{ var precio_u,porcentaje,precio_limite;
  //controlamos que el precio sea distinto de 0 y que lo hayan completado
  precio_u=parseFloat(eval("document.all.unitario_"+pos_trans+".value"));
  if(precio_u=="" || precio_u==0)
  {alert('El precio ingresado no es válido');
   return false;
  }
  //si el precio es mayor que 0 controlamos que la cantidad ingresada sea menor que el 10%
  //de la compra o a 30 (el precio limite es el mayor de los dos)
  //Pero si el precio es menor que 0 no hacemos este control
  else if(precio_u>0)
  {

   //calculamos el 10% de la compra
   porcentaje=0.10*(parseFloat(document.all.total.value)-precio_u);
   if(porcentaje<=30)
    precio_limite=30;
   else
    precio_limite=porcentaje;

   //si el precio del conexo es mayor que el precio limite, damos alerta
   if(precio_u>precio_limite)
   {alert('El precio ingresado es mayor que el permitido ('+formato_BD(precio_limite)+').');
    return false;
   }
  }//de else if(precio_u>0)
  else//el precio es negativo
  {
   if(typeof(no_mostrar_confirm)=="undefined")
   {if(confirm("Ha ingresado un precio negativo en el Conexo agregado.\n¿Está seguro que desea continuar?"))
  	 return true;
  	else
  	 return false;
   }
  }//de else//el precio es negativo

  return true;
}//de function control_trans(no_mostrar_confirm)

/*******************************************************************
Devuelve true si el nombre pasado es un stock, y false en otro caso
********************************************************************/
function es_stock_js(nbre_prov)
{
 if(nbre_prov.substring(0,5)=="Stock")
  return 1;
 else
  return 0;
}//de function es_stock_js(nbre_prov)

function nuevo_item(links_stock)
{var pagina_prod;
 var nbre_prov;
 var id_prov;
 var stock_page;
 if (wproductos==0 || wproductos.closed)
 {
	//si la OC no es Orden de Servicio Tecnico, le damos el nombre del proveedor desde el select_proveedor
	if(document.all.modo.value!="oc_serv_tec")
	 nbre_prov=document.all.select_proveedor[document.all.select_proveedor.selectedIndex].text;
	else if(document.all.modo.value=="oc_serv_tec")//si es una Orden de Servicio Tecnico, le damos el nombre previamente guardado
	 nbre_prov=document.all.nombre_proveedor.value;

	id_prov=document.all.id_proveedor_a.value;
    /*si el nombre del proveedor empieza con la palabra 'Stock' entonces
      los productos a seleccionar deben ser solo los que esten en ese stock seleccionado*/
    if(es_stock_js(nbre_prov))
    {    switch(nbre_prov)
         {
          case "Stock San Luis": pagina_prod=links_stock['san luis'];
                         break;
          case "Stock Buenos Aires": pagina_prod=links_stock['buenos aires'];
                         break;
          case "Stock New Tree": pagina_prod=links_stock['new tree'];
                         break;
          case "Stock ANECTIS": pagina_prod=links_stock['anectis'];
                         break;
          case "Stock SICSA": pagina_prod=links_stock['sicsa'];
                         break;
          case "Stock Serv. Tec. Bs. As.": pagina_prod=links_stock['st_ba'];
                         break;
         }//de switch(nbre_prov)

    }//de if(es_stock_js(nbre_prov))
    //En otro caso funciona como es usual, trayendo todos los productos cargados
    else
    {
        pagina_prod=links_stock["no_stock"];
    }

    //abrimos la pagina para elegir los productos (ya sea un stock o la pagian de productos generales
    wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,resizable=1,status=0, menubar=0,scrollbars=1,left=25,top=10,width=950,height=500');
 }//de if (wproductos==0 || wproductos.closed)
 else
  if (!wproductos.closed)
   wproductos.focus();
}//de function nuevo_item()

/*************************************************/
function borrar_items()
{
 var i=0;
 while(typeof(document.all.chk)!='undefined' && typeof(document.all.chk.length)!='undefined' && i < document.all.chk.length)
 {
   //Para borrar una fila
  if (typeof(document.all.chk[i])!='undefined' && document.all.chk[i].checked)
   document.all.productos.deleteRow(i+1);
  else
  	i++;
 }

 if(typeof(document.all.chk)!='undefined' && document.all.chk.checked)
 {
   document.all.productos.deleteRow(1);
   document.all.boton_eliminar.disabled=1;
 }
 else if (typeof(document.all.chk)=='undefined')
   document.all.boton_eliminar.disabled=1;

 total();
}//de function borrar_items()

/**********************************************************/
//funciones para busqueda abreviada utilizando teclas en la lista que muestra los clientes.
var digitos=10; //cantidad de digitos buscados
var puntero=0;
var buffer=new Array(digitos); //declaración del array Buffer
var cadena="";

function buscar_op_submit(obj)
{
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos)
   {
       cadena="";
       puntero=0;
   }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13)
   {
       borrar_buffer();
	 	 document.all.refresh.value=1;
		 form1.action='ord_compra.php';
		 form1.submit();


   }//de if (event.keyCode == 13)
   //sino busco la cadena tipeada dentro del combo...
   else
   {
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       //en el indice cero la opcion no es valida
       for (var opcombo=1;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }//del else de if (event.keyCode == 13)
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}//de function buscar_op_submit(obj)

function onchange_proveedor(object)
{
 document.all.refresh.value=1;
 if(es_stock_js(object.options[object.options.selectedIndex].text))
  document.all.borrar_filas_stock.value=1;
 else
  document.all.borrar_filas_stock.value=0;
 form1.action='ord_compra.php';
 form1.submit();
}//de function onchange_proveedor(object)

function montos_confirm()
{
 if(newConfirm("Ordenes de Compra","¿Desea especificar los montos de los pagos?",1,1,0))
 {document.all.destino_para_autorizar.value="ord_compra_pagar.php";
 }
 else
 {document.all.destino_para_autorizar.value="ord_compra_listar.php";
 }
}//de function montos_confirm()

function control_pagos()
{
 var total_monto;
 var total_pagos;
 var total_nc;
 var aux;
 var confirmacion;

 if(document.all.internacional.value!=1)
  total_monto=parseFloat(document.all.total.value);
 else
  total_monto=parseFloat(document.all.total_global.value);

 total_pagos=parseFloat(document.all.montos_pagos.value);
 total_nc=parseFloat(document.all.montos_nc.value);

 aux=(total_monto-total_nc)-total_pagos;
 //alert('Total a pagar '+total_monto+' Total NC '+total_nc+' Total Pagos '+total_pagos+' Diferencia '+aux);
 //dependiendo del caso setea un valor
 if (total_pagos==0)
 {
  //aca no pregunta nada y hace la division
  document.all.destino_para_autorizar.value="ord_compra_listar.php";
  document.all.accion.value="dividir";
  return true;
 }//de if (total_pagos==0)
 else
 {
  if (((aux<=0.10)&&(aux>=0)) || ((aux>=-0.10)&&(aux<=0)))
  {
   document.all.destino_para_autorizar.value="ord_compra_listar.php";
   document.all.accion.value="nada";
   return true;
  }
  else
  {
   //si es distinto pregunta
   //si dice que si que siga, seguro que hay que mandar mail
   //si dice que no se tiene que quedar en la pagina
   confirmacion=confirm('Advertencia - Los valores ingresados no se corresponden con el total, Desea Seguir?');
   if (confirmacion==1)
   {
    document.all.destino_para_autorizar.value="ord_compra_listar.php";
    document.all.accion.value="nada";
    return true;
   } //fin del then del tercer if
   else
   {
    document.all.destino_para_autorizar.value="ord_compra_listar.php";
    document.all.accion.value="nada";
    return false;
   }//del else
  } //del else que controla el rango de error con los montos
 }//fin del else de if (total_pagos==0)

 return false;
}//de function control_pagos()

function cuerpo()
{
 var retorno;
 retorno=control_pagos();

 if (retorno==1)
 {
  var valor;
  valor=prompt("Ingrese el texto a enviar en el mail","");
  //montos_confirm();
  if (!valor)
   return false;
  document.all.contenido.value=valor;
  return true;
 }
 else
  return false;

}//de function cuerpo()


//--------------------------------------------------------
//FUNCION PARA CHECKEAR LOS CAMPOS
var msg;
function chk_campos()
{
 var ret_value=0;
 var long_prod=0;  //cantidad de productos agregados

 if (typeof(document.all.chk) !='undefined')
 {
	if (typeof(document.all.chk.length) !='undefined')
	  long_prod=document.all.chk.length;
	else long_prod=1;
 }

 if(check_fecha_legal(document.all.fecha_entrega)==0)
 {
  msg="La Fecha de Entrega de la "+titulo_pagina+" es MENOR a la Fecha de Creación de la misma.\n";
  ret_value++;
  return ret_value;
 }

 if(typeof(document.all.fecha_facturacion)!="undefined" && typeof(document.all.cuenta_corriente)!="undefined" && document.all.cuenta_corriente.checked==1)
 {if(check_fecha_legal(document.all.fecha_facturacion)==0)
  {
   msg="La Fecha de Facturación de la "+titulo_pagina+" es MENOR a la Fecha de Creación de la misma.\n";
   ret_value++;
   return ret_value;
  }
 }//de if(typeof(document.all.fecha_facturacion)!="undefined")

 msg="---------------------------------------------------------------------\t\n";
 msg+="Falta Completar:\n\n";


 document.all.valor_dolar.value=document.all.valor_dolar.value.replace(',','.');

 if (document.all.fecha_entrega.value=='')
 {
  msg+="\tFecha de Entrega\n";
  ret_value++;
 }

 if(typeof(document.all.cuenta_corriente)!="undefined")
 {if (document.all.cuenta_corriente.checked==1 && document.all.fecha_facturacion.value=='')
  {
   msg+="\tFecha de Facturación\n";
   ret_value++;
  }
 }

 //obviamos estos controles si es una Orden de Servicio Tecnico
 if(document.all.modo.value!="oc_serv_tec")
 {if (document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value==-1)
  {
   msg+="\tNombre del Proveedor\n";
   ret_value++;
  }
  if (document.all.select_contacto[document.all.select_contacto.selectedIndex].value==-1)
  {
   msg+="\tNombre del Contacto\n";
   ret_value++;
  }
 }//de if(document.all.modo.value!="oc_serv_tec")

 //Si la OC es internacional, controlamos que llenen los campos obligatorios
 if(document.all.internacional.value==1)
 {
   if(document.all.direccion_proveedor.value=="")
   {
    msg+="\tDirección del Proveedor\n";
    ret_value++;
   }
   if(document.all.banco_proveedor.value=="")
   {
    msg+="\tBanco del Proveedor\n";
    ret_value++;
   }
   if(document.all.dir_banco_proveedor.value=="")
   {
    msg+="\tDirección del Banco del Proveedor\n";
    ret_value++;
   }
   if(document.all.swift_proveedor.value=="")
   {
    msg+="\tSwift del Proveedor\n";
    ret_value++;
   }
   if(document.all.id_despachante.value=="")
   {
    msg+="\tDatos del Despachante\n";
    ret_value++;
   }
   if(document.all.tipo_flete.value=="")
   {
    msg+="\tTipo de Flete\n";
    ret_value++;
   }
   if(document.all.monto_flete.value=="")
   {
    msg+="\tMonto de Flete y Seguro\n";
    ret_value++;
   }
   if(document.all.honorarios_gastos.value=="")
   {
    msg+="\tHonorarios y Gastos\n";
    ret_value++;
   }
 }//de if(document.all.internaciona.value==1)

  //obviamos estos controles si es una Orden de Servicio Tecnico
 if(document.all.modo.value!="oc_serv_tec")
 {if (document.all.cliente.value=='' ||
 	  document.all.cliente.value=='Haga click en la palabra cliente para ver la lista')
  {
   msg+="\tInformacion del cliente\n";
   ret_value++;
  }

  if (document.all.select_pago[document.all.select_pago.selectedIndex].value==-1)
  {//if(document.all.es_stock.value==0)
  if(document.all.select_pago.disabled==0)
   {msg+="\tForma de Pago\n";
    ret_value++;
   }
  }//de if (document.all.select_pago[document.all.select_pago.selectedIndex].value==-1)

  if (document.all.select_moneda[document.all.select_moneda.selectedIndex].value==-1)
  {msg+="\tTipo de Moneda\n";
   ret_value++;
  }
 }//de if(document.all.modo.value!="oc_serv_tec")

 if (typeof(document.all.proveedor_reclamo)!="undefined" && document.all.proveedor_reclamo[document.all.proveedor_reclamo.selectedIndex].value==-1)
 {
  msg+="\tProveedor de productos que generan el RMA\n";
  ret_value++;
 }

 //obviamos estos controles si es una Orden de Servicio Tecnico
 if(document.all.modo.value!="oc_serv_tec")
 {
  if(document.all.select_moneda[document.all.select_moneda.selectedIndex].text=='Dólares' && (isNaN(document.all.valor_dolar.value)|| document.all.valor_dolar.value==0))
  {
   msg+="\tEl valor del dolar debe ser un número valido\n";
   ret_value++;
  }

  if(document.all.select_moneda[document.all.select_moneda.selectedIndex].text=='Dólares' && document.all.valor_dolar.value=="")
  {
   msg+="\tEl valor del dolar\n";
   ret_value++;
   }
 }//de if(document.all.modo.value!="oc_serv_tec")

 var cant_veces=0;
 var i=0;
 while (cant_veces < long_prod)
 {
  c=eval("document.all.cant_"+i);
  p=eval("document.all.unitario_"+i);
  posad=eval("document.all.select_posad_"+i);
  var fila=cant_veces+1
  if (typeof(c)!='undefined' && typeof(p)!='undefined')
  {
   if (c.value=="" || isNaN(c.value))
   {
    msg+="\tIngresar un número valido para el campo Cantidad en la fila " + fila +" \n";
    ret_value++;
   }
   if (p.value=="" || isNaN(p.value))
   {
    msg+="\tIngresar un valor valido para el campo Precio Unitario en la fila " + fila +" \n";
    ret_value++;
   }
   //Si la OC es internacional, controlamos que llenen los campos para la fila
   if(document.all.internacional.value==1)
   {
    if(posad[posad.selectedIndex].value==-1)
    {
     msg+="\tElegir el POSAD para la fila " + fila +" \n";
     ret_value++;
    }
   }//de if(document.all.internacional.value==1)
   cant_veces++;   //cantidad de checkbox
  }//de if (typeof(c)!='undefined' && typeof(p)!='undefined')
  i++;  //nombre que corresponde al nombre de los campos cant_ y unitario_
 }//de while (cant_veces < long_prod)

 if (ret_value < 27)
	msg+="-------------------------------------------------------------------\t";
 else
  msg+="----------------------------------------------------------------------\t";

 return ret_value;
}//de function chk_campos()

/***********************************************************
 Muestra, oculta la fecha de facturacion.
 @check  es el checkbox que decide si se muestra o se oculta
 		 la fecha de facturacion
************************************************************/
function mostrar_fecha_facturacion(check)
{
 if(check.checked==1)
  document.all.tr_fecha_facturacion.style.display="block";
 else
  document.all.tr_fecha_facturacion.style.display="none";

}//de function mostrar_cuenta_corriente()


//muestra los botones para eliminar fila si la OC esta en estado 'a', 'e' ,'d' o 'g'
//tambien habilita el campo cantidad y el precio unitario para que lo cambien
//(y el respectivoboton de guardar esos cambios)
function habilitar_cambios_especiales()
{var i,boton_del,cant,precio,nbre_prov,es_stock;

 //si la OC no es Orden de Servicio Tecnico, le damos el nombre del proveedor desde el select_proveedor
 if(document.all.modo.value!="oc_serv_tec")
  nbre_prov=document.all.select_proveedor[document.all.select_proveedor.selectedIndex].text;
 else if(document.all.modo.value=="oc_serv_tec")//si es una Orden de Servicio Tecnico, le damos el nombre previamente guardado
  nbre_prov=document.all.nombre_proveedor.value;
 if(es_stock_js(nbre_prov))
  es_stock=1;
 else
  es_stock=0;

 for(i=0;i<document.all.items.value;i++)
 {
  if(document.all.internacional.value==1)
  {posad=eval("document.all.select_posad_"+i);
   boton_posad=eval("document.all.detalle_posad_"+i);
  }
  boton_del=eval("document.all.borrar_fila_"+i);
  cant=eval("document.all.cant_"+i);
  precio=eval("document.all.unitario_"+i);

  if(document.all.check_habilitar_cambios.checked==0)
  {boton_del.style.visibility='hidden';
   if(!es_stock)
   {cant.disabled=1;
    precio.disabled=1;
    if(document.all.internacional.value==1)
    {posad.disabled=1;
     boton_posad.disabled=1;
    }
   }
  }
  else
  {boton_del.style.visibility='visible';
   if(!es_stock)
   {cant.disabled=0;
    precio.disabled=0;
    if(document.all.internacional.value==1)
    {posad.disabled=0;
     boton_posad.disabled=0;
    }
   }
  }
 }//de for(i=0;i<document.all.items.value;i++)
 if(!es_stock)
 {if(document.all.check_habilitar_cambios.checked==0)
   document.all.guardar_cambios_fila.style.visibility='hidden';
  else
   document.all.guardar_cambios_fila.style.visibility='visible';
 }

}//de function habilitar_cambios_especiales()

IE4 = document.all;

function newConfirm(title,mess,icon,defbut,mods)
{
   if (IE4) {
      icon = (icon==0) ? 0 : 2;
      defbut = (defbut==0) ? 0 : 1;
      retVal = makeMsgBox(title,mess,icon,4,defbut,mods);
      retVal = (retVal==6);
   }
   else {
      retVal = confirm(mess);
   }
   return retVal;
}//de function newConfirm(title,mess,icon,defbut,mods)
