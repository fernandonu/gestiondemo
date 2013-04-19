<?php
/*
$Author: enrique
Fecha:8/8/2005
modificado por
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2006/01/04 10:43:01 $

*/

include_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");




$do=0;
extract($_POST,EXTR_SKIP);

$onclick['elegir']=$parametros['onclickelegir'] or $onclick['elegir']=$_GET['onclickelegir'] or $onclick['elegir']="pass_data('{$parametros['pagina']}')";

if (($boton=="Guardar")&&($editar!="editar"))
	$do=1;
if(($boton=="Guardar")&&($editar=="editar"))
    $do=3;
$tabla="transporte";

	if($do==1)
	{$db->StartTrans();
	 $query3="select nextval('transporte_id_transporte_seq')as id_tran";
	 $id_transpor=sql($query3,"<br> error al traer el id_transporte<br>") or fin_pagina();
	 $id_transporte=$id_transpor->fields['id_tran'];
	 $campos="(id_transporte,nombre_transporte,direccion_transporte,telefono_transporte,comentarios_transporte)";
	 $query_insert="INSERT INTO transporte $campos VALUES ".
	"($id_transporte,'$nombre','$direccion','$telefono','$comentarios')";
	 sql($query_insert) or fin_pagina();

	 if($nombre_con)
	 {
	$campos1="(id_transporte,nombre,telefono,direccion,correo)";
    $query_insert="INSERT INTO transporte_contactos $campos1 VALUES ".
	"($id_transporte,'$nombre_con','$telefono_con','$direccion_con','$correo_con')";
	sql($query_insert)	or fin_pagina();
	 }
	 $db->CompleteTrans();
	 if($_POST['pagina_viene']!="")
	 {
	 ?>
	 <SCRIPT LANGUAGE="JavaScript">

    if (typeof(window.opener.document.all.transporte)!='undefined') {
	var transporte_nuevo=eval(window.opener.document.all.transporte);
    transporte_nuevo.length++;
    transporte_nuevo.options[transporte_nuevo.length-1].text="<?=$nombre?>";
    transporte_nuevo.options[transporte_nuevo.length-1].value=<?=$id_transporte?>;
    }

    //dejamos seleccionado el nuevo sector insertado, en el combo que está al lado del botón apretado
    transporte_nuevo.options.selectedIndex= transporte_nuevo.length-1;

	 window.close();
	 </SCRIPT>
	 <?
	 }

   }//de if($do==1)
   elseif ($do==3)
   {	$db->StartTrans();
     $campos="(nombre_transporte,direccion_transporte,telefono_transporte,comentarios_transporte)";
     $query_update="UPDATE $tabla set nombre_transporte='$nombre',direccion_transporte='$direccion',telefono_transporte='$telefono',comentarios_transporte='$comentarios' where id_transporte=$select_transporte ";
     sql($query_update) or fin_pagina();

     if($nombre_con)
	 {
     $campos1="(id_transporte,nombre,telefono,direccion,correo)";
     $query_insert="INSERT INTO transporte_contactos $campos1 VALUES ".
	 "('$select_transporte','$nombre_con','$telefono_con','$direccion_con','$correo_con')";
	 sql($query_insert) or fin_pagina();
	 }

    $h=0;
    while($cant_cont>$h)
    {
    $dir=$_POST["Direccion_$h"];
    $nom=$_POST["Nombre_$h"];
    $cor=$_POST["Correo_$h"];
    $tel=$_POST["Telefono_$h"];
    $id_co=$_POST["id_contac_$h"];
    $h++;
    $query_update="UPDATE transporte_contactos set nombre='$nom',direccion='$dir',telefono='$tel' ,correo='$cor',id_transporte=$select_transporte where id_transporte=$select_transporte and id_contacto=$id_co";
    sql($query_update) or fin_pagina();

    }
  $db->CompleteTrans();
}
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
$filtro="";
$condicion="";

         $sql="select id_transporte,nombre_transporte,direccion_transporte,telefono_transporte,comentarios_transporte
               from transporte
               where nombre_transporte ilike '$filtro%' order by nombre_transporte";
        $datos_transporte=sql($sql) or fin_pagina();

        $sql1="select id_transporte,id_contacto,nombre,telefono,correo,direccion
	      from transporte_contactos
		  where nombre ilike '$filtro%' order by id_transporte";

         $res=sql($sql1) or fin_pagina();

