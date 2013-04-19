<?
/*
Author: lizi

modificada por
$Author: fernando $
$Revision: 1.1 $
$Date: 2005/05/03 22:57:33 $
*/

require_once ("../../config.php");
require_once("funciones.php");
echo $html_header
?>
<script>

  function aceptar_datos(){

      window.opener.document.form1.reemplazo_respuesto_muleto.value=1;
      window.opener.document.form1.marca_reemplazo.value=form1.marca.value;
      window.opener.document.form1.modelo_reemplazo.value=form1.modelo.value;
      window.opener.document.form1.observaciones_reemplazo.value=form1.observaciones.value;
      window.opener.document.form1.nro_serie_reemplazo.value=form1.nro_serie.value;
      window.opener.document.form1.submit();
      window.close();

  }

  function cargar_datos(){

   document.form1.marca.value=window.opener.document.form1.descripcion_reem.value;

  }
</script>

<form name=form1 method=post>
  <table width=80% align=center class=bordes>
    <tr id=mo><td>Ingrese los datos del nuevo muleto</td></tr>
    <tr>
       <td align=center width=100%>
          <? tabla_datos_muletos();?>
       </td>
    </tr>
    <tr>
       <td align=Center>
       <input type=button name=aceptar value=Aceptar onclick="aceptar_datos();">
       &nbsp;
       <input type=button name=cancelar value=Cancelar onclick="window.close()">
       </td>
    </tr>
  </table>
</form>
<script>
  cargar_datos();
</script>
<?echo fin_pagina();?>