<?
/*

Author: marco_canderle

$Author: cestila $
$Revision: 1.16 $
$Date: 2005/07/20 21:56:48 $

*/

require_once("../../config.php");
echo $html_header;
$error = 0;

function generar_porcentaje_grafica($presentadas,$total_presentadas,$terminadas,$total_terminadas){

?>
  <table align=left width=100%>
  <?if ($total_terminadas) {?>
          <tr>
           <td id=ma width=20%>
           Terminadas<br>
           (<?=$total_terminadas?>)
           </td>
           <td>
             <table width=100% align=center border=1 cellspacing=0 cellpadding=0  bordercolor=<?=$bgcolor5?>>
               <tr id=mo>
                 <td width=33% align=center><b>Estado</b></td>
                 <td width=33% align=center> <b>Cantidad</b></td>
                 <td width=34% align=center><b>%</b></td>
               </tr>
               <?

               foreach ($terminadas as $key => $value){

                $porcentaje=number_format(($value*100)/$total_terminadas,2,".","");
                $data_terminadas[]=$porcentaje;
                $leyenda_terminadas[]=$key;
                if (!$value) $value=0;
               ?>
               <tr>
                <td><b><?=$key?></td>
                <td align=center><?=$value?></td>
                <td align=center><?=$porcentaje;?></td>
               </tr>
               <?
               }
               ?>
             </table>
           </td>
          </tr>
          <?
          }//del if de total terminadas
          ?>
          <tr><td colspan=2>&nbsp;</td></tr>
          <?if ($total_presentadas){?>
          <tr>
            <td id=ma >
            Presentadas/Próximas<br>
            (<?=$total_presentadas?>)
            </td>
            <td>
             <table width=100% align=center border=1 cellspacing=0 cellpadding=0  bordercolor=<?=$bgcolor5?>>
               <tr id=mo>
                 <td width=33% align=center><b>Estado</b></td>
                 <td width=33% align=center> <b>Cantidad</b></td>
                 <td width=34% align=center><b>%</b></td>
               </tr>

               <?

               foreach ($presentadas as $key => $value){
                Switch ($key) {
                case "Presuntamente ganada":
                      $key="P. Ganadas";
                      break;
                case "Orden de compra":
                     $key="O. de Compra";
                     break;
                }
                 if (!$value) $value=0;
                 $porcentaje=number_format(($value*100)/$total_presentadas,2,".","");
                 $leyenda_presentadas[]=$key;
                 $data_presentadas[]=$porcentaje;
                ?>
               <tr>
                <td><b><?=$key?></td>
                <td align=center><?=$value?></td>
                <td align=center><?=$porcentaje;?></td>
               </tr>
               <?
               }
               ?>
             </table>

            </td>
          </tr>
         <?
          } //del if de total presentadas
         ?>
        </table>
       </td>
    </tr>
   <tr>
   <td  align=center  colspan=2>
   <table width=100% align=Center>
   <tr>
   <td width=50% align=Center>
    <?
    if($total_terminadas>0)
    {
    $link_s=encode_link("lic_graficas.php",array("data"=>$data_terminadas,"leyenda"=>$leyenda_terminadas,"titulo"=>"Licitaciones Terminadas","tamaño"=>"small"));
    $link_l=encode_link("lic_graficas.php",array("data"=>$data_terminadas,"leyenda"=>$leyenda_terminadas,"titulo"=>"Licitaciones Terminadas","tamaño"=>"large"));
    echo "<a href='$link_l' target='_blank'><img src='$link_s'  border=0 align=top></a>\n";
    }
    ?>
    </td>
   <td width=50% align=center>
   <? if ($total_presentadas>0)
      {
      $link_s=encode_link("lic_graficas.php",array("data"=>$data_presentadas,"leyenda"=>$leyenda_presentadas,"titulo"=>"Licitaciones Presentadas","tamaño"=>"small"));
      $link_l=encode_link("lic_graficas.php",array("data"=>$data_presentadas,"leyenda"=>$leyenda_presentadas,"titulo"=>"Licitaciones Presentadas","tamaño"=>"large"));
      echo "<a href='$link_l' target='_blank'><img src='$link_s'  border=0 align=top></a>\n";
      }
    ?>
    </td>
    </tr>
    </table>
</td>
</tr>

</table>

<?

} // de la funciom que genera los porcentajes y las graficas



variables_form_busqueda("stat");

