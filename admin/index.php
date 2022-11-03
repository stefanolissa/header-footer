<?php

namespace HeaderFooter;

defined('ABSPATH') || exit;

global $options;

// Quick security patch, to be better integrated
if (!current_user_can('administrator')) {
    die();
}

if (isset($_POST['save'])) {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'save')) {
        die('Page expired');
    }
    
    $options = stripslashes_deep($_REQUEST['options']);

    // Another thing to be improved...
    if (!isset($options['enable_php'])) {
        $options['enable_php'] = '0';
    }

    $options['mobile_user_agents'] = trim($options['mobile_user_agents']);
    
    if (empty($options['mobile_user_agents'])) {
        $options['mobile_user_agents'] = "phone\niphone\nipod\nandroid.+mobile\nxoom";
    }
    $agents1 = explode("\n", $options['mobile_user_agents']);
    $agents2 = [];
    foreach ($agents1 as $agent) {
        $agent = trim($agent);
        if (empty($agent)) {
            continue;
        }
        $agents2[] = strtolower($agent);
    }
    $options['mobile_user_agents_parsed'] = implode('|', $agents2);

    update_option('hefo', $options);
} else {
    $options = get_option('hefo');
}

$controls = new Controls($options);
?>

<script>
    jQuery(function () {

        jQuery("textarea.hefo-controls-editor").each(function () {
            wp.codeEditor.initialize(this);
        });
        jQuery("#hefo-tabs").tabs();
    });
</script>

