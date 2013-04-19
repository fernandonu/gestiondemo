<?
/*
Creado por: marco_canderle

Modificada por
$Author: mari $
$Revision: 1.102 $
$Date: 2007/02/19 11:45:26 $
*/


require_once("../../config.php");
require_once("funciones.php"); 

//funcion para mandar el arreglo por post


/*****************************************************************************
 * insertar_bandera_renglones_entregados()
 * @return void
 * @param $id_renglones_oc es el id de los renglones a considerar 
 * @desc Verifica si las facturas asociadas a los renglons oc de este seguimiento
         tienen todos sus items entregados, si es asi , la cobranza asociada a esa factura
         se le agrega una bandera para que pueda considerarse en el balance
 ****************************************************************************/
function   insertar_bandera_renglones_entregados($id_renglones_oc){
    
$sql=" select facturas.id_factura,facturas.nro_factura,
       cantidad_items.cant_items,
       cantidad_items_entregados.cant_items_entregados
       from 
       facturacion.facturas join 
       (
            select count(if.id_item) as cant_items,f.id_factura,f.nro_factura 
               from facturacion.facturas f
               join facturacion.items_factura if using (id_factura)
               join licitaciones.renglones_oc roc using (id_renglones_oc)
               
            group by f.id_factura,f.nro_factura
            order by f.nro_factura DESC
       ) as cantidad_items using (id_factura)

       left join    
       (
            select count(if.id_item) as cant_items_entregados ,f.id_factura,f.nro_factura 
                from facturacion.facturas f
                join facturacion.items_factura if using (id_factura)
                join licitaciones.renglones_oc roc using (id_renglones_oc)
                where roc.estado=1 and id_factura in (
                                                       select id_factura from facturacion.facturas 
                        						       join facturacion.items_factura if using (id_factura)
                                                       join licitaciones.renglones_oc roc using (id_renglones_oc)
                                                       where roc.id_renglones_oc in $id_renglones_oc
                                                     ) 
            group by f.id_factura,f.nro_factura
            order by f.nro_factura DESC
        ) as cantidad_items_entregados  using (id_factura)       
        where cantidad_items.cant_items=cantidad_items_entregados.cant_items_entregados
        order by facturas.nro_factura DESC";
  

$res=sql($sql) or fin_pagina();    
 for($i=0;$i<$res->recordcount();$i++){
     $id_factura=$res->fields["id_factura"];
     $sql=" update licitaciones.cobranzas set renglones_entregados=1 where id_factura=$id_factura";
     sql($sql) or fin_pagina();
     $res->movenext(); 
     }   

    
} //de la funcion


function array_envia($array) {
    $tmp = serialize($array);
    $tmp = urlencode($tmp);
    return $tmp;
}
//print_r($parametros);
//funcion para recibir un arreglo del post
function array_recibe($array) {
    $tmp = urldecode($array);
    $tmp = unserialize($tmp);
   return $tmp;
}

/*prueba de enviar mail de finalizar seguimiento Sacar despues de probar
if(isset($_POST["prueba_fin_seguimento"]))
{	
	$id_entrega_estimada = $parametros['id_entrega_estimada'];
	require_once("funciones.php");
	enviar_mail_lic_entregada($id_entrega_estimada);	
	echo "Mensaje inteligente enviado";
	die();
}*/

//inserto nuevo comentario del seguimiento

if(isset($_POST["guardar"])) {
 $id_subir=$_POST['id_subir'];
 $sql="insert into comentarios_seguimientos(id_subir,id_usuario,comentario,fecha_comentario) values($id_subir,".$_ses_user["id"].",'".$_POST["nuevo_coment"]."','".date("Y-m-d H:i:s")."')";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
}

$id_entrega_estimada=$parametros['id_entrega_estimada'];
$licitacion=$parametros['licitacion'];
$nro=$parametros['numero'];
$pagina_volver=$parametros['pagina_volver'];
$oc=$parametros["oc"];
$cliente=$parametros['cliente'];
$sele="select distrito.nombre from entidad join distrito using(id_distrito) where entidad.nombre='$cliente'";
$sel_ent=sql($sele,"no se pudo recuperar el nombre del cliente")or fin_pagina();
$nom_cli=$sel_ent->fields['nombre'];
$vencimiento=$parametros['vencimiento'];
$msg=$parametros['msg'];
$sql23 = "select fecha_estimada from entrega_estimada where id_entrega_estimada=$id_entrega_estimada";
$res_fecha=sql($sql23,"No se pudo recuperar la fecha")or fin_pagina();
$fecha1=$res_fecha->fields['fecha_estimada'];
//echo $id_entrega_estimada;
//traigo comentarios de la BD
/*$query="select comentarios_seguimientos.id_usuario,comentarios_seguimientos.comentario,
        comentarios_seguimientos.fecha_comentario,subido_lic_oc.id_subir,usuarios.nombre,
        usuarios.apellido
        from entrega_estimada
        join subido_lic_oc using(id_entrega_estimada)
        left join comentarios_seguimientos using(id_subir) 
        left join usuarios using(id_usuario) 
        where id_entrega_estimada=$id_entrega_estimada order by(fecha_comentario)";
*/

$sql = "select comentario_prorroga.comentario,comentario_prorroga.fecha_comentario,comentario_prorroga.id_usuario,comentario_prorroga.id_prorroga from prorroga join comentario_prorroga using(id_prorroga) where id_entrega_estimada=$id_entrega_estimada";
$sql2 = "select comentarios_seguimientos.comentario,comentarios_seguimientos.fecha_comentario,
         comentarios_seguimientos.id_usuario,NULL as id_prorroga from entrega_estimada 
         join subido_lic_oc using(id_entrega_estimada) 
         join comentarios_seguimientos using(id_subir) 
         where id_entrega_estimada=$id_entrega_estimada";
$sql3 = "$sql UNION ALL $sql2 order by fecha_comentario asc";

//$comentarios=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

$comentarios=$db->Execute($sql3) or die($db->ErrorMsg()."<br>".$sql3);

//variables para subir archivos
$cant_arch = $_POST["files_cant"] or $cant_arch = 1;
$cmd1=$parametros['cmd1'];

if ($cmd1=="download") {
   $file=$parametros["file"];
   $size=$parametros["size"];
   Mostrar_Header($file,"application/octet-stream",$size);
   $filefull = UPLOADS_DIR ."/archivos_seg/".$id_entrega_estimada.'-'.$file;
   readfile($filefull);
   exit();
}

if ($_POST['eliminar']=='Eliminar Archivo'){
$archivos=PostvartoArray('elim_'); //crea un arreglo con los checkbox chequeados
$tam_arch=sizeof($archivos); 

if ($archivos){  //para ver si hay check seleccionados
 $list='(';
 foreach($archivos as $key => $value){
   $list.=$value.',';
 }
 $list=substr_replace($list,')',(strrpos($list,',')));

 $query="select nbre_arch,id_archivo_seg from arch_seguimiento where id_archivo_seg in $list";
 $res_archivo=sql($query) or fin_pagina(); 
  while (!$res_archivo->EOF) {
    if (!unlink(UPLOADS_DIR."/archivos_seg/".$id_entrega_estimada.'-'.$res_archivo->fields['nbre_arch']))
      Error("No se encontro el archivo");
    if (!$error){
    $id_arch=$res_archivo->fields['id_archivo_seg'];
    $query="delete from arch_seguimiento where id_archivo_seg=$id_arch";
  
     sql( $query) or fin_pagina();
    }
 $res_archivo->MoveNext();
}
} //fin de si hay check seleccionados 
} //fin eliminar archivos


if ($_POST["gua_presentacion"]){
	//$id_ent = $parametros["id_entrega_estimada"];
    $nom_pre=$_POST["nom_par"];
    $tele_pre=$_POST["tele_par"];
    $mail_pre=$_POST["mail_par"];
    
    $otros=$_POST['otros'];
  	$nro_cuit=$_POST['nro_cuit'];
  	$razon_social_para_factura=$_POST['razon_social_para_factura'];
  	$domicilio_para_factura=$_POST['domicilio_para_factura'];
  	$fact_orig=$_POST['fact_orig'];
  	$rem_orig=$_POST['rem_orig'];
  	if ($_POST['libre_deuda']) $libre_deuda=1;
  	else $libre_deuda=0;
  	if ($_POST['ultimo_sus']) $ultimo_sus=1;
  	else $ultimo_sus=0;
  	if ($_POST['ing_brutos']) $ing_brutos=1;
  	else $ing_brutos=0;
  	$lugar_pres_fact=$_POST['lugar_pres_fact'];
    guardar_contactos_segumientos($id_entrega_estimada,2,$nom_pre,$tele_pre,$mail_pre,$otros,$nro_cuit,$razon_social_para_factura,$domicilio_para_factura,$fact_orig,$rem_orig,$libre_deuda,$ultimo_sus,$ing_brutos,$lugar_pres_fact);
}

