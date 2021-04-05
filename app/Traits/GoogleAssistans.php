<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait GoogleAssistans
{

  public function sync(Request $request): array
  {
    $data = [];
    $data['requestId'] = $request->requestId;
//    $data['payload'] = [
//      "agentUserId" => "1836.15267389",
//      "devices" => [
//        [
//          "id" => "123",
//          "type" => "action.devices.types.OUTLET",
//          "traits" => ["action.devices.traits.OnOff"],
//          "name" => [
//            "defaultNames" => ["Свет на кухне"],
//            "name" => "Свет на кухне",
//            "nicknames" => ["Свет на кухне"],
//          ],
//          "willReportState" => false,
//          "roomHint" => "Кухня",
//          "deviceInfo" => [
//            "manufacturer" => "lights-out-inc",
//            "model" => "hs1234",
//            "hwVersion" => "3.2",
//            "swVersion" => "11.4"
//          ],
//          "attributes" => [
//            "commandOnlyOnOff" => false,
//            "queryOnlyOnOff" => false
//          ]
//        ],
//        [
//          "id" => "1233",
//          "type" => "action.devices.types.SENSOR",
//          "traits" => [
//            "action.devices.traits.TemperatureControl",
//            "action.devices.traits.EnergyStorage",
//            "action.devices.traits.SensorState"
//          ],
//          "name" => [
//            "defaultNames" => ["Температура на кухне"],
//            "name" => "Температура на кухне",
//            "nicknames" => ["Температура на кухне"],
//          ],
//          "willReportState" => false,
//          "roomHint" => "Кухня",
//          "deviceInfo" => [
//            "manufacturer" => "smart-home-inc",
//            "model" => "hs1234",
//            "hwVersion" => "3.2",
//            "swVersion" => "11.4"
//          ],
//          "attributes" => [
//            "temperatureRange" => [
//              "minThresholdCelsius" => 0,
//              "maxThresholdCelsius" => 35,
//              "temperatureAmbientCelsius" => 23
//            ],
//            "temperatureUnitForUX" => "C",
//            "commandOnlyTemperatureControl" => false,
//            "queryOnlyTemperatureControl" => true,
//          ],
//        ],
//        [
//          "id" => "1234",
//          "type" => "action.devices.types.CURTAIN",
//          "traits" => [
//            "action.devices.traits.OpenClose"
//          ],
//          "name" => [
//            "name" => "Шторы на кухне"
//          ],
//          "willReportState" => true,
//          "roomHint" => "Кухня",
//          "attributes" => [
//            "openDirection" => [
//              "LEFT",
//              "RIGHT"
//            ]
//          ],
//          "deviceInfo" => [
//            "manufacturer" => "smart-home-inc",
//            "model" => "hs1234",
//            "hwVersion" => "3.2",
//            "swVersion" => "11.4"
//          ]
//        ]
//      ]
//    ];

    $response = Http::get('http://95.188.80.41:8080/api/google-home/modules');
    $rooms = $response->json()['rooms'];
    return $rooms;

    return $data;
  }

  public function query (Request $request)
  {
    $request->validate([
      'inputs' => 'required|array',
    ]);
    $data = $request->all();
    $input = $data['inputs'][0];
    if ($input['intent'] === 'action.devices.QUERY') {
      $deviceId = $input['payload']['devices'][0]['id'];
      if ($deviceId === '1233') {
        return $this->getTemperatureControl($data['requestId']);
      }
    }
    return [];
  }

  private function getTemperatureControl ($requestId): array
  {
    return [
      "requestId" => $requestId,
      "payload" => [
        "devices" => [
          "1233" => [
            "status" => "SUCCESS",
            "online" => true,
            // "temperatureRange" => [
            "temperatureAmbientCelsius" => 10,
            // "queryOnlyTemperatureControl" => false
            // ]
          ]
        ]
      ]
    ];
  }

}
