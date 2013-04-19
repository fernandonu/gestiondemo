<?PHP
/*
Autor: ???
Modificado por:
$Author: mari $
$Revision: 1.138 $
$Date: 2007/02/19 11:44:58 $
*/
require_once("../personal/gutils.php");
require_once("../../config.php");
require_once("../ord_compra/fns.php");


function licitacion_produccion($licitacion){
global $db,$_ses_user;
if (($_ses_user['login']=="juanmanuel") || ($_ses_user['login']=="mascioni") || ($_ses_user['login']=="victor") || ($_ses_user['login']=="serval") || ($_ses_user['login']=="diego"))
 {
 $sql="select nro_orden from orden_de_produccion where orden_de_produccion.estado<>'AN' and id_licitacion=$licitacion";
 $resultado=sql($sql) or die ($db->ErrorMsg()."<br>".$sql);
 if ($resultado->RecordCount()>0) //existen ordenes de produccion asociadas a la licitacion
  echo "bgcolor='#aaaacc'";
 }
}

$var_sesion=array(
                  "lideres"=>""
                  );
       
$download=$parametros['download'];
$cmd1=$parametros['cmd1'] or $cmd1=$_POST["cmd1"];
//$sel_lideres=array("lista"=>"", "sel_lideres_inic"=>"");
variables_form_busqueda("seg_ord",$var_sesion);

if ($cmd == "") {
	     $cmd="actuales";
	     $_ses_seg_ord['cmd']=$cmd;
	     $_ses_seg_ord['lideres']="";
       // phpss_svars_set("_ses_seg_ord", $_ses_seg_ord);
       }

/*print_r($_ses_seg_ord);
echo "<br> $lideres-".$_POST['lideres'];*/
$id_lic=$_POST['id'];

if ($_POST['bregistrar'])
{
	$q="insert into usuarios_reg (id_usuario) values ({$_ses_user['id']})";
	sql($q) or fin_pagina();
}

if ($download)
   ob_start();
else
{
	$silent=1;//variable usada por el archivo requerido
	//require("op_problemas_xml.php");

}

function listado_licitaciones() {
    global $bgcolor3,$cmd,$cmd1,$proxima,$datos_barra,$lideres;
    global $bgcolor2,$itemspp,$db,$parametros,$barra,$html_header,$html_root;
    global $keyword,$filter,$page,$sort,$estado,$ver_papelera,$atrib_tr,$download,$_ses_user;
    //global $_ses_seg_ord, $sel_lideres_inic;
    $datos_barra = array(
			array(
			      "descripcion"=> "Actuales",
			      "cmd"=> "actuales",
			     ),
			array(
			      "descripcion"=> "Historial",
			      "cmd"=> "historial"
			    )
			);
    
    if($cmd=="historial") $or=0;
    else $or=1;
    //echo "<br> el valor de or es: ".$or;
    generar_barra_nav($datos_barra);
    $orden = array(
        "default" => "4",
        "default_up" => "$or",
        "1" => "licitacion.id_licitacion",
        //"2" => "subido_lic_oc.vence_oc",
        "3" => "entrega_estimada.nro",
        "4" => "subido_lic_oc.vence_oc",
        "5" => "entidad.nombre",
        "6" => "ensamblador.nombre",
        "7" => "entrega_estimada.fecha_estimada",
		"8" => "cant_orden.nro_orden",
		"9" => "cobranzas.nro_factura",
		"10" =>"lider_iniciales",
		"11" => "total_oc"
    );
    //echo "<br>"; print_r($orden);
    $filtro = array(
        "distrito.nombre" => "Distrito",
        "entidad.nombre" => "Entidad",
        "entrega_estimada.observaciones" => "Observaciones",
        "licitacion.id_moneda" => "Moneda",
        "licitacion.id_licitacion" => "ID de licitación",
        "licitacion.nro_lic_codificado" => "Número de licitación",
		"subido_lic_oc.nro_orden" => "Número de orden",
		"entrega_estimada.responsable" => "Responsable",
		"subido_lic_oc.vence_oc"=>"Fecha de vencimiento",
		"tipo_entidad.nombre"=>"Tipo de organismo"
    );

    if ($download)
    {
        $itemspp = 1000000;
        $page = 0;
    }
    else
    		$itemspp = 50;

    $fecha_hoy = date("Y-m-d 23:59:59",mktime());


$sql_tmp="SELECT licitacion.id_licitacion,entrega_estimada.nro,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado,";
$sql_tmp.=" entidad.nombre as nombre_entidad,distrito.nombre as nombre_distrito,entrega_estimada.responsable,";
$sql_tmp.=" subido_lic_oc.nro_orden as numero,subido_lic_oc.id_subir,subido_lic_oc.vence_oc as vence,garantia_oferta.pedida,subido_lic_oc.pedir_prorroga,
		    garantia_oferta.entregada, entrega_estimada.id_ensamblador,entrega_estimada.fecha_estimada,entrega_estimada.observaciones,
		    entrega_estimada.fecha_inamovible, ensamblador.nombre,cant_orden.nro_orden, tmp_oc.total_oc, 
		    tipo_entidad.nombre as tipo_entidad, tipo_entidad.observaciones as tipo_entidad_desc";
$sql_tmp.=" ,cobranzas.nro_factura,falta_factura,falta_remito,";
$sql_tmp.=" usuarios.apellido||', '||usuarios.nombre as lider_nombre, usuarios.iniciales as lider_iniciales,usuarios.id_usuario";
$sql_tmp.="	, usuarios.login as lider_login";//agregado por Gabriel
$sql_tmp.=" FROM (licitaciones.licitacion LEFT JOIN licitaciones.entidad USING (id_entidad))";
$sql_tmp.=" LEFT JOIN licitaciones.distrito USING (id_distrito)";
$sql_tmp .= " LEFT JOIN licitaciones.garantia_oferta USING (id_licitacion)";
if ($cmd=="actuales") $sql_tmp .= " LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion)";
else $sql_tmp .= " JOIN licitaciones.entrega_estimada USING (id_licitacion)";
$sql_tmp .= " LEFT JOIN ordenes.ensamblador USING (id_ensamblador) ";
$sql_tmp.= " LEFT JOIN (select count(nro_factura) as nro_factura,id_licitacion 
	from licitaciones.cobranzas  group by id_licitacion) as cobranzas USING (id_licitacion) ";
