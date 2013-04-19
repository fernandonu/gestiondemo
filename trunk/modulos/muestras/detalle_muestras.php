<?

/*AUTOR: MAC
  FECHA: 10/06/04

$Author: fernando $
$Revision: 1.23 $
$Date: 2006/08/04 14:39:47 $
*/


require_once("../../config.php");
include("func_muestras_ordprod.php");
include("../stock/funciones.php");

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$id_muestra=$_POST['id_muestra'] or $id_muestra=$parametros["id_muestra"]; 

$items=0;

//reseteamos siempre esta variable de sesion para evitar que funcione mal
//licitaciones_view
phpss_svars_set("_ses_global_backto", "");

$guardar=$_POST['guardar'];
if($_POST['boton_guardar']=="Guardar" || $guardar){
 $db->StartTrans();
 $estado=$_POST['estado'];
 $observaciones=$_POST['observaciones'];
 $id=$_POST['id_muestra'];
 $entidad=$_POST['select_entidad'];
 $descripcion=$_POST['descripcion'];
 if($_POST['id_licitacion'])
  $id_licitacion=$_POST['id_licitacion'];
 else
  $id_licitacion="null";
 if($_POST['fecha_vencimiento'])
  $fecha_vencimiento="'".fecha_db($_POST['fecha_vencimiento'])."'";
 else
  $fecha_vencimiento="null";
  if($_POST['fecha_devolucion'])
  $fecha_devolucion="'".fecha_db($_POST['fecha_devolucion'])."'";
 else
  $fecha_devolucion="null";
 if ($_POST["costo"]) {
	$costo=$_POST["costo"];
 }
 else $costo="NULL";
 //se guarda todo, solo si el estado es vacio o pendiente
 if($estado=="" || $estado==0)
 {//si tenemos el id entonces actualizamos
  if($id)
  { $estado=0;
    $query="update muestra set estado=$estado, costo=$costo, observaciones='$observaciones',id_entidad=$entidad,descripcion='$descripcion',fecha_vencimiento=$fecha_vencimiento";
    $query.=",fecha_devolucion=$fecha_devolucion,id_licitacion=$id_licitacion";
    $query.=" where id_muestra=$id";


    if(sql($query))
    {$fecha_hoy=date("Y-m-d H:i:s",mktime());
     $usuario=$_ses_user['name'];
     $tipo="modificación";
    	//agregamos el log de modificacion del reclamo de partes
    	$query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
    	        values('$fecha_hoy','$usuario','$tipo',$id)";
     if(sql($query))
     {
      $msg="<center><b>La muestra se actualizó con éxito</b></center>";
     }
    	else
    	 $msg="<center><b>La muestra no se pudo actualizar.</b></center>";
    }
    else
     $msg="<center><b>La muestra no se pudo actualizar.</b></center>";
  }//de if($_POST['generar_nc']==0)

  else//sino, lo insertamos
  {//reservamos el id  para insertar

   $query="select nextval('muestra_id_muestra_seq') as id_muestra";
   $id_val=sql($query) or fin_pagina();
   $id=$id_val->fields['id_muestra'];
   $query="insert into muestra(id_muestra,costo,estado,observaciones,descripcion,id_entidad,fecha_vencimiento,id_licitacion)
           values($id,$costo,0,'$observaciones','$descripcion',$entidad,$fecha_vencimiento,$id_licitacion)";
   if(sql($query) or fin_pagina()){
    $fecha_hoy=date("Y-m-d H:i:s",mktime());
    $usuario=$_ses_user['name'];
    $tipo="creación";
   	//agregamos el log de creción del reclamo de partes
   	$query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
   	        values('$fecha_hoy','$usuario','$tipo',$id)";
    if(sql($query))
   	 $msg="<center><b>La muestra se insertó con éxito</b></center>";
   	else
   	 $msg="<center><b>No se pudo insertar la muestra</b></center>";
    }
   else
    $msg="<center><b>No se pudo insertar la muestra</b></center>";
  }

  //inserta las ordenes de produccion en la tabla ordprod_muestra
  insertar_ordprod($id);
  //insertar_partes($id);
 }//de if($estado=="" || $estado==0)
 else//sino, solo actualizamos las observaciones
 {
  if($estado==1)  {
    $query="update muestra set observaciones='$observaciones', costo=$costo, fecha_devolucion=$fecha_devolucion where id_muestra=$id";
    }
   else
   if($estado==2){
       $query="update muestra set observaciones='$observaciones' where id_muestra=$id";
   }
  if(sql($query) or fin_pagina())
  {$fecha_hoy=date("Y-m-d H:i:s",mktime());
     $usuario=$_ses_user['name'];
     $tipo="modificación";
    	//agregamos el log de modificacion del reclamo de partes
    	$query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
    	        values('$fecha_hoy','$usuario','$tipo',$id)";
     if(sql($query)){
      $msg="<center><b>La muestra se actualizó con éxito</b></center>";
     }
    	else
    	 $msg="<center><b>La muestra no se pudo actualizar.</b></center>";
   }
   else
     $msg="<center><b>La muestra no se pudo actualizar.</b></center>";
 }

 $db->CompleteTrans();
 if($_POST['boton_guardar']=="Guardar"){
     $link=encode_link("seguimiento_muestras.php",array("msg"=>$msg));
     header("location: $link");
 }

 
 if ($_POST["comentario_nuevo"]){
         $comentario=$_POST["comentario_nuevo"];
         $id_gestion=$id_muestra;
         $tipo="muestras";
         $sql=nuevo_comentario($id_gestion,$tipo,$comentario);
         sql($sql) or fin_pagina();
	   }
 
}		//fin guardar