//echo "cantidad de datos".$datos_transporte->recordcount();
//die();
?>
<SCRIPT LANGUAGE="JavaScript">
<?php
//A PARTIR DE AHORA LEASE ENTIDADES DONDE SE LEE CLIENTES PORQUE
//TOMA TODA LA INFORMACION DESDE LA TABLA ENTIDADES.

while (!$datos_transporte->EOF)
{

   $id_transporte=$datos_transporte->fields["id_transporte"];
?>
	var transporte_<?=$id_transporte?>=new Array();
	<?php
	if($datos_transporte->fields["nombre_transporte"])
	{
	$datos_t=$datos_transporte->fields["nombre_transporte"];
	$datos_t=ereg_replace("\r\n","<br>",$datos_t);
	$datos_t=ereg_replace("\n","<br>",$datos_t);
	$datos_t=ereg_replace("'","",$datos_t);
	}
	if($datos_transporte->fields["direccion_transporte"])
	{
	$datos_d=$datos_transporte->fields["direccion_transporte"];
	$datos_d=ereg_replace("\r\n","<br>",$datos_d);
	$datos_d=ereg_replace("\n","<br>",$datos_d);
	$datos_d=ereg_replace("'","",$datos_d);
	}

	if($datos_transporte->fields["telefono_transporte"])
	{
	$datos_tel=$datos_transporte->fields["telefono_transporte"];
	$datos_tel=ereg_replace("\r\n","<br>",$datos_tel);
	$datos_tel=ereg_replace("\n","<br>",$datos_tel);
	$datos_tel=ereg_replace("'","",$datos_tel);
	}

	if($datos_transporte->fields["comentarios_transporte"])
	{
	$datos_c=$datos_transporte->fields["comentarios_transporte"];
	$datos_c=ereg_replace("\r\n","<br>",$datos_c);
	$datos_c=ereg_replace("\n","<br>",$datos_c);
	$datos_c=ereg_replace("'","",$datos_c);
	}

	?>
	transporte_<?=$id_transporte?>["contacto"]=new Array();
	transporte_<?=$id_transporte?>["nombre_transporte"]="<?=$datos_t?>";
	transporte_<?=$id_transporte?>["direccion_transporte"]="<?=$datos_d?>";
	transporte_<?=$id_transporte?>["telefono_transporte"]="<?=$datos_tel?>";
	transporte_<?=$id_transporte?>["comentarios_transporte"]="<?=$datos_c?>";
<?
$datos_transporte->MoveNext();
}
$t=0;
$id_tran=$res->fields['id_transporte'];
while(!$res->EOF)
{
   $id_tran=$res->fields['id_transporte'];
   if($id_tran==$id_tran1)
   $t++;
   else $t=0;
 if($res->fields["nombre"])
 {
	$datos_n=$res->fields["nombre"];
	$datos_n=ereg_replace("\r\n","<br>",$datos_n);
	$datos_n=ereg_replace("\n","<br>",$datos_n);
	$datos_n=ereg_replace("'","",$datos_n);
 }
 if($res->fields["direccion"])
 {
	$datos_dir=$res->fields["direccion"];
	$datos_dir=ereg_replace("\r\n","<br>",$datos_dir);
	$datos_dir=ereg_replace("\n","<br>",$datos_dir);
	$datos_dir=ereg_replace("'","",$datos_dir);
 }
 if($res->fields["correo"])
 {
	$datos_co=$res->fields["correo"];
	$datos_co=ereg_replace("\r\n","<br>",$datos_co);
	$datos_co=ereg_replace("\n","<br>",$datos_co);
	$datos_co=ereg_replace("'","",$datos_co);

 }
 if($res->fields["comentarios"])
 {
	$datos_com=$res->fields["comentarios"];
	$datos_com=ereg_replace("\r\n","<br>",$datos_com);
	$datos_com=ereg_replace("\n","<br>",$datos_com);
	$datos_com=ereg_replace("'","",$datos_com);
 }
 if($res->fields["telefono"])
 {
	$datos_tele=$res->fields["telefono"];
	$datos_tele=ereg_replace("\r\n","<br>",$datos_tele);
	$datos_tele=ereg_replace("\n","<br>",$datos_tele);
	$datos_tele=ereg_replace("'","",$datos_tele);
 }

?>
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]=new Array();
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]['nombre']="<?=$datos_n?>";
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]['direccion']="<?=$datos_dir?>";
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]['correo']="<?=$datos_co?>";
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]['comentarios']="<?=$datos_com?>";
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]['telefono']="<?=$datos_tele?>";
	transporte_<?=$id_tran?>["contacto"][<?=$t?>]['id_contacto']="<?php if($res->fields["id_contacto"]){echo $res->fields["id_contacto"];}else echo "null";?>";

