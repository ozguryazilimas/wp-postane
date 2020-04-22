<?php
class Welcome_Messages_Elementor
{
    private $key;
    private $settings;
    public function process($settings, $key)
    {
        if( ! isset($settings['page_id_elementor']) ) return;
        if( $settings['page_id_elementor'] == "" ) return;
        
        $this->key = $key;
        $this->settings = $settings;
        add_action( 'admin_notices', array( $this, 'welcome_panel' ) );
    }

    public function welcome_panel()
    {
        ?>
        <div id="welcome-panel<?php echo $this->key?>" data-welcome_key="<?php echo $this->key?>" class="welcome-panel wlcms-welcome-panel" style="display:none">
        <?php
        if(isset($this->settings['dismissible'])):
            ?><a class="welcome-panel-close" href="#" aria-label="Dismiss the welcome panel">Dismiss</a>
        <?php endif?>
            <div class="welcome-panel-content welcome-panel-content<?php echo $this->key?>">
                <h2>
                <?php if( isset($this->settings['page_id_elementor']) && isset($this->settings['show_title']) ):?>
                    <?php echo get_the_title($this->settings['page_id_elementor'])?>
                <?php endif;?>
                &nbsp;
                </h2>
                <?php $this->template(); ?>
            </div>
        </div>
        <?php
        $welcome = sprintf(";jQuery('#welcome-panel%1\$d').insertBefore('#dashboard-widgets-wrap');jQuery('#welcome-panel%1\$d').show();", $this->key);
        wlcms_add_js($welcome);
    }

    public function template()
    {
        if( ! $this->settings['page_id_elementor'] ) return;
        $elementor = @Elementor\Plugin::instance();

        $elementor->frontend->register_styles();
        $elementor->frontend->enqueue_styles();

        $elementor->frontend->register_scripts();
        $elementor->frontend->enqueue_scripts();

        echo $elementor->frontend->get_builder_content($this->settings['page_id_elementor'], true);

    }
}