if ($_POST["boton_guardar_comentario"]){
 if ($_POST["comentario_nuevo"]){
         $comentario=$_POST["comentario_nuevo"];
         $id_gestion=$id_muestra;
         $tipo="muestras";
         $sql=nuevo_comentario($id_gestion,$tipo,$comentario);
         sql($sql) or fin_pagina();
	   }

}

//boton de En Curso
if ($_POST["eliminar_foto"]){
    $fotos=$_POST["chk_fotos"];
    for($i=0;$i<count($fotos);$i++){
        if ($fotos[$i])
           $sql[]="delete from foto_muestra where id_foto_muestra=".$fotos[$i];
    }
    sql($sql) or fin_pagina(); 
    
}

if($_POST['en_curso']=="En Curso"){
    
  $db->StartTrans();

  $observaciones=$_POST['observaciones'];
  $fecha_vencimiento=fecha_db($_POST['fecha_vencimiento']);
  //traigo el id de produccion
  $sql="select id_deposito from depositos where nombre='Produccion'";
  $res=sql($sql,"<br>Error al traer el id del deposito de produccion<br>") or fin_pagina();
  $id_dep_en_produccion=$res->fields["id_deposito"];
 
  //si no esta elegido descuento, si esta elegido no descuento en stock en produccion
   $cantidad_productos=$_POST["cantidad_productos"];
   $id_licitacion=$_POST['id_licitacion'];
   for($i=0;$i<$cantidad_productos;$i++){
       if ($_POST["chk_producto_$i"]) {
           $id_prod_esp = $_POST["chk_producto_$i"];
           $cantidad_descontar = $_POST["cantidad_$i"];
           $comentario = "Muesta para la licitación $id_licitacion paso a estado En Curso";
           if ($cantidad_descontar){
               descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cantidad_descontar,$comentario);       
               //inserto en productos_descontado para poder usarlo despues
               actualiza_producto_descontado($id_muestra,$id_prod_esp,$cantidad_descontar);
               }
        } 
       
  }//del for
  $query="update muestra set estado=1,observaciones='$observaciones',fecha_vencimiento='$fecha_vencimiento' where id_muestra=$id_muestra";
  if(sql($query)){
             $fecha_hoy=date("Y-m-d H:i:s",mktime());
             $usuario=$_ses_user['name'];
             $tipo="pasado a En Curso";
   	         //agregamos el log de en curso
   	         $query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
   	                  values('$fecha_hoy','$usuario','$tipo',$id_muestra)";
              if(sql($query))
   	                $msg="<center><b>La Muestra se actualizó con éxito</b></center>";
   	                else
   	                $msg="<center><b>No se pudo actualizar la muestra</b></center>";
              }
              else
                $msg="<center><b>No se pudo actualizar la muestra</b></center>";

      $link=encode_link("seguimiento_muestras.php",array("msg"=>$msg));
      $db->CompleteTrans();
       header("location: $link");      
 } // del if pasar a en curso