if ($_POST["gua_entrega"]){
	//$id_ent = $parametros["id_entrega_estimada"];
    $nom_entre=$_POST["nom_entrega"];
    $tele_entre=$_POST["tele_entrega"];
    $mail_entre=$_POST["mail_entrega"];
    $fecha1=Fecha_db($_POST['fecha']);
    $sql="update licitaciones.entrega_estimada set fecha_estimada='".(($fecha1)?$fecha1:"null")."' where id_entrega_estimada=".$id_entrega_estimada;
	$result=sql($sql, "c16 ".$sql) or fin_pagina();
    $otros='';
  	$nro_cuit='';
  	$razon_social_para_factura='';
  	$domicilio_para_factura='';
  	$fact_orig='';
  	$rem_orig='';
  	if ($_POST['libre_deuda']) $libre_deuda=1;
  	else $libre_deuda=0;
  	if ($_POST['ultimo_sus']) $ultimo_sus=1;
  	else $ultimo_sus=0;
  	if ($_POST['ing_brutos']) $ing_brutos=1;
  	else $ing_brutos=0;
  	$lugar_pres_fact='';
  	
    guardar_contactos_segumientos($id_entrega_estimada,1,$nom_entre,$tele_entre,$mail_entre,$otros,$nro_cuit,$razon_social_para_factura,$domicilio_para_factura,$fact_orig,$rem_orig,$libre_deuda,$ultimo_sus,$ing_brutos,$lugar_pres_fact);
}

if ($_POST["enviar_mail"]){
	//$id_ent = $parametros["id_entrega_estimada"];
    $nom_cont=$_POST["nom_cont"];
    $id_mail=$_POST["id_mail"];
    $mails=$_POST["mails"];
    $desde=$_POST["desde"];
    $hasta=$_POST["hasta"];
    $tel_cont=$_POST["tel_cont"];
    $correo=$_POST["correo"];
    $mercaderias=$_POST["mercaderias"];
    $tele_entre=$_POST["tele_entrega"];
    $mail_entre=$_POST["mail_entrega"];
    $fecha1=Fecha_db($_POST['fecha_ent']);
    $banda="$desde-$hasta";
    if($mails==0)
    {
     $sql="update mail_entregas set fecha_entregas='$fecha1',contacto='$nom_cont',telefono_contacto='$tel_cont',mail_destinatarios='$correo', 
     mercaderias='$mercaderias',banda_horaria='$banda' where id_mail_entregas=$id_mail";
	 $result=sql($sql, "c16 ".$sql) or fin_pagina();
    }
    else 
    {
     $campos="id_entrega_estimada,id_licitacion,fecha_entregas,contacto,telefono_contacto,mail_destinatarios,mercaderias,banda_horaria";
     $valores="$id_entrega_estimada,$licitacion,'$fecha1','$nom_cont','$tel_cont','$correo','$mercaderias','$banda'";	
     $insert_mail="insert into mail_entregas ($campos) values ($valores)";
     sql($insert_mail,"No se pudo guardar los mail") or fin_pagina();
    }
  
   $fecha1=Fecha($fecha1); 
   //$mercaderias=html_out($mercaderias);
   $para="$correo";
   $asunto="Entrega de la Licitacion $licitacion";
   $contenido="Orden de Despacho\n ";
   $contenido.="\n ";  
   $contenido.=" ID $licitacion \n ";   
   $contenido.=" Cliente:$cliente \n ";   
   $contenido.=" Fecha de Entrega:$fecha1 \n ";   
   $contenido.=" Horario de Entrega:Desde $desde  Hasta $hasta\n ";   
   $contenido.=" Contacto Entrega:$nom_cont \n ";   
   $contenido.=" Telefono $tel_cont \n ";   
   $contenido.=" Mercaderia a Entregar: \n ";   
   $contenido.=" $mercaderias \n ";   
   $contenido.="\n";
   $contenido.="SALUDOS. \n";
   $contenido.="\n";
   /*echo"para $para<br>";
   echo"asunto $asunto<br>";
   echo"contenido $contenido<br>";
   die();*/
   enviar_mail($para,$asunto,$contenido,'','',0);
   
}

