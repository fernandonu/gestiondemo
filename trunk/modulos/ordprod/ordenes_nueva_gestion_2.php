<?php

/*

AUTOR: Carlitos 

MODIFICADO POR:

$Author: nazabal $

$Revision: 1.8 $

$Date: 2007/03/27 21:31:46 $



*/



require_once("../../config.php");

//////////////////////////////////////////// GABRIEL ////////////////////////////////////////

$gag_modo=$parametros["gag_cmd"] or $gag_modo=$_POST["gag_cmd"];

$gag_id_renglon=$parametros["gag_id_renglon"] or $gag_id_renglon=$_POST["gag_id_renglon"];

/////////////////////////////////////////////////////////////////////////////////////////////





//recupero el modo

if ($_GET["modo"]) 

                 $modo=$_GET["modo"];

                 else 

                 $modo=$parametros["modo"] or $modo=$_POST["modo"];







$volver=$_POST["volver"] or $volver=$parametros["volver"];

if ($parametros["pagina"]) $modo="asociado_lic";

if (!$modo) $modo="asociar";

if ($modo=="nuevo" || $modo=="modificar") {

    ?>

	<script src='../../lib/NumberFormat150.js'></script>

	<script src='../../lib/checkform.js'></script>

	<script>

    

		var wcliente=0;

		var wproductos=0;

		var wproveedor=0;

        

        function confirmar()

        {

        if (document.all.pasa_cantidad.value>document.all.cant_que.value)

           {                   

            if(confirm ("Esta seguro de pasar a Inspección? No cumple con la cantidad de verificaciones correctas requeridas"))return true;

             else return false;

            } 

        }  // de la funcion confirmar               







		

		function cargar_cliente()

		{

			document.all.id_entidad.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;

			document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;

			if (wcliente.document.all.chk_direccion.checked)

  	 		            document.all.direccion.value=wcliente.document.all.direccion.value;

			document.all.lugar_entrega.value=wcliente.document.all.direccion.value;

		}

		function seleccionar() {

			if (document.all.id_ensamblador.value==0) 

                              document.all.generar.disabled=1;

			                  else 

                              document.all.generar.disabled=0;

		}

		function nuevo_item() {

			pagina_prod='<?=encode_link('../productos/listado_productos_especificos.php',array('onclick_cargar'=>"window.opener.cargar()",

                                                                                              'onclick_salir'=>'window.close()',

                                                                                              'pagina_viene' => 'ordenes_nueva.php',

                                                                                              'cambiar'=>1))?>';

			if (wproductos==0 || wproductos.closed)

				wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');

		}

  		function cargar() {

        

			var items=eval("document.all.item");

			items.value++;

			var fila=document.all.productos.insertRow(document.all.productos.rows.length);

			fila.id='ma';

			fila.insertCell(0).innerHTML="<img src='../../imagenes/up.gif' style='cursor: hand;' id=imagen alt='Subir el producto' onclick='subir(this.parentNode.parentNode.rowIndex);'><input type=hidden name=orden_"+items.value+" id='orden' value="+items.value+"><input type=hidden name='id_"+items.value+"' value='"+wproductos.document.all.id_producto_seleccionado.value+"'><input type=checkbox name=chk value=1>";

			fila.insertCell(1).innerHTML="<input size=3 name=canti_"+items.value+" value=1>";

			fila.insertCell(2).innerHTML="<textarea rows=2 style='width:93%' name=desc_"+items.value+">"+wproductos.document.all.descripcion_producto_elegido.value+"</textarea>";

  		    var text=new String(items.value);

			document.all.item.value=text;

			document.all.eliminar.disabled=0;

			document.all.guardar.disabled=0;

            

		}



		function borrar_items() {

			var i=0;

			//var items=eval('document.all.item');

			while ((typeof(document.all.chk)!='undefined') && (typeof(document.all.chk.length)!='undefined') && (i<document.all.chk.length)) {

				if ((typeof(document.all.chk[i])!='undefined') && (document.all.chk[i].checked)) {

					document.all.productos.deleteRow(i+1);

					//items.value--;	

				}

				else 

					i++;

			}



			if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)

			{

				document.all.productos.deleteRow(1);

				//items.value--;

				document.all.eliminar.disabled=1;

				document.all.modo.disabled=1;

			}

			else if (typeof(document.all.chk)=='undefined') {

				document.all.eliminar.disabled=1;

				document.all.modo1.disabled=1;

			}

			//var text=new String(items.value);

			//document.all.item.value=text;

		}

        

        

		function subir(rownum) {

			//alert(rownum);

			if (rownum>1) {

				document.all.orden[rownum-2].value=rownum

				document.all.orden[rownum-1].value=rownum-1

				var filas=document.all.productos.rows;

				var fila=filas[rownum];

				var filanueva=document.all.productos.insertRow(rownum-1);

				filanueva.id='ma';

				filanueva.insertCell(0).innerHTML=fila.cells(0).innerHTML;

				filanueva.insertCell(1).innerHTML=fila.cells(1).innerHTML;

				filanueva.insertCell(2).innerHTML=fila.cells(2).innerHTML;

                

				//filanueva.insertCell(3).innerHTML=fila.cells(3).innerHTML;

				//filanueva.insertCell(4).innerHTML=fila.cells(4).innerHTML;

				//filanueva.insertCell(5).innerHTML=fila.cells(5).innerHTML;

				document.all.productos.deleteRow(rownum+1);

				//alert(document.all.orden[rownum-2].value);

			}

		}

        



        function control_datos(){

        

        if (document.all.fechaentrega.value=="")

            {

            alert('Debe Ingresar una Fecha de Entrega');

            return false;

            } 

        if (document.all.fechainicio.value=="")

            {

            alert('Debe Elegir una fecha de inicio');

            return false;

            }             

        if (document.all.pasa_cliente.value=="")

            {

            alert('Debe elegir un Cliente');

            return false;

            }             

        if (document.all.id_ensamblador.value==0 )

            {

            alert('Debe Seleccionar un ensamblador');

            return false;

            }             

        

        return true;

        }

        

   function alternar_color(obj,color) {

		color=color.toLowerCase();

		if (obj.style.backgroundColor == color)



			obj.style.backgroundColor = ""

		else

			obj.style.backgroundColor = color

	}

		</script>

    <?

    echo $html_header;



	$nro_orden     = $_POST["nro_orden"] or $nro_orden = $parametros["nro_orden"] or $nro_orden = $_GET["nro_orden"];

	$id_licitacion = $parametros["id_licitacion"];

	$id_renglon    = $parametros["id_renglon"];



	cargar_calendario();

	if ($modo=="modificar") {

		$sql="select titulo_etiqueta,descripcion_etiqueta,orden_de_produccion.id_entidad,orden_de_produccion.id_licitacion,orden_de_produccion.id_renglon,orden_de_produccion.id_ensamblador

			        ,orden_de_produccion.fecha_inicio,orden_de_produccion.fecha_entrega,orden_de_produccion.lugar_entrega

			        ,orden_de_produccion.nserie_desde,orden_de_produccion.nserie_hasta,orden_de_produccion.desc_prod as titulo,orden_de_produccion.cantidad

			        ,orden_de_produccion.comentario,orden_de_produccion.estado,adicionales,rechazada,id_sistema_operativo

			        ,entidad.nombre,entidad.direccion,renglon.codigo_renglon from orden_de_produccion 

			        left join entidad using(id_entidad) 

			        left join renglon using(id_renglon) where nro_orden=$nro_orden";

			

		$licitacion=sql($sql) or fin_pagina();

		if ($licitacion->RecordCount()>0) $estfield="readonly";

		else $estfield="";



	}

	elseif ($id_licitacion && $id_renglon) {

		$sql="select producto.id_producto,producto.tipo,producto.marca,producto.modelo,productos.desc_gral,producto.precio_licitacion as preicio,

                    renglon.ganancia,renglon.titulo,renglon.cantidad,

                    entidad.nombre,entidad.direccion,entidad.id_entidad,renglon.codigo_renglon from producto 

                    left join productos USING (id_producto) 

                    left join renglon USING (id_renglon) 

                    left join licitacion USING (id_licitacion) 

                    left join entidad USING (id_entidad) 

                    where id_renglon=$id_renglon";

		$licitacion=sql($sql) or fin_pagina();

		if ($licitacion->RecordCount()>0) $estfield="readonly";

		else $estfield="";

		//print_r($licitacion->fields);

	}

	$entidad=$_POST["id_entidad"] or $entidad=$licitacion->fields["id_entidad"];

	$id_sistema_operativo=$_POST["sist_instalado"] or $id_sistema_operativo=$licitacion->fields["id_sistema_operativo"];

  $fechainicio = $_POST["fechainicio"] or $fechainicio=fecha($licitacion->fields["fecha_inicio"]) or $fechainicio=date("d/m/Y");

	$fechaentrega = $_POST["fechaentrega"] or $fechaentrega=fecha($licitacion->fields["fecha_entrega"]);

	$comentario = $_POST["comentario"] or $comentario=$licitacion->fields["comentario"];

	$cliente = $_POST["cliente"] or $cliente=$licitacion->fields["nombre"];

	$direccion = $_POST["direccion"] or $direccion=$licitacion->fields["direccion"];

	$lugar_entrega = $_POST["lugar_entrega"] or $lugar_entrega=$licitacion->fields["lugar_entrega"] or $lugar_entrega=$licitacion->fields["direccion"];

	$desc_prod = $_POST["desc_prod"] or $desc_prod=$licitacion->fields["titulo"];

	$cant_prod  =$_POST["cant_prod"] or $cant_prod=$licitacion->fields["cantidad"];

	$serialp = $_POST["serialp"] or $serialp=$licitacion->fields["nserie_desde"];

	$serialu = $_POST["serialu"] or $serialu=$licitacion->fields["nserie_hasta"];

	$adicionales = $_POST["adicionales"] or $adicionales=$licitacion->fields["adicionales"];

	$rechazada = $_POST["rechazada"] or $rechazada=$licitacion->fields["rechazada"];

	$id_licitacion = $parametros["id_licitacion"] or $id_licitacion=$licitacion->fields["id_licitacion"];

	$codigo_renglon = $parametros["codigo_renglon"] or $codigo_renglon=$licitacion->fields["codigo_renglon"];

  if (!$id_renglon) $id_renglon=$parametros["id_renglon"] or $id_renglon=$licitacion->fields["id_renglon"];

	$estado = $parametros["estado"] or $estado=$licitacion->fields["estado"];

  $titulo_etiqueta = $parametros["titulo_etiqueta"] or $titulo_etiqueta=$licitacion->fields["titulo_etiqueta"];

  $titulo_renglon = $licitacion->fields["titulo"] or $titulo_renglon=$_POST["h_titulo_renglon"];

  $descripcion_etiqueta = $parametros["descripcion_etiqueta"] or $descripcion_etiqueta=$licitacion->fields["descripcion_etiqueta"];

	if (!$msg) $msg=$parametros["msg"] or $msg=$_POST["msg"];

	if ($nro_orden) {

		$sql="SELECT fecha,descripcion,nombre,apellido from log_ord_prod 

			         left join usuarios using(id_usuario) 

                     where nro_orden=$nro_orden order by fecha DESC";

		$log=sql($sql) or fin_pagina();

		echo "<div style='overflow:auto;";

		if ($log->RowCount() > 3) echo "height:60;";

		echo "'>\n";

		echo "<table width='95%' cellspacing=0 border=1 bordercolor=#E0E0E0 align='center' bgcolor=#cccccc>\n";

		while ($fila=$log->FetchRow()) {

			echo "<tr>";

			echo "<td height='20' nowrap>Fecha ".$fila["descripcion"]." ".date("j/m/Y H:i:s",strtotime($fila["fecha"]))."</td>\n";

			echo "<td nowrap > Usuario : ".$fila["nombre"]." ".$fila["apellido"]."</td>\n";

			echo "</tr>\n";

		}

		echo "</table></div>\n";

	}

    if ($msg) aviso($msg);

    $link=encode_link("etiquetas.php",array("nro_orden"=>$nro_orden));      

    ?>

    <script>



           function control_etiquetas()

               {

               if ((document.all.texto_descripcion.value=='') || (document.all.texto_titulo.value==''))

                     {

                     alert('No puede generar las etiquetas sin el titulo o la descripción de los productos');

                     return false;

                     }

                     else 

                     if (document.all.texto_titulo.value.length>48)

                     alert('No puede ingresar un titulo con mas de 48 caracteres');

                     else

                     {

                     window.location='<?=$link;?>&titulo_etiqueta='+document.all.texto_titulo.value+'&descripcion_etiqueta='+document.all.texto_descripcion.value;

                     return true;

                     }

               } 

    </script>

	<form name='frm_guardar' action='ordenes_nueva.php' method='POST'>

	<input type=hidden name="gag_cmd" value='<?=$gag_modo?>'>

	<input type=hidden name=nro_orden value='<?=$nro_orden?>'>

	<input type=hidden name=id_entidad value='<?=$entidad?>'>

	<input type=hidden name=volver value='<?=$volver?>'>

	<input type=hidden name=id_renglon value='<?=$id_renglon?>'>

	<input type=hidden name=id_licitacion value='<?=$id_licitacion?>'>

	<input type=hidden name=nro_orden value='<?=$nro_orden?>'>

    <table width='95%' align='center' class=bordes>

	<tr id=mo>

        <td colspan=2>

        <?

	    if ($modo=="modificar") $tit="Modificar Orden de Producción Nro: $nro_orden";

	                       else $tit="Nueva Orden de Producción";

        ?>

	    <font size=3><?=$tit?></font>

	    </td>

    </tr>

    <?

	if ($rechazada) {

    ?>    

		<tr bgcolor=$bgcolor_out>

                <td colspan=2>

		        <font size=2><font color=yellow>ADVERTENCIA:</font> La orden fue rechazada.<br>

		        <br>Motivo del rechazo: <b><?=$rechazada?></b></font>

		        </td>

        </tr>

    <?    

	}

    ?>

	<tr >

            <td>

            <?

            if($id_licitacion){

            $consult="select  lider,u1.apellido||', '||u1.nombre as lider_nombre from licitaciones.licitacion l

	                      left join sistema.usuarios u1 on (lider=u1.id_usuario)   

                          where id_licitacion=$id_licitacion";

            $ejecuta=sql($consult,"no se pudo recuperar el lider") or fin_pagina();

            }

	        $link2=encode_link('../licitaciones/licitaciones_view.php',array("ID"=>$id_licitacion,"cmd1"=>"detalle"));

	        $est=array(

		        "A"=>"Autorizada",

		        "AN"=>"Anulada",

		        "T"=>"Terminada",

		        "PA"=>"Para Autorizar",

		        "P"=>"Pendiente",

		        "R"=>"Rechazada",

		        "E"=>"Enviada"

	        ); 

            ?>

	        <a target='_blank' href='<?=$link2?>'><font size=3><b><u>Asociada a la Licitación ID: <?=$id_licitacion?></u></b></font></a><br>

            <input name="pasa_id" type="hidden" value="<?=$id_licitacion?>">

            <script>var warchivos=0;</script>	

            <font size=3><b>Asociada al Renglón: <?=$codigo_renglon?></b></font>

            </td>

            <td>

	        <font size=3><b>Estado: <font size=3 color='red'><?=$est[$estado]?></font></b></font>

	        </td>

    </tr>

     <tr>

    <td>

	<b>Lider: <?=$ejecuta->fields['lider_nombre']?></b>

	</td>

    </tr>

	<tr>

            <td>

             <b>Fecha Inicio: </b>

             <input type=text size=10 name='fechainicio' value='<?=$fechainicio?>'> <?=link_calendario("fechainicio")?>

	        </td>

            <td>

	        <b>Fecha Entrega: </b>

            <input type=text size=10 name='fechaentrega' value='<?=$fechaentrega?>'>

	        <input name="pasa_fecha" type="hidden" value="<?=$fechaentrega?>">

	        <?=link_calendario("fechaentrega")?>

	        </td>

    </tr>

	<tr id=mo>

        <td colspan=2 align=left>

           <font size=2> Datos del Cliente</font>

        </td>

    </tr>

    <tr>

    <td colspan=2>

	        <table width="100%">

	            <tr>

                    <td colspan=2>

                    <?

	                $link=encode_link('../ord_compra/elegir_cliente.php',array("onclickaceptar"=>"window.opener.cargar_cliente();window.close()","onclicksalir"=>"window.close()"))

                    ?>

	                <a <?=$disa?> title='Haga click para ver elegir/editar el cliente' style='cursor: hand;'

                    

	                onclick="if (wcliente==0 || wcliente.closed) wcliente=window.open('<?=$link?>'); 

	                             else if (!wcliente.closed) wcliente.focus()">

                    <b><u>Cliente</u></b> (<---Haga click en la palabra para editar/elegir el cliente)

                    </a>

	                </td>

                </tr>

	            <tr >

                    <td>

	                <b>Nombre: </b>

                    <input type=text disabled size=40 name='cliente' value='<?=$cliente?>'>

	                <input name="pasa_cliente" type="hidden" value="<?=$cliente?>">

                    </td>

                    <td>

                    <b>Dirección: </b>

                    <input type=text disabled  size=40 name='direccion' value='<?=$direccion?>'>

                    </td>

                </tr>

                <tr >

                  <td>

                  <font size=2><b>Lugar de entrega: </b></font><br>

                  <textarea name='lugar_entrega' cols=55 rows=5><?=$lugar_entrega?></textarea>

                  </td>

                  <td>

                  <font size=2><b>Comentario: </b></font><br>

                  <textarea name='comentario' cols=55 rows=5><?=$comentario?></textarea>

                  </td>

                </tr>

            </table>

           </td>

    </tr>

    <tr id=mo>

        <td colspan=2>

        <font size=2>Ensamblador</font>

        </td>

    </tr>

    

    <tr>

     <td colspan=2>

    

    

            <table width=100%>

            <tr >

            <td>

                <?

	            if ($estado!="P" && $estado!="PA" && $estado!="") $disabl="disabled";

                ?>

	            <b>Seleccionar Ensamblador:</b>

            </td>

            <td>    

                <select name='id_ensamblador' <?=$disabl?>>

	            <option value=0>---Seleccionar Ensamblador---</option>

                <?

	            $sql="select id_ensamblador,nombre from ensamblador";

	            $rs=sql($sql) or fin_pagina();

	            $id_ensamblador=$_POST["id_ensamblador"] or $id_ensamblador=$licitacion->fields["id_ensamblador"];

	            while ($fila=$rs->FetchRow()) {

		            echo "<option value='".$fila["id_ensamblador"]."'";

		            if ($fila["id_ensamblador"]==$id_ensamblador) echo " selected";

		            echo ">".$fila["nombre"]."</option>\n";

	            }

                ?>

                </select>

                </td>

            </tr>

            <tr >

                <td>

                <b>Numeros de Serie: </b>

                </td>

                <td>

                <font size=3 color=red><b><?=$serialp?> ... <?=$serialu?></b></font>

                <?

	            $link=encode_link("datos_etiquetas.php",array("titulo_renglon"=>$titulo_renglon));

                ?>

                </td>

            </tr>    

            <tr >

    	        <input name="pasa_titulo" type="hidden" value="<?=$titulo_etiqueta?>">

    	        <input name="h_titulo_renglon" type="hidden" value="<?=$titulo_renglon?>">

    	        <td>

    		       <b> Título Etiqueta: </b>

                </td>

                <td>    

    		        <input type="button" name="boton_titulo" value="E" onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=0,left=125,top=10,width=500,height=400');"> 

    		        <input type="text" name="texto_titulo" value="<?=$titulo_etiqueta?>" readonly size="80">

    	        </td>

            </tr>

            <tr >

               <input name="pasa_descripcion" type="hidden" value="<?=$descripcion_etiqueta?>">

    	        <td>

    		        <b>Descripción Etiqueta: </b>

                 </td>

                 <td>   

    		        <input type="button" name="boton_titulo" value="E" onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=500,height=400');"> 

    		        <textarea name="texto_descripcion" cols="80" rows="2" readonly><?=$descripcion_etiqueta?></textarea>

    	        </td>

            </tr>

            <?

            if ($estado && ($estado!='P' && $estado!="PA" && $estado!="R"))

               {

            ?>

               <tr>

               <td colspan="2">

                  <input type="button" name="etiqueta" value="Generar etiquetas" onclick="return control_etiquetas();">

            <?

            }

            ?>

                </td>

                </tr>            

    </table>

    </td>

    </tr>

    



    <tr id=mo>

        <td colspan=2>

        <font size=2>Productos</font>

        </td>

    </tr>

    <tr >

         <td colspan=2>

         <table width=100%>

            <tr>

            <td valign=top width=5%><b>Producto:</b></td>

            <td width=50%>

             <textarea size=40 name='desc_prod' rows=3 style="width:95%"><?=$desc_prod?></textarea>

            </td>

            </tr>

            <tr>

            <td valign=top>

            <?

            if ($rechazada) $readonly=" readonly";

            ?>

            <b>Cantidad: </b>

            </td>

            <td valign=top>

            <input type=text size=10 name='cant_prod'	 value='<?=$cant_prod?>' <?=$readonly?>>            

	        <input name="pasa_cantidad" type="hidden" value="<?=$cant_prod?>">

            </td>

            </tr>

            

           </table> 

            </td>

    </tr>

      <?

      $sql_sist_op = "select id_sistema_operativo,descripcion

                       from sistema_operativo

                       where activo=1 order by descripcion";

      $result_sist_op = sql($sql_sist_op) or fin_pagina();

      ?>    

    <tr id=mo>

       <td colspan=2><font size=2>Sistema Operativo</font></td>

    </tr>

    <tr >

       <td colspan=2>

       

          <table width=100%>

          <tr>

          <td width=15%>

       

            <b>Sistema operativo: </b>

         </td>

         <td>   

            <select name='sist_instalado'>

            <option value=''></option>

            <?

	        while (! $result_sist_op->EOF) {

		        echo "<option value='".$result_sist_op->fields["id_sistema_operativo"]."'";

		        if ($id_sistema_operativo == $result_sist_op->fields["id_sistema_operativo"]) {

			        echo " selected";

		        }

		        echo ">".$result_sist_op->fields["descripcion"]."</option>";

		        $result_sist_op->MoveNext();

	        }

            ?>

            </select>



       </td>

       </tr>

       <tr>

       <td>

        <b> Clave de Root: </b>

        </td>

        <td>

         <input type=text name="clave_root" value="<?=$clave_root?>">

     

     

     

         </td>

         </tr>

      </table>

     

     

       </td>

       

    </tr>

    <tr id=mo>

        <td colspan=2>

        <font size=2>Descripción de los Productos</font>

        </td>

    </tr>

    <tr>

    <td colspan=2 nowrap>

	

    <!--

    <div id='div_com2'  style='border-width: 0;overflow: hidden;height: 1'>

    -->

	<table width="100%">

        <tr bgcolor=<?=$bgcolor_out?>>

              <td colspan=2 nowrap>

              <table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="width=<?=(($gag_modo=="ap" || $gag_modo=="apa")?"50%":"100%")?>">

                <table id=productos cellspacing=2 cellpadding=2 border=0>

                <tr id=mo>

                    <td width=10%>&nbsp;</td>

                    <td width=5% width=center>Cantidad</td>

                    <td >Producto</td>

               </tr>

    <?

                   

	$i=0;

    /*

	if ($id_renglon and !$_POST["item"]) {

           //Aca deberia ir la parte de traer la parte de licitaciones

           

	}

    */

	if ($nro_orden and !$_POST["item"]) {

         

		$sql="select id_fila,id_producto,productos.desc_gral as producto,

                     filas_ord_prod.cantidad,filas_ord_prod.descripcion as desc_gral, productos.id_tipo_prod, tipos_prod.codigo

                     from filas_ord_prod 

	   		          join  productos using (id_producto)

	   		          left join general.tipos_prod using(id_tipo_prod)

			         where nro_orden=$nro_orden order by filas_ord_prod.orden";



		$renglon=sql($sql) or fin_pagina();



	}

	$gag_flag_gtia=0;

	while (!$_POST["item"] and $renglon and $fila=$renglon->FetchRow()) {

		$i++;



		$desc=$_POST["desc_$i"] or $desc=$fila["desc_gral"] or $desc=$fila["descrip"];

		$original=$_POST["desc_orig_$i"] or $original=$fila["producto"] or $original=$fila["desc_gral"] or $original=$fila["descrip"];

		//if ($fila["producto"]) $h_desc=substr($fila["desc_gral"],strlen($original)+1);

		if ($original) $h_desc=substr($desc,strlen($original)+1);

		else $h_desc=$desc;

		$canti=$_POST["canti_$i"] or $canti=$fila["cantidad"];

		$gag_flag_gtia= $gag_flag_gtia || ($fila["codigo"]=="garantia");

        ?>

        <input type=hidden name=id_fila_<?=$i?> value='<?=$fila["id_fila"]?>'>

 		<input type=hidden name=orden_<?=$i?> id=orden value='<?=$i-1?>'>

		<input type=hidden name=id_<?=$i?> value='<?=$fila["id_producto"]?>'>



		<tr id=ma>

		<td>

           <img src='../../imagenes/up.gif' style='cursor: hand;' id=imagen alt='Subir el producto' onclick='subir(this.parentNode.parentNode.rowIndex);'>

           <input name='chk' type='checkbox' id='chk' value='1'>

        </td>

		<td>

           <input size=3 name='canti_<?=$i?>' value='<?=$canti?>'>

        </td>

		<td>

         <textarea readonly rows=1 style="width:90%" name='desc_<?=$i?>'><?=$desc?></textarea>

		 <input type=button name=descripcion value='E' onclick="window.open('../ord_compra/desc_adicional.php?posicion=<?=$i?>');">

		 <input type=hidden name='desc_orig_<?=$i?>' value='<?=$original?>'>

		 <input type=hidden name='h_desc_<?=$i?>' value='<?=$h_desc?>'>

       </td>

     </tr>

    <?

    } // del while

	if ($_POST["item"]) {

		while ($i<$_POST["item"]) {

			$i++;

			if ($_POST["id_$i"]) {

		        $desc=$_POST["desc_$i"];

				$canti=$_POST["canti_$i"];

				$original=$_POST["desc_orig_$i"];

				$h_desc=$_POST["h_desc_$i"];

		        ?>        

				<tr id=ma>

				    <input type=hidden name=id_fila_<?=$i?> value='<?=$_POST["id_fila_$i"]?>'>

				    <input type=hidden name=orden_<?=$i?> id=orden value='<?=$i-1?>'>

				    <input type=hidden name=id_<?=$i?> value='<?=$_POST["id_$i"]?>'>

				<td align=right nowrap>

                    <img src='../../imagenes/up.gif' alt='Subir el producto' style='cursor: hand;' onclick='subir(this.parentNode.parentNode.rowIndex);'>

                    <input name='chk' type='checkbox' id='chk' value='1'>

                </td>

				<td><input size=5 name='canti_<?=$i?>' value='<?=$canti?>'></td>

				<td>

                    <textarea readonly rows=1  style="width:90%" name='desc_<?=$i?>'><?=$desc?></textarea>

				    <input type=hidden name='desc_orig_<?=$i?>' value='$original'>

                    <input type=hidden name='h_desc_<?=$i?>' value='<?=$h_desc?>'>

                </td>



				</tr>

                <?

			} //del if 

		} //del while

	} //del if

	?>

	<input type=hidden name='item' value=<?=$i?>>

    

	<?

	if ($gag_modo=="ap" || $gag_modo=="apa"){

		$consulta="select productos.desc_gral, p.cantidad

              from licitaciones.producto p

              join  general.productos using(id_producto)

              where p.id_renglon = $id_renglon order by desc_gral asc";

		$rta_consulta=sql($consulta, "c640 - ordenes_nueva_gestion_2") or fin_pagina();

    ?>

    </table>

    </td>

        <td  colspan=3>

        <table border="0" cellpadding="2" cellspacing="2" width="100%" style="cursor:hand">

			    <tr id=mo>

      	    <td width=5% width=center>Cantidad</td><td>Producto</td>

			    </tr>

			    <?while(!$rta_consulta->EOF){?>

			    <tr bgcolor="<?=$bgcolor_out?>" onclick="alternar_color(this,'#a6c2fc')">

				    <td><?=$rta_consulta->fields["cantidad"]?></td>

				    <td><?=$rta_consulta->fields["desc_gral"]?></td>

			    </tr>

			    <? $rta_consulta->moveNext();}?>

        </table>

        </td>

    </tr>

    </table>

<?

	}

