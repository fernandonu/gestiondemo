<?PHP
/*
$Author: ferni $
$Revision: 1.57 $
$Date: 2005/11/21 21:22:56 $
*/

include("../../config.php");

echo $html_header;

if ($_POST['editar']==1) $aviso='Al presionar el botón Guardar se edita el producto seleccionado. <br> Para ingresar un nuevo producto presione el botón Nuevo y luego el botón Guardar. ';
else $aviso='';
if($_POST["Volver"]=="Volver")
{ 	
  $tipo_p=$_POST['select_producto'];
  if ($tipo_p=='') $tipo_p=$parametros["tipo"];
  
 
 $link4 = encode_link($html_root."/index.php",array("menu"=>"productos1","extra"=>array("pagina"=>$parametros["pagina"],"tipo"=>$tipo_p, "texto"=>$parametros["texto"], "campo"=>$parametros["campo"])));
 echo "<html><head><script language=javascript>"; 
 echo "window.parent.location='$link4';";
 echo "</script></head></html>";  
 die;
}

if ($_POST['guardar']=='Guardar') {
 
  if ($_POST['chk']=='on'){
   $tipo_sel=$_POST['nuevo_tipo']; //si ingresa nuevo tipo de producto se guarda en la bd
   $q_control="select descripcion from tipos_prod where descripcion ilike '$tipo_sel'";
   $res_control=sql($q_control) or die;
     if ($res_control->RecordCount() ==0){ 
       $q="insert into tipos_prod (codigo,descripcion,vigente_desde) values ('".
        strtolower($tipo_sel)."','".$tipo_sel."','Now')";
        sql($q) or die;
        $tipo_sel= strtolower($tipo_sel); //el codigo es en minuscula
      } 
	  else  ERROR ('EL TIPO INGRESADO YA EXISTE');
  }
 else $tipo_sel=$_POST['select_producto'];
 
 if ($_POST['editar'] ==0){ //insert
 //control para ver si ya existe la marca y el modelo
  $query="select id_producto from productos where tipo='".$tipo_sel."' and marca='".$_POST['marca']."' and modelo='".$_POST['modelo']."' ";
  $res_query=sql($query) or die;
  if ($res_query->RecordCount()==0){ 
   
  	 if (($_POST['activo_productos'] == 'on' ) or ($_POST['chk']== 'on')){
  	 	$activo_productos="1";
  	 }
  	 else {
  	 	$activo_productos="0";
  	 }

     $query_insert="INSERT INTO productos (tipo,marca,modelo,desc_gral,activo_productos) VALUES ('".$tipo_sel."','".$_POST['marca']."','".$_POST['modelo']."','".$_POST['descripcion']."','".$activo_productos."')"; 
     sql($query_insert) or die;
	   //si es placa madre, insertamos los onboard de ese mother
          if ($_POST['select_producto']=='placa madre')
          {//obtenemos el ultimo id_producto que se acaba de insertar
           //para poder agregar sus componentes onboard, si tiene.
		  
          $query="select max(id_producto) as max from productos";
          $max=sql($query) or die;
           
           if($_POST["video"]== "onboard")
           {$query="insert into onboard (id_producto,componente) values(".$max->fields['max'].",'video')";
            sql($query) or die;
           } 
           if($_POST["sonido"]== "onboard")
           {$query="insert into onboard (id_producto,componente) values(".$max->fields["max"].",'sonido')";
            sql($query) or die;
           } 
           if($_POST["red"]== "onboard")
           {$query="insert into onboard (id_producto,componente) values(".$max->fields["max"].",'red')";
            sql($query) or die;
           }
           if($_POST["red2"]== "onboard")
           {$query="insert into onboard (id_producto,componente) values(".$max->fields["max"].",'red2')";
            sql($query) or die;
           } 
           if($_POST["modem"]== "onboard") 
           {$query="insert into onboard (id_producto,componente) values(".$max->fields["max"].",'modem')";
           sql($query) or die;
           } 
          } //fin si es mother
     
	 if ($_POST['precio'] !=''){
	 //selecciona el id del producto insertado
	 $query="select max(id_producto) as max from productos";
     $max=sql($query) or die;
     $insert="INSERT INTO precios (id_producto,id_proveedor,precio,observaciones) VALUES ('".$max->fields["max"]."','".$_POST['select_proveedor']."','".$_POST['precio']."','".$_POST['desc_precio']."')";   	
     sql($insert) or die;
	  //$tipos_prov=$db->Execute($insert) or die ($db->ErrorMsg().$insert);
	  }    
  } //fin de recordcount
  else { ERROR ('EL PRODUCTO YA EXISTE'); }

} //fin if guardar
else { //editar 
  
     if ($_POST['activo_productos'] == 'on' ){
  	 	$activo_productos="1";
  	 }
  	 else {
  	 	$activo_productos="0";
  	 }
  	 
	$query_update="update productos SET  marca = '".$_POST['marca']."', modelo = '".$_POST['modelo']."', desc_gral='".$_POST['descripcion']."', activo_productos='".$activo_productos."'   WHERE  id_producto= '".$_POST['prod_mostrar']."'";
   sql($query_update) or die;
   
    if ($_POST['select_producto']=='placa madre'){
     //eliminamos todos los onboard de la mother seleccionada y reinsertamos
      //todos los checkeados luego de editar
      $id_p=$_POST['prod_mostrar'];
      
      $query="delete from onboard where onboard.id_producto=".$id_p;
      sql("$query") or die;

      //si es placa madre el producto editado, reinsertamos los onboard de ese mother
      if($_POST["video"]=="onboard")
           {
           	$query="insert into onboard (id_producto,componente) values(".$id_p.",'video')";
            sql($query) or die;
           } 
      if($_POST["sonido"]=="onboard")
           {$query="insert into onboard (id_producto,componente) values(".$id_p.",'sonido')";
           sql($query) or die;
           } 
      if($_POST["red"]=="onboard")
           {$query="insert into onboard (id_producto,componente) values(".$id_p.",'red')";
          sql($query) or die;
           } 
      if($_POST["red2"]=="onboard")
           {$query="insert into onboard (id_producto,componente) values(".$id_p.",'red2')";
            sql($query) or die;
           }      
      if($_POST["modem"]=="onboard") 
           {$query="insert into onboard (id_producto,componente) values(".$id_p.",'modem')";
            sql($query) or die;
           } 
     } //fin if placa madre
 
 } // fin else editar
 
}  //fin de guardar
 

