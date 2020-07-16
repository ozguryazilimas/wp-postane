<?php

class Admin_Dashboard_Welcome_Message
{
    private $settings, $key;

    public function set($settings, $key)
    {
        $this->settings = $settings;
        $this->key = $key;
    }

    public function handle()
    {

        if( ! $this->settings['template_type'] ) return;
        $type = $this->settings['template_type'];
        wlcms()->require_class("Welcome_Messages/Welcome_Messages_Elementor");
        wlcms()->require_class("Welcome_Messages/Welcome_Messages_Html");
        wlcms()->require_class("Welcome_Messages/Welcome_Messages_Beaver_Builder");
        
        if( $type == 'Elementor' ){
            $template = new Welcome_Messages_Elementor();
        }elseif( $type == 'Beaver Builder' ){
            $template = new Welcome_Messages_Beaver_Builder();
        }else {
            $template = new Welcome_Messages_Html();
        }
        
        $template->process($this->settings, $this->key);

    }
   
    public function make_welcome_panel()
    {
        
        $nonce = wp_create_nonce("vum_hide_dashboard_nonce");
        $link = admin_url('admin-ajax.php');
    
        $welcome = sprintf(";jQuery('.wlcms-welcome-panel a.welcome-panel-close').on('click', function(e){
            e.preventDefault();
            var vum_panel = jQuery(this).parent('.wlcms-welcome-panel');
            jQuery.ajax({
                type : 'post',
                dataType : 'json',
                url : '%s',
                data : {action: 'hide_vum_dashboard', key : vum_panel.data('welcome_key'), nonce: '%s'},
                success: function(response) {
                    if(response.type == 'success') {
                        vum_panel.hide();
                    }
                }
            })
        });", $link, $nonce);
        wlcms_add_js($welcome);
    }
}
