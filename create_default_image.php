<?php
// Create a simple default poster image
$width = 300;
$height = 450;

// Create image
$image = imagecreate($width, $height);

// Colors
$dark_gray = imagecolorallocate($image, 50, 50, 50);
$light_gray = imagecolorallocate($image, 150, 150, 150);
$white = imagecolorallocate($image, 255, 255, 255);

// Fill background
imagefill($image, 0, 0, $dark_gray);

// Add border
imagerectangle($image, 0, 0, $width-1, $height-1, $light_gray);

// Add text
$text = "No Image";
$font = 5; // Built-in font
$text_width = imagefontwidth($font) * strlen($text);
$text_height = imagefontheight($font);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font, $x, $y, $text, $white);

// Save image
header('Content-Type: image/jpeg');
imagejpeg($image, 'assets/img/default-poster.jpg');
imagejpeg($image); // Output to browser
imagedestroy($image);

echo "Default image created successfully!";
?>