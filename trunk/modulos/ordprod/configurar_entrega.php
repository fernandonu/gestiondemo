<?
/*
Creado por: Quique

Modificada por
$Author: enrique $
$Revision: 
$Date: 2006/03/31 13:02:03 $
*/


require_once("../../config.php");
//funcion para mandar el arreglo por post
//print_r($parametros);
$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST["id_entrega_estimada"];
$id=$parametros['id'] or $id=$_POST["id"];
$oc=$parametros['oc'] or $oc=$_POST["oc"];
$cliente=$parametros['cliente'] or $cliente=$_POST["cliente"];
$agregar=1;
$agregado=1;
$cons=1;
$base=1;
$nuevo=1;
$comentar=0;
$desea=0;
echo $html_header;
if($_POST["guardar"])
{
 $sum=$_POST["contador"];
 echo "sum $sum";
// die();		
 $agregado=$_POST["agregado"];		
 $id_entrega_estimada=$_POST["id_entrega_estimada"];
 $contad=1;
 $db->StartTrans();
 $eliminar="delete from licitaciones.configuracion_entrega
 where id_entrega_estimada=$id_entrega_estimada";
 sql($eliminar,"No se pudo eliminar los renglones");
 /*$eliminar="delete from licitaciones.lugar_entrega where id_entrega_estimada=$id_entrega_estimada";
 sql($eliminar,"No se pudo eliminar los renglones");*/
 $sumas=1;
 while($sumas<=$agregado)
 {
 	$direc=$_POST["dir_$sumas"];
    $tele=$_POST["tel_$sumas"];
    $contac=$_POST["contacto_$sumas"];
    $b_h=$_POST["bh_$sumas"];
    $id_lu=$_POST["id_lugar_$sumas"];
    if($direc!="")
    {
    if($id_lu!="")
    {
     $cons="update licitaciones.lugar_entrega set direccion='$direc',telefono='$tele',contacto='$contac',banda_horaria='$b_h' where id_lugar_entrega=$id_lu";
     sql($cons,"No se pudo actualizar el lugar de entrega") or fin_pagina();	
     $a[$sumas]=$id_lu;
    } 
    else
    {
    $q_nextval="select nextval ('licitaciones.lugar_entrega_id_lugar_entrega_seq') as id_lugar_ent";
    $res_q=sql($q_nextval, "Error al traer secuencia de id log_envio") or fin_pagina();
    $id_lugar=$res_q->fields['id_lugar_ent'];
    $campos1="(contacto,telefono,direccion,banda_horaria,id_entrega_estimada,id_lugar_entrega)";
    $query_insert="INSERT INTO licitaciones.lugar_entrega $campos1 VALUES ".
	"('$contac','$tele','$direc','$b_h',$id_entrega_estimada,$id_lugar)";
	sql($query_insert) or fin_pagina();
	$a[$sumas]=$id_lugar;
    }
    }
    $sumas++;
 }
 while($contad<=$sum)
 {
 	if($_POST["cant_$contad"])
 	{	
    $pos=$_POST["entrega_$contad"];
    
    $id_lug=$a[$pos];
    $canti=$_POST["cant_$contad"];
    $id_oc=$_POST["idoc_$contad"];
    $campos1="(cantidad,id_renglones_oc,id_lugar_entrega,id_entrega_estimada)";
    $query_insert="INSERT INTO licitaciones.configuracion_entrega $campos1 VALUES ".
	"($canti,$id_oc,$id_lug,$id_entrega_estimada)";
	sql($query_insert) or fin_pagina();
 	}
	$contad++;
 }
 $cons=0;
 $db->CompleteTrans();
 ?>
    <script>
    window.close();
    </script>
 <?	
}
if($_POST["aceptar"])
{
$agregado1=$_POST["agregado"];
$i=1;
$agregado2=0;
while($agregado1>=$i)
{	
$cont=$_POST["contacto_$i"];	
$tele=$_POST["tel_$i"];	
$direc=$_POST["dir_$i"];	
$ban=$_POST["bh_$i"];
 if(($cont!="")&&($direc!="")&&($tele!="")&&($ban!=""))
 {
	$agregado2++;
 }
 $i++;
}
$agregado1=$agregado2;
$agregar=0;	
$comentar=1;
$desea=1;
}
if($_POST["cerrar"])
{
?>
    <script>
    window.close();
    </script>
 <?	
}

