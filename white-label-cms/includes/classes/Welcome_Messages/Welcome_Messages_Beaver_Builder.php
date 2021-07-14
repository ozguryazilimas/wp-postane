<?php
class Welcome_Messages_Beaver_Builder
{
    private $key;
    private $settings;
    private $template;
    public function process($settings, $key)
    {
        if( ! isset($settings['page_id_beaver']) ) return;

        $this->key = $key;
        $this->settings = $settings;
        $this->template = $settings['page_id_beaver'];
        add_action( 'in_admin_header', array( $this, 'welcome_panel' ) );
        add_action('admin_enqueue_scripts', 'FLBuilder::register_layout_styles_scripts');
    }
    
    public function template()
    {
        echo do_shortcode('[fl_builder_insert_layout id="' . $this->template . '"]');
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
                <?php if( isset($this->settings['page_id_beaver']) && isset($this->settings['show_title']) ):?>
                <h2>
                    <?php echo get_the_title($this->settings['page_id_beaver'])?>
                </h2>
                <?php endif;?>
                <?php $this->template(); ?>
            </div>
        </div>
        <?php
        $welcome = sprintf(";jQuery('#welcome-panel%1\$d').insertBefore('#dashboard-widgets-wrap');jQuery('#welcome-panel%1\$d').show();", $this->key);
        wlcms_add_js($welcome);
    }
}