<?php
/*
$Author: enrique $
$Revision: 1.4 $
$Date: 2005/10/19 13:46:31 $
*/
require_once("../../config.php");

$observaciones=$_POST["observaciones"];
$id_componente_daniado=$parametros["id_componente_daniado"] or $id_componente_daniado=$_POST["id_componente_daniado"];
$producto=$_POST["producto"];
$proveedor=$_POST["proveedor"];


if ($_POST["rechazar"]){
           $db->starttrans();
           $usuario=$_ses_user["name"];
           $fecha=date("Y-m-d H:i:s");
           $tipo="eliminado";

           $sql=" update componentes_daniados set activo=0 where id_componente_daniado=$id_componente_daniado";
           sql($sql) or fin_pagina();
           $sql=" insert into log_componentes_daniados (id_componente_daniado,usuario,fecha,tipo)
                                              values ($id_componente_daniado,'$usuario','$fecha','$tipo')";
           sql($sql) or fin_pagina();
            if ($db->completetrans()) $msg="Se ha rechazado el componente con éxito";
                                  else $msg="No se ha rechazado el componente";


}


if ($id_componente_daniado && $parametros["pagina"]=='listado'){
$sql="select p.id_producto,p.desc_gral,cb.codigo_barra,id_proveedor,razon_social,
               log.usuario,log.fecha,componentes.observacion,componentes.id_componente_daniado,id_producto_dani
               from componentes_daniados  componentes
               join general.codigos_barra cb using(codigo_barra)
               left join log_componentes_daniados  log using(id_componente_daniado)
               join general.productos p using (id_producto)
               left join general.log_codigos_barra on(cb.codigo_barra=log_codigos_barra.codigo_barra)
               left join orden_de_compra using(nro_orden)
               left join proveedor using(id_proveedor)
               where componentes.id_componente_daniado=$id_componente_daniado
                ";

               $res=sql($sql) or fin_pagina();
               $producto=$res->fields["desc_gral"];
               $proveedor=$res->fields["razon_social"];
               $codigo_barra=$res->fields["codigo_barra"];
               $observacion=$res->fields["observacion"];
               $id_pro=$res->fields["id_producto_dani"];
               
               if($id_pro!="")
               {
			   $sql1="select descripcion from producto_especifico where id_prod_esp=$id_pro";
               $res1=sql($sql1) or fin_pagina();
               $pro_des=$res1->fields["descripcion"];
               }            
               }

if ( $_POST["traer_componente"]){
	   $codigo_barra=$_POST["codigo_barra"];
       $sql="select p.id_producto,p.desc_gral,cb.codigo_barra,id_proveedor,razon_social
               from general.productos p
               join general.codigos_barra cb using(id_producto)
               left join general.log_codigos_barra using(codigo_barra)
               left join orden_de_compra using(nro_orden)
               left join proveedor using(id_proveedor)
               where cb.codigo_barra = '$codigo_barra'";
      $result=sql($sql) or fin_pagina();
      
      $id_producto=$result->fields["id_producto"];
      $id_proveedor=$result->fields["id_proveedor"];
      $codigo_barra=$result->fields["codigo_barra"];
      $producto=$result->fields["desc_gral"];
      $proveedor=$result->fields["razon_social"];
}

if ($_POST["aceptar"] && !$id_componente_daniado) {
     //controlo que este insertado si esta aviso pero inserta igual
     
     $codigo_barra=$_POST["codigo_barra"];
     $sql="select codigo_barra from componentes_daniados where codigo_barra='$codigo_barra'";
     $res=sql($sql) or fin_pagina();
     
     if ($res->EOF){
	 //empieza la insecion
     $db->starttrans();
     $id_pro=$_POST["id_producto_dani"];
     $codigo_barra=$_POST["codigo_barra"];
     $id_producto=$_POST["id_producto"];
     $id_proveedor=$_POST["id_proveedor"];
     $observacion=$_POST["observacion"];
     $usuario=$_ses_user["name"];
     $fecha=date("Y-m-d H:i:s");
     $tipo="creacion";

     $sql="select nextval('componentes_daniados_id_componente_daniado_seq')
            as id_componente_daniado ";
     $res=sql($sql) or fin_pagina();
     $id_componente_daniado_new=$res->fields["id_componente_daniado"];

     $sql=" insert into componentes_daniados (id_componente_daniado,codigo_barra,observacion)
                                      values ($id_componente_daniado_new,'$codigo_barra','$observacion') ";
     sql($sql) or fin_pagina();
     $sql=" insert into log_componentes_daniados (id_componente_daniado,usuario,fecha,tipo)
                                          values ($id_componente_daniado_new,'$usuario','$fecha','$tipo')";
     sql($sql) or fin_pagina();
     if ($db->completetrans()) $msg="Se ha insertado el componente con éxito";
                          else $msg="No se ha insertado el componente";
	}
     else{
	 $msg="Este Elemento se Encuentra Insertado";
	}
}

//Modificacion
if ($_POST["aceptar"] && $id_componente_daniado) {
       
     $db->starttrans();
     $id_pro=$_POST["id_pro"];
     $pro_des=$_POST["prod"];
     $codigo_barra=$_POST["codigo_barra"];
     $id_producto=$_POST["id_producto"];
     $id_proveedor=$_POST["id_proveedor"];
     $observacion=$_POST["observacion"];
     $usuario=$_ses_user["name"];
     $fecha=date("Y-m-d H:i:s");
     $tipo="Modificación";

     if($pro_des!="")
     {
     	$sql=" update componentes_daniados set observacion='$observacion',id_producto_dani=$id_pro
                          where id_componente_daniado=$id_componente_daniado";
     	sql($sql) or fin_pagina();
     }
     else 
     {
     	$sql=" update componentes_daniados set observacion='$observacion' where id_componente_daniado=$id_componente_daniado";
     	sql($sql) or fin_pagina();	
     }
     $sql=" insert into log_componentes_daniados (id_componente_daniado,usuario,fecha,tipo)
                                          values ($id_componente_daniado,'$usuario','$fecha','$tipo')";
     sql($sql) or fin_pagina();
     if ($db->completetrans()) $msg="Se ha modificado el componente con éxito";
                          else $msg="No se ha modificado el componente";


}

