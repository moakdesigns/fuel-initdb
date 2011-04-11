initdb Fuelphp Task
===================

**Description:**

This task will allow you to create your database scheme from your models.

When ran, this task will:

 - Reset any existing migration information.
 - Gather the models in your models directory.
 - Drop any tables that are of the same name as any of your models. (**LOOSING ALL DATA**)
 - Create Migrations based on definition in models.
 - Update the Database to reflect migrations

**Purpose:**

To allow you to define your data in the models and have it translated to the Database

**USAGE**

 1. Copy initdb.php into your fuel/app/tasks directory
 2. Construct your models following provided example models
 3. Run this command:

    php oil refine initdb 

**Next Goal:**

 - To add many-to-many mapping table support

**Author:**

 - Jonathan David Johnson
 - me@jondavidjohn.com
 - http://jondavidjohn.com


