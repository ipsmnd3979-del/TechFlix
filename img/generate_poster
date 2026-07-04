<?php
header('Content-Type: image/jpeg');

$width = 300;
$height = 450;
$image = imagecreate($width, $height);

// Background color (dark gray)
$bg = imagecolorallocate($image, 40, 40, 40);
// Text color (white)
$text_color = imagecolorallocate($image, 255, 255, 255);

// Add text
$text = "No Poster";
$font = 5; // Built-in font
$text_width = imagefontwidth($font) * strlen($text);
$text_height = imagefontheight($font);

$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font, $x, $y, $text, $text_color);

imagejpeg($image);
imagedestroy($image);
?>
