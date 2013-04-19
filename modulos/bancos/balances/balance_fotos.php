<?
/*
$Author: fernando $
$Revision: 1.39 $
$Date: 2007/01/24 18:44:06 $
*/

// si la pagina se llama desde balance_historial no entre al then por que balance historial ya lo tiene
// los datos de configuracion son necesarios cuando se ejecuta la foto diaria
function dia_habil_anterior($fecha) {
				$fecha_aux=$fecha;
				$feriado=0;
				$dia_anterior=0;

				while(!$dia_anterior) {
				  $fecha_total=split("/",$fecha_aux);
				  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
				  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
				  $fecha_test=split("/",$fecha_aux);
				  if($fecha_test[3]!=0 && !feriado($dfecha))
				      $dia_anterior=1;
				}

				$fecha_retornar=split("/",$fecha_aux);
				$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));

				return $a;
				//echo "el dia anterior ".$dia_anterior;

				//return $dia_anterior;
				//return $fecha_aux;

	       }//de function dia_habil_anterior($fecha)


           
if (!$foto_del_dia) {
            // Librerias del sistema
            $LIB_DIR = "/extra/admin/gestion/lib";
            //$LIB_DIR ="D:/proyectos/gestion_oficial/lib" ;

            require_once($LIB_DIR."/adodb/adodb.inc.php");
            require_once($LIB_DIR."/adodb/adodb-pager.inc.php");
            $db_type='postgres7';


            //$db_host = 'devel.local';			// Host de la página.
            $db_host = 'localhost';			// Host de la página.


            $db_user = 'projekt';				// Usuario.
            $db_password = 'propcp';			// Contraseña.
            $db_name = 'gestion';
            $db_schemas = array(
	            "bancos",
	            "compras",
	            "general",
	            "internet",
	            "licitaciones",
	            "ordenes",
	            "mensajes",
	            "permisos",
	            "sistema",
	            "facturacion",
                "caja",
	            "personal",
	            "casos",
	            "maquinas",
	            "encuestas",
	            "calidad",
	            "reclamo_partes",
	            "muestras",
	            "mov_material",
	            "reporte_tecnico",
	            "tareas_divisionsoft",
	            "stock",
                "remito_interno",
                "comidas",
                "pcpower_presupuesto",
                "licitaciones_datos_adicionales",
                "asientos",
                "compras_adicional",
                "pymes",

            );
            $db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
            $db->Connect($db_host, $db_user, $db_password, $db_name);
            $db->cacheSecs = 3600;
            $result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
            unset($result);
            $db->debug = $db_debug;

      /********************************************
      Autor: MAC
     -funcion que devuelve true si la fecha pasada
      es feriado, y false si no lo es
     *********************************************/
     function feriado($dia_feriado) {
        global $_ses_feriados;

       $dia_fer=split("/",$dia_feriado);

        $feriado=0;
        $dia=intval($dia_fer[0]);
        $mes=intval($dia_fer[1]);
        $anio=intval($dia_fer[2]);

        if (is_array($_ses_feriados[$anio."-".$mes."-".$dia])) {
	       $feriado = count($_ses_feriados[$anio."-".$mes."-".$dia]);
        }
        else {
	    $feriado = 0;
     }
     return $feriado;
}





     function Fecha($fecha_db) {
		            $m = substr($fecha_db,5,2);
		            $d = substr($fecha_db,8,2);
		            $a = substr($fecha_db,0,4);
		            if (is_numeric($d) && is_numeric($m) && is_numeric($a)) {
				            return "$d/$m/$a";
		            }
		            else {
				            return "";
		            }
            }//function fecha



     function Fecha_db($fecha) {
		            if (strstr($fecha,"/"))
			            list($d,$m,$a) = explode("/",$fecha);
		            elseif (strstr($fecha,"-"))
			            list($d,$m,$a) = explode("-",$fecha);
		            else
			            return "";
		            return "$a-$m-$d";
            }//function fecha_db

}// del if que simula scar una foto del dia de la fecha

