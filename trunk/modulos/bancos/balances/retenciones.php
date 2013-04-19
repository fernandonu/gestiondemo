<?
/*
Autor: MAC
Fecha: 22/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.12 $
$Date: 2005/07/29 15:14:56 $

*/

require_once("../../../config.php");
require_once("funciones.php");

if($_POST["guardar"]=="Guardar")
{$db->StartTrans();
 extract($_POST,EXTR_SKIP);
 //insertamos el asiento de remuneracion
 $fecha_hoy=date("Y-m-d H:i:s",mktime());	
 
 if($actualizar=="")//si es un asiento nuevo, lo insertamos
 {
 	$query="select nextval('retenciones_id_retenciones_seq') as id_log_asiento";
    $idlog=sql($query,"<br><br>Error al traer secuencia de retenciones<br><br>") or fin_pagina();
 	$id_retenciones=$idlog->fields['id_log_asiento'];
 	
 	$query="insert into retenciones(id_retenciones,mes_periodo,anio_periodo,retencion_iva,
 	                                percepcion_iva,retencion_imp_ganancias)
 	        values($id_retenciones,$mes,$año,$retencion_iva,$percepcion_iva,$retencion_imp_ganancias)";
 	sql($query,"<br>Error al guardar el asiento de retenciones") or fin_pagina();
 	
 	insertar_percepcion_retencion($id_retenciones);
 	
 	//insertamos el log de creación del asiento
  	$query="insert into log_retenciones (id_retenciones,tipo,fecha,usuario)
    	    values($id_retenciones,'Creación','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de creación<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se insertó con éxito el asiento de Retenciones del período $mes/$año</center></b>";
 }
 else//sino actualizamos el ya existente
 {
 	 	$query="update retenciones set 
                retencion_iva=$retencion_iva,percepcion_iva=$percepcion_iva,
 	 	        retencion_imp_ganancias=$retencion_imp_ganancias
 	 	        where id_retenciones=$id_retenciones";
 	        
 	sql($query,"<br>Error al actualizar el asiento de retenciones<br>") or fin_pagina();
 	
 	//borramos todas las entradas percepcion_retencion para este asiento de retencion,
 	//e insertamos todo nuevamente para facilitar el código
 	$query="delete from percepcion_retencion where id_retenciones=$id_retenciones";
 	sql($query) or fin_pagina();
 	
 	insertar_percepcion_retencion($id_retenciones); 	
 	
 	//insertamos el log de creación del asiento
  	$query="insert into log_retenciones (id_retenciones,tipo,fecha,usuario)
    	    values($id_retenciones,'Actualización','$fecha_hoy','".$_ses_user['name']."')";
  	sql($query,"<br><br>Error al insertar log de actualización<br><br>") or fin_pagina();
  	$msg="<br><b><center>Se actualizó con éxito el asiento de Retenciones del período $mes/$año</center></b>";
 }	
 
 $db->CompleteTrans();
 
}


