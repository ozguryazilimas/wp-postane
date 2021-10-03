<?php
/*
Plugin Name: Hodor
Plugin URI: https://www.ozguryazilim.com.tr
Description: Protects registration screen from automated attacks.
Version: 0.5.1
Author: Onur Küçük
Author URI: https://www.delipenguen.net
License: GPL2
*/

/*  Copyright (C) 2020, Onur Küçük

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function hodor_generate_random_string($string_length) {
  return bin2hex(random_bytes($string_length / 2));
}

function hodor_generate_transient_token() {
  $token = array(
    'redirect_token_key' => hodor_generate_random_string(8),
    'redirect_token_value' => hodor_generate_random_string(32),
    'register_form_salt_key' => hodor_generate_random_string(8),
    'register_form_token_key' => hodor_generate_random_string(8),
    'register_form_secret' => hodor_generate_random_string(32)
  );

  return $token;
}

function hodor_generate_token_hash($secret, $salt) {
  return hash_hmac('sha256', $secret, $salt);
}

function hodor_transient_token() {
  $transient_name = 'hodor_transient_token';
  $request_result = get_transient($transient_name);

  if (false === $request_result) {
    $request_result = hodor_generate_transient_token($transient_name);
    $timeout = random_int(15, 60) * MINUTE_IN_SECONDS;
    set_transient($transient_name, $request_result, $timeout);
  }

  return $request_result;
}

function hodor_validate_register_form_token($user_input, $salt, $token_secret = null) {
  if (!$token_secret) {
    $token = hodor_transient_token();
    $token_secret = $token['register_form_secret'];
  }

  $expected = hodor_generate_token_hash($token_secret, $salt);

  return hash_equals($expected, $user_input);
}

function hodor_register_url($url) {
  if (is_admin()) {
    return $url;
  }

  $token = hodor_transient_token();
  $new_path = 'wp-login.php?action=register&' . $token['redirect_token_key'] . '=' . $token['redirect_token_value'];

  return site_url($new_path, 'login');
}
add_filter('register_url', 'hodor_register_url');

function hodor_register_form_customize() {
  $token = hodor_transient_token();
  $redirect_key = $token['redirect_token_key'];
  $redirect_value = $token['redirect_token_value'];
  $redirect_token_valid = isset($_REQUEST[$redirect_key]) && hash_equals($redirect_value, $_REQUEST[$redirect_key]);

  if (!$redirect_token_valid) {
    ob_get_clean();
    die();
  }

  $register_form_secret = $token['register_form_secret'];
  $register_form_salt_key = 'Z' . $token['register_form_salt_key'];
  $register_form_token_key = 'Z' . $token['register_form_token_key'];
  $register_form_salt_value = hodor_generate_random_string(8);
  $register_form_token_value = hodor_generate_token_hash($register_form_secret, $register_form_salt_value);
  $register_form_token_value_splitted = str_split($register_form_token_value, 8);
  $token_value_ordered_keys = array();

  $input_mapping = array(
    $redirect_key => $redirect_value,
    $register_form_salt_key => $register_form_salt_value,
    $register_form_token_key => hodor_generate_random_string(8)
  );

  foreach ($register_form_token_value_splitted as $k) {
    $key = hodor_generate_random_string(8);
    $input_mapping[$key] = $k;
    array_push($token_value_ordered_keys, base64_encode($key));
  }

  $shuffled_keys = array_keys($input_mapping);
  shuffle($shuffled_keys);

  foreach ($shuffled_keys as $k) {
    echo '<input type="hidden" id="' . $k . '" name="' . $k . '" value="' . $input_mapping[$k] . '">' . "\n";
  }

?>

  <script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(event) {

    setTimeout(function() {
      var val = ''
<?php
      foreach ($token_value_ordered_keys as $k) {
        echo "      + document.getElementById(atob('" . $k . "')).value\n";
      }
      echo ';';

      foreach ($shuffled_keys as $k) {
        if (hash_equals($redirect_key, $k) || hash_equals($register_form_salt_key, $k)) {
          continue;
        }

        echo "      document.getElementById('" . $k . "').value = val;\n";
      }
?>
    }, 1200);

  });
  </script>

<?php
}
add_action('register_form', 'hodor_register_form_customize');

function hodor_register_form_validate_token($errors, $sanitized_user_login, $user_email) {
  $token = hodor_transient_token();
  $register_form_secret = $token['register_form_secret'];
  $register_form_salt_key = 'Z' . $token['register_form_salt_key'];
  $register_form_token_key = 'Z' . $token['register_form_token_key'];
  $register_form_token_valid = false;

  // esc_attr(stripslashes($user_email));
  if (isset($_POST[$register_form_salt_key]) && isset($_POST[$register_form_token_key])) {
    $token_value = $_POST[$register_form_token_key];
    $salt_value = $_POST[$register_form_salt_key];
    $register_form_token_valid = hodor_validate_register_form_token($token_value, $salt_value, $register_form_secret);
  }

  if (!$register_form_token_valid) {
    $errors->add('email_error', '¯\_(ツ)_/¯');
  }

  return $errors;
}
add_filter('registration_errors', 'hodor_register_form_validate_token', 10, 3);


?>
