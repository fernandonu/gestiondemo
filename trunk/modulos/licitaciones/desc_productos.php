<?
/*
Author: Cesar

MODIFICADA POR
$Author: ferni $
$Revision: 1.60 $
$Date: 2006/04/26 19:57:45 $
*/
include("../../config.php");
$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"];
$pagina_viene=$parametros["pagina_viene"] or $pagina_viene=$_POST["pagina_viene"];


if ($id_producto)
      $aviso="";
      else {
      $aviso="<font size='2' color='#3333FF'>
               PARA INGRESAR LA DESCRIPCIÓN DE UN PRODUCTO DEBE PRESIONAR<BR>
               EL BOTÓN 'ELEGIR PRODUCTOS'
               Y ELEGIR EL PRODUCTO DESEADO
               </font>
               ";
      }


//guarda las descripciones nuevas
if ($_POST["guardar_desc"]){
          $cantidad_titulos=$_POST["cantidad_titulos"];
          for($i=0;$i<$cantidad_titulos;$i++)
                 {
                  //if($_POST["chk_$i"]){
                        $titulo=$_POST["titulo_$i"];
                        $contenido=$_POST["contenido_$i"];
                        $contenido=ereg_replace("'","\'",$contenido);
                        $sql=" update descripciones set contenido='$contenido' where id_producto=$id_producto and titulo='$titulo'";
                        $sql_array[]=$sql;
                    //    }//del  if($_POST["chk_$i"])
                 }//del for
           //inserto el nuevo contenido
           $contenido=$_POST["contenido"];
           $contenido=ereg_replace("'","\'",$contenido);
           $titulo=$_POST["text_titulo"];
           if ($titulo!="") {

                          $sql="select count(id_producto) as cantidad from descripciones
                                where id_producto=$id_producto and titulo='$titulo'";
                          $res=sql($sql) or fin_pagina();
                          if ($res->fields["cantidad"]>0){
                                      error("Ya existe ese titulo para el producto");
                                      }
                          if (!$error){
                                 $sql="insert into descripciones (id_producto,titulo,contenido) values ($id_producto,'$titulo','$contenido')";
                                 $sql_array[]=$sql;
                           }
                         }// del if ($titulo!="")

    if (sizeof($sql_array)>0)
                        sql($sql_array) or fin_pagina();
    }//del if de guardar_desc

if ($_POST["eliminar_desc"]){
          $cantidad_titulos=$_POST["cantidad_titulos"];
          for($i=0;$i<$cantidad_titulos;$i++)
                 {
                  if($_POST["chk_$i"]){
                        $titulo=$_POST["titulo_$i"];
                        $contenido=$_POST["contenido_$i"];
                        $sql=" delete from  descripciones where id_producto=$id_producto and titulo='$titulo'";
                        $sql_array[]=$sql;
                        }//del  if($_POST["chk_$i"])
                 }//del for
    if (sizeof($sql_array)>0)
                        sql($sql_array) or fin_pagina();

}//del if ($_POST["eliminar_desc"])
echo $html_header;
?>
<script>
var titulo_pagina;
 var wrecibir_prod=new Object();
	wrecibir_prod.closed=1;
</script>
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/checkform.js"></script>
<script src="../../lib/NumberFormat150.js"></script>

<script LANGUAGE=VBScript TYPE="text/vbscript">
Function makeMsgBox(title,mess,icon,buts,defbut,mods)
   butVal = buts + (icon*16) + (defbut*256) + (mods*4096)
   makeMsgBox = MsgBox(mess,butVal,title)
End Function

Function makeInputBox(title,pr,def)
   makeInputBox = InputBox(pr,title,def)
End Function
</script>
<script>
function elegir_producto(){
	   var producto=eval("document.all.producto");
	   var id_producto=eval("document.all.id_producto");
	   producto.value=wrecibir_prod.document.all.nombre_producto_elegido.value;
	   id_producto.value=wrecibir_prod.document.all.id_producto_seleccionado.value;
	   wrecibir_prod.close();
	   document.form1.submit();
	}//de function elegir_producto_recibido(id_fila)

