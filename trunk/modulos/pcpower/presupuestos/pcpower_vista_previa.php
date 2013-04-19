<?PHP
/*AUTOR: MAC
  Fecha: 14/10/04

$Author: mari $
$Revision: 1.4 $
$Date: 2006/04/20 21:15:34 $
*/


include_once("../../../config.php");
include_once("pcpower_funciones.php");
$ganancia_oculta=$parametros["ganancia_oculta"];
//flag que viene de la página realizar_oferta que indica si existieron o no
//cambios en el renglon.

if (!$parametros["volver"]) $volver="pcpower_licitaciones_renglones.php";
                      else  $volver=$parametros["volver"];

$link=encode_link($volver, array("licitacion" =>$parametros['licitacion'],"id_renglon"=>$parametros['id_renglon'],"ganancia_oculta"=>$ganancia_oculta));
$link1=encode_link("pcpower_vista_previa.php", array("licitacion" =>$parametros['licitacion'],"id_renglon"=>$parametros['id_renglon'],"volver"=>$parametros["volver"],"ganancia_oculta"=>$ganancia_oculta));

$nro_licitacion=$parametros['licitacion'];
$nro_renglon=$parametros['id_renglon'];


if($_POST["Generar_Documento"]=="Generar Documento")
{/*$link4= encode_link("enviar_mensajes.php",array("renglon" => $nro_renglon,
                                       "licitacion" => $nro_licitacion,
                                       "pagina"=>'vista_previa'));
 header("Location:$link4");*/
if (!$parametros["volver"]) $volver="pcpower_licitaciones_renglones.php";
                      else  $volver=$parametros["volver"];

 $link=encode_link($volver, array("licitacion" =>$parametros['licitacion'],"id_renglon"=>$parametros['id_renglon'],"word"=>"ok","ganancia_oculta"=>$ganancia_oculta));
 genera_descripcion_renglon($nro_renglon);
 header("Location:$link");
}

$datos_licitacion="SELECT pcpower_licitacion.id_licitacion,pcpower_licitacion.ultimo_usuario,pcpower_licitacion.ultimo_usuario_fecha,pcpower_candado.estado 
                   FROM pcpower_licitacion join pcpower_candado using(id_licitacion) 
                   WHERE id_licitacion=".$nro_licitacion;
$resultados=$db->Execute($datos_licitacion) or die($db->ErrorMsg().$datos_licitacion);

if($resultados->fields['estado']==1)
 $candado="disabled";
else
 $candado="";

//recupero los datos del renglon
$datos_renglon="SELECT pcpower_entidad.nombre,pcpower_renglon.usuario,pcpower_renglon.codigo_renglon,pcpower_renglon.usuario_time,pcpower_renglon.cantidad,pcpower_renglon.titulo,pcpower_renglon.nro_version 
                FROM (pcpower_renglon join pcpower_licitacion on pcpower_licitacion.id_licitacion=pcpower_renglon.id_licitacion) join pcpower_entidad on pcpower_entidad.id_entidad=pcpower_licitacion.id_entidad 
                WHERE id_renglon=$nro_renglon";
$resultados_renglon=$db->Execute($datos_renglon) or die($db->ErrorMsg().$datos_renglon);
    $nro=$resultados_renglon->fields["codigo_renglon"];
    $query_productos="SELECT id FROM pcpower_producto WHERE id_renglon=$nro_renglon";
	$resultados_productos=$db->Execute($query_productos) or die($db->ErrorMsg().$query_productos);
	$id_prod=$resultados_productos->fields['id'];
	$cant_prod=$resultados_productos->RecordCount();
	$c=0;
	$resultados_productos->Move(0);
	while(!$resultados_productos->EOF)
	{$ids_prod[$c]=$resultados_productos->fields['id'];
     $c++;
     $resultados_productos->MoveNext();	
	} 

//controla si ya no se habia creado antes el doc para dar la confirmacion
	//de si lo desea borrar o no.
	$name="Desc_";
    $name.="lic_" ;
    $name.=$nro_licitacion;
    $name.="_renglon_";
    $nro=ereg_replace(" ","_",$nro);
    $name.=$nro;
    $name.=".doc";
    
