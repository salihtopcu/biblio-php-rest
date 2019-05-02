<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 18.02.2019
 * Time: 20:08
 */

namespace Biblio\http;

class ResponseBuilder {

    /**
     * @param       $data
     * @param       $code       NetworkStatusCode
     * @param array $dbEnvoys   array of iDatabaseEnvoy
     */
    public function run($data, $code, array $dbEnvoys = []) {
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
            foreach ($dbEnvoys as $dbEnvoy) {
                if (!is_null($dbEnvoy))
                    $dbEnvoy->disconnect();
            }
            header("HTTP/1.1 " . $code . " " . Network::getStatusMessage($code));
            header("Content-Type:" . Request::getAcceptedContentType());
            echo $data;
            exit;
        }
    }
}