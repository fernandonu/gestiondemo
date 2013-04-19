<?php
/*
$Author: nazabal $
$Revision: 1.85 $
$Date: 2007/05/04 21:25:33 $
*/

require_once("../../config.php");
require_once("funciones.php");


$nro_legajo = $parametros["nro_legajo"]  or $nro_legajo = $_POST["nro_legajo"];
$pagina     = $parametros['pagina'];
$id_sueldo  = $parametros['id_sueldo'];
$id         = $parametros['id'];
$fecha_hoy  = fecha_db(date("d/m/Y",mktime()));
$fecha      = fecha_db("01/04/2005");

if ($_POST["pendientes"]){
   $sql="update sueldos set estado_liquidacion=0 where id_sueldo=$id_sueldo";
   sql($sql) or fin_pagina();
   }
   
   
if ($_POST["guardar"]){
   
  
    $db->StartTrans();
    
    $mes = $_POST['mes'];
    $año = $_POST['año'];
    $fecha = fecha_db("08/$mes/$año");      
    $fecha_pago = fecha_db($_POST['fecha_pago']);
    if (!FechaOk($_POST['fecha_pago'])) error ("Fecha Inválida");
    
    $fecha_jubilacion = fecha_db($_POST['fecha_jubilacion']);
    if (!FechaOk($_POST['fecha_jubilacion']))  error ("Fecha Inválida");
                  
    $basico = $_POST['h_basico'];
    $dec1529 = $_POST['h_dec1529'];
    $acuenta = $_POST['h_acuenta'];
    $presentismo = $_POST['h_presentismo'];
    ($_POST["no_tiene_presentismo"])?$no_tiene_presentismo=1:$no_tiene_presentismo=0;
    $vacaciones_dias = $_POST['h_vacaciones_dias'];
    $vacaciones = $_POST['h_vacaciones'];
    $jubilacion = $_POST['h_jubilacion'];
    $ley19032   = $_POST['h_ley19032'];
    $obra_social= $_POST['h_obra_social'];
    $sindicato  = $_POST['h_sindicato'];
    $sindicato_familiar = $_POST['h_sindicato_familiar'];
    $faecys = $_POST['h_faecys'];
    $salario_familiar = $_POST['h_salario_familiar'];
    $dec1347 = $_POST['h_dec1347'];
    $vac_ng  =  $_POST['h_vac_ng'];
    $sac_sobre_vng = $_POST['h_sac_sobre_vng'];
    $ausentismo=$_POST['h_ausentismo'];          
    $dias_inasistencia  = $_POST['h_dias_inasistencia'];
    $total_inasistencia = $_POST['h_total_inasistencia'];
    $vac = $_POST['h_vac'];
    $total_vac = $_POST['h_total_vac'];
    $dias_dec1347=$_POST['h_dias_dec1347'];
    $dias_dec2005=$_POST['h_dias_dec2005'];
    $dec2005_04=$_POST['h_dec2005'];
    $sac_indemnizacion=$_POST['h_sac_indemnizacion'];
    $sac_preaviso = $_POST['h_sac_preaviso'];    
    if ($_POST['tipo_jub']==0)  $tipo_jub=0;
                   elseif ($_POST['tipo_jub']==1)   $tipo_jub=1;
                           else $tipo_jub=0;
    $sac = $_POST['h_sac'];                           
    if ($sac=="")
             {
      	      $sac=0;
    	      $mes_sac=0;
    	      $dias_sac=0;
              }
              else {
                $mes_sac=$_POST['h_mes_sac'];
                $dias_sac=$_POST['h_dias_sac'];
                }


    ($_POST["h_horas"])?$horas=$_POST["h_horas"]:$horas=0;    
    ($_POST["h_valor_hora"])?$valor_hora=$_POST["h_valor_hora"]:$valor_hora=0;    
    ($_POST["h_horas_extras"])?$horas_extras=$_POST["h_horas_extras"]:$horas_extras=0;    
    ($_POST["h_tipo_hora_extras"])?$tipo_hora_extras=$_POST["h_tipo_hora_extras"]:$tipo_hora_extras=0;    
    ($_POST["sueldo_por_horas"])?$sueldo_por_horas=$_POST["sueldo_por_horas"]:$sueldo_por_horas=0;    
    
    
    ($_POST["h_anticipo"])?$anticipo=$_POST["h_anticipo"]:$anticipo=0;    
    ($_POST["h_gratif"])?$gratificacion=$_POST["h_gratif"]:$gratificacion=0;
    ($_POST["h_indem"])?$indemnizacion=$_POST["h_indem"]:$indemnizacion=0;                
    ($_POST["h_pre_aviso"])?$pre_aviso=$_POST["h_pre_aviso"]:$pre_aviso=0;                
    ($_POST['h_ajuste_anterior'])?$ajuste_anterior=$_POST['h_ajuste_anterior']:$ajuste_anterior=0;
    ($_POST["h_cantidad_dias_feriados"])?$cantidad_dias_feriados=$_POST["h_cantidad_dias_feriados"]:$cantidad_dias_feriados=0;
    ($_POST["h_feriados"])?$feriados=$_POST["h_feriados"]:$feriados=0;    
    ($_POST["h_ayuda"])?$ayuda=$_POST["ayuda"]:$ayuda=0;
    ($_POST["pasar_historial"]==1)?$estado_liq=1:$estado_liq=0;
    ($_POST["h_embargo"])?$embargo=$_POST["h_embargo"]:$embargo=0;
    ($_POST["h_acuerdo_abril_2006"])?$acuerdo_abril_2006=$_POST["h_acuerdo_abril_2006"]:$acuerdo_abril_2006=0;
    switch ($_POST['liq_tipo']){
                case 1:
                       $tipo_liq=1;
                       break;
                case 2:
                       $tipo_liq=2;
                       break;
                 default: 
                       $tipo_liq=0;
                       break;
     }
     
    
    //determino que accion hago
    
    
    if (!$error) {
    
            switch($pagina){
                
             case "modificar_legajo.php":
                            $sql = "INSERT INTO sueldos (fecha,id_legajo,basico,dec1529,
                                              acuenta,vacaciones_dias,salario_familiar,dec1347,
                                              presentismo,vacaciones,jubilacion,ley19032,obra_social,
                                              sindicato,sindicato_familiar,faecys,estado_liquidacion,ausentismo,
                                              fecha_pago,fecha_jubilacion,ayuda_escolar,sac,mes_sac,dias_sac,gratificacion,vac_no_gozadas,
                                              dias_inasistencia,total_inasistencia,dias_vac,total_vac,indemnizacion,pre_aviso,sac_sobre_vng,sac_sobre_preaviso, 
                                              dec2005_04,dias_dec1347,dias_dec2005,sac_sobre_indemnizacion, anticipo,ajuste_anterior, 
                                              cantidad_dias_feriados,feriados,
                                              sueldo_por_horas,horas,valor_hora,horas_extras,tipo_hora_extras,embargo,acuerdo_abril_2006,
                                              no_tiene_presentismo
                                              )
                                              VALUES ('$fecha',$id,$basico,$dec1529,
                                              $acuenta,$vacaciones_dias,$salario_familiar,$dec1347,
                                              $presentismo,$vacaciones,$jubilacion,$ley19032,$obra_social,
                                              $sindicato,$sindicato_familiar,$faecys,0,$ausentismo, 
                                              '$fecha_pago','$fecha_jubilacion',$ayuda,$sac,$mes_sac,$dias_sac,$gratificacion,$vac_ng, 
                                              $dias_inasistencia,$total_inasistencia,$vac,$total_vac,$indemnizacion,$pre_aviso,$sac_sobre_vng,$sac_preaviso, 
                                              $dec2005_04,$dias_dec1347,$dias_dec2005, $sac_indemnizacion, $anticipo,$ajuste_anterior,
                                              $cantidad_dias_feriados,$feriados,
                                              $sueldo_por_horas,$horas,$valor_hora,$horas_extras,$tipo_hora_extras,$embargo,$acuerdo_abril_2006,
                                              $no_tiene_presentismo
                                              )";
                            
                            
                             sql($sql,"Error al crear el recibo de sueldos") or fin_pagina();  
                         
                             $sql="update legajos set tipo_liq=$tipo_liq, tipo_jub=$tipo_jub where id_legajo=$id ";
                             sql($sql,"Error: Ha ocurrido un error al modificar el legajo")or fin_pagina();
                            
                             $accion="Los datos se guardaron con Exito";   
                             $link=encode_link('../personal/listado_legajos.php',array("accion"=>$accion));
                             break;
                             
             case "listado_liq_sueldo.php":
             
                                       
                              $sql="update sueldos set fecha='$fecha',id_legajo=$id,basico=$basico,
                                          dec1529=$dec1529,acuenta=$acuenta,vacaciones_dias=$vacaciones_dias,
                                          salario_familiar=$salario_familiar,dec1347=$dec1347,presentismo=$presentismo,
                                          vacaciones=$vacaciones,jubilacion=$jubilacion,ley19032=$ley19032,
                                          obra_social=$obra_social,sindicato=$sindicato,sindicato_familiar=$sindicato_familiar,
                                          faecys=$faecys,estado_liquidacion=$estado_liq,ausentismo=$ausentismo, fecha_pago='$fecha_pago', 
                                          fecha_jubilacion='$fecha_jubilacion', ayuda_escolar=$ayuda, 
                                          sac=$sac,mes_sac=$mes_sac,dias_sac=$dias_sac, 
                                          gratificacion=$gratificacion,vac_no_gozadas=$vac_ng, 
                                          dias_inasistencia=$dias_inasistencia,total_inasistencia=$total_inasistencia, 
                                          dias_vac=$vac,total_vac=$total_vac,indemnizacion=$indemnizacion,pre_aviso=$pre_aviso, 
                                          sac_sobre_vng=$sac_sobre_vng,sac_sobre_preaviso=$sac_preaviso, 
                                          dec2005_04=$dec2005_04,dias_dec1347=$dias_dec1347,dias_dec2005=$dias_dec2005, sac_sobre_indemnizacion=$sac_indemnizacion, 
                                          cantidad_dias_feriados=$cantidad_dias_feriados,feriados=$feriados,
                                          anticipo=$anticipo,ajuste_anterior=$ajuste_anterior,
                                          horas=$horas,valor_hora=$valor_hora,horas_extras=$horas_extras,tipo_hora_extras=$tipo_hora_extras,sueldo_por_horas=$sueldo_por_horas,
                                          embargo=$embargo,acuerdo_abril_2006=$acuerdo_abril_2006,no_tiene_presentismo=$no_tiene_presentismo
                                          where id_sueldo=$id_sueldo";
                              sql($sql,"Error al modificar el recibo") or fin_pagina();                                  
                              
                              $sql="update legajos set tipo_liq=$tipo_liq, tipo_jub=$tipo_jub where id_legajo=$id ";
                              sql($sql,"Error al modificar el legajo")or fin_pagina();
                              $accion="Los datos se guardaron con Exito";
                              $link=encode_link('./listado_liq_sueldo.php',array("accion"=>$accion));     

                              break;         
             }//del swtich
    
    }   
    
    
  $db->CompleteTrans();
  header("Location:$link") or die();  
} //del post de guardar
    



    //recupero el sueldo que se tiene que imprimir - estado pendiente
