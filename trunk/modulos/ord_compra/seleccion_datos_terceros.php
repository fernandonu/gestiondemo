<?
/*
Autor: MAC
Fecha: 23/07/2005

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.1 $
$Date: 2005/08/17 15:24:03 $

*/

require_once("../../config.php");

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
   /* echo "<tr>";
    echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value=''; document.form1.submit();\"> Los mas Usados";
    echo "</td>";
   echo "</tr>";*/
   echo "</table>";
}  //de la funcion

extract($_POST,EXTR_SKIP);

if ($parametros)
 extract($parametros,EXTR_OVERWRITE);

if($modo=="despachantes")
{/*if($filtro!="")
 {*/
  if ($filtro=='Todas') 
   $q="select id_despachante as id,nombre,domicilio,telefono,mail from despachante order by nombre";
  else 
   $q="select id_despachante as id,nombre,domicilio,telefono,mail from despachante where nombre ilike '$filtro%' order by nombre ";
  $datos=sql($q,"No se pudo realizar la consulta que trae los despachantes") or fin_pagina();   	
 /*}
 else
 {
  $id_usuario=$_ses_user['id'];	
  $sql="select * from licitaciones.usuarios_clientes 
      left join licitaciones.entidad using(id_entidad)
      where id_usuario=$id_usuario order by peso_uso desc limit 10";
  $mas_usados=sql($sql,"No pudo recuperar los clientes mas usados") or fin_pagina();  
 } */ 
}//de if($modo=="despachantes")
else if($modo=="fletes")
{
 /*if($filtro!="")
 {*/
  if ($filtro=='Todas') 
   $q="select id_flete as id,nombre,domicilio,telefono,mail from flete order by nombre";
  else 
   $q="select id_flete as id,nombre,domicilio,telefono,mail from flete where nombre ilike '$filtro%' order by nombre ";
  $datos=sql($q,"No se pudo realizar la consulta que trae los fletes") or fin_pagina();   	
 /*}
 else
 {
  $id_usuario=$_ses_user['id'];	
  $sql="select * from licitaciones.usuarios_clientes 
      left join licitaciones.entidad using(id_entidad)
      where id_usuario=$id_usuario order by peso_uso desc limit 10";
  $mas_usados=sql($sql,"No pudo recuperar los clientes mas usados") or fin_pagina();  
 } */ 
}//de else if($modo=="fletes")


echo $html_header;
?>

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

function setear_datos(id)
{
 if(id!=-1)	
 {document.all.nombre.value=eval('document.all.nombre_'+id+'.value');
  document.all.domicilio.value=eval('document.all.domicilio_'+id+'.value');
  document.all.telefono.value=eval('document.all.telefono_'+id+'.value');
  document.all.mail.value=eval('document.all.mail_'+id+'.value');
  document.all.aceptar.disabled=0;
 }
 else
 {
  document.all.nombre.value='';
  document.all.domicilio.value='';
  document.all.telefono.value='';
  document.all.mail.value='';
  document.all.aceptar.disabled=1;
 }       
}//de function setear_datos(id)
</script>

<form name='form1' method="POST" action="<?=encode_link("seleccion_datos_terceros.php",array("modo"=>$modo,"onclick_cargar"=>$onclick_cargar))?>">
<table width="100%" border="0" cellspacing="1" cellpadding="1">
    <tr> 
      <td height="27" colspan="2" align="center">
       <?if($modo=="despachantes")
          $titulo="DESPACHANTES";
         elseif ($modo=="fletes") 
          $titulo="FLETES";
       ?>
       <b><?=$titulo?></b>
      </td>
    </tr>    
	<tr>
	<td colspan="2"><? tabla_filtros_nombres();?></td>
	</tr>
    <tr> 
      <td height="168" colspan="2" align="center" nowrap> 
        <center> 
        <!--  <select name="select_entidad" size="10" style="width:576" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="editar_campos(0);if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=0;borrar_buffer()">       -->
         <select name="elegir_uno" size="10" style="width:576"  ondblclick="<?=$onclick_cargar?>;"
          onchange="setear_datos(this.value);"
          onclick="setear_datos(this.value);"
          onKeypress="
           if(event.keyCode==13)
            <?=$onclick_cargar?>
           buscar_op(this);
          " 
          onblur="borrar_buffer()" 
          onclick="borrar_buffer()"  
        >    
         <option value="-1">
          Seleccione...
         </option>
         <?
         while (!$datos->EOF)
         {
          ?>
          <option value="<?=$datos->fields["id"]?>" <?if($datos->fields["id"]==$_POST["elegir_uno"])echo "selected"?>>
           <?=$datos->fields["nombre"]?>
          </option>
          <?	
          $datos->MoveNext();
         }//de while(!$datos->EOF)
         ?>
        </select>   
        <?
        //almacenamos en hiddens, los datos de cada nupla
        $datos->Move(0);
        while (!$datos->EOF)
        {?>
         <input type="hidden" name="nombre_<?=$datos->fields["id"]?>" value="<?=$datos->fields["nombre"]?>">
         <input type="hidden" name="domicilio_<?=$datos->fields["id"]?>" value="<?=$datos->fields["domicilio"]?>">
         <input type="hidden" name="telefono_<?=$datos->fields["id"]?>" value="<?=$datos->fields["telefono"]?>">
         <input type="hidden" name="mail_<?=$datos->fields["id"]?>" value="<?=$datos->fields["mail"]?>">
         <?
         $datos->MoveNext();
        }//de while(!$datos->EOF)
        ?>
     </td>
    </tr>
  </table>
  <table width="95%" border="0" cellspacing="1" cellpadding="1" align="center">   
    <tr>
     <td width="10%">
      Nombre 
     </td> 
     <td width="50%"> 
      <input type="text" name="nombre" value="" size="50" onclick="<?=$onclick_cargar?>;">
     </td>
     <td width="10%">
      E-Mail
     </td>
     <td>
      <input type="text" name="mail" value="" size="40">
     </td>
    </tr>
    <tr> 
     <td>
      Domicilio 
     </td> 
     <td> 
      <input type="text" name="domicilio" value="" size="50">
     </td>
     <td>
      Teléfono 
     </td>
     <td>
      <input type="text" name="telefono" value="" size="40">
     </td>
    </tr>
   </table> 
   <br>
   <table width="95%" border="0" cellspacing="1" cellpadding="1" align="center"> 
    <tr>
     <td width="50%" align="right">
      <input type="button" name="aceptar" disabled value="Aceptar" onclick="<?=$onclick_cargar?>">
     </td>
     <td width="50%">
      <input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
     </td>
    </tr>
   </table>
</body>
</html>