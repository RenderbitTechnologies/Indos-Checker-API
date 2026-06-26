<?php

namespace Renderbit\IndosCheckerApi\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Renderbit\IndosCheckerApi\IndosChecker;
use Renderbit\IndosCheckerApi\IndosCheckerException;

class IndosCheckerTest extends TestCase
{
    // Mirrors the 10-field response returned by the live API for 05LL0262 / 14/08/1963
    private const VALID_RECORD_HTML = <<<HTML
        <html><body><table>
            <tr><td>Name</td><td>YADAV SANJEEV</td></tr>
            <tr><td>Date of Birth</td><td>14-AUG-1963</td></tr>
            <tr><td>INDoS No.</td><td>05LL0262</td></tr>
            <tr><td>Passport No.</td><td>M2069200</td></tr>
            <tr><td>Passport Issue Date</td><td>15-SEP-2014</td></tr>
            <tr><td>Passport Valid To</td><td>14-SEP-2024</td></tr>
            <tr><td>CDC No.</td><td>MUM 133201</td></tr>
            <tr><td>CDC Issue Date</td><td>22-MAY-2015</td></tr>
            <tr><td>CDC Valid To</td><td>21-MAY-2025</td></tr>
            <tr><td>CDC Issue Place</td><td>Mumbai</td></tr>
        </table></body></html>
        HTML;

    // Simulates the "no record found" page — table present but no 2-column data rows
    private const EMPTY_RESPONSE_HTML = <<<HTML
        <html><body><table>
            <tr><th colspan="2">No record found</th></tr>
        </table></body></html>
        HTML;

    // ── helpers ──────────────────────────────────────────────────────────────

    private function makeChecker(string $responseBody, int $status = 200): IndosChecker
    {
        $mock    = new MockHandler([new Response($status, [], $responseBody)]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        return new IndosChecker($client);
    }

    // ── Group 1: Input Validation ─────────────────────────────────────────

    public function testThrowsOnEmptyIndosNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INDOS number cannot be empty.');

        // No network call needed — validation fires before request()
        $checker = new IndosChecker();
        $checker->getData('', '14/08/1963');
    }

    public function testThrowsOnWhitespaceIndosNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $checker = new IndosChecker();
        $checker->getData('   ', '14/08/1963');
    }

    public function testThrowsOnWrongDateFormat_YYYYMMDD(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Date of birth must be in DD/MM/YYYY format');

        $checker = new IndosChecker();
        $checker->getData('05LL0262', '1963-08-14');
    }

    public function testThrowsOnWrongDateFormat_noSlashes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $checker = new IndosChecker();
        $checker->getData('05LL0262', '14081963');
    }

    public function testThrowsOnWrongDateFormat_singleDigitDay(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $checker = new IndosChecker();
        $checker->getData('05LL0262', '1/8/1963');
    }

    public function testDoesNotThrowOnValidInput(): void
    {
        $checker = $this->makeChecker(self::VALID_RECORD_HTML);

        // No exception expected; just verify it returns an array
        $data = $checker->getData('05LL0262', '14/08/1963');
        $this->assertIsArray($data);
    }

    // ── Group 2: HTML Parsing ─────────────────────────────────────────────

    public function testGetDataReturnsAllFieldsFromValidHtml(): void
    {
        $checker = $this->makeChecker(self::VALID_RECORD_HTML);
        $data    = $checker->getData('05LL0262', '14/08/1963');

        $this->assertSame('YADAV SANJEEV', $data['Name']);
        $this->assertSame('14-AUG-1963',  $data['Date of Birth']);
        $this->assertSame('05LL0262',     $data['INDoS No.']);
        $this->assertSame('M2069200',     $data['Passport No.']);
        $this->assertSame('MUM 133201',   $data['CDC No.']);
        $this->assertSame('Mumbai',       $data['CDC Issue Place']);
        $this->assertCount(10, $data);
    }

    public function testGetDataStripsSpansFromValues(): void
    {
        $html = <<<HTML
            <html><body><table>
                <tr><td>INDoS No.</td><td>05LL0262<span class="hidden">extra</span></td></tr>
            </table></body></html>
            HTML;

        $checker = $this->makeChecker($html);
        $data    = $checker->getData('05LL0262', '14/08/1963');

        $this->assertSame('05LL0262', $data['INDoS No.']);
    }

    public function testGetDataSkipsRowsWithWrongColumnCount(): void
    {
        $html = <<<HTML
            <html><body><table>
                <tr><td>only one col</td></tr>
                <tr><td>INDoS No.</td><td>05LL0262</td></tr>
                <tr><td>col1</td><td>col2</td><td>col3</td></tr>
            </table></body></html>
            HTML;

        $checker = $this->makeChecker($html);
        $data    = $checker->getData('05LL0262', '14/08/1963');

        $this->assertCount(1, $data);
        $this->assertArrayHasKey('INDoS No.', $data);
        $this->assertArrayNotHasKey('only one col', $data);
        $this->assertArrayNotHasKey('col1', $data);
    }

    public function testGetDataTrimsWhitespaceFromKeysAndValues(): void
    {
        $html = <<<HTML
            <html><body><table>
                <tr><td>  INDoS No.  </td><td>  05LL0262  </td></tr>
            </table></body></html>
            HTML;

        $checker = $this->makeChecker($html);
        $data    = $checker->getData('05LL0262', '14/08/1963');

        $this->assertArrayHasKey('INDoS No.', $data);
        $this->assertSame('05LL0262', $data['INDoS No.']);
    }

    public function testGetDataReturnsEmptyArrayForHtmlWithNoMatchingRows(): void
    {
        $checker = $this->makeChecker(self::EMPTY_RESPONSE_HTML);
        $data    = $checker->getData('XXXXXXXX', '01/01/2000');

        $this->assertSame([], $data);
    }

    // ── Group 3: checkValid() Logic ───────────────────────────────────────

    public function testCheckValidReturnsTrueWhenRecordFound(): void
    {
        $checker = $this->makeChecker(self::VALID_RECORD_HTML);
        $this->assertTrue($checker->checkValid('05LL0262', '14/08/1963'));
    }

    public function testCheckValidReturnsFalseWhenRecordNotFound(): void
    {
        $checker = $this->makeChecker(self::EMPTY_RESPONSE_HTML);
        $this->assertFalse($checker->checkValid('XXXXXXXX', '01/01/2000'));
    }

    public function testCheckValidReturnsFalseForHtmlWithRowsButNoIndosKey(): void
    {
        // A page that has 2-column rows but none keyed 'INDoS No.'
        $html = <<<HTML
            <html><body><table>
                <tr><td>Error</td><td>Record not found</td></tr>
            </table></body></html>
            HTML;

        $checker = $this->makeChecker($html);
        $this->assertFalse($checker->checkValid('XXXXXXXX', '01/01/2000'));
    }

    // ── Group 4: Exception Wrapping ───────────────────────────────────────

    public function testWrapsGuzzleConnectExceptionInIndosCheckerException(): void
    {
        $this->expectException(IndosCheckerException::class);

        $request = new Request('POST', 'http://example.com');
        $mock    = new MockHandler([new ConnectException('Connection refused', $request)]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $checker = new IndosChecker($client);

        $checker->getData('05LL0262', '14/08/1963');
    }

    public function testWrapsGuzzleServerErrorInIndosCheckerException(): void
    {
        $this->expectException(IndosCheckerException::class);

        // http_errors=true (Guzzle default) converts 5xx into ServerException
        $mock    = new MockHandler([new Response(500)]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $checker = new IndosChecker($client);

        $checker->getData('05LL0262', '14/08/1963');
    }

    public function testPreviousExceptionIsChained(): void
    {
        $request = new Request('POST', 'http://example.com');
        $guzzleEx = new ConnectException('Connection refused', $request);

        $mock    = new MockHandler([$guzzleEx]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $checker = new IndosChecker($client);

        try {
            $checker->getData('05LL0262', '14/08/1963');
            $this->fail('Expected IndosCheckerException was not thrown.');
        } catch (IndosCheckerException $e) {
            $this->assertSame($guzzleEx, $e->getPrevious());
        }
    }

    public function testExceptionMessageContainsContext(): void
    {
        $request = new Request('POST', 'http://example.com');
        $mock    = new MockHandler([new ConnectException('timed out', $request)]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $checker = new IndosChecker($client);

        try {
            $checker->getData('05LL0262', '14/08/1963');
            $this->fail('Expected IndosCheckerException was not thrown.');
        } catch (IndosCheckerException $e) {
            $this->assertStringStartsWith('Failed to reach INDOS API:', $e->getMessage());
        }
    }

    // ── Group 5: Constructor / DI ─────────────────────────────────────────

    public function testCustomEndpointIsUsedInRequest(): void
    {
        $customEndpoint = 'http://staging.example.com/indos';
        $history        = [];

        $mock    = new MockHandler([new Response(200, [], self::VALID_RECORD_HTML)]);
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($history));
        $client  = new Client(['handler' => $handler]);
        $checker = new IndosChecker($client, $customEndpoint);

        $checker->getData('05LL0262', '14/08/1963');

        $this->assertCount(1, $history);
        $this->assertSame($customEndpoint, (string) $history[0]['request']->getUri());
    }

    public function testDefaultClientIsCreatedWhenNullPassed(): void
    {
        // Just verify construction doesn't throw — no network call made
        $checker = new IndosChecker();
        $this->assertInstanceOf(IndosChecker::class, $checker);
    }
}
