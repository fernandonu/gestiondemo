<?
/*
Autor: mariela
Modificado por:
$Author: mari $
$Revision: 1.3 $
$Date: 2007/02/12 12:30:25 $
*/

require_once("../../config.php");
echo $html_header;

$var_sesion=array("lideres"=>"-1");
variables_form_busqueda("control_seg",$var_sesion);

?>
<form name='form1' action="control_seguimiento.php" method="post">
<input type="hidden" name="chk" value="">
<?
$orden = array(
        "default" => "3",
        "default_up" => 1,
        "1" => "licitacion.id_licitacion",
        "2" => "lider_iniciales",
        "3" => "subido_lic_oc.vence_oc",
        "4" => "entidad.nombre",
        "5" => "ensamblador.nombre",
        "6" => "fecha_estimada"
    );
   
    $filtro = array(
        "distrito.nombre" => "Distrito",
        "entidad.nombre" => "Entidad",
        "entrega_estimada.observaciones" => "Observaciones",
        "licitacion.id_licitacion" => "ID de licitación",
		"entrega_estimada.responsable" => "Responsable",
		"subido_lic_oc.vence_oc"=>"Fecha de vencimiento",
		"tipo_entidad.nombre"=>"Tipo de organismo"
    );

$sql="select licitacion.id_licitacion,entrega_estimada.nro,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado, 
      entidad.nombre as nombre_entidad,distrito.nombre as nombre_distrito,entrega_estimada.responsable, 
      subido_lic_oc.nro_orden as numero,subido_lic_oc.id_subir,subido_lic_oc.vence_oc as vence,garantia_oferta.pedida,
      subido_lic_oc.pedir_prorroga,garantia_oferta.entregada, entrega_estimada.id_ensamblador,entrega_estimada.fecha_estimada,
      entrega_estimada.observaciones,entrega_estimada.fecha_inamovible, ensamblador.nombre,cant_orden.nro_orden, tmp_oc.total_oc, 
	  tipo_entidad.nombre as tipo_entidad, tipo_entidad.observaciones as tipo_entidad_desc ,cobranzas.nro_factura,
	  falta_factura,falta_remito, usuarios.apellido||', '||usuarios.nombre as lider_nombre, 
	  usuarios.iniciales as lider_iniciales,usuarios.id_usuario	, usuarios.login as lider_login, 
	  falta_oc,falta_pm,falta_op,no_llego_oc,todo_ok,ultima_modificacion_fecha,ultima_modificacion_usuario
	  FROM (licitaciones.licitacion LEFT JOIN licitaciones.entidad USING (id_entidad)) 
	  LEFT JOIN licitaciones.distrito USING (id_distrito) 
	  LEFT JOIN licitaciones.garantia_oferta USING (id_licitacion) 
	  LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion) 
	  LEFT JOIN ordenes.ensamblador USING (id_ensamblador) 
	  LEFT JOIN (select count(nro_factura) as nro_factura,id_licitacion 
	             from licitaciones.cobranzas  group by id_licitacion) as cobranzas USING (id_licitacion)  
	  LEFT JOIN (select licitacion.id_licitacion, count(nro_orden) as nro_orden 
	            from (licitaciones.licitacion left join compras.orden_de_compra USING (id_licitacion))
	             group by licitacion.id_licitacion
	           )as cant_orden 
	  using (id_licitacion) LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada) 
	  LEFT JOIN (select count(id_renglones_oc) as falta_remito,id_subir
			     from licitaciones.renglones_oc
				 where id_renglones_oc not in (
				     select id_renglones_oc from facturacion.remitos
				      join facturacion.items_remito using (id_remito)
				      where estado <> 'a' and id_renglones_oc is not null
				      order by id_renglones_oc)
				       group by id_subir) as f_rem using (id_subir) 
	  LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider 
	  LEFT JOIN (select count(id_renglones_oc) as falta_factura,id_subir
						from licitaciones.renglones_oc
						where id_renglones_oc not in (
						select id_renglones_oc from facturacion.facturas
						join facturacion.items_factura using (id_factura)
						where estado <> 'a' and id_renglones_oc is not null
						order by id_renglones_oc)
						group by id_subir) as f_fact using (id_subir)
						left join (select id_subir, sum(renglones_oc.precio* renglones_oc.cantidad) as total_oc
				                   from  licitaciones.subido_lic_oc
				                   join licitaciones.renglones_oc using(id_subir)
					               group by id_subir)as tmp_oc on (subido_lic_oc.id_subir=tmp_oc.id_subir)
			           left join licitaciones.tipo_entidad using (id_tipo_entidad)
	  left join ordenes.control_seguimiento on subido_lic_oc.id_subir=control_seguimiento.id_subir";

$where="entrega_estimada.finalizada=0 AND borrada='f'"; 

if ($lideres!=-1)
	  	$where.=" and (usuarios.id_usuario='".$lideres."')";

$sel_lider="select id_usuario,iniciales as label
		    from sistema.usuarios
		    where (tipo_lic='L')and(login<>'corapi') order by label";