require_once("funciones_balance.php");


$sql="select monto from saldo_libre_disponibilidad order by fecha DESC limit 1";
$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
($res->fields["monto"])?$saldo_libre_disponibilidad=$res->fields["monto"]:$saldo_libre_disponibilidad=0;


$sql="select monto from suss order by fecha DESC limit 1";
$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
($res->fields["monto"])?$suss=$res->fields["monto"]:$suss=0;



 

$fecha_hasta=date("Y-m-d G:00:00");
$fecha=date("Y-m-d");
$db->starttrans();
// esta parte es para que saque un historial momentaneo del dia asi se puede comparar
                     
$sql="select id_balance_historial
      from balance_historial where fecha='$fecha_hasta'";
$res=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
$id_balance_historial=$res->fields["id_balance_historial"];

//pruebo si hay con esa fecha para conservar datos viejos

if ($res->recordcount()>0) {

                     $sql="select id_balance_historial
                            from balance_historial where fecha='$fecha_hasta'";
                     $res=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
                     $id_balance_historial=$res->fields["id_balance_historial"];

                     $sql="select id_detalle_balance_historial from detalle_balance_historial where id_balance_historial=$id_balance_historial";
                     $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
                     for($i=0;$i<$res->recordcount();$i++){
                          $id_detalle_balance_historial=$res->fields["id_detalle_balance_historial"];
                          $sql="delete from items_detalle_balance where id_detalle_balance_historial=$id_detalle_balance_historial";
                          $db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
                          $res->movenext();
                          }
                    $sql="delete from detalle_balance_historial where id_balance_historial=$id_balance_historial";
                    $db->execute($sql) or die($db->errormsg()."<br>".$sql);

                    $sql="delete from balance_historial where id_balance_historial=$id_balance_historial";
                    $db->execute($sql) or die($db->errormsg()."<br>".$sql);
            }

//cuentas a cobrar

$cuentas_a_cobrar=sql_cuentas_a_cobrar(1);


$cuentas_a_cobrar_dolares = $cuentas_a_cobrar["monto_dolar"];
$cuentas_a_cobrar_pesos   = $cuentas_a_cobrar["monto_pesos"];


if (!$cuentas_a_cobrar_dolares) $cuentas_a_cobrar_dolares=0;
if (!$cuentas_a_cobrar_pesos) $cuentas_a_cobrar_pesos=0;

     
/*******************************************************************************************
                                  BANCOS
*********************************************************************************************/

$datos_bancos   =  sql_bancos($fecha_hasta);

$arreglo_bancos = $datos_bancos["datos"];
$bancos_pesos   = $datos_bancos["monto_pesos"]; 
$bancos_dolares = $datos_bancos["monto_dolar"];

if (!$bancos_pesos) $bancos_pesos=0;
if (!$banco_dolares) $banco_dolares=0;

/******************************************************************************
                        STOCK

stock total + stock de produccion + notas de credito pendientes
/******************************************************************************/



  
$datos_stock=sql_stock();

$stock_depositos = $datos_stock["datos"];
$stock_pesos     = $datos_stock["monto_pesos"];
$stock_dolares   = $datos_stock["monto_dolar"];


//FALTA SUMAR ESTA CANTIDAD AL STOCK EN GENERAL
//$stock_depositos[]=array("nombre"=>"RMA","moneda"=>"U\$S","total"=>32169.52);  
  
/***********************************************************************
                                BIENES DE USO
***********************************************************************/
$sql="select sum(precio_unitario*cantidad)  as total
             from stock.inventario
             join stock.estado_inventario ei using(id_estado)
      ";

$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);

 $total_bienes_de_uso_dolar=0;
($res->fields["total"])?$bienes_de_uso_pesos=$res->fields["total"]:$bienes_de_uso_pesos=0;
$bienes_de_uso_dolares=0;

