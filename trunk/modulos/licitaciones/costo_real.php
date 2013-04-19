<?
/*
Author: Fernando

MODIFICADA POR
$Author: ferni $
$Revision: 1.16 $
$Date: 2007/06/26 17:51:23 $
*/

require_once("../../config.php");
require_once("../bancos/balances/funciones_balance.php");


$id_entrega_estimada = $_POST["id_entrega_estimada"] or $id_entrega_estimada = $parametros["id_entrega_estimada"];
$id_costo_real       = $_POST["id_costo_real"]       or  $id_costo_real = $parametros["id_costo_real"];

function formato_money_excel($number){
 return number_format($number, 2, ',', '');
}

//
function generar_nuevo_excel(){
     $sql = "select subido_lic_oc.nro_orden,cr.compra,entrega_estimada.id_licitacion,entrega_estimada.id_entrega_estimada,cr.costo_presunto,cr.dolar_presunto,cr.ganancia_presunto,
                    cr.costo_real,cr.transporte_real,cr.dolar_real,cr.ganancia_real,
                    cr.monto_factura,cr.balance_antes,cr.balance_despues,(cr.balance_despues - cr.balance_antes) as diferencia       
             from licitaciones.costo_real cr 
             join licitaciones.entrega_estimada using (id_entrega_estimada)
             join licitaciones.subido_lic_oc using (id_entrega_estimada)
             where cerrar_2 = 1";
     $res = sql($sql) or fin_pagina();
     
     $tabla = "  <html>
                 <head><title>Bajadas de Licitaciones</title></head> 
                 <body>
                 <table width=100% align=center border=1 cellpading=0 cellspacing=0>
                 <tr>
                   <td colspan=2 align=center>&nbsp;      </b></td>
                   <td colspan=3 align=center><b>Presunto </b></td>
                   <td colspan=4 align=center><b>Real     </b></td>
                   <td colspan=2 align=center><b>Facturas </b></td>
                   <td colspan=4 align=center><b>Balance  </b></td>
                </td>
                <tr>
                   <td align=center><b>OC             </b></td>
                   <td align=center><b>Compra         </b></td>
                   <td align=center><b>ID             </b></td>
                   <td align=center><b>Costo P.       </b></td>
                   <td align=center><b>Dolar P.       </b></td>
                   <td align=center><b>Ganancia P.    </b></td>
                   <td align=center><b>Costo R.       </b></td>
                   <td align=center><b>Transp.        </b></td>
                   <td align=center><b>Dolar R        </b></td>
                   <td align=center><b>Ganancia R.    </b></td>
                   <td align=center><b>Factura        </b></td>
                   <td align=center><b>Nro. Factura   </b></td>
                   <td align=center><b>Suba           </b></td>
                   <td align=center><b>Antes          </b></td>
                   <td align=center><b>Despues        </b></td>
                   <td align=center><b>Diferencia     </b></td>
                </tr>";
     

     for($i=0;$i<$res->recordcount();$i++){
     	
     	
     $sql = " select facturas.nro_factura
                from licitaciones.entrega_estimada 
                join licitaciones.subido_lic_oc using (id_entrega_estimada)
                join licitaciones.renglones_oc  using (id_subir)
                join facturacion.items_factura using (id_renglones_oc)
                join facturacion.facturas using (id_factura)
                where id_entrega_estimada=".$res->fields["id_entrega_estimada"] ."and facturas.estado<>'a'
                ";
     $fac = sql($sql) or fin_pagina();
     $fact_aux = "";
     for( $y = 0;$y<$fac->recordcount();$y++){
        	$fact_aux .= $fac->fields["nro_factura"]."&nbsp;";
        	$fac->movenext();
     }
       
     	
     	
     $diferencia = $res->fields["balance_despues"] - $res->fields["balance_antes"];
     $costo_presunto = $res->fields["costo_presunto"] * $res->fields["dolar_presunto"];
     $costo_real = $res->fields["costo_real"] * $res->fields["dolar_real"];     
     $suba = $res->fields["monto_factura"] - $costo_real;
     $costo_real = $costo_real + $res->fields["transporte_real"];
     	
     $tabla .= "<tr>";
     $tabla .= "<td>".$res->fields["nro_orden"]."</td>";
     $tabla .= "<td >".$res->fields["compra"]."</td>";
     $tabla .= "<td >".$res->fields["id_licitacion"]."</td>";
     $tabla .= "<td >".$costo_presunto."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["dolar_presunto"])."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["ganancia_presunto"])."</td>";
     $tabla .= "<td >".formato_money_excel($costo_real)."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["transporte_real"])."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["dolar_real"])."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["ganancia_real"])."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["monto_factura"])."</td>";
     $tabla .= "<td >".$fact_aux."</td>";
     $tabla .= "<td >".formato_money_excel($suba)."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["balance_antes"])."</td>";
     $tabla .= "<td >".formato_money_excel($res->fields["balance_despues"])."</td>";
     $tabla .= "<td >".formato_money_excel($diferencia)."</td>";
     $tabla .= "</tr>";
     	     
     $res->movenext();
     }
     
     $tabla.= "</body></html></table>";
     
     $path=UPLOADS_DIR."/bajadas_licitaciones";
     mkdirs($path);
     $temporal=$path."/bajadas_licitaciones.xls";  //linux
     $fp = fopen($temporal,"w+");
     fwrite($fp,$tabla);
     fclose($fp);     
	
}

