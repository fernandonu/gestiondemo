<?PHP
/*
Author: marco_canderle

MODIFICADA POR
$Author: mari $
$Revision: 1.7 $
$Date: 2007/01/05 19:53:59 $
*/
require_once("../../config.php");
require_once("../general/funciones_contactos.php");

echo $html_header;
variables_form_busqueda("gestion");

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
						)*/
				 );

if (!$cmd) {
	$cmd="actual";
}
if ($cmd1 == "Guardar Comentario" && $_POST["comentario_nuevo"]) {
	$sql1="select count(*) as cant from gestiones_estado where id_gestion=".$_POST["id"];
	$cantidad=sql($sql1) or die;
	if ($cantidad->fields["cant"]==0)
		$sql_array[0]="INSERT INTO gestiones_estado (id_gestion,estado) values (".$_POST["id"].",'actual')";
	$sql = nuevo_comentario($_POST["id"],"GESTIONES",$_POST["comentario_nuevo"]);
	$sql_array[1]=$sql;
	sql($sql_array) or die;
	$cmd1="detalle";
}
if ($cmd1 =="Finalizar") {
	$sql="UPDATE gestiones_estado SET estado='finalizada' WHERE id_gestion=".$_POST["id"];
	sql($sql) or die;
	$cmd1="detalle";
}
if ($cmd1 == "modificar_comentario") {
	$id_comentario = $parametros["id_comentario"];
	editar_comentario($id_comentario,"detalle");
}
elseif ($cmd1 == "detalle") {
	$id = $parametros["id"] or $id=$_POST["id"];
	if ($id == "") {
		listado_licitaciones();
	}
	else {
		detalle($id);
	}
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
else {
	listado_licitaciones();
}
function listado_licitaciones() {
	global $bgcolor3,$cmd,$cmd1,$proxima,$datos_barra;
	global $bgcolor2,$itemspp,$db,$parametros,$barra,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera;
	generar_barra_nav($datos_barra);
	$orden = array(
		"default" => "3",
		"default_up" => "0",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.id_estado",
		"3" => "licitacion.fecha_apertura",
		"4" => "entidad.nombre",
		"5" => "distrito.nombre",
		"6" => "licitacion.nro_lic_codificado"
	);

	$filtro = array(
		"distrito.nombre" => "Distrito",
		"entidad.nombre" => "Entidad",
		"gestiones_comentarios.comentario" => "Comentarios",
		"licitacion.mant_oferta_especial" => "Mantenimiento de oferta",
		"licitacion.forma_de_pago" => "Forma de pago",
		"licitacion.id_moneda" => "Moneda",
		"licitacion.id_licitacion" => "ID de licitación",
		"licitacion.nro_lic_codificado" => "Número de licitación"
	);

	$itemspp = 50;

	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
//	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
	echo "<tr><td align=center>\n";

	$sql_tmp = "SELECT DISTINCT gestiones_comentarios.id_gestion,licitacion.*, entidad.nombre as nombre_entidad, distrito.nombre as nombre_distrito ";
	$sql_tmp .= "FROM (licitacion LEFT JOIN entidad ";
	$sql_tmp .= "USING (id_entidad)) ";
	$sql_tmp .= "LEFT JOIN distrito ";
	$sql_tmp .= "USING (id_distrito) ";
	$sql_tmp .= "LEFT JOIN gestiones_comentarios ";
	$sql_tmp .= "ON gestiones_comentarios.id_gestion=id_licitacion ";
	$sql_tmp .= "LEFT JOIN gestiones_estado ";
	$sql_tmp .= "ON gestiones_estado.id_gestion=id_licitacion ";
	
	//$where_tmp = " (licitacion.id_estado=1 ";
	//$where_tmp .= "OR licitacion.id_estado=7) ";
	$where_tmp = "gestiones_comentarios.tipo='GESTIONES' ";
	if ($cmd!="historial")
		$where_tmp .= "AND gestiones_estado.estado='$cmd' ";
	$where_tmp .= "AND borrada='f' ";
//	$where_tmp .= "GROUP BY id_gestion";
    $contar="buscar"; 
    list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);
//	echo $sql;
	$sql_est = "select id_estado,nombre,color from estado";
	$result = sql($sql_est) or die;
	$estados = array();
	while (!$result->EOF) {
		$estados[$result->fields["id_estado"]] = array(
				"color" => $result->fields["color"],
				"texto" => $result->fields["nombre"]
			);
		$result->MoveNext();
	}
/*	foreach ($estados as $est => $arr) {
		echo "<option value=$est";
		if ("$est" == "$estado") { echo " selected"; }
		echo ">".$estados[$est]["texto"];
	}
	echo "</select>";*/
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
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Apertura</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Entidad</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Distrito</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))."'>Número</td>\n";
	
	echo "</tr>\n";
 
	while (!$result->EOF) {

		//guardamos en esta variable, las observaciones de la licitacion
		//para mostrarlos en title del nobre de la licitacion
		$title_obs=$result->fields["observaciones"];

		$ma = substr($result->fields["fecha_apertura"],5,2);
		$da = substr($result->fields["fecha_apertura"],8,2);
		$ya = substr($result->fields["fecha_apertura"],0,4);
//		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle","sort"=>$sort,"up"=>$parametros["up"],"page"=>$page,"keyword"=>$keyword,"estado"=>$estado,"filter"=>$filter,"ID"=>$result->fields["id_licitacion"]));
		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","id"=>$result->fields["id_licitacion"]));
		tr_tag($ref);
		echo "<td align=center bgcolor='".$estados[$result->fields["id_estado"]]["color"]."' title='".$estados[$result->fields["id_estado"]]["texto"]."'><b><a style='color=".contraste($estados[$result->fields["id_estado"]]["color"],"#000000","#ffffff").";' href='$ref'>".$result->fields["id_licitacion"]."</a></b></td>\n";
		echo "<td align=center>$da/$ma/$ya</td>\n";
		echo "<td align=left title='".$title_obs."'>&nbsp;".html_out($result->fields["nombre_entidad"])."</td>\n";
		echo "<td align=left>&nbsp;".html_out($result->fields["nombre_distrito"])."</td>\n";
		
/*		$query="select res.id_renglon from (select id_renglon, id_licitacion from  renglon where id_licitacion=".$result->fields['id_licitacion'].") AS res, oferta  where res.id_renglon=oferta.id_renglon";  
		$res=$db->Execute($query) or die($db->ErrorMsg());
		if ($res->RecordCount() > 0 ) 
		$valor=1;
		else $valor=0;
*/		
		echo "<td align=left valign=middle>".html_out($result->fields["nro_lic_codificado"]);
/*		switch ($valor)
		{
		  case 0:
				 break;
		  case 1: echo "<a href='".encode_link("lic_ver_res.php",array("keyword"=>$result->fields["id_licitacion"]))."'>
			 <img src=$html_root/imagenes/R.gif border=0 title='Ver Resultados' width='15' height='14' align='absmiddle'></a>";
			 break;
		}
		 
				//mostramos el logo de orden de produccion subida, 
		//si existe la entrada correspondiente para esta licitacion
		$query="select orden_subida,mostrar,vence,oferta_subida,archivo_oferta from entregar_lic where id_licitacion=".$result->fields['id_licitacion'];
		$res1=$db->Execute($query)  or die ($db->ErrorMsg().$query);
		$link_mostrar=encode_link("mostrar_logo.php",array("id_licitacion"=>$result->fields['id_licitacion'])); 
		if(($res1->fields['orden_subida']==1)&&($res1->fields['mostrar']==1))
		 echo "&nbsp;<a href=\"#\" onmousedown=\"window.open('$link_mostrar','','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,left=170,top=220,width=500,height=100');return false;\"> <img src=$html_root/imagenes/peso.gif border=0 title='La orden de compra ha sido cargada' width='15' height='14' align='absmiddle'></a>";
		if(($proxima)&&($res1->fields['oferta_subida']==1))
		{//revisamos si el archivo de oferta esta listo para imprimir
		 $query="select imprimir from archivos where id_licitacion=".$result->fields['id_licitacion']." and nombre='".$res1->fields['archivo_oferta']."'";
		 $res_n=$db->Execute($query)  or die ($db->ErrorMsg().$query);
		 if($res_n->fields['imprimir']=='t')
		  echo "&nbsp;<img src=$html_root/imagenes/enviar1.gif border=0 title='La oferta ha sido cargada' width='15' height='14' align='absmiddle'>";
		}
		*/
		//mostramos el logo de impresora, si el archivo correspondiente
		//a esta licitacion, esta listo para imprimir
		/*	
		$nombre="oferta_lic_";
		$nombre.=$result->fields['id_licitacion'];
		$nombre.=".xls";
		$archivo="select imprimir from archivos where nombre='$nombre'	";
		$res1=$db->Execute($archivo)  or die ($db->ErrorMsg().$archivo);
		if($res1->fields['imprimir']=='t')
		 echo "&nbsp; <img src=$html_root/imagenes/imp2.gif border=0 title='La licitación esta lista para imprimir' height='14'>\n";
		*/
		 echo "</td>\n";
		$result->MoveNext();
	}
	echo "<tr><td colspan=6 align=center><br>\n";
	echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
	echo "<tr>\n";
	$cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td></tr>\n";
	echo "</table><br>\n";	
}


