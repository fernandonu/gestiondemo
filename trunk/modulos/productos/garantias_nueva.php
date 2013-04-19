<?
/*
Author: Fernando

MODIFICADA POR
$Author: ferni $
$Revision: 1.5 $
$Date: 2007/03/02 19:59:37 $
*/

require_once("../../config.php");
require_once("../general/func_seleccionar_cliente.php");

$id_garantia_producto = $parametros["id_garantia_producto"] or $id_garantia_producto = $_POST["id_garantia_producto"];
$viene_de_listado     = $parametros["viene_de_listado"] or $viene_de_listado=$_POST["viene_de_listado"];
$primera_vez          = $parametros["primera_vez"];
$modo				  = $parametros["modo"];
$db->starttrans();

$fecha=date("Y-m-d H:m:s");
$usuario=$_ses_user['id'];

if ($_POST['cambio_entidad']=="si_cambio") {
$id_entidad=$_POST['id_entidad'];	
   actualizar_clientes_mas_usuados($id_entidad,$usuario,$fecha);
}//de que se cambio la entidad


if ($_POST["aceptar"]){
    $descripcion     = $_POST["descripcion"];
    $cantidad        = $_POST["cantidad"];
    $observaciones   = $_POST["observaciones"];
    $nombre_entidad  = $_POST["entidad"];	
    $id_prod_esp     = $_POST["id_prod_esp"];
    $cantidad        = $_POST["cantidad"];	
  	$tiempo_garantia = $_POST["garantia"];
	$fecha           = date("Y-m-d h:i:s");
	$usuario 		 = $_ses_user["name"];
	$id_entidad      = $_POST["id_entidad"];
	
	
	$observaciones = eregi_replace("'","",$observaciones);
	$observaciones = eregi_replace("\"","",$observaciones);
	
	if ($id_garantia_producto){
	//realizo el update		
			$sql = "delete from garantia_prod_numeros_series where id_garantia_producto = $id_garantia_producto";
			sql($sql) or fin_pagina();

			$id_subir=$_POST['oc'];
		    if ($id_subir!=''){		    	
		    	$sql_lic="select id_licitacion from subido_lic_oc where id_subir=$id_subir";
		    	$result_lic=sql($sql_lic,'No se puede ejecutar para vincular oc');
		    	$id_licitacion=$result_lic->fields['id_licitacion'];
		    }
		    else {
		    	$id_subir=0;
		    	$id_licitacion=0;
		    }
		      
			$sql = " update garantia_producto set id_prod_esp = $id_prod_esp,tiempo_garantia = $tiempo_garantia ,
			         observaciones = '$observaciones', id_licitacion = '$id_licitacion', id_subir = '$id_subir'";
			if ($id_entidad){
				$sql.=",id_entidad = '$id_entidad'"; 
			}
			         
			$sql .=" where id_garantia_producto = $id_garantia_producto";
		    sql($sql) or fin_pagina();
		    
		    for($i=0;$i<$cantidad;$i++){
				    		$nro_serie = $_POST["nro_serie_$i"];    		
				    		$campos = "id_garantia_producto,nro_serie";
				    		$values = "$id_garantia_producto,'$nro_serie'";
				    		$sql="insert into garantia_prod_numeros_series ($campos) values ($values)";
				    		sql($sql) or fin_pagina();
		    }//del for
		  	$campos = "id_garantia_producto,tipo,usuario,fecha";
		   	$values = "$id_garantia_producto,'Modificacion','$usuario','$fecha'";
		   	$sql="insert into log_garantia_producto ($campos) values ($values)";
		   	sql($sql) or fin_pagina();
		    $refrescar = 0;
			
   	   } //del then
	   else{
			//realizo el insert	
  	        $sql = "select nextval('general.garantia_producto_id_garantia_producto_seq') as id_garantia_producto ";
		    $res = sql($sql) or fin_pagina();
		    $id_garantia_producto = $res->fields["id_garantia_producto"];
		 	

		    $id_subir=$_POST['oc'];
		    if ($id_subir!=''){		    	
		    	$sql_lic="select id_licitacion from subido_lic_oc where id_subir=$id_subir";
		    	$result_lic=sql($sql_lic,'No se puede ejecutar para vincular oc');
		    	$id_licitacion=$result_lic->fields['id_licitacion'];
		    }
		    else {
		    	$id_subir=0;
		    	$id_licitacion=0;
		    }
		    
		    $campos ="id_garantia_producto,id_prod_esp,tiempo_garantia,observaciones,fecha_creacion,id_licitacion,id_subir";	   
		   	$values="$id_garantia_producto,$id_prod_esp,$tiempo_garantia,'$observaciones','$fecha',$id_licitacion,$id_subir";
		    
		   	if ($id_entidad){
		   		 $campos .=",id_entidad";
		   		 $values .=",$id_entidad";
		   	}	
		    $sql = "insert into garantia_producto ($campos) values ($values)";
		    sql($sql) or fin_pagina();
		    	
		    for($i=0;$i<$cantidad;$i++){
		    		$nro_serie = $_POST["nro_serie_$i"];    		
		    		$campos = "id_garantia_producto,nro_serie";
		    		$values = "$id_garantia_producto,'$nro_serie'";
		    		$sql="insert into garantia_prod_numeros_series ($campos) values ($values)";
		    		sql($sql) or fin_pagina();
		    }//del for
		    	
		    	
		    //inserto el log
		    	
		   	$campos = "id_garantia_producto,tipo,usuario,fecha";
		   	$values = "$id_garantia_producto,'Creacion','$usuario','$fecha'";
		   	$sql="insert into log_garantia_producto ($campos) values ($values)";
		   	sql($sql) or fin_pagina();
            $refrescar = 1;	
	}
} //del if
$db->completetrans();

