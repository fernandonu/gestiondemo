<?

require_once("../../config.php");
echo $html_header;

		$numero=$parametros['numero_ch'] or $_POST['numero_ch'];
		$idbanco=$parametros['id_banco'] or $_POST['id_banco'];
		if($_POST[notificado])
		{
		 //$db->StartTrans();	
		 $fecha=fecha_db(date("d/m/Y",mktime()));
		 $tipo_lo="aceptado";	
		 $usuario=$_ses_user['name'];
		 $id_banco1=$_POST['idban'];
		 $numero_ch=$_POST['Modificacion_Cheque_Numero_Old'];
		 $campos="(fecha,usuario,tipo_log,idbanco,númeroch)";	
		 $sql3="INSERT INTO log_cheques_debitados $campos VALUES ".
		 "('$fecha','$usuario','$tipo_lo',$id_banco1,$numero_ch)";
		 $result1=sql($sql3,"no se pudo insertar en log_cheques_debitados") or fin_pagina();
		 $numero=$numero_ch;
		 $comentario="Los datos se modificaron correctamente";
		 //$db->CompleteTrans();
		}
		
        $sql = "SELECT ";
        $sql .= "bancos.cheques.IdBanco,";
        $sql .= "bancos.cheques.idprov,";
        $sql .= "bancos.cheques.FechaEmiCh,";
        $sql .= "bancos.cheques.FechaVtoCh,";
        $sql .= "bancos.cheques.FechaDébCh,";
        $sql .= "bancos.cheques.ImporteCh,";
        $sql .= "bancos.cheques.Comentarios, ";
        $sql .= "bancos.cheques.numero_cuenta ";
        $sql .= "FROM bancos.cheques ";
        $sql .= "WHERE bancos.cheques.NúmeroCh=$numero";
        $result = sql($sql,"no se pudo recuperar los datos sobre el cheque") or fin_pagina();
        list(
            $mod_idbanco,
            $mod_proveedor,
            $mod_fecha_e,
            $mod_fecha_v,
            $mod_fecha_d,
            $mod_importe,
            $mod_comentarios,
            $mod_numero_cuenta) = $result->fetchrow();
            $mod_fecha_d = Fecha($mod_fecha_d);
            $mod_fecha_v = Fecha($mod_fecha_v);
            $mod_fecha_e = Fecha($mod_fecha_e);
            $mod_importe = formato_money($mod_importe);
        echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
        
        /***********************************************
		   Traemos y mostramos el Log de la OC
		************************************************/
		//left join por si alguna vez se elimina el usuario
		$q="select * from log_cheques_debitados where númeroch=$numero";
		$log=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
		?>
		
		<div align="right">
			<input name="mostrar_ocultar_log" type="checkbox" value="1" onclick="if(!this.checked)
																			  document.all.tabla_logs.style.display='none'
																			 else 
																			  document.all.tabla_logs.style.display='block'
																			  "> Mostrar Logs
		</div>	
		
		<div style="display:'none';overflow:auto;<? if ($log->RowCount() > 0) echo 'height:60;' ?> " id="tabla_logs" >
		<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
		<?
		while (!$log->EOF)
		{
			
		 if ($log->fields['tipo_log']=="de notificacion")
		 {
		 ?>
		<tr>
		      <td height="20" nowrap>Fecha de débito <?=fecha($log->fields['fecha'])." ".hora($log->fields['fecha'])?> </td>
		      <td nowrap > Usuario : <?=$log->fields['usuario']?> </td>
		</tr>
		<?}
		 //controlo si la orden ha sido anulada
		 if ($log->fields['tipo_log']=="aceptado")
		  {?>
		  <tr>
		      <td height="20" nowrap>Fecha de aceptación <?=fecha($log->fields['fecha'])." ".hora($log->fields['fecha'])?> </td>
		      <td nowrap > Usuario : <?=$log->fields['usuario']?> </td>
		 </tr>	
		  <?}
		 $log->MoveNext();
		}
		?>
		</table>
		 </div>
		<?
	     	
        
        
        echo "<form action=cheques_confirmados.php method=post>\n";
        echo "<table align=center cellpadding=5 cellspacing=0 class='bordes'>\n";//bordercolor='$bgcolor3'   
        echo "<tr ><td id=mo align=center>$comentario</td></tr>";    
        echo "<tr ><td id=mo align=center>Datos del Cheque</td></tr>";    
        echo "<tr ><td align=center>";
        echo "<table cellspacing=5 border=0 bgcolor='$bgcolor_out' >";//bordercolor='$bgcolor3'
        echo "<tr><td align=right><b>Banco</b></td>";
        echo "<td align=left>";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        echo "<select name=Modificacion_Cheque_IdBanco disabled>\n";
        while ($fila = $result->fetchrow()) {
        	echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $mod_idbanco)
                echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Proveedor</b></td>";
        echo "<td align=left>";
        echo "<select name=Modificacion_Cheque_IdProveedor disabled>\n";
        $sql = "SELECT id_proveedor, razon_social FROM general.proveedor ORDER BY razon_social";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value='".$fila[id_proveedor]."'";
            if ($fila[id_proveedor] == "$mod_proveedor") echo " selected";
            echo ">".$fila[razon_social]."</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Emision value='$mod_fecha_e' title='Ingrese la fecha de emisión del cheque' disabled>";
	
		echo link_calendario("Modificacion_Cheque_Fecha_Emision");
        echo "</td>\n";
        echo "</tr>\n";
        echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Vencimiento value='$mod_fecha_v'  title='Ingrese la fecha de vencimiento del cheque' disabled>";
	
		echo link_calendario("Modificacion_Cheque_Fecha_Vencimiento");
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Fecha Débito</b></td>";
        echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Debito value='$mod_fecha_d' title='Ingrese la fecha de débito del cheque' disabled>";
	
        echo link_calendario("Modificacion_Cheque_Fecha_Debito");
        echo "</td></tr>\n";
      
        echo "<tr><td align=right><b>Número</b>\n";
        echo "</td><td align=left>";
        echo "<input type=hidden name=Modificacion_Cheque_Numero_Old value='$numero'>";
        echo "<input type=hidden name=idban value='$idbanco'>";
        echo "<input type=hidden name=Modificacion_id_banco_Old value='$mod_idbanco'>";
        echo "<input type=text name=Modificacion_Cheque_Numero value='$numero' size=10 maxlength=50 disabled>";
        echo "</td></tr>\n";
        echo "<tr><td align=right><b>Importe</b>\n";
        echo "</td><td align=left>";
        echo "<input type=text name=Modificacion_Cheque_Importe value='$mod_importe' size=10 maxlength=50 disabled>&nbsp;";
        echo "</td></tr>\n";      
        echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
        echo "</td><td align=left>";
        echo "<textarea name=Modificacion_Cheque_Comentarios cols=30 rows=3 disabled>$mod_comentarios</textarea>";
        echo "</td></tr>\n";
        echo "<tr><td align=center colspan=2>\n";
        echo "<table border=0 width=100%>\n";
        echo "<tr><td colspan=2 align=center>\n";
        echo "<input type=hidden name=Modificacion_Cheque_Volver value='$cmd'>\n";
	   $sql1="select númeroch from compras.ordenes_pagos where númeroch=$numero";
       $result1 = sql($sql1) or fin_pagina();
       $nch=$result1->RecordCount();
       $sql2="select usuario from bancos.log_cheques_debitados where númeroch=$numero";
       $result2 = sql($sql2) or fin_pagina();
     
       $cant_usuarios=$result2->RecordCount();
       $i=0;
       $usuario=$_ses_user['name'];
       $corapi=0;
       $juan=0;	
       $noelia=0;
       //$quique=0;
       if($cant_usuarios<2)
       {
       while($cant_usuarios>$i)
       {     
       $nom_usuario=$result2->fields['usuario'];
       if($nom_usuario=="Alberto Corapi")
       {
       $corapi=1;
       }
       if($nom_usuario=="Juan Manuel Baretto")
       {
       $juan=1;
       }
       if($nom_usuario=="Noelia Lucero")
       {
       $noelia=1;
       } 
      /* if($nom_usuario=="Enrique Sanchez")
       {
       $quique=1;
       }  */         
       $i++;
       $result2->MoveNext();
       }
       /* if(($usuario=="Enrique Sanchez")&&($quique==0))
       {?>
       <input type="submit" name=notificado value='Notificado'>  
       <?}*/
       if(($usuario=="Alberto Corapi")&&($corapi==0))
       {?>
       <input type="submit" name=notificado value='Notificado'>  
       <?}
       if(($usuario=="Juan Manuel Baretto")&&($juan==0))
       {?> 
       <input type="submit" name=notificado value='Notificado'>  
       <?} 
        if(($usuario=="Noelia Lucero")&&($noelia==0))
       {?> 
       <input type="submit" name=notificado value='Notificado'>    
       <?}
	   } 
        echo "<input type=button name=Volver value='   Volver   ' OnClick=\"window.location='".encode_link("cheques_para_confirmar.php",array('idbanco'=>$mod_idbanco))."';\">\n";	 
	    if($nch==0)
	    echo "<tr ><td  align=center><strong>Cheque no asociado a una orden  de compra</strong></td></tr>";
        echo "</form>\n";
        echo "</td></tr>\n";
        echo "</table>";
        echo "</td></tr>\n";
        echo "</table>";
        echo "</td></tr>\n";
        echo "</table><br>\n";
 fin_pagina();