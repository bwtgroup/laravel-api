<?php

namespace BwtTeam\LaravelAPI\Response;

use JsonSerializable;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use BwtTeam\LaravelAPI\Processors\BaseProcessor;
use BwtTeam\LaravelAPI\Processors\DataProcessor;
use BwtTeam\LaravelAPI\Processors\MetaProcessor;
use BwtTeam\LaravelAPI\Processors\StatusProcessor;
use BwtTeam\LaravelAPI\Processors\OriginalDataProcessor;


class ApiResponse extends JsonResponse
{
    const FIXED_STATUS = 200;

    /**
     * Response format.
     *
     * @var string|array
     */
    protected $format = ':originalData';

    /**
     * Response message
     *
     * @var string
     */
    protected $message;

    /**
     * Processors for registration.
     *
     * @var BaseProcessor[]
     */
    protected $processors = [
        OriginalDataProcessor::class,
        DataProcessor::class,
        StatusProcessor::class,
        MetaProcessor::class
    ];

    /**
     * Registered processors.
     *
     * @var array
     */
    protected $registeredProcessors = [];

    /**
     * Factory method for chainability.
     *
     * @param mixed $data The json response data
     * @param int $status The response status code
     * @param array $headers An array of response headers
     * @param int $options
     *
     * @return ApiResponse
     */
    public static function create($data = null, $status = 200, $headers = [], $options = 0)
    {
        return new static($data, $status, $headers, $options);
    }

