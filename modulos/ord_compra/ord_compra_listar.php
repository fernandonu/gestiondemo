<?
/*
Autor: GACZ

MODIFICADA POR
$Author: fernando $
$Revision: 1.290 $
$Date: 2007/01/05 16:26:52 $
*/

require_once("../../config.php");
require_once("fns.php");
include("../modulo_clientes/funciones.php");

//ver dependencia con el archivo pdf.php
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs

//DATOS DEL REMITO se extraen del arreglo POST
extract($_POST,EXTR_SKIP);

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

/*if ($_POST['select_cantidad'])
   {
    $itemspp=$_POST['select_cantidad'];
    phpss_svars_set("_ses_ord_compra_itemspp",$itemspp);
    // $_ses_ord_compra_itemspp=$itemspp;
    }
    else
	    $itemspp=$_ses_ord_compra_itemspp or $itemspp=100;
*/
//se cambian los valores de las variables de sesion cuando viene de la pagina
//de licitaciones para que tome el id de lic y el estado todas
if ($parametros["volver_lic"]) {
	phpss_svars_set("_ses_ord_compra",$parametros);
}
//////
variables_form_busqueda("ord_compra",array("sin_proveedor_stock"=>""));
if ($_POST["buscar"])
        $page=0;


if ($cmd=="")
      {
       $cmd='a';
       $_ses_ord_compra["cmd"]=$cmd;
       phpss_svars_set("_ses_ord_compra",$_ses_ord_compra);
       $page=0;
      }

if($_POST["form_busqueda"] && $_POST["sin_proveedor_stock"]=="" && $sin_proveedor_stock!="")
{
 $sin_proveedor_stock=-1;
 $_ses_ord_compra["sin_proveedor_stock"]=$sin_proveedor_stock;
 phpss_svars_set("_ses_ord_compra",$_ses_ord_compra);
}

//para mandar un mail al usuario corriente en caso de que se presione el boton resumen
//el mail se manda con un resumen de todos los pagos desde la ultima ves que se presiono el boton
//por el usuario hasta la fecha actual.
 if (permisos_check("inicio","permiso_resumen"))
               $tiene_permiso="";
            else
               $tiene_permiso="disabled";

//if($_POST['Resumen']=='Resumen') {
if($_POST['resumen_pagos']=='Resumen') {

          $fecha_actual=date("Y-m-d H:m:s",mktime());
          $datos_usuario="SELECT * from resumen WHERE usuario='".$_ses_user['login']."'";
          $datos=sql($datos_usuario) or die($db->ErrorMsg());
          $fecha_desde=$datos->fields['fecha'];
          $query="SELECT ordenes_pagos.*,orden_de_compra.comentario_pagos, pago_orden.nro_orden,moneda.simbolo from ordenes_pagos join pago_orden using (id_pago) join orden_de_compra using (nro_orden) join moneda using (id_moneda) WHERE ordenes_pagos.fecha < '$fecha_actual' AND ordenes_pagos.fecha > '$fecha_desde'";
          $resultados=sql($query)or die($db->ErrorMsg()."<br>".$query);
          $filas_encontradas=$resultados->RecordCount();
          $text="";

          for($i=0;$i<$filas_encontradas;$i++)
              {

              $link_mail=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"ord_compra","extra"=>array("nro_orden"=>$resultados->fields['nro_orden'] )));
              $text.="Número de Orden: ";
              $text.="<a href='$link_mail'>".$resultados->fields['nro_orden']."</a>";
              $text.="<br>";
              $text.="Pago: ";
              //controles para mostrar la informacion del pago dependiendo de la forma de pago
              if(!is_null($resultados->fields['id_ingreso_egreso'])) {
              	    $flag="efectivo";
                    $id_ingreso_egreso=$resultados->fields['id_ingreso_egreso'];
                    $query_dist="select id_distrito from  ingreso_egreso join caja using (id_caja) where id_ingreso_egreso=$id_ingreso_egreso";
                 	$res_dist=sql($query_dist) or fin_pagina();
              	    if ($res_dist->fields['id_distrito']==1)
              	          $link_efectivo=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"egresos_bsas","extra"=>array("id_ingreso_egreso"=>$id_ingreso_egreso,"pagina"=>"egreso")));
              	     elseif ($res_dist->fields['id_distrito']==2)  $link_efectivo=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"egresos_sl","extra"=>array("id_ingreso_egreso"=>$id_ingreso_egreso,"pagina"=>"egreso")));
                    $link_efectivo=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"egresos_bsas","extra"=>array("id_ingreso_egreso"=>$resultados->fields['id_ingreso_egreso'],"pagina"=>"egreso")));
                    $datos_pago="SELECT * FROM ingreso_egreso join tipo_egreso using(id_tipo_egreso) WHERE id_ingreso_egreso=$id_ingreso_egreso";
                    $resultados_pagos=sql($datos_pago)or die($db->ErrorMsg()."<br>".$datos_pago);
                    $text.="<a href='$link_efectivo'>Efectivo</a><br>";
                    $text.="Item: ".$resultados_pagos->fields['item'];
                    $text.="<br>";
                    $text.="Tipo de egreso: ".$resultados_pagos->fields['nombre'];
                    $text.="<br>";
                    }

            if(!is_null($resultados->fields['iddébito'])){
                   $flag="transferencia";
                   $id_debito=$resultados->fields['iddébito'];
                   $datos_pago="SELECT * FROM débitos JOIN tipo_banco using(idbanco) JOIN tipo_débito using(idtipodéb) WHERE iddébito=$id_debito";
                   $resultados_pagos=sql($datos_pago)or die($db->ErrorMsg()."<br>".$datos_pago);
                   $link_debito=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"bancos_movi_debitos","extra"=>array("id_debito"=>$id_debito,"pagina"=>"mail")));
                   $text.="<a href='$link_debito'>Transferencia</a><br>";
                   $text.="Banco: ".$resultados_pagos->fields['nombrebanco'];
                   $text.="<br>";
                   $text.="Tipo Debito: ".$resultados_pagos->fields['tipodébito']."<br>";
                   $text.="Monto debito: $".$resultados_pagos->fields['importedéb']."<br>";
            }

          if(!is_null($resultados->fields['idbanco'])){
          	       $flag="banco";
                   $id_banco=$resultados->fields['idbanco'];
                   $nro_ch=$resultados->fields['númeroch'];
                   $datos_pago="SELECT * FROM tipo_banco WHERE idbanco=$id_banco";
                   $resultados_pagos=sql($datos_pago)or die($db->ErrorMsg()."<br>".$datos_pago);
                   $datos_cheque="SELECT fechavtoch,importech,fechadébch FROM cheques WHERE idbanco=$id_banco and númeroch=$nro_ch";
                   $resultados_cheques=sql($datos_cheque)or die($db->ErrorMsg()."<br>".$datos_cheque);
//Es para link a numero de cheque
     if ($resultados_cheques->fields['fechadébch']!=NULL)
     $link_cheque=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"bancos_movi_chdeb","extra"=>array("Modificar_Cheque_Numero"=>$nro_ch,"pagina"=>"mail","banco"=>$id_banco)));
     else   $link_cheque=encode_link("https://".$_SERVER['SERVER_NAME']."/index.php",array("menu" =>"bancos_movi_chpen","extra"=>array("Modificar_Cheque_Numero"=>$nro_ch,"pagina"=>"mail","banco"=>$id_banco)));
                   $text.= "Banco";
                   $text.= "<br>";
                   $text.= "Nombre del Banco: ".$resultados_pagos->fields['nombrebanco'];
                   $text.= "<br>";
                   $text.= "Número de Cheque: ";
                   $text.="<a href='$link_cheque'>".$nro_ch."</a>";
                   $text.="<br>";
                   $text.= "Fecha Vencimiento Cheque: ".Fecha($resultados_cheques->fields['fechavtoch']);
                   $text.="<br>";
                   $text.= "Monto cheque: $ ".formato_money($resultados_cheques->fields['importech']);
                   $text.="<br>";
                    }
     $text.="";
     $text.="Monto del Pago: ";
     $text.=$resultados->fields['simbolo']." ".formato_money($resultados->fields['monto']);
     $text.="<br>";
     $text.="Usuario: ";
     $text.=$resultados->fields['usuario'];
     $text.="<br>";
     $text.="Fecha: ";
     $text.=Fecha($resultados->fields['fecha']);
     $text.="<br>";
     $text.="Comentarios: ";
     $text.=$resultados->fields['comentario_pagos'];
     $text.="<br>";
     $text.="----------------------------------------------------------";
     $text.="<br><br>\n";
     $resultados->MoveNext();
 }
  //******************************************/
  $mail_header="";
    $mail_header .= "MIME-Version: 1.0";
    $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
    $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
    $mail_header .="\nTo: juanmanuel@pcpower.com.ar";

    if ($_ses_user['login']=='corapi')
         $mail_header .="\nBcc: corapi@coradir.com.ar";
         elseif ($_ses_user['login']=='noelia')
         $mail_header .="\nBcc: noelia@pcpower.com.ar";
         elseif ($_ses_user['login']=='mascioni')
         $mail_header .="\nBcc: carlos@pcpower.com.ar";

    $mail_header .= "\nContent-Type: text/html";
    $mail_header .= "\nContent-Transfer-Encoding: 8bit";
    $mail_header .= "\n\n" . $text."\n";
	$mail_header .= "\n\n" . firma_coradir_mail()."\n";


	//echo $mailtext;

