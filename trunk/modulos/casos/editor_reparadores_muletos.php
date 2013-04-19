<?php

include_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

$do=0;
extract($_POST,EXTR_SKIP);

if (($boton=="Guardar")&&($editar!="editar"))
	$do=1;
if(($boton=="Guardar")&&($editar=="editar"))
    $do=3;

if($do==1){
	 $db->StartTrans();
	 $query3="select nextval('reparadores_id_reparador_seq')as id_rep";
	 $id_rep=sql($query3,"<br> error al traer el id_reparador<br>") or fin_pagina();
	 $id_reparador=$id_rep->fields['id_rep'];
	 	 
	 $campos="(id_reparador,nombre_reparador,cuit,direccion,localidad,telefono,contacto,observaciones)";	
	 $query_insert="INSERT INTO casos.reparadores $campos VALUES ".
	 "($id_reparador,'$nombre','$cuit','$direccion','$localidad','$telefono','$contacto','$observaciones')";
	 sql($query_insert) or fin_pagina();
	 $db->CompleteTrans();
	 echo "<center><b><font size='3' color='red'>Se Agrego un Nuevo Reparador</font></b></center>";
	 
}//de if($do==1)
elseif ($do==3){	
   	 $db->StartTrans();
     $query_update="UPDATE casos.reparadores set nombre_reparador='$nombre',cuit='$cuit',direccion='$direccion',localidad='$localidad',telefono='$telefono',contacto='$contacto',observaciones='$observaciones' where id_reparador=$select_transporte ";
     sql($query_update) or fin_pagina();
     $db->CompleteTrans();  	   
     echo "<center><b><font size='3' color='red'>Se Modifico el Reparador</font></b></center>";
}
echo $html_header;
?>
<style type="text/css">
</style>
</head>
<?
$sql="select * from casos.reparadores order by nombre_reparador";
$datos_reparador=sql($sql) or fin_pagina(); 
?>

<SCRIPT LANGUAGE="JavaScript">
<?php
while (!$datos_reparador->EOF){
   $id_reparador=$datos_reparador->fields["id_reparador"];
?>
	var reparador_<?=$id_reparador?>=new Array();
<?php 
	$datos_t="";
	$datos_cuit="";
	$datos_d="";
	$datos_loc="";
	$datos_tel="";
	$datos_con="";
	$datos_c="";
	
	if($datos_reparador->fields["nombre_reparador"])
	{
	$datos_t=$datos_reparador->fields["nombre_reparador"];
	$datos_t=ereg_replace("\r\n","<br>",$datos_t);
	$datos_t=ereg_replace("\n","<br>",$datos_t);
	$datos_t=ereg_replace("'","",$datos_t);
	}
	
	if($datos_reparador->fields["cuit"])
	{
	$datos_cuit=$datos_reparador->fields["cuit"];
	$datos_cuit=ereg_replace("\r\n","<br>",$datos_cuit);
	$datos_cuit=ereg_replace("\n","<br>",$datos_cuit);
	$datos_cuit=ereg_replace("'","",$datos_cuit);
	}
	
	if($datos_reparador->fields["direccion"])
	{
	$datos_d=$datos_reparador->fields["direccion"];
	$datos_d=ereg_replace("\r\n","<br>",$datos_d);
	$datos_d=ereg_replace("\n","<br>",$datos_d);
	$datos_d=ereg_replace("'","",$datos_d);
	}
	
	if($datos_reparador->fields["localidad"])
	{
	$datos_loc=$datos_reparador->fields["localidad"];
	$datos_loc=ereg_replace("\r\n","<br>",$datos_loc);
	$datos_loc=ereg_replace("\n","<br>",$datos_loc);
	$datos_loc=ereg_replace("'","",$datos_loc);
	}
	
	if($datos_reparador->fields["telefono"])
	{
	$datos_tel=$datos_reparador->fields["telefono"];
	$datos_tel=ereg_replace("\r\n","<br>",$datos_tel);
	$datos_tel=ereg_replace("\n","<br>",$datos_tel);
	$datos_tel=ereg_replace("'","",$datos_tel);
	}
	
	if($datos_reparador->fields["contacto"])
	{
	$datos_con=$datos_reparador->fields["contacto"];
	$datos_con=ereg_replace("\r\n","<br>",$datos_con);
	$datos_con=ereg_replace("\n","<br>",$datos_con);
	$datos_con=ereg_replace("'","",$datos_con);
	}
	
	if($datos_reparador->fields["observaciones"])
	{
	$datos_c=$datos_reparador->fields["observaciones"];
	$datos_c=ereg_replace("\r\n","<br>",$datos_c);
	$datos_c=ereg_replace("\n","<br>",$datos_c);
	$datos_c=ereg_replace("'","",$datos_c);
	}
	
?> 
	reparador_<?=$id_reparador?>["nombre_reparador"]="<?=$datos_t?>";
	reparador_<?=$id_reparador?>["cuit_reparador"]="<?=$datos_cuit?>";
	reparador_<?=$id_reparador?>["direccion_reparador"]="<?=$datos_d?>";
	reparador_<?=$id_reparador?>["localidad_reparador"]="<?=$datos_loc?>";
	reparador_<?=$id_reparador?>["telefono_reparador"]="<?=$datos_tel?>";
	reparador_<?=$id_reparador?>["contacto_reparador"]="<?=$datos_con?>";
	reparador_<?=$id_reparador?>["observaciones_reparador"]="<?=$datos_c?>";
<?
$datos_reparador->MoveNext();
}
?>

