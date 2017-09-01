=== Captcha ===
Contributors: wpdev17
Tags: captcha, capcha, security, spam blocker, simple captcha, antispam, protection, text captcha, captcha numbers, captcha plugin, web form protection, captcha protection
Requires at least: 3.9
Tested up to: 4.8.1
Stable tag: 4.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to implement super security captcha form into web forms. 

== Description ==

The Captcha plugin allows you to implement a super security captcha form into web forms. It protects your website from spam by means of math logic, easily understood by human beings. You will not have to spend your precious time on annoying attempts to understand hard-to-read words, combinations of letters or pictures that make your eyes pop up. All you need is to do one of the three basic maths actions - add, subtract and multiply.
This captcha can be used for login, registration, password recovery, comments forms, contact form7.

= Translation =

* Arabic (ar_AR) (thanks to Albayan Design Hani Aladoli)
* Bangla (bn_BD) (thanks to [SM Mehdi Akram](mailto:mehdi.akram@gmail.com), www.shamokaldarpon.com)
* Belarusian (bel) (thanks to [Natasha Diatko](mailto:natasha.diatko@gmail.com))
* Brazilian Portuguese (pt_BR) (thanks to [Breno Jacinto](mailto:brenojac@gmail.com), www.iconis.org.br)
* Bulgarian (bg_BG) (thanks to [Nick](mailto:paharaman@gmail.com))
* Catalan (ca) (thanks to [Psiete](mailto:psiete@gmail.com))
* Chinese (zh_CN) (thanks to [TIM](mailto:416441872@qq.com), [Jack Chen](email: mailto:beijingtours@foxmail.com), www.jackchen.im)
* Taiwan (zh_TW) (thanks to [Kaiconan](mailto:ch.unk.ai.ma.o@gmail.com))
* Croatian (hr) (thanks to [Daniel](mailto:daniel@croteh.com))
* Czech (cs_CZ) (thanks to [Michal Kučera](mailto:kucerami@gmail.com) www.n0lim.it)
* Danish (da_DK) (thanks to Byrial Ole Jensed)
* Dutch (nl_NL) (thanks to [Bart Duineveld](mailto:byrial@vip.cybercity.dk))
* Estonian (et) (thanks to Ahto Tanner)
* Greek (el) (thanks to Aris, www.paraxeno.net)
* Farsi/Persian (fa_IR) (thanks to [Mostafa Asadi](mailto:mostafaasadi73@gmail.com), www.ma73.ir, [Morteza Gholami](mailto:Morteza.Gholami@Yahoo.Com))
* Finnish (fi) (thanks to Mikko Sederholm)
* French (fr_FR) (thanks to Martel Benjamin, [Capronnier luc](mailto:lcapronnier@yahoo.com))
* German (de_DE) (thanks to Thomas Hartung, [Lothar Schiborr](mailto:lothar.schiborr@web.de))
* Hebrew (he_IL) (thanks to Sagive SEO)
* Hindi (hi_IN) (thanks to [Outshine Solutions](mailto:ash.pr@outshinesolutions.com), www.outshinesolutions.com)
* Hungarian (hu_HU) (thanks to [Peter Aprily](mailto:solarside09@gmail.com))
* Japanese (ja) (thanks to Foken)
* Indonesian (id_ID) (thanks to [Nasrulhaq Muiz](mailto:nasroel@al-badar.net), www.al-badar.net)
* Italian (it_IT) (thanks to [Marco](mailto:marco@blackstudio.it))
* Latvian (lv) (thanks to [Juris O](mailto:juris.o@gmail.com))
* Lithuanian (lt_LT) (thanks to [Arnas](mailto:arnas.metal@gmail.com))
* Norwegian (nb_NO) (thanks to Tore Hjartland)
* Polish (pl_PL) (thanks to Krzysztof Opuchlik)
* Portuguese (pt_PT) (thanks to [João Paulo Antunes](mailto:jp.jp@sapo.pt))
* Romanian (ro_RO) (thanks to Ciprian)
* Russian (ru_RU)
* Serbian (sr_RS) (thanks to Radovan Georgijevic)
* Slovak (sk_SK) (thanks to Branco Radenovich)
* Slovenian (sl_SI) (thanks to [Uroš Klopčič](mailto:uros.klopcic@gmail.com), www.klopcic.net)
* Spain (es_ES)
* Swedish (sv_SE) (thanks to Christer Rönningborg, [Blittan](mailto:blittan@xbmc.org))
* Tagalog (tl) (thanks to [Roozbeh Jalali](mailto:rjalali@languageconnect.net), www.languageconnect.net)
* Turkish (tr_TR) (thanks to Can Atasever, www.canatasever.com)
* Ukrainian (uk)
* Vietnamese (vi_VN) (thanks to NDT Solutions)

== Installation ==