?>

    </td>

    </tr>

<?

	if (!$gag_flag_gtia){

?>

		<tr>

			<td colspan="3" align="center">

				<font color="Red" size="5">

					NO SE HA ENCONTRADO LA GARANTÍA EN LA LISTA DE PRODUCTOS

				</font>

			</td>

		</tr>

<?

	}

/*?>

    <tr>

    <?

    if ($i==0) $disabled_eliminar="disabled ";

    ?>

    <td align=center colspan=2>

    <!--

    <input type=button name=eliminar <?=$disabled?>  value='Eliminar' onclick='borrar_items();'>

    <input type=button name=agregar value='Agregar' onclick='nuevo_item();'>

    -->

    </td>

    </tr>*/?>

    <tr id=mo>

    <td colspan=3>

    <font size=2>KIT</font>

    </td></tr>

    <tr><td align=center colspan=3>

    <table width=60% cellspacing=2 cellpadding=2 border=0>

    <tr id=mo>

    <td>Accesorios</td>

    <td>Modelo</td>

    <td width=50%>Observaciones</td>

    </tr>

    <?

	if ($nro_orden) {

		$sql="select id_accesorio,descripcion,esp1,tipo from accesorios 

              where nro_orden=$nro_orden order by tipo";

		$acc=sql($sql) or fin_pagina();

		$acc->MoveFirst();

    ?>

		<tr id='ma'>

		<td><b>Teclado</b></td>

		<td>

		<select name='esp1_0' onchange='beginEditing(this);'>

            <? if ($acc->fields["esp1"]=="DIN") 

                                    $selected_din=" selected";?>

            <option <?=$selected_din?>>DIN</option>

            <? if (!$acc->fields["esp1"] || $acc->fields["esp1"]=="MINI DIN") 

                                   $selected_mini_din=" selected";?>

            <option <?=$selected_mini_din?>>MINI DIN</option>

            <? if ($acc->fields["esp1"]=="Ninguno")

                           $selected_ninguno="selected";?>

            <option <?=$selected_ninguno?>>Ninguno</option>

            <?

            if ($acc->fields["esp1"]!="MINI DIN" && $acc->fields["esp1"]!="DIN" && $acc->fields["esp1"]!="Ninguno")

             {

            ?>

		    <option selected><?=$acc->fields["esp1"]?></option>

            <?

             }

            ?>

		    <option id='editable'>Edite aqui</option>

        </select>

		</td>

		<td>

        <input type='text' name='observ_0' value='<?=$acc->fields["descripcion"]?>' size='33'>

		</td>

		</tr>

        <?

		$acc->MoveNext();

        ?>

		<tr id='ma'>

		<td><b>Mouse </b></td>

		<td>

        <select name='esp1_1' onchange='beginEditing(this);'>

          <option selected>PS/2</option>

          <?

          if ($acc->fields["esp1"]=="SERIAL") $selected_serial=" selected";  

          ?>

          <option <?=$selected_serial?>>SERIAL</option>

          <?

          if ($acc->fields["esp1"]=="Ninguno") $selected_ninguno=" selected";

                                        else   $selected_ninguno="";

          ?>

	      <option <?=$selected_ninguno?>>Ninguno</option>

          <?

		  if ($acc->fields["esp1"]!="PS/2" && $acc->fields["esp1"]!="SERIAL" && $acc->fields["esp1"]!="Ninguno")

          {

          ?>

 		  <option selected><?=$acc->fields["esp1"]?></option>

          <?

          }

          ?>

		  <option id='editable'>Edite aqui</option>

        </select>

		</td>

		<td>

        <input type='text' name='observ_1' value='<?=$acc->fields["descripcion"]?>' size='33'>

		</td>

		</tr>

        <?

		$acc->MoveNext();

            

        ?>

		<tr id='ma'>

		<td><b>Parlantes </b></td>

		<td>

        <select name='esp1_2' onchange='beginEditing(this);'>

        <?

        if ($acc->fields["esp1"]=="220") $selected_220=" selected";

        ?>

         <option <?=$selected_220?>>220</option>

         <?

         if ($acc->fields["esp1"]=="Interno") $selected_interno=" selected";

         ?>

         <option <?=$selected_interno?>>Interno</option>
         <?
         if ($acc->fields["esp1"]=="USB") $selected_usb=" selected";
                                        else  $selected_usb="";
         ?>
	     <option <?=$selected_usb?>>USB</option>
         <?

         if ($acc->fields["esp1"]=="Ninguno") $selected_ninguno=" selected";

                                        else  $selected_ninguno="";

         ?>

	     <option <?=$selected_ninguno?>>Ninguno</option>

         <?

		 if ($acc->fields["esp1"] && $acc->fields["esp1"]!="220" && $acc->fields["esp1"]!="Interno" && $acc->fields["esp1"]!="Ninguno")

         {

         ?>

		 <option selected><?=$acc->fields["esp1"]?></option>

         <?

         }

         ?>

		  <option id='editable'>Edite aqui</option>

        </select>

		</td>

		<td>

        <input type='text' name='observ_2' size='33' value='<?=$acc->fields["descripcion"]?>'>

		</td>

		</tr>

		<?

		$acc->MoveNext();

        ?>

		<tr id='ma'>

		<td><b>Micrófono</b></td>

		<td>

        <?

        if ($acc->fields["esp1"]=="on") $checked_microfono=" checked";

        ?>

        <input type='checkbox' name='lleva_microfono' <?=$checked_microfono?>>

		</td>

		<td>

        <input type='text' name='observ_3' size='33' value='<?=$acc->fields["descripcion"]?>'>

		</td>

		</tr>

        <?

		$acc->MoveNext();

        ?>

		<tr id='ma'>

		<td height='23'><b>Floppy</b></td>

		<td>

        <?

        if (!$acc->fields["esp1"] || $acc->fields["esp1"]=="on") $checked_floppy=" checked";	

        ?>

        <input type='checkbox' name='lleva_floppy' <?=$checked_floppy?>>

		</td>

		<td valign='top'>&nbsp;</td>

		</tr>

    <?    

	}

	else {

    ?>    

		<tr>

        <td><b>Teclado</b></td>

        <td>

            <select name="esp1_0" onchange="beginEditing(this);">

              <option>DIN</option>

              <option selected>MINI DIN</option>

              <option>Ninguno</option>

              <option id="editable">Edite aqui</option>

            </select>

         </td>

         <td>

           <input type="text" name="observ_0" size="33" >

         </td>

        </tr>

       <tr>

           <td><b>Mouse</b></td>

           <td>

                <select name="esp1_1" onchange="beginEditing(this);">

                  <option selected>PS/2</option>

                  <option>SERIAL</option>

		          <option>Ninguno</option>

		          <option id="editable">Edite aqui</option>

                </select>

           </td>

           <td>

           <input type="text" name="observ_1" size="33">

           </td>

       </tr>

       <tr>

           <td><b>Parlantes</b></td>

           <td>

            <select name="esp1_2" onchange="beginEditing(this);">

              <option>220</option>

              <option>Interno</option>
              <option selected>USB</option>
		      <option>Ninguno</option>

		      <option id="editable">Edite aqui</option>

            </select>

          </td>

          <td>

            <input type="text" name="observ_2" size="33">

          </td>

       </tr>

        <tr>

          <td><b>Micr&oacute;fono</b></td>

          <td>

            <input type="checkbox" name="lleva_microfono" >

          </td>

          <td>

            <input type="text" name="observ_3" size="33">

          </td>

        </tr>

        <tr>

          <td height="23"><b>Floppy</b></td>

          <td>

            <input type="checkbox" name="lleva_floppy" checked>

          </td>

          <td valign="top">&nbsp;</td>

        </tr>

    <?

	}



    ?>

    </table>

    <input type=hidden name=modo value=''>

    </td>

    </tr>

    <tr>

        <td colspan=3 align=center>

        <font size=3><b>Adicionales</b></font><br>

        <textarea name=adicionales cols=80 rows=5><?=$adicionales?></textarea>

        </td>

    </tr>

