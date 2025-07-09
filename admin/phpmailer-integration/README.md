# PHPMailer Integration Project

This project demonstrates how to integrate PHPMailer for sending emails using SMTP. It includes configuration settings and a main script to handle email sending.

## Project Structure

```
phpmailer-integration
├── src
│   ├── mailer.php          # Main script for sending emails
│   └── config
│       └── mail_config.php # Configuration settings for the mailer
├── vendor
│   └── PHPMailer           # PHPMailer library files
├── composer.json           # Composer configuration file
└── README.md               # Project documentation
```

## Requirements

- PHP 7.0 or higher
- Composer

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/phpmailer-integration.git
   cd phpmailer-integration
   ```

2. Install dependencies using Composer:
   ```
   composer install
   ```

## Configuration

- Open `src/config/mail_config.php` and update the SMTP settings with your email provider's details.

## Usage

- To send an email, run the `src/mailer.php` script. Make sure to provide the necessary parameters for the email.

## License

This project is licensed under the MIT License. See the LICENSE file for details.