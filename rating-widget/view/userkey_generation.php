<div class="wrap rw-dir-ltr">
    <h2><?php _e( 'Rating-Widget Account Generation', WP_RW__ID ); ?></h2>
    <p style="line-height: 18px;">
        Before you start using the Rating-Widget plugin it's important for you to understand that the Rating-Widget project is a self-hosted rating system for your website. It is based on dynamic HTML & Javascript and was intestinally developed as plug &amp; play widget for easy installation (without the need of setting any DB or backend support).
        Therefore all the ratings and voting data is sent and stored on Rating-Widget's servers. In addition, limited personal information like your email and Blog name is sent and stored so we can stay in touch with you and send you different announcements (e.g. maintenance, updates, new release). The truth is that we rarely send any emails and you would have the option to unsubscribe from the mailing list.
        Please read our full <a href="<?php echo WP_RW__ADDRESS;?>/terms-of-use/" target="_blank">Terms of Use</a> and <a href="<?php echo WP_RW__ADDRESS;?>/privacy/" target="_blank">Privacy Policy</a> for more details before you start using the plugin.<br />
    </p>

    <form action="" method="post">
        <script type="text/javascript">
            var RecaptchaOptions = { theme : 'white' };
        </script>
        <div id="rw_recaptcha_container">
            <script type="text/javascript">
                document.write('<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=' + 
                               RWM.RECAPTCHA_PUBLIC + '"></' + 'script>');
            </script>
        </div>
        <noscript>
            <script type="text/javascript">
                document.write('<iframe src="http://www.google.com/recaptcha/api/noscript?k=' + RWM.RECAPTCHA_PUBLIC + '" height="300" width="500" frameborder="0"></iframe><br>');
            </script>
            <textarea name="recaptcha_challenge_field" rows="3" cols="40">
            </textarea>
            <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
        </noscript>
        <p class="submit">
            <label><input type="checkbox" name="rw_service_terms" value="1" /> I've read and I accept the <a href="<?php echo WP_RW__ADDRESS;?>/terms-of-use/" target="_blank">Terms of Use</a> and <a href="<?php echo WP_RW__ADDRESS;?>/privacy/" target="_blank">Privacy Policy</a> of the <a href="<?php echo WP_RW__ADDRESS;?>" target="_blank">Rating-Widget</a> service.</label>
            <br />
            <input type="hidden" name="action" value="account" />
            <br />
            <input type="submit" value="I accept - Activate Account »" />
        </p>
        <p>
        </p>
    </form>
    <div style="text-align: center; margin: 20px auto; padding: 20px; border-top: 1px solid #ccc">
        <a href="<?php echo WP_RW__ADDRESS;?>/track/?s=1&r=<?php echo urlencode("http://www.host1plus.com");?>" title="Host1Plus Hosting" target="_blank"><img src="<?php echo WP_RW__ADDRESS;?>/track/?s=1&t=<?php echo time();?>&r=<?php echo urlencode(WP_RW__ADDRESS_IMG . "sponsor/host1plus/728x90.jpg");?>" alt="" /></a>
    </div>
</div>