if ($_POST['entregado'] || $_POST['entregar']) {
//marcar como entregado los renglones	
$db->StartTrans();
$contador=$_POST['contador'];
$contador1=$_POST['total_renglones'];
$renglon=PostvartoArray('chk_'); //crea un arreglo con los checkbox chequeados
$tam_renglon=sizeof($renglon); 

if ($renglon){  //para ver si hay check seleccionados
        $list='(';
        foreach($renglon as $key => $value){
                $list.=$value.',';
        }
      
        $list=substr_replace($list,')',(strrpos($list,',')));
        $fec_entregado=fecha_db(date("d/m/Y",mktime()));
         
         if ($_POST['entregado']) {  
 	        //si presiono el boton entregado se reliza el control
 	        //si presiono el boton entregar  s/factura no se reliza el control (solo ve este boton juan )
            $precio_renglones=array_recibe($_POST['datos_renglones']);   
                     
            $sql="select sum(precio * cant_prod) as precio_renglon, id_renglones_oc 
                  from items_factura join facturas using (id_factura)
                  where id_renglones_oc in $list and estado!='a' group by id_renglones_oc";
            $res_control=sql($sql) or fin_pagina();
            $control_renglon_fact=array();
            $gTotalMontos=0;
            while (!$res_control->EOF) {
 	              $control_renglon_fact[$res_control->fields['id_renglones_oc']]['precio']=number_format($res_control->fields['precio_renglon'],"2",".","");
 	              $gTotalMontos+=$res_control->fields["precio_renglon"];
                  $res_control->MoveNext();
            }
	        //////////////////////////////////////// Gabriel ///////////////////////////////////////////////
            if ($gTotalMontos>10000){
            ?>
  	        <script>
  		    window.open('<?=encode_link("../modulo_clientes/cliente_referencia_editar.php", array("modo"=>"nuevo", "pagina"=>"seguimiento_orden", "id_licitacion"=>$licitacion, "monto"=>$gTotalMontos))?>','','toolbar=0,location=0,directories=0,status=0,resizable=1, menubar=0,scrollbars=1,left=190,top=0,width=640,height=300');
  	        </script>
            <?
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////
            foreach($renglon as $key => $value) {
                $dif_factura=abs($precio_renglones[$value]['precio'] - $control_renglon_fact[$value]['precio']);
                $dif_factura=number_format($dif_factura,"2",".","");
                if ($dif_factura > 0.01 ) 
                        Error ("El precio del renglon no coincide con el monto de la factura (renglon número ". $precio_renglones[$value]['codigo_renglon'].")" );
            }
            }
            if (!$error) {	
                if($contador==$contador1){
                    
                    $monto_prod=$_POST['monto_prod'];
                    if($monto_prod==""){
                           $licitacion=$parametros['licitacion'];
                           $mon_prod="select sum(precio_stock*en_produccion.cantidad) as monto
                                       from stock.en_stock join general.producto_especifico using(id_prod_esp)   
                                       join stock.en_produccion using(id_en_stock) 
                                       where id_licitacion=$licitacion";
                           $prod=sql($mon_prod,"No se pudo recuperar el monto de Bs As") or fin_pagina();
                           $monto_prod=$prod->fields['monto'];
                    }
                    if($monto_prod=="") {
  	                       $monto_prod=0;
                    }
                    $campos="id_entrega_estimada,monto_prod";
                    $valores="$id_entrega_estimada,$monto_prod";	
                    $insert_foto="insert into foto_seguimiento ($campos) values ($valores)";
                    sql($insert_foto,"No se pudo guardar la foto") or fin_pagina();
                }	 	
           $query="update renglones_oc set estado=1 where id_renglones_oc in $list";
           $res_query=sql($query) or fin_pagina();
	       //guardo log de entrega del renglon
	       $query_log="";
	       foreach($renglon as $key => $value){
	               $sql_l="select * from log_renglones_oc where id_renglones_oc=$value";
	               $res_l=sql($sql_l) or fin_pagina();	
	               if ($res_l->RecordCount() == 0)
	                    $query_log[]="insert into log_renglones_oc (id_renglones_oc,usuario,fecha_entrega,tipo) values ($value,'".$_ses_user['name']."','$fec_entregado','entrega')";
	       }
	       if ($query_log!="") sql($query_log) or fin_pagina();
	  
	       //busco los renglones que sean tipo computadora
	       $sql_datos="select id_renglones_oc,renglones_oc.cantidad,renglones_oc.observacion 
	                   from licitaciones.renglones_oc join licitaciones.renglon
                       using (id_renglon) where id_renglones_oc in $list and tipo like '%Computadora%'";
	       $res_datos=sql($sql_datos) or fin_pagina();
	      
	       while (!$res_datos->EOF) {
	           $sql_insert[]="insert into resumen_produccion (id_renglones_oc,cant_renglon,descripcion,fecha_entrega)
	                          values(".$res_datos->fields['id_renglones_oc'].",".$res_datos->fields['cantidad'].",
	                          '".$res_datos->fields['observacion']."','$fec_entregado')";
	        
	       $res_datos->MoveNext();
	       }
	       
	       if ($sql_insert) {
	                 sql($sql_insert,"Error al insertar resumen produccion") or fin_pagina();
	       }
	       
	       
	       $sql_total="select id_subir,count(id_subir) as cant_renglon 
                       from licitaciones.renglones_oc  
                       join licitaciones.subido_lic_oc using (id_subir)
                       where id_entrega_estimada=$id_entrega_estimada group by id_subir";
           $res_total=sql($sql_total) or fin_pagina();
           $sql_parcial="select id_subir,count(id_subir) as cant_renglon 
                         from licitaciones.renglones_oc  
                         join licitaciones.subido_lic_oc using (id_subir)
                         where id_entrega_estimada=$id_entrega_estimada and estado=1 group by id_subir";
           $res_parcial=sql($sql_parcial) or fin_pagina(); 
           if ($res_total->fields['cant_renglon']) 
                   $total=$res_total->fields['cant_renglon'];
                   else 
                   $total=0;
           if ($res_parcial->fields['cant_renglon']) 
                   $parcial=$res_parcial->fields['cant_renglon'];
                   else 
                   $parcial=0;
           $finalizado = 0;
           
           //funcion que si todos los renglones relacionados
           ///con  items de una factura estan entregados, la factura
           //se va a tomar en cuenta en el balance en cuentas a cobrar
           insertar_bandera_renglones_entregados($list);
            if ($total>0 && $total==$parcial) {
   	
           //si se entregaron todos los renglones finaliza el seguimiento
           $sql="update entrega_estimada set finalizada=1 where id_entrega_estimada=".$id_entrega_estimada;
            if (sql($sql)) {
      	          $msg1="     SE HA FINALIZADO EL SEGUIMIENTO";    
      	          $finalizado = 1;
            }
            else {
       	       fin_pagina();
            }
            }
  
           if ($db->CompleteTrans()) {
 	             $msg='SE ACTUALIZARON LOS RENGLONES CON EXITO.';
 	             $msg.=$msg1;
 	             if ($finalizado) { 
                     //agregar cuando este perfecto mail seguimiento
		            require_once("funciones.php");
      	            enviar_mail_lic_entregada($id_entrega_estimada);
 	             }
           }
           else $msg='NO SE ACTUALIZARON LOS RENGLONES';
        }
       }
} //fin entregado

if ($_POST['cambiar']=="Cambiar Lugar de Entrega")
{
	$sql="select id_renglones_oc from renglones_oc join subido_lic_oc using(id_subir) where id_entrega_estimada=$id_entrega_estimada";
	$result=sql($sql) or fin_pagina();
	//foreach($result->fields as $key => $value)
    /*
    $sql="select count(id_renglones_oc) as cantidad from log_renglones_oc where id_renglones_oc=".$result->fields["id_renglones_oc"]." and tipo='cambiar lugar entrega'";
    $cant=sql($sql) or fin_pagina();
    if ($cant->fields["cantidad"])
        $q[]="update log_renglones_oc set usuario='$_ses_user_name',fecha_entrega='".date("Y-m-d")."' where id_renglones_oc=".$result->fields["id_renglones_oc"]." and tipo='cambiar lugar entrega'";
        else
        */
    
	//$q[]="insert into log_renglones_oc (id_renglones_oc,usuario,fecha_entrega,tipo) values (".$result->fields["id_renglones_oc"].",'$_ses_user_name','".date("Y-m-d")."','cambiar lugar entrega')";
	
	$q[]="UPDATE subido_lic_oc set lugar_entrega='".$_POST["lugar_entrega"]."' where id_entrega_estimada=$id_entrega_estimada";
	sql($q) or fin_pagina();
}
echo $html_header;

if ($_POST['cambiar_coment']=="Cambiar Comentario") {
	
	$q="UPDATE subido_lic_oc set comentario_adicional='".$_POST["comentario_adicional"]."' where id_entrega_estimada=$id_entrega_estimada";
	sql($q) or fin_pagina();
}
echo $html_header;
cargar_calendario();
?>

<script>
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';
function f_todos(){
	var cantidad=document.all.total_renglones.value;
	
	if (document.all.ch_todos.checked==true){
		for (i=0; i<cantidad; i++){
			var ch_cur=eval("document.all.chk_"+i);
			if (typeof(ch_cur)!=undefined)ch_cur.checked=true;
		}
	}else{
		for (i=0; i<cantidad; i++){
			var ch_cur=eval("document.all.chk_"+i);
			if (typeof(ch_cur)!=undefined) ch_cur.checked=false;
		}
	}
	window.event.cancelBubble=true;
}

function control_atar(){
	var cantidad=document.all.total_renglones.value;
	var checkeado;
	var cont=0;
	for (i=0; i<cantidad; i++){
		checkeado=eval("document.all.chk_"+i+".checked");
		if (checkeado==true){
			cont++;	
		}
	}
	
	if (cont==1) return true;
	else{
		alert ("Debe Seleccionar 1 Renglon");
		return false;
	}
}

function control() {
	var cant=0;  //cantidad de renglones
	var i,ctrl=0;
	
	if (typeof(document.all.cant) !='undefined') {
		if (typeof(document.all.cant.length) !='undefined') cant=document.all.cant.length;
		else cant=1;  
	}
	document.all.h_selected_rows.value='';
	for (i=0;i<cant;i++) {
		c=eval("document.all.chk_"+i);
		
		d=document.getElementById("celda_"+i);
		
  	if  (c.checked){
  		ctrl=1;
  		for (j=0; j<d.childNodes.length; j++){
				if (d.childNodes[j].name=='fact'){
					if (document.all.h_selected_rows.value!='') 
  					document.all.h_selected_rows.value=document.all.h_selected_rows.value+', '+d.childNodes[j].innerText+', '+document.getElementById('tipo_fact_'+d.childNodes[j].innerText).value;					
	  			else 
	  				document.all.h_selected_rows.value=d.childNodes[j].innerText+', '+document.getElementById('tipo_fact_'+d.childNodes[j].innerText).value;
	  				
				}
		}
		document.all.h_facturas.value=document.all.h_selected_rows.value;
  	}
	}

	/*
	for (i=0;i<cant;i++) {
		c=eval("document.all.chk_"+i);
		
		d=document.getElementById("celda_"+i);
		
  	if  (c.checked){
  		ctrl=1;
  		for (j=0; j<d.childNodes.length; j++){
				if (d.childNodes[j].tagName=='A'){
					if (document.all.h_selected_rows.value!='') 
  					document.all.h_selected_rows.value=document.all.h_selected_rows.value+', '+d.childNodes[j].innerText;
	  			else document.all.h_selected_rows.value=d.childNodes[j].innerText;
				}
			}
  	}
	}
	
	
	*/
	
	if (ctrl==1) return true;
	else { 
		alert ("DEBE SELECCIONAR AL MENOS UN RENGLON");
		return false;
	}
}

//controla que no haya fact cuando se presiona el boton entregar sin fact
/*function sin_fact() {
var cant=0;  //cantidad de renglones
var i,ctrl=0;
if (typeof(document.all.cant) !='undefined') {
	if (typeof(document.all.cant.length) !='undefined')
	  cant=document.all.cant.length;
	else cant=1;  
	}
var j;
for (i=0;i<cant;i++) {
j=i+1	
c=eval("document.all.chk_"+i);

   if  (c.checked) {
    ct=eval("document.all.ctl_"+i);
    num=eval("document.all.cod_"+i);
    //alert (num.value);
    
    if (ct.value >0 )  //tiene factura y remitos
   	    ctrl=1;
    else {
    alert ("EL RENGLON DE LA FILA "+ j +" NO DEBE TENER ASOCIADO REMITOS "); 
   	c.checked=0; 
     }
   }
}

if (ctrl==1) {
	alert ("Hay facturas/remitos asociadas");
	return false;
}
else 
	return true;


}*/

//controla que se hayan ingresen facturas y remitos para entregar el renglon
function control_entregado() {
var cant=0;  //cantidad de renglones
var i,ctrl=0;
if (typeof(document.all.cant) !='undefined') {
	if (typeof(document.all.cant.length) !='undefined')
	  cant=document.all.cant.length;
	else cant=1;  
	}
var j;
for (i=0;i<cant;i++) {
j=i+1	
c=eval("document.all.chk_"+i);

   if  (c.checked) {
    ct=eval("document.all.ctl_"+i);
    num=eval("document.all.cod_"+i);
    //alert (num.value);
    
    if (ct.value >0 )  //tiene factura y remitos
   	    ctrl=1;
    else {
    alert ("EL RENGLON DE LA FILA "+ j +" DEBE TENER ASOCIADO REMITOS Y FACTURAS"); 
   	c.checked=0; 
     }
   }
}

if (ctrl==1) return true;
else {
	alert ("DEBE SELECCIONAR RENGLONES CON FACTURAS Y REMITOS")
	return false;
}
}


function contar() {
var cant=0;  //cantidad de renglones
var i,ctrl=0,contador=0;
if (typeof(document.all.cant) !='undefined') {
	if (typeof(document.all.cant.length) !='undefined')
	  cant=document.all.cant.length;
	else cant=1;  
	}
var j;
for (i=0;i<cant;i++) {
j=i+1	
c=eval("document.all.chk_"+i);

   if  ((c.checked)||(c.disabled)) {
        contador=eval(contador)+1;
        //alert (contador);
     }
   }
   //alert("holaaaaaaa"); 
   //alert(contador);
   document.all.contador.value=eval(contador);
   return true;
}

function muestra_tabla(obj_tabla,nro)
{oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 if (obj_tabla.style.display=='none')
    {obj_tabla.style.display='inline';
     oimg.show=0;
     oimg.src=img_ext;
     if (nro==4) oimg.title='Ocultar Archivos';
	 else 
	 {
	if (nro==8) oimg.title='Ocultar Contactos';
	else
	{
	if (nro==9) oimg.title='Ocultar Contactos';
	else
	if (nro==10) oimg.title='Ocultar Mail Entregas';
	else
	 oimg.title='Ocultar Ordenes';;
	}
    }
    }
 else
    {obj_tabla.style.display='none';
    oimg.show=1;
	oimg.src=img_cont;
	if (nro==4) oimg.title='Mostrar Archivos';
	else 
	{
	if (nro==8) oimg.title='Mostrar Contactos';
	else
	{
	if (nro==9) oimg.title='Mostrar Contactos';
	else
	if (nro==10) oimg.title='Mostrar Mail Entregas';
	else
	oimg.title='Mostrar Ordenes'
	}
	}
    }
}

function control_entrega1()
{
 if(document.all.nom_par.value=="")
 {
  alert('Debe llenar el campo nombre del Contacto Presentación de la Factura');	
  return false;
 }
 if(document.all.tele_par.value=="")
 {
  alert('Debe llenar el campo telefono del Contacto Presentación de la Factura');	
  return false;
 }
 return true;
}

</script>

<? 
$sql="select id_subir,simbolo 
	  from licitaciones.subido_lic_oc
	  join licitaciones.licitacion using (id_licitacion)
	  join licitaciones.moneda using (id_moneda)
     where id_entrega_estimada=".$parametros['id_entrega_estimada'];
$res_id=sql($sql,"al seleccionar el id subir") or fin_pagina();
$id_subir=$res_id->fields['id_subir'];
$simbolo=$res_id->fields['simbolo'];
$monto_prod=$parametros['monto_prod'];
if($monto_prod=="")
{
 $monto_prod=$_POST['monto_prod'];
}
$link_form=encode_link('seleccionar_renglon_adj.php',array("id_entrega_estimada"=>$id_entrega_estimada,"licitacion"=>$licitacion,"numero"=>$nro,"pagina_volver"=>'entregas.php',"oc"=>$oc,"cliente"=>$cliente,"vencimiento"=>$vencimiento,"id_subir"=>$id_subir));?>
<!--target="_blank"-->
<!--<form  name='form1' action='../facturas/factura_nueva.php' target="_blank" method="post">-->
<form  name='form1' action='<?=$link_form?>'  method="post">
<input type="hidden" name='cmd' value='facturar_prod'>
<input type="hidden" name='seg' value='<?=$id_entrega_estimada?>'>
<input type="hidden" name='monto_prod' value='<?=$monto_prod?>'>
<input type="hidden" name='volver' value='seleccionar_renglon_adj.php'>
<input type="hidden" name='h_selected_rows' value='<?=$_POST["h_selected_rows"]?>'>
<input type="hidden" name="total_renglones" value="<?=$_POST["ch_todos"]?>">
<?

	
//envia mail si se factura despues que el renglon fue entregado
if ($parametros['exito']==1 || $parametros['exito']==2) { //si se guardo o anulo una factura
$list="";
$contenido="";
  if ($parametros['exito']==1) {
    $renglones=array_recibe($parametros['renglones']);
    $list='(';
    foreach($renglones as $key => $value){
       $list.=$value.',';
    }
   $list=substr_replace($list,')',(strrpos($list,',')));
  } 
  else { //anula la factura
	$list='(';
	$list.=$parametros['renglones'];
	$list.=')';  
    
}

if ($list!="") {

$sql_ent="select estado,codigo_renglon from licitaciones.renglones_oc
          join licitaciones.renglon using (id_renglon)
          where id_renglones_oc in $list";
$res_entregados=sql($sql_ent,"consulta por estado de renglones") or fin_pagina();

while (!$res_entregados->EOF ) {
   if ($res_entregados->fields['estado']==1) {
   	if ($parametros['exito']==1) {
      $contenido.="\n Se ha cambiado la factura del renglon número: ".$res_entregados->fields['codigo_renglon']." ,que ya está entregado,";
      $contenido.=" de la licitacion N° ". $parametros['licitacion']." - seg N°".$parametros['numero'];
      if ($parametros['factura']) {
   	  $sql="select (numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,
   	        tipo_factura 
   	        from facturas 
   	        join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
   	        where id_factura=".$parametros['factura'];	
   	  $res=sql($sql,"al seleccionar nro de factura") or fin_pagina();
   	  $contenido.=" .Nueva Factura F".$res->fields['tipo_factura']."  N° ".$res->fields['nro_factura'];
   	}
   	}
   	elseif ($parametros['exito']==2) {
   	$contenido.=" Se ha anulado la factura del renglon número " .$res_entregados->fields['codigo_renglon'].", que ya está entregado,";
   	$contenido.=" de la licitacion N° ". $parametros['licitacion']." - seg N°".$parametros['numero'];
   	if ($parametros['factura']) {
   	  $sql="select (numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,
   	        tipo_factura 
   	        from facturas
            join facturacion.numeracion_sucursal using(id_numeracion_sucursal)  
   	        where id_factura=".$parametros['factura'];	
   	  $res=sql($sql,"al seleccionar nro de factura") or fin_pagina();
   	  $contenido.=" . Factura Anulada: F ".$res->fields['tipo_factura']." N° ".$res->fields['nro_factura'];
   	}
   	}
   	   	
   }
$res_entregados->MoveNext();
}


$para="juanmanuel@coradir.com.ar";
$asunto="Factura de renglon entregado";
if ($contenido !="") {
	enviar_mail($para,$asunto,$contenido,$nombre_archivo,$path_archivo,$type);
  }
}
}


$sql="select id_renglones_oc,id_renglon,subido_lic_oc.id_licitacion,codigo_renglon,titulo,subido_lic_oc.id_subir,comentario_adicional,
      renglones_oc.cantidad,estado, renglones_oc.precio,subido_lic_oc.lugar_entrega
      from licitaciones.subido_lic_oc
      join licitaciones.renglones_oc using (id_subir)
      join licitaciones.renglon using (id_renglon)

      where id_entrega_estimada=$id_entrega_estimada order by codigo_renglon" ;
$res=sql($sql) or fin_pagina();
$cant_renglones=$res->RecordCount();
if ($res->RecordCount()==0) {
	$permiso="disabled";
    $msg="NO HAY RENGLONES CARGADOS";
}
$lugar_entrega=$res->fields["lugar_entrega"];
$comentario_adicional=$res->fields["comentario_adicional"];
//$id_subir=$res->fields["id_subir"];
echo "<input type=hidden name='id_subir' value='$id_subir'>";


$sql="select renglon.codigo_renglon,
      log_renglones_oc.usuario,log_renglones_oc.tipo,fecha_entrega
      from licitaciones.subido_lic_oc
      join licitaciones.renglones_oc using (id_subir)
      join licitaciones.renglon using (id_renglon)
      left join licitaciones.log_renglones_oc using (id_renglones_oc)
      where id_entrega_estimada=$id_entrega_estimada order by codigo_renglon" ;
$log_renglones_oc=sql($sql) or fin_pagina();
?>
<!-- tabla de log estado entregada de renglones_oc-->
<div style="overflow:auto;height:35;" >
<?
if ($log_renglones_oc->RecordCount()>0) { ?>
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
while (!$log_renglones_oc->EOF) {
 if ($log_renglones_oc->fields['usuario'] !=NULL) {
	?>
  <tr>
<?
	if ($log_renglones_oc->fields['tipo']=="cambiar lugar entrega")
		echo "<td height='20' nowrap><b>Se cambió lugar de entrega</td>\n";
	else
		echo "<td height='20' nowrap><b>NRO RENGLON</b>: ".$log_renglones_oc->fields['codigo_renglon']."</td>\n"
?>
     <td width="35%" nowrap><b>Fecha de entrega</b>: <?=Fecha($log_renglones_oc->fields['fecha_entrega'])?></td>
     <td nowrap width="25%" > <b>Usuario</b>: <?=$log_renglones_oc->fields['usuario']?> </td>
 </tr>
<?
}
 $log_renglones_oc->MoveNext();
}
?>
</table>
<?
}
?>

<!-- tabla de log de facturas-->
<?$log_fact="select (numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,
             usuario,fecha,tipo_log
	         from facturacion.items_factura
		     join facturacion.facturas using (id_factura)
		     join facturacion.log using (id_factura)
		     join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
		     join (
			select id_renglones_oc
			from licitaciones.subido_lic_oc
				join licitaciones.renglones_oc using (id_subir)
			where id_entrega_estimada=$id_entrega_estimada
		)as tmp0 on (tmp0.id_renglones_oc=items_factura.id_renglones_oc and (tipo_log='creacion' or tipo_log='anulacion'))";
$log=sql($log_fact) or fin_pagina();
if ($log->RecordCount()>0) {
?>
<!-- tabla de registro -->
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
do
{
?>
<tr>
      <td height="20" nowrap><b>NRO FACTURA:</b> <?=$log->fields['nro_factura']?> </td>
      <td width="35%" nowrap><b>Fecha de <?=$log->fields['tipo_log'].": </b>".fecha($log->fields['fecha'])?> </td>
      <td nowrap width="25%"><b> Usuario: </b><?=$log->fields['usuario'] ?> </td>
</tr>
<?
 $log->MoveNext();
}
while (!$log->EOF);
?>
</table>
<?
}

$sql="select (numeracion_sucursal.numeracion || text('-') || remitos.nro_remito) as nro_remito, 
      usuario,fecha,tipo_log 
      from facturacion.items_remito 
      join facturacion.remitos using (id_remito) 
      join facturacion.log using (id_remito)
      join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
      join( select id_renglones_oc from licitaciones.subido_lic_oc 
      join licitaciones.renglones_oc using (id_subir) where id_entrega_estimada=$id_entrega_estimada)
      as tmp0 on(tmp0.id_renglones_oc=items_remito.id_renglones_oc 
      and (tipo_log='creacion' or tipo_log='anulacion')) ";
$log_r=sql($sql) or fin_pagina();
if ($log_r->RecordCount()>0 ) {
?>
<!-- tabla de log_remitos -->

<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
do
{
?>
<tr>
      <td height="20" nowrap><b>NRO REMITO:</b> <?=$log_r->fields['nro_remito']?> </td>
      <td width="35%"><b>Fecha de creacion: </b> <?=fecha($log_r->fields['fecha'])?> </td>
      <td nowrap width="25%"><b> Usuario: </b><?=$log_r->fields['usuario']; ?> </td>
</tr>
<?
 $log_r->MoveNext();
}
while (!$log_r->EOF);
?>
</table>
<!--</div>-->
<?} //fin log_remito
?>

</div>

<hr>
<?
$res->MoveFirst();
echo "<div align='center'><font size='3' color='blue'>".$msg."</font></div>" ?>
<table align="center" width="95%">
<tr>
<td><b>LICITACION CON  ID </b> <?=$licitacion?> <b> SEG N°</b> <?=$nro?></td>
<td colspan="2"> <b>ORDEN DE COMPRA: </b>
     <?
     $sql="select id_subir,nro_orden from subido_lic_oc where id_entrega_estimada=$id_entrega_estimada";
     $roc=sql($sql) or fin_pagina();
     $id_subir=$roc->fields["id_subir"];
     $oc=$roc->fields["nro_orden"];
     $link=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$id_subir,"solo_lectura"=>1));
     ?>
     <a href="<?=$link?>" target="_blank">
       <?=$oc?>
     </a>
