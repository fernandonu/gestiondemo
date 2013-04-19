<?
/*
$Author: fernando $
$Revision: 1.5 $
$Date: 2005/09/06 20:23:54 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");
echo $html_header;
// Cuerpo de la pagina
cargar_calendario();
echo "<form action=bancos_movi_saldos.php method=post>\n";
if (!$_POST["Mov_Saldo_Banco"]) {
	$Banco=-1;  // Banco por defecto
	$Fecha_Saldo = date("d/m/Y",mktime());
	$Fecha_Saldo_db = date("Y-m-d",mktime());
}
else {
	$Banco=$_POST["Mov_Saldo_Banco"];
	$Fecha_Saldo = $_POST["Mov_Saldo_Fecha"];
	$Fecha_Saldo_db = Fecha_db($_POST["Mov_Saldo_Fecha"]);
}
if ($parametros["idbanco"]) $Banco=$parametros["idbanco"];
/*
//Total	Depositos
$sql = "SELECT sum(ImporteDep) AS total ";
$sql .= "FROM bancos.depósitos ";
$sql .= "INNER JOIN tipo_banco ";
$sql .= "ON bancos.depósitos.idbanco=tipo_banco.idbanco ";
$sql .= "WHERE FechaCrédito IS NOT NULL ";
$sql .= "AND FechaCrédito BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
if ($Banco!="todos")
    $sql .= "AND bancos.depósitos.IdBanco=$Banco";
else
    $sql .= "AND tipo_banco.activo=1";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total_Depositos1 = $res_tmp["total"];

//Total	Tarjetas
$sql = "SELECT sum(ImporteCrédTar) AS total ";
$sql .= "FROM bancos.tarjetas ";
$sql .= "INNER JOIN tipo_banco ";
$sql .= "ON bancos.tarjetas.idbanco=tipo_banco.idbanco ";
$sql .= "WHERE FechaCrédTar IS NOT NULL ";
$sql .= "AND FechaCrédTar BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
if ($Banco!="todos")
       $sql .= "AND bancos.tarjetas.IdBanco=$Banco";
   else
       $sql .= "AND tipo_banco.activo=1";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total_Tarjetas1 = $res_tmp["total"];

//Total	Cheques
$sql = "SELECT sum(ImporteCh) AS total ";
$sql .= "FROM bancos.cheques ";
$sql .= "INNER JOIN tipo_banco ";
$sql .= "ON bancos.cheques.idbanco=tipo_banco.idbanco ";
$sql .= "WHERE FechaDébCh IS NOT NULL ";
$sql .= "AND FechaDébCh BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
if ($Banco!="todos")
       $sql .= "AND bancos.cheques.IdBanco=$Banco";
   else
       $sql .= "AND tipo_banco.activo=1";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total_Cheques1 = $res_tmp["total"];

//Total	Debitos
$sql = "SELECT sum(ImporteDéb) AS total ";
$sql .= "FROM bancos.débitos ";
$sql .= "INNER JOIN tipo_banco ";
$sql .= "ON bancos.débitos.idbanco=tipo_banco.idbanco ";
$sql .= "WHERE FechaDébito IS NOT NULL ";
$sql .= "AND FechaDébito BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' ";
if ($Banco!="todos")
       $sql .= "AND bancos.débitos.IdBanco=$Banco";
   else
       $sql .= "AND tipo_banco.activo=1";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total_Debitos1 = $res_tmp["total"];

$Saldo=($Total_Depositos1 + $Total_Tarjetas1 - $Total_Cheques1 - $Total_Debitos1);
*/

//traemos todos los saldos, de deposito, tarjeta, cheques y debitos, agrupados por cada bancao activo
$query="select idbanco,total_deposito,total_tarjeta,total_cheque,total_debito,nombrebanco
from
(
SELECT sum(ImporteDep) AS total_deposito,idbanco
FROM bancos.depósitos INNER JOIN bancos.tipo_banco using(idbanco)
WHERE FechaCrédito IS NOT NULL AND FechaCrédito BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' and tipo_banco.activo=1
group by idbanco
) as dep
FULL OUTER JOIN
(
SELECT sum(ImporteCrédTar) AS total_tarjeta,idbanco
FROM bancos.tarjetas INNER JOIN bancos.tipo_banco using(idbanco) 
WHERE FechaCrédTar IS NOT NULL AND FechaCrédTar BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' and tipo_banco.activo=1
group by idbanco
) as tar using(idbanco)
FULL OUTER JOIN
(
SELECT sum(ImporteCh) AS total_cheque ,idbanco
FROM bancos.cheques INNER JOIN bancos.tipo_banco using(idbanco)
WHERE FechaDébCh IS NOT NULL AND FechaDébCh BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' and tipo_banco.activo=1
group by idbanco
)as cheq using(idbanco)
FULL OUTER JOIN
(
SELECT sum(ImporteDéb)AS total_debito,idbanco
FROM bancos.débitos INNER JOIN bancos.tipo_banco using (idbanco)
WHERE FechaDébito IS NOT NULL AND FechaDébito BETWEEN '1996-01-01' AND '$Fecha_Saldo_db' and tipo_banco.activo=1
group by idbanco
)as deb using(idbanco)
FULL OUTER JOIN
bancos.tipo_banco using(idbanco)
where tipo_banco.activo=1";
if($Banco!=-1)
 $query.="and tipo_banco.idbanco=$Banco";
 