if ($pagina=="modificar_legajo.php")
   $where="id_legajo=$id";
//   $where="id_legajo=$id order by fecha desc";
   else 
     $where="id_sueldo=$id_sueldo";
//     $where="id_sueldo=$id_sueldo order by fecha desc";
    
   $sql="select * from legajos left join sueldos using (id_legajo) where $where";
   $result = sql($sql) or fin_pagina();
   $result->MoveLast();
   $id = $result->fields['id_legajo'];
   //esto valores los voy a usar en los hidden para las funciones cuando los tiene que recuperar
   $basico    = $result->fields['basico'];
   $dec1529   = $result->fields['dec1529'];
   $acuenta   = $result->fields['acuenta'];
   $presentismo = $result->fields['presentismo'];
   $no_tiene_presentismo = $result->fields['no_tiene_presentismo'];
   $vacaciones_dias = $result->fields['vacaciones_dias'];
   $vacaciones = $result->fields['vacaciones'];
   $jubilacion = $result->fields['jubilacion'];
   $obra_social = $result->fields['obra_social'];
   $ley19032 = $result->fields['ley19032'];
   $sindicato = $result->fields['sindicato'];
   $sindicato_familiar = $result->fields['sindicato_familiar'];
   $dec1347 = $result->fields['dec1347'];
   $salario_familiar = $result->fields['salario_familiar'];
   $faecys = $result->fields['faecys'];
   $estado = $result->fields['estado_liquidacion'];
   $ausentismo = $result->fields['ausentismo'];
   $ayuda = $result->fields['ayuda_escolar'];
   $gratificacion = $result->fields['gratificacion'];
   $vac_ng = $result->fields['vac_no_gozadas'];
   $sac_sobre_vng = $result->fields['sac_sobre_vng'];
   $dias_inasistencia = $result->fields['dias_inasistencia'];
   $total_inasistencia = $result->fields['total_inasistencia'];
   $total_vac = $result->fields['total_vac'];
   $vac = $result->fields['dias_vac'];
   $indemnizacion = $result->fields['indemnizacion'];
   $sac_indemnizacion = $result->fields['sac_sobre_indemnizacion'];
   $pre_aviso = $result->fields['pre_aviso'];
   $sac_preaviso = $result->fields['sac_sobre_preaviso'];
   $dec2005_04 = $result->fields['dec2005_04'];
   $fecha_pago = $result->fields['fecha_pago'];
   $anticipo   = $result->fields['anticipo'];
   $ajuste_anterior = $result->fields["ajuste_anterior"];
   $cantidad_dias_feriados = $result->fields["cantidad_dias_feriados"];
   $feriados = $result->fields["feriados"];
   $apellido = $result->fields['apellido'];
   $nombre   = $result->fields['nombre'];
   $fecha_anterior = $result->fields['fecha'];
   $tipo_liq = $result->fields['tipo_liq'];
   $tipo_jub = $result->fields['tipo_jub'];
   list($año, $mes, $dia)=split('[-]', $fecha_anterior);
   $fecha_pago = $result->fields['fecha_pago'];
   $fecha_jubilacion = $result->fields['fecha_jubilacion'];
   $sac = $result->fields['sac'];
   $mes_sac = $result->fields['mes_sac'];
   $dias_sac = $result->fields['dias_sac'];
   $dias_dec1347 = $result->fields['dias_dec1347'];
   $dias_dec2005 = $result->fields['dias_dec2005'];
   $anticipo = $result->fields['anticipo'];
   $horas=$result->fields["horas"];
   $valor_hora=$result->fields["valor_hora"];
   $horas_extras=$result->fields["horas_extras"];
   $tipo_hora_extras=$result->fields["tipo_hora_extras"];
   $sueldo_por_horas=$result->fields["sueldo_por_horas"];
   $embargo=$result->fields["embargo"];
   $acuerdo_abril_2006=$result->fields["acuerdo_abril_2006"];

   $db->SetFetchMode(ADODB_FETCH_ASSOC);
   $datos=$result->fields; 
   $arreglo=calculo_valores($datos);

?>
<script src="../../lib/popcalendar.js"></script>
<script language="javascript">

//funcion para controlar que se ingresaron correctamente todos los datos
function sacar_sac(){
if (document.all.chequeo2.checked==0){
	document.all.mes_sac.options[0].selected=1;
    document.all.dias_sac.options[0].selected=1;
}
}


function control_fecha_deposito(){
 if (document.all.fecha_jubilacion.value==""){
   alert("Falta Fecha de Depósito");
   return false;
   };
return true;
}

/*
function control_fecha(){
var fecha_aux=new Array();
var aux;
var fecha=document.all.fecha_pago.value;
fecha_aux=fecha.split("/");
if (parseInt(fecha_aux[0]) >= 6){
  alert("Fecha de pago incorrecta");
  return false;
  };
return control_fecha_deposito();
//return aux;
}
*/

function control_datos_total(){
if (document.all.sueldo_por_horas.checked){

    if(document.all.horas.value==""){
      alert('Falta Ingresar las horas');
      return false;
    }
    if (document.all.valor_hora.value==""){
      alert('Falta ingresar el valor de las horas');
      return false
    }
}
else{
  if (document.all.basico.value==""){
    alert("Falta ingresar el Sueldo Básico");
    return false;
    }
   if (document.all.decreto.value==""){
     document.all.decreto.value=0;
     }
 }    
if (document.all.a_cuenta.value==""){
  document.all.a_cuenta.value=0;
  }
if (document.all.dias.value==""){
  document.all.dias.value=0;
  }
if (document.all.dias_vac.value==""){
  document.all.dias_vac.value=0;
  }
if (document.all.dias_inasistencia.value==""){
  document.all.dias_inasistencia.value=0;
  }    
return true;
}


function formato_money(valor){
var numero=new  NumberFormat();
 numero.setNumber(parseFloat(valor));
 numero.setInputDecimal(numero.PERIOD);
 numero.setCurrency(false);
 numero.setSeparators(true, numero.PERIOD)
 return numero.toFormatted();
}

// funciones para calcular

