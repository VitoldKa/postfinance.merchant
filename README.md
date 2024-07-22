# PostFinance Merchant for WP e-Commerce

PostFinance payment gateway with the WP e-Commerce plugin in WordPress. The integration allows you to process payments through PostFinance seamlessly.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

## Features

- Seamless integration with WP e-Commerce.
- Supports multiple payment statuses including Authorized, Payment Requested, and more.
- Secure payment processing using SHA-1 hashing.
- Configurable from the WordPress admin panel.
- Automatic redirection to PostFinance payment page.

## Installation

1. **Download the repository:**
   ```bash
   git clone https://github.com/yourusername/postfinance-wp-ecommerce.git
   ```

2. **Upload to WordPress:**
   - Upload the downloaded files to your WordPress installation directory, typically within the `wp-content/plugins` directory.

3. **Activate the Plugin:**
   - Log in to your WordPress admin panel.
   - Navigate to `Plugins` and activate the "PostFinance for WP e-Commerce" plugin.

## Configuration

1. **Navigate to Settings:**
   - Go to `Settings` > `Store` > `Payments` in your WordPress admin panel.

2. **Select PostFinance:**
   - Enable the "PostFinance Checkout" payment method.

3. **Configure Gateway Settings:**
   - Enter your `PSPID`, `Gateway URL`, `SHA-IN`, and `SHA-OUT` values.
   - Save the settings.

## Usage

Once configured, the PostFinance payment gateway will be available as a payment option during the checkout process on your WP e-Commerce store. Customers selecting this option will be redirected to the PostFinance payment page to complete their transactions.

### Handling Callbacks

The plugin includes a callback handler to process the transaction results from PostFinance. Ensure that your server is configured to handle incoming POST requests from PostFinance.

## Contributing

Contributions are welcome! If you have suggestions for improvements or have found a bug, please open an issue or submit a pull request.

1. **Fork the repository**
2. **Create a new branch**
   ```bash
   git checkout -b feature-name
   ```
3. **Commit your changes**
   ```bash
   git commit -m "Description of changes"
   ```
4. **Push to the branch**
   ```bash
   git push origin feature-name
   ```
5. **Open a pull request**

## License

This project is licensed under the GNU General Public License v3.0. See the [LICENSE](LICENSE) file for details.

## Author

- **Kapshitzer Vitold** - [GitHub](https://github.com/yourusername)

For any queries, feel free to open an issue or contact the author.
