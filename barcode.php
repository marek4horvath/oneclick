<?php
/** @file
 * Part of utility package.
*/

namespace nge;

/*
 * This library provides barcode processing functions.
*/

class Barcode
{
    const HORISONTAL = 0;
    const VERTICAL = 90;

    // font
    public $font = '';

    // Create the barcode. $code is the 12/13 digit barcode to be displayed.
    public $code  = 0;

    // The $scale is the scale of the image in integers.
    public $scale = 0;

    // $bgColor is an array of RGB colors for the barcode background.
    public $bgColor = array();

    // $colerText is an array of RGB colors for the code.
    public $colerText = array();

    // $colerText is an array of RGB colors for the bars.
    public $colerBars = array();

    // $rotate specifies the barcode rendering type.
    public $rotate = 0;

    // The $_key is based on the first digit.
    private $_key;

    // The $_bars is array binar.
    private $_bars;

    // The $_image is object gd resource.
    private $_image;

    // The $_width is width image.
    private $_width;

    // The $_height is height image.
    private $_height;

    /*******************************************************************/
    /* Start maping binar					                           */
    /*******************************************************************/

    public static $PARITY_KEY = array (
        0 => "000000", 1 => "001011", 2 => "001101", 3 => "001110",
        4 => "010011", 5 => "011001", 6 => "011100", 7 => "010101",
        8 => "010110", 9 => "011010"
    );

    public static $LEFT_PARITY = array (
        // Odd Encoding
        0 => array(
            0 => "0001101", 1 => "0011001", 2 => "0010011", 3 => "0111101",
            4 => "0100011", 5 => "0110001", 6 => "0101111", 7 => "0111011",
            8 => "0110111", 9 => "0001011"
        ),
        // Even Encoding
        1 => array (
            0 => "0100111", 1 => "0110011", 2 => "0011011", 3 => "0100001",
            4 => "0011101", 5 => "0111001", 6 => "0000101", 7 => "0010001",
            8 => "0001001", 9 => "0010111"
        )
    );

    public static $RIGHT_PARITY = array (
        0 => "1110010", 1 => "1100110", 2 => "1101100", 3 => "1000010",
        4 => "1011100", 5 => "1001110", 6 => "1010000", 7 => "1000100",
        8 => "1001000", 9 => "1110100"
    );

    public static $GUARD = array (
        'start' => "101", 'middle' => "01010", 'end' => "101"
    );

    /*******************************************************************/
    /* END maping binar					                               */
    /*******************************************************************/

    /**
    * @param int $code The code barcode.
    * @param int $scale The scale img. min 2, max 12
    * @param string $fontpath The font path.
    * @param int $rotate The rotate barcode.
    * @param array $colerText The array that specifies the color for the code.
    * @param array $colerBars The array that specifies the color for the bars
    */
    public function __construct ($code, $scale, $fontpath = null, $rotate = self::HORISONTAL,  $colerText = array(0x00, 0x00, 0x00), $colerBars = array(0x00, 0x00, 0x00), $bgColor = array(0xFF, 0xFF, 0xFF))
    {
        $code = (string)$code;
        self::validateCode($code);
        if (! array_key_exists(substr($code,0,1), self::$PARITY_KEY)) throw new Exception("The incorrect parity key " . substr($code,0,1));

        // Get the key, which is based on the first digit.
        $this->_key = self::$PARITY_KEY[substr($code,0,1)];

        if (count($colerText) != 3) throw new Exception("The color for the code must be in the form of RGB");
        $this->colerText = $colerText;

        if (count($colerBars) != 3) throw new Exception("The color for the bars must be in the form of RGB");
        $this->colerBars = $colerBars;

        if (count($bgColor) != 3) throw new Exception("The color for the background must be in the form of RGB");
        $this->bgColor = $bgColor;

        $this->rotate = $rotate;

        // Set font
        if (!$fontpath) $this->font = app::$root.'/www/FreeSansBold.ttf';
        else $this->font = $fontpath;

        // Clamp scale between 2 and 12
        if ($scale < 2) $this->scale = 2;

        elseif ($scale > 12) $this->scale = 12;

        else $this->scale = $scale;

        $len = strlen($code);

        if ($len != 13 && $len != 12) throw new Exception("Barcode expects 12 or 13 digit number");


        // The generateGITIN13 (13th digit) can be calculated or supplied
        $this->code = $code;
        if ($len === 12)
            $this->code = self::generateGITIN13($code, true);

        $this->_bars = $this->encode();

        // Create image barcode.
        $this->createImage();

        // Draw bars barcode.
        $this->drawBars();

        // Draw text barcode.
        $this->drawCode();
    }

