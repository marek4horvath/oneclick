<?php
/** @file
 * Catalog module.
 * @author Marek Horvath.
 */

namespace nge;

require_once 'lib/util/barcode.php';

/**
 * Gs1 module.
 * This module provides Gs1 related methods.
 * @author Marek Horvath.
 */
class Gs1 extends module 
{

/**
 * Seting the coordinate crop x.
 * @var string $cropX
 * @xtl.property label="Crop x" type="Number"
 */
public $cropX;

/**
 * Seting the coordinate crop y.
 * @var string $cropY
 * @xtl.property label="Crop y" type="Number"
 */
public $cropY;

public static function actions (& $gs1)
{
    $can = array();
    // Generate PDF (always)
    $can[] = xtl::action('gs1-edit', _('Edit'), array('id' => $gs1['id']));
    $can[] = xtl::action('gs1-import', _('Import'), array('id' => $gs1['id']));
    $can[] = xtl::action('Gs1::export', _('Export CSV'), array('id' => $gs1['id']));
    return $can;
}


/**
* Creates an gs1
* @param array $gs1 gs1 entity
* @return Returns created gs1 entity
*/
public static function create ($gs1)
{
    sql::begin();
    $gs1['id'] = sql::seq('seq_gs1_id');
    sql::query('gs1')->insert($gs1);
    sql::commit();
}

public function edit($id = null){
    $gs1 = array();

    if (! v::string($id, $id))
		throw new BadRequestException('Invalid gs1 identifier');

    if ($id === null) {

        $gs1 = array(
            'type' => 'gtin-13',
            'prefix' => null,
            'range_from' => null,
            'range_to' => null,
            'last_gtin' => null,
        );
    }
    else {
        $gs1 = self::get($id);
        $gs1['page_title'] = _("Edit gs1");
    }

    // Processing submitted form
	if (nav::isPost() && v::isValid()) {
        ob_start();

		$gs1 = self::validate($_POST, $id);
		if (! v::isValid()) return nav::INVALID;


        if (isset($id)) self::update($id, $gs1);
        else self::create($gs1);

	}

	xtl::template('edit', $gs1);
}


public function editListing ()
{
    $gs1 = search::rows();
	$limit = search::limit();
	$page = search::page();
	$total = search::results();

    if (isset($_GET['export'])) self::export();

	$order = $ordering = array();
	foreach (search::orders() as $o) {
		$ordering[$o->column] = $o->selected;
		$order[$o->column] = htmlspecialchars_decode(search::href(search::ORDER_SELECT, $o));
	}

    $data = array(
        'actions' => array('nge\gs1', 'actions'),
		'gs1' => $gs1,
		'total' => $total,
		'headerDir' => $ordering,
		'headerUrl' => $order,
		'first' => ($page - 1) * $limit + 1,
		'firstPage' => htmlspecialchars_decode(search::href(search::PAGE_FIRST)),
		'last' => $page * $limit > $gs1 ? $gs1 : $page * $limit,
		'lastPage' => htmlspecialchars_decode(search::href(search::PAGE_LAST)),
		'nextPage' => htmlspecialchars_decode(search::href(search::PAGE_NEXT)),
		'prevPage' => htmlspecialchars_decode(search::href(search::PAGE_PREVIOUS)),
		'rowActionsMethod' => array($this, 'actions'),
		//'batchActionsMethod' => array($this, 'actionsBatch'),
    );

    //$barcode = new Barcode(858419100217, 3, app::$root.'/www/FreeSansBold.ttf');
    //dump($barcode);
    xtl::template('edit-listing', $data);
}



/**
* Export product of type csv
* @param int $id The gs1 identifier.
* @throws BadRequestException If error occurs.
*/
public static function export ($id = null)
{
    require_once 'lib/util/csv.php';
    acl::assert('sale.create');

	v::int($_POST['id'], $id, array('required' => true));
	if (! v::isValid() && ! $id) throw new BadRequestException();

    // Get current date
	$date = dt::now()->format('Y-m-d');

    $data = array();

    // Variants and single products that do not have a GTIN code.
    $products = sql::query('v_product_paper_tags')
                ->eq('lang', i18n::$id)
                ->select();

    // Data gs1.
    $gs1 = self::get($id);

    // Array range generate GITIN-13
    if (!empty($gs1['last_gtin']))
        $gitin13 = self::range( (substr_replace($gs1['last_gtin'] ,"", -1) + 1), $gs1['range_to'], count($products));
    else
        $gitin13 = self::range($gs1['range_from'], $gs1['range_to'], count($products));

    if (! empty($products)) {
        foreach ($products as $key => $product) {
            // XXX: Skip variant if variable product is archived
            if ($product['archived'] || $product['variable_archived']) continue;

            $data['GTIN'][] = $gitin13[$key];
            $data['Názov'][] = $product['name'];
            $data['Značka'][] = null;
            $data['Jazyk'][] = null;
            $data['Web'][] = null;
            $data['Obsah'][] = null;
            $data['MJ '][] = null;
            $data['Popis'][] = null;
            $data['Výška'][] = null;
            $data['Šírka'][] = null;
            $data['Hĺbka'][] = null;
            $data['MJ  '][] = null;
            $data['Hmotnosť'][] = null;
            $data['MJ   '][] = null;
            $data['Farba'][] = null;
            $data['Kategória'][] = null;
            $data['Cieľový trh'][] = null;
            $data['Krajina pôvodu'][] = null;
            $data['Recyklovateľnosť obalov'][] = 0;
            $data['Najmenšie objednávkové množstvo'][] = null;
            $data['MJ'][] = null;
            $data['Marketingové info'][] = null;
            $data['Zloženie'][] = null;
            $data['Počet ks'][] = 0;
            $data['Výrobca'][] = null;
            $data['Interné číslo'][] = $product['id'];
            $data['Ostatné'][] = null;
            $data['Externá fotka'][] = null;
        }

    } else {
        print '<div style=" text-align: center; background-color: red; height: 40px; line-height: 40px; width: 50%; margin: auto; border-radius: 4px;">
        <h3>' . _('No product found for gitin code export') . '</h3>
        </div>';
        return;
    }

    // Terminate all output buffering.
    while (ob_get_level()) ob_end_clean();

    // CREATE CSV.
	// Send HTTP headers
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="gs1-' . $date . '.csv');

    $fp = fopen('php://output', 'w');

    $csv = new csvfile($fp, ';');
    $csv->row(array_keys($data));

    for($i = 0; $i < count(current($data)); $i++) {
        $row = array();
        foreach($data as $v) {
            $row[] = $v[$i];
        }
        $csv->row($row);
    }
    $csv->close();

	exit;
}

/**
* Generate barcode
* @param string $code 12 or 13 digit code
* @return no
* @xtl.property cropX="0" cropY="50"
*/
public function generateBarcode ($code)
{
    $cropX = isset($this->cropX) ? (int) $this->cropX : null;
    $cropY = isset($this->cropY) ? (int) $this->cropY : null;

    // Checking the control number.
    if (strlen($code) === 13) {
       $control_number = substr($code, -1);
       if ($control_number != Barcode::generateGITIN13(substr_replace($code, '', -1))) throw new Exception("The control number is incorrect ($control_number)");
    }

    $barcode = new Barcode($code, 3, app::$root.'/www/FreeSansBold.ttf', $cropX, $cropY, Barcode::HORISONTAL);

    $barcode->display();
    //$barcode->save(app::$root.'/var/tmp/barcode.png');
    $barcode->__destruct();

    exit;
}

/**
* Generation GTIN-13
* @param int $ean number value
* @return string Returns GTIN-13.
*/
public static function generateGTIN13 ($ean)
{
    $code = str_pad($ean, 9, '0');
    $weight_flag = true;
    $sum = 0;

    for ($i = strlen($code) - 1; $i >= 0; $i--) {
        $sum += (int)$code[$i] * ( $weight_flag ? 3 : 1 );
        $weight_flag = !$weight_flag;
    }

    return $code .= (10 - ($sum % 10)) % 10;
}

/**
* The function return gs1 data.
* @param int $id id gs1 entity
* @return array $data gs1
*/
public static function get ($id) {
    v::int($id, $id);

    if (! v::isValid()) throw new BadRequestException();

    $data  = sql::query('gs1')->eq('id', $id)->selectOne();
    return $data;
}

/**
* The function return product attribut.
* @param int $id id product entity
* @return array $data attribut
*/
public static function getAttr ($id, $nameAttr) {
    v::int($id, $id);

    if (! v::isValid()) throw new BadRequestException();

    $data = sql::query('product$' . $nameAttr, 'p')
            ->columns('e.name')
            ->eq('p.id', $id)
            ->join('enum_' . $nameAttr, 'e')->on(q::eq('e.id', q::expr('p.' . $nameAttr)))
            ->select();

    return $data;
}

/**
 * Generates contract PDF
 * @param array $data Contract data.
 * @return PDF document as string
 */
public static function generatePDF ()
{
    set_time_limit(0);
    $date = dt::now()->format('Y-m-d');
    // Variants and single products that do not have a GTIN code.
    $products = sql::query('v_product_paper_tags')
                ->eq('lang', i18n::$id)
                ->select();

    // FIXME
    $gs1Id = sql::query('gs1')
            ->select();
    // Data gs1.
    $gs1 = self::get($gs1Id[0]['id']);

    if (empty($products)) {
        print '<div style=" text-align: center; background-color: red; height: 40px; line-height: 40px; width: 50%; margin: auto; border-radius: 4px;">
        <h3>' . _('No products found') . '</h3>
        </div>';
        return false;
    }

    // Array range generate GITIN-13
    if (!empty($gs1['last_gtin'])) $gitin13 = self::range( (substr_replace($gs1['last_gtin'] ,"", -1) + 1), $gs1['range_to'], count($products));
    else $gitin13 = self::range( $gs1['range_from'], $gs1['range_to'], count($products));

    $data = array();
    foreach ($products as $key => $p) {
       // if ($key == 10) break;
        $data[$key]['name'] = strlen($p['name']) <= 32 ? $p['name'] : substr($p['name'], 0, 30) . '...';
        $data[$key]['price'] = 0;
        $data[$key]['size'] = ! empty($size) ?  $size[0]['name'] : null;
        if (array_key_exists($key, $gitin13))
        $data[$key]['barcode'] = nav::urlHost() . '/' . nav::$languages[nav::$lang] . '/gs1-barcode/' . $gitin13[$key];
    }

    $data = array_chunk($data, 10); // row 10

	$scope = array(
		'data' => $data
	);
    //dumpe($data);
	xtl::enter($scope);
	$tpl = xtl::load("gs1-pdf");
	$content = $tpl->toText();
	xtl::leave();

	if (! defined('_MPDF_TEMP_PATH')) define('_MPDF_TEMP_PATH', app::$root.'/var/tmp/');
	if (! defined('_MPDF_TTFONTDATAPATH')) define('_MPDF_TTFONTDATAPATH', app::$root.'/var/tmp/');

	require_once 'lib/3rd/mpdf-8.0/vendor/autoload.php';
	$mpdf = new \Mpdf\Mpdf(array(
		'mode' => 'utf-8',
		'format' => 'A4',
		'margin_left' => 7.5,
		'margin_right' => 7.5,
		'margin_top' => 3,
		'margin_bottom' => 0,
		'margin_header' => 0,
		'margin_footer' => 0,
        'dpi' => 100,
		'orientation' => 'L',
		'tempDir' => app::$root . '/var/tmp/',
	));

    $mpdf->fontdata["opensans"] = array(
        'R'  => "/theme/custom/font/OpenSans-Regular.ttf",
        'B'  => "/theme/custom/font/OpenSans-Bold.ttf",
        'I'  => "/theme/custom/font/OpenSans-LightItalic.ttf",
        'BI' => "/theme/custom/font/OpenSans-Semibolditalic.ttf"
    );

    $mpdf->SetDefaultFont('opensans');
    $mpdf->use_kwt = true;
    $mpdf->autoPageBreak = true;

	$mpdf->allow_charset_conversion = true;
	$mpdf->charset_in ='utf-8';
	$mpdf->list_indent_first_level = 0;
	$mpdf->WriteHTML($content);
	$mpdf->Output();
	//$mpdf->Output(app::$root . '/var/tmp/gs1/barcodePDF/' . $date . '-barcode.pdf', 'F');
}

/**
* Import product of type csv
*/
public function import ($id)
{
    if (! v::string($id, $id)) throw new BadRequestException('Invalid gs1 identifier');
    $gtin = array();
    if (nav::isPost() && v::isValid()) {
        ob_start();

        $date = dt::now()->format('Y-m-d');
        $name = !empty($_FILES['import']['name']) ? $_FILES["import"]["name"] : 'null';

        $target = app::$root . '/var/tmp/gs1/' . $id;

        define('_MPDF_TEMP_PATH', app::$root.'/var/tmp/');
		define('_MPDF_TTFONTDATAPATH', app::$root.'/var/tmp/');

        if (! file_exists($target)) {
            mkdir($target, 0777, true);
            if (! move_uploaded_file($_FILES["import"]["tmp_name"], $target . '/' . $date . '-gs1.csv' )) throw new Exception('The file was not imported (' . $name . ')' );
        } else  if (! move_uploaded_file($_FILES["import"]["tmp_name"], $target . '/' . $date . '-gs1.csv')) throw new Exception('The file was not imported (' . $name . ')' );

        $open = fopen($target . '/' . $date . '-gs1.csv', "r");
        $row = 0;
        $path = app::$root . '/var/tmp/idProduct.txt';
        sql::begin();
        while (($data = fgetcsv($open, 1000, ";")) !== false) {
            if($row != 0) {
                $gtin[$row] = $data[0];
                // Update product column gtin.
                self::updateProductGTIN($data[25], $data[0]);
                file_put_contents($path, "UPDATE product SET gtin = null WHERE id = $data[25] \n", FILE_APPEND | LOCK_EX);
            }
            $row++;
        }
        // Insert last gtin-13 max.
        sql::query('gs1')->eq('id', $id)->update(array('last_gtin' => max($gtin)));

        sql::commit();
        file_put_contents($path, "===============================================================================================\n", FILE_APPEND | LOCK_EX);
        fclose($open);

    }

    xtl::template('import');
}

/**
* The function returns the range from the generated code.
* @param int $range_from range from.
* @param int $range_to range to.
* @param int $count_product specifies the number of code generation.
* @return array Returns the array from the range code.
*/
public static function range ($range_from, $range_to, $count_product)
{
    set_time_limit(0);

    $range_code = array();
    $end = $range_from + $count_product;

    for ($code = $range_from; $code < $end; $code++) {
        if ($range_to < $code) throw new Exception("The given code is out of range" . " (" . $code . "). The allowed range is" . " (" . $range_to . ")" );
        $range_code[] = Barcode::generateGITIN13($code, true); //self::generateGITIN13($code);
    }

    return $range_code;
}

/**
* Displays overview
* @param int $id qs1 id.
*/
public function overview ($id)
{
	v::int($id, $id, 'required');
	if (! v::isValid()) throw new BadRequestException();

    $p = sql::query('product', 'p')
        ->columns('p.id', 'p.code', 'p.gtin', 'p.created', 'p_nls.name', array( 'category_name' => 'pc_nls.name'))
        ->ne('p.gtin', NULL)
        ->eq('p_nls.lang', i18n::$id)
        ->join('category_nls', 'pc_nls')->on(q::eq('p.category', q::expr('pc_nls.id')))
        ->join('product_nls', 'p_nls')->on(q::eq('p.id', q::expr('p_nls.id')))
        ->select();

    $data = array(
        'products' => $p
    );
	xtl::template('overview', $data);
}

/**
* Updates an qs1
* @param int $id qs1 id.
* @param array $qs1 qs1 entity.
* @return Returns updated qs1 entity
*/
public static function update ($id, $qs1) {
    sql::begin();
        sql::query('gs1')
            ->eq('id', $id)
            ->update($qs1);

    sql::commit();
}

/**
* Updates an product column gtin
* @param int $id product id.
* @param int $gtin gtin column value.
* @return Returns updated product entity
*/
public static function updateProductGTIN ($id, $gtin) {
    sql::query('product')
        ->eq('id', $id)
        ->update(array('gtin' => $gtin));
}


/**
 * Validates and sanitizes provided data
 * @param array $input Data to be validated
 * @return array $entity Sanitized entity
 */

public static function validate ($input)
{
    $entity = array();

    // Validation
    v::int($input, $entity, array('required' => true, 'maxlength' => 7), 'prefix');
    v::float($input, $entity, array('required' => true, 'maxlength' => 12), 'range_from');
    v::float($input, $entity, array('required' => true, 'maxlength' => 12), 'range_to');
    v::string($input, $entity, array('required' => false), 'last_gtin');

	return $entity;
}

}

cfg::module('nge\gs1');

?>