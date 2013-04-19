<?
/*
Autor:Mariela
Modificado por:
$Author: fernando $
$Revision: 1.2 $
$Date: 2007/03/06 21:15:03 $
*/

require_once("../../config.php");

if ($_POST["form_busqueda"]){
	 if(!$_POST["busqueda"]){
	  $_POST["busqueda"]=-1;
	 }
}
$var_sesion=array("mes_buscar"=>"","anio_buscar"=>"","busqueda"=>"-1");
variables_form_busqueda("resumen_produccion",$var_sesion);

if ($_POST['eliminar']) {
$db->Starttrans();
$renglon=PostvartoArray('elim_'); //crea un arreglo con los checkbox chequeados
$tam_renglon=sizeof($renglon); 
$list="";

if ($renglon) {  //para ver si hay check seleccionados
        $list='(';
        foreach($renglon as $key => $value){
                $list.=$value.',';
        }
      
        $list=substr_replace($list,')',(strrpos($list,',')));
        
    }
    
if ($list!= "") {
$sql="update ordenes.resumen_produccion set activo=0 where id_resumen_produccion in $list";
sql($sql,"$sql") or fin_pagina();  
} 
$db->CompleteTrans();
}

$itemspp=50;
$order=0;

$orden= array
(
	"default" => "1",
	"default_up"=>"$order",
	"1" => "fecha_entrega",
	"2" => "id_licitacion",
	"3" => "entidad",		
	"4" => "descripcion",	
	"5" => "cant_renglon",
	"6" => "tipo",
    "7" => "nombre",
	"8" => "sistema_operativo",
	"9" => "procesador"	
	
);

$filtro= array
(
	"fecha_entrega"   => "Fecha Entrega",
	"cant_renglon"    => "Cantidad",
	"descripcion"     => "Descripcion",
	"id_licitacion"   => "Licitacion",
	"nombre"          => "Provincia",
	"entidad"         => "Entidad",
);

$query="select * from(
        select id_renglones_oc,cant_renglon,resumen_produccion.descripcion,distrito.nombre,resumen_produccion.fecha_entrega,
        licitacion.id_licitacion,tipo,id_resumen_produccion,distrito.id_distrito,entidad.nombre as entidad,
        so.descripcion as sistema_operativo,pro.descripcion as procesador,resumen_produccion.activo
        from ordenes.resumen_produccion 
        join licitaciones.renglones_oc using (id_renglones_oc)
        join licitaciones.renglon using (id_renglon)
        join licitaciones.licitacion using (id_licitacion)
        join licitaciones.entidad using (id_entidad)        
        left join licitaciones.distrito on resumen_produccion.id_distrito  = distrito.id_distrito
        left join sistema_operativo_rp as so on resumen_produccion.sistema_operativo = so.id_sistema_operativo_rp
        left join procesador_rp as pro on resumen_produccion.procesador = pro.id_procesador_rp
        ) as r
";

$where=" r.activo = 1";

if ($busqueda != "-1") {

if ($mes_buscar<10 && $mes_buscar<>"-1")
   $fecha_seleccionada=$anio_buscar."-0".$mes_buscar."%";
   elseif($mes_buscar>=10  && $mes_buscar<>"-1")
      $fecha_seleccionada=$anio_buscar."-".$mes_buscar."%";
        elseif($mes_buscar=="-1")
             $fecha_seleccionada=$anio_buscar."-"."%"."-"."%";

$where=" fecha_entrega ilike '$fecha_seleccionada'";
}
else {
  $mes_buscar=-1;
  $anio_buscar=2007;
}
echo $html_header;
?>

<form name="form1" action="resumen_produccion.php" method="post">
<div align="center">
<font color="Blue" size="2+"> <b>Resumen de Producción </b></font>
</div>

