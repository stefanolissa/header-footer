<?php

namespace HeaderFooter;

defined('ABSPATH') || exit;

class Controls {

    var $options;

    public function __construct($options) {
        $this->options = $options;
    }

    function get_value($name, $default = '') {
        if (!isset($this->options[$name])) {
            return $default;
        }
        return $this->options[$name];
    }

    function checkbox($name, $label = '') {
        static $count = 0;
        $count++;
        $value = $this->get_value($name, '0');
        $id = 'checkbox_' . $count;
        echo '<input type="hidden" id="', $id, '" name="options[', esc_attr($name), ']" value="', esc_attr($value), '">';
        echo '<label>';
        echo '<input type="checkbox" ', (empty($value) ? '' : 'checked'), ' onchange="document.getElementById(\'', $id, '\').value=this.checked?1:0"> ';
        echo esc_html($label);
        echo '</label>';
    }

    function text($name) {
        echo '<input type="text" name="options[', esc_attr($name), ']" value="', esc_attr($this->get_value($name)), '">';
    }

    function editor($name) {
        echo '<textarea class="hefo-controls-editor" name="options[' . esc_attr($name) . ']">';
        echo esc_html($this->get_value($name));
        echo '</textarea>';
    }

    function textarea($name) {
        echo '<textarea style="width: 100%; height: 150px" name="options[' . esc_attr($name) . ']">';
        echo esc_html($this->get_value($name));
        echo '</textarea>';
    }

    function rule($number) {
        $options = $this->options;

        if (empty($options['inner_pos_' . $number])) {
            $options['inner_pos_' . $number] = 'after';
        }

        if (empty($options['inner_skip_' . $number])) {
            $options['inner_skip_' . $number] = 0;
        }

        if (empty($options['inner_tag_' . $number])) {
            $options['inner_tag_' . $number] = '';
        }
        if (empty($options['inner_alt_' . $number])) {
            $options['inner_alt_' . $number] = '';
        }

        echo '<div class="rules">';
        echo '<div style="float: left">Inject</div>';
        echo '<select style="float: left" name="options[inner_pos_' . esc_attr($number) . ']">';
        echo '<option value="after"';
        echo $options['inner_pos_' . $number] == 'after' ? ' selected' : '';
        echo '>after</option>';
        echo '<option value="before"';
        echo $options['inner_pos_' . $number] == 'before' ? ' selected' : '';
        echo '>before</option>';
        echo '</select>';
        echo '<input style="float: left" type="text" placeholder="marker" name="options[inner_tag_' . esc_attr($number) . ']" value="';
        echo esc_attr($options['inner_tag_' . $number]);
        echo '">';
        echo '<a href="" style="float: left"><span class="dashicons dashicons-info"></span></a>';
        echo '<div style="float: left">skipping</div>';
        echo '<input style="float: left" type="text" size="5" name="options[inner_skip_' . esc_attr($number) . ']" value="';
        echo esc_attr($options['inner_skip_' . $number]);
        echo '">';
        echo '<div style="float: left">chars, on failure inject</div>';
        echo '<select style="float: left" name="options[inner_alt_' . esc_attr($number) . ']">';
        echo '<option value=""';
        echo $options['inner_alt_' . $number] == 'after' ? ' selected' : '';
        echo '>nowhere</option>';
        echo '<option value="after"';
        echo $options['inner_alt_' . $number] == 'after' ? ' selected' : '';
        echo '>after the content</option>';
        echo '<option value="before"';
        echo $options['inner_alt_' . $number] == 'before' ? ' selected' : '';
        echo '>before the content</option>';
        echo '</select>';
        echo '<div class="clearfix"></div></div>';
    }

}
