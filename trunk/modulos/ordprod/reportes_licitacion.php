<?PHP
/*
Autor: ???
Modificado por:
$Author: enrique $
$Revision: 1.4 $
$Date: 2006/05/11 18:59:00 $
*/
require_once("../personal/gutils.php");
require_once("../../config.php");
require_once("../ord_compra/fns.php");
echo $html_header;
$var_sesion=array(
                  "lideres"=>""
                  );
       
$download=$parametros['download'];
$cmd1=$parametros['cmd1'] or $cmd1=$_POST["cmd1"];
variables_form_busqueda("seg_ord1",$var_sesion);
if ($cmd == "") {
	     $cmd="actuales";
	     $_ses_seg_ord['cmd']=$cmd;
	     $_ses_seg_ord['lideres']="";
       }


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
}
    /*global $bgcolor3,$cmd,$cmd1,$proxima,$datos_barra,$lideres;
    global $bgcolor2,$itemspp,$db,$parametros,$barra,$html_header,$html_root;
    global $keyword,$filter,$page,$sort,$estado,$ver_papelera,$atrib_tr,$download,$_ses_user;*/
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
    generar_barra_nav($datos_barra);
    $orden = array(
        "default" => "4",
        "default_up" => "$or",
        "1" => "licitacion.id_licitacion",
        "3" => "entrega_estimada.nro",
        "4" => "subido_lic_oc.vence_oc",
        "5" => "entidad.nombre",
        "6" => "ensamblador.nombre",
        "7" => "entrega_estimada.fecha_estimada",
	"8" => "cant_orden.nro_orden",
	"10" =>"lider_iniciales",
	"11" => "total_oc"
    );
    $filtro = array(
        "distrito.nombre" => "Distrito",
        "entidad.nombre" => "Entidad",
        "entrega_estimada.observaciones" => "Observaciones",
        "licitacion.id_moneda" => "Moneda",
        "licitacion.id_licitacion" => "ID de licitación",
        "licitacion.nro_lic_codificado" => "Número de licitación",
		"entrega_estimada.responsable" => "Responsable",
		"subido_lic_oc.vence_oc"=>"Fecha de vencimiento"
    );

    if ($download)
    {
        $itemspp = 1000000;
        $page = 0;
    }
    else
    		$itemspp = 50;

    $fecha_hoy = date("Y-m-d 23:59:59",mktime());


$sql_tmp="SELECT licitacion.id_licitacion,c.ncon,cc.ncon1,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado,";
$sql_tmp.=" entidad.nombre as nombre_entidad,distrito.nombre as nombre_distrito,entrega_estimada.responsable,";
$sql_tmp.=" subido_lic_oc.nro_orden as numero,subido_lic_oc.id_subir,subido_lic_oc.vence_oc as vence,entrega_estimada.id_ensamblador,entrega_estimada.fecha_estimada,entrega_estimada.observaciones,
		cant_orden.nro_orden, tmp_oc.total_oc, tipo_entidad.observaciones as tipo_entidad_desc";
$sql_tmp.=" ,comprados,recibidos,subido_lic_oc.fecha_notificacion as notificacion,";
$sql_tmp.=" usuarios.apellido||', '||usuarios.nombre as lider_nombre, usuarios.iniciales as lider_iniciales,usuarios.id_usuario";
$sql_tmp.="	, usuarios.login as lider_login, recepcion.ordenes,creacion.fecha_crea,fecha_apertura";//agregado por Gabriel
$sql_tmp.=" FROM (licitaciones.licitacion LEFT JOIN licitaciones.entidad USING (id_entidad))";
$sql_tmp.=" LEFT JOIN licitaciones.distrito USING (id_distrito)";
if ($cmd=="actuales") $sql_tmp .= " LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion)";
else $sql_tmp .= " JOIN licitaciones.entrega_estimada USING (id_licitacion)";
$sql_tmp .= "LEFT JOIN (select fecha_crea,id_entrega_estimada 
from licitaciones_datos_adicionales.contacto_seguimiento where estado=1  group by id_entrega_estimada,fecha_crea) as creacion USING (id_entrega_estimada)  ";
$sql_tmp .= " LEFT JOIN (select licitacion.id_licitacion, count(nro_orden) as nro_orden 
	from (licitaciones.licitacion left join compras.orden_de_compra USING (id_licitacion)) group by licitacion.id_licitacion
	)as cant_orden using (id_licitacion)";
