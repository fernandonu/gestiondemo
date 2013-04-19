<?php
/*
$Author: mari $
$Revision: 1.182 $
$Date: 2007/01/08 18:56:22 $
*/
require_once("../../config.php");

//error_reporting(8);
require_once("../contactos_generales/funciones_contactos.php");
require_once("fun_cobranzas_atadas.php");
require_once("../bancos/balances/funciones_balance.php");

variables_form_busqueda("cob",array("ordenar"=>"fecha"));
//echo "id_cobranza= ".$_ses_global_extra[id]."\n";
//echo "id_factura=".$parametros[id_factura]."\n";


function ordenar($array, $index, $order='1', $natsort=FALSE, $case_sensitive=FALSE) {
	if(is_array($array) && count($array)>0) {
		foreach(array_keys($array) as $key) {
			$temp[$key]=$array[$key][$index];
		}
		if(!$natsort) {
			($order=='1')? asort($temp) : arsort($temp);
		}
		else {
			($case_sensitive)? natcasesort($temp) : natsort($temp);
			if($order!='1') {
				$temp=array_reverse($temp,TRUE);
			}
   		}
   //foreach(array_keys($temp) as $key) (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
		foreach(array_keys($temp) as $key) {
			$sorted[$key]=$array[$key];
		}
		return $sorted;
	}
	return $array;
}


//Funcion que verifica si el estado de los atadas es igual al primario
function verificar_estados_atadas($id_primario){
 $sql="select c.nro_factura,e.nombre
       from licitaciones.cobranzas c
       join licitaciones.atadas a on(c.id_cobranza=a.id_secundario)
       join licitaciones.historial_estados_cobranzas h using(id_cobranza)
       join licitaciones.estado_cobranzas e using(id_estado_cobranza)
       where a.id_primario=$id_primario and h.activo=1";
  $res=sql($sql) or fin_pagina();
  for($i=0;$i<$res->recordcount();$i++){
        $text.=$res->fields["nro_factura"]."  ".$res->fields["nombre"]."\n" ;
        $res->movenext();
  }//del for
  return $text;
}



function mostrar_log_estados($id_cobranza){

    $sql=" select * from
                   historial_estados_cobranzas
                   join estado_cobranzas using(id_estado_cobranza)
                   where id_cobranza=$id_cobranza order by fecha DESC";
    $res=sql($sql) or fin_pagina();
    ?>
    <table width=75% align=Center>
        <tr id=mo>
           <td>Estado</td>
           <td>Usuario</td>
           <td>Fecha y Hora</td>
        </tr>
    <?
    for ($i=0;$i<$res->recordcount();$i++){
    ?>
      <tr id=ma>
          <td><?=$res->fields["nombre"]?></td>
          <td><?=$res->fields["usuario"]?></td>
          <td><?=fecha($res->fields["fecha"])." ".substr($res->fields["fecha"],10,6)?></td>
      </tr>
    <?
    $res->movenext();
    }
    ?>
    </table>
    <?
}

//Funcion para guardar el estado de las cobranzas
function guardar_estado_cobranza($id_cobranza,$id_estado,$id_factoring=0){
    global $_ses_user;
    //los demas estados ya no son validos
    $sql="update licitaciones.historial_estados_cobranzas set activo=0 where id_cobranza=$id_cobranza";
    sql($sql) or fin_pagina();

    $usuario=$_ses_user["name"];
    $fecha=date("Y-m-d H:i:s");
    $campos="id_cobranza,id_estado_cobranza,usuario,fecha,activo";
    if ($id_factoring)
                  $campos.=" ,id_factoring";
    if ($id_estado<>-1) $values="$id_cobranza,$id_estado,'$usuario','$fecha',1";
                   else  $values="$id_cobranza,6,'$usuario','$fecha',1";
    if ($id_factoring)
                  $values.=",$id_factoring";
    //inserto el estado valido
    $sql=" insert into licitaciones.historial_estados_cobranzas ($campos) values ($values)";
    sql($sql) or fin_pagina();

}

//si flag==1 muestra los ingresos parciales (venta_factura)
function mostrar_ingresos ($id_cob,$flag=0,$arr="",$estado="") {

$id_volver=$id_cob;
$list_cob='(';
if (is_array($arr)) {
foreach($arr as $key => $value)
	  $list_cob.=$value.",";
$list_cob=substr_replace($list_cob,')',(strrpos($list_cob,',')));
} else {
  $list_cob.=$id_cob.')';
}
if ($flag==1) { //venta de factura de una sola factura-> ingresos parciales
//datos de los ingresos
$sql_ing="select detalle_ingresos.id_ingreso_egreso,ingreso_egreso.monto,dolar_ingreso as dolar_ingreso,
		  fecha_creacion,simbolo,tipo_ingreso.nombre as tipo_ingreso,caja.id_distrito,
		  tipo_cuenta_ingreso.nombre as tipo_cuenta, entidad.nombre as entidad
		  from licitaciones.cobranzas
          join licitaciones.datos_ingresos using (id_datos_ingreso)
          join licitaciones.pagos_ingreso using (id_datos_ingreso)
          join licitaciones.detalle_ingresos using (id_detalle_ingreso)
		  left join caja.ingreso_egreso on detalle_ingresos.id_ingreso_egreso=ingreso_egreso.id_ingreso_egreso
		  left join caja.caja using (id_caja)
		  left join licitaciones.entidad on ingreso_egreso.id_entidad=entidad.id_entidad
		  left join licitaciones.moneda on caja.id_moneda=moneda.id_moneda
		  left join caja.tipo_ingreso on ingreso_egreso.id_tipo_ingreso=tipo_ingreso.id_tipo_ingreso
		  left join caja.tipo_cuenta_ingreso on ingreso_egreso.id_cuenta_ingreso=tipo_cuenta_ingreso.id_cuenta_ingreso
		  where cobranzas.id_cobranza in $list_cob and detalle_ingresos.id_ingreso_egreso is not null";
$res_ing=sql($sql_ing) or fin_pagina();

//monto total
$sql="select sum(ingreso_egreso.monto) as total
      from licitaciones.cobranzas
      join licitaciones.datos_ingresos using (id_datos_ingreso)
      join licitaciones.pagos_ingreso using (id_datos_ingreso)
      join licitaciones.detalle_ingresos using (id_detalle_ingreso)
      join caja.ingreso_egreso on ingreso_egreso.id_ingreso_egreso=detalle_ingresos.id_ingreso_egreso
      where cobranzas.id_cobranza in $list_cob";
$res=sql($sql) or fin_pagina();
$total=$res->fields['total'];
}
else { //ingreso/egreso de una sola factura y de facturas atadas
$sql_ing="select cobranzas.id_ingreso_egreso,ingreso_egreso.monto,cobranzas.cotizacion_dolar as dolar_ingreso,
		  fecha_creacion,simbolo,tipo_ingreso.nombre as tipo_ingreso,caja.id_distrito,
		  tipo_cuenta_ingreso.nombre as tipo_cuenta, entidad.nombre as entidad
		  from licitaciones.cobranzas
		  left join caja.ingreso_egreso using (id_ingreso_egreso)
		  left join caja.caja using (id_caja)
		  left join licitaciones.entidad on ingreso_egreso.id_entidad=entidad.id_entidad
		  left join licitaciones.moneda on caja.id_moneda=moneda.id_moneda
		  left join caja.tipo_ingreso on ingreso_egreso.id_tipo_ingreso=tipo_ingreso.id_tipo_ingreso
		  join caja.tipo_cuenta_ingreso on ingreso_egreso.id_cuenta_ingreso=tipo_cuenta_ingreso.id_cuenta_ingreso
		  where id_cobranza in $list_cob";
$res_ing=sql($sql_ing) or fin_pagina();



if (is_array($arr)) {
   $sql="select sum(ingreso_egreso.monto) as total from licitaciones.cobranzas
         left join caja.ingreso_egreso using (id_ingreso_egreso)
         where id_cobranza in $list_cob";
   $res=sql($sql) or fin_pagina();
   $total=$res->fields['total'];
  }
  else $total=$res_ing->fields['monto'];
}


$visib = "none";
$archivo="../caja/ingresos_egresos.php";
$distrito=$res_ing->fields['id_distrito'];

$cant_ing=$res_ing->RecordCount();
if ($cant_ing > 0 ) {
	    echo "<br>";
	    echo "<table align=center width=95%><tr id=mo><td colspan=7><font size=+1>Detalle Ingresos</font></td></tr>";
        echo "<tr  bgcolor=$bgcolor2 ><td><b>Monto Total: " .$res_ing->fields['simbolo']."&nbsp;".formato_money($total);
          if (formato_money($res_ing->fields['dolar_ingreso'])!='0,00') { echo "&nbsp;&nbsp;&nbsp;&nbsp;Valor Dolar: ". formato_money($res_ing->fields['dolar_ingreso']); }
        if ($flag==0)
              echo"  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Ingresos:".fecha($res_ing->fields['fecha_creacion']);
           echo "</td>";
        echo "<td align=right><b>Mostrar Detalles:</b>
         <input type=checkbox class='estilos_check' name=det_ing  onclick='javascript:(this.checked)?Mostrar(\"tabla_det_ing\"):Ocultar(\"tabla_det_ing\");'></td></tr></table>";
        echo " <div id='tabla_det_ing' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr id=mo>";
	    echo "<td>ID</td>";
	    echo "<td>Monto</td>";
	    echo "<td>Tipo Ingreso</td>";
	    echo "<td>Cliente</td>";
	    echo "<td>Tipo Cuenta</td>";
	    if ($flag==1) echo "<td>Fecha Ingreso</td>";
	    echo "</tr>";


while (!$res_ing ->EOF) {
     	echo "<tr id=ma>";
     	$id_ingreso_egreso=$res_ing->fields['id_ingreso_egreso'];
	    if ($estado=="") //una factura
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"ingreso","pagina_viene"=>'lic_cobranzas',"id_cobranza"=>$id_volver,"distrito"=>$distrito));
     	  else  //facturas atadas
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"ingreso","pagina_viene"=>'lic_cobranzas',"id_cob"=>$id_volver,"distrito"=>$distrito));

     	$onclick_caja="window.open(\"$link_caja\",\"\",\"\")";
     	echo "<td title='ingreso'><a style='cursor: hand;' onclick='$onclick_caja'>$id_ingreso_egreso </a>";
     	echo "</td>";
     	echo "<td>".$res_ing->fields['simbolo']." ". formato_money($res_ing->fields['monto'])."</td>";
	    echo "<td>".$res_ing->fields['tipo_ingreso']."</td>";
	    echo "<td>".$res_ing->fields['entidad']."</td>";
	    echo "<td>".$res_ing->fields['tipo_cuenta']." </td>";
	    if ($flag==1)
	      echo "<td>".fecha($res_ing->fields['fecha_creacion'])." </td>";
	    echo "</tr>";
	    $res_ing->MoveNext();
}
echo "</table></div>";
}
}

function mostrar_ficticio ($id_cob,$cod,$nombre,$ctrl=0) {

$sql="select simbolo,monto_detalle,dolar_egreso,fecha_detalle,control_egreso from licitaciones.detalle_egresos
      join licitaciones.moneda using (id_moneda)
      where id_cobranza=$id_cob and id_cob_egreso=$cod";

$res=sql($sql) or fin_pagina();

while (!$res->EOF) {
echo "<tr id=ma><td>&nbsp;</td> ";
echo "<td align='right'><font color='black'>$nombre:</font></td><td>&nbsp;</td><td>".$res->fields['simbolo']."".formato_money($res->fields['monto_detalle'])."</td>";
echo "<td colspan=2>&nbsp;<font size=1 color='black'>(este monto no egresó de caja)</font></td>";
 if ($res->fields['control_egreso']==0 || $ctrl==1)
   echo "<td>&nbsp;</td><td>".fecha($res->fields['fecha_detalle'])."</td>";
   else echo "<td colspan=2>&nbsp;</td>";
echo "</tr>";
$res->Movenext();
}
}



function mostrar_egresos ($id_cob,$flag,$estado="",$id_datos_ingreso="") {
//si flag=1 => ya se realizo el egreso
//si flag=0 => solo se guardaron los detalles
//si es venta de factura necesita ejecutar las dos consulta
global $db;
$id_volver=$id_cob;
//ya se realizo el egreso
$sql_egreso1="select detalle_egresos.id_cob_egreso,descripcion,monto,dolar_egreso,razon_social,caja.id_moneda,
              caja.id_distrito,iddepósito,
			  tipo_egreso.nombre,concepto,plan,simbolo,caja.fecha,id_ingreso_egreso
 			  from licitaciones.detalle_egresos
 		      join caja.ingreso_egreso using (id_ingreso_egreso)
			  join caja.caja using (id_caja)
 		      join licitaciones.moneda on caja.id_moneda=moneda.id_moneda
 			  join licitaciones.egreso_cob using (id_cob_egreso)
			  join general.proveedor on ingreso_egreso.id_proveedor=proveedor.id_proveedor
			  join caja.tipo_egreso on ingreso_egreso.id_tipo_egreso=tipo_egreso.id_tipo_egreso
			  join general.tipo_cuenta on ingreso_egreso.numero_cuenta=tipo_cuenta.numero_cuenta
	          left join bancos.depósitos using (iddepósito)
			  where id_cobranza=$id_cob and id_cob_egreso!=7 and id_cob_egreso!=8 order by ingreso_egreso.id_ingreso_egreso";


$sql_monto1="select sum(monto) as total from licitaciones.detalle_egresos join caja.ingreso_egreso
using (id_ingreso_egreso) where id_cobranza=$id_cob";


if ($id_datos_ingreso!="")  {
	$res_eg=sql($sql_egreso1) or fin_pagina();
   	$res_monto=sql($sql_monto1) or fin_pagina();
    $monto1=$res_monto->fields['total'];
}

elseif ($flag==1){
    $res_eg=sql($sql_egreso1) or fin_pagina();
    $res_monto=sql($sql_monto1) or fin_pagina();
    $monto1=$res_monto->fields['total'];
    $monto2=0;
}



//monto efectivo y cheque
$sql_efec="select sum(monto_detalle) as monto_detalle from licitaciones.detalle_egresos where id_cobranza=$id_cob and id_cob_egreso=7";
$sql_cheque="select sum(monto_detalle) as monto_detalle from licitaciones.detalle_egresos where id_cobranza=$id_cob and id_cob_egreso=8";



$res_efec=sql($sql_efec) or fin_pagina();
$res_cheque=sql($sql_cheque) or fin_pagina();
$monto_total=$monto1+$res_efec->fields['monto_detalle'] + $res_cheque->fields['monto_detalle'];
//$monto_total=0;
$visib = "none";
$archivo="../caja/ingresos_egresos.php";
$distrito=$res_eg->fields['id_distrito'];
if (isset($res_eg)) $cant1=$res_eg->RecordCount();
    else $cant1=0;


if ($cant1 > 0) {
	    echo "<br>";
	    echo "<table align=center width=95%><tr id=mo><td colspan=7><font size=+1>Detalle Egresos</font></td></tr>";
        echo "<tr  bgcolor=$bgcolor2 ><td title='egresos + ficticio'><b>Monto total: " .$res_eg->fields['simbolo']."&nbsp;".formato_money($monto_total);
          if (formato_money($res_eg->fields['dolar_egreso'])!='0,00') { echo "&nbsp;&nbsp;&nbsp;&nbsp;Valor Dolar: ". formato_money($res_eg->fields['dolar_egreso']); }
       if ($flag==1 && $id_datos_ingreso=="") echo " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Egresos:".fecha($res_eg->fields['fecha']);
       echo "</td><td align=right><b>Mostrar Detalles:</b>
         <input type=checkbox class='estilos_check' name=det_eg  onclick='javascript:(this.checked)?Mostrar(\"tabla_det_eg\"):Ocultar(\"tabla_det_eg\");'></td></tr></table>";
        echo " <div id='tabla_det_eg' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr id=mo><td>&nbsp;</td><td>&nbsp;</td>";
	    echo "<td>ID</td>";
	    echo "<td>Monto</td>";
	    echo "<td>Tipo Egreso</td>";
	    echo "<td>Proveedor</td>";
	    echo "<td>Concepto y Plan</td>";
	    if ($flag==0 || $id_datos_ingreso!="") echo "<td>Fecha</td>";
	    echo "<tr>";


if ($cant1 > 0) { //datos de los esgreso en caja
 while (!$res_eg ->EOF) {
     	echo "<tr id=ma>";
     	if($res_eg->fields['id_cob_egreso']==9) //traigo cheques diferidos de la BD
     	 echo "<td><input type='checkbox' class='estilos_check' name='chk_diferido' onclick='javascript:(this.checked)?Mostrar(\"tabla_diferido\"):Ocultar(\"tabla_diferido\");'></td>";
     	else
     	 echo "<td>&nbsp;</td>";
     	echo "<td align=right><font color=black>".strtoupper($res_eg->fields['descripcion']).":</font></td>";
	    $id_ingreso_egreso=$res_eg->fields['id_ingreso_egreso'];
	    $id_deposito=$res_eg->fields['iddepósito'];
	    if ($estado=="") //una factura
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"egreso","pagina_viene"=>'lic_cobranzas',"id_cobranza"=>$id_volver,"distrito"=>$distrito));
     	  else  //facturas atadas
     	  $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"egreso","pagina_viene"=>'lic_cobranzas',"id_cob"=>$id_volver,"distrito"=>$distrito));

      	  $onclick_caja="window.open(\"$link_caja\",\"\",\"\")";
    	  $link_dep=encode_link('../bancos/bancos_movi_deppen.php',array("Modificar_Deposito_Numero"=> $id_deposito,"Modificar"=>1));
     	  $onclick_dep="window.open(\"$link_dep\",\"\",\"\")";
     	if ($id_deposito!=null || $id_deposito !="") {
     	echo "<td><table><tr><td title='egreso'><a style='cursor: hand;' onclick='$onclick_caja'>$id_ingreso_egreso </a></td>
     	       <td title='depósito'><a style='cursor: hand;' onclick='$onclick_dep'>/$id_deposito </a></td>
     	       </tr></table>";
     	} else
             echo "<td title='egreso'><a style='cursor: hand;' onclick='$onclick_caja'>$id_ingreso_egreso </a>";
     	echo "</td>";
     	echo "<td>".$res_eg->fields['simbolo']." ". formato_money($res_eg->fields['monto'])."</td>";
	    echo "<td>".$res_eg->fields['nombre']."</td>";
	    echo "<td>".$res_eg->fields['razon_social']."</td>";
	    echo "<td>".$res_eg->fields['concepto']."[". $res_eg->fields['plan']."] </td>";
	    if ($flag==0 || $id_datos_ingreso!="") echo "<td>".fecha($res_eg->fields['fecha'])."</td>";
	    echo"</tr>";

	    if($res_eg->fields['id_cob_egreso']==9) //traigo cheques diferidos de la BD
	    {echo "<tr><td></td><td colspan=6>";
	    echo " <div id='tabla_diferido' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr id=mo>";
	    echo "<td>Numero</td>";
	    echo "<td>Emision</td>";
	    echo "<td>Vencimiento</td>";
	    echo "<td>Monto</td>";
	    echo "<td>Comentario</td>";
	    echo "</tr>";
	    $sql = "select * from cheques_diferidos join cheque_cobranza using(id_chequedif) where cheque_cobranza.id_cobranza=$id_cob";
	    $resultado_diferido = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
        while(!$resultado_diferido->EOF)
	    {
	     echo "<tr id=ma>";
	     echo "<td>".$resultado_diferido->fields['nro_cheque']."</td>";
	     echo "<td>".Fecha($resultado_diferido->fields['fecha_ingreso'])."</td>";
	     echo "<td>".Fecha($resultado_diferido->fields['fecha_vencimiento'])."</td>";
	     echo "<td>".$resultado_diferido->fields['monto']."</td>";
	     echo "<td>".$resultado_diferido->fields['comentario']."</td>";
	     echo "</tr>";
	     $resultado_diferido->MoveNext();
	    }
	    echo "</table>";
	    echo "</div>";
	    echo "</td></tr>";
	    }
	   $res_eg->MoveNext();
}
  mostrar_ficticio($id_cob,7,'EFECTIVO');
  mostrar_ficticio($id_cob,8,'CHEQUE');
}
   echo "</table>";
   echo "</div>";
} else { //muestra solo el efectivo y el cheque, en el caso que no se haya guardado otra descripcion
	$sql="select simbolo,dolar_egreso from licitaciones.detalle_egresos join licitaciones.moneda using (id_moneda)
          where id_cobranza=$id_cob and (id_cob_egreso=7 or id_cob_egreso=8)";
          $res=sql($sql) or fin_pagina();

    if ($res->RecordCount() > 0) {
	    echo "<br>";
	    echo "<table align=center width=95%><tr id=mo><td colspan=7><font size=+1>Detalle Egresos</font></td></tr>";
        echo "<tr  bgcolor=$bgcolor2 ><td title='egresos + ficticio'><b>Monto total: " .$res->fields['simbolo']."&nbsp;".formato_money($monto_total);
          if (formato_money($res->fields['dolar_egreso'])!='0,00') { echo "&nbsp;&nbsp;&nbsp;&nbsp;Valor Dolar: ". formato_money($res->fields['dolar_egreso']); }
        echo "</td><td align=right><b>Mostrar Detalles:</b>
         <input type=checkbox class='estilos_check' name=det_eg  onclick='javascript:(this.checked)?Mostrar(\"tabla_det_eg\"):Ocultar(\"tabla_det_eg\");'></td></tr></table>";
        echo " <div id='tabla_det_eg' style='display:$visib'>";
        echo "<table align=center width=95%>";
        echo "<tr id=mo><td>&nbsp;</td><td>&nbsp;</td>";
	    echo "<td>ID</td>";
	    echo "<td>Monto</td>";
	    echo "<td>Tipo Egreso</td>";
	    echo "<td>Proveedor</td>";
	    echo "<td>Concepto y Plan</td>";
	    echo "<td>Fecha</td>";
	    echo "</tr>";
         mostrar_ficticio($id_cob,7,'EFECTIVO',1);
         mostrar_ficticio($id_cob,8,'CHEQUE',1);
        echo "</table></div>";
        }
  }
} //fin de mostrar egresos



if ($_POST["activos"]=="activos"){
 //script que actualiza los datos correspondientes
 //selecciono todas las cobranzas con id_secundario en atadas
 $sql="select distinct(id_secundario) from atadas";
 $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
 $db->starttrans();
 for($i=0;$i<$resultado->recordcount();$i++){
   $id_cobranza=$resultado->fields["id_secundario"];
   $sql="update cobranzas set activo=0 where id_cobranza=$id_cobranza";
   $db->execute($sql) or die($db->errormsg()."<br>".$sql);
   $resultado->movenext();
 } // del for
 $db->CompleteTrans();
}



if ($_ses_global_backto) {
	$parametros[id]=$_ses_global_extra[id];
 	phpss_svars_set("_ses_global_backto", "");
 	phpss_svars_set("_ses_global_extra", array());
}

$valor_dolar=$_POST["valor_dolar"] or $valor_dolar=$parametros["valor_dolar"];



