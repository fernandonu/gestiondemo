<?
/*
Autor: Elizabeth Ferreira - Marco Canderle

$Author: fernando $
$Revision: 1.87 $
$Date: 2007/02/13 21:26:18 $
*/


require_once("../../config.php");
echo $html_header;

if ($_POST["actualizar_numeros"]) {
	
	$sql=" select nro_factura,id_factura from facturas";
	$res=sql($sql) or fin_pagina();
	echo "Actualizando Datos.....<br>";
	for($i=0;$i<$res->recordcount();$i++){
   		$nro_factura=$res->fields["nro_factura"];
   		$id_factura=$res->fields["id_factura"];
        $asterisco="";
   		//echo "<br>Nro Factura: $nro_factura res: ".substr_count("*",$nro_factura);
   		
   		if (substr_count($nro_factura,"*")){
   			//si tiene * el nro de factura es un caso especial
   		       switch (substr_count($nro_factura,"*")){
   		       	case 1:
   		       		  $nro_factura=substr($nro_factura,1);
   		       		  $asterisco="*";
   		       		  break;
   		       	case 2:
   		       		  $nro_factura=substr($nro_factura,2);
   		       		  $asterisco="**";
   		       		  break;	  
   		       }	
          			
   		}
   		
        
   		switch (strlen($nro_factura)){
			    			case 1:
				   				   $nro_factura="0000000".$nro_factura;
				   				   break;
				   			case 2:
				   				   $nro_factura="000000".$nro_factura;
				   				   break;
				   		    case 3:
				   				   $nro_factura="00000".$nro_factura;
				   				   break;
				   			case 4:
				   				   $nro_factura="0000".$nro_factura;
				   				   break;
				   			case 5:
				   				   $nro_factura="000".$nro_factura;
				   				   break;
				   			case 6:
				   				   $nro_factura="00".$nro_factura;
				   				   break;
				   			case 7:
				   				   $nro_factura="0".$nro_factura;
				   				   break;
				   		}
   		
   		$nro_factura=$asterisco.$nro_factura;
  		$sql="update facturas set nro_factura='$nro_factura' where id_factura=$id_factura";
   		sql($sql) or fin_pagina();
		$res->movenext();
	}//del for1
	echo "Termino de actualizar ".$res->recordcount()." facturas <br>";
} //del if de actualizar numeros



/////////esto ahy que bortrar luego		
if ($parametros['informar']!="")
echo "<b><br>".$parametros['informar']."<br></b>";

//////////////////////////////////
if ($_POST["form_busqueda"]){
	 if(!$_POST["filtro_fecha"]){
	  $_POST["filtro_fecha"]=0;
	  
	 }
	 if(!$_POST["filtro_monto"])
	  $_POST["filtro_monto"]=0; 
 }  
 $var_sesion=array(
               "tipo_factura"=>"",
               "moneda"=>"",
               "filtro_fecha"=>"0",
               "fecha_desde"=>"",
               "fecha_hasta"=>"",
               "filtro_monto"=>"0",
               "monto_desde"=>"",
               "monto_hasta"=>"",
               );	
variables_form_busqueda("facturas",$var_sesion);


//ver dependencia con el archivo pdf.php
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs
$total_en_pesos=0;
$total_en_dolares=0;
if ($parametros["cmd"] == "detalle") {
	include("factura_nueva.php");
	exit;
}

//DATOS DE LA FACTURA se extraen del arreglo POST
//extract($_POST,EXTR_SKIP);
/*if ($parametros)
	extract($parametros,EXTR_OVERWRITE);*/

$backto=$parametros['backto'] or $backto=$_POST['backto'];
$onclick = $parametros['onclick'];


if ($backto && $_ses_global_backto!=$backto)
{
	phpss_svars_set("_ses_global_backto",$backto);
	phpss_svars_set("_ses_global_extra",$parametros['_ses_global_extra']);
}
if ($onclick)
	phpss_svars_set("_ses_onclick",$onclick);
	

//////////////////////////////////////////////	
// para pasar al form busqueda
// arreglos orden y filtro
$orden = array (
    "default" => "1",
    "1" => "fecha_factura",
    "2" => "nro_factura",
    "3" => "tipo_factura",
    "4" => "cliente",
    "5" => "total",
    "6" => "usuario"
  );
  