$query="select nombre from pcpower_archivos where nombre='$name'";
$resultado_nombre=$db->Execute($query) or die($db->ErrorMsg().$query);
$borrar_nombre=$resultado_nombre->RecordCount();

//obtenemos el numero de ok mayor que se dio para ese renglon
	$query="select max(nro_ok) as ok from pcpower_log_renglon where id_renglon=$nro_renglon and tipo_log='OK'";
	$res_ok=$db->Execute($query) or die($db->ErrorMsg().$query);
	$ult_ok=$res_ok->fields['ok'];
    if(!$ult_ok)
     $ult_ok=0;

//obtenemos la ultima modificacion hecha a este renglon.
	$query="select * from pcpower_log_renglon where id_renglon=$nro_renglon and tipo_log='modificacion'";
	$res_modif=$db->Execute($query) or die($db->ErrorMsg().$query);
	

//si dieron OK, controlamos cual es el nro de OK (primero o segundo)
//y actualizamos la informacion necesaria en consecuencia.
if($_POST["OK"]=="Dar OK")
{   
	
	//inicio de transaccion
	$db->StartTrans();
	
	//obtengo el codigo del renglon, dado el id de renglon
	$query="select codigo_renglon from pcpower_renglon where id_renglon=$nro_renglon";
	$res=$db->Execute($query) or die ($db->ErrorMsg().$query);
	$cod_r=$res->fields['codigo_renglon'];
	$fecha_hoy = date("Y-m-d H:i:s"); 	
	
	//insertamos el nuevo ok para ese renglon
	$query="insert into pcpower_log_renglon(nro_ok,id_renglon,usuario_log,logtime,tipo_log)values($ult_ok+1,$nro_renglon,'$_ses_user_name','$fecha_hoy','OK')";
	$db ->Execute($query) or die($db->ErrorMsg().$query);
	
	if($ult_ok==0)
	 $ok=1;
	else
	 $ok=$ult_ok+1;
	$ult_ok++;
/*
	//si tiene un mensaje que avisa que ya esta listo para la revision que se acaba de hacer a este renglon
	//se debe eliminar este mensaje (en forma logica, pero no fisica), ya que ya esta dado el ok de la primera revision
	$query="update mensajes set desestimado='t',comentario='FUE BORRADO justificativo: completo la revision del renglon' where usuario_destino='$_ses_user_login' and comentario ilike 'El renglon $cod_r de la licitacion $nro_licitacion, esta listo para la revision Nº $ok%'";
	$db->Execute($query) or die($db->ErrorMsg().$query);
    //print_r($query);die;
	*/
	//si acepto borrar en el dialogo de confirmacion del boton Dar OK,
	//borramos la entrada del archivo en la BD
    if($_POST["confirmacion"]=="borrar")
    { 
      $query="delete from pcpower_archivos where nombre='$name'";
      $db ->Execute($query) or die($db->ErrorMsg().$query);
           
    }
    
   //cierra transaccion
      $db->CompleteTrans();
      header("Location:$link1");
	//}  
}

