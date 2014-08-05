<?php
function migrate_damtp_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      'damtp' => array(
        'title' => t('maths faculty migrations'),
      	'default_format' => 'filtered_html',
      ),
    ),
    'migrations' => array(
      'MigrateDamtpArticle' => array(
        'class_name' => 'MigrateDamtpArticleMigration',
        'group_name' => 'damtp',
      )
    ),
  );
  return $api;
}


 


