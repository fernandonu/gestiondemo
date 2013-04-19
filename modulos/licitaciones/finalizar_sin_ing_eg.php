<?
/*
$Author: mari $
$Revision: 1.4 $
$Date: 2007/01/05 19:43:19 $

*/

require_once("../../config.php");
require_once("func_cobranzas.php");
echo $html_header;

$id_cobranza=$parametros["id_cobranza"] or $_POST['id_cobranza'];
$cob_atadas=$parametros["cob_atadas"] or $_POST['cob_atadas'];  //si cob_atadas ==1 facturas atadas

if ($_POST['finalizar'])  {
     $id_cobranza=$_POST["id_cobranza"] ;
     $comentario=$_POST['contenido'];
$db->starttrans();	
	if ($_POST["cob_atadas"] ==1 ) {
		$sql="select id_secundario from atadas where id_primario=$id_cobranza";
		$res=sql($sql,"selecciona atadas") or fin_pagina();
		$arr[]=$id_cobranza;
		while ($arr1=$res->fetchrow())
			$arr[]=$arr1["id_secundario"];
		while (list($key,$co)=each($arr)) {
			$array_sql[]="UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='".$_ses_user['name']."',fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza=$co";
		}
		sql($array_sql) or fin_pagina();
		//finalizar_vta_factura($id_cobranza,1);
	}
	else {
		$sql = "UPDATE cobranzas SET estado='FINALIZADA',fin_usuario='".$_ses_user['name']."',fin_fecha='".date("Y-m-d H:i:s")."' WHERE id_cobranza=$id_cobranza";
		sql($sql) or fin_pagina();
		//finalizar_vta_factura($id_cobranza,1);
	}
    if ($comentario !="") {
	$sql="insert into comentarios_finalizar (id_cobranza,comentario) values 
	      ($id_cobranza,'$comentario')";
	$res=sql($sql,"insert comentario") or fin_pagina();
    }
$db->CompleteTrans();
$ref=encode_link('lic_cobranzas.php',array("cmd"=>"finalizada","cmd1"=>"detalle_cobranza","id"=>$id_cobranza));	
?> 

<script>
  window.opener.location.href='<?=$ref?>';
  window.close();
</script>

<?
}


?>

<form name=form1 method=post action="finalizar_sin_ing_eg.php">
<input type=hidden name=id_cobranza value="<?=$id_cobranza?>">
<input type=hidden name=cob_atadas value="<?=$cob_atadas?>">
<?

  $sql="select cobranzas.id_licitacion,monto,tipo_factura,entidad.nombre,simbolo,
	    case when facturas.nro_factura is null then cobranzas.nro_factura
		else facturas.nro_factura end as numero
		from cobranzas 
		join moneda using (id_moneda)
		left join entidad using (id_entidad)
		left join facturas using (id_factura)
		where id_cobranza=$id_cobranza";
  $res=sql($sql,"datos cobranzas") or fin_pagina();
  
  if ($cob_atadas == 1) {
     $sql_atadas="select id_secundario from licitaciones.atadas where id_primario=$id_cobranza";
     $res_atadas=sql($sql_atadas,"traer atadas") or fin_pagina(); 
    
     $list_cob='(';
      while (!$res_atadas->EOF) {
      $list_cob.=$res_atadas->fields['id_secundario'].",";
      $res_atadas->MoveNext();	
      }
     $list_cob=substr_replace($list_cob,')',(strrpos($list_cob,',')));
     
     $sql_atadas="select monto,tipo_factura,simbolo,
	       case when facturas.nro_factura is null then cobranzas.nro_factura
		   else facturas.nro_factura end as numero
		   from cobranzas join moneda using (id_moneda)
		   left join entidad using (id_entidad)
		   left join facturas using (id_factura)
		   where id_cobranza in $list_cob";
     $datos_atadas=sql($sql_atadas,"datos atadas") or fin_pagina();
   }
   ?>
 <table width=80% align=center >
   <tr align="center">
      <td colspan=2><font size=2> <b>Factura </b> </font> <?=strtoupper($res->fields['tipo_factura'])."<b> Nº</b> ".$res->fields['numero']?>
      &nbsp; &nbsp; <b>Monto: </b><?=$res->fields['simbolo']." ".formato_money($res->fields['monto'])?></td>
   </tr>   
    <? if ($cob_atadas==1 ) {
       while (!$datos_atadas->EOF) {
       ?>
       <tr align="center">
           <td colspan=2><font size=2> <b>Factura </b> </font> <?=strtoupper($datos_atadas->fields['tipo_factura'])."<b> Nº</b> ".$datos_atadas->fields['numero']?>
           &nbsp; &nbsp; <b>Monto: </b><?=$datos_atadas->fields['simbolo']." ".formato_money($datos_atadas->fields['monto'])?></td>	
       </tr>  
     <? $datos_atadas->MoveNext();
       }  
    } 

   if ($res->fields['id_licitacion'] !="") {
   ?>

   <tr  align="center">  
        <td><b>Licitación: </b><?=$res->fields['id_licitacion']?></td>   
        <td><b>Entidad: </b><?=$res->fields['nombre']?></td>
   </tr>
   <?}?>
   </table>
   <br>
   <table width=80% align=center class=bordes>
   <tr>
   </tr>
     <tr id=mo>
       <td width=100%> Comentarios</td>
     </tr>
     <tr>
      <td align=center>
       <textarea name="contenido" rows=20 cols='120'></textarea>
      </td>
     </tr>
     <tr>
       <td align=center>
          <input type=submit name=finalizar value=Finalizar>
          &nbsp;
          <input type=button name=cancelar value=Cerrar onclick="window.close()">
       </td>
     </tr>
   </table>
</form>