$filtro = array (
    "nro_factura" => "Nº Factura",
    "nro_remito" => "Nº Remito",
    "id_licitacion" => "ID Licitación",
    "pedido" => "Nº Pedido",
    "venta" => "Venta",
    "cliente" => "Cliente - Nombre",
    "direccion" => "Cliente - Direccion",
    "cuit" => "Cliente - C.U.I.T.",
    "iib" => "Cliente - Nº I.I.B.",
    "iva_tipo" => "Cliente - Condición I.V.A.",
    "iva_tasa" => "Cliente - Tasa I.V.A.",
    //"cliente.otros" => "Cliente - Otros"
  ); 

// esta es la consulta q se ejecuta siempre a la cual hay q adicionarle los where dep
// de los campos q elija  
$campos="facturas.id_factura,(numeracion_sucursal.numeracion || text('-') || facturas.nro_factura) as nro_factura,facturas.cliente,
         nro_remito,pedido,venta,facturas.tipo_factura,direccion,cuit,iib,iva_tipo,iva_tasa,
         facturas.fecha_factura,facturas.estado,facturas.id_moneda,facturas.id_licitacion,moneda.simbolo,id_renglones_oc,
         log.usuario,log.fecha,log.tipo_log,case when estado='a' then 0 else total end as total ";
$sql_tmp="select * from (select $campos FROM facturas
          left join (select count(id_renglones_oc) as id_renglones_oc,id_factura from facturacion.items_factura group by id_factura) as roc using(id_factura)
          left join log on log.id_factura=facturas.id_factura and log.tipo_log='creacion' 
          left join moneda using(id_moneda)
          left join numeracion_sucursal using (id_numeracion_sucursal)
          left join 
             (select sum(precio*cant_prod) as total,id_factura from items_factura group by id_factura) mtotal 
               on facturas.id_factura=mtotal.id_factura) as sub ";
$where_tmp="";

// se usa en el form busqueda como where de la consulta general
// en where_tmp tengo q agregar el resto de los campos q se eljan
switch ($cmd) 
{
	case 'anuladas': $where_tmp=" estado='a'";
	                 $contar="select count(id_factura) from facturas where estado='a'";
			         break;
	case 'terminadas': $where_tmp=" estado='t'";
	                   $contar="select count(id_factura) from facturas where estado='t'";
			           break;
	case 'todas': $contar="select count(id_factura) from facturas";
	              break;
	default: $where_tmp=" estado='p'";
	         $contar="select count(id_factura) from facturas where estado='p'";
	         $cmd='pendientes';
	         break;
}

/// no forman parte del arreglo filtro se tiene que agregar a where_tmp
//para ver que tipos de facturs busca (A,B o ambas)
switch($tipo_factura)
{case 'a':if ($where_tmp!="")
            $where_tmp.=" and sub.tipo_factura='a'";
          else
            $where_tmp.=" sub.tipo_factura='a'";
          $contar="buscar";  
          break;
 case 'b':if ($where_tmp!="")
            $where_tmp.=" and sub.tipo_factura='b'";
          else
            $where_tmp.=" sub.tipo_factura='b'";
          $contar="buscar";  
          break;
}

//para ver que tipos de facturs busca (A,B o ambas)
switch($moneda)
{case 'pesos':if ($where_tmp!="")
            $where_tmp.=" and sub.id_moneda=1";
          else
            $where_tmp.=" sub.id_moneda=1";
          $contar="buscar";  
          break;
 case 'dolares':if ($where_tmp!="")
            $where_tmp.=" and sub.id_moneda=2";
          else
            $where_tmp.=" sub.id_moneda=2";
          $contar="buscar";  
          break;
}

if ($filtro_fecha == "1") {
	/*$error = 0;
	if (!FechaOk($fecha_desde)) {
		
		Error("El formato de la fecha de inicio no es válido");
	}
	if (!FechaOk($fecha_hasta)) {
		Error("El formato de la fecha de finalización no es válido");
	}
	if (!$error) {*/
		if ($where_tmp!="")
			$where_tmp.=" and sub.fecha_factura between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";
		else
			$where_tmp.=" sub.fecha_factura between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";	
	$contar="buscar";
}

if ($filtro_monto == "1") {
	/*$error = 0;
	if (!es_numero($monto_desde)) {
		Error("El formato del monto de inicio no es válido");
	}
	if (!es_numero($monto_hasta)) {
		Error("El formato del monto de finalización no es válido");
	}
	if (!$error) {*/
		if ($where_tmp!="")
			$where_tmp.=" and total between $monto_desde and $monto_hasta";
		else
			$where_tmp.=" total between $monto_desde and $monto_hasta";
	
	$contar="buscar";
}