    /**
    * Generation GITIN-13
    * @param int $ean number value
    * @return string Returns GITIN-13.
    */
    public static function generateGITIN13 ($ean, $fullCode = false) {
        $code = str_pad($ean, 9, '0');
        $weight_flag = true;
        $sum = 0;

        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ( $weight_flag ? 3 : 1 );
            $weight_flag = !$weight_flag;
        }

        if (! $fullCode) $code = (10 - ($sum % 10)) % 10;
        else $code .= (10 - ($sum % 10)) % 10;

        return $code;
    }

    // The function create image barcode.
    protected function createImage()
    {
        $color = $this->bgColor;
        // set height img.
        $this->_height = $this->scale * 60;

        // set width img.
        $this->_width  = 1.9 * $this->_height; // 1.8 * $this->_height;

        // create image.
        $this->_image = imagecreate($this->_width, $this->_height);

        ImageColorAllocate($this->_image, $color[0], $color[1], $color[2]);
    }

    // Function to display the image of the barcode in the browser.
    public function display()
    {
        // Send HTTP headers.
        header("Content-Type: image/png; name=\"barcode.png\"");

        // Terminate all output buffering.
        while (ob_get_level()) ob_end_clean();
        // Rotate img;
        $this->_image = imagerotate( $this->_image, $this->rotate, 0);

        imagepng($this->_image);
    }

    // The function creates vertical bars.
    protected function drawBars()
    {
        $color = $this->colerBars;

        $bar_color = ImageColorAllocate($this->_image, $color[0], $color[1], $color[2]);

        $max   = $this->_height * 0.025;
        $floor = $this->_height * 0.825;
        $width = $this->scale;

        $x = ($this->_height * 0.2) - $width;

        foreach($this->_bars as $bar) {
            $tall = 0;

            if(strlen($bar) == 3 || strlen($bar) == 5) $tall = ($this->_height * 0.15);

            for($i = 1; $i <= strlen($bar); $i++) {
                if(substr($bar, $i-1, 1) === '1') imagefilledrectangle($this->_image, $x, $max, $x + $width, $floor + $tall, $bar_color);
                $x += $width;
            }

        }

    }

    // The function draws the code.
    protected function drawCode()
    {
        $x = $this->_width * 0.05;
        $y = $this->_height * 0.96;
        $color = $this->colerText;

        $text_color = ImageColorAllocate($this->_image, $color[0], $color[1], $color[2]);

        $fontsize = $this->scale * 7;
        $space = $fontsize * 1;

        for($i = 0; $i < strlen($this->code); $i++) {
            if ($i == 12) imagettftext($this->_image, $fontsize, 0, ($x + ($this->scale * 10)) + 2, $y, $text_color, $this->font, '>');

            imagettftext($this->_image, $fontsize, 0, $x, $y, $text_color, $this->font, $this->code[$i]);

            if($i == 0 || $i == 6) $x += $space * 0.5;
            $x += $space;

        }
    }

    // The function deletes the img from the buffer memory.
    public function __destruct()
    {
        imagedestroy($this->_image);
    }


    // The function encodes the code
    protected function encode()
    {
        $barcode[] = self::$GUARD['start'];

        for($i=1; $i <= strlen($this->code)-1; $i++) {

            if($i < 7) $barcode[] = self::$LEFT_PARITY[$this->_key[$i-1]][substr($this->code, $i, 1)];
            else  $barcode[] = self::$RIGHT_PARITY[substr($this->code, $i, 1)];

            if($i == 6) $barcode[] = self::$GUARD['middle'];
        }
        $barcode[] = self::$GUARD['end'];

        return $barcode;
    }



    // The function return obj img.
    public function &image()
    {
        return $this->_image;
    }


    /**
    * The function saves the barcode img to DIR in png format.
    * @param string $path The path dir.
    * @return no
    */
    public function save($path = 'barcode.png')
    {
        $dir = dirname($path);

        if (!file_exists($dir)) mkdir($dir, 0644, true);

        imagepng($this->_image, $path);
    }

    /**
    * The Code verification.
    * @param string $code gitin.
    * @return no
    */
    public static function validateCode ($code) {
        if (! preg_match("/^\d+$/", $code))throw new Exception("The code is invalid " . $code);
    }
}