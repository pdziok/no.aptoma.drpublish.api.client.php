<?php
/**
 * DrPublishApiClientTag.php
 * @package    no.aptoma.drpublish.client.core
 */
/**
 * DrPublishApiClientAuthor represents an article source
 *
 * @package    no.aptoma.drpublish.client.core
 * @copyright  Copyright (c) 2006-2010 Aptoma AS (http://www.aptoma.no)
 * @version    $Id: DrPublishApiClient.php 967 2010-09-27 07:35:54Z stefan $
 * @author     stefan@aptoma.no
 */
class DrPublishApiClientSource
{
	protected $id;
	protected $name;

    public function __construct($data) {
        foreach($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

    public function __toString()
    {
        return $this->name;
    }

}