<?php
    if (WP_RW__LOCALHOST)
    {
        define("WP_RW__ADDRESS_CSS", "http://" . WP_RW__DOMAIN . "/css/");
        define("WP_RW__ADDRESS_JS", "http://" . WP_RW__DOMAIN . "/js/");
        define("WP_RW__ADDRESS_IMG", "http://" . WP_RW__DOMAIN . "/img/");
    }
    else if (WP_RW__HTTPS && false !== WP_RW__USER_SECRET)
    {
        define("WP_RW__ADDRESS_CSS", "https://secure." . WP_RW__DOMAIN . "/css/");
        define("WP_RW__ADDRESS_JS", "https://secure." . WP_RW__DOMAIN . "/js/");
        define("WP_RW__ADDRESS_IMG", "https://secure." . WP_RW__DOMAIN . "/img/");
    }
    else
    {
        define("WP_RW__ADDRESS_CSS", "http://css.rating-widget.com/");
        define("WP_RW__ADDRESS_JS", "http://js.rating-widget.com/");
        define("WP_RW__ADDRESS_IMG", "http://img.rating-widget.com/");
    }
?>
