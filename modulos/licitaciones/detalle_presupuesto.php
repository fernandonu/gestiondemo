<?
/*
Autor: Diego - GACZ
Creado: viernes 30/04/04

MODIFICADA POR
$Author: gabriel $
$Revision: 1.88 $
$Date: 2006/01/05 22:28:28 $
*/

require_once("../../config.php");

if($_POST['valor_boton'])
 $_POST['boton']=$_POST['valor_boton'];

$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST['id_entrega_estimada'];
$id_subir=$parametros['id_subir'] or $id_subir=$_POST['id_subir'];
$nro_orden_cliente=$parametros['nro_orden_cliente'] or $nro_orden_cliente=$_POST['nro_orden_cliente'];
$id=$parametros['ID'] or $id=$_POST['id'];
$id_lic_prop=$parametros['id_lic_prop'] or $id_lic_prop=$_POST['id_prop'];
$pagina=$parametros['pagina'];

if ($_POST['beliminar'])
{
		require_once("detalle_presupuesto_proc.php");
		if ($pagina=="listado")
		{
?>
		<script>
		window.opener.location.reload();
		window.close()
		</script>
<?
		}
		else
			header("location:../ordprod/ver_seguimiento_ordenes.php");
		die;
}


if ($_POST['doForm']=="Guardar")
		require_once("detalle_presupuesto_proc.php");

if ($_POST['bexport'])
		require_once("detalle_presupuesto_excel.php");

if ($_POST['boton']=="Volver")
{
	$link=encode_link("../ordprod/seguimiento_orden.php",array("id"=>$id,"id_entrega_estimada"=>$id_entrega_estimada,"id_subir"=>$id_subir,"nro_orden_cliente"=>$nro_orden_cliente));
	header("location: $link");
	die;
}

if ($_POST['doForm']=='OC')
{
   	require_once("detalle_presupuesto_proc.php");
   	if ($_POST['id_proveedor'])
		{
  		$link=encode_link("../ord_compra/ord_compra.php",array("licitacion"=>$id,"select_proveedor"=>$_POST['id_proveedor'],"id_renglon_prop"=>$_POST['id_renglon_prop'],"select_contacto"=>-2,"select_pago"=>4,"select_moneda"=>2));
  		header("location: $link");
		}
}

//traemos info de licitacion para
if ($id_lic_prop!=-1)
{
	$q ="select licitacion.id_licitacion,titulo,estado,fecha_cotizacion,comentarios,
	     vence_oc,entidad.nombre as nombre_entidad,valor_dolar_lic,nro_orden,nro,entrega_estimada_producto
		 from licitacion_presupuesto_new
		 left join licitacion  using(id_licitacion)
		 left join entidad using(id_entidad)
		 left join subido_lic_oc using(id_entrega_estimada)
		 left join entrega_estimada using(id_entrega_estimada)
		 where id_licitacion_prop=$id_lic_prop";
}
else
{
	$q ="select licitacion.id_licitacion,vence_oc,entidad.nombre as nombre_entidad,valor_dolar_lic,nro_orden,nro
	     from licitacion
		 left join subido_lic_oc on subido_lic_oc.id_licitacion=licitacion.id_licitacion AND subido_lic_oc.id_entrega_estimada=$id_entrega_estimada
		 left join entidad using(id_entidad)
		 left join entrega_estimada using(id_entrega_estimada)
		 where subido_lic_oc.id_licitacion=$id";
}
$resultado_licitacion=sql($q,"<br>Error al traer los datos de la licitacion<br>") or fin_pagina();

$q= "select id_producto,min(monto) as monto
from log_modif_precio_presupuesto
join producto_presupuesto_new using(id_producto_presupuesto)
group by id_producto
order by id_producto";
$precios_min=sql($q,"<br>Error al traer datos del log de modificacion<br>") or fin_pagina();

?>
<html>
<head>
<title>Presupuestar Licitación</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script languaje="javascript" src="../../lib/funciones.js"></script>
<script language="javascript">
var precios_prod=new Array();
<?
while (!$precios_min->EOF)
{
 echo "precios_prod[".$precios_min->fields['id_producto']."]=new Array();\n";
 echo "precios_prod[".$precios_min->fields['id_producto']."]['monto']=".$precios_min->fields['monto'].";\n";
 $precios_min->movenext();
}
?>
var fila="";
var columna="";
var tabla="";
var total_proveedor=new Array();

function cargar_ventana(objeto)
{
var o;
 if(typeof(objeto)!="undefined")
  o=objeto;
 else
  o=this;

divi=o.name.split("_");
tabla=divi[2];
fila=divi[3];
columna=divi[4];
window.open("cargar_comentario_presupuesto.php",'','left=40,top=80,width=700,height=300,resizable=1,status=1');
}

function total_renglon_prov(renglon,columna)
{
 var total_reng=0;
 var orenglon=new getRenglonNode(renglon);
 var onode;
// if (!confirm(orenglon.cantidadProductos)) return 0;
 for(var i=2; i < orenglon.cantidadProductos+2 ;i++)
 {
 	 onode=getDataNode(renglon,i,columna);
	 if (onode!=null && onode.elegido)
	 	total_reng+=onode.precio*onode.cantidad;
 }
 return total_reng.toFixed(2);
}


 var cambioForm=0;//esta variable se utiliza para indicar si se cambio algo en el formulario
 var chk_coments=0;//indica si se deben checkear por cambios los comentarios de proveedores
 var chk_adic=0;//indica si se deben checkear por cambios los comentarios adicionales de productos

//funcion que cambia el total presupuestado al prveedor sin modif. el color de la celda
function cambiar_precio(tabla,fila,columna)
{
	var oprov=getProvNode(tabla,columna);
	var onode=getDataNode(tabla,fila,columna);

	if (onode!=null && onode.elegido)
	{
		oprov.setTotal(total_renglon_prov(tabla,columna));
		ftotal_provs(tabla);//recalculo el total de proveedores
	}
	cambioForm=1;//se hizo un cambio en el formulario
}