function calcular_subtotal(){
//C=A+B subtotal

 var A,B,C;
 var mostrar_dec_1347;
 var dec_1347;
 var horas,valor_hora,horas_extras,tipo_hora_extras;

 mostrar_dec_1347=document.all.mostrar_dec_1347.value;
 if (mostrar_dec_1347=='dec_rem') {
        dec_1347=parseFloat(document.all.dec_1347_r.value);
        document.all.h_dec1347.value=document.all.dec_1347_r.value;
        }
        else {
        dec_1347=0;
        document.all.h_dec1347.value=0;
        }
 
if (document.all.sueldo_por_horas.checked)
           {
            horas=parseFloat(document.all.horas.value);
            valor_hora=parseFloat(document.all.valor_hora.value);
            
            A=horas*valor_hora;
            B=0;
            document.all.sueldo_por_horas.value=1;
            document.all.h_basico.value=0;
            document.all.h_dec1529.value=B;    
            document.all.h_horas.value=horas;
            document.all.h_valor_hora.value=valor_hora;
//            document.all.h_horas_extras.value=0;
//            document.all.h_tipo_hora_extras.value=0;
            
//            if (document.all.horas_extras.value!="")
//                     {
//                     horas_extras=parseFloat(document.all.horas_extras.value);
//                     
//                     if (document.all.tipo_hora_extras[0].checked)
//                                                  tipo_hora_extras=1.5;
//                     if (document.all.tipo_hora_extras[1].checked)
//                                                  tipo_hora_extras=2;
//                     total_horas_extras=horas_extras*valor_hora*tipo_hora_extras;
//                     
//                     document.all.h_horas_extras.value=horas_extras;
//                     document.all.h_tipo_hora_extras.value=tipo_hora_extras;
//                     //A=A+total_horas_extras;
//                     }
    
}
else{ 
                     A=parseFloat(document.all.basico.value);
                     B=parseFloat(document.all.decreto.value);
                     document.all.h_basico.value=A;
                     document.all.h_dec1529.value=B;
                     document.all.sueldo_por_horas.value=0;
//            if (document.all.horas_extras.value!="")
//                     {
//                     horas_extras=parseFloat(document.all.horas_extras.value);
//                     
//                     if (horas_extras > 0) {
//	                     if (document.all.tipo_hora_extras[0].checked)
//	                                                  tipo_hora_extras=1.5;
//	                     if (document.all.tipo_hora_extras[1].checked)
//	                                                  tipo_hora_extras=2;
//	                                                  
//	                     if (isNaN(document.all.subtotal2.value))
//	                     	valor_hora = document.all.h_subtotal2.value/200
//	                     else
//	                     	valor_hora = document.all.subtotal2.value/200;
//	                     	
//	                     total_horas_extras=horas_extras*valor_hora*tipo_hora_extras;
//	                     
//	                     document.all.h_horas_extras.value=horas_extras;
//	                     document.all.h_tipo_hora_extras.value=tipo_hora_extras;
//                     }
//                     }
}
 
 
 C=A+B+dec_1347;
 document.all.h_subtotal.value=C;
 document.all.subtotal.value=formato_money(C);
} // del calcular sub_total()

function calcular_horas_extras(){
	var valor_hora,horas_extras,tipo_hora_extras,total_horas_extras;
	 
	total_horas_extras=0;
	
	if (document.all.horas_extras.value!="") {
		horas_extras=parseFloat(document.all.horas_extras.value);
		if (document.all.sueldo_por_horas.checked)
			valor_hora=parseFloat(document.all.valor_hora.value);
		else
			valor_hora = (parseFloat(document.all.h_subtotal.value)+parseFloat(document.all.h_acuenta.value))/200;
        if (horas_extras > 0) {             
			if (document.all.tipo_hora_extras[0].checked)
				tipo_hora_extras=1.5;
			if (document.all.tipo_hora_extras[1].checked)
				tipo_hora_extras=2;
			total_horas_extras=horas_extras*valor_hora*tipo_hora_extras;

			document.all.h_horas_extras.value=horas_extras;
			document.all.h_tipo_hora_extras.value=tipo_hora_extras;
        }
        else {
			document.all.h_horas_extras.value=0;
			document.all.h_tipo_hora_extras.value=0;
			document.all.horas_extras.value=0;
        }
	}
    else {
		document.all.h_horas_extras.value=0;
		document.all.h_tipo_hora_extras.value=0;
		document.all.horas_extras.value=0;
    }
	 
	document.all.h_subtotal_horas.value=total_horas_extras;
	document.all.subtotal_horas.value=formato_money(total_horas_extras);
} // del calcular_horas_extras()

function calcular_presentismo(){
//D=C*0.0834  presentismo
 var D,C,AUX;
 
  C=parseFloat(document.all.h_subtotal.value);

  //if ((parseFloat(document.all.dias_inasistencia.value)<=0) && (!document.all.sueldo_por_horas.checked))
  if (!document.all.sueldo_por_horas.checked && !document.all.no_tiene_presentismo.checked)
       D=C*0.0834;
       else
       D=0;
  
       document.all.h_presentismo.value=D;
       document.all.presentismo.value=formato_money(D);
 
}

function calcular_subtotal2(){

 //Agrego Ajuste Anterior var AA
//F=C+D+E  subtotal2
 var D,E,F,C,AA,Feriados,horas_extras;
 
 E=parseFloat(document.all.a_cuenta.value);
 AA=parseFloat(document.all.ajuste_anterior.value);
 document.all.h_ajuste_anterior.value=document.all.ajuste_anterior.value;
 if (isNaN(AA))
                 {
                  AA=0;
                  document.all.h_ajuste_anterior.value=0;
                  }


 E=parseFloat(document.all.a_cuenta.value);
 document.all.h_acuenta.value=document.all.a_cuenta.value;

             
 C=parseFloat(document.all.h_subtotal.value);
 D=parseFloat(document.all.h_presentismo.value);
 horas_extras=parseFloat(document.all.h_subtotal_horas.value);
// Feriados=parseFloat(document.all.h_feriados.value);

 F=C+D+E + AA+horas_extras;
 document.all.h_subtotal2.value=F ;
 document.all.subtotal2.value=formato_money(F);
}



function calcular_feriados(){

var cantidad_dias_feriados,feriados,subtotal;

  

 cantidad_dias_feriados=parseInt(document.all.select_feriados.value);
 document.all.h_cantidad_dias_feriados.value=cantidad_dias_feriados;
 subtotal = 0;
 subtotal=parseFloat(document.all.h_subtotal2.value);

 
 if (cantidad_dias_feriados)
            feriados=((subtotal/25) * cantidad_dias_feriados);
            else
            feriados=0;
  
  document.all.h_feriados.value=feriados;
  document.all.feriados.value=formato_money(feriados);
  
}



function calcular_vacaciones_adicional(){
 var W,X;
 W=parseFloat(document.all.dias_vac.value);
 <?
 if($pagina=="modificar_legajo.php"){
 ?>
 W=parseFloat(document.all.dias_vac.value);
 document.all.h_vac.value=document.all.dias_vac.value;
 <?}
   else {
 ?>
         if(document.all.h_vac.value==document.all.dias_vac.value)
           W=parseFloat(document.all.h_vac.value);
         else{
           W=parseFloat(document.all.dias_vac.value);
           document.all.h_vac.value=document.all.dias_vac.value;}
 <?
  }
  ?>
 F=parseFloat(document.all.h_subtotal2.value);
 X=(F*W)/25;
 document.all.h_total_vac.value=X;
 document.all.total_vac.value=formato_money(X);
}

function calcular_vacaciones(){
//J=(F/25)*G   vacaciones  uso 30 para hacer una prueba
//con 30 el valor se ausentismo que se descuenta es mayor
 var F,G,J;
 <?if($pagina=="modificar_legajo.php"){?>
 G=parseFloat(document.all.dias.value);
 document.all.h_vacaciones_dias.value=document.all.dias.value;
 <?} 
 else {?>
     if(document.all.h_vacaciones_dias.value==document.all.dias.value)
       G=parseFloat(document.all.h_vacaciones_dias.value);
     else{
       G=parseFloat(document.all.dias.value);
       document.all.h_vacaciones_dias.value=document.all.dias.value;
       }
     
 <?}?>
 F=parseFloat(document.all.h_subtotal2.value);
 J=(F/25)*G;
 document.all.h_vacaciones.value=J;
 document.all.vacaciones.value=formato_money(J);
 
}

