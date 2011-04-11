<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
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
	 * Task that will Generate your DB schema based on your ORM models
	 *
	 * @return void
	 * @author jondavidjohn
	 */	
	public static function run($model_dir = 'fuel/app/classes/model/')
	{
		\Cli::write();
		if (strtolower($model_dir) === "--help" || strtolower($model_dir) === "help")
		{
			$help = <<<HELP
Usage:
  php oil refine initdb [optional path to model root (if not default)]

Description:
  This task is designed to allow ORM users to build their models first and then build
  a database starting point for you that matches your models.

  It utilizes migrations to build your database from the scheme you outline
  in your models.

Examples:
  php oil refine initdb
  php oil refine initdb non/standard/path/to/models/   <--note trailing slash

Project Home:
  https://github.com/jondavidjohn/fuel-initdb
HELP;

			\Cli::write($help);
		}
		else
		{
			// first remove any migration files
			$migrations_dir = 'fuel/app/migrations/';
			$md = opendir($migrations_dir);
			while($file = readdir($md))
			{
				//make sure file is not a directory or parent
				if ( ! is_dir($migrations_dir.$file) && $file != '..' )
				{
					unlink($migrations_dir.$file);
				}
			}
		
			//get models from app/classes/model directory
			$model_name_array = array();
			$dirpath = $model_dir;
			$dh = opendir($dirpath);
			while ($file = readdir($dh)) 
			{
				//make sure file is not a directory or index.html
				if (!is_dir($dirpath.$file) || $file !== 'index.html') 
				{
					//Truncate the file extension
					$model_name_array[] = htmlspecialchars(preg_replace('/\..*$/', '', $file));
					\Cli::write("Model found! -> ".end($model_name_array),'green');
				}
			}
			closedir($dh);
			
			if(empty($model_name_array))
			{
				throw new Exception("Could not find any models.\n\nExiting...");
			}
			else 
			{
				$fire = \Cli::color("*** READ THIS OR YOUR COMPUTER WILL LIGHT ON FIRE ***", 'red');
				
				$disclaimer = <<<DISCLAIMER
$fire

  Ok, not really light on fire, but the intent of this tool is to
  give your project a starting point based on the models you've 
  already defined.  NOT TO EDIT TABLES MID PROJECT.

  THIS WILL COMPLETELY RESET YOUR MIGRATIONS AND ANY DATA CONTAINED
  WITHIN THE MODELS YOU ARE TRANSLATING INTO DATABASE TABLES 
  WILL BE LOST.

  What would you like to do?
DISCLAIMER;
				\Cli::beep(1);
				\Cli::write();
				$response = \Cli::prompt($disclaimer, array('CONTINUE', 'EXIT'));
				
				if($response !== 'CONTINUE')
				{
					throw new Exception("\nExiting...");
				}
			}
			\Cli::write();
			// drop migrations table if present and all tables with corresponding models to be rebuilt
			$current_tables = \DB::list_tables();
			\DBUtil::drop_table('migration');
			foreach($model_name_array as $model_name)
			{
				$class_name = 'Model_'.ucfirst($model_name);
				$table_name = $class_name::$_table_name;
				if (in_array($table_name, $current_tables))
				{
					\DBUtil::drop_table($table_name);
					\Cli::write("Table Dropped. -> ".$table_name,'green');
				}
			}
			\Cli::write();

			foreach($model_name_array as $model_name)
			{
				$class_name = 'Model_'.ucfirst($model_name);
				$table_name = $class_name::$_table_name;
				$properties = $class_name::$_properties;
				$args       = array('create_'.$table_name);
			
				foreach ($properties as $property_name => $property)
				{
					//skip id (assumed primary key)
					if ($property_name == 'id') { continue; }
				
					$field_string = $property_name.':'.$property['type'];
				
					if (isset($property['max_length']))
					{
						$field_string .= '['.$property['max_length'].']';
					}
				
					$args[] = $field_string;
				}
			
				\Oil\Generate::migration($args);
			}
			\Migrate::latest();
			
			\Cli::write();
			\Cli::write("Migrations Complete, Database Built", 'green');
		}
	}
}

/* End of file tasks/syncdb.php */