function set_datos()
{
    switch(document.all.select_transporte.options[document.all.select_transporte.selectedIndex].value)
    {<?PHP
     $datos_reparador->Move(0);
     while(!$datos_reparador->EOF)
     {
      $id_reparador=$datos_reparador->fields["id_reparador"];	
     ?>
      case '<?=$id_reparador?>': info=reparador_<?=$id_reparador?>;break;
     <?
      $datos_reparador->MoveNext();
     }
     ?>
    }
    if(info["nombre_reparador"]!="null")
            document.all.nombre.value=info["nombre_reparador"];
            else
            document.all.nombre.value="";
            
    if(info["cuit_reparador"]!="null")
            document.all.cuit.value=info["cuit_reparador"];
            else
            document.all.cuit.value="";
            
                    
    if(info["direccion_reparador"]!="null")
            document.all.direccion.value=info["direccion_reparador"];
            else
            document.all.direccion.value="";
           
    if(info["localidad_reparador"]!="null")
            document.all.localidad.value=info["localidad_reparador"];
            else
            document.all.localidad.value="";
  
    if(info["telefono_reparador"]!="null")
            document.all.telefono.value=info["telefono_reparador"];
            else
            document.all.telefono.value="";
            
    if(info["contacto_reparador"]!="null")
            document.all.contacto.value=info["contacto_reparador"];
            else
            document.all.contacto.value="";
    
    if(info["observaciones_reparador"]!="null")
            document.all.observaciones.value=info["observaciones_reparador"];
            else
            document.all.observaciones.value=-1;
            
} //fin de la funcion set_datos()

//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos(){
	
	if (document.all.nombre.value=='' || document.all.nombre.value==' '){
	   alert ('Debe completar el nombre del Reparador');
	   return false;
    }
       
	if(document.all.nombre.value.indexOf('"')!=-1){
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
       return false;
    }
   
	if(document.all.cuit.value.indexOf('"')!=-1){
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Cuit');
       return false;
    }
   
    if(document.all.direccion.value.indexOf('"')!=-1)
       {
         alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
         return false;
       }
    
    if(document.all.localidad.value.indexOf('"')!=-1){
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Localidad');
       return false;
    }
       
    if(document.all.telefono.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono');
        return false;
    }
    
   if(document.all.contacto.value.indexOf('"')!=-1){
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Contacto');
       return false;
    }
       
    if(document.all.observaciones.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Observaciones');
        return false;
    }
    
    return true;

} //fin de la funcion control_datos()

//funcion para limpiar el formulario
function limpiar(){
 document.all.nombre.value='';
 document.all.cuit.value='';
 document.all.direccion.value='';
 document.all.localidad.value='';
 document.all.telefono.value='';
 document.all.contacto.value='';
 document.all.observaciones.value='';
 document.all.editar.value='';
 document.all.select_transporte.selectedIndex=-1;
 
}
//funcion para setear los valores de los campos de la entidad seleccionada, en
//la pagina de factura o remito