/*********************************************************************/
  //echo "<br>"."El encabezado del mail es: ".$mail_header;
  // End
 if(mail("","Resumen de pagos","",$mail_header))
      echo "<b><center> Se ha enviado un mail a la casilla de correo de '".$_ses_user['mail']."' con el resumen de los pagos desde '".Fecha($fecha_desde)."' hasta '".Fecha($fecha_actual)."'</b></center>";
      else
      echo "<b><center> No se ha podido enviar el mail con el resumen de pagos </center></b>";

 //modifico la fecha del usuario con la fecha de hoy:
 $actualizar="UPDATE resumen SET fecha='".substr($fecha_actual,0,10)."' WHERE usuario='".$_ses_user['login']."'";
// $actualizar="UPDATE resumen SET fecha='$fecha_actual' WHERE usuario='juanmanuel'";
 $actualizar_fecha=sql($actualizar) or die($db->ErrorMsg()."<br>".$actualizar);

}

echo $html_header;
?>
<script language="JavaScript" src="../../lib/NumberFormat150.js"></script>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}
function link_sobre(src) {
    src.id='me';
}
function link_bajo(src) {
    src.id='mi';
}
var contador=0;

//esta funcion sirve para habilitar los mail
function habilitar_mail(valor)
{
 if (valor.checked)
             contador++;
             else
             contador--;
 if (contador>=1)
        window.document.all.boton.disabled=0;
        else
         window.document.all.boton.disabled=1;
}//fin function

function calcular_monto(check,monto){
//esta funcion me calcula los montos de las ordenes de compra
//que selecciono de los check
//total_auxiliar es la que lleva realmente las cuentas
//suma totales es solo para presentear el format valido


if (check.checked==true){
         document.all.total_auxiliar.value=parseFloat(document.all.total_auxiliar.value) + parseFloat(check.value);
         document.all.total_auxiliar_pagos.value=parseFloat(document.all.total_auxiliar_pagos.value) + parseFloat(monto);
         }
         else{
         document.all.total_auxiliar.value=parseFloat(document.all.total_auxiliar.value) - parseFloat(check.value);
         document.all.total_auxiliar_pagos.value=parseFloat(document.all.total_auxiliar_pagos.value) - parseFloat(monto);
       }

var aux=cantidad_chekeados();


if ((document.all.total_auxiliar.value<=0.01)&&(document.all.total_auxiliar.value>=-0.01))
                                          {
                                           document.all.suma_totales.value=0;
                                           document.all.suma_totales_pagos.value=0;
                                           }
                                          else{

                                          var numero=new  NumberFormat();
                                          numero.setNumber(document.all.total_auxiliar.value);
                                          numero.setSeparators(true, numero.PERIOD);
                                          numero.setCurrencyPosition(numero.LEFT_OUTSIDE)
                                          numero.setCurrencyPrefix(document.all.moneda_suma.value+ ' ');
                                          document.all.suma_totales.value=numero.toFormatted();
                                          //para los montos de los pagos
                                          numero.setNumber(document.all.total_auxiliar_pagos.value);
                                          numero.setSeparators(true, numero.PERIOD);
                                          numero.setCurrencyPosition(numero.LEFT_OUTSIDE)
                                          numero.setCurrencyPrefix(document.all.moneda_suma.value+ ' ');
                                          document.all.suma_totales_pagos.value=numero.toFormatted();

                                          //esto lo hago para los montos
                                          }

}// fin de la function


