<?php
/*
$Author: Pablo Rojo

modificado por
$Author: ferni $
$Revision: 1.19 $
$Date: 2007/06/14 18:17:36 $

A PARTIR DEL 04/06/04 ADMINISTRA LA TABLA DE ENTIDADES
EN LUGAR DE LA DE CLIENTES, COMO ERA ANTES

*/

include_once("../../config.php");
include_once("./funciones.php");
require_once("config_pymes.php");

$es_pymes=$_GET["es_pymes"] or $es_pymes=$parametros["es_pymes"] or $es_pymes=$_POST["es_pymes"];

$do=0;
extract($_POST,EXTR_SKIP);
//print_r($_POST);

$onclick['elegir']=$parametros['onclickelegir'] or $onclick['elegir']=$_GET['onclickelegir'] or $onclick['elegir']="pass_data('{$parametros['pagina']}')";

if ($sincronizar){

        $db_pymes->starttrans();

        $sql="select empresas.*,plantas.telefono,plantas.fax,plantas.codigo_postal,plantas.nbre_loc as localidad,
                     plantas.dom_calle,plantas.dom_nro,plantas.dom_piso,
                     provincias.nbre_prov as nombre_provincia,
                     mails.mail
               from empresas
               left join plantas using(id_empresa)
               left join mails using(id_empresa)
               left join  provincias using(id_provincia)
               where sincronizado=0 and plantas.principal=1 and verificado=1 and mails.principal=1";
        $pymes=$db_pymes->execute($sql) or die($db_pymes->errormsg()."<br>".$sql);
        $cantidad_pymes=$pymes->recordcount();
        for($i=0;$i<$cantidad_pymes;$i++){
            $id_empresa=$pymes->fields["id_empresa"];
            $nombre=$pymes->fields["razon_social"];
            $telefono=$pymes->fields["telefono"];
            $fax=$pymes->fields["fax"];
            $direccion=$pymes->fields["dom_calle"]." ".$pymes->fields["dom_nro"]." ".$pymes->fields["dom_piso"];
            $codigo_postal=$pymes->fields["codigo_postal"];
            $localidad=$pymes->fields["localidad"];
            $mail=$pymes->fields["mail"];


            $provincia_pymes=$pymes->fields["nombre_provincia"];

            $sql="select id_distrito from distrito where nombre ilike '%$provincia_pymes%'";
            $distrito=sql($sql) or fin_pagina();
            $id_distrito=$distrito->fields["id_distrito"];
            if (!$id_distrito) $id_distrito=2;

            $sql="select id_entidad from entidad where id_empresa=$id_empresa and id_tipo_entidad=6";
            $res=sql($sql) or fin_pagina();
            if ($res->recordcount())
                              {
                               $id_entidad=$res->fields["id_entidad"];
                               $sql="update entidad set nombre='$nombre', telefono='$telefono',fax='$fax',
                                            direccion='$direccion',codigo_postal='$codigo_postal',localidad='$localidad',
                                            mail='$mail',id_distrito=$id_distrito
                                            where id_entidad=$id_entidad";
                              //modifico
                              }
                              else
                              {

                               $campos="id_empresa,nombre,telefono,fax,direccion,codigo_postal,localidad,mail,id_distritoid_tipo_entidad";
                               $values="$id_empresa,'$nombre','$telefono','$fax','$direccion','$codigo_postal','$localidad','$mail',$id_distrito,6";
                               $sql="insert into entidad ($campos) values ($values)";
                               $sql_array[]=$sql;
                              }
         $sql="update empresas set sincronizado=1 where id_empresa=$id_empresa";
         //$db_pymes->execute($sql) or die($sql);
         $pymes->movenext();
        }//del for
        if (sizeof($sql_array)){
                     print_r($sql_array);
                    //sql($sql_array,"Error al sincronizar los datos") or fin_pagina();
                   }


      $db_pymes->completetrans();

}



if ($es_pymes) $tabla=" entidad_pymes";
          else $tabla=" entidad";

if (($boton=="Guardar")&&($editar!="editar"))
	   $do=1;
       elseif ($boton=="Eliminar")
	           $do=2;
               elseif($editar=="editar")
               $do=3;
$inserto="";

if ($_POST["activo_entidad"]==on)$activo_entidad=1;
else $activo_entidad=0;