$sql_tmp .= " LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)";

$sql_tmp.=" LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider ";

$sql_tmp .="LEFT JOIN (
				select id_entrega_estimada, sum(fila.cantidad) as comprados,
				case when sum(recibido_entregado.cantidad) is null then 0 else sum(recibido_entregado.cantidad) end as recibidos,
		licitaciones.unir_texto(nro_orden||' ('||proveedor.razon_social||'), ') as ordenes
			from compras.orden_de_compra
		    join compras.fila using (nro_orden)
				left join compras.recibido_entregado using (id_fila)
				left join general.proveedor using (id_proveedor)
			where id_entrega_estimada is not null
				and estado <> 'n' and razon_social not ilike '%Stock%' and (ent_rec=1 or ent_rec is null) 
				and (es_agregado is null or es_agregado<>1)
			group by id_entrega_estimada) as recepcion using (id_entrega_estimada)
			left join (select id_subir, sum(renglones_oc.precio* renglones_oc.cantidad) as total_oc
				from  licitaciones.subido_lic_oc
					join licitaciones.renglones_oc using(id_subir)
					group by id_subir)as tmp_oc on (subido_lic_oc.id_subir=tmp_oc.id_subir)
			left join licitaciones.tipo_entidad using (id_tipo_entidad)
			left join (select count (nombre)as ncon,id_entrega_estimada from licitaciones_datos_adicionales.contacto_seguimiento where estado=1 
            group by id_entrega_estimada)as c using (id_entrega_estimada) 
            left join (select count (nombre)as ncon1,id_entrega_estimada from licitaciones_datos_adicionales.contacto_seguimiento where estado=2 
            group by id_entrega_estimada)as cc using (id_entrega_estimada) ";
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

