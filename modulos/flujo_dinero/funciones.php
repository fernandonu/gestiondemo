<?
/*
$Author: fernando $
$Revision: 1.17 $
$Date: 2005/01/03 13:19:28 $
*/


function arma_fecha($fecha_parametro){


   $fecha=$fecha_parametro;
   for($i=0;$i<4;$i++){
        $dat=explode("-",$fecha);
        $a�o=$dat[0];
        $mes=$dat[1];
        $dia=$dat[2];
        $array_fechas[]=$fecha;
        $fecha=date("Y-m-d",mktime(0,0,0,$mes,$dia+7,$a�o));
  }//del for
return  $array_fechas;
}//de la funcion arma_fecha

function calcula_numero_dia_semana($dia,$mes,$ano){
    $numerodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
    if ($numerodiasemana == 0)
       $numerodiasemana = 6;
    else
       $numerodiasemana--;
    return $numerodiasemana;
}

function ultimoDia($mes,$ano){
    $ultimo_dia=28;
    while (checkdate($mes,$ultimo_dia + 1,$ano)){
       $ultimo_dia++;
    }
    return $ultimo_dia;
}



function datos_generales($mes,$a�os){

$cant_dias=ultimoDia($mes,$a�os);

$mes_anterior =date("m",mktime(0,0,0,$mes-1,1,$a�os));
$mes_siguiente=date("m",mktime(0,0,0,$mes+1,$cant_dias,$a�os));

if ($mes==12) $a�os_siguiente=$a�os+1;
         else $a�os_siguiente=$a�os;

if ($mes==1)  $a�os_anteriores=$a�os-1;
        else  $a�os_anteriores=$a�os;

$cant_dias_mes_anterior =ultimoDia($mes_anterior,$a�os_anteriores);
$cant_dias_mes_siguiente=ultimoDia($mes_siguiente,$a�os_siguiente);

//veo en que dia termian el mes
$ultimo_dia_mes = calcula_numero_dia_semana($cant_dias,$mes,$a�os);
/*
$ultima_dia_mes_siguiente = calcula_numero_dia_semana($cant_dias_mes_siguiente,$mes_siguiente,$a�os_siguiente);
$ultima_dia_mes_anterior = calcula_numero_dia_semana($cant_dias_mes_anterior,$mes_anterior,$a�os_anteriores);
*/

$mes_v["mes"]=$mes;
$mes_v["cant_dias"]=$cant_dias;
$mes_v["ult_dia_mes"]=$ultimo_dia_mes;

$mes_s["mes"]=$mes_siguiente;
$mes_s["cant_dias"]=$cant_dias_mes_siguiente;


$mes_a["mes"]=$mes;
$mes_a["cant_dias"]=$cant_dias_mes_anterior;

//armo las semanas

$primer_dia=calcula_numero_dia_semana(1,$mes,$a�os);
if ($primer_dia!=0)
    {
    $i=1;
    $dia_aux=$cant_dias_mes_anterior;
    while(calcula_numero_dia_semana($dia_aux,$mes_anterior,$a�os_anteriores))
    {
    $dia_aux--;
    $i++;
    }
    $fecha_comienzo="$dia_aux/$mes_anterior/$a�os_anteriores";
    }
    else
    {
    $fecha_comienzo="1/$mes/$a�os";
    }


if ($ultimo_dia_mes!=6){
              $i=0;
              $dia_de_la_semana=$ultima_dia_mes;
              $fecha_aux="$cant_dias/$mes/$a�os";

              while (($dia_de_la_semana!=6))
              {
              $dat=explode("/",$fecha_aux);
              $d=$dat[0]+1;
              $m=$dat[1];
              $a=$dat[2];
              $dia_de_la_semana=calcula_numero_dia_semana($d,$m,$a);
              $fecha_aux=date("d/m/Y",mktime(0,0,0,$m,$d,$a));
              $i++;
              }
              $fecha_fin=$fecha_aux;

              }   //del while
              else
                  {
                  $fecha_fin="$cant_dias/$mes/$a�os";
                  }


$semanas["comienzo"]=$fecha_comienzo;
$semanas["fin"]=$fecha_fin;
$retorno["mes"]=$mes_v;
$retorno["mes_anterior"]=$mes_a;
$retorno["mes_siguiente"]=$mes_s;
$retorno["semanas"]=$semanas;


return $retorno;
}


