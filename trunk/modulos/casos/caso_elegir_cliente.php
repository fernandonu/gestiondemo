<?
/*
MODIFICADA POR
$Author: cestila $
$Revision: 1.10 $
$Date: 2006/05/03 20:58:46 $
*/

require_once("../../config.php");
extract($_POST,EXTR_SKIP);

//obtengo valor $filtro ->letra seleccionada en el filtro, es 'a' si es la primera vez que carga, es 'vacio' si selecciona 'Todos'
if($letra!="")
 $filtro=$letra;
elseif($_POST['filtro']=="")
 $filtro="a";
elseif($_POST['filtro']=="Todos")
 $filtro="";
else
 $filtro=$_POST['filtro'];

//muestra tabla para filtar proveedores
function tabla_filtros_nombres(){

 $abc=array("a","b","c","d","e","f","g","h","i",
			"j","k","l","m","n","ñ","o","p","q",
			"r","s","t","u","v","w","x","y","z");
$cantidad=count($abc);

echo "<table  align='center' width='100%' height='80%' id='mo'>";
echo "<input type=hidden name='filtro' value=''";
	echo "<tr>";
	for($i=0;$i<$cantidad;$i++){
		$letra=$abc[$i];
	   switch ($i) {
					 case 9:
					 case 18:
					 case 27:echo "</tr><tr>";
						  break;
				   default:
				  } //del switch

echo "<td style='cursor:hand' onclick=\"document.all.filtro.value='$letra'; document.form1.submit();\">$letra</td>";
	  }//del for
   echo "</tr>";
   echo "<tr>";
	echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='Todos'; document.form1.submit();\"> Todos";
	echo "</td>";
   echo "</tr>";
   echo "</table>";
}  //de la funcion


if ($parametros)
 extract($parametros,EXTR_OVERWRITE);
 
$q="select id_entidad,nombre,direccion,telefono,mail from entidad where nombre ilike '$filtro%' and activo_entidad=1 order by nombre";
$clientes=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");

