<?
/**
 * Example Post Model
 *
 * @author jondavidjohn
 */
class Model_Post extends Orm\Model {            //--- Singular Entity Model Name
                                                
	public static $_table_name = 'posts';       //--- This will be used for actual table name
                                                
	public static $_properties = array(         //--- Make sure all properties are public
		'id'         => array('type' => 'int'),
		'title'      => array('type' => 'string'),
		'author'     => array('type' => 'string'),
		'contents'   => array('type' => 'text'),
		'publish'    => array('type' => 'int' , 'max_length' => 1),
		'created_at' => array('type' => 'int'),
		'updated_at' => array('type' => 'int')
	);

	public static $_has_many = array(
		'comments' => array(
			'key_from' => 'id',
			'model_to' => 'Model_Comment',
			'key_to' => 'post_id',
		)
	);
	
    public static $_many_many = array(
        'users' => array(  //<----------------- Make sure this is the other entity's table name
            'key_from' => 'id',
            'key_through_from' => 'post_id', // column 1 from the table in between, should match a posts.id
            'table_through' => 'posts_users', // both models plural without prefix in alphabetical order
            'key_through_to' => 'user_id', // column 2 from the table in between, should match a users.id
            'model_to' => 'Model_User',
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