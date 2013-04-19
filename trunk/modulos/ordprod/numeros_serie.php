<?
/*
Autor: lizi
Fecha: 12/04/05

MODIFICADA POR
$Author: mari $
$Revision: 1.16 $
$Date: 2006/10/17 16:02:12 $

*/
require_once("../../config.php");

$cant=$_POST['cant'] or $cant=$parametros['cant'];//echo $cant;
$cant_agregar=$_POST['cant_agregar'] or $cant_agregar=$parametros['cant_agregar'];
$id=$_POST['id'] or $id=$parametros['id']; // id_renglones_oc
$id_envio_renglones=$_POST['id_envio_renglones'] or $id_envio_renglones=$parametros['id_envio_renglones'];
$id_renglones_bultos=$_POST['id_renglones_bultos'] or $id_renglones_bultos=$parametros['id_renglones_bultos'];
// para controlar si viene de preparar envios o desde envios_generales
$pagina=$_POST['pagina'] or $pagina=$parametros['pagina'];
$id_remito_asociado=$_POST['id_remito_asociado']; 
$nro_remito_asociado=$_POST['nro_remito_asociado']; 
$nro_despacho=$_POST['nro_despacho'];

// para guardar
if (($_POST['guardar']=="Guardar")&&($_POST['pagina']=="preparar_envios")){
global $db;
$db->StartTrans();
	
   $id=$_POST['id'];
   $cantidad=$_POST['cantidad'];
   $id_renglones_bultos=$_POST['id_renglones_bultos'];
   $id_remito_asociado=$_POST['id_remito_asociado']; 
   $nro_despacho=$_POST['nro_despacho'];
   $id_envio_renglones=$_POST['id_envio_renglones'];
   
    // borro todos los nros de serie y los vuelvo a insertar
   $q_delete="delete from licitaciones_datos_adicionales.nro_serie_renglon  
              where id_renglones_oc=$id and id_renglones_bultos=$id_renglones_bultos";
   $res_q_delete=sql($q_delete, "Error al eliminar los nros de serie para el renglon") or fin_pagina(); 

   for ($i=0;$i<$cantidad;$i++){
     //busca proximo id_nro_serie_renglon
     $query_id= "select nextval('licitaciones_datos_adicionales.nro_serie_renglon_id_nro_serie_renglon_seq') as id";
     $res=sql($query_id,"Error al recuperar id_nro_serie_renglon") or fin_pagina();
     $id_nro_serie_renglon=$res->fields['id'];
     
     $ns=$_POST["nro_serie_$i"];
     
     $q_insert="insert into licitaciones_datos_adicionales.nro_serie_renglon (id_nro_serie_renglon,nro_serie,id_renglones_bultos, id_renglones_oc) 
                values ($id_nro_serie_renglon,'$ns',$id_renglones_bultos, $id)";
     $res_q_insert=sql($q_insert, "Error al insertar los números de serie para el renglón ") or fin_pagina();
   } // fin del for ($i=0;$i<$cantidad;$i++)
      
     if ($id_remito_asociado !="") {
          $q_update="update licitaciones_datos_adicionales.renglones_bultos 
                     set id_remito=$id_remito_asociado
                     where id_renglones_bultos=$id_renglones_bultos and id_renglones_oc=$id";
          $res_q_update=sql($q_update, "Error al insertar Remito Asociado al renglón") or fin_pagina();
     }

      if ($nro_despacho!="") {
      	$q_update="update licitaciones_datos_adicionales.renglones_bultos 
                set nro_despacho='$nro_despacho'
                where id_renglones_bultos=$id_renglones_bultos and id_renglones_oc=$id";
      	$res_q_update=sql($q_update, "Error al insertar Nro. de Despacho al renglón") or fin_pagina();
      } 
      $db->Completetrans();
   ?>
   <script>
      //window.close();
    </script>
   <?
 } // fin del if ($_POST['guardar']=="Guardar")
 
 
 if (($_POST['guardar']=="Guardar")&&($_POST['pagina']=="nuevos_envios")){
global $db;
$db->StartTrans();
	
   //$id=$_POST['id'];
   $cantidad=$_POST['cantidad'];
   $id_renglones_bultos=$_POST['id_renglones_bultos'];
   $id_remito_asociado=$_POST['id_remito_asociado']; 
   $nro_despacho=$_POST['nro_despacho'];
   $id_envio_renglones=$_POST['id_envio_renglones'];
  // print_r($_POST);
    // borro todos los nros de serie y los vuelvo a insertar
  $q_delete="delete from licitaciones_datos_adicionales.nro_serie_renglon  
              where id_renglones_bultos=$id_renglones_bultos";
   $res_q_delete=sql($q_delete, "Error al eliminar los nros de serie para el renglon") or fin_pagina(); 

   for ($i=0;$i<$cantidad;$i++){
     //busca proximo id_nro_serie_renglon
     $query_id= "select nextval('licitaciones_datos_adicionales.nro_serie_renglon_id_nro_serie_renglon_seq') as id";
     $res=sql($query_id,"Error al recuperar id_nro_serie_renglon") or fin_pagina();
     $id_nro_serie_renglon=$res->fields['id'];
     
     $ns=$_POST["nro_serie_$i"];
     
     $q_insert="insert into licitaciones_datos_adicionales.nro_serie_renglon (id_nro_serie_renglon,nro_serie,id_renglones_bultos) 
                values ($id_nro_serie_renglon,'$ns',$id_renglones_bultos)";
     $res_q_insert=sql($q_insert, "Error al insertar los números de serie para el renglón ") or fin_pagina();
   } // fin del for ($i=0;$i<$cantidad;$i++)
      
      if ($id_remito_asociado!="") { 
      
       $q_update="update licitaciones_datos_adicionales.renglones_bultos 
                set id_remito='$id_remito_asociado'
                where id_renglones_bultos=$id_renglones_bultos ";
       $res_q_update=sql($q_update, "Error al insertar Remito Asociado al renglón") or fin_pagina();
       //  }
      }
      if ($nro_despacho!="") {
      	$q_update="update licitaciones_datos_adicionales.renglones_bultos 
                set nro_despacho='$nro_despacho'
                where id_renglones_bultos=$id_renglones_bultos";
      	$res_q_update=sql($q_update, "Error al insertar Nro. de Despacho al renglón") or fin_pagina();
      } 
      $db->Completetrans();
   ?>
   <script>
      //window.close();
    </script>
   <?
 } // fin del if ($_POST['guardar']=="Guardar")
 