if($_POST["original"])
{
 $id_entrega_estimada=$_POST["id_entrega_estimada"];
 //$contad=1;
 $db->StartTrans();
 $sel="Select id_lugar_entrega from lugar_entrega where id_entrega_estimada=$id_entrega_estimada";
 $id_lugares=sql($sel,"Error no se pudo recuperar los lugares de entregas") or fin_pagina();
 $eliminar="delete from licitaciones.configuracion_entrega
 where id_entrega_estimada=$id_entrega_estimada";
 sql($eliminar,"No se pudo eliminar los renglones");
 While(!$id_lugares->EOF)
 {
 	$id_lugar1=$id_lugares->fields['id_lugar_entrega'];
 	$sel1="select id_envio_renglones from envio_renglones where id_lugar_entrega=$id_lugar1";
 	$res_sel1=sql($sel1,"No se pudo recuperar el id_envio_renglones") or fin_pagina();
 	if(!$res_sel1->EOF)
 	{
 	 $id_env=$res_sel1->fields['id_envio_renglones'];	
 	 $eliminar="delete from licitaciones_datos_adicionales.log_envio_renglones
     where id_envio_renglones=$id_env";
     sql($eliminar,"No se pudo eliminar los log renglones");	
     $eliminar="delete from licitaciones_datos_adicionales.renglones_bultos
     where id_envio_renglones=$id_env";
     sql($eliminar,"No se pudo eliminar los log renglones");
     $eliminar="delete from licitaciones_datos_adicionales.datos_envio
     where id_envio_renglones=$id_env";
     sql($eliminar,"No se pudo eliminar los datos envio");
 	}
 	$eliminar="delete from licitaciones_datos_adicionales.envio_renglones
    where id_lugar_entrega=$id_lugar1";
    sql($eliminar,"No se pudo eliminar los renglones");
    $id_lugares->MoveNext();
 }
 
 $db->CompleteTrans();	
 $agregado1=$_POST["agregado"];
 $agregar=1;
 $cons=1;
}

if($_POST["borrar"])
{
  
   $db->StartTrans();

	   $agreg=$_POST["agregado"];
	   for ($i=0; $i<$agreg; $i++) {
	      	if ($_POST["borrar_$i"]){
	           $id_lug=$_POST["id_lugar_$i"];
	           //$id_renglones_bultos=$_POST["id_renglones_bultos_$i"];

	      // eliminar numeros de serie si ya fueron cargados
         $q_del_ns="delete from licitaciones.lugar_entrega
       			    where id_lugar_entrega=$id_lug";
	     $res_q_del_ns=sql($q_del_ns, "Error al eliminar los lugares de entrega") or fin_pagina();

	     
	         } // if ($_POST["borrar_$i"])
	      } // for ($i=0; $i<$agre; $i++)
	      
    $db->Completetrans();
}
	
	


?>
<form action="configurar_entrega.php" method="POST">
<table align="center" border="1" bgcolor='<?=$bgcolor3?>'>
<tr>
 <td>
  <font color="Blue"><b>Configuración de Entregas</b></font>
 </td>
</tr>
</table>

