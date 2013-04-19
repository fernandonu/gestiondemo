<?
/*
Autor: lizi

MODIFICADA POR
$Author: enrique $
$Revision: 1.37 $
$Date: 2006/02/07 17:34:11 $
*/

require_once("../../config.php");
//print_r($parametros);
$fecha=fecha_db(date("d/m/Y",mktime()));
$cmd=$parametros['cmd'];
$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST['id_entrega_estimada'];
$tipo=$_POST['tipo'] or $tipo=$parametros['tipo'];
$id_licitacion=$_POST['id_licitacion'];
$id_subir=$_POST['id_subir'];
//$id_entidad1=$_POST['id_entidad'];
$id_envio_renglones=$parametros['id_envio_renglones'] or $id_envio_renglones=$_POST['id_envio_renglones'];
$id_datos_envio=$_POST['id_datos_envio'] or $id_datos_envio=$parametros['id_datos_envio'];
$pagina=$_POST['pagina'] or $pagina=$parametros['pagina'];
$boton_viene=$parametros['viene_boton'] or $boton_viene=$_POST['viene_boton'];

$dir_entrega=$_POST['lugar_entrega'];
$entidad=$_POST['nombre'];
$nro_lic=$_POST['nro_lic_codificado'];
$contacto=$_POST['contacto'];
$nro_oc=$_POST['nro_orden'];
$id_subir=$_POST['id_subir'];
$id_licitacion=$_POST['id_licitacion'];
$origen_envio=$_POST['id_envio_origen'];
$destino_envio=$_POST['id_envio_destino'];
$comentar=1;

// guardar el envio
if ($_POST["preparar"]=="Preparar para el Envío"){
   $db->StartTrans();
     $id_envio_renglones=$_POST['id_envio_renglones'];
     $nuevo=$_POST['nuevo'];
     //$cant_bultos=$_POST['cant_bultos'];
     $cant_bultos=0;

     // para controlar la cantidad de check y despues verificar cuales fueron checkeados para guardarlos en el envio
     $cant_check=$_POST['cant_check'];
     $y=0;
     $iguales=1;
     for ($i=0; $i<$cant_check; $i++) {
      	  if ($_POST["renglon_$i"]){
      	  	if($y==0)
      	  	{
      	  	    $compara1=$_POST["entrega_$i"];
      	  	    //$compara=$_POST["entrega_$i"];
      	  		$y=1;	
      	  	}
            $compara=$_POST["entrega_$i"];
       
            if($compara!=$compara1)
            {
            $i=$cant_check;
            $iguales=0;
            }    	
      	  }
     	 }
    
     if($iguales!=0)
     {
     if ($id_envio_renglones=="") {
     	
     	$q_nextval="select nextval('licitaciones_datos_adicionales.envio_renglones_id_envio_renglones_seq') as id_envio_renglones";
      	$res_q=sql($q_nextval, "Error la traer secuencia de id de envio_renglones") or fin_pagina();
      	$id_envio_renglones=$res_q->fields['id_envio_renglones'];

      	$que="select id_entidad from licitacion where id_licitacion=$id_licitacion";
      	$res2=sql($que, "Error al traer id_entidad") or fin_pagina();
      	$id_entidad1=$res2->fields['id_entidad'];
        if($nuevo==1)
        {
      	$q_insert_envio_renglones="insert into licitaciones_datos_adicionales.envio_renglones (id_envio_renglones, id_subir, id_licitacion, envio_cerrado,id_entidad,id_lugar_entrega)
                                 values ($id_envio_renglones, $id_subir, $id_licitacion, 0,$id_entidad1,$compara1)";
      	$res_q_insert_envio_renglones=sql($q_insert_envio_renglones, "Error al insertar envio_renglones") or fin_pagina();
        }
        else 
        {
        $q_insert_envio_renglones="insert into licitaciones_datos_adicionales.envio_renglones (id_envio_renglones, id_subir, id_licitacion, envio_cerrado,id_entidad)
                                 values ($id_envio_renglones, $id_subir, $id_licitacion, 0,$id_entidad1)";
      	$res_q_insert_envio_renglones=sql($q_insert_envio_renglones, "Error al insertar envio_renglones") or fin_pagina();	
        }
      	// insert en la tabla del log = se creo un nuevo envio
      	$q_nextval="select nextval ('licitaciones_datos_adicionales.log_envio_renglones_id_log_envio_seq') as id_log_envio";
     	$res_q=sql($q_nextval, "Error al traer secuencia de id log_envio") or fin_pagina();
      	$id_log_envio=$res_q->fields['id_log_envio'];

      	$usuario=$_ses_user['name'];
      	$q_insert_log="insert into licitaciones_datos_adicionales.log_envio_renglones
                     (id_log_envio, id_envio_renglones, usuario, tipo_log, fecha)
                     values ($id_log_envio, $id_envio_renglones, '$usuario', 'creacion', '$fecha')";
      	$res_q_insert_log=sql($q_insert_log, "Error al insertar el log del envío") or fin_pagina();
      	$pase=1;
        $entrega1="";      
       }
       else {
       $sele="select id_lugar_entrega from licitaciones_datos_adicionales.envio_renglones
       where id_envio_renglones=$id_envio_renglones";	
       $sel=sql($sele, "Error al recuperar id_lugar_entrega ") or fin_pagina();
       $entrega1=$sel->fields['id_lugar_entrega'];        
       }
     $j=0;
     $e=0;
     if(($entrega1=="")||($entrega1==$compara1))
     {
     for ($i=0; $i<$cant_check; $i++) {
      	if ($_POST["renglon_$i"])
      	{
           $id_renglones_oc=$_POST["id_renglones_oc_$i"];
           $cantidad=$_POST["cant_a_enviar_$i"];
           $titulo_mod=$_POST["titulo_$i"];
           $entrega=$_POST["entrega_$i"];
	       $q_nextval="select nextval('licitaciones_datos_adicionales.renglones_bultos_id_renglones_bultos_seq') as id_renglones_bulto";
	       $res_q=sql($q_nextval, "Error la traer secuencia de id de renglones_bulto") or fin_pagina();
	       $id_renglones_bultos=$res_q->fields['id_renglones_bulto'];
	
	       $q_insert_renglones_bultos="insert into licitaciones_datos_adicionales.renglones_bultos (id_renglones_bultos, id_envio_renglones, id_renglones_oc, cantidad_enviada, titulo_mod)
	                                  values ($id_renglones_bultos, $id_envio_renglones, $id_renglones_oc, $cantidad, '$titulo_mod') ";
	       $res_q_insert_renglones_bultos=sql($q_insert_renglones_bultos, "Error al insertar los renglones que se incluyen en los bultos") or fin_pagina();
	       $cant_bultos+=$cantidad;
	       $entrega1=$entrega;
	       $e=1;
      
      	   
      }
      }
      // insertar la cantidad de bultos totales al envio
      if($nuevo==1)
      {
      $q="update licitaciones_datos_adicionales.envio_renglones set cantidad_total=cantidad_total+$cant_bultos,id_lugar_entrega=$entrega1 where id_envio_renglones=$id_envio_renglones";
      $res_q=sql($q, "Error al actualizar la cantidad total de bultos para este envio") or fin_pagina();
      }
      else 
      {
      	$q="update licitaciones_datos_adicionales.envio_renglones set cantidad_total=cantidad_total+$cant_bultos where id_envio_renglones=$id_envio_renglones";
      $res_q=sql($q, "Error al actualizar la cantidad total de bultos para este envio") or fin_pagina();
      }
      // insertar el log cuando agrego un nuevo bulto al envio
      $q_nextval="select nextval ('licitaciones_datos_adicionales.log_envio_renglones_id_log_envio_seq') as id_log_envio";
      $res_q=sql($q_nextval, "Error al traer secuencia de id log_envio") or fin_pagina();
      $id_log_envio=$res_q->fields['id_log_envio'];

      $usuario=$_ses_user['name'];
      $q_insert_log="insert into licitaciones_datos_adicionales.log_envio_renglones
                     (id_log_envio, id_envio_renglones, usuario, tipo_log, fecha)
                     values ($id_log_envio, $id_envio_renglones, '$usuario', 'agregar bultos al envío', '$fecha')";
      $res_q_insert_log=sql($q_insert_log, "Error al insertar el log del envío") or fin_pagina();
     }
     else 
     {
     ?>
     <script>
     alert("Los renglones no se incluyeron en el envió por que la dirección de entrega no es la misma");
     </script>
     <?
     }
     
  }
  else 
  {	
  ?>
     <script>
     alert("Los renglones no se incluyeron en el envió por que la dirección de entrega no es la misma");
     </script>
  <?
  }
  $db->Completetrans();
}

