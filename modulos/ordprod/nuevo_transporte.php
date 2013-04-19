<?
/*
Autor: lizi
Creado: martes 06/06/05

MODIFICADA POR
$Author: elizabeth $
$Revision: 1.5 $
$Date: 2005/06/24 21:20:48 $
*/

require_once("../../config.php");


$onclick['aceptar']=$parametros['onclickaceptar'];
$onclick['cancelar']=$parametros['onclickcancelar'] or $onclick['cancelar']="window.close()";

$nombre_transporte=$_POST['nombre_transporte'];
$contacto_transporte=$_POST['contacto_transporte'];
$direccion_transporte=$_POST['direccion_transporte'];
$telefono_transporte=$_POST['telefono_transporte'];
$comentarios=$_POST['comentarios'];

if ($_POST['aceptar']=="Guardar"){
  $db->StartTrans();	
    $nombre_transporte=$_POST['nombre_transporte'];
    $contacto_transporte=$_POST['contacto_transporte'];
    $direccion_transporte=$_POST['direccion_transporte'];
    $telefono_transporte=$_POST['telefono_transporte'];
    $comentarios=$_POST['comentarios'];
    
    // control de q no inserte un nuevo transporte con el mismo nombre
    $q_control="select nombre_transporte from licitaciones_datos_adicionales.transporte 
    			where nombre_transporte ilike '$nombre_transporte'";
    $res_q_control=sql($q_control, "Error al traer los nbres de los transportes") or fin_pagina();
    $res=$res_q_control->RecordCount();
    
    if ($res==0) {
    
    $q="select nextval ('licitaciones_datos_adicionales.transporte_id_transporte_seq') as id_transporte";
    $res=sql($q, "Error al traer la secuencia para el nuevo transporte") or fin_pagina();
    $id_transporte=$res->fields['id_transporte'];
    
    $q_transporte="insert into licitaciones_datos_adicionales.transporte (id_transporte, nombre_transporte, 
                   contacto_transporte, direccion_transporte, telefono_transporte, comentarios_transporte)
                   values ($id_transporte,'$nombre_transporte', '$contacto_transporte', '$direccion_transporte', 
                   '$telefono_transporte', '$comentarios')";
    $res_q_transporte=sql($q_transporte, "Error al insertar el nuevo tranporte") or fin_pagina(); 
?>   
    <script language='javascript' src='../../lib/fns.js'></script> 
    <script>
     var id_transporte=<?=$id_transporte?>;
     var nbre_transporte='<?=$nombre_transporte?>'; 
     var comentarios='<?=$comentarios?>'
     var transporte;
     transporte=eval("window.opener.document.all.transporte");
     add_option(transporte,id_transporte,nbre_transporte);
     transporte.selectedIndex=transporte.options.length-1;
     window.opener.document.all.comentarios_transporte.value=comentarios;
     window.opener.comentarios[id_transporte]=comentarios;
     this.close();
    </script>
<?  }
	else {
	   $msg="El transporte <b>$nombre_transporte </b> ya existe";
	}
  $db->Completetrans();	
}
echo $html_header;
?>
<script>

function control_datos(){
  if (document.all.nombre_transporte.value==""){
  	alert ("Debe ingresar un nombre para el Transporte"); 
    return false;
  }
 return true;  
}
</script>
<? echo "<center><font size='3' color='red'>$msg</center>"; ?>

<form name='form1' method="post" action="">
<table align="center" width="100%" class='tabla_cont'>
   <tr>
     <td align="center" colspan="2" id='mo'><font size="3"><b>Ingresar los Datos para el Transporte</b></td>
   </tr>
   <tr><td>&nbsp;</td></tr>
   <tr>
     <td><b>Nombre Transporte: </b></td><td><input type="text" name="nombre_transporte" value="<?=$nombre_transporte?>" size="50"></td>
   </tr>
   <tr>     
     <td><b>Nombre Contacto: </b></td><td><input type="text" name="contacto_transporte" value="<?=$contacto_transporte?>" size="50"></td>
   </tr>
   <tr>      
     <td><b>Dirección: </b></td><td><input type="text" name="direccion_transporte" value="<?=$direccion_transporte?>" size="50"></td>
   </tr>
   <tr>      
     <td><b>Teléfono: </b></td><td><input type="text" name="telefono_transporte" value="<?=$telefono_transporte?>"></td>   
   </tr>
   <tr><td>&nbsp;</td></tr>
   <tr>
     <td><b>Comentarios del Transporte: </b></td>
     <td><textarea name="comentarios" cols="50" rows="3"><?=$comentarios?></textarea></td>
   </tr>
   <tr>
     <td align="center" colspan="2">
	     <input type="submit" name="aceptar" value="Guardar" onclick="return control_datos();">
	     &nbsp; &nbsp;
	     <input type="button" name="cancelar" value="Cancelar" onclick="window.close()">
     </td>
   </tr>
</table>
</form>
</body>
</html> 