function cantidad_chekeados(){
var cantidad=0;

for(i=0;i<document.all.chk_monto.length;i++){
   if (document.all.chk_monto[i].checked) cantidad++;
}

return cantidad;
}

</script>

<form name="form" method="post" action="<?php echo encode_link($_SERVER['SCRIPT_NAME'],array()); ?>">
<?
$datos_barra = array(
                    array(
                        "descripcion"    => "Pendientes",
                        "cmd"            => "p"
                        ),
                    array(
                        "descripcion"    => "Por Autorizar",
                        "cmd"            => "u"
                        ),
                    array(
                        "descripcion"    => "Autorizadas",
                        "cmd"            => "a"
                        ),
                    array(
                        "descripcion"    => "Enviadas",
                        "cmd"            => "e"
                        ),
                    array(
                        "descripcion"    => "Pagadas",
                        "cmd"            => "d"
                        ),
                    array(
                        "descripcion"    => "Todas",
                        "cmd"            => "todas"
                        )
);

generar_barra_nav($datos_barra);
?>
<!-- BUSQUEDAS -->

<table align="center" border="0">
<tr>
<?
   if ($cmd=='todas' || $cmd=='a')
    {
?>
<input type='hidden' name='resumen_pagos' value='0'>
<td align="left"><input type="button" name="Resumen" value="Resumen"  title="Envie por mail el resumen de las ordenes hasta la fecha" <?=$tiene_permiso?> onclick="document.all.resumen_pagos.value='Resumen';document.form.submit(); "></td>

<td>&nbsp;&nbsp;&nbsp;</td>
<? }?>
<td>
<?
if (permisos_check("inicio","ord_compra_materiales")) {
?>
<input type="button" name="Materiales" value="Materiales" title="Ver los materiales comprados" onclick="mat=window.open('ord_compra_materiales.php','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=790,height=590');">
&nbsp;&nbsp;&nbsp;
<?
}
/*******************************************************************************
SECCION DE BUSQUEDA SECCION DE BUSQUEDA SECCION DE BUSQUEDA SECCION DE BUSQUEDA
********************************************************************************/
$campos="distinct(o.nro_orden),o.estado,o.desc_prod,o.fecha_entrega,o.id_licitacion,o.cliente,p.razon_social,simbolo,o.id_moneda,total_orden,total_sum, licitacion.es_presupuesto,
         o.nrocaso, o.flag_honorario, o.flag_stock, o.orden_prod,o.estado_proveedor,estado.nombre as est_lic,opl.id_licitacion as orden_prod_lic,o.internacional,ord_pago ";
if($cmd=="p")
{$campos.=",log_ord.nombre||log_ord.apellido as nombre_creador,log_ord.fecha_creacion";
}
if($cmd=='p' || $cmd=='u')
 $campos.=",plantilla_pagos.descripcion as titulo_pago";
$query="SELECT $campos FROM orden_de_compra o left join
proveedor p using(id_proveedor) left join
(select sum(cantidad*(case when estado='n' then 0 else precio_unitario end)) as total_sum,sum(cantidad*precio_unitario) as total_orden,nro_orden from fila join orden_de_compra using (nro_orden) group by nro_orden) costo using(nro_orden) left join
moneda on(moneda.id_moneda=o.id_moneda) left join licitacion using(id_licitacion)
left join estado using (id_estado)
left join (select nro_orden as n_op,id_licitacion from orden_de_produccion) as opl on opl.n_op=o.orden_prod
";

if ($cmd!='todas')
{	if ($cmd=="p")
	{$where="(estado='p' OR estado='r')";
	 //$contar="select count (*) from orden_de_compra where estado='p' or estado='r'";
	}
	elseif ($cmd=='d')
	{ $where="(estado='d' OR estado='g' OR estado='t')";
	  //$contar="select count (*) from orden_de_compra where estado='g' or estado='t' or estado='d'";
	}
	elseif($cmd=='e')
	{$where="(estado='d' OR estado='e')";
	 //$contar="select count (*) from orden_de_compra where estado='d' or estado='e'";
	}
	else
	{$where="estado='$cmd'";
	 //$contar="select count (*) from orden_de_compra where estado='$cmd'";
	}

	if($sin_proveedor_stock==1)
	 $where.=" and razon_social not ilike '%Stock%'";

	if($cmd=='p' || $cmd=='u')
	{$query.=" left join plantilla_pagos using (id_plantilla_pagos)";
	}

}
//else
 //$contar="select count (*) from orden_de_compra";
	//$where="(estado='g' OR estado='t' OR estado='e')";

//Las ordenes de pago se muestran en la lista de Ordenes para Autorizar
if ($cmd!="u")
{
	if ($where)
		$where.=" AND ord_pago is null ";
	else
		$where="ord_pago is null";
}
$contar = 'buscar';
if (!(strpos($cmd,"Fecha")==false))
    	$keyword=Fecha_db($keyword);

if ($cmd!='p')
{$order=0;
 $campo_orden=3;
}
else
{$order=0;
 $campo_orden=1;
}

$orden= array
(
		"default" => "$campo_orden",
                "default_up"=>"$order",
		"1" => "o.nro_orden",
		"2" => "o.estado",
		"3" => "o.fecha_entrega",
		"4" => "o.id_licitacion",
		"5" => "o.cliente",
		"6" => "p.razon_social",
                "7" => "total_orden"

);
$filtro_array= array
(
		"o.nro_orden"=>"Nº de Orden",
		"o.nro_factura"=>"Nº de Factura",
		"o.id_licitacion"=>"ID Licitacion",
		"o.lugar_entrega"=>"Lugar de entrega",
		"o.cliente"=>"Cliente",
		"o.notas"=>"Comentarios",
		"p.razon_social"=> "Proveedor",
	//	"fila.descripcion_prod"=>"Productos"
);
//*Para que pueda buscar en los productos
if ($cmd=='fila.descripcion_prod' || $cmd=='all')
{
	 $query.=" left join fila using(nro_orden)";
}