$array_bienes_de_uso=array("nombre"=>"Bienes de Uso","total"=>$bienes_de_uso_pesos,"moneda"=>"\$");



/**********************************************************************
                        CAJA
**********************************************************************/
 $mes = substr($fecha,5,2);
 $dia = substr($fecha,8,2);
 $anio = substr($fecha,0,4);
 $nrodiasemana = date('w', mktime(0,0,0,$mes,$dia,$anio));

if ($nrodiasemana==0 || feriado(fecha($fecha))) //si es domingo o feriado
 	 $fecha_caja=fecha_db(dia_habil_anterior(fecha($fecha)));
     else
     $fecha_caja=$fecha;

/**********************************************************************
                        CAJA  DE SEGURIDAD
**********************************************************************/

$sql=" select sum (monto) as total , id_moneda
       from item_caja_seguridad
       join caja_seguridad using(id_caja_seguridad)

       WHERE caja_seguridad.id_caja_seguridad='1'
              and item_caja_seguridad.estado='existente'
       group by  id_moneda";
$result=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);

for ($i=0;$i<$result->recordcount();$i++){
    if ($result->fields["id_moneda"]==1)
              $caja_de_seguridad_pesos=$result->fields["total"];
    if ($result->fields["id_moneda"]==2)
              $caja_de_seguridad_dolar=$result->fields["total"];

    $result->movenext();
}
if (!$caja_de_seguridad_pesos) $caja_de_seguridad_pesos=0;
if (!$caja_de_seguridad_dolar) $caja_de_seguridad_dolar=0;


$caja_pesos_sl=total_caja(1,1,$fecha);
$caja_pesos_bs=total_caja(2,1,$fecha);
$caja_pesos=$caja_pesos_sl + $caja_pesos_bs  + $caja_de_seguridad_pesos;
$caja_dolar_sl=total_caja(1,2,$fecha);
$caja_dolar_bs=total_caja(2,2,$fecha);
$caja_dolares=$caja_dolar_sl + $caja_dolar_bs + $caja_de_seguridad_dolar;


$arreglo_cajas=array();
$arreglo_cajas[] = array("caja"=>"Caja San Luis","moneda"=>"\$","id_moneda"=>1,"total"=>$caja_pesos_sl);
$arreglo_cajas[] = array("caja"=>"Caja San Luis","moneda"=>"u\$s","id_moneda"=>2,"total"=>$caja_dolar_sl);
$arreglo_cajas[] = array("caja"=>"Caja Bs. As.","moneda"=>"\$","id_moneda"=>1,"total"=>$caja_pesos_bs);
$arreglo_cajas[] = array("caja"=>"Caja Bs. As.","moneda"=>"u\$s","id_moneda"=>2,"total"=>$caja_dolar_bs);
$arreglo_cajas[] = array("caja"=>"Caja De Seguridad","moneda"=>"u\$s","id_moneda"=>2,"total"=>$caja_de_seguridad_dolar);
$arreglo_cajas[] = array("caja"=>"Caja De Seguridad","moneda"=>"\$","id_moneda"=>1,"total"=>$caja_de_seguridad_pesos);


/**********************************************************************
                         Depositos pendientes
***********************************************************************/
//depositos pendientes
	  
$sql="SELECT sum(ImporteDep) as total,nombrebanco,idbanco
      FROM bancos.depósitos
      JOIN bancos.tipo_banco using(idbanco)
      WHERE bancos.depósitos.FechaCrédito IS NULL AND tipo_banco.activo=1
      and tipo_banco.idbanco<>10 and  tipo_banco.idbanco<>7 and tipo_banco.idbanco<>8
      group by nombrebanco,idbanco
      ";	  