<table align="center" bgcolor='<?=$bgcolor3?>'>
<?
$sql="select id_lugar_entrega,configuracion_entrega.cantidad,titulo,id_renglones_oc,configuracion_entrega.id_entrega_estimada,codigo_renglon from licitaciones.configuracion_entrega
left join renglones_oc using(id_renglones_oc)
join licitaciones.renglon using (id_renglon)
where configuracion_entrega.id_entrega_estimada=$id_entrega_estimada";//join licitaciones.lugar_entrega using(id_lugar_entrega)
$res=sql($sql) or fin_pagina();
if((!$res->EOF)||($cons==1))
{
$base=0;
$cons="select direccion,contacto,telefono,banda_horaria,id_lugar_entrega from licitaciones.lugar_entrega where id_entrega_estimada=$id_entrega_estimada";
$consulta=sql($cons,"No se pudo recuperar las direcciones");
$agregado=$consulta->RecordCount();

if($consulta->RecordCount()>0)
{
$nuevo=0;
$desea=1;
}
}


if($res->EOF)
{
$sql="select id_renglones_oc,id_renglon,subido_lic_oc.id_licitacion,codigo_renglon,titulo,subido_lic_oc.id_subir,comentario_adicional,
      renglones_oc.cantidad,estado, renglones_oc.precio,subido_lic_oc.lugar_entrega
      from licitaciones.subido_lic_oc
      join licitaciones.renglones_oc using (id_subir)
      join licitaciones.renglon using (id_renglon)
      where id_entrega_estimada=$id_entrega_estimada order by codigo_renglon" ;
$res=sql($sql) or fin_pagina();
//$base=1;
}
?>
<tr>
<td>
<table width="100%" align="center" class="bordes">
  <tr align="center">
  	  <td align="center" colspan="2">
  	  	<strong>
  	  	<font color="Red">
  	  	Presionar el Boton despues de Copiar los Datos de Excel
  	  	</font>
  	  	</strong>  	  
  	  </td>
  </tr>
  <tr align="center">
  	<td align="center" colspan="2">
  		<b>Ingrese Numero de Inicio:&nbsp;</b>
  		<input type="text" value="1" name="rango" title="Ingrese el Numero Desde" size="4"<?if($desea==0){?>disabled<?}?>>
  	</td>
  </tr>
  
  <tr align="center">
  	  <td align="center" colspan="2">
  	  	<input type="button" name="cargar_series" value="Cargar Datos del Portapapeles" onclick="cargarSeries();" <?if($desea==0){?>disabled<?}?>>
  	    <br>
  	  </td>
  </tr>
 </table>
<br>
</td>
</tr>
<input type="hidden" name="agregados" value="0">
<tr> 
 <td colspan="2" align="center"><b>Por favor especifique los lugares de entrega</b>&nbsp;&nbsp;
 <input type="button" name="agregar" value="Agregar Lugar de Entrega" onclick="agregar1()"></td>
</tr>
<tr>
<td colspan="2">
<table id="tabla1" name="tabla1" width="80%" align="center">
<tr id="mo">
 <td><b>N°</b></td>
 <td><b>Contacto</b></td>
 <td><b>Tel</b></td>
 <td><b>Direccion</b></td>
 <td><b>Banda Horaria</b></td>
 <td><b>Borrar</b></td>
