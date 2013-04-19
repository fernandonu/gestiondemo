<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.9 $
$Date: 2006/08/14 15:41:05 $
*/
require_once("../../config.php");
require_once("fns.php");

//DATOS DEL REMITO se extraen del arreglo POST
extract($_POST,EXTR_SKIP);

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

//tiene el nombre del arreglo de variables de sesion	
$ses_varname="ord_pago";

//viene del menu?, de cual?
if($_GET['ver'])
	phpss_svars_set("_ses_ord_pago_ver",$ver=$_GET['ver']);        
else 
	$ver=phpss_svars_get("_ses_ord_pago_ver") or $ver="compras";        

//si se llamo desde el menu de pagos/cobros	
if ($ver=="pagos")     
{ 
	//prefijo de sesion
	$ses_varname="op_pagos";
	
	$datos_barra = array(
                    array(
                        "descripcion"    => "A Pagar",//sin pagar y pagadas parcialmente
                        "cmd"            => "e"
                        ),
                    array(
                        "descripcion"    => "Pagadas",//solo las pagadas totalmente (historial)
                        "cmd"            => "d"
                        ),
                    array(
                        "descripcion"    => "Todas",//solo los estados de arriba (No incluye otros estados de OC)
                        "cmd"            => "todas"
                        )
	);
	variables_form_busqueda($ses_varname);	
}
//sino entro directo por orden de compra
else //$ver=='compras'
{ 
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
	                        "descripcion"    => "Pagadas",
	                        "cmd"            => "d"
	                        ),
	                    array(
	                        "descripcion"    => "Todas",
	                        "cmd"            => "todas"
	                        )
	);
	//se cambian los valores de las variables de sesion cuando viene de la pagina
	//de licitaciones para que tome el id de lic y el estado todas
	if ($parametros["volver_lic"]) 
		phpss_svars_set("_ses_ord_pago",$parametros);        
   if ($_POST["form_busqueda"])  {
     if(!$_POST["mostrar_hst"]){
         $_POST["mostrar_hst"]=-1;
     }
   }	
	variables_form_busqueda($ses_varname,array("mostrar_hst"=>""));
}
$itemspp=100;

if ($_POST["buscar"])
        $page=0;

//viene el cmd por parametros?        
if ($parametros['cmd']!="")
  $cmd=$parametros['cmd'];
else
{
	//esta el cmd como variable de sesion?
	if (${"_ses_$ses_varname"}['cmd']!="")
     $cmd=${"_ses_$ses_varname"}['cmd'];
   else
   		$cmd='a';//vista por defecto para compras
}//del primer if 

