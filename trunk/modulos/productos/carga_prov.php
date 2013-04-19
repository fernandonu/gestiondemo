<?PHP
/*
$Author: ferni $
$Revision: 1.91 $
$Date: 2007/03/16 20:53:27 $
*/

//value de boton agregar contacto = 'Agregar Contacto'
include("../../config.php");
require_once "../../lib/class.gacz.php";
/*print_r($_POST);
echo "<br>";
print_r($parametros);*/

if ($_POST['nuevo']=='Nuevo Proveedor')   {
	$_POST["select_razon_social"]="";
	$parametros["id_prov"]="";
}
//borrar archivo
if ($_POST['borrar_archivo']=="Borrar Archivo")
{
 
 $archivo_comp=$_POST['nombre_archivo_comp'];
 if (unlink(UPLOADS_DIR."/proveedores/archivos_iso/$archivo_comp")){
 	$sql="delete from general.prov_archivos_subidos_iso where id_prov_archivos_subidos_iso=".$_POST['id_archivo_subido'];
    $borrado=sql($sql) or fin_pagina();
    $msg.="El Archivo \"$archivo_comp\" se borro Correctamente.<br>";
 }
 else $msg.="El Archivo \"$archivo_comp\" no se pudo Borrar.<br>";
       	       
}//del post de borrar archivos	 	

