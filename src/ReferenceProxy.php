<?php
namespace ComemApi;

use JsonSerializable;
use Omeka\Api\Representation\ResourceReference;

class ReferenceProxy implements JsonSerializable
{
    /**
     * @var ResourceReference
     */
    private $ref;

    /**
     * @var array
     */
    private $extraData;

    public function __construct(ResourceReference $ref, array $extraData)
    {
        $this->ref = $ref;
        $this->extraData = $extraData;
    }

    public function jsonSerialize()
    {
        $json = $this->ref->jsonSerialize();
        $json = array_merge($json, $this->extraData);
        return $json;
    }
}
