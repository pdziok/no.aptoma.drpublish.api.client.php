<?php
/**
 * DrPublishApiClientArticle.php
 * @package    no.aptoma.drpublish.client.core
 */
/**
 * DrPublishApiClientArticle represents a DrPublish article including its meta information.
 * The class provides several general and specific methods for accessing article elements
 *
 * @package    no.aptoma.drpublish.client.core
 * @copyright  Copyright (c) 2006-2010 Aptoma AS (http://www.aptoma.no)
 * @version    $Id: DrPublishApiClient.php 967 2010-09-27 07:35:54Z stefan $
 * @author     stefan@aptoma.no
 */
class DrPublishApiClientArticle
{
    protected $dom;
    protected $xpath;
    protected $dpClient;
    protected $xml;

    /**
     * Class constructor
     * @param string $xmlArticle XML representation of an DrPublish article; retrieved from DrPublish API
     * @param DrPublishApiClient Referance to DrPublishApiClient object, used for loading additional information
     * @return void
     * @throws DrPublishApiClientException
     */
    public function __construct($dom, $dpClient = null)
    {
        $this->dom = $dom;
        $notFound = $this->dom->getElementsByTagNameNS(DrPublishApiClient::$XMLNS_URI, "notFound")->item(0);
        if (!empty($notFound)) {
            throw new DrPublishApiClientException("Article id='" . $notFound->getAttribute('articleId') . "' not found",
                DrPublishApiClientException::ARTICLE_NOT_FOUND_ERROR);
        }
        $this->dpClient = $dpClient;
        $this->xpath = new DOMXPath($this->dom);
        $this->xpath->registerNamespace('DrPublish', DrPublishApiClient::$XMLNS_URI);
    }

    /**
     * Gets a list of article elements queried by XPath
     *
     * @see DPArticleElement
     * @param string $xpathQuery XPath query
     * @return DPArticleElementList
     */
    public function getElements($xpathQuery)
    {
        $list = new DrPublishApiClientList();
        $domNodes = $this->xpath->query($xpathQuery, $this->dom);
        if (!empty($domNodes)) foreach ($domNodes as $domNode) {
            $list->add($this->createDrPublishApiClientArticleElement($domNode, $this->dom));
        }
        return $list;
    }

    /**
     * Gets one single article element queried by XPath. If multiple elements match the query, the first one will be returned
     * @param string $xpathQuery XPath query
     * @return DPArticleElement | null
     */
    public function getElement($xpathQuery)
    {
        $list = $this->getElements($xpathQuery);
        if (empty($list)) {
            return null;
        }
        return $list->first();
    }

    /**
     * Gets all DPImages included in the article. A DPImage is an picture inserted by using the DrPublish image plugin
     * @see DrPublishApiClientArticleImageElement
     * @return DrPublishApiClientList List items type is DrPublishApiClientArticleImageElement
     */
    public function getDPImages()
    {
        $list = new DrPublishApiClientList();
        $domNodes = $this->xpath->query("//div[@class and contains(concat(' ',normalize-space(@class),' '),' dp-article-image ')]");
        foreach ($domNodes as $domNode) {
            $list->add($this->createDrPublishApiClientArticleImageElement($domNode, $this->dom));
        }
        return $list;
    }

    /**
     * Gets the first DPImage of this article.
     * @see DrPublishApiClientArticle::getDPImages()
     * @return DrPublishApiClientArticleImageElement | null
     */
    public function getLeadDPImage()
    {
        $dpClientImages = $this->getDPImages();
        return $dpClientImages->first();
    }

    /**
     * Gets a list of DrPublishApiClientAuthor objects
     * @see DrPublishApiClientAuthor
     * @return DrPublishApiClientList
     */
    public function getDPAuthors()
    {
        $list = new DrPublishApiClientList();
        $domNodes = $this->xpath->query('//DrPublish:meta/authors/author');
        foreach ($domNodes as $domNode) {
            $list->add($this->createDrPublishApiClientAuthor($domNode, $this->dom));
        }
        return $list;
    }

    /**
     * Gets a category list of DrPublishApiClientCategory objects
     * @see DrPublishApiClientCategory
     * @return DrPublishApiClientList
     */
    public function getDPCategories()
    {
        $list = new DrPublishApiClientList();
        $domNodes = $this->xpath->query('//DrPublish:meta/categories/category');
        foreach ($domNodes as $domNode) {
            $list->add($this->createDrPublishApiClientCategory($domNode));
        }
        return $list;
    }

