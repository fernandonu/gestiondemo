/*
Autor Diego Broggi
Fecha 30/07/2004

$Author: broggi $
$Revision: 1.15 $
$Date: 2005/07/27 16:03:27 $
*/

/******************************************************************
Esto es para controlar las distintas opciones y configuraciones
para la busqueda avanzada en ordenes de compra.
*******************************************************************/

control_chequeo=0;


function habilitar_ordenar(valor,por)
{if (valor.checked)
    {//document.busq_avanzada_armo.ordenar_nro_orden.checked=0;
     document.busq_avanzada_armo.ordenar_estado.checked=0;   
     document.busq_avanzada_armo.ordenar_monto.checked=0;          
     document.busq_avanzada_armo.ordenar_id_licitacion.checked=0;
     document.busq_avanzada_armo.ordenar_proveedor.checked=0;     
     document.busq_avanzada_armo.ordenar_entidad.checked=0;
     document.busq_avanzada_armo.ordenar_orden_prod.checked=0;
     document.busq_avanzada_armo.ordenar_nro_factura.checked=0;
     document.busq_avanzada_armo.ordenar_fecha_factura.checked=0;
     document.busq_avanzada_armo.ordenar_lugar_entrega.checked=0;
     document.busq_avanzada_armo.ordenar_fecha_entrega.checked=0;
     valor.checked = 1;
    }
}

////////////////////////////////Nro. Orden/////////////////////////////////////
function habilitar_nro_orden()
{
 document.busq_avanzada_armo.filtrar_nro_orden.checked=1;            
// document.busq_avanzada_armo.ordenar_nro_orden.disabled=0;            
} 

function check_nro_orden()
{
if (!document.busq_avanzada_armo.filtrar_nro_orden.checked) {
	//document.all.ordenar_nro_orden.disabled=0;
//else {
 document.busq_avanzada_armo.nro_orden.value="";            
// document.busq_avanzada_armo.ordenar_nro_orden.disabled=1;            
 //document.busq_avanzada_armo.ordenar_nro_orden.checked=0;            
}
} 

/////////////////////////////Estado de la Orden///////////////////////////////////
function habilitar_estado()
{
 document.busq_avanzada_armo.filtrar_estado.checked=1;     
 document.all.ordenar_estado.disabled=0; 
}
function check_estado()
{
if (document.busq_avanzada_armo.filtrar_estado.checked) 
	document.all.ordenar_estado.disabled=0;
else {
 document.busq_avanzada_armo.estado.selectedIndex=0;            
 document.busq_avanzada_armo.ordenar_estado.disabled=1;            
 document.busq_avanzada_armo.ordenar_estado.checked=0;            
}
}
 
////////////////////////////Tipo de Orden/////////////////////////////////////////
function habilitar_tipo_orden()
{if (document.busq_avanzada_armo.tipo_orden.value=='l' || document.busq_avanzada_armo.tipo_orden.value=='p')
    {document.busq_avanzada_armo.filtrar_orden_prod.disabled=1;
     document.busq_avanzada_armo.orden_prod.disabled=1;          
     document.busq_avanzada_armo.filtrar_tipo_orden.checked=1;     
    }	
 else {if (document.busq_avanzada_armo.tipo_orden.value!=-1)
          {document.busq_avanzada_armo.filtrar_orden_prod.disabled=0;
           document.busq_avanzada_armo.orden_prod.disabled=0;
           document.busq_avanzada_armo.filtrar_tipo_orden.checked=1;           
          } 
       else {document.busq_avanzada_armo.filtrar_orden_prod.disabled=0;
             document.busq_avanzada_armo.orden_prod.disabled=0;
             document.busq_avanzada_armo.filtrar_tipo_orden.checked=0;             
            }   
      }   
}

function check_tipo_orden(){
	if (!document.all.filtrar_tipo_orden.checked) {
		document.all.tipo_orden.selectedIndex=0;
		document.all.filtrar_orden_prod.disabled=0; 
		document.all.orden_prod.disabled=0;
	}
}

///////////////////////////Monto//////////////////////////////////////////////////
function habilitar_monto()
{document.busq_avanzada_armo.filtrar_por_monto.checked=1;
 document.busq_avanzada_armo.ordenar_monto.disabled=0;    
}   

