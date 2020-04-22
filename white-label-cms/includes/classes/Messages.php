<?php

class WLCMS_Messages
{
    public static function queue($message, $class = '')
    {
        $default_allowed_classes = array('error', 'warning', 'success', 'info');
        $allowed_classes = apply_filters('wlcms_messages_allowed_classes', $default_allowed_classes);
        $default_class = apply_filters('wlcms_messages_default_class', 'success');

        if (!in_array($class, $allowed_classes)) {
            $class = $default_class;
        }

        $messages = maybe_unserialize(get_option('_wlcms_messages', array()));
        $messages[$class][] = $message;

        update_option('_wlcms_messages', $messages);
    }

    public static function show()
    {
        $group_messages = maybe_unserialize(get_option('_wlcms_messages'));
        
        if (!$group_messages) {
            return;
        }

        $errors = "";
        if (is_array($group_messages)) {
            foreach ($group_messages as $class => $messages) {
                $errors .= '<div class="notice notice-' . $class . ' is-dismissible"">';
                $prev_message = '';
                foreach ($messages as $message) {
                    if( $prev_message !=  $message)
                    $errors .= '<p>' . $message . '</p>';
                    $prev_message =  $message;
                }
                $errors .= '</div>';
            }
        }

        delete_option('_wlcms_messages');

        echo $errors;
    }
}

if (class_exists('WLCMS_Messages') && !function_exists('WLCMS_Queue')) {
    function WLCMS_Queue($message, $class = null)
    {
        WLCMS_Messages::queue($message, $class);
    }
}
add_action('admin_notices', array('WLCMS_Messages', 'show'));