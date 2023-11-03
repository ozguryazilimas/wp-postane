<?php
class Welcome_Messages_Page
{
    private $key;
    private $settings;
    private $template;
    public function process($settings, $key)
    {
        if( ! isset($settings['page_id_page']) ) return;

        $this->key = $key;
        $this->settings = $settings;
        $this->template = $settings['page_id_page'];
        
        add_action( 'in_admin_header', array( $this, 'welcome_panel' ) );
    }
    
    
    public function template()
    {
        if(!$this->template || ($this->template && $this->template=='')) {
            return;
        }
        $url = get_permalink($this->template);
        echo "<iframe class=\"responsive-iframe\" onLoad=\"wlcms_iframe_height(this)\" frameborder=\"0\" scrolling=\"no\" width=\"100%\" src=\"{$url}\"></iframe>";
    }
    
    public function welcome_panel()
    {
        ?>
        <div id="welcome-panel<?php echo $this->key?>" data-welcome_key="<?php echo $this->key?>" class="wlcms-welcome-panel" style="display:none">
        <?php
        if(isset($this->settings['dismissible'])):
        ?><a class="welcome-panel-close" href="#" aria-label="Dismiss the welcome panel">Dismiss</a>
        <?php endif?>
            <div class="welcome-panel-content welcome-panel-content<?php echo $this->key?>">
                <?php if( isset($this->settings['page_id_page']) && isset($this->settings['show_title']) ):?>
                <h2>
                    <?php echo get_the_title($this->settings['page_id_page'])?>
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