if (!$valor_dolar) {
                    $sql="select valor from dolar_general";
                    $res_dolar=sql($sql)  or fin_pagina();
                    $valor_dolar=number_format($res_dolar->fields["valor"],"2",".","");
                    }



$download=$parametros['download'];
$datos_barra = array(
					array(
						"descripcion"	=> "Pendientes",
						"cmd"			=> "pendiente"
						//"sql_contar"	=> "SELECT count(*) as cant FROM cobranzas
						//					WHERE estado='PENDIENTE'"
						),
					array(
						"descripcion"	=> "Finalizadas",
						"cmd"			=> "finalizada"
						//"sql_contar"	=> "SELECT count(*) as cant FROM cobranzas
						//					WHERE estado='FINALIZADA'"
						)
				 );


if ($cmd == "") {
	$cmd="pendiente";
	$_ses_cob["cmd"] = $cmd;
	phpss_svars_set("_ses_cob", $_ses_cob);
}


//Esta parte se utiliza para que cuando las facturas esten atadas se puedan guardar comentarios
if ($_POST["comentarios_finalizadas"] && $_POST["comentario_nuevo"]) {
	$id_cobranza = $_POST["finalizada_id_cobranza"];
	$comentarios = $_POST["comentario_nuevo"];
    $ultimo_usuario = $_ses_user["name"];
    
    $fecha=date("Y-m-d H:i:s");
    $sql_array[] = "UPDATE cobranzas SET
                     ultimo_comentario='$comentarios',usuario_ultimo_comentario='$ultimo_usuario',fecha_ultimo_comentario='$fecha'
                     WHERE id_cobranza=$id_cobranza";
	$sql_array[] = nuevo_comentario($id_cobranza,"COBRANZAS",$comentarios);
	sql($sql_array) or fin_pagina();
	//$cmd1="detalle_cobranza";

}

if ($_POST["comentarios"] && $_POST["comentario_nuevo"]) {
	$id_cobranza = $_POST["cob_id_cobranza"] or $id_cobranza=$parametros["id"];
	$comentarios = $_POST["comentario_nuevo"];
    $ultimo_usuario = $_ses_user["name"];
    
	$comentarios=ereg_replace("'","\'",$comentarios);
    $comentarios=ereg_replace("\"","\\\"",$comentarios);	
    
    $fecha=date("Y-m-d H:i:s");
    $sql_array[] = "UPDATE cobranzas SET
                     ultimo_comentario='$comentarios',usuario_ultimo_comentario='$ultimo_usuario',fecha_ultimo_comentario='$fecha'
                     WHERE id_cobranza=$id_cobranza";

    $id_cobranza=$_POST["id_comentario"];
	$comentarios=$_POST["comentario_nuevo"];
	$sql_array[] = nuevo_comentario($id_cobranza,"COBRANZAS",$comentarios);
	sql($sql_array) or fin_pagina();
	$cmd1="detalle_cobranza";
	$parametros["id"]=$id_cobranza;
}

if ($parametros[id_factura]) {
	if ($parametros[id])
		editar_factura($parametros[id],$parametros[id_factura]);
	else
		nueva_factura($parametros[id_factura]);
}

elseif ($cmd1 == "detalle_cobranza") {
	$id_cobranza = $parametros["id"];
	$sql1="SELECT id_secundario FROM atadas WHERE id_primario=$id_cobranza";
	$result=sql($sql1) or fin_pagina();
	//echo $result->fields["id"];
	if ($cmd == "pendiente") {
		if ($result->recordcount()>0) editar_atadas($id_cobranza,$result);
		else editar_factura($id_cobranza);
	}
	elseif ($cmd == "finalizada"){
		if ($result->recordcount()>0) detalle_atadas($id_cobranza,$result);
		else detalle_factura($id_cobranza);
	}
	else
		listado_facturas();
}
elseif ($cmd1 == "modificar_comentario") {
	$id_comentario = $parametros["id_comentario"];
	editar_comentario($id_comentario,"detalle_cobranza");
}

elseif ($_POST["desatar"]) {
	$id=$_POST["id_comentario"];
	$ok=1;
	$db->begintrans();
	// restableser los comentarios de cada cobranza
    $sql="select ultimo_comentario,fecha_ultimo_comentario,usuario_ultimo_comentario
          from cobranzas
          where id_cobranza=$id";
    $result=sql($sql) or fin_pagina();
    $ultimo_comentario = $result->fields["ultimo_comentario"];
    $fecha_ultimo_comentario = $result->fields["fecha_ultimo_comentario"];
    $usuario_ultimo_comentario = $result->fields["usuario_ultimo_comentario"];
	$sql="select id_secundario from atadas where id_primario=$id";
	$res=$db->execute($sql);
	while (($fila=$res->fetchrow()) and $ok) {
		$sql="select id_comentario from atadas_comentarios where id_cobranza=".$fila["id_secundario"];
		$res1=$db->execute($sql);
        if ($ultimo_comentario) {
                $sql3=" update cobranzas
                        set ultimo_comentario='$ultimo_comentario',
                            usuario_ultimo_comentario='$usuario_ultimo_comentario',
                            fecha_ultimo_comentario='$fecha_ultimo_comentario'
                         where id_cobranza=".$fila["id_secundario"];
                 sql($sql3) or fin_pagina();
        }



		while ($fila1=$res1->fetchrow() and $ok) {
			$sql="UPDATE gestiones_comentarios SET id_gestion=".$fila["id_secundario"]
				."where id_comentario=".$fila1["id_comentario"];
			$ok=$db->execute($sql);
		}
		if ($ok and $res1->recordcount()>0){
			$sql="delete from atadas_comentarios where id_cobranza=".$fila["id_secundario"];
			$ok=$db->execute($sql) or die($sql);
		}
        if ($ok) {
                $sql="update cobranzas set activo=1 where id_cobranza=".$fila["id_secundario"];
                $ok1=$db->execute($sql) or die($sql);
                }
	}
	// Copiar los comentarios que no sena de ninguno
	$sql="select fecha,comentario,ultimo_usuario from gestiones_comentarios where id_gestion=$id and "
		."id_comentario NOT IN (select id_comentario from atadas_comentarios where id_cobranza=$id)";
		//."where id_gestion=$id and id_cobranza=$id";
	$res1=$db->execute($sql) or die($db->errormsg(). " - " .$sql);
	while (($fila1=$res1->fetchrow()) and $ok) {
		$res->MoveFirst();
		//print_r($res->fields);
		while (($fila=$res->fetchrow()) and $ok) {
			$sql="insert into gestiones_comentarios (id_gestion,fecha,comentario,ultimo_usuario,tipo) "
				."Values (".$fila["id_secundario"].",'".$fila1["fecha"]."','".$fila1["comentario"]."','".$fila1["ultimo_usuario"]."','COBRANZAS')";
			//echo $sql."<br><br>";
			$ok=$db->execute($sql);
		}
	}
	if ($ok) {
		$sql="delete from atadas_comentarios where id_cobranza=$id";
		$ok=$db->execute($sql);
	}
	if ($ok) {
		$sql="delete from atadas where id_primario=$id";
		$ok=$db->execute($sql);
	}
	if ($ok){
		$db->committrans();
	}
	else {
		$db->RollbackTrans();
		error($sql);
	}
	echo "<script>window.location='lic_cobranzas.php';</script>";
}
elseif ($cmd1 == "detalle") {
	$id_lic = $parametros["id_lic"];
	if ($id_lic == "") {
		listado_licitaciones();
	}
	else {
		detalle_cobranzas($id_lic);
	}
}
elseif ($_POST["cob_modificar_factura"]) {
	$id_cobranza = $_POST["cob_id_cobranza"];
	editar_factura($id_cobranza);
}
elseif ($_POST["cob_finalizar_factura"]) {
	$id_cobranza = $_POST["cob_id_cobranza"] ;
	$id_licitacion = $_POST["cob_id_lic"];
	if ($_POST["cob_atadas"]) {
		$sql="select id_secundario from atadas where id_primario=$id_cobranza";
		$res=sql($sql) or fin_pagina();
		$arr[]=$id_cobranza;
		while ($arr1=$res->fetchrow())
			$arr[]=$arr1["id_secundario"];
		while (list($key,$co)=each($arr)) {

			$array_sql[]="UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='".$_ses_user['name']."',fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza=$co";

		}
		sql($array_sql) or fin_pagina();
		$res->movefirst();
		detalle_atadas($id_cobranza,$res);
	}
	else {

		$sql = "UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='".$_ses_user_name."',fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza=$id_cobranza";

		sql($sql) or fin_pagina();
		detalle_factura($id_cobranza);
	}
}
elseif ($_POST["cob_agregar_factura"] || $parametros['terminar_factura']=="auto") {
	$ID = $_POST["cob_id_lic"];
    // ESTOS DATOS YA ESTAN EN LA FACTURA
	$fecha_factura = $_POST["cob_fecha_factura"] or Error("Falta ingresar la fecha de facturación");
	$monto = $_POST["cob_monto"] or Error("Falta ingresar el monto");
	$id_moneda = $_POST["cob_id_moneda"];
	$nro_factura = $_POST["cob_nro_factura"] or Error("Falta ingresar el número de factura");
	$fecha_presentacion = $_POST["cob_fecha_presentacion"];
	$fecha_estimativa = $_POST["cob_fecha_estimativa"];
	$fecha_legal = $_POST["cob_fecha_legal"];
	$nro_carpeta = $_POST["cob_nro_carpeta"];
	$nro_remitos = "'".$_POST["cob_nro_remitos"]."'" or $nro_remitos = "NULL";
	$id_entidad = $_POST["cob_id_entidad"];
	$estado_nombre = $_POST["cob_estado"];
	$comentarios = $_POST["cob_comentarios"];
	$nombre = $_POST["cob_nombre"];
    if (!$error) {
    /* NO HACE FALTA CONTROLAR AQUI PORQUE SE CONTROLA EN FACTURA_NUEVA
	$sql = "SELECT id_factura FROM cobranzas WHERE id_factura = '$_POST[cob_id_factura]'";
	$result = sql($sql) or die;
	if ($result->RecordCount() > 0) {
	Error("Ya existe una factura con el número $nro_factura");
	}
*/
	if (FechaOk($fecha_factura)) {
			$fecha_factura = "'".Fecha_db($fecha_factura)."'";
		}
		else {
			Error("El formato de la fecha de facturación no es válido");
		}
		if (!es_numero($monto)) {
			Error("El formato del monto no es válido");
		}
		if ($fecha_presentacion != "") {
			if (FechaOk($fecha_presentacion)) {
				$fecha_presentacion = "'".Fecha_db($fecha_presentacion)."'";
			}
			else {
				Error("El formato de la fecha de presentación no es válido");
			}
		}
		else {
			$fecha_presentacion="NULL";
		}
		if ($fecha_estimativa != "") {
			if (FechaOk($fecha_estimativa)) {
				$fecha_estimativa = "'".Fecha_db($fecha_estimativa)."'";
			}
			else {
				Error("El formato de la fecha de estimativa no es válido");
			}
		}
		else {
			$fecha_estimativa="NULL";
		}
		if ($fecha_legal != "") {
			if (FechaOk($fecha_legal)) {
				$fecha_legal = "'".Fecha_db($fecha_legal)."'";
			}
			else {
				Error("El formato de la fecha legal no es válido");
			}
		}
		else {
			$fecha_legal="NULL";
		}
        if ($estado_nombre) {
            $fecha_estado = "'".date("Y-m-d")."'";
        }
        else {
            $estado_nombre = "NULL";
            $fecha_estado = "NULL";
        }
	}
	if (!$error) {
		$sql_array = array();
		$sql = "INSERT INTO cobranzas (id_licitacion,nro_carpeta,nombre,";
        //estos datos ya estan en la factura
		$sql .= "nro_factura,monto,fecha_factura,id_moneda,";
		$sql .= "fecha_presentacion,fecha_estimativa,fecha_legal,id_entidad,";
        $sql .= "estado,estado_nombre,fecha_estado,nro_remitos,id_factura";
        if ($comentarios != "") {
             $sql.=",ultimo_comentario,usuario_ultimo_comentario,fecha_ultimo_comentario";
             }
        $sql.= " ) ";
		$sql .= "VALUES ($ID,'$nro_carpeta','$nombre',";
        //estos datos ya estan en la factura
		$sql .= "'$nro_factura','$monto',$fecha_factura,$id_moneda,";
		$sql .= "$fecha_presentacion,$fecha_estimativa,$fecha_legal,$id_entidad,";
		$sql .= "'PENDIENTE',$estado_nombre,$fecha_estado,$nro_remitos,$_POST[cob_id_factura]";
        if ($comentarios != "") {
             $usuario=$_ses_user["name"];
             $fecha=date("Y-m-d H:i:s");
             $sql.=",'$comentarios','$usuario','$fecha'";
             }
        $sql.=" )";
		$sql_array[] = $sql;
		if ($comentarios != "") {
			$sql = nuevo_comentario("currval('cobranzas_id_cobranza_seq')","COBRANZAS",$comentarios);
			$sql_array[] = $sql;
		}
		sql($sql_array) or fin_pagina();
	 if (es_numero($ID))
		detalle_cobranzas($ID);
	 else
	 {
		//temporal cambiar por el link correcto
		$informar="La cobranza de la factura Nº $nro_factura se actualizo exitosamente";
	 	header("location: ".encode_link("$html_root/modulos/facturas/factura_listar.php",array("informar"=>$informar)));
	 }

	}
	else {
		Aviso("No se pudo cargar la factura<br>Complete todos los datos con <font color=#ff0000>*</font>");
		nueva_factura($_POST[cob_id_factura]);
	}
}
elseif ($_POST["cob_guardar_factura"]) {
    global $db;
	$ID = $_POST["cob_id_lic"];
	$nro_factura = $_POST["cob_nro_factura"] or Error("Falta ingresar el número de factura");
	$fecha_factura = $_POST["cob_fecha_factura"] or Error("Falta ingresar la fecha de facturación");
	$monto = $_POST["cob_monto"] or Error("Falta ingresar el monto");
	$id_moneda = $_POST["cob_id_moneda"];
	$nro_remitos = "'".$_POST["cob_nro_remitos"]."'" or $nro_remitos = "NULL";
	$fecha_presentacion = $_POST["cob_fecha_presentacion"];
	$fecha_estimativa = $_POST["cob_fecha_estimativa"];
	$fecha_legal = $_POST["cob_fecha_legal"];
	$nro_carpeta = $_POST["cob_nro_carpeta"];
	$nombre = $_POST["cob_nombre"];
	$id_entidad = $_POST["cob_id_entidad"] OR $id_entidad='NULL' ;
	$id_cobranza = $_POST["cob_id_cobranza"] or $id_cobranza=$parametros[id];
	$comentarios = $_POST["comentario_nuevo"];
	$comentarios=ereg_replace("'","\'",$comentarios);
    $comentarios=ereg_replace("\"","\\\"",$comentarios);	
    $estado_nombre = $_POST["cob_estado"];
    $ultimo_usuario = $_ses_user["name"];
    $id_factura=$_POST["cob_id_factura"] or $id_factura='NULL';
    $aceptacion_definitiva=$_POST["cob_aceptacion_definitiva"];
    $fecha=date("Y-m-d H:i:s");
    $estado_cobranza=$_POST["estado_cobranzas"];
    $id_factoring=$_POST["factoring"];
	if (!$error) {
    /* NO HACE FALTA CONTROLAR PORQUE SE CONTROLA AL CREARLA
		$sql = "SELECT nro_factura FROM cobranzas WHERE nro_factura = '$nro_factura'";
		$result = sql($sql) or die;
		if ($result->RecordCount() == 0) {
			Error("No existe una factura con el número $nro_factura");
		}
*/
	if (FechaOk($fecha_factura)) {
			$fecha_factura = "'".Fecha_db($fecha_factura)."'";
	}
	else {
			Error("El formato de la fecha de facturación no es válido");
		}
		if (!es_numero($monto)) {
			Error("El formato del monto no es válido");
		}
		if ($fecha_presentacion != "") {
			if (FechaOk($fecha_presentacion)) {
				$fecha_presentacion = "'".Fecha_db($fecha_presentacion)."'";
			}
			else {
				Error("El formato de la fecha de presentación no es válido");
			}
		}
		else {
			$fecha_presentacion="NULL";
		}
		if ($fecha_estimativa != "") {
			if (FechaOk($fecha_estimativa)) {
				$fecha_estimativa = "'".Fecha_db($fecha_estimativa)."'";
			}
			else {
				Error("El formato de la fecha de estimativa no es válido");
			}
		}
		else {
			$fecha_estimativa="NULL";
		}
		if ($fecha_legal != "") {
			if (FechaOk($fecha_legal)) {
				$fecha_legal = "'".Fecha_db($fecha_legal)."'";
			}
			else {
				Error("El formato de la fecha legal no es válido");
			}
		}
		else {
			$fecha_legal="NULL";
		}
        if ($estado_nombre) {
            $fecha_estado = "'".date("Y-m-d")."'";
        }
        else {
            $estado_nombre = "NULL";
            $fecha_estado = "NULL";
        }
    if ($id_factoring==-1)  $id_factoring=0;
	}
	if (!$error) {
		$sql_array = array();
        $db->starttrans();
		$sql = "UPDATE cobranzas SET nro_carpeta='$nro_carpeta',";
		$sql .= "nro_factura='$nro_factura',monto='$monto',fecha_factura=$fecha_factura,id_moneda=$id_moneda,id_factura=$id_factura,";
		$sql .= "nombre='$nombre',fecha_presentacion=$fecha_presentacion,";
		$sql .= "fecha_estimativa=$fecha_estimativa,fecha_legal=$fecha_legal,";
		$sql .= "estado_nombre=$estado_nombre,fecha_estado=$fecha_estado,";
		$sql .= "nro_remitos=$nro_remitos,aceptacion_definitiva='$aceptacion_definitiva'";
        if ($comentarios)
                $sql .= ",ultimo_comentario='$comentarios',usuario_ultimo_comentario='$ultimo_usuario',fecha_ultimo_comentario='$fecha'";
		$sql .= "WHERE id_cobranza=$id_cobranza";

        if ($estado_cobranza<>-1)    guardar_estado_cobranza($id_cobranza,$estado_cobranza,$id_factoring);
                         elseif ($id_factoring)
                                    guardar_estado_cobranza($id_cobranza,-1,$id_factoring);
	    if (!($result=$db->Execute($sql)))
	       {
		    //esa palabra nunca sale al principio
		    if (strpos($db->ErrorMsg(),"factura_unica"))
		               Error("Esa factura ya esta en cobranzas");
		               else
	                   die($db->ErrorMsg()."<br>".$sql);
	       }
		if ($comentarios != "")
		 {
		  if (!$_POST["cob_atadas"])
			{
              $sql_array[] = "UPDATE cobranzas SET
                              ultimo_comentario='$comentarios',usuario_ultimo_comentario='$ultimo_usuario',fecha_ultimo_comentario='$fecha'
                              WHERE id_cobranza=$id_cobranza";
 			  $sql_array[] = nuevo_comentario($id_cobranza,"COBRANZAS",$_POST["comentario_nuevo"]);
			  sql($sql_array) or fin_pagina();
			}
		}
        $db->completetrans();
		listado_facturas();
	}
	else {
		Aviso("No se pudo modificar la factura");
		editar_factura($id_cobranza);
	}
}
elseif ($_POST["guardar_comentario"]) {
	$id_comentario = $_POST["id_comentario"];
	$id_cobranza = $_POST["id_gestion"];
	$sql1="SELECT id_secundario FROM atadas WHERE id_primario=$id_cobranza";
	$result=sql($sql1) or fin_pagina();
	if (guardar_comentario()) {
		if ($result->recordcount()>0) editar_atadas($id_cobranza,$result);
		else editar_factura($id_cobranza);
	}
	else {
		editar_comentario($id_comentario,"detalle_cobranza");
	}
}
elseif ($cmd == "pendiente") {
	listado_facturas();
}
elseif ($cmd == "finalizada") {
	listado_facturas();
}

if ($_POST["script_estados"]){
      $db->starttrans();
      $sql="select * from licitaciones.cobranzas ";
      $res=sql($sql) or fin_pagina();
      for($i=0;$i<$res->recordcount();$i++){
            $id_cobranza=$res->fields["id_cobranza"];
            $fecha_presentacion=$res->fields["fecha_presentacion"];
            if ($fecha_presentacion) $id_estado="2";
                                  else $id_estado="1";
           $sql="insert into licitaciones.historial_estados_cobranzas
                         (id_cobranza,id_estado_cobranza,usuario,fecha,activo)
                          values
                          ($id_cobranza,$id_estado,'sistema',current_date,1)";
           sql($sql) or fin_pagina();
           $res->movenext();
         }
      $db->completetrans();
}
///ACA CORRO EL SCRIPT PARA LLEVAR EL ULTIMO USUARIO
if ($_POST["script_uu"]){
    $db->starttrans();
    $sql=" select id_cobranza from licitaciones.cobranzas";
    $cobranzas=sql($sql) or fin_pagina();
    for($i=0;$i<$cobranzas->recordcount();$i++){
      $id_cobranza=$cobranzas->fields["id_cobranza"];
      $sql="select max(fecha) as fecha,ultimo_usuario,comentario
            from gestiones_comentarios where
            id_gestion=$id_cobranza and tipo='COBRANZAS'
            group by ultimo_usuario,comentario
            order by fecha DESC
            LIMIT 1";
      $result=sql($sql) or fin_pagina();
      if ($result->recordcount()>0){
             $uc=$result->fields["comentario"];
             $fuc=$result->fields["fecha"];
             $uuc=$result->fields["ultimo_usuario"];
             $sql=" update cobranzas set ultimo_comentario='$uc',
                                         fecha_ultimo_comentario='$fuc',
                                         usuario_ultimo_comentario='$uuc'
                                         where id_cobranza=$id_cobranza";
             sql($sql)or fin_pagina();
            }
         $cobranzas->movenext();
    }//del for
$db->completetrans();
}//del if del script