if($_POST['keyword'] || $keyword){// en la variable de sesion para keyword hay datos)
	if(($filter!='all')&&($filter!='tipo_entidad.nombre'))
	{
    $contar.=" and $filter ILIKE '%$keyword%'";
	}
	
   else
   $contar="buscar";
}
    echo "<form name='form1' action='".$_SERVER["PHP_SELF"]."' method='post'>";
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
    echo "<td align=center>\n";
	list($sql,$total_lic,$link_pagina,$up, $sumatoria) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar,array("campo"=>"total_oc", "mask"=>array()));
    $result = sql($sql) or die;
    $estados = array();//se guardan los estados que se mostraran en
    $estados[0]['color']="#FF0000";//color rojo
    $estados[0]['texto']="Orden de Compra vencida";
    $estados[1]['color']="#FF8000";//color naranja
    $estados[1]['texto']="Orden de Compra vence en 1 a 2 días";
    $estados[2]['color']="#FFFF80";//color amarillo
    $estados[2]['texto']="Orden de Compra vence en 3 a 5 días";
    $link11=encode_link('reportes_licitacion.php',array("total"=>$total_lic));
    $onclick1="ventana=window.open(\"$link11\",\"\",\"\")";
    echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'></td>";
    echo"</tr></table>";
    echo "</form>";
		if ($download)
			ob_clean();
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
    echo "<td width='8%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
    echo "/<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>";
    echo "<td width='8%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"10","up"=>$up))."'>Lider</a>"; 
    echo "<td width='5%' align=right id=mo width=14% title='Fecha de Vencimiento de la Orden de Compra'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Venc.</a></td>";
    if (permisos_check("inicio", "columna_monto_orden_compra")) echo("<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"11","up"=>$up))."'>Monto OC</a></td>");
    echo "<td width='40%' align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Entidad</td>";
    echo "<td align=right id=mo>Cont/Cliente</td>\n";
    echo "<td align=right id=mo>Recepción</td>\n";
    echo "<td align=right id=mo colspan=2>";
    echo "<table cellpadding=0 cellspacing=0 border=0><tr><td lign=right id=mo colspan=2>Producción</td></tr>";
    echo("<tr align=center><td id=mo>OP</td><td id=mo>Est</td><tr></table>");
    echo "</td>";
    echo("<td width='5%' align=right id=mo title='Envío'><big>E</big></td>");
    echo("<td width='5%' align=right id=mo title='Contacto Entrega Informatica'>Con Inf</td>");
    echo("<td width='5%' align=right id=mo title='Contacto Presentacion de la Factura'>Con Fac</td>");
    echo "</tr>\n";
    if ($cmd=='historial') { //busco la fecha de entraga del seguimiento que es la fecha del ultimo renglon entregado
    $sql_fecha="select max (fecha_entrega) as fecha_finalizacion,id_subir from
				(select id_subir,fecha_entrega
 				from licitaciones.log_renglones_oc join licitaciones.renglones_oc using (id_renglones_oc)) as res
				group by id_subir";
    $res_fechas=sql($sql_fecha) or fin_pagina();
    $fechas_vencimiento=array();
    while(!$res_fechas->EOF) {
      $fechas_vencimiento[$res_fechas->fields['id_subir']]=$res_fechas->fields['fecha_finalizacion'];
      $res_fechas->MoveNext();
    }
    }
    while (!$result->EOF) {
        $title_obs=$result->fields["observaciones"];
        $long_title=strlen($title_obs);
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
        $link3= encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$result->fields["id_licitacion"], "id_entrega_estimada"=>$result->fields['id_entrega_estimada'], "nro_orden"=>$result->fields["nro_orden"],"nro"=>$result->fields["nro"],"id_subir"=>$result->fields["id_subir"],"nro_orden_cliente"=>$result->fields["numero"],"fin"=>$fin));
        //$ref="ventana=window.open(\"$link3\",\"\",\"\")";
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
        $identr=$result->fields['id_entrega_estimada'];
        $comentario=$comen[$identr];
       }
    $g_title="Responsable: ".$result->fields['responsable']."\nObservaciones:\n".$result->fields['observaciones'];
      ?>

<script>
var ventana=0;
</script>
      <tr <?=$atrib_tr?> title="<?=$g_title?>" <?=atrib_tr()?> onclick="window.open('<?=$link3?>')">
      <td width="8%" align="center" style="cursor:hand" bgcolor="<?=$color_state?>" title="<?=$texto_state?>">
      <b> <?= $result->fields["id_licitacion"]?></b>
      </td>
      <td width="8%" align="center" style="cursor:hand" title="<?=$result->fields['lider_nombre']?>">
      <b><?=$result->fields['lider_iniciales']?></b>
      </td>
      <td width="5%" <?if($result->fields['id_entrega_estimada']=="") echo" bgcolor='#FF0000'" ?>>
        <table border="0" cellpadding="0" cellspacing="0">
         <tr>
          <td align="left" style="cursor:hand" <?if($result->fields['id_entrega_estimada']=="") echo" bgcolor='#FF0000'" ?> title='<?=$comentario?>'>
           <? if ($da!="") {?> <?=$da?>/<?=$ma?>/<?=$ya?> <?}
              else {?> 01/08/2003 <?}?>

           <?$fecha_completa="$da/$ma/$ya"?>
          </td>
         </tr>
        </table>
       </td>
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
<?
$fecha_crea=Fecha($result->fields['fecha_crea']);
$fecha_aper=Fecha($result->fields['notificacion']);
if(($fecha_crea!="")&&($fecha_aper!=""))
{
$dias=diferencia_dias_habiles($fecha_aper,$fecha_crea);
?>
<td align="center" style="cursor:hand" title="Tiempo transcurrido desde la llegada de la orden de compra hasta el contacto con el cliente">
<?=$dias?>
</td>
<?
}
else 
{
$dias="S/C";
?>
<td align="center" style="cursor:hand" title="S/C No hay contacto con el cliente">
<?=$dias?>
</td>
<?}
	if ($result->fields['comprados'] > 0) {
		$tam1=number_format((($result->fields['recibidos']*100)/$result->fields['comprados']),2,".","");
  } else $tam1=0;

  if ($tam1==0) $tam2=100;
  elseif ($tam1==100) $tam2=0;
  else $tam2=(100-$tam1);

  if ($tam1<100) $g_title2=ereg_replace(", ", "\n", substr($result->fields["ordenes"], 0, strlen($result->fields["ordenes"])-2));
