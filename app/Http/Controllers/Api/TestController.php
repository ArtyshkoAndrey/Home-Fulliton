<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
  public function test(Request $request)
  {
    return redirect($request->redirect_uri . '?code='. config('token.code') . '=' . $request->state);
//    dd($request->all());
  }

  public function token(Request $request): JsonResponse
  {
    $data = [
    "token_type" => "Bearer",
    "access_token" => "1836.15267389",
    "expires_in" => 36000
    ];

    return response()->json($data);
  }

  public function google_assistant (Request $request): JsonResponse
  {
    $data = (object) [];
    $data->requestId = $request->requestId;
    $data->payload = [
      "agentUserId" => "1836.15267389",
      "devices" => [
        [
          "id" => "123",
          "type" => "action.devices.types.OUTLET",
          "traits" => ["action.devices.traits.OnOff"],
          "name" => [
            "defaultNames" => ["Свет на кухне"],
            "name" => "Свет на кухне",
            "nicknames" => ["Свет на кухне"],
          ],
          "willReportState" => false,
          "roomHint" => "Кухня",
          "deviceInfo" => [
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
        [
          "id" => "1233",
          "type" => "action.devices.types.THERMOSTAT",
          "traits" => ["action.devices.traits.TemperatureControl"],
          "name" => "Температура",
          "willReportState" => true,
          "roomHint" => "Кухня",
          "deviceInfo" => [
            "manufacturer" => "smart-home-inc",
            "model" => "hs1234",
            "hwVersion" => "3.2",
            "swVersion" => "11.4"
          ],
          "attributes" => [
            "temperatureRange" => [
              "minThresholdCelsius" => 0,
              "maxThresholdCelsius" => 35,
              "temperatureAmbientCelsius" => 23.3
            ],
            "temperatureStepCelsius" => 1,
            "temperatureUnitForUX" => "C",
            "commandOnlyTemperatureControl" => true,
            "queryOnlyTemperatureControl" => true,
          ],
        ],
      ]
    ];

    return response()->json($data);
  }
}