if ($refrescar){
	
?>
	<script>
	  window.opener.document.form1.submit();
	  window.close();
	</script>	
<?	
}


$garantia = array("1","2","3","4","5","6","7","8","9","12","18","24","36","48","60");

$onclick_cargar="window.opener.document.form1.id_prod_esp.value=document.all.id_producto_seleccionado.value;
                 window.opener.document.form1.descripcion.value = document.all.nombre_producto_elegido.value;
                 window.close();";

$onclickelegir="window.opener.document.form1.id_entidad.value =document.all.select_entidad.options[document.all.select_entidad.selectedIndex].value;
                window.opener.document.form1.entidad.value = document.all.select_entidad.options[document.all.select_entidad.selectedIndex].text;
                window.close();
                ";


if ($id_garantia_producto){
	 
	$sql = "select gp.id_garantia_producto,gp.tiempo_garantia,gp.observaciones,
	               pe.descripcion,gp.id_prod_esp,entidad.id_entidad,entidad.nombre,
	               gp.fecha_creacion,gp.id_licitacion,gp.id_subir
	        from general.garantia_producto gp 
	        join general.producto_especifico pe using (id_prod_esp)
	        left join licitaciones.entidad using (id_entidad)	        
	        where id_garantia_producto = $id_garantia_producto";
	
	$res = sql($sql) or fin_pagina();
	$id_prod_esp     = $res->fields["id_prod_esp"];
	$descripcion     = $res->fields["descripcion"];
	$tiempo_garantia = $res->fields["tiempo_garantia"];
	$observaciones   = $res->fields["observaciones"];
	$id_entidad      = $res->fields["id_entidad"];
	$nombre_entidad  = $res->fields["nombre"];
	$fecha_creacion  = $res->fields["fecha_creacion"];
	$id_licitacion   = $res->fields["id_licitacion"];
	$id_subir        = $res->fields["id_subir"];	
	
	$sql = "select nro_serie from garantia_prod_numeros_series s 
	        where id_garantia_producto = $id_garantia_producto";
	$numeros_series = sql($sql) or fin_pagina();	
	$cantidad=$numeros_series->recordcount();
	
	
	//traigo los log
	$sql = "select * from general.log_garantia_producto 
	        where id_garantia_producto = $id_garantia_producto
	        order by fecha DESC";
	$log = sql($sql) or fin_pagina();
}


//por si desea cambiar la cantidad de elementos
if ($_POST["aceptar_cantidad"]) {
	
          $id_prod_esp     = $_POST["id_prod_esp"];
          $tiempo_garantia = $_POST["garantia"];
	      $descripcion     = $_POST["descripcion"];
          $cantidad        = $_POST["cantidad"];
          $observaciones   = $_POST["observaciones"];
          $id_entidad      = $_POST["id_entidad"];
          $nombre_entidad  = $_POST["entidad"];
          $viene_por_post  = 1;
}

if (!$cantidad){
	$cantidad = 1;
}