function listado_licitaciones() {
	global $bgcolor3,$cmd,$cmd1,$proxima,$datos_barra;
	global $bgcolor2,$itemspp,$db,$parametros,$barra,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera;
	echo $html_header;
	generar_barra_nav($datos_barra);
	$orden = array(
		"default" => "3",
		"default_up" => "0",
		"1" => "licitacion.id_licitacion",
		"2" => "licitacion.id_estado",
		"3" => "licitacion.fecha_apertura",
		"4" => "entidad.nombre",
		"5" => "distrito.nombre",
		"6" => "licitacion.nro_lic_codificado"
	);

	$filtro = array(
		"distrito.nombre" => "Distrito",
		"entidad.nombre" => "Entidad",
		"licitacion.observaciones" => "Comentarios",
		"licitacion.mant_oferta_especial" => "Mantenimiento de oferta",
		"licitacion.forma_de_pago" => "Forma de pago",
		"licitacion.id_moneda" => "Moneda",
		"licitacion.id_licitacion" => "ID de licitación",
		"licitacion.nro_lic_codificado" => "Número de licitación"
	);
	$itemspp = 50;
	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
//	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<table cellspacing=2 cellpadding=5 border=0 bgcolor=$bgcolor3 width=100% align=center>\n";
	echo "<tr><td align=center>\n";

	$sql_tmp = "SELECT licitacion.*, entidad.nombre as nombre_entidad, distrito.nombre as nombre_distrito ";
	$sql_tmp .= "FROM (licitacion LEFT JOIN entidad ";
	$sql_tmp .= "USING (id_entidad)) ";
	$sql_tmp .= "LEFT JOIN distrito ";
	$sql_tmp .= "USING (id_distrito) ";
	$where_tmp = " (licitacion.id_estado=1 ";
	$where_tmp .= "OR licitacion.id_estado=7) ";
	$where_tmp .= " AND borrada='f' ";
	$contar="select count(*) from licitacion where (id_estado=1 or id_estado=7) and borrada='f'";
	if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
	 $contar="buscar";
	list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);

	$sql_est = "select id_estado,nombre,color from estado";
	$result = $db->Execute($sql_est) or die($db->ErrorMsg());
	$estados = array();
	while (!$result->EOF) {
		$estados[$result->fields["id_estado"]] = array(
				"color" => $result->fields["color"],
				"texto" => $result->fields["nombre"]
			);
		$result->MoveNext();
	}
/*	foreach ($estados as $est => $arr) {
		echo "<option value=$est";
		if ("$est" == "$estado") { echo " selected"; }
		echo ">".$estados[$est]["texto"];
	}
	echo "</select>";*/
	echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";
	echo "</td></tr></table><br>\n";
	echo "</form>\n";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	echo "<table border=0 width=95% cellspacing=2 cellpadding=3 bgcolor=$bgcolor3 align=center>";
	echo "<tr><td colspan=5 align=left id=ma>\n";
	echo "<table width=100%><tr id=ma>\n";
	echo "<td width=30% align=left><b>
	Total:</b> $total_lic licitacion/es.</td>\n";
	echo "<td width=70% align=right>$link_pagina</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
	echo "<tr>";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>ID</a>";
	echo "&nbsp;/&nbsp;<a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Est.</a></td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Apertura</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Entidad</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Distrito</td>\n";
	echo "<td align=right id=mo><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))."'>Número</td>\n";
	echo "</tr>\n";
	while (!$result->EOF) {
		//guardamos en esta variable, las observaciones de la licitacion
		//para mostrarlos en title del nobre de la licitacion
		$title_obs=$result->fields["observaciones"];
		$ma = substr($result->fields["fecha_apertura"],5,2);
		$da = substr($result->fields["fecha_apertura"],8,2);
		$ya = substr($result->fields["fecha_apertura"],0,4);
//		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle","sort"=>$sort,"up"=>$parametros["up"],"page"=>$page,"keyword"=>$keyword,"estado"=>$estado,"filter"=>$filter,"ID"=>$result->fields["id_licitacion"]));
		$ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle","id_lic"=>$result->fields["id_licitacion"]));
		tr_tag($ref);
		echo "<td align=center bgcolor='".$estados[$result->fields["id_estado"]]["color"]."' title='".$estados[$result->fields["id_estado"]]["texto"]."'><b><a style='color=".contraste($estados[$result->fields["id_estado"]]["color"],"#000000","#ffffff").";' href='$ref'>".$result->fields["id_licitacion"]."</a></b></td>\n";
		echo "<td align=center>$da/$ma/$ya</td>\n";
		echo "<td align=left title='".$title_obs."'>&nbsp;".html_out($result->fields["nombre_entidad"])."</td>\n";
		echo "<td align=left>&nbsp;".html_out($result->fields["nombre_distrito"])."</td>\n";
    	echo "<td align=left valign=middle>".html_out($result->fields["nro_lic_codificado"]);
        echo "</td>\n";
		$result->MoveNext();
	}
	echo "<tr><td colspan=6 align=center><br>\n";
	echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	echo "<tr><td colspan=10 bordercolor='#FFFFFF'><b>Colores de referencia:</b></td></tr>\n";
	echo "<tr>\n";
    $cont=0;
	foreach ($estados as $est => $arr) {
	if (!($cont % 3)) { echo "</tr><tr>"; }
		echo "<td width=33% bordercolor='#FFFFFF'><table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 wdith=100%><tr>";
		echo "<td width=15 bgcolor='".$estados[$est]["color"]."' bordercolor='#000000' height=15>&nbsp;</td>\n";
		echo "<td bordercolor='#FFFFFF'>".$estados[$est]["texto"]."</td>\n";
		echo "</tr></table></td>";
	   $cont++;
	}
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td></tr>\n";
    echo "</table><br>\n";
}

/*
Funcion que me determina si los renglones a los cuales
esta asocioda la factura de la cobranza estan entregados
el parametro que se le pasa es el id_primario, por si la factura esta atada
a otra
si flag=1 es que esta atada si flag=0 es que no esta atada
devuelve tru o false
*/

function renglones_entregado($id,$flag){

switch ($flag){
    case 1:
            $sql="select count(roc.estado) as cantidad_entregados,count(if.id_item)as cantidad_renglones
                         from licitaciones.atadas a
                         join licitaciones.cobranzas c on(c.id_cobranza=a.id_primario or c.id_cobranza=a.id_secundario)
                         join facturacion.facturas f on(c.id_factura=f.id_factura)
                         join facturacion.items_factura if on(f.id_factura=if.id_factura)
                         join licitaciones.renglones_oc roc using(id_renglones_oc)
                         where id_primario=$id";
          break;
    case 0:
          $sql="select count(roc.estado) as cantidad_entregados,count(if.id_item)as cantidad_renglones
                        from cobranzas
                        join facturas f using(id_factura)
                        join items_factura if using(id_factura)
                        join renglones_oc roc using(id_renglones_oc)
                        where id_cobranza=$id ";
          break;
}//del switch
$res=sql($sql) or fin_pagina();


$arreglo["cantidad_entregados"]=$res->fields["cantidad_entregados"];
$arreglo["cantidad_renglones"]=$res->fields["cantidad_renglones"];
return $arreglo;
}// de la function renglones_entregado


function listado_facturas() {
	global $bgcolor3,$cmd,$cmd1,$total_registros,$datos_barra;
	global $bgcolor2,$itemspp,$db,$parametros,$barra,$html_header,$html_root;
	global $keyword,$filter,$page,$sort,$estado,$ver_papelera;
	global $permisos;
	global $_ses_user;
	global $download,$ordenar,$valor_dolar;
    global $atrib_tr;
    $color_nombre = array(
		"1" => "#FFC0C0",           // Llamar en un rato
		"2" => "#FFFFC0",           // Llamar mañana - Terminado por hoy
		"3" => "#00D500"            // Llamar dentro de unos días
	);
	$texto_nombre = array(
		"1" => "Llamar en un rato",
		"2" => "Llamar mañana - Terminado por hoy",
		"3" => "Llamar dentro de unos días"
	);
	$orden = array(
		"default" => "",
		"default_up" => "1",
		"1" => "nro_carpeta",
		"2" => "id_licitacion",
		"3" => "nombre_entidad",
		"4" => "nro_factura",
		"5" => "monto",
		"6" => "fecha_presentacion",
		"7" => "fecha_estimativa",
		"8" => "fecha_legal",
		"9" => "nombre_cobranzas",
		"10" => "ultima_modif" ,
        "11" =>"fecha",
        "12" =>"dias_atraso",
        "13" =>"nombre_distrito",
        "14" =>"nombre_estado",
	);

	$filtro = array(
		"nro_carpeta" => "Carpeta",
		"id_licitacion" => "ID Licitación",
		"nombre_entidad" => "Entidad",
		"nro_factura" => "Número de factura",
		"principal.monto" => "Monto",
        "nombre_cobranzas"=> "Nombre",
        "nombre_distrito"=>"Distrito",
        "nombre_estado"=>"Estado Factura",
	);
    if ($cmd=="finalizada") $itemspp=50;
	else $itemspp=100000;
//envio la pagina como archivo excel
if($download)
{
//*
	if (isset($_SERVER["HTTPS"])) {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
	}
	header("Pragma: ");
	header("Cache-Control: ");
	header("Content-Type: application/xls");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=\"Seg_Cobros(".date("j-m-Y").").xls\"");
/* 	*/
}
else
{
	echo $html_header;
	generar_barra_nav($datos_barra);
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post' name='form1'>";
}

//       echo "<input type=submit name=activos value=activos>";
// Calcular los totales por moneda
$id_cobranza_balance=array();
    if ($cmd=="pendiente"){
        $datos_aux=sql_cuentas_a_cobrar(0,0);
        $sql=$datos_aux["sql"];
        $datos=sql($sql) or fin_pagina();
        for($i=0;$i<$datos->recordcount();$i++){
            $id_cobranza_balance[]=$datos->fields["id_cobranza"];
            $datos->movenext();
         }
      } //del if de cmd==pendiente
    if ($cmd=="pendiente")
      {
	  $sql = "SELECT simbolo, sum(monto_original) as total FROM
	            licitaciones.cobranzas LEFT JOIN licitaciones.moneda
	            USING (id_moneda) left join facturas using (id_factura)
	            WHERE  cobranzas.estado='".strtoupper($cmd)."' and ((facturas.estado<>'A' and facturas.estado<>'a')or facturas.estado isnull)
	            GROUP BY simbolo";
      }
      else
          {
  	      $sql = "SELECT simbolo,sum(monto_original) as total FROM
	            licitaciones.cobranzas LEFT JOIN licitaciones.moneda
	            USING (id_moneda) left join facturas using (id_factura)
	            WHERE cobranzas.estado='".strtoupper($cmd)."' and ((facturas.estado<>'A' and facturas.estado<>'a')or facturas.estado isnull)
	            GROUP BY simbolo";
        }
    $result = sql($sql) or fin_pagina();
	$total_moneda = array();
	while (!$result->EOF) {
		$total_moneda[$result->fields["simbolo"]] = formato_money($result->fields["total"]);
		$result->MoveNext();
	}
    	$fecha_hoy = date("Y-m-d 23:59:59",mktime());
   // Tabla con totales por moneda
   if (count($total_moneda) > 0) {
		if (!$download)
		{
			echo "<table cellspacing=0 cellpadding=0 border=1 bgcolor=$bgcolor3 width=95% align=center><tr>";
			echo "<td width=20%>";
			echo "<table width=100% border=0 bordercolor=$bgcolor3 bgcolor=#ffffff>";
			echo "<tr id=mo><td style=\"border:$bgcolor3;\"><b>Total por moneda</b></td></tr>";
			foreach ($total_moneda as $total_simbolo => $total_monto) {
				echo "<tr><td align=right><b>$total_simbolo&nbsp;$total_monto</b></td></tr>";
			}
			echo "</table>\n";
			echo "</td>";
		}
		else
		{
			echo "<table>";
			echo "<tr><td colspan=2>";
				echo "<table width=100% border=1 bordercolor=$bgcolor3 bgcolor=#ffffff>";
					echo "<tr><td colspan=2>&nbsp;</td></tr>";
						echo "<tr><td colspan=2>&nbsp;</td></tr>";
						echo "<tr id=mo><td colspan=2 align=right style=\"border:$bgcolor3;\"><b>Total por moneda</b></td></tr>";
						foreach ($total_moneda as $total_simbolo => $total_monto) {
							echo "<tr><td colspan=2 align=right><b>$total_simbolo&nbsp;$total_monto</b></td></tr>";
						}
						echo "</table>\n";
			echo "</td>";
			echo "<td>";
						echo "<table><tr><td colspan=3 align=center><b>SEGUIMIENTO DE COBROS</b></td></tr>";
						echo "<tr><td colspan=3>&nbsp</td></tr>";
 					    echo "<tr><td colspan=3 align=center><b>Estado cobranzas:</b> ".(($cmd=="pendiente")?"Pendientes":"Finalizadas")."</td></tr>";
						if ($keyword) echo "<tr><td colspan=3 align=center><b>Palabra Buscada:</b> '$keyword' en <b>".(($filter=='all')?"Todos los campos":$filtro[$filter])."</b> </td></tr>";
                        //echo "<tr><td colspan=3><b>Valor Dolar Usado: &nbsp \$ $valor_dolar</b></td></tr>";
						echo "</table>";
			echo "<td></tr>";
            echo "<tr><td colspan=2 align=right><b>Valor Dolar Usado: &nbsp \$ $valor_dolar</b></td></tr>";
			echo"</table>";
		}
	}
    //CONSULTA NUEVA
        $Fecha_Desde_db=fecha_db($_POST["desde"]) or $Fecha_Desde_db=fecha_db($parametros["desde"]);
        $Fecha_Hasta_db=fecha_db($_POST["hasta"]) or $Fecha_Hasta_db=fecha_db($parametros["hasta"]);
        $where_fecha="";
        if ($_POST["fechas"]==1 || $parametros["fechas"]==1)
                 {
                 //$where_tmp.="   (fecha_presentacion Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db')";
                 $where_fecha=" AND  (fecha_estimativa Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db')";
                 }
        if ($_POST["fechas"]==2)
                 {
                 $where_fecha=" AND (fecha_presentacion Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db')";
                 }
       if ($_POST["fechas"]==3)
                 {
                 $where_fecha="  AND (fin_fecha Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db')";
                 }

if ($cmd=='pendiente'){
       $estado_reporte="Pendiente";
       $resta_fechas=" (
                        cast(current_timestamp as date)-
                        cast(
                           case when  to_char(fecha_entrega,'YYYY-MM-DD') isnull
                                      then to_char(cobranzas.fecha_factura,'YYYY-MM-DD')
                                      else to_char(cast(fecha_entrega as timestamp),'YYYY-MM-DD')
                                      end
                            as date)
                        )";

       $estado_facturas="";
       }
       else {
       $estado_reporte="Solucionado";
       $resta_fechas=" (cast(fin_fecha as date)-cast(cobranzas.fecha_factura as date))";
       $estado_facturas=" (facturas.estado<>'a'   or facturas.estado is  null ) and";
       }


if ($cmd=="pendiente")
   $columna_monto="cobranzas.monto_original";
   else
   $columna_monto="cobranzas.monto_original";
$sql_tmp="
  select * from (
     SELECT
      case when q.total is not null
                              then case when c.simbolo='U\$S' then (c.monto_aux + q.total)*$valor_dolar
                                                        else (c.monto_aux + q.total) end
                              else case when c.simbolo='U\$S' then c.monto_aux*$valor_dolar
                                                        else c.monto_aux end
                          end as monto,
      case when q.total is not null
                              then (c.monto_aux + q.total)
                              else c.monto_aux
                          end as monto_orig,
      case when numero_compuesto is not null then (c.nro_factura_aux  || text (', ') || numero_compuesto)
                              else c.nro_factura_aux end as nro_factura,
      c.*,ll.obs_llamadas,ll.fecha,reportes.cantidad,minimo.dias_atraso,c.id_estado,c.estado,
      case when to_char(c.fecha_presentacion,'YYYY-MM-DD') is not null
                   then
                   cast(current_timestamp as date) -
                   cast(c.fecha_presentacion as date)
                   else null
                   end as dias_atraso_legal
      FROM
             (
                 SELECT cobranzas.id_cobranza,cobranzas.activo,nro_carpeta,cobranzas.nro_factura as nro_factura_aux,
                 $columna_monto as monto_aux,cobranzas.fecha_factura,facturas.estado,
                 simbolo,fecha_legal,fecha_estimativa,cobranzas.nombre as nombre_cobranzas,
                 ultimo_comentario as comentarios,fecha_ultimo_comentario as ultima_modif,
                 fecha_presentacion,cobranzas.id_licitacion,aceptacion_definitiva,
                 case when entidad.nombre is not null
                   then entidad.nombre else facturas.cliente end as nombre_entidad ,
                 estado_nombre,fecha_estado,id_primario,distrito.nombre as nombre_distrito
                 ,fv.nombre_estado,fv.nombre_factoring,licitacion.id_estado
                 FROM licitaciones.cobranzas
                 LEFT JOIN licitaciones.moneda USING (id_moneda)
                 LEFT JOIN licitaciones.entidad USING (id_entidad)
                 LEFT JOIN facturacion.facturas ON (facturas.id_factura=cobranzas.id_factura)
                 LEFT JOIN licitaciones.atadas ON (atadas.id_primario=cobranzas.id_cobranza)
                 LEFT JOIN licitaciones.atadas_comentarios ON cobranzas.id_cobranza=atadas_comentarios.id_cobranza
                 LEFT JOIN licitaciones.distrito using(id_distrito)
                 LEFT JOIN licitaciones.licitacion ON (licitacion.id_licitacion=cobranzas.id_licitacion)
 		         LEFT JOIN
	               (
                    select id_cobranza,id_estado_cobranza,estado_cobranzas.nombre as nombre_estado,
                            factoring.nombre as nombre_factoring
                            from  licitaciones.historial_estados_cobranzas
                            join licitaciones.estado_cobranzas using (id_estado_cobranza)
                            left join licitaciones.factoring using(id_factoring)
                            where historial_estados_cobranzas.activo=1
                  ) as fv on (cobranzas.id_cobranza=fv.id_cobranza)

                 where $estado_facturas cobranzas.estado ='".strtoupper($cmd)."' $where_fecha
                 GROUP BY cobranzas.id_cobranza,nro_carpeta,cobranzas.nro_factura,facturas.estado,$columna_monto,
                          id_primario,cobranzas.fecha_factura,simbolo,fecha_legal,fecha_estimativa,
                          fecha_presentacion,entidad.nombre,cobranzas.id_licitacion,
                          cobranzas.nombre,estado_nombre,fecha_estado,
                          facturas.cliente,aceptacion_definitiva,
                          cobranzas.activo,ultimo_comentario,fecha_ultimo_comentario,distrito.nombre
                          ,fv.nombre_estado,fv.nombre_factoring,licitacion.id_estado
                 ) as c
                 LEFT JOIN
                 (
                 select fecha,id_cobranza,observaciones as obs_llamadas from
                 licitaciones.cobranzas_llamadas
                 where  id_llamada in
                           (select max(id_llamada) as id_llamada
                            from licitaciones.cobranzas_llamadas
                            group by id_cobranza
                            )
                  ) as ll using (id_cobranza)
                 LEFT JOIN
                  (
                  select licitaciones.unir_texto(cobranzas.nro_factura || text(' ')) as numero_compuesto,
                   sum(cobranzas.monto) as total,id_primario
                    FROM licitaciones.cobranzas
                    LEFT JOIN facturacion.facturas ON (facturas.id_factura=cobranzas.id_factura)
                    LEFT JOIN licitaciones.atadas ON (atadas.id_secundario=cobranzas.id_cobranza)
                    /*
           		    LEFT JOIN
	                    (
                            select id_cobranza,estado_cobranzas.nombre as nombre_estado,factoring.nombre as nombre_factoring
                                   from  licitaciones.historial_estados_cobranzas
                                   join licitaciones.estado_cobranzas using (id_estado_cobranza)
                                   left join licitaciones.factoring using(id_factoring)
                                   where historial_estados_cobranzas.activo=1
                            ) as fv on (cobranzas.id_cobranza=fv.id_cobranza)
                     */
                    GROUP BY  id_primario
                  ) as q using (id_primario)
                 LEFT JOIN
                 (
                 select count(id_licitacion) as cantidad,id_licitacion
                                      from reporte_tecnico.reporte_tecnico
                                      where fechafinal is NULL and estado='$estado_reporte'
                                      group by id_licitacion
                  ) as reportes
                using(id_licitacion)
                --parte de dias atrasados
                LEFT JOIN
                  (
                   select min(dia_atraso.dias_atraso) as dias_atraso,dia_atraso.id_cobranza from
                      (
                        select  $resta_fechas as dias_atraso ,id_cobranza
                            from licitaciones.cobranzas
      ";
      if ($cmd!="finalizada") {
                       $sql_tmp.="
                            left join facturacion.facturas using (id_factura)
                            join facturacion.items_factura using (id_factura)
                            left join licitaciones.renglones_oc using (id_renglones_oc)
                            left join (select id_renglones_oc,fecha_entrega from licitaciones.log_renglones_oc where tipo='entrega') as log
                            using(id_renglones_oc)
                       ";
      }
      $sql_tmp.="  where cobranzas.estado ='".strtoupper($cmd)."'
                      ) as dia_atraso
                     group by id_cobranza
                   ) as minimo
               using (id_cobranza)
              --fin de partes nuevas de dias atrasados
     )   as principal
    ";

if ($download)
	ob_start();
echo "<td align=center>\n";
if ($cmd=="pendiente")
      $contar="SELECT count(*) as cant FROM cobranzas
	           WHERE estado='PENDIENTE'";
      elseif($cmd=="finalizada")
      $contar="SELECT count(*) as cant FROM cobranzas
	           WHERE estado='FINALIZADA'";


if ($filter || $keyword)
         $contar="buscar";

//print_r($sumas);
list($sql,$total_reg,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar);

if ($download)
	ob_clean();
else
{
echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>\n";


if (permisos_check("inicio","excel_seg_cobros")){
    echo "&nbsp;<b>Valor Dolar</b>&nbsp;";
    echo  "<input type=hidden name=excel value='0'>";
    echo  "<input type=text name=valor_dolar value='$valor_dolar' size=3 onkeypress=\"return filtrar_teclas(event,'0123456789.');\">";
    $link=encode_link($_SERVER['SCRIPT_NAME'],array("download"=>1,
                                                     "valor_dolar"=>$valor_dolar,
                                                     "fechas"=>$_POST["fechas"],
                                                     "desde"=>$_POST["desde"],
                                                     "hasta"=>$_POST["hasta"]));
    echo"&nbsp;<img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' onclick='window.open(\"$link\");'>";
	echo "</td></tr>";
	echo "<tr>";
	echo "<td>";
	echo "&nbsp;";
	echo "</td>";
	echo "<td align=center width=100%>";
   }
if(permisos_check("inicio","filtrado_fechas"))
	  {
	  $Fecha_Desde=$_POST["desde"];
	  $Fecha_Hasta=$_POST["hasta"];
       cargar_calendario();
	   echo "<table width=100% alig=Center border=0>";
	   echo "<tr>";
	   echo "<td align=center>";
	   if ($_POST["fechas"]==1) $selected_1="selected";
	   if ($_POST["fechas"]==2) $selected_2="selected";
	   if ($_POST["fechas"]==3) $selected_3="selected";
	   echo "<select name=fechas>";
			 echo "<option> </option>";
			 echo "<option value='1' $selected_1>Fecha Estimativa </option>";
			 echo "<option value='2' $selected_2>Fecha Presentación </option>";
			 if ($cmd=="finalizada"){
			 echo "<option value='3' $selected_3>Fecha de Finalización </option>";
			 }
		   echo "</select>";
		  echo "</td>";
	   echo "<td align=center>";
	   echo "<b>Desde</b> ";
	   echo "</td>";
	   echo "<td align=center>";
	   echo "<input type=text name=desde value='$Fecha_Desde' size=10>";
	   echo "&nbsp;";
	   echo link_calendario("desde");
	   echo "</td>";
	   echo "<td align=center>";
	   echo "<b>Hasta</b>";
	   echo "</td>";
	   echo "<td align=center>";
	   echo "<input type=text name=hasta value='$Fecha_Hasta' size=10>";
	   echo "&nbsp;";
	   echo link_calendario("hasta");
	   echo "</td>";
	   echo "</tr>";
	   echo "</table>";
	  } //fin del control de acl
	  else
	  {
          echo "&nbsp;";
	  }
	echo "</td>";
	echo "</tr>";
	echo "</table><br>\n";
    echo "&nbsp;&nbsp;<input type=button  name='listado' value='Listado de Clientes' onclick=\"window.open('lic_cobranzas_listado.php');\" title='Listado de Clientes'>";
    if ($_ses_user["login"]=="fernando" || $_ses_user["login"]=="juanmanuel"){
       echo "<input type='submit' name='script_uu' value='Setear Comentarios'>&nbsp;";
       //echo "<input type='submit' name='script_estados' value='Script Estados'>";
    }
	echo "</form>\n";
	echo "<table class='bordes' width=95% cellspacing=2 cellpadding=3 align=center>";
	echo "<tr><td colspan=13 align=right id=ma>";
	echo "<table width=100%><tr id=ma>\n";
	echo "<td width=30% align=left><b>Total:</b> ".$total_reg." factura/s.</td>\n";
	echo "<td width=70% align=right>$link_pagina</td>\n";
	echo "</tr></table>\n";
	echo "</td></tr>";
}
	$result = sql($sql) or fin_pagina();