//solo cuando viene de la pagina preparar_envios consulto usando id_renglones_oc
if ($pagina=="preparar_envios") { 
// recupero los nros de serie q ingrese para el renglon
$q_select="select nro_despacho,numeracion_sucursal.numeracion || text('-') || remitos.nro_remito as nro_remito ,id_numeracion_sucursal, id_nro_serie_renglon, nro_serie 
           from licitaciones_datos_adicionales.nro_serie_renglon 
           left join licitaciones_datos_adicionales.renglones_bultos using (id_renglones_bultos)
           left join facturacion.remitos using (id_remito)
           left join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
           where nro_serie_renglon.id_renglones_oc=$id and id_renglones_bultos=$id_renglones_bultos"; 

$res_q_select=sql($q_select, "Error al traer los nros de serie para el renglon") or fin_pagina(); 
$nro_serie="";
$id_nro_serie_renglon=$res_q_select->fields['id_nro_serie_renglon'];
$t=1;

if ($id_nro_serie_renglon) {
    $nro_remito_asociado=$res_q_select->fields["nro_remito"];
    $id_remito_asociado=$res_q_select->fields["id_remito"];
	$nro_despacho=$res_q_select->fields['nro_despacho'];
	$cantidad_cargada=$res_q_select->RecordCount();
  } // if ($id_nro_serie_renglon) 
} // if ($pagina=="preparar_envios")

// cuando viene de la pagina envios_generales uso el id del producto ...
if ($pagina=="envios_generales") {
// consulta
}

if ($pagina=="nuevos_envios") {
//$cantidad_cargada=0;

$q_select="select nro_despacho, numeracion_sucursal.numeracion || text('-') || remitos.nro_remito as nro_remito,id_numeracion_sucursal,id_nro_serie_renglon, nro_serie 
           from licitaciones_datos_adicionales.nro_serie_renglon 
           left join licitaciones_datos_adicionales.renglones_bultos using (id_renglones_bultos)
           left join facturacion.remitos using (id_remito)
           left join facturacion.numeracion_sucursal using (id_numeracion_sucursal)
           where id_renglones_bultos=$id_renglones_bultos"; 

$res_q_select=sql($q_select, "Error al traer los nros de serie para el renglon") or fin_pagina(); 
$nro_serie="";
$id_nro_serie_renglon=$res_q_select->fields['id_nro_serie_renglon'];
$t=2;

if ($id_nro_serie_renglon) {
    $nro_remito_asociado=$res_q_select->fields["nro_remito"];
    $id_remito_asociado=$res_q_select->fields["id_remito"];
	$nro_despacho=$res_q_select->fields['nro_despacho'];
	$cantidad_cargada=$res_q_select->RecordCount();
  } // if ($id_nro_serie_renglon) 
}

echo $html_header;
echo "<b><center><font size='3' color='red'>".$msg."</font></center></b>";

?>