?>
      <td width="10%" align="center" style="cursor:hand" valign="middle" <?if($result->fields['id_entrega_estimada']=="") echo " bgcolor='#FF0000' "?> title="<?=$g_title2?>">
	      <table width="100%" cellspacing=1 cellpadding=3  align=center>
  	     	<tr>
    	     	<? if ($tam1 > 0) {?> <td height=80% width="<?=$tam1?> %" bgcolor='green'>&nbsp;</td> <?}?>
          	<? if ($tam2 > 0) {?> <td height=80% bgcolor='white' width="<?=$tam2?> %">&nbsp;</td> <?}?>
       		<tr>
      	</table>
      </td>
  <?
       if($result->fields["nombre"]!="SIN PRODUCCION") {
       	$consulta="select nro_orden, id_renglon, estado, estado_bsas,
       		 case when cant_envios is null then 0 else cant_envios end
       		from licitaciones.licitacion 
       			join ordenes.orden_de_produccion using(id_licitacion)
       			left join (
							select id_licitacion, count(id_envio_renglones) as cant_envios
							from licitaciones_datos_adicionales.envio_renglones
							where id_licitacion=".$result->fields["id_licitacion"]."
							group by id_licitacion
						)as tmp0 using (id_licitacion)
					where id_licitacion=".$result->fields["id_licitacion"];
       	$rta_consulta=sql($consulta, "<br>627") or fin_pagina();
				if ($rta_consulta->recordCount()>0) $color_op="green";
				else $color_op="red";
       ?>
			<td width="10%">
				<table width="100%" cellspacing="1" cellpadding="3" align="center">
					<tr>
       			<td align="center" bgcolor="<?=$color_op?>" height="80%">&nbsp;</td>
       		</tr>
       	</table>
      </td>
       
       <?
          /*$sql_produc="select estado_bsas from ordenes.orden_de_produccion
                       where orden_de_produccion.estado <>'AN' and id_licitacion=".$result->fields["id_licitacion"]."
                       group by estado_bsas";
          $res_produc=sql($sql_produc) or fin_pagina();*/
          /////////////////////////////////////////// GABRIEL ////////////////////////////////////////
          // la consulta arriba comentada trae datos que ya trae lan anterior a ésta (línea 752) 
          // solo que repetidos, entonces para optimizar la página aprovecho la capacidad de cálculo
          // del servidor y filtro en el resultSet de la consulta anterior los datos que necesito
          // para la que sigue, notar también que los datos de la consulta comentada solo son 
          // requeridos en 
          //		if (count($res_produc)==0 )
          //			...
          //			$color_prod='Yellow';
         	//     }
         	// de esta forma se reduce el tiempo de cargado de la página en (cantidad de registros del
         	// listado por tiempo de ejecución de la consulta por tiempo de transferencia de resultados)
         	// menos tiempo de procesamiento del arreglo "res_produc"
          ////////////////////////////////////////////////////////////////////////////////////////////
          if ($rta_consulta) $rta_consulta->moveFirst();
          $res_produc=array();
          $k=0;
          for ($i=0; $i<$rta_consulta->recordCount(); $i++){
          	if (($rta_consulta->fields["estado"]!="AN")){
          		$j=0;
          		$lim=count($res_produc);
          		for (; (($j<$lim)&&($rta_consulta->fields["estado_bsas"]!=$res_produc[$j])); $j++);
          		if ($j==$lim) $res_produc[$k++]=$rta_consulta->fields["estado_bsas"];
          	}
          }
          ////////////////////////////////////////////////////////////////////////////////////////////
          if (count($res_produc)==0 ) $color_prod="white";  //no hay ordenes asociadas
          elseif (count($res_produc) == 1 && $res_produc[0]=="") $color_prod='red';//todas pendientes
          elseif (count($res_produc) == 1 && $res_produc[0]==2) $color_prod='green';//todas en historial
          elseif (count($res_produc) > 1 && $res_produc[0]==1) {
            for($i=0; $i<count($res_produc); $i++){
            	if ($res_produc[$i]==1){ 
            			$color_prod='orange';    //al menos una en producion
              }else  $color_prod='red';
            }
         	}elseif ($res_produc[$i]==2) {
         		$color_prod='green';
         	}else{
         	  $color_prod='Yellow';
         	}
        ?>
       
       <td width="10%" align="center" style="cursor:hand" valign="middle" <?if($result->fields['id_entrega_estimada']=="") echo " bgcolor='#FF0000' "?>>

       <?$color='green';?>
       <table width="100%" cellspacing=1 cellpadding=3  align=center>
         <tr><td height=80% bgcolor='<?=$color_prod?>'>&nbsp;</td></tr>
       </table>
       </td>
      
       <?
        }
       else
       {
       ?>
        <td width="10%">
		<table width="100%" cellspacing="1" cellpadding="3" align="center">
		<tr>
        <td height=80% width="<?=$tam1?> %" bgcolor='green'>&nbsp;</td>
        </tr>
        </table>
       	</td>
       <td width="10%">
		<table width="100%" cellspacing="1" cellpadding="3" align="center">
		<tr>
        <td height=80% width="<?=$tam1?> %" bgcolor='green'>&nbsp;</td>
        </tr>
        </table>
       	</td>
       <?
       }   
       if ($rta_consulta) $rta_consulta->moveFirst();
					if ($rta_consulta->fields["cant_envios"]>0) $color_op="green";
					else $color_op="red";
     ?>			
     <td width="10%">
					<table width="100%" cellspacing="1" cellpadding="3" align="center">
						<tr>
       				<td align="center" bgcolor="<?=$color_op?>" height="80%">&nbsp;</td>
       			</tr>
       		</table>
       	</td>
       	<td width="10%">
		<table width="100%" cellspacing="1" cellpadding="3" align="center">
		<tr>
       	<td height="80%" <?if($result->fields['ncon']!=""){?>bgcolor="Green"<?}else{?>bgcolor="Red"<?}?>>&nbsp;
       	</td>
       	</tr>
       	</table>
       	</td>
       	<td width="10%">
       	<table width="100%" cellspacing="1" cellpadding="3" align="center">
		<tr>
       	<td height="80%" <?if($result->fields['ncon1']!=""){?>bgcolor="Green"<?}else{?>bgcolor="Red"<?}?>>&nbsp;
       	</td>
       	</tr>
       	</table>
       	</td>
        </tr>
        <?
        $result->MoveNext();
     }
     else $result->MoveNext();
     }
      ?>
    </table>
    <table align="center" width="80%">
    <tr>
    <td align="center"><input type="button" name="cerrar" value="Cerrar" onclick="window.close();"></td>
    </tr>
    </table>
    <br>
    <?
$id = $parametros["id"] or $id=$_POST["id"];
fin_pagina();
?>