function cargar_prod($tipo_prod){
global $db; 
if ($tipo_prod=='placa madre'){
   $query_cargar= "select id_producto, marca, modelo, desc_gral, componente, activo_productos from productos  left join onboard using (id_producto)  where productos.tipo='".$tipo_prod."' order by id_producto" ;
}
else {
   $query_cargar= "select id_producto, marca, modelo, desc_gral,activo_productos from  productos where tipo='$tipo_prod' order by id_producto" ;
} 

$resultados_cargar=sql($query_cargar) or die;
return $resultados_cargar;
} //fin cargar_prod

if  (($_POST['select_producto']) || ($parametros['tipo']!='')){
	if ($_POST['select_producto']=='') $tipo=$parametros['tipo'];
	 else $tipo=$_POST['select_producto'];
 $resultados_cargar=cargar_prod($tipo);
 $resultados_cargar->MoveFirst();
 while (!$resultados_cargar->EOF)
 { 
 
 if (($_POST['select_producto']=='placa madre') || ($parametros['tipo']=='placa madre')) {
 	
    if ($id_prod_ant==$resultados_cargar->fields['id_producto']){
       //ya se guardo una componente ?>
	<script> 
	   producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["componente"]["<? echo $resultados_cargar->fields['componente']?>"]="<?php if($resultados_cargar->fields['componente']){echo $resultados_cargar->fields['componente'];}else echo "null";?>";
	</script>
	 <? }
	 else { ?>
	 <script>  
    var producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>=new Array(); 
        producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["marca"]="<?php if($resultados_cargar->fields['marca']){echo $resultados_cargar->fields['marca'];}else echo "null";?>";
        producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["modelo"]="<?php if($resultados_cargar->fields['modelo']){echo $resultados_cargar->fields['modelo'];}else echo "null";?>";
        producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["desc"]="<?php if($resultados_cargar->fields['desc_gral']){echo $resultados_cargar->fields['desc_gral'];}else echo "null";?>";
        producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["activo_productos"]="<?php if($resultados_cargar->fields['activo_productos']){echo $resultados_cargar->fields['activo_productos'];}else echo "null";?>";
        producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["componente"]= new Array();
        producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["componente"]["<? echo $resultados_cargar->fields['componente']?>"]="<?php if($resultados_cargar->fields['componente']){echo $resultados_cargar->fields['componente'];}else echo "null";?>";
	    
	 </script>
		<? }
 }
 else { //no es placa madre?> 
   <script language="javascript">
    var producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>=new Array();
    producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["marca"]="<?php if($resultados_cargar->fields['marca']){echo $resultados_cargar->fields['marca'];}else echo "null";?>";
    producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["modelo"]="<?php if($resultados_cargar->fields['modelo']){echo $resultados_cargar->fields['modelo'];}else echo "null";?>";
    producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["desc"]="<?php if($resultados_cargar->fields['desc_gral']){echo $resultados_cargar->fields['desc_gral'];}else echo "null";?>";
    producto_<?php echo $resultados_cargar->fields["id_producto"]; ?>["activo_productos"]="<?php if($resultados_cargar->fields['activo_productos']){echo $resultados_cargar->fields['activo_productos'];}else echo "null";?>";
   </script>
<?
      } //fin else no es placa madre
  ?>
 
 <? 
 $id_prod_ant=$resultados_cargar->fields["id_producto"];
 $resultados_cargar->MoveNext();
}
$resultados_cargar->MoveFirst();  
} //fin if 
?>