$sql_tmp .= " LEFT JOIN (select licitacion.id_licitacion, count(nro_orden) as nro_orden 
	from (licitaciones.licitacion left join compras.orden_de_compra USING (id_licitacion)) group by licitacion.id_licitacion
	)as cant_orden using (id_licitacion)";
$sql_tmp .= " LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)";
$sql_tmp .= " LEFT JOIN (select count(id_renglones_oc) as falta_remito,id_subir
			  from licitaciones.renglones_oc
				where id_renglones_oc not in (
				select id_renglones_oc from facturacion.remitos
				join facturacion.items_remito using (id_remito)
				where estado <> 'a' and id_renglones_oc is not null
				order by id_renglones_oc)
				group by id_subir) as f_rem using (id_subir)";
$sql_tmp.=" LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider ";
$sql_tmp .= "LEFT JOIN (select count(id_renglones_oc) as falta_factura,id_subir
						from licitaciones.renglones_oc
						where id_renglones_oc not in (
						select id_renglones_oc from facturacion.facturas
						join facturacion.items_factura using (id_factura)
						where estado <> 'a' and id_renglones_oc is not null
						order by id_renglones_oc)
						group by id_subir) as f_fact using (id_subir)";
$sql_tmp .="left join (select id_subir, sum(renglones_oc.precio* renglones_oc.cantidad) as total_oc
				from  licitaciones.subido_lic_oc
					join licitaciones.renglones_oc using(id_subir)
					group by id_subir)as tmp_oc on (subido_lic_oc.id_subir=tmp_oc.id_subir)
			left join licitaciones.tipo_entidad using (id_tipo_entidad)";
if ($cmd=="actuales"){
   $where_tmp = "entrega_estimada.finalizada=0 AND borrada='f'";//estado orden de compra
   $contar="select count(licitacion.id_licitacion)as total
           from licitaciones.licitacion LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion)
           LEFT JOIN licitaciones.entidad USING (id_entidad)
           LEFT JOIN licitaciones.distrito USING (id_distrito)
           LEFT JOIN sistema.usuarios on usuarios.id_usuario=lider 
           LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada) 
           where entrega_estimada.finalizada=0 AND borrada='f'
          ";
   $where_1 = "entrega_estimada.finalizada=0 AND borrada='f'";
   $fin=0;
   }else{
   $where_tmp = "entrega_estimada.finalizada=1 AND borrada='f'";//estado entregada
    $contar="select count(licitacion.id_licitacion)as total
    from licitaciones.licitacion join  licitaciones.entrega_estimada USING (id_licitacion) 
    LEFT JOIN licitaciones.entidad USING (id_entidad)
    LEFT JOIN licitaciones.distrito USING (id_distrito)
    LEFT JOIN sistema.usuarios on usuarios.id_usuario=lider 
    LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada) 
    where entrega_estimada.finalizada=1 AND borrada='f'
    ";
   $where_1 = "entrega_estimada.finalizada=1 AND borrada='f'";//estado entregada
   $fin=1;
   }

  if ($lideres!=""){
	  	$where_tmp.=" and (usuarios.id_usuario='".$lideres."')";
	  	$contar.=" and (usuarios.id_usuario='".$lideres."')";
	  	//$lid=$_POST['lideres'];
	}
if($cmd=="historial"){
 // $contar="buscar";
}

$con_datos="select id_licitacion_prop,titulo,entrega_estimada.id_entrega_estimada
                       from licitaciones.licitacion_presupuesto_new
left join licitaciones.licitacion using (id_licitacion)
left join licitaciones.entrega_estimada USING (id_entrega_estimada) where $where_1 order by (id_entrega_estimada)";
$res_datos=sql($con_datos,"No se pudo recuperar los datos $con_datos") or fin_pagina();

$con_cantidad="select count(entrega_estimada.id_entrega_estimada)as contador,id_entrega_estimada
                       from licitaciones.licitacion_presupuesto_new
left join licitaciones.licitacion using (id_licitacion)
left join licitaciones.entrega_estimada USING (id_entrega_estimada) where $where_1 group by (id_entrega_estimada) order by (id_entrega_estimada)";
$res_cantidad=sql($con_cantidad,"No se pudo recuperar los datos de la cantidad $con_cantidad") or fin_pagina();
while(!$res_cantidad->EOF)
{ 
 $cantidad=$res_cantidad->fields['contador'];
 $iden=$res_cantidad->fields['id_entrega_estimada'];
 $i=1;
 while($cantidad>=$i)
 {
  $ident=$res_datos->fields['id_entrega_estimada'];	
  $idlicprop=$res_datos->fields['id_licitacion_prop'];	
  $tit=$res_datos->fields['titulo'];	
  $datos[$i][$ident]['id']=$idlicprop;	
  $datos[$i][$ident]['titulo']=$tit;
  $res_datos->MoveNext();	
  $i++;
 }
 $contador[$iden]=$cantidad;
 $res_cantidad->MoveNext();	
}



$con_datos1="select comentario,entrega_estimada.id_entrega_estimada from licitaciones.log_cambio_fecha
left join licitaciones.entrega_estimada USING (id_entrega_estimada)
left join licitaciones.licitacion using (id_licitacion)
where $where_1 order by (id_entrega_estimada)";
$res_datos1=sql($con_datos1,"No se pudo recuperar los datos $con_datos") or fin_pagina();

$con_cantidad1="select count(entrega_estimada.id_entrega_estimada)as contador,id_entrega_estimada
                       from licitaciones.log_cambio_fecha
left join licitaciones.entrega_estimada USING (id_entrega_estimada)
left join licitaciones.licitacion using (id_licitacion) where $where_1 group by (id_entrega_estimada) order by (id_entrega_estimada)";
$res_cantidad1=sql($con_cantidad1,"No se pudo recuperar los datos de la cantidad $con_cantidad") or fin_pagina();
while(!$res_cantidad1->EOF)
{ 
 $cantidad=$res_cantidad1->fields['contador'];
 $iden=$res_cantidad1->fields['id_entrega_estimada'];
 $i=1;
 while($cantidad>=$i)
 {
  $ident=$res_datos1->fields['id_entrega_estimada'];	
  $tit=$res_datos1->fields['comentario'];
  if($cantidad==$i)
  {	
  $comen[$ident]=$tit;	
  }
  $res_datos1->MoveNext();	
  $i++;
 }
 $res_cantidad1->MoveNext();	
}