if($_POST['Guardar']=="Guardar" || $_POST["g_cambios"]=="si") {
   $fecha_hoy = date("Y-m-d H:i:s");
   //inicio de transaccion
   $db->StartTrans();

   $link1=encode_link("pcpower_vista_previa.php", array("licitacion" =>$parametros['licitacion'],"id_renglon"=>$parametros['id_renglon'],"volver"=>$parametros["volver"],"ganancia_oculta"=>$ganancia_oculta));

   //eliminamos los ok y la ultima modificacion dados para este renglon porque se ha modificado algo.
   $query="delete from pcpower_log_renglon where id_renglon=$nro_renglon";
   $db ->Execute($query) or die($db->ErrorMsg().$query);

   //guardamos la entrada de la modificacion (como ultima)
   //insertamos el nuevo ok para ese renglon
	$query="insert into pcpower_log_renglon(nro_ok,id_renglon,usuario_log,logtime,tipo_log)values(-1,$nro_renglon,'$_ses_user_name','$fecha_hoy','modificacion')";
	$db ->Execute($query) or die($db->ErrorMsg().$query);
    $ult_ok++;

   //le asignamos 1 al campo lista_descripcion de la tabla renglon,
   //para avisar que ya estan las descripciones guardadas, si el checkbox
   //correspondiente esta checkeado

   if($_POST['avisar_desc']=='avisar_desc')
   {$query="update pcpower_renglon set lista_descripcion=1 where id_renglon=$nro_renglon";
    $db ->Execute($query) or die($db->ErrorMsg().$query);
   }
   else //sino, lo ponemos en 0, porque no hay que avisar
   {$query="update pcpower_renglon set lista_descripcion=0 where id_renglon=$nro_renglon";
    $db ->Execute($query) or die($db->ErrorMsg().$query);
   }
   //borramos las descripciones viejas en descripciones_renglon, para dar
   //lugar a las nuevas.
   for($c=0;$c<$cant_prod;$c++)
   {if($_POST['guardar_todos']=="si")
     $query_limpiar="DELETE from pcpower_descripciones_renglon where id = $ids_prod[$c]";
    else
     $query_limpiar="DELETE from pcpower_descripciones_renglon where id = $ids_prod[$c] and borrado=0";
    $db->Execute($query_limpiar) or die(ErrorMsg().$query_limpiar);
   }

    $query="select pcpower_producto.id,pcpower_producto.id_producto,pcpower_producto.desc_gral from pcpower_renglon join pcpower_producto on pcpower_producto.id_renglon=pcpower_renglon.id_renglon and pcpower_renglon.id_renglon=$nro_renglon";

    $resultados1=$db->Execute($query) or die($db->ErrorMsg()."$query");   //$db->ErrorMsg()
	$filas_encontradas=$resultados1->RecordCount();
    $j=0;

	while(!$resultados1->EOF){
		$query_d="select * from descripciones where id_producto=".$resultados1->fields['id_producto'];
        $desc_nuevo=$db->Execute($query_d) or die ($db->ErrorMsg().$query_d);
		$cant=$desc_nuevo->RecordCount();
		while(!$desc_nuevo->EOF)
		 {
		  $query_t="select borrado,titulo from pcpower_descripciones_renglon where id=".$resultados1->fields['id']." and titulo='".$desc_nuevo->fields['titulo']."'";
          $desc_borrado=$db->Execute($query_t) or die ($db->ErrorMsg().$query_t);
          $desc_borrado_cant=$desc_borrado->RecordCount();
          if($desc_borrado_cant>0)
            $titulo_desc=$desc_borrado->fields['titulo'];
          else
            $titulo_desc=$desc_nuevo->fields['titulo'];

           $producto=$resultados1->fields['id'];
           $nombre="text_desc"."$producto"."$titulo_desc";
		   $nombre_desc="name_desc"."$producto"."$titulo_desc";
		   $nombre_desc_check="check_desc"."$producto"."$titulo_desc";
		   $nombre=ereg_replace(" ","_",$nombre);
           $nombre_desc=ereg_replace(" ","_",$nombre_desc);
           $nombre_desc_check=ereg_replace(" ","_",$nombre_desc_check);
		    $contenido=$_POST["$nombre"];
		   $titulo=$_POST["$nombre_desc"];
           //si el check de guardar las descripciones originales es true,
		   //se actualizan las descripciones originales para el producto.
		   if($_POST[$nombre_desc_check]=="guardar")
		   {$query="update descripciones set contenido='$contenido' where id_producto=".$resultados1->fields['id_producto']." and titulo='".$desc_nuevo->fields['titulo']."'";
            $db ->Execute($query) or die($db->ErrorMsg()."$query");
		   }

           if(($contenido!="")&&($desc_borrado_cant==0||(($desc_borrado_cant>0)&&($desc_borrado->fields['borrado']!=1))))
           {
                $titulo=ereg_replace("'","\'",$titulo);
                $contenido=ereg_replace("'","\'",$contenido);
		        $query="INSERT INTO pcpower_descripciones_renglon (id,titulo,contenido,borrado) VALUES ($producto,'$titulo','$contenido',0)";
            }
		   else{
                $titulo_desc=ereg_replace("'","\'",$titulo_desc);
                $contenido=ereg_replace("'","\'",$contenido);
                $query="INSERT INTO pcpower_descripciones_renglon (id,titulo,contenido,borrado) VALUES ($producto,'$titulo_desc','$contenido',1)";
            }
    	    $db ->Execute($query) or die($db->ErrorMsg()."$query");

		   $desc_nuevo->MoveNext();
		  }

		$resultados1->MoveNext();
	}

	//cierra transaccion
     $db->CompleteTrans();
     if($_POST["g_cambios"]=="si")
	  header("Location:$link");
	 else
	  header("Location:$link1");
	//}

}