if($_POST['historial']=="Historial"){
    
 $db->StartTrans();
 $id_muestra=$_POST['id_muestra'];
 $query="update muestra set estado=2 where id_muestra=$id_muestra";
 sql($query) or fin_pagina();
 $fecha_hoy=date("Y-m-d H:i:s",mktime());
 $usuario=$_ses_user['name'];
 $tipo="pasado a Historial";
 if ($_POST['sel_cliente']){
 	$tipo="pasado a Historial, la muestra es para el cliente";
 }
 //agregamos el log de historial 
 $query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
         values('$fecha_hoy','$usuario','$tipo',$id_muestra)";
 sql($query) or fin_pagina();
 
 //si hay algun producto en producto descontado y lo marcaron lo ingreso al stock correspondiente
 $sql=" select id_deposito from depositos where nombre = 'Buenos Aires'";
 $res=sql($sql) or fin_pagina();
 $id_deposito=$res->fields["id_deposito"];
 
 $comentario=" Por Devolución de Muestras";
 
 $cantidad_productos=$_POST["cantidad_productos"];
 $id_licitacion=$_POST['id_licitacion'];
 
 for($i=0;$i<$cantidad_productos;$i++){
      
       if ($_POST["chk_producto_$i"]) {
           $id_prod_esp = $_POST["chk_producto_$i"];
           $cantidad = $_POST["cantidad_$i"];
           if ($cantidad){
              agregar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,22,"disponible");
              //aca pongo en 0 la columna activo de producto descontada
              $sql=" update productos_descontado set activo=0 where id_prod_esp=$id_prod_esp and id_muestra=$id_muestra";
              sql($sql) or fin_pagina();
           }    
        } 
 }//del for
 
 $msg="<center><b>La Muestra se actualizó con éxito</b></center>";
 $link=encode_link("seguimiento_muestras.php",array("msg"=>$msg));
 $db->CompleteTrans();
 header("location: $link");
}


echo $html_header;
echo cargar_calendario();
?>
<script src="../../lib/funciones.js"></script>
<script src="../../lib/NumberFormat150.js"></script>
<script>

//FUNCIONES PARA AGREGAR Y ELIMINAR ORDENES DE PRODUCCION
//variable que contiene la ventana hijo productos
var wproductos=0;

//variable para mantener el total de los productos de la muestra

function cargar(nro,lic,prod,cant)
{
/*Para insertar una fila*/

var items;
var i,ord,insertar_ok;

largo=document.all.items.value;

insertar_ok=1;
for(i=0;i<largo;i++)
 {
   ord=eval("document.all.idp_"+i);

   if(typeof(ord)!='undefined' && ord.value==nro)
   {
      alert('La orden seleccionada ya fue insertada.\nNO se puede volver a insertar.');
      insertar_ok=0;
   }
 }

if (insertar_ok==1) {

        items=document.all.items.value++;
         //inserta al final
      
        var fila=document.all.productos.insertRow(document.all.productos.rows.length);
        //inserta al principio
        
        fila.insertCell(0).width='5%';
        fila.cells[0].innerHTML="<div align='center'> <input name='chk' type='checkbox' value='1'> </div><input type='hidden' name='idp_"+
                                 items+"' value='"+nro+"'>";
        fila.insertCell(1).width='10%';
        fila.cells[1].innerHTML="<div align='center'> <input name='nroorden_"+
                                 items+"' type='text'  readonly size='8' value="+nro+"> </div>";
        fila.insertCell(2).width='20%';
        fila.cells[2].innerHTML="<div align='center'> <textarea name='desc_"+
                                 items +"' cols='50' rows='1' wrap='VIRTUAL' readonly >"+prod+"</textarea> </div>";
        fila.insertCell(3).width='5%';
        fila.cells[3].innerHTML="<div align='center'> <input name='cantmaq_"+
                                 items+"' type='text' size='6' value="+cant+" > </div>";
        if (lic==-1) {
                fila.insertCell(4).width='15%';
                fila.cells[4].innerHTML="<div align='center'> <input name='lic_"+
                                         items+"' type='text' readonly  size='6' value='' ></div>";
        }
        else {
              fila.insertCell(4).width='15%';
              fila.cells[4].innerHTML="<div align='center'> <input name='lic_"+
                                      items+"' type='text' readonly  size='6' value="+lic+" ></div>";
        }
        alert("La orden de produccion nro "+nro+" se cargo correctamente");
        }

wproductos.focus();
}


/**********************************************************/
function nuevo_item()
{var pagina_prod;
 var nbre_prov;
 var stock_page;


 if (wproductos==0 || wproductos.closed)
 {  <? if ($licitacion)
        $link=encode_link('selec_ordprod.php',array("detalle"=>1,"cmd"=>'ta',"keyword"=>$licitacion,"filter"=>"orden_de_produccion.id_licitacion"));
       else
        $link=encode_link('selec_ordprod.php',array("detalle"=>1,"cmd"=>'ta',filter=>'all'));?>
      pagina_prod="<?=$link ?>"
      wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');

 }
 else
  if (!wproductos.closed)
   wproductos.focus();
}
/*************************************************/

