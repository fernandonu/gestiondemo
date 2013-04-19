<?
/*
Author: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.6 $
$Date: 2006/11/06 18:41:37 $
*/

require_once("../../config.php");
$estado=$_POST['estado'];
cargar_calendario();
echo $html_header;
if ($_POST["form_busqueda"]) 
{
 if(!$_POST["filtro_fecha"])
  $_POST["filtro_fecha"]=0;
  
 if(!$_POST["filtro_monto"])
  $_POST["filtro_monto"]=0;
}   
 $var_sesion=array(
               "tipo_remitos"=>"",
               "moneda"=>"",
               "filtro_fecha"=>"0",
               "fecha_desde"=>"",
               "fecha_hasta"=>"",
               "filtro_monto"=>"0",
               "monto_desde"=>"",
               "monto_hasta"=>"",
               "estado"=>"",
               );	
variables_form_busqueda("busq_avanzada_remitos",$var_sesion);
$orden = array(
			"default_up" => "0",
			"default" => "1",
			"1" => "res.nro_remito",
			"2" => "nro_factura",
			"3" => "res.cliente",
			"4" => "usuario",
			"5" => "res.fecha_remito",
			"6" => "total"
		);
  
$filtro = array(
			"res.nro_remito" => "Nº Remito",
			"res.fecha_remito" => "Fecha Remito",
			"res.pedido" => "Nº Pedido",
			"res.venta" => "Venta",
			"res.direccion" => "Dirección",
			"nro_factura" => "Nº Factura",
			"res.cliente" => "Cliente - Nombre",
			"res.cuit" => "Cliente - C.U.I.T.",
			"res.iib" => "Cliente - Nº I.I.B.",
			"res.iva_tipo" => "Cliente - Condición de I.V.A.",
			"res.iva_tasa" => "Cliente - Tasa de I.V.A.",
			"res.otros" => "Otros",
			"cliente2" => "Recibido por - Nombre",
			"tipo_doc_c2" => "Recibido por - Tipo Documento",
			"nro_doc_c2" => "Recibido por - Nº Documento",
			"res.id_licitacion" => "ID Licitacion",
			"descripcion" => "Descripción del Producto"
		);

// esta es la consulta q se ejecuta siempre a la cual hay q adicionarle los where dep
// de los campos q elija  


$sql_tmp="select id_remito,nro_remito,fecha_remito,pedido,usuario,
          venta,direccion,nro_factura,cliente,cuit,iib,iva_tipo,
          iva_tasa,otros,cliente2,tipo_doc_c2,nro_doc_c2,id_licitacion, 
          fecha_remito,estado,id_moneda,simbolo,id_renglones_oc,total";
if ($filter=="descripcion" || $filter=="all") $sql_tmp.=" ,descripcion";
$sql_tmp.=" from (SELECT remitos.estado,(n_remitos.numeracion || text('-') || remitos.nro_remito) as nro_remito, remitos.cliente,
          remitos.id_remito,
          (n_facturas.numeracion || text('-') || facturas.nro_factura) as nro_factura, log.usuario,log.fecha,tipo_factura,fecha_remito,
          remitos.pedido,remitos.venta, remitos.direccion,remitos.cuit,remitos.iib,remitos.iva_tipo,remitos.iva_tasa,remitos.otros, 
          remitos.cliente2,tipo_doc_c2,nro_doc_c2,remitos.id_licitacion,remitos.id_factura,roc.id_renglones_oc,remitos.id_moneda,simbolo, 
          case when remitos.estado='a' then 0 else total end as total";
if ($filter=="descripcion" || $filter=="all") $sql_tmp.=" ,descripcion";
           
