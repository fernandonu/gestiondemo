<?php
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2005/05/14 18:47:00 $
*/

$codigo_barra=$_POST["codigo_barra"];
require_once("../../config.php");

if ($_POST["traer"]){
      $sql="select p.id_producto,p.desc_gral,cb.codigo_barra,id_proveedor,razon_social
               from general.productos p
               join general.codigos_barra cb using(id_producto)
               left join general.log_codigos_barra using(codigo_barra)
               left join orden_de_compra using(nro_orden)
               left join proveedor using(id_proveedor)
               where cb.codigo_barra ilike  '%$codigo_barra%'";
               $resultado=sql($sql) or fin_pagina();

 }
echo $html_header;
?>
<script>
  function aceptar_codigo(){
    var codigo;
    var radio;
    radio=document.form1.radio;

     if (typeof(radio.length)!='undefined')
          {
          for(i=0;i<=radio.length;i++){
           if (radio[i].checked){
               window.opener.document.form1.codigo_barra.value=radio[i].value;
               break;
               }
          }
          }
          else{
             window.opener.document.form1.codigo_barra.value=radio.value;
          }
     window.opener.document.form1.traer_productos_pagina.value=1;
     window.opener.document.form1.submit();
     window.close();


  }


function aceptar_codigo_fila(indice){

    var codigo;
    var radio;
    radio=document.form1.radio;


 if (typeof(radio.length)!='undefined')
     {
      radio[indice].checked='true';
      aceptar_codigo();
     }
     else
     {
     radio.checked;
     aceptar_codigo();
     }

}

</script>
<form name=form1  method=post>
   <table width=100% align=Center class=bordes>

     <tr>
        <td align=center>
        <b>Código Barra</b>

        <input type=text name=codigo_barra value='<?=$codigo_barra?>'>

        <input type=submit value=Traer name=traer >
        </td>
     </tr>
     <tr>
        <td id=mo>Elija el Código de Barra Correspondiente</td>
     </tr>
<?
if ($resultado){
?>
     <tr>
        <td>
           <table width=100% align=center>
              <tr id=ma>
                <td width=1%> </td>
                <td>Código Barra</td>
                <td>Producto</td>
                <td>Proveedor</td>
              </tr>
           <?
           for ($i=0;$i<$resultado->recordcount();$i++)
           {
           ?>
           <tr <?=atrib_tr()?> onclick="aceptar_codigo_fila(<?=$i?>);">
               <td><input type=radio name='radio' value="<?=$resultado->fields["codigo_barra"]?>"></td>
              <td><?=$resultado->fields["codigo_barra"]?></td>
              <td><?=$resultado->fields["desc_gral"]?></td>
              <td><?=$resultado->fields["razon_social"]?></td>
           </tr>
           <?
           $resultado->movenext();
           }
           ?>
           </table>
        </td>
     </tr>
     <?
 $disabled=" ";
  }
  else
  $disabled=" disabled";
      ?>

     <tr>
        <td align=center>
          <input type=button name=aceptar value=Aceptar <?=$disabled?> onclick="aceptar_codigo()">
          &nbsp;
          <input type=button name=cancelar value=Cancelar onclick="window.close()">
        </td>
     </tr>

   </table>
</form>