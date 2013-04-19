<?
/*
$Author: fernando $
$Revision: 1.6 $
$Date: 2005/11/02 22:21:01 $

*/

require_once ("../../config.php");

$tipo=$parametros["tipo"] or $tipo=$_POST["tipo"];
$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$_POST["id_licitacion"];

echo $html_header;
?>
<script>
  function aceptar_datos(){

    window.opener.document.form1.contenido.value=document.form1.contenido.value;
    <?
    if ($tipo=="descripcion"){
    ?>
    window.opener.document.form1.avisar_descripcion.value=1;
    <?
    }
    if ($tipo=="oferta"){
    ?>
    window.opener.document.form1.avisar_oferta.value=1;
    <?
    }
    ?>

    window.opener.document.form1.submit();
    window.close();

  }
</script>
<form name=form1 method=post>
<input type=hidden name=tipo value="<?=$tipo?>">
<input type=hidden name=id_licitacion value="<?=$id_licitacion?>">

 <? if ($tipo=="oferta") {
   $sql="select comentario from comentario_avisar_oferta where id_licitacion=$id_licitacion";
   $res=sql($sql,"buscar en comentario oferta") or fin_pagina();
   if ($res->RecordCount() >0 )  
      $contenido=$res->fields['comentario'];
   }
   else $contenido="";
   ?>
   <table width=80% align=center class=bordes>
     <tr id=mo>
       <td width=100%>Ingrese el texto a enviar por mail</td>
     </tr>
     <tr>
      <td align=center>
       <textarea name="contenido" rows=20 style="width:100%"><?=$contenido?></textarea>
      </td>
     </tr>
     <tr>
       <td align=center>
          <input type=button name=aceptar value=Aceptar onclick="aceptar_datos();">
          &nbsp;
          <input type=button name=cancelar value=Cancelar onclick="window.close()">
       </td>
     </tr>
   </table>
</form>