if ($_POST['guardar']=="Guardar"){
   $db->StartTrans();
     $id_envio_renglones=$_POST['id_envio_renglones'];
// recupero x post los valores q guardo en las distintas tablas
     $ent_mod=$_POST['entidad'];
     $dir_mod=$_POST['direccion'];
     $contacto_mod=$_POST['contacto'];
     $nro_lic_mod=$_POST['nro_lic'];
     $nro_oc_mod=$_POST['oc'];
     $id_transporte=$_POST['transporte'];

     $comentarios_transporte=$_POST['comentarios_transporte'];

     $id_datos_envio=$_POST['id_datos_envio'];
     // recupero origen y destino del envio
     $origen=$_POST['origen'];
     $destino=$_POST['destino'];
     $comen_envio=$_POST['comentarios_en'];
     $cant_renglones_en_el_envio=$_POST['cant_renglones_en_el_envio'];
     $comentarios1=$comen_envio;
     if ($id_datos_envio=="") {
// insert de los datos para las etiquetas -- la primera vez
        $q_nextval="select nextval ('licitaciones_datos_adicionales.datos_envio_id_datos_envio_seq') as id_datos_envio";
        $res_q=sql($q_nextval, "Error al traer secuencia de id de datos_envio") or fin_pagina();
        $id_datos_envio=$res_q->fields['id_datos_envio'];
      // comentarios_envio_transporte,  '$comentarios_envios',
     	$q_datos_envios="insert into licitaciones_datos_adicionales.datos_envio
                       (id_datos_envio, id_envio_renglones, entidad_mod, dir_entrega_mod, contacto_mod, nro_lic_mod, nro_oc_mod,
                       id_envio_origen, id_envio_destino,comentarios_envio)
                       values ($id_datos_envio, $id_envio_renglones, '$ent_mod', '$dir_mod', '$contacto_mod', '$nro_lic_mod', '$nro_oc_mod',
                       $origen , $destino,'$comen_envio')";
        $res_q_datos_envio=sql($q_datos_envios, "Error al insertar los datos para el envío") or fin_pagina();
     }
     else {
     	//comentarios_envio_transporte='$comentarios_envios',
        $q_update_datos_envio="update licitaciones_datos_adicionales.datos_envio set id_envio_renglones=$id_envio_renglones,
                               entidad_mod='$ent_mod', dir_entrega_mod='$dir_mod', contacto_mod='$contacto_mod',
                               nro_lic_mod='$nro_lic_mod', nro_oc_mod='$nro_oc_mod',
                               id_envio_origen=$origen, id_envio_destino=$destino,comentarios_envio='$comen_envio' where id_datos_envio=$id_datos_envio";
        $res_q_update_datos_envio=sql($q_update_datos_envio, "Error al actualizar los datos del envío") or fin_pagina();
     }
        // insertar la cantidad de bultos totales al envio

        $q="update licitaciones_datos_adicionales.envio_renglones set id_transporte=$id_transporte where id_envio_renglones=$id_envio_renglones";
        $res_q=sql($q, "Error al actualizar el transporte para este envio") or fin_pagina();
		$totalBultosOcupados=0;
        for ($k=0; $k<$cant_renglones_en_el_envio; $k++){

           $bultos_ocupados=$_POST["bultos_ocupados_$k"];
           $id_renglones_bultos=$_POST["id_renglones_bultos_$k"];
           $q_i="update licitaciones_datos_adicionales.renglones_bultos set bultos_ocupados=$bultos_ocupados
                 where id_renglones_bultos=$id_renglones_bultos";
           $res_q_i=sql($q_i, "Error al actualizatr los valores para los bultos") or fin_pagina();
           $totalBultosOcupados+=$_POST["bultos_ocupados_$k"];
        }
        $q="update licitaciones_datos_adicionales.envio_renglones set cantidad_total=$totalBultosOcupados where id_envio_renglones=$id_envio_renglones";
        $res_q_i=sql($q, "Error al actualizar los valores para el total de bultos") or fin_pagina();
		// para actualizar el comentario del trasnporte antes cargado !
        $q_u="update licitaciones_datos_adicionales.transporte set comentarios_transporte='$comentarios_transporte'
              where id_transporte=$id_transporte";
        $res_q_u=sql($q_u, "Error al actualizar los comentarios del transporte") or fin_pagina();
   $db->Completetrans();
}