</td>
</tr>
<tr>
<td colspan=2><b>CLIENTE: <?=$cliente?></b></td>
<?

$link=encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$licitacion, "id_entrega_estimada"=>$parametros['id_entrega_estimada'], "nro_orden"=>$result->fields["nro_orden"],"nro"=>$nro,"id_subir"=>$id_subir,"nro_orden_cliente"=>$oc));?>
<td><input type="button" name="boton_entrega" value="Ver seguimiento" onclick="window.open('<?=$link;?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1')" style="cursor:hand"></td>
</tr>
<tr>
<td colspan=3><b> VENCIMIENTO DE ENTREGA</b> <?=Fecha($vencimiento)?></td>
</tr>
<tr>
 <?$sql="select nombre,apellido from licitaciones.licitacion
           left join sistema.usuarios on (lider=id_usuario)
           where id_licitacion=$licitacion";
     $resul_lider=sql($sql,"Error al traer el lider de la Licitación") or fin_pagina();
     if ($resul_lider->RecordCount()>0) $lider=$resul_lider->fields['apellido'].", ".$resul_lider->fields['nombre'];
     else $lider="Lider no Cargado";
   ?>
 <td colspan=2><b> LIDER LICITACION</b> &nbsp;<?=$lider?>&nbsp;  <b>Distrito del Cliente</b> &nbsp;<?=$nom_cli?></td>  
 <td>
 <?
 $link_ent = encode_link("configurar_entrega.php",array("id_entrega_estimada"=>$id_entrega_estimada,"id"=>$licitacion,"oc"=>$oc,"cliente"=>$cliente));
 echo "&nbsp;&nbsp;<input type=button name=c_entrega value='Configuracion de Entregas' onclick='window.open(\"$link_ent\",\"\",\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\");'>&nbsp;&nbsp;";
 ?>
 </td>