if($_POST["excel"]){
 
 $FileNameFull = UPLOADS_DIR."/bajadas_licitaciones/bajadas_licitaciones.xls";
 $FileType     = "application/ms-excel";
 $FileSize     = filesize($FileNameFull);
	
 if (file_exists($FileNameFull))
			{

				FileDownload(0,"bajadas_licitaciones.xls",$FileNameFull,$FileType,$FileSize,0);
			}
			else
			{
				Mostrar_Error("Se produjo un error al intentar abrir el archivo comprimido");
			}
}//de la bajada del excel


$db->starttrans();

if($_POST['guardar_comentario']=='Guardar'){	
	$comentario_costo=$_POST['comentario_costo'];
	$sql="update licitaciones.costo_real set comentario='$comentario_costo' where id_costo_real=$id_costo_real";	
	sql($sql,'No se puede actualizar el comentario');
}

if ($_POST["eliminar"]) {
	    $sql = " delete from facturas_costo_real where id_costo_real = $id_costo_real";
	    sql($sql) or fin_pagina();	
        $sql = " delete from costo_real where id_costo_real = $id_costo_real";
        sql($sql) or fin_pagina();	
        $id_costo_real = "";	 
        ?>
        <script>
         window.opener.document.form.submit();
        </script>
        <?
} // del $_POST["eliminar"]


if ($_POST["cerrar_1"]){
	     $sql = "update costo_real set cerrar_1 = 1 where id_costo_real = $id_costo_real";
	     sql($sql) or fin_pagina();
}//del $_POST["cerrar_1"]

if ($_POST["cerrar_2"]){
	     $sql = "update costo_real set cerrar_2 = 1 where id_costo_real = $id_costo_real";
	     sql($sql) or fin_pagina();
	     
	     //genero el excel con todas las licitaciones con estado cerrar_2 = 1
	     generar_nuevo_excel();
	     $para='juanmanuel@coradir.com.ar';
	     $asunto='Excel con costos de licitaciones entregadas';
	     $contenido='Excel con costos de licitaciones entregadas';
	     $path=UPLOADS_DIR."/bajadas_licitaciones";
	     $adjunto='bajadas_licitaciones.xls';
	     //enviar_mail($para,$asunto,$contenido,$adjunto,$path,"","","");
}//del $_POST["cerrar_2"]