$res_sel=sql($sel_lider,"$sel_lider")or fin_pagina();




?>
<bR>
<div align="center" >
  <font color="Teal" size="3"><b>  CONTROL DE SEGUIMIENTOS DE PRODUCCION  </b></font>
</div>
<br>
<table align=center cellpadding=5 cellspacing=0>
  <tr> 
     <td align=left bgcolor=<?=$bgcolor2?>>
    	Líderes:<select name="lideres">
    	<?while(!$res_sel->EOF){?>
    	<option value="<?=$res_sel->fields['id_usuario']?>" 
              <?if($lideres==$res_sel->fields['id_usuario']) echo "selected";?> >
    	    <?=$res_sel->fields['label']?>
    	</option>
    	<?$res_sel->MoveNext();}?>
    	<option value="-1" <?if ($lideres==-1) echo 'selected'?>>Todos</option>
    	</select>
    </td>
     <td>
     <? list($sql,$total,$link_pagina,$up)=form_busqueda($sql,$orden,$filtro,$link_tmp,$where,"buscar");
     //echo "SQL= ".$sql;?>
     <input type=submit name='form_busqueda' value='Buscar' class='estilo_boton'>
     </td>
     <td align="right"><input type="button" name="cerrar" value="Cerrar" onclick="window.close();"></td>
  </tr>
</table>
<br>
<?$res=sql($sql) or fin_pagina();?>
<table class="bordessininferior" width="95%" align="center" cellpadding="3" cellspacing='0'>
   <tr id=ma>
      <td align=left> <b>Total seguimientos:</b>  <?=$total?></td>
      <td align="right"><?=$link_pagina;?></td>
   </tr>