if ($_POST["eliminar"]=="Eliminar Renglones") {
   $db->StartTrans();

	   $cant_r_envio=$_POST["cant_r_envio"];
	   for ($i=0; $i<$cant_r_envio; $i++) {
	      	if ($_POST["renglon_eliminar_$i"]){
	           $id_renglones_oc=$_POST["id_renglones_oc_$i"];
	           $id_renglones_bultos=$_POST["id_renglones_bultos_$i"];

	      // eliminar numeros de serie si ya fueron cargados
         $q_del_ns="delete from licitaciones_datos_adicionales.nro_serie_renglon
       			    where id_renglones_bultos=$id_renglones_bultos";
	     $res_q_del_ns=sql($q_del_ns, "Error al eliminar los renglones que se incluyen en el Envío") or fin_pagina();

	     $q_del_rb="delete from licitaciones_datos_adicionales.renglones_bultos
	                where id_renglones_bultos=$id_renglones_bultos";
	     $res_q_del_rb=sql($q_del_rb, "Error al eliminar los renglones que se incluyen en el Envío") or fin_pagina();
	         } // if ($_POST["renglon_eliminar_$i"])
	      } // for ($i=0; $i<$cant_r_envio; $i++)
	      $selecte="select id_renglones_bultos from renglones_bultos where id_envio_renglones=$id_envio_renglones";
	      $res_sel=sql($selecte, "Error al traer los renglones de este envío") or fin_pagina();
	      $result_sel=$res_sel->RecordCount();
	      if($result_sel==0)
	      {
	      	$q_del="update  licitaciones_datos_adicionales.envio_renglones set id_lugar_entrega=null
	                where id_envio_renglones=$id_envio_renglones";
	       sql($q_del, "Error al eliminar los renglones que se incluyen en el Envío") or fin_pagina();
	      }
    $db->Completetrans();
}
if ($_POST["terminar_envio"]=="Terminar Envío"){
   $db->StartTrans();

    $q="select id_remito from licitaciones_datos_adicionales.renglones_bultos
	    where id_envio_renglones=$id_envio_renglones and not id_remito is null";
	$res_q=sql($q, "Error al traer los nros. de remitos asociados q este envío") or fin_pagina();
	$result=$res_q->RecordCount();
	if ($result==0) {
	   $msg="Faltan ingresar el remito asociado a este Envío";
	 }

     $id_envio_renglones=$_POST['id_envio_renglones'];
     $q_update_envio="update licitaciones_datos_adicionales.envio_renglones set envio_cerrado=1 where id_envio_renglones=$id_envio_renglones";
     $res_q_update_envio=sql($q_update_envio, "Error al cerrar el envio") or fin_pagina();

     // insert en la tabla del log = se cerro el envio
      $q_nextval="select nextval ('licitaciones_datos_adicionales.log_envio_renglones_id_log_envio_seq') as id_log_envio";
      $res_q=sql($q_nextval, "Error al traer secuencia de id log_envio") or fin_pagina();
      $id_log_envio=$res_q->fields['id_log_envio'];
      $usuario=$_ses_user['name'];
      $q_insert_log="insert into licitaciones_datos_adicionales.log_envio_renglones
                     (id_log_envio, id_envio_renglones, usuario, tipo_log, fecha)
                     values ($id_log_envio, $id_envio_renglones, '$usuario', 'cerrar envío', '$fecha')";
      $res_q_insert_log=sql($q_insert_log, "Error al insertar el log del envío") or fin_pagina();
     $usu=1;
   $db->Completetrans();
   $flag_mail=true;
   ?>
   <script>
     alert ('Los Cambios se realizaron con éxito');
     //window.opener.location.reload();
     //this.close();
    </script>
    <?
}

if ($_POST["anular_envio"]=="Anular Envío")
{
  	
	  $db->StartTrans(); 
	  $id_envio_renglones=$_POST['id_envio_renglones'];    
	  $q_update_envio="update licitaciones_datos_adicionales.envio_renglones set envio_cerrado=2 where id_envio_renglones=$id_envio_renglones";
	  $res_q_update_envio=sql($q_update_envio, "Error al anular el envio") or fin_pagina(); 
	  $db->Completetrans();
	  $link=encode_link("listado_envios.php",array());
	  //header("location:$link");
	  ?>
	  <script>
	  document.location.href="<?=$link?>";
	  </script>
	  <?
}

// traigo solo los renglones
$q_renglones="select distinct(id_renglones_oc),id_renglon,subido_lic_oc.id_licitacion,codigo_renglon,titulo,subido_lic_oc.id_subir,
      renglones_oc.cantidad
      from licitaciones.subido_lic_oc
      join licitaciones.renglones_oc using (id_subir)
      join licitaciones.renglon using (id_renglon)
      left join licitaciones.log_renglones_oc using (id_renglones_oc)
      where id_entrega_estimada=$id_entrega_estimada";
$res_q_renglones=sql($q_renglones, "Error la traer datos de los renglones");
$r=$res_q_renglones->RecordCount();


if ($id_envio_renglones==""){

		// traigo los datos originales q despues guardo en la tabla datos_envio
		$q_select_datos="select id_renglones_oc,id_renglon,subido_lic_oc.id_licitacion,codigo_renglon,titulo,
		renglones_oc.cantidad ,subido_lic_oc.lugar_entrega,nombre, nro_lic_codificado,contacto,nro_orden,
		subido_lic_oc.id_subir,comentarios_envio, datos_envio.id_datos_envio,envio_renglones.id_lugar_entrega
		from licitaciones.subido_lic_oc
		join licitaciones.licitacion using (id_licitacion)
		join licitaciones.entidad using (id_entidad)
		join licitaciones.renglones_oc using (id_subir)
		join licitaciones.renglon using (id_renglon)
		left join licitaciones_datos_adicionales.envio_renglones using (id_subir)
		left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones)
		where id_entrega_estimada=$id_entrega_estimada";
		$res_q_select=sql($q_select_datos, "Error al traer los datos del Envío") or fin_pagina();
		$id_datos_envio=$res_q_select->fields['id_datos_envio'];
}
else {
	if ($id_datos_envio){
		// , comentarios_envio_transporte
		// traer los datos del envio (q van en la etiqueta) q estan en la tabla datos_envios
		$q_select_datos="select distinct entidad_mod as nombre,comentarios_envio, dir_entrega_mod as lugar_entrega, contacto_mod as contacto,
		nro_oc_mod as nro_orden, nro_lic_mod as nro_lic_codificado, id_datos_envio, id_envio_origen, id_envio_destino,
		envio_renglones.id_subir, envio_renglones.id_licitacion,envio_renglones.id_lugar_entrega
		from licitaciones_datos_adicionales.datos_envio
		left join licitaciones_datos_adicionales.envio_renglones using (id_envio_renglones)
		left join licitaciones_datos_adicionales.renglones_bultos using (id_envio_renglones)
		left join licitaciones.renglones_oc using (id_renglones_oc)
		where id_envio_renglones=$id_envio_renglones and id_datos_envio=$id_datos_envio";
		$res_q_select=sql($q_select_datos, "Error al traer los datos para el Envío") or fin_pagina();
	}
	else
	{
		// traigo los datos originales q despues guardo en la tabla datos_envio
		$q_select_datos="select id_renglones_oc,id_renglon,subido_lic_oc.id_licitacion,comentarios_envio,codigo_renglon,titulo,
		renglones_oc.cantidad ,subido_lic_oc.lugar_entrega,nombre, nro_lic_codificado,contacto,nro_orden,
		subido_lic_oc.id_subir, datos_envio.id_datos_envio,envio_renglones.id_lugar_entrega
		from licitaciones.subido_lic_oc
		join licitaciones.licitacion using (id_licitacion)
		join licitaciones.entidad using (id_entidad)
		join licitaciones.renglones_oc using (id_subir)
		join licitaciones.renglon using (id_renglon)
		left join licitaciones_datos_adicionales.envio_renglones using (id_subir)
		left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones)
		where id_entrega_estimada=$id_entrega_estimada";
		$res_q_select=sql($q_select_datos, "Error al traer los datos del Envío") or fin_pagina();
		$id_datos_envio=$res_q_select->fields['id_datos_envio'];
	}
	 $q_envio="select cantidad_enviada, envio_renglones.cantidad_total,id_envio_origen,id_envio_destino, titulo_mod, id_renglones_bultos,
	 envio_cerrado, codigo_renglon,id_transporte, nombre_transporte,id_renglones_oc, bultos_ocupados, comentarios_transporte,direccion_transporte,telefono_transporte
	 from licitaciones_datos_adicionales.renglones_bultos
	 left join licitaciones_datos_adicionales.envio_renglones using (id_envio_renglones)
	 left join licitaciones.renglones_oc using (id_renglones_oc)
	 left join licitaciones.renglon using (id_renglon)
	 left join licitaciones_datos_adicionales.transporte using (id_transporte)
	 left join licitaciones_datos_adicionales.datos_envio using(id_envio_renglones)
	 where id_envio_renglones=$id_envio_renglones";
	 $res_q_envio=sql($q_envio, "Error al traer los datos de los envíos ") or fin_pagina();
	 $envio_cerrado=$res_q_envio->fields["envio_cerrado"];
	 $id_transporte=$res_q_envio->fields['id_transporte'];
	 $comentario_transporte_guardado=$res_q_envio->fields['comentarios_transporte'];
	 $cant_renglones_en_el_envio=$res_q_envio->RecordCount();
	 $bultos_ocupados=$res_q_envio->fields['bultos_ocupados'];
	 $origen_envio=$res_q_envio->fields['id_envio_origen'];
	 $destino_envio=$res_q_envio->fields['id_envio_destino'];

}
if($id_envio_renglones!=""){
$sel_lugar="Select contacto,direccion,telefono,banda_horaria from envio_renglones
            left join lugar_entrega using(id_lugar_entrega)
            where id_envio_renglones=$id_envio_renglones";
$sel_lugares=sql($sel_lugar,"No se pudo recuperar el lugar de entrega")or fin_pagina();
$dir_ent=$sel_lugares->fields["direccion"];
}
$datos=1;
if($dir_ent!="")
{	
 $contacto=$sel_lugares->fields["contacto"];
 $dir_entrega=$sel_lugares->fields["direccion"]; 
 $tel_entrega=$sel_lugares->fields["telefono"]; 
 $bh_entrega=$sel_lugares->fields["banda_horaria"];
 $datos=0; 
}
else 
{
if(!$dir_entrega)
$dir_entrega=$res_q_select->fields['lugar_entrega'];
if(!$contacto)
$contacto=$res_q_select->fields['contacto'];
}
if(!$entidad)
$entidad=$res_q_select->fields['nombre'];
if(!$nro_lic)
$nro_lic=$res_q_select->fields['nro_lic_codificado'];

