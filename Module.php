<?php
namespace ComemApi;

use ComemApi\ReferenceProxy;
use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;

class Module extends AbstractModule
{
    public function attachListeners(SharedEventManagerInterface $events)
    {
        $events->attach(
            'Omeka\Api\Representation\ItemRepresentation',
            'rep.resource.json',
            [$this, 'addItemMediaThumbnails']
        );
    }

    /**
     * Append "thumbnail_urls" key to o:media entries for item api json
     *
     * @param Event $e
     */
    public function addItemMediaThumbnails(Event $e)
    {
        $json = $e->getParam('jsonLd');
        $itemRep = $e->getTarget();
        $thumbnailsById = [];
        $setTitlesById = [];

        foreach ($itemRep->media() as $media) {
            $thumbnailsById[$media->id()] = $media->thumbnailUrls();
        }

        foreach ($json['o:media'] as $index => $mediaRef) {
            $id = $mediaRef->id();
            $json['o:media'][$index] = new ReferenceProxy($mediaRef, 
                ['thumbnail_urls' => $thumbnailsById[$id]]
            );
        }

        foreach ($itemRep->itemSets() as $set) {
            $setTitlesById[$set->id()] = (string) $set->value('dcterms:title');
        }

        foreach ($json['o:item_set'] as $index => $setRef) {
            $id = $setRef->id();
            $json['o:item_set'][$index] = new ReferenceProxy($setRef,
                ['title' => $setTitlesById[$id]]
            );
        }
        $e->setParam('jsonLd', $json);
    }
}

