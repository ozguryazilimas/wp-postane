<?php #comp-page builds: premium

/**
 * Добавление новых опций в базу данных
 */
class WAPTUpdate030700 extends Wbcr_Factory466_Update
{

    public function install()
    {
        if (is_multisite() && $this->plugin->isNetworkActive()) {
            global $wpdb;

            $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

            if (!empty($blogs)) {
                foreach ($blogs as $id) {

                    switch_to_blog($id);

                    $this->new_migration();

                    restore_current_blog();
                }
            }

            return;
        }

        $this->new_migration();
    }

    /**
     * @author Artem Prihodko <webtemyk@yandex.ru>
     * @since  3.7.0
     */
    public function new_migration()
    {

        $this->plugin->updateOption('generate_autoimage', 'find');

        $this->plugin->updateOption('background-type', 'color');
        $this->plugin->updateOption('background-color', '#ff6262');
        $this->plugin->updateOption('background-image', '');
        $this->plugin->updateOption('default-background', '');
        $this->plugin->updateOption('image-type', 'jpg');

        $this->plugin->updateOption('font', 'Arial.ttf');
        $this->plugin->updateOption('font-size', 25);
        $this->plugin->updateOption('font-color', '#ffffff');

        $this->plugin->updateOption('shadow', 0);
        $this->plugin->updateOption('shadow-color', '#000000');

        $this->plugin->updateOption('text-transform', 'no');
        $this->plugin->updateOption('text-crop', 100);
        $this->plugin->updateOption('text-line-spacing', 1.5);

        $this->plugin->updateOption('text-align-horizontal', 'center');
        $this->plugin->updateOption('text-align-vertical', 'center');

        $this->plugin->updateOption('text-padding-lr', 15);
        $this->plugin->updateOption('text-padding-tb', 15);

        $this->plugin->updateOption('before-text', '');
        $this->plugin->updateOption('after-text', '');
    }
}
