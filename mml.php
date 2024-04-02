<?php

namespace nge;
/** @file
 * Multi-media library module.
 * @author Marek Horvath.
 */
require_once 'lib/idx/idx.php';
require_once 'module/language.php';
require_once 'module/feedback.php';

/**
 * Multi-media library module.
 * This module provides multi-media librery related methods.
 * @author Marek Horvath.
 */
class Mml extends module
{

const PRIMARY_CATEGORY = 1200;
const BROWSING_CATEGORY = 1210;

const STATE_NOT_PUBLISHED = 1;
const STATE_PUBLISHED = 2;

/**
 * The template for displaying mml or list of mmls.
 * @var string $template
 * @xtl.property label="Template" type="SelectOne"
 */
public $template;
/**
 * The columns for displaying mml or list of mmls.
 * @var string $columns
 * @xtl.property label="Columns for desktop" type="SelectOne"
 */
public $columns;
/**
 * The number of columns tablet to display on tablet-landscape
 * @var string $columnsTabletLandscape
 * @xtl.property label="Columns for tablet-landscape" type="SelectOne"
 */
public $columnsTabletLandscape;
/**
 * The number of columns tablet to display on tablet-portrait
 * @var string $columnsTabletPortrait
 * @xtl.property label="Columns for tablet-portrait" type="SelectOne"
 */
public $columnsTabletPortrait;
/**
 * The number of columns to display on mobile-landscape
 * @var string $columnsMobileLandscape
 * @xtl.property label="Columns for mobile-landscape" type="SelectOne"
 */
public $columnsMobileLandscape;
/**
 * The number of columns tablet to display mobile-portrait
 * @var string $columnsMobilePortrait
 * @xtl.property label="Columns for mobile-portrait" type="SelectOne"
 */
public $columnsMobilePortrait;
/**
 * The category from which mmls are displayed.
 * @var string $category
 * @xtl.property label="Category" type="CategoryOne"
 */
public $category;
/**
 * The scope of categories from which mml are displayed.
 * @var string $categoryScope
 * @xtl.property label="Include subcategories" type="SelectOne"
 */
public $categoryScope;


public static function actions (& $mml)
{
    $can = array();

    $can[] = xtl::action('mml-detail', _('View'), array('link' => $mml['id']), null, null, array('icon' => 'fa-search'));
    if (acl::can('mml.create'))
	$can[] = xtl::action('mml-edit', _('Edit'), array('id' => $mml['id']));

    if (acl::can('mml.publish')) {
		if ($mml['state'] == self::STATE_NOT_PUBLISHED)
			$can[] =  xtl::action('Mml::publishAction',  _('Publish'), array('id' => $mml['id']));
		else
			$can[] =  xtl::action('Mml::unpublishAction',  _('Unpublish'), array('id' => $mml['id']));
	}

    if (acl::can('mml.create'))
		$can[] = xtl::action('Mml::deleteAction', _('Remove'), array('id' => $mml['id']),
						 _('Are you sure you want to&#160;remove this mml?'));


    return $can;
}

/**
 * Returns available batch actions.
 * @return array Returns an array of XTL actions.
 */
public static function actionsBatch ()
{

	$can = array();

    $can[] = xtl::action('Mml::publishActionBatch', _('Publish selected in listings'), null, null, true);
    $can[] = xtl::action('Mml::unpublishActionBatch', _('Unpublish selected in listings'), null, null, true);
    $can[] = xtl::action('Mml::deleteActionBatch', _('Remove selected in listings'), null, null, true);
	return $can;
}

/**
 * Creates an mml item
 * @param array $mml mml entity
 * @return Returns created mml entity
 */
public static function create ($mml)
{
    sql::begin();

    if (isset($mml['categories'])) {
		$categories = $mml['categories'];
		unset($mml['categories']);
	}

    if (array_key_exists('tags', $mml)) {
		$tags = $mml['tags'];
		unset($mml['tags']);
	}
    sql::query('mml_exif')
		->insert($mml);

    if (! empty($categories)) {
        category::addMemberOrdered('mml', $mml['mml'], $categories['categories']);
	}

    if (isset($tags))
        Tag::processInput($tags, 'mml', $mml['mml'], false);

    sql::commit();

    idx::insert('sql', 'mml', array('id' => $mml['mml']));
}

/**
 * Creates mml on import
 * @param string $path path img
 * @param int $category mml primary category
 * @param array $categories mml categories
 * @param array $exif exif data
 * @param int $state state mml
 * @param int $type type mml
 */
public function createImportMML ($path, $category, $categories, $exif, $state, $type, $attributes)
{
    sql::begin();

    $mml = array (
        'id' => sql::seq('seq_mml_id'),
        'path' => $path,
        'type' => $type,
        'state' => isset($state) ? $state : 1,
        'category' => $category,
        'featured' => true,
        'uid' => user::$id,
        'gid' => user::$group,
        'access' => 1,
        'created' => date ('Y-m-d H:i:s'),
        'modified' => date ('Y-m-d H:i:s'),
        'name' => '',
        'description' => '',
    );

    try {
        list($id) = sql::query('mml')->nls('name', 'description')
            ->timestamps()->acl('mml')
            ->insert($mml, 'id');
    }
    catch (UniqueConstraintException $e) {
        // $mess = _('The picture must not be called the same. ' . $path);
        return;
    }
    if (!empty($attributes)) attr::createValues('mml', $attributes, $id);


    sql::commit();

     // Create folder.
    $folder = new t_folder();
    $folder->access = acl::ACCESS_PUBLIC;

	// Create content
	// Read new mml template from repository
	if (isset($template) && repo::exists('template', '/content/prebuilt-blocks/mml/'. $template)) {
		$content = repo::document('template', '/content/prebuilt-blocks/mml/'. $template);
        $content->access = acl::ACCESS_PUBLIC;
	}
	// Default template fallback
	else {
        $content = new t_content();
        $content->access = acl::ACCESS_PUBLIC;
        $text = new xtlxml();
        $text->xml = '<div class="ice-dummy">'. _('Insert mml content'). '</div>';
        $content->_kids[] = $text;
    }


    $mml_exif = array (
        'mml' =>  $id,
        'categories' => !empty($categories) ? $categories : array(),
        'title' => null,
        'subject' => null,
        'rating' => null,
        'tags' => null,
        'authors' => null,
        'comments' => null,
        'copyright' => isset($exif['Copyright']) ? $exif['Copyright'] : null,
        'program_name' => isset($exif['Software']) ? $exif['Software'] : null,
        'camera_maker' => isset($exif['Make']) ? $exif['Make'] : null,
        'camera_model' => isset($exif['Model']) ? $exif['Model'] : null,
        'lens' => isset($exif['UndefinedTag:0xA434']) ? $exif['UndefinedTag:0xA434'] : null,
        'focal_length' => isset($exif['FocalLength']) ? $exif['FocalLength'] : null,
        'exposure_time' => isset($exif['ExposureTime']) ? $exif['ExposureTime'] : null,
        'exposure_bias' => isset($exif['ExposureBiasValue']) ? $exif['ExposureBiasValue'] : null,
        'f_stop' => isset($exif['f_stop']) ? $exif['f_stop'] : null,
        'iso_speed' => isset($exif['ISOSpeedRatings']) ? $exif['ISOSpeedRatings'] : null,
        'flash' => isset($exif['Flash']) ? $exif['Flash'] : null,
        'metering_mode' => isset($exif['MeteringMode']) ? $exif['MeteringMode'] : null,
        'taken' => isset($exif['DateTime']) ? $exif['DateTime'] : null,
    );

    self::create($mml_exif);
}

/**
 * Delete mml action
 * @throws BadRequestException On invalid identifier.
 */
public function deleteAction ()
{
	if (! v::int($_POST['id'], $id, 'required'))
		throw new BadRequestException();

	self::delete($id);
}

public static function delete ($id) {
    v::int($id, $id, array('required' => true));
	if (! v::isValid()) throw new BadRequestException();

	sql::begin();

    sql::query('mml')->acl('mml')->eq('id', $id)->delete();

	sql::commit();

    idx::delete('sql', 'mml', array('id' => $id));
}

/**
 * Deletes multiple mml.
 * @throws BadRequestException On invalid request.
 */
public function deleteActionBatch ()
{
	if (! isset($_POST['ids'])) throw new BadRequestException();
	$ids = explode(',', $_POST['ids']);

	foreach ($ids as $id) {
		$id = (int) $id;
		self::delete($id);
	}
}
/**
 * Displays detail of mml.
 * @return No value is returned.
 * @xtl.property template="default"
 */
public function detail()
{
   
	$m = self::getByArguments();
	if (! $m) throw new NotFoundException();

    $listingPath =  parse_url(nav::hrefAbs('mml-listing'));

    if (! array_key_exists('path', $listingPath)) $listingPath = null;

    $type = self::getMmlNlsType();
    $template = isset($this->template) ? $this->template : 'default';
    $m['template'] = $template;

    $m['tags'] = !empty( $m['tags']) ? explode(',', $m['tags']) : null;
    $m['tags_listing_path'] = $listingPath['path'];
    $m['type_name'] = $type[$m['type']];
    $m['type_name'] = $type[$m['type']];

    $m['download_content_length'] = convertBytes(self::getFileSize(nav::urlHost() . xtl::streamUrlOf($m['path'])), 'MB', 2, true);

    $attributes = attr::valuesOf('mml', $m);
    $m['attributes'] = $attributes;

	xtl::template('detail/'. $template, $m);
}

public function edit ($id = null) {
    acl::assert('mml.create');

    if (! v::int($id, $id))
		throw new BadRequestException('Invalid multimedia library identifier');

    if ($id === null) {

        $data = array(
            'mml' => null,
            'title' => null,
            'name' => null,
            'subject' => null,
            'authors' => null,
            'description' => null,
            'comments' => null,
            'tags' => null,
            'copyright' => null,
            'program_name' => null,
            'camera_maker' => null,
            'camera_model' => null,
            'lens' => null,
            'focal_lenght' => null,
            'exposure_bias' => null,
            'exposure_time' => null,
            'f_stop' => null,
            'iso_speed' => null,
            'flash' => null,
            'metering_mode' => null,
            'attributes' => array(),
        );
    }
    else {
        $data = self::get($id);
        $data['page_title'] = _("Edit multimedia library");
    }

	// Get enabled languages
	$data['languages'] = Language::getItems();

    // Get categories from primary tree
	$data['primary_categories'] = tree::toOptions(category::getAllPrimary('mml'));
	$data['primary_categories'][self::PRIMARY_CATEGORY] = _('Default');

	// Get other categories
	$data['additional_categories'] = tree::fromArray(category::getAllAdditional('mml'), 'category');

    // Get other categories
	$data['additional_categories'] = tree::fromArray(category::getAllAdditional('mml'), 'category');

    $data['attributes'] = attr::valuesOf('mml', $data);

    // Processing submitted form
	if (nav::isPost() && v::isValid()) {
        ob_start();
		$mml = self::validate($_POST, $id);
		if (! v::isValid()) return nav::INVALID;

        $exists = sql::query('mml_exif')
            ->columns('mml')
            ->eq('mml', $id)
            ->select();

        if (! empty($exists)) {
            if (array_key_exists('attributes', $_POST)) {
                $mml['attributes'] = $_POST['attributes'];
            }
            else {
                $mml['attributes'] = null;
            }

            self::update($id, $mml);
        } else {
            $mml['mml'] = $id;
            self::create($mml);
        }

	}

    xtl::template('edit-details', $data);
}

public function editListing ()
{
    $mml = search::rows();
	$limit = search::limit();
	$page = search::page();
	$total = search::results();

    if (isset($_GET['export'])) self::export();

	static $state_icons = array(null,
		'icon-forbidden red',	// Not published
		'icon-tick green'		// Published
	);

    $languages = i18n::languages(i18n::$lang);

    foreach ($mml as & $m) {
        //$ids[] = $m['id'];
        $m['language'] = $languages[$m['lang']]['language'];
		$m['primary_category_name'] = ($m['primary_category'] == self::PRIMARY_CATEGORY)
										? _('Mml') : $m['primary_category_name'];
        $m['state_icon'] = $state_icons[$m['state']];
        $m['featured_icon'] = $m['featured'] ? 'fa fa-star-o' : '-';
        $m['categories'] = implode(', ', rowset::collect($m['categories'], 'category_name'));
    }

    unset($m);

	$order = $ordering = array();
	foreach (search::orders() as $o) {
		$ordering[$o->column] = $o->selected;
		$order[$o->column] = htmlspecialchars_decode(search::href(search::ORDER_SELECT, $o));
	}


    $data = array(
        'actions' => array('nge\mml', 'actions'),
		'mml' => $mml,
		'total' => $total,
		'headerDir' => $ordering,
		'headerUrl' => $order,
		'first' => ($page - 1) * $limit + 1,
		'firstPage' => htmlspecialchars_decode(search::href(search::PAGE_FIRST)),
		'last' => $page * $limit > $mml ? $mml : $page * $limit,
		'lastPage' => htmlspecialchars_decode(search::href(search::PAGE_LAST)),
		'nextPage' => htmlspecialchars_decode(search::href(search::PAGE_NEXT)),
		'prevPage' => htmlspecialchars_decode(search::href(search::PAGE_PREVIOUS)),
		'rowActionsMethod' => array($this, 'actions'),
		'batchActionsMethod' => array($this, 'actionsBatch'),
    );

    xtl::template('edit-listing', $data);
}

/**
 * Lists mml data by tag.
 * @return Returns If the search is successful, it returns the data, otherwise it returns false.
 */
public static function listingTag () {
    $url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url =  parse_url($url);

    if ( array_key_exists('query', $url)) {
        $query = explode('&', $url['query']);
        // Get tag from url.
        $query = array_map(function($item) {
            $pos = strpos($item, 'tags');
            if ($pos !== false) {
            return array('tags' => explode('=', $item));
            }
        },$query);

        $query = array_filter($query);

        if (!empty($query)) {

            $mml = idx::query('mml')
                ->lang(i18n::$id)
                ->match($query[0]['tags'][1], true)
                ->columns('*')
                ->desc('rank');
            $mml = idx::search('mml', $mml);

        } else $mml = false;

    } else $mml = false;

    return $mml;
}


/**
 * Returns variables for dynamic fragment on category change.
 * @param xtlevt The XTL event.
 * @param array $form The submitted form.
 * @param string $id The product identifier or null when adding new product.
 * @throws BadRequestException If error occurs.
 * @return Returns an array.
 */
public function editOnCategoryChange (xtlevt $event, $form, $id = null)
{
	v::int($form, $category, 'required', 'category');
	if (! v::isValid()) throw new BadRequestException();
	attr::submit('mml', $form, $form);

	if (! isset($form['variable_product'])) $form['variable_product'] = false;

	// Product can be variable
	$varyOptions = array();
	if ($form['attributes']) {
		foreach ($form['attributes']->names() as $name)
			if (self::isVariantAttribute($form['attributes']->meta($name)))
				$varyOptions[] = array(
					'label' => $form['attributes']->label($name),
					'value' => $name
				);
	}
	$form['varyOptions'] = $varyOptions;

	// Reset varies on change of category or variable product option.
	if ($event->target == 'category' )
		$form['varies'] = array();

	// If editing existing mml try to use existing attribute values.
	if (isset($id)) {
		v::int($id, $id, 'required');
		if (! v::isValid()) throw new BadRequestException();

		$data = array(
			'id' => $id,
			'category' => $category
		);

		$form['attributes'] = attr::valuesOf('mml', $data);
	}


	return $form;
}



/**
 * Exports module data in various formats
 * @param array $data Array with data (t:search is used if null)
 * @param string $type Type of export (xls)
 */
public static function export ($data = null, $type = 'xls')
{
	// Export all filtered items
	if(! $data) $data = search::rows(true);

	// Get current date
	$date = dt::now()->format('Y-m-d');

	// Switch export type
	switch ($type) {
		case 'xls':
			// Columns mapping
			$columns = array(
				'id' => _('ID'),
				'path' => _('Path'),
                'name' => _('Name'),
                'description' => _('Description'),
                'subject' => _('Subject'),
                'rating' =>_('Rating'),
                'tags' => _('Tags'),
                'comments' => _('Comments'),
                'copyright' => _('Copyright'),
                'primary_category_name' => _('Primary categories'),
                'program_name' => _('Program name'),
                'camera_maker' => _('Camera maker'),
                'camera_model' => _('Camera model'),
                'lens' => _('Lens'),
                'focal_length' => _('Focal length'),
                'exposure_time' => _('Exposure time'),
                'exposure_bias' => _('Exposure bias'),
                'f_stop' => _('f stop'),
                'iso_speed' => _('ISO speed'),
                'flash' => _('Flash'),
                'metering_mode' => _('Metering mode'),
                'taken' => _('Taken'),
                'created' => _('Created'),
                'modifed' => _('Modifed'),
                'featured' => _('Featured'),
                'state' => _('Published'),

			);

			return search::exportXls($data, $columns, array(), 'Multimedia-library', 'multimedia-library-'.$date);
			break;
	}
}


/**
 * Returns mml entity.
 * @param int $id MMl identifier.
 * @param string $lang The language tag.
 * @return Returns an mml entity
 * @throws BadRequestException On invalid request.
 * @throws NotFoundException If there is no such mml.
 */
public static function get ($id, $lang = null)
{
	v::int($id, $id);

	if (! v::isValid()) throw new BadRequestException();

	if ($lang === null) $lang = i18n::$lang;
	$mml = sql::query('v_mml_detail')
                ->acl('mml')
				->eq('id', $id)
                ->eq('lang', i18n::$id)
				->selectOne();
	if (! $id)  throw new NotFoundException('Invalid MML identifier');

	$mml['categories'] = sql::query('mml_category')
							 ->eq('mml', $id)
							 ->selectArray('category');

	$mml['tags'] = implode(',', Tag::getAssigned($lang, 'mml', $id));

	return $mml;
}


/**
 * Returns mml entity based on navigation arguments.
 * @return Returns associative array of mml entity.
 * @throws NotFoundException If there is no such mml.
 * @throws BadRequestException On invalid request.
 */
public static function getByArguments ()
{
	static $mml;

	if (isset($mml)) return $mml;

	if (! nav::$args)
		throw new BadRequestException('Missing mml link argument');

	list($id) = nav::$args;

	// Get mml.
	$m = sql::query('v_mml_detail', 'm');
	$m->con(q::eq('m.lang', i18n::$id), q::eq('m.id', $id));
	$m = $m->selectOne();

	if (! $m) throw new NotFoundException('No such mml');

	// Only authorized users can view not published mml.
	if ($m['state'] == self::STATE_NOT_PUBLISHED)
		acl::assertAny('mml.create', 'mml.publish');

    $type = self::getMediaType(str_replace('content:', '', $m['path']));
    $m['streamext'] = $type['streamext'];

	// Get content URI.
	$m['content'] = xtl::deref('content', 'content:/content/mml/'.$m['id']);


	// Get additional categories.
	$m['categories'] = sql::query('mml_category', 'mc')
							->join('category_nls', 'c_nls')->on(q::eq('c_nls.id', q::expr('category')))
							->eq('mml', $m['id'])
							->eq('c_nls.lang', i18n::$id)
							->selectArray('c_nls.name', 'category');

	return $mml = $m;
}

/**
 * Returns data report metaData type.
 * @return array data.
 */
public static function getMmlNlsType ()
{
	$data = sql::query('mml_type_nls')->eq('lang', i18n::$id)->select();
	foreach ($data as  $d)
		$data[$d['id']] =  $d['name'];

	unset($data[0]);
	return $data;
}

public static function getMediaType ($path)
{
    $obj = repo::document('content', $path);
    $data = array();

    if ($obj instanceof t_audio) {
        if (isset($obj->streamext)) {
            $data['type'] = 'audio';
            $data['streamext'] = $obj->streamext;
        } else {
               $data['type'] = 'm3u';
        }
    } elseif ($obj instanceof t_img) {
        $data['type'] = 'image';
        $data['streamext'] = isset($obj->streamext) ? $obj->streamext : null;
    } elseif ($obj instanceof t_video) {
        if (isset($obj->streamext)) {
            $data['type'] = 'video';
            $data['streamext'] = $obj->streamext;
        } else {
            // Checks if the video is from youtube.
            if(strpos($obj->url, 'http://www.youtube.com') !== false) {
               $data['type'] = 'youtobe';
            }
        }
    } else {
         $data['type'] = 'document';
         $data['streamext'] = isset($obj->streamext) ? $obj->streamext : null;
    }

    return $data;
}

/**
 * Returns exif data.
 * Only supports jpg/jpeg/TIFF
 * @param string $path The path images (content:/media/XXXX).
 * @param string $tag  ANY_TAG, IFD0, THUMBNAIL, EXIF
 * @param bool $as_arrays Specifies whether or not each section becomes an array.
 * @return Returns an exif data image
 */
public static function getExif ($path, $tag = null, $as_arrays = true) {

    $img = xtl::streamUrlOf($path); //'content:/media/test'

    $pathImg = nav::urlHost() . $img;

    // Checks is image type jpg/jpeg.
    if (exif_imagetype($pathImg) == IMAGETYPE_JPEG) return @exif_read_data($pathImg, $tag, $as_arrays);

    return false;
}

/**
 * Returns size file.
 * @param string $url The url media.
 * @return float size file.
 */
public function getFileSize( $url ) {
    $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, TRUE);
     curl_setopt($ch, CURLOPT_NOBODY, TRUE);

