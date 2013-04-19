    /*
$Author: marco_canderle $
$Revision: 1.33 $
$Date: 2004/01/29 18:47:03 $
*/
//esta funcion es de agregar producto
function activar(){
/*
document.form1.modificar.disabled=false;
document.form1.eliminar.disabled=false;
document.form1.actualizar.disabled=true;
document.form1.generar_documento.disabled=true;
document.form1.aceptar_renglon.disabled=true;
document.form1.cancelar_renglon.disabled=true;
  */
document.form1.modificar.disabled=!(document.form1.modificar.disabled);
document.form1.eliminar.disabled= !(document.form1.eliminar.disabled);
document.form1.actualizar.disabled= !(document.form1.actualizar.disabled);
document.form1.generar_documento.disabled= !(document.form1.generar_documento.disabled);
document.form1.aceptar_renglon.disabled= !(document.form1.aceptar_renglon.disabled);
document.form1.cancelar_renglon.disabled= !(document.form1.cancelar_renglon.disabled);

}

//script de realizar oferta
function activar_botones(){
document.form.eliminar_renglon.disabled=false;
document.form.agregar.disabled=true;
document.form.modificar_renglon.disabled=false;
//document.form.cancelar.disabled=true;
//document.form.terminar_licitacion.disabled=true;
//document.form.aceptar_renglon.disabled=true;


}

function activar2(objeto){

document.form1.tipo_iva.disabled = !(document.form1.tipo_iva.disabled);

}


function aceptar_valores(objeto) {

 if ((objeto.value <= 0 ) || (objeto.value > 1) ) {
                        alert("Dato invalido, por favor reingrese dato");
                        return false;
                                                  }

                         return true;
}

//scrip de agregar producto
function  actualizar_valor() {
var i=0;
if (document.all.precio_proveedor.length>1) {
             while (document.all.precio_proveedor[i].checked==false)
                    i++;

	document.form1.precio.value = document.all.precio_proveedor[i].value;
      } //del then
      else
      document.form1.precio.value = document.all.precio_proveedor.value;


}





function desactiva_recarga(selec,id_renglon){
//aca doy el id del renglon
 document.all.noparticipa.value=id_renglon;
 if(selec.options[selec.options.length-1].selected) {
    //boton.disabled  = !(boton.disabled) ;
    document.form.submit();
   }


}


//fijarse si se puede eliminar
function pone_valores_text(){

var i=0;
var id=0;
var titulo;
var codigo;
var valor_titulo;
var valor_codigo;
if (document.all.renglon.length>1) {
             while (document.all.renglon[i].checked==false)
                   i++;
       id = document.all.renglon[i].value;
      } //del then
      else
      id = document.all.renglon.value;

//en id tengo el valor del id del renglon
titulo ="titulo_" + id;
codigo ="codigo_" + id;

//valor_titulo="document.all." + titulo + ".value";
document.form.codigo.value=i;
document.form.titulo.value=i;


}//fin

function verificar_cambio() {
document.all.cambio_precio.value=1;


}


//funcion para agregar una fila a la tabla detalle_lic del archivo lic_garantia_oferta
function desplegar_detalle_lic()
{
 
 var fila=document.all.detalle_lic.insertRow();
 
 document.all.detalle_lic.style.visibility='visible';//mostramos la tabla
  
 fila.insertCell(0).width='50%';
 fila.cells[0].innerHTML="<b>Mantenimiento de oferta: </b>"+document.all.mant_oferta.value+
 "<br><b>Forma de pago:</b> "+document.all.forma_pago.value+
 "<br><b>Plazo de entrega: </b>"+document.all.plazo_entrega.value+
 "<br><b>Fecha de entrega: </b>"+document.all.fecha_entrega.value;

 fila.insertCell(1).width='50%';
 fila.cells[1].innerHTML="<b>Número:</b> "+document.all.nro_lic.value+
 "<br><b>Expediente:</b> "+document.all.expediente_h.value+
 "<br><b>Valor del pliego:</b> "+document.all.valor_pliego.value;

 var fila1=document.all.detalle_lic.insertRow();
 fila1.insertCell(0).width='50%';
 fila1.cells[0].innerHTML="<b>Moneda:</b> "+document.all.nombre_moneda.value+
 "<br><b>Ofertado:</b> "+document.all.monto_ofertado.value+
 "<br><b>Estimado:</b> "+document.all.monto_estimado.value+  
 "<br><b>Ganado:</b> "+document.all.monto_ganado.value;

 fila1.insertCell(1).width='50%';
 fila1.cells[1].innerHTML="<b>Estado:</b> <span style=\"background-color: "+document.all.color_estado+
 "; border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;\">&nbsp;&nbsp;&nbsp;</span> "+document.all.nombre_estado.value;

 var fila2=document.all.detalle_lic.insertRow(); 
 fila2.insertCell(0).colSpan=2;
 fila2.cells[0].innerHTML="<b>Comentarios/Seguimiento:</b><br>"+document.all.observ_coment.value;
 
 var fila3=document.all.detalle_lic.insertRow();
 fila3.insertCell(0).colSpan=2;
 fila3.cells[0].innerHTML="<b>Perfil: </b>"+document.all.perfil.value;
 
 
 if(document.all.protocolo.value=='no')
 {var fila4=document.all.detalle_lic.insertRow();
  fila4.insertCell(0).colSpan=2;
  fila4.cells[0].innerHTML="<b>Protocolo Legal:<font color=\"red\"> No se ha cargado el protocolo legal</font>";
  document.all.mas1.value='si';
 } 
 document.all.detalle.value='Ocultar detalles';
 document.all.detalle.title='Oculta los detalles de la licitacion';
 document.all.detalle.onclick=contraer_detalle_lic;
 
}

function contraer_detalle_lic()
{
 var long=document.all.detalle_lic.rows.length;
 for(i=0;i<long;i++)
  document.all.detalle_lic.deleteRow();
   
 document.all.detalle_lic.style.visibility='hidden';//ocultamos la tabla 

 document.all.detalle.value='Más detalles';
 document.all.detalle.title='Muestra más detalles de la licitacion';
 document.all.detalle.onclick=desplegar_detalle_lic;
}