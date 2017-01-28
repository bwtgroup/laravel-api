<?php

namespace BwtTeam\LaravelAPI\Response;

use BwtTeam\LaravelAPI\Processors\BaseProcessor;
use BwtTeam\LaravelAPI\Processors\DataProcessor;
use BwtTeam\LaravelAPI\Processors\MetaProcessor;
use BwtTeam\LaravelAPI\Processors\OriginalDataProcessor;
use BwtTeam\LaravelAPI\Processors\StatusProcessor;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

class ApiResponse extends BaseJsonResponse
{
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
     * Data after processing.
     *
     * @var string|mixed
     */
    protected $processedData = null;

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
        return $this->setJsonOptions($encodingOptions);
    }

    /**
     * Get the JSON encoding options.
     *
     * @return int
     */
    public function getJsonOptions()
    {
        return $this->getEncodingOptions();
    }

    /**
     * Set the JSON encoding options.
     *
     * @param  int $options
     *
     * @return mixed
     */
    public function setJsonOptions($options)
    {
        $this->encodingOptions = (int)$options;

        return $this->setData($this->getData());
    }

    /**
     * @inheritdoc
     */
    public function setData($data = [])
    {
        $this->data = $data;
        $this->prepareMessage($data);
        $this->updateProcessedData();

        return $this->update();
    }

    /**
     * Get the data from the response.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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
//        $this->updateProcessedData();
//
//        return $this->update();
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
     * Get the processed data from the response.
     *
     * @return mixed|string
     */
    public function getProcessedData()
    {
        return $this->processedData;
    }

    /**
     * Updates the processed data
     *
     * @return $this
     */
    protected function updateProcessedData()
    {
        $data = $this->getData();
        $this->processedData = $this->prepareData($data);

        return $this;
    }

    /**
     * Sets the JSONP callback.
     *
     * @param  string|null $callback
     *
     * @return $this
     */
    public function withCallback($callback = null)
    {
        return $this->setCallback($callback);
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
    public function resolveProcessorClass($processor)
    {
        return new $processor($this);
    }

    /**
     * Get the registered processor instance if it exists.
     *
     * @param BaseProcessor|string $processor
     *
     * @return BaseProcessor
     */
    public function getProcessor($processor)
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
    public function prepareData($data)
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
    public function applyProcessor($signature, $data)
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
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return $this
     */
    protected function update()
    {
        $data = json_encode($this->getProcessedData(), $this->encodingOptions);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            $this->setContent(sprintf('/**/%s(%s);', $this->callback, $data));

            return $this;
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        $this->setContent($data);

        return $this;
    }
}