/*************************************************/
function borrar_items()
{
var i=0,j=0;var aux;
var precio;

  while (typeof(document.all.chk)!='undefined' &&
		 typeof(document.all.chk.length)!='undefined' &&
		 i < document.all.chk.length)
  {

   /*Para borrar una fila*/
   if (typeof(document.all.chk[i])!='undefined' && document.all.chk[i].checked)
   {

   	//eliminamos el id del producto del hidden, para indicar que no se debe
    //volver a insertar
    aux=eval("document.all.idp_"+j);
    if(typeof(aux)!='undefined')
     aux.value="";
     document.all.productos.deleteRow(i+1);
     j++;
   }
   else
  	  i++;
  }//del while

   if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
   {
  	aux=eval("document.all.idp_"+j);
    if(typeof(aux)!='undefined')
     aux.value="";
    document.all.productos.deleteRow(1);
   }

}



/**********************************************************/
//funcion que controla que se carguen algunos datos obligatorios
//el parametro indica si debe hacer un control extra
function control_datos(control)
{
var long_ord=0;

if (typeof(document.all.chk) !='undefined') {
	if (typeof(document.all.chk.length) !='undefined')
	  long_ord=document.all.chk.length;
	else long_ord=1;
	}

  if(document.all.descripcion.value=="")
  {alert('Debe ingresar una descripción para la muestra');
   return false;
  }
  if(document.all.select_entidad.value==-1 || document.all.select_entidad.value=="" )
  {alert('Debe seleccionar una entidad para la muestra');
   return false;
  }

  if(control==1 && document.all.fecha_vencimiento.value=="")
  {alert('Para poder pasar a estado \'En Curso\', debe completar la fecha estimada de devolución');
   return false;
  }

  if(control==2 && document.all.fecha_devolucion.value==""){  	
  	if (document.all.sel_cliente.checked) return true;
  	alert('Para poder pasar a estado \'Historial\', debe completar la fecha de devolución');
   	return false;
  }

var cant_veces=0;
var i=0;
while (cant_veces < long_ord) {
c=eval("document.all.cantmaq_"+i);

var fila=cant_veces+1
if (typeof(c)!='undefined') {

if (c.value=="" || isNaN(c.value)) {
 alert("Ingresar un número valido para el campo Cantidad en la fila " + fila);
 return false;
}

cant_veces++;   //cantidad de checkbox
}
i++;  //nombre que corresponde al nombre de los campos cant_ y unitario_
}

return true;
}

function marcar_fila(indice,check){
var fila;

celda_1=eval("document.all.celda_1_"+indice);
celda_2=eval("document.all.celda_2_"+indice);
celda_3=eval("document.all.celda_3_"+indice);

if (check.checked)
    //fila.style.backgroundColor="red";
    {
    celda_1.disabled=celda_2.disabled=celda_3.disabled=true;
    }
    else
        celda_1.disabled=celda_2.disabled=celda_3.disabled=false;
    //fila.style.backgroundColor="<?=$bgcolor2?>";
} // de la funcion marcar_fila

function seleccionar(valor){
  var chk,cantidad,bool;
  
  cantidad=parseInt(document.all.cantidad_productos.value);
  
  if (valor) bool=true;
       else  bool=false;
  
  for(i=0;i<cantidad;i++){
      chk=eval("document.all.chk_producto_"+i);
      chk.checked=bool;
  }
    
}

function seleccionar_cliente(valor1){
  var chk,cantidad,bool;
  
  cantidad=parseInt(document.all.cantidad_productos.value);
  
  for(i=0;i<cantidad;i++){
      chk=eval("document.all.chk_producto_"+i);
      chk.checked=false;
  }
  document.all.seleccionar_todos.checked=false;
  
  if (valor1) bool=true;
  else bool=false;
  	
  for(i=0;i<cantidad;i++){
      chk=eval("document.all.chk_producto_"+i);
      chk.disabled=bool;
  	} 
  	
  document.all.fecha_devolucion.value='';
  document.all.fecha_devolucion.disabled=bool;
  
  document.all.seleccionar_todos.disabled=bool;
}

</script>

<?
//al volver de la pagina licitaciones (cuando se va a asociar la muestra con la
//licitacion, se setea el id de la muestra con el parametro que vuelve desde
//licitaciones_view, que se envio antes desde esta pagina (esto solo se aplica
//si estamos actualizando. Si estamos insertando esto no se aplica).
if($parametros['nro_orden']!="")
 $id_muestra=$parametros['nro_orden'];

