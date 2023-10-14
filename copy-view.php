<?php
namespace nge;


// spusta sa: nge /www/copy-view script copy-view.php  | tee subor.txt

// nge /var/www/copy-view script copy-view.php 

global $DIR;

$LANGUAGE = ['cs-cz', 'de-de', 'en-us','es-es','fr-fr','hu-hu','it-it','pl-pl','ru-ru','sk-sk','uk-ua','zh-cn'];
$LANG = 'en-us';

$DIR = '/products/generate-paper-tag.xtl'; // name dir /xxx or name file /dir/file.xtl. /products/generate-paper-tag.xtl'
$path = '/www/nge/repo/view/system/';


// The function copies and updates the language in <t:view lang= ....
function _copy($src, $dst, $from_lang, $to_lang, $isDir = true) {

    if ($isDir) {
        if ($dir = opendir($src) ) {
            if(file_exists($dst)) return; // Skip file exist.

            if (mkdir($dst) ) {
                foreach (scandir($src) as $file) {
                    if (( $file != '.' ) && ( $file != '..' )) {
                        if ( is_dir($src . '/' . $file) ) {
                            _copy($src . '/' . $file, $dst . '/' . $file, $from_lang, $to_lang);
                            print 'The folder from ' . $src . ' to ' . $dst . ' has been copied. (' . $file . ')' . PHP_EOL;

                            updateLangView($dst, $file, $from_lang, $to_lang);

                        } else {
                            copy($src . '/' . $file, $dst . '/' . $file);

                            updateLangView($dst, $file, $from_lang, $to_lang);
                        }
                    }
                }

                closedir($dir);
            } else print 'ERROR COPY DIR' . PHP_EOL;
        } else print 'ERROR OPEN DIR' . PHP_EOL;

    } else {
        global $DIR;
        $nameDir = string_between_two_string($DIR, '/', '/');
        if ( @copy($src, $dst)) {
            print 'The folder from ' . $src . ' to ' . $dst . ' has been copied.' . PHP_EOL;
            updateLangView($dst, null, $from_lang, $to_lang); //When updating the language only for file.xtl, the $makeFile parameter must be set to null.
        } else {
             print 'Error: Dir probably does not exist or is named ' .  $nameDir . ' differently. (' . $dst . ')' . PHP_EOL;
        }

    }


}

// The function updates the language in <t:view lang= ....
function updateLangView($src, $nameFile, $from_lang, $to_lang) {
    $row = 0;
    $text = '';

    if (!empty($nameFile)) $path = $src . '/' . $nameFile;
    else $path = $src;

    if ($file = fopen($path, 'r+')) {
        while ($buff = fgets($file, 4096)) {
            if ($row == 0) $buff = str_replace('lang="' . $from_lang . '"', 'lang="' . $to_lang . '"', $buff);
            $text .= $buff;
            $row++;
        }
        file_put_contents($path, $text);

        fclose($file);

        print 'The language was updated from '. $from_lang . ' to ' . $to_lang  . PHP_EOL;
    } else {
         print 'ERROR UPDATE LANG VIEW ' . $src . PHP_EOL;
    }
}

function string_between_two_string($str, $starting_word, $ending_word)
{
    $count = substr_count($str, '/');

    //$lastChar = substr($str, -1);
    $subtring_start = strpos($str, $starting_word);
    $subtring_start += strlen($starting_word);
    if ($count == 1) {
       $result = substr($str, 1);
    } else {
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;  
        $result = substr($str, $subtring_start, $size);
    }
     return $result;
}


$src = $path . $LANG . $DIR;

// Run copy and updates the language in <t:view lang= ....
foreach ($LANGUAGE as $l) {
    if ($LANG == $l ) continue;
    $dst = $path . $l . $DIR;
    _copy($src, $dst, $LANG, $l, false); // If only file.xtl is to be copied, the parameter $isDir should be set to false
}