if ($download)
{
    //filas pares
	$style[0]='bgcolor=white';
	//filas impares
	$style[1]='bgcolor=#99CCFF';
	echo "<table><tr><td>&nbsp;</td></tr></table>";
	echo "<table border=1>";
	echo "<tr align=center bgcolor=#000000 style='color:#E9E9E9;font-weight: bold;'>";
	//echo "<td>Carpeta</td>";
    //echo "<td>F. LLamada</td>";
	//echo "<td width='10%'>Nombre</td>";
	echo "<td>ID&nbsp;Lic.</td>";
	echo "<td width=20%>Entidad</td>";
	echo "<td width=20%>Factura</td>";
	echo "<td width=10%>Estado</td>";
	echo "<td>Monto</td>";
	echo "<td title='Fecha de Presentación'>F.P.</td>";
	echo "<td title='Fecha Estimativa'>F.E.</td>";
 	//echo "<td title='Ultima Modificación'>U.M.</td>";
    echo "<td>Días Atraso</td>";
	echo "</tr>";
}
else
{
	echo "<tr id=mo>";
    echo "<td><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>Carpeta</a></td>";
    echo "<td title='Fecha Próxima Llamada'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"11","up"=>$up))."'>F.LLamada</a></td>";
	echo "<td width=15%><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"9","up"=>$up))."'>Nombre</a></td>";
	echo "<td><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>ID&nbsp;Lic.</a></td>";
	echo "<td width=20%><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))."'>Entidad</a></td>";
    echo "<td width=20%><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"13","up"=>$up))."'>Distrito</a></td>";
	echo "<td width='25%'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))."'>Factura</a></td>";
    echo "<td width='20%'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"14","up"=>$up))."'>Estado</a></td>";
	echo "<td><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))."'>Monto</a></td>";
	echo "<td title='Fecha de Presentación'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))."'>F.P.</a></td>";
	echo "<td title='Fecha Estimativa'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))."'>F.E.</a></td>";
//	echo "<td title='Fecha Legal'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"9","up"=>$up))."'>F.L.</a></td>";
	echo "<td title='Ultima Modificación'><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"10","up"=>$up))."'>U.M.</a></td>";
    echo "<td><a id=mo href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"12","up"=>$up))."'>Días Atraso</a></td>";
	echo "</tr>";
}
    // Mostrar los datos
	$cuenta=0;
	//foreach($datos_facturas as $datos){
   // $result->move(0);

    for ($i=0;$i<$result->recordcount();$i++){
        if ($result->fields["activo"]==1) {
                      $dias_atraso=$result->fields["dias_atraso"];
		              ++$cuenta;
		              $bg_celda = "";
                      $bg_nombre = "";
                      $tip_nombre = "";
		              $ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle_cobranza","id"=>($result->fields["id_primario"])?$result->fields["id_primario"]:$result->fields["id_cobranza"]));
      		          $comentarios_ok = ereg_replace("'","",$result->fields["comentarios"]);
      		          $comentarios_ok = ereg_replace("\"","",$result->fields["comentarios"]);
                      if ($cmd=="pendiente"){
                        if ($result->fields["id_primario"]){
                                  $entregado=renglones_entregado($result->fields["id_primario"],1);
                                   if ($entregado["cantidad_entregados"]<$entregado["cantidad_renglones"])
                                          $bgcolor_entregado="bgcolor='#F8FBE1'";
                                          else
                                          $bgcolor_entregado="";
                                  }
                                  else{
                                      $entregado=renglones_entregado($result->fields["id_cobranza"],0);
                                       if ($entregado["cantidad_entregados"]<$entregado["cantidad_renglones"])
                                            $bgcolor_entregado="bgcolor='#F8FBE1'";
                                             else
                                            $bgcolor_entregado="";
                                            }
                       }//del if de cmd=pendiente
                      $bgcolor_reporte="";
		              if ($result->fields["id_licitacion"]) {
                                  if ($result->fields["cantidad"]){
	                                      $bgcolor_reporte="bgcolor='red'";
                                         }
			                              else
			                              $bgcolor_reporte="";
		                          }//del if
          		        if ($download)
 			                 echo "<tr ".$style[$i%2].">";
		                     else
                              {
                              $title="title=\"".cortar2($comentarios_ok,500,"...\n",1)."\"";
                              $onclick="onClick=\"location.href ='$ref'\"";
                               if ($bgcolor_reporte && $bgcolor_entregado)
                                                $color_fila=$bgcolor_reporte;
                                                elseif ($bgcolor_reporte)
                                                        $color_fila=$bgcolor_reporte;
                                                        elseif($bgcolor_entregado)
                                                          $color_fila=$bgcolor_entregado;
                                                          else
                                                          $color_fila="";
                               if ($color_fila!=""){
                                                $bgcolor_fila="$color_fila style=\"cursor:'hand'\" $title";
                                                 }
                                                 else{
                                                 $bgcolor_fila=atrib_tr();
                                                 }
                              echo "<tr ".$bgcolor_fila.">\n";
                              }
  		                     if ($cmd == "pendiente" and $result->fields["fecha_legal"] != "" and (strtotime(date("Y-m-d 23:59:59")) >= strtotime($result->fields["fecha_legal"])))
                                {
			                     $bg_celda = " bgcolor='#dd0000'";
                                }
                          if ($cmd == "pendiente" and $result->fields["estado_nombre"])
                                 {
                                 if (compara_fechas(date("Y-m-d"),$result->fields["fecha_estado"]) === 1 and $result->fields["estado_nombre"] != "3")
                                     {
                                     $sql_update = "UPDATE cobranzas SET estado_nombre=NULL,fecha_estado=NULL ";
                                     $sql_update .= " WHERE id_cobranza=".$result->fields["id_cobranza"];
                                     sql($sql_update) or fin_pagina();
                                     }
                                     else
                                         {
                                         $bg_nombre = "bgcolor='".$color_nombre[$result->fields["estado_nombre"]]."'";
                                         $tip_nombre = "title='".$texto_nombre[$result->fields["estado_nombre"]]."'";
                                         }
                                 }//del if
                         if (Fecha($result->fields["ultima_modif"])) {
        	                       if ($download)
 	                                      $ultima_modif=date_spa("d/m/Y H:i:s" ,$result->fields["ultima_modif"]);
        	                              else
        	                              $ultima_modif = Fecha($result->fields["ultima_modif"])."<br>".Hora($result->fields["ultima_modif"]);
                          }
                          else {
                               $ultima_modif = "&nbsp;";
                               }
     	                  if ($result->fields["aceptacion_definitiva"] == "t") {
                                           $bg_factura = ($download)?"bgcolor=aqua":"bgcolor='#8EFFFF'";
		 	                               $tip_factura = "title='Aceptación definitiva lista'";
	                                       }
	                                       else {
                                                 $bg_factura = "";
		                                         $tip_factura = "";
	                                             }
                         $estado_cobranza=$result->fields["nombre_estado"];
                         $nro_factura=$result->fields["nro_factura"];
                         if (
                             ($_ses_user["login"]=='fernando'                     
						      || $_ses_user["login"]=='juanmanuel'
							  || $_ses_user["login"]=='corapi'					  
                              || $_ses_user["login"]=='noelia')
                              and in_array($result->fields["id_cobranza"],$id_cobranza_balance)
                            )

                            $bgcolor_balance="bgcolor= #8888FF";
                            else
                            $bgcolor_balance="";

	                      if ($download)
		                               {
		                                //echo "<td align=center valign=top>".$result->fields["nro_carpeta"]."</td>";
                                        //echo "<td align='center' ".excel_style("timestamp_corto")." valign=top title='".$result->fields["obs_llamadas"]."'>".$result->fields["fecha"]."</td>";
		                                //echo "<td align=left >".cortar2($result->fields["nombre_cobranzas"],128,"<br>")."</td>";
                                	  echo "<td align=center $bgcolor_balance valign=top>".$result->fields["id_licitacion"]."</td>";
		                              echo "<td valign=top align=left ".excel_style("texto").">".cortar2($result->fields["nombre_entidad"],128,"<br>")."</td>";
                                      echo "<td valign=top align=center $bg_factura ".excel_style("texto").">".$nro_factura."</td>";
                                      $monto=$result->fields["monto"];
                                      $monto_original=$result->fields["monto_orig"];
                                      if ($valor_dolar){
                                               switch ($result->fields["simbolo"]){
                                                       case "dolar":
                                                       case "U\$S":
                                                               $simbolo="\$";
                                                               break;
                                                       default:
                                                       $simbolo="\$";
                                                       }//del switch
                                                }//del if
                                         else {
                                              $simbolo=$result->fields["simbolo"];
                                              }
		                                  echo "<td valign=top align=right ".excel_style("texto").">".$estado_cobranza."</td>";
                                          //si esta anulada no muestra el monto en el excel
                                          if ($result->fields["estado"]!='A' and $result->fields["estado"]!='a')
                                             echo "<td valign=top align=right ".excel_style($simbolo).">".formato_money($monto)."</td>";
                                             else
										     echo "<td valign=top align=right>&nbsp;</td>";
		                                  echo "<td valign=top align=center ".excel_style("fecha_corta"). "><b><font color='990000'>".Fecha($result->fields["fecha_presentacion"])."</font></b></td>";
		                                  echo "<td valign=top align=center ".excel_style("fecha_corta"). "><b><font color='005500'>".Fecha($result->fields["fecha_estimativa"])."</font></b></td>";
		                                  echo "<td valign=top align=center ".excel_style("texto")." >$dias_atraso </td>";
		                                  echo "</tr>";
		                                 } //del if($download)
		                                 else
		                                      {
		                                       if($result->fields["estado"]=='A' || $result->fields["estado"]=='a')
			                                            {
                                                        $color_null="color='red'";
			                                            $title_null="title='Esta factura esta anulada'";
	                                                    }
		                                                else
		                                                   {
                                                           $color_null="";
		                                                   $title_null="";
		                                                   }
		                                        echo "<td  valign=top align=center $bg_celda $title $onclick><b>".$result->fields["nro_carpeta"]."</b>&nbsp;</td>";
                                                echo "<td  valign=top align=center>";
                                                $id_aux=$result->fields["id_cobranza"];
                                                $link=encode_link("lic_llamadas.php",array("id_cobranza"=>$id_aux));
                                                ?>
                                                <script>
                                                   function cargar_llamadas_esp_<?=$id_aux?>()
                                                   {
                                                   wventana=window.open('<?=$link?>','','left=5,top=5,width=950,height=700,resizable=1');
                                                   }
                                                </script>
                                                <?
                                                if ($result->fields["fecha"]){
                                                     $title_celda="title=\"".$result->fields["obs_llamadas"]."\"";
                                                     echo "<b>";
                                                     echo "<table width=100% align=center>";
                                                     echo "<tr>";
                                                     echo "<td width=50% align=Center $title_celda>";
                                                     echo  fecha($result->fields["fecha"]);
                                                     echo "<br>";
                                                     $hora=substr($result->fields["fecha"],11);
                                                     echo $hora;
                                                     echo "</td>";
                                                     echo "<td width=50%>";
                                                     $id_aux=$result->fields["id_cobranza"];
                                                     echo "<input type=button name=lic_llamdas value=H onclick='cargar_llamadas_esp_$id_aux()'";
                                                     echo "</td>";
                                                     echo "</tr>";
                                                     echo "</table>";
                                                     echo "</b>";
                                                     }
                                                     else
                                                         echo "&nbsp";
                                             echo "</td>";
	                                         echo "<td align=left $bg_nombre $tip_nombre $title $onclick>".cortar2($result->fields["nombre_cobranzas"],128,"<br>")."&nbsp;</td>";
		                                     echo "<td valign=top align=center $bgcolor_balance $title $onclick>".$result->fields["id_licitacion"]."&nbsp;</td>";
		                                     echo "<td align=left $title $onclick>".cortar2($result->fields["nombre_entidad"],128,"<br>")."&nbsp;</td>";
                                             echo "<td valign=top align=left $title $onclick>".cortar2($result->fields["nombre_distrito"],35)."&nbsp;</td>";
                                             echo "<td valign=top align=left width=25% $title  $onclick $bg_factura $tip_factura>".$nro_factura."</td>";
                                            if ($result->fields["id_primario"]){
                                                 //verifico si todos los estados de las facturas son iguales
                                                 $text=verificar_estados_atadas($result->fields["id_primario"]);
                                            }
                                            else {
                                                  $text="";
                                            }
                                            $title_estado=" title='$text' ";
                                            echo "<td valign=top align=center $title_estado  $onclick >".$estado_cobranza."</td>";
                                            $monto=$result->fields["monto"];
                                            $monto_original=$result->fields["monto_orig"];
                                             /*parte nueva*/
                                             if ($valor_dolar){
                                              switch ($result->fields["simbolo"]){
                                                          case "dolar":
                                                          case "U\$S":
                                                                $simbolo="\$";
                                                                $title_dolar=" title=' U\$S $monto_original'";
                                                                 $imagen="<img src='$html_root/imagenes/dolar.gif' border=0 >";
                                                                 break;
                                                           default:
                                                                 $simbolo="\$";
                                                                 $title_dolar="";
                                                                 $imagen="";
                                                                 break;
                                                        }//del switch
                                                }//del if
                                                else {
                                                    $title_dolar="";
                                                    $imagen="";
                                                    $simbolo=$result->fields["simbolo"];
                                                  }
                                           //   echo "dia a".$result->fields["dias_atraso_legal"]."<br>";
                                             if ($result->fields["dias_atraso_legal"] && ($cmd == "pendiente"))
                                                      $dias_atraso_legal="(".$result->fields["dias_atraso_legal"].")";
                                                      else $dias_atraso_legal="";
	                                         echo "<td valign=top align=right  $title_null $title_dolar $onclick>$imagen<font size=2 $color_null>".$simbolo."&nbsp;".formato_money($monto)."&nbsp;</font></td>";
	                                         echo "<td valign=top align=center $title $onclick><b><font color='990000'>".Fecha($result->fields["fecha_presentacion"]).$dias_atraso_legal."</font></b>&nbsp;</td>";
		                                     echo "<td valign=top align=center $title $onclick><b><font color='005500'>".Fecha($result->fields["fecha_estimativa"])."</font></b>&nbsp;</td>";
		                                     echo "<td valign=top align=center $title $onclick>$ultima_modif</td>";
                                             echo "<td valign=top align=center $title $onclick>$dias_atraso</td>";
		                                     echo "</tr>";
		                                     }
}
$result->movenext();
}
 //fin de mostrar datos de la parte nueva
        if (!$download) {
        echo "<tr>";
        echo "<td align=center colspan=10>";
	    echo "<table border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='100%' cellspacing=0 cellpadding=0>\n";
	    echo "<tr>\n";
             echo "<td  colspan=3 bordercolor='#FFFFFF'>";
             echo "<b>Colores de referencia Nombre</b>";
             echo "</td>";
             echo "</tr>";
             echo "<tr>";
             echo "<td  width=33% bordercolor='#FFFFFF'>";
             echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
             echo "<tr>";
             echo "<td width=15 bgcolor='#FFC0C0' bordercolor='#000000' height=15>&nbsp;</td>\n";
             echo "<td>LLamar en un rato</td>";
             echo "</tr>";
             echo "</table>";
             echo "</td>";
             echo "<td  width=33% bordercolor='#FFFFFF'>";
             echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
             echo "<tr>";
             echo "<td width=15 bgcolor='#FFFFC0' bordercolor='#000000' height=15>&nbsp;</td>\n";
             echo "<td>Llamar mañana - Terminado por hoy</td>";
             echo "</tr>";
             echo "</table>";
             echo "</td>";
             echo "<td  width=33% bordercolor='#FFFFFF'>";
             echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
             echo "<tr>";
             echo "<td width=15 bgcolor='#00D500' bordercolor='#000000' height=15>&nbsp;</td>\n";
             echo "<td>Llamar dentro de unos días</td>";
             echo "</tr>";
             echo "</table>";
             echo "</td>";
             echo "</tr>";
            //Referencias de Carpeta
 	         echo "<tr>";
             echo "<td  colspan=3 bordercolor='#FFFFFF'>";
             echo "<b>Colores de referencia Carpeta</b><br>";
             echo "</td>";
             echo"</tr>\n";
             echo "<tr>";
             echo "<td colspan=3 bordercolor='#FFFFFF'>";
             echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
             echo "<tr>";
             echo "<td width=15 bgcolor='#dd0000' bordercolor='#000000' height=15>&nbsp;</td>\n";
             echo "<td>Cobro Vencido</td>";
             echo "</tr>";
             echo "</table>";
             echo "</td>";
             echo "</tr>";
         //Colores de referencia de facturas
	     echo "<tr>\n";
         echo "<td colspan=3 bordercolor='#FFFFFF'>";
         echo "<b>Colores de referencia Facturas</b>";
         echo "</td>";
         echo "</tr>";
         echo "<tr>";
         echo "<td  colspan=3 bordercolor='#FFFFFF'>";
         echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
         echo "<tr>";
         echo "<td width=15 bgcolor='Aqua' bordercolor='#000000' height=15>&nbsp;</td>\n";
         echo "<td>Lista para ser Vendida</td>";
         echo "</tr>";
         echo "</table>";
         echo "</td>";
         echo "</tr>";
         //colores de referencia de fila completa
         echo "<tr>\n";
         echo "<td colspan=3 bordercolor='#FFFFFF'>";
         echo "<b>Colores de referencia Fila Completa</b>";
         echo "</td>";
         echo "</tr>";
         echo "<tr>";
         echo "<td  width=33% bordercolor='#FFFFFF'>";
         echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
         echo "<tr>";
         echo "<td width=15 bgcolor='#F8FBE1' bordercolor='#000000' height=15>&nbsp;</td>\n";
         echo "<td >Falta Entregar Mercaderia</td>";
         echo "</tr>";
         echo "</table>";
         echo "</td>";
         echo "<td  width=33% bordercolor='#FFFFFF'>";
         echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
         echo "<tr>";
         echo "<td width=15 bgcolor='red' bordercolor='#000000' height=15>&nbsp;</td>\n";
         echo "<td >Reporte Técnicos</td>";
         echo "</tr>";
         echo "</table>";
         echo "</td>";
         echo "</tr>";
        //Colores de referencia de id licitacion
        echo "<tr>\n";
        echo "<td colspan=3 bordercolor='#FFFFFF'>";
        echo "<b>Colores de referencia Id Licitación</b>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td  colspan=3 bordercolor='#FFFFFF'>";
        echo "<table width=100% align=Center bordercolor='#FFFFFF' cellspacing=0 cellpadding=0>";
        echo "<tr>";
        echo "<td width=15 $bgcolor_balance bordercolor='#000000' height=15>&nbsp;</td>\n";
        echo "<td>Cobranza considerada en Balance</td>";
        echo "</tr>";
        echo "</table>";
        echo "</td>";
        echo "</tr>";
//Colores
        } //del if de los downloads
        echo "</table>";
        echo "</td>";
        echo "</tr>";
    echo "</table><br>";
    //esto se agrega para que el color de las filas no se extienda toda la hoja
    //puede se esto o cualquier texto texto texto
	if ($download) echo "<font color=white>CDR Computers ".date("Y"). "®</font>";
}

