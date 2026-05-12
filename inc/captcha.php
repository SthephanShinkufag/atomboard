<?php
/**
 * Script for generation of CAPTCHAs
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @version 0.3
 */

declare(strict_types=1);
session_start();
putenv('GDFONTPATH=' . realpath(dirname(__FILE__)) . '/fonts/');

class SimpleCaptcha {
	// Image size
	public int $width = 175;
	public int $height = 55;

	// Word length
	public int $minWordLength = 4;
	public int $maxWordLength = 7;

	// Session name to store the original text
	public string $sessionVar = 'atom_captcha';

	// Path for font files
	public string $resourcesPath = './fonts';

	// Font configuration
	public array $fonts = [
		'Roboto-Regular' => [ 
			'spacing' => 1, // relative pixel space between characters
			'minSize' => 22, // min font size
			'maxSize' => 32, // max font size
			'font' => 'roboto_regular.ttf'], // TTF file in the resources path
		'Roboto-Bold' => [
			'spacing' => 1,
			'minSize' => 22,
			'maxSize' => 32,
			'font' => 'roboto_bold.ttf']
	];

	// Image format: 'png' or 'jpeg'
	public string $imageFormat = 'png';

	// Background color in RGB-array, used only for JPEG (PNG background is transparent)
	// For example: [25, 39, 52] for a dark background, or [220, 220, 220] for a light one
	public array $bgColor = [220, 220, 220];

	// Wave configuration in X and Y axes
	public int $xPeriod = 10;
	public int $xAmplitude = 5;
	public int $yPeriod = 11;
	public int $yAmplitude = 13;

	// Letter rotation clockwise
	public int $maxRotation = 6;

	// Internal image size factor (for better image quality)
	// 1: low, 2: medium, 3: high
	public int $scale = 3;

	// Thickness of the lines, relative to the scale
	public float $lineThickness = 3;
	// Opacity of the lines (0-127, where 127 is fully transparent)
	public int $lineOpacity = 70;

	// Number of noise dots, relative to the scale
	public int $noiseNumber = 40;
	// Size of the noise dots, relative to the scale
	public float $noiseSize = 1.5;
	// Opacity of the noise dots (0-127, where 127 is fully transparent)
	public int $noiseOpacity = 50;

	// Blur effect for better image quality (but slower image processing)
	public bool $blur = true;

	// Debug mode: write the generated text and processing time on the image (for testing purposes)
	public bool $debug = false;

	// GD image
	public \GdImage $img;
	public int $gdFgColor;
	public int $gdBgColor;
	public ?int $gdShadowColor = null;
	public float $textFinalX = 0;

	// Horizontal line insertion
	protected function addLines(): void {
		$scaledW = $this->width * $this->scale;
		$scaledH = $this->height * $this->scale;
		// Create a color: the same as the text, but with transparency
		// If PNG is not supported, it will just be slightly lighter
		$lineColor = (int)imagecolorallocatealpha($this->img, 
			($this->gdFgColor >> 16) & 0xFF,
			($this->gdFgColor >> 8) & 0xFF,
			$this->gdFgColor & 0xFF,
			$this->lineOpacity);
		$thickness = max(1, (int)($this->lineThickness * $this->scale));
		for ($l = 0; $l < 2; $l++) {
			$x1 = (int)($scaledW * 0.05);
			$x2 = (int)($scaledW * 0.95);
			$y1 = mt_rand((int)($scaledH * 0.25), (int)($scaledH * 0.75));
			$y2 = mt_rand((int)($scaledH * 0.25), (int)($scaledH * 0.75));
			// Draw a thicker line by drawing multiple lines with a vertical offset
			for ($t = 0; $t < $thickness; $t++) {
				imageline($this->img, $x1, $y1 + $t, $x2, $y2 + $t, $lineColor);
			}
		}
	}

