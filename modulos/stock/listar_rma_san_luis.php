<?
/*
Creado por: Fernando

Modificada por
$Author: fernando $
$Revision: 1.2 $
$Date: 2006/12/19 21:59:48 $
*/

require_once("../../config.php");
require_once("funciones.php");

$sql = "select id_deposito 	from depositos
	    where depositos.nombre = 'RMA-Produccion-San Luis'";
$res = sql($sql) or fin_pagina();
$id_deposito_rma = $res->fields["id_deposito"];




if ($_POST["actualizar_movimientos"]) {
	
   $sql = "select id_info_rma,tipo_log from stock.log_info_rma where tipo_log ilike'%Creacion PM%'";
   $res = sql($sql) or fin_pagina();
   $db->starttrans();
   for($i=0; $i < $res->recordcount(); $i++){
   	   $pm = trim ( substr($res->fields["tipo_log"],14,strlen($res->fields["tipo_log"]))); 
   	   $id_info_rma = $res->fields["id_info_rma"];
   	   $sql = " update info_rma set id_movimiento_material = $pm 
   	            where id_info_rma = $id_info_rma";
   	   sql($sql) or fin_pagina();
   $res->movenext();	
   }//del for
   $db->completetrans();  
} //del if



if($_POST["ajustar_cant_en_stock"]=="Ajustar cantidades en_stock") {
	
	$db->StartTrans();
	$fecha_hoy = date("Y-m-d H:i:s");
	$query  = "select * from(
				select en_stock.id_en_stock,en_stock.id_prod_esp,
  				       sum(info_rma.cantidad) as cant_rma,en_stock.cant_disp
				from stock.info_rma join stock.en_stock using(id_en_stock)
				where info_rma.id_estado_rma<12
				group by en_stock.id_en_stock,en_stock.cant_disp,en_stock.id_prod_esp
			   )as sa
			  where cant_rma<>cant_disp";
	$datos_rma = sql($query,"<br>Error al traer los datos del rma<br>") or fin_pagina();
	//si la cantidad de en_stock es mayor que la suma de las entradas de info_rma hacemos un descuento de stock disponible
	//para el deposito de RMA, para que tambien se genere un log en los movimientos de stock
	$id_stock_afectados = "";
	$id_stock_negativos = "";
	$id_stock_afectados_mov = "";
	$id_stock_negativos_mov = "";
	while (!$datos_rma->EOF) {
		$dif_cant_rma=$datos_rma->fields["cant_disp"]-$datos_rma->fields["cant_rma"];
		if($dif_cant_rma>0)
		{
			$query="update stock.en_stock set cant_disp=cant_disp-$dif_cant_rma
					where id_en_stock=".$datos_rma->fields["id_en_stock"];
			sql($query,"<br>Error al actualizar el cant_disp para ".$datos_rma->fields["id_en_stock"]."<br>") or fin_pagina();
			$id_stock_afectados.=$datos_rma->fields["id_en_stock"]." - ";

		}//de if($datos_rma->fields["cant_disp"]>$datos_rma->fields["cant_rma"])
		elseif ($dif_cant_rma<0)
			$id_stock_negativos.=$datos_rma->fields["id_en_stock"]." - ";

	 	$datos_rma->MoveNext();
	 }//de while(!$datos_rma->EOF)


	$query = "select en_stock.id_en_stock,en_stock.id_prod_esp,en_stock.cant_disp
				from stock.en_stock 
				where id_deposito = $id_deposito_rma";
	$dat_disp_rma = sql($query,"<br>Error al traer las cantidades disponibles de RMA<br>") or fin_pagina();

	while (!$dat_disp_rma->EOF)	{
		$total_ing=$total_eg=$dif_insertar=0;
	 	//revisamos las cantidades de ingreos y egresos y las comparamos con la cantidad total en stock.
		$query="select 	sum(log_movimientos_stock.cantidad) as total_mov,tipo_movimiento.clase_mov,en_stock.id_prod_esp
		     from stock.log_movimientos_stock
			  join stock.en_stock using (id_en_stock)
			  join stock.tipo_movimiento using(id_tipo_movimiento)
		      where id_en_stock=".$dat_disp_rma->fields["id_en_stock"]." and (clase_mov=1 or clase_mov=2) and ( id_deposito=9)
		      group by tipo_movimiento.clase_mov,en_stock.id_prod_esp";
		$ing_eg=sql($query,"<br>Error al traer las sumas de los ingresos y egresos para el producto<br>") or fin_pagina();

		while (!$ing_eg->EOF) {
		 	if($ing_eg->fields["clase_mov"]==1)
		 	 	$total_ing=$ing_eg->fields["total_mov"];
		 	if($ing_eg->fields["clase_mov"]==2)
		 		$total_eg=$ing_eg->fields["total_mov"];

		 	$ing_eg->MoveNext();
		}//de while(!$ing_eg->EOF)

		if($total_ing=="")
			        $total_ing=0;
		if($total_eg=="")
		 	        $total_eg=0;

		$dif_ing_eg=$total_ing-$total_eg;

		//si la diferencia entre ingresos y egresos es mayor que la cantidad disponible en stock,
		//agregamos un egreso para ajustar esa discrepancia
		if(($dif_ing_eg!="" && $dif_ing_eg!=0) &&($dif_ing_eg > $dat_disp_rma->fields["cant_disp"])) {
			$dif_insertar=$dif_ing_eg-$dat_disp_rma->fields["cant_disp"];
			//agregamos el egreso de ajuste
			$query="insert into stock.log_movimientos_stock(id_en_stock,id_tipo_movimiento,cantidad,fecha_mov,usuario_mov,comentario)
					values(".$dat_disp_rma->fields["id_en_stock"].",32,$dif_insertar,'$fecha_hoy','".$_ses_user["name"]."','Se agregó egreso de RMA para ajustar las cantidades registradas en el movimiento de este deposito, debido a una serie de bugs ocurridos en el sistema de RMA')";
			sql($query,"<br>Error al agregar egreso de ajuste en los movimientos de stock<br>") or fin_pagina();

			$id_stock_afectados_mov.=$dat_disp_rma->fields["id_en_stock"]." - ";
		}//de if(($total_ing-$total_eg) > $datos_rma->fields["cant_disp"]);
		else if(($dif_ing_eg!="" && $dif_ing_eg!=0) &&($dif_ing_eg < $dat_disp_rma->fields["cant_disp"])) {
			$id_stock_negativos_mov.=$dat_disp_rma->fields["id_en_stock"]." - ";
		}

		echo "<br>Para ".$dat_disp_rma->fields["id_en_stock"].", dif_ing_eg: $dif_ing_eg - Cantidad disp: ".$dat_disp_rma->fields["cant_disp"]." - Cantidad a insertar en el log: $dif_insertar<br>";
	 	$dat_disp_rma->MoveNext();
	}//de while(!$dat_disp_rma->EOF)

	echo "Id en Stock afectados rma: $id_stock_afectados<br><br>";
	echo "Id en Stock negativos rma: $id_stock_negativos<br><br>";
	echo "Id en Stock afectados por ajuste egresos de mov: $id_stock_afectados_mov<br><br>";
	echo "Id en Stock afectados por ajuste ingresos de mov: $id_stock_negativos_mov<br><br>";

	echo "<br>LA TRANSACCION NO FUE CERRADA, NO SE REALIZARON CAMBIOS EN EL STOCK<br>";
	//$db->CompleteTrans();
}//de if($_POST["ajustar_cant_en_stock"]=="Ajustar cantidades en_stock")


