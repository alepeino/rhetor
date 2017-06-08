<?php

namespace Alepeino\Rhetor;

use RuntimeException;

class ResourceNotFoundException extends RuntimeException
{
    /**
     * Name of the affected resource.
     *
     * @var string
     */
    protected $resource;

    /**
     * The affected resource IDs.
     *
     * @var int|array
     */
    protected $ids;

    /**
     * Set the affected resource and instance ids.
     *
     * @param  string  $resource
     * @param  int|array  $ids
     */
    public function __construct($resource, $ids = [])
    {
        $this->resource = $resource;
        $this->ids = is_array($ids) ? $ids : [$ids];

        $this->message = "No query results for resource [{$resource}]";

        if (count($this->ids) > 0) {
            $this->message .= ' '.implode(', ', $this->ids);
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    /**
     * Get the affected resource.
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get the affected resource IDs.
     *
     * @return mixed
     */
    public function getIds()
    {
        return $this->ids;
    }
}