if ($_POST["guardar"]){
	
	$compra = $_POST["compra"];
	$costo_presunto    = $_POST["costo_presunto"];
	$dolar_presunto    = $_POST["dolar_presunto"];
	$ganancia_presunto = $_POST["ganancia_presunto"];
	$costo_real        = $_POST["costo_real"];
	$dolar_real        = $_POST["dolar_real"];
	$ganancia_real     = $_POST["ganancia_real"];
	$transporte_real   = $_POST["transporte_real"];
	$monto_factura     = $_POST["monto_factura"];
	
	
	if ($id_costo_real){
		//realizo el update
		
		$sql = " update costo_real set ";
		$sql .= " costo_presunto = $costo_presunto, dolar_presunto = $dolar_presunto,ganancia_presunto = $ganancia_presunto ";
		$sql .= " ,costo_real = $costo_real,dolar_real = $dolar_real, ganancia_real = $ganancia_real ";
		$sql .= " ,transporte_real = $transporte_real";		
        $sql .= " ,compra = '$compra'";
        $sql .= " ,monto_factura = $monto_factura";
        $sql .= " where id_costo_real = $id_costo_real";
        sql($sql) or fin_pagina();		
    }
	else{
		//realizo el insert
		$sql="select nextval('licitaciones.costo_real_id_costo_real_seq') as id_costo_real";
		$res = sql($sql) or fin_pagina();
		$id_costo_real = $res->fields["id_costo_real"];
		
		$campos ="id_costo_real,id_entrega_estimada,compra";
		$campos .=",costo_presunto,dolar_presunto,ganancia_presunto";
		$campos .=",costo_real,dolar_real,ganancia_real,transporte_real";
		$campos .=",monto_factura";
		
		
		$values ="$id_costo_real,$id_entrega_estimada,'$compra'";
		$values .=",$costo_presunto,$dolar_presunto,$ganancia_presunto";
		$values .=",$costo_real,$dolar_real,$ganancia_real,$transporte_real";
		$values .=",$monto_factura";
        $sql = " insert into costo_real ($campos) values ($values)";
        sql($sql) or fin_pagina();
	}
	
}//del if ($_POST["guardar"]){


if ($_POST["guardar"] || $_POST["eliminar_facturas"]) {
	
    //modifico las facturas
	//las cliqueadas las elimino
	/*
	$sql = "delete from facturas_costo_real where id_costo_real = $id_costo_real";
	sql($sql) or fin_pagina();
	
	$cantidad_facturas = $_POST["cantidad_facturas"];
	
	for($i = 0; $i<$cantidad_facturas; $i++){
		
		if ($_POST["id_factura_$i"]) {
			$id_factura = $_POST["id_factura_$i"]; 
			$campos = "id_costo_real,id_factura";
			$values = "$id_costo_real,$id_factura";
			$sql = "insert into facturas_costo_real ($campos) values ($values)";
			sql($sql) or fin_pagina();
		
		} //del if
	} //del for
	*/
} //del if ($_POST["aceptar"] || $_POST["eliminar_facturas"])



$db->completetrans();
$sql = " select id_costo_real from
         licitaciones.costo_real
         where costo_real.id_entrega_estimada =$id_entrega_estimada";
$res = sql($sql) or fin_pagina();
$id_costo_real = $res->fields["id_costo_real"];





$sql = " select licitacion.id_licitacion,entidad.nombre as nombre_entidad
         from licitaciones.entrega_estimada ee 
         join licitaciones.licitacion using (id_licitacion)
         join licitaciones.entidad using (id_entidad)
         where ee.id_entrega_estimada =$id_entrega_estimada";
$res = sql($sql) or fin_pagina();

$id_licitacion  = $res->fields["id_licitacion"];
$nombre_entidad = $res->fields["nombre_entidad"];


//traigo las ordenes de compra
$sql = " select nro_orden,id_subir,id_licitacion  
         from subido_lic_oc
    	 where id_licitacion=$id_licitacion and id_entrega_estimada=$id_entrega_estimada";
$orden_de_compra = sql($sql) or fin_pagina();     

//traigo las facturas
$sql = " select facturas.nro_factura,facturas.id_factura,moneda.simbolo,facturas.cotizacion_dolar,
                sum ((items_factura.precio*items_factura.cant_prod)) as monto
         from licitaciones.entrega_estimada 
         join licitaciones.subido_lic_oc using (id_entrega_estimada)
         join licitaciones.renglones_oc  using (id_subir)
         join facturacion.items_factura using (id_renglones_oc)
         join facturacion.facturas using (id_factura)
         join licitaciones.moneda using (id_moneda)
         where id_entrega_estimada=$id_entrega_estimada and facturas.estado<>'a'
         group by facturas.nro_factura,moneda.simbolo,facturas.id_factura,facturas.cotizacion_dolar";
