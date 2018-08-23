# joomla-hydro-raindrop
A Joomla plugin to integrate Hydro Raindrop MFA

## Using windows
- Clone the repository `git clone https://github.com/DaveM2011/joomla-hydro-raindrop` to anywhere on your computer
- Make sure you have installed composer (https://getcomposer.org/)
- Decend into the `plg_twofactorauth_hydroraindrop` directory
- Run composer `composer install`
- Go back to the `joomla-hydro-raindrop` directory
- Right click `CreatePackage.ps1` and click `Run with PowerShell` note `7Zip` needs to be installed to use this
- Congrats you should have a `pkg_hydroraindrop.zip` file now this is the package you install

## Using unix
- Clone the repository `git clone https://github.com/DaveM2011/joomla-hydro-raindrop` to anywhere on your server
- Make sure you have installed composer (https://getcomposer.org/)
- `cd plg_twofactorauth_hydroraindrop`
- Run composer `composer install`
- Go back to the main directory `cd ..`
- Run `chmod +x createPackage.sh && bash createPackage.sh` u might need `sudo` who knows :smile
- Congrats you should have a `pkg_hydroraindrop.zip` file now this is the package you install

## Usage instructions
1. Login to your Joomla site admin.
2. Go to `Extensions -> Manage -> Install` and drag & drop the `pkg_hydroraindrop.zip` package you created earlier. The package shoud install.

If you don't have a **Hydrogen Developer Account**, go to [www.hydrogenplatform.com](https://www.hydrogenplatform.com) to register an account.

1. Go to `Extensions -> Plugins` search for `Hydro` you will **note there are 2 plugins listed this is because the administration area of joomla requires a seperate plugin to handle the administration if you dont plan on using Hydro's 2FA on your admin you can safely disable this**
2. Select the `Two Factor Authentication - Hydro Raindrop MFA` plugin to edit its configuration
3. Enter your `Application ID`, `Client ID` & `Client Secret` change the `Status` to `Enabled` and then `Save & Close`
4. Go to your proflle on the frontend site of Joomla find the `Two Factor Authentication` settings and change to `Hydro Raindrop`, enter your HydroID in the `Hydro Raindrop MFA` section.
5. Follow the verification procedure to activate MFA for your account.

## Requirements

* **SSL MUST be enabled for MFA to work.**
* PHP 7.0 or higher is required.

## Documentation

https://www.hydrogenplatform.com/developers

## Issues

https://github.com/DaveM2011/joomla-hydro-raindrop/issues

## Support

https://github.com/DaveM2011/joomla-hydro-raindrop/issues

## Contributing to Hydro Raindrop Joomla plugin
If you want to address an issue/bug, please create an issue first.
