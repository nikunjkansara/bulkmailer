<?php

namespace horsefly\Http\Controllers;

use Illuminate\Http\Request;
use horsefly\Model\Email;
use horsefly\Model\Subscriber;
use horsefly\Library\StringHelper;

class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Preview email for a given subscriber.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function preview($uid, $subscriber_uid)
    {
        $email = Email::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);
        $logs = $email->trackingLogs()->where('subscriber_id', $subscriber->id)->get();
        
        if ($logs->count() == 0) {
            list($message, $msgId) = $email->prepare($subscriber);
            return response($message->toString(), 200)
                  ->header('Content-Type', 'text/plain');
        } else {
            $links = [];
            foreach ($logs as $log) {
                $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($log->message_id)], false);
                $url = $email->buildTrackingUrl($path);
                $links[] = "{$log->email_id} <a href='{$url}'>{$url}</a>";

            }
            return response(implode('<br>', $links), 200);
        }
        
    }

    /**
     * Template screenshot.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function image(Request $request, $uid, $email_uid)
    {
        // Get current user
        $email = Email::findByUid($email_uid);

        if ($email->getThumb()) {
            $img = \Image::make($email->getThumb());
        } else {
            $img = \Image::make(public_path('assets/images/placeholder.jpg'));
        }

        return $img->response();
    }
}
