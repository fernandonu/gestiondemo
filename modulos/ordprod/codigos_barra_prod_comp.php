<?
/*
Autor: enrique
Fecha: 17/11/04

MODIFICADA POR
$Author: enrique $
$Revision: 1.15 $
$Date: 2006/05/18 11:40:07 $

*/
require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");
$numero=$parametros['id_prod'] or $numero=$_POST['id_prod'];
$pagina_ant=$parametros['pagina'] or $pagina_ant=$_POST['pagina'];
$pagina_ant1=$_POST['pagina1'];
$serie1=$parametros['serie'] or $serie1=$_POST['num_serie'];
$cant=$_POST['cant_hijos'] or $cant=15;
$esta_hijo=0;

/////Guardar codigos de barra ya existentes

if(($_POST["guardar"]=="Guardar")&&($pagina_ant1==1))
{  
   $db->StartTrans();     
   $control=1;
   $cantida_n =$_POST['cant_hijos'];
   $numero =$_POST['id_prod'];
   $ser1 =$_POST['ser'] or $ser1="";
   $chequeo2=0;
   $esta_hijo=0;
   $contar=1;
   $numero_serie=$_POST['num_serie'];
   $ir_pagina=1;
   $p=1;
   $pp=1;
   
   /*borro todos los codigos de barra de la tabla codigo_barra_prog*/
         $sql="delete from general.codigo_barra_prod where id_producto_compuesto=$numero";
         sql($sql,"No se Puede Eliminar") or fin_pagina();
   /*fin de borrar*/
         
   if($ser1!="")
   {
	   if($numero_serie!="")
	   {
	   $sel_numero="select nro_serie from ordenes.maquina where nro_serie='$numero_serie'";
	   $numero_ser=sql($sel_numero,"No se pudo recuperar el numero de serie") or fin_pagina();
	   if($numero_ser->RecordCount()>0) 
	   {
	   $update_serie="update productos_compuestos set nro_serie='$numero_serie' where id_producto_compuesto=$numero";
	   $up_serie=sql($update_serie,"no se pudo ingresar el numero de serie") or fin_pagina();
	   $coment="";
	   }
	   else 
	   {
	    $coment="El numero de serie es incorrecto";
        $ir_pagina=0;
	   }
	   }
	   else 
	   {	
	   $update_serie="update productos_compuestos set nro_serie='$numero_serie' where id_producto_compuesto=$numero";
	   $up_serie=sql($update_serie,"no se pudo ingresar el numero de serie") or fin_pagina(); 
	   }
   }
   if(($ser1=="")&&($numero_serie==""))
   {
   }
   else 
   {
	   $sel_numero="select nro_serie from ordenes.maquina where nro_serie='$numero_serie'";
	   $numero_ser=sql($sel_numero,"No se pudo recuperar el numero de serie") or fin_pagina();
	   if($numero_ser->RecordCount()>0) 
	    {
	    $update_serie="update productos_compuestos set nro_serie='$numero_serie' where id_producto_compuesto=$numero";
	    $up_serie=sql($update_serie,"no se pudo ingresar el numero de serie") or fin_pagina(); 
    }
   }
  

   while ($control<=$cantida_n)
         {
         
          $cod_bar=$_POST['cod_barra_n_'.$control];
          $cod_bar_prod=$_POST['cod_barra_prod_'.$control];
          
          /*inserto los codigos de barra*/
          if($cod_bar_prod!=""){
          	$sql="insert into general.codigo_barra_prod (codigo_barra_prod,id_producto_compuesto) VALUES ('$cod_bar_prod',$numero)";
          	sql($sql,"No se Puede Insertar") or fin_pagina();
          }
          /*fin de insertar*/
          
          if($cod_bar=="")
          {
          }
          else 
          {
          $select_hijo="select codigo_barra,id_producto_compuesto from codigos_barra where codigo_barra='$cod_bar'";   
          $controlh=sql($select_hijo) or fin_pagina();          
          if($controlh->RecordCount()==0) 
          {
   	      $esta_hijo=1;
   	      $arreglo[$contar]=$cod_bar;
   	      $contar++;
   	      $ir_pagina=0;
          }
   	      else
   	      {
   	      if($controlh->fields['id_producto_compuesto']=='')
   	      {	 
   	      $borrar_cod="update codigos_barra set id_producto_compuesto=$numero where codigo_barra='$cod_bar'";
          $barra=sql($borrar_cod,"no de ingresar el codigos de barra") or fin_pagina(); 
   	      
   	      }
   	      else
   	      {
   	     
   	      if($controlh->fields['id_producto_compuesto']!=$numero)
   	      {
   	      $repetido=1;
   	      $repe[$p]=$cod_bar;
   	      $p++;
   	      $ir_pagina=0;	
   	      }
   	      else 
   	      {
   	      $n_rep=1;
   	      $igual[$pp]=$cod_bar;
   	      $pp++;
   	      $ir_pagina=0;
   	      }	
   	      }
   	      }
          }
   	      $control++;
         }
          $fecha=fecha_db(date("d/m/Y",mktime()));
   	      $usuario=$_ses_user['name'];
   	      $log="modificacion";
   	      $campos1="(id_producto_compuesto,fecha,tipo_log,usuario)";	
   	      $insert="insert into log_productos_compuestos $campos1 VALUES ".
         "($numero,'$fecha','$log','$usuario')";
         sql($insert,"insertar el log_productos_contactos") or fin_pagina(); 
         if($ir_pagina==1)
         {
         $pagina_ant=0;
         $serie1='';
         $comen=1;	
         }
         else 
         {
         $pagina_ant=1;	
         }
 // $cant=0; 
 $db->CompleteTrans(); 
 } 