function detalle_cobranzas($ID) {
    global $html_header,$bgcolor2,$bgcolor3,$cmd,$datos_barra;
	echo $html_header;
    generar_barra_nav($datos_barra);
	$sql = "SELECT entidad.nombre AS cliente FROM ";
	$sql .= "licitacion LEFT JOIN entidad ";
	$sql .= "USING (id_entidad)";
	$sql .= "WHERE id_licitacion=$ID";
	$result=sql($sql) or fin_pagina();
	$cliente = $result->fields["cliente"];
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=+1>Detalles de cobranzas</font></td></tr>";
	echo "<tr><td align=center><b>ID Licitación:</b> $ID</td>";
	echo "<td align=left><b>Cliente:</b> $cliente</td>";
	echo "</tr><tr>";
	echo "<td align=left colspan=2><b>Facturas:</b><br>";
	$sql = "SELECT id_cobranza,nro_carpeta,nro_factura,monto,monto_original,fecha_factura,";
	$sql .= "fecha,comentario,simbolo,fecha_legal,fecha_estimativa,";
	$sql .= "fecha_presentacion,estado ";
	$sql .= "FROM (cobranzas LEFT JOIN moneda USING (id_moneda)) ";
	$sql .= "LEFT JOIN gestiones_comentarios ";
	$sql .= "ON cobranzas.id_cobranza = gestiones_comentarios.id_gestion and (gestiones_comentarios.tipo='COBRANZAS' OR tipo IS NULL)";
	$sql .= "WHERE id_licitacion=$ID ";
	//$sql .= "AND (gestiones_comentarios.tipo='COBRANZAS' OR tipo IS NULL) ";
	$sql .= "ORDER BY fecha_factura ASC, fecha ASC";
	$result = sql($sql) or fin_pagina();
	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$datos[$result->fields["id_cobranza"]]["estado"] = $result->fields["estado"];
			$datos[$result->fields["id_cobranza"]]["nro_carpeta"] = $result->fields["nro_carpeta"];
			$datos[$result->fields["id_cobranza"]]["nro_factura"] = $result->fields["nro_factura"];
            if ($cmd=="pendiente")
               $monto_factura=formato_money($result->fields["monto"]);
               else
               $monto_factura=formato_money($result->fields["monto_original"]);
			$datos[$result->fields["id_cobranza"]]["monto"] = $result->fields["simbolo"]."&nbsp;".$monto_factura;
			$datos[$result->fields["id_cobranza"]]["fecha_factura"] = Fecha($result->fields["fecha_factura"]);
			if ($result->fields["comentario"]) {
				if (!$datos[$result->fields["id_cobranza"]]["comentario"]) {
					$datos[$result->fields["id_cobranza"]]["comentario"] = "Comentarios:";
				}
				$datos[$result->fields["id_cobranza"]]["comentario"] .= "\n".Fecha($result->fields["fecha"])." - ".$result->fields["comentario"];
			}
			$datos[$result->fields["id_cobranza"]]["fecha_legal"] = Fecha($result->fields["fecha_legal"]);
			$datos[$result->fields["id_cobranza"]]["fecha_estimativa"] = Fecha($result->fields["fecha_estimativa"]);
			$datos[$result->fields["id_cobranza"]]["fecha_presentacion"] = Fecha($result->fields["fecha_presentacion"]);
			$result->MoveNext();
		}
		echo "<table border=1 width=100% bgcolor=#ffffff bordercolor=$bgcolor3 cellspacing=1 cellpadding=3>";
		echo "<tr id=mo>";
		echo "<td>Estado</td>";
		echo "<td>Carpeta</td>";
		echo "<td>Factura</td>";
		echo "<td>Monto</td>";
		echo "<td title='Fecha de Facturación'>F.F.</td>";
		echo "<td title='Fecha de Presentación'>F.P.</td>";
		echo "<td title='Fecha Estimativa'>F.E.</td>";
		echo "<td title='Fecha Legal'>F.L.</td>";
		echo "</tr>";
		foreach ($datos as $id_cobranza => $datos_cobranza) {
			$ref = encode_link($_SERVER["PHP_SELF"],array("cmd"=>strtolower($datos_cobranza["estado"]),"cmd1"=>"detalle_cobranza","id"=>$id_cobranza));
			if (strlen($datos_cobranza["comentario"]) > 500)
				$datos_cobranza["comentario"] = "...\n".substr($datos_cobranza["comentario"],-500);
			tr_tag($ref,"title='".$datos_cobranza["comentario"]."'");
			echo "<td align=center>".ucfirst(strtolower($datos_cobranza["estado"]))."&nbsp;</td>";
			echo "<td align=center>".$datos_cobranza["nro_carpeta"]."&nbsp;</td>";
			echo "<td align=center>".$datos_cobranza["nro_factura"]."&nbsp;</td>";
			echo "<td align=right>".$datos_cobranza["monto"]."&nbsp;</td>";
			echo "<td align=center>".$datos_cobranza["fecha_factura"]."&nbsp;</td>";
			echo "<td align=center>".$datos_cobranza["fecha_presentacion"]."&nbsp;</td>";
			echo "<td align=center>".$datos_cobranza["fecha_estimativa"]."&nbsp;</td>";
			echo "<td align=center>".$datos_cobranza["fecha_legal"]."&nbsp;</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	else {
		echo "No hay facturas cargadas";
	}
	echo "</td>";
	echo "</tr>";
	echo "</tr>";
	echo "<tr>";
	echo "<td style=\"border:$bgcolor3;\" align=center colspan=2>";
	echo "<br><input type=hidden name=cob_id_lic value='$ID'>";
//	echo "<input type=submit name=cob_nueva_factura value='Cargar Factura' style='width:160;'>&nbsp;&nbsp;&nbsp;";
	
    echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
	echo "<br><br></td></tr>";
	echo "</table></form>";
}
function nueva_factura($id_factura) {
	global $html_header,$bgcolor2,$bgcolor3;
	$q="select id_cobranza,estado from cobranzas where id_factura=$id_factura";
	$d=sql($q) or fin_pagina();
	if ($d && $d->fields[id_cobranza])
	{
		header ("location: ".encode_link($_SERVER['SCRIPT_NAME'],array("cmd"=>strtolower($d->fields["estado"]),"cmd1"=>"detalle_cobranza","id"=>$d->fields[id_cobranza])));
//		editar_factura($d->fields[id_cobranza]);
		die;
	}
	echo $html_header;
	$datos_factura=datos_factura($id_factura);

	if (!$datos_factura) die ("El id_factura no es valido");



	if ($ID=$datos_factura[id_licitacion])
	{
		$sql = "SELECT entidad.id_entidad,entidad.nombre AS cliente FROM ";
		$sql .= "licitacion LEFT JOIN entidad ";
		$sql .= "USING (id_entidad)";
		$sql .= "WHERE id_licitacion=$ID";
		$result=sql($sql) or fin_pagina();
		$id_cliente = $result->fields["id_entidad"];
  	    $cliente = $result->fields["cliente"];

	}
	else
	{
		$id_cliente='NULL';
		$cliente=$datos_factura[cliente]	;
		$ID= "<font color=red>Sin Licitacion</font>";
	}
	cargar_calendario();
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>";
	echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
	echo "<input type=hidden name=cob_id_factura value='$id_factura'>";
	echo "<input type=hidden name=cob_id_moneda value='$datos_factura[id_moneda]'>";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=+1>Nueva Factura</font></td></tr>";
	echo "<tr><td align=center><b>ID Licitación:</b> $ID</td>";
	echo "<td align=left><b>Cliente:</b> $cliente</td>";
	echo "</tr><tr>";
	echo "<td align=left colspan=2><b>Detalles de la factura:</b><br>";
	echo "<table width=100% border=0><tr>";
	echo "<td align=right><b>Nombre:</b></td>";
	echo "<td align=left><input type=text name=cob_nombre size=25 value='".$_POST["cob_nombre"]."'></td>";
	echo "<td align=right><b>Estado:</b></td>";
	echo "<td align=left><select name=cob_estado>";
	echo "<option value=0";
	if (!$_POST["cob_estado"]) echo " selected";
	echo ">Normal</option>";
	echo "<option value=1";
	if ($_POST["cob_estado"] == "1") echo " selected";
	echo ">Llamar en un rato</option>";
	echo "<option value=2";
	if ($_POST["cob_estado"] == "2") echo " selected";
	echo ">Llamar mañana</option>";
	echo "<option value=3";
	if ($_POST["cob_estado"] == "3") echo " selected";
	echo ">Llamar en unos días</option>";
	echo "</select></td>";
	echo "</tr><tr>";
	echo "<td align=right><b><font color=#ff0000>*</font>Fecha facturación:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_factura readonly size=10 maxlength=10 value='$datos_factura[fecha_factura]'></td>";
	echo "<td align=right><b><font color=#ff0000>*</font>Número de factura:</b></td>";
	echo "<td align=left>Tipo ".strtoupper($datos_factura[tipo_factura])."&nbsp;&nbsp;<input type=text size=13 name=cob_nro_factura style='text-align:right' readonly value='$datos_factura[nro_factura]'>&nbsp;";
	echo "</td>";
	echo "</tr><tr>";
	echo "<td align=right><b>Fecha presentación:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_presentacion size=10 maxlength=10 value=".$_POST["cob_fecha_presentacion"].">".link_calendario("cob_fecha_presentacion")."</td>";
	echo "<td align=right><b><font color=#ff0000>*</font>Monto:</b></td>";
	echo "<td align=left>";
	echo "$datos_factura[simbolo] &nbsp;<input type=text name=cob_monto size=15 maxlength=50 style='text-align:right' readonly value='".formato_money($datos_factura[monto])."'></td>";
	echo "</tr><tr>";
	echo "<td align=right><b>Fecha estimativa:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_estimativa size=10 maxlength=10 value=".$_POST["cob_fecha_estimativa"].">".link_calendario("cob_fecha_estimativa")."</td>";
	echo "<td align=right><b>Carpeta Número:</b></td>";
	echo "<td align=left><input type=text name=cob_nro_carpeta value='".$_POST["cob_nro_carpeta"]."'></td>";
	echo "</tr><tr>";
	echo "<td align=right><b>Fecha legal:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_legal size=10 maxlength=10 value=".$_POST["cob_fecha_legal"].">".link_calendario("cob_fecha_legal")."</td>";
	echo "<td align=right><b>Números remitos:</b></td>";
	echo "<td align=left><input type=text name=cob_nro_remitos readonly value='$datos_factura[nro_remito]'></td>";
	echo "</tr></table>";
	echo "</td>";
	echo "</tr><tr>";
	echo "<td align=center colspan=2><table width=100% border=0><tr>";
	echo "<td valign=top width=5%><b>Comentarios:</b></td>";
	echo "<td><textarea name='cob_comentarios' style='width:100%;' rows=5>".$_POST["cob_comentarios"]."</textarea></td>\n";
	echo "</tr></table></td>";
	echo "</tr><tr>";
	echo "<td align=center colspan=2 style=\"border:$bgcolor2\">";
	echo "<br><input type=hidden name=cob_id_lic value='".((es_numero($ID))?$ID:'NULL')."'>";
	echo "<input type=submit name=cob_agregar_factura value='Agregar' style='width:160;'>&nbsp;&nbsp;&nbsp;";
	echo "<input type=reset name=cob_reset value='Deshacer' style='width:160;'>&nbsp;&nbsp;&nbsp;";
//VER EL LINK DE ESTE BOTON CUANDO NO TIENE LICITACION
	if (es_numero($ID))
		echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".encode_link($_SERVER["PHP_SELF"],array("cmd"=>$cmd,"cmd1"=>"detalle","id_lic"=>"$ID"))."';\">";
	else
		echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".encode_link($_SERVER["PHP_SELF"],array())."';\">";
	echo "<br><br></td>";
	echo "</tr>";
	echo "</table></form>";
}
function editar_factura($id_cobranza,$param_id_factura=0) {
	global $permisos,$_ses_user,$html_header,$html_root,$bgcolor2,$bgcolor3,$_ses_user,$datos_barra;
	echo $html_header;
    //generar_barra_nav($datos_barra);
    //EL JOIN CON FACTURAS SE DEBE HACER POR ID_FACTURA Y NO POR NRO_FACTURA
	$sql = "SELECT cobranzas.id_cobranza,nro_carpeta,monto_original,facturas.id_factura,facturas.estado as estado_factura,facturas.tipo_factura,facturas.cliente,cobranzas.nro_factura as nro_factura,cobranzas.monto,cobranzas.fecha_factura,cobranzas.id_ingreso_egreso,cobranzas.cotizacion_dolar,";
	$sql .= "moneda.*,fecha_legal,fecha_estimativa,fecha_presentacion,facturas.id_entidad as entidad_factura,id_datos_ingreso,ctrl_ingreso,ctrl_egreso,";
	$sql .= "entidad.id_entidad,entidad.nombre as nombre_entidad,aceptacion_definitiva,";
	$sql .= "cobranzas.id_licitacion,cobranzas.nombre as nombre_cobranzas,cobranzas.estado,";
	$sql .= "fin_usuario,fin_fecha,estado_nombre,nro_remitos,ingreso_egreso.fecha_creacion ";
	$sql .= "FROM ((cobranzas LEFT JOIN moneda USING (id_moneda)) ";
	$sql .= "LEFT JOIN entidad USING (id_entidad)) LEFT JOIN facturas USING (id_factura)";
	$sql .= "LEFT JOIN caja.ingreso_egreso using (id_ingreso_egreso)";
	$sql .= " left join datos_ingresos using (id_datos_ingreso) ";
    $sql .= "WHERE cobranzas.id_cobranza=$id_cobranza ";

 

	$result = sql($sql) or fin_pagina();
	if ($result->RecordCount() == 1) {
		$nro_carpeta = $result->fields["nro_carpeta"];
		$nro_remitos = $result->fields["nro_remitos"];
		$nombre = $result->fields["nombre_cobranzas"];
		$id_factura = $result->fields["id_factura"];
		$nro_factura = $result->fields["nro_factura"];
		$tipo_factura = $result->fields["tipo_factura"];
		$id_moneda = $result->fields["id_moneda"];
		$monto= $result->fields["monto"];
        $monto_original=$result->fields["monto_original"];
		$fecha_factura = Fecha($result->fields["fecha_factura"]);
		$fecha_legal = Fecha($result->fields["fecha_legal"]);
		$fecha_estimativa = Fecha($result->fields["fecha_estimativa"]);
		$fecha_presentacion = Fecha($result->fields["fecha_presentacion"]);
		$id_licitacion = $result->fields["id_licitacion"] OR $id_licitacion="<font color=red>Sin Licitacion</font>";
		$id_cliente = $result->fields["id_entidad"];
		$entidad = $result->fields["nombre_entidad"] or $entidad = $result->fields["cliente"];
		$estado = $result->fields["estado"];
		$fin_nombre = $result->fields["fin_usuario"];
		$fin_fecha = Fecha($result->fields["fin_fecha"]);
		$fin_hora = Hora($result->fields["fin_fecha"]);
		$estado_nombre = $result->fields["estado_nombre"];
		$simbolo_moneda=$result->fields["simbolo"];
		$estado_factura=$result->fields["estado_factura"];
		$aceptacion_definitiva=$result->fields["aceptacion_definitiva"];
		$id_ingreso_egreso=$result->fields["id_ingreso_egreso"];
		$id_datos_ingreso=$result->fields["id_datos_ingreso"];
		$entidad_factura=$result->fields["entidad_factura"];  //esto es para tener el id_de la entidad de la factura cuando el seguimiento no tiene licitacion
		$fecha_ingreso=fecha($result->fields["fecha_creacion"]);
		$cotizacion_dolar=formato_money($result->fields["cotizacion_dolar"]);
		$control_ingreso=$result->fields["ctrl_ingreso"];
		$control_egreso=$result->fields["ctrl_egreso"];
        $id_estado_cobranzas_guardado=$result->fields["id_estado_cobranza"];
		if ($param_id_factura)
		{
			$datos_factura=datos_factura($param_id_factura);
			$id_factura=$param_id_factura;
			$nro_remitos = $datos_factura[nro_remito];
			$nro_factura = $datos_factura[nro_factura];
			$tipo_factura = $datos_factura[tipo_factura];
			$id_moneda = $datos_factura[id_moneda];
			$monto= $datos_factura[monto];
			$fecha_factura = $datos_factura[fecha_factura];
			$simbolo_moneda=$datos_factura[simbolo];
			$estado_factura=$datos_factura[estado];
			if ($id_licitacion=$datos_factura[id_licitacion])
			{
				$sql = "SELECT entidad.id_entidad,entidad.nombre AS cliente FROM ";
				$sql .= "licitacion LEFT JOIN entidad ";
				$sql .= "USING (id_entidad)";
				$sql .= "WHERE id_licitacion=$id_licitacion";
				$result=sql($sql) or fin_pagina();
				$id_cliente = $result->fields["id_entidad"];
				$entidad = $result->fields["cliente"];
			}
			else
			{
				$id_cliente='NULL';
				$entidad=$datos_factura[cliente]	;
				$id_licitacion= "<font color=red>Sin Licitacion</font>";
			}
		}
	}
	else { Error("No se encontro la factura"); die; }
    cargar_calendario();
	echo "<form action='".$_SERVER["PHP_SELF"]."' name=form method=post>";
	echo "<input type=hidden name=cob_id_lic value='".((es_numero($id_licitacion))?$id_licitacion:'NULL')."'>";
	echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
	echo "<input type=hidden name=cob_id_factura value='$id_factura'>";
	echo "<input type=hidden name=cob_id_moneda value='$id_moneda'>";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\"  align=center id=mo><font size=+1>Modificar Factura</font></td></tr>";
	echo "<tr><td align=center>";
	if (es_numero($id_licitacion))
		echo "<a style='cursor: hand;' onclick='window.location=\"".encode_link("licitaciones_view.php",array("ID"=>$id_licitacion,"cmd1"=>"detalle"))."\";'><b>ID Licitación:</b> $id_licitacion</a>";
	else
		echo "<b>ID Licitación:</b> $id_licitacion";
	echo "</td></tr>";
	echo "<tr><td align=center>";
		$qu="select fecha,estado,reporte,fechafinal from reporte_tecnico where id_licitacion=$id_licitacion";
		if (es_numero($id_licitacion)) $reportes=sql($qu) or fin_pagina();
        if (es_numero($id_licitacion) && $reportes->RecordCount()>0) {
			echo "<table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
			echo "<tr><td colspan=4 style=\"border:$bgcolor3;\"  align=center id=mo>\n";
			echo "<b>Reportes Técnicos</b>\n";
			echo "</td></tr>\n";
			echo "<tr>\n";
			echo "<td id=ma>Fecha inicio</td>\n";
			echo "<td id=ma>Reporte</td>\n";
			echo "<td id=ma>Estado</td>\n";
			echo "<td id=ma>Fecha de fin</td>\n";
			echo "</tr>\n";
			while ($fila=$reportes->fetchrow()) {
				$fech=substr($fila["fecha"],0,10);
				$hora=substr($fila["fecha"],11,8);
				echo "<tr><td>".fecha($fech)." $hora</td>\n";
				echo "<td>".$fila["reporte"]."</td>\n";
				echo "<td>".$fila["estado"]."</td>\n";
				$fechf=substr($fila["fechafinal"],0,10);
				$horaf=substr($fila["fechafinal"],11,8);
				if (fechaok(fecha($fechf)))
					echo "<td>".fecha($fechf)." $horaf</td></tr>\n";
				else
					echo "<td>Sin fecha final</td></tr>\n";
			}
		echo "</table>\n";
		}
		if (es_numero($id_licitacion)) echo "<input type=button name=reporte value='Reportar Problemas Técnicos' onclick='window.open(\"".encode_link('reportetecnico.php',Array("id_lic"=>$id_licitacion,"cliente"=>$entidad, "monto_factura"=>$simbolo_moneda." ".$monto, "nro_factura"=>$tipo_factura." ".$nro_factura))."\",\"\",\"toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400\")'>";
        echo "</td></tr>";
//   Aca va la parte de reporte
   if (es_numero($id_licitacion))
          {
           //tengo que ver que tengo renglones asociados
           $sql=" select ee.id_entrega_estimada,roc.id_subir
                                        from licitaciones.cobranzas c
                           join facturas f using(id_factura)
                           join items_factura using(id_factura)
                           join renglones_oc roc using(id_renglones_oc)
                           join subido_lic_oc sloc using (id_subir)
                           join entrega_estimada ee using (id_entrega_estimada)
                           where c.id_cobranza=$id_cobranza";
           $res=sql($sql) or fin_pagina();
           $id_entrega_estimada=$res->fields["id_entrega_estimada"];
           $id_subir=$res->fields["id_subir"];
           $oc=$res->fields["nro_orden"];
          $link=encode_link("../ordprod/seleccionar_renglon_adj.php",array("licitacion"=>$id_licitacion,
                                                                            "id_entrega_estimada"=>$id_entrega_estimada,
                                                                            "pagina_volver"=>"lic_cobranzas.php",
                                                                            "oc"=>$oc,
                                                                            "id_subir"=>$id_subir));
           if ($id_entrega_estimada){
              echo "<tr><td>";
              echo  "<input type='button' name='Ver Entregas' Value='Ver Entregas'  onclick=\"window.open('$link');\">";
              echo "</td></tr>";

           }

          }
	echo " <tr>";
    echo "<td align=left>";
    //incluyo la funcion que verifica si hay contactos
    echo "<table width='100%'>";
          echo "<tr>";
            echo "<td align='left'>";
            $nuevo_contacto=encode_link("../contactos_generales/contactos.php",array("modulo"=>"Cobranzas",
                                         "id_licitaciones"=>$id_licitacion,
                                         "id_general"=>$id_cliente));
            echo " <b>Cliente:</b> $entidad </td>";
            echo " <td align='right'>";
				if (es_numero($id_licitacion))
	  		        echo  "<input type='button' name='Nuevo' Value='Nuevo Contacto'  onclick=\"window.open('$nuevo_contacto','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td align='right' colspan=2>";
            if ($id_cliente!=null || $id_cliente != "")
           contactos_existentes("Cobranzas",$id_cliente);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
        echo "</td>";
        echo "</tr>";
	echo " <tr>";
        echo "<td align=left>";
        //incluyo la funcion que verifica si hay contactos de licitaciones
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<td align='left'>";
            $nuevo_contacto=encode_link("../general/contacto_licitacion.php",array("id_licitacion"=>$id_licitacion));
            echo " <b>Licitacion:</b> $id_licitacion </td>";
            echo " <td align='right'>";
				if (es_numero($id_licitacion))
	  		        echo  "<input type='button' name='Nuevo' Value='Nuevo Contacto Licitacion'  onclick=\"window.open('$nuevo_contacto','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td align='right' colspan=2>";
            if (es_numero($id_licitacion))
            contactos_existentes_licitacion($id_licitacion);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
        echo "</td>";
        echo "</tr>";
		echo "<tr>";
	echo "<td align=left><b>Detalles de la factura:</b>&nbsp;";
	if ($estado_factura=='a')
		echo "<font size=+1 color=red title='Deberia cambiar la factura por una factura sin anular'> LA FACTURA HA SIDO ANULADA </font>";
       if (permisos_check("inicio","cob_cambiar_factura"))
        {
		echo "<input type=button name='boton_cambiar' value='Cambiar Factura' title='Cambie:\n\tEl ID de Licitacion\n\tNº de factura\n\tfecha y monto de la misma' onclick='location=\""
		.encode_link("../facturas/factura_listar.php",array ("backto"=>$_SERVER['SCRIPT_NAME'],"filtro"=>"terminadas","_ses_global_extra"=>array("id"=>$id_cobranza)))."\";'>";
        }
if ($id_ingreso_egreso != "") {
          $sql_dist="select id_distrito,id_moneda from caja.ingreso_egreso
                   join caja.caja using (id_caja) where id_ingreso_egreso=$id_ingreso_egreso";
          $res_dist=sql($sql_dist) or fin_pagina();
          $moneda_ingreso=$res_dist->fields['id_moneda'];
          }

    echo "<b> </td></tr>";
    echo "<td align=center>";
           echo "<table width=100% border=0>";
           echo "<tr><td align=right><b>Nombre:</b></td>";
           echo "<td align=left><input type=text name=cob_nombre size=25 value='$nombre'></td>";
           echo "<td align=right><b>Estado:</b></td>";
           echo "<td align=left>";
           echo "<select name=cob_estado>";
           echo "<option value=0";
               if (!$estado_nombre) echo " selected";
               echo ">Normal</option>";
               echo "<option value=1";
               if ($estado_nombre == "1") echo " selected";
               echo ">Llamar en un rato</option>";
               echo "<option value=2";
               if ($estado_nombre == "2") echo " selected";
               echo ">Llamar mañana</option>";
               echo "<option value=3";
               if ($estado_nombre == "3") echo " selected";
               echo ">Llamar en unos días</option>";
           echo "</select></td>";
           echo "</tr>";
           echo"<tr>";
           echo "<td align=right><b><font color=#ff0000>*</font>Fecha facturación:</b></td>";
           echo "<td align=left><input type=text name=cob_fecha_factura readonly size=10 maxlength=10 value='$fecha_factura'></td>";
           echo "<td align=right><b><font color=#ff0000>*</font>Número de factura:</b></td>";
           echo "<td align=left> Tipo ".strtoupper($tipo_factura)."&nbsp;&nbsp;<input type=text style='text-align:right' size=13 readonly name=cob_nro_factura value='$nro_factura'>\n";
           echo "<input type=button name='boton_ir' value='Ir' onclick='window.location=\""
           .encode_link($html_root."/modulos/facturas/factura_nueva.php",array("modulo"=>"facturas","id_factura"=>$id_factura))."\";'>";
           echo "</td>";
           echo "</tr><tr>";
           echo "<td align=right><b>Fecha presentación:</b></td>";
           echo "<td align=left><input type=text name=cob_fecha_presentacion size=10 maxlength=10 value='$fecha_presentacion'>".link_calendario("cob_fecha_presentacion")."</td>";
           echo "<td align=right><b><font color=#ff0000>*</font>Monto:</b></td>";
           echo "<td align=left>";
           if($estado_factura=='A' || $estado_factura=='a')
	   $style_null="style='text-align:right;color=\"red\"'";
	   else
	   $style_null="style='text-align:right'";
	   echo "$simbolo_moneda &nbsp;<input type=text name=cob_monto size=15 maxlength=50 $style_null readonly value='".formato_money($monto_original)."'></td>";
	   echo "</tr><tr>";
	   echo "<td align=right><b>Fecha estimativa:</b></td>";
	   echo "<td align=left><input type=text name=cob_fecha_estimativa size=10 maxlength=10 value='$fecha_estimativa'>".link_calendario("cob_fecha_estimativa")."</td>";
	   echo "<td align=right><b>Carpeta Número:</b></td>";
	   echo "<td align=left><input type=text name=cob_nro_carpeta value='$nro_carpeta'></td>";
	   echo "</tr><tr>";
	   echo "<td align=right><b>Fecha legal:</b></td>";
	   echo "<td align=left><input type=text name=cob_fecha_legal size=10 maxlength=10 value='$fecha_legal'>".link_calendario("cob_fecha_legal")."</td>";
	   echo "<td align=right><b>Números de remitos:</b></td>";
	   echo "<td align=left><input type=text name=cob_nro_remitos value='$nro_remitos'></td>";
	   echo "</tr><tr>";
	   echo "<td align=right colspan=3><b>Aceptación definitiva lista:</b></td>";
	   echo "<td align=left>";
	   echo "<select name=cob_aceptacion_definitiva>";
	   echo "<option value='t'";
	   if ($aceptacion_definitiva == "t") echo " selected";
	   echo ">Sí\n";
	   echo "<option value='f'";
	   if ($aceptacion_definitiva == "f") echo " selected";
	   echo ">No\n";
	   echo "</select>";
	   echo "</td>";
	   echo "</tr>";
	   echo "</table>";
	echo "</td></tr>\n";
    ?>
    <script>
       function habilitar_factoring(object,tabla){
              if (object.options[object.selectedIndex].text=="Vendida")
                            Mostrar(tabla);
                            else
                            Ocultar(tabla);
       }//de la funcion
    </script>
    <?
    $sql="select id_estado_cobranza,estado_cobranzas.nombre,factoring.nombre as nombre_factoring,factoring.id_factoring ,fecha
          from
            licitaciones.historial_estados_cobranzas join licitaciones.estado_cobranzas using (id_estado_cobranza)
            left join licitaciones.factoring using(id_factoring)
           where id_cobranza=$id_cobranza and historial_estados_cobranzas.activo=1 ";
    $res=sql($sql) or fin_pagina();
    $estado_actual=$res->fields["nombre"];
    $nombre_factoring=$res->fields["nombre_factoring"];
    if (!$estado_actual) $estado_actual=" No Hay ningun estado";
    $sql=" select * from estado_cobranzas where activo=1";
    $estado_cobranzas=sql($sql) or fin_pagina();
    //Estado Cobranzas
    echo "<tr><td>";
    echo "<table width=90% border=0>";
    echo  "<tr><td align=left><b>Log Estado</b>&nbsp;";
    echo  "<input type=checkbox class='estilos_check' name='chk_log' onclick='javascript:(this.checked)?Mostrar(\"tabla_detalles_log\"):Ocultar(\"tabla_detalles_log\");'>";
    echo  "</td>";
    echo "</tr>";
    echo "</table></td></tr>";
    echo "<tr><td align=center>";
    echo "<div id='tabla_detalles_log' style='display:none'>";
    mostrar_log_estados($id_cobranza);
    echo "</div>";
    echo "</td></tr>";
    echo "<tr><td><table width=100% align=left border=0>";
    echo "<tr>";
    echo "<td width=40% align=left>";
    echo "<b>Estado Actual: &nbsp;&nbsp;<font color=red size=2>$estado_actual</font></b>";
    echo "</td>";
    echo "<td align=left width=20%><b>Cambiar Estado</b> </td>";
    echo "<td >";
       echo "<select name=estado_cobranzas onchange=\"habilitar_factoring(this,'tabla_factoring')\">";
            echo "<option value='-1'></option>";
            for($i=0;$i<$estado_cobranzas->recordcount();$i++){
                $nombre_estado=$estado_cobranzas->fields["nombre"];
                $id_estado_cobranzas=$estado_cobranzas->fields["id_estado_cobranza"];
                echo "<option  value='$id_estado_cobranzas'> ".$nombre_estado."</option>";
                $estado_cobranzas->movenext();
            }
         echo "</select>";
     echo "</td>";
     echo "</tr>";
     echo "</table></td></tr>";
    //fin del estado de las cobranzas
    //Aca va la parte de los FACTORIN
   if ($estado_actual=="Vendida")
			$det_visib = "block";
			else
			$det_visib = "none";

    $sql=" select * from licitaciones.factoring where activo=1";
    $factoring=sql($sql) or fin_pagina();
    echo "<tr><td>";
    echo "<div id='tabla_factoring' style='display:$det_visib'>";
    echo "<table width=100% align=left border=0>";
      echo "<tr>";
      echo "<td width=40% align=left><b>Factoring:&nbsp;&nbsp;<font color=red size=2>$nombre_factoring</font></b></td>";
      echo "<td  align=left width=20%><b>Cambiar Factoring</b></td>";
      echo "<td>";
         echo "<select name=factoring>";
         echo "<option value='-1'></option>";
             for($i=0;$i<$factoring->recordcount();$i++){
                  $nombre=$factoring->fields["nombre"];
                  $id_factoring=$factoring->fields["id_factoring"];
                  if ($id_factoring==$id_factoring_guardado)
                                     $selected=" selected";
                                     else
                                     $selected=" ";
                   echo "<option $selected value='$id_factoring'>$nombre</option>";
              $factoring->movenext();
             }
         echo "</select>";
      echo "</td>";
    echo "</table></div></td></tr>";
   //Aca finaliza la parte de los FACTORIN
	echo "<tr><td align=center>\n";
	echo "<input type=button name=copiar value='Copiar Comentario A' onclick='window.open(\"".encode_link("copiar_comentario.php",array("id_cobranza"=>$id_cobranza))."\",\"\",\"toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300\")';>\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr><td align=center>\n";
	if ($id_datos_ingreso!=null || $id_datos_ingreso!="")
	    mostrar_ingresos($id_cobranza,1);
	else  mostrar_ingresos($id_cobranza,0);
	$sql_egresos="select * from licitaciones.detalle_egresos where id_cobranza=$id_cobranza and control_egreso=1";
		$res_eg=sql($sql_egresos) or fin_pagina();

	if ($res_eg->RecordCount()>0) $flag=1;
	else $flag=0;
	mostrar_egresos($id_cobranza,$flag,'',$id_datos_ingreso);
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=left >";
	gestiones_comentarios($id_cobranza,"COBRANZAS",1);
	echo "</td>";
	echo "</tr><tr>";
	if ($estado == "FINALIZADA") {
		echo "<td align=center><b><font size=4 color=#ff0000>Finalizada por $fin_nombre el $fin_fecha a las $fin_hora</font></b><br>";
		echo "</tr><tr>";
	}
	echo "<td align=center  style=\"border:$bgcolor2\"><br>";
	if ($estado != "FINALIZADA") {
		        echo "<input type=hidden name=cob_id_cobranza value='$id_cobranza'>";
                echo "<input type=hidden name=cob_guardar_factura value=''>";
		        $link=encode_link("lic_llamadas.php",array("id_cobranza"=>$id_cobranza,"script"=>"window.opener.cerrar_ventana_1();"));
                ?>
                <script>
                 function cerrar_ventana_1(){
                  document.form.cob_guardar_factura.value='Guardar';
                  document.form.submit();
                  return true;
                  }
                 function cargar_llamadas(){
                  var error=0;
                  var fecha_presentacion,fecha_estimativa;
                  fecha_presentacion=eval(document.all.cob_fecha_presentacion);
                  fecha_estimativa=eval(document.all.cob_fecha_estimativa);
                  if (document.all.estado_cobranzas.options[document.all.estado_cobranzas.selectedIndex].text=='Vendida' &&   document.all.factoring.options[document.all.factoring.selectedIndex].value==-1)
                       {
                       error=1;
                       alert('Debe elegir un Factoring');
                       }
                  if (document.all.estado_cobranzas.options[document.all.estado_cobranzas.selectedIndex].text=='Presentada' &&   fecha_presentacion.value=="")
                       {
                       error=1;
                       alert('Si elegi el estado Presentada debe tener fecha de presentación');
                       }
                  if (fecha_presentacion.value!="" && fecha_estimativa.value=="") {
                         error=1;
                         alert('Si ingresa Fecha de Presentacion, Debe ingresar Fecha Estimativa');
                        }
                 if (fecha_estimativa.value!="" && fecha_presentacion.value=="") {
                         error=1;
                         alert('Si ingresa Fecha Estimativa, Debe ingresar Fecha Presentacion');
                       }
                 if (error==0)
                    wventana=window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1');
                 }//de la function
                </script>
                <?
                echo "<input type=button name=g_factura value='Guardar' style='width:80;' onclick=\"return cargar_llamadas()\">&nbsp;&nbsp;&nbsp;";
		        if (permisos_check("inicio","atar_facturas")) {
			     echo "<input type=button name=atar value='atar esta factura' onclick='window.open(\"".encode_link("lic_atar.php",array("id"=>$id_cobranza,"nro"=>$nro_factura,"lic"=>$id_licitacion))."\");'>&nbsp;&nbsp;&nbsp;";
		        }

		if (permisos_check("inicio","licitaciones_ingreso_cob") )
               $permiso_ingreso="";
            else
               $permiso_ingreso=" disabled";

        if ($id_ingreso_egreso!=NULL || $id_ingreso_egreso!="" ) {  //se registro  ingreso
            	// $fin1='disabled';
            	 $des_vtafact='disabled';
            }
             else {
             	// $fin1="";
             	 $des_vtafact="";
             }
        if($estado_factura=='A' || $estado_factura=='a') {
             //$fin1='disabled';
             $des_vtafact='disabled';
             }
       //anda bien
		$entidad_caja=$id_cliente or $entidad_caja=$entidad_factura;
    	//if ($res_eg->RecordCount() > 0 || $estado_factura=='a' || $fin1=="") {
		if ($res_eg->RecordCount() > 0 || $estado_factura=='a') {
		   $fin_eg="disabled";
		}
		else $fin_eg="";
	  /* if ($monto >= 0) {
 		          if ($fin1 =='disabled' && $fin_eg =='disabled')
                       $fin=" ";
		               else $fin=" disabled";
		    }
		    else {
			 $fin=""; //es nota de credito => puede finalizar
			 $fin1=" disabled";
			 $des_vtafact=" disabled";
			 */
		 if ($monto >= 0) {
 		          if ($fin_eg =='disabled')
                       $fin=" ";
		               else $fin=" disabled";
		    }
		    else {
			 $fin=""; //es nota de credito => puede finalizar
			 //$fin1=" disabled";
			 $des_vtafact=" disabled";
	 	     }
		if ($id_datos_ingreso!=null || $id_datos_ingreso!="")  { //venta_factura
             $fin1='disabled';
             $fin_eg='disabled ';
             if ($control_ingreso==1 && $control_egreso==1) {
             	       $fin="";
                       //$des_vtafact=" disabled ";
                        }
                        else {
                              $fin=" disabled";
                              }
              }
       /* $link=encode_link('elegir_caja.php',array("monto"=>$monto,"tipo_fact"=>$tipo_factura,"id_moneda"=>$id_moneda,"id_cliente"=>$entidad_caja,"id_cobranza"=>$id_cobranza,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura,"id_factura"=>$id_factura));
        $onclick="window.open(\"$link\",\"\",\"\")";
        $link1=encode_link('elegir_caja_egresos.php',array("monto"=>$monto,"tipo_fact"=>$tipo_factura,"id_moneda"=>$moneda_ingreso,"id_cliente"=>$entidad_caja,"id_cobranza"=>$id_cobranza,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura,"id_factura"=>$id_factura,"moneda_factura"=>$id_moneda));
        $onclick_eg="window.open(\"$link1\",\"\",\"\")";
        echo "<input type=button name=registrar_ingreso value='Ingreso' $permiso_ingreso $fin1 style='width:80;' onclick='$onclick' >&nbsp;&nbsp;&nbsp;";
        echo "<input type=button name=registrar_egreso value='Egreso' $fin_eg style='width:80;' onclick='$onclick_eg' >&nbsp;&nbsp;&nbsp;";*/
        $link=encode_link('elegir_caja.php',array("monto"=>$monto,"tipo_fact"=>$tipo_factura,"moneda_factura"=>$id_moneda,"id_cliente"=>$entidad_caja,"id_cobranza"=>$id_cobranza,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura,"id_factura"=>$id_factura));
        $onclick="window.open(\"$link\",\"\",\"\")";
        echo "<input type=button name=registrar_egreso value='Ingreso/Egreso' $fin_eg onclick='$onclick' >&nbsp;&nbsp;&nbsp;";
		if ($estado != "FINALIZADA" && permisos_check("inicio","lic_cobranzas_finalizar")) {
		   echo "<input type=submit name=cob_finalizar_factura value='Finalizar' style='width:90;' $fin onClick=\"return confirm('ADVERTENCIA: Se va a finalizar esta factura!');\">&nbsp;&nbsp;&nbsp;";
		}
    echo "<input type=reset name=cob_reset value='Deshacer' style='width:90;'>&nbsp;&nbsp;&nbsp;";
	}
	echo "<input type=button name=volver style='width:90;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
	echo "<br><br></td>";
	echo "</tr>";
    if ($estado != "FINALIZADA") {
	    $link=encode_link('venta_factura.php',array("monto_factura"=>$monto,"monto_original"=>$monto_original,"tipo_factura"=>$tipo_factura,"moneda_factura"=>$id_moneda,"id_cliente"=>$entidad_caja,"id_cobranza"=>$id_cobranza,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura,"id_factura"=>$id_factura));
        $onclick="window.open(\"$link\",\"\",\"\")";
	    echo "<tr><td><input type='button' name='venta_factura' $des_vtafact  value='Venta de Factura' onclick='$onclick'>";
	    $link_fin=encode_link('finalizar_sin_ing_eg.php',array("id_cobranza"=>$id_cobranza));
        if (permisos_check("inicio","permiso_fin_sin_ing_eg") && $fin1=="") { //tiene permiso y no hay ingresos
	          echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' name='finalizar_sin_ing_eg' value='Finalizar sin Ingresos/Egresos' onClick=\"window.open('$link_fin','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=450');\">";
              }
     }
	echo "</td></tr>\n";
    echo "</table></form>";
}

