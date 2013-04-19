<?
/*
Autor: GACZ

MODIFICADA POR
$Author: ferni $
$Revision: 1.23 $
$Date: 2005/10/17 14:50:53 $
*/

require_once("../../config.php");
extract($_POST,EXTR_SKIP);



//obtengo valor $filtro ->letra seleccionada en el filtro, es 'a' si es la primera vez que carga, es 'vacio' si selecciona 'Todos'
/*if($letra!="")
 $filtro=$letra;
elseif($_POST['filtro']=="")
 $filtro="a";
elseif($_POST['filtro']=="Todos")
 $filtro="";
else
 $filtro=$_POST['filtro'];*/

//muestra tabla para filtar proveedores


function tabla_filtros_nombres(){

 $abc=array("a","b","c","d","e","f","g","h","i",
            "j","k","l","m","n","ñ","o","p","q",
            "r","s","t","u","v","w","x","y","z");
$cantidad=count($abc);

echo "<table  align='center' width='80%' height='80%' id='mo'>";
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
    echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='Todas'; document.form1.submit();\"> Todos";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value=''; document.form1.submit();\"> Los mas Usados";
    echo "</td>";
   echo "</tr>";
   echo "</table>";
}  //de la funcion


if ($parametros)
 extract($parametros,EXTR_OVERWRITE);
if($filtro!="")
  {if ($filtro=='Todas') $q="select * from entidad order by nombre";
   else $q="select * from entidad where nombre ilike '$filtro%' and activo_entidad=1 order by nombre ";
   $clientes=sql($q,"No se pudo realizar la consulta que trae los clientes") or fin_pagina();   	
  }
else{
$id_usuario=$_ses_user['id'];	
$sql="select * from licitaciones.usuarios_clientes 
      left join licitaciones.entidad using(id_entidad)
      where id_usuario=$id_usuario order by peso_uso desc limit 10";
$mas_usados=sql($sql,"No pudo recuperar los clientes mas usados") or fin_pagina();  
}  
?>
<head>
<title>Clientes</title>
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<?=$html_header?>
</head>
<body topmargin="0">
<script>
/**********************************************************/
//funciones para busqueda abreviada utilizando teclas en la lista que muestra los clientes.
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

<form name="form1" method="post" action="" >
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr> 
      <td height="27" colspan="2" align="center"><b>CLIENTES</b></td>
    </tr>    
	<tr>
	<td colspan="2"><? tabla_filtros_nombres();?></td>
	</tr>
    <tr> 
      <td height="168" colspan="2" align="center" nowrap> 
        <center> 
        <!--  <select name="select_entidad" size="10" style="width:576" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="editar_campos(0);if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=0;borrar_buffer()">       -->
         <select name="select_cliente" size="10" style="width:576"  ondblclick="<?=$onclickaceptar?>" 
onchange="if (this.selectedIndex!=-1 && aceptar.disabled) 
	      aceptar.disabled=0;
	      document.all.nbrecl.disabled=0;
	      email.disabled=0;
	      direccion.disabled=0;
	      telefono.disabled=0;
          direccion.value=eval('document.all.direccion_'+ this[this.selectedIndex].value +'.value');
          telefono.value=eval('document.all.telefono_'+ this[this.selectedIndex].value +'.value');
          email.value=eval('document.all.email_'+ this[this.selectedIndex].value +'.value');
          nbrecl.value=eval('document.all.nbrecl_'+ this[this.selectedIndex].value +'.value');"
onKeypress="if (this.selectedIndex!=-1 && aceptar.disabled) 
	aceptar.disabled=0;
	document.all.nbrecl.disabled=0;
	email.disabled=0;
	direccion.disabled=0;
	telefono.disabled=0;
 direccion.value=eval('document.all.direccion_'+ this[this.selectedIndex].value +'.value');
 telefono.value=eval('document.all.telefono_'+ this[this.selectedIndex].value +'.value');
 email.value=eval('document.all.email_'+ this[this.selectedIndex].value +'.value');
 nbrecl.value=eval('document.all.nbrecl_'+ this[this.selectedIndex].value +'.value');
 if(event.keyCode==13){<?//$usuario=$_ses_user['id'];
                         //$id_cliente=10;
                         //$fecha=date("Y-m-d H:m:s");
                         //$sql="insert into licitaciones.usuarios_clientes (id_usuario,id_entidad,fecha_ultimo_uso)
                           //    values ($usuario,$id_cliente,'$fecha')";
                         //$consulta=sql($sql) or fin_pagina();
                         echo $onclickaceptar
                        ?>}
