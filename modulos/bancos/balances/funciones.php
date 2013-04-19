<?
/*
Autor: MAC
Fecha: 23/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2006/07/11 14:45:26 $

*/

require_once("../../../config.php");

/************************************************************
funcion que genera el listado de distritos que pueden ir para
 retencion ingresos brutos (parametro 0),
 percepcion ingresos brutos (parametro 1), o
 retencion bancaria (parametro 2)

Los nombres de los campos de monto generados, tienen un nombre
de la forma:
monto_<tipo>_<nbre_distrito>
El parametro $valores, tiene los valores que van en el campo
de montos, de cada distrito
El parametro $formato sirve para decir si debe generar las
tablas con formato (para mostrar en la pagina de carga de datos),
o sin formato (para imprimir el asiento)
*************************************************************/
function generar_lista_provincias($clase,$valores,$formato=1,$onchange="")
{global $bgcolor_out,$actualizar;


    for($i=0;$i<sizeof($valores);$i++)
    {
     $aux=$valores[$i]["plan"];
     if($aux!="")
     { $dist=$aux;

      switch ($clase)
      {case 0:if($onchange=="")
                $onchange="calcular_ret_ing_brutos();";
              $readonly="readonly";
              break;
       case 1:if($onchange=="")
                $onchange="calcular_per_ing_brutos();";
              if(!$actualizar)
               $readonly="";
              else
               $readonly="readonly";
              break;
       case 2:if($onchange=="")
                $onchange="calcular_ret_bancaria();";
              $readonly="readonly";
              break;
      }
    ?>
     <tr <?if($formato)echo "bgcolor='$bgcolor_out'"?>>
      <td width="10%">
       &nbsp;
      </td>
      <td width="40%" align="right">
       <?=$dist?>
      </td>
      <td width="20%">
       <?if($formato)
         {?>
          <input type="text" name="monto_<?=$clase?>_<?=$i?>"   value="<?=number_format($valores[$i]["monto"],2,'.','')?>" <?=$readonly?> size="13" onchange="<?=$onchange?>hay_cambios();" onkeypress="return filtrar_teclas(event,'0123456789.');">
          <input type="hidden" name="dist_<?=$clase?>_<?=$i?>" value="<?=$dist?>">
         <?
         }
         else
          echo number_format($valores[$i]["monto"],2,'.','');
         ?>
      </td>
      <td width="15%">
       &nbsp;
      </td>
     </tr>
<?
    }//de if($aux[0]=="Ingresos Brutos a Pagar" && $aux[1]!="")
   }//de  for($i=0;$i<sizeof($valores);$i++)

}//de function generar_lista_provincias($clase)


/********************************************************
Funcion que inserta los datos en la tabla
percepcion_retencion, para el asiento de retencion pasado
como parametro
*********************************************************/
function insertar_percepcion_retencion($id_retenciones)
{global $cant_ret_ing_brutos,$cant_per_ing_brutos,$cant_ret_bancaria,$db;
 $db->StartTrans();
    //insertamos las retenciones de ingresos brutos
 	for($g=0;$g<$cant_ret_ing_brutos;$g++)
 	{//Se podria usar el id del distrito, sacandolo de la tabla distrito,
 	 //pero si le cambian el nombre a un distrito, puede fallar el modulo.
 	 // Asi que en lugar de eso, se guarda el nombre en esta tabla.
 	 $dist=$_POST["dist_0_".$g];

 	 $monto=$_POST["monto_0_".$g];

 	 $query="insert into percepcion_retencion (id_retenciones,nombre_distrito,monto,clase)
 	         values($id_retenciones,'$dist',$monto,0)";
 	 sql($query,"<br>Error al insertar ret ib $g") or fin_pagina();
 	}

 	//insertamos las percepciones de ingresos brutos
 	for($g=0;$g<$cant_per_ing_brutos;$g++)
 	{
 	 //Se podria usar el id del distrito, sacandolo de la tabla distrito,
 	 //pero si le cambian el nombre a un distrito, puede fallar el modulo.
 	 // Asi que en lugar de eso, se guarda el nombre en esta tabla.
 	 $dist=$_POST["dist_1_".$g];

 	 $monto=$_POST["monto_1_".$g];

 	 $query="insert into percepcion_retencion (id_retenciones,nombre_distrito,monto,clase)
 	         values($id_retenciones,'$dist',$monto,1)";
 	 sql($query,"<br>Error al insertar percepcion ib $g") or fin_pagina();
 	}

 	//insertamos las retenciones bancarias
 	for($g=0;$g<$cant_ret_bancaria;$g++)
 	{//Se podria usar el id del distrito, sacandolo de la tabla distrito,
 	 //pero si le cambian el nombre a un distrito, puede fallar el modulo.
 	 // Asi que en lugar de eso, se guarda el nombre en esta tabla.
 	 $dist=$_POST["dist_2_".$g];

 	 $monto=$_POST["monto_2_".$g];

 	 $query="insert into percepcion_retencion (id_retenciones,nombre_distrito,monto,clase)
 	         values($id_retenciones,'$dist',$monto,2)";
 	 sql($query,"<br>Error al insertar ret bancaria $g") or fin_pagina();
 	}

 $db->CompleteTrans();
}//de function insertar_percepcion_retencion($id_retenciones)


/**********************************************************
Funcion que inserta los montos para cada cuenta del asiento
de compras, en la tabla cuentas_compras
***********************************************************/
function  insertar_cuentas_compras($id_asiento_compras)
{global $cant_cuentas,$db;
 $db->StartTrans();
 //insertamos las cuentas_compras
 for($g=0;$g<$cant_cuentas;$g++)
 {
  $numero_cuenta=$_POST["nro_cuenta_".$g];
  $monto=$_POST["cuenta_".$g];

  $query="insert into cuentas_compras (id_asiento_compras,monto,numero_cuenta)
          values($id_asiento_compras,$monto,$numero_cuenta)";
  sql($query,"<br>Error al insertar cuenta de compras $g") or fin_pagina();
 }

 $db->CompleteTrans();
}//de function insertar_cuentas_compras($id_asiento_compras)


/********************************************************
Funcion que inserta los datos en la tabla
retencion_ib, para el asiento de bancos pasado
como parametro
*********************************************************/
function insertar_retencion_ib($id_asiento_bancos)
{global $cant_ret_ing_brutos,$db;
 $db->StartTrans();
    //insertamos las retenciones de ingresos brutos
 	for($g=0;$g<$cant_ret_ing_brutos;$g++)
 	{//Se podria usar el id del distrito, sacandolo de la tabla distrito,
 	 //pero si le cambian el nombre a un distrito, puede fallar el modulo.
 	 // Asi que en lugar de eso, se guarda el nombre en esta tabla.
 	 $dist=$_POST["dist_0_".$g];
 	 $monto=$_POST["monto_0_".$g];

 	 $query="insert into retencion_ib (id_asiento_bancos,nombre_distrito,monto)
 	         values($id_asiento_bancos,'$dist',$monto)";
 	 sql($query,"<br>Error al insertar ret ib $g") or fin_pagina();
 	}

 $db->CompleteTrans();
}//de function insertar_retencion_ib($id_asiento_bancos)

?>