<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
<style type="text/css">
.boton{
        font-size:10px;
        font-family:Verdana,Helvetica;
        font-weight:bold;
        color:white;
        background:#638cb9;
        border:0px;
        width:160px;
        height:19px;
       }
</style>

<script language="JavaScript1.2">
//funciones para busqueda abrebiada utilizando teclas en la lista que muestra los clientes.
var digitos=15 //cantidad de digitos buscados
var puntero=0
var buffer=new Array(digitos) //declaración del array Buffer
var cadena=""

function buscar_op(obj){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13){
       borrar_buffer();
      // if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0
    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       for (var opcombo=0;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}

function borrar_buffer(){
   //inicializa la cadena buscada
    cadena="";
    puntero=0;
}
</script>

<script language="javascript">

function habilitar(obj){
if (obj) {
document.form1.select_producto.disabled = obj.checked; 
document.form1.nuevo_tipo.disabled = !obj.checked;
document.form1.marca.disabled= !obj.checked;
document.form1.modelo.disabled= !obj.checked;
document.form1.descripcion.disabled= !obj.checked;
document.form1.guardar.disabled= !obj.checked;
//document.form1.nuevo.disabled= !obj.checked;
if (typeof (document.form1.control_precio) !='undefined'){
 document.form1.precio.disabled= !obj.checked;
 document.form1.desc_precio.disabled= !obj.checked;

}
if (document.form1.prod_mostrar) document.form1.prod_mostrar.disabled = obj.checked;
  if (obj.checked)  {
     document.form1.marca.value='';
     document.form1.modelo.value='';
     document.form1.descripcion.value='';
     tipo=eval(document.all.select_producto);
     if (tipo.value =='placa madre') document.form1.submit();
     }
  else {
    document.form1.nuevo_tipo.value='';
   }
 document.form1.editar.value=0; //indica que se debe realizar un insert 
 } 
else { 
 document.form1.select_producto.disabled = true; 
 document.form1.marca.disabled= false;
 document.form1.modelo.disabled= false;
 document.form1.descripcion.disabled= false;
 document.form1.precio.disabled= false;
 document.form1.desc_precio.disabled= false;
 document.form1.prod_mostrar.disabled= false;
 //document.form1.nuevo.disabled= false;
 document.form1.guardar.disabled= false;
 document.form1.marca.value='';
 document.form1.modelo.value='';
 document.form1.descripcion.value='';
 document.form1.chk.checked=true;
 document.form1.editar.value=0;
 if (document.form1.prod_mostrar) document.form1.prod_mostrar.disabled = true;
}

if (typeof(document.all.control_precio)=='undefined') {
	document.all.control.value=1;
	document.form1.submit();
}

} //fin habilitar


function set_datos(){
//muestra la marca, modelo  y desc general del producto seleccionado
obj= eval(document.all.prod_mostrar.options[document.all.prod_mostrar.selectedIndex]);
document.all.prod_sel.value=obj.value;
id_sel=eval (document.all.prod_sel);

info=eval ("producto_"+ id_sel.value);
document.all.marca.value=(info ['marca']);
document.all.modelo.value=(info ['modelo']);
document.all.descripcion.value=(info ['desc']);
if (info['activo_productos']=='t'){
document.all.activo_productos.checked=1;
}
else{
document.all.activo_productos.checked=0;
}
document.all.editar.value=1; //indica que se debe realizar un update
tipo=eval(document.all.select_producto);
if  (tipo.value =='placa madre'){
document.all.video.checked=false;
document.all.sonido.checked=false;
document.all.red.checked=false;
document.all.red2.checked=false;
document.all.modem.checked=false;
  if  (info["componente"]['video']== 'video') document.all.video.checked=true;
  if  (info["componente"]['sonido']== 'sonido') document.all.sonido.checked=true;
  if  (info["componente"]['red']== 'red') document.all.red.checked=true;
  if  (info["componente"]['red2']== 'red2') document.all.red2.checked=true;
  if  (info["componente"]['modem']== 'modem') document.all.modem.checked=true;
}

document.form1.submit();  

}

function control_datos()
{ var aux; 
if (document.form1.chk.checked) {
	   if (document.all.nuevo_tipo.value=='' || document.all.nuevo_tipo.value==' ')
        {
	     alert ('Debes completar el tipo de producto');
	     return false;
        }
	 }
	 else {
	  if (document.all.select_producto.value=='vacio')
       {
	   alert ('Debes seleccionar el tipo de producto');
	   return false;
       }  
	 }
	 
	 if (document.all.marca.value=='' || document.all.marca.value==' ')
    {
	 alert ('Debes completar la marca del producto');
	 return false;
    }
	if (document.all.modelo.value=='' || document.all.modelo.value==' ')
    {
	 alert ('Debes completar el modelo del producto');
	 return false;
    }
	if (document.all.descripcion.value=='' || document.all.descripcion.value==' ')
    {
	 alert ('Debes completar la descripción del producto');
	 return false;
    }
	if(document.all.marca.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo marca');
        return false;
    }
    if(document.all.modelo.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo modelo');
        return false;
    }
   	if(document.all.descripcion.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo descripcion');
        return false;
    }
	
if (typeof (document.all.select_proveedor)!= 'undefined')
{ if ((document.all.select_proveedor.value!="0") && (document.all.precio.value=='') || (document.all.precio.value==' '))
   { alert("Debe ingresar el precio que ofrece el proveedor seleccionado");
    return false;
   }
 if ((document.all.select_proveedor.value=="0") && (document.all.precio.value!=""))
   {alert("Debe seleccionar el proveedor para el precio ingresado");
    return false;
   } 
 
 if (document.all.precio.value.indexOf(',')!=-1)
  {alert("Especifique la parte fraccionada con .");
  return false;
  } 

 if ((document.all.select_proveedor.value!="0") && (document.all.precio.value!=""))
 {
  aux=parseFloat(document.all.precio.value);  
  if (!isNaN(aux)) 
  { //alert("Usted ha actualizado el precio con exito");
    return true;
  }
  else { alert("El valor del campo precio debe ser un número");
         return false;
       } 
 } 
}
}

function limpiar(){
document.all.marca.value='';
document.all.modelo.value='';
document.all.descripcion.value='';
document.all.nuevo_tipo.value='';
tipo=eval(document.all.select_producto);
if  (tipo.value =='placa madre'){
document.all.video.checked=false;
document.all.sonido.checked=false;
document.all.red.checked=false;
document.all.red2.checked=false;
document.all.modem.checked=false;
}
<? if ($_POST['editar']!=0) {?>
 document.form1.submit();
<? }
 else  { ?> 
 document.all.nuevo_tipo.value='';
 document.all.desc_precio.value='';
 document.all.precio.value='';
 <? }?>
}
 
</script>
<?
//para recargar la pagina
$tipo_prod=$_POST['select_producto'];
if ($tipo_p=='') $tipo_prod=$parametros["tipo"];
$link1=encode_link("altas_prod.php",array("pagina"=>$parametros["pagina"],"tipo"=>$tipo_prod));
?>

<form action="<?php $link1?>" method="post" name="form1">
<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/productos/ayuda_nuevoprod.htm" ?>', 'NUEVO PRODUCTO')" >
    </div>  
<input name="prod_sel" type="hidden" value="">
<input type='hidden' name='control' value=0>
<input name="editar" type="hidden" value='<? if(isset($_POST['editar'])) echo $_POST['editar']; else echo 0;?>' >  

 <TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
 <br>
 <tr>   <td width="51%">
   <strong> Tipo de producto</strong>&nbsp;
   <select name='select_producto'  Onchange="document.all.editar.value=0;document.form1.submit();" <? if ($_POST['control']==1) echo 'disabled'?>>
   <option value='vacio'></option>
   <?   
   $query_prod="SELECT DISTINCT codigo,descripcion FROM tipos_prod order by descripcion";
   $resultados_prod = sql($query_prod) or die;
   $filas_encontradas = $resultados_prod->RecordCount();
        for($i=0;$i<$filas_encontradas;$i++)  {
            if($resultados_prod->fields["codigo"] ==$_POST["select_producto"] ||
             	$resultados_prod->fields["codigo"] == strtolower($_POST['nuevo_tipo_prod'])|| ($parametros['tipo']==$resultados_prod->fields["codigo"]))
             {
             	$string=$resultados_prod->fields["descripcion"];
                echo "<option selected value='".$resultados_prod->fields['codigo']."'> $string </option>";
                $resultados_prod->Movenext();
             }
            else {
                $string=$resultados_prod->fields["descripcion"];
                echo "<option value='".$resultados_prod->fields['codigo']."' > $string  </option>";
                $resultados_prod->Movenext();
                }
       }

  ?>
 </select></td>

<td width="48%"> <input type="checkbox" name="chk" <? if ($_POST['control']==1)echo 'checked'?> onclick="habilitar(this);" > <b> Nuevo Tipo de producto: </b ><input name="nuevo_tipo" type="text"  value='<? echo $_POST['nuevo_tipo']?>' onFocus="habilitar();" > </td> 
</tr> 
<tr> <td colspan=2><hr></td></tr> 
<tr><td colspan=2> <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' ))  
echo "<font color='green' size=3>" .'Debe seleccionar o ingresar un tipo de producto para ingresar un nuevo producto.<br> Los campos marcados con (*) son obligatorios.'."</font>" ; 
elseif ($_POST['editar']!=1) echo "<font color='green' size=3>" .'Los campos marcados con (*) son obligatorios.'."</font>" ?>
<tr><td>&nbsp;</td></tr>
 </td></tr>
 <tr> <td colspan=2><? echo "<font color='red' size=3>" .$aviso."</font>" ?></td></tr>
 </table>
 <table width="100%" align="center" class="bordes" cellpaddindg=2 cellspacing=2>
 <tr bgcolor="<? echo $bgcolor1; ?>">
  <td height="50%" align="center"><strong>INFORMACION DEL PRODUCTO</strong></td>
  <td height="50%" align="center" ><strong>PRODUCTOS CARGADOS</strong></td>
  </tr>

 <tr bgcolor=<?=$bgcolor_out?>>
   <td> <strong>Marca*: </strong> &nbsp; <input name="marca" type="text" size="30" value='<? if ($_POST['editar']!=0)  echo $_POST['marca'] ?>' 
                                                                                            <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>> 
																							&nbsp; <? if ($_POST['editar']!=0) echo "<input type='button' name='nuevo' value='Nuevo' title= 'Limpia el formulario, para agregar un nuevo producto' onclick='document.all.editar.value=0; limpiar();'>" ?>  </td>
  <td rowspan=8>  <div align="center">
		 <select name="prod_mostrar" size="14" onChange="set_datos();" <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>> 
             <? if (isset($resultados_cargar)){
			
			 while (!$resultados_cargar->EOF)
	           {
                if ($id_ant!=$resultados_cargar->fields['id_producto']) {?>
				
                <option value="<? echo $resultados_cargar->fields['id_producto']; ?>" <? if (($_POST['editar']!=0) && ($_POST['prod_mostrar']==$resultados_cargar->fields['id_producto'])) echo 'selected';?>>
                <? echo $resultados_cargar->fields['desc_gral']; ?>
                </option>
				
                <? }
                    $id_ant=$resultados_cargar->fields['id_producto'];
                 	$resultados_cargar->MoveNext(); 
	           } ?>
			
		</select>  
		<? } ?>
  
  
        </div> </td>
 </tr>
 <!--<tr bgcolor=<?=$bgcolor_out?>>
  <td nowrap align="center"> </td>
  <td width="1%" align="center" nowrap> </td>
 </tr>-->
 <tr bgcolor=<?=$bgcolor_out?>>
 <td><strong>Modelo*:</strong>
   <input name="modelo" type="text" size="30" value='<? if ($_POST['editar']!=0) echo $_POST['modelo'];?>' <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>></strong></td>
 </tr>
 <tr bgcolor=<?=$bgcolor_out?>>
 <td> <strong>Descripción General*: </strong>
   <input name="descripcion" type="text" size=60 value='<? if ($_POST['editar']!=0) echo $_POST['descripcion'];?>' title='Es el texto con el cual se referencia al producto' <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>></td>
 </tr>
 
 <tr bgcolor=<?=$bgcolor_out?>>
 <td><strong>Producto Activo: </strong>
 		<? if ($_POST['editar']!=0){
 		   		if ($_POST['activo_productos']=='on'){
 					$marca_check="checked";
 				}
 				else {
 					$marca_check="";
 				}
 			}
 			else{
 				$marca_check="checked";
 			}
 		?>
 		<input type="checkbox" name="activo_productos" <?=$marca_check?> title='Indica si esta o no el Producto Activo' <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>>
 </td>
 </tr>
 
 </table>
  
 
 <br>
<?php if (($_POST['select_producto']=='placa madre') || ($parametros['tipo']=='placa madre')){ ?>
<table align="center">
<tr  bgcolor='<?echo $bgcolor1?>' align="center">
 <td colspan="2"><font color=<?echo $bgcolor3?>><b>Especifique componentes onboard para la motherboard</b></font></TD>
 </tr> 
<tr>
 <td bgcolor='#CCCCCC' align="center" colspan="2">
   <input type="checkbox" name="video" value="onboard" <? if ($_POST['video']) echo 'checked'?>> Video &nbsp;
   <input type="checkbox" name="sonido" value="onboard" <? if ($_POST['sonido']) echo 'checked'?>> Sonido &nbsp;
   <input type="checkbox" name="red" value="onboard" <? if ($_POST['red']) echo 'checked'?>> LAN &nbsp;
   <input type="checkbox" name="red2" value="onboard" <? if ($_POST['red2']) echo 'checked'?>> LAN2 &nbsp;
   <input type="checkbox" name="modem" value="onboard" <? if ($_POST['modem']) echo 'checked'?>> Modem  &nbsp;
 </td>
</tr> 
</table>  <? } ?>

<? if ($_POST['editar']!=1){
 $aviso2='No es obligatorio completar estos campos.';
 echo "<input type='hidden' name='control_precio' value=0>";	
 $query_prov="SELECT id_proveedor, razon_social FROM proveedor where activo='true' order by razon_social";
 $res_prov=sql($query_prov) or die;
 
?> 
<table width="100%" height="107" align="center">
 <tr bgcolor="<? echo $bgcolor1; ?>">
   <td colspan="2" align="center" height="31"><B>INGRESAR PRECIO</B></td> 
 </tr>
 <tr><td colspan="2"><? echo "<div align='center'><font color='green' size=3>".$aviso2."</font></div>" ?></td></tr>
 <tr>
 <td width="56%"></td>
 <td><strong>Seleccione Proveedor </strong></td>
 </tr>
 <tr>
 <td> 
  <table> 
 <tr>
  <td width="360"><strong><br>Precio U$S:</strong>&nbsp;<input name="precio" type="text" value='<? if ($_POST['editar']!=0)echo $_POST['precio']?>' <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>> <br></td>
 <tr> 
 <tr><td>&nbsp; </td></tr>
  <td><strong>Descripción general de Precio:</strong> </td>
 </tr>
 <tr>
 <td><input name="desc_precio" type="text" size="60" value='<? if ($_POST['editar']!=0) $_POST['desc_precio']?>' <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>> </td> 
 </tr>
 </table> 
    </td>
  <td><select name="select_proveedor" size='8' onKeypress="buscar_op(this);" onblur="borrar_buffer()" onclick="borrar_buffer()">
     <option value=0 <? if ($_POST[control]!=1) echo 'selected'?> >Seleccione un proveedor </option>
     <?  
	  while (!$res_prov->EOF) 
		{?>
     <option value='<? echo $res_prov->fields['id_proveedor'] ?>' <? if (($_POST['editar']!=0) && ($_POST['select_proveedor']==$res_prov->fields['id_proveedor'])) echo 'selected'?>> <? echo $res_prov->fields["razon_social"] ?> </option>
     <? $res_prov->MoveNext();
		}
	 ?>
   </select></td>
 </tr>
 </table>
 <? } ?>
 

<br>
<table align="center" cellspacing="0">
<tr>
<td> <input type="submit" name="guardar" value="Guardar" onClick="return control_datos()" <? if ( $parametros['pagina']!='productos' && (!isset($_POST['select_producto']) || $_POST['select_producto'] =='vacio' )) echo 'disabled';?>></TD>
<td> <? if ($parametros['pagina']== 'productos') echo "<input type='submit' name='Volver' value='Volver'" ?> </td>
</tr>
</table>

</form> 

</body>
</html>