function calcular_ausentismo(){
//Jbis=-(F/30)*G   ausentismo   uso 30 como prueba
 var F,G,Jbis;
 <?if($pagina=="modificar_legajo.php"){?>
 G=parseFloat(document.all.dias.value);
 document.all.h_vacaciones_dias.value=document.all.dias.value;
 <?} else {?>
 if(document.all.h_vacaciones_dias.value==document.all.dias.value)
   G=parseFloat(document.all.h_vacaciones_dias.value);
 else{
   G=parseFloat(document.all.dias.value);
   document.all.h_vacaciones_dias.value=document.all.dias.value;}
 <?}?>
 F=parseFloat(document.all.h_subtotal2.value);
 Jbis=-(F/30)*G;
 document.all.h_ausentismo.value=Jbis;
 document.all.ausentismo.value=formato_money(Jbis);
}

function calcular_inasistencia(){
//(SUBTOTAL2 * CANTIDAD_DIAS_INASISTENCIA)/30
 var W,F,Y;
 <?if($pagina=="modificar_legajo.php"){?>
 W=parseFloat(document.all.dias_inasistencia.value);
 document.all.h_dias_inasistencia.value=document.all.dias_inasistencia.value;
 <?} else {?>
 if(document.all.h_dias_inasistencia.value==document.all.dias_inasistencia.value)
   W=parseFloat(document.all.h_dias_inasistencia.value);
 else{
   W=parseFloat(document.all.dias_inasistencia.value);
   document.all.h_dias_inasistencia.value=document.all.dias_inasistencia.value;}
 <?}?>
 F=parseFloat(document.all.h_subtotal2.value);
 Y=(F*W)/30;
 document.all.h_total_inasistencia.value=Y;
 document.all.inasistencia.value=formato_money(Y);
 
}

function calcular_total(){
//I=F+J+H    total
 var J,I,H,F,Feri;
 var W,Z;
 var c_mes, c_dias, mxd, cdt, sac;
 if(control_datos_total()){
  
              
              calcular_subtotal();
              calcular_horas_extras();
              calcular_presentismo();  
              calcular_subtotal2();
              calcular_feriados();              
              calcular_vacaciones();
              calcular_vacaciones_adicional(); 
              calcular_ausentismo();
              calcular_inasistencia();
       
              F=parseFloat(document.all.h_subtotal2.value);
              J=parseFloat(document.all.h_vacaciones.value);
            //cambio la h=monto por h=ausentismo y lo sumo porque ausentismo es negativo
              H=parseFloat(document.all.h_ausentismo.value);
              W=parseFloat(document.all.h_total_inasistencia.value);
              Z=parseFloat(document.all.h_total_vac.value);
              Feri=parseFloat(document.all.h_feriados.value);
              //I=F+J+H-W+Z;
              I=F+J+H +Z +Feri;
              
              //  I=F+J+H
              if(document.all.chequeo2.checked){
              
  	                    c_mes=parseInt(document.all.mes_sac.options[document.all.mes_sac.selectedIndex].value);
                        c_dias=parseInt(document.all.dias_sac.options[document.all.dias_sac.selectedIndex].value);
                        mxd=c_mes*30;
                        
                        cdt=mxd+c_dias;
                        sac=((I/2)/180)*cdt;
                        document.all.h_sac.value=1;
                        document.all.h_mes_sac.value=c_mes;
                        document.all.h_dias_sac.value=c_dias;
                        document.all.sac.value=formato_money(sac);
                        //document.all.h_total.value=I+sac;
                        //document.all.total.value=formato_money(I+sac);
                        
                       }
                       else{
                          sac=0;
                          document.all.h_sac.value=0;
                          document.all.h_mes_sac.value=0;
                          document.all.h_dias_sac.value=0;	
                          document.all.sac.value=formato_money(sac);
                          //document.all.h_total.value=I;
                          //document.all.total.value=formato_money(I);
                          }
             
             
             I=I + sac -W; 
             document.all.total.value=formato_money(I);
             document.all.h_total.value=I;  
                                  
              return true;
  }
 else return false;
}

function calcular_jubilacion(){
//K=0.07*I   jubilacion afjp
//K=0.11*I   jubilacion reparto
 var K,I;
 I=parseFloat(document.all.h_total.value);
  
 if (document.all.tipo_jub[0].checked){
  K=0.07*I;
  
 }
 else if (document.all.tipo_jub[1].checked){
  K=0.11*I;
  
 }
 document.all.h_jubilacion.value=K;
 document.all.jubilacion.value=formato_money(K);
}

function calcular_ley(){
//L=0.03*I   ley 19032
 var L,I;
 I=parseFloat(document.all.h_total.value);
 L=0.03*I;
 document.all.h_ley19032.value=L;
 document.all.ley.value=formato_money(L);
}

function calcular_ob_soc(){
//M=0.03*I   ob_soc
 var M,I;
 I=parseFloat(document.all.h_total.value);
 M=0.03*I;
 document.all.h_obra_social.value=M;
 document.all.ob_social.value=formato_money(M);
}

function calcular_sindicato(){
//N=0.02*I   sindicato
 var N,I;
 I=parseFloat(document.all.h_total.value);
 N=0.02*I;
 document.all.h_sindicato.value=N;
 document.all.sindicato.value=formato_money(N);
}

function calcular_embargo(){
 var embargo;
 
 if (document.all.embargo.value=="" || document.all.embargo.value==0)
        {
        embargo=0;
        document.all.h_embargo.value=embargo;
        document.all.embargo.value=embargo;
         }
         else{
            embargo=parseFloat(document.all.embargo.value);
            document.all.h_embargo.value=embargo;
            }
}



function calcular_sindicato_familiar(){
//O=0.015*I  sindicato familiar
//se cambia la formula x O=0.02*I 
 var O,I;
 I=parseFloat(document.all.h_total.value);
 O=0.02*I;
 document.all.h_sindicato_familiar.value=O;
 document.all.sindi_familiar.value=formato_money(O);
}

function calcular_faecys(){
//P=0.005*I  faecys
 var P,I;
 I=parseFloat(document.all.h_total.value);
 P=0.005*I;
 document.all.h_faecys.value=P;
 document.all.faecys.value=formato_money(P);
}

function calcular_total_desc(){
//Q=K+L+M+N+O+P   total descuentos
 var I,K,L,M,N,O,P,Q,Em;
 //if (calcular_total()){
 if (document.all.h_total.value){
          calcular_jubilacion();
          calcular_ley();
          calcular_ob_soc();
          calcular_sindicato();
          calcular_embargo();
          if(document.all.chequeo.checked) calcular_sindicato_familiar()
          else {
            document.all.h_sindicato_familiar.value=0;
            document.all.sindi_familiar.value=0;}
          calcular_faecys();
          I=parseFloat(document.all.h_total.value);
          K=parseFloat(document.all.h_jubilacion.value);
          L=parseFloat(document.all.h_ley19032.value);
          M=parseFloat(document.all.h_obra_social.value);
          N=parseFloat(document.all.h_sindicato.value);
          O=parseFloat(document.all.h_sindicato_familiar.value);
          P=parseFloat(document.all.h_faecys.value);
          Em=parseFloat(document.all.h_embargo.value);
          if(document.all.chequeo.checked) Q=K+L+M+N+O+P+Em;
          else  Q=K+L+M+N+P+Em;
          document.all.h_total_descuentos.value=Q;
          document.all.total_desc.value=formato_money(Q);
          return true;
 }
 else {alert("Falta calcular el total");
       return false;}
 } // de la funcion

function calcular_decreto_2005(){
// DEC2005_04 (100 * dias_trabajados)/30
var dias_trab_dec2005;
var aux_dec2005;

aux_dec2005=parseInt(document.all.dias_dec2005.options[document.all.dias_dec2005.selectedIndex].value);

  dias_trab_dec2005=parseInt(document.all.dias_dec2005.options[document.all.dias_dec2005.selectedIndex].value);
  document.all.h_dias_dec2005.value=dias_trab_dec2005;
  DEC2005_04=(100*dias_trab_dec2005)/30;
  document.all.h_dec2005.value=DEC2005_04;
  document.all.decreto2005_04.value=formato_money(DEC2005_04);
}

function calcular_decreto_1347(){
// DEC1347_03 (50 * dias_trabajados)/30
var dias_trab_dec1347;
var aux_dec1347;

aux_dec1347=parseInt(document.all.dias_dec1347.options[document.all.dias_dec1347.selectedIndex].value);

  dias_trab_dec1347=parseInt(document.all.dias_dec1347.options[document.all.dias_dec1347.selectedIndex].value);
  document.all.h_dias_dec1347.value=dias_trab_dec1347;
  DEC1347_03=(50*dias_trab_dec1347)/30;
  document.all.h_dec1347.value=DEC1347_03;
  document.all.dec_1347_nr.value=formato_money(DEC1347_03);
}


