![tests](https://github.com/alessandrotesoro/wp-diagnostics/workflows/Tests/badge.svg)
![license](https://img.shields.io/github/license/alessandrotesoro/wp-diagnostics)

<!-- ABOUT THE PROJECT -->
## ℹ️ About WP Diagnostics
WP Diagnostics is a utility library to handle detection of minimum system requirements in WordPress plugins.

<!-- Features -->
## ✨ Benefits

1. Validate PHP Version, WordPress version, active plugins, php extensions, class existance or use custom callbacks.
2. Support for custom requirements validators.

<!-- GETTING STARTED -->
## ✅ Requirements

1. PHP 7.2 or higher.
2. Composer

<!-- GETTING STARTED -->
## 📖 Usage

#### 1. Installation

```php
composer require alessandrotesoro/wp-diagnostics
```

#### 2. Basic usage

1. Create an instance of `Sematico\Diagnostics\Runner`.
2. Add checks using the `addCheck` method.
3. Verify that requirements are satisfied using the `satisfied` method.
4. Display an admin notice using the `printNotice` method if requirements are not met.

**Example**:

```php
<?php

use Sematico\Diagnostics\Check\PHPVersion;
use Sematico\Diagnostics\Check\WPVersion;
use Sematico\Diagnostics\Runner;

defined( 'ABSPATH' ) || exit;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload.php';
}

$requirements = new Runner(
	__FILE__,
	'My plugin',
	[
		new WPVersion( '5.7' ),
		new PHPVersion( '7.2' ),
	]
);

if ( ! $requirements->satisfied() ) {
	$requirements->printNotice();
	return;
}

// Now that requirements are met, you can boot your plugin.


```

In the example above we're creating an instance of the `Runner` by passing through the plugin's entry file path, the title of the plugin an array of checks to verify.

#### 3. Display an admin notice

When a plugin's requirements are not satisfied, you can use the `printNotice` method to display an error message in the admin panel and automatically disable the plugin. The notice will contain all errors found.

```php
if ( ! $requirements->satisfied() ) {
	$requirements->printNotice();
	return;
}
```

#### 4. Setting up the name of the plugin

When creating an instance of the `Runner`, the 2nd parameter takes in a string which is used as the name of your plugin. The name is used within the error notice displayed when requirements are not satisfied.

```php
$requirements = new Runner(
	__FILE__,
	'My plugin'
);
```

#### 5. Checks configuration

The third parameter of the `Runner` instance takes in an array containing instances of all checks required for your plugin.

```php
$requirements = new Runner(
	__FILE__,
	'My plugin',
	[
		new WPVersion( '5.7' ),
		new PHPVersion( '7.2' ),
	]
);
```

In the above example, we're verifying that WordPress is at least running version 5.7 and that the php version is at least 7.2.

Alternatively you may add checks using the `addCheck` method.

```php
$requirements->addCheck( new WPVersion( '5.7' ) );
```

#### 6. Stop execution on first fail

In some cases you may want to stop the checks verification process as soon as the first error occurs. To enable this, simply set the 4th parameter as "true".

```php
$requirements = new Runner(
	__FILE__,
	'My plugin',
	[
		new WPVersion( '5.7' ),
		new PHPVersion( '7.2' ),
	],
	true
);
```

Alternatively, you may use the `setBreakOnFailure` method.

```php
$requirements->setBreakOnFailure( true );
```

#### 7. Messages customization

By default, the library does not provide support for internationalization. Should you wish to customize the default messages or add support for translation, you may use the `setMessages` method and overwrite any or all messages used by the library.

```php
$requirements->setMessages( [
	'title' => __( 'The &#8220;%1$s&#8221; plugin could not be activated.', 'my-plugin-textdomain' )
] );
```

In the above example, we're replacing one of the strings and adding translation support too.

You can find the full list of default strings by inspecting the `getDefaultMessages` method of the `Runner.php` file.

## 📖 Available checks

#### 1. Callback

Run a function and use its return value as the result.

In order to pass the check, you must return a boolean value of true. To fail a check you must return an instance of [WP_Error.](https://developer.wordpress.org/reference/classes/wp_error/)

```php
$check1 = new Callback(
	function() {
		return true;
	}
);

$check2 = new Callback(
	function() {
		return new WP_Error( 'example', 'Your error message goes here' );
	}
);

$requirements->addChecks( [ $check1, $check2 ] );

```

#### 2. ClassExists

Check if a class or an array of classes exists. Example:

```php
// Single class check
$requirements->addCheck( new ClassExists( WP_Error::class ) );

// Multiple classes check
$requirements->addCheck( new ClassExists( [ WP_Error::class, '\Another\Example\Class' ] ) );
```

#### 3. ExtensionLoaded

Check if a PHP extension or an array of extensions is loaded.

```php

$check1 = new ExtensionLoaded( 'mbstring' );

$requirements->addCheck( $check1 );

$check2 = new ExtensionLoaded( [ 'mbstring', 'rar' ] );

$requirements->addCheck( $check2 );


```

#### 4. PHPVersion

Check if the current PHP version matches the given requirement. The check accepts 2 parameters: the version to test for and the [comparison operator](https://www.php.net/version_compare).

```php
$check1 = new PHPVersion( '7.2' );

$requirements->addCheck( $check1 );

$check2 = new PHPVersion( '7.2', '<' );

$requirements->addCheck( $check2 );
```

#### 5. PluginActive

Check if plugin or an array of plugins are currently active on the site.

```php
$check1 = new PluginActive( 'contact-form-7/wp-contact-form-7.php' );

$requirements->addCheck( $check1 );

$check2 = new PluginActive( [ 'woocommerce/woocommerce.php', 'elementor/elementor.php' ] );

$requirements->addCheck( $check2 );

```

#### 6. WPVersion

Check if the current WordPress version matches the given requirement. The check accepts 2 parameters: the version to test for and the [comparison operator](https://www.php.net/version_compare).

```php
$check1 = new WPVersion( '5.7' );

$requirements->addCheck( $check1 );
```

## 📖 Writing custom checks

Every check class must implement the `CheckInterface` and extend the `AbstractCheck` class. You can then provide the `check` method which is responsible for performing the actual check.

The method must return either a `true` boolean value to mark the check as passed or an instance of [WP_Error](https://developer.wordpress.org/reference/classes/wp_error/) to mark the check as failed.

Below is an example class that checks if the registrations are allowed on the site.

```php
namespace MyNameSpace\Example;

use Sematico\Diagnostics\Check\AbstractCheck;
use Sematico\Diagnostics\Check\CheckInterface;
use WP_Error;

class RegistrationActive extends AbstractCheck implements CheckInterface {

	/**
	 * Perform the check.
	 *
	 * @return bool|\WP_Error
	 */
	public function check() {

		if ( ! get_option( 'users_can_register' ) ) {
			return new WP_Error( 'my-error', 'My error message here' );
		}

		return true;
	}

}

```

## 🚨 Security Issues
If you discover a security vulnerability within WP Diagnostics, please email [hello@sematico.com](mailto:hello@sematico.com). All security vulnerabilities will be promptly addressed.

<!-- LICENSE -->
## 🔖 License

Distributed under the MIT License. See `LICENSE` for more information.
