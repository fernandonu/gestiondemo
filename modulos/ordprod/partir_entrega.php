<?
/*
Creado por: Quique

Modificada por
$Author: enrique $
$Revision: 
$Date: 2006/02/06 16:32:03 $
*/


require_once("../../config.php");
$num=$parametros['num'] or $num=$_POST["num"];
$cant=$parametros['cant'] or $cant=$_POST["cant"] or $cant=$_GET["cant"];
$div=$parametros['div'] or $div=$_POST["div"];
$row=$parametros['row'] or $row=$_POST["row"];
$id_oc=$parametros['id_oc'] or $id_oc=$_POST["id_oc"];
$titulo=$parametros['titulo'] or $titulo=$_POST["titulo"];
$agre=$parametros['agre'] or $agre=$_POST["agre"];
echo $html_header;
//print_r($parametros);
?>

<form name="form1" action="partir_entrega.php" method="POST">
 <input type="hidden" name="cant" value="<?=$cant?>">
 <input type="hidden" name="num" value="<?=$num?>">
 <input type="hidden" name="row" value="<?=$row?>">
 <input type="hidden" name="titulo" value="<?=$titulo?>">
<table align="center" bgcolor='<?=$bgcolor3?>' width="80%">
<tr align="center">
 <td colspan="2" width="100%"><b>Renglon:<?=$num;?> Cantidad:<?=$cant;?> &nbsp;&nbsp; 
 Partir en:</b> <select name="div" size="1" onchange="document.form1.submit()">
 <?
 $p=1;
 while($cant>=$p)
 {?>
 <option value="<?=$p?>"><?=$p?></option> 	
 <?
 $p++;
 }	
 ?>
 </select>
 </td>
</tr> 
<tr align="center">
<td colspan="2">
<table>
<tr id="mo">
 <td width="10%"><b>Cant</b></td>
 <td width="70%"><b>Descripcion</b></td>
</tr> 
<?
$i=1;
while($div>=$i)
{
?>
<tr <?=$atrib_tr?>>
 <td><input type="text" size="3" name="conta_<?=$i?>"></td>
 <td><input type="text" size="70" name="desc_<?=$i?>" value="<?=$titulo?>"></td>
</tr>
<?
$i++;
}
?>
<input type="hidden" name="contador" value="<?=$i?>">
<input type="hidden" name="id_oc" value="<?=$id_oc?>">
<input type="hidden" name="agre" value="<?=$agre?>">
</table>
</td>
</tr>
<tr align="center">
<td colspan="2">
<?
/*$row1=$row;
$t=1;
while($t<$contador)
{
$buffer="<select name='entrega_$row1'>";*/
 $dividir=$row+$div;
 $j=1;
 $buffer="<option value=$j>$j</option>";
 $j++;
 while($j<=$agre)
 {
 $buffer.="<option value=$j>$j</option>";
 $j++;
 }
 /*$buffer.="</select>";
 $buf[$row1]=$buffer;
$t++;
$row1++;/
}*/
?>
<input type="button" name='aceptar' value='Aceptar' onclick="var text=new String('<?=$num?>');if(partir(<?=$div?>,text,<?=$cant?>,<?=$row?>,<?=$id_oc?>)){window.close();window.opener.partir1(<?=$div?>,<?=$row?>);}">
<input type='button' name='cerrar' value='Cerrar' onclick="window.close()"></td>
</tr>

</table>

</form>
<script>

function partir(div,num,cant,row,idoc)
{
 var t=1;
 var con=1;
 var suma=0;
 while(con<=div)
 {
   var ren =eval("document.all.conta_"+con);
       ren =ren.value;
   suma=parseInt(suma) + parseInt(ren);
   con++;	
 }
 
 if(suma==cant)
 {
  var tabla=window.opener.tabla;
  tabla.deleteRow(row);
  var total=tabla.rows.length;
      total=parseInt(total)+1;
      <?$row1=$row;?>
 //var contador=window.opener.contador;
 //alert(document.tabla.contador.value);
  while(t<=div)
  {
   var ren =tabla.rows.length;
   ren1 =eval("document.all.conta_"+t);
   ren1 =ren1.value;
   <?
   $link = encode_link("partir_entrega.php",array("num"=>$num,"cant"=>$cant,"div"=>0,"row"=>$dividir,"id_oc"=>$id_oc,"titulo"=>$titulo,"agre"=>$agregado));	
   $onclick_esp="window.open(\\\"$link\\\",\\\"\\\",\\\"top=50, left=170, width=800, height=600, scrollbars=1, status=1,directories=0\\\")";
   
   ?>
   var fila=tabla.insertRow(ren);
   fila.insertCell(0).innerHTML="<input type=button name=especial_"+total+" value='Esp' onclick='<?=$onclick_esp?>'>";
   fila.insertCell(1).innerHTML="<input type='text' size='20' name=num_"+total+" value='"+num+"' readonly>";
   fila.insertCell(2).innerHTML="<input type='text' size='5' name=cant_"+total+" value="+ren1+" readonly>";
   fila.insertCell(3).innerHTML="<input type='text' size='75' name=titulo_"+total+" value='<?=$titulo?>'>";
   //alert(fila.cells[0].innerHTML);
   //fila.insertCell(4).innerHTML="<input type='text' size='10' name=entrega"+total+">";
   fila.insertCell(4).innerHTML="<select name=entrega_"+total+"> <?=$buffer?></select>"; 
   /*"+var j=1;
   while(j<=agre)
   {+"
   <option value="+j+">"+j+"</option>
   "+j=j+1;}+"";*/
   fila.insertCell(5).innerHTML="<input type='hidden' size='3' name=idoc_"+total+" value="+idoc+">";
   //fila.insertCell(6).innerHTML="<input type='hidden' size='3' name=cant1_"+total+" value="+ren1+">";
   total++;
   row++; 		   
   t++;
   <?$row1++;?>			
  }
 return true;
 }
 else
 {
  alert("La cantidad total de productos para el renglón es incorrecta");	
  return false;
 }
  
 	
}
</script>