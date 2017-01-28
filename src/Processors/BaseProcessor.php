<?php

namespace BwtTeam\LaravelAPI\Processors;


abstract class BaseProcessor
{
    /**
     * Data after processing.
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * The response instance.
     *
     * @var \BwtTeam\LaravelAPI\Response\ApiResponse
     */
    protected $response;

    /**
     * Create a new processor instance.
     *
     * @param  \BwtTeam\LaravelAPI\Response\ApiResponse $response
     *
     * @return void
     */
    public function __construct(&$response)
    {
        $this->response = $response;
    }

    /**
     * Get signature of api processor.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function getSignature()
    {
        throw new \RuntimeException('Processor does not implement getSignature method.');
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param mixed $data Data for processing
     *
     * @return void
     */
    abstract public function handle($data);

    /**
     *  Get the data from processor.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data to be sent by processor.
     *
     * @param array $data
     */
    public function setData($data = [])
    {
        $this->data = $data;
    }
}