$orden_total=$parametros['sort'] or $orden_total=$_ses_ord_compra['sort'] or $orden_total=$campo_orden;
//este arrego es para que la consulta que se agrega 
//para los estados $cmd=='todas' || $cmd=='e' || $cmd=='d
//quede ordenada
$orden1= array
(
		"default" => "fecha_entrega",
		"1" => "nro_orden",
		"2" => "estado",
		"3" => "fecha_entrega",
		"4" => "id_licitacion",
		"5" => "cliente",
		"6" => "razon_social",
        "7" => "total_orden"

);

if ($up==1) $asc='ASC';
  else $asc='DESC';
$orden_total_por=$orden1[$orden_total];

if($cmd=="p")
{$query.=" left join (select nombre,apellido,fecha as fecha_creacion,nro_orden from usuarios join log_ordenes on user_login=login where tipo_log='de creacion') as log_ord using(nro_orden)";
}
/**/
$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "total_sum",
 		"mask" => array ("\$","U\$S")
);

/*
//Las Ordenes de Servicio Tecnico no se listan mas en este listado, sino en el Listado de Ordenes de Servicio Tecnico
if($where!="")
 $where.=" and";
$where.=" ( (nrocaso = '' or nrocaso isnull) or ((nrocaso<>'' or nrocaso is not null) and razon_social<> 'Stock Serv. Tec. Bs. As.')) ";
*/

if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
     $contar="buscar";
$itemspp=50;
list($query,$total,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro_array,$link_tmp,$where,$contar,$sumas);
//$datos_orden=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
//echo $query;
//echo "Total= ".$total;
//query para saber si le faltan recibir productos a una OC
//el usar el join despues del limit y el offset que se usa en el form_busqueda
//reduce notablemente  el tiempo de ejecucion de la consulta
//esta consulta agrega 2-4 segundos mas al tiempo de la consulta principal (total ordenes 330)
if ($cmd=='todas' || $cmd=='e' || $cmd=='d')
	{
		$query= "select * from ($query) as t1 ";
		$query.=" left join
		(select nro_orden,sum(recibidos) as recibidos_oc,sum(entregados) as entregados_oc,sum(comprados) as comprados_oc,case when sum(comprados)-sum(recibidos)=0 then -1
									  else sum(comprados)-sum(recibidos)
								end as falta_recibir,
                          case when sum(comprados)-sum(entregados)=0 then -1
									  else sum(comprados)-sum(entregados)
								end as falta_entregar
		from
			(select nro_orden,id_fila,sum(cantidad) as comprados
			   from
			   compras.fila
		      where (es_agregado isnull or es_agregado<>1)
			group by id_fila,nro_orden) f left join
			(select id_fila,sum(cantidad) as recibidos
			   from
			   compras.recibido_entregado
		       where ent_rec=1
		       group by id_fila) r using(id_fila)
		       left join
			(select id_fila,sum(cantidad) as entregados
			   from
			   compras.recibido_entregado
		       where ent_rec=0
		       group by id_fila) e using(id_fila)
		group by nro_orden) t2 using(nro_orden)
		order by $orden_total_por $asc";
	}
/* */
//echo "query: $query";

$datos_orden=sql($query) or die($db->ErrorMsg()."<br>".$query);

//die($query);

if($total==0 && $keyword)
	$msg_nulo="<b><center>No se encontró ninguna orden de compra que concuerde con lo buscado</center></b>";


?>
<input type="submit" name="buscar" value="Buscar">&nbsp;&nbsp;

<?
if (($cmd=='d'||$cmd=='e' ||$cmd=='todas')and(permisos_check('inicio','permiso_boton_reporte_oc_pagadas'))){?>
	<input type="button" name="listar_ordenes" value="Listar Ordenes" onclick="window.open('reporte_oc_pagadas.php')">&nbsp;
<?}?>

</td>
<?
 if (($cmd=='d')||($cmd=='e') || ($cmd=='t')|| ($cmd=='todas'))
     {
 ?>
<td bgcolor="<?=$bgcolor3 ?>" colspan="5">
<table width='100%'>
 <tr>
  <td>
   <font color='red' size='1'>
   Montos Ordenes
   </font>
  </td>
  <td>
   <input type="text" style="text-align:right" name="suma_totales" size="15" value="0" readonly >
  </td>
 </tr>
 <? if (($cmd=='d') || ($cmd=='todas')) { ?>
 <tr>
  <td>
  <font color='red' size='1'>
  Montos Pagados
  </font>
  </td>
  <td>
   <input type="text" style="text-align:right" name="suma_totales_pagos" size="15" value="0" readonly >
  </td>
 </tr>
 <? } else { echo "<input type='hidden' name='suma_totales_pagos'>"; }
 ?>
</table>
</td>
<?} ?>
</tr>
<?if (permisos_check("inicio","busq_avanzada"))
{
?>
<tr>
 <?$link=encode_link("busq_avanzada_armo.php",array()); ?>
 <td align="left" colspan="4"><input type="button" name="busqueda_avanzada" value="Busqueda Avanzada"  title="Realiza Busquedas Avanzadas" onclick="window.open('<?=$link?>')"></td>
 <td align="right"><input type="checkbox" name="sin_proveedor_stock" value="1" <?if($sin_proveedor_stock==1)echo "checked";?> onclick="document.all.buscar.click()"><b> No Mostrar OC con Proveedores Stock</b></td>
</tr>
<?
}
?>
</table>
<?

   if ($cmd=='todas' || $cmd=='a')
    {
?>
<?
 }
 if ($msg)
   echo "<div align='center'><font color='red' size='2'><b>$msg</b></font></div>";
   unset($msg);
   if($msg_nulo)
    echo "<div align='center'><br>$msg_nulo</div>";
// Calcular los totales por moneda
//Esta consulta es la que me determinada la montos totales
//por pagina
//ahora trar unicamente las ordenes que se muestran por pantalla