if($_POST["Borrar"]=="Borrar")
{
 $id_retenciones=$_POST["id_retenciones"];
 $db->StartTrans();
 $query="delete from percepcion_retencion where id_retenciones=$id_retenciones";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from log_retenciones where id_retenciones=$id_retenciones";
 sql($query,"<br>Error <br>") or fin_pagina();
 $query="delete from retenciones where id_retenciones=$id_retenciones";
 sql($query,"<br>Error <br>") or fin_pagina();
 $id_retenciones="";
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


if($_POST["traer_datos"]=="Traer Datos" ||$_POST["guardar"]=="Guardar")
{
 
 $mes=$_POST["mes"];
 $año=$_POST["año"];
 $valor_dolar=$_POST["valor_dolar"];
 //controlamos si el asiento para el periodo elegido, ya esta cargado
 $query="select * from retenciones where mes_periodo=$mes and anio_periodo=$año";
 $asiento_info=sql($query,"<br><br>Error al traer datos de retenciones<br><br>") or fin_pagina(); 
 $id_retenciones=$asiento_info->fields["id_retenciones"];

 //si selecciona el mes actual traigo los que no tienen mes aun
 if ($mes==date("m") && $año==date("Y"))
 	$include_null=true;
 //traemos los datos para llenar todos los campos correspondientes
 if($id_retenciones=="")
 {
  /*********************************************************	
        Retenciones de IVA del periodo seleccionado
  **********************************************************/
  //CAJA
  $query ="select sum(monto) as ret_iva ";
  $query.="from caja.ingreso_egreso ";
  $query.="join retenciones_fact using(id_ingreso_egreso) ";
  $query.="join caja.caja using (id_caja) ";
  $query.="join general.tipo_cuenta using(numero_cuenta) ";
  $query.="join licitaciones.moneda using (id_moneda) ";
  $query.="where simbolo='$' and concepto='Impuestos' and Plan='Retenciones I.V.A.' AND ";
  //incluyo aquellos que no tienen mes asociado
  if ($include_null)
  	 $query.="(anio=$año AND mes=$mes OR mes is null AND anio is null) "; 
  else 
  	$query.="anio=$año AND mes=$mes ";
  $datos=sql($query) or fin_pagina();
  
	//no deberian haber retenciones en dolares!!
  $query="select sum(monto*$valor_dolar) as ret_iva 
  from caja.ingreso_egreso join caja.caja using (id_caja) 
  join general.tipo_cuenta using(numero_cuenta) join licitaciones.moneda using (id_moneda)
  where simbolo='U$\S' and concepto='Impuestos' and Plan='Retenciones I.V.A.' 
  and fecha ilike '$año-$mes-%'";
  $datos1=sql($query) or fin_pagina();
  
  $retencion_iva=number_format($datos->fields["ret_iva"]+$datos1->fields["ret_iva"],2,'.','');
  //cheques
  /*$query="select sum(importech) as ret_iva 
  from bancos.cheques join general.tipo_cuenta using(numero_cuenta) 
  where concepto='Impuestos' and Plan='Retenciones I.V.A.' 
  and fechaemich ilike '$año-$mes-%'";
  $datos=sql($query) or fin_pagina();
  $retencion_iva+=number_format($datos->fields["ret_iva"],2,'.','');
  //debitos
  $query="
  select sum(importedéb) as ret_iva 
  from bancos.débitos join general.tipo_cuenta using(numero_cuenta) 
  where concepto='Impuestos' and Plan='Retenciones I.V.A.' 
  and fechadébito ilike '$año-$mes-%'";
  $datos=sql($query) or fin_pagina();
  $retencion_iva+=number_format($datos->fields["ret_iva"],2,'.','');*/
  /**************************************************
         Fin de Retenciones de I.V.A.
  ***************************************************/
  
  /**************************************************
         Percepcion I.V.A.
  ***************************************************/
  //traemos los datos de la percepcion de I.V.A. desde
  //fact_prov
  $query ="select sum(percepcion_iva) as total from fact_prov ";
  //incluyo aquellos que no tienen mes asociado
  if ($include_null)
  	$query.="where anio_percepcion=$año AND mes_percepcion=$mes OR anio_percepcion is null AND mes_percepcion is null";
  else
  	$query.="where anio_percepcion=$año AND mes_percepcion=$mes";
  $perc_iva=sql($query,"<br>Error al traer info de percepcion de iva<br>") or fin_pagina();
  
  $percepcion_iva=number_format($perc_iva->fields["total"],2,'.','');
  /**************************************************
         Fin de Percepcion I.V.A.
  ***************************************************/
  
  
  /**************************************************
  	     Retenciones de Impuesto a las Ganancia
  ***************************************************/
  //CAJA
  $query="select sum(monto) as ret_gan
  from caja.ingreso_egreso 
  join retenciones_fact using(id_ingreso_egreso)   
  join caja.caja using (id_caja) 
  join general.tipo_cuenta using(numero_cuenta) join licitaciones.moneda using (id_moneda)
  where simbolo='$' and concepto='Impuestos' and Plan='Retenciones Ganancia' ";
  //incluyo aquellos que no tienen mes asociado
  if ($include_null)
  	$query.="AND anio=$año AND mes=$mes OR anio is null AND mes is null";
  else
  	$query.="AND anio=$año AND mes=$mes";
  $datos=sql($query) or fin_pagina();
  
	//no deberian haber retenciones en dolares!!
  $query="select sum(monto*$valor_dolar) as ret_gan
  from caja.ingreso_egreso join caja.caja using (id_caja) 
  join general.tipo_cuenta using(numero_cuenta) join licitaciones.moneda using (id_moneda)
  where simbolo='U$\S' and concepto='Impuestos' and Plan='Retenciones Ganancia' 
  and fecha ilike '$año-$mes-%'";
  $datos1=sql($query) or fin_pagina();

  $retencion_imp_ganancias=number_format($datos->fields["ret_gan"]+$datos1->fields["ret_gan"],2,'.','');
  //cheques
  /*$query="select sum(importech) as ret_gan
  from bancos.cheques join general.tipo_cuenta using(numero_cuenta) 
  where concepto='Impuestos' and Plan='Retenciones Ganancia' 
  and fechaemich ilike '$año-$mes-%'";
  $datos=sql($query) or fin_pagina();
  $retencion_imp_ganancias+=number_format($datos->fields["ret_gan"],2,'.','');
  //debitos
  $query="
  select sum(importedéb) as ret_gan
  from bancos.débitos join general.tipo_cuenta using(numero_cuenta) 
  where concepto='Impuestos' and Plan='Retenciones Ganancia' 
  and fechadébito ilike '$año-$mes-%'";
  $datos=sql($query) or fin_pagina();
  $retencion_imp_ganancias+=number_format($datos->fields["ret_gan"],2,'.','');*/
  /**************************************************
     Fin de Retenciones de impuesto a las ganancias
  ***************************************************/
  
  
  /****************************************************
         Retención Ingresos Brutos
  *****************************************************/
  //primero traemos todas las cuentas que sean "Impuestos RIB"
  $query="select numero_cuenta,plan from tipo_cuenta where concepto='Impuestos' 
         and plan ilike 'RIB%'";
  $ing_br=sql($query,"<br>Error al traer todas las cuentas de ing br<br>") or fin_pagina();
  
  //luego por cada cuenta traemos los totales por distrito y lo guardamos en el 
  //arreglo siguiente:
  $valores_ret_ing_brutos=array();$i=0;
  while(!$ing_br->EOF)
  {
   $valores_ret_ing_brutos[$i]["plan"]=substr($ing_br->fields["plan"],4);
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de caja
   $query="select sum(monto) as total
   from caja.ingreso_egreso 
   join retenciones_fact using(id_ingreso_egreso) 
   left join caja.caja using (id_caja) 
   left join general.tipo_cuenta using(numero_cuenta) 
   left join licitaciones.moneda using (id_moneda) 
   where simbolo='$' and numero_cuenta=".$ing_br->fields["numero_cuenta"].
   " and (anio=$año and mes=$mes)";
   $datos=sql($query,"<br>Error al traer datos caja en el while $i<br>") or fin_pagina();
   
	//En retenciones IB "no deberia haber valores en dolares"
   $query="select sum(monto*$valor_dolar) as total
   from caja.ingreso_egreso join caja.caja using (id_caja) 
   join general.tipo_cuenta using(numero_cuenta) join licitaciones.moneda using (id_moneda) 
   where simbolo='U$\S' and numero_cuenta=".$ing_br->fields["numero_cuenta"]." 
   and fecha ilike '$año-$mes-%'";
   $datos1=sql($query,"<br>Error al traer datos caja dolar en el while $i<br>") or fin_pagina();
   
   $monto_pesos=($datos->fields["total"])?$datos->fields["total"]:0;
   $monto_dolar=($datos1->fields["total"])?$datos1->fields["total"]:0;
   
   $valores_ret_ing_brutos[$i]["monto"]=$monto_pesos+$monto_dolar;
   
   /*
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de cheques
   $query="select sum(importech) as total
   from bancos.cheques 
   join general.tipo_cuenta using(numero_cuenta)
   where numero_cuenta=".$ing_br->fields["numero_cuenta"]."
   and fechaemich ilike '$año-$mes-%'";
   $datos=sql($query,"<br>Error al traer datos cheques en el while $i<br>") or fin_pagina();
   $valores_ret_ing_brutos[$i]["monto"]+=$datos->fields["total"];
   
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de débitos
   $query="select sum(importedéb) as total
   from bancos.débitos 
   join general.tipo_cuenta using(numero_cuenta)
   where numero_cuenta=".$ing_br->fields["numero_cuenta"]."
   and fechadébito ilike '$año-$mes-%'";
   $datos=sql($query,"<br>Error al traer datos debitos en el while $i<br>") or fin_pagina();
   $valores_ret_ing_brutos[$i]["monto"]+=$datos->fields["total"];
   */
   $valores_ret_ing_brutos[$i]["monto"]=number_format($valores_ret_ing_brutos[$i]["monto"],2,'.','');
   
   $i++;
   $ing_br->MoveNext();
  }//de while(!$ing_br->EOF) 
  /**********************************************
     Fin de Retención Ingresos Brutos
  ***********************************************/
  
  
  /**********************************************
    Percepción Ingresos Brutos 
  ***********************************************/
  //traemos los datos de la percepcion de ingresos brutos desde 
  //fact_prov
  $query ="select sum (monto_ib) as total,distrito.nombre ";
  $query.="from fact_prov ";
  $query.="join percepciones_ib using(id_factura) ";
  $query.="join distrito using(id_distrito) ";
  //$query.="where fecha_emision ilike '$año-$mes-%' group by distrito.nombre";
  //ahora se toman las percepciones que se pusieron en un mes-año determinado
  $query.="where anio_percepcion=$año AND mes_percepcion=$mes group by distrito.nombre";
  $per_ing_br=sql($query,"<br>Error al traer percepcion de ingresos brutos<br>");
  $i=0;
  $valores_per_ing_brutos=array();
  while(!$per_ing_br->EOF)
  {
   $valores_per_ing_brutos[$i]["plan"]=$per_ing_br->fields["nombre"];
   $valores_per_ing_brutos[$i]["monto"]=$per_ing_br->fields["total"];
   
   $i++;
   $per_ing_br->MoveNext();
  }//de while(!$valores_per_ing_brutos->fields)  
  /**********************************************
    Fin Percepción Ingresos Brutos 
  ***********************************************/
  
  
  /**********************************************
    Retención Bancaria
  ***********************************************/
  //primero traemos todas las cuentas que sean "Bancos RIB"
  $query="select numero_cuenta,plan from tipo_cuenta where concepto='Bancos' 
         and plan ilike 'RIB%'";
  $banc=sql($query,"<br>Error al traer todas las cuentas de ing br<br>") or fin_pagina();
  
  //luego por cada cuenta traemos los totales por distrito y lo guardamos en el 
  //arreglo siguiente:
  $valores_ret_bancaria=array();$i=0;
  while(!$banc->EOF)
  {
   $valores_ret_bancaria[$i]["plan"]=substr($banc->fields["plan"],4);
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de caja
   /*$query="select sum(monto) as total
   from caja.ingreso_egreso join caja.caja using (id_caja) 
   join general.tipo_cuenta using(numero_cuenta) join licitaciones.moneda using (id_moneda) 
   where simbolo='$' and numero_cuenta=".$banc->fields["numero_cuenta"]." 
   and fecha ilike '$año-$mes-%'";
   $datos=sql($query,"<br>Error al traer datos caja en el while $i<br>") or fin_pagina();
   $valores_ret_bancaria[$i]["monto"]=$datos->fields["total"];
   */
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de cheques
   $query="select sum(importech) as total
   from bancos.cheques 
   join general.tipo_cuenta using(numero_cuenta)
   where numero_cuenta=".$banc->fields["numero_cuenta"]."
   and fechaemich ilike '$año-$mes-%'";
   $datos=sql($query,"<br>Error al traer datos cheques en el while $i<br>") or fin_pagina();
   $valores_ret_bancaria[$i]["monto"]+=$datos->fields["total"];
   
   //traemos por cada distrito que tiene cuenta de este tipo, el total del mismo, dentro de débitos
   $query="select sum(importedéb) as total
   from bancos.débitos 
   join general.tipo_cuenta using(numero_cuenta)
   where numero_cuenta=".$banc->fields["numero_cuenta"]."
   and fechadébito ilike '$año-$mes-%'";
   $datos=sql($query,"<br>Error al traer datos debitos en el while $i<br>") or fin_pagina();
   $valores_ret_bancaria[$i]["monto"]+=$datos->fields["total"];
   
   $valores_ret_bancaria[$i]["monto"]=number_format($valores_ret_bancaria[$i]["monto"],2,'.','');
   
   $i++;
   $banc->MoveNext();
  }//de while(!$ing_br->EOF) 
  /**********************************************
    Fin de Retención Bancaria
  ***********************************************/
  
 }//de if($id_asiento_venta=="")
 else 
 {
  $cartel="<br><B><center><font color='red'>ATENCION: El asiento para este período ya fue realizado</font></center></b>";	
  //si existe el nro_asiento, entonces llenamos las variables con valores
  //traidos desde la BD
  $retencion_iva=number_format($asiento_info->fields["retencion_iva"],2,'.','');
  $percepcion_iva=number_format($asiento_info->fields["percepcion_iva"],2,'.','');
  $retencion_imp_ganancias=number_format($asiento_info->fields["retencion_imp_ganancias"],2,'.','');
  
  //traemos todos los datos de percepcion_retencion
  $query="select * from percepcion_retencion where id_retenciones=$id_retenciones";
  $pr=sql($query,"<br>Error al traer percepcion_retencion, del asiento") or fin_pagina();
  
  $valores_ret_ing_brutos=array();
  $valores_per_ing_brutos=array();
  $valores_ret_bancaria=array();
  $i=$j=$k=0;
  while (!$pr->EOF) 
  {
  	//armamos los arreglos para mostrar los datos respectivos a:
  	//retencion ingresos brutos, percepcion ingresos brutos, retencion bancaria
    switch ($pr->fields["clase"])
    {
    	case 0:$valores_ret_ing_brutos[$i]["monto"]=$pr->fields["monto"];
    	       $valores_ret_ing_brutos[$i]["plan"]=$pr->fields["nombre_distrito"];
    	       $i++;
    	       break;
    	case 1:$valores_per_ing_brutos[$j]["monto"]=$pr->fields["monto"];
    	       $valores_per_ing_brutos[$j]["plan"]=$pr->fields["nombre_distrito"];
    	       $j++;
    	       break;
    	case 2:$valores_ret_bancaria[$k]["monto"]=$pr->fields["monto"];
    	       $valores_ret_bancaria[$k]["plan"]=$pr->fields["nombre_distrito"];
    	       $k++;
    	       break;
    }
  	
   $pr->MoveNext();	
  }//de while (!$pr->EOF) 
  
  $actualizar=1;
 }//del else de if($nro_asiento=="")
 
}//de if($_POST["traer_datos"]=="Traer Datos")

echo $html_header;
?>
<script language="JavaScript" src="../../../lib/NumberFormat150.js"></script>
<script>

//funcion que calcula el total de retencion de ingresos brutos
function calcular_ret_ing_brutos()
{var i,aux;
 var acum=0;
 var cant_ret_ing_brutos;	
 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 
 for(i=0;i<cant_ret_ing_brutos;i++)
 {
   aux=eval("document.all.monto_0_"+i+".value");
   if(aux=="")
    aux=0;
   acum+=parseFloat(aux);
 }

 document.all.total_ret_ib.value=formato_BD(acum);
 calcular_suma_debe();
}	

//funcion que calcula el total de percepcion de ingresos brutos
function calcular_per_ing_brutos()
{var i,aux;
 var acum=0;
 var cant_per_ing_brutos;	
 cant_per_ing_brutos=document.all.cant_per_ing_brutos.value;	
 
 for(i=0;i<cant_per_ing_brutos;i++)
 {
   aux=eval("document.all.monto_1_"+i+".value");
   if(aux=="")
    aux=0;
   acum+=parseFloat(aux);
 }

 document.all.total_per_ib.value=formato_BD(acum);
 calcular_suma_debe();
}

//funcion que calcula el total de retencion bancaria
function calcular_ret_bancaria()
{var i,aux;
 var acum=0;
 var cant_ret_bancaria;	
 cant_ret_bancaria=document.all.cant_ret_bancaria.value;	
 
 for(i=0;i<cant_ret_bancaria;i++)
 {
   aux=eval("document.all.monto_2_"+i+".value");
   if(aux=="")
    aux=0;
   acum+=parseFloat(aux);
 }

 document.all.total_ret_bancaria.value=formato_BD(acum);
 calcular_suma_debe();
}	

//funcion que calcula la suma del debe y setea los campos caja y suma_haber
function calcular_suma_debe()
{
 var retencion_iva;	
 var percepcion_iva;
 var retencion_imp_ganancias;
 var total_ret_ib;
 var total_per_ib;
 var total_ret_bancaria;
 var total;
 
 if(document.all.retencion_iva.value=="")
  retencion_iva=0;
 else 
  retencion_iva=parseFloat(document.all.retencion_iva.value);	
 if(document.all.percepcion_iva.value=="")
  percepcion_iva=0;
 else 
  percepcion_iva=parseFloat(document.all.percepcion_iva.value);	
 if(document.all.retencion_imp_ganancias.value=="") 
  retencion_imp_ganancias=0;	
 else 
  retencion_imp_ganancias=parseFloat(document.all.retencion_imp_ganancias.value);	
 if(document.all.total_ret_ib.value=="")
  total_ret_ib=0;
 else
  total_ret_ib=parseFloat(document.all.total_ret_ib.value);	
 if(document.all.total_per_ib.value=="")
  total_per_ib=0;
 else 	
  total_per_ib=parseFloat(document.all.total_per_ib.value);	
 if(document.all.total_ret_bancaria.value=="")
  total_ret_bancaria=0;
 else 	
  total_ret_bancaria=parseFloat(document.all.total_ret_bancaria.value);	
 
 total=retencion_iva+percepcion_iva+retencion_imp_ganancias+
                                         total_ret_ib+total_per_ib+total_ret_bancaria
 document.all.suma_debe.value=formato_money(total);
 document.all.caja.value=formato_BD(total);
 document.all.suma_haber.value=formato_money(total);
 
}	

//funcion que controla que los campos obligatorios sean llenados
function control_campos()
{var msg;
 var faltan;
 var i,aux,aux1;
 faltan=0;
 msg="Faltan llenar los siguientes campos\n";
 msg+="-------------------------------------------\n";
 
 if(document.all.retencion_iva.value=="")
 {faltan=1;
  msg+="Retención I.V.A.\n";
 }
 if(document.all.percepcion_iva.value=="")
 {faltan=1;
  msg+="Pecepción I.V.A.\n";
 }
 if(document.all.retencion_imp_ganancias.value=="")
 {faltan=1;
  msg+="Retención Impuesto a las Ganancias\n";
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
 
 //control de percepcion ingresos brutos
 var cant_per_ing_brutos;	
 cant_per_ing_brutos=document.all.cant_per_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {aux=eval("document.all.monto_1_"+i);
  aux1=eval("document.all.dist_1_"+i);
  if(aux.value=="")
  {faltan=1;
   msg+="Percepción Ingresos Brutos "+aux1.value+"\n";
  } 
 }//del for
 
 //control de retencion bancaria
 var cant_ret_bancaria;	
 cant_ret_bancaria=document.all.cant_ret_bancaria.value;	
 for(i=0;i<cant_ret_bancaria;i++)
 {aux=eval("document.all.monto_2_"+i);
  aux1=eval("document.all.dist_2_"+i);
  if(aux.value=="")
  {faltan=1;
   msg+="Retención Bancaria "+aux1.value+"\n";
  } 
 }//del for
 
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
{var i,aux;
 var acum=0;
 var cant_ret_bancaria;	
	
 document.all.retencion_iva.readOnly=0;
 document.all.percepcion_iva.readOnly=0;
 document.all.retencion_imp_ganancias.readOnly=0;

 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {aux=eval("document.all.monto_0_"+i);
  aux.readOnly=0; 
 }
 
 cant_per_ing_brutos=document.all.cant_per_ing_brutos.value;	
 for(i=0;i<cant_per_ing_brutos;i++)
 {aux=eval("document.all.monto_1_"+i);
  aux.readOnly=0; 
 }
 
 cant_ret_bancaria=document.all.cant_ret_bancaria.value;	
 for(i=0;i<cant_ret_bancaria;i++)
 {aux=eval("document.all.monto_2_"+i);
  aux.readOnly=0; 
 }
 
}

function deshabilitar_edicion()
{var i,aux;
 var acum=0;
 var cant_ret_bancaria;	
	
 document.all.retencion_iva.readOnly=1;
 <?
 if($actualizar)
 {?>
 document.all.percepcion_iva.readOnly=1;
 <?
 }
 ?>
 document.all.retencion_imp_ganancias.readOnly=1;

 cant_ret_ing_brutos=document.all.cant_ret_ing_brutos.value;	
 for(i=0;i<cant_ret_ing_brutos;i++)
 {aux=eval("document.all.monto_0_"+i);
  aux.readOnly=1; 
 }
 <?
 if($actualizar)
 {?>
 cant_per_ing_brutos=document.all.cant_per_ing_brutos.value;	
 for(i=0;i<cant_per_ing_brutos;i++)
 {aux=eval("document.all.monto_1_"+i);
  aux.readOnly=1; 
 }
 <?
 }
 ?>
 
 cant_ret_bancaria=document.all.cant_ret_bancaria.value;	
 for(i=0;i<cant_ret_bancaria;i++)
 {aux=eval("document.all.monto_2_"+i);
  aux.readOnly=1; 
 }
 
}	

</script>
<?
if($actualizar)
{$query="select id_retenciones,tipo,fecha,usuario 
 from log_retenciones where id_retenciones=$id_retenciones order by fecha DESC";
 $log_info=sql($query,"<br>Error al traer el log de retenciones<br>") or fin_pagina();
 ?>
<center>
<div align="right" style='position:relative; width:95%; height:10%; overflow:auto;'>
 <table  width="100%">
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
</center>
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
   <font size="3">Retenciones</font>
  </td>
 </tr>
</table>
<form name="form1" action="retenciones.php" method="POST" <?=$disabled_form?>>
<input type="hidden" name="actualizar" value="<?=$actualizar?>">
<input type="hidden" name="id_retenciones" value="<?=$id_retenciones?>">
<input type="hidden" name="cambios" value="0">
<input type="hidden" name="cant_ret_ing_brutos" value="<?=sizeof($valores_ret_ing_brutos)?>">
<input type="hidden" name="cant_per_ing_brutos" value="<?=sizeof($valores_per_ing_brutos)?>">
<input type="hidden" name="cant_ret_bancaria" value="<?=sizeof($valores_ret_bancaria)?>">
<table align="center" width="95%" class="bordes">
 <tr> 
  <td colspan="6">
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
     <td align="right" colspan="2">
      <table border="1" width="60%">
       <tr>
        <td>
         <font color="Blue"><b>Período</b></font>
        </td>
        <td>
         <b>Mes</b>&nbsp; 
         <select name=mes onchange="document.all.guardar.disabled=1">
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
         <select name=año onchange="document.all.guardar.disabled=1">
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
      <font color="Blue"><b>Valor Dolar</b></font> <input type="text" name="valor_dolar" value="<?=$valor_dolar?>" size="6" onkeypress="return filtrar_teclas(event,'0123456789.');" onchange="document.all.guardar.disabled=1">
     </td>
     <td>
      <input type="submit" name="traer_datos" value="Traer Datos" 
          onclick="if(document.all.valor_dolar.value=='')
                     {alert('Debe ingresar un Valor Dolar para traer los datos'); 
                      return false;
                     }
                     else 
                     { 
	                  if(document.all.cambios.value==1)
                      {if(confirm('Ha realizado cambios en este Asiento de Retenciones. Si continúa se perderán los cambios.\n¿Está Seguro que desea continuar?'))
                        return true;
                       else
                       { 
                        return false; 
                       }
                      }
                     }
                     return true;
                    
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
   <td colspan="6" align="center">
    <font size='3' color='red'><b>Seleccione el período del asiento de retenciones que desea completar y presione el botón traer datos</b></font>
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
   Descripción
  </td>
  <td width="20%">
   Detalle
  </td>
  <td width="15%">
   DEBE
  </td>
  <td width="15%">
   HABER
  </td>
 </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      45
     </td>
     <td width="40%">
      Retención I.V.A.
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="retencion_iva" value="<?=$retencion_iva?>" onchange="calcular_suma_debe();hay_cambios();" readonly size="13" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      46
     </td>
     <td width="40%">
      Percepción I.V.A.
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="percepcion_iva" value="<?=$percepcion_iva?>"  size="13" <?if($actualizar) echo "readonly"?>  onchange="calcular_suma_debe();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>   
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      55
     </td>
     <td width="40%">
      Retención Impuesto a las Ganancias
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="retencion_imp_ganancias" readonly value="<?=$retencion_imp_ganancias?>"  size="13"  onchange="calcular_suma_debe();hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      50
     </td>
     <td width="40%">
      <b>Retención Ingresos Brutos</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="total_ret_ib"  readonly value="<?=$total_ret_ib?>"  size="13">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <?=generar_lista_provincias(0,$valores_ret_ing_brutos)?>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      51
     </td>
     <td width="40%">
      <b>Percepción Ingresos Brutos</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="total_per_ib" value="<?=$total_per_ib?>" readonly size="13">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <?=generar_lista_provincias(1,$valores_per_ing_brutos)?>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      52
     </td>
     <td width="40%">
      <b>Retención Bancaria</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="total_ret_bancaria" value="<?=$total_ret_bancaria?>" readonly size="13">
     </td>
     <td width="15%">
      &nbsp;
     </td>
    </tr>
    <?=generar_lista_provincias(2,$valores_ret_bancaria)?>
    <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr bgcolor="<?=$bgcolor_out?>"> 
     <td width="10%">
      50
     </td>
     <td width="40%">
      <b>CAJA</b>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="caja"  readonly value="<?=$caja?>"  size="13">
     </td>
    </tr>
     <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
     <tr bgcolor="<?=$bgcolor_out?>">
     <td colspan="5">
      &nbsp;
     </td>
    </tr>
    <tr  bgcolor="White"> 
     <td width="10%">
      &nbsp;
     </td>
     <td width="40%">
      <font color="Blue"><b>Totales</b></font>
     </td>
     <td width="20%">
      &nbsp;
     </td>
     <td width="15%">
      <input type="text" name="suma_debe"  readonly value="<?=$suma_debe?>" class="text_8"  size="13">
     </td>
     <td width="15%">
      <input type="text" name="suma_haber"  readonly value="<?=$suma_haber?>" class="text_8" size="13">
     </td>
    </tr>
    
</table>
<table align="center" border="1" width="95%">
 <tr>
  <?
  if($id_retenciones && permisos_check("inicio","permiso_boton_borrar_asientos"))
  {
   ?>
   <td width="1%">
     <input type="submit" name="Borrar"   value="Borrar" onclick="return confirm ('Se borraran los datos de este asiento.\n¿Está seguro que desea continuar?');">
   </td>
  <?
  }
 ?>
  <td align="<?if($actualizar) echo "right";else echo "center"?>">
   <?
   if(!permisos_check("inicio","permiso_boton_guarda_retenciones"))
    $disabled_permiso="disabled";
   ?>
   <input type="submit" name="guardar" <?=$disabled_permiso?> <?=$disabled_form?> value="Guardar" onclick="return control_campos();">
  </td>
  <?
  if($actualizar)
  {
   $link_imprimir=encode_link("imprimir_retenciones.php",array("id_retenciones"=>$id_retenciones));
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
//calculamos el total de retencion ingresos brutos
calcular_ret_ing_brutos();
//calculamos el total de percepcion ingresos brutos
calcular_per_ing_brutos();
//calculamos el total de retencion bancaria
calcular_ret_bancaria();
//calculamos la suma del debe y el haber
calcular_suma_debe();
</script>

<br>
<?fin_pagina();?>