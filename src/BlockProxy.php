<?php
namespace ComemApi;

use Omeka\Api\Representation\SitePageBlockRepresentation;

class BlockProxy extends SitePageBlockRepresentation
{
    /**
     * @var array
     */
    private $extraAttachmentData;

    public function __construct(SitePageBlockRepresentation $blockRep, $extraAttachmentData)
    {
        $this->setServiceLocator($blockRep->getServiceLocator());
        $this->block = $blockRep->block;
        $this->extraAttachmentData = $extraAttachmentData;
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        foreach ($json['o:attachment'] as $index => $attachmentRep) {
            if (!isset($this->extraAttachmentData[$index])) {
                continue;
            }
            $attachmentData = $this->extraAttachmentData[$index];
            $json['o:attachment'][$index] = new RepresentationProxy($attachmentRep, $attachmentData);
        }
        return $json;
    }
}