if ($_POST["stat_update"]) {
	$stat_fecha_desde = $_POST["stat_fecha_desde"];
	$stat_fecha_hasta = $_POST["stat_fecha_hasta"];
	if (!FechaOk($stat_fecha_desde)) {
		Error("El formato de la fecha de inicio no es válido");
	}
	else {
		$fecha_desde = strtotime(Fecha_db($stat_fecha_desde));
	}
	if (!FechaOk($stat_fecha_hasta)) {
		Error("El formato de la fecha de finalización no es válido");
	}
	else {
		$fecha_hasta = strtotime(Fecha_db($stat_fecha_hasta));
	}
	if ($fecha_desde > $fecha_hasta) {
		Error("Las fechas no son válidas");
	}
	$show_detalles = $_POST["stat_show_detalles"];
	$show_ofertado = $_POST["stat_show_ofertado"];
	$show_estimado = $_POST["stat_show_estimado"];
	$show_ganado = $_POST["stat_show_ganado"];
}
else {
	$fecha_desde = mktime() - (60 * 60 * 24 * 30);
	$fecha_hasta = mktime();
}

if (!$show_ofertado and !$show_estimado and !$show_ganado and !$show_detalles) {
	$show_detalles = 1;
	$show_ofertado = 1;
	$show_estimado = 1;
	$show_ganado = 1;
}
$stat_fecha_desde=date("d/m/Y",$fecha_desde);
$stat_fecha_hasta=date("d/m/Y",$fecha_hasta);
cargar_calendario();
echo "<form action='licitaciones_stats.php' method=post>\n";

echo "<table class='bordes' cellspacing=3 cellpadding=3  align=center width=75% bgcolor=$bgcolor_out>\n";
echo "<tr id=mo><td colspan=2>Estadísticas de las Licitaciones</td></tr>";


echo "<tr>\n";

echo "<td ><b>Fecha </b>\n</td>";
echo "<td> ";

  echo "<table align=center width=100%>";
   echo "<tr>";
    echo "<td>";
    echo "<b>Desde:&nbsp;<input type=text name=stat_fecha_desde value='$stat_fecha_desde' size=10 maxlength=10>";
    echo link_calendario("stat_fecha_desde");
    echo "&nbsp; ";
    echo "<b>Hasta: </b>\n";
    echo "<input type=text name=stat_fecha_hasta value='$stat_fecha_hasta' size=10 maxlength=10>";
    echo link_calendario("stat_fecha_hasta");
    echo "</td>";
    /*
    echo "<td align=right>";
    $onclick="window.open(\"licitaciones_stats_estados.php\")";
    echo "<input type=button name='estadisticas' value='Más' onclick='$onclick'>";
    echo "</td>";
    */
   echo "</tr>";
  echo "</table>";

echo "</td>";
echo "</tr>\n";

echo "<tr>";
echo "<td><b>Resultados cargados: </b></td>";
echo "<td><select name=stat_resultados>\n";
echo "<option value='todos'>Todos</options>\n";
echo "<option value='1'";
if ($_POST["stat_resultados"]==1) echo " Selected";
echo ">Si</options>\n";
echo "<option value='no'";
if ($_POST["stat_resultados"]=='no') echo " Selected";
echo ">No</options>\n";
echo "</select>\n";
echo "</td></tr>";

$sql = "select id_estado,nombre from estado";
$result = $db->Execute($sql) or die($db->ErrorMsg());
echo "<tr>";

echo "<td><b>Estado: </b></td>";
echo "<td><select name=stat_estado>\n";
echo "<option value=''>Todos\n";
echo make_options($result,"id_estado","nombre",$_POST["stat_estado"]);
echo "</select>\n";
echo "</td></tr>";

$sql = "select id_moneda,nombre from moneda";
$result = $db->Execute($sql) or die($db->ErrorMsg());
echo "<tr>\n";

echo "<td><b>Moneda: </b>\n</td>";
echo "<td><select name=stat_moneda>\n";
echo "<option value=''>Cualquiera";
echo make_options($result,"id_moneda","nombre",$_POST["stat_moneda"]);
echo "</select></td>\n";
echo "</tr>";

echo "<tr>\n";

