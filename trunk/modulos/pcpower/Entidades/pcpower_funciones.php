<?PHP

function tabla_filtros_nombres($link){

 $abc=array("a","b","c","d","e","f","g","h","i",
            "j","k","l","m","n","ñ","o","p","q",
            "r","s","t","u","v","w","x","y","z");
$cantidad=count($abc);
echo "<table width='98%' height='80%' id='mo'>";
echo "<input type=hidden name='filtro' value=''";
    echo "<tr>";
    for($i=0;$i<$cantidad;$i++){
        $letra=$abc[$i];
       switch ($i) {
                     case 9:
                     case 18:
                     case 27:echo "</tr><tr>";
                          break;
                   default:
                  } //del switch
//echo "<a id='link_load' href=$link><td style='cursor:hand' onclick=\"document.all.filtro.value='$letra'\">$letra</td></a>\n";
echo "<td style='cursor:hand' onclick=\"document.all.filtro.value='$letra';document.all.editar.value=''; document.form.submit();\">$letra</td>";
      }//del for
   echo "</tr>";
   echo "<tr>";
    echo "<td colspan='9' style='cursor:hand' onclick=\"document.all.filtro.value='Todos'; document.all.editar.value='';document.form.submit();\"> Todos";
    echo "</td>";
   echo "</tr>";
   echo "</table>";
}  //de la funcion
?>