if(!$nro_oc)
$nro_oc=$res_q_select->fields['nro_orden'];
if(!$id_subir)
$id_subir=$res_q_select->fields['id_subir'];
if(!$id_licitacion)
$id_licitacion=$res_q_select->fields['id_licitacion'];
if(!$origen_envio)
$origen_envio=$res_q_select->fields['id_envio_origen'];
if(!$destino_envio)
$destino_envio=$res_q_select->fields['id_envio_destino'];
//if(!$id_lugar_entrega)
/*if(!$direc)
$direc=$res_q_select->fields['direccion_transporte'];
if(!$tel)
$tel=$res_q_select->fields['telefono_transporte'];*/
if(!$comentarios1)
$comentarios1=$res_q_select->fields['comentarios_envios'];

// solo lo uso cuando viene del listado
if ($pagina=="listado") {
$q_a="select envio_cerrado from licitaciones_datos_adicionales.envio_renglones where id_envio_renglones=$id_envio_renglones";
$res_q_a=sql($q_a, "Error al traer campo de envio_cerrado") or fin_pagina();
$envio_cerrado=$res_q_a->fields['envio_cerrado'];
if ($envio_cerrado==1) {
	$deshab_botones_terminado="disabled";
    $usu=1;
}
else
{
	$deshab_botones_terminado="";
	$usu=0;
}
}

if ($id_datos_envio==""){
$deshab_botones_sin_guardar_datos="disabled";
}
if ($id_envio_renglones && $envio_cerrado==0)
$msg_aviso="Este Envío está en estado Pendiente, ya tiene productos incluidos";


$link_f=encode_link("preparar_envios.php", array("id_entrega_estimada"=>$id_entrega_estimada,"id_envio_renglones"=>$id_envio_renglones, "id_datos_envio"=>$id_datos_envio));
echo $html_header;
echo "<b><center><font size='3' color='red'>".$msg."</font></center></b>";
echo "<b><center><font size='3' color='red'>".$msg2."</font></center></b>";
echo "<b><center><font size='3' color='red'>".$msg_aviso."</font></center></b>";
?>
<script>
function control_preparar_envio(){
var sent, sent2;
var check, text;
var cant=0;
  /*if (document.all.cant_bultos.value==""){
  	alert ("Debe ingresar la cantidad de bultos totales para el Envío");
    return false;
  }*/
  for (i=0; i<parseInt(document.all.cant_check.value); i++) {
    sent="document.all.renglon_"+i;
    sent2="document.all.cant_a_enviar_"+i;
    check=eval(sent);
    text=eval(sent2);
    if (check.checked && text.value==""){
      alert ("Debe ingresar la cantidad a ser enviada");
      return false;
     }
    if (check.checked) cant++;
   }
  if (cant==0){
    alert ("Debe seleccionar un renglón para ser Enviado");
    return false;
  }
  
  //Control de lugar de entrega
  var cant=0,control=0;
  
  for (i=0; i<parseInt(document.all.cant_check.value); i++) {
    sent="document.all.renglon_"+i;
    text="document.all.entrega_"+i;
    check=eval(sent);
    text=eval(text);
    if (check.checked && parseInt(control)==0){
      sent2="document.all.entrega_"+i;
      sent2=eval(sent2);	
      control=1;
      //alert ("Debe ingresar la cantidad a ser enviada");
      }
    if (check.checked && sent2.value!=text.value){
      	
     alert("Los renglones no se incluyeron en el envió por que la dirección de entrega no es la misma");
      return false;
    }  
  }
  //Fin de control de entrega
  
 return true;
}


function control_guardar() {
if (document.all.transporte.value==-1){
    alert ("Debe seleccionar un transporte para el Envío");
    return false;
  }
 return true;
}

function control_elim(){
var cant, sent, check;
cant=0;
	for (i=0; i<parseInt(document.all.cant_r_envio.value); i++) {
	   sent="document.all.renglon_eliminar_"+i;
	   check=eval(sent);
	   if (check.checked) cant++;
      }
    if (cant==0){
    alert ("Debe seleccionar un renglón para Borrar");
    return false;
     }
 return true;
}
</script>