function check_monto(){
	if (document.all.filtrar_por_monto.checked) 
 		document.busq_avanzada_armo.ordenar_monto.disabled=0;    
 	else {
 		document.busq_avanzada_armo.ordenar_monto.disabled=1;    
 		document.busq_avanzada_armo.ordenar_monto.checked=0;    
 		document.busq_avanzada_armo.monto_1.value="";    
 		document.busq_avanzada_armo.monto_2.value="";    
	}
}

//////////////////////////Forma Pago///////////////////////////////////////////////
function habilitar_forma_pago()
{document.busq_avanzada_armo.filtrar_por_forma_pago.checked=1;   

}   

function check_forma_pago()
{
	if(!document.busq_avanzada_armo.filtrar_por_forma_pago.checked)
		document.busq_avanzada_armo.forma_pago_texto.value="";     
}  
///////////////////////////////////////////////////////////////////////////////////

//////////////////////////Usar busq general///////////////////////////////////////////////
function habilitar_keyword()
{document.busq_avanzada_armo.usar_bus_general.checked=1;   

}   

function check_bus_general()
{
	if(!document.busq_avanzada_armo.usar_bus_general.checked)
		document.busq_avanzada_armo.keyword.value="";     
}  
///////////////////////////////////////////////////////////////////////////////////





//////////////////////////Productos///////////////////////////////////////////////
function habilitar_productos()
{document.busq_avanzada_armo.filtrar_productos.checked=1;     
}   

function check_habilitar_productos()
{
	if(!document.busq_avanzada_armo.filtrar_productos.checked)
		document.busq_avanzada_armo.productos.selectedIndex=0;     
} 

/////////////////////////////Entregados/Recibidos/////////////////////////////////
function habilitar_re_en()
{document.busq_avanzada_armo.filtrar_re_en.checked=1;     
}   

function check_habilitar_re_en()
{
	if(!document.busq_avanzada_armo.filtrar_re_en.checked)
		document.busq_avanzada_armo.entregado_recibido.selectedIndex=0;     
} 

//////////////////////////Id. Licitación//////////////////////////////////////////
function habilitar_id_licitacion()
{document.busq_avanzada_armo.filtrar_id_licitacion.checked=1;           
document.busq_avanzada_armo.ordenar_id_licitacion.disabled=0;     
}   
function check_id_licitacion()
{
	if(document.busq_avanzada_armo.filtrar_id_licitacion.checked)
		document.busq_avanzada_armo.ordenar_id_licitacion.disabled=0;
	else {     
		document.busq_avanzada_armo.id_licitacion.value="";     
		document.busq_avanzada_armo.ordenar_id_licitacion.disabled=1;     
		document.busq_avanzada_armo.ordenar_id_licitacion.checked=0;     
	}
} 

//////////////////////////Proveedor///////////////////////////////////////////////
function habilitar_proveedor()
{document.busq_avanzada_armo.filtrar_id_proveedor.checked=1;     
document.busq_avanzada_armo.ordenar_proveedor.disabled=0;     
} 

function check_proveedor()
{
	if(document.busq_avanzada_armo.filtrar_id_proveedor.checked)
		document.busq_avanzada_armo.ordenar_proveedor.disabled=0;
	else {     
		document.busq_avanzada_armo.proveedor.selectedIndex=0;     
		document.busq_avanzada_armo.ordenar_proveedor.disabled=1;     
		document.busq_avanzada_armo.ordenar_proveedor.checked=0;     
	}
} 

//////////////////////////Moneda//////////////////////////////////////////////////
function habilitar_moneda()
{document.busq_avanzada_armo.filtrar_id_moneda.checked=1;     
} 

function check_moneda()
{
	if(!document.busq_avanzada_armo.filtrar_id_moneda.checked)
		document.busq_avanzada_armo.moneda.selectedIndex=0;     
} 

//////////////////////////Entidad/////////////////////////////////////////////////
function habilitar_entidad()
{document.busq_avanzada_armo.filtrar_id_entidad.checked=1;     
document.busq_avanzada_armo.ordenar_entidad.disabled=0;     
} 