<?
$res->MoveNext();
$id_tran1=$id_tran;
}
?>

function set_datos()
{
    switch(document.all.select_transporte.options[document.all.select_transporte.selectedIndex].value)
    {<?PHP
     $datos_transporte->Move(0);
     while(!$datos_transporte->EOF)
     {
      $id_transporte=$datos_transporte->fields["id_transporte"];
     ?>
      case '<?=$id_transporte?>': info=transporte_<?=$id_transporte?>;break;
     <?
      $datos_transporte->MoveNext();
     }
     ?>
    }
    if(info["nombre_transporte"]!="null")
            document.all.nombre.value=info["nombre_transporte"];
            else
            document.all.nombre.value="";
    if(info["direccion_transporte"]!="null")
            document.all.direccion.value=info["direccion_transporte"];


    if(info["telefono_transporte"]!="null")
            document.all.telefono.value=info["telefono_transporte"];
            else
            document.all.telefono.value="";
    if(info["comentarios_transporte"]!="null")
            document.all.comentarios.value=info["comentarios_transporte"];
            else
            document.all.comentarios.value=-1;
    var p=0;
    var contactos1=info["contacto"].length;
    contactos1--;
    var h=0;
    var total1=document.all.contac_trans.rows.length;
    //total1++;
    while(total1>h) {
    document.all.contac_trans.deleteRow(0);
    h++;

   // i=i+4;
    }

    while(contactos1>=0)
    {
    cargar(p,info);
    p++;
    contactos1--;
    }

    //document.all.editar.value="editar";
} //fin de la funcion set_datos()



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

  if(document.all.nombre_con.value != '')
       {
      	if (document.all.nombre_con.value=='' || document.all.nombre_con.value==' ')
       {
	   alert ('Debes completar el nombre de la entidad');
	   return false;
       }
	if(document.all.nombre_con.value.indexOf('"')!=-1)
       {
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre');
       return false;
        }

    if(document.all.direccion_con.value.indexOf('"')!=-1)
       {
         alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
         return false;
       }


    if(document.all.telefono_con.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono');
        return false;
    }


    if(document.all.correo_con.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo correo');
        return false;
    }


  }

} //fin de la funcion control_datos()