///Guardar para codicos de barra nuevos 

if(($_POST["guardar"]=="Guardar")&&($pagina_ant1==0)){  
   $db->StartTrans();     
   $control=1;
   $cantida_n =$_POST['cant_hijos'];
   $numero =$_POST['id_prod'];
   $chequeo2=0;
   $esta_hijo=0;
   $contar=1;
   $primero=1;
   $ir_pagina=1;
   $p=1;
   $pp=1;
   $datos=0;
         
         $q="select nextval('productos_compuestos_id_producto_compuesto_seq') as id_producto_compuesto";
         $res=sql($q, "Error la traer secuencia de id de producto_compuesto") or fin_pagina();
         $id_producto_compuesto=$res->fields['id_producto_compuesto']; 	
         $campos="(id_producto_compuesto)";	
         $insertar="insert into productos_compuestos $campos VALUES ".
         "($id_producto_compuesto)";
         sql($insertar,"no se pudo insertar en la tabla producto compuesto") or fin_pagina();
         //$primero=0;
         $inser_log=1;
         $datos=1;
         $repetido=0;
         
         /*borro todos los codigos de barra de la tabla codigo_barra_prog*/
         $sql="delete from general.codigo_barra_prod where id_producto_compuesto=$id_producto_compuesto";
         sql($sql,"No se Puede Eliminar") or fin_pagina();
         /*fin de borrar*/
         
         while ($control<=$cantida_n)
         {
          $cod_bar=$_POST['cod_barra_n_'.$control];
          $cod_bar_prod=$_POST['cod_barra_aux_'.$control];
          
          /*inserto los codigos de barra*/
          if($cod_bar_prod!=""){
          	$sql="insert into general.codigo_barra_prod (codigo_barra_prod,id_producto_compuesto) VALUES ('$cod_bar_prod',$id_producto_compuesto)";
          	sql($sql,"No se Puede Insertar") or fin_pagina();
          }
          /*fin de insertar*/
          
          
          
          if($cod_bar!=""){
	          $select_hijo="select codigo_barra,id_producto_compuesto from codigos_barra where codigo_barra='$cod_bar'";   
	          $controlh=sql($select_hijo) or fin_pagina();          
	          if($controlh->RecordCount()==0) 
	          {
		   	      $esta_hijo=1;
		   	      $arreglo[$contar]=$cod_bar;
		   	      $contar++;
		   	      $ir_pagina=0;
	          }
	   	      else{
		   	      if($controlh->fields['id_producto_compuesto']==null){	
			   	      $borrar_cod="update codigos_barra set id_producto_compuesto=$id_producto_compuesto where codigo_barra='$cod_bar'";
			          $barra=sql($borrar_cod,"no de ingresar el codigos de barra") or fin_pagina(); 
		   	      }
		   	      else{
			   	      if($contrloh->fields['id_producto_compuesto']!=$id_producto_compuesto){
				   	      $repetido=1;
				   	      $repe[$p]=$cod_bar;
				   	      $p++;
				   	      $ir_pagina=0;	
			   	      }
			   	      else{
				   	      $n_rep=1;
				   	      $igual[$pp]=$cod_bar;
				   	      $pp++;
				   	      $ir_pagina=0;
			   	      }	
		   	      }
   	      	  }
          }
          $control++;
          }
         
          $fecha=fecha_db(date("d/m/Y",mktime()));
   	      $usuario=$_ses_user['name'];
   	      $log="de creacion";
   	      $campos1="(id_producto_compuesto,fecha,tipo_log,usuario)";	
   	      $insert="insert into log_productos_compuestos $campos1 VALUES ".
          "($id_producto_compuesto,'$fecha','$log','$usuario')";
          sql($insert,"insertar el log_productos_contactos") or fin_pagina();
         
          //$cant=0; 
          $numero=$id_producto_compuesto;
          $numero_serie=$_POST['num_serie'];
          $serie1=$numero_serie;
          if($numero_serie=="")
          {
   	       
		   }
		   else 
		   {
		   $sel_numero="select nro_serie from ordenes.maquina where nro_serie='$numero_serie'";
		   $numero_ser=sql($sel_numero,"No se pudo recuperar el numero de serie") or fin_pagina();
		   if($numero_ser->RecordCount()>0) 
		    {
		    $update_serie="update productos_compuestos set nro_serie='$numero_serie' where id_producto_compuesto=$numero";
		    $up_serie=sql($update_serie,"no se pudo ingresar el numero de serie") or fin_pagina(); 
		    }
		   }
 
 if($ir_pagina==1)
 {
 $pagina_ant=0;
 $serie1='';
 $comen=1;	
 }
 else 
 {
 $pagina_ant=1;	
 }
 $db->CompleteTrans(); 
 }  

 