//es distinto el cmd del guardado en la sesion?  
if (${"_ses_$ses_varname"}['cmd'] != $cmd)
{
	${"_ses_$ses_varname"}['cmd']=$cmd;
   phpss_svars_set("_ses_$ses_varname",${"_ses_$ses_varname"});
   $page=0;
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


<form name="form1" method="post" action="<?php echo encode_link($_SERVER['SCRIPT_NAME'],array('cmd'=>$cmd)); ?>">
<div style="width:100%;overflow:auto;position:relative" id="div_formulario" >
<?
generar_barra_nav($datos_barra);
?>
<table align="center" border="0" id=tabla_buscar>
<tr>
<?
   if ($cmd=='todas' || $cmd=='a')
    {
?>
<input type='hidden' name='resumen_pagos' value='0'>
<!--<td align="left"><input type="button" name="Resumen" value="Resumen"  title="Envie por mail el resumen de las ordenes hasta la fecha" <?=$tiene_permiso?> onclick="document.all.resumen_pagos.value='Resumen';document.form.submit(); "></td>-->
<td>&nbsp;&nbsp;&nbsp;</td>
<? 
    }
?>
<td>
<?
if (0)//(permisos_check("inicio","ord_pago_materiales")) 
{
?>
<input type="button" name="Materiales" value="Materiales" title="Ver los materiales comprados" onclick="mat=window.open('ord_pago_materiales.php','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=790,height=590');">
&nbsp;&nbsp;&nbsp;
<?
}
/*******************************************************************************
SECCION DE BUSQUEDA SECCION DE BUSQUEDA SECCION DE BUSQUEDA SECCION DE BUSQUEDA 
********************************************************************************/
$campos="distinct(o.nro_orden),o.estado,o.desc_prod,o.fecha_entrega,o.id_licitacion,o.cliente,p.razon_social,simbolo,o.id_moneda,total_orden,total_sum,
         o.nrocaso, o.flag_honorario, o.flag_stock, o.orden_prod,o.estado_proveedor";
if($cmd=="p")
{$campos.=",log_ord.nombre||log_ord.apellido as nombre_creador,log_ord.fecha_creacion";
}
if($cmd=='u')
 $campos.=",plantilla_pagos.descripcion as titulo_pago";

 $query="SELECT $campos FROM orden_de_compra o left join
proveedor p using(id_proveedor) left join
(select sum(cantidad*(case when estado='n' then 0 else precio_unitario end)) as total_sum,
 sum(cantidad*precio_unitario) as total_orden,nro_orden from fila join orden_de_compra using (nro_orden) group by nro_orden) 
   costo using(nro_orden) left join
   moneda on(moneda.id_moneda=o.id_moneda) ";



if ($cmd!='todas')
{	if ($cmd=="p")
	{$where="(estado='p' OR estado='r')";
	 //$contar="select count (*) from orden_de_compra where estado='p' or estado='r'";
	}
	elseif ($cmd=="d" && $ver=='pagos') {
		$where="(estado='g' OR estado='t')";
	}
	elseif ($cmd=='d')
	{ $where="(estado='d' OR estado='g' OR estado='t')";
	  //$contar="select count (*) from orden_de_compra where estado='g' or estado='t' or estado='d'";
	}
	elseif($cmd=='e')
	{$where="(estado='d' OR estado='e')";
	 //$contar="select count (*) from orden_de_compra where estado='d' or estado='e'";
	}
	elseif ($cmd=='a')
	{$where="(estado='a' OR estado='e' or estado='m')";
	 //$contar="select count (*) from orden_de_compra where estado='$cmd'";
	}
	else
	{$where="estado='$cmd'";
	 //$contar="select count (*) from orden_de_compra where estado='$cmd'";
	}
	if($cmd=='u')
	{$query.=" left join plantilla_pagos using (id_plantilla_pagos)";
	}
	
}
//mostrar todas en pagos/cobros ??
elseif($cmd=="todas" && $ver=="pagos")
	$where="(estado='g' OR estado='t' OR estado='e')";

if ($where)	
	$where.=" AND ord_pago is not null ";
else 
	$where=" ord_pago is not null";

if ($mostrar_hst==1) {
   $where.=" and flag_honorario=1";
}	
	
$contar = 'buscar';
if (!(strpos($cmd,"Fecha")==false))
    	$keyword=Fecha_db($keyword);

if ($cmd!='p')
           $order=0;
           else
           $order=1;

$orden= array
(
		"default" => "3",
                "default_up"=>"$order",
		"1" => "o.nro_orden",
		"2" => "o.estado",
		"3" => "o.fecha_entrega",
		"4" => "o.id_licitacion",
		"6" => "p.razon_social",
         "7" => "total_orden"

);
$filtro_array= array
(
		"o.nro_orden"=>"Nº de Orden",
		"o.notas"=>"Comentarios",
		"p.razon_social"=> "Proveedor",
);
if($cmd=="p")
 $query.=" left join (select nombre,apellido,fecha as fecha_creacion,nro_orden from usuarios join log_ordenes on user_login=login where tipo_log='de creacion') as log_ord using(nro_orden)";
	
/**/
$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "total_sum",
 		"mask" => array ("\$","U\$S")
);

if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
     $contar="buscar";
$itemspp=50;
list($query,$total,$link_pagina,$up,$suma) = form_busqueda($query,$orden,$filtro_array,$link_tmp,$where,$contar,$sumas);

//$datos_orden=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);

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
		group by nro_orden) t2 using(nro_orden) ";
	}
/* */
//echo "query: $query";

$datos_orden=sql($query) or die($db->ErrorMsg()."<br>".$query);
if($total==0 && $keyword)
	$msg_nulo="<b><center>No se encontró ninguna orden de pago que concuerde con lo buscado</center></b>";


