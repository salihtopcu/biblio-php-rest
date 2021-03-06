<?php

namespace Biblio\http;

interface IResponseBuilderListener
{
    function onBeforeResponseSend();
}

class ResponseBuilder
{

    private $listener;

    public function __construct(IResponseBuilderListener $listener = null)
    {
        $this->listener = $listener;
    }

    public function setListener(IResponseBuilderListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @param       $data
     * @param       $code       NetworkStatusCode
     */
    public function run($data, $code)
    {
        // TODO: Will be edit for different content types
        if (!is_null($data)) {
            if (is_array($data) == "Collection" && method_exists($data, "listingDto"))
                $data = $data->listingDto();
            else if (method_exists($data, "dto"))
                $data = $data->dto();
            if (is_array($data)) {
                // TODO: add xml parsing
//                if (Request::getAcceptedContentType() == ContentType::Xml) {
                /*                    $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');*/
//                    parent::array_to_xml($data, $xml);
//                    $data = $xml->asXML();
//                } else
                $data = json_encode($data);
            }

            if (!is_null($this->listener))
                $this->listener->onBeforeResponseSend();

            header("HTTP/1.1 " . $code . " " . Network::getStatusMessage($code));
            header("Content-Type:" . Request::getAcceptedContentType());
            echo $data;
            exit;
        }
    }
}
