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

    $rooms = $this->getData();
    $modules = [];
    foreach ($rooms as $room) {
      foreach ($room['modules'] as $m) {
        if ($m['type']['type'] === 'temperature') {
          $module = $this->getModuleTemperature($m, $room);
        } else if ($m['type']['type'] === 'light') {
          $module = $this->getModuleLight($m, $room);
        } else {
          continue;
        }

        array_push($modules, $module);
      }
    }
    $data['payload'] = [
      "agentUserId" => "1836.15267389",
      "devices" => $modules
    ];

    return $data;
  }

  public function query (Request $request): array
  {
    $request->validate([
      'inputs' => 'required|array',
      'requestId' => 'required'
    ]);
    $data = $request->all();
    $input = $data['inputs'][0];
    $modules = (object) [];
    if ($input['intent'] === 'action.devices.QUERY') {

      foreach ($input['payload']['devices'] as $device) {
        $id = $device['id'];

        $m = $this->getModule((int) $id);

        if ($m['type']['type'] === 'temperature') {
          $module = $this->getTemperatureState($m);
        } else if ($m['type']['type'] === 'light') {
          $module = $this->getLightState($m);
        } else {
          continue;
        }

        $modules->{$m['id']} = $module;
      }

      return [
        'requestId' => $request->get('requestId'),
        'payload' => [
          'devices' => $modules
        ]
      ];
    }
    return [];
  }

  public function execute (Request $request): array {
    $request->validate([
      'inputs' => 'required|array',
      'requestId' => 'required'
    ]);
    $data = $request->all();
    $input = $data['inputs'][0];
    $commands = [];
    foreach ($input['payload']['commands'] as $command) {
      $state = true;
      $devices = [];
      foreach ($command['devices'] as $device) {
        $id = $device['id'];
        array_push($devices, $command['devices']);
        $m = $this->getModule((int) $id);

        if ($m['type']['type'] === 'light') {
          $s = $this->getLightExecute($m, $command['execution'][0]['params']);
          if ($s === false) {
            $state = false;
          }
        } else {
          $state = false;
        }
      }
      $c = [
        'ids' => $devices,
        'status' => $state ? 'SUCCESS' : 'ERROR',
        'states' => [
          'online' => true,
          $command['execution'][0]
        ]
      ];
      if ($state === false) {
        array_push($c[], ['errorCode' => 'deviceTurnedOff']);
      }
      array_push($commands, $c);

    }


    return [
      'requestId' => $request->get('requestId'),
      'payload' => [
        'commands' => $commands
      ]
    ];

  }

  private function getLightExecute ($m, $data): bool {
    return $this->setDataModule((int) $m->id, $data['on'] ? '1' : '0');
  }

  private function getModuleLight ($m, $room): array
  {
    $module = [];
    $module['id'] = $m['id'];
    $module['type'] = $m['type']['google_type']['name'];
    $traits = [];
    foreach ($m['type']['google_traits'] as $trait) {
      array_push($traits, $trait['name']);
    }
    $module['traits'] = $traits;
    $module['name'] = [
      'name' => $m['name']
    ];
    $module['willReportState'] = true;
    $module['roomHint'] = $room['name'];
    $module['deviceInfo'] = [
      "manufacturer" => "smart-home-inc",
      "model" => "hs1234",
      "hwVersion" => "3.2",
      "swVersion" => "11.4"
    ];
    return $module;
  }

  private function getLightState ($m): array {
    return [
      "status" => "SUCCESS",
      "online" => true,
      "on" => $m['data'] === '1'
    ];
  }

  private function getModuleTemperature ($m, $room): array
  {
    $module = [];
    $module['id'] = $m['id'];
    $module['type'] = $m['type']['google_type']['name'];
    $traits = [];
    foreach ($m['type']['google_traits'] as $trait) {
      array_push($traits, $trait['name']);
    }
    $module['traits'] = $traits;
    $module['name'] = [
      'defaultNames' => [$m['name']],
      'name' => $m['name'],
      'nicknames' => [$m['name']]
    ];
    $module['willReportState'] = false;
    $module['roomHint'] = $room['name'];
    $module['deviceInfo'] = [
      "manufacturer" => "smart-home-inc",
      "model" => "hs1234",
      "hwVersion" => "3.2",
      "swVersion" => "11.4"
    ];
    $module['attributes'] = [
      "temperatureRange" => [
        "minThresholdCelsius" => 0,
        "maxThresholdCelsius" => 35,
        "temperatureAmbientCelsius" => (int) $m['data'],
        "temperatureSetpointCelsius" => (int) $m['data']
      ],
      "temperatureUnitForUX" => "C",
      "commandOnlyTemperatureControl" => false,
      "queryOnlyTemperatureControl" => true,
    ];
    return $module;
  }

  private function getTemperatureState (array $m): array
  {
    return [
      "status" => "SUCCESS",
      "online" => true,
      "temperatureAmbientCelsius" => (int) $m['data'],
    ];
  }

  private function getData(): array
  {
    $response = Http::get('http://95.188.80.41:8080/api/google-home/modules');
    return $response->json()['rooms'];
  }

  private function getModule (int $id): array
  {
    $response = Http::get('http://95.188.80.41:8080/api/google-home/modules/' . $id);
    return $response->json()['module'];
  }

  private function setDataModule(int $id,string $data): bool {
    $response = Http::post('http://95.188.80.41:8080/api/google-home/module/' . $id, [
      'data' => $data
    ]);
    return $response->json()['status'];
  }

}
