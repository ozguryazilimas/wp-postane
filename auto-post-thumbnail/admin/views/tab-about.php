<style>
	.apt-section-wrapper
	{
		width: 100%;
		margin-top: 10px;
		padding-right: 20px;
		box-sizing: border-box;
	}

	.apt-section
	{
		padding: 29px 29px 0px 29px;
	}

	.apt-section .container
	{
		display: block;
		margin-right: auto;
		margin-left: auto;
		position: relative;
		max-width: 1140px;
		min-height: 400px;
	}

	.apt-section-intro
	{
		width: 1280px;
		height: 414px;
		box-shadow: 0px 0px 24px rgba(107, 107, 107, 0.5);
		text-align: center;
		margin: 0 auto;
		padding: 0;
	}

	.apt-section-intro img
	{
		width: 100%;
		height: auto;
	}

	.apt-section-intro .container h2
	{
		font-size: 61px;
		font-weight: 500;
		text-transform: uppercase;
		line-height: 1.1em;
		color: #fff;
		text-align: center;
	}

	.apt-section-intro .container p
	{
		margin-bottom: 1.6em;
		color: #fffcfc;
		font-family: "Arial", Sans-serif;
		font-size: 22px;
		line-height: 1.3em;
		letter-spacing: 1.1px;
	}

	.apt-section-video p
	{
		font-size: 16px;
		text-align: center;
		padding: 30px;
	}

	.apt-section-video iframe
	{
		margin: 0 auto;
		display: block;
	}

	.apt-section-changelog h4
	{
		font-size: 1.3333333333333rem;
	}

	.apt-section-changelog p,
	.apt-section-changelog ul > li
	{
		font-size: 15px;
	}

	.apt-section-changelog ul
	{
		list-style: inherit;
		margin-left: 40px;
	}

	#wpfooter
	{
		position: relative !important;
	}

	.heading-title
	{
		text-align: center;
	}

	.heading-container
	{
		text-align: center;
		margin-top: 20px;
	}

	.heading-container > p > a
	{
		font-size: 2em;
	}

	h1.heading-title
	{
		font-size: 1.8em;
		line-height: 1.2em;

	}

	h2.image-box-title
	{
		font-size: 2em;
		line-height: 1.2em;
		margin: 0;
	}

	p.image-box-description
	{
		font-size: 1.15em;
		line-height: 1.2em;
		margin: 5px;
		margin-bottom: 20px;
		font-weight: bold;
	}

	div.text-editor > p
	{
		font-size: 1.2em;
		line-height: 1.5em;
		margin: 10px;
	}

	div.image > img
	{
		box-sizing: border-box;
		/*border: 1px solid black;*/
		box-shadow: 0px 0px 24px rgba(107, 107, 107, 0.5);
		text-align: center;
		display: block;
		margin: auto;
		margin-bottom: 30px;
	}

	hr
	{
		border: 1px solid black;
	}

	@media screen and (max-width: 1500px)
	{
		.apt-section .container
		{
			min-height: 300px;
		}

		.apt-section-intro
		{
			box-sizing: border-box;
			width: 100%;
			min-height: auto;
			height: calc(100% - 10px);
		}

		.apt-section-video p
		{
			padding: 10px;
		}

		.apt-section-video iframe
		{
			width: 100%;
		}
	}
</style>

