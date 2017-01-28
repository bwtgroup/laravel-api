<?php

namespace BwtTeam\LaravelAPI\Processors;


class StatusProcessor extends BaseProcessor
{
    const STATUS_FAILURE = 0;
    const STATUS_SUCCESS = 1;

    /**
     * Get signature of api processor.
     *
     * @return string
     */
    public static function getSignature()
    {
        return ':status';
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
        $processedData = $this->response->isSuccessful() ? self::STATUS_SUCCESS : self::STATUS_FAILURE;

        $this->setData($processedData);
    }
}