function calcular_total_no_rem(){
//U=R+S+AE   total no remunerativo
//AE=ayuda escolar
//ahora se agregan G+VNG ----> U=R+S+AE+G+VNG
//se agregan IND+PREAV ------> U=R+S+AE+G+VNG+IND+PREAV
//se debe calcular sac sobre vacaciones no gozadas es el 8,33 % del monto de las 
//vacaciones no gozadas el total se guarda en SAC_VNG
//se debe calcular sac sobre preaviso es el 8,33 % del monto del 
//preaviso el total se guarda en SAC_PREAVISO
 var U,R,AE,G,VNG,IND,PREAV,SAC_VNG,SAC_PREAVISO;
 var aux, aux_preaviso, aux_indemnizacion, aux_sac_indemnizacion;
 var aux_dec1347,aux_dec2005;
 var mostrar_dec_1347;
 var acuerdo_abril_2006;
 
 mostrar_dec_1347=document.all.mostrar_dec_1347.value;
 
 if(document.all.sal_fam.value==""){
  R=0;
  document.all.h_salario_familiar.value=0;
  }
  else{
       R=parseFloat(document.all.sal_fam.value);
       document.all.h_salario_familiar.value=R;
  }
// saco dl control sal_fam    
// if(control_datos_total_no_rem()){  
         if(document.all.ayuda.value==""){
         AE=0;
         document.all.h_ayuda.value=0;
         }
         else{
            AE=parseFloat(document.all.ayuda.value);
            document.all.h_ayuda.value=document.all.ayuda.value;
         }
  
        if (mostrar_dec_1347=='dec_no_rem') {
                 calcular_decreto_1347();
                 aux_dec1347=parseFloat(document.all.h_dec1347.value);
                 }
                 else aux_dec1347=0;
        calcular_decreto_2005();
        aux_dec2005=parseFloat(document.all.h_dec2005.value);
 
        if(document.all.gratificacion.value==""){
            G=0;
            document.all.h_gratif.value=0;
            }
            else{
            G=parseFloat(document.all.gratificacion.value);
            document.all.h_gratif.value=G;
            }
  
        if(document.all.no_vacaciones.value==""){
            VNG=0;
            document.all.h_vac_ng.value=0;
            }
            else{
            VNG=parseFloat(document.all.no_vacaciones.value);
            document.all.h_vac_ng.value=VNG;
            } 
  
         if(document.all.pre_aviso.value==""){
            PREAV=0;
            document.all.h_pre_aviso.value=0;
            }
            else{
            PREAV=parseFloat(document.all.pre_aviso.value);
            document.all.h_pre_aviso.value=PREAV;
            } 

         if(document.all.indemnizacion.value==""){
            IND=0;
            document.all.h_indem.value=0;
            }
            else{
            IND=parseFloat(document.all.indemnizacion.value);
            document.all.h_indem.value=IND;
            }  

         if(document.all.no_vacaciones.value=="" || document.all.no_vacaciones.value==0){
            SAC_VNG=0;
            document.all.h_sac_sobre_vng.value=0;
            document.all.sac_sobre_vng.value=0;
            }
            else{
            aux=parseFloat(document.all.h_vac_ng.value);	
            SAC_VNG=(aux * 8.3334)/100;
            document.all.h_sac_sobre_vng.value=SAC_VNG;
            document.all.sac_sobre_vng.value=formato_money(SAC_VNG);
            }

    
         
         if(document.all.pre_aviso.value=="" || document.all.pre_aviso.value==0){
            SAC_PREAVISO=0;
            document.all.h_sac_preaviso.value=0;
            document.all.sac_sobre_preaviso.value=0;
            }
             else{
               aux=parseFloat(document.all.h_pre_aviso.value);	
               SAC_PREAVISO=(aux * 8.3334)/100;
               document.all.h_sac_preaviso.value=SAC_PREAVISO;
               document.all.sac_sobre_preaviso.value=formato_money(SAC_PREAVISO);
             }
          if(document.all.acuerdo_abril_2006.value=="" || document.all.acuerdo_abril_2006.value==0){
            acuerdo_abril_2006=0;
            document.all.h_acuerdo_abril_2006.value=0;
            document.all.acuerdo_abril_2006.value=0;
            }
            else{
            acuerdo_abril_2006=parseFloat(document.all.acuerdo_abril_2006.value);
            document.all.h_acuerdo_abril_2006.value=acuerdo_abril_2006;
            document.all.acuerdo_abril_2006.value=acuerdo_abril_2006;
            
            }     
         // calculo sac_sobre_indemnizacion 
         aux_indemnizacion=parseFloat(document.all.h_indem.value);
         aux_sac_indemnizacion=(aux_indemnizacion * 8.3334) / 100;
         document.all.h_sac_indemnizacion.value=aux_sac_indemnizacion;
         document.all.sac_indemnizacion.value=formato_money(document.all.h_sac_indemnizacion.value);
    
  U=R+AE+G+VNG+IND+PREAV+SAC_VNG+SAC_PREAVISO+aux_dec1347+aux_dec2005+aux_sac_indemnizacion + acuerdo_abril_2006;

  
  
  document.all.h_total_no_remunerativo.value=U;
  document.all.total_no_rem.value=formato_money(U);
 return true;
}

function calcular_neto(){
//T=I-Q+U
 var T,I,Q,U, anticipo;
 calcular_total();
 I=parseFloat(document.all.h_total.value);

 calcular_total_desc(); //dentro de esta funcion ya la estoy llamando dentro de la condicion
 Q=parseFloat(document.all.h_total_descuentos.value);

 calcular_total_no_rem();
 U=parseFloat(document.all.h_total_no_remunerativo.value);
 
 T=I-Q+U;
 
 if (document.all.anticipo.value) {
 		 anticipo=parseFloat(document.all.anticipo.value);
     	 document.all.h_anticipo.value=anticipo;
		 T=parseFloat(T-anticipo);
         document.all.total_no_rem.value=formato_money(parseFloat(U - anticipo));
         }	 
    
    
 document.all.h_neto.value=T;
 document.all.neto.value=formato_money(T);
}





</script>
<?
function decreto_1347_c(){
global $dec1347, $dias_dec1347;
?>	
     <td><b>Decreto 1347/03:</b> </td>
     <td aling='center'>
       <table>
        <tr>
         <td><b>Días Trabajados</b>&nbsp;
           <select name=dias_dec1347>
             <? for ($i=0;$i<32;$i++){
        	    if ($dias_dec1347==$i) 
        	      $selected="selected";
        	    else $selected=""; ?>   
               <option value="<?=$i?>" <?=$selected;?>> <?=$i?> </option>
             <? } ?>      
            </select>
         </td>
         <td>$&nbsp;<input type="text" name="dec_1347_nr" class=text_4 readonly size="10" value="<?=setea_datos($dec1347);?>"></td>
        </tr>
       </table>
      </td>  
<?
}

function decreto_1347(){
global $dec1347;	
?>	
     <td><b>Decreto 1347/03:</b></td>
     <td aling='center'>
      $ <input type="text" name="dec_1347_r" size="10" value="<?=setea_datos($dec1347);?>">
     </td>
<?
}
echo $html_header;
?>

<script language="JavaScript" src="../../lib/NumberFormat150.js"></script>
<?
$link=encode_link("liq_sueldo.php",array ("id"=>$id,"pagina"=>$pagina,"id_sueldo"=>$id_sueldo,"fecha_anterior"=>$fecha_anterior));
?>
<FORM name="form1" action="<?=$link?>" method="POST">

<!-- estos hidden se usa para los calculos de los totales -->
<input type=hidden name="h_subtotal" value="">
<input type=hidden name="h_subtotal_horas" value=""> 
<input type=hidden name="h_subtotal2" value="">
<input type=hidden name="h_monto" value="">
<input type=hidden name="h_total" value="">
<input type=hidden name="h_total_descuentos" value="">
<input type=hidden name="h_total_no_remunerativo" value="">
<input type=hidden name="h_neto" value="">

<!-- estos hydden se usan para guardar los datos que despues voy a guardar en la base -->
<input type=hidden name="h_basico" value="<?=$basico?>">
<input type=hidden name="h_dec1529" value="<?=$dec1529?>">
<input type=hidden name="h_acuenta" value="<?=$acuenta?>">
<!--esotos representan a las vacaciones gozadas -->
<input type=hidden name="h_vacaciones_dias" value="<?=$vacaciones_dias?>">
<input type=hidden name="h_vacaciones" value="<?=$vacaciones?>">
<!-- estos representan a las vacaciones -->
<input type="hidden" name="h_vac" value="<?=$vac?>">
<input type="hidden" name="h_total_vac" value="<?=$total_vac?>">


<input type=hidden name="h_salario_familiar" value="<?=$salario_familiar?>">
<input type=hidden name="h_dec1347" value="<?=$dec1347?>">
<input type=hidden name="h_dec2005" value="<?=$dec2005_04?>">
<input type=hidden name="h_presentismo" value="<?=$presentismo?>">