$query.=" order by nombrebanco";
$saldos=sql($query,"<br>Error al traer los saldos de los bancos<br>") or fin_pagina();
//--------------->>>>> $Saldo=($Total_Depositos + $Total_Tarjetas - $Total_Cheques - $Total_Debitos);

?>
<script src="../../lib/NumberFormat150.js"></script>
<script>
function sumar()
{
 var cant_check=parseInt(document.all.cant_bancos.value);
 var i,suma_total=0,check,no_todos_chequeados=0;
 for(i=0;i<cant_check;i++)
 {
  check=eval("document.all.sumar_"+i);
  if(check.checked)	
   suma_total+=parseFloat(check.value);
  else
   no_todos_chequeados=1; 
 }
 if(no_todos_chequeados)
  document.all.sumar_todos.checked=0;
 else 
  document.all.sumar_todos.checked=1;
 document.all.acum_saldo_total.value=formato_money(suma_total);
}

function seleccionar_todos()
{
 var cant_check=parseInt(document.all.cant_bancos.value);
 var i,check,chequear=0;
 if(document.all.sumar_todos.checked)
  chequear=1;
 for(i=0;i<cant_check;i++)
 {
  check=eval("document.all.sumar_"+i);
  check.checked=chequear;
 }
 sumar();
}
</script>
<br>
<table width=600 align=center cellpadding=5 cellspacing=0>
 <tr>
  <td colspan=1 align=left><b>Banco</b>
   <?
   $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
   $result = $db->query($sql) or die($db->ErrorMsg());
   ?>
   <select name=Mov_Saldo_Banco OnChange="document.forms[0].submit();">
    <option value=-1 <?if ($Banco==-1) echo " selected"?>>Todos</option>
     <?
     while ($fila = $result->fetchrow()) 
     {?>
	  <option value="<?=$fila["idbanco"]?>" <?if ($fila['idbanco'] == $Banco)echo " selected"?>>
	   <?=$fila['nombrebanco']?>
	  </option>
      <?
     }
    ?> 
   </select>
  </td>
  <td align=right>
   <b>Fecha</b>
   <input type=text size=10 name=Mov_Saldo_Fecha value='<?=$Fecha_Saldo?>' title='Ingrese la fecha y\nhaga click en Actualizar'>
   <?=link_calendario("Mov_Saldo_Fecha");?>
  </td>
  <td colspan=3 align=center>
   <input type=submit name=Saldo_Actualizar value='Actualizar'>&nbsp;&nbsp;&nbsp;
   <!--
    <input type=button name=Volver value='   Volver   ' OnClick="javascript:window.location='bancos.php?PHPSESSID=<?//echo $PHPSESSID?>&mode=view';">
   --> 
  </td>
 </tr>
</table>
<?
//generamos la tabla con los saldos de todos los bancos activos
$cant_bancos_activos=$saldos->RecordCount();
?>
<table width="95%" align="center" cellpadding="4">
 <tr id="mo">
  <td align="left" colspan="2">
   <input type="hidden" name="cant_bancos" value="<?=$cant_bancos_activos?>">
   Cantidad de Bancos Activos: <?=$cant_bancos_activos?>
  </td>
 </tr> 
 <tr id=ma>
  <td width="80%">
   Banco
  </td>
  <td width="20%" align="right">
   Saldo al <?=$Fecha_Saldo?>
  </td>
 </tr>
