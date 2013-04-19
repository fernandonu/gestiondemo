<?php
/*
$Author: enrique

modificado por
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2006/01/04 10:44:09 $

A PARTIR DEL 04/06/04 ADMINISTRA LA TABLA DE ENTIDADES
EN LUGAR DE LA DE CLIENTES, COMO ERA ANTES

*/

include_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");


$code = $_POST["code"] or $code = $parametros["code"];


$do=0;
extract($_POST,EXTR_SKIP);

$onclick['elegir']=$parametros['onclickelegir'] or $onclick['elegir']=$_GET['onclickelegir'] or $onclick['elegir']="pass_data('{$parametros['pagina']}')";



if ($boton=="Guardar")



{	 $db->StartTrans();

     $campos="(nombre_transporte,direccion_transporte,telefono_transporte,comentarios_transporte)";
     $query_update="UPDATE transporte set nombre_transporte='$nombre',direccion_transporte='$direccion',telefono_transporte='$telefono',comentarios_transporte='$comentarios' where id_transporte=$id_t ";
     sql($query_update) or fin_pagina();

     if($nombre_con)
	 {
     $campos1="(id_transporte,nombre,telefono,direccion,correo)";
     $query_insert="INSERT INTO transporte_contactos $campos1 VALUES ".
	 "('$id_t','$nombre_con','$telefono_con','$direccion_con','$correo_con')";
	 sql($query_insert) or fin_pagina();
	 }
      $query2 = "select id_contacto
	  from transporte_contactos
	  where id_transporte = $id_t ";
      $res2 = sql($query2,"Error en consulta de logs.") or fin_pagina();
      $h=0;
      $i=0;
    while($cant_cont>$i)
    {
    $id_contacto=$res2->fields['id_contacto'];
    $dir=$_POST["direc_$h"];
    $nom=$_POST["ap_nbre_conc_$h"];
    $cor=$_POST["correo_$h"];
    $tel=$_POST["telefono_conc_$h"];
    $id_co=$id_contacto;
    $h++;
    $i++;
    $query_update="UPDATE transporte_contactos set nombre='$nom',direccion='$dir',telefono='$tel',correo='$cor',id_transporte=$id_t where id_transporte=$id_t and id_contacto=$id_co ";
    sql($query_update) or fin_pagina();

    if(!$res2->EOF) {
    $res2->MoveNext();
    }
    }

   echo "<center><b>El Transporte se actualizó con éxito</b></center>";
   $db->CompleteTrans();
}//de if ($boton=="Guardar")


	/*      $h=0;
   while(document.all.contac_trans.rows.length >$h)
    {
    document.all.contac_trans.deleteRow(h);
    }   */
//datos por parametros
$id_transporte=$parametros['id_transporte'];




echo $html_header;

?>
<style type="text/css">
<!--
.tablaEnc {
	background-color: #006699;
	color: #c0c6c9;
	font-weight: bold;
}
-->
</style>
</head>
<?
//trae los entidades junto con su informacion y los deja en la variable
//con nombre "entidad" concatenado con el id de la entidad

//$fecha_log =  $result->fields["fecha_log"];
//$usuario =  $result->fields["usuario"];
//$tipo_log =  $result->fields["tipo_log"];
echo $html_header;

//echo "cantidad de datos".$datos_transporte->recordcount();
//die();
?>
<SCRIPT LANGUAGE="JavaScript">

//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{
	if (document.all.nombre.value=='' || document.all.nombre.value==' ')
       {
	   alert ('Debes completar el nombre de la entidad');
	   return false;
       }
	if(document.all.nombre.value.indexOf('"')!=-1)
       {
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
       return false;
        }

    if(document.all.direccion.value.indexOf('"')!=-1)
       {
         alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
         return false;
       }


    if(document.all.telefono.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono');
        return false;
    }


    if(document.all.comentarios.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Observaciones');
        return false;
    }

   var total=parseInt(document.all.cant_cont.value);
  document.all.cant_cont.value=parseInt(total);
  var total1=parseInt(document.all.id_t.value);
  document.all.id_t.value=parseInt(total1);

} //fin de la funcion control_datos()



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
	document.forms[0].direccion.readOnly=boolvalue;
	document.forms[0].comentarios.readOnly=boolvalue;
	document.forms[0].telefono.readOnly=boolvalue;
	document.forms[0].nombre.readOnly=boolvalue;
	//document.forms[0].boton.disabled=false;



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