$sql_tmp.=" FROM (facturacion.remitos LEFT JOIN facturacion.facturas USING (id_factura)) 
          left join facturacion.numeracion_sucursal as n_facturas on (facturas.id_numeracion_sucursal=n_facturas.id_numeracion_sucursal) 
          left JOIN facturacion.log ON (log.id_remito=remitos.id_remito and log.tipo_log='creacion') 
          left join facturacion.numeracion_sucursal as n_remitos on (remitos.id_numeracion_sucursal=n_remitos.id_numeracion_sucursal)
          left join (select count(id_renglones_oc) as id_renglones_oc,id_remito from facturacion.items_remito group by id_remito) as roc on roc.id_remito=remitos.id_remito
          left join licitaciones.moneda on moneda.id_moneda=remitos.id_moneda
          left join 
             (select sum(precio*cant_prod) as total,id_remito from facturacion.items_remito group by id_remito) mtotal 
          on remitos.id_remito=mtotal.id_remito";

if ($filter=="descripcion" || $filter=="all") 
$sql_tmp.=" left join facturacion.items_remito on remitos.id_remito=items_remito.id_remito ) as res";
    else $sql_tmp.=" ) as res";
$where_tmp="";

// se usa en el form busqueda como where de la consulta general
// en where_tmp tengo q agregar el resto de los campos q se eljan
switch ($estado) 
{
	
	case 'anulados': $where_tmp=" res.estado='a'";
	                 $contar="select count(id_remito) from remitos where estado='a'";
			         break;
	case 'terminados': $where_tmp=" res.estado='t'";
	                   $contar="select count(id_remito) from remitos where estado='t'";
			           break;
    case 'recibidos': $where_tmp=" res.estado='r'";
	                   $contar="select count(id_remito) from remitos where estado='r'";
			           break;			           
	case 'todos': $contar="select count(id_remito) from remitos";
	              break;
	default: $where_tmp=" res.estado='p'";
	         $contar="select count(id_remito) from remitos where estado='p'";
	         $estado='pendientes';
	         break;
}

/// no forman parte del arreglo filtro se tiene que agregar a where_tmp
//para ver si los remitos tienen factura o no
switch($tipo_remitos)
{case 'cf':if ($where_tmp!="")
            $where_tmp.=" and res.id_factura is not NULL";
          else
            $where_tmp.=" res.id_factura is not NULL";
          $contar="buscar";  
          break;
 case 'sf':if ($where_tmp!="")
            $where_tmp.=" and res.id_factura is NULL";
          else
            $where_tmp.=" res.id_factura is NULL";
          $contar="buscar";  
          break;
}

//para ver que tipos de facturs busca (A,B o ambas)
switch($moneda)
{case 'pesos':if ($where_tmp!="")
            $where_tmp.=" and res.id_moneda=1";
          else
            $where_tmp.=" res.id_moneda=1";
          $contar="buscar";  
          break;
 case 'dolares':if ($where_tmp!="")
            $where_tmp.=" and res.id_moneda=2";
          else
            $where_tmp.=" res.id_moneda=2";
          $contar="buscar";  
          break;
}

if ($filtro_fecha == "1") {
	/*$error = 0;
	if (!FechaOk($fecha_desde)) {
		$error=1;
		Error("El formato de la fecha de inicio no es válido");
	}
	if (!FechaOk($fecha_hasta)) {
		$error=1;
		Error("El formato de la fecha de finalización no es válido");
	}
	if (FechaOK($fecha_desde) && FechaOK($fecha_hasta) && $fecha_hasta<$fecha_desde) {
		$error=1;
	    Error("La fecha Desde debe ser menor a la fecha Hasta");
	}    
	if (!$error) {*/
		if ($where_tmp!="")
			$where_tmp.=" and res.fecha_remito between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";
		else
			$where_tmp.=" res.fecha_remito between '".Fecha_db($fecha_desde)."' and '".Fecha_db($fecha_hasta)."'";
	
	$contar="buscar";
}

