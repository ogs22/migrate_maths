<?php
function migrate_maths_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      'wwwmaths' => array(
        'title' => t('maths faculty migrations'),
      	'default_format' => 'full_html',
      ),
    ),
    'migrations' => array(
      'MigrateUndergrad' => array(
        'class_name' => 'MigrateUndergrad',
        'group_name' => 'wwwmaths',
      ),
      'MigrateCatam' => array(
        'class_name' => 'MigrateCatam',
        'group_name' => 'wwwmaths',
      ),
      'MigrateCatamMenu' => array(
        'class_name' => 'MigrateCatamMenu',
        'group_name' => 'wwwmaths',
      ),
      'MigrateUndergradMenu' => array(
        'class_name' => 'MigrateUndergradMenu',
        'group_name' => 'wwwmaths',
      ),
      'MigrateAll' => array(
        'class_name' => 'MigrateAll',
        'group_name' => 'wwwmaths',
      ),
      'MigrateAllMenu' => array(
        'class_name' => 'MigrateAllMenu',
        'group_name' => 'wwwmaths',
      )
    ),
  );
  return $api;
}

 


