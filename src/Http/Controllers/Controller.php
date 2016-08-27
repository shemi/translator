<?php

namespace Shemi\Translator\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Shemi\Translator\Manager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $status_code = 200;

    /** @var \Shemi\Translator\Manager */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        $appName = config('app.name', env('APP_NAME'));
        $appName = $appName ? "The {$appName} Translator" : "Translator";
        view()->share('appName', $appName);
    }



    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param int $status_code
     * @return $this
     */
    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;

        return $this;
    }


    public function respondNotFound($message = 'Not found.')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }



    public function respondNotAuthorized($message = 'not Authorized.')
    {
        return $this->setStatusCode(401)->respondWithError($message);
    }



    public function respondInternalError($message = 'Internal error.')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    public function respondBadRequest($message = 'Bad Request')
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    public function respondWithError($message)
    {

        if( is_string($message)){
            $message = [
                'global' => [$message]
            ];
        }

        return Response::json([
            'error' => [
                'messages' => $message,
                'code'    => $this->getStatusCode()
            ]
        ], $this->getStatusCode());
    }



    public function respond($data, $headers = [], $collection = false)
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }


}
