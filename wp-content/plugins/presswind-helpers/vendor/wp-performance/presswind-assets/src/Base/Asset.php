<?php

declare(strict_types=1);

namespace PressWind\Base;

class Asset
{
    /**
     * handle of file
     */
    protected string $handle;

    /**
     * path to file
     */
    protected string $src;

    /**
     * dependencies of file
     */
    protected array $deps = [];

    /**
     * version of file
     */
    protected string $ver;

    /**
     * @throws \Exception
     */
    public function __construct($handle, $src = '')
    {
        if (!is_string($handle)) {
            throw new \Exception('handle must be a string');
        }

        $this->handle = $handle;
        $this->src = $src;
    }

    /**
     * define dependencies for asset
     *
     *
     * @return Asset object
     */
    public function dependencies(array $deps): Asset
    {
        $this->deps = $deps;

        return $this;
    }

    /**
     * define version for asset
     * if not defined, get filemtime
     *
     *
     * @return Asset object
     */
    public function version(string $ver): Asset
    {
        $this->ver = $ver;

        return $this;
    }

    /**
     * attach inline script to script
     */
    public function withInline(string $script, string $position = 'after'): void
    {
        add_action('wp_enqueue_scripts', function () use ($script, $position) {
            wp_add_inline_script($this->handle, $script, $position);
        });
    }

    /**
     * enqueue asset in front
     */
    public function toFront(): Asset
    {
        $enqueue = function () {
            $this->enqueue();
        };
        add_action('wp_enqueue_scripts', $enqueue);

        return $this;
    }

    /**
     * enqueue asset in back
     */
    public function toBack(): void
    {
        $enqueue = function () {
            $this->enqueue();
        };
        add_action('admin_enqueue_scripts', $enqueue);
    }

    /**
     * enqueue asset in block editor
     */
    public function toBlock(): void
    {
        $enqueue = function () {
            $this->enqueue();
        };
        add_action('enqueue_block_editor_assets', $enqueue);
    }

    /**
     * enqueue asset in login
     */
    public function toLogin(): void
    {
        $enqueue = function () {
            $this->enqueue();
        };
        add_action('login_enqueue_scripts', $enqueue);
    }

    /**
     * get version of file
     */
    protected function getVersion(): string
    {
        $theme_dir = get_stylesheet_directory();
        $theme_url = get_stylesheet_directory_uri();

        // Si $this->src commence par $theme_url, on construit le chemin local
        if (strpos($this->src, $theme_url) === 0) {
            $relative_path = str_replace($theme_url, '', $this->src);
            $file_path = $theme_dir . $relative_path;

            // dev domain (hot reload etc.)
            if (str_contains($file_path, 'fdhpa17.devwarehouse.ddns.net')) {
                return strval(time());
            }

            // Si le fichier existe bien, on retourne filemtime
            if (file_exists($file_path)) {
                return $this->ver ?? strval(filemtime($file_path));
            } else {
                // fallback si le fichier n'existe pas
                return $this->ver ?? strval(time());
            }
        }

        // Si ce n’est pas une ressource du thème local (genre CDN, localhost:3000, etc.)
        return $this->ver ?? strval(time());
    }

    protected function enqueue(): void
    {
    }
}
