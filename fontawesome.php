<?php
/** @file
 * Font Awesome module.
 * @author Marek Horvath
 */

namespace nge;

/**
 * Font Awesome module.
 * @author Marek Horvath
 */
class Fontawesome extends module
{

    const FABICONS = array(
        'youtube', 'bitcoin', 'gg-circle',
        'accessible-icon',
        'btc',
        'gg',
        'playstation','steam', 'steam-square', 'steam-symbol', 'twitch', 'xbox',
        'acquisitions-incorporated', 'critical-role', 'd-and-d', 'd-and-d-beyond', 'fantasy-flight-games', 'penny-arcade', 'wizards-of-the-coast',
        'napster', 'soundcloud', 'spotify', 'alipay', 'galactic-republic', 'galactic-senate', 'jedi-order', 'old-republic',
        'amazon-pay', 'apple-pay',
        'bitbucket', 'sellcast',
        'facebook-f', 'facebook',
        'bluetooth-b', 'bluetooth',
        'cc-diners-club', 'cc-jcb', 'cc-amazon-pay', 'cc-visa', 'cc-amex', 'cc-mastercard', 'cc-apple-pay', 'cc-discover', 'cc-paypal', 'cc-stripe',
        'stripe-s', 'paypal', 'ethereum', 'stripe', 'stripe-s', 'google-wallet', 'square-steam', 'square-facebook', 'facebook', 'facebook-messenger',
        'facebook-f', 'instagram', 'square-instagram'
    );

    const FABRANDSICON = array(
        'square-steam', 'square-facebook', 'facebook', 'facebook-messenger', 'facebook-f', 'instagram', 'square-instagram'
    );


    const WEIGHT = array(
        'far' => 'Regular',
        'fas' => 'Solid',
        'fal' => 'Light',
        'fat' => 'Thin',
        'fad' => 'Duotone'
    );

    const ROTATION = array(
        '' => 'None',
        'fa-rotate-90' => 'Rotate 90',
        'fa-rotate-180' => 'Rotate 180',
        'fa-rotate-270' => 'Rotate 270',
        'fa-flip-horizontal' => 'Flip horizontally',
        'fa-flip-vertical' => 'Flip Vertically'
    );

    const ANIMATION = array(
        '' => 'None',
        'fa-spin' => 'Spin',
        'fa-pulse' => 'Pulse',
    );

    const STYLE = array(
        '' => 'None',
        'fa-icon-style-1' => 'Bordered I',
        'fa-icon-style-2' => 'Bordered II',
        'fa-icon-style-3' => 'Bordered III',
        'fa-icon-style-4' => 'Grayscale',
        'fa-icon-style-5' => 'Layered'
    );

    const SIZE = array(
        '' => 'Normal',
        'fa-lg' => 'Large',
        'fa-2x' => '2x',
        'fa-3x' => '3x',
        'fa-5x' => '5x',
        'fa-7x' => '7x',
        'fa-10x' => '10x',
    );

    const HOVER = array(
        '' => 'None',
        'fa-icon-hover-1' => 'Hover I',
        'fa-icon-hover-2' => 'Hover II',
        'fa-icon-hover-3' => 'Hover III',
        'fa-icon-hover-4' => 'Hover IV'
    );

/**
 * Displays Font Awesome icons from the given category or filtered.
 * @param string $category Category name
 * @param string $filter
 */
public static function getByCategory ($category, $filter = null)
{
    $icons = self::loadIcons();
    $categories = self::getCategories();
    $resultIcon = array();
    $resultprefix = array();
    $data = array();
    /*$p = 0;
    foreach ($icons as $r) {
        $p += count($r['icons']);
          dump($r['icons']);
    }
    dump($p);
   exit;*/

    // filtering
    if ($filter != '' && isset($filter)) {

        $filterBy =  preg_quote($filter, '~');

        $filter = array_map(function ($icon) use ($filterBy) {
            if ( !empty (preg_grep('~' . $filterBy . '~', $icon['icons'])))
            return preg_grep('~' . $filterBy . '~', $icon['icons']);

        }, $icons);

        $filter = array_filter($filter, function($value) {
            return !is_null($value) && $value !== '';
        });

        foreach ($filter as $c) {
            foreach ($c as $key => $icon) {
                 $data['icons'][] = $icon;
                if (in_array($icon, self::FABICONS)) {
                    $data['prefix'][] = "fab";
                } else {
                    $data['prefix'][] = "fas";
                }

                if (in_array($icon, self::FABRANDSICON)) {
                    $data['prefix'][$key] .= " fa-brands";
                }
            }
        }
        $resultIcon = array_chunk($data['icons'], 9);
        $resultprefix = array_chunk($data['prefix'], 9);

    } else {
        foreach ($icons[$category]['icons'] as $key => $icon) {
            if (in_array($icon, self::FABICONS)) {
                $icons[$category]['prefix'][] = "fab";
            } else {
                $icons[$category]['prefix'][] = "fas";
            }

            if (in_array($icon, self::FABRANDSICON)) {
                $icons[$category]['prefix'][$key] .= " fa-brands";
            }
        }

        $resultIcon = array_chunk($icons[$category]['icons'], 9);
        $resultprefix = array_chunk($icons[$category]['prefix'], 9);

    }

    $data = array(
        'icons' => $resultIcon,
        'category' => $category,
        'categories' => $categories,
        'prefix' => $resultprefix,
    );


    xtl::template('listing', $data);
}


/**
  * Returns category list
  * @return array Returns array of category names
 */
public static function getCategories ()
{
    $categories = array();
    $icons = self::loadIcons();

    foreach ($icons as $key => $c) {
        $categories[$key] = $c['label'];
    }

	return $categories;
}


/**
  * Loads icons from file
  * @return array Returns array of category names
 */
public static function loadIcons ()
{
    // Request-wide cache.
	static $cache;
	if (isset($cache)) return $cache;

    $categories = array();

    // Load
	$path = __DIR__ .  '/../lib/3rd/font-awesome/categories.json'; // categories.yml

	$json = file_get_contents($path);

    $categories = json_decode($json, true);

	return $cache = $categories;
}

}

?>