     $data = curl_exec($ch);
     $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

     curl_close($ch);
     return $size;
}


/**
 * Returns value from object property.
 * @param object $object
 * @param string $property value which is a property.
 * @return string value
 */
public static function getProperty($object, $property) {
    $array = (array) $object;

    foreach ($array as $key => $value) {
        $propertyNameParts = explode("\0", $key);
        $propertyName = end($propertyNameParts);
        if ($propertyName === $property) {
            return $value;
        }
    }
}

public static function privileges ()
{
    // Definition: 'privilege' => [ 'name', 'description', [ 'inherit', ... ] ]
    return array(
        'mml.access.public' => array(
            _('View public multimedia'),
            _('This privilege grants right to view public multimedia.')
        ),
        'mml.access.group' => array(
            _('View group multimedia'),
            _('This privilege grants right to view group multimedia.')
        ),
        'mml.access.any' => array(
            _('View any multimedia'),
            _('This privilege grants unrestricted right to view any multimedia.')
        ),
        'mml.create.public' => array(
            _('Create public multimedia'),
            _('This privilege grants right to create public multimedia.'),
            array('mml.access.public')
        ),
        'mml.create.any' => array(
            _('Create any multimedia'),
            _('This privilege grants unrestricted right to create any multimedia.'),
            array('mml.access.any')
        ),
        'mml.publish.public' => array(
            _('Publish public multimedia'),
            _('This privilege grants right to publish public multimedia.'),
            array('mml.create.public')
        ),
        'mml.publish.any' => array(
            _('Publish any multimedia'),
            _('This privilege grants unrestricted right to publish any multimedia.'),
            array('mml.create.any')
        )
    );
}