echo "<td ><b>Ordenado por: </b></td>\n";
echo "<td><select name=stat_orden>\n";
echo "<option value='Apertura'";
if ($_POST["stat_orden"] == "Apertura") echo " selected";
echo ">Fecha de Apertura";
echo "<option value='ID'";
if ($_POST["stat_orden"] == "ID") echo " selected";
echo ">ID de la Licitación";
echo "<option value='Ofertado'";
if ($_POST["stat_orden"] == "Ofertado") echo " selected";
echo ">Monto Ofertado";
echo "<option value='Estimado'";
if ($_POST["stat_orden"] == "Estimado") echo " selected";
echo ">Monto Estimado";
echo "<option value='Ganado'";
if ($_POST["stat_orden"] == "Ganado") echo " selected";
echo ">Monto Ganado";
echo "</select>\n";
echo "<b> Dirección: </b>\n";
echo "<input type=radio name=stat_direccion value='DESC'";
if (($_POST["stat_direccion"] == "DESC") or ($_POST["stat_direccion"] == "")) echo " checked";
echo "> Descendente ";
echo "<input type=radio name=stat_direccion value='ASC'";
if ($_POST["stat_direccion"] == "ASC") echo " checked";
echo "> Ascendente ";
echo "</td>\n";
echo "</tr>";
echo "<tr>\n";

echo "<td><b>Mostrar:</b></td> ";
echo "<td align=center><table width=70% align=center>";
//echo "<td valign=top rowspan=2><b>Mostrar:</b></td>\n";
echo "<td align=left valign=top>\n";
echo "<input type=checkbox name=stat_show_detalles";
if ($show_detalles) echo " checked";
echo "> Detalles<br>\n";
echo "<input type=checkbox name=stat_show_ofertado";
if ($show_ofertado) echo " checked";
echo "> Total Ofertado<br>\n";
echo "</td>";
echo "<td>";
echo "<input type=checkbox name=stat_show_estimado";
if ($show_estimado) echo " checked";
echo "> Total Estimado<br>\n";
echo "<input type=checkbox name=stat_show_ganado";
if ($show_ganado) echo " checked";
echo "> Total Ganado<br>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>";
echo "</td>";
echo "<tr>\n";

echo "<td colspan=3 align=center>\n";

$sql = "select licitacion.id_licitacion,";
$sql .= "licitacion.monto_ofertado,";
$sql .= "licitacion.monto_estimado,";
$sql .= "licitacion.monto_ganado,";
$sql .= "licitacion.id_estado,";
$sql .= "estado.ubicacion,";
$sql .= "licitacion.fecha_apertura,";
$sql .= "licitacion.fecha_entrega,";
$sql .= "licitacion.id_moneda,";
$sql .= "entidad.nombre as nombre_entidad,";
$sql .= "distrito.nombre as nombre_distrito ";
//$sql .= "from licitaciones.licitaciones,licitaciones.entidades,licitaciones.distrito ";
$sql .= "from (licitacion ";
$sql .= "left join entidad ";
$sql .= "using (id_entidad)) ";
$sql .= "left join distrito ";
$sql .= "using (id_distrito) ";
$sql .= "left join estado ";
$sql .= "using (id_estado) ";

$stat_fecha_desde=Fecha_db($stat_fecha_desde);
$stat_fecha_hasta=Fecha_db($stat_fecha_hasta);
//$sql .= "where licitaciones.licitaciones.IDEntidad=licitaciones.entidades.IDEntidad and licitaciones.entidades.IDDistrito=licitaciones.distrito.IDDistrito ";
$where_tmp = "(fecha_apertura between '$stat_fecha_desde' and '$stat_fecha_hasta') and es_presupuesto=0 and borrada='f'";
//$sql .= "and Fecha >= '$stat_fecha_desde' ";
//$sql .= "and Fecha <= '$stat_fecha_hasta' ";
if ($_POST["stat_estado"] != "") {
		$where_tmp .= " and id_estado=$_POST[stat_estado]";
}
if ($_POST["stat_moneda"] != "") {
		$where_tmp .= " and id_moneda=$_POST[stat_moneda]";
}
if ($_POST["stat_resultados"] != "todos"){
	if ($_POST["stat_resultados"]=="no")
		$where_tmp .= "and resultados_cargados is null ";
	else $where_tmp .= "and resultados_cargados=".$_POST["stat_resultados"]." ";
}

switch ($_POST["stat_orden"]) {
						 case "Apertura": $sort=1 ; break;
						 case "ID": $sort=2; break;
						 case "Ofertado": $sort=3; break;
						 case "Estimado": $sort=4; break;
						 case "Ganado": $sort=5; break;
						 default: $sort=6; break;
}
$up=($_POST["stat_direccion"]=='ASC')?"1":"0";

