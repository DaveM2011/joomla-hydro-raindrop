if (-not (test-path "$env:ProgramFiles\7-Zip\7z.exe")) {
	throw "$env:ProgramFiles\7-Zip\7z.exe needed"
}
Set-Alias sz "$env:ProgramFiles\7-Zip\7z.exe"

# Create a plugin zip file with the contents of .\plg_system_hydroraindrop
If (Test-Path -Path plg_system_hydroraindrop.zip) {
	Remove-Item plg_system_hydroraindrop.zip
}
sz a -mx=9 plg_system_hydroraindrop.zip .\plg_system_hydroraindrop\*

# Create a plugin zip file with the contents of .\plg_twofactorauth_hydroraindrop
If (Test-Path -Path plg_twofactorauth_hydroraindrop.zip) {
	Remove-Item plg_twofactorauth_hydroraindrop.zip
}
sz a -mx=9 plg_twofactorauth_hydroraindrop.zip .\plg_twofactorauth_hydroraindrop\*

# Create the package zip
If (Test-Path -Path pkg_hydroraindrop.zip) {
	Remove-Item pkg_hydroraindrop.zip
}
sz a -mx=9 pkg_hydroraindrop.zip pkg_hydroraindrop.xml, pkg_script.php, plg_system_hydroraindrop.zip, plg_twofactorauth_hydroraindrop.zip
