<?php
$currPath = dirname(__FILE__);
require_once('util.php');
require_once('rede.php');


$up = '';
if (isset($_GET['upload'])) {
	$up = $_GET['upload'];
} 


if ($up == 'rede') {
	copy($_FILES['arqRede']['tmp_name'], $currPath.'/redes/'.$_FILES['arqRede']['name']);
	$acao = 'verRede';
	$arq = $currPath.'/redes/'.$_FILES['arqRede']['name'];
} else if ($up == 'img') {
	$arqInfo = pathinfo($_FILES['arqIMG']['name']);
	$ext = strtolower($arqInfo['extension']);
	//var_dump($arqInfo);
	if (($ext == 'jpg') || ($ext == 'jpeg')) {
		$img = imagecreatefromjpeg($_FILES['arqIMG']['tmp_name']);
	} else if ($ext == 'png')  { 
		$img = imagecreatefrompng($_FILES['arqIMG']['tmp_name']);
	} else {
		echo "ERRO: Utilize apenas arquivos png ou jpg"; exit;
	}
	
	$img2 = resizeImage($img,$_FILES['arqIMG']['tmp_name'], 250, 250);
	imagepng($img2,$currPath.'/imagens/'.$_FILES['arqIMG']['name']);
	ob_clean();
	header('Content-type: image/jpeg');
	imagejpeg($img2);
	exit;
} else if (isset($_GET['acao'])) {
	$acao = $_GET['acao'];
	$arq = $_GET['arq'];
	if ($acao == 'apagar') {
		unlink('redes/'.$arq);

	} else {	
		$r = new Rede;
		$r->loadTXT('redes/'.$arq);
		if ($acao == 'verRede') { 
			$r->viewVIVA();
		} else if ($acao == 'gexf') {
			$r->exportGEXF();
		}
		exit;
	}

} else {
	$acao = '';
}


?>
<!DOCTYPE html>
<html>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
	<body  ng-app="myApp" ng-controller="myCtrl" class="container">

		<center>
			<h1><a href="index.php">Ferramenta para Visualização de Redes</a> </h1>
		</center>

		<div class="alert alert-success">
			Redes Disponíveis
		</div>
		<div class="alert">
			<table class="table">
			<?php
			if ($handle = opendir('redes')) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						?><tr>
						<td><B><a href='redes/<?=$entry?>' target='_blank'><?=$entry?></a></B></td>
						<td><a href='index.php?acao=verRede&arq=<?=$entry?>'  target='_blank' >Ver VIVAGraphJS</a></td>
						<td><a href='index.php?acao=gexf&arq=<?=$entry?>'  target='_blank' >Export GEXF</a></td>
						<td><a href='index.php?acao=apagar&arq=<?=$entry?>'>Excluir</a></td>
						</tr>
						<?php
					}
				}
				closedir($handle);
			}
			?>
			</table>
		</div>

		<hr>
		<div class="alert alert-success">
			<h4>Enviar Arquivo de Rede em Formato TXT</h4>
			<form action='index.php?upload=rede' method="post" enctype="multipart/form-data" >
				<input type="file" name='arqRede' />
				<input type="submit" value="OK"	>
			</form><br>
			<i>
				<b>OBS</b><br>
				* Os IDs devem ser sempre sequencial iniciando em 1<br>
			</i>
			
		</div>

		<HR>
		<div class="alert alert-success">
			<h4>Enviar Imagem em Formato PNG ou JPG</h4>
			<form action='index.php?upload=img' method="post" enctype="multipart/form-data" target="_blank">
				<input type="file" name='arqIMG' />
				<input type="submit" value="OK"	>
			</form>
		</div>

		<hr>
		<div class="alert alert-success">
			Imagens Disponíveis
		</div>
		<div class="alert">
			<?php
			if ($handle = opendir('imagens')) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						echo "<a href='imagens/$entry' target='_blank'>$entry</a>\n<Br>";
					}
				}
				closedir($handle);
			}
			?>
		</div>

		<script>
			var app = angular.module('myApp', []);
			app.controller('myCtrl', function($scope) {

			});
		</script>

		<hr>
		<center>
			<h5> Powered by Márcio Rossato
			<Br>
			</h5>
			<hr>
		</center>

	</body>
</html>
