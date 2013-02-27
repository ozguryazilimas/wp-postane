<?php
require_once('../../../wp-config.php');
require_once('psr.class.php');
$id = $_REQUEST['p'];
$PSR =& new PSR();
$PSR->init();
echo $PSR->getVotingStars();
?>
