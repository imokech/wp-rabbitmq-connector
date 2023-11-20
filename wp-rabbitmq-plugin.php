<?php

/**
 * Plugin Name:       WordPress RabbitMQ Connector Plugin
 * Plugin URI:         http://mokech.ir/
 * Description:       The WP RabbitMQ Connector WordPress Plugin enables seamless integration between your WordPress website and RabbitMQ messaging services.
 * Version:           1.0.0
 * Author:            Mohammad Keshavarz
 * Author URI:        http://mokech.ir/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-rabbit-mq
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/autoload.php';

use WPRabbitMQPlugin\RabbitMQ\Connector;

class WP_RabbitMQ_Plugin
{
    private static WP_RabbitMQ_Plugin $instance;
    private $rabbitmq_connector;

    private function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        $this->rabbitmq_connector = Connector::get_instance();
        $this->setup_hooks();
    }

    public static function get_instance()
    {
        if (is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    private function init_rabbitmq_connector() {
        $options = get_option('rabbitmq_plugin_options', array());
        $this->rabbitmq_connector = Connector::getInstance($options);
    }

    private function setup_hooks()
    {
        add_action('publish_post', array($this, 'publish_message_on_post_publish'), 10, 2);

        register_activation_hook(__FILE__, array($this, 'consume_messages_on_activation'));

        add_action('init', array($this, 'rabbitmq_hook_init'));
    }

    public function publish_message_on_post_publish(int $ID, $post)
    {
        $message = 'Post Published: ' . $post->post_title;
        $queue_name = 'post_publish_queue';

        $this->rabbitmq_connector->publish_message($message, $queue_name);
    }

    public function consume_messages_on_activation()
    {
        $queue_name = 'post_publish_queue';

        $callback = function ($msg) {
            // Handle the received message
            echo 'Received: ', $msg->body, PHP_EOL;
        };

        $this->rabbitmq_connector->consume_messages($queue_name, $callback);
    }

    public function get_rabbitmq_connector(): Connector
    {
        return $this->rabbitmq_connector;
    }

    public function rabbitmq_hook_init()
    {
        // ... (use other RabbitMQ functions/hooks as needed)
    }

    public function add_admin_menu() {
        add_menu_page(
            'RabbitMQ Options',
            'RabbitMQ',
            'manage_options',
            'rabbitmq-plugin-settings',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>RabbitMQ Options</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('rabbitmq_plugin_options');
                do_settings_sections('rabbitmq_plugin_options');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting(
            'rabbitmq_plugin_options',
            'rabbitmq_plugin_options',
            array($this, 'sanitize_options')
        );

        add_settings_section(
            'rabbitmq_plugin_settings_section',
            'RabbitMQ Settings',
            array($this, 'section_callback'),
            'rabbitmq_plugin_options'
        );

        add_settings_field(
            'rabbitmq_user',
            'RabbitMQ User',
            array($this, 'user_callback'),
            'rabbitmq_plugin_options',
            'rabbitmq_plugin_settings_section'
        );

        add_settings_field(
            'rabbitmq_pass',
            'RabbitMQ Password',
            array($this, 'password_callback'),
            'rabbitmq_plugin_options',
            'rabbitmq_plugin_settings_section'
        );

        add_settings_field(
            'rabbitmq_port',
            'RabbitMQ Port',
            array($this, 'port_callback'),
            'rabbitmq_plugin_options',
            'rabbitmq_plugin_settings_section'
        );

        add_settings_field(
            'rabbitmq_ip',
            'RabbitMQ IP',
            array($this, 'ip_callback'),
            'rabbitmq_plugin_options',
            'rabbitmq_plugin_settings_section'
        );
    }

    public function sanitize_options($options) {
        $sanitized_options = array();

        if (isset($options['rabbitmq_user']))
            $sanitized_options['rabbitmq_user'] = sanitize_text_field($options['rabbitmq_user']);

        if (isset($options['rabbitmq_pass']))
            $sanitized_options['rabbitmq_pass'] = sanitize_text_field($options['rabbitmq_pass']);

        if (isset($options['rabbitmq_ip']))
            $sanitized_options['rabbitmq_ip'] = sanitize_text_field($options['rabbitmq_ip']);

        if (isset($options['rabbitmq_port']))
            $sanitized_options['rabbitmq_port'] = sanitize_text_field($options['rabbitmq_port']);

        return $sanitized_options;
    }

    public function section_callback() {
        // Section callback, if needed
    }

    public function user_callback() {
        $options = get_option('rabbitmq_plugin_options');
        $user = isset($options['rabbitmq_user']) ? esc_attr($options['rabbitmq_user']) : '';
        echo "<input type='text' name='rabbitmq_plugin_options[rabbitmq_user]' value='$user' />";
    }

    public function password_callback() {
        $options = get_option('rabbitmq_plugin_options');
        $pass = isset($options['rabbitmq_pass']) ? esc_attr($options['rabbitmq_pass']) : '';
        echo "<input type='password' name='rabbitmq_plugin_options[rabbitmq_pass]' value='$pass' />";
    }

    public function port_callback() {
        $options = get_option('rabbitmq_plugin_options');
        $port = isset($options['rabbitmq_port']) ? esc_attr($options['rabbitmq_port']) : '';
        echo "<input type='text' name='rabbitmq_plugin_options[rabbitmq_port]' value='$port' />";
    }

    public function ip_callback() {
        $options = get_option('rabbitmq_plugin_options');
        $ip = isset($options['rabbitmq_ip']) ? esc_attr($options['rabbitmq_ip']) : '';
        echo "<input type='text' name='rabbitmq_plugin_options[rabbitmq_ip]' value='$ip' />";
    }
}

WP_RabbitMQ_Plugin::get_instance();