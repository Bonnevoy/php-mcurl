<?php
header('Content-type: text/plain');
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }



require('../mcurl.class.php');
$mcurl = Mcurl::factory();
$mcurl->max_per_second = 5;
$mcurl->debug = true;
for ($i=0; $i<50; $i++)
{
	$mcurl->add_url('http://experiments.bonnevoy.com/mcurl/example/server.php');
}

$mcurl->execute();
?>