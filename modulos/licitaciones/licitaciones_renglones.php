<?php
/*
Autor: ????????
Modificado por:
$Author: mari $
$Revision: 1.72 $
$Date: 2007/01/05 19:58:22 $
*/



require_once("../../config.php");
require_once("funciones.php");

//obtengo el nro de la licitacion
$id_licitacion=$parametros["licitacion"] or $id_licitacion=$parametros["ID"] or $id_licitacion=$parametros["id_licitacion"];
//echo "cantidad de filas".$_POST["cantidad_renglones"];

if($_ses_global_lic_o_pres=="pres")
 $pagina_padre="../presupuestos/presupuestos_view.php";
else
 $pagina_padre="licitaciones_view.php";


 

 

//esto es para mostrar los resultados redondeados o no
if ($_POST['redondear']=="Redondear Resultados"){
               //$db->StartTrans();
               $red="update licitaciones.licitacion set redondear=0 where id_licitacion=$id_licitacion";
               sql($red) or fin_pagina();
               //$db->execute($red) or die($red."<br>".$db->errorMsg());
               //$db->CompleteTrans();
}

elseif ($_POST['redondear']=="No Redondear Resultados") {
	//$db->StartTrans();
    $red="update licitaciones.licitacion set redondear=1 where id_licitacion=$id_licitacion";
    sql($red) or fin_pagina();
    //$db->execute($red) or die($red."<br>".$db->errorMsg());
    //$db->CompleteTrans();
   }



///////////////////////////////////////////////

$fecha = date("Y-m-d h:i:s",mktime());
$usuario = $_ses_user["id"];
if ($_POST["cambiar_lider"]) {
	//print_r($_POST);
	$lider=$_POST["lider_lic"] or $lider="NULL";
	$patrocinador=$_POST["patrocinador_lic"] or $patrocinador="NULL";
    $responsable_apertura = $_POST["responsable"] or $responsable_apertura="NULL";

	if ($lider=="NULL") $error="Debe seleccionar un lider.";
	if ($lider==$patrocinador) $error="Un mismo usuario no puede ser líder y patrocinador.";



	if (!$error) {
		$sql="UPDATE licitacion SET lider=$lider,patrocinador=$patrocinador ,id_responsable_apertura=$responsable_apertura
              where id_licitacion=$id_licitacion";
		sql($sql) or fin_pagina();
	}

	else
		error($error);
//echo $sql;
}

///Codigo para enviar mail si para avisar la descripcion y la oferta



if ($_POST["avisar_descripcion"]){
	            $sql="select usuario_avisar from mail_aviso where usuario_avisa='".$_ses_user['login']."' and tipo=0";
                $resultado=sql($sql) or fin_pagina();
                $para=$resultado->fields['usuario_avisar'];
                $sql="select entidad.nombre from licitacion join entidad on licitacion.id_licitacion=$id_licitacion and licitacion.id_entidad=entidad.id_entidad";
		        $resultado=sql($sql) or fin_pagina();
                $entidad=$resultado->fields['nombre'];
                $mailtext=$_POST['contenido'];
                if($_ses_global_lic_o_pres=="pres")
                        $asunto_title="Presupuesto";
                        else
                        $asunto_title="Licitación";

                $asunto="Descripciones listas - $asunto_title Nº $id_licitacion - Entidad: $entidad ";
                if ($_ses_user['login']=="juanmanuel")
                     $para_oculto="juanmanuel@coradir.com.ar";
                enviar_mail($para,$asunto,$mailtext,'','','',0,$para_oculto);
}

if ($_POST["avisar_oferta"]) {
	         $sql="select usuario_avisar from mail_aviso where usuario_avisa='".$_ses_user['login']."' and tipo=1";
	         $resultado=sql($sql) or fin_pagina();
             $para=$resultado->fields['usuario_avisar'];
             $sql="select entidad.nombre from licitacion join entidad on licitacion.id_licitacion=$id_licitacion and licitacion.id_entidad=entidad.id_entidad";
	         $resultado=sql($sql) or fin_pagina();
             $entidad=$resultado->fields['nombre'];
             $mailtext=$_POST['contenido'];
             //guardo el texto que se envia en el mail
             $fecha_hoy=date("Y-m-d H:i:s",mktime());
             $sql="select comentario from comentario_avisar_oferta where id_licitacion=$id_licitacion";
             $res=sql($sql,"buscar en comentario oferta") or fin_pagina();
             if ($res->RecordCount() > 0) {
                $sql_oferta="update comentario_avisar_oferta set comentario='$mailtext' where id_licitacion=$id_licitacion";
                sql($sql_oferta,"actualizar en comentario oferta") or fin_pagina();
             }
             else {
                $sql_oferta="insert into comentario_avisar_oferta (id_licitacion,comentario,usuario,fecha)
                          values ($id_licitacion,'$mailtext','".$_ses_user['login']."','$fecha_hoy')";
                sql($sql_oferta,"insertar en comentario oferta") or fin_pagina();
             }
             if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="Presupuesto";
                             else
                             $asunto_title="Licitación";
             $asunto="Oferta lista - $asunto_title Nº $id_licitacion - Entidad: $entidad ";


             if ($_ses_user['login']=="juanmanuel")
                    $para_oculto="juanmanuel@coradir.com.ar";
             enviar_mail($para,$asunto,$mailtext,'','','',0,$para_oculto);
}



////////////////////////////////////////////////////
//guardo la modificacion del texto que se envia en el mail