if ($do==1)
   {
   $campos="(nombre,direccion,id_tipo_entidad,id_clasificacion_entidad,codigo_postal,localidad,cuit,contacto,telefono,mail,perfil,fax,iib,id_iva,id_condicion,id_distrito,observaciones,activo_entidad)";
   if($select_tipo_entidad==-1)
              $select_tipo_entidad="null";
   if($select_clas_entidad==-1)
              $select_clas_entidad="null";
   if($select_tasa_iva==-1)
              $select_tasa_iva="null";
   if($select_condicion_iva==-1)
              $select_condicion_iva="null";
   if($distrito==-1)
              $distrito="null";

   $query_insert="INSERT INTO $tabla $campos VALUES ".
	              "('$nombre','$direccion',$select_tipo_entidad,$select_clas_entidad,'$cod_pos','$localidad','$cuit','$contacto','$telefono','$mail','$perfil','$fax','$iib',$select_tasa_iva,$select_condicion_iva,$distrito,'$observaciones',$activo_entidad)";

	if (sql($query_insert))
           $informar="<center><b>La entidad \"$nombre\" fue añadida con éxito</b></center>";
           else
	       $informar="<center><b>La entidad \"$nombre\" no se pudo agregar</b></center>";
    }
     elseif ($do==3)
            {
	         if($select_tipo_entidad==-1)
                           $select_tipo_entidad="null";
             if($select_clas_entidad==-1)
                           $select_clas_entidad="null";
             if($select_tasa_iva==-1)
                           $select_tasa_iva="null";
             if($select_condicion_iva==-1)
                           $select_condicion_iva="null";
             if($distrito==-1)
                           $distrito="null";

            $query="update $tabla set nombre='$nombre',direccion='$direccion',codigo_postal='$cod_pos',localidad='$localidad', id_clasificacion_entidad=$select_clas_entidad,activo_entidad=$activo_entidad,
                    contacto='$contacto',telefono='$telefono',mail='$mail',perfil='$perfil',observaciones='$observaciones',fax='$fax',cuit='$cuit',
                    iib='$iib',id_iva=$select_tasa_iva,id_distrito=$distrito,id_condicion=$select_condicion_iva,id_tipo_entidad=$select_tipo_entidad
                    ";
                    if (!$es_pymes) $query.=" WHERE id_entidad=$select_entidad";
                             else   $query.=" WHERE id_entidad_pyme=$select_entidad";
            //echo $query;
            if (sql($query) or fin_pagina())
	                       $informar="<center><b>La entidad \"$nombre\" fue actualizada con éxito</b></center>";
                           else
	                       $informar="<center><b>La entidad \"$nombre\" no se pudo actualizar</b></center>";
 }

//datos por parametros
$id_entidad=$parametros['id_entidad'];
$pagina=$parametros['pagina'];

//$tipo es una parte nueva
//si no pasa nada se traen todas las entidades menos las pymes
//si tipo trae algo se traen las pymes unicamente
$pymes=$parametros["pymes"];

$link=encode_link("nuevo_cliente.php",array("id_entidad"=>$id_entidad,"pagina"=>$pagina,"pymes"=>$pymes));

//Parte para trabajar con sesiones
if ($_POST['filtro']) {
    $itemspp=$_POST['filtro'];
    phpss_svars_set("_ses_cliente",$itemspp);
    }
    else
    $itemspp=$_ses_cliente or $itemspp=50;


if (!$_POST['filtro']) {
            if (!$_ses_cliente)
                          $filtro='a';
                          else
                          $filtro=$_ses_cliente;
            }
            else
               $filtro=$_POST['filtro'];


if ($_ses_cliente != $filtro) {
 phpss_svars_set("_ses_cliente",$filtro);
}

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

if ($filtro=="Todos") $filtro="";

//   $sql="select * from general.clientes order by nombre";
if (!$pymes) $condicion=" and id_tipo_entidad<>6";
if ($pymes)  $condicion=" and id_tipo_entidad=6";
$condicion="";

if (!$es_pymes)
              {
             $sql="select e.id_entidad,e.nombre,e.telefono,e.codigo_postal,e.fax,e.direccion,e.activo_entidad,
                   id_clasificacion_entidad,
                       e.localidad,e.observaciones,e.mail,e.perfil,e.id_tipo_entidad,
                       e.id_distrito,e.cuit,e.contacto,e.iib ,e.id_condicion ,e.id_iva
                       from licitaciones.entidad e
                       left join licitaciones.tipo_entidad using(id_tipo_entidad)
                       where e.nombre ilike '$filtro%' $condicion order by nombre";
              }
              else{
              $sql="select  e.id_entidad_pyme,e.nombre,e.telefono,e.codigo_postal,e.fax,e.direccion,e.activo_entidad,
                       e.localidad,e.observaciones,e.mail,e.perfil,e.id_tipo_entidad,
                       e.id_distrito,e.cuit,e.contacto,e.iib ,e.id_condicion ,e.id_iva
                       from pymes.entidad_pymes e
                       left join licitaciones.tipo_entidad using(id_tipo_entidad)
                       where e.nombre ilike '$filtro%'  order by nombre";

              }

$datos_entidad=sql($sql) or fin_pagina();
//echo "cantidad de datos".$datos_entidad->recordcount();
//die();
?>
<SCRIPT LANGUAGE="JavaScript">
<?php
//A PARTIR DE AHORA LEASE ENTIDADES DONDE SE LEE CLIENTES PORQUE
//TOMA TODA LA INFORMACION DESDE LA TABLA ENTIDADES.