$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
$arreglo_depositos_pendientes=array();
$depositos_pendientes=0;
for($i=1;$i<=$res->recordcount();$i++){

  $nombre=$res->fields["nombrebanco"];
  $total=($res->fields["total"])?$res->fields["total"]:0;
  $idbanco=$res->fields["idbanco"];
  $arreglo_depositos_pendientes[]=array("nombre"=>$nombre,"total"=>$total,"idbanco"=>$idbanco);
  $res->movenext();
  $depositos_pendientes+=$total;
}
$depositos_pendientes_pesos=$depositos_pendientes;
$depositos_pendientes_dolares=0;

if (!$depositos_pendientes_pesos) $depositos_pendientes_pesos=0;


/********************************************************************
                  Cheques Diferidos Pendientes
*********************************************************************/

$total_cheques_diferidos=0;

	  
$sql="SELECT sum(monto) as total,nombre,id_banco
	  FROM bancos.cheques_diferidos
          join bancos.bancos_cheques_dif using(id_banco)
	  WHERE cheques_diferidos.IdDepósito IS NULL
	  and cheques_diferidos.id_ingreso_egreso IS NULL and activo=1 
      group by nombre,id_banco ";	  

$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
$arreglo_cheques_diferidos=array();
$cheques_diferidos_pendientes=0;
for($i=1;$i<=$res->recordcount();$i++){
    $total=($res->fields["total"])?$res->fields["total"]:0;
    $nombre=$res->fields["nombre"];
    $id_banco=$res->fields["id_banco"];
    $arreglo_cheques_diferidos[]=array("nombre"=>$nombre,"total"=>$total,"id_banco"=>$id_banco);
    $cheques_diferidos_pendientes+=$total;
    $res->movenext();
    }
$cheques_diferidos_pendientes_pesos=$cheques_diferidos_pendientes;
$cheques_diferidos_pendientes_dolares=0;


if (!$cheques_diferidos_pendientes_pesos) $cheques_diferidos_pendientes_pesos=0;
/*******************************************************************************
                        Adelantos
*******************************************************************************/
 $adelantos=sql_adelantos(1); 
 
 $id_detalle_balance_historial=proximo_id_detalle(); 
 $array_adelantos[]=array("nombre"=>"Orden de Compra Pesos",
                          "total"=>$adelantos["monto_pesos"],
                          "moneda"=>"\$",
                          "id_detalle_balance_historial"=>$id_detalle_balance_historial,
                          "datos"=>$adelantos["datos"]);
 
 $id_detalle_balance_historial=proximo_id_detalle();                           
 $array_adelantos[]=array("nombre"=>"Orden de Compra Dolar",
                          "total"=>$adelantos["monto_dolar"],
                          "id_detalle_balance_historial"=>$id_detalle_balance_historial,
                          "moneda"=>"U\$S",
                          "datos"=>$adelantos["datos"]);
 
 $adelantos_pesos+=$adelantos["monto_pesos"];
 $adelantos_dolares+=$adelantos["monto_dolar"];
 
 
 if (!$adelantos_pesos) $adelantos_pesos=0;
 if (!$adelantos_dolares) $adelantos_dolares=0;


/*****************************************************************************
                                        DEBE
******************************************************************************/

/*******************************************************************************
                        Cheques
*******************************************************************************/

$datos=sql_cheques_pendientes(1);
$arreglo_cheques = $datos["montos_por_banco"];
$arreglo_cheques_datos  = $datos["datos"] ;
$cheques_pesos  =  $datos["monto_pesos"] ;
$cheques_dolares = $datos["monto_dolar"];

if (!$cheques_pesos) $cheques_pesos=0;
if (!$cheques_dolares) $cheques_dolares=0;



/*******************************************************************************
                        Deuda Comercial
*******************************************************************************/

//Ordenes de Compra que se recibieron los productos y no se pago nada
//Si se cambia la consulta aca, cambiarla en detalle_deuda_comercial
//ahora tiene en cuenta las ordenes internacionales

