<?php

namespace BwtTeam\LaravelAPI;

use BwtTeam\LaravelAPI\Response\ApiResponse;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Events\Dispatcher as Event;
use Symfony\Component\HttpFoundation\Response;

class Debugger
{

    /**
     * @var Collection
     */
    private $queries;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Collection
     */
    private $debug;

    /**
     * @var bool
     */
    private $collectQueries = false;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Create a new Debugger service.
     *
     * @param Event $event
     * @param Connection $connection
     */
    public function __construct(Event $event, Connection $connection)
    {
        $this->queries = new Collection();
        $this->debug = new Collection();
        $this->event = $event;
        $this->connection = $connection;

        $this->event->listen(\Illuminate\Foundation\Http\Events\RequestHandled::class, function ($event) {
            $this->updateResponse($event->request, $event->response);
        });
    }

    /**
     * Listen database queries events.
     */
    public function collectDatabaseQueries()
    {
        $this->collectQueries = true;
        $this->connection->enableQueryLog();

        $this->connection->listen(function ($event) {
            $this->logQuery($event->sql, $event->bindings, $event->time);
        });
    }

    /**
     * Log DB query.
     *
     * @param string $query
     * @param array $attributes
     * @param float $time
     */
    private function logQuery($query, $attributes, $time)
    {
        if (!empty($attributes)) {
            $query = vsprintf(str_replace(['%', '?'], ['%%', "'%s'"], $query), $attributes) . ';';
        }

        $this->queries->push([
            'query' => $query,
            'time' => $time,
        ]);
    }

    /**
     * Add vars to debug output.
     */
    public function dump()
    {
        foreach (func_get_args() as $var) {
            $this->debug->push($var);
        }
    }

    /**
     * Update final response.
     *
     * @param Request $request
     * @param Response $response
     */
    private function updateResponse(Request $request, Response $response)
    {
        if ($response instanceof ApiResponse && $this->needToUpdateResponse($response)) {
            $originalData = $response->getOriginalContent();
            $data = [];

            if($originalData instanceof \Exception) {
                $data['exception'] = [
                    'message' => $originalData->getMessage(),
                    'code' => $originalData->getCode(),
                    'type' => get_class($originalData),
                    'file' => $originalData->getFile(),
                    'line' => $originalData->getLine(),
                ];
            }

            if ($this->collectQueries) {
                $data['database'] = [
                    'total_queries' => $this->queries->count(),
                    'queries' => $this->queries,
                ];
            }

            if (!$this->debug->isEmpty()) {
                $data['dump'] = $this->debug;
            }

            $response->setDebugData($data);
        }
    }

    /**
     * Check if debugger has to update the response.
     *
     * @param ApiResponse $response
     *
     * @return bool
     */
    private function needToUpdateResponse(ApiResponse $response)
    {
        return $this->collectQueries || !$this->debug->isEmpty() || $response->getOriginalContent() instanceof \Exception;
    }
}