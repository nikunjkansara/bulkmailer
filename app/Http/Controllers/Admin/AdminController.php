<?php

namespace horsefly\Http\Controllers\Admin;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;

class AdminController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // authorize
        if (\Gate::denies('read', new \horsefly\Model\Admin())) {
            return $this->notAuthorized();
        }

        // If admin can view all sending domains
        if (!$request->user()->admin->can("readAll", new \horsefly\Model\Admin())) {
            $request->merge(array("creator_id" => $request->user()->id));
        }

        $admins = \horsefly\Model\Admin::search($request);

        return view('admin.admins.index', [
            'admins' => $admins,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        // authorize
        if (\Gate::denies('read', new \horsefly\Model\Admin())) {
            return $this->notAuthorized();
        }

        // If admin can view all sending domains
        if (!$request->user()->admin->can("readAll", new \horsefly\Model\Admin())) {
            $request->merge(array("creator_id" => $request->user()->id));
        }

        $admins = \horsefly\Model\Admin::search($request)->paginate($request->per_page);

        return view('admin.admins._list', [
            'admins' => $admins,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $admin = new \horsefly\Model\Admin();
        $admin->status = 'active';
        $admin->uid = '0';

        if (!empty($request->old())) {
            $admin->fill($request->old());
            $admin->admin_group_id = $request->old()["admin_group_id"];
            // User info
            $admin->user = new \horsefly\Model\User();
            $admin->user->fill($request->old());
        }

        // authorize
        if (\Gate::denies('create', $admin)) {
            return $this->notAuthorized();
        }

        return view('admin.admins.create', [
            'admin' => $admin,
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
        // Get current user
        $current_user = $request->user();
        $admin = new \horsefly\Model\Admin();
        $contact = new \horsefly\Model\Contact();

        // authorize
        if (\Gate::denies('create', $admin)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('post')) {
            $rules = $admin->rules();
            $rules = array_merge($rules, [
                'admin_group_id' => 'required',
            ]);
            $this->validate($request, $rules);

            // Create user account for admin
            $user = new \horsefly\Model\User();
            $user->email = $request->email;
            $user->activated = true;
            // Update password
            if (!empty($request->password)) {
                $user->password = bcrypt($request->password);
            }
            $user->save();

            // Save current user info
            $admin->user_id = $user->id;
            $admin->creator_id = $request->user()->id;
            $admin->fill($request->all());
            $admin->admin_group_id = $request->admin_group_id;
            $admin->status = 'active';

            if ($admin->save()) {
                // Upload and save image
                if ($request->hasFile('image')) {
                    if ($request->file('image')->isValid()) {
                        // Remove old images
                        $admin->removeImage();
                        $admin->image = $admin->uploadImage($request->file('image'));
                        $admin->save();
                    }
                }

                // Remove image
                if ($request->_remove_image == 'true') {
                    $admin->removeImage();
                    $admin->image = '';
                }

                // create customer account
                if (!empty($request->create_customer_account) && $request->create_customer_account == 'yes') {
                    $admin->createCustomerAccount($current_user->admin);
                }

                $request->session()->flash('alert-success', trans('messages.admin.created'));

                return redirect()->action('Admin\AdminController@index');
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
        $admin = \horsefly\Model\Admin::findByUid($id);

        // authorize
        if (\Gate::denies('update', $admin)) {
            return $this->notAuthorized();
        }

        if (!empty($request->old())) {
            $admin->fill($request->old());
            $admin->admin_group_id = $request->old()["admin_group_id"];
            // User info
            $admin->user = new \horsefly\Model\User();
            $admin->user->fill($request->old());
        }

        return view('admin.admins.edit', [
            'admin' => $admin,
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
        $admin = \horsefly\Model\Admin::findByUid($id);

        // authorize
        if (\Gate::denies('update', $admin)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            $rules = $admin->rules();
            $rules = array_merge($rules, [
                'admin_group_id' => 'required',
            ]);
            $this->validate($request, $rules);

            // Update user account for admin
            $user = $admin->user;
            $user->email = $request->email;
            // Update password
            if (!empty($request->password)) {
                $user->password = bcrypt($request->password);
            }
            $user->save();

            // Save current user info
            $admin->fill($request->all());

            // Change group
            $admin->admin_group_id = $request->admin_group_id;

            // Upload and save image
            if ($request->hasFile('image')) {
                if ($request->file('image')->isValid()) {
                    // Remove old images
                    $admin->removeImage();
                    $admin->image = $admin->uploadImage($request->file('image'));
                }
            }

            // Remove image
            if ($request->_remove_image == 'true') {
                $admin->removeImage();
                $admin->image = '';
            }

            // create customer account
            if (!empty($request->create_customer_account) && $request->create_customer_account == 'yes') {
                $admin->createCustomerAccount($current_user->admin);
            }

            if ($admin->save()) {
                $request->session()->flash('alert-success', trans('messages.admin.updated'));
                return redirect()->action('Admin\AdminController@index');
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

    public function select2(Request $request)
    {
        $result = [['id' => '1', 'text' => 'One'], ['id' => '2', 'text' => 'Two']];

        return response()->json($result);
    }

    /**
     * Enable item.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {
        $items = \horsefly\Model\Admin::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if (\Gate::allows('update', $item)) {
                $item->enable();
            }
        }

        // Redirect to my lists page
        echo trans('messages.admins.enabled');
    }

    /**
     * Disable item.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $items = \horsefly\Model\Admin::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if (\Gate::allows('update', $item)) {
                $item->disable();
            }
        }

        // Redirect to my lists page
        echo trans('messages.admins.disabled');
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
        $items = \horsefly\Model\Admin::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if (\Gate::denies('delete', $item)) {
                return;
            }
        }

        foreach ($items->get() as $item) {
            $item->deleteRecursive();
        }

        // Redirect to my lists page
        echo trans('messages.admins.deleted');
    }

    /**
     * Switch user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function loginAs(Request $request)
    {
        $admin = \horsefly\Model\Admin::findByUid($request->uid);

        // authorize
        if (\Gate::denies('loginAs', $admin)) {
            return;
        }

        $orig_id = $request->user()->uid;
        \Auth::login($admin->user);
        \Session::put('orig_admin_id', $orig_id);

        return redirect()->action('Admin\HomeController@index');
    }

    /**
     * Log in back user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function loginBack(Request $request)
    {
        $id = \Session::pull('orig_admin_id');
        $orig_user = \horsefly\Model\User::findByUid($id);

        \Auth::login($orig_user);

        return redirect()->action('Admin\AdminController@index');
    }

    /**
     * Render user image.
     */
    public function avatar(Request $request)
    {
        // Get current user
        if ($request->uid != '0') {
            $user = \horsefly\Model\Admin::findByUid($request->uid);
        } else {
            $user = new \horsefly\Model\Admin();
        }
        if (!empty($user->imagePath())) {
            $img = \Image::make($user->imagePath());
        } else {
            $img = \Image::make(public_path('assets/images/placeholder.jpg'));
        }

        return $img->response();
    }
}