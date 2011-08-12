<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package	   Fuel
 * @version	   1.0
 * @author	   Fuel Development Team
 * @license	   MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link	   http://fuelphp.com
 */

namespace Fuel\Tasks;

/**
 *
 * Task that will Generate your DB schema based on your ORM models
 *
 * @author jondavidjohn
 */

class Initdb {


	/**
	 * Returns the table name from class table_name property when available 
	 * or from model_name with an 's' 
	 *
	 * @return table_name
	 * @author dimitridamasceno
	 */ 
	private static function _table_name($model_name = null)
	{
		$class_name = self::_class_name($model_name);
		if(property_exists($class_name,'_table_name'))
		{
			$table_name = $class_name::$_table_name;
		} 
		else
		{ 
			$table_name = $model_name.'s';
		}
		return $table_name;
	}
	
	/**
	 * Returns the class name
	 *
	 * @return class_name
	 * @author dimitridamasceno
	 */ 
	private static function _class_name($model_name = null)
	{
		$class_name = 'Model_'.ucfirst($model_name);
		if(!class_exists($class_name))
		{
			throw new \Oil\Exception("Could not find class:".$class_name);
		}
		else
		{
			return $class_name;
		}
	}
	
	/**
	 * Task that will Generate your DB schema based on your ORM models
	 *
	 * @return void
	 * @author jondavidjohn
	 */ 
	public static function run()
	{
	    $model_dir = APPPATH.'classes/model';
		if (strtolower($model_dir) === "--help" || strtolower($model_dir) === "help")
		{
			$help = <<<HELP
\nUsage:
  php oil refine initdb

Description:
  This task is designed to allow ORM users to build their models first and then build
  a database starting point for you that matches your models.

  It utilizes migrations to build your database from the scheme you outline
  in your models.

Task Home:
  https://github.com/jondavidjohn/fuel-initdb
HELP;

			\Cli::write($help);
		}
		else
		{
			// first remove any migration files
			$migrations_dir = APPPATH.'migrations';
			$md = opendir($migrations_dir);
			while($file = readdir($md))
			{
				//make sure file is not a directory or parent
				if ( ! is_dir($migrations_dir.$file) && $file != '..' && $file != '.' && $file != '.gitkeep' )
				{
					unlink($migrations_dir.'/'.$file);
				}
			}
			
			\Cli::write();
			
			//get models from app/classes/model directory
			$model_name_array = array();
			$dirpath = $model_dir;
			$dh = opendir($dirpath);
			while ($file = readdir($dh)) 
			{
				//make sure file is not a directory or index.html
				if (!is_dir($dirpath.$file) && $file !== 'index.html' && $file != '.' && $file != '..' && $file != '.gitkeep') 
				{
					//Truncate the file extension
					$model_name_array[] = htmlspecialchars(preg_replace('/\..*$/', '', $file));
					\Cli::write("Model found! -> ".end($model_name_array),'green');
					
				}
			}
			\Cli::write();
			closedir($dh);
			
			if(empty($model_name_array))
			{
				throw new \Oil\Exception("Could not find any models.");
			}
			else 
			{
				$fire = \Cli::color("*** READ THIS OR YOUR COMPUTER WILL LIGHT ON FIRE ***", 'red');
				
				$disclaimer = <<<DISCLAIMER
$fire

  Ok, not really light on fire, but the intent of this tool is to
  give your project a starting point based on the models you've 
  already defined.	NOT TO EDIT TABLES MID PROJECT.

  THIS WILL COMPLETELY RESET YOUR MIGRATIONS AND ANY DATA CONTAINED
  WITHIN THE MODELS YOU ARE TRANSLATING INTO DATABASE TABLES 
  WILL BE LOST.

  What would you like to do?
DISCLAIMER;
				\Cli::beep(1);
				$response = \Cli::prompt($disclaimer, array('CONTINUE', 'EXIT'));
				
				if($response !== 'CONTINUE')
				{
					throw new \Oil\Exception("Exiting...");
				}
			}
			
			\Cli::write();
			
			// drop migrations table if present and all tables with corresponding models to be rebuilt
			$current_tables = \DB::list_tables();
			\DBUtil::drop_table('migration');
			foreach($model_name_array as $model_name)
			{
				$class_name = self::_class_name($model_name);
				$table_name = self::_table_name($model_name);
				if (in_array($table_name, $current_tables))
				{
					\DBUtil::drop_table($table_name);
					\Cli::write("Table Dropped. -> ".$table_name,'green');
				}
			}
			\Cli::write();

			$mapping_tables = array();
			foreach($model_name_array as $model_name)
			{
				$class_name = self::_class_name($model_name);
				$table_name = self::_table_name($model_name);
				$properties = $class_name::properties(); // This allow to keep properties private
				$args		= array('create_'.$table_name);
			
				foreach ($properties as $property_name => $property)
				{
					//skip id (assumed primary key)
					if ($property_name == 'id') { continue; }
				
					if(isset($property['data_type']))
					{
						$field_string = $property_name.':'.$property['data_type'];
					} 
					elseif(isset($property['type'])) 
					{
						$field_string = $property_name.':'.$property['type'];
					}
				
					if (isset($property['max']))
					{
						$field_string .= '['.$property['max'].']';
					}
				
					$args[] = $field_string;
				}
				
				// if $_many_many defined, we need to create a mapping table
				if(property_exists($class_name,"_many_many"))
				{
					foreach ($class_name::$_many_many as $key => $rel)
					{
						$name_sort = array($table_name, $key);
						sort($name_sort);
						$mapping_table_name = $name_sort[0].'_'.$name_sort[1];
						
						if( ! array_key_exists($mapping_table_name, $mapping_tables))
						{
							$mapping_tables[$mapping_table_name] = array($rel['key_through_to'].':int', $rel['key_through_from'].':int');
						}
					}
				}
			
				\Oil\Generate::migration($args);
			}
			
			//now build mapping tables
			if ( ! empty($mapping_tables) )
			{
				\Cli::write("\nCreating Mapping tables...\n", 'green');
				
				foreach($mapping_tables as $name => $table)
				{
					$args = array('create_'.$name);
					foreach($table as $field)
					{
						$args[] = $field;
					}
					
					\Oil\Generate::migration($args);
				}
			}
			
			
			\Migrate::latest();
			
			\Cli::write("\nMigrations Complete, Database Built", 'green');
		}
	}
}

/* End of file initdb.php */