$query.=" ORDER BY";
if ($orden)
	$query.=" $orden";
else 
	$query.=" fecha_factura";



?>
<script>
function borrar()
{if (!document.all.filtro_fecha.checked)
    {document.all.fecha_desde.value="";
     document.all.fecha_hasta.value="";
    }    	
}	
function borrar2()
{if (!document.all.filtro_monto.checked)
    {document.all.monto_desde.value="";
     document.all.monto_hasta.value="";
    }
}

function convert_fecha(fecha)
{partes_fecha=fecha.split("/");
 fecha=partes_fecha[2]+"-"+partes_fecha[1]+"-"+partes_fecha[0]; 
 return fecha;
}	

function controles()
{var fecha1=document.all.fecha_desde.value;
 var fecha2=document.all.fecha_hasta.value; 
 if (document.all.filtro_fecha.checked)
    {if (document.all.fecha_desde.value=="" || document.all.fecha_hasta.value=="")
        {alert ("Debe ingresar ambas fechas para poder buscar entre fechas");
         return false;
        }
     else if (convert_fecha(fecha1)>convert_fecha(fecha2))
           {alert ("La fecha 'Desde' debe ser menor o igual a la fecha 'Hasta'");
            return false;
           }     
    }
 if (document.all.filtro_monto.checked)
    {if (document.all.monto_desde.value=="" || document.all.monto_hasta.value=="")
        {alert ("Debe ingresar ambos montos para poder buscar entre montos ");
         return false;
        }
     else if (parseInt(document.all.monto_desde.value)>parseInt(document.all.monto_hasta.value))
             {alert ("El monto 'Desde' debe ser menor o igual al monto 'Hasta'");
              return false;
             }     
    }
 return true;      	  
}	
 

//esto es para truncar los flotantes a x posiciones
//la condicion es para saber si la funcion esta definida para la clase Number
if (!Number.toFixed)
	{
	Number.prototype.toFixed=
	function(x) {
   					var temp=this;
   					temp=Math.round(temp*Math.pow(10,x))/Math.pow(10,x);
   					return temp;
					};
	}

function calcular_monto(check,moneda)
{
var separador,long,string,fin,substr,p;
if (document.all.primera_vez.value==1) //veo que moneda se usa
{document.all.moneda_actual.value=moneda.value;
 document.all.primera_vez.value=0;
 document.all.contador.value=0;
}
else
{if (document.all.moneda_actual.value!=moneda.value)
 {alert("Debe sumar mismo tipo de moneda");
  check.checked=false;
  return 0;
 }
}
 string=check.value;
 string=string.replace('.',"");
 string=string.replace(',',".");
 //check.value=string;
 if (check.checked==true)
  {document.all.monto_total.value=parseFloat(document.all.monto_total.value) + parseFloat(string);
   document.all.contador.value=parseInt(document.all.contador.value)+1;
  }
 else
  {document.all.monto_total.value=parseFloat(document.all.monto_total.value) - parseFloat(string);
   document.all.contador.value=parseInt(document.all.contador.value)-1;
  }
var string= new String(document.all.monto_total.value); 
	if (string.indexOf(".")==-1)
		string=string+".00";
//alert(string);
string=string.replace('.',',');
fin=string.length;
substr=string.substring(fin-3,fin);
p=1;
while(fin>3)
{if (p!=1)
 {aux=string.substring(fin-3,fin);
  substr='.'+aux+substr;
 }
 else
  p=0; 
 fin-=3;
}
substr=string.substring(0,fin)+substr;
string=substr;
document.all.monto.value=string;
if (moneda.value==0)
 document.all.monto.value="$ "+document.all.monto.value;
else
 document.all.monto.value="U$S "+document.all.monto.value;
if (document.all.contador.value==0)
 {document.all.monto.value=0;
  document.all.primera_vez.value=1;
 }
return 1;
}

function resetear()
{var i,objeto;
 i=1;
 while(i<=document.all.cantidad_check.value)
 {objeto=eval("document.all.chk_"+i);
  objeto.checked=false;
  i++;
 }
 document.all.primera_vez.value=1;
 document.all.moneda_actual.value=0;
 document.all.monto_total.value=0;
 document.all.contador.value=0;
 document.all.monto.value=0;
}
</script>
<?php
cargar_calendario();
include("../ayuda/ayudas.php");
?>
</head>
<?
/*
 if ($_ses_global_backto!="detalle_movimiento.php"){
     echo ch_menu($_SERVER['SCRIPT_NAME']); 
 }
*/ 
 ?>

