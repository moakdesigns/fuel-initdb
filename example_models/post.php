<?
/**
 * Example Post Model
 *
 * @package initdb
 * @author jondavidjohn
 */
class Model_Post extends Orm\Model {         //<--- Singular Capitalized Element name `Model_Element`

	public static $_table_name = 'posts';    //<--- this will be used for the table name (must be public)

	public static $_properties = array(      //<--- must use id as primary key and make $_properties public
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