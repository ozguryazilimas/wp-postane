<style>
    .apt-section-wrapper {
        width: 100%;
        margin-top: 10px;
        padding-right: 20px;
        box-sizing: border-box;
    }

    .apt-section {
        padding: 29px 29px 29px 29px;
    }

    .apt-section .container {
        display: block;
        margin-right: auto;
        margin-left: auto;
        position: relative;
        max-width: 1140px;
        min-height: 400px;
    }

    .apt-section-intro {
        width: 1280px;
        height: 414px;
        box-shadow: 0px 0px 24px rgba(107, 107, 107, 0.5);
        text-align: center;
        margin: 0 auto;
        padding: 0;
    }

    .apt-section-intro img {
        width: 100%;
        height: auto;
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

    .apt-section-video p {
        font-size: 16px;
        text-align: center;
        padding: 30px;
    }

    .apt-section-video iframe {
        margin: 0 auto;
        display: block;
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

    @media screen and (max-width: 1500px) {
        .apt-section .container {
            min-height: 300px;
        }

        .apt-section-intro {
            box-sizing: border-box;
            width: 100%;
            min-height: auto;
            height: calc(100% - 10px);
        }

        .apt-section-video p {
            padding: 10px;
        }

        .apt-section-video iframe {
            width: 100%;
        }
    }
</style>
<div class="apt-section-wrapper">
    <div class="apt-section apt-section-intro">
        <img src="<?php echo WAPT_PLUGIN_URL; ?>/admin/assets/img/photo_2019-09-10_11-21-14.jpg" alt="">
    </div>
    <section class="apt-section apt-section-video">
        <div class="container">
            <p><?php printf( __( 'We suppose you’ve noticed the changes which happened with <a href="%s" target="_blank" rel="noopener">Auto Post Thumbnail</a>. In this tutorial you can get more information about new features.', 'apt' ), 'https://cm-wp.com/apt/' ) ?></p>
            <iframe width="800" height="441" src="https://www.youtube.com/embed/rucqKNdVQGY" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </section>
    <section class="apt-section apt-section-changelog">
        <div class="container">
            <div>
                <h4>3.4.2</h4>
                <p><?php _e( 'As you’ve already noticed, we haven’t updated the plugin for more than 2 years. This new version fixes existing problems. APT becomes a fully functional plugin.', 'apt' ) ?></p>
                <h4>3.5.0</h4>
                <p><?php _e( 'Starting from this version, the APT plugin evolves from being an aiding tool to the full-featured search & image editing system with a Creative Commons license for your website. It means that you get:', 'apt' ) ?></p>
                <ul>
                    <li><?php _e( 'Search and download images from Google', 'apt' ) ?></li>
                    <li><?php _e( 'Auto generate feature image in editor', 'apt' ) ?></li>
                    <li><?php _e( 'Image search through the 3 popular stock services from the plugin interface. Just enter a search query and choose an image (images) you like.', 'apt' ) ?></li>
                </ul>
                <h4>3.6.0</h4>
                <ul>
                    <li><?php _e( 'Upload images from the external URL to your post or product (for Woocommerce).', 'apt' ) ?></li>
                    <li><?php _e( 'Compatibility with the most popular builders.', 'apt' ) ?></li>
                </ul>
                <h4>3.7.0</h4>
                <ul>
                    <li><?php _e( 'Advanced APT editor. You can edit images using layers. It means that you can overlay text, logo, or mask, adjust color, brightness, and contract and use other great features. Save presets and apply them on any image in one click. The editor doesn’t replace the default WordPress editor.', 'apt' ) ?></li>
                </ul>
            </div>
        </div>
    </section>
</div>