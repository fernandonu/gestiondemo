<?PHP
/*
Autor: Marco Canderle

$Author: mari $
$Revision: 1.44 $
$Date: 2007/01/08 17:59:38 $
*/
require_once("../../config.php");
require_once("../general/funciones_contactos.php");


$cmd1=$parametros['cmd1'] or $cmd1=$_POST["cmd1"];
variables_form_busqueda("lic_oc");
/*
$datos_barra = array(
					array(
						"descripcion"	=> "Actuales",
						"cmd"			=> "actual"
						//"sql_contar"	=> "SELECT count(*) as cant FROM gestiones_estado
						//					WHERE estado='actual'"
						),
					array(
						"descripcion"	=> "Finalizadas",
						"cmd"			=> "finalizada"
						//"sql_contar"	=> "SELECT count(*) as cant FROM gestiones_estado
						//					WHERE estado='finalizada'"
						)
					/*array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial",
						"sql_contar"	=> "SELECT count(*) as cant FROM gestiones_estado"
						)
				 );

if (!$cmd) {
	$cmd="actual";
}*/
if ($cmd1 == "Guardar") {

	$id = $parametros["id"] or $id=$_POST["id"];
	$sql1="select id_licitacion,fecha_pedida,usuario_pedida,fecha_entregada,usuario_entregada from garantia_oferta where id_licitacion=$id";
	$res_garantia=sql($sql1) or die;
	$pedida=0;$entregada=0;
	if($_POST['check_pedida']=='pedida')
	 $pedida=1;
	if($_POST['check_entregada']=='entregada')
	 $entregada=1;
	if($_POST['fecha_pedida']!="" && $pedida)
	 $fecha_pedida=fecha_db($_POST['fecha_pedida']) ;
	else
	 $fecha_pedida='01/01/1000';
	if($_POST['fecha_entregada']!="" && $entregada)
	 $fecha_entregada=fecha_db($_POST['fecha_entregada']) ;
	else
	 $fecha_entregada='01/01/1000';

	 if($fecha_pedida!=""&&$fecha_pedida!=$res_garantia->fields['fecha_pedida'] && $_POST["check_pedida"]!='')
	  $usuario_pedida=$_ses_user['name'];
	 elseif($_POST["check_pedida"]!='')
	  $usuario_pedida=$res_garantia->fields['usuario_pedida'];
	 else
	  $usuario_pedida="";

	 if($fecha_entregada!=""&&$fecha_entregada!=$res_garantia->fields['fecha_entregada'] && $_POST["check_entregada"]!='')
	  $usuario_entregada=$_ses_user['name'];
	 elseif($_POST["check_entregada"]!='')
	  $usuario_entregada=$res_garantia->fields['usuario_entregada'];
	 else
	  $usuario_entregada="";

	if($_POST['porcentaje_garantia']!="")
	 $porcentaje=$_POST['porcentaje_garantia'];
	else
	 $porcentaje=-1;
	//guarda los datos de porcentaje y fecha para la garantia de oferta
	//y los datos de la orden de compra
    if($res_garantia->fields['id_licitacion'])
	{
	 $sql2="update garantia_oferta set pedida=$pedida,entregada=$entregada,porcentaje=$porcentaje,fecha_pedida='$fecha_pedida',fecha_entregada='$fecha_entregada',usuario_pedida='$usuario_pedida',usuario_entregada='$usuario_entregada' where id_licitacion=$id";
	 //$sql_array[0]=$sql2;

	 $db->Execute($sql2) or die($db->ErrorMsg()."<br>$sql2");
	}
	else
	{$sql2="insert into garantia_oferta(pedida,entregada,porcentaje,fecha_pedida,fecha_entregada,usuario_pedida,usuario_entregada,id_licitacion) values($pedida,$entregada,$porcentaje,'$fecha_pedida','$fecha_entregada','$usuario_pedida','$usuario_entregada',$id)";
	 $sql_array[0]=$sql2;
	 //$db->Execute($sql2) or die($db->ErrorMsg()."<br>$sql2");
	}
	//guardamos el nuevo comentario si es que hay
	if($_POST["comentario_nuevo"]!="")
	{$sql = nuevo_comentario($_POST["id"],"GARANTIA_OFERTA",$_POST["comentario_nuevo"]);
	 $sql_array[1]=$sql;
	 //$db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	}
	//actualizamos el monto y la fecha de la orden de compra
	$sql3="update entregar_lic ";
    $where_si=0;
	if($_POST['monto_orden']!="")
     $sql3.="set monto_orden=".$_POST['monto_orden'];
    elseif($_POST['monto_orden']=="")
     $sql3.="set monto_orden=null";
    if($_POST['vence_orden']!="")
     $sql3.=",vence='".fecha_db($_POST['vence_orden'])."'";
    elseif($_POST['vence_orden']=="")
     $sql3.=",vence=null";
    if($_POST['combo_moneda']!=-1)
     $sql3.=",id_moneda=".$_POST['combo_moneda'];
    elseif($_POST['combo_moneda']=="")
     $sql3.=",id_moneda=null";

    $sql3.=" where id_licitacion=$id";
	 $sql_array[2]=$sql3;
	 //$db->Execute($sql3) or die($db->ErrorMsg()."<br>$sql3");

	//guardamos los datos de la entrega_estimada si se ingreso
	//(si no esta creada la entrada para esa licitacion,
	//se crea sino, se actualiza)
	if($_POST['fecha_estimada']!="")
	{
	 $sql4="select id_licitacion from entrega_estimada where id_licitacion=$id";
	 $estima=sql($sql4) or die;
	 //$estima=$db->Execute($sql4) or die($db->ErrorMsg());
	 $fecha_est=fecha_db($_POST['fecha_estimada']);
	 if ($estima->RecordCount()==0)//si no exite la entrada la creamos
	 {$sql5="insert into entrega_estimada(fecha_estimada,id_licitacion) values('$fecha_est',$id)";
	  //$db->Execute($sql5) or die ($db->ErrorMsg());
	  $sql_array[3]=$sql5;
	 }
	 else //sino, actulizamos la entrada existente
	 {$sql5="update entrega_estimada set fecha_estimada='$fecha_est' where id_licitacion=$id";
	  //$db->Execute($sql5) or die ($db->ErrorMsg());
	  $sql_array[3]=$sql5;
	 }
    }
	sql($sql_array) or die;
	//$cmd1="detalle";
}

if ($cmd1 == "modificar_comentario") {
	echo $html_header;
	$id_comentario = $parametros["id_comentario"];
	editar_comentario($id_comentario,"detalle");
}
elseif ($cmd1 == "detalle") {
	echo $html_header;
	$id = $parametros["id"] or $id=$_POST["id"];
	if ($id == "") {
		listado_licitaciones();
	}
	else {
		detalle($id);
	}
}
elseif ($cmd1 == "download") {
	$ID = $parametros["ID"];
	download_file($ID);

}
elseif ($_POST["guardar_comentario"]) {
	$id_comentario = $_POST["id_comentario"];
	$id_gestion = $_POST["id_gestion"];
	if (guardar_comentario()) {
		detalle($id_gestion);
	}
	else {
		editar_comentario($id_comentario,"detalle");
	}
}
elseif ($_POST["files_add"]) {
	$ID = $_POST["ID"];
	$extensiones = array("doc","obd","xls","zip");
	echo $html_header;
//	print_r($_POST);
	ProcForm($_FILES);
	detalle($ID);
	/*$filename="orden_de_compra_".$clave_valor[1].".pdf";
	$mailtext="Este es un mail enviado automaticamente por orden de compra";
	$mail_header="";
	$mail_header .= "MIME-Version: 1.0";
	$mail_header .= "\nfrom: Sistema Inteligente CORADIR";
	$mail_header .="\nTo: ".$clave_valor2[1];
	$mail_header .="\nBcc: daingara@unsl.edu.ar";
	$mail_header .= "\nReply-To: gonzalo@pcpower.com.ar";
	$mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary";
	$mail_header .= "\n\nThis is a multi-part message in MIME format ";
	// Mail-Text
	$mail_header .= "\n--$boundary";
	$mail_header .= "\nContent-Type: text/plain";
	$mail_header .= "\nContent-Transfer-Encoding: 8bit";
	$mail_header .= "\n\n" . $mailtext."\n";
	// Your File
	$mail_header .= "\n--$boundary";
	$mail_header .= "\nContent-Type: application/pdf; name=\"$filename\"";
	// Read from Array $contenttypes the right MIME-Typ
	$mail_header .= "\nContent-Transfer-Encoding: base64";
	$mail_header .= "\n\n".$archivo=chunk_split(base64_encode(fread(fopen($filepath.$filename, "r"), filesize($filepath.$filename))));
	// End
	$mail_header .= "\n--$boundary--";
	mail("","Nueva Orden de Compra Autorizada","",$mail_header)*/
}
elseif ($_POST["files_cancel"]) {
	$ID = $_POST["ID"];
	echo $html_header;
	detalle($ID);
}
elseif ($_POST["det_addfile"]) {
	genera_det_addfile("Licitaciones OC");
}
elseif ($_POST["det_delfile"]) {
	$ID = $_POST["id"];
	genera_det_delfile($id);
	echo $html_header;
	detalle($ID);
}
else {
	echo $html_header;
	listado_licitaciones();
}
echo "<script src='funciones.js'></script>";
echo "<script src='../../lib/popcalendar.js'></script>";
echo "<script>
function compara_fechas(fecha1,fecha2)
{
 var fecha_a=fecha1.split('/');
 var fecha_b=fecha2.split('/');
 var f1=new Date(fecha_a[2],fecha_a[1],fecha_a[0]);
 var f2=new Date(fecha_b[2],fecha_b[1],fecha_b[0]);

 if(f1>f2)
  return 1;
 else if(f1==f2)
  return 0;
 else
  return -1;
}

function control(fecha1,fecha2)
{var aux;

 //controlamos que no se olvide de agregar una fecha, cuando el check correspondiente esta chequeado.
 if(document.all.fecha_pedida.value=='' && document.all.check_pedida.checked==true)
 { alert('Debe especificar la fecha en que la garantia de oferta ha sido pedida');
   return false;
 }
 if(document.all.fecha_entregada.value=='' && document.all.check_entregada.checked==true)
 { alert('Debe especificar la fecha en que la garantia de oferta ha sido entregada');
   return false;
 }
 //controlamos que llene el campo de porcentaje si se pidio una orden de contrato
 if(document.all.check_pedida.checked==true && document.all.porcentaje_garantia.value=='')
 { alert('Debe especificar el porcentaje de la garantia de oferta');
   return false;
 }
 //si ingreso un monto debe elegir una moneda
 if(document.all.monto_orden.value!='' && document.all.combo_moneda.value==-1)
 { alert('Debe especificar la moneda para el monto de la orden de compra');
   return false;
 }
 //si selecciono una moneda debe agregar un monto para la orden de compra
 if(document.all.combo_moneda.value!=-1 && document.all.monto_orden.value=='')
 { alert('Debe ingresar el monto de la orden de compra');
   return false;
 }
 //controla si la fecha de entrega (anio1,mes1,dia1) es mayor que
 //la fecha de vencimiento de la orden de compra(anio2,mes2,dia2)
 aux=compara_fechas(fecha1,fecha2);
 if(aux==1)//si es mayor
 {if(confirm('La fecha de entrega estimada es posterior que la fecha de vencimiento de la orden de compra.¿Está seguro que desea guardar los datos?'))
   return true;
  else
   return false;
 }
 return true;
}



</script>";
function listado_licitaciones() {
	global $bgcolor3,$cmd,$cmd1,$proxima,$datos_barra;
	global $bgcolor2,$itemspp,$db,$parametros,$barra,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera;
	//generar_barra_nav($datos_barra);
	$orden = array(
		"default" => "3",
		"default_up" => "0",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.id_estado",
		"3" => "entregar_lic.vence",
		"4" => "entidad.nombre",
		"5" => "distrito.nombre",
		"6" => "licitacion.nro_lic_codificado"
	);

	$filtro = array(
		"distrito.nombre" => "Distrito",
		"entidad.nombre" => "Entidad",
		"licitacion.id_moneda" => "Moneda",
		"licitacion.id_licitacion" => "ID de licitación",
		"licitacion.nro_lic_codificado" => "Número de licitación"
	);

	$itemspp = 50;

	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
	echo "<table cellspacing=2 cellpadding=5 class='bordes' bgcolor=$bgcolor3 width=80% align=center>\n";
	echo "<tr><td align=center>\n";

	$sql_tmp = "SELECT DISTINCT licitacion.*, entidad.nombre as nombre_entidad, ";
	$sql_tmp .= "distrito.nombre as nombre_distrito,entregar_lic.vence,garantia_oferta.pedida,";
	$sql_tmp .= "entregar_lic.garantia_contrato_subida,garantia_oferta.entregada ";
	$sql_tmp .= "FROM (licitacion LEFT JOIN entidad ";
	$sql_tmp .= "USING (id_entidad)) ";
	$sql_tmp .= "LEFT JOIN distrito ";
	$sql_tmp .= "USING (id_distrito) ";
	$sql_tmp .= "LEFT JOIN entregar_lic ";
	$sql_tmp .= "USING (id_licitacion) ";
    $sql_tmp .= "LEFT JOIN garantia_oferta ";
	$sql_tmp .= "USING (id_licitacion) ";

	$where_tmp = "licitacion.id_estado=7 AND borrada='f' ";//estado orden de compra
    $contar="select count(*) from licitacion where id_estado=7 and borrada='f'";
    if($_POST['keyword'] || $keyword)
     $contar="buscar";
	list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);
//	echo $sql;
	$sql_est = "select id_estado,nombre,color from estado";
	$result = sql($sql_est) or die;
	$estados = array();//se guardan los estados que se mostraran en
	$estados[0]['color']="#FF0000";//color rojo
	$estados[0]['texto']="Orden de Compra vencida";
    $estados[1]['color']="#FF8000";//color naranja
	$estados[1]['texto']="Orden de Compra vence en 1 a 2 días";
    $estados[2]['color']="#FFFF80";//color amarillo
	$estados[2]['texto']="Orden de Compra vence en 3 a 5 días";


	echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";
	echo "</td></tr></table><br>\n";
	echo "</form>\n";
	$result = sql($sql) or die;
	echo "<table class='bordes' width=95% cellspacing=2 cellpadding=3 bgcolor=$bgcolor3 align=center>";
	echo "<tr><td colspan=5 align=left id=ma>\n";
	echo "<table width=100%><tr id=ma>\n";
	echo "<td width=30% align=left><b>
	Total:</b> $total_lic licitacion/es.</td>\n";
	echo "<td width=70% align=right>$link_pagina</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
	echo "<tr>";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
	echo "&nbsp;/&nbsp;<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>\n";
	echo "<td align=right id=mo title='Fecha de Vencimiento de la Orden de Compra'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Vencimiento</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Entidad</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Distrito</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))."'>Número</td>\n";

	echo "</tr>\n";

	while (!$result->EOF) {

		//guardamos en esta variable, las observaciones de la licitacion
		//para mostrarlos en title del nombre de la licitacion
		$title_obs=$result->fields["observaciones"];

		//LIMITAR OBSERVACIONES: controlamos el ancho y la cantidad de
		//lineas que tienen las observaciones y cortamos el string si
		//se pasa de alguno de los limites
		$long_title=strlen($title_obs);
		//cortamos si el string supera los 600 caracteres
		if($long_title>600)
		{$title_obs=substr($title_obs,0,600);
		 $title_obs.="   SIGUE >>>";
		}
		$count_n=str_count_letra("\n",$title_obs);
		//cortamos si el string tiene mas de 12 lineas
		if($count_n>12)
		{$cn=0;$j=0;
		 for($i=0;$i<$long_title;$i++)
		 {
		  if($cn>12)
		   $i=$long_title;
		  if($title_obs[$i]=="\n")
		   $cn++;
		  $j++;

		 }
		 $title_obs=substr($title_obs,0,$j);
		 $title_obs.="   SIGUE >>>";
		}
		//FIN DE LIMITAR OBSERVACIONES






		$ma = substr($result->fields["vence"],5,2);
		$da = substr($result->fields["vence"],8,2);
		$ya = substr($result->fields["vence"],0,4);

		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","id"=>$result->fields["id_licitacion"]));
		tr_tag($ref);

	/*mostramos un color de acuerdo al vencimiento de la orden de compra externa
     Rojo=orden vencida
     Naranja=2 dias habiles para vencimiento de la orden
     Amarillo=5 dias habiles para vencimiento de la orden
	 Verde oscuro=faltan mas de 5 dias para vencimiento de la orden
	*/
	$fecha_vencimiento=fecha($result->fields['vence']);
	$fecha_hoy1 = date("d/m/Y",mktime());
	switch(diferencia_dias_habiles($fecha_hoy1,$fecha_vencimiento))
	{case 0:$color_state="#FF0000";//color rojo
	        $texto_state="La orden de compra esta vencido";break; //ya vencio la orden
	 case 1:
	 case 2:$color_state="#FF8000";//color naranja
	        $texto_state="Faltan de 1 a 2 dias para el vencimiento de la orden";break; //1 o 2 dias habiles para vencer
	 case 3:
	 case 4:
	 case 5:$color_state="#FFFF80";//color amarillo
	        $texto_state="Faltan de 3 a 5 dias para vencimiento de la orden"; break;//3, 4 o 5 dias habiles para vencer
	 default:$color_state="#FFFFFF";//color verde oscuro
	         $texto_state="";
	         break;
	}

		echo "<td align=center bgcolor='".$color_state."' title='".$texto_state."'><b><a style='color=".contraste($color_state,"#000000","#ffffff").";' href='$ref'>".$result->fields["id_licitacion"]."</a></b></td>\n";
		echo "<td align=center title='Fecha de Vencimiento de la Orden de Compra'>$da/$ma/$ya</td>\n";
		echo "<td align=left title='".$title_obs."'>&nbsp;".html_out($result->fields["nombre_entidad"])."</td>\n";
		echo "<td align=left>&nbsp;".html_out($result->fields["nombre_distrito"])."</td>\n";

		echo "<td align=left valign=middle>".html_out($result->fields["nro_lic_codificado"]);
		if( $result->fields["garantia_contrato_subida"])
		{
			//echo "<img src=$html_root/imagenes/R.gif border=0 title='Garantía de Contrato cargada' width='15' height='14' align='absmiddle'></a>";
			echo "&nbsp;<span style='color:#FFD86F;background-color:#000000;font:bold;' title='Garantía de Contrato cargada'>&nbsp;G&nbsp;</span>";
		}

		//mostramos los iconos de ordenes (pedia y/o entregada)
		/*if($result->fields['pedida'])
		 echo " <b>P</b>";
		 //echo " <img src=$html_root/imagenes/check1.gif border=0 width='15' height='14' align='absmiddle' title='Esta licitacion ya ha sido chequeada'> ";
		if($result->fields['entregada'])
         echo " <b>E</b>";*/
		 //echo " <img src=$html_root/imagenes/check1.gif border=0 width='15' height='14' align='absmiddle' title='Esta licitacion ya ha sido chequeada'> ";
		echo "</td>\n";
		$result->MoveNext();
	}
	echo "</table><br>";
	echo "<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='98%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
	echo "<tr>\n";
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
	echo "</tr>\n";
	echo "</table>\n";
}


