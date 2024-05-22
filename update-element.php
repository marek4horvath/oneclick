<?php
namespace nge;

/** @file
 * @author Marek Horvath
 */
// spusta sa: nge /www/copy-view script copy-view.php  | tee subor.txt

// nge /var/www/copy-view script copy-view.php 

function validLang() {
    print ("\nLanguage (e.g. en-us): ");
    $lang = fgets(STDIN);
    $lang = preg_replace('/\s+/', '', $lang);

    if (empty($lang)) {
        validLang();
    }  else {
        if (strpos($lang, '-')) {
           return $lang;
        } else {
            validLang();
        }

    }
}

function getDirContents($dir, &$results = array(), $isXTL = false) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        //dump($dir . $value);
        $path = $dir . DIRECTORY_SEPARATOR . $value;
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results, $isXTL);
            if ($isXTL)
            $results[] = $path;
        }
    }

    return $results;
}
// , $search, $replace
function updateView($src) {
    $row = 0;
    $src = str_replace("\\", '/', $src);
    $content = '';
    $j = 0;

    if ($file = fopen($src, 'r+')) {
        while ($buff = fgets($file, 4096)) {

            if (preg_match('/<div class="clearfix">/', $buff, $matches, PREG_OFFSET_CAPTURE)) {
                $j = $row + 2;
                $matches = str_replace($matches[0][0], '', $matches[0][0]);
                $buff = $matches . PHP_EOL;
            } else if (preg_match('/<h1>[^.]*<\/h1>/', $buff, $matches, PREG_OFFSET_CAPTURE)
                || preg_match('/<h1 class="[^.]*">[^.]*<\/h1>/', $buff, $matches2, PREG_OFFSET_CAPTURE)
            ) {
                if(! empty($matches[0][0]))
                    $matches = str_replace('<h1>', '<t:pageTitle>', $matches[0][0]);
                else
                    $matches = preg_replace('/<h1 class="[^.]*">/', '<t:pageTitle>', $matches2[0][0]);

                $matches = str_replace('</h1>', '</t:pageTitle>', $matches);
                $buff = $matches . PHP_EOL;

            } else  if (preg_match('/<h2>[^.]*<\/h2>/', $buff, $matches, PREG_OFFSET_CAPTURE)
                || preg_match('/<h2 class="[^.]*">[^.]*<\/h2>/', $buff, $matches2, PREG_OFFSET_CAPTURE)
            ) {
                if(! empty($matches[0][0]))
                    $matches = str_replace('<h2>', '<t:pageTitle>', $matches[0][0]);
                else
                    $matches = preg_replace('/<h2 class="[^.]*">/', '<t:pageTitle>', $matches2[0][0]);

                $matches = str_replace('</h2>', '</t:pageTitle>', $matches);
                $buff = $matches  . PHP_EOL;

            }

            // remove </div>
            if ($j == $row && ($j != 0 && $row != 0) ) {
                $buff = str_replace($buff, '', $buff) . PHP_EOL;
                $j = 0;
            }

            $content .= $buff;

            $row++;
        }

        print PHP_EOL . PHP_EOL . 'File put content into ' . $src . PHP_EOL;

        file_put_contents($src, $content);

        fclose($file);
    }

}

function main() {
    $argc = $_SERVER['argc'];
    $argv = $_SERVER['argv'];

    global $DIR;    $src = '/www/nge/repo/view/system/';

    $lang = validLang();



    $files_path = getDirContents($src . $lang);
    $i = 0;

    foreach ( $files_path as $path) {
        if ($i >= 2 ) continue;
        updateView($path);
        $i++;
    }



}

main();