public function import () {
    $mess = array();

    if(nav::isPost() && v::isValid()) {
        ob_start();
        $mml_categories = array();

        if (empty($_POST['file_value']) && empty($_POST['folder_value']) ) {
            v::string($_POST['file_value'], $import_file, array('required' => true), 'file_value');
            v::string($_POST['folder_value'], $import_folder, array('required' => true), 'folder_value');

        } else {
            v::string($_POST['file_value'], $import_file, array('required' => false), 'file_value');
            v::string($_POST['folder_value'], $import_folder, array('required' => false), 'folder_value');
        }

        v::string($_POST['category'], $category, array('required' => true), 'category');
        v::int($_POST['state'], $state, array('default' => 1), 'state');

        if (isset($_POST['categories'])) {
            if (is_array($_POST['categories'])) {
                $mml_categories['categories'] = array();
                foreach ($_POST['categories'] as $cid)
                    if (is_int($cid) || v::int($cid, $cid, 'required', 'categories'))
                    $mml_categories['categories'][] = $cid;

            } else v::msg(v::E_INVALID, _('Value must of type array'), 'categories');
        }

        if (array_key_exists('attributes', $_POST)) {
            foreach ($_POST['attributes'] as $key => $attribute) {
                if ( !empty($attribute) ) {
                    $attributes[$key] = $attribute;
                } else  $attributes = null;
            }
        } else $attributes = null;
		if (! v::isValid()) return nav::INVALID;

        $data['category'] = $category;
        // Import folder
        if (isset($import_folder)) {
            /*$folders = repo::getDescendants('content', str_replace('content:', '', $import_folder));
            foreach ($folders as $f) {
                if ($f['type'] != 'img') continue;
                $exif = self::getExif('content:' . $f['path'], null, false);

                if (! $exif) {
                    // $mess = _('The image must be of the jpg type. ' . $f['path']);
                    continue;
                } else {
                    self::createImportMML('content:' . $f['path'], $category, $mml_categories , $exif, $state);
                }
            }*/

            $import_folders = explode(',', $import_folder);
            foreach ($import_folders as $import_folder) {
                if (empty($import_folder)) continue;

                    $type_data = self::getMediaType(str_replace('content:', '', $import_folder));
                    if($type_data['type'] == 'youtobe') continue;
                    if($type_data['type'] == 'm3u') continue;

                    if ($type_data['type'] == 'image') {
                        $exif = self::getExif('content:' . $import_folder, null, false);
                        if (! $exif) {
                            self::createImportMML('content:' . $import_folder, $category, $mml_categories, $exif, $state, array_search(  ucfirst($type_data['type']), self::getMmlNlsType()), $attributes );
                            // $mess = _('The image must be of the jpg type. ' . $import_folder);
                            continue;
                        } else {
                            self::createImportMML('content:' . $import_folder, $category, $mml_categories, $exif, $state, array_search(  ucfirst($type_data['type']), self::getMmlNlsType()), $attributes );
                        }
                    } else {
                        self::createImportMML('content:' . $import_folder, $category, $mml_categories, array(), $state, array_search(  ucfirst($type_data['type']), self::getMmlNlsType()), $attributes );
                    }
            }


        }

        // Import file
        if (isset($import_file)) {
            $import_files = explode(',', $import_file);
            foreach ($import_files as $import_file) {
                $type_data = self::getMediaType(str_replace('content:', '', $import_file));

                if($type_data['type'] == 'youtobe') continue;
                if($type_data['type'] == 'm3u') continue;

                if ( $type_data['type'] == 'image') {
                    $exif = self::getExif($import_file, null, false);
                    if (! $exif) {
                        self::createImportMML('content:' . $import_folder, $category, $mml_categories, $exif, $state, array_search(  ucfirst($type_data['type']), self::getMmlNlsType()), $attributes );
                        // $mess = _('The image must be of the jpg type. ' . $import_file);
                        continue;
                    } else {
                        self::createImportMML($import_file, $category, $mml_categories, $exif, $state, array_search( ucfirst($type_data['type']), self::getMmlNlsType()), $attributes );
                    }
                } else {
                    self::createImportMML($import_file, $category, $mml_categories, array(), $state, array_search(  ucfirst($type_data['type']), self::getMmlNlsType()), $attributes );
                }
            }
        }
    }

    $data = array(
        'mess' => $mess
    );
    // Get categories from primary tree
	$data['primary_categories'] = tree::toOptions(category::getAllPrimary('mml'));
	$data['primary_categories'][self::PRIMARY_CATEGORY] = _('Default');

	// Get other categories
	$data['additional_categories'] = tree::fromArray(category::getAllAdditional('mml'), 'category');

    // Get other categories
	$data['additional_categories'] = tree::fromArray(category::getAllAdditional('mml'), 'category');

    $data['category'] = self::PRIMARY_CATEGORY;

    $data['attributes'] = attr::valuesOf('mml', $data);

    xtl::template('import', $data);

}

