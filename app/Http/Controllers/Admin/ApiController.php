<?php

namespace horsefly\Http\Controllers\Admin;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;

class ApiController extends Controller
{
    /**
     * Docs for api v1 - Admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function doc()
    {
        return view('docs.api.v1', ['view' => 'backend']);
    }
}