if ($_POST['borrar_rma']) {
      include_once("eliminar_rma.php");
      die();
}

$usuario     = $_ses_user["name"];
$fecha_actual= date("Y-m-d H:i:s",mktime());
$keyword     = $parametros["keyword"] or $keyword=$_POST["keyword"];
$filter      = $parametros["filter"] or $filter=$_POST["filter"];
$id_deposito = $parametros["id_deposito"];
$deposito    = "RMA-Produccion-San Luis";
$pagina_listado = "listar_rma_san_luis.php";

if((permisos_check("inicio","permiso_borrar_rma")) && $cmd!="historial")
           $mostrar_eliminar_rma=1;
           else
           $mostrar_eliminar_rma=0;

if ($id_deposito=="")   $id_deposito=$id_deposito_rma;
					  

$pagina_oc = $parametros["pagina_oc"] or $pagina_oc = $_POST['pagina_oc'];

variables_form_busqueda("listar_rma_san_luis",array("filtro_atendido_por"=>-1,"tipo_producto"=>"todos"));

if ($cmd == "" ) {
				$cmd="real";
				$_ses_listar_rma["cmd"]=$cmd;
				$_ses_listar_rma["filtro_atendido_por"]=-1;
				phpss_svars_set("_ses_listar_rma", $_ses_listar_rma);
				}

//$pagina_listado=$_ses_pagina_listado;
//$sort = $_POST["sort"] or $sort=$parametros["sort"];
//$up   = $_POST["up"] or $up=$parametros["up"];
if ($id_deposito=="") {
	$id_deposito=$_ses_id_deposito;
	$deposito=$_ses_deposito;
}

/*para el menu con los tipos de productos*/
//if ($tipo_producto=="") $tipo_producto="todos";