/**
 * Determines whether attribute is suitable as variant attribute.
 * @param array $attr The attribute entity.
 * @return Returns true if attribute is suitable, false otherwise.
 */
public static function isVariantAttribute ($attr)
{
	return ($attr['type'] == attr::TYPE_ENUM // Enumeration type.
			|| $attr['type'] == attr::TYPE_TIMESTAMP)
		   && ! $attr['multilingual'] // Non-NLS attribute.
		   && $attr['size'] == 0; // Not an array.
}

/**
 * Displays list of mml from specific category.
 * @return No value is returned.
 * @xtl.property category="1200" categoryScope="self" template="default" columns="4" columnsTabletLandscape="4" columnsTabletPortrait="4" columnsMobileLandscape="2" columnsMobilePortrait="1"
 */
public function listing ()
{
    $category = isset($this->category) ? (int) $this->category : null;

    // Get mml
    $mml = sql::query('v_mml_list_by_category')
        ->acl('mml')
        ->eq('lang', i18n::$id)
		->eq('state', self::STATE_PUBLISHED)
        ->eq('category', $category)
        ->select();

    if ($mml) {
        $template = isset($this->template) ? $this->template : 'default';
        $data = self::processListingData($mml, $this);

        xtl::template('listing/' . $template, $data);

    } else xtl::template('listing/no-results');
}

