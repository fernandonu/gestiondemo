<?php
  /*
$Author: nazabal $
$Revision: 1.34 $
$Date: 2006/02/17 18:43:43 $
*/
include("../../config.php");

echo $html_header;
?>
<style type="text/css">
<!--
a {
	cursor: hand;text-decoration:none;
	color: #006699;
}
-->
</style>
<?php
include("../ayuda/ayudas.php");
?>


</head>
<body bgcolor="#E0E0E0">

<div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/productos/ayuda_listprov.htm" ?>', 'LISTA DE PROVEDORES')" >
    </div>
<center>

<?php
/*
$filtro = array(
	"" => "Razon Social",
	"" => "Contacto",
	"" => "Telefono",
	"" => "Mail",
	"" => "Observaciones",
	"" => "C.U.I.T.",
	"" => "Domicilio"
);
*/
$rs="";
$c="";
$tel="";
$mail="";

if($_POST['tbuscar'])
{
 $buscado=$_POST["tbuscar"];
}
else
 $buscado=$parametros["texto_buscado"]; 

switch($_POST['select_en'])
{case "Razon Social":$rs="selected";
                      break;
 case "Contacto":$c="selected";
                      break;
 case "Telefono":$tel="selected";
                      break;
 case "Mail":$mail="selected";
                         break;
 case "Observaciones":$obser="selected";
						 break;
 case "C.U.I.T.":$cuit="selected";
						 break;	 
 case "Domicilio":$domicilio="selected";
						 break;	 
}

$state=$_POST["tipo_prov"] or $state=$parametros['estado'] or $state="todos";
$link=encode_link("proveedores.php",array("texto_buscado"=>$buscado))
?>

<form name="form1" method="post" action="<?=$link?>">
<center>
    <table class="bordes" cellspacing="0" cellpadding="0" width="90%" height="35">
      <tr bgcolor=<?=$bgcolor3?>>
        <!--
	     FILTRO DE TIPO DE PROVEEDOR...NO BORRAR!!!
         <td width="160" height="35">
          <p align="center"><b>Proveedores</b></p>
        </td>
        <td>-->
        <?
        //traemos los tipos de proveedores para generar el select de tipos de proveedores
    /*$query="select * from tipos_prov";
    $tipos_prov=$db->Execute($query) or die ($db->ErrorMsg().$query);      
	$todos=($state=='todos')?'selected':'';
    ?>
	<select name="tipo_prov" value="est" <? if($id) echo 'disabled';?> onchange="document.form1.submit()">
            
	        <option value="todos" <?echo $todos?>>Todos</option>
	<?
	while(!$tipos_prov->EOF)
	{ if(($state!='todos')&&($state==$tipos_prov->fields['tipo'])) 
	    $selected='selected';
	   else 
	    $selected='';
	?>          
	        <option value='<?echo $tipos_prov->fields['tipo']; echo "' ".$selected;?>><? echo $tipos_prov->fields['descripcion']?></option>
	 <?
     $tipos_prov->MoveNext();
	} */  
    ?>   
  <!--</select>
        </td>-->
       <?PHP
       
        
 $limit_query=50;
 if($parametros["total_prov"]=="")
 {$offset=0;
  //si no se solicito una busqueda con filtro
  if(($_POST['bconsulta']!="Ver Datos")||($_POST["tbuscar"]==""))
  {
   if($state=='todos')
    $query="SELECT distinct count(razon_social) as total FROM proveedor";
   else
    $query="SELECT count(*) as total FROM proveedor join prov_t on proveedor.id_proveedor=prov_t.id_proveedor where activo='true' and prov_t.tipo='$state'"; 
   
   $resultado2=$db->Execute($query) or die($db->ErrorMsg().$query);
   $total_proveedores=$resultado2->fields["total"];
   $cant_filas_mostradas=0;
   $nro_paginas=intval($total_proveedores/$limit_query) + 1;
   $pagina_actual=1;
  }
 }  
 else
 { $total_proveedores=$parametros["total_prov"]; 
   $offset=$parametros["cant_filas_mostradas"];
   $nro_paginas=$parametros["nro_paginas"];
   $pagina_actual=$parametros["pagina_actual"];
 }
 if(($state=='todos')||(($_POST['bconsulta']=="Ver Datos")&&($_POST["tbuscar"]!="")))
 {$query="select proveedor.id_proveedor,proveedor.activo,proveedor.razon_social,proveedor.observaciones,contactos.nombre,contactos.tel,contactos.mail,contactos.direccion ";
  $query.="from proveedor left join contactos on proveedor.id_proveedor=contactos.id_proveedor ";
 }
 else  
 {$query="select p.id_proveedor,p.activo,p.razon_social,p.observaciones,contactos.nombre,contactos.tel,contactos.mail ";
  $query.="from (select proveedor.id_proveedor,proveedor.razon_social,proveedor.observaciones from proveedor join prov_t on proveedor.id_proveedor=prov_t.id_proveedor and prov_t.tipo='$state') as p left join contactos on p.id_proveedor=contactos.id_proveedor ";
 }