/* para el menu con los tipos de productos*/
if ($_POST["precios"]=="precios"){
    $db->starttrans();
    /////////cambie la consulta sacando * Quique///////
    $sql="select id_producto from productos";
    //$sql="select * from productos";
    $productos=sql($sql) or fin_pagina();;

    for($i=0;$i<$productos->recordcount();$i++) {
      $id_producto=$productos->fields["id_producto"];

      $sql="select sum(precio) as precios,count(id_producto) as cantidad,id_producto
             from precios where id_producto=$id_producto
             group by id_producto
             ";
      $precios=sql($sql) or fin_pagina();
      $nuevo_precio_stock=0;
      if ($precios->recordcount()>0) {
                $nuevo_precio_stock=($precios->fields["precios"]/$precios->fields["cantidad"]);
                $sql="update productos set precio_stock=$nuevo_precio_stock where id_producto=$id_producto";
                sql($sql) or fin_pagina();

      }
      $productos->movenext();
     }
    $db->completetrans();
} // del scrip que actualiza lso precios


//fin de la base de datos
$datos_barra = array(
                array(
					"descripcion"    => "Transito",
					"cmd"            => "tran"
					),
				array(
					"descripcion"    => "Coradir",
					"cmd"            => "cor"
					),
				array(
					"descripcion"    => "Proveedor",
					"cmd"            => "prov"
					),
				array(
					"descripcion"    => "Todas",
					"cmd"            => "real"
					),
				array(
					"descripcion"    => "Historial",
					"cmd"            => "historial"
					)
				 );

   
$sql=" select * from (
       select info_rma.cantidad, en_stock.id_prod_esp,en_stock.ubicacion,estado_rma.nombre_corto,producto_especifico.descripcion,
          en_stock.id_deposito,producto_especifico.precio_stock,stock.info_rma.id_info_rma,nrocaso,id_nota_credito,
          info_rma.id_proveedor,proveedor.razon_social,producto_especifico.id_tipo_prod,
          tipos_prod.codigo,info_rma.desc_void,estado_rma.lugar,fecha_hist,info_rma.nro_orden,voids,f.fecha_emision,
          info_rma.fecha_creacion,ubicacion_rma.id_ubicacion_rma,ubicacion_rma.comentario,
          (current_date-fecha_hist)as dias_envio_aux,cas_ate.nombre,(cantidad*precio_stock)as totales,2 as id_moneda,
          info_rma.id_movimiento_material as pm
       from stock.en_stock
       join general.producto_especifico using(id_prod_esp)
       join general.depositos using (id_deposito)
       join stock.info_rma using (id_en_stock)
       join general.tipos_prod using(id_tipo_prod)
       join general.proveedor using (id_proveedor)
       join stock.estado_rma using (id_estado_rma)
       left join (select licitaciones.unir_texto(void || text(' '))as voids,id_info_rma  from stock.void_rma  group by id_info_rma)as tabla_void using(id_info_rma)
       left join (select min(fact_prov.fecha_emision)as fecha_emision, factura_asociadas.nro_orden from compras.factura_asociadas join general.fact_prov using(id_factura) group by nro_orden)as f using (nro_orden)
       left join stock.ubicacion_rma using (id_ubicacion_rma)
       left join casos.casos_cdr using(nrocaso)
       left join casos.cas_ate using(idate)
       ) as rma"; 
   
   
   
   
   $where="  id_deposito=$id_deposito and cantidad > 0 ";
   if ($tipo_producto!="todos")
			 {
			 $where.=" and  codigo='$tipo_producto'";
			 }
	switch ($cmd){
	 case "tran": $where.=" and nombre_corto='T'";
	              break;
	 case "cor":  $where.=" and nombre_corto='C'";
	              break;
	 case "prov": $where.=" and nombre_corto='P' ";
	              break;
	 case "real": $where.= "and (nombre_corto <> 'B' and nombre_corto <> 'E')";
	              break;
     case "historial": $where.= " and (nombre_corto='B')";
                       break;
	};

$where="$where $group";
   if ($cmd=="cor") $ord=10;
   else $ord=9;
   $orden=array(
		  "default" => "$ord",
		  "1"  => "cantidad",
		  "2"  => "codigo",
          "3"  => "precio_stock",
          "4"  => "nrocaso",
          "5"  => "razon_social",
          "6"  => "descripcion",
          "7"  => "id_movimiento_material",
          "8"  => "control_stock.fecha_modif",
          "9"  => "id_info_rma",
          "10" => "lugar",
          "11" => "comentario",
          "12" => "fecha_emision",
          "13" => "dias_envio_aux",
          "14" => "nombre",
          "15" => "totales",
          "16" => "pm"
           );

   //if ($cmd=="real") $orden["10"]= "estado_rma.nombre_corto";
   if ($cmd=="cor") $orden["10"]= "lugar";

   $filtro= array(
		"descripcion"   => "Descripción",
		"cantidad"      =>"Cantidad",
        "nrocaso"       => "Nº C.A.S.",
        "razon_social"  => "Proveedor",
        "voids"         =>"Codigo Barra",
        "id_info_rma"   =>"Nº RMA",
        "lugar"		    =>"Estado",
        "comentario"    =>"Ubicacion",
        "fecha_creacion"=>"Fecha Creacion",
        "nombre"		=>"Caso Atendido Por",
        "pm"            =>"Nº PM"
        //"ubicacion.nombre_corto" => "Ubicacion"
		 );
   if ($cmd=="real") $filtro["nombre_corto"]= "Ubicacion";


  // } //si cmd  real
   $contar="buscar";
   $link=encode_link("listar_rma_san_luis.php",array("id_deposito"=>$id_deposito,"deposito"=>$deposito,"cmd"=>$cmd,'keyword'=>$keyword));
