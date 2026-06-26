<?php

namespace RenderbitTechnologies\IndosCheckerApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class IndosChecker
{
    // The DGS eSamudra server listens only on port 80 — port 443 is closed.
    // HTTPS cannot be used until the operator enables TLS on the server side.
    private const DEFAULT_ENDPOINT = 'http://220.156.189.33/esamudraUI/checkerajaxservlet';

    private Client $client;
    private string $endpoint;

    public function __construct(?Client $client = null, ?string $endpoint = null)
    {
        $this->client   = $client ?? new Client();
        $this->endpoint = $endpoint ?? self::DEFAULT_ENDPOINT;
    }

    private function validate(string $no, string $dob): void
    {
        if (trim($no) === '') {
            throw new \InvalidArgumentException('INDOS number cannot be empty.');
        }

        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dob)) {
            throw new \InvalidArgumentException(
                'Date of birth must be in DD/MM/YYYY format (e.g. 14/08/1963).'
            );
        }
    }

    private function request(string $no, string $dob): string
    {
        try {
            $response = $this->client->post($this->endpoint, [
                'form_params' => [
                    'txtNo'      => $no,
                    'dob'        => $dob,
                    'processId'  => 'PPIndosCheck',
                    'searchType' => 'Indos',
                ],
            ]);

            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            throw new IndosCheckerException(
                'Failed to reach INDOS API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function parse(string $response): array
    {
        $dom  = new Crawler($response);
        $data = [];

        foreach ($dom->filter('tr') as $row) {
            $row = new Crawler($row);

            if ($row->children('td')->count() !== 2) {
                continue;
            }

            $cols = $row->children('td');
            $col1 = $cols->eq(0);
            $col2 = $cols->eq(1);

            $col2->filter('span')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            $data[trim($col1->text())] = trim($col2->text());
        }

        return $data;
    }

    public function getData(string $no, string $dob): array
    {
        $this->validate($no, $dob);

        return $this->parse($this->request($no, $dob));
    }

    public function checkValid(string $no, string $dob): bool
    {
        return isset($this->getData($no, $dob)['INDoS No.']);
    }
}
