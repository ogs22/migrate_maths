<?php
function migrate_maths_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      'wwwmaths' => array(
        'title' => t('maths faculty migrations'),
      	'default_format' => 'filtered_html',
      ),
    ),
    'migrations' => array(
      'MigrateUndergrad' => array(
        'class_name' => 'MigrateUndergrad',
        'group_name' => 'wwwmaths',
      ),
      'MigrateUndergradMenu' => array(
        'class_name' => 'MigrateUndergradMenu',
        'group_name' => 'wwwmaths',
      )
    ),
  );
  return $api;
}


 


