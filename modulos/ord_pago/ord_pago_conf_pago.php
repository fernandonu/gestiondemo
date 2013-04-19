<?php
/*
$Author: nazabal $
$Revision: 1.9 $
$Date: 2007/06/28 21:47:28 $
*/
include("../caja/func.php");
require_once("../../config.php");
require_once("fns.php");


//obtengo todos los pagos de la orden de pago

$nro_orden=$parametros["nro_orden"];
$valor_dolar=$parametros['valor_dolar'];
$simbolo=$parametros['simbolo'];
$id_distrito=$parametros["id_distrito"];
$nro_cuenta=$parametros['cuentas'];
$Fecha_Vencimiento=$parametros['fecha_v'];
$Fecha_Vencimiento_old=$parametros['fecha_v_old'];
$id_imputacion=$parametros["id_imputacion"] or $id_imputacion=$_POST["id_imputacion"];

$valores_imputacion=$parametros["valores_imputacion"];

if ($valor_dolar) $moneda=1;
$fecha=date("Y-m-d H:i:s",mktime());

//echo phpinfo();die();
if ($_POST["aceptar"]=="Aceptar")
{
$db->StartTrans();
  //controlamos si la orden es de un pago multiple
  //en cuyo caso, actualizamos los estados de todas las ordenes
  //que estan en el pago multiple, junto a $nro_orden
  $ordenes_atadas=PM_ordenes($nro_orden);
  $cant_ordenes=sizeof($ordenes_atadas);

  //esto lo hago por si es pago de servicio tecnico

  pagar_casos($ordenes_atadas);
  //fin de la logica de casos
  switch($parametros["pagina_pago"]) {
         case "efectivo":

                       //$comentarios=$parametros['comentario_pagos'];
                       $importe=$_POST["text_monto"];
                       if ($moneda) $_POST["text_monto"]=$_POST["monto_mostrar"];

                       //falta recuperar los datos de depositos en bancos

                       $parametros['pagina_viene']="";//por si viene desde ingresos_egresos
                       $_POST['forcesave']=1;//ya se hizo el control en ingreso_egreso
                       $state_guardar=guardar_ie("egreso",$id_distrito);

                       if ($state_guardar)
                       {

                        $sql="select id_ingreso_egreso from ingreso_egreso order by id_ingreso_egreso";
                        $resultado=sql($sql,10) or die($sql);
                        $resultado->MoveLast();
                        $id_ingreso_egreso=$resultado->fields['id_ingreso_egreso'];

  	                    $datos=array("pagina"=>"efectivo","nro_orden"=>$parametros['nro_orden'],"id_ingreso_egreso"=>$id_ingreso_egreso,"importe"=>$importe,"id_pago"=>$parametros["id_pago"]);

    	                  insertar_pago_orden($parametros,$datos);
                        $pagos_restantes=pagos_restantes($nro_orden);
                        if ($pagos_restantes<=0) {
                                                 $estado='g'; //totalmente pagada
                                                 pasa_nc_utilizada($nro_orden);
                                                 }
                                                 else
                                                 {
                                                 $estado='d'; //parcialmente pagada
                                                 }

                       for($i=0;$i<$cant_ordenes;$i++)
                       {$sql="insert into log_ordenes (nro_orden,user_login,fecha,tipo_log)";
                        $sql.=" values ($ordenes_atadas[$i],'$_ses_user[login]','$fecha','Pago orden de pago')";
                        sql($sql,2) or die($sql);
                       }

                       for($i=0;$i<$cant_ordenes;$i++)
                       {$sql="update orden_de_compra set estado='$estado' where nro_orden=".$ordenes_atadas[$i];
                        $db->execute($sql) or die($sql);
                       }
                       	$link=encode_link("../ord_pago/ord_pago_pagar.php",array("nro_orden"=>$parametros["nro_orden"],"exito"=>1));

                       }
                       break;
         case "cheque":
                      //$comentario_pagos=$parametros['comentario_pagos'];
                      $banco = $_POST['Ingreso_Cheque_Banco'];
                      $proveedor = $_POST['Ingreso_Cheque_Proveedor'];
                      $fecha_e = $_POST['Ingreso_Cheque_Fecha_Emision'];
                      $fecha_v = $_POST['Ingreso_Cheque_Fecha_Vencimiento'];
                      ////////Diego
                      $fecha_v_old = $_POST['Ingreso_Cheque_Fecha_Vencimiento_old'];
                      $fecha_p = $_POST['Ingreso_Cheque_Fecha_Debito'];
                      $numero = $_POST['Ingreso_Cheque_Numero'];
                      $importe = $_POST['Ingreso_Cheque_Importe'];
                      $comentarios = $_POST['Ingreso_Cheque_Comentarios'];
                      $numero_orden = $_POST['numero_de_orden'];
                      $no_a_la_orden = $_POST['no_a_la_orden'];
                      if ($no_a_la_orden != "1") $no_a_la_orden = "0";

                      //agrego el campo de nro_cuenta cuandos e elige un concepto y un
                      //plan para el cheque, lo paso como parametro cuando llamo a
                      //esta pagina
                      $nro_cuenta=$_POST['nro_cuenta'];
                      //echo  "nro-cuenta   ".$nro_cuenta."<br>";

                      list($d,$m,$a) = explode("/",$fecha_e);
                      if (FechaOk($fecha_e)) {                                
                                $fe_db = "$a-$m-$d " . date("H:m:s");
                                }
                         else {
                             Error("La fecha de Emisión ingresada es inválida");
                            }
                      list($d,$m,$a) = explode("/",$fecha_v);
                      if (FechaOk($fecha_v)) {
                                     $fv_db = "$a-$m-$d";
                      }
                      else {
                        Error("La fecha de Vencimiento ingresada es inválida");
                      }
                      if ($fecha_p == "") {
                              $fp_db = "NULL";
                       }
                       else {
                       list($d,$m,$a) = explode("/",$fecha_p);
                        if (FechaOk($fecha_p)) {
                              $fp_db = "'$a-$m-$d'";
                        }
                      else {
                         Error("La fecha de Débito ingresada es inválida");
                         }
                       }
                     if ($proveedor == "") {
                         Error("Falta ingresar el Proveedor");
                       }
                     if ($numero == "") {
                         Error("Falta ingresar el Número del Cheque");
                       }
                     if ($importe == "") {
                         Error("Falta ingresar el Importe");
                      }
                    elseif (!es_numero($importe)) {
                         Error("El Importe ingresado es inválido");
                      }
                       $sql="select númeroch,nombrebanco from tipo_banco join cheques using (idbanco) where";
                       $sql.=" cheques.númeroch=$numero and cheques.idbanco=$banco";
                       $resultado=$db->execute($sql)or die($db->errormsg()."<br>".$sql);
                       $cantidad_cheques=$resultado->RecordCount();

                       if ($cantidad_cheques) Error("Ese número de cheque ya existe");


                    if (!$error) {


                        $sql="update chequera set ultimo_cheque_usado=".$parametros['Ingreso_Cheque_Numero']." where id_chequera=".$parametros['id_chequera'];
                        $resultado=$db->execute($sql)or die($db->errormsg()."<br>".$sql);

                       for($i=0;$i<$cant_ordenes;$i++)
                       {$sql="insert into log_ordenes (nro_orden,user_login,fecha,tipo_log)";
                        $sql.=" values ($ordenes_atadas[$i],'$_ses_user[login]','$fecha','Pago orden de pago')";
                        sql($sql,2) or die($sql);
                       }


                       $sql = "INSERT INTO bancos.cheques ";
                       $sql .= "(IdBanco, FechaEmiCh, FechaVtoCh, FechaPrev, FechaDébCh, NúmeroCh, ImporteCh, IdProv, Comentarios, numero_cuenta,no_a_la_orden) ";
                       $sql .= "VALUES ($banco,'$fe_db','$fv_db',$fp_db,NULL,$numero,$importe,$proveedor,'$comentarios', $nro_cuenta,$no_a_la_orden)";


                       sql($sql,2) or die();
                       $sql = "SELECT id_usuario FROM usuarios WHERE login='".$_ses_user["login"]."'";
                       $resultado=sql($sql) or fin_pagina();
                       $id_usuario=$resultado->fields['id_usuario'];
                       $sql = "select * from bancos.ultimo_cheque_usuario where id_usuario=$id_usuario";
                       $resultado=sql($sql,3) or fin_pagina();
                       $cantidad_usuarios=$resultado->RecordCount();
                       if ($cantidad_usuarios>0){
                                                 //hago update
                                                 $sql="update bancos.ultimo_cheque_usuario set númeroch=$numero , idbanco=$banco, ultima_chequera=".$parametros['id_chequera']." where id_usuario=$id_usuario";
                                                 sql($sql,4) or fin_pagina();

                                                 }
                                                 else
                                                 {
                                                //si no hago insert ya que es nuevo
                                                 $sql="insert into bancos.ultimo_cheque_usuario (númeroch,idbanco,id_usuario,ultima_chequera) values($numero,$banco,$id_usuario,".$parametros['id_chequera'].")";
                                                 sql($sql,4) or fin_pagina();
                                                 }
                         //$datos=array("pagina"=>"cheques","nro_orden"=>$parametros['nro_orden'],"id_banco"=>$banco,"numero_cheque"=>$numero,"importe"=>$importe,"comentarios"=>$comentario_pagos);
                       $datos=array("pagina"=>"cheques","nro_orden"=>$parametros['nro_orden'],"id_banco"=>$banco,"numero_cheque"=>$numero,"importe"=>$importe,"id_pago"=>$parametros['id_pago']);
                       insertar_pago_orden($parametros,$datos);
                       $pagos_restantes=pagos_restantes($nro_orden);
                        if ($pagos_restantes<=0) {
                                                 $estado='g'; //totalmente pagada
                                                 pasa_nc_utilizada($nro_orden);
                                                 }
                                                 else
                                                 {
                                                 $estado='d'; //parcialmente pagada
                                                 }

                       for($i=0;$i<$cant_ordenes;$i++)
                       {$sql="update orden_de_compra set estado='$estado' where nro_orden=".$ordenes_atadas[$i];
                        $db->execute($sql) or die($sql);
                       }

                        $simbolo="$";

           			    $tipo_pago_titulo="Pago con cheque Nº ".$_POST['Ingreso_Cheque_Numero'].", monto: $simbolo ".formato_money($importe);
					    //traemos el nombre del proveedor
					    $query="select razon_social from proveedor where id_proveedor=$proveedor";
    					$prov_n=sql($query) or fin_pagina();

					    //controla la cuenta por default del proveedor, para saber
			 		    //si debe avisar por MAIL que se uso una cuenta que no
					    //es la que el proveedor tiene por default

						cuenta_proveedor_default($proveedor,$nro_cuenta,"$tipo_pago_titulo",$prov_n->fields['razon_social']);
                        if ($fecha_v!=$fecha_v_old)
                           {$sql = "select nombrebanco from bancos.tipo_banco where idbanco=$banco";
                            $resul_banco = sql($sql,"Error al consultar los Bancos") or fin_pagina();
                            $sql = "select razon_social from general.proveedor where id_proveedor=$proveedor";
                            $resul_prov = sql($sql,"Error al consultar los Proveedores") or fin_pagina();
                            $prov_nbre=$resul_prov->fields['razon_social'];
                            $banco_nbre=$resul_banco->fields['nombrebanco'];
                           	$usuario=$_ses_user["name"];
                            $mensaje="La fecha de vencimiento del cheque Nº $numero, del Banco $banco_nbre, correspondiente a la Orden \n";
                            $mensaje.="Nº $numero_orden y al proveedor \"$prov_nbre\", fue cambiada de: $fecha_v_old a: $fecha_v. \n";
                            $mensaje.="El cambio fue realizado por el usuario $usuario.";
                            $para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar";
                            //$para="broggi@coradir.com.ar,marco@coradir.com.ar";
                            //echo $mensaje;die();
                            enviar_mail($para,"Cambio Fecha de Vencimiento del Cheque Nº $numero, del Banco $banco_nbre",$mensaje,' ',' ',' ',0);
                            //enviar_mail("juanmanuel@coradir.com.ar","Cambio Fecha de Cheque Nº $numero, del Banco $banco_nbre",$mensaje,' ',' ',' ',0);
                           }



                           /***********************************************************
						     Llamamos a la funcion de imputar pago
						   ************************************************************/
                           include_once("../contabilidad/funciones.php");
                           $pago[]=array();
						   $pago["tipo_pago"]="númeroch";
						   $pago["id_pago"]=$numero;
						   $pago["id_banco"]=$banco;
						   $id_imputacion=$_POST["id_imputacion"];

						   imputar_pago($pago,$id_imputacion,$fecha_e);

                           $link=encode_link("../ord_pago/ord_pago_pagar.php",array("nro_orden"=>$parametros["nro_orden"],"exito"=>1));

                       } //del if cuando inserta los datos
                      break;
         case "transferencia":
                      //$comentarios=$parametros['comentario_pagos'];
                      $banco = $_POST['Ingreso_Debito_Banco'];
                      $tipo = $_POST['Ingreso_Debito_Tipo'];
                      $fecha_imputacion=$fecha = $_POST['Ingreso_Debito_Fecha'];
                      $importe = $_POST['Ingreso_Debito_Importe'];
                      $nro_cuenta=$_POST['nro_cuenta'];
                      $comentarios=$_POST["comentarios"];
                      //echo  "nro-cuenta   ".$nro_cuenta."<br>";

                      list($d,$m,$a) = explode("/",$fecha);
                      if (FechaOk($fecha)) {
                                 $fecha = "$a-$m-$d";
                                 }
                                 else {
                                     Error("La fecha de débito es inválida");
                                 }
                      if ($tipo == "") {
                                  Error("Falta ingresar el Tipo de Débito");
                                  }
                      if ($importe == "") {
                                  Error("Falta ingresar el Importe del Débito");
                                  }
                                  elseif (!es_numero($importe)) {
                                       Error("El Importe ingresado no es válido");
                                           }
                      if (!$error) {


                               for($i=0;$i<$cant_ordenes;$i++)
                               {$sql="insert into log_ordenes (nro_orden,user_login,fecha,tipo_log)";
                                $sql.=" values ($ordenes_atadas[$i],'$_ses_user[login]','$fecha','Pago orden de pago')";
                                sql($sql,2) or die($sql);
                               }

                               $query="select nextval('débitos_iddébito_seq') as id_debito";
        					   $id_deb=sql($query,"<br>Error al traer id de debito<br>") or fin_pagina();
                               $iddebito=$id_deb->fields["id_debito"];

                               $sql = "INSERT INTO bancos.débitos ";
                               $sql .= "(iddébito,IdBanco, FechaDébito, IdTipoDéb, ImporteDéb, numero_cuenta,comentario) ";
                               $sql .= "VALUES ($iddebito,$banco,'$fecha',$tipo,$importe, $nro_cuenta,'$comentarios')";
                               $result = sql($sql,1) or die();

                               //inserto el log de debitos
                               $user_login=$_ses_user['name'];
						       $fecha_log_debito=date('Y-m-d H:i:s');
						       $tipo_log=1;//alta del debito
						       $sql = "INSERT INTO bancos.log_debitos
						       (iddébito,user_login,fecha,tipo_log,comentario)
						       VALUES ($iddebito,'$user_login','$fecha_log_debito',$tipo_log,'$comentarios')";
						       sql ($sql,"No se puede insertar el log de debitos")or fin_pagina();

                               //ACA INSERTAN LOS PAGOS DE ORDEN DE pago
                               $sql="select * from débitos order by IdDébito";
                               $resultado=sql($sql,2) or die();
                               $resultado->MoveLast();
                               $importe=$_POST["Ingreso_Debito_Importe"];
                               $datos=array("pagina"=>"transferencia",
                                            "nro_orden"=>$parametros['nro_orden'],
                                            "id_debito"=>$resultado->fields[0],
                                            "importe"=>$importe,
                                            "id_pago"=>$parametros['id_pago']);
                               insertar_pago_orden($parametros,$datos);
                               $pagos_restantes=pagos_restantes($nro_orden);
                               if ($pagos_restantes<=0) {
                                                  $estado='g'; //totalmente pagada
                                                  pasa_nc_utilizada($nro_orden);
                                                  }
                                                  else
                                                  {
                                                  $estado='d'; //parcialmente pagada
                                                  }

                              for($i=0;$i<$cant_ordenes;$i++)
                              {$sql="update orden_de_compra set estado='$estado' where nro_orden=".$ordenes_atadas[$i];
                               $db->execute($sql) or die($sql);
                              }

                               /***********************************************************
							     Llamamos a la funcion de imputar pago
							   ************************************************************/
	                           include_once("../contabilidad/funciones.php");
	                           $pago[]=array();
							   $pago["tipo_pago"]="iddébito";
							   $pago["id_pago"]=$iddebito;
							   $id_imputacion=$_POST["id_imputacion"];

							   imputar_pago($pago,$id_imputacion,$fecha_imputacion);
                               $link=encode_link("../ord_pago/ord_pago_pagar.php",array("nro_orden"=>$parametros["nro_orden"],"exito"=>1));

                      } //del if del !error
                       break;   //del case transferencia

         }//deñ switch


   $db->CompleteTrans();
   header("location:$link");die;

} // Cuando guardo los elementos
//ACLARACION IMPORTANTE!!!!!"
//parametros['comentario_pagos'] se usa cuando viene por cheque nada mas
$link=encode_link("./ord_pago_conf_pago.php",array("pagina"=>$parametros['pagina'],
                                                      "nro_orden"=>$parametros['nro_orden'],
                                                      "valor_dolar"=>$parametros['valor_dolar'],
                                                      "simbolo"=>$simbolo,
                                                      "id_pago"=>$parametros['id_pago'],
                                                      "pagina_pago"=>$parametros["pagina_pago"],
                                                      "id_distrito"=>$id_distrito,
                                                      "Ingreso_Cheque_Numero"=>$parametros['Ingreso_Cheque_Numero'],
                                                      "id_chequera"=>$parametros['id_chequera'],
                                                      "volver"=>$parametros['volver']
                                                      ));
