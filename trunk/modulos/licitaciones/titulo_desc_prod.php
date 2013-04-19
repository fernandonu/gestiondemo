<?
/*
Author: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.4 $
$Date: 2004/10/18 21:49:18 $
*/
include("../../config.php");

$id_producto=$parametros["id_producto"];
$onclickcargar=$parametros['onclickcargar'] or $onclickcargar=$_POST['onclickcargar'];


if ($_POST["guardar"]) {
  $titulo=$_POST["nuevo_titulo"];
    if ($titulo=="")
              error("Error:Tiene que ingresar un titulo");

    $sql="select count(titulo) as cantidad from prioridades where titulo='$titulo'";
    $res=sql($sql) or fin_pagina();
    if ($res->fields["cantidad"]>0){
         error("Error:Ese titulo ya existe");
    }
    if(!$error){
             $sql="insert into prioridades (titulo) values ('$titulo')";
             $msg=sql($sql) or fin_pagina();
             Aviso("Se inserto el nuevo título");
             }
 }

if ($_POST["buscar"] || $_POST["filtro"]){
  $argumento=$_POST['filtro'];
  $where=" where titulo ilike '%$argumento%'";
}

echo $html_header;
$sql="select distinct titulo from prioridades $where";
$res=sql($sql) or fin_pagina();

?>
<script>
function setear_hidden(titulo) {

 document.form1.titulo_elegido.value=titulo;
 <?
 echo $onclickcargar;
 ?>;
 window.close();
}
</script>
<form name=form1 method=post action="titulo_desc_prod.php">
<input type=hidden name=onclickcargar value='<?=$onclickcargar?>'>
<input type=hidden name=titulo_elegido value=''>
<table width=100% align=Center class=bordes>
    <tr><td id=mo>Titulos de las Descripciones</td></tr>
    <tr>
       <td>
          <table width=100% align=center class=bordes>
             <tr>
                <td align=right><b>Título a Buscar</b></td>
                <td align=center><input type='text' name='filtro' size=40></td>
                <td align=center><input type='submit' name='buscar' value='Buscar'></td>
             </tr>
          </table>
       </td>
    </tr>
</table>

<?
if ($res->recordcount()>5) $porcentaje="40%";
                       else $porcentaje="20%";
?>
<div style="position:relative; width:100%;height:<?=$porcentaje?>; overflow:auto;">
<table width=100% align=Center class=bordes>
      <tr id=mo>
         <td>
          Títulos Existentes
         </td>
      </tr>
      <?
      for($i=0;$i<$res->recordcount();$i++){
      ?>
      <tr <?=atrib_tr()?> onclick="setear_hidden('<?=$res->fields["titulo"]?>');">
        <td>
        <b><?=$res->fields["titulo"];?></b>
        </td>
      </tr>
      <?
      $res->MoveNext();
      }
      ?>
      </td>
    </tr>
</table>
</div>
<br>
<table width=100% align=Center class=bordes>
    <tr>
       <td>
          <table width=100% align=center>
            <tr id=mo_sf>
              <td>Nuevo Título</td>
            </tr>
            <tr>
              <td>
              <textarea name=nuevo_titulo rows=3 style="width:100%" class="estilos_textarea"></textarea>
              </td>
            </tr>
          </table>
       </td>
    </tr>

    <tr>
    <td>
       <table width=100% align=center>
          <tr>
            <td width=50% align=center>
               <input type=submit name=guardar value="Guardar Nuevo Título" style="width:70%">
            </td>
            <td width=50% align=center>
               <input type=button name=cerrar value=Cerrar onclick="window.close()" style="width:70%">
            </td>
          </tr>
       </table>
     </td>
    </tr>

</table>
</form>
<?
fin_pagina();
?>