//print_r($_ses_user);
   //$contar="buscar";
if($_POST['keyword'] || $keyword){// en la variable de sesion para keyword hay datos)
	if(($filter!='all')&&($filter!='tipo_entidad.nombre'))
	{
    $contar.=" and $filter ILIKE '%$keyword%'";
	}
	
   else
   $contar="buscar";
}
    echo "<form name='form1' action='".$_SERVER["PHP_SELF"]."' method='post'>";
   // echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center onkeypress='control_boton();' onload='this.focus();'>\n";
    echo "<table cellspacing=2 cellpadding=5 border=0  width=100% align=center>\n";
    echo "<tr>";
    $sel_lider="select id_usuario as id_usu,iniciales as label
		from sistema.usuarios
		where (tipo_lic='L')and(login<>'corapi') order by label";
    $res_sel=sql($sel_lider,"$sel_lider")or fin_pagina();
    ?>
    <td align=center bgcolor=<?=$bgcolor2?>>
    	Líderes:<select name="lideres">
    	<option value=""></option>
    	<?while(!$res_sel->EOF){?>
    	<option value="<?=$res_sel->fields['id_usu']?>" <?if($lideres==$res_sel->fields['id_usu']){?>selected<?}?>><?=$res_sel->fields['label']?></option>
    	<?$res_sel->MoveNext();}?>
    	</select>
    </td>
    <?