if ($_POST['cambiar_coment']) {
 $coment=$_POST['comentario_avisar_oferta'];
 if ($coment != "") {
 $sql="select comentario from comentario_avisar_oferta where id_licitacion=$id_licitacion";
 $res=sql($sql,"buscar en comentario oferta") or fin_pagina();
     if ($res->RecordCount() > 0) {
         $sql_oferta="update comentario_avisar_oferta set comentario='$coment' where id_licitacion=$id_licitacion";
         sql($sql_oferta,"actualizar en comentario oferta") or fin_pagina();
     }
 }
}
/////////////////////////////////////////////////
switch ($_POST['boton'])
{
 case "Agregar Renglon":
                $link=encode_link("licitaciones_renglones_oferta.php",array("id_licitacion"=>$id_licitacion,"volver"=>"licitaciones_renglones.php"));
                header("Location:$link");
                break;

 case "Ver Descripciones":
               $link=encode_link("vista_previa.php", array("licitacion" =>$id_licitacion,"id_renglon"=>$_POST['radio_renglon'],"modificacion" => 1,"volver"=>"licitaciones_renglones.php"));
               header("Location:$link");
               break;
 case "Modificar Renglon":
                $link=encode_link("licitaciones_renglones_oferta.php",array("id_licitacion"=>$id_licitacion,"id_renglon"=>$_POST['radio_renglon']));
                header("Location:$link");
                break;
 case "Eliminar Renglon":
               echo kill_reng($_POST['radio_renglon']);
               break;
 case "Actualizar Dolar y Ganancia":
	      $db->StartTrans();
	      $valor_dolar=$_POST['valor_dolar'];
	      $sql="update licitacion set valor_dolar_lic = $valor_dolar where id_licitacion = $id_licitacion" ;
	      sql($sql) or fin_pagina();
	      //actualizo las ganancias
	      $cantidad=$_POST["cantidad_renglones"] ;
	      $i=0;
	      //actualizo los valores
	      while ($i<$cantidad) {
	          $ganancia=$_POST["ganancia$i"];
 		       if (($ganancia > 0) && ($ganancia <=1)) {
             	         $id_renglon = $_POST["renglon$i"];
             			 $sql="update renglon set ganancia = $ganancia where id_renglon = $id_renglon" ;
             			 sql($sql) or fin_pagina();
                         }

		      $i++;
 	         }//del while

  		  $db->CompleteTrans();
		 break;

 case "Actualizar  Ganancias":
         $db->StartTrans();
		//actualizo las ganancias
		$cantidad=$_POST["cantidad_renglones"] ;
		$i=0;
		//actualizo los valores
		while ($i<$cantidad) {
		         $ganancia=$_POST["ganancia$i"];
  			     if (($ganancia > 0) && ($ganancia <=1)) {
                  			      $id_renglon = $_POST["renglon$i"];
                  			      $sql="update renglon set ganancia = $ganancia where id_renglon = $id_renglon" ;
                  			      sql($sql) or fin_pagina();
                                  }

		          $i++;
		}
      	$db->CompleteTrans();
        break;

 case "Oferta":
           $link=encode_link("renglon_alternativa.php",array("licitacion"=>$id_licitacion,"volver"=>"licitaciones_renglones.php"));
 	       header("location: $link") or die();
	       break;

 case "Terminar":
 		  $db->StartTrans();
   	      $query_monto="SELECT licitacion.monto_ofertado FROM licitacion WHERE id_licitacion=$id_licitacion";
	      $resultado_monto=sql($query_monto) or fin_pagina();
	      $numero_ofertas=$resultado_monto->RecordCount();

	      if($numero_ofertas>0) $aux=$resultado_monto->fields['monto_ofertado'];
	      if(($numero_ofertas==0)||($aux==0)){
	            $monto_ofertado=$_POST['monto_ofertado'];
  		        $actualizar_monto="UPDATE licitacion SET monto_ofertado=$monto_ofertado WHERE id_licitacion=$id_licitacion";
 		        sql($actualizar_monto) or fin_pagina();
  	     }



	     $query_control="SELECT * from oferta_licitacion WHERE id_licitacion=$id_licitacion";
         $control=sql($query_control) or fin_pagina();
         $cantidad_ofertas=$control->RecordCount();
          if($cantidad_ofertas>0) {
       		   $nombre_excel=genera_cotizacion_licitacion($id_licitacion);
       		   //genera el excel para el cd de oferta
       		   $nombre_excel_cd=genera_cotizacion_licitacion_cd($id_licitacion);
               //insertamos en entregar_lic el nombre del archivo  e indicamos
        	  //que es la oferta de esta licitacion, y que esta lista para imprimir
       		   $query_arch="update entregar_lic set oferta_subida=1, archivo_oferta='$nombre_excel' where id_licitacion=$id_licitacion";
        	   sql($query_arch) or fin_pagina();
        	  //log
        	  $query1="insert into log_check_verde (id_licitacion,fecha,id_usuario,accion) values ($id_licitacion,'$fecha',$usuario,2)";
              sql($query1) or fin_pagina();

         	//enviar mail avisando que se realizo la oferta
            $contenido = "Se generó la oferta de la licitacion ID $id_licitacion\n";
            $contenido .= "Archivo: $nombre_excel\n";
            $asunto = "Se generó la oferta de la licitación ID $id_licitacion";
            enviar_mail(to_group("licitaciones"),$asunto,$contenido,"","","");
            //envio mail con contenido del texto en la apertura
			$sql="select fecha_apertura,entidad.nombre as entidad,usuarios.nombre,apellido,
			      distrito.nombre as distrito,informacion,id_responsable_apertura
          	      from licitacion
          		  join entidad using(id_entidad)
          		  join distrito using (id_distrito)
         		  join ver_apertura using (id_licitacion)
	              left join responsables_apertura using (id_responsable_apertura)
			      left join usuarios using (id_usuario)
         		  where id_licitacion=$id_licitacion";
			$res=sql($sql,"datos licitacion") or fin_pagina();
			$info=$res->fields['informacion'];
			$id_responsable_apertura=$res->fields['id_responsable_apertura'];
			if ($info != "" || $info!=null) {
  			      $entidad=$res->fields['entidad'];
                  $distrito=$res->fields['distrito'];
                  $fecha_apertura=Fecha($res->fields['fecha_apertura']);
                  $hora_apertura=Hora($res->fields['fecha_apertura']);
                  $asunto = "Datos a tener en cuenta para la apertura de la licitacion $id_licitacion";
                  $contenido = "Se generó la oferta de la licitacion ID $id_licitacion\n \n";
                  $contenido.="Entidad $entidad \nDistrito: $distrito \n";
                  $contenido.="Fecha Apertura: $fecha_apertura         Hora Apertura: $hora_apertura \n";
                  if ($id_responsable_apertura !="" || $id_responsable_apertura !=null) {
                  	  $contenido.="Responsable de la apertura: ".$res->fields['apellido']." ".$res->fields['nombre']."\n\n";
          		  }
			$contenido.="Datos a tener en cuenta para la apertura de la licitacion $id_licitacion\n";
			$contenido.=$info;
			enviar_mail(to_group("apertura"),$asunto,$contenido,"","","");
			}

		   $link_fin = encode_link("$pagina_padre",array("cmd1"=>"detalle","ID"=>$id_licitacion));
		   echo "<html><head><script language=javascript>";
           echo "window.opener.document.location.href='$link_fin';window.opener.focus();window.close();";
           echo "</script></head></html>";
 	       
           }

           else {
                 $informar="<font color='red'><b>Error: No esta armada la oferta</b></font>";
                 echo "<script>alert ('Error: No esta definida la combinacion de renglones para armar la oferta')</script>";
                 }
            $db->CompleteTrans();
            break;

 case "Cancelar":
               $link=encode_link("realizar_oferta.php", array("licitacion" =>$id_licitacion));
                header("location: $link") or die();
                break;
}



