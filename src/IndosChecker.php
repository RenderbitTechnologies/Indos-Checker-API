<?php

namespace Renderbit\IndosCheckerApi;

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class IndosChecker
{
    protected static $config = [
        'request_uri' => 'http://220.156.189.33/esamudraUI/checkerajaxservlet',
    ];

    protected static function request($no, $dob)
    {
        $client = new Client();
        $response = $client->post(static::$config['request_uri'], [
            'form_params' => [
                'txtNo' => $no,
                'dob' => $dob,
                'processId' => 'PPIndosCheck',
                'searchType' => 'Indos'
            ]
        ]);

        return $response->getBody();
    }

    protected static function parse($response)
    {
        $dom = new Crawler($response);
        $rows = $dom->filter('tr');
        $data = [];

        foreach($rows as $row) {
            $row = new Crawler($row);
            if($row->children('td')->count() === 2) {
                $cols = $row->children('td');
                $col1 = $cols->eq(0);
                $col2 = $cols->eq(1);
                $col2->filter('span')->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
                $col1_text = $col1->text();
                $col2_text = $col2->text();
                $data[$col1_text] = $col2_text;
            }
        }

        return $data;
    }

    public static function getData($no, $dob)
    {
        $response = static::request($no, $dob);
        $data = static::parse($response);

        return $data;
    }

    public static function checkValid($no, $dob)
    {
        $data = static::getData($no, $dob);

        return count($data) > 0;
    }
}
