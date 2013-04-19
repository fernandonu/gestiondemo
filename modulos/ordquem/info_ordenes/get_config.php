<?

$arch_config = "./configuracion.cfg";
$arch = fopen($arch_config,"r");
$configuracion = fread($arch,filesize($arch_config));
fclose($arch);

print '#'.$configuracion;
?>