if ($_POST['boton_duplicar'] == "Duplicar Renglon") {
        if ($_POST["radio_renglon"]){
                    $msg=duplicar_renglon($id_licitacion,$_POST["radio_renglon"],1);
                    if ($msg) aviso_duplicar($msg,$msg_destino);
                    }
                    else aviso("Error:Debe Elegir un renglon");
       //die();
}//del duplicar renglon



if ($_POST['id_firmante_lic']){
    $id_firmante_lic=$_POST['id_firmante_lic'];
    $sql="update licitacion set id_firmante_lic=$id_firmante_lic where id_licitacion=$id_licitacion";
    sql($sql) or fin_pagina();
}



$sql="select licitacion.*,entidad.*,tipo_entidad.nombre as tipo_entidad,
        candado.estado as candado,distrito.nombre as nbre_dist,simbolo,
        firmantes_lic.nombre as firmante_nombre,
		u1.apellido||', '||u1.nombre as nombre_lider, u2.apellido||', '||u2.nombre as nombre_patrocinador,
        id_responsable_apertura
        from licitacion
        join entidad using(id_entidad)
        join candado using (id_licitacion)
        left join tipo_entidad using(id_tipo_entidad)
        join distrito using(id_distrito)
        join moneda using(id_moneda)
        left join firmantes_lic using(id_firmante_lic)
        left join sistema.usuarios u1 on (lider=u1.id_usuario)
		left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
        left join responsables_apertura using (id_responsable_apertura)
        where id_licitacion=$id_licitacion
        ";



$resultado_licitacion=sql($sql) or fin_pagina();
$simbolo_moneda=$resultado_licitacion->fields["simbolo"];
$firmante_nombre=$resultado_licitacion->fields["firmante_nombre"];
$responsable=$resultado_licitacion->fields["responsable"];
if ($parametros["pv"])
        informacion_usuario($id_licitacion);



if($_POST['poner_check']=="Poner Check")
          {//agregamos el check a la licitacion
          $db->StartTrans();
          $query="update licitacion set check_lic=1 where id_licitacion=$id_licitacion";
          sql($query) or fin_pagina();
		  //log del chack
		  $query1="insert into log_check_verde (id_licitacion,fecha,id_usuario,accion) values ($id_licitacion,'$fecha',$usuario,1)";
          sql($query1) or fin_pagina();
          $db->CompleteTrans();
          $link=encode_link("licitaciones_renglones.php", array("id_licitacion" =>$id_licitacion));
          header("location:$link") or die();
          }

elseif($_POST['sacar_check']=="Sacar Check")
          {//quitamos el check a la licitacion
          $db->StartTrans();
          $query="update licitacion set check_lic=0 where id_licitacion=$id_licitacion";
          sql($query) or fin_pagina();
		  $query1="insert into log_check_verde (id_licitacion,fecha,id_usuario,accion) values ($id_licitacion,'$fecha',$usuario,0)";
          sql($query1) or fin_pagina();
          $db->CompleteTrans();
          $link=encode_link("licitaciones_renglones.php", array("id_licitacion" =>$id_licitacion));
          header("location:$link") or die();
          }
//if ($_POST['producto']!="") $_POST['boton']="Agregar Renglon";
?>

<script src="../../lib/fns.js"></script>
<script languaje="javascript">
function control_terminar(){
if(document.all.monto_ofertado.value==''){
   alert('No se puede terminar  porque aun no se ha cargado la oferta.');
   return false;
  }

if (document.all.firmante_nombre.value==''){
   alert('Falta elegir Firmante');
   return false;
  }
return true;
}



function cuerpo(){
 var valor;
 valor=prompt("Ingrese el texto a enviar en el mail","");
 if (!valor)
  return false;
 document.all.contenido.value=valor;
 return true;
}



//funciones de cargar firmante

var ventana_firmante="";
function cargar_firmante(){
document.all.id_firmante_lic.value=ventana_firmante.document.all.select_firmante.options[ventana_firmante.document.all.select_firmante.options.selectedIndex].value;
document.form1.submit();
//document.all.firmante_text.value=ventana_firmante.document.all.select_firmante.options[ventana_firmante.document.all.select_firmante.options.selectedIndex].text;
//document.all.id_activo.value=ventana_firmante.document.all.select_firmante.options[ventana_firmante.document.all.select_firmante.options.selectedIndex].value;
}
</script>
<?php
include("../ayuda/ayudas.php");
?>
<meta Name="generator" content="PHPEd Version 3.2 (Build 3220 )   ">
<title>Renglones</title>
<script languaje="javascript">
<?
//si el candado esta puesto, la funcion habilita_botones()
//no debe habilitar ningun boton salvo el de Ver Descripciones
if($resultado_licitacion->fields['candado']==0){
       echo"
       function habilita_botones(){
       document.all.boton[1].disabled=0;
       document.all.boton[2].disabled=0;
       document.all.boton[3].disabled=0;
       document.all.boton[4].disabled=0;
       document.all.boton[5].disabled=0;
       document.all.boton[6].disabled=0;
       document.all.boton[7].disabled=0;
       document.all.boton[8].disabled=0;
       document.all.boton_duplicar.disabled=0;
       document.all.boton_duplicar_ol.disabled=0;
       }";
       }
        else{
          echo "
          function habilita_botones(){
          document.all.boton[7].disabled=0;
          }";
         }
?>



function chequea_radio(indice){
 if (document.all.radio_renglon.length>1)
      document.all.radio_renglon[indice].checked="true";
      else
      document.all.radio_renglon.checked="true";
 habilita_botones();
}