<div class="apt-section">
	<div class="row">
		<div class="widget-container">
			<h1 class="heading-title size-default">
				We suppose you’ve noticed the changes which happened with Auto Featured Image.
				<br>Reading this tutorial you can get more information about new features.</h1>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<img width="70" height="70" class="attachment-thumbnail size-thumbnail" alt=""
			     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/icon-6.png' ); ?>">
			<h2 class="image-box-title">New image generation tools</h2>
			<p class="image-box-description">You can generate featured images by single click as you did in the past.
				But comparing with last release&nbsp; here appeared some new additional tools, so you can:</p>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix">
				<p>Bulk generate or delete&nbsp;featured images.</p>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="1024"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/interface-1.png' ); ?>"
				     class="attachment-full size-full" alt="">
			</div>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix">
				<p>Selective generation and deletion of featured images using filters
					<a href="https://cm-wp.com/apt/pricing/?utm_source=wordpress.org&amp;utm_content=license_page">(PRO)</a>.
				</p>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="1024" height="320"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/Sequence-01.gif' ); ?>"
				     class="attachment-large size-large" alt="">
			</div>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix"><p>Disable automatic post thumbnail generation.</p></div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="500"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/interface-2.png' ); ?>"
				     class="attachment-large size-large" alt="">
			</div>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix">
				<p>If you do not want the plug-in settings to be saved, after
					uninstalling, click “delete settings”
				</p>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="500" height="145"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/interface-3.png' ); ?>"
				     class="attachment-large size-large" alt="">
			</div>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<div class="image-box-wrapper">
				<img width="70" height="70"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/icon-2.png' ); ?>"
				     class="attachment-thumbnail size-thumbnail" alt="">
				<div class="image-box-content"><h2 class="image-box-title">Bulk featured Images generation or unset in
						post list</h2>
					<p class="image-box-description">Use bulk actions when you set featured images. For example Generate
						or Unset all of them.</p></div>
			</div>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix">
				<p>Also we changed the Posts list interface in dashboard and added a
					Column for displaying featured images. If any post stays without any featured image you’ll see this
					from the posts list. Opening post is unnecessary, that’s why It makes the process easier.</p>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="1024"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/Sequence-02.gif' ); ?>"
				     class="attachment-full size-full" alt="">
			</div>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<div class="image-box-wrapper">
				<img width="70" height="70"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/icon-3.png' ); ?>"
				     class="attachment-thumbnail size-thumbnail" alt="">
				<div class="image-box-content"><h2 class="image-box-title">Featured Images selective generation </h2>
					<p class="image-box-description">Execute custom generation or unset featured images applying this
						tool for single or some posts.</p></div>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="500"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/Sequence-03.png' ); ?>"
				     class="attachment-large size-large" alt="">
			</div>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<div class="image-box-wrapper">
				<a href="http://cm-wp.com/apt/pricing/">
					<img width="70" height="70"
					     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/icon-4.png' ); ?>"
					     class="attachment-thumbnail size-thumbnail" alt="">
				</a>
				<div class="image-box-content"><h2 class="image-box-title">Manual Featured Images Selection</h2>
					<p class="image-box-description">Select featured images from the post images when you are in
						dashboard’s Posts list. This feature is available for users who have PRO account.</p></div>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="1024"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/Sequence-04.gif' ); ?>"
				     class="attachment-full size-full" alt=""></div>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix">
				<p>You can add featured images even if the picture is not uploaded to the
					medialibrary but inserted into the post using an external link or shortcode.</p>
			</div>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<div class="image-box-wrapper">
				<img width="70" height="70"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/icon-1.png' ); ?>"
				     class="attachment-thumbnail size-thumbnail" alt="">
				<div class="image-box-content">
					<h2 class="image-box-title">Image search in Google, Unsplash, Pixabay</h2>
					<p class="image-box-description">Search for images with Creative Commons license.</p></div>
			</div>
		</div>
		<div class="widget-container">
			<div class="text-editor clearfix">
				<p>
					<span style="font-weight: 400;">It appeared new tab in the Media Library =&gt; </span>
					<i><span style="font-weight: 400;">Add from APT. </span></i>
					<span style="font-weight: 400;">There you can find images using popular free stock sites:&nbsp; Google, Pixabay
						<a href="https://cm-wp.com/apt/pricing/?utm_source=wordpress.org&amp;utm_content=license_page">(pro)</a>,
						Unsplash <a
								href="https://cm-wp.com/apt/pricing/?utm_source=wordpress.org&amp;utm_content=license_page">(pro)</a>.
						Just enter a search query, choose the image and insert it into the Media library by single click.
					</span>
				</p>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="1024"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/Sequence-05.gif' ); ?>"
				     class="attachment-large size-large" alt="">
			</div>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<div class="image-box-wrapper">
				<img width="70" height="70"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/icon-5.png' ); ?>"
				     class="attachment-thumbnail size-thumbnail" alt="">
				<div class="image-box-content">
					<h2 class="image-box-title">Compatibility with Elementor and Gutenberg</h2>
					<p class="image-box-description">Auto Featured Image is compatible with Classic Editor, Gutenberg
						and Elementor plugins.</p>
				</div>
			</div>
		</div>
		<div class="widget-container">
			<div class="image">
				<img width="1024"
				     src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/about/Sequence-06.gif' ); ?>"
				     class="attachment-large size-large" alt="">
			</div>
		</div>

		<div class="widget-container">
			<div class="divider">
				<hr>
			</div>
		</div>

		<div class="heading-container">
			<p>
				<a href="https://cm-wp.com/apt/apt-f-a-q/?utm_medium=right_banner&amp;utm_campaign=apt&amp;utm_content=link"
				   target="_blank" rel="noopener">F.A.Q.</a>
			</p>
			<p>
				<a href="https://forum.webcraftic.com/" target="_blank" rel="noopener">Get starting free support</a>
			</p>
		</div>
	</div>
</div>