if($id_muestra)
{
 $query="select * from muestra where id_muestra=$id_muestra";
 $muestras=sql($query) or fin_pagina();
 // Iniciar costo.
 $costo=$muestras->fields["costo"];

 if($muestras->fields['estado']==1 ||$muestras->fields['estado']==2)
  //si esta en curso o en historial debe aparecer todo deshabilitado
  $permiso="disabled";
  

 if(!$descripcion)
  $descripcion=$muestras->fields['descripcion'];
 if(!$id_entidad)
  $id_entidad=$muestras->fields['id_entidad'];
 if(!$estado)
  $estado=$muestras->fields['estado'];
 if(!$observaciones)
  $observaciones=$muestras->fields['observaciones'];
 if(!$fecha_vencimiento)
  $fecha_vencimiento=$muestras->fields['fecha_vencimiento'];
 if(!$fecha_devolucion)
  $fecha_devolucion=$muestras->fields['fecha_devolucion'];
 if(!$id_licitacion)
  $id_licitacion=$muestras->fields['id_licitacion'];

}

//si tenemos el parametro de la paigna de licitaciones, lo asociamos a la muestra
if($parametros['licitacion']!=""){
	 $id_licitacion=$parametros['licitacion'];
	 $id_entidad=$parametros['id_entidad'];
	 $disabled_entidad="disabled";
     }

if($id_licitacion){
    $disabled_entidad="disabled";
    }

    $link=encode_link("detalle_muestras.php",array("pagina"=>$pagina));

//traemos y luego generamos el log del reclamo de partes
if($id_muestra)
{
 $query="select * from log_muestra where id_muestra=$id_muestra order by id_log_muestra DESC";
 $log=sql($query) or fin_pagina();
?>
<center>
<div style='position:relative; width:95%; height:13%; overflow:auto;'>
<table width="100%" cellpadding="1" cellspacing="1" align="center">
<?
while(!$log->EOF){
  list($fecha,$hora)=split(" ",$log->fields['fecha']);
 ?>
 <tr id=ma>
  <td align="left">
   Fecha de <?=$log->fields['tipo']?>: <?=fecha($fecha)?> <?=$hora?>
  </td>
  <td align="right">
   Usuario: <?=$log->fields['usuario']?>
  </td>
 </tr>
 <?
 $log->MoveNext();
} //del while
?>
</table>
</div>
</center>
<?
}//de if($id_reclamo_partes)
?>
<form name="form1" method="POST" action="<?=$link?>">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<table width="95%" align="center" class=bordes>
<tr>
 <td align="center">
  <table width="100%" cellpadding="5">
   <tr>
    <td align="center" colspan="2" id=mo>
     <font size="3"><b>Muestra Nº <?=$id_muestra?></b></font>
    </td>
   </tr>
   <tr>
    <td colspan="2">
       <table width="100%">
        <tr>
         <td>
        <?
         //utilizamos el parametro nro_orden para enviar el id de muestra,
         //asi cuando vuelve desde la pagina de licitaciones, recuerda
         //los datos que se habian cargado
         $link=encode_link("../licitaciones/licitaciones_view.php",array("backto"=>"../muestras/detalle_muestras.php","nro_orden"=>$id_muestra,"pag"=>"asociar","_ses_global_extra"=>array()));
        ?>
        <br>
        <?
        if($id_licitacion)
         echo " <b>Muestra asociada a la Licitación Nº $id_licitacion</b><br>";
        ?>
        <input type="button" name="asociar" <?=$permiso?> style="width=180" value="Asociar Muestra a Licitación" onclick='if(confirm("Si continua se perderá cualquier cambio que haya realizado.¿Desea Continuar?"))document.location="<?=$link?>"'>
       </td>
       <td align="right">
        <b>Estado </b>
        <?
         switch($estado){
          case 0:echo "Pendiente";break;
          case 1:echo "En Curso";break;
          case 2:echo "Historial";break;
          default:echo "Nuevo";
         }
        ?>
       </td>
       </tr>
      </table>
    </td>
   </tr>
   <tr>
    <td>
     <b>Descripción </b>
    </td>
    <td>
     <input type="hidden" name="estado" value="<?=$estado?>" size="87">
     <input type="text" name="descripcion" value="<?=$descripcion?>" <?=$permiso?> size="87">
    </td>
   </tr>
   <tr>
    <td>
     <b>Entidad</b>
    </td>
    <td>
     <? //traemos los poveedores para mostrar en un combo
      $query="select nombre,id_entidad from entidad order by nombre";
      $entidades=sql($query) or fin_pagina();
     ?>
     <input type="hidden" name="select_entidad" value="<?=$id_entidad?>">
     <select name="selec_entidad" <?=$permiso?> <?=$disabled_entidad?> onchange="document.all.select_entidad.value=this.value" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
      <option value="-1">Seleccione una Entidad</option>
      <?
       while(!$entidades->EOF){
      ?>
        <option value="<?=$entidades->fields['id_entidad']?>" <?if($id_entidad==$entidades->fields['id_entidad'])echo "selected"?>>
          <? if(strlen($entidades->fields['nombre'])>85)
            { $str=substr($entidades->fields['nombre'],0,85);
              echo "$str...";
            }
            else
             echo $entidades->fields['nombre'];
          ?>
        </option>
       <?
       	$entidades->MoveNext();
       }
       ?>
      </select>
    </td>
    <input type="hidden" name="h_id_entidad" value="<?=$id_entidad?>">
   </tr>
   <tr>
    <td colspan="2">
     <table width="100%">
      <tr>
       <td>
        <b>Fecha Prevista de Devolución</b> <input type="text" <?=$permiso?> name="fecha_vencimiento" value="<?=fecha($fecha_vencimiento)?>"> <? echo link_calendario("fecha_vencimiento"); ?>
       </td>
       <td align="center">
        <?
        //si el estado es en curso, se puede especificar la fecha de devolucion
        if($estado==1 ||$estado==2){
        ?>
        <b>Fecha de Devolución</b> <input type="text" name="fecha_devolucion" value="<?=fecha($fecha_devolucion)?>" <?if($estado==2)echo "disabled";?>> <?echo link_calendario("fecha_devolucion"); ?>
        <?
        }
        ?>
       </td>
      </tr>
      <tr>
      	<td>
      		<b>Muestra para el cliente: &nbsp;&nbsp;</b>
      		<input type="checkbox" name="sel_cliente" value="1" onclick="seleccionar_cliente(this.checked)">
      	</td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
 </td>
