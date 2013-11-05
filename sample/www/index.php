<?php
require_once(dirname(__FILE__).'/firmador_pdf.php');

$firmador = new firmador_pdf();

//-- Guardar sesion en XML
//$firmador->set_guardar_sesion_en_xml();

//-- Guardar sesion en BD
//$nombre_base = "toba_trunk";
//$dbh = new PDO("pgsql:host=localhost;dbname=$nombre_base", "postgres", "");
//$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
//$firmador->set_guardar_sesion_en_db($dbh);

//-- Compartir sesion de PHP con el applet
$firmador->set_guardar_sesion_en_php();


//-- Enviar applet
if (! isset($_GET['accion'])) {
	$url_actual = $firmador->get_url_base_actual(). $_SERVER['REQUEST_URI'];
	$firmador->generar_applet("firmador.jar", 
								$url_actual."?accion=descargar",
								$url_actual."?accion=subir",
								"Motivo de firma"
			);
	$firmador->generar_visor_pdf("pdfobject.min.js", $url_actual."?accion=descargar");
	die;
}

//-- DESCARGAR
if ($_GET['accion'] == 'descargar') {
	if (! isset($_GET['codigo'])) {
		header('HTTP/1.1 500 Internal Server Error');
		die("Falta indicar el codigo");
	}
	if (! $firmador->validar_sesion($_GET['codigo'])) {
		header('HTTP/1.1 500 Internal Server Error');
		die("Codigo invalido");   
	}	
	//Enviar PDF
	$firmador->enviar_headers_pdf();
        
	$file = dirname(dirname(__FILE__)).'/docOriginal.pdf';
	$fd = fopen($file,'r');
	fpassthru($fd);
	die;
}

//-- SUBIR
if ($_GET['accion'] == 'subir') {
	if (! isset($_POST['codigo'])) {
		header('HTTP/1.1 500 Internal Server Error');
		die("Falta indicar el codigo");
	}
	if ( ! $firmador->validar_sesion($_POST['codigo'])) {
		header('HTTP/1.1 500 Internal Server Error');
		die("Codigo invalido");   
	}
	if ($_FILES["md5_fileSigned"]["error"] != UPLOAD_ERR_OK) {
		error_log("Error uploading file");
		header('HTTP/1.1 500 Internal Server Error');
		die;
	}	
	$path = $_FILES['md5_fileSigned']['tmp_name'];
	$destino = dirname(dirname(__FILE__)).'/docFirmado.pdf';
	if (! move_uploaded_file($path, $destino)) {
		error_log("Error uploading file");
		header('HTTP/1.1 500 Internal Server Error');
		die;
	}

	die;
}

?>