while (!$datos_entidad->EOF)
{
if ($es_pymes) $id_entidad=$datos_entidad->fields["id_entidad_pyme"];
       else    $id_entidad=$datos_entidad->fields["id_entidad"];

$nombre=$datos_entidad->fields["nombre"];
$nombre=ereg_replace("\r\n","<br>",$nombre);
$nombre=ereg_replace("\n","<br>",$nombre);
?>
var entidad_<?=$id_entidad?>=new Array();
entidad_<?=$id_entidad?>["nombre"]="<?php if($nombre){echo $nombre;}else echo "null";?>";
entidad_<?=$id_entidad?>["id_tipo_entidad"]="<?php if($datos_entidad->fields["id_tipo_entidad"]){echo $datos_entidad->fields["id_tipo_entidad"];}else echo "null";?>";
<? if (!$es_pymes) { ?>
entidad_<?=$id_entidad?>["id_clas_entidad"]="<?php if($datos_entidad->fields["id_clasificacion_entidad"]){echo $datos_entidad->fields["id_clasificacion_entidad"];}else echo "null";?>";
<?}?>
entidad_<?=$id_entidad?>["direccion"]="<?php if($datos_entidad->fields["direccion"]){echo $datos_entidad->fields["direccion"];}else echo "null";?>";
entidad_<?=$id_entidad?>["cod_pos"]="<?php if($datos_entidad->fields["codigo_postal"]){echo $datos_entidad->fields["codigo_postal"];}else echo "null";?>";
entidad_<?=$id_entidad?>["localidad"]="<?php if($datos_entidad->fields["localidad"]){echo $datos_entidad->fields["localidad"];}else echo "null";?>";
entidad_<?=$id_entidad?>["distrito"]="<?php if($datos_entidad->fields["id_distrito"]){echo $datos_entidad->fields["id_distrito"];}else echo "null";?>";
entidad_<?=$id_entidad?>["cuit"]="<?php if($datos_entidad->fields["cuit"]){echo $datos_entidad->fields["cuit"];}else echo "null";?>";
entidad_<?=$id_entidad?>["contacto"]="<?php if($datos_entidad->fields["contacto"]){echo $datos_entidad->fields["contacto"];}else echo "null";?>";
entidad_<?=$id_entidad?>["telefono"]="<?php if($datos_entidad->fields["telefono"]){echo $datos_entidad->fields["telefono"];}else echo "null";?>";
entidad_<?=$id_entidad?>["fax"]="<?php if($datos_entidad->fields["fax"]){echo $datos_entidad->fields["fax"];}else echo "null";?>";
entidad_<?=$id_entidad?>["mail"]="<?php if($datos_entidad->fields["mail"]){echo $datos_entidad->fields["mail"];}else echo "null";?>";
entidad_<?=$id_entidad?>["iib"]="<?php if($datos_entidad->fields["iib"]){echo $datos_entidad->fields["iib"];}else echo "null";?>";
entidad_<?=$id_entidad?>["id_condicion"]="<?php if($datos_entidad->fields["id_condicion"]){echo $datos_entidad->fields["id_condicion"];}else echo "null";?>";
entidad_<?=$id_entidad?>["id_iva"]="<?php if($datos_entidad->fields["id_iva"]){echo $datos_entidad->fields["id_iva"];}else echo "null";?>";
entidad_<?=$id_entidad?>["activo_entidad"]="<?php if($datos_entidad->fields["activo_entidad"]){echo $datos_entidad->fields["activo_entidad"];}else echo "null";?>";
<?
$perfil=$datos_entidad->fields["perfil"];
$perfil=ereg_replace("\r\n","<br>",$perfil);
$perfil=ereg_replace("\n","<br>",$perfil);
?>
entidad_<?=$id_entidad?>["perfil"]="<?php if($perfil){echo $perfil;}else echo "null";?>";
<?
$observaciones=$datos_entidad->fields["observaciones"];
$observaciones=ereg_replace("\r\n","<br>",$observaciones);
$perfil=ereg_replace("\n","<br>",$perfil);
?>
entidad_<?=$id_entidad?>["observaciones"]="<?php if($observaciones){echo $observaciones;}else echo "null";?>";

<?
$datos_entidad->MoveNext();
}
?>