//formo una cadena con los nro de ordenes que hay en la pagina
/*$cantidad_ordenes=$datos_orden->RecordCount();
if ((($cmd=='g')||($cmd=='e')||($cmd=='todas')||($cmd=='d'))&& $cantidad_ordenes>0)
{

$where="where orden_de_compra.estado<>'n' and(";
for ($i=0;$i<$cantidad_ordenes;$i++){
     $orden_busqueda=$datos_orden->fields["nro_orden"];
     if ($i==$cantidad_ordenes-1)
                $where.=" orden_de_compra.nro_orden=$orden_busqueda )";
                else
                $where.=" orden_de_compra.nro_orden=$orden_busqueda or ";
     $datos_orden->MoveNext();
}//
$where.="  GROUP BY simbolo ";
$datos_orden->Move(0);
$sql="select simbolo,sum(cantidad * precio_unitario) as precio_productos";
$sql.=" from compras.fila join compras.orden_de_compra using(nro_orden)";
$sql.=" join licitaciones.moneda using (id_moneda) ".$where;
    $result =$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
    $total_moneda = array();
    while (!$result->EOF) {
        $total_moneda[$result->fields["simbolo"]] = formato_money($result->fields["precio_productos"]);
        $result->MoveNext();
        }

}*/
/* para cortar los comentarios de arriba*/
 ?>
<input type="hidden" name="moneda_suma" value=789>
<input type="hidden" name="total_auxiliar" value=0>
<input type="hidden" name="total_auxiliar_pagos" value=0>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr bgcolor="#c0c6c9">
		<td id=ma style="text-align:left" >
        <b>Total - Ordenes de Compra
		  <? if ($cmd) echo ": ".$total ?>
		  </b></font>
		</td>
<?
  if (($cmd=="d")|| ($cmd=='e') || ($cmd=='t')|| ($cmd=='todas'))
          {
         // foreach ($total_moneda as $total_simbolo => $total_monto) {
         // echo "<td align=right><b>Total $total_simbolo&nbsp;$total_monto</b></td>";
          echo "<td align=right><b>Total $suma</b></td>";
         // }
}
        if($cmd=='e' && permisos_check("inicio","permiso_boton_montos_oc"))
        {$link_montos_oc=encode_link("montos_en_oc.php",array("estado"=>'e'));
?>
        <td>
         &nbsp;<input type="button" name="montos_oc" value="$" title="Montos de OC" onclick="window.open('<?=$link_montos_oc?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=190,top=160,width=600,height=380')">
        </td>
        <?
        }//de if($cmd='e')
        elseif($cmd=='d' && permisos_check("inicio","permiso_boton_montos_oc"))
        {/*$link_montos_oc=encode_link("montos_en_oc.php",array("estado"=>'g'));
?>
        <td>
         &nbsp;<input type="button" name="montos_oc" value="$" title="Montos de OC" onclick="window.open('<?=$link_montos_oc?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=180,top=180,width=450,height=200')">
        </td>
        <?
        */
        }
        ?>
		<td id=ma style="text-align:right">
		<?= $link_pagina ?>
		</td>
	</tr>
  </table>
	<table border="0" cellspacing="2" cellpadding="0" width="100%">
	  <tr bgcolor="#006699" align="center">
<?
	//checkbox para enviar mail
	//autorizadas
	if ($cmd=='a' )
	{
?>
    <td width="3%">&nbsp;</td>
<?
	}
if (($cmd=='d')||($cmd=='e') || ($cmd=='t')|| ($cmd=='todas')) { //es para los chekes
?>
    <td width="3%">&nbsp;</td>
<?
} //fin del if de los checked
?>
    <td id=mo width="5%" height="13"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up)) ?>'>Nro Orden</a></td>
    <td id=mo width="5%" height="13">TIPO</td>
<? if ($cmd=='todas')
   {
?>
		<td id=mo width="10%">Estado</td>
<?
}
?>
		<td id=mo width="1%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"4","up"=>$up)) ?>'>Id Licitación</a></td>
		<td id=mo width="10%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>Fecha entrega</a></td>
		<?if($cmd=="e" || $cmd=="d" || $cmd=="todas")
		  {?>
		   <td id=mo width="1%" title="Recibidos/Entregados (totales de cada OC)">R/E</td>
		  <?
		  }
		  ?>
		<td id=mo width="<?if($cmd=='u')echo "25"; else echo "35"?>%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"5","up"=>$up)) ?>'>Cliente</a></td>
		<td id=mo width="<?if($cmd=='u')echo "20"; else echo "25"?>%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up)) ?>'>Proveedor</a></td>
<?
 if (($cmd=='p') || ($cmd=='u') || ($cmd=='d') || ($cmd=='e') || ($cmd=='t')||($cmd=='todas'))
  {
?>
        <td id=mo width="10%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"7","up"=>$up)) ?>'> Monto </a></td>

<?
  }
  if($cmd=='p' || $cmd=='u')
  {
  ?>
    <td id=mo width="13%">Forma de Pago</td>
   <?
  }
       //imagen PDF
		//autorizadas,enviadas,terminadas,por terminar,todas
		if ($cmd=='a' || $cmd=='e' || $cmd=='t' || $cmd=='d' || $cmd=='todas')
		{
?>
		<td width="3%">&nbsp; </td>
<?
		}
?>
	  </tr>

