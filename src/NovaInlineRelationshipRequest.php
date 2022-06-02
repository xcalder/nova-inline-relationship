<?php

namespace KirschbaumDevelopment\NovaInlineRelationship;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaInlineRelationshipRequest extends NovaRequest
{
    /**
     * {@inheritdoc}
     */
    public function duplicate(
        array $query = null,
        array $request = null,
        array $attributes = null,
        array $cookies = null,
        array $files = null,
        array $server = null
    ): static
    {
        return parent::duplicate($query, $request, $attributes, $cookies, $files, $server);
    }

    /**
     * Update list of converted files
     *
     * @param Collection $files
     */
    public function updateConvertedFiles(Collection $files)
    {
        if (! empty($files)) {
            $this->clearConvertedFiles();

            $files->each(function ($file, $key) {
                if ($file instanceof UploadedFile) {
                    $this->convertedFiles[$key] = $file;
                }
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function createFrom(Request $from, $to = null)
    {
        $request = $to ?: new static;

        $files = $from->files->all();

        $files = is_array($files) ? array_filter($files) : $files;

        $request->initialize(
            $from->query->all(),
            $from->request->all(),
            $from->attributes->all(),
            $from->cookies->all(),
            $files,
            $from->server->all(),
            $from->getContent()
        );

        $request->headers->replace($from->headers->all());

        $request->setJson($from->json());

        if ($session = $from->getSession()) {
            $request->setLaravelSession($session);
        }

        $request->setUserResolver($from->getUserResolver());

        $request->setRouteResolver($from->getRouteResolver());

        return $request;
    }

    /**
     * Clear all the converted files
     */
    public function clearConvertedFiles()
    {
        $this->convertedFiles = null;
    }
}