function set_datos()
{
    /*switch(document.all.select_entidad.options[document.all.select_entidad.selectedIndex].value)
    {<?PHP
     $datos_entidad->Move(0);
     while(!$datos_entidad->EOF)
     {
     if ($es_pymes) $id_entidad=$datos_entidad->fields["id_entidad_pyme"];
           else    $id_entidad=$datos_entidad->fields["id_entidad"];

     ?>
      case '<?=$id_entidad?>': info=entidad_<?=$id_entidad?>;break;
     <?
      $datos_entidad->MoveNext();
     }
     ?>
    }*/
   	for (i=0; i< document.all.select_entidad.length; i++){
   		if (document.all.select_entidad.options[i].selected){
		    switch(document.all.select_entidad.options[document.all.select_entidad.selectedIndex].value){<?
    			$datos_entidad->Move(0);
		    	while(!$datos_entidad->EOF){
						if ($es_pymes) $id_entidad=$datos_entidad->fields["id_entidad_pyme"];
           	else    $id_entidad=$datos_entidad->fields["id_entidad"];
	     	?>
  		    case '<?=$id_entidad?>': info=entidad_<?=$id_entidad?>;break;
     		<?
      			$datos_entidad->MoveNext();
     			}
     		?>
    		}
   		}
   	}

    if(info["nombre"]!="null")
            document.all.nombre.value=info["nombre"];
            else
            document.all.nombre.value="";
    if(info["id_tipo_entidad"]!="null")
            document.all.select_tipo_entidad.value=info["id_tipo_entidad"];
            else
            document.all.select_tipo_entidad.value=-1;

    if(info["id_clas_entidad"]!="null")
            document.all.select_clas_entidad.value=info["id_clas_entidad"];
            else
            document.all.select_clas_entidad.value=-1;
    if(info["direccion"]!="null")
            document.all.direccion.value=info["direccion"];
            else
            document.all.direccion.value="";
    if(info["cod_pos"]!="null")
            document.all.cod_pos.value=info["cod_pos"];
            else
            document.all.cod_pos.value="";
    if(info["localidad"]!="null")
            document.all.localidad.value=info["localidad"];
            else
            document.all.localidad.value="";
    if(info["distrito"]!="null")
            document.all.distrito.value=info["distrito"];
            else
            document.all.distrito.value=-1;
    if(info["cuit"]!="null")
           document.all.cuit.value=info["cuit"];
           else
           document.all.cuit.value="";
    if(info["contacto"]!="null")
           document.all.contacto.value=info["contacto"];
           else
           document.all.contacto.value="";
    if(info["telefono"]!="null")
           document.all.telefono.value=info["telefono"];
           else
           document.all.telefono.value="";
    if(info["fax"]!="null")
           document.all.fax.value=info["fax"];
           else
           document.all.fax.value="";
    if(info["mail"]!="null")
          document.all.mail.value=info["mail"];
          else
          document.all.mail.value="";
    if(info["iib"]!="null")
          document.all.iib.value=info["iib"];
           else
           document.all.iib.value="";

    if(info["id_condicion"]!="null")
          document.all.select_condicion_iva.value=info["id_condicion"];
          else
          document.all.select_condicion_iva.value=-1;

    if(info["id_iva"]!="null")
         document.all.select_tasa_iva.value=info["id_iva"];
         else
         document.all.select_tasa_iva.value=-1;
    if(info["perfil"]!="null")
         document.all.perfil.value=info["perfil"];
         else
         document.all.perfil.value="";
    if(info["observaciones"]!="null")
        document.all.observaciones.value=info["observaciones"];
        else
        document.all.observaciones.value="";

    if(info["activo_entidad"]!="null")
        document.all.activo_entidad.checked=true;
        else
        document.all.activo_entidad.checked=false;

    if(info["activo_entidad"]!="null"){
    	document.forms[0].elegir.disabled=false;
    	document.all.msg_elegir_cliente.innerText=''
    }
    	else{
	   	document.forms[0].elegir.disabled=true;
	   	document.all.msg_elegir_cliente.innerText='Debe estar Activo el Cliente para Elegir'
    	}


   document.all.editar.value="editar";
   document.all.asoc_a_lic.disabled=false;
   document.all.ent.value=document.all.select_entidad.value;
   document.all.ent_nombre.value=info["nombre"];

} //fin de la funcion set_datos()



//funcion que chequea que no se hayan puesto caracteres del tipo comillas dobles (")
//para que no salte un error de JavaScript...ver Bd de errores para mas info
function control_datos()
{

	if  (document.all.select_tipo_entidad.options[document.all.select_tipo_entidad.selectedIndex].value==-1){
		alert('Debe elegir tipo de entidad');
		return false;
	}
	if  (document.all.distrito.options[document.all.distrito.selectedIndex].value==-1){
		alert('Debe elegir el distrito');
		return false;
	}
    if  (document.all.select_clas_entidad.options[document.all.select_clas_entidad.selectedIndex].value==-1){
		alert('Debe elegir el clasificación de la entidad');
		return false;
	}

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
    if(document.all.localidad.value.indexOf('"')!=-1)
      {
       alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Localidad');
       return false;
       }
    if(document.all.direccion.value.indexOf('"')!=-1)
       {
         alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Dirección');
         return false;
       }
    if(document.all.mail.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo E-mail');
        return false;
    }
    if(document.all.contacto.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Nombre del contacto');
        return false;
    }
    if(document.all.telefono.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Teléfono');
        return false;
    }
    if(document.all.fax.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Fax');
        return false;
    }
    if(document.all.iib.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo I.I.B. del contacto');
        return false;
    }
    if(document.all.perfil.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Perfil');
        return false;
    }
    if(document.all.observaciones.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Observaciones');
        return false;
    }
    if(document.all.cod_pos.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Código Postal');
        return false;
    }
    if(document.all.cuit.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Código Postal');
        return false;
    }
    if(document.all.contacto.value.indexOf('"')!=-1)
    {   alert('Ha ingresado un caracter no permitido: evite ingresar comillas dobles (") en el campo Contacto');
        return false;
    }

} //fin de la funcion control_datos()

