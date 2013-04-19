<?
/*
AUTOR: nazabal
MODIFICADO POR:
$Author: nazabal $
$Revision: 1.10 $
$Date: 2006/02/20 19:59:03 $
*/

require_once("funciones_generales.php");

function total_cheques($fecha) {
	global $db;
	$sql = "
	SELECT idbanco,nombrebanco,chpendientes,
	       (total_deposito+total_tarjeta-total_cheque-total_debito) AS saldo
	FROM (
	     SELECT
	           bancos.tipo_banco.idbanco,
	           bancos.tipo_banco.nombrebanco,
	           SUM(bancos.cheques.importech) AS chpendientes
	     FROM
	          bancos.cheques
	          LEFT OUTER JOIN bancos.tipo_banco ON (bancos.cheques.idbanco=bancos.tipo_banco.idbanco)
	     WHERE
	          (bancos.tipo_banco.activo = 1)
	          AND (bancos.cheques.\"fechadébch\" IS NULL)
	          AND bancos.cheques.fechavtoch <= '$fecha'
	     GROUP BY tipo_banco.nombrebanco, tipo_banco.idbanco
	) AS pendientes
	LEFT JOIN
	(
	     SELECT idbanco,
	            COALESCE(total_deposito,0) as total_deposito,
	            COALESCE(total_tarjeta,0) as total_tarjeta,
	            COALESCE(total_cheque,0) as total_cheque,
	            COALESCE(total_debito,0) as total_debito
	     FROM (
	          SELECT SUM(ImporteDep) AS total_deposito,idbanco
	          FROM bancos.depósitos INNER JOIN bancos.tipo_banco USING(idbanco)
	          WHERE FechaCrédito IS NOT NULL AND FechaCrédito <= '$fecha' and tipo_banco.activo=1
	          GROUP BY idbanco
	          ) AS dep
	     FULL OUTER JOIN (
	          SELECT SUM(ImporteCrédTar) AS total_tarjeta,idbanco
	          FROM bancos.tarjetas INNER JOIN bancos.tipo_banco USING(idbanco)
	          WHERE FechaCrédTar IS NOT NULL AND FechaCrédTar <= '$fecha' and tipo_banco.activo=1
	          GROUP BY idbanco
	          ) AS tar USING(idbanco)
	     FULL OUTER JOIN (
	          SELECT SUM(ImporteCh) AS total_cheque ,idbanco
	          FROM bancos.cheques INNER JOIN bancos.tipo_banco USING(idbanco)
	          WHERE FechaDébCh IS NOT NULL AND FechaDébCh <= '$fecha' and tipo_banco.activo=1
	          GROUP BY idbanco
	          ) AS cheq USING(idbanco)
	     FULL OUTER JOIN (
	          SELECT SUM(ImporteDéb) AS total_debito,idbanco
	          FROM bancos.débitos INNER JOIN bancos.tipo_banco USING(idbanco)
	          WHERE FechaDébito IS NOT NULL AND FechaDébito <= '$fecha' and tipo_banco.activo=1
	          GROUP BY idbanco
	          ) AS deb USING(idbanco)
	     FULL OUTER JOIN
	          bancos.tipo_banco USING(idbanco)
	     WHERE tipo_banco.activo=1
	) AS saldos USING(idbanco)
	WHERE
	     (total_deposito+total_tarjeta-total_cheque-total_debito) < chpendientes
	";
	
	$res = $db->Execute($sql) or die($db->ErrorMsg());
	return $res;
}

function total_cheques_banco($id_banco, $fecha) {
	global $db;
	$sql = "
		SELECT
	         SUM(bancos.cheques.importech) AS total_pendiente
	    FROM
	         bancos.cheques
	    WHERE
	         bancos.cheques.idbanco = $id_banco
	         AND (bancos.cheques.\"fechadébch\" IS NULL)
	         AND bancos.cheques.fechavtoch <= '$fecha'
    ";
	$res = $db->Execute($sql) or die($db->ErrorMsg());
	return $res;
}

$limite_descubierto = array(
	"3" 	=> "297000",
	"21"	=> "500000"
);
$dias = 7;
$fecha = date("Y-m-d",mktime(0,0,0,date("m"),date("d") + $dias, date("Y")));
$out = "";
$mail_para = "noelia@coradir.com.ar, juanmanuel@coradir.com.ar";
//$mail_para = "nazabal@coradir.com.ar";
$mail_asunto = "ATENCION!!! NO HAY SALDO PARA CUBRIR LOS CHEQUES PENDIENTES";

$result = total_cheques($fecha);

if ($result->recordCount() > 0) {
	$header = false;
	$i = 1;
	while (list($id_banco, $nombre_banco, $total_cheques, $saldo) = $result->fetchRow()) {
		if (($limite_descubierto[$id_banco] != "") and ($limite_descubierto[$id_banco] + $saldo > 0)) {
			continue;
		}
		if (!$header) {
			$out .= "<html><body><br>\n";
			$out .= "En los siguientes bancos el saldo no alcanza para cubrir los cheques al ".Fecha($fecha).":<br>\n";
			$header = true;
		}
		$out .= ($i++).") $nombre_banco - Saldo Actual: \$ ".formato_money($saldo)."<br>\n";
		$out .= "Cheques Pendientes:<br>\n";
		for ($j = 0; $j <= $dias; $j++) {
			$fecha_tmp = date("Y-m-d",mktime(0,0,0,date("m"),date("d") + $j, date("Y")));
			$result_pendientes = total_cheques_banco($id_banco, $fecha_tmp);
			if ($result_pendientes->fields['total_pendiente'] != "") {
				if ($result_pendientes->fields['total_pendiente'] > $saldo) {
					$color = "#FF0000";
				}
				else {
					$color = "#000000";
				}
				$out .= Fecha($fecha_tmp).": <font color='$color'>\$ ".formato_money($result_pendientes->fields['total_pendiente'])."</font><br>\n";
			}
		}
	}
	if ($out != "") {
		enviar_mail_html($mail_para,$mail_asunto, $out, "", "");
	}
}
?>