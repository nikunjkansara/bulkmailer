<?php

namespace horsefly\Http\Controllers\Admin;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;
use horsefly\Model\SendingServer;

class SendingServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->admin->can('read', new \horsefly\Model\SendingServer())) {
            return $this->notAuthorized();
        }

        // If admin can view all sending domains
        if (!$request->user()->admin->can("readAll", new \horsefly\Model\SendingServer())) {
            $request->merge(array("admin_id" => $request->user()->admin->id));
        }

        // exlude customer seding servers
        $request->merge(array("no_customer" => true));

        $items = SendingServer::search($request);

        return view('admin.sending_servers.index', [
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
        if (!$request->user()->admin->can('read', new \horsefly\Model\SendingServer())) {
            return $this->notAuthorized();
        }

        // If admin can view all sending domains
        if (!$request->user()->admin->can("readAll", new \horsefly\Model\SendingServer())) {
            $request->merge(array("admin_id" => $request->user()->admin->id));
        }

        // exlude customer seding servers
        $request->merge(array("no_customer" => true));

        $items = \horsefly\Model\SendingServer::search($request)->paginate($request->per_page);

        return view('admin.sending_servers._list', [
            'items' => $items,
        ]);
    }

    /**
     * Select sending server type.
     *
     * @return \Illuminate\Http\Response
     */
    public function select(Request $request)
    {
        return view('admin.sending_servers.select');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $server = new \horsefly\Model\SendingServer();
        $server->type = $request->type;
        $server = \horsefly\Model\SendingServer::mapServerType($server);        
        $server->status = 'active';
        $server->uid = '0';
        $server->quota_value = '1000';
        $server->quota_base = '1';
        $server->quota_unit = 'hour';
        $server->fill($request->old());
        
        $server->name = trans('messages.' . $request->type);

        // authorize
        if (!$request->user()->admin->can('create', $server)) {
            return $this->notAuthorized();
        }

        return view('admin.sending_servers.create', [
            'server' => $server,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // New sending server
        $server = new SendingServer();        

        // authorize
        if (!$request->user()->admin->can('create', $server)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('post')) {
            // options
            $options = [
                'allow_verify_domain_remotely' => 'yes',
            ];
            
            // fill values
            $server->fill($request->all());
            $server = \horsefly\Model\SendingServer::mapServerType($server);
            
            // validation
            $validator = $server->validConnection($request); //\Validator::make($request->all(), $server->getRules());            
                      
            if ($validator->fails()) {
                return redirect()->action('Admin\SendingServerController@create', $server->type)
                            ->withErrors($validator)
                            ->withInput();
            }
            
            $server->admin_id = $request->user()->admin->id;
            $server->status = SendingServer::STATUS_ACTIVE;
            
            // default name
            if (!$server->name) {
                $server->name = trans('messages.' . $server->type);
            }
            
            // default sever quota
            if (!$server->quota_value) {
                $server->quota_value = 1000;
                $server->quota_base = 1;
                $server->quota_unit = 'hour';
                $options['sending_limit'] = '1000_per_hour';
            }
            
            $server->options = json_encode($options);

            // bounce / feedback hanlder nullable
            if (empty($request->bounce_handler_id)) {
                $server->bounce_handler_id = null;
            }
            if (empty($request->feedback_loop_handler_id)) {
                $server->feedback_loop_handler_id = null;
            }

            if ($server->save()) {
                // init sending server identities
                $server->initVerifiedIdentities();

                $request->session()->flash('alert-success', trans('messages.sending_server.created'));
                return redirect()->action('Admin\SendingServerController@edit', [$server->uid, $server->type]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $server = \horsefly\Model\SendingServer::findByUid($id);
        $server = \horsefly\Model\SendingServer::mapServerType($server);
        $originServer = \horsefly\Model\SendingServer::mapServerType($server);

        // authorize
        if (!$request->user()->admin->can('update', $server)) {
            return $this->notAuthorized();
        }

        // bounce / feedback hanlder nullable
        if ($request->old() && empty($request->old()["bounce_handler_id"])) {
            $server->bounce_handler_id = null;
        }
        if ($request->old() && empty($request->old()["feedback_loop_handler_id"])) {
            $server->feedback_loop_handler_id = null;
        }

        $server->fill($request->old());        
        
        $notices = [];
        
        try {
            $server->test();
        } catch (\Exception $ex) {
            $originServer->disable();
            
            $notices[] = [
                'title' => trans('messages.sending_server.connect_failed'),
                'message' => $ex->getMessage()
            ];
        }        
        
        $identities = null;
        
        try {
            $identities = $server->getVerifiedIdentities();
        } catch (\Exception $ex) {
            $notices[] = [
                'title' => trans('messages.sending_server.identities_list_failed'),
                'message' => $ex->getMessage(),
            ];
        }        
        
        // options
        if (isset($request->old()['options'])) {
            $server->options = json_encode($request->old()['options']);
        }

        return view('admin.sending_servers.edit', [
            'server' => $server,
            'notices' => $notices,
            'identities' => $identities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Get current user
        $current_user = $request->user();
        $server = \horsefly\Model\SendingServer::findByUid($id);
        $server = \horsefly\Model\SendingServer::mapServerType($server);

        // authorize
        if (!$request->user()->admin->can('update', $server)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            // Save current user info
            $server->fill($request->all());
            
            // validation
            $validator = $server->validConnection($request);
            
            if ($validator->fails()) {
                return redirect()->action('Admin\SendingServerController@edit', [$server->uid, $server->type])
                            ->withErrors($validator)
                            ->withInput();
            }

            // bounce / feedback hanlder nullable
            if (empty($request->bounce_handler_id)) {
                $server->bounce_handler_id = null;
            }
            if (empty($request->feedback_loop_handler_id)) {
                $server->feedback_loop_handler_id = null;
            }

            if ($server->save()) {
                $request->session()->flash('alert-success', trans('messages.sending_server.updated'));

                return redirect()->action('Admin\SendingServerController@edit', [$server->uid, $server->type]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = \horsefly\Model\SendingServer::findByUid($row[0]);

            // authorize
            if (!$request->user()->admin->can('update', $item)) {
                return $this->notAuthorized();
            }

            $item->custom_order = $row[1];
            $item->save();
        }

        echo trans('messages.sending_server.custom_order.updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $items = \horsefly\Model\SendingServer::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->user()->admin->can('delete', $item)) {
                $item->doDelete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_servers.deleted');
    }

    /**
     * Disable sending server.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $items = \horsefly\Model\SendingServer::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->user()->admin->can('disable', $item)) {
                $item->disable();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_servers.disabled');
    }

    /**
     * Disable sending server.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {
        $items = \horsefly\Model\SendingServer::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->user()->admin->can('enable', $item)) {
                $item->enable();
            }
        }

        // Redirect to my lists page
        echo trans('messages.sending_servers.enabled');
    }

    /**
     * Test Sending server.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function test(Request $request, $uid)
    {
        // Get current user
        $current_user = $request->user();

        // Fill new server info
        if ($uid) {
            $server = \horsefly\Model\SendingServer::findByUid($uid);
        } else {
            $server = new \horsefly\Model\SendingServer();
            $server->uid = 0;
        }

        $server->fill($request->all());

        // authorize
        if (!$current_user->admin->can('test', $server)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // @todo testing method and return result here. Ex: echo json_encode($server->test())
            try {
                $server->sendTestEmail([
                    'from_email' => $request->from_email,
                    'to_email' => $request->to_email,
                    'subject' => $request->subject,
                    'plain' => $request->content
                ]);
            } catch (\Exception $ex) {
                echo json_encode([
                    'status' => 'error', // or success
                    'message' => $ex->getMessage()
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success', // or success
                'message' => trans('messages.sending_server.test_email_sent')
            ]);
            return;
        }

        return view('admin.sending_servers.test', [
            'server' => $server,
        ]);
    }
    
    /**
     * Test Sending server.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function testConnection(Request $request, $uid)
    {
        $server = \horsefly\Model\SendingServer::findByUid($uid);
        $server = \horsefly\Model\SendingServer::mapServerType($server);

        // authorize
        if (!$request->user()->admin->can('update', $server)) {
            return $this->notAuthorized();
        }
        
        try {
            $server->test();
            
            return trans('messages.sending_server.test_success');
        } catch (\Exception $e) {
            $server->disable();
            
            return $e->getMessage();
        }
    }
    
    /**
     * Test PHP mail work.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function testPhpMail(Request $request)
    {
        return trans('messages.sending_server.test_success');
    }
    
    /**
     * Select2 customer.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function select2(Request $request)
    {
        echo \horsefly\Model\SendingServer::adminSelect2($request);
    }
    
    /**
     * Sending Limit Form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendingLimit(Request $request)
    {
        if (!$request->uid) {
            $server = new \horsefly\Model\SendingServer();
        } else {
            $server = \horsefly\Model\SendingServer::findByUid($request->uid);
        }
        
        $server->fill($request->all());
        
        // Default quota
        if ($server->quota_value == -1) {
            $server->quota_value = '1000';
            $server->quota_base = '1';
            $server->quota_unit = 'hour';
            $server->setOption('sending_limit', '1000_per_hour');
        }
        
        // save posted data
        if ($request->isMethod('post')) {
            $selectOptions = $server->getSendingLimitSelectOptions();
            
            return view('admin.sending_servers.form._sending_limit', [
                'quotaValue' => $request->quota_value,
                'quotaBase' => $request->quota_base,
                'quotaUnit' => $request->quota_unit,
                'server' => $server,
            ]);
        }
        
        return view('admin.sending_servers.form.sending_limit', [
            'server' => $server,
        ]);
    }
    
    /**
     * Save sending server config settings.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function config(Request $request, $uid)
    {
        // find server
        $server = SendingServer::findByUid($uid);

        // authorize
        if (!$request->user()->admin->can('update', $server)) {
            return $this->notAuthorized();
        }
        
        // Save current user info
        $server->fill($request->all());
        
        // default sever quota
        if ($request->options) {
            $server->setOptions($request->options); // options = json_encode($request->options);
        }
        
        // Sening limit
        if ($request->options['sending_limit'] != 'custom' && $request->options['sending_limit'] != 'current') {
            $limits = SendingServer::sendingLimitValues()[$request->options['sending_limit']];
            $server->quota_value = $limits['quota_value'];
            $server->quota_unit = $limits['quota_unit'];
            $server->quota_base = $limits['quota_base'];
        }

        // save posted data        
        $this->validate($request, $server->getConfigRules());
        
        // bounce / feedback hanlder nullable
        if (empty($request->bounce_handler_id)) {
            $server->bounce_handler_id = null;
        }
        if (empty($request->feedback_loop_handler_id)) {
            $server->feedback_loop_handler_id = null;
        }

        if ($server->save()) {
            $request->session()->flash('alert-success', trans('messages.sending_server.updated'));

            return redirect()->action('Admin\SendingServerController@edit', [$server->uid, $server->type]);
        }
        
    }
    
    /**
     * Sending Limit Form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function awsRegionHost(Request $request)
    {
        if ($request->uid) {
            $server = SendingServer::findByUid($request->uid);
        } else {
            $server = new SendingServer();
        }
        
        foreach(SendingServer::awsRegionSelectOptions() as $option) {
            if (isset($option['host']) && $option['value'] == $request->aws_region) {
                $server->host = $option['host'];
            }
        }
        return view('admin.sending_servers.form._aws_region_host', [
            'server' => $server,
        ]);
    }
    
    /**
     * Add domain to sending server.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function addDomain(Request $request, $uid)
    {
        $server = SendingServer::findByUid($request->uid);
        
        // save posted data
        if ($request->isMethod('post')) {
            $valid = true;
            
            if (checkEmail($request->domain)) {
                // validation
                $validator = \Validator::make($request->all(), [
                    'domain' => 'required|email',
                ]);
                
                if (in_array(strtolower($request->domain), $server->getEmails())) {
                    $validator->errors()->add('domain', trans('messages.sending_identity.exist_error'));
                    $valid = false;
                }
                
                if (!$valid || $validator->fails()) {
                    return redirect()->action('Admin\SendingServerController@addDomain', $server->uid)
                                ->withErrors($validator)
                                ->withInput();
                }

                $server->addEmail(strtolower($request->domain)); 
            } else {
                // validation
                $validator = \Validator::make($request->all(), [
                    'domain' => 'required|regex:/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/i',
                ]);
                
                if (in_array(strtolower($request->domain), $server->getDomains())) {
                    $validator->errors()->add('domain', trans('messages.sending_identity.exist_error'));
                    $valid = false;
                }
                
                if (!$valid || $validator->fails()) {
                    return redirect()->action('Admin\SendingServerController@addDomain', $server->uid)
                                ->withErrors($validator)
                                ->withInput();
                }

                $server->addDomain(strtolower($request->domain)); 
            }
            
            $request->session()->flash('alert-success', trans('messages.sending_server.updated'));
            return;
        }
        
        return view('admin.sending_servers.add_domain', [
            'server' => $server,
        ]);
    }
    
    /**
     * Remove domain from sending server.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function removeDomain(Request $request, $uid, $domain)
    {
        $server = SendingServer::findByUid($request->uid);
        
        $domain = base64_decode($domain);

        if (checkEmail($domain)) {
            $server->removeEmail($domain);
        } else {
            $server->removeDomain($domain);
        }
        
        $request->session()->flash('alert-success', trans('messages.sending_server.domain.removed'));
        return redirect()->action('Admin\SendingServerController@edit', [$server->uid, $server->type]);
    }
    
    /**
     * Dropbox list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function fromDropbox(Request $request)
    {
        $server = SendingServer::findByUid($request->uid);
        
        $droplist = $server->verifiedIdentitiesDroplist(strtolower(trim($request->keyword)));        
        return response()->json($droplist);
    }
}
