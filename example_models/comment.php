<?
/**
 * Example Comment Model
 *
 * @author jondavidjohn
 */
class Model_Comment extends Orm\Model {        //--- Singular Entity Model Name
	
	public static $_table_name = 'comments';   //--- This will be used for actual table name
	
	public static $_properties = array(        //--- Make sure all properties are public
		'id'         => array('data_type' => 'int'),
		'author'     => array('data_type' => 'string'),
		'contents'   => array('data_type' => 'text'),
		'post_id'    => array('data_type' => 'int'),
		'created_at' => array('data_type' => 'int'),
		'updated_at' => array('data_type' => 'int'),
	);
	
	public static $_belongs_to = array(
		'post' => array(
			'model_to' => 'Model_Post',
			'key_from' => 'post_id',
			'key_to' => 'id',
	));
	
	public static $_observers = array(
		'Orm\\Observer_UpdatedAt' => array('before_save'),
		'Orm\\Observer_CreatedAt' => array('before_insert'),
		'Orm\\Observer_Typing'    => array('before_save', 'after_save', 'after_load')  //-- using the typing observer
	);

}

/* End of file - comment.php */