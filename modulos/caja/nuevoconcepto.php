<?
/*
Autor: GACZ
Creado: viernes 01/04/05

MODIFICADA POR
$Author:  $
$Revision: $
$Date: 2005/04/06 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");
echo $html_header;


?>
<script language='javascript' src='../../lib/fns.js'></script>
<script>

function agregar(nro_cuenta,nbre_concepto,plan) { 
var ctas=eval("window.opener.document.all.cuentas");
    ctas.length++;
	nbre=nbre_concepto + " [ " + nbre_plan + " ] ";
    ctas.options[ctas.length-1].text=nbre;
    ctas.options[ctas.length-1].value=nro_cuenta;
    ctas.options[ctas.length-1].selected=true;
}


function control_datos() {
	
if (document.all.concepto[0].checked == true && document.all.nuevo_concepto_1.options[document.all.nuevo_concepto_1.options.selectedIndex].value ==-1) {
 	 alert ('Seleccione un concepto');
     return false;	
}
else if (document.all.concepto[1].checked == true && document.all.nuevo_concepto_2.value=="" ) {
     alert ('Ingrese un concepto');
     return false;
}

if (document.all.nuevo_plan.value == "") {
     alert ('Ingrese el plan para la cuenta');
     return false;
} 

return true;
}

</script>



<?
if ($_POST['baceptar']=="Aceptar"){
	
	$selec=$_POST['concepto'];

	if ($selec==1)
	    $nbre_concepto=$_POST['nuevo_concepto_1'];
	else  $nbre_concepto=$_POST['nuevo_concepto_2']; 
	
    $plan=$_POST['nuevo_plan'];
	
    $sql="select * from general.tipo_cuenta where concepto ilike '$nbre_concepto' and plan ilike '$plan'";
    $res_sql=sql($sql,"verifica concepto y plan ") or fin_pagina(); 

    if ($res_sql->RecordCount() == 0) {
    $q="select max(numero_cuenta) as max_nro from general.tipo_cuenta";
    $res=sql($q, "Error al traer max numeo de cuenta") or fin_pagina();
    $numero_cuenta=$res->fields['max_nro'] +1;
    
    $cuentas="insert into tipo_cuenta (numero_cuenta,concepto,plan".(($_POST["t_nro_contador"])?", numero_contador":"").") 
              values ($numero_cuenta,'$nbre_concepto','$plan'".(($_POST["t_nro_contador"])?", '".$_POST["t_nro_contador"]."'":"").")";
    $res_cuentas=sql($cuentas, "Error al insertar el nuevo concepto y plan ") or fin_pagina(); 
?>    
    <script>
     var nro_cuenta=<?=$numero_cuenta?>;
     var nbre_cuenta='<?=$nbre_concepto?>'; 
     var nbre_plan='<?=$plan?>'; 
     agregar(nro_cuenta,nbre_cuenta,nbre_plan);
     window.close();
    </script>
 <?   
    }
    else Error( "La cuenta ".$nbre_concepto." [ ".$plan. " ] ya existe.");
}  
?>
<html>
<head>
 <title>Nuevo Concepto y Plan </title>
</head>
<body>
<form name='form1' method="post" action="">

<?
if ($_POST['concepto'] == 1) {
	  $var=1;
	  $dis_1="";
	  $dis_2="  disabled" ;
}  
elseif ($_POST['concepto'] == 2 || !isset($_POST['concepto']) ) {
	   $var=2;
       $dis_2="";
	   $dis_1="  disabled" ;
}

$sql="select distinct (concepto) from tipo_cuenta order by concepto ";
$res_concepto=sql($sql,"recuperar conceptos ") or fin_pagina();

?>

<div align='center'> <font color='blue'>NUEVA CUENTA </font> </div>
<br>
<table align="center" width="100%" cellpadding="2" class="bordes">
   <tr id="mo" bgcolor="<?=$bgcolor3?>">
     <td> &nbsp;</td>
     <td><b> Seleccionar o Ingresar Nuevo Concepto</b></td>
     <td><b> Ingresar Nuevo Plan</b></td>
   </tr>
   <tr bgcolor=<?=$bgcolor_out?>>
    <td> <input type="radio" name='concepto' value='1'  <? if ($var == 1) echo 'checked'?>  
                  onclick='document.all.nuevo_concepto_1.disabled=false;document.all.nuevo_concepto_2.disabled=true'>
        <td> <select name='nuevo_concepto_1' <?=$dis_1?> >
             <option value=-1> Seleccionar Concepto </option>";
             <? while (!$res_concepto -> EOF) { ?>
             <option value='<?=$res_concepto->fields['concepto']?>'> <?=$res_concepto->fields['concepto']?> </option>
             <?
               $res_concepto->MoveNext();
               }
             ?>
        </td>
        <td align="center" rowspan="2"><input type="text" name="nuevo_plan" value=""></td>
   </tr>
   <tr bgcolor=<?=$bgcolor_out?>>
       <td> <input type="radio" name='concepto' value='2'  <? if ($var == 2) echo 'checked'?> 
              onclick='document.all.nuevo_concepto_1.disabled=true;document.all.nuevo_concepto_2.disabled=false'>
       <td align="left" ><input type="text" name="nuevo_concepto_2"  <?=$dis_2?> value=""></td> 
   </tr>
   <tr>
   	<td colspan="3" bgcolor=<?=$bgcolor_out?>>
   		Nro. Contador: <input type="text" name="t_nro_contador" value="<?=($_POST["t_nro_contador"])?>">
   	</td>
   </tr>
   <tr  bgcolor=<?=$bgcolor_out?>>
     <td align="center" colspan='3'>
      <input type='submit' name='baceptar' value='Aceptar' onclick='return control_datos();'> &nbsp;&nbsp;&nbsp;
      <input type='button' name='Cancelar' value='Cancelar' onclick='window.close()'>
     </td>
   </tr>
</table>
</form>
</body>
</html> 

