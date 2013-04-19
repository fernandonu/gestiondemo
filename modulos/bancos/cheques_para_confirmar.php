<?
/*
$Author: enrique

MODIFICADA POR
$Author: enrique $
$Revision: 1.3 $
$Date: 2005/08/31 19:34:58 $
*/

require_once("../../config.php");

$msg=$_POST['msg'] or $msg=$parametros['msg'];
$numero=$_POST['numero'] or $numero=$parametros['numero'];
echo $html_header;
if($_POST['notificado'])
{	
$cant=$_POST['cantidad'];
$t=0;
$i=1;			
 while ($cant>=$i)
 {
 if ($_POST['borrar_'.$i])
 {
 $fecha=fecha_db(date("d/m/Y",mktime()));
  $tipo_lo="aceptado";	
  $t=1;
  $usuario=$_ses_user['name'];
  $nu=$_POST['borrar_'.$i];
  $numero_ch=$nu;
  $consulta="select * from log_cheques_debitados where númeroch=$numero_ch";
  $cons_log=sql($consulta,"no se pudo insertar en log_cheques_debitados") or fin_pagina();
  $id_banco1=$cons_log->fields['idbanco'];
  $usuario1=$cons_log->fields['usuario'];
  if($usuario1==$usuario)
  {
  $comentarios="Hay cheques que usted debito y necesita que otra persona los confirme";	
  }
  else 
  {
  $campos="(fecha,usuario,tipo_log,idbanco,númeroch)";	
  $sql3="INSERT INTO log_cheques_debitados $campos VALUES ".
  "('$fecha','$usuario','$tipo_lo',$id_banco1,$numero_ch)";
  $result1=sql($sql3,"no se pudo insertar en log_cheques_debitados") or fin_pagina();
  $numero=$numero_ch;
  $comentario="Los datos se modificaron correctamente";
  }
 
 }
 $i++;
 }
 /*if(t==0)
  {?>
  <script>
  alert('No hay cheques seleccionados');
  </script>
  <?
  }*/
  
  //$db->StartTrans();
}

variables_form_busqueda("cheques_para_confirmar");
//print_r($_ses_listado_envios);
//print_r($_POST);


if (!$cmd) {
	$cmd="pendiente";
	$_ses_cheques_para_confirmar["cmd"]=$cmd;
	phpss_svars_set("_ses_cheques_para_confirmar", $_ses_cheques_para_confirmar);
	
}
$datos_barra = array(
					array(
						"descripcion"	=> "Pendiente",
						"cmd"		=> "pendiente",
						 ),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						)
	            );


generar_barra_nav($datos_barra);
?>
<form name="form1" method="post" action="cheques_para_confirmar.php">
<?


echo "<table align=center cellpadding=5 cellspacing=0 >";
echo "<tr><td>";

$itemspp=50;

// Fin variables necesarias
if ($up=="") $up = "1";   // 1 ASC 0 DESC


$seleccion="";
$ignorar = "";
$orden = Array (
"default" => "3",
        "1" => "númeroch",
        "2" => "nombrebanco",
        "3" => "fecha",        
        "4" => "usuario",
      );
$filtro = Array (
   "númeroch" => "Nro de cheque",
   "bancos.tipo_banco.nombrebanco" => "Nombre Banco",
   "fecha" => "Fecha Debito",
   "usuario" => "Usuario",
);
/*$seleccion = Array ( "importe" => "idbancos in (select idbancos
                                        from bancos.cheques join 
                                        bancos.log_cheques_debitados using (idbancos)
                                        where nro_serie ILIKE '%$keyword%')"
);

$ignorar = Array ( 0 => "importe");*/

//entidad_mod, dir_entrega_mod, contacto_mod, nro_lic_mod, 
if($cmd=='historial')
{
$sql_tmp="select  usuario,bancos.log_cheques_debitados.númeroch,fecha,nombrebanco,idbanco,importech,comentarios
 from   bancos.cheques join bancos.log_cheques_debitados using (númeroch,idbanco) left join bancos.tipo_banco using (idbanco)
";
$where_tmp="tipo_log='aceptado'";
}
else 
{
$sql_tmp="select  usuario,bancos.log_cheques_debitados.númeroch,fecha,nombrebanco,idbanco,importech,comentarios
 from   bancos.cheques join bancos.log_cheques_debitados using (númeroch,idbanco) left join bancos.tipo_banco using (idbanco)
 ";
$where_tmp="númeroch not in(select  númeroch
 from bancos.log_cheques_debitados where tipo_log='aceptado') ";
	
}	
  