<?	



if ($modo=="modificar") {

// Si es una nueva orden no tiene numero de orden

// Entonces no se muestra la parte de archivos



	$q = "SELECT subir_archivos.*,usuarios.nombre ||' '|| usuarios.apellido as nbre_completo 

            FROM subir_archivos 

            join usuarios on subir_archivos.creadopor=usuarios.login 

            join archivos_ordprod on id_archivo=subir_archivos.id 

            where nro_orden=$nro_orden";

    $rs=sql($q) or fin_pagina();

	?>

	<tr>

    <td colspan=3 align=center>

    <table width=99% align=center>

        <tr> <td id="mo" ><font size=3> Archivos </font></td> </tr>

        <tr>

            <td align=right>

            <table width="100%">

                        <tr>

                        <td align="left">

                        <b><?=$msg ?></b>

                        </td>

                        <td align="right">

                        <input type="button" name="bagregar" value="Agregar Archivo" style="width:105" onclick="if (typeof(warchivos)=='object' && warchivos.closed || warchivos==false) warchivos=window.open('<?= encode_link($html_root.'/modulos/archivos/archivos_subir.php',array("onclickaceptar"=>"window.opener.location.reload();","nro_orden"=>$nro_orden,"proc_file"=>"../ordprod/orden_file_proc.php")) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1'); else warchivos.focus()">

                        </td>

                        </tr>

            </table>

            </td>

        </tr>

       <tr>

       <td>

       <table width='100%'>

       <tr>

       <td colspan=7  id=ma style="text-align:left">

       <b>Total:</b><?=$total_archivos=$rs->recordcount() ?>

       </td>

       <tr >

           <td align=right id=mo>Archivo</td>

           <td align=right id=mo>Fecha</td>

            <td align=right id=mo>Subido por</td>

            <td align=right id=mo>Tamaño</td>

            <td align=center id=mo>&nbsp;</td>

        </tr>

  <?

  while (!$rs->EOF) {

  ?>

       <tr <?=atrib_tr()?> > <!-- bgcolor='#f0f0f0' -->

       <td align=center>

      <?    

       if (is_file("../../uploads/archivos/".$rs->fields["nombre"]))

             echo "<a target=_blank href='".encode_link("../archivos/archivos_lista.php",array ("file" =>$rs->fields["nombre"],"size" => $rs->fields["size"],"cmd" => "download"))."'>";

       echo $rs->fields["nombre"]."</a></td>\n";

    ?>    

      <td align=center>&nbsp;<?= Fecha($rs->fields["fecha"]) ?></td>

      <td align=center>&nbsp;<?= $rs->fields["nbre_completo"] ?></td>

      <td align=center>&nbsp;<?= $size=number_format($rs->fields["size"] / 1024); ?> Kb</td>

      <td align=center>

     <?    

	$lnk=encode_link("$_SERVER[PHP_SELF]",Array("nro_orden"=>$nro_orden,"id_archivo"=>$rs->fields["id"],"filename"=>$rs->fields["nombre"],"modo"=>"borrar_archivo"));

    echo "<a href='$lnk'><img src='../../imagenes/close1.gif' border=0 alt='Eliminar el archivo: \"". $rs->fields["nombre"] ."\"'></a>";

    ?>

    </td>

    </tr>

<?

    $rs->MoveNext();

}

?>

    </table>

    </td>

    </tr>

	</table>

<!--	</div> -->

    </td>

    </tr>

<?

}



?>    

</table>

</td>

</tr>

<tr><td colspan=2 align=center>

<?/*<!--

        <input type=button name='guardar' <?=$disabled_guardar?> value='Guardar' <?=$onclick?>>       

        <input type=button name=paraautor <?=$disabled_para_autorizar?> value='Para Autorizar'  onclick='document.all.modo.value="Para Autorizar";document.all.frm_guardar.submit();'>       

        <input type=button name=autorizar <?=$disabled_autorizar?> value='Autorizar' onclick='document.all.modo.value="Autorizar";document.all.frm_guardar.submit();'>

        <input type=hidden name=rechazada>

        <input type=button name=rechazar  <?=$disabled_rechazar?> value='Rechazar'  onclick='window.open("rechazar.php","","toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=150");'>       

        <input type=button name=anular    <?=$disabled_anular?>  value='Anular' onclick='window.location="<?=$link_anular?>"'>

         -->*/?>

	   </td>

       </tr>

	  </table>

      </form>

<?



}

echo fin_pagina();

?>