?> 
        
        <td width="40%" align="center">
          Buscar:&nbsp;
                    <input type="text" name="tbuscar" value="<?  /*como uno de los dos es vacio
															y el otro no, los concateno*/
              									echo $buscado ?>">
        </td>
        <td width="40%" algin="center">
          en:&nbsp;
            <select name="select_en" >
              <option <?php echo $rs;?>>Razon Social</option>
              <option <?php echo $c;?>>Contacto</option>
              <option <?php echo $tel;?>>Telefono</option>
              <option <?php echo $mail;?>>Mail</option>
			  <option <? echo $obser;?>>Observaciones</option>
			  <option <? echo $cuit;?>>C.U.I.T.</option>
			  <option <? echo $domicilio?>>Domicilio</option> 
            </select>
        </td>
      
        <td width="20%" align="left">
            <input type="submit" name="bconsulta" value="Ver Datos">
        </td>
      </tr>
    </table>

 <?php
 
 
  if ((($_POST['bconsulta']=="Ver Datos")&&($_POST["tbuscar"]!=""))||($buscado!=""))
 {
 //creamos el query para contar cuantas filas vamos a traer de
 //la busqueda con filtro	
  $query_total="select count(*) as total ";
  $query_total.="from proveedor left join contactos on proveedor.id_proveedor=contactos.id_proveedor ";
 	
  switch ($_POST['select_en'])
   {
    case "Razon Social":{
	   if ($_POST["tbuscar"] != "") {
   		$where="where proveedor.razon_social ilike '%".$_POST["tbuscar"]."%' ";
       }
       break;
    }
    case "Contacto":{
    	if ($_POST["tbuscar"] != "") {
   		$where="where contactos.nombre ilike '%".$_POST["tbuscar"]."%' ";
       }
       break;
    }
    case "Telefono":{
       if ($_POST["tbuscar"] != "") {
   		$where="where contactos.tel ilike '%".$_POST["tbuscar"]."%' ";
       }
       break;
    }
    case "Mail":{
       if ($_POST["tbuscar"] != "") {
   		$where="where contactos.mail ilike '%".$_POST["tbuscar"]."%' ";
       }
       break;
    }
    case "Observaciones":{
		if ($_POST["tbuscar"] != "") {
			$where="where proveedor.observaciones ilike '%".$_POST["tbuscar"]."%' ";
		}
		break;
	}
	case "C.U.I.T.":{
		if ($_POST["tbuscar"] != "") {
			$where="where proveedor.cuit ilike '%".$_POST["tbuscar"]."%' ";
		}
		break;
	}	
	case "Domicilio":{
		if ($_POST["tbuscar"] != "") {
			$where="where contactos.direccion ilike '%".$_POST["tbuscar"]."%' ";
		}
		break;
	}	
  }// fin switch
  /*if($where=="")
   $where.="where proveedor.activo='true' ";
  $query_total.=$where;
  */
  //hacemos la cuenta de cuantas filas vamos a traer 
  //en la busqueda con filtro 
  $resultado2=$db->Execute($query_total) or die($db->ErrorMsg().$query);
  $total_proveedores=$resultado2->fields["total"];
  $cant_filas_mostradas=0;
  $nro_paginas=intval($total_proveedores/$limit_query) + 1;
  $pagina_actual=1;
    
}//fin if

$cant_filas_mostradas=$offset;
$limit="limit $limit_query offset $offset";
$query.=$where;
if(($state=='todos')||(($_POST['bconsulta']=="Ver Datos")&&($_POST["tbuscar"]!=""))||($buscado!=""))
 $query.="order by proveedor.razon_social ";
else
 $query.="order by p.razon_social ";
$query.=$limit;
$resultado = $db->Execute($query) or die($db->ErrorMsg().$query);
$filas_encontradas=$resultado->RecordCount();
?>

