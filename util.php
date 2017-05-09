<?php
/**
 * Resize an image and keep the proportions
 * @author Allison Beckwith <allison@planetargon.com>
 * @param string $filename
 * @param integer $max_width
 * @param integer $max_height
 * @return image
 */
function resizeImage($image, $filename, $max_width, $max_height) {
	list($orig_width, $orig_height) = getimagesize($filename);

	$width = $orig_width;
	$height = $orig_height;

	# taller
	if ($height > $max_height) {
		$width = ($max_height / $height) * $width;
		$height = $max_height;
	}

	# wider
	if ($width > $max_width) {
		$height = ($max_width / $width) * $height;
		$width = $max_width;
	}

	$image_p = imagecreatetruecolor($width, $height);

	//$image = imagecreatefromjpeg($filename);

	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

	return $image_p;
}
