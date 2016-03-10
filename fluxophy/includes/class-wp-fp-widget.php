<?php
/*
 * Copyright (c) 2014, Onur Küçük <onur@ozguryazilim.com.tr>
 * @license http://www.gnu.org/licenses/gpl-2.0.html  GPLv2
 */

/*
 * WP Fluxophy Widget Class
 */

class WP_Fluxophy_Widget extends WP_Widget {
  function __construct() {
    $widget_ops = array('classname' => 'wp-fp-widget', 'description' => __('Fetches data from external URL and shows as a nice list', 'fluxophy'));
    $control_ops = array('width' => 350);
    parent::__construct('fluxophy', 'Fluxophy', $widget_ops, $control_ops);
  }

  function widget($args, $instance) {
    global $wpdb;

    $cache = wp_cache_get('widget_fluxophy', 'widget');

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
    $twitter_account = $instance['twitter_account'];
    $twitter_data_id = $instance['twitter_data_id'];

    $output .= $before_widget;
    if ($title && $title != '') {
      $output .= $before_title . $title . $after_title;
    }

    $output .= fluxophy_display_data(
      $source_url,
      $display_count,
      $picture_url,
      $account_name,
      $account_link,
      $twitter_account,
      $twitter_data_id
    );

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
    $instance['twitter_account'] = strip_tags($new_instance['twitter_account']);
    $instance['twitter_data_id'] = strip_tags($new_instance['twitter_data_id']);

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
    $twitter_account = (isset($instance['twitter_account'])) ? strip_tags($instance['twitter_account']) : '';
    $twitter_data_id = (isset($instance['twitter_data_id'])) ? strip_tags($instance['twitter_data_id']) : '';
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
      <p>
        <label for="<?php echo $this->get_field_id('twitter_account'); ?>"><?php _e('Twitter Account:', 'fluxophy'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('twitter_account'); ?>" name="<?php echo $this->get_field_name('twitter_account'); ?>" type="text" value="<?php echo esc_attr($twitter_account); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('twitter_data_id'); ?>"><?php _e('Twitter Data ID:', 'fluxophy'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('twitter_data_id'); ?>" name="<?php echo $this->get_field_name('twitter_data_id'); ?>" type="text" value="<?php echo esc_attr($twitter_data_id); ?>" />
      </p>
<?php
  }
}


function fluxophy_display_data($source_url, $display_count, $picture_url, $account_name, $account_link, $twitter_account, $twitter_data_id) {
  $output = '';
  /* old style fb
   *
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
   */

  $output .= '<div>';
  $output .= '<div id="fluxophy_button_show_twitter">';
  $output .= '<span class="fluxophy_button_home fluxophy_button_home_twitter">';
  $output .= '<svg viewBox="0 0 512 512" width="25" height="25" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><path d="M419.6 168.6c-11.7 5.2-24.2 8.7-37.4 10.2 13.4-8.1 23.8-20.8 28.6-36 -12.6 7.5-26.5 12.9-41.3 15.8 -11.9-12.6-28.8-20.6-47.5-20.6 -42 0-72.9 39.2-63.4 79.9 -54.1-2.7-102.1-28.6-134.2-68 -17 29.2-8.8 67.5 20.1 86.9 -10.7-0.3-20.7-3.3-29.5-8.1 -0.7 30.2 20.9 58.4 52.2 64.6 -9.2 2.5-19.2 3.1-29.4 1.1 8.3 25.9 32.3 44.7 60.8 45.2 -27.4 21.4-61.8 31-96.4 27 28.8 18.5 63 29.2 99.8 29.2 120.8 0 189.1-102.1 185-193.6C399.9 193.1 410.9 181.7 419.6 168.6z"></path></g></svg>';
  $output .= '</span>';
  $output .= '</div>';
  $output .= '<div id="fluxophy_button_show_fb" class="active">';
  $output .= '<span class="fluxophy_button_home fluxophy_button_home_fb">';
  $output .= '<svg viewBox="0 0 33 33" width="25" height="25" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><path d="M 17.996,32L 12,32 L 12,16 l-4,0 l0-5.514 l 4-0.002l-0.006-3.248C 11.993,2.737, 13.213,0, 18.512,0l 4.412,0 l0,5.515 l-2.757,0 c-2.063,0-2.163,0.77-2.163,2.209l-0.008,2.76l 4.959,0 l-0.585,5.514L 18,16L 17.996,32z"></path></g></svg>';
  $output .= '</span>';
  $output .= '</div>';
  $output .= '</div>';

  $output .= '<div id="fluxophy_widget_fb">';
  $output .= '<ul id="fluxophy_widget">';
  $output .= fluxophy_fetch_data_fb($source_url, $display_count, $picture_url);
  $output .= '</ul>';
  $output .= '</div>';

  $output .= '<div id="fluxophy_widget_twitter" style="display:none;">';
  $output .= fluxophy_fetch_data_twitter($twitter_account, $twitter_data_id);
  $output .= '</div>';

  $output .= "<script>
    jQuery('#fluxophy_button_show_fb').on('click', function() {
      jQuery('#fluxophy_button_show_twitter').removeClass('active');
      jQuery('#fluxophy_button_show_fb').addClass('active');
      jQuery('#fluxophy_widget_twitter').hide();
      jQuery('#fluxophy_widget_fb').show();
    });
    jQuery('#fluxophy_button_show_twitter').on('click', function() {
      jQuery('#fluxophy_button_show_fb').removeClass('active');
      jQuery('#fluxophy_button_show_twitter').addClass('active');
      jQuery('#fluxophy_widget_fb').hide();
      jQuery('#fluxophy_widget_twitter').show();
    });
    </script>";

  return $output;
}

function fluxophy_fetch_data_fb($source_url, $display_count, $picture_url) {
  $output = '';
  $request_result = get_transient('fluxophy_facebook_response');

  if (false === $request_result) {
    $request_result = fluxophy_get_contents_for_browser($source_url);
    set_transient('fluxophy_facebook_response', $request_result, 1800);
  }

  if ($request_result) {
    $received = json_decode($request_result);

    for ($i = 0; $i < $display_count; $i++) {
      if (isset($received->data[$i]->message)) {
        $output .= '<li class="fluxophy_widget">';
        $output .= '<div class="fluxophy_entry_header">';

        if (isset($picture_url)) {
          $output .= '<img src="' . $picture_url . '"></img>';
        }
        $output .= "<div class='fluxophy_22dakika'><a href='http://www.facebook.com/22dakika.org'>22dakika<br/><span style='font-weight:normal'>/22dakika.org</span></a></div>";
        $output .= '<a href="' . $received->data[$i]->link . '" title="' . date_i18n('G:i - d F Y', strtotime($received->data[$i]->updated_time)) . '" target="_blank">';
        $output .= date_i18n('d M', strtotime($received->data[$i]->updated_time));
        $output .= '</a>';

        $output .= '</div>';
        $output .= '<div class="fluxophy_entry_data">';
        // $output .= $request_result;
        $output .= $received->data[$i]->message;
        $output .= '</div>';
        $output .= '</li>';
      }
    }
  }

  return $output;
}

function fluxophy_fetch_data_twitter($account, $data_id) {
  $output = '<a class="twitter-timeline" href="https://twitter.com/' . $account . '" height="360" data-widget-id="' . $data_id . '">@22dakika kullanıcısından Tweetler</a>' .
    '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';

  return $output;
}

// initiate a fake curl session to mimmick browser
function fluxophy_get_contents_for_browser($url) {
  $header = array(
    // 'Content-Type: application/json',
    'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
    'Cache-Control: max-age=0',
    'Connection: keep-alive',
    'Keep-Alive: 300',
    'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
    'Accept-Language: en-us,en;q=0.5',
    'Pragma: ' // keep this blank
  );

  $options = array(
    CURLOPT_URL => $url,
    CURLOPT_HTTPHEADER => $header,
    CURLOPT_RETURNTRANSFER => true,
    // CURLOPT_FOLLOWLOCATION => true,
    // CURLOPT_USERAGENT => "Mozilla",
    CURLOPT_USERAGENT => 'spider',
    CURLOPT_AUTOREFERER => true,
    // CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
    // CURLOPT_TIMEOUT => 120, // timeout on response
    CURLOPT_TIMEOUT => 20,
    // CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_REFERER => '',
    CURLOPT_ENCODING => 'gzip,deflate'
  );

  $ch = curl_init();
  curl_setopt_array( $ch, $options );

  $result = curl_exec($ch);
  $response_header = curl_getinfo($ch);
  curl_close($ch);

  return $result;
}

?>