function control_datos()
   {
   	if (document.all.id_producto.value==''){
   		alert("Debe elegir un producto para guardar la descripción");
      return false;
   	}
   //si ingreso un titulo controlo que haya ingresado una descripcion
   if(document.form1.text_titulo.value!='')
        {
         if (document.form1.contenido.value==''){
         alert("Debe ingresar un contenido para el titulo ingresado");
         return false;
         }//del segundo if
        }
   //si ingreso una descripcion controlo que haya ingresado un titulo
   if(document.form1.contenido.value!='')
        {
         if (document.form1.text_titulo.value==''){
         alert("Debe ingresar un contenido para el titulo ingresado");
         return false;
         }//del segundo if
        }
 return true;
}

function deshacer(){
    document.all.contenido.value="";
    document.all.text_titulo.value="";
}
var wproductos;
var wtitulos;
function cargar_producto(){
   document.form1.id_producto.value=wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].value;
   document.form1.submit();
   wproductos.close();
}

//funcion que me agrega los productos
function agregar() {

    if (typeof(wproductos.document.all.radio_idprod.length)!='undefined')
         {
          for(i=0;i<wproductos.document.all.radio_idprod.length;i++){
               if (wproductos.document.all.radio_idprod[i].checked)
                               {
                               document.form1.id_producto.value=wproductos.document.all.radio_idprod[i].value;
                               break;
                               }
          }
         }
         else
         {
         document.form1.id_producto.value=wproductos.document.all.radio_idprod.value;
         }
   document.form1.submit();
   wproductos.close();
}
function elegir_titulo(){
         document.form1.text_titulo.value=wtitulos.document.all.titulo_elegido.value;
//         window.close();
}


</script>
<?php
include("../ayuda/ayudas.php");
?>
<form name="form1" method="post" action="desc_productos.php">
<input type=hidden name="id_producto" value="<?=$id_producto?>">
<input type=hidden name="pagina_viene" value="<?=$pagina_viene?>">
<?php
$query1="select distinct codigo,descripcion from  tipos_prod order by descripcion";
$result1= $db->Execute($query1) or die ($db->ErrorMsg());
$link=encode_link("elegir_producto.php",array("onclickcargar"=>"window.opener.cargar_producto()"));
if ($aviso != ""){
?>
<br>
<table align='center' class='bordes' cellpadding=2>
   <tr  bgcolor=<?=$bgcolor3?>><td><?=$aviso?></td></tr>
</table>
<?
}
?>
<br>
<table width="50%" border="0" id="ma" class="bordes" align=center>
    <tr>
      <td>Elegir Producto</td>
      <td>
       <?
       $link=encode_link('../productos/listado_productos.php',array('pagina_viene'=>'desc_productos.php','onclick_cargar'=>"window.opener.elegir_producto()",'cambiar'=>0));
       ?>
       <input type=button name=agregar_productos value="Elegir Producto" onclick=" if(wrecibir_prod.closed)
       		wrecibir_prod=window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=10,top=10,width=700,height=500');
          else wrecibir_prod.focus();
       ">
      </td>
      <td align='right'>
      <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_descripcion.htm" ?>', 'CARGAR DESCRIPCION TECNICA')" >
      </td>
    </tr>
