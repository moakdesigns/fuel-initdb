<?
/**
 * Example Comment Model
 *
 * @package initdb
 * @author jondavidjohn
 */
class Model_Comment extends Orm\Model {
	
	public static $_table_name = 'comments'; //<--- this will be used for the table name (must be public)
	
	public static $_properties = array(      //<--- must use id as primary key and make $_properties public
		'id'         => array('type' => 'int'),
		'author'     => array('type' => 'string'),
		'contents'   => array('type' => 'text'),
		'post_id'    => array('type' => 'int'),
		'created_at' => array('type' => 'timestamp'),
		'updated_at' => array('type' => 'timestamp'),
	);
	
	protected static $_belongs_to = array(
		'post' => array(
			'model_to' => 'Model_Post',
			'key_from' => 'id',
			'key_to' => 'post_id',
			'cascade_save' => true,
			'cascade_delete' => true,
	));
	
	protected static $_observers = array(
		'Orm\\Observer_UpdatedAt' => array('before_save'),
		'Orm\\Observer_CreatedAt' => array('before_insert'),
	);
}

/* End of file - comment.php */