$orden_array= array
(
		"default" => 6,
		"1" => "licitacion.fecha_apertura",
		"2" => "licitacion.id_licitacion ",
		"3" => "licitacion.monto_ofertado ",
		"4" => "licitacion.monto_estimado ",
		"5" => "licitacion.monto_ganado ",
		"6" => "licitacion.fecha_apertura "
);
$filtro_array= array
(
	"licitacion.observaciones"=>"Comentarios",
	"entidad.direccion"=>"Direccion",
	"distrito.nombre"=>"Distrito",
	"entidad.nombre"=>"Entidad",
	"licitacion.forma_de_pago"=>"Forma de Pago",
	"licitacion.mant_oferta_especial"=>"Mantenimiento de Oferta",
	"licitacion.nro_lic_codificado"=>"Nº Licitacion",
	"licitacion.id_licitacion"=>"ID Licitacion"
);
$itemspp=5000;

list($sql,$total,$link_pagina,$up) = form_busqueda($sql,$orden_array,$filtro_array,$link_tmp,$where_tmp);

echo "<input type=submit name=stat_update value='Buscar'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</form>";

//echo $sql;


if ($_POST["stat_update"] and !$error) {

//$result = $db->Execute($sql) or die($db->ErrorMsg());
$result = sql($sql) or fin_pagina();

$Pres_Ofertado_Pesos = 0;
$Pres_Estimado_Pesos = 0;
$Pres_Ganado_Pesos = 0;
$Pres_Ofertado_Dolares = 0;
$Pres_Estimado_Dolares = 0;
$Pres_Ganado_Dolares = 0;
$Prox_Ofertado_Pesos = 0;
$Prox_Estimado_Pesos = 0;
$Prox_Ganado_Pesos = 0;
$Prox_Ofertado_Dolares = 0;
$Prox_Estimado_Dolares = 0;
$Prox_Ganado_Dolares = 0;
$Hist_Ofertado_Pesos = 0;
$Hist_Estimado_Pesos = 0;
$Hist_Ganado_Pesos = 0;
$Hist_Ofertado_Dolares = 0;
$Hist_Estimado_Dolares = 0;
$Hist_Ganado_Dolares = 0;
$Cont_Proximas = 0;
$Cont_Presentadas = 0;
$Cont_Historial = 0;
/*$Cont_En_Curso = 0;
$Cont_Finalizado = 0;
$Cont_Presuntamente_Ganado = 0;
$Cont_Ganado = 0;
$Cont_Impugnado = 0;
*/
while (!$result->EOF) {
//		$fecha_hoy = mktime(0,0,0,date("m"),date("d"),date("Y"));
		$fecha_hoy = mktime();
		$dl = substr($result->fields["fecha_apertura"],8,2);
		$ml = substr($result->fields["fecha_apertura"],5,2);
		$al = substr($result->fields["fecha_apertura"],0,4);
		$fecha_lic = mktime(0,0,0,$ml,$dl,$al);
		switch ($result->fields["id_estado"]) {
				case 0:
						$en_curso++;
						break;
				case 1:
						$entregada++;
						break;
				case 2:
						$presuntamente_ganada++;
						break;
				case 3:
						$preadjudicada++;
						break;
				case 4:
						$impugnada++;
						break;
				case 5:
						$perdida++;
						break;
				case 6:
						$robada++;
						break;
				case 7:
						$orden_de_compra++;
						break;
				case 8:
						$fracasada++;
						break;
				case 9:
						$multa++;
						break;

				case 10:
						$presupuesto++;
						break;


		} // del switch
//        $Contador_Estados[$result->fields["id_estado"]]++;

        if ($fecha_hoy < $fecha_lic) {
				if ($result->fields["ubicacion"] == "HISTORIAL" ) {
						$Cont_Historial++;
						if ($result->fields["id_moneda"] == "1") {
								$Hist_Ofertado_Pesos += $result->fields["monto_ofertado"];
								$Hist_Estimado_Pesos += $result->fields["monto_estimado"];
								$Hist_Ganado_Pesos += $result->fields["monto_ganado"];
						}
						else {
								$Hist_Ofertado_Dolares += $result->fields["monto_ofertado"];
								$Hist_Estimado_Dolares += $result->fields["monto_estimado"];
								$Hist_Ganado_Dolares += $result->fields["monto_ganado"];
						}
				}
				else {

						$Cont_Proximas++;
						if ($result->fields["id_moneda"] == "1") {
								$Prox_Ofertado_Pesos += $result->fields["monto_ofertado"];
								$Prox_Estimado_Pesos += $result->fields["monto_estimado"];
								$Prox_Ganado_Pesos += $result->fields["monto_ganado"];
						}
						else {
								$Prox_Ofertado_Dolares += $result->fields["monto_ofertado"];
								$Prox_Estimado_Dolares += $result->fields["monto_estimado"];
								$Prox_Ganado_Dolares += $result->fields["monto_ganado"];
						}
				}
		}
		else {
            if ($result->fields["ubicacion"] == "HISTORIAL") {
						$Cont_Historial++;
						if ($result->fields["id_moneda"] == "1") {
								$Hist_Ofertado_Pesos += $result->fields["monto_ofertado"];
								$Hist_Estimado_Pesos += $result->fields["monto_estimado"];
								$Hist_Ganado_Pesos += $result->fields["monto_ganado"];
						}
						else {
								$Hist_Ofertado_Dolares += $result->fields["monto_ofertado"];
								$Hist_Estimado_Dolares += $result->fields["monto_estimado"];
								$Hist_Ganado_Dolares += $result->fields["monto_ganado"];
						}
				}
				else {
						$Cont_Presentadas++;
						if ($result->fields["id_moneda"] == "1") {
								$Pres_Ofertado_Pesos += $result->fields["monto_ofertado"];
								$Pres_Estimado_Pesos += $result->fields["monto_estimado"];
								$Pres_Ganado_Pesos += $result->fields["monto_ganado"];
						}
						else {
								$Pres_Ofertado_Dolares += $result->fields["monto_ofertado"];
								$Pres_Estimado_Dolares += $result->fields["monto_estimado"];
								$Pres_Ganado_Dolares += $result->fields["monto_ganado"];
						}
				}
		}
                //Esto tengo que moverlo a un while mas abajo
                /*
		if ($_POST["stat_show_detalles"]) {
				detalle($result->fields["id_licitacion"]);
		}
                */
		$result->MoveNext();
}

 //genero el arreglo para pasar como parametros  a la funcion
//arreglo terminadas
$terminadas["Entregada"]=$entregada;
$terminadas["Perdida"] =$perdida;
$terminadas["Robada"]=$robada;
$terminadas["Fracasada"]=$fracasada;

$total_terminadas=$entregada + $perdida + $robada + $fracasada;


//arreglo presentadas

$presentadas["En Curso"]=$en_curso;
$presentadas["Presuntamente ganada"]=$presuntamente_ganada;
$presentadas["Preadjudicada"]=$preadjudicada;
$presentadas["Orden de Compra"]=$orden_de_compra;

$total_presentadas=$en_curso + $presuntamente_ganada + $preadjudicada + $orden_de_compra;
// fin de la creacion de datos


$Total = $Cont_Proximas + $Cont_Presentadas + $Cont_Historial;
echo "<br><br>\n";
echo "<table width=90% bgcolor=$bgcolor2 class='bordes' cellspacing=3 cellpadding=3 align=center>\n";
echo "<tr align=center id=mo>\n";
echo "<td colspan=2 width=33%>Próximas</td>\n";
echo "<td colspan=2 width=33%>Presentadas</td>\n";
echo "<td colspan=2 width=34%>Terminadas/Entregadas</td>\n";
echo "</tr>\n";
echo "<tr align=center id=ma>\n";
echo "<td>Pesos</td><td>Dólares</td>\n";
echo "<td>Pesos</td><td>Dólares</td>\n";
echo "<td>Pesos</td><td>Dólares</td>\n";
echo "</tr>\n";
echo "<tr bgcolor=$bgcolor_out>\n";
echo "<td>\n";
if ($_POST["stat_show_ofertado"]) echo "Ofertado:&nbsp;".formato_money($Prox_Ofertado_Pesos)."<br>\n";
if ($_POST["stat_show_estimado"]) echo "Estimado:&nbsp;".formato_money($Prox_Estimado_Pesos)."<br>\n";
if ($_POST["stat_show_ganado"]) echo "Ganado:&nbsp;".formato_money($Prox_Ganado_Pesos)."<br>\n";
echo "</td>\n";
echo "<td>\n";
if ($_POST["stat_show_ofertado"]) echo "Ofertado:&nbsp;".formato_money($Prox_Ofertado_Dolares)."<br>\n";
if ($_POST["stat_show_estimado"]) echo "Estimado:&nbsp;".formato_money($Prox_Estimado_Dolares)."<br>\n";
if ($_POST["stat_show_ganado"]) echo "Ganado:&nbsp;".formato_money($Prox_Ganado_Dolares)."<br>\n";
echo "</td>\n";
echo "<td>\n";
if ($_POST["stat_show_ofertado"]) echo "Ofertado:&nbsp;".formato_money($Pres_Ofertado_Pesos)."<br>\n";
if ($_POST["stat_show_estimado"]) echo "Estimado:&nbsp;".formato_money($Pres_Estimado_Pesos)."<br>\n";
if ($_POST["stat_show_ganado"]) echo "Ganado:&nbsp;".formato_money($Pres_Ganado_Pesos)."<br>\n";
echo "</td>\n";
echo "<td>\n";
if ($_POST["stat_show_ofertado"]) echo "Ofertado:&nbsp;".formato_money($Pres_Ofertado_Dolares)."<br>\n";
if ($_POST["stat_show_estimado"]) echo "Estimado:&nbsp;".formato_money($Pres_Estimado_Dolares)."<br>\n";
if ($_POST["stat_show_ganado"]) echo "Ganado:&nbsp;".formato_money($Pres_Ganado_Dolares)."<br>\n";
echo "</td>\n";
echo "<td>\n";
if ($_POST["stat_show_ofertado"]) echo "Ofertado:&nbsp;".formato_money($Hist_Ofertado_Pesos)."<br>\n";
if ($_POST["stat_show_estimado"]) echo "Estimado:&nbsp;".formato_money($Hist_Estimado_Pesos)."<br>\n";
if ($_POST["stat_show_ganado"]) echo "Ganado:&nbsp;".formato_money($Hist_Ganado_Pesos)."<br>\n";
echo "</td>\n";
echo "<td>\n";
if ($_POST["stat_show_ofertado"]) echo "Ofertado:&nbsp;".formato_money($Hist_Ofertado_Dolares)."<br>\n";
if ($_POST["stat_show_estimado"]) echo "Estimado:&nbsp;".formato_money($Hist_Estimado_Dolares)."<br>\n";
if ($_POST["stat_show_ganado"]) echo "Ganado:&nbsp;".formato_money($Hist_Ganado_Dolares)."<br>\n";
echo "</td>\n";
echo "</tr>\n";
echo "<tr id=ma>\n";
echo "<td colspan=2>\n";
echo "Total: $Cont_Proximas\n";
if ($Cont_Proximas == 1) echo " Licitación";
else echo " Licitaciones";
echo "</td>\n";
echo "<td colspan=2>\n";
echo "Total: $Cont_Presentadas\n";
if ($Cont_Presentadas == 1) echo " Licitación";
else echo " Licitaciones";
echo "</td>\n";
echo "<td colspan=2>\n";
echo "Total: $Cont_Historial\n";
if ($Cont_Historial == 1) echo " Licitación";
else echo " Licitaciones";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";


if ($_POST["stat_estado"]== "")  {
    //unicamente muestro el grafico cuando no elige
    //ningun estado para buscar
    echo "<table width=90% align=center>\n";
    echo "<tr>\n";
    echo "<td>\n";
         generar_porcentaje_grafica($presentadas, $total_presentadas, $terminadas, $total_terminadas);
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
}

//si elige detalle muestro el detalle de la licitacion
if ($_POST["stat_show_detalles"]) {
                $result->move(0);
                while(!$result->EOF){
 		detalle($result->fields["id_licitacion"]);
                $result->MoveNext();
		} // del detalle
}//del if


}