//funcion para bajar archivato
if ($parametros["download"]) {
	$sql = "select * from general.prov_archivos_subidos_iso where id_prov_archivos_subidos_iso = ".$parametros["FileID"];
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	

	if ($parametros["comp"]) {
		$FileName = $result->fields["nombre_archivo_comp"];		
		$FileNameFull = UPLOADS_DIR."/proveedores/archivos_iso/$FileName";		
		$FileType="application/zip";
		$FileSize = $result->fields["filesize_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["nombre_archivo"];
		$FileNameFull = UPLOADS_DIR."/proveedores/archivos_iso/$FileName";
		$FileType = $result->fields["filetype"];
		$FileSize = $result->fields["filesize"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}	
}//fin de la funcion downnload

function cargar_credito($id){
global $db,$permiso_editar;
?>
	<hr>
<?
$sql="select * from credito_proveedor where id_proveedor = $id";
$result=sql($sql,"Error extrayendo el credito del proveedor") or fin_pagina();

if ($result->RecordCount()>0) {
	$moneda=$result->fields["id_moneda"];
	$limite=$result->fields["limite"];
	$valor_dolar=$result->fields["valor_dolar"];
	$id_pago_st=$result->fields["id_plantilla_pagos"];
	$id_proveedor=$result->fields["id_proveedor"];
} else {
	$moneda=-1;
	$limite=0;
	$valor_dolar=0;
	$id_pago_st=-1;
	$id_proveedor=-1;
}
?>
<TABLE class="bordes" align="center">
<TR>
<TD id="mo" colspan="2">Configuración del Límite de Crédito asignado al Proveedor</td>
</tr>
<TR id="ma">
<TD>Límite de crédito</td>
<TD align="right">
<SELECT name="moneda">
	<OPTION <?if ($moneda == -1) echo "selected";?>></OPTION>
	<OPTION value="1" <?if ($moneda == 1) echo "selected";?>>$</OPTION>
	<OPTION value="2" <?if ($moneda == 2) echo "selected";?>>U$S</OPTION>
</SELECT>
<INPUT type="text" name="limite" value="<?=number_format($limite,2,'.','')?>" style="text-align:'right';">
</td>
</tr>
<?
/*<!--<TR  id="ma" >
<TD>Valor del Dólar tomado</td>
<TD align="right">
$<INPUT type="text" name="valor_dolar" size="8"  value="<?//=number_format($valor_dolar,2,'.','')?>" style="text-align:'right';">
</td>
</tr>-->
*/?>
<INPUT type="hidden" name="valor_dolar"  value="0"><!-- oculto para completar dolar no usado-->
<TR  id="ma">
<TD>Forma de Pago Estándar</td>
<TD align="right">
<SELECT name="f_pago_est">
<OPTION <?if ($id_pago_st == -1) echo "selected";?>></OPTION>
<?
 $sql="select id_plantilla_pagos,descripcion from plantilla_pagos where mostrar=1";
 $result=sql($sql,"error buscando las formas de pago ".$sql) or die();
 
 while(!$result->EOF){
 	$descripcion = $result->fields["descripcion"];
 	$id_plantilla = $result->fields["id_plantilla_pagos"];
 	echo "<option value='$id_plantilla' ";
 	if ($id_pago_st == $id_plantilla) echo "selected";
 	echo ">$descripcion</option>";
 	$result->MoveNext();
 }
?>
</SELECT>
</td>
</tr>
<TR id="ma">
<TD colspan="2"><INPUT type="submit" name="guardar" value="Guardar Configuración"  style='cursor:hand' onclick="return check_guardar();" <? if(!$permiso_editar) echo "disabled"; ?>></TD>
</TR>
</TABLE>
<input type='hidden' name='id_proveedor' value='<?=$id?>'>
<SCRIPT>
function check_guardar() {
	var falta = '';
	if (document.all.moneda.selectedIndex == 0) falta+='- Debe establecer una moneda. \n'; 
	if (document.all.f_pago_est.selectedIndex == 0) falta+='- Debe establecer una forma de pago.'; 
	if (falta !='') {
		alert(falta);
		return false;
	} else {
		return true;
	}
}
function habilitaSubeArch(i){
	/*
	if (i==1){
		eval("document.all.sube_arch_isov.style.display='disabled'");
	}
	else {
		eval("document.all.sube_arch_isov.style.display='enable'");
	}*/

}
</SCRIPT>
<hr>
<?
}//FIN DE LA FUNCION CARGAR CREDITO

//db_tipo_res("a");
$state=$parametros['tipo_prov'].$_POST['tipo_prov'] or $state="todos";
$link_prov=($parametros['backto'])?encode_link($parametros['backto'],array()):encode_link("proveedores.php",array("cant_filas_mostradas"=> $parametros["cant_filas_mostradas"],"total_prov"=> $parametros["total_prov"],"nro_paginas"=> $parametros["nro_paginas"],"pagina_actual"=> $parametros["pagina_actual"],"texto_buscado"=> $parametros["texto_buscado"],"estado"=>$state));
$vio_datos=0;
if ($_POST['ver_datos']=="Ver Datos")
{$vio_datos=1;
}	


if ($_POST['vio_datos']==1 || $vio_datos==1)
{
$id=$parametros["id_prov"] or $id=$_POST["select_razon_social"];
}
else $id=$parametros["id_prov"];

//con esto indicamos si se esta editando o no, para que dependiendo de 
//si tiene permiso o no, pueda editar el proveedor seleccionado
if(es_numero($id))
 $editando=1;
else {
 $editando=0;
}


//vemos el permiso de edicion que el usuario tiene
if(permisos_check("inicio","permiso_editar_prov")) 
 $permiso_editar=1;
else 
 $permiso_editar=0; 
 
$pagina=$parametros["pagina"];
//$link1=encode_link("carga_prov.php",array("pagina"=>$pagina, "cant_filas_mostradas"=> $parametros["cant_filas_mostradas"],"total_prov"=> $parametros["total_prov"],"nro_paginas"=> $parametros["nro_paginas"],"pagina_actual"=> $parametros["pagina_actual"],"query"=> $parametros["query"],"texto_buscado"=> $parametros["texto_buscado"],"tipo_prov"=>$state));


//traemos los tipos de proveedores para generar el select de tipos de proveedores
        $query="select * from tipos_prov";
        $tipos_prov=$db->Execute($query) or die ($db->ErrorMsg().$query);

//vemos el tipo de cuenta por defecto que tiene este proveedor
if (es_numero($id)){
	$select_tcuenta = "Select * from cuentas where id_proveedor=$id and es_default=1";
	$res_tcuenta = sql($select_tcuenta,"Error consultando las cuentas del proveedor") or fin_pagina();

	if ($res_tcuenta->RecordCount()>0) {//si hay uno por default para este proveedor
		$def_anterior = $res_tcuenta->fields["numero_cuenta"];
	} else { //si no tiene default
		$def_anterior = -1;
	}
} else $def_anterior = -1;

if($_POST["activo"]=="activo")
         $activo='true';
        else
         $activo='false';    

if($_POST["calif_prove"]=="calif_prove")
         $calif_prove='true';
        else
         $calif_prove='false';    
        
//echo "<br>La pagina es: ".$pagina."<br>";
if (($_POST["agregar"] == "Agregar/Modificar proveedor") && ($pagina!="proveedores") &&!(es_numero($id) ))
   {
           //inicio de transaccion
        $db->StartTrans();
          //insertamos el nuevo proveedor
        $nextq = "Select nextval('proveedor_id_proveedor_seq')"; 
        $nextr = sql($nextq,"Error obteniendo datos para el proveedor") or fin_pagina();  
        $next = $nextr->fields["nextval"];
        
        
        $prov_loc=0;
        $prov_inter=0;
        $prov_pago_serv=0;
        if ($_POST['check_1']==1) $prov_loc=1;
        if ($_POST['check_2']==1) $prov_inter=1;
        if ($_POST['check_3']==1) $prov_pago_serv=1;
        $prov_iso=0;
        if ($_POST['check_iso']==1) $prov_iso=1;
                
        
        $query1="INSERT INTO proveedor (id_proveedor,razon_social,politica_rma,cuit,iva,observaciones,activo,nbre_fantasia,proveedor_local,proveedor_internacional,proveedor_pago_servicio,iso,calif_prove) 
                 VALUES ($next,'".$_POST['nombre_proveedor']."','".$_POST['politica_rma']."','".$_POST['text_cuit']."','".$_POST['text_iva']."','".$_POST['text_observaciones']."','$activo','".$_POST['text_fantasia']."',$prov_loc,$prov_inter,$prov_pago_serv,$prov_iso,'$calif_prove')";
        sql($query1,"error agregando un nuevo proveedor") or fin_pagina();
        //        $db->Execute("$query1")or die($db->ErrorMsg().$query1);

        //insertar el tipo de cuenta del proveedor nuevo
        if ($_POST["cuentas"]!=-1){
        	$ins_tcuenta="insert into cuentas (id_proveedor,numero_cuenta,es_default) values ($next,".$_POST["cuentas"].",1)";
        	sql($ins_tcuenta,"Error insertando el tipo de cuenta para este proveedor") or fin_pagina();
        }
        $def_anterior = $_POST["cuentas"];
           /* PARA AGREGAR LOS TIPOS DEL PROVEEDOR SEGUN LOS CHECK....NO BORRAR!!!
           //seleccionamos el nuevo id recien insertado
           $query1="select max(id_proveedor) as p from proveedor";
           $pp=$db->Execute("$query1")or die($db->ErrorMsg().$query1);


           //insertamos los tipos del nuevo proveedor
           $tipos_prov->Move(0);
           while(!$tipos_prov->EOF)
           {//insertamos el tipo si se selecciono el checkbox correspondiente
            $nombre_check=$tipos_prov->fields["descripcion"];
            $tipo_check=$tipos_prov->fields["tipo"];
            if($_POST[$nombre_check]==$tipo_check)
            {$query1="insert into prov_t (id_proveedor,tipo) values(".$pp->fields['p'].",'$tipo_check')";
             $db->Execute("$query1")or die($db->ErrorMsg().$query1);
            }
            $tipos_prov->MoveNext();
           }*/
           //cierra transaccion
           $sql_cuenta_nueva="select * from tipo_cuenta where numero_cuenta=".$_POST["cuentas"];
	       $resul_cuenta_nueva=sql($sql_cuenta_nueva,"Error listando las cuentas") or fin_pagina();
           $cuenta_nueva=$resul_cuenta_nueva->fields['concepto']." [".$resul_cuenta_nueva->fields['plan']."]";
       if ($db->CompleteTrans()) 
          $fecha= date("d/m/Y",mktime());
          $asunto="Se cargo nuevo Proveedor";
          //$para="noelia@pcpower.com.ar,carlos@coradir.com.ar,juanmanuel@coradir.com.ar,corapi@coradir.com.ar";
          $para="noelia@pcpower.com.ar,carlos@coradir.com.ar,juanmanuel@coradir.com.ar";
          //$para="broggi@coradir.com.ar,marco@pcpower.com.ar";
          $mensaje="Se agrego un nuevo Proveedor con los siguientes Datos:";
          //$mensaje.="\nId_proveedor           $next";
          $mensaje.="\nRazon Social           ".$_POST['nombre_proveedor'];
          $mensaje.="\nNro. C.U.I.T.          ".$_POST['text_cuit'];
          $mensaje.="\nNombre Fantasia        ".$_POST['text_fantasia'];
          $mensaje.="\nTipo de I.V.A.         ".$_POST['text_iva'];
          $mensaje.="\nPolítica RMA.          ".$_POST['politica_rma'];
          $mensaje.="\nCuenta por defecto     ".$cuenta_nueva;          
          $mensaje.="\n\nEl nuevo Proveedor fue agregado por el usuario ".$_ses_user['name'].", el día $fecha";
          enviar_mail($para,$asunto,$mensaje,"","","",0);     
          header("Location:carga_prov.php"); 
}

 if (($_POST["agregar"] == "Agregar/Modificar proveedor") && (($pagina=="proveedores")||(es_numero($id))))
 { //die("esta en el actualizar");
     //inicio de transaccion
    $db->StartTrans();
    $sql="select * from general.proveedor where id_proveedor=$id";
    $resul_recupe=sql($sql,"No se pudieron recuperar los datos antiguos") or fin_pagina(); 
    
    $prov_loc=0;
    $prov_inter=0;
    $prov_pago_serv=0;
    
    if ($_POST['check_1']==1) $prov_loc=1;
    if ($_POST['check_2']==1) $prov_inter=1;
    if ($_POST['check_3']==1) $prov_pago_serv=1;
    
     $prov_iso=0;
     if ($_POST['check_iso']==1) $prov_iso=1;
        
    $query5="UPDATE proveedor SET razon_social='".$_POST["nombre_proveedor"]."',politica_rma='".$_POST["politica_rma"]."',cuit='".$_POST["text_cuit"]."',iva='".$_POST["text_iva"]."',observaciones='".$_POST["text_observaciones"]."',activo='$activo', nbre_fantasia='".$_POST['text_fantasia']."',proveedor_local=$prov_loc,proveedor_internacional=$prov_inter,proveedor_pago_servicio=$prov_pago_serv,iso=$prov_iso,calif_prove='$calif_prove' WHERE id_proveedor=$id";
    sql($query5,"Error actualizando los datos del proveedor<br>$query5") or fin_pagina();
   
    
    
    $sql_cuenta_vieja="select * from tipo_cuenta where numero_cuenta=$def_anterior";
	$resul_cuenta_vieja=sql($sql_cuenta_vieja,"Error listando las cuentas") or fin_pagina();
    $cuenta_vieja=$resul_cuenta_vieja->fields['concepto']." [".$resul_cuenta_vieja->fields['plan']."]";

    $sql_cuenta_nueva="select * from tipo_cuenta where numero_cuenta=".$_POST["cuentas"];
	$resul_cuenta_nueva=sql($sql_cuenta_nueva,"Error listando las cuentas") or fin_pagina();
    $cuenta_nueva=$resul_cuenta_nueva->fields['concepto']." [".$resul_cuenta_nueva->fields['plan']."]";

    
    
    if ($_POST["cuentas"]!=-1){
	    		
		$select_tcuenta = "Select * from cuentas where id_proveedor=$id and numero_cuenta=".$_POST["cuentas"];
		$res_tcuenta = sql($select_tcuenta,"Error consultando las cuentas del proveedor") or fin_pagina();

		if ($res_tcuenta->RecordCount()>0) {//si ya existe
			$updt_tcuenta="update cuentas set es_default = 1 where id_proveedor=$id and numero_cuenta=".$_POST["cuentas"];
        	sql($updt_tcuenta,"Error actualizando el tipo de cuenta para este proveedor") or fin_pagina();
		} else { //si no existe
        	$ins_tcuenta="insert into cuentas (id_proveedor,numero_cuenta,es_default) values ($id,".$_POST["cuentas"].",1)";
        	sql($ins_tcuenta,"Error insertando el tipo de cuenta para este proveedor") or fin_pagina();
		}
		
    	if (($def_anterior!=-1) && ($def_anterior!=$_POST["cuentas"])){
			//quito el anterior
			$updt_tcuenta="update cuentas set es_default = 0 where id_proveedor=$id and numero_cuenta=$def_anterior";
        	sql($updt_tcuenta,"Error actualizando el tipo de cuenta para este proveedor") or fin_pagina();
    		//si tiene default y no es el mismo que el seleccionado envio mail
    		
			/*$sql_cuenta_vieja="select * from tipo_cuenta where numero_cuenta=$def_anterior";
			$resul_cuenta_vieja=sql($sql_cuenta_vieja,"Error listando las cuentas") or fin_pagina();
       		$cuenta_vieja=$resul_cuenta_vieja->fields['concepto']." [".$resul_cuenta_vieja->fields['plan']."]";

			$sql_cuenta_nueva="select * from tipo_cuenta where numero_cuenta=".$_POST["cuentas"];
			$resul_cuenta_nueva=sql($sql_cuenta_nueva,"Error listando las cuentas") or fin_pagina();
       		$cuenta_nueva=$resul_cuenta_nueva->fields['concepto']." [".$resul_cuenta_nueva->fields['plan']."]";*/

    		//$para="corapi@coradir.com.ar,juanmanuel@coradir.com.ar,noelia@coradir.com.ar";
    		//$para="corapi@coradir.com.ar";
    		//$para="broggi@coradir.com.ar,marco@pcpower.com.ar";
    		//$fecha_mail = date("d/m/Y H:i:s",mktime());
    		//$asunto="Se cambio la cuenta por defecto del proveedor ".$_POST["nombre_proveedor"];
    		//$contenido="Fecha: $fecha_mail \nEl usuario ".$_ses_user['name']." cambió la cuenta por defecto '$cuenta_vieja' para el proveedor ".$_POST["nombre_proveedor"]." a la nueva cuenta por defecto '$cuenta_nueva'";
    		//enviar_mail($para,$asunto,$contenido,"","","");
    	}
    }
    $def_anterior = $_POST["cuentas"];
	/*insertamos los tipos del nuevo proveedor...NO BORRAR!!!
    $tipos_prov->Move(0);
       while(!$tipos_prov->EOF)
       {//insertamos el tipo si se selecciono el checkbox correspondiente
        //o eliminamos, si estaba en la BD pero no se checkeo.
        $nombre_check=$tipos_prov->fields["descripcion"];
        $tipo_check=$tipos_prov->fields["tipo"];
        //buscamos a ver si el proveedor tiene el tipo ya cargado
        $query1="select * from prov_t where id_proveedor=".$_POST['select_razon_social']." and tipo='$tipo_check'";
        $tipos_a=$db->Execute("$query1")or die($db->ErrorMsg().$query1);
        $cant_tipo=$tipos_a->RecordCount();
        //si el checkbox esta chequeado, lo insertamos si no existe
        if($_POST[$nombre_check]==$tipo_check)
        {//si no existe en la BD una entrada para este tipo para el proveedor seleccionado
         //, lo insertamos, sino no hacemos nada
         if($cant_tipo==0)
         {$query1="insert into prov_t (id_proveedor,tipo) values(".$_POST['select_razon_social'].",'$tipo_check')";
          $db->Execute("$query1")or die($db->ErrorMsg().$query1);
         }
        }
        else  //y si no esta chequeado y existe, lo eliminamos
        {if($cant_tipo>0)
         {$query1="delete from prov_t where id_proveedor=".$_POST['select_razon_social']." and tipo='$tipo_check'";
          $db->Execute("$query1")or die($db->ErrorMsg().$query1);
         }
        }
        $tipos_prov->MoveNext();
       }
     */
    //cierra transaccion
    $db->CompleteTrans();
    $fecha= date("d/m/Y",mktime());
    $asunto="Se Modificaron los Datos del Proveedor: ".$resul_recupe->fields['razon_social'];
    //$para="noelia@pcpower.com.ar,carlos@coradir.com.ar,juanmanuel@coradir.com.ar,corapi@coradir.com.ar";
    $para="noelia@pcpower.com.ar,carlos@coradir.com.ar,juanmanuel@coradir.com.ar";
    //$para="broggi@coradir.com.ar,marco@pcpower.com.ar";
    $mensaje="Se Modificaron los Datos del Proveedor: ".$resul_recupe->fields['razon_social'] ;
    $mensaje.="\n-------------------------------------------------------------------------------------------------------------";
    $mensaje.="\n                       Datos Anteriores                                              Datos Nuevos            ";
    $mensaje.="\n-------------------------------------------------------------------------------------------------------------";
    //$mensaje.="\nId_proveedor           $next";
    $mensaje.="\nRazon Social           ".$resul_recupe->fields['razon_social']."                ".$_POST['nombre_proveedor'];
    $mensaje.="\nNro. C.U.I.T.          ".$resul_recupe->fields['cuit']."                ".$_POST['text_cuit'];
    $mensaje.="\nNombre Fantasia        ".$resul_recupe->fields['nbre_fantasia']."              ".$_POST['text_fantasia'];
    $mensaje.="\nTipo de I.V.A.         ".$resul_recupe->fields['iva']."                       ".$_POST['text_iva'];
    $mensaje.="\nPolítica RMA.          ".$resul_recupe->fields['politica_rma']."              ".$_POST['politica_rma'];
    $mensaje.="\nCuenta por defecto     ".$cuenta_vieja."              ".$cuenta_nueva;          
    $mensaje.="\n\nLos cambios fueron realizados por el usuario ".$_ses_user['name'].", el día $fecha";
    enviar_mail($para,$asunto,$mensaje,"","","",0);     
 }

if (($_POST["Agregar"] == "Agregar Contacto") && (es_numero($id))) {

$query2="INSERT INTO contactos (id_proveedor,nombre,tel,direccion,provincia,localidad,cod_postal,mail,fax,comentarios,observaciones,icq) VALUES ($id,'".$_POST['nombre']."','".$_POST['tel']."','".$_POST['direccion']."','".$_POST['provincia']."','".$_POST['localidad']."','".$_POST['cod_postal']."','".$_POST['mail']."','".$_POST['fax']."','".$_POST['comentarios']."','".$_POST['observaciones']."','".$_POST['icq']."')";
sql($query2,"No se pudo guardar el contacto") or fin_pagina();

}

echo $html_header;
?>
<!--
<html>
<head>
-->
<?php
include("../ayuda/ayudas.php");
?>
<SCRIPT language='JavaScript' src="funciones.js"></SCRIPT>
<script src="../../lib/funciones.js"></script>
<script languaje="javascript">

//edita el nombre del proveedor en el boton select
function editar_nombre(nombre)
{if(nombre!="")
 {
  nuevo_nombre=prompt('Edite aqui el nombre del Proveedor, presione "Aceptar"',nombre);
  if(nuevo_nombre!=null)
  {document.all.select_razon_social.options[document.all.select_razon_social.selectedIndex].text=nuevo_nombre;
   window.document.all.nombre_editado.value=nuevo_nombre;
  }
 }
}


function check_contacto()
{if(document.all.nombre.value=='')
 {alert('Debe especificar un nombre para el contacto.');
  return false;
 }
 else
  return true;
}

function check_proveedor()
{var check=0;
 var editando=<?=$editando?>;
 var permiso_editar=<?=$permiso_editar?>;
 if(!permiso_editar && editando)
 {alert('Usted no tiene permiso para editar los datos de los proveedores.\n         Comuníquese con la División Software de la empresa.');
  return false;
 }
 if(document.all.select_razon_social.value=='0')
 {alert('Debe seleccionar un proveedor o editar un nuevo nombre antes de agregar o actualizar un proveedor.');
  return false;
 }
 <?/*
 $tipos_prov->Move(0);
 while(!$tipos_prov->EOF)
 {echo"
  if(document.all.".$tipos_prov->fields['descripcion'].".checked==true)
  {check=1;
  }";
  $tipos_prov->MoveNext();
 }*/
 ?>
 /*if(check!=0)
 {alert('Debe seleccionar al menos un tipo para este proveedor');
  return false;
 }*/
 return true;
}

/**********************************************************/
//esta funcion es es distinta de la que esta en funciones.js porque renvia la pagina 
var digitos=10; //cantidad de digitos buscados
var puntero=0;
var buffer=new Array(digitos); //declaración del array Buffer
var cadena="";

function buscar_op_submit(obj){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13) {  
   	   borrar_buffer();
	   form1.action='<?=$_SERVER['SCRIPT_NAME'] ?>';
	   form1.submit();
      }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       //en el indice cero la opcion no es valida
       for (var opcombo=1;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}

</script>


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
<!--
<title>Untitled Document</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
$estilos_select="style='width:100%;height:50px;'";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#E0E0E0"  text="#000000">
-->
<form name="form1" method="post" action="<?php echo $link?>">
<input type="hidden" name="nombre_editado" value="0">
<input type="hidden" name="vio_datos" value="<?=$vio_datos?>">
<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/productos/ayuda_nuevoprov.htm" ?>', 'ALTA DE NUEVOS PROVEDORES')" >
    </div>

<hr>

<div align="center">
<!-- Tabla que me engloba a las dos tablas
es una tabla principal que tiene dos filas
en la primera fila esta el encabezado
en la segunda fila esta divida en dos celdas
en la primera celda esta una tabla con los datos de los proveedores
en la segunda celda esta una tabla con los proveedores existentes
 -->
<table width="100%" align="center" class="bordes" cellpaddindg=2 cellspacing=2>
<tr  bgcolor="<? echo $bgcolor1; ?>">
<td colspan="2" ><font color=<? echo "$bgcolor3"; ?>><center><b>Proveedores</b></center></font>
</td>
</tr>
<tr>
<td valign="top" width="60%" bgcolor=<?=$bgcolor_out?>>
<table border="0" width="100%" heigth="100%">

<tr>
  <td align="right" heigh="15">
   <b>Datos del Proveedor <b>
  </td>
  <td align="right"><input type='submit' name='nuevo' value='Nuevo Proveedor' <?if (isset($parametros["id_prov"])) echo"disabled";?>></td>
</tr>
<tr><td>&nbsp;</td></tr>
<?
//Actualizamos el nombre del proveedor si este fue editado
/*
if(($_POST["nombre_editado"]!="0") && (es_numero($_POST["select_razon_social"]) ))
 {
	 $query5="UPDATE proveedor SET razon_social='".$_POST["nombre_editado"]."' WHERE id_proveedor='".$_POST['select_razon_social']."'";
     $db->Execute("$query5")or die($db->ErrorMsg().$query5);

 }
  */
  if($state=='todos')
   $query="SELECT * FROM proveedor order by razon_social";
  else
   $query="SELECT distinct proveedor.*,prov_t.tipo FROM proveedor join prov_t on proveedor.id_proveedor=prov_t.id_proveedor where activo='true' and prov_t.tipo='$state' order by razon_social";

  $resultados = $db ->Execute("$query") or die($db->ErrorMsg().$query);
//  $resultados1 = $resultados;
  $cantidad_proveedores=$resultados->RecordCount();
  $filas_encontradas = $resultados->RecordCount();

  //para pasar los nombres de proveedores por si edita el nombre de usuario
  $link=encode_link("carga_prov.php",array("pagina"=>$pagina, "cant_filas_mostradas"=> $parametros["cant_filas_mostradas"],"total_prov"=> $parametros["total_prov"],"nro_paginas"=> $parametros["nro_paginas"],"pagina_actual"=> $parametros["pagina_actual"],"texto_buscado"=> $parametros["texto_buscado"],"tipo_prov"=>$state,"id_contacto"=>$_POST["bot_contacto"]));
  //si vino de la pagina proveedores, se debe quedar siempre mostrando por defecto
  //la parte de contactos, por eso, lo siguiente
  if($parametros["pagina"]=="proveedores")
   $link.="#contacto";
//
if ($_POST["select_razon_social"]!=" ") {
echo "<tr bgcolor='#CCCCCC' width='25%'>";
echo "<td> Raz&oacute;n Social: </td>";
echo "<td >";
//parte de fernando
/*
if (es_numero($_POST['select_razon_social'])){
$sql="select razon_social from proveedor where id_proveedor=".$_POST['select_razon_social'];
$resultados_proveedor = $db->execute($sql) or die($sql);
}
*/
 if(es_numero($id)) {
     $query="SELECT * FROM proveedor WHERE id_proveedor = $id";
     $resultados_2 = $db ->Execute("$query") or die($db->ErrorMsg().$query);
     $filas_encontradas = $resultados_2->RecordCount();
  }


 if($_POST["filtro"]!="activado")
 echo "<input type='text' name='nombre_proveedor' value='". $resultados_2->fields['razon_social']."' size='30'>";
 else
echo "<input type='text' name='nombre_proveedor' value='' size='30'>";
//fin de nueva consulta
echo "</td>";
$checked="";
if($resultados_2->fields['activo']=='t')
 $checked="checked";
echo "<td align='center'><input type='checkbox' name='activo' value='activo' $checked> Activo";  

$checked="";
if($resultados_2->fields['calif_prove']=='t')
 $checked="checked";
echo "&nbsp<input type='checkbox' name='calif_prove' value='calif_prove' $checked>Califica Prov.</td></tr>"; 

}  // cierro el if anterior al select.



     ?>
<tr  bgcolor="#CCCCCC">
<td >CUIT:</td>
<td colspan="2">
<?
 if($_POST["filtro"]!="activado")
  echo "<input type='text' name='text_cuit' value='". $resultados_2->fields['cuit']."' size='30'>";
 else
  echo "<input type='text' name='text_cuit' value='' size='30'>"; ?>
</td>
</tr>
<tr  bgcolor="#CCCCCC">
<td >NOMBRE FANTASÍA:</td>
<td colspan="2">
<?
 if($_POST["filtro"]!="activado")
  echo "<input type='text' name='text_fantasia' value='". $resultados_2->fields['nbre_fantasia']."' size='30'>";
 else
  echo "<input type='text' name='text_fantasia' value='' size='30'>"; ?>
</td>
</tr>

<tr  bgcolor="#CCCCCC">
<td >Tipo IVA:</td>
<td colspan="2">
<? /*
  if($_POST["filtro"]!="activado")
   echo "<input type='text' name='text_iva' value='".$resultados_2->fields['iva']."' size='20'>";
  else
   echo "<input type='text' name='text_iva' value='' size='20'>";
   */

?>
 <!-- tipos de iva
Responsable Inscripto.
Responsable No Inscripto.
Exento.
No alcanzado.
Monotributista.
-->
<?
 if($_POST["filtro"]!="activado") {
 ?>

<select name="text_iva" >
  <option selected>
   </option>
  <option <?if ($resultados_2->fields['iva']=="Responsable Inscripto") echo "selected"; ?>>
     Responsable Inscripto
     </option>
  <option <?if ($resultados_2->fields['iva']=="Responsable No Inscripto") echo "selected"; ?>>
      Responsable No Inscripto
    </option>
  <option <?if ($resultados_2->fields['iva']=="Exento") echo "selected"; ?>>
      Exento
  </option>
  <option <?if ($resultados_2->fields['iva']=="No alcanzado") echo "selected"; ?>>
        No alcanzado
   </option>
   <option <?if ($resultados_2->fields['iva']=="Monotributista") echo "selected"; ?>>
        Monotributista
   </option>

</select>
<?  } //del if de activo
    else {
?>

<select name="text_iva" >
  <option selected> </option>
  <option>Responsable Inscripto </option>
  <option>Responsable No Inscripto </option>
  <option >Exento </option>
  <option >No alcanzado   </option>
  <option>Monotributista  </option>
</select>

<?
} //del else
?>


</td>
</tr>
<?
/*GENERACION DE LOS CHECKBOX PARA TIPOS DE PROVEEDORES....NO BORRAR!!  
if(es_numero($id))
  $pro=$id;
 elseif(es_numero($_POST["select_razon_social"]))
  $pro=$_POST["select_razon_social"];
 else
  $pro=0;
 //traemos todos los tipos correspondientes al proveedor seleccionado
 $query="select tipo from prov_t join proveedor on proveedor.id_proveedor=$pro and proveedor.id_proveedor=prov_t.id_proveedor";
 $tipos_prov_sel=$db->Execute($query) or die($db->ErrorMsg().$query);

 $tipos_prov->Move(0);
 $tipos_sel[0]='';
 $i3=0;
 while(!$tipos_prov_sel->EOF)
 {$tipos_sel[$i3]=$tipos_prov_sel->fields['tipo'];
  $tipos_prov_sel->MoveNext();
  $i3++;
 }?>
 <table width="100%" border="0" cellpadding="0" cellspacing="0">
 <tr>
 <?
 //generacion de los checkbox para editar los tipos de proveedores
 $num=0;
 while(!$tipos_prov->EOF)
 {$num++;
  if($num <=4)
  { echo "<td><input type='checkbox' name='".$tipos_prov->fields['descripcion']."' value='".$tipos_prov->fields['tipo']."' "; if(($_POST["filtro"]!="activado")&&(in_array($tipos_prov->fields['tipo'],$tipos_sel))) echo "checked"; echo " > ".$tipos_prov->fields['descripcion']." &nbsp;</td>";
  }
  else
  {echo "</tr><tr><td><input type='checkbox' name='".$tipos_prov->fields['descripcion']."' value='".$tipos_prov->fields['tipo']."' "; if(($_POST["filtro"]!="activado")&&(in_array($tipos_prov->fields['tipo'],$tipos_sel))) echo "checked"; echo " > ".$tipos_prov->fields['descripcion']." &nbsp;</td>";
   $num=0;   
  }	
  $tipos_prov->MoveNext();
 }
echo "</tr></table></td></tr>"*/
?>
<tr bgcolor="#CCCCCC">
<td>
Tipo de cuenta por defecto
</td>
<td colspan="2">
<?

$con="select * from tipo_cuenta order by concepto, plan";
$resul_con=sql($con,"Error listando las cuentas") or fin_pagina();
$cant_resul_con=$resul_con->RecordCount();

echo "<select name='cuentas'>
       <option value=-1> Seleccionar Concepto y Plan </option>";
      for ($j=0; $j<$cant_resul_con; $j++){
      	$cuenta=$resul_con->fields['concepto']."&nbsp;&nbsp;[ ".$resul_con->fields['plan']." ] ";
      	echo "<option value='".$resul_con->fields['numero_cuenta']."'";
      	if($def_anterior==$resul_con->fields['numero_cuenta'])
	  		echo " selected ";
	  	echo"> $cuenta </option>";
	  	$resul_con->MoveNext();
      }
echo "</select>";
?>
</td>
</tr>
<tr bgcolor="#CCCCCC">
<td colspan="3" align="center">
<b>
Observaciones (En que se destaca)
</td>
</tr>
<tr bgcolor="#CCCCCC">
<td  align="center" colspan="3">
<?
if($_POST["filtro"]!="activado")
 echo "<input type='text' name='text_observaciones' value='".$resultados_2->fields['observaciones']."' size='60'>";
else
 echo "<input type='text' name='text_observaciones' value='' size='60'>";
?>
</td>

<tr bgcolor="#CCCCCC">
<td id='mo' colspan="3" align="center">
<b>
Clasificasión de Proveedor
</td>
</tr>

<tr bgcolor="#CCCCCC">
<td colspan="3" align="center">
<b>
<?if ($resultados_2->fields['proveedor_local']== 1) $check_cla="checked"; 
	else $check_cla="";
?>
Proveedor Local: 
<INPUT type=checkbox name="check_1" value="1" <?=$check_cla?>>
<?if ($resultados_2->fields['proveedor_internacional']== 1) $check_cla="checked";
	else $check_cla="";
?>
&nbsp;Proveedor Internacional: 
<INPUT type=checkbox name="check_2" value="1" <?=$check_cla?>>
<?if ($resultados_2->fields['proveedor_pago_servicio']== 1) $check_cla="checked";
	else $check_cla="";
?>

&nbsp;Proveedor Pago y Servicios: 
<INPUT type=checkbox name="check_3" value="1" <?=$check_cla?>>
</td>
</tr>

<tr bgcolor="#CCCCCC">
<td id='mo' colspan="3" align="center">
<b>
Certificación ISO
</td>
</tr>

<tr bgcolor="#CCCCCC">
<td colspan="3" align="center">
<b>
<?if ($resultados_2->fields['iso']== 1) $check_iso="checked"; 
	else $check_iso="";
?>
Certificación ISO: 
<INPUT type=checkbox name="check_iso" value="1" <?=$check_iso?> onclick="(this.checked)?habilitaSubeArch(1) : habilitaSubeArch(0);">&nbsp;&nbsp;

<?$link=encode_link("subir_archivo_iso.php",array("id_prov"=>$resultados_2->fields['id_proveedor']));
     $window=new JsWindow($link,"_blank",450,200);
     $window->center=true;
     $window->maximized=false;
   
 $id_prov_consulta=$resultados_2->fields['id_proveedor'];
 if ($id_prov_consulta){
 	
 $sql = "select * from general.prov_archivos_subidos_iso where id_proveedor=$id_prov_consulta ";
 $resultado_sql=sql($sql) or fin_pagina();
 
 if (!$resultado_sql->EOF) {
 $habilitado="disabled";
 $habilitado_borrar="";
 
 }
 else{
 $habilitado="";
 $habilitado_borrar="disabled";
 }
?>   

<input type="button" name="subir_archivo" value="Subir Archivo" onclick="<?$window->toBrowser();?>" <?=$habilitado?>>
</td>
</tr>

<tr>
	<? if (!$habilitado_borrar=="disabled") {?>
	<td align="center" colspan="3">
	<input name="id_archivo_subido" type="hidden" value="<? echo $resultado_sql->fields["id_prov_archivos_subidos_iso"]; ?>">
	<input name="nombre_archivo_comp" type="hidden" value="<? echo $resultado_sql->fields["nombre_archivo_comp"]; ?>">
	<a title='<?=$resultado_sql->fields["nombre_archivo_comp"]?> [<?=number_format($resultado_sql->fields["filesize_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$resultado_sql->fields["id_prov_archivos_subidos_iso"],"download"=>1,"comp"=>1))?>'>
	<img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0></A>
    <a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$resultado_sql->fields["id_prov_archivos_subidos_iso"],"download"=>1,"comp"=>0))?>'><?=$resultado_sql->fields["nombre_archivo"]?>
    <? echo " (".number_format(($resultado_sql->fields["filesize"]/1024),"2",".","")."Kb)"?></a>
	
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input name="borrar_archivo" type="submit" value="Borrar Archivo" <?=$habilitado_borrar?> onclick="return control_borrar_archivo();">
    </td>
    <?}?>
</tr>	

<?}?>
<tr bgcolor="#CCCCCC">
<td id='mo' colspan="3" align="center">
<b>
Política de RMA
</td>
</tr>
<tr bgcolor="#CCCCCC">
<td  align="center" colspan="3">
<?
if($_POST["filtro"]!="activado")
 echo "<textarea name='politica_rma' rows=4 cols=60>".$resultados_2->fields['politica_rma']."</textarea>";
