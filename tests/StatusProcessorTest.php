<?php

use BwtTeam\LaravelAPI\Processors\StatusProcessor;
use BwtTeam\LaravelAPI\Response\ApiResponse;
use Tests\TestCase;

class StatusProcessorTest  extends TestCase
{
    /** @var Symfony\Component\HttpFoundation\JsonResponse */
    protected $response;

    /** @var BwtTeam\LaravelAPI\Processors\BaseProcessor */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->response = new ApiResponse();
        $this->processor = new StatusProcessor($this->response);
    }

    public function testInformationalData()
    {
        $data = null;
        $this->response->setStatusCode(122);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_FAILURE, $this->processor->getData());
    }
    
    public function testSuccessData()
    {
        $data = null;
        $this->response->setStatusCode(215);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_SUCCESS, $this->processor->getData());
    }

    public function testOkData()
    {
        $data = null;
        $this->response->setStatusCode(200);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_SUCCESS, $this->processor->getData());
    }

    public function testRedirectionData()
    {
        $data = null;
        $this->response->setStatusCode(302);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_FAILURE, $this->processor->getData());
    }

    public function testClientErrorData()
    {
        $data = null;
        $this->response->setStatusCode(402);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_FAILURE, $this->processor->getData());
    }

    public function testServerErrorData()
    {
        $data = null;
        $this->response->setStatusCode(500);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_FAILURE, $this->processor->getData());
    }

    public function testForbiddenData()
    {
        $data = null;
        $this->response->setStatusCode(403);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_FAILURE, $this->processor->getData());
    }

    public function testNotFoundData()
    {
        $data = null;
        $this->response->setStatusCode(404);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_FAILURE, $this->processor->getData());
    }

    public function testEmptyData()
    {
        $data = null;
        $this->response->setStatusCode(204);
        $this->processor->handle($data);
        $this->assertSame(StatusProcessor::STATUS_SUCCESS, $this->processor->getData());
    }
}