</tr>
</table>
<br>
<table width="95%" align="center"  class=bordes>
 <tr>
  <td>
   <table width="100%" align="center">
    <tr id=mo><td align="center"><b>Agregar Ordenes de Producción</b></td></tr>
    <tr><td>
      <table width="100%" align="center" id="productos">
       <tr id=ma>
         <td width="4%" >&nbsp;   </td>
        <td width="17%">Nro Orden</td>
        <td width="54%">Producto </td>
        <td width="14%">Cant Maq.</td>
        <td width="11%">ID</td>
       </tr>
       <?
       if($id_muestra){
        //traemos los productos de la muestra
        $items=get_items_ordprod($id_muestra);
        $cnr=1;

        //SI SE LLEGA A RECARGAR LA PAGINA EN ALGUN MOMENTO, SE PIERDEN ESTOS
        //DATOS. ENTONCES HAY QUE AGREGAR LA PARTE QUE TOMA DATOS DEL POST.
        //FUNCIONAMIENTO SIMILAR AL DE ORDENES DE COMPRA

        for($x=0;$x<$items['cantidad_ord'];$x++){
       	?>
         <input type="hidden" name="idp_<?=$x?>" value="<?=$items[$x]['id_producto']?>">
         <tr>
          <td><div align="center">
              <input type="checkbox" <?=$permiso?> name="chk" value="1">
         </div> </td>
          <td align="center">
           <? $nro_orden=$items[$x]['nroorden']?>
           <input name="nroorden_<?=$x?>" type="text" size="8" <?=$permiso?> readonly value='<?=$items[$x]['nroorden']?>' ><a target="_blank" href='<?=encode_link("../ordprod/ordenes_nueva.php",array("nro_orden"=>$nro_orden,"modo"=>"modificar","volver"=>"ver_seguimiento_ordenes.php"))?>'><font color="blue"><b>&nbsp;Ir</b></font></a>
           <td>
           <div align="center">
            <textarea name="desc_<?=$x?>" cols="50" rows="1" wrap="VIRTUAL" <?=$permiso?> readonly ><?=$items[$x]['producto']?></textarea>
           </div>
          </td>
             <td align="center">
           <input name="cantmaq_<?=$x?>" type="text" size="6" <?=$permiso?>  style='text-align:right' value="<?=$items[$x]['cantidad']?>" >
          </td>
          <td align="center">

           <? $id_lic=$items[$x]['lic'];?>
           <input name="lic_<?=$x?>" type="text" size="6" <?=$permiso?> readonly style='text-align:right' value="<?=$items[$x]['lic']?>"> <a target="_blank" href='<?=encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_lic))?>'><font color="blue"><b>&nbsp;Ir</b></font></a>
          </td>
		   </tr>
       <?

        }//de for($x=0;$x<$items['cantidad'];$x++)
         $total_muestras=$items['total_muestra'];
         $items=$x;
       }//de if($id_reclamo_partes)
        ?>
        <input type="hidden" name="items" value="<?=$items?>">
       </table>
       <?
       if($id_muestra){
       ?>
       <script>total_muestra=parseInt(<?=$total_muestras?>);</script>
       <?
       }
       ?>
       <table width="100%">
        <tr>
         <td colspan="5" align="center">
          <br>
          <input type="button" name="Agregar" value="Agregar"  <?=$permiso?> onclick="nuevo_item()">
          <input type="button" name="Eliminar" value="Eliminar" <?=$permiso?>
           onclick=
           "
           if (confirm('¿Está seguro que desea eliminar los items seleccionados ?'))
	        borrar_items()
	       "
          >
         </td>
        </tr>
       </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<br>
