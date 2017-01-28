<?php

namespace BwtTeam\LaravelAPI\Processors;

class OriginalDataProcessor extends BaseProcessor
{
    /**
     * Get signature of api processor.
     *
     * @return string
     */
    public static function getSignature()
    {
        return ':originalData';
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
        if ($data instanceof \Exception) {
            $this->setData($this->handleException($data));
        } else {
            $this->setData($data);
        }
    }

    /**
     * Handle exception data
     *
     * @param \Exception $e
     *
     * @return array
     */
    protected function handleException(\Exception $e)
    {
        return [];
    }
}