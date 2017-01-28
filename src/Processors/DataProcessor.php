<?php

namespace BwtTeam\LaravelAPI\Processors;

use Illuminate\Contracts\Support\Arrayable;

class DataProcessor extends BaseProcessor
{
    /**
     * Get signature of api processor.
     *
     * @return string
     */
    public static function getSignature()
    {
        return ':data';
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param mixed $data Data for processing
     *
     * @return void
     */
    public function handle($data)
    {
        if ($this->response->isClientError() || $this->response->isServerError()) {
            $data = [];
        } else if (is_array($data) && current($data) instanceof \Illuminate\Pagination\AbstractPaginator) {
            $data[key($data)] = $this->handlePagination(current($data));
        } else if ($data instanceof \Illuminate\Pagination\AbstractPaginator) {
            $data = $this->handlePagination($data);
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->setData($data);
    }

    /**
     * Handle pagination data
     *
     * @param \Illuminate\Pagination\AbstractPaginator $paginator
     *
     * @return array
     */
    protected function handlePagination(\Illuminate\Pagination\AbstractPaginator $paginator)
    {
        return $paginator->getCollection()->toArray();
    }
}