<br>
<table width="95%" align="center" class=bordes>
    <tr id=mo><td colspan=2 align=center> <b>Costo agregados a la muestra</b></td></tr>
	<tr>
       <td align=center>
        Ingrese el Costo U$S:
        <input type=text name="costo" value="<?=$costo;?>" onkeypress="return filtrar_teclas(event,'0123456789.');">
	 </td>
	 <td align=center>
		<font color="green"><i>(ADVERTENCIA: ingrese un punto como separador de decimales, no utilizar separadores de miles).</i></font>
	 </td>
 </tr>
</table>
<br>
<input type="hidden" name="id_muestra" value="<?=$id_muestra?>">
<?
if ($id_muestra){
            $sql=" select id_foto_muestra,nombre_archivo,usuario,fecha from foto_muestra where id_muestra=$id_muestra";
            $res=sql($sql) or fin_pagina();
            ?>
            <table width="95%" align=center class=bordes>
               <tr id=mo><td >Fotos</td></tr>
               <tr>
               <td>
               <table width=100% align=center>
               <tr>
               <?
               for ($i=0;$i<$res->recordcount();$i++){
                  if ($i % 4 ==0) echo "</tr><tr>";
                  $link=encode_link("ver_foto_ampliada.php",array("id_foto_muestra"=>$res->fields["id_foto_muestra"],"id_muestra"=>$id_muestra))           
               ?>
                   <td width=25% align=center>
                   <img width=120px height=100px src="<?="./Fotos/$id_muestra/".$res->fields["nombre_archivo"]?>" onclick="window.open('<?=$link?>')">
                   <br>
                   <input type=checkbox name="chk_fotos[]" value="<?=$res->fields["id_foto_muestra"]?>">
                   <?=$res->fields["nombre_archivo"]?>
                   </td> 
               <?
               $res->movenext();   
               }
               $link=encode_link("subir_foto.php",array("id_muestra"=>$id_muestra));   
               ?>
               </table>
               </td></tr>
               <tr>
                  <td align=center>
                     <input type=button name=agregar_foto value="Agregar Foto" onclick="window.open('<?=$link?>')">
                     &nbsp;
                     <input type=submit name=eliminar_foto value="Eliminar Foto">
                  </td>
               </tr>
            </table>
            <br>
            <!--
            Inserto si esta en estado en curso los productos que hay en productos descontados
           -->
<?
} // de foto muestra