if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
     $contar="buscar";

list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar","",$ignorar,$seleccion);

$res_query = sql($sql) or fin_pagina();
//print_r($res_query);
echo "<input type=submit name='buscar' value='Buscar'>";
echo "</td>";
echo "</tr>\n";
echo "</table>\n";
echo "<center><b><font size=2>".$msg."</font></b></center>";
?>

<table border=0 width=95% cellspacing=2 cellpadding=3 align="center">
  <tr>
  <?if($cmd=='pendiente')
  {?>
  <td colspan=7 align=left id=ma> <? echo "\n";?>
   <?}
   else
   {?>
   <td colspan=6 align=left id=ma> <? echo "\n";?>
   <?}?>
	<table width=100%>
	 <tr id=ma><? echo "\n";?>
	  <td width=60% align=left><b><? echo "Total:</b> $total Envios</td>\n";?>
      <td width=40% align=right><? echo $link_pagina ?></td> <? echo"\n";?>
	 </tr>
	</table> <? echo "\n";?>
  </td>
  </tr>
 
  <?if($cmd=='pendiente')
       {?>
       <tr>
       <td colspan=7 align=center><strong>
       <?=$comentarios?> 
       </strong></td> 
       </tr>
       <tr>
      <td align="center" id=mo><a id=mo> <b>Campos</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>1,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Nro. Cheque</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>2,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Banco</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Fecha</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Usuario</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Importe</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Comentario</b></a></td>
       <?
       }
       else 
       { 
       ?>
      
       <tr>
       <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>1,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Nro. Cheque</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>2,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Banco</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Fecha</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Usuario</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Importe</b></a></td>
      <td align="center" id=mo><a id=mo href='<? echo encode_link("cheques_para_confirmar.php",Array('sort'=>3,'up'=>$up2,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'><b>Comentario</b></a></td>
      <?
       }
       ?>
      </tr>

 <?
 $i=1;
 while (!$res_query->EOF) {
 	   $numero=$res_query->fields['númeroch'];
       $ref=encode_link("cheques_confirmados.php", array("numero_ch"=>$numero, "id_banco"=>$res_query->fields['idbanco']));
       if($cmd=='pendiente')
       {
      ?>
       <tr <?=atrib_tr()?>>
     
      
       <td align="center"><input name='borrar_<? echo $i; ?>' type='checkbox' value='<?=$numero?>'></td>
       <a href="<?=$ref?>">
       <td align="center" ><?=$numero?></td>
       <td align="center" ><?=$res_query->fields['nombrebanco']?></td>
       <td align="center" ><?=Fecha($res_query->fields['fecha'])?></td>
       <td align="center" ><?=$res_query->fields['usuario'] ?></td>
       <td align="center" >$<?=$res_query->fields['importech']?></td>
       <td align="center" ><?=$res_query->fields['comentarios'] ?></td>
       </a>
       </tr>
       <?
       }
       else 
       {?>
       <tr <?=atrib_tr()?>>
       <a href="<?=$ref?>">
       <td align="center" ><?=$numero?></td>
       <td align="center" ><?=$res_query->fields['nombrebanco']?></td>
       <td align="center" ><?=Fecha($res_query->fields['fecha'])?></td>
       <td align="center" ><?=$res_query->fields['usuario'] ?></td>
       <td align="center" >$<?=$res_query->fields['importech']?></td>
       <td align="center" ><?=$res_query->fields['comentarios']?></td>
       </tr>
      <?}		 			
   $res_query->MoveNext();
   $i++;
   
   } 
   ?>
   <input type='hidden' name='cantidad' value='<?=$i?>'>
   </table>
   <table align="center">
   <tr>
   <td>
   <?
   if($cmd=='pendiente')
   {
        $usuario=$_ses_user['name'];
       /* if($usuario=="Enrique Sanchez")
       {?>
       <input align="center" type="submit" name=notificado value='Notificado'>  
       <?}*/
       if($usuario=="Alberto Corapi")
       {?>
       <input align="center" type="submit" name=notificado value='Notificado'>  
       <?}
       if($usuario=="Juan Manuel Baretto")
       {?> 
       <input align="center" type="submit" name=notificado value='Notificado'>  
       <?} 
        if($usuario=="Noelia Lucero")
       {?> 
       <input align="center" type="submit" name=notificado value='Notificado'>    
       <?}
   }     
	   ?>
  </td>
  </tr>       
  </table>
<br>
</form>
</html>
<?echo fin_pagina();?>