</tr>
<?
if(($base==1)||($agregar==0))
{
$i=1;
if($agregar==0)
$agregado=$agregado1;
while($agregado>=$i)
{
$cont=$_POST["contacto_$i"];	
$tele=$_POST["tel_$i"];	
$direc=$_POST["dir_$i"];	
$ban=$_POST["bh_$i"];
$id_lugar=$_POST["id_lugar_$i"];
if($base==0)
{
$t=1;
while(!$consulta->EOF)
{
$id=$consulta->fields['id_lugar_entrega'];	
$ar[$t]=$id;
$t++;
$consulta->MoveNext();
}
}
?> 
<tr>
 <td><b><?=$i?></b></td>
 <td><input type="text" size="20" name="contacto_<?=$i?>" value="<?=$cont?>"></td>
 <td><input type="text" size="25" name="tel_<?=$i?>" value="<?=$tele?>"></td>
 <td><input type="text" size="50" name="dir_<?=$i?>" value="<?=$direc?>"></td>
 <td align="center"><input type="text" size="10" name="bh_<?=$i?>" value="<?=$ban?>"></td>
 <td><input type="checkbox"  name="borrar_<?=$i?>" value="<?=$i?>" <?if($id_lugar==""){?>disabled<?}?>></td>
 <td align="center"><input type="hidden" name="id_lugar_<?=$i?>" value="<?=$id_lugar?>"></td>
</tr>
<?
$i++;
}
?>

<?	
}
else 
{
$i=1;	
while(!$consulta->EOF)
{
$id=$consulta->fields['id_lugar_entrega'];	
$ar[$i]=$id;
?> 
<tr align="center">
 <td><b><?=$i?></b></td>
 <td><input type="text" size="20" name="contacto_<?=$i?>" value="<?=$consulta->fields['contacto'];?>"></td>
 <td><input type="text" size="25" name="tel_<?=$i?>" value="<?=$consulta->fields['telefono'];?>"></td>
 <td><input type="text" size="50" name="dir_<?=$i?>" value="<?=$consulta->fields['direccion'];?>"></td>
 <td><input type="text" size="10" name="bh_<?=$i?>" value="<?=$consulta->fields['banda_horaria'];?>"></td>
 <td><input type="checkbox" name="borrar_<?=$i?>" value="<?=$i?>"></td>
 <td><input type="hidden" size="10" name="id_lugar_<?=$i?>" value="<?=$consulta->fields['id_lugar_entrega'];?>"></td>
</tr>
<?
$i++;
$consulta->MoveNext();
}
?>

<?	
}
?>
</table>
<input type="hidden" name="agregado" value="<?=(($_POST['agregado'])?$_POST['agregado']:$i)?>">
<tr>
<td colspan="2" align="center">
<input type="submit" name="aceptar" value="Aceptar" onclick="return control_guardar_lugar();">

<input type="submit" name="borrar" value="Borrar" onclick="return control_borrar_lugar();" <?if($nuevo!=0)
{?>disabled<?}?>>
<input type="hidden" name="pagina" value="0">
</td>
</tr>
<tr>
<td colspan="2" align="center">
<table id="tabla" name="tabla">
<?
if(($agregar==0)||($nuevo==0))
{?>
<tr id="mo">
 <td width="3%"><b>&nbsp;&nbsp;</b></td>
 <td width="15%"><b>Reng</b></td>
 <td width="5%"><b>Cant</b></td>
 <td width="67%"><b>Titulo</b></td>
 <td width="10%"><b>Entrega</b></td>
 <td width="1%"></td>
</tr> 
<?
$i=1;
while(!$res->EOF)
{
$link = encode_link("partir_entrega.php",array("num"=>$res->fields['codigo_renglon'],"cant"=>$res->fields['cantidad'],"div"=>0,"row"=>$i,"id_oc"=>$res->fields['id_renglones_oc'],"titulo"=>$res->fields['titulo'],"agre"=>$agregado));	
$link1="window.open(\"$link\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\")";
?>
<tr>
<td>
 <?
 $c=$res->fields['cantidad'];
 if($c==1){$ver="disabled";}
 else
 $ver="enabled";
 echo "<input type=button name=especial_$i value='Esp' onclick='$link1;'
 $ver>";?></td>
 <td><input type="text" size="20" name="num_<?=$i?>" value="<?=$res->fields['codigo_renglon'];?>" readonly></td>
 <td><input type="text" size="5" name="cant_<?=$i?>" value="<?=$res->fields['cantidad'];?>" readonly></td>
 <td><input type="text" size="75" name="titulo_<?=$i?>" value="<?=$res->fields['titulo'];?>"></td>
 <td align="left"><select name="entrega_<?=$i?>">
 
 <?
 $j=1;
 while($j<=$agregado)
 {
 ?>
 <option value="<?=$j?>" <?if($res->fields['id_lugar_entrega']==$ar[$j]){?>selected<?}?>><?=$j?></option>
 <?$j++;}?>
 </td>
 <td><input type="hidden" name="idoc_<?=$i?>" id="idoc_<?=$i?>" value="<?=$res->fields['id_renglones_oc'];?>"><td>
</tr>

<?
$i++;
$res->MoveNext();
}
?>
</table>
</td>
</tr>
<tr align="center">
<td colspan="2">
<input type="submit" name='guardar' value='Guardar'>
<input type="submit" name='original' value='Traer Originales'>
<input type='submit' name='cerrar' value='Cerrar' onclick="<?if($comentar==1){?>return (confirm('¿Está seguro que quiere Cerrar la pagina los cambios realizados no han sido guardados?'));<?}?>"></td>
</tr>
</table>
<?	
}
?>
<input type="hidden" name="contador" id="contador" value="<?=$i?>">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<input type="hidden" name="col" value="0">
</form>
<script>
function cargar(con)
{
 var h=0,i=1;
 var ren =eval("document.all.copy_"+con);
     ren=ren.value;
 
 if(typeof(eval("document.all.num_"+i))!="undefined")
 {   
 	 var nume =eval("document.all.num_"+i);
     nume =nume.value;
 }
     
 var contador=eval("document.all.contador");
     contador=contador.value;

 while(contador>=i && i!=ren)
   {
    i++;
    while((typeof(eval("document.all.num_"+i))=="undefined")&&(contador>i))
    {
     i++;	 
    }
    if(contador>=i)
    {
     var nume =eval("document.all.num_"+i);
     nume =nume.value;
    }
   }
   if(contador>=i)
    {
     var contac =eval("document.all.contacto_"+con);
     contac=contac.value;
     var contac1 =eval("document.all.contacto_"+i);
     contac1.value=contac;
     var dire =eval("document.all.dir_"+con);
     dire=dire.value;
     var dire1 =eval("document.all.dir_"+i);
     dire1.value=dire;
     var tele =eval("document.all.tel_"+con);
     tele=tele.value;
     var tele1 =eval("document.all.tel_"+i);
     tele1.value=tele;
     var bah =eval("document.all.bh_"+con);
     bah=bah.value;
     var bah1 =eval("document.all.bh_"+i);
     bah1.value=bah;
    }
}

