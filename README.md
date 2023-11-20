# WordPress RabbitMQ Connector

The WP RabbitMQ Connector WordPress Plugin enables seamless integration between your WordPress website and RabbitMQ messaging services.

## Features

- Establishes a connection between WordPress and RabbitMQ.
- Provides functions and hooks to utilize RabbitMQ functionalities in themes or other plugins.
- Options page in the WordPress admin panel to set RabbitMQ credentials.

## Installation

1. Clone or download this repository.
2. Upload the `wp-rabbitmq-connector` directory to the `wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

### Setting RabbitMQ Credentials

1. After activation, navigate to the 'RabbitMQ' section in the WordPress admin panel.
2. Enter your RabbitMQ username, password, Port and IP address.
3. Click 'Save Settings' to store the RabbitMQ credentials.

### Accessing RabbitMQ Functions and Hooks

Use the following code to access the RabbitMQ connector instance and utilize its functionalities:

```php
$rabbitmq_connector = WP_RabbitMQ_Plugin::get_instance()->get_rabbitmq_connector();

// Publish a message
$rabbitmq_connector->publish_message('Hello Buddy!', 'buddy_message_queue');

// Consume messages
$rabbitmq_connector->consume_messages('buddy_message_queue', function ($msg) {
    // Handle received messages
});
```
## Support
For any issues, feature requests, or questions, please submit an issue on GitHub.

## Contributing
Contributions are welcome! Feel free to fork the repository and submit a pull request.