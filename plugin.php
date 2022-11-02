<?php

namespace HeaderFooter;

/*
  Plugin Name: Head, Footer and Post Injections
  Plugin URI: https://www.satollo.net/plugins/header-footer
  Description: Header and Footer lets to add html/javascript code to the head and footer and posts of your blog. Some examples are provided on the <a href="http://www.satollo.net/plugins/header-footer">official page</a>.
  Version: 3.3.0
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: Stefano Lissa
  Author URI: https://www.satollo.net
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

/*
  Copyright 2008-2022 Stefano Lissa (stefano@satollo.net)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

defined('ABSPATH') || exit;

class Plugin {

    const MAX_GENERICS = 5;

    static $instance;
    var $options;
    var $is_mobile = false;
    var $is_amp_endpoint = false;
    var $is_php_enabled = false;
    var $body_block = '';
    var $generic_block = [];

    public function __construct() {
        self::$instance = $this;

        $this->options = get_option('hefo', []);

        if (!is_admin()) {
            add_action('wp', [$this, 'hook_wp']);
        }
    }

    function hook_wp() {
        if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($this->options['mobile_user_agents_parsed'])) {
            $this->is_mobile = preg_match('/' . $this->options['mobile_user_agents_parsed'] . '/', strtolower($_SERVER['HTTP_USER_AGENT']));
        }

        $this->is_php_enabled = apply_filters('hefo_php_exec', !empty($this->options['enable_php']));

        $this->is_amp_endpoint = function_exists('is_amp_endpoint') && is_amp_endpoint();

        if ($this->is_amp_endpoint) {
            $this->init_amp();
            add_action('the_content', [$this, 'hook_the_content_amp']);
        } else {
            add_action('wp_head', [$this, 'hook_wp_head_pre'], 1);
            add_action('wp_head', [$this, 'hook_wp_head_post'], 11);

            if (!empty($this->options['disable_css_id']) || !empty($this->options['disable_css_media'])) {
                add_filter('style_loader_tag', [$this, 'hook_style_loader_tag']);
            }
            add_action('wp_footer', [$this, 'hook_wp_footer']);
            add_action('template_redirect', [$this, 'hook_template_redirect'], 1);
            add_action('the_excerpt', [$this, 'hook_the_excerpt']);
            add_action('the_content', [$this, 'hook_the_content']);
        }
    }

    function hook_style_loader_tag($link) {
        if (!empty($this->options['disable_css_id'])) {
            $link = preg_replace("/id='.*?-css'/", "", $link);
        }

        if (!empty($this->options['disable_css_media'])) {
            if (!preg_match("/media='print'/", $link)) {
                $link = preg_replace("/media='.*?'/", "", $link);
            }
        }
        return $link;
    }

    function hook_wp_head_pre() {

        if (!empty($this->options['disable_wlwmanifest_link'])) {
            remove_action('wp_head', 'wlwmanifest_link');
        }

        if (!empty($this->options['disable_rsd_link'])) {
            remove_action('wp_head', 'rsd_link');
        }

        if (!empty($this->options['disable_feed_links_extra'])) {
            remove_action('wp_head', 'feed_links_extra', 3);
        }

        if (!empty($this->options['disable_wp_shortlink_wp_head'])) {
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        }

        if (!empty($this->options['disable_wp_shortlink_wp_head'])) {
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        }
    }

    function hook_wp_head_post() {
        if (is_front_page()) {
            $this->execute_option('head_home', true);
        }

        $this->execute_option('head', true);
    }

    function hook_wp_footer() {
        if ($this->is_mobile && !empty($this->options['mobile_footer_enabled'])) {
            hefo_execute_option('mobile_footer', true);
        } else {
            hefo_execute_option('footer', true);
        }
    }

    function hook_the_content($content) {
        global $hefo_options, $wpdb, $post;

        $before = '';
        $after = '';

        if (!is_singular()) {
            return $content;
        }

        $type = '';

        if (is_page() && empty($this->options['page_use_post'])) {
            $type = 'page_';
        }

        //if (!get_post_meta($post->ID, 'hefo_before', true)) {
        if ($this->is_mobile && !empty($this->options['mobile_' . $type . 'before_enabled'])) {
            $before = $this->execute_option('mobile_' . $type . 'before');
        } else {
            $before = $this->execute_option($type . 'before');
        }
        //}
        //if (!get_post_meta($post->ID, 'hefo_after', true)) {
        if ($this->is_mobile && isset($this->options['mobile_' . $type . 'after_enabled'])) {
            $after = $this->execute_option('mobile_' . $type . 'after');
        } else {
            $after = $this->execute_option($type . 'after');
        }
        //}
        // Rules

        for ($i = 1; $i <= 5; $i++) {
            if (empty($this->options['inner_tag_' . $i])) {
                continue;
            }
            $prefix = '';
            if ($this->is_mobile && !empty($this->options['mobile_inner_enabled_' . $i])) {
                $prefix = 'mobile_';
            }
            $skip = trim($this->options['inner_skip_' . $i]);
            if (empty($skip)) {
                $skip = 0;
            } else if (substr($skip, -1) == '%') {
                $skip = (intval($skip) * strlen($content) / 100);
            }

            if ($this->options['inner_pos_' . $i] == 'after') {
                $res = $this->insert_after($content, $this->execute_option($prefix . 'inner_' . $i), $this->options['inner_tag_' . $i], $skip);
            } else {
                $res = $this->insert_before($content, $this->execute_option($prefix . 'inner_' . $i), $this->options['inner_tag_' . $i], $skip);
            }
            if (!$res) {
                switch ($this->options['inner_alt_' . $i]) {
                    case 'after':
                        $content = $content . $this->execute_option($prefix . 'inner_' . $i);
                        break;
                    case 'before':
                        $content = $this->execute_option($prefix . 'inner_' . $i) . $content;
                }
            }
        }

        return $before . $content . $after;
    }

    function init_amp() {
        add_action('amp_post_template_head', function () {
            echo $this->execute_option('amp_head', true);
        }, 100);

        add_action('amp_post_template_css', function () {
            $this->execute_option('amp_css', true);
        }, 100);

        add_action('amp_post_template_body_open', function () {
            $this->execute_option('amp_body', true);
        }, 100);

        add_action('amp_post_template_footer', function () {
            $this->execute_option('amp_footer', true);
        }, 100);
    }

    function hook_the_content_amp($content) {
        $before = '';
        $after = '';

        $before = $this->execute_option('amp_post_before');
        $after = $this->execute_option('amp_post_after');
        return $before . $content . $after;
    }

    function execute($buffer) {
        global $wpdb, $post;

        if ($this->is_php_enabled) {
            ob_start();
            eval('?>' . $buffer);
            $buffer = ob_get_clean();
        }
        return trim($buffer);
    }

    function execute_option($key, $echo = false) {
        global $wpdb, $post;
        if (empty($this->options[$key])) {
            return '';
        }

        $buffer = $this->replace($this->options[$key]);
        if ($echo) {
            echo $this->execute($buffer);
        } else {
            return $this->execute($buffer);
        }
    }

    function hook_template_redirect() {

        if ($this->is_mobile && !empty($this->options['mobile_body_enabled'])) {
            $this->body_block = $this->execute_option('mobile_body');
        } else {
            $this->body_block = $this->execute_option('body');
        }

        $empty = empty($this->body_block);

        for ($i = 1; $i <= self::MAX_GENERICS; $i++) {
            if ($this->is_mobile && !empty($this->options['mobile_generic_enabled_' . $i])) {
                $this->generic_block[$i] = $this->execute_option('mobile_generic_' . $i);
            } else {
                $this->generic_block[$i] = $this->execute_option('generic_' . $i);
            }
            $empty = $empty && empty($this->generic_block[$i]);
        }

        if (!$empty) {
            ob_start([$this, 'template_redirect_callback']);
        }
    }

    function template_redirect_callback($buffer) {

        for ($i = 1; $i <= self::MAX_GENERICS; $i++) {
            if (!empty($this->options['generic_tag_' . $i])) {
                $this->insert_before($buffer, $this->generic_block[$i], $this->options['generic_tag_' . $i]);
            }
        }
        $x = strpos($buffer, '<body');
        if ($x === false) {
            return $buffer;
        }
        $x = strpos($buffer, '>', $x);
        if ($x === false) {
            return $buffer;
        }
        $x++;
        return substr($buffer, 0, $x) . "\n" . $this->body_block . substr($buffer, $x);
    }

    function insert_before(&$content, $what, $marker, $starting_from = 0) {
        if (strlen($content) < $starting_from) {
            return false;
        }

        if (empty($marker)) {
            $marker = ' ';
        }
        $x = strpos($content, $marker, $starting_from);
        if ($x !== false) {
            $content = substr_replace($content, $what, $x, 0);
            return true;
        }
        return false;
    }

    function insert_after(&$content, $what, $marker, $starting_from = 0) {

        if (strlen($content) < $starting_from) {
            return false;
        }

        if (empty($marker)) {
            $marker = ' ';
        }

        $x = strpos($content, $marker, $starting_from);

        if ($x !== false) {
            $content = substr_replace($content, $what, $x + strlen($marker), 0);
            return true;
        }
        return false;
    }

    function hook_the_excerpt($content) {
        global $post, $wpdb, $hefo_count;
        $hefo_count++;
        if (is_category() || is_tag() || is_home()) {
            $before = $this->execute_option('excerpt_before');
            $after = $this->execute_option('excerpt_after');

            return $before . $content . $after;
        } else {
            return $content;
        }
    }

    function replace($buffer) {
        for ($i = 1; $i <= 5; $i++) {
            if (empty($this->options['snippet_' . $i]))
                continue;
            $buffer = str_replace('[snippet_' . $i . ']', $this->options['snippet_' . $i], $buffer);
        }

        return $buffer;
    }

}

new Plugin();

if (is_admin()) {
    require_once dirname(__FILE__) . '/admin/admin.php';
}

register_activation_hook(__FILE__, function () {
   
});

register_deactivation_hook(__FILE__, function () {
    $options = get_option('hefo');
    if ($options) {
        $options['updated'] = time();
        update_option('hefo', $options, false);
    }
});