<script language="JavaScript" type="text/javascript">
function cargarSeries(){
	var arregloaux = new Array();
	var arreglo = new Array();
	var tamArreglo;
	arregloaux=window.clipboardData.getData("Text");
	arreglo=arregloaux.split("\n");
	tamArreglo=arreglo.length;
	var i=eval ("document.all.rango.value");
	var j=0;
	var error=0;
	var errorCont=0;
	while (j<tamArreglo-1){
		var res = eval("document.all.nro_serie_"+i);
		
		if (typeof (res)=="undefined"){
			error=1;
			errorCont++;			
		}
		else{
			res.value=arreglo[j];
		}
		i++;
		j++;
	}
	if (error==1){
		alert ("La Cantidad de Datos del Portapapeles es MAYOR a los Cuadros de Textos Disponibles en la Pagina.\nLo Sobrepasa en "+errorCont+" Fila/s.");
	} 
}
</script>


<form name="form1" method="POST" action="">
<input type="hidden" name="id" value="<?=$id?>">
<input type="hidden" name="id_renglones_bultos" value="<?=$id_renglones_bultos?>"> 
<input type="hidden" name="id_envio_renglones" value="<?=$id_envio_renglones?>">
<input type="hidden" name="id_renglones_oc" value="<?=$id?>">
<?
  $sql="select * from numeracion_sucursal where activo=1";
  $res=sql($sql) or fin_pagina();

  $link=encode_link("../remitos/remito_listar.php",array("backto"=>"numeros_serie"));
?>          

 <table align="center" width="100%">
   <tr>
    <td><input name="Buscar" type="button" value="Asociar Remito" onClick="window.open('<?=$link?>')"></td>
    <td> <input name="nro_remito_asociado" type="text" readonly  value="<?=$nro_remito_asociado?>" size="20"> 
        <input name="id_remito_asociado" type="hidden" value="<?=$id_remito_asociado?>"> 
    </td>    
    
   </tr>
   <tr>
     <td><b>Nro. de Despacho</td>
     <td colspan="2" align="left"><input type="text" name="nro_despacho" value="<?=$nro_despacho?>"></td>
   </tr>
 </table>  
 <br>
 <table width="100%" align="center">
  <tr><td id="ma" colspan="2"><font size="3">Ingresar Números de Serie
   </td>
  </tr>
  <? if ($cant_agregar>$cant) $cant=$cant_agregar; ?>
  <tr>
   <td align="center"><b>Cantidad</b> <input type="text" name="cant_agregar" value="<?=$cant?>" size="2"></td>
   <td align="center"><input type="submit" name="agregar" value="Agregar nros serie"></td>
   </tr>
 </table>
 <br>
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
  		<input type="text" value="0" name="rango" title="Ingrese el Numero Desde" size="4">
  	</td>
  </tr>
  
  <tr align="center">
  	  <td align="center" colspan="2">
  	  	<input type="button" name="cargar_series" value="Cargar Series del Portapapeles" onclick="cargarSeries();">
  	    <br>
  	  </td>
  </tr>
 </table>
<br>
 <?if ($pagina=="preparar_envios"){?>
 <table align="center" width="100%">  
  <? if ($cantidad_cargada>$cant) $cant=$cantidad_cargada; 
//echo "cant       ".$cant;
//echo($cant);
    for ($i=0;$i<$cant;$i++) {
  	 $nro_serie=$_POST["nro_serie_$i"] or $nro_serie=$res_q_select->fields['nro_serie'];
  	?>
  
  <tr>
   <td align="center">
    <?echo ("$i - ");?><input type="text" name="nro_serie_<?=$i?>" value="<?=$nro_serie?>" size="50">
   </td>
  </tr>
  <? $res_q_select->MoveNext();
    } ?>
  
  <input type="hidden" name="cantidad" value="<?=$cant?>">
  <input type="hidden" name="pagina" value="preparar_envios">
  
 </table>
 <?}?>
 <?if ($pagina=="nuevos_envios"){?>
 <table align="center" width="100%">  
  <? if ($cantidad_cargada>$cant) $cant=$cantidad_cargada; 
   //else $cant=$cantidad_cargada;
//echo "cant       ".$cant;
    for ($i=0;$i<$cant;$i++) {
  	 $nro_serie=$_POST["nro_serie_$i"] or $nro_serie=$res_q_select->fields['nro_serie'];
  	?>
  <tr>
   <td align="center">
    <?echo ("$i - ");?><input type="text" name="nro_serie_<?=$i?>" value="<?=$nro_serie?>" size="50">
   </td>
  </tr>
  <? $res_q_select->MoveNext();
    } ?>
  <input type="hidden" name="cantidad" value="<?=$cant?>">
  <input type="hidden" name="pagina" value="nuevos_envios">
 </table>
 <?}?>

 <table width="100%" align="center">
  <tr>
   <td align="center">
    <input type="submit" name="guardar" value="Guardar">
   </td>
   <td align="center"> 
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr>
 </table> 

</form>
</body>
</html>