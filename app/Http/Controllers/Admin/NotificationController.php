<?php

namespace horsefly\Http\Controllers\Admin;

use Illuminate\Http\Request;

use horsefly\Http\Requests;
use horsefly\Http\Controllers\Controller;
use horsefly\Model\Plan;
use horsefly\Model\Setting;
use horsefly\Model\SendingServer;
use horsefly\Model\Notification;

class NotificationController extends Controller
{

    /**
     * Notification index.
     */
    public function index(Request $request)
    {
        return view('admin.notifications.index');
    }

    /**
     * Notification listing.
     */
    public function listing(Request $request)
    {
        $notifications = Notification::search($request)->paginate($request->per_page);

        return view('admin.notifications.listing', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if (isSiteDemo()) {
            echo trans('messages.operation_not_allowed_in_demo');
            return;
        }

        $notifications = Notification::whereIn('uid', explode(',', $request->uids));

        foreach ($notifications->get() as $notification) {
            $notifications->delete();
        }

        // Redirect to my lists page
        echo trans('messages.notifications.deleted', ['number' => $notifications->count()]);
    }
}