?>

<?=$html_header?>
<script>
//funcion que se le pasa dos checked
//el primero es el checked que sirve para elegir los check (segundo parametro)}
//estilo como funcionan los mail
function seleccionar_todos_local(elegir){
var valor;
            if(elegir.checked==true){
            	valor=true;
                var i=0;
                loco=eval ("document.form1.rma_"+i);
                while (typeof(loco)!='undefined'){
                 	loco.checked=valor;
                    i++;
                    loco=eval ("document.form1.rma_"+i);
               	}//del while
             }
             else{
             	valor=false;
                var i=0;
                loco=eval ("document.form1.rma_"+i);
                while (typeof(loco)!='undefined'){
                 	loco.checked=valor;
                    i++;
                    loco=eval ("document.form1.rma_"+i);
               	}//del while
             }
}//de la funcion

function cant_chequeados() {
var cant=0;
var i,sum=0;

cant=window.document.all.cant.value;

  for (i=0;i<cant;i++) {
  	c=eval("window.document.all.rma_"+i);
  	if (typeof(c) !='undefined') {
	if (c.checked) {
  		sum++;
	}
  	}
}

if (sum > 0) return true;
else {
	 alert ('Debe seleccionar al menos un RMA a eliminar');
	 return false;
}
}

function buscar_op(obj){
   var letra = String.fromCharCode(event.keyCode)
   if(puntero >= digitos){
       cadena="";
       puntero=0;
    }
   //si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto...
   if (event.keyCode == 13){
       borrar_buffer();

    }
   //sino busco la cadena tipeada dentro del combo...
   else{
       buffer[puntero]=letra;
       //guardo en la posicion puntero la letra tipeada
       cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array
       puntero++;

       //barro todas las opciones que contiene el combo y las comparo la cadena...
       for (var opcombo=0;opcombo < obj.length;opcombo++){
          if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase()){
          obj.selectedIndex=opcombo;break;
          }
       }
    }
   event.returnValue = false; //invalida la acción de pulsado de tecla para evitar busqueda del primer caracter
}
function borrar_buffer(){
   //inicializa la cadena buscada
    cadena="";
    puntero=0;
}
</script>
<form name="form1" method=POST action="listar_rma_san_luis.php">
<input type=hidden name="id_deposito" value="<?=$id_deposito?>">
<input type=hidden name="deposito" value="<?=$deposito?>">
<?
$exito=$parametros["exito"] or $exito=$exito;
if ($exito) Aviso($exito);

$ref1        = encode_link("stock_rma_san_luis.php",array("pagina"=>1));
$link_info   = encode_link("informe_rma.php",array());
$link_usuar  = encode_link("permisos_usuarios.php",array("pagina"=>"listar_rma","control"=>1));
$link_borrar = encode_link("eliminar_rma.php",array());
?>
<table width=98% align=Center border=0>
  <tr>
  <td colspan=4><?generar_barra_nav($datos_barra);?></td></tr>
  <tr>
  <td align=Center  colspan=4>
   <table width=100% align=center class='bordes' bgcolor=<?=$bgcolor3?>>
      <tr>
         <td rowspan="2" align=center bgcolor="white">
            <?
               $cons="select sum(cantidad*precio_stock)as monto from stock.info_rma
                      join stock.en_stock using (id_en_stock)
                      left join general.producto_especifico using(id_prod_esp)
                      left join stock.estado_rma using (id_estado_rma)";
               switch ($cmd) {
               	     case "tran": 
               	           $cons.=" where nombre_corto='T'";
	                       break;
	                  case "cor":  
	                       $cons.=" where nombre_corto='C'";
	                       break;
	                  case "prov": 
	                       $cons.=" where (nombre_corto='P') ";
	                       break;
	                  case "real": 
	                       $cons.= " where (nombre_corto <> 'B' and nombre_corto <> 'E')";
	                       break;
                       case "historial": 
                           $cons.= " where (nombre_corto='B')";
                           break;
	            };
	            $cons.=" and id_deposito=$id_deposito";
                $res_cons=sql($cons,"No se pudo recuperar el monto") or fin_pagina();
                $mont=$res_cons->fields['monto'];
      ?>
      <b>
      <font color=black>Monto: <?echo "u\$s&nbsp;".formato_money($mont);?></font>
      </b>
      </td>
      <td align="left">
   </td>
   <td>