buscar_op(this);
if (this.selectedIndex!=-1 && aceptar.disabled) 
	aceptar.disabled=0;
	document.all.nbrecl.disabled=0;
	email.disabled=0;
	direccion.disabled=0;
	telefono.disabled=0;
 direccion.value=eval('document.all.direccion_'+ this[this.selectedIndex].value +'.value');
 telefono.value=eval('document.all.telefono_'+ this[this.selectedIndex].value +'.value');
 email.value=eval('document.all.email_'+ this[this.selectedIndex].value +'.value');
 nbrecl.value=eval('document.all.nbrecl_'+ this[this.selectedIndex].value +'.value');
" 
onblur="borrar_buffer()" 
onclick="borrar_buffer()"  
>            <?
if ($filtro!="")
{ 
while (!$clientes->EOF)
{
?>
            <option value="<?=$clientes->fields['id_entidad'] ?>" > 
            <?=$clientes->fields['nombre'] ?>
            </option>
            <?
	$clientes->MoveNext();
}
}
else {
	 while (!$mas_usados->EOF)
{
?>
            <option value="<?=$mas_usados->fields['id_entidad'] ?>" > 
            <?=$mas_usados->fields['nombre'] ?>
            </option>
            <?
	$mas_usados->MoveNext();
}
}
?>
          </select>
        </center>
        <b>Para elegir el cliente para la orden de compra, seleccionelo de la lista,<br> modifique los campos que sean necesarios y presione el botón "Cargar Cliente"</b><br><br><br>
        </td>
     </tr>
	<tr><td colspan="2" align="center"><strong>Nombre del Cliente:</strong> <input name="nbrecl" type="text" size="50" disabled > </td></tr>
    <tr> 
      <td width="72%" height="91" rowspan="2" align="center"><strong>Dirección</strong>&nbsp;&nbsp; &nbsp;&nbsp;
        <input name="chk_direccion" type="checkbox" id="chk_direccion" value="1" checked>
        dirección de entrega<br> 
        <textarea name="direccion" cols="45" id="direccion" disabled></textarea> </td>
      <td width="28%" height="45" align="center"><strong>Telefono</strong> <br><input name="telefono" type="text" id="telefono" disabled > 
      </td>
    </tr>
    <tr> 
      <td height="45" align="center"><strong>Email</strong><br> <input name="email" type="text" disabled style="text-align: right;"> 
      </td>
    </tr>
    <tr> 
      <td colspan="2" height="20" align="center"><input name="aceptar" type="button" value="Cargar Cliente" disabled onclick="<?=$onclickaceptar?>"> 
        &nbsp;&nbsp; <input name="cancelar" type="button" value="Cerrar" onclick="location.href=<?=$onclicksalir?>"></td>
    </tr>
  </table>
<?
if ($filtro!="")
{ 
$clientes->MoveFirst();
while (!$clientes->EOF)
{
?>
  <input type="hidden" name="direccion_<?=$clientes->fields['id_entidad'] ?>" value="<?=$clientes->fields['direccion'] ?>" > 
  <input type="hidden" name="telefono_<?=$clientes->fields['id_entidad'] ?>" value="<?=$clientes->fields['telefono'] ?>" > 
  <input type="hidden" name="email_<?=$clientes->fields['id_entidad'] ?>" value="<?=$clientes->fields['mail'] ?>" >
  <input type="hidden" name="nbrecl_<?=$clientes->fields['id_entidad'] ?>" value="<?=$clientes->fields['nombre'] ?>" >  
  
<?
	$clientes->MoveNext();
}
}
else{

$mas_usados->MoveFirst();
while (!$mas_usados->EOF)
{
?>
  <input type="hidden" name="direccion_<?=$mas_usados->fields['id_entidad'] ?>" value="<?=$mas_usados->fields['direccion'] ?>" > 
  <input type="hidden" name="telefono_<?=$mas_usados->fields['id_entidad'] ?>" value="<?=$mas_usados->fields['telefono'] ?>" > 
  <input type="hidden" name="email_<?=$mas_usados->fields['id_entidad'] ?>" value="<?=$mas_usados->fields['mail'] ?>" >
  <input type="hidden" name="nbrecl_<?=$mas_usados->fields['id_entidad'] ?>" value="<?=$mas_usados->fields['nombre'] ?>" >  
  
<?
	$mas_usados->MoveNext();
}
}
echo fin_pagina();
?>
  