//pendientes atadas
function editar_atadas($id_cob,$ata) {
	global $html_header,$html_root,$bgcolor2,$bgcolor3,$permisos,$_ses_user;
	echo $html_header;
	$arr[]=$id_cob;
	$contador_facturas = 0;

	while ($arr1=$ata->fetchrow())
		$arr[]=$arr1["id_secundario"];

$cont_ingresos=0;
$contador_anuladas=0;
$moneda_prim=0;
$control_estado="";
//print_r($arr);
$id_cobra=$arr[0];
//echo $id_cobra;
$ka="select id_licitacion,entidad.nombre from cobranzas
      left join entidad using(id_entidad) where id_cobranza=$id_cobra";
$licenti=sql($ka) or fin_pagina();
$entidad=$licenti->fields["nombre"];
$id_licitacion=$licenti->fields["id_licitacion"];
$qu="select fecha,reporte_tecnico.estado,reporte,fechafinal from reporte_tecnico
     WHERE id_licitacion=$id_licitacion";
		if (es_numero($id_licitacion)) {
			$reportes=sql($qu) or fin_pagina();
		//print_r($reportes->fields);
        //if ($reportes->RecordCount()>0) {
			echo "<table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
			echo "<tr><td colspan=4 style=\"border:$bgcolor3;\"  align=center id=mo>\n";
			echo "<b>Reportes Técnicos</b>\n";
			echo "</td></tr>\n";
			echo "<tr>\n";
			echo "<td id=ma>Fecha inicio</td>\n";
			echo "<td id=ma>Reporte</td>\n";
			echo "<td id=ma>Estado</td>\n";
			echo "<td id=ma>Fecha de fin</td>\n";
			echo "</tr>\n";
			while ($fila=$reportes->fetchrow()) {
				$fech=substr($fila["fecha"],0,10);
				$hora=substr($fila["fecha"],11,8);
				echo "<tr><td>".fecha($fech)." $hora</td>\n";
				echo "<td>".$fila["reporte"]."</td>\n";
				echo "<td>".$fila["estado"]."</td>\n";
				$fechf=substr($fila["fechafinal"],0,10);
				$horaf=substr($fila["fechafinal"],11,8);
				if (fechaok(fecha($fechf)))
					echo "<td>".fecha($fechf)." $horaf</td></tr>\n";
				else
					echo "<td>Sin fecha final</td></tr>\n";
			}
			echo "<tr><td colspan=4 align=center>\n";
			echo "<input type=button name=reporte value='Reportar Problemas Técnicos' onclick='window.open(\"".encode_link('reportetecnico.php',Array("id_lic"=>$id_licitacion,"cliente"=>$entidad))."\",\"\",\"toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400\")'>";
			echo "</td></tr>\n";
			echo "</table>\n";
		}
		//if (es_numero($id_licitacion)) echo "<input type=button name=reporte value='Reportar Problemas Técnicos' onclick='window.open(\"".encode_link('reportetecnico.php',Array("id_lic"=>$id_licitacion,"cliente"=>$entidad))."\",\"\",\"toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=400\")'>";
	$monto_atadas=0;
	$i=0;
	while (list($key,$id_cobranza)=each($arr)) {
	$contador_facturas++;
	$sql = "SELECT id_cobranza,nro_carpeta,facturas.id_factura,facturas.tipo_factura,facturas.cliente,cobranzas.nro_factura as nro_factura,cobranzas.monto,cobranzas.fecha_factura,facturas.estado as estado_factura,cobranzas.id_ingreso_egreso,cobranzas.cotizacion_dolar,";
	$sql .= "moneda.*,fecha_legal,fecha_estimativa,fecha_presentacion,facturas.id_entidad as entidad_factura,monto_original,id_vta_atada,";
	$sql .= "entidad.id_entidad,entidad.nombre as nombre_entidad,aceptacion_definitiva,";
	$sql .= "cobranzas.id_licitacion,cobranzas.nombre as nombre_cobranzas,cobranzas.estado,";
	$sql .= "fin_usuario,fin_fecha,estado_nombre,nro_remitos,ingreso_egreso.fecha_creacion ";
	$sql .= "FROM ((cobranzas LEFT JOIN moneda USING (id_moneda)) ";
	$sql .= "LEFT JOIN entidad USING (id_entidad)) LEFT JOIN facturas USING (id_factura)";
	$sql .= "LEFT JOIN caja.ingreso_egreso using (id_ingreso_egreso)";
	$sql .= "WHERE id_cobranza=$id_cobranza ";
	$result = sql($sql) or fin_pagina();
	if ($result->RecordCount() == 1) {
		$nro_carpeta = $result->fields["nro_carpeta"];
		$nro_remitos = $result->fields["nro_remitos"];
		$nombre = $result->fields["nombre_cobranzas"];
		$id_factura = $result->fields["id_factura"];
		$nro_factura = $result->fields["nro_factura"];
		$tipo_factura = $result->fields["tipo_factura"];
		$id_moneda = $result->fields["id_moneda"];
		$monto= $result->fields["monto"];  //este queda en el caso que se realice las resta del monto pagado
		$monto_original= $result->fields["monto_original"];
		$fecha_factura = Fecha($result->fields["fecha_factura"]);
		$fecha_legal = Fecha($result->fields["fecha_legal"]);
		$fecha_estimativa = Fecha($result->fields["fecha_estimativa"]);
		$fecha_presentacion = Fecha($result->fields["fecha_presentacion"]);
		$id_licitacion = $result->fields["id_licitacion"] OR $id_licitacion="<font color=red>Sin Licitacion</font>";
		$id_cliente = $result->fields["id_entidad"];
		$entidad = $result->fields["nombre_entidad"] or $entidad = $result->fields["cliente"];
		$estado = $result->fields["estado"];
		$fin_nombre = $result->fields["fin_usuario"];
		$fin_fecha = Fecha($result->fields["fin_fecha"]);
		$fin_hora = Hora($result->fields["fin_fecha"]);
		$estado_nombre = $result->fields["estado_nombre"];
		$simbolo_moneda = $result->fields["simbolo"];
		$estado_factura = $result->fields["estado_factura"];
		$aceptacion_definitiva = $result->fields["aceptacion_definitiva"];
		$id_ingreso_egreso=$result->fields["id_ingreso_egreso"];
		$id_vta_atada=$result->fields["id_vta_atada"];
		$entidad_factura=$result->fields["entidad_factura"];  //esto es para tener el id_de la entidad de la factura cuando el seguimiento no tiene licitacion
		$fecha_ingreso=fecha($result->fields['fecha_creacion']);
		$cotizacion_dolar=formato_money($result->fields['cotizacion_dolar']);
  	    if ($id_ingreso_egreso !=null || $id_ingreso_egreso!="") {
		     $cont_ingresos++;
		 }
		if ($control_estado=="") $control_estado=$estado_factura; //estado de la factura anulada
		if ($estado_factura !='a' || $estado_factura !='A') {
		   $monto_atadas+=$monto_original;
		}
	}
	else { Error("No se encontro la factura"); die; }
	cargar_calendario();
	echo "<script src='$html_root/lib/funciones.js'></script>";
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post name='form$contador_facturas'>";
	echo "<input type=hidden name=cob_id_lic value='$id_licitacion'>";
	echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=+1>Modificar Factura</font></td></tr>";
	echo "<tr><td colspan=2>";
	if ($contador_facturas == 1) {
		$det_visib = "block";
		$det_check = "checked";
	}
	else {
		$det_visib = "none";
		$det_check = "";
	}
	echo "<table border=0 width=100%>";
	echo "<tr><td>";
	echo "<b>Tipo:</b> ".strtoupper($tipo_factura);
	echo "</td><td>";
	echo "<b>Número:</b> ".$nro_factura;
	echo "</td><td>";
	echo "<b>Monto:</b> ".$simbolo_moneda." ".formato_money($monto);
	echo "</td><td align=right>";
	echo "<b>Mostrar Detalles:</b> <input type=checkbox name=det $det_check onclick='javascript:(this.checked)?Mostrar(\"tabla_detalles_$contador_facturas\"):Ocultar(\"tabla_detalles_$contador_facturas\");'>";
	echo "</td></tr></table>";
	echo "</td></tr>";
	echo "<tr><td>";
	//REGISTRAR INGRESO
	if (permisos_check("inicio","licitaciones_ingreso_cob"))
               $permiso_ingreso="";
            else
               $permiso_ingreso=" disabled";
			if ($id_ingreso_egreso!=NULL || $id_ingreso_egreso!="" ) {  //se registro un ingreso
            	 $fin="";
		         $fin1=' disabled'; //para deshabilitar boton ingreso
		         $fin_eg=' disabled'; //para deshabilitar boton egreso
            }
            elseif ($id_vta_atada!=NULL || $id_vta_atada != "") {
                 $fin1=' disabled'; //para deshabilitar boton ingreso
                 $fin_eg=' disabled'; //para deshabilitar boton egreso
             } else {
             	 $fin=' disabled'; //no puede finalizar el seguimiento
             	 $fin1="";
             }
	       if($estado_factura=='A' || $estado_factura=='a') {
               $fin1=' disabled';
               $contador_anuladas++;
	       }
           $entidad_caja=$id_cliente or $entidad_caja=$entidad_factura;
        //$id_cob es id_cobranza primaria
        $link=encode_link('elegir_caja.php',array("monto"=>$monto,"tipo_fact"=>$tipo_factura,"id_moneda"=>$id_moneda,"id_cliente"=>$entidad_caja,"id_cobranza"=>$id_cobranza,"id_licitacion"=>$id_licitacion,"nro_factura"=>$nro_factura,"id_factura"=>$id_factura,"id_cob"=>$id_cob));
        $onclick="window.open(\"$link\",\"\",\"\")";
	    if ($id_ingreso_egreso != "") {
          $sql_dist="select id_distrito,id_moneda from caja.ingreso_egreso
                   join caja.caja using (id_caja) where id_ingreso_egreso=$id_ingreso_egreso";
          $res_dist=sql($sql_dist) or fin_pagina();
          $moneda_ingreso=$res_dist->fields['id_moneda'];
	    }
       // echo "<input type=button name=registrar_ingreso_$i value='Registrar Ingreso' style='width:120;' $permiso_ingreso $fin1 onclick='$onclick'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
       //  $i++;
       //FIN DE REGISTRAR INGRESO
  	   // busco el id_moneda para el egreso
       //si todas las faturas estan en dolares mon_prim es dolares
       //si todas las faturas estan en pesos o hay en pesos y dolares mon_prim es pesos
        if ($moneda_prim==0)
            $moneda_prim=$moneda_ingreso;
        elseif ($moneda_prim != $moneda_ingreso)
             $moneda_prim=1;
	echo "</td></tr>";
    //aca va la parte de entregas
    if (es_numero($id_licitacion))
          {
          //tengo que ver que tengo renglones asociados
           $sql=" select ee.id_entrega_estimada from licitaciones.cobranzas c
                           join facturas f using(id_factura)
                           join items_factura using(id_factura)
                           join renglones_oc roc using(id_renglones_oc)
                           join subido_lic_oc sloc using (id_subir)
                           join entrega_estimada ee using (id_entrega_estimada)
                           where c.id_cobranza=$id_cobranza and f.nro_factura='$nro_factura'";
           $res=sql($sql) or fin_pagina();
           $id_entrega_estimada=$res->fields["id_entrega_estimada"];
           $oc=$res->fields["id_subir"];
           $link=encode_link("../ordprod/seleccionar_renglon_adj.php",array("licitacion"=>$id_licitacion,
                                                                            "id_entrega_estimada"=>$id_entrega_estimada,
                                                                             "pagina_volver"=>"lic_cobranzas.php",
                                                                             "oc"=>$oc));
           if ($id_entrega_estimada){
           echo "<tr><td>";
           echo  "<input type='button' name='entregas' Value='Ver Entregas'  onclick=\"window.open('$link');\">";
           echo "</td></tr>";
           }
          }
    //finaliza la parte de entregas
	echo "<tr><td align=center colspan=2>";
	echo "<div id='tabla_detalles_$contador_facturas' style='display:$det_visib'>";
	echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";
	echo "<tr><td align=center>";
	if (es_numero($id_licitacion))
		echo "<a style='cursor: hand;' onclick='window.location=\"".encode_link("licitaciones_view.php",array("ID"=>$id_licitacion,"cmd1"=>"detalle"))."\";'><b>ID Licitación:</b> $id_licitacion</a>";
	else
		echo "<b>ID Licitación:</b> $id_licitacion";
	// Reportes tecnicos
	echo "<tr><td align=center>";
	echo "</td></tr>";
	// Fin de Reportes tecnicos
	echo " <tr>";
	echo "</td></tr>";
	echo " <tr><td align=left>";
//incluyo la funcion que verifica si hay contactos
    echo "<table width='100%'>";
    echo "<tr>";
            echo "<td align='left'>";
            $nuevo_contacto=encode_link("../contactos_generales/contactos.php",array("modulo"=>"Cobranzas",
                                         "id_licitaciones"=>$id_licitacion,
                                         "id_general"=>$id_cliente));
            echo " <b>Cliente:</b> $entidad </td>";
            echo " <td align='right'>";
				if (es_numero($id_licitacion))
					echo  "<input type='button' name='Nuevo' Value='Nuevo Contacto'  onclick=\"window.open('$nuevo_contacto','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td align='right' colspan=2>";
           if ($id_cliente!=null || $id_cliente != "")

            contactos_existentes("Cobranzas",$id_cliente);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
    echo "</td>";
    echo "</tr><tr>";
	echo "<td align=left colspan=2><b>Detalles de la factura:</b>&nbsp;";
	if ($estado_factura=='a')
		echo "<font size=+1 color=red title='Deberia cambiar la factura por una factura sin anular'> LA FACTURA HA SIDO ANULADA </font>";
        if (permisos_check("inicio","cob_cambiar_factura"))
		echo "<input type=button name='boton_cambiar' style='width=110' value='Cambiar Factura' title='Cambie:\n\tEl ID de Licitacion\n\tNº de factura\n\tfecha y monto de la misma' onclick='location=\""
		.encode_link("../facturas/factura_listar.php",array ("backto"=>$_SERVER['SCRIPT_NAME'],"filtro"=>"terminadas","_ses_global_extra"=>array("id"=>$id_cobranza)))."\";'>";
	echo "<br><br>";
	echo "<table width=100% border=0><tr>";
	echo "<td align=right><b>Nombre:</b></td>";
	echo "<td align=left><input type=text name=cob_nombre size=25 value='$nombre'></td>";
	echo "<td align=right><b>Estado:</b></td>";
	echo "<td align=left><select name=cob_estado>";
    echo "<option value=0";
    if (!$estado_nombre) echo " selected";
    echo ">Normal</option>";
    echo "<option value=1";
    if ($estado_nombre == "1") echo " selected";
    echo ">Llamar en un rato</option>";
    echo "<option value=2";
    if ($estado_nombre == "2") echo " selected";
    echo ">Llamar mañana</option>";
    echo "<option value=3";
    if ($estado_nombre == "3") echo " selected";
    echo ">Llamar en unos días</option>";
    echo "</select></td>";
	echo "</tr><tr>";
	echo "<td align=right><b><font color=#ff0000>*</font>Fecha facturación:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_factura readonly size=10 maxlength=10 value='$fecha_factura'></td>";
	echo "<td align=right><b><font color=#ff0000>*</font>Número de factura:</b></td>";
	echo "<td align=left> Tipo ".strtoupper($tipo_factura)."&nbsp;&nbsp;<input type=text style='text-align:right' size=13 readonly name=cob_nro_factura value='$nro_factura'>\n";
	echo "<input type=button name='boton_ir' value='Ir' onclick='window.location=\""
		.encode_link($html_root."/modulos/facturas/factura_nueva.php",array("modulo"=>"facturas","id_factura"=>$id_factura))."\";'>";
	echo "</td>";
	echo "</tr><tr>";
	echo "<td align=right><b>Fecha presentación:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_presentacion size=10 maxlength=10 value='$fecha_presentacion'>".link_calendario("cob_fecha_presentacion")."</td>";
	echo "<td align=right><b><font color=#ff0000>*</font>Monto:</b></td>";
	echo "<td align=left><input type=hidden name=cob_id_moneda value='$id_moneda'>";
	echo "$simbolo_moneda &nbsp;<input type=text name=cob_monto size=15 maxlength=50 style='text-align:right' readonly value='".formato_money($monto)."'></td>";
	echo "</tr><tr>";
	echo "<td align=right><b>Fecha estimativa:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_estimativa size=10 maxlength=10 value='$fecha_estimativa'>".link_calendario("cob_fecha_estimativa")."</td>";
	echo "<td align=right><b>Carpeta Número:</b></td>";
	echo "<td align=left><input type=text name=cob_nro_carpeta value='$nro_carpeta'></td>";
	echo "</tr><tr>";
	echo "<td align=right><b>Fecha legal:</b></td>";
	echo "<td align=left><input type=text name=cob_fecha_legal size=10 maxlength=10 value='$fecha_legal'>".link_calendario("cob_fecha_legal")."</td>";
	echo "<td align=right><b>Números de remitos:</b></td>";
	echo "<td align=left><input type=text name=cob_nro_remitos value='$nro_remitos'></td>";
	echo "</tr><tr>";
	echo "<td align=right colspan=3><b>Aceptación definitiva lista:</b></td>";
	echo "<td align=left>";
	echo "<select name=cob_aceptacion_definitiva>";
	echo "<option value='t'";
	if ($aceptacion_definitiva == "t") echo " selected";
	echo ">Sí\n";
	echo "<option value='f'";
	if ($aceptacion_definitiva == "f") echo " selected";
	echo ">No\n";
	echo "</select>";
	echo "</td>";
	echo "</tr></table>";
    echo "</td></tr>\n";
    ?>
    <script>
       function habilitar_factoring(object,tabla){
              if (object.options[object.selectedIndex].text=="Vendida")
                            Mostrar(tabla);
                            else
                            Ocultar(tabla);
       }//de la funcion
    </script>
    <?
    $sql="select id_estado_cobranza,estado_cobranzas.nombre,factoring.nombre as nombre_factoring,factoring.id_factoring ,fecha
             from
             licitaciones.historial_estados_cobranzas join licitaciones.estado_cobranzas using (id_estado_cobranza)
             left join licitaciones.factoring using(id_factoring)
             where id_cobranza=$id_cobranza and historial_estados_cobranzas.activo=1 ";
    $res=sql($sql) or fin_pagina();
    $estado_actual=$res->fields["nombre"];
    $nombre_factoring=$res->fields["nombre_factoring"];
    if (!$estado_actual) $estado_actual=" No Hay ningun estado";
    $sql=" select * from estado_cobranzas where activo=1";
    $estado_cobranzas=sql($sql) or fin_pagina();
    //Estado Cobranzas
    echo "<tr><td >";
    echo "<table width=90% border=0>";
    echo  "<tr><td align=left><b>Log Estado</b>&nbsp; ";
    echo  "<input type=checkbox class='estilos_check' name='chk_log' onclick='javascript:(this.checked)?Mostrar(\"tabla_detalles_log_$contador_facturas\"):Ocultar(\"tabla_detalles_log_$contador_facturas\");'>";
    echo  "</td></tr>";
    echo "</table></td></tr>";
    echo "<tr><td align=center>";
    echo "<div id='tabla_detalles_log_$contador_facturas' style='display:none'>";
    mostrar_log_estados($id_cobranza);
    echo "</div>";
    echo "</td></tr>";
    echo "<tr><td><table width=100% align=left >";
    echo "<tr>";
    echo "<td width=40% align=left>";
    echo "<b>Estado Actual: &nbsp;&nbsp;<font color=red size=2>$estado_actual</font></b>";
    echo "</td>";
    echo "<td  align=left width=20%><b>Cambiar Estado</b> </td>";
    echo "<td>";
     echo "<select name=estado_cobranzas onchange=\"habilitar_factoring(this,'tabla_factoring_$contador_facturas')\">";
             echo "<option value='-1'></option>";
             for($i=0;$i<$estado_cobranzas->recordcount();$i++){
                 $nombre_estado=$estado_cobranzas->fields["nombre"];
                 $id_estado_cobranzas=$estado_cobranzas->fields["id_estado_cobranza"];
                 echo "<option  value='$id_estado_cobranzas'> ".$nombre_estado."</option>";
                $estado_cobranzas->movenext();
             }
         echo "</select>";
      echo "</td>";
      echo "</tr>";
    echo "</table></td></tr>";
     //fin del estado de las cobranzas
    //Aca va la parte de los FACTORIN
   if ($estado_actual=="Vendida")
			$det_visib = "block";
			else
			$det_visib = "none";
    $sql=" select * from licitaciones.factoring where activo=1";
    $factoring=sql($sql) or fin_pagina();
    echo "<tr><td>";
    echo "<div id='tabla_factoring_$contador_facturas' style='display:$det_visib'>";
    echo "<table width=100% align=left >";
    echo "<tr>";
    echo "<td width=40%><b>Factoring:&nbsp;&nbsp;<font color=red size=2>$nombre_factoring</font></b></td>";
    echo "<td  align=left width=20%><b>Cambiar Factoring</b></td>";
    echo "<td>";
    echo "<select name=factoring>";
         echo "<option value='-1'></option>";
             for($i=0;$i<$factoring->recordcount();$i++){
                  $nombre=$factoring->fields["nombre"];
                  $id_factoring=$factoring->fields["id_factoring"];
                  if ($id_factoring==$id_factoring_guardado)
                                     $selected=" selected";
                                     else
                                     $selected=" ";
                   echo "<option $selected value='$id_factoring'>$nombre</option>";
              $factoring->movenext();
             }
         echo "</select>";
      echo "</td>";
    echo "</table></div></td></tr>";
    //Aca finaliza la parte de los FACTORIN
	echo "<td align=center  style=\"border:$bgcolor2\"><br>";
		if ($estado != "FINALIZADA") {
			echo "<input type=hidden name=cob_atadas value='atadas'>";
			echo "<input type=hidden name=cob_id_cobranza value='$id_cobranza'>";
			echo "<input type=hidden name=cob_id_factura value='$id_factura'>";
                        echo "<input type=hidden name=cob_guardar_factura value='56'>";
                        $link=encode_link("lic_llamadas.php",array("id_cobranza"=>$id_cobranza,"script"=>"window.opener.cerrar_ventana_$contador_facturas();"));
                        ?>
                        <script>
                        function cerrar_ventana_<?=$contador_facturas?>(){
                          document.form<?=$contador_facturas?>.cob_guardar_factura.value='Guardar';
                          document.form<?=$contador_facturas?>.submit();
                        return true;
                        }
                         function cargar_llamadas_<?=$contador_facturas?>(){
                        //alert(document.form<?=$contador_facturas?>.cob_guardar_factura.value);
                          var error=0;
                          var fecha_presentacion,fecha_estimativa;
                           fecha_presentacion=eval(document.form<?=$contador_facturas?>.cob_fecha_presentacion);
                           fecha_estimativa=eval(document.form<?=$contador_facturas?>.cob_fecha_estimativa);
                           if (fecha_presentacion.value!="" && fecha_estimativa.value=="") {
                                error=1;
                                alert('Si ingresa Fecha de Presentacion, Debe ingresar Fecha Estimativa');
                                }
                           if (document.form<?=$contador_facturas?>.estado_cobranzas.options[document.form<?=$contador_facturas?>.estado_cobranzas.selectedIndex].text=='Vendida' &&   document.form<?=$contador_facturas?>.factoring.options[document.form<?=$contador_facturas?>.factoring.selectedIndex].value==-1)
                              {
                              error=1;
                              alert('Debe elegir un Factoring');
                              }
                            if (document.form<?=$contador_facturas?>.estado_cobranzas.options[document.form<?=$contador_facturas?>.estado_cobranzas.selectedIndex].text=='Presentada' &&   fecha_presentacion.value=="")
                                 {
                                 error=1;
                                 alert('Si elegi el estado Presentada debe tener fecha de presentación');
                                 }
                          if (fecha_estimativa.value!="" && fecha_presentacion.value=="") {
                                error=1;
                                alert('Si ingresa Fecha Estimativa, Debe ingresar Fecha Presentacion');
                                }
                        if (error==0)
                            wventana=window.open('<?=$link?>','','left=40,top=80,width=700,height=350,resizable=1');
                         }//de la function
                        </script>
                <?
			//echo "<input type=submit name=cob_guardar_factura value='Guardar' style='width:160;'>&nbsp;&nbsp;&nbsp;";
        echo "<input type=button name=cob_guardar_factura_button value='Guardar' style='width:160;' onclick='cargar_llamadas_$contador_facturas()'>&nbsp;&nbsp;&nbsp;";
		echo "<input type=reset name=cob_reset value='Deshacer' style='width:160;'>&nbsp;&nbsp;&nbsp;";
   	    }
		echo "</td></tr>";
		echo "</table></div>";
        echo "</td></tr>";
		echo "</table></form>";
	}
    if($id_ingreso_egreso !=NULL || $id_ingreso_egreso!="") {
	     mostrar_ingresos($id_cob,0,$arr,$control_estado);
	     $sql_egresos="select * from licitaciones.detalle_egresos where id_cobranza=$id_cob and  control_egreso=1";
		 $res_eg=sql($sql_egresos) or fin_pagina();
	     if ($res_eg->recordCount() > 0) $flag=1;
	       else $flag=0;
	     mostrar_egresos($id_cob,$flag,$control_estado);
        }
	    elseif($id_vta_atada !=NULL || $id_vta_atada!="") {
	      mostrar_ingresos_atadas($id_cob);
	      mostrar_egresos_atadas($id_cob);
	    }
	    echo "<form action='".$_SERVER["PHP_SELF"]."' method=post name=form_comentarios>";
		echo "<input type=hidden name=cob_id_cobranza value='$arr[0]'>";
		echo "<input type=hidden name=cob_id_lic value='$id_licitacion'>";
		echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
		echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		echo "<tr>";
		echo "<td align=left colspan=2>";
		gestiones_comentarios($id_cob,"COBRANZAS",1);
		echo "</td>";
		echo "</tr><tr>";
		if ($estado == "FINALIZADA") {
			echo "<td align=center colspan=2><b><font size=4 color=#ff0000>Finalizada por $fin_nombre el $fin_fecha a las $fin_hora</font></b><br>";
			echo "</tr><tr>";
		}
	echo "<td align=center colspan=2 style=\"border:$bgcolor2\"><br>";
	if ($estado != "FINALIZADA") {
		echo "<input type=hidden name=id_comentario value='$id_cob'>";
		echo "<input type=hidden name=cob_atadas value='atadas'>";
        echo "<input type=hidden name=comentarios value=''>";
        $link=encode_link("lic_llamadas.php",array("id_cobranza"=>$id_cob,"script"=>"window.opener.cerrar_ventana_guardar_comentarios();"));
        ?>
        <script>
        function cerrar_ventana_guardar_comentarios(){
        document.form_comentarios.comentarios.value='Guardar Comentario';
        document.form_comentarios.submit();
        return true;
        }
         function cargar_llamadas_guardar_comentarios(){
         wventana=window.open('<?=$link?>','','left=5,top=5,width=900,height=700,resizable=1');
         }//de la function
        </script>
        <?
		echo "<input type=button name=button_comentarios value='Guardar Comentario' style='width:160;' onclick='cargar_llamadas_guardar_comentarios();'>&nbsp;&nbsp;&nbsp;";
		if (permisos_check("inicio","desatar_facturas")) {
			echo "<input type=submit name=desatar value='Desatar Facturas' style='width:160;' onClick=\"return confirm('ADVERTENCIA: Se va a desatar estas facturas!');\">&nbsp;&nbsp;&nbsp;";
		}
		if ($monto_atadas==0 )  { //en el caso que se ate una factura y su nota de credito
		    $fin=" ";
		    $fin_eg="disabled";
		    $des_vtafact="disabled";
		    //deshabilita los botones de ingreso y egreso y venta factura
		    for ($ind=0; $ind < $contador_facturas; $ind ++) {?>
		     <script>
		         if(typeof (document.all.registrar_ingreso_<?=$ind?>) != 'undefined' )
		             document.all.registrar_ingreso_<?=$ind?>.disabled=true;
		     </script>
		    <? }
		}
		else {
		if ($contador_facturas-$contador_anuladas > $cont_ingresos  ) {  //se registraron ingresos para todas las facturas
            $des_ingreso= " ";  //registrar ingreso
		}
         else {
            $des_ingreso='disabled';
              }
        if ($contador_facturas-$contador_anuladas==0) $anuladas=1; //todas anuladas
         else $anuladas=0;
		if($id_ingreso_egreso!=NULL || $id_ingreso_egreso!="") {
         if ($res_eg->RecordCount() > 0 || $anuladas==1 || $des_ingreso ==" " ) {
		   $fin_eg="disabled";
		}
		else $fin_eg="";
		}

		if ($id_vta_atada != NULL || $id_vta_atada!="") {
		$sql="select id_vta_atada from venta_fac_atadas
		      where id_vta_atada=$id_vta_atada and ctrl_ingreso=1 and ctrl_egreso=1";
		$res=sql($sql,"$sql") or fin_pagina();
		if ($res->RecordCount() > 0) $fin=" ";
		 else $fin=" disabled";
		}
		else {
	    //para finalizar el seguimiento
		if ($des_ingreso =='disabled' && $fin_eg =='disabled') {
		  $fin=" ";
		}
		else $fin=" disabled";
		}
		}
		//$link=encode_link('elegir_caja_egresos.php',array("id_cobranza"=>$id_cobranza,"id_cob"=>$id_cob,"moneda_factura"=>$moneda_prim,"id_cliente"=>$id_cliente));
		$link=encode_link('elegir_caja.php',array("id_cob"=>$id_cob,"moneda_factura"=>$id_moneda));
        $onclick_eg="window.open(\"$link\",\"\",\"\")";
        echo "<input type=button name=egresos value='Ingresos/Egresos' style='width:120;' $fin_eg  onClick='$onclick_eg'>&nbsp;&nbsp;&nbsp;";
		if (permisos_check("inicio","lic_cobranzas_finalizar")) {
                        //este no anda bien
                        echo $cmd;
           echo "<input type=submit name=cob_finalizar_factura value='Finalizar' style='width:120;' $fin   onClick=\"return confirm('ADVERTENCIA: Se va a finalizar esta factura?!');\">&nbsp;&nbsp;&nbsp;";
		}
	}
	$link_fin=encode_link('finalizar_sin_ing_eg.php',array("id_cobranza"=>$id_cob,"cob_atadas"=>1));
    if ($estado != "FINALIZADA") {
	  if (permisos_check("inicio","permiso_fin_sin_ing_eg") && $cont_ingresos == 0 ) {
	      echo "&nbsp;<input type='button' name='finalizar_sin_ing_eg' value='Finalizar sin Ingresos/Egresos' onClick=\"window.open('$link_fin','','toolbar=0,location=0,directories=0,resizable=1,status=1,menubar=0,scrollbars=1,left=0,top=0,width=950,height=450');\">&nbsp;&nbsp;";
      }
     }
	echo "<input type=button name=volver style='width:120;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
	echo "<br><br></td>";
	echo "</tr>";
//pendientes atadas
    if ($cont_ingresos > 0 )
        $des_vtafact=" disabled";
    else $des_vtafact=" ";
  if ($estado != "FINALIZADA") {
	$link=encode_link('venta_factura_atadas.php',array("id_cobranza"=>$id_cob,"monto_factura"=>"$monto","moneda_factura"=>$id_moneda,"monto_original"=>$monto_atadas,"simbolo_factura"=>$simbolo_moneda,"atadas"=>comprimir_variable($arr)));
    $onclick="window.open(\"$link\",\"\",\"\")";
	echo "<tr><td><input type='button' name='venta_factura' value='Venta de Factura' $des_vtafact onclick='$onclick'>";
  }
	echo "</table></form>";
}
function detalle_atadas($id_cob,$ata) {
	global $html_header,$bgcolor2,$bgcolor3,$permisos,$_ses_user,$sino,$datos_barra,$html_root;
	echo $html_header;
    generar_barra_nav($datos_barra);
	$arr[]=$id_cob;
	$contador_facturas = 0;
	$control_estado="";
	while ($arr1=$ata->fetchrow())
		$arr[]=$arr1["id_secundario"];
	while (list($key,$id_cobranza)=each($arr)) {
		$contador_facturas++;
		$sql = "SELECT id_cobranza,nro_carpeta,facturas.id_factura,cobranzas.nro_factura as nro_factura,cobranzas.monto,cobranzas.fecha_factura,id_ingreso_egreso,cobranzas.cotizacion_dolar,id_vta_atada,";
		$sql .=" case when entidad.id_entidad is null then facturas.cliente ";
		$sql .=" else entidad.nombre end as nombre_entidad,aceptacion_definitiva,";
		$sql .= "cobranzas.id_moneda,fecha_legal,fecha_estimativa,fecha_presentacion,";
		$sql .= "entidad.id_entidad,ingreso_egreso.fecha_creacion,";
		$sql .= "cobranzas.id_licitacion,cobranzas.nombre as nombre_cobranzas,cobranzas.estado,";
		$sql .= "fin_usuario,fin_fecha,estado_nombre,nro_remitos ";
		$sql .= "FROM ((cobranzas LEFT JOIN moneda USING (id_moneda)) ";
		$sql .= "LEFT JOIN entidad USING (id_entidad)) LEFT JOIN facturas USING (id_factura)";
		$sql .= "LEFT JOIN caja.ingreso_egreso using (id_ingreso_egreso)";
		$sql .= "WHERE id_cobranza=$id_cobranza ";
  	    $result = sql($sql) or fin_pagina();
		if (!$result->RecordCount()) {
			Error("No se encontro la factura"); die;
		}
		$nro_carpeta = $result->fields["nro_carpeta"];
		$nro_remitos = $result->fields["nro_remitos"];
		$nombre = $result->fields["nombre_cobranzas"];
		$id_factura = $result->fields["id_factura"];
		$nro_factura = $result->fields["nro_factura"];
		$id_moneda = $result->fields["id_moneda"];
		$monto= $result->fields["monto"];
		$fecha_factura = Fecha($result->fields["fecha_factura"]);
		$fecha_legal = Fecha($result->fields["fecha_legal"]);
		$fecha_estimativa = Fecha($result->fields["fecha_estimativa"]);
		$fecha_presentacion = Fecha($result->fields["fecha_presentacion"]);
		$id_licitacion = $result->fields["id_licitacion"];
		$id_cliente = $result->fields["id_entidad"];
		$entidad = $result->fields["nombre_entidad"];
		$estado = $result->fields["estado"];
		$fin_nombre = $result->fields["fin_usuario"];
		$fin_fecha = Fecha($result->fields["fin_fecha"]);
		$fin_hora = Hora($result->fields["fin_fecha"]);
		$estado_nombre = $result->fields["estado_nombre"];
		$aceptacion_definitiva = $sino[$result->fields["aceptacion_definitiva"]];
		$id_ingreso_egreso= $result->fields["id_ingreso_egreso"];
		$id_vta_atada= $result->fields["id_vta_atada"];
		$fecha_ingreso=fecha($result->fields['fecha_creacion']);
		$cotizacion_dolar=formato_money($result->fields['cotizacion_dolar']);
		cargar_calendario();
		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>";
		echo "<input type=hidden name=cob_id_lic value='$id_licitacion'>";
		echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
		echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=+1>Modificar Factura</font></td></tr>";
		echo "<tr><td colspan=2>";
		if ($contador_facturas == 1) {
			$det_visib = "block";
			$det_check = "checked";
		}
		else {
			$det_visib = "none";
			$det_check = "";
		}
		echo "<table border=0 width=100%>";
		echo "<tr><td>";
		echo "<b>Tipo:</b> ".strtoupper($tipo_factura);
		echo "</td><td>";
		echo "<b>Número:</b>".$nro_factura;
		echo "</td><td>";
		echo "<b>Monto:</b> ".$simbolo_moneda." ".formato_money($monto);
		echo "</td><td align=right>";
		echo "<b>Mostrar Detalles:</b> <input type=checkbox name=det $det_check onclick='javascript:(this.checked)?Mostrar(\"tabla_detalles_$contador_facturas\"):Ocultar(\"tabla_detalles_$contador_facturas\");'>";
		echo "</td></tr></table>";
		echo "</td></tr>";
		echo "<tr><td align=center colspan=2>";
		echo "<div id='tabla_detalles_$contador_facturas' style='display:$det_visib'>";
		echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";
		echo "<tr><td align=center>";
		if (es_numero($id_licitacion))
			echo "<a style='cursor: hand;' onclick='window.location=\"".encode_link("licitaciones_view.php",array("ID"=>$id_licitacion,"cmd1"=>"detalle"))."\";'>"."<b>ID Licitación:</b> $id_licitacion";
		else
			echo "<b>ID Licitación:</b><font color=red> Sin Licitacion</font>";
		echo "</td></tr>";
		echo " <tr><td align=left>";
//incluyo la funcion que verifica si hay contactos
        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td align='left'>";
        $nuevo_contacto=encode_link("../contactos_generales/contactos.php",array("modulo"=>"Cobranzas",
                                    "id_licitaciones"=>$id_licitacion,
                                    "id_general"=>$id_cliente));
        echo " <b>Cliente:</b> $entidad </td>";
        echo " <td align='right'>";

  	     if (es_numero($id_licitacion))
				echo  "<input type='button' name='Nuevo' Value='Nuevo Contacto'  onclick=\"window.open('$nuevo_contacto','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align='right' colspan=2>";
        if ($id_cliente!=null || $id_cliente != "")
           contactos_existentes("Cobranzas",$id_cliente);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</td>";
        echo "</tr><tr>";
		echo "<td align=left colspan=2><b>Detalles de la factura:</b><br>";
		echo "<table width=100% border=0><tr>";
		if ($control_estado=="") $control_estado=$estado_factura;
		/*if ($id_ingreso_egreso != "") {
          $sql_dist="select id_distrito from caja.ingreso_egreso
                   join caja.caja using (id_caja) where id_ingreso_egreso=$id_ingreso_egreso";
          $res_dist=sql($sql_dist) or fin_pagina();
					$distrito=$res_dist->fields['id_distrito'];
          $archivo="../caja/egresos_bsas.php";
          $link_caja=encode_link($archivo,array("id_ingreso_egreso"=> $id_ingreso_egreso,"pagina"=>"ingreso","pagina_viene"=>'lic_cobranzas',"id_cobranza"=>$id_cobranza,"id_cob"=>$id_cob,"distrito"=>$distrito));
          $onclick_caja="window.open(\"$link_caja\",\"\",\"\")";
        $onclick_caja="window.open(\"$link_caja\",\"\",\"\")";
		echo "<td align=right><b>ID Ingreso:</b></td>";
		echo "<td align=left><a style='cursor: hand;' onclick='$onclick_caja'> $id_ingreso_egreso</a></td>";
		echo "<td align=right><b>Fecha Ingreso:</b></td>";
		echo "<td align=left>$fecha_ingreso";
		     if ($cotizacion_dolar !='0,00') echo "<b> &nbsp;Dolar:</b> $cotizacion_dolar";
		 echo "  </td></tr><tr>";
		}*/
		echo "<td align=right><b>Nombre:</b></td>";
		echo "<td align=left>$nombre</td>";
		echo "<td align=right>&nbsp;</td>";
		echo "<td align=left>&nbsp;</td>";
		echo "</tr><tr>";
		echo "<td align=right><b><font color=#ff0000>*</font>Fecha facturación:</b></td>";
		echo "<td align=left>$fecha_factura</td>";
		echo "<td align=right><b><font color=#ff0000>*</font>Número de factura:</b></td>";
		echo "<td align=left>$nro_factura&nbsp;";
		echo "<input type=button name='boton_ir' value='Ir' onclick='window.location=\""
			.encode_link($html_root."/modulos/facturas/factura_nueva.php",array("modulo"=>"facturas","id_factura"=>$id_factura))."\";'>";
		echo "</td>";
		echo "</tr><tr>";
		echo "<td align=right><b>Fecha presentación:</b></td>";
		echo "<td align=left>$fecha_presentacion</td>";
		echo "<td align=right><b><font color=#ff0000>*</font>Monto:</b></td>";
		echo "<td align=left>";
		$sql = "SELECT id_moneda, simbolo FROM moneda where id_moneda=$id_moneda";
		$result1 = sql($sql) or fin_pagina();
		echo $result1->fields["simbolo"];
		echo "&nbsp;".formato_money($monto);
		echo "</td>";
		echo "</tr><tr>";
		echo "<td align=right><b>Fecha estimativa:</b></td>";
		echo "<td align=left>$fecha_estimativa</td>";
		echo "<td align=right><b>Carpeta Número:</b></td>";
		echo "<td align=left>$nro_carpeta</td>";
		echo "</tr><tr>";
		echo "<td align=right><b>Fecha legal:</b></td>";
		echo "<td align=left>$fecha_legal</td>";
		echo "<td align=right><b>Números de remitos:</b></td>";
		echo "<td align=left>$nro_remitos</td>";
		echo "</tr><tr>";
		echo "<td align=right colspan=3><b>Aceptación definitiva lista:</b></td>";
		echo "<td align=left>$aceptacion_definitiva</td>";
		echo "</tr></table></td></tr>";
		echo "</table></div></td></tr>";
		echo "</table></form>";
	}
	if ($id_datos_ingreso!=null || $id_datos_ingreso !="")

	$flag=1;
	else $flag=0;

	if($id_vta_atada !=NULL || $id_vta_atada!="") {
	      mostrar_ingresos_atadas($id_cob);
	      mostrar_egresos_atadas($id_cob);
	  }
	  else {
	    mostrar_ingresos($id_cob,$flag,$arr,$control_estado);
	    mostrar_egresos($id_cob,1,$control_estado);
	  }
		echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>";
        echo "<input type=hidden name='finalizada_id_cobranza' value='$id_cob'>";
		echo "<input type=hidden name=cob_id_lic value='$id_licitacion'>";
		echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
		echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		echo "<tr>";
		echo "<td align=left colspan=2>";
		gestiones_comentarios($id_cob,"COBRANZAS",1);
		echo "</td>";
		echo "</tr>";
		$sql="select comentario from comentarios_finalizar where id_cobranza=$id_cob";
	    $res=sql($sql,"comentarios") or fin_pagina();
	    if ($res->fields['comentario'] != "" || $res->fields['comentario'] !=null ) {
		$comentario=$res->fields['comentario'];
		$nro=row_count($comentario,120);
        if ($nro >10) $nro=10;
        echo "<tr><td colspan=2><b>Comentarios al finalizar la factura:<br></b><textarea name='comentario_finalizar' readonly style='width=100%' rows='$nro'>$comentario</textarea>";
	    echo "</tr>";
     	}
		echo "<tr>";
		if ($estado == "FINALIZADA") {
			echo "<td align=center colspan=2><b><font size=4 color=#ff0000>Finalizada por $fin_nombre el $fin_fecha a las $fin_hora</font></b><br>";
			echo "</tr><tr>";
		}
	echo "<td align=center colspan=2 style=\"border:$bgcolor2\"><br>";
    echo "<input type=submit name='comentarios_finalizadas' value='Guardar Comentarios'>";
	echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
	echo "<br><br></td>";
	echo "</tr>";
	echo "</table></form>";
}
function datos_factura($id_factura) {
	global $db;
	$q= "select facturas.id_factura,moneda.*,id_licitacion,tipo_factura,nro_factura,nro_remito,fecha_factura,cliente,estado,";
	$q.="(select sum(cant_prod*precio) from items_factura where id_factura=$id_factura) as monto ";
	$q.="from facturas join moneda on moneda.id_moneda=facturas.id_moneda where id_factura=$id_factura";
//	$r=sql($q) or die;
	$r= $db->Execute($q) or die($q);
	if ($r->RecordCount())
	{
  	   $d=$r->fetchrow();
 	   $d[monto]=($d[monto])?$d[monto]:0;
 	   $d[fecha_factura]=Fecha($d[fecha_factura]);
		return $d;
	}
	else
		return false;
}

