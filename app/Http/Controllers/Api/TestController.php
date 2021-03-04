<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
  public function test(Request $request)
  {
    return redirect($request->redirect_uri . '?code=6610734270-rqltucr203301ins8740shf53t7106i6.apps.googleusercontent.com&state=' . $request->state);
//    dd($request->all());
  }

  public function data(Request $request)
  {

  }

  public function google_assistant (Request $request)
  {
    $data = (object) [];
    $data->requestId = $request->requestId;
    $data->payload = (object) [
      "agentUserId" => "1836.15267389",
      "devices" => [
        1 => (object) [
          "id" => "123",
          "type" => "action.devices.types.OUTLET",
          "traits" => ["action.devices.traits.OnOff"],
          "name" => (object) [
            "defaultNames" => ["My Outlet 1234"],
            "name" => "Night light",
            "nicknames" => ["wall plug"],
          ],
          "willReportState" => false,
          "roomHint" => "TestAndrey",
          "deviceInfo" => (object) [
            "manufacturer" => "lights-out-inc",
            "model" => "hs1234",
            "hwVersion" => "3.2",
            "swVersion" => "11.4"
          ],
          "attributes" => (object) [
            "commandOnlyOnOff" => true,
            "queryOnlyOnOff" => false
          ]
        ],
      ]
    ];

    return response()->json($data);
  }
}
