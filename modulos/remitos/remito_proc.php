<?
/*
Author: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.90 $
$Date: 2007/02/26 15:17:15 $
*/

require_once("../../config.php");


//codigo que muestra la factura vinculada a un remito (viene de remito_nuevo.php)
if ($_POST['bfactura'])
{$link=encode_link("../facturas/factura_nueva.php",array("id_factura"=>$_POST['id_fact']));
 header("location: $link");
 die();
}

require_once("fns.php");

/******PARAMETROS DE ENTRADA DE LA PAGINA***********
@boton indica que tipo de accion se debe hacer
****************************************************/

//while que sirve para poner "null"  los items en el campo precio del remito que vienen vacios
$i=0;
while ($i<$_POST['items']){
	if ($_POST["precio_".$i]=='')$_POST["precio_".$i]='null';
	$i++;
}

extract($_POST,EXTR_SKIP);

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

	
$db->starttrans();
if ($_POST["nro_factura"]){
	$nro_fact=trim($_POST["nro_factura"]);
	$datos=split("-",$nro_fact);
	$sucursal=$datos[0];
	$sql="select id_numeracion_sucursal 
	      from facturacion.numeracion_sucursal where numeracion='$sucursal'";  
	$res=sql($sql) or fin_pagina();
	$id_numeracion_sucursal=$res->fields['id_numeracion_sucursal'];
	$num_fact=$datos[1];
	$tipos_fact=descomprimir_variable($_POST['tipos']);
	$tipo=trim($tipos_fact[$nro_fact]);
	$and="";
	if ($tipo != "") $and=" and tipo_factura='$tipo'";
	if ($sucursal != "") $and.=" and id_numeracion_sucursal=$id_numeracion_sucursal";
	$consulta="select * from facturacion.facturas where nro_factura='$num_fact' $and";	
	$rta_consulta=sql($consulta, "33: ") or die();
	if ($rta_consulta->recordCount()==1){
		$id_fact=$id_factura=$rta_consulta->fields["id_factura"];
	}
}

///////////////////////////////////////////////////////////////////////////////