</table>
<table width='95%' class="bordessinsuperior" cellspacing='2' align="center">   
   <tr id=mo>
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"1","up"=>$up))?>'>ID/Est</a></td>
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"6","up"=>$up))?>'>Nº Seg</a></td>
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"2","up"=>$up))?>'>Lider</a></td>
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"3","up"=>$up))?>'>Venc</a></td>
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"4","up"=>$up))?>'>Entidad</a></td>
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"5","up"=>$up))?>'>Ensamblador</a></td>   
     <td>F</td>   
     <td>R</td>   
     <td><a href='<?=encode_link('control_seguimiento.php',array("sort"=>"6","up"=>$up))?>'>Entrega</a></td>   
     <td>Falta OC</td>   
     <td>Falta PM</td>   
     <td>Falta OP</td>   
     <td>No llego OC</td>   
     <td>Todo OK</td>   
     <td>UM</a></td>   
   </tr>
    
   <? $fecha_hoy1 = date("d/m/Y",mktime());
       while(!$res->EOF) {
       	$id_subir=$res->fields['id_subir'];
   	    $fecha_vencimiento=fecha($res->fields['vence']); 

   	    if ((compara_fechas(fecha_db($fecha_hoy1),fecha_db($fecha_vencimiento)) >= 0))//la fecha actual es mayor a la vencida
		       {
		        $color_state="red";//color rojo
		        $texto_state="La orden de compra esta vencida"; //ya vencio la orden
		       }
		     else
		      {
		     	switch(diferencia_dias_habiles($fecha_hoy1,$fecha_vencimiento))
		                {
		                 case 1:
		                 case 2:
		                        $color_state="orange";//color naranja
		                        $texto_state="Faltan de 1 a 2 dias para el vencimiento de la orden";
		                        break; //1 o 2 dias habiles para vencer
		                 case 3:
		                 case 4:
		                 case 5:
		                         $color_state="yellow";//color amarillo
		                         $texto_state="Faltan de 3 a 5 dias para vencimiento de la orden";
		                         break;//3, 4 o 5 dias habiles para vencer
		                 default:
		                          $color_state="white";//color blanco
		                          $texto_state="";
		                 break;
		                }
		   }
   	
   	
       $g_title="Responsable: ".$res->fields['responsable']."\nObservaciones:\n".$res->fields['observaciones'];
   ?>
   <tr <?=atrib_tr();?> title="<?=$g_title?>" style="cursor:hand">
    <a style="color=<?=contraste($color_state,"#000000","#ffffff")?>"  href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank">
      <td align="center" bgcolor="<?=$color_state?>" title="<?=$texto_state?>"><?=$res->fields['id_licitacion']?></td>   
   </a>  
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank">
      <td align="center"><?=$res->fields['nro']?></td>   
   </a>   
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank">
      <td align="center"><?=$res->fields['lider_iniciales']?></td>   
   </a>      
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank">
      <td align="center"><?=Fecha($res->fields["vence"])?></td>   
   </a>
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank"> 
      <td align="center" <?if($res->fields['comprado']==1) {echo "bgcolor='pink'";} elseif ($res->fields['comprado']==2) echo "bgcolor='green'";?>>
      <?=$res->fields["nombre_entidad"]?>
      </td>   
   </a>
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank"> 
      <td align="center"> <?
        	if (!(stripos($res->fields["nombre"], "sin producc")===false)) $salida="SP";
        	elseif (!(stripos($res->fields["nombre"], "coradir bs")===false)) $salida="BsAs";
        	elseif (!(stripos($res->fields["nombre"], "pcpower")===false)) $salida="PCP";
        	else $salida=$res->fields["nombre"];
        	echo html_out($salida);
      ?>
      </td>   
   </a>
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"nro"=>$res->fields["nro"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank"> 
      <td align="center"><?
          if ($res->fields["falta_factura"] !=null || $res->fields["falta_factura"] !="")
                $color='red';
          else $color='green'; ?>
       <table width="100%" cellspacing=1 cellpadding=3  align=center>
         <tr>
           <td height=80% bgcolor='<?=$color?>'>&nbsp;</td>
         <tr>
       </table>
      </td>   
   </a>
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"],"nro"=>$res->fields["nro"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank"> 
     <td align="center">  
      <?if ($res->fields["falta_remito"] !=null || $res->fields["falta_remito"] !="")
           $color='red';
        else $color='green'; ?>
       <table width="100%" cellspacing=1 cellpadding=3  align=center>
         <tr>
           <td height=80% bgcolor='<?=$color?>'>&nbsp;</td>
         <tr>
       </table>
     </td>   
   </a>
   <a href='<?=encode_link('ver_seguimiento_ordenes.php',array("cmd1" => "detalle", "id" => $res->fields['id_licitacion'],"id_entrega_estimada" => $res->fields['id_entrega_estimada'],"nro_orden"=>$res->fields["nro_orden"],"nro"=>$res->fields["nro"], "id_subir" => $res->fields['id_subir'],"nro_orden_cliente"=>$res->fields["numero"],"fin" => 0,"pagina"=>"control_seguimiento"))?>' target="_blank"> 
      <td align="center" <?=(($res->fields["fecha_inamovible"]==1)?"bgcolor='#FFCC00'":((($res->fields["fecha_estimada"])&&(date("Y-m-d") > $res->fields["fecha_estimada"]))?"bgcolor='#f34141'":"") )?>>
      <?=Fecha($res->fields["fecha_estimada"])?></td>   
   </a>
      
   <td align="center"><input type="checkbox" name="faltaoc" value=1 class="estilos_check" onclick="(this.checked)?valor=1:valor=0;window.open('control_seguimiento_proc.php?id_subir=<?=$id_subir?>&col=1&chk='+valor+'','','width=2,height=2')" <?if($res->fields['falta_oc']==1) echo 'checked'?>></td>
   <td align="center"><input type="checkbox" name="faltapm" value=1 class="estilos_check" onclick="(this.checked)?valor=1:valor=0;window.open('control_seguimiento_proc.php?id_subir=<?=$id_subir?>&col=2&chk='+valor+'','','width=2,height=2')" <?if($res->fields['falta_pm']==1) echo 'checked'?>></td>
   <td align="center"><input type="checkbox" name="faltaop" value=1 class="estilos_check" onclick="(this.checked)?valor=1:valor=0;window.open('control_seguimiento_proc.php?id_subir=<?=$id_subir?>&col=3&chk='+valor+'','','width=2,height=2')" <?if($res->fields['falta_op']==1) echo 'checked'?>></td>
   <td align="center"><input type="checkbox" name="nooc" value=1 class="estilos_check" onclick="(this.checked)?valor=1:valor=0;window.open('control_seguimiento_proc.php?id_subir=<?=$id_subir?>&col=4&chk='+valor+'','','width=2,height=2')" <?if($res->fields['no_llego_oc']==1) echo 'checked'?>></td>
   <td align="center"><input type="checkbox" name="todook" value=1 class="estilos_check" onclick="(this.checked)?valor=1:valor=0;window.open('control_seguimiento_proc.php?id_subir=<?=$id_subir?>&col=5&chk='+valor+'','','width=2,height=2')" <?if($res->fields['todo_ok']==1) echo 'checked'?>></td>
   
   <?  
   if  ($res->fields['ultima_modificacion_fecha']!=""){
	   $cant_dias_habiles=diferencia_dias_habiles(fecha($res->fields['ultima_modificacion_fecha']),date("d-m-Y"));   
   }
   if ($cant_dias_habiles>3){
   	$fondo_vence="#FF6600";
    $texto_vence=", Tiene mas de 3 Dias habiles de vencimiento";   	
   }
   else{
   	$fondo_vence="";
    $texto_vence="";   
   }
   ?>
   <td align="center" title="Ultima moficiación por: <?=$res->fields['ultima_modificacion_usuario'].$texto_vence?>" bgcolor='<?=$fondo_vence?>'>
           <?=Fecha($res->fields['ultima_modificacion_fecha'])?>
   </td>
   </tr>
   <? $res->MoveNext();
   }?>
</table>
<br>
<div align="center">
<input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
</div>
</form>