///Borrar codigos ya existentes 
 
if(($_POST["borrar"]=="B")&&($pagina_ant==1))
{     
  $barra=$_POST['cod_borrar'];
  $numero=$_POST['id_prod'];
  
  $borrar_cod="update codigos_barra set id_producto_compuesto=null where codigo_barra='$barra' and id_producto_compuesto=$numero";
  $barra=sql($borrar_cod,"no de pudo dar de baja los codigos de barra") or fin_pagina();
  
}
if(($_POST["borrar_prod"]=="Borrar")&&($pagina_ant==1))
{     
  $barra_prod=$_POST['cod_borrar_prod'];
  $numero=$_POST['id_prod'];
    
  $borrar_cod="delete from general.codigo_barra_prod where codigo_barra_prod='$barra_prod' and id_producto_compuesto=$numero";
  $barra=sql($borrar_cod,"no de pudo dar de baja los codigos de barra") or fin_pagina();

}

if($_POST["borrar_todos"]=="Eliminar Producto Compuesto"){  
  $numero=$_POST['id_prod'];
  $borrar_cod="update codigos_barra set id_producto_compuesto=null where id_producto_compuesto=$numero";
  $barra=sql($borrar_cod,"no de pudo dar de baja los codigos de barra") or fin_pagina();
  
  $sql="delete from general.codigo_barra_prod where id_producto_compuesto=$numero";
  sql($sql,"No se Puede Eliminar") or fin_pagina();
  
  $borrar_prod_comp="delete from general.productos_compuestos where id_producto_compuesto=$numero";
  $barra=sql($borrar_prod_comp,"no de pudo dar de baja los codigos de barra") or fin_pagina();
  
  $accion="Se Elimino el Producto Compuesto con ID $numero";
  $link=encode_link('productos_compuestos.php',array("accion"=>$accion));
  header("Location:$link") or die("No se encontró la página destino");
}

