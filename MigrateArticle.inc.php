<?php

class MigrateArticle extends Migration {
  public $base_dir;
 
  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();
 
    // A map of source HTML filename -> destination node id.
    $this->map = new MigrateSQLMap($this->machineName,
        array(
          'sourceid' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          )
        ),
        MigrateDestinationNode::getKeySchema()
    );
 
    // The source fields.
    $fields = array(
      'title' => t('Title'),
      'body' => t('Body'),
      'uid' => t('User id'),
        'facpath' => t('the path')  
    );
 
    // Since the base directory of the HTML files can change depending on the
    // environment, we keep it in a variable. There is no interface for this,
    // set it using drush vset.
//    $this->base_dir = variable_get('my_migration_source', '');
 
    $this->base_dir = '/local/httpd/sites/htdocs-maths';

    // Match HTML files.
    $regex = '/.*\.html/';
 
    // The source of the migration is HTML files from the old site.
    $list_files = new MigrateListFiles(array($this->base_dir), $this->base_dir, $regex);
    $item_file = new MigrateItemFile($this->base_dir);
    $this->source = new MigrateSourceList($list_files, $item_file, $fields);
 
    // The destination is the mynode content type.
    $this->destination = new MigrateDestinationNode('page');
 
    // Map the fields, pretty straightforward in this case.
    $this->addFieldMapping('uid', 'uid');
    $this->addFieldMapping('title', 'title');
    $this->addFieldMapping('body', 'body')
      ->arguments(array('format' => 'full_html'));
    $this->addFieldMapping('path', 'facpath');
    
  }
 
  /**
   * Prepare a row.
   */
  public function prepareRow($row) {
      //print_r($row);
    // Set to admin for now.
    $row->uid = 1;
 
    // Create a new SourceParser to handle HTML content.
    $source_parser = new SourceParser(substr($row->sourceid, 1), $row->filedata);
    $row->body = $source_parser->getBody();
 
    // The title is the filename.
    $row->facpath = substr($row->sourceid,1);
//    if (basename($row->sourceid) == "index.html") {
//        $row->alt[1] = substr(dirname($row->sourceid),1);
//    }
    
    $row->title = $source_parser->getTitle($this->base_dir,$row->path);
    if ($row->title == "") {
        $row->title = $row->sourceid;
    }
  }
}
