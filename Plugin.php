<?php namespace LukeTowers\SnappyPDF;

use App;
use Config;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

/**
 * SnappyPDF Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'luketowers.snappypdf::lang.plugin.name',
            'description' => 'luketowers.snappypdf::lang.plugin.description',
            'author'      => 'Luke Towers',
            'icon'        => 'icon-file-pdf-o',
            'homepage'    => 'https://github.com/LukeTowers/oc-snappypdf-plugin'
        ];
    }

    /**
     * Runs right before the request route
     */
    public function boot()
    {    
        // Setup required packages
        $this->bootPackages();

        // Overwrite config values if necessary
        $this->validateConfig();
    }

    /**
     * Boots (configures and registers) any packages found within this plugin's packages.load configuration value
     *
     * @see https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-plugins
     * @author Luke Towers <octobercms@luketowers.ca>
     */
    public function bootPackages()
    {
        // Get the namespace of the current plugin to use in accessing the Config of the plugin
        $pluginNamespace = str_replace('\\', '.', strtolower(__NAMESPACE__));

        // Instantiate the AliasLoader for any aliases that will be loaded
        $aliasLoader = AliasLoader::getInstance();

        // Get the packages to boot
        $packages = Config::get($pluginNamespace . '::packages');

        // Boot each package
        foreach ($packages as $name => $options) {
            // Setup the configuration for the package, pulling from this plugin's config
            if (!empty($options['config']) && !empty($options['config_namespace'])) {
                Config::set($options['config_namespace'], $options['config']);
            }

            // Register any Service Providers for the package
            if (!empty($options['providers'])) {
                foreach ($options['providers'] as $provider) {
                    App::register($provider);
                }
            }

            // Register any Aliases for the package
            if (!empty($options['aliases'])) {
                foreach ($options['aliases'] as $alias => $path) {
                    $aliasLoader->alias($alias, $path);
                }
            }
        }
    }

    /**
     * Checks the configuration for the plugin
     */
    public function validateConfig()
    {
        $checkComponents = [
            'pdf' => [
                'binary_x64' => 'wkhtmltopdf-amd64',
                'binary_x32' => 'wkhtmltopdf-i386',
            ],
            'image' => [
                'binary_x64' => 'wkhtmltoimage-amd64',
                'binary_x32' => 'wkhtmltoimage-i386',
            ]
        ];

        foreach ($checkComponents as $component => $options) {
            if (!Config::get("snappy.$component.enabled")) {
                continue;
            }

            if (empty(env('SNAPPY_' . strtoupper($component) . '_BINARY'))) {
                $binaryPathConfigKey = 'snappy.' . $component . '.binary';
                $binaryBasename = pathinfo(Config::get($binaryPathConfigKey), PATHINFO_BASENAME);
                if (!file_exists(Config::get($binaryPathConfigKey))) {
                    Config::set($binaryPathConfigKey, $this->getBinaryPath($binaryBasename));
                }

                if (!$this->is64Bit() && ($binaryBasename === $options['binary_x64'])) {
                    Config::set($binaryPathConfigKey, $this->getBinaryPath($options['binary_x32']));
                }
            }

            $executable = Config::get($binaryPathConfigKey); 
            if (!is_executable($executable)) {
                throw new \Exception(sprintf("The %s binary is not executable. The htmlTo%s library cannot be used.", $executable, strtoupper($component)));
            }
        }
    }

    /**
     * Checks the architecture of the server this plugin is being run on
     *
     * @return bool $is64Bit
     */
    private function is64Bit()
    {
        return (PHP_INT_SIZE === 8);
    }

    /**
     * Generates the path to a binary based on the vendor directory existance
     *
     * @param string $binaryBasen
     * @return string $path
     */
    private function getBinaryPath($binaryBasename)
    {
        $pluginVendorDir = plugins_path('luketowers/snappypdf/vendor/');
        $binPath = "bin/$binaryBasename";

        // Potential sources where the binary could be stored
        $potentialSources = [
            $pluginVendorDir . $binPath,
            $pluginVendorDir . "$binaryBasename/$binPath",
            base_path('vendor/' . $binPath),
            base_path("$binaryBasename/$binPath"),
        ];

        foreach ($potentialSources as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        throw new \Exception("The $binaryBasename binary could not be detected anywhere. Please manually specify the path to this binary through the SNAPPY_TYPE_BINARY environment variables or by setting the paths in config/luketowers/snappypdf/config.php");
    }
}