if ($id_muestra!=""){
 
if ($estado==0) {	
      $sql="select * from ( 
            select sum(ep.cantidad) as cantidad,pe.id_prod_esp,muestra.id_licitacion,
                       pe.descripcion,pe.precio_stock
                 
            from muestras.muestra
            join stock.en_produccion ep using (id_licitacion)
            join stock.en_stock using (id_en_stock)
            join general.producto_especifico pe using (id_prod_esp)
            where id_muestra=$id_muestra
            group by pe.id_prod_esp,muestra.id_licitacion,
                       pe.descripcion,pe.precio_stock
            ) as principal where principal.cantidad > 0 order by principal.descripcion";   
      $res=sql($sql) or fin_pagina();
      $sql="select sum (cantidad*precio_stock) as suma from ( 
            select sum(ep.cantidad) as cantidad,pe.precio_stock
                 
            from muestras.muestra
            join stock.en_produccion ep using (id_licitacion)
            join stock.en_stock using (id_en_stock)
            join general.producto_especifico pe using (id_prod_esp)
            where id_muestra=$id_muestra
            group by pe.id_prod_esp,muestra.id_licitacion,
                       pe.descripcion,pe.precio_stock
            ) as principal where principal.cantidad > 0";
      $res_suma=sql($sql) or fin_pagina();
      $mensaje="Los Productos seleccionados  se descontaran del Stock en Producción cuando la muestra pase a En Curso";
}
if ($estado==1){      
      $sql=" select pd.id_prod_esp,pd.id_productos_descontado,pe.descripcion,pd.cantidad,pe.precio_stock
         from productos_descontado pd
         join producto_especifico pe using (id_prod_esp)
         where pd.id_muestra=$id_muestra and pd.activo=1";
      $res=sql($sql) or fin_pagina();
      $sql="select sum (pd.cantidad*pe.precio_stock) as suma
         from muestras.productos_descontado pd
         join general.producto_especifico pe using (id_prod_esp)
         where pd.id_muestra=$id_muestra and pd.activo=1";
      $res_suma=sql($sql) or fin_pagina();
      $mensaje="Los Productos seleccionados pasaran al stock de Bs. As. cuando la muestra pase a Historial";      
}
?>
  <input type=hidden name="cantidad_productos" value="<?=$res->recordcount()?>">
  <table width="95%" align="center" class=bordes>
     <tr id=mo >
        <td colspan=4><?=$mensaje?></td>
     </tr>
    <tr id=ma>
        <td width=1%><input type="checkbox" name="seleccionar_todos" value="1" onclick="seleccionar(this.checked)"></td>
        <td>Cantidad</td>        
        <td>Producto</td>
        <td>Precio</td>
    </tr>
   <?
   for($i=0;$i<$res->recordcount();$i++){
   ?>
     <tr <?=atrib_tr() ?> >
     <!--onclick="marcar_fila(<?=$i?>,this)" -->
        <td align=center ><input type=checkbox name="chk_producto_<?=$i?>" value="<?=$res->fields["id_prod_esp"]?>" ></td>
        <td align=center ><input type=text size="4" name="cantidad_<?=$i?>" value="<?=$res->fields["cantidad"]?>" ></td>        
        <td align=left   >&nbsp;&nbsp;<?=$res->fields["descripcion"]?></td>
        <td align=right  >U$S <?=formato_money($res->fields["precio_stock"])?></td>
     </tr>
   <?
   $res->movenext();
   }   
   ?>
   <tr <?=atrib_tr() ?> >
	<td align="right" colspan="3" >Monto Total:</td>
	<td align=right  >U$S <?=formato_money($res_suma->fields["suma"])?></td>
   </tr>	
</table>
<?}?>
<table width="95%" align="center" class=bordes>
 <tr>
  <td align="center">
   <table>
    <tr></tr>
    <tr><td align="center"><br><b>Observaciones</b></td></tr>
    <tr><td align="center">
      <textarea cols="100" rows="5" name="observaciones"><?=$observaciones?></textarea>
    </td></tr>
   </table>
  </td>
 </tr>
</table>
<br>
<table width="95%" align="center" class=bordes>
  <tr>
    <td>
     <?
     gestiones_comentarios($id_muestra,"muestras",1);
     ?>
    </td>
  </tr>
</table>
<br>
<input type="hidden" name="guardar" value="0">
<?
  
  (permisos_check("inicio","adm_muestras"))?$permiso="":$permiso="disabled";
  ($estado>0 || $estado=="")?$disabled_encurso="disabled":$disabled_encurso="";
  ($estado==0 || $estado==2)?$disabled_historial="disabled":$disabled_historial="";
  ($estado==2)?$disabled_guardar="disabled":$disabled_guardar="";
  
?>
<div align="center">
 <input type="submit" name="boton_guardar" value="Guardar" <?=$disabled_guardar?> onclick='return control_datos(0)'>
 <input type="submit" name="boton_guardar_comentario" value="Guardar Comentario" onclick='return control_datos(0)'> 
 <input type="submit" name="en_curso" value="En Curso" title="La muestra entra 'En Curso'" <?=$disabled_encurso?> onclick='document.all.guardar.value=1;return control_datos(1)' <?=$permiso?>>
 <input type="submit" name="historial" value="Historial" title="Los productos de la muestra han sido devueltos" <?=$disabled_historial?> <?=$permiso?> onclick='document.all.guardar.value=1;return control_datos(2)'>
 <input type="button" name="volver" value="Volver" onclick="document.location='seguimiento_muestras.php'">
</div>
</form>
</body>
</html>
<? fin_pagina();?>    