<form name="form" method="post" action="<?php echo encode_link("factura_listar.php",array('filtro'=>$cmd)); ?>">
<input type="hidden" name="primera_vez" value="1">
<input type="hidden" name="moneda_actual" value="0">
<input type="hidden" name="monto_total" value="0">
<input type="hidden" name="contador" value=0>
<input type="hidden" name="id_factura_cargar" value="">
<input type="hidden" name="nro_factura_cargar" value="">
<table  width=100% align=center >
 <tr>
  <td width="98%" align="right">
  <? $datos_barra = array(
  					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendientes"
						),
					array(
						"descripcion"	=> "Terminadas",
						"cmd"			=> "terminadas"
						),
					array(
						"descripcion"	=> "Anuladas",
						"cmd"			=> "anuladas"
						),
					array(
						"descripcion"	=> "Todas",
						"cmd"			=> "todas"
						)
				 );
     generar_barra_nav($datos_barra);  
  ?>
  </td>
  <td width="2%">
   <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/facturas/fact_listar.htm" ?>', 'FACTURAS')" >
  </td>
 </tr>
</table>
<table width="100%" >
 <tr>
  <td align="center">
   <? 
   // if($keyword!="")
   // $contar="buscar"; 
    $sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "total",
 		"mask" => array ("\$","U\$S")
   );

//echo "<br> sql_tmp".$sql_tmp;
//echo "<br> where_tmp".$where_tmp;
   
   
    list($sql,$total_facturas,$link_pagina,$up,$suma) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar",$sumas);
       
    $facturas=sql($sql) or fin_pagina();
//echo "<br>".$sql;

//echo "<br> SUMAS ".$suma;
    // Calcular los totales por moneda
//	$pat_busq = array("/^select .+ FROM/s","/ORDER BY.*LIMIT \d+ OFFSET \d+/s");
//	$reemplazo = array("SELECT moneda.nombre,moneda.simbolo,sum(case when estado='a' then 0 else total end) as total FROM", "GROUP BY moneda.simbolo,moneda.nombre");
//	$sql_montos = preg_replace($pat_busq,$reemplazo,$sql);
//
//    $result_montos = sql($sql_montos) or fin_pagina();
//	$total_moneda = array();
//	while (!$result_montos->EOF) {
//		$total_moneda[$result_montos->fields["nombre"]] = 
//							array ("nombre" => $result_montos->fields["nombre"],
//									"simbolo" => $result_montos->fields["simbolo"],
//									"total" => formato_money($result_montos->fields["total"])
//									);
//		$result_montos->MoveNext();
//	}
//	arsort($total_moneda);
//   ?>
  </td>
  <td>
   <table align="center">
    <tr>
     <td>
      <input type="submit" name="boton" value="Buscar" onclick="return controles()">
     </td>
     <td>   
      <input type="button" name="busq_avanzada" value="Busqueda Avanzada" onclick="window.open('busq_avanzada_facturas.php');">&nbsp;&nbsp;&nbsp;
     </td>      
    </tr>
   </table>    
  </td>
 </tr>