else
 echo "<textarea name='politica_rma' rows=4 cols=60></textarea>";
?>
</td>
</tr>
<tr>
<td align="right" colspan='2'>
<?/*if(($_ses_user_login=='marcos')||($_ses_user_login=="juanmanuel")||($_ses_user_login=="pablo")||($_ses_user_login=="mariela")||($_ses_user_login=="gonzalo")||($_ses_user_login=="nazabal")||($_ses_user_login=="diego")||($_ses_user_login=="fernando")||($_ses_user_login=="cestila"))
{*/?>
<input type="button" name="tipo_prov" value="Administración de Tipos" style="visibility:hidden" title="Permite agregar/eliminar/modificar los tipos de proveedor" onclick="window.open('admin_tipos.php','','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,left=150,top=95,width=500,height=240');">
<?//}?>
</td>
</tr>
<tr>
<td align="center" colspan="2">
<input type="submit" name="agregar" value="Agregar/Modificar proveedor" <? if (!$permiso_editar) echo "disabled"; ?>>
<?
if($resultados_2) {
	echo "<input type=\"button\" name=\"clasificar\" value=\"Clasificar\" onclick=\"window.location='".encode_link("clasif_prove.php",array("proveedor"=>$resultados_2->fields["id_proveedor"]))."';\">\n";
}
if ($editando) echo "<input type='button'  name='Cerrar' value='Cerrar' OnClick='window.close();'>";
?>

