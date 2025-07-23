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

namespace Common\SzamlazzhuBundle\Manager;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;

class InvoiceManager
{
    /**
     * Szamlazz.hu account (username, password).
     *
     * @var array
     */
    protected $credentials;

    /**
     * PDF path.
     *
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $e_invoice;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $billing_data = [];

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var string
     */
    protected $payment_method = 'utánvétel';

    /**
     * @var string
     */
    protected $currency = 'HUF';

    /**
     * @var string
     */
    protected $language = 'hu';

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var bool
     */
    protected $proforma = false;

    /**
     * @var string
     */
    protected $pdf;

    /**
     * @var array|null
     */
    protected $response;

    /**
     * InvoiceManager constructor.
     *
     * @param $username
     * @param $password
     * @param $e_invoice
     * @param Filesystem $filesystem
     */
    public function __construct($username, $password, $e_invoice, Filesystem $filesystem)
    {
        $this->credentials = ['username' => $username, 'password' => $password];
        $this->e_invoice = $e_invoice;
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $billing_data
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addCustomer(array $billing_data)
    {
        if (!isset($billing_data['name'])) {
            throw new \Exception('Incorrect or invalid billing data: required name');
        }

        if (!isset($billing_data['post_code'])) {
            throw new \Exception('Incorrect or invalid billing data: required postcode');
        }

        if (!isset($billing_data['city'])) {
            throw new \Exception('Incorrect or invalid billing data: required city');
        }

        if (!isset($billing_data['address'])) {
            throw new \Exception('Incorrect or invalid billing data: required address');
        }

        if (!isset($billing_data['tax_number'])) {
            throw new \Exception('Incorrect or invalid billing data: required tax_number');
        }

        if (!isset($billing_data['order_number'])) {
            throw new \Exception('Incorrect or invalid billing data: required tax_number');
        }

        if ($this->e_invoice && !isset($billing_data['email'])) {
            throw new \Exception('Incorrect or invalid billing data: required email address');
        }

        $this->billing_data = $billing_data;

        return $this;
    }

    /**
     * @param array $item
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addItem(array $item)
    {
        if (!isset($item['name'])) {
            throw new \Exception('Incorrent or incomplete item value');
        }

        if (!isset($item['quantity'])) {
            $item['quantity'] = 1;
        }

        if (!isset($item['quantity_unit'])) {
            $item['quantity_unit'] = 'db';
        }

        if (!isset($item['net_unit_price'])) {
            throw new \Exception('Incorrent or incomplete item value');
        }

        if (!isset($item['vat_rate'])) {
            $data['quantity_unit'] = 27;
        }

        if (!isset($item['net_value'])) {
            throw new \Exception('Incorrent or incomplete item value');
        }

        if (!isset($item['vat_value'])) {
            throw new \Exception('Incorrent or incomplete item value');
        }

        if (!isset($item['gross_value'])) {
            throw new \Exception('Incorrent or incomplete item value');
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function paymentData(array $data)
    {
        if (isset($data['method']) && !empty($data['method'])) {
            $this->payment_method = $data['method'];
        }

        if (isset($data['currency']) && !empty($data['currency'])) {
            $this->currency = $data['currency'];
        }

        if (isset($data['language']) && !empty($data['language'])) {
            $this->language = $data['language'];
        }

        if (isset($data['prefix']) && !empty($data['prefix'])) {
            $this->prefix = $data['prefix'];
        }

        if (isset($data['proforma'])) {
            $this->proforma = $data['proforma'];
        }

        return $this;
    }

    /**
     * @param $headers
     * @param $content
     *
     * @throws \Exception
     */
    public function parseResponse($headers, $content)
    {
        if (
            (isset($headers['szlahu_error']) && isset($headers['szlahu_error_code'])) &&
            (!empty($headers['szlahu_error']) && !empty($headers['szlahu_error_code']))
        ) {
            throw new \Exception(
                $headers['szlahu_error_code'][0]
                  .' - '.
                urldecode($headers['szlahu_error'][0])
            );
        }

        $xml = simplexml_load_string($content);

        $this->response = [
            'invoice_number' => $headers['szlahu_szamlaszam'][0],
            'net_price' => $headers['szlahu_nettovegosszeg'][0],
            'gross_price' => $headers['szlahu_bruttovegosszeg'][0],
        ];

        $this->pdf = $xml->pdf[0]->__toString();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     *
     * @return $this
     */
    public function create()
    {
        $xml = $this->createXML();

        $client = new Client();

        $xmlString = preg_replace('/<(\\w+)\\/>/', '<\\1></\\1>', $xml->asXML());
        $xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8"?>', $xmlString);

        $response = $client->request('POST', 'https://www.szamlazz.hu/szamla/', [
            'header' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'verify' => false,
            'cookie' => new \GuzzleHttp\Cookie\CookieJar(),
            'form_params' => [
                'action-xmlagentxmlfile' => $xmlString,
            ],
        ]);

        $this->parseResponse($response->getHeaders(), $response->getBody()->getContents());

        return $this;
    }

    /**
     * @param $path
     *
     * @return $this
     */
    public function save($path)
    {
        $dir = dirname($path);
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir);
        }

        $this->filesystem->dumpFile($path, base64_decode($this->pdf));

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function createXML()
    {
        $xml = new \SimpleXMLElement('<xmlszamla/>');
        $deadline = date('Y-m-d');

        if ($this->proforma) {
            $deadline = date('Y-m-d', strtotime('+ 8 days'));
        }

        $parent = $xml;
        $parent->addAttribute('xmlns', 'http://www.szamlazz.hu/xmlszamla');
        $parent->addAttribute('xsi:schemaLocation', 'http://www.szamlazz.hu/xmlszamla/xmlszamla.xsd', 'http://www.w3.org/2001/XMLSchema-instance');

        $settings = $parent->addChild('beallitasok');
        $settings->addChild('felhasznalo', $this->credentials['username']);
        $settings->addChild('jelszo', $this->credentials['password']);
        $settings->addChild('eszamla', $this->e_invoice ? 'true' : 'false');
        $settings->addChild('szamlaLetoltes', 'true');
        $settings->addChild('valaszVerzio', 2);

        $header = $parent->addChild('fejlec');
        $header->addChild('keltDatum', date('Y-m-d'));
        $header->addChild('teljesitesDatum', date('Y-m-d'));
        $header->addChild('fizetesiHataridoDatum', $deadline);
        $header->addChild('fizmod', $this->payment_method);
        $header->addChild('penznem', strtoupper($this->currency));
        $header->addChild('szamlaNyelve', $this->language);
        $header->addChild('megjegyzes', '');
        $header->addChild('arfolyamBank', '');
        $header->addChild('arfolyam', 0.0);
        $header->addChild('rendelesSzam', $this->billing_data['order_number']);
        $header->addChild('elolegszamla', 'false');
        $header->addChild('vegszamla', 'false');
        $header->addChild('dijbekero', $this->proforma ? 'true' : 'false');

        $header->addChild('szamlaszamElotag', (!empty($this->prefix)) ? $this->prefix : null);

        $seller = $parent->addChild('elado');
        $seller->addChild('bank', '');
        $seller->addChild('bankszamlaszam', '');
        $seller->addChild('emailReplyto');
        $seller->addChild('emailTargy', '');
        $seller->addChild('emailSzoveg', '');

        $customer = $parent->addChild('vevo');
        $customer->addChild('nev', htmlspecialchars($this->billing_data['name'], ENT_QUOTES, 'utf-8'));
        $customer->addChild('irsz', $this->billing_data['post_code']);
        $customer->addChild('telepules', $this->billing_data['city']);
        $customer->addChild('cim', $this->billing_data['address']);
        $customer->addChild('email', $this->billing_data['email'] ?: '');
        $customer->addChild('sendEmail', 'true');
        $customer->addChild('adoszam', $this->billing_data['tax_number']);
        $customer->addChild('telefonszam', '');
        $customer->addChild('megjegyzes', '');

        $items = $parent->addChild('tetelek');

        foreach ($this->items as $product) {
            $item = $items->addChild('tetel');
            $item->addChild('megnevezes', htmlspecialchars($product['name'], ENT_QUOTES, 'utf-8'));
            $item->addChild('mennyiseg', $product['quantity']);
            $item->addChild('mennyisegiEgyseg', $product['quantity_unit']);
            $item->addChild('nettoEgysegar', $product['net_unit_price']);
            $item->addChild('afakulcs', $product['vat_rate']);
            $item->addChild('nettoErtek', $product['net_value']);
            $item->addChild('afaErtek', $product['vat_value']);
            $item->addChild('bruttoErtek', $product['gross_value']);
            $item->addChild('megjegyzes', '');
        }

        return $xml;
    }
}