</tr>
</table>
<br>
<?if (permisos_check("inicio","atar_factura_en_entrega")){
		$link_atar_f=encode_link('atar_factura.php',array());
		$link_atar_r=encode_link('atar_remito.php',array());?>
      	<div align="right">
	   	<input type="submit" name="atar_facturas" value="Atar Facturas" onclick="form.action='<?=$link_atar_f?>';form.target='_blank';return control_atar();">
	   	&nbsp;&nbsp;&nbsp;
	   	<input type="submit" name="atar_remitos" value="Atar Remitos" onclick="form.action='<?=$link_atar_r?>';form.target='_blank';return control_atar();">
		</div>
<?}?>
<table align="center" bgcolor='<?=$bgcolor3?>'>
 
 <tr>
     <td width='5%' id='mo'><input type="checkbox" name="ch_todos" onclick="f_todos();">Todos</td>
     <td width='20%'align="center" id='mo'>NUMERO RENGLON</td>
     <td width='5%' align="center" id='mo'>CANTIDAD</td>     
     <td width='20%'align="center" id='mo'>TITULO RENGLON</td>
     <td width='20%'align="center" id='mo'>PRECIO</td>
     <td width='20%'align="center" id='mo'>NRO FACTURA</td>
     <td width='20%'align="center" id='mo'>NRO REMITO</td>
     <td width='20%'align="center" id='mo'>ESTADO</td>
     