$facturas = sql($sql) or fin_pagina(); 



if ($id_costo_real) {
	
	
	//traigo las facturas que dejos seleccionada
	/*
    $sql = " select facturas.nro_factura,facturas.id_factura,moneda.simbolo,facturas.cotizacion_dolar
	                from  licitaciones.facturas_costo_real	         
	                join facturacion.facturas using (id_factura)
	                join licitaciones.moneda using (id_moneda)
	                where id_costo_real=$id_costo_real 
	         group by facturas.nro_factura,moneda.simbolo,facturas.id_factura,facturas.cotizacion_dolar";
	$facturas = sql($sql) or fin_pagina(); 	
 	*/
	

	$sql = " select * from costo_real where id_costo_real = $id_costo_real";
	$res  = sql($sql) or fin_pagina();
	
	$compra = $res->fields["compra"];
	$costo_presunto    = $res->fields["costo_presunto"];
	$dolar_presunto    = $res->fields["dolar_presunto"];
	$ganancia_presunto = $res->fields["ganancia_presunto"];
	$costo_real        = $res->fields["costo_real"];
	$dolar_real        = $res->fields["dolar_real"];
	$ganancia_real     = $res->fields["ganancia_real"];
	$transporte_real   = $res->fields["transporte_real"];	
	$monto_factura     = $res->fields["monto_factura"];
	$nro_factura 	   = $res->fields["nro_factura"];
	$balance_antes     = $res->fields["balance_antes"];
	$balance_despues   = $res->fields["balance_despues"];
	$diferencia        = $balance_despues - $balance_antes;
	$cerrar_1          = $res->fields["cerrar_1"];
	$cerrar_2          = $res->fields["cerrar_2"];
	$comentario_costo  = $res->fields["comentario"];
	
	$datos_presunto = $costo_presunto * $dolar_presunto;
    	       
	$datos_real     = ($costo_real * $dolar_real) + $transporte_real;  
	$datos_real_aux = ($costo_real * $dolar_real);     

	$disabled  = "";	        

 if (!$cerrar_1 && !$cerrar_2) {
			 	   $disabled_cerrar_1 = "";
			 	   $disabled_cerrar_2 = " disabled";     
                   }
                   elseif($cerrar_1 && !$cerrar_2){
                   	   $disabled_cerrar_1 = " disabled";
                   	   $disabled_cerrar_2 = " ";                    	
                   }elseif ($cerrar_1 && $cerrar_2){
                   	   $disabled_cerrar_1 = " disabled";
                   	   $disabled_cerrar_2 = " disabled";                    	
                   	   
                   }

 }
 else{
 	
	
 	
        for($i=0;$i<$facturas->recordcount();$i++) {
          ($facturas->fields["cotizacion_dolar"])?$valor_dolar = $facturas->fields["cotizacion_dolar"]: $valor_dolar=1;	
	      $monto_factura += $facturas->fields["monto"] * $valor_dolar;		    
	     
	      $facturas->movenext();
        }  


        $facturas->move(0);
	    //traigo el valor de el dolar
	    $sql = " select valor from general.dolar_general ";
	    $res = sql($sql) or fin_pagina();
	    $dolar_real = $dolar_presunto = $res->fields["valor"];

        
		
	
        $sql =" select sum((renglon.cantidad * producto.cantidad * producto.precio_licitacion)) as costo_presunto,licitacion.valor_dolar_lic
		 		from licitaciones.entrega_estimada 
		 		join licitaciones.licitacion using (id_licitacion)
		 		join licitaciones.subido_lic_oc using (id_entrega_estimada)
		 		join licitaciones.renglones_oc  using (id_subir)
                                join licitaciones.renglon using (id_renglon)
                                join licitaciones.producto on renglon.id_renglon = producto.id_renglon 
		 		where id_entrega_estimada=$id_entrega_estimada
		 		group by licitacion.valor_dolar_lic";
		 				
		 $res_costo_presunto = sql($sql) or fin_pagina();
		 $costo_presunto  = $res_costo_presunto->fields["costo_presunto"];
		 $dolar_presunto  = $res_costo_presunto->fields["valor_dolar_lic"]; 
		 $datos_presunto  = number_format($costo_presunto * $dolar_presunto,2,".","");
		
		//calculo el costo real 
		$sql="select sum(precio_stock*en_produccion.cantidad) as monto
		      from stock.en_stock join general.producto_especifico using(id_prod_esp)
		      join stock.en_produccion using(id_en_stock)
		      where id_licitacion=$id_licitacion";
		$res_costo_real = sql($sql) or fin_pagina();
		$costo_real = $res_costo_real->fields['monto']; 
		$datos_real = ($costo_real * $dolar_real) + $transporte_real;       
		$datos_real = number_format($datos_real,2,".","");
		
		$disabled  = " disabled";
		$disabled_cerrar_1 = "disabled";
		$disabled_cerrar_2 = "disabled";
		$disabled_eliminar_facturas = "disabled";
} //del else

 