function check_entidad()
{
	if(document.busq_avanzada_armo.filtrar_id_entidad.checked)
		document.busq_avanzada_armo.ordenar_entidad.disabled=0;
	else {     
		document.busq_avanzada_armo.entidad.selectedIndex=0;     
		document.busq_avanzada_armo.ordenar_entidad.disabled=1;     
		document.busq_avanzada_armo.ordenar_entidad.checked=0;     
	}
}
 
//////////////////////////Orden Producción////////////////////////////////////////
function habilitar_orden_prod()
{document.busq_avanzada_armo.filtrar_orden_prod.checked=1;        
document.busq_avanzada_armo.ordenar_orden_prod.disabled=0;        
}

function check_orden_prod(){
	if (document.all.filtrar_orden_prod.checked) 
		document.busq_avanzada_armo.ordenar_orden_prod.disabled=0;        
	else {
		document.all.orden_prod.value=""; 
		document.busq_avanzada_armo.ordenar_orden_prod.disabled=1;        
		document.busq_avanzada_armo.ordenar_orden_prod.checked=0;        
	}
}
//////////////////////////Nro. Factura////////////////////////////////////////////
function habilitar_nro_factura()
{document.busq_avanzada_armo.filtrar_nro_factura.checked=1;     
document.busq_avanzada_armo.ordenar_nro_factura.disabled=0;     
}

function check_factura(){
	if (document.all.filtrar_nro_factura.checked) 
		document.busq_avanzada_armo.ordenar_nro_factura.disabled=0;     
	else {
		document.all.nro_factura.value=""; 
		document.busq_avanzada_armo.ordenar_nro_factura.disabled=1;     
		document.busq_avanzada_armo.ordenar_nro_factura.checked=0;     
	}
}

//////////////////////////Lugar Entrega///////////////////////////////////////////
function habilitar_lugar_entrega()
{document.busq_avanzada_armo.filtrar_lugar_entrega.checked=1;    
document.busq_avanzada_armo.ordenar_lugar_entrega.disabled=0;    
}

function check_entrega(){
	if (document.all.filtrar_lugar_entrega.checked) 
		document.busq_avanzada_armo.ordenar_lugar_entrega.disabled=0;    
	else {
		document.all.lugar_entrega.value=""; 
		document.busq_avanzada_armo.ordenar_lugar_entrega.disabled=1;    
		document.busq_avanzada_armo.ordenar_lugar_entrega.checked=0;    
	}
}

///////////////////////////Fecha Factura//////////////////////////////////////////////////
function habilitar_fecha_factura()
{document.busq_avanzada_armo.filtrar_fecha_factura.checked=1;
 document.busq_avanzada_armo.ordenar_fecha_factura.disabled=0;    
}   

function check_fecha_factura(){
	if (document.all.filtrar_fecha_factura.checked) 
 		document.busq_avanzada_armo.ordenar_fecha_factura.disabled=0;    
 	else {
 		document.busq_avanzada_armo.ordenar_fecha_factura.disabled=1;    
 		document.busq_avanzada_armo.ordenar_fecha_factura.checked=0;    
 		document.busq_avanzada_armo.fecha_factura_1.value="";    
	}
}

///////////////////////////Fecha Entrega//////////////////////////////////////////////////
function habilitar_fecha_entrega()
{document.busq_avanzada_armo.filtrar_fecha_entrega.checked=1;
 document.busq_avanzada_armo.ordenar_fecha_entrega.disabled=0;    
}   

function check_fecha_entrega(){
	if (document.all.filtrar_fecha_entrega.checked) 
 		document.busq_avanzada_armo.ordenar_fecha_entrega.disabled=0;    
 	else {
 		document.busq_avanzada_armo.ordenar_fecha_entrega.disabled=1;    
 		document.busq_avanzada_armo.ordenar_fecha_entrega.checked=0;    
 		document.busq_avanzada_armo.fecha_entrega_1.value="";    
	}
}

