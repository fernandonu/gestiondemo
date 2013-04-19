<?
/*
$Author: fernando $
$Revision: 1.2 $
$Date: 2007/03/06 21:15:49 $
*/
require_once("../../config.php");


$id_resumen_produccion=$parametros['id_resumen_produccion'] or $id_resumen_produccion=$_POST['id_resumen_produccion'];

if ($_POST['guardar']){
$db->Starttrans(); 

 $sql = "update resumen_produccion set";
 
 if ($_POST['provincia']!=-1){
    $sql.= " id_distrito='".$_POST['provincia']."'";
    $hay_uno = 1;
 }
    
 if ($_POST["procesador"]!=-1)  {
 	if ($hay_uno) $sql.=",";
    $sql.= " procesador = ".$_POST["procesador"]."";
    $hay_uno = 1;
 }
    
 if ($_POST["sistema_operativo"]!=-1)   {
 	if ($hay_uno) $sql.=",";
    $sql.= " sistema_operativo=".$_POST['sistema_operativo'];
 }
    
 $sql.= " where id_resumen_produccion=$id_resumen_produccion";
 $result = sql($sql,"$sql") or fin_pagina();
 
?>
<script>
 window.opener.document.form1.submit();
 window.close();
</script> 
<?
$db->CompleteTrans();
}

if ($_POST["modificar_procesador"]){
	$id = $_POST["procesador"];
	$link = encode_link("nuevos_items_resumen_produccion.php",array("accion"=>"Modificar","items"=>"Procesador","id"=>$id));
	?>
	<script>
	window.open('<?=$link?>');
	</script>
	<?
}//del if

if ($_POST["modificar_so"]){
	$id = $_POST["sistema_operativo"];
	$link = encode_link("nuevos_items_resumen_produccion.php",array("accion"=>"Modificar","items"=>"Sistema Operativo","id"=>$id));
	?>
	<script>
	window.open('<?=$link?>');
	</script>
	<?
}//del if


if ($id_resumen_produccion){
	
	$sql = "select id_distrito,sistema_operativo,procesador from resumen_produccion where id_resumen_produccion = $id_resumen_produccion";
	$res = sql($sql) or fin_pagina();
	$id_distrito       = $res->fields["id_distrito"];
	$sistema_operativo = $res->fields["sistema_operativo"];
	$procesador        = $res->fields["procesador"];
	
}

echo $html_header;
?>
<form name='form1' action='provincia_modificar.php' method='POST'>
<input type="hidden" name="id_resumen_produccion" value="<?=$id_resumen_produccion?>">
<input type="hidden" name="id_distrito"           value="<?=$id_distrito?>">
<input type="hidden" name="procesador"            value="<?=$procesador?>">
<input type="hidden" name="sistema_operativo"     value="<?=$sistema_operativo?>">

<?
$sql_dist="select id_distrito,nombre
           from licitaciones.distrito";
$res=sql($sql_dist,"$sql_dist") or fin_pagina();
?>
<table align="center" cellpadding="2" width="40%" class="bordes" bgcolor=<?=$bgcolor_out?>>
 <tr> 
    <td id="mo" bgcolor="<?=$bgcolor3?>" align="center" colspan="4"> 
         Provincia
    </td>    
 </tr>
 <tr>
     <td colspan="4">
        <select name="provincia">
        <option value=-1>Seleccione una Provincia</option>
        <? 
        for($i=0;$i<$res->recordcount();$i++){
        	  $id          = $res->fields['id_distrito'];
        	  $selected    = ($id_distrito == $id)?"selected":"";
        	  $nombre      = $res->fields["nombre"];
        ?>
          <option value='<?=$id?>' <?=$selected?>><?=$nombre?> </option>
        <?
        $res->MoveNext();
        }
        ?>
        </select>
     </td>
 </tr>
  <tr> 
    <td id="mo" bgcolor="<?=$bgcolor3?>" align="center" colspan="4"> 
      Procesador
    </td>
 </tr>
  <?
  $sql = "select * from procesador_rp ";
  $res = sql($sql) or fin_pagina();
  ?>  
 <tr>
   <td width="20%">
     <select name="procesador" style="width:170">
     <option value=-1>Seleccione un Procesador</option>
     <?
     for($i=0;$i<$res->recordcount();$i++){
     	$id          = $res->fields["id_procesador_rp"];
     	$descripcion = $res->fields["descripcion"];     	
     	$selected = ($id == $procesador)?"selected":"";
     ?>
       <option  value="<?=$id?>" <?=$selected?>><?=$descripcion?></option>
     <?
       $res->movenext();
     }
     ?>     
     </select>
   </td>
   <?
    $link_modificar = encode_link("nuevos_items_resumen_produccion.php",array("accion"=>"Modificar","items"=>"Procesador"));
    $link_nuevo     = encode_link("nuevos_items_resumen_produccion.php",array("accion"=>"Nuevo","items"=>"Procesador"));    
   ?>
   <td><input type="submit" name="eliminar_procesador"  value="Eliminar"></td>      
   <td><input type="submit" name="modificar_procesador" value="Modificar"></td>
   <td><input type="button" name="nuevo_procesador"     value="Nuevo"     onclick="window.open('<?=$link_nuevo?>')" ></td>   
 </tr>
  <tr> 
    <td id="mo" bgcolor="<?=$bgcolor3?>" align="center" colspan="4"> 
         Sistema Operativo
    </td>
 </tr>
 <?
 $sql = "select * from sistema_operativo_rp ";
 $res = sql($sql) or fin_pagina();
 ?> 
 <tr>
   <td>
     <select name="sistema_operativo" style="width:170">
     <option value=-1>Seleccione un SO</option>     
     <?
     
     for($i=0;$i<$res->recordcount();$i++){
     	$id          = $res->fields["id_sistema_operativo_rp"];
     	$descripcion = $res->fields["descripcion"];     	
     	$selected    = ($sistema_operativo == $id)?"selected":"";
     ?>
       <option  value="<?=$id?>" <?=$selected?>><?=$descripcion?></option>
     <?
       $res->movenext();
     }
     ?>
     </select>
   </td>
   <?
    $link_modificar = encode_link("nuevos_items_resumen_produccion.php",array("accion"=>"Modificar","items"=>"Sistema Operativo"));
    $link_nuevo     = encode_link("nuevos_items_resumen_produccion.php",array("accion"=>"Nuevo","items"=>"Sistema Operativo"));    
   ?>   
   <td><input type="submit" name="eliminar_so"  value="Eliminar"></td>      
   <td><input type="submit" name="modificar_so" value="Modificar" ></td>
   <td><input type="button" name="nuevo_so"     value="Nuevo"     onclick="window.open('<?=$link_nuevo?>')"></td>   
 </tr>
</table>
<BR>
<table align="center"> 
  <tr>
    <td><input type="submit" name="guardar" value="Guardar"></td>
    <td><input type="button" name="cerrar" value="Cerrar" onclick="window.close();"></td>
  </tr>
</table>  
<?
echo fin_pagina();
?>