function partir1(con,contar2)
{
 var contar =eval("document.all.contador");
     contar.value =parseInt(con)+parseInt(contar.value)-1;
  var col1 =eval("document.all.col");
      col1.value =contar2; 
  for (nn=1; nn<=parseInt(contar.value); nn++) {	
   var var1 =eval("document.all.especial_"+nn);
   if (typeof (eval(var1))!="undefined"){
   	   var1.disabled=true;
   	   var1.title="Debe guardar y luego podrá partir nuevamente el renglón";
   }	
 }    
      
}

function agregar1()
{
var tabla=document.all.tabla1;
   var ren =tabla.rows.length;
  
   var fila=tabla.insertRow(ren);
   //fila.onmouseOver=function(){"this.style.backgroundColor = '#EEFFE6'; this.style.color = '#004962'"}; 
   //fila.onmouseOut=function(){"this.style.backgroundColor = '#B7C7D0'; this.style.color = '#000000'"};
  
   fila.insertCell(0).innerHTML="<b>"+ren+"</b>";
   fila.insertCell(1).innerHTML="<input type='text' size='20' name=contacto_"+ren+">";
   fila.insertCell(2).innerHTML="<input type='text' size='25' name=tel_"+ren+">";
   fila.insertCell(3).innerHTML="<input type='text' size='50' name=dir_"+ren+">";
   fila.insertCell(4).innerHTML="<input type='text' size='10' name=bh_"+ren+">";
   fila.insertCell(5).innerHTML="<input type='checkbox'  name=borrar_"+ren+" value="+ren+" disabled>";
   fila.insertCell(6).innerHTML="<input type='hidden' name=id_lugar_"+ren+">";
   var agregado =eval("document.all.agregado");
   agregado.value=ren;
   var cargar_serie =eval("document.all.cargar_series");
   cargar_serie.disabled=false;
   var rango1 =eval("document.all.rango");
   rango1.disabled=false;
     
}

