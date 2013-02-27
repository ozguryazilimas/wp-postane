<?php
    $theme_options = new stdClass();
    $theme_options->type = "star";
    $theme_options->style = "gray";
    $theme_options->advanced = new stdClass();
    $theme_options->advanced->font = new stdClass();
    $theme_options->advanced->font->color = "#999";
    $theme_options->advanced->font->size = "11px";
    $theme_options->advanced->css = new stdClass();
    $theme_options->advanced->css->container = "background: #F4F4F4; padding: 1px 2px 0px 2px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;";
    $theme_options->advanced->font->hover = new stdClass();
    $theme_options->advanced->font->hover->color = "#999";
    
    $theme = array(
        "name" => "star_bp1",
        "title" => "BuddyPress Stars",
        "options" => $theme_options
    );
?>
