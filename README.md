*CuteViewer* is a simple file/directory viewer made in PHP for my personal use. Since some people have shown interest in using it, I decided to distribute the source.
Most features require JavaScript enabled to function properly.

## Features
-Password protection for deletion/renaming of files
-View file/directory listings
-Hiding files/directories from being listed
-Rename files/directories
-Delete files
-Delete directories recursively
-Upload several files
-Different colors for the interface (the CSS stylesheet is generated dynamically)

## Install instructions
Download zip/tarball and extract.
Open up fviewer.php in your favorite text editor.
Change the $pass variable.
Add the files/directories you want to hide to the $hiddenDirs array.
Change/add any color you want to the $colors array. Each array key is a hex color in the RRGGBB or RGB format, which fstyle.php uses to generate a stylesheet using that color.

## License
GPLv2
