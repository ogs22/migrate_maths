<?php

class MigrateAll extends Migration {

    public $base_dir;
    public $partimp = '/';
    public $maindir = '/usr/local/www/drupal/sites/www.maths.cam.ac.uk/files/pre2014';
    public $linkedfiles = '/sites/www.maths.cam.ac.uk/files/pre2014/';
    public $exclude = array("abaqus_docs", "/RCS");

    /**
     * Constructor.
     */
    public function __construct($arguments) {
        parent::__construct($arguments);

        $this->map = new MigrateSQLMap($this->machineName, array(
            'sourceid' => array(
                'type' => 'varchar',
                'length' => 255,
                'not null' => TRUE,
            )
                ), MigrateDestinationNode::getKeySchema()
        );
        // The source fields.
        $fields = array(
            'title' => t('Title'),
            'body' => t('Body'),
            'uid' => t('User id'),
            'facpath' => t('the path'),
            'termname' => t('tags')
        );

        $this->base_dir = $this->maindir . $this->partimp;

        // Match HTML files.
        $regex = '/(?!(undergrad|madeup)).*\.htm/';

        // The source of the migration is HTML files from the old site.
        $list_files = new MigrateListFiles(array($this->base_dir), $this->base_dir, $regex);

//print_r($list_files);

        $item_file = new MigrateItemFile($this->base_dir);
        $this->source = new MigrateSourceList($list_files, $item_file, $fields);

//print_r($this->source);
        // The destination is the mynode content type.
        $this->destination = new MigrateDestinationNode('page');

        // Map the fields, pretty straightforward in this case.
        $this->addFieldMapping('uid', 'uid');
        $this->addFieldMapping('title', 'title');
        $this->addFieldMapping('body', 'body');
        $this->addFieldMapping('body:format')->defaultValue('full_html');
        $this->addFieldMapping('path', 'facpath');
        $this->addFieldMapping('pathauto', FALSE);
        $this->addFieldMapping('field_security', 'termname')->separator('|');
    }

    /**
     * Prepare a row.
     */
    public function prepareRow($row) {

        //print_r($row);
        // Set to admin for now.
        $row->uid = 1;

        // Create a new SourceParser to handle HTML content.
        $source_parser = new SourceParser(substr($row->sourceid, 1), $row->filedata, $this);
        $row->body = $source_parser->getBody();

        // The title is the filename.
        $row->facpath = substr($row->sourceid, 1);
        echo $row->facpath . "\n";
        foreach ($this->exclude as $x) {
            if (strstr($row->facpath, $x)) {
                echo "Excluded: " . $row->facpath . "\n";
                return false;
            }
        }
//    if (basename($row->sourceid) == "index.html") {
//        $row->alt[1] = substr(dirname($row->sourceid),1);
//    }

        $row->title = $source_parser->getTitle($this->base_dir, $row->facpath);
        if ($row->title == "") {
            $row->title = $row->sourceid;
        }
        $row->termname = "Community|Public";
    }

    public function determineSecurity($param) {
        $editterms = array(
            "Community",
            "Internal",
            "InternalAdmin",
            "News",
            "PostGrad",
            "Research",
            "UnderGrad"
        );

        /*
         * So if the path matches ~editterm add that tag - editterms are editing groups
         * 
         */

        $viewterms = array(
            "Public",
            "Raven",
        );
        
        /*
         * Beware viewterms as Public overrides Raven overrides GROUPX
         */
        
        
    }

}
