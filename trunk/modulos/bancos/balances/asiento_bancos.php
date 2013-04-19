<?
/*
Autor: MAC
Fecha: 23/02/05

MODIFICADA POR
$Author: fernando $
$Revision: 1.24 $
$Date: 2006/09/07 21:46:08 $

*/

require_once("../../../config.php");
require_once("funciones.php");

if($_POST["guardar"]=="Guardar"){
$db->StartTrans();
 extract($_POST,EXTR_SKIP);
 //insertamos el asiento de remuneracion
 $fecha_hoy=date("Y-m-d H:i:s",mktime());	
 
 if($actualizar=="" || $actualizar==0){
 //si es un asiento nuevo, lo insertamos
  	$query="select nextval('asiento_bancos_id_asiento_bancos_seq') as id_log_asiento";
    $idlog=sql($query,"<br><br>Error al traer secuencia de asiento de bancos<br><br>") or fin_pagina();
 	$id_asiento_bancos=$idlog->fields['id_log_asiento'];
 	
 	$query="insert into asiento_bancos(id_asiento_bancos,idbanco,depositos_acreditados,comision_gastos,
 	        retencion_iva,retencion_imp_ganancias,corapi_cta_particular,sellados,intereses_saldo_deudor,
 	        impuesto_ley_25413,caja,mes_periodo,anio_periodo)
 	        values($id_asiento_bancos,".$_POST["bancos"].",$depositos_acreditados,$comision_gastos,
 	        $retencion_iva,$retencion_imp_ganancias,$corapi_cta_particular,$sellados,$intereses_saldo_deudor,
 	        $impuesto_ley_25413,$caja,$mes,$año)";
 	sql($query,"<br>Error al guardar el asiento de bancos") or fin_pagina();
 	
 	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_bancos (id_asiento_bancos,tipo,fecha,usuario)
    	    values($id_asiento_bancos,'Creación','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de creación<br><br>") or fin_pagina();
  	
  	insertar_retencion_ib($id_asiento_bancos);
  	
  	//traemos el nombre del banco para mostrar en el cartel
  	$query="select nombrebanco from tipo_banco where idbanco=".$_POST['bancos'];
  	$bank=sql($query,"<br>Error al traer el nombre del banco<br>") or fin_pagina();
  	
  	$msg="<br><b><center>Se insertó con éxito el asiento de bancos del período $mes/$año, para el Banco ".$bank->fields["nombrebanco"]."</center></b>";
 }
 else {
 	//sino actualizamos el ya existente
 	$query="update asiento_bancos set 
 	 	    depositos_acreditados=$depositos_acreditados,
 	 	    comision_gastos=$comision_gastos,retencion_iva=$retencion_iva,
 	 	    retencion_imp_ganancias=$retencion_imp_ganancias,corapi_cta_particular=$corapi_cta_particular,
 	 	    sellados=$sellados,
 	 	    intereses_saldo_deudor=$intereses_saldo_deudor,impuesto_ley_25413=$impuesto_ley_25413,caja=$caja
 	 	    where id_asiento_bancos=$id_asiento_bancos";
 	        
 	sql($query,"<br>Error al actualizar el asiento de ventas") or fin_pagina();
 	
 	//insertamos el log de creación del asiento
  	$query="insert into log_asiento_bancos (id_asiento_bancos,tipo,fecha,usuario)
    	    values($id_asiento_bancos,'Actualización','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de actualización<br><br>") or fin_pagina();
  	
  	//borramos todas las entradas retencion_ib para este asiento de retencion,
 	//e insertamos todo nuevamente para facilitar el código
 	$query="delete from retencion_ib where id_asiento_bancos=$id_asiento_bancos";
 	sql($query) or fin_pagina();
 	
  	insertar_retencion_ib($id_asiento_bancos);
  	
  	$msg="<br><b><center>Se actualizó con éxito el asiento de ventas del período $mes/$año</center></b>";
 }	
 $db->CompleteTrans();
 
}//de if($_POST["guardar"]=="Guardar")	

if($_POST["Borrar"]=="Borrar") {
 
 $id_asiento_bancos=$_POST["id_asiento_bancos"];
 $db->StartTrans();
 $query="delete from retencion_ib where id_asiento_bancos=$id_asiento_bancos";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from log_asiento_bancos where id_asiento_bancos=$id_asiento_bancos";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from asiento_bancos where id_asiento_bancos=$id_asiento_bancos";
 sql($query,"<br>Error <br>") or fin_pagina();
 $id_asiento_bancos="";
 unset($_POST);
?>
<table align="center">
<tr>
<td>
<b>El proceso de borrado se ha realizado Correctamente</b>
</td>
</tr>
</table>
<?
 $db->CompleteTrans();
 
}




if($_POST["traer_datos"]=="Traer Datos" ||$_POST["guardar"]=="Guardar") {
 
 $mes=$_POST["mes"];
 $año=$_POST["año"];
 $idbanco=$_POST["bancos"];
 
 //controlamos si el asiento para el periodo elegido, ya esta cargado
 $query="select * from asiento_bancos where mes_periodo=$mes and anio_periodo=$año and idbanco=$idbanco";
 $asiento_info=sql($query,"<br><br>Error al traer datos del asiento<br><br>") or fin_pagina(); 
 $id_asiento_bancos=$asiento_info->fields["id_asiento_bancos"];
  
 //traemos los datos para llenar todos los campos correspondientes
 if($id_asiento_bancos=="") {
 	 
  /****************************************************
                Depositos Acreditados
  *****************************************************/	
  //traemos los datos para los depositos acreditados
  $query="select sum(importedep) as deposito_acreditado 
          from bancos.depósitos
          join bancos.tipo_banco using(idbanco)
          where  fechacrédito ilike '$año-$mes-%' and idbanco=$idbanco";	
  $dep_ac=sql($query,"<br>Error al traer la suma de deposito acreditado<br>") or fin_pagina();
  $depositos_acreditados=number_format($dep_ac->fields["deposito_acreditado"],2,'.','');
  
  //traemos los datos de "comisiones y gastos banco"
  //traemos la suma de los monto de debito que tienen como cuenta: 
  //Concepto="Banco" y plan:"Comisiones" o "Gastos Varios"
  $query="select sum(importedéb) as comision_gastos 
          from bancos.débitos 
          join bancos.tipo_banco using(idbanco)
          join
              (
               select numero_cuenta from general.tipo_cuenta where concepto='Bancos' 
                      and (plan='Comisiones' or plan='Gastos Varios' or plan='Gastos Fianzas')
            )as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $com_gastos_banco=sql($query,"<br>Error al traer los datos de cuenta de Bancos - Comisiones y Gastos Varios<br>") or fin_pagina();
  $comision_gastos=number_format($com_gastos_banco->fields["comision_gastos"],2,'.','');
  /****************************************************
   Fin Depositos Acreditados
  *****************************************************/
  
  /****************************************************
   Retenciones IVA
  *****************************************************/
  //traemos los datos de los débitos con la suma de los que tienen la cuenta
  //"Bancos - Impuesto al Valor Agregado"
  $query="select sum(importedéb) as retencion_iva
          from bancos.débitos 
          join bancos.tipo_banco using(idbanco)
          join
           (select numero_cuenta from general.tipo_cuenta where concepto='Bancos' 
            and (plan='Impuesto al Valor Agregado'))as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $ret_iva=sql($query,"<br>Error al traer los datos de cuenta de Bancos-Impuesto al Valor Agregado<br>") or fin_pagina();
  $retencion_iva=number_format($ret_iva->fields["retencion_iva"],2,'.','');
  /****************************************************
   Fin Retenciones IVA
  *****************************************************/	
  
  /****************************************************
   Impuesto a las ganancias
  *****************************************************/
  //traemos los datos de los débitos con la suma de los que tienen la cuenta
  //"Impuestos - Impuesto a las ganancias"
  $query="select sum(importedéb) as retencion_imp_ganancias
          from bancos.débitos
          join bancos.tipo_banco using(idbanco)
          join
           (select numero_cuenta from general.tipo_cuenta where concepto='Impuestos' 
            and (plan='Retenciones Ganancia'))as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $ret_gan=sql($query,"<br>Error al traer los datos de cuenta de Impuestos -Retenciones Ganancia<br>") or fin_pagina();
  $retencion_imp_ganancias=number_format($ret_gan->fields["retencion_imp_ganancias"],2,'.','');
  /****************************************************
   Fin Impuesto a las ganancias
  *****************************************************/	
  
  /****************************************************
         Retención Ingresos Brutos
  *****************************************************/
  //primero traemos todas las cuentas que sean "Bancos RIB"
  $query="select numero_cuenta,plan from tipo_cuenta where concepto='Bancos' 
         and plan ilike 'RIB%'";
  $ing_br=sql($query,"<br>Error al traer todas las cuentas de ing br<br>") or fin_pagina();
  
  //luego por cada cuenta traemos los totales por distrito y lo guardamos en el 
  //arreglo siguiente:
  $valores_ret_ing_brutos=array();$i=0;
  while(!$ing_br->EOF) {

   $valores_ret_ing_brutos[$i]["plan"]=substr($ing_br->fields["plan"],4);
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de debitos
   $query="select sum(importedéb) as total_rib
           from bancos.débitos  
           join bancos.tipo_banco using(idbanco)
           join general.tipo_cuenta using(numero_cuenta) 
           where numero_cuenta=".$ing_br->fields["numero_cuenta"]." 
           and fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
   $datos1=sql($query,"<br>Error al traer datos debitos en el while $i<br>") or fin_pagina();
   
   $valores_ret_ing_brutos[$i]["monto"]=number_format($datos1->fields["total_rib"],2,'.','');
   
   $i++;
   $ing_br->MoveNext();
  }//de while(!$ing_br->EOF) 
  /**********************************************
     Fin de Retención Ingresos Brutos
  ***********************************************/
  
  /**********************************************
     Corapi Cuenta Particular
  ***********************************************/
  //traemos los datos de los débitos con la suma de los que tienen la cuenta
  //"Retiros": "Corapi, Cuenta Particular"
  $query="select sum(importedéb) as corapi_cta_particular
          from bancos.débitos 
          join bancos.tipo_banco using(idbanco)
          join
           (select numero_cuenta from general.tipo_cuenta where concepto='Retiros' 
            and (plan='Corapi, Cuenta Particular'))as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $corapi_cta_part=sql($query,"<br>Error al traer los datos de cuenta de Retiros - Corapi, Cuenta Particular<br>") or fin_pagina();
  $sub_cta_particular1=$corapi_cta_part->fields["corapi_cta_particular"];

  //traemos todos los cheques debitados del periodo seleccionado, que tengan como cuenta:
  //'Retiros' - 'Corapi, Cuenta Particular'
  $query="select sum(importech) as total_cheque_cta
          from bancos.cheques 
          join general.tipo_cuenta using(numero_cuenta)
          where concepto='Retiros' and plan='Corapi, Cuenta Particular'
          and fechadébch ilike '$año-$mes-%' and idbanco=$idbanco";
  $cheque_cta_corapi=sql($query,"<br>Error al traer datos cheques en el while $i<br>") or fin_pagina();
  
  $sub_cta_particular2=$cheque_cta_corapi->fields["total_cheque_cta"];
   
  $corapi_cta_particular=number_format($sub_cta_particular1+$sub_cta_particular2,2,'.','');
  /**********************************************
     Fin Corapi Cuenta Particular
  ***********************************************/  
  
  /**********************************************
     Sellados
  ***********************************************/
  //traemos los datos de los débitos con la suma de los que tienen la cuenta
  //"Bancos - Sellados"
  $query="select sum(importedéb) as sellados
          from bancos.débitos 
          join bancos.tipo_banco using(idbanco)
          join
           (select numero_cuenta from general.tipo_cuenta where concepto='Bancos' 
            and (plan='Sellados'))as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $sellados_q=sql($query,"<br>Error al traer los datos de cuenta de Bancos - Sellados<br>") or fin_pagina();
  $sellados=number_format($sellados_q->fields["sellados"],2,'.','');
  /**********************************************
     Fin Sellados
  ***********************************************/
  
  /**********************************************
     Intereses s/saldo deudor
  ***********************************************/
  //traemos los datos de los débitos con la suma de los que tienen la cuenta
  //"Bancos - Intereses"
  $query="select sum(importedéb) as intereses_saldo_deudor
          from bancos.débitos 
          join bancos.tipo_banco using(idbanco)
          join
           (select numero_cuenta from general.tipo_cuenta where concepto='Bancos' 
            and (plan='Intereses'))as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $intereses_q=sql($query,"<br>Error al traer los datos de cuenta de Bancos - Intereses<br>") or fin_pagina();
  $intereses_saldo_deudor=number_format($intereses_q->fields["intereses_saldo_deudor"],2,'.','');
  /**********************************************
     Fin Intereses s/saldo deudor
  ***********************************************/
  
  /**********************************************
     Intereses Ley 25413
  ***********************************************/
  //traemos los datos de los débitos con la suma de los que tienen la cuenta
  //"Bancos": "Impuesto al crédito" o "Impuesto al débito"
  $query="select sum(importedéb) as impuesto_ley_25413
          from bancos.débitos 
          join bancos.tipo_banco using(idbanco)
          join
           (select numero_cuenta from general.tipo_cuenta 
            where concepto='Bancos'
            and (plan='Impuesto al débito' or plan='Impuesto al crédito'))as cuentas using(numero_cuenta)
          where fechadébito ilike '$año-$mes-%' and idbanco=$idbanco";
  $impuesto_q=sql($query,"<br>Error al traer los datos de cuenta de Bancos - Impuesto al crédito e Impuesto al débito<br>") or fin_pagina();
  $impuesto_ley_25413=number_format($impuesto_q->fields["impuesto_ley_25413"],2,'.','');
  /**********************************************
     Fin Intereses Ley 25413
  ***********************************************/
  
  /**********************************************
     Caja
  ***********************************************/
    //traemos todos los cheques debitados del periodo seleccionado, que tengan como cuenta:
  //'Retiros' - 'Corapi, Cuenta Particular'
  
  
   $sql = "select sum(importech) as caja
           from bancos.cheques 
           left join general.tipo_cuenta using(numero_cuenta)
           where ((concepto<>'Retiros'  or concepto isnull) and (plan<>'Corapi, Cuenta Particular' or plan isnull or plan <> 'Corapi 2') )
           and fechadébch ilike '$año-$mes-%' and idbanco=$idbanco";
   $res = sql($sql) or fin_pagina();            
   
   ($res->fields["caja"]) ? $caja_cheques = $res->fields["caja"]:$caja_cheques=0;
   

   $sql = " select sum(importedéb) as caja
            from bancos.débitos 
            join bancos.tipo_banco using(idbanco)
            left join general.tipo_cuenta using(numero_cuenta)
            where fechadébito ilike '$año-$mes-%' 
            and idbanco=$idbanco
            and  (concepto<>'Retiros' or concepto isnull)
            and (
                plan<>'Intereses' and plan<>'Corapi, Cuenta Particular' and plan<>'Gastos Fianzas'
                and plan<>'Impuesto al Valor Agregado' and plan<>'Retenciones Ganancia' and not plan ilike 'RIB%'
                and plan<>'Sellados' and plan<>'Impuesto al débito' and plan<>'Impuesto al crédito' 
                and plan<>'Gastos Varios'  
                and plan<>'Comisión tarjeta de crédito' and plan <> 'Comisiones'
                )";
   
    $res = sql($sql) or fin_pagina();
    ($res->fields["caja"]) ? $caja_debitos = $res->fields["caja"]: $caja_debitos = 0;
    
  /*
   $query="select sum(importech) as caja
           from bancos.cheques 
           left join general.tipo_cuenta using(numero_cuenta)
           where ((concepto<>'Retiros' or concepto isnull) and (plan<>'Corapi, Cuenta Particular' or plan isnull))
           and fechadébch ilike '$año-$mes-%' and idbanco=$idbanco";
   $caja_debe=sql($query,"<br>Error al traer datos cheques en el while $i<br>") or fin_pagina();
   */
   $caja=number_format($caja_debitos + $caja_cheques,2,'.','');
  /**********************************************
     Fin Caja
  ***********************************************/  
  
 }//de if($id_asiento_bancos=="")
 else 
 {
  $cartel="<br><B><center><font color='red'>ATENCION: El asiento para este período ya fue realizado</font></center></b>";	
  //si existe el nro_asiento, entonces llenamos las variables con valores
  //traidos desde la BD
  $depositos_acreditados=number_format($asiento_info->fields["depositos_acreditados"],2,'.','');
  $comision_gastos=number_format($asiento_info->fields["comision_gastos"],2,'.','');
  $retencion_iva=number_format($asiento_info->fields["retencion_iva"],2,'.','');
  $retencion_imp_ganancias=number_format($asiento_info->fields["retencion_imp_ganancias"],2,'.','');
  $corapi_cta_particular=number_format($asiento_info->fields["corapi_cta_particular"],2,'.','');
  $sellados=number_format($asiento_info->fields["sellados"],2,'.','');
  $intereses_saldo_deudor=number_format($asiento_info->fields["intereses_saldo_deudor"],2,'.','');
  $impuesto_ley_25413=number_format($asiento_info->fields["impuesto_ley_25413"],2,'.','');
  $caja=number_format($asiento_info->fields["caja"],2,'.','');
  //traemos todos los datos de percepcion_retencion
  $query="select * from retencion_IB where id_asiento_bancos=$id_asiento_bancos";
  $pr=sql($query,"<br>Error al traer retencion_ib, del asiento") or fin_pagina();
  
  $valores_ret_ing_brutos=array();
  $i=$j=$k=0;
  while (!$pr->EOF) 
  {
   $valores_ret_ing_brutos[$i]["monto"]=$pr->fields["monto"];
   $valores_ret_ing_brutos[$i]["plan"]=$pr->fields["nombre_distrito"];
   $i++;

   $pr->MoveNext();	
  }//de while (!$pr->EOF) 
  
  $actualizar=1;
 }//del else de if($nro_asiento=="")
 
}//de if($_POST["traer_datos"]=="Traer Datos")

echo $html_header;
?>
<script language="JavaScript" src="../../../lib/NumberFormat150.js"></script>
<script>

//funcion que controla que los campos obligatorios sean llenados
function control_campos()
{var msg;
 var faltan;
 faltan=0;
 msg="Faltan llenar los siguientes campos\n";
 msg+="-------------------------------------------\n";
 
 if(document.all.depositos_acreditados.value=="")
 {faltan=1;
  msg+="Banco <?=$nombre_banco?>\n";
 }
 if(document.all.comision_gastos.value=="")
 {faltan=1;
  msg+="Comisiones y Gastos Banco\n";
 }		
 if(document.all.retencion_iva.value=="")
 {faltan=1;
  msg+="Retencion I.V.A.\n";
 }		
 if(document.all.retencion_imp_ganancias.value=="")
 {faltan=1;
  msg+="Retención Impuestos a las Ganancias\n";
 }		
 
 //control de retencion ingresos brutos
 var cant_ret_ing_brutos;	
 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {aux=eval("document.all.monto_0_"+i);
  aux1=eval("document.all.dist_0_"+i);
  if(aux.value=="")
  {faltan=1;
   msg+="Retención Ingresos Brutos "+aux1.value+"\n";
  } 
 }//del for
 
 if(document.all.corapi_cta_particular.value=="")
 {faltan=1;
  msg+="Corapi Cuenta Particular\n";
 }		 
 if(document.all.sellados.value=="")
 {faltan=1;
  msg+="Sellados\n";
 }		
  if(document.all.intereses_saldo_deudor.value=="")
 {faltan=1;
  msg+="Intereses s/saldo deudor\n";
 }
 if(document.all.impuesto_ley_25413.value=="")
 {faltan=1;
  msg+="Impuesto Ley 25.413\n";
 }				
 if(document.all.caja.value=="")
 {faltan=1;
  msg+="Caja\n";
 }	 
 
 if(faltan)
 {msg+="-------------------------------------------\n";
  alert(msg);
  return false;
 }	
 else
  return true;
 
}//de function control_campos()

//funcion que deshabilita el botón de imprimir y avisa que hubo cambios
function hay_cambios()
{
 document.all.cambios.value=1;	
 if(typeof(document.all.imprimir)!='undefined')
 {document.all.imprimir.disabled=1;	
  document.all.imprimir.title="Debe guardar para poder imprimir";
 } 
}

//habilita los campos para editarlos
function habilitar_edicion()
{document.all.depositos_acreditados.readOnly=0;
 document.all.comision_gastos.readOnly=0;
 document.all.retencion_iva.readOnly=0;
 document.all.retencion_imp_ganancias.readOnly=0;
 
 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {aux=eval("document.all.monto_0_"+i);
  aux.readOnly=0; 
 }
 
 document.all.corapi_cta_particular.readOnly=0;
 document.all.sellados.readOnly=0;
 document.all.intereses_saldo_deudor.readOnly=0;
 document.all.impuesto_ley_25413.readOnly=0;
 document.all.caja.readOnly=0;
}

function deshabilitar_edicion()
{document.all.depositos_acreditados.readOnly=1;
 document.all.comision_gastos.readOnly=1;
 document.all.retencion_iva.readOnly=1;
 document.all.retencion_imp_ganancias.readOnly=1;
 
 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {aux=eval("document.all.monto_0_"+i);
  aux.readOnly=1; 
 }
 
 document.all.corapi_cta_particular.readOnly=1;
 document.all.sellados.readOnly=1;
 document.all.intereses_saldo_deudor.readOnly=1;
 document.all.impuesto_ley_25413.readOnly=1;
 document.all.caja.readOnly=1;
}	

function calcular_montos()
{var depositos_acreditados,comision_gastos,retencion_iva,retencion_imp_ganancias;
 var cant_ret_ing_brutos,corapi_cta_particular,sellados,intereses_saldo_deudor,impuesto_ley_25413,caja,acum=0;
 var caja_haber,suma_debe,suma_haber;
 	
 depositos_acreditados=(document.all.depositos_acreditados.value)?parseFloat(document.all.depositos_acreditados.value):0;
 comision_gastos=(document.all.comision_gastos.value)?parseFloat(document.all.comision_gastos.value):0;
 retencion_iva=(document.all.retencion_iva.value)?parseFloat(document.all.retencion_iva.value):0;
 retencion_imp_ganancias=(document.all.retencion_imp_ganancias.value)?parseFloat(document.all.retencion_imp_ganancias.value):0;
  
 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {
   aux=eval("document.all.monto_0_"+i+".value");
   if(aux=="")
    aux=0;
   acum+=parseFloat(aux);
 }
 
 corapi_cta_particular=(document.all.corapi_cta_particular.value)?parseFloat(document.all.corapi_cta_particular.value):0;
 sellados=(document.all.sellados.value)?parseFloat(document.all.sellados.value):0;
 intereses_saldo_deudor=(document.all.intereses_saldo_deudor.value)?parseFloat(document.all.intereses_saldo_deudor.value):0;
 impuesto_ley_25413=(document.all.impuesto_ley_25413.value)?parseFloat(document.all.impuesto_ley_25413.value):0;
 caja=(document.all.caja.value)?parseFloat(document.all.caja.value):0;

 document.all.caja_haber.value=formato_BD(depositos_acreditados);
 document.all.banco_haber.value=formato_BD(comision_gastos+retencion_iva+retencion_imp_ganancias+
                                           acum+corapi_cta_particular+sellados+intereses_saldo_deudor+
                                           impuesto_ley_25413+caja);
 document.all.suma_debe.value=formato_BD(depositos_acreditados+comision_gastos+retencion_iva+
                                         retencion_imp_ganancias+acum+corapi_cta_particular+sellados+
                                         intereses_saldo_deudor+impuesto_ley_25413+caja);
 document.all.suma_haber.value=document.all.suma_debe.value;
 
}

function control_traer_datos()
{
 if(document.all.bancos.value==-1)
 {alert("Debe seleccionar un Banco");
  return false;
 }
 document.all.actualizar.value=0;
 return true;
}

</script>
<?
if($actualizar)
{$query="select tipo,fecha,usuario 
 from log_asiento_bancos where id_asiento_bancos=$id_asiento_bancos order by fecha DESC";
 $log_info=sql($query,"<br>Error al traer el log del asiento<br>") or fin_pagina();
 ?>
<div align="right" style='position:relative; width:95%; height:10%; overflow:auto;'>
 <table width="100%">
  <?
   while(!$log_info->EOF)
   {?>
    <tr id="ma">
     <td align="left">
      Fecha de <?=$log_info->fields["tipo"]?>: <?=fecha($log_info->fields["fecha"])?> <?=Hora($log_info->fields["fecha"])?>
     </td>
     <td align="right">
      Usuario: <?=$log_info->fields["usuario"]?>
     </td>
    </tr>
   	<?
   	$log_info->MoveNext();
   }//de while(!$log_info->EOF)	
  ?>
 </table>
</div> 
 <?
}//de if($actualizar)

if($msg=="")
 echo $cartel;
else 
 echo $msg; 
?>
<br>
<table align="center" width="95%" border="1">
 <tr>
  <td id="mo">
   <font size="3">Asiento de Bancos</font>
  </td>
 </tr>
</table>
<form name="form1" action="asiento_bancos.php" method="POST" <?=$disabled_form?>>
<input type="hidden" name="actualizar" value="<?=$actualizar?>">
<input type="hidden" name="id_asiento_bancos" value="<?=$id_asiento_bancos?>">
<input type="hidden" name="cambios" value="0">
<input type="hidden" name="cant_ret_ing_brutos" value="<?=sizeof($valores_ret_ing_brutos)?>">
<table align="center" width="95%" class="bordes">
 <tr> 
  <td colspan="4">
   <table width="100%" bgcolor="White" cellpadding="3">
    <tr>
     <td>
      <?if($_POST["mes"]!="")
      {?>
       <input type="checkbox" name="editar" onchange="if(this.checked==1)habilitar_edicion();else deshabilitar_edicion();"> Editar
      <?
      }
	  else  
	  {?>
	   &nbsp;
	  <?
	  }?>
     </td>
     <td>
      <table border="1" width="60%">
       <tr>
        <td>
         <font color="Blue"><b>Banco</b></font>
        </td>
        <td>
         <?//traemos los bancos posibles, para hacer el asiento respectivos
         $query="select idbanco,nombrebanco from tipo_banco where activo=1 order by nombrebanco";
         $bancos=sql($query,"<br>Error al traer los bancos<br>") or fin_pagina();
         ?>
         <select name="bancos" onchange="document.all.guardar.disabled=1">
          <option value=-1>Seleccione Banco</option>
          <?
           while(!$bancos->EOF)
           {
           	if($_POST["bancos"]==$bancos->fields["idbanco"])
           	{$selected_bancos="selected";
           	 $nombre_banco=$bancos->fields['nombrebanco'];
           	}
           	else 
           	 $selected_bancos="";
           	?>
            <option value='<?=$bancos->fields["idbanco"]?>' <?=$selected_bancos?>>
             <?=$bancos->fields['nombrebanco']?>
            </option>
           	<?
            $bancos->MoveNext();
           }
          ?>
         </select>
        </td>
       </tr>
      </table>
     </td>   
     <td align="right" colspan="2">
      <table border="1" width="60%">
       <tr>
        <td>
         <font color="Blue"><b>Período</b></font>
        </td>
        <td>
         <b>Mes</b>&nbsp; 
         <select name=mes  onchange="document.all.guardar.disabled=1">
          <option value='01' <?if ($mes==1) echo "selected"?>>Enero</option>
          <option value='02' <?if ($mes==2) echo "selected"?>>Febrero</option>
          <option value='03' <?if ($mes==3) echo "selected"?>>Marzo</option>
          <option value='04' <?if ($mes==4) echo "selected"?>>Abril</option>
          <option value='05' <?if ($mes==5) echo "selected"?>>Mayo</option>
          <option value='06' <?if ($mes==6) echo "selected"?>>Junio</option>
          <option value='07' <?if ($mes==7) echo "selected"?>>Julio</option>
          <option value='08' <?if ($mes==8) echo "selected"?>>Agosto</option>
          <option value='09' <?if ($mes==9) echo "selected"?>>Septiembre</option>
          <option value='10' <?if ($mes==10) echo "selected"?>>Octubre</option>
          <option value='11' <?if ($mes==11) echo "selected"?>>Noviembre</option>
          <option value='12' <?if ($mes==12) echo "selected"?>>Diciembre</option>
         </select>
        </td>
        <td colspan="2">
         <b>Año</b>&nbsp;
         <select name=año  onchange="document.all.guardar.disabled=1">
          <option value='2003' <?if ($año==2003) echo "selected"?>>2003</option>
          <option value='2004' <?if ($año==2004) echo "selected"?>>2004</option>
          <option value='2005' <?if ($año==2005) echo "selected"?>>2005</option>
          <option value='2006' <?if ($año==2006) echo "selected"?>>2006</option>
          <option value='2007' <?if ($año==2007) echo "selected"?>>2007</option>
          <option value='2008' <?if ($año==2008) echo "selected"?>>2008</option>
          <option value='2009' <?if ($año==2009) echo "selected"?>>2009</option>
          <option value='2010' <?if ($año==2010) echo "selected"?>>2010</option>
          <option value='2011' <?if ($año==2011) echo "selected"?>>2011</option>
          <option value='2012' <?if ($año==2012) echo "selected"?>>2012</option>
         </select>
        </td>
       </tr>
      </table> 
     </td>
     <td>
      <input type="submit" name="traer_datos" value="Traer Datos" 
          onclick="if(document.all.cambios.value==1)
                   {if(confirm('Ha realizado cambios en este Asiento de Bancos. Si continúa se perderán los cambios.\n¿Está Seguro que desea continuar?'))
                    {
                     document.all.actualizar.value=0; 
                     return true;
                    } 
                    else
                     return false; 
                   }
                   return control_traer_datos();
                  "
      >
     </td>
    </tr>
   </table>  
  </td>
 </tr> 
 <?
 if($_POST["mes"]=="")
 {$disabled_form="disabled";
  ?>
  <tr>
   <td colspan="4" align="center">
    <font size='3' color='red'><b>Seleccione el período del asiento de bancos que desea completar y presione el botón traer datos</b></font>
   </td>
  </tr> 
  <?
 }
?> 
 <tr id="ma">
  <td width="10%">
   Cuenta
  </td>
  <td width="40%">
   Concepto
  </td>
  <td width="25%">
   DEBE
  </td>
  <td width="25%">
   HABER
  </td>
 </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <b>por los depósitos</b>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Banco <?=$nombre_banco?>
     </td>
     <td width="25%">
      <input type="text" name="depositos_acreditados" readonly value="<?=$depositos_acreditados?>"  size="10" onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>   
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Caja
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      <input type="text" name="caja_haber" readonly value="<?=$caja_haber?>"  size="10" onchange="hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <b>por los egresos de banco</b>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Comisiones y gastos banco
     </td>
     <td width="25%"> 
      <input type="text" name="comision_gastos" readonly value="<?=$comision_gastos?>"  size="10" onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Retenciones I.V.A.
     </td>
     <td width="25%">
      <input type="text" name="retencion_iva" value="<?=$retencion_iva?>"  size="10" onchange="calcular_montos();hay_cambios();" readonly>
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Retención Imp. a las Ganancias
     </td>
     <td width="25%">
      <input type="text" name="retencion_imp_ganancias" value="<?=$retencion_imp_ganancias?>"  readonly size="10" onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%" align="center">
      <b>Retenciones de Ingresos Brutos</b>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <?
     //generamos la lista de ingresos brutos con las provincias
     //correspondientes, segun la BD
     generar_lista_provincias(0,$valores_ret_ing_brutos,1,"calcular_montos();");
    ?>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Corapi Cuenta Particular
     </td>
     <td width="25%">
      <input type="text" name="corapi_cta_particular" value="<?=$corapi_cta_particular?>"  size="10" readonly onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Sellados
     </td>
     <td width="25%">
      <input type="text" name="sellados" value="<?=$sellados?>"  size="10" readonly onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Intereses s/saldo deudor
     </td>
     <td width="25%">
      <input type="text" name="intereses_saldo_deudor" value="<?=$intereses_saldo_deudor?>"  size="10" readonly onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Impuesto Ley 25413
     </td>
     <td width="25%">
      <input type="text" name="impuesto_ley_25413" value="<?=$impuesto_ley_25413?>"  size="10" readonly onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Caja
     </td>
     <td width="25%">
      <input type="text" name="caja" value="<?=$caja?>"  size="10" readonly onchange="calcular_montos();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="25%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      Banco <?=$nombre_banco?>
     </td>
     <td width="25%">
      &nbsp;
     </td>
     <td width="25%">
      <input type="text" name="banco_haber" readonly value="<?=$banco_haber?>"  size="10" onchange="hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="4">
      &nbsp;
     </td>
    </tr>    
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%" align="right">
      <b>TOTALES</b>
     </td>
     <td width="25%">
      <input type="text" name="suma_debe" value="<?=$suma_debe?>"  size="10" readonly>
     </td>
     <td width="25%">
      <input type="text" name="suma_haber" value="<?=$suma_haber?>"  size="10" readonly>
     </td>
    </tr>
</table>
<table align="center" border="1" width="95%">
 <tr>
  <?
  if($id_asiento_bancos && permisos_check("inicio","permiso_boton_borrar_asientos"))
  {
   ?>
   <td width="1%">
     <input type="submit" name="Borrar"   value="Borrar" onclick="if(confirm ('Se borraran los datos de este asiento.\n¿Está seguro que desea continuar?'))
   																  {
   																   document.all.actualizar.value=0;   
   																   return true;
   																  }
   																  else
   																   return false; 
                                                                 "
     >
   </td>
  <?
  }
 ?>
  <td align="<?if($actualizar) echo "right";else echo "center"?>">
   <?
   if(!permisos_check("inicio","permiso_boton_guardar_asiento_bancos"))
    $disabled_permiso="disabled";
   ?>
   <input type="submit" name="guardar" <?=$disabled_permiso?> <?=$disabled_form?> value="Guardar" onclick="return control_campos();">
  </td>
  <?
  if($actualizar)
  {
   $link_imprimir=encode_link("imprimir_bancos.php",array("id_asiento_bancos"=>$id_asiento_bancos));
   ?>
   <td align="left">
    <input type="button" name="imprimir" value="Imprimir" onclick="window.open('<?=$link_imprimir?>')">
   </td>
  <?
  }
  ?>
 </tr>  
</table>
</form>
<script>
//calculamos los montos totales (suma_debe,suma_haber,caja,banco_haber)
calcular_montos();
</script>
<br>
<?fin_pagina();?>