if($_POST["eliminar"]=="Eliminar")
{
	//inicio de transaccion
	$db->StartTrans();

 //cuando se elimina una descripcion, se hace una baja logica, indicado por el campo
 //"borrado", pero no fisica. La baja fisica se hace solo cuando se aprieta "guardar" o 
 //se elimina el producto asociado
 $id_del=$_POST["eliminar_desc"];
 $id_desc_post=$_POST["eliminar_desc_id"];
 //$query="delete from descripciones_renglon where id=$id_del and id_descripciones=$id_desc_post";
 $query="update pcpower_descripciones_renglon set borrado=1 where id=$id_del and id_descripciones=$id_desc_post";
 $db ->Execute($query) or die($db->ErrorMsg()."$query"); 
 
 //cierra transaccion
 $db->CompleteTrans();

 header("Location:$link1");
}

?>
<html>
<head>
<title>Vista Previa</title>
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">";
</style>

<script>

function guarda_cambios()
{var aux;
 if(document.all.hay_cambios.value==1)
 {aux=confirm('Hay cambios que no han sido guardados\n¿Desea guardarlos?');
  if(aux)
  {
   document.all.g_cambios.value='si';
   document.form1.submit();
  }	
  else 
   location.href='<?echo $link; ?>';
 }
 else 
   location.href='<?echo $link; ?>';
} 

function avisar_cambios()
{
 document.all.hay_cambios.value=1;
 document.all.OK.disabled=true;
 document.all.Imprimir.disabled=true;
 document.all.Generar_Documento.disabled=true;
//esto es para deshabilitar los botones de eliminar cuando se hizo una 
//modificacion y no se guardo 
 if (typeof(document.all.eliminar)!="undefined"){
 	if (document.all.eliminar.length>1){
 	   long=document.all.eliminar.length;
 	   for (i=0;i<long;i++){
 	      document.all.eliminar[i].disabled=true;
 	      }
 	   }
 	else 
 	 document.all.eliminar.disabled=true;
  }
}

function confirma()
{var acepta=confirm('Existe un archivo de Word, generado previamente, que puede no coincidir con los datos actuales.\n\n¿Desea eliminarlo?');
 if(acepta)
  document.all.confirmacion.value='borrar';
} 
function mensaje_confirm()
{var acepta=confirm('¿Desea avisar sobre la finalización de la confección de este renglón?');
 if(acepta)
  document.all.mensaje.value='enviar';
 return true; 
}
function mensaje_confirm_ok()
{var acepta=confirm('¿Desea avisar sobre la finalización del chequeo Nº <?echo $ult_ok+1?> para este renglón?');
 if(acepta)
  document.all.confirmacion.value='borrar';
 return true;  

if(acepta)
  document.all.mensaje.value='enviar_dos';
 return true; 
}
</script>

</head>
<body bgcolor="#E0E0E0">
<center>
<form name="form1" action="<? echo $link1 ?>" method="POST">
<input type="hidden" name="confirmacion" value="">
<input type="hidden" name="mensaje" value="">
<!--campo oculto para no perder el id de producto.-->
<input type='hidden' name='producto' value='$id_prod'>
<?

switch($ult_ok)
 {case 0:echo "<div style='position:relative; width:100%;height:11%; overflow:auto;'>";break;
  //case 1:echo "<div style='position:relative; width:100%;height:12%; overflow:auto;'>";break;
  default:echo "<div style='position:relative; width:100%;height:15%; overflow:auto;'>";break; 
 } 
 
 
