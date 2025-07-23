<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\Manager\Util;

/**
 * Class BisNodeManager.
 */
class BisNodeManager
{
    /**
     * @var \SoapClient
     */
    private $SOAPClient;

    /**
     * @var \DOMDocument
     */
    private $XMLOBJOut;

    /**
     * @var string
     */
    private $XMLNodeNamespacePrefix;

    /**
     * @var \DOMElement
     */
    private $XMLRootNode;

    /**
     * @var string
     */
    private $username = 'all-4-one';

    /**
     * @var string
     */
    private $password = '8sxO3p4mE1';

    /**
     * @var int
     */
    private $serviceCd = 205;

    /**
     * @param string $xmlStr
     *
     * @return string
     */
    public static function xmlToHumanReadable($xmlStr = '')
    {
        $dom = new \DOMDocument('1.0');
        $dom->encoding = 'UTF-8';
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlStr);

        return $dom->saveXML();
    }

    /**
     * @param string $taxNumber
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function getFirmDataFromTaxNumber(string $taxNumber)
    {
        $command = 'GetBisnodeResponse';
        $this->makeXMLOutOBJ($command);

        $this->addXMLUserAuthNode();
        $this->XMLAppendChild('taxnbr', $taxNumber);
        $this->addXMLServiceCdNode();

        if (false == $SOAPClient = $this->initSoapClient()) {
            throw new \Exception('SOAP init error');
        }

        $XMLStr = mb_substr($this->XMLOBJOut->saveXML(), (mb_strpos($this->XMLOBJOut->saveXML(), '>') + 1));

        $xmlvar = new \SoapVar($XMLStr, XSD_ANYXML);

        $response = $SOAPClient->$command($xmlvar);

        if (!$SOAPClient->__getLastResponse()) {
            throw new \Exception('SOAP call error');
        }

        $resObj = $response->GetBisnodeResponseResult;
        if (isset($resObj->Errors)) {
            throw new \Exception('BisNode error: '.var_export($resObj->Errors));
        }

        return $resObj;
    }

    /**
     * @return bool|\SoapClient
     */
    private function initSoapClient()
    {
        try {
            $this->SOAPClient = new \SoapClient('https://www.cegminosites.hu/webservice/bs/BisnodeService.svc?singleWsdl',
                [
                    'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
                    'style' => SOAP_RPC,
                    'use' => SOAP_ENCODED,
                    'soap_version' => SOAP_1_1, // depends of your version
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'connection_timeout' => 15,
                    'trace' => true,
                    'encoding' => 'UTF-8',
                    'exceptions' => false,
                    'stream_context' => stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ],
                    ]),
                ]
            );
        } catch (\Exception $e) {
            return false;
        }

        return $this->SOAPClient;
    }

    /**
     * @param $rootElementName
     * @param string $nodeNamespacePrefix
     *
     * @return \DOMNode
     */
    private function makeXMLOutOBJ($rootElementName, $nodeNamespacePrefix = 'ns1:')
    {
        $this->XMLNodeNamespacePrefix = $nodeNamespacePrefix;
        $doc = new \DOMDocument('1.0', 'utf-8');
        $node = $doc->createElement($nodeNamespacePrefix."$rootElementName");
        $rootElement = $doc->appendChild($node);
        $this->XMLOBJOut = $doc;
        $this->XMLRootNode = $rootElement;

        return $rootElement;
    }

    /**
     * @param $name
     * @param null $value
     * @param null $node
     *
     * @return \DOMElement
     */
    private function XMLAppendChild($name, $value = null, $node = null)
    {
        if (null === $node) {
            $node = $this->XMLRootNode;
        }
        if (null !== $value) {
            $newNode = $this->XMLOBJOut->createElement($this->XMLNodeNamespacePrefix.$name, $value);
        } else {
            $newNode = $this->XMLOBJOut->createElement($this->XMLNodeNamespacePrefix.$name);
        }
        $node->appendChild($newNode);

        return $newNode;
    }

    /**
     * @param null $mainElement
     */
    private function addXMLUserAuthNode($mainElement = null)
    {
        $this->XMLAppendChild('username', $this->username, $mainElement);
        $this->XMLAppendChild('password', $this->password, $mainElement);
    }

    /**
     * @param null $mainElement
     */
    private function addXMLServiceCdNode($mainElement = null)
    {
        $this->XMLAppendChild('serviceCd', $this->serviceCd, $mainElement);
    }
}