<form name="form1" method="POST" action="<?=$link_f?>">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<input type="hidden" name="id_subir" value="<?=$id_subir?>">
<input type="hidden" name="id_envio_renglones" value="<?=$id_envio_renglones?>">
<input type="hidden" name="id_datos_envio" value="<?=$id_datos_envio?>">
<input type="hidden" name="cambio_datos" value="">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<input type="hidden" name="envio_cerrado" value="<?=$envio_cerrado?>">
<!-- hidden para guardar los datos para la etiqueta, se guardan en la tabla datos_envio -->
<input type="hidden" name="ent_mod" value="<?=$entidad?>">
<input type="hidden" name="dir_mod" value="<?=$dir_entrega?>">
<input type="hidden" name="contacto_mod" value="<?=$contacto?>">
<input type="hidden" name="nro_lic_mod" value="<?=$nro_lic?>">
<input type="hidden" name="nro_oc_mod" value="<?=$nro_oc?>">
<input type="hidden" name="id_lugar_entrega" value="<?=$id_lugar_entrega?>">

<input type="hidden" name="cant_renglones_en_el_envio" value="<?=$cant_renglones_en_el_envio?>">
<br>

  <table width="100%" align="center">
 <?if($usu==1){
      /*
      $sql_sel="select * from log_envio_renglones where id_envio_renglones=$id_envio_renglones";
	  $res18=sql($sql_sel, "Error al traer los datos") or fin_pagina();
	  $usuario=$res18->fields['usuario'];
      */
      ?>
     <tr>
     <td align="center" colspan="5"><strong>
     Usuario que cerro el envío <?=$_ses_user["name"]?>
     </strong></td>
     </tr>
     <?
 }
     if($comentar==0)
     {?>
     <tr>
     <td align="center" colspan="5"><strong>
     Los renglones siguientes no se incluyeron en el envió por que la dirección de entrega no es la misma</strong></td>  
     </tr>
     <?
     $c=0;
     while($conta>$c)
     {?>
     <tr><td align="center" colspan="5"><strong><?=$comen[$c]?></strong></td></tr>
     <?
     $c++;
     }?>
          
     <?
     }
     $con="select id_lugar_entrega,configuracion_entrega.cantidad,direccion,contacto,telefono,titulo,id_renglones_oc,configuracion_entrega.id_entrega_estimada,codigo_renglon from licitaciones.configuracion_entrega
	 left join licitaciones.renglones_oc using(id_renglones_oc)
	 left join licitaciones.renglon using (id_renglon)
	 left join licitaciones.lugar_entrega using (id_lugar_entrega)
	 where configuracion_entrega.id_entrega_estimada=$id_entrega_estimada";
     $consulta=sql($con,"No se pudo recuperar los datos de la entrega");
     ?>
     <br>
     <br>
    <tr>
     <td id='mo'></td>
     <td align="center" id='mo'>Cantidad a Enviar</td>
     <td align="center" id='mo'>Cantidad Total</td>
     <td align="center" id='mo'>Número Renglón</td>
     <td align="center" id='mo'>Título del Renglón</td>
     <?
     if(!$consulta->EOF)
     {?>
     <td align="center" id='mo'>Entrega</td>	
     <?} 
     ?>
    </tr>
   <? 
     if(!$consulta->EOF)
     {
     $nuevos=1;    
     $i=0;	
     while(!$consulta->EOF)
     {
     $id_renglones_oc=$consulta->fields['id_renglones_oc'];		
     ?>
     <tr <?=$atrib_tr?>>
     <input type="hidden" name="nuevo" value="1">
     <td align="center"><input type="checkbox" name="renglon_<?=$i?>" value="1"></td>
     <input type="hidden" name="id_renglones_oc_<?=$i?>" value="<?=$consulta->fields['id_renglones_oc'];?>">
     <td align="center"><b><input type="text" name="cant_a_enviar_<?=$i?>" value="<?=$cantidad_a_enviar?>" size="6"></td>
     <td align="center"><b><?=$consulta->fields['cantidad'];?></td>
     <input type="hidden" name="cantidad_<?=$i?>" value="<?=$consulta->fields['cantidad'];?>">
     <td align="center"><b><?=$consulta->fields['codigo_renglon'];?></td>
     <input type="hidden" name="codigo_renglon_<?=$i?>" value="<?=$consulta->fields['codigo_renglon'];?>">
     <td align="center"><b><?=$consulta->fields['titulo'];?></td>
     <input type="hidden" name="titulo_<?=$i?>" value="<?=$consulta->fields['titulo'];?>">
       <td align="center"><b><?=$consulta->fields['direccion'];?></td>
     <input type="hidden" name="entrega_<?=$i?>" value="<?=$consulta->fields['id_lugar_entrega'];?>">
     </tr>
    <? 
    $i++;
    $consulta->MoveNext(); }	
    }
    else {  
   for ($i=0; $i<$r; $i++) {
   	    $id_renglones_oc=$res_q_renglones->fields['id_renglones_oc'];
   	    $cantidad=$res_q_renglones->fields['cantidad'];
   	    $codigo_renglon=$res_q_renglones->fields['codigo_renglon'];
   	    $titulo_renglon=$res_q_renglones->fields['titulo'];
   	    ?>
   <tr <?=$atrib_tr?>>
     <td align="center"><input type="checkbox" name="renglon_<?=$i?>" value="1"></td>
     <input type="hidden" name="id_renglones_oc_<?=$i?>" value="<?=$id_renglones_oc?>">
     <td align="center"><b><input type="text" name="cant_a_enviar_<?=$i?>" value="<?=$cantidad_a_enviar?>" size="6"></td>
     <td align="center"><b><?=$cantidad?></td>
     <input type="hidden" name="cantidad_<?=$i?>" value="<?=$cantidad?>">
     <td align="center"><b><?=$codigo_renglon?></td>
     <input type="hidden" name="codigo_renglon_<?=$i?>" value="<?=$codigo_renglon?>">
     <td align="center"><b><?=$titulo_renglon?></td>
     <input type="hidden" name="titulo_<?=$i?>" value="<?=$titulo_renglon?>">
  </tr>
  <? $res_q_renglones->MoveNext(); }} ?>
 <input type="hidden" name="cant_check" value="<?=$i?>">
 </table>
 <br>
 <table width="50%" align="center">
  <!--tr>
   <td align="left"><b>Cantidad de Bultos para este Envío</b>&nbsp;&nbsp;<input type="text" name="cant_bultos" value="" size="6"></td>
   <td></td>
  </tr>
  <tr-->
   <td align="center">
    <input type="submit" name="preparar" value="Preparar para el Envío" title="Los renglones seleccionados se incluirán en el envío" <?//=$deshabilitar_botones?>
     onclick="return control_preparar_envio();" <?//=$deshab_botones_terminado?>>
   </td>
   <?
   if($datos==0)
   {?>
   <td align="center">
   <?
 $link_ent = encode_link("mostrar_entregas.php",array("id_entrega_estimada"=>$id_entrega_estimada));
 echo "&nbsp;&nbsp;<input type=button name=configurar value='Mostrar Configuracion de Entregas' onclick='window.open(\"$link_ent\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\");'>&nbsp;&nbsp;";
 ?>
   </td>
   <?}?>
 </tr>
 </table>