//muestro los datos de la licitacion y del renglon en el encabezado de la página.
	echo "<table width='90%' border='0' id=mo>";
	  echo "<tr>";
         $asunto_title="Presupuesto";

     	echo "<td align='left'>Nro $asunto_title: ".$resultados->fields['id_licitacion'];
	    if($candado=="disabled")
			 echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Este presupuesto solo puede verse, pero no modificarse' height='14'>";
     	echo "</td>";
	    echo "<td align='right'>Entidad: ".$resultados_renglon->fields['nombre'];
		echo "</td>";
	echo "</tr>";
	  echo "</table>";
	  echo "<table width='90%' bgcolor=$bgcolor2>";
      echo "<tr>";
	    echo "<td width='20%'><b>Creado por:</b></td>";
	    echo "<td width='35%'>".$resultados_renglon->fields['usuario'];
		echo "</td>";
		$f_h=split(" ",$resultados_renglon->fields['usuario_time']);
		$f_h[0]=fecha($f_h[0]);//damos formato local a la fecha
		$f_h[1]=substr($f_h[1],0,5);//no mostramos los segundos de la hora
		echo "<td width='50%'  align='right'><b>Fecha de creación:</b> ".$f_h[0]." ".$f_h[1];
		echo "</td>";
		 echo "</tr>";
		if($res_modif->fields['usuario_log'] && $res_modif->fields['logtime'])
		{$f_h=split(" ",$res_modif->fields['logtime']);
         $f_h[0]=fecha($f_h[0]);//damos formato local a la fecha
		 $f_h[1]=substr($f_h[1],0,5);//no mostramos los segundos de la hora
		 echo "<tr>";
	     echo "<td width='20%'><b>Ultima modificación:</b></td>";
	     echo "<td width='35%'>".$res_modif->fields['usuario_log'];
		 echo "</td>";
		 echo "<td width='50%'  align='right'><b>Fecha de modificación:</b> ".$f_h[0]." ".$f_h[1];
		 echo "</td>";
		 echo "</tr>";
		} 
		 
		//traemos los ok del renglon y generamos el encabezado en consecuencia
		$query="select nro_ok,usuario_log,logtime from pcpower_log_renglon where id_renglon=$nro_renglon and tipo_log='OK'";
	    $res=$db->Execute($query) or die($db->ErrorMsg().$query);
        while(!$res->EOF)
        {$f_h=split(" ",$res->fields['logtime']);
         $f_h[0]=fecha($f_h[0]);//damos formato local a la fecha
		 $f_h[1]=substr($f_h[1],0,5);//no mostramos los segundos de la hora
         echo "<tr>";
	     echo "<td><b>OK Nº: ".$res->fields['nro_ok']."</b></td>";
	     echo "<td>".$res->fields['usuario_log'];
		 echo "</td>";
		 echo "<td align='right'><b>Fecha de OK Nº ".$res->fields['nro_ok'].":</b> ".$f_h[0]." ".$f_h[1];
		 echo "</td>";
		 echo "</tr>";
		 $res->MoveNext();
        }	
	 echo "</tr>";	
	echo "</table>";
?>
</div>
<hr>
<? 
 switch($ult_ok)
 {case 0:echo "<div style='position:relative; width:100%;height:76%; overflow:auto;'>";break;
  //case 1:echo "<div style='position:relative; width:100%;height:75%; overflow:auto;'>";break;
  default:echo "<div style='position:relative; width:100%;height:72%; overflow:auto;'>";break; 
 } 
 
//si el campo lista_descripcion es 1 mostramos el checkbox checkeado, sino no
$query="select lista_descripcion from pcpower_renglon where id_renglon=$nro_renglon";
$avisar_d=$db->Execute($query) or die ($db->ErrorMsg().$query); 
//checkbox para avisar o no que las descripciones se han terminado 
 ?> 
<table  border='0' width='90%'>
 <tr>
  <td align="right" colspan=3>
   <input type='checkbox' name='avisar_desc' value='avisar_desc' <?=$candado?> <?if($avisar_d->fields['lista_descripcion']==1)echo " checked"?> onclick="avisar_cambios()"> Avisar que se terminaron las descripciones
  </td>
 </tr>
</table>

<TABLE border='0' width='90%' bgcolor=<? echo $bgcolor1?>>

<tr>
 <td><font color="E0E0E0"><b>Renglon: <? echo $resultados_renglon->fields["codigo_renglon"];?></b></font></td>
     <td align='right'><font color="E0E0E0"><b>Titulo del renglon: <? echo $resultados_renglon->fields['titulo'];?></b></font></td>
  </tr>		
 </table>
<input type="hidden" name="hay_cambios" value=<?if($_POST["desc_org"]=="traer")echo "1"; else echo "0";?>>
<input type="hidden" name="g_cambios" value="">
 <?	
	