	protected function addNoise(): void {
		$scaledW = $this->width * $this->scale;
		$scaledH = $this->height * $this->scale;
		$noiseColor = (int)imagecolorallocatealpha($this->img, 
			($this->gdFgColor >> 16) & 0xFF,
			($this->gdFgColor >> 8) & 0xFF,
			$this->gdFgColor & 0xFF,
			$this->noiseOpacity);
		$size = max(1, (int)($this->noiseSize * $this->scale));
		$dotsCount = $this->noiseNumber * $this->scale;
		for ($i = 0; $i < $dotsCount; $i++) {
			$x = mt_rand(0, $scaledW);
			$y = mt_rand(0, $scaledH);
			if ($size === 1) {
				imagesetpixel($this->img, $x, $y, $noiseColor);
			} else {
				imagefilledrectangle($this->img, $x, $y, $x + $size, $y + $size, $noiseColor);
			}
		}
	}

	// Text insertion
	protected function addText(string $text, array $fontcfg = []): void {
		$fontfile = realpath($this->resourcesPath . '/' . $fontcfg['font']);
		// Increase font-size for shortest words: 9% for each glyph missing
		$fontSizefactor = 1 + (($this->maxWordLength - strlen($text)) * 0.09);
		// Initial X position for the first letter (20% of the image width)
		$x = 20 * $this->scale;
		// Move the baseline below the center (70% from the top)
		// to avoid cutting off the top of the letters
		$y = (int)(($this->height * $this->scale) * 0.75); 
		foreach (str_split($text) as $letter) {
			$degree = rand($this->maxRotation * -1, $this->maxRotation);
			$fontsize = (int)(rand($fontcfg['minSize'], $fontcfg['maxSize']) *
				$this->scale * $fontSizefactor);
			if ($this->gdShadowColor !== null) {
				imagettftext($this->img, $fontsize, $degree, $x + $this->scale, $y + $this->scale,
					$this->gdShadowColor, $fontfile, $letter);
			}
			$coords = imagettftext($this->img, $fontsize, $degree, $x, $y,
				$this->gdFgColor, $fontfile, $letter);
			$x += ($coords[2] - $coords[0]) + ($fontcfg['spacing'] * $this->scale);
		}
		$this->textFinalX = (float)$x;
	}