<input type=hidden name="h_jubilacion" value="<?=$jubilacion?>">
<input type=hidden name="h_ley19032" value="<?=$ley19032?>">
<input type=hidden name="h_obra_social" value="<?=$obra_social?>">
<input type=hidden name="h_sindicato" value="<?=$sindicato?>">
<input type=hidden name="h_sindicato_familiar" value="<?=$sindicato_familiar?>">
<input type=hidden name="h_faecys" value="<?=$faecys?>">
<input type=hidden name="h_ausentismo" value="<?=$ausentismo?>">
<input type=hidden name="h_ayuda" value="<?=$ayuda?>">
<input type=hidden name="h_ajuste_anterior" value="<?=$ajuste_anterior?>">

<!--estos dos hiddens se usa para mantener los valores de gratificacion
y vacaciones no gozadas para cuando renuncia un empleado
le agrego tambien dias de inasistencia y el total por inasistncia
tambien se agrega indemnizacion y pre aviso q sirven para lo mismo -->
<input type="hidden" name="h_gratif" value="<?=$gratificacion?>">
<input type="hidden" name="h_vac_ng" value="<?=$vac_ng?>">
<input type="hidden" name="h_sac_sobre_vng" value="<?=$sac_sobre_vng?>">
<input type="hidden" name="h_dias_inasistencia" value="<?=$dias_inasistencia?>"> 
<input type="hidden" name="h_total_inasistencia" value="<?=$total_inasistencia?>">
<input type="hidden" name="h_indem" value="<?=$indemnizacion?>">
<input type="hidden" name="h_pre_aviso" value="<?=$pre_aviso?>">
<input type="hidden" name="h_sac_preaviso" value="<?=$sac_preaviso?>">
<input type="hidden" name="h_sac_indemnizacion" value="<?=$sac_indemnizacion?>">
<input type="hidden" name="h_sac" value="<?=$sac?>">
<input type="hidden" name="h_mes_sac" value="<?=$mes_sac?>">
<input type="hidden" name="h_dias_sac" value="<?=$dias_sac?>">
<input type="hidden" name="h_dias_dec1347" value="<?=$dias_dec1347?>">
<input type="hidden" name="h_dias_dec2005" value="<?=$dias_dec2005?>">
<input type="hidden" name="h_anticipo" value="<?=$anticipo?>">
<input type="hidden" name="h_cantidad_dias_feriados" value="<?=$cantidad_dias_feriados?>"> 
<input type="hidden" name="h_feriados" value="<?=$feriados?>"> 
<input type="hidden" name="h_sueldo_por_horas" value="<?=$sueldo_por_horas?>"> 
<input type="hidden" name="h_horas" value="<?=$horas?>"> 
<input type="hidden" name="h_valor_hora" value="<?=$valor_hora?>"> 
<input type="hidden" name="h_horas_extras" value="<?=$horas_extras?>"> 
<input type="hidden" name="h_tipo_hora_extras" value="<?=$tipo_hora_extras?>"> 
<input type="hidden" name="h_embargo" value="<?=$embargo?>"> 
<input type="hidden" name="h_acuerdo_abril_2006" value="<?=$acuerdo_abril_2006?>">  


<TABLE width=95% border=1 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align=center>
  <TR>
   <TD><b>Nro. de legajo:</b>&nbsp;&nbsp;<input type="text" name="nro_leg" size="4" class=text_4 readonly value="<?=$id;?>"></td>
   <td colspan="4"><b>Período al que pertenece la liquidación</b></td>
  </tr>
  <!-- &nbsp;&nbsp;<input type="text" name="periodo" size="10" class=text_4 readonly value="<?=$periodo;?>"></td> -->
  <tr>
   <TD><b>Apellido y Nombre:</b>&nbsp;&nbsp;<input type="text" name="apell_nombre" size="35" class=text_4 readonly value="<?=$apellido." ".$nombre;?>"></td>
   <td align="right"><b>Mes:</b></td>
   <td align="center">
    <?
    if($mes=="") 
     $mes=date("m",mktime());
   ?>
   <select name=mes>
     <option value='1' <?if ($mes==1) echo "selected"?>>Enero</option>
     <option value='2' <?if ($mes==2) echo "selected"?>>Febrero</option>
     <option value='3' <?if ($mes==3) echo "selected"?>>Marzo</option>
     <option value='4' <?if ($mes==4) echo "selected"?>>Abril</option>
     <option value='5' <?if ($mes==5) echo "selected"?>>Mayo</option>
     <option value='6' <?if ($mes==6) echo "selected"?>>Junio</option>
     <option value='7' <?if ($mes==7) echo "selected"?>>Julio</option>
     <option value='8' <?if ($mes==8) echo "selected"?>>Agosto</option>
     <option value='9' <?if ($mes==9) echo "selected"?>>Septiembre</option>
     <option value='10' <?if ($mes==10) echo "selected"?>>Octubre</option>
     <option value='11' <?if ($mes==11) echo "selected"?>>Noviembre</option>
     <option value='12' <?if ($mes==12) echo "selected"?>>Diciembre</option>
     </select> </td>
   <td align="right"><b>Año:</b></td>
   <td align="center">
   <?
    if($año=="") 
     $año=date("Y",mktime());
   ?>
   <select name=año>
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
     </select> </td>
  </TR>
  <tr>
   <td>&nbsp;</td>
   <td cospan="2" align="right"><b>Fecha Pago:</b></td>
   <td colspan="3"><input type=text name=fecha_pago value="<?=fecha($fecha_pago)?>"> <?=link_calendario("fecha_pago")?></td>
  </tr>
  <tr>
   <td align="left">
     <table>
       <tr>
        <td align="left"><b>Tipo de Liquidación</b>&nbsp;</td>
        <td>
         <INPUT TYPE="RADIO" name="liq_tipo" value="1" <?if ($tipo_liq==1) echo "checked"?>>&nbsp;<b>Sueldos y jornales</b> &nbsp;
         <INPUT TYPE="RADIO" name="liq_tipo" value="2" <?if ($tipo_liq==2) echo "checked"?>>&nbsp;<b>Honorarios</b>
        </td>
       </tr>
     </table>
    </td>
   <td cospan="2" align="right"><b>Fecha depósito:</b></td>
   <td colspan="3"><input type=text name=fecha_jubilacion value="<?=fecha($fecha_jubilacion)?>"> <?=link_calendario("fecha_jubilacion")?></td>
  </tr>

  <?
  if ($sueldo_por_horas)
                     $checked_sueldo_por_horas="checked";
                     else
                     $checked_sueldo_por_horas="";
                     
  ?>
  <tr>
   <td align="left" colspan=3>
   <input type=checkbox  name=sueldo_por_horas value=1 <?=$checked_sueldo_por_horas?>>&nbsp;
   <b>Sueldo por Horas</b>
   </td>
  </tr>

  