function forma_semanas($semana,$fin_semana,$cant_sem_atras=2,$cant_sem_ade=2)
{
  $data=explode("/",$semana);
   $dia  = $data[0];
   $mes  = $data[1];
   $a�os = $data[2];


 $data=explode("/",$fin_semana);
 $dia_u  = $data[0];
 $mes_u  = $data[1];
 $a�os_u = $data[2];
 $dias_atras=7*$cant_sem_atras;
 $dias_adelantes=7*$cant_sem_ade;

 $fecha_inicial=date("d/m/Y",mktime(0,0,0,$mes,$dia-$dias_atras,$a�os));
 $fecha_final  =date("d/m/Y",mktime(0,0,0,$mes_u,$dia_u+ 1 + $dias_adelantes,$a�os_u));

  $data=explode("/",$fecha_inicial);
   $dia  = $data[0];
   $mes  = $data[1];
   $a�os = $data[2];


 $fecha_aux=$fecha_inicial;
 $indice=0;
 $i=7;
// echo "esta es la ff: $fecha_final";
 while ($fecha_aux!=$fecha_final) {
     $fecha[$indice]=$fecha_aux;
     $fecha_aux=date("d/m/Y",mktime(0,0,0,$mes,$dia+$i,$a�os));
     $i=$i+7;
     $indice++;

  }
 $fecha[$indice]=$fecha_final;


return $fecha;
}

function arma_fecha_consulta($fecha){
 $dat = explode("/",$fecha);
 $dia = $dat[0];
 $mes = $dat[1];
 $a�os= $dat[2];
 $fecha="$a�os-$mes-$dia";
 return $fecha;

}

//esta funcion me obtiene los valores que voy a mostrar
//me devuelve un arreglo con las cosas que quiero
//ya esta con el formato debido