($costo_presunto)?$costo_presunto = number_format($costo_presunto,2,".",""): $costo_presunto = 0;
($dolar_presunto)?$dolar_presunto = number_format($dolar_presunto,2,".",""): $dolar_presunto = 0;
($costo_real)?$costo_real = number_format($costo_real,2,".",""): $costo_real = 0;
($dolar_real)?$dolar_real = number_format($dolar_real,2,".",""): $dolar_real = 0;
($transporte_real)?$transporte_real = number_format($transporte_real,2,".",""): $transporte_real = 0;
($monto_factura)?$monto_factura = number_format($monto_factura,2,".",""): $monto_factura = 0;

($ganancia_presunto)?$ganancia_presunto = number_format($ganancia_presunto,2,".",""): $ganancia_presunto = 0;
($ganancia_real)?$ganancia_real = number_format($ganancia_real,2,".",""): $ganancia_real = 0;

echo $html_header;
?>
<script>
//Calcular Ganancias
 function calcular_ganancias(){
 	var datos_real,datos_presunto,monto_factura;

 	datos_real     = parseFloat(document.form1.datos_real.value);
 	datos_presunto = parseFloat(document.form1.datos_presunto.value);
 	monto_factura  = parseFloat(document.form1.monto_factura.value);
 	
    if (datos_presunto>0) 	
 	  document.form1.ganancia_presunto.value = formato_BD ( parseFloat (datos_presunto/monto_factura) ) ;
 	if (datos_real>0)  
 	  document.form1.ganancia_real.value     = formato_BD ( parseFloat (datos_real/monto_factura) );
 }

 //Calcular datos
 function calcular_datos(){
 	var dolar,costo,transporte,datos;
 	
 	dolar = parseFloat(document.form1.dolar_presunto.value);
 	costo = parseFloat(document.form1.costo_presunto.value);
    document.form1.datos_presunto.value = formato_BD( parseFloat (dolar * costo) );

 	dolar = parseFloat(document.form1.dolar_real.value);
 	costo = parseFloat(document.form1.costo_real.value);
 	transporte = parseFloat(document.form1.transporte_real.value);
 	
 	factura = parseFloat(document.form1.monto_factura.value); 	
    document.form1.datos_real.value =formato_BD( parseFloat ((dolar * costo)+transporte));	
    datos = parseFloat(document.form1.datos_real.value);
    datos_aux = formato_BD( parseFloat ((dolar * costo)));	
    
    document.form1.suba.value = formato_BD(parseFloat(factura-datos_aux));
    
    
     
    calcular_ganancias();
 }
 
 
 function alert_eliminar(){
 var c 	
    c =	confirm('Esta Seguro que desea elimar el costo?');
    return c;
 } 
 
 function poner_disabled(check){
 	
 	if (check.checked == true )
 	                      check.disabled = true;
 	                      else
 	                      check.disabled = false;
 	                      
 }
 
</script>

<form name="form1" method="POST" action="costo_real.php">
<script languaje='javascript' src='<?=$html_root?>/lib/NumberFormat150.js'></script>

