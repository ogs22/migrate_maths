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

 
    $this->partimp = "undergrad/catam";
    $this->base_dir = '/local/httpd/sites/htdocs-maths/'.$this->partimp;

    // Match HTML files.
    $regex = '/.*\.htm/';
 
    // The source of the migration is HTML files from the old site.
    $list_files = new mathMigrateListFiles(array($this->base_dir), $this->base_dir, $regex);
    
    $item_file = new MigrateItemFile($this->base_dir);
    $this->source = new MigrateSourceList($list_files, $item_file, $fields);
    //print_r($this);

    // The destination is the mynode content type.
    $this->destination = new MigrateDestinationMenuLinks();
 
    // Map the fields, pretty straightforward in this case.
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

  //    $this->addFieldMapping('options')->defaultValue('a:1:{s:10:"attributes";a:1:{s:5:"title";s:0:"";}}');
  }
 
  /**
   * Prepare a row.
   */
  public function prepareRow($row) {

    $row->uid = 1;
 
    $source_parser = new SourceParser(substr($row->sourceid, 1), $row->filedata,$this);

    $row->facpath = $this->partimp.'/'.substr($row->sourceid,1);

    $row->parentNID = $this->getParentNid($row->facpath);

    $row->ref_parent = $this->getParent($row->parentNID);
    $row->nodepath = drupal_get_normal_path($row->facpath);
    
    $row->link_title = $source_parser->getTitle($this->base_dir,$row->path);
    if ($row->link_title == "") {
        $row->link_title = $row->sourceid;
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
      
      if ($rejoin == "undergrad/catam/index.html" or $rejoin == "undergrad/index.html") {
        $nid = 31648; //top level page for this import
      } else {

      $pnodepath = drupal_get_normal_path($rejoin);
      
      if ($pnodepath==$rejoin) {
        $pnodepath = false;
        echo "No parent found::\n";
        $np = true;
        return false;
      } else {
        echo "Parent found at:: ".$pnodepath."\n";
        $tmp = explode('/', $pnodepath);
        $nid = $tmp[1];
        echo $nid." - nid\n";
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


      echo $mlid." - mlid\n";
      return $mlid;
  }

  protected function createStub($migration,array $source_id) {
//   print_r($migration);
    // if ref_parent is 0, that means it has no parent, so don't create a stub
  //  if (!$migration->sourceValues->ref_parent) {
  //    echo "\nstub called but not Creating stub:\n";
  //    return FALSE;
  //  }
    echo "\nCreating stub:\n";
    $menu_link = array (
      'menu_name' => 'main-menu',
      'link_path' => '<front>',
      'router_path' => 'stub-path',
      'link_title' => t('Stub for @id', array('@id' => $source_id[0])),
      'plid' => 36098,
      'enabled' => 1
    );
    $mlid = menu_link_save($menu_link);
    if ($mlid) {
      return array($mlid);
    }
    else {
      return FALSE;
    }
  }

  protected function getnidtitle($nid) {
    $node = node_load($nid);
    return $node->title;
  }

}








