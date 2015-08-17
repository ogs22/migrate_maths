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

 