<!-- FIN DE BUSQUEDA DE PROVEEDORES -->
<?$link=encode_link("proveedores.php",array("texto_buscado"=>$buscado))?>
<form name="form1" action="<?=$link?>" method="POST">
<hr>
<div style="position:relative; width:96%; height:80%; overflow:auto;">
<table width="100%" class='bordessininferior' cellspacing="0">
      <tr bgcolor="#c0c6c9">
      <td align="left" height="30"><b>&nbsp;&nbsp;Total: <? echo $total_proveedores; ?> proveedores.</b>
      </td>
      <td align="right">
        <?php  
               	echo "<b>";
                if($offset!=0) 
                  echo "<a href='".encode_link("proveedores.php",array("cant_filas_mostradas"=> $cant_filas_mostradas-$limit_query,"total_prov"=> $total_proveedores,"nro_paginas"=> $nro_paginas,"pagina_actual"=> $pagina_actual-1,"texto_buscado"=> $buscado,"estado"=>$state))."'><<  </a>";
                else  
                 echo "&nbsp;&nbsp;&nbsp;";
                echo " &nbspP&aacute;gina ".$pagina_actual." de ".$nro_paginas." &nbsp;";
                if($pagina_actual<$nro_paginas) 
                {
                	echo"<a href='".encode_link("proveedores.php",array("cant_filas_mostradas"=> $cant_filas_mostradas+$limit_query,"total_prov"=> $total_proveedores,"nro_paginas"=> $nro_paginas,"pagina_actual"=> $pagina_actual+1,"texto_buscado"=> $buscado,"estado"=>$state))."'> >> </a>&nbsp;";
                } 
                else
                { echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; 
                } 
                echo "</b>";
           //    } 
         ?>
      </td> 
    </tr>
  </table>
<!--</div>
<div style="position:relative; width:96%; height:70%; overflow:auto;">
-->
<table width="100%" class='bordessinsuperior'  cellspacing="2">
<tr title="Vea comentarios de los proveedores" id=mo>
<th width="20%"><b>Raz&oacute;n Social</b></th>
<th width="20%"><b>Contacto</b></th>
<th width="10%"><b>Tel&eacute;fono</b></th>
<th width="40%"><b>Dirección</b></th>
<th width="10%"><b>Mail</b></th>
<th ><b><font>Estado</b></font></th>
</tr>
<?
$cnr=1;
$resultado->MoveFirst();
while (!$resultado->EOF)
{ 
  if($resultado->fields['activo']=='t')
    $estado="Activo";
  else 
	$estado="No Activo";
	 
  if($resultado->fields['observaciones']=="")
    $comentario="Observaciones: no tiene";
  else 
	$comentario="Observaciones:".$resultado->fields['observaciones'];

  // Para pasar a la página de proveedores el id del proveedor.
  //A esta pagina, "carga_prov.php", iva antes
//  $link=encode_link("muestra_prov_contacto.php",array("id_prov"=> $resultado->fields["id_proveedor"],"pagina"=>"proveedores", "cant_filas_mostradas"=> $cant_filas_mostradas,"total_prov"=> $total_proveedores,"nro_paginas"=> $nro_paginas,"pagina_actual"=> $pagina_actual,"texto_buscado"=> $buscado,"tipo_prov"=>$state));
  $link=encode_link("carga_prov.php",array("id_prov"=> $resultado->fields["id_proveedor"]));
  //muestra_prov_contacto.php
  $link.="#contacto";
  /*
  if ($cnr==1)
  {$color2=$bgcolor2;
   $color=$bgcolor1;
   $atrib ="bgcolor='$bgcolor1'";
   $cnr=0;
  }
  else
  {$color2=$bgcolor1;
   $color=$bgcolor2;
   $atrib ="bgcolor='$bgcolor2'";
   $cnr=1;
  }
  $atrib.=" onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\"";
  $atrib.=" title='$comentario' style=cursor:hand";
  */
  //'left=40,top=80,width=700,height=365
  $onclick="ventana=window.open('$link','','resizable=1,scrollbars=1 ' )";
  ?>

<!--  <tr <?php echo $atrib; ?>>
  <td width="20%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["razon_social"]; ?></td>
  <td width="20%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["nombre"]; ?></td>
  <td width="10%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["tel"]; ?></td>
  <td width="40%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["direccion"]; ?></td>
  <td width="10%" <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $resultado->fields["mail"]; ?></td>
  <td <?php echo "onClick=\"location.href='$link'\";"; ?>><?php echo $estado; ?></td>
  </tr> -->

  <tr <?echo atrib_tr()."title='$comentario'"; ?>>
  <td width="20%" onclick="<?=$onclick?>"><?php echo $resultado->fields["razon_social"]; ?></td>
  <td width="20%" onclick="<?=$onclick?>"><?php echo $resultado->fields["nombre"]; ?></td>
  <td width="10%" onclick="<?=$onclick?>"><?php echo $resultado->fields["tel"]; ?></td>
  <td width="40%" onclick="<?=$onclick?>"><?php echo $resultado->fields["direccion"]; ?></td>
  <td width="10%" onclick="<?=$onclick?>"><?php echo $resultado->fields["mail"]; ?></td>
  <td onclick="<?=$onclick?>"><?php echo $estado; ?></td>

  <?php
    $x++;
   $resultado->MoveNext();

}   //del while de proveedores.
?>
</table>

</center>
</div>
</form>
</html>