</td>
</tr>
</table>

<!--esta /table cierra la primera tabla que hay en la primera celda-->
</td>

<td bgcolor=<?=$bgcolor_out?>>
<table width="100%">
<?
/*
FILTRO DE TIPOS DE PROVEEDORES....NO BORRAR
echo "<tr>";
echo "<td align='center'>";
echo " Filtrar Por:";
    //traemos los tipos de proveedores para generar el select de tipos de proveedores
    $query="select * from tipos_prov";
    $tipos_prov=$db->Execute($query) or die ($db->ErrorMsg().$query);
    $todos=($state=='todos')?'selected':'';
    ?>
    <select name="tipo_prov" value="est" <? if($id) echo 'disabled';?> onchange="document.all.filtro.value='activado';document.form1.submit()">
    <option value="todos" <?echo $todos?>>Todos</option>
    <?
    while(!$tipos_prov->EOF)
    { if(($state!='todos')&&($state==$tipos_prov->fields['tipo']))
       $selected='selected';
      else
       $selected='';
    ?>
            <option value='<?echo $tipos_prov->fields['tipo']; echo "' ".$selected;?>><? echo $tipos_prov->fields['descripcion'] ?> </option>
     <?
     $tipos_prov->MoveNext();
    }
echo "</select>";
echo "</td>";
echo "</tr>";*/
?>
<tr><td> <b>Seleccionar un proveedor y presionar Enter o Presionar Ver datos </b></td></tr>
<tr>
<td valign="top">

