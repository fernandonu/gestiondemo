<?php
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: nazabal $
$Revision: 1.137 $
$Date: 2007/03/27 21:31:56 $

*/

require_once("../../config.php");
require_once("funciones_prod_bsas.php");

//print_r($parametros); echo("<br>");
//print_r($_POST["cmd"]); echo("<br>");
//////////////////////////////////////////// GABRIEL ////////////////////////////////////////
$gag_modo=$parametros["gag_cmd"] or $gag_modo=$_POST["gag_cmd"];
$gag_id_renglon=$parametros["gag_id_renglon"] or $gag_id_renglon=$_POST["gag_id_renglon"];
if ($parametros) $por_tanda=$parametros['por_tanda'];
else $por_tanda=$_POST['por_tanda'];

/////////////////////////////////////////////////////////////////////////////////////////////

function gen_serial($id_ensamblador) {
	global $db;
	global $rs,$serialp,$serialu;
	$sql="select letra from ensamblador where id_ensamblador=$id_ensamblador";
	$letra=sql($sql) or fin_pagina();
	$serialu=$serialp=$letra->fields['letra'];
	list($d,$m,$a)=explode("/",fecha($rs->fields["fecha_inicio"]));
	switch ($a) {
		case 2003:$serialu.='Q';$serialp.='Q';break;
		case 2004:$serialu.='R';$serialp.='R';break;
		case 2005:$serialu.='S';$serialp.='S';break;
		case 2006:$serialu.='T';$serialp.='T';break;
		case 2007:$serialu.='U';$serialp.='U';break;
		case 2008:$serialu.='V';$serialp.='V';break;
	} //fin switch
	$serialp.=$d; //calculamos dia
	$serialu.=$d;
	switch ($m) {
		case '01':$serialu.='CE';$serialp.='CE';break;
		case '02':$serialu.='CF';$serialp.='CF';break;
		case '03':$serialu.='CM';$serialp.='CM';break;
		case '04':$serialu.='AA';$serialp.='AA';break;
		case '05':$serialu.='AM';$serialp.='AM';break;
		case '06':$serialu.='AJ';$serialp.='AJ';break;
		case '07':$serialu.='JJ';$serialp.='JJ';break;
		case '08':$serialu.='JA';$serialp.='JA';break;
		case '09':$serialu.='JS';$serialp.='JS';break;
		case '10':$serialu.='OO';$serialp.='OO';break;
		case '11':$serialu.='ON';$serialp.='ON';break;
		case '12':$serialu.='OD';$serialp.='OD';break;
	}// fin switch

	$query="Select * from serial";
	$temp = sql($query) or fin_pagina();
	$primer=0;
	$primer=($temp->fields['nro']+1)%1000; //obtengo la primer maquina
	//$pserial=$primer-1;
	$letra="";
	if ($primer==000) {
		if (trim(chr(ord($temp->fields['letra3'])+1)) > 'Z') {
			if (trim(chr(ord($temp->fields['letra2'])+1)) > 'Z') {
				$letra1=trim(chr(ord($temp->fields['letra1'])+1));
				if ($letra1=='M') $letra='MB';
				else $letra.=$letra1.trim(chr(ord('A')));
			}
			else {
				$letra=trim(chr(ord($temp->fields['letra1'])));
				$letra2=trim(chr(ord($temp->fields['letra2'])+1));
				if ($temp->fields['letra1']=='E' && $letra2=='N') $letra.=trim(chr(ord($letra2)+1));
				if ($temp->fields['letra1']=='S' && $letra2=='E') $letra.=trim(chr(ord($letra2)+1));
				if ($temp->fields['letra1']=='O' && $letra2=='M') $letra.=trim(chr(ord($letra2)+1));

			}
			$letra.=trim(chr(ord('A')));
		}
		else {
			$letra=trim(chr(ord($temp->fields['letra1'])));
			$letra.=trim(chr(ord($temp->fields['letra2'])));
			$letra.=trim(chr(ord($temp->fields['letra3'])+1));
		}
	}
	else {
		$letra=trim(chr(ord($temp->fields['letra1'])));
		$letra.=trim(chr(ord($temp->fields['letra2'])));
		$letra.=trim(chr(ord($temp->fields['letra3'])));
	}
	$serialp.=$letra;
	$letra="";
	//$primer_ser=$serialpri.$primer;    //obtengo el primer numero de serial
	$ultimo=($primer+$rs->fields['cant_prod']-1)%1000; //obtengo la primer maquina
	if ($rs->fields['cant_prod']+$temp->fields['nro']>1000) { //actualizamos la letra
		if (trim(chr(ord($temp->fields['letra3'])+1)) > 'Z') {
			if (trim(chr(ord($temp->fields['letra2'])+1)) > 'Z') {
				$letra1=trim(chr(ord($temp->fields['letra1'])+1));
				if ($letra1=='M') $letra='MB';
				else $letra.=$letra1.trim(chr(ord('A')));
			}
			else {
				$letra=trim(chr(ord($temp->fields['letra1'])));
				$letra2=trim(chr(ord($temp->fields['letra2'])+1));
				if ($temp->fields['letra1']=='E' && $letra2=='N') $letra.=trim(chr(ord($letra2)+1));
				if ($temp->fields['letra1']=='S' && $letra2=='E') $letra.=trim(chr(ord($letra2)+1));
				if ($temp->fields['letra1']=='O' && $letra2=='M') $letra.=trim(chr(ord($letra2)+1));

			}
			$letra.=trim(chr(ord('A')));
		}
		else {
			$letra=trim(chr(ord($temp->fields['letra1'])));
			$letra.=trim(chr(ord($temp->fields['letra2'])));
			$letra.=trim(chr(ord($temp->fields['letra3'])+1));
		}
	}
	else {
		$letra=trim(chr(ord($temp->fields['letra1'])));
		$letra.=trim(chr(ord($temp->fields['letra2'])));
		$letra.=trim(chr(ord($temp->fields['letra3'])));
	}
	$serialu.=$letra;
	//$letra=trim($temp->fields['letra']);//$temp->fields['letra'];
	if ($primer<100) //concateno valor con los 0 que pueden llegar a faltar
		$serialp.='0';
	if ($primer<10)
		$serialp.='0';
	if ($ultimo<100) //concateno valor con los 0 que pueden llegar a faltar
		$serialu.='0';
	if ($ultimo<10)
		$serialu.='0';
	$serialp.=$primer;
	$serialu.=$ultimo;

}//fin funcion gen_serial


function modificar($datos) {
	global $db;


	if (!$datos["id_entidad"])
                            $error="No ha seleccionado una entidad.<br>";
	                        else {
		                        $campo[]="id_entidad";
		                        $valor[]=$datos["id_entidad"];
	                        }
	if (FechaOk($datos["fechainicio"])) {
		$campo[].=",fecha_inicio";
		$valor[].="'".Fecha_db($datos["fechainicio"])."'";
	    }
	    else
        $error.="Falta ingresar la fecha de inicio de la orden de producción.";

	$campo[].=",fecha_entrega";

	if (FechaOk($datos["fechaentrega"]))
		$valor[].="'".Fecha_db($datos["fechaentrega"])."'";
	    else
		$valor[].="NULL";
	$campo[].=",lugar_entrega";
	$valor[].="'".$datos["lugar_entrega"]."'";
	// Etiquetas
	$campo[].=",titulo_etiqueta";
	$valor[].="'".$datos["texto_titulo"]."'";
	$campo[].=",descripcion_etiqueta";
	$valor[].="'".$datos["texto_descripcion"]."'";
	// Productos
	if (!$datos["desc_prod"]) $error.="Falta colocar la descripcion del producto.<br>";
	$campo[].=",desc_prod";
	$valor[].="'".$datos["desc_prod"]."'";
	if (!$datos["desc_prod"]) $error.="Falta colocar la cantidad de producto.<br>";
	$campo[].=",cantidad";
	$valor[].="".$datos["cant_prod"];
	$campo[].=",id_sistema_operativo";
	$valor[].="".(($datos["sist_instalado"] > 0)?$datos["sist_instalado"]:"NULL");
    $campo[].=",clave_root";



    $valor[].="'".$datos["clave_root"]."'";

	$campo[].=",comentario";
	$valor[].="'".$datos["comentario"]."'";
	$campo[].=",adicionales";
	$valor[].="'".$datos["adicionales"]."'";
	if ($datos["id_ensamblador"]) {
		$campo[].=",id_ensamblador";
		$valor[].=$datos["id_ensamblador"];
  	    }

	$query="UPDATE orden_de_produccion SET ";
	$i=0;
	while ($i<count($campo)) {
		$query.=$campo[$i]."=".$valor[$i];
		$i++;
	    }
	$query.=" where nro_orden=".$datos["nro_orden"];
	$q[]=$query;
	// Accesorios
	$h=0;
	$num_ord=$datos["nro_orden"];
	while ($h<=2) {
		$acc_desc="'".$datos["observ_$h"]."'";
		$esp1="'".$datos["esp1_$h"]."'";
		$q[]="UPDATE accesorios SET descripcion=$acc_desc,esp1=$esp1 Where nro_orden=$num_ord and tipo=$h";
		$h++;
    }
	// Microfono
	if ($datos["lleva_microfono"]) $lleva="'on'";
	                         else $lleva="'off'";
	$acc_desc="'".$datos["observ_3"]."'";
	$q[]="UPDATE accesorios SET descripcion=$acc_desc,esp1=$lleva Where nro_orden=$num_ord and tipo=3";
	
	
	// Floppy
	if ($datos["lleva_floppy"]) $lleva="'on'";
	                       else $lleva="'off'";
	$q[]="UPDATE accesorios SET esp1=$lleva Where nro_orden=$num_ord and tipo=4";
	
	//etiquetas windows
	
	if ($datos["etiquetas_windows"]) {
		$acc_desc="'".$datos["observacion_etiquetas"]."'";
		$esp1="'".$datos["etiquetas_windows"]."'";
		$q[]="UPDATE accesorios SET descripcion=$acc_desc,esp1=$esp1 Where nro_orden=$num_ord and tipo=5";
	     
	}
	
	// items


	$query="select id_fila from filas_ord_prod where nro_orden=".$datos["nro_orden"];
	$rs=sql($query) or fin_pagina();
	$cant_db=$rs->RecordCount();


	while ($fila=$rs->FetchRow()) {
		$j=1;

		while ($j<=$datos["item"]) {
			if ($fila["id_fila"]==$datos["id_fila_$j"])
				break;
			$j++;
		}
		 if ($j>$datos["item"])
			     $q[]="DELETE FROM filas_ord_prod WHERE id_fila=".$fila["id_fila"];

		else {

			$q[]="UPDATE filas_ord_prod SET
				id_prod_esp=".$datos["id_$j"]."
				,descripcion='".$datos["desc_$j"]."'
				,cantidad=".$datos["canti_$j"]."
				,orden=".$datos["orden_$j"]."
				 WHERE id_fila=".$datos["id_fila_$j"];
		    }
	 } // del while


	$j=1;


	while ($j<=$datos["item"]) {
		if (!$datos["id_fila_$j"] and $datos["id_$j"]) {
			if ($datos["provid_$j"])
                           $provid=$datos["provid_$j"];
			               else
                           $provid="NULL";
			$q[]="INSERT INTO filas_ord_prod (nro_orden,id_prod_esp,descripcion,cantidad,orden)
                  VALUES "
				 ."(".$datos["nro_orden"].",".$datos["id_$j"].",'".$datos["desc_$j"]."',".$datos["canti_$j"].",".$datos["orden_$j"].")";
		}
		$j++;
	}
	return $q;
} // de la funcion modificar datos

