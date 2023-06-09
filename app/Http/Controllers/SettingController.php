<?php

namespace horsefly\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LaravelLog;

class SettingController extends Controller
{

    /**
     * Render uploaded file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function file(Request $request, $filename)
    {
        return \Image::make(\horsefly\Model\Setting::getUploadFilePath($filename))->response();
    }
}