//funcion para limpiar el formulario
function limpiar()
{document.all.nombre.value='';
 document.all.direccion.value='';
 document.all.telefono.value='';
 document.all.comentarios.value='';
 document.all.editar.value='';


}
function limpiar1()
{
document.all.nombre_con.value='';
document.all.direccion_con.value='';
document.all.telefono_con.value='';
document.all.correo_con.value='';
document.all.cant_cont.value=0;
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
	document.forms[0].direccion.readOnly=boolvalue;
	document.forms[0].comentarios.readOnly=boolvalue;
	document.forms[0].telefono.readOnly=boolvalue;
	document.forms[0].nombre_con.readOnly=boolvalue;
	document.forms[0].direccion_con.readOnly=boolvalue;
	document.forms[0].correo_con.readOnly=boolvalue;
	document.forms[0].telefono_con.readOnly=boolvalue;
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

function edi()
{
 var h=0,i=0;
 //alert (document.all.contac_trans.rows.length);
    while(document.all.contac_trans.rows.length >i)
    {
    var dir =eval("document.all.Direccion_"+h);
   // alert(i);
    dir.readOnly=0;
    var nom =eval("document.all.Nombre_"+h);
    nom.readOnly=0;
    var cor =eval("document.all.Correo_"+h);
    cor.readOnly=0;
    var tel=eval("document.all.Telefono_"+h);
    tel.readOnly=0;
    h++;
    i=i+4;
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
    var total1=document.all.contac_trans.rows.length;

    //total1++;
    while(total1>h)
    {

    document.all.contac_trans.deleteRow(0);
     h++;

    }
    items=0;
    document.all.cant_cont.value=0;
}

</script>


<?=$informar;?>
<form name="form" method="post" action="transporte_editor_avanzado.php">
<?
?>
<input type=hidden name='pagina_viene' value='<?=$parametros['pagina_viene']?>'>
<input type=hidden name='cant_cont' value='0'>
<input type=hidden name="editar" value="">
  <TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td width="40%" align="center"><strong>INFORMACION DE LOS TRANSPORTES</strong>

      </td>
      <td width="60%" height="20" align="center" >
      <strong>TRANSPORTES CARGADAS</strong>
       </td>
    </tr>
    <tr>
      <td valign=top align="center">
      <!-- En esta tabla se muestran los datos personales de las entidades -->
      <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor=#E0E0E0>
        <tr>
         <td colspan="2">
          <table>
          <tr>
          <td width="95%">
           <font color=red>
           <b>No ingresar datos con comillas dobles ("")</b>
           </font>
           </td>
           <td nowrap align="left">
           <input type="button" name="nuevo" value="Nuevo" title="Para agregar un nuevo transporte" onclick="document.all.cancelar.disabled=false;document.all.editar.value='editar';document.all.select_transporte.disabled=true;borrar_tabla(); edi();limpiar();editar_campos(1); if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=1;">
           </td>
          </tr>
          </table>
         </td>
        </tr>

     <table width="95%" class='bordes' cellspacing="0" cellpadding="0" align='center'>
      <tr>
     <td nowrap><strong>TRANSPORTE </strong> &nbsp;</td>
     </tr>
     <tr>
     <td>
        <table class='tabla_cont'>
           <tr>
           <td nowrap><strong>Nombre Transporte:</strong> </td>
           <td nowrap> <input name="nombre" type="text" id="nombre" readonly size="35"  value="<?if($do!=2)echo $_POST['nombre']?>"></td>
           </tr>
           <tr>
           <td  nowrap><strong>Dirección</strong></td>
           <td nowrap> <input name="direccion" type="text" id="direccion" readonly size="30" value="<?if($do!=2)echo$_POST['direccion']
           ?>"></td>
           </tr>
           <tr>
           <td  nowrap><strong>Telefono</strong></td>
           <td nowrap> <input name="telefono" type="text" id="telefono" readonly size="30"></td>
           </tr>
           <tr>
           <td colspan="2" nowrap width="100%"><br><strong>Comentarios</strong></td>
           </tr>
           <tr>
           <td colspan="2" nowrap width="100%">
           <textarea name="comentarios" cols="52"  wrap="VIRTUAL" id="comentarios" readonly></textarea>
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
 ?>
 <tr>
   <td>
    <table width="100%" >
    <tr>
     <td class="titulo" width="1%">
    <img src='../../imagenes/drop2.gif' border=0 style='cursor: hand;'
	onClick='if (this.src.indexOf("drop2.gif")!=-1)
    {
	this.src="../../imagenes/dropdown2.gif";
	div_com1.style.overflow="visible";
	}
	else
	{
	this.src="../../imagenes/drop2.gif";
	div_com1.style.overflow="hidden";
	}'>
    </td>
    <td>
     <strong> CONTACTOS </strong> </td>
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
  <div id='div_com1' style='border-width: 0;overflow: hidden;height: 1'>
  <table class='tabla_cont' id='contac_trans' width="100%">

 </table>
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
    <td><strong> NUEVO CONTACTO </strong> &nbsp;</td>
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
  <td width="70%"><input type="text"  name="nombre_con" value="" readonly size="50"></td>
  </tr>
  <tr>
  <td><strong>Teléfono:</strong> </td>
  <td>
  <table class='tabla_cont_ad'>
  <tr>
   <td><input type="text"  name="telefono_con" readonly value=""></td>
    </tr>
  </table>
  </td>
  </tr>
  <tr>
  <td><strong>Direccion:</strong></td>
  <td>
  <table class='tabla_cont_ad'>
  <tr>
  <td><input type="text" name="direccion_con" readonly value=""></td>
  <td><strong>Correo</strong></td>
  <td><input type="text" name="correo_con" value="" readonly></td>
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
      <td align="center" nowrap valign="top">
        <TABLE width="100%">
          <input type=hidden name='id' value="">
             <tr>
               <td >
                     <select name="select_transporte" size="30" style="width:98%" onchange="limpiar1(); set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onclick="editar_campos(0);borrar_buffer()" onblur="borrar_buffer()" >
                        <? $datos_transporte->Move(0);
                         while (!$datos_transporte->EOF)
	                      {

	                      	$id_transporte=$datos_transporte->fields["id_transporte"];
	                     ?>

                        <option value="<?=$id_transporte?>" <?if($_POST['select_transporte']==$id_transporte) echo "selected"?>>


                        <?=$datos_transporte->fields['nombre_transporte']?>
                       </option>
                          <? 	$datos_transporte->MoveNext();
                          } ?>
                     </select>

               </td>
             </tr>

        </table>
      </td>
    </tr>
  </TABLE>
<br>

</center>

<TABLE width="100%" align="center" cellspacing="0">
<!-- <tr><td> <input type="submit" name="boton" value="Guardar" onClick="set_opener_campos();return control_datos()">
-->
<tr>
<td width="35%" align="center">
<input type="submit" name="boton" value="Editar" title="Editar datos del transporte" disabled style="width:80px" onClick="if (this.value=='Editar') {document.all.select_transporte.disabled=true;document.all.editar.value='editar';document.all.cancelar.disabled=false;edi();editar_campos(1);return false;} else {document.all.select_transporte.disabled=false;return control_datos();}">
<td width="35%" align="center"">
         <input type=button name='cancelar' value='Cancelar' disabled  onclick="document.all.select_transporte.disabled=false;borrar_tabla();limpiar1();limpiar();edi();editar_campos(0);document.all.editar.value='';document.all.boton.value='Editar';document.all.boton.disabled=true;document.all.cancelar.disabled=true;">
    </td>
<td width="35%" align="center"">
         <input type=button name='cerrar_ventana' value='Cerrar Ventana'onclick="window.close();">
    </td>
</tr>
</TABLE>

<script>
limpiar();
//if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=1;
document.all.select_transporte.selectedIndex=-1;
</script>
</form>
</body>
</html>
<? //echo fin_pagina()?>

<script language="JavaScript1.2">
var items=0;
function cargar(p,info,t)
{
  var fila=document.all.contac_trans.insertRow(document.all.contac_trans.rows.length );
  fila.insertCell(0).innerHTML="<strong>Nombre</strong>";
  fila.insertCell(1).innerHTML="<input type='text' name='Nombre_"+items+"' value='"+info["contacto"][p]['nombre']+"' readonly>" ;
  fila=document.all.contac_trans.insertRow(document.all.contac_trans.rows.length );
  fila.insertCell(0).innerHTML="<strong>Telefono</strong>";
  fila.insertCell(1).innerHTML="<input type='text' name='Telefono_"+items+"' value='"+info["contacto"][p]['telefono']+"' readonly>";
  fila.insertCell(2).innerHTML="<input type=hidden name='id_contac_"+items+"' value='"+info["contacto"][p]['id_contacto']+"'>";
  fila=document.all.contac_trans.insertRow(document.all.contac_trans.rows.length );
  fila.insertCell(0).innerHTML="<strong>Direccion<strong>";
  fila.insertCell(1).innerHTML="<input type='text' name='Direccion_"+items+"' value='"+info["contacto"][p]['direccion']+"' readonly>";
  fila.insertCell(2).innerHTML="<strong>Correo<strong>";
  fila.insertCell(3).innerHTML="<input type='text' name='Correo_"+items+"' value='"+info["contacto"][p]['correo']+"' readonly>";
  fila=document.all.contac_trans.insertRow(document.all.contac_trans.rows.length );
  fila.insertCell(0).colSpan=4;
  fila.cells[0].innerHTML="<hr>";
  items++;
  document.all.cant_cont.value=parseInt(items);
  //fila.insertCell(3).innerHTML="<input type='hidden' value='"+p+"'>";*/

}//de function cargar()
</SCRIPT>