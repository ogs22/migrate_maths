<?php

class MigrateAll extends Migration {

    public $base_dir;
    public $partimp = '/';
    public $maindir = '/usr/local/www/drupal/sites/www.maths.cam.ac.uk/files/pre2014';
    public $linkedfiles = '/sites/www.maths.cam.ac.uk/files/pre2014/';
    public $exclude = array("/computing/", "abaqus_docs", "RCS");

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
        //$row->termname = "Community|Public";
        $row->termname = $this->determineSecurity($row->facpath);
    }

    public function determineSecurity($path) {
    	echo "testing security by:".$path;
        $terms = array();

//        $editterms = array(
//            "Community",
//            "Internal",
//            "InternalAdmin",
//            "News",
//            "PostGrad",
//            "Research",
//            "UnderGrad"
//        );

        $editmap = array(
            "about/community/" => "Community",
            "internal/" => "Internal",
            "news/" => "News",
            "postgrad/" => "Postgrad",
            "research/" => "Research",
            "undergrad/" => "UnderGrad",
            "undergradnst" => "UnderGrad"
        );

        foreach ($editmap as $key => $value) {
            $pos = stripos($path, $key);
            if ($pos === false) {
                //no match
            } elseif ($pos == 0) {
                $terms[] = $value;
            }
        }

        /*
         * So if the path matches ~editterm add that tag - editterms are editing groups
         * 
         */

//        $viewterms = array(
//            "Public",
//            "Raven",
//            "Raven-cos",
//            "Raven-ms",
//            "Raven-catamadmin",
//            "Raven-cpac",
//            "Raven-damtpusers",
//            "Raven-dpmms"
//        );

        $viewmap = array(
            "undergrad/catam/projects/" => "Raven-cpac",
            "undergrad/catam/projects/AdminDocuments/" => "Raven-catamadmin",
            "undergrad/catam/projects/AdminDocuments/MS-Temp/" => "Raven-ms",
            "undergrad/catam/software/matlabinstall/" => "Raven",
            "undergrad/catam/software/matlabSV-download/" => "Raven",
            "undergrad/catam/Declarations/2014-15/II/" => "Raven",
            "undergrad/catam/Declarations/2014-15/IB/" => "Raven",
            "undergrad/catam/Declarations/2013-14/II/" => "Raven",
            "undergrad/catam/Declarations/2013-14/IB/" => "Raven",
            "undergrad/catam/Declarations/Current/II/" => "Raven",
            "undergrad/catam/Declarations/Current/IB/" => "Raven",
            "undergrad/studentsurvey/" => "Raven",
            "nstmaths/" => "Raven",
            /* "facultyboard/admissionscommittee/" => "Raven-", */
            /* "facultyboard/mathsdos/" => "Raven-",      	    */
            "facultyboard/meetings/" => "Raven",
            "facultyboard/partiiicommittee/" => "Raven",
            "internal/ref/" => "Raven",
            "internal/email_lists/" => "Raven",
            "internal/admin/RMASS/" => "Raven-damtpusers|Raven-dpmms",
            "postgrad/mathiii/registration/" => "Raven",
            "postgrad/mathiii/Declarations/2014-15/" => "Raven",
            "postgrad/mphil/Declarations/2014-15/" => "Raven",
            "postgrad/mathiii-archive2013-Sept27/mathiii/registration/" => "Raven",
        );

	$vterms= array();

        foreach ($viewmap as $key => $value) {
            $pos = stripos($path, $key);
            if ($pos === false) {
                //no match
            } elseif ($pos == 0) {
                $vterms[] = $value;
            }
        }

	if ($vterms == array() ) {
	   $vterms[] = "Public";
	}

	$allterms= array_merge($terms,$vterms);

        /*
         * Beware viewterms as Public overrides Raven overrides GROUPX
         */

        $termpd = implode("|", $allterms);
        echo "\n".$termpd."\n";
        return $termpd;
    }

}
