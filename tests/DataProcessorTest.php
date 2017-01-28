<?php

use BwtTeam\LaravelAPI\Processors\DataProcessor;
use BwtTeam\LaravelAPI\Response\ApiResponse;

class DataProcessorTest extends \TestCase
{
    /** @var Symfony\Component\HttpFoundation\JsonResponse */
    protected $response;

    /** @var BwtTeam\LaravelAPI\Processors\BaseProcessor */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->response = new ApiResponse();
        $this->processor = new DataProcessor($this->response);
    }

    public function testEmptyData()
    {
        $data = null;
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testEmptyStringData()
    {
        $data = '';
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testStringData()
    {
        $data = 'test';
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());

    }

    public function testNumberStringData()
    {
        $data = '1';
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testZeroNumberData()
    {
        $data = 0;
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());

    }

    public function testSimpleNumberData()
    {
        $data = 1;
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testComplicatedNumberData()
    {
        $data = 12345678901234;
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testEmptyArrayData()
    {
        $data = [];
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testSimpleArrayData()
    {
        $data = [1, 'test'];
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testComplicatedArrayData()
    {
        $data = [1, [1, 'test'], [[1, 'test'], 2]];
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testSimpleObject()
    {
        $data = new stdClass();
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testComplicatedObject()
    {
        $data = new stdClass();
        $data->test = 'test';
        $this->processor->handle($data);
        $this->assertSame($data, $this->processor->getData());
    }

    public function testPaginatorObject()
    {
        $data = new \Illuminate\Pagination\Paginator([1, 2, 3, 4], 2, 1);
        $this->processor->handle($data);
        $this->assertSame([1,2], $this->processor->getData());
    }

    public function testCollectionWithPaginatorObject()
    {
        $data = new \Illuminate\Pagination\Paginator(collect([1, 2, 3, 4]), 2, 1);
        $this->processor->handle($data);
        $this->assertSame([1,2], $this->processor->getData());
    }

    public function testLengthAwarePaginatorObject()
    {
        $data = new \Illuminate\Pagination\LengthAwarePaginator([1, 2], 4, 2, 1);
        $this->processor->handle($data);
        $this->assertSame([1,2], $this->processor->getData());
    }

    public function testCollectionWithLengthAwarePaginatorObject()
    {
        $data = new \Illuminate\Pagination\LengthAwarePaginator(collect([1, 2]), 4, 2, 1);
        $this->processor->handle($data);
        $this->assertSame([1,2], $this->processor->getData());
    }
}
