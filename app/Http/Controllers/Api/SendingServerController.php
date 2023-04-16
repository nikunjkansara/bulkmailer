<?php

namespace horsefly\Http\Controllers\Api;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;

/**
 * /api/v1/sending_servers - API controller for managing sending servers.
 */
class SendingServerController extends Controller
{
    /**
     * Display all sending servers.
     *
     * GET /api/v1/sending_servers
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::guard('api')->user();
        
        // authorize
        if (!$user->can('readAll', new \horsefly\Model\SendingServer())) {
            return \Response::json(array('message' => 'Unauthorized'), 401);
        }
        
        $servers = \horsefly\Model\SendingServer::getAll()
            ->select('uid', 'name', 'created_at', 'updated_at')
            ->get();

        return \Response::json($servers, 200);
    }
}
