$zipFile = "travelship_v2_5.zip"

if (Test-Path $zipFile) { Remove-Item $zipFile }

# List of files to include
$files = @(
    "assets",
    "includes",
    "templates",
    "travelship.php",
    "uninstall.php",
    ".gitignore"
)

Compress-Archive -Path $files -DestinationPath $zipFile
Write-Host "Plugin packaged successfully: $zipFile"