function detalle($ID) {
	global $db,$bgcolor2,$bgcolor3,$html_root,$estados;
	if ($ID) {
		$sql = "SELECT licitacion.*, ";
		$sql .= "entidad.nombre as nombre_entidad, ";
		$sql .= "entidad.direccion, ";
		$sql .= "tipo_entidad.nombre as tipo_entidad, ";
		$sql .= "distrito.nombre as nombre_distrito, ";
		$sql .= "moneda.nombre as nombre_moneda, ";
		$sql .= "estado.nombre as nombre_estado, ";
		$sql .= "estado.color as color_estado ";
		$sql .= "FROM (((licitacion LEFT JOIN entidad ";
		$sql .= "USING (id_entidad)) ";
		$sql .= "LEFT JOIN distrito ";
		$sql .= "USING (id_distrito)) ";
		$sql .= "LEFT JOIN moneda ";
		$sql .= "USING (id_moneda)) ";
		$sql .= "LEFT JOIN estado ";
		$sql .= "USING (id_estado) ";
		$sql .= "LEFT JOIN tipo_entidad ";
		$sql .= "USING (id_tipo_entidad) ";
		$sql .= "WHERE licitacion.id_licitacion=$ID and es_presupuesto=0";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		echo "<form action='licitaciones_view.php' method=post>\n";
		echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=ma><font size=3><b>Detalles de la Licitación</b></td></tr>";
		if ($result->RecordCount() == 1) {
			$ma = substr($result->fields["fecha_apertura"],5,2);
			$da = substr($result->fields["fecha_apertura"],8,2);
			$ya = substr($result->fields["fecha_apertura"],0,4);
			$ha = substr($result->fields["fecha_apertura"],11,5);
			echo "<tr>\n";
			echo "<td  width=50% align=left valign=top><A style='color:black' href='".encode_link('licitaciones_view.php',array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]))."'> <b>ID:</b> ".$result->fields["id_licitacion"]."</a></td>\n";
			echo "<td  width=50% align=right><b>Apertura:</b> $da/$ma/$ya<br><b>Hora:</b> $ha</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left colspan=2><b>Distrito:</b> ".$result->fields["nombre_distrito"]."\n";
			echo "<br><b>Entidad:</b> ".html_out($result->fields["nombre_entidad"]);
			echo "<br><b>Direccion:</b> ".$result->fields["direccion"];
			echo "<br><b>Entidad:</b> ".$result->fields[tipo_entidad]."</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left><b>Mantenimiento de oferta:</b> ".$result->fields["mant_oferta_especial"]."\n";
			echo "<br><b>Forma de pago:</b> ".$result->fields["forma_de_pago"]."\n";
			echo "<br><b>Fecha de entrega</b>: \n";
			if ($result->fields["fecha_entrega"] != "") {
				$me = substr($result->fields["fecha_entrega"],5,2);
				$de = substr($result->fields["fecha_entrega"],8,2);
				$ye = substr($result->fields["fecha_entrega"],0,4);
				echo "$de/$me/$ye\n";
			}
			else { echo "N/A\n"; }
			echo "</td>\n";
			echo "<td align=right valign=top><b>Número:</b> ".html_out($result->fields["nro_lic_codificado"])."\n";
			echo "<br><b>Valor del pliego:</b> \$".formato_money($result->fields["valor_pliego"])."</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left><b>Moneda:</b> ".$result->fields["nombre_moneda"]."</b>\n";
			echo "<br><b>Ofertado:</b> ".formato_money($result->fields["monto_ofertado"])."\n";
			echo "<br><b>Estimado:</b> ".formato_money($result->fields["monto_estimado"])."\n";
			echo "<br><b>Ganado:</b> ".formato_money($result->fields["monto_ganado"])."</td>\n";
//			echo "</tr><tr>\n";
			echo "<td align=right valign=top><b>Estado:</b>\n";
			echo "<span style='background-color: ".$result->fields["color_estado"]."; border: 1px solid #000000; font-family:Verdana; font-size:10px; text-decoration: none;'>&nbsp;&nbsp;&nbsp;</span> ".$result->fields["nombre_estado"]."</td>";
//			echo "<img src='$html_root/imagenes/".$estados[$result->fields["id_estado"]][imagen]."' height=8 width=8 alt='".$estados[$result->fields["id_estado"]][texto]."'>\n";
//			echo $estados[$result->fields["id_estado"]][texto]."</td>\n";
			echo "</tr><tr>\n";
			echo "<td align=left colspan=2><b>Comentarios/Seguimiento:</b><br>".html_out($result->fields["observaciones"])."</td>\n";
			echo "</tr>\n";
/*			echo "<tr>\n";
			echo "<td align=left colspan=2>\n";
			echo "<b>Archivos:</b><br>\n";
			echo "<table cellpadding=3 cellspacing=3 width=100%>\n";
			echo "<tr><td colspan=5 align=left></td></tr>\n";
			$result1 = $db->Execute("select * from archivos where id_licitacion=$ID") or die($db->ErrorMsg());
			if ($result1->RecordCount() > 0) {
				echo "<tr bgcolor=$bgcolor3>\n";
				echo "<td align=center><b>Eliminar</b></td>\n";
				echo "<td align=left><b>Nombre</b></td>\n";
				echo "<td align=center><b>Fecha de cargado</b></td>\n";
				echo "<td align=left><b>Cargado por</b></td>\n";
				echo "</tr>\n";
				while (!$result1->EOF) {
					$mc = substr($result1->fields["subidofecha"],5,2);
					$dc = substr($result1->fields["subidofecha"],8,2);
					$yc = substr($result1->fields["subidofecha"],0,4);
					$hc = substr($result1->fields["subidofecha"],11,5);
					$imprimir = $result1->fields["imprimir"];
					if ($imprimir == "t") $color_imprimir = "#00cc00";
					else $color_imprimir = "#cc2222";
					echo "<tr bgcolor=$bgcolor3>\n";
					echo "<td width=10% align=center bgcolor='$color_imprimir'>\n";
					echo "<input type=checkbox name=file_id[] value='".$result1->fields["idarchivo"]."'>\n";
					echo "</td>\n";
					echo "<td width=45% align=left>\n";
					echo "<a title='Archivo: ".$result1->fields["nombrecomp"]."\nTamaño: ".number_format($result1->fields["tamañocomp"]/1024)." Kb' href='".encode_link("licitaciones_view.php",array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download","Comp"=>1))."'>\n";
					echo "<img align=middle src=$html_root/imagenes/zip.gif border=0>\n";
					echo "</a>&nbsp;&nbsp;";
					echo "<a title='Archivo: ".$result1->fields["nombre"]."\nTamaño: ".number_format($result1->fields["tamaño"]/1024)." Kb' href='".encode_link("licitaciones_view.php",array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download"))."'>".$result1->fields["nombre"]."</a>\n";
					echo "</td>\n";
					echo "<td width=20% align=center>$dc/$mc/$yc $hc hs.</td>\n";
					echo "<td width=25% align=left>".$result1->fields["subidousuario"]."</td>\n";
					echo "</tr>\n";
					$result1->MoveNext();
				}
			}
			else {
				echo "<tr><td colspan=5 align=center><b>No hay archivos disponibles para esta licitación</b></td></tr>\n";
			}
			echo "</table>\n";
			echo "</td></tr>\n";
			if ($result->fields["ultimo_usuario"]) {
				$mm = substr($result->fields["ultimo_usuario_fecha"],5,2);
				$dm = substr($result->fields["ultimo_usuario_fecha"],8,2);
				$ym = substr($result->fields["ultimo_usuario_fecha"],0,4);
				$hm = substr($result->fields["ultimo_usuario_fecha"],11,5);
				echo "<tr>\n";
				echo "<td colspan=2><b>Ultima modificación hecha por ".$result->fields["ultimo_usuario"]." el $dm/$mm/$ym a las $hm</b></td>\n";
				echo "</tr>\n";
			}
			echo "<tr>\n";
			echo "<td style=\"border:$bgcolor2\" colspan=2 align=center><br>\n";
			echo "<input type=hidden name=ID value='$ID'>\n";
			echo "<input type=submit name=det_oferta style='width:160;' value='Realizar Oferta' onClick=\"document.location='".encode_link("realizar_oferta.php",array("ID"=>$ID))."'; return false;\">&nbsp;&nbsp;&nbsp;";
			echo "<input type=submit name=det_edit style='width:160;' value='     Modificar     '>&nbsp;&nbsp;&nbsp;";
			echo "<input type=submit name=det_volver style='width:160;' value='      Volver      ' onClick=\"document.location='licitaciones_view.php'; return false;\"><br><br>";
			echo "<input type=submit name=det_addfile style='width:160;' value='Agregar archivo'>&nbsp;&nbsp;&nbsp;";
			echo "<input type=submit name=det_delfile style='width:160;' value='Eliminar Archivos' onClick=\"return confirm('ADVERTENCIA: Se van a eliminar los archivos seleccionados')\">&nbsp;&nbsp;&nbsp;";
			echo "<input type=submit name=det_del style='width:160;' value='Eliminar Licitación' onClick=\"return confirm('ADVERTENCIA: Se va a eliminar la Licitación número $ID');\">";
			echo "<br><br></td>";
			echo "</tr>\n";*/
		}
	}
	echo "</table><br>\n";
}
?>