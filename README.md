# About

October-fied wrapper around the [wkhtmltopdf and wkhtmltoimage](https://wkhtmltopdf.org/) conversion libraries provided through the [barryvdh/laravel-snappy](https://github.com/barryvdh/laravel-snappy) package.

# Installation

To install from the [Marketplace](https://octobercms.com/plugin/luketowers-snappypdf), click on the "Add to Project" button and then select the project you wish to add it to before updating the project to pull in the plugin.

To install from the backend, go to **Settings -> Updates & Plugins -> Install Plugins** and then search for `LukeTowers.SnappyPDF`.

To install from [the repository](https://github.com/luketowers/oc-snappypdf-plugin), clone it into **plugins/luketowers/snappypdf** and then run `composer update` from your project root in order to pull in the dependencies.

# Configuration

The main configuration values that you may wish to change would be the paths to the executable binaries for `wkhtmltopdf` and `wkhtmltoimage`. These binaries are pulled into **plugins/luketowers/snappypdf/vendor/bin** by default (when installing from the marketplace) and the default configuration reflects this. Also supported by default is installing by cloning the [repository](https://github.com/luketowers/oc-snappypdf-plugin) and loading the dependencies with `composer update` (where the binaries will be located in your project's **vendor/bin**).

If you need to change the path used to locate those binaries, then you have a few options. If you are already using [`.env` files for environment level configuration](http://octobercms.com/docs/setup/configuration#environment-config-extended) then you can simply add `SNAPPY_PDF_BINARY` and `SNAPPY_IMAGE_BINARY` with their respective paths to your `.env` file and call it a day. 

If you are not using `.env` files, or you need to change a configuration value other than the binary paths; then you can override this plugin's configuration and change the necessary values by copying **config/config.php** from the plugin into your project's **config/luketowers/snappypdf/config.php** file [(as specified in the OctoberCMS docs)](http://octobercms.com/docs/plugin/settings#file-configuration).

# Usage

When using this plugin, it is highly recommended that you [add a dependency on it](http://octobercms.com/docs/plugin/registration#dependency-definitions) to the plugin you are using it from. 

In order to use the libraries provided by this plugin, simply import them with `use` statements at the top of your PHP files.

The example below will render a record from a controller and stream it to the browser for downloading or viewing.

```php
<?php namespace MyVendor\MyPlugin\Controllers;

use Response;
use SnappyPDF;
use Backend\Classes\Controller;

use MyVendor\MyPlugin\Models\Example as ExampleModel;

class Examples extends Controller
{
    public function download($id)
    {
        // Load the example record
        $example = ExampleModel::find($id);
        
        // Load the template
        $template = File::get(plugins_path('myvendor/myplugin/views/example-record-template.htm'));
        
        // Render the template
        $renderedHtml = Twig::parse($template, [
            'example' => $example,
        ]);
        
        // Render as a PDF
        $pdf = SnappyPDF::loadHTML($renderedHtml)
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setPaper('letter')
            ->output();
            
        return Response::make($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "filename={$example->name}.pdf",
        ]);
    }
}
```
    
More information on utilizing the libraries is available at the [barryvdh/laravel-snappy repository](https://github.com/barryvdh/laravel-snappy#usage) and the [wkhtmltopdf manual](http://wkhtmltopdf.org/usage/wkhtmltopdf.txt).
