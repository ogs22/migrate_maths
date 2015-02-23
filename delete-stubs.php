<?php



$i = 0;
$sql = db_query("SELECT n.nid, n.title FROM {node} n WHERE n.title='DELETE ME'");

foreach ($sql as $n) {
  $nid = $n->nid;
  $title = $n->title;
  node_delete($n->nid);
  print 'Deleted '. $i . ':' . $nid . ':' . $title . "\n";
  $i++;
}
print '\nDeleted '. $i .' nodes.';


?>