echo $html_header;

echo $msg."<br>";
?>
<script>
 var cont;

function anular_enter()
{ 
  if(typeof(eval("document.all.cod_barra_n_"+document.all.indice_actual.value))!="undefined")	
  {var campo=eval("document.all.cod_barra_n_"+document.all.indice_actual.value);
   campo.focus();
  }

}

function alProximoInput(elmnt,content,next)
{
  if (content.length==elmnt.maxLength)
	{
	  
	  if (typeof(next)!="undefined")
		{
		  next.readOnly=0;
		  next.focus();
		  //document.all.mensaje.value="Ingrese el código de barras a hermanar";
		}
	  else
	  {
	  
	   document.all.guardar.focus();	
	  } 
	}
}

function controles()
{var aux=1;
 var chequeo=0;
 var retorno="true";
 var alerto="";
 alerto+="--------------------------------------------------------------\n" ;  	
 while (aux<=cont)
       {texto=eval("document.all.cod_barra_hijo_"+aux);
        if (texto.value=="")
           {alerto+="Debe ingresar el valor del Código de barra Hijo Nº "+aux+"\n";           
            retorno="false";
            chequeo=1;
           }
        aux++;
       }
 
 if (chequeo) 
    {alerto+="--------------------------------------------------------------\n";  
   	 alert(alerto);
    } 
           
 if (retorno=="false") return false;      	   
 else return true;  
 
}

function control_num_series_nuevo(){
	var i=1;
	var condicion;
	while (typeof(eval("document.all.cod_barra_aux_"+i))!="undefined")	{
		condicion=eval("document.all.cod_barra_aux_"+i+".value.length");
		if (condicion==9){
			alert ("El Número de Serie "+i+" puede ser un Void. VERIFIQUE!!!");
		}
		i++;
	}
return 0;	
}

function control_num_series_mod(){
	var i=1;
	var condicion;
	while (typeof(eval("document.all.cod_barra_prod_"+i))!="undefined")	{
		condicion=eval("document.all.cod_barra_prod_"+i+".value.length");
		if (condicion==9){
			alert ("El Número de Serie "+i+" puede ser un Void. VERIFIQUE!!!");
		}
		i++;
	}
return 0;	
}

</script>

