# MCB Juice QR Payment Gateway Pro - Junior Developer Guide

Welcome to the MCB Juice QR Payment Gateway Pro project! This guide is designed to help new developers quickly understand the plugin's structure, development practices, and how to contribute effectively.

## 1. Project Structure

The plugin follows a standard WordPress plugin structure. Here's a breakdown of the main directories and files:

*   `mcbjuicepay.php`: The main plugin file. It contains the plugin header, basic security checks, and initializes the core functionalities.
*   `readme.txt`: The plugin's readme file, used for WordPress.org plugin repository.
*   `README.md`: A more detailed README for GitHub, providing an overview of the plugin, features, and installation steps.
*   `uninstall.php`: This file is executed when the plugin is uninstalled, ensuring all plugin-related data is properly removed from the database.
*   `assets/`: Contains static assets like images, CSS, and JavaScript.
    *   `assets/screenshot-1.png`, `screenshot-2.png`, `screenshot-3.png`: Screenshots for the plugin.
    *   `assets/css/`: (Currently empty) For custom CSS files.
    *   `assets/icon/icon.svg`: Plugin icon.
    *   `assets/js/admin-scripts.js`: JavaScript for admin-side functionalities, such as media uploader for QR codes and bank logos.
*   `includes/`: Contains the core PHP logic, broken down into functional areas.
    *   `includes/admin-functions.php`: Functions related to the WordPress admin area, like enqueuing scripts and adding settings links.
    *   `includes/front-functions.php`: Functions for front-end display, such as displaying the dynamic QR code on the thank you page.
    *   `includes/woocommerce-integration.php`: The main WooCommerce integration class, handling payment gateway registration, settings, and payment processing.
*   `languages/`: Contains translation files (`.po`, `.mo`, `.pot`).
*   `tests/`: Contains PHPUnit test files for automated testing.
    *   `tests/test-gateway-initialization.php`: A basic test to verify the payment gateway is registered correctly.

## 2. Key Components

*   **`mcb_juice_qr_init_gateway()`**: This function in `mcbjuicepay.php` is the entry point for initializing the payment gateway. It checks for WooCommerce, includes other necessary files, and registers the `MCB_Juice_QR_Payment_Gateway_Premium` class.
*   **`MCB_Juice_QR_Payment_Gateway_Premium` Class**: Defined in `includes/woocommerce-integration.php`, this is the heart of the payment gateway. It extends `WC_Payment_Gateway` and handles:
    *   `__construct()`: Initializes gateway properties, settings, and hooks.
    *   `init_form_fields()`: Defines the settings fields for the gateway in the WooCommerce admin.
    *   `process_admin_options()`: Handles saving and validating the admin settings, including API URL and Key.
    *   `process_payment()`: Manages the payment process, setting order status and scheduling API verification.
    *   `payment_fields()`: Displays payment information on the checkout page.
*   **`mcb_juice_qr_display_dynamic_qr_code()`**: Located in `includes/front-functions.php`, this function is responsible for generating and displaying the dynamic QR code on the order received page.

## 3. Coding Standards

We adhere to the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) and [WooCommerce Coding Standards](https://developer.woocommerce.com/2015/03/04/woocommerce-coding-standards/). Key aspects include:

*   **Indentation**: Use tabs, not spaces.
*   **Naming Conventions**:
    *   Functions and variables: `snake_case` (e.g., `my_function_name`).
    *   Classes: `CamelCase` (e.g., `MyClassName`).
    *   Constants: `UPPER_CASE_SNAKE_CASE` (e.g., `MY_CONSTANT`).
*   **Documentation**: Use Javadoc-style comments for functions, classes, and file headers.
*   **Security**: Always sanitize input (`sanitize_text_field()`, `esc_url_raw()`, etc.) and escape output (`esc_html()`, `esc_attr()`, `wp_kses_post()`, etc.) to prevent security vulnerabilities. Use nonces for actions that modify data.
*   **Internationalization**: All user-facing strings must be translatable using `__()` or `_e()` with the correct text domain (`mcb-juice-qr-gateway`).

## 4. Development Workflow

1.  **Local Environment Setup**:
    *   Use a local development environment like Local by Flywheel, Laragon, or Docker with a WordPress setup.
    *   Ensure you have PHP 7.4+ and WooCommerce 8.9+ installed.
    *   Install WP-CLI.
    *   Set up PHPUnit for WordPress plugin testing (refer to the PHPUnit test generation steps for guidance).
2.  **Clone the Repository**:
    ```bash
    git clone [repository-url]
    cd mcbjuicepaypro
    ```
3.  **Install Dependencies (if any)**: Currently, there are no Composer dependencies, but if they are introduced, you would run `composer install`.
4.  **Make Changes**:
    *   Create a new Git branch for your feature or bug fix: `git checkout -b feature/your-feature-name` or `git checkout -b bugfix/your-bug-fix`.
    *   Implement your changes, adhering to coding standards.
    *   Write or update tests for your changes.
5.  **Run Tests**:
    *   Navigate to the plugin's root directory.
    *   Run `phpunit` to execute the tests. Ensure all tests pass.
6.  **Commit Changes**:
    *   Stage your changes: `git add .`
    *   Commit with a descriptive message: `git commit -m "Feat: Add new feature"`, `git commit -m "Fix: Resolve bug in X"`, `git commit -m "Docs: Update documentation"`.
7.  **Push to Remote**:
    *   Push your branch: `git push origin your-branch-name`.
8.  **Create a Pull Request**: Open a pull request to the `develop` branch for review.

## 5. Troubleshooting

*   **Plugin Not Activating**:
    *   Check your `debug.log` file in `wp-content/` for PHP errors.
    *   Ensure all `require_once` paths are correct.
    *   Verify that `ABSPATH` is defined at the top of your PHP files.
*   **Settings Not Saving**:
    *   Check for JavaScript errors in your browser console.
    *   Ensure nonce fields are correctly implemented and verified in `process_admin_options()`.
    *   Verify that `init_form_fields()` is correctly defining your settings.
*   **QR Code Not Displaying**:
    *   Check the browser console for any errors related to image loading or JavaScript.
    *   Verify the QR code URL is correct and accessible.
    *   Ensure the `qr_code_type` setting is correctly configured.
*   **API Verification Issues**:
    *   Check your server's error logs for any issues with `wp_remote_post()`.
    *   Verify API URL and API Key are correct in the plugin settings.
    *   Ensure the API endpoint is accessible from your server.

## 6. Contribution Guidelines

*   **Branching Strategy**: We use a `main` and `develop` branching strategy. All new features and bug fixes should be developed on separate feature branches branched off `develop`.
*   **Pull Requests**:
    *   Submit pull requests to the `develop` branch.
    *   Provide a clear and concise description of your changes.
    *   Ensure your code adheres to coding standards and all tests pass.
    *   Request a review from another team member.
*   **Code Reviews**: Be open to feedback and constructive criticism during code reviews.
*   **Testing**: Always write tests for new features and bug fixes.
*   **Documentation**: Update relevant documentation (code comments, `README.md`, `JUNIOR_DEV_GUIDE.md`) for any changes you make.

Thank you for contributing to the MCB Juice QR Payment Gateway Pro!