//////////////////////////Controles Generales///////////////////////////////////////////
function control_general()
{
    
 if (document.busq_avanzada_armo.filtrar_nro_orden.checked && document.busq_avanzada_armo.nro_orden.value=="")
    {alert ("El campo Número de Orden no puede estar vacío.");
     return (false);
    }

 if (document.busq_avanzada_armo.filtrar_estado.checked && document.busq_avanzada_armo.estado.selectedIndex==0)
    {alert ("Debe seleccionar algún Estado de la orden.");
     return (false);
    }

 if (document.busq_avanzada_armo.filtrar_tipo_orden.checked && document.busq_avanzada_armo.tipo_orden.selectedIndex==0)
    {alert ("Debe seleccionar algún Tipo de Orden para filtrar ordenes.");
     return (false);
    }

 if (document.busq_avanzada_armo.filtrar_por_monto.checked)
 	if(document.busq_avanzada_armo.monto_1.value=="" || document.busq_avanzada_armo.monto_2.value=="")    
    	{alert ("Debe ingresar ambos valores para poder buscar entre Montos");
     	return (false);
    	} else if (parseFloat(document.busq_avanzada_armo.monto_1.value) > parseFloat(document.busq_avanzada_armo.monto_2.value))
    	{alert("El primer Monto debe ser menor o igual que el segundo para buscar entre montos.");
    	return(false);
    	}

 if (document.busq_avanzada_armo.filtrar_por_forma_pago.checked && document.busq_avanzada_armo.forma_pago_texto.value=="" && document.busq_avanzada_armo.forma_pago.value==-1)
    {alert ("Debe establecer alguna Forma de Pago conocida para filtrar por forma de pago.");
     return (false);
    }
    
 if (document.busq_avanzada_armo.filtrar_productos.checked && document.busq_avanzada_armo.productos.selectedIndex==0)
    {alert ("Debe seleccionar algún Tipo de Producto para filtrar ordenes.");
     return (false);
    }
    
 if (document.busq_avanzada_armo.filtrar_id_proveedor.checked && document.busq_avanzada_armo.proveedor.selectedIndex==0)
    {alert ("Debe seleccionar algún Proveedor de la lista para filtrar ordenes.");
     return (false);
    }

 if (document.busq_avanzada_armo.filtrar_id_moneda.checked && document.busq_avanzada_armo.moneda.selectedIndex==0)
    {alert ("Debe seleccionar algún Tipo de Moneda para filtrar ordenes.");
     return (false);
    }

 if (document.busq_avanzada_armo.filtrar_id_entidad.checked && document.busq_avanzada_armo.entidad.selectedIndex==0)
    {alert ("Debe seleccionar alguna Entidad de la lista para filtrar ordenes.");
     return (false);
    }

 if (document.busq_avanzada_armo.filtrar_fecha_factura.checked)
 	if (document.busq_avanzada_armo.fecha_factura_1.value=="")
    	{alert ("El primer campo de Fecha de Factura no puede quedar vacío.");
     	return (false);
    	} else
    	if (compara_fechas(document.busq_avanzada_armo.fecha_factura_1.value,document.busq_avanzada_armo.fecha_factura_2.value)>0)
    		{alert ("Tenga en cuenta que la primera Fecha de Factura debe ser menor o igual que la segunda");
    		return (false);
    	}	      
    
 if (document.busq_avanzada_armo.filtrar_lugar_entrega.checked && document.busq_avanzada_armo.lugar_entrega.value=="")
    {alert ("Debe ingresar algún Lugar de Entrega para filtrar ordenes.");
     return (false);
    } 

 if (document.busq_avanzada_armo.filtrar_fecha_entrega.checked)
 	if (document.busq_avanzada_armo.fecha_entrega_1.value=="")
    	{alert ("El primer campo de Fecha de Entrega no puede quedar vacío.");
     	return (false);
    	} else
    	if (compara_fechas(document.busq_avanzada_armo.fecha_entrega_1.value,document.busq_avanzada_armo.fecha_entrega_2.value)>0)
    		{alert ("Tenga en cuenta que la primera Fecha de Entrega debe ser menor o igual que la segunda");
    		return (false);
    	}	      

    
    window.open('busq_avanzada_muestro.php?first=1');   
  
}	

function compara_fechas(f1,f2)
{
 
 fa1=new String();
 fa2=new String();
 aux=new Array();
 aux=f1.split("/");
 fa1=aux[2]+"-"+aux[1]+"-"+aux[0];
 aux=f2.split("/");
 fa2=aux[2]+"-"+aux[1]+"-"+aux[0];

 
 if (fa1 == fa2) return 0; 
 if (fa1 < fa2) return -1; 
 if (fa1 > fa2) return 1; 

}	