//    <td><input type='submit' name='boton' value='Nuevo Seguimiento' style='width:150;cursor:hand'></td>
    echo "<td align=center>\n";

		list($sql,$total_lic,$link_pagina,$up, $sumatoria) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar,array("campo"=>"total_oc", "mask"=>array()));
		//echo "<br>".$sql."<br>"
    $result = sql($sql) or die;
    if ($download)
    {
    	ob_clean();
    	require_once "../../lib/excel/class.writeexcel_workbook.inc.php";
	    require_once "../../lib/excel/class.writeexcel_worksheet.inc.php";
			$fname = tempnam("/tmp", "seguimiento.xls");
			$libro =& new writeexcel_workbook($fname);
			$hoja =& $libro->addworksheet('Presupuestos');
			$formato_enc=& $libro->addformat(array(
                                            bold    => 1,
                                            italic  => 0,
                                            color   => 'white',
                                            size    => 10,
                                            bg_color => 'black',
                                            align => 'center',
                                        ));
			//FILA 1
			$hoja->write('A1', "ID/EST",$formato_enc);
			$hoja->write('B1', "Nro de Seg.",$formato_enc);
			$hoja->write('C1', "Vencimiento",$formato_enc);
			$hoja->write('D1', "Entidad", $formato_enc);
			$hoja->write('E1', "Ensamblador", $formato_enc);
			$hoja->write('F1', "Entrega", $formato_enc);

			$_styles['textcenter']= $libro->addformat(array(border=>1, text_justlast =>1,align => 'center'));
			$_styles['textleft']= $libro->addformat(array(border=>1, text_justlast =>1,align => 'left'));

		$_styles['id']["red"]["style"]=$libro->addformat(array(border=>1, text_justlast =>1,align => 'center',bg_color => 'red'));
		  $_styles['id']["red"]["leyenda"]="Orden de Compra vencida";
		  $_styles['id']["orange"]["style"]= $libro->addformat(array(border=>1, text_justlast =>1,align => 'center',bg_color => 'orange'));
		  $_styles['id']["orange"]["leyenda"]="Orden de Compra vence en 1 a 2 días";
		  $_styles['id']["yellow"]["style"]=$libro->addformat(array(border=>1, text_justlast =>1,align => 'center',bg_color => 'yellow'));
		  $_styles['id']["yellow"]["leyenda"]="Orden de Compra vence en 3 a 5 días";

		  $_styles['id']["white"]["style"]=$libro->addformat(array(border=>1, text_justlast =>1,align => 'center'));

		  $_styles['entidad']['white']['style']= $libro->addformat(array(border=>1, text_justlast =>1,text_wrap=>1, bg_color => 'white',));
			$_styles['entidad']['white']['leyenda']= "No se ha ingresado nada todavia";
			$_styles['entidad']['red']['style']= $libro->addformat(array(border=>1, text_justlast =>1,text_wrap=>1, bg_color => 'red',));
			$_styles['entidad']['red']['leyenda']= "Falta comprar productos";
			$_styles['entidad']['green']['style']= $libro->addformat(array(border=>1, text_justlast =>1,text_wrap=>1, bg_color => 'green',));
			$_styles['entidad']['green']['leyenda']= "Todos los productos estan comprados";
			
			$_styles['fecha_entrega']['red']['style']=$libro->addformat(array(border=>1, text_justlast =>1,align => 'center',bg_color => 'red'));
			$_styles['fecha_entrega']['red']['leyenda']="Fecha vencida";
			$_styles['fecha_entrega']['orange']['style']=$libro->addformat(array(border=>1, text_justlast =>1,align => 'center',bg_color => 'orange'));
			$_styles['fecha_entrega']['orange']['leyenda']="La Fecha es Inamovible Coordinada con el Cliente";

			for ($i=2; !$result->EOF; $i++)
			{

             $ma = substr($result->fields["vence"],5,2);
             $da = substr($result->fields["vence"],8,2);
             $ya = substr($result->fields["vence"],0,4);
		    /*mostramos un color de acuerdo al vencimiento de la orden de compra externa
		     Rojo=orden vencida
		     Naranja=2 dias habiles para vencimiento de la orden
		     Amarillo=5 dias habiles para vencimiento de la orden
		     Verde oscuro=faltan mas de 5 dias para vencimiento de la orden
		    */
		    $fecha_vencimiento=fecha($result->fields['vence']);
		    $fecha_hoy1 = date("d/m/Y",mktime());

		    if ((compara_fechas(fecha_db($fecha_hoy1),fecha_db($fecha_vencimiento)) >= 0))//la fecha actual es mayor a la vencida
		       {
		        $color_state="red";//color rojo
		        $texto_state="La orden de compra esta vencida"; //ya vencio la orden
		       }
		     else
		      {
		     	switch(diferencia_dias_habiles($fecha_hoy1,$fecha_vencimiento))
		                {
		                 case 1:
		                 case 2:
		                        $color_state="orange";//color naranja
		                        $texto_state="Faltan de 1 a 2 dias para el vencimiento de la orden";
		                        break; //1 o 2 dias habiles para vencer
		                 case 3:
		                 case 4:
		                 case 5:
		                         $color_state="yellow";//color amarillo
		                         $texto_state="Faltan de 3 a 5 dias para vencimiento de la orden";
		                         break;//3, 4 o 5 dias habiles para vencer
		                 default:
		                          $color_state="white";//color blanco
		                          $texto_state="";
		                 break;
		                }
		   }

	    $estados=&$_styles['id'];
		  $estados2=&$_styles['entidad'];
		  $estados3=&$_styles['fecha_entrega'];

		  $hoja->write("A$i", $result->fields["id_licitacion"],$estados[$color_state]["style"]);
			$hoja->write("B$i",($result->fields['nro'] > 0?$result->fields['nro']:"0")."/ ".$result->fields['numero'],$_styles['textcenter']);
			$hoja->write("C$i", $da!="" ?"$da/$ma/$ya":"",$_styles['textcenter']);
			if($result->fields['comprado']==1)
				$color_state='red';
			elseif ($result->fields['comprado']==2)
				$color_state='green';
			else
				$color_state='white';
			$hoja->write("D$i", $result->fields["nombre_entidad"],$estados2[$color_state]['style']);
			$hoja->write("E$i", $result->fields["nombre"],$_styles['textleft']);
			if($result->fields["fecha_inamovible"]==1)
				$st=$_styles['fecha_entrega']['orange']['style'];
			elseif (($result->fields["fecha_estimada"])&&(date("Y-m-d") > $result->fields["fecha_estimada"]))
				$st=$_styles['fecha_entrega']['red']['style'];
			else 
				$st=$_styles['textcenter'];
			$hoja->write("F$i", fecha($result->fields["fecha_estimada"]),$st);
			$result->movenext();
			}
			$hoja->set_column('B:B', 22); //nro seguimiento
			$hoja->set_column('C:C', 12); //vencimiento
			$hoja->set_column('D:D', 36); //entidad
			$hoja->set_column('E:E', 22); //ensamblador
			$hoja->set_column('F:F', 12); //entrega

			$i+=2;
		  $hoja->write($i-1,0, "Colores de referencia: Los colores estan reflejados en el campo de ID ",$libro->addformat(array(bold=>1,merge=>1,align=>'left')));
		  $i++;
		  $j=1;

		  foreach ($estados as $clave => $valor)
		  {
		  	$hoja->write("A$i","",$valor["style"]);
		  	$hoja->write("B$i",$valor["leyenda"]);
		  	$i++;
		  	if (3==$j++) break;//para que no ponga el blanco
		  }

		  $hoja->write(++$i,0, "Colores de referencia: Los colores estan reflejados en el campo Entidad ",$libro->addformat(array(bold=>1,merge=>1,align=>'left')));
		  $i+=2;
		  foreach ($estados2 as $clave => $valor)
		  {
		  	$hoja->write("A$i","",$valor["style"]);
		  	$hoja->write("B$i",$valor["leyenda"]);
		  	$i++;
		  }

		  $hoja->write(++$i,0, "Colores de referencia: Los colores estan reflejados en el campo Entrega ",$libro->addformat(array(bold=>1,merge=>1,align=>'left')));
		  $i+=2;
		  foreach ($estados3 as $clave => $valor)
		  {
		  	$hoja->write("A$i","",$valor["style"]);
		  	$hoja->write("B$i",$valor["leyenda"]);
		  	$i++;
		  }

			$libro->close();
			$filename="SegProd(".date("d-m-Y").").xls";
			if (isset($_SERVER["HTTPS"])) {
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: must-revalidate"); // HTTP/1.1
				header("Cache-Control: post-check=0, pre-check=0", false);
			}
			header("Content-Type: application/x-msexcel;");
			//inline para que se cierre automaticamente la ventana despues de bajar el archivo
			header("Content-Disposition: inline; filename=\"$filename\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);
			fclose($fh);
			unlink($fname);
			die;
    }

//    $sql_est = "select id_estado,nombre,color from estado";
//    $result2 = sql($sql_est) or die;
    $estados = array();//se guardan los estados que se mostraran en
    $estados[0]['color']="#FF0000";//color rojo
    $estados[0]['texto']="Orden de Compra vencida";
    $estados[1]['color']="#FF8000";//color naranja
    $estados[1]['texto']="Orden de Compra vence en 1 a 2 días";
    $estados[2]['color']="#FFFF80";//color amarillo
    $estados[2]['texto']="Orden de Compra vence en 3 a 5 días";

    $link11=encode_link('reportes_licitacion.php',array("total"=>$total_lic));
    $onclick1="ventana=window.open(\"$link11\",\"\",\"\")";
    
    $link_costo=encode_link('../licitaciones/listado_costos.php',array(""));
    $link_control=encode_link('control_seguimiento.php',array(""));
    echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
    echo "<input type=button name=reporte value=Reportes onclick='$onclick1' title='diagrama de gantt'>";
    echo "<input type=button name=costos value='Listado de Costos' onclick='window.open(\"$link_costo\")'>";
    echo "<input type=button name=control value='Control' onclick='window.open(\"$link_control\")'>";
    echo "</td>";	
   	echo "<td align=left><a target='_blank' href='".encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1))."'><img src='$html_root/imagenes/excel.gif' border=0 alt='bajar en un excel' /></a></td>";

    echo"</tr></table>";
    echo "</form>";
		if ($download)
			ob_clean();

?>
<script>
//encontrado en inet
var do_show_segu=0;
var do_show_tipo=0;