if ($filtro_monto == "1") {
	/*$error = 0;
	if (!es_numero($monto_desde)) {
		$error=1;
		Error("El formato del monto de inicio no es válido");
	}
	if (!es_numero($monto_hasta)) {
		$error=1;
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
	$query.=" fecha_remito";
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
</script>
<form name="busq_avanzada_remitos" method="post" action="busq_avanzada_remitos.php">
<table align="center">
 <tr>
  <td>
   <font size="4"><b>Busqueda Avanzada de Remitos</b></font>
  </td>
 </tr>
</table>
<table align="center">
 <tr>
  <td>
<?if($keyword!="")
 $contar="buscar";  
list($sql,$total_remitos,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);

$remitos=sql($sql) or fin_pagina();
//echo "<br>".$sql."<br>";
?>
  </td>
  <td>
   <input type="submit" name="boton" value="Buscar" onclick="return controles()">
  </td>  
 </tr> 
</table>
<table align="center" width="85%" >
 <tr>
  <td width="15%" align="right">
   <b>Remitos</b>
   <select name="tipo_remitos">
    <option value='todas' <?if($tipo_remito=='todas') echo "selected"?>>Todos</option>
    <option value='cf' <?if($tipo_remito=='cf') echo "selected"?>>Con Factura</option>
    <option value='sf' <?if($tipo_remito=='sf') echo "selected"?>>Sin Factura</option>
   </select>
   &nbsp;&nbsp;
  </td>
  <td width="15%" align="left">
   &nbsp;&nbsp;&nbsp;&nbsp;
   <b>Moneda</b>
   <select name="moneda">
    <option value='todas' <?if($moneda=='todas') echo "selected"?>>Todas</option>
    <option value='dolares' <?if($moneda=='dolares') echo "selected"?>>Dólares</option>
    <option value='pesos' <?if($moneda=='pesos') echo "selected"?>>Pesos</option>
   </select>
  </td> 
  <td width="15%" align="left">
   &nbsp;&nbsp;&nbsp;&nbsp;
   <b>Estado</b>
   <select name="estado">
    <option value='todos' <?if($estado=='todos') echo "selected"?>>Todos</option>
    <option value='pendientes' <?if($estado=='pendientes') echo "selected"?>>Pendientes</option>
    <option value='anulados' <?if($estado=='anulados') echo "selected"?>>Anulados</option>
    <option value='terminados' <?if($estado=='terminados') echo "selected"?>>Terminados</option>
    <option value='recibidos' <?if($estado=='recibidos') echo "selected"?>>Recibidos</option>
   </select>
  </td>
 </tr>
 <tr> 
  <td width="100%" colspan="3" align="center">
     <table border="1" bgcolor="#EEEEEE">
      <tr>
       <td>
        <table>
         <tr>
          <td>
           <input type="checkbox" name="filtro_fecha" value="1" <?=($filtro_fecha==1)?"checked":""?> onclick="borrar()">
           <b>Filtrar por fechas:</b> 
          </td> 
         </tr>
         <tr>
          <td> 
           <?if (!$filtro_fecha) {$fecha_desde="";$fecha_hasta="";}?>
           <b>Desde</b><input type="text" size="10" name="fecha_desde" readonly value="<?=$fecha_desde?>">
           <?=link_calendario("fecha_desde")?>
           <b>Hasta</b><input type="text" size="10" name="fecha_hasta" readonly value="<?=$fecha_hasta?>">
           <?=link_calendario("fecha_hasta")?>
          </td>
         </tr>
        </table>   
       </td>
       <td>
        <table>
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
           <b>Hasta</b><input type="text" size="10" name="monto_hasta" value="<?=$monto_hasta?>">
          </td>
         </tr>
        </table>   
       </td>
      </tr> 
     </table>  
    </td>
   </tr>
  </table>
<?="<b>$informar</b>"?>
    <table class="bordes" cellspacing="2" cellpadding="3" width="100%" align="center">
	<tr><td colspan="<? echo ($estado=="todos")?"8":"7"; ?>" id="ma">
	<table width="100%" border="0">
	 <tr id="ma">
      <td align="left">Total de Remitos <? if ($estado) echo ": ".$total_remitos; ?></td>
      <td align="right"><? echo $link_pagina; ?></td>
	 </tr>
	</table>
	</td></tr>
      <tr align="center" id="mo"> 
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"1","up"=>$up)) ?>'>
      <td style="cursor:hand;" title="Ordenar por Nº de Remito" width="15%">Nº Remito</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"6","up"=>$up)) ?>'>
      <td style="cursor:hand;" title="Ordenar por monto width="15%">Monto</td>
</a>
	<?
		if($estado=="todos") {
			echo "<td width='5%'>Estado</td>";
		}
	?>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"2","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por Nº de Factura" width="5%">Nº Factura</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"3","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por nombre de Cliente"  align="center">Cliente</td>
</a>        
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"4","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por usuario de creacion" width="15%">Creado por</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array("sort"=>"5","up"=>$up)) ?>'>
        <td style="cursor:hand;" title="Ordenar por fecha" width="15%">Fecha</td>
</a>                
	<?
		if ($estado=="terminados" || $estado=="recibidos" || $estado=="todos" || $estado=="anulados") { 
			echo "<td>&nbsp;</td>";
 		}
	?>
</tr>
<?
	while (!$remitos->EOF ) {
		$ref = encode_link("remito_nuevo.php",array("remito"=>$remitos->fields['id_remito'] ));
		//tr_tag($ref);
		echo "<tr $atrib_tr>";
        echo " <a href='$ref'>";
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['nro_remito']."</td>";
        ?>
         <td align='center' style='cursor:hand'>
          <table width="100%" align="center">
           <tr>
            <td align="left"><?=$remitos->fields['simbolo']?></td>
            <td align="right"><?=formato_money($remitos->fields['total'])?></td>
           </tr>
          </table>
         </td>
        <?
        if($estado=="todos") {
			echo "<td align='center' style='cursor:hand'>";
			switch ($remitos->fields['estado']) {
        		case 'a':
        		case 'A': echo "A";break;
        		case 'r':
			  	case 'R': echo "R";break;
        		case 'p':
			  	case 'P': echo "P";	break;
        		case 't':
			  	case 'T': echo "T";	break;
			}
			echo "</td>";
        } 
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['nro_factura']."</td>";
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['cliente']."</td>";
        echo "<td align='center' style='cursor:hand'>".$remitos->fields['usuario']."</td>";
        echo "<td align='center' style='cursor:hand'>".fecha($remitos->fields['fecha_remito'])."</td>";
		if ($estado=="terminados" || $estado=="recibidos" || $estado=="todos" || $estado=="anulados") {
			echo "<td align='center' width='3%' style='cursor:hand'>";
			switch ($remitos->fields['estado']) {
				case 'a':
 			  	case 'r': 
			  	case 't': 
			    $id_remito=$remitos->fields['id_remito'];
			    $sql_seg="select * from facturacion.items_remito where id_remito=$id_remito";
                $res_seg=sql($sql_seg)or fin_pagina();
            
            if ($res_seg->fields['id_renglones_oc'])
                 $seg=1; //el remito se creo desde el menu Produccion/entregas
            else $seg=0;
			
			  $link=encode_link("pdf.php", array("id_remito"=>$remitos->fields['id_remito'],"seg"=>$seg));	
		     echo "<A target='_blank' href='".$link."'><IMG src='$html_root/imagenes/pdf_logo.gif' height='16' width='16' border='0'> </a>";
			}
			echo "</td> </a>";
			
			
			echo "</tr>";
		}
  		$remitos->MoveNext();
	}
?>

<?
if ($parametros["volver_lic"]) {
		$ref = encode_link($html_root."/index.php",array("menu" => "licitaciones_view","extra" => array("cmd1"=>"detalle","ID"=>$parametros["volver_lic"])));
		echo "<tr><td align=center colspan=6><br><input type=button name=volver style='width:320;' value='Volver a los detalles de la licitacion' onClick=\"parent.document.location='$ref';\"></td></tr>\n";
	}
?>

</table>
</form>
<br><? echo fin_pagina();//"Página generada en ".tiempo_de_carga()." segundos.<br>"; ?>
</body>
</html>