	// Creates the image resources
	protected function allocateImage(): void {
		$scaledW = $this->width * $this->scale;
		$scaledH = $this->height * $this->scale;
		$this->img = imagecreatetruecolor($scaledW, $scaledH);

		// Background color
		if ($this->imageFormat === 'png') {
			// Disable blending so that imagefilledrectangle overwrites pixels with transparency
			imagealphablending($this->img, false);
			// 127 is full transparency for alpha channel in GD
			$this->gdBgColor = (int)imagecolorallocatealpha($this->img, 0, 0, 0, 127);
			imagefilledrectangle($this->img, 0, 0, $scaledW, $scaledH, $this->gdBgColor);
			// Enable saving of the alpha channel and blending for text drawing
			imagesavealpha($this->img, true);
			imagealphablending($this->img, true); 
		} else {
			$this->gdBgColor = (int)imagecolorallocate($this->img, 
				$this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
			imagefilledrectangle($this->img, 0, 0, $scaledW, $scaledH, $this->gdBgColor);
		}

		// Foreground color: random between 50 and 200 to avoid colors that are hard to read
		$this->gdFgColor = (int)imagecolorallocate($this->img,
			mt_rand(40, 190), mt_rand(40, 190), mt_rand(40, 190));

		// Shadow color: light shadow for universal contrast
		$this->gdShadowColor = (int)imagecolorallocatealpha($this->img, 255, 255, 255, 40);
	}

	// Main method to create the CAPTCHA image
	public function createImage(): void {
		// Initialization
		$time = microtime(true);
		$this->allocateImage();

		// Text insertion
		$text = $this->getRandomText();
		$_SESSION[$this->sessionVar] = $text;
		$this->addText($text, $this->fonts[array_rand($this->fonts)]);

		// Transformations
		$this->addLines();
		$this->addNoise();
		$this->waveImage();
		if ($this->blur && function_exists('imagefilter')) {
			imagefilter($this->img, IMG_FILTER_GAUSSIAN_BLUR);
		}
		$this->reduceImage();

		// Debug info
		if ($this->debug) {
			imagestring($this->img, 1, 1, $this->height - 8,
				$text . ' ' . round((microtime(true) - $time) * 1000) . 'ms', $this->gdFgColor);
		}

		// Output
		$this->writeImage();
		// Cleanup
		imagedestroy($this->img);
	}

	// Random text generation
	protected function getRandomText(?int $length = null): string {
		$length ??= rand($this->minWordLength, $this->maxWordLength);
		// Eliminate similar symbols (k is similar to x, l is similar to i)
		$consonants = 'bcdfghjmnpqrstvwxyz';
		$consLen = strlen($consonants) - 1;
		$vowels = 'aeiou';
		$text = '';
		for ($i = 0; $i < $length; $i++) {
			// Avoid more than 2 consecutive vowels or consonants for better readability
			if ($i === 0 || strpos($vowels, $text[$i - 1]) !== false) {
				// If the previous letter was a vowel, put a consonant with an 70% chance, or second vowel
				$text .= rand(0, 10) < 7 ? $consonants[mt_rand(0, $consLen)] : $vowels[mt_rand(0, 4)];
			} else {
				// If the previous letter was a consonant, put a vowel with an 80% chance, or second consonant
				$text .= rand(0, 10) < 8 ? $vowels[mt_rand(0, 4)] : $consonants[mt_rand(0, $consLen)];
			}
		}
		return $text;
	}

	// Reduce the image to the final size
	protected function reduceImage(): void {
		$resampledImg = imagecreatetruecolor($this->width, $this->height);
		if ($this->imageFormat === 'png') {
			imagealphablending($resampledImg, false);
			imagesavealpha($resampledImg, true);
			imagefill($resampledImg, 0, 0, (int)imagecolorallocatealpha($resampledImg, 0, 0, 0, 127));
		}
		imagecopyresampled($resampledImg, $this->img, 0, 0, 0, 0, $this->width, $this->height,
			$this->width * $this->scale, $this->height * $this->scale);
		imagedestroy($this->img);
		$this->img = $resampledImg;
	}

	// Wave filter
	protected function waveImage(): void {
		$width = $this->width * $this->scale;
		$height = $this->height * $this->scale;

		// Create a temporary copy for calculations
		$tempImg = imagecreatetruecolor($width, $height);
		if ($this->imageFormat === 'png') {
			imagealphablending($tempImg, false);
			imagesavealpha($tempImg, true);
		}

		// Fill the background in the new image so that there are no black bars
		imagefilledrectangle($tempImg, 0, 0, $width, $height, $this->gdBgColor);

		// Calculate the wave parameters
		$xp = $this->scale * $this->xPeriod * rand(1, 2);
		$yp = $this->scale * $this->yPeriod * rand(1, 2);
		$xa = $this->scale * $this->xAmplitude;
		// Limit the Y amplitude to 12% of the height, to avoid cutting off the text
		$ya = min($this->scale * $this->yAmplitude, $height * 0.12);
		$rand = rand(0, 100);

		// Wave along the X axis (horizontal offset) - move vertically and copy horizontal lines
		for ($i = 0; $i < $height; $i++) {
			imagecopy($tempImg, $this->img, (int)(sin($i / $xp + $rand) * $xa), $i, 0, $i, $width, 1);
		}

		// Clean up the main resource so that the text does not appear doubled
		imagealphablending($this->img, false); // Turn off blending for complete cleaning
		imagefilledrectangle($this->img, 0, 0, $width, $height, $this->gdBgColor);
		imagealphablending($this->img, true);

		// Wave on the Y axis (vertical offset)
		for ($i = 0; $i < $width; $i++) {
			imagecopy($this->img, $tempImg, $i, (int)(sin($i / $yp + $rand) * $ya), $i, 0, 1, $height);
		}

		// Cleanup
		imagedestroy($this->img);
	}

	// File generation
	protected function writeImage(): void {
		header('Cache-Control: no-cache, must-revalidate');
		if ($this->imageFormat === 'png') {
			imagesavealpha($this->img, true); // Transparent background
			header('Content-type: image/png');
			imagepng($this->img);
		} else {
			header('Content-type: image/jpeg');
			imagejpeg($this->img, null, 85);
		}
	}
}

$captcha = new SimpleCaptcha();
$captcha->createImage();