function show_hide_column(col_no, do_show){
  var stl;
  var tbl  = document.getElementById('tabla_listado');

  if (!do_show){
  	stl = 'block';
  	do_show=1;
  	tbl.rows[1].cells[col_no].childNodes[0].style.display='block';
  	tbl.rows[1].cells[col_no].childNodes[1].style.display='none';
  }else{
  	stl = 'none';
  	do_show=0;
  	tbl.rows[1].cells[col_no].childNodes[0].style.display='none';
  	tbl.rows[1].cells[col_no].childNodes[1].style.display='block';
  }

  for (var row=2; row<tbl.rows.length;row++) {
  	tbl.rows[row].cells[col_no].childNodes[0].style.display=stl;
  }
  return do_show;
}
</script>
<?
 $sumat1=0;
 
 while(!$result->EOF) {
      $sumat1=$sumat1+$result->fields['total_oc'];
      $result->MoveNext();
    }
    $result->moveFirst();
    echo "<table border=0 width=100% cellspacing=1 cellpadding=3 bgcolor=$bgcolor3 align=center id='tabla_listado'>";
    echo "<tr><td colspan=".((permisos_check("inicio", "columna_monto_orden_compra"))?"17":"17")." align=left id=ma>";
    echo "<table width=100%><tr id=ma>";
    echo "<td width=20% align=left nowrap><b>
    Total:</b> ".$total_lic." seguimiento/s de producción.</td><td nowrap>Total de \"Monto OC.\": $ ".formato_money($sumat1)."</td>";
    echo "<td width=10% align=right>$link_pagina</td>";
    echo "</tr></table>";
    echo "</td></tr>";
    echo "<tr>";

       //echo "<td  align=right id=mo></td>\n";

    echo "<td width='8%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
    echo "/<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>";
    echo "<td width='8%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"10","up"=>$up))."'>Lider</a>";
    //<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'> nro. seg
    echo "<td width='8%' align=right id=mo title='Nro. de Seguimiento' style='cursor:hand' onclick='do_show_segu=show_hide_column(2, do_show_segu)'><span id='cabecera' style='display:none'>Nro de Seg</span><span id='foto' style='display:inline'><img src='$html_root/imagenes/right2.gif'></span></td>";
    echo "<td width='5%' align=right id=mo width=14% title='Fecha de Vencimiento de la Orden de Compra'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Venc.</a></td>";
    if (permisos_check("inicio", "columna_monto_orden_compra")) echo("<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"11","up"=>$up))."'>Monto OC</a></td>");
    /*echo "<td align=right id=mo width='14%'> <table border='1'><tr><td title='Fecha de Vencimiento de la Orden de Compra'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Vencimiento</a></td><td>Diego</td></tr></table></td>";*/
    echo "<td width='40%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Entidad</td>";
		echo "<td width='8%' align=right id=mo title='Tipo de Entidad' style='cursor:hand' onclick='do_show_tipo=show_hide_column(6, do_show_tipo)'><span id='cabecera2' style='display:none'>Tipo entidad</span><span id='foto2' style='display:inline'><img src='$html_root/imagenes/right2.gif'></span></td>";
    echo "<td width='7%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))."'>Ensam.</td>";

   
   /* echo "<td align=right id=mo colspan=2>";

    echo "<table cellpadding=0 cellspacing=0 border=0><tr><td lign=right id=mo colspan=2>Producción</td></tr>";
    echo("<tr align=center><td id=mo>OP</td><td id=mo>Est</td><tr></table>");

    echo "</td>";*/
    echo "<td width='5%' align=right id=mo title='Factura'><big>F</big></td>\n";
    echo "<td width='5%' align=right id=mo title='Remitido'><big>R</big></td>\n";
    echo "<td width='5%' align=right id=mo>Presup.</td>\n";
   // echo("<td width='5%' align=right id=mo title='Envío'><big>E</big></td>");
    echo "<td width='14%' align=right id=mo width=14% title='Fecha estimada de entrega'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))."'>Entrega</a></td>\n";
    echo "</tr>\n";
    if ($cmd=='historial') { //busco la fecha de entraga del seguimiento que es la fecha del ultimo renglon entregado
    $sql_fecha="select max (fecha_entrega) as fecha_finalizacion,id_subir from
				(select id_subir,fecha_entrega
 				from licitaciones.log_renglones_oc join licitaciones.renglones_oc using (id_renglones_oc)) as res
				group by id_subir";
    $res_fechas=sql($sql_fecha) or fin_pagina();
    //armo arreglo con id_subir y fecha del ultimo renglon entregado
    $fechas_vencimiento=array();
    while(!$res_fechas->EOF) {
      $fechas_vencimiento[$res_fechas->fields['id_subir']]=$res_fechas->fields['fecha_finalizacion'];
      $res_fechas->MoveNext();
    }
    }
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
           {
            $title_obs=substr($title_obs,0,600);
            $title_obs.="   SIGUE >>>";
           }
        $count_n=str_count_letra("\n",$title_obs);
        //cortamos si el string tiene mas de 12 lineas
        if($count_n>12)
           {
            $cn=0;$j=0;
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
        $ref = encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"detalle","id"=>$result->fields["id_licitacion"], "id_entrega_estimada"=>$result->fields['id_entrega_estimada'], "nro_orden"=>$result->fields["nro_orden"],"nro"=>$result->fields["nro"],"id_subir"=>$result->fields["id_subir"],"nro_orden_cliente"=>$result->fields["numero"],"fin"=>$fin));
        //tr_tag($ref);

    /*mostramos un color de acuerdo al vencimiento de la orden de compra externa
     Rojo=orden vencida
     Naranja=2 dias habiles para vencimiento de la orden
     Amarillo=5 dias habiles para vencimiento de la orden
     Verde oscuro=faltan mas de 5 dias para vencimiento de la orden
    */
    if ($da!=""){
    $fecha_vencimiento=fecha($result->fields['vence']);
    $fecha_hoy1 = date("d/m/Y",mktime());
    if ($cmd=='actuales') {
    if ((compara_fechas(fecha_db($fecha_hoy1),fecha_db($fecha_vencimiento)) >= 0))//la fecha actual es mayor a la vencida
       {
        $color_state="#FF0000";//color rojo
        $texto_state="La orden de compra esta vencida"; //ya vencio la orden
        $prueba="";
       }
     else
      {
        $prueba= " <input name='cambiar' type='submit' value='C' >";
     	switch(diferencia_dias_habiles($fecha_hoy1,$fecha_vencimiento))
                {
                 case 1:
                 case 2:
                        $color_state="#FF8000";//color naranja
                        $texto_state="Faltan de 1 a 2 dias para el vencimiento de la orden";
                        break; //1 o 2 dias habiles para vencer
                 case 3:
                 case 4:
                 case 5:
                         $color_state="#FFFF80";//color amarillo
                         $texto_state="Faltan de 3 a 5 dias para vencimiento de la orden";
                         break;//3, 4 o 5 dias habiles para vencer
                 default:
                          $color_state="#FFFFFF";//color blanco
                          $texto_state="";
                 break;
                }
   }
    }
    else {
       /*si la entrega se realizo despues del vencimiento de oc color rojo
        si la entrega se realizo antes del vencimiento de oc color verde
       */
    $id_subir=$result->fields['id_subir'];
    $fecha_finalizacion=$fechas_vencimiento[$id_subir];

    if ($fecha_finalizacion)
       $comp=compara_fechas(fecha_db($fecha_vencimiento),$fecha_finalizacion);
    else $comp=2;   //si no tengo fecha de finalizacion

    if ($comp ==1 || $comp==0) {
     $color_state="#00CC66";//color verde
     $texto_state="La entrega se realizó en termino";
    }
    elseif($comp ==-1)  {
        $color_state="#FF0000";//color rojo
        $texto_state="La entrega se realizó fuera de termino";
    }
   else {
    $color_state="";
    $texto_state="";
    }

    }
    /****************************************************/
    $comentario="";
    if ($color_state!="#FF0000")  //si no esta vencida
        {
        /*$sql = "select comentario from licitaciones.log_cambio_fecha where id_entrega_estimada=".$result->fields['id_entrega_estimada'];
        $result_comentario = sql($sql) or fin_pagina();
        $result_comentario->MoveLast();*/
        $identr=$result->fields['id_entrega_estimada'];
        $comentario=$comen[$identr];
       }
    $g_title="Responsable: ".$result->fields['responsable']."\nObservaciones:\n".$result->fields['observaciones'];
      ?>

<script>
var ventana=0;
</script>

      <tr <?=$atrib_tr?> title="<?=$g_title?>">
       <?/* quitado por falta de uso 21/12/2005
       <td title="<?="cambiar parametros para el diagrama de Gantt"?>">
         <?
          $link_g=encode_link('parametros_gantt.php',array("id_subir"=>$result->fields['id_subir']));
          $onclick_g="ventana=window.open('$link_g','','');";

          if (!permisos_check('inicio','param_gantt')) $permiso='disabled';
           else $permiso="";?>
             <input name='gantt' type='button' value='G' onclick="<?=$onclick_g?>" <?=$permiso ?>>

        </td>
        */?>
        <a style="color=<?=contraste($color_state,"#000000","#ffffff")?>" href="<?=$ref?>">
       <td width="8%" align="center" style="cursor:hand" bgcolor="<?=$color_state?>" title="<?=$texto_state?>">
          <b> <?= $result->fields["id_licitacion"]?></b>
       </td>
       </a>
       <td width="8%" align="center" style="cursor:hand" title="<?=$result->fields['lider_nombre']?>">
          <b><?=$result->fields['lider_iniciales']?></b>
       </td>
       <a href="<?=$ref?>">
       <td width="8%" align="center" style="cursor:hand">
       	<span id="nro_seg" style="display:none">
         <?
          if ($result->fields['nro']>0) echo $result->fields['nro'];
                                  else echo "0"?>
         <b>/</b>&nbsp;<?=$result->fields['numero']?>
         </span>
       </td>
       </a>
       <td width="5%" <?if($result->fields['id_entrega_estimada']=="") echo" bgcolor='#FF0000'" ?>>
        <table border="0" cellpadding="0" cellspacing="0">
         <tr>
          <a href="<?=$ref?>">
          <td align="left" style="cursor:hand" <?if($result->fields['id_entrega_estimada']=="") echo" bgcolor='#FF0000'" ?> title='<?=$comentario?>'>
           <? if ($da!="") {?> <?=$da?>/<?=$ma?>/<?=$ya?> <?}
              else {?> 01/08/2003 <?}?>

           <?$fecha_completa="$da/$ma/$ya"?>
          </td>
         </tr>
         <tr>
          </a>
          <?
            ($result->fields["pedir_prorroga"])?$color_celda=" bgcolor='66FFFF'":$color_celda="";
          ?>
          <td align="center" <?=$color_celda?>>
           <?
            //if ($color_state!="#FF0000")
             $link=encode_link('cambia_fecha_vencimiento.php',array("fecha"=>$fecha_completa,"id"=>$result->fields["id_entrega_estimada"]));
             $onclick="ventana=window.open('$link')";
             if (permisos_check('inicio','cambiar_fecha')){
           ?>
			 <center><table border="0" cellpadding="0" cellspacing="0"><tr><td style="cursor:hand" onclick="<?=$onclick?>"><b><font color="Blue">C</font></b></td></tr></table></center>
            <?
             }
            ?>
          </td>
         </tr>
        </table>
       </td>
       <a href="<?=$ref?>">
       <?////////////////////////////// gabriel //////////////////////////////////////////////
       if (permisos_check("inicio", "columna_monto_orden_compra")){?>
       	<td nowrap align="right">
       		$ <?=formato_money((($result->fields["total_oc"])?$result->fields["total_oc"]:0))?>
       	</td>
       	<?}////////////////////////////////////////////////////////////////////////////////?>
       <td align="center" style="cursor:hand" <?if($result->fields['comprado']==1) {echo "bgcolor='pink'";} elseif ($result->fields['comprado']==2) echo "bgcolor='green'";?>
        title="<?=$g_title?>">
        <?=html_out($result->fields["nombre_entidad"])?>
       </td>
       <td title="<?=$result->fields["tipo_entidad_desc"]?>">
       	<span id='tipo_entidad' style="display:none">
       		<?=$result->fields["tipo_entidad"]?>
       	</span>
       </td>
       </a>
       <a href="<?=$ref?>">
       <td align="center" style="cursor:hand" <?if($result->fields['id_entrega_estimada']=="") echo " bgcolor='#FF0000' "?>>
        <?
        	if (!(stripos($result->fields["nombre"], "sin producc")===false)) $salida="SP";
        	elseif (!(stripos($result->fields["nombre"], "coradir bs")===false)) $salida="BsAs";
        	elseif (!(stripos($result->fields["nombre"], "pcpower")===false)) $salida="PCP";
        	else $salida=$result->fields["nombre"];
        	echo html_out($salida);
        ?>
       </td>

       </a>
      
      
      
       <a href="<?=$ref?>">

       <td width="10%" align="center" style="cursor:hand" valign="middle" <?if($result->fields['id_entrega_estimada']=="") echo " bgcolor='#FF0000' "?>>
       <?
          if ($result->fields["falta_factura"] !=null || $result->fields["falta_factura"] !="")
                $color='red';
          else $color='green'; ?>
       <table width="100%" cellspacing=1 cellpadding=3  align=center>
         <tr>
           <td height=80% bgcolor='<?=$color?>'>&nbsp;</td>

         <tr>
       </table>
       </td>
       </a>
       <a href="<?=$ref?>">
       <td width="8%" align="center" style="cursor:hand" valign="middle" <?if($result->fields['id_entrega_estimada']=="") echo " bgcolor='#FF0000' "?>>
        <?  if ($result->fields["falta_remito"] !=null || $result->fields["falta_remito"] !="")
                $color='red';
            else $color='green'; ?>
       <table width="100%" cellspacing=1 cellpadding=3  align=center>
         <tr>
           <td height=80% bgcolor='<?=$color?>'>&nbsp;</td>

         <tr>
       </table>
       </td>
       </a>
       <td>
       <table>
       <tr>
        <td>
          	<b><a href="<?=encode_link("../mov_material/producto_lista_material.php", array("ID"=>$result->fields["id_licitacion"], "id_entrega_estimada"=>$result->fields['id_entrega_estimada'], "id_subir"=>$result->fields['id_subir']));?>" target="_blank" style="color:black;font-weight:bold;"><font color="Blue">L</font></a></b>
          </td>
          <?
          $identrega=$result->fields["id_entrega_estimada"];
          $canti=$contador[$identrega];
          $t=1;
			while($canti>=$t){
			$titu=$datos[$t][$identrega]['titulo'];	
			$idprop=$datos[$t][$identrega]['id'];	
?>
          <td title="<?=$titu;?>">
          	<a href="<?=encode_link("../licitaciones/detalle_presupuesto.php",array("id_lic_prop"=>$idprop,"ID"=>$result->fields["id_licitacion"],"id_entrega_estimada"=>$result->fields['id_entrega_estimada'],"id_subir"=>$result->fields['id_subir']));?>" target="_blank" style="color:black;font-weight:bold;">
          	<?="P".$t++;?></a>
          </td>
          	<?
           }
?>
          </tr>
        </table>
			</td>
<?
		      /*$consulta="select * from licitaciones.licitacion join licitaciones_datos_adicionales.envio_renglones using(id_licitacion)
					where id_licitacion=".$result->fields["id_licitacion"];
      	 	$rta_consulta=sql($consulta, "<br>744") or fin_pagina();*/
		      //////////////////////////////////// GABRIEL ////////////////////////////////////////
		      // la consulta arriba comentada solo se usa para asignar un color cuando la
		      // cantidad de filas en el resultSet es mayor que 0, debido a que ese dato puede
		      // ser obtenido con un join (el que tiene comentario en la consulta de la línea 755)
		      // la consulta de arriba puede ser descartada y ahorrar el tiempo de la consulta
		      // multiplicado por la cantidad de registros en el listado multiplicado por el tiempo
		      // de transferencia de los resultados, todo eso menos el tiempo de recarga de la 
		      // subconsulta de conteo de los envíos
		      /////////////////////////////////////////////////////////////////////////////////////
		      ?>
       	</a>
       	<a href="<?=$ref?>">
       	<td nowrap align="right" <?=(($result->fields["fecha_inamovible"]==1)?"bgcolor='#FFCC00'":((($result->fields["fecha_estimada"])&&(date("Y-m-d") > $result->fields["fecha_estimada"]))?"bgcolor='#f34141'":"") )?>>
       	<?=Fecha($result->fields["fecha_estimada"])?>&nbsp;
       	<?if ((permisos_check("inicio", "boton_cambiar_entrega_estimada"))&&(($_ses_user["login"]==$result->fields["lider_login"])||($_ses_user["login"]=="juanmanuel")||($_ses_user["login"]=="marcos")||($_ses_user["login"]=="fernando")||($_ses_user["login"]=="gaudina")||($_ses_user["login"]=="ferni"))){?>
       	<br><center><table border="0" cellpadding="0" cellspacing="0"><tr style='cursor:hand'><td onclick="ventana=window.open('<?=encode_link('editar_hora_sop.php',array("fecha"=>$result->fields["fecha_estimada"], "id_entrega_estimada"=>$result->fields["id_entrega_estimada"], "fecha_inamovible"=>$result->fields["fecha_inamovible"]))?>','','left=40,top=80,width=850,height=250,resizable=1,status=1')"><b><font color="Blue">C</font></b></td></tr></table></center>
       	<?}/*<input type="button" name="cambiar_fecha_entrega" value="C" onclick="ventana=window.open('<?=encode_link('editar_hora_sop.php',array("fecha"=>$result->fields["fecha_estimada"], "id_entrega_estimada"=>$result->fields["id_entrega_estimada"]))?>','','left=40,top=80,width=850,height=250,resizable=1,status=1')">*/?>
       </td>
       </a>
      	</td>
      </tr>
        <?
      
       $result->MoveNext();
     }
     else $result->MoveNext();
     }
      ?>
    </table>
    <br>
    <?
    $link1=encode_link('estado_licitacion.php',array("total"=>$total_lic));
    $link3=encode_link('reportes_produccion.php',array("pagi"=>$cmd));
    $onclick="ventana=window.open(\"$link1\",\"\",\"\")";
    $link="window.open(\"seleccionar_ensamblador.php\",\"\",\"left=40,top=80,width=700,height=300,resizable=1,status=1\")";
    $link2="window.open(\"reportes.php\",\"\",\"left=60,top=80,width=900,height=600,scrollbars=1,resizable=1,status=1\")";
    $link4="ventana=window.open(\"$link3\",\"\",\"\")";
    $q="select count(*) from usuarios_reg where id_usuario={$_ses_user['id']}";