<!-- empieza la tabla de remunerativo -->
  <TR>
  <TD colspan="5">
  <TABLE width=100% border=1 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align=center>
   <TR>
    <TD style=\"border:$bgcolor3;\" colspan=6 align=center id=mo><b>Remunerativo</b></TD>
   </TR>
   <TR>
    <td align="left"><b>Sueldo Básico:</b></td>
    <td align="left">$&nbsp;<input type="text" name="basico" size="10" value="<?=setea_datos($basico);?>"></td>
    <td align="left"><b>Decreto 1529:</b></td>
    <td align="left">$&nbsp;<input type="text" name="decreto" size="10" value="<?=setea_datos($dec1529);?>"></td>
    <? if ($fecha_pago>$fecha) {    
          decreto_1347();
          ?>
      <input type="hidden" name="mostrar_dec_1347" value="dec_rem">    
    <? }
       else { ?>
         <td colspan="2">&nbsp;</td>
    <? } ?>
   </TR>
   <tr>
      <td colspan="2" align="center"><b>Horas (Cant.)</b> <input type=text name=horas value="<?=$horas?>" size=3></td>
      <td colspan="2" align="center"><b>Valor</b> <input type=text name=valor_hora value="<?=setea_datos($valor_hora)?>" size=4></td>
      <td aling="center"><b>Subtotal:</b></td>
      <td aling="center">$&nbsp;<input type="text" name="subtotal" size="10" class=text_4 readonly value="<?=setea_datos($arreglo['subtotal']);?>"></td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <tr>
    <td colspan="2" align="center"><b>Horas Extras</b> <input type=text name=horas_extras value="<?=$horas_extras?>" size=3></td>
    <td colspan="2" align="center"><b>Tipo</b> 
              <?
              if ($tipo_hora_extras=="1.5") $checked_15="checked";
                                   else   $checked_15="";
              if ($tipo_hora_extras=="2") $checked_2="checked";
                                   else   $checked_2="";
              if (!$tipo_hora_extras) $checked_2="checked";                                   
                                   
              ?>
              <input type=radio name=tipo_hora_extras value="1.5" <?=$checked_15?>> <b>1.5</b>&nbsp;&nbsp;&nbsp;
              <input type=radio name=tipo_hora_extras value="2" <?=$checked_2?>> <b>2</b> 
    </td>
    <td aling="center"><b>Subtotal:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="subtotal_horas" size="10" class=text_4 readonly value="<?=setea_datos($arreglo['subtotal_horas']);?>"></td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <TR>
    <td><b>a cta. Futuros Ajustes:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="a_cuenta" size="10" value="<?=setea_datos($acuenta);?>"></td>
    <td><b>Presentismo:</b></td>
    <td aling="center">$&nbsp;
         <input type="text" name="presentismo" size="10" class=text_4 readonly value="<?=setea_datos($presentismo);?>">
         &nbsp;
         <?
         ($no_tiene_presentismo)?$checked_presentismo="checked":$checked_presentismo="";
         ?>
         <input type="checkbox" name="no_tiene_presentismo" value="1" <?=$checked_presentismo?> title="Si no tiene presentismo clickear esta opción">         
       </td>
    <td><b>Subtotal:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="subtotal2" size="10" class=text_4 readonly value="<?=setea_datos($arreglo['subtotal2']);?>"></td>
   </TR>
   <tr><td colspan=6>&nbsp;</td></tr>
   <TR>
    <td><b>Vacaciones Gozadas (Días):</b></td>
    <td aling="center">&nbsp;<input type="text" name="dias" size="10" value="<?=$vacaciones_dias;?>"></td>
    <td><b>Vacaciones Gozadas:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="vacaciones" size="10" class=text_4 readonly value="<?=setea_datos($vacaciones);?>"></td>
    <td><b>Ausentismo:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="ausentismo" size="10" class=text_4 readonly value="<?=setea_datos($ausentismo);?>"></td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <tr>
    <td><b>Vacaciones (Días):</b></td>
    <td aling="center">&nbsp;<input type="text" name="dias_vac" size="10" value="<?=$vac?>"></td>
    <td><b>Vacaciones:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="total_vac" size="10" class=text_4 readonly value="<?=setea_datos($total_vac);?>"></td>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <tr> 
    <td><b>Inasistencias (Días):</b></td>
    <td aling="center">&nbsp;<input type="text" name="dias_inasistencia" size="10" value="<?=$dias_inasistencia;?>"></td>
    <td><b>Inasistencias:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="inasistencia" size="10" class=text_4 readonly value="<?=setea_datos($total_inasistencia);?>"></td>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <tr>
      
      <td title="Chequear cuando se tiene esta opción">&nbsp;&nbsp;
      <? if ($sac==1)
          $checked="checked";
         else $checked="";
          ?> 
        <input type="checkbox" name="chequeo2" value="1" <?=$checked;?> onclick="sacar_sac()">&nbsp;&nbsp;
         <b>S.A.C.</b>
      </td>
      <td><b>Meses Trabajados</b>&nbsp;
        <select name=mes_sac>
        <? for ($i=0;$i<13;$i++){ 
        	if ($mes_sac==$i) 
        	  $selected="selected";
        	else $selected=""; 
          ?>  
          <option value='<?=$i?>' <?=$selected;?>> <?=$i?> </option>
          <? } ?>      
        </select>
      </td>
      <td><b>Días Trabajados</b>&nbsp;
        <select name=dias_sac>
        <? for ($i=0;$i<32;$i++){
        	if ($dias_sac==$i) 
        	  $selected="selected";
        	else $selected=""; 
          ?>   
           <option value='<?=$i?>' <?=$selected;?>> <?=$i?> </option>
         <? } ?>      
        </select>
      </td>
      <td><b>Total SAC: </b></td>
      <td aling="center">$&nbsp;<input type="text" name="sac" size="10" class=text_4 readonly value="<?=setea_datos($arreglo['sac']);?>"></td>
      <td>&nbsp;</td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <tr>
     <td><b>Ajuste Anterior</b></td>
     <td><input type=text name="ajuste_anterior" size="10" value="<?=number_format($ajuste_anterior,"2",".","")?>"></td>
     <td colspan=4>&nbsp;</td>
   </tr>
   <tr>
     <td><b>Feriados</b></td>
     <td>
       <select name=select_feriados>
         <option value=0 selected>0</option>
         <?
         for ($i=1;$i<31;$i++){
             if ($i==$cantidad_dias_feriados)
                    $selected="selected";
                    else
                    $selected="";
         ?>
         <option value="<?=$i?>" <?=$selected?>><?=$i?></option>
         <?
         }
         ?>
       </select>
     </td>
     <td><input type=text name=feriados value="<?=formato_money($feriados)?>" size=10></td>
     <td colspan=4>&nbsp;</td>
   </tr>   
</TABLE>
</TD>
</TR>
<!-- aca termina la tabla de remunerativo -->
<!-- aca empieza la tabla de descuentos -->
<TR>
<TD colspan="5">
<TABLE width=100% border=1 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align=center>
   <TR>
    <TD style=\"border:$bgcolor3;\" colspan=10 align=center id=mo><b>Descuentos</b></TD>
   </TR>
   <TR>
    <td>
     <table>
       <tr><td><b>Jubilación:</b></td></tr>
       <tr><td>
         <INPUT TYPE="RADIO" name="tipo_jub" value="0" <?if ($tipo_jub==0) echo "checked"?>>&nbsp;<b>A.F.J.P</b> &nbsp;
         <INPUT TYPE="RADIO" name="tipo_jub" value="1" <?if ($tipo_jub==1) echo "checked"?>>&nbsp;<b>Reparto</b>
       </td></tr>
     </table>    
    </td>
    <td aling="center">$&nbsp;<input type="text" name="jubilacion" size="10" class=text_4 readonly value="<?=setea_datos($jubilacion);?>"></td>
    <td><b>Ley 19.032:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="ley" size="10" class=text_4 readonly value="<?=setea_datos($ley19032);?>"></td>
    <td><b>Obra Social:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="ob_social" size="10" class=text_4 readonly value="<?=setea_datos($obra_social);?>"></td>
   </tr>
   <tr><td colspan=6>&nbsp;</td></tr>
   <tr>
    <td><b>Sindicato:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="sindicato" size="10" class=text_4 readonly value="<?=setea_datos($sindicato);?>"></td>
    <td><b>Faecys:</b></td>
    <td aling="center">$&nbsp;<input type="text" name="faecys" size="10" class=text_4 readonly value="<?=setea_datos($faecys);?>"></td>
    <td title="Chequear cuando se tiene esta opción"> <input type="checkbox" name="chequeo" value="" <?if($sindicato_familiar!=0) echo "checked"?>>
    <!-- onClick="document.all.sal_fam.disabled=!document.all.sal_fam.disabled;document.all.sal_fam.value=0" -->
     <b>Sindicato (familiar):</b>
    <!-- esto tiene q ir para q traiga el chekbox marcado --> 
          <?//if($sindicato_familiar!=0) echo "checked"?> 
    </td>
    <td aling="center">$&nbsp;<input type="text" name="sindi_familiar" size="10" class=text_4 readonly value="<?=setea_datos($sindicato_familiar);?>"></td>
   </tr>
   <tr>
   <td><b>Embargos</b></td>
   <td aling="center">
        $&nbsp;
        <input type="text" name="embargo" size="10" value="<?=setea_datos($embargo);?>"></td>
   <td colspan=4>&nbsp;</td></tr>