function detalle_factura ($id_cobranza) {
	global $html_header,$bgcolor2,$bgcolor3,$sino,$html_root;
	echo $html_header;

	//CUANDO SE CARGUEN BIEN LOS DATOS DE LAS FACTURAS SE DEBE HACER EL JOIN
	//POR ID_FACTURA Y NO POR NRO_FATURA


	$sql = "SELECT id_cobranza,nro_carpeta,facturas.id_factura,facturas.tipo_factura,
	         cobranzas.nro_factura as nro_factura,cobranzas.monto,cobranzas.fecha_factura,
	         id_ingreso_egreso,cobranzas.cotizacion_dolar,id_datos_ingreso,";
	$sql .=" case when entidad.id_entidad is null then facturas.cliente ";
	$sql .=" else entidad.nombre end as nombre_entidad,aceptacion_definitiva,";
	$sql .= "cobranzas.id_moneda,fecha_legal,fecha_estimativa,fecha_presentacion,";
	$sql .= "entidad.id_entidad,ingreso_egreso.fecha_creacion,";
	$sql .= "cobranzas.id_licitacion,cobranzas.nombre as nombre_cobranzas,cobranzas.estado,";
	$sql .= "fin_usuario,fin_fecha,estado_nombre,nro_remitos ";
	$sql .= "FROM ((cobranzas LEFT JOIN moneda USING (id_moneda)) ";
	$sql .= "LEFT JOIN entidad USING (id_entidad)) LEFT JOIN facturas USING (id_factura)";
	$sql .= "LEFT JOIN caja.ingreso_egreso using (id_ingreso_egreso)";
	$sql .= "WHERE id_cobranza=$id_cobranza ";
	$result = sql($sql) or fin_pagina();
	if ($result->RecordCount() == 1) {
		$nro_carpeta = $result->fields["nro_carpeta"];
		$nro_remitos = $result->fields["nro_remitos"];
		$nombre = $result->fields["nombre_cobranzas"];
		$id_factura = $result->fields["id_factura"];
		$nro_factura = $result->fields["nro_factura"];
		$tipo_factura = $result->fields["tipo_factura"];
		$id_moneda = $result->fields["id_moneda"];
		$monto= $result->fields["monto"];
		$fecha_factura = Fecha($result->fields["fecha_factura"]);
		$fecha_legal = Fecha($result->fields["fecha_legal"]);
		$fecha_estimativa = Fecha($result->fields["fecha_estimativa"]);
		$fecha_presentacion = Fecha($result->fields["fecha_presentacion"]);
		$id_licitacion = $result->fields["id_licitacion"] or $id_licitacion="<font color=red>Sin Licitacion</font>";
		$id_cliente = $result->fields["id_entidad"];
		$entidad = $result->fields["nombre_entidad"];
		$estado = $result->fields["estado"];
		$fin_nombre = $result->fields["fin_usuario"];
		$fin_fecha = Fecha($result->fields["fin_fecha"]);
		$fin_hora = Hora($result->fields["fin_fecha"]);
		$aceptacion_definitiva = $sino[$result->fields["aceptacion_definitiva"]];
		$id_ingreso_egreso= $result->fields["id_ingreso_egreso"];
		$id_datos_ingreso= $result->fields["id_datos_ingreso"];
		$fecha_ingreso=fecha($result->fields["fecha_creacion"]);
		$cotizacion_dolar=formato_money($result->fields["cotizacion_dolar"]);
	}

	else { Error("No se encontro la factura"); die; }
	cargar_calendario();
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>";
//	echo "<input type=hidden name=cob_id_lic value='$id_licitacion'>";
//	echo "<input type=hidden name=cob_id_entidad value='$id_cliente'>";
    echo "<input type=hidden name=finalizada_id_cobranza value='$id_cobranza'>";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=+1>Detalle de la Factura</font></td></tr>";
	echo "<tr><td align=center>";
	if (es_numero($id_licitacion))
		echo "<a style='cursor: hand;' onclick='window.location=\"".encode_link("licitaciones_view.php",array("ID"=>$id_licitacion,"cmd1"=>"detalle"))."\";'>"."<b>ID Licitación:</b> $id_licitacion";
 	    else
		echo "<b>ID Licitación:</b> $id_licitacion";
		
		echo " <tr><td align=left>";
//incluyo la funcion que verifica si hay contactos
        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td align='left'>";
        $nuevo_contacto=encode_link("../contactos_generales/contactos.php",array("modulo"=>"Cobranzas",
                                    "id_licitaciones"=>$id_licitacion,
                                    "id_general"=>$id_cliente));
        echo " <b>Cliente:</b> $entidad </td>";
        echo " <td align='right'> ";
        if (es_numero($id_licitacion))
				echo "<input type='button' name='Nuevo' Value='Nuevo Contacto'  onclick=\"window.open('$nuevo_contacto','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,left=25,top=10,width=750,height=550');\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align='right' colspan=2>";
        if ($id_cliente!=null || $id_cliente != "")
              contactos_existentes("Cobranzas",$id_cliente);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</td>";
   	    //echo "<td align=left><b>Cliente:</b> $entidad</td>";
  	    echo "</tr><tr>";
 	    echo "<td align=left colspan=2><b>Detalles de la factura:</b><br>";
     	echo "<table width=100% border=0><tr>";
  	    echo "<td align=right><b>Nombre:</b></td>";
 	    echo "<td align=left colspan=3>$nombre&nbsp;</td>";
 	    echo "</tr><tr>";
        echo "<td align=right><b>Fecha facturación:</b></td>";
    	echo "<td align=left>$fecha_factura&nbsp;</td>";
    	echo "<td align=right><b>Número de factura:</b></td>";
    	echo "<td align=left>$nro_factura&nbsp;";
    	echo "<input type=button name='boton_ir' value='Ir' onclick='window.location=\""
		.encode_link($html_root."/modulos/facturas/factura_nueva.php",array("modulo"=>"facturas","id_factura"=>$id_factura))."\";'>";
    	echo "</td>";
    	echo "</tr><tr>";
    	echo "<td align=right><b>Fecha presentación:</b></td>";
    	echo "<td align=left>$fecha_presentacion&nbsp;</td>";
    	echo "<td align=right><b>Monto:</b></td>";
    	echo "<td align=left>".$result->fields["simbolo"]."&nbsp;";
    	echo formato_money($monto)."&nbsp;</td>";
     	echo "</tr><tr>";
    	echo "<td align=right><b>Fecha estimativa:</b></td>";
    	echo "<td align=left>$fecha_estimativa&nbsp;</td>";
  	    echo "<td align=right><b>Carpeta Número:</b></td>";
        echo "<td align=left>$nro_carpeta&nbsp;</td>";
    	echo "</tr><tr>";
    	echo "<td align=right><b>Fecha legal:</b></td>";
		echo "<td align=left>$fecha_legal&nbsp;</td>";
		echo "<td align=right><b>Números remitos:</b></td>";
		echo "<td align=left>$nro_remitos&nbsp;</td>";
		echo "</tr><tr>";
		echo "<td align=right colspan=3><b>Aceptación definitiva lista:</b></td>";
		echo "<td align=left>$aceptacion_definitiva</td>";
		echo "</tr></table>";
		echo "</td>";
		echo "</tr>";
 	    echo "<tr><td align=center>\n";
	if ($id_datos_ingreso!=null || $id_datos_ingreso !="") {
	  mostrar_ingresos($id_cobranza,1);
	  mostrar_egresos($id_cobranza,1,"",$id_datos_ingreso);
	}
	 else {
	   mostrar_ingresos($id_cobranza,0);
	   mostrar_egresos($id_cobranza,1);
	 }
	echo "</td>";
	echo "<tr>";
	echo "<td align=left colspan=2>";
	gestiones_comentarios($id_cobranza,"COBRANZAS",1);
	echo "</td></tr>";
	$sql="select comentario from comentarios_finalizar where id_cobranza=$id_cobranza";
	$res=sql($sql,"comentarios") or fin_pagina();
    if ($res->fields['comentario'] != "" || $res->fields['comentario'] !=null ) {
		$comentario=$res->fields['comentario'];
		$nro=row_count($comentario,120);
        if ($nro >10) $nro=10;
	    echo "<tr><td colspan=2><b>Comentarios al finalizar la factura:<br></b><textarea name='comentario_finalizar' style='width=100%' readonly rows='$nro'>$comentario</textarea>";
	    echo "</tr>";
    }
	echo "<tr>";
	if ($estado == "FINALIZADA") {
		echo "<td align=center colspan=2><b><font size=4 color=#ff0000>Finalizada por $fin_nombre el $fin_fecha a las $fin_hora</font></b><br>";
		echo "</tr><tr>";
	}
	echo "<td align=center colspan=2 style=\"border:$bgcolor2\"><br>";
	if ($estado != "FINALIZADA") {
		echo "<input type=hidden name=cob_id_cobranza value='$id_cobranza'>";
		echo "<input type=submit name=cob_modificar_factura value='Modificar' style='width:160;'>&nbsp;&nbsp;&nbsp;";
		echo "<input type=submit name=cob_finalizar_factura value='Finalizar' style='width:160;' onClick=\"return confirm('ADVERTENCIA: Se va a finalizar esta factura!');\">&nbsp;&nbsp;&nbsp;";
//		echo "<input type=reset name=cob_reset value='Deshacer' style='width:160;'>&nbsp;&nbsp;&nbsp;";
	}
    echo "<input type=submit name='comentarios_finalizadas' value = 'Guardar Comentario' >";
	echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".$_SERVER["PHP_SELF"]."';\">";
	echo "<br><br></td>";
	echo "</tr>";
	echo "</table></form>";
}
if (!$download) fin_pagina();
?>