</table>
<hr>
<?php
 if ($id_producto){
    $sql="select desc_gral from productos where id_producto=$id_producto";
    $res=sql($sql) or fin_pagina();
    $desc_gral=$res->fields["desc_gral"];
 }
 ?>
 <table border="0" width=100% align=center>
           <tr id=mo>
             <td colspan="2">
              Datos del Producto
              </td>
           </tr>
           <tr id=ma>
	   	   <td width="10%">
	   			Descripción General
	   		</td>
	     	<td>
	     	    <?if ($pagina_viene=="detalle_producto_general"){?>
	     	    	<input type="text" name="producto" value="<?=$desc_gral?>" style="width:100%" class="text_4">
	     	    <?}
	     	    else{?>
	     			<input type="text" name="producto" value="<?=$_POST["producto"]?>" style="width:100%" class="text_4">
	     		<?}?>
	     	</td>
           </tr>
         </table>
         <br>
         <table border=0  width=100% align=center>
           <tr id=mo>
              <td colspan=3>Descripciones</td>
           </tr>
           </tr>
           <tr id=ma>
            <td width=1%>&nbsp;</td>
            <td width=15%>Titulo</td>
            <td width=85%>Contenido</td>
           </tr>

 <?
 if ($id_producto)
           {
           $query="SELECT descripciones.titulo,descripciones.contenido
                   from productos
                   join descripciones using(id_producto)
                   where productos.id_producto=$id_producto
                   order by descripciones.titulo
                   ";
           $result= $db->Execute($query) or die ($db->ErrorMsg());

         if($result->recordcount()>0)
           {?>
           <input type=hidden name=cantidad_titulos value="<?=$result->recordcount();?>">
            <?
            for($i=0;$i<$result->recordcount();$i++)
             {
             ?>
             <tr bgcolor="<?=$bgcolor2?>">
                <td><input type=checkbox name=chk_<?=$i?> value="1" class='estilos_check'></td>
                <td align=center>
                   <input type=text name="titulo_<?=$i?>" value="<?=$result->fields["titulo"];?>" readonly class="text_7">


                 </td>
                 <td valign=top>
                   <textarea name="contenido_<?=$i?>" rows=5 style="width:100%" class="estilos_textarea"><?echo $result->fields["contenido"];?></textarea>
                 </td>
           </tr>
            <?
            $result->MoveNext();
            }//del for
         }//del if

}     //del if de id_producto
?>
         <tr bgcolor="<?=$bgcolor2?>">
         <td colspan=2 align=center>
           <font color=red>
           <?
           //link a la pagina para elegir productos
           $link=encode_link("titulo_desc_prod.php",array("onclickcargar"=>"window.opener.elegir_titulo()"));
           ?>
           <input type=button name=elegir_titulo value="Elegir Titulo" onclick="wtitulos=window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=10,top=10,width=750,height=500')">
           </font>
           <br>
           <br>
         <input type=text name="text_titulo" value="<?if ($error) echo $_POST["text_titulo"]?>" size=20 class="text_7" readonly>
         </td>
         <td valign=top>
           <textarea name="contenido" rows=5 style="width:100%" class="estilos_textarea"><?if ($error) echo $_POST["contenido"]?></textarea>
         </td>
         </tr>
         <tr>
            <td colspan=3 align=center>
                <table width=100% align=center>
                  <tr>
                     <td width=33% align=center>
                     <input type=submit name="guardar_desc" value="Guardar Descripción" style="width:70%" onclick="return control_datos()">
                     </td>
                     <td width=33% align=center>
                     <input type=submit name="eliminar_desc" value="Eliminar" style="width:70%">
                     </td>
                     <td width=33% align=center>
                     <input type=button name="deshacer_desc" value="Deshacer" style="width:70%" onclick="deshacer();">
                     </td>
                     <?if ($pagina_viene=="detalle_producto_general"){?>
	                     <td width=33% align=center>
	                     <?$ref=encode_link("../productos/detalle_producto_general.php",array("id_producto"=>$id_producto));?>
	                     <input type=button name='cerrar_ventana' value='Cerrar Ventana'onclick="window.opener.location.href='<?=$ref?>';window.close();">
	                     </td>
                     <?}?>

                  </tr>
                </table>
            </td>
         </tr>
  </table>
</form>
<?
fin_pagina();
?>