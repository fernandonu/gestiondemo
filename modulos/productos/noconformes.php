<?
require_once("../../config.php");

switch($_POST['boton'])
{case "Nuevo Producto":{require_once("insertar_noconformes.php");break;}
 case "Borrar":{$i=1;
 	            while ($i<=$_POST['cant'])
                 {if ($_POST['borrar_'.$i]!="")
                  {$sql="delete from noconformes where id_noconforme=".$_POST['borrar_'.$i];
                   $resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                   }
                  $i++;
                 }
                header("location: noconformes.php");
                break;
                }
 default:{
?>
<html>
<head>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;src.style.cursor="hand";
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;src.style.cursor="default";
}

</script>
</head>
<body bgcolor="#E0E0E0">
<?

switch($parametros['campos'])
{case 1:{$campo="fecha_emision";break;}
 case 2:{$campo="descripcion_inconforme";break;}
 default :{$campo="fecha_emision";break;}
}

$sql="select id_noconforme,descripcion_inconformidad,fecha_emision from noconformes order by $campo;";
$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>
<form name="form1" method="post" action="noconformes.php">
  <br>
  <table border=0 width=100% cellspacing=2 cellpadding=3 >
    <tr>
      <td colspan=3 align=left id=ma><table width=100%>
            <tr id=ma><td width=30% align=left>    
                <b>Total Productos:</b><? echo  $resultado->RecordCount(); ?></b></td>
                <td width=70% align=right>&nbsp;</td>
                </b></tr>
          </table>       </td>
    </tr>
    <tr>
      <td width="16%" align="center" id=mo><a id=mo href='<? echo encode_link("noconformes.php",Array("campo"=>1)); ?>'><b>Fecha Emisi&oacute;n</b></a></td>
      <td width="75%" align="center" id=mo><a id=mo href='<? echo encode_link("noconformes.php",Array("campo"=>2)); ?>'><b>Descripcion</b></a></td>
      <td width="9%" align="center" id=mo><b>Borrar</b></td>
    </tr>
    <? 
  $i=1;
  while (!$resultado->EOF )
  {
  if ($cnr==1)
  {$color1=$bgcolor1;
   $color =$bgcolor2;
   $cnr=0;
  }
else
  {$color1=$bgcolor2;
   $color =$bgcolor1;
   $cnr=1;
  }
?>
      <tr  bgcolor='<?php echo $color; ?>' onMouseOver="sobre(this,'#FFFFFF');" onMouseOut="bajo(this,'<? echo $color?>' );"><a href="<? echo encode_link("descripcion_noconformes.php", array("id" =>$resultado->fields["id_noconforme"])); ?>" >
      <td align="center"><font color="<? echo $color1?>"><b><? echo $resultado->fields['fecha_emision']; ?></font></td>
      <td align="center"><font color="<? echo $color1?>"><b><? echo $resultado->fields['descripcion_inconformidad']; ?></font></td>
      <td align="center"><font color="<? echo $color1?>">
       <input type="checkbox" name="borrar_<? echo $i; ?>" value="<? echo $resultado->fields['id_noconforme']; ?>">
      </font></td>
    </a> </tr>
    <? 		
	$resultado->MoveNext();
	$i++;
  }  ?>
  </table>
  <input type="hidden" name="cant" value="<? echo $resultado->RecordCount(); ?>">
  <div align="right">
    <input type="submit" name="boton" value="Borrar">
    <br>
  </div>
  <div align="center">
    <table>
      <tr>
        <td><input type="submit" name="boton" value='Nuevo Producto'>
        </td>
      </tr>
    </table>
  </div>
</form>
</body>
</html>
<?
 }//fin default
}//fin switch
?>