if ($id_componente_daniado_new) $id_componente_daniado=$id_componente_daniado_new;

$link=encode_link("componentes_daniados.php",array("pagina"=>$parametros["pagina"]));
echo $html_header;
if ($msg) Aviso($msg);
echo "
     <script>
     var wproductos=0;
     function nuevo_item() {
              
			//pagina_prod='".encode_link('../ord_compra/seleccionar_productos.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>1))."';
			pagina_prod='".encode_link('../productos/mostrar_productos.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>1))."';
			if (wproductos==0 || wproductos.closed)
				wproductos=window.open(pagina_prod,'','toolbar=0,location=1,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');
		}
		
	 function cargar() {
			document.all.prod.value=wproductos.document.all.descripcion.value;
			document.all.id_pro.value=wproductos.document.all.id_producto.value;	
		}	
		
		
		</script>
		"
?>
<script>
   function cancelar(){
      document.form1.id_componente_daniado.value=0;
      document.form1.codigo_barra.value="";
      document.form1.producto.value="";
      document.form1.proveedor.value="";
      document.form1.observacion.value="";
      document.form1.traer_documentos_daniado.value="0";
   }//de la function
   

</script>

<form name=form1 method=post action="<?=$link?>">
<input type=hidden name="id_producto" value="<?=$id_producto?>">
<input type=hidden name="id_proveedor" value="<?=$id_proveedor?>">
<input type=hidden name="traer_productos_pagina" value="0">
<input type=hidden name="id_componente_daniado" value="<?=$id_componente_daniado?>">
<?
if ($id_componente_daniado){
    $sql=" select * from log_componentes_daniados where id_componente_daniado=$id_componente_daniado";
    $log=sql($sql) or fin_pagina();

?>
<div style="overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> "  >
 <table width=80% align=center>
   <tr>
      <td id=mo>Log</td>
   </tr>
   <tr>
     <td>
      <table width=100% align=center>
         <tr id=ma>
           <td>Usuario</td>
           <td>Tipo</td>
           <td>Fecha</td>
          </tr>
          <?
          for($i=0;$i<$log->recordcount();$i++){
              $fecha=fecha($log->fields["fecha"]);
              $hora=substr($log->fields["fecha"],10,10);
              $fecha=$fecha." ".$hora;
          ?>
           <tr>
              <td align=center><?=$log->fields["usuario"]?></td>
              <td align=center><?=$log->fields["tipo"]?></td>
              <td align=center><?=$fecha?></td>
           </tr>
          <?
          $log->movenext();
          }
          ?>
      </table>
     </td>
   </tr>
 </table>
</div>
 <?
}?>
 <table width=80% align=center class=bordes>
   <tr>
     <td id=mo>Componentes Dañados</td>
   </tr>
   <tr>
      <td width=1005 align=center>
        <table width=100% align=center>
          
        	<tr>
        	<td id=ma>Código Barra</td>
        		<td>
        		<input type=text name=codigo_barra value="<?=$codigo_barra?>" size=25 >&nbsp;&nbsp;
        		<input type="submit" name="traer_componente" value="Traer Componente">
        		</td>
        	</tr>
        
          <tr>
            <td id=ma>Producto</td>
            <td><input type=text name=producto value="<?=$producto?>" size=50 readonly></td>
          </tr>
          <tr>
            <td id=ma>Proveedor</td>
            <td><input type=text name=proveedor value="<?=$proveedor?>" size=50 readonly></td>
          </tr>
           <tr>
            <td id=ma>Producto espesifico</td>
            <td>
            <input type="hidden" name=id_pro value="<?=$id_pro?>">
            <input type=text name=prod value="<?=$pro_des?>" size=50>
            <input type=button name=agregar value='Buscar' onclick='nuevo_item();'></td>
          </tr>
          <tr>
            <td id=ma colspan=2>Observaciones</td>
          </tr>
          <tr>
            <td colspan=2>
            <textarea name=observacion rows=5 style="width=100%"><?=$observacion?></textarea>
            </td>
          </tr>

        </table>
      </td>
   </tr>
   <tr>
     <td align=center>
        <input type=submit name=aceptar value=Aceptar>
        &nbsp;
       <?
       if ($id_componente_daniado && $parametros["pagina"]=='listado')
          {
          $accion_cancelar=" onclick=\"document.location='listado_componentes_daniados.php'\"";
          }
          elseif ($parametros["pagina"]=="nuevo")
             {
              $accion_cancelar="onclick=\"window.close()\"";
             }
          else

          {
           $accion_cancelar=" onclick=\"cancelar();\"";
          }

       ?>
        <input type=button name="boton_cancelar" value="Cancelar" <?=$accion_cancelar?>>
       <?
       if ($id_componente_daniado && $parametros["pagina"]=='listado')
       {
       ?>
       &nbsp;
       <input type=submit name=rechazar value=Rechazar>
       <?
       }
       ?>
     </td>
   </tr>
 </table>
</form>
<?
echo fin_pagina();
?>