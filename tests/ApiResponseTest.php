<?php

use BwtTeam\LaravelAPI\Processors\DataProcessor;
use BwtTeam\LaravelAPI\Processors\MetaProcessor;
use BwtTeam\LaravelAPI\Processors\StatusProcessor;
use BwtTeam\LaravelAPI\Response\ApiResponse;

class ApiResponseTest extends \TestCase
{
    /** @var array|string */
    protected $format = [];

    /** @var BwtTeam\LaravelAPI\Response\ApiResponse */
    protected $response;

    protected function setUp()
    {
        parent::setUp();

        $this->format = [
            'status' => StatusProcessor::getSignature(),
            'meta' => MetaProcessor::getSignature(),
            'response' => DataProcessor::getSignature()
        ];
        $this->response = new ApiResponse();
        $this->response->setFormat($this->format);
    }

    protected function checkFormat($response, $paginator = null)
    {
        $this->assertInternalType('string', $response);
        $response = $this->decodeContent($response);

        $this->assertInternalType('array', $response);

        $this->assertArrayHasKey('status', $response);
        $this->checkStatus($response['status']);

        $this->assertArrayHasKey('meta', $response);
        $this->checkMeta($response['meta'], $paginator);

        $this->assertArrayHasKey('response', $response);
    }

    protected function checkStatus($statusData)
    {
        $this->assertInternalType('integer', $statusData);
    }

    protected function checkMeta($metaData, $paginator = null)
    {
        $this->assertInternalType('array', $metaData);

        $this->assertArrayHasKey('http_code', $metaData);
        $this->assertInternalType('integer', $metaData['http_code']);

        if (400 <= $metaData['http_code'] && $metaData['http_code'] < 600) {
            $this->assertArrayHasKey('message', $metaData);
            $this->assertInternalType('string', $metaData['message']);

            $this->assertArrayHasKey('error_type', $metaData);
            $this->assertInternalType('string', $metaData['error_type']);

            if ($metaData['http_code'] == 422) {
                $this->assertArrayHasKey('errors', $metaData);
                $this->assertInternalType('array', $metaData['errors']);
            }
        }

        if ($paginator) {
            $this->checkMetaPagination($metaData, $paginator);
        }
    }

    protected function checkMetaPagination($metaData, $paginator = null)
    {
        $this->assertArrayHasKey('pagination', $metaData);
        $this->assertInternalType('array', $metaData['pagination']);

        $this->assertArrayHasKey('limit', $metaData['pagination']);
        $this->assertInternalType('integer', $metaData['pagination']['limit']);

        $this->assertArrayHasKey('current_page', $metaData['pagination']);
        $this->assertInternalType('integer', $metaData['pagination']['current_page']);

        if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $this->assertArrayHasKey('total', $metaData['pagination']);
            $this->assertInternalType('integer', $metaData['pagination']['total']);

            $this->assertArrayHasKey('last_page', $metaData['pagination']);
            $this->assertInternalType('integer', $metaData['pagination']['last_page']);
        }
    }

    protected function decodeContent($content)
    {
        return json_decode($content, true);
    }

    public function testEmptyData()
    {
        $data = null;
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testEmptyStringData()
    {
        $data = '';
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testStringData()
    {
        $data = 'test';
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testNumberStringData()
    {
        $data = '1';
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testZeroNumberData()
    {
        $data = 0;
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testSimpleNumberData()
    {
        $data = 1;
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testComplicatedNumberData()
    {
        $data = 12345678901234;
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testEmptyArrayData()
    {
        $data = [];
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testSimpleArrayData()
    {
        $data = [1, 'test'];
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testComplicatedArrayData()
    {
        $data = [1, [1, 'test'], [[1, 'test'], 2]];
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data, $responseData['response']);
    }

    public function testSimpleObject()
    {
        $data = new stdClass();
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame((array)$data, $responseData['response']);
    }

    public function testComplicatedObject()
    {
        $data = new stdClass();
        $data->test = 'test';
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response);

        $responseData = $this->decodeContent($response);
        $this->assertSame((array)$data, $responseData['response']);
    }

    public function testPaginatorObject()
    {
        $data = new \Illuminate\Pagination\Paginator([1, 2, 3, 4], 2, 1);
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response, \Illuminate\Pagination\Paginator::class);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data->getCollection()->toArray(), $responseData['response']);
    }

    public function testCollectionWithPaginatorObject()
    {
        $data = new \Illuminate\Pagination\Paginator(collect([1, 2, 3, 4]), 2, 1);
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response, \Illuminate\Pagination\Paginator::class);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data->getCollection()->toArray(), $responseData['response']);
    }

    public function testLengthAwarePaginatorObject()
    {
        $data =  new \Illuminate\Pagination\LengthAwarePaginator([1, 2], 4, 2, 1);
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response, \Illuminate\Pagination\LengthAwarePaginator::class);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data->getCollection()->toArray(), $responseData['response']);
    }

    public function testCollectionWithLengthAwarePaginatorObject()
    {
        $data = new \Illuminate\Pagination\LengthAwarePaginator(collect([1, 2]), 4, 2, 1);
        $this->response->setData($data);
        $response = $this->response->getContent();
        $this->checkFormat($response, \Illuminate\Pagination\LengthAwarePaginator::class);

        $responseData = $this->decodeContent($response);
        $this->assertSame($data->getCollection()->toArray(), $responseData['response']);
    }
}