</tr>
<? 
$ind=0;
$datos_reng=array();
while (!$res->EOF) {
	 $id_renglon=$res->fields['id_renglones_oc'];
	 $precio=$res->fields['precio'] * $res->fields['cantidad'];
	 $datos_reng[$id_renglon]['precio']= $precio;
	 $datos_reng[$id_renglon]['codigo_renglon']=$res->fields['codigo_renglon'];
	 $sql_factura="select id_factura,facturas.estado,(numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,
	               tipo_factura,cant_prod, precio 
                   from facturacion.items_factura 
                   join facturacion.facturas using (id_factura) 
                   join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
                   where id_renglones_oc=$id_renglon";
	 $res_fact=sql($sql_factura);
	 $facturas=array();
	 $diferencia_monto=array();
	 $i=0;
	 $fact_anuladas=0;
	 $precio_total_facturas=0;
	 $tiene_permiso2="";
	 while(!$res_fact->EOF) {
	 
	   $facturas[$i]=array();
	   $facturas[$i]['id_fact']=$res_fact->fields['id_factura'];
	   $facturas[$i]['tipo_fact']=$res_fact->fields['tipo_factura'];
	   $facturas[$i]['nro_fact']=$res_fact->fields['nro_factura'];
	   $facturas[$i]['estado']=$res_fact->fields['estado'];
	   $facturas[$i]['precio_fact']=$res_fact->fields['cant_prod']*$res_fact->fields['precio'];
	   //
	   if ($res_fact->fields['estado']=='a' && $res->fields['estado']==1) $fact_anuladas++;
	   elseif ($res_fact->fields['estado']=='a') $fact_anuladas++;
	   else $precio_total_facturas+=$facturas[$i]['precio_fact'];
	   //echo "precio total fact ".$facturas[$i]['nro_fact']." - ".$precio_total_facturas."<br>";
	   $i++;
	   $res_fact->MoveNext();
	   }
	  
	 //$diferencia_monto[$id_renglon]=$precio_total_facturas-$res->fields['precio']; 
	 $monto_renglon=$res->fields['precio']*$res->fields['cantidad'];
	 $diferencia_monto[$id_renglon]=$monto_renglon-$precio_total_facturas; 
	 //echo "diferencia ".$id_renglon." - ".$diferencia_monto[$id_renglon];
	 //$diferencia_monto[$i]=$res->fields['precio']-$precio_total_facturas; 
	 if ($diferencia_monto[$id_renglon]<=0) {
	    $tiene_permiso2="disabled";
      }
     else  $tiene_permiso2="";  
         
     $cant_fact=count($facturas);
	 
	 $sql_rem="select id_remito,remitos.estado,(numeracion_sucursal.numeracion || text('-') || remitos.nro_remito) as nro_remito,
	           cant_prod, precio 
	           from facturacion.remitos 
	           join facturacion.numeracion_sucursal using(id_numeracion_sucursal)
               left  join facturacion.items_remito using(id_remito)          
               where id_renglones_oc=$id_renglon";

	 $res_rem=sql($sql_rem);
	 $remitos=array();
	 $i=0;
	 $rem_anulados=0;
	 while(!$res_rem->EOF) {
	   $remitos[$i]=array();
	   $remitos[$i]['id_remito']=$res_rem->fields['id_remito'];
	   $remitos[$i]['nro_remito']=$res_rem->fields['nro_remito'];
	   $remitos[$i]['estado']=$res_rem->fields['estado'];
	   $remitos[$i]['precio_rem']=$res_rem->fields['cant_prod']*$res_rem->fields['precio'];
	   if ($res_rem->fields['estado']=='a') $rem_anulados++;
	   else $precio_total_remitos+=$remitos[$i]['precio_rem'];
	   $i++;
	   $res_rem->MoveNext();
	 }
	 $cant_rem=count($remitos);

	 if (($cant_rem-$rem_anulados==0) || ($cant_fact-$fact_anuladas==0)) $control=0;
	    else $control=1;

//Descripcion: Ordenes-Permiso facturar entregas
//permiso de noelia aunque este entregado puede seguir facturando (por ej en el caso que el prov perdido una factura)


if (permisos_check("inicio","permisos_fact_entrega"))  
   $tiene_permiso="";
   else {
       if ($res->fields['estado']==1)
        $tiene_permiso="disabled";
        else
        $tiene_permiso="";
   }

$dif=$diferencia_monto[$id_renglon];	   
	?>
<tr <?=$atrib_tr?>>
<? /*<input type="hidden" name="dif_<?=$id_renglon?>" value="<?=$dif?>"> */?>
 <td align="center"> <input type="checkbox" name="chk_<?=$ind?>" value="<?=$res->fields['id_renglones_oc']?>"  <?if ($res->fields['estado']==1) {?> title='renglon entregado'<?}?> >
 <?/* <input type="hidden" name='id_renglon_oc' value='<?=$res->fields['id_renglones_oc']?>'>*/?>
</td>
<td> <input type='text' name='cod_<?$ind?>' readonly value='<?=$res->fields['codigo_renglon']?>'></td>


<td align="center">
   <input type='text' name="cant" size='5' readonly value='<?=$res->fields['cantidad']?>'>
</td>
<?
 $id_renglones_oc=$res->fields['id_renglones_oc'];
?> 
<input type="hidden" name="ctl_<?=$ind?>" value="<?=$control?>">

<td> <textarea name='titulo' cols='45' rows='1' wrap='VIRTUAL' readonly ><?=$res->fields['titulo']?></textarea></td>
<td align="center"><?echo $simbolo." ". formato_money($precio) ?> </td>
<td id="celda_<?=$ind?>"> <?
      for($k=0;$k<$cant_fact;$k++){ 
      	$link_f=encode_link('../facturas/factura_nueva.php',array("id_factura"=>$facturas[$k]['id_fact'],"seg"=>$id_entrega_estimada,"volver"=>'seleccionar_renglon_adj.php',"id_renglon"=>$res->fields['id_renglones_oc'],"dif"=>$diferencia));
      	if ($facturas[$k]['estado']=='a') {
	        $estilo='color:red;font-weight:bold;';
					$title="anulada";
					$name="";
	      }else {
		    	$estilo='color:black;font-weight:bold;';
					$title="";
					$name="name='fact'";
				}
	   		echo "<a href='$link_f' $name target='_blank' style=".$estilo." title='$title'>". $facturas[$k]['nro_fact']."</a>";
	   		echo "<input type='hidden' name='tipo_fact_".$facturas[$k]['nro_fact']."' value='".$facturas[$k]['tipo_fact']."'>";
	   		echo " ";
      }
      $nro_fila_renglon++;
       // control del precio del renglon con lo que se factura
      ?>
</td>
<td> <?for($k=0;$k<$cant_rem;$k++){
       $link_r=encode_link('../remitos/remito_nuevo.php',array("remito"=>$remitos[$k]['id_remito'],"seg"=>$id_entrega_estimada,"volver"=>'seleccionar_renglon_adj.php'));
       if ($remitos[$k]['estado']=='a') {
	        $estilo='color:red;font-weight:bold;';
			$title="anulada";
	        }
          else {
		    $estilo='color:black;font-weight:bold;';
			$title="";
			}
       echo "<a href='$link_r' target='_blank' style=".$estilo." title='$title'>".$remitos[$k]['nro_remito']."</a>";
	   		echo " ";
       }
      ?>
</td>
<td <?if ($res->fields['estado']==1) echo "title='renglon entregado'"?>>
<?if ($res->fields['estado']==1) echo "Entregado"?>
</td>
</tr>
<?
$ind++;
$res->MoveNext();
}
?>
	  	<script>
	  	document.all.total_renglones.value=<?=$ind?>;
	  	</script>
<?
$valores=array_envia($datos_reng);
echo "<input type='hidden' name='datos_renglones' value='$valores'>";
?>
<input type="hidden" name="contador" value="">
</table>
<?
$link_fact=encode_link('../facturas/factura_nueva.php',array("cmd"=>'facturar_prod',"seg"=>$id_entrega_estimada,"volver" =>'seleccionar_renglon_adj.php',"id_renglon"=>$id_renglon));
$link1=encode_link($pagina_volver,array("id_entrega_estimada"=>$id_entrega_estimada,"licitacion"=>$id,"numero"=>$nro));
$link2=encode_link('../remitos/remito_nuevo.php',array("seg"=>$id_entrega_estimada,"cmd"=>'crear_remito',"pagina_volver"=>'seleccionar_renglon_adj.php'));
//$link2=encode_link('../remitos/remito_nuevo.php',array("seg"=>$id_entrega_estimada,"cmd"=>'crear_remito',"pagina_volver"=>'seleccionar_renglon_adj.php', "modo"=>"reload"));

?>
<br>
<div align="center">
<input type="hidden" name="h_facturas" value="">
<input type='submit' name='crear_facturar' value='Crear Factura' onclick="form.action='<?=$link_fact?>';form.target='_blank';return control();"  <?=$permiso?>>
<input type='submit' name='crear_remito' value='Crear Remito'  onclick="form.action='<?=$link2?>';form.target='_blank';return control();" <?=$permiso?>>
<? if (permisos_check("inicio","permiso_boton_entregado")) {?>
<input type='submit' name='entregado' value='Entregado'  onclick="form.action='<?=$link_form?>';return contar();/*return control_entregado();*/" <?=$permiso?>>
<?}?>
<? if (permisos_check("inicio","entrega_sin_factura")) {  //descripcion: Ordenes-entregar renglon sin fact/remito ?>
   <input type='submit' name='entregar' value='Entregar s/factura'  onclick="form.action='<?=$link_form?>';return control();" <?=$permiso?>>
<? } ?>

<?
if ($parametros["pagina_volver"]=="lic_cobranzas.php"){

?>
   <input type='button' name='cerrar' value='Cerrar' onclick="window.close()">
<?
      }
      else{

?>   <input type='button' name='volver' value='Volver' onclick="location.href='<?=$link1?>'">

<?
}

?>

<?
if (permisos_check("inicio","permiso_botones_envios")) {
$q_select2="select id_envio_renglones, id_datos_envio 
           from licitaciones_datos_adicionales.envio_renglones
           left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones)
           join licitaciones.subido_lic_oc using (id_licitacion)
           where id_entrega_estimada=$id_entrega_estimada and envio_cerrado=0";
$res_q_select2=sql ($q_select2, "Error al traer el id de envío para la entrega") or fin_pagina();
$id_envio_renglones=$res_q_select2->fields['id_envio_renglones'];
$id_datos_envio=$res_q_select2->fields['id_datos_envio'];