    /**
     * ApiResponse constructor.
     *
     * @param null $data
     * @param int $status
     * @param array $headers
     * @param int $options
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        $this->encodingOptions = $options;

        foreach ($this->processors as $processor) {
            $this->register($processor);
        }

        $config = app('config')->get('api');
        $this->setFormat($config['format']);

        parent::__construct($data, $status, $headers);
    }

    /**
     * Set the response format.
     *
     * @param array|string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Get the response format
     *
     * @return array|string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int)$encodingOptions;

        return $this->setData($this->getOriginalContent());
    }

    /**
     * @inheritdoc
     */
    public function setData($data = [])
    {
        $this->original = $data;
        $this->prepareMessage($data);
        $processedData = $this->prepareData($data);

        if ($processedData instanceof Arrayable) {
            $this->data = json_encode($processedData->toArray(), $this->encodingOptions);
        } elseif ($processedData instanceof Jsonable) {
            $this->data = $processedData->toJson($this->encodingOptions);
        } elseif ($processedData instanceof JsonSerializable) {
            $this->data = json_encode($processedData->jsonSerialize(), $this->encodingOptions);
        } else {
            $this->data = json_encode($processedData, $this->encodingOptions);
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $this->update();
    }

    /**
     * Sets the response message text.
     *
     * @param $message
     *
     * @return ApiResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get the response message text.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function setStatusCode($code, $text = null)
    {
        parent::setStatusCode($code, $text);

        if ($text !== null) {
            $this->setMessage($text);
        } else {
            switch ($code) {
                case 404:
                    $this->setMessage('Unable to resolve the request ' . \Request::getRequestUri());
                    break;
                default :
                    $this->setMessage($this->statusText);
            }
        }

        return $this;
    }

    /**
     * Get the response type text.
     *
     * @return string
     */
    public function getStatusType()
    {
        $statusText = isset(self::$statusTexts[$this->getStatusCode()]) ? self::$statusTexts[$this->getStatusCode()] : 'unknown status';

        return snake_case($statusText);
    }

    /**
     * Register all processors.
     *
     * @param $processor
     * @param bool $force
     *
     * @return BaseProcessor
     */
    public function register($processor, $force = false)
    {
        if (($registered = $this->getProcessor($processor)) && !$force) {
            return $registered;
        }

        if (is_string($processor)) {
            $processor = $this->resolveProcessorClass($processor);
        }

        if (method_exists($processor, 'register')) {
            $processor->register();
        }

        $this->registerProcessor($processor);

        return $processor;
    }

    /**
     * Register processor.
     *
     * @param BaseProcessor $processor
     */
    public function registerProcessor(BaseProcessor $processor)
    {
        $processorSignature = $processor::getSignature();

        $this->registeredProcessors[$processorSignature] = $processor;
    }

    /**
     * Resolve a processor instance from the class name.
     *
     * @param $processor
     *
     * @return mixed
     */
    protected function resolveProcessorClass($processor)
    {
        return new $processor($this, app('config')->get('api'));
    }

    /**
     * Get the registered processor instance if it exists.
     *
     * @param BaseProcessor|string $processor
     *
     * @return BaseProcessor
     */
    protected function getProcessor($processor)
    {
        $name = is_string($processor) ? $processor : get_class($processor);

        if (is_string($processor)) {
            $processor = $this->resolveProcessorClass($processor);
        }

        $signature = $processor::getSignature();

        return array_key_exists($signature, $this->registeredProcessors) && $processor instanceof $name ? $processor : null;
    }

    /**
     * Prepare the response data.
     *
     * @param mixed $data Data for processing
     *
     * @return mixed
     */
    protected function prepareData($data)
    {
        $processedData = [];
        $format = $this->getFormat();

        if (is_array($format)) {
            foreach ($format as $key => $signature) {
                $processedData[$key] = $this->applyProcessor($signature, $data);
            }
        } else {
            $processedData = $this->applyProcessor($format, $data);
        }

        return $processedData;
    }

    /**
     * Prepare the message from data.
     *
     * @param $data
     */
    protected function prepareMessage($data)
    {
        $config = app('config')->get('api');
        if (!$config['messages']['fixed']) {
            if ($data instanceof \Exception) {
                $this->prepareExceptionMessage($data);
            }
        }
    }

    /**
     * Prepare the message from exception.
     *
     * @param \Exception $e
     */
    protected function prepareExceptionMessage(\Exception $e)
    {
        if (!empty($e->getMessage())) {
            $this->setMessage($e->getMessage());
        }
    }

    /**
     * Apply processor to the response.
     *
     * @param $signature
     * @param $data
     *
     * @return mixed
     */
    protected function applyProcessor($signature, $data)
    {
        $processedData = $signature;
        if (array_key_exists($signature, $this->registeredProcessors)) {
            /** @var BaseProcessor $processor */
            $processor = $this->registeredProcessors[$signature];
            $processor->handle($data);
            $processedData = $processor->getData();
        }

        return $processedData;
    }

    /**
     * Sends HTTP headers.
     *
     * @return $this
     */
    public function sendHeaders()
    {
        if ($this->isFixedStatusCode()) {
            $tmpStatusCode = $this->statusCode;
            $tmpStatusText = $this->statusText;

            $this->statusCode = self::FIXED_STATUS;
            $this->statusText = self::$statusTexts[self::FIXED_STATUS];

            parent::sendHeaders();

            $this->statusCode = $tmpStatusCode;
            $this->statusText = $tmpStatusText;
        } else {
            parent::sendHeaders();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isFixedStatusCode()
    {
        $config = app('config')->get('api');
        $always = array_get($config, 'status_code.fixed.always', false);
        $byHeader = array_get($config, 'status_code.fixed.header', false) && \Request::header(array_get($config, 'status_code.fixed.header', false));
        $byGetParam = array_get($config, 'status_code.fixed.get_param', false) && \Request::get(array_get($config, 'status_code.fixed.get_param', false));

        return $always || $byHeader || $byGetParam;
    }

    /**
     * @param $debugData
     *
     * @return $this
     */
    public function setDebugData($debugData)
    {
        $data = array_merge($this->getData(true), ['debug' => $debugData]);
        $this->data = json_encode($data, $this->encodingOptions);

        return $this->update();
    }
}