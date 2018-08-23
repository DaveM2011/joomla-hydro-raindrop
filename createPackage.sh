#!/bin/bash

# cleanup
rm plg_system_hydroraindrop.zip plg_twofactorauth_hydroraindrop.zip pkg_hydroraindrop.zip

# Create a plugin zip file with the contents of .\plg_system_hydroraindrop
zip plg_system_hydroraindrop.zip plg_system_hydroraindrop/*

# Create a plugin zip file with the contents of .\plg_twofactorauth_hydroraindrop
zip plg_twofactorauth_hydroraindrop.zip plg_twofactorauth_hydroraindrop/*

# Create the package zip
zip pkg_hydroraindrop.zip pkg_hydroraindrop.xml pkg_script.php plg_system_hydroraindrop.zip plg_twofactorauth_hydroraindrop.zip