<?
	//referido al filtro de CASOS (atendido por)
    switch ($filtro_atendido_por)    {
    	case 1://Filtro por No atendidos por Coradir BS AS
    		   $selected_todos="";
    		   $selected_bs_as="selected";
    		   $selected_no_bs_as="";
    		   $add_query_casos=" nombre ilike 'CORADIR Bs.As.'";
    		   break;
    	case 2://Filtro por No atendidos por Coradir BS AS
    		   $selected_todos="";
    		   $selected_bs_as="";
    		   $selected_no_bs_as="selected";
    		   $add_query_casos=" (nombre is not null and nombre not ilike 'CORADIR Bs.As.')";
    		   break;
    	case -1://si es -1 o no tiene valor, el valor por defecto es TODOS
    	default:$selected_todos="selected";
    			$selected_bs_as="";
    			$selected_no_bs_as="";
    			$add_query_casos="";
    			break;
    }//de switch ($filtro_atendido_por)

    if($where!="" && $add_query_casos!="")
    	$where.=" and ";
	
    $where.=$add_query_casos;

	$sumas = array(
 		"moneda" => "id_moneda",
 		"campo" => "totales",
 		"mask" => array("U\$S")
		);

     list($query_productos,$total,$link_pagina,$up2,$suma_total) = form_busqueda($sql,$orden,$filtro,$link_tmp,$where,$contar,$sumas,$ignorar,$seleccion);
    // echo "".$query_productos;
     ?>
      <select name="tipo_producto" style="width:120" onkeypress="buscar_op(this);" onblur='borrar_buffer()' onclick='borrar_buffer()' >
    <?
    $sql="select descripcion, codigo from tipos_prod order by descripcion";
    $resultado_desc = sql($sql) or fin_pagina();
     while (!$resultado_desc->EOF){
           $codigo      = $resultado_desc->fields['codigo'];
           $descripcion = $resultado_desc->fields['descripcion'];
           if ($tipo_producto==$codigo)
						$selected="selected";
						else
						$selected="";
            if ($tipo_producto=="todos") $selected="selected";
     ?>
      <option value="<?=$codigo?>" <?=$selected?> > <?=$descripcion;?></option>
      <?
      $resultado_desc->MoveNext();
     }
     ?>
     <option value="todos" <?=$selected?>>Todos
     </select>
     <input type=submit name=form_busqueda value='Buscar'>
     <input type='hidden' name="cant" value='<?=$total?>'>
   </td>
 </tr>
 <tr>
   <td align="left" colspan="2"<?
   if($mostrar_eliminar_rma && $cmd!="historial")   {
   ?>align="left"
   <?
   }
   else{
   ?>align="center"
   <?}?>>
   <?
   /*
   if($mostrar_eliminar_rma && $cmd!="historial")  {
	?>
    <input type="submit" name="borrar_rma" value="Borrar RMA" onclick="return (cant_chequeados());">
   <?
   }
   */
   $link = encode_link("configurar_vista.php",array("deposito"=>"RMA-Produccion-San Luis"))
   ?>
   
   <input type="button" name="configurar" value="Configurar Vista"  onclick="window.open('<?=$link?>');">
   <?
   /* 
   if(permisos_check("inicio","permiso_boton_configurar_mail")){?>
   <input type="button" name="configurar" value="Config Mail" title='Asignar Permisos' onclick="window.open('<?=$link_usuar?>','','')">
   <?
   }
   */
   ?>
   </td>
   <!--
   <td>
    <b>Filtro de C.A.S.&nbsp;</b>
   	<select name="filtro_atendido_por">
   		<option value="-1" <?=$selected_todos?>>Todos</option>
   		<option value="1" <?=$selected_bs_as?>>Coradir Bs. As.</option>
   		<option value="2" <?=$selected_no_bs_as?>>Resto del País</option>
   	</select>
   </td>
   -->
  </tr>
</table>

 </td>
  </tr>
  <tr>
  <td>
  <table class="bordessininferior" width=99% cellspacing=0 cellpadding=1 bordercolor='' align=center>
  <tr>
  <td  align=left id="ma_sf">
	<b>Total: <?=$total?></b>
  </td>
  <? $link=encode_link("rma_excel.php",array("sql"=>$query_productos,"cmd"=>$cmd,"suma_total"=>$suma_total));?>
  <td id="ma_sf" align="center">
	     Monto Total en Listado: <?=$suma_total?>
	       <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
              <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
           </a>
  </td>
  <td align=right id="ma_sf">
  <?=$link_pagina?>
  </td>
  </tr>
  </table>

  </td>
  </tr>
  <tr>
   <td colspan=4>
  <?
  $id_usuario=$_ses_user['id'];
  $sql = "select campo,ver from configurar_vista where id_usuario=$id_usuario and deposito = 'RMA-Produccion-San Luis'";
  $rs = sql($sql) or fin_pagina();
  $cantid=$rs->RecordCount();
  while (!$rs->EOF) {
  	$r=$rs->fields['campo'];
  	$ver[$r]=$rs->fields['ver'];
  	$rs->MoveNext();
  }
  ?>
	 <table width=99% align=center class='bordessinsuperior' >
		 <?