function control_borrar_lugar(){
var sent, sent2;
var check, text;
var cant=0;
var agre="document.all.agregado";  
var co="document.all.contador";
co=eval(co);
agre=eval(agre);  
  for (i=1; i<parseInt(document.all.agregado.value); i++) {
    sent="document.all.borrar_"+i;
    check=eval(sent);
    if (check.checked){
     
      for (t=1; i<parseInt(document.all.contador.value); t++) {
       sent2="document.all.entrega_"+t;
       sent2=eval(sent2);
       if(parseInt(sent2.value)==parseInt(check.value))
       {
       	 alert ("El lugar o los lugares de entrega que quiere eliminar están en uso por favor cambie el lugar de entrega,guarde y después podrá eliminar el o los lugares de entrega");
         return false;
       }	
      } 		
     }
    
   }
 
 return true;
}

function control_guardar_lugar(){
var sent, sent2;
var check, text;
var cant=0;
var agre="document.all.agregado";  
agre=eval(agre);
  for (i=1; i<=parseInt(document.all.agregado.value); i++) {
  	 var contac =eval("document.all.contacto_"+i);
     var dire =eval("document.all.dir_"+i);
     var tele =eval("document.all.tel_"+i);
     var bah =eval("document.all.bh_"+i);
     if(contac.value!="")
     {
     cant++;	
      if(dire.value=="")
     {
     	alert("Falta completar el campo Direccion del contacto "+contac.value+" .");
     	return false;
     }
     if(tele.value=="")
     {
     	alert("Falta completar el campo Telefono del contacto "+contac.value+" ."); 
     	return false; 	
     }
     if(bah.value=="")
     {
     	alert("Falta completar el campo Banda Horaria del contacto "+contac.value+" .");
     	return false;
     }		
     }
   }
 if(eval(cant)==0)
 {
 	alert("No hay lugares de entrega cargados");
    return false;
 }
   
 return true;
}

function cargarSeries(){
	var agregado =eval("document.all.agregado");
    agregado=agregado.value;
	var arregloaux = new Array();
	var arreglo = new Array();
	var tamArreglo;
	arregloaux=window.clipboardData.getData("Text");
	arreglo=arregloaux.split("\n");
	tamArreglo=arreglo.length-1;
	var i=eval ("document.all.rango.value");
	var j=0;
	var error=0;
	var errorCont=0;
	var ii=eval(i);
	agregado=eval(agregado)+1-eval(i);
	if(eval(tamArreglo)>eval(agregado))
	{
	 alert ("La Cantidad de Datos del Portapapeles (Filas) es MAYOR a los Cuadros de Textos Disponibles en la Pagina.");
	 return false;
	}
	while (j<tamArreglo){	
	var arregloaux1 = new Array();
	var arreglo1 = new Array();
	var tamArreglo1;
	arreglo1=arreglo[j].split("\t");
	tamArreglo1=arreglo1.length; 
	var jj=0;
	var error1=0;
	var errorCont1=0;
	var res1 = eval("document.all.contacto_"+ii);
	var res2 = eval("document.all.tel_"+ii);
	var res3 = eval("document.all.dir_"+ii);
	var res4 = eval("document.all.bh_"+ii);
	if(eval(tamArreglo1)>4)
	{
	 alert ("La Cantidad de Datos del Portapapeles (Columnas) es MAYOR a los Cuadros de Textos Disponibles en la Pagina.");
	 return false;
	}
	else{
		if (typeof (arreglo1[0])!="undefined"){
		res1.value=arreglo1[0];
		}
		if (typeof (arreglo1[1])!="undefined"){
		res2.value=arreglo1[1];
		}
		if (typeof (arreglo1[2])!="undefined"){
		res3.value=arreglo1[2];
		}
		if (typeof (arreglo1[3])!="undefined"){
		res4.value=arreglo1[3];
		}
		}
		i++;
		ii++;
		j++;
	}
}

</script>