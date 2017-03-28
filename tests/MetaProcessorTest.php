<?php

use BwtTeam\LaravelAPI\Processors\MetaProcessor;
use BwtTeam\LaravelAPI\Response\ApiResponse;
use Tests\TestCase;

class MetaProcessorTest extends TestCase
{
    /** @var Symfony\Component\HttpFoundation\JsonResponse */
    protected $response;

    /** @var BwtTeam\LaravelAPI\Processors\BaseProcessor */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->response = new ApiResponse();
        $this->processor = new MetaProcessor($this->response);
    }

    public function testInformationalData()
    {
        $data = null;
        $this->response->setStatusCode(122);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(122, $processorData['http_code']);
    }

    public function testSuccessData()
    {
        $data = null;
        $this->response->setStatusCode(215);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(215, $processorData['http_code']);
    }

    public function testOkData()
    {
        $data = null;
        $this->response->setStatusCode(200);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(200, $processorData['http_code']);
    }

    public function testRedirectionData()
    {
        $data = null;
        $this->response->setStatusCode(302);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(302, $processorData['http_code']);
    }

    public function testClientErrorData()
    {
        $data = null;
        $this->response->setStatusCode(402);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(402, $processorData['http_code']);
    }

    public function testServerErrorData()
    {
        $data = null;
        $this->response->setStatusCode(500);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(500, $processorData['http_code']);
    }

    public function testForbiddenData()
    {
        $data = null;
        $this->response->setStatusCode(403);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(403, $processorData['http_code']);
    }

    public function testNotFoundData()
    {
        $data = null;
        $this->response->setStatusCode(404);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(404, $processorData['http_code']);
    }

    public function testEmptyData()
    {
        $data = null;
        $this->response->setStatusCode(204);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(204, $processorData['http_code']);
    }

    public function testPaginationData()
    {
        $data = new \Illuminate\Pagination\LengthAwarePaginator([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 10, 2);
        $this->response->setStatusCode(200);
        $this->processor->handle($data);
        $processorData = $this->processor->getData();

        $this->assertInternalType('array', $processorData);
        $this->assertArrayHasKey('http_code', $processorData);
        $this->assertSame(200, $processorData['http_code']);

        $this->assertArrayHasKey('pagination', $processorData);
        $this->assertInternalType('array', $processorData['pagination']);

        $this->assertArrayHasKey('total', $processorData['pagination']);
        $this->assertInternalType('integer', $processorData['pagination']['total']);

        $this->assertArrayHasKey('limit', $processorData['pagination']);
        $this->assertInternalType('integer', $processorData['pagination']['limit']);

        $this->assertArrayHasKey('last_page', $processorData['pagination']);
        $this->assertInternalType('integer', $processorData['pagination']['last_page']);

        $this->assertArrayHasKey('current_page', $processorData['pagination']);
        $this->assertInternalType('integer', $processorData['pagination']['current_page']);
    }
}