?>
<input type="submit" id="buscar" name="buscar" value="Buscar" onclick="parent.menu">&nbsp;&nbsp;&nbsp;&nbsp;
<td align="right"><input type="checkbox" class="estilos_check" name="mostrar_hst" value="1" <?if($mostrar_hst==1)echo "checked";?>><b> Mostrar OP Honorarios Serv. Tec.</b></td>
</td>
<?
 if (($cmd=='d')||($cmd=='e') || ($cmd=='t')|| ($cmd=='todas'))
     {
 ?>
<td bgcolor=<?=$bgcolor3 ?>>
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
<?if (0)//permisos_check("inicio","busq_avanzada"))
{
?>
<tr>
 <?$link=encode_link("busq_avanzada_armo.php",array()); ?>
 <td align="left" colspan="4"><input type="button" name="busqueda_avanzada" value="Busqueda Avanzada"  title="Realiza Busquedas Avanzadas" onclick="window.open('<?=$link?>')"></td>
</tr>
<?
}
?>

</table>
<br>
<? if ($msg)
   echo "<div align='center'>$msg</div>";
   unset($msg);
   if($msg_nulo)
    echo "<div align='center'><br>$msg_nulo</div>";
?>
<input type="hidden" name="moneda_suma" value=789>
<input type="hidden" name="total_auxiliar" value=0>
<input type="hidden" name="total_auxiliar_pagos" value=0>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr  id="ma">
		<td height="20px" style="text-align:left" >
        Total - Pagos
		  <? if ($cmd) echo ": ".$total ?>
		</td>
<?
  if (($cmd=="d")|| ($cmd=='e') || ($cmd=='t')|| ($cmd=='todas'))
          {
         // foreach ($total_moneda as $total_simbolo => $total_monto) {
         // echo "<td align=right><b>Total $total_simbolo&nbsp;$total_monto</b></td>";
          echo "<td align=right>Total $suma</td>";
         // }
}
        if($cmd=='e' && permisos_check("inicio","permiso_boton_montos_oc"))
        {$link_montos_oc=encode_link("montos_en_oc.php",array("estado"=>'e'));
?>      
        <td>
         &nbsp;<input type="button" name="montos_oc" value="$" title="Montos de OC" onclick="window.open('<?=$link_montos_oc?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=180,top=180,width=450,height=200')">
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
		<td style="text-align:right">
		<?= $link_pagina ?>
		</td>
	</tr>
  </table>
	<table border="0" cellspacing="2" cellpadding="0" width="100%">
	  <tr id="mo" align="center">
<?

	//checkbox para enviar mail
	//autorizadas  que en ord_pago es el estado e y el cmd=a
	if ($cmd=='a' ) { ?>
    <td width="1%">&nbsp;</td>
    <?}
if (($cmd=='d')||($cmd=='e') || ($cmd=='t')|| ($cmd=='todas')) { //es para los chekes
?>
    <td width="3%">&nbsp;</td>
<?
} //fin del if de los checked
?>
    <td width="5%" height="13"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up)) ?>'>NºOrden</a></td>
    <td width="5%" height="13">Tipo</a></td>
<?
if ($cmd=='todas')
   {
?>
		<td width="10%">Estado</td>
<?
	}
?>
    
	 <td width="10%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>Fecha</a></td>
		<?/*if($cmd=="e" || $cmd=="d" || $cmd=="todas")
		  {?>
		   <td width="1%" title="Recibidos/Entregados (totales de cada OC)">R/E</td>
		  <?
		  }*/
		  ?> 
		
		<td width="<?if($cmd=='u')echo "20"; else echo "25"?>%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up)) ?>'>Proveedor</a></td>
    <td width="10%"><a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"7","up"=>$up)) ?>'> Monto </a></td>

