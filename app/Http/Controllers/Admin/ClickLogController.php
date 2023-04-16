<?php

namespace horsefly\Http\Controllers\Admin;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;

class ClickLogController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->admin->getPermission('report_click_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = \horsefly\Model\ClickLog::getAll();

        return view('admin.click_logs.index', [
            'items' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if ($request->user()->admin->getPermission('report_click_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = \horsefly\Model\ClickLog::search($request)->paginate($request->per_page);

        return view('admin.click_logs._list', [
            'items' => $items,
        ]);
    }
}