$deuda_comercial= sql_deuda_comercial(0,1);

$id_detalle_balance_historial=proximo_id_detalle();
$array_deuda_comercial[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,
                               "nombre"=>"Deuda Comercial Pesos",
                               "monto"=>$deuda_comercial["monto_pesos"],
                               "moneda"=>"\$",
                               "internacional"=>0,
                               "datos"=>$deuda_comercial["datos"]);
                               
$deuda_comercial_pesos=$deuda_comercial["monto_pesos"];

$id_detalle_balance_historial=proximo_id_detalle();    
$array_deuda_comercial[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,
                               "nombre"=>"Deuda Comercial Dolar",
                               "monto"=>$deuda_comercial["monto_dolar"],
                               "moneda"=>"U\$S","internacional"=>0,
                               "datos"=>$deuda_comercial["datos"]);
                               
$deuda_comercial_dolares+=$deuda_comercial["monto_dolar"];
  

$id_detalle_balance_historial=proximo_id_detalle();    

$deuda_comercial_internacional= sql_deuda_comercial(1,1);
$array_deuda_comercial[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,
                               "nombre"=>"Deuda Comercial Internacional Pesos",
                               "monto"=>$deuda_comercial_internacional["monto_pesos"],
                               "moneda"=>"\$",
                               "internacional"=>1,
                               "datos"=>$deuda_comercial_internacional["datos"]);
                               
$deuda_comercial_pesos+=$deuda_comercial_internacional["monto_pesos"];

$id_detalle_balance_historial=proximo_id_detalle();    
$array_deuda_comercial[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,
                               "nombre"=>"Deuda Comercial Internacional Dolar",
                               "monto"=>$deuda_comercial_internacional["monto_dolar"],
                               "moneda"=>"U\$S",
                               "internacional"=>1,
                               "datos"=>$deuda_comercial_internacional["datos"]);
$deuda_comercial_dolares+=$deuda_comercial_internacional["monto_dolar"];



if (!$deuda_comercial_dolares) $deuda_comercial_dolares=0;
if (!$deuda_comercial_pesos) $deuda_comercial_pesos=0;

 /************************************************************************************************************/
 /******************************************* Deuda Financiera  **********************************************/
 /************************************************************************************************************/

$id_detalle_balance_historial=proximo_id_detalle();    

$deuda_financiera= sql_deuda_financiera(1);
$array_deuda_financiera[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,
                               "nombre"=>"Deuda Financiera Pesos",
                               "monto"=>$deuda_financiera["monto_pesos"],
                               "moneda"=>"\$",
                               "datos"=>$deuda_financiera["datos"]);
                               
$deuda_financiera_pesos=$deuda_financiera["monto_pesos"];

$id_detalle_balance_historial=proximo_id_detalle();    
$array_deuda_financiera[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,
                               "nombre"=>"Deuda Financiera Dolar",
                               "monto"=>$deuda_financiera["monto_dolar"],
                               "moneda"=>"U\$S",
                               "datos"=>$deuda_financiera["datos"]);
                               
$deuda_financiera_dolares=$deuda_financiera["monto_dolar"];

 
if (!$deuda_financiera_dolares) $deuda_financiera_dolares=0;
if (!$deuda_financiera_pesos) $deuda_financiera_pesos=0;




//recupero el valor dolar
$sql="select valor from general.dolar_general";
$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
$valor_dolar=$res->fields['valor'];


//Inserto los valores   en las tablas

    $sql="select nextval('bancos.balance_historial_id_balance_historial_seq') as id_balance_historial";
    $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
    $id_balance_historial=$res->fields["id_balance_historial"];

    /*
    //hago esto para sacar la foto del 23 que no se cargo por dar error en una consulta
    if($_ses_user["login"]=="marcos")
     $fecha_hasta='2006-01-23';*/