echo  $html_header;
?>
<script>
  function control_datos(){
  	var cant,sent;

  	
  	if(document.form1.id_prod_esp.value==""){
  		alert('Debe seleccionar un Producto');
  		return false;
  	}  	
  	cant = parseInt(document.form1.cantidad.value);
  	
  	for(i=0;i<cant;i++){
  		sent = eval("document.form1.nro_serie_"+i);
  		if (sent.value==""){
  		 alert('Falta Número de Serie '+(i+1));
  		 return false;	
  		}
  		
  	}
  	
  return true;	

  }
  
var wcliente=0;
function cargar_cliente() {
 document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;

 //indica que se debe actualizar los clientes mas usuados
 document.all.cambio_entidad.value="si_cambio";
}
  
function cargarSeries(){
	var arregloaux = new Array();
	var arreglo = new Array();
	var tamArreglo;
	arregloaux=window.clipboardData.getData("Text");
	arreglo=arregloaux.split("\n");
	tamArreglo=arreglo.length;
	var i=eval ("document.all.rango.value");
	var j=0;
	var error=0;
	var errorCont=0;
	while (j<tamArreglo-1){
		var res = eval("document.all.nro_serie_"+i);
		
		if (typeof (res)=="undefined"){
			error=1;
			errorCont++;			
		}
		else{
			res.value=arreglo[j];
		}
		i++;
		j++;
	}
	if (error==1){
		alert ("La Cantidad de Datos del Portapapeles es MAYOR a los Cuadros de Textos Disponibles en la Pagina.\nLo Sobrepasa en "+errorCont+" Fila/s.");
	} 
}  
</script>
<form name="form1" method="POST" action="garantias_nueva.php">
<input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
<input type="hidden" name="id_garantia_producto" value="<?=$id_garantia_producto?>">
<input type="hidden" name="viene_de_listado" value="<?=$viene_de_listado?>">
<input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
<input name="cambio_entidad" type="hidden" value="no_cambio">
<?
if ($log && $log->recordcount()){  
?>
<div align="center">
<div style="display:'visible';width:85%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;'?> " id="tabla_logs" >

<table width="100%" align="center" class="bordes" bgcolor="<?=$bgcolor2?>">
  <tr id=mo><td>Logs</td></tr>
  <tr><td>
     <table width="100%" align="center">
     <tr id=ma>
       <td>Fecha  </td>
       <td>Usuario</td>
       <td>Acción </td>
     </tr>
	  <?
	  for ($i=0;$i<$log->recordcount();$i++){
	  ?>
        <tr>
          <td align="center"><?=fecha($log->fields["fecha"])." ".substr($log->fields["fecha"],11,8) ?></td>
          <td align="left"><?=$log->fields["usuario"]?></td>
          <td align="center"><?=$log->fields["tipo"]?></td>
        </tr>
	  <?	
	  $log->movenext();
	  }
	  ?>
     </table>
   </td></tr>
</table> 
</div>
</div>
<?}?>