<?
if($pagina_ant==1)
{
?>
<form name="form1" method="POST" action="codigos_barra_prod_comp.php" onkeypress="if(getkey(event)==43) document.all.guardar.click();">
 <input type='hidden' name='pagina' value='1'>
 <input type='hidden' name='pagina1' value='1'>
 <input type="hidden" name="indice_actual" value="">
 <input type='hidden' name='id_prod' value='<?=$numero?>'>
 <input type='hidden' name='ser' value='<?=$serie1?>'>
 <table width="100%" align="center">
  
  <tr>
   <td align="center">
    <!--boton para anular el enter -->
    <input type="submit" name="oculto" value="" onclick=" anular_enter();return false;" style="width:0px"> 
    <input type="submit" name="guardar" value="Guardar" onclick="return control_num_series_mod()">
    &nbsp;&nbsp;
    <font color="Green" size="2"><b>Para Guardar presioné la tecla "+" o el botón Guardar</b></font> 
    &nbsp;&nbsp;
    <input type="button" name="volver" value="Volver" align="left" onclick="document.location='productos_compuestos.php'">
    </td>
  </tr> 
  
  <tr>
  	<td align="center" colspan="2">
  		<? if (permisos_check('inicio','ordprod_eliminar_prod_compuesto')){?>
  		<br><input type="submit" name="borrar_todos" value="Eliminar Producto Compuesto">
  		<?}?>
  	</td>
  </tr>
  
  </table> 
  
<table width="95%" align="center" class="bordes">
<tr>
<td>

<table width="100%" align="center">
  <tr>
	  <td id="ma_sf" align="center"><font color="Red">
	  <?=$coment?></font>
	  </td>
  </tr>
  <tr align="center">
	   <td id="ma_sf" align="center" class="bordes">
	    Códigos de Barras Productos Compuestos
	   </td>
  </tr>
</table>
     
   <table width="100%" align="center">
	   <tr align="center">
	      <td class="bordes">      
	       <b>Cantidad de campos: </b>	
	        <select name="cant_hijos" id="cant_hijos" onchange="beginEditing(this)">
	        <option selected value="<?=$cant?>"><?=$cant?></option>
	        <option value="15">15</option>
	        <option value="30">30</option>
	        <option value="50">50</option>
	        <option value="100">100</option>
	        <option value="500">500</option>
	         <option id=editable>Edite aqui</option>
	       </select>
	       <input type="submit" name=aceptar value="Aceptar">
	       &nbsp;&nbsp;&nbsp;&nbsp;  
	       <b>Numero de Serie:</b>
		   <input type="text" name="num_serie" size="18" value="<?=$serie1?>">
		  </td>
	  </tr> 
	    
  </table>

 <table width="100%" align="center">  
  <tr>
    <td>
    	<table width="100%" align="center"class="bordes">
   		   
    		  <tr>
    			<td id="ma_sf" align="center" colspan="2" class="bordes">
    				Columna para Ingresar los <font color="Red" size="2">Void de Coradir</font>
    			</td>
      		  </tr>  
    		  <?	
		      $sql_codigos="select codigo_barra, producto_especifico.descripcion, productos.desc_gral
		      				from general.codigos_barra 
		      				left join general.productos using (id_producto)
		      				left join general.producto_especifico using (id_prod_esp)
		      				where id_producto_compuesto=$numero";     
		      $sql_cod=sql($sql_codigos,"no se pudo recuperar los codigos de barra") or fin_pagina();
		      $j=1;
		      while(!$sql_cod->EOF)
		      {
		      ?>
		      <tr>
			      <td>
			        <b>Nº <?=$j?>  </b>
			      
				      <input type="text" maxlength="9"  size="12" name="cod_barra_<?=$j?>" value="<?=$sql_cod->fields['codigo_barra']?>" readonly>      
				      <input type="submit" name="borrar" value="B" style="width:20" onclick="
				  																			if(confirm('Se borrará el código de barra <?=$sql_cod->fields['codigo_barra']?> del sistema.\n¿Está seguro?'))
				  																			{document.all.cod_borrar.value='<?=$sql_cod->fields['codigo_barra']?>';
				  																			 return true;
				  																			}
				  																			else
				  																			 return false;
				  	">
				      <?
				      if ($sql_cod->fields['descripcion']!='') $descripcion_producto=$sql_cod->fields['descripcion'];
				      else $descripcion_producto=$sql_cod->fields['desc_gral']
				      ?>
				      <input type="text" size="60"  name="desc" value="<?=$descripcion_producto?>" readonly>      
				<?
				$j++;
				$sql_cod->MoveNext();	
				?>
				  </td>
			  </tr>
				<?}?>
	    
    <?if ($esta_hijo==1){
    ?>
      <tr>
	      <td colspan="2" align="center">
	      <font size="2" color="Red"><b>Los siguientes códigos de barra no están en el sistema</b></font>
	      </td>      
      </tr> 
      <?
       $i=1;
       while ($i<$contar)
      {//$orden="cod_barra_hijo_$i"; 
       $third_par="cod_barra_n_".($j); 
       $foco=$j;    
      ?>	  
     <tr>
	     <td>   
	     	<b>Código de Barras Nº <?=$j?>  </b>
	     
		     <input type="text" maxlength="9" name="cod_barra_n_<?=$j?>" class="text_9" value="<?=$arreglo[$i]?>" onchange="document.all.indice_actual.value=<?=$j+1?>" onkeyup="alProximoInput(this,this.value,<?=$third_par?>);">
		     <input type="button" name="limpiar_<?=$j?>" value="Limpiar" onclick="document.all.cod_barra_n_<?=$j?>.value=''">
	     </td>
     </tr>     
     <?
     $j++;
     $i++;
      }
     }
    if ($repetido==1){
    ?>
      <tr>
	      <td colspan="2" align="center">
	      	<font size="2" color="Red"><b>Ya existen productos compuestos con este código de barra</b></font>
	      </td>      
      </tr> 
      <?
       $i=1;
       while ($i<$p)
      {//$orden="cod_barra_hijo_$i"; 
       $third_par="cod_barra_n_".($j);     
       $foco=$j;
      ?>	  
     <tr>
	     <td>   
	     	<b>Código de Barras Nº <?=$j?>  </b>
	      
		     <input type="text" maxlength="9" name="cod_barra_n_<?=$j?>" class="text_9" value="<?=$repe[$i]?>" onchange="document.all.indice_actual.value=<?=$j+1?>" onkeyup="alProximoInput(this,this.value,<?=$third_par?>);">
		     <input type="button" name="limpiar_<?=$j?>" value="Limpiar" onclick="document.all.cod_barra_n_<?=$j?>.value=''">
	     </td>
     </tr>     
     <?
     $j++;
     $i++;
      }
     }
      if ($n_rep==1) 
    {
    ?>
      <tr>
	      <td colspan="2" align="center">
	      	<font size="2" color="Red"><b>No puede ingresar el mismo código de barra más de una vez</b></font>
	      </td>      
      </tr> 
      <?
       $i=1;
       while ($i<$pp)
      {//$orden="cod_barra_hijo_$i"; 
       $third_par="cod_barra_n_".($j);     
      ?>	  
     <tr>
	     <td>   
	     	<b>Código de Barras Nº <?=$j?>  </b>
	      
		     <input type="text" maxlength="9" name="cod_barra_n_<?=$j?>" class="text_9" value="<?=$igual[$i]?>" onchange="document.all.indice_actual.value=<?=$j+1?>" onkeyup="alProximoInput(this,this.value,<?=$third_par?>);">
		     <input type="button" name="limpiar_<?=$j?>" value="Limpiar" onclick="document.all.cod_barra_n_<?=$j?>.value=''">
	     </td>
     </tr>     
     <?
     $j++;
     $i++;
      }
     }
     $i=1;
     while ($j<=$cant){//$orden="cod_barra_hijo_$i";
     if($foco=="")
     $foco=$j;?>	  
     <tr>
	     <td>   
	     	<b>Código de Barras Nº <?=$j?>  </b>
	    
     <?if ($cant==$j){?>
     <input type="text" maxlength="9" name="cod_barra_n_<?=$j?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" onchange="document.all.indice_actual.value=<?=$j+1?>" onkeyup="alProximoInput(this,this.value,document.all.guardar);" >
     <? 
     }
     else{?> 
     		<input type="text" maxlength="9" name="cod_barra_n_<?=$j?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" onchange="document.all.indice_actual.value=<?=$j+1?>" onkeyup="alProximoInput(this,this.value,cod_barra_n_<?=$j+1?>);" >
     <?}?>
       		<input type="button" name="limpiar_<?=$j?>" value="Limpiar" onclick="document.all.cod_barra_n_<?=$j?>.value=''">
     	</td>
     </tr>
      <?
       $j++;
       $i++;
      }?>
  
     <input type='hidden' name='cod_borrar' value=''>
     <input type='hidden' name='cantidad_barra' value='<?=$j?>'>
     <input type='hidden' name='cantidad_nuevos' value='<?=$i?>'>
	</table>
	   
 </td>  
<?/**************************empieza numeros de serie*******************************/?>
  
 <td>
   <table width="100%" align="center" class="bordes">
   	  
 	  <tr>
    	<td id="ma_sf" align="center" colspan="2" class="bordes">
    		Columna para Ingresar los <font color="Red" size="2">Números de Serie</font>
    	</td>
      </tr>  
   	  <?	
      $sql="select * from general.codigo_barra_prod where id_producto_compuesto=$numero";     
      $res_cod=sql($sql,"no se pudo recuperar los codigos de barra") or fin_pagina();
      $j=1;
      $res_cod->MoveFirst();
      while($j<=$cant){
      ?>
      <tr>
	      <td>
	        <b>Número de Serie <?=$j?></b>
	      </td>  
	      <td>  
		      <?if ($res_cod->fields['codigo_barra_prod']){?>
			      <input type="text" name="cod_barra_prod_<?=$j?>" value="<?=$res_cod->fields['codigo_barra_prod']?>" readonly>      
			      <input type="submit" name="borrar_prod" value="Borrar" style="width:63" onclick="
			  																			if(confirm('Se borrará el código de barra <?=$res_cod->fields['codigo_barra_prod']?> del sistema.\n¿Está seguro?'))
			  																			{document.all.cod_borrar_prod.value='<?=$res_cod->fields['codigo_barra_prod']?>';
			  																			 return true;
			  																			}
			  																			else
			  																			 return false;">
		      <?}
		      else{
		      ?>
		      	<input type="text" name="cod_barra_prod_<?=$j?>" size="20" value="" >
		     	<input type="button" name="limpiar_prod<?=$j?>" value="Limpiar" onclick="document.all.cod_barra_prod_<?=$j?>.value=''">
		      <?
		      }//del else
		      ?>
      	  </td>
     </tr>
     <?
      $j++;
      $res_cod->MoveNext();	
      }
     ?>
   </table>
     <input type='hidden' name='cod_borrar_prod' value=''>
     <input type='hidden' name='cantidad_barra_prod' value='<?=$j?>'>
 </td>
<?/**************************termina numeros de serie*******************************/?>

 </tr>
</table>

</td>
</tr>   
</table>
</from>
<?
}
else{ // va por aca si es ingresa in producto especifico nuevo *****************
?>
<form name="form1" method="POST" action="codigos_barra_prod_comp.php" onkeypress="if(getkey(event)==43) document.all.guardar.click();">
<input type='hidden' name='pagina1' value='0'>
 

<table width="100%" align="center">
  <tr>
   <td align="center">
   <input type="hidden" name="indice_actual" value="1">
    <!--boton para anular el enter -->
    <input type="submit" name="oculto" value="" onclick="anular_enter();return false;" style="width:0px"> 
    <input type="submit" name="guardar" value="Guardar" onclick="control_num_series_nuevo()">
    &nbsp;&nbsp;
    <font color="Green" size="2"><b>Para Guardar presioné la tecla "+" o el botón Guardar</b></font> 
    &nbsp;&nbsp;
    <input type="button" name="volver" value="Volver" onclick="document.location='productos_compuestos.php'">
    </td>
  </tr> 
 </table> 
 <?
if($comen==1)
{
?>
<table width="65%" align="center" class="bordes">
  
  <tr align="center">
   <td id="ma_sf" align="center">
   <font size="3" color="Green"><b> El Producto Compuesto se Ingreso Correctamente</b> </font>
   </td>
  </tr>
</table>  
<?
}	
?>
 <table width="90%" align="center" class="bordes">
  
  <tr align="center">
   <td id="ma_sf" align="center" colspan="3" class="bordes">
    Nuevo Producto Compuesto
   </td>
  </tr>
  
       
     <tr>
      <td colspan="2" align="center" class="bordes">      
       <b>Cantidad de Campos</b>
      
        <select name="cant_hijos" id="cant_hijos" onchange="beginEditing(this)">
        <option selected value="<?=$cant?>"><?=$cant?></option>
        <option value="15">15</option>
        <option value="30">30</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="500">500</option>
        <option id=editable>Edite aqui</option>
       </select>     
       <input type="submit" name=aceptar value="Aceptar">
	   &nbsp;&nbsp;&nbsp;&nbsp; 
       <b>Nro Serie</b><input type="text" size="18" name="num_serie" size="2" value="<?=$serie1?>">
      </td>
     </tr> 
       
  <tr>
   <td>
    <table width="100%" class="bordes">
    
    <tr>
    	<td id="ma_sf" align="center" colspan="2" class="bordes">
    		Columna para Ingresar los <font color="Red" size="2">Void de Coradir</font>
    	</td>
    </tr>
    
     <?if ($chequeo2==1) {?>
       <tr>
        <td colspan="3" align="center">
         <font size="2" color="Red"><b>Los Códigos en Rojo ya están en el sistema</b></font>
       </td>      
      </tr>
      <?}      
      $i=1;
       while ($i<=$cant)
      {if($foco=="")
      $foco=$i;      
      $orden="cod_barra_hijo_$i";       
      ?>	  
     <tr>
      <td>   
       <b>Código de Barras Nº <?=$i?>  </b>
      </td>
      <td> 
      
      <?if ($cant==$i) 
           {?><input type="text" maxlength="9" name="cod_barra_n_<?=$i?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" onchange="document.all.indice_actual.value=<?=$i+1?>" onkeyup="alProximoInput(this,this.value,document.all.guardar);" >
      <? }else 
          {?> <input type="text" maxlength="9" name="cod_barra_n_<?=$i?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" onchange="document.all.indice_actual.value=<?=$i+1?>" onkeyup="alProximoInput(this,this.value,cod_barra_n_<?=$i+1?>);" ><?}?>
              <input type="button" name="limpiar_<?=$i?>" value="Limpiar" onclick="document.all.cod_barra_n_<?=$i?>.value=''">
          </td>
     </tr>
      <?
       $i++;
      }
     ?>
    </table>
   </td>
  
<?/***************************** Numeros de Serie *****************************************/?>
   <td>
    <table width="100%" class="bordes">
    
    <tr>
    	<td id="ma_sf" align="center" colspan="2" class="bordes">
    		Columna para Ingresar los <font color="Red" size="2">Números de Serie</font>
    	</td>
    </tr>
     
      <td colspan="3">
      
     <?
      $i=1;
       while ($i<=$cant)
      {if($foco=="")
      $foco=$i;      
      $orden="cod_barra_hijo_$i";       
      ?>	  
     <tr>
      <td>   
       <b>Número de Serie <?=$i?></b>
      </td>
      <td> 
      
      <?if ($cant==$i) 
           {?><input type="text" name="cod_barra_aux_<?=$i?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" >
      <? }else 
          {?> <input type="text" name="cod_barra_aux_<?=$i?>" <?if ($arreglo[$i]==1) {?>class="text_9" <?}?> size="20" value="<?=$_POST[$orden]?>" ><?}?>
              <input type="button" name="limpiar_<?=$i?>" value="Limpiar" onclick="document.all.cod_barra_aux_<?=$i?>.value=''">
          </td>
     </tr>
      <?
       $i++;
      }
     ?>
    </table>
   </td>
   <?/*********************fin de Numeros de serie**********************************/?>
   
  </tr> 
  
 </table>
 <input type='hidden' name='cantidad_nuevos' value='<?=$i?>'>
 
 <script> 
   cont=document.all.cant_hijos.value;  
 </script>
</from>
<?
}
?>
<script>
   if(typeof(eval("document.all.cod_barra_n_"+<?=$foco?>))!="undefined")	  
   document.all.cod_barra_n_<?=$foco?>.focus();  
 </script>
</body>
</html>
<?fin_pagina();?>