function obtener_montos($fecha_hasta,$numero_de_semama){

   global $db;

   /*
  $fecha_desde_db=arma_fecha_consulta($fecha_desde);
  $fecha_hasta_db=arma_fecha_consulta($fecha_hasta);

   if ($numero_de_semama==0) $fecha_inicio='1996-01-01';
                      else   $fecha_inicio=$fecha_desde_db;
   */
  $fecha_inicio='1996-01-01';

   //obtengo los montos desde tal fechas hasta
  $sql="select sum(monto) as total,simbolo ";
  $sql.=" from cobranzas join moneda using (id_moneda)";
  $sql.=" where ";
  $sql.=" (";
  $sql.= " (";
  $sql.=" (fecha_estimativa >='$fecha_inicio')";
  $sql.="  and ";
  $sql.=" (fecha_estimativa<='$fecha_hasta')";
  $sql.=" )";
  $sql.=" or ";
  $sql.=" (";
  $sql.=" (fecha_estimativa is NULL) and";
  $sql.=" ((DATE(fecha_presentacion) + 60)>='$fecha_inicio') and";
  $sql.=" ((DATE(fecha_presentacion) + 60)<='$fecha_hasta')";
  $sql.=" ) ";
  $sql.=" )and estado='PENDIENTE'";

  /*
  $sql.=" ((fecha_presentacion + '60')>='$fecha_desde_db') and";
  $sql.=" ((fecha_presentacion + '60')<='$fecha_hasta_db')";
  $sql.=" )";
  */
  $sql.=" group by simbolo";


  $resultado=sql($sql) or fin_pagina();

  while (!$resultado->EOF) {
        $totales_cobranzas[$resultado->fields["simbolo"]] = $resultado->fields["total"];
        $resultado->MoveNext();
        }

  $datos["cobranzas"]=$totales_cobranzas;


  //calculo el saldo de los bancos

   //Total    Depositos

   $sql = "SELECT sum(ImporteDep) AS total ";
   $sql .= "FROM bancos.dep�sitos ";
   $sql .= "JOIN tipo_banco USING (IdBanco) ";
   //$sql .= "ON bancos.dep�sitos.idbanco=tipo_banco.idbanco ";
   $sql .= "WHERE FechaCr�dito IS NOT NULL ";
   $sql .= "AND FechaCr�dito BETWEEN '$fecha_inicio' AND '$fecha_hasta' ";
   $sql .= "AND tipo_banco.activo=1";

   $resultado=sql($sql) or fin_pagina();
   $total_depositos=$resultado->fields["total"];
   //echo "Depositos: $total_depositos <br>";
    //Total    Tarjetas
   $sql = "SELECT sum(ImporteCr�dTar) AS total ";
   $sql .= "FROM bancos.tarjetas ";
   $sql .= "JOIN tipo_banco using(IdBanco)";
   $sql .= "WHERE FechaCr�dTar IS NOT NULL ";
   $sql .= "AND FechaCr�dTar BETWEEN '$fecha_inicio' AND '$fecha_hasta' ";
   $sql .= "AND tipo_banco.activo=1";
   $resultado=sql($sql) or fin_pagina();
   $total_tarjetas=$resultado->fields["total"];
   //echo "Tarjetas: $total_tarjetas <br>";
   //Total    Cheques
   $sql = "SELECT sum(ImporteCh) AS total ";
   $sql .= "FROM bancos.cheques ";
   $sql .= "JOIN tipo_banco using(IdBanco)";
   $sql .= "WHERE FechaD�bCh IS NOT NULL ";
   $sql .= "AND FechaD�bCh BETWEEN '$fecha_inicio' AND '$fecha_hasta' ";
   $sql .= "AND tipo_banco.activo=1";
   $resultado=sql($sql) or fin_pagina();
   $total_cheques=$resultado->fields["total"];

   //Total    Debitos
   $sql = "SELECT sum(ImporteD�b) AS total ";
   $sql .= "FROM bancos.d�bitos ";
   $sql .= "JOIN tipo_banco using(IdBanco)";
   $sql .= "WHERE FechaD�bito IS NOT NULL ";
   $sql .= "AND FechaD�bito BETWEEN '$fecha_inicio' AND '$fecha_hasta' ";
   $sql .= "AND tipo_banco.activo=1";
   $resultado=sql($sql) or fin_pagina();
   $total_debitos=$resultado->fields["total"];
   //echo "debitos: $total_debitos <br>";
   //calculo el saldo
   $saldo=($total_tarjetas+$total_depositos)-($total_cheques+$total_debitos);
   $datos["saldo"]=$saldo;

    //obtengo los montos de las enviadas
    $sql =" select sum(ordenes_pagos.monto) as total,simbolo from compras.orden_de_compra ";
    $sql.=" join compras.pago_orden using (nro_orden)";
    $sql.=" join compras.ordenes_pagos using(id_pago)";
    $sql.=" join compras.forma_de_pago using (id_forma)";
    $sql.=" join licitaciones.moneda using(id_moneda)";
    //$sql.=" where ((fecha_entrega + dias) >= '$fecha_desde_db' and (fecha_entrega + dias) <= '$fecha_hasta_db')";
    $sql.=" where (fecha_entrega >= '$fecha_inicio' and (fecha_entrega + dias)  <= '$fecha_hasta')";
    $sql.=" and estado='e' ";
    $sql.=" group by simbolo";
    $resultado=sql($sql) or fin_pagina();
    while (!$resultado->EOF) {
        $pagos[$resultado->fields["simbolo"]] = $resultado->fields["total"];
        $resultado->MoveNext();
        }

    //obtengo los montos de las parcialmente pagadas
    $sql =" select sum(ordenes_pagos.monto) as total,simbolo from compras.orden_de_compra ";
    $sql.=" join compras.pago_orden using (nro_orden)";
    $sql.=" join compras.ordenes_pagos using(id_pago)";
    $sql.=" join compras.forma_de_pago using (id_forma)";
    $sql.=" join licitaciones.moneda using(id_moneda)";
    //$sql.=" where ((fecha_entrega + dias) >= '$fecha_desde_db' and (fecha_entrega + dias) <= '$fecha_hasta_db')";
    $sql.=" where (fecha_entrega  >= '$fecha_inicio' and (fecha_entrega +dias) <= '$fecha_hasta')";
    //$sql.=" where (fecha_entrega >= '1996-01-01' and fecha_entrega  <= '$fecha_hasta_db')";
    $sql.=" and estado='d' and ((not n�meroch is null) or (not idd�bito is null) or (not id_ingreso_egreso is null)) ";
    $sql.=" group by simbolo";
    $resultado=sql($sql) or fin_pagina();
    while (!$resultado->EOF) {
        $pagos[$resultado->fields["simbolo"]] += $resultado->fields["total"];
        $resultado->MoveNext();
        }
    $datos["pagos"]=$pagos;

  // if ($numero_de_semama==1) echo "<br>".print_r($datos)."<br>";
   /*
   Traigo los cheques pendientes
   */
   $sql = "SELECT sum(ImporteCh) AS total ";
   $sql .= " FROM bancos.cheques ";
   $sql .= " JOIN tipo_banco using(IdBanco)";
   $sql .= " WHERE FechaD�bCh IS  NULL ";
   $sql .= " AND Fechavtoch BETWEEN '$fecha_inicio' AND '$fecha_hasta' ";
   $sql .= " AND tipo_banco.activo=1 ";
   $resultado=sql($sql) or fin_pagina();
   $cheques_pendientes=$resultado->fields["total"];
   $datos["cheques_pendientes"]=$cheques_pendientes;

   return $datos;
} //fin de la funcion obtener montos


function forma_semana_base($semana,$flag="resta",$cant_dias=7){

if ($flag=="resta"){
          $dat=explode("/",$semana);
          $dia=$dat[0];
          $mes=$dat[1];
          $a�os=$dat[2];
          $semana_base=date("d/m/Y",mktime(0,0,0,$mes,$dia-$cant_dias,$a�os));

          }
          else {
          $dat=explode("/",$semana);
          $dia=$dat[0];
          $mes=$dat[1];
          $a�os=$dat[2];
          $semana_base=date("d/m/Y",mktime(0,0,0,$mes,$dia+$cant_dias,$a�os));
          }

return $semana_base;
}//de la funcion
?>