<table width="85%" align="center" class="bordes" bgcolor="<?=$bgcolor2?>">
    <tr id=mo>  
      <td>Nueva Garantía</td>
    </tr>
    <?if ($fecha_creacion){?>
    <tr>
      <td align="center"><font color="Red" size="4"><b>Fecha Creación : <?=fecha($fecha_creacion)?></b></font></td>
    </tr>
    <?}?>
    <tr>
      <td width="100%" align="center" >
        <table width="100%" align="center">
        <?
        $link_producto = encode_link("listado_productos_especificos.php",array("onclick_cargar"=>$onclick_cargar,"pagina_viene"=>"garantias_nueva.php"));
        ?>
         <tr>
            <td id="ma_sf" width="15%" align="left">Producto</td>
            <td>
            <input type="text" name="descripcion" value="<?=$descripcion?>" size="60">
            <input type="button" name="traer" value="Producto" onclick="window.open('<?=$link_producto?>')">
            </td>
            <td align="center">
            	<input type="button" value="Asignar Orden de Compra" name="asignar_oc" onclick="window.open('garantia_sel_ord_compra.php')">
            	<input type="hidden" value="<?=$id_subir?>" name="oc"> 
            </td>
          </tr>
         <tr>
            <td id="ma_sf"  align="left">Entidad</td>
            <td>
            <input type="text" name="entidad" value="<?=$nombre_entidad?>" size="60">
            <input type="button" name="clientes" value="Elegir cliente"  title="Permite elegir cliente " 
                 onclick="if (wcliente==0 || wcliente.closed)
	                                    wcliente=window.open('<?=encode_link('../general/seleccionar_clientes.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
                       else
	                   if (!wcliente.closed)     
	 	               wcliente.focus();
"
                 >

            </td>
            <td align="center">            	
            	<?if ($id_subir!=0){
            		$sql="select nro_orden from licitaciones.subido_lic_oc where id_subir=$id_subir";
            		$result_subir=sql($sql,'Error en traer la orden de compra del cliente');
            		$nro_orden=$result_subir->fields['nro_orden'];            		
            		$link2=encode_link('../licitaciones/licitaciones_view.php',array("ID"=>$id_licitacion,"cmd1"=>"detalle"));
            		$link=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$id_subir,"solo_lectura"=>1));?>
		            <b>Asociado al ID: </b>
            		<a target="_blank" href="<?=$link2?>"><b><?=$id_licitacion;?>. </b></a>
            		<b> y a la Orden de Compra </b>
		            <a target="_blank" href="<?=$link?>"><b><?=$nro_orden;?>.</b></a>	                        		
            	<?}
            	else{?>
            		<font color="Red"><b>No tiene Orden de Compra Asignada</b></font>
            	<?}?>              	           	
            </td>
          </tr>          
          <tr>
            <td id="ma_sf" align="left">Cantidad</td>
            <td>
               <input type="text" name="cantidad" value="<?=$cantidad?>" size="5">
               &nbsp;
               <input type="submit" value="Aceptar" name="aceptar_cantidad">
            </td>            
          </tr>
          <tr>
            <td id="ma_sf" align="left">Garantía</td>
            <td>
              <select name="garantia">
              <?
              for($i=0;$i<sizeof($garantia);$i++){              	
              	($garantia[$i]==$tiempo_garantia)?$selected=" selected":$selected="";
              ?>
                <option value="<?=$garantia[$i]?>" <?=$selected?>> <?=$garantia[$i]?> Meses </option>  
              <?
              }
              ?>
              </select>
            </td>
          </tr>
                    
          <!--empieza carga datos del portapapeles-->
          <tr>
           <td colspan="3" align="center">
			 <table width="80%" align="center" class="bordes">
			  <tr align="center" id=ma_sf>
			  	  <td align="center" colspan="2">
			  	  	<strong>
			  	  	<font color="Red">
			  	  	Presionar el Boton despues de Copiar los Datos de Excel
			  	  	</font>
			  	  	</strong>  	  
			  	  </td>
			  </tr>
			  <tr align="center">
			  	<td align="center" colspan="2">
			  		<b>Ingrese Numero de Inicio:&nbsp;</b>
			  		<input type="text" value="0" name="rango" title="Ingrese el Numero Desde" size="4">
			  	</td>
			  </tr>
			  
			  <tr align="center">
			  	  <td align="center" colspan="2">
			  	  	<input type="button" name="cargar_series" value="Cargar Series del Portapapeles" onclick="cargarSeries();">
			  	    <br>
			  	  </td>
			  </tr>
			 </table>
		  </td>
		</tr>
		<!--termina carga datos del portapapeles-->
		
          <tr>
           <td colspan="3" align="center">
           <table width="80%" align="center" class="bordes">
             <tr id=mo>
               <td colspan="3">Números de Serie</td>
             </tr>
             <?
	          for($i=0;$i<$cantidad;$i++){
	          	
	          if ($viene_por_post)
	               $numero=$_POST["nro_serie_$i"];
	               elseif($modo!="nueva"){
	               $numero=$numeros_series->fields["nro_serie"]; 
	               $numeros_series->movenext();
	               }
	         ?>
	          <tr <?=atrib_tr()?>>
	            <td align="left">
	            <b>Número <?=($i+1)?></b>
	            </td>
	            <td align="left">
	            <input type="text" name="nro_serie_<?=$i?>" value="<?=$numero?>" size="75">
	            </td>
	          </tr>
          <?
          } //del for
          ?>
           </table> 
           </td>
          </tr>
          <tr>
            <td colspan="3" id="ma_sf" align="left">Observaciones</td>
          </tr>        
          <tr>
            <td colspan="3"align="left">
            <textarea name=observaciones rows="7" style="width:90%"><?=$observaciones?></textarea>
            </td>
          </tr>            
        </table>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="submit" name="aceptar" value="Aceptar" onclick="return control_datos();">
        &nbsp;
        <?
        if ($viene_de_listado){
        ?>
        <input type="button" name="volver" value="Volver"  onclick="location.href='<?=encode_link("garantias_listado.php",array(""))?>'">
        <? }else{?>
        <input type="button" name="cancelar" value="Cancelar" onclick="window.close()">
        <?}?>
      </td>
    </tr>
  </table>
  
</form>
<?
echo fin_pagina();
?>