    /**
     * Gets a DrPublishApiClientSource object
     * @return DrPublishApiClientArticleElentList
     */
    public function getDPSource()
    {
        $domNode = $this->xpath->query('//DrPublish:meta/source[1]')->item(0);
        if (empty($domNode)) {
            return null;
        } else {
            $data = new stdClass();
            $data->id = $domNode->getAttribute('id');
            $data->name = $domNode->nodeValue;
            return new DrPublishApiClientSource($data);
        }
    }

    /**
     * Gets a tag list of DrPublishApiClientTag objects
     * @return DrPublishApiClientArticleElentList
     */
    public function getDPTags()
    {
        $list = new DrPublishApiClientList();
        $domNodes = $this->xpath->query('//DrPublish:meta/tags/tag');
        foreach ($domNodes as $domNode) {
            $list->add($this->createDrPublishApiClientTag($domNode));
        }
        return $list;
    }

    /**
     * Creates a DrPublishApiClientArticleElement from DomElement
     * This method can be overwritten by custom client
     *
     * @param DomElement $domNode
     * @param DomDocument $domDocument
     * @return DrPublishApiClientArticleElement
     */
    protected function createDrPublishApiClientArticleElement($domNode, $domDocument)
    {
        return new DrPublishApiClientArticleElement($domNode, $domDocument, $this->dpClient, $this->xpath);
    }

    /**
     * Creates a DrPublishApiClientArticleImageElement from DomElement
     * This method can be overwritten by custom client
     *
     * @param DomElement $domNode
     * @param DomDocument $domDocument
     * @return DrPublishApiClientArticleImageElement
     */
    protected function createDrPublishApiClientArticleImageElement($domNode, $domDocument)
    {
        return new DrPublishApiClientArticleImageElement($domNode, $domDocument, $this->dpClient, $this->xpath);
    }

    /**
     * Creates a DrPublishApiClientAuthor from DomElement
     * This method can be overwritten by custom client
     *
     * @param DomElement $domNode
     * @param DomDocument $domDocument
     * @return DrPublishApiClientAuthor
     */
    protected function createDrPublishApiClientAuthor($domNode)
    {
       $data = new stdClass();
       $data->id = $domNode->getAttribute('id');
       $data->fullname = $domNode->nodeValue;
       $data->username = $domNode->getAttribute('username');
       $data->email =  $domNode->getAttribute('email');
       return new DrPublishApiClientAuthor($data);
    }

    /**
     * Creates a DrPublishApiClientCategory from DomElement
     * This method can be overwritten by custom client
     * @param DomElement $domNode
     * @param DomDocument $domDocument
     * @return DrPublishApiClientCategory
     */
    protected function createDrPublishApiClientCategory($domNode)
    {
       $data = new stdClass();
       $data->id = $domNode->getAttribute('id');
       $data->name = $domNode->nodeValue;
       $data->parentId =  $domNode->getAttribute('parentId');
       $data->isMain = $domNode->getAttribute('isMain');
       return new DrPublishApiClientCategory($data, $this->dpClient);
    }

    /**
     * Creates a DrPublishApiClientTag from DomElement
     * This method can be overwritten by custom client
     *
     * @param DomElement $domNode
     * @param DomDocument $domDocument
     * @return DrPublishApiClientCategory
     */
    protected function createDrPublishApiClientTag($domNode)
    {
       $data = new stdClass();
       $tagType = new stdClass();
       $tagType->id = $domNode->getAttribute('tagTypeId');
       $tagType->name = $domNode->getAttribute('tagTypeName');
       $data->id = $domNode->getAttribute('id');
       $data->name = $domNode->nodeValue;
       $data->tagType =  $tagType;
       return new DrPublishApiClientTag($data);
    }

    /**
     * Removes elements found by XPATH query
     * @param string $xpathQuery XPATH query
     */
    public function removeElements($xpathQuery)
    {
        $domNodes = $this->xpath->query($xpathQuery, $this->dom);
        foreach ($domNodes as $domNode) {
            $domNode->parentNode->removeChild($domNode);
        }
    }

    /**
     * Changes the node names of elements found by XPATH query
     * @param string $xpathQuery
     * @param string $name new node name
     */
    public function changeNames($xpathQuery, $name)
    {
        $domNodes = $this->xpath->query($xpathQuery, $this->dom);
        foreach ($domNodes as $node) {
            $newNode = $node->ownerDocument->createElement($name);
            foreach ($node->childNodes as $child) {
                $child = $node->ownerDocument->importNode($child, true);
                $newNode->appendChild($child, true);
            }
            foreach ($node->attributes as $attrName => $attrNode) {
                $newNode->setAttribute($attrName, $attrNode);
            }
            $newNode->ownerDocument->replaceChild($newNode, $node);
        }
    }

    public function xml()
    {
        return $this->dom->saveXml();
    }

}