<?
  $contador=0;
  while (!$datos_orden->EOF )
  {
	$monto=($datos_orden->fields['total_orden'])?number_format($datos_orden->fields['total_orden'],2,".",""):0;
  	if (($cmd=='e')||($cmd=='d')|| ($cmd=='t')|| ($cmd=='todas')) {
                        $monto_pagado=number_format(monto_pagado($datos_orden->fields['nro_orden']),2,".","");
                        }

    $desc_orden=$datos_orden->fields['desc_prod'];

    if ($datos_orden->fields['estado']=="r")
    { ?>
    <tr <?echo atrib_tr("#FFFFC0");?> style="cursor:hand">
    <?
	}
	elseif ($datos_orden->fields['estado']=="n")
     {
        ?>
        <tr <?echo atrib_tr("#FF8080");?> style="cursor:hand">
        <?
	 }
	else {
?>
  <tr <?echo atrib_tr();?>  style="cursor:hand">
<?
	}
	//checkbox para enviar mail
	//autorizadas
	if ($cmd=='a')
	{
?>
    <td align="center">

<?
if (($datos_orden->fields['estado']=='a')&&($cmd=='a'))
	{
	$contador++;
?>
	  <input type="checkbox" name="chk_<?=$contador ?>" value="<?=$datos_orden->fields['nro_orden'] ?>" onclick="habilitar_mail(this)">
	  <input type="hidden" name="idorden[<?php echo $contador; ?>]" value="<?php echo $datos_orden->fields['nro_orden']; ?>">

<?
	}
?>

		</td>
<?
   }
if (($cmd=='d')||($cmd=='e') || ($cmd=='t')|| ($cmd=='todas'))
{ // de los checked
  switch($datos_orden->fields['estado']){
  case 'g':
  case 'd':
  case 'e':
 ?>
  <td align="center">
  <?
  //el check solo se genera si la orden no esta en un pago multiple
  $ordenes_atadas=PM_ordenes($datos_orden->fields['nro_orden']);
  $pm=sizeof($ordenes_atadas);
  if($pm>1)
  {//si la orden es parte de un pago multiple, se toma como
   //que fue totalmente pagada, para el sumador automaticamente
   $monto_pagado=$monto;
   $title_pm="Pago Múltiple de las ordenes:";
  	for($i=0;$i<$pm;$i++)
  	 $title_pm.=" ".$ordenes_atadas[$i];
  }
  ?>
  <input type="checkbox" name="chk_monto" value="<?=$monto ?>"
  onclick=
   "if ((document.all.moneda_suma.value==789) || (document.all.moneda_suma.value=='<?=$datos_orden->fields['simbolo'];?>')){
      document.all.moneda_suma.value='<?=$datos_orden->fields['simbolo'];?>';
      calcular_monto(this,<?=$monto_pagado?>);
      if (cantidad_chekeados()==0) document.all.moneda_suma.value=789;
      }
      else {
            this.checked=false;
            alert('Error: Esta intentando sumar 2 monedas distintas');
            }
      ">
  </td>

<?
  break;
  default:
?>
    <!-- Si no  coincide ninguno coloco una celda vacia -->
    <td>&nbsp;

    </td>
 <?
 }//del swtich
}//de los cheked
?>
		<a href="<? echo encode_link($datos_orden->fields['ord_pago']?"../ord_pago/ord_pago.php":"ord_compra.php",array("nro_orden"=>$datos_orden->fields['nro_orden'])); ?>">
<!-- ESTO COMENTADO MOSTRABA LA CELDA DE COLOR VERDE SI ESTABA PAGADA-->
<!--		<td height="18" align="center" bgcolor=<? if ($datos_orden->fields['monto']) echo "green title='Esta orden esta pagada'"; ?> ><font color="<?  if ($datos_orden->fields['monto']) echo "white"; else echo "#006699" ?>"><b><? echo $datos_orden->fields['nro_orden'] ?></b></font></td> -->

<?

if (($cmd=="todas")&&($datos_orden->fields['estado']=='g'))
                                         $color="bgcolor='#C0FFC0'";
                                         else
                                         $color="";

if($cmd=="p")
{
 $title_pendiente="OC Creada por: ".$datos_orden->fields['nombre_creador']."\nFecha Creación: ".fecha($datos_orden->fields['fecha_creacion']);
}
?>
<td height="18"  <?=$color;?> align="center" title="<?=$title_pendiente?>"><font color="#006699"><b><? echo $datos_orden->fields['nro_orden'] ?></b></font></td>
<?$tipo="Otro";
  $titulo="Orden no Asociada";
  if ($datos_orden->fields['id_licitacion']!='' && $datos_orden->fields['es_presupuesto']==1)
     {$tipo="Pres";
      $titulo="Presupuesto";
     }
  elseif ($datos_orden->fields['id_licitacion']!='')
         {$tipo="Lic";
          $titulo="Licitacion";
         }
  if ($datos_orden->fields['nrocaso']!='')
     {$tipo="ServT";
      $titulo="Servicio Técnico\nNro Caso: ".$datos_orden->fields['nrocaso'];
     }
  if ($datos_orden->fields['flag_honorario']==1)
     {$tipo="HST";
      $titulo="Honorario Servicio Técnico";
     }
  if ($datos_orden->fields['flag_stock']==1)
     {$tipo="Stock";
      $titulo="Stock Coradir";
     }
  if ($datos_orden->fields['orden_prod']!='')
     {$tipo="RMA";
      $titulo="RMA de Producción";
     }
  if($datos_orden->fields['internacional']==1)
  {$tipo="INT";
   $titulo="Orden de Compra Internacional";
  }
  if ($datos_orden->fields['ord_pago']!="")
  {
  	$tipo="PAGO";
  	$titulo="Orden de Pago";
  }

?>
<td height="18"  title="<?=$titulo?>" align="center" ><font color="#006699"><b><?=$tipo?></b></font></td>
<?