$campos="
          id_balance_historial,
          cuentas_a_cobrar_pesos,cuentas_a_cobrar_dolares,
          bancos_pesos,bancos_dolares,
          stock_pesos,stock_dolares,
          adelantos_pesos,adelantos_dolares,
          caja_pesos,caja_dolares,
          cheques_diferidos_pendientes_pesos,cheques_diferidos_pendientes_dolares,
          depositos_pendientes_pesos,depositos_pendientes_dolares,
          cheques_pendientes_pesos,cheques_pendientes_dolares,
          deuda_comercial_pesos,deuda_comercial_dolares,
          deuda_financiera_pesos,deuda_financiera_dolares,
          bienes_de_uso_pesos,bienes_de_uso_dolares,
          valor_dolar,fecha,saldo_libre_disponibilidad,suss
          ";
$values="

        $id_balance_historial,
        $cuentas_a_cobrar_pesos,$cuentas_a_cobrar_dolares,
        $bancos_pesos,$bancos_dolares,
        $stock_pesos,$stock_dolares,
        $adelantos_pesos,$adelantos_dolares,
        $caja_pesos,$caja_dolares,
        $cheques_diferidos_pendientes_pesos,$cheques_diferidos_pendientes_dolares,
        $depositos_pendientes_pesos,$depositos_pendientes_dolares,
        $cheques_pesos,$cheques_dolares,
        $deuda_comercial_pesos,$deuda_comercial_dolares,
        $deuda_financiera_pesos,$deuda_financiera_dolares,
        $bienes_de_uso_pesos,$bienes_de_uso_dolares,
        $valor_dolar,'$fecha_hasta',$saldo_libre_disponibilidad,$suss

        ";
$sql="
     insert into balance_historial ($campos) values ($values);
     ";