//Para imprimir en la pagina de impresion:



echo "<TABLE border='1' width='90%' id=ma>
		  <tr>
		 <th>Producto</th><th>Descripcion</th><th><input type='submit' name='originales' value='Originales' $candado title='Muestra las descripciones originales de los productos' onclick=\"document.all.desc_org.value='traer';\"></th>
		  </tr>";

 
$query="select pcpower_producto.id,pcpower_producto.id_producto,pcpower_producto.desc_gral 
        from pcpower_renglon join pcpower_producto on pcpower_producto.id_renglon=pcpower_renglon.id_renglon and pcpower_renglon.id_renglon=$nro_renglon";
$resultados=$db->Execute($query) or die($db->ErrorMsg()."$query");  
$filas_encontradas1=$resultados->RecordCount();
$estan=$filas_encontradas1;
echo "<input type='hidden' name='estan' value='$filas_encontradas1'>";
//--------
$se_guardo=0;
    $i=-1;
    $descripciones[]=array();
    $resultados->Move(0);
	while(!$resultados->EOF) {
	 //por cada producto, traemos o bien la descripcion desde
     //descripciones_renglon, o en su defecto, desde decripciones
	 $query_d="select * from pcpower_descripciones_renglon join pcpower_prioridades using(titulo) where id=".$resultados->fields['id']; 
     $desc_viejo=$db->Execute($query_d) or die ($db->ErrorMsg().$query_d);
     //si no encontro nada en descripciones_renglon busca en descripciones 
     if(($desc_viejo->RecordCount()==0)||($_POST['desc_org']=="traer"))
     {
      $query_d="select * from descripciones join pcpower_prioridades using(titulo) where id_producto=".$resultados->fields['id_producto'];
      $desc_nuevo=$db->Execute($query_d) or die ($db->ErrorMsg().$query_d);
      //si no encontro nada en descripciones
      //pone un -1 para indicar que no tiene descripcion
      if($desc_nuevo->RecordCount()>0)
      {//encontro en descripciones y no en descripciones_renglon
       
       while(!$desc_nuevo->EOF)
       {$i++;
       	$descripciones[$i]["id_descripciones"]=$desc_nuevo->fields['id_descripciones'];
        $descripciones[$i]["titulo"]=$desc_nuevo->fields['titulo'];
        $descripciones[$i]["contenido"]=$desc_nuevo->fields['contenido'];
        $descripciones[$i]["prioridad"]=$desc_nuevo->fields['id_prioridad'];
        $descripciones[$i]["id_producto"]=$resultados->fields['id_producto'];
        $descripciones[$i]["id"]=$resultados->fields['id'];
        $descripciones[$i]["nuevo"]=1;
        $desc_nuevo->MoveNext();
       }  
      }
     }
     else//encontro en descripciones_renglon
     {while(!$desc_viejo->EOF)
      {if($desc_viejo->fields['borrado']==0)//si no fue borrado lo mostramos
       {$i++;
       	$descripciones[$i]["id_descripciones"]=$desc_viejo->fields['id_descripciones'];
        $descripciones[$i]["titulo"]=$desc_viejo->fields['titulo'];
        $descripciones[$i]["contenido"]=$desc_viejo->fields['contenido'];
        $descripciones[$i]["prioridad"]=$desc_viejo->fields['id_prioridad'];
        $descripciones[$i]["id_producto"]=$resultados->fields['id_producto'];  
        $descripciones[$i]["id"]=$resultados->fields['id'];
        $descripciones[$i]["nuevo"]=0;
       }
       
       $desc_viejo->MoveNext();
      } 	
     }	
      
     $resultados->MoveNext();
	}//del while
	
	$descripciones=qsort_second_dimension($descripciones,"prioridad",$i+1);
	$resultados->Move(0);
	$se_guardo=1;
    $cant_desc=sizeof($descripciones);
    $no_hay_descripciones=1;
    for($i=0;$i<$cant_desc;$i++)
    {    
    	 $id_desc=$descripciones[$i]['id_descripciones'];
    	 $producto=$descripciones[$i]["titulo"];
		 $descripcion=$descripciones[$i]['contenido'];
		 $id_pro=$descripciones[$i]['id_producto'];
         if($id_desc!=0)  
         {$no_hay_descripciones=0;
          $query_desc="select desc_gral from productos where id_producto=$id_pro";
	      $resulta=$db->Execute($query_desc) or die ($db->ErrorMsg().$query_desc);
		  $desc_gral=$resulta->fields['desc_gral'];	
		  $id=$descripciones[$i]["id"];
		  $name_desc="name_desc"."$id"."$producto";
		  $name1="text_desc"."$id"."$producto";
		  $name2="check_desc"."$id"."$producto";
		  $name_desc=ereg_replace(" ","_",$name_desc);
		  $name1=ereg_replace(" ","_",$name1);
		  $name2=ereg_replace(" ","_",$name2);
		  if($descripciones[$i]["nuevo"]==1)
		  {$disabled_bottom="disabled";
		   echo "<SCRIPT>document.all.hay_cambios.value=1</script>";
		   $disabled_el="disabled title='Para poder eliminar una descripción, primero debe guardar'";
		  }
		  else 
		   $disabled_el="";
		  $long_desc=ceil(strlen($descripcion)/64);
		  $cant_barra_n=str_count_letra("\n",$descripcion);
		  $rows=$cant_barra_n+$long_desc+1;		    
		  if($candado=="disabled")
		   $readonly="readonly";
		  else 
		   $readonly=""; 
		  echo "<tr>
			<td title='$desc_gral'>
		       $producto
		    </td>
		      <input type='hidden' name='$name_desc' value='$producto'>
    	    <td><textarea name='$name1' cols='75' rows='$rows' $readonly onchange='avisar_cambios()'>$descripcion</textarea>";
		       /*<div align='left'><input type='checkbox' name='$name2' value='guardar' $candado title='Actualiza la descripción original del producto'><font color=black size=-2> Actualizar descripción original</font></div> */
		    echo"
		     </td>
	         <td><input type='submit' name='eliminar' $disabled_el $candado value='Eliminar' onclick='document.all.eliminar_desc.value=".$id.";document.all.eliminar_desc_id.value=".$id_desc.";document.all.prod.value=\"".$producto."\";return confirm(\"¿Está seguro que desea eliminar la descripción?\");'></td>	
		    </tr>";
        }//if($id_desc!=0)  
	}//del for	
   if($no_hay_descripciones)
     echo "<tr><td colspan=3><br><h3><font color=black>NO SE HAN ENCONTRADO DESCRIPCIONES PARA LOS PRODUCTOS CARGADOS</font></h3></td></tr>";	
		
  echo "</table>";