<?
  if($cmd=='u')
  {
  ?>
    <td width="13%">Forma de Pago</td>
   <?
  } 
       //imagen PDF
		//autorizadas,enviadas,terminadas,por terminar,todas
		if ($cmd=='a' || $cmd=='e' || $cmd=='t' || $cmd=='d' || $cmd=='m' || $cmd=='todas')
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
    {
       ?>
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
	//autorizadas  en caso de ord pago es el estado e
	if ($cmd=='a')  //ordenes Autorizadas
	{
    ?>
    <td align="center">
   <?
   if (($datos_orden->fields['estado']=='e')&&($cmd=='a') && $datos_orden->fields['flag_honorario']==1)
	{
	$contador++; ?>
	  <input type="checkbox" class="estilos_check" name="chk_<?=$contador?>" value="<?=$datos_orden->fields['nro_orden'] ?>" onclick="habilitar_mail(this)">
	  <input type="hidden" name="idorden[<?php echo $contador; ?>]" value="<?php echo $datos_orden->fields['nro_orden']; ?>">
    <? }
    else { echo "&nbsp;";}?>
   </td>
    <?
   }
	
if (($cmd=='d')||($cmd=='e') || ($cmd=='t')|| ($cmd=='todas'))
{ // de los checked
  switch($datos_orden->fields['estado']) {
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
  <input type="checkbox" class="estilos_check" name="chk_monto" value="<?=$monto ?>"
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
    <td>&nbsp;</td>
 <?
 }//del swtich
}//de los cheked

if ($ver=="pagos")
{
	//si tiene que pagarse algo 
	if ($datos_orden->fields['estado']=="e" || $datos_orden->fields['estado']=="d")
		$page="ord_pago_pagar.php";
	else //si esta pagada
		$page="ord_pago_resumen_pagos.php";
}
else 
	$page="ord_pago.php";

if (($cmd=="todas")&&($datos_orden->fields['estado']=='g'))
                                         $color="bgcolor='#C0FFC0'";
                                         else
                                         $color="";
                                         
if($cmd=="p")
{
 $title_pendiente="OC Creada por: ".$datos_orden->fields['nombre_creador']."\nFecha Creación: ".fecha($datos_orden->fields['fecha_creacion']);
}
?>
<a href="<?=encode_link($page,array("nro_orden"=>$datos_orden->fields['nro_orden'],"ver"=>$ver)); ?>">
<td height="18"  <?=$color;?> align="center" title="<?=$title_pendiente?>"><? echo $datos_orden->fields['nro_orden'] ?></td>
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
     ?>	 
  <td height="18" align="center" <?if ($datos_orden->fields['estado']=='m') echo "bgcolor=#C1E9AA"?> title="<?=$titulo?>"><? echo $tipo?></td>             
<? if ($cmd=='todas') {
?>
		<td align="center">
		  <? switch ($datos_orden->fields['estado'])
			  {
				case 'p': echo "Pendiente";break;
				case 't': echo "Terminada";break;
				case 'a': echo "Autorizada";break;
				case 's': echo "Sin Terminar";break;
				case 'r': echo "Rechazada";break;
				case 'g': //totalmente pagada
				case 'd': echo "Pagada";break; //parcialmente pagada
				case 'e': echo "Autorizada";break;
				case 'n': echo "Anulada";break;
				case 'u': echo "Por Autorizar";break;
				case 'm': echo "Enviada";break;

    		  }
?>  </td> 
<?
         }//del if de $cmd=="todas"
        $id_licitacion_listado="";
		if($tipo=="RMA")
		  $id_licitacion_listado=$datos_orden->fields['orden_prod_lic'];
		elseif($tipo=="Lic" || $tipo=="Pres")
          $id_licitacion_listado=$datos_orden->fields['id_licitacion']; 
?>        
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
		 <? echo Fecha($datos_orden->fields['fecha_entrega']) ?>
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
		 /* if($cmd=="e" || $cmd=="d" || $cmd=="todas")
		  {
		   if($ent==$comprados)	
		    $todos_entregados="bgcolor='#00C100'";
		   else 
		    $todos_entregados="";
		   ?>
		    <td title="<?=$title_comprados?>" <?=$todos_entregados?>><?=$rec?>/<?=$ent?></td>
		  <?
		  }*/
		  ?> 
		
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

$long_title=strlen($desc_orden);
  if($long_title>600) {
  	$desc_orden=substr($desc_orden,0,600);
	$desc_orden.="   SIGUE >>>";
   }
  $count_n=str_count_letra("\n",$desc_orden);
  //cortamos si el string tiene mas de 12 lineas
   if($count_n>12)	{
   	 $cn=0;$j=0;
	 for($i=0;$i<$long_title;$i++) {
		 if($cn>12)
		    $i=$long_title;
		 if($desc_orden[$i]=="\n")
		    $cn++;
		 $j++;
	 }
   $desc_orden=substr($desc_orden,0,$j);
   $desc_orden.="   SIGUE >>>";
   }

?>		
 <td align="center" title='<?=$desc_orden?>' <?=($color_prov!="")?"bgcolor='$color_prov';":"";?>><? echo $datos_orden->fields['razon_social'] ?></td>
		</a>
   <td align="right" <?
                     if ($datos_orden->fields['estado']=='d' && $pm<=1)
                         echo "bgcolor=#FFFFC0 title='Pagado: $monto_pagado'";
                     elseif($pm>1)    
                         echo "bgcolor=#FFA042 title='$title_pm'";
                      ?>  >
   
   <?=$datos_orden->fields['simbolo']."  $monto";?>
   </td>
<?
   if($cmd=='u')
   {if(strlen($datos_orden->fields['titulo_pago'])>20)
    {$titulo_pago=substr($datos_orden->fields['titulo_pago'],0,17);
     $titulo_pago.="...";
     $title_titulo_pago=$datos_orden->fields['titulo_pago'];
    }
    else 
    {$titulo_pago=$datos_orden->fields['titulo_pago'];
     $title_titulo_pago="";
    }
   	echo "<td align='center' title='$title_titulo_pago'>$titulo_pago</td>";
   }	 
	//imagen PDF
   //autorizadas,enviadas,terminadas,por terminar,todas
		if ($cmd=='a' || $cmd=='e' || $cmd=='t' || $cmd=='d' || $cmd=='m' || $cmd=='todas')
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
                case 'm':
				//echo "<A target='_blank' href='./PDF/orden_de_compra_".$datos_orden->fields['nro_orden'].".pdf"."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'>";
                $link=encode_link("ord_pago_pdf.php", array("nro_orden"=>$datos_orden->fields['nro_orden']));	
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
<br>

<table align="center">
  <tr>
     <?
      if ($cmd=='a') {?>
       <td align=center><input type="submit" name="boton" value="Enviar mail" disabled title="Envie por mail las ordenes seleccionadas" onclick="form.action='ord_pago_mail.php'"> </td>
       <?
      }///del if ?>
  </tr>
</table>

<? if ($cmd=='todas' || $cmd=='e' || $cmd=='d')
	{
?>
</b>
<br><a name="leyenda">
<table border=1 bordercolor= '#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>
 <tr><td colspan="7" width='15%' bordercolor ='#FFFFFF'>
<? 
if ($cmd=='d' || $cmd=='todas') {
	?>
<table width='100%' bordercolor ='#FFFFFF' border="1">
<tr>
	<td colspan="7" ><strong>Colores de referencia para monto</strong> </td></tr>
<tr>    
	<td width='15%'>&nbsp;</td>
	<td width='25%' align='right'>Parcialmente Pagadas</td>
	<td width='5%' bgcolor='#FFFFC0' bordercolor ='#000000'>&nbsp;</td>
	<td  width='20%' align='right'>Pago Múltiple</td>
	<td width='5%' bgcolor='#FFA042' bordercolor ='#000000'>&nbsp; </td>
	<td  width='25%' align='right'>&nbsp;</td>
	<td width='5%'>&nbsp;</td>
</tr>
<tr>
	<td colspan="7"><hr><strong>Colores de referencia para filas</strong></td>
</tr>
<tr>
	<td width='10%'>&nbsp;</td>
	<td width='20%' align='right'>Orden Anulada </td>
	<td width='5%' bgcolor="#FF8080" bordercolor ='#000000'>&nbsp;</td>
	<td width='20%' align='right'>Orden Rechazada </td>
	<td width='5%' bgcolor="#FFFFC0" bordercolor ='#000000'>&nbsp;</td>
	<td width='20%' align='right'>&nbsp; </td>
	<td width='5%'>&nbsp;</td>
</tr>
</table>
<?
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
<?
} 
?>
</td></tr>
</table> 


<br>

<?
}
elseif ($cmd=='p') {?>
<br>
<table border=1 bordercolor= '#000000' bgcolor='#FFFFFF' width='90%' align="center" cellspacing=0 cellpadding=1>
<tr><td colspan="4" bordercolor ='#FFFFFF'><strong>Colores de referencia para filas</strong> </td></tr>
<tr><td bordercolor ='#FFFFFF'>&nbsp;&nbsp;&nbsp;&nbsp;</td>  
<td width="3%" bgcolor="#FFFFC0" align="left">&nbsp;</td>
<td width="97%" bordercolor ='#FFFFFF'>&nbsp;Orden Rechazada </td>
</table>
<?
}
else if ($cmd=='a') {?>
<br>
<table border=1 bordercolor= '#000000' bgcolor='#FFFFFF' width='90%' align="center" cellspacing=0 cellpadding=1>
<tr><td colspan="4" bordercolor ='#FFFFFF'><strong>Colores de referencia para filas</strong> </td></tr>
<tr><td bordercolor ='#FFFFFF'>&nbsp;&nbsp;&nbsp;&nbsp;</td>  
<td width="3%" bgcolor="#C1E9AA" align="left">&nbsp;</td>
<td width="97%" bordercolor ='#FFFFFF'>&nbsp;Orden HST Enviada </td>
</table>
<?
}
//echo $cmd;
?>
</div>
</form>
</body>
</html>