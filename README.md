# Pageman CMS

Pageman CMS is an open-source package for Laravel that provides a simple content management system. It is designed to be easily integrated into your Laravel application and offers a range of features to manage content efficiently.

## Features

- Easy installation and setup
- User role management with customizable roles
- Admin panel for managing pages and content
- Customizable authentication and authorization
- Responsive design using Tailwind CSS
- Extendable and customizable

## Requirements

- PHP 7.4 or higher
- Laravel 8.x or higher
- MySQL or any other database supported by Laravel

## Installation

1. Install the package via Composer:

   ```bash
   composer require tsrgtm/pageman
   ```

2. Publish the configuration file:

   ```bash
   php artisan vendor:publish --provider="Tsrgtm\Pageman\PagemanServiceProvider"
   ```

3. Run the installation command:

   ```bash
   php artisan pageman:install
   ```

4. Follow the on-screen instructions to complete the setup.

## Configuration

- Update the `.env` file to configure the database and other necessary settings.
- Customize the `config/pageman.php` file as needed.

## Usage

- Access the admin panel at `/pageman/admin`.
- Manage users, roles, and content through the admin interface.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

## License

Pageman CMS is open-source software licensed under the [MIT license](LICENSE).

## Contact

For any questions or support, please contact [Your Name] at [Your Email].