function switch_func(valor,link){
  var objeto;
  objeto=eval("window.document.all.boton"+valor);
  if (objeto.value=="agregar")
     {
     wproductos=window.open(link,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=40,top=100,width=700,height=400,resizable=1');
     objeto.value="eliminar";
     }
   else
   {
   eliminar(valor);
   objeto.value="agregar";
   }
}



function chequear_dolar()
   {
   if (window.document.all.valor_dolar.value==""){
        alert('Debe ingesar un valor para el Dolar');
        return false;
        }
   else
      return true;
   }
 </script>        
<?
echo $html_header;
$link=encode_link("licitaciones_renglones.php", array("licitacion" => $id_licitacion));
?>
<!--Menu Contextual -->
<div id="ie5menu" class="skin1" onMouseover="highlightie5()" onMouseout="lowlightie5()" onClick="jumptoie5();">
<div class="menuitems" url="javascript:document.all.boton[6].click();">Modificar Renglon</div>
<div class="menuitems" url="javascript:document.all.boton_duplicar.click()">Duplicar Renglon</div>
<div class="menuitems" url="javascript:document.all.boton[7].click();">Ver Descripciones</div>
<hr>
<div class="menuitems" url="javascript:document.all.boton[8].click()">Eliminar Renglon</div>
</div>
<!--Espacio de logs del poner chek -->
<div id="Log_poner_check" style="display:none; overflow:auto; height:60;">
<TABLE width="100%">
	<TR id="mo">
	<TD width="25%">
	Fecha
	</TD>
	<TD width="30%">
	Usuario
	</TD>
	<TD>
	Acción
	</TD>
	</TR>
	<?
    $query_log_check="select fecha,usuarios.nombre,usuarios.apellido,accion from log_check_verde join usuarios using(id_usuario) where id_licitacion=$id_licitacion";
	$result_log_check = sql($query_log_check,"Error en lista de logs: ".$query_log_check);
	while (!$result_log_check->EOF) {
	?>
	<TR id="ma">
	<TD>
	<? echo fecha($result_log_check->fields["fecha"]).'['.hora($result_log_check->fields["fecha"]).']';?>
	</TD>
	<TD>
	<? echo $result_log_check->fields["nombre"].' '.$result_log_check->fields["apellido"]?>
	</TD>
	<TD>
	<? switch ($result_log_check->fields["accion"]){
		case 0: echo "Quitó el check en la licitación actual";
		break;
		case 1: echo "Colocó el check en la licitación actual";
		break;
		case 2: echo "Termino la oferta.";
		break;
	}
	?>
	</TD>
	</TR>
	<?
	$result_log_check->MoveNext();
	}?>
</TABLE>
</DIV>
<FORM  action="<? echo $link; ?>" name="form1" method="POST">
<INPUT TYPE="HIDDEN"  name="accion_tomar">
<font size='3'><center>
<?
if ($informar)aviso($informar);
if($_POST['boton']=="Terminar") echo "<br>";
?>
<table align="center" border=0 width="100%">
    <tr>
    <td id="mo">
    <?
    	if ($_ses_global_lic_o_pres!="pres"){
   ?>
   	<input type="button" name="mailto" value="Mail to ..." onclick='document.location.href=
 			"mailto:?subject=Mail%20licitación%20id%20<?=$id_licitacion?>&body=Id:%20<?=$id_licitacion?>%0D%0A"+
			"Entidad:%20<?=$resultado_licitacion->fields['nombre']?>%0D%0A"+
			"Dirección:%20<?=$resultado_licitacion->fields['dir_entidad']?>%0D%0A"+
			"Número:%20<?=$resultado_licitacion->fields['nro_lic_codificado']?>%0D%0A"+
			"Expediente:%20<?=$resultado_licitacion->fields['exp_lic_codificado']?>%0D%0A"+
			"Fecha%20de%20apertura:%20<?=Fecha(substr($resultado_licitacion->fields["fecha_apertura"],0, 10))?>%0D%0A"+
			"Hora%20de%20apertura:%20<?=substr($resultado_licitacion->fields["fecha_apertura"],11)?>%0D%0A"+
			"Líder:%20<?=$resultado_licitacion->fields["nombre_lider"]?>%0D%0A"+
			"Patrocinador:%20<?=$resultado_licitacion->fields["nombre_patrocinador"]?>%0D%0A"'>
    	<?
    	}else{
    		echo("&nbsp");
    	}
    	?>
    </td>
    <td colspan="4" align="center" width=85% id="mo">
    <font color="#E0E0E0">
    <?
      if($_ses_global_lic_o_pres=="pres")
                   $datos_de="del Presupuesto";
                   else
                   $datos_de="de la Licitación";
     ?>
    </font>
    <b>Datos <?=$datos_de?>
    </td>
    <td width=2% align=center>
    <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_realizar_of.htm" ?>', 'REALIZAR OFERTA')" >
    </td>
    </tr>
    <tr>
    <td>
    <b>
    <?
    if($_ses_global_lic_o_pres=="pres")
                $datos_de="Presupuesto";
                else
                $datos_de="Licitación";
    echo $datos_de;
   ?>
   <font color="#FF0000">
   <?
    echo $id_licitacion;
    if($resultado_licitacion->fields['candado']!=0)
        {
        if($_ses_global_lic_o_pres=="pres")
                    $datos_de="e presupuesto";
                    else
                    $datos_de="a Licitación";
        echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Est$datos_de solo puede verse, pero no modificarse'>";
        $candado="disabled";
        }
       else
           $candado="";
   ?>
   </font>
   </td>
   <td>
   <b>
   Entidad
   </td>
   <td>
    <font color="#FF0000">
    <b>
    <? echo $resultado_licitacion->fields['nombre'];  ?>
    </font>
    <?$nbre_dist=$resultado_licitacion->fields['nbre_dist'];?>
   </td>
   <td align="right" bgcolor="Silver">
   <?
   if (permisos_check("inicio","poner_check_lic"))
                    $visibility="visible";
                    else
                   $visibility="hidden";
   if($resultado_licitacion->fields['check_lic']==0)
     {
     ?>
      <input type="submit" name="poner_check" style="visibility:<?=$visibility?>" value="Poner Check">
     <?
     }
     else
       {
       ?>
       <input type="submit" name="sacar_check" style="visibility:<?=$visibility?>" value="Sacar Check">
	   <?
       }
       ?>
    </td>
    <td bgcolor="Silver" align="right">
	Ver Logs
    </td>
    <td bgcolor="Silver">
    <INPUT type="checkbox" name="logs" value="1" onclick="javascript:(this.checked)?Mostrar('Log_poner_check'):Ocultar('Log_poner_check');">
    </td>
 </tr>
</table>
<table align="center" width=100%>
<input type="hidden" name="contenido" value="">
<?
if ($resultado_licitacion->fields['id_moneda']==1)  {
?>
 <tr>
   <td align="left">
   </td>
   <td align="right">
     <? $link_notas_adic=encode_link("notas_adicionales.php",array("id_licitacion"=>$id_licitacion)) ?>
      <input type=button name=notas_adic value="Notas Adicionales" onclick="window.open('<?=$link_notas_adic?>')"> 
   </td>
   <td align="left">
   <?
    $link=encode_link("verificacion_final.php",array("id_licitacion"=>$id_licitacion));   
   ?>
   <input type="button" name="boton" value="Protocolo" style="width:130;" onclick="window.open('<?=$link?>')"    <?=$candado?> >
   <input type="hidden" name="avisar_descripcion" value=0>
   </td>
   <td align="left">
   <?
   $link=encode_link("licitaciones_enviar_mail.php",array("tipo"=>"oferta","id_licitacion"=>$id_licitacion));
   ?>
   <input type="button" name="boton" value="Avisar Oferta" style='width:130;' onclick="window.open('<?=$link?>')" <?=$candado?> >
   <input type="hidden" name="avisar_oferta" value=0>
   </td>
   </tr>
   <tr>
   <td>
     <table>
      <tr>
        <td>
          <font color="#000000">
             <b> Dolar </b>
          </font>
        </td>
        <td>
        <input type="text" name="valor_dolar" size="5" value="<? echo $resultado_licitacion->fields['valor_dolar_lic'];?>">
        </td>
       </tr>
     </table>
   </td>
   <td width="60%">
   <table width=100% border=0>
   <tr>
     <td width="50%" align="center">
     <input type="submit" name="boton" value="Actualizar Dolar y Ganancia" size="5" <?=$candado?> onclick="return chequear_dolar();" >
	 </td>
     <td  align=left style="cursor:hand" title="Consultar Valor del Dolar">
     <img src='<?php echo "$html_root/imagenes/dolar.gif" ?>' border="0"  onclick="window.open('../../lib/consulta_valor_dolar.php','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=0,top=0,width=160,height=140')"  >
     </td>
   </tr>
   </table>
   </td>
   <td>
   <input type="submit" name="boton" <?=$candado?> value="Oferta" style='width:130;' >
   </td>
   <td>
   <input type="button" name="boton"  value="Salir" style='width:130;' onclick='window.close();'>
   </td>
 </tr>
 <?
 } //del if de idmoneda
   else { //coloco las filas sin valor dolar
 ?>
     <tr>
        <? $link_notas_adic=encode_link("notas_adicionales.php",array("id_licitacion"=>$id_licitacion)) ?>
        <td><input type=button name=notas_adic value="Notas Adicionales" onclick="window.open('<?=$link_notas_adic?>')"> </td>
        <td align="center">
         <?
        $link=encode_link("verificacion_final.php",array("id_licitacion"=>$id_licitacion));   
         ?>
         <input type="button" name="boton" value="Protocolo" style="width:130;" onclick="window.open('<?=$link?>')"    <?=$candado?> >
         <input type="hidden" name="avisar_descripcion" value=0>
        </td>
       <td align="center">
        <?
        $link=encode_link("licitaciones_enviar_mail.php",array("tipo"=>"oferta","id_licitacion"=>$id_licitacion));
		
        ?>
        <input type="button" name="boton" value="Avisar Oferta" style='width:130;' onclick="window.open('<?=$link?>')" <?=$candado?> >
        <input type="hidden" name="avisar_oferta" value=0>
        </td>
        <td>
        <input type="submit" name="boton" value="Actualizar  Ganancias" <?=$candado?> size="5"  >
        </td>
        <td>
        <input type="submit" name="boton"  value="Oferta" <?=$candado?> style='width:130;' >
        </td>
        <td>
        <input type="button" name="boton"  value="Salir" style='width:130;' onclick='window.close();'>
        </td>
        </tr>
        <?
        } //del else de valor moneda
       ?>
</table>
<hr>
<?
// lideres y patrocinadores
$sql="select id_usuario,nombre,apellido from usuarios where tipo_lic='L' order by nombre";
$lider=sql($sql) or fin_pagina();
$sql="select id_usuario,nombre,apellido from usuarios where tipo_lic='P' or tipo_lic='L' order by nombre";
$patrocinador=sql($sql) or fin_pagina();
$sql="select id_responsable_apertura,nombre,apellido from responsables_apertura
      join  usuarios  using(id_usuario)
      order by nombre";
$responsable=sql($sql) or fin_pagina();
?>
<table  align="center" border="0" width=95%>
<tr id=mo>
	<td align="center" colspan=4>
		<b>Seleccionar líder y patrocinador para la Licitación</b>
	</td>
</tr>
<tr>
	<td align=center>
		<b>Líder:</b> <select name="lider_lic">
		<option value="">Seleccionar...</option>
<?
		while ($fila=$lider->FetchRow()) {
			echo "<option value='".$fila["id_usuario"]."'";
			if ($fila["id_usuario"]==$resultado_licitacion->fields["lider"]) echo " selected";
			echo ">".$fila["nombre"]." ".$fila["apellido"]."</option>\n";
		}
?>
	</select>
	</td>
	<td align=center>
		<b>Patrocinador:</b> <select name="patrocinador_lic">
		<option value="">Seleccionar...</option>
<?
		while ($fila=$patrocinador->FetchRow()) {
			echo "<option value='".$fila["id_usuario"]."'";
			if ($fila["id_usuario"]==$resultado_licitacion->fields["patrocinador"]) echo " selected";
			echo ">".$fila["nombre"]." ".$fila["apellido"]."</option>\n";
		}
?>
		</select>
	</td>
    <td>
      <b> Responsable: </b>
      <select  name="responsable">
      <option value="">Seleccionar...</option>
<?
		while ($fila=$responsable->FetchRow()) {
			echo "<option value='".$fila["id_responsable_apertura"]."'";
			if ($fila["id_responsable_apertura"]==$resultado_licitacion->fields["id_responsable_apertura"]) echo " selected";
			echo ">".$fila["nombre"]." ".$fila["apellido"]."</option>\n";
		}
?>
      </select>
    </td>
    <? $link_apertura=encode_link("datos_apertura.php",array("id_licitacion"=>$id_licitacion)) ?>
    <td><input type=button name=r value="En la apertura Ver" onclick="window.open('<?=$link_apertura?>')"> </td>
</tr>
<tr>
   <td align="center" colspan=4>
	<input type="submit" name="cambiar_lider" value="Guardar Líder/Patrocinador/Responsable">
	</td>
</tr>
</table>
<hr>
<?
//consulta para saber los datos de los renglones
$query = "select * from (
          SELECT renglon.*,etaps.id_etap,etaps.titulo as titulo_etap,
                 etaps.texto as texto_etap
          FROM renglon left join etaps using(id_etap)
          WHERE id_licitacion = $id_licitacion
          ) as renglon
          left join
          (
          select sum(cantidad*precio_licitacion) as total_renglon,id_renglon
          from producto group by id_renglon
          ) as totales
          using(id_renglon) order by codigo_renglon
          ";