?>
<head>
<title>Clientes</title>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<?=$html_header?>
</head>
<body topmargin="0">
<script>
var dependencias=new Array();
<?
while (!$clientes->EOF)
{
?>
var dependencias_<?php echo $clientes->fields["id_entidad"]; ?>=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["dependencia"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["id_dependencia"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["cp"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["mail"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["telefono"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["direccion"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["lugar"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["id_distrito"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["contacto"]=new Array();
dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["comentario"]=new Array();
var cliente_<?php echo $clientes->fields["id_entidad"]; ?>=new Array();
cliente_<?php echo $clientes->fields["id_entidad"]; ?>["nombre"]="<?php if($clientes->fields["nombre"]){echo $clientes->fields["nombre"];}else echo "null";?>";
cliente_<?php echo $clientes->fields["id_entidad"]; ?>["direccion"]="<?php if($clientes->fields["direccion"]){echo $clientes->fields["direccion"];}else echo "null";?>";
cliente_<?php echo $clientes->fields["id_entidad"]; ?>["telefono"]="<?php if($clientes->fields["telefono"]){echo $clientes->fields["telefono"];}else echo "null";?>";
cliente_<?php echo $clientes->fields["id_entidad"]; ?>["mail"]="<?php if($clientes->fields["mail"]){echo $clientes->fields["mail"];}else echo "null";?>";
<?
$sql1="select * from dependencias where id_entidad=".$clientes->fields["id_entidad"];
$sql1.=" order by dependencia";
$dependencias=$db->execute($sql1) or die($db->errormsg(). " - ".$sql1);
$i=0;
while(!$dependencias->EOF)
  {
?>
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["dependencia"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["dependencia"]); ?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["id_dependencia"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["id_dependencia"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["telefono"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["telefono"])?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["cp"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["cp"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["direccion"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["direccion"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["mail"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["mail"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["lugar"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["lugar"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["id_distrito"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["id_distrito"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["contacto"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["contacto"]);?>";
    dependencias_<?php echo $clientes->fields["id_entidad"]; ?>["comentario"][<? echo $i; ?>]="<?php echo ereg_replace('"','\"',$dependencias->fields["comentario"]);?>";
<?
  $i++;
  $dependencias->MoveNext();
  }
$clientes->MoveNext();
}
?>

function set_datos()
{var i;
	switch(document.all.select_cliente.options[document.all.select_cliente.selectedIndex].value)
	{<?PHP
	 $clientes->Move(0);
	 while(!$clientes->EOF)
	 {?>
	  case '<? echo $clientes->fields["id_entidad"]?>': info=cliente_<? echo $clientes->fields["id_entidad"];?>;
	                                                    dependencias['dependencia']=new Array();
	                                                    dependencias['id_dependencia']=new Array();
	                                                    dependencias['telefono']=new Array();      
	                                                    dependencias['cp']=new Array();      
	                                                    dependencias['mail']=new Array();      
	                                                    dependencias['direccion']=new Array();      
	                                                    dependencias['id_distrito']=new Array();      
	                                                    dependencias['lugar']=new Array();
	                                                    dependencias['contacto']=new Array();
	                                                    dependencias['comentario']=new Array();
	                                                    i=0;
	                                                    while (i<dependencias_<? echo $clientes->fields["id_entidad"]; ?>['dependencia'].length)
	                                                     {dependencias['dependencia'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['dependencia'][i];
	                                                      dependencias['id_dependencia'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['id_dependencia'][i];
	                                                      dependencias['telefono'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['telefono'][i];
	                                                      dependencias['cp'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['cp'][i];
	                                                      dependencias['mail'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['mail'][i];
	                                                      dependencias['id_distrito'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['id_distrito'][i];
	                                                      dependencias['direccion'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['direccion'][i];
	                                                      dependencias['lugar'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['lugar'][i];
	                                                      dependencias['contacto'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['contacto'][i];
	                                                      dependencias['comentario'][i]=dependencias_<? echo $clientes->fields["id_entidad"]; ?>['comentario'][i];
	                                                      i++;
	                                                     }
	                                                    break;
	 <?
	  $clientes->MoveNext();
	 }
	 ?>
	}
	if(info["nombre"]!="null")
	 document.all.nombre.value=info["nombre"];
	else
	 document.all.nombre.value="";
	if(info["direccion"]!="null")
	 document.all.direccion.value=info["direccion"];
	else
	 document.all.direccion.value="";
	 if(info["telefono"]!="null")
	 document.all.telefono.value=info["telefono"];
	else
	 document.all.telefono.value="";
	 if(info["mail"]!="null")
	 document.all.mail.value=info["mail"];
	else
	 document.all.mail.value="";
} //fin de la funcion set_datos()

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
<?
$link=encode_link("caso_elegir_cliente.php",array('onclickcargar'=>$parametros['onclickcargar'],'onclicksalir'=>$parametros['onclicksalir']));
?>
<form name="form1" method="post" action="<? echo $link; ?>">

<table width=100% align=center border=0>
<tr>
  <td colspan=2 id=mo>
	 Casos - Selección de Clientes
  </td>
</tr>
<tr>
  <td width=50% align=center valign=top>
	<table width=100% align=center>
	 <tr>
	   <td colspan=2  id="ma"  align=center><b>Datos del Cliente</td>
	 </tr>
	 <tr>
	   <td colspan=2>&nbsp; </td>
	 </tr>
	 <tr>
	  <td width=20%> <b> Nombre: </td>
	  <td align=center><input type="text" name="nombre" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
	  </td>
	 </tr>
	 <tr>
	  <td><b> Dirección: </td>
	  <td align=center><input type="text" name="direccion" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
	  </td>
	 </tr>
	 <tr>
	  <td> <b> Teléfono: </td>
	  <td align=center> <input type="text" name="telefono" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
	  </td>
	 </tr>
	 <tr>
	  <td> <b> Mail: </td>
	  <td align=center><input type="text" name="mail" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;">
	  </td>
	 </tr>

	</table>
  </td>
  <td width=50% align=center>
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
	<tr>
	  <td  id="ma" colspan="2" align="center"><b>CLIENTES</b></td>
	</tr>
	<tr>
	<td colspan="2"><? tabla_filtros_nombres();?></td>
	</tr>
	<tr>
	  <td height="100%" colspan="2" align="center" nowrap>
	 <select name="select_cliente" size="10" style="width:100%" onchange="set_datos();" onkeypress="set_datos;buscar_op(this);set_datos();" onblur="borrar_buffer()">
	  <?
	  $clientes->Move(0);
	  while (!$clientes->EOF)
	  {
	  ?>
			<option value="<?=$clientes->fields['id_entidad'] ?>" >
			<?=$clientes->fields['nombre'] ?>
			</option>
			<?
	 $clientes->MoveNext();
	  }
	 ?>
		  </select>
		</p></td>
	</tr>
 </table>
 </td>
</tr>
<tr>
  <td align="center" colspan=2>
  <input name="aceptar" type="button" value="Cargar" onclick="<?=$parametros['onclickcargar']; ?>window.close();" style="width:'10%'">
  <input name="cancelar" type="button" value="Salir" onclick="<?=$parametros['onclicksalir']; ?>" style="width:'10%'">
  </td>
</tr>
</table>
</form>
</body>