<div class="wrap">

    <h2>Head, Footer and Post Injections</h2>

    <div class="hefo-notices">
        <div>
            <h4>Are you saving you time and money?</h4>
            <p>Take into consideration to <strong>help children</strong> with a small donation!</p>
            <p><a href="https://www.satollo.net/donations" target="_blank">Read how I'm using what you give.</a> A big thank you!</p>
            <p style="text-align: center"><a class="hefo-donate-button" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5PHGDGNHAYLJ8" target="_blank">HELP CHILDREN HERE</a></p>
        </div>

        <div>
            <h4>Would you kindly rate this plugin?</h4>
            <p>
                It helps me keeping on. </p>
            <p>
                And if you have an issue, please <a href="https://wordpress.org/support/plugin/header-footer/" target="_blank">share it on support forum</a>.
            </p>


            <p style="text-align: center"><a class="hefo-rate-button" href="https://wordpress.org/support/plugin/header-footer/reviews/#new-post" target="_blank">SHARE YOUR STARS HERE</a></p>
        </div>

        <div>
            <h4>Updates...</h4>
            <p>
                I rarely send a newsletter about my plugins or WordPress topics.
            </p>
            <form action="https://www.satollo.net/?na=s" target="_blank" method="post">
                <input type="hidden" value="header-footer" name="nr">
                <input type="hidden" value="2" name="nl[]">
                <p><input type="email" name="ne" value="<?php echo esc_attr(get_option('admin_email')) ?>" placeholder="Your email address" required></p>

                <p><input type="submit" value="I'm fine to get it" class="hefo-newsletter-button"></p>
            </form>
        </div>
    </div>


    <form method="post" action="">
        <?php wp_nonce_field('save') ?>

        <p>
            <input type="submit" class="button-primary" name="save" value="<?php esc_attr_e('Save', 'header-footer'); ?>">
        </p>

        <div id="hefo-tabs">
            <ul>
                <li class="active"><a href="#tabs-first"><?php esc_html_e('Head and footer', 'header-footer'); ?></a></li>
                <li><a href="#tabs-post"><?php esc_html_e('Posts', 'header-footer'); ?></a></li>
                <li><a href="#tabs-post-inner"><?php esc_html_e('Inside posts', 'header-footer'); ?></a></li>
                <li><a href="#tabs-page"><?php esc_html_e('Pages', 'header-footer'); ?></a></li>
                <li><a href="#tabs-excerpt"><?php esc_html_e('Excerpts', 'header-footer'); ?></a></li>
                <li><a href="#tabs-amp"><?php esc_html_e('AMP version', 'header-footer'); ?></a></li>
                <li><a href="#tabs-snippets"><?php esc_html_e('Snippets', 'header-footer'); ?></a></li>
                <li><a href="#tabs-generics"><?php esc_html_e('Generics', 'header-footer'); ?></a></li>
                <li><a href="#tabs-8"><?php esc_html_e('Advanced', 'header-footer'); ?></a></li>
                <li><a href="#tabs-7"><?php esc_html_e('Notes and...', 'header-footer'); ?></a></li>
                <li><a href="#tabs-mobile"><?php esc_html_e('Mobile', 'header-footer'); ?></a></li>
            </ul>


            <div id="tabs-first">

                <h3><?php esc_html_e('<HEAD> page section injection', 'header-footer') ?></h3>
                <div class="row">

                    <div class="col-2">
                        <label><?php esc_html_e('On every page', 'header-footer') ?></label>
                        <?php $controls->editor('head'); ?>
                    </div>
                    <div class="col-2">
                        <label><?php esc_html_e('Only on the home page', 'header-footer') ?></label>
                        <?php $controls->editor('head_home'); ?>
                    </div>
                </div>

                <h3><?php esc_html_e('After the <BODY> tag', 'header-footer') ?></h3>

                <?php $controls->editor('body'); ?>

                <h3><?php esc_html_e('Before the </BODY> closing tag (footer)', 'header-footer') ?></h3>

                <?php $controls->editor('footer'); ?>

            </div>

            <div id="tabs-post">

                <h3><?php esc_html_e('Before the post content', 'header-footer'); ?></h3>

                <?php $controls->editor('before'); ?>

                <h3><?php esc_html_e('After the post content', 'header-footer'); ?></h3>

                <?php $controls->editor('after'); ?>

            </div>

            <div id="tabs-generics">
                <p>
                    Recent themes provide lot of widget areas making this injection almost useless. Probably they'll be removed in a
                    future version. Let me know if you uset them!
                </p>

                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <h3>Generic injection <?php echo $i; ?></h3>
                    <p>Inject before the <?php $controls->text('generic_tag_' . $i); ?> marker</p>
                    <div class="row">
                        <div class="col-2">
                            <label><?php esc_html_e('Desktop', 'header-footer') ?>*</label>
                            <?php $controls->editor('generic_' . $i); ?>
                        </div>
                        <div class="col-2">
                            <?php $controls->checkbox('mobile_generic_enabled_' . $i, __('Mobile', 'header-footer')); ?>
                            <?php $controls->editor('mobile_generic_' . $i); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                <?php } ?>
                <div class="clearfix"></div>
            </div>


            <div id="tabs-post-inner">

                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <h3>Inner post injection <?php echo $i; ?></h3>
                    <?php $controls->rule($i); ?>
                    <div class="row">
                        <div class="col-2">
                            <label><?php esc_html_e('Desktop', 'header-footer') ?>*</label>
                            <?php $controls->editor('inner_' . $i); ?>
                        </div>
                        <div class="col-2">
                            <?php $controls->checkbox('mobile_inner_enabled_' . $i, __('Mobile', 'header-footer')); ?> (deprecated)
                            <?php $controls->editor('mobile_inner_' . $i); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                <?php } ?>
            </div>


            <div id="tabs-page">

                <?php $controls->checkbox('page_use_post', __('Use the post configurations', 'header-footer')); ?><br>
                <?php $controls->checkbox('page_add_tags', __('Let pages to have tags', 'header-footer')); ?><br>
                <?php $controls->checkbox('page_add_categories', __('Let pages to have categories', 'header-footer')); ?>

                <h3><?php esc_html_e('Before the page content', 'header-footer') ?></h3>
                <?php $controls->editor('page_before'); ?>

                <h3><?php _e('After the page content', 'header-footer') ?></h3>
                <?php $controls->editor('page_after'); ?>

            </div>


            <div id="tabs-excerpt">

                <p><?php esc_html_e('It works only on home and category and tag pages.', 'header-footer'); ?></p>

                <h3><?php esc_html_e('Code to be inserted before each post excerpt', 'header-footer') ?></h3>
                <?php $controls->editor('excerpt_before') ?>

                <h3><?php esc_html_e('Code to be inserted after each post excerpt', 'header-footer') ?></h3>
                <?php $controls->editor('excerpt_after') ?>

            </div>


            <div id="tabs-amp">
                <p>
                    You need the <a href="https://it.wordpress.org/plugins/amp/" target="_blank">AMP</a> plugin. Other AMP plugins could be supported
                    in the near future.
                </p>

                <h3><?php esc_html_e('<HEAD> page section', 'header-footer') ?></h3>
                <?php $controls->editor('amp_head'); ?>

                <h3><?php esc_html_e('Extra CSS', 'header-footer') ?></h3>
                <?php $controls->editor('amp_css'); ?>

                <h3><?php esc_html_e('Just after the <BODY> tag', 'header-footer') ?></h3>
                <?php $controls->editor('amp_body'); ?>

                <h3><?php esc_html_e('Before the post content', 'header-footer') ?></h3>
                <?php $controls->editor('amp_post_before'); ?>

                <h3><?php esc_html_e('After the post content', 'header-footer') ?></h3>
                <?php $controls->editor('amp_post_after'); ?>

                <h3><?php esc_html_e('Footer', 'header-footer') ?></h3>
                <?php $controls->editor('amp_footer'); ?>
            </div>


            <div id="tabs-snippets">
                <p>
                    <?php esc_html_e('Common snippets that can be used in any header or footer area referring them as [snippet_N] where N is the snippet number
            from 1 to 5. Snippets are inserted before PHP evaluation.', 'header-footer'); ?><br />
                    <?php esc_html_e('Useful for social button to be placed before and after the post or in posts and pages.', 'header-footer'); ?>
                </p>

                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <h3><?php _e('Snippet', 'header-footer') ?> <?php echo $i ?></h3>
                    <?php $controls->editor('snippet_' . $i) ?>
                <?php } ?>

            </div>

            <div id="tabs-8">
                <h3>PHP</h3>
                <p>
                    <?php $controls->checkbox('enable_php', __('Enable PHP execution', 'header-footer'), ''); ?>
                </p>

                <h3><?php esc_html_e('<HEAD> meta element', 'header-footer') ?></h3>
                <p>
                    WordPress automatically add some meta link on the head of the page, for example the RSS links, the previous and next
                    post links and so on. Here you can disable those links if not of interest. SEO plugins already do that.
                </p>
                <p>
                    <?php $controls->checkbox('disable_css_id', __('Disable the id attribute on css links generated by WordPress', 'header-footer'), '', 'http://www.satollo.net/plugins/header-footer#disable_css_id'); ?>
                    <br>
                    <?php $controls->checkbox('disable_css_media', __('Disable the media attribute on css links generated by WordPress, id the option above is enabled.', 'header-footer'), '', 'http://www.satollo.net/plugins/header-footer#disable_css_media'); ?>
                    <br>                
                    <?php $controls->checkbox('disable_feed_links_extra', __('Disable extra feed links like category feeds or single post comments feeds', 'header-footer')); ?>
                    <br>                    
                    <?php $controls->checkbox('disable_wp_shortlink_wp_head', __('Disable the short link for posts', 'header-footer')); ?>
                    <br>                    
                    <?php $controls->checkbox('disable_wlwmanifest_link', __('Disable the Windows Live Writer manifest', 'header-footer')); ?>
                    <br>                    
                    <?php $controls->checkbox('disable_rsd_link', __('Disable RSD link', 'header-footer')); ?>
                    <br>                    
                    <?php $controls->checkbox('disable_adjacent_posts_rel_link_wp_head', __('Disable adjacent post links', 'header-footer')); ?>
                </p>
            </div>


            <div id="tabs-7">
                <table class="form-table">
                    <tr valign="top"><?php $controls->editor('notes', __('Notes and parked codes', 'header-footer'), ''); ?></tr>
                </table>
                <div class="clearfix"></div>
            </div>

            <div id="tabs-mobile">
                <h3><?php _e('Injections for mobile devices detected server-side', 'header-footer') ?></h3>
                <p style="font-weight: bold">
                    <?php _e('Detecting mobile devices at "server side" is no more reliable and most of the third-party code (or example ads) are already mobile aware. You should consider to not use anymore those settings.', 'header-footer') ?>
                </p>
                <p>
                    <?php _e('When a mobile device injection is enabled it replaces the standard one.', 'header-footer') ?>
                </p>

                <h3><?php esc_html_e('Mobile user agent strings', 'header-footer') ?></h3>
                <?php $controls->textarea('mobile_user_agents') ?>
                <p class="description">
                    For coders: a regular expression is built with values above and the resulting code will be somrthing like
                    <code>preg_match('/<?php echo esc_html($controls->options['mobile_user_agents_parsed']) ?>/', ...);</code>.
                    <a href="https://www.satollo.net/plugins/header-footer" target="_blank">Read this page</a> for more.
                </p>

                <div class="row">
                    <div>
                        <h3><?php esc_html_e('After the <BODY> tag', 'header-footer') ?></h3>

                        <?php $controls->checkbox('mobile_body_enabled', __('Enable', 'header-footer')); ?>
                        <?php $controls->editor('mobile_body'); ?>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Before the &lt;/BODY&gt; closing tag (footer)', 'header-footer') ?></h3>

                        <?php $controls->checkbox('mobile_footer_enabled', __('Enable', 'header-footer')); ?>
                        <?php $controls->editor('mobile_footer'); ?>
                    </div>
                </div>

                <div class="row">
                    <div>
                        <h3><?php esc_html_e('Before the post content', 'header-footer'); ?></h3>

                        <?php $controls->checkbox('mobile_before_enabled', __('Enable', 'header-footer')); ?>
                        <?php $controls->editor('mobile_before'); ?>
                    </div>
                    <div>
                        <h3><?php esc_html_e('After the post content', 'header-footer'); ?></h3>

                        <?php $controls->checkbox('mobile_after_enabled', __('Enable', 'header-footer')); ?>
                        <?php $controls->editor('mobile_after'); ?>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <h3><?php esc_html_e('Before the page content', 'header-footer') ?></h3>
                        <?php $controls->checkbox('mobile_page_before_enabled', __('Enable', 'header-footer')); ?><br>
                        <?php $controls->editor('mobile_page_before'); ?>
                    </div>
                    <div>
                        <h3><?php _e('After the page content', 'header-footer') ?></h3>

                        <?php $controls->checkbox('mobile_page_after_enabled', __('Enable', 'header-footer')); ?><br>
                        <?php $controls->editor('mobile_page_after'); ?>
                    </div>
                </div>
            </div>
        </div>

        <p class="submit"><input type="submit" class="button-primary" name="save" value="<?php esc_attr_e('save', 'header-footer'); ?>"></p>

    </form>
</div>