function edi()
{
 var h=0,i=1;
 //
 var total=parseInt(document.all.cant_cont.value);
  document.all.cant_cont.value=parseInt(total);
  var total1=parseInt(document.all.id_t.value);
  document.all.id_t.value=parseInt(total1);
  alert (total);
    while(total >i)
    {
    var dir =eval("document.all.direc_"+h);
   // alert(i);
    dir.readOnly=0;
    var nom =eval("document.all.ap_nbre_conc_"+h);
    nom.readOnly=0;
    var cor =eval("document.all.correo_"+h);
    cor.readOnly=0;
    var tel=eval("document.all.telefono_conc_"+h);
    tel.readOnly=0;
    h++;
    i++;
    }
 document.all.nombre_con.readOnly=0;
 document.all.direccion_con.readOnly=0;
 document.all.telefono_con.readOnly=0;
 document.all.correo_con.readOnly=0;
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
function borrar_tabla()
{
 var h=0;
    while(document.all.contac_trans.rows.length >h)
    {
    document.all.contac_trans.deleteRow(h);
    }
    document.all.cant_cont.value=0;
}

</script>


<?=$informar;?>
<form name="form" method="post" action="transporte_detalle.php">

<?
$query="select id_transporte,comentarios_transporte,telefono_transporte,nombre_transporte,direccion_transporte
		from transporte
		where id_transporte = '$code' ";

$result = sql($query,"Error consultando datos de la orden") or die();

if ($result->RecordCount()==0)
	die("No hay nada que mostrar..");
$id_transporte = $result->fields["id_transporte"];
$nombre = $result->fields["nombre_transporte"];
$direccion = $result->fields["direccion_transporte"];
$telefono = $result->fields["telefono_transporte"];
$comentarios= $result->fields["comentarios_transporte"];
?>

<input type="hidden" name="editar" value="<?if($do!=0 && $do!=2 && $do!=3)echo "editar"?>">
<input type=hidden name='id_t' value='<?=$id_transporte;?>'>
<input type=hidden name='code' value='<?=$id_transporte;?>'>
<input type=hidden name='parametros[code]' value='<?=$id_transporte;?>'>
  <TABLE width="70%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td width="80%" align="center"><strong>INFORMACION DEL TRANSPORTE</strong>

      </td>

    </tr>
    <tr>
      <td>
      <!-- En esta tabla se muestran los datos personales de las entidades -->
      <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=#E0E0E0>
        <tr>
         <td colspan="2">
          <table align="center">
          <tr>
          <td width="95%" >
           <font color=red>
           <b>No ingresar datos con comillas dobles ("")</b>
           </font>
           </td>
          </tr>
          </table>
         </td>
        </tr>
      <tr>
      <td>
     <table width="95%" class='bordes' cellspacing="0" cellpadding="0" align='center'>
      <tr>
     <td align="center"><strong>TRANSPORTE </strong> &nbsp;</td>
     </tr>

       <tr>
       <td colspan="2">
       <hr>
       </td>
       </tr>
       <tr>
       <td>
        <table class='tabla_cont'>
           <tr>
           <td nowrap><strong>Nombre Transporte:</strong> </td>
           <td nowrap> <input name="nombre" type="text"   size="35"  value="<?echo $nombre;?>"></td>
           </tr>
           <tr>
           <td  nowrap><strong>Dirección</strong></td>
           <td nowrap> <input name="direccion" type="text"   size="30" value="<?echo $direccion;?>"></td>
           </tr>
           <tr>
           <td  nowrap><strong>Telefono</strong></td>
           <td nowrap> <input name="telefono" type="text"   size="30" value="<?echo $telefono;?>"></td>
           </tr>
           <tr>
           <td colspan="2" nowrap width="100%"><br><strong>Comentarios</strong></td>
           </tr>
           <tr>
           <td colspan="2" nowrap width="100%">
           <textarea name="comentarios" cols="52"><?echo $comentarios;?> </textarea>
           </td>
           </tr>
           <tr>
           <td>

           </td>
           </tr>
        </table>
    </td>
    </tr>
     <tr>
     <td colspan="2">
     <hr>
     </td>
     </tr>

 <?

/*********************** CONTACTOS ***********************/
$i=0;
if ($code!="") {
$query1 = "select id_transporte,id_contacto,nombre,telefono,correo,direccion
	      from transporte_contactos
		  where id_transporte = $code ";
$res = sql($query1,"Error en consulta de logs.") or fin_pagina();
$id_contacto=$res->fields['id_contacto'];
$ap_nbre=$res->fields['nombre'];
$correo=$res->fields['correo'];
$direc=$res->fields['direccion'];
$telefono_c=$res->fields['telefono'];
if ($res->recordcount() > 0 ) {?>
<tr><td align="center"><strong>
      CONTACTO &nbsp;
 </strong></td></tr>
 <tr>
 <td colspan="2">
 <hr>
 </td>
 </tr>
 <tr>
    <td>

        <table class='tabla_cont'>
               <tr>
                   <td width="30%"><strong>Apellido y Nombre:</strong> </td>
                   <td width="70%"><input type="text"  name="ap_nbre_conc_<?=$i?>" value="<?=$ap_nbre?>" size="50"></td>
               </tr>
               <tr>
                   <td><strong>Correo:</strong> </td>
                   <td><input type="text"  name="correo_<?=$i?>" value="<?=$correo?>"  size="50"></td>
               </tr>
               <tr>
                   <td><strong>Domicilio: </strong></td>
                   <td><input type="text"  name="direc_<?=$i?>" value="<?=$direc?>"  size="50"></td>
               </tr>
               <tr>
                   <td><strong>Teléfono: </strong></td>
                   <td>
                     <table class='tabla_cont_ad'>
                       <tr>
                         <td><input type="text"  name="telefono_conc_<?=$i?>" value="<?=$telefono_c?>" ></td>
                         </tr>
                     </table>
                   </td>
                   </tr>
                 </table>

 <input type=hidden name='id_c_<?=$i;?>' value='<?=$id_contacto;?>'>
      </td>
      </tr>

         <tr>
         <td  colSpan="2">
       <hr>
         </td>
         </tr>
<?
$res->MoveNext();
$i++;
if ($res->recordcount() > 1)	{?>
<tr>
<td>
<table width="100%" class="titulo">
          <tr>
           <td align="center" width="1%">
            <img src='../../imagenes/drop2.gif' border=0 style='cursor: hand;'
	         onClick='if (this.src.indexOf("drop2.gif")!=-1)
                      {
	                   this.src="../../imagenes/dropdown2.gif";
		               div_cont.style.overflow="visible";
	                  }
	                  else
	                  {
		               this.src="../../imagenes/drop2.gif";
		               div_cont.style.overflow="hidden";
	                  }'
	        >
           </td>
           <td align="center" ><strong>OTROS CONTACTOS  &nbsp;</strong></td>
           </tr>
    </table>
    </td>
    </tr>
     <tr>
     <td colspan="2">
     <hr>
     </td>
     </tr>
    <tr>
    <td>
    <div id='div_cont' style='border-width: 0;overflow: hidden;height: 1'>
       <table width="100%" class='tabla_cont'>


 <?

while (!$res->EOF) {
$ap_nbre=$res->fields['nombre'];
$correo=$res->fields['correo'];
$direc=$res->fields['direccion'];
$telefono_c=$res->fields['telefono'];

?>


               <tr>
               <td>
              <table class='tabla_cont'>
               <tr>
                   <td width="30%"><strong>Apellido y Nombre:</strong> </td>
                   <td width="70%"><input type="text"  name="ap_nbre_conc_<?=$i?>" value="<?=$ap_nbre?>" size="50"></td>
               </tr>
               <tr>
                   <td><strong>Correo:</strong> </td>
                   <td><input type="text"  name="correo_<?=$i?>" value="<?=$correo?>"  size="50"></td>
               </tr>
               <tr>
                   <td><strong>Domicilio: </strong></td>
                   <td><input type="text"  name="direc_<?=$i?>" value="<?=$direc?>"  size="50"></td>
               </tr>
               <tr>
                   <td><strong>Teléfono: </strong></td>
                   <td>
                     <table class='tabla_cont_ad'>
                       <tr>
                         <td><input type="text"  name="telefono_conc_<?=$i?>" value="<?=$telefono_c?>" ></td>
                         </tr>
                     </table>
                   </td>
                   </tr>
                 </table>

                   </td>
                   <input type=hidden name='id_c_<?=$i;?>' value='<?=$id_contacto;?>'>
         </tr>
         <tr>
         <td colspan="2">
         <hr>
         </td>
         </tr>
<?

$res->MoveNext();
$i++;
 } // fin while
 ?>

 </table>
 </div>
 </td></tr>
<?
  } //recordcount > 1
}
}
?>
<input type=hidden name='cant_cont' value='<?=$i;?>'>
  <tr>
  <td  colSpan="2">
  <hr>
  </td>
  </tr>
  <tr>
   <td>
    <table width="100%" >
    <tr>
    <td class="titulo" width="1%">
    <img src='../../imagenes/drop2.gif' border=0 style='cursor: hand;'
	onClick='if (this.src.indexOf("drop2.gif")!=-1)
    {
	this.src="../../imagenes/dropdown2.gif";
	div_com.style.overflow="visible";
	}
	else
	{
	this.src="../../imagenes/drop2.gif";
	div_com.style.overflow="hidden";
	}'>
    </td>
    <td align="center"><strong> NUEVO CONTACTO </strong> &nbsp;</td>
    </tr>
    </table>
   </td>
  </tr>
    <tr>
    <td colspan="2">
    <hr>
    </td>
    </tr>
  <tr>
  <td>
  <div id='div_com' style='border-width: 0;overflow: hidden;height: 1'>
  <table class='tabla_cont'>
  <tr>
  <td width="30%"><strong>Apellido y Nombre:</strong> </td>
  <td width="70%"><input type="text"  name="nombre_con" value=""  size="50"></td>
  </tr>
  <tr>
  <td><strong>Teléfono:</strong> </td>
  <td>
  <table class='tabla_cont_ad'>
  <tr>
   <td><input type="text"  name="telefono_con"  value=""></td>
    </tr>
  </table>
  </td>
  </tr>
  <tr>
  <td><strong>Direccion:</strong></td>
  <td>
  <table class='tabla_cont_ad'>
  <tr>
  <td><input type="text" name="direccion_con"  value=""></td>
  <td><strong>Correo</strong></td>
  <td><input type="text" name="correo_con" value="" ></td>
  </tr>
  </table>
  </td>
  </tr>
  </table>
  </div>
  </td>
 </tr>
 </table>
      </td> <!--  En esta celda van todas las entidades -->
    </tr>
  </TABLE>
<br>

</center>

<TABLE width="100%" align="center" cellspacing="0">
<!-- <tr><td> <input type="submit" name="boton" value="Guardar" onClick="set_opener_campos();return control_datos()">
-->
<tr>
<td width="50%" align="center">
<input type="submit" name="boton" value="Guardar" title="Guardar datos del transporte"  style="width:80px" onClick="control_datos()">
<td width="50%" align="center">
<input type="button" name="volver" value="Volver" onclick="document.location='transporte_listado.php'">
</td>
</tr>
</TABLE>

<script>

</script>
</form>
</body>
<? fin_pagina();?>
</html>
<? //echo fin_pagina()?>