<SELECT size='20' name='select_razon_social'  style='width:250px;font-size:12px;' <?if (!isset($parametros["id_prov"])) echo"onKeypress='buscar_op_submit(this);'"?> onblur="borrar_buffer();"onclick="borrar_buffer();" <?if (isset($parametros["id_prov"])) echo"onchange='this.selectedIndex=rsanterior;'";?> >
<?/*
if (!es_numero($id)) {
echo "<option value='0'>NUEVO PROVEEDOR </option>\n";
}*/
    //generacion del select
//  for($i=0;$i<$filas_encontradas;$i++) {
  for($i=0;$i<$cantidad_proveedores;$i++) {
      // if para dejar seleccionado el ultimo proveedor seleccionado en el select
         //  if($resultados->fields["id_proveedor"] == $_POST['select_razon_social']) {
     if((((es_numero($id))&&($resultados->fields["id_proveedor"] == $id)) || ($resultados->fields["id_proveedor"] == $_POST['select_razon_social']))&&($_POST["filtro"]!="activado")) {
              $string=$resultados->fields["razon_social"];
              $prov_name=$string;
              echo "<option selected value='".$resultados->fields['id_proveedor']."'> $string </option>";
              $resultados->Movenext();
  		 	  echo "<script>var rsanterior = $i;</script>";
          } //fin del then
          else {
              $string=$resultados->fields["razon_social"];
              echo "<option value='".$resultados->fields['id_proveedor']."'> $string </option>";
              $resultados->Movenext();
          } //fin del else
  }