1. Upload the `captcha` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin via the 'Plugins' menu in WordPress.
3. Plugin settings are located in "Captcha".

== Frequently Asked Questions ==

= How to add Captcha plugin to the Wordpress login page (form)? =

Follow the next steps in order to add Captcha to your Wordpress login page (form):
1. Open your Wordpress admin Dashboard.
2. Navigate to Captcha settings page.
3. Find "Enable Captcha for" for the "Login form".
4. Save changes.

= Any captcha answer results an error =

Captcha will only be displayed if you are using standard registration, login, comments form pages. In case of using custom forms and pages it will be necessary to make changes in them so that captcha could be displayed and work correctly.

= Add Captcha plugin to a custom form on my Wordpress website =

Follow the instructions below in order to add Captcha plugin to your custom PHP or HTML form:
1. Install the Captcha plugin and activate it.
2. Open the file with the form (where you would like to add CAPTCHA);
3. Find a place to insert the code for the CAPTCHA output;
4. Insert the following lines:

`<?php echo apply_filters( 'hctpc_display', '', 'my_contact_form' ); ?>`

In this example, the second parameter is a slug for your custom form. If you don`t use the custom form settings (see point 2 of this instructions) you can leave it empty:

`<?php echo apply_filters( 'hctpc_display', '' ); ?>`

5. After that, you should add the following lines to the function of the entered data checking:


`<?php $error = apply_filters( 'hctpc_verify', true );
if ( true === $error ) { /* the CAPTCHA answer is right */
    /* do necessary action */
} else { /* the CAPTCHA answer is wrong or there are some other errors */
    echo $error; /* display the error message or do other necessary actions in case when the CAPTCHA test was failed */
} ?>`


If there is a variable in the check function responsible for the errors output, you can concatenate variable $error to this variable. If the 'hctpc_verify' filter hook returns 'true', it means that you have entered the CAPTCHA answer properly. In all other cases, the function will return the string with the error message.

= Why is the CAPTCHA missing in the comments form? =

Plugin displays captcha for those comments forms which were written in the same way as comments forms for the standard WordPress themes. Unfortunately, the plugin is incompatible with comments forms generated by using SAAS (eg: Disqus or JetPack comments forms). If you don't use SAAS comments forms, please follow the next steps:
1. Using FTP, please go to {wp_root_folder}/wp-content/themes/{your_theme}.
2. Find and open "comments.php" file. It is possible that the file that is used to display the comment form in your theme called differently or comment form output functionality is inserted directly in the other templates themes (eg "single.php" or "page.php"). In this case, you need to open the corresponding file.
3. Make sure that the file contains one of the next hooks:

`do_action ( 'comment_form_logged_in_after' )
do_action ( 'comment_form_after_fields' )
do_action ( 'comment_form' )`

If you didn't find one of these hooks, then put the string `<?php do_action( 'comment_form', $post->ID ); ?>` in the comment form.

= Can I move the Captcha block in the comment form? =

It depends on the comments form.
If the hook call by means of which captcha works (after_comment_field or something like this) is present in the file "comments.php", you can change captcha positioning by moving this hook call. Please find the file "comments.php" in the theme and change position of the line do_action( 'comment_form_after_fields' ); or any similar line - place it under the Submit button. In case there is no such hook in the comments file of your theme, then, unfortunately, this option is not available.

= Spam bots getting past captcha and creating new user accounts =

Unfortunately, there is no captcha that could provide 100% spam protection. Real people (and spammers) can get through captcha easily. Here is a list of simple recommendations to stop spam on your WordPress website:
- Make sure that the Captcha plugin is installed, activated, updated to the latest version and integrated with your form(-s).
- Enable all captcha protection complexity. Go to the plugin settings page and enable such options as Plus, Minus, Multiplication; enable Numbers, Images, and Words.
- Try to submit the form(-s) with captcha (use the wrong answer to make sure that Captcha is working correctly).

= How do you add captcha to the Contact Form 7? =
Simply add this shortcode [wpcaptcha] to your contact form.

== Screenshots ==

1. Login form with Captcha.
2. Registration form with Captcha.
3. Lost password form with Captcha.
4. Comments form with Captcha.
5. Captcha Settings page.
6. Captcha Packages page.
7. Captcha Whitelist page.
8. Contact Form7 + Captcha step1.
9. Contact Form7 + Captcha step2.
10. Contact Form7 + Captcha step4.
11. Contact Form7 + Captcha output.

== Changelog ==

= V4.3.3 - 30.08.2017 =
* Update : Fix last version issues

= V4.3.2 - 30.08.2017 =
* Update : Captcha + Contact Form7 integration.
* Update : fixed- Bug image captcha last update



= V4.3.1 - 10.07.2017 =
* Update : The plugin settings page has been updated.

== Upgrade Notice ==

= V4.3.1 =
* Appearance improved.