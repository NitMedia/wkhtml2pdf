wkhtml2pdf
==========
Version 1.0 - Html to PDF Composer Package

#Usage#

	return PDF::html('hello');

## Quick start

### Required setup

In the `require` key of `composer.json` file add the following

    "nitmedia/wkhtml2pdf": "dev-master"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'Nitmedia\Wkhtml2pdf\Wkhtml2pdfServiceProvider'` to the end of the `$providers` array

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'Nitmedia\Wkhtml2pdf\Wkhtml2pdfServiceProvider',

    ),

At the end of `config/app.php` add `'Wkhtml2pdf'    => 'Nitmedia\Wkhtml2pdf\Facade\Wkhtml2pdf'` to the `$aliases` array

    'aliases' => array(

        'App'        => 'Illuminate\Support\Facades\App',
        'Artisan'    => 'Illuminate\Support\Facades\Artisan',
        ...
        'PDF'    => 'Nitmedia\Wkhtml2pdf\Facade\Wkhtml2pdf',

    ),

### Configuration

Set the properly values to the `config/Wkhtml2pdf/config.php`. 

### Features

	Add PDF::url('http://google.com'); // Pdf from url

The MIT License (MIT)

Copyright (c) <2013> <Nithin Meppurathu>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
