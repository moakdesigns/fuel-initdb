<?

/**
 * Test Post Model
 *
 * @package Fuel
 * @author jondavidjohn
 */
class Model_Post extends Orm\Model {

	public static $_table_name = 'posts';

	public static $_properties = array(
		'id'         => array('type' => 'int'),
		'title'      => array('type' => 'string'),
		'author'     => array('type' => 'string'),
		'contents'   => array('type' => 'text'),
		'publish'    => array('type' => 'int' , 'max_length' => 1),
		'created_at' => array('type' => 'timestamp'),
		'updated_at' => array('type' => 'timestamp')
	);

	protected static $_has_many = array(
		'comments' => array(
			'key_from' => 'id',
			'model_to' => 'Model_Comment',
			'key_to' => 'post_id',
			'cascade_save' => true,
			'cascade_delete' => true,
		)
	);
	
	protected static $_observers = array(
		'Orm\\Observer_UpdatedAt' => array('before_save'),
		'Orm\\Observer_CreatedAt' => array('before_insert'),
	);
}

/* End of file - post.php */