function detalle($id) {
	global $bgcolor3, $bgcolor2,$permisos,$_ses_user,$html_root,$db,$bgcolor_out;
	$sql = "SELECT licitacion.*, ";
    $sql.= "entidad.id_entidad as id_entidad, ";
	$sql .= "entidad.nombre as nombre_entidad, ";
	$sql .= "distrito.nombre as nombre_distrito, ";
	$sql .= "moneda.nombre as nombre_moneda, ";
	$sql .= "estado.nombre as nombre_estado, ";
	$sql .= "estado.color as color_estado, ";
	$sql .= "garantia_oferta.pedida,garantia_oferta.entregada,garantia_oferta.fecha_pedida,garantia_oferta.fecha_entregada,garantia_oferta.porcentaje,garantia_oferta.usuario_pedida,garantia_oferta.usuario_entregada, ";
	$sql .= "entregar_lic.orden_subida,entregar_lic.vence,entregar_lic.monto_orden, ";
	$sql .= "entrega_estimada.fecha_estimada, ";
	$sql .= "tipo_entidad.nombre as tipo_entidad,candado.estado as candado ";
	$sql .= "FROM licitacion ";
	$sql .= "LEFT JOIN entidad ";
	$sql .= "USING (id_entidad) ";
	$sql .= "LEFT JOIN tipo_entidad ";
	$sql .= "USING (id_tipo_entidad) ";
	$sql .= "LEFT JOIN distrito ";
	$sql .= "USING (id_distrito) ";
	$sql .= "LEFT JOIN moneda ";
	$sql .= "USING (id_moneda) ";
	$sql .= "LEFT JOIN estado ";
	$sql .= "USING (id_estado) ";
	$sql .= "LEFT JOIN candado ";
	$sql .= "USING (id_licitacion) ";
	$sql .= "LEFT JOIN garantia_oferta ";
	$sql .= "USING (id_licitacion) ";
	$sql .= "LEFT JOIN entregar_lic ";
	$sql .= "USING (id_licitacion) ";
    $sql .= "LEFT JOIN entrega_estimada ";
	$sql .= "USING (id_licitacion) ";
	$sql .= "WHERE licitacion.id_licitacion=$id";
	$result = sql($sql) or die;

// Detalles de la Licitacion
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Garantias de Oferta de la Licitación</b></td></tr>";
	if ($result->RecordCount() == 1) {
		$ma = substr($result->fields["fecha_apertura"],5,2);
		$da = substr($result->fields["fecha_apertura"],8,2);
		$ya = substr($result->fields["fecha_apertura"],0,4);
		$ha = substr($result->fields["fecha_apertura"],11,5);
		echo "<tr>\n";
		echo "<td  width=50% align=left valign=middle>";
		if($result->fields['candado']!=0)
		 echo "<img align=middle src=../../imagenes/candado1.gif border=0 title='Esta licitacion solo puede verse, pero no modificarse'> ";
		echo "<a style='cursor: hand;' onclick='parent.window.location=\""
			.encode_link("../../index.php",array ("menu"=>"licitaciones_view","extra"=>array("ID"=>$id,"cmd1"=>"detalle")))."\";'>"
			."<font size=4><b>ID:</b> "
			.$result->fields["id_licitacion"]."</font></a>";
     	//$link=encode_link("$html_root/index.php",array("menu"=>"licitaciones_view","extra"=>array("cmd1"=>"detalle","ID"=>$id)));
		//echo "&nbsp;<input type=button name='ir' value='Ir' title='Ir a Detalle Completo de Licitaciones' onclick=\"parent.document.location='$link';\">";
		$link=encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id));
		echo "&nbsp;<input type=button name='ir' value='Ir' title='Ir a Detalle Completo de Licitaciones' onclick=\"location.href='$link';\">";
		echo "</td>\n";
		echo "<td  width=50% align=left><b>Apertura: <font color=#FF0000>$da/$ma/$ya</font></b><br><b>Hora: <font color=#FF0000>$ha</font></b></td>\n";
		echo "</tr><tr>\n";
		echo "<td align=left colspan=1>";
        echo "<table width='100%'>" ;
        echo "<tr>";
        echo "<td width='70%'>";
        echo "<b>Distrito:</b> ".$result->fields["nombre_distrito"]."\n";
		echo "<br><b>Entidad:</b> ".html_out($result->fields["nombre_entidad"]);
        echo "<br><b>Dirección: </b>" .html_out($result->fields["dir_entidad"]);
        echo "<br><b>Tipo de Entidad:</b> ".html_out($result->fields["tipo_entidad"]);
        echo "</td>";
        echo "<td valign='top'>";
        $id_entidad=$result->fields["id_entidad"];
        $perfil=encode_link("./perfil_entidad.php",array("id_entidad"=>$id_entidad,"modulo"=>"Licitaciones"));
        echo "<input type='button' name='Nuevo' Value='Perfil' style=\"width:100%\" onclick=\"window.open('$perfil','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400');\">";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</td>";
        echo "<td align=left>";