array("menu"=>"ord_compra_listar","extra"=>array("filtro"=>"todas","keyword"=>$ID,"filter"=>"o.id_licitacion","volver_lic"=>$ID, "cmd"=>"todas"));

 $link_envio=encode_link("preparar_envios.php", array("id_entrega_estimada"=>$id_entrega_estimada, "id_envio_renglones"=>$id_envio_renglones, "id_datos_envio"=>$id_datos_envio));
 //$link_listado=encode_link("listado_envios.php", array("menu"=>"listado_envios", "extra"=>array("filtro"=>"todas","keyword"=>$id_entrega_estimada,"filter"=>"id_entrega_estimada")));
 $link_listado=encode_link("listado_envios.php", array("id_entrega_estimada"=>$id_entrega_estimada,"pagina"=>"seleccionar_renglon_adj"));
 ?>
  <input type="button" name="envio_renglon" value="Envío" title="Generar un envío" onclick="window.open('<?=$link_envio?>','','toolbar=1,location=0,directories=1,status=1,resizable=1, menubar=1,scrollbars=1,left=125,top=10,width=800,height=600')">
  <input type="button" name="listado_envios" value="Listado Envíos" title="Muestra un listado de los envíos realizados" onclick="window.open('<?=$link_listado?>','','toolbar=1,location=0,directories=1,status=1, menubar=1,scrollbars=1,left=125,top=10,width=800,height=600')">
<? } 
$sql_tmp="select distinct id_envio_renglones, 
          nro_oc_mod, envio_renglones.id_licitacion, entidad_mod, nombre_envio_origen, 
          licitaciones.distrito.nombre as nombre_envio_destino, deco_envio_destino, deco_envio_origen,
          nombre_transporte, telefono_transporte,envio_renglones.cantidad_total
         from licitaciones_datos_adicionales.envio_renglones 
          left join licitaciones_datos_adicionales.renglones_bultos using (id_envio_renglones) 
          left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones)         
          left join licitaciones_datos_adicionales.envio_origen using (id_envio_origen)         
          left join licitaciones_datos_adicionales.envio_destino using (id_envio_destino)         
          left join licitaciones.distrito using (id_distrito)         
          left join licitaciones_datos_adicionales.log_envio_renglones using (id_envio_renglones) 
          left join licitaciones.renglones_oc using (id_renglones_oc)
          left join licitaciones.subido_lic_oc using (id_licitacion) 
          left join licitaciones_datos_adicionales.transporte using (id_transporte)
         where envio_renglones.id_licitacion=$licitacion and envio_renglones.envio_cerrado<>2 
         ";

$res_query = sql($sql_tmp,"No se pudo recuperar los envios ligados a esta lici") or fin_pagina();

?>

<!--
<? /*Sacar despues de probar
$permiso_especial=""; if (($_SESSION["user"] != "daniele") || ($_SESSION["user"] != "juanmanuel")) $permiso_especial= "disable" ?>
<input type='submit' name='prueba_fin_seguimento' value='Prueba mail' <?=$permiso_especial*/?> title="Usar para probar el mail de seguimiento">
-->
</div>
<hr>
<?
if ($res_query->RecordCount()>0 ) {
?>
<table border=0 width=95% cellspacing=2 cellpadding=3 align="center">
  
  <tr>
      <td align="center" id=mo><b>Nro. Envio</b></td>
      <td align="center" id=mo><b>Transporte</b></td>
      <td align="center" id=mo><b>Tel&eacute;fono</b></td>
      <td align="center" id=mo><b>Id. Licitación</b></td>
      <td align="center" id=mo><b>Origen</b></td>
      <td align="center" id=mo><b>Destino</b></td>
      <td align="center" id=mo><b>Entidad</b></td>
      <td align="center" id=mo><b>Cant. Bultos</b></td>
  </tr>
   <?
 while (!$res_query->EOF) {
 	 $nro_envio=$res_query->fields['id_envio_renglones'];
     $id=$res_query->fields['id_licitacion'];
     $entidad=$res_query->fields['entidad_mod'];
     $id_envio_renglones=$res_query->fields['id_envio_renglones'];
     $serial=$res_query->fields["deco_envio_origen"]."-".$res_query->fields["deco_envio_destino"]."-";
     $serial.=str_pad($id_envio_renglones,10,'0',STR_PAD_LEFT);
	 $cant_b=$res_query->fields["cantidad_total"];
	/*
    $q_a="select distinct  id_entrega_estimada, cantidad_total
           from licitaciones.subido_lic_oc 
           left join licitaciones_datos_adicionales.envio_renglones using (id_subir) 
		   where id_envio_renglones=$id_envio_renglones";
     $res_q_a=sql($q_a, "Error al traer el id_entrega_estimada") or fin_pagina();
     $id_entrega_estimad=$res_q_a->fields['id_entrega_estimada'];
	
     $q_c="select cantidad_total
           from licitaciones_datos_adicionales.envio_renglones  
		   where id_envio_renglones=$id_envio_renglones";
     $res_q_c=sql($q_c, "Error al traer el id_entrega_estimada") or fin_pagina();
     $cant_b=$res_q_c->fields['cantidad_total'];
	*/  
    
     $ref=encode_link("preparar_envios.php", array("id_entrega_estimada"=>$id_entrega_estimada, "id_licitacion"=>$id, "id_envio_renglones"=>$id_envio_renglones, "pagina"=>"listado","id_entidad"=>$id_ent));
     tr_tag($ref);
    ?>
    <td align="center" style="cursor:hand"><?=$serial?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nombre_transporte'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['telefono_transporte'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['id_licitacion'] ?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nombre_envio_origen']?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['nombre_envio_destino']?></td>
	<td align="center" style="cursor:hand"><?=$res_query->fields['entidad_mod'] ?></td>
	<td align="center" style="cursor:hand"><?=$cant_b?></td>
   </tr>
   <? 		
   $res_query->MoveNext();
   } 
   ?>
</table>
<?
}
?>
<hr>

<table align="center">
  <tr> 
      <td >
          <font size=2><b>Comentario identificando licitacion en Factura/Remito</b></font><br><br>
           <textarea name="comentario_adicional" cols=70 rows=8><?=$comentario_adicional;?></textarea><br>
           <input type="submit" name="cambiar_coment" value="Cambiar Comentario" >
     </td>
     <td>
          <font size=2><b>Lugar de Entrega</b></font><br><br>
           <textarea name="lugar_entrega" cols=70 rows=8><?echo $lugar_entrega;?></textarea><br><input type="submit" name="cambiar" value="Cambiar Lugar de Entrega">
     </td>
    
  </tr>
</table>
<?//////////////////////////////////////////quique/////////////////////////?>
<br>
<?
$luga=$lugar_entrega;
$id_ent = $parametros["id_entrega_estimada"];
$tabla=mostrar_contactos_segumientos($id_ent,$luga,1,0,$fecha1);
echo $tabla;
?>
<br>
<?
$id_ent = $parametros["id_entrega_estimada"];
$tabla1=mostrar_contactos_segumientos1($id_ent);
echo $tabla1;
$sql_mail = "select * from mail_entregas where id_entrega_estimada =$id_entrega_estimada and id_licitacion=$licitacion";
$result_mail = sql($sql_mail,"Error recolectando datos: $sql_mail");
if($result_mail->RecordCount()==0)
{
$sql_contacto = "select nombre,telefono,mail from contacto_seguimiento where id_entrega_estimada =$id_entrega_estimada and estado=1";
$result_contacto = sql($sql_contacto,"Error recolectando datos: $sql_contacto");
$contacto=$result_contacto->fields['nombre'];
$tel_cont=$result_contacto->fields['telefono'];
$mails=1;
}
else {
 $fecha1=$result_mail->fields['fecha_entregas'];
 $id_mail=$result_mail->fields['id_mail_entregas'];
 $contacto=$result_mail->fields['contacto'];
 $tel_cont=$result_mail->fields['telefono_contacto'];
 $mercaderias=$result_mail->fields['mercaderias'];
 $dest_mail=$result_mail->fields['mail_destinatarios'];
 $banda=$result_mail->fields['banda_horaria'];
 list($desde,$hasta)=explode("-",$banda);	
 $mails=0;
}
?>
<br>
<table border=1 width='100%' cellpadding=0>
<input type="hidden" name="mails" value="<?=$mails?>">
<input type="hidden" name="id_mail" value="<?=$id_mail?>">
 <tr align="center" id="mo">
  <td align="center" width="3%">
   <img id="imagen_10" src="<?=$img_cont?>" border=0 title="Mostrar Comentarios" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.mail,10);" >
  </td>
  <td align="center">
   <b>Preparar Mail Entregas</b>
  </td>
 </tr>