//funcion para limpiar el formulario
function limpiar()
{document.all.nombre.value='';
 document.all.direccion.value='';
 document.all.cod_pos.value='';
 document.all.localidad.value='';
 document.all.cuit.value='';
 document.all.contacto.value='';
 document.all.telefono.value='';
 document.all.fax.value='';
 document.all.mail.value='';
 document.all.iib.value='';
 document.all.observaciones.value='';
 document.all.perfil.value='';
 document.all.distrito.value=-1;
 document.all.select_tasa_iva.value=-1;
 document.all.select_clas_entidad.value=-1;
 document.all.select_condicion_iva.value=-1;
 document.all.select_tipo_entidad.value=-1;
 document.all.editar.value='';
 document.all.activo_entidad.checked=true;
}

//funcion para setear los valores de los campos de la entidad seleccionada, en
//la pagina de factura o remito
function pass_data(pagina)
{

 if (pagina=='presupuestos')
 {
  window.opener.document.all.mod_entidad.value=document.all.select_entidad.value;
  window.opener.document.all.mod_nombre_cliente.value=document.all.nombre.value;
  window.opener.document.all.mod_dir.value=document.all.direccion.value;
  window.close();
}
 if (pagina=='presupuestos_new') {

  window.opener.document.all.new_entidad.value=document.all.select_entidad.value;
  window.opener.document.all.new_nombre_cliente.value=document.all.nombre.value;
  window.opener.document.all.new_dir.value=document.all.direccion.value;
  window.opener.document.all.dir_cambio.value=1;
  window.close();

 }


 if (pagina=='caso_estadisticas'){
 	/////////////////// agregado por Gabriel ///////////////////
 	// permite selección múltiple de clientes y devuelva una lista de ids y nombres separados por ";"
 	str=new String('');
 	ids=new Array();

 	for (i=0, j=0; i<document.all.select_entidad.length; i++){
 		if (document.all.select_entidad.options[i].selected){
 			str+=document.all.select_entidad.options[i].text+'; ';
 			ids[j++]=document.all.select_entidad.options[i].value;
 		}
 	}
 	window.opener.document.all.nombre_cliente.value=str.substring(0, str.length-2);
 	window.opener.document.all.id_entidad.value=ids.join(";");
 	/////////////////////////////////////////////////////////
  //window.opener.document.all.id_entidad.value=document.all.select_entidad.value;
  //window.opener.document.all.nombre_cliente.value=document.all.nombre.value;

 }

 if(pagina=='detalle_movimiento')
 {
 	 window.opener.document.all.id_entidad.value=document.all.select_entidad.value;
	 window.opener.document.all.cliente.value=document.all.nombre.value;
	 window.close();
 }

 if (pagina=='altas_cheque_dif')
 {
 window.opener.document.all.cliente.value=document.all.nombre.value;
 window.opener.document.all.id_cliente.value=document.all.select_entidad.value;
 }

 if (pagina=='altas_cheque_dif_atadas') {

	     i=window.opener.document.all.num_fila.value;
		 nom=eval("window.opener.document.all.cliente_"+i);
		 id=eval("window.opener.document.all.id_cliente_"+i);
		 nom.value=document.all.nombre.value;
		 id.value=document.all.select_entidad.value;
 }

 if(pagina=='remitos' || pagina=='facturas')
 {
  window.opener.document.all.id_entidad.value=document.all.select_entidad.value;
  window.opener.document.all.nbre.value=document.all.nombre.value;
  window.opener.document.all.nbre.disabled=0;
  window.opener.document.all.dir.value=document.all.direccion.value;
  window.opener.document.all.dir.disabled=0;
  window.opener.document.all.cuit.value=document.all.cuit.value;
  window.opener.document.all.cuit.disabled=0;
  //si tiene iva, lo pasamos, sino no, pasamos un 0
  if(document.all.select_tasa_iva.value!=-1)
   window.opener.document.all.iva.value=document.all.select_tasa_iva.options[document.all.select_tasa_iva.selectedIndex].text;
  else
    window.opener.document.all.iva.value="0";
  window.opener.document.all.iva.disabled=0;
  //si tiene condicion de iva, lo pasamos, sino no, pasamos el string "Debe cargarse"
  if(document.all.select_condicion_iva.value!=-1)
    window.opener.document.all.condicion_iva.value=document.all.select_condicion_iva.options[document.all.select_condicion_iva.selectedIndex].text;
  else
    window.opener.document.all.condicion_iva.value="Debe cargarse";
  window.opener.document.all.condicion_iva.disabled=0;
  window.opener.document.all.iib.value=document.all.iib.value;
  window.opener.document.all.iib.disabled=0;
// no se corresponde con las observaciones
//  window.opener.document.all.otros.value=document.all.observaciones.value;
  window.opener.document.all.otros.disabled=0;
 }
}

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