</TABLE>
</TD>
</TR>
<!-- aca termina la tabla de descuentos -->
<!-- aca empieza la tabla de no remunerativo -->
<TR>
<TD colspan="5">
<TABLE width=100% border=1 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align=center>
   <TR>
    <TD style=\"border:$bgcolor3;\" colspan=10 align=center id=mo><b>No Remunerativo</b></TD>
   </TR>
   <TR>
     <td><b>Decreto 2005/04:</b> </td>
     <td aling="center">
      <table>
       <tr>
        <td><b>Días Trabajados</b>&nbsp;
            <select name=dias_dec2005>
            <? for ($i=0;$i<32;$i++){
         	   if ($dias_dec2005==$i) 
        	     $selected="selected";
        	   else $selected="";?>   
              <option value='<?=$i?>' <?=$selected;?>> <?=$i?> </option>
            <? } ?>      
           </select>
        </td> 
        <td>$&nbsp;<input type="text" name="decreto2005_04" class=text_4 readonly size="10" value="<?=setea_datos($dec2005_04);?>"></td>
       </tr>
      </table>
    </td> 
    <? //echo "fecha_pago   ".$fecha_pago."<br>";
       //echo "fecha   ".$fecha."<br>";
       if ($fecha_pago<$fecha) {
          decreto_1347_c();
          // uso un hidden para guardar donde  se mostro el decreto 1347
        	 ?>
     <input type="hidden" name="mostrar_dec_1347" value="dec_no_rem">
       <? }	
       else { ?>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
    <?  } ?>   
  </TR>
  <TR>
     <td><b>Salario Familiar:</b></td>
     <!-- tiene q ir para deshabilitar el text cuando viene chekeado el chek de sind_fam -->
     <?//if($sindicato_familiar==0) echo "disabled"?>
     <td aling="center" title="Ingresar un monto cuando esta chequeda la opción de sindicato familiar">
     $&nbsp;<input type="text" name="sal_fam" size="10" <?//if($sindicato_familiar==0) echo "disabled"?> value="<?=setea_datos($salario_familiar);?>"></td>
     <td><b>Ayuda Escolar:</b> </td>
     <td aling="center">$&nbsp;<input type="text" name="ayuda" size="10" value="<?=setea_datos($ayuda);?>"></td>
  </TR>
  <TR>
     <td><b>Pre Aviso:</b> </td>
     <td aling="center">$&nbsp;<input type="text" name="pre_aviso" size="10" value="<?=setea_datos($pre_aviso);?>"></td>
     <td><b>Sac Pre Aviso:</b></td>
     <td aling="center">$&nbsp;<input type="text" name="sac_sobre_preaviso" class=text_4 readonly size="10" value="<?=setea_datos($sac_preaviso);?>"></td>
  </TR>
  <tr>
     <td><b>Vacaciones no Gozadas:</b></td>
     <td aling="center">$&nbsp;<input type="text" name="no_vacaciones" size="10" value="<?=setea_datos($vac_ng);?>"></td>
     <td><b>Sac Vacaciones no Gozadas:</b> </td>
     <td aling="center">$&nbsp;<input type="text" name="sac_sobre_vng" class=text_4 readonly size="10" value="<?=setea_datos($sac_sobre_vng);?>"></td>
  </tr>
  <tr>
     <td><b>Gratificación:</b> </td>
     <td aling="center">$&nbsp;<input type="text" name="gratificacion" size="10" value="<?=setea_datos($gratificacion);?>"></td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
  </tr> 
  <tr>  
     <td><b>Indemnización:</b> </td> 
     <td aling="center">$&nbsp;<input type="text" name="indemnizacion" size="10" value="<?=setea_datos($indemnizacion);?>"></td>
     <td><b>Sac Indemnización:</b> </td>
     <td aling="center">$&nbsp;<input type="text" name="sac_indemnizacion" class=text_4 readonly size="10" value="<?=setea_datos($sac_indemnizacion);?>"></td>
 </tr>
 <tr><td colspan="4">&nbsp;</td></tr>
 <tr>
 <td><b>Anticipos:</b></td>
 <td>$&nbsp;<input type="text" name="anticipo" size="10" value="<?=setea_datos($anticipo)?>" onchange="document.all.h_anticipo.value=this.value"></td>
 <td colspan="2">&nbsp;</td>
 </tr>
 
 <tr>
 <td><b>Acuerdo Abril 2006:</b></td>
 <td>$&nbsp;<input type="text" name="acuerdo_abril_2006" size="10" value="<?=setea_datos($acuerdo_abril_2006)?>" onchange="document.all.h_acuerdo_abril_2006.value=this.value"></td>
 <td colspan="2">&nbsp;</td>
 </tr> 
  
</TABLE>
</TD>
</TR>
<!-- aca termina la tabla de no remunerativo -->
 <tr><td colspan=6>&nbsp;</td></tr>
  <tr>
    <td width=100% colspan="5">
    <table width=100% align=center border=1 cellspacing=0 cellpadding=3>
       <tr>
        <td width=50%>
         <TABLE width=70% border=0 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align="center">
          <tr>
          <td style=\"border:$bgcolor3;\" id=mo_sf width=70%><b>Total Remunerativo</b></td>
          <td style=\"border:$bgcolor3;\" align=center id=ma_sf2 width=70%>$&nbsp;<input type="text" name="total" size="10" class=text_4 readonly value="<?=formato_money($arreglo['total']);?>"></td>
          <!--  <td><input type="button" value="Calcular Total" onclick="calcular_total()"></td>-->
          </tr>
          </table>
        </td>
        <td  width=50% colspan="2">
        <TABLE width=70% border=0 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align="center">
        <tr>
        <td style=\"border:$bgcolor3;\" id=mo_sf width=70%><b>Total Descuentos</b></td>
        <td align="center" style=\"border:$bgcolor3;\" id=ma_sf2 width=70%>$&nbsp;<input type="text" name="total_desc" size="10" readonly class=text_4 value="<?=formato_money($arreglo['total_desc']);?>"></td>
        <!-- <td><input type="button" value="Calcular Total Descuentos" onclick="calcular_total_desc()"></td>-->
         </tr>
         </table>
       </td>
    </tr>
    <tr>
    <td>
    <TABLE width=70% border=0 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align="center">
     <tr>
     <td style=\"border:$bgcolor3;\" id=mo_sf width=70%><b>Total No Remunerativo</b> </td>
     <td align="center" style=\"border:$bgcolor3;\" id=ma_sf2 width=70%>$&nbsp;<input type="text" name="total_no_rem" size="10" class=text_4 readonly value="<?=formato_money($arreglo['total_no_rem']);?>"></td>
     </tr>
    </table>
    </td>
    <td align="center" colspan="2">
      <TABLE width=70% border=0 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?> align="center">
         <tr>
           <td style=\"border:$bgcolor3;\" align=left id=mo_sf width=70%><b>Neto</b></td>
           <td style=\"border:$bgcolor3;\" align=center id=ma_sf2 width=70%>$&nbsp;<input type="text" name="neto" size="10" readonly class=text_4 value="<?=formato_money($arreglo['neto']);?>"></td>
         </tr>
      </TABLE>
   </td>
   </tr>
   <tr>
       <?
        if ((($pagina=="listado_liq_sueldo.php")&&($estado==0))||($pagina=="modificar_legajo.php")){?>
        <td colspan=4 align=right>
          <input type="button" value="Calcular Neto" onclick="calcular_neto()">
        </td>
       <?
       }
       ?>
   </tr>
 </table>
   </td>
 </tr>
 <tr>
   <td align="center" colspan="5">
   <TABLE width=40% border=0 cellspacing=0 cellpadding=3 bgcolor= <?=$bgcolor2?>>
   <tr>
   <? if ($pagina=="modificar_legajo.php"){?>

           <td align="center"><input type="submit" value="Guardar" name="guardar" ></td>
           <td align="center"><input type="reset" value="Cancelar" name="cancelar"></td>
           <?
           } //cierra el if de pagina = modificar_legajo
             else {
                if ($estado==0){
                    ?>
                   <td><b>Terminar liquidación de Sueldo</b>&nbsp;</td>
                   <td><INPUT TYPE="CHECKBOX" name="pasar_historial" title="Pasar a Terminados" value="1"></td>
                   <td align="center"><input type="submit" value="Guardar" name="guardar"></td>
                   <!-- onclick="return control_fecha(); -->
                   <td align="center"><input type="reset" value="Cancelar" name="cancelar" title="Deshace todas las modificaciones realizadas"></td>
                   <? } //cierra el if de estado = 0
                      else{
                          ?>
                          <td><input type=submit name=pendientes value="Pasar a Pendiente"></td>
                          <?
                          }
               $link_imprimir=encode_link("recibo_sueldo.php", array("id_legajo" =>$id,"id_sueldo"=>$id_sueldo,"fecha_anterior"=>$fecha_anterior,"titulo_recibo"=>"Original para el Empleado","firma"=>"Firma Empleador"));
               $link_imprimir2=encode_link("recibo_sueldo.php", array("id_legajo" =>$id,"id_sueldo"=>$id_sueldo,"fecha_anterior"=>$fecha_anterior,"titulo_recibo"=>"Original para la Empresa","firma"=>"Firma Empleado"));
               ?>
               <td align="center"><input type="submit" value="Imprimir" name="imprimir" title="Vista previa del recibo e impresión" onclick="window.open('<?php echo $link_imprimir; ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,width=800,height=600');window.open('<?php echo $link_imprimir2; ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,width=800,height=600')"></td>
               <td align="center"><input type="button" value="Volver" name="volver" onclick="document.location='listado_liq_sueldo.php';"></td>
               <?
               } //cierra el else de pagina = modificar_legajo
               ?>

               
   </tr>
   </table>
   </td>
 </tr>
</TABLE>
<?
echo fin_pagina();
?>