$resultados=sql($query) or fin_pagina();
//Esta asignacion la uso mas adelante
$resultado_renglon=$resultados;
$i=0;
$cantidad_filas = $resultado_renglon->RecordCount();
?>
<center>
<input type=text name=alerta  size=100 class='text_aviso' value='' readonly>
<input type=text name=warning  size=100 class='text_aviso' value='' readonly>
<input type=text name=warning_ganancia  size=100 class='text_aviso' value='' readonly>
</center>
<!-- tabla que me muestra la informacion de los renglones -->
<table  align="center" border="0" width="100%" bordercolor="#580000" id='tabla_renglones'>
<tr id="mo">
<td colspan="7"><b>Renglones existentes</b></td>
</tr>
<tr  id="mo">
           <td  width="3%">&nbsp;   </td>
           <td><b> Renglón</b>    </td>
           <td><b> Cant.  </b>    </td>
           <td><b> Título </b>    </td>
           <td><b>Ganancia</b>    </td>
           <td><b>P. Unitario</b> </td>
           <td><b>P. Total </b>   </td>
</tr>
<?
$i=0;
$titulos_total=array();
$db->StartTrans();
$ganancia_superior=0;
while ( $i< $cantidad_filas ){
     $id_renglon = $resultado_renglon->fields['id_renglon'];
     $titulo_renglon = $resultado_renglon->fields['titulo'];
     $nro_renglon = $resultado_renglon->fields['codigo_renglon'];
     $ganancia = $resultado_renglon->fields['ganancia'];
     if (!$ganancia_superior){
          if ($ganancia>0.81){
                  $texto_ganancia="Hay renglones con una ganancia mayor a 0.81, esto solo es posible con autorización del Ing. Corapi";
                  $ganancia_superior=1;
          }
          else
                $texto_ganancia="";
      }
 
     $cantidad = $resultado_renglon->fields['cantidad'];
     $sin_descripcion=$resultado_renglon->fields['sin_descripcion'];
     $lista_descripcion=$resultado_renglon->fields['lista_descripcion'];
?>
    <input type='hidden' name='<?="renglon$i"?>' value='<?=$id_renglon?>' >
    <tr align='center' bgcolor='<?=$bgcolor_out?>' onclick="chequea_radio(<?=$i?>);" oncontextmenu="chequea_radio(<?=$i?>);" ondblclick="document.all.boton[6].click()">
    <td align='Center'>
    <input type='radio' name='radio_renglon' value='<?=$id_renglon?>' onclick="habilita_botones();"></td>
    </td>
	<td align='Center' >
       <b><?=$nro_renglon?></b>
    </td>
    <td align='Center' >
    <b><?=$cantidad?>
    </td>
    <td align='Left'>
           <table width='100%'>
            <tr>
            <td width='90%'><b><?=$titulo_renglon?></td>
            <td align='rigth' title='Sin Descripción' width='5%'>
            <?
            if ($sin_descripcion)
                   echo "<img align=middle src=../../imagenes/sin_desc.gif border=0>";
            ?>
            </td>
            <td align='rigth' title='Descripción guardada' width='5%'>
            <?
            if ($lista_descripcion)
                   echo "<img align=middle src=../../imagenes/descrip.gif border=0>";
            ?>
            </td>
          </tr>
         </table>
     </td>
     <td align='center'>
     <input type='text' name='<?="ganancia$i"?>' value='<?=$ganancia?>' size='5'>
     </td>
     <?
     //total renglon tiene la suma de los productos de los renglones
     $total_renglon=$resultado_renglon->fields["total_renglon"];
     $total_renglon=number_format($total_renglon,'2','.','');
     if ($resultado_licitacion->fields['id_moneda']==1)
     {
      /*if($_ses_user["login"]=="marcos")
       echo "RENGLON: $nro_renglon ------Total renglon $total_renglon - valor_dolar ".$resultado_licitacion->fields['valor_dolar_lic']." Ganancia $ganancia<br>";*/
      $subtotal_renglon=($total_renglon * $resultado_licitacion->fields['valor_dolar_lic'])/$ganancia;
     }
                else
                   $subtotal_renglon=$total_renglon /$ganancia;

                  $subtotal_renglon_sr=$subtotal_renglon;
                  $subtotal_renglon_cr=ceil($subtotal_renglon);
                  $arreglo_error[$i][0]=$titulo_renglon;
                  $arreglo_error[$i][1]=$subtotal_renglon_sr;
                  $arreglo_error[$i][2]=$subtotal_renglon_cr;
                  $arreglo_error[$i][3]=$resultado_renglon->fields['cantidad'];
                  if ($resultado_licitacion->fields['redondear']==0)
                             $subtotal_renglon=$subtotal_renglon_cr;
                             else
                             $subtotal_renglon=$subtotal_renglon_sr;
                  echo "<td align='center'>";
                    echo "<table width=100% align=center>";
                     echo "<tr>";
                       echo "<td align=center width=5%> <b>$simbolo_moneda </b></td>";
                       echo "<td align=right> <b>".number_format($subtotal_renglon,2,'.','')."</b></td>";
                     echo "</tr>";
                   echo "</table>";
                  echo "</td>";
                  $total_cantidad_renglon=$resultado_renglon->fields['cantidad']*$subtotal_renglon;
                  echo "<td align='center'>";
                    echo "<table width=100% align=center>";
                     echo "<tr>";
                       echo "<td align=center width=5%> <b>$simbolo_moneda </b></td>";
                       echo "<td align=right> <b>".number_format($total_cantidad_renglon,2,'.','')."</b></td>";
                     echo "</tr>";
                   echo "</table>";
                  echo "</td>";
  //en el renglon coloco unicamente el subototal osea el precio unitario falta multiplicarlo por la cantidad
  $titulo_total[$i]=array("titulo"=>$titulo_renglon,"total"=>$subtotal_renglon);
  $sql="update renglon set total = $subtotal_renglon where id_renglon = $id_renglon";
  sql($sql) or fin_pagina();
  echo "</tr>";
  $resultado_renglon->MoveNext();
  $i++;
}//de while ( $i< $cantidad_filas )
$db->CompleteTrans();
echo "</table>";
//print_r($titulo_total);
//comienzo a controlar los titulosy los precios
//$resultado_renglon->move(0);
if ($cantidad_filas > 0) {
	//verifico que los renglones no tengan el mismo nombre
	//o el mismo monto
	$warning="";//cuando el precio es el mismo y difieren los titulos
	$alert="";//cuando los titulos son iguales y difiere el monto
	$registros=0;
	//debe tener por lo menos dos renglones
        for ($i=0;$i<$cantidad_filas;$i++){
                $resultado_renglon->move($i);
		$tl=trim($titulo_total[$i]['titulo']);
		$ml=$titulo_total[$i]['total'];
        //echo "titulo comparar:$tl   *** total:$ml ----  i:$i<br>";
        $j=$i+1;
		do
 		{
        	if ($ml==0)
		break;
        	if ($ml==$titulo_total[$j]['total'] && $tl!=trim($titulo_total[$j]['titulo']))
        	{
	  	       $warning="Existen Renglones con el mismo precio y diferentes títulos";
        	}
		if ($tl==trim($titulo_total[$j]['titulo']) && $ml!=$titulo_total[$j]['total'])
		    $alert="EXISTEN RENGLONES CON EL MISMO TITULO Y DIFERENTE PRECIO";
             $j++;
		}
		while ($j<=$cantidad_filas);
        }
?>
<script>
//llama al menu contextual
if (document.all && window.print) {
ie5menu.className = menuskin;
document.all.tabla_renglones.oncontextmenu = showmenuie5;
document.body.onclick = hidemenuie5;
}

//scripts para el control de los renglones
document.all.alerta.value='<?=$alert?>';
document.all.warning.value='<?=$warning?>';
document.all.warning_ganancia.value='<?=$texto_ganancia?>';
</script>
<?
//muestra el monto y nombre de la/s ofertas realizadas (si es que las hay).
//busco las ofertas en la base de datos.
$sql = " select * from oferta_licitacion
         where id_licitacion=$id_licitacion
         order by id_oferta";
$resultado_oferta=sql($sql) or fin_pagina();
//consulta para poner un title que en la tabla que muestra las ofertas cargadas.
$totales=array();
//$resultado_renglones_oferta=sql($sql) or fin_pagina();
$filas_encontradas =$resultado_oferta->RecordCount();
if($filas_encontradas==0)
            $no_hay_ofertas=0;
            else
            $no_hay_ofertas=1;
echo "<table width='100%' align=center border=0>";
for($i=0;$i<$filas_encontradas;$i++) {
   $id_oferta = $resultado_oferta->fields['id_oferta'];
   //calculo los totales por renglon
   $sql="select * from (
        select sum(renglon.total*renglon.cantidad)as total_renglon,id_renglon
        from licitaciones.renglon
        join licitaciones.elementos_oferta using (id_renglon)
        where id_oferta=$id_oferta group by id_renglon
        )
        as p
        join
        (
        select titulo,codigo_renglon,id_renglon
               from licitaciones.renglon where id_licitacion=$id_licitacion
         ) as u using (id_renglon) order by codigo_renglon";
   $resultado_renglones_oferta=sql($sql) or fin_pagina();
   $str_title="";
   $total=0;
   while(!$resultado_renglones_oferta->EOF){
          $str_title.=$resultado_renglones_oferta->fields['codigo_renglon'];
          $str_title.=" - ";
          $str_title.=$resultado_renglones_oferta->fields['titulo']."\n";
          $total+=$resultado_renglones_oferta->fields['total_renglon'];
          $resultado_renglones_oferta->MoveNext();
         }
     echo "<tr  title='$str_title'>";
     echo "<td width=50%>&nbsp</td>";
     echo "<td bgcolor='#D5D5D5' width=35% align=left>";
     echo "<B>TOTAL OFERTA ".$resultado_oferta->fields['nombre']."</B>";
     echo "</td>";
     echo "<td bgcolor='#D5D5D5' width=15% align='center'>";
     //$total=ceil($total);
     $totales[$i]=$total; //en este arreglo guardo todos los totales para despues buscar el mayor.
     $total=number_format($total,2,',','.');
     echo"<b>";
     echo $simbolo_moneda."   "."<font color='#FF0000'>".$total."</font>";
     echo "</td>";
     echo "</tr>";
     $resultado_oferta->MoveNext();
     } //fin del for que calcula los totales de la oferta