//		 if ($cmd=="real" || $cmd=="tran" || $cmd=="cor" || $cmd=="prov" || $cmd="historial") {
		 ?>
		 <tr>
		 <?
		 if($mostrar_eliminar_rma && $cmd!="historial")
		 {
		 ?>
		  <td id="mo">Elim.RMA <INPUT class='estilos_check' type=checkbox name="selec_todos" onclick="seleccionar_todos_local(this)"> </td>
		 <?
		 }
		 if(($ver['cant']==1) ||($cantid==0)) {
		 ?>
		 <td id="mo" title="Cantidad de productos"><a href="<?=encode_link("listar_rma.php",array("sort"=>1,'up'=>$up2,'keyword'=>$keyword));?>">Cant</a></td>
		 <?
		 }
		  if(($ver['codigo']==1) ||($cantid==0)) {
		 ?>
		 <td id="mo" title="Tipo de productos"><a href="<?=encode_link("listar_rma.php",array("sort"=>2,'up'=>$up2,'keyword'=>$keyword));?>">Tipo</a></td>
		 <?
		 }
		  if(($ver['descripcion']==1) ||($cantid==0)) {
		 ?>
		 <td id="mo" title="Descripcion"><a href="<?=encode_link("listar_rma.php",array("sort"=>6,'up'=>$up2,'keyword'=>$keyword));?>">Descripci&oacute;n Producto</a></td>
         <?
		 }
		  if(($ver['id_info_rma']==1) ||($cantid==0)) {
		 ?>
		 <td id="mo" title="Numero de RMA"><a href="<?=encode_link("listar_rma.php",array("sort"=>9,'up'=>$up2,'keyword'=>$keyword));?>">N° RMA</a></td>
		 <?
		 }
		  if(($ver['dias_crea']==1) ||($cantid==0))	 {
		 ?>
		 <td id="mo" title="Días Creación"><a href="<?=encode_link("listar_rma.php",array("sort"=>13,'up'=>$up2,'keyword'=>$keyword));?>">Dc</a></td><!--//Borggi-->
		 <?
		 }
		  if(($ver['dias_env']==1) ||($cantid==0))  {
		 ?>
		 <td id="mo" title="Días Envio"><a href="<?=encode_link("listar_rma.php",array("sort"=>13,'up'=>$up2,'keyword'=>$keyword));?>">De</a></td><!--//Borggi-->
		<?
		 }
		 if(($ver['nro_caso']==1) ||($cantid==0)) {
		 ?>
		 <td id="mo" title="Asociado a Casos"><a href="<?=encode_link("listar_rma.php",array("sort"=>4,'up'=>$up2,'keyword'=>$keyword));?>">Caso</a></td>
		 <td id="mo" title="Caso Atendido Por"><a href="<?=encode_link("listar_rma.php",array("sort"=>14,'up'=>$up2,'keyword'=>$keyword));?>">Atendido Por</a></td>
		 <td id="mo" title="Pm Asociados al caso"><a href="<?=encode_link("listar_rma.php",array("sort"=>16,'up'=>$up2,'keyword'=>$keyword));?>">PM</a></td>
         <?
		 }
         if(($ver['razon_social']==1) ||($cantid==0))
		 {
		 ?>
		 <td id="mo"><a href="<?=encode_link("listar_rma.php",array("sort"=>5,'up'=>$up2,'keyword'=>$keyword));?>">Proveedor</a></td>
         <?
		 }
		  if(($ver['monto']==1) ||($cantid==0))	 {
		 ?>
		 <td id="mo" title="Monto total renglon"><a href="<?=encode_link("listar_rma.php",array("sort"=>15,'up'=>$up2,'keyword'=>$keyword));?>">Monto</a></td>
         <?
		 }
         if(permisos_check("inicio","permiso_boton_cambiar_precio") && $cmd != "historial") {
           ?>
            <td id="mo">M.P.</td>
           <?
          }
         if($cmd=="cor" || $cmd=="prov" || $cmd=="real" || $cmd=="historial")  {
         	if(($ver['nombre_corto']==1) ||($cantid==0)) {

         	   ?>
         	    <td id="mo"><a href="<?=encode_link("listar_rma.php",array("sort"=>11,'up'=>$up2,'keyword'=>$keyword));?>" title="Ubicación de la Parte">Ubi.</a></td><!--//Borggi-->
         	   <?

     		 }
			 if(($ver['lugar']==1) ||($cantid==0)) {
	         ?>
	         <td id="mo"><a href="<?=encode_link("listar_rma.php",array("sort"=>10,'up'=>$up2,'keyword'=>$keyword));?>">Estado</a></td>
	         <?
			 }
		 }
		 if(($ver['void']==1) ||($cantid==0)) {
         ?>
          <td id="mo" title="Void asociados al RMA"><b>Void</b></td>
         <?
		 }
         if(($ver['fecha_emision']==1) ||($cantid==0))
		 {
         ?>
          <td id="mo" title="Fecha primera Factura"><a href="<?=encode_link("listar_rma.php",array("sort"=>12,'up'=>$up2,'keyword'=>$keyword));?>"><b>Fec Factura</b></a></td>
         <?
		 }
         ?>
		 </tr>
		 <?
		 $resultado=sql($query_productos) or fin_pagina();
         $cantidad=$resultado->recordcount();
          for ($i=0;$i<$cantidad;$i++){
             $id_info_rma = $resultado->fields["id_info_rma"];
       		 $ref = encode_link("stock_rma.php",array(/*"id_producto"=>$resultado->fields["id_prod_esp"],
	  	   					        "id_deposito"=>$resultado->fields["id_deposito"],"id_proveedor"=>$resultado->fields["id_proveedor"],*/
	  	   					        "id_info_rma"=>$id_info_rma,"pagina_listado"=>"real"
							 ));

            //$onclick="onClick=\"location.href='$ref'\";";
            $lug=$resultado->fields["lugar"];
       ?>
       <tr <?=atrib_tr();?> title='<?=$comentario?>'>
       <?
  // }
      if($mostrar_eliminar_rma && $cmd!= "historial") {
      ?>
       	 <td align=center> <INPUT class='estilos_check' type=checkbox name="rma_<?=$i?>" value='<?=$resultado->fields["id_info_rma"]?>'>
      <?
      }
      ?>
	   	<input type="hidden" name="id_info_rma_<?=$i?>" value="<?=$resultado->fields["id_info_rma"]?>">
	   	<input type="hidden" name="id_deposito_<?=$i?>" value="<?=$resultado->fields["id_deposito"]?>">
      	<input type="hidden" name="id_producto_<?=$i?>" value="<?=$resultado->fields["id_producto"]?>">
      	<input type="hidden" name="id_proveedor_<?=$i?>" value="<?=$resultado->fields["id_proveedor"]?>">
       	<input type="hidden" name="cantidad_<?=$i?>"     value="<?=$resultado->fields["cantidad"]?>"></td>
       	<!--continuo cargando la tabla-->
       	<?
		    if(($ver['cant']==1) ||($cantid==0))
		    {
		    ?>
        	<td align=right <?=$onclick?>><b><?echo $resultado->fields["cantidad"]?></b></td>
        	<?
		    }
		    if(($ver['codigo']==1) ||($cantid==0))
		    {
		    ?>
        	<td align=left  <?=$onclick?>><b><?echo $resultado->fields["codigo"]?></b></td>
            <?
		    }
		    if(($ver['descripcion']==1) ||($cantid==0))
		    {
		    ?>
        	<td align=left  <?=$onclick?>><b><?echo $resultado->fields["descripcion"]?></b></td>
            <?
		    }
		    if(($ver['id_info_rma']==1) ||($cantid==0))
		    {
		    ?>
        	<td align=right <?=$onclick?>><b><?=$resultado->fields["id_info_rma"]?></b></td>
        	<?
		    }
            //$id_i_rma=trim($resultado->fields['id_info_rma']);

            $que=trim($resultado->fields['nombre_corto']);

              if ($que!="P")
                 {
                  if(($ver['dias_crea']==1) ||($cantid==0))
		          {
                  $fecha_actual=date("d/m/Y");
                  $fecha_base=fecha($resultado->fields["fecha_hist"]);
                  $color="";
                  $dias=diferencia_dias_habiles($fecha_base,$fecha_actual);
                  if ($dias>3) $color="yellow";
                  if ($dias>8) $color="red";
                  ?>
                  <td align=center  <?=$onclick?> bgcolor="<?=$color?>"><b><?echo $dias?></b></td><!--//Borggi-->
                  <?
                  }
                  if(($ver['dias_env']==1) ||($cantid==0))
		          {
		          ?>
                  <td align=center  <?=$onclick?>><b><?echo "&nbsp;"?></b></td><!--//Borggi-->
                  <?
		          }
		         }
              else {
              	    if(($ver['dias_crea']==1) ||($cantid==0))
		            {
                    ?>
                    <td align=center  <?=$onclick?>><b><?echo "&nbsp;"?></b></td><!--//Borggi-->
                    <?
		            }

              	    if(($ver['dias_env']==1) ||($cantid==0))
		            {
              	    $fecha_actual=date("d/m/Y");
                    $fecha_base=fecha($resultado->fields["fecha_hist"]);
                    $color="";
                    $dias=diferencia_dias_habiles($fecha_base,$fecha_actual);
                    if ($dias>10) $color="yellow";
                    if ($dias>20) $color="red";
		            ?>
                    <td align=center  <?=$onclick?> bgcolor="<?=$color?>"><b><?echo $dias?></b></td><!--//Borggi-->
            <?     }
                   }
            ?>
           <!-- <td align=left  <?//=$onclick?>><b>&nbsp;</b></td>-->
           <!-- <td align=left  <?//=$onclick?>><b><?//echo $resultado->fields["id_movimiento_material"]?></b></td>-->
            <?
            if(($ver['nro_caso']==1) ||($cantid==0))
		    {
		    ?>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["nrocaso"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["nombre"]?></b></td>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["pm"]?></b></td>
            <?
		    }
		    if(($ver['razon_social']==1) ||($cantid==0))
		    {
            ?>
            <td align=left  <?=$onclick?>><b><?echo $resultado->fields["razon_social"]?></b></td>
            <?
		    }
		    if(($ver['monto']==1) ||($cantid==0))
		    {
            ?>
            <td align=right <?=$onclick?> <?=$coment_precio?>>

            <table width=100% align=Center>
            <tr>
              <td width=20% align=center><b>U$S</b></td>
              <?$tot1=$resultado->fields["precio_stock"];
                $tot=formato_money($tot1 * $resultado->fields["cantidad"]);
              ?>
              <td align=right><b><?=formato_money($resultado->fields["totales"]);?></b></td>
            </tr>
            </table>
            </td>
            <?
		    }

            if(permisos_check("inicio","permiso_boton_cambiar_precio") && $cmd!= "historial")
            {
            $link=encode_link("stock_mod_precio.php",array("id_prod_esp"=>$resultado->fields["id_prod_esp"]));
            ?>
            <td><input type=button name=boton_precio value=$ onclick="window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1,scrollbars=1');" style="cursor:hand;"></td>
            <?
            }
         if($cmd=="cor" || $cmd=='prov' || $cmd=="real" || $cmd=="historial")
         {

             if(($ver['nombre_corto']==1) ||($cantid==0))
		     {
		      if($resultado->fields["comentario"]!="")
		      {
         	  ?>
              <td align=center  <?=$onclick?>><b><?echo $resultado->fields["comentario"]?></b></td><!--//Borggi-->
              <?
		      }
             else {
             ?>
              <td align=center  <?=$onclick?>><b><?echo $resultado->fields["ubicacion"]?></b></td><!--//Borggi-->
              <?
             }
		     }

         	if(($ver['lugar']==1) ||($cantid==0))
		    {
             ?>
          <td align=left  <?=$onclick?>><b><?echo $resultado->fields["lugar"]?></b></td>
          <?
		  }
          }
          if(($ver['void']==1) ||($cantid==0))
		  {
          ?>
          <td align=left <?=$onclick?>><b>
          <?
          $cadena=split(' ',$resultado->fields["voids"]);
          echo $cadena[0];
          ?>
          </b></td>
          <?
		  }
          $oc=$resultado->fields['nro_orden'];
          if(($ver['fecha_emision']==1) ||($cantid==0))
		  {
		  if($oc!="")
		  {
		  ?>
		  <td <?=$onclick?>>
		  <b><?=Fecha($resultado->fields['fecha_emision'])?></b>
		  </td>
          <?}
          else{?>
          <td <?=$onclick?>>
		  &nbsp;&nbsp;
		  </td>
          <?}
		  }?>
            </tr>

          <?
           $resultado->movenext();
        }//del for
     // } //de real y pendientes
	  ?>
</table>
</td>
</tr>
</table>

<table bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0 class="bordes">
 <tr>
  <td align="center">
   <table width="100%">
    <tr>
     <td align="center"><font size="2"><b>Referencias</b></font></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td align="center">
   <table width="100%">
    <tr>
     <td><font size="2"><b>Ubicación:</b></font></td>
     <td><font size="2"><b>T&nbsp;</b></font>= En Tránsito.</td>
     <td><font size="2"><b>C&nbsp;</b></font>= Coradir (SL o Bs. As.)</td>
     <td><font size="2"><b>P&nbsp;</b></font>= Proveedor</td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td>
   <table>
    <tr>
     <td><font size="2"><b>Días Creación (Dc):&nbsp;&nbsp; </b></font></td>
     <td width=15 bgcolor='yellow' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 3 Días.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
     <td width=15 bgcolor='Red' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 8 Días.&nbsp;&nbsp;</b></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td>
   <table>
    <tr>
     <td><font size="2"><b>Días Envio (De):&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b></font></td>
     <td width=15 bgcolor='yellow' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 10 Días.&nbsp;&nbsp;&nbsp;</b></td>
     <td width=15 bgcolor='Red' bordercolor='#000000' height=15>&nbsp</td>
     <td><b>&nbsp;&nbsp;Mas de 20 Días.&nbsp;&nbsp;</b></td>
    </tr>
   </table>
  </td>
 </tr>
</table>

</form>
<?
fin_pagina();
?>