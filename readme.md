# TIFFInfo 

This is a (LibTIFF)[http://www.libtiff.org/index.html] wrapper.

# Installation

    composer install vluzrmos/tiffinfo

# Usage

```php
<?php
	$tiff = new Vluzrmos\TIFFInfo\TIFFInfo('/path/to/tiff/file.tiff');

	$tiff->totalPages(); // count pages on tiff
	$tiff->info(); // get an array of all info about the tiff
	$tiff->pages(); // get an array of all info about every page
	$tiff->page(0); // get an array of all info about page 0
?>	
```