//agregamos firmante de la licitacion
if (isset($_POST['firmante_text']))
    {
    $texto=$_POST['firmante_text'];
    $activo=$_POST['id_activo'];
    }
    else
    {
    $sql="select nombre,id_firmante_lic from firmantes_lic where activo=1";
    $resultado_activo=sql($sql) or fin_pagina();
    $texto=$resultado_activo->fields['nombre'];
    $activo=$resultado_activo->fields['id_firmante_lic'];
    }
?>
<tr>
<? if ($resultado_licitacion->fields['redondear']==1)
        $redondear="Redondear Resultados";
        else
        $redondear="No Redondear Resultados";
   if ($resultado_licitacion->fields['candado']==1)
        $deshabilitar="disabled";
?>
<td >
<input type="submit" name="redondear" value="<?=$redondear;?>" <?=$deshabilitar;?>>
</td>
<td align=center colspan=2>
    <table width=100% align=center class=bordes>
       <tr>
         <td>
           <input type=hidden name='id_firmante_lic' value=''>
           <!--Unicamente se utiliza para control -->
           <input type=hidden name='firmante_nombre' value='<?=$firmante_nombre?>'>
               <font color="blue" onclick="ventana_firmante=window.open('<?=encode_link('ventana_firmante.php',array('onclickcargar'=>'window.opener.cargar_firmante();','onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=40,top=80,width=700,height=150,resizable=1')" style="cursor:hand;">
                <b><u>Firmante de la Licitación:</u>
               </font>
         </td>
         <td>
          <input  type="hidden" name="id_activo" value="<?=$activo;?>">
             <font color=red size=2><b>
             <?
             if (!$firmante_nombre) echo "NO HAY NINGUN FIRMANTE";
                               else echo $firmante_nombre;
            ?>
            </b>
             </font>
         </td>
      </tr>
   </table>