<br>
<? $tam=sizeof($meses);?>
<table align=center cellpadding=5 cellspacing=0 id=tabla_buscar>
   <tr>
     <td>
     <? list($query,$total,$link_pagina,$up)=form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar"); ?>
     <input type=submit name='form_busqueda' value='Buscar' class='estilo_boton'>
     </td>
  </tr>
  <tr>
     <td>
      <input type="checkbox" name="busqueda" value="1" <?if ($busqueda==1) echo 'checked'?>> <b>Avanzada</b>&nbsp;&nbsp;
      <b>Mes:</b>
      <select name="mes_buscar">
        <option value='-1' selected>Todos</option>
           <?
           for ($i=1;$i<$tam;$i++){
               ?>
               <option value='<?=$i?>' <? if ($mes_buscar==$i) echo 'selected' ?>><?=$meses[$i]?></option>
               <?
            }
           ?>
      </select>
      <b>Año</b>
      <select name=anio_buscar>
      <?
      for($i=2000;$i<2015;$i++){
           ?>
           <option value='<?=$i?>' <? if ($anio_buscar==$i) echo 'selected' ?>><?=$i?></option>
           <?
           }
      ?>
      </select> 
     </td>
  </tr>
</table>

<br>
<?  

$res=sql($query," ERROR 3 $query") or fin_pagina();?>

<table class="bordessininferior" width="100%" align="center" cellpadding="3" cellspacing='0'>
   <tr id=ma>
      <td align=left> <b>Total:</b>  <?=$total?></td>
      <td align="right"><?=$link_pagina;?></td>
   </tr>
</table>
<table width='100%' class="bordessinsuperior" cellspacing='2' align="center">   
   <tr id=mo>
     <td>&nbsp;</td>
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"1","up"=>$up))?>'>Fecha Entrega</a></td>
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"2","up"=>$up))?>'>Id Licitación</a></td>
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"3","up"=>$up))?>'>Entidad</a></td>     
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"4","up"=>$up))?>'>Descripción</a></td>
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"5","up"=>$up))?>'>Cantidad</a></td>    
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"6","up"=>$up))?>'>Tipo Renglon</a></td>   
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"7","up"=>$up))?>'>Provincia</a></td>   
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"8","up"=>$up))?>'>Sistema Operativo</a></td>   
     <td><a href='<?=encode_link('resumen_produccion.php',array("sort"=>"9","up"=>$up))?>'>Procesador</a></td>   
     <td>C</td>
   </tr> 
   <? 
   $i=0;
   $cantidad = 0;
   while(!$res->EOF) {
   	$link = encode_link("provincia_modificar.php",array ("id_resumen_produccion" => $res->fields['id_resumen_produccion']));
   	$onclick = "onclick = document.href=''";
   ?>
   <tr <?=atrib_tr();?>  style="cursor:hand">
      <td> <input type="checkbox" name="elim_<?=$i?>" value='<?=$res->fields['id_resumen_produccion']?>' class="estilos_check"></td>
      <td align="center"><?=Fecha($res->fields['fecha_entrega'])?></td>  
      <td align="center"><?=$res->fields['id_licitacion']?>       </td>    
      <td align="left"><?=$res->fields['entidad']?>               </td>            
      <td align="center"><?=$res->fields['descripcion']?>         </td>  
      <td align="center"><?=$res->fields['cant_renglon']?>        </td>     
      <td align="center"><?=$res->fields['tipo']?>                </td> 
      <td align="center"><?=$res->fields['nombre'];?>             </td>
      <td align="left">  <?=$res->fields['sistema_operativo']?>   </td>
      <td align="left">  <?=$res->fields['procesador']?>          </td>
      <td align="center"><a target="_blank" href="<?=$link?>"><img src='../../imagenes/modificar.gif' border=0 alt='Haz click para modificar la provincia'></a></td>
   </tr>
   <? $cantidad+=$res->fields["cant_renglon"];
      $res->MoveNext();
      $i++; 
   }?>
   <tr <?=atrib_tr();?>>
     <td colspan="4">   &nbsp;</td>
     <td align="center"><b>Total:</b></td>
     <td align="center"><b><font color="Red"><?=$cantidad?></font></b></td>
     <td colspan="5">   &nbsp;</td>
   </tr>
</table>
<br>
<table width="100%">
 <tr>
  <td align='center'>
   <input type='submit' name='eliminar' value='Eliminar' class='estilo_boton'>
   <input type='button' name='cerrar' value='Cerrar' onClick="window.close();" class='estilo_boton'> 
  </td>
 </tr>
</table> 

</form>
</body>
</html>
<?
echo fin_pagina();
?>