<br>
   <? //|| $boton_viene!=""  agregar a la condicion !
    if ($id_envio_renglones) {?>
<table width="100%" align="center">
   <tr>
     <td id='mo' colspan="2"><font size="2"><b>Datos del Envío Nro. <?=$id_envio_renglones?>&nbsp;&nbsp;
     <a target="_blank" href='<?=encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));?>'>ID Lic<?=$id_licitacion?>
     </a></font> </td>
     </tr>
   <tr>
     <td align="center" id='ma'>Entidad</td>
     <td align="center" id='ma'>
       <textarea name="entidad" cols="100" rows="2"><?=$entidad?></textarea>
     </td>
   </tr>
   <tr>
     <td align="center" id='ma'>Dirección de Entrega</td>
     <td align="center" id='ma'>
       <textarea name="direccion" cols="100" rows="2"><?=$dir_entrega?></textarea>
     </td>
   </tr>
   <tr>
     <td align="center" id='ma'>Contacto</td>
     <td align="center" id='ma'>
       <input type="text" name="contacto" value="<?=$contacto?>" size="100">
     </td>
   </tr>
   <?
   if($datos==0)
   {?>
   <tr>
     <td align="center" id='ma'>Nro. Telefono</td>
     <td align="center" id='ma'>
       <input type="text" name="nro_tel" value="<?=$tel_entrega?>" size="100">
    </td>
   </tr>
   <tr>
     <td align="center" id='ma'>Banda Horaria</td>
     <td align="center" id='ma'>
       <input type="text" name="bh_ent" value="<?=$bh_entrega?>" size="100">
    </td>
   </tr>
   
   <?}
   ?>
   <tr>
     <td align="center" id='ma'>Nro. Licitación</td>
     <td align="center" id='ma'>
       <input type="text" name="nro_lic" value="<?=$nro_lic?>" size="100">
    </td>
   </tr>
   <tr>
     <td align="center" id='ma'>Nro. Orden de Compra</td>
     <td align="center" id='ma'>
       <input type="text" name="oc" value="<?=$nro_oc?>" size="100">
    </td>
   </tr>
   <tr>
   <?

   $q_origen="select nombre_envio_origen, id_envio_origen from licitaciones_datos_adicionales.envio_origen";
   $res_q_origen=sql($q_origen, "Error al traer Sucursales de Coradir") or fin_pagina();

   $q_destino="select nombre, id_distrito, id_envio_destino from licitaciones.distrito
               left join licitaciones_datos_adicionales.envio_destino using (id_distrito)";
   $res_q_destino=sql($q_destino, "Error al traer las provincias destino del envío") or fin_pagina();

   ?>
    <td colspan="2">
      <table width="100%"><tr>
        <td align="center" id='ma'>Origen Envío:&nbsp;&nbsp;

           <select name="origen">
           <?
           echo "$origen_envio";
           while (!$res_q_origen->EOF) {
           	  $id_or=$res_q_origen->fields['id_envio_origen'];
           	 ?>
             <option value="<?=$id_or?>"
           	  <? if ($id_or==$origen_envio) echo "selected"?>><?=$res_q_origen->fields['nombre_envio_origen']?></option>
           <? $res_q_origen->MoveNext(); } ?>
           </select>
        </td>
        <td align="center" id='ma'>Provincia Destino Envío:&nbsp;&nbsp;
           <select name="destino">
           <? while (!$res_q_destino->EOF) {
           	  $id_dest=$res_q_destino->fields['id_envio_destino'];
           	  ?>
             <option value="<?=$id_dest?>"
              <? if ($id_dest==$destino_envio) echo "selected"?>><?=$res_q_destino->fields['nombre']?></option>
           <? $res_q_destino->MoveNext(); } ?>
           </select>
        </td>
       </tr></table>
     </td>
   </tr>
   <tr><td colspan="2">
   <? // consulta para traer los datos de los transportes
     $q_trans="select nombre_transporte, id_transporte, comentarios_transporte,direccion_transporte,telefono_transporte from licitaciones_datos_adicionales.transporte";
     $res_q_trans=sql($q_trans, "Error al traer datos de los Transportes") or fin_pagina();
     $comentarios_arreglo=array();
     //$i=0;?>
     <script>
        var comentarios=new Array();
        var telefono=new Array();
        var direccion=new Array();
        var contacto1=new Array();

     <? while (!$res_q_trans->EOF) {
	       $i=$res_q_trans->fields['id_transporte'];
	       $nom_tran="select nombre from licitaciones_datos_adicionales.transporte_contactos where id_transporte=$i";
           $tran=sql($nom_tran, "Error al traer datos de los Transportes") or fin_pagina();
	        ?>
	       direccion[<?=$i?>]='<?=$res_q_trans->fields['direccion_transporte'];?>';
	       telefono[<?=$i?>]='<?=$res_q_trans->fields['telefono_transporte'];?>';
	       contacto1[<?=$i?>]='<?=$tran->fields['nombre'];?>';

	 <?  $res_q_trans->MoveNext(); } ?>

		function comentarios_transporte2() {
		  var id_transporte;
		  if (document.all.transporte.value!=-1) {
		        id_transporte=document.all.transporte.options[document.form1.transporte.selectedIndex].value;
		        document.all.direccion1.value=direccion[id_transporte];
		        document.all.telefono.value=telefono[id_transporte];
		        var nom=telefono[id_transporte];
		        document.all.contacto2.value=contacto1[id_transporte];
		  }
		  else {
                document.all.comentarios_transporte.value="";
		  }
		} //de la funcion
     </script>
     <?
     $res_q_trans->Move(0);
    // print_r($comentarios_arreglo);
   ?>
   <table align="center" width="100%">
    <tr>
     <td align="center" id='ma'>Datos del Transporte
     <td align="center" id='ma'>Direccion</td>
     <td align="center" id='ma'>Telefono</td>
     <td align="center" id='ma'>Contacto</td>
    </tr>
     <tr align="center">
     <td align="center" id='ma'>
      <select name="transporte" onchange="comentarios_transporte2();">
        <option value="-1">Seleccionar un Transporte</option>
       <? while (!$res_q_trans->EOF) {

       	?>
        <option value='<?=$res_q_trans->fields['id_transporte']?>'
           <? if ($id_transporte==$res_q_trans->fields['id_transporte']) echo "selected";?>>
           <?=$res_q_trans->fields['nombre_transporte']?>
        </option>
          <? $res_q_trans->MoveNext();
         } ?>
       </select><br>
       <? 	$link_transporte=encode_link("../logistica/transporte_editor_avanzado.php",array('pagina_viene'=>'preparar_envios.php'));  ?>

        <input type="button" name="nuevo_transporte" value="Nuevo Transporte" title="Agregar un Nuevo Transporte para los Envíos"
        onclick="window.open('<?=$link_transporte?>','','top=50, left=200, width=800px, height=600px, scrollbars=1, status=1,directories=0')">
       </td>
      <?

      ?>

     <td align="center" id="ma"><input class="text_3" readonly type="text" name="direccion1" value=""></td>
     <td align="center" id="ma"><input class="text_3" readonly type="text" name="telefono" value=""></td>
     <td align="center" id="ma"><input class="text_3" readonly type="text" name="contacto2" value=""></td>
     </tr>
     </table>
   </td></tr>
   <tr><td colspan="2">
   <? //if ($boton_viene=="") {
	 $q_control_ns="select cantidad_enviada as total from licitaciones_datos_adicionales.renglones_bultos
	                where id_renglones_oc=$id_renglones_oc and id_envio_renglones=$id_envio_renglones";
	 $res_q_control_ns=sql($q_control_ns, "Error al traer la cantidad de nros de serie para el renglon") or fin_pagina();
	 $cant_ns=$res_q_control_ns->fields['total'];

   $aux_bultos_ocupados=$res_q_envio->fields['cantidad_total'];
   $arreglo_cantidades=Array();
   $k=0;
   if ($aux_bultos_ocupados % $cant_renglones_en_el_envio!=0){
    while ($aux_bultos_ocupados % $cant_renglones_en_el_envio!=0){
   	   $arreglo_cantidades[$k]=ceil($aux_bultos_ocupados/$cant_renglones_en_el_envio);
   	   $aux_bultos_ocupados=$aux_bultos_ocupados-$arreglo_cantidades[$k];
       $cant_renglones_en_el_envio--;
       $k++;
     }
   while ($cant_renglones_en_el_envio!=0) {
        $arreglo_cantidades[$k]=ceil($aux_bultos_ocupados/$cant_renglones_en_el_envio);
        $aux_bultos_ocupados=$aux_bultos_ocupados-$arreglo_cantidades[$k];
        $k++;
        $cant_renglones_en_el_envio--;
       }
   }
   else {
   	 while ($cant_renglones_en_el_envio!=0) {
        $arreglo_cantidades[$k]=ceil($aux_bultos_ocupados/$cant_renglones_en_el_envio);
        $aux_bultos_ocupados=$aux_bultos_ocupados-$arreglo_cantidades[$k];
        $k++;
        $cant_renglones_en_el_envio--;
       }
   }
   //}
   ?>
   <table width="100%">
   <tr><td id='mo' colspan="6">Productos Enviados</td></tr>
   <tr>
     <td id='mo' align="left">Cantidad de Bultos Totales: <?=$res_q_envio->fields['cantidad_total']?></td>
     <td id='mo' colspan="5">&nbsp;</td>
   </tr>
   <tr>
       <td id='mo'>&nbsp;</td>
       <td align="center" id='mo'><b>Números de Serie</td>
       <td align="center" id="mo"><b>Bultos que ocupa</td>
       <td align="center" id='mo'><b>Cantidad Enviada</td>
       <td align="center" id='mo'><b>Número Renglón</td>
       <td align="center" id='mo'><b>Título del Renglón</td>
   </tr>
   <?
    $k=0;
    while (!$res_q_envio->EOF) {
       $id_renglones_bultos=$res_q_envio->fields['id_renglones_bultos'];
   	   $id_renglones_oc=$res_q_envio->fields['id_renglones_oc'];
   	   $bultos_ocupados=$res_q_envio->fields['bultos_ocupados'];
   	   $link_ns=encode_link("numeros_serie.php",array("id"=>$id_renglones_oc,"cant"=>$res_q_envio->fields['cantidad_enviada'],"id_envio_renglon"=>$id_envio_renglones, "id_renglones_bultos"=>$id_renglones_bultos, "pagina"=>"preparar_envios"));
  	?>
     <tr <?=$atrib_tr?>>
       <td align="center" title="Marcar los Renglones para eliminar del Envío">
         <input type="checkbox" name="renglon_eliminar_<?=$k?>" value="1">
       </td>
       <td align="center" title="Cargar los números de serie del renglón">
         <input type="button" name="nro_serie" value="ns" onclick="window.open('<?=$link_ns?>','','top=130, left=250, width=420px, height=450px, scrollbars=1, status=1,directories=0')">
       </td>
       <td align="center"><input type="text" name="bultos_ocupados_<?=$k?>" value="<?=$bultos_ocupados/*if ($bultos_ocupados) echo*//*; else echo $arreglo_cantidades[$k];*/?>" size="5"></td>
       <td align="center"><b><?=$res_q_envio->fields['cantidad_enviada']?></td>
       <td align="center"><b><?=$res_q_envio->fields['codigo_renglon']?></td>
       <td align="center"><b><?=$res_q_envio->fields['titulo_mod']?></td>
       <input type="hidden" name="id_renglones_bultos_<?=$k?>" value="<?=$id_renglones_bultos?>">
     </tr>
   <? $res_q_envio->MoveNext(); $k++;
     } ?>
   <input type="hidden" name="cant_r_envio" value="<?=$k?>">
   </table>
   </td></tr>
 </table>
 <?
    }
 ?>
 <br>
  <table align="center" width="100%">
    <tr>
     <td align="center" id='ma'>Comentarios del Envio<br>
     <textarea name='comentarios_en' cols='95' rows='4' wrap='VIRTUAL' ><?=$comentarios1?></textarea>
     </td>
     </tr>
     </table>
  <?
  if($tipo!="anulados")
  {
  ?>  
   <table align="center" width="50%">
   <tr>
     <td align="right"><input type="submit" name="guardar" value="Guardar" title="Guardar los Datos para el Envío" <?=$deshab_botones_terminado?> onclick="return control_guardar();"></td>
     <td> <input type="submit" name="eliminar" value="Eliminar Renglones" title="Eliminar los Renglones seleccionados del Envío" onclick="alert ('eliminar'); return control_elim();"> </td>
   </tr>
   <tr>
     <?  $link_adjunto=encode_link("adjunto_remito_envio.php", array("id_envio_renglones"=>$id_envio_renglones));	?>
     <td>
       <input type="button" name="adjunto_remito" value="Imprimir Adjunto Remitos" title="Imprimir Adjuntos para los Remitos Asociados a este Envío"
       <?//=$deshab_botones_terminado?> <?//=$deshab_botones_sin_guardar_datos?> onclick="window.open('<?=$link_adjunto?>','','toolbar=1,location=0,directories=1,status=1, menubar=1,scrollbars=1,left=125,top=10,width=800,height=600')">
     </td>
     <?  $link_etiqueta=encode_link("etiquetas_envios.php", array("id_envio_renglones"=>$id_envio_renglones,"nuevo"=>$nuevos));    ?>
     <td>
      <input type="button" name="etiquetas" value="Imprimir Etiquetas" title="Configurar las Etiquetas para los Envíos"
       onclick="window.open('<?=$link_etiqueta?>','','toolbar=1,location=0,directories=1,status=1, menubar=1,scrollbars=1,left=125,top=10,width=400,height=300')"
       <?//=$deshab_botones_terminado?> <?//=$deshab_botones_sin_guardar_datos?>>
     </td>
     <td><input type="submit" name="terminar_envio" value="Terminar Envío" title="Cerrar el Envío, no se pueden agregar más productos"
          <?=$deshab_botones_terminado?> onclick="return (confirm('¿Está seguro que quiere cerrar este Envío ? \nCuando el Envío esté cerrado solo estará disponible para consulta en el Listado Historial'))"></td>
   </tr>
   </table>
   <?// if ($pagina=="listado"){
   	$link_volver=encode_link("listado_envios.php", array());
   	 ?>
   <table align="center" width="50%">
   <tr>
     <td align="center">
     <?
     if(permisos_check("inicio","permisos_anular_envios"))
     {
     ?>
     <input type="submit" name="anular_envio" value="Anular Envío" title="Anular el Envío" 
   onclick="return (confirm('¿Está seguro que quiere anular este Envío ?'))">
     <?}?>
   &nbsp;&nbsp;
     <input type="button" name="volver" value="Volver" title="Volver al Listado de los Envíos" onclick="location.href='<?=$link_volver?>'"></td>
   </tr>
    </table>
    <?
  }
  else 
  {
  ?>
  <table width="50%" align="center">
   <tr align="center">
   <td align="center">
   <input type="button"  name="boton_volver" value="Volver"  onclick="window.location='../ordprod/listado_envios.php'"> 
   </td>
   </tr>
   </table>
  <?}?> 
   <? //}
   	 if ($flag_mail){
	////////////////////////////////// envío de e-mail avisando //////////////////////////////////////////
		$sql="select distinct u.nombre, u.apellido, id_usuario, u.mail, lider, id_licitacion
			            from licitaciones.licitacion
                        join sistema.usuarios u on (licitacion.lider=u.id_usuario)
                        where id_licitacion=$id_licitacion";
		$result_cons=sql($sql,"No se pudo traer el lider de la Licitación") or fin_pagina();//select
   	$mail = array (0 => "juanmanuel@coradir.com.ar", 1 => "carlos@coradir.com.ar");
	  if ($result_cons->RecordCount()>0){
	  	$mail[]=$result_cons->fields['mail'];
	  	$nombre_lider_mail=$result_cons->fields["apellido"].", ".$result_cons->fields["nombre"];
	  }
	  else{
	  	$sql_lideres="select nombre, apellido, mail from sistema.usuarios join sistema.usr_mail using(id_usuario) where id_grupo=1";
	  	$rta_sql_lideres=sql($sql_lideres, "No se pudo acceder a las direcciones de correo de los líderes de licitaciones") or fin_pagina();
	  	for ($i=0; $i<$rta_sql_lideres->recordCount(); $i++){
	  		$mail[]=$rta_sql_lideres->fields["mail"];
				$rta_sql_lideres->moveNext();
	  	}
	  }
	$para=elimina_repetidos($mail,0);
   	$q_envio_mail="select distinct id_datos_envio, entidad_mod, nro_lic_mod, nro_oc_mod, cantidad_enviada, id_renglones_bultos,
   			titulo_mod, codigo_renglon, nro, r.id_licitacion, nombre_transporte
			from licitaciones_datos_adicionales.renglones_bultos
			left join licitaciones_datos_adicionales.envio_renglones using (id_envio_renglones)
			left join licitaciones.renglones_oc using (id_renglones_oc)
			left join licitaciones.renglon r using (id_renglon)
			left join licitaciones_datos_adicionales.transporte using (id_transporte)
			left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones),
			licitaciones.entrega_estimada e
			where e.id_licitacion=r.id_licitacion and id_envio_renglones=$id_envio_renglones";

   	$res_q_envio_mail=sql($q_envio_mail, "Error al traer los datos de los envíos ") or fin_pagina();

   	$id_datos_envio_mail=$res_q_envio_mail->fields["id_datos_envio"];
   	$id_seguimiento_mail=$res_q_envio_mail->fields["nro"];
   	$id_licitacion_mail=$res_q_envio_mail->fields["id_licitacion"];
   	$cliente_mail=$res_q_envio_mail->fields["entidad_mod"];
   	$transporte_mail=$res_q_envio_mail->fields["nombre_transporte"];
   	$nro_oc_mail=$nro_oc;

   	$asunto="Se ha despachado el envío nro. ".$id_envio_renglones." - Lic. ID $id_licitacion_mail\n";
   	$contenido="Envío: ".$id_datos_envio_mail."\nCliente: ".$cliente_mail;
   	$contenido.="\nOrden de compra: ".$nro_oc_mail."\nLicitación: ".$id_licitacion_mail."\nLíder: ".$nombre_lider_mail;
   	$contenido.="\nSeguimiento: ".$id_seguimiento_mail."\nTransporte: ".$transporte_mail;
   	$contenido.="\nUsuario (despachante): ".$usuario;
		$contenido.="\n-------------------------------------------------------\nSe han enviado los siguientes items:\n-------------------------------------------------------\n";
   	for ($i=0; $i<$res_q_envio_mail->recordCount(); $i++){
	   	$cantidad_enviada_mail=$res_q_envio_mail->fields["cantidad_enviada"];
			$codigo_renglon_mail=$res_q_envio_mail->fields["codigo_renglon"];
			$titulo_mod_mail=$res_q_envio_mail->fields["titulo_mod"];
   		$contenido.="\nCantidad: ".$cantidad_enviada_mail."\nRenglón: ".$codigo_renglon_mail."\nTítulo de renglón: ".$titulo_mod_mail."\n";
   		
   		
   		$id_renglon=$res_q_envio_mail->fields["id_renglones_bultos"];
	   	if ($id_renglon){
	   		$select_ren="select nro_serie from licitaciones_datos_adicionales.nro_serie_renglon where id_renglones_bultos=$id_renglon";
		   	$select_renglon=sql($select_ren, "Error al traer los numeros de series") or fin_pagina();
	   		
		   	if ($select_renglon->recordCount()>0){
		   		$contenido.="\nNúmeros de Serie de los Productos del Renglón";
		   	}
		   	else{
		   		$contenido.="\nNo Existen Números de Serie para el Renglón";
		   	}
		   	for ($t=0; $t<$select_renglon->recordCount(); $t++){
			   	$nro_series=$select_renglon->fields["nro_serie"];
			   	$ii=$t+1;
				$contenido.="\nNúmero de Serie $ii: ".$nro_series."";
		   		$select_renglon->moveNext();
	   	    }
   	    }
   		
   	    $res_q_envio_mail->moveNext();
   	}
   	$contenido.="-------------------------------------------------------";
   	
   	//echo $select_renglon->recordCount() . "<br> <br>";
   	//echo "$id_renglon <br> <br>";
   	//echo "$para <br>, $asunto <br>, $contenido<br>,";
   	//die();
   	
   	enviar_mail($para, $asunto, " ".$contenido, "", "", "", 0);
		//////////////////////////////////////////////////////////////////////////////////////////////////////
}
   	 ?>
</form>
<script>
if(typeof(document.all.transporte)!="undefined" && document.all.transporte.value!=-1)
	comentarios_transporte2();
</script>
</body>
</html>
<?echo fin_pagina();?>