</table>
<table align="center" >
 <tr>
  <td>
   <table class="bordes">
    <tr>
     <td>
      <input type="checkbox" name="filtro_fecha" value="1" <?=($filtro_fecha==1)?"checked":""?> onclick="borrar()">
      <b>Filtrar por fechas:</b> 
     </td> 
    </tr>
    <tr>
     <td> 
      <?if (!$filtro_fecha) {$fecha_desde="";$fecha_hasta="";}?>
      <b>Desde</b><input type="text" size="10" name="fecha_desde" value="<?=$fecha_desde?>" readonly>
      <?=link_calendario("fecha_desde")?>
      <b>Hasta</b><input type="text" size="10" name="fecha_hasta" value="<?=$fecha_hasta?>" readonly>
      <?=link_calendario("fecha_hasta")?>
     </td>
    </tr>
   </table>   
  </td>
  <td>
   <table class="bordes">
    <tr>
     <td>
      <input type="checkbox" name="filtro_monto" value="1" <?=($filtro_monto==1)?"checked":""?> onclick="borrar2()">
      <b>Filtrar por montos:</b>
     </td>
    </tr>
    <tr> 
     <td> 
      <?if (!$filtro_monto) {$monto_desde="";$monto_hasta="";}?>
      <b>Desde</b><input type="text" size="10" name="monto_desde" value="<?=$monto_desde?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
      <b>Hasta</b><input type="text" size="10" name="monto_hasta" value="<?=$monto_hasta?>" onkeypress="return filtrar_teclas(event,'0123456789.')">
     </td>
    </tr>
   </table>   
  </td>
  <td>
   <table class="bordes">
    <tr>
     <td width="15%">
      <b>Tipo Factura</b>
      <select name="tipo_factura">
       <option value='todas' <?if($tipo_factura=='todas') echo "selected"?>>Todas</option>
       <option value='a' <?if($tipo_factura=='a') echo "selected"?>>A</option>
       <option value='b' <?if($tipo_factura=='b') echo "selected"?>>B</option>
      </select> 
     </td>
    </tr>
    <tr>
     <td width="10%">
      <b>Moneda&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
      <select name="moneda">
       <option value='todas' <?if($moneda=='todas') echo "selected"?>>Todas</option>
       <option value='dolares' <?if($moneda=='dolares') echo "selected"?>>Dólares</option>
       <option value='pesos' <?if($moneda=='pesos') echo "selected"?>>Pesos</option>
      </select>
     </td>
    </tr>
   </table>
  </td>     
 </tr>    
</table>

<br><?="<b>".$informar."</b>"?>
<table width="100%" cellpadding="0" cellspacing="0">
 <tr id=ma>
  <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Suma 
    <input type="text" name="monto" size="14" style="border-style:none;background-color:'transparent';font-weight: bold;" value="0" size="12" readonly>
    <input type="button" name="boton" value="Reset" onclick="resetear()">
    </b></font>
    </td>
    <td>
      <? echo "TOTAL: <span class='text_4'>".$suma."</span>"?>
    </td>
   <?
//   		foreach ($total_moneda as $totales) {
//			echo "<td align=right>Total en ".$totales["nombre"].": <span class='text_4'>".$totales["simbolo"]."&nbsp;".$totales["total"]."</span></td>";
//		}
	//echo "<td align=right >Total: <span class='text_4'>".$suma."</span></td>";
   ?>
 </tr>
</table>
<table border=0 width=100% cellspacing=2 cellpadding=3  align=center>
  <tr>
   <td align=left id=ma>
    <b>Total:</b><?=$total_facturas?>
   </td>
   <td align=left id=ma>
    <?=($link_pagina)?$link_pagina:"&nbsp;"?>
   </td>
  </tr>
</table>  
<table border=0 width=100% cellspacing=2 cellpadding=3  align=center>  
  <tr>
   <td align=right id=mo></td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("factura_listar.php",array("sort"=>"1","up"=>$up))?>'>Fecha</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("factura_listar.php",array("sort"=>"2","up"=>$up))?>'>Nº Factura</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("factura_listar.php",array("sort"=>"3","up"=>$up))?>'>Tipo</a>
   </td>
   <?
   if($cmd=="todas") 
   {
   ?>
    <td align=right id=mo>Estado</td>
   <?
    }
   ?>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("factura_listar.php",array("sort"=>"4","up"=>$up))?>'>Cliente</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("factura_listar.php",array("sort"=>"5","up"=>$up))?>'>Monto Total</a>
   </td>
   <td align=right id=mo>
    <a id=mo href='<?=encode_link("factura_listar.php",array("sort"=>"6","up"=>$up))?>'>Creada por</a>
   </td>
<?if ($cmd=="terminadas" || $cmd=="todas" || $cmd=="anuladas") 
  { ?>
   <td align=right id=mo></td> 
   <td align=right id=mo></td> 
<?} ?>
 </tr>
