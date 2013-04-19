<?
/*
MODIFICADA POR
$Author: fernando $
$Revision: 1.2 $
$Date: 2006/12/19 21:52:06 $
*/
require_once("../../config.php");
echo $html_header;
$deposito = $parametros["deposito"] or $deposito = $_POST["deposito"];
if (!$deposito) $deposito = "RMA";

if ($_POST['guardar']=="Guardar")
{      $db->StartTrans(); 
	   /*$sql = "select * from usuarios where login='$_ses_user_login'";
	   $rs = sql($sql) or fin_pagina();*/
	   $id_usuario=$_ses_user['id'];
       $cantidad=$_POST['cantidad'];
       $cantid=$_POST['cantid'];
       if($cantid>0)
       {
       	$query="DELETE FROM configurar_vista WHERE id_usuario=$id_usuario";
		$rs1 = $db->Execute($query) or die($db->ErrorMsg());
       }
       $t=0;
       while ($t<$cantidad) {
       	$rma=$_POST["rma_$t"];
       	$campo=$_POST["campo_$t"];
       	if($rma)
       	{
       	 $campos="campo,ver,id_usuario,deposito";
		 $valores="'$campo',1,$id_usuario,'$deposito'";
		 $q="insert into configurar_vista ($campos) values ($valores)";
         $db->Execute($q)or die($db->ErrorMsg()."<br>".$q);	
       	}
       	else 
       	{
         $campos="campo,ver,id_usuario,deposito";
		 $valores="'$campo',0,$id_usuario,'$deposito'";
		 $q="insert into configurar_vista ($campos) values ($valores)";
         $db->Execute($q)or die($db->ErrorMsg()."<br>".$q);		
       	}
       	$t++;
       }
	   
	   
		$db->CompleteTrans();
        ?>
        <script>
        window.opener.location.reload();
        window.close();
        </script>
        <?

}
 $id_usuario=$_ses_user['id'];
 $sql = "select * from configurar_vista where id_usuario=$id_usuario and deposito = '$deposito'";
 $rs = sql($sql) or fin_pagina();
 $cantid=$rs->RecordCount();
 
?>
<script>
function seleccionar_todos_local(elegir){
var valor;
            if(elegir.checked==true){
            	valor=true;
                var i=0;
                loco=eval ("document.form1.rma_"+i);
                while (typeof(loco)!='undefined'){
                 	loco.checked=valor;
                    i++;
                    loco=eval ("document.form1.rma_"+i);
               	}//del while
             }
             else{
             	valor=false;
                var i=0;
                loco=eval ("document.form1.rma_"+i);
                while (typeof(loco)!='undefined'){
                 	loco.checked=valor;
                    i++;
                    loco=eval ("document.form1.rma_"+i);
               	}//del while
             }
}//de la funcion
</script>
<?
$sql="select * from campos_ver";
$res=sql($sql) or fin_pagina();

?>
<form name="form1" action="configurar_vista.php" method="POST">
<input type="hidden" name="deposito" value="<?=$deposito?>">
<table align="center" width="80%">
<tr>
<td align="center">
<b><font size="4" color="Blue">Configurar Vista </font></b>
</td>
</tr>
</table>
<table width="50%" border="1" cellspacing="1" cellpadding="1" align="center">
 <tr id="mo">
  <td align="center" valign="top">
   <b>Campos</b>
  </td>
  <td>
   <b>Ver</b><INPUT class='estilos_check' type=checkbox name="selec_todos" onclick="seleccionar_todos_local(this)">
  </td>    
  </tr>
 <?
 $i=0;
 while (!$res->EOF)
 {
 ?>   
 <tr>
 <td>
 <b><?=$res->fields['campos_ver']?></b>
 </td>
 <td>
 <INPUT class='estilos_check' type=checkbox name="rma_<?=$i?>" value='<?=$res->fields["campos_tabla"]?>'<?if($cantid>0 && $rs->fields['ver']==1){?> checked <?}?>>
 </td>
 </tr>
 <input type="hidden" name="campo_<?=$i?>" value='<?=$res->fields["campos_tabla"]?>'>
 <?
 $i++;
 $res->MoveNext();
 if($cantid>0)
 {
  $rs->movenext();	
 }
 }
?>
<input type="hidden" name="cantidad" value="<?=$i?>">
<input type="hidden" name="cantid" value="<?=$cantid?>">
</table>
 <table  width="100%">
     <tr>
      <td width="100%" align="center">
      <input name="guardar" type="submit"  value="Guardar">
      <input name="cerrar" type="button"  value="Cerrar" onclick="window.close()">
      </td>
    </tr>
 </TABLE>  