//    $r=sql($q) or fin_pagina();

    if (!permisos_check('inicio','permisos_gantt_ensamblador')) $permiso_ens=' disabled';
           else $permiso_ens="";
    if ($cmd=='actuales') {
   	   echo "<table align=center>
   	             <tr>
   	               <td align=right><input type='button' name='ver_entregas' value='Gantt Entregas' onclick='$onclick' title='diagrama de gantt'></td>";
       echo "      <td align=right><input type='button' name='gantt_ensamblador' value='Gantt Ensamblador' onclick='$link' $permiso_ens ></td>";
	  // if ($_ses_user_login == 'juanmanuel' || $_ses_user_login == 'quique' || $_ses_user_login == 'marcos')
	   if (permisos_check('inicio','permisos_reportes'))
	   {
       echo "      <td align=right><input type='button' name='reportes' value='Reportes Lideres' onclick='$link2'></td>";
      
	   }
	   if (permisos_check('inicio','permisos_reportes_produccion'))
	   {	
        echo "      <td align=right><input type='button' name='reportes' value='Reportes Para Produccion' onclick='$link4'></td>";	   
	   }
	    $link_prod=encode_link("resumen_produccion.php",array());
	    echo " <td align=right><input type='button' name='resumen_produccion' value='Resumen Producción' onclick='window.open(\"$link_prod\")'></td>";
	   
        /*       if ($r->fields['count']==0)
       {
       	$title='Registrarse en el Programa de Ayuda de Producción (SIN REGISTRAR)';
       	$regitrado=0;
       }
       else
       {
       	$title='Registrarse en el Programa de Ayuda de Producción (REGISTRADO)';
       	$regitrado=1;
       }
       echo "<td align=right>";
     	 echo "<form action='{$_SERVER['SCRIPT_NAME']}' method=post>";
       echo "<input type='submit' name='bregistrar' value='Registrarme...' title='$title' ".($regitrado?"disabled":"").">";
       echo "<input type='button' name='bconfig' value='Configuracion' ".(!$regitrado?"disabled":"")." title='Cambiar valores de los avisos de Ayuda de Producción' onclick=\"window.open('op_problemas_status.php','','height=600px, width=800,resizable=1,scrollbars=1')\">";
       echo "</form></td>";
*/       echo "</tr></table><br>";

    echo "<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='98%' cellspacing=0 cellpadding=0>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Los colores estan reflejados en el campo de ID de la orden</b></td></tr>\n";
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
    echo "<tr bordercolor='#FFFFFF'><td colspan=10><b>Toda la fila en rojo significa que la orden es nueva</b></td></tr>\n";
    echo "</table><br>\n";
}
 else {

    echo "<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='98%' cellspacing=0 cellpadding=0>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Los colores estan reflejados en el campo de ID de la orden</b></td></tr>\n";
    echo "<tr>\n";
    echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
    echo "<td width=15 bgcolor='#00CC66' bordercolor='#000000' height=15>&nbsp;</td>\n";
    echo "<td bordercolor='#FFFFFF'> La entrega se realizó en termino</td>\n";
    echo "<td width=15 bgcolor='#FF0000' bordercolor='#000000' height=15>&nbsp;</td>\n";
    echo "<td bordercolor='#FFFFFF'> La entrega se realizó fuera de termino</td>\n";
    echo "</tr>";
    echo "</table></td>";
    echo "<table><tr><td>&nbsp;</td></tr></table>";
      }
    //colores de referencia de los productos ya comprados

    echo "<table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='98%' cellspacing=0 cellpadding=0>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
    echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Los colores estan reflejados en el campo Entidad de la orden</b></td></tr>\n";
    echo "<tr>\n";
    echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
    echo "<td width=15 bgcolor='' bordercolor='#000000' height=15>&nbsp;</td>\n";
    echo "<td bordercolor='#FFFFFF'>No se ha ingresado nada todavia</td>\n";
    echo "</tr></table></td>";
    echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
    echo "<td width=15 bgcolor='pink' bordercolor='#000000' height=15>&nbsp;</td>\n";
    echo "<td bordercolor='#FFFFFF'>Falta comprar productos</td>\n";
    echo "</tr></table></td>";
    echo "<td width=30% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%><tr>";
    echo "<td width=15 bgcolor='green' bordercolor='#000000' height=15>&nbsp;</td>\n";
    echo "<td bordercolor='#FFFFFF'>Todos los productos estan comprados</td>\n";
    echo "</tr></table></td>";
    echo "</tr>\n";
    echo "</table><br>\n";

    ?>
    	<table align="center" border="1" bordercolor='black' bgcolor="White" width="98%" cellpadding="0" cellspacing="0">
    		<tr>
    			<td bordercolor="white"><b>Colores de referencia:</b></td>
    		</tr>
    		<tr>
    			<td bordercolor='white'>
    				<table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>
    					<tr>
       					<td colspan="4"><b>Columna OP de campo "Producci&oacute;n"</b></td>
       				</tr>
       				<tr>
    						<td width=15 bgcolor='Red' bordercolor='#000000' height=15>&nbsp;</td>
			    			<td>La licitaci&oacute;n no tiene alguna orden de producci&oacute;n asignada</td>
    						<td width=15 bgcolor='Green' bordercolor='#000000' height=15>&nbsp;</td>
    						<td>La licitaci&oacute;n tiene al menos una orden de producci&oacute;n asignada</td>
    					</tr>
				  		<tr>
    						<td colspan="4"><b>Columna "Env&iacute;o"</b></td>
    					</tr>
			    		<tr>
    						<td width=15 bgcolor='Red' bordercolor='#000000' height=15>&nbsp;</td>
			    			<td>La licitaci&oacute;n no tiene alg&uacute;n env&iacute;o asignado</td>
    						<td width=15 bgcolor='Green' bordercolor='#000000' height=15>&nbsp;</td>
    						<td>La licitaci&oacute;n tiene al menos un env&iacute;o asignado</td>
    					</tr>
    					
    					<tr>
       						<td colspan="4"><b>Columna "Entrega"</b></td>
       					</tr>
       					<tr>
    						<td width=15 bgcolor='#FFCC00' bordercolor='#000000' height=15>&nbsp;</td>
			    			<td>La Fecha es Inamovible Coordinada con el Cliente</td>
    					</tr>
    					
    					
    					<tr>
       						<td colspan="4"><b>Columna "Vence."</b></td>
       					</tr>
       					<tr>
    						<td width=15 bgcolor='66FFFF' bordercolor='#000000' height=15>&nbsp;</td>
			    			<td>Pedir Prorroga</td>
    					</tr>
    					
       					
    				</table>
    			</td>
    		</tr>
    	</table>
<?
}

$id = $parametros["id"] or $id=$_POST["id"];
if ($id == "") {
	echo $html_header;
	cargar_calendario();
 	listado_licitaciones();
}
else {
  require("seguimiento_orden.php");
   }

echo "<script src='funciones.js'></script>";
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

fin_pagina();
?>
