<?php

namespace horsefly\Http\Controllers\Api;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;

/**
 * /api/v1/subscriptions - API controller for managing subscriptions.
 */
class SubscriptionController extends Controller
{
    /**
     * Subscribe customer to a plan (For admin only).
     *
     * POST /api/v1/subscriptions
     *
     * @param \Illuminate\Http\Request $request         All supscription information
     * @param string                   $customer_uid    Customer's uid
     * @param string                   $plan_uid        Plan's uid
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get current user
        $user = \Auth::guard('api')->user();
        $subscription = new \horsefly\Model\Subscription();
        $subscription->status = \horsefly\Model\Subscription::STATUS_ACTIVE;

        // authorize
        if (!$user->isAdmin() || !$user->admin->can('create', $subscription)) {
            return \Response::json(array('message' => 'Unauthorized'), 401);
        }

        // save posted data
        if ($request->isMethod('post')) {
            $validator = \Validator::make($request->all(), $subscription->rules());
            if ($validator->fails()) {
                return response()->json($validator->messages(), 403);
            }

            $subscription->fillAttributes($request->all());
            $subscription->admin_id = $user->admin->id;

            // Allow admin update start/end date
            if(!empty($request->start_at)) {
                $subscription->start_at = \horsefly\Library\Tool::systemTimeFromString($request->start_at . ' 00:00');
            }
            if(!empty($request->end_at)) {
                $subscription->end_at = \horsefly\Library\Tool::systemTimeFromString($request->end_at . ' 00:00');
            }

            $subscription->save();

            return \Response::json(array(
                'message' => trans('messages.subscription.created'),
                'subscription_uid' => $subscription->uid
            ), 200);
        }
    }
}