</SCRIPT>
<script language="JavaScript1.2">
//funciones para busqueda abrebiada utilizando teclas en la lista que muestra las entidades.
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

function editar_campos(boolvalue)
{
	boolvalue=!boolvalue;
	document.forms[0].nombre.readOnly=boolvalue;
	if (!boolvalue) {document.forms[0].nombre.focus();document.forms[0].nombre.select()};
	document.forms[0].cuit.readOnly=boolvalue;
	document.forms[0].direccion.readOnly=boolvalue;
	document.forms[0].localidad.readOnly=boolvalue;
	document.forms[0].telefono.readOnly=boolvalue;
	document.forms[0].contacto.readOnly=boolvalue;
	document.forms[0].observaciones.readOnly=boolvalue;
	document.forms[0].nombre.readOnly=boolvalue;
	document.forms[0].boton.disabled=false;
	
	
	
	if (boolvalue)
	{
	document.forms[0].boton.value='Editar';
	document.forms[0].boton.title='Editar datos de la entidad';
  
	}
	else
	{
   
		if (typeof document.forms[0].elegir!='undefined')
		document.forms[0].elegir.disabled=true;
		document.forms[0].boton.value='Guardar';
		document.forms[0].boton.title='Guardar datos';
	}


}

//funcion que simula la propiedad de readonly en un select
//se debe llamar en el evento onclick
function readonly()
{
	oselect=window.event.srcElement;//le asigno el que genero el evento
	oselect.selectedIndex2=oselect.selectedIndex;
	if (typeof oselect.onchange2=='undefined') oselect.onchange2=oselect.onchange;

	if (typeof oselect.readOnly=='undefined' || oselect.readOnly)
	{
		oselect.onchange=function (){oselect.selectedIndex=oselect.selectedIndex2;}
		oselect.readOnly=1;
	}
	else
	{
		oselect.onchange=oselect.onchange2;
		oselect.readOnly=0;
	}
}
</script>