$f1=date("Y/m/j H:i:s");
//quita los espacios en blanco
$nro_remito=str_replace(" ","",$nro_remito);
$iva=str_replace(",",".",$iva);
if ($boton=="Guardar" || $guardar > 0 || $boton=="Terminar")
{
	if ($id_remito==-1)
	{
		 //recupero el id que se le asignara a la remito
		$q="select nextval('remitos_id_remito_seq') as id_remito";
		$id_remito=sql($q) or fin_pagina();
		$id_remito=$id_remito->fields['id_remito'];
		
		if($id_entidad=="")
		 $id_entidad="null";
		$campos="id_remito,id_numeracion_sucursal,cliente,direccion,cuit,iib,iva_tipo,iva_tasa,pedido,venta,otros,id_moneda,id_entidad";
		$fecha=date("Y/m/j H:i:s");
		$valores="$id_remito,$numeracion,'$nbre','$dir','$cuit','$iib','$condicion_iva',$iva,'$pedido','$venta','$otros',$select_moneda,$id_entidad";
		if ($fecha_remito) {
		 $campos.=",fecha_remito";
		 $valores.=",'".Fecha_db($fecha_remito)."'";
		 }
		if ($nro_remito && $nro_remito!="") {
		 $campos.=",nro_remito";
		 $valores.=",'$nro_remito'";
		 }
        if ($id_factura!=-1 && $id_factura!=-2 && ($nro_factura || $chk_asociar)) {
		   $campos.=",id_factura";
		   $valores.=",$id_factura";
		}
		 else 
		  $sin_fac=1;
		  
		if ($licitacion && $asociar) {
		 $campos.=",id_licitacion";
		 $valores.=",$licitacion";
		}
 	    if ($boton=="Terminar") {
		 	$campos.=",estado";
		 	$valores.=",'t'";
			$q2="insert into log (id_remito,tipo_log,usuario,fecha) values ($id_remito,'finalizacion','".$_ses_user['name']."','$f1')";
		 }
		 else {
		 	$campos.=",estado";
		 	$valores.=",'p'";
		 }

		//$valores="$id_remito,$valores";
		//se guarda si el remito se imprime con o sin precios
		$campos.=" ,chk_precios";
		    if ($chk_precios==1) $valores.=" ,1";
		    else $valores.=" ,0";
		    
		$q="insert into remitos ($campos) values ($valores)";
		//die($q);//////////////////////////////////////////////////////////////////////////////////////////
		if (sql($q)) {
			$q="insert into log (id_remito,tipo_log,usuario,fecha) values ($id_remito,'creacion','".$_ses_user['name']."','$f1')";
			sql($q) or fin_pagina();

			if ($q2)
				sql($q2) or fin_pagina();
		 }
		 elseif (strpos($db->ErrorMsg(),'unique')>0) {
			$msg.="NO SE PUDO GUARDAR EL REMITO, YA EXISTE EL NUMERO \"$nro_remito\"";
			
			if ($seg) { 
				$sql="SELECT subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion as licitacion,subido_lic_oc.vence_oc as vencimiento,subido_lic_oc.nro_orden as oc,entidad.nombre as cliente,entrega_estimada.nro as numero FROM subido_lic_oc ";
				$sql.="LEFT JOIN licitacion USING (id_licitacion) LEFT JOIN entidad USING (id_entidad) ";
				$sql.="LEFT JOIN entrega_estimada USING (id_entrega_estimada) WHERE subido_lic_oc.id_entrega_estimada=$seg";
				$result=sql($sql) or fin_pagina();
				$fila=$result->fetchrow();
				$fila["msg"]=$msg;
				$fila["pagina_volver"]="entregas.php";
				$link4=encode_link("../ordprod/seleccionar_renglon_adj.php",$fila);
				echo "<html><head><script language=javascript>"; 
                echo "window.opener.location.href='$link4';";
                echo "window.close();";
                echo "</script></head></html>";  
		    }
			else header("location: ".encode_link("remito_nuevo.php",array("msg"=>$msg)));
			die;
		}
    }
	else {
        if($id_entidad=="")
		 $id_entidad="null";
		 if ($chk_precios==1) $imprime=1;
		    else $imprime=0;
		$q="update remitos set ".
		   "cliente='$nbre',direccion='$dir',cuit='$cuit',iib='$iib',iva_tipo='$condicion_iva',".
		   "iva_tasa=$iva,pedido='$pedido',venta='$venta',otros='$otros',id_entidad=$id_entidad, chk_precios=$imprime";
		$tipo_log="modificacion";
		if ($boton=="Terminar")	{
		  $q.=",estado='t'";
	      $tipo_log="finalizacion";
		}	
		if ($select_moneda && $select_moneda!=-1)
			$q.=",id_moneda=$select_moneda";
		if ($fecha_remito)
		    $q.=",fecha_remito='".Fecha_db($fecha_remito)."'";
	 	if ($nro_remito && $nro_remito!="")
		    $q.=",nro_remito='$nro_remito'";
  	    if ($id_factura && $id_factura!=-1 && $id_factura!=-2 && ($chk_asociar || $nro_factura))
		    $q.=",id_factura=$id_factura";
	        else
		    $q.=",id_factura=NULL";

		if ($licitacion && $asociar)
 			$q.=",id_licitacion=$licitacion ";
 		    else
 			$q.=",id_licitacion=null ";
 			
 		if ($numeracion)
 		    $q.=",id_numeracion_sucursal=$numeracion";
 		    	
		$q.=" where id_remito=$id_remito";	

		sql($q) or fin_pagina();
		$q="insert into log (id_remito,tipo_log,usuario,fecha) values ($id_remito,'$tipo_log','$_ses_user_name','$f1')";
		sql($q) or fin_pagina();

	}
	//organiza los datos provenientes de la pagina anterior
	$items=get_items();
	
	//elimina los items que no estan en items
	del_items($items,$id_remito);
	
	//eliminar los campos inecesarios para poder usar la funcion replace
	prepare_remito($id_remito,$items);
	
	//la funcion hace su trabajo
	if (replace("items_remito",$items,array("id_item")) == 0) {
		$msg2=($nro_remito)?" Nº $nro_remito":"";
		$msg="Su Remito$msg2 se guardó exitosamente";
		//remito sin factura => enviar mail
		if ($sin_fac) {
		  $q="select * from usuarios where login='$_ses_user_login'";
  		  $datos_usuario=sql($q) or fin_pagina();

  		  $mail=$datos_usuario->fields['mail'];
		  $boundary = strtoupper(md5(uniqid(time())));
	     $mailtext="Se creo un remito sin factura:\n";
	     $mailtext.="\nRemito Nº $nro_remito\n";
	     $f1=date("j/m/Y H:i:s");
	     switch (date('w')) {
	     	case 0: $f1="Domingo $f1";break;
	     	case 1: $f1="Lunes $f1";break;
	     	case 2: $f1="Martes $f1";break;
	     	case 3: $f1="Miercoles $f1";break;
	     	case 4: $f1="Jueves $f1";break;
	     	case 5: $f1="Viernes $f1";break;
	     	case 6: $f1="Sabado $f1";break;
	     }
	     $mailtext.="\nFecha: $f1 \n";
	     $mailtext.="\nUsuario: ".$_ses_user['name']."\n";
	     $mailtext.="\nCliente: $nbre\n";
         $mailtext.="\nRazón:\n";
         $mailtext.="\n"." ".$_POST['valor']."\n";
         //items del remito:
         //$mailtext.="\nNúmero de remito: $id_remito\n";
         $mailtext.="\n"."Items del Remito:"."\n\n----------------------------\n";
         //muestro en el mail los items del remito
         $query_items_remito="SELECT * FROM items_remito WHERE id_remito=$id_remito";
         $resultados_items_remito=sql($query_items_remito) or fin_pagina();
         //$mailtext.="Cantidad de Productos:".$resultados_items_remito->RecordCount()."\n\n";
         while(!$resultados_items_remito->EOF){
         $mailtext.="Cantidad: ".$resultados_items_remito->fields['cant_prod']."\n";
         //$mailtext.="Tipo: ".$resultados_items_remito->fields['tipo']."\n";
         $mailtext.="Descripción: ".$resultados_items_remito->fields['descripcion']."\n----------------------------\n";
         $resultados_items_remito->MoveNext();
         }
         //echo $mailtext;  
	     $mail_header="";
	     $mail_header .= "MIME-Version: 1.0";
		 $mail_header .= "\nFrom: Sistema Inteligente CORADIR <$mail>";// <".$_SERVER["HTTP_HOST"].">";
         $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
	  	 //$mail_header .="\nTo: cestila@pcpower.com.ar";
	   	// $mail_header .="\nTo: juanmanuel@pcpower.com.ar";
		 //$mail_header .="\nBcc: corapi@coradir.com.ar";
		 $mail_header .="\nBcc: ordenesdecompraenviadas@coradir.com.ar";  //carlos,juanmanuel,noelia
         //$mail_header .="\nBcc: noelia@pcpower.com.ar";
		 $mail_header .= "\nReply-To: $mail";
		 $mail_header .= "\nContent-Type: text/plain; ";
		 $mail_header .= "\nContent-Transfer-Encoding: 8bit";
		  // Mail-Text
		 $mail_header .= "\n\n" . $mailtext."\n";
	     $mail_header .= "\n\n" . firma_coradir()."\n"; 
	      // End 
	      mail("","Remito sin factura","",$mail_header);
		}
	}
	else {
		$msg="NO SE PUDO GUARDAR EL REMITO";	
	}

}
//anula el remito tengo que preguntar por la variable anular_aux que es un hidden
//este hidden lo setea la pagina comantario_anulacion_remito en true
elseif ($boton=="Anular" || $anular_aux=="true") {
	$q="update remitos set estado='a' where id_remito=$id_remito";	
	$msg2=($nro_remito)?" Nº $nro_remito":"";
	if	(sql($q)) {
		$msg="Su Remito$msg2 se guardo exitosamente";	
		$q="insert into log (id_remito,tipo_log,usuario,fecha,otros) values ($id_remito,'anulacion','$_ses_user_name','$f1','$comentario_anular')";
		sql($q) or fin_pagina();

	}
}
if ($subir_remito=="Subir remito escaneado") {
	//print_r($_FILES);
	$tamanio=$_FILES["archivo"]["size"];
	if (!$_FILES["archivo"])
         $error.="Debe seleccionar un archivo.<br>";
    if ($_FILES["archivo"]["error"])
         $error.="El archivo es muy grande.";
	if (!$error) {
		$archivo=$_FILES["archivo"]["name"];
		mkdirs(UPLOADS_DIR."/remitos");
		if (is_file(UPLOADS_DIR."/remitos/$archivo"))
			$error="El archivo ya existe.";
		if (!$error) {
			if (!copy($_FILES["archivo"]["tmp_name"],UPLOADS_DIR."/remitos/".$_FILES["archivo"]["name"]))
                   $error.="No se pudo Subir el archivo.";
			else {
				$sql="INSERT INTO archivo_remito
					(archivo,id_usuario,fecha,size,id_remito) Values
					('$archivo','".$_ses_user["id"]."','".date ("Y/m/d")."','$tamanio',$id_remito)";
				sql($sql) or fin_pagina();
				$sql="update remitos set estado='r' where id_remito=$id_remito";
				sql($sql) or fin_pagina();

				$q="insert into log (id_remito,tipo_log,usuario,fecha) values ($id_remito,'Subir remito escaneado','$_ses_user_name','".date("Y/m/d")."')";
				sql($q) or fin_pagina();

				$msg2=($nro_remito)?"Nº $nro_remito":"";
				$msg="Su Remito $msg2 se guardo exitosamente";
			}
		}
	}
	if ($error) $msg=$error;
}