function detalle($id){
	global $bgcolor3, $bgcolor2, $bgcolor_out;
	$sql = "SELECT licitacion.*, ";
    $sql.= "entidad.id_entidad as id_entidad, ";
	$sql .= "entidad.nombre as nombre_entidad, ";
	$sql .= "distrito.nombre as nombre_distrito, ";
	$sql .= "moneda.nombre as nombre_moneda, ";
	$sql .= "estado.nombre as nombre_estado, ";
	$sql .= "estado.color as color_estado, ";
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
	$sql .= "WHERE licitacion.id_licitacion=$id";
	$result = sql($sql) or die;
// Detalles de la Licitacion
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor_out align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Gestion de la Licitación</b></td></tr>";
	if ($result->RecordCount() == 1) {
		$query="select estado from gestiones_estado where id_gestion=$id";
		$est=sql($query) or die;
		$ma = substr($result->fields["fecha_apertura"],5,2);
		$da = substr($result->fields["fecha_apertura"],8,2);
		$ya = substr($result->fields["fecha_apertura"],0,4);
		$ha = substr($result->fields["fecha_apertura"],11,5);
		echo "<tr>\n";
		echo "<td  width=50% align=left valign=middle>";
		if($result->fields['candado']!=0)
			echo "<img align=middle src=$html_root/imagenes/candado1.gif border=0 title='Esta licitacion solo puede verse, pero no modificarse'> ";
	     	//echo "<a style='cursor: hand;' onclick='parent.window.location=\""
			//.encode_link("../../index.php",array ("menu"=>"licitaciones_view","extra"=>array("ID"=>$id,"cmd1"=>"detalle")))."\";'>"
			//."<font size=4><b>ID:</b> "
			//.$result->fields["id_licitacion"]."</font></a>";
			
			$link=encode_link("../licitaciones/licitaciones_view.php",array("ID"=>$id,"cmd1"=>"detalle"));
			?>
			<a href="<?=$link?>" target="_blank"><font size=4 color="Black"><b>ID: </b><?=$result->fields["id_licitacion"]?></font></A>
			<?
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
		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
		if ($result->fields["ultimo_usuario"]) {
			$mm = substr($result->fields["ultimo_usuario_fecha"],5,2);
			$dm = substr($result->fields["ultimo_usuario_fecha"],8,2);
			$ym = substr($result->fields["ultimo_usuario_fecha"],0,4);
			$hm = substr($result->fields["ultimo_usuario_fecha"],11,5);
			echo "<tr>\n";
			echo "<td colspan=2><b>Ultima modificación hecha por ".$result->fields["ultimo_usuario"]." el $dm/$mm/$ym a las $hm</b></td>\n";
			echo "</tr>\n";
		}
		echo "<tr><td colspan=2>\n";
		echo $est->fields["estado"];
		if (!$est or $est->fields["estado"]!="finalizada")
			$cambiar=1;
		else
			$cambiar=0;
		gestiones_comentarios($id,"GESTIONES",$cambiar);
		echo "</td></tr>\n";
		echo "<tr> <td style=\"border:$bgcolor2\" colspan=2 align=center>";
		echo "<a style='cursor: hand;' onclick='parent.window.location=\""
			.encode_link("../../index.php",array ("menu"=>"licitaciones_view","extra"=>array("ID"=>$id,"cmd1"=>"detalle")))."\";'>"
			."<font size=4><b>ID:</b> "
			.$result->fields["id_licitacion"]."</font></a>";
		echo "</td></tr><tr>";
		echo "<td align=center colspan=2 style=\"border:$bgcolor2\"><br>";
		echo "<input type=hidden name=id value='$id'>";
		if (!$est or $est->fields["estado"]!="finalizada"){
			echo "<input type=submit name=cmd1 value='Guardar Comentario'>";
			if ($est->fields["estados"]!="")
				echo "<input type=submit name=cmd1 value='Finalizar'>";
		}
		echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
		echo "<br><br></td>";
		echo "</tr>";
		echo "</table></form><br>";
	}
}
?>