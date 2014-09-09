<?php


  /*
  * MENU!!!!
  *
  */

class MigrateCatamMenu extends Migration {
  public $base_dir;
 
  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();
 


    $this->map = new MigrateSQLMap($this->machineName,
        array(
          'sourceid' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          )
        ),
        MigrateDestinationMenuLinks::getKeySchema()
    );

    
 
    // The source fields.
    $fields = array(
      'plid' => t('The parent'),
      'facpath' => t('the path'),
      'nodepath' => t('real node path'),
      'link_title' => t('link title'),
      'p1' => t('err P1?'),
    );
 
    // Since the base directory of the HTML files can change depending on the
    // environment, we keep it in a variable. There is no interface for this,
    // set it using drush vset.
//    $this->base_dir = variable_get('my_migration_source', '');
 
    $this->partimp = "undergrad/catam";
    $this->base_dir = '/local/httpd/sites/htdocs-maths/'.$this->partimp;

    // Match HTML files.
    $regex = '/.*\.htm/';
 
    // The source of the migration is HTML files from the old site.
    $list_files = new MigrateListFiles(array($this->base_dir), $this->base_dir, $regex);
    $item_file = new MigrateItemFile($this->base_dir);
    $this->source = new MigrateSourceList($list_files, $item_file, $fields);
 
    // The destination is the mynode content type.
    $this->destination = new MigrateDestinationMenuLinks();
 
    // Map the fields, pretty straightforward in this case.
    $this->addFieldMapping('menu_name')->defaultValue('main-menu');
    $this->addFieldMapping('plid','plid')->defaultValue(18520);
    $this->addFieldMapping('link_path','nodepath');
    $this->addFieldMapping('link_title','link_title');
    $this->addFieldMapping('hidden')->defaultValue(0);
    $this->addFieldMapping('external')->defaultValue('0');
    $this->addFieldMapping('expanded')->defaultValue('1');
    $this->addFieldMapping('customized')->defaultValue('1');
    $this->addFieldMapping('p1', 'p1')->sourceMigration($this->getMachineName());
    $this->addFieldMapping('router_path')->defaultValue('node/%');

  //    $this->addFieldMapping('options')->defaultValue('a:1:{s:10:"attributes";a:1:{s:5:"title";s:0:"";}}');
  }
 
  /**
   * Prepare a row.
   */
  public function prepareRow($row) {
      //print_r($row);
    // Set to admin for now.
    $row->uid = 1;
 
    $row->p1 = 18520;
    // Create a new SourceParser to handle HTML content.
    $source_parser = new SourceParser(substr($row->sourceid, 1), $row->filedata,$this);
    //$row->body = $source_parser->getBody();
 
    // The title is the filename.
    $row->facpath = $this->partimp.'/'.substr($row->sourceid,1);

    $row->nodepath = drupal_get_normal_path($row->facpath);

//    if (basename($row->sourceid) == "index.html") {
//        $row->alt[1] = substr(dirname($row->sourceid),1);
//    }
    
    $row->link_title = $source_parser->getTitle($this->base_dir,$row->path);
    if ($row->link_title == "") {
        $row->link_title = $row->sourceid;
    }
  }
}