/**
 * Displays list of mml using search API.
 * @return No value is returned.
 * @xtl.property template="default" columns="4" columnsTabletLandscape="4"  columnsTabletPortrait="4" columnsMobileLandscape="2" columnsMobilePortrait="1"
 * @xtl.method context="args"
 * @xtl.provides langAlternatives
 */
public function listByCategory ()
{
    if (self::listingTag() !== false)  $mml = self::listingTag();
    else $mml = search::rows();

    if ($mml) {
        $template = isset($this->template) ? $this->template : 'default';
        $data = self::processListingData($mml, $this);
        $data['template'] = $template;
        xtl::template('listing/' . $template, $data);
    } else xtl::template('listing/no-results');
}

public static function processListingData ($data, $properties)
{
    $template = isset($properties->template) ? $properties->template : 'default';

    // Column count.
	if (! $properties->columns) $properties->columns = '1';
	$gridClass = '';

  /*  if ($properties->columns == 2) $gridClass = 'grid-one-half';
	if ($properties->columns == 3) $gridClass = 'grid-one-third grid-one-half-mobile-landscape grid-one-half-tablet-portrait';
	if ($properties->columns == 4) $gridClass = 'grid-one-fourth';
	if ($properties->columns == 5) $gridClass = 'grid-one-fifth grid-one-half-mobile-landscape grid-one-half-tablet-portrait';
*/

	// Flex grid settings
	$deviceSuffixes = array( 	// device-suffx => property name
		'columns' => '',
		'columnsTabletLandscape' => '-tablet-landscape',
		'columnsTabletPortrait' => '-tablet-portrait',
		'columnsMobileLandscape' => '-mobile-landscape',
		'columnsMobilePortrait' => '-mobile-portrait' ,
	);

	// Backwards compatibility values
	$gridClasses = array( 	// columns => desktop-classes, tablet-landscape-classes, tablet-portrait, mobile-classes
		1 => array('grid-1-col', '', '', '', ''),
		2 => array('grid-2-col', 'grid-2-col-tablet-landscape', 'grid-1-col-tablet-portrait', 'grid-1-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
		3 => array('grid-3-col', 'grid-3-col-tablet-landscape', 'grid-2-col-tablet-portrait', 'grid-2-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
		4 => array('grid-4-col', 'grid-4-col-tablet-landscape', 'grid-2-col-tablet-portrait', 'grid-2-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
		5 => array('grid-5-col', 'grid-5-col-tablet-landscape', 'grid-2-col-tablet-portrait', 'grid-2-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
		6 => array('grid-6-col', 'grid-6-col-tablet-landscape', 'grid-4-col-tablet-portrait', 'grid-4-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
		7 => array('grid-7-col', 'grid-7-col-tablet-landscape', 'grid-4-col-tablet-portrait', 'grid-4-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
		8 => array('grid-8-col', 'grid-8-col-tablet-landscape', 'grid-4-col-tablet-portrait', 'grid-4-col-mobile-landscape', 'grid-1-col-mobile-portrait'),
	);

	$gridClass = '';

	$i = 0;
	foreach ($deviceSuffixes as $propertyName => $suffix) {
		// Use user provided value
		if (! empty($properties->{$propertyName})) {
			$gridClass .= ' ' . $gridClasses[$properties->{$propertyName}][0] . $suffix;
		}
		// Use backwards compatibility value
		else {
			$gridClass .= ' ' . $gridClasses[$properties->columns][$i];
		}
		$i++;
	}
	$gridClass = ltrim($gridClass, ' ');


    $items = $columns = array();
    $column = 0;
    $i = 1;

    foreach ($data as & $d) {
        $i++;
        // Set grid column class
		$d['gridClass'] = $gridClass;
        $type = self::getMediaType(str_replace('content:', '', $d['path']));
        $d['streamext'] = $type['streamext'];
        $d['type_name'] = sql::query('mml_type_nls')->columns('name')->eq('id', $d['type'])->eq('lang', i18n::$id)->select();

        $d['type_name'] =  $d['type_name'][0]['name'];

		// Set responsive "new-row" classes
		$d['gridNewRowClass'] = '';
        if($i % 2 == 0) $d['gridNewRowClass'] = 'grid-row-mobile-landscape grid-row-tablet-portrait';
		if(($i -2) % ($properties->columns) == 0) $d['gridNewRowClass'] .= ' grid-row-tablet-landscape';

        $items[] = $columns[] = $d;
        if (++$column >= $properties->columns) {
            $rows[] = $columns;
            $column = 0;
            $columns = array();
        }
    }
    unset($d);
    if ($columns) {
		$rows[] = $columns;
	}

    $data = array(
		'items' => $items,
        'rows' => isset($rows) ? $rows : array(),
        'gridClass' => $gridClass,
		'columns' => $properties->columns,
        'columnsTabletLandscape' => $properties->columnsTabletLandscape,
		'columnsTabletPortrait' => $properties->columnsTabletPortrait,
		'columnsMobileLandscape' => $properties->columnsMobileLandscape,
		'columnsMobilePortrait' => $properties->columnsMobilePortrait,
        'template' => $template,
	);

    return $data;
}

/**
 * Publish mml.
 * @return No value is returned.
 * @throws BadRequestException On invalid identifier.
 */
public function publishAction ()
{
	if (! v::int($_POST['id'], $id, 'required'))
		throw new BadRequestException();

	self::publish($id);
}

public static function publish ($id)
{
	acl::assert('mml.publish');

	v::int($id, $id, array('required' => true));
	if (! v::isValid()) throw new BadRequestException();

	$mml = array(
		'state' => self::STATE_PUBLISHED
	);

	sql::begin();
	sql::query('mml')->timestamps()->acl('mml')
		->eq('id', $id)->update($mml);
	sql::commit();

}

/**
 * Publish multiple mml.
 * @throws BadRequestException On invalid request.
 */
public function publishActionBatch ()
{
	if (! isset($_POST['ids'])) throw new BadRequestException();
	$ids = explode(',', $_POST['ids']);

	foreach ($ids as $id) {
		$id = (int) $id;
		self::publish($id);
	}
}

/**
 * Returns module property metadata.
 * @param string $name The property name.
 * @param string $function The module function.
 * @param array $args An optional context arguments.
 * @return Returns array of property metadata.
 */
public static function property ($name, $function, $args = array())
{
	$prop = parent::property($name, $function);

	switch ($name) {
        case 'category':
            $roots = array();
            foreach (Category::getAdditionalRoot('mml') as $root)
                $roots[] = $root['id'];

            $prop += array(
                'root' => implode(',', $roots)
            );
            break;
        case 'categoryScope':
            $prop += array(
                'items' => array(
                    'self' => _('No'),
                    'selfAndDescendants' => _('Yes')
                )
            );
            break;
        case 'template':
            $prop += array(
                'items' => decode(
                    $function,
                    'detail', array(
                        'default'  => _('Default'),
                        'test'  => _('Test')
                    ),
                    'listing', array(
                        'default'  => _('Default'),
                        'masonry'  => _('Masonry'),
                        'carousel'  => _('Carousel'),
                        'left-thumbnail'  => _('Left thumbnail'),
                        'table'  => _('Table'),
                        'minimalistic' => _('Minimalistic'),
                    ),
                    'listByCategory', array(
                        'default'  => _('Default'),
                        'masonry'  => _('Masonry'),
                        'carousel'  => _('Carousel'),
                        'left-thumbnail'  => _('Left thumbnail'),
                        'table'  => _('Table'),
                        'minimalistic' => _('Minimalistic'),
                    )
                )
            );
            break;
        case 'columns':
        case 'columnsTabletLandscape':
        case 'columnsTabletPortrait':
        case 'columnsMobileLandscape':
        case 'columnsMobilePortrait':
		    $prop += array(
                'items' => array(
                    1 => '1-column',
                    2 => '2-columns',
                    3 => '3-columns',
                    4 => '4-columns',
                    5 => '5-columns',
                    6 => _('6-columns'),
                    7 => _('7-columns'),
                    8 => _('8-columns')
			    )
            );
            break;
    }
    return $prop;
}

/**
 * Updates an mml
 * @param int $id mml id.
 * @param array $mml MML entity.
 * @return Returns updated mml entity
 */
public static function update ($id, $mml) {
    sql::begin();
    $catchg = attr::getCategoryChange('mml', $mml, $id);
   
    if (isset($mml['categories'])) {
		$categories = $mml['categories'];
		unset($mml['categories']);
	}

    if (isset($mml['category'])) {
		$category = $mml['category'];
		unset($mml['category']);
	}

    if (isset($mml['attributes'])) {
		$attributes = $mml['attributes'];
		unset($mml['attributes']);
	}

    if (isset($mml['description'])) {
		$description = $mml['description'];
		unset($mml['description']);
	} else {
        $description = null;
		unset($mml['description']);
    }

    if (isset($mml['name'])) {
		$name = $mml['name'];
		unset($mml['name']);
	} else {
        $name = null;
		unset($mml['name']);
    }
    

    if (array_key_exists('tags', $mml)) {
		$tags = $mml['tags'];
	}

    if (isset($attributes) || $catchg) {
        if (! isset($attributes)) $attributes = array();
		    attr::updateValues('mml', $attributes, $id, $catchg);

       /* if (!isset($catchg)) {
            attr::createValues ('mml', $attributes, $id);
        }*/
        
	}

    sql::query('mml')->nls('name', 'description')
        ->timestamps()->acl('mml')
        ->eq('id', $id)
        ->update(array('category' => $category));

    sql::query('mml_nls')
		->eq('id', $id)
        ->eq('lang', i18n::$id)
		->update(array('name' => $name, 'description' => $description));

    unset($mml['attributes']);
    sql::query('mml_exif')
		->eq('mml', $id)
		->update($mml);

    if (! empty($categories)) {
		sql::query('mml_category')->eq('mml', $id)->delete();
        category::addMemberOrdered('mml', $id, $categories);
		/*foreach ($categories as $cid)
			sql::query('mml_category')
				->insert(array('mml' => $id, 'category' => $cid));*/
	}
	else {
		sql::query('mml_category')->eq('mml', $id)->delete();
	}

    if (isset($tags))
		    Tag::processInput($tags, 'mml', $id, true);

    sql::commit();

    idx::update('sql', 'mml', array('id' => $id));
}

/**
 * Unpublishes mml.
 * @return No value is returned.
 * @throws BadRequestException On invalid identifier.
 */
public function unpublishAction ()
{
	if (! v::int($_POST['id'], $id, 'required'))
		throw new BadRequestException();

	self::unpublish($id);
}

public static function unpublish ($id)
{
	acl::assert('mml.publish');

	v::int($id, $id, array('required' => true));
	if (! v::isValid()) throw new BadRequestException();

	$mml = array(
		'state' => self::STATE_NOT_PUBLISHED
	);

	sql::begin();
	sql::query('mml')->timestamps()->acl('mml')
		->eq('id', $id)->update($mml);
	sql::commit();
}

/**
 * Unpublishes multiple mml.
 * @throws BadRequestException On invalid request.
 */
public function unpublishActionBatch ()
{
	if (! isset($_POST['ids'])) throw new BadRequestException();
	$ids = explode(',', $_POST['ids']);

	foreach ($ids as $id) {
		$id = (int) $id;
		self::unpublish($id);
	}
}

/**
 * Validates and sanitizes provided data
 * @param array $input Data to be validated
 * @param int $id ID of entity (required when updating)
 * @return array $entity Sanitized entity
 */

public static function validate ($input, $id = null)
{
    $entity = array();

    // Validation
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'title');
    v::string($input, $entity, array('required' => true, 'maxlength' => 255), 'name');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'subject');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'authors');
    v::string($input, $entity, array('required' => false, 'maxlength' => 500), 'comments');
    v::string($input, $entity, array('required' => false, 'maxlength' => 500), 'description');
    v::string($input, $entity, array('required' => false, 'maxlength' => 1000), 'tags');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'copyright');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'program_name');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'camera_maker');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'camera_model');
    v::string($input, $entity, array('required' => false, 'maxlength' => 255), 'lens');
    v::string($input, $entity, array('required' => false, 'maxlength' => 20), 'focal_length');
    v::string($input, $entity, array('required' => false, 'maxlength' => 6), 'exposure_bias');
    v::string($input, $entity, array('required' => false, 'maxlength' => 6), 'exposure_time');
    v::string($input, $entity, array('required' => false, 'maxlength' => 5), 'f_stop');
    v::string($input, $entity, array('required' => false, 'maxlength' => 6), 'iso_speed');
    v::string($input, $entity, array('required' => false, 'maxlength' => 20), 'flash');
    v::string($input, $entity, array('required' => false, 'maxlength' => 20), 'metering_mode');
    v::int($input, $entity, array('required' => true), 'category');

    if (isset($input['categories'])) {
        if (is_array($input['categories'])) {
            $entity['categories'] = array();
            foreach ($input['categories'] as $cid)
                if (is_int($cid) || v::int($cid, $cid, 'required', 'categories'))
                $entity['categories'][] = $cid;

        } else v::msg(v::E_INVALID, _('Value must of type array'), 'categories');
    }

	return $entity;
}

}

cfg::module('nge\mml');

?>