// echo "<option id='editable'> Agregar Nuevo</option>";
echo "</SELECT>";
echo "</td>";
echo "</tr>";
echo "<td align='center'><input type='submit'name='ver_datos' value='Ver Datos'></td>";
echo "</table>";
 //echo "&nbsp;&nbsp;&nbsp;<input type='submit' name='Editar Nombre' value='Editar Nombre'"; if($id) $_POST["select_razon_social"]=$string; echo" onclick='return editar_nombre(document.all.select_razon_social.options[document.all.select_razon_social.selectedIndex].text);'>";

?>
</td>
</tr>
</table>
<!-- esta /table cierra la segunda tabla de la segunda celda -->

</td>
</tr>
</table>

</div>
<?PHP
if(($_POST["Select_razon_social"]!=" ")) {
?>


<div align="center">

<?

if ($_POST["guardar"] == "Guardar Configuración"){

	$moneda=$_POST["moneda"];
	$limite=$_POST["limite"];
	$valor_dolar=$_POST["valor_dolar"];
	$id_pago_st=$_POST["f_pago_est"];
	$id_proveedor = $_POST["id_proveedor"];
	
	$db->StartTrans();
	$sql="select * from general.credito_proveedor left join compras.plantilla_pagos using(id_plantilla_pagos)
	      left join licitaciones.moneda using (id_moneda)
          where id_proveedor=$id_proveedor";
	$result=sql($sql,"Error consultando el credito actual") or fin_pagina();
	$sql="select * from compras.plantilla_pagos where id_plantilla_pagos=".$id_pago_st=$_POST["f_pago_est"];
	$resul=sql($sql,"Error al traer las formas de pago") or fin_pagina();
	$sql="select * from licitaciones.moneda where id_moneda=".$moneda=$_POST["moneda"];
	$mon=sql($sql,"Error al traer las monedas") or fin_pagina();
	$fecha= date("d/m/Y",mktime());
	$asunto_anterior=1;
	if ($result->RecordCount()>0) {//actualizar uno existente
		$sql="update credito_proveedor set id_moneda=$moneda,id_plantilla_pagos=$id_pago_st,limite=$limite,valor_dolar=$valor_dolar where id_proveedor=$id_proveedor";
		if (($result->fields['simbolo']!=$mon->fields['simbolo']) || ($result->fields['limite']!=$_POST['limite']))
		{$asunto="Modificación límite de Crédito del Proveedor ".$_POST['nombre_proveedor'];
		 $mensaje="Se modifico el Límite de Crédito del Proveedor ".$_POST['nombre_proveedor'].". El límite anterior era de: ".$result->fields['simbolo']." ".formato_money($result->fields['limite']).", el límite actual es: ".$mon->fields['simbolo']." ".formato_money($_POST['limite']);
		 $asunto_anterior=0;
		} 
		if ($result->fields['descripcion']!=$resul->fields['descripcion']) 
		   {if ($asunto_anterior) $asunto="Modificación Forma de pago Estándar del Proveedor ".$_POST['nombre_proveedor'];
		   	$asunto="Modificación de Forma de Pago Estándar del Proveedor ".$_POST['nombre_proveedor'];
		    $mensaje.="\nLa forma de pago Estándar era: ".$result->fields['descripcion'].", se cambio por la forma de pago: ".$resul->fields['descripcion'];		
		   }
		$mensaje.="\n\nEl usuario que realizo el cambio fue ".$_ses_user['name']." el día $fecha";
	} else {//insertar uno nuevo
		$sql="insert into credito_proveedor (id_proveedor,id_moneda,id_plantilla_pagos,limite,valor_dolar)
		values ($id_proveedor,$moneda,$id_pago_st,$limite,$valor_dolar)";
		$asunto="Se agrego límite de Crédito para el Proveedor ".$_POST['nombre_proveedor'];
		$mensaje="Se agrego de Límite de Crédito para el Proveedor ".$_POST['nombre_proveedor'].". El límite es de: ".$mon->fields['simbolo']." ".formato_money($_POST['limite']);
		$mensaje.="\nLa forma de pago Estándar es: ".$resul->fields['descripcion'];
		$mensaje.="\n\nEl usuario que realizo el cambio fue ".$_ses_user['name']." el día $fecha";
	}	
	
	$result=sql($sql,"Error en la actualizacion de datos de configuracion del credito del proveedor") or fin_pagina();
	$db->CompleteTrans();
	//$para="noelia@pcpower.com.ar,carlos@coradir.com.ar,juanmanuel@coradir.com.ar,corapi@coradir.com.ar";
	$para="noelia@pcpower.com.ar,carlos@coradir.com.ar,juanmanuel@coradir.com.ar";
	//$para="broggi@coradir.com.ar,marco@pcpower.com.ar";
	enviar_mail($para,$asunto,$mensaje,"","","",0);
}


if($_POST["Eliminar"] == "Eliminar Contacto")
{
    if(es_numero($id))
      $_POST["select_razon_social"]=$id;
	//elimino el contacto de la base de datos.
	$query="delete from contactos where id_proveedor=".$_POST["select_razon_social"]." and id_contacto='".$_POST["id_cont"]."'";
	$db->Execute("$query") or die($db->ErrorMsg()."$query");

}

//Para realizar actualizaciones en los contactos
if ($_POST["Cambiar"] == "Cambiar") {

     //actualizo la base de datos con los cambios realizados
      //$query="UPDATE contactos SET nombre = '".$_POST["nombre"]."',tel='".$_POST["tel"]."',direccion='".$_POST["direccion"]."', provincia='".$_POST["provincia"]."',localidad='".$_POST["localidad"]."',cod_postal='".$_POST["cod_postal"]."' ,mail='".$_POST["mail"]."' ,fax='".$_POST["fax"]."',observaciones='".$_POST["observaciones"]."',icq='".$_POST["icq"]."' WHERE nombre='".$_POST["nombre_cont"]."' and tel='".$_POST["tel_cont"]."' and direccion='".$_POST["dir_cont"]."' and provincia='".$_POST["prov_cont"]."' and localidad='".$_POST["localidad_cont"]."' and cod_postal='".$_POST["cod_postal_cont"]."' and mail='".$_POST["mail_cont"]."' and fax='".$_POST["fax_cont"]."' and observaciones='".$_POST["observ_cont"]."'";
      $query="UPDATE contactos SET nombre = '".$_POST["nombre"]."',tel='".$_POST["tel"]."',direccion='".$_POST["direccion"]."', provincia='".$_POST["provincia"]."',localidad='".$_POST["localidad"]."',cod_postal='".$_POST["cod_postal"]."' ,mail='".$_POST["mail"]."' ,fax='".$_POST["fax"]."',observaciones='".$_POST["observaciones"]."',icq='".$_POST["icq"]."' WHERE id_contacto=".$_POST['id_cont'];
      $db->Execute("$query") or die($db->ErrorMsg().$query);
  }

// Para traer de la base de datos los datos relacionados al contacto elejido por
// el usuario
//si no es el contacto por defecto (solo si lo eligio)
	if ($_POST["id_cont"] && $_POST["bot_contacto"] && $_POST["bot_contacto"]!="Nuevo Contacto")
		$q="select * from contactos where id_contacto=".$_POST["id_cont"];
//sino selecciono un contacto por defecto (el primero)
//	else if ($_POST["select_razon_social"])
	else if ($id)
	{
		$q="select * from contactos where id_proveedor=$id order by id_contacto desc";
		$default_contacto=1;
	}
	
   if ($q) 
   	$resultados_cont = $db ->Execute($q) or die($db->ErrorMsg().$q);

//campos ocultos para no perder los datos anteriores a los cambios
echo "<input type=hidden name='nombre_cont' value='".$resultados_cont->fields["nombre"]."'>";
echo "<input type=hidden name='tel_cont' value='".$resultados_cont->fields['tel']."'>";
echo "<input type=hidden name='dir_cont' value='".$resultados_cont->fields["direccion"]."'>";
echo "<input type=hidden name='prov_cont' value='".$resultados_cont->fields["provincia"]."'>";
echo "<input type=hidden name='localidad_cont' value='".$resultados_cont->fields["localidad"]."'>";
echo "<input type=hidden name='cod_postal_cont' value='".$resultados_cont->fields["cod_postal"]."'>";
echo "<input type=hidden name='mail_cont' value='".$resultados_cont->fields["mail"]."'>";
echo "<input type=hidden name='fax_cont' value='".$resultados_cont->fields["fax"]."'>";
echo "<input type=hidden name='observ_cont' value='".$resultados_cont->fields["observaciones"]."'>";
echo "<input type=hidden name='filtro' value=''>";
echo "<input type='hidden' name='id_cont' value='".$resultados_cont->fields["id_contacto"]."'>";

/*if(((isset($_POST["filtro"]))&&($_POST["filtro"]!="activado"))||($parametros["pagina"]=="proveedores"))*/
if((isset($_POST["select_razon_social"]))||(es_numero($id)))
 {
  if((es_numero($_POST["select_razon_social"]))&&(!es_numero($id))){
  	$query="SELECT DISTINCT nombre,id_contacto FROM contactos WHERE id_proveedor =".$_POST["select_razon_social"];
  	 $resultados = $db ->Execute("$query") or die($db->ErrorMsg().$query);
     $filas_encontradas = $resultados->RecordCount();
  }
  elseif(es_numero($id)){
  	 $query="SELECT DISTINCT nombre,id_contacto FROM contactos WHERE id_proveedor = $id";
      $resultados = $db ->Execute("$query") or die($db->ErrorMsg().$query);
     $filas_encontradas = $resultados->RecordCount();
  }
 }
 else
  $filas_encontradas=0;
 echo "<a name='contacto'></a>";
  if($parametros["pagina"]=="proveedores")
  {
   echo "Proveedor:<b><font color='$bgcolor1'> $prov_name</font> </b><hr>";
  }
//si el proveedor no es nuevo
/*if (($_POST['select_razon_social'] && $_POST['select_razon_social']!=0) || $parametros['pagina']=='muestra_prov') 
{
  cargar_credito($_POST['select_razon_social']); */
if (($id!=0) || $parametros['pagina']=='muestra_prov') 
{
  cargar_credito($id); 
  echo "<table border='0'>";
  echo "<tr>";
// Genero los botones para seleccionar un contacto del proveedor.
   $id_selected= ($default_contacto)?$resultados->fields['id_contacto']:$_POST['id_cont'];
   if ($_POST['bot_contacto']=="Nuevo Contacto")
   	$id_selected=0;
   for($i=0;$i<$filas_encontradas;$i++)
      {
       $cont=$resultados->fields['id_contacto'];
       echo "<td><input type='submit' name='bot_contacto'";
       if ($cont==$id_selected) echo " style='border: thin solid black' ";
       echo  " value='".$resultados->fields['nombre']."' class='boton' style='cursor:hand'  onclick=\"document.all.id_cont.value='$cont'\"></td>";
       $resultados->Movenext();
      }
  echo "<td><input type='submit' name='bot_contacto'";
  if ($id_selected==0)
	echo " style='border: thin solid black' ";  
  echo " value='Nuevo Contacto' style='cursor:hand'></td>";
  echo "<tr>";
  echo "</table>";

// Para traer de la base de datos los datos relacionados al contacto elejido por
// el usuario
/*if((isset($_POST["filtro"]))&&($_POST["filtro"]!="activado"))
{ //$query="SELECT * FROM contactos WHERE nombre = '".$_POST["bot_contacto"]."'";
  $query="select * from contactos where id_contacto=".$_POST['id_cont'];
  $resultados_cont = $db ->Execute("$query") or die($db->ErrorMsg().$query);
  $filas_encontradas = $resultados->RecordCount();
}*/
echo "<table border='0' width='80%'>";
echo "<tr  bgcolor='$bgcolor1'> ";
echo "<td colspan='6' ><font color=$bgcolor3><div align='center'><b>Contacto o Vendedor</b></div></font></TD>";
echo "</tr>";

//echo "<input type='text' name ='prueba' value='".$_POST["select_razon_social"]."'>";

$mostrar_contacto=(($_POST["bot_contacto"]!="")&&($_POST["bot_contacto"]!="Nuevo Contacto") || (($_POST["bot_contacto"]!="Nuevo Contacto") && $resultados_cont->RecordCount()));

?>
<tr  bgcolor="#CCCCCC">
<td>Nombre: </td>
<td colspan="5"><input type="text" name="nombre" value="<?
     if($mostrar_contacto)
      echo $resultados_cont->fields['nombre'];
       ?>" size="25"></td>
</tr>

<tr  bgcolor="#CCCCCC">
<td>E-Mail:</td>
<td colspan="2"><input type="text" name="mail" value="<? if($mostrar_contacto)echo $resultados_cont->fields['mail'];?>" size="25"></td>
<td>ICQ/MSN</td>
<td colspan="2"><input type="text" name="icq" value="<? if($mostrar_contacto)echo $resultados_cont->fields['icq'];?>" size="30"></td>
</tr>
<tr  bgcolor="#CCCCCC">
<td>Telefono:</td>
<td colspan="2"><input type="text" name="tel" value="<? if($mostrar_contacto)echo $resultados_cont->fields['tel'];?>" size="25"></td>
<td>Fax:</td>
<td colspan="2"><input type="text" name="fax" value="<? if($mostrar_contacto)echo $resultados_cont->fields['fax'];?>" size="30"></td>

</tr>


<tr  bgcolor="#CCCCCC">
<td>Direccion:</td>
<td colspan="2"><input type="text" name="direccion" value="<? if($mostrar_contacto)
                        echo $resultados_cont->fields['direccion'];?>" size="25"></td>
<td>Localidad:</td>
<td colspan="2"><input type="text" name="localidad" value="<? if($mostrar_contacto)echo $resultados_cont->fields['localidad'];?>" size="30"></td>

</tr>

<tr  bgcolor="#CCCCCC">
<td>Provincia:</td>
<td colspan="2"><input type="text" name="provincia" value="<? if($mostrar_contacto)echo $resultados_cont->fields['provincia'];?>" size="25"></td>
<td>Codigo Postal:</td>
<td colspan="2"><input type="text" name="cod_postal" value="<? if($mostrar_contacto)echo $resultados_cont->fields['cod_postal'];?>" size="30"></td>
</tr>
</table>
<table border='0' width="80%">
<tr  bgcolor= <? echo $bgcolor1 ?>>
<td><div align='center'><font color=<? echo $bgcolor3 ?>><b> Comentarios:</b></font><div></td>
</tr>
<tr>
<td><textarea name="observaciones" rows="3" cols="100"><? if($mostrar_contacto) echo $resultados_cont->fields['observaciones'];?> </textarea></td>
</tr>
</table>

<?

  echo "<div align='center'>";
  if($mostrar_contacto)//&&(!es_numero($id)))
  {
    echo "<input type='submit' name='Cambiar' value='Cambiar' style='cursor:hand' >&nbsp;";
    echo "<input type='submit' name='Eliminar' value='Eliminar Contacto' style='cursor:hand'>";
    
  }
  else 
  {echo "<INPUT type='submit'  name='Agregar' value='Agregar Contacto' style='cursor:hand'onclick='return check_contacto();'>";
  }
?>   

<!--<INPUT type="button"  name="Volver" value="Volver" OnClick="location.href='<?php //echo $link_prov ?>'" style='cursor:hand'>-->
<input type="hidden" name="seleccion_prov" value="">
<? //si se selecciono el contacto por defecto ?>
<input type="hidden" name="default_cont" value="<?=($_POST['bot_contacto'] && $_POST['bot_contacto']!="Nuevo Contacto" && !$default_contacto)?0:1 ?>">
</div>
<center>
<hr>
<? } //fin si el proveedor no es nuevo ?>

</CENTER>
</div>
<?PHP
}
?>

</form>
</body>
</html>