function editar_campos(boolvalue)
{
	boolvalue=!boolvalue;
	document.forms[0].nombre.readOnly=boolvalue;
	if (!boolvalue) {document.forms[0].nombre.focus();document.forms[0].nombre.select()};
	document.forms[0].select_tipo_entidad.readOnly=boolvalue;
	document.forms[0].select_clas_entidad.readOnly=boolvalue;
	document.forms[0].direccion.readOnly=boolvalue;
	document.forms[0].cod_pos.readOnly=boolvalue;
	document.forms[0].localidad.readOnly=boolvalue;
	document.forms[0].distrito.readOnly=boolvalue;
	document.forms[0].cuit.readOnly=boolvalue;
	document.forms[0].contacto.readOnly=boolvalue;
	document.forms[0].telefono.readOnly=boolvalue;
	document.forms[0].fax.readOnly=boolvalue;
	document.forms[0].mail.readOnly=boolvalue;
	document.forms[0].iib.readOnly=boolvalue;
	document.forms[0].select_tasa_iva.readOnly=boolvalue;
	document.forms[0].select_condicion_iva.readOnly=boolvalue;
	document.forms[0].observaciones.readOnly=boolvalue;
	document.forms[0].perfil.readOnly=boolvalue;
	document.forms[0].select_entidad.readOnly=boolvalue;
	document.forms[0].activo_entidad.disabled=boolvalue;
	<?if (permisos_check("inicio","permiso_editar_cliente")) {?>
	   document.forms[0].boton.disabled=false;
	<?}?>

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
<?$link=encode_link('nuevo_cliente.php',array('pagina'=>$parametros['pagina'],'pymes'=>$parametros["pymes"],"onclickelegir"=>$onclick['elegir']));

  $link_sinc=encode_link('sincronizar_entidades_pymes.php',array());
  $link_lic_asoc=encode_link('asociado_a_lic.php',array('id_entidad'=>"nada"));
?>
<form name="form" method="post" action="<?=$link?>">
<input type=button name=sincronizar value=Sincronizar onclick="window.open('<?=$link_sinc?>')" title="Sincroniza con Sistema PyME">
<input type="button" name="asoc_a_lic" value="Asociado a Lic." disabled onclick="window.open('<?=$link_lic_asoc?>')" title="Reporte si la entidad esta vinculada a licitacion">
<input type="hidden" name="ent" value="">
<input type="hidden" name="ent_nombre" value="">
<input type="hidden" name="editar" value="<?if($do!=0 && $do!=2 && $do!=3)echo "editar"?>">
<input type="hidden" name="es_pymes" value="<?=$es_pymes?>">
  <TABLE width="100%" align="center" border="0" cellspacing="2" cellpadding="0">
    <tr id=mo>
      <td width="40%" align="center"><strong>INFORMACION DE LA ENTIDAD</strong>

      </td>
      <td width="60%" height="20" align="center" >
      <strong>ENTIDADES CARGADAS</strong>
       </td>
    </tr>
    <tr>
      <td align="center">
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
           <?
           if (!$es_pymes) {
           ?>
           <input type="button" name="nuevo" value="Nuevo" title="Limpia el formulario, para agregar una nueva entidad" onclick="limpiar();editar_campos(1); if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=1;">
           <?
           }
           ?>
           </td>
          </tr>
          </table>
         </td>
        </tr>
        <tr>
          <td width="20%" nowrap><strong>Nombre</strong></td>
          <td width="70%" nowrap> <input name="nombre" type="text" id="nombre" size="35" readonly value="<?//if($do!=2)echo $_POST['nombre']?>"></td>
        </tr>
        <tr>
           <td width="20%" nowrap><strong>Tipo Entidad</strong></td>
            <td width="70%" nowrap>
            <?
            //traemos los tipos de entidad posibles
            $query="select id_tipo_entidad,nombre from tipo_entidad order by nombre";
            $tipo_entidad=sql($query) or fin_pagina();
            ?>
            <select name="select_tipo_entidad" onclick="readonly()" >
             <option value=-1>Seleccione Tipo de Entidad</option>
             <?
             while(!$tipo_entidad->EOF)
             {
             ?>
              <option value="<?=$tipo_entidad->fields['id_tipo_entidad']?>"><?=$tipo_entidad->fields['nombre']?></option>
             <?
              $tipo_entidad->MoveNext();
             }
             ?>
            </select>
           </td>
          </tr>
             <tr>
           <td width="20%" nowrap><strong>Clasificación Entidad</strong></td>
            <td width="70%" nowrap>
            <?
            //traemos los tipos de entidad posibles
            $query="select id_clasificacion_entidad,descripcion from clasificacion_entidad order by descripcion";
            $clas_entidad=sql($query) or fin_pagina();
            ?>
            <select name="select_clas_entidad" onclick="readonly()">
             <option value=-1>Seleccione Clasificación de Entidad</option>
             <?
             while(!$clas_entidad->EOF)
             {
             ?>
              <option value="<?=$clas_entidad->fields['id_clasificacion_entidad']?>"><?=$clas_entidad->fields['descripcion']?></option>
             <?
              $clas_entidad->MoveNext();
             }
             ?>
            </select>
           </td>
          </tr>
          <tr>
            <td  nowrap><strong>Dirección</strong></td>
            <td nowrap> <input name="direccion" type="text" id="direccion" readonly size="30" value="<?//if($do!=2)echo$_POST['direccion']
            ?>"></td>
          </tr>
          <tr>
            <td  nowrap> <strong>Código Postal</strong></td>
            <td nowrap> <input name="cod_pos" type="text" id="cod_pos" readonly size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Localidad</strong></td>
            <td nowrap> <input name="localidad" type="text" id="localidad" readonly size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Distrito</strong></td>
            <td>
            <?
            //traemos los distritos de la BD
            $query="select nombre,id_distrito from distrito order by nombre";
            $distritos=sql($query) or fin_pagina();
            ?>
            <SELECT name="distrito" onclick="readonly()">
             <option value=-1>Seleccione un Distrito</option>
            <?
            while(!$distritos->EOF)
            {?>
             <option value="<?=$distritos->fields['id_distrito']?>" <?if($_POST['distrito']==$distritos->fields['id_distrito'])echo "selected"?>><?=$distritos->fields['nombre']?></option>
             <?
             $distritos->MoveNext();
            }
            ?>
            </select>
            </td>


          </tr>
          <tr>
            <td  nowrap><strong>CUIT</strong></td>
            <td nowrap> <input name="cuit" type="text" readonly id="cuit" size="30"></td>

          </tr>
          <tr>
            <td  nowrap><strong>Nombre del Contacto</strong></td>
            <td nowrap> <input name="contacto" type="text" readonly id="contacto" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Teléfono</strong></td>
            <td nowrap> <input name="telefono" type="text" readonly id="telefono" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>Fax</strong></td>
            <td nowrap> <input name="fax" type="text" readonly id="fax" size="30"></td>
          </tr>
          <tr>
            <td  nowrap><strong>E-mail</strong></td>
            <td nowrap> <input name="mail" type="text" readonly id="mail" size="30"></td>
          </tr>

          <tr>
            <td  nowrap><strong>Nº I.I.B.</strong></td>
            <td nowrap> <input name="iib" type="text" readonly id="iib" size="30"></td>
          </tr>

           <tr>
            <td  nowrap><strong>Tasa IVA</strong></td>
            <td>
             <?php
              $query="SELECT * from tasa_iva";
              $resultado = sql($query) or fin_pagina();
              $filas_encontradas=$resultado->RecordCount();
             ?>
              <select name='select_tasa_iva' onclick="readonly()">
               <option value=-1>Seleccione</option>
             <?
              for($i=0;$i<$filas_encontradas; $i++){
                  $string=$resultado->fields['porcentaje'];
                  $valor=$resultado->fields['id_iva'];
                  echo "<option value='$valor' ";if($_POST['select_tasa_iva']=="$valor"){echo "selected";}echo">$string</option>";
                  $resultado->MoveNext();
              }
              ?>
              </select>
              
              <strong>
      			Activo:&nbsp;&nbsp;&nbsp;<INPUT name="activo_entidad" type=checkbox disabled id="activo_entidad">
      		  </strong>
            </td>
           </tr>
           <tr>
              <td  nowrap><strong>Condición IVA</strong></td>
              <td>
               <?php
              $query="SELECT * from condicion_iva";
              $resultado = $db->Execute($query) or die($db->ErrorMsg());
              $filas_encontradas=$resultado->RecordCount();
              ?>
              <select name='select_condicion_iva' onclick="readonly()">
               <option value=-1>Seleccione</option>
              <?
              for($i=0;$i<$filas_encontradas; $i++){
                  $string=$resultado->fields['nombre'];
                  $valor=$resultado->fields['id_condicion'];
                  echo "<option value='$valor' ";if($_POST['select_condicion_iva']=="$valor"){echo "selected";}echo">$string</option>";
                  $resultado->MoveNext();
              }
              echo "</select>";
              echo "</td>";
           echo "</tr>";
             ?>
          <tr>
            <td colspan="2" nowrap width="100%"><strong>Observaciones</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap width="100%">
                <textarea name="observaciones" rows="1" cols="52" readonly wrap="VIRTUAL" id="observaciones"></textarea>

            </td>
          </tr>
          <tr>
            <td colspan="2" nowrap><strong>Perfil</strong></td>
          </tr>
          <tr>
            <td colspan="2" nowrap>
                <textarea name="perfil" rows="1" cols="52" readonly wrap="VIRTUAL" id="perfil"></textarea>

            </td>
          </tr>      	

        </table>
      </td> <!--  En esta celda van todas las entidades -->
      <td align="center" >
        <TABLE width="100%">
             <tr>
               <td>
                <?tabla_filtros_nombres($link);?>
               </td>
             </tr>
             <tr>
               <td>
<?/*<<<<<<< nuevo_cliente.php
                    <select name="select_entidad" size="30" style="width:98%" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="editar_campos(0);if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=0;borrar_buffer()">
                     <!--<select name="select_cliente" size="10" style="width:576"  ondblclick="<?=$onclickaceptar?>" onkeypress="if(event.keyCode==13){<?=$onclickaceptar?>}"
onKeypress="buscar_op(this);
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
onchange=
"if (this.selectedIndex!=-1 && aceptar.disabled)
	aceptar.disabled=0;
	document.all.nbrecl.disabled=0;
	email.disabled=0;
	direccion.disabled=0;
	telefono.disabled=0;
 direccion.value=eval('document.all.direccion_'+ this[this.selectedIndex].value +'.value');
 telefono.value=eval('document.all.telefono_'+ this[this.selectedIndex].value +'.value');
 email.value=eval('document.all.email_'+ this[this.selectedIndex].value +'.value');
 nbrecl.value=eval('document.all.nbrecl_'+ this[this.selectedIndex].value +'.value');
">-->
=======*/?>
               	<?
               		if ($pagina=="caso_estadisticas") $multiple="multiple";
               		else $multiple="";
								?>
                     <select name="select_entidad" size="26" <?=$multiple?> style="width:98%" onchange="set_datos()" onKeypress="set_datos();buscar_op(this);set_datos()" onblur="borrar_buffer()" onclick="editar_campos(0);if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=0; if(document.all.activo_entidad.checked==true){ document.forms[0].elegir.disabled=false; document.all.msg_elegir_cliente.innerText=''; } else { document.forms[0].elegir.disabled=true; document.all.msg_elegir_cliente.innerText='Debe estar Activo el Cliente para Elegir'; }borrar_buffer()">

                        <? $datos_entidad->Move(0);
                         while (!$datos_entidad->EOF)
	                      {
                        if ($es_pymes) $id_entidad=$datos_entidad->fields["id_entidad_pyme"];
                               else    $id_entidad=$datos_entidad->fields["id_entidad"];


                        ?>
                        <option value="<?=$id_entidad?>" <?if($_POST['select_entidad']==$id_entidad) echo "selected"?>>
                          <?=$datos_entidad->fields['nombre']?>
                       </option>
                          <? 	$datos_entidad->MoveNext();
                          } ?>
                     </select>
               </td>
             </tr>
             <tr>
             	<td>
<TABLE width="100%" align="center" cellspacing="0">
<!-- <tr><td> <input type="submit" name="boton" value="Guardar" onClick="set_opener_campos();return control_datos()">
-->
<tr>
<td width="50%" align="left">
<?
if (
    ($parametros['pagina']=="presupuestos")||
    ($parametros['pagina']=="presupuestos_new")||
    ($parametros['pagina']=="remitos")||
    ($parametros['pagina']=="facturas")||
    ($parametros['pagina']=="caso_estadisticas")||
    ($parametros['pagina']=="altas_cheque_dif") || $onclick['elegir']!='')
{?>
 	<font color="Red">
 	<strong>
	<span id="msg_elegir_cliente"></span>
 	</strong>
 	</font> 	
	<input type="button" name="elegir" value="Elegir cliente/entidad" disabled title="Traslada los datos de la entidad seleccionada a la página <?=$parametros['pagina']?>" onclick="<?=$onclick['elegir']?>">
<?
}
?>
</td>
<td width="50%">
<?if (permisos_check("inicio","permiso_editar_cliente"))
	 $permiso_editar=" ";
	else 
	 $permiso_editar=" disabled";
	
?>
<input type="submit" name="boton" value="Editar" <?=$permiso_editar?> title="Editar datos de la entidad" disabled style="width:80px" onClick="if (this.value=='Editar') {editar_campos(1);return false;} else return control_datos()">

</TD>
<td width="40%" align="left">
<?

if(
    ($parametros['pagina']=="presupuestos")||
    ($parametros['pagina']=="presupuestos_new")||
    ($parametros['pagina']=="remitos")||
    ($parametros['pagina']=="facturas")||
    ($parametros['pagina']=="caso_estadisticas")||
    ($parametros['pagina']=="altas_cheque_dif") || $onclick['elegir']!='')



    {
    ?>
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
    <?
    }
    ?>
</td>
</tr>
</TABLE>
             	</td>
             </tr>
        </table>
      </td>
    </tr>
  </TABLE>
</center>
<INPUT type="hidden" name="entidad">
<script>
limpiar();
if (typeof document.all.elegir!='undefined') document.all.elegir.disabled=1;
document.all.select_entidad.selectedIndex=-1;
</script>
</form>
</body>
</html>
<? //echo fin_pagina()?>