<? 	$l=1;
    while (!$facturas->EOF ) {    	 
    	if ($_ses_global_backto=='remito_nuevo') {
    	   $ref = encode_link("../remitos/remito_nuevo.php",array("id_factura"=>$facturas->fields['id_factura'],"cmd"=>"asociar_factura"));
           $onclick_elegir="onclick=\"location.href='$ref'\"";    	   
    	}
        elseif(trim($_ses_global_backto) == "detalle_movimiento.php"){
        	//la pagina es pm y es un pm venta directa
        	$id_factura_cargar = $facturas->fields['id_factura'];
    	    $nro_factura_cargar = $facturas->fields['nro_factura'];
        	$onclick_elegir = "onclick=\"document.all.id_factura_cargar.value = '$id_factura_cargar';
        	                   document.all.nro_factura_cargar.value = '$nro_factura_cargar'; 
        	                   $_ses_onclick\"";
        }else {
           $ref = encode_link("factura_nueva.php",array("id_factura"=>$facturas->fields['id_factura'],"cmd"=>$cmd));
           $onclick_elegir="onclick=\"location.href='$ref'\"";
        }
     ?>
     <tr <?=atrib_tr($bgcolor_out)?>>
      <td><input type="checkbox" name="chk_<?php echo $l; ?>" value="<?php echo number_format($facturas->fields['total'], 2, ',', '.')?>" onclick="return calcular_monto(chk_<?php echo $l; ?>,tipo_moneda_<?php echo $l; ?>);"></td>
      <td align="center" <?=$onclick_elegir?>><font color="#006699" size=-2><b><? echo Fecha($facturas->fields['fecha_factura']) ?></b></font></td>
      <?$nose=$facturas->fields['nro_factura'];
        $parte=ereg_replace("\*","",$nose);
      ?>
      <td align="center"  <?=$onclick_elegir?>><font color="#006699" size=-2><b><? echo $parte?></b></font></td>
      <td align="center"  <?=$onclick_elegir?>><font color="#006699" size=-2><b><? echo strtoupper($facturas->fields['tipo_factura']); ?></b></font></td>
      <? if($cmd=="todas") { ?>
      <td align="center"  <?=$onclick_elegir?>><font color="#006699"><b>
      <? switch ($facturas->fields['estado']) {
        		case 'a':
        		case 'A': echo "Anulada";break;
        		case 'p':
			  	case 'P': echo "Pendiente";	break;
        		case 't':
			  	case 'T': echo "Terminada";	break;		
         } ?>
         </b></font></td>
      <? } ?>
      </a>
      <td align="center"  <?=$onclick_elegir?>><font color="#006699" size=-2><b><? echo $facturas->fields['cliente'] ?></b></font></td>
      <td  <?=$onclick_elegir?>><table width="100%"><tr>
           
           <input type="hidden" name="tipo_moneda_<?php echo $l; ?>" value="<?php if ($facturas->fields['simbolo']=="$") echo 0; else echo 1; ?>"> 
           <td width="20%" align="left"><font color="#006699" size=-2><b><?=$facturas->fields['simbolo']?></b></center></font></td>
           <td width="80%" align="right"><font color="#006699" size=-2><b><?= number_format($facturas->fields['total'], 2, ',', '.') ?></b></center></font></td>
      </tr></table></td>
      <td align="center"  <?=$onclick_elegir?>><font color="#006699" size=-2><b><? echo $facturas->fields['usuario'] ?></b></font></td>
      <? if ($cmd=="terminadas" || $cmd=="todas" || $cmd=="anuladas") { ?>
        <td align="center">
        <? switch ($facturas->fields['estado']) {	
        	case 'p': break;
      		case 'a': 
 			case 't': 
              if ($facturas->fields['id_renglones_oc'])
                  $seg=1; //la factura se creo desde el menu Produccion/entregas
              else $seg=0;
			  if ($facturas->fields['tipo_factura']=='a')
			     $link=encode_link("facturaA_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
			  else  
			     $link=encode_link("facturaB_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
		      echo "<a target='_blank' href='".$link."' title='Para San Luis'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'></a>";
		      //Si es factura de Bs AS
		      echo "</td>";
		      echo "<td align='center'>";
		      if ($facturas->fields['tipo_factura']=='a')
			     $link=encode_link("facturaA_bsas_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
			  else  
			     $link=encode_link("facturaB_bsas_pdf.php", array("id_factura"=>$facturas->fields['id_factura'],"seg"=>$seg));	
		      echo "<a target='_blank' href='".$link."' title='Para Bs As'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'></a>";
        } ?>
        </td>
        <? 	}
  		echo   "</tr>";
  		$facturas->MoveNext();
  		$l++;
  }
?>
  </table>
  </center>
<!--<input type="submit" name="actualizar_numeros" value="actualizar numeros">  -->
</form>
<input type="hidden" name="cantidad_check" value="<?php echo $l-1; ?>">
<br>


<?
echo "</a>";
fin_pagina();
?>