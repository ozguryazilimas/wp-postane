<?php
/*
 * Copyright (c) 2018, Onur Küçük <onur@ozguryazilim.com.tr>
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 */

function bududu_post_images() {
  global $post, $posts;

  $image_array = array();

  $attachment_args = array(
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'numberposts'    => -1,
    # 'order'          => 'ASC',
    'post_status'    => null,
    'post_parent'    => $post->ID
  );

  $attachments = get_posts($attachment_args);

  if ($attachments) {
    foreach ($attachments as $attachment) {
      $image_url =  wp_get_attachment_url($attachment->ID);

      if (substr($image_url, 0, 1) == '/') {
        $image_url = get_option('siteurl') . $image_url;
      }

      array_push($image_array, $image_url);
    }
  } else {
    $image_array[0] = '';
  }

  return $image_array;
}

function bududu_opengraph_tags() {
  $output = '';
  $output_images = '';

  $og_type = 'article' ;
  $og_site_name = get_option('blogname');

  // post
  if (is_single() || is_page()) {
    if (have_posts()) {
      while (have_posts()) {
        the_post();

        $og_title = get_the_title($post->post_title);
        $og_url = get_permalink();
        $og_description = $post->post_excerpt;
        $itemprop_name = get_the_title($post->post_title);
        $itemprop_description = $post->post_excerpt;

        $image_array = bududu_post_images();

        foreach ($image_array as $image) {
          if ($image != '') {
            $output_images .= sprintf("\n<meta property='og:image' content='%s' />", $image);
          }
        }

        $output_images .= sprintf("\n<meta itemprop='image' content='%s' />", $image_array[0]);
       }
    }
  } else {
    $og_title = get_option('blogname');
    $og_url = get_option('siteurl');
    $og_description = get_option('blogdescription');
    $itemprop_name = get_option('siteurl');
    $itemprop_description = get_option('blogdescription');

    if (is_home() || is_front_page()) {
      $og_type = 'blog' ;
    }
  }

  $output .= sprintf("\n<meta property='og:title' content='%s' />", $og_title);
  $output .= sprintf("\n<meta property='og:url' content='%s' />", $og_url);
  $output .= sprintf("\n<meta property='og:site_name' content='%s' />", $og_site_name);
  $output .= sprintf("\n<meta property='og:description' content='%s' />", $og_description);
  $output .= sprintf("\n<meta property='og:type' content='%s' />", $og_type);
  $output .= sprintf("\n<meta itemprop='name' content='%s' />", $itemprop_name);
  $output .= sprintf("\n<meta itemprop='description' content='%s' />", $itemprop_description);

  echo $output . $output_images;
}

function bududu_buttons() {
  $buttons = array(
    'twitter' => array(
      'logo' => 'fab fa-twitter',
      'share' => 'https://twitter.com/share?url={url}&text={text}',
    ),
    'facebook' => array(
      'logo' => 'fab fa-facebook-f',
      'share' => 'https://facebook.com/sharer/sharer.php?u={url}',
    ),
    #'googleplus' => array(
    #  'logo' => 'fab fa-google',
    #  'share' => 'https://plus.google.com/share?url={url}',
    #),
    # 'linkedin' => array(
    #   'logo' => 'fab fa-linkedin',
    #   'share' => 'https://www.linkedin.com/shareArticle?mini=true&url={url}',
    # ),
    'pinterest' => array(
      'logo' => 'fab fa-pinterest',
      'share' => 'https://pinterest.com/pin/create/bookmarklet/?url={url}&description={text}',
    ),
    'stumbleupon' => array(
      'logo' => 'fab fa-stumbleupon',
      'share' => 'http://www.stumbleupon.com/submit?url={url}&title={text}',
    ),
    # 'pocket' => array(
    #   'logo' => 'fab fa-get-pocket',
    #   'share' => 'https://getpocket.com/save?url={url}&title={text}',
    # ),
    'whatsapp' => array(
      'logo' => 'fab fa-whatsapp',
      'share' => 'whatsapp://send?text={url} {text}',
      'mobile' => true
    ),
    'telegram' => array(
      'logo' => 'fab fa-telegram',
      'share' => 'tg://msg_url?text={url}&text={text}',
      'mobile' => true
    ),
    'viber' => array(
      'logo' => 'fab fa-viber',
      'share' => 'viber://forward?text={url} {text}',
      'mobile' => true
    )
  );

  return $buttons;
}

function bududu_build_link($the_link, $the_title, $name, $options) {
  $logo = $options['logo'];
  $share = $options['share'];

  if ($options['mobile']) {
    $mobile_class = 'bududu_mobile';
  } else {
    $mobile_class = '';
  }

  $output = sprintf('
    <div id="bududu_%s" class="bududu_share bududu_share_%s %s">
      <a id="bududu_%s_link" class="bududu_share_link" data-share="%s" data-url="%s" data-text="%s" href="#" title="%s">
        <i class="%s"/></i>
      </a>
    </div>
    ',
    $name,
    $name,
    $mobile_class,
    $name,
    $share,
    $the_link,
    $the_title,
    $name,
    $logo
  );

  return $output;
}


