<?php
/**
* @version $Id$
* @author Sendloop.com <support@sendloop.com>
* @see http://sendloop.com/
* @see Help: http://sendloop.com/help/integration/wordpress/
* @package Sendloop Subscribe WP Plugin
*/

if (!function_exists('add_action')) {
    require_once("../../../wp-config.php");
}

// checking if plugin enabled and POST params are sent
if (
    !defined('SENDLOOP_SUBSCRIBE') or 
    !is_array($_POST) or 
    !isset($_POST['email']) or
    !isset($_POST['target_list'])
) {
    die('Hack attempt');
} else {
    $SendloopSubscribeDispatcher = new SendloopSubscribeDispatcher();
    $SendloopSubscribeDispatcher->setEmail($_POST['email']);
    $SendloopSubscribeDispatcher->setTargetList($_POST['target_list']);
    if (isset($_POST['custom_fields']))
        $SendloopSubscribeDispatcher->setCustomFields($_POST['custom_fields']);
    if ('unsubscribe' == $_POST['action'])
        $SendloopSubscribeDispatcher->unsubscribe();
    else
        $SendloopSubscribeDispatcher->subscribe();
}