if ($cmd=='todas') {
?>
		<td align="center"><font color="#006699"><b>
		  <? switch ($datos_orden->fields['estado'])
			  {
				case 'p': echo "Pendiente";break;
				case 't': echo "Terminada";break;
				case 'a': echo "Autorizada";break;
				case 's': echo "Sin Terminar";break;
				case 'r': echo "Rechazada";break;
				case 'g': //totalmente pagada
//				case 'd': echo "Por Terminar";break; ANTES ERA ESTE ESTADO
				case 'd': echo "Pagada";break; //parcialmente pagada
				case 'e': echo "Enviada";break;
				case 'n': echo "Anulada";break;
				case 'u': echo "Por Autorizar";break;

    		  }
?>  </b></font></td> <?
         }//del if de $cmd=="todas"
		?>
        <?
        $id_licitacion_listado="";
		if($tipo=="RMA")
		  $id_licitacion_listado=$datos_orden->fields['orden_prod_lic'];
		elseif($tipo=="Lic" || $tipo=="Pres")
          $id_licitacion_listado=$datos_orden->fields['id_licitacion'];

        if ($datos_orden->fields['estado']=='e' || $datos_orden->fields['estado']=='d' && $cmd=='g') {?>
		<td align="center" <?if ($datos_orden->fields['est_lic'] == 'Entregada') {?> bgcolor="Red" <?}?>>
		<?
		if ($datos_orden->fields['est_lic'] == 'Entregada') {?><font color="Black"><? } else {?>
		<font color="#006699"><?}?><b><? echo $id_licitacion_listado ?></b></font></td>
		<?} else {?>
		<td align="center"><font color="#006699"><b><? echo $id_licitacion_listado ?></b></font></td>
		<?}?>
		<td align="center" <?
		if($datos_orden->fields['estado']!='n')
		{?>
		 bgcolor="<?
		 //si el proveedor es un stock, tomamos en cuenta los productos que faltan entregar
		 if(substr_count($datos_orden->fields['razon_social'],"Stock")>0)
		 {$falta=$datos_orden->fields['falta_entregar'];
		  $title_falta="Se entregaron todos los productos";
		 }
		 //sino, como antes tomamos en cuenta los productos recibidos
		 else
		 {$falta=$datos_orden->fields['falta_recibir'];
		  $title_falta="Se recibieron todos los productos";
		 }


		 if ($falta>0)
		 	echo 'yellow" title="Falta recibir: '.$falta.' unidades"';
		 elseif ($falta<0)
	  		echo '#00C100" title="'.$title_falta.'"';

		 ?>"
         <?
		 }
         ?>
         >
		 <font color="#006699"><b><? echo Fecha($datos_orden->fields['fecha_entrega']) ?></b></font>
		</td>
		<?if($datos_orden->fields['recibidos_oc'])
		   $rec=$datos_orden->fields['recibidos_oc'];
		  else
		   $rec=0;
		  if($datos_orden->fields['entregados_oc'])
		   $ent=$datos_orden->fields['entregados_oc'];
		  else
		   $ent=0;
		  $comprados=$datos_orden->fields['comprados_oc'];
		  $title_comprados="Recibidos/Entregados\nTotal Comprados: ".$comprados;
		  if($cmd=="e" || $cmd=="d" || $cmd=="todas")
		  {
		   if($ent==$comprados)
		    $todos_entregados="bgcolor='#00C100'";
		   else
		    $todos_entregados="";

		   $link_recepciones=encode_link("ord_compra_recepcion.php",array('nro_orden'=>$datos_orden->fields['nro_orden'],'es_stock'=>$datos_orden->fields['es_stock'],'mostrar_dolar'=>$mostrar_dolar,"tipo_lic"=>$tipo_lic_text));
		   ?>
		    </a><a href="<?=$link_recepciones?>"><td title="<?=$title_comprados?>" <?=$todos_entregados?>><font color="#006699"><b><?=$rec?>/<?=$ent?></font></b></td></a>
		    <a href="<? echo encode_link($datos_orden->fields['ord_pago']?"../ord_pago/ord_pago.php":"ord_compra.php",array("nro_orden"=>$datos_orden->fields['nro_orden'])); ?>">
		  <?
		  }
		  ?>
		<td align="center" title='<?=$desc_orden?>'><font color="#006699"><b><?= $datos_orden->fields['cliente'] ?></b></font></td>
<?
// Control de proveedores para Graciela Tedeschi

if(permisos_check("inicio","control_compras_proveedores"))
{
$color_prov ="";
switch ($datos_orden->fields['estado_proveedor'])
{
  case 1:$color_prov = "#9999cc";break;
  case 2:$color_prov = "pink";break;
  case 3:$color_prov = "yellow";break;
}
}
?>
 <td align="center" title="<?=$datos_orden->fields['simbolo']." ".$monto ?>" <?=($color_prov!="")?"bgcolor='$color_prov';":"";?>><font color='#006699'><b><? echo $datos_orden->fields['razon_social'] ?></b></font></td>
		</a>
<?
    if (($cmd=='p') ||($cmd=='u') || ($cmd=='d')||($cmd=='e') || ($cmd=='t')||($cmd=='todas'))
    {
?>
   <td align="right" <?
                     if ($datos_orden->fields['estado']=='d' && $pm<=1)
                         echo "bgcolor=#FFFFC0 title='Pagado: $monto_pagado'";
                     elseif($pm>1)
                         echo "bgcolor=#FFA042 title='$title_pm'";
                      ?>  >
   <font color="#006699"><b>
   <?=$datos_orden->fields['simbolo']."  $monto";?>
   </b></td>

<?
   }
   if($cmd=='p' || $cmd=='u')
   {if(strlen($datos_orden->fields['titulo_pago'])>20)
    {$titulo_pago=substr($datos_orden->fields['titulo_pago'],0,17);
     $titulo_pago.="...";
     $title_titulo_pago=$datos_orden->fields['titulo_pago'];
    }
    else
    {$titulo_pago=$datos_orden->fields['titulo_pago'];
     $title_titulo_pago="";
    }
   	echo "<td title='$title_titulo_pago'><font color='#006699'><b>$titulo_pago</font></td>";
   }
	//imagen PDF
   //autorizadas,enviadas,terminadas,por terminar,todas
		if ($cmd=='a' || $cmd=='e' || $cmd=='t' || $cmd=='d' || $cmd=='todas')
		{

?>

		<td align="center">
<?
		switch ($datos_orden->fields['estado'])
		{
				case 'a':
				case 'e':
       			case 't':
				case 'd':
                case 'g':
				//echo "<A target='_blank' href='./PDF/orden_de_compra_".$datos_orden->fields['nro_orden'].".pdf"."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
                $link=encode_link("ord_compra_pdf.php", array("nro_orden"=>$datos_orden->fields['nro_orden']));
		        echo "<A target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
			    break;
		}
?>
		</td>
<? 	}
		echo   "</tr>";
?>
<?
		$datos_orden->MoveNext();
  }
?>
<input type="hidden" name="cantidad_check" value="<?php echo $contador; ?>">
   </table>

