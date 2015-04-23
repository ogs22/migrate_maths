<?php

  /*
  * MENU!!!!
  *
  */

class MigrateAllMenu extends Migration {
  public $base_dir;
  public $topnid = 9993; // set manually....
  public $partimp = '/';
  public $maindir = '/usr/local/www/drupal/sites/www.maths.cam.ac.uk/files/pre2014';
  //public $exclude = array("undergrad/","abaqus_docs","computing/windows/play/pt3_feedback"); 
  public $exclude = array("abaqus_docs","computing/","/RCS");
  /**
   * Constructor.
   */
  public function __construct($arguments) {
    parent::__construct($arguments);
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
 

    $this->base_dir = $this->maindir.$this->partimp;
    // Match HTML files.
//    $regex = '/.*\.htm/';
     $regex = '/(?!(undergrad|madeup)).*\.htm/';

    // The source of the migration is HTML files from the old site. // class extended to override filescan order
    $list_files = new mathMigrateListFiles(array($this->base_dir), $this->base_dir, $regex);
    
    $item_file = new MigrateItemFile($this->base_dir);
    $this->source = new MigrateSourceList($list_files, $item_file, $fields);
    $this->destination = new MigrateDestinationMenuLinks();
 
    // Map the fields
    $this->addFieldMapping('menu_name')->defaultValue('main-menu');
    $this->addFieldMapping('plid', 'ref_parent');
    $this->addFieldMapping('link_path','nodepath');
    $this->addFieldMapping('link_title','link_title');
    $this->addFieldMapping('hidden')->defaultValue(0);
    $this->addFieldMapping('external')->defaultValue('0');
    $this->addFieldMapping('expanded')->defaultValue('1');
    $this->addFieldMapping('customized')->defaultValue('1');
    $this->addFieldMapping('p1', 'p1')->sourceMigration($this->getMachineName());
    $this->addFieldMapping('p2', 'p2')->sourceMigration($this->getMachineName());
    $this->addFieldMapping('p3', 'p3')->sourceMigration($this->getMachineName());
    $this->addFieldMapping('p4', 'p4')->sourceMigration($this->getMachineName());
    $this->addFieldMapping('router_path')->defaultValue('node/%');
  }
 


  /**
   * Prepare a row.
   */
  public function prepareRow($row) {

    $row->uid = 1;
    $source_parser = new SourceParser(substr($row->sourceid, 1), $row->filedata,$this);
    $row->facpath = substr($row->sourceid,1);

    $row->parentNID = $this->getParentNid($row->facpath);
    $row->ref_parent = $this->getParent($row->parentNID);
    $row->nodepath = drupal_get_normal_path($row->facpath);
    $row->link_title = $source_parser->getTitle($this->base_dir,$row->path);
    if ($row->link_title == "") {
        $row->link_title = $row->sourceid;
    }
    echo $row->facpath."\n";
    foreach($this->exclude as $x) {
        if (strstr($row->facpath,$x)) {
     	  echo "Excluded: ".$row->facpath."\n";
	  return false;
        }
     }

  }

  public function getParentNid($htmlpath) {
    /*
      so if old path is /somebit/somepath/index.html
      we need to look to /somebit/index.html to be parent
      oldpath is /somebit/somepath/something.html then look for /somebit/somepath/index.html as parent
    */
      $mlid='';
      $np = false;
      $path_parts = pathinfo($htmlpath);
      $dirs = explode('/', $path_parts['dirname']);
      if ($path_parts['basename']=="index.html") {
        $me = array_pop($dirs);
      }
      $rejoin = implode('/',$dirs).'/index.html';
      echo "\n".$htmlpath." looking for Parent:: ".$rejoin."\n";
      
      if ($rejoin == "/index.html" or $rejoin == "//index.html" or $rejoin == "./index.html") {
        $nid = $this->topnid; //top level page for this import
	echo " Top level child nid:: ".$nid;
      } else {

      $pnodepath = drupal_get_normal_path($rejoin);
      
      if ($pnodepath==$rejoin) {
        $pnodepath = false;
        echo " No parent found::\n";
        $np = true;
        return false;
      } else {
        echo "Parent found at:: ".$pnodepath."\n";
        $tmp = explode('/', $pnodepath);
        $nid = $tmp[1];
        //echo $nid." - nid\n";
      }
    }
      return $nid;
  }

  public function getParent($pnid) {
      //return parent menu id mlid
      $pnodepath = "node/".$pnid;
      $mlid = db_select('menu_links' , 'ml')
              ->condition('ml.link_path' , $pnodepath)
              ->condition('ml.menu_name','main-menu')
              ->fields('ml' , array('mlid'))
              ->execute()
              ->fetchField();  

      //echo $mlid." - mlid\n";
      return $mlid;
  }

}