function bududu_content_filter($content, $force = false) {

  if (in_the_loop()) {
    $the_link = strip_tags(get_permalink());
    $the_title = strip_tags(get_the_title());
  } else {
    # $the_link = get_option('siteurl');
    # $the_title = get_option('blogname');
    return $content;
  }

  $output = '  <div id="bududu_container">';

  // $output .= bududu_upstream_links($the_link, $the_title);

  foreach (bududu_buttons() as $name => $opts) {
    $output .= bududu_build_link($the_link, $the_title, $name, $opts);
  }

  $output .= '  </div>';

  return $content . $output;
}

# function bududu_upstream_links($the_link, $the_title) {
#   $combined_text = $the_link . ' - ' . $the_title;
#   $output = '';
#
#   $output .= sprintf('
#       <div id="bududu_tweet" class="bududu_button">
#         <a class="twitter-share-button" href="https://twitter.com/inetnt/tweet" data-size="large" data-count="horizontal"
#           data-url="%s"
#           data-text="%s">Tweet</a>
#       </div>
#
#       <script>
#          window.twttr = (function(d, s, id) {
#          var js, fjs = d.getElementsByTagName(s)[0],
#            t = window.twttr || {};
#          if (d.getElementById(id)) return t;
#          js = d.createElement(s);
#          js.id = id;
#          js.src = "//platform.twitter.com/widgets.js";
#          fjs.parentNode.insertBefore(js, fjs);
#
#          t._e = [];
#          t.ready = function(f) {
#            t._e.push(f);
#          };
#
#          return t;
#          }(document, "script", "twitter-wjs"));
#        </script>
#       ', $the_link, $the_title);
#
#   $output .= sprintf('
#       <div id="bududu_facebook" class="bududu_button">
#         <div id="fb-root"></div>
#         <div class="fb-like" data-send="false" data-layout="button_count" data-width="" data-show-faces="" data-action="like" size="large"
#           data-href="%s">
#         </div>
#       </div>
#
#       <script>(function(d, s, id) {
#          var js, fjs = d.getElementsByTagName(s)[0];
#          if (d.getElementById(id)) return;
#          js = d.createElement(s); js.id = id;
#          js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0";
#          fjs.parentNode.insertBefore(js, fjs);
#            }(document, "script", "facebook-jssdk"));
#        </script>
#       ', $the_link);
#
#   $output .= sprintf('
#       <div id="bududu_gplus" class="bududu_button">
#         <div class="g-plus" data-action="share" data-annotation="bubble" data-height="32" data-href="%s"></div>
#       </div>
#
#       <script type="text/javascript">
#       (function() {
#         var po = document.createElement("script");
#         po.type = "text/javascript";
#         po.async = true;
#         po.src = "//apis.google.com/js/platform.js";
#         var s = document.getElementsByTagName("script")[0];
#         s.parentNode.insertBefore(po, s);
#       })();
#       </script>
#
#
#       ', $the_link);
#
#   $output .= sprintf('
#       <div id="bududu_pinterest" class="bududu_button">
#         <a href="https://www.pinterest.com/pin/create/button/" data-pin-do="buttonBookmark"
#           data-pin-tall="true"
#           data-pin-url="%s"
#           data-pin-media="%s"
#           data-pin-description="%s"
#           >
#         </a>
#       </div>
#
#       <script type="text/javascript">
#         (function() {
#           window.PinIt = window.PinIt || { loaded:false };
#           if (window.PinIt.loaded) return;
#           window.PinIt.loaded = true;
#           function async_load(){
#             var s = document.createElement("script");
#             s.type = "text/javascript";
#             s.async = true;
#             s.src = "//assets.pinterest.com/js/pinit.js";
#             var x = document.getElementsByTagName("script")[0];
#             x.parentNode.insertBefore(s, x);
#           }
#           if (window.attachEvent)
#               window.attachEvent("onload", async_load);
#           else
#               window.addEventListener("load", async_load, false);
#         })();
#       </script>
#       ', $the_link, '', $the_title);
#
#   $whatsapp_image = plugins_url('../images/bududu_whatsapp.png', __FILE__);
#   $output .= sprintf('
#     <div id="bududu_whatsapp" class="bududu_button">
#       <a id="bududu_whatsapp_link" data-link="%s" data-text="%s">
#         <img id="bududu_whatsapp_icon" src="%s" />
#       </a>
#     </div>
#       ', $the_link, $the_title, $whatsapp_image);
#
#   return $output;
# }

# function bududu_footer() {
#   #if (!in_the_loop()) {
#   #  return;
#   #}
#
#   $output = '
#     <script type="text/javascript" charset="utf-8" async src="//platform.twitter.com/widgets.js"></script>
#     <script type="text/javascript" charset="utf-8" async src="//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0"></script>
#     <script type="text/javascript" charset="utf-8" src="//apis.google.com/js/platform.js" async defer></script>
#     <script type="text/javascript" charset="utf-8" async defer src="//assets.pinterest.com/js/pinit.js"></script>
#   ';
#
#   echo $output;
# }

?>