<?=$informar;?>
<form name="form" method="post" action="editor_reparadores_muletos.php">
<input type=hidden name="editar" value="">
  <TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
    <!--el encabezado-->
  	<tr id=mo>
      <td width="40%" align="center">
      	<strong>INFORMACION DE LOS REPARADORES</strong>
      </td>
      <td width="60%" height="20" align="center" >
      <strong>REPARADORES CARGADOS</strong>
       </td>
    </tr>
    
    <tr>
      <td valign=top align="center">
      <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=#E0E0E0>
        <tr>
         <td colspan="2">
          <table>
          	<tr>
          		<td width="95%">
           			<font color=red><b>No ingresar datos con comillas dobles ("")</b></font>
           		</td>
           		<td nowrap align="left">
           		<input type="button" name="nuevo" value="Nuevo" title="Para agregar un nuevo transporte" 
				onclick="document.all.cancelar.disabled=false;document.all.editar.value='editar';document.all.select_transporte.disabled=true;limpiar();editar_campos(1); if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=1;">
           		</td>
          	</tr>
          </table>
         </td>
        </tr>
        
     <table width="95%" class='bordes' cellspacing="0" cellpadding="0" align='center'>
     
      <tr><td>&nbsp;</td></tr>
      <tr align="center">
     	<td colspan="3"><strong><font color="Blue">REPARADORES</font></strong>&nbsp;</td>
      </tr>
      <tr><td>&nbsp;</td></tr>
            
      <tr>
     	 <td width="50%" align="right"><strong>Reparador:&nbsp;</strong></td>
         <td align="right"><input name="nombre" type="text" id="nombre" readonly size="40"  
			   value="<?if($do!=2)echo $_POST['nombre']?>">&nbsp;</td>
      </tr>
           
      <tr><td>&nbsp;</td></tr>
      <tr>
     	 <td width="50%" align="right"><strong>Cuit:&nbsp;</strong></td>
         <td align="right"><input name="cuit" type="text" id="cuit" readonly size="40"  
			   value="<?if($do!=2)echo $_POST['cuit']?>">&nbsp;</td>
      </tr>
      
      <tr><td>&nbsp;</td></tr>
      <tr>
      	  <td width="50%" align="right"><strong>Dirección:&nbsp;</strong></td>
          <td align="right"><input name="direccion" type="text" id="direccion" readonly size="40" 
			   value="<?if($do!=2)echo$_POST['direccion']?>">&nbsp;</td>
      </tr>
                 
      <tr><td>&nbsp;</td></tr>
      <tr>
      	  <td width="50%" align="right"><strong>Localidad:&nbsp;</strong></td>
          <td align="right"><input name="localidad" type="text" id="localidad" readonly size="40" 
			   value="<?if($do!=2)echo$_POST['localidad']?>">&nbsp;</td>
      </tr>
      
      <tr><td>&nbsp;</td></tr>
      <tr>
           <td width="50%" align="right"><strong>Telefono:&nbsp;</strong></td>
           <td align="right"><input name="telefono" type="text" id="telefono" readonly size="40"
			   value="<?if($do!=2)echo$_POST['telefono']?>">&nbsp;</td>
      </tr> 

      <tr><td>&nbsp;</td></tr>
      <tr>
      	  <td width="50%" align="right"><strong>Contacto:&nbsp;</strong></td>
          <td align="right"><input name="contacto" type="text" id="contacto" readonly size="40" 
			   value="<?if($do!=2)echo$_POST['contacto']?>">&nbsp;</td>
      </tr>     
      
      <tr><td>&nbsp;</td></tr>
      <tr> 
           <td width="50%" align="right"><strong>Observaciones:&nbsp;</strong></td>
           <td align="right"><textarea name="observaciones" cols="39"  
			   wrap="VIRTUAL" id="observaciones" readonly><?if($do!=2)echo$_POST['observaciones']?></textarea>&nbsp;
           </td>
      </tr>
      
      <tr><td>&nbsp;</td></tr>
      
  </table>
  
 </td> <!--  En esta celda van todas las entidades -->

 
      <td align="center" nowrap valign="top">
        <TABLE width="100%">
          <input type=hidden name='id' value=""> 
             <tr>
               <td >
                     <select name="select_transporte" size="24" style="width:98%" onchange="set_datos()" 
			   			onKeypress="set_datos();buscar_op(this);set_datos()" onclick="editar_campos(0);borrar_buffer()" onblur="borrar_buffer()" >
                        <? $datos_reparador->Move(0);
                         while (!$datos_reparador->EOF)
	                      {
	                      	
	                      	$id_reparador=$datos_reparador->fields["id_reparador"];
	                     ?> 	
	                                         
                        <option value="<?=$id_reparador?>" <?if($_POST['select_transporte']==$id_reparador) echo "selected"?>>
                        

                        <?=$datos_reparador->fields['nombre_reparador']?>
                       </option>
                          <? 	$datos_reparador->MoveNext();
                          } ?>
                     </select>
                       
               </td>
             </tr>
          
        </table>
      </td>
    </tr>
  </TABLE>
<br>

<TABLE width="100%" align="center" cellspacing="0">
<!-- <tr><td> <input type="submit" name="boton" value="Guardar" onClick="set_opener_campos();return control_datos()">
-->
<tr>
<td width="35%" align="center">
<input type="submit" name="boton" value="Editar" title="Editar datos del transporte" disabled style="width:80px" onClick="if (this.value=='Editar') {document.all.select_transporte.disabled=true;document.all.editar.value='editar';document.all.cancelar.disabled=false;editar_campos(1);return false;} else {document.all.select_transporte.disabled=false;return control_datos();}">
<td width="35%" align="center"">
         <input type=button name='cancelar' value='Cancelar' disabled  onclick="document.all.select_transporte.disabled=false;limpiar();editar_campos(0);document.all.editar.value='';document.all.boton.value='Editar';document.all.boton.disabled=true;document.all.cancelar.disabled=true;">
    </td> 
<td width="35%" align="center"">
         <input type=button name='cerrar_ventana' value='Cerrar Ventana'onclick="window.close();">
    </td>
</tr>
</TABLE>
</form>
</body>
</html>
<? //echo fin_pagina()?>