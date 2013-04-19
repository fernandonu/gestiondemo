/*
AUTOR: MAC
FECHA: 29/07/05

$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2005/09/03 16:28:57 $
*/


/**************************************************************
 Funcion para calcular el PROPORCIONAL FLETE, para la 
 fila pasada como parametro (el indice de fila que se 
 le concatena al final de cada campo correspondiente a la fila)
***************************************************************/
function calcular_proporcional_flete(index_fila)
{
 var valor_prop_flete=eval("document.all.proporcional_flete_"+index_fila);
 var subtotal=eval("document.all.subtotal_"+index_fila+".value");
 var monto_flete=document.all.monto_flete.value;
 var total_filas=document.all.total.value;
 
 valor_prop_flete.value=formato_BD(parseFloat(subtotal)/parseFloat(total_filas)*parseFloat(monto_flete));
}//de function calcular_proporcional_flete(index_fila)

/**************************************************************
 Funcion para calcular la BASE IMPONIBLE CIF , para la 
 fila pasada como parametro (el indice de fila que se 
 le concatena al final de cada campo correspondiente a la fila)
***************************************************************/
function calcular_base_imponible_cif(index_fila)
{
 var valor_base_imp=eval("document.all.base_imponible_cif_"+index_fila);
 var subtotal=eval("document.all.subtotal_"+index_fila+".value");
 var proporcional_flete=eval("document.all.proporcional_flete_"+index_fila+".value");
 
 valor_base_imp.value=formato_BD(parseFloat(subtotal)+parseFloat(proporcional_flete));
}//de function calcular_base_imponible_cif(index_fila)


/**************************************************************
 Funcion para calcular el monto de DERECHOS , para la 
 fila pasada como parametro (el indice de fila que se 
 le concatena al final de cada campo correspondiente a la fila)
***************************************************************/
function calcular_derechos(index_fila)
{
 var id_posad=eval("document.all.select_posad_"+index_fila+".value");
 var valor_derechos=eval("document.all.derechos_"+index_fila);
 var base_imponible_cif=eval("document.all.base_imponible_cif_"+index_fila+".value");

 //si está seleccionado el posad, calculamos el campo de derechos, sino, lo dejamos vacio
 if(id_posad!=-1)
 { var posad=eval("posad_"+id_posad);
 
  valor_derechos.value=formato_BD((parseFloat(base_imponible_cif)*parseFloat(posad["derechos"]))
 															+
 								 (parseFloat(base_imponible_cif)*parseFloat(posad["estadistica"]))
 								);
 }//de if(typeof(eval("posad_"+id_posad))!="undefined")
 else
  valor_derechos.value='';

 
}//de function calcular_derechos(index_fila)


/**************************************************************
 Funcion para calcular el monto de DERECHOS , para la 
 fila pasada como parametro (el indice de fila que se 
 le concatena al final de cada campo correspondiente a la fila)
***************************************************************/
function calcular_iva_ganancias(index_fila)
{
 var id_posad=eval("document.all.select_posad_"+index_fila+".value");
 var valor_iva_ganancias=eval("document.all.iva_"+index_fila);
 var base_imponible_cif=eval("document.all.base_imponible_cif_"+index_fila+".value");
 var derechos=eval("document.all.derechos_"+index_fila+".value");
 
 //si está seleccionado el posad, calculamos el campo de IVA_ganancias, sino, lo dejamos vacio
 if(id_posad!=-1)
 {var posad=eval("posad_"+id_posad);
 
  valor_iva_ganancias.value=formato_BD(((parseFloat(base_imponible_cif)+parseFloat(derechos))*posad["iva_ganancias"])
                                                                 +
                                      ((parseFloat(base_imponible_cif)+parseFloat(derechos))*0.03)
                                     ); 
 }//de if(typeof(eval("posad_"+id_posad))!="undefined") 
 else
  valor_iva_ganancias.value='';
                                     
}//de function calcular_iva(index_fila)


/**************************************************************
 Funcion para calcular el monto de DERECHOS , para la 
 fila pasada como parametro (el indice de fila que se 
 le concatena al final de cada campo correspondiente a la fila)
***************************************************************/
function calcular_ib(index_fila)
{
 var id_posad=eval("document.all.select_posad_"+index_fila+".value");
 var valor_ib=eval("document.all.ib_"+index_fila);
 var base_imponible_cif=eval("document.all.base_imponible_cif_"+index_fila+".value");
 var derechos=eval("document.all.derechos_"+index_fila+".value");
 
 //si está seleccionado el posad, calculamos el campo de IB, sino, lo dejamos vacio
 if(id_posad!=-1)
 {var posad=eval("posad_"+id_posad);
 
  valor_ib.value=formato_BD((parseFloat(base_imponible_cif)+parseFloat(derechos))*0.01);
 }//de if(typeof(eval("posad_"+id_posad))!="undefined")
 else
  valor_ib.value='';
 
}//de function calcular_ib(index_fila)