$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);



   
    $campos=" id_detalle_balance_historial,nombre,moneda,monto,id_tipo_cuenta_balance,id_balance_historial";
    //inserto los detalles correspondientes
   //detalle de banco
  for($i=0;$i<sizeof($arreglo_bancos);$i++){
      $id_detalle_balance_historial=proximo_id_detalle();    
      $nombre=$arreglo_bancos[$i]["nombre"];
      $monto=$arreglo_bancos[$i]["saldo"];

      $values="$id_detalle_balance_historial,'$nombre','\$',$monto,1,$id_balance_historial";
      $sql="insert into detalle_balance_historial ($campos) values ($values)";
      $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
  }

  //

      //inserto cuentas a cobrar
      $id_detalle_balance_historial=proximo_id_detalle();     
      $values="$id_detalle_balance_historial,'Cuentas a Cobrar Pesos','\$',$cuentas_a_cobrar_pesos,3,$id_balance_historial";
      $sql="insert into detalle_balance_historial ($campos) values ($values)";
      $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
      $datos=$cuentas_a_cobrar["datos"];
      //inserto cuentas a cobrar pesos
      for($i=0;$i<count($datos);$i++){
                 if ($datos[$i]["id_moneda"]==1)    
                    insertar_items_balance($id_detalle_balance_historial,$datos[$i]);   
      }
        
      

      $id_detalle_balance_historial=proximo_id_detalle();
      $values="$id_detalle_balance_historial,'Cuentas a Cobrar Dolares','u\$s',$cuentas_a_cobrar_dolares,3,$id_balance_historial";
      $sql="insert into detalle_balance_historial ($campos) values ($values)";
      $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
      
      $datos=$cuentas_a_cobrar["datos"];
      for($i=0;$i<count($datos);$i++){
                 if ($datos[$i]["id_moneda"]==2)    
                    insertar_items_balance($id_detalle_balance_historial,$datos[$i]);   
      }        
     
    

      //inserto el stock
      for($i=0;$i<sizeof($stock_depositos);$i++) {
           $id_detalle_balance_historial=proximo_id_detalle();    
           $nombre=$stock_depositos[$i]["nombre"];
           $monto=$stock_depositos[$i]["total"];
           $moneda=$stock_depositos[$i]["moneda"];

          $values="$id_detalle_balance_historial,'$nombre','$moneda',$monto,2,$id_balance_historial";
          $sql="insert into detalle_balance_historial ($campos) values ($values)";
          $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
      }

      //adelantos
     for($i=0;$i<sizeof($array_adelantos);$i++){
         
           $id_detalle_balance_historial = $array_adelantos[$i]["id_detalle_balance_historial"];    
           $nombre =   $array_adelantos[$i]["nombre"];
           $monto  =   $array_adelantos[$i]["total"];
           $moneda =   $array_adelantos[$i]["moneda"];
           if(!$monto) $monto=0;
          
           $values="$id_detalle_balance_historial,'$nombre','$moneda',$monto,10,$id_balance_historial";
           $sql="insert into detalle_balance_historial ($campos) values ($values)";
           $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
           
           
           //inserto el detalle de deuda comercial
           $datos=$array_adelantos[$i]["datos"];      
           for($y=0;$y<count($datos);$y++){
                  if ($datos[$y]["moneda"]==$moneda) {
                          $array=array("monto" => $datos[$y]["monto"],
                                       "moneda" => $datos[$y]["moneda"],
                                       "descripcion" => $datos[$y]["razon_social"],
                                       "id_licitacion" => $datos[$y]["id_licitacion"],
                                       "nro_orden" => $datos[$y]["nro_orden"]);
                           insertar_items_balance($id_detalle_balance_historial,$array);              
                  }
           
           } 
      }
     

     
     //cajas
      for ($i=0;$i<sizeof($arreglo_cajas);$i++){
          $id_detalle_balance_historial=proximo_id_detalle();    
          $nombre  = $arreglo_cajas[$i]["caja"];
          $total   = $arreglo_cajas[$i]["total"];
          $simbolo = $arreglo_cajas[$i]["moneda"];
          $values  ="$id_detalle_balance_historial,'$nombre','$simbolo',$total,4,$id_balance_historial";
          $sql="insert into detalle_balance_historial ($campos) values ($values)";
          $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
      }

      //depositos pendientes
      for($i=0;$i<sizeof($arreglo_depositos_pendientes);$i++){
              $id_detalle_balance_historial=proximo_id_detalle();    
              $nombre=$arreglo_depositos_pendientes[$i]["nombre"];
              $monto=$arreglo_depositos_pendientes[$i]["total"];
              $values="$id_detalle_balance_historial,'$nombre','\$',$monto,5,$id_balance_historial";
              $sql="insert into detalle_balance_historial ($campos) values ($values)";
              $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
      }


      //cheques diferidos pendientes
      for($i=0;$i<sizeof($arreglo_cheques_diferidos);$i++){
              $id_detalle_balance_historial=proximo_id_detalle();    
              $nombre=$arreglo_cheques_diferidos[$i]["nombre"];
              $monto=$arreglo_cheques_diferidos[$i]["total"];
              $values="$id_detalle_balance_historial,'$nombre','\$',$monto,6,$id_balance_historial";
              $sql="insert into detalle_balance_historial ($campos) values ($values)";
              $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
      }


     //bienes de uso
      $nombre=$array_bienes_de_uso["nombre"];
      $moneda=$array_bienes_de_uso["moneda"];
      $total=$array_bienes_de_uso["total"];
      $id_detalle_balance_historial=proximo_id_detalle();    

      $values="$id_detalle_balance_historial,'$nombre','$moneda',$total,11,$id_balance_historial";
      $sql="insert into detalle_balance_historial ($campos) values ($values)";
      $db->execute($sql) or die($db->errormsg()." <br> ".$sql);



      /*******************************************************************************************/
      //                      DEBE
      /*******************************************************************************************/
      //cheques
      for($i=0;$i<sizeof($arreglo_cheques);$i++){
              $id_detalle_balance_historial=proximo_id_detalle();                  
              $nombre=$arreglo_cheques[$i]["nombre"];
              $monto=$arreglo_cheques[$i]["total"];
              $values="$id_detalle_balance_historial,'$nombre','\$',$monto,7,$id_balance_historial";
              $sql="insert into detalle_balance_historial ($campos) values ($values)";
              $db->execute($sql) or die($db->errormsg()." <br> ".$sql);
              
              for ($y = 0;$y < sizeof ($arreglo_cheques_datos) ; $y++ ){
               if ($arreglo_cheques_datos[$y]["idbanco"]==$arreglo_cheques[$i]["idbanco"]){
                          $array=array("monto" => $arreglo_cheques_datos[$y]["monto"],
                                       "moneda" => $arreglo_cheques_datos[$y]["moneda"],
                                       "descripcion" => $arreglo_cheques_datos[$y]["descripcion"],
                                       );
                           insertar_items_balance($id_detalle_balance_historial,$array);                                      
                        
                    }
              }
      }


      
      //deuda comercial
     
      for($i=0;$i<count($array_deuda_comercial);$i++){

           $nombre  = $array_deuda_comercial[$i]["nombre"];
           $monto   = $array_deuda_comercial[$i]["monto"];
           $moneda  = $array_deuda_comercial[$i]["moneda"];
           $id_detalle_balance_historial = $array_deuda_comercial[$i]["id_detalle_balance_historial"];
           $values = "$id_detalle_balance_historial,'$nombre','$moneda',$monto,8,$id_balance_historial";
           $sql = "insert into detalle_balance_historial ($campos) values ($values)";
           $db->execute($sql) or die($db->errormsg()." Deuda comercial <br> ".$sql);      
           
           
           //inserto el detalle de deuda comercial
           $datos=$array_deuda_comercial[$i]["datos"];      

           for($y=0;$y<count($datos);$y++){
                  if ($datos[$y]["moneda"]==$moneda) {
                              				  
                          $array=array("monto" => $datos[$y]["monto"],
                                       "moneda" => $datos[$y]["moneda"],
                                       "descripcion" => $datos[$y]["razon_social"],
                                       "id_licitacion" => $datos[$y]["id_licitacion"],
                                       "nro_orden" => $datos[$y]["nro_orden"]);
                           insertar_items_balance($id_detalle_balance_historial,$array);              
                  }
           }     //del for de detalle deuda comercial            
        }   //del for de deuda comercial

     
      
      for($i=0;$i<count($array_deuda_financiera);$i++){

           $nombre  = $array_deuda_financiera[$i]["nombre"];
           $monto   = $array_deuda_financiera[$i]["monto"];
           $moneda  = $array_deuda_financiera[$i]["moneda"];
           $id_detalle_balance_historial = $array_deuda_financiera[$i]["id_detalle_balance_historial"];
           $values = "$id_detalle_balance_historial,'$nombre','$moneda',$monto,9,$id_balance_historial";
           $sql = "insert into detalle_balance_historial ($campos) values ($values)";
           
           
           $db->execute($sql) or die($db->errormsg()." Deuda comercial <br> ".$sql);      
           
      
           //inserto el detalle de deuda financiera
           $datos=$array_deuda_financiera[$i]["datos"];      

           for($y=0;$y<count($datos);$y++){
                  if ($datos[$y]["moneda"]==$moneda) {
				          
                          $array=array("monto" => $datos[$y]["monto"],
                                       "moneda" => $datos[$y]["moneda"],
                                       "id_licitacion" => $datos[$y]["id_licitacion"],
                                       "nro_factura" => $datos[$y]["nro_factura"],
									   "descripcion" => $datos[$y]["descripcion"]
									   );
                           insertar_items_balance($id_detalle_balance_historial,$array);              
                  }
           }     //del for de detalle deuda comercial            
        }   //del for de deuda comercial
       
$db->completetrans();