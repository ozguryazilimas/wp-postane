<style>

    .apt-section-wrapper {
        width: 100%;
        margin-top: 10px;
    }

    .apt-section {
        padding: 29px 29px 29px 29px;
    }

    .apt-section .container {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        margin-right: auto;
        margin-left: auto;
        position: relative;
        max-width: 1140px;
        min-height: 600px;
        -webkit-box-align: center;
        -webkit-align-items: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .apt-section-intro {
        background-image: url(http://cm-wp.com/wp-content/uploads/2019/05/fon.png);
        background-position: bottom center;
        background-size: cover;
        box-shadow: 0px 0px 34px 0px rgba(107, 107, 107, 0.5);
        transition: background 0.3s, border 0.3s, border-radius 0.3s, box-shadow 0.3s;
        text-align: center;
    }

    .apt-section-intro .container h2 {
        font-size: 61px;
        font-weight: 500;
        text-transform: uppercase;
        line-height: 1.1em;
        color: #fff;
        text-align: center;
    }

    .apt-section-intro .container p {
        margin-bottom: 1.6em;
        color: #fffcfc;
        font-family: "Arial", Sans-serif;
        font-size: 22px;
        line-height: 1.3em;
        letter-spacing: 1.1px;
    }

    .apt-section-changelog h4 {
        font-size: 1.3333333333333rem;
    }

    .apt-section-changelog p,
    .apt-section-changelog ul > li {
        font-size: 15px;
    }

    .apt-section-changelog ul {
        list-style: inherit;
        margin-left: 40px;
    }

    #wpfooter {
        position: relative !important;
    }


</style>

<div class="apt-section-wrapper">
    <section class="apt-section apt-section-intro">
        <div class="container">

            <div>
                <h2><?php esc_html_e( 'Auto Post Thumbnail', 'apt' ) ?></h2>

                <p><?php esc_html_e( 'We didn’t please you with updated lately. However, great news today! We are about to tell you about all the spectacular changes that are planned for our plugin!', 'apt' ) ?></p>

                <p><?php echo __( 'First of all, we proudly announce that a new group of developers, <span style="text-decoration: underline;"><strong>Creative Motion</strong></span>, are helping us with plugin improvement.', 'apt' ) ?></p>

                <p><?php esc_html_e( 'Auto Post Thumbnails has perfectly fit in our close family of popular plugins with more than 600,000 users worldwide.', 'apt' ) ?></p>

                <p><?php esc_html_e( 'What you can expect soon:', 'apt' ) ?></p>

            </div>
        </div>

    </section>

    <section class="apt-section apt-section-changelog">
        <div class="container">
            <div>
                <h4>3.4.2</h4>
                <p><?php esc_html_e( 'As you’ve already noticed, we haven’t updated the plugin for more than 2 years. This new version fixes existing problems. APT becomes a fully functional plugin.', 'apt' ) ?></p>

                <h4>3.5.0</h4>
                <p><?php esc_html_e( 'In the next release, you can automatically generate featured images from any image in the post, not only the first one. Besides, we offer you an advanced tool – choose an image for the featured image right from the Posts tab. You no longer need to edit each post to install or change the featured image. Feel free to do it right from the list of posts. It saves much time and efforts. ​', 'apt' ) ?>
                    ​</p>


                <h4>3.5.0</h4>
                <p><?php esc_html_e( 'Starting from this version, the APT plugin evolves from being an aiding tool to the full-featured search & image editing system with a Creative Commons license for your website. <strong>It means that you get:</strong>', 'apt' ) ?></p>

                <ul>
                    <li><?php esc_html_e( 'Image search through the 5 popular stock services from the plugin interface. Just enter a search query and choose an image(images) you like.', 'apt' ) ?></li>
                    <li><?php esc_html_e( 'Advanced APT editor. You can edit images using layers. It means that you can overlay text, logo, or mask, adjust color, brightness, and contract and use other great features. Save presets and apply them on any image in one click. The editor doesn’t replace the default WordPress editor.', 'apt' ) ?></li>
                </ul>


                <h4>3.5.0</h4>
                <ul>
                    <li><?php esc_html_e( 'Upload images from the external URL to your post or product (for Woocommerce).', 'apt' ) ?></li>
                    <li><?php esc_html_e( 'Compatibility with the most popular builders.', 'apt' ) ?></li>
                </ul>
            </div>
        </div>
    </section>
</div>