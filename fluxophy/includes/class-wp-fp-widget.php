<?php
/*
 * Copyright (c) 2014, Onur Küçük <onur@ozguryazilim.com.tr>
 * @license http://www.gnu.org/licenses/gpl-2.0.html  GPLv2
 */

/*
 * WP Fluxophy Widget Class
 */
class WP_Fluxophy_Widget extends WP_Widget {
    function WP_Fluxophy_Widget() {
        $widget_ops = array('classname' => 'wp-fp-widget', 'description' => __('Fetches data from external URL and shows as a nice list', 'fluxophy'));
        $control_ops = array('width' => 350);
        $this->WP_Widget('fluxophy', 'Fluxophy', $widget_ops, $control_ops);
    }


    function widget($args, $instance) {
        global $wpdb;

        $cache = wp_cache_get('widget_flıxophy', 'widget');

        if (!is_array($cache)) {
            $cache = array();
        }

        if (!isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        if (isset($cache[$args['widget_id']])) {
            echo $cache[$args['widget_id']];
            return;
        }

        extract($args, EXTR_SKIP);

        $output = '';
        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        $display_count = empty($instance['display_count']) ? FLUXOPHY_DISPLAY_COUNT : (int) $instance['display_count'];
        $source_url = $instance['source_url'];
        $picture_url = $instance['picture_url'];
        $account_name = $instance['account_name'];
        $account_link = $instance['account_link'];

        $output .= $before_widget;
        if ($title && $title != '') {
            $output .= $before_title . $title . $after_title;
        }

        $output .= fluxophy_display_data($source_url, $display_count, $picture_url, $account_name, $account_link);

        $output .= $after_widget;
        echo $output;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['source_url'] = strip_tags($new_instance['source_url']);
        $instance['picture_url'] = strip_tags($new_instance['picture_url']);
        $instance['account_name'] = strip_tags($new_instance['account_name']);
        $instance['account_link'] = strip_tags($new_instance['account_link']);
        $instance['display_count'] = $new_instance['display_count'];

        return $instance;
    }

    function form($instance) {
        global $wp_fp;

        $instance_name = strip_tags($instance['instance']);

        $title = (isset($instance['title'])) ? strip_tags($instance['title']) : 'Fluxophy';
        $source_url = (isset($instance['source_url'])) ? strip_tags($instance['source_url']) : '';
        $picture_url = (isset($instance['picture_url'])) ? strip_tags($instance['picture_url']) : '';
        $account_name = (isset($instance['account_name'])) ? strip_tags($instance['account_name']) : '';
        $account_link = (isset($instance['account_link'])) ? strip_tags($instance['account_link']) : '';
        $display_count = empty($instance['display_count']) ? FLUXOPHY_DISPLAY_COUNT: (int) $instance['display_count'];

?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'fluxophy'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('source_url'); ?>"><?php _e('Source URL:', 'fluxophy'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('source_url'); ?>" name="<?php echo $this->get_field_name('source_url'); ?>" type="text" value="<?php echo esc_attr($source_url); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('picture_url'); ?>"><?php _e('Picture URL:', 'fluxophy'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('picture_url'); ?>" name="<?php echo $this->get_field_name('picture_url'); ?>" type="text" value="<?php echo esc_attr($picture_url); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('account_name'); ?>"><?php _e('Account Name:', 'fluxophy'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('account_name'); ?>" name="<?php echo $this->get_field_name('account_name'); ?>" type="text" value="<?php echo esc_attr($account_name); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('account_link'); ?>"><?php _e('Account Link:', 'fluxophy'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('account_link'); ?>" name="<?php echo $this->get_field_name('account_link'); ?>" type="text" value="<?php echo esc_attr($account_link); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('display_count'); ?>"><?php _e('Number of entries to show (default 10):', 'fluxophy') ?></label>
            <input id="<?php echo $this->get_field_id('display_count'); ?>" name="<?php echo $this->get_field_name('display_count'); ?>" type="text" value="<?php echo $display_count; ?>" size="3" />
        </p>
<?php
    }
}


function fluxophy_display_data($source_url, $display_count, $picture_url, $account_name, $account_link) {
    $output = '';
    $output .= '<div>';
    $output .= '<a href="'. $account_link . '" class="fluxophy_button_home" target="_blank">';
    $output .= '<span class="fluxophy_button_home_icon">';
    $output .= '<svg viewBox="0 0 33 33" width="25" height="25" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><path d="M 17.996,32L 12,32 L 12,16 l-4,0 l0-5.514 l 4-0.002l-0.006-3.248C 11.993,2.737, 13.213,0, 18.512,0l 4.412,0 l0,5.515 l-2.757,0 c-2.063,0-2.163,0.77-2.163,2.209l-0.008,2.76l 4.959,0 l-0.585,5.514L 18,16L 17.996,32z"></path></g></svg>';
    $output .= '</span>';
    $output .= '<span class="fluxophy_button_home_text">';
    $output .= $account_name;
    $output .= '</span>';
    $output .= '</a>';
    $output .= '</div>';

    $output .= '<ul id="fluxophy_widget">';
    $output .= fluxophy_fetch_data_fb($source_url, $display_count, $picture_url);
    $output .= '</ul>';

    return $output;
}

function fluxophy_fetch_data_fb($source_url, $display_count, $picture_url) {
    $output = '';
    $request_result = fluxophy_get_contents_for_browser($source_url);

    if ($request_result) {
        $received = json_decode($request_result);

        for ($i = 0; $i < $display_count; $i++) {
            $output .= '<li class="fluxophy_widget">';
            $output .= '<div class="fluxophy_entry_header">';

            if (isset($picture_url)) {
                $output .= '<img src="' . $picture_url . '"></img>';
            }

            $output .= '<a href="' . $received->entries[$i]->alternate . '" title="' . date_i18n('G:i - d F Y', strtotime($received->entries[$i]->published)) . '" target="_blank">';
            $output .= date_i18n('d M', strtotime($received->entries[$i]->published));
            $output .= '</a>';

            $output .= '</div>';
            $output .= '<div class="fluxophy_entry_data">';
            $output .= $received->entries[$i]->title;
            $output .= '</div>';
            $output .= '</li>';
        }
    }

    return $output;
}

// initiate a fake curl session to mimmick browser
function fluxophy_get_contents_for_browser($url) {
    $curl = curl_init();

    $header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
    $header[] = "Cache-Control: max-age=0";
    $header[] = "Connection: keep-alive";
    $header[] = "Keep-Alive: 300";
    $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $header[] = "Accept-Language: en-us,en;q=0.5";
    $header[] = "Pragma: "; // keep this blank.

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_REFERER, '');
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    $resp = curl_exec($curl);
    curl_close($curl);

    return $resp;
}

?>
