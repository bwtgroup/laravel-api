<?php

namespace BwtTeam\LaravelAPI\Processors;


use Illuminate\Validation\ValidationException;

class MetaProcessor extends BaseProcessor
{
    /**
     * Get signature of api processor.
     *
     * @return string
     */
    public static function getSignature()
    {
        return ':meta';
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
        $processedData = [
            'http_code' => $this->response->getStatusCode()
        ];

        if ($this->response->isClientError() || $this->response->isServerError()) {
            $processedData["message"] = $this->response->getMessage();
            $processedData["error_type"] = $this->response->getStatusType();
            if ($data instanceof ValidationException) {
                $processedData['errors'] = $data->validator->errors()->getMessages();
            }
        } else {
            $dataBody = is_array($data) ? current($data) : $data;
            if ($dataBody instanceof \Illuminate\Pagination\AbstractPaginator) {
                $processedData['pagination'] = $this->handlePagination($dataBody);
            }
        }


        $this->setData($processedData);
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
        $paginatorData = [
            'limit' => (int)$paginator->perPage(),
            'current_page' => (int)$paginator->currentPage(),
        ];
        if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $paginatorData['total'] = (int)$paginator->total();
            $paginatorData['last_page'] = (int)$paginator->lastPage();
        }
        return $paginatorData;
    }
}