<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<input type="hidden" name="id_costo_real"       value="<?=$id_costo_real?>">
<input type="hidden" name="excel" value=0>

 <table width="70%" class="bordes" align="center" bgcolor="<?=$bgcolor3?>">
   <tr id=mo><td>COSTOS</td></tr>
   <tr>
    <td>
      <table width="100%" align="center">
        <tr>
         <td width="15%" align="left" id="ma_sf">ID:</td>
          <?$link = encode_link("licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$id_licitacion));?>
          <td>
           <table width="100%">
             <tr>
               <td width="15%"><a href="<?=$link?>" target="_blank"><b><?=$id_licitacion?></b></a></td>
               <td align="right"><img title="Excel con el listado de los costos reales" src="../../imagenes/excel.gif" style='cursor:hand;'  onclick='document.form1.excel.value=1;document.form1.submit();'></td>
             </tr>
           </table>
          </td>
        </tr>
        <tr>
          <td align="left" id="ma_sf">Entidad:</td>
          <td><b><?=$nombre_entidad?></b></td>
        </tr>
        <tr>
          <td align="left" id="ma_sf">OC:</td>
          <td>
          <b>
           <?
            for ($i=0;$i<$orden_de_compra->recordcount();$i++){
             $link = encode_link("../../lib/archivo_orden_de_compra.php",array("ID"=>$id_licitacion,"id_subir"=>$orden_de_compra->fields["id_subir"],"solo_lectura"=>1));
             echo "<a href='$link' target='_blank'>".$orden_de_compra->fields["nro_orden"]."</a> &nbsp;";
             $orden_de_compra->movenext();
            }//del for
           ?>
           </b>
          </td>
        </tr>
        <tr>
          <td align="left" id="ma_sf">Compra:</td>
          <td><input type="text" name="compra" size="90" value="<?=$compra?>"></td>
        </tr>
        <tr><td colspan="2" id=mo>Facturas</td></tr>        
        <tr>
          <td align="left" id="ma_sf">Facturas:</td>
          <td width="100%">
          <input type="hidden" name="cantidad_facturas" value="<?=$facturas->recordcount()?>">
            <?
            for ($i=0;$i<$facturas->recordcount();$i++){
            $link = encode_link("../facturas/factura_nueva.php",array("id_factura"=>$facturas->fields["id_factura"]));	
            //echo "<input type='checkbox' name='id_factura_$i' value='".$facturas->fields["id_factura"]."' class='estilos_check' checked>";            
            echo "<a href='$link' target='_blank'>";
            echo $facturas->fields["nro_factura"];
            echo "</a>";
            echo "&nbsp;";
            
            $facturas->movenext();
            } 
			?>          
		   <!--
			 <input type="submit" name="eliminar_facturas" value="Eliminar" <?=$disabled_eliminar_facturas?>>
			 -->
          </td>
        </tr>             
        <tr>
          <td align="left" id="ma_sf">Montos:</td>
          <td><input type="text" name="monto_factura" size="15" value="<?=$monto_factura?>" onchange="calcular_datos()"></td>
        </tr>   
        
        <tr>
          <td colspan="2">
            <table width="100%" align="center" class="bordes">
             <tr> 
               <td id="mo" colspan="4">Costo Presunto </td>
             </tr>
             <tr>
               <td width="25%" id="ma_sf">Costos: U$S</td>
               <td width="25%"><input type="text" name="costo_presunto" size="15" value="<?=$costo_presunto?>" onchange="calcular_datos()"></td>
               <td width="25%" id="ma_sf">Valor Dolar:</td>
               <td width="25%"><input type="text" name="dolar_presunto" size="15" value="<?=$dolar_presunto?>" onchange="calcular_datos()"></td>
             </tr>
             <?
             if ($monto_factura>0)
                    $ganancia_presunto = number_format($datos_presunto/$monto_factura,2,".","");
             ?>
             <tr>
               <td width="25%" id="ma_sf">Total Presunto:$</td>
               <td width="25%"><input type="text" size="15" name="datos_presunto" value="<?=$datos_presunto?>" readonly></td>
               <td width="25%" id="ma_sf">Ganancia:</td>
               
               <td width="25%"><input type="text" size="15" name="ganancia_presunto" value="<?=$ganancia_presunto?>"></td>                              
             </tr>
             <tr> 
               <td id="mo" colspan="4">Costo Real</td>
             </tr>             
             <tr>
               <td width="25%" id="ma_sf">Costos: U$S</td>
               <td width="25%"><input type="text" name="costo_real" size="15" value="<?=$costo_real?>" onchange="calcular_datos()"></td>
               <td width="25%" id="ma_sf">Valor Dolar:</td>
               <td width="25%"><input type="text" name="dolar_real" size="15" value="<?=$dolar_real?>" onchange="calcular_datos()"></td>
             </tr>
             <tr>
               <td width="25%" id="ma_sf">Tranporte: $</td>
               <td width="25%"><input type="text" size="15" name="transporte_real" value="<?=$transporte_real?>" onchange="calcular_datos()"></td>
               <td width="50%" colspan="2">&nbsp;</td>
             </tr>
             <?
             if ($monto_factura>0)
                    $ganancia_real = number_format($datos_real/$monto_factura,2,".","");
             ?>             
             <tr>
               <td width="25%" id="ma_sf">Total Real: $</td>
               <td width="25%"><input type="text" size="15" name="datos_real" value="<?=$datos_real?>"></td>
               <td width="25%" id="ma_sf">Ganancia:</td>
               <td width="25%"><input type="text" size="15" name="ganancia_real" value="<?=$ganancia_real?>"></td>                              
             </tr>
           </table>
          </td>
        </tr>
        <!-- Balance -->  
        <?
        $suba = number_format($monto_factura - $datos_real_aux,2,".","");
        ?>     
        <tr><td colspan="2" id=mo> Incremento del Balance</tr>                
        <tr>
          <td align="left" id="ma_sf">Suba:</td>
          <td><input type="text" name="suba" size="15" value="<?=$suba?>"></td>
        </tr>             
        <tr>
          <td align="left" id="ma_sf">Ok:</td>
          <td><input type="text" name="ok" size="15" value=""></td>
        </tr> 
        <tr>
          <td align="left" id="ma_sf">Antes:</td>
          <td><input type="text" name="antes" size="15" value="<?=number_format($balance_antes,2,".","")?>"></td>
        </tr> 
        <tr>
          <td align="left" id="ma_sf">Después:</td>
          <td><input type="text" name="despues" size="15" value="<?=number_format($balance_despues,2,".","")?>"></td>
        </tr>         
        <tr>
          <td align="left" id="ma_sf">Diferencia:</td>
          <td><input type="text" name="diferencia" size="15" value="<?=number_format($diferencia,2,".","")?>"></td>
        </tr>         
     </table>
    </td>
   </tr>
   
   <?if ($id_costo_real!=''){?>
   <tr bgcolor="<?=$bgcolor2?>">
     <td align="center">
     	<textarea name="comentario_costo" cols="95" rows="4"><?=$comentario_costo?></textarea> &nbsp;
       	<input type="submit" value="Guardar" name="guardar_comentario">
     </td>
   </tr>
   <?}?>
    
   <tr bgcolor="<?=$bgcolor2?>">
     <td align="center">
       <input type="submit" name="guardar"     style="width:100px" value="Guardar"     onclick="calcular_datos()">
       &nbsp;
       <input type="submit" name="eliminar"    style="width:100px" value="Eliminar"    <?=$disabled?> title="Borra el costo actual y recalcula los valores" onclick="return alert_eliminar()">      
       &nbsp;
       <input type="submit" name="cerrar_1"    style="width:100px" value="Cerrar 1"    <?=$disabled_cerrar_1?>>
       &nbsp;
       <input type="submit" name="cerrar_2"    style="width:100px" value="Cerrar 2"    <?=$disabled_cerrar_2?>>
       &nbsp;
       <input type="button" name="salir"       style="width:100px" value="Salir"      onclick="window.close()">       
     </td>
   </tr>
 </table>
</form>
<?

echo fin_pagina();
?>



