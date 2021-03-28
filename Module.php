<?php
namespace ComemApi;

use ComemApi\BlockProxy;
use ComemApi\RepresentationProxy;
use Omeka\Module\AbstractModule;
use Omeka\Stdlib\DateTime;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;

class Module extends AbstractModule
{
    public function attachListeners(SharedEventManagerInterface $events)
    {
        $events->attach(
            'Omeka\Api\Representation\ItemRepresentation',
            'rep.resource.json',
            [$this, 'enhanceItemApi']
        );

        $events->attach(
            'Omeka\Api\Representation\SitePageRepresentation',
            'rep.resource.json',
            [$this, 'enhanceSitePageApi']
        );

        $events->attach(
            'Omeka\Api\Representation\SiteRepresentation',
            'rep.resource.json',
            [$this, 'enhanceSiteApi']
        );
    }

    /**
     * Enhance the API output for items
     *
     * - Append "thumbnail_urls" key to o:media entries
     * - Append "title" key to o:item_set entries
     *
     * @param Event $e
     */
    public function enhanceItemApi(Event $e)
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
            $json['o:media'][$index] = new RepresentationProxy($mediaRef,
                ['thumbnail_urls' => $thumbnailsById[$id]]
            );
        }

        foreach ($itemRep->itemSets() as $set) {
            $setTitlesById[$set->id()] = (string) $set->value('dcterms:title');
        }

        foreach ($json['o:item_set'] as $index => $setRef) {
            $id = $setRef->id();
            $json['o:item_set'][$index] = new RepresentationProxy($setRef,
                ['title' => $setTitlesById[$id]]
            );
        }
        $e->setParam('jsonLd', $json);
    }

    /**
     * Enhance the API output for site pages
     *
     * - Append "thumbnail_urls" key to o:attachment entries
     * - Append "title" key to o:attachment entries
     *
     * @param Event $e
     */
    public function enhanceSitePageApi(Event $e)
    {
        $json = $e->getParam('jsonLd');
        $pageRep = $e->getTarget();

        $blocks = $pageRep->blocks();

        $thumbnailsById = [];
        $itemTitlesById = [];

        foreach ($json['o:block'] as $blockIndex => $block) {
            $extraAttachmentData = [];
            foreach ($block->attachments() as $attachmentIndex => $attachment) {
                $item = $attachment->item();
                if ($item) {
                    $extraAttachmentData[$attachmentIndex]['title']
                        = (string) $item->value('dcterms:title');
                }
                $media = $attachment->media();
                if ($media) {
                    $extraAttachmentData[$attachmentIndex]['thumbnail_urls']
                        = $media->thumbnailUrls();
                }
            }
            $json['o:block'][$blockIndex] = new BlockProxy($block, $extraAttachmentData);
        }

        $e->setParam('jsonLd', $json);
    }

    /**
     * Enhance the API output for sites
     *
     * - Append "created" key to o:page entries
     * - Append "title" key to o:page entries
     *
     * @param Event $e
     */
    public function enhanceSiteApi(Event $e)
    {
        $json = $e->getParam('jsonLd');
        $siteRep = $e->getTarget();

        $pages = $siteRep->pages();
        $extraDataById = [];

        foreach ($pages as $page) {
            $extraDataById[$page->id()] = [
                'created' => new DateTime($page->created()),
                'title' => $page->title(),
            ];
        }

        foreach ($json['o:page'] as $index => $pageRef) {
            $id = $pageRef->id();
            $json['o:page'][$index] = new RepresentationProxy($pageRef,
                $extraDataById[$id]);
        }
        $e->setParam('jsonLd', $json);
    }
}