//cuando ingresa quien recibio el remito
if ($boton=="Aceptar") {
   $q="update remitos set estado='r',cliente2='$apellido,$nbre',
   tipo_doc_c2='$select_tipo_doc',nro_doc_c2='$nrodoc' where id_remito=$id_remito";	
	$msg2=($nro_remito)?" Nº $nro_remito":"";
	if	(sql($q)) {
		$msg="Su Remito$msg2 se guardo exitosamente";	
		$q="insert into log (id_remito,tipo_log,usuario,fecha) values ($id_remito,'modificacion','$_ses_user_name','$f1')";
		sql($q) or fin_pagina();

	}
	echo "<script>window.opener.location.href='".encode_link("remito_listar.php",array("informar"=>$msg))."';";
	echo "window.close(); </script>" ;die;
}
$db->completetrans();
if ($seg) { 
	$msg="Los datos se guardaron correctamente.";
	$sql="SELECT subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion as licitacion,subido_lic_oc.vence_oc as vencimiento,subido_lic_oc.nro_orden as oc,entidad.nombre as cliente,entrega_estimada.nro as numero FROM subido_lic_oc ";
	$sql.="LEFT JOIN licitacion USING (id_licitacion) LEFT JOIN entidad USING (id_entidad) ";
	$sql.="LEFT JOIN entrega_estimada USING (id_entrega_estimada) WHERE subido_lic_oc.id_entrega_estimada=$seg";
	$result=sql($sql) or fin_pagina();
	$fila=$result->fetchrow();
	$fila["msg"]=$msg;
	$fila["pagina_volver"]="entregas.php";

	$link4=encode_link("../ordprod/seleccionar_renglon_adj.php",$fila);
    echo "<html><head><script language=javascript>"; 
    echo "window.opener.location.href='$link4';";
    echo "window.close();";
    echo "</script></head></html>";  

}
else header("location: ".encode_link("remito_listar.php",array("informar"=>$msg)));
?>