<?
$contador=0;
$acum_deposito=$acum_tarjeta=$acum_cheque=$acum_debito=$acum_saldo_total=0;
while (!$saldos->EOF)
{
 $total_deposito=($saldos->fields["total_deposito"])?$saldos->fields["total_deposito"]:0;	
 $acum_deposito+=$total_deposito;
 $total_tarjeta=($saldos->fields["total_tarjeta"])?$saldos->fields["total_tarjeta"]:0;	
 $acum_tarjeta+=$total_tarjeta;
 $total_cheque=($saldos->fields["total_cheque"])?$saldos->fields["total_cheque"]:0;	
 $acum_cheque+=$total_cheque;
 $total_debito=($saldos->fields["total_debito"])?$saldos->fields["total_debito"]:0;	
 $acum_debito+=$total_debito;
 $saldo_total=$total_deposito+$total_tarjeta-$total_cheque-$total_debito;
 $acum_saldo_total+=$saldo_total;
 ?>
 <tr bgcolor="<?=$bgcolor_out?>">
  <td>
   <?=$saldos->fields["nombrebanco"]?>
  </td>
  <td align="right">
   <table width="100%">
    <tr>
     <td width="40%" align="left">
      <font size="2"><b>$</b></font>
     </td> 
     <td width="60%" align="right">
      <?
      if ($saldo_total < 0) 
   	   $color_saldo = "red";
      else
 	   $color_saldo = "green";
      ?>
      <font color="<?=$color_saldo?>" size="2">
       <b>
       <?
        echo formato_money($saldo_total);
       ?>
       </b>
      </font>
     </td>
     <td width="1%">
       <?
       //mostramos los checks solo si se estan visualizando todos los bancos
       if($Banco==-1)
       {?>
        <input type="checkbox" checked name="sumar_<?=$contador?>" value="<?=$saldo_total?>" onclick="sumar()">
       <?
       }
       else  
        echo "&nbsp;";
       ?> 
     </td>
    </tr>
   </table>  
  </td>
 </tr> 
 <?
 $contador++;
 $saldos->MoveNext();
}//de while(!$saldos->EOF)
 
 //mostramos los checks solo si se estan visualizando todos los bancos
 if($Banco==-1)
 { ?>
  <tr id=mo>
   <td align="right">
    <font size="2"><b>Totales</b></font>
   </td>
   <td align="right">
    <table width="100%">
     <tr>
      <td width="40%" align="left">
       <font size="2"><b>$</b></font>
      </td>
      <td width="60%" align="right">
       <input type="text" name="acum_saldo_total" size="15" value="<?=formato_money($acum_saldo_total)?>" class="text_4" style="font-size:15;text-align:right;">
      </td>
      <td>
       <input type="checkbox" checked name="sumar_todos" value="<?=$saldo_total?>" title="Seleccionar todos" onclick="seleccionar_todos()">
      </td>
     </tr>
    </table>   
   </td>
  <tr>
 <?
 }//de if($Banco==-1)
 ?> 
</table>

<?/*
echo "<table width=600 align=center class='bordes' bgcolor=$bgcolor_out border=1>";
echo "<tr bordercolor='#000000'><td id=mo colspan=3 align=center>Resumen a la fecha</td></tr>";
echo "<tr bordercolor='#000000' id=ma>";
echo "<td width=33% align=center>Ingresos</td>";
echo "<td width=33% align=center>Egresos</td>";
echo "<td width=33% align=center>Resumen</td>";
echo "</tr>\n";
echo "<tr bordercolor='#000000'>\n";
echo "<td align=center>";
echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
echo "<tr>";
echo "<td id=mo>Depósitos</td>";
echo "</tr>";
echo "<tr>";
echo "<td id=ma nowrap>$ ".formato_money($Total_Depositos1)."</td>";
echo "</tr>";
echo "</table><br>";
echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
echo "<tr>";
echo "<td id=mo>Tarjetas</td>";
echo "</tr>";
echo "<tr>";
echo "<td id=ma nowrap>$ ".formato_money($Total_Tarjetas1)."</td>";
echo "</tr>";
echo "</table>";
echo "</td>\n";
echo "<td align=center>";
echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
echo "<tr>";
echo "<td id=mo>Cheques</td>";
echo "</tr>";
echo "<tr>";
echo "<td id=ma nowrap>$ ".formato_money($Total_Cheques1)."</td>";
echo "</tr>";
echo "</table><br>";
echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
echo "<tr>";
echo "<td id=mo>Débitos</td>";
echo "</tr>";
echo "<tr>";
echo "<td id=ma nowrap>$ ".formato_money($Total_Debitos1)."</td>";
echo "</tr>";
echo "</table>";
echo "</td>\n";
echo "<td align=center>";
echo "<table border=1 cellspacing=5 cellpadding=5 width=75% bordercolor='$bgcolor1' bgcolor='$bgcolor3'>";
echo "<tr>";
echo "<td id=mo><font size=3>Saldo</font></td>";
echo "</tr>";
echo "<tr>";
echo "<td id=ma nowrap><b><font size=4 color='$color_saldo'>$ ".formato_money($Saldo)."</font></b></td>";
echo "</tr>";
echo "</table>";
echo "</td>\n";
echo "</tr>\n";
echo "</table></form>\n";*/
?>