//incluyo la funcion que verifica si hay contactos
        echo "<table width='100%' align='right'>";
        echo "<tr align='right'>";
        echo "<td align='right'>";
        $nuevo_contacto=encode_link("../general/contactos.php",array("modulo"=>"Licitaciones",
                                         "id_licitaciones"=>$ID,
                                         "id_general"=>$result->fields['id_entidad']));
        echo "<input type='button' name='Nuevo' Value='Nuevo Contacto' style=\"width:30%\" onclick=\"window.open('$nuevo_contacto','','toolbar=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr align='right'>";
        echo "<td>";
        contactos_existentes("Licitaciones",$result->fields['id_entidad']);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</td>";
		echo "</tr>\n";

		//echo "<tr><td colspan=2><input type=button name='detalle' value='Más detalles' title='Muestra más detalles de la licitacion' onclick='desplegar_detalle_lic()'><br>";
		echo "<tr><td colspan=2>";
		echo "<table width=100% border=1 id='detalle_lic'>";
		/***************************************************************************************************
		hiddens que guardan los valores a mostrar si presionan el boton Mas detalles
		****************************************************************************************************/
        //echo "<input type='hidden' name='mant_oferta' value='$value'>";
		//echo "<input type='hidden' name='forma_pago' value='$value'>";
	    //echo "<input type='hidden' name='plazo_entrega' value='$value'>";
		//echo "<input type='hidden' name='fecha_entrega' value='$value'>";
        //echo "<input type='hidden' name='nro_lic' value='$value'>";
		//echo "<input type='hidden' name='expediente_h' value='$value'>";
		//echo "<input type='hidden' name='valor_pliego' value='$value'>";
        //echo "<input type='hidden' name='nombre_moneda' value='$value'>";
		//echo "<input type='hidden' name='monto_ofertado' value='$value'>";
		//echo "<input type='hidden' name='monto_estimado' value='$value'>";
		//echo "<input type='hidden' name='monto_ganado' value='$value'>";
		//echo "<input type='hidden' name='color_estado' value='$value'>";
		//echo "<input type='hidden' name='nombre_estado' value='$value'>";
		//echo "<input type='hidden' name='observ_coment' value='$value'>";
		//echo "<input type='hidden' name='perfil' value='$value'>";
		//echo "<input type='hidden' name='protocolo' value='$value'>";

		/******************************************************************
				AHORA SE USA UNA TABLA DESPLEGADA DIRECTAMENTE
		*******************************************************************/
		if ($result->fields["fecha_entrega"] != "") {
				$me = substr($result->fields["fecha_entrega"],5,2);
				$de = substr($result->fields["fecha_entrega"],8,2);
				$ye = substr($result->fields["fecha_entrega"],0,4);
				$fecha_en="$de/$me/$ye";
		}
		else { $fecha_en="N/A\n"; }
		//este control es porque las licitaciones que ya estan cargadas no tiene nuemro de expediente
		if ($result->fields["exp_lic_codificado"]=="") $exp_lic=0;
		else $exp_lic=$result->fields["exp_lic_codificado"];

		if ($result->fields["perfil"])
			$perfil_t=$result->fields["perfil"];
		else
		    $perfil_t="<b><font color=red>No se ha cargado el perfil</font></b>";
		?>
		<tr>
		 <td width='50%'>
		  <b>Mantenimiento de oferta:</b> <?=$result->fields["mant_oferta_especial"]?>
          <br><b>Forma de pago:</b> <?=$result->fields["forma_de_pago"]?>
          <br><b>Plazo de entrega: </b><?=$result->fields["plazo_entrega"]?>
          <br><b>Fecha de entrega: </b><?=$fecha_en?>
         </td>
         <td width='50%'>
          <b>Número:</b> <?=$result->fields["nro_lic_codificado"]?>
          <br><b>Expediente:</b> <?=$exp_lic?>
          <br><b>Valor del pliego:</b> <?=formato_money($result->fields["valor_pliego"])?>
         </td>
        </tr>
        <tr>
         <td width='50%'>
          <b>Moneda:</b> <?=$result->fields["nombre_moneda"]?>
          <br><b>Ofertado:</b> <?=formato_money($result->fields["monto_ofertado"])?>
          <br><b>Estimado:</b> <?=formato_money($result->fields["monto_estimado"])?>
          <br><b>Ganado:</b> <?=formato_money($result->fields["monto_ganado"])?>
         </td>
         <td width='50%'>
          <b>Estado:</b> <span style=\"background-color: <?=$result->fields["color_estado"]?>;
		  border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;\">&nbsp;&nbsp;&nbsp;</span> <?=$result->fields["nombre_estado"]?>
         </td>
        </tr>
        <tr>
         <td colspan="2">
          <b>Comentarios/Seguimiento:</b><br> <?=$result->fields["observaciones"]?>
         </td>
        </tr>
        <tr>
         <td colspan="2">
          <b>Perfil: </b> <?=$perfil_t?>
         </td>
        </tr>
        <?
        $sql="select * from protocolo_leg where id_licitacion=".$id." and entidad='".$result->fields["nombre_entidad"]."';"; //verifico si llenaron el protocolo
		$resultado_ex=sql($sql) or die;
		if ($resultado_ex->RecordCount()<=0)
		{?>
         <tr>
          <td colspan="2">
		   <b>Protocolo Legal:<font color="red"> No se ha cargado el protocolo legal</font>
		  </td>
		 </tr>
		<?
		}

		/******************************************************************************************************************************************************************/
		echo "</table>";
		echo "</td></tr>";

		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
        echo "<input type='hidden' name='mas1' value=''>";
		if($result->fields['pedida']==1)
		{$estado_garantia="pedida";
		 $checked_pedida="checked";
		 $fecha_pedida=fecha($result->fields['fecha_pedida']);
		}
		else
		{//$disabled_pedida="disabled";
		 $fecha_pedida="";
		}

		if($result->fields['entregada']==1)
		{$estado_garantia="entregada";
		 $checked_entregada="checked";
		 $fecha_entregada=fecha($result->fields['fecha_entregada']);
		}
		else
		{//$disabled_entregada="disabled";
		 $fecha_entregada="";
		}

		if($fecha_pedida=="01/01/1000")
		 $fecha_pedida="";
		if($fecha_entregada=="01/01/1000")
		 $fecha_entregada="";
		if($result->fields['pedida']==1 ||$result->fields['entregada']==1)
		{echo "<tr><td colspan=2 align='center'><table width=95%><tr><td align=center><font size=+1><b>La garantia de oferta ya fue $estado_garantia<b></font></td></tr>";
		 echo "<tr><td>&nbsp;</td></tr><tr><td>";
		 if($result->fields['pedida']==1)
		  echo "<table width='100%'><tr><td align=left width=30%><b>Garantia Pedida por: </b></td><td align=left width=35%><b>".$result->fields['usuario_pedida']."</b></td><td align=left width=30%><b>Fecha Garantia Pedida: </b></td><td align=right width=5%><b>".fecha($result->fields['fecha_pedida'])."</b></td></tr></table>";
		 if($result->fields['entregada']==1)
		  echo "<table width='100%'><tr><td align=left width=30%><b>Garantia Entregada por: </b></td><td align=left width=35%><b>".$result->fields['usuario_entregada']."</b></td><td align=left width=30%><b>Fecha Garantia Entregada: </b></td><td width=5% align=right><b>".fecha($result->fields['fecha_entregada'])."</b></td></tr></table>";
		 echo "</td></tr></table></td></tr>";
		}
		echo "<tr><td colspan=2><table align='center'>";
		if($result->fields['orden_subida'])
		 $monto_orden=$result->fields['monto_orden'];
		else
		 $monto_orden='';
		echo "<tr><td width='65%'><b>Monto de la orden de compra </b></td><td width='35%'>";

	   //si existe monto para la orden de compra, traemos la moneda que le corresonde
	   if(es_numero($result->fields['monto_orden']))
	   {$moneda_sel=sql("select id_moneda from entregar_lic where id_licitacion=$id") or die;
	    $moneda=$moneda_sel->fields['id_moneda'];
	   }
	   //traemos el tipo de la moneda
       $moneda_query=sql("select nombre,id_moneda from moneda") or die;
	   echo "<select name='combo_moneda' title='Seleccione una moneda'>
         <option value=-1>Moneda</option>";
       while(!$moneda_query->EOF)
       {echo "<option value=".$moneda_query->fields['id_moneda'];
        if($moneda==$moneda_query->fields['id_moneda'])
         echo " selected ";
        echo ">".$moneda_query->fields['nombre']."</option>";
   	    $moneda_query->MoveNext();
        }

        echo "</select> ";

		echo "<input type='text' name='monto_orden' value='$monto_orden' size=10></td></tr>\n";
		if($result->fields['orden_subida'])
		 $fecha_orden=Fecha($result->fields['vence']);
		else
		 $fecha_orden='';
		echo "<tr><td><b>Vencimiento orden de compra</b></td><td><input type='text' name='vence_orden' value='$fecha_orden'>";echo link_calendario("vence_orden");echo"</td></tr>\n";
		echo "<tr><td colspan=2><hr></td></tr>";

		//control de que solo los usuarios con permiso pueden deshabilitar los checkboxes

		if ($checked_pedida=="checked" && !permisos_check("inicio","sacar_check_oc"))
     		$disabled_permiso_pedida="disabled";
     	else
     	 	$disabled_permiso_pedida="";

     	if ($checked_entregada=="checked" && !permisos_check("inicio","sacar_check_oc"))
     		$disabled_permiso_entregada="disabled";
     	else
     	 	$disabled_permiso_entregada="";
     	if($result->fields['porcentaje']!=-1)
     	 $percent=$result->fields['porcentaje'];
     	else
     	 $percent="";
		echo "<tr><td><b>Garantia de contrato pedida </b><input type='checkbox' name='check_pedida' value='pedida' $checked_pedida $disabled_permiso_pedida></td><td><b>Garantia de contrato entregada </b><input type='checkbox' name='check_entregada' value='entregada' $checked_entregada $disabled_permiso_entregada></td></tr>";
		echo "<tr><td><input type='text' name='fecha_pedida' value='$fecha_pedida' $disabled_pedida>";echo link_calendario("fecha_pedida");echo"</td><td><input type='text' name='fecha_entregada' value='$fecha_entregada' $disabled_entregada>";echo link_calendario("fecha_entregada");echo"</td></tr>\n";
		echo "<tr><td><b>Fecha garantia pedida</b></td><td><b>Fecha garantia entregada</b></td></tr>\n";
		echo "<tr><td colspan=2>&nbsp;</td></tr>";
		echo "<tr><td><b>Porcentaje de la garantia de oferta</b></td><td><input type='text' name='porcentaje_garantia' value='$percent' size=3><b>%</b></td></tr>\n";
		echo "<tr><td colspan=2><hr></td></tr>";
		if($result->fields['fecha_estimada']!="")
		 $fecha_estimada=fecha($result->fields['fecha_estimada']);
		else
		 $fecha_estimada="";
		echo "<tr><td><b>Fecha Estimada de Entrega</b></td><td><input type=text name=fecha_estimada value='$fecha_estimada'>";echo link_calendario("fecha_estimada");echo"</td></tr>";
		echo "<tr><td colspan=2 align=center>
		         <input type=button name=ordenes_asoc style='width:200;' value='Ordenes de compra asociadas' onClick=\"location.href='".encode_link("../ord_compra/ord_compra_listar.php",array("filtro"=>"todas","keyword"=>$id,"filter"=>"o.id_licitacion","volver_lic"=>$id))."';\"> ";
		echo "&nbsp;&nbsp;<input type=button name=ordenes_prod_asoc style='width:200;' value='Ordenes de producción asociadas' onClick=\"location.href='".encode_link("../ordprod/ordenes_ver.php",array("filtro"=>"todas","keyword"=>$id,"volver_lic"=>$id))."';\">";
		
		//echo "<tr><td colspan=2 align=center><input type=button name=ordenes_asoc style='width:200;' value='Ordenes de compra asociadas' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"ord_compra_listar","extra"=>array("filtro"=>"todas","keyword"=>$id,"filter"=>"o.id_licitacion","volver_lic"=>$id)))."';\"> ";
		//echo "&nbsp;&nbsp;<input type=button name=ordenes_prod_asoc style='width:200;' value='Ordenes de producción asociadas' onClick=\"parent.document.location='".encode_link($html_root."/index.php",array("menu"=>"ordenes_ver","extra"=>array("filtro"=>"todas","keyword"=>$id,"volver_lic"=>$id)))."';\">";
		echo "</td></tr>";
		echo "</table></td></tr>";


		if ($result->fields["ultimo_usuario"]) {
			$mm = substr($result->fields["ultimo_usuario_fecha"],5,2);
			$dm = substr($result->fields["ultimo_usuario_fecha"],8,2);
			$ym = substr($result->fields["ultimo_usuario_fecha"],0,4);
			$hm = substr($result->fields["ultimo_usuario_fecha"],11,5);
			echo "<tr>\n";
			echo "<td colspan=2><b>Ultima modificación hecha por ".$result->fields["ultimo_usuario"]." el $dm/$mm/$ym a las $hm</b></td>\n";
			echo "</tr>\n";
		}
		echo "<tr>\n";
			echo "<td align=left colspan=2>\n";
			echo "<b>Archivos:</b><br>\n";
		    lista_archivos_lic($id);
		    echo "<br><center><input type=submit name=det_addfile style='width:160;' value='Agregar archivo'>&nbsp;";
		    echo "<input type=submit name=det_delfile style='width:160;' ".$disabled." value='Eliminar Archivos' onClick=\"return confirm('ADVERTENCIA: Se van a eliminar los archivos seleccionados');\"></center><br>";

		echo "</td></tr>\n";
		echo "<tr><td colspan=2>\n";
		echo $est->fields["estado"];
		gestiones_comentarios($id,"GARANTIA_OFERTA",1);
		echo "</td></tr>\n";
		echo "<tr> <td style=\"border:$bgcolor2\" colspan=2 align=center>";
		echo "<a style='cursor: hand;' onclick='parent.window.location=\""
			.encode_link("../../index.php",array ("menu"=>"licitaciones_view","extra"=>array("ID"=>$id,"cmd1"=>"detalle")))."\";'>"
			."<font size=4><b>ID:</b> "
			.$result->fields["id_licitacion"]."</font></a>";
		echo "</td></tr><tr>";
		echo "<td align=center colspan=2 style=\"border:$bgcolor2\"><br>";
		echo "<input type=hidden name=id value='$id'>";
		echo "<input type=submit name=cmd1 value='Guardar' onclick='return control(document.all.fecha_estimada.value,document.all.vence_orden.value)' style='width:160'>";
		echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
		echo "<br><br></td>";
		echo "</tr>";
		echo "</table></form><br>";

	}

}
fin_pagina();
?>