/**************************************************************
 Funcion que realiza todos los calculos para los montos de la
 OC Internacional,  para la fila pasada como parametro 
 (el indice de fila que se le concatena al final de cada campo
 correspondiente a la fila). Para eso, llama a las funciones 
 que calculan los montos de cada campo particular
***************************************************************/
function set_montos_fila_oc_internacional()
{
 var index_fila,cant_filas=document.all.items.value;
 for(index_fila=0;index_fila<cant_filas;index_fila++)	
 {
  if(typeof(eval("document.all.subtotal_"+index_fila))!="undefined")	
  {calcular_proporcional_flete(index_fila);
   calcular_base_imponible_cif(index_fila);
   calcular_derechos(index_fila);
   calcular_iva_ganancias(index_fila);
   calcular_ib(index_fila);
  } 
 }
 //recalculamos todos los montos totales de los montos de OC internacional
 calcular_monto_total(0,document.all.total_proporcional_flete);
 calcular_monto_total(1,document.all.total_base_imponible_cif);
 calcular_monto_total(2,document.all.total_derechos);
 calcular_monto_total(3,document.all.total_iva);
 calcular_monto_total(4,document.all.total_ib);
 
 set_montos_totales();
}// de set_montos_fila_oc_internacional

/**************************************************************
 Funcion para calcular el total de todas las filas, para cada
 campo de montos correspondientes a las OC internacionales. 
 La correspondencia del numero pasado como parametro con 
 el campo que se esta calculando, es la siguiente:
  Proporcional Flete=0
  Base Imponible=1 
  Derechos=2
  IVA=3
  IB=4
  
 El parametro "campo" se usa para saber donde se asignara el 
 monto calculado
***************************************************************/
function calcular_monto_total(tipo_monto,campo)
{
 	
 var cant_filas=document.all.items.value;
 var monto_total=0;
 var i=0;
 var monto_parcial;
 var aux;
 while(i<cant_filas)
 {
 	switch(tipo_monto)
	 {
	  case 0://Proporcional Flete=0
	         if(typeof(eval('document.all.proporcional_flete_'+i))!="undefined")
	            aux=eval('document.all.proporcional_flete_'+i+'.value');
	         else
	            aux=0;   
	         break;
	  case 1://Base Imponible=1 
	         if(typeof(eval('document.all.base_imponible_cif_'+i))!="undefined") 
	            aux=eval('document.all.base_imponible_cif_'+i+'.value');
	         else
	            aux=0;   
	         break;
	  case 2://Derechos=3
	         if(typeof(eval('document.all.derechos_'+i))!="undefined")
	            aux=eval('document.all.derechos_'+i+'.value');
	         else
	            aux=0;   
	         break;
	  case 3://IVA=4
	         if(typeof(eval('document.all.iva_'+i))!="undefined")
	            aux=eval('document.all.iva_'+i+'.value');
	         else
	            aux=0;
	         break;
	  case 4://IB
	         if(typeof(eval('document.all.ib_'+i))!="undefined")
	            aux=eval('document.all.ib_'+i+'.value');
	         else
	            aux=0;
	         break;
	  default:alert('Ha ocurrido un error: El tipo de parametro para calcular el monto no es correcto');
	          i=cant_filas;
	          break;
	 }//de switch(tipo_monto)
	if(aux!="")
	  monto_parcial=parseFloat(aux);
	else
	  monto_parcial=0; 
	monto_total+=monto_parcial;
	i++;        
 }//de while(i<cant_filas)
 	 
 campo.value=formato_BD(monto_total);
}//de function calcular_monto_total(tipo_monto)


/**************************************************************
 Funcion que setea los campos de totales, que se muestran 
 debajo del listado de productos de la OC internacional
***************************************************************/
function set_montos_totales()
{
 var set_fob,set_flete,set_iva,set_ib,set_derechos,set_honorarios;
 
 set_fob=document.all.total.value;
 set_flete=document.all.monto_flete.value;
 set_iva=document.all.total_iva.value;
 set_ib=document.all.total_ib.value;
 set_derechos=document.all.total_derechos.value;
 set_honorarios=document.all.honorarios_gastos.value;
	
 document.all.total_fob_final.value=set_fob;
 document.all.total_flete_final.value=set_flete;
 document.all.total_iva_ganancias_final.value=set_iva;
 document.all.total_ib_final.value=set_ib;
 document.all.total_derechos_final.value=set_derechos;
 document.all.total_honorarios_final.value=set_honorarios;
 
 set_fob=(set_fob)?parseFloat(set_fob):0;
 set_flete=(set_flete)?parseFloat(set_flete):0;
 set_iva=(set_iva)?parseFloat(set_iva):0;
 set_ib=(set_ib)?parseFloat(set_ib):0;
 set_derechos=(set_derechos)?parseFloat(set_derechos):0;
 set_honorarios=(set_honorarios)?parseFloat(set_honorarios):0;
 
 document.all.total_global.value=formato_BD(set_fob+set_flete+set_iva+set_ib+set_derechos+set_honorarios);
 
}