</td>
</tr>
   <?
   $sql="select comentario from comentario_avisar_oferta where id_licitacion=$id_licitacion";
   $res=sql($sql,"buscar en comentario oferta") or fin_pagina();
   if ($res->RecordCount() >0 )  {
      $comen_avisar_oferta=$res->fields['comentario'];
   ?>
   <br>
   <tr>
    <td colspan="3" align="center">
    <table class="bordes">
    <tr>
       <td  align="center">
         <font color="blue"> Texto enviado en mail avisar oferta </font>
       </td>
       <td>
         <input type='submit' name='cambiar_coment' value='Cambiar' title="Cambiar texto avisar oferta">
       </td>
    </tr>
    <?
    $nro=row_count($comen_avisar_oferta,120);
    if ($nro >10) $nro=10;
    ?>
    <tr>
       <td colspan="3" align="center">
         <textarea name='comentario_avisar_oferta' cols="120" rows="<?=$nro?>" ><?=$comen_avisar_oferta?></textarea>
       </td>
    </tr>
    </table>
    </td>
 </tr>
 <br>
 <? } 
if ($resultado_licitacion->fields['redondear']==0){?>
        <tr><td align=left>
        <input type=checkbox class='estilos_check' name=detalle onclick="javascript:(this.checked)?Mostrar('tabla_error'):Ocultar('tabla_error');" <?=$deshabilitar;?>>
        <b>Detalle Redondear Resultados</b></td>
        </tr>
        <tr>
        <td colspan="3">
                <div id='tabla_error' style='display:none'>
                <table width="80%" align="center">
                <tr><td id=mo colspan="2"><b>Diferencia por redondeo </b></td></tr>
                <tr id=ma>
                <td widt=90%>Renglon</td>
                <td>Diferencia</td>
                </tr>
                <?
                for ($j=0; $j<sizeof($arreglo_error); $j++){
                        echo "<tr bgcolor=$bgcolor_out>";
                        echo "<td>";
                        echo "<b>".$arreglo_error[$j][0]."</b>";
                        echo "</td>";
                        $diferencia=($arreglo_error[$j][2] - $arreglo_error[$j][1]) * $arreglo_error[$j][3];
                        echo "<td align=center>";
                                 echo "<table width=100% align=center>";
                                 echo "<tr><td width=5% align=center>";
                                 echo "  <b>".$simbolo_moneda."</b></td><td align=right>";
                                 echo "<font color='#FF0000'>".number_format($diferencia,2,',','.')."</b></font></td></tr>";
                                 echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                        }
                        ?>
                 </table>
                 </div>
       </td>
       </tr>
       </table>
       <?
       } //es la llave que cierra el if de arriba
