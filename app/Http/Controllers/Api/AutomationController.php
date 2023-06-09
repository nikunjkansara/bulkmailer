<?php

namespace horsefly\Http\Controllers\Api;

use horsefly\Http\Controllers\Controller;
use horsefly\Model\Automation2;
use horsefly\Jobs\RunAutomation;

/**
 * /api/v1/campaigns - API controller for managing campaigns.
 */
class AutomationController extends Controller
{
    /**
     * Call api for automation api call type.
     *
     * GET /api/v1/campaigns
     *
     * @return \Illuminate\Http\Response
     */
    public function execute($uid)
    {
        try {
            $automation = Automation2::findByUid($uid);
            $automation->logger()->info(sprintf('Queuing automation "%s" in response to API call', $automation->name));
            dispatch(new RunAutomation($automation));

            return \Response::json(['success' => true], 200);
        } catch (\Exception $ex) {
            return \Response::json(['success' => false, 'error' => $ex->getMessage()], 500);
        }
        
    }
}
