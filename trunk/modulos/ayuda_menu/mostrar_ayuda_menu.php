<?
require_once("../../config.php");
$pagina=$parametros['menu'];
$pagina=ereg_replace("/","&",$pagina);
$pagina=ereg_replace("\?","-",$pagina);

?>

<form name='form1' action="mostrar_ayuda_menu.php" method="post">
  <img src='./imagenes_menu/<?=$pagina?>.jpg'>
</form>