<?
   if ($cmd=='todas' || $cmd=='a')
	{
?>
<br>
<table align="center">
<tr>
<?
if ($cmd=='a'){
?>
<td align=center><input type="submit" name="boton" value="Enviar mail" disabled title="Envie por mail las ordenes seleccionadas" onclick="form.action='ord_compra_mail.php'"> </td>
<?
}///del if
?>
</tr>
<?
	if ($parametros["volver_lic"]) {
		//$ref = encode_link($html_root."/index.php",array("menu" => "licitaciones_view","extra" => array("cmd1"=>"detalle","ID"=>$parametros["volver_lic"])));
		//echo "<tr><td align=center colspan=2><br><input type=button name=volver style='width:320;' value='Volver a los detalles de la licitacion' onClick=\"parent.document.location='$ref';\"></td></tr>\n";
		echo "<tr><td align=center colspan=2><br><input type=button name=volver style='width:320;' value='Cerrar' onClick=window.close();></td></tr>\n";
	}

	}

?>
</table>
<? if ($cmd=='todas' || $cmd=='e' || $cmd=='d')
	{
?>
</b>
<br><a name="leyenda">
<table border=1 bordercolor= '#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
 <tr><td colspan="7" bordercolor ='#FFFFFF'> <strong>Colores de referencia para Fecha de entrega</strong> </td></tr>
 <tr>
  <td width='15%' bordercolor ='#FFFFFF'>&nbsp; </td>
  <td width='25%' align='right' bordercolor ='#FFFFFF'>Falta recibir productos</b></td>
  <td width='5%' bgcolor='yellow'>&nbsp; </td>
  <td  width='25%' align='right' bordercolor ='#FFFFFF'>Productos recibidos</b></td>
  <td width='5%' bgcolor="#00C100">&nbsp; </td>
  <td  width='20%' align='right' bordercolor ='#FFFFFF'></td>
  <td width='5%' bordercolor ='#FFFFFF' >&nbsp; </td>
 </tr>
 <tr><td colspan="7" bordercolor ='#FFFFFF'><hr><strong>Colores de referencia para Columna R/E</strong> </td></tr>
 <tr>
  <td width='15%' bordercolor ='#FFFFFF'>&nbsp; </td>
  <td  width='25%' align='right' bordercolor ='#FFFFFF'>Se entregaron todos los productos</b></td>
  <td width='5%' bgcolor="#00C100">&nbsp; </td>
  <td width='5%' colspan="4" bordercolor ='#FFFFFF'>&nbsp; </td>
 </tr>
 <!---->
 <tr><td colspan="7" width='15%' bordercolor ='#FFFFFF'>
<?
if ($cmd=='d' || $cmd=='todas') {
	?>
<table width='100%' bordercolor ='#FFFFFF' border="1">
<tr><td colspan="7" ><hr><strong>Colores de referencia para monto</strong> </td></tr>
<tr>
<td width='15%'>&nbsp;</td>
<td width='25%' align='right'>Parcialmente Pagadas</td>
<td width='5%' bgcolor='#FFFFC0' bordercolor ='#000000'>&nbsp;</td>
<td  width='25%' align='right'>Totalmente Pagadas</td>
<td width='5%' bgcolor='#cccccc' bordercolor ='#000000'>&nbsp;</td>
<td  width='20%' align='right'>Pago Múltiple</td>
<td width='5%' bgcolor='#FFA042' bordercolor ='#000000'>&nbsp; </td>
</tr>
<tr><td colspan="7"><hr><strong>Colores de referencia para filas</strong></td></tr>
<tr>
<td width='10%'>&nbsp;</td>
<td width='20%' align='right'>Orden Anulada </td>
<td width='5%' bgcolor="#FF8080" bordercolor ='#000000'>&nbsp;</td>
</tr>
</table>
<?if ($cmd=='todas') { ?>
<!-- agrego etsa tabla para coplor de referencia en el id d licitacion -->
<table width='100%' bordercolor ='#FFFFFF' border="1">
<tr>
  <td colspan="7"><hr><strong>Colores de referencia para ID Licitación</strong></td>
</tr>
<tr>
<td width='15%'>&nbsp;</td>
<td width='25%' align='right'>Licitación Entregada</td>
<td width='5%' bgcolor='Red' bordercolor ='#000000'>&nbsp;</td>
<td  align='right' colspan="5">&nbsp; </td>

</tr>
</table>
<?
}
}
elseif($cmd=='e'){?>
<table width='100%' bordercolor ='#FFFFFF' border="1">
<tr>
  <td colspan="7"><hr><strong>Colores de referencia para monto</strong></td>
</tr>
<tr>
<td width='15%'>&nbsp;</td>
<td width='25%' align='right'>Parcialmente Pagadas</td>
<td width='5%' bgcolor='#FFFFC0' bordercolor ='#000000'>&nbsp;</td>
<td  width='25%' align='right'>Enviadas </td>
<td width='5%' bgcolor='#cccccc' bordercolor ='#000000'>&nbsp;</td>
<td  width='20%' align='right'>Pago Múltiple </td>
<td width='5%' bgcolor='#FFA042' bordercolor ='#000000'>&nbsp; </td>
</tr>
</table>
<!-- agrego etsa tabla para coplor de referencia en el id d licitacion -->
<table width='100%' bordercolor ='#FFFFFF' border="1">
<tr>
  <td colspan="7"><hr><strong>Colores de referencia para ID Licitación</strong></td>
</tr>
<tr>
<td width='15%'>&nbsp;</td>
<td width='25%' align='right'>Licitación Entregada</td>
<td width='5%' bgcolor='Red' bordercolor ='#000000'>&nbsp;</td>
<td  width='25%' align='right' colspan="5">&nbsp; </td>
</tr>
</table>
<?
} ?>

</td></tr>
</table>


<br>

<?
}
elseif ($cmd=='p') {?>
<br>
<table border=1 bordercolor= '#000000' bgcolor='#FFFFFF' width='90%' align="center" cellspacing=0 cellpadding=1>
<tr>
<td colspan="4" bordercolor ='#FFFFFF'><strong>Colores de referencia para filas</strong> </td></tr>
<tr>
<td bordercolor ='#FFFFFF'>&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td width="3%" bgcolor="#FFFFC0" align="left">&nbsp;</td>
<td width="97%" bordercolor ='#FFFFFF'>&nbsp;Orden Rechazada </td>
</table>
<?
}
?>

 </center>
</form>
<?=fin_pagina(); ?>