$link_imprimir=encode_link("pcpower_imprimir.php", array("licitacion" =>$nro_licitacion,"id_renglon"=>$nro_renglon));  

?>
</div>
<br>
<hr>

<input type="hidden" name="eliminar_desc" value="">
<input type="hidden" name="eliminar_desc_id" value="">
<input type="hidden" name="guardar_todos" value="">
<input type="hidden" name="prod" value="">
<input type="hidden" name="desc_org" value="">
<input type="submit" name="Guardar" value="Guardar" style="width:19%" <?=$candado?> title="Guarda los cambios realizados en las descripciones" onclick='if(document.all.desc_org.value="traer"){document.all.guardar_todos.value="si";}else{document.all.guardar_todos.value="";}//return mensaje_confirm()'>
<input type="submit" name="OK" value="Dar OK" <?=$disabled_bottom?> <?=$candado?> style="width:19%" title="Confirma que no se detectaron errores en la revisi&oacute;n" onclick='<? if($borrar_nombre) echo"confirma();";?>//return mensaje_confirm_ok();' >
<input type="button" name=" " value="Volver" style="width:19%" title="Vuelve a la pagina anterior" onclick="guarda_cambios()">
<input type="submit" name="Generar_Documento" value="Generar Documento" <?=$disabled_bottom?> <?=$candado?> style="width:19%" title="Genera un documento Word para este rengl&oacute;n" <?if(!$se_guardo) echo disabled;?>>
<input type="button" name="Imprimir" value="Imprimir" <?=$disabled_bottom?> title="Imprime esta vista previa" style="width:19%" onclick="window.open('<?php echo $link_imprimir; ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=100,width=500,height=300');"> 
</body>
</html>