</table>
<table id="mail" border="1" width="100%" style="display:none;border:thin groove" border=1 bordercolor=black cellpadding=0 cellspacing=1 rules="none">
<tr><td>
 <table align="center" width="100%">
  <tr>
     <td width=20% valign=top colspan="2">
     <b>Orden de Despacho:</b>
     </td>
  </tr>
  <tr>    
     <td>
     <b>ID:<font color="Blue"><?=$licitacion?></font></b>
     </td>
     <td>
     <b>Cliente<font color="Blue"><?=$cliente?></font></b>
     </td>
  </tr>
 <tr>    
     <td>
     <b>Fecha Entrega:
     <input type="text" name="fecha_ent" value="<?=Fecha($fecha1)?>" size="9"><?=link_calendario('fecha_ent');?></b>
     </td>
     <td>
     <b>Horario de Entrega:Desde<input type="text" name="desde" size="8" value="<?=$desde?>">
     Hasta<input type="text" name="hasta" size="8" value="<?=$hasta?>"></b>
     </td>
 </tr>
 <tr>    
     <td>
     <b>Contacto Entrega:<input type="text" name="nom_cont" value="<?=$contacto?>" size="50"></b>
     </td>
     <td><b>Telefono:<input type="text" name="tel_cont" value="<?=$tel_cont?>"size="25"></b>
     </td>
 </tr>
 <tr>    
     <td colspan="2">
     <b>Mercaderia a Entregar</b>
     </td>
 </tr>
 <tr>    
   <td colspan="2"><b><textarea name='mercaderias' cols='85' rows='7' wrap='VIRTUAL'><?=$mercaderias?></textarea></b></td>
 </tr>
 <tr>
  <td colspan="2">
   <b><font color="Red" size="2">(*)</font>Destinatarios Mail:</b>
   </td>
  </tr> 
 <tr>    
   <td colspan="2"><b><textarea name='correo' cols='85' rows='2' wrap='VIRTUAL'><?=$dest_mail?></textarea></b></td>
 </tr>
 <tr>    
   <td colspan="2"><b><font color="Red" size="2">* Ingrese los mail separados por coma</font></b></td>
 </tr>

 <tr>
  <td align="center" colspan="2">
  <input type="submit" name="enviar_mail" value="Enviar Mail">
   </td>
   </tr>
   </table>
</td></tr>
</table>
<!--********************************FIN QUIQUE*****************************************-->

<?
$sql_arch="select * from arch_seguimiento where id_entrega_estimada=$id_entrega_estimada";
$res_arch=sql($sql_arch) or fin_pagina();
echo "<hr>\n";
echo "<img src='../../imagenes/mas.gif' border=0 style='cursor: hand;' onClick='if (this.src.indexOf(\"mas.gif\")!=-1) {
	this.src=\"../../imagenes/menos.gif\";
	div_comunicar.style.overflow=\"visible\";
	} else {
	this.src=\"../../imagenes/mas.gif\";
	div_comunicar.style.overflow=\"hidden\";
	}'>\n";
$sql = "SELECT id_comentario FROM ";
$sql .= "gestiones_comentarios WHERE id_gestion=$licitacion ";
$sql .= "AND tipo='COMUNICAR_CLIENTE'";
$resu=sql($sql) or fin_pagina();
if ($resu->recordcount()>=1) 
	echo "&nbsp;<b><blink tipo='color'>Comunicaciones con el cliente</blink></b>\n";
else
	echo "&nbsp;<b>Comunicaciones con el cliente</b>\n";
echo "<div id='div_comunicar' style='border-width: 0;overflow: hidden;height: 1'>\n";
gestiones_comentarios($licitacion,"COMUNICAR_CLIENTE",0);
echo "</div>\n";?>
<hr>
<table align="center">
<tr>
<td colspan='4'><strong>Archivos Cargados :</strong>
<? if ($res_arch->RecordCount()>0){ ?>
	<tr><td colspan='4'>
	<div align='center'>
	  <table>
	  <tr id=mo>
	   <td align='center'><B>Eliminar</B></td>
	   <td align='center'>Nombre</td>
	   <td align='center'>Fecha de Cargado</td>
	   <td align='center'>Cargado Por</td></tr>
	   <?$i=0;
		 while (!$res_arch->EOF) {
		   echo "<tr><td align='center'><input name='elim_".$i."' type='checkbox' value='".$res_arch->fields['id_archivo_seg']."' ></td>";
		   echo "<td>";
			      if (is_file("../../uploads/archivos_seg/".$id_entrega_estimada.'-'.$res_arch->fields["nbre_arch"])){
                  echo "<a href='".encode_link("seleccionar_renglon_adj.php",array ("id_entrega_estimada"=>$id_entrega_estimada,"file" =>$res_arch->fields["nbre_arch"],"size" => $res_arch->fields["tam_arch"],"cmd1" => "download"))."'>";
                  echo $res_arch->fields["nbre_arch"]."</a>";}
            echo "</td>\n";
			echo "<td align='center'>"; echo Fecha($res_arch->fields['fecha_carga'])."</td>\n";
			echo "<td align='center'>"; echo $res_arch->fields['subidopor']."</td>\n";
            $i++;
			$res_arch->MoveNext();
			echo "</tr>";
         }?>
		</table>
		</div>

		</td></tr>
		</td></tr>
		<?}
		else {?>
        <table><tr><td><?='No hay archivos cargados';?></td></tr>
		</table></td></tr>
		<? }?>
</tr>
<?$link3=encode_link("seg_subir_arch.php", array("id_entrega_estimada"=>$id_entrega_estimada,"licitacion"=>$licitacion,"numero"=>$nro,"volver"=>'seleccionar_renglon_adj.php',"oc"=>$oc,"cliente"=>$cliente,"vencimiento"=>$vencimiento));?>
<tr> <? if ($res_arch->RecordCount() > 0) { ?>
      <td><input name='eliminar' type='submit' value='Eliminar Archivo' onclick="form.action='<?=$link_form?>'"> </td>
      <?}?>
      <td><input name="Subir" type="button" value="Subir Archivo" onClick="location.href='<?=$link3?>'"></td>
</tr>
</table>
<?
//traemos los comentarios del producto en transito

?>

<table width="95%" align="center" class=bordes>
 <tr id=mo><td><font size="3">Comentarios</font></td></tr>
 <tr>
  <td>
   <table align="center" width="100%">
    <?
    //generamos los comentarios ya cargados
    while(!$comentarios->EOF)
    {

	$long_desc=ceil(strlen($comentarios->fields["comentario"])/64);
	if($descripcion!="")
	 $cant_barra_n=substr_count("\n",$descripcion);
	else
	 $cant_barra_n=0;
	$rows=$cant_barra_n+$long_desc+1;

	$usuario = $comentarios->fields["id_usuario"];
     $sql = "select  (nombre || ' ' || apellido) as nombre from usuarios where id_usuario = $usuario";
     $result_usuario = $db->execute($sql) or die($db->errormsg()."<br>".$sql);
     $usuario = $result_usuario->fields['nombre'];

     if ($comentarios->fields['id_prorroga']=="")
      $modulo = "<font color='blue'><b>Cargado en Entregas</b></font>";
     else
      $modulo = "<font color='blue'><b>Cargado en Prorrogas</b></font>";

     if ($comentarios->fields['comentario']!="") { ?>
     <tr>
      <td width=20% valign=top>
       <table width="100%">
        <tr  id="ma_sf">
          <td width="65%" align="right">
          <b>
          <?
           $fecha=split(" ",$comentarios->fields['fecha_comentario']);
           echo fecha($fecha[0])." ".$fecha[1];
          ?>
          </b>
          </td>
         </tr>
         <tr id="ma_sf">
          <td align="right">
           <?="$usuario<br>$modulo";?>
          </td>
        </tr>
       </table>
      </td>
      <td>
       <textarea rows="<?= $rows?> " style="width:100%" readonly name="coment_<?=$comentarios->fields['id_comentarios_seguimientos']?>"><?=$comentarios->fields['comentario']?></textarea>
      </td>
     </tr>
     <?
     }
     $comentarios->MoveNext();
    }
    //y luego damos la opcion a guardar uno mas
    ?>
    <tr>
     <td colspan="2">
      <table>
       <tr>
        <td width="25%"  id="ma_sf">
         <b>Nuevo Comentario</b>
        </td>
        <td width="75%">
         &nbsp;<textarea rows="4" cols="70" name="nuevo_coment"></textarea>
        </td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<br>
<table width="80%" align="center">
 <tr>
  <td align="center">
   <input type="submit" name="guardar" value="Guardar"
     onclick="form.action='<?=$link_form?>';if(document.all.nuevo_coment.value=='')
              {alert('No se puede guardar un comentario vacio');
               return false;
              }
             "
   >

   <?
if ($parametros["pagina_volver"]=="lic_cobranzas.php"){
   ?>
   <input type='button' name='cerrar' value='Cerrar' onclick="window.close()">
    <?
    }
    else{
    ?>
    <input type='button' name='volver' value='Volver' onclick="location.href='<?=$link1?>'">
    <?
    }
    ?>
  </td>
 </tr>
</table>
</form>
</html>

<?
echo fin_pagina();
?>
