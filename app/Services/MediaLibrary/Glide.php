<?php

namespace App\Services\MediaLibrary;

use A17\Twill\Services\MediaLibrary\Glide as GlideExtend;
use A17\Twill\Services\MediaLibrary\ImageServiceDefaults;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Glide\Urls\UrlBuilder;
use League\Glide\Urls\UrlBuilderFactory;

class Glide extends GlideExtend
{
    use ImageServiceDefaults;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    public function __construct(Config $config, Application $app, Request $request)
    {
        $this->config = $config;
        $this->app = $app;
        $this->request = $request;

        $baseUrl = implode('/', [
            rtrim($this->config->get('twill.glide.base_url'), '/'),
            ltrim($this->config->get('twill.glide.base_path'), '/'),
        ]);

        $this->urlBuilder = UrlBuilderFactory::create(
            $baseUrl,
            $this->config->get('twill.glide.use_signed_urls') ? $this->config->get('twill.glide.sign_key') : null
        );

        parent::__construct($config, $app, $request);
    }

    /**
     * @param  string  $id
     * @return string
     */
    public function getUrl($id, array $params = [])
    {
        $defaultParams = config('twill.glide.default_params');

        // if crop width is less than default width, use crop width
        if (! empty($params['crop']) && ! empty($defaultParams['w'])) {
            $defaultWidth = $defaultParams['w'];
            $width = explode(',', $params['crop'])[0];

            $defaultParams['w'] = $width > $defaultWidth ? $defaultWidth : $width;
        }

        if (! Str::endsWith($id, '.svg')) {
            $ext = last(explode('.', $id));
            $fname = substr($id, 0, strlen($id) - (strlen($ext) + 1));
            $query = bin2hex(http_build_query(array_replace($defaultParams, $params)));
            $id = $fname.'__'.$query.'.'.$ext;
        }

        return $this->urlBuilder->getUrl($id);
    }
}