//if ($resultado_licitacion->fields['redondear']==0)

if(sizeof($totales)>0) $monto_ofertado=max($totales);
                  else $monto_ofertado=0;

echo "<input type='hidden' name='monto_ofertado' value='$monto_ofertado'>";
echo "<input type='hidden' name='cantidad_renglones' value='$cantidad_filas'>";
} //del if de controla la cantidad de renglones y las ofertas
 else
 {
   echo "<input type='hidden' name='monto_ofertado' value='$monto_ofertado'>";
   echo "<table width=100% align=center";
   echo "<tr>";
   echo "<td align=center>";
     if($_ses_global_lic_o_pres=="pres")
      $datos_de="E PRESUPUESTO";
     else
      $datos_de="A LICITACION";
     echo "<b>NO HAY RENGLONES EN EST$datos_de";
    echo "</td>";
  echo "</tr>";
 echo "</table>";
 }
?>
<br>
<hr>
<table align="center" width="100%">
<tr align="center">
 <td>
 <input type="submit" name="boton"  value="Agregar Renglon" <?=$candado?> style='width:120;'>
 </td>
 <td>
 <input type="submit" name="boton"  value="Modificar Renglon" style='width:120;' disabled>
 </td>
 <td>
 <input type="submit" name="boton_duplicar"  value="Duplicar Renglon" style='width:120;'>
 </td>
 <?
 $link_duplicar=encode_link("duplicar_renglon_avanzado.php",array("id_licitacion_original"=>$id_licitacion));
 ?>
 <td>
 <input type="button" name="boton_duplicar_ol"  value="Duplicar OL" style='width:120;' onClick="window.open('<?=$link_duplicar?>')" >
 </td>
 <td>
 <input type="submit" name="boton" value="Ver Descripciones" style='width:120;' disabled>
 </td>
  <td>
 <input type="submit" name="boton"  value="Eliminar Renglon" style='width:120;'  onClick="return confirm('ADVERTENCIA:Se va a eliminar el renglon y sus datos');" disabled>
 </td>
  <td>
   <?if($_ses_global_lic_o_pres=="pres")
      $datos_de="el Presupuesto";
     else
      $datos_de="la Licitación";
   ?>
 <input type="submit" name="boton" value="Terminar" <?=$candado?> style='width:120;' onclick="return control_terminar();">
 </td>
</tr>
</table>
<hr>
<?
if (($_POST['producto']=="")&&(($_POST['boton']=="")|| ($_POST['boton']=="Eliminar Renglon") || ($_POST['boton']=='Actualizar Dolar y Ganancia') || ($_POST['boton']=='Actualizar  Ganancias') || ($_POST['boton']=='Avisar Descripcion') || ($_POST['boton']=='Terminar') || ($_POST['boton']=='Avisar Oferta')))
{
$resultado_renglon->movefirst();
//Los datos de esta consulta la obtengo al principio de la pagina
$cantidad_renglones = $resultado_renglon->RecordCount();
$i =0;
//este if esta para que me pongo un cartel
if ($cantidad_renglones>=1) {
?>
 <table width=100% align=center bgcolor=<?=$bgcolor3?>>
  <tr><td align=center><b> RESUMEN DE LOS RENGLONES</td></tr>
 </table>
<?
}
while ($i<$cantidad_renglones) {
         $id_renglon=$resultado_renglon->fields['id_renglon'];
         $tipo=$resultado_renglon->fields['tipo'];
         $sql="select * from producto
               join  productos using(id_producto)
               where producto.id_renglon = $id_renglon ";
          generar_desc_productos_renglon($resultado_renglon,$i);
          $resultado_renglon->MoveNext();
          $i++;
       }//del while
} //del if prinicpal
?>
</FORM>
<?
fin_pagina()
?>