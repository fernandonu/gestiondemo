<?
/*
Autor: GACZ
Creado: lunes 20/12/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.4 $
$Date: 2005/01/05 18:19:47 $
*/

require_once("../../config.php");
//print_r($_POST);
	//por bterminar viene desde op_problemas.php
	//por bguardar viene desde op_problemas_status.php
	if ($_POST['bterminar'] || $_POST['bguardar'])
	{
		$apostergar=$_POST['select_postergar'];
		$adescartar=$_POST['chk_descartar'];
		$aid_entrega=$_POST['id_entrega'];
		$aid_tipo_prob=$_POST['id_tipo_prob'];
		$aid_prob=$_POST['id_prob'];
		$acambio=$_POST['hcambio'];//flags que indican que cambio el valor del select
		$total=count($aid_tipo_prob);
		$q="";
		for($i=0; $i < $total; $i++)
		{
			$timestamp=date("Y-m-j H:i:s");
			if ($adescartar[$i])
			{
				$apostergar[$i]='null';
				$dias=$horas=$minutos='null';
			}
			else
			{
				$atimestamp=getdate(strtotime(date("Y-m-j H:i:00"))); 
				list($dias,$horas,$minutos)=split(",",$apostergar[$i]);
				$apostergar[$i]="'". date("Y-m-j H:i:00",mktime($atimestamp['hours']+$horas,$atimestamp['minutes']+$minutos,0,$atimestamp['mon'],$atimestamp['mday']+$dias,$atimestamp['year']))."'";
				$adescartar[$i]='null';
			}
			if ($aid_prob[$i])
			{
				if ($acambio[$i])
				{
				$q.="update problemas_op ";
				$q.="set descartar={$adescartar[$i]},postergar_hasta={$apostergar[$i]},last_review='$timestamp',dias=$dias,horas=$horas,minutos=$minutos ";
				$q.="where id_prob={$aid_prob[$i]}; ";
				}
			}
			else
			{
				$q.="insert into problemas_op (id_usuario,id_entrega_estimada,descartar,last_review,postergar_hasta,id_tipo_prob,dias,horas,minutos) ";
				$q.="values ({$_ses_user['id']},{$aid_entrega[$i]},{$adescartar[$i]},'$timestamp',{$apostergar[$i]},{$aid_tipo_prob[$i]},$dias,$horas,$minutos);";
			}
			
		}
		if ($q)
			sql($q) or fin_pagina();
	}
?>
