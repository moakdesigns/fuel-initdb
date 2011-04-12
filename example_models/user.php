<?

/**
 * Example User Model
 *
 * @author jondavidjohn
 */
class Model_User extends Orm\Model {           //--- Singular Entity Model Name
                                               
	public static $_table_name = 'users';      //--- This will be used for actual table name
                                               
	public static $_properties = array(        //--- Make sure all properties are public
		'id'         => array('type' => 'int'),
		'first_name' => array('type' => 'string'),
		'last_name'  => array('type' => 'string'),
		'birthday'   => array('type' => 'datetime'),
		'color'      => array('type' => 'string'),
		'created_at' => array('type' => 'int'),
		'updated_at' => array('type' => 'int')
	);

	public static $_many_many = array(
		'users' => array(
			'key_from' => 'id',
			'key_through_from' => 'user_id',
			'table_through' => 'posts_users',
			'key_through_to' => 'post_id',
			'model_to' => 'Model_Post',
			'key_to' => 'id',
			'cascade_save' => true,
			'cascade_delete' => false,
		)
	);
	
	public static $_observers = array(
		'Orm\\Observer_UpdatedAt' => array('before_save'),
		'Orm\\Observer_CreatedAt' => array('before_insert'),
	);
}

/* End of file - post.php */