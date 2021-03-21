<?php
namespace ComemApi;

use JsonSerializable;
use Omeka\Api\Representation\AbstractRepresentation;

class RepresentationProxy implements JsonSerializable
{
    /**
     * @var AbstractRepresentation
     */
    private $rep;

    /**
     * @var array
     */
    private $extraData;

    public function __construct(AbstractRepresentation $rep, array $extraData)
    {
        $this->rep = $rep;
        $this->extraData = $extraData;
    }

    public function jsonSerialize()
    {
        $json = $this->rep->jsonSerialize();
        $json = array_merge($json, $this->extraData);
        return $json;
    }
}