if ($_POST["modo"]=="Guardar") {

    $db->starttrans();

	$error="";
	$query="select nextval('ordenes.orden_de_produccion_nro_orden_seq') as nro_orden";
	$rs=sql($query) or fin_pagina();
	$orden["campo"]="nro_orden";
	$orden["valor"]=$rs->fields["nro_orden"];
	if (!$_POST["id_entidad"])
                      $error="No ha seleccionado una entidad.<br>";
                else {
		            $orden["campo"].=",id_entidad";
		            $orden["valor"].=",".$_POST["id_entidad"];
	            }
	if ($_POST["id_renglon"]) {
		$orden["campo"].=",id_renglon";
		$orden["valor"].=",".$_POST["id_renglon"];
	    }
	if ($_POST["id_licitacion"]) {
		$orden["campo"].=",id_licitacion";
		$orden["valor"].=",".$_POST["id_licitacion"];
	}
	if ($_POST["id_ensamblador"]) {
		$orden["campo"].=",id_ensamblador";
		$orden["valor"].=",".$_POST["id_ensamblador"];

	}
	// Sistema Operativo
	$orden["campo"].=",id_sistema_operativo";
	$orden["valor"].=",".(($_POST["sist_instalado"] > 0)?$_POST["sist_instalado"]:"NULL");

    $orden["campo"].=",clave_root";
    $orden["valor"].=",'".(($_POST["clave_root"])?$_POST["clave_root"]:"")."'";

	if (FechaOk($_POST["fechainicio"])) {
		$orden["campo"].=",fecha_inicio";
		$orden["valor"].=",'".Fecha_db($_POST["fechainicio"])."'";
	  }
	  else
      $error.="Falta ingresar la fecha de inicio de la orden de producción.<br>";


	$orden["campo"].=",fecha_entrega";
	if (FechaOk($_POST["fechaentrega"]))
		$orden["valor"].=",'".Fecha_db($_POST["fechaentrega"])."'";
	    else
		$orden["valor"].=",NULL";

	$orden["campo"].=",lugar_entrega";
	$orden["valor"].=",'".$_POST["lugar_entrega"]."'";
	// Etiquetas
	$orden["campo"].=",titulo_etiqueta";
	$orden["valor"].=",'".$_POST["texto_titulo"]."'";
	$orden["campo"].=",descripcion_etiqueta";
	$orden["valor"].=",'".$_POST["texto_descripcion"]."'";
	// Productos
	if (!$_POST["desc_prod"]) $error.="Falta colocar la descripcion del producto.<br>";
	$orden["campo"].=",desc_prod";
	$orden["valor"].=",'".$_POST["desc_prod"]."'";
	if (!$_POST["desc_prod"]) $error.="Falta colocar la cantidad de producto.<br>";
	$orden["campo"].=",cantidad";
	$orden["valor"].=",".$_POST["cant_prod"];
	$orden["campo"].=",comentario";
	$orden["valor"].=",'".$_POST["comentario"]."'";
	$orden["campo"].=",adicionales";
	$orden["valor"].=",'".$_POST["adicionales"]."'";
	$orden["campo"].=",estado";
	$orden["valor"].=",'P'";
	$sql[]="INSERT INTO orden_de_produccion (".$orden["campo"].") VALUES (".$orden["valor"].")";
	// Accesorios
	$i=0;
	$num_ord=$rs->fields["nro_orden"];
	while ($i<=2) {
		$acc_desc="'".$_POST["observ_$i"]."'";
		$esp1="'".$_POST["esp1_$i"]."'";
		$sql[]="INSERT INTO accesorios (nro_orden,descripcion,esp1,tipo) Values
			    ($num_ord,$acc_desc,$esp1,$i)";
		$i++;
	}
	// Microfono
	if ($_POST["lleva_microfono"]) $lleva="'on'";
 	                          else $lleva="'off'";
	$acc_desc="'".$_POST["observ_3"]."'";

	$sql[]="INSERT INTO accesorios (nro_orden,descripcion,esp1,tipo) Values "
		    ."($num_ord,$acc_desc,$lleva,3)";
	// Floppy
	if ($_POST["lleva_floppy"]) $lleva="'on'";
	                       else $lleva="'off'";
	$sql[]="INSERT INTO accesorios (nro_orden,esp1,tipo) Values "
		."($num_ord,$lleva,4)";
		
	//etiquetas
     $etiquetas_windows=$_POST["etiquetas_windows"];
     $observacion_etiquetas=$_POST["observacion_etiquetas"];
	$sql[]="INSERT INTO accesorios (nro_orden,esp1,descripcion,tipo) Values ($num_ord,'$etiquetas_windows','$observacion_etiquetas',5)";
	 
	// items
	$i=1;

	while ($i<=$_POST["item"]) {
		if ($_POST["id_$i"]) {
			$fila["campo"]="nro_orden";
			$fila["valor"]=$rs->fields["nro_orden"];
			$fila["campo"].=",id_prod_esp";
			$fila["valor"].=",".$_POST["id_$i"];
			$fila["campo"].=",descripcion";
			$fila["valor"].=",'".$_POST["desc_$i"]."'";
			$fila["campo"].=",cantidad";
			$fila["valor"].=",".$_POST["canti_$i"];
			$fila["campo"].=",orden";
			$fila["valor"].=",'".$_POST["orden_$i"]."'";
			$sql[]="INSERT INTO filas_ord_prod (".$fila["campo"].") VALUES (".$fila["valor"].")";
		}
		$i++;
	}

	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES
		     (".$rs->fields["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Creado')";

	if ($error) {
		$msg=$error;
		header("location: ".encode_link("ordenes_nueva.php",array("modo"=>"nuevo","volver"=>$_POST["volver"],"msg"=>$msg)));
	    }
	    else {
		    sql($sql) or fin_pagina();
		    $msg="Los datos se modificaron correctamente.";
		    header("location: ".encode_link("ordenes_nueva.php",array("modo"=>"modificar","nro_orden"=>$rs->fields["nro_orden"],"volver"=>$_POST["volver"],"msg"=>$msg)));
	    }

   $db->completetrans();
}  // de modo guardar


if ($_POST['actualizar']) {
	$fecha= date("d/m/Y",mktime());
   	if ($_POST['cmd']=="pendientes") {
                           $val=1;
                           $donde="al estado en Producción";
                           }
    if ($_POST['cmd']=="produccion") {
                            $val=5;
                            $donde="a Inspeccion";
                             }
    if ($_POST['cmd']=="inspeccion") {
                            $val=3;
                            $donde="a Embalaje";
                            }
    if ($_POST['cmd']=="embalaje") {
                            $val=4;
                            $donde="a Calidad";
                            }

    if($_POST['id_licitacion']!="")
                            {
              $sql="select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador from licitaciones.licitacion l
	          left join sistema.usuarios u1 on (lider=u1.id_usuario and u1.id_usuario<>16)
	          left join sistema.usuarios u2 on (patrocinador=u2.id_usuario and u2.id_usuario<>16)where id_licitacion=".$_POST['id_licitacion'];
    		  $resul_lider=sql($sql,"No se pudo traer el lider de la Licitación") or fin_pagina();
                            $resul_lider=sql($sql,"No se pudo traer el lider de la Licitación") or fin_pagina();
                            }

    $sql="update ordenes.orden_de_produccion set estado_bsas=$val where nro_orden=".$_POST['nro_orden'];
    $resul=sql($sql,"No se pudo realizar el update en orden de produccion $sql") or fin_pagina();
    sql("insert into ordenes.log_op_bsas (usuario, nuevo_estado, observaciones, nro_orden)values('".$_ses_user["login"]."', $val, '$donde', ".$_POST['nro_orden'].")", "c31") or fin_pagina();
    //$para="juanmanuel";
    if($val==4)
              {
              $mail = array (0 => "aranzubia@coradir.com.ar",
                             1 => "carlos@coradir.com.ar"
                             );
              $i=2;
              }
              else
                {
                 if($val==5)
                    {
		            $can=$_POST['pasa_cantidad'];
		            $cant_q=$_POST['cant_que'];
		            if($can>$cant_q)
		                {
			            $menor=1;
			            $mail = array (0 => "juanmanuel@coradir.com.ar",
                                       1 => "valentino@coradir.com.ar",
                                       2 => "andrada@coradir.com.ar"
                                       );
                        $i=3;
		                }

                     }

                     else
                      $i=0;
                }//del else

    if($_POST['id_licitacion']!="")
    {
     while (!$resul_lider->EOF)
     {
    	$mail[$i]=$resul_lider->fields['mail_lider'];
    	$i++;
    	$mail[$i]=$resul_lider->fields['mail_patrocinador'];
    	$resul_lider->MoveNext();
     }
    }
    $para=elimina_repetidos($mail,0);

    //$para="broggi@coradir.com.ar,nazabal@coradir.com.ar";
    $asunto="Paso de Orden de Producción Nº ".$_POST['nro_orden']." $donde";
    $mensaje="La Orden de Producción Nº ".$_POST['nro_orden']." se paso $donde.";
    if($menor==1)
    $mensaje.="\n No paso la cantidad de verificaciones correctas de Prueba de vida";
    $mensaje.="\n--------------------------Breve Descripción de la Orden--------------------------";
    $mensaje.="\nID. Licitación:        ".$_POST['pasa_id'];
    $mensaje.="\nCliente:               ".$_POST['pasa_cliente'];
    $mensaje.="\nFecha Entrega:         ".$_POST['pasa_fecha'];
    $mensaje.="\nCantidad de Maquinas:  ".$_POST['pasa_cantidad'];
    $mensaje.="\nTitulo:                ".$_POST['pasa_titulo'];
    $mensaje.="\nDescripción:           ".$_POST['pasa_descripcion'];
    $mensaje.="\n---------------------------------------------------------------------------------------";
    $mensaje.="\nEl cambio se realizo el día $fecha, por el Usuario ".$_ses_user['name'];
   // echo"$para <br>$mensaje<br>";
    //die();
   enviar_mail($para,$asunto,$mensaje,"","","",0);

    if (($_POST['cmd']=="inspeccion")||($_POST['cmd']=="embalaje")){

    	$para="";
    	$asunto="";
    	$mensaje="";
	    if ($_POST['cmd']=="inspeccion") {
	                            $donde="a Embalaje";
	                            }
	    if ($_POST['cmd']=="embalaje") {
	                            $donde="a Calidad";
	                            }

	    if($_POST['id_licitacion']!="")
	    {
	     while (!$resul_lider->EOF)
	     {
	    	$mail[$i]=$resul_lider->fields['mail_lider'];
	    	$i++;
	    	$mail[$i]=$resul_lider->fields['mail_patrocinador'];
	    	$resul_lider->MoveNext();
	     }
	    }
	    $para=elimina_repetidos($mail,0);

	    $id_licitacion_mail="";
	    if ($_POST['id_licitacion']!="") $id_licitacion_mail=", asociado al ID Nº ".$_POST['id_licitacion']. ". ";

	    $asunto="Paso de Orden de Producción Nº ".$_POST['nro_orden']." $donde $id_licitacion_mail";
	    $mensaje.="\n----------------------------------ATENCION----------------------------------";
	    $mensaje.="\nLa Orden de Producción Nº ".$_POST['nro_orden']."$id_licitacion_mail se paso $donde, lo que";
	    $mensaje.="\nsignifica que esta Pronta a ser DESPACHADA.";
	    $mensaje.="\n\nDebería Pedir:";
	    $mensaje.="\n	1) Que se Facturen las Computadoras.";
	    $mensaje.="\n	2) Coordinar con el Cliente la Entrega.";
	    $mensaje.="\n	3) Avisar a las Áreas Interesadas que Coordinen el Trasporte o Custodia.";
	    $mensaje.="\n----------------------------------------------------------------------------";
	    $mensaje.="\nEl cambio se realizo el día $fecha, por el Usuario ".$_ses_user['name'];
	    //echo $para . "<br>" . $asunto . "<br>" . $mensaje;
	    enviar_mail($para,$asunto,$mensaje,"","","",0);
    }


    ?>
    <script>
    document.location.href="seguimiento_produccion_bsas.php";
    </script>
    <?

   }	// del if de actualizar


   if ($_POST['actualizar1'] || $_POST['actualizar2']) {
     $db->StartTrans();

       if ($_POST['actualizar1']) $estado_bsas=2;
       if ($_POST['actualizar2']) $estado_bsas="NULL";

	   $fecha= date("d/m/Y",mktime());
   	   $op = $_POST['nro_orden'];
	   /*si reprueba el control de calidad los que hace es aumentar en uno el campo de
	   prioridad y aumentar el uno el campo reprobo_calidad */
	   if ($_POST['actualizar2']){
		   	//hace update y aumenta en 1 el campo reprobo_calidad

		   	$sql="update ordenes.orden_de_produccion set reprobo_calidad=reprobo_calidad+1 where nro_orden=".$op;
	        sql($sql,"No se pudo realizar el update en orden de produccion $sql") or fin_pagina();
			//aumenta la prioridad en uno
		   	sql("update licitaciones.linea_produccion_bsas set prioridad=prioridad+1 where nro_orden= $op", "c70") or fin_pagina();
	   }

	   $sql="update ordenes.orden_de_produccion set estado_bsas=$estado_bsas where nro_orden=".$_POST['nro_orden'];
       $resul=sql($sql,"No se pudo realizar el update en orden de produccion $sql") or fin_pagina();

       sql("insert into ordenes.log_op_bsas (usuario, nuevo_estado, observaciones, nro_orden)values('".$_ses_user["login"]."', -1, 'Aprobó auditoría (".$_POST["accion_reproceso"].")', ".$_POST['nro_orden'].")", "c122") or fin_pagina();

       $sql="insert into auditorias (nro_orden, estado, usuario, fecha_hora, accion) values (".$_POST['nro_orden'].", ";
	    if ($_POST["actualizar1"]) {
		  $sql.="'true', ";
		  $sql_estado="update orden_de_produccion set estado_audit='t' where nro_orden=".$_POST['nro_orden'];
		  sql($sql_estado) or fin_pagina();//update
		} else {
				$sql.="'false', ";
			}
			$sql.="'".$_ses_user["name"]."', '".date("Y-m-d H:i:s")."', '".$_POST["accion_reproceso"]."')";

		sql($sql) or fin_pagina();//insert

       if($_POST['id_licitacion']!="")
                {
                $sql= "select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador from licitaciones.licitacion l
	                      left join sistema.usuarios u1 on (lider=u1.id_usuario and u1.id_usuario<>16)
	                      left join sistema.usuarios u2 on (patrocinador=u2.id_usuario and u1.id_usuario<>16)
                          where id_licitacion=".$_POST['id_licitacion'];
                $resul_lider=sql($sql,"No se pudo traer el lider de la Licitación") or fin_pagina();
                }
                //$para="juanmanuel";
                if ($_POST['actualizar1']) {
                           				$donde="a Historial; Aprobo Auditoria de Calidad. Listo para Entregar";
                           				}
                if ($_POST['actualizar2']) {
                           				$donde="a Pendiente: REPRUEBA Auditoria de Calidad. No se puede Entregar";
                           				}

                $mail = array (0 => "juanmanuel@coradir.com.ar", 1 => "carlos@coradir.com.ar", 2 => "aranzubia@coradir.com.ar",
				3 => "valentino@coradir.com.ar", 4 => "andrada@coradir.com.ar");

   				$i=5;
                if($_POST['id_licitacion']!="")
                {
   				while (!$resul_lider->EOF)
                    {
    	            $mail[$i]=$resul_lider->fields['mail_lider'];
    	            $i++;
    	            $mail[$i]=$resul_lider->fields['mail_patrocinador'];
    	            $resul_lider->MoveNext();
                   }
                }
                $para=elimina_repetidos($mail,0);

                //$para="broggi@coradir.com.ar,nazabal@coradir.com.ar";
                $asunto="Paso de Orden de Producción Nº ".$_POST['nro_orden']." $donde";
                $mensaje="La Orden de Producción Nº ".$_POST['nro_orden']." se paso $donde.";
                $mensaje.="\n--------------------------Breve Descripción de la Orden--------------------------";
                $mensaje.="\nID. Licitación:        ".$_POST['pasa_id'];
                $mensaje.="\nCliente:               ".$_POST['pasa_cliente'];
                $mensaje.="\nFecha Entrega:         ".$_POST['pasa_fecha'];
                $mensaje.="\nCantidad de Maquinas:  ".$_POST['pasa_cantidad'];
                $mensaje.="\nTitulo:                ".$_POST['pasa_titulo'];
                $mensaje.="\nDescripción:           ".$_POST['pasa_descripcion'];
                $mensaje.="\nAcción de reproceso:\n".$_POST["accion_reproceso"];
                $mensaje.="\n---------------------------------------------------------------------------------------";
                $mensaje.="\nEl cambio se realizo el día $fecha, por el Usuario ".$_ses_user['name'];
				//echo"$para <br>$mensaje<br><br>";
                //die();

               enviar_mail($para,$asunto,$mensaje,"","","",0);
    $db->CompleteTrans();

    ?>
    <script>
    document.location.href="seguimiento_produccion_bsas.php";
    </script>
    <?
    die();
   }

//------------------------------------------------------------------------------
// si la orden fue dividida en algun momento
 if ($_POST['actualizar1_tanda'] || $_POST['actualizar2_tanda']) {
 	$db->StartTrans();
     $cant_a_ingresar=$_POST['cant_por_tanda'];

       if ($_POST['actualizar1_tanda']) {
       	    $estado=2;  //aprueba pasa a historial
            $orden=6;
            $cartel="Aprobó ";
       }
       elseif ($_POST['actualizar2_tanda']) {
       	   $estado=0; //reprueba pasa a pendientes
       	   $orden=1;
       	   $cartel="Reprobó ";
       }

	   $fecha= date("d/m/Y",mktime());
   	   $op = $_POST['nro_orden'];
	   /*si reprueba el control de calidad los que hace es aumentar en uno el campo de
	   prioridad y aumentar el uno el campo reprobo_calidad */
	   if ($_POST['actualizar2_tanda']){
		   	//hace update y aumenta en 1 el campo reprobo_calidad

		   	$sql="update ordenes.prod_bsas_por_tanda set reprobo_calidad_bsas=reprobo_calidad_bsas+1
		   	      where nro_orden=".$op;
	        sql($sql,"No se pudo realizar el update en orden de produccion por tanda $sql") or fin_pagina();
			//aumenta la prioridad en uno
		   	sql("update licitaciones.linea_produccion_bsas set prioridad=prioridad+1 where nro_orden= $op", "c70") or fin_pagina();
     }

       buscar_ordenes($op,4,5,$cant_a_ingresar,$cant_a_ingresar); //actual 4->calidad
       buscar_ordenes($op,$estado,$orden,$cant_a_ingresar,-1);

	   //LOG
	   $sql="insert into ordenes.log_op_bsas (usuario, nuevo_estado, observaciones, nro_orden)
	         values('".$_ses_user["login"]."', -1, '$cartel auditoría (".$cant_a_ingresar." Máquinas)', ".$_POST['nro_orden'].")";
       sql("$sql", "$sql c122") or fin_pagina();

       //cambia el estado de la orden si ya pasan todas a historial
       $act=actualiza_estado_bsas(4,$op,"prod_bsas_por_tanda.estado_bsas_por_tanda in(0,1,5,3)",$estado);

       $sql="insert into auditorias (nro_orden, estado, usuario, fecha_hora, accion) values (".$_POST['nro_orden'].", ";
	    if ($_POST["actualizar1_tanda"]){
		$sql.="'true', ";
		if ($act==1) { //si pasaron todas a historial
		$sql_estado[]="update orden_de_produccion set estado_audit='t' where nro_orden=".$_POST['nro_orden'];
		$sql_estado[]="update prod_bsas_por_tanda set reprobo_calidad_bsas=0 where nro_orden=".$_POST['nro_orden'];
		sql($sql_estado) or fin_pagina();//update
		}
		}else{
				$sql.="'false', ";
			}

			$sql.="'".$_ses_user["name"]."', '".date("Y-m-d H:i:s")."', '".$_POST["accion_reproceso"]."')";
	  sql($sql) or fin_pagina();//insert

	  if ($_POST["actualizar2_tanda"]){ //si reprueba pasa toda la orden a pendiente
	  $est="NULL";
	     $sql="update ordenes.orden_de_produccion set estado_bsas=$est,reprobo_calidad=1 where nro_orden=".$_POST['nro_orden'];
         $resul=sql($sql,"No se pudo realizar el update en orden de produccion $sql") or fin_pagina();
	  }
       if($_POST['id_licitacion']!="")
                {
                $sql= "select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador from licitaciones.licitacion l
	                      left join sistema.usuarios u1 on (lider=u1.id_usuario and u1.id_usuario<>16)
	                      left join sistema.usuarios u2 on (patrocinador=u2.id_usuario and u1.id_usuario<>16)
                          where id_licitacion=".$_POST['id_licitacion'];
                $resul_lider=sql($sql,"No se pudo traer el lider de la Licitación") or fin_pagina();
                }
                //$para="juanmanuel";
                if ($_POST['actualizar1_tanda']) {
                           				$donde="a Historial; Aprobo Auditoria de Calidad. Listo para Entregar";
                           				}
                if ($_POST['actualizar2_tanda']) {
                           				$donde="a Pendiente: REPRUEBA Auditoria de Calidad. No se puede Entregar";
                           				}

                $mail = array (0 => "juanmanuel@coradir.com.ar", 1 => "carlos@coradir.com.ar", 2 => "aranzubia@coradir.com.ar",
				3 => "valentino@coradir.com.ar", 4 => "andrada@coradir.com.ar");

   				$i=5;
                if($_POST['id_licitacion']!="")
                {
   				while (!$resul_lider->EOF)
                    {
    	            $mail[$i]=$resul_lider->fields['mail_lider'];
    	            $i++;
    	            $mail[$i]=$resul_lider->fields['mail_patrocinador'];
    	            $resul_lider->MoveNext();
                   }
                }
                $para=elimina_repetidos($mail,0);

                //$para="broggi@coradir.com.ar,nazabal@coradir.com.ar";
                $asunto="Paso de Orden de Producción Nº ".$_POST['nro_orden']." $donde";
                $mensaje="La Orden de Producción Nº ".$_POST['nro_orden']." se paso $donde.";
                $mensaje.="\n--------------------------Breve Descripción de la Orden--------------------------";
                $mensaje.="\nID. Licitación:        ".$_POST['pasa_id'];
                $mensaje.="\nCliente:               ".$_POST['pasa_cliente'];
                $mensaje.="\nFecha Entrega:         ".$_POST['pasa_fecha'];
                $mensaje.="\nCantidad de Maquinas:  ".$_POST['pasa_cantidad'];
                $mensaje.="\nTitulo:                ".$_POST['pasa_titulo'];
                $mensaje.="\nDescripción:           ".$_POST['pasa_descripcion'];
                $mensaje.="\nAcción de reproceso:\n".$_POST["accion_reproceso"];
                $mensaje.="\n---------------------------------------------------------------------------------------";
                $mensaje.="\nEl cambio se realizo el día $fecha, por el Usuario ".$_ses_user['name'];
				//echo "<br> MENsAJE ".$mensaje;
               enviar_mail($para,$asunto,$mensaje,"","","",0);


    $db->CompleteTrans();

     ?>
    <script>
    document.location.href="seguimiento_produccion_bsas.php";
    </script>
    <?
  die();
   }


//-------------------------------------------------------------------------------

//fin de las accciones por post relacionadas con la base de datos


//recupero el modo
if ($_GET["modo"])
                 $modo=$_GET["modo"];
                 else
                 $modo=$parametros["modo"] or $modo=$_POST["modo"];



$volver=$_POST["volver"] or $volver=$parametros["volver"];
if ($parametros["pagina"]) $modo="asociado_lic";
//if (!$modo) $modo="asociado";
if (!$modo) $modo="nuevo";
if($modo=="asociar") {
	echo $html_header;
    ?>
	<br><br><br><br>
	<form name='form1' action='ordenes_nueva.php' method='POST'>
	<input type='hidden' name='modo' value='asociado'>
	<table width='60%' align='center' class='bordes'>
	<tr id=mo>
        <td>
	    <font size=3>Asociar Orden de Produccíon a:</font>
	    </td>
    </tr>
	<tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.radio_asociar[0].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='lic'> Licitación
	    </td>
    </tr>
	<tr align='left' bgcolor="<?=$bgcolor_out?>" onclick='document.all.radio_asociar[1].checked=true'>
	    <td>
	    <input type='radio' name='radio_asociar' value='otro'> No asociado
	    </td>
    </tr>
	<tr bgcolor=<?=$bgcolor3?>><td align='center'><br>
	<input type='submit' name='boton_asociar' value='Asociar'>
	</td></tr>
    </table>
    </form>
    <?
}
elseif($modo=="asociado") {
	if ($_POST["radio_asociar"]=="lic") {
		$link=encode_link("../licitaciones/licitaciones_view.php",array("backto"=>"../ordprod/ordenes_nueva.php","modo"=>"asociado_lic","nro_orden"=>$nro_orden,"pag"=>"asociado_lic","_ses_global_extra"=>array()));
		header("location:$link");
		exit();
	    }
	    else
        $modo="nuevo";
     }
     elseif($modo=="asociado_lic")
               {
                //cuanto esta asociado a la licitacion
	            echo $html_header;
	            $licitacion=$parametros["licitacion"] or $licitacion=$_POST["licitacion"];
	            variables_form_busqueda("ordenes_nuevas");
	            //borra la variable en caso de que venga desde licitaciones
	            if ($_ses_global_backto)
	            {
		            extract($_ses_global_extra,EXTR_SKIP);
		            phpss_svars_set("_ses_global_backto", "");
		            phpss_svars_set("_ses_global_extra", array());
	            }


	            if (!$sort) $sort="1";

	            $orden = array(
		            "default" => "1",
		            "default_up" => 0,
		            "1" => "codigo_renglon",
		            "2" => "cantidad",
		            "3" => "titulo",
	            );

	            $filtro = array(
		            "codigo_renglon"	=> "Renglón",
		            "cantidad"	=> "Cantidad",
		            "Titulo"	=> "Titulo"

	            );
	            $query_tmp= "SELECT id_renglon,titulo,cantidad,codigo_renglon FROM renglon";
                $where_tmp= " id_licitacion = $licitacion ";
	            $link_temp = Array(
		                    "sort" => $sort,
		                    "up" => $up,
		                    "filter" => $filter,
		                    "keyword" => $keyword,
		                    "modo" => "asociado_lic",
		                    "licitacion" => $licitacion
  	                        );

                ?>
                <form action='ordenes_nueva.php' name='buscar' method='post'>
                <input type=hidden name=sort value='<?=$sort?>'>
                <input type=hidden name=modo value='asociado_lic'>
                <input type=hidden name='licitacion' value='<?=$licitacion?>'>
                <table width=99% class=bordes>
                <tr id=mo>
                    <td colspan=3 align=center>
                    Seleccione el renglón correspondiente para  realizar la producción
                    </td>
                </tr>
                <tr>
                <td colspan=3 align=center>
                <?
	            list($sql,$total,$link_pagina,$up2) = form_busqueda($query_tmp,$orden,$filtro,$link_temp,$where_tmp,"buscar");
	            $rs1 = sql($sql) or fin_pagina();
                ?>
	            <input type='submit' name=enviar value=Buscar>
	            <table width=99% class=bordes>
	            <tr>
                  <td style='border-right: 0;' align=left id=ma>
	               <b>Total:</b> <?=$total?> Renglones.
                   </td>
	               <td style='border-left: 0;' colspan=2 align=right id=ma>&nbsp;<?=$link_pagina?>&nbsp;</td>
               </tr>
                <?
	            $link_temp["page"]=$page;
	            $link_temp["up"]=$up2;
	            $link_temp["sort"]=1;
                ?>
	            <tr>
                  <td align=right id=mo><a id=mo href='<?=encode_link("ordenes_nueva.php",$link_temp)?>'>Renglón</a></td>
                  <?
                  $link_temp["sort"]=2;
                  ?>
  	             <td align=right id=mo><a id=mo href='<?=encode_link("ordenes_nueva.php",$link_temp)?>'></a>Cantidad</td>
                <?
	            $link_temp["sort"]=3;
                ?>
	            <td align=right id=mo><a id=mo href='<?=encode_link("ordenes_nueva.php",$link_temp)?>'></a>Titulo</td>
	            </tr>
                <?
	            while (!$rs1->EOF) {
		            $ref = encode_link("ordenes_nueva.php",Array("id_renglon"=>$rs1->fields["id_renglon"],"id_licitacion"=>$licitacion,"modo"=>"nuevo"));
		            tr_tag($ref,"title=\"$title\"");
                ?>
		            <td align=left style='font-size: 9pt;'>&nbsp;<?=$rs1->fields["codigo_renglon"]?></td>
		            <td align=left width=80 style='font-size: 9pt;'>&nbsp;<?=$rs1->fields["cantidad"]?></td>
		            <td align=left style='font-size: 9pt;'>&nbsp;<?=$rs1->fields["titulo"]?></td>
		            </tr>

                <?
                  $rs1->MoveNext();

	            }
                $link=encode_link("ordenes_nueva.php",array("modo"=>"asociar"));
                ?>


	            <tr>
                <td colspan=3 align=center>

                 <input type=button name=volver value='Volver' onclick='window.location="<?=$link?>"'>

	            </td>
                </tr>
                </table>
                </form>
<?
}
if ($modo=="Para Autorizar") {
    //cuando autoriza la orden de produccion
	$query=modificar($_POST);
	sql($query) or fin_pagina();
	$query="select id_entidad,fecha_entrega,nserie_desde,
                   nserie_hasta,estado,id_sistema_operativo
                   from orden_de_produccion where nro_orden=".$_POST["nro_orden"];
	$rs=sql($query) or fin_pagina();

	if (!$rs->fields["id_entidad"])
                       $error="Falta seleccionar el cliente.<br>";
	if (!$rs->fields["id_sistema_operativo"])
                       $error.="Falta seleccionar el sistema operativo.<br>";
	if (!$rs->fields["fecha_entrega"])
                      $error.="Falta la fecha de entrega.<br>";
	if ($rs->fields["estado"]!="P" && $rs->fields["estado"]!="R")
                       $error.="La orden debe estar Pendiente.<br>";

	$sql[]="UPDATE orden_de_produccion SET estado='PA' WHERE nro_orden=".$_POST["nro_orden"];
 	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES "
  		  ."(".$_POST["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Puesto Para Autorizar')";
	if (!$error) {
		$msg="Los datos se modificaron correctamente.";
		sql($sql) or fin_pagina();
	}
	else {
		$msg=$error;
	}
	$modo="modificar";
} // del if de para autorizar
if ($modo=="Terminar") {
    //cuando termina la licitacion
	$query="select id_entidad,fecha_entrega,nserie_desde,nserie_hasta,estado
                   from orden_de_produccion where nro_orden=".$parametros["nro_orden"];
	$rs=sql($query) or fin_pagina();
	if (!$rs->fields["id_entidad"])
                                $error="Falta seleccionar el cliente.<br>";
	if (!$rs->fields["fecha_entrega"])
                                 $error.="Falta la fecha de entrega.<br>";
	if ($rs->fields["estado"]!="E")
                                 $error.="La orden debe estar Enviada.<br>";
	if (!$rs->fields["nserie_desde"] || !$rs->fields["nserie_hasta"])
                                 $error.="Falta generar los Nro de Serie.<br>";
	$sql[]="UPDATE orden_de_produccion SET estado='T' WHERE nro_orden=".$parametros["nro_orden"];
	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES "
		  ."(".$parametros["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Terminada')";
	if (!$error) {
		$msg="Los datos se modificaron correctamente.";
		sql($sql) or fin_pagina();
	    }
	    else
		    $msg=$error;

	$modo="modificar";
}// del if de terminar
if ($modo=="Anular") {
    //cuando se anula la orden de produccion
	$query="select id_entidad,fecha_entrega,nserie_desde,nserie_hasta,estado from orden_de_produccion where nro_orden=".$parametros["nro_orden"];
	$rs=sql($query);
	if ($rs->fields["estado"]=="AN")
                             $error.="La orden no debe estar Anulada.<br>";

	$sql[]="UPDATE orden_de_produccion SET estado='AN' WHERE nro_orden=".$parametros["nro_orden"];
	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES "
  		  ."(".$parametros["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Anulado')";
	if (!$error) {
		$msg="Los datos se modificaron correctamente.";
		sql($sql) or fin_pagina();
	}
	else {
		$msg=$error;
	}
	$modo="modificar";
} //de anular
if ($modo=="rechazar") {
    //cuando se rechaza
	$query="select id_entidad,fecha_entrega,nserie_desde,nserie_hasta,estado from orden_de_produccion where nro_orden=".$_POST["nro_orden"];
	$rs=sql($query);
	if (!$rs->fields["id_entidad"]) $error="Falta seleccionar el cliente.<br>";
	if (!$rs->fields["fecha_entrega"]) $error.="Falta la fecha de entrega.<br>";

	if (!$rs->fields["nserie_desde"] || !$rs->fields["nserie_hasta"]) $error.="Falta generar los Nro de Serie.<br>";
	$sql[]="UPDATE orden_de_produccion SET estado='R',rechazada='".$_POST["rechazada"]."' WHERE nro_orden=".$_POST["nro_orden"];
	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES "
  		  ."(".$_POST["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Rechazado')";
	if (!$error) {
		$msg="Los datos se modificaron correctamente.";
		sql($sql) or fin_pagina();
	}
	else {
		$msg=$error;
	}
	$modo="modificar";
} // cuando se rechaza
if ($modo=="Autorizar") {
	$query=modificar($_POST);
	sql($query) or fin_pagina();
	$query="select id_entidad,desc_prod as tipo,cantidad as cant_prod,fecha_inicio,
                   fecha_entrega,id_ensamblador,nserie_desde,nserie_hasta,estado,
                   id_sistema_operativo
                   from orden_de_produccion where nro_orden=".$_POST["nro_orden"];
	$rs=sql($query) or fin_pagina();
	if (!$rs->fields["id_entidad"])
                              $error="Falta seleccionar el cliente.<br>";
	if (!$rs->fields["id_sistema_operativo"])
                              $error.="Falta seleccionar el sistema operativo.<br>";
	if (!$rs->fields["fecha_entrega"])
                              $error.="Falta la fecha de entrega.<br>";
	if (!$rs->fields["cant_prod"])
                              $error.="Falta ingresar la cantidad de equipos.<br>";
	if ($rs->fields["estado"]!="PA")
                              $error.="La orden debe estar para autorizar.<br>";
	if (!$rs->fields["id_ensamblador"])
                              $error.="Falta generar elegir el Ensamblador.<br>";
	elseif (!$rs->fields["nserie_desde"] && !$rs->fields["nserie_hasta"]) {
		gen_serial($rs->fields["id_ensamblador"]);
		$sql[]="UPDATE orden_de_produccion SET nserie_desde='$serialp',nserie_hasta='$serialu',estado='A' WHERE nro_orden=".$_POST["nro_orden"];
		$h=1;
		$resto_numero=substr($serialp,0,6);
		$letra1=substr($serialp,6,1);
		$letra2=substr($serialp,7,1);
		$letra3=substr($serialp,8,1);
		$nrot=substr($serialp,-3);
		$nro=intval($nrot);
		$letra=$letra1.$letra2.$letra3;
		while ($h<=$rs->fields["cant_prod"]) {
			$serial=$resto_numero.$letra.$nrot;
			$sql[]="INSERT INTO maquina (nro_serie,nro_orden) VALUES ('$serial',".$_POST["nro_orden"].")";
			$nro++;
			if ($nro==1000) {
				$nro=000;
				if (trim(chr(ord($letra3)+1)) > 'Z') {
					if (trim(chr(ord($letra2)+1)) > 'Z') {
						$letra1++;
						$letra=trim(chr(ord($letra1)));
						if ($letra1=='M') $letra2='B';
						else $letra2='A';
						$letra.=trim(chr(ord($letra2)));
					}
					else {
						$letra=trim(chr(ord($letra1)));
						$letra2++;
						if ($letra1=='E' && $letra2=='N') $letra2++;
						if ($letra1=='S' && $letra2=='E') $letra2++;
						if ($letra1=='O' && $letra2=='M') $letra2++;
						$letra.=trim(chr(ord($letra2)));
					}
					$letra3='A';
					$letra.=trim(chr(ord($letra3)));
				}
				else {
					$letra=trim(chr(ord($letra1)));
					$letra.=trim(chr(ord($letra2)));
					$letra3++;
					$letra.=trim(chr(ord($letra3)));
				}
			}
			else {
				$letra=trim(chr(ord($letra1)));
				$letra.=trim(chr(ord($letra2)));
				$letra.=trim(chr(ord($letra3)));
			}
			$nrot="";
			if ($nro<100) $nrot.="0";
			if ($nro<10) $nrot.="0";
			$nrot.= $nro;
			$h++;
		}
	}
	else
    $sql[]="UPDATE orden_de_produccion SET estado='A' WHERE nro_orden=".$_POST["nro_orden"];
	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES "
		."(".$_POST["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Autorizado')";
	if ($serialu) {
		$letra1=substr($serialu,strlen($serialu)-6,1);
		$letra2=substr($serialu,strlen($serialu)-5,1);
		$letra3=substr($serialu,strlen($serialu)-4,1);
		$numero=substr($serialu,strlen($serialu)-3,3);
		$sql[]="UPDATE serial SET nro=$numero,letra1='$letra1',letra2='$letra2',letra3='$letra3'";
	}
	//print_r($sql);
	if (!$error) {
		$msg="Los datos se modificaron correctamente.";
		sql($sql) or fin_pagina();
	}
	else $msg=$error;

	$nro_orden=$_POST["nro_orden"];
	//include("pdf.php");
	$modo="modificar";
} // del if de Autorizar


if ($modo=="Modificar") {
	//print_r($_POST);
	$sql=modificar($_POST);
	$sql[]="INSERT INTO log_ord_prod (nro_orden,fecha,id_usuario,descripcion) VALUES "
		."(".$_POST["nro_orden"].",'".date("Y-m-d H:i:s")."',".$_ses_user["id"].",'Modificado')";

	if ($error) $msg=$error;
	else {
		sql($sql) or fin_pagina();
		$msg="Los datos se modificaron correctamente.";
	}
	header("location: ".encode_link("ordenes_nueva.php",array("modo"=>"modificar","nro_orden"=>$_POST["nro_orden"],"volver"=>$_POST["volver"],"msg"=>$msg, "gag_cmd"=>$gag_modo, "gag_id_renglon"=>$gag_id_renglon)));
} //cuando se modifica

if ($modo=="borrar_archivo") {
	$id_archivo=$parametros["id_archivo"];
	$filename=$parametros["filename"];
	$db->beginTrans();
	$query="delete from archivos_ordprod where id_archivo=$id_archivo and nro_orden=".$parametros["nro_orden"];
	sql($query) or $error="Error al eliminar el Archivo $filename.";
	$query="delete from subir_archivos where id=$id_archivo";
	sql($query) or $error="Error al eliminar el Archivo $filename.";
	if (!$error) {
		if (unlink(UPLOADS_DIR."/archivos/$filename")) {
			$db->commitTrans();
			aviso("El archivo $filename se elimino correctamente.");
		}
		else {
			$db->Rollback();
			error("No se pudo eliminar el archivo $filename");
		}
	}
	else {
		$db->Rollback();
		error("No se pudo eliminar el archivo $filename");
	}
	$modo="modificar";
} // del if de borrar archivo

if ($modo=="nuevo" || $modo=="modificar") {



    ?>
	<script src='../../lib/NumberFormat150.js'></script>
	<script src='../../lib/checkform.js'></script>
	<script>

		var wcliente=0;
		var wproductos=0;
		var wproveedor=0;

        function confirmar()
        {
         if (parseInt(document.all.pasa_cantidad.value) > parseInt(document.all.cant_que.value))
           {
            if(confirm ("Esta seguro de pasar a Inspección? No cumple con la cantidad de verificaciones correctas requeridas"))return true;
             else return false;
            }
        }  // de la funcion confirmar




		function cargar_cliente()
		{
			document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
			document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
			if (wcliente.document.all.chk_direccion.checked)
  	 		            document.all.direccion.value=wcliente.document.all.direccion.value;
			document.all.lugar_entrega.value=wcliente.document.all.direccion.value;
		}
		function seleccionar() {
			if (document.all.id_ensamblador.value==0)
                              document.all.generar.disabled=1;
			                  else
                              document.all.generar.disabled=0;
		}
		function nuevo_item() {
			pagina_prod='<?=encode_link('../productos/listado_productos_especificos.php',array('onclick_cargar'=>"window.opener.cargar()",
                                                                                              'onclick_salir'=>'window.close()',
                                                                                              'pagina_viene' => 'ordenes_nueva.php',
                                                                                              'cambiar'=>1))?>';
			if (wproductos==0 || wproductos.closed)
				wproductos=window.open(pagina_prod);
		}
  		function cargar() {

			var items=eval("document.all.item");
			items.value++;

			var fila=document.all.productos.insertRow(document.all.productos.rows.length);
			fila.id='ma';
			fila.insertCell(0).innerHTML="<img src='../../imagenes/up.gif' style='cursor: hand;' id=imagen alt='Subir el producto' onclick='subir(this.parentNode.parentNode.rowIndex);'><input type=hidden name=orden_"+items.value+" id='orden' value="+items.value+"><input type=hidden name='id_"+items.value+"' value='"+wproductos.document.all.id_producto_seleccionado.value+"'><input type=checkbox name=chk value=1>";
			fila.insertCell(1).innerHTML="<input size=3 name=canti_"+items.value+" value=1>";
			fila.insertCell(2).innerHTML="<textarea rows=2 style='width:93%' name=desc_"+items.value+">"+wproductos.document.all.nombre_producto_elegido.value+"</textarea>";
  		    var text=new String(items.value);
			document.all.item.value=text;
			document.all.eliminar.disabled=0;
			document.all.guardar.disabled=0;

		}

		function borrar_items() {
			var i=0;
			var items=eval('document.all.item');
			while ((typeof(document.all.chk)!='undefined') && (typeof(document.all.chk.length)!='undefined') && (i<document.all.chk.length)) {
				if ((typeof(document.all.chk[i])!='undefined') && (document.all.chk[i].checked)) {
					document.all.productos.deleteRow(i+1);
					//items.value--;
				}
				else
					i++;
			}

			if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
			{
				document.all.productos.deleteRow(1);
				//items.value--;
				document.all.eliminar.disabled=1;
				document.all.modo.disabled=1;
			}
			else if (typeof(document.all.chk)=='undefined') {
				document.all.eliminar.disabled=1;
				document.all.modo1.disabled=1;
			}
			//var text=new String(items.value);
			//document.all.item.value=text;

		}


		function subir(rownum) {
			//alert(rownum);
			if (rownum>1) {
				document.all.orden[rownum-2].value=rownum
				document.all.orden[rownum-1].value=rownum-1
				var filas=document.all.productos.rows;
				var fila=filas[rownum];
				var filanueva=document.all.productos.insertRow(rownum-1);
				filanueva.id='ma';
				filanueva.insertCell(0).innerHTML=fila.cells(0).innerHTML;
				filanueva.insertCell(1).innerHTML=fila.cells(1).innerHTML;
				filanueva.insertCell(2).innerHTML=fila.cells(2).innerHTML;

				//filanueva.insertCell(3).innerHTML=fila.cells(3).innerHTML;
				//filanueva.insertCell(4).innerHTML=fila.cells(4).innerHTML;
				//filanueva.insertCell(5).innerHTML=fila.cells(5).innerHTML;
				document.all.productos.deleteRow(rownum+1);
				//alert(document.all.orden[rownum-2].value);
			}
		}


        function control_datos(){

        if (document.all.fechaentrega.value=="")
            {
            alert('Debe Ingresar una Fecha de Entrega');
            return false;
            }
        if (document.all.fechainicio.value=="")
            {
            alert('Debe Elegir una fecha de inicio');
            return false;
            }
        if (document.all.pasa_cliente.value=="")
            {
            alert('Debe elegir un Cliente');
            return false;
            }
        if (document.all.id_ensamblador.value==0 )
            {
            alert('Debe Seleccionar un ensamblador');
            return false;
            }

        return true;
        }
function alternar_color(obj,color) {
		color=color.toLowerCase();
		if (obj.style.backgroundColor == color)

			obj.style.backgroundColor = ""
		else
			obj.style.backgroundColor = color
	}
		</script>
    <?
    echo $html_header;

	$nro_orden     = $_POST["nro_orden"] or $nro_orden = $parametros["nro_orden"] or $nro_orden = $_GET["nro_orden"];
	$id_licitacion = $parametros["id_licitacion"];
	$id_renglon    = $parametros["id_renglon"];

    $id_entrega_estimada = $parametros["id_entrega_estimada"] or $id_entrega_estimada  = $_POST["id_entrega_estimada"];
 //   print_r($parametros);
	cargar_calendario();
	if ($modo=="modificar") {
		$sql="select titulo_etiqueta,descripcion_etiqueta,orden_de_produccion.id_entidad,orden_de_produccion.id_licitacion,orden_de_produccion.id_renglon,orden_de_produccion.id_ensamblador
			        ,orden_de_produccion.fecha_inicio,orden_de_produccion.fecha_entrega,orden_de_produccion.lugar_entrega
			        ,orden_de_produccion.nserie_desde,orden_de_produccion.nserie_hasta,orden_de_produccion.desc_prod as titulo,orden_de_produccion.cantidad
			        ,orden_de_produccion.comentario,orden_de_produccion.estado,adicionales,rechazada,id_sistema_operativo,clave_root
			        ,entidad.nombre,entidad.direccion,renglon.codigo_renglon from orden_de_produccion
			        left join entidad using(id_entidad)
			        left join renglon using(id_renglon) where nro_orden=$nro_orden";

		$licitacion=sql($sql) or fin_pagina();
		if ($licitacion->RecordCount()>0) $estfield="readonly";
		else $estfield="";
		if (!$gag_id_renglon) $gag_id_renglon=$licitacion->fields["id_renglon"];

	}
	elseif ($id_licitacion && $id_renglon) {
		$sql="select producto.id_producto,producto.tipo,producto.marca,producto.modelo,productos.desc_gral,
		           producto.precio_licitacion as preicio,
                    renglon.ganancia,renglon.titulo,renglon.cantidad,
                    entidad.nombre,entidad.direccion,entidad.id_entidad,renglon.codigo_renglon from producto
                    left join productos USING (id_producto)
                    left join renglon USING (id_renglon)
                    left join licitacion USING (id_licitacion)
                    left join entidad USING (id_entidad)
                    where id_renglon=$id_renglon";
		$licitacion=sql($sql) or fin_pagina();
		if ($licitacion->RecordCount()>0) $estfield="readonly";
		else $estfield="";
		if (!$gag_id_renglon) $gag_id_renglon=$id_renglon;
		//print_r($licitacion->fields);
	}

	$entidad=$_POST["id_entidad"] or $entidad=$licitacion->fields["id_entidad"];
	$id_sistema_operativo=$_POST["sist_instalado"] or $id_sistema_operativo=$licitacion->fields["id_sistema_operativo"];
    $clave_root=$_POST["clave_root"] or $clave_root=$licitacion->fields["clave_root"];

    $fechainicio = $_POST["fechainicio"] or $fechainicio=fecha($licitacion->fields["fecha_inicio"]) or $fechainicio=date("d/m/Y");
	$fechaentrega = $_POST["fechaentrega"] or $fechaentrega=fecha($licitacion->fields["fecha_entrega"]);
	$comentario = $_POST["comentario"] or $comentario=$licitacion->fields["comentario"];
	$cliente = $_POST["cliente"] or $cliente=$licitacion->fields["nombre"];
	$direccion = $_POST["direccion"] or $direccion=$licitacion->fields["direccion"];
	$lugar_entrega = $_POST["lugar_entrega"] or $lugar_entrega=$licitacion->fields["lugar_entrega"] or $lugar_entrega=$licitacion->fields["direccion"];
	$desc_prod = $_POST["desc_prod"] or $desc_prod=$licitacion->fields["titulo"];
	$cant_prod  =$_POST["cant_prod"] or $cant_prod=$licitacion->fields["cantidad"];
	$serialp = $_POST["serialp"] or $serialp=$licitacion->fields["nserie_desde"];
	$serialu = $_POST["serialu"] or $serialu=$licitacion->fields["nserie_hasta"];
	$adicionales = $_POST["adicionales"] or $adicionales=$licitacion->fields["adicionales"];



	$rechazada = $_POST["rechazada"] or $rechazada=$licitacion->fields["rechazada"];
	$id_licitacion = $parametros["id_licitacion"] or $id_licitacion=$licitacion->fields["id_licitacion"];
	$codigo_renglon = $parametros["codigo_renglon"] or $codigo_renglon=$licitacion->fields["codigo_renglon"];
   // if (!$id_renglon) $id_renglon=$parametros["id_renglon"] or $id_renglon=$licitacion->fields["id_renglon"];
	$estado = $parametros["estado"] or $estado=$licitacion->fields["estado"];
    $titulo_etiqueta = $parametros["titulo_etiqueta"] or $titulo_etiqueta=$licitacion->fields["titulo_etiqueta"];
    $titulo_renglon = $licitacion->fields["titulo"] or $titulo_renglon=$_POST["h_titulo_renglon"];
    $descripcion_etiqueta = $parametros["descripcion_etiqueta"] or $descripcion_etiqueta=$licitacion->fields["descripcion_etiqueta"];

    if ($modo=="nuevo") {
         $adicionales="USB Frontales Conectados";
         $clave_root="Administrador";
         }

	if (!$msg) $msg=$parametros["msg"] or $msg=$_POST["msg"];
	if ($nro_orden) {
		$rta_consulta=sql("select * from ordenes.log_op_bsas left join sistema.usuarios on(usuarios.login=log_op_bsas.usuario) where nro_orden=$nro_orden order by fecha desc", "c851")or fin_pagina();
		?>
			<table border="1" cellpadding="0" cellspacing="0" width="95%" align="center" bgcolor="<?=$bgcolor3?>">
				<tr>
					<td id="mo" align="center">Logs de producción Bs. As.</td>
				</tr>
			</table>
			<div style="overflow:auto; <?=(($rta_consulta->recordCount()>3)?" height:60;":"")?>">
				<table border="1" cellpadding="0" cellspacing="0" width="95%" align="center" bgcolor="<?=$bgcolor3?>">
				<?while (!$rta_consulta->EOF) {?>
					<tr>
						<td> <?echo $rta_consulta->fields["observaciones"]." (".Fecha($rta_consulta->fields["fecha"])." ".substr($rta_consulta->fields["fecha"], strpos($rta_consulta->fields["fecha"], " "), 6).")"?></td>
						<td> <?echo "Usuario: ".$rta_consulta->fields["apellido"].", ".$rta_consulta->fields["nombre"]?></td>
					</tr>
				<?
					$rta_consulta->moveNext();
				}?>
				</table>
			</div>
			<br>
		<?
		$sql="SELECT fecha,descripcion,nombre,apellido from log_ord_prod
			         left join usuarios using(id_usuario)
                     where nro_orden=$nro_orden order by fecha DESC";
		$log=sql($sql) or fin_pagina();
		echo "<div style='overflow:auto;";
		if ($log->RowCount() > 3) echo "height:60;";
		echo "'>\n";
		echo "<table width='95%' cellspacing=0 border=1 bordercolor=#E0E0E0 align='center' bgcolor=#cccccc>\n";
		while ($fila=$log->FetchRow()) {
			echo "<tr>";
			echo "<td height='20' nowrap>Fecha ".$fila["descripcion"]." ".date("j/m/Y H:i:s",strtotime($fila["fecha"]))."</td>\n";
			echo "<td nowrap > Usuario : ".$fila["nombre"]." ".$fila["apellido"]."</td>\n";
			echo "</tr>\n";
		}
		echo "</table></div>\n";
	}
    if ($msg) aviso($msg);
    $link=encode_link("etiquetas.php",array("nro_orden"=>$nro_orden));
    ?>
   <script>

      function control_etiquetas(i)
               {
               if ((document.all.texto_descripcion.value=='') || (document.all.texto_titulo.value==''))
                     {
                     alert('No puede generar las etiquetas sin el titulo o la descripción de los productos');
                     return false;
                     }
                     else
                     if (document.all.texto_titulo.value.length>48)
                     alert('No puede ingresar un titulo con mas de 48 caracteres');
                     else
                     {
                          window.location='<?=$link;?>&titulo_etiqueta='+document.all.texto_titulo.value+'&descripcion_etiqueta='+document.all.texto_descripcion.value+'&num='+i;
                     return true;
                     }
               }
               </script>
	<form name='frm_guardar' action='ordenes_nueva.php' method='POST'>
	<input type="hidden" name=nro_orden value='<?=$nro_orden?>'>
	<input type=hidden name=id_entidad value='<?=$entidad?>'>
	<input type=hidden name=volver value='<?=$volver?>'>
	<input type="hidden" name=id_renglon value='<?=$id_renglon?>'>
	<input type=hidden name=id_licitacion value='<?=$id_licitacion?>'>
	<input type="hidden" name="gag_id_renglon" value="<?=$gag_id_renglon?>">
	<input type="hidden" name="gag_cmd" value="<?=$gag_modo?>">
	<table width='95%' align='center' class=bordes>
	<tr id=mo>
        <td colspan=2>
        <?
	    if ($modo=="modificar") $tit="Modificar Orden de Producción Nro: $nro_orden";
	                       else $tit="Nueva Orden de Producción";
        ?>
	    <font size=3><?=$tit?></font>
	    </td>
    </tr>
    <?
	if ($rechazada) {
    ?>
		<tr bgcolor=$bgcolor_out>
                <td colspan=2>
		        <font size=2><font color=yellow>ADVERTENCIA:</font> La orden fue rechazada.<br>
		        <br>Motivo del rechazo: <b><?=$rechazada?></b></font>
		        </td>
        </tr>
    <?
	}
    ?>
	<tr >
            <td>
            <?
            if($id_licitacion){
            $consult="select  lider,u1.apellido||', '||u1.nombre as lider_nombre from licitaciones.licitacion l
	                      left join sistema.usuarios u1 on (lider=u1.id_usuario)
                          where id_licitacion=$id_licitacion";
            $ejecuta=sql($consult,"no se pudo recuperar el lider") or fin_pagina();
            }
	        $link2=encode_link('../licitaciones/licitaciones_view.php',array("ID"=>$id_licitacion,"cmd1"=>"detalle"));
	        $est=array(
		        "A"=>"Autorizada",
		        "AN"=>"Anulada",
		        "T"=>"Terminada",
		        "PA"=>"Para Autorizar",
		        "P"=>"Pendiente",
		        "R"=>"Rechazada",
		        "E"=>"Enviada"
	        );
            ?>
	        <a target='_blank' href='<?=$link2?>'><font size=3><b><u>Asociada a la Licitación ID: <?=$id_licitacion?></u></b></font></a><br>
            <input name="pasa_id" type="hidden" value="<?=$id_licitacion?>">
            <script>var warchivos=0;</script>
            <font size=3><b>Asociada al Renglon: <?=$codigo_renglon?></b></font>
            </td>
            <td>
	        <font size=3><b>Estado: <font size=3 color='red'><?=$est[$estado]?></font></b></font>
	        </td>

    </tr>
    <tr>
    <td>
	<b>Lider: <?=$ejecuta->fields['lider_nombre']?></b>
	</td>
    </tr>
	<tr>
            <td>
             <b>Fecha Inicio: </b>
             <input type=text size=10 name='fechainicio' value='<?=$fechainicio?>'> <?=link_calendario("fechainicio")?>
	        </td>
            <td>
	        <b>Fecha Entrega: </b>
            <input type=text size=10 name='fechaentrega' value='<?=$fechaentrega?>'>
	        <input name="pasa_fecha" type="hidden" value="<?=$fechaentrega?>">
	        <?=link_calendario("fechaentrega")?>
	        </td>
    </tr>
	<tr id=mo>
        <td colspan=2 align=left>
           <font size=2> Datos del Cliente</font>
        </td>
    </tr>
    <tr>
    <td colspan=2>

	        <table width="100%">
	            <tr>
                    <td colspan=2>
                    <?
	                $link=encode_link('../ord_compra/elegir_cliente.php',array("onclickaceptar"=>"window.opener.cargar_cliente();window.close()","onclicksalir"=>"window.close()"))
                    ?>
	                <a <?=$disa?> title='Haga click para ver elegir/editar el cliente' style='cursor: hand;'

	                onclick="if (wcliente==0 || wcliente.closed) wcliente=window.open('<?=$link?>');
	                             else if (!wcliente.closed) wcliente.focus()">
                    <b><u>Cliente</u></b> (<---Haga click en la palabra para editar/elegir el cliente)
                    </a>
	                </td>
                </tr>
	            <tr >
                    <td>
	                <b>Nombre: </b>
                    <input type=text disabled size=40 name='cliente' value='<?=$cliente?>'>
	                <input name="pasa_cliente" type="hidden" value="<?=$cliente?>">
                    </td>
                    <td>
                    <b>Dirección: </b>
                    <input type=text disabled  size=40 name='direccion' value='<?=$direccion?>'>
                    </td>
                </tr>
                <tr >
                  <td>
                  <font size=2><b>Lugar de entrega: </b></font><br>
                  <textarea name='lugar_entrega' cols=55 rows=5><?=$lugar_entrega?></textarea>
                  </td>
                  <td>
                  <font size=2><b>Comentario: </b></font><br>
                  <textarea name='comentario' cols=55 rows=5><?=$comentario?></textarea>
                  </td>
                </tr>
            </table>

           </td>
    </tr>
    <tr id=mo>
        <td colspan=2>
        <font size=2>Ensamblador</font>
        </td>
    </tr>

    <tr>
     <td colspan=2>


            <table width=100%>
            <tr >
            <td>
                <?
	            if ($estado!="P" && $estado!="PA" && $estado!="") $disabl="disabled";
                ?>
	            <b>Seleccionar Ensamblador:</b>
            </td>
            <td>
                <select name='id_ensamblador' <?=$disabl?>>
	            <option value=0>---Seleccionar Ensamblador---</option>
                <?
	            $sql="select id_ensamblador,nombre from ensamblador";
	            $rs=sql($sql) or fin_pagina();
	            $id_ensamblador=$_POST["id_ensamblador"] or $id_ensamblador=$licitacion->fields["id_ensamblador"];
                if ($modo=="nuevo") $id_ensamblador=4;
	            while ($fila=$rs->FetchRow()) {
		            echo "<option value='".$fila["id_ensamblador"]."'";
		            if ($fila["id_ensamblador"]==$id_ensamblador) echo " selected";
		            echo ">".$fila["nombre"]."</option>\n";
	            }
                ?>
                </select>
                </td>
            </tr>
            <tr >
                <td>
                <b>Numeros de Serie: </b>
                </td>
                <td>
                <font size=3 color=red><b><?=$serialp?> ... <?=$serialu?></b></font>
                <?
	            $link=encode_link("datos_etiquetas.php",array("titulo_renglon"=>$titulo_renglon));
                ?>
                </td>
            </tr>
            <tr >
    	        <input name="pasa_titulo" type="hidden" value="<?=$titulo_etiqueta?>">
    	        <input name="h_titulo_renglon" type="hidden" value="<?=$titulo_renglon?>">
    	        <td>
    		       <b> Título Etiqueta: </b>
                </td>
                <td>
    		        <input type="button" name="boton_titulo" value="E" onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=125,top=10,width=500,height=400');">
    		        <input type="text" name="texto_titulo" value="<?=$titulo_etiqueta?>" readonly size="80">
    	        </td>
            </tr>
            <tr >
               <input name="pasa_descripcion" type="hidden" value="<?=$descripcion_etiqueta?>">
    	        <td>
    		        <b>Descripción Etiqueta: </b>
                 </td>
                 <td>
    		        <input type="button" name="boton_titulo" value="E" onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=500,height=400');">
    		        <textarea name="texto_descripcion" cols="80" rows="2" readonly><?=$descripcion_etiqueta?></textarea>
    	        </td>
            </tr>
            <?

            if ($estado && ($estado!='P' && $estado!="PA" && $estado!="R"))
            {
            	$et_x_boton=306;  //numero de etiquetas por boton  306 si se cambia esta constante cambiarla en la pagina etiquetas.php
               	$cant_boton=ceil($cant_prod/$et_x_boton);

               	?>
               <tr>
               	<td colspan="2">
               	<?
                for ($i=0;$i<$cant_boton;$i++) {
                ?>
                  <input type="button" name="etiqueta_<?=$i?>" value="Etiquetas <?=$i+1?>/<?=$cant_boton?>" onclick=" return control_etiquetas(<?=$i?>);" title='Generar Etiquetas'>
                <?

                } ?>

                 </td>
               </tr>
            <?
            }
            ?>

    </table>
    </td>
    </tr>

    <tr id=mo>
        <td colspan=2>
        <font size=2>Productos</font>
        </td>
    </tr>
    <tr >
         <td colspan=2>
         <table width=100%>
            <tr>
            <td valign=top width=5%><b>Producto:</b></td>
            <td width=50%>
             <textarea size=40 name='desc_prod' rows=3 style="width:95%"><?=$desc_prod?></textarea>
            </td>
            </tr>
            <tr>
            <td valign=top>
            <?
            if ($rechazada) $readonly=" readonly";
            ?>
            <b>Cantidad: </b>
            </td>
            <td valign=top>
            <input type=text size=10 name='cant_prod' value='<?=$cant_prod?>' <?=$readonly?>>
	        <input name="pasa_cantidad" type="hidden" value="<?=$cant_prod?>"> <?//cant_prod tiene el total de maquinas?>
            </td>
            </tr>

           </table>
            </td>
    </tr>
      <?
      $sql_sist_op = "select id_sistema_operativo,descripcion
                       from sistema_operativo
                       where activo=1 order by descripcion";
      $result_sist_op = sql($sql_sist_op) or fin_pagina();
      ?>
    <tr id=mo>
       <td colspan=2><font size=2>Sistema Operativo</font></td>
    </tr>
    <tr >
       <td colspan=2>

          <table width=100%>
          <tr>
          <td width=15%>

            <b>Sistema operativo: </b>
         </td>
         <td>
            <select name='sist_instalado'>
            <option value=''></option>
            <?
	        while (! $result_sist_op->EOF) {
		        echo "<option value='".$result_sist_op->fields["id_sistema_operativo"]."'";
		        if ($id_sistema_operativo == $result_sist_op->fields["id_sistema_operativo"]) {
			        echo " selected";
		        }
		        echo ">".$result_sist_op->fields["descripcion"]."</option>";
		        $result_sist_op->MoveNext();
	        }
            ?>
            </select>

       </td>
       </tr>
       <tr>
       <td>
        <b> Clave de Root: </b>
        </td>
        <td>
         <input type=text name="clave_root" value="<?=$clave_root?>">
         </td>
         </tr>
      </table>
       </td>
    </tr>
    <tr id=mo>
        <td colspan=2>
        <font size=2>Descripción de los Productos</font>
        </td>
    </tr>
    <tr>
    <td colspan=2 nowrap>
    <!--
    <div id='div_com2'  style='border-width: 0;overflow: hidden;height: 1'>
    -->
	<table width="100%" border="0">
        <tr bgcolor=<?=$bgcolor_out?>>
              <td colspan=2>
              	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="width=<?=(($gag_modo=="ap" || $gag_modo=="apa")?"50%":"100%")?>">
                <table width=100% id=productos cellspacing=2 cellpadding=2 border=0>
                <tr id=mo>
                    <td width=10%>&nbsp;</td>
                    <td width=5% width=center>Cantidad</td>
                    <td >Producto</td>
               </tr>
    <?

	$i=0;


    if ($id_renglon and !$_POST["item"]) {

		$consulta2="select plm.id_prod_esp,
                           producto_especifico.descripcion as desc_gral,
                           plm.cantidad, producto_especifico.id_tipo_prod, tipos_prod.codigo
			    from licitaciones.producto
				 join mov_material.producto_lista_material plm on (plm.id_renglon=$id_renglon and producto.id_producto=plm.id_producto and id_entrega_estimada=$id_entrega_estimada)
				 join general.producto_especifico using (id_prod_esp)
				 join general.tipos_prod using (id_tipo_prod)
			where producto.id_renglon=$id_renglon";
		$renglon=sql($consulta2, "c74") or fin_pagina();
//       print_r($resultado);
    }

	if ($nro_orden and !$_POST["item"]) {

		$sql="select id_fila,id_prod_esp,productos.descripcion as producto,
                     filas_ord_prod.cantidad,filas_ord_prod.descripcion as desc_gral, productos.id_tipo_prod, tipos_prod.codigo
                     from filas_ord_prod
	   		          join producto_especifico productos using (id_prod_esp)
	   		          join general.tipos_prod using(id_tipo_prod)
			         where nro_orden=$nro_orden order by filas_ord_prod.orden";

		$renglon=sql($sql) or fin_pagina();

	}
	$gag_flag_gtia=0;
	while (!$_POST["item"] and $renglon and $fila=$renglon->FetchRow()) {
		$i++;

		$desc=$_POST["desc_$i"] or $desc=$fila["desc_gral"] or $desc=$fila["descrip"];
		$original=$_POST["desc_orig_$i"] or $original=$fila["producto"] or $original=$fila["desc_gral"] or $original=$fila["descrip"];
		//if ($fila["producto"]) $h_desc=substr($fila["desc_gral"],strlen($original)+1);
		if ($original) $h_desc=substr($desc,strlen($original)+1);
		else $h_desc=$desc;
		$canti=$_POST["canti_$i"] or $canti=$fila["cantidad"];
		$gag_flag_gtia= $gag_flag_gtia || ($fila["codigo"]=="garantia");
        ?>


		<tr id=ma>
		<td nowrap>

           <img src='../../imagenes/up.gif' style='cursor: hand;' id=imagen alt='Subir el producto' onclick='subir(this.parentNode.parentNode.rowIndex);'>
           <input name='chk' type='checkbox' id='chk' value='1'>
        </td>
		<td>
        <input type=hidden name=id_fila_<?=$i?> value='<?=$fila["id_fila"]?>'>
 		<input type=hidden name=orden_<?=$i?> id=orden value='<?=$i-1?>'>
		<input type=hidden name=id_<?=$i?> value='<?=$fila["id_prod_esp"]?>'>

           <input size=3 name='canti_<?=$i?>' value='<?=$canti?>'>
        </td>
		<td>
         <textarea readonly rows=1 style="width:90%" name='desc_<?=$i?>'><?=$desc?></textarea>
		 <input type=button name=descripcion value='E' onclick="window.open('../ord_compra/desc_adicional.php?posicion=<?=$i?>');">
		 <input type=hidden name='desc_orig_<?=$i?>' value='<?=$original?>'>
		 <input type=hidden name='h_desc_<?=$i?>' value='<?=$h_desc?>'>
       </td>
     </tr>
    <?
    } // del while
	if ($_POST["item"]) {
		while ($i<$_POST["item"]) {
			$i++;
			if ($_POST["id_$i"]) {
		        $desc=$_POST["desc_$i"];
				$canti=$_POST["canti_$i"];
				$original=$_POST["desc_orig_$i"];
				$h_desc=$_POST["h_desc_$i"];
		        ?>
				<tr id=ma>
				<input type=hidden name=id_fila_<?=$i?> value='<?=$_POST["id_fila_$i"]?>'>
				    <input type=hidden name=orden_<?=$i?> id=orden value='<?=$i-1?>'>
				    <input type=hidden name=id_<?=$i?> value='<?=$_POST["id_$i"]?>'>
				<td align=right nowrap>
                    <img src='../../imagenes/up.gif' alt='Subir el producto' style='cursor: hand;' onclick='subir(this.parentNode.parentNode.rowIndex);'>
                    <input name='chk' type='checkbox' id='chk' value='1'>
                </td>
				<td><input size=5 name='canti_<?=$i?>' value='<?=$canti?>'></td>
				<td>
                    <textarea readonly rows=1  style="width:90%" name='desc_<?=$i?>'><?=$desc?></textarea>
				    <input type=hidden name='desc_orig_<?=$i?>' value='$original'>
                    <input type=hidden name='h_desc_<?=$i?>' value='<?=$h_desc?>'>
                </td>

				</tr>
                <?
			} //del if
		} //del while
	} //del if

    ?>
    <input type=hidden name='item' value=<?=$i?>>
    </table>

 	<?////////////////////////////////// GABRIEL //////////////////////////////////////////////

	if (($gag_modo=="ap" || $gag_modo=="apa") && $gag_id_renglon){

		$consulta="select productos.desc_gral, p.cantidad
              from licitaciones.producto p
              join  general.productos using(id_producto)
              where p.id_renglon = $gag_id_renglon order by desc_gral asc";
		$rta_consulta=sql($consulta, "c1547 - ordprod/ordenes_nueva") or fin_pagina();
		
		$consulta="select cantidad
              from licitaciones.renglon              
              where id_renglon = $gag_id_renglon";
		$result=sql($consulta, "c1547 - ordprod/ordenes_nueva") or fin_pagina();
    ?>

    </td><td width="50%">
   <table border="0" cellpadding="2" cellspacing="2" width="100%" style="cursor:hand">
   			<tr>
   				<td colspan="2" align="center">
   					<font size="+1"><b>Cantidad del Renglon: <font color="Red"><?=$result->fields['cantidad']?></font></font></b>
   				</td>
   			</tr>
			<tr id=mo>
      	<td width=5% width=center>Cantidad</td><td>Producto</td>
			</tr>
			<?while(!$rta_consulta->EOF){?>
			<tr bgcolor="<?=$bgcolor_out?>" onclick="alternar_color(this,'#a6c2fc')">
				<td><?=$rta_consulta->fields["cantidad"]?></td>
				<td><?=$rta_consulta->fields["desc_gral"]?></td>
			</tr>
			<?$rta_consulta->moveNext();}?>
    </table>
    </td></tr></table>
<?
	}///////////////////////////////////////////////////////////////////////////////////////////
?>

    </td>
    </tr>
<?
	if (!$gag_flag_gtia){
?>
		<tr>
			<td colspan="2" align="center">
				<font color="Red" size="5">
					NO SE HA ENCONTRADO LA GARANTÍA EN LA LISTA DE PRODUCTOS
				</font>
			</td>
		</tr>
<?
	}
?>
    <tr>
    <?
    if ($i==0) $disabled_eliminar="disabled ";
    ?>
    <td align=center colspan=2>
    <input type=button name=eliminar <?=$disabled?>  value='Eliminar' onclick='borrar_items();'>
    <input type=button name=agregar value='Agregar' onclick='nuevo_item();'>
    </td>
    </tr>
    <tr id=mo>
    <td colspan=2>
    <font size=2>KIT</font>
    </td></tr>
    <tr><td align=center colspan=2>
    <table width=60% cellspacing=2 cellpadding=2 border=0>
    <tr id=mo>
    <td>Accesorios</td>
    <td>Modelo</td>
    <td width=50%>Observaciones</td>
    </tr>
    <?
	if ($nro_orden) {
		$sql="select id_accesorio,descripcion,esp1,tipo from accesorios
              where nro_orden=$nro_orden order by tipo";
		$acc=sql($sql) or fin_pagina();
		$acc->MoveFirst();
    ?>
		<tr id='ma'>
		<td><b>Teclado</b></td>
		<td>
		<select name='esp1_0' >
            <?if ($acc->fields["esp1"]=="DIN")
                                    $selected_din=" selected";?>
            <option <?=$selected_din?>>DIN</option>
            <?if (!$acc->fields["esp1"] || $acc->fields["esp1"]=="MINI DIN")
                                   $selected_mini_din=" selected";?>
            <option <?=$selected_mini_din?>>MINI DIN</option>
            <?if ($acc->fields["esp1"]=="Ninguno")
                           $selected_ninguno="selected";?>
            <option <?=$selected_ninguno?>>Ninguno</option>
            <?
            if ($acc->fields["esp1"]!="MINI DIN" && $acc->fields["esp1"]!="DIN" && $acc->fields["esp1"]!="Ninguno")
             {
            ?>
		    <option selected><?=$acc->fields["esp1"]?></option>
            <?
             }
            ?>
		    <option id='editable'>Edite aqui</option>
        </select>
		</td>
		<td>
        <input type='text' name='observ_0' value='<?=$acc->fields["descripcion"]?>' size='33'>
		</td>
		</tr>
        <?
		$acc->MoveNext();
        ?>
		<tr id='ma'>
		<td><b>Mouse </b></td>
		<td>
        <select name='esp1_1' >
          <option selected>PS/2</option>
          <?
          if ($acc->fields["esp1"]=="SERIAL") $selected_serial=" selected";
          ?>
          <option <?=$selected_serial?>>SERIAL</option>
          <?
          if ($acc->fields["esp1"]=="Ninguno") $selected_ninguno=" selected";
                                        else   $selected_ninguno="";
          ?>
	      <option <?=$selected_ninguno?>>Ninguno</option>
          <?
		  if ($acc->fields["esp1"]!="PS/2" && $acc->fields["esp1"]!="SERIAL" && $acc->fields["esp1"]!="Ninguno")
          {
          ?>
 		  <option selected><?=$acc->fields["esp1"]?></option>
          <?
          }
          ?>

        </select>
		</td>
		<td>
        <input type='text' name='observ_1' value='<?=$acc->fields["descripcion"]?>' size='33'>
		</td>
		</tr>
        <?
		$acc->MoveNext();

        ?>
		<tr id='ma'>
		<td><b>Parlantes </b></td>
		<td>
        <select name='esp1_2' >
        <?
        if ($acc->fields["esp1"]=="220") $selected_220=" selected";
        ?>
         <option <?=$selected_220?>>220</option>
         <?
         if ($acc->fields["esp1"]=="Interno") $selected_interno=" selected";
         ?>
         <option <?=$selected_interno?>>Interno</option>
         <?
         if ($acc->fields["esp1"]=="Ninguno") $selected_ninguno=" selected";
                                        else  $selected_ninguno="";
         ?>
	     <option <?=$selected_ninguno?>>Ninguno</option>
         <?
         if ($acc->fields["esp1"]=="USB") $selected_usb=" selected";
                                        else  $selected_usb="";
         ?>
	     <option <?=$selected_usb?>>USB</option>

         <?
		 if ($acc->fields["esp1"] && $acc->fields["esp1"]!="220" && $acc->fields["esp1"]!="Interno" && $acc->fields["esp1"]!="Ninguno")
         {
         ?>
		 <option selected><?=$acc->fields["esp1"]?></option>
         <?
         }
         ?>
        </select>
		</td>
		<td>
        <input type='text' name='observ_2' size='33' value='<?=$acc->fields["descripcion"]?>'>
		</td>
		</tr>
		<?
		$acc->MoveNext();
        ?>
		<tr id='ma'>
		<td><b>Micrófono</b></td>
		<td>
        <?
        if ($acc->fields["esp1"]=="on") $checked_microfono=" checked";
        ?>
        <input type='checkbox' name='lleva_microfono' <?=$checked_microfono?>>
		</td>
		<td>
        <input type='text' name='observ_3' size='33' value='<?=$acc->fields["descripcion"]?>'>
		</td>
		</tr>
        <?
		$acc->MoveNext();
        ?>
		<tr id='ma'>
		<td height='23'><b>Floppy</b></td>
		<td>
        <?
        if (!$acc->fields["esp1"] || $acc->fields["esp1"]=="on") $checked_floppy=" checked";
        ?>
        <input type='checkbox' name='lleva_floppy' <?=$checked_floppy?>>
		</td>
		<td valign='top'>&nbsp;</td>
		</tr>
		<!-- Etiquetas De windos vista y xp-->
		<?
		$acc->MoveNext();
		?>
		<tr id='ma'>
		<td><b>Etiquetas Windows </b></td>
		<td>
        <select name='etiquetas_windows'>
        <?
        if ($acc->fields["esp1"]=="Windows XP") $windows_xp=" selected";
        ?>
         <option <?=$windows_xp?>>Windows XP</option>
         <?
         if ($acc->fields["esp1"]=="Windows XP/Vista Capable") $windows_vista=" selected";
         ?>
         <option <?=$windows_vista?>>Windows XP/Vista Capable</option>
         <?
         if ($acc->fields["esp1"]=="No lleva etiquetas") $no_lleva_etiquetas=" selected";
         ?>
        <option <?=$no_lleva_etiquetas?>>No lleva etiquetas</option>
		 
        </select>
		</td>
		<td>
        <input type='text' name='observacion_etiquetas' size='33' value='<?=$acc->fields["descripcion"]?>'>
		</td>
		</tr>
		
    <?
	}
	else {
    ?>
		<tr>
        <td><b>Teclado</b></td>
        <td>
            <select name="esp1_0">
              <option>DIN</option>
              <option selected>MINI DIN</option>
              <option>Ninguno</option>
            </select>
         </td>
         <td>
           <input type="text" name="observ_0" size="33" value="CDR">
         </td>
        </tr>
       <tr>
           <td><b>Mouse</b></td>
           <td>
                <select name="esp1_1">
                  <option selected>PS/2</option>
                  <option>SERIAL</option>
		          <option>Ninguno</option>
                </select>
           </td>
           <td>
           <input type="text" name="observ_1" size="33" value="CDR">
           </td>
       </tr>
       <tr>
           <td><b>Parlantes</b></td>
           <td>
            <select name="esp1_2">
              <option>220</option>
              <option>Interno</option>
              <option selected>USB</option>
		      <option>Ninguno</option>
            </select>
          </td>
          <td>
            <input type="text" name="observ_2" size="33" value="CDR">
          </td>
       </tr>
        <tr>
          <td><b>Micrófono</b></td>
          <td>
            <input type="checkbox" name="lleva_microfono" >
          </td>
          <td>
            <input type="text" name="observ_3" size="33" value="">
          </td>
        </tr>
        <tr>
          <td height="23"><b>Floppy</b></td>
          <td>
            <input type="checkbox" name="lleva_floppy" checked>
          </td>
          <td valign="top">&nbsp;</td>
        </tr>
		<tr>
		<td><b>Etiquetas Windows</b></td>
		<td>
        <select name='etiquetas_windows' >
         <option >Windows XP</option>
         <option >Windows XP/Vista Capable</option>
		 <option selected>No lleva etiquetas</option>
        </select>
		</td>
		<td>
        <input type='text' name='observacion_etiquetas' size='33' value='<?=$acc->fields["observacion_etiquetas"]?>'>
		</td>
		</tr>
		
    <?
	}

    ?>
    </table>
    <input type=hidden name=modo value=''>
    </td>
    </tr>
    <tr>
        <td colspan=2 align=center>
        <font size=3><b>Adicionales</b></font><br>
        <textarea name=adicionales cols=80 rows=5><?=$adicionales?></textarea>
        </td>
    </tr>
<?

if ($modo=="modificar") {
// Si es una nueva orden no tiene numero de orden
// Entonces no se muestra la parte de archivos

	$q = "SELECT subir_archivos.*,usuarios.nombre ||' '|| usuarios.apellido as nbre_completo
            FROM subir_archivos
            join usuarios on subir_archivos.creadopor=usuarios.login
            join archivos_ordprod on id_archivo=subir_archivos.id
            where nro_orden=$nro_orden";
    $rs=sql($q) or fin_pagina();
	?>
	<tr>
    <td colspan=2 align=center>
    <table width=99% align=center>
        <tr> <td id="mo" ><font size=3> Archivos </font></td> </tr>
        <tr>
            <td align=right>
            <table width="100%">
                        <tr>
                        <td align="left">
                        <b><?=$msg ?></b>
                        </td>
                        <td align="right">
                        <input type="button" name="bagregar" value="Agregar Archivo" style="width:105" onclick="if (typeof(warchivos)=='object' && warchivos.closed || warchivos==false) warchivos=window.open('<?= encode_link($html_root.'/modulos/archivos/archivos_subir.php',array("onclickaceptar"=>"window.opener.location.reload();","nro_orden"=>$nro_orden,"proc_file"=>"../ordprod/orden_file_proc.php")) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1'); else warchivos.focus()">
                        </td>
                        </tr>
            </table>
            </td>
        </tr>
       <tr>
       <td>
       <table width='100%'>
       <tr>
       <td colspan=7  id=ma style="text-align:left">
       <b>Total:</b><?=$total_archivos=$rs->recordcount() ?>
       </td>
       <tr >
           <td align=right id=mo>Archivo</td>
           <td align=right id=mo>Fecha</td>
            <td align=right id=mo>Subido por</td>
            <td align=right id=mo>Tamaño</td>
            <td align=center id=mo>&nbsp;</td>
        </tr>
  <?
  while (!$rs->EOF) {
  ?>
       <tr <?=atrib_tr()?> > <!-- bgcolor='#f0f0f0' -->
       <td align=center>
      <?
       if (is_file("../../uploads/archivos/".$rs->fields["nombre"]))
             echo "<a target=_blank href='".encode_link("../archivos/archivos_lista.php",array ("file" =>$rs->fields["nombre"],"size" => $rs->fields["size"],"cmd" => "download"))."'>";
       echo $rs->fields["nombre"]."</a></td>\n";
    ?>
      <td align=center>&nbsp;<?= Fecha($rs->fields["fecha"]) ?></td>
      <td align=center>&nbsp;<?= $rs->fields["nbre_completo"] ?></td>
      <td align=center>&nbsp;<?= $size=number_format($rs->fields["size"] / 1024); ?> Kb</td>
      <td align=center>
     <?
	$lnk=encode_link("$_SERVER[PHP_SELF]",Array("nro_orden"=>$nro_orden,"id_archivo"=>$rs->fields["id"],"filename"=>$rs->fields["nombre"],"modo"=>"borrar_archivo"));
    echo "<a href='$lnk'><img src='../../imagenes/close1.gif' border=0 alt='Eliminar el archivo: \"". $rs->fields["nombre"] ."\"'></a>";
    ?>
    </td>
    </tr>
<?
    $rs->MoveNext();
}
?>
    </table>
    </td>
    </tr>
	</table>
<!--	</div> -->
    </td>
    </tr>
<?
}

?>
</table>
</td>
</tr>
<?
if ($volver=="seguimiento_produccion_bsas")
{
?>
<tr><td colspan=2 align=center>

	<br>
	<table width='95%' align="center"  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
		<tr id=mo>
			<td colspan=2>
				<font size=2>Auditor&iacute;as</font>
			</td>
			<input type="hidden" name="orden" value="<?=$nro_orden?>">
		</tr>
		<?
		//////////////////////////////////////////////
		if ($nro_orden){
			$resultf=sql("select  max (fecha_hora) as fecha_hora,nro_orden from ordenes.orden_de_produccion
			left join  ordenes.auditorias using(nro_orden)
			 where nro_orden=$nro_orden group by nro_orden ") or fin_pagina();
			$fec_hor=$resultf->fields["fecha_hora"];
		    if($fec_hor!="")
		    {
			$resul=sql("select * from ordenes.orden_de_produccion
			left join  ordenes.auditorias using(nro_orden)
			where nro_orden=$nro_orden and fecha_hora='$fec_hor'") or fin_pagina();
			if ($resul->fields["estado_audit"]=="t") $auditoria="aprobada";
			else $auditoria="reprobada";

			$comentarios=$resul->fields["accion"];
			}
		}
		   ?>
  	       <tr><td colspan=2>Acción de reproceso: <textarea rows="3" cols="80" name="accion_reproceso"><?=$comentarios?>
		   </textarea></td></tr>
</table>
</td></tr>
<?
}
?>
<tr><td colspan=2 align=center>
<?
if ($volver=="seguimiento_produccion_bsas")
	   {  $sql_tanda="select prod_bsas_por_tanda.cantidad_por_tanda,prod_bsas_por_tanda.estado_bsas_por_tanda
          from ordenes.prod_bsas_por_tanda where nro_orden=$nro_orden
	      order by prod_bsas_por_tanda.orden_tanda";

	      $res_tanda=sql($sql_tanda,"$sql_tanda") or fin_pagina();
          if ($res_tanda->recordCount()>0) { ?>
          <table align="center" width="90%" class="bordes">
	 <tr id=ma_sf>
	  <td align=center>
	<?
	echo "<img src='../../imagenes/mas.gif' border=0 style='cursor: hand;'
	     onClick='if (this.src.indexOf(\"mas.gif\")!=-1) {
		this.src=\"../../imagenes/menos.gif\";
		div_ing_eg.style.overflow=\"visible\";
		} else {
		this.src=\"../../imagenes/mas.gif\";
		div_ing_eg.style.overflow=\"hidden\";
		}'>\n";
	echo "&nbsp;<b><font size='1+'>Detalles Cambios de Estados</font></b>\n";?>
	  </td>
	 </tr>
	</table>
	<?
	echo "<div id='div_ing_eg' style='border-width: 0;overflow: hidden;height: 1'>\n"; ?>
          <br>
              <table width='25%' align="center"  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
	            <tr id=mo>
	               <td>Cantidad Máquinas</td>
	               <td>Estado Bs As</td>
	            </tr>
          <?
          $descontar=0;
           while (!$res_tanda->EOF) {
	         switch ($res_tanda->fields['estado_bsas_por_tanda']) {
	              case 0:$nombre="pendientes";break;
	              case 1:$nombre="produccion";break;
	              case 2:$nombre="historial";break;
	              case 3:$nombre="embalaje";break;
	              case 4:$nombre="calidad";break;
	              case 5:$nombre="inspeccion";break;
	            }
	            if ($nombre==$parametros['cmd']) {
	            	$cant_por_tanda=$res_tanda->fields['cantidad_por_tanda'];
	            	$color="bgcolor=#5F9F9F";
	            }
	            else $color="";
	            if ($parametros['cmd']=='produccion' && $nombre=='inspeccion') {
	               if ($res_tanda->fields['cantidad_por_tanda'])
	                 $descontar=$res_tanda->fields['cantidad_por_tanda'];
	             }
           	?>
	          <tr <?=$color?>>
	            <td ><?=$res_tanda->fields['cantidad_por_tanda']?></td>
	            <td><?=strtoupper($nombre)?></td>
	          </tr>
	        <?
	       $res_tanda->MoveNext();
	      }
	      ?>
	      </table>
	      </div>
	      <br>
	      <?
          }
          if ($cant_por_tanda=="")
          	 $cant_por_tanda=$cant_prod; //es el total de las maquinas


	   ?>
	    <input type="hidden" name="cmd" value="<?=$parametros['cmd']?>">
	    <input type="hidden" name="nro_orden" value="<?=$parametros['nro_orden']?>">
	    <input type="hidden" name="cant_por_tanda" value="<?=$cant_por_tanda;?>">
	    <input type="hidden" name="por_tanda" value="<?=$por_tanda;?>">
	    <?
          if ($parametros['cmd']=="pendientes") {
          	    $val="Pasar a Producción";
                $val_tanda='Pasar a Producción Por tanda';
          }
	      if ($parametros['cmd']=="inspeccion") {
	      	$val="Pasar a Embalaje";
	      	$val_tanda="Pasar a Embalaje Por tanda";
	      }
	      if ($parametros['cmd']=="embalaje") {
	      	 $val="Pasar a Calidad";
	      	 $val_tanda="Pasar a Calidad Por tanda";
	      }
	      if ($parametros['cmd']=="calidad") {
	      	 $val="Auditoria";
	      	 //$val_tanda="Historial";
	      }

          if (($parametros['cmd']!="historial")&&($parametros['cmd']!="calidad"))
          {
          	if ($parametros['cmd']=="produccion")
            {   $val="Pasar a Inspección";
	            $val_tanda="Pasar a Inspección Por tanda";
	            ////////////////////////////Quique////////////////////////
				//$nro_or=$_POST['nro_orden'];
			    $consulta="select nro_serie, mac, resultado
				            from ordenes.reportes
				            join ordenes.reporteorden  on ( reportes.id_reporte = reporteorden.id_reporte)
				            where resultado=1 and reporteorden.id_orden =".$nro_orden." group by nro_serie, mac, resultado";
				$rta_consulta=sql($consulta, "c871 ") or fin_pagina();
				$cant_que=$rta_consulta->recordCount();
				echo "<input type=hidden name=cant_que value='$cant_que'>\n";
				////////////////////////////Quique////////////////////////

	          $link_tanda=encode_link("confirmar_cantidad.php",array("nro_orden"=>$nro_orden,"cant_por_tanda"=>$cant_por_tanda,"estado"=>$parametros['cmd'],"proximo_estado"=>$val,"por_tanda"=>$por_tanda,"descontar"=>$descontar));

			 if ($por_tanda==0) {?>
	               <input type="submit" name="actualizar" Value="<?=$val?>" onclick="return (confirmar());">
	               <?}?>
	          <?/* <input type="button" name="actualizar_tanda" Value="<?=$val_tanda?>" onclick="if (confirmar()) window.open('<?=$link_tanda?>','','left=80,top=80,width=700,height=250,resizable=1,status=1');">*/?>
	         <input type="button" name="actualizar_tanda" Value="<?=$val_tanda?>" onclick="window.open('<?=$link_tanda?>','','left=80,top=80,width=700,height=250,resizable=1,status=1')">
	          <?
              }
	          else
	             {
	             $link_tanda=encode_link("confirmar_cantidad.php",array("nro_orden"=>$nro_orden,"cant_por_tanda"=>$cant_por_tanda,"estado"=>$parametros['cmd'],"proximo_estado"=>$val,"por_tanda"=>$por_tanda,"descontar"=>$descontar));

                if ($por_tanda==0) {?>
	      	     <input type="submit" name="actualizar" Value="<?=$val?>">
	      	    <?}?>
	      	     <input type="button" name="actualizar_tanda" Value="<?=$val_tanda?>" onclick="window.open('<?=$link_tanda?>','','left=80,top=80,width=700,height=250,resizable=1,status=1');">
	          <?
	             }
            }
            if ($parametros['cmd']=="calidad")
            { //Aprob&oacute; auditor&iacute;a de calidad. Listo para entrega

             if ($por_tanda==0 ) { //|| $cant_por_tanda==$cant_prod
             	//si nunca se dividio por tanda o si se dividio pero llego al sstado calidad con todas las maquinas
             	?>
             <input type="submit" name="actualizar1" style="background-color:#66BB66" align="middle" value="Aprob&oacute; auditor&iacute;a de calidad. Listo para entrega">
             <input type="submit" name="actualizar2" style="background-color:#BB5555" align="middle" value="REPRUEBA auditor&iacute;a de calidad. No se puede entregar"><br>
            <?}
            else {?>
             <input type="submit" name="actualizar1_tanda" align="middle" style="background-color:#66BB66" value="Aprob&oacute; auditor&iacute;a de calidad. Listo para entrega">
             <input type="submit" name="actualizar2_tanda" align="middle" style="background-color:#BB5555" value="REPRUEBA auditor&iacute;a de calidad. No se puede entregar"><br>
            <?}
            }
            ?>
	        <input type="button" name="volver" Value="Volver" onclick="document.location='seguimiento_produccion_bsas.php'">
	        <?
    }//de if ($volver=="seguimiento_produccion_bsas")
	else {
	      //if (!$volver) $vol="disabled";
	      if ($volver=="nueva_maquina.php")
                     echo "<input type=button name=cerrar value='Cerrar' onclick='window.close();'>";
	                 else
                     echo "<input type=button name=volver $vol value='Volver al listado' onclick='window.location=\"ordenes_ver.php\";'>";

	      if ($modo=="modificar")
                  $value="Modificar";
	              else
                  $value="Guardar";
          if (($i==0) || (($licitacion->fields["estado"]=="A" || $licitacion->fields["estado"]=="AN" || $licitacion->fields["estado"]=="T" || $licitacion->fields["estado"]=="E")) )
                     $disabled_guardar=" disabled";
                     else
                     $disabled_guardar=" ";


         if ($licitacion->fields["estado"] && $licitacion->fields["estado"]!="AN" && $licitacion->fields["estado"]!="E" && $licitacion->fields["estado"]!="A" && $licitacion->fields["estado"]!="PA" && $licitacion->fields["estado"]!="T")
                $disabled_para_autorizar="";
	            else
                $disabled_para_autorizar="disabled";

	     if ($licitacion->fields["estado"]=="PA" && permisos_check("inicio","permiso_autorizar"))
                $disabled_autorizar="";
	            else
                $disabled_autorizar="disabled";


	      //if ($estado && $licitacion->fields["estado"]!="P" && $licitacion->fields["estado"]!="AN" && $licitacion->fields["estado"]!="R" && $licitacion->fields["estado"]!="PA")
	      if ($estado=="PA" || $estado=="P")
                  $disabled_rechazar="";
	              else
                  $disabled_rechazar="disabled";

	      if ($licitacion->fields["estado"] && $licitacion->fields["estado"]!="AN")
                 $disabled_anular="";
	             else
                 $disabled_anular="disabled";
          $link_anular=encode_link("ordenes_nueva.php",array("modo"=>"Anular","nro_orden"=>$nro_orden,"volver"=>$volver));
          $onclick=" onclick='document.all.modo.value=\"$value\";document.all.frm_guardar.submit()'";
        ?>
        <input type=hidden name=rechazada>

        <!--<input type=button name='guardar' <?=$disabled_guardar?> value='Guardar' <?=$onclick?>>-->
        <input type=button name='guardar'  value='Guardar' <?=$onclick?>>		
        <input type=button name=paraautor <?=$disabled_para_autorizar?> value='Para Autorizar'  onclick='document.all.modo.value="Para Autorizar";document.all.frm_guardar.submit();'>
        <?
        if(permisos_check("inicio","permiso_autorizar_anular_OP"))
        {?>
         <input type=button name=autorizar <?=$disabled_autorizar?> value='Autorizar' onclick='document.all.modo.value="Autorizar";document.all.frm_guardar.submit();'>
         <input type=button name=rechazar  <?=$disabled_rechazar?> value='Rechazar'  onclick='window.open("rechazar.php","","toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=150");'>
         <input type=button name=anular    <?=$disabled_anular?>  value='Anular' onclick='window.location="<?=$link_anular?>"'>
	    <?
        }//de if(permisos_check("inicio","permiso_autorizar_anular_OP"))

	}//de if ($volver=="seguimiento_produccion_bsas")


        ?>
        </td>
       </tr>
	  </table>

      </form>
<?

}

echo fin_pagina();
?>