?>
<script>
function aceptar_pago(){
var re;

re=confirm('<?=$nombre_pila;?> se van a guardar los datos , esta seguro?');
if (re==true){
             document.form1.aceptar.style.visibility='hidden';
             return true;
             }
            else
            return false;
}
</script>
<html>
<body bgcolor='<? echo $bgcolor2;?>'>
<form name="form1" method="POST" action="<?=$link;?>">
<?
echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>";
if (!($moneda)) $colspan=5;
                else $colspan=7;

?>
<table width="70%"  align="Center" border="1" cellspacing="1"  bordercolor="#000000">
<tr id="mo">
  <td colspan='2' align="center"> <b> Confirmación de Pagos - Orden de Pago Nro: <? echo $nro_orden; ?> </td>
  <input type="hidden" name="numero_de_orden" value="<?=$nro_orden; ?>";
</tr>
<tr >
  <td colspan='2' align="center"> <font color='red'> <b> Por Favor, leer Atentamente los siguientes datos:  </td>
</tr>

<?

 switch ($parametros['pagina_pago']) {
 case "efectivo":
              $id_proveedor_pago=$parametros["id_proveedor_pago"] or $id_proveedor_pago=$_POST["id_proveedor_pago"];
              $select_proveedor=$parametros["select_proveedor"] or $select_proveedor=$_POST["select_proveedor"];
              $select_tipo=$parametros['select_tipo'] or $select_tipo=$_POST['select_tipo'];
              $select_concepto=$parametros['select_concepto'] or $select_concepto=$_POST['select_concepto'];
              $select_plan=$parametros['select_plan'] or $select_plan=$_POST['select_plan'];
              $select_moneda=$parametros["select_moneda"] or $select_moneda=$_POST["select_moneda"];
              $text_fecha=$parametros["text_fecha"] or $text_fecha=$_POST["text_fecha"];
              $text_monto=$parametros["text_monto"] or $text_monto=$_POST["text_monto"];
              $text_item=$parametros["text_item"] or $text_item=$_POST["text_item"];
              $observaciones=$parametros["observaciones"] or $observaciones=$_POST["observaciones"];
              $monto_mostrar=$parametros["monto_mostrar"]  or $monto_mostar=$_POST["monto_mostrar"];
              $cuentas=$parametros["cuentas"] or $cuentas=$_POST["cuentas"];
              echo "<input type=hidden name=idbanco value={$parametros['idbanco']}>\n";
              echo "<input type=hidden name=select_tipodep_banco value={$parametros['idtipodepbanco']}>\n";
              echo "<input type=hidden name=select_proveedor value=$select_proveedor>\n";
              echo "<input type=hidden name=select_tipo value='$select_tipo'>\n";
              echo "<input type=hidden name=select_concepto value='$select_concepto'>\n";
              echo "<input type=hidden name=select_plan value='$select_plan'>\n";
              echo "<input type=hidden name=select_moneda value='$select_moneda'>\n";
              echo "<input type=hidden name=text_fecha value='$text_fecha'>\n";
              echo "<input type=hidden name=text_monto value='$text_monto'>\n";
              echo "<input type=hidden name=monto_mostrar value='$monto_mostrar'>\n";
              echo "<input type=hidden name=text_item value='$text_item'>\n";
              echo "<input type=hidden name=observaciones value='$observaciones'>\n";
              echo "<input type=hidden name=cuentas value='$cuentas'>\n";
              echo "<input type=hidden name=id_proveedor_pago value='$id_proveedor_pago'>\n";
               if ($cuentas){
               	         $sql="select concepto, plan from general.tipo_cuenta where numero_cuenta = $cuentas";
                         $resultado=sql($sql,1) or die();
                         $nombre_concepto=$resultado->fields['concepto'];
                         $nombre_plan=$resultado->fields['plan'];
                         }
               if ($select_proveedor) {
                         $sql="select razon_social from  proveedor where id_proveedor = $select_proveedor";
                         $resultado=sql($sql,1) or die();
                         $nombre_proveedor=$resultado->fields['razon_social'];
                         }
              if ($select_tipo){
                         $sql="select nombre from  tipo_egreso where id_tipo_egreso = $select_tipo";
                         $resultado=sql($sql,2) or die();
                         $tipo=$resultado->fields['nombre'];
                           }
                echo "<tr>
                         <td width='30%'>
                            <b>Forma de Pago
                         </td>
                         <td align='center'>
                           <font size='3'>
                             <b>EFECTIVO
                           </font>
                         </td>
                     </tr>";
                echo "<tr>
                         <td >
                           <b>Tipo
                         </td>
                         <td bgcolor='#F0F0F0'>
                         &nbsp;&nbsp;$tipo
                         </td>
                      </tr>";
                echo "<tr>
                        <td>
                         <b>Concepto
                         </td>
                         <td bgcolor='#F0F0F0'>
                         &nbsp;&nbsp;$nombre_concepto
                         </td>
                      </tr>";
                echo "<tr>
                        <td>
                          <b>Proveedor
                         </td>
                         <td bgcolor='#F0F0F0'>
                         &nbsp;&nbsp;$nombre_proveedor
                         </td>
                      </tr>";
                echo "<tr>
                         <td>
                         <b>Importe
                         </td>
                         <td bgcolor='#F0F0F0'>
                         <b>&nbsp;&nbsp; $simbolo $monto_mostrar
                         </td>
                      </tr>";
                echo "<tr>
                        <td>
                        <b>Fecha
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$text_fecha
                        </td>
                     </tr>";
                echo "<tr>
                       <td>
                        <b>Plan
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$nombre_plan
                        </td>
                      </tr>";

                $link=encode_link("../caja/".$parametros['volver'],array("pagina"=>"egreso","pagina_viene"=>"ord_pago","nro_orden"=>$nro_orden,
                                 "id_moneda"=>$parametros["id_moneda"],
                                 "valor_dolar"=>$valor_dolar,
                                 "id_pago"=>$parametros['id_pago'],
                                 "distrito"=>$id_distrito
                                 ));

                break;

 case "cheque":
               $banco = $parametros['banco'] or $banco = $_POST['Ingreso_Cheque_Banco'];;
               $proveedor = $parametros['proveedor'] or $proveedor = $_POST['Ingreso_Cheque_Proveedor'];;
               $fecha_e = $parametros['fecha_e'] or $fecha_e = $_POST['Ingreso_Cheque_Fecha_Emision'];;
               $fecha_v = $parametros['fecha_v'] or $fecha_v =$_POST['Ingreso_Cheque_Fecha_Vencimiento'];;
               ///////Diego
               $fecha_v_old = $parametros['fecha_v_old'] or $fecha_v_old=$_POST['Ingreso_Cheque_Fecha_Vencimiento_old'];;
               $fecha_p = $parametros['fecha_p'] or $fecha_p =$_POST['Ingreso_Cheque_Fecha_Debito'];;
               $numero = $parametros['numero'] or $numero =$_POST['Ingreso_Cheque_Numero'];;
               $importe = $parametros['importe'] or $importe =$_POST['Ingreso_Cheque_Importe'];;
               $comentarios = $parametros['comentarios'] or $comentarios =$_POST['Ingreso_Cheque_Comentarios'];;
               $nro_cuenta=$parametros['nro_cuenta'] or $nro_cuenta=$_POST['nro_cuenta'];
               $no_a_la_orden = $parametros['no_a_la_orden'] or $_POST['no_a_la_orden'];
			   if ($no_a_la_orden != "1") $no_a_la_orden = "0";
               $sql="select númeroch,nombrebanco from tipo_banco join cheques using (idbanco) where";
               $sql.=" cheques.númeroch=$numero and cheques.idbanco=$banco";
               $resultado=$db->execute($sql)or die($db->errormsg()."<br>".$sql);
               $cantidad_cheques=$resultado->RecordCount();

               echo "<input type=hidden name=Ingreso_Cheque_Proveedor value=$proveedor>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Banco value='$banco'>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Fecha_Emision value='$fecha_e'>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento value='$fecha_v'>\n";
               /////////Diego
               echo "<input type=hidden name=Ingreso_Cheque_Fecha_Vencimiento_old value='$fecha_v_old'>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Fecha_Debito value='$fecha_p'>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Importe value='$importe'>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Comentarios value='$comentarios'>\n";
               echo "<input type=hidden name=Ingreso_Cheque_Numero value='$numero'>\n";
               echo "<input type=hidden name=nro_cuenta value='$nro_cuenta'>\n";
               echo "<input type=hidden name=no_a_la_orden value='$no_a_la_orden'>\n";
               $sql="select nombrebanco from  tipo_banco where idbanco = $banco";
               $resultado=sql($sql) or die();
               $nombre_banco=$resultado->fields['nombrebanco'];
			   if ($nro_cuenta){
               	         $sql="select concepto, plan from general.tipo_cuenta where numero_cuenta = $nro_cuenta";
                         $resultado=sql($sql,1) or die();
                         $nombre_concepto=$resultado->fields['concepto'];
                         $nombre_plan=$resultado->fields['plan'];
                         }
               if ($proveedor) {
                         $sql="select razon_social from  proveedor where id_proveedor = $proveedor";
                         $resultado=sql($sql,2) or die();
                         $nombre_proveedor=$resultado->fields['razon_social'];
                         }
                echo "<tr>
                       <td width='30%'>
                         <b>Forma de Pago </td>
                       <td align='center' >
                       <font size='3'>
                          <b>CHEQUE
                        </font>
                       </td>
                     </tr>";
                echo "<tr>
                        <td>
                        <b>A nombre de
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$nombre_proveedor
                        </td>
                      </tr>";
                echo "<tr>
                        <td>
                        <b>No a la orden
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;".($sino[$no_a_la_orden])."
                        </td>
                      </tr>";
                echo "<tr>
                       <td>
                       <b>Fecha Emisión
                       </td>
                       <td bgcolor='#F0F0F0'>
                       &nbsp;&nbsp;$fecha_e
                       </td>
                     </tr>";
                echo "<tr>
                        <td>
                          <b>Fecha Vencimiento
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$fecha_v
                        </td>
                     </tr>";
                echo "<tr>
                        <td>
                        <b>Fecha Débito
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$fecha_p
                        </td>
                     </tr>";
                echo "<tr>
                       <td >
                        <b>Banco
                       </td>
                       <td bgcolor='#F0F0F0'>
                       &nbsp;&nbsp;$nombre_banco
                       </td>
                     </tr>";
                echo "<tr  >
                         <td ><b>Número de Cheque </td>
                         <td bgcolor='#FFFFC0'><font size='2' color=red>
                         <b>&nbsp;&nbsp; $numero </font>
                         </td>
                      </tr>";
                echo "<tr>
                       <td>
                        <b>Importe
                       </td>
                       <td bgcolor='#F0F0F0'>
                         <b>&nbsp;&nbsp; $simbolo $importe
                       </td>
                     </tr>";
                echo "<tr>
                       <td>
                        <b>Concepto
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$nombre_concepto
                        </td>
                      </tr>";
                echo "<tr>
                       <td>
                        <b>Plan
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$nombre_plan
                        </td>
                      </tr>";
                echo "<tr>
                        <td>
                        <b>Comentarios
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$comentarios
                        </td>
                     </tr>";
                echo "<tr>
                        <td colspan='2'>
                        &nbsp;
                        </td>
                      </tr>";
                if ($cantidad_cheques) {
                            echo "<tr>
                                  <td colspan='2' align='center' >
                                  <font color='red' size='2'>
                                  <b>Advertencia: Ese número de cheque ya existe por favor verifique los datos
                                  </font>
                                  </td>
                                  </tr>";
                                  }
                                else{
                                 echo "<tr>
                                   <td colspan='2' align='center'>
                                   <b>Por favor verifique que el  número de cheque sea correcto
                                   </td>
                                   </tr>";
                                   }
                $link=encode_link("../bancos/bancos_ing_ch.php",array("pagina"=>"ord_pago","pagina_viene"=>"ord_pago",
                                                                     "nro_orden"=>$nro_orden,
                                                                     "valor_dolar"=>$valor_dolar,"proveedor"=>$proveedor,"numero_cuenta"=>$nro_cuenta,
                                                                     "id_pago"=>$parametros["id_pago"]));
        break;
case "transferencia":
      //parte de debitos de la pagina
          $banco = $parametros['banco'] or  $banco = $_POST['Ingreso_Debito_Banco'];;
          $tipo = $parametros['tipo'] or $tipo = $_POST['Ingreso_Debito_Tipo'];;
          $fecha = $parametros['fecha_debito'] or $fecha = $_POST['Ingreso_Debito_Fecha'];;
          $importe = $parametros['importe'] or $importe = $_POST['Ingreso_Debito_Importe'];;
          $nro_cuenta=$parametros['nro_cuenta'] or $nro_cuenta=$_POST['nro_cuenta'];
          $comentarios=$parametros["comentario_pagos"] or $_POST["comentarios"];
          //hago los hidden para poder pasarlos por post
          echo "<input type=hidden name=Ingreso_Debito_Banco value='$banco'>\n";
          echo "<input type=hidden name=Ingreso_Debito_Tipo  value='$tipo'>\n";
          echo "<input type=hidden name=Ingreso_Debito_Fecha value='$fecha'>\n";
          echo "<input type=hidden name=Ingreso_Debito_Importe value='$importe'>\n";
          echo "<input type=hidden name=nro_cuenta value='$nro_cuenta'>\n";
          echo "<input type=hidden name=comentarios value='$comentarios'>\n";
           $sql="select nombrebanco from  tipo_banco where idbanco = $banco";
           $resultado=sql($sql) or die();
           $nombre_banco=$resultado->fields['nombrebanco'];
           $sql="select tipodébito from  tipo_débito where idtipodéb = $tipo";
           $resultado=sql($sql) or die();
           $tipo_debito=$resultado->fields['tipodébito'];
          if ($nro_cuenta){
               	         $sql="select concepto, plan from general.tipo_cuenta where numero_cuenta = $nro_cuenta";
                         $resultado=sql($sql,1) or die();
                         $nombre_concepto=$resultado->fields['concepto'];
                         $nombre_plan=$resultado->fields['plan'];
                         }
           echo "<tr>
                    <td width='30%'>
                    <b>Forma de Pago
                    </td>
                    <td align='center'>
                      <font size='3'>
                      <b>DEBITOS
                      </font>
                    </td>
                 </tr>";
           echo "<tr>
                   <td >
                   <b>Banco
                   </td>
                   <td bgcolor='#F0F0F0'>
                   &nbsp;&nbsp;$nombre_banco
                   </td>
                 </tr>";
           echo "<tr>
                  <td>
                  <b>
                  Tipo Débito
                  </td>
                  <td bgcolor='#F0F0F0'>
                  &nbsp;&nbsp;$tipo_debito
                  </td>
                </tr>";
           echo "<tr>
                  <td>
                  <b>Fecha Débito
                  </td>
                  <td bgcolor='#F0F0F0'>
                  &nbsp;&nbsp;$fecha
                  </td>
                </tr>";
            echo "<tr>
                       <td>
                        <b>Concepto
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$nombre_concepto
                        </td>
                      </tr>";
           echo "<tr>
                       <td>
                        <b>Plan
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$nombre_plan
                        </td>
                      </tr>";
           echo "<tr>
                        <td>
                        <b>Comentarios
                        </td>
                        <td bgcolor='#F0F0F0'>
                        &nbsp;&nbsp;$comentarios
                        </td>
                     </tr>";
           echo "<tr>
                 <td>
                 <b>Importe
                 </td>
                 <td bgcolor='#F0F0F0'>
                 <b>&nbsp;&nbsp; $simbolo $importe
                 </td>
                </tr>";
          $link=encode_link("../bancos/bancos_ing_deb.php",array("pagina"=>"ord_pago",
                                                                 "nro_orden"=>$nro_orden,
                                                                 "valor_dolar"=>$valor_dolar,
                                                                 "id_pago"=>$parametros['id_pago']));
         break;

} //del switch
?>
</table>
<?
//realizo los link para volver a las paginas correspondientes
$nombre_pila=nombre_pila($_ses_user['name']);
?>
<br>
<table width="70%"  align="Center" border="1" cellspacing="1"  bordercolor="#000000">
<tr id='ma'>
 <td width="20%" align="center"> <input type="Submit" name="aceptar" value="Aceptar" style="width:100" onClick="return aceptar_pago();"> </td>
 <td width="20%" align="center"> <input type="button" name="volver" value="Volver" style="width:100"   OnClick="javascript:window.location='<?=$link?>'"> </td>
</tr>
</table>

<input type="hidden" name="id_imputacion" value="<?=$id_imputacion?>">
<?
include_once("../contabilidad/funciones.php");
generar_hiddens_datos_imputacion($valores_imputacion);
?>
</form>
</body>
</html>