function cambiar_precios(nro_renglon,nro_prod)
{
	for (var i=2; i-2 < cant_prov; i++)
	{
  	if (!col_exist(i))
  		continue;//paso a la siguiente iteracion
		else
			cambiar_precio(nro_renglon,nro_prod,i);
	}
}
//@return true si esta elegido, false sino o no existe el objeto
function elegido(itabla,irow,icol)
{
	//alert("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+icol);
	var elegido=eval("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+icol);
	return (typeof elegido!='undefined' && elegido.value==1);
}

function elegir_precio(tabla,fila,columna) //cambia de color al apretar el boton E
{
var node=getDataNode(tabla,fila,columna);
var oprov=getProvNode(tabla,columna);
//si lo quiere deselegir o (si lo va a elegir) checkear cantidades
if (node!=null && node.elegido || chk_cantidad(tabla,fila,columna,node.cantidad))
{
	if (node.elegido)
	{
	 	oprov.setTotal(oprov.total-node.precio*node.cantidad);
	 	node.setElegido(false);
	}
	else
	{
		var oprod=getProdNode(tabla,fila);
		//puede que aun no haya log de ese producto
		if (typeof precios_prod[oprod.id]!='undefined' && (precios_prod[oprod.id]['monto']*1.05) < node.precio)
		{
			//si presiono el boton cancelar
			if (showModalDialog('<?=encode_link("popup_precio.php",array())?>&id_producto='+oprod.id+'&precio='+node.precio)==1)
				return;
		}
	 	//alert(typeof oprov.total+ ", "+typeof node.precio+", "+typeof node.cantidad);
	 	oprov.setTotal(oprov.total+node.precio*node.cantidad);
	 	node.setElegido(true);
	}
	ftotal_provs(tabla);
	cambioForm=1;//se hizo un cambio en el formulario
}
else
	alert("Verifique las cantidades\n no se puede elegir el producto con la cantidad indicada");
}

//Constructor de clase, devuelve un objeto, no hace falta usar new
//Uso: var obj=getDataNode(i,j,k);
function getDataNode(itabla,irow,icol)
{
	var o=new Object();
	if (typeof eval("document.forms[0].texto_precio_"+itabla+"_"+irow+"_"+icol)!='undefined')
	{
		//Propiedades del NODO
		//para escribir estas propiedades, usat los metodos set{Nbrepropiedad}
 		//alert("dentro del if");
		o.elegido=eval("document.forms[0].hidden_precio_elegido_"+itabla+"_"+irow+"_"+icol+".value==1");//obj HIDDEN
		o.precio=eval("parseFloat(document.forms[0].texto_precio_"+itabla+"_"+irow+"_"+icol+".value.replace(',','.'))");//obj TEXT
		if (isNaN(o.precio))
		{
			o.precio=0;
			eval("document.forms[0].texto_precio_"+itabla+"_"+irow+"_"+icol+".value="+o.precio);//obj TEXT
		}
		o.cantidad=eval("parseInt(document.forms[0].hcantidadprov_"+itabla+"_"+irow+"_"+icol+".value)");//obj HIDDEN
		o.comentario=eval("document.forms[0].hidden_comentario_"+itabla+"_"+irow+"_"+icol+".value");//obj HIDDEN

		//DESCOMENTAR SI HACE FALTA ACCEDER A ESTO DESDE AFUERA DE LA CLASE
		//o.itabla=itabla;
		//o.irow=irow;
		//o.icol=icol;

		o.setPrecio=function(precio)
		{
			o.precio=parseFloat(precio);
			eval("document.forms[0].texto_precio_"+itabla+"_"+irow+"_"+icol+".value="+o.precio.toFixed(2));
		}
	 	o.setPrecio(o.precio);//para que reemplaze las comas por puntos y deje un numero flotante
		o.setCantidad=function(cantidad)
		{
			o.cantidad=typeof parseInt(cantidad)=='Number'?parseInt(cantidad):0;
			eval("document.forms[0].hcantidadprov_"+itabla+"_"+irow+"_"+icol+".value="+o.cantidad);
		}
		o.setComentario=function(comentario)
		{
			o.comentario=comentario;
			eval("document.forms[0].hidden_comentario_"+itabla+"_"+irow+"_"+icol+".value="+o.comentario);
			eval("document.all.columna_"+itabla+"_"+irow+"_"+icol+".title="+o.comentario);
		}
		o.setElegido=function(booleanvalue)
		{
			o.elegido=(typeof booleanvalue!='undefined' && booleanvalue?true:false);
			eval("document.forms[0].hidden_precio_elegido_"+itabla+"_"+irow+"_"+icol+".value="+(o.elegido?1:0));
			eval("document.all.columna_"+itabla+"_"+irow+"_"+icol+".style.backgroundColor="+(o.elegido?"'#9AE292'":"'<?=$bgcolor2?>'"));
		}
	}
	else
		o=null
	return o;
}
//Constructor de Clase que contiene los datos de un proveedor para un determinado renglon
function getProvNode(itabla,icol)
{
	var o=new Object();
	if (typeof eval("document.forms[0].hidden_idprov_"+icol)!='undefined')
	{
		//Propiedades del proveedor comun a todos los nodos en la columna @icol
		o.id=eval("document.forms[0].hidden_idprov_"+icol+".value");//obj HIDDEN
		o.nbre=eval("document.forms[0].hnombreprov_"+icol+".value");//obj HIDDEN
		o.flag=eval("document.forms[0].hflagprov_"+icol+".value");//obj HIDDEN
		o.total=eval("parseFloat(document.forms[0].monto_"+itabla+"_"+icol+".value.replace(',','.'))");//obj TEXT

		o.setTotal=function(total)
		{
			o.total=parseFloat(total);
			eval("document.forms[0].monto_"+itabla+"_"+icol+".value="+o.total.toFixed(2));
		}
	}
	else
		o=null;
	return o;
}
//Constructor de clase contiene los datos utiles sobre un renglon determinado
function getRenglonNode(itabla)
{
	var o=new Object();
	if (eval("document.forms[0].hidrenglon_"+itabla)!='undefined')
	{
		//Propiedades del renglon comun a todos los nodos en la tabla @itabla
		//id_renglon de licitaciones
		o.id=eval("document.forms[0].hidrenglon_"+itabla+".value");//obj HIDDEN
		//id_renglon de presupuesto
		o.id_pres=eval("document.forms[0].hidden_idrenglon_"+itabla+".value");//obj HIDDEN
		//cantidad de renglones a comprar
		o.cantidad=eval("parseInt(document.forms[0].cant_renglon_"+itabla+".value)");//obj TEXT
		//cantidad de productos en el renglon, maxima cantidad posible (si no se borro ninguno)
		o.cantidadProductos=eval("parseInt(document.forms[0].cant_prod_reng_"+itabla+".value)");//obj HIDDEN

		o.setCantidadProductos=function(cantidad)
		{
			o.cantidadProductos=parseInt(cantidad);
			eval("document.forms[0].cant_prod_reng_"+itabla+".value="+o.cantidadProductos);
		}
	}
	else
		o=null;
	return o;
}

//Constructor de clase contiene datos utiles sobre un producto de un determinado renglon
function getProdNode(itabla,irow)
{
	var o=new Object();
	//si se requiere acceder a los datos del producto, llamar a esta funcion
	if (typeof eval("document.forms[0].hidproducto_"+itabla+"_"+irow)!='undefined')
	{
		//Propiedades del producto comun a todos los nodos en la fila @irow
		o.id=eval("document.forms[0].hidproducto_"+itabla+"_"+irow+".value");//obj HIDDEN
		//puede no existir, (en el caso de que se agrega un producto que no esta en la licitacion)
		if (typeof eval("document.forms[0].hidproducto_orig_"+itabla+"_"+irow)!='undefined')
		{
			o.idOrig=eval("document.forms[0].hidproducto_orig_"+itabla+"_"+irow+".value");//obj HIDDEN
			o.idPres=eval("document.forms[0].producto_"+itabla+"_"+irow+".value");//obj HIDDEN
		}
		if (o.idOrig=='')
		{
			o.idOrig=o.id;
			eval("document.forms[0].hidproducto_orig_"+itabla+"_"+irow+".value="+o.idOrig);//obj HIDDEN
		}
		o.descOrig=eval("document.forms[0].hdescorig_"+itabla+"_"+irow+".value");//obj HIDDEN
		o.descAdic=eval("document.forms[0].hdescadic_"+itabla+"_"+irow+".value");//obj HIDDEN
		o.descFull=eval("document.forms[0].nbreproducto_"+itabla+"_"+irow+".value");//obj TEXT
		o.cantidad=eval("parseInt(document.forms[0].cantidad_prod_"+itabla+"_"+irow+".value)");//obj HIDDEN

		o.setCantidad=function(cantidad)
		{
			o.cantidad=parseInt(cantidad);
			eval("document.forms[0].cantidad_prod_"+itabla+"_"+irow+".value="+o.cantidad);
		}
		o.setDescAdic=function(desc)
		{
			o.descAdic=desc;
			eval("document.forms[0].hdescadic_"+itabla+"_"+irow+".value="+o.descAdic);
		}
		o.setDescFull=function(desc)
		{
			o.descFull=desc;
			eval("document.forms[0].nbreproducto_"+itabla+"_"+irow+".value="+o.descFull);//obj TEXT
		}
		o.setId=function(id)
		{
			o.id=parseInt(id);
			eval("document.forms[0].hidproducto"+itabla+"_"+irow+".value");//obj HIDDEN
		}
	}
	else
		o=null;
	return o;
}


var arreglo_prov=new Array();
//inserto proveedores

var id_proveedor="";
var texto_proveedor="";
//funciones para cargar y borrar proveedores
function nuevo_prov(nro_tabla)
{

 table = eval("document.all.tabla_"+nro_tabla);
 //por si la tabla se borro
 if (typeof table!='undefined')
 {
	 rows=table.rows.length-1;
	 var col=cant_prov+2;//los proveedores empiezan en 2
	 //alert("Columna destino: "+col);
	 //inserto nombre de proveedor
	 mycurrent_cell=document.createElement("TD");
	 mycurrent_cell.setAttribute("id","columna_"+nro_tabla+"_"+0+"_"+col);
	 mycurrent_cell.setAttribute("title",texto_proveedor);
	 mycurrent_cell.vAlign="top";
	 mycurrent_cell.ondblclick=function (){change_image(col)};
	 mycurrent_cell.innerHTML=
	 "<table width=100% border=0 cellpadding=0 cellspacing=0 align=center>"+
	"<tr>"+
		"<td><img id='imagen_"+col+"_"+nro_tabla+"' src="+img_ext+" border=0 title='Ocultar Proveedor' align='left' style='cursor:hand;' onclick='change_image("+col+");' /></td>"+
	"</tr>"+
	"<tr>"+
		"<td id=mo><b>"+texto_proveedor+"</b>"+
		"<div align='center'>"+
		"<input type='button' name='boton_borrar_"+nro_tabla+"_"+col+"' onclick='borrar_proveedor(this);' value='B' style='cursor:hand;'>"+
		"</div>"+
		"</td>"+
	"</tr>"+
	"</table>";
	 table.rows[0].appendChild(mycurrent_cell);
	 table.rows[0].cells[table.rows[0].cells.length-1].style.fontWeight="bold";

	 mycurrent_cell=document.createElement("TD");
	 mycurrent_cell.setAttribute("id","columna_"+nro_tabla+"_"+1+"_"+col);
	 mycurrent_cell.setAttribute("name","columna_"+nro_tabla+"_"+1+"_"+col);
	 span=document.createElement("span");
	 span.appendChild(document.createTextNode("U$S"));
	 mycurrent_cell.appendChild(span);
	 text_monto=document.createElement("input");
	 text_monto.setAttribute("type","text");
	 text_monto.setAttribute("name","monto_"+nro_tabla+"_"+col);
	 text_monto.setAttribute("id","monto_"+nro_tabla+"_"+col);
	 text_monto.setAttribute("value",0);
	 text_monto.setAttribute("size",8);
	 mycurrent_cell.appendChild(text_monto);

	 table.rows[1].appendChild(mycurrent_cell);
	 table.rows[1].cells[table.rows[1].cells.length-1].style.fontWeight="bold";
	 var i=2;//identificador de fila
	 var j=2;//contador de cuantas filas se van agregando, (indice de la tabla)
	 //var max_irow=eval("parseInt(document.all.cant_prod_reng_"+nro_tabla+".value)+2");//maximo valor de i
	 while ( j < rows)
	 {
	 	//alert("tabla="+nro_tabla+" fila="+i+"  "+row_exist(nro_tabla,i));
	 	//si no esta definido entonces no existe esta fila i, se pudo haber borrado
	 	if (!row_exist(nro_tabla,i++))
			continue;

	 table.rows[j++].appendChild(create_prodprovcell(nro_tabla,i-1,col));
	 }
 }
}

function insertar_prov()
{
	var col=0;
 	id_proveedor=wprov.document.all.proveedor.options[wprov.document.all.proveedor.options.selectedIndex].value;
  texto_proveedor=wprov.document.all.proveedor.options[wprov.document.all.proveedor.options.selectedIndex].text;
  if (!buscar_elemento(id_proveedor,arreglo_prov))
  {
		for(t=1;t<=document.all.cant_renglones.value;t++)
		{
		  table = eval("document.all.tabla_"+t);
			 //por si la tabla se borro
			 if (typeof table!='undefined')
			 {
				 rows=table.rows.length-1;
				 col=2+cant_prov;
			   nuevo_prov(t);
			 }
		}
		//si entro alguna vez en el if de arriba
		if (col!=0)
		{
		  //Datos del proveedor
			hidden_idprov=document.createElement("input");
			hidden_idprov.setAttribute("type","hidden");
			hidden_idprov.setAttribute("name","hidden_idprov_"+col);
			hidden_idprov.setAttribute("id","hidden_idprov_"+col);
			hidden_idprov.setAttribute("value",id_proveedor);
			hflagprov=document.createElement("input");
			hflagprov.setAttribute("type","hidden");
			hflagprov.setAttribute("name","hflagprov_"+col);
			hflagprov.setAttribute("id","hflagprov_"+col);
			hflagprov.setAttribute("value","insertar");
			document.forms[0].appendChild(hidden_idprov);
			document.forms[0].appendChild(hflagprov);
			arreglo_prov[arreglo_prov.length]=id_proveedor;
			cant_prov++;
		  cambioForm=1;//se hizo un cambio en el formulario
		}
  }
	else
 		alert('El proveedor que intenta insertar ya existe en el presupuesto');
}

function borrar_columna(nro,objeto)
{var o;
 o=objeto;
 divi=o.name.split("_");
 tabla=nro;
 col=divi[3];
 tabla_form=eval("document.all.tabla_"+nro);
 if (typeof tabla_form!='undefined')
 {
	 filas=tabla_form.rows.length;
	 celda=eval('document.all.columna_'+tabla+'_'+0+'_'+col);
	 celda.removeNode(true);
	 celda=eval('document.all.columna_'+tabla+'_'+1+'_'+col);
	 col=celda.cellIndex;
	 celda.removeNode(true);
	 for(i=2;i < filas-1;i++)
	 {
		celda=tabla_form.rows[i].cells[col];
		celda.removeNode(true);
	 }
 }
}

function borrar_proveedor(objeto)
{
 if(typeof(objeto)!="undefined")
  o=objeto;
 else
  o=this;

 divi=o.name.split("_");
 col=divi[3];
 hflagprov=eval("document.all.hflagprov_"+col);
 hflagprov.value="borrar";

 for(t=1; t <= document.all.cant_renglones.value;t++)
 {
  borrar_columna(t,o);
  ftotal_provs(t);//recalculo el total de proveedores
 }
 cambioForm=1;//se hizo un cambio en el formulario
}

//se setea en tiempo de ejecucion
var col_prov;
//chk_prod by GACZ
//chequea que no existan 2 productos iguales con diferentes precios para un mismo proveedor
//usa la variable global @col_prov
//retorna true si no existen dos productos con diferente precio, false sino
function chk_prod()
{
	//renglones totales
	var cr=document.all.cant_renglones.value;

	//nombre de las variables
	//prefijo_#renglon_#fila_#columna;
	//#renglon ampieza en 1, #columna empieza en 2 , #fila empiezan en 2;

	//cantidad de productos para el 1er renglon que se encuentre
	var cpr1;
	var i;

	//busco el primer renglon que haya
  for (i=1; i <= cr && (typeof cpr1=='undefined') ;i++ )
  	cpr1=eval("document.all.cant_prod_reng_"+i);

  cpr1=parseInt(cpr1.value);

  for (r1=i-1; r1 < cr; r1++)
  {
	  for (fila1=2; fila1-2 < cpr1; fila1++)
	  {
			var elegido1=eval("document.all.hidden_precio_elegido_"+r1+"_"+fila1+"_"+col_prov);
			if (typeof elegido1=='undefined')
				continue;
			elegido1=elegido1.value;
			if (elegido1==1)
			{
  			var idprod1=eval("document.all.hidproducto_"+r1+"_"+fila1+".value");
			  var precio1=eval("parseFloat(document.all.texto_precio_"+r1+"_"+fila1+"_"+col_prov+".value)");
				//@r2=#renglon o tabla
				for (r2=r1+1; r2 <= cr; r2++)
				{
					//cantidad de productos por renglon
				  var cpr2=eval("document.all.cant_prod_reng_"+r2);
				  if (typeof cpr2=='undefined')
				  	continue;
				  cpr2=cpr2.value;

					for (fila2=2; fila2-2 < cpr2; fila2++)
					{
						var elegido=eval("document.all.hidden_precio_elegido_"+r2+"_"+fila2+"_"+col_prov);
						if (typeof elegido=='undefined')
							continue;
						elegido=elegido.value;
						if (elegido==1)
						{
						  var idprod2=eval("document.all.hidproducto_"+r2+"_"+fila2+".value");
						  var precio2=eval("parseFloat(document.all.texto_precio_"+r2+"_"+fila2+"_"+col_prov+".value)");
//		  			  alert(precio1+"  "+precio2);
						  if (idprod1==idprod2 && precio1!=precio2)
						  	return false;
						}
					}
				}
			}
		}
  }
  return true;
}

//se setea en tiempo de ejecucion
var id_renglon_tmp;
var renglones=new Array();//se asigna valores mas abajo en el while de proveedores
//depende de variable globales al momento de invocarse
//hidden @id_renglon_prop
//var @renglones
function recuperar_renglones()
{
	if (!window.event.srcElement.disabled)
	{
		if (window.event.srcElement.value==2)
		{
		 document.forms[0].id_renglon_prop.value=renglones[document.forms[0].id_proveedor.value];
		 if (!chk_prod())
		 {
		 	var msg="Usted tiene 2 Productos iguales\n";
		 	msg+="con diferentes precios para un mismo PROVEEDOR";
	 		alert(msg);
	 		return false;
		 }
		}
		else
			document.forms[0].id_renglon_prop.value=id_renglon_tmp;

		win.show();//se oculta cuando termina la funcion check_rowcant()
		document.forms[0].doForm.value='OC';//indica a check_rowcant que cuando termine debe hacer la orden de compra
		check_rowCant(0,true);//checkear todos los renglones
	}
//	alert (id_renglon_tmp);
//	alert (document.forms[0].id_renglon_prop.value);
}
function doOC()
{
	flagguardar=1;
	document.forms[0].target='_blank';
	document.forms[0].submit();
	document.forms[0].target='';
	setTimeout("document.location.href=document.location.href;", 1000);
}

function borrar_renglones()
{
	for (i=1,j=0; i <= document.forms[0].cant_renglones.value && document.getElementById('tabla_renglones').rows.length >2; i++)
	{
		if (typeof eval("document.forms[0].chk_"+i)!='undefined')
		{
			if (eval("document.forms[0].chk_"+i+".checked"))
			{
				//borro la tabla con los productos
				document.getElementById('tabla_'+i).removeNode(true);

				renglonprop=eval("document.all.hidden_idrenglon_"+i);

				//guardo una lista de los renglones borrados
				if (renglonprop.value!='')//vacio si es nuevo
					document.forms[0].hborrar_reng.value+=","+renglonprop.value;

				//borro el renglon con el checkbox
		    document.getElementById('tabla_renglones').deleteRow(j+2);
			}
			else
			 j++;
		}
	}
	if (document.getElementById('tabla_renglones').rows.length ==2)
	{
	 document.forms[0].bborrar.disabled=true;
	 document.forms[0].bcargar.disabled=true;
	}
	cambioForm=1;//se hizo un cambio en el formulario
}

function exportar_renglones()
{
	var exportar = new Array(0);
	for (i=1,j=0; i <= document.forms[0].cant_renglones.value && document.getElementById('tabla_renglones').rows.length >2; i++)
	{
		if (typeof eval("document.forms[0].chk_"+i)!='undefined')
		{
			if (eval("document.forms[0].chk_"+i+".checked"))
			{
				renglonprop=eval("document.all.hidden_idrenglon_"+i);

				//guardo una lista de los renglones a exportar
				if (renglonprop.value!='')//vacio si es nuevo
					exportar.push(renglonprop.value);
			}
		}
	}
	document.forms[0].hexportar_reng.value = exportar.join(",");
	if (document.forms[0].hexportar_reng.value == '') {
		alert("Debe seleccionar al menos un renglon para exportar.");
		return false;
	}
	if (document.forms[0].entrega_estimada_producto.value == '') {
		alert("Falta la fecha de entrega estimada de los productos.");
		return false;
	}
	return true;
}

function borrar_prod(nro_renglon)
{
	chk=eval("document.forms[0].chk_bprod_"+nro_renglon);
	tabla=eval("document.all.tabla_"+nro_renglon);
	total=eval("document.all.cant_prod_reng_"+nro_renglon);
	row_offset=2;
	if (typeof chk!='undefined')
	{
		if (typeof chk.length!='undefined')
			for (i=0,j=0; i < chk.length ; j++)
			{
				if (chk[i].checked)
				{
					//guardo una lista de los productos borrados
					//si el valor es nulo => es producto alternativo que se agrego en esta sesion
					if (chk[i].value!='')
						document.all.hborrar_prod.value+=","+chk[i].value;
					tabla.deleteRow(row_offset+i);
				}
				else
				 i++;
			}
		else if (chk.checked)
		{
				//guardo una lista de los productos borrados
				//si el valor es nulo => es producto alternativo que se agrego en esta sesion
				if (chk.value!='')
						document.all.hborrar_prod.value+=","+chk.value;

				tabla.deleteRow(row_offset);
		}

		if (tabla.rows.length==row_offset+1)
				eval("document.forms[0].beliminar_"+nro_renglon+".disabled=true;");

		//RECALCULA LOS TOTALES PARA TODOS LOS PROVEEDORES
		for (i=2; i-2 < cant_prov; i++)
		{
			monto_total=eval("document.all.monto_"+nro_renglon+"_"+i);
			if (typeof monto_total!='undefined')
				monto_total.value=total_renglon_prov(nro_renglon,i);
		}
		cambioForm=1;//se hizo un cambio en el formulario
	}
}
//variable que hace referencia a la ventana con productos
var wproductos;

function add_prod(nro_renglon)
{
	var tabla=eval("document.all.tabla_"+nro_renglon);
	var total=eval("document.all.cant_prod_reng_"+nro_renglon);
	var nro_prod=parseInt(total.value)+2;//porque los indices de producto empiezan en dos
	total.value=parseInt(total.value)+1;//incremento el total de productos
	var cols=tabla.rows[1].cells.length;
	//inserto al final
	var fila=tabla.insertRow(tabla.rows.length-1);
  //falta darle el valor al id_producto
 	var id_renglon_prop = eval("document.all.hidden_idrenglon_"+nro_renglon);

  fila.onclick=function (){if(window.event.srcElement.tagName!='INPUT'){alternar_color(this,'#a6c2fc');}};
	fila.insertCell(0).innerHTML="<input type='checkbox' name='chk_bprod_"+nro_renglon+"' value=''><input type='hidden' name='hidproducto_"+nro_renglon+"_"+nro_prod+"' value="+wproductos.document.all.id_producto_seleccionado.value+">";
  fila.insertCell(1).innerHTML="<input type='text' name='cantidad_prod_"+nro_renglon+"_"+nro_prod+"' value=1 size=2 style='text-align:right' onchange='cambiar_precios("+nro_renglon+","+nro_prod+");ftotal_reng("+nro_renglon+")' ><input type='hidden' name='hadicional_"+nro_renglon+"_"+nro_prod+"' value=1>";
  fila.insertCell(2).innerHTML="<input type='text' name='nbreproducto_"+nro_renglon+"_"+nro_prod+"' readonly value='"+wproductos.document.all.nombre_producto_elegido.value+"' title='"+wproductos.document.all.nombre_producto_elegido.value+"' size=35 /><input type='button' name='log' value='L' title='Historial de cambios de precio' onclick=\"window.open('<?=encode_link('ver_log_producto_presupuesto.php',array())?>&id_producto="+wproductos.document.all.id_producto_seleccionado.value+"&id_renglon_prop="+id_renglon_prop.value+"','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=450,resizable=1');\"><input type=button name=bhistorial value='H' onclick=\"window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto="+wproductos.document.all.id_producto_seleccionado.value+"','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');\" />"+"<input type='button' name='bdescadic' value='A' title='Agregar descripción adicional del producto' onclick=\"window.open('../ord_compra/desc_adicional.php?nombres=hdescorig_"+nro_renglon+"_"+nro_prod+",hdescadic_"+nro_renglon+"_"+nro_prod+",nbreproducto_"+nro_renglon+"_"+nro_prod+"&title=1','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400');\">"+"<input type='hidden' name='hdescorig_"+nro_renglon+"_"+nro_prod+"' value='"+wproductos.document.all.nombre_producto_elegido.value+"'/>"+"<input type='hidden' name='hdescadic_"+nro_renglon+"_"+nro_prod+"' value=''/>";
  fila.insertCell(3).innerHTML="<table width=100%><tr>"+
  "<td align=left>U$S</td>"+
  "<td align=right><input type=text name=precio_prod_"+nro_renglon+"_"+nro_prod+" onkeypress=\"return filtrar_teclas(event,'0123456789.')\" value="+wproductos.document.all.precio_producto_elegido.value+" size=5 style='text-align:right' onchange=\"ftotal_reng("+nro_renglon+")\" /></td>"+
  "</tr>"+
  "</table>";
  fila.style.backgroundColor='<?=$bgcolor2;?>';
	var col_tabla=4;
  for (var col=2; col-2 < cant_prov; col++ )
  {
  	//se resta 2  a col pq los nombres empiezan con col >=2
	  //var hflagprov=eval("document.all.hflagprov_"+col);
	  //if (hflagprov.value=="borrar")
	  if (!col_exist(col))
	  	continue;
	  //mycurrent_cell=fila.insertCell(col_tabla++);//incremento la columna de la tabla
	  var oimg=eval("document.all.imagen_"+col+"_"+nro_renglon);//objeto tipo IMG
	  var display;
	  if (typeof oimg.show=="undefined" || oimg.show)
	  	display="inline";
	  else
	  	display="none";
	  fila.appendChild(create_prodprovcell(nro_renglon,nro_prod,col,display=='inline'));
  }
  ftotal_reng(nro_renglon);

	cambioForm=1;//se hizo un cambio en el formulario
}//de function add_prod(nro_renglon)


//crea y retorna una celda en la interseccion producto-proveedor
//@itabla indice de la tabla
//@irow indice de la fila o producto
//@icol indice de la columna o proveedor
//@show bool indica si debe aparecer visible u oculto en la columna
function create_prodprovcell(itabla,irow,icol,show)
{
		//alert(itabla+","+irow+","+icol+","+show);
	  var display=(typeof show=='undefined' || show)?'inline':'none';//por defecto aparece visible

	  var mycurrent_cell=document.createElement("TD");
	 	mycurrent_cell.setAttribute("align","center");
	 	mycurrent_cell.setAttribute("id","columna_"+itabla+"_"+irow+"_"+icol);

	 	//texto U$S
	 	var span=document.createElement("span");
		span.appendChild(document.createTextNode("U$S"));
		span.style.display=display;
		mycurrent_cell.appendChild(span);

		//input del precio
		var texto=document.createElement("INPUT");
		texto.setAttribute("type","text");
		texto.setAttribute("name","texto_precio_"+itabla+"_"+irow+"_"+icol);
		texto.setAttribute("id","texto_precio_"+itabla+"_"+irow+"_"+icol);
		texto.onchange=function () {cambiar_precio(itabla,irow,icol)};
		texto.onkeypress=function(){return filtrar_teclas(event,'0123456789.')};
		texto.onclick=function(){this.select()};
		texto.setAttribute("size","7");
		texto.setAttribute("value","");
		texto.style.display=display;
		mycurrent_cell.appendChild(texto);

		//hidden para guardar si esta chequeado
		var elegido=document.createElement("INPUT");
		elegido.setAttribute("type","hidden");
		elegido.setAttribute("name","hidden_precio_elegido_"+itabla+"_"+irow+"_"+icol);
		elegido.setAttribute("id","hidden_precio_elegido_"+itabla+"_"+irow+"_"+icol);
		elegido.setAttribute("value",0);
		mycurrent_cell.appendChild(elegido);

		//boton para cargar comentario
		var btn=document.createElement("INPUT");
		btn.setAttribute("type","button");
		btn.setAttribute("name","boton_comentario_"+itabla+"_"+irow+"_"+icol);
		btn.setAttribute("id","boton_comentario_"+itabla+"_"+irow+"_"+icol);
		btn.setAttribute("value","C");
		btn.onclick=cargar_ventana;
		btn.style.cursor="hand";
		btn.style.display=display;
		mycurrent_cell.appendChild(btn);

		//boton para elegir precio de proveedor
		btn=document.createElement("INPUT");
		btn.setAttribute("type","button");
		btn.setAttribute("name","boton_precio_"+itabla+"_"+irow+"_"+icol);
		btn.setAttribute("id","boton_precio_"+itabla+"_"+irow+"_"+icol);
		btn.setAttribute("value","E");
		btn.style.cursor="hand";
		btn.onclick=function (){elegir_precio(itabla,irow,icol)};
		btn.style.display=display;
		mycurrent_cell.appendChild(btn);

		//<input type="button" name="bcant_<?=$i;?>_<?=$j?>_<?=$col_prov?>" value="U" size="2" title="Unidades: <?=($resultado_precios->fields['cantidad']?$resultado_precios->fields['cantidad']:$resultado->fields['cantidad']*$resultado_producto->fields['cantidad'])?> (Cantidad a comprar o comprada)" onclick="document.all.menuCantbaceptar.onclick=function() {fnBtnAceptar(document.all.hcantidadprov_<?=$i?>_<?=$j?>_<?=$col_prov?>)}; show_menuCant(document.all.hcantidadprov_<?=$i?>_<?=$j?>_<?=$col_prov?>)">
		//boton para cambiar la cantidad
		btn=document.createElement("INPUT");
		btn.setAttribute("type","button");
		btn.setAttribute("name","bcant_"+itabla+"_"+irow+"_"+icol);
		btn.setAttribute("id","bcant_"+itabla+"_"+irow+"_"+icol);
		btn.setAttribute("value","U");
		btn.style.cursor="hand";
		var oprod=getProdNode(itabla,irow);
		var oreng=getRenglonNode(itabla);
		btn.title="Unidades: "+(oprod.cantidad*oreng.cantidad)+" (Cantidad a comprar o comprada)";
		btn.onclick=function (){document.all.menuCantbaceptar.onclick=function() {fnBtnAceptar(eval("document.all.hcantidadprov_"+itabla+"_"+irow+"_"+icol))}; show_menuCant(eval("document.all.hcantidadprov_"+itabla+"_"+irow+"_"+icol))};
		btn.style.display=display;
		mycurrent_cell.appendChild(btn);

		//<input type="hidden" name="hcantidadprov_<?=$i?>_<?=$j?>_<?=$col_prov?>" value="<?=($resultado_precios->fields['cantidad']?$resultado_precios->fields['cantidad']:$resultado->fields['cantidad']*$resultado_producto->fields['cantidad'])?>">
		//hidden cantidad proveedor
		var hidden=document.createElement("INPUT");
		hidden.setAttribute("type","hidden");
		hidden.setAttribute("name","hcantidadprov_"+itabla+"_"+irow+"_"+icol);
		hidden.setAttribute("id","hcantidadprov_"+itabla+"_"+irow+"_"+icol);
		hidden.setAttribute("value",(oprod.cantidad*oreng.cantidad));
		mycurrent_cell.appendChild(hidden);

		//hidden comentario
		hidden=document.createElement("INPUT");
		hidden.setAttribute("type","hidden");
		hidden.setAttribute("name","hidden_comentario_"+itabla+"_"+irow+"_"+icol);
		hidden.setAttribute("id","hidden_comentario_"+itabla+"_"+irow+"_"+icol);
		hidden.setAttribute("value"," ");
		mycurrent_cell.appendChild(hidden);

		return mycurrent_cell;
}

//variable que me dice cuantos proveedores max hay
var cant_prov=0;

//esta funcion solo suma los totales de la fila de totales por proveedor
function ftotal_provs(nro_renglon)
{
	var ototalp=eval("document.all.total_provs_"+nro_renglon);//objeto span
	if (typeof ototalp=='undefined')
		return;
	var totalp=0;
	for (var i=2; i-2 < cant_prov; i++)
	{
  	//hflagprov=eval("document.all.hflagprov_"+i);
  	//if (hflagprov.value=="borrar") //si el proveedor esta oculto o se borro
  	if (!col_exist(i))
  		continue;//paso a la siguiente iteracion
		ototalpX=eval("document.all.monto_"+nro_renglon+"_"+i);//objeto INPUT TEXT
		if (typeof ototalpX!="undefined")
			totalp+=parseFloat(ototalpX.value.replace(",","."));
	}

	//CALCULA EL TOTAL DE LOS PROVEEDORES PARA UN RENGLON
	var cantr=eval("parseInt(document.all.cant_renglon_"+nro_renglon+".value)");
	totalp=totalp/cantr;
	ototalp.innerHTML=(totalp.toFixed(2)).replace(".",",");
}

function ftotal_reng(nro_renglon)
{
		var ototalr=eval("document.all.total_reng_"+nro_renglon);//objeto span
		var totalprod=parseInt(eval("document.all.cant_prod_reng_"+nro_renglon+".value"));//int
		var totalr=0;

		for (i=2; i-2 < totalprod; i++ )
		{
		 oprecio=eval("document.all.precio_prod_"+nro_renglon+"_"+i); //objeto span o input si es un producto nuevo
		 if (typeof oprecio!="undefined")
		 {
		 	cant=parseInt(eval("document.all.cantidad_prod_"+nro_renglon+"_"+i+".value")); //int
			totalr+=((oprecio.tagName=='SPAN')?parseFloat((oprecio.innerHTML.replace(".","")).replace(",",".")):parseFloat(oprecio.value=oprecio.value.replace(",",".")))*cant;
			//totalr+=parseFloat((oprecio.innerHTML.replace(".","")).replace(",","."));
		 }
		}
		ototalr.innerHTML=(totalr.toFixed(2)).replace(".",",");
}

function actualizar_cant(nro_renglon)
{
	ocant1=eval("document.all.cant_renglon_"+nro_renglon);//textfield
	ocant2=eval("document.all.cantr_"+nro_renglon);//span
	ocant2.innerHTML=ocant1.value;
}

var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function change_image(colprov)
{
	cant_renglones=eval("document.all.cant_renglones.value");
	//si hay mas de una
	for (var i=1; i <= cant_renglones ; i++)
	{
		oimg=eval("document.all.imagen_"+colprov+"_"+i);//objeto tipo IMG
		if (typeof oimg!='undefined')
		{
			//si por defecto esta visible o esta visible
			if (typeof oimg.show=='undefined' || oimg.show)
			{
				oimg.show=0;
				oimg.src=img_cont;
				oimg.title='Mostrar Proveedor';
			}
			else
			{
				oimg.show=1;
				oimg.src=img_ext;
				oimg.title='Ocultar Proveedor';
			}
			//img.td.tr.tbody.table.td.tr.tbody.table
			//alert(oimg.parentNode.parentNode.parentNode.parentNode.parentNode.cellIndex);
			hide_col(oimg.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode,oimg.parentNode.parentNode.parentNode.parentNode.parentNode.cellIndex,oimg.show);
		}
	}
}

//HIDE_COL BY GACZ
//funcion que oculta o muestra una columna de una tabla
//@otabla es el objeto tabla
//@colindex es el #columna (0..n) para ocultar debe ser valido
//@show si es 0 (cero) oculta, sino hace visible
function hide_col(otabla,colindex,show)
{
	otabla.rows[0].cells[colindex].childNodes[0].rows[1].cells[0].style.display=(show)?'block':'none';
	//desde la primer fila y sin contar la ultima(donde estan los botones)
	for (j=1; j < otabla.rows.length-1; j++ )
		show_hide(otabla.rows[j].cells[colindex+2],show);
}
//SHOW_HIDE BY GACZ
//funcion que oculta o muestra los objetos dentro de un td
//@otd es un objeto tipo td
//@show si es 0 (cero) oculta, sino hace visible
function show_hide(otd,show)
{
	display=(show)?'inline':'none';
	for (i=0; i < otd.childNodes.length; i++ )
	{
		if(typeof otd.childNodes[i].tagName!='undefined')
			otd.childNodes[i].style.display=display;
	}
}
function guardar_desc()
{
	if (wdescadic.control())
			cambioForm=1;//se hizo un cambio en el formulario
}

/*cambiar_prod BY GACZ
	cambia un producto por otro
	@renglon es un entero que indica la tabla en la que se encuentra el objeto
	@fila es un entero que indica la fila dentro de la tabla en la que se encuentra el objeto
*/
function cambiar_prod(renglon,fila)
{
 oidprodpres=eval("document.all.producto_"+renglon+"_"+fila);//id producto_presupuesto
 oidprod=eval("document.all.hidproducto_"+renglon+"_"+fila);
 oidprod_orig=eval("document.all.hidproducto_orig_"+renglon+"_"+fila);
 onbreprod=eval("document.all.nbreproducto_"+renglon+"_"+fila);
 odesc_orig=eval("document.all.hdescorig_"+renglon+"_"+fila);
 odesc_adic=eval("document.all.hdescadic_"+renglon+"_"+fila);

 //si el producto es nuevo (no esta en la BD) y se cambio por otro
 //guardo el id_producto original
 if (oidprod_orig.value=='' && oidprodpres.value=='')
 	oidprod_orig.value=oidprod.value;
 oidprod.value=wcambiarprod.document.all.id_producto_seleccionado.value;//le asigno el nuevo id
 onbreprod.value=wcambiarprod.document.all.nombre_producto_elegido.value;//le asigno el nuevo nombre
 onbreprod.title=wcambiarprod.document.all.nombre_producto_elegido.value;//le asigno el nuevo nombre
 odesc_orig.value=wcambiarprod.document.all.nombre_producto_elegido.value;//le asigno el nuevo nombre
 odesc_adic.value="";

}//de function cambiar_prod(renglon,fila)

//col_exist() by GACZ
//me dice si el proveedor existe logicamente
function col_exist(nro_col)
{
	hflagprov=eval("document.all.hflagprov_"+nro_col);
  if (typeof hflagprov=='undefined' || hflagprov.value=="borrar") //si el proveedor esta oculto o se borro
  	return false;//el proveedor o columna, no existe o no existira
  else
  	return true;//el proveedor o columna, si existe
}

//row_exist() by GACZ
//me dice si existe la fila de un producto, en una determinada tabla
function row_exist(itabla,irow)
{
	var obj=eval("document.all.hidproducto_"+itabla+"_"+irow);

  if (typeof obj=='undefined') //si existe el producto
  	return false;//no existe la fila
  else
  	return true;//si existe
}

//se usa para que no surja el popup de cambios en la pagina
var flagguardar=0;
//variable para identificar la ventana de descripcion adicional
var wdescadic=new Object();
wdescadic.closed=1;
//variable para identificar la ventana de cargar proveedor
var wprov=new Object();
wprov.closed=1;
//variable para identificar la ventana de cambiar producto (es la misma de agregar_productos)
var wcambiarprod=new Object();
wcambiarprod.closed=1;

</script>
<style type="text/css">
<!--
.borde_datos {
border: thin groove;
}
-->
</style>
<link rel="stylesheet" href="../../lib/estilos2.css" type="text/css" media="all">
<script src="../../lib/fns.js"></script>
<script src="../../lib/checkform.js"></script>
<!-- este tipo de declaracion funciona unicamente en IE 5.5+ -->
<SCRIPT FOR=window EVENT=onbeforeunload>
	//if (!flagguardar && testDefaultValues(document.forms[0]))
	if (!flagguardar && cambioForm)
		{
			return "SE HAN DETECTADO CAMBIOS EN LA PAGINA";
		}

</SCRIPT>

</head>
<?=$html_header;?>


<!--<body bgcolor="#E0E0E0" onkeypress="control_boton();" onload="this.focus()">-->
<?

cargar_calendario();

if ($id_lic_prop!=-1)
{
	//traer todos los renglones de la licitacion
	if ($btraer=$_POST['btraer'])
	{
		$add_rigth="right ";
		//renglones en estado orden de compra
		$tabla_renglon="(select distinct r.id_renglon,roc.cantidad,r.titulo,r.codigo_renglon ";
		$tabla_renglon.="from renglones_oc roc ";
		$tabla_renglon.="join renglon r using(id_renglon) where id_licitacion=".$resultado_licitacion->fields['id_licitacion']." and id_subir=$id_subir) ";

	}
	//sino solo los renglones del presupuesto
	else
	  $tabla_renglon="renglon";

	$sql ="select id_renglon_prop,r.id_renglon,rp.cantidad,r.cantidad as cantidadr,r.titulo,r.codigo_renglon ";
	$sql.="from ";
	$sql.="(select * from renglon_presupuesto_new where renglon_presupuesto_new.id_licitacion_prop=$id_lic_prop) rp ";
	$sql.="$add_rigth join $tabla_renglon r using(id_renglon) ";
	$sql.="order by id_renglon";

	$querylog="Select usuarios.nombre,usuarios.apellido,fecha,descripcion from log_presupuesto "
		."left join usuarios USING (id_usuario) where id_licitacion_prop=$id_lic_prop order by fecha DESC";
	$log=sql($querylog) or fin_pagina();
}
//nuevo presupuesto
else
{
	$sql ="select distinct r.id_renglon,roc.cantidad,r.cantidad as cantidadr,r.titulo,r.codigo_renglon ";
	$sql.="from ";
	$sql.="renglon r ";
	$sql.="join renglones_oc roc using(id_renglon) ";//renglones en estado orden de compra
	$sql.="where id_licitacion=$id and id_subir=$id_subir ";
	$sql.="order by id_renglon";

}
$resultado=sql($sql) or die ($db->ErrorMsg()."<br>".$sql);

//trae los proveedores de los renglones de presupuesto
$sql ="select distinct razon_social,proveedor.id_proveedor ";
$sql.="from renglon_presupuesto_new ";
$sql.="join producto_presupuesto_new using(id_renglon_prop) ";
$sql.="join producto_proveedor_new using(id_producto_presupuesto) ";
$sql.="join general.proveedor using(id_proveedor) ";
$sql.="where id_licitacion_prop=$id_lic_prop ";
$sql.="order by razon_social ";
//die ("<br>".$sql);
$resultado_prov=sql($sql) or die ($db->ErrorMsg()."<br>".$sql);


/*/recupero el total de cada renglon de acuerdo a los precios de los proveedores
$q ="select rp.id_renglon_prop,rp.id_renglon,rp.cantidad,sum(pprov.monto_unitario*pp.cantidad) as total ";
$q.="from licitaciones.producto_proveedor_new pprov ";
$q.="join licitaciones.producto_presupuesto_new pp using(id_producto_presupuesto) ";
$q.="join licitaciones.renglon_presupuesto_new rp using(id_renglon_prop) ";
$q.="where activo=1 AND id_licitacion_prop=$id_lic_prop ";
$q.="group by rp.id_renglon_prop,rp.id_renglon,rp.cantidad ";
$q.="order by id_renglon";
if ($id_lic_prop)
$total_provs=sql($q) or fin_pagina();
*/
$hora=substr($resultado_licitacion->fields['fecha_cotizacion'],11,2);
$minuto=substr($resultado_licitacion->fields['fecha_cotizacion'],14,2);
//proveedores
$link=encode_link("detalle_presupuesto.php",array('ID'=>$id,"id_lic_prop"=>$id_lic_prop,"id_subir"=>$id_subir,"nro_orden_cliente"=>$nro_orden_cliente,"pagina"=>$pagina));

//Log de presupuestos
if ($log) {
	echo "<div id=ma style='height: 50px;overflow-y: auto;width: 99%;'>\n";
	while (!$log->EOF) {
		$fecha=fecha(substr($log->fields["fecha"],0,10));
		$hora=substr($log->fields["fecha"],11,8);
		echo "<b>".$log->fields["descripcion"]." por ".$log->fields["nombre"]." ".$log->fields["apellido"]." el $fecha a las $hora.</b><br>\n";
		$log->MoveNext();
	}
	echo "</div><br>\n";
}

?>
<form name="form1" action="<?=$link?>" method="POST" onload="this.focus()" >
<div id="menuOC" class="buttonmenu">
<strong>Opciones para "Mover"</strong>
<ul>
<li value=1 title="OC para este Renglon">Este Renglon</li>
<li value=2 title="OC para Todos Los Renglones" <?=($btraer)?" disabled ":"" ?>>Todos</li>
</ul>
</div>

<div id="menuCant" onkeypress="if (window.event.keyCode==27) document.all.menuCantbcancelar.click()" onclick="document.all.menuCantprovcant.select();window.event.cancelBubble=true//para que no se oculte el div" nowrap style="background-color:<?=$bgcolor2?>; top:0;left:0;z-index:2;position:absolute;visibility:hidden;border-style:solid;border-width:1px;">
<center>
<b>¿Cantidad a comprar?</b>
<br>
<br>
<input type="text" name="menuCantprovcant" value="1" style="text-align:right" size="4" onkeypress="if (window.event.keyCode==13) {document.all.menuCantbaceptar.click();  return false;} return filtrar_teclas(event,'0123456789');  ">
<br>
<br>
<input type="button" name="menuCantbaceptar" value="Aceptar" style="width:60px">&nbsp;<input type="button" name="menuCantbcancelar" value="Cancelar" onclick="hide_menuCant()" style="width:60px">
</center>
</div>

<div id="pseudoWin" nowrap style="visibility:hidden;background-color:<?=$bgcolor2?>;position:absolute;width:200px;heigth:100px;top:0;left:0;z-index:2;border:outset;border-width:3px;">
<table border="0" cellpadding="5" cellspacing="5" align="center" width="100%">
<tr>
	<td width="100%" height="40%" align="center" valign="middle">
	<b><span id="msg"></span></b>
	<br><br><img src="<?="../../imagenes/wait.gif"; ?>">
	</td>
</tr>
</table>
</div>

<!--<script src="<?=LIB_DIR."/genMove.js" ?>"></script>-->
<script>
function noClick()
{
	window.event.cancelBubble=true;
	window.event.returnValue=false;
	if ((event.button==1)||(event.button==2))
		alert('Espera, estoy trabajando...');
}
function noKey()
{
		window.event.cancelBubble=true;
		window.event.returnValue=false;
		alert('Espera, estoy trabajando...');
}

var win=document.all.pseudoWin;//obj DIV

//texto por defecto de la ventanita
document.all.msg.innerText='Verificando Cantidades\nEspere por favor';
win.hide=function (){
	this.style.visibility='hidden';
	document.onmousedown=null;//bloqueo el mousedocument.onkeydown=noKey;
	document.onkeydown=null;//bloqueo el teclado
};
win.setText=function (text){
	document.all.msg.innerText=text;//obj SPAN
}
win.show=function (width,height){
	document.onmousedown=noClick;//bloqueo el mouse
	document.onkeydown=noKey;//bloqueo el teclado
	if (typeof width=='undefined')
		width=200;
	if (typeof height=='undefined')
		height=100;

	this.style.width=width;
	this.style.height=height;
//	this.style.top=parseInt((screen.height-height)/2)+'px';
	this.style.top=((screen.height-height)/2)+(document.body.scroll!='no'?document.body.scrollTop-50:0);
//top:expression(((screen.height-100)/2)+(document.body.scroll!='no'?document.body.scrollTop-50:0));
//	this.style.left=parseInt((screen.width-width)/2)+'px';
	this.style.left=(screen.width-width)/2+(document.body.scroll!='no'?document.body.scrollLeft-20:0);
	this.style.visibility='visible';
//	alert(document.body.scroll+" "+document.body.scrollTop);
}

//variable usada para saber si hubo error al controlar las cantidades
var error_cant=0;

//CHECK_ROWCANT BY GACZ
//checkea que no ingresen un cantidad de producto mayor que la maxima posible
//@irow es el indice de la fila
//@itabla es el indice de la tabla en la que debe empezar
//@check_all booleano que le indica si debe checkear una o todas las tablas
function check_rowCant(itabla,check_all,irow)
{
	if (typeof irow=='undefined')
		irow=2;
	else
		irow=parseInt(irow);

	if (typeof check_all=='undefined')
		check_all=false;

	//espero un miliseg si itabla==0
	if (typeof itabla=='undefined' || itabla==0)
	{
		//espero 1 miliseg antes de empezar
		setTimeout("check_rowCant(1,"+check_all+","+irow+")",1);//chequeo la prox. fila;
		return;
	}
	else
		itabla=parseInt(itabla);

	//verifico que existe la fila y que sea menor que el total
	if (row_exist(itabla,irow))
	{
			var cant_max=get_prodtotal(itabla,irow);

			//solo necesito checkear una columna(seleccionada), pero algunas pueden haber sido borradas
		  for (var col=2; col-2 < cant_prov; col++ )
		  {
		  	//se resta 2 a col pq los nombres empiezan con col >=2
			  if (!col_exist(col))
			  	continue;

			  //si esta elegido
				if (eval("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+col+".value==1"))
				{
					//si hay problemas con la cantidad del elegido => ninguno tiene problemas de cantidad
			  	if (!chk_cantidad(itabla,irow,col,eval("parseInt(document.all.hcantidadprov_"+itabla+"_"+irow+"_"+col+".value)")))
			  	{
			  		//PINTAR LAS FILAS CON PROBLEMAS Y SETEAR UN FLAG QUE NO DEJE SUBMIT NI HACER OC
				  	if (eval("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+col+".parentNode.parentNode.style.backgroundColor!='red'"))
			  			alternar_color(eval("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+col+".parentNode.parentNode"),'red');
			  		error_cant++;
			  	}
			  	else	if (eval("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+col+".parentNode.parentNode.style.backgroundColor=='red'"))
			  		alternar_color(eval("document.all.hidden_precio_elegido_"+itabla+"_"+irow+"_"+col+".parentNode.parentNode"),'red');

		  		break;//no hace falta revisar las demas columnas
				}
		  }
  		//chequeo la prox. fila
  		setTimeout("check_rowCant("+itabla+","+check_all+","+(irow+1)+ ")",1);
	}
	else
	{
		//si es menor que la cantidad de filas posibles
		if (typeof eval("document.all.cant_prod_reng_"+itabla)!='undefined' && irow-2 < parseInt(eval("document.all.cant_prod_reng_"+itabla+".value")))
			//chequeo la prox. fila
			setTimeout("check_rowCant("+itabla+","+check_all+","+(irow+1)+ ")",1);
		else
		{
			if (check_all && itabla < parseInt(document.all.cant_renglones.value))
			//chequeo la prox. tabla
			setTimeout("check_rowCant("+(itabla+1)+","+check_all+")",1);
			else
			{
				//fin recursion
				win.hide();
				//si tiene un valor se debe mostrar un mensaje
				if (error_cant > 0)
				{
					error_cant=0;
					document.forms[0].doForm.value='';
					alert("Por favor Chequee las cantidades a comprar,\n se encontraron cantidades incorrectas\n");
				}
				//no hubo errores
				else
				{
					//dependiendo de donde se llame
					switch (document.forms[0].doForm.value)
					{
						//si se llamo antes de guardar
						case 'Guardar':		document.forms[0].submit(); break;
						//si se llamo antes de hacer una Orden de Compra
						case 'OC':	doOC();break;
					}
				}
			}
		}
	}
}

function check_form()
{
	if (document.all.titulo.value=='')
	{
		alert('Falta completar el titulo del Presupuesto');
		return false;
	}
	return true;
}
//funcion que muestra el menu de cantidad
//@ocant_origen es el text desde donde tomara la cantidad a mostrar en el menu
function show_menuCant(ocant_origen)
{
	omenu=document.all.menuCant;
	document.all.menuCantprovcant.value=ocant_origen.value;
	var x=event.srcElement.offsetLeft;
	var y=event.srcElement.offsetTop;
	var oparent=event.srcElement.offsetParent;
	while (eval(oparent))
	{
		x+=oparent.offsetLeft;
		y+=oparent.offsetTop;
		oparent=oparent.offsetParent;
	}

	//sale en la punta superior izquiera(sobre el objeto) del objeto que llama al menu
	var top=y +'px';
	var left=x +'px';

	//sale en la punta superior derecha
	var top=y +'px';
	var left=(x+event.srcElement.offsetWidth)+'px';

	//sale en la punta inferior derecha
	var top=(y+event.srcElement.offsetHeight)+'px';
	var left=(x+event.srcElement.offsetWidth)+'px';

	//sale en la punta inferior izquierda
	var top=(y+event.srcElement.offsetHeight)+'px';
	var left=(x+event.srcElement.offsetWidth-omenu.offsetWidth)+'px';

	omenu.style.top = top;
	omenu.style.left = left;
	omenu.style.visibility='visible';
//	omenu.focus();
//	document.all.menuCantprovcant.select();//se mueve la pantalla para hacer foco en el anterior

	window.event.cancelBubble=true;//para que no se siga ejecutando en la jerarquia de objetos
	document.onclick=hide_menuCant;
}

//funcion que oculta el menu de cantidades
function hide_menuCant()
{
	document.all.menuCant.style.visibility='hidden';
	document.onclick = null;
}

//funcion del boton aceptar del menu para ingresar cantidades por proveedor
//@ocant_dest es el text donde se guardara la cantidad que ira a la base de datos
function fnBtnAceptar(ocant_dest)
{
	if (parseInt(document.all.menuCantprovcant.value)<=0)
	{
		alert('Por favor ingrese una cantidad valida');
		return false;
	}
	var indices=ocant_dest.name.split("_");
	//si esta elegido
	if (eval("document.all.hidden_precio_elegido_"+indices[1]+"_"+indices[2]+"_"+indices[3]+".value==1"))
	{
		if (!chk_cantidad(indices[1],indices[2],indices[3],parseInt(document.all.menuCantprovcant.value)))
		{
   		alert("Verifique las cantidades\n no se puede elegir el producto con la cantidad indicada");
   		return false;
		}
	}

	ocant_dest.value=document.all.menuCantprovcant.value;
	eval("document.all.bcant_"+indices[1]+"_"+indices[2]+"_"+indices[3]+".title="+'"Unidades: '+ocant_dest.value+' (Cantidad a comprar o comprada)"');

	//RECALCULAR EL TOTAL POR PROVEEDOR
	if(elegido(indices[1],indices[2],indices[3]))
	{
		cambiar_precio(indices[1],indices[2],indices[3]);
		ftotal_provs(indices[1]);
	}
	cambioForm=1;
	hide_menuCant();
}

//@sumar contiene la cantidad a sumar al total ya elegido
//checkea que no se ingrese una cantidad mayor de la posible
function chk_cantidad(nro_tabla,fila,column,sumar_cant)
{
//boton_precio_#tabla_#fila_#col

	 var j=0;
	 table = document.getElementById("tabla_"+nro_tabla);
	 col=table.rows[0].cells.length;
	 var acum_cant=0;//acumulador de cantidades
	 var total_cant=get_prodtotal(nro_tabla,fila);//cantidad total para el producto
	 for(j=2;j < col;j++)
	 {
	  if (!col_exist(j))
	  		continue;//paso a la siguiente iteracion

	  //alert("col="+j);
	  hidden_precio=eval("document.all.hidden_precio_elegido_"+nro_tabla+"_"+fila+"_"+j);
		//solo sumo la cantidad si el proveedor no es el que se va a elegir
	  if(column!=j && hidden_precio.value==1)
	  	acum_cant+=eval("parseInt(document.all.hcantidadprov_"+nro_tabla+"_"+fila+"_"+j+".value)");

    //si esta elegido y pasa la cantidad total
    if(acum_cant+sumar_cant > total_cant)
	    return false;

	 }
	 return true;//todo bien
}
//@return la cantidad total posible para un producto
//@itabla es el indice de la tabla
//@iprod es el indice del producto dentro de la tabla
function get_prodtotal(itabla,iprod)
{
	 var ocantr=eval("document.all.cant_renglon_"+itabla);//obj text: cantidad del renglon
	 var oh_cantprod=eval("document.all.cantidad_prod_"+itabla+"_"+iprod);//obj hidden: cantidad del producto
	 return	parseInt(ocantr.value)*parseInt(oh_cantprod.value);
}

</script>

<input type="hidden" name="cant_renglones" value="<?=$resultado->recordcount();?>">
<input type="hidden" name="valor_boton">
<input type="hidden" name="id_entrega_estimada" value="<? echo $id_entrega_estimada; ?>">
<table width="100%"  border="0" bgcolor=<?=$bgcolor2?>>
  <tr>
    <td align="left" >
       <b> Licitación ID </b>
    </td>
    <td align=left>
       <font color="Blue" size="2">
       <a href='<?=encode_link("../licitaciones/licitaciones_view.php",array('cmd1'=>'detalle',"ID"=>$id));?>'><? echo $id; ?></a>
       </font>
    </td>
    <td align="left" >
    <b>Entidad:
    </td>
    <td align=left>
     <font color="Blue" size="2"><? echo $resultado_licitacion->fields['nombre_entidad']; ?></font>
    </td>
  </tr>
  <tr>
    <td align="left" >
    <b>Titulo:</b>
    </td>
    <td align=left width="20%">
    <input type="text" name="titulo"  style="border-style:none;color:blue;width:90%"  value="<?= $resultado_licitacion->fields['titulo']; ?>" />
    <!--background-color:transparent; -->
    </td>
    <td align="left" >
    <font color="red" size="3"><b>Vencimiento OC:</b></font>
    </td>
    <td align=left>
     <font color="red" size="3"><b><?=Fecha($resultado_licitacion->fields['vence_oc']); ?></b></font></td>
  </tr>
  <tr>
    <td align="left" >
    <b>Dolar Licitacion:</b>
    </td>
    <td align=left>
    <font color="Blue" size="2">$ <?=formato_money($resultado_licitacion->fields['valor_dolar_lic']); ?></font>
    </td>
    <td align="left" >
    <b>Seguimiento de Orden: </b>
    </td>
    <td align=left>
    <font color="Blue" size="2" ><a href="<?=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$id_subir,"solo_lectura"=>1));?>" target="_blank"><?=$resultado_licitacion->fields['nro_orden']?></font>
    </td>
  </tr>
  <tr>
	<td align='left' colspan='4'>
		<b>Fecha de entrega estimada de los productos:&nbsp;<input type=text name=entrega_estimada_producto value='<? echo Fecha($resultado_licitacion->fields['entrega_estimada_producto']); ?>' size=10 maxlength=10>
		<? echo link_calendario("entrega_estimada_producto"); ?></b>
	</td>
  </tr>
</table>
<input type="hidden" name="id_lic_prop" value="<? echo $id_lic_prop; ?>">
<input type="hidden" name="hnro_seg" value="<?=$resultado_licitacion->fields['nro']?>">

<table id='tabla_renglones' width="100%" class="bordes" cellspacing="2" align="center">
<tr>
   <td colspan=5 id=mo>
   Renglones a Presupuestar
   </td>
</tr>
<tr id="mo">
<td align="center">&nbsp;</td>
<td width="5%" align="center"><b>Cant.</b></td>
<td width="45%" align="center"><b>Reng.</b></td>
<td width="50%" align="center"><b>Descripcion</b></td>
</tr>
<?
$i=1;


while (!$resultado->EOF) //traigo los renglones
{
		$file_name="Desc_lic_{$id}_renglon_".ereg_replace(" ","_",$resultado->fields['codigo_renglon']);
		//se asume que solo hay un archivo para mostrar
		$q="select * from archivos where nombrecomp='$file_name.zip'";
		$filedata=sql($q) or fin_pagina();
?>
		<tr bgcolor="<?=$bgcolor_out;?>" >
     <input type="hidden" name="hidden_idrenglon_<?=$i;?>" value="<?=$resultado->fields['id_renglon_prop'];?>">
     <input type="hidden" name="hrenglon_name_<?=$i;?>" value="<?=$resultado->fields['codigo_renglon'];?>">
     <input type="hidden" name="hidrenglon_<?=$i;?>" value="<?=$resultado->fields['id_renglon'];?>">
     <td align="center"><input type="checkbox" title="Borrar Renglon" name="chk_<?=$i ?>" value=1 /></td>
     <td align="center" style="cursor:hand" > <input type="text" name="cant_renglon_<?=$i ?>" style="border-style:none;width:90%;text-align:right"  value="<?= ($resultado->fields['cantidad'])?$resultado->fields['cantidad']:$resultado->fields['cantidadr']; ?>" onchange="actualizar_cant(<?=$i?>);	win.show();check_rowCant(<?=$i?>)" /></td>
<a style="cursor:default" href='#r<?=$resultado->fields['id_renglon']?>'>
     <td align="center" style="cursor:hand" ><b><? echo $resultado->fields['codigo_renglon']; ?></b></td>
</a>
		<td colspan="2">
    	<table align="center" width="100%">
     		<tr>
     			<td align="left" width="1%">
     				<a target="_blank" title="<?= "Archivo: $file_name.doc\nTamaño: ".$filedata->fields['tamaño']." bytes" ?>" href="<?=encode_link("../ordprod/seguimiento_orden.php",array("downloadfile"=>1,"zip"=>0,"filename"=>$file_name,"id_lic"=>$id,'filesize'=>$filedata->fields['tamaño'])); ?>" >
     					<img border=0 src="../../imagenes/word.gif" />
     				</a>
     			</td>
     			<td align="center">
     				<b><?=$resultado->fields['titulo']?></b>
     			</td>
     		</tr>
     	</table>
    </td>
  </tr>
<?
 $resultado->MoveNext();
 $i++;
 }

?>
</table>
<table width="100%" border="0">
	<tr>
		<td align="center">
			<input type="button" name="bborrar" value="Borrar Renglones" onclick="if (confirm('Se borraran los renglones seleccionados\n Desea continuar?')) borrar_renglones()" />
			&nbsp;&nbsp;
			<input type="submit" name="btraer" value="Traer Renglones" title="Trae todos los Renglones" />
			&nbsp;&nbsp;
			<input type="submit" name="bexport" value="Exportar a Excel" title="Exporta los datos de los renglones a un archivo Excel" onclick="return exportar_renglones();" <? echo (($id_lic_prop != -1)?"":"disabled"); ?> />
			&nbsp;&nbsp;
			<input type="button" name="lista_materiales" value="Lista de materiales" title="Armado de lista de productos asociados al/los renglón/es" onclick="window.open('<?=encode_link("../mov_material/producto_lista_material.php", array("ID"=>$id, "id_entrega_estimada"=>$id_entrega_estimada, "id_subir"=>$id_subir, "id_lic_prop"=>$id_lic_prop))?>')"/>
		</td>
	</tr>
</table>
<br>
<?

$i=1;
$resultado->Move(0);
$cont=0;
while (!$resultado_prov->EOF)
{
?>
<input type="hidden" name="hidden_idprov_<?=($cont+2);?>" value="<?=$resultado_prov->fields['id_proveedor'];?>">
<input type="hidden" name="hnombreprov_<?=($cont+2);?>" value="<?=$resultado_prov->fields['razon_social'];?>">
<input type="hidden" name="hflagprov_<?=($cont+2);?>" value="existe">
<script>
arreglo_prov[<?=$cont;?>]=<?=$resultado_prov->fields['id_proveedor'];?>;
renglones[<?=$resultado_prov->fields['id_proveedor']?>]=new Array();
</script>
<?
$cont++;
$resultado_prov->MoveNext();
}

?>
<input type="button" name="bcargar" value="Cargar Proveedor" style="cursor:hand" onclick="if (wprov.closed) wprov=window.open('<?=encode_link("ventana_proveedor_presupuesto.php",array("onclickcargar"=>"window.opener.insertar_prov()"));?>','','resizable=1,scrollbars=yes,width=700,height=100,left=20,top=50,status=yes'); else wprov.focus()" style="width:120px" />
<br>
<script>
cant_prov=<?=$resultado_prov->recordcount();?>
</script>
<?
//lista de renglones
while (!$resultado->EOF)
{
$resultado_prov->Move(0);
?>
<br>
<a name=r<?=$resultado->fields['id_renglon'];?> > </a>
<table id="tabla_<?=$i;?>"  class='bordes' cellspacing="2" cellpadding="0" >
<tr id=mo>
<td colspan="2" align="center" width="60%" title="Cantidad Renglon" >Cant: <span id="cantr_<?=$i ?>"><?= ($resultado->fields['cantidad']?$resultado->fields['cantidad']:$resultado->fields['cantidadr']); ?></span></td>
<td colspan="2" align="center" width="40%"><?=$resultado->fields['codigo_renglon'];?></td>

<? //lista proveedores
$col_prov=2;
while(!$resultado_prov->EOF)
{
//si  no tiene id_renglon_prop
if ($resultado->fields['id_renglon_prop']=="")
	$permiso['OC']=" disabled ";
else
	$permiso['OC']="";
/* */
?>
<td id="columna_<?=$i;?>_0_<?=($col_prov);?>" valign="top" title="<? echo $resultado_prov->fields['razon_social']; ?>" ondblclick="change_image(<?=$col_prov ?>)">
	<table width="100%" border=0 cellpadding=0 cellspacing=0 align="center">
	<tr>
		<td><img id="imagen_<?=$col_prov?>_<?=$i?>" src="<?=$img_ext?>" border=0 title="Ocultar Proveedor" align="left" style="cursor:hand;" onclick="change_image(<?=$col_prov?>);" ></td>
	</tr>
	<tr>
		<td id=mo>
		<b><?= cortar2($resultado_prov->fields['razon_social'],16); ?></b>
		<div align="center">
		<input type="button" name="boton_borrar_<?=$i;?>_<?=($col_prov);?>" id="boton_borrar_<?=$i;?>_<?=($col_prov);?>" onclick="borrar_proveedor(boton_borrar_<?=$i;?>_<?=($col_prov);?>);" value="B" style="cursor:hand;">
		&nbsp;&nbsp;
		<button type="button" id="botonOC" name="botonOC" id="botonOC" class="menubutton" title="Crear Orden de Compra para este Proveedor" onclick="document.forms[0].id_proveedor.value=<?=$resultado_prov->fields['id_proveedor'].";id_renglon_tmp=".(($resultado->fields['id_renglon_prop'])?$resultado->fields['id_renglon_prop']:0)?>;show_menu('menuOC',recuperar_renglones);col_prov=<?=$col_prov?>; " style="cursor:hand" <?=$permiso['OC'] ?> >
		OC
		</button>
		</div>
		</td>
	</tr>
	</table>
</td>
<?
$resultado_prov->MoveNext();
$col_prov++;
}
?>
</tr>
<? //aca iria estadisticas del proveedor?>
<tr id="fila_<? echo $i; ?>" bgcolor="<?=$bgcolor_out;?>">
<td width="1%"></td>
<td width="1%"><b>Cant</td>
<td align="center" width="10%">
<table width="100%">
	<tr><td rowspan="2" align="right" width="40%" ><b>Producto</td><td align="right" title="Total Proveedores (1 renglon)" >Total Proveedores-></td></tr>
	<tr><td align="right" title="Total 1 Renglon">Total Renglon-></td></tr>
</table>
</td>
<td style="background-color:transparent" align="center" nowrap style="border-color:black"><b>
	    <table width="100%" cellpadding="0" cellspacing="0">
	    	<tr><td bgcolor="<?=$bgcolor_out;?>"><table title="Total Proveedores (1 renglon)" width="100%"><tr><td align="left">U$S</td> <td align="right"><span id=total_provs_<?=$i?>>0,00</span></td></tr></table></td> </tr>
	    	<tr><td bgcolor="<?=$bgcolor2 ?>"><table title="Total 1 Renglon" width="100%"><tr><td align="left">U$S</td><td align="right"><span id=total_reng_<?=$i?>>0,00</span></td></tr></table></td></tr>
	    </table>
</td>

<?
$resultado_prov->Move(0);
$col_prov=2;
while(!$resultado_prov->EOF)
{
	if ($resultado->fields['id_renglon_prop'])
	{
		$sql="select SUM((producto_proveedor_new.cantidad*producto_proveedor_new.monto_unitario)) as total
      from producto_presupuesto_new
      join renglon_presupuesto_new using (id_renglon_prop)
      join producto_proveedor_new using(id_producto_presupuesto)
      where renglon_presupuesto_new.id_renglon_prop=".$resultado->fields['id_renglon_prop']."
            and id_proveedor=".$resultado_prov->fields['id_proveedor']."
            and activo=1 group by(id_proveedor)";

	$res_monto_prod=sql($sql) or die ($db->ErrorMsg()."<br>".$sql);
	$total_monto=number_format($res_monto_prod->fields['total'],2,".","");
	}
	else
		$total_monto=0.00;
?>
<td align="center" id="columna_<?=$i;?>_1_<?=$col_prov;?>">
<span>U$S</span><input type="text" name="monto_<?=$i;?>_<?=$col_prov;?>" value="<?=$total_monto;?>" size=8 readonly><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
</td>
<script>
renglones[<?=$resultado_prov->fields['id_proveedor']?>][<?=$i-1 ?>]=<?=(($resultado->fields['id_renglon_prop'])?$resultado->fields['id_renglon_prop']:0) ?>;
</script>
<?
$resultado_prov->MoveNext();
$col_prov++;
}
?>
</tr>
<?

 //traigo productos del renglon_presupuestado
 if ($resultado->fields['id_renglon_prop'])
 {
  $q="
      select distinct p.id_renglon,id_producto_presupuesto,desc_orig,desc_adic,precio_presupuesto,desc_precio,
             desc_gral,cantidad,precio_licitacion,p.id_producto,p.id_producto_orig,id_compra,((cantidad*cantidadr)-cantidad_oc) as faltacomprar
             from
            (
            select  id_renglon,id_producto,id_producto_presupuesto,desc_orig,desc_adic,desc_gral,
             producto_presupuesto_new.cantidad,precio_presupuesto,id_producto_orig,renglon_presupuesto_new.cantidad as cantidadr
             from licitaciones.renglon_presupuesto_new
             join licitaciones.producto_presupuesto_new  using(id_renglon_prop)
             join general.productos using(id_producto)
             where id_renglon_prop=".$resultado->fields['id_renglon_prop']."
            ) as p
        left join
            (
            select renglon.id_renglon,producto.id_producto,producto.precio_licitacion,producto.desc_precio_licitacion as desc_precio
            from licitaciones.renglon
           join licitaciones.producto using(id_renglon)
           ) as pp
          on (pp.id_renglon=p.id_renglon and p.id_producto_orig=pp.id_producto)
  			left join
  				(
  					select id_producto_presupuesto as id_compra,sum(oc_pp.cantidad_oc) as cantidad_oc
  					from oc_pp
						join orden_de_compra using(nro_orden)
  					where estado!='n' group by id_producto_presupuesto
  				) as prod_comp
  				on prod_comp.id_compra=p.id_producto_presupuesto";

 }
//sino productos del renglon original
 else
 {
	$q="select renglon.id_renglon,producto.id_producto,producto.precio_licitacion,productos.desc_gral,producto.cantidad ";
	$q.="from renglon ";
	$q.="join producto using(id_renglon) ";
	$q.="join productos using(id_producto) ";
	$q.="where id_renglon=".$resultado->fields['id_renglon'];

 }
 $resultado_producto=sql($q) or die ($db->ErrorMsg()."<br>".$q);
 $j=2;
 $total_comprado=0;
while(!$resultado_producto->EOF)
{
$desc=$resultado_producto->fields['desc_nueva'];
?>
  <tr id="fila_<? echo $i; ?>_<? echo $j; ?>" bgcolor="<?=($j%2)?$bgcolor2:$bgcolor3;?>"  onclick="if(window.event.srcElement.tagName!='INPUT'){alternar_color(this,'#a6c2fc');}" >
   <input type="hidden" name="producto_<?=$i;?>_<?=$j;?>" value="<?=$resultado_producto->fields['id_producto_presupuesto'];?>">
   <input type="hidden" name="hidproducto_<?=$i;?>_<?=$j;?>" value="<?=$resultado_producto->fields['id_producto'];?>">
   <input type="hidden" name="hidproducto_orig_<?=$i;?>_<?=$j;?>" />
  <td><input type="checkbox" name="chk_bprod_<?=$i ?>" value="<?=$resultado_producto->fields['id_producto_presupuesto'];?>" <? if ($resultado_producto->fields['id_compra']){$total_comprado++; echo "disabled";} ?>/></td>
	<td align="center"><b><?=$resultado_producto->fields['cantidad']; ?>
	<input type="hidden" name="cantidad_prod_<?=$i;?>_<?=$j;?>" value="<? echo $resultado_producto->fields['cantidad']; ?>">
	</td>
    <td>
    <input type="button" name="bcambiar_<?=$i."_".$j ?>" value="C" title="Cambiar producto" <? if ($resultado_producto->fields['id_compra']) echo "disabled" ?> onclick="if (!wcambiarprod.closed) wcambiarprod.close(); wcambiarprod=window.open('<?=encode_link('../productos/listado_productos.php',array("onclick_cargar"=>"window.opener.cambiar_prod($i,$j);window.close()","pagina_viene"=>"detalle_presupuesto.php"))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500');"><input type="text" name="nbreproducto_<?=$i;?>_<?=$j;?>" readonly value="<?= $desc=($resultado_producto->fields['desc_orig'])?$resultado_producto->fields['desc_orig']." ".$resultado_producto->fields['desc_adic']:$resultado_producto->fields['desc_gral']?>" title="<?=$desc?>" size=35 />
    <input type="button" name="log" value="L" title="Historial de cambios de precio" onclick="window.open('<?=encode_link('ver_log_producto_presupuesto.php',array("id_producto" => $resultado_producto->fields['id_producto'] ,"id_renglon_prop" => $resultado->fields['id_renglon_prop']))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=450,resizable=1');">
    <input type="button" name="bhistorial" value="H" title="Historial de producto" onclick="window.open('<?=encode_link('historial_comentarios.php',array())?>&id_producto=<?=$resultado_producto->fields['id_producto']?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=700,height=500,resizable=1');">
    <input type="button" name="bdescadic" value="A" title="Agregar descripción adicional del producto" <? if ($resultado_producto->fields['id_compra']) echo "disabled" ?> onclick="if (!wdescadic.closed) wdescadic.close(); wdescadic=window.open('../ord_compra/desc_adicional.php?nombres='+'<?="hdescorig_{$i}_{$j},hdescadic_{$i}_{$j},nbreproducto_{$i}_{$j}"?>&title=1&onclickguardar=window.opener.guardar_desc()','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400');">
    <input type="hidden" name="hdescorig_<?=$i;?>_<?=$j;?>" value="<?=($resultado_producto->fields['desc_orig'])?$resultado_producto->fields['desc_orig']:$resultado_producto->fields['desc_gral']; ?>"/>
    <input type="hidden" name="hdescadic_<?=$i;?>_<?=$j;?>" value="<?=$resultado_producto->fields['desc_adic']; ?>"/>
		</td><!--verde de activo #9AE292 #FFFFCC-->
  <td <? $link_orden=encode_link("segprod_abrir_oc.php",array("id_prod_pres"=>$resultado_producto->fields['id_producto_presupuesto']));          //ya se compraron todos los productos
          if ($resultado_producto->fields['id_compra']) {$aoc[$resultado->fields['id_renglon_prop']]=$resultado_producto->fields['id_compra'] ; if ($resultado_producto->fields['faltacomprar'] <= 0) echo "bgcolor=#FFFFDF"; else echo "bgcolor=orange";} echo " title=\"".ereg_replace('"',"",$resultado_producto->fields["desc_precio"])."\"" ?>>
	    <table width="100%">
	    <tr>
<!--Aca tengo que modificar bgcolor=#FFFFDF-->
        <td align="left">U$S</td>
        <? if ($resultado_producto->fields['id_compra']) {?>
	    <td align="right"><a href="<?=$link_orden?>" target="_blank"><span id="precio_prod_<?=$i;?>_<?=$j;?>"><?=formato_money(($resultado_producto->fields['precio_licitacion']?$resultado_producto->fields['precio_licitacion']:$resultado_producto->fields['precio_presupuesto'])) ?></a></span></td>
	    <?}
          else {?>
          <td align="right"><span id="precio_prod_<?=$i;?>_<?=$j;?>"><?=formato_money(($resultado_producto->fields['precio_licitacion']?$resultado_producto->fields['precio_licitacion']:$resultado_producto->fields['precio_presupuesto'])) ?></span></td>
          <?}?>
	    </tr>
	    </table>
    </td>


<?
//si esta guardado el producto en producto_presupuesto
if ($resultado_producto->fields['id_producto_presupuesto'])
{
	//trae todo los precios de todos los proveedores para el producto
	$q ="select pprov.monto_unitario,pprov.comentario,pprov.activo,pprov.cantidad ";
	$q.="from producto_proveedor_new pprov ";
	$q.="join proveedor using(id_proveedor) ";
	$q.="where pprov.id_producto_presupuesto=".$resultado_producto->fields['id_producto_presupuesto'];
	$q.=" order by razon_social";
  $resultado_precios=sql($q) or fin_pagina();
	if($resultado_precios->recordcount() != $resultado_prov->recordcount())
		echo("<font color=red ><b>ERROR: La cantidad de proveedores difiere con la<br> cantidad de precios para el id_producto_presupuesto=".$resultado_producto->fields['id_producto_presupuesto']."</b></font>");
		//si este error ocurre, checkear la BD -> licitaciones.producto_proveedor_new
		//y ver si para ese producto existen X entradas, donde X es la cantidad de proveedores en el presupuesto
}
else
{
  $q="select 0.00 as monto_unitario,0 as activo";
  //(si renglon_presupuesto.cantidad, sino renglon_lic.cantidad)*cantidad del producto del renglon
  $cant_prod=$resultado->fields['cantidad']*$resultado_producto->fields['cantidad']
  or $cant_prod=$resultado->fields['cantidadr']*$resultado_producto->fields['cantidad'];
  $q.=",$cant_prod as cantidad";
  $resultado_precios=sql($q) or fin_pagina();
}

  $resultado_prov->Move(0);
  $col_prov=2;
   while(!$resultado_prov->EOF)
   {
   if($resultado_precios->fields['activo']==1)
                            $color="bgcolor='#9AE292';";
                            else
                            $color="";
?>
    <td align="center" id="columna_<?=$i;?>_<?=($j);?>_<?=($col_prov);?>" title="<?=$comentario=ereg_replace("\"","",$resultado_precios->fields['comentario']);?>" <?=$color?> >
    <span>&nbsp;U$S</span><input type="text" name="texto_precio_<?=$i;?>_<?=($j);?>_<?=($col_prov);?>" onkeypress="return filtrar_teclas(event,'0123456789.');" value="<?=number_format($resultado_precios->fields['monto_unitario'],2,".","")?>" size="7" onfocus="this.select()" onchange="cambiar_precio(<?=$i?>,<?=$j?>,<?=$col_prov?>)">
    <input type="button" tabindex="-1" name="boton_comentario_<?=$i;?>_<?=($j);?>_<?=($col_prov);?>" value="C" onclick="cargar_ventana(this)" style="cursor:hand">
    <input type="button" tabindex="-1" name="boton_precio_<?=$i;?>_<?=($j);?>_<?=$col_prov?>" value="E" onclick="elegir_precio(<?=$i?>,<?=$j?>,<?=$col_prov?>)" style="cursor:hand">
    <input type="button" name="bcant_<?=$i;?>_<?=$j?>_<?=$col_prov?>" value="U" size="2" title="Unidades: <?=($resultado_precios->fields['cantidad']?$resultado_precios->fields['cantidad']:$resultado->fields['cantidad']*$resultado_producto->fields['cantidad'])?> (Cantidad a comprar o comprada)" onclick="document.all.menuCantbaceptar.onclick=function() {fnBtnAceptar(document.all.hcantidadprov_<?=$i?>_<?=$j?>_<?=$col_prov?>)}; show_menuCant(document.all.hcantidadprov_<?=$i?>_<?=$j?>_<?=$col_prov?>)">
    <input type="hidden" name="hidden_comentario_<?=$i;?>_<?=($j);?>_<?=($col_prov);?>" value="<?= $comentario?>">
    <input type="hidden" name="hidden_precio_elegido_<?=$i;?>_<?=($j);?>_<?=($col_prov);?>" value="<?=(($resultado_precios->fields['activo']=="")||($resultado_precios->fields['activo']==0))?0:1;?>">
    <input type="hidden" name="hcantidadprov_<?=$i?>_<?=$j?>_<?=$col_prov?>" value="<?=($resultado_precios->fields['cantidad']?$resultado_precios->fields['cantidad']:$resultado->fields['cantidad']*$resultado_producto->fields['cantidad'])?>">
    </td>
<?
    $resultado_prov->MoveNext();
    if ($resultado_precios->recordcount()==$resultado_prov->recordcount())
   		$resultado_precios->MoveNext();
    $col_prov++;
   }
?>
   </tr>
<?
$j++;
$resultado_producto->MoveNext();
}
 if (!permisos_check("inicio","permiso_productos_presup"))
 {
 	$permisos_b['agregar']=" disabled title='Usted no tiene permiso para agregar productos\n (hablar con Adrian o Juan Manuel)' ";
 	$permisos_b['eliminar']=" disabled title='Usted no tiene permiso para eliminar productos\n (hablar con Adrian o Juan Manuel)' ";
 }
?>
<tr>
	<td align="center" colspan=<?=$resultado_prov->recordcount()+4 ?> >
		<input type="button" name="bagregar" value="Agregar" <?=$permisos_b['agregar']?> onclick="wproductos=window.open('<?=encode_link('../productos/listado_productos.php',array("onclick_cargar"=>"window.opener.add_prod($i)","pagina_viene"=>"detalle_presupuesto.php"))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500');" title="Agregar Producto" />
		&nbsp;&nbsp;
		<input type="button" name="beliminar_<?=$i?>" value="Eliminar" <? if ($resultado_producto->recordcount()==$total_comprado) echo "disabled"; echo $permisos_b['eliminar']?> onclick="if (confirm('Se eliminaran los productos seleccionados\n Desea continuar?')) { borrar_prod(<?=$i?>);ftotal_reng(<?=$i?>);ftotal_provs(<?=$i?>);}//recalculo los totales de proveedores y renglon " title="Eliminar Producto"/>
<!--		&nbsp;&nbsp;
		<input type="button" name="bcambiar_<?=$i?>" value="Cambiar Producto" style="width:115px" <? if ($resultado_producto->recordcount()==$total_comprado) echo "disabled"; echo $permisos_b['cambiar']?> onclick="" title='Cambia el Producto seleccionado (UNO A LA VEZ)'/>
-->
	</td>
</tr>
<input type="hidden" name="cant_prod_reng_<? echo $i; ?>" id="cant_prod_reng_<? echo $i; ?>" value="<?=$resultado_producto->recordcount() //echo ($j-2); ?>">
</table>
<script>

<? if ($aoc[$resultado->fields['id_renglon_prop']]) echo "document.all.cant_renglon_$i.readOnly=true;\n";//deshabilito que cambie la cantidad en caso de que se haya echo una OC en el renglon ?>
ftotal_reng(<?=$i?>);
ftotal_provs(<?=$i++?>);
</script>
<?
	$resultado->MoveNext();
	//$total_provs->MoveNext();
 }
?>
<!--</table>-->
<br>
<table align="center" width="100%">
<tr>
<td align="center">
<b>Comentarios</b>
<br>
<textarea name="comentarios" style="width:80%" rows="5" ><?=$resultado_licitacion->fields['comentarios'] ?></textarea>
</td>
</tr>
</table>
<br>
<!--se usa para hacer la OC -->
<input type="hidden" name="id_proveedor" >
<input type="hidden" name="id_renglon_prop" >
<!--<input type="submit" name="boton" value="Suspender Pedido de Presupuesto">-->

<input type="hidden" name="hexportar_reng" >
<input type="hidden" name="hborrar_reng" >
<input type="hidden" name="hborrar_prod" >
<table width=100% align=center>
  <tr>
     <td width=25%>
     <?
     $link=encode_link("compras_productos.php",array("id_licitacion_pro"=>$id_lic_prop));
     if (!$_ses_user["login"]=="fernando" || $id_lic_prop==-1) $disabled_compras="disabled";

     ?>
     <input type="button" name="compras" value="Compras" style="width:140px" onclick="window.open('<?=$link?>')" <?=$disabled?> />
     </td>
     <td width=25%>
     <input type="button" name="bguardar" value="Guardar"
     onclick="if (check_form()) {flagguardar=1;document.forms[0].doForm.value='Guardar';win.show();check_rowCant(0,true)}//checkea todas las tablas" style="width:140px" />
     </td>
<?	if (!permisos_check("inicio","beliminar_presupuesto") || $id_lic_prop==-1)
			$permiso['beliminar']="disabled";
?>
     <td width=25%>
     <input type="submit" name="beliminar" value="Eliminar Presupuesto" <?= $permiso['beliminar']?> onclick="return confirm('Se eliminará el presupuesto de forma permanente\n\n Desea Continuar?')" style="width:140px" />
     </td>
     <td width=25%>
     <input type="button" name="bcerrar" value="Cerrar" style="width:140px" onclick="window.close()">
     </td>
  </tr>
</table>
</center>
<input type="hidden" name="id_subir" value="<?=$id_subir ?>" />
<input type="hidden" name="doForm" />
</form>
<?= fin_pagina(); ?>