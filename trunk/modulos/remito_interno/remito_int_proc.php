<?
/*
Author: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.5 $
$Date: 2007/01/04 15:30:29 $
*/


require_once("../../config.php");

//codigo que muestra la factura vinculada a un remito (viene de remito_nuevo.php)
/*
if ($_POST['bfactura'])
{$link=encode_link("../facturas/factura_nueva.php",array("id_factura"=>$_POST['id_fact']));
 header("location: $link");
 die();
}
  */
require_once("fns.php");

/******PARAMETROS DE ENTRADA DE LA PAGINA***********
@boton indica que tipo de accion se debe hacer
****************************************************/

extract($_POST,EXTR_SKIP);

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$f1=date("Y/m/j H:i:s");
//quita los espacios en blanco
$id_remito=str_replace(" ","",$id_remito);
//$iva=str_replace(",",".",$iva);
if ($boton=="Guardar" || $guardar > 0 || $boton=="Pasar Historial")
{
	if ($id_remito==-1)
	{
		 //recupero el id que se le asignara a el remito
		$q="select nextval('remito_interno_id_remito_seq') as id_remito";
		$id_remito=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
		$id_remito=$id_remito->fields['id_remito'];
		
		/*if($id_entidad=="")
		 $id_entidad="null";*/
		$campos="id_remito,cliente,direccion,entrega";
		$fecha=date("Y/m/j H:i:s");
		$valores="'$nbre','$dir','$entrega'";
		 if ($fecha_remito)
		 {
		 $campos.=",fecha_remito";
		 $valores.=",'".Fecha_db($fecha_remito)."'";
		 }
		/* if ($nro_remito && $nro_remito!="")
		 {
		 $campos.=",nro_remito";
		 $valores.=",'$nro_remito'";
		 }
		 if ($id_factura!=-1 && $id_factura!=-2 && $chk_asociar)
		 {
		 	$campos.=",id_factura";
		   $valores.=",$id_factura";
		 }
		 else
		 */
                  $sin_fac=1;

		if ($licitacion)// && $asociar)
		{
		 $campos.=",id_licitacion";
		 $valores.=",$licitacion";
		}
		  
		 if ($boton=="Pasar Historial")
		 {
		 	$campos.=",estado";
		 	$valores.=",'h'";
			$q2="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'finalizacion','".$_ses_user['name']."','$f1')";
		 }
		 else	
		 {
		 	$campos.=",estado";
		 	$valores.=",'p'";
		 }

		$valores="$id_remito,$valores";
		
		$q="insert into remito_interno ($campos) values ($valores)";
		if ($db->Execute($q))
		{
			$q="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'creacion','".$_ses_user['name']."','$f1')";
			$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
			if ($q2)
				$db->Execute($q2) or die($db->ErrorMsg()."<br>".$q2);
		}
		elseif (strpos($db->ErrorMsg(),'unique')>0)
		{
			$msg="NO SE PUDO GUARDAR EL REMITO, YA EXISTE EL NUMERO \"$nro_remito\" ";
                        die($db->ErrorMsg());
                        header("location: ".encode_link("remito_int_nuevo.php",array("msg"=>$msg)));
			die;
		}
		
		
	}
	else 
	{
        /*if($id_entidad=="")
		 $id_entidad="null";*/
		$q="update remito_interno set ".
		"cliente='$nbre',direccion='$dir',
		entrega='$entrega'";

		$tipo_log="modificacion";
		if ($boton=="Pasar Historial")
		{
		  $q.=",estado='h'";
	     $tipo_log="finalizacion";
		}	
		/*if ($select_moneda && $select_moneda!=-1)
			$q.=",id_moneda=$select_moneda";*/
		if ($fecha_remito)
		 $q.=",fecha_remito='".Fecha_db($fecha_remito)."'";
	 	/*if ($nro_remito && $nro_remito!="")
		 $q.=",nro_remito='$nro_remito'";*/
  	 /* if ($id_factura && $id_factura!=-1 && $id_factura!=-2 && $chk_asociar)
		 $q.=",id_factura=$id_factura";
	  else
		$q.=",id_factura=NULL";
        */
		if ($licitacion)// && $asociar)
 			$q.=",id_licitacion=$licitacion ";
 		else
 			$q.=",id_licitacion=null ";


		$q.=" where id_remito=$id_remito";	
		$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
		$q="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'$tipo_log','".$_ses_user['name']."','$f1')";
		$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);

	}
	//organiza los datos provenientes de la pagina anterior
	$items=get_items();

	//elimina los items que no estan en items
	del_items($items,$id_remito);

	//eliminar los campos innecesarios para poder usar la funcion replace
	prepare_remito($id_remito,$items);

	//la funcion hace su trabajo
        $fallaron=replace("items_remito_interno",$items,array("id_item"));

        if ($fallaron == 0)
	{
		$msg2=($nro_remito)?" Nº $nro_remito":"";
		$msg="Su Remito$msg2 se guardo exitosamente";
		//remito sin factura => enviar mail
	/*	if ($sin_fac)
		{
		  $q="select * from usuarios where login='$_ses_user_login'";
  		  $datos_usuario=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
  		  $mail=$datos_usuario->fields['mail'];
		  $boundary = strtoupper(md5(uniqid(time())));
	     $mailtext="Se creo un remito sin factura:\n";
	     $mailtext.="\nRemito Nº $nro_remito\n";
	     $f1=date("j/m/Y H:i:s");
	     switch (date('w'))
	     {
	     	case 0: $f1="Domingo $f1";break;
	     	case 1: $f1="Lunes $f1";break;
	     	case 2: $f1="Martes $f1";break;
	     	case 3: $f1="Miercoles $f1";break;
	     	case 4: $f1="Jueves $f1";break;
	     	case 5: $f1="Viernes $f1";break;
	     	case 6: $f1="Sabado $f1";break;
	     }
	     $mailtext.="\nFecha: $f1 \n";
	     $mailtext.="\nUsuario: $_ses_user_name\n";
	     $mailtext.="\nCliente: $nbre\n";
         $mailtext.="\nRazon:\n";
         $mailtext.="\n"." ".$_POST['valor']."\n";
         //items del remito:
         $mailtext.="\nNumero de remito: $id_remito\n";
         $mailtext.="\n"."Items del Remito:"."\n";
         //muestro en el mail los items del remito
         $query_items_remito="SELECT * FROM items_remito join productos using(id_producto) WHERE id_remito=$id_remito";
         $resultados_items_remito=$db->Execute($query_items_remito) or die($db->ErrorMsg().$query_items_remito);
         while(!$resultados_items_remito->EOF){
         $mailtext.=$resultados_items_remito->fields['tipo'].": ";
         $mailtext.=$resultados_items_remito->fields['descripcion']."\n";
         $resultados_items_remito->MoveNext();
         }

	     $mail_header="";
	     $mail_header .= "MIME-Version: 1.0";
		 $mail_header .= "\nfrom: Sistema Inteligente CORADIR <$mail>";// <".$_SERVER["HTTP_HOST"].">";
         //$mail_header .="\nTo: soypablorojo@hotmail.com";
		  $mail_header .="\nTo: juanmanuel@pcpower.com.ar";
		  $mail_header .="\nBcc: corapi@coradir.com.ar";
		  $mail_header .="\nBcc: ordenesdecompraenviadas@coradir.com.ar";
          $mail_header .="\nBcc: noelia@pcpower.com.ar";
		  $mail_header .= "\nReply-To: $mail";
		  $mail_header .= "\nContent-Type: text/plain; ";
		  $mail_header .= "\nContent-Transfer-Encoding: 8bit";
		  // Mail-Text
		  $mail_header .= "\n\n" . $mailtext."\n";
	     $mail_header .= "\n\n" . firma_coradir()."\n";
	      // End
			mail("","Remito sin factura","",$mail_header);
		}
		if ($boton=="Pasar Historial")
		{
		 include("pdf.php");
		}
        */
	}
	else
	{
		$msg="NO SE PUDO GUARDAR EL REMITO";	
	}


}

elseif ($boton=="Anular")
{
	$q="update remito_interno set estado='a' where id_remito=$id_remito";
	$msg2=($nro_remito)?" Nº $nro_remito":"";
	if	($db->Execute($q))
	{
		$msg="Su Remito$msg2 se guardo exitosamente";
		$q="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'anulacion','".$_ses_user['name']."','$f1')";
		$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
	}
}

//cuando ingresa quien recibio el remito
if ($boton=="Aceptar")
{
   $q="update remito_interno set estado='r',cliente2='$apellido,$nbre',
   tipo_doc_c2='$select_tipo_doc',nro_doc_c2='$nrodoc' where id_remito=$id_remito";	
	$msg2=($id_remito)?" Nº $id_remito":"";
	if	($db->Execute($q))
	{
		$msg="Su Remito$msg2 se guardo exitosamente";	
		$q="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'modificacion','".$_ses_user['name'].",'$f1')";
		$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
	}

	echo "<script>window.opener.location.href='".encode_link("remito_int_listar.php",array("informar"=>$msg))."';";
	echo "window.close